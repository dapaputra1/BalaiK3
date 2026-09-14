<?php

namespace App\Http\Controllers;

use App\Models\Koding;
use App\Models\KodingItem;
use App\Models\ParameterLod;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\PengujianDokumenParameter;
use App\Models\Prepanalisa;
use App\Models\PrepanalisaItem;
use App\Models\User;
use App\Services\FormulaReference\DebuExcelReferenceService;
use App\Services\FormulaReference\BenzeneExcelReferenceService;
use App\Services\FormulaReference\H2sExcelReferenceService;
use App\Services\FormulaReference\HclEmisiExcelReferenceService;
use App\Services\FormulaReference\HfEmisiExcelReferenceService;
use App\Services\FormulaReference\HgEmisiExcelReferenceService;
use App\Services\FormulaReference\HgAasExcelReferenceService;
use App\Services\FormulaReference\Nh3ExcelReferenceService;
use App\Services\FormulaReference\No2ExcelReferenceService;
use App\Services\FormulaReference\PbExcelReferenceService;
use App\Services\FormulaReference\CdExcelReferenceService;
use App\Services\FormulaReference\AsExcelReferenceService;
use App\Services\FormulaReference\CoExcelReferenceService;
use App\Services\FormulaReference\SbExcelReferenceService;
use App\Services\FormulaReference\TlExcelReferenceService;
use App\Services\FormulaReference\CrExcelReferenceService;
use App\Services\FormulaReference\CuExcelReferenceService;
use App\Services\FormulaReference\TolueneExcelReferenceService;
use App\Services\FormulaReference\XyleneExcelReferenceService;
use App\Services\FormulaReference\ZnExcelReferenceService;
use App\Services\FormulaReference\AbsorbanceReferenceService;
use App\Services\FormulaReference\OxExcelReferenceService;
use App\Services\FormulaReference\So2AmbienExcelReferenceService;
use App\Models\WorkflowStep;
use App\Services\Rumus\PrepanalisaRumusRegistry;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class PrepanalisaController extends Controller
{
    public function index()
    {
        $this->ensureAccess();

        $step = WorkflowStep::where('kode', 'preparasi_analisa')->first();
        $permohonanQuery = Permohonan::with([
            'company',
            'pengujian.lokasi.dokumen.files',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'koding.items',
            'prepanalisa.items.assignedUser',
        ]);
        if ($step) {
            $permohonanQuery->with(['steps' => function ($query) use ($step) {
                $query->where('step_id', $step->id)
                    ->orderByDesc('started_at');
            }]);
        }

        if ($step) {
            $permohonanQuery->whereHas('steps', function ($query) use ($step) {
                $query->where('step_id', $step->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            });
        }

        $permohonans = $permohonanQuery->oldest()->get();
        $orders = $permohonans->map(function (Permohonan $permohonan) use ($step) {
            $company = $permohonan->company;
            $pengujian = $permohonan->pengujian;
            $koding = $permohonan->koding;
            $prepanalisa = $permohonan->prepanalisa;
            $kodingMap = $koding ? $koding->items->keyBy('pengujian_dokumen_parameter_id') : collect();
            $legacyKodingByDoc = $koding
                ? $koding->items
                    ->filter(function ($item) {
                        return empty($item->pengujian_dokumen_parameter_id) && !empty($item->pengujian_dokumen_id);
                    })
                    ->groupBy('pengujian_dokumen_id')
                    ->map(fn ($items) => $items->sortBy('id')->values())
                : collect();
            $legacyKodingCursorByDoc = [];
            $prepanalisaMap = $prepanalisa ? $prepanalisa->items->keyBy('pengujian_dokumen_parameter_id') : collect();
            $revisiIds = collect();
            if ($prepanalisa && $prepanalisa->items->count()) {
                $revisiIds = $prepanalisa->items
                    ->where('verif_status', 'revisi')
                    ->pluck('pengujian_dokumen_parameter_id')
                    ->filter()
                    ->values();
            }
            $revisiNotes = collect();
            if ($prepanalisa && $prepanalisa->items->count()) {
                $revisiNotes = $prepanalisa->items
                    ->where('verif_status', 'revisi')
                    ->pluck('verif_note')
                    ->filter()
                    ->values()
                    ->unique()
                    ->values();
            }

            $parameterMap = [];
            if ($pengujian) {
                $pengujian->lokasi->sortBy('urutan')->each(function ($lokasi) use (&$parameterMap, $kodingMap, $prepanalisaMap, $revisiIds, $legacyKodingByDoc, &$legacyKodingCursorByDoc) {
                    $lokasi->dokumen->sortBy('urutan')->each(function ($dokumen) use (&$parameterMap, $kodingMap, $prepanalisaMap, $lokasi, $revisiIds, $legacyKodingByDoc, &$legacyKodingCursorByDoc) {
                        $files = $dokumen->files->map(function ($file) {
                            return [
                                'id' => $file->id,
                                'name' => $file->original_name,
                                'url' => $file->file_path ? route('pengujian.files.show', $file->id) : null,
                            ];
                        })->values();

                        $dokumen->parameters->sortBy('urutan')->each(function ($docParam) use (
                            &$parameterMap,
                            $lokasi,
                            $dokumen,
                            $files,
                            $prepanalisaMap,
                            $kodingMap,
                            $revisiIds,
                            $legacyKodingByDoc,
                            &$legacyKodingCursorByDoc
                        ) {
                            if ($docParam->is_direct) {
                                return;
                            }
                            $service = $docParam->serviceParameter;
                            // Tampilkan semua parameter; revisi hanya ditandai lewat badge/status.
                            $serviceName = strtolower((string) ($service?->name ?? ''));
                            $categoryName = strtolower((string) ($service?->category?->name ?? ''));
                            $isNo2Ambien = str_contains($serviceName, 'no2') && str_contains($categoryName, 'ambien');
                            $kodeFinal = '';
                            $kodingItemId = null;
                            $preItem = null;
                            if ($prepanalisaMap instanceof \Illuminate\Support\Collection) {
                                $preItem = $prepanalisaMap->get($docParam->id);
                            }
                            if ($kodingMap instanceof \Illuminate\Support\Collection) {
                                $item = $kodingMap->get($docParam->id);
                                if (!$item && $legacyKodingByDoc instanceof \Illuminate\Support\Collection) {
                                    $legacyPool = $legacyKodingByDoc->get($dokumen->id, collect());
                                    $cursor = (int) ($legacyKodingCursorByDoc[$dokumen->id] ?? 0);
                                    if ($legacyPool instanceof \Illuminate\Support\Collection && $cursor < $legacyPool->count()) {
                                        $item = $legacyPool->get($cursor);
                                        $legacyKodingCursorByDoc[$dokumen->id] = $cursor + 1;
                                    }
                                }
                                $kodeFinal = $item?->kode ?? '';
                                $kodingItemId = $item?->id;
                            }
                            if (!$kodeFinal) {
                                $kodeFinal = $preItem?->kode_koding ?? '';
                            }
                            $paramKey = $service?->id ?: $docParam->id;
                            if (!isset($parameterMap[$paramKey])) {
                                $parameterMap[$paramKey] = [
                                    'id' => $service?->id ?? $docParam->id,
                                    'nama' => $service?->name ?? '-',
                                    'kategori' => $service?->category?->short_code ?: ($service?->category?->name ?? '-'),
                                    'metode' => '-',
                                    'lokasi' => [],
                                ];
                            }

                            $parameterMap[$paramKey]['lokasi'][] = [
                                'nama' => $lokasi->nama_lokasi ?? '-',
                                'dokumen_label' => $dokumen->label ?? '-',
                                'dokumen_id' => $dokumen->id,
                                'dokumen_files' => $files,
                                'koding' => $kodeFinal,
                                'koding_item_id' => $kodingItemId,
                                'assigned_user_id' => $preItem?->assigned_user_id,
                                'assigned_analis' => $preItem?->assignedUser?->name,
                                'is_done' => (bool) ($preItem?->is_done ?? false),
                                'verif_status' => $preItem?->verif_status,
                                'verif_note' => $preItem?->verif_note,
                                'saved' => $preItem ? [
                                    'skpm' => $preItem->data_skpm,
                                    'hasil_baca' => $preItem->data_hasil_baca,
                                    'hasil_perhitungan' => $preItem->data_hasil_perhitungan,
                                ] : null,
                                'samples' => $kodeFinal ? [$kodeFinal] : [],
                                'pengujian_dokumen_parameter_id' => $docParam->id,
                                'service_parameter_id' => $service?->id,
                            ];
                        });
                    });
                });
            }

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'masuk_at_unix' => optional(
                    $step
                        ? $permohonan->steps->firstWhere('step_id', $step->id)?->started_at
                        : null
                )->timestamp,
                'lokasi_induk' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                'jadwal_mulai' => $permohonan->jadwal_mulai,
                'jadwal_selesai' => $permohonan->jadwal_selesai,
                'has_revisi' => $revisiIds->isNotEmpty(),
                'revisi_notes' => $revisiNotes->values()->all(),
                'parameter' => array_values($parameterMap),
            ];
        });

        return view('admin.superadmin_prepanalisa', [
            'orders' => $orders,
            'formulaReferences' => $this->loadLightweightFormulaReferences(),
            'formulaReferencesUrl' => route((auth()->user()?->role === 'analis' ? 'analis' : 'superadmin') . '.prepanalisa.formula-references'),
            'routePrefix' => auth()->user()?->role === 'analis' ? 'analis' : 'superadmin',
        ]);
    }

    public function formulaReferences(Request $request): JsonResponse
    {
        $this->ensureAccess();

        $keys = array_values(array_unique(array_filter(
            (array) $request->query('keys', []),
            fn ($key) => is_string($key) && $key !== ''
        )));

        return response()->json($this->loadFormulaReferences($keys));
    }

    public function history(Request $request)
    {
        $this->ensureAccess();

        $filters = $this->parseHistoryFilters($request);
        $rows = $this->buildHistoryRows($filters);
        $years = Prepanalisa::query()
            ->where('status', 'sent_to_verifikasi')
            ->selectRaw('DISTINCT YEAR(updated_at) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->filter()
            ->values();

        return view('admin.superadmin_prepanalisa_riwayat', [
            'rows' => $rows,
            'years' => $years,
            'filters' => $filters,
            'routePrefix' => auth()->user()?->role === 'analis' ? 'analis' : 'superadmin',
        ]);
    }

    public function exportHistory(Request $request): Response
    {
        $this->ensureAccess();

        $filters = $this->parseHistoryFilters($request);
        $rows = $this->buildHistoryRows($filters);
        $filename = 'riwayat_prepanalisa_' . now()->format('Ymd_His') . '.xls';
        $createdAt = now()->format('d-m-Y H:i');

        $html = '<html><head><meta charset="UTF-8"></head><body>';
        $html .= '<table border="1" cellspacing="0" cellpadding="6" style="border-collapse:collapse;font-family:Calibri,Arial,sans-serif;font-size:11pt;">';
        $html .= '<tr><th colspan="8" style="background:#15406A;color:#fff;font-size:14pt;text-align:center;">Rekap Riwayat Preparasi Analisa</th></tr>';
        $html .= '<tr><td colspan="8" style="background:#f5f7fa;color:#444;">Dibuat: ' . e($createdAt) . ' | Total data: ' . $rows->count() . '</td></tr>';
        $html .= '<tr style="background:#dbe9f6;font-weight:bold;text-align:center;">';
        $html .= '<th>Tanggal Selesai</th>';
        $html .= '<th>Kode Order</th>';
        $html .= '<th>Perusahaan</th>';
        $html .= '<th>Parameter</th>';
        $html .= '<th>Kategori</th>';
        $html .= '<th>Kode Koding</th>';
        $html .= '<th>Analis</th>';
        $html .= '<th>Status Item</th>';
        $html .= '</tr>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td style="text-align:center;">' . e($row['tanggal_selesai_label']) . '</td>';
            $html .= '<td>' . e($row['kode_order']) . '</td>';
            $html .= '<td>' . e($row['perusahaan']) . '</td>';
            $html .= '<td>' . e($row['parameter']) . '</td>';
            $html .= '<td>' . e($row['kategori']) . '</td>';
            $html .= '<td>' . e($row['kode_koding']) . '</td>';
            $html .= '<td>' . e($row['analis']) . '</td>';
            $html .= '<td style="text-align:center;">' . e($row['status_item']) . '</td>';
            $html .= '</tr>';
        }

        if ($rows->isEmpty()) {
            $html .= '<tr><td colspan="8" style="text-align:center;color:#777;">Tidak ada data sesuai filter.</td></tr>';
        }

        $html .= '</table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function loadLightweightFormulaReferences(): array
    {
        return [
            'absorbance' => app(AbsorbanceReferenceService::class)->getAllReferences(),
            'parameter_lods' => $this->loadParameterLodReferences(),
        ];
    }

    private function loadFormulaReferences(array $keys = []): array
    {
        $loaders = [
            'debu_ambien' => fn () => app(DebuExcelReferenceService::class)->getReference(),
            'benzene' => fn () => app(BenzeneExcelReferenceService::class)->getReference(),
            'toluene' => fn () => app(TolueneExcelReferenceService::class)->getReference(),
            'xylene' => fn () => app(XyleneExcelReferenceService::class)->getReference(),
            'h2s' => fn () => app(H2sExcelReferenceService::class)->getReference(),
            'hcl_emisi' => fn () => app(HclEmisiExcelReferenceService::class)->getReference(),
            'hf_emisi' => fn () => app(HfEmisiExcelReferenceService::class)->getReference(),
            'hg_emisi' => fn () => app(HgEmisiExcelReferenceService::class)->getReference(),
            'nh3' => fn () => app(Nh3ExcelReferenceService::class)->getReference(),
            'no2' => fn () => app(No2ExcelReferenceService::class)->getReference(),
            'pb' => fn () => app(PbExcelReferenceService::class)->getReference(),
            'cd' => fn () => app(CdExcelReferenceService::class)->getReference(),
            'as' => fn () => app(AsExcelReferenceService::class)->getReference(),
            'hg_aas' => fn () => app(HgAasExcelReferenceService::class)->getReference(),
            'co' => fn () => app(CoExcelReferenceService::class)->getReference(),
            'sb' => fn () => app(SbExcelReferenceService::class)->getReference(),
            'tl' => fn () => app(TlExcelReferenceService::class)->getReference(),
            'cr' => fn () => app(CrExcelReferenceService::class)->getReference(),
            'cu' => fn () => app(CuExcelReferenceService::class)->getReference(),
            'zn' => fn () => app(ZnExcelReferenceService::class)->getReference(),
            'ox' => fn () => app(OxExcelReferenceService::class)->getReference(),
            'so2_ambien' => fn () => app(So2AmbienExcelReferenceService::class)->getReference(),
        ];

        $selectedKeys = $keys === []
            ? array_keys($loaders)
            : array_values(array_intersect($keys, array_keys($loaders)));

        $references = [];
        foreach ($selectedKeys as $key) {
            $references[$key] = $loaders[$key]();
        }

        return $references;
    }

    private function loadParameterLodReferences(): array
    {
        return ParameterLod::query()
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function (ParameterLod $lod) {
                return [
                    (string) $lod->service_parameter_id => [
                        'kons' => $lod->kons,
                        'vol' => $lod->vol,
                        'waktu' => $lod->waktu,
                        'fr' => $lod->fr,
                        'sk' => $lod->sk,
                        'pm' => $lod->pm,
                        'sample_kons' => $lod->sample_kons,
                        'sample_vol' => $lod->sample_vol,
                        'sample_waktu' => $lod->sample_waktu,
                        'sample_fr' => $lod->sample_fr,
                        'sample_sk' => $lod->sample_sk,
                        'sample_pm' => $lod->sample_pm,
                        'sample_ppm' => $lod->sample_ppm,
                        'sample_ugm3' => $lod->sample_ugm3,
                    ],
                ];
            })
            ->all();
    }

    public function exportStdKalibrasiExcel(Request $request): Response
    {
        $this->ensureAccess();

        $data = $request->validate([
            'file_name' => ['nullable', 'string', 'max:255'],
            'parameter_name' => ['nullable', 'string', 'max:255'],
            'sample_type' => ['nullable', 'string', 'max:255'],
            'receipt_date' => ['nullable', 'string', 'max:255'],
            'analysis_date' => ['nullable', 'string', 'max:255'],
            'analis_name' => ['nullable', 'string', 'max:255'],
            'curve_label' => ['nullable', 'string', 'max:255'],
            'curve_y' => ['nullable'],
            'curve_x' => ['nullable'],
            'rows' => ['nullable', 'array'],
            'rows.*.no_sampel' => ['nullable', 'string', 'max:255'],
            'rows.*.volume' => ['nullable'],
            'rows.*.waktu_baca' => ['nullable', 'string', 'max:255'],
            'rows.*.hasil_baca' => ['nullable'],
            'rows.*.kandungan' => ['nullable'],
            'rows.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        if (!class_exists(ZipArchive::class)) {
            return $this->exportStdKalibrasiExcelViaPowerShell($data);
        }

        $templateConfig = $this->resolveStdKalibrasiTemplateConfig($data);
        $templatePath = $templateConfig['path'] ?? null;
        $templateProfile = is_array($templateConfig['profile'] ?? null) ? $templateConfig['profile'] : [];
        if (!$templatePath || !is_file($templatePath)) {
            abort(404, 'Template std kalibrasi tidak ditemukan.');
        }

        $tempDir = storage_path('app/tmp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $tempPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('std_kalibrasi_pb_', true) . '.xlsx';
        if (!@copy($templatePath, $tempPath)) {
            abort(500, 'Template std kalibrasi Pb tidak dapat disiapkan.');
        }

        $zip = new ZipArchive();
        if ($zip->open($tempPath) !== true) {
            @unlink($tempPath);
            abort(500, 'File Excel std kalibrasi tidak dapat dibuka.');
        }

        try {
            $rowStart = (int) ($templateProfile['row_start'] ?? 17);
            $rowCount = max(1, (int) ($templateProfile['row_count'] ?? 6));
            $curveYCell = (string) ($templateProfile['curve_y_cell'] ?? 'G27');
            $curveXCell = (string) ($templateProfile['curve_x_cell'] ?? 'G28');
            $cells = is_array($templateProfile['cells'] ?? null) ? $templateProfile['cells'] : [];
            $rows = array_values(array_slice((array) ($data['rows'] ?? []), 0, $rowCount));
            $sheetXml = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
            $workbookXml = (string) $zip->getFromName('xl/workbook.xml');
            $workbookRelsXml = (string) $zip->getFromName('xl/_rels/workbook.xml.rels');
            $contentTypesXml = (string) $zip->getFromName('[Content_Types].xml');
            $chartXml = (string) $zip->getFromName('xl/charts/chart1.xml');

            $sheetDom = $this->loadXmlDocument($sheetXml);
            $sheetXPath = new DOMXPath($sheetDom);
            $sheetXPath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            $parameterName = trim((string) ($data['parameter_name'] ?? '')) ?: 'Timbal (Pb)';
            $sampleType = trim((string) ($data['sample_type'] ?? '')) ?: 'LK';
            $receiptDate = trim((string) ($data['receipt_date'] ?? '')) ?: Carbon::now()->locale('id')->translatedFormat('d F Y');
            $analysisDate = trim((string) ($data['analysis_date'] ?? '')) ?: Carbon::now()->locale('id')->translatedFormat('d F Y');
            $analisName = trim((string) ($data['analis_name'] ?? '')) ?: '-';
            $curveLabel = trim((string) ($data['curve_label'] ?? '')) ?: 'Pb';
            $curveY = $this->normalizeExcelNumber($data['curve_y'] ?? null);
            $curveX = $this->normalizeExcelNumber($data['curve_x'] ?? null);

            $this->setWorksheetCellString($sheetDom, $sheetXPath, (string) ($cells['parameter_name'] ?? 'D7'), ': ' . $parameterName);
            $this->setWorksheetCellString($sheetDom, $sheetXPath, (string) ($cells['sample_type'] ?? 'D8'), ': ' . $sampleType);
            $this->setWorksheetCellString($sheetDom, $sheetXPath, (string) ($cells['condition'] ?? 'D9'), ': Baik');
            if (!empty($cells['receipt_date'])) {
                $this->setWorksheetCellString($sheetDom, $sheetXPath, (string) $cells['receipt_date'], ': ' . $receiptDate);
            }
            $this->setWorksheetCellString($sheetDom, $sheetXPath, (string) ($cells['analysis_date'] ?? 'D11'), ': ' . $analysisDate);
            $this->setWorksheetCellString($sheetDom, $sheetXPath, (string) ($cells['analis_name'] ?? 'D12'), ': ' . $analisName);

            $xValues = [];
            $yValues = [];
            for ($i = 0; $i < $rowCount; $i++) {
                $rowNumber = $rowStart + $i;
                $row = (array) ($rows[$i] ?? []);
                $sampleNo = trim((string) ($row['no_sampel'] ?? ''));
                $volume = $this->normalizeExcelNumber($row['volume'] ?? null);
                $waktuBaca = $this->convertTimeToExcelSerial($row['waktu_baca'] ?? null);
                $hasilBaca = $this->normalizeExcelNumber($row['hasil_baca'] ?? null);
                $kandungan = $this->normalizeExcelNumber($row['kandungan'] ?? null);
                $keterangan = trim((string) ($row['keterangan'] ?? ''));

                $this->setWorksheetCellString($sheetDom, $sheetXPath, 'B' . $rowNumber, $sampleNo !== '' ? $sampleNo : (string) ($i + 1));
                $this->setWorksheetCellNumber($sheetDom, $sheetXPath, 'C' . $rowNumber, $volume);
                $this->setWorksheetCellNumber($sheetDom, $sheetXPath, 'D' . $rowNumber, $waktuBaca);
                $this->setWorksheetCellNumber($sheetDom, $sheetXPath, 'E' . $rowNumber, $hasilBaca);
                $this->setWorksheetCellNumber($sheetDom, $sheetXPath, 'F' . $rowNumber, $kandungan);
                if ($keterangan !== '') {
                    $this->setWorksheetCellString($sheetDom, $sheetXPath, 'G' . $rowNumber, $keterangan);
                } else {
                    $this->clearWorksheetCell($sheetXPath, 'G' . $rowNumber);
                }

                $xValues[] = $kandungan ?? 0.0;
                $yValues[] = $hasilBaca ?? 0.0;
            }

            $this->setWorksheetCellNumber($sheetDom, $sheetXPath, $curveYCell, $curveY);
            $this->setWorksheetCellNumber(
                $sheetDom,
                $sheetXPath,
                $curveXCell,
                $curveX,
                $curveY !== null && $curveY != 0.0 ? '1/' . $curveYCell : null
            );

            $zip->addFromString('xl/worksheets/sheet1.xml', $sheetDom->saveXML());

            if ($chartXml !== '') {
                $chartDom = $this->loadXmlDocument($chartXml);
                $chartXPath = new DOMXPath($chartDom);
                $chartXPath->registerNamespace('c', 'http://schemas.openxmlformats.org/drawingml/2006/chart');
                $chartXPath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
                $this->updateChartTitle($chartXPath, 'kurva kalibrasi ' . $curveLabel);
                $this->updateChartCache($chartDom, $chartXPath, '//c:scatterChart/c:ser/c:xVal/c:numRef/c:numCache', $xValues, '0.000');
                $this->updateChartCache($chartDom, $chartXPath, '//c:scatterChart/c:ser/c:yVal/c:numRef/c:numCache', $yValues, '0.0000');
                $zip->addFromString('xl/charts/chart1.xml', $chartDom->saveXML());
            }

            if ($workbookXml !== '') {
                $workbookDom = $this->loadXmlDocument($workbookXml);
                $workbookXPath = new DOMXPath($workbookDom);
                $workbookXPath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $this->removeWorkbookExternalReferences($workbookXPath);
                $this->enableWorkbookFullCalc($workbookXPath);
                $zip->addFromString('xl/workbook.xml', $workbookDom->saveXML());
            }

            if ($workbookRelsXml !== '') {
                $workbookRelsDom = $this->loadXmlDocument($workbookRelsXml);
                $workbookRelsXPath = new DOMXPath($workbookRelsDom);
                $workbookRelsXPath->registerNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');
                foreach ($workbookRelsXPath->query('//rel:Relationship[contains(@Type, "/externalLink")]') as $node) {
                    $node->parentNode?->removeChild($node);
                }
                $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsDom->saveXML());
            }

            if ($contentTypesXml !== '') {
                $contentTypesDom = $this->loadXmlDocument($contentTypesXml);
                $contentTypesXPath = new DOMXPath($contentTypesDom);
                $contentTypesXPath->registerNamespace('ct', 'http://schemas.openxmlformats.org/package/2006/content-types');
                foreach ($contentTypesXPath->query('//ct:Override[@PartName="/xl/externalLinks/externalLink1.xml"]') as $node) {
                    $node->parentNode?->removeChild($node);
                }
                $zip->addFromString('[Content_Types].xml', $contentTypesDom->saveXML());
            }

            $this->replaceStdKalibrasiLogoInArchive($zip);
            $zip->deleteName('xl/externalLinks/externalLink1.xml');
            $zip->deleteName('xl/externalLinks/_rels/externalLink1.xml.rels');
        } finally {
            $zip->close();
        }

        $downloadName = $this->sanitizeExcelDownloadName((string) ($data['file_name'] ?? 'std-kalibrasi.xlsx'));

        return response()->download($tempPath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function exportStdKalibrasiExcelViaPowerShell(array $data): Response
    {
        $templateConfig = $this->resolveStdKalibrasiTemplateConfig($data);
        $templatePath = $templateConfig['path'] ?? null;
        if (!$templatePath || !is_file($templatePath)) {
            abort(404, 'Template std kalibrasi tidak ditemukan.');
        }

        $scriptPath = base_path('scripts/export_std_kalibrasi_pb.ps1');
        if (!is_file($scriptPath)) {
            abort(500, 'Script ekspor std kalibrasi tidak ditemukan.');
        }

        $tempDir = storage_path('app/tmp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $payloadPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('std_kalibrasi_payload_', true) . '.json';
        $outputPath = $tempDir . DIRECTORY_SEPARATOR . uniqid('std_kalibrasi_pb_', true) . '.xlsx';
        $payload = $data;
        $payload['template_profile'] = $templateConfig['profile'] ?? [];
        file_put_contents($payloadPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $command = sprintf(
            'powershell -NoProfile -ExecutionPolicy Bypass -File %s -TemplatePath %s -OutputPath %s -PayloadPath %s -LogoPath %s',
            escapeshellarg($scriptPath),
            escapeshellarg($templatePath),
            escapeshellarg($outputPath),
            escapeshellarg($payloadPath),
            escapeshellarg(public_path('images/Logo Kemnaker.png'))
        );

        [$exitCode, $stdout, $stderr] = $this->runShellCommand($command, base_path());
        @unlink($payloadPath);

        if ($exitCode !== 0 || !is_file($outputPath)) {
            @unlink($outputPath);
            report(new \RuntimeException('Ekspor std kalibrasi via PowerShell gagal: ' . trim($stderr ?: $stdout)));
            abort(500, 'File Excel std kalibrasi tidak dapat dibuat.');
        }

        $downloadName = $this->sanitizeExcelDownloadName((string) ($data['file_name'] ?? 'std-kalibrasi.xlsx'));

        return response()->download($outputPath, $downloadName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function previewHistoryHasil(PrepanalisaItem $item)
    {
        $this->ensureAccess();

        $item->loadMissing(['prepanalisa.permohonan.company', 'serviceParameter.category', 'assignedUser']);
        $analis = $item->assignedUser;
        if (!$analis && !empty($item->updated_by)) {
            $analis = User::query()->find($item->updated_by);
        }
        if (!$analis && !empty($item->created_by)) {
            $analis = User::query()->find($item->created_by);
        }
        $tanggalAnalisis = $item->updated_at ?? $item->prepanalisa?->updated_at;

        return view('admin.prepanalisa_history_hasil_preview', [
            'kode' => $item->prepanalisa?->permohonan?->kode ?? '-',
            'perusahaan' => $item->prepanalisa?->permohonan?->company?->company_name ?? '-',
            'parameter' => $item->serviceParameter?->name ?? '-',
            'kategori' => $item->serviceParameter?->category?->name ?? '-',
            'analis' => $analis?->name ?? '-',
            'analis_signature_url' => !empty($analis?->signature_path) ? route('petugas.signature', $analis) : '',
            'tanggal_analisis' => $tanggalAnalisis,
            'hasilBaca' => $item->data_hasil_baca ?? [],
            'hasilPerhitungan' => $item->data_hasil_perhitungan ?? [],
        ]);
    }

    public function historyHasilData(PrepanalisaItem $item): JsonResponse
    {
        $this->ensureAccess();

        $item->loadMissing(['prepanalisa.permohonan.company', 'serviceParameter.category']);
        $parameterName = (string) ($item->serviceParameter?->name ?? '-');
        $categoryName = (string) ($item->serviceParameter?->category?->name ?? '-');

        return response()->json([
            'kode' => $item->prepanalisa?->permohonan?->kode ?? '-',
            'perusahaan' => $item->prepanalisa?->permohonan?->company?->company_name ?? '-',
            'parameter' => $parameterName,
            'table' => $this->formatHistoryHasilTable($item->data_hasil_perhitungan ?? [], $parameterName, $categoryName),
        ]);
    }

    public function saveDraft(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        $payload = $this->parsePayload($request);
        if ($payload === null) {
            return response()->json(['message' => 'Payload preparasi analisa tidak valid.'], 422);
        }

        $this->persistDraftItems($permohonan, $payload['items']);

        return response()->json(['message' => 'Draft preparasi analisa berhasil disimpan.']);
    }

    public function previewPenyerahan(Request $request)
    {
        $this->ensureAccess();

        $ids = collect(explode(',', (string) $request->query('permohonan_ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter();

        if ($ids->isEmpty()) {
            abort(404);
        }

        $permohonans = Permohonan::with([
            'company',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'koding.items',
            'koding.creator',
            'koding.updater',
            'prepanalisa.items.assignedUser',
        ])->whereIn('id', $ids->values()->all())->get();

        if ($permohonans->isEmpty()) {
            abort(404);
        }

        $orderCodes = $permohonans->pluck('kode')->filter()->values();
        $samplingDate = $permohonans->pluck('jadwal_mulai')->filter()->sort()->first();
        $receiptDate = $permohonans->pluck('jadwal_selesai')->filter()->sort()->last();

        $rowsMap = [];
        foreach ($permohonans as $permohonan) {
            $pengujian = $permohonan->pengujian;
            $koding = $permohonan->koding;
            $prepanalisa = $permohonan->prepanalisa;
            $kodingMap = $koding ? $koding->items->keyBy('pengujian_dokumen_parameter_id') : collect();
            $legacyKodingByDoc = $koding
                ? $koding->items
                    ->filter(function ($item) {
                        return empty($item->pengujian_dokumen_parameter_id) && !empty($item->pengujian_dokumen_id);
                    })
                    ->groupBy('pengujian_dokumen_id')
                    ->map(fn ($items) => $items->sortBy('id')->values())
                : collect();
            $legacyKodingCursorByDoc = [];
            $prepanalisaMap = $prepanalisa ? $prepanalisa->items->keyBy('pengujian_dokumen_parameter_id') : collect();

            if (!$pengujian) {
                continue;
            }

            $pengujian->lokasi->sortBy('urutan')->each(function ($lokasi) use ($kodingMap, $prepanalisaMap, &$rowsMap, $legacyKodingByDoc, &$legacyKodingCursorByDoc) {
                $lokasi->dokumen->sortBy('urutan')->each(function ($dokumen) use ($kodingMap, $prepanalisaMap, &$rowsMap, $legacyKodingByDoc, &$legacyKodingCursorByDoc) {
                    $dokumen->parameters->sortBy('urutan')->each(function ($docParam) use (&$rowsMap, $prepanalisaMap, $kodingMap, $dokumen, $legacyKodingByDoc, &$legacyKodingCursorByDoc) {
                        if ($docParam->is_direct) {
                            return;
                        }
                        $service = $docParam->serviceParameter;
                        $paramName = $service?->name ?? '-';
                        $preItem = null;
                        if ($prepanalisaMap instanceof \Illuminate\Support\Collection) {
                            $preItem = $prepanalisaMap->get($docParam->id);
                        }
                        $kode = '';
                        $hasAdminKode = false;
                        if ($kodingMap instanceof \Illuminate\Support\Collection) {
                            $item = $kodingMap->get($docParam->id);
                            if (!$item && $legacyKodingByDoc instanceof \Illuminate\Support\Collection) {
                                $legacyPool = $legacyKodingByDoc->get($dokumen->id, collect());
                                $cursor = (int) ($legacyKodingCursorByDoc[$dokumen->id] ?? 0);
                                if ($legacyPool instanceof \Illuminate\Support\Collection && $cursor < $legacyPool->count()) {
                                    $item = $legacyPool->get($cursor);
                                    $legacyKodingCursorByDoc[$dokumen->id] = $cursor + 1;
                                }
                            }
                            $kode = $item?->kode ?? '';
                            $hasAdminKode = !empty($kode);
                        }
                        $kodeFinal = $kode ?: ($preItem?->kode_koding ?? '');
                        if (!$kodeFinal) {
                            return;
                        }
                        $hasAnalisPick = !empty($preItem?->assigned_user_id) || !empty($preItem?->service_parameter_id);
                        $key = $paramName . '::' . $kodeFinal;
                        if (!isset($rowsMap[$key])) {
                            $rowsMap[$key] = [
                                'parameter' => $paramName,
                                'jumlah' => 1,
                                'kode' => $kodeFinal,
                                'admin_checked_count' => $hasAdminKode ? 1 : 0,
                                'analis_checked_count' => $hasAnalisPick ? 1 : 0,
                            ];
                        } else {
                            $rowsMap[$key]['jumlah'] += 1;
                            if ($hasAdminKode) {
                                $rowsMap[$key]['admin_checked_count'] += 1;
                            }
                            if ($hasAnalisPick) {
                                $rowsMap[$key]['analis_checked_count'] += 1;
                            }
                        }
                    });
                });
            });
        }

        $rows = collect(array_values($rowsMap))
            ->map(function ($row) {
                $jumlah = (int) ($row['jumlah'] ?? 0);
                $adminCount = (int) ($row['admin_checked_count'] ?? 0);
                $analisCount = (int) ($row['analis_checked_count'] ?? 0);
                $row['admin_checked'] = $jumlah > 0 && $adminCount >= $jumlah;
                // Untuk kolom analis, tampilkan centang jika sudah dipilih analis mana pun.
                $row['analis_checked'] = $analisCount > 0;
                unset($row['admin_checked_count'], $row['analis_checked_count']);
                return $row;
            })
            ->sortBy('parameter')
            ->values();

        $samplingLabel = $samplingDate
            ? Carbon::parse($samplingDate)->locale('id')->translatedFormat('d-F-Y')
            : '-';
        $receiptLabel = $receiptDate
            ? Carbon::parse($receiptDate)->locale('id')->translatedFormat('d-F-Y')
            : '-';
        $handoverLabel = Carbon::now()->locale('id')->translatedFormat('d-F-Y');
        $petugasList = $permohonans->map(function (Permohonan $permohonan) {
            $koding = $permohonan->koding;
            if (!$koding) {
                return null;
            }
            if (!empty($koding->sent_to_prepanalisa_at)) {
                return $koding->updater ?: $koding->creator;
            }
            return $koding->creator ?: $koding->updater;
        })->filter()->unique('id')->values();
        $petugasAdministrasi = $petugasList->pluck('name')->filter()->implode(', ');
        $petugasTtdUser = $petugasList->first(function ($user) {
            return !empty($user?->signature_path);
        });
        $petugasAdministrasiTtdUrl = $this->signatureDataUrl($petugasTtdUser?->signature_path);
        $analisPenerima = $permohonans
            ->flatMap(function (Permohonan $permohonan) {
                return $permohonan->prepanalisa?->items ?? collect();
            })
            ->filter(function ($item) {
                return !empty($item?->assigned_user_id) && !empty($item?->assignedUser?->name);
            })
            ->unique(function ($item) {
                return (int) ($item->assigned_user_id ?? 0);
            })
            ->map(function ($item) {
                $user = $item->assignedUser;
                return [
                    'nama' => $user?->name ?: '-',
                    'ttd_url' => $this->signatureDataUrl($user?->signature_path),
                ];
            })
            ->values();

        return view('admin.prepanalisa_penyerahan_preview', [
            'kodeCustomer' => $orderCodes->implode(', ') ?: '-',
            'tanggalSampling' => $samplingLabel,
            'tanggalPenerimaan' => $receiptLabel,
            'tanggalPenyerahan' => $handoverLabel,
            'petugasAdministrasi' => $petugasAdministrasi !== '' ? $petugasAdministrasi : '-',
            'petugasAdministrasiTtdUrl' => $petugasAdministrasiTtdUrl,
            'analisPenerima' => $analisPenerima,
            'rows' => $rows,
        ]);
    }

    public function updateItemDone(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();
        $this->decodeGroupedItemsInput($request);

        $data = $request->validate([
            'koding_item_id' => ['required_without:items', 'nullable', 'integer'],
            'pengujian_dokumen_parameter_id' => ['required_without:items', 'nullable', 'integer'],
            'service_parameter_id' => ['nullable', 'integer'],
            'items' => ['required_without:koding_item_id', 'nullable', 'array', 'min:1'],
            'items.*.koding_item_id' => ['required', 'integer'],
            'items.*.pengujian_dokumen_parameter_id' => ['required', 'integer'],
            'is_done' => ['required', 'boolean'],
        ]);

        $prepanalisa = Prepanalisa::where('permohonan_id', $permohonan->id)->first();
        if (!$prepanalisa) {
            return response()->json(['message' => 'Prepanalisa tidak ditemukan.'], 404);
        }

        $requestedItems = $this->normalizeGroupedItemRequest($data);
        $items = $this->findPrepanalisaItems($prepanalisa, $requestedItems);
        if ($items->count() !== count($requestedItems)) {
            return response()->json(['message' => 'Ada item prepanalisa yang tidak ditemukan.'], 404);
        }
        $user = auth()->user();
        if ($user?->role !== 'superadmin' && $items->contains(
            fn (PrepanalisaItem $item) => (int) ($item->assigned_user_id ?? 0) !== (int) ($user?->id ?? 0)
        )) {
            return response()->json(['message' => 'Anda hanya dapat menyelesaikan parameter yang Anda pilih sendiri.'], 403);
        }

        DB::transaction(function () use ($items, $data) {
            foreach ($items as $item) {
                $resolvedServiceParamId = (int) ($item->pengujianDokumenParameter?->service_parameter_id ?? 0);
                if (!$resolvedServiceParamId) {
                    abort(422, 'Parameter layanan item prepanalisa tidak valid.');
                }

                $item->update([
                    'service_parameter_id' => $resolvedServiceParamId,
                    'is_done' => (bool) $data['is_done'],
                    'updated_by' => auth()->id(),
                ]);
                if ($item->is_done && $item->verif_status === 'revisi') {
                    $item->verif_status = 'revised';
                    $item->verif_note = null;
                    $item->verif_by = null;
                    $item->verif_at = null;
                    $item->save();
                }
            }
        });

        return response()->json(['message' => 'Status selesai diperbarui.']);
    }

    public function submitToVerifikasi(Request $request)
    {
        $this->ensureAccess();

        $ids = collect(explode(',', (string) $request->input('permohonan_ids')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['message' => 'Permohonan tidak valid.'], 422);
        }

        $permohonans = Permohonan::with('prepanalisa.items')
            ->whereIn('id', $ids->all())
            ->get();

        if ($permohonans->isEmpty()) {
            return response()->json(['message' => 'Permohonan tidak ditemukan.'], 404);
        }

        foreach ($permohonans as $permohonan) {
            $items = $permohonan->prepanalisa?->items ?? collect();
            if ($items->isEmpty() || $items->where('is_done', true)->count() !== $items->count()) {
                return response()->json(['message' => 'Masih ada item yang belum selesai.'], 422);
            }
        }

        DB::transaction(function () use ($permohonans) {
            foreach ($permohonans as $permohonan) {
                $prepanalisa = Prepanalisa::firstOrCreate(
                    ['permohonan_id' => $permohonan->id],
                    ['status' => 'draft', 'created_by' => auth()->id()]
                );

                $prepanalisa->update([
                    'status' => 'sent_to_verifikasi',
                    'updated_by' => auth()->id(),
                ]);

                $this->transitionToVerifikasi($permohonan);
            }
        });

        return response()->json(['message' => 'Preparasi analisa berhasil dikirim ke verifikasi.']);
    }

    public function assign(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();
        $this->decodeGroupedItemsInput($request);

        $data = $request->validate([
            'koding_item_id' => ['required_without:items', 'nullable', 'integer'],
            'pengujian_dokumen_parameter_id' => ['required_without:items', 'nullable', 'integer'],
            'service_parameter_id' => ['nullable', 'integer'],
            'kode_koding' => ['nullable', 'string'],
            'items' => ['required_without:koding_item_id', 'nullable', 'array', 'min:1'],
            'items.*.koding_item_id' => ['required', 'integer'],
            'items.*.pengujian_dokumen_parameter_id' => ['required', 'integer'],
            'items.*.service_parameter_id' => ['nullable', 'integer'],
            'items.*.kode_koding' => ['nullable', 'string'],
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 403);
        }

        return DB::transaction(function () use ($permohonan, $data, $user) {
            $prepanalisa = Prepanalisa::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                ['status' => 'draft', 'created_by' => $user->id]
            );

            $requestedItems = $this->normalizeGroupedItemRequest($data);
            $docParams = $this->loadDocParameterMapForPermohonan($permohonan, $requestedItems);
            if ($docParams->count() !== count($requestedItems)) {
                return response()->json(['message' => 'Ada parameter layanan yang tidak valid.'], 422);
            }

            $serviceParameterIds = $docParams->pluck('service_parameter_id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();
            if ($serviceParameterIds->count() !== 1) {
                return response()->json(['message' => 'Kelompok hitung harus menggunakan parameter yang sama.'], 422);
            }

            $preparedItems = [];
            foreach ($requestedItems as $requestedItem) {
                $docParamId = (int) $requestedItem['pengujian_dokumen_parameter_id'];
                $kodingItemId = (int) $requestedItem['koding_item_id'];
                $docParam = $docParams->get($docParamId);
                $resolvedServiceParamId = (int) ($docParam?->service_parameter_id ?? 0);

                $kodingItemIsValid = KodingItem::query()
                    ->whereKey($kodingItemId)
                    ->where('pengujian_dokumen_parameter_id', $docParamId)
                    ->whereHas('koding', fn ($query) => $query->where('permohonan_id', $permohonan->id))
                    ->exists();
                if (!$kodingItemIsValid) {
                    return response()->json(['message' => 'ID koding tidak sesuai dengan parameter.'], 422);
                }

                $attributes = [
                    'prepanalisa_id' => $prepanalisa->id,
                    'koding_item_id' => $kodingItemId,
                    'pengujian_dokumen_parameter_id' => $docParamId,
                ];

                $item = PrepanalisaItem::query()
                    ->where($attributes)
                    ->lockForUpdate()
                    ->first() ?? new PrepanalisaItem($attributes);
                if ($item->exists && $item->assigned_user_id && $item->assigned_user_id !== $user->id) {
                    $assignedName = $item->assignedUser?->name;
                    return response()->json([
                        'message' => $assignedName
                            ? "Parameter sudah dipilih oleh {$assignedName}."
                            : 'Parameter sudah dipilih analis lain.',
                    ], 409);
                }

                $preparedItems[] = compact(
                    'item',
                    'requestedItem',
                    'resolvedServiceParamId',
                    'kodingItemId',
                    'docParamId'
                );
            }

            $savedItems = [];
            foreach ($preparedItems as $prepared) {
                /** @var PrepanalisaItem $item */
                $item = $prepared['item'];
                $resolvedKodeKoding = $this->resolveKodeKoding(
                    $prepared['requestedItem']['kode_koding'] ?? null,
                    $item->kode_koding,
                    $prepared['kodingItemId']
                );
                $item->fill([
                    'service_parameter_id' => $prepared['resolvedServiceParamId'],
                    'kode_koding' => $resolvedKodeKoding,
                    'assigned_user_id' => $user->id,
                    'updated_by' => $user->id,
                ]);
                if (!$item->exists) {
                    $item->created_by = $user->id;
                }
                $item->save();
                $savedItems[] = [
                    'koding_item_id' => $prepared['kodingItemId'],
                    'pengujian_dokumen_parameter_id' => $prepared['docParamId'],
                ];
            }

            return response()->json([
                'message' => count($savedItems) > 1
                    ? 'Seluruh lokasi untuk parameter ini berhasil dipilih.'
                    : 'Penugasan analis berhasil disimpan.',
                'assigned_user_id' => $user->id,
                'assigned_name' => $user->name,
                'items' => $savedItems,
            ]);
        });
    }

    public function resetAction(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();
        $this->decodeGroupedItemsInput($request);

        $data = $request->validate([
            'koding_item_id' => ['required_without:items', 'nullable', 'integer'],
            'pengujian_dokumen_parameter_id' => ['required_without:items', 'nullable', 'integer'],
            'service_parameter_id' => ['nullable', 'integer'],
            'items' => ['required_without:koding_item_id', 'nullable', 'array', 'min:1'],
            'items.*.koding_item_id' => ['required', 'integer'],
            'items.*.pengujian_dokumen_parameter_id' => ['required', 'integer'],
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 403);
        }

        $prepanalisa = Prepanalisa::where('permohonan_id', $permohonan->id)->first();
        if (!$prepanalisa) {
            return response()->json(['message' => 'Data preparasi analisa tidak ditemukan.'], 404);
        }

        $requestedItems = $this->normalizeGroupedItemRequest($data);
        $items = $this->findPrepanalisaItems($prepanalisa, $requestedItems);
        if ($items->count() !== count($requestedItems)) {
            return response()->json(['message' => 'Ada item preparasi analisa yang tidak ditemukan.'], 404);
        }

        foreach ($items as $item) {
            if ($user->role !== 'superadmin' && (int) ($item->assigned_user_id ?? 0) !== (int) $user->id) {
                return response()->json(['message' => 'Anda hanya dapat reset aksi yang Anda pilih sendiri.'], 403);
            }
        }

        DB::transaction(function () use ($items, $user) {
            foreach ($items as $item) {
                $resolvedServiceParamId = (int) ($item->pengujianDokumenParameter?->service_parameter_id ?? 0);
                if (!$resolvedServiceParamId) {
                    abort(422, 'Parameter layanan item prepanalisa tidak valid.');
                }

                $item->update([
                    'service_parameter_id' => $resolvedServiceParamId,
                    'assigned_user_id' => null,
                    'is_done' => false,
                    'data_skpm' => null,
                    'data_hasil_baca' => null,
                    'data_hasil_perhitungan' => null,
                    'verif_status' => 'pending',
                    'verif_note' => null,
                    'verif_by' => null,
                    'verif_at' => null,
                    'updated_by' => $user->id,
                ]);
            }
        });

        return response()->json([
            'message' => count($requestedItems) > 1
                ? 'Aksi seluruh lokasi parameter berhasil direset. Silakan pilih ulang dari awal.'
                : 'Aksi parameter berhasil direset. Silakan pilih ulang dari awal.',
        ]);
    }

    private function normalizeGroupedItemRequest(array $data): array
    {
        $items = isset($data['items']) && is_array($data['items'])
            ? $data['items']
            : [[
                'koding_item_id' => $data['koding_item_id'] ?? null,
                'pengujian_dokumen_parameter_id' => $data['pengujian_dokumen_parameter_id'] ?? null,
                'service_parameter_id' => $data['service_parameter_id'] ?? null,
                'kode_koding' => $data['kode_koding'] ?? null,
            ]];

        return collect($items)
            ->map(function ($item) {
                return [
                    'koding_item_id' => (int) ($item['koding_item_id'] ?? 0),
                    'pengujian_dokumen_parameter_id' => (int) ($item['pengujian_dokumen_parameter_id'] ?? 0),
                    'service_parameter_id' => isset($item['service_parameter_id'])
                        ? (int) $item['service_parameter_id']
                        : null,
                    'kode_koding' => $item['kode_koding'] ?? null,
                ];
            })
            ->filter(fn ($item) => $item['koding_item_id'] > 0 && $item['pengujian_dokumen_parameter_id'] > 0)
            ->unique(fn ($item) => $item['koding_item_id'] . ':' . $item['pengujian_dokumen_parameter_id'])
            ->values()
            ->all();
    }

    private function decodeGroupedItemsInput(Request $request): void
    {
        $rawItems = $request->input('items');
        if (!is_string($rawItems)) {
            return;
        }

        $decoded = json_decode($rawItems, true);
        if (is_array($decoded)) {
            $request->merge(['items' => $decoded]);
        }
    }

    private function findPrepanalisaItems(Prepanalisa $prepanalisa, array $requestedItems): Collection
    {
        $pairs = collect($requestedItems)
            ->mapWithKeys(fn ($item) => [
                $item['koding_item_id'] . ':' . $item['pengujian_dokumen_parameter_id'] => true,
            ]);

        return PrepanalisaItem::query()
            ->with('pengujianDokumenParameter')
            ->where('prepanalisa_id', $prepanalisa->id)
            ->whereIn('koding_item_id', collect($requestedItems)->pluck('koding_item_id')->all())
            ->whereIn(
                'pengujian_dokumen_parameter_id',
                collect($requestedItems)->pluck('pengujian_dokumen_parameter_id')->all()
            )
            ->get()
            ->filter(fn (PrepanalisaItem $item) => $pairs->has(
                $item->koding_item_id . ':' . $item->pengujian_dokumen_parameter_id
            ))
            ->values();
    }

    private function ensureAccess(): void
    {
        $user = auth()->user();
        $role = $user?->role;
        if (!in_array($role, ['superadmin', 'analis'], true)) {
            abort(403);
        }
    }

    private function parseHistoryFilters(Request $request): array
    {
        $year = (int) $request->query('year', 0);
        $month = (int) $request->query('month', 0);
        $day = (int) $request->query('day', 0);

        return [
            'year' => $year > 0 ? $year : null,
            'month' => ($month >= 1 && $month <= 12) ? $month : null,
            'day' => ($day >= 1 && $day <= 31) ? $day : null,
        ];
    }

    private function buildHistoryRows(array $filters): Collection
    {
        $query = PrepanalisaItem::query()
            ->with([
                'serviceParameter.category',
                'kodingItem',
                'assignedUser',
                'prepanalisa.permohonan.company',
                'prepanalisa.permohonan.koding.items',
            ])
            ->whereHas('prepanalisa', function ($q) use ($filters) {
                $q->where('status', 'sent_to_verifikasi');
                if (!empty($filters['year'])) {
                    $q->whereYear('updated_at', (int) $filters['year']);
                }
                if (!empty($filters['month'])) {
                    $q->whereMonth('updated_at', (int) $filters['month']);
                }
                if (!empty($filters['day'])) {
                    $q->whereDay('updated_at', (int) $filters['day']);
                }
            })
            ->orderByDesc('id');

        return $query->get()->map(function (PrepanalisaItem $item) {
            $prepanalisa = $item->prepanalisa;
            $permohonan = $prepanalisa?->permohonan;
            $serviceParameter = $item->serviceParameter;
            $tanggalSelesai = $prepanalisa?->updated_at;
            $kodeKoding = trim((string) ($item->kode_koding ?? ''));
            if ($kodeKoding === '') {
                $kodeKoding = trim((string) ($item->kodingItem?->kode ?? ''));
            }
            if ($kodeKoding === '') {
                $kodeKoding = trim((string) (
                    $permohonan?->koding?->items
                        ?->firstWhere('pengujian_dokumen_parameter_id', $item->pengujian_dokumen_parameter_id)
                        ?->kode ?? ''
                ));
            }

            return [
                'item_id' => $item->id,
                'tanggal_selesai_label' => $tanggalSelesai ? Carbon::parse($tanggalSelesai)->format('d-m-Y H:i') : '-',
                'tanggal_selesai_raw' => $tanggalSelesai,
                'kode_order' => $permohonan?->kode ?? '-',
                'perusahaan' => $permohonan?->company?->company_name ?? '-',
                'parameter' => $serviceParameter?->name ?? '-',
                'kategori' => $serviceParameter?->category?->name ?? '-',
                'kode_koding' => $kodeKoding !== '' ? $kodeKoding : '-',
                'analis' => $item->assignedUser?->name ?? '-',
                'status_item' => $item->is_done ? 'Selesai' : 'Belum',
            ];
        })->values();
    }

    private function transitionToVerifikasi(Permohonan $permohonan): void
    {
        $steps = WorkflowStep::whereIn('kode', ['preparasi_analisa', 'verifikasi'])->get()->keyBy('kode');
        $prepanalisaStep = $steps->get('preparasi_analisa');
        $verifikasiStep = $steps->get('verifikasi');

        if (!$prepanalisaStep || !$verifikasiStep) {
            return;
        }

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $prepanalisaStep->id)
            ->update([
                'status' => 'approved',
                'note' => 'Preparasi analisa diteruskan ke verifikasi',
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $verifikasiStep->id],
            ['status' => 'pending', 'started_at' => now()]
        );

        $permohonan->update([
            'status_global' => 'verifikasi',
            'status_lab' => 'verifikasi',
        ]);
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

    private function persistDraftItems(Permohonan $permohonan, array $items): void
    {
        DB::transaction(function () use ($permohonan, $items) {
            $prepanalisa = Prepanalisa::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                ['status' => 'draft', 'created_by' => auth()->id()]
            );

            $prepanalisa->update([
                'status' => 'draft',
                'updated_by' => auth()->id(),
            ]);

            $docParams = $this->loadDocParameterMapForPermohonan($permohonan, $items);

            foreach ($items as $item) {
                $kodingItemId = (int) ($item['koding_item_id'] ?? 0);
                $docParamId = (int) ($item['pengujian_dokumen_parameter_id'] ?? 0);
                $docParam = $docParams->get($docParamId);
                $serviceParamId = (int) ($docParam?->service_parameter_id ?? 0);
                if (!$kodingItemId || !$docParamId || !$serviceParamId) {
                    continue;
                }

                $normalized = PrepanalisaRumusRegistry::apply($serviceParamId, $item);

                $attributes = [
                    'prepanalisa_id' => $prepanalisa->id,
                    'koding_item_id' => $kodingItemId,
                    'pengujian_dokumen_parameter_id' => $docParamId,
                ];
                $updateData = [
                    'service_parameter_id' => $serviceParamId,
                    'kode_koding' => $item['kode_koding'] ?? null,
                    'assigned_user_id' => auth()->id(),
                    'updated_by' => auth()->id(),
                ];
                if (array_key_exists('skpm', $normalized)) {
                    $updateData['data_skpm'] = $this->normalizeAnalysisDataset($normalized['skpm']);
                }
                if (array_key_exists('hasil_baca', $normalized)) {
                    $updateData['data_hasil_baca'] = $this->normalizeAnalysisDataset($normalized['hasil_baca']);
                }
                if (array_key_exists('hasil_perhitungan', $normalized)) {
                    $updateData['data_hasil_perhitungan'] = $this->normalizeAnalysisDataset($normalized['hasil_perhitungan']);
                }

                $prepanalisaItem = PrepanalisaItem::firstOrNew($attributes);
                $resolvedKodeKoding = $this->resolveKodeKoding(
                    $item['kode_koding'] ?? null,
                    $prepanalisaItem->kode_koding,
                    $kodingItemId
                );
                $prepanalisaItem->fill($updateData);
                $prepanalisaItem->kode_koding = $resolvedKodeKoding;
                if (!$prepanalisaItem->exists) {
                    $prepanalisaItem->created_by = auth()->id();
                }
                $prepanalisaItem->save();
            }
        });
    }

    private function loadDocParameterMapForPermohonan(Permohonan $permohonan, array $items): Collection
    {
        $docParamIds = collect($items)
            ->pluck('pengujian_dokumen_parameter_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($docParamIds->isEmpty()) {
            return collect();
        }

        return PengujianDokumenParameter::query()
            ->with(['dokumen.lokasi.pengujian', 'serviceParameter.category'])
            ->whereIn('id', $docParamIds->all())
            ->get()
            ->filter(function (PengujianDokumenParameter $docParam) use ($permohonan) {
                return (int) ($docParam->dokumen?->lokasi?->pengujian?->permohonan_id ?? 0) === (int) $permohonan->id;
            })
            ->keyBy('id');
    }

    private function findDocParameterForPermohonan(Permohonan $permohonan, int $docParamId): ?PengujianDokumenParameter
    {
        if ($docParamId <= 0) {
            return null;
        }

        return PengujianDokumenParameter::query()
            ->with(['dokumen.lokasi.pengujian', 'serviceParameter.category'])
            ->whereKey($docParamId)
            ->get()
            ->first(function (PengujianDokumenParameter $docParam) use ($permohonan) {
                return (int) ($docParam->dokumen?->lokasi?->pengujian?->permohonan_id ?? 0) === (int) $permohonan->id;
            });
    }

    private function resolveKodeKoding(mixed $incomingCode, mixed $existingCode, int $kodingItemId): ?string
    {
        $incoming = trim((string) ($incomingCode ?? ''));
        if ($incoming !== '' && $incoming !== '-') {
            return $incoming;
        }

        $existing = trim((string) ($existingCode ?? ''));
        if ($existing !== '' && $existing !== '-') {
            return $existing;
        }

        if ($kodingItemId > 0) {
            $fromKodingItem = trim((string) (KodingItem::query()->find($kodingItemId)?->kode ?? ''));
            if ($fromKodingItem !== '' && $fromKodingItem !== '-') {
                return $fromKodingItem;
            }
        }

        return null;
    }

    private function normalizeAnalysisDataset(mixed $dataset): array
    {
        if (!is_array($dataset)) {
            return [];
        }

        if (array_key_exists('rows', $dataset) && is_array($dataset['rows'])) {
            $columns = [];
            if (isset($dataset['columns']) && is_array($dataset['columns'])) {
                foreach ($dataset['columns'] as $column) {
                    $text = trim((string) $column);
                    if ($text !== '') {
                        $columns[] = $text;
                    }
                }
            }

            return [
                'columns' => array_values($columns),
                'rows' => array_values($dataset['rows']),
            ];
        }

        // Backward compatibility: old data format is rows array only.
        return array_values($dataset);
    }

    private function formatHistoryHasilTable(mixed $dataset, string $parameterName = '', string $categoryName = ''): array
    {
        $rows = [];
        $headers = [];
        if (is_array($dataset)) {
            if (array_key_exists('rows', $dataset) && is_array($dataset['rows'])) {
                $rows = array_values($dataset['rows']);
                if (isset($dataset['columns']) && is_array($dataset['columns'])) {
                    $headers = collect($dataset['columns'])
                        ->map(fn ($col) => trim((string) $col))
                        ->filter(fn ($col) => $col !== '')
                        ->values()
                        ->all();
                }
            } else {
                $rows = array_values($dataset);
            }
        }

        if (empty($headers)) {
            $first = collect($rows)->first();
            if (is_array($first) && is_array($first['cols'] ?? null) && count($first['cols']) > 0) {
                $headers = $this->inferHistoryHeadersFromRows($rows, $parameterName, $categoryName);
                if (empty($headers)) {
                    for ($i = 1; $i <= count($first['cols']); $i++) {
                        $headers[] = 'Kolom ' . $i;
                    }
                }
            } elseif (is_array($first)) {
                $headers = collect(array_keys($first))
                    ->filter(fn ($key) => !is_int($key))
                    ->reject(fn ($key) => in_array((string) $key, ['cols', 'row_type'], true))
                    ->values()
                    ->all();
                if (empty($headers)) {
                    $maxCols = collect($rows)->map(function ($row) {
                        if (!is_array($row)) {
                            return 1;
                        }
                        if (is_array($row['cols'] ?? null)) {
                            return count($row['cols']);
                        }
                        return count($row);
                    })->max() ?? 1;
                    $headers = [];
                    for ($i = 1; $i <= $maxCols; $i++) {
                        $headers[] = 'Kolom ' . $i;
                    }
                }
            } else {
                $headers = ['Data'];
            }
        }
        $headers = collect($headers)
            ->map(fn ($header) => $this->normalizeHistoryHeaderLabel((string) $header))
            ->values()
            ->all();
        if ($this->isGenericColumnHeaders($headers)) {
            $inferred = $this->inferHistoryHeadersFromRows($rows, $parameterName, $categoryName);
            if (!empty($inferred)) {
                if (count($inferred) === count($headers)) {
                    $headers = $inferred;
                } elseif (count($inferred) === count($headers) + 1 && $this->isNoHeader($inferred[0] ?? '')) {
                    $headers = array_values(array_slice($inferred, 1));
                } elseif (count($inferred) + 1 === count($headers) && $this->isNoHeader($headers[0] ?? '')) {
                    $headers = array_merge([$headers[0]], $inferred);
                }
            }
        }

        $normalizedRows = collect($rows)->values()->map(function ($row, $rowIndex) use ($headers) {
            if (!is_array($row)) {
                return [(string) $row];
            }

            $cols = is_array($row['cols'] ?? null)
                ? array_values($row['cols'])
                : [];
            if (!empty($cols)) {
                $hasNoHeader = collect($headers)->contains(fn ($h) => $this->isNoHeader((string) $h));
                $shiftNoColumn = $hasNoHeader && count($cols) === max(0, count($headers) - 1);
                $cursor = 0;
                $mapped = [];
                foreach ($headers as $header) {
                    if ($shiftNoColumn && $this->isNoHeader((string) $header)) {
                        $mapped[] = (string) ($row['no'] ?? ($rowIndex + 1));
                        continue;
                    }
                    $cell = $cols[$cursor] ?? null;
                    if (($cell === null || $cell === '') && array_key_exists($header, $row)) {
                        $cell = $row[$header];
                    }
                    $mapped[] = $this->stringifyHistoryCell($cell);
                    $cursor++;
                }
                return $mapped;
            }

            return collect($headers)->map(function ($header, $headerIndex) use ($row, $rowIndex) {
                if ($this->isNoHeader((string) $header) && !array_key_exists($header, $row) && !array_key_exists($headerIndex, $row)) {
                    return (string) ($row['no'] ?? ($rowIndex + 1));
                }
                if (array_key_exists($header, $row)) {
                    return $this->stringifyHistoryCell($row[$header]);
                }
                if (array_key_exists($headerIndex, $row)) {
                    return $this->stringifyHistoryCell($row[$headerIndex]);
                }
                return '';
            })->values()->all();
        })->all();

        return [
            'headers' => $headers,
            'rows' => $normalizedRows,
        ];
    }

    private function signatureDataUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $disk = null;
        if (Storage::disk('local')->exists($path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($path)) {
            $disk = 'public';
        }
        if (!$disk) {
            return null;
        }

        $data = Storage::disk($disk)->get($path);
        if ($data === false || $data === null) {
            return null;
        }

        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    private function resolveStdKalibrasiPbTemplatePath(): ?string
    {
        foreach ([
            storage_path('app/std_pb_template.xlsx'),
            storage_path('app/reference_pb.xlsx'),
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function resolveStdKalibrasiTemplateConfig(array $data): array
    {
        $curveLabel = strtoupper(trim((string) ($data['curve_label'] ?? '')));
        $parameterName = strtoupper(trim((string) ($data['parameter_name'] ?? '')));

        if ($curveLabel === 'CR' || preg_match('/\bCR\b/', $parameterName)) {
            $crReference = app(CrExcelReferenceService::class)->getReference();
            $crPath = $this->resolveExistingTemplatePath([
                $crReference['sourcePath'] ?? null,
                storage_path('app/reference_cr.xlsx'),
                storage_path('app/private/reference_cr.xlsx'),
            ]);

            if ($crPath) {
                return [
                    'path' => $crPath,
                    'profile' => [
                        'row_start' => 16,
                        'row_count' => 5,
                        'curve_y_cell' => 'G25',
                        'curve_x_cell' => 'G26',
                        'cells' => [
                            'parameter_name' => 'D7',
                            'sample_type' => 'D8',
                            'condition' => 'D9',
                            'analysis_date' => 'D10',
                            'analis_name' => 'D11',
                        ],
                    ],
                ];
            }
        }

        return [
            'path' => $this->resolveStdKalibrasiPbTemplatePath(),
            'profile' => [
                'row_start' => 17,
                'row_count' => 6,
                'curve_y_cell' => 'G27',
                'curve_x_cell' => 'G28',
                'cells' => [
                    'parameter_name' => 'D7',
                    'sample_type' => 'D8',
                    'condition' => 'D9',
                    'receipt_date' => 'D10',
                    'analysis_date' => 'D11',
                    'analis_name' => 'D12',
                ],
            ],
        ];
    }

    private function resolveExistingTemplatePath(array $candidates): ?string
    {
        foreach ($candidates as $path) {
            $value = is_string($path) ? trim($path) : '';
            if ($value !== '' && is_file($value)) {
                return $value;
            }
        }

        return null;
    }

    private function replaceStdKalibrasiLogoInArchive(ZipArchive $zip): void
    {
        $logoPath = public_path('images/Logo Kemnaker.png');
        if (!is_file($logoPath)) {
            return;
        }

        $logoBinary = @file_get_contents($logoPath);
        if ($logoBinary === false || $logoBinary === '') {
            return;
        }

        $zip->addFromString('xl/media/image1.png', $logoBinary);
        $drawingXml = (string) $zip->getFromName('xl/drawings/drawing1.xml');
        if ($drawingXml !== '') {
            $drawingDom = $this->loadXmlDocument($drawingXml);
            $drawingXPath = new DOMXPath($drawingDom);
            $drawingXPath->registerNamespace('xdr', 'http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing');
            $drawingXPath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
            foreach ($drawingXPath->query('//xdr:pic/xdr:blipFill/a:blip/a:lum | //xdr:pic/xdr:blipFill/a:blip/a:grayscl | //xdr:pic/xdr:blipFill/a:blip/a:biLevel') as $node) {
                $node->parentNode?->removeChild($node);
            }
            $zip->addFromString('xl/drawings/drawing1.xml', $drawingDom->saveXML());
        }
    }

    private function sanitizeExcelDownloadName(string $fileName): string
    {
        $value = trim($fileName);
        if ($value === '') {
            $value = 'std-kalibrasi-pb.xlsx';
        }
        $value = preg_replace('/[\\\\\\/:*?"<>|]+/', '-', $value) ?: 'std-kalibrasi-pb.xlsx';
        $value = preg_replace('/\s+/', ' ', $value) ?: 'std-kalibrasi-pb.xlsx';
        if (!str_ends_with(strtolower($value), '.xlsx')) {
            $value .= '.xlsx';
        }

        return $value;
    }

    private function runShellCommand(string $command, string $workingDirectory): array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, $workingDirectory);
        if (!is_resource($process)) {
            return [1, '', 'Tidak dapat menjalankan proses shell.'];
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [$exitCode, (string) $stdout, (string) $stderr];
    }

    private function loadXmlDocument(string $xml): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        return $dom;
    }

    private function clearWorksheetCell(DOMXPath $xpath, string $ref): void
    {
        $cell = $xpath->query("//main:c[@r='{$ref}']")->item(0);
        if (!$cell) {
            return;
        }

        while ($cell->firstChild) {
            $cell->removeChild($cell->firstChild);
        }
        $cell->removeAttribute('t');
    }

    private function setWorksheetCellString(DOMDocument $dom, DOMXPath $xpath, string $ref, string $value): void
    {
        $cell = $xpath->query("//main:c[@r='{$ref}']")->item(0);
        if (!$cell) {
            return;
        }

        while ($cell->firstChild) {
            $cell->removeChild($cell->firstChild);
        }

        $cell->setAttribute('t', 'inlineStr');
        $inlineString = $dom->createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'is');
        $textNode = $dom->createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main', 't');
        if ($value !== trim($value)) {
            $textNode->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }
        $textNode->appendChild($dom->createTextNode($value));
        $inlineString->appendChild($textNode);
        $cell->appendChild($inlineString);
    }

    private function setWorksheetCellNumber(DOMDocument $dom, DOMXPath $xpath, string $ref, ?float $value, ?string $formula = null): void
    {
        $cell = $xpath->query("//main:c[@r='{$ref}']")->item(0);
        if (!$cell) {
            return;
        }

        while ($cell->firstChild) {
            $cell->removeChild($cell->firstChild);
        }
        $cell->removeAttribute('t');

        if ($formula !== null && trim($formula) !== '') {
            $formulaNode = $dom->createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'f');
            $formulaNode->appendChild($dom->createTextNode($formula));
            $cell->appendChild($formulaNode);
        }

        if ($value === null) {
            return;
        }

        $valueNode = $dom->createElementNS('http://schemas.openxmlformats.org/spreadsheetml/2006/main', 'v');
        $valueNode->appendChild($dom->createTextNode($this->formatExcelNumericValue($value)));
        $cell->appendChild($valueNode);
    }

    private function updateChartTitle(DOMXPath $xpath, string $title): void
    {
        $node = $xpath->query('//c:chart/c:title//a:t')->item(0);
        if ($node) {
            $node->nodeValue = $title;
        }
    }

    private function updateChartCache(DOMDocument $dom, DOMXPath $xpath, string $cacheQuery, array $values, string $formatCode): void
    {
        $cache = $xpath->query($cacheQuery)->item(0);
        if (!$cache) {
            return;
        }

        foreach (iterator_to_array($xpath->query('c:ptCount|c:pt', $cache)) as $node) {
            $cache->removeChild($node);
        }

        $formatNode = $xpath->query('c:formatCode', $cache)->item(0);
        if (!$formatNode) {
            $formatNode = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/chart', 'c:formatCode', $formatCode);
            $cache->appendChild($formatNode);
        } else {
            $formatNode->nodeValue = $formatCode;
        }

        $countNode = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/chart', 'c:ptCount');
        $countNode->setAttribute('val', (string) count($values));
        $cache->appendChild($countNode);

        foreach (array_values($values) as $index => $value) {
            $pointNode = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/chart', 'c:pt');
            $pointNode->setAttribute('idx', (string) $index);
            $valueNode = $dom->createElementNS('http://schemas.openxmlformats.org/drawingml/2006/chart', 'c:v', $this->formatExcelNumericValue((float) $value));
            $pointNode->appendChild($valueNode);
            $cache->appendChild($pointNode);
        }
    }

    private function removeWorkbookExternalReferences(DOMXPath $xpath): void
    {
        foreach ($xpath->query('/main:workbook/main:externalReferences') as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    private function enableWorkbookFullCalc(DOMXPath $xpath): void
    {
        $calcPr = $xpath->query('/main:workbook/main:calcPr')->item(0);
        if (!$calcPr) {
            return;
        }

        $calcPr->setAttribute('calcMode', 'auto');
        $calcPr->setAttribute('fullCalcOnLoad', '1');
        $calcPr->setAttribute('forceFullCalc', '1');
    }

    private function normalizeExcelNumber(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = str_replace([' ', "\u{A0}"], '', $raw);
        $hasComma = str_contains($normalized, ',');
        $hasDot = str_contains($normalized, '.');
        if ($hasComma && $hasDot) {
            if (strrpos($normalized, ',') > strrpos($normalized, '.')) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($hasComma) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }

    private function convertTimeToExcelSerial(mixed $value): ?float
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $raw, $matches) === 1) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = isset($matches[3]) ? (int) $matches[3] : 0;
            return (($hours * 3600) + ($minutes * 60) + $seconds) / 86400;
        }

        return $this->normalizeExcelNumber($value);
    }

    private function formatExcelNumericValue(float $value): string
    {
        $formatted = number_format($value, 12, '.', '');
        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function stringifyHistoryCell(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        return (string) ($value ?? '');
    }

    private function isNoHeader(string $header): bool
    {
        $normalized = strtolower(trim($header));
        return $normalized === 'no' || $normalized === 'no.' || str_starts_with($normalized, 'no ');
    }

    private function normalizeHistoryHeaderLabel(string $header): string
    {
        $raw = trim($header);
        if ($raw === '') {
            return '';
        }

        $lookup = [
            'fr' => 'FR (lpm)',
            'pm' => 'P mmHg',
            'sk' => 'Sk C',
            'berat' => 'Berat',
            'kadar' => 'Kadar',
            'waktu' => 'Waktu (mnt)',
            'lokasi' => 'Lokasi',
            'vol' => 'Volume',
            'kons' => 'Konsentrasi',
            'no sample' => 'No. Sample',
            'no. sample' => 'No. Sample',
            'no_sampel' => 'No. Sample',
            'no sampel' => 'No. Sample',
            'jam timbang' => 'Jam Timbang',
            'selisih' => 'Selisih',
            'ket' => 'Keterangan',
        ];
        $key = strtolower(preg_replace('/\s+/', ' ', str_replace('_', ' ', $raw)));
        if (isset($lookup[$key])) {
            return $lookup[$key];
        }

        return preg_replace_callback('/\b([a-z])/', fn ($m) => strtoupper($m[1]), strtolower($raw));
    }

    private function inferHistoryHeadersFromRows(array $rows, string $parameterName = '', string $categoryName = ''): array
    {
        $maxCols = collect($rows)->map(function ($row) {
            if (!is_array($row)) {
                return 1;
            }
            if (is_array($row['cols'] ?? null)) {
                return count($row['cols']);
            }
            return count($row);
        })->max() ?? 0;
        if ($maxCols <= 0) {
            return [];
        }

        $rowTypes = collect($rows)
            ->map(fn ($row) => strtolower(trim((string) (is_array($row) ? ($row['row_type'] ?? '') : ''))))
            ->filter()
            ->values();
        $param = strtolower(trim($parameterName));
        $category = strtolower(preg_replace('/\s+/', '', trim($categoryName)));
        $isNh3Emisi = (str_contains($param, 'nh3') || str_contains($param, 'amonia') || str_contains($param, 'ammonia'))
            && in_array($category, ['e', 'emisi'], true);

        $isSo2 = str_contains($param, 'so2')
            || preg_match('/\bhg\b/i', $param) === 1
            || str_contains($param, 'merkuri')
            || str_contains($param, 'mercury')
            || $isNh3Emisi
            || str_contains($param, 'hcl')
            || str_contains($param, 'hidrogen klorida')
            || str_contains($param, 'hydrogen chloride')
            || $rowTypes->contains(fn ($type) => str_contains($type, 'so2'));
        if ($isSo2) {
            $gasLabel = str_contains($param, 'hf') || str_contains($param, 'hidrogen fluorida') || str_contains($param, 'hydrogen fluoride')
                ? 'HF'
                : ($isNh3Emisi
                    ? 'NH3'
                : ((preg_match('/\bhg\b/i', $param) === 1 || str_contains($param, 'merkuri') || str_contains($param, 'mercury'))
                    ? 'Hg'
                : ((str_contains($param, 'hcl') || str_contains($param, 'hidrogen klorida') || str_contains($param, 'hydrogen chloride'))
                    ? 'HCL'
                    : 'SO2')));
            if ($gasLabel === 'HF' && $maxCols >= 9) {
                return ['Lokasi', "Kons {$gasLabel} (mg)", 'Volume Spl (ml)', 'FR (lpm)', 'Waktu (mnt)', 'Titr HF (ml)', 'TM (C)', 'P mmHg', "Kadar {$gasLabel} (mg/m3)"];
            }
            if ($maxCols >= 11) {
                return ['No', 'Lokasi', 'Konsentrasi', 'Volume Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', "Kadar {$gasLabel} (ppm)", "Kadar {$gasLabel} (ug/m3)", "Kadar {$gasLabel} (mg/m3)"];
            }
            if ($maxCols >= 10) {
                return ['No', 'Lokasi', 'Konsentrasi', 'Volume Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', "Kadar {$gasLabel} (ppm)", "Kadar {$gasLabel} (ug/m3)"];
            }
            return ['Lokasi', "Kons {$gasLabel} (mg)", 'Volume Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', "Kadar {$gasLabel} (mg/m3)"];
        }

        $isNo2 = str_contains($param, 'no2') || $rowTypes->contains(fn ($type) => str_contains($type, 'no2'));
        if ($isNo2) {
            return $maxCols >= 10
                ? ['No', 'Lokasi', 'Konsentrasi', 'Volume Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', 'Kadar NO2 (ppm)', 'Kadar NO2 (ug/m3)']
                : ['Lokasi', 'Konsentrasi', 'Volume Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', 'Kadar NO2 (ppm)', 'Kadar NO2 (ug/m3)'];
        }

        $isHcLike = collect(['benzene', 'toluene', 'xylene'])->contains(fn ($k) => str_contains($param, $k))
            || $rowTypes->contains(fn ($type) => str_contains($type, 'hc'));
        if ($isHcLike) {
            $gasLabel = 'Benzene';
            if (str_contains($param, 'toluene')) {
                $gasLabel = 'Toluene';
            } elseif (str_contains($param, 'xylene')) {
                $gasLabel = 'Xylene';
            }
            if ($maxCols >= 11) {
                return ['No', 'Lokasi', 'Konsentrasi', 'Volume Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', "Kadar {$gasLabel} (ppm)", "Kadar {$gasLabel} (ug/m3)", "Kadar {$gasLabel} (mg/m3)"];
            }
            return $maxCols >= 10
                ? ['No', 'Lokasi', 'Konsentrasi', 'Volume Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', "Kadar {$gasLabel} (ppm)", "Kadar {$gasLabel} (ug/m3)"]
                : ['Lokasi', 'Konsentrasi', 'Volume Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', "Kadar {$gasLabel} (ppm)", "Kadar {$gasLabel} (ug/m3)"];
        }

        return [];
    }

    private function isGenericColumnHeaders(array $headers): bool
    {
        if (empty($headers)) {
            return false;
        }
        return collect($headers)->every(function ($header) {
            return (bool) preg_match('/^kolom\s+\d+$/i', trim((string) $header));
        });
    }
}
