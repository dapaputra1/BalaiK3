<?php

namespace App\Http\Controllers;

use App\Models\Koding;
use App\Models\KodingItem;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\PengujianLokasi;
use App\Models\Prepanalisa;
use App\Models\PrepanalisaItem;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KodingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user?->role;
        if (!in_array($role, ['admin', 'superadmin'], true)) {
            abort(403);
        }

        $kodingStep = WorkflowStep::where('kode', 'koding')->first();
        $permohonanQuery = Permohonan::with([
            'company',
            'pengujian.lokasi.dokumen.files',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'koding.items',
        ]);
        if ($kodingStep) {
            $permohonanQuery->with(['steps' => function ($query) use ($kodingStep) {
                $query->where('step_id', $kodingStep->id)
                    ->orderByDesc('started_at');
            }]);
        }

        $permohonanQuery->where(function ($query) use ($kodingStep) {
            $query->where('status_lab', 'koding');
            if ($kodingStep) {
                $query->orWhereHas('steps', function ($stepQuery) use ($kodingStep) {
                    $stepQuery->where('step_id', $kodingStep->id)
                        ->whereIn('status', ['pending', 'in_progress']);
                });
            }
        });

        $permohonans = $permohonanQuery->oldest()->get();

        $seqMap = [];
        Permohonan::query()
            ->where(function ($query) {
                $query->whereNull('status_global')
                    ->orWhere('status_global', '!=', 'cancelled');
            })
            ->orderBy('id')
            ->pluck('id')
            ->values()
            ->each(function ($permohonanId, $idx) use (&$seqMap) {
                $seqMap[(int) $permohonanId] = $idx + 1;
            });

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($seqMap, $kodingStep) {
            $company = $permohonan->company;
            $pengujian = $permohonan->pengujian;
            $koding = $permohonan->koding;
            $userId = (int) ($permohonan->user_id ?? 0);
            $year = (int) (optional($permohonan->created_at)->format('y') ?? now()->format('y'));
            $seq = (int) ($seqMap[$permohonan->id] ?? 1);
            $userPart = str_pad((string) $userId, 3, '0', STR_PAD_LEFT);
            $yearPart = str_pad((string) $year, 2, '0', STR_PAD_LEFT);
            $seqPart = str_pad((string) $seq, 2, '0', STR_PAD_LEFT);

            $kodingMap = $koding ? $koding->items->keyBy('pengujian_dokumen_parameter_id') : collect();

            $lokasiRows = collect();
            $docFilesByLabelMethod = [];
            if ($pengujian) {
                $pengujian->lokasi->each(function ($lokasi) use (&$docFilesByLabelMethod) {
                    $lokasi->dokumen->each(function ($dokumen) use (&$docFilesByLabelMethod) {
                        $files = $dokumen->files->map(function ($file) {
                            return [
                                'id' => $file->id,
                                'name' => $file->original_name,
                                'url' => $file->file_path ? route('pengujian.files.show', $file->id) : null,
                            ];
                        })->values();
                        if ($files->isEmpty()) {
                            return;
                        }
                        $methods = $dokumen->parameters
                            ->map(fn ($param) => $param->is_direct ? 'Direct' : 'Indirect')
                            ->unique()
                            ->values();
                        $label = $dokumen->label ?? '';
                        foreach ($methods as $method) {
                            $key = $label . '::' . $method;
                            if (!isset($docFilesByLabelMethod[$key])) {
                                $docFilesByLabelMethod[$key] = $files->all();
                            }
                        }
                    });
                });
            }
            if ($pengujian) {
                $pengujian->lokasi->sortBy('urutan')->each(function ($lokasi) use (&$lokasiRows, $kodingMap, $docFilesByLabelMethod, $userPart, $yearPart, $seqPart) {
                    $lokasiPart = str_pad((string) ($lokasi->urutan ?? 0), 3, '0', STR_PAD_LEFT);
                    $dokumenRows = $lokasi->dokumen->sortBy('urutan')->flatMap(function ($dokumen) use ($kodingMap, $docFilesByLabelMethod, $userPart, $yearPart, $seqPart, $lokasiPart) {
                        $files = $dokumen->files->map(function ($file) {
                            return [
                                'id' => $file->id,
                                'name' => $file->original_name,
                                'url' => $file->file_path ? route('pengujian.files.show', $file->id) : null,
                            ];
                        })->values();

                        $params = $dokumen->parameters->sortBy('urutan');
                        if ($params->isEmpty()) {
                            return [[
                                'doc_id' => $dokumen->id,
                                'doc_label' => $dokumen->label,
                                'files' => $files,
                                'param_id' => null,
                                'param_category' => '-',
                                'param_name' => '-',
                                'method' => '-',
                                'kode' => '',
                                'has_param' => false,
                            ]];
                        }

                        return $params->map(function ($param) use ($dokumen, $files, $kodingMap, $docFilesByLabelMethod, $userPart, $yearPart, $seqPart, $lokasiPart) {
                            $service = $param->serviceParameter;
                            $category = $service?->category?->name ?? '-';
                            $name = $service?->name ?? '-';
                            $kode = '';
                            if ($kodingMap instanceof \Illuminate\Support\Collection) {
                                $item = $kodingMap->get($param->id);
                                $kode = $item?->kode ?? '';
                            }
                            $method = $param->is_direct ? 'Direct' : 'Indirect';
                            $catCode = $service?->category?->short_code ?? '';
                            $paramCode = $service?->short_code ?? '';
                            $prefix = implode('.', array_filter([$userPart, $yearPart, $seqPart]));
                            $suffix = implode('.', array_filter([$catCode, $lokasiPart, $paramCode]));
                            $baseCode = $suffix ? ($prefix . '/' . $suffix) : $prefix;
                            $finalFiles = $files;
                            if ($finalFiles->isEmpty()) {
                                $key = ($dokumen->label ?? '') . '::' . $method;
                                if (isset($docFilesByLabelMethod[$key])) {
                                    $finalFiles = collect($docFilesByLabelMethod[$key]);
                                }
                            }
                            return [
                                'doc_id' => $dokumen->id,
                                'doc_label' => $dokumen->label,
                                'files' => $finalFiles->values(),
                                'param_id' => $param->id,
                                'param_category' => $category,
                                'param_name' => $name,
                                'method' => $method,
                                'base_code' => $baseCode,
                                'is_indirect' => !$param->is_direct,
                                'kode' => $kode,
                                'has_param' => true,
                            ];
                        })->values();
                    })->values();

                    $lokasiRows->push([
                        'lokasi' => $lokasi->nama_lokasi,
                        'rows' => $dokumenRows,
                    ]);
                });
            }

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'masuk_at_unix' => optional(
                    $kodingStep
                        ? $permohonan->steps->firstWhere('step_id', $kodingStep->id)?->started_at
                        : null
                )->timestamp,
                'lokasi_induk' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                'total_lokasi' => $lokasiRows->count(),
                'lokasi_rows' => $lokasiRows->values(),
            ];
        });

        return view('admin.superadmin_koding', [
            'orders' => $orders,
        ]);
    }

    public function saveDraft(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        $payload = $this->parsePayload($request);

        if ($payload === null) {
            return response()->json([
                'message' => 'Payload koding tidak valid.',
            ], 422);
        }

        // Validasi nomor koding agar tidak duplikat
        $codeError = $this->validateKodingCodes(
            $permohonan,
            $payload['items']
        );

        if ($codeError) {
            return response()->json([
                'message' => $codeError,
            ], 422);
        }

        DB::transaction(function () use ($permohonan, $payload) {
            $koding = Koding::firstOrCreate(
                [
                    'permohonan_id' => $permohonan->id,
                ],
                [
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]
            );

            $koding->update([
                'status' => 'draft',
                'updated_by' => auth()->id(),
            ]);

            $this->syncExistingLocations(
                $permohonan,
                $payload['locations'] ?? []
            );

            $this->syncItems(
                $koding,
                $payload['items']
            );

            $this->ensureKodingStep($permohonan);
        });

        return response()->json([
            'message' => 'Koding berhasil disimpan.',
        ]);
    }

    public function submitToPrepanalisa(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        $payload = $this->parsePayload($request);

        if ($payload === null) {
            return response()->json([
                'message' => 'Payload koding tidak valid.',
            ], 422);
        }

        $codeError = $this->validateKodingCodes(
            $permohonan,
            $payload['items']
        );

        if ($codeError) {
            return response()->json([
                'message' => $codeError,
            ], 422);
        }

        $missingMessage = $this->validateKodingItems(
            $permohonan,
            $payload['items']
        );

        if ($missingMessage) {
            return response()->json([
                'message' => $missingMessage,
            ], 422);
        }

        $hasIndirect = $this->hasIndirectParameters($permohonan);
        $hasDirect = $this->hasDirectParameters($permohonan);

        DB::transaction(function () use ($permohonan, $payload, $hasIndirect, $hasDirect) {
            $koding = Koding::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                ['status' => 'draft', 'created_by' => auth()->id()]
            );

            $koding->update([
                'status' => 'submitted',
                'sent_to_prepanalisa_at' => now(),
                'updated_by' => auth()->id(),
            ]);

            $this->syncExistingLocations($permohonan, $payload['locations'] ?? []);
            $this->syncItems($koding, $payload['items']);
            if ($hasIndirect) {
                $this->ensurePrepanalisaItems($permohonan, $koding);
                $this->transitionToPrepanalisa($permohonan);
            } else {
                $this->transitionToDraftLhu($permohonan);
            }

            if ($hasDirect && $hasIndirect) {
                $this->ensureDraftLhuStepPending($permohonan);
            }
        });

        $message = 'Koding direct selesai, diteruskan ke draft LHU.';
        if ($hasIndirect && $hasDirect) {
            $message = 'Koding berhasil diteruskan ke preparasi analisa. Draft LHU untuk direct sudah dapat dikerjakan.';
        } elseif ($hasIndirect) {
            $message = 'Koding berhasil diteruskan ke preparasi analisa.';
        }

        return response()->json(['message' => $message]);
    }

    private function ensureAccess(): void
    {
        $user = auth()->user();
        $role = $user?->role;
        if (!in_array($role, ['admin', 'superadmin'], true)) {
            abort(403);
        }
    }

    private function parsePayload(Request $request): ?array
    {
        $raw = $request->input('payload');
        $payload = json_decode((string) $raw, true);

        if (!is_array($payload) || !isset($payload['items']) || !is_array($payload['items'])) {
            return null;
        }

        return $payload;
    }

    private function syncExistingLocations(Permohonan $permohonan, array $locations): void
    {
        if (!$locations) {
            return;
        }

        $pengujianId = (int) ($permohonan->pengujian?->id ?? 0);
        if (!$pengujianId) {
            return;
        }

        $locationMap = collect($locations)
            ->mapWithKeys(function ($location) {
                $id = (int) ($location['id'] ?? 0);
                $name = trim((string) ($location['name'] ?? ''));
                if (!$id || $name === '') {
                    return [];
                }
                return [$id => $name];
            });

        if ($locationMap->isEmpty()) {
            return;
        }

        PengujianLokasi::query()
            ->where('pengujian_id', $pengujianId)
            ->whereIn('id', $locationMap->keys()->all())
            ->get()
            ->each(function (PengujianLokasi $lokasi) use ($locationMap) {
                $name = trim((string) $locationMap->get((int) $lokasi->id, ''));
                if ($name === '' || $name === (string) $lokasi->nama_lokasi) {
                    return;
                }

                $lokasi->update([
                    'nama_lokasi' => $name,
                ]);
            });
    }

    private function syncItems(Koding $koding, array $items): void
    {
        $ids = collect($items)->pluck('pengujian_dokumen_parameter_id')->filter()->map(fn ($id) => (int) $id)->values();
        $existing = $koding->items()->whereNotIn('pengujian_dokumen_parameter_id', $ids)->get();
        $existing->each->delete();

        foreach ($items as $item) {
            $paramId = (int) ($item['pengujian_dokumen_parameter_id'] ?? 0);
            if (!$paramId) {
                continue;
            }
            $docId = null;
            $docParam = \App\Models\PengujianDokumenParameter::find($paramId);
            if ($docParam) {
                $docId = $docParam->dokumen_id;
            }
            KodingItem::updateOrCreate(
                [
                    'koding_id' => $koding->id,
                    'pengujian_dokumen_parameter_id' => $paramId,
                ],
                [
                    'pengujian_dokumen_id' => $docId,
                    'kode' => trim((string) ($item['kode'] ?? '')),
                ]
            );
        }
    }

    private function ensureKodingStep(Permohonan $permohonan): void
    {
        $kodingStep = WorkflowStep::where('kode', 'koding')->first();
        if (!$kodingStep) {
            return;
        }

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $kodingStep->id],
            ['status' => 'in_progress', 'started_at' => now()]
        );
    }

    private function transitionToPrepanalisa(Permohonan $permohonan): void
    {
        $steps = WorkflowStep::whereIn('kode', ['koding', 'preparasi_analisa'])->get()->keyBy('kode');
        $kodingStep = $steps->get('koding');
        $prepanalisaStep = $steps->get('preparasi_analisa');

        if (!$kodingStep || !$prepanalisaStep) {
            return;
        }

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $kodingStep->id)
            ->update([
                'status' => 'approved',
                'note' => 'Koding diteruskan ke preparasi analisa',
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $prepanalisaStep->id],
            ['status' => 'pending', 'started_at' => now()]
        );

        $permohonan->update([
            'status_global' => 'preparasi_analisa',
            'status_lab' => 'preparasi_analisa',
        ]);
    }

    private function transitionToDraftLhu(Permohonan $permohonan): void
    {
        $steps = WorkflowStep::whereIn('kode', ['koding', 'pembuatan_lhu'])->get()->keyBy('kode');
        $kodingStep = $steps->get('koding');
        $draftStep = $steps->get('pembuatan_lhu');

        if (!$kodingStep || !$draftStep) {
            return;
        }

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $kodingStep->id)
            ->update([
                'status' => 'approved',
                'note' => 'Koding selesai (direct), lanjut ke draft LHU',
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $draftStep->id],
            ['status' => 'pending', 'started_at' => now()]
        );

        $permohonan->update([
            'status_global' => 'pembuatan_lhu',
            'status_lab' => 'pembuatan_lhu',
        ]);
    }

    private function ensureDraftLhuStepPending(Permohonan $permohonan): void
    {
        $draftStep = WorkflowStep::where('kode', 'pembuatan_lhu')->first();
        if (!$draftStep) {
            return;
        }

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $draftStep->id],
            ['status' => 'pending', 'started_at' => now()]
        );
    }

    private function ensurePrepanalisaItems(Permohonan $permohonan, Koding $koding): void
    {
        $prepanalisa = Prepanalisa::firstOrCreate(
            ['permohonan_id' => $permohonan->id],
            ['status' => 'draft', 'created_by' => auth()->id()]
        );

        $prepanalisa->update([
            'status' => 'draft',
            'updated_by' => auth()->id(),
        ]);

        $koding->loadMissing('items.pengujianDokumenParameter.serviceParameter');

        foreach ($koding->items as $item) {
            $docParam = $item->pengujianDokumenParameter;
            if (!$docParam) {
                continue;
            }
            if ($docParam->is_direct) {
                continue;
            }
            $serviceParamId = $docParam->serviceParameter?->id;
            if (!$serviceParamId) {
                continue;
            }
            PrepanalisaItem::updateOrCreate(
                [
                    'prepanalisa_id' => $prepanalisa->id,
                    'koding_item_id' => $item->id,
                    'pengujian_dokumen_parameter_id' => $docParam->id,
                ],
                [
                    'service_parameter_id' => $serviceParamId,
                    'kode_koding' => $item->kode,
                    'updated_by' => auth()->id(),
                    'created_by' => auth()->id(),
                ]
            );
        }
    }

    private function validateKodingCodes(
        Permohonan $permohonan,
        array $items
    ): ?string {
        $codes = collect($items)
            ->map(function ($item) {
                return trim((string) ($item['kode'] ?? ''));
            })
            ->filter()
            ->values();

        if ($codes->isEmpty()) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Cek duplikat dalam permohonan yang sama
        |--------------------------------------------------------------------------
        */

        $duplicateCodes = $codes
            ->countBy()
            ->filter(function ($count) {
                return $count > 1;
            })
            ->keys()
            ->values();

        if ($duplicateCodes->isNotEmpty()) {
            return 'Nomor koding tidak boleh sama dalam satu permohonan: '
                . $duplicateCodes->implode(', ')
                . '.';
        }

        /*
        |--------------------------------------------------------------------------
        | Cek apakah nomor sudah digunakan permohonan lain
        |--------------------------------------------------------------------------
        */

        $currentKodingId = Koding::where(
            'permohonan_id',
            $permohonan->id
        )->value('id');

        $query = KodingItem::query()
            ->whereIn('kode', $codes->all());

        if ($currentKodingId) {
            $query->where(
                'koding_id',
                '!=',
                $currentKodingId
            );
        }

        $usedCodes = $query
            ->pluck('kode')
            ->filter()
            ->unique()
            ->values();

        if ($usedCodes->isNotEmpty()) {
            return 'Nomor koding berikut sudah digunakan pada sampel lain: '
                . $usedCodes->implode(', ')
                . '.';
        }

        return null;
    }

    private function validateKodingItems(Permohonan $permohonan, array $items): ?string
    {
        $kodeByDoc = collect($items)
            ->filter(fn ($item) => !empty($item['pengujian_dokumen_parameter_id']))
            ->mapWithKeys(function ($item) {
                $docId = (int) ($item['pengujian_dokumen_parameter_id'] ?? 0);
                $kode = trim((string) ($item['kode'] ?? ''));
                return [$docId => $kode];
            });

        $permohonan->loadMissing('pengujian.lokasi.dokumen.parameters.serviceParameter');
        $missing = [];

        $lokasiList = $permohonan->pengujian?->lokasi ?? collect();
        foreach ($lokasiList as $lokasi) {
            foreach ($lokasi->dokumen as $dokumen) {
                foreach ($dokumen->parameters as $param) {
                    $kode = $kodeByDoc->get($param->id, '');
                    if ($kode === '') {
                        $label = $dokumen->label ?: 'Dokumen';
                        $lokasiName = $lokasi->nama_lokasi ?: '-';
                        $paramName = $param->serviceParameter?->name ?? '-';
                        $missing[] = $label . ' - ' . $paramName . ' (' . $lokasiName . ')';
                    }
                }
            }
        }

        if (!$missing) {
            return null;
        }

        return 'Masih ada koding kosong. Lengkapi semua ID koding sebelum lanjut.';
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
}