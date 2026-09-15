<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSuketSetting;
use App\Models\DraftLhu;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    use InteractsWithSuketSetting;

    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

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
            $hasSigned = !empty($draft?->invoice_file_path) && $this->fileExists($draft?->invoice_file_path);
            $canSubmit = $hasSigned && empty($invoiceGeneratedAt);

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
                'signed_invoice_url' => $hasSigned ? route($rolePrefix . '.invoice.signed.show', $permohonan->id) : null,
                'signed_invoice_name' => $draft?->invoice_file_name,
                'signed_invoice_uploaded_at' => optional($draft?->invoice_uploaded_at)->format('d M Y H:i'),
                'upload_url' => route($rolePrefix . '.invoice.upload', $permohonan->id),
                'submit_url' => route($rolePrefix . '.invoice.submit', $permohonan->id),
                'has_signed_invoice' => $hasSigned,
                'can_submit' => $canSubmit,
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

    // =========================================================================
    // [PERCOBAAN KUITANSI TTD BASAH] - Method Upload & Preview Scan Kuitansi Basah
    // =========================================================================
    public function uploadSigned(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        $data = $request->validate([
            'signed_invoice_file' => ['required', 'file', 'max:10240'],
        ], [
            'signed_invoice_file.required' => 'File kuitansi bertanda tangan wajib dipilih.',
            'signed_invoice_file.file' => 'File kuitansi bertanda tangan tidak valid.',
            'signed_invoice_file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $file = $data['signed_invoice_file'];
        SafeDocumentUpload::validatePdfOrFail($file, 'signed_invoice_file');
        $ext = strtolower((string) $file->getClientOriginalExtension());

        $draft = DraftLhu::firstOrCreate(
            ['permohonan_id' => $permohonan->id],
            ['created_by' => auth()->id()]
        );

        $this->deleteIfExists($draft->invoice_file_path);

        $folder = 'invoice/' . $permohonan->id;
        $filename = 'kuitansi_ttd_' . now()->format('Ymd_His') . '.' . $ext;
        $path = $file->storeAs($folder, $filename, self::PRIVATE_DISK);

        DB::transaction(function () use ($draft, $path, $file) {
            $draft->update([
                'invoice_file_path' => $path,
                'invoice_file_name' => $file->getClientOriginalName(),
                'invoice_uploaded_by' => auth()->id(),
                'invoice_uploaded_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });

        $rolePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'superadmin';

        return response()->json([
            'message' => 'File kuitansi bertanda tangan basah berhasil diupload.',
            'name' => $draft->invoice_file_name,
            'uploaded_at' => optional($draft->invoice_uploaded_at)->format('d M Y H:i'),
            'url' => route($rolePrefix . '.invoice.signed.show', $permohonan->id),
            'can_submit' => true,
        ]);
    }

    public function showSigned(Permohonan $permohonan)
    {
        $this->ensureAccess();
        return $this->showFile($permohonan->draftLhu?->invoice_file_path, $permohonan->draftLhu?->invoice_file_name);
    }
    // =========================================================================
    // [/PERCOBAAN KUITANSI TTD BASAH]
    // =========================================================================

    public function submitToBilling(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        if (empty($permohonan->draftLhu?->billing_verified_at)) {
            return response()->json(['message' => 'Pembayaran belum diverifikasi. Tahap kuitansi belum bisa diproses.'], 422);
        }

        // [PERCOBAAN KUITANSI TTD BASAH] - Wajibkan upload PDF scan kuitansi TTD basah
        $signedInvoicePath = $permohonan->draftLhu?->invoice_file_path;
        if (!$signedInvoicePath || !$this->fileExists($signedInvoicePath)) {
            return response()->json([
                'message' => 'Upload PDF kuitansi bertanda tangan basah terlebih dahulu sebelum meneruskan ke pelanggan.',
            ], 422);
        }

        // [PERCOBAAN KUITANSI TTD BASAH] - Baca opsi alur berikutnya (suket vs penyerahan_lhu)
        $validated = $request->validate([
            'next_step' => ['nullable', 'in:suket,penyerahan_lhu'],
        ]);

        $nextStepChoice = $validated['next_step'] ?? null;
        $toSuket = $nextStepChoice !== null
            ? ($nextStepChoice === 'suket')
            : $this->isSuketEnabled();
        // [/PERCOBAAN KUITANSI TTD BASAH]

        $invoiceStep = $this->ensureWorkflowStep('invoice', 'Kuitansi', 18);
        $suketStep = $this->ensureWorkflowStep('penerbitan_suket', 'Penerbitan Suket', 19);
        $penyerahanStep = $this->ensureWorkflowStep('penyerahan_lhu', 'Penyerahan LHU', 20);
        $targetUserIds = $this->resolveCustomerNotificationUserIds($permohonan);

        DB::transaction(function () use ($permohonan, $invoiceStep, $suketStep, $penyerahanStep, $targetUserIds, $toSuket) {
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
                    'status' => $toSuket ? 'pending' : 'approved',
                    'note' => $toSuket
                        ? 'Menunggu penerbitan surat keterangan'
                        : 'Tahap penerbitan surat keterangan dilewati (pemohon tidak memerlukan suket)',
                    'started_at' => now(),
                    'finished_at' => $toSuket ? null : now(),
                    'updated_by' => auth()->id(),
                ]
            );

            if (!$toSuket) {
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
                'status_global' => $toSuket ? 'penerbitan_suket' : 'penyerahan_lhu',
                'status_lab' => $toSuket ? 'penerbitan_suket' : 'penyerahan_lhu',
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
                ->each(function (User $user) use ($permohonan, $toSuket) {
                    Notifikasi::create([
                        'user_id' => $user->id,
                        'title' => 'Kuitansi Diteruskan',
                        'message' => 'Kuitansi permohonan ' . $permohonan->kode . ' sudah diteruskan ke pemohon. '
                            . ($toSuket
                                ? 'Lanjut ke tahap penerbitan suket.'
                                : 'Tahap penerbitan suket dilewati dan permohonan langsung masuk ke penyerahan LHU.'),
                        'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
                    ]);
                });
        });

        return response()->json([
            'message' => $toSuket
                ? 'Kuitansi berhasil diteruskan ke pelanggan. Permohonan lanjut ke tahap Penerbitan Suket.'
                : 'Kuitansi berhasil diteruskan ke pelanggan. Tahap Suket dilewati dan permohonan langsung masuk ke Penyerahan LHU.',
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

    private function showFile(?string $path, ?string $name)
    {
        if (!$path || !$this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $originalName = $name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $originalName . '"',
            ]);
        }

        return response()->download($fullPath, $originalName);
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
