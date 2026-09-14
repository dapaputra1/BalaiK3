<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanCompany;
use App\Models\PermohonanParameter;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PermohonanController extends Controller
{
    private const MAX_SIGNATURE_BYTES = 1048576; // 1 MB

    private const MAX_SIGNATURE_WIDTH = 2000;

    private const MAX_SIGNATURE_HEIGHT = 2000;

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'responsible_name' => ['required', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_type' => ['nullable', 'string', 'max:100'],
            'company_province' => ['nullable', 'string', 'max:100'],
            'company_city' => ['nullable', 'string', 'max:100'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'worker_count' => ['required', 'integer', 'min:1'],
            'order_proof' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'authority_same' => ['nullable', 'boolean'],
            'authority_name' => ['nullable', 'string', 'max:255'],
            'authority_role' => ['nullable', 'string', 'max:255'],
            'responsible_signature' => ['required', 'string'],
        ], [
            'company_email.email' => 'Format email tidak sesuai.',
            'order_proof.required' => 'File bukti pemesanan wajib diunggah.',
            'worker_count.required' => 'Jumlah pekerja wajib diisi.',
        ]);

        $authoritySame = (bool) ($data['authority_same'] ?? false);
        if (! $authoritySame) {
            if (empty($data['authority_name']) || empty($data['authority_role'])) {
                return response()->json([
                    'message' => 'Nama dan jabatan penandatangan wajib diisi jika berbeda dengan penanggung jawab.',
                ], 422);
            }
        }

        $cart = Cart::with(['items.serviceParameter'])
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Keranjang masih kosong.',
            ], 422);
        }

        $this->ensureWorkflowSteps();

        $signaturePath = $this->storeSignature($data['responsible_signature'] ?? null);
        if (! $signaturePath) {
            return response()->json([
                'message' => 'Tanda tangan penanggung jawab tidak valid. Silakan tanda tangan ulang. Ukuran maksimal 1 MB.',
            ], 422);
        }
        $orderProofPath = $this->storeOrderProof($request);
        if (! $orderProofPath) {
            return response()->json([
                'message' => 'File bukti pemesanan tidak valid.',
            ], 422);
        }

        $originalParameters = $cart->items->map(function ($item) {
            return [
                'service_parameter_id' => $item->service_parameter_id,
                'parameter_name' => $item->serviceParameter?->name ?? '-',
                'qty' => (int) $item->qty,
                'price' => (float) $item->price,
            ];
        })->values()->all();

        $permohonan = DB::transaction(function () use ($data, $cart, $authoritySame, $signaturePath, $orderProofPath, $originalParameters) {
            $permohonan = Permohonan::create([
                'kode' => 'PMH-'.Str::upper(Str::random(6)),
                'user_id' => auth()->id(),
                'status_global' => 'verifikasi_pesanan',
                'order_review_status' => 'pending_admin',
                'order_review_original_parameters' => $originalParameters,
            ]);

            $permohonan->update([
                'kode' => sprintf('PMH-%s-%04d', now()->format('Ymd'), $permohonan->id),
            ]);

            PermohonanCompany::create([
                'permohonan_id' => $permohonan->id,
                'company_name' => $data['company_name'],
                'responsible_name' => $data['responsible_name'],
                'company_email' => $data['company_email'] ?? null,
                'company_phone' => $data['company_phone'] ?? null,
                'company_type' => $data['company_type'] ?? null,
                'company_province' => $data['company_province'] ?? null,
                'company_city' => $data['company_city'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'worker_count' => (int) ($data['worker_count'] ?? 0),
                'order_proof_path' => $orderProofPath,
                'authority_same' => $authoritySame,
                'authority_name' => $authoritySame ? null : ($data['authority_name'] ?? null),
                // Simpan jabatan penandatangan meski authority_same=true, karena field ini tetap diisi dari form keranjang.
                'authority_role' => $data['authority_role'] ?? null,
                'responsible_signature_path' => $signaturePath,
            ]);

            $cart->items->each(function ($item) use ($permohonan) {
                PermohonanParameter::create([
                    'permohonan_id' => $permohonan->id,
                    'service_parameter_id' => $item->service_parameter_id,
                    'parameter_name' => $item->serviceParameter?->name ?? '-',
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'status' => 'active',
                ]);
            });

            $cart->update(['status' => 'submitted']);
            $cart->items()->delete();

            return $permohonan;
        });

        User::query()
            ->whereIn('role', ['admin', 'superadmin'])
            ->where('is_active', true)
            ->get(['id'])
            ->each(function (User $user) use ($permohonan) {
                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => 'Pesanan Baru Perlu Diverifikasi',
                    'message' => 'Pesanan '.$permohonan->kode.' menunggu pemeriksaan parameter dan jumlah.',
                    'url' => route('superadmin.order-review.index').'?kode='.urlencode((string) $permohonan->kode),
                ]);
            });

        return response()->json([
            'success' => true,
            'kode' => $permohonan->kode,
        ]);
    }

    public function signature(Permohonan $permohonan)
    {
        $user = auth()->user();
        $role = $user?->role;
        $isOwner = $permohonan->user_id && $user && $permohonan->user_id === $user->id;
        if (! $isOwner && ! in_array($role, ['admin', 'superadmin'], true)) {
            abort(403);
        }

        $path = $permohonan->company?->responsible_signature_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $mime = Storage::disk('local')->mimeType($path) ?? 'image/png';

        return Storage::disk('local')->response($path, 'ttd-penanggung-jawab', [
            'Content-Type' => $mime,
        ]);
    }

    private function storeSignature(?string $signature): ?string
    {
        if (! $signature) {
            return null;
        }

        $signature = trim($signature);
        if (! preg_match('/^data:image\/(png|jpeg);base64,(.+)$/s', $signature, $matches)) {
            return null;
        }

        $base64 = preg_replace('/\s+/', '', $matches[2] ?? '');
        if ($base64 === '') {
            return null;
        }

        $maxBase64Length = (int) ceil(self::MAX_SIGNATURE_BYTES * 4 / 3) + 16;
        if (strlen($base64) > $maxBase64Length) {
            return null;
        }

        $binary = base64_decode($base64, true);
        if ($binary === false || $binary === '' || strlen($binary) > self::MAX_SIGNATURE_BYTES) {
            return null;
        }

        $imageInfo = @getimagesizefromstring($binary);
        if (! is_array($imageInfo)) {
            return null;
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);
        $mime = strtolower((string) ($imageInfo['mime'] ?? ''));

        if ($width < 1 || $height < 1 || $width > self::MAX_SIGNATURE_WIDTH || $height > self::MAX_SIGNATURE_HEIGHT) {
            return null;
        }

        $extension = match ($mime) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            default => null,
        };

        if ($extension === null) {
            return null;
        }

        $filename = 'permohonan/signatures/'.now()->format('Ymd').'-'.Str::uuid().'.'.$extension;
        Storage::disk('local')->put($filename, $binary);

        return $filename;
    }

    private function storeOrderProof(Request $request): ?string
    {
        if (! $request->hasFile('order_proof')) {
            return null;
        }

        $file = $request->file('order_proof');
        if (! $file || ! $file->isValid()) {
            return null;
        }

        SafeDocumentUpload::validateOrderProofOrFail($file, 'order_proof');

        $ext = strtolower((string) $file->getClientOriginalExtension());
        $filename = 'permohonan/order-proof/'.now()->format('Ymd').'-'.Str::uuid().'.'.$ext;
        Storage::disk('local')->putFileAs(dirname($filename), $file, basename($filename));

        return $filename;
    }

    private function ensureWorkflowSteps(): array
    {
        $steps = [
            ['kode' => 'disposisi', 'nama' => 'Disposisi', 'urutan' => 1],
            ['kode' => 'kaji_ulang', 'nama' => 'Kaji Ulang', 'urutan' => 2],
            ['kode' => 'penawaran', 'nama' => 'Penawaran', 'urutan' => 3],
            ['kode' => 'penjadwalan', 'nama' => 'Penjadwalan', 'urutan' => 4],
        ];

        $result = [];
        foreach ($steps as $step) {
            $model = WorkflowStep::firstOrCreate(
                ['kode' => $step['kode']],
                ['nama' => $step['nama'], 'urutan' => $step['urutan']]
            );
            $result[$step['kode']] = $model;
        }

        return $result;
    }
}
