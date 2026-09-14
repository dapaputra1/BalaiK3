<?php

namespace App\Http\Controllers;

use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class TtdLhuController extends Controller
{
    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

    public function index()
    {
        $this->ensureAccess();

        $ttdStep = $this->ensureWorkflowStep('ttd_lhu', 'TTD LHU', 15);

        $query = Permohonan::with(['company', 'draftLhu']);
        if ($ttdStep) {
            $query->whereHas('steps', function ($q) use ($ttdStep) {
                $q->where('step_id', $ttdStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            });
        } else {
            $query->where('status_global', 'ttd_lhu');
        }

        $permohonans = $query->oldest()->get();
        $orders = $permohonans->map(function (Permohonan $permohonan) {
            $draft = $permohonan->draftLhu;
            $company = $permohonan->company;

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'lokasi' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                'final_lhu_name' => $draft?->final_file_name ?: 'LHU QC',
                'final_lhu_url' => !empty($draft?->final_file_path) ? route('superadmin.ttd-lhu.final.show', $permohonan->id) : null,
                'signed_lhu_name' => $draft?->signed_file_name,
                'signed_lhu_url' => !empty($draft?->signed_file_path) ? route('superadmin.ttd-lhu.signed.show', $permohonan->id) : null,
                'signed_uploaded_at' => optional($draft?->signed_uploaded_at)->format('d M Y H:i'),
                'upload_url' => route('superadmin.ttd-lhu.upload', $permohonan->id),
                'submit_url' => route('superadmin.ttd-lhu.submit', $permohonan->id),
                'can_submit' => !empty($draft?->signed_file_path),
            ];
        });

        return view('admin.superadmin_ttd_lhu', [
            'orders' => $orders,
        ]);
    }

    public function uploadSigned(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        if (
            !Schema::hasTable('draft_lhus')
            || !Schema::hasColumn('draft_lhus', 'signed_file_path')
            || !Schema::hasColumn('draft_lhus', 'signed_file_name')
            || !Schema::hasColumn('draft_lhus', 'signed_uploaded_by')
            || !Schema::hasColumn('draft_lhus', 'signed_uploaded_at')
        ) {
            return response()->json([
                'message' => 'Struktur database LHU TTD belum tersedia. Jalankan migrate terbaru terlebih dahulu.',
            ], 500);
        }

        $data = $request->validate([
            'signed_lhu_file' => ['required', 'file', 'max:10240'],
        ], [
            'signed_lhu_file.required' => 'File LHU TTD wajib dipilih.',
            'signed_lhu_file.file' => 'File LHU TTD tidak valid.',
            'signed_lhu_file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $file = $data['signed_lhu_file'];
        SafeDocumentUpload::validateOrFail($file, 'signed_lhu_file');
        $ext = strtolower((string) $file->getClientOriginalExtension());

        $draft = DraftLhu::firstOrCreate(
            ['permohonan_id' => $permohonan->id],
            ['created_by' => auth()->id()]
        );

        $this->deleteIfExists($draft->signed_file_path);

        $folder = 'ttd-lhu/' . $permohonan->id;
        $filename = 'lhu_ttd_' . now()->format('Ymd_His') . '.' . $ext;
        $path = $file->storeAs($folder, $filename, self::PRIVATE_DISK);

        DB::transaction(function () use ($draft, $path, $file) {
            $draft->update([
                'signed_file_path' => $path,
                'signed_file_name' => $file->getClientOriginalName(),
                'signed_uploaded_by' => auth()->id(),
                'signed_uploaded_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });

        return response()->json([
            'message' => 'LHU TTD berhasil diupload. Klik tombol Lanjut ke Surat Tagihan untuk next step.',
            'name' => $draft->signed_file_name,
            'uploaded_at' => optional($draft->signed_uploaded_at)->format('d M Y H:i'),
            'url' => route('superadmin.ttd-lhu.signed.show', $permohonan->id),
        ]);
    }

    public function submitToSuratTagihan(Permohonan $permohonan)
    {
        $this->ensureAccess();

        $signedPath = $permohonan->draftLhu?->signed_file_path;
        if (!$signedPath || !$this->fileExists($signedPath)) {
            return response()->json(['message' => 'Upload LHU TTD terlebih dahulu sebelum lanjut ke Surat Tagihan.'], 422);
        }

        $ttdStep = $this->ensureWorkflowStep('ttd_lhu', 'TTD LHU', 15);
        $suratTagihanStep = $this->ensureWorkflowStep('surat_tagihan', 'Surat Tagihan', 16);

        DB::transaction(function () use ($permohonan, $ttdStep, $suratTagihanStep) {
            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $ttdStep->id],
                [
                    'status' => 'approved',
                    'note' => 'LHU sudah ditandatangani',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]
            );

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $suratTagihanStep->id],
                [
                    'status' => 'pending',
                    'note' => 'Menunggu pembuatan surat tagihan',
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]
            );

            $permohonan->update([
                'status_global' => 'surat_tagihan',
                'status_lab' => 'surat_tagihan',
            ]);
        });

        return response()->json(['message' => 'Permohonan berhasil dilanjutkan ke tahap Surat Tagihan.']);
    }

    public function showFinal(Permohonan $permohonan)
    {
        $this->ensureAccess();
        return $this->showFile($permohonan->draftLhu?->final_file_path, $permohonan->draftLhu?->final_file_name);
    }

    public function showSigned(Permohonan $permohonan)
    {
        $this->ensureAccess();
        return $this->showFile($permohonan->draftLhu?->signed_file_path, $permohonan->draftLhu?->signed_file_name);
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

    private function ensureAccess(): void
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'mp', 'mt'], true)) {
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
