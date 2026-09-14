<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Feedback;
use App\Models\Permohonan;
use App\Models\UlasanPermohonanResponse;
use App\Models\User;
use App\Models\WorkflowStep;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SuperadminDashboardController extends Controller
{
    private const STATUS_DIPROSES = [
        'verifikasi_pesanan',
        'disposisi',
        'kaji_ulang',
        'penawaran',
        'penjadwalan',
        'pengujian',
        'alur_bap',
        'verifikasi_pengujian',
        'verifikasi_pcu',
        'koding',
        'preparasi_analisa',
        'verifikasi',
        'pembuatan_lhu',
        'lhu',
        'qc_lhu',
        'ttd_lhu',
        'surat_tagihan',
        'invoice',
        'billing',
        'kode_billing',
        'penerbitan_suket',
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'filter_mode' => ['nullable', 'in:all,year,month,range'],
            'year' => ['nullable', 'integer'],
            'month' => ['nullable', 'date_format:Y-m'],
            'start_date' => ['nullable', 'date', 'required_if:filter_mode,range'],
            'end_date' => ['nullable', 'date', 'required_if:filter_mode,range', 'after_or_equal:start_date'],
        ]);

        $filterMode = $validated['filter_mode'] ?? 'year';

        $availableYears = Permohonan::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->values()
            ->all();
        if (empty($availableYears)) {
            $availableYears = [now()->year];
        }

        $requestedYear = isset($validated['year']) ? (int) $validated['year'] : null;
        $currentYear = (int) now()->year;
        $selectedYear = ($requestedYear && in_array($requestedYear, $availableYears, true))
            ? $requestedYear
            : (in_array($currentYear, $availableYears, true) ? $currentYear : max($availableYears));

        $startDate = null;
        $endDate = null;
        $filterDescription = 'Filter tahunan '.$selectedYear;
        $permohonanFilteredQuery = Permohonan::query();
        $selectedMonth = null;

        if ($filterMode === 'range') {
            $startDate = ! empty($validated['start_date']) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
            $endDate = ! empty($validated['end_date']) ? Carbon::parse($validated['end_date'])->endOfDay() : null;
            if (! $startDate || ! $endDate) {
                $startDate = now()->startOfMonth()->startOfDay();
                $endDate = now()->endOfDay();
            }
            $permohonanFilteredQuery
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate);
            $filterDescription = sprintf(
                'Filter rentang tanggal %s s/d %s',
                $startDate->translatedFormat('d M Y'),
                $endDate->translatedFormat('d M Y')
            );
        } elseif ($filterMode === 'month') {
            $selectedMonth = ! empty($validated['month']) ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth() : now()->startOfMonth();
            $startDate = $selectedMonth->copy()->startOfDay();
            $endDate = $selectedMonth->copy()->endOfMonth()->endOfDay();
            $permohonanFilteredQuery
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate);
            $filterDescription = 'Filter bulanan '.$selectedMonth->translatedFormat('F Y');
        } elseif ($filterMode === 'year') {
            $permohonanFilteredQuery->whereYear('created_at', $selectedYear);
            $filterDescription = 'Filter tahunan '.$selectedYear;
        }

        $totalUsers = User::where('role', 'user')->count();
        $potentialBuyers = Cart::where('status', 'active')
            ->whereHas('items')
            ->distinct('user_id')
            ->count('user_id');
        $activeAdmins = User::where('is_active', true)
            ->where('role', '!=', 'user')
            ->count();

        $permohonanSelesai = (clone $permohonanFilteredQuery)->where('status_global', 'penyerahan_lhu')
            ->whereHas('draftLhu', function ($query) {
                $query->whereNotNull('lhu_user_approved_at');
            })
            ->count();
        $totalPermohonanFiltered = (clone $permohonanFilteredQuery)->count();
        $permohonanDibatalkan = (clone $permohonanFilteredQuery)->where('status_global', 'cancelled')->count();
        $permohonanDiproses = (clone $permohonanFilteredQuery)->where(function ($query) {
            $query->whereIn('status_global', self::STATUS_DIPROSES)
                ->orWhere(function ($penyerahanQuery) {
                    $penyerahanQuery->where('status_global', 'penyerahan_lhu')
                        ->where(function ($draftQuery) {
                            $draftQuery->whereDoesntHave('draftLhu')
                                ->orWhereHas('draftLhu', function ($query) {
                                    $query->whereNull('lhu_user_approved_at');
                                });
                        });
                });
        })->count();
        $permohonanFilteredIdsQuery = (clone $permohonanFilteredQuery)->select('id');
        $avgRatingByCategory = UlasanPermohonanResponse::query()
            ->join('ulasan_permohonan_questions as q', 'q.id', '=', 'ulasan_permohonan_responses.question_id')
            ->whereNotNull('permohonan_id')
            ->whereIn('permohonan_id', $permohonanFilteredIdsQuery)
            ->whereNotNull('rating_value')
            ->where('q.type', 'rating')
            ->whereIn('q.category', ['ikm', 'ikk'])
            ->groupBy('q.category')
            ->selectRaw('q.category, AVG(ulasan_permohonan_responses.rating_value) as avg_rating')
            ->pluck('avg_rating', 'q.category');

        $avgIkm = $avgRatingByCategory->has('ikm') ? round((float) $avgRatingByCategory->get('ikm'), 2) : null;
        $avgIkk = $avgRatingByCategory->has('ikk') ? round((float) $avgRatingByCategory->get('ikk'), 2) : null;
        $rasioIkm = ! is_null($avgIkm) ? round(($avgIkm / 4) * 100, 1) : null;
        $rasioIkk = ! is_null($avgIkk) ? round(($avgIkk / 4) * 100, 1) : null;

        $stageLabels = [
            'Verifikasi Pesanan',
            'Disposisi',
            'Kaji Ulang',
            'Penawaran',
            'Penjadwalan',
            'Approval MA',
            'Dokumen SPT',
            'Pengujian',
            'BAP',
            'Verifikasi Pengujian',
            'Verifikasi PCU',
            'Koding',
            'Preparasi Analisa',
            'Verifikasi Hasil Analisa',
            'Draft LHU',
            'QC LHU',
            'Penandatanganan LHU',
            'Surat Tagihan',
            'Kode Billing',
            'Kuitansi',
            'Penerbitan Suket',
            'Penyerahan LHU',
        ];

        $filteredIdsQuery = (clone $permohonanFilteredQuery)->select('id');
        $baseFiltered = fn () => Permohonan::query()->whereIn('id', $filteredIdsQuery);

        $steps = WorkflowStep::query()
            ->whereIn('kode', [
                'disposisi',
                'kaji_ulang',
                'penawaran',
                'penjadwalan',
                'alur_bap',
                'verifikasi_pengujian',
                'verifikasi_pcu',
                'koding',
                'preparasi_analisa',
                'verifikasi',
                'pembuatan_lhu',
                'surat_tagihan',
            ])
            ->get()
            ->keyBy('kode');

        $stageValues = array_fill_keys($stageLabels, 0);
        $hasMaApprovalColumn = Schema::hasColumn('permohonans', 'ma_approved_at');

        $stageValues['Verifikasi Pesanan'] = $baseFiltered()
            ->where('status_global', 'verifikasi_pesanan')
            ->count();

        $disposisiStep = $steps->get('disposisi');
        if ($disposisiStep) {
            $baseDisposisi = $baseFiltered()->whereHas('steps', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            });
            $countPendingMp = (clone $baseDisposisi)->whereHas('approvals', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->where('role', 'mp')
                    ->where('status', 'pending');
            })->count();
            $countPendingMt = (clone $baseDisposisi)->whereHas('approvals', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->where('role', 'mt')
                    ->where('status', 'pending');
            })->whereHas('approvals', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->where('role', 'mp')
                    ->where('status', 'approved');
            })->count();
            $stageValues['Disposisi'] = $countPendingMp + $countPendingMt;
        }

        $kajiUlangStep = $steps->get('kaji_ulang');
        if ($kajiUlangStep) {
            $stageValues['Kaji Ulang'] = $baseFiltered()->whereHas('steps', function ($query) use ($kajiUlangStep) {
                $query->where('step_id', $kajiUlangStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })->count();
        }

        $penawaranStep = $steps->get('penawaran');
        if ($penawaranStep) {
            $stageValues['Penawaran'] = $baseFiltered()->whereHas('steps', function ($query) use ($penawaranStep) {
                $query->where('step_id', $penawaranStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })->count();
        }

        $penjadwalanStep = $steps->get('penjadwalan');
        if ($penjadwalanStep) {
            $stageValues['Penjadwalan'] = $baseFiltered()->whereHas('steps', function ($query) use ($penjadwalanStep) {
                $query->where('step_id', $penjadwalanStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })->whereNull('penjadwalan_sent_at')->count();
        }

        if ($hasMaApprovalColumn) {
            $stageValues['Approval MA'] = $baseFiltered()
                ->whereNotNull('jadwal_mulai')
                ->whereNotNull('jadwal_selesai')
                ->whereNotNull('penjadwalan_sent_at')
                ->whereNull('ma_approved_at')
                ->whereNull('spt_sent_at')
                ->count();

            $stageValues['Dokumen SPT'] = $baseFiltered()
                ->whereNotNull('jadwal_mulai')
                ->whereNotNull('jadwal_selesai')
                ->whereNotNull('penjadwalan_sent_at')
                ->whereNotNull('ma_approved_at')
                ->whereNull('spt_sent_at')
                ->count();
        } else {
            $stageValues['Approval MA'] = 0;
            $stageValues['Dokumen SPT'] = $baseFiltered()
                ->whereNotNull('jadwal_mulai')
                ->whereNotNull('jadwal_selesai')
                ->whereNotNull('penjadwalan_sent_at')
                ->whereNull('spt_sent_at')
                ->count();
        }

        $excludePengujianStatuses = [
            'verifikasi_pengujian',
            'verifikasi_pcu',
            'alur_bap',
            'koding',
            'verifikasi',
            'pembuatan_lhu',
            'qc_lhu',
            'ttd_lhu',
            'surat_tagihan',
            'lhu',
            'invoice',
            'billing',
            'kode_billing',
            'penyerahan_lhu',
            'cancelled',
        ];
        $stageValues['Pengujian'] = $baseFiltered()
            ->whereNotNull('spt_sent_at')
            ->where(function ($query) use ($excludePengujianStatuses) {
                $query->whereNull('status_global')
                    ->orWhereNotIn('status_global', $excludePengujianStatuses);
            })
            ->count();

        $bapStep = $steps->get('alur_bap');
        if ($bapStep) {
            $bapEligible = $baseFiltered()->where(function ($query) use ($bapStep) {
                $query->whereHas('steps', function ($stepQuery) use ($bapStep) {
                    $stepQuery->where('step_id', $bapStep->id)
                        ->whereIn('status', ['pending', 'in_progress']);
                })->orWhereHas('bap', function ($bapQuery) {
                    $bapQuery->whereNotNull('sent_to_user_at')
                        ->orWhereNotNull('user_approved_at');
                });
            });

            $stageValues['BAP'] = (clone $bapEligible)
                ->where(function ($query) {
                    $query->whereDoesntHave('bap')
                        ->orWhereHas('bap', function ($bapQuery) {
                            $bapQuery->whereNull('user_approved_at');
                        });
                })
                ->count();
        }

        $verifikasiPengujianStep = $steps->get('verifikasi_pengujian');
        if ($verifikasiPengujianStep) {
            $stageValues['Verifikasi Pengujian'] = $baseFiltered()
                ->where(function ($query) use ($verifikasiPengujianStep) {
                    $query->whereHas('steps', function ($inner) use ($verifikasiPengujianStep) {
                        $inner->where('step_id', $verifikasiPengujianStep->id)
                            ->whereIn('status', ['pending', 'in_progress']);
                    })->orWhere('status_global', 'verifikasi_pengujian');
                })
                ->count();
        }

        $stageValues['Verifikasi PCU'] = $baseFiltered()
            ->whereNotNull('spt_sent_at')
            ->whereIn('status_dokumen', ['verifikasi_pcu', 'siap_bap'])
            ->count();

        $kodingStep = $steps->get('koding');
        if ($kodingStep) {
            $stageValues['Koding'] = $baseFiltered()->whereHas('steps', function ($query) use ($kodingStep) {
                $query->where('step_id', $kodingStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })->count();
        }

        $prepanalisaStep = $steps->get('preparasi_analisa');
        if ($prepanalisaStep) {
            $stageValues['Preparasi Analisa'] = $baseFiltered()->whereHas('steps', function ($query) use ($prepanalisaStep) {
                $query->where('step_id', $prepanalisaStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })->count();
        }

        $verifikasiStep = $steps->get('verifikasi');
        if ($verifikasiStep) {
            $stageValues['Verifikasi Hasil Analisa'] = $baseFiltered()
                ->where('status_global', 'verifikasi')
                ->count();
        }

        $draftStep = $steps->get('pembuatan_lhu');
        if ($draftStep) {
            $stageValues['Draft LHU'] = $baseFiltered()->where(function ($query) use ($draftStep) {
                $query->whereHas('steps', function ($inner) use ($draftStep) {
                    $inner->where('step_id', $draftStep->id)
                        ->whereIn('status', ['pending', 'in_progress']);
                })->orWhere('status_global', 'pembuatan_lhu')
                    ->orWhere(function ($extra) {
                        $extra->whereIn('status_global', ['preparasi_analisa', 'verifikasi'])
                            ->whereHas('pengujian.lokasi.dokumen.parameters', function ($paramQuery) {
                                $paramQuery->where('is_direct', true);
                            });
                    });
            })->count();
        }

        $stageValues['QC LHU'] = $baseFiltered()->where('status_global', 'qc_lhu')->count();
        $stageValues['Penandatanganan LHU'] = $baseFiltered()->where('status_global', 'ttd_lhu')->count();
        $stageValues['Surat Tagihan'] = $baseFiltered()->where('status_global', 'surat_tagihan')->count();
        $stageValues['Kuitansi'] = $baseFiltered()->where('status_global', 'invoice')->count();
        $stageValues['Kode Billing'] = $baseFiltered()->whereIn('status_global', ['billing', 'kode_billing'])->count();
        $stageValues['Penerbitan Suket'] = $baseFiltered()->where('status_global', 'penerbitan_suket')->count();
        $stageValues['Penyerahan LHU'] = $baseFiltered()
            ->where('status_global', 'penyerahan_lhu')
            ->where(function ($query) {
                $query->whereDoesntHave('draftLhu')
                    ->orWhereHas('draftLhu', function ($inner) {
                        $inner->whereNull('lhu_user_approved_at');
                    });
            })
            ->count();

        $barLabels = [];
        $barValues = [];
        $barTitle = 'Permohonan per Bulan';
        $barSubtitle = (string) $selectedYear;

        if (in_array($filterMode, ['range', 'month'], true)) {
            $dailyCounts = (clone $permohonanFilteredQuery)
                ->selectRaw('DATE(created_at) as tgl, COUNT(*) as total')
                ->groupBy('tgl')
                ->pluck('total', 'tgl')
                ->toArray();

            foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                $key = $date->format('Y-m-d');
                $barLabels[] = $date->translatedFormat('d M');
                $barValues[] = (int) ($dailyCounts[$key] ?? 0);
            }

            $barTitle = 'Permohonan Harian';
            $barSubtitle = $filterMode === 'month'
                ? $selectedMonth->translatedFormat('F Y')
                : sprintf('%s - %s', $startDate->translatedFormat('d M Y'), $endDate->translatedFormat('d M Y'));
        } elseif ($filterMode === 'year') {
            $monthlyCounts = (clone $permohonanFilteredQuery)
                ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
                ->groupBy('bulan')
                ->pluck('total', 'bulan')
                ->toArray();

            $barLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $barValues = array_fill(0, 12, 0);
            foreach ($monthlyCounts as $bulan => $total) {
                $idx = (int) $bulan - 1;
                if ($idx >= 0 && $idx < 12) {
                    $barValues[$idx] = (int) $total;
                }
            }
        } else {
            $yearlyCounts = (clone $permohonanFilteredQuery)
                ->selectRaw('YEAR(created_at) as tahun, COUNT(*) as total')
                ->groupBy('tahun')
                ->orderBy('tahun')
                ->get();
            foreach ($yearlyCounts as $row) {
                if (empty($row->tahun)) {
                    continue;
                }
                $barLabels[] = (string) $row->tahun;
                $barValues[] = (int) $row->total;
            }
            if (empty($barLabels)) {
                $barLabels = [(string) now()->year];
                $barValues = [0];
            }
            $barTitle = 'Permohonan per Tahun';
            $barSubtitle = 'Semua Periode';
        }

        $dashboardDataset = [
            'barTitle' => $barTitle,
            'barSubtitle' => $barSubtitle,
            'barLabels' => $barLabels,
            'barValues' => $barValues,
            'totalPermohonan' => $totalPermohonanFiltered,
            'statusLabels' => $stageLabels,
            'statusValues' => array_values($stageValues),
        ];

        $agendaEvents = [];
        $permohonanAgenda = Permohonan::with(['company', 'assignments.user', 'pengujian'])
            ->whereNotNull('jadwal_mulai')
            ->whereNotNull('jadwal_selesai')
            ->get();

        foreach ($permohonanAgenda as $permohonan) {
            $hasPengujianData = $permohonan->pengujian !== null;
            $isPastPengujianStage = in_array((string) $permohonan->status_global, [
                'cancelled',
                'alur_bap',
                'verifikasi_pengujian',
                'verifikasi_pcu',
                'koding',
                'preparasi_analisa',
                'verifikasi',
                'pembuatan_lhu',
                'lhu',
                'qc_lhu',
                'ttd_lhu',
                'surat_tagihan',
                'invoice',
                'billing',
                'kode_billing',
                'penerbitan_suket',
                'penyerahan_lhu',
            ], true);

            if ($hasPengujianData || $isPastPengujianStage) {
                continue;
            }

            $mulai = $permohonan->jadwal_mulai instanceof Carbon
                ? $permohonan->jadwal_mulai
                : Carbon::parse($permohonan->jadwal_mulai);
            $selesai = $permohonan->jadwal_selesai instanceof Carbon
                ? $permohonan->jadwal_selesai
                : Carbon::parse($permohonan->jadwal_selesai);
            if ($selesai->lessThan($mulai)) {
                $selesai = $mulai->copy();
            }
            $durasiHari = $mulai->diffInDays($selesai) + 1;

            $pcuNames = $permohonan->assignments
                ->where('role', 'pcu')
                ->pluck('user.name')
                ->filter()
                ->values()
                ->implode(', ');

            $title = sprintf(
                'Pengujian - %s',
                $permohonan->company?->company_name ?? $permohonan->kode ?? 'Permohonan'
            );
            $companyName = $permohonan->company?->company_name ?? '-';
            $companyCity = $permohonan->company?->company_city ?? '-';

            foreach (CarbonPeriod::create($mulai, $selesai) as $date) {
                $agendaEvents[] = [
                    'date' => $date->format('Y-m-d'),
                    'title' => $title,
                    'pcu' => $pcuNames ?: '-',
                    'company' => $companyName,
                    'city' => $companyCity,
                    'days' => $durasiHari,
                ];
            }
        }

        // Hitung jumlah feedback per rating (1-5)
        $ratingSummary = Feedback::selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        return view('admin.superadmin_dashboard', compact(
            'ratingSummary',
            'totalUsers',
            'potentialBuyers',
            'activeAdmins',
            'permohonanDiproses',
            'permohonanSelesai',
            'permohonanDibatalkan',
            'avgIkm',
            'avgIkk',
            'rasioIkm',
            'rasioIkk',
            'filterMode',
            'availableYears',
            'selectedYear',
            'selectedMonth',
            'startDate',
            'endDate',
            'filterDescription',
            'dashboardDataset',
            'agendaEvents'
        ));
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
            'preparasi_analisa' => 'Preparasi Analisa',
            'verifikasi' => 'Verifikasi Hasil Analisa',
            'pembuatan_lhu', 'lhu' => 'Draft LHU',
            'qc_lhu' => 'QC LHU',
            'ttd_lhu' => 'Penandatanganan LHU',
            'surat_tagihan' => 'Surat Tagihan',
            'invoice' => 'Kuitansi',
            'billing', 'kode_billing' => 'Kode Billing',
            'penerbitan_suket' => 'Penerbitan Suket',
            'penyerahan_lhu' => 'Penyerahan LHU',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Kaji Ulang',
            default => 'Disposisi',
        };
    }
}
