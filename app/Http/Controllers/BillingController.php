<?php

namespace App\Http\Controllers;

use App\Models\DraftLhu;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Services\BillingGuideService;
use App\Support\SafeDocumentUpload;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BillingController extends Controller
{
    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

    public function index(BillingGuideService $billingGuideService)
    {
        $this->ensureAccess();

        $billingStep = $this->ensureWorkflowStep('kode_billing', 'Kode Billing', 17);
        $query = Permohonan::with([
            'company',
            'parameters.serviceParameter.category',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'draftLhu',
            'steps.step',
        ]);

        $query->where(function ($q) use ($billingStep) {
            $q->whereHas('steps', function ($inner) use ($billingStep) {
                $inner->where('step_id', $billingStep->id)
                    ->whereIn('status', ['pending', 'in_progress', 'approved']);
            })->orWhereIn('status_global', ['kode_billing', 'penyerahan_lhu']);
        });

        $permohonans = $query->oldest()->get();
        $rolePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'superadmin';

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($rolePrefix) {
            $company = $permohonan->company;
            $penawaran = $this->aggregatePenawaran($permohonan);
            $pengujian = $this->aggregatePengujian($permohonan, $penawaran);
            $draft = $permohonan->draftLhu;
            $billingStep = $permohonan->steps
                ->first(function (PermohonanStep $step) {
                    return ($step->step->kode ?? null) === 'kode_billing';
                });
            $billingStepNote = trim((string) ($billingStep?->note ?? ''));
            $isRenewalRequest = str_contains(strtolower($billingStepNote), 'kode billing terbaru');

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'alamat' => $company?->company_address ?? '-',
                'penawaran_items' => $penawaran['items']->values()->all(),
                'pengujian_items' => $pengujian['items']->values()->all(),
                'subtotal_penawaran' => (float) $penawaran['subtotal'],
                'subtotal_pengujian' => (float) $pengujian['subtotal'],
                'surat_tagihan_url' => route($rolePrefix . '.surat-tagihan.show', $permohonan->id),
                'invoice_url' => route($rolePrefix . '.invoice.show', $permohonan->id),
                'billing_name' => $draft?->billing_file_name,
                'billing_uploaded_at' => optional($draft?->billing_uploaded_at)->format('d M Y H:i'),
                'billing_url' => !empty($draft?->billing_file_path) ? route($rolePrefix . '.billing.show', $permohonan->id) : null,
                'payment_proof_name' => $draft?->billing_payment_proof_name,
                'payment_proof_uploaded_at' => optional($draft?->billing_payment_proof_uploaded_at)->format('d M Y H:i'),
                'payment_proof_url' => (!empty($draft?->billing_payment_proof_path) && $this->fileExists($draft->billing_payment_proof_path))
                    ? route($rolePrefix . '.billing.payment-proof.show', $permohonan->id)
                    : null,
                'upload_url' => route($rolePrefix . '.billing.upload', $permohonan->id),
                'send_url' => route($rolePrefix . '.billing.send', $permohonan->id),
                'verify_url' => route($rolePrefix . '.billing.verify', $permohonan->id),
                'sent_to_user' => !empty($draft?->billing_sent_at),
                'invoice_verified_at' => optional($draft?->invoice_verified_by_user_at)->format('d M Y H:i'),
                'billing_expires_at' => optional($draft?->billing_expires_at)->format('d M Y H:i'),
                'paid_by_user' => !empty($draft?->billing_paid_by_user_at),
                'paid_by_user_at' => optional($draft?->billing_paid_by_user_at)->format('d M Y H:i'),
                'verified_at' => optional($draft?->billing_verified_at)->format('d M Y H:i'),
                'status_key' => $this->resolveBillingStatusKey($draft),
                'is_billing_renewal_request' => $isRenewalRequest,
                'billing_step_note' => $billingStepNote,
            ];
        });

        return view('admin.superadmin_billing', [
            'orders' => $orders,
            'routePrefix' => $rolePrefix,
            'paymentGuide' => $billingGuideService->getViewData(false),
            'canUploadPaymentGuide' => in_array(auth()->user()?->role, ['admin', 'superadmin'], true),
            'paymentGuideUploadUrl' => route('billing-guide.upload'),
            'paymentGuideDeleteUrl' => route('billing-guide.destroy'),
        ]);
    }

    public function upload(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();
        $this->ensureBillingColumns();

        if (empty($permohonan->draftLhu?->invoice_verified_by_user_at)) {
            return response()->json(['message' => 'Surat tagihan belum di-ACC pelanggan.'], 422);
        }

        $data = $request->validate([
            'billing_file' => ['required', 'file', 'max:10240'],
        ], [
            'billing_file.required' => 'File kode billing wajib dipilih.',
            'billing_file.file' => 'File kode billing tidak valid.',
            'billing_file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $file = $data['billing_file'];
        SafeDocumentUpload::validateOrFail($file, 'billing_file');
        $ext = strtolower((string) $file->getClientOriginalExtension());

        $draft = DraftLhu::firstOrCreate(
            ['permohonan_id' => $permohonan->id],
            ['created_by' => auth()->id()]
        );

        $this->deleteIfExists($draft->billing_file_path);

        $folder = 'billing/' . $permohonan->id;
        $filename = 'kode_billing_' . now()->format('Ymd_His') . '.' . $ext;
        $path = $file->storeAs($folder, $filename, self::PRIVATE_DISK);

        $draft->update([
            'billing_file_path' => $path,
            'billing_file_name' => $file->getClientOriginalName(),
            'billing_uploaded_by' => auth()->id(),
            'billing_uploaded_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $rolePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'superadmin';
        return response()->json([
            'message' => 'Kode billing berhasil diupload.',
            'name' => $draft->billing_file_name,
            'uploaded_at' => optional($draft->billing_uploaded_at)->format('d M Y H:i'),
            'url' => route($rolePrefix . '.billing.show', $permohonan->id),
        ]);
    }

    public function sendToUser(Permohonan $permohonan, BillingGuideService $billingGuideService)
    {
        $this->ensureAccess();
        $this->ensureBillingColumns();

        $draft = $permohonan->draftLhu;
        if (empty($draft?->invoice_verified_by_user_at)) {
            return response()->json(['message' => 'Surat tagihan belum di-ACC pelanggan.'], 422);
        }

        $billingPath = $draft?->billing_file_path;
        if (!$billingPath || !$this->fileExists($billingPath)) {
            return response()->json(['message' => 'Upload dokumen kode billing terlebih dahulu.'], 422);
        }

        if (!$billingGuideService->getActiveGuide()) {
            return response()->json([
                'message' => 'Upload panduan pembayaran terlebih dahulu sebelum mengirim kode billing ke pemohon.',
            ], 422);
        }

        $billingStep = $this->ensureWorkflowStep('kode_billing', 'Kode Billing', 17);

        $existingProofPath = $draft?->billing_payment_proof_path;
        if ($existingProofPath) {
            $this->deleteIfExists($existingProofPath);
        }

        DB::transaction(function () use ($permohonan, $billingStep) {
            DraftLhu::where('permohonan_id', $permohonan->id)->update([
                'billing_sent_at' => now(),
                'billing_sent_by' => auth()->id(),
                'billing_expires_at' => now()->addHours(24),
                'billing_payment_proof_path' => null,
                'billing_payment_proof_name' => null,
                'billing_payment_proof_uploaded_by' => null,
                'billing_payment_proof_uploaded_at' => null,
                'billing_paid_by_user_id' => null,
                'billing_paid_by_user_at' => null,
                'billing_verified_by' => null,
                'billing_verified_at' => null,
                'updated_by' => auth()->id(),
            ]);

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $billingStep->id],
                [
                    'status' => 'in_progress',
                    'note' => 'Kode billing dikirim ke pemohon, menunggu pembayaran',
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]
            );

            $permohonan->update([
                'status_global' => 'kode_billing',
                'status_lab' => 'kode_billing',
            ]);

            Notifikasi::create([
                'user_id' => $permohonan->user_id,
                'title' => 'Kode Billing Tersedia',
                'message' => 'Kode billing untuk permohonan ' . $permohonan->kode . ' sudah dikirim dan berlaku 24 jam. Panduan pembayaran PDF tersedia pada detail billing di riwayat pelayanan.',
                'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
            ]);
        });

        return response()->json(['message' => 'Kode billing berhasil dikirim ke pemohon.']);
    }

    public function verifyPayment(Permohonan $permohonan)
    {
        $this->ensureAccess();
        $this->ensureBillingColumns();

        $draft = DraftLhu::firstWhere('permohonan_id', $permohonan->id);
        if (!$draft || empty($draft->billing_paid_by_user_at)) {
            return response()->json(['message' => 'Pelanggan belum mengirim konfirmasi pembayaran.'], 422);
        }
        if (empty($draft->billing_payment_proof_path) || !$this->fileExists($draft->billing_payment_proof_path)) {
            return response()->json(['message' => 'Bukti pembayaran dari pelanggan belum tersedia.'], 422);
        }
        if (!empty($draft->billing_verified_at)) {
            return response()->json(['message' => 'Pembayaran sudah diverifikasi sebelumnya.']);
        }

        $billingStep = $this->ensureWorkflowStep('kode_billing', 'Kode Billing', 17);
        $kuitansiStep = $this->ensureWorkflowStep('invoice', 'Kuitansi', 18);

        DB::transaction(function () use ($permohonan, $draft, $billingStep, $kuitansiStep) {
            $draft->update([
                'billing_verified_by' => auth()->id(),
                'billing_verified_at' => now(),
                'invoice_generated_by' => null,
                'invoice_generated_at' => null,
                'updated_by' => auth()->id(),
            ]);

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $billingStep->id],
                [
                    'status' => 'approved',
                    'note' => 'Pembayaran terverifikasi, menunggu penerbitan kuitansi',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]
            );

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $kuitansiStep->id],
                [
                    'status' => 'pending',
                    'note' => 'Pembayaran terverifikasi, menunggu petugas meneruskan kuitansi ke pemohon',
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]
            );

            $permohonan->update([
                'status_global' => 'invoice',
                'status_lab' => 'invoice',
            ]);

            Notifikasi::create([
                'user_id' => $permohonan->user_id,
                'title' => 'Pembayaran Terverifikasi',
                'message' => 'Pembayaran permohonan ' . $permohonan->kode . ' sudah diverifikasi petugas. Kuitansi sedang disiapkan.',
                'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
            ]);
        });

        User::query()
            ->whereIn('role', ['admin', 'superadmin'])
            ->select('id', 'role')
            ->get()
            ->each(function (User $user) use ($permohonan) {
                $url = $user->role === 'admin'
                    ? route('admin.invoice.index')
                    : route('superadmin.invoice.index');
                Notifikasi::create([
                    'user_id' => $user->id,
                    'title' => 'Siap Terbitkan Kuitansi',
                    'message' => 'Pembayaran permohonan ' . $permohonan->kode . ' sudah diverifikasi. Lanjutkan ke tahap kuitansi.',
                    'url' => $url . '?kode=' . urlencode((string) $permohonan->kode),
                ]);
            });

        return response()->json(['message' => 'Pembayaran diverifikasi. Permohonan lanjut ke tahap Kuitansi.']);
    }

    public function show(Permohonan $permohonan)
    {
        $this->ensureAccess();
        $path = $permohonan->draftLhu?->billing_file_path;
        if (!$path || !$this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $name = $permohonan->draftLhu?->billing_file_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $name . '"',
            ]);
        }

        return response()->download($fullPath, $name);
    }

    public function showPaymentProof(Permohonan $permohonan)
    {
        $this->ensureAccess();

        $path = $permohonan->draftLhu?->billing_payment_proof_path;
        if (!$path || !$this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }

        $fullPath = Storage::disk($disk)->path($path);
        $name = $permohonan->draftLhu?->billing_payment_proof_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp'], true)) {
            return response()->file($fullPath, [
                'Content-Disposition' => 'inline; filename="' . $name . '"',
            ]);
        }

        return response()->download($fullPath, $name);
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

    private function ensureBillingColumns(): void
    {
        if (
            !Schema::hasTable('draft_lhus')
            || !Schema::hasColumn('draft_lhus', 'billing_file_path')
            || !Schema::hasColumn('draft_lhus', 'billing_file_name')
            || !Schema::hasColumn('draft_lhus', 'billing_uploaded_by')
            || !Schema::hasColumn('draft_lhus', 'billing_uploaded_at')
            || !Schema::hasColumn('draft_lhus', 'billing_sent_at')
            || !Schema::hasColumn('draft_lhus', 'billing_sent_by')
            || !Schema::hasColumn('draft_lhus', 'invoice_verified_by_user_id')
            || !Schema::hasColumn('draft_lhus', 'invoice_verified_by_user_at')
            || !Schema::hasColumn('draft_lhus', 'billing_expires_at')
            || !Schema::hasColumn('draft_lhus', 'billing_payment_proof_path')
            || !Schema::hasColumn('draft_lhus', 'billing_payment_proof_name')
            || !Schema::hasColumn('draft_lhus', 'billing_payment_proof_uploaded_by')
            || !Schema::hasColumn('draft_lhus', 'billing_payment_proof_uploaded_at')
            || !Schema::hasColumn('draft_lhus', 'billing_paid_by_user_id')
            || !Schema::hasColumn('draft_lhus', 'billing_paid_by_user_at')
            || !Schema::hasColumn('draft_lhus', 'billing_verified_by')
            || !Schema::hasColumn('draft_lhus', 'billing_verified_at')
        ) {
            abort(500, 'Struktur database kode billing belum tersedia. Jalankan migrate terbaru.');
        }
    }

    private function resolveBillingStatusKey(?DraftLhu $draft): string
    {
        if (!$draft || empty($draft->billing_sent_at)) {
            return 'belum_mengirim';
        }
        if (!empty($draft->billing_verified_at)) {
            return 'selesai';
        }
        if (!empty($draft->billing_paid_by_user_at)) {
            return 'pelanggan_sudah_bayar';
        }
        return 'menunggu_kode_billing';
    }

    private function aggregatePenawaran(Permohonan $permohonan): array
    {
        $rows = $permohonan->parameters->map(function ($param) {
            return [
                'service_parameter_id' => $param->service_parameter_id,
                'nama' => $param->parameter_name ?? '-',
                'kategori' => $param->serviceParameter?->category?->name ?? '-',
                'qty' => (int) ($param->qty ?? 0),
                'harga' => (float) ($param->price ?? 0),
            ];
        });

        $items = $this->aggregateRows($rows);
        $subtotal = $items->sum(fn ($item) => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));

        return ['items' => $items, 'subtotal' => (float) $subtotal];
    }

    private function aggregatePengujian(Permohonan $permohonan, array $penawaran): array
    {
        $penawaranMap = collect($penawaran['items'])
            ->mapWithKeys(fn ($item) => [$this->buildParamKey($item['service_parameter_id'] ?? null, $item['nama'] ?? '-') => $item]);

        $rows = collect();
        if ($permohonan->pengujian) {
            $permohonan->pengujian->lokasi->each(function ($lokasi) use (&$rows) {
                $lokasi->dokumen->each(function ($dokumen) use (&$rows) {
                    $dokumen->parameters->each(function ($param) use (&$rows) {
                        $rows->push([
                            'service_parameter_id' => $param->service_parameter_id,
                            'nama' => $param->serviceParameter?->name ?? '-',
                            'kategori' => $param->serviceParameter?->category?->name ?? '-',
                            'qty' => (int) ($param->qty ?? 0),
                            'harga' => (float) ($param->serviceParameter?->price ?? 0),
                        ]);
                    });
                });
            });
        }

        $items = $this->aggregateRows($rows, function ($row) use ($penawaranMap) {
            $key = $this->buildParamKey($row['service_parameter_id'] ?? null, $row['nama'] ?? '-');
            return (float) ($penawaranMap->get($key)['harga'] ?? ($row['harga'] ?? 0));
        });

        if ($items->isEmpty()) {
            $items = collect($penawaran['items']);
        }

        $subtotal = $items->sum(fn ($item) => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));
        return ['items' => $items, 'subtotal' => (float) $subtotal];
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
                    'kategori' => trim((string) ($row['kategori'] ?? '')) !== '' ? trim((string) $row['kategori']) : '-',
                    'qty' => 0,
                    'harga' => $harga,
                ];
            }
            $grouped[$key]['qty'] += $qty;
            if (($grouped[$key]['kategori'] ?? '-') === '-' && trim((string) ($row['kategori'] ?? '')) !== '') {
                $grouped[$key]['kategori'] = trim((string) $row['kategori']);
            }
            if ($grouped[$key]['harga'] <= 0 && $harga > 0) {
                $grouped[$key]['harga'] = $harga;
            }
        }

        return collect(array_values($grouped))->map(function ($item) {
            $item['subtotal'] = (float) (($item['qty'] ?? 0) * ($item['harga'] ?? 0));
            return $item;
        })->values();
    }

    private function buildParamKey($serviceParameterId, string $name): string
    {
        if (!empty($serviceParameterId)) {
            return 'id:' . $serviceParameterId;
        }
        return 'name:' . strtolower(trim($name));
    }

    private function resolveDisk(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            return self::PRIVATE_DISK;
        }
        if (Storage::disk(self::LEGACY_DISK)->exists($path)) {
            return self::LEGACY_DISK;
        }
        return null;
    }

    private function fileExists(?string $path): bool
    {
        return $this->resolveDisk($path) !== null;
    }

    private function deleteIfExists(?string $path): void
    {
        $disk = $this->resolveDisk($path);
        if ($disk) {
            Storage::disk($disk)->delete($path);
        }
    }
}
