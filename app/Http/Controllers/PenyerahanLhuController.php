<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSuketSetting;
use App\Models\DraftLhu;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenyerahanLhuController extends Controller
{
    use InteractsWithSuketSetting;

    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

    public function index()
    {
        $this->ensureAccess();

        $penyerahanStep = $this->ensureWorkflowStep('penyerahan_lhu', 'Penyerahan LHU', 20);
        $query = Permohonan::with(['company', 'draftLhu']);

        $query->where(function ($q) use ($penyerahanStep) {
            $q->whereHas('steps', function ($inner) use ($penyerahanStep) {
                $inner->where('step_id', $penyerahanStep->id)
                    ->whereIn('status', ['pending', 'in_progress', 'approved']);
            })->orWhere('status_global', 'penyerahan_lhu')
                ->orWhereHas('draftLhu', function ($inner) {
                    $inner->whereNotNull('lhu_user_revision_at')
                        ->whereNull('lhu_user_approved_at');
                });
        });

        $permohonans = $query->oldest()->get()->values();
        $rolePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'superadmin';
        $suketEnabled = $this->isSuketEnabled();

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($rolePrefix, $suketEnabled) {
            $company = $permohonan->company;
            $draft = $permohonan->draftLhu;
            $statusKey = $this->resolveStatusKey($draft);

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'alamat' => $company?->company_address ?? '-',
                'signed_lhu_name' => $draft?->signed_file_name ?: 'LHU TTD',
                'signed_lhu_url' => !empty($draft?->signed_file_path)
                    ? route($rolePrefix . '.penyerahan-lhu.signed.show', $permohonan->id)
                    : null,
                'suket_name' => $draft?->suket_file_name ?: 'Surat Keterangan',
                'suket_url' => !empty($draft?->suket_file_path)
                    ? route($rolePrefix . '.suket.show', $permohonan->id)
                    : null,
                'suket_uploaded_at' => optional($draft?->suket_uploaded_at)->format('d M Y H:i'),
                'suket_required' => $suketEnabled,
                'sent_to_user' => !empty($draft?->lhu_sent_to_user_at),
                'sent_at' => optional($draft?->lhu_sent_to_user_at)->format('d M Y H:i'),
                'revision_at' => optional($draft?->lhu_user_revision_at)->format('d M Y H:i'),
                'revision_note' => $draft?->lhu_user_revision_note,
                'approved_at' => optional($draft?->lhu_user_approved_at)->format('d M Y H:i'),
                'status_key' => $statusKey,
                'can_send_to_user' => $this->canSendToUser($draft),
                'upload_url' => route($rolePrefix . '.penyerahan-lhu.upload', $permohonan->id),
                'send_url' => route($rolePrefix . '.penyerahan-lhu.send', $permohonan->id),
            ];
        });

        return view('admin.superadmin_penyerahan_lhu', [
            'orders' => $orders,
            'routePrefix' => $rolePrefix,
        ]);
    }

    public function sendToUser(Permohonan $permohonan)
    {
        $this->ensureAccess();

        $draft = DraftLhu::firstOrCreate(
            ['permohonan_id' => $permohonan->id],
            ['created_by' => auth()->id()]
        );
        $suketEnabled = $this->isSuketEnabled();

        if (!$draft->signed_file_path || !$this->fileExists($draft->signed_file_path)) {
            return response()->json(['message' => 'Dokumen LHU TTD belum tersedia.'], 422);
        }
        if ($suketEnabled && (!$draft->suket_file_path || !$this->fileExists($draft->suket_file_path))) {
            return response()->json(['message' => 'Dokumen surat keterangan belum tersedia.'], 422);
        }
        if (!empty($draft->lhu_user_revision_at) && !$this->canSendToUser($draft)) {
            return response()->json(['message' => 'Upload dokumen LHU revisi terbaru terlebih dahulu sebelum kirim ulang.'], 422);
        }

        if (empty($draft->billing_verified_at)) {
            return response()->json(['message' => 'Pembayaran belum diverifikasi pada tahap kode billing.'], 422);
        }

        $penyerahanStep = $this->ensureWorkflowStep('penyerahan_lhu', 'Penyerahan LHU', 20);

        DB::transaction(function () use ($permohonan, $draft, $penyerahanStep, $suketEnabled) {
            $draft->update([
                'lhu_sent_to_user_by' => auth()->id(),
                'lhu_sent_to_user_at' => now(),
                'lhu_user_approved_by' => null,
                'lhu_user_approved_at' => null,
                'lhu_user_revision_at' => null,
                'lhu_user_revision_note' => null,
                'updated_by' => auth()->id(),
            ]);

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $penyerahanStep->id],
                [
                    'status' => 'in_progress',
                    'note' => 'LHU dikirim ke pemohon, menunggu konfirmasi',
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]
            );

            $permohonan->update([
                'status_global' => 'penyerahan_lhu',
                'status_lab' => 'penyerahan_lhu',
            ]);

            Notifikasi::create([
                'user_id' => $permohonan->user_id,
                'title' => $suketEnabled ? 'LHU dan Surat Keterangan Siap Ditinjau' : 'LHU Siap Ditinjau',
                'message' => $suketEnabled
                    ? 'LHU dan surat keterangan untuk permohonan ' . $permohonan->kode . ' sudah dikirim. Silakan lihat, lalu ACC atau ajukan revisi.'
                    : 'LHU untuk permohonan ' . $permohonan->kode . ' sudah dikirim. Silakan lihat, lalu ACC atau ajukan revisi.',
                'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
            ]);
        });

        return response()->json([
            'message' => $suketEnabled
                ? 'LHU dan surat keterangan berhasil diteruskan ke pemohon.'
                : 'LHU berhasil diteruskan ke pemohon. Tahap surat keterangan dilewati.',
        ]);
    }

    public function uploadRevised(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        $data = $request->validate([
            'signed_lhu_file' => ['required', 'file', 'max:10240'],
        ], [
            'signed_lhu_file.required' => 'File LHU wajib dipilih.',
            'signed_lhu_file.file' => 'File LHU tidak valid.',
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
        $filename = 'lhu_revisi_ttd_' . now()->format('Ymd_His') . '.' . $ext;
        $path = $file->storeAs($folder, $filename, self::PRIVATE_DISK);

        $draft->update([
            'signed_file_path' => $path,
            'signed_file_name' => $file->getClientOriginalName(),
            'signed_uploaded_by' => auth()->id(),
            'signed_uploaded_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $rolePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'superadmin';
        return response()->json([
            'message' => 'Dokumen LHU revisi berhasil diupload.',
            'name' => $draft->signed_file_name,
            'uploaded_at' => optional($draft->signed_uploaded_at)->format('d M Y H:i'),
            'url' => route($rolePrefix . '.penyerahan-lhu.signed.show', $permohonan->id),
        ]);
    }

    public function showSigned(Permohonan $permohonan)
    {
        $this->ensureAccess();
        $path = $permohonan->draftLhu?->signed_file_path;
        if (!$path || !$this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $name = $permohonan->draftLhu?->signed_file_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
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

    private function resolveStatusKey(?DraftLhu $draft): string
    {
        if (!$draft) {
            return 'belum_diserahkan';
        }
        if (!empty($draft->lhu_user_approved_at)) {
            return 'selesai';
        }
        if (!empty($draft->lhu_user_revision_at)) {
            return 'revisi';
        }
        if (empty($draft->lhu_sent_to_user_at)) {
            return 'belum_diserahkan';
        }
        return 'menunggu';
    }

    private function canSendToUser(?DraftLhu $draft): bool
    {
        if (!$draft || empty($draft->signed_file_path) || !$this->fileExists($draft->signed_file_path)) {
            return false;
        }

        if (empty($draft->lhu_user_revision_at)) {
            return true;
        }

        if (empty($draft->signed_uploaded_at)) {
            return false;
        }

        return $draft->signed_uploaded_at->greaterThan($draft->lhu_user_revision_at);
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
