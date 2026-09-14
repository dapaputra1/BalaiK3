<?php

namespace App\Services\FormulaReference;

use App\Models\ParameterLod;
use App\Support\ServiceSystemCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class SbExcelReferenceService
{
    private const SERVICE_PARAMETER_SYSTEM_CODE = 'LK__KDLSB';
    private const STORAGE_PATH = 'reference_sb.xlsx';
    private const FALLBACK_EXTERNAL_PATHS = [
        'C:\Users\user\OneDrive\Sb.xlsx',
        'C:\Users\user\OneDrive\Documents\Sb.xlsx',
        'C:\Users\user\OneDrive\Antimony (Sb).xlsx',
    ];

    private const DEFAULT_REFERENCE = [
        'formulaConc' => 'cons = 0 + 285.714286 * abs',
        'concIntercept' => 0.0,
        'concSlope' => 285.714285714286,
        'curveY' => 0.0035,
        'curveX' => 285.714285714286,
        'formulaKadar' => 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
        'sheetName' => '',
        'sourcePath' => '',
        'sourceMode' => 'fallback',
        'absorbanceSourceMode' => 'fallback',
        'lodSourceMode' => 'fallback',
        'constants' => [
            'temperatureOffset' => 273.0,
            'pressureFactor' => 760.0,
            'denominatorTemperature' => 298.0,
        ],
        'standardSeries' => [1.0, 3.0, 5.0, 10.0, 15.0],
        'lod' => [
            'kons' => '',
            'vol' => '',
            'fr' => '',
            'waktu' => '',
            'sk' => '',
            'p' => '',
            'ugm3' => '',
            'mgm3' => '',
        ],
    ];

    public function __construct(
        private readonly AbsorbanceReferenceService $absorbanceReferenceService
    ) {
    }

    public function getReference(): array
    {
        $workbookPath = $this->resolveWorkbookPath();
        $reference = $workbookPath === null
            ? $this->fallbackReference(null)
            : Cache::rememberForever(
                'prepanalisa:formula-reference:sb:' . md5($workbookPath . '|' . (string) @filesize($workbookPath) . '|' . (string) @filemtime($workbookPath)),
                function () use ($workbookPath) {
                    try {
                        return $this->normalizeReference($this->extractReference($workbookPath), $workbookPath);
                    } catch (Throwable $e) {
                        report($e);

                        return $this->fallbackReference($workbookPath);
                    }
                }
            );

        return $this->applyDatabaseOverrides($reference);
    }

    public function calculateConcentration(float $abs): float
    {
        $reference = $this->getReference();
        $intercept = $this->normalizeFloat($reference['concIntercept'] ?? null, self::DEFAULT_REFERENCE['concIntercept']);
        $slope = $this->normalizeFloat($reference['concSlope'] ?? null, self::DEFAULT_REFERENCE['concSlope']);

        return $intercept + ($slope * $abs);
    }

    public function calculate(float $kons, float $vol, float $fr, float $waktu, float $sk, float $p): array
    {
        $reference = $this->getReference();
        $constants = is_array($reference['constants'] ?? null) ? $reference['constants'] : [];
        $temperatureOffset = $this->normalizeFloat($constants['temperatureOffset'] ?? null, self::DEFAULT_REFERENCE['constants']['temperatureOffset']);
        $pressureFactor = $this->normalizeFloat($constants['pressureFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['pressureFactor']);
        $denominatorTemperature = $this->normalizeFloat($constants['denominatorTemperature'] ?? null, self::DEFAULT_REFERENCE['constants']['denominatorTemperature']);

        $denominator = $fr * $waktu * $denominatorTemperature * $p;
        if ($denominator == 0.0) {
            return [
                'kadar_mgm3' => null,
                'kadar_ugm3' => null,
            ];
        }

        $mgm3 = ($kons * $vol * ($temperatureOffset + $sk) * $pressureFactor) / $denominator;

        return [
            'kadar_mgm3' => $mgm3,
            'kadar_ugm3' => $mgm3 * 1000,
        ];
    }

    private function applyDatabaseOverrides(array $reference): array
    {
        $absorbance = $this->absorbanceReferenceService->getReference('SB');
        $curveY = $this->normalizeFloat($absorbance['curve_y'] ?? ($absorbance['intercept'] ?? null), $this->normalizeFloat($reference['curveY'] ?? null, self::DEFAULT_REFERENCE['curveY']));
        $curveX = $this->normalizeFloat($absorbance['curve_x'] ?? ($absorbance['slope'] ?? null), $this->normalizeFloat($reference['curveX'] ?? null, self::DEFAULT_REFERENCE['curveX']));
        if ($curveY == 0.0 && $curveX != 0.0) {
            $curveY = 1 / $curveX;
        }
        if ($curveX == 0.0 && $curveY != 0.0) {
            $curveX = 1 / $curveY;
        }

        $reference['curveY'] = $curveY;
        $reference['curveX'] = $curveX;
        $reference['concIntercept'] = 0.0;
        $reference['concSlope'] = $curveX;
        $reference['formulaConc'] = sprintf(
            'cons = %s + %s * abs',
            rtrim(rtrim(number_format(0, 6, '.', ''), '0'), '.'),
            rtrim(rtrim(number_format($curveX, 6, '.', ''), '0'), '.')
        );
        $reference['absorbanceSourceMode'] = (string) ($absorbance['source_mode'] ?? 'fallback');

        $lod = ParameterLod::query()
            ->whereHas('serviceParameter', function ($query) {
                $query->where('system_code', self::SERVICE_PARAMETER_SYSTEM_CODE);
            })
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($lod) {
            $reference['lod'] = $this->buildLodRowFromModel($lod, (array) ($reference['lod'] ?? []));
            $reference['lodSourceMode'] = 'database';
        }

        return $reference;
    }

    private function extractReference(string $workbookPath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive tidak tersedia untuk membaca acuan Excel Sb.');
        }

        $zip = new ZipArchive();
        if ($zip->open($workbookPath) !== true) {
            throw new RuntimeException('File acuan Excel Sb tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            [$sheetName, $sheetPath] = $this->resolveWorksheet($zip);
            $cells = $this->readWorksheetCells($zip, $sheetPath, $sharedStrings);

            $curveY = $this->toNullableFloat($cells['G25']['value'] ?? null);
            $curveX = $this->toNullableFloat($cells['G26']['value'] ?? null);
            if ($curveY === null && $curveX !== null && $curveX != 0.0) {
                $curveY = 1 / $curveX;
            }
            if ($curveX === null && $curveY !== null && $curveY != 0.0) {
                $curveX = 1 / $curveY;
            }

            return [
                'sheetName' => $sheetName,
                'formulaConc' => sprintf('cons = 0 + %s * abs', $curveX ?? self::DEFAULT_REFERENCE['curveX']),
                'concIntercept' => 0.0,
                'concSlope' => $curveX,
                'curveY' => $curveY,
                'curveX' => $curveX,
                'formulaKadar' => self::DEFAULT_REFERENCE['formulaKadar'],
                'standardSeries' => $this->extractStandardSeries($cells),
            ];
        } finally {
            $zip->close();
        }
    }

    private function extractStandardSeries(array $cells): array
    {
        $values = [];
        for ($row = 16; $row <= 20; $row += 1) {
            $value = $this->toNullableFloat($cells['F' . $row]['value'] ?? null);
            if ($value !== null) {
                $values[] = $value;
            }
        }

        return count($values) > 0 ? $values : self::DEFAULT_REFERENCE['standardSeries'];
    }

    private function buildLodRowFromModel(ParameterLod $lod, array $fallback): array
    {
        $kons = $this->normalizeFloat($lod->kons, (float) ($fallback['kons'] ?? 0.0));
        $vol = $this->normalizeFloat($lod->vol, (float) ($fallback['vol'] ?? 0.0));
        $fr = $this->normalizeFloat($lod->fr, (float) ($fallback['fr'] ?? 0.0));
        $waktu = $this->normalizeFloat($lod->waktu, (float) ($fallback['waktu'] ?? 0.0));
        $sk = $this->normalizeFloat($lod->sk, (float) ($fallback['sk'] ?? 0.0));
        $p = $this->normalizeFloat($lod->pm, (float) ($fallback['p'] ?? 0.0));
        $calculated = $this->calculate($kons, $vol, $fr, $waktu, $sk, $p);
        $mgm3 = $calculated['kadar_mgm3'] ?? null;
        $ugm3 = $calculated['kadar_ugm3'] ?? null;

        return [
            'kons' => $kons > 0 ? number_format($kons, 4, '.', '') : (string) ($fallback['kons'] ?? ''),
            'vol' => $vol > 0 ? number_format($vol, 1, '.', '') : (string) ($fallback['vol'] ?? ''),
            'fr' => $fr > 0 ? number_format($fr, 3, '.', '') : (string) ($fallback['fr'] ?? ''),
            'waktu' => $waktu > 0 ? number_format($waktu, 0, '.', '') : (string) ($fallback['waktu'] ?? ''),
            'sk' => $sk > 0 ? number_format($sk, 1, '.', '') : (string) ($fallback['sk'] ?? ''),
            'p' => $p > 0 ? number_format($p, 0, '.', '') : (string) ($fallback['p'] ?? ''),
            'ugm3' => $ugm3 !== null ? number_format($ugm3, 4, '.', '') : (string) ($fallback['ugm3'] ?? ''),
            'mgm3' => $mgm3 !== null ? number_format($mgm3, 4, '.', '') : (string) ($fallback['mgm3'] ?? ''),
        ];
    }

    private function resolveWorkbookPath(): ?string
    {
        $candidates = [
            env('PREPANALISA_SB_REFERENCE_PATH'),
            storage_path('app/' . self::STORAGE_PATH),
            storage_path('app/private/' . self::STORAGE_PATH),
            ...self::FALLBACK_EXTERNAL_PATHS,
        ];

        try {
            $candidates[] = Storage::disk('local')->path(self::STORAGE_PATH);
        } catch (Throwable) {
        }

        foreach (array_unique(array_filter($candidates)) as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $document = simplexml_load_string($xml);
        if ($document === false) {
            return [];
        }

        $strings = [];
        foreach ($document->si as $item) {
            $text = '';
            if (isset($item->t)) {
                $text = (string) $item->t;
            } elseif (isset($item->r)) {
                foreach ($item->r as $run) {
                    $text .= (string) $run->t;
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function resolveWorksheet(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            throw new RuntimeException('Workbook Excel Sb tidak lengkap.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if ($workbook === false || $rels === false) {
            throw new RuntimeException('Workbook Excel Sb tidak dapat diparse.');
        }

        $relationships = [];
        foreach ($rels->Relationship as $relationship) {
            $attributes = $relationship->attributes();
            $relationships[(string) $attributes['Id']] = (string) $attributes['Target'];
        }

        $namespaces = $workbook->getNamespaces(true);
        $relationshipNamespace = $namespaces['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        foreach ($workbook->sheets->sheet as $sheet) {
            $attributes = $sheet->attributes($relationshipNamespace, true);
            $relationshipId = (string) ($attributes['id'] ?? '');
            $sheetName = (string) $sheet['name'];
            $target = $relationships[$relationshipId] ?? null;
            if (!$target) {
                continue;
            }

            $target = ltrim($target, '/');
            $sheetPath = str_starts_with($target, 'xl/')
                ? $target
                : 'xl/' . $target;

            if ($zip->locateName($sheetPath) !== false) {
                return [$sheetName, $sheetPath];
            }
        }

        throw new RuntimeException('Worksheet Excel Sb tidak ditemukan.');
    }

    private function readWorksheetCells(ZipArchive $zip, string $sheetPath, array $sharedStrings): array
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            throw new RuntimeException('Worksheet Excel Sb tidak dapat dibaca.');
        }

        $worksheet = simplexml_load_string($xml);
        if ($worksheet === false) {
            throw new RuntimeException('Worksheet Excel Sb tidak dapat diparse.');
        }

        $cells = [];
        foreach ($worksheet->sheetData->row as $row) {
            foreach ($row->c as $cell) {
                $attributes = $cell->attributes();
                $reference = (string) ($attributes['r'] ?? '');
                if ($reference === '') {
                    continue;
                }

                $value = '';
                if (isset($cell->v)) {
                    $value = (string) $cell->v;
                    if ((string) ($attributes['t'] ?? '') === 's') {
                        $value = $sharedStrings[(int) $value] ?? '';
                    }
                } elseif (isset($cell->is->t)) {
                    $value = (string) $cell->is->t;
                }

                $cells[$reference] = [
                    'value' => $value,
                ];
            }
        }

        return $cells;
    }

    private function fallbackReference(?string $workbookPath): array
    {
        return $this->normalizeReference([], $workbookPath);
    }

    private function normalizeReference(array $reference, ?string $workbookPath): array
    {
        $normalized = array_replace_recursive(self::DEFAULT_REFERENCE, $reference);
        $normalized['concIntercept'] = $this->normalizeFloat($normalized['concIntercept'] ?? null, self::DEFAULT_REFERENCE['concIntercept']);
        $normalized['concSlope'] = $this->normalizeFloat($normalized['concSlope'] ?? null, self::DEFAULT_REFERENCE['concSlope']);
        $normalized['curveY'] = $this->normalizeFloat($normalized['curveY'] ?? null, self::DEFAULT_REFERENCE['curveY']);
        $normalized['curveX'] = $this->normalizeFloat($normalized['curveX'] ?? null, self::DEFAULT_REFERENCE['curveX']);
        $normalized['sheetName'] = (string) ($normalized['sheetName'] ?? '');
        $normalized['sourcePath'] = $workbookPath ?? '';
        $normalized['sourceMode'] = $workbookPath ? 'excel' : 'fallback';
        $normalized['standardSeries'] = array_values($reference['standardSeries'] ?? self::DEFAULT_REFERENCE['standardSeries']);
        $normalized['constants'] = array_merge(self::DEFAULT_REFERENCE['constants'], (array) ($normalized['constants'] ?? []));
        $normalized['lod'] = array_merge(self::DEFAULT_REFERENCE['lod'], (array) ($normalized['lod'] ?? []));

        return $normalized;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeFloat(mixed $value, float $fallback): float
    {
        $number = $this->toNullableFloat($value);

        return $number ?? $fallback;
    }
}
