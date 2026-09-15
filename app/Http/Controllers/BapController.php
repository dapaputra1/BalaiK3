<?php

namespace App\Http\Controllers;

use App\Models\Bap;
use App\Models\BapItem;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class BapController extends Controller
{
    private const PRIVATE_DISK = 'local';

    public function index()
    {
        $user = auth()->user();
        $role = $user?->role;
        if (!in_array($role, ['admin', 'superadmin', 'pcu'], true)) {
            abort(403);
        }

        $permohonanQuery = Permohonan::with([
            'company',
            'parameters.serviceParameter.category',
            'assignments.user',
            'dokumenPenawaran',
            'spt',
            'draftLhu',
            'pengujian.lokasi.dokumen.files',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'bap.items',
            'steps',
        ])
            ->where(function ($query) {
                $query->where(function ($bapActiveQuery) {
                    $bapActiveQuery->where('status_global', 'alur_bap')
                        ->whereIn('status_dokumen', ['bap', 'menunggu_persetujuan_bap']);
                })->orWhereHas('bap', function ($bapQuery) {
                    $bapQuery->whereNotNull('user_approved_at');
                });
            });

        if ($role === 'pcu') {
            $permohonanQuery->whereHas('assignments', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('role', 'pcu');
            });
        }

        $bapStep = WorkflowStep::where('kode', 'alur_bap')->first();
        $permohonans = $permohonanQuery->get()
            ->sortByDesc(function (Permohonan $permohonan) use ($bapStep) {
                $stepMap = $permohonan->steps->keyBy('step_id');
                $bapEnteredAt = $bapStep
                    ? optional($stepMap->get($bapStep->id)?->started_at ?? $stepMap->get($bapStep->id)?->updated_at)->timestamp
                    : null;
                return $bapEnteredAt
                    ?? optional($permohonan->pengujian?->sent_to_bap_at)->timestamp
                    ?? optional($permohonan->updated_at)->timestamp
                    ?? optional($permohonan->created_at)->timestamp
                    ?? 0;
            })
            ->values();
        $self = $this;

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($self, $bapStep) {
            $company = $permohonan->company;
            $pengujian = $permohonan->pengujian;
            $bap = $permohonan->bap;
            $stepMap = $permohonan->steps->keyBy('step_id');
            $bapEnteredAtUnix = $bapStep
                ? optional($stepMap->get($bapStep->id)?->started_at ?? $stepMap->get($bapStep->id)?->updated_at)->timestamp
                : null;
            $statusGlobal = $permohonan->status_global ?? 'disposisi';
            $authoritySame = (bool) ($company?->authority_same ?? false);
            $jabatanPenandatangan = trim((string) ($company?->authority_role ?? ''));
            if ($jabatanPenandatangan === '') {
                $jabatanPenandatangan = $authoritySame ? 'Penanggung Jawab' : '-';
            }

            $penawaran = $self->aggregatePenawaran($permohonan);
            $pengujianDetail = $self->aggregatePengujian($permohonan, $penawaran);
            $penawaranItems = $penawaran['items'];
            $pengujianItems = $pengujianDetail['items'];
            $subtotalPenawaran = $penawaran['subtotal'];
            $subtotalPengujian = $pengujianDetail['subtotal'];
            $hasPerubahan = $self->hasParameterDifference($penawaranItems, $pengujianItems);
            $rincianAktif = $hasPerubahan ? $pengujianItems : $penawaranItems;

            $dokumenPenawaran = $permohonan->dokumenPenawaran
                ->whereNotIn('status', ['draft'])
                ->sortByDesc('created_at')
                ->map(function ($doc) {
                    $path = $doc->signed_file_path ?: $doc->file_path;
                    return [
                        'nama' => 'Surat Penawaran',
                        'url' => $path ? route('penawaran.documents.show', $doc) : null,
                    ];
                })
                ->values()
                ->all();

            $paramOrder = $penawaranItems->map(function ($item) {
                return [
                    'nama' => $item['nama'] ?? '-',
                    'kategori' => $item['kategori'] ?? '-',
                    'qty' => (int) ($item['qty'] ?? 0),
                ];
            })->values();

            $pengujianParams = collect();
            $lokasiRows = collect();
            if ($pengujian) {
                $catatanMap = $bap
                    ? $bap->items->keyBy('pengujian_dokumen_parameter_id')
                    : collect();

                $lokasiMap = [];
                $mergeDocLists = function ($existingDocs, $newDocs) {
                    $docMap = [];
                    foreach ($existingDocs as $doc) {
                        $key = isset($doc['id']) && $doc['id']
                            ? 'id:' . (string) $doc['id']
                            : strtolower(trim((string) ($doc['nama'] ?? 'dokumen')));
                        if ($key === '') {
                            $key = 'dokumen';
                        }
                        $docMap[$key] = $doc;
                    }
                    foreach ($newDocs as $doc) {
                        $key = isset($doc['id']) && $doc['id']
                            ? 'id:' . (string) $doc['id']
                            : strtolower(trim((string) ($doc['nama'] ?? 'dokumen')));
                        if ($key === '') {
                            $key = 'dokumen';
                        }
                        if (!isset($docMap[$key])) {
                            $docMap[$key] = $doc;
                            continue;
                        }
                        $existing = $docMap[$key];
                        $mergedFiles = collect($existing['files'] ?? [])
                            ->merge($doc['files'] ?? [])
                            ->unique(function ($file) {
                                return $file['id'] ?? ($file['url'] ?? $file['name'] ?? '');
                            })
                            ->values();
                        $mergedParams = collect($existing['parameter'] ?? [])
                            ->merge($doc['parameter'] ?? [])
                            ->unique(function ($param) {
                                $id = $param['id'] ?? null;
                                if ($id) {
                                    return 'id:' . $id;
                                }
                                $name = strtolower(trim((string) ($param['nama'] ?? '')));
                                $direct = !empty($param['is_direct']) ? 'direct' : 'indirect';
                                return 'name:' . $name . ':' . $direct;
                            })
                            ->values();
                        $docMap[$key] = array_merge($existing, [
                            'files' => $mergedFiles,
                            'parameter' => $mergedParams,
                        ]);
                    }
                    return collect(array_values($docMap));
                };
                $pengujian->lokasi->sortBy('urutan')->each(function ($lokasi) use (&$lokasiRows, &$pengujianParams, $catatanMap, &$lokasiMap, $mergeDocLists, $self) {
                    $dokumenList = $lokasi->dokumen->sortBy('urutan')->map(function ($dokumen) use (&$pengujianParams, $catatanMap, $self) {
                        $params = $dokumen->parameters->sortBy('urutan')->map(function ($param) use (&$pengujianParams, $catatanMap) {
                            $categoryName = $param->serviceParameter?->category?->name ?? '-';
                            $pengujianParams->push([
                                'nama' => $param->serviceParameter?->name ?? '-',
                                'kategori' => $categoryName,
                                'qty' => $param->qty,
                                'sesuai' => (bool) $param->is_sesuai,
                                'is_direct' => (bool) $param->is_direct,
                            ]);

                            $catatan = '';
                            if ($catatanMap instanceof \Illuminate\Support\Collection) {
                                $item = $catatanMap->get($param->id);
                                $catatan = $item?->catatan ?? '';
                            }

                            return [
                                'id' => $param->id,
                                'nama' => $param->serviceParameter?->name ?? '-',
                                'kategori' => $categoryName,
                                'qty' => $param->qty,
                                'sesuai' => (bool) $param->is_sesuai,
                                'is_direct' => (bool) $param->is_direct,
                                'catatan' => $catatan,
                            ];
                        })->values();

                        $docFiles = $dokumen->files->map(function ($file) use ($self) {
                            $disk = $self->resolveDisk($file->file_path);
                            return [
                                'id' => $file->id,
                                'name' => $file->original_name,
                                'url' => $disk ? route('pengujian.files.show', $file->id) : null,
                            ];
                        })->filter(fn ($file) => !empty($file['url']))->values();
                        return [
                            'id' => $dokumen->id,
                            'nama' => $dokumen->label,
                            'files' => $docFiles->values(),
                            'parameter' => $params,
                        ];
                    })->values();

                    $lokasiKey = trim((string) $lokasi->nama_lokasi);
                    if ($lokasiKey === '') {
                        $lokasiKey = 'Lokasi';
                    }

                    if (isset($lokasiMap[$lokasiKey])) {
                        $existingIndex = $lokasiMap[$lokasiKey];
                        $existing = $lokasiRows->get($existingIndex);
                        $mergedDocs = ($existing && is_array($existing))
                            ? $mergeDocLists($existing['dokumen_list'] ?? [], $dokumenList)
                            : collect($dokumenList);
                        $lokasiRows->put($existingIndex, array_merge($existing, [
                            'dokumen_list' => $mergedDocs,
                        ]));
                        return;
                    }

                    $lokasiMap[$lokasiKey] = $lokasiRows->count();
                    $lokasiRows->push([
                        'lokasi' => $lokasi->nama_lokasi,
                        'dokumen_list' => $dokumenList,
                    ]);
                });
            }

            $assignments = $permohonan->assignments;
            $pcu = $assignments->where('role', 'pcu')->pluck('user.name')->filter()->values()->all();
            $analis = [];

            $ketuaTim = $assignments->where('role', 'pcu')->firstWhere('is_leader', true)
                ?? $assignments->where('role', 'pcu')->first();
            $ketuaTimNama = $ketuaTim?->user?->name ?? '-';
            $ketuaTimTtd = $this->signatureDataUrl($ketuaTim?->user?->signature_path);
            $penanggungTtd = ($bap?->user_approved_at && $company?->responsible_signature_path)
                ? route('permohonan.signature', $permohonan)
                : '';

            $bapStatusLabel = 'Belum mengirim BAP';
            if ($bap?->user_approved_at) {
                $bapStatusLabel = 'Sudah disetujui';
            } elseif ($bap?->sent_to_user_at) {
                $bapStatusLabel = 'Menunggu persetujuan';
            }

            $rolePrefix = auth()->user()?->role === 'pcu'
                ? 'pcu'
                : (auth()->user()?->role === 'admin' ? 'admin' : 'superadmin');
            $draft = $permohonan->draftLhu;
            $dokumenArsip = collect($dokumenPenawaran);

            if (!empty($permohonan->spt?->signed_file_path)) {
                $dokumenArsip->push([
                    'nama' => 'Dokumen SPT',
                    'url' => $this->routeIfExists($rolePrefix . '.dokumen-spt.preview', $permohonan),
                ]);
            }

            if ($bap?->sent_to_user_at || $bap?->user_approved_at) {
                $dokumenArsip->push([
                    'nama' => 'Dokumen BAP',
                    'action' => 'bap',
                ]);
                $dokumenArsip->push([
                    'nama' => 'Dokumen Rincian Pengambilan Sampel',
                    'action' => 'rincian',
                ]);
                if ($hasPerubahan) {
                    $dokumenArsip->push([
                        'nama' => 'Form Penambahan/Pengurangan Pengujian',
                        'action' => 'penambahan',
                    ]);
                }
            }

            if (!empty($draft?->surat_tagihan_generated_at)) {
                $dokumenArsip->push([
                    'nama' => 'Surat Tagihan',
                    'url' => $this->routeIfExists($rolePrefix . '.surat-tagihan.show', $permohonan),
                ]);
            }

            if (!empty($draft?->billing_file_path) && !empty($draft?->billing_sent_at)) {
                $dokumenArsip->push([
                    'nama' => 'Kode Billing',
                    'url' => $this->routeIfExists($rolePrefix . '.billing.show', $permohonan),
                ]);
            }

            if (!empty($draft?->billing_verified_at) && !empty($draft?->invoice_generated_at)) {
                $invoiceUrl = !empty($draft?->invoice_file_path)
                    ? $this->routeIfExists($rolePrefix . '.invoice.signed.show', $permohonan)
                    : $this->routeIfExists($rolePrefix . '.invoice.show', $permohonan);
                $dokumenArsip->push([
                    'nama' => 'Kuitansi',
                    'url' => $invoiceUrl,
                ]);
            }

            if (!empty($draft?->final_file_path)) {
                $dokumenArsip->push([
                    'nama' => 'Draft LHU Final',
                    'url' => $this->routeIfExists($rolePrefix . '.draft-lhu.final.show', $permohonan),
                ]);
            }

            if (!empty($draft?->qc_revision_file_path)) {
                $dokumenArsip->push([
                    'nama' => 'Catatan Revisi QC LHU',
                    'url' => $this->routeIfExists($rolePrefix . '.draft-lhu.qc-revision.show', $permohonan),
                ]);
            }

            if (!empty($draft?->signed_file_path)) {
                $dokumenArsip->push([
                    'nama' => 'LHU Bertanda Tangan',
                    'url' => $this->routeIfExists($rolePrefix . '.penyerahan-lhu.signed.show', $permohonan),
                ]);
            }

            $dokumenArsip = $dokumenArsip
                ->filter(fn ($doc) => !empty($doc['action']) || !empty($doc['url']))
                ->unique(function ($doc) {
                    if (!empty($doc['action'])) {
                        return 'action:' . $doc['action'];
                    }
                    return 'url:' . ($doc['url'] ?? ($doc['nama'] ?? ''));
                })
                ->values();

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'masuk_at_unix' => $bapEnteredAtUnix ?? optional($pengujian?->sent_to_bap_at)->timestamp,
                'admin_unread' => (bool) ($bap?->user_approved_at && !$bap?->admin_viewed_at),
                'lokasi' => $lokasiRows->pluck('lokasi')->filter()->implode(', ') ?: ($permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-')),
                'tanggal_pengujian' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? '-',
                'tanggal_pengujian_mulai' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? '-',
                'tanggal_pengujian_selesai' => optional($permohonan->jadwal_selesai)->format('Y-m-d') ?? '-',
                'tanggal_permohonan' => optional($permohonan->created_at)->format('d M Y') ?? '-',
                'status_label' => $self->mapStatusToLabel($statusGlobal),
                'tahap' => $self->mapStatusToTahap($statusGlobal),
                'progress' => $self->mapStatusToProgress($statusGlobal),
                'ketua_pcu' => $ketuaTimNama !== '-' ? [$ketuaTimNama] : [],
                'pcu' => $pcu,
                'analis' => $analis,
                'penanggung_jawab' => $company?->responsible_name ?? '-',
                'email_perusahaan' => $company?->company_email ?? '-',
                'telepon_perusahaan' => $company?->company_phone ?? '-',
                'alamat_perusahaan' => $company?->company_address ?? '-',
                'jenis_perusahaan' => $company?->company_type ?? '-',
                'provinsi_perusahaan' => $company?->company_province ?? '-',
                'kota_perusahaan' => $company?->company_city ?? '-',
                'penandatangan_sama' => $authoritySame,
                'penandatangan_nama' => $authoritySame
                    ? ($company?->responsible_name ?? '-')
                    : ($company?->authority_name ?? '-'),
                'penandatangan_jabatan' => $jabatanPenandatangan,
                'penanggung_jawab_ttd' => $penanggungTtd,
                'bap_status_label' => $bapStatusLabel,
                'nomor_bap' => $bap?->nomor_bap ?? '',
                'status' => $bap?->status ?? 'Draft',
                'lokasi_rows' => $lokasiRows->values(),
                'parameter_order' => $paramOrder,
                'parameter' => $pengujianParams->values(),
                'layanan' => $rincianAktif->values()->all(),
                'layanan_penawaran' => $penawaranItems->values()->all(),
                'layanan_pengujian' => $pengujianItems->values()->all(),
                'has_perubahan_pengujian' => $hasPerubahan,
                'subtotal_penawaran' => $subtotalPenawaran,
                'subtotal_pengujian' => $subtotalPengujian,
                'subtotal' => $hasPerubahan ? $subtotalPengujian : $subtotalPenawaran,
                'total' => $hasPerubahan ? $subtotalPengujian : $subtotalPenawaran,
                'dokumen' => $dokumenArsip->all(),
                'ketua_tim_nama' => $ketuaTimNama,
                'ketua_tim_ttd' => $ketuaTimTtd,
            ];
        });

        return view('admin.superadmin_bap', [
            'orders' => $orders,
        ]);
    }

    public function markViewed(Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);

        $bap = $permohonan->bap;
        if (!$bap || !$bap->user_approved_at) {
            return response()->json(['message' => 'BAP belum disetujui user.'], 422);
        }

        if (!$bap->admin_viewed_at) {
            $bap->update([
                'admin_viewed_at' => now(),
                'admin_viewed_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'message' => 'Notifikasi BAP ditandai sudah dibaca.',
            'viewed' => true,
        ]);
    }

    public function saveDraft(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);
        $payload = $this->parsePayload($request);
        if ($payload === null) {
            return response()->json(['message' => 'Payload BAP tidak valid.'], 422);
        }

        DB::transaction(function () use ($permohonan, $payload) {
            $bap = Bap::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                ['status' => 'draft', 'created_by' => auth()->id()]
            );

            $bap->update([
                'status' => 'draft',
                'updated_by' => auth()->id(),
            ]);

            $this->syncItems($bap, $payload['items']);
            $this->ensureBapStep($permohonan);
        });

        return response()->json(['message' => 'Draft BAP berhasil disimpan.']);
    }

    public function submitToVerifikasi(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess($permohonan);
        $payload = $this->parsePayload($request);
        if ($payload === null) {
            return response()->json(['message' => 'Payload BAP tidak valid.'], 422);
        }

        DB::transaction(function () use ($permohonan, $payload) {
            $bap = Bap::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                ['status' => 'draft', 'created_by' => auth()->id()]
            );

            $bap->update([
                'status' => 'sent_to_user',
                'sent_to_verifikasi_at' => now(),
                'sent_to_user_at' => $bap->sent_to_user_at ?? now(),
                'updated_by' => auth()->id(),
            ]);

            $this->syncItems($bap, $payload['items']);
            $permohonan->update([
                'status_global' => 'alur_bap',
                'status_dokumen' => 'menunggu_persetujuan_bap',
                'status_lab' => $permohonan->status_lab ?: 'pengujian',
            ]);

            if (!empty($permohonan->user_id)) {
                Notifikasi::create([
                    'user_id' => $permohonan->user_id,
                    'title' => 'Persetujuan BAP menunggu',
                    'message' => 'BAP permohonan ' . $permohonan->kode . ' sudah dikirim. Silakan cek dan setujui.',
                    'url' => url('/riwayat_pelayanan?kode=' . urlencode((string) $permohonan->kode) . '&open=detail'),
                ]);
            }
        });

        return response()->json(['message' => 'BAP berhasil dikirim ke pemohon.']);
    }

    private function ensureAccess(?Permohonan $permohonan = null): void
    {
        $user = auth()->user();
        $role = $user?->role;
        if (!in_array($role, ['admin', 'superadmin', 'pcu'], true)) {
            abort(403);
        }

        if ($role === 'pcu' && $permohonan) {
            $hasAccess = $permohonan->assignments()
                ->where('user_id', $user->id)
                ->where('role', 'pcu')
                ->exists();

            if (!$hasAccess) {
                abort(403, 'Anda tidak terdaftar sebagai PCU untuk permohonan ini.');
            }
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

    private function syncItems(Bap $bap, array $items): void
    {
        $ids = collect($items)->pluck('pengujian_param_id')->filter()->map(fn ($id) => (int) $id)->values();
        $existing = $bap->items()->whereNotIn('pengujian_dokumen_parameter_id', $ids)->get();
        $existing->each->delete();

        foreach ($items as $item) {
            $paramId = (int) ($item['pengujian_param_id'] ?? 0);
            if (!$paramId) {
                continue;
            }
            BapItem::updateOrCreate(
                [
                    'bap_id' => $bap->id,
                    'pengujian_dokumen_parameter_id' => $paramId,
                ],
                [
                    'catatan' => trim((string) ($item['catatan'] ?? '')),
                ]
            );
        }
    }

    private function ensureBapStep(Permohonan $permohonan): void
    {
        $bapStep = WorkflowStep::where('kode', 'alur_bap')->first();
        if (!$bapStep) {
            return;
        }

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $bapStep->id],
            ['status' => 'in_progress', 'started_at' => now()]
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
        return null;
    }

    private function routeIfExists(string $name, mixed $parameters = []): ?string
    {
        if (!Route::has($name)) {
            return null;
        }

        return route($name, $parameters);
    }

    private function signatureDataUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }
        $disk = $this->resolveDisk($path);
        if ($disk === null) {
            return '';
        }
        $raw = Storage::disk($disk)->get($path);
        if ($raw === '' || $raw === null) {
            return '';
        }
        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($raw);
    }

    private function aggregatePenawaran(Permohonan $permohonan): array
    {
        $rows = $permohonan->parameters
            ->reject(function ($param) {
                return ($param->status ?? null) === 'rejected' || (int) ($param->qty ?? 0) <= 0;
            })
            ->map(function ($param) {
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

        return [
            'items' => $items,
            'subtotal' => (float) $subtotal,
        ];
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
            if ($penawaranMap->has($key)) {
                return (float) ($penawaranMap->get($key)['harga'] ?? 0);
            }

            return (float) ($row['harga'] ?? 0);
        });

        if ($items->isEmpty()) {
            $items = collect($penawaran['items']);
        }

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
            $kategori = trim((string) ($row['kategori'] ?? '-'));
            $qty = (int) ($row['qty'] ?? 0);
            $harga = $resolvePrice ? (float) $resolvePrice($row) : (float) ($row['harga'] ?? 0);
            $id = $row['service_parameter_id'] ?? null;
            $key = $this->buildParamKey($id, $nama);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'service_parameter_id' => $id,
                    'nama' => $nama !== '' ? $nama : '-',
                    'kategori' => $kategori !== '' ? $kategori : '-',
                    'qty' => 0,
                    'harga' => $harga,
                ];
            }

            $grouped[$key]['qty'] += $qty;
            if ((($grouped[$key]['kategori'] ?? '-') === '-' || trim((string) ($grouped[$key]['kategori'] ?? '')) === '') && $kategori !== '') {
                $grouped[$key]['kategori'] = $kategori;
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

    private function hasParameterDifference(Collection $penawaranItems, Collection $pengujianItems): bool
    {
        if ($pengujianItems->isEmpty()) {
            return false;
        }

        $penawaranMap = $penawaranItems->mapWithKeys(fn ($item) => [$this->buildParamKey($item['service_parameter_id'] ?? null, $item['nama'] ?? '-') => (int) ($item['qty'] ?? 0)]);
        $pengujianMap = $pengujianItems->mapWithKeys(fn ($item) => [$this->buildParamKey($item['service_parameter_id'] ?? null, $item['nama'] ?? '-') => (int) ($item['qty'] ?? 0)]);

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

    private function mapStatusToTahap(string $status): string
    {
        return match ($status) {
            'disposisi' => 'Disposisi',
            'kaji_ulang' => 'Kaji Ulang',
            'penawaran' => 'Penawaran',
            'penjadwalan' => 'Penjadwalan',
            'pengujian' => 'Pengujian',
            'alur_bap' => 'BAP',
            'verifikasi_pengujian' => 'Verifikasi Pengujian',
            'verifikasi_pcu' => 'Verifikasi PCU',
            'koding' => 'Koding',
            'preparasi_analisa', 'prepanalisa', 'verifikasi' => 'Analisa',
            'pembuatan_lhu', 'qc_lhu', 'ttd_lhu' => 'LHU',
            'surat_tagihan' => 'Surat Tagihan',
            'invoice' => 'Kuitansi',
            'billing', 'kode_billing' => 'Kode Billing',
            'penerbitan_suket' => 'Penerbitan Suket',
            'penyerahan_lhu' => 'Penyerahan LHU',
            'cancelled' => 'Dibatalkan',
            default => 'Disposisi',
        };
    }

    private function mapStatusToLabel(string $status): string
    {
        return match ($status) {
            'cancelled' => 'Dibatalkan',
            'penyerahan_lhu' => 'Selesai',
            default => 'Dalam Proses',
        };
    }

    private function mapStatusToProgress(string $status): int
    {
        $map = [
            'disposisi' => 10,
            'kaji_ulang' => 20,
            'penawaran' => 30,
            'penjadwalan' => 40,
            'pengujian' => 50,
            'alur_bap' => 60,
            'verifikasi_pengujian' => 65,
            'verifikasi_pcu' => 68,
            'koding' => 70,
            'preparasi_analisa' => 80,
            'prepanalisa' => 80,
            'verifikasi' => 85,
            'pembuatan_lhu' => 90,
            'qc_lhu' => 93,
            'ttd_lhu' => 95,
            'surat_tagihan' => 96,
            'billing' => 97,
            'kode_billing' => 97,
            'invoice' => 98,
            'penerbitan_suket' => 99,
            'penyerahan_lhu' => 100,
            'cancelled' => 0,
        ];

        return (int) ($map[$status] ?? 0);
    }
}
