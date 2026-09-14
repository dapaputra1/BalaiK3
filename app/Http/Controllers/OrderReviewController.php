<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanParameter;
use App\Models\PermohonanStep;
use App\Models\ServiceParameter;
use App\Models\StepApproval;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderReviewController extends Controller
{
    public function index()
    {
        $permohonans = Permohonan::query()
            ->with([
                'company',
                'user:id,name,email',
                'orderReviewer:id,name',
                'parameters.serviceParameter.category',
            ])
            ->where(function ($query) {
                $query->whereIn('order_review_status', ['pending_admin', 'pending_customer'])
                    ->orWhereNotNull('order_reviewed_at');
            })
            ->whereNotIn('status_global', ['cancelled', 'rejected'])
            ->latest()
            ->limit(200)
            ->get();

        $serviceParameters = ServiceParameter::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'service_category_id', 'name', 'price']);

        return view('admin.order_review', [
            'permohonans' => $permohonans,
            'serviceParameters' => $serviceParameters,
        ]);
    }

    public function sendToCustomer(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate([
            'parameters' => ['required', 'array', 'min:1', 'max:100'],
            'parameters.*.service_parameter_id' => ['required', 'integer', 'distinct', 'exists:service_parameters,id'],
            'parameters.*.qty' => ['required', 'integer', 'min:1', 'max:9999'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [
            'parameters.*.service_parameter_id.distinct' => 'Parameter yang sama tidak boleh dipilih lebih dari sekali.',
        ]);

        $parameterIds = collect($data['parameters'])
            ->pluck('service_parameter_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $serviceParameters = ServiceParameter::query()
            ->whereIn('id', $parameterIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($serviceParameters->count() !== $parameterIds->count()) {
            throw ValidationException::withMessages([
                'parameters' => 'Salah satu parameter tidak aktif atau tidak tersedia.',
            ]);
        }

        DB::transaction(function () use ($permohonan, $data, $serviceParameters) {
            $locked = Permohonan::query()->lockForUpdate()->findOrFail($permohonan->id);

            if (! in_array($locked->order_review_status, ['pending_admin', 'pending_customer'], true)) {
                throw ValidationException::withMessages([
                    'parameters' => 'Pesanan sudah disetujui pelanggan dan tidak dapat diubah lagi.',
                ]);
            }

            if ($this->parameterSignaturesMatch($data['parameters'], $locked->order_review_original_parameters ?? [])) {
                throw ValidationException::withMessages([
                    'parameters' => 'Tidak ada perubahan dari pesanan awal pelanggan. Gunakan tombol verifikasi langsung ke disposisi.',
                ]);
            }

            $locked->parameters()->delete();

            foreach ($data['parameters'] as $item) {
                $parameter = $serviceParameters->get((int) $item['service_parameter_id']);

                PermohonanParameter::create([
                    'permohonan_id' => $locked->id,
                    'service_parameter_id' => $parameter->id,
                    'parameter_name' => $parameter->name,
                    'qty' => (int) $item['qty'],
                    'price' => $parameter->price,
                    'status' => 'active',
                ]);
            }

            $locked->update([
                'status_global' => 'verifikasi_pesanan',
                'order_review_status' => 'pending_customer',
                'order_review_note' => trim((string) ($data['note'] ?? '')) ?: null,
                'order_reviewed_by' => auth()->id(),
                'order_reviewed_at' => now(),
                'order_review_sent_at' => now(),
                'order_review_customer_approved_at' => null,
                'order_review_customer_approved_by' => null,
            ]);

            Notifikasi::create([
                'user_id' => $locked->user_id,
                'title' => 'Perbaikan Pesanan Menunggu Persetujuan',
                'message' => 'Parameter dan jumlah pesanan '.$locked->kode.' telah diperiksa. Silakan cek dan setujui hasil perbaikannya.',
                'url' => route('riwayat_pelayanan.index').'?kode='.urlencode((string) $locked->kode),
            ]);
        });

        return response()->json([
            'message' => 'Perbaikan pesanan berhasil dikirim ke pelanggan.',
        ]);
    }

    public function approveDirectly(Permohonan $permohonan)
    {
        DB::transaction(function () use ($permohonan) {
            $locked = Permohonan::query()->lockForUpdate()->findOrFail($permohonan->id);

            if (! in_array($locked->order_review_status, ['pending_admin', 'pending_customer'], true)) {
                throw ValidationException::withMessages([
                    'order_review' => 'Pesanan sudah diverifikasi dan tidak dapat diproses ulang.',
                ]);
            }

            if (! $locked->parameters()->where('qty', '>', 0)->exists()) {
                throw ValidationException::withMessages([
                    'order_review' => 'Pesanan tidak memiliki parameter yang dapat diverifikasi.',
                ]);
            }

            if (! $this->currentParametersMatchOriginal($locked)) {
                throw ValidationException::withMessages([
                    'order_review' => 'Hasil verifikasi berbeda dari pesanan awal. Kirim perbaikan ke pelanggan terlebih dahulu.',
                ]);
            }

            $this->sendToDisposisi($locked);

            $locked->update([
                'status_global' => 'disposisi',
                'order_review_status' => 'approved',
                'order_review_note' => null,
                'order_reviewed_by' => auth()->id(),
                'order_reviewed_at' => now(),
                'order_review_sent_at' => null,
                'order_review_customer_approved_at' => null,
                'order_review_customer_approved_by' => null,
            ]);

            Notifikasi::create([
                'user_id' => $locked->user_id,
                'title' => 'Pesanan Diterima',
                'message' => 'Pesanan '.$locked->kode.' telah diverifikasi dan diterima. Pesanan dilanjutkan ke tahap selanjutnya.',
                'url' => route('riwayat_pelayanan.index').'?kode='.urlencode((string) $locked->kode),
            ]);

            $this->notifyDisposisiRecipients($locked, 'Pesanan Masuk Disposisi', 'Pesanan '.$locked->kode.' telah diverifikasi dan masuk ke tahap disposisi.');
        });

        return response()->json([
            'message' => 'Pesanan diverifikasi dan diteruskan ke disposisi.',
            'redirect_url' => route('superadmin.disposisi.index').'?kode='.urlencode((string) $permohonan->kode),
        ]);
    }

    public function approveByCustomer(Permohonan $permohonan)
    {
        if ((int) $permohonan->user_id !== (int) auth()->id()) {
            abort(403);
        }

        DB::transaction(function () use ($permohonan) {
            $locked = Permohonan::query()->lockForUpdate()->findOrFail($permohonan->id);

            if ($locked->order_review_status !== 'pending_customer') {
                throw ValidationException::withMessages([
                    'order_review' => 'Perbaikan pesanan belum dikirim atau sudah disetujui.',
                ]);
            }

            if (! $locked->parameters()->where('qty', '>', 0)->exists()) {
                throw ValidationException::withMessages([
                    'order_review' => 'Pesanan tidak memiliki parameter yang dapat disetujui.',
                ]);
            }

            $this->sendToDisposisi($locked);

            $locked->update([
                'status_global' => 'disposisi',
                'order_review_status' => 'approved',
                'order_review_customer_approved_at' => now(),
                'order_review_customer_approved_by' => auth()->id(),
            ]);

            $this->notifyDisposisiRecipients($locked, 'Pesanan Disetujui Pelanggan', 'Pesanan '.$locked->kode.' telah disetujui pelanggan dan masuk ke tahap disposisi.');
        });

        return response()->json([
            'message' => 'Perbaikan pesanan disetujui. Pesanan diteruskan ke disposisi.',
        ]);
    }

    private function sendToDisposisi(Permohonan $permohonan): void
    {
        $disposisiStep = WorkflowStep::firstOrCreate(
            ['kode' => 'disposisi'],
            ['nama' => 'Disposisi', 'urutan' => 1]
        );

        PermohonanStep::firstOrCreate(
            [
                'permohonan_id' => $permohonan->id,
                'step_id' => $disposisiStep->id,
            ],
            [
                'status' => 'pending',
                'started_at' => now(),
            ]
        );

        foreach (['mp', 'mt'] as $role) {
            StepApproval::firstOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                    'step_id' => $disposisiStep->id,
                    'role' => $role,
                ],
                ['status' => 'pending']
            );
        }
    }

    private function notifyDisposisiRecipients(Permohonan $permohonan, string $title, string $message): void
    {
        User::query()
            ->whereIn('role', ['admin', 'mp', 'superadmin'])
            ->where('is_active', true)
            ->get(['id', 'role'])
            ->each(function (User $user) use ($permohonan, $title, $message) {
                $url = $user->role === 'admin'
                    ? route('superadmin.order-review.index')
                    : route('superadmin.disposisi.index');

                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url.'?kode='.urlencode((string) $permohonan->kode),
                ]);
            });
    }

    private function currentParametersMatchOriginal(Permohonan $permohonan): bool
    {
        $currentParameters = $permohonan->parameters()
            ->get(['service_parameter_id', 'qty'])
            ->all();

        return $this->parameterSignaturesMatch(
            $currentParameters,
            $permohonan->order_review_original_parameters ?? []
        );
    }

    private function parameterSignaturesMatch(iterable $left, iterable $right): bool
    {
        return $this->parameterSignature($left) === $this->parameterSignature($right);
    }

    private function parameterSignature(iterable $parameters): array
    {
        return collect($parameters)
            ->map(function ($parameter) {
                if (is_array($parameter)) {
                    return [
                        'service_parameter_id' => (int) ($parameter['service_parameter_id'] ?? 0),
                        'qty' => (int) ($parameter['qty'] ?? 0),
                    ];
                }

                return [
                    'service_parameter_id' => (int) ($parameter->service_parameter_id ?? 0),
                    'qty' => (int) ($parameter->qty ?? 0),
                ];
            })
            ->filter(fn ($parameter) => $parameter['service_parameter_id'] > 0 && $parameter['qty'] > 0)
            ->sortBy([
                ['service_parameter_id', 'asc'],
                ['qty', 'asc'],
            ])
            ->values()
            ->all();
    }
}
