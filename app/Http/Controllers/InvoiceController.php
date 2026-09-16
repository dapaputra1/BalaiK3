<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSuketSetting;
use App\Models\DraftLhu;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    use InteractsWithSuketSetting;

    public function index()
    {
        $this->ensureAccess();

        $invoiceStep = $this->ensureWorkflowStep('invoice', 'Kuitansi', 18);
        $query = Permohonan::with([
            'company',
            'parameters',
            'pengujian.lokasi.dokumen.parameters.serviceParameter',
            'draftLhu',
        ]);

        $query->where(function ($q) use ($invoiceStep) {
            $q->whereHas('steps', function ($inner) use ($invoiceStep) {
                $inner->where('step_id', $invoiceStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })->orWhere('status_global', 'invoice');
        });

        $permohonans = $query->oldest()->get();
        $rolePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'superadmin';

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($rolePrefix) {
            $company = $permohonan->company;
            $penawaran = $this->aggregatePenawaran($permohonan);
            $pengujian = $this->aggregatePengujian($permohonan, $penawaran);
            $hasPerubahan = $this->hasParameterDifference($penawaran['items'], $pengujian['items']);
            $draft = $permohonan->draftLhu;
            $invoiceGeneratedAt = $draft?->invoice_generated_at;
            $statusKey = !empty($invoiceGeneratedAt) ? 'terkirim' : 'belum_kirim';

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'alamat' => $company?->company_address ?? '-',
                'penawaran_items' => $penawaran['items']->values()->all(),
                'pengujian_items' => $pengujian['items']->values()->all(),
                'subtotal_penawaran' => (float) $penawaran['subtotal'],
                'subtotal_pengujian' => (float) $pengujian['subtotal'],
                'has_perubahan_pengujian' => $hasPerubahan,
                'invoice_url' => route($rolePrefix . '.invoice.show', $permohonan->id),
                'submit_url' => route($rolePrefix . '.invoice.submit', $permohonan->id),
                'kuitansi_status' => $statusKey,
                'kuitansi_sent_at' => optional($invoiceGeneratedAt)->format('d M Y H:i'),
            ];
        });

        return view('admin.superadmin_invoice', [
            'orders' => $orders,
            'routePrefix' => $rolePrefix,
        ]);
    }

    public function show(Permohonan $permohonan)
    {
        $this->ensureAccess();

        $permohonan->loadMissing([
            'company',
            'parameters',
            'pengujian.lokasi.dokumen.parameters.serviceParameter',
        ]);

        $penawaran = $this->aggregatePenawaran($permohonan);
        $pengujian = $this->aggregatePengujian($permohonan, $penawaran);
        $company = $permohonan->company;
        $items = $pengujian['items'] instanceof Collection ? $pengujian['items']->values()->all() : [];
        $total = (float) ($pengujian['subtotal'] ?? 0);

        return view('admin.invoice_preview', [
            'kode' => $permohonan->kode ?? '-',
            'tanggal' => now()->format('d-m-Y'),
            'perusahaan' => $company?->company_name ?? '-',
            'alamat' => $company?->company_address ?? '-',
            'items' => $items,
            'total' => $total,
            'document_title' => 'KUITANSI',
        ]);
    }

    public function submitToBilling(Permohonan $permohonan)
    {
        $this->ensureAccess();

        if (empty($permohonan->draftLhu?->billing_verified_at)) {
            return response()->json(['message' => 'Pembayaran belum diverifikasi. Tahap kuitansi belum bisa diproses.'], 422);
        }

        $invoiceStep = $this->ensureWorkflowStep('invoice', 'Kuitansi', 18);
        $suketStep = $this->ensureWorkflowStep('penerbitan_suket', 'Penerbitan Suket', 19);
        $penyerahanStep = $this->ensureWorkflowStep('penyerahan_lhu', 'Penyerahan LHU', 20);
        $targetUserIds = $this->resolveCustomerNotificationUserIds($permohonan);
        $suketEnabled = $this->isSuketEnabled();

        DB::transaction(function () use ($permohonan, $invoiceStep, $suketStep, $penyerahanStep, $targetUserIds, $suketEnabled) {
            DraftLhu::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                ['created_by' => auth()->id()]
            )->update([
                'invoice_generated_by' => auth()->id(),
                'invoice_generated_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $invoiceStep->id],
                [
                    'status' => 'approved',
                    'note' => 'Kuitansi diteruskan ke pemohon',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]
            );

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $suketStep->id],
                [
                    'status' => $suketEnabled ? 'pending' : 'approved',
                    'note' => $suketEnabled
                        ? 'Menunggu penerbitan surat keterangan'
                        : 'Tahap penerbitan surat keterangan dilewati karena fitur nonaktif',
                    'started_at' => now(),
                    'finished_at' => $suketEnabled ? null : now(),
                    'updated_by' => auth()->id(),
                ]
            );

            if (!$suketEnabled) {
                PermohonanStep::updateOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $penyerahanStep->id],
                    [
                        'status' => 'pending',
                        'note' => 'Menunggu penyerahan LHU ke pemohon',
                        'started_at' => now(),
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }

            $permohonan->update([
                'status_global' => $suketEnabled ? 'penerbitan_suket' : 'penyerahan_lhu',
                'status_lab' => $suketEnabled ? 'penerbitan_suket' : 'penyerahan_lhu',
            ]);

            foreach ($targetUserIds as $userId) {
                Notifikasi::create([
                    'user_id' => $userId,
                    'title' => 'Kuitansi Tersedia',
                    'message' => 'Kuitansi untuk permohonan ' . $permohonan->kode . ' sudah diteruskan ke Anda.',
                    'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
                ]);
            }

            User::query()
                ->whereIn('role', ['admin', 'superadmin'])
                ->select('id')
                ->get()
                ->each(function (User $user) use ($permohonan, $suketEnabled) {
                    Notifikasi::create([
                        'user_id' => $user->id,
                        'title' => 'Kuitansi Diteruskan',
                        'message' => 'Kuitansi permohonan ' . $permohonan->kode . ' sudah diteruskan ke pemohon. '
                            . ($suketEnabled
                                ? 'Lanjut ke tahap penerbitan suket.'
                                : 'Tahap penerbitan suket dilewati dan permohonan masuk ke penyerahan LHU.'),
                        'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
                    ]);
                });
        });

        return response()->json([
            'message' => $suketEnabled
                ? 'Kuitansi berhasil diteruskan ke pelanggan. Permohonan lanjut ke tahap Penerbitan Suket.'
                : 'Kuitansi berhasil diteruskan ke pelanggan. Tahap Penerbitan Suket dilewati dan permohonan langsung masuk ke Penyerahan LHU.',
        ]);
    }

    private function ensureAccess(): void
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['admin', 'superadmin'], true)) {
            abort(403);
        }
    }

    private function ensureWorkflowStep(string $kode, string $nama, int $urutan): WorkflowStep
    {
        return WorkflowStep::firstOrCreate(
            ['kode' => $kode],
            ['nama' => $nama, 'urutan' => $urutan]
        );
    }

    private function aggregatePenawaran(Permohonan $permohonan): array
    {
        $rows = $permohonan->parameters->map(function ($param) {
            return [
                'service_parameter_id' => $param->service_parameter_id,
                'nama' => $param->parameter_name ?? '-',
                'qty' => (int) ($param->qty ?? 0),
                'harga' => (float) ($param->price ?? 0),
            ];
        });

        $items = $this->aggregateRows($rows);
        $subtotal = $items->sum(fn ($item) => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));

        return [
            'items' => $items,
            'subtotal' => (float) $subtotal,
        ];
    }

    private function aggregatePengujian(Permohonan $permohonan, array $penawaran): array
    {
        $penawaranMap = collect($penawaran['items'])
            ->mapWithKeys(function ($item) {
                return [$this->buildParamKey($item['service_parameter_id'] ?? null, $item['nama'] ?? '-') => $item];
            });

        $rows = collect();
        if ($permohonan->pengujian) {
            $permohonan->pengujian->lokasi->each(function ($lokasi) use (&$rows) {
                $lokasi->dokumen->each(function ($dokumen) use (&$rows) {
                    $dokumen->parameters->each(function ($param) use (&$rows) {
                        $rows->push([
                            'service_parameter_id' => $param->service_parameter_id,
                            'nama' => $param->serviceParameter?->name ?? '-',
                            'qty' => (int) ($param->qty ?? 0),
                            'harga' => (float) ($param->serviceParameter?->price ?? 0),
                        ]);
                    });
                });
            });
        }

        $items = $this->aggregateRows($rows, function ($row) use ($penawaranMap) {
            $key = $this->buildParamKey($row['service_parameter_id'] ?? null, $row['nama'] ?? '-');
            if ($penawaranMap->has($key)) {
                return (float) ($penawaranMap->get($key)['harga'] ?? 0);
            }

            return (float) ($row['harga'] ?? 0);
        });

        $subtotal = $items->sum(fn ($item) => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));
        return [
            'items' => $items,
            'subtotal' => (float) $subtotal,
        ];
    }

    private function aggregateRows(Collection $rows, ?callable $resolvePrice = null): Collection
    {
        $grouped = [];

        foreach ($rows as $row) {
            $nama = trim((string) ($row['nama'] ?? '-'));
            $qty = (int) ($row['qty'] ?? 0);
            $harga = $resolvePrice ? (float) $resolvePrice($row) : (float) ($row['harga'] ?? 0);
            $id = $row['service_parameter_id'] ?? null;
            $key = $this->buildParamKey($id, $nama);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'service_parameter_id' => $id,
                    'nama' => $nama !== '' ? $nama : '-',
                    'qty' => 0,
                    'harga' => $harga,
                ];
            }

            $grouped[$key]['qty'] += $qty;
            if ($grouped[$key]['harga'] <= 0 && $harga > 0) {
                $grouped[$key]['harga'] = $harga;
            }
        }

        return collect(array_values($grouped))->map(function ($item) {
            $item['subtotal'] = (float) (($item['qty'] ?? 0) * ($item['harga'] ?? 0));
            return $item;
        })->values();
    }

    private function hasParameterDifference(Collection $penawaranItems, Collection $pengujianItems): bool
    {
        if ($pengujianItems->isEmpty()) {
            return false;
        }

        $penawaranMap = $penawaranItems->mapWithKeys(function ($item) {
            $key = $this->buildParamKey($item['service_parameter_id'] ?? null, $item['nama'] ?? '-');
            return [$key => (int) ($item['qty'] ?? 0)];
        });

        $pengujianMap = $pengujianItems->mapWithKeys(function ($item) {
            $key = $this->buildParamKey($item['service_parameter_id'] ?? null, $item['nama'] ?? '-');
            return [$key => (int) ($item['qty'] ?? 0)];
        });

        if ($penawaranMap->keys()->sort()->values()->all() !== $pengujianMap->keys()->sort()->values()->all()) {
            return true;
        }

        foreach ($penawaranMap as $key => $qty) {
            if ((int) $qty !== (int) ($pengujianMap->get($key) ?? 0)) {
                return true;
            }
        }

        return false;
    }

    private function buildParamKey($serviceParameterId, string $name): string
    {
        if (!empty($serviceParameterId)) {
            return 'id:' . $serviceParameterId;
        }

        return 'name:' . strtolower(trim($name));
    }

    private function resolveCustomerNotificationUserIds(Permohonan $permohonan): array
    {
        $userIds = collect();

        if (!empty($permohonan->user_id)) {
            $userIds->push((int) $permohonan->user_id);
        }

        $companyEmail = trim((string) ($permohonan->company?->company_email ?? ''));
        if ($companyEmail !== '') {
            $matchedIds = User::query()
                ->where('role', 'user')
                ->where('email', $companyEmail)
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            $userIds = $userIds->merge($matchedIds);
        }

        return $userIds
            ->filter(fn ($id) => (int) $id > 0)
            ->unique()
            ->values()
            ->all();
    }


}
