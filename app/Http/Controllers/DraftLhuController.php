<?php

namespace App\Http\Controllers;

use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Services\Ai\SumopodLhuService;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class DraftLhuController extends Controller
{
    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

    public function index()
    {
        $this->ensureAccess();

        $draftStep = WorkflowStep::where('kode', 'pembuatan_lhu')->first();
        $verifStep = WorkflowStep::where('kode', 'verifikasi')->first();
        $qcStep = WorkflowStep::where('kode', 'qc_lhu')->first();

        $baseQuery = Permohonan::with([
            'company',
            'pengujian.lokasi.dokumen.files',
            'pengujian.lokasi.dokumen.parameters.serviceParameter',
            'prepanalisa.items',
            'bap.items',
            'draftLhu',
        ]);

        if (auth()->user()?->role === 'pcu') {
            $userId = (int) auth()->id();
            $baseQuery->whereHas('assignments', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('role', 'pcu');
            });
        }

        $permohonans = collect();
        if ($draftStep) {
            $permohonans = $baseQuery->clone()
                ->whereHas('steps', function ($query) use ($draftStep) {
                    $query->where('step_id', $draftStep->id)
                        ->whereIn('status', ['pending', 'in_progress']);
                })
                ->oldest()
                ->get();
        } else {
            $permohonans = $baseQuery->clone()
                ->where('status_global', 'pembuatan_lhu')
                ->oldest()
                ->get();
        }

        // Fallback: tampilkan permohonan dengan direct meski masih di preparasi/verifikasi.
        $extra = $baseQuery->clone()
            ->whereIn('status_global', ['preparasi_analisa', 'verifikasi', 'pembuatan_lhu'])
            ->oldest()
            ->get()
            ->filter(function (Permohonan $permohonan) {
                if ($permohonan->status_global === 'pembuatan_lhu') {
                    return true;
                }
                return $this->hasDirectParameters($permohonan);
            });
        if ($extra->isNotEmpty()) {
            $permohonans = $permohonans->merge($extra)->unique('id')->sortBy('created_at')->values();
        }

        $permohonanIds = $permohonans->pluck('id')->values();

        $verifApprovedIds = collect();
        if ($verifStep && $permohonanIds->isNotEmpty()) {
            $verifApprovedIds = PermohonanStep::whereIn('permohonan_id', $permohonanIds)
                ->where('step_id', $verifStep->id)
                ->where('status', 'approved')
                ->pluck('permohonan_id')
                ->values();
        }

        $draftMasukAtByPermohonan = collect();
        if ($draftStep && $permohonanIds->isNotEmpty()) {
            $draftMasukAtByPermohonan = PermohonanStep::whereIn('permohonan_id', $permohonanIds)
                ->where('step_id', $draftStep->id)
                ->get()
                ->keyBy('permohonan_id')
                ->map(function (PermohonanStep $step) {
                    return optional($step->started_at ?? $step->updated_at ?? $step->created_at)->timestamp;
                });
        }
        $routePrefix = $this->getRoutePrefix();

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($verifApprovedIds, $routePrefix, $qcStep, $draftMasukAtByPermohonan) {
            $company = $permohonan->company;
            $hasIndirect = $this->hasIndirectParameters($permohonan);
            $indirectDone = !$hasIndirect || $verifApprovedIds->contains($permohonan->id);
            $catalog = $this->buildSelectionCatalog($permohonan);
            $detailRows = $this->buildDetailRows($permohonan, true, $catalog);
            $documents = $this->resolveDocumentPayloads($permohonan, $catalog);
            $indirectRows = $this->buildIndirectRowsBySelection($permohonan, $catalog);
            $revisiNotes = collect();
            if ($qcStep) {
                $revisiNotes = PermohonanStep::where('permohonan_id', $permohonan->id)
                    ->where('step_id', $qcStep->id)
                    ->where('status', 'rejected')
                    ->whereNotNull('note')
                    ->orderByDesc('updated_at')
                    ->pluck('note')
                    ->filter()
                    ->unique()
                    ->values();
            }
            if (!$indirectDone) {
                $documents = array_map(function (array $doc) {
                    $doc['indirect_table'] = ['columns' => [], 'rows' => []];
                    return $doc;
                }, $documents);
            }

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'masuk_at_unix' => (int) ($draftMasukAtByPermohonan->get($permohonan->id) ?? optional($permohonan->created_at)->timestamp ?? 0),
                'lokasi' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                'has_indirect' => $hasIndirect,
                'indirect_done' => $indirectDone,
                'locked' => $hasIndirect && !$indirectDone,
                'save_url' => route($routePrefix . '.draft-lhu.draft', $permohonan->id),
                'word_url' => route($routePrefix . '.draft-lhu.word', $permohonan->id),
                'word_all_url' => route($routePrefix . '.draft-lhu.word-all', $permohonan->id),
                'ai_summary_url' => route($routePrefix . '.draft-lhu.ai-summary', $permohonan->id),
                'final_lhu_upload_url' => route($routePrefix . '.draft-lhu.final.upload', $permohonan->id),
                'final_lhu_show_url' => route($routePrefix . '.draft-lhu.final.show', $permohonan->id),
                'final_lhu_name' => $permohonan->draftLhu?->final_file_name,
                'final_lhu_uploaded_at' => optional($permohonan->draftLhu?->final_uploaded_at)->format('d M Y H:i'),
                'final_lhu_ready' => !empty($permohonan->draftLhu?->final_file_path),
                'revisi_notes' => $revisiNotes->all(),
                'qc_revisi_file_url' => !empty($permohonan->draftLhu?->qc_revision_file_path)
                    ? route($routePrefix . '.draft-lhu.qc-revision.show', $permohonan->id)
                    : null,
                'qc_revisi_file_name' => $permohonan->draftLhu?->qc_revision_file_name,
                'qc_revisi_file_uploaded_at' => optional($permohonan->draftLhu?->qc_revision_file_uploaded_at)->format('d M Y H:i'),
                'documents' => $documents,
                'selection_catalog' => $catalog,
                'indirect_rows' => $indirectRows,
                'detail_rows' => $detailRows,
            ];
        });

        return view('admin.superadmin_draft_lhu', [
            'orders' => $orders,
            'routePrefix' => $routePrefix,
        ]);
    }

    public function saveDraft(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);
        if (!$this->isIndirectReady($permohonan)) {
            return response()->json(['message' => 'Indirect belum selesai diverifikasi.'], 422);
        }

        $payload = $this->parsePayload($request);
        if ($payload === null) {
            return response()->json(['message' => 'Payload draft LHU tidak valid.'], 422);
        }

        $catalog = $this->buildSelectionCatalog($permohonan);
        $current = (array) (DraftLhu::where('permohonan_id', $permohonan->id)->value('tables') ?? []);
        $storedDocs = array_values((array) data_get($current, '__documents', []));
        $existingDocs = [];
        foreach ($storedDocs as $doc) {
            if (is_array($doc) && !empty($doc['id'])) {
                $existingDocs[(string) $doc['id']] = (int) ($doc['type'] ?? 0);
            }
        }

        $documents = [];
        try {
            if (isset($payload['document']) && is_array($payload['document'])) {
                $rawDoc = $payload['document'];
                $docId = (string) ($rawDoc['id'] ?? '');
                $sanitized = $this->sanitizeDocumentPayload($rawDoc, $catalog, $existingDocs[$docId] ?? null);
                $replaced = false;
                foreach ($storedDocs as $index => $oldDoc) {
                    if ((string) data_get($oldDoc, 'id', '') === (string) $sanitized['id']) {
                        $storedDocs[$index] = $sanitized;
                        $replaced = true;
                        break;
                    }
                }
                if (!$replaced) {
                    $storedDocs[] = $sanitized;
                }
                foreach ($storedDocs as $mergedDoc) {
                    if (!is_array($mergedDoc)) {
                        continue;
                    }
                    $mergedId = (string) ($mergedDoc['id'] ?? '');
                    $documents[] = $this->sanitizeDocumentPayload($mergedDoc, $catalog, $existingDocs[$mergedId] ?? null);
                }
            } else {
                foreach (($payload['documents'] ?? []) as $rawDoc) {
                    if (!is_array($rawDoc)) {
                        continue;
                    }
                    $docId = (string) ($rawDoc['id'] ?? '');
                    $documents[] = $this->sanitizeDocumentPayload($rawDoc, $catalog, $existingDocs[$docId] ?? null);
                }
            }
            $this->validateParameterUsageQuota($documents, $catalog);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $tables = $current;
        $tables['__documents'] = array_values($documents);

        DraftLhu::updateOrCreate(
            ['permohonan_id' => $permohonan->id],
            [
                'tables' => $tables,
                'updated_by' => auth()->id(),
                'created_by' => auth()->id(),
            ]
        );

        $indirectRows = $this->buildIndirectRowsBySelection($permohonan, $catalog);
        $savedDocs = array_map(function (array $doc) use ($indirectRows) {
            $doc['indirect_table'] = $this->buildIndirectTableByDocument($doc, $indirectRows);
            return $doc;
        }, array_values($documents));

        return response()->json([
            'message' => 'Draft LHU berhasil disimpan.',
            'documents' => $savedDocs,
        ]);
    }

    public function generateAiSummary(Request $request, Permohonan $permohonan, SumopodLhuService $sumopodLhuService)
    {
        $this->ensureAccess($permohonan);
        if (!$this->isIndirectReady($permohonan)) {
            return response()->json(['message' => 'Indirect belum selesai diverifikasi.'], 422);
        }

        $data = $request->validate([
            'document_id' => ['required', 'string'],
        ], [
            'document_id.required' => 'Dokumen LHU wajib dipilih.',
        ]);

        $documentId = trim((string) $data['document_id']);
        $permohonan->loadMissing([
            'company',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'prepanalisa.items',
            'draftLhu',
        ]);

        $catalog = $this->buildSelectionCatalog($permohonan);
        $documents = $this->resolveDocumentPayloads($permohonan, $catalog);
        $docIndex = collect($documents)->search(fn ($doc) => (string) ($doc['id'] ?? '') === $documentId);
        if ($docIndex === false) {
            return response()->json(['message' => 'Dokumen LHU tidak ditemukan.'], 404);
        }
        $document = $documents[$docIndex];

        $indirectRows = $this->buildIndirectRowsBySelection($permohonan, $catalog);
        $indirectTable = $this->buildIndirectTableByDocument($document, $indirectRows);
        $lokasiLabel = implode(', ', array_map(function ($id) use ($catalog) {
            return (string) data_get(
                collect((array) data_get($catalog, 'locations', []))->firstWhere('id', (int) $id),
                'name',
                '-'
            );
        }, (array) ($document['lokasi_ids'] ?? [])));

        $context = [
            'permohonan' => [
                'kode' => (string) ($permohonan->kode ?? '-'),
                'perusahaan' => (string) ($permohonan->company?->company_name ?? '-'),
                'lokasi_pengujian' => $lokasiLabel !== '' ? $lokasiLabel : '-',
                'alamat' => (string) ($permohonan->jadwal_lokasi ?: ($permohonan->company?->company_city ?? '-')),
                'tanggal_pengujian' => $permohonan->jadwal_mulai
                    ? Carbon::parse($permohonan->jadwal_mulai)->format('Y-m-d')
                    : null,
            ],
            'direct_table' => data_get($document, 'table', ['columns' => [], 'rows' => []]),
            'indirect_table' => $indirectTable,
            'catatan_nab' => 'Gunakan NAB yang sudah diisi pada tabel jika tersedia. Hanya gunakan estimasi AI non-resmi bila kolom NAB kosong.',
        ];

        try {
            $result = $sumopodLhuService->generateKesimpulanSaran($context);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Generate kesimpulan/saran AI gagal: ' . $e->getMessage(),
            ], 502);
        }

        $draft = DraftLhu::firstOrCreate(
            ['permohonan_id' => $permohonan->id],
            ['created_by' => auth()->id()]
        );
        $tables = (array) ($draft->tables ?? []);
        $documents[$docIndex]['ai'] = $this->sanitizeAiPayload([
            'kesimpulan' => $result['kesimpulan'],
            'saran' => $result['saran'],
            'model' => $result['model'] ?? config('services.sumopod.model'),
            'generated_at' => now()->toIso8601String(),
        ]);
        $tables['__documents'] = array_values($documents);

        $draft->update([
            'tables' => $tables,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Kesimpulan dan saran AI berhasil dibuat.',
            'data' => $documents[$docIndex]['ai'],
        ]);
    }

    public function uploadFinal(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        $data = $request->validate([
            'final_lhu_file' => ['required', 'file', 'max:10240'],
        ], [
            'final_lhu_file.required' => 'File LHU jadi wajib dipilih.',
            'final_lhu_file.file' => 'File LHU jadi tidak valid.',
            'final_lhu_file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $file = $data['final_lhu_file'];
        SafeDocumentUpload::validateOrFail($file, 'final_lhu_file');
        $ext = strtolower((string) $file->getClientOriginalExtension());
        $draft = DraftLhu::firstOrCreate(
            ['permohonan_id' => $permohonan->id],
            ['created_by' => auth()->id()]
        );

        $this->deleteIfExists($draft->final_file_path);

        $folder = 'draft-lhu-final/' . $permohonan->id;
        $filename = 'lhu_jadi_' . now()->format('Ymd_His') . '.' . $ext;
        $path = $file->storeAs($folder, $filename, self::PRIVATE_DISK);

        $draft->update([
            'final_file_path' => $path,
            'final_file_name' => $file->getClientOriginalName(),
            'final_uploaded_by' => auth()->id(),
            'final_uploaded_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $rolePrefix = $this->getRoutePrefix();

        return response()->json([
            'message' => 'LHU jadi berhasil diupload.',
            'name' => $draft->final_file_name,
            'uploaded_at' => optional($draft->final_uploaded_at)->format('d M Y H:i'),
            'url' => route($rolePrefix . '.draft-lhu.final.show', $permohonan->id),
        ]);
    }

    public function showFinal(Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        $draft = $permohonan->draftLhu;
        $path = $draft?->final_file_path;
        if (!$path || !$this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }
        $fullPath = Storage::disk($disk)->path($path);
        $originalName = $draft->final_file_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $originalName . '"',
            ]);
        }

        return response()->download($fullPath, $originalName);
    }

    public function downloadWord(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);
        if (!$this->isIndirectReady($permohonan)) {
            return response()->json(['message' => 'Indirect belum selesai diverifikasi.'], 422);
        }

        $documentId = trim((string) $request->query('document_id'));
        if ($documentId === '') {
            return response()->json(['message' => 'Dokumen LHU tidak valid.'], 422);
        }

        $permohonan->loadMissing([
            'company',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'prepanalisa.items',
            'draftLhu',
        ]);

        $catalog = $this->buildSelectionCatalog($permohonan);
        $documents = $this->resolveDocumentPayloads($permohonan, $catalog);
        $document = collect($documents)->firstWhere('id', $documentId);
        if (!$document) {
            return response()->json(['message' => 'Dokumen LHU tidak ditemukan.'], 404);
        }

        $indirectRows = $this->buildIndirectRowsBySelection($permohonan, $catalog);
        $indirectTable = $this->buildIndirectTableByDocument($document, $indirectRows);
        $logoAsset = $this->resolveWordHeaderLogoAsset();
        $content = $this->buildWordHtmlByDocument($permohonan, $document, $catalog, $indirectTable, $logoAsset);

        $safeTitle = preg_replace('/[^A-Za-z0-9\-]+/', '-', (string) ($document['title'] ?? 'Dokumen-LHU'));
        $safeTitle = trim((string) $safeTitle, '-');
        if ($safeTitle === '') {
            $safeTitle = 'Dokumen-LHU';
        }
        $filename = $safeTitle . '.doc';

        if ($logoAsset) {
            [$mhtml, $boundary] = $this->buildWordMhtml($content, $logoAsset);

            return response($mhtml, 200, [
                'Content-Type' => 'multipart/related; boundary="' . $boundary . '"',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return response("\xEF\xBB\xBF" . $content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function downloadAllWord(Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);
        if (!$this->isIndirectReady($permohonan)) {
            return response()->json(['message' => 'Indirect belum selesai diverifikasi.'], 422);
        }

        $permohonan->loadMissing([
            'company',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'prepanalisa.items',
            'draftLhu',
        ]);

        $catalog = $this->buildSelectionCatalog($permohonan);
        $documents = $this->resolveDocumentPayloads($permohonan, $catalog);
        if (empty($documents)) {
            return response()->json(['message' => 'Belum ada dokumen LHU untuk digabungkan.'], 422);
        }

        $indirectRows = $this->buildIndirectRowsBySelection($permohonan, $catalog);
        $logoAsset = $this->resolveWordHeaderLogoAsset();
        $content = $this->buildWordHtmlByDocuments($permohonan, $documents, $catalog, $indirectRows, $logoAsset);

        $safeCode = preg_replace('/[^A-Za-z0-9\-]+/', '-', (string) ($permohonan->kode ?? 'LHU-Gabungan'));
        $safeCode = trim((string) $safeCode, '-');
        if ($safeCode === '') {
            $safeCode = 'LHU-Gabungan';
        }
        $filename = $safeCode . '-gabungan.doc';

        if ($logoAsset) {
            [$mhtml, $boundary] = $this->buildWordMhtml($content, $logoAsset);

            return response($mhtml, 200, [
                'Content-Type' => 'multipart/related; boundary="' . $boundary . '"',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return response("\xEF\xBB\xBF" . $content, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function submitToQc(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        if (!$this->isIndirectReady($permohonan)) {
            return response()->json(['message' => 'Indirect belum selesai diverifikasi.'], 422);
        }

        $draft = DraftLhu::where('permohonan_id', $permohonan->id)->first();
        $finalPath = $draft?->final_file_path;
        if (!$finalPath || !$this->fileExists($finalPath)) {
            return response()->json(['message' => 'Upload LHU jadi terlebih dahulu sebelum lanjut ke QC LHU.'], 422);
        }

        $draftStep = $this->ensureWorkflowStep('pembuatan_lhu', 'Draft LHU', 13);
        $qcStep = $this->ensureWorkflowStep('qc_lhu', 'QC LHU', 14);

        DB::transaction(function () use ($permohonan, $draftStep, $qcStep) {
            PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $draftStep->id)
                ->update([
                    'status' => 'approved',
                    'note' => 'Draft LHU diteruskan ke QC LHU',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]);

            $existingQcStep = PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $qcStep->id)
                ->first();

            $noteToKeep = trim((string) ($existingQcStep?->note ?? ''));
            if ($noteToKeep === '' || in_array(strtolower($noteToKeep), [
                'draft lhu diteruskan ke qc lhu',
                'draft lhu diteruskan ulang ke qc lhu',
                'menunggu qc lhu',
                'qc lhu disetujui',
            ], true)) {
                $noteToKeep = 'Menunggu QC LHU';
            }

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $qcStep->id],
                [
                    'status' => 'pending',
                    'note' => $noteToKeep,
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]
            );

            $permohonan->update([
                'status_global' => 'qc_lhu',
                'status_lab' => 'qc_lhu',
            ]);
        });

        return response()->json(['message' => 'Draft LHU diteruskan ke QC LHU.']);
    }

    private function hasIndirectParameters(Permohonan $permohonan): bool
    {
        $permohonan->loadMissing('pengujian.lokasi.dokumen.parameters');
        $lokasi = $permohonan->pengujian?->lokasi ?? collect();
        return $lokasi->flatMap(fn ($loc) => $loc->dokumen)
            ->flatMap(fn ($doc) => $doc->parameters)
            ->contains(fn ($param) => !$param->is_direct);
    }

    private function hasDirectParameters(Permohonan $permohonan): bool
    {
        $permohonan->loadMissing('pengujian.lokasi.dokumen.parameters');
        $lokasi = $permohonan->pengujian?->lokasi ?? collect();
        return $lokasi->flatMap(fn ($loc) => $loc->dokumen)
            ->flatMap(fn ($doc) => $doc->parameters)
            ->contains(fn ($param) => (bool) $param->is_direct);
    }

    private function ensureAccess(?Permohonan $permohonan = null): void
    {
        $user = auth()->user();
        $role = $user?->role;
        if (!in_array($role, ['admin', 'superadmin', 'pcu'], true)) {
            abort(403);
        }

        if ($role !== 'pcu' || !$permohonan) {
            return;
        }

        $hasAccess = $permohonan->assignments()
            ->where('user_id', $user->id)
            ->where('role', 'pcu')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak terdaftar sebagai PCU untuk permohonan ini.');
        }
    }

    private function getRoutePrefix(): string
    {
        return match (auth()->user()?->role) {
            'admin' => 'admin',
            'pcu' => 'pcu',
            default => 'superadmin',
        };
    }

    private function buildLocationTables(Permohonan $permohonan): array
    {
        $permohonan->loadMissing([
            'pengujian.lokasi.dokumen.files',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'prepanalisa.items',
            'draftLhu',
        ]);

        $locations = [];
        foreach (($permohonan->pengujian?->lokasi ?? collect())->sortBy('urutan') as $lokasi) {
            $lokasiId = (int) $lokasi->id;
            if (!$lokasiId) {
                continue;
            }
            $params = collect($lokasi->dokumen ?? [])
                ->flatMap(function ($dokumen) {
                    return collect($dokumen->parameters ?? []);
                });
            $categories = collect($lokasi->dokumen ?? [])
                ->flatMap(function ($dokumen) {
                    return collect($dokumen->parameters ?? [])->map(function ($param) {
                        return trim((string) ($param->serviceParameter?->category?->name ?? ''));
                    });
                })
                ->filter(function ($name) {
                    return $name !== '';
                })
                ->unique()
                ->values()
                ->all();

            $locations[] = [
                'id' => $lokasiId,
                'name' => $lokasi->nama_lokasi ?? ('Lokasi ' . $lokasiId),
                'categories' => $categories,
                'has_direct' => $params
                    ->contains(function ($param) {
                        return (bool) $param->is_direct;
                    }),
                'has_indirect' => $params
                    ->contains(function ($param) {
                        return !(bool) $param->is_direct;
                    }),
                'payload' => $this->resolveLocationPayload($permohonan, $lokasiId),
                'indirect_table' => $this->buildIndirectAutoTable($permohonan, $lokasiId),
            ];
        }

        return $locations;
    }

    private function buildDetailRows(Permohonan $permohonan, bool $includeIndirect = true, array $catalog = []): array
    {
        $permohonan->loadMissing([
            'pengujian.lokasi.dokumen.files',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'prepanalisa.items',
            'bap.items',
        ]);

        $rows = [];
        $preMap = ($permohonan->prepanalisa?->items ?? collect())->keyBy('pengujian_dokumen_parameter_id');
        $bapReviewMap = ($permohonan->bap?->items ?? collect())->keyBy('pengujian_dokumen_parameter_id');
        $canonicalByLokasiId = (array) data_get($catalog, 'canonical_by_lokasi_id', []);
        $lokasiList = ($permohonan->pengujian?->lokasi ?? collect())->sortBy('urutan')->values();
        $docFilesByLabel = [];

        // Samakan fallback dokumen seperti BAP/Verifikasi Pengujian:
        // jika dokumen di lokasi tertentu kosong, ambil file berdasarkan label + metode.
        $lokasiList->each(function ($lokasi) use (&$docFilesByLabel) {
            $lokasi->dokumen->each(function ($dokumen) use (&$docFilesByLabel) {
                $label = trim((string) $dokumen->label);
                if ($label === '') {
                    $label = 'Dokumen';
                }
                $key = strtolower($label);
                if (!isset($docFilesByLabel[$key])) {
                    $docFilesByLabel[$key] = [
                        'direct' => collect(),
                        'indirect' => collect(),
                    ];
                }

                $hasDirect = $dokumen->parameters->contains(function ($param) {
                    return (bool) $param->is_direct;
                });

                $files = $dokumen->files->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->original_name ?? 'File',
                        'url' => $file->file_path ? route('pengujian.files.show', $file->id) : null,
                    ];
                });

                $bucket = $hasDirect ? 'direct' : 'indirect';
                $docFilesByLabel[$key][$bucket] = $docFilesByLabel[$key][$bucket]->merge($files);
            });
        });

        foreach ($lokasiList as $lokasi) {
            foreach (($lokasi->dokumen ?? collect())->sortBy('urutan') as $dokumen) {
                $docLabel = trim((string) ($dokumen->label ?? 'Dokumen'));
                if ($docLabel === '') {
                    $docLabel = 'Dokumen';
                }
                $labelKey = strtolower($docLabel);

                $baseFiles = ($dokumen->files ?? collect())->map(function ($file) {
                    return [
                        'id' => $file->id,
                        'name' => $file->original_name ?? 'File',
                        'url' => $file->file_path ? route('pengujian.files.show', $file->id) : null,
                    ];
                });

                foreach (($dokumen->parameters ?? collect()) as $docParam) {
                    if (!$includeIndirect && !(bool) $docParam->is_direct) {
                        continue;
                    }
                    $service = $docParam->serviceParameter;
                    $preItem = $preMap->get($docParam->id);
                    $bapReview = $bapReviewMap->get($docParam->id);
                    $isNotOk = ($preItem?->verif_status ?? '') === 'revisi';
                    $methodKey = (bool) $docParam->is_direct ? 'direct' : 'indirect';
                    $isDirect = (bool) $docParam->is_direct;
                    $reviewStatus = trim((string) ($isDirect ? ($bapReview?->review_status ?? '') : ($preItem?->verif_status ?? '')));
                    $reviewNote = trim((string) ($isDirect ? ($bapReview?->catatan ?? '') : ($preItem?->verif_note ?? '')));

                    $docFiles = $baseFiles;
                    if ($docFiles->isEmpty() && $labelKey !== '' && isset($docFilesByLabel[$labelKey])) {
                        $bucketFiles = $docFilesByLabel[$labelKey][$methodKey] ?? collect();
                        if ($bucketFiles->isEmpty()) {
                            $bucketFiles = ($docFilesByLabel[$labelKey]['direct'] ?? collect())
                                ->merge($docFilesByLabel[$labelKey]['indirect'] ?? collect());
                        }
                        $docFiles = $bucketFiles;
                    }
                    $docFiles = $docFiles->unique(function ($file) {
                        return $file['id'] ?? ($file['url'] ?? $file['name'] ?? '');
                    })->values()->all();

                    $rows[] = [
                        'lokasi_id' => (int) ($lokasi->id ?? 0),
                        'canonical_lokasi_id' => (int) ($canonicalByLokasiId[(int) ($lokasi->id ?? 0)] ?? ((int) ($lokasi->id ?? 0))),
                        'lokasi' => (string) ($lokasi->nama_lokasi ?? '-'),
                        'dokumen_id' => (int) ($dokumen->id ?? 0),
                        'dokumen_label' => $docLabel,
                        'dokumen_files' => $docFiles,
                        'parameter_id' => (int) ($docParam->service_parameter_id ?? 0),
                        'parameter' => (string) ($service?->name ?? '-'),
                        'metode' => $isDirect ? 'Direct' : 'Indirect',
                        'jumlah' => 1,
                        'sesuai' => $isNotOk ? 'Tidak' : 'Sesuai',
                        'catatan' => $reviewNote,
                        'review_status' => $reviewStatus,
                    ];
                }
            }
        }

        return $rows;
    }

    private function resolveLocationPayload(Permohonan $permohonan, int $lokasiId): array
    {
        $stored = data_get($permohonan->draftLhu?->tables, (string) $lokasiId);
        return $this->sanitizeLocationPayload($stored, $this->buildDefaultLocationTable($permohonan, $lokasiId));
    }

    private function buildDefaultLocationTable(Permohonan $permohonan, int $lokasiId): array
    {
        $columns = ['Parameter', 'Hasil Pengukuran'];

        $lokasi = $permohonan->pengujian?->lokasi?->firstWhere('id', $lokasiId);
        $rows = [];
        foreach (($lokasi?->dokumen ?? collect())->sortBy('urutan') as $dokumen) {
            foreach (($dokumen->parameters ?? collect()) as $docParam) {
                if (!(bool) $docParam->is_direct) {
                    continue;
                }

                $service = $docParam->serviceParameter;

                $rows[] = [
                    (string) ($service?->name ?? '-'),
                    '',
                ];
            }
        }

        if (empty($rows)) {
            $rows[] = array_fill(0, count($columns), '');
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    private function buildIndirectAutoTable(Permohonan $permohonan, int $lokasiId): array
    {
        $columns = ['Parameter', 'Hasil Analisa'];
        $preMap = ($permohonan->prepanalisa?->items ?? collect())->keyBy('pengujian_dokumen_parameter_id');

        $lokasi = $permohonan->pengujian?->lokasi?->firstWhere('id', $lokasiId);
        $rows = [];
        foreach (($lokasi?->dokumen ?? collect())->sortBy('urutan') as $dokumen) {
            foreach (($dokumen->parameters ?? collect()) as $docParam) {
                if ((bool) $docParam->is_direct) {
                    continue;
                }

                $service = $docParam->serviceParameter;
                $preItem = $preMap->get($docParam->id);

                $hasil = $this->formatIndirectResult($preItem?->data_hasil_perhitungan, $preItem?->data_hasil_baca);

                $rows[] = [
                    (string) ($service?->name ?? '-'),
                    $hasil,
                ];
            }
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    private function buildSelectionCatalog(Permohonan $permohonan): array
    {
        $permohonan->loadMissing([
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
        ]);

        $locations = [];
        $parameters = [];
        $locationParamMap = [];
        $locationParamMethodMap = [];
        $canonicalByName = [];
        $canonicalByLokasiId = [];
        $aliasMap = [];
        foreach (($permohonan->pengujian?->lokasi ?? collect())->sortBy('urutan') as $lokasi) {
            $lokasiId = (int) $lokasi->id;
            if ($lokasiId <= 0) {
                continue;
            }
            $lokasiName = trim((string) ($lokasi->nama_lokasi ?? ''));
            if ($lokasiName === '') {
                $lokasiName = 'Lokasi ' . $lokasiId;
            }
            $nameKey = mb_strtolower(preg_replace('/\s+/', ' ', $lokasiName));
            $canonicalId = $canonicalByName[$nameKey] ?? $lokasiId;
            if (!isset($canonicalByName[$nameKey])) {
                $canonicalByName[$nameKey] = $canonicalId;
                $locations[] = [
                    'id' => $canonicalId,
                    'name' => $lokasiName,
                ];
            }
            $canonicalByLokasiId[$lokasiId] = $canonicalId;
            $aliasMap[$canonicalId] = $aliasMap[$canonicalId] ?? [];
            if (!in_array($lokasiId, $aliasMap[$canonicalId], true)) {
                $aliasMap[$canonicalId][] = $lokasiId;
            }

            foreach (($lokasi->dokumen ?? collect()) as $dokumen) {
                foreach (($dokumen->parameters ?? collect()) as $docParam) {
                    $paramId = (int) ($docParam->service_parameter_id ?? 0);
                    if ($paramId <= 0) {
                        continue;
                    }
                    $locationParamMap[$canonicalId] = $locationParamMap[$canonicalId] ?? [];
                    if (!in_array($paramId, $locationParamMap[$canonicalId], true)) {
                        $locationParamMap[$canonicalId][] = $paramId;
                    }
                    $locationParamMethodMap[$canonicalId] = $locationParamMethodMap[$canonicalId] ?? [];
                    $locationParamMethodMap[$canonicalId][$paramId] = $locationParamMethodMap[$canonicalId][$paramId] ?? [
                        'has_direct' => false,
                        'has_indirect' => false,
                    ];
                    if ((bool) $docParam->is_direct) {
                        $locationParamMethodMap[$canonicalId][$paramId]['has_direct'] = true;
                    } else {
                        $locationParamMethodMap[$canonicalId][$paramId]['has_indirect'] = true;
                    }
                    if (!isset($parameters[$paramId])) {
                        $categoryName = trim((string) ($docParam->serviceParameter?->category?->name ?? ''));
                        $categoryShort = trim((string) ($docParam->serviceParameter?->category?->short_code ?? ''));
                        $parameters[$paramId] = [
                            'id' => $paramId,
                            'name' => (string) ($docParam->serviceParameter?->name ?? ('Parameter ' . $paramId)),
                            'category' => $categoryName,
                            'category_short' => $categoryShort !== '' ? $categoryShort : '-',
                            'has_direct' => false,
                            'has_indirect' => false,
                            'total_qty' => 0,
                        ];
                    }
                    $parameters[$paramId]['total_qty'] += max(1, (int) ($docParam->qty ?? 1));
                    if ((bool) $docParam->is_direct) {
                        $parameters[$paramId]['has_direct'] = true;
                    } else {
                        $parameters[$paramId]['has_indirect'] = true;
                    }
                }
            }
        }

        foreach ($locationParamMap as $lokasiId => $parameterIds) {
            sort($parameterIds);
            $locationParamMap[$lokasiId] = array_values($parameterIds);
        }

        foreach ($parameters as $paramId => $param) {
            $hasDirect = (bool) ($param['has_direct'] ?? false);
            $hasIndirect = (bool) ($param['has_indirect'] ?? false);
            $parameters[$paramId]['method_label'] = $hasDirect && $hasIndirect
                ? 'Direct & Indirect'
                : ($hasDirect ? 'Direct' : ($hasIndirect ? 'Indirect' : '-'));
        }

        return [
            'locations' => array_values($locations),
            'parameters' => array_values($parameters),
            'location_param_map' => $locationParamMap,
            'location_param_method_map' => $locationParamMethodMap,
            'canonical_by_lokasi_id' => $canonicalByLokasiId,
            'location_alias_map' => $aliasMap,
        ];
    }

    private function resolveDocumentPayloads(Permohonan $permohonan, array $catalog): array
    {
        $tables = (array) ($permohonan->draftLhu?->tables ?? []);
        $docs = (array) data_get($tables, '__documents', []);
        if (!empty($docs)) {
            return collect($docs)->map(function ($doc) use ($catalog) {
                if (!is_array($doc)) {
                    return null;
                }
                try {
                    return $this->sanitizeDocumentPayload($doc, $catalog, null);
                } catch (RuntimeException $e) {
                    return null;
                }
            })->filter()->values()->all();
        }

        // Jika belum ada dokumen tersimpan, tampilkan kosong.
        // Petugas harus klik "Tambah Dokumen" untuk mulai.
        return [];
    }

    private function sanitizeDocumentPayload(array $doc, array $catalog, ?int $existingType = null): array
    {
        $id = trim((string) ($doc['id'] ?? ''));
        if ($id === '') {
            $id = 'doc-' . Str::lower(Str::random(8));
        }

        $type = (int) ($doc['type'] ?? 0);
        if (!in_array($type, [1, 2, 3], true)) {
            throw new RuntimeException('Tipe dokumen LHU tidak valid.');
        }
        if ($existingType !== null && $existingType !== $type) {
            throw new RuntimeException('Tipe dokumen LHU tidak dapat diubah. Hapus dokumen lalu buat baru.');
        }

        $validLocationIds = collect((array) ($catalog['locations'] ?? []))->pluck('id')->map(fn ($id) => (int) $id)->all();
        $validParameterIds = collect((array) ($catalog['parameters'] ?? []))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $lokasiIds = collect((array) ($doc['lokasi_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $validLocationIds, true))
            ->unique()
            ->values()
            ->all();
        $parameterIds = collect((array) ($doc['parameter_ids'] ?? []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => in_array($id, $validParameterIds, true))
            ->unique()
            ->values()
            ->all();

        if ($type === 1 && (count($lokasiIds) !== 1 || count($parameterIds) < 1)) {
            throw new RuntimeException('Tipe 1 wajib 1 lokasi dan minimal 1 parameter.');
        }
        if ($type === 2 && (count($parameterIds) !== 1 || count($lokasiIds) < 1)) {
            throw new RuntimeException('Tipe 2 wajib 1 parameter dan minimal 1 lokasi.');
        }
        if ($type === 3 && (count($lokasiIds) < 1 || count($parameterIds) < 1)) {
            throw new RuntimeException('Tipe 3 wajib minimal 1 lokasi dan 1 parameter.');
        }

        $parameterMeta = collect((array) ($catalog['parameters'] ?? []))->keyBy('id');
        $selectedCategoryCodes = collect($parameterIds)
            ->map(function ($id) use ($parameterMeta) {
                return trim((string) data_get($parameterMeta->get($id), 'category_short', ''));
            })
            ->filter(fn ($code) => $code !== '' && $code !== '-')
            ->unique()
            ->values();
        if ($selectedCategoryCodes->count() > 1) {
            throw new RuntimeException('Parameter harus dari kategori yang sama.');
        }

        $locationParamMap = (array) ($catalog['location_param_map'] ?? []);
        foreach ($parameterIds as $paramId) {
            $ok = false;
            foreach ($lokasiIds as $lokasiId) {
                if (in_array($paramId, (array) ($locationParamMap[$lokasiId] ?? []), true)) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                throw new RuntimeException('Ada parameter yang tidak tersedia pada lokasi terpilih.');
            }
        }

        return [
            'id' => $id,
            'title' => mb_substr(trim((string) ($doc['title'] ?? ('Dokumen ' . strtoupper((string) $id)))), 0, 120),
            'type' => $type,
            'lokasi_ids' => $lokasiIds,
            'parameter_ids' => $parameterIds,
            'table' => $this->ensureDirectTableHasNab(
                $type,
                $this->sanitizeTable((array) ($doc['table'] ?? []))
            ),
            'meteorologi' => isset($doc['meteorologi']) ? $doc['meteorologi'] : null,
            'ai' => $this->sanitizeAiPayload($doc['ai'] ?? null),
            'indirect_nab_values' => $this->sanitizeIndirectNabValues($doc['indirect_nab_values'] ?? []),
            'indirect_table_manual' => (bool) ($doc['indirect_table_manual'] ?? false),
            'indirect_table' => $this->sanitizeTable((array) ($doc['indirect_table'] ?? ['columns' => [], 'rows' => []])),
        ];
    }

    private function buildIndirectRowsBySelection(Permohonan $permohonan, array $catalog): array
    {
        $permohonan->loadMissing([
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'prepanalisa.items',
        ]);

        $preMap = ($permohonan->prepanalisa?->items ?? collect())->keyBy('pengujian_dokumen_parameter_id');
        $rows = [];
        foreach (($permohonan->pengujian?->lokasi ?? collect())->sortBy('urutan') as $lokasi) {
            foreach (($lokasi->dokumen ?? collect())->sortBy('urutan') as $dokumen) {
                foreach (($dokumen->parameters ?? collect()) as $docParam) {
                    if ((bool) $docParam->is_direct) {
                        continue;
                    }
                    $hasil = $this->formatIndirectResult(
                        $preMap->get($docParam->id)?->data_hasil_perhitungan,
                        $preMap->get($docParam->id)?->data_hasil_baca
                    );
                    $dataset = $this->extractStructuredResultDataset(
                        $preMap->get($docParam->id)?->data_hasil_perhitungan,
                        $preMap->get($docParam->id)?->data_hasil_baca
                    );
                    if ($hasil === '' && $dataset === null) {
                        continue;
                    }
                    $lokasiId = (int) ($lokasi->id ?? 0);
                    $canonicalLokasiId = (int) data_get($catalog, 'canonical_by_lokasi_id.' . $lokasiId, $lokasiId);
                    $rows[] = [
                        'lokasi_id' => $canonicalLokasiId,
                        'lokasi_name' => (string) ($lokasi->nama_lokasi ?? '-'),
                        'parameter_id' => (int) ($docParam->service_parameter_id ?? 0),
                        'parameter_name' => (string) ($docParam->serviceParameter?->name ?? '-'),
                        'parameter_category' => (string) ($docParam->serviceParameter?->category?->name ?? '-'),
                        'hasil' => $hasil,
                        'dataset' => $dataset,
                    ];
                }
            }
        }

        return $rows;
    }

    private function buildIndirectTableByDocument(array $doc, array $rows): array
    {
        $lokasiIds = array_map('intval', (array) ($doc['lokasi_ids'] ?? []));
        $parameterIds = array_map('intval', (array) ($doc['parameter_ids'] ?? []));

        $filtered = array_values(array_filter($rows, function ($row) use ($lokasiIds, $parameterIds) {
            return in_array((int) ($row['lokasi_id'] ?? 0), $lokasiIds, true)
                && in_array((int) ($row['parameter_id'] ?? 0), $parameterIds, true);
        }));

        $manualTable = $this->appendNabColumnToIndirectTable(
            $this->sanitizeTable((array) ($doc['indirect_table'] ?? ['columns' => [], 'rows' => []])),
            (array) ($doc['indirect_nab_values'] ?? [])
        );
        if ((bool) ($doc['indirect_table_manual'] ?? false)) {
            return $manualTable;
        }

        return $this->appendNabColumnToIndirectTable(
            $this->buildIndirectKadarTable($filtered),
            (array) ($doc['indirect_nab_values'] ?? [])
        );
    }

    private function buildIndirectKadarTable(array $filtered): array
    {
        if (empty($filtered)) {
            return [
                'columns' => ['No', 'Parameter', 'Hasil Analisa'],
                'rows' => [],
            ];
        }

        $parameterLokasiMap = [];
        foreach ($filtered as $entry) {
            $parameterName = trim((string) ($entry['parameter_name'] ?? '-'));
            if ($parameterName === '') {
                $parameterName = '-';
            }
            $lokasiName = trim((string) ($entry['lokasi_name'] ?? '-'));
            if ($lokasiName === '') {
                $lokasiName = '-';
            }
            if (!isset($parameterLokasiMap[$parameterName])) {
                $parameterLokasiMap[$parameterName] = [];
            }
            if (!in_array($lokasiName, $parameterLokasiMap[$parameterName], true)) {
                $parameterLokasiMap[$parameterName][] = $lokasiName;
            }
        }

        $parameterOrder = [];
        $groupedValues = [];
        $headerOrder = [];
        foreach ($filtered as $entry) {
            $parameterName = trim((string) ($entry['parameter_name'] ?? '-'));
            if ($parameterName === '') {
                $parameterName = '-';
            }
            $lokasiName = trim((string) ($entry['lokasi_name'] ?? '-'));
            if ($lokasiName === '') {
                $lokasiName = '-';
            }

            if (!in_array($parameterName, $parameterOrder, true)) {
                $parameterOrder[] = $parameterName;
                $groupedValues[$parameterName] = [];
            }

            $measurements = $this->extractIndirectMeasurements($entry);
            if (empty($measurements)) {
                $fallback = trim((string) ($entry['hasil'] ?? ''));
                if ($fallback !== '') {
                    $measurements[] = ['header' => 'Hasil Analisa', 'value' => $fallback];
                }
            }

            $needLokasiPrefix = count($parameterLokasiMap[$parameterName] ?? []) > 1;
            foreach ($measurements as $measurement) {
                $header = trim((string) ($measurement['header'] ?? 'Kadar'));
                if ($header === '') {
                    $header = 'Hasil Analisa';
                }
                if ($this->isIndirectAnalysisColumnLabel($header)) {
                    $header = 'Hasil Analisa';
                }
                $value = trim((string) ($measurement['value'] ?? ''));
                if ($value === '') {
                    continue;
                }
                if (!in_array($header, $headerOrder, true)) {
                    $headerOrder[] = $header;
                }
                $display = $needLokasiPrefix && $lokasiName !== '-'
                    ? ($lokasiName . ': ' . $value)
                    : $value;
                if (!isset($groupedValues[$parameterName][$header])) {
                    $groupedValues[$parameterName][$header] = [];
                }
                if (!in_array($display, $groupedValues[$parameterName][$header], true)) {
                    $groupedValues[$parameterName][$header][] = $display;
                }
            }
        }

        if (empty($headerOrder)) {
            $headerOrder = ['Hasil Analisa'];
        }
        $columns = array_merge(['No', 'Parameter'], $headerOrder);

        $rows = [];
        foreach ($parameterOrder as $index => $parameterName) {
            $line = [
                (string) ($index + 1),
                $parameterName,
            ];
            foreach ($headerOrder as $header) {
                $values = (array) ($groupedValues[$parameterName][$header] ?? []);
                $line[] = implode(' | ', $values);
            }
            $rows[] = $line;
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    private function extractIndirectMeasurements(array $entry): array
    {
        $dataset = $entry['dataset'] ?? null;
        if (!is_array($dataset)) {
            return [];
        }

        $columns = array_values((array) ($dataset['columns'] ?? []));
        $rows = array_values((array) ($dataset['rows'] ?? []));
        if (empty($columns) || empty($rows)) {
            return [];
        }

        $valueIndexes = [];
        $unitIndexes = [];
        foreach ($columns as $index => $column) {
            $label = strtolower(trim((string) $column));
            if ($label === '') {
                continue;
            }
            if (str_contains($label, 'satuan') || str_contains($label, 'unit')) {
                $unitIndexes[] = $index;
                continue;
            }
            if (str_contains($label, 'kadar')
                || str_contains($label, 'hasil')
                || str_contains($label, 'konsentrasi')
                || str_contains($label, 'concentration')
                || str_contains($label, 'conc')) {
                $valueIndexes[] = $index;
            }
        }

        if (empty($valueIndexes)) {
            foreach ($columns as $index => $column) {
                $label = strtolower(trim((string) $column));
                if ($label === ''
                    || $label === 'no'
                    || $label === 'no.'
                    || str_contains($label, 'parameter')
                    || str_contains($label, 'lokasi')
                    || str_contains($label, 'sampel')
                    || str_contains($label, 'sample')
                    || str_contains($label, 'koding')
                    || str_contains($label, 'metode')
                    || str_contains($label, 'satuan')
                    || str_contains($label, 'unit')) {
                    continue;
                }
                $valueIndexes[] = $index;
            }
        }

        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $unit = '';
            foreach ($unitIndexes as $unitIndex) {
                $unitCell = trim((string) ($row[$unitIndex] ?? ''));
                if ($unitCell !== '') {
                    $unit = $unitCell;
                    break;
                }
            }

            foreach ($valueIndexes as $valueIndex) {
                $value = trim((string) ($row[$valueIndex] ?? ''));
                if ($value === '') {
                    continue;
                }
                $header = trim((string) ($columns[$valueIndex] ?? ''));
                if ($header === '') {
                    $header = 'Kadar';
                }
                $display = $value;
                if ($unit !== '' && stripos($display, $unit) === false) {
                    $display .= ' ' . $unit;
                }
                $result[] = [
                    'header' => $header,
                    'value' => trim($display),
                ];
            }
        }

        return $result;
    }

    private function isIndirectReady(Permohonan $permohonan): bool
    {
        if (!$this->hasIndirectParameters($permohonan)) {
            return true;
        }

        $verifStep = WorkflowStep::where('kode', 'verifikasi')->first();
        if (!$verifStep) {
            return false;
        }

        return PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $verifStep->id)
            ->where('status', 'approved')
            ->exists();
    }

    private function formatIndirectResult($hasilPerhitungan, $hasilBaca): string
    {
        $perhitunganRows = $this->extractResultRows($hasilPerhitungan);
        if (!empty($perhitunganRows)) {
            $formatted = $this->formatResultRows($perhitunganRows);
            if ($formatted !== '') {
                return $formatted;
            }
            return $this->flattenResultValue($perhitunganRows);
        }

        $hasilBacaRows = $this->extractResultRows($hasilBaca);
        if (!empty($hasilBacaRows)) {
            $formatted = $this->formatResultRows($hasilBacaRows);
            if ($formatted !== '') {
                return $formatted;
            }
            return $this->flattenResultValue($hasilBacaRows);
        }

        return '';
    }

    private function extractResultRows($dataset): array
    {
        if (!is_array($dataset)) {
            return [];
        }

        if (array_key_exists('rows', $dataset) && is_array($dataset['rows'])) {
            return $dataset['rows'];
        }

        // Backward compatibility: old dataset stored rows array directly.
        return array_is_list($dataset) ? $dataset : [];
    }

    private function extractStructuredResultDataset($hasilPerhitungan, $hasilBaca): ?array
    {
        foreach ([$hasilPerhitungan, $hasilBaca] as $dataset) {
            if (!is_array($dataset) || !isset($dataset['columns'], $dataset['rows'])) {
                continue;
            }
            if (!is_array($dataset['columns']) || !is_array($dataset['rows'])) {
                continue;
            }

            $columns = array_values(array_filter(array_map(function ($column) {
                return trim((string) $column);
            }, $dataset['columns']), function ($column) {
                return $column !== '';
            }));
            if (empty($columns)) {
                continue;
            }

            $rows = [];
            foreach ($dataset['rows'] as $rowIndex => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $cells = [];
                if (array_is_list($row)) {
                    $hasNoHeader = $this->hasNoHeader($columns);
                    $shiftNoColumn = $hasNoHeader && count($row) === max(0, count($columns) - 1);
                    $cursor = 0;
                    foreach ($columns as $idx => $label) {
                        if ($shiftNoColumn && $this->isNoHeaderLabel((string) $label)) {
                            $cells[] = (string) ($rowIndex + 1);
                            continue;
                        }
                        $cells[] = trim((string) ($row[$cursor] ?? ''));
                        $cursor++;
                    }
                } elseif (isset($row['cols']) && is_array($row['cols'])) {
                    $rowCols = array_values($row['cols']);
                    $hasNoHeader = $this->hasNoHeader($columns);
                    $shiftNoColumn = $hasNoHeader && count($rowCols) === max(0, count($columns) - 1);
                    $cursor = 0;
                    foreach ($columns as $idx => $label) {
                        if ($shiftNoColumn && $this->isNoHeaderLabel((string) $label)) {
                            $cells[] = trim((string) ($row['no'] ?? ($rowIndex + 1)));
                            continue;
                        }
                        $cells[] = trim((string) ($rowCols[$cursor] ?? ''));
                        $cursor++;
                    }
                } else {
                    foreach ($columns as $idx => $label) {
                        $cells[] = $this->resolveStructuredCellByHeader($row, (string) $label, (int) $idx, (int) $rowIndex);
                    }
                }

                if ($this->shouldSkipStructuredDatasetRow($row, $cells)) {
                    continue;
                }
                $rows[] = $cells;
            }

            if (!empty($rows)) {
                return [
                    'columns' => $columns,
                    'rows' => $rows,
                ];
            }
        }

        return null;
    }

    private function shouldSkipStructuredDatasetRow(array $row, array $cells): bool
    {
        $rowType = strtolower(trim((string) ($row['row_type'] ?? '')));
        if (str_contains($rowType, 'mdl') || str_contains($rowType, 'lod')) {
            return true;
        }

        return $this->looksLikeMdlOrLod($cells);
    }

    private function resolveStructuredCellByHeader(array $row, string $header, int $idx, int $rowIndex = 0): string
    {
        $label = strtolower(trim($header));
        $label = preg_replace('/\s+/', ' ', $label) ?? $label;

        $candidates = [];
        if ($label === 'no' || $label === 'no.' || str_starts_with($label, 'no ')) {
            $candidates = ['no', 'nomor', 'urutan'];
        } elseif (str_contains($label, 'sampel') || str_contains($label, 'sample') || str_contains($label, 'koding')) {
            $candidates = ['label', 'no_sampel', 'sample', 'koding', 'lokasi'];
        } elseif (str_contains($label, 'vol') && str_contains($label, 'cs2')) {
            $candidates = ['vol_cs2', 'volume', 'vol'];
        } elseif (str_contains($label, 'rt benzene')) {
            $candidates = ['rt_benzene'];
        } elseif (str_contains($label, 'area benzene')) {
            $candidates = ['area_benzene'];
        } elseif (str_contains($label, 'benzene') && str_contains($label, 'mg/ml')) {
            $candidates = ['benzene'];
        } elseif (str_contains($label, 'rt toluene')) {
            $candidates = ['rt_toluene'];
        } elseif (str_contains($label, 'area toluene')) {
            $candidates = ['area_toluene'];
        } elseif (str_contains($label, 'toluene') && str_contains($label, 'mg/ml')) {
            $candidates = ['toluene'];
        } elseif (str_contains($label, 'rt xylene')) {
            $candidates = ['rt_xylene'];
        } elseif (str_contains($label, 'area xylene')) {
            $candidates = ['area_xylene'];
        } elseif (str_contains($label, 'xylene') && str_contains($label, 'mg/ml')) {
            $candidates = ['xylene'];
        } elseif (str_contains($label, 'lokasi')) {
            $candidates = ['lokasi', 'label', 'sample'];
        } elseif ($label === 'parameter') {
            $candidates = ['parameter', 'nama', 'label'];
        } elseif ($label === 'sk' || str_contains($label, 'sk ')) {
            $candidates = ['sk'];
        } elseif ($label === 'pm' || str_contains($label, 'mmhg')) {
            $candidates = ['pm', 'p'];
        }

        $fallbackKey = $this->normalizeDatasetHeaderToKey($header);
        if ($fallbackKey !== '') {
            $candidates[] = $fallbackKey;
        }

        foreach (array_values(array_unique($candidates)) as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $text = trim((string) ($row[$key] ?? ''));
            if ($text !== '') {
                return $text;
            }
        }

        if ($label === 'no' || $label === 'no.' || str_starts_with($label, 'no ')) {
            return (string) ($rowIndex + 1);
        }

        if (isset($row['cols']) && is_array($row['cols'])) {
            return trim((string) ($row['cols'][$idx] ?? ''));
        }

        return '';
    }

    private function hasNoHeader(array $columns): bool
    {
        foreach ($columns as $column) {
            if ($this->isNoHeaderLabel((string) $column)) {
                return true;
            }
        }
        return false;
    }

    private function isNoHeaderLabel(string $label): bool
    {
        $text = strtolower(trim($label));
        return $text === 'no' || $text === 'no.' || str_starts_with($text, 'no ');
    }

    private function normalizeDatasetHeaderToKey(string $header): string
    {
        $text = strtolower(trim($header));
        if ($text === '') {
            return '';
        }

        $text = str_replace(['.', '-', '/', '(', ')'], ' ', $text);
        $text = preg_replace('/\s+/', '_', $text) ?? $text;
        $text = preg_replace('/[^a-z0-9_]/', '', $text) ?? $text;
        return trim($text, '_');
    }

    private function formatResultRows(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }

        $parts = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $mappedLine = $this->formatMappedResultRow($row);
                if ($mappedLine !== '') {
                    $parts[] = $mappedLine;
                    continue;
                }
            }

            if (is_scalar($row)) {
                $text = trim((string) $row);
                if ($text !== '') {
                    $parts[] = $text;
                }
            }
        }

        if (empty($parts)) {
            return '';
        }

        return implode('; ', array_slice($parts, 0, 10));
    }

    private function formatMappedResultRow(array $row): string
    {
        $rowType = strtolower(trim((string) ($row['row_type'] ?? '')));
        $cols = isset($row['cols']) && is_array($row['cols']) ? array_values($row['cols']) : [];

        // Baris MDL/LOD tidak dipakai sebagai hasil utama di Draft LHU.
        if (str_contains($rowType, 'mdl') || str_contains($rowType, 'lod')) {
            return '';
        }
        if ($this->looksLikeMdlOrLod($cols)) {
            return '';
        }

        $label = '';
        foreach (['lokasi', 'label', 'sample', 'titik'] as $labelKey) {
            if (!array_key_exists($labelKey, $row)) {
                continue;
            }
            $label = $this->toText($row[$labelKey]);
            if ($label !== '') {
                break;
            }
        }
        if ($label === '' && isset($cols[1])) {
            $label = $this->toText($cols[1]);
        }

        $value = '';
        if (str_contains($rowType, 'no2') || str_contains($rowType, 'hc')) {
            $value = $this->pickByKeys($row, ['kadar_ugm3', 'kadar_ppm', 'kadar_mg_m3', 'kadar', 'kndbl', 'knd-bl', 'kand']);
            if ($value === '') {
                $value = $this->pickByIndexes($cols, [9, 8, 7]);
            }
        } elseif (str_contains($rowType, 'so2')) {
            $value = $this->pickByKeys($row, ['kadar_mg_m3', 'kadar_ugm3', 'kadar_ppm', 'knd-bl', 'kndbl', 'kand-spl', 'kand_spl']);
            if ($value === '') {
                $value = $this->pickByIndexes($cols, [8, 7, 6]);
            }
        } elseif (str_contains($rowType, 'debu')) {
            $value = $this->pickByKeys($row, ['kadar', 'kadar_mg_m3']);
            if ($value === '') {
                $value = $this->pickByIndexes($cols, [7, 6]);
            }
        } else {
            $value = $this->pickByKeys($row, ['hasil_akhir', 'hasil', 'kadar_ugm3', 'kadar_mg_m3', 'kadar_ppm', 'kadar', 'kndbl', 'knd-bl', 'kand_spl', 'kand', 'conc']);
            if ($value === '') {
                $value = $this->pickLastNumericFromCols($cols);
            }
        }

        if ($value === '') {
            return '';
        }

        return $value;
    }

    private function formatColsRow(array $cols): string
    {
        $clean = array_map(function ($value) {
            return trim((string) $value);
        }, $cols);
        $clean = array_values(array_filter($clean, function ($value) {
            return $value !== '';
        }));
        if (empty($clean)) {
            return '';
        }

        $label = $clean[0] ?? '';
        $value = '';
        for ($i = count($clean) - 1; $i >= 1; $i--) {
            if ($clean[$i] !== '') {
                $value = $clean[$i];
                break;
            }
        }
        if ($value === '') {
            $value = end($clean) ?: '';
        }
        if ($value === '') {
            return '';
        }

        if ($label !== '' && $label !== $value) {
            return $label . ': ' . $value;
        }

        return $value;
    }

    private function formatAssocRow(array $row): string
    {
        $preferredKeys = [
            'hasil_akhir',
            'hasil',
            'kadar_ugm3',
            'kadar_mg_m3',
            'kadar_ppm',
            'kadar',
            'conc',
            'konsentrasi',
            'kons',
            'nilai',
            'value',
            'result',
            'kand_spl',
        ];

        $picked = '';
        foreach ($preferredKeys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $candidate = $row[$key];
            if (is_scalar($candidate)) {
                $text = trim((string) $candidate);
                if ($text !== '') {
                    $picked = $text;
                    break;
                }
            }
        }

        if ($picked === '') {
            foreach ($row as $key => $value) {
                if ($key === 'row_type' || $key === 'cols') {
                    continue;
                }
                if (is_scalar($value)) {
                    $text = trim((string) $value);
                    if ($text !== '') {
                        $picked = $text;
                        break;
                    }
                }
            }
        }

        if ($picked === '') {
            return '';
        }

        $label = '';
        foreach (['lokasi', 'label', 'sample', 'titik'] as $labelKey) {
            if (!array_key_exists($labelKey, $row) || !is_scalar($row[$labelKey])) {
                continue;
            }
            $candidate = trim((string) $row[$labelKey]);
            if ($candidate !== '') {
                $label = $candidate;
                break;
            }
        }

        return $label !== '' ? ($label . ': ' . $picked) : $picked;
    }

    private function pickByKeys(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $text = $this->toText($row[$key]);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    private function pickByIndexes(array $cols, array $indexes): string
    {
        foreach ($indexes as $index) {
            if (!array_key_exists($index, $cols)) {
                continue;
            }
            $text = $this->toText($cols[$index]);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    private function pickLastNumericFromCols(array $cols): string
    {
        for ($i = count($cols) - 1; $i >= 0; $i--) {
            if (!array_key_exists($i, $cols)) {
                continue;
            }
            $text = $this->toText($cols[$i]);
            if ($text === '') {
                continue;
            }
            if ($this->isNumericLike($text)) {
                return $text;
            }
        }

        return '';
    }

    private function looksLikeMdlOrLod(array $cols): bool
    {
        foreach (array_slice($cols, 0, 3) as $value) {
            $text = strtolower($this->toText($value));
            if ($text === '') {
                continue;
            }
            if (str_contains($text, 'mdl') || str_contains($text, 'lod')) {
                return true;
            }
        }

        return false;
    }

    private function isNumericLike(string $text): bool
    {
        $clean = str_replace(',', '.', trim($text));
        return $clean !== '' && is_numeric($clean);
    }

    private function toText($value): string
    {
        if (is_scalar($value)) {
            return trim((string) $value);
        }
        return '';
    }

    private function flattenResultValue(array $data): string
    {
        $values = [];
        $walker = function ($node) use (&$walker, &$values) {
            if (is_array($node)) {
                foreach ($node as $child) {
                    $walker($child);
                }
                return;
            }
            if (is_scalar($node)) {
                $text = trim((string) $node);
                if ($text !== '') {
                    $values[] = $text;
                }
            }
        };
        $walker($data);

        if (empty($values)) {
            return '';
        }

        return implode('; ', array_slice($values, 0, 6));
    }

    private function sanitizeTable($table): array
    {
        $columns = [];
        foreach (($table['columns'] ?? []) as $column) {
            $name = trim((string) $column);
            $columns[] = $name;
            if (count($columns) >= 30) {
                break;
            }
        }
        if (empty($columns)) {
            $columns = ['Parameter', 'Hasil Pengukuran'];
        }

        $rows = [];
        foreach (($table['rows'] ?? []) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $clean = [];
            for ($i = 0; $i < count($columns); $i++) {
                $clean[] = trim((string) ($row[$i] ?? ''));
            }
            $rows[] = $clean;
            if (count($rows) >= 500) {
                break;
            }
        }

        if (empty($rows)) {
            $rows[] = array_fill(0, count($columns), '');
        }

        return [
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    private function ensureDirectTableHasNab(int $type, array $table): array
    {
        $columns = array_values((array) ($table['columns'] ?? []));
        $rows = array_values((array) ($table['rows'] ?? []));
        $targetColumns = match ($type) {
            1 => ['Parameter', 'Hasil Pengukuran', 'NAB'],
            2 => ['Lokasi', 'Hasil Pengukuran', 'NAB'],
            default => ['Lokasi', 'Parameter', 'Hasil Pengukuran', 'NAB'],
        };

        if (empty($columns)) {
            $columns = $targetColumns;
        }

        $nabIndex = null;
        foreach ($columns as $index => $column) {
            if ($this->isNabColumnLabel((string) $column)) {
                $nabIndex = $index;
                break;
            }
        }

        if ($nabIndex === null) {
            $columns[] = 'NAB';
            $nabIndex = count($columns) - 1;
            $rows = array_map(function ($row) use ($columns) {
                $next = is_array($row) ? array_values($row) : [];
                while (count($next) < count($columns) - 1) {
                    $next[] = '';
                }
                $next[] = '';
                return $next;
            }, $rows);
        }

        if (empty($rows)) {
            $rows[] = array_fill(0, count($columns), '');
        }

        $normalizedRows = [];
        foreach ($rows as $row) {
            $clean = is_array($row) ? array_values($row) : [];
            for ($i = count($clean); $i < count($columns); $i++) {
                $clean[] = '';
            }
            $normalizedRows[] = array_slice($clean, 0, count($columns));
        }

        return [
            'columns' => $columns,
            'rows' => $normalizedRows,
        ];
    }

    private function sanitizeIndirectNabValues($values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $clean = [];
        foreach ($values as $key => $value) {
            $normalizedKey = $this->normalizeParameterKey((string) $key);
            if ($normalizedKey === '') {
                continue;
            }
            $clean[$normalizedKey] = mb_substr(trim((string) $value), 0, 255);
        }

        return $clean;
    }

    private function sanitizeLocationPayload($payload, ?array $defaultMain = null, $fallbackAi = null): array
    {
        $defaultMain = $defaultMain ?? [
            'columns' => ['Parameter', 'Hasil Pengukuran'],
            'rows' => [array_fill(0, 2, '')],
        ];
        $result = [
            'table' => $defaultMain,
            'meteorologi' => null,
            'ai' => $this->sanitizeAiPayload($fallbackAi),
        ];

        if (!is_array($payload)) {
            return $result;
        }

        // Backward compatibility: old payload was main table only.
        if (isset($payload['columns']) && isset($payload['rows'])) {
            $result['table'] = $this->sanitizeTable($payload);
            return $result;
        }

        if (isset($payload['table']) && is_array($payload['table'])) {
            $result['table'] = $this->sanitizeTable($payload['table']);
        }
        if (isset($payload['ai']) && is_array($payload['ai'])) {
            $result['ai'] = $this->sanitizeAiPayload($payload['ai']);
        }

        if (!isset($payload['meteorologi']) || !is_array($payload['meteorologi'])) {
            return $result;
        }

        $met = $payload['meteorologi'];
        $type = (int) ($met['type'] ?? 0);
        if (!in_array($type, [1, 2], true)) {
            return $result;
        }

        $defaultMet = $this->defaultMeteorologyTemplate($type);
        $table = $this->sanitizeTable($met['table'] ?? $defaultMet['table']);
        $result['meteorologi'] = [
            'type' => $type,
            'table' => $table,
        ];

        return $result;
    }

    private function sanitizeAiPayload($payload): ?array
    {
        if (!is_array($payload)) {
            return null;
        }

        $analisa = $this->normalizeAiTextFormatting((string) ($payload['analisa'] ?? ''));
        $kesimpulan = $this->normalizeAiTextFormatting((string) ($payload['kesimpulan'] ?? ''));
        $saran = $this->normalizeAiTextFormatting((string) ($payload['saran'] ?? ''));
        if ($analisa === '' && $kesimpulan === '' && $saran === '') {
            return null;
        }

        return [
            'analisa' => mb_substr($analisa, 0, 3000),
            'kesimpulan' => mb_substr($kesimpulan, 0, 3000),
            'saran' => mb_substr($saran, 0, 3000),
            'model' => mb_substr(trim((string) ($payload['model'] ?? '')), 0, 120),
            'generated_at' => trim((string) ($payload['generated_at'] ?? '')),
        ];
    }

    private function normalizeAiTextFormatting(string $text): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        // Pecah list bernomor yang masih menyatu dalam satu baris: "1. ... 2. ..."
        $normalized = preg_replace('/(?<!^)\h+(?=\d+[\.\)]\h+)/u', "\n", $normalized);
        $normalized = preg_replace("/\n{3,}/", "\n\n", $normalized);

        return trim((string) $normalized);
    }

    private function normalizeAiParagraph(string $text): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", $text);
        $normalized = preg_replace('/\s*\n\s*/u', ' ', $normalized);
        $normalized = preg_replace('/ {2,}/', ' ', $normalized);

        return trim((string) $normalized);
    }

    private function defaultMeteorologyTemplate(int $type): array
    {
        if ($type === 1) {
            return [
                'type' => 1,
                'table' => [
                    'columns' => ['No', 'Parameter', 'Hasil Pengujian::1', 'Hasil Pengujian::2', 'Hasil Pengujian::3', 'Satuan'],
                    'rows' => [
                        ['1', 'Suhu Udara', '29,0', '29,0', '29,0', 'oC'],
                        ['2', 'Kelembaban Nisbi (RH)', '76,0', '76,0', '76,0', '%'],
                        ['3', 'Kecepatan Angin', '0,98', '0,98', '0,98', 'm/dtk'],
                        ['4', 'Arah Angin Ke', 'Barat', 'Barat', 'Barat', '-'],
                        ['5', 'Cuaca', 'Cerah', 'Cerah', 'Cerah', '-'],
                    ],
                ],
            ];
        }

        return [
            'type' => 2,
            'table' => [
                'columns' => ['No.', 'Parameter', 'Satuan', 'Hasil Pengukuran'],
                'rows' => [
                    ['1.', 'Suhu Udara', 'oC', '29,0'],
                    ['2.', 'Kelembaban ( RH )', '%', '76,0'],
                ],
            ],
        ];
    }

    private function validateParameterUsageQuota(array $documents, array $catalog): void
    {
        $totals = [];
        foreach ((array) ($catalog['parameters'] ?? []) as $param) {
            $paramId = (int) ($param['id'] ?? 0);
            if ($paramId <= 0) {
                continue;
            }
            $totals[$paramId] = max(1, (int) ($param['total_qty'] ?? 1));
        }

        $used = [];
        foreach ($documents as $doc) {
            foreach (array_values(array_unique(array_map('intval', (array) ($doc['parameter_ids'] ?? [])))) as $paramId) {
                if ($paramId <= 0) {
                    continue;
                }
                $used[$paramId] = ($used[$paramId] ?? 0) + 1;
            }
        }

        foreach ($used as $paramId => $count) {
            $quota = (int) ($totals[$paramId] ?? 1);
            if ($count > $quota) {
                throw new RuntimeException('Jumlah pemakaian parameter melebihi kuota BAP.');
            }
        }
    }

    private function parsePayload(Request $request): ?array
    {
        $raw = $request->input('payload');
        $payload = is_string($raw) ? json_decode($raw, true) : $raw;

        if (!is_array($payload)) {
            return null;
        }

        $hasDocuments = isset($payload['documents']) && is_array($payload['documents']);
        $hasDocument = isset($payload['document']) && is_array($payload['document']);
        if (!$hasDocuments && !$hasDocument) {
            return null;
        }

        return $payload;
    }

    private function buildWordHtmlByDocument(Permohonan $permohonan, array $document, array $catalog, array $indirectTable, ?array $logoAsset = null): string
    {
        $lokasiNames = collect((array) ($document['lokasi_ids'] ?? []))
            ->map(function ($lokasiId) use ($catalog) {
                return data_get(collect((array) data_get($catalog, 'locations', []))->firstWhere('id', (int) $lokasiId), 'name', '-');
            })
            ->filter()
            ->values()
            ->all();
        $lokasiLabel = empty($lokasiNames) ? '-' : implode(', ', $lokasiNames);

        $payload = [
            'table' => (array) ($document['table'] ?? ['columns' => [], 'rows' => []]),
            'meteorologi' => $document['meteorologi'] ?? null,
            'ai' => $document['ai'] ?? null,
        ];

        return $this->buildWordHtml(
            $this->buildWordDocumentMarkup($permohonan, $lokasiLabel, $payload, $indirectTable, $logoAsset),
            $logoAsset
        );
    }

    private function buildWordHtmlByDocuments(Permohonan $permohonan, array $documents, array $catalog, array $indirectRows, ?array $logoAsset = null): string
    {
        $sections = [];
        foreach ($documents as $document) {
            if (!is_array($document)) {
                continue;
            }

            $lokasiNames = collect((array) ($document['lokasi_ids'] ?? []))
                ->map(function ($lokasiId) use ($catalog) {
                    return data_get(collect((array) data_get($catalog, 'locations', []))->firstWhere('id', (int) $lokasiId), 'name', '-');
                })
                ->filter()
                ->values()
                ->all();
            $lokasiLabel = empty($lokasiNames) ? '-' : implode(', ', $lokasiNames);
            $payload = [
                'table' => (array) ($document['table'] ?? ['columns' => [], 'rows' => []]),
                'meteorologi' => $document['meteorologi'] ?? null,
                'ai' => $document['ai'] ?? null,
            ];
            $indirectTable = $this->buildIndirectTableByDocument($document, $indirectRows);
            $sections[] = $this->buildWordDocumentMarkup($permohonan, $lokasiLabel, $payload, $indirectTable, $logoAsset);
        }

        if (empty($sections)) {
            $sections[] = '<div class="doc-wrap"><div style="margin:0 0 8px 0;">Belum ada dokumen LHU.</div></div>';
        }

        $joined = implode('<div class="page-break"></div>', $sections);

        return $this->buildWordHtml($joined, $logoAsset);
    }

    private function buildWordDocumentMarkup(Permohonan $permohonan, string $lokasiName, array $locationPayload, array $indirectTable, ?array $logoAsset = null): string
    {
        $companyName = $permohonan->company?->company_name ?? '-';
        $noLab = $this->buildNoLab($permohonan);
        $dateLabel = now()->locale('id')->translatedFormat('d F Y');
        $alamat = $permohonan->jadwal_lokasi ?: ($permohonan->company?->company_city ?? '-');
        $tglPengujian = $permohonan->jadwal_mulai
            ? \Illuminate\Support\Carbon::parse($permohonan->jadwal_mulai)->locale('id')->translatedFormat('d F Y')
            : '-';

        $directTable = is_array($locationPayload['table'] ?? null) ? $locationPayload['table'] : [];
        $mainTableHtml = $this->buildWordTableHtml($directTable);
        $indirectTableHtml = $this->buildWordTableHtml($indirectTable);
        $hasDirectSection = $this->tableHasMeaningfulRows($directTable);
        $hasIndirectSection = $this->tableHasMeaningfulRows($indirectTable);
        $hasilSection = '';
        if ($hasDirectSection && $hasIndirectSection) {
            $hasilSection =
                '<h3 style="font-size:12pt;margin:10px 0 8px 0;">II.A HASIL PENGUJIAN DIRECT</h3>'
                . $mainTableHtml
                . '<h3 style="font-size:12pt;margin:12px 0 8px 0;">II.B HASIL PENGUJIAN INDIRECT</h3>'
                . $indirectTableHtml;
        } elseif ($hasDirectSection) {
            $hasilSection =
                '<h3 style="font-size:12pt;margin:10px 0 8px 0;">II. HASIL PENGUJIAN</h3>'
                . $mainTableHtml;
        } elseif ($hasIndirectSection) {
            $hasilSection =
                '<h3 style="font-size:12pt;margin:10px 0 8px 0;">II. HASIL PENGUJIAN</h3>'
                . $indirectTableHtml;
        } else {
            $hasilSection =
                '<h3 style="font-size:12pt;margin:10px 0 8px 0;">II. HASIL PENGUJIAN</h3>'
                . '<div style="margin:0 0 8px 0;">-</div>';
        }
        $meteoSection = '';
        $meteo = $locationPayload['meteorologi'] ?? null;
        if (is_array($meteo) && !empty($meteo['table'])) {
            $meteoSection = '<h3 style="font-size:12pt;margin:14px 0 8px 0;">III. DATA METEOROLOGIS PADA SAAT PENGUJIAN</h3>'
                . $this->buildWordTableHtml($meteo['table']);
        }
        $aiPayload = is_array($locationPayload['ai'] ?? null) ? $locationPayload['ai'] : [];
        $analisaText = trim((string) ($aiPayload['analisa'] ?? ''));
        $kesimpulanText = trim((string) ($aiPayload['kesimpulan'] ?? ''));
        $saranText = trim((string) ($aiPayload['saran'] ?? ''));
        $analisaHtml = nl2br(e($analisaText !== '' ? $analisaText : '-'));
        $kesimpulanHtml = nl2br(e($kesimpulanText !== '' ? $kesimpulanText : '-'));
        $saranHtml = nl2br(e($saranText !== '' ? $saranText : '-'));
        $logoHtml = $logoAsset
            ? '<img src="cid:word-header-logo" alt="Logo Kemenaker" width="96" height="96" style="width:72pt;height:72pt;max-width:72pt;max-height:72pt;display:block;margin:0 auto;">'
            : '';

        return '<div class="doc-wrap">
          <div class="kop">
            <table class="kop-table">
              <tr>
                <td class="kop-logo">' . $logoHtml . '</td>
                <td class="kop-text">
                  <div class="line1">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
                  <div class="line1">DIREKTORAT JENDERAL</div>
                  <div class="line1">PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
                  <div class="line1">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
                  <div class="line2">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
                  <div class="line4">Jl. Dukuh Menanggal No.122, Dukuh Menanggal, Kec. Gayungan, Kota SBY, Jawa Timur 60234, Laman : <a href="mailto:balaik3surabaya@kemnaker.go.id">balaik3surabaya@kemnaker.go.id</a></div>
                </td>
              </tr>
            </table>
          </div>
          <h1>LAPORAN HASIL UJI</h1>
          <div class="subtitle">No. LAB. ' . e($noLab) . '</div>
          <h3>I. UMUM</h3>
          <table class="meta">
            <tr><td>1.</td><td>Nama Perusahaan</td><td>:</td><td>' . e($companyName) . '</td></tr>
            <tr><td>2.</td><td>Alamat Perusahaan</td><td>:</td><td>' . e($alamat) . '</td></tr>
            <tr><td>3.</td><td>Jenis Pengujian</td><td>:</td><td>Tingkat Kebauan Lingkungan Kerja</td></tr>
            <tr><td>4.</td><td>Lokasi Pengujian</td><td>:</td><td>' . e($lokasiName) . '</td></tr>
            <tr><td>5.</td><td>Tanggal Pengujian</td><td>:</td><td>' . e($tglPengujian) . '</td></tr>
          </table>
          ' . $hasilSection . '
          ' . $meteoSection . '
          <h3 style="font-size:12pt;margin:14px 0 8px 0;">IV. ANALISA</h3>
          <div style="margin:0 0 8px 0;">' . $analisaHtml . '</div>
          <h3 style="font-size:12pt;margin:14px 0 8px 0;">V. KESIMPULAN</h3>
          <div style="margin:0 0 8px 0;">' . $kesimpulanHtml . '</div>
          <h3 style="font-size:12pt;margin:14px 0 8px 0;">VI. SARAN</h3>
          <div style="margin:0 0 8px 0;">' . $saranHtml . '</div>
          <div class="footer">
            <div>Surabaya, ' . e($dateLabel) . '</div>
            <div>Manajer Teknis,</div>
            <div class="name">(.................................)</div>
          </div>
          </div>';
    }

    private function buildWordHtml(string $bodyContent, ?array $logoAsset = null): string
    {
        return '<html><head><meta charset="UTF-8"><style>
            body{font-family:Calibri,Arial,sans-serif;font-size:11pt;color:#111;}
            .doc-wrap{max-width:900px;margin:0 auto;}
            .kop{border-bottom:4px double #8b8b8b;padding-bottom:8px;margin-bottom:12px;}
            .kop-table{width:100%;border-collapse:collapse;}
            .kop-table td{border:0;padding:0;vertical-align:middle;}
            .kop-logo{width:17%;text-align:center;}
            .kop-logo img{width:72pt !important;height:72pt !important;max-width:72pt !important;max-height:72pt !important;display:block;margin:0 auto;}
            .kop-text{text-align:center;line-height:1.2;color:#6f7276;font-weight:700;}
            .kop-text .line1{font-size:12pt;white-space:nowrap;}
            .kop-text .line2{font-size:13pt;white-space:nowrap;}
            .kop-text .line3{font-size:10.5pt;white-space:nowrap;}
            .kop-text .line4{font-size:10.5pt;font-weight:400;white-space:nowrap;}
            .kop-text .line4 a{color:#8aa2c8;text-decoration:underline;}
            h1{font-size:16pt;margin:0 0 4px 0;text-align:center;letter-spacing:0.5px;}
            .subtitle{font-size:12pt;margin:0 0 14px 0;text-align:center;font-weight:700;text-decoration:underline;}
            h3{font-size:12pt;margin:10px 0 8px 0;}
            .meta{margin:0 0 12px 0;border-collapse:collapse;width:100%;}
            .meta td{padding:2px 4px;vertical-align:top;border:0;}
            .meta td:first-child{width:28px;}
            .meta td:nth-child(2){width:170px;}
            table{border-collapse:collapse;width:100%;}
            th,td{border:1px solid #000;padding:4px 6px;}
            td{vertical-align:top;}
            th{background:#f2f2f2;text-align:center;vertical-align:middle;}
            .footer{margin-top:22px;text-align:right;}
            .footer .name{margin-top:46px;font-weight:700;text-decoration:underline;}
            .page-break{page-break-before:always;}
          </style></head><body>' . $bodyContent . '</body></html>';
    }

    private function resolveWordHeaderLogoAsset(): ?array
    {
        $candidates = [
            'images/Logo Kemnaker.png',
            'images/Logo.png',
        ];

        foreach ($candidates as $relativePath) {
            $fullPath = public_path($relativePath);
            if (!is_file($fullPath) || !is_readable($fullPath)) {
                continue;
            }
            $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                default => 'application/octet-stream',
            };

            $binary = @file_get_contents($fullPath);
            if ($binary === false) {
                continue;
            }

            return [
                'mime' => $mime,
                'binary' => $binary,
                'filename' => 'word-header-logo.' . ($ext !== '' ? $ext : 'png'),
            ];
        }

        return null;
    }

    private function buildWordMhtml(string $html, array $logoAsset): array
    {
        $boundary = '----=_NextPart_' . md5((string) microtime(true));
        $eol = "\r\n";

        $mhtml = 'MIME-Version: 1.0' . $eol;
        $mhtml .= 'Content-Type: multipart/related; boundary="' . $boundary . '"; type="text/html"' . $eol . $eol;

        $mhtml .= '--' . $boundary . $eol;
        $mhtml .= 'Content-Type: text/html; charset="utf-8"' . $eol;
        $mhtml .= 'Content-Transfer-Encoding: quoted-printable' . $eol;
        $mhtml .= 'Content-Location: file:///C:/document.html' . $eol . $eol;
        $mhtml .= quoted_printable_encode("\xEF\xBB\xBF" . $html) . $eol;

        $mhtml .= '--' . $boundary . $eol;
        $mhtml .= 'Content-Type: ' . ($logoAsset['mime'] ?? 'image/png') . $eol;
        $mhtml .= 'Content-Transfer-Encoding: base64' . $eol;
        $mhtml .= 'Content-Location: ' . ($logoAsset['filename'] ?? 'word-header-logo.png') . $eol;
        $mhtml .= 'Content-ID: <word-header-logo>' . $eol . $eol;
        $mhtml .= chunk_split(base64_encode((string) ($logoAsset['binary'] ?? ''))) . $eol;

        $mhtml .= '--' . $boundary . '--';

        return [$mhtml, $boundary];
    }

    private function buildWordTableHtml(array $table): string
    {
        $columns = (array) ($table['columns'] ?? []);
        $visibleIndexes = [];
        foreach ($columns as $index => $column) {
            $name = strtolower(trim((string) $column));
            if ($name === 'kode' || str_contains($name, 'koding')) {
                continue;
            }
            $visibleIndexes[] = $index;
        }
        if (empty($visibleIndexes)) {
            $visibleIndexes = array_keys($columns);
        }

        $columnMeta = [];
        foreach ($visibleIndexes as $index) {
            $label = trim((string) ($columns[$index] ?? ''));
            if ($label !== '' && str_contains($label, '::')) {
                [$parent, $child] = array_pad(explode('::', $label, 2), 2, '');
                $parent = trim((string) $parent);
                $child = trim((string) $child);
                if ($parent !== '') {
                    $columnMeta[] = [
                        'index' => $index,
                        'grouped' => true,
                        'parent' => $parent,
                        'child' => $child !== '' ? $child : 'Subkolom',
                        'label' => $label,
                    ];
                    continue;
                }
            }

            $columnMeta[] = [
                'index' => $index,
                'grouped' => false,
                'parent' => null,
                'child' => null,
                'label' => $label,
            ];
        }

        $hasGroupedColumns = collect($columnMeta)->contains(fn ($meta) => (bool) ($meta['grouped'] ?? false));
        $theadHtml = '';
        if ($hasGroupedColumns) {
            $topHeaderCells = [];
            $bottomHeaderCells = [];
            $currentGroupParent = null;
            $currentGroupSpan = 0;

            $flushGroup = function () use (&$topHeaderCells, &$currentGroupParent, &$currentGroupSpan): void {
                if ($currentGroupParent !== null && $currentGroupSpan > 0) {
                    $topHeaderCells[] = '<th colspan="' . $currentGroupSpan . '">' . e($currentGroupParent) . '</th>';
                }
                $currentGroupParent = null;
                $currentGroupSpan = 0;
            };

            foreach ($columnMeta as $meta) {
                if (!empty($meta['grouped'])) {
                    $parent = (string) ($meta['parent'] ?? '');
                    if ($currentGroupParent === $parent) {
                        $currentGroupSpan++;
                    } else {
                        $flushGroup();
                        $currentGroupParent = $parent;
                        $currentGroupSpan = 1;
                    }
                    $bottomHeaderCells[] = '<th>' . e((string) ($meta['child'] ?? '')) . '</th>';
                    continue;
                }

                $flushGroup();
                $topHeaderCells[] = '<th rowspan="2">' . e((string) ($meta['label'] ?? '')) . '</th>';
            }

            $flushGroup();
            $theadHtml = '<thead><tr>' . implode('', $topHeaderCells) . '</tr><tr>' . implode('', $bottomHeaderCells) . '</tr></thead>';
        } else {
            $columnsHtml = '';
            foreach ($visibleIndexes as $index) {
                $columnsHtml .= '<th>' . e((string) ($columns[$index] ?? '')) . '</th>';
            }
            $theadHtml = '<thead><tr>' . $columnsHtml . '</tr></thead>';
        }

        $isParameterColumnByIndex = [];
        foreach ($visibleIndexes as $index) {
            $raw = trim((string) ($columns[$index] ?? ''));
            $leaf = $raw;
            if ($raw !== '' && str_contains($raw, '::')) {
                $parts = explode('::', $raw, 2);
                $leaf = trim((string) ($parts[1] ?? $parts[0] ?? ''));
            }
            $isParameterColumnByIndex[$index] = strtolower($leaf) === 'parameter';
        }

        $rowsHtml = '';
        foreach (($table['rows'] ?? []) as $row) {
            $rowsHtml .= '<tr>';
            foreach ($visibleIndexes as $index) {
                $cellStyle = !empty($isParameterColumnByIndex[$index])
                    ? 'text-align:left;'
                    : 'text-align:center;';
                $rowsHtml .= '<td style="' . $cellStyle . '">' . e((string) ($row[$index] ?? '')) . '</td>';
            }
            $rowsHtml .= '</tr>';
        }

        return '<table>' . $theadHtml . '<tbody>' . $rowsHtml . '</tbody></table>';
    }

    private function tableHasMeaningfulRows(array $table): bool
    {
        $columns = array_values((array) ($table['columns'] ?? []));
        $rows = array_values((array) ($table['rows'] ?? []));
        if (empty($rows)) {
            return false;
        }

        $valueIndexes = [];
        foreach ($columns as $index => $column) {
            $name = strtolower(trim((string) $column));
            if ($name === 'kode' || str_contains($name, 'koding')) {
                continue;
            }
            if ($name === 'no' || $name === 'no.' || str_starts_with($name, 'no ')) {
                continue;
            }
            $valueIndexes[] = $index;
        }

        if (empty($valueIndexes)) {
            $valueIndexes = array_keys($columns);
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                $text = trim((string) $row);
                if ($text !== '') {
                    return true;
                }
                continue;
            }

            foreach ($valueIndexes as $idx) {
                $text = trim((string) ($row[$idx] ?? ''));
                if ($text !== '') {
                    return true;
                }
            }
        }

        return false;
    }

    private function appendNabColumnToIndirectTable(array $table, array $nabValues = []): array
    {
        $rawColumns = array_values((array) ($table['columns'] ?? []));
        $mappedColumns = array_map(function ($column) {
            $label = trim((string) $column);
            if ($this->isIndirectCategoryColumnLabel($label)) {
                return '';
            }
            if ($this->isNumberColumnLabel($label)) {
                return 'No';
            }
            if ($this->isParameterColumnLabel($label)) {
                return 'Parameter';
            }
            if ($this->isIndirectAnalysisColumnLabel($label)) {
                return 'Hasil Analisa';
            }
            if ($this->isNabColumnLabel($label)) {
                return 'NAB';
            }
            return $label;
        }, $rawColumns);
        $columns = array_values(array_filter($mappedColumns, fn ($label) => $label !== ''));
        $rows = array_values((array) ($table['rows'] ?? []));
        $parameterIndex = null;
        $nabIndex = null;

        foreach ($columns as $index => $column) {
            $label = strtolower(trim((string) $column));
            if ($label === 'parameter') {
                $parameterIndex = $index;
            }
            if ($this->isNabColumnLabel((string) $column)) {
                $nabIndex = $index;
            }
        }

        if ($parameterIndex === null) {
            $parameterIndex = 2;
        }

        if ($nabIndex === null) {
            $columns[] = 'NAB';
            $nabIndex = count($columns) - 1;
        }

        $normalizedNab = $this->sanitizeIndirectNabValues($nabValues);
        $normalizedRows = [];
        foreach ($rows as $row) {
            $sourceRow = is_array($row) ? array_values($row) : [];
            $clean = [];
            foreach ($columns as $column) {
                $sourceIndex = array_search($column, $mappedColumns, true);
                $clean[] = $sourceIndex === false ? '' : trim((string) ($sourceRow[$sourceIndex] ?? ''));
            }
            for ($i = count($clean); $i < count($columns); $i++) {
                $clean[] = '';
            }
            $paramName = trim((string) ($clean[$parameterIndex] ?? ''));
            $paramKey = $this->normalizeParameterKey($paramName);
            if ($paramKey !== '' && array_key_exists($paramKey, $normalizedNab)) {
                $clean[$nabIndex] = $normalizedNab[$paramKey];
            } else {
                $clean[$nabIndex] = trim((string) ($clean[$nabIndex] ?? ''));
            }
            $normalizedRows[] = array_slice($clean, 0, count($columns));
        }

        return [
            'columns' => $columns,
            'rows' => $normalizedRows,
        ];
    }

    private function isParameterColumnLabel(string $label): bool
    {
        $text = strtolower(trim($label));
        if (str_contains($text, '::')) {
            $parts = explode('::', $text, 2);
            $text = trim((string) ($parts[1] ?? $parts[0] ?? ''));
        }

        return $text === 'parameter';
    }

    private function isNumberColumnLabel(string $label): bool
    {
        $text = strtolower(trim($label));
        if (str_contains($text, '::')) {
            $parts = explode('::', $text, 2);
            $text = trim((string) ($parts[1] ?? $parts[0] ?? ''));
        }

        return $text === 'no' || $text === 'no.';
    }

    private function isIndirectCategoryColumnLabel(string $label): bool
    {
        $text = strtolower(trim($label));
        if (str_contains($text, '::')) {
            $parts = explode('::', $text, 2);
            $text = trim((string) ($parts[1] ?? $parts[0] ?? ''));
        }

        return $text === 'kategori';
    }

    private function isIndirectAnalysisColumnLabel(string $label): bool
    {
        $text = strtolower(trim($label));
        if (str_contains($text, '::')) {
            $parts = explode('::', $text, 2);
            $text = trim((string) ($parts[1] ?? $parts[0] ?? ''));
        }

        return $text === 'hasil analisa' || $text === 'kadar';
    }

    private function isNabColumnLabel(string $label): bool
    {
        $text = strtolower(trim($label));
        if ($text === '') {
            return false;
        }
        if (str_contains($text, '::')) {
            $parts = explode('::', $text, 2);
            $text = trim((string) ($parts[1] ?? $parts[0] ?? ''));
        }

        return $text === 'nab';
    }

    private function normalizeParameterKey(string $value): string
    {
        $text = trim(mb_strtolower($value));
        if ($text === '') {
            return '';
        }

        return preg_replace('/\s+/', ' ', $text) ?? $text;
    }

    private function ensureWorkflowStep(string $kode, string $nama, int $urutan): WorkflowStep
    {
        return WorkflowStep::firstOrCreate(
            ['kode' => $kode],
            ['nama' => $nama, 'urutan' => $urutan]
        );
    }

    public function showQcRevision(Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        $path = $permohonan->draftLhu?->qc_revision_file_path;
        if (!$path || !$this->fileExists($path)) {
            abort(404);
        }

        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            abort(404);
        }

        $fullPath = Storage::disk($disk)->path($path);
        $name = $permohonan->draftLhu?->qc_revision_file_name ?: basename($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $name . '"',
            ]);
        }

        return response()->download($fullPath, $name);
    }

    private function buildNoLab(Permohonan $permohonan): string
    {
        $sequence = $this->resolveGlobalPermohonanSequence($permohonan);
        $sequencePart = str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        $monthRoman = $this->toRomanMonth((int) Carbon::now()->format('n'));
        $yearPart = Carbon::now()->format('Y');

        return $sequencePart . '/' . $monthRoman . '/' . $yearPart;
    }

    private function resolveGlobalPermohonanSequence(Permohonan $permohonan): int
    {
        $sequence = Permohonan::query()
            ->where(function ($query) {
                $query->whereNull('status_global')
                    ->orWhere('status_global', '!=', 'cancelled');
            })
            ->where('id', '<=', $permohonan->id)
            ->count();

        return max(1, (int) $sequence);
    }

    private function toRomanMonth(int $month): string
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $map[$month] ?? 'I';
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
