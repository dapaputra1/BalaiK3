<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\JejaringEntry;
use App\Models\ServicePackage;
use App\Models\SocialMediaPost;
use App\Models\ServiceCategory;
use App\Models\UlasanPermohonanResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $canLoadUlasanStats = Schema::hasTable('ulasan_permohonan_responses')
            && Schema::hasTable('ulasan_permohonan_questions');

        $loadAverageRatio = function (string $category) use ($canLoadUlasanStats): ?float {
            if (!$canLoadUlasanStats) {
                return null;
            }

            $avgRating = UlasanPermohonanResponse::query()
                ->selectRaw('AVG(ulasan_permohonan_responses.rating_value) as avg_rating')
                ->join('ulasan_permohonan_questions as q', 'q.id', '=', 'ulasan_permohonan_responses.question_id')
                ->where('q.category', $category)
                ->where('q.type', 'rating')
                ->where('q.is_active', true)
                ->whereNotNull('ulasan_permohonan_responses.rating_value')
                ->value('avg_rating');

            if (is_null($avgRating)) {
                return null;
            }

            return round((((float) $avgRating) / 4) * 100, 1);
        };

        $ikmRatio = $loadAverageRatio('ikm');
        $ikkRatio = $loadAverageRatio('ikk');

        $classifyIndexRatio = static function (?float $ratio, array $labels): array {
            if (is_null($ratio)) {
                return [
                    'score' => null,
                    'ratio' => null,
                    'ratio_label' => '-',
                    'category' => 'Belum ada data',
                    'class' => 'muted',
                ];
            }

            $normalizedRatio = max(0, min(100, round($ratio, 1)));
            $score = round(($normalizedRatio / 100) * 4, 2);
            $level = match (true) {
                $score <= 1.75 => 'poor',
                $score <= 2.50 => 'fair',
                $score <= 3.25 => 'good',
                default => 'excellent',
            };

            return [
                'score' => $score,
                'ratio' => $normalizedRatio,
                'ratio_label' => number_format($normalizedRatio, 1, ',', '.') . '%',
                'category' => $labels[$level] ?? $level,
                'class' => $level,
            ];
        };

        $ikmIndex = $classifyIndexRatio($ikmRatio, [
            'poor' => 'Tidak Baik',
            'fair' => 'Kurang Baik',
            'good' => 'Baik',
            'excellent' => 'Sangat Baik',
        ]);

        $ikkIndex = $classifyIndexRatio($ikkRatio, [
            'poor' => 'Tidak Bersih dari Korupsi',
            'fair' => 'Kurang',
            'good' => 'Cukup Bersih',
            'excellent' => 'Bersih dari Korupsi',
        ]);

        $buildConicGradient = function (array $segments): string {
            $total = array_sum(array_map(static fn (array $segment): int => (int) ($segment[1] ?? 0), $segments));
            if ($total <= 0) {
                return 'conic-gradient(#dfe5eb 0 100%)';
            }

            $stops = [];
            $current = 0.0;
            foreach ($segments as [$color, $value]) {
                $percent = ($value / $total) * 100;
                $start = number_format($current, 2, '.', '');
                $end = number_format($current + $percent, 2, '.', '');
                $stops[] = "{$color} {$start}% {$end}%";
                $current += $percent;
            }

            return 'conic-gradient(' . implode(', ', $stops) . ')';
        };

        $statsPie = [
            'ikm' => [
                'index' => $ikmIndex,
            ],
            'ikk' => [
                'index' => $ikkIndex,
            ],
        ];

        $serviceCategoryOrder = [
            'Lingkungan Kerja',
            'Ambien',
            'Emisi',
            'Kesehatan',
            'Pelatihan',
        ];

        $parameterCategoryRows = collect();
        $serviceCategories = collect();
        $canLoadParameterCategoryStats = Schema::hasTable('permohonan_parameters')
            && Schema::hasTable('permohonans')
            && Schema::hasTable('draft_lhus')
            && Schema::hasTable('service_parameters')
            && Schema::hasTable('service_categories');

        if ($canLoadParameterCategoryStats) {
            $serviceCategoryOrderCase = 'CASE name ' . collect($serviceCategoryOrder)
                ->map(fn ($name, $index) => "WHEN ? THEN {$index}")
                ->implode(' ') . ' ELSE 999 END';

            $serviceCategories = ServiceCategory::with(['parameters' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
                ->where('is_active', true)
                ->orderByRaw($serviceCategoryOrderCase, $serviceCategoryOrder)
                ->orderBy('name')
                ->get();

            $parameterCategoryRows = DB::table('permohonan_parameters as pp')
                ->join('permohonans as p', 'p.id', '=', 'pp.permohonan_id')
                ->join('draft_lhus as dl', 'dl.permohonan_id', '=', 'p.id')
                ->leftJoin('service_parameters as sp', 'sp.id', '=', 'pp.service_parameter_id')
                ->leftJoin('service_categories as sc', 'sc.id', '=', 'sp.service_category_id')
                ->where('p.status_global', 'penyerahan_lhu')
                ->whereNotNull('dl.lhu_user_approved_at')
                ->where('pp.qty', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('pp.status')
                        ->orWhere('pp.status', '!=', 'rejected');
                })
                ->selectRaw("
                    sc.id as category_id,
                    COALESCE(sc.name, 'Tanpa Kategori') as category_name,
                    sp.id as service_parameter_id,
                    sp.short_code as short_code,
                    COALESCE(sp.name, pp.parameter_name, 'Tanpa Nama Parameter') as parameter_name,
                    SUM(pp.qty) as total
                ")
                ->groupBy('sc.id', 'sc.name', 'sp.id', 'sp.short_code', 'sp.name', 'pp.parameter_name')
                ->orderByDesc('total')
                ->get();
        }

        $parameterPalette = [
            '#1d4b78',
            '#40ab83',
            '#f89538',
            '#7c3aed',
            '#0f9f9a',
            '#d63384',
            '#6b7280',
            '#0d6efd',
            '#dc3545',
            '#198754',
        ];

        if ($serviceCategories->isEmpty()) {
            $serviceCategories = collect($serviceCategoryOrder)->map(function (string $name, int $index) {
                return (object) [
                    'id' => null,
                    'name' => $name,
                    'parameters' => collect(),
                    'sort_index' => $index,
                ];
            });
        }

        $parameterSubcategoryMap = [
            'Lingkungan Kerja' => [
                [
                    'name' => 'Parameter Fisika',
                    'short_codes' => ['GLTAR', 'GSTAR', 'GMTAR', 'PKP4M', 'ISBB', 'ISBB1', 'DB', 'DBAF', 'NDISE', 'PLNCA', 'PUP1M', 'MLDAN', 'MMDAN', 'ULTRA', 'SDHTK', 'DPM25'],
                ],
                [
                    'name' => 'Parameter Kimia',
                    'short_codes' => ['BENZ', 'ETANO', 'HCOH', 'METAN', 'MEK', 'TOLU', 'XELE', 'HC', 'CL2LK', 'COLK', 'CO2LK', 'H2S', 'NH3', 'NO2', 'OX', 'SO2', 'DPBUP', 'DPM10', 'DPM25', 'DSBUS', 'KDTLR', 'KDLAR', 'KDLAS', 'KDLCD', 'KDLCO', 'KDLCR', 'KDLCU', 'KDLHG', 'KDLPB', 'KDLSB', 'KDLTL', 'KDLZN', 'PSAKU', 'PSAKL', 'DEBULK'],
                ],
                [
                    'name' => 'Parameter Biologi',
                    'short_codes' => ['PMKJM', 'PMKBA', 'PMPGU'],
                ],
                [
                    'name' => 'Parameter Ergonomi dan Psikologi',
                    'short_codes' => ['ANTRO', 'OBSERG', 'UKKJA', 'ERGON'],
                ],
            ],
            'Ambien' => [
                [
                    'name' => 'Parameter Fisika',
                    'short_codes' => ['DB24', 'DB', 'DBAF', 'MLDA1'],
                ],
                [
                    'name' => 'Parameter Kimia',
                    'short_codes' => ['COAM', 'CO2AM', 'H2S1', 'NH31', 'NO21', 'SO21', 'HC1', 'OX1', 'DPM11', 'DPM21', 'TSPT2', 'KDAAS', 'KDACD', 'KDACO', 'KDACR', 'KDACU', 'KDAHG', 'KDAPB', 'KDAPB2', 'KDASB', 'KDATL', 'KDAZN', 'DEBUAM'],
                ],
            ],
            'Emisi' => [
                [
                    'name' => 'Emisi Sumber Tidak Bergerak',
                    'short_codes' => ['DEBU', 'COSTB', 'CO2STB', 'CL2', 'H2S2', 'HCL', 'HCL1', 'HF', 'NH32', 'HG', 'SO22', 'NO22', 'LAJUA', 'LPPAM', 'SDPCR'],
                ],
                [
                    'name' => 'Emisi Sumber Bergerak',
                    'short_codes' => ['OPASI', 'CO', 'CO2', 'O2', 'HCEM'],
                ],
            ],
        ];

        $rowsByCategoryId = $parameterCategoryRows->groupBy(fn ($row) => (string) ($row->category_id ?? ''));
        $rowTotalsByParameterId = $parameterCategoryRows
            ->filter(fn ($row) => !empty($row->service_parameter_id))
            ->keyBy(fn ($row) => (int) $row->service_parameter_id);

        $makeParameterPayload = function ($parameter, int $fallbackTotal = 0) use ($rowTotalsByParameterId): array {
            $row = !empty($parameter->id)
                ? $rowTotalsByParameterId->get((int) $parameter->id)
                : null;

            return [
                'id' => !empty($parameter->id) ? (int) $parameter->id : null,
                'name' => (string) ($parameter->name ?? 'Tanpa Nama Parameter'),
                'short_code' => (string) ($parameter->short_code ?? ''),
                'total' => (int) ($row->total ?? $fallbackTotal),
            ];
        };

        $buildDetail = function (string $name, array $parameters, int $index) use ($parameterPalette): array {
            $parameters = collect($parameters)
                ->sortByDesc(fn (array $parameter) => (int) ($parameter['total'] ?? 0))
                ->values()
                ->map(function (array $parameter, int $parameterIndex) use ($parameterPalette) {
                    $parameter['color'] = $parameterPalette[$parameterIndex % count($parameterPalette)];

                    return $parameter;
                })
                ->all();

            return [
                'name' => $name,
                'total' => (int) collect($parameters)->sum('total'),
                'color' => $parameterPalette[$index % count($parameterPalette)],
                'parameters' => $parameters,
            ];
        };

        $parameterCategoryStats = $serviceCategories
            ->map(function ($category, int $categoryIndex) use ($buildConicGradient, $buildDetail, $makeParameterPayload, $parameterPalette, $parameterSubcategoryMap, $rowsByCategoryId) {
                $categoryRows = $rowsByCategoryId->get((string) ($category->id ?? ''), collect());
                $parameters = collect($category->parameters ?? collect())
                    ->map(fn ($parameter) => $makeParameterPayload($parameter))
                    ->values();

                $existingParameterIds = $parameters
                    ->pluck('id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $extraParameters = $categoryRows
                    ->filter(fn ($row) => empty($row->service_parameter_id) || !in_array((int) $row->service_parameter_id, $existingParameterIds, true))
                    ->map(function ($row) {
                        return [
                            'id' => !empty($row->service_parameter_id) ? (int) $row->service_parameter_id : null,
                            'name' => (string) ($row->parameter_name ?: 'Tanpa Nama Parameter'),
                            'short_code' => (string) ($row->short_code ?? ''),
                            'total' => (int) $row->total,
                        ];
                    });

                $parameters = $parameters
                    ->concat($extraParameters)
                    ->sortByDesc(fn (array $parameter) => (int) ($parameter['total'] ?? 0))
                    ->values();

                $subcategories = $parameterSubcategoryMap[$category->name] ?? null;
                if ($subcategories) {
                    $mappedShortCodes = collect($subcategories)
                        ->flatMap(fn (array $subcategory) => $subcategory['short_codes'] ?? [])
                        ->map(fn ($shortCode) => strtoupper((string) $shortCode))
                        ->unique()
                        ->values();

                    $details = collect($subcategories)
                        ->map(function (array $subcategory, int $index) use ($buildDetail, $parameters) {
                            $shortCodes = collect($subcategory['short_codes'] ?? [])
                                ->map(fn ($shortCode) => strtoupper((string) $shortCode))
                                ->all();

                            $subcategoryParameters = $parameters
                                ->filter(fn (array $parameter) => in_array(strtoupper((string) ($parameter['short_code'] ?? '')), $shortCodes, true))
                                ->values()
                                ->all();

                            return $buildDetail($subcategory['name'], $subcategoryParameters, $index);
                        });

                    $unmappedParameters = $parameters
                        ->filter(fn (array $parameter) => !$mappedShortCodes->contains(strtoupper((string) ($parameter['short_code'] ?? ''))))
                        ->values()
                        ->all();

                    if ($unmappedParameters !== []) {
                        $details->push($buildDetail('Parameter Lainnya', $unmappedParameters, $details->count()));
                    }
                } else {
                    $details = $parameters
                        ->map(function (array $parameter, int $index) use ($buildDetail) {
                            return $buildDetail($parameter['name'], [$parameter], $index);
                        });
                }

                $details = $details
                    ->values()
                    ->map(function (array $detail, int $index) use ($parameterPalette) {
                        $detail['color'] = $parameterPalette[$index % count($parameterPalette)];

                        return $detail;
                    });

                $total = (int) $details->sum('total');

                return [
                    'name' => (string) ($category->name ?? 'Tanpa Kategori'),
                    'total' => $total,
                    'details' => $details->all(),
                    'has_subcategories' => !empty($subcategories),
                    'color' => $parameterPalette[$categoryIndex % count($parameterPalette)],
                    'gradient' => $buildConicGradient(
                        $details
                            ->map(fn (array $detail): array => [$detail['color'], $detail['total']])
                            ->all()
                    ),
                ];
            })
            ->values();

        $parameterCategoryTotal = (int) $parameterCategoryStats->sum('total');
        $parameterCategoryMax = max(1, (int) $parameterCategoryStats->max('total'));
        $parameterCategoryStats = $parameterCategoryStats
            ->map(function (array $category) use ($parameterCategoryMax) {
                $category['bar_percent'] = $category['total'] > 0
                    ? round(($category['total'] / $parameterCategoryMax) * 100, 2)
                    : 0;

                return $category;
            });

        $homeNews = Schema::hasTable('beritas')
            ? Berita::query()
                ->with(['uploader', 'viewCounter'])
                ->latest()
                ->take(4)
                ->get()
            : collect();

        $socialDefinitions = SocialMediaPost::platformDefinitions();
        $socialPostsByPlatform = Schema::hasTable('social_media_posts')
            ? SocialMediaPost::query()
                ->with('uploader')
                ->latest('updated_at')
                ->latest('id')
                ->get()
                ->groupBy('platform')
            : collect();

        $homeSocialPosts = collect($socialDefinitions)
            ->map(function (array $config, string $platform) use ($socialPostsByPlatform) {
                $posts = ($socialPostsByPlatform->get($platform) ?? collect())
                    ->values();

                return [
                    'platform' => $platform,
                    'config' => $config,
                    'posts' => $posts,
                    'has_posts' => $posts->isNotEmpty(),
                ];
            })
            ->values();

        return view('home', [
            'statsPie' => $statsPie,
            'parameterCategoryStats' => $parameterCategoryStats,
            'parameterCategoryTotal' => $parameterCategoryTotal,
            'homeFeaturedBerita' => $homeNews->first(),
            'homeSideBeritas' => $homeNews->slice(1)->values(),
            'homeSocialPosts' => $homeSocialPosts,
        ]);
    }

    public function daftarPelayanan()
    {
        $order = [
            'Lingkungan Kerja',
            'Ambien',
            'Emisi',
            'Kesehatan',
            'Pelatihan',
        ];

        $orderCase = 'CASE name ' . collect($order)
            ->map(fn ($name, $index) => "WHEN ? THEN {$index}")
            ->implode(' ') . ' ELSE 999 END';

        $categories = Schema::hasTable('service_categories') && Schema::hasTable('service_parameters')
            ? ServiceCategory::with(['parameters' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
                ->where('is_active', true)
                ->orderByRaw($orderCase, $order)
                ->orderBy('name')
                ->get()
                ->map(function ($category) {
                    if ($category->name === 'Lingkungan Kerja') {
                        $fisikaConfig = collect([
                            [
                                'name' => 'Pengujian Getaran',
                                'hide_group_price' => true,
                                'children' => [
                                    [
                                        'short_code' => 'GLTAR',
                                        'name' => 'Getaran Lengan Tangan',
                                        'unit' => 'per sampel orang per parameter per pengujian',
                                        'price' => 100000,
                                    ],
                                    [
                                        'short_code' => 'GSTAR',
                                        'name' => 'Getaran Seluruh Tubuh',
                                        'unit' => 'per sampel orang per parameter per pengujian',
                                        'price' => 100000,
                                    ],
                                    [
                                        'short_code' => 'GMTAR',
                                        'name' => 'Getaran Mekanik',
                                        'unit' => 'per titik per pengujian',
                                        'price' => 125000,
                                    ],
                                ],
                            ],
                            [
                                'name' => 'Pengujian Tekanan Panas',
                                'hide_group_price' => true,
                                'children' => [
                                    ['short_code' => 'ISBB', 'name' => 'Indeks Suhu Basah dan Bola / ISBB', 'unit' => 'per titik per pengujian', 'price' => 75000],
                                    ['short_code' => 'ISBB1', 'name' => 'Indeks Suhu Basah dan Bola / ISBB dan Kecepatan Aliran Udara', 'unit' => 'per titik per pengujian', 'price' => 100000],
                                ],
                            ],
                            [
                                'name' => 'Pengujian Intensitas Kebisingan',
                                'hide_group_price' => true,
                                'children' => [
                                    ['short_code' => 'DB', 'name' => 'Kebisingan Sesaat - Tanpa Analisis Frekuensi', 'unit' => 'per titik per pengujian', 'price' => 50000],
                                    ['short_code' => 'DBAF', 'name' => 'Kebisingan Sesaat - Dengan Analisis Frekuensi', 'unit' => 'per titik per pengujian', 'price' => 75000],
                                    ['short_code' => 'NDISE', 'name' => 'Noise Dosimeter', 'unit' => 'per sampel orang', 'price' => 350000],
                                    ['short_code' => 'PKP4M', 'name' => 'Pemetaan Kebisingan (per 400 m2)', 'unit' => 'per 400 m2', 'price' => 4000000],
                                ],
                            ],
                            [
                                'name' => 'Pengujian Intensitas Penerangan Cahaya',
                                'hide_group_price' => true,
                                'children' => [
                                    ['short_code' => 'PLNCA', 'name' => 'Pencahayaan Lokal', 'unit' => 'per titik', 'price' => 50000],
                                    ['short_code' => 'PUP1M', 'name' => 'Pencahayaan Umum (per 100 m2)', 'unit' => 'per 100 m2', 'price' => 200000],
                                ],
                            ],
                            [
                                'short_code' => 'MLDAN',
                                'name' => 'Medan Listrik',
                                'unit' => 'per titik per pengujian',
                                'price' => 100000,
                            ],
                            [
                                'short_code' => 'MMDAN',
                                'name' => 'Medan Magnet',
                                'unit' => 'per titik per pengujian',
                                'price' => 100000,
                            ],
                            [
                                'short_code' => 'ULTRA',
                                'name' => 'Ultraviolet',
                                'unit' => 'per titik per pengujian',
                                'price' => 100000,
                            ],
                            [
                                'short_code' => 'SDHTK',
                                'name' => 'Sanitasi dan Higiene Tempat Kerja',
                                'unit' => 'per lokasi',
                                'price' => 500000,
                            ],
                        ]);

                        $kimiaConfig = collect([
                            [
                                'name' => 'Pengujian Sampel dan Analisis Gas Organik',
                                'hide_group_price' => true,
                                'children' => [
                                    ['short_code' => 'BENZ', 'name' => 'Benzene', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                    ['short_code' => 'ETANO', 'name' => 'Etanol', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                    ['short_code' => 'HC', 'name' => 'HC', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                    ['short_code' => 'HCOH', 'name' => 'HCOH', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                    ['short_code' => 'HC', 'name' => 'Total Hidrokarbon', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                    ['short_code' => 'METAN', 'name' => 'Metanol', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                    ['short_code' => 'MEK', 'name' => 'Metil Etil Keton', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                    ['short_code' => 'TOLU', 'name' => 'Toluene', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                    ['short_code' => 'XELE', 'name' => 'Xylene', 'unit' => 'per sampel per parameter', 'price' => 250000],
                                ],
                            ],
                            [
                                'name' => 'Pengujian Sampel dan Analisis Gas Anorganik',
                                'hide_group_price' => true,
                                'children' => [
                                    ['short_code' => 'CL2LK', 'name' => 'Cl2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'COLK', 'name' => 'CO', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'CO2LK', 'name' => 'CO2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'H2S', 'name' => 'H2S', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'NH3', 'name' => 'NH3', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'NO2', 'name' => 'NO2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'OX', 'name' => 'OX', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'SO2', 'name' => 'SO2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                ],
                            ],
                            [
                                'name' => 'Pengujian Kualitas Udara Dalam Ruangan (KUDR)',
                                'hide_group_price' => true,
                                'children' => [
                                    ['short_code' => 'COLK', 'name' => 'CO', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'CO2LK', 'name' => 'CO2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'NO2', 'name' => 'NO2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'OX', 'name' => 'OX', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                    ['short_code' => 'DPM25', 'name' => 'Debu KUDR - Direct Reading', 'unit' => 'per parameter per sampel uji', 'price' => 150000],
                                ],
                            ],
                            ['short_code' => 'DPBUP', 'name' => 'Debu Perseorangan', 'unit' => 'per parameter per sampel uji', 'price' => 450000],
                            ['short_code' => 'KDTLR', 'name' => 'Kadar Debu Total (LVS)', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                            [
                                'name' => 'Kadar Debu Logam (AAS)',
                                'hide_group_price' => true,
                                'children' => [
                                    ['short_code' => 'KDLAS', 'name' => 'Kadar Debu Logam (AAS) - As', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLCD', 'name' => 'Kadar Debu Logam (AAS) - Cd', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLCO', 'name' => 'Kadar Debu Logam (AAS) - Co', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLCR', 'name' => 'Kadar Debu Logam (AAS) - Cr', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLCU', 'name' => 'Kadar Debu Logam (AAS) - Cu', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLHG', 'name' => 'Kadar Debu Logam (AAS) - Hg', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLPB', 'name' => 'Kadar Debu Logam (AAS) - Pb', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLSB', 'name' => 'Kadar Debu Logam (AAS) - Sb', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLTL', 'name' => 'Kadar Debu Logam (AAS) - Tl', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                    ['short_code' => 'KDLZN', 'name' => 'Kadar Debu Logam (AAS) - Zn', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                                ],
                            ],
                            [
                                'name' => 'Pengujian Serat Asbes',
                                'hide_group_price' => true,
                                'children' => [
                                    ['short_code' => 'PSAKU', 'name' => 'Kuantitatif', 'unit' => 'per parameter per sampel per pengujian', 'price' => 250000],
                                    ['short_code' => 'PSAKL', 'name' => 'Kualitatif', 'unit' => 'per parameter per sampel per pengujian', 'price' => 250000],
                                ],
                            ],
                        ]);

                        $biologiConfig = collect([
                            ['short_code' => 'PMKJM', 'name' => 'Pengujian Mikrobiologi Koloni Jamur', 'unit' => 'per sampel', 'price' => 500000],
                            ['short_code' => 'PMKBA', 'name' => 'Pengujian Mikrobiologi Bakteri', 'unit' => 'per sampel', 'price' => 500000],
                        ]);

                        $ergonomiPsikologiConfig = collect([
                            [
                                'short_code' => 'ANTRO',
                                'name' => 'Pengukuran Antropometri Tenaga Kerja dan Rekomendasi Alat dan Sarana Kerja',
                                'unit' => 'per orang per pemeriksaan',
                                'price' => 50000,
                            ],
                            [
                                'short_code' => 'OBSERG',
                                'name' => 'Observasi Ergonomi',
                                'unit' => 'per orang',
                                'price' => 250000,
                            ],
                            [
                                'short_code' => 'UKKJA',
                                'name' => 'Uji Psikologi Kerja (Kuesioner)',
                                'unit' => 'per orang per pemeriksaan',
                                'price' => 150000,
                            ],
                        ]);

                        $sourceParameters = $category->parameters->keyBy('short_code');
                        $mapConfiguredRows = function ($configRows) use ($sourceParameters) {
                            return $configRows
                                ->map(function ($config) use ($sourceParameters) {
                                    if (isset($config['children']) && is_array($config['children'])) {
                                        $sourceParameter = !empty($config['short_code'])
                                            ? $sourceParameters->get($config['short_code'])
                                            : null;
                                        $children = collect($config['children'])
                                            ->map(function ($childConfig) use ($sourceParameters) {
                                                if (!empty($childConfig['short_code'])) {
                                                    $sourceParameter = $sourceParameters->get($childConfig['short_code']);
                                                    if (!$sourceParameter) {
                                                        return null;
                                                    }

                                                    $parameter = clone $sourceParameter;
                                                } else {
                                                    $parameter = (object) ['id' => null];
                                                }

                                                $parameter->name = $childConfig['name'];
                                                $parameter->unit = $childConfig['unit'];
                                                $parameter->price = $childConfig['price'];

                                                return $parameter;
                                            })
                                            ->filter()
                                            ->values()
                                            ->all();

                                        if ($children === []) {
                                            return null;
                                        }

                                        $parameter = $sourceParameter ? clone $sourceParameter : (object) ['id' => null];
                                        $parameter->name = $config['name'];
                                        $parameter->unit = $config['unit'] ?? '';
                                        $parameter->price = $config['price'] ?? collect($config['children'])->sum('price');
                                        $parameter->hide_group_price = $config['hide_group_price'] ?? false;
                                        $parameter->children = $children;

                                        return $parameter;
                                    }

                                    $sourceParameter = $sourceParameters->get($config['short_code']);
                                    if (!$sourceParameter) {
                                        return null;
                                    }

                                    $parameter = clone $sourceParameter;
                                    $parameter->name = $config['name'];
                                    $parameter->unit = $config['unit'];
                                    $parameter->price = $config['price'];

                                    return $parameter;
                                })
                                ->filter()
                                ->values();
                        };

                        $fisikaRows = $mapConfiguredRows($fisikaConfig);
                        $kimiaRows = $mapConfiguredRows($kimiaConfig);
                        $biologiRows = $mapConfiguredRows($biologiConfig);
                        $ergonomiPsikologiRows = $mapConfiguredRows($ergonomiPsikologiConfig);

                        $category->display_sections = [
                            [
                                'title' => null,
                                'column_label' => 'Parameter Fisika',
                                'rows' => $fisikaRows,
                            ],
                            [
                                'title' => null,
                                'column_label' => 'Parameter Kimia',
                                'rows' => $kimiaRows,
                            ],
                            [
                                'title' => null,
                                'column_label' => 'Parameter Biologi',
                                'rows' => $biologiRows,
                            ],
                            [
                                'title' => null,
                                'column_label' => 'Parameter Ergonomi dan Psikologi',
                                'rows' => $ergonomiPsikologiRows,
                            ],
                        ];

                        return $category;
                    }

                    $hiddenParameters = [];
                    $renamedParameters = [];
                    $mergeGroups = [];
                    $nestedGroups = [];

                    if ($category->name === 'Ambien') {
                        $hiddenParameters = [
                            'Benzene',
                            'ETANOL',
                            'METANOL',
                            'Toluene',
                            'Xylene',
                            'KADAR DEBU LOGAM (AAS)',
                            'MEDAN LISTRIK',
                        ];
                    } elseif ($category->name === 'Emisi') {
                        $renamedParameters = [
                            'LAJU ALIR' => 'Penentuan Laju Alir Cerobong',
                        ];
                    }

                    if ($hiddenParameters === [] && $renamedParameters === [] && $mergeGroups === [] && $nestedGroups === []) {
                        return $category;
                    }

                    $parameters = $category->parameters
                        ->reject(fn ($parameter) => in_array($parameter->name, $hiddenParameters, true))
                        ->map(function ($parameter) use ($renamedParameters) {
                            if (isset($renamedParameters[$parameter->name])) {
                                $parameter->name = $renamedParameters[$parameter->name];
                            }

                            return $parameter;
                        });

                    foreach ($mergeGroups as $mergeGroup) {
                        $sourceNames = $mergeGroup['source_names'];
                        $matchedParameters = $parameters
                            ->filter(fn ($parameter) => in_array($parameter->name, $sourceNames, true))
                            ->values();

                        if ($matchedParameters->count() !== count($sourceNames)) {
                            continue;
                        }

                        $mergedParameter = clone $matchedParameters->first();
                        $mergedParameter->name = $mergeGroup['name'];
                        $mergedParameter->price = $matchedParameters->first()->price;
                        $mergedParameter->merged_ids = $matchedParameters->pluck('id')->values()->all();

                        $mergedInserted = false;
                        $parameters = $parameters
                            ->map(function ($parameter) use ($sourceNames, &$mergedInserted, $mergedParameter) {
                                if (!in_array($parameter->name, $sourceNames, true)) {
                                    return $parameter;
                                }

                                if ($mergedInserted) {
                                    return null;
                                }

                                $mergedInserted = true;

                                return $mergedParameter;
                            })
                            ->filter();
                    }

                    foreach ($nestedGroups as $nestedGroup) {
                        $childNames = $nestedGroup['child_names'];
                        $matchedParameters = $parameters
                            ->filter(fn ($parameter) => in_array($parameter->name, $childNames, true))
                            ->values();

                        if ($matchedParameters->count() !== count($childNames)) {
                            continue;
                        }

                        $groupedParameter = (object) [
                            'name' => $nestedGroup['group_name'],
                            'children' => $matchedParameters->all(),
                        ];

                        $groupInserted = false;
                        $parameters = $parameters
                            ->map(function ($parameter) use ($childNames, &$groupInserted, $groupedParameter) {
                                if (!in_array($parameter->name, $childNames, true)) {
                                    return $parameter;
                                }

                                if ($groupInserted) {
                                    return null;
                                }

                                $groupInserted = true;

                                return $groupedParameter;
                            })
                            ->filter();
                    }

                    if ($category->name === 'Emisi' && !$parameters->contains(fn ($parameter) => $parameter->name === 'Penentuan Suhu dan Per Cerobong')) {
                        $parameters->push((object) [
                            'id' => null,
                            'name' => 'Penentuan Suhu dan Per Cerobong',
                            'price' => 200000,
                        ]);
                    }

                    $category->setRelation(
                        'parameters',
                        $parameters->values()
                    );

                    return $category;
                })
            : collect();

        $categoryMeta = [
            'Lingkungan Kerja' => [
                'icon' => 'fa-solid fa-vest',
                'description' => 'Layanan pengukuran dan evaluasi kondisi lingkungan kerja untuk memastikan keselamatan, kenyamanan, dan kepatuhan terhadap standar K3.',
            ],
            'Ambien' => [
                'icon' => 'bi bi-clouds',
                'description' => 'Pengukuran kualitas udara ambien di lingkungan sekitar perusahaan untuk memastikan area tetap aman dan tidak berdampak negatif.',
            ],
            'Emisi' => [
                'icon' => 'bi bi-building-fill-gear',
                'description' => 'Pengujian tingkat emisi industri guna memastikan aktivitas operasional memenuhi baku mutu lingkungan.',
            ],
            'Kesehatan' => [
                'icon' => 'fa-solid fa-notes-medical',
                'description' => 'Pemeriksaan kesehatan kerja untuk memastikan kesehatan tenaga kerja sesuai standar K3.',
            ],
            'Pelatihan' => [
                'icon' => 'bi bi-mortarboard-fill',
                'description' => 'Program pelatihan peningkatan kompetensi sesuai standar K3 dan kebutuhan industri.',
            ],
        ];

        $emisiCategory = $categories->firstWhere('name', 'Emisi');
        $emisiPackages = collect();

        if (
            $emisiCategory
            && Schema::hasTable('service_packages')
            && Schema::hasTable('service_package_items')
        ) {
            $packageInfoMeta = [
                'EMS_GENSET_1' => [
                    'title' => 'Rincian Paket Genset 1',
                    'items' => [
                        'Jumlah komponen paket: 3',
                        'Parameter paket: NOx, CO, dan O2',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Total paket: Rp. 1.350.000',
                    ],
                ],
                'EMS_GENSET_2' => [
                    'title' => 'Rincian Paket Genset 2',
                    'items' => [
                        'Jumlah komponen paket: 4',
                        'Parameter paket: NOx, CO, Laju Alir, dan O2',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Total paket: Rp. 2.100.000',
                    ],
                ],
                'EMS_GENSET_3' => [
                    'title' => 'Rincian Paket Genset 3',
                    'items' => [
                        'Jumlah komponen paket: 5',
                        'Parameter paket: SO2, NOx, CO, O2, dan Total Partikulat',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Total Partikulat: Rp. 450.000 per cerobong',
                        'Total paket: Rp. 2.250.000',
                    ],
                ],
                'EMS_GENSET_4' => [
                    'title' => 'Rincian Paket Genset 4',
                    'items' => [
                        'Jumlah komponen paket: 6',
                        'Parameter paket: SO2, NOx, CO, O2, Total Partikulat, dan Laju Alir',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Total Partikulat: Rp. 450.000 per cerobong',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'Total paket: Rp. 3.000.000',
                    ],
                ],
                'EMS_GENSET_5' => [
                    'title' => 'Rincian Paket Genset 5',
                    'items' => [
                        'Jumlah komponen paket: 4',
                        'Parameter paket: SO2, NOx, CO, dan O2',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Total paket: Rp. 1.800.000',
                    ],
                ],
                'EMS_GENSET_6' => [
                    'title' => 'Rincian Paket Genset 6',
                    'items' => [
                        'Jumlah komponen paket: 5',
                        'Parameter paket: SO2, NOx, CO, O2, dan Laju Alir',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'Total paket: Rp. 2.550.000',
                    ],
                ],
                'EMS_GENSET_7' => [
                    'title' => 'Rincian Paket Genset 7',
                    'items' => [
                        'Jumlah komponen paket: 5',
                        'Parameter paket: SO2, NOx, CO, O2, dan Total Partikulat',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Total Partikulat: Rp. 450.000 per cerobong',
                        'Total paket: Rp. 2.250.000',
                    ],
                ],
                'EMS_GENSET_8' => [
                    'title' => 'Rincian Paket Genset 8',
                    'items' => [
                        'Jumlah komponen paket: 6',
                        'Parameter paket: SO2, NOx, CO, O2, Total Partikulat, dan Laju Alir',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Total Partikulat: Rp. 450.000 per cerobong',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'Total paket: Rp. 3.000.000',
                    ],
                ],
                'EMS_GENSET_9' => [
                    'title' => 'Rincian Paket Genset 9',
                    'items' => [
                        'Jumlah komponen paket: 4',
                        'Parameter paket: SO2, NOx, CO, dan O2',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Total paket: Rp. 1.800.000',
                    ],
                ],
                'EMS_GENSET_10' => [
                    'title' => 'Rincian Paket Genset 10',
                    'items' => [
                        'Jumlah komponen paket: 5',
                        'Parameter paket: SO2, NOx, CO, O2, dan Laju Alir',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NOx: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'Total paket: Rp. 2.550.000',
                    ],
                ],
                'EMS_BOILER_GAS' => [
                    'title' => 'Rincian Paket Boiler Gas',
                    'items' => [
                        'Jumlah komponen paket: 3',
                        'Parameter paket: SO2, NO2, dan Laju Alir',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'Total paket: Rp. 1.650.000',
                    ],
                ],
                'EMS_BOILER' => [
                    'title' => 'Rincian Paket Boiler',
                    'items' => [
                        'Jumlah komponen paket: 6',
                        'Parameter paket: SO2, NO2, Total Partikulat, O2, Laju Alir, dan Opasitas',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'Total Partikulat: Rp. 450.000 per cerobong',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'Opasitas: Rp. 350.000 per cerobong',
                        'Total paket: Rp. 2.900.000',
                    ],
                ],
                'EMS_BOILER_BB_LAINNYA' => [
                    'title' => 'Rincian Paket Boiler BB Lainnya',
                    'items' => [
                        'Jumlah komponen paket: 17',
                        'Parameter paket: NO2, SO2, Total Partikulat, O2, Laju Alir, NH3, HF, HCL, Hg, Cl2, H2S, Opasitas, Cd, As, Zn, Pb, dan Sb',
                        'NO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'Total Partikulat: Rp. 450.000 per cerobong',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'NH3: Rp. 150.000 per sampel',
                        'HF: Rp. 150.000 per sampel',
                        'HCL: Rp. 150.000 per sampel',
                        'Hg: Rp. 150.000 per sampel',
                        'Cl2: Rp. 150.000 per sampel',
                        'H2S: Rp. 150.000 per sampel',
                        'Opasitas: Rp. 350.000 per cerobong',
                        'Cd: Rp. 150.000 per sampel',
                        'As: Rp. 150.000 per sampel',
                        'Zn: Rp. 150.000 per sampel',
                        'Pb: Rp. 150.000 per sampel',
                        'Sb: Rp. 150.000 per sampel',
                        'Total paket: Rp. 4.550.000',
                    ],
                ],
                'EMS_INCINERATOR' => [
                    'title' => 'Rincian Paket Incinerator',
                    'items' => [
                        'Jumlah komponen paket: 16',
                        'Parameter paket: SO2, NO2, CO, Total Partikulat, Laju Alir, O2, HF, HCL, HC, Hg, Opasitas, As, Cd, Cr, Pb, dan Tl',
                        'SO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'NO2: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'CO: Rp. 150.000 per sampel (3 x Rp. 150.000 = Rp. 450.000)',
                        'Total Partikulat: Rp. 450.000 per cerobong',
                        'Laju Alir: Rp. 750.000 per cerobong',
                        'O2 (Komposisi Gas): Rp. 450.000 per cerobong',
                        'HF: Rp. 150.000 per sampel',
                        'HCL: Rp. 150.000 per sampel',
                        'HC: Rp. 150.000 per sampel',
                        'Hg: Rp. 150.000 per sampel',
                        'Opasitas: Rp. 350.000 per cerobong',
                        'As: Rp. 150.000 per sampel',
                        'Cd: Rp. 150.000 per sampel',
                        'Cr: Rp. 150.000 per sampel',
                        'Pb: Rp. 150.000 per sampel',
                        'Tl: Rp. 150.000 per sampel',
                        'Total paket: Rp. 4.700.000',
                    ],
                ],
            ];

            $normalizePackageLabel = static function (?string $label): string {
                $text = trim((string) $label);
                if ($text === '') {
                    return '';
                }

                if (preg_match('/^TP(\b|\s|\()/i', $text) === 1) {
                    return preg_replace('/^TP/i', 'Total Partikulat', $text, 1) ?: $text;
                }

                return $text;
            };

            $emisiPackages = ServicePackage::query()
                ->with(['items.parameter'])
                ->where('service_category_id', $emisiCategory->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(function (ServicePackage $package) use ($normalizePackageLabel, $packageInfoMeta) {
                    $items = $package->items
                        ->sortBy('sort_order')
                        ->values();

                    $labels = $items
                        ->map(fn ($item) => $normalizePackageLabel($item->label ?: ($item->parameter?->name ?? null)))
                        ->filter()
                        ->values()
                        ->all();

                    $parameterIds = $items
                        ->pluck('service_parameter_id')
                        ->filter(fn ($id) => !is_null($id))
                        ->map(fn ($id) => (int) $id)
                        ->filter(fn ($id) => $id > 0)
                        ->unique()
                        ->values()
                        ->all();

                    $calculatedPrice = $items
                        ->filter(fn ($item) => !is_null($item->service_parameter_id) && $item->parameter)
                        ->unique('service_parameter_id')
                        ->sum(fn ($item) => (float) ($item->parameter?->price ?? 0));

                    if ($calculatedPrice <= 0) {
                        $calculatedPrice = (float) $package->price;
                    }

                    $displayPrice = (float) $package->price;
                    if ($displayPrice <= 0) {
                        $displayPrice = $calculatedPrice;
                    }

                    return [
                        'short_code' => $package->short_code,
                        'badge' => $package->badge ?: 'Emisi',
                        'title' => $package->name,
                        'subtitle' => $package->subtitle ?: '',
                        'parameters' => $labels,
                        'parameter_ids' => $parameterIds,
                        'price' => 'Rp. ' . number_format($displayPrice, 0, ',', '.') . ',00',
                        'unit' => $package->unit ?: 'per lokasi',
                        'info' => $packageInfoMeta[$package->short_code] ?? null,
                    ];
                })
                ->values();
        }

        return view('daftar_pelayanan', [
            'categories' => $categories,
            'categoryMeta' => $categoryMeta,
            'emisiPackages' => $emisiPackages,
        ]);
    }

    public function jejaringUniversitas()
    {
        return $this->renderJejaringPage('universitas');
    }

    public function jejaringPjk3()
    {
        return $this->renderJejaringPage('pjk3');
    }

    public function jejaringPerusahaan()
    {
        return $this->renderJejaringPage('perusahaan');
    }

    public function jejaringInstansiWilayahKerja()
    {
        return $this->renderJejaringPage('instansi_wilayah_kerja');
    }

    public function jejaringInstansi()
    {
        return $this->renderJejaringPage('instansi');
    }

    public function sitemap(): Response
    {
        $jejaringLastmod = function (string $category) {
            if (!Schema::hasTable('jejaring_entries')) {
                return null;
            }

            return JejaringEntry::query()
                ->where('category', $category)
                ->max('updated_at');
        };

        $pages = collect([
            [
                'loc' => route('home'),
                'changefreq' => 'daily',
                'priority' => '1.0',
                'lastmod' => null,
            ],
            [
                'loc' => route('daftar_pelayanan'),
                'changefreq' => 'weekly',
                'priority' => '0.9',
                'lastmod' => Schema::hasTable('service_categories')
                    ? ServiceCategory::query()->max('updated_at')
                    : null,
            ],
            [
                'loc' => route('jejaring.universitas'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => $jejaringLastmod('universitas'),
            ],
            [
                'loc' => route('jejaring.pjk3'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => $jejaringLastmod('pjk3'),
            ],
            [
                'loc' => route('jejaring.perusahaan'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => $jejaringLastmod('perusahaan'),
            ],
            [
                'loc' => route('jejaring.instansi-wilayah-kerja'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => $jejaringLastmod('instansi_wilayah_kerja'),
            ],
            [
                'loc' => route('jejaring.instansi'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => $jejaringLastmod('instansi'),
            ],
            [
                'loc' => route('berita'),
                'changefreq' => 'daily',
                'priority' => '0.9',
                'lastmod' => Schema::hasTable('beritas')
                    ? Berita::query()->max('updated_at')
                    : null,
            ],
            [
                'loc' => route('kontak'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => null,
            ],
            [
                'loc' => route('visi_misi'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => null,
            ],
            [
                'loc' => route('alur_pelayanan'),
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'lastmod' => null,
            ],
            [
                'loc' => route('struktur'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => null,
            ],
            [
                'loc' => route('sarana_prasarana'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => null,
            ],
            [
                'loc' => route('video_profil'),
                'changefreq' => 'monthly',
                'priority' => '0.6',
                'lastmod' => null,
            ],
        ])->merge(
            Schema::hasTable('beritas')
                ? Berita::query()
                    ->select(['slug', 'updated_at'])
                    ->latest('updated_at')
                    ->get()
                    ->map(fn (Berita $article) => [
                        'loc' => route('berita.show', $article->slug),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                        'lastmod' => $article->updated_at,
                    ])
                : collect()
        );

        return response()
            ->view('sitemap', ['pages' => $pages])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function renderJejaringPage(string $category)
    {
        $categoryMeta = JejaringEntry::categoryDefinition($category);

        abort_unless($categoryMeta !== null, 404);

        $entries = collect();

        if (Schema::hasTable('jejaring_entries')) {
            $entries = JejaringEntry::query()
                ->forCategory($category)
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();
        } elseif ($category === 'universitas') {
            $entries = collect([
                (object) [
                    'name' => 'Universitas Airlangga',
                    'address' => 'Mulyorejo, Kec. Mulyorejo, Surabaya, Jawa Timur 60115',
                    'website_url' => 'https://www.unair.ac.id',
                ],
            ]);
        }

        return view('jejaring_index', [
            'entries' => $entries,
            'categoryMeta' => $categoryMeta,
        ]);
    }
}
