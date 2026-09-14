<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Services\PermohonanDeletionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class SuperadminPermohonanController extends Controller
{
    public function index()
    {
        $permohonans = $this->buildPermohonanRows();

        return view('admin.superadmin_permohonan', [
            'permohonans' => $permohonans,
        ]);
    }

    public function export(Request $request): Response
    {
        $rows = $this->applyExportFilters($this->buildPermohonanRows(), $request)->values();
        $filename = 'permohonan_'.now()->format('Ymd_His').'.xls';
        $title = 'Rekap Permohonan';
        $createdAt = now()->format('d-m-Y H:i');

        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11pt;">';
        $html .= '<tr><th colspan="9" style="background:#15406A;color:#fff;font-size:14pt;text-align:center;">'.e($title).'</th></tr>';
        $html .= '<tr><td colspan="9" style="background:#f5f7fa;color:#444;">Dibuat: '.e($createdAt).' | Total data: '.$rows->count().'</td></tr>';
        $html .= '<tr style="background:#dbe9f6;font-weight:bold;text-align:center;">';
        $html .= '<th>Kode Permohonan</th>';
        $html .= '<th>Pelanggan</th>';
        $html .= '<th>Tahap</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Progress (%)</th>';
        $html .= '<th>Tanggal Permohonan</th>';
        $html .= '<th>Provinsi</th>';
        $html .= '<th>Kota</th>';
        $html .= '<th>Total (IDR)</th>';
        $html .= '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.e($row->kode ?? '-').'</td>';
            $html .= '<td>'.e($row->pelanggan ?? '-').'</td>';
            $html .= '<td>'.e($row->tahap ?? '-').'</td>';
            $html .= '<td>'.e($row->status ?? '-').'</td>';
            $html .= '<td style="text-align:center;">'.(int) ($row->progress ?? 0).'</td>';
            $html .= '<td style="text-align:center;">'.e(optional($row->tanggal)->format('Y-m-d') ?? '-').'</td>';
            $html .= '<td>'.e($row->provinsi ?? '-').'</td>';
            $html .= '<td>'.e($row->kota ?? '-').'</td>';
            $html .= '<td style="text-align:right;">'.number_format((float) ($row->total ?? 0), 0, ',', '.').'</td>';
            $html .= '</tr>';
        }

        if ($rows->isEmpty()) {
            $html .= '<tr><td colspan="9" style="text-align:center;color:#777;">Tidak ada data sesuai filter.</td></tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function destroy(Permohonan $permohonan, PermohonanDeletionService $deletionService)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $kode = (string) ($permohonan->kode ?? $permohonan->id);

        try {
            $deletionService->delete($permohonan);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('superadmin.permohonan.index')
                ->with('error', 'Permohonan '.$kode.' gagal dihapus.');
        }

        return redirect()
            ->route('superadmin.permohonan.index')
            ->with('success', 'Permohonan '.$kode.' berhasil dihapus beserta data alur kerjanya.');
    }

    private function buildPermohonanRows(): Collection
    {
        $query = Permohonan::with([
            'company',
            'parameters.serviceParameter.category',
            'dokumenPenawaran',
            'spt',
            'bap.items',
            'draftLhu',
            'assignments.user',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
        ]);

        $this->applyRoleVisibility($query);

        return $query->latest()
            ->get()
            ->map(function (Permohonan $permohonan) {
                $company = $permohonan->company;
                $authoritySame = (bool) ($company?->authority_same ?? false);
                $jabatanPenandatangan = trim((string) ($company?->authority_role ?? ''));
                if ($jabatanPenandatangan === '') {
                    $jabatanPenandatangan = $authoritySame ? 'Penanggung Jawab' : '-';
                }

                $penawaran = $this->aggregatePenawaran($permohonan);
                $pengujian = $this->aggregatePengujian($permohonan, $penawaran);

                $penawaranItems = $penawaran['items'];
                $pengujianItems = $pengujian['items'];
                $subtotalPenawaran = $penawaran['subtotal'];
                $subtotalPengujian = $pengujian['subtotal'];

                $hasPerubahan = $this->hasParameterDifference($penawaranItems, $pengujianItems);
                $rincianAktif = $hasPerubahan ? $pengujianItems : $penawaranItems;

                $dokumen = $permohonan->dokumenPenawaran
                    ->whereNotIn('status', ['draft'])
                    ->sortByDesc('created_at')
                    ->map(function ($doc) {
                        $path = $doc->signed_file_path ?: $doc->file_path;
                        $url = $path ? route('penawaran.documents.show', $doc) : null;

                        return [
                            'nama' => 'Surat Penawaran',
                            'url' => $url,
                        ];
                    })
                    ->values();
                $draft = $permohonan->draftLhu;
                if (! empty($permohonan->spt?->signed_file_path)) {
                    $dokumen->push([
                        'nama' => 'Dokumen SPT',
                        'url' => route('superadmin.dokumen-spt.preview', $permohonan),
                    ]);
                }
                if ($permohonan->bap?->sent_to_user_at || $permohonan->bap?->user_approved_at) {
                    $dokumen->push([
                        'nama' => 'Dokumen BAP',
                        'action' => 'bap',
                    ]);
                    $dokumen->push([
                        'nama' => 'Dokumen Rincian Pengambilan Sampel',
                        'action' => 'rincian',
                    ]);
                    if ($hasPerubahan) {
                        $dokumen->push([
                            'nama' => 'Form Penambahan/Pengurangan Pengujian',
                            'action' => 'penambahan',
                        ]);
                    }
                }
                if (! empty($draft?->surat_tagihan_generated_at)) {
                    $dokumen->push([
                        'nama' => 'Surat Tagihan',
                        'url' => route('superadmin.surat-tagihan.show', $permohonan),
                    ]);
                }
                if (! empty($draft?->billing_file_path) && ! empty($draft?->billing_sent_at)) {
                    $dokumen->push([
                        'nama' => 'Kode Billing',
                        'url' => route('superadmin.billing.show', $permohonan),
                    ]);
                }
                if (! empty($draft?->billing_verified_at) && ! empty($draft?->invoice_generated_at)) {
                    $dokumen->push([
                        'nama' => 'Kuitansi',
                        'url' => route('superadmin.invoice.show', $permohonan),
                    ]);
                }
                if (! empty($draft?->final_file_path)) {
                    $dokumen->push([
                        'nama' => 'Draft LHU Final',
                        'url' => route('superadmin.draft-lhu.final.show', $permohonan),
                    ]);
                }
                if (! empty($draft?->qc_revision_file_path)) {
                    $dokumen->push([
                        'nama' => 'Catatan Revisi QC LHU',
                        'url' => route('superadmin.draft-lhu.qc-revision.show', $permohonan),
                    ]);
                }
                if (! empty($draft?->signed_file_path)) {
                    $dokumen->push([
                        'nama' => 'LHU Bertanda Tangan',
                        'url' => route('superadmin.penyerahan-lhu.signed.show', $permohonan),
                    ]);
                }
                $dokumen = $dokumen
                    ->filter(fn ($doc) => ! empty($doc['action']) || ! empty($doc['url']))
                    ->unique(function ($doc) {
                        if (! empty($doc['action'])) {
                            return 'action:'.$doc['action'];
                        }

                        return 'url:'.($doc['url'] ?? ($doc['nama'] ?? ''));
                    })
                    ->values();
                $assignments = $permohonan->assignments;
                $pcu = $assignments->where('role', 'pcu')->pluck('user.name')->filter()->values()->all();
                $analis = $assignments->where('role', 'analis')->pluck('user.name')->filter()->values()->all();
                $ketuaPcu = $assignments->where('role', 'pcu')->firstWhere('is_leader', true)
                    ?? $assignments->where('role', 'pcu')->first();
                $ketuaPcuList = $ketuaPcu?->user?->name ? [$ketuaPcu->user->name] : [];
                $lokasiRows = collect();
                if ($permohonan->pengujian) {
                    $catatanMap = $permohonan->bap
                        ? $permohonan->bap->items->keyBy('pengujian_dokumen_parameter_id')
                        : collect();
                    $permohonan->pengujian->lokasi->sortBy('urutan')->each(function ($lokasi) use (&$lokasiRows, $catatanMap) {
                        $dokumenList = $lokasi->dokumen->sortBy('urutan')->map(function ($dokumen) use ($catatanMap) {
                            $params = $dokumen->parameters->sortBy('urutan')->map(function ($param) use ($catatanMap) {
                                $catatan = '';
                                if ($catatanMap instanceof Collection) {
                                    $catatan = $catatanMap->get($param->id)?->catatan ?? '';
                                }

                                return [
                                    'id' => $param->id,
                                    'nama' => $param->serviceParameter?->name ?? '-',
                                    'kategori' => $param->serviceParameter?->category?->name ?? '-',
                                    'qty' => $param->qty,
                                    'sesuai' => (bool) $param->is_sesuai,
                                    'is_direct' => (bool) $param->is_direct,
                                    'catatan' => $catatan,
                                ];
                            })->values();

                            return [
                                'id' => $dokumen->id,
                                'nama' => $dokumen->label,
                                'parameter' => $params,
                            ];
                        })->values();

                        $lokasiRows->push([
                            'lokasi' => $lokasi->nama_lokasi,
                            'dokumen_list' => $dokumenList,
                        ]);
                    });
                }

                $statusGlobal = $permohonan->status_global ?? 'disposisi';

                return (object) [
                    'id' => $permohonan->id,
                    'kode' => $permohonan->kode,
                    'pelanggan' => $company?->company_name ?? '-',
                    'jenis_pengujian' => $penawaranItems->pluck('nama')->filter()->take(2)->implode(', ') ?: '-',
                    'tahap' => $this->mapStatusToTahap($statusGlobal),
                    'progress' => $this->mapStatusToProgress($statusGlobal),
                    'status' => $this->mapStatusToLabel($statusGlobal),
                    'tanggal' => $permohonan->created_at,
                    'perusahaan' => $company?->company_name ?? '-',
                    'penanggung_jawab' => $company?->responsible_name ?? '-',
                    'email' => $company?->company_email ?? '-',
                    'telepon' => $company?->company_phone ?? '-',
                    'alamat' => $company?->company_address ?? '-',
                    'jenis_perusahaan' => $company?->company_type ?? '-',
                    'provinsi' => $company?->company_province ?? '-',
                    'kota' => $company?->company_city ?? '-',
                    'tanggal_pengujian' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? '-',
                    'tanggal_pengujian_mulai' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? '-',
                    'tanggal_pengujian_selesai' => optional($permohonan->jadwal_selesai)->format('Y-m-d') ?? '-',
                    'lokasi' => $lokasiRows->pluck('lokasi')->filter()->implode(', ') ?: ($permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-')),
                    'lokasi_rows' => $lokasiRows->values()->all(),
                    'penandatangan_sama' => $authoritySame,
                    'nama_penandatangan' => $authoritySame
                        ? ($company?->responsible_name ?? '-')
                        : ($company?->authority_name ?? '-'),
                    'jabatan_penandatangan' => $jabatanPenandatangan,
                    'penanggung_jawab_ttd' => ($permohonan->bap?->user_approved_at && $company?->responsible_signature_path)
                        ? route('permohonan.signature', $permohonan)
                        : '',
                    'ketua_tim_nama' => $ketuaPcu?->user?->name ?? '-',
                    'layanan' => $rincianAktif->values()->all(),
                    'layanan_penawaran' => $penawaranItems->values()->all(),
                    'layanan_pengujian' => $pengujianItems->values()->all(),
                    'has_perubahan_pengujian' => $hasPerubahan,
                    'subtotal_penawaran' => $subtotalPenawaran,
                    'subtotal_pengujian' => $subtotalPengujian,
                    'subtotal' => $hasPerubahan ? $subtotalPengujian : $subtotalPenawaran,
                    'total' => $hasPerubahan ? $subtotalPengujian : $subtotalPenawaran,
                    'parameter_order' => $penawaranItems->map(fn ($item) => ['nama' => $item['nama'], 'qty' => $item['qty']])->values()->all(),
                    'parameter_pengujian' => $pengujianItems->values()->all(),
                    'dokumen' => $dokumen->all(),
                    'ketua_pcu' => $ketuaPcuList,
                    'pcu' => $pcu,
                    'analis' => $analis,
                ];
            });
    }

    private function applyRoleVisibility(Builder $query): void
    {
        $user = auth()->user();

        if ($user?->role !== 'pcu') {
            return;
        }

        $query->whereHas('assignments', function ($assignmentQuery) use ($user) {
            $assignmentQuery->where('user_id', $user->id)
                ->where('role', 'pcu');
        });
    }

    private function applyExportFilters(Collection $rows, Request $request): Collection
    {
        $keyword = strtolower(trim((string) $request->query('keyword', '')));
        $tahap = strtolower(trim((string) $request->query('tahap', '')));
        $status = strtolower(trim((string) $request->query('status', '')));
        $provinsi = strtolower(trim((string) $request->query('provinsi', '')));
        $kota = strtolower(trim((string) $request->query('kota', '')));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $progressMin = $this->parseOptionalNumber($request->query('progress_min'));
        $progressMax = $this->parseOptionalNumber($request->query('progress_max'));

        return $rows->filter(function ($row) use (
            $keyword,
            $tahap,
            $status,
            $provinsi,
            $kota,
            $dateFrom,
            $dateTo,
            $progressMin,
            $progressMax
        ) {
            $rowKode = strtolower(trim((string) ($row->kode ?? '')));
            $rowPelanggan = strtolower(trim((string) ($row->pelanggan ?? '')));
            $rowTahap = strtolower(trim((string) ($row->tahap ?? '')));
            $rowStatus = strtolower(trim((string) ($row->status ?? '')));
            $rowProvinsi = strtolower(trim((string) ($row->provinsi ?? '')));
            $rowKota = strtolower(trim((string) ($row->kota ?? '')));
            $rowProgress = (int) ($row->progress ?? 0);
            $rowDate = optional($row->tanggal)->format('Y-m-d') ?? '';

            if ($keyword !== '' && ! str_contains($rowKode, $keyword) && ! str_contains($rowPelanggan, $keyword)) {
                return false;
            }
            if ($tahap !== '' && $rowTahap !== $tahap) {
                return false;
            }
            if ($status !== '' && $rowStatus !== $status) {
                return false;
            }
            if ($provinsi !== '' && $rowProvinsi !== $provinsi) {
                return false;
            }
            if ($kota !== '' && $rowKota !== $kota) {
                return false;
            }
            if ($progressMin !== null && $rowProgress < max(0, min(100, $progressMin))) {
                return false;
            }
            if ($progressMax !== null && $rowProgress > max(0, min(100, $progressMax))) {
                return false;
            }
            if ($dateFrom !== '' && ($rowDate === '' || $rowDate < $dateFrom)) {
                return false;
            }
            if ($dateTo !== '' && ($rowDate === '' || $rowDate > $dateTo)) {
                return false;
            }

            return true;
        });
    }

    private function parseOptionalNumber(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (! is_numeric($text)) {
            return null;
        }

        return (float) $text;
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

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'service_parameter_id' => $id,
                    'nama' => $nama !== '' ? $nama : '-',
                    'kategori' => $kategori !== '' ? $kategori : '-',
                    'qty' => 0,
                    'harga' => $harga,
                ];
            }

            $grouped[$key]['qty'] += $qty;
            if (
                (($grouped[$key]['kategori'] ?? '-') === '-' || trim((string) ($grouped[$key]['kategori'] ?? '')) === '')
                && $kategori !== ''
            ) {
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
        if (! empty($serviceParameterId)) {
            return 'id:'.$serviceParameterId;
        }

        return 'name:'.strtolower(trim($name));
    }

    private function mapStatusToTahap(string $status): string
    {
        return match ($status) {
            'verifikasi_pesanan' => 'Verifikasi Pesanan',
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
            'verifikasi_pesanan' => 5,
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
