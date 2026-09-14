<?php

namespace App\Http\Controllers;

use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QcLhuController extends Controller
{
    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

    public function index()
    {
        $this->ensureAccess();

        $qcStep = $this->ensureWorkflowStep('qc_lhu', 'QC LHU', 14);
        $this->repairStuckQcQueue($qcStep);

        $permohonanQuery = Permohonan::with([
            'company',
            'draftLhu',
        ]);

        if ($qcStep) {
            $permohonanQuery->whereHas('steps', function ($query) use ($qcStep) {
                $query->where('step_id', $qcStep->id)
                    ->whereIn('status', ['pending', 'in_progress', 'rejected', 'approved']);
            });
        } else {
            $permohonanQuery->where('status_global', 'qc_lhu');
        }

        $permohonans = $permohonanQuery->oldest()->get();
        $permohonanIds = $permohonans->pluck('id')->values();
        $qcSteps = collect();
        if ($permohonanIds->isNotEmpty()) {
            $qcSteps = PermohonanStep::query()
                ->whereIn('permohonan_id', $permohonanIds)
                ->where('step_id', $qcStep->id)
                ->get()
                ->keyBy('permohonan_id');
        }

        $rolePrefix = auth()->user()?->role === 'qc' ? 'qc' : 'superadmin';
        $orders = $permohonans->map(function (Permohonan $permohonan) use ($rolePrefix, $qcSteps) {
            $company = $permohonan->company;
            $draft = $permohonan->draftLhu;
            $qcState = $qcSteps->get($permohonan->id);
            $qcStatus = strtolower((string) ($qcState?->status ?? 'pending'));

            $statusFilter = 'belum_qc';
            $statusLabel = 'Belum di QC';
            $statusBadgeClass = 'text-bg-warning';
            if ($qcStatus === 'rejected') {
                $statusFilter = 'diajukan_revisi';
                $statusLabel = 'Diajukan Revisi';
                $statusBadgeClass = 'text-bg-danger';
            } elseif ($qcStatus === 'approved') {
                $statusFilter = 'selesai_qc';
                $statusLabel = 'Selesai QC';
                $statusBadgeClass = 'text-bg-success';
            }

            $note = trim((string) ($qcState?->note ?? ''));
            $revisiNote = null;
            if ($note !== '' && !in_array(strtolower($note), [
                'menunggu qc lhu',
                'draft lhu diteruskan ke qc lhu',
                'draft lhu diteruskan ulang ke qc lhu',
                'qc lhu disetujui',
            ], true)) {
                $revisiNote = $note;
            }

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'lokasi' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                'qc_status_filter' => $statusFilter,
                'qc_status_label' => $statusLabel,
                'qc_status_badge_class' => $statusBadgeClass,
                'revisi_note' => $revisiNote,
                'revisi_file_url' => !empty($draft?->qc_revision_file_path)
                    ? route($rolePrefix . '.qc-lhu.revision.show', $permohonan->id)
                    : null,
                'revisi_file_name' => $draft?->qc_revision_file_name,
                'revisi_file_uploaded_at' => optional($draft?->qc_revision_file_uploaded_at)->format('d M Y H:i'),
                'can_review' => in_array($qcStatus, ['pending', 'in_progress'], true),
                'final_lhu_name' => $draft?->final_file_name ?? '-',
                'final_lhu_url' => $draft?->final_file_path ? route($rolePrefix . '.qc-lhu.final.show', $permohonan->id) : null,
                'submit_url' => route($rolePrefix . '.qc-lhu.submit', $permohonan->id),
            ];
        });

        return view('admin.superadmin_qc_lhu', [
            'orders' => $orders,
        ]);
    }

    public function submit(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        $data = $request->validate([
            'action' => ['required', 'in:approve,revisi'],
            'note' => ['nullable', 'string', 'max:2000'],
            'revision_file' => ['nullable', 'file', 'max:10240'],
        ]);

        if ($data['action'] === 'revisi' && empty(trim((string) ($data['note'] ?? '')))) {
            return response()->json(['message' => 'Catatan revisi wajib diisi.'], 422);
        }

        $qcStep = $this->ensureWorkflowStep('qc_lhu', 'QC LHU', 14);
        $draftStep = $this->ensureWorkflowStep('pembuatan_lhu', 'Draft LHU', 13);
        $ttdStep = $this->ensureWorkflowStep('ttd_lhu', 'TTD LHU', 15);

        DB::transaction(function () use ($permohonan, $data, $qcStep, $draftStep, $ttdStep, $request) {
            if ($data['action'] === 'approve') {
                $draft = DraftLhu::firstWhere('permohonan_id', $permohonan->id);
                $this->deleteIfExists($draft?->qc_revision_file_path);
                if ($draft) {
                    $draft->update([
                        'qc_revision_file_path' => null,
                        'qc_revision_file_name' => null,
                        'qc_revision_file_uploaded_by' => null,
                        'qc_revision_file_uploaded_at' => null,
                        'updated_by' => auth()->id(),
                    ]);
                }

                PermohonanStep::where('permohonan_id', $permohonan->id)
                    ->where('step_id', $qcStep->id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->update([
                        'status' => 'approved',
                        'note' => 'QC LHU disetujui',
                        'finished_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);

                PermohonanStep::firstOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $ttdStep->id],
                    ['status' => 'pending', 'started_at' => now()]
                );

                $permohonan->update([
                    'status_global' => 'ttd_lhu',
                    'status_lab' => 'ttd_lhu',
                ]);
                return;
            }

            $note = trim((string) ($data['note'] ?? ''));
            $draft = DraftLhu::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                ['created_by' => auth()->id()]
            );

            if ($request->hasFile('revision_file')) {
                $file = $request->file('revision_file');
                SafeDocumentUpload::validateOrFail($file, 'revision_file');
                $this->deleteIfExists($draft->qc_revision_file_path);

                $ext = strtolower((string) $file->getClientOriginalExtension());
                $folder = 'qc-lhu-revision/' . $permohonan->id;
                $filename = 'catatan_revisi_qc_' . now()->format('Ymd_His') . '.' . $ext;
                $path = $file->storeAs($folder, $filename, self::PRIVATE_DISK);

                $draft->update([
                    'qc_revision_file_path' => $path,
                    'qc_revision_file_name' => $file->getClientOriginalName(),
                    'qc_revision_file_uploaded_by' => auth()->id(),
                    'qc_revision_file_uploaded_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
            }

            PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $qcStep->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->update([
                    'status' => 'rejected',
                    'note' => $note,
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]);

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $draftStep->id],
                [
                    'status' => 'pending',
                    'note' => 'Revisi QC LHU: ' . $note,
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]
            );

            $permohonan->update([
                'status_global' => 'pembuatan_lhu',
                'status_lab' => 'pembuatan_lhu',
            ]);
        });

        if ($data['action'] === 'approve') {
            return response()->json(['message' => 'QC LHU disetujui, diteruskan ke TTD LHU.']);
        }

        return response()->json(['message' => 'Revisi diajukan, permohonan dikembalikan ke Draft LHU.']);
    }

    public function showFinal(Permohonan $permohonan)
    {
        $this->ensureAccess();

        $path = $permohonan->draftLhu?->final_file_path;
        if (!$path || !$this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $originalName = $permohonan->draftLhu?->final_file_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $originalName . '"',
            ]);
        }

        return response()->download($fullPath, $originalName);
    }

    public function showRevision(Permohonan $permohonan)
    {
        $this->ensureAccess();

        $path = $permohonan->draftLhu?->qc_revision_file_path;
        if (!$path || !$this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }

        $fullPath = Storage::disk($disk)->path($path);
        $originalName = $permohonan->draftLhu?->qc_revision_file_name ?: basename($path);
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
        if (!in_array($role, ['qc', 'superadmin'], true)) {
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

    private function repairStuckQcQueue(WorkflowStep $qcStep): void
    {
        $stuckIds = Permohonan::query()
            ->where('status_global', 'qc_lhu')
            ->whereDoesntHave('steps', function ($query) use ($qcStep) {
                $query->where('step_id', $qcStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })
            ->pluck('id');

        if ($stuckIds->isEmpty()) {
            return;
        }

        foreach ($stuckIds as $permohonanId) {
            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonanId, 'step_id' => $qcStep->id],
                [
                    'status' => 'pending',
                    'note' => 'Perbaikan otomatis antrean QC LHU',
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]
            );
        }
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
