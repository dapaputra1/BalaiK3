<?php

namespace App\Http\Controllers;

use App\Models\Pengujian;
use App\Models\PengujianDokumen;
use App\Models\PengujianDokumenFile;
use App\Models\PengujianDokumenParameter;
use App\Models\PengujianLokasi;
use App\Models\Permohonan;
use App\Models\PermohonanAssignment;
use App\Models\PermohonanStep;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PengujianController extends Controller
{
    private const PRIVATE_DISK = 'local';
    private const PENGUJIAN_UPLOAD_MAX_MB = 20;
    private const PENGUJIAN_UPLOAD_MAX_BYTES = self::PENGUJIAN_UPLOAD_MAX_MB * 1024 * 1024;

    public function index()
    {
        return $this->renderIndex(false);
    }

    public function revisionIndex()
    {
        return $this->renderIndex(true);
    }

    private function renderIndex(bool $revisionOnly = false)
    {
        $user = auth()->user();
        $role = $user?->role;
        $pengujianStep = WorkflowStep::where('kode', 'pengujian')->first();
        $verifikasiPcuStep = WorkflowStep::where('kode', 'verifikasi_pcu')->first();

        if (!in_array($role, ['pcu', 'superadmin'], true)) {
            abort(403);
        }

        $permohonanQuery = Permohonan::with([
            'company',
            'parameters.serviceParameter.category',
            'pengujian.lokasi.dokumen.files',
            'pengujian.lokasi.dokumen.parameters.serviceParameter',
            'assignments.user',
            'spt',
            'bap.items.pengujianDokumenParameter.serviceParameter.category',
            'steps',
        ])->whereNotNull('spt_sent_at');

        if ($revisionOnly) {
            $permohonanQuery->whereIn('status_dokumen', ['verifikasi_pcu', 'siap_bap']);
        } else {
            $permohonanQuery->where(function ($query) {
                $query->whereNull('status_dokumen')
                    ->orWhere('status_dokumen', 'pengujian')
                    // Fallback untuk data lama/inkonsisten: global sudah pengujian
                    // tapi status_dokumen belum ikut terbarui dari penjadwalan.
                    ->orWhere('status_global', 'pengujian');
            });
        }

        if ($role === 'pcu') {
            $permohonanQuery->whereHas('assignments', function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('role', 'pcu');
            });
        }

        $permohonans = $permohonanQuery->oldest()->get();

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($role, $revisionOnly, $pengujianStep, $verifikasiPcuStep) {
            $company = $permohonan->company;
            $stepMap = $permohonan->steps->keyBy('step_id');
            $summaryParamsSource = $permohonan->parameters
                ->filter(fn ($param) => !empty($param->service_parameter_id) && $param->status === 'approved');

            if ($summaryParamsSource->isEmpty()) {
                $summaryParamsSource = $permohonan->parameters
                    ->filter(fn ($param) => !empty($param->service_parameter_id) && $param->status !== 'rejected');
            }

            $targetStepId = $revisionOnly ? ($verifikasiPcuStep?->id ?? null) : ($pengujianStep?->id ?? null);
            $enteredAtUnix = $targetStepId
                ? optional($stepMap->get($targetStepId)?->started_at ?? $stepMap->get($targetStepId)?->updated_at)->timestamp
                : null;
            $allowedParams = $permohonan->parameters
                ->filter(fn ($param) => !empty($param->service_parameter_id) && $param->status !== 'rejected')
                ->map(function ($param) {
                    $categoryModel = $param->serviceParameter?->category;
                    $category = $categoryModel?->short_code ?: ($categoryModel?->name ?? '');
                    return [
                        'id' => (int) $param->service_parameter_id,
                        'name' => $param->parameter_name,
                        'category' => $category,
                        'qty' => (int) ($param->qty ?? 0),
                    ];
                })
                ->values();

            $reviewStatusMap = collect($permohonan->bap?->items ?? [])
                ->keyBy(fn ($item) => (int) ($item->pengujian_dokumen_parameter_id ?? 0));

            $existing = null;
            if ($permohonan->pengujian) {
                $existing = [
                    'id' => $permohonan->pengujian->id,
                    'status' => $permohonan->pengujian->status,
                    'lokasi' => $permohonan->pengujian->lokasi
                        ->sortBy('urutan')
                        ->map(function ($lokasi) use ($reviewStatusMap) {
                            return [
                                'id' => $lokasi->id,
                                'nama' => $lokasi->nama_lokasi,
                                'urutan' => $lokasi->urutan,
                                'dokumen' => $lokasi->dokumen
                                    ->sortBy('urutan')
                                    ->map(function ($dokumen) use ($reviewStatusMap) {
                                        return [
                                            'id' => $dokumen->id,
                                            'label' => $dokumen->label,
                                            'urutan' => $dokumen->urutan,
                                            'files' => $dokumen->files->map(function ($file) {
                                                $disk = $this->resolveDisk($file->file_path);
                                                return [
                                                    'id' => $file->id,
                                                    'name' => $file->original_name,
                                                    'path' => $file->file_path,
                                                    'url' => $disk ? route('pengujian.files.show', $file->id) : null,
                                                ];
                                            })->filter(fn ($file) => !empty($file['url']))->values(),
                                            'parameters' => $dokumen->parameters
                                                ->sortBy('urutan')
                                                ->map(function ($param) use ($reviewStatusMap) {
                                                    $categoryModel = $param->serviceParameter?->category;
                                                    $category = $categoryModel?->short_code ?: ($categoryModel?->name ?? '');
                                                    $parameterName = $param->serviceParameter?->name ?? '-';
                                                    $displayName = $category ? ($category . ' - ' . $parameterName) : $parameterName;
                                                    $reviewItem = $reviewStatusMap->get((int) $param->id);
                                                    $reviewStatus = (string) ($reviewItem?->review_status ?? '');
                                                    $reviewNote = trim((string) ($reviewItem?->catatan ?? ''));
                                                    return [
                                                        'id' => $param->id,
                                                        'parameter_id' => $param->service_parameter_id,
                                                        'parameter_name' => $displayName,
                                                        'qty' => $param->qty,
                                                        'is_direct' => (bool) $param->is_direct,
                                                        'is_sesuai' => (bool) $param->is_sesuai,
                                                        'review_status' => $reviewStatus !== '' ? $reviewStatus : null,
                                                        'catatan' => $reviewNote !== '' ? $reviewNote : null,
                                                        'urutan' => $param->urutan,
                                                    ];
                                                })
                                                ->values(),
                                        ];
                                    })
                                    ->values(),
                            ];
                        })
                        ->values(),
                ];
            }

            $sptSignedPath = $permohonan->spt?->signed_file_path;
            $sptUrl = $sptSignedPath ? route('dokumen-spt.signed', $permohonan) : null;
            $approvedParams = $summaryParamsSource
                ->map(function ($param) {
                    $categoryModel = $param->serviceParameter?->category;
                    $category = $categoryModel?->short_code ?: $categoryModel?->name;
                    $name = $param->parameter_name ?? '-';
                    return [
                        'name' => $category ? ($category . ' - ' . $name) : $name,
                        'qty' => $param->qty,
                    ];
                })
                ->values();
            $pcuNames = $permohonan->assignments
                ->where('role', 'pcu')
                ->map(fn ($a) => $a->user?->name)
                ->filter()
                ->values()
                ->all();
            $revisionItems = collect($permohonan->bap?->items ?? [])
                ->filter(fn ($item) => ($item->review_status ?? '') === 'revisi')
                ->map(function ($item) {
                    $param = $item->pengujianDokumenParameter;
                    $service = $param?->serviceParameter;
                    $category = $service?->category?->short_code ?: ($service?->category?->name ?? '-');
                    $name = $service?->name ?? '-';
                    return [
                        'param_id' => $param?->id,
                        'parameter' => $category && $category !== '-' ? ($category . ' - ' . $name) : $name,
                        'catatan' => trim((string) ($item->catatan ?? '')),
                    ];
                })
                ->unique(fn ($item) => ($item['param_id'] ?? 0) . '|' . ($item['catatan'] ?? ''))
                ->values();

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'lokasi' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                'masuk_at_unix' => $enteredAtUnix ?? optional($permohonan->spt_sent_at)->timestamp,
                'parameter' => $allowedParams,
                'existing' => $existing,
                'has_revision' => $revisionItems->isNotEmpty(),
                'ready_for_bap' => (string) ($permohonan->status_dokumen ?? '') === 'siap_bap',
                'pcu_verification_stage' => (string) ($permohonan->status_dokumen ?? '') === 'siap_bap'
                    ? 'sesuai'
                    : 'revisi',
                'revision_items' => $revisionItems->all(),
                'spt_url' => $sptUrl,
                'detail' => [
                    'kode' => $permohonan->kode,
                    'perusahaan' => $company?->company_name ?? '-',
                    'alamat' => $company?->company_address ?? '-',
                    'lokasi' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                    'jadwal_mulai' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? '-',
                    'jadwal_selesai' => optional($permohonan->jadwal_selesai)->format('Y-m-d') ?? '-',
                    'catatan_sampling' => $permohonan->jadwal_catatan ?: '-',
                    'pcu' => $pcuNames,
                    'parameter' => $approvedParams,
                ],
                'parameter_summary' => $approvedParams->all(),
            ];
        });

        $extraCategories = \App\Models\ServiceCategory::with(['parameters' => function ($query) {
            $query->where('is_active', 1)->orderBy('name');
        }])
            ->where('is_active', 1)
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'short_code' => $category->short_code,
                    'parameters' => $category->parameters->map(function ($parameter) {
                        return [
                            'id' => $parameter->id,
                            'name' => $parameter->name,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();

        $viewName = $revisionOnly
            ? 'admin.superadmin_verifikasi_pcu'
            : 'admin.superadmin_pengujian';

        return view($viewName, [
            'orders' => $orders,
            'extraCategories' => $extraCategories,
            'revisionOnly' => $revisionOnly,
            'pengujianUploadMaxMb' => self::PENGUJIAN_UPLOAD_MAX_MB,
        ]);
    }

    public function saveDraft(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        if (!$permohonan->spt_sent_at) {
            return response()->json(['message' => 'SPT belum diteruskan untuk pengujian.'], 422);
        }

        $payload = $this->parsePayload($request);
        if ($payload === null) {
            return response()->json(['message' => 'Payload pengujian tidak valid.'], 422);
        }
        if ($this->hasRejectedParametersInPayload($permohonan, $payload)) {
            return response()->json([
                'message' => 'Parameter yang ditolak saat kaji ulang tidak dapat dipilih pada pengujian.',
            ], 422);
        }
        $uploadError = $this->validateIncomingUploads($request);
        if ($uploadError) {
            return response()->json(['message' => $uploadError], 422);
        }
        if ($this->hasIncomingUploadedFiles($request) && !$this->hasMappedDokumenParameters($payload)) {
            return response()->json([
                'message' => 'Dokumen belum dapat disimpan. Lengkapi mapping parameter dan lokasi terlebih dahulu.',
            ], 422);
        }
        if ($sizeError = $this->validateIncomingFileSizes($request)) {
            return response()->json(['message' => $sizeError], 422);
        }

        $pengujian = DB::transaction(function () use ($permohonan, $payload, $request) {
            $pengujian = Pengujian::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                [
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]
            );

            $pengujian->update([
                'status' => 'draft',
                'updated_by' => auth()->id(),
            ]);

            $this->syncPengujian($pengujian, $payload, $request);
            $this->ensurePengujianStep($permohonan);

            return $pengujian;
        });

        return response()->json([
            'message' => 'Draft pengujian berhasil disimpan.',
            'pengujian_id' => $pengujian->id,
        ]);
    }

    public function submitToBap(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        if (!$permohonan->spt_sent_at) {
            return response()->json(['message' => 'SPT belum diteruskan untuk pengujian.'], 422);
        }

        $payload = $this->parsePayload($request);
        if ($payload === null) {
            return response()->json(['message' => 'Payload pengujian tidak valid.'], 422);
        }
        if ($this->hasRejectedParametersInPayload($permohonan, $payload)) {
            return response()->json([
                'message' => 'Parameter yang ditolak saat kaji ulang tidak dapat dipilih pada pengujian.',
            ], 422);
        }
        $uploadError = $this->validateIncomingUploads($request);
        if ($uploadError) {
            return response()->json(['message' => $uploadError], 422);
        }

        $missingMessage = $this->validateDokumenFiles($payload, $request);
        if ($missingMessage) {
            return response()->json(['message' => $missingMessage], 422);
        }

        $isRevisionResubmission = in_array((string) $permohonan->status_dokumen, ['verifikasi_pcu'], true)
            || $permohonan->bap()->whereHas('items', function ($query) {
                $query->where('review_status', 'revisi');
            })->exists();

        $result = DB::transaction(function () use ($permohonan, $payload, $request, $isRevisionResubmission) {
            $pengujian = Pengujian::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                [
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]
            );

            $pengujian->update([
                'status' => $isRevisionResubmission ? 'revision_resubmitted' : 'submitted',
                'sent_to_bap_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            $this->syncPengujian($pengujian, $payload, $request);
            if ($permohonan->bap && !$isRevisionResubmission) {
                $permohonan->bap->items()->update([
                    'review_status' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ]);
            }
            $this->ensurePengujianStep($permohonan);
            if ($isRevisionResubmission) {
                $this->transitionBackToVerifikasiPengujian($permohonan);
            } else {
                $this->transitionToBap($permohonan);
            }

            return $pengujian;
        });

        $role = auth()->user()?->role;
        $redirectUrl = null;
        if ($isRevisionResubmission) {
            if (in_array($role, ['superadmin', 'penyelia'], true)) {
                $routeName = $role . '.verifikasi-pengujian.index';
                if (Route::has($routeName)) {
                    $redirectUrl = route($routeName);
                }
            }
        } elseif (in_array($role, ['pcu', 'superadmin', 'admin'], true)) {
            $routeName = $role . '.bap.index';
            if (Route::has($routeName)) {
                $redirectUrl = route($routeName);
            }
        }

        return response()->json([
            'message' => $isRevisionResubmission
                ? 'Perbaikan pengujian berhasil dikirim ulang ke verifikasi pengujian.'
                : 'Pengujian berhasil dikirim ke BAP.',
            'pengujian_id' => $result->id,
            'next_stage' => $isRevisionResubmission ? 'verifikasi_pengujian' : 'bap',
            'redirect_url' => $redirectUrl,
        ]);
    }

    public function forwardToBap(Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        if ((string) ($permohonan->status_dokumen ?? '') !== 'siap_bap') {
            return response()->json([
                'message' => 'Data belum siap diteruskan ke BAP.',
            ], 422);
        }

        DB::transaction(function () use ($permohonan) {
            $this->transitionToBap($permohonan);
        });

        return response()->json([
            'message' => 'Data sesuai dari penyelia berhasil diteruskan ke BAP.',
        ]);
    }

    public function returnToPenjadwalan(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Alasan pengembalian ke penjadwalan wajib diisi.',
            'reason.max' => 'Alasan pengembalian maksimal 2000 karakter.',
        ]);

        $this->sendBackToPenjadwalan($permohonan, trim((string) $data['reason']), 'Pengujian');

        return response()->json([
            'message' => 'Permohonan berhasil dikembalikan ke penjadwalan.',
        ]);
    }

    public function downloadFile(PengujianDokumenFile $file)
    {
        $user = auth()->user();
        $role = $user?->role;

        if (!$user || !in_array($role, ['superadmin', 'pcu', 'penyelia', 'admin'], true)) {
            abort(403);
        }

        if ($role === 'pcu') {
            $permohonanId = $file->dokumen?->lokasi?->pengujian?->permohonan_id;
            if (!$permohonanId) {
                abort(404);
            }

            $hasAccess = PermohonanAssignment::where('permohonan_id', $permohonanId)
                ->where('user_id', $user->id)
                ->where('role', 'pcu')
                ->exists();

            if (!$hasAccess) {
                abort(403);
            }
        }

        $disk = $this->resolveDisk($file->file_path);
        if (!$file->file_path || $disk === null) {
            abort(404);
        }

        $filename = $file->original_name ?: 'file';
        $disposition = 'inline; filename="' . addslashes($filename) . '"';

        return Storage::disk($disk)->response($file->file_path, $filename, [
            'Content-Disposition' => $disposition,
        ]);
    }

    private function ensureAccess(Permohonan $permohonan): void
    {
        $user = auth()->user();
        $role = $user?->role;

        if ($role === 'superadmin') {
            return;
        }

        if ($role !== 'pcu') {
            abort(403);
        }

        $hasAccess = $permohonan->assignments()
            ->where('user_id', $user->id)
            ->where('role', 'pcu')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Anda tidak terdaftar sebagai PCU untuk permohonan ini.');
        }
    }

    private function parsePayload(Request $request): ?array
    {
        $raw = $request->input('payload');
        $payload = json_decode((string) $raw, true);

        if (!is_array($payload)) {
            return null;
        }

        if (!isset($payload['lokasi']) || !is_array($payload['lokasi'])) {
            return null;
        }

        return $payload;
    }

    private function syncPengujian(Pengujian $pengujian, array $payload, Request $request): void
    {
        $lokasiPayload = collect($payload['lokasi'] ?? []);
        $lokasiIds = $lokasiPayload->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
        $fileSyncGroups = [];

        $pengujian->lokasi()
            ->when(!empty($lokasiIds), function ($query) use ($lokasiIds) {
                $query->whereNotIn('id', $lokasiIds);
            }, function ($query) {
                $query->whereRaw('1 = 1');
            })
            ->get()
            ->each(function (PengujianLokasi $lokasi) {
                $lokasi->dokumen->each(function (PengujianDokumen $dokumen) {
                    $dokumen->files->each(function (PengujianDokumenFile $file) {
                        if ($file->file_path) {
                            Storage::disk('local')->delete($file->file_path);
                        }
                    });
                    $dokumen->delete();
                });
                $lokasi->delete();
            });

        foreach ($lokasiPayload as $index => $lokasiItem) {
            $lokasiId = isset($lokasiItem['id']) ? (int) $lokasiItem['id'] : null;
            $nama = trim((string) ($lokasiItem['nama'] ?? ''));
            if ($nama === '') {
                continue;
            }

            $lokasiModel = $lokasiId
                ? PengujianLokasi::where('pengujian_id', $pengujian->id)->where('id', $lokasiId)->first()
                : null;

            if (!$lokasiModel) {
                $lokasiModel = PengujianLokasi::create([
                    'pengujian_id' => $pengujian->id,
                    'nama_lokasi' => $nama,
                    'urutan' => $index + 1,
                ]);
            } else {
                $lokasiModel->update([
                    'nama_lokasi' => $nama,
                    'urutan' => $index + 1,
                ]);
            }

            $docPayload = collect($lokasiItem['dokumen'] ?? []);
            $docIds = $docPayload->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

            $lokasiModel->dokumen()
                ->when(!empty($docIds), function ($query) use ($docIds) {
                    $query->whereNotIn('id', $docIds);
                }, function ($query) {
                    $query->whereRaw('1 = 1');
                })
                ->get()
                ->each(function (PengujianDokumen $dokumen) {
                    $dokumen->files->each(function (PengujianDokumenFile $file) {
                        if ($file->file_path) {
                            Storage::disk('local')->delete($file->file_path);
                        }
                    });
                    $dokumen->delete();
                });

            foreach ($docPayload as $docIndex => $docItem) {
                $docId = isset($docItem['id']) ? (int) $docItem['id'] : null;
                $label = trim((string) ($docItem['label'] ?? ''));
                $docKey = (string) ($docItem['key'] ?? '');

                if ($label === '') {
                    $label = 'Dokumen ' . ($docIndex + 1);
                }

                $dokumenModel = $docId
                    ? PengujianDokumen::where('lokasi_id', $lokasiModel->id)->where('id', $docId)->first()
                    : null;

                if (!$dokumenModel) {
                    $dokumenModel = PengujianDokumen::create([
                        'lokasi_id' => $lokasiModel->id,
                        'label' => $label,
                        'urutan' => $docIndex + 1,
                    ]);
                } else {
                    $dokumenModel->update([
                        'label' => $label,
                        'urutan' => $docIndex + 1,
                    ]);
                }

                $dokumenModel->parameters()->delete();
                $paramPayload = collect($docItem['parameters'] ?? []);
                foreach ($paramPayload as $paramIndex => $paramItem) {
                    $paramId = isset($paramItem['parameter_id']) ? (int) $paramItem['parameter_id'] : null;
                    if (!$paramId) {
                        continue;
                    }
                    $isDirect = (bool) ($paramItem['is_direct'] ?? false);
                    $rawQty = $paramItem['qty'] ?? null;
                    $qty = $isDirect
                        ? max(0, (int) $rawQty)
                        : max(1, (int) ($rawQty ?? 1));
                    PengujianDokumenParameter::create([
                        'dokumen_id' => $dokumenModel->id,
                        'service_parameter_id' => $paramId,
                        'qty' => $qty,
                        'is_direct' => $isDirect,
                        'is_sesuai' => (bool) ($paramItem['is_sesuai'] ?? true),
                        'urutan' => $paramIndex + 1,
                    ]);
                }

                $keepFileIds = collect($docItem['existing_file_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values()
                    ->all();

                $groupKey = $docKey !== '' ? ('doc-key:' . $docKey) : ('doc-id:' . $dokumenModel->id);
                if (!isset($fileSyncGroups[$groupKey])) {
                    $fileSyncGroups[$groupKey] = [
                        'doc_key' => $docKey,
                        'keep_file_ids' => [],
                        'dokumen_ids' => [],
                    ];
                }
                $fileSyncGroups[$groupKey]['keep_file_ids'] = array_values(array_unique(array_merge(
                    $fileSyncGroups[$groupKey]['keep_file_ids'],
                    $keepFileIds
                )));
                $fileSyncGroups[$groupKey]['dokumen_ids'][] = $dokumenModel->id;
            }
        }

        foreach ($fileSyncGroups as $group) {
            $this->syncDokumenFileGroup(
                $pengujian,
                array_values(array_unique(array_map('intval', $group['dokumen_ids'] ?? []))),
                array_values(array_unique(array_map('intval', $group['keep_file_ids'] ?? []))),
                $this->getIncomingFiles($request, (string) ($group['doc_key'] ?? ''))
            );
        }
    }

    private function validateDokumenFiles(array $payload, Request $request): ?string
    {
        $missing = [];
        $lokasiPayload = $payload['lokasi'] ?? [];

        foreach ($lokasiPayload as $lokasiIndex => $lokasiItem) {
            $docPayload = $lokasiItem['dokumen'] ?? [];
            foreach ($docPayload as $docIndex => $docItem) {
                $label = trim((string) ($docItem['label'] ?? ''));
                if ($label === '') {
                    $label = 'Dokumen ' . ($docIndex + 1);
                }

                $keepFileIds = collect($docItem['existing_file_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values()
                    ->all();

                $docKey = (string) ($docItem['key'] ?? '');
                $incomingFiles = $this->getIncomingFiles($request, $docKey);
                $incomingCount = collect($incomingFiles)->filter()->count();

                $existingCount = PengujianDokumenFile::query()
                    ->whereIn('id', $keepFileIds)
                    ->get()
                    ->filter(fn (PengujianDokumenFile $file) => $this->resolveDisk($file->file_path))
                    ->count();

                if (($existingCount + $incomingCount) < 1) {
                    $missing[] = $label;
                }
            }
        }

        if (!$missing) {
            return null;
        }

        return 'Dokumen berikut belum diupload file: ' . implode(', ', $missing) . '. Minimal 1 file per dokumen.';
    }

    private function validateIncomingUploads(Request $request): ?string
    {
        foreach ($this->buildIncomingFileMap($request) as $files) {
            foreach ($files as $file) {
                if (($file->getSize() ?: 0) > self::PENGUJIAN_UPLOAD_MAX_BYTES) {
                    return 'Ukuran file maksimal ' . self::PENGUJIAN_UPLOAD_MAX_MB . ' MB per file.';
                }

                try {
                    SafeDocumentUpload::validatePengujianOrFail($file, 'files');
                } catch (ValidationException $exception) {
                    return collect($exception->errors())
                        ->flatten()
                        ->filter()
                        ->first() ?: 'File upload tidak valid.';
                }
            }
        }

        return null;
    }

    private function ensurePengujianStep(Permohonan $permohonan): void
    {
        $pengujianStep = WorkflowStep::where('kode', 'pengujian')->first();
        if (!$pengujianStep) {
            return;
        }

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $pengujianStep->id],
            ['status' => 'in_progress', 'started_at' => now()]
        );

        $updates = [];
        if (in_array($permohonan->status_global, [null, 'penjadwalan'], true)) {
            $updates['status_global'] = 'pengujian';
        }
        if (empty($permohonan->status_dokumen)) {
            $updates['status_dokumen'] = 'pengujian';
        }
        if (empty($permohonan->status_lab)) {
            $updates['status_lab'] = 'pengujian';
        }
        if ($updates) {
            $permohonan->update($updates);
        }
    }

    private function getIncomingFiles(Request $request, string $docKey): array
    {
        if ($docKey === '') {
            return [];
        }
        $metaMap = $this->buildIncomingFileMap($request);
        if (isset($metaMap[$docKey])) {
            return $metaMap[$docKey];
        }
        $files = $request->allFiles();
        $incoming = Arr::get($files, "files.$docKey", []);
        if ($incoming instanceof \Illuminate\Http\UploadedFile) {
            return [$incoming];
        }
        if (!is_array($incoming)) {
            return [];
        }
        $flatten = Arr::flatten($incoming);
        return array_values(array_filter($flatten, fn ($item) => $item instanceof \Illuminate\Http\UploadedFile));
    }

    private function buildIncomingFileMap(Request $request): array
    {
        static $cached = null;
        if (is_array($cached)) {
            return $cached;
        }
        $cached = [];
        $raw = $request->input('files_meta');
        if (!$raw) {
            return $cached;
        }
        $meta = json_decode((string) $raw, true);
        if (!is_array($meta)) {
            return $cached;
        }
        foreach ($meta as $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $field = (string) ($entry['field'] ?? '');
            $docKey = (string) ($entry['docKey'] ?? '');
            if ($field === '' || $docKey === '') {
                continue;
            }
            $file = $request->file($field);
            if (!$file instanceof \Illuminate\Http\UploadedFile) {
                continue;
            }
            if (!isset($cached[$docKey])) {
                $cached[$docKey] = [];
            }
            $cached[$docKey][] = $file;
        }
        return $cached;
    }

    private function syncDokumenFileGroup(Pengujian $pengujian, array $dokumenIds, array $keepFileIds, array $incomingFiles): void
    {
        if (empty($dokumenIds)) {
            return;
        }

        $dokumenModels = PengujianDokumen::with('files')
            ->whereIn('id', $dokumenIds)
            ->get()
            ->keyBy('id');

        if ($dokumenModels->isEmpty()) {
            return;
        }

        $sourceFiles = PengujianDokumenFile::query()
            ->whereIn('id', $keepFileIds)
            ->get()
            ->filter(fn (PengujianDokumenFile $file) => $this->resolveDisk($file->file_path))
            ->keyBy('id');

        foreach ($dokumenIds as $dokumenId) {
            /** @var PengujianDokumen|null $dokumenModel */
            $dokumenModel = $dokumenModels->get($dokumenId);
            if (!$dokumenModel) {
                continue;
            }

            $keepCurrentIds = $dokumenModel->files
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->intersect($sourceFiles->keys()->map(fn ($id) => (int) $id))
                ->values()
                ->all();

            $dokumenModel->files->each(function (PengujianDokumenFile $file) use ($keepCurrentIds) {
                if (!in_array((int) $file->id, $keepCurrentIds, true)) {
                    $this->deletePengujianFileRecord($file);
                }
            });

            foreach ($sourceFiles as $sourceFile) {
                if (in_array((int) $sourceFile->id, $keepCurrentIds, true)) {
                    continue;
                }
                $this->cloneStoredPengujianFile($sourceFile, $dokumenModel, (int) $pengujian->permohonan_id);
            }

            foreach ($incomingFiles as $upload) {
                if (!$upload instanceof UploadedFile) {
                    continue;
                }
                $this->storeUploadedPengujianFile($upload, $dokumenModel, (int) $pengujian->permohonan_id);
            }
        }
    }

    private function storeUploadedPengujianFile(UploadedFile $upload, PengujianDokumen $dokumenModel, int $permohonanId): void
    {
        if (($upload->getSize() ?? 0) > self::PENGUJIAN_UPLOAD_MAX_BYTES) {
            throw ValidationException::withMessages([
                'files' => 'Ukuran file pengujian maksimal ' . self::PENGUJIAN_UPLOAD_MAX_MB . ' MB per file.',
            ]);
        }

        SafeDocumentUpload::validatePengujianOrFail($upload, 'files');

        $realPath = $upload->getRealPath();
        if (!$realPath) {
            return;
        }

        $stream = fopen($realPath, 'rb');
        if (!is_resource($stream)) {
            return;
        }

        try {
            $path = $this->storePengujianFileStream(
                $stream,
                $permohonanId,
                $upload->getClientOriginalExtension() ?: $upload->extension()
            );
        } finally {
            fclose($stream);
        }

        if (!$path) {
            return;
        }

        PengujianDokumenFile::create([
            'dokumen_id' => $dokumenModel->id,
            'file_path' => $path,
            'original_name' => $upload->getClientOriginalName(),
            'mime' => $upload->getClientMimeType(),
            'size' => $upload->getSize() ?: 0,
            'uploaded_by' => auth()->id(),
        ]);
    }

    private function cloneStoredPengujianFile(PengujianDokumenFile $sourceFile, PengujianDokumen $dokumenModel, int $permohonanId): void
    {
        $disk = $this->resolveDisk($sourceFile->file_path);
        if (!$disk) {
            return;
        }

        $stream = Storage::disk($disk)->readStream($sourceFile->file_path);
        if (!is_resource($stream)) {
            return;
        }

        try {
            $path = $this->storePengujianFileStream(
                $stream,
                $permohonanId,
                pathinfo((string) $sourceFile->file_path, PATHINFO_EXTENSION)
            );
        } finally {
            fclose($stream);
        }

        if (!$path) {
            return;
        }

        PengujianDokumenFile::create([
            'dokumen_id' => $dokumenModel->id,
            'file_path' => $path,
            'original_name' => $sourceFile->original_name,
            'mime' => $sourceFile->mime,
            'size' => $sourceFile->size,
            'uploaded_by' => auth()->id(),
        ]);
    }

    private function storePengujianFileStream($stream, int $permohonanId, ?string $extension = null): ?string
    {
        $normalizedExtension = strtolower(trim((string) $extension, ". \t\n\r\0\x0B"));
        $filename = Str::random(40) . ($normalizedExtension !== '' ? '.' . $normalizedExtension : '');
        $path = 'pengujian/' . $permohonanId . '/' . $filename;

        return Storage::disk(self::PRIVATE_DISK)->put($path, $stream)
            ? $path
            : null;
    }

    private function deletePengujianFileRecord(PengujianDokumenFile $file): void
    {
        if ($file->file_path && $this->resolveDisk($file->file_path)) {
            Storage::disk(self::PRIVATE_DISK)->delete($file->file_path);
        }
        $file->delete();
    }

    private function transitionToBap(Permohonan $permohonan): void
    {
        $steps = WorkflowStep::whereIn('kode', ['pengujian', 'verifikasi_pcu', 'alur_bap'])->get()->keyBy('kode');
        $pengujianStep = $steps->get('pengujian');
        $verifikasiPcuStep = $steps->get('verifikasi_pcu');
        $bapStep = $steps->get('alur_bap');
        $sourceStep = in_array((string) ($permohonan->status_dokumen ?? ''), ['verifikasi_pcu', 'siap_bap'], true)
            ? ($verifikasiPcuStep ?: $pengujianStep)
            : $pengujianStep;
        $sourceLabel = $sourceStep?->kode === 'verifikasi_pcu' ? 'Verifikasi PCU' : 'Pengujian';

        if (!$sourceStep || !$bapStep) {
            return;
        }

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $sourceStep->id)
            ->update([
                'status' => 'approved',
                'note' => $sourceLabel . ' diteruskan ke BAP',
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $bapStep->id],
            ['status' => 'pending', 'started_at' => now()]
        );
        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $bapStep->id)
            ->update([
                'status' => 'pending',
                'started_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        $permohonan->update([
            'status_global' => 'alur_bap',
            'status_dokumen' => 'bap',
            'status_lab' => $permohonan->status_lab ?: 'pengujian',
        ]);
    }

    private function sendBackToPenjadwalan(Permohonan $permohonan, string $reason, string $source): void
    {
        $steps = WorkflowStep::whereIn('kode', ['penjadwalan', 'pengujian', 'verifikasi_pcu', 'alur_bap', 'verifikasi_pengujian'])
            ->get()
            ->keyBy('kode');

        $penjadwalanStep = $steps->get('penjadwalan');
        $message = 'Dikembalikan ke penjadwalan dari ' . $source . ': ' . $reason;

        DB::transaction(function () use ($permohonan, $steps, $penjadwalanStep, $message) {
            if ($permohonan->pengujian) {
                $permohonan->pengujian->update([
                    'status' => 'draft',
                    'sent_to_bap_at' => null,
                    'updated_by' => auth()->id(),
                ]);
            }

            $permohonan->update([
                'jadwal_sent_to_user_at' => null,
                'jadwal_user_approved_at' => null,
                'jadwal_user_approved_by' => null,
                'penjadwalan_sent_at' => null,
                'ma_approved_at' => null,
                'spt_sent_at' => null,
                'status_global' => 'penjadwalan',
                'status_dokumen' => 'penjadwalan',
                'status_lab' => 'penjadwalan',
            ]);

            foreach (['pengujian', 'verifikasi_pcu', 'alur_bap', 'verifikasi_pengujian'] as $kode) {
                $step = $steps->get($kode);
                if (!$step) {
                    continue;
                }

                PermohonanStep::where('permohonan_id', $permohonan->id)
                    ->where('step_id', $step->id)
                    ->update([
                        'status' => 'pending',
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]);
            }

            if ($penjadwalanStep) {
                PermohonanStep::updateOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $penjadwalanStep->id],
                    [
                        'status' => 'in_progress',
                        'note' => $message,
                        'started_at' => now(),
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }
        });
    }

    private function transitionToVerifikasiPengujian(Permohonan $permohonan): void
    {
        $steps = WorkflowStep::whereIn('kode', ['pengujian', 'verifikasi_pengujian'])->get()->keyBy('kode');
        $pengujianStep = $steps->get('pengujian');
        $verifStep = $steps->get('verifikasi_pengujian');

        if (!$pengujianStep || !$verifStep) {
            return;
        }

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $pengujianStep->id)
            ->update([
                'status' => 'approved',
                'note' => 'Pengujian diteruskan ke verifikasi pengujian',
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        PermohonanStep::updateOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $verifStep->id],
            [
                'status' => 'pending',
                'note' => null,
                'started_at' => now(),
                'finished_at' => null,
                'updated_by' => auth()->id(),
            ]
        );

        if ($permohonan->bap) {
            $permohonan->bap->update([
                'status' => 'draft',
                'sent_to_user_at' => null,
                'user_approved_at' => null,
                'user_approved_by' => null,
                'updated_by' => auth()->id(),
            ]);
        }

        $permohonan->update([
            'status_global' => 'verifikasi_pengujian',
            'status_dokumen' => 'verifikasi_pengujian',
            'status_lab' => 'menunggu_verifikasi_pengujian',
        ]);
    }

    private function transitionBackToVerifikasiPengujian(Permohonan $permohonan): void
    {
        $steps = WorkflowStep::whereIn('kode', ['pengujian', 'verifikasi_pcu', 'verifikasi_pengujian'])->get()->keyBy('kode');
        $pengujianStep = $steps->get('pengujian');
        $verifikasiPcuStep = $steps->get('verifikasi_pcu');
        $verifStep = $steps->get('verifikasi_pengujian');

        $sourceStep = (string) ($permohonan->status_dokumen ?? '') === 'verifikasi_pcu'
            ? ($verifikasiPcuStep ?: $pengujianStep)
            : $pengujianStep;

        if (!$sourceStep || !$verifStep) {
            return;
        }

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $sourceStep->id)
            ->update([
                'status' => 'approved',
                'note' => 'Perbaikan revisi penyelia dikirim ulang ke verifikasi pengujian',
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        PermohonanStep::updateOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $verifStep->id],
            [
                'status' => 'pending',
                'note' => 'Telah direvisi oleh PCU, perlu verifikasi kembali',
                'started_at' => now(),
                'finished_at' => null,
                'updated_by' => auth()->id(),
            ]
        );

        if ($permohonan->bap) {
            $permohonan->bap->update([
                'status' => 'revisi_selesai',
                'updated_by' => auth()->id(),
            ]);
        }

        $permohonan->update([
            'status_global' => 'verifikasi_pengujian',
            'status_dokumen' => 'verifikasi_pengujian',
            'status_lab' => 'menunggu_verifikasi_pengujian',
        ]);
    }

    private function hasMappedDokumenParameters(array $payload): bool
    {
        foreach (($payload['lokasi'] ?? []) as $lokasiItem) {
            foreach (($lokasiItem['dokumen'] ?? []) as $docItem) {
                foreach (($docItem['parameters'] ?? []) as $paramItem) {
                    $paramId = isset($paramItem['parameter_id']) ? (int) $paramItem['parameter_id'] : 0;
                    if ($paramId > 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function hasIncomingUploadedFiles(Request $request): bool
    {
        $metaMap = $this->buildIncomingFileMap($request);
        foreach ($metaMap as $files) {
            if (is_array($files) && count($files) > 0) {
                return true;
            }
        }
        return false;
    }

    private function resolveDisk(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            return self::PRIVATE_DISK;
        }
        return null;
    }

    private function validateIncomingFileSizes(Request $request): ?string
    {
        $maxBytes = self::PENGUJIAN_UPLOAD_MAX_BYTES;
        foreach ($this->buildIncomingFileMap($request) as $uploads) {
            foreach ($uploads as $upload) {
                if (!$upload) {
                    continue;
                }
                if (($upload->getSize() ?? 0) > $maxBytes) {
                    return 'Ukuran file pengujian maksimal ' . self::PENGUJIAN_UPLOAD_MAX_MB . ' MB per file.';
                }
            }
        }

        return null;
    }

    private function hasRejectedParametersInPayload(Permohonan $permohonan, array $payload): bool
    {
        $rejectedIds = $permohonan->parameters
            ->filter(fn ($param) => !empty($param->service_parameter_id) && $param->status === 'rejected')
            ->pluck('service_parameter_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $selectedIds = collect($payload['lokasi'] ?? [])
            ->flatMap(function ($lokasiItem) {
                return collect($lokasiItem['dokumen'] ?? [])
                    ->flatMap(function ($docItem) {
                        return collect($docItem['parameters'] ?? [])
                            ->pluck('parameter_id');
                    });
            })
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        return $selectedIds->intersect($rejectedIds)->isNotEmpty();
    }
}
