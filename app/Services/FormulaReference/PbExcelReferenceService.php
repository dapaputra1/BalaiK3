<?php

namespace App\Services\FormulaReference;

use App\Models\ParameterLod;
use App\Support\ServiceSystemCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class PbExcelReferenceService
{
    private const SERVICE_PARAMETER_SYSTEM_CODE = 'LK__KDLPB';
    private const STORAGE_PATH = 'reference_pb.xlsx';
    private const FALLBACK_EXTERNAL_PATHS = [
        'C:\Users\user\OneDrive\Documents\Timbal (Pb).xlsx',
        'C:\Users\user\OneDrive\Documents\Pb.xlsx',
    ];

    private const DEFAULT_REFERENCE = [
        'formulaConc' => 'cons = 0 + 222.222 * abs',
        'concIntercept' => 0.0,
        'concSlope' => 222.222,
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
        'lod' => [
            'kons' => '0.0095',
            'vol' => '15.0',
            'fr' => '500.000',
            'waktu' => '30',
            'sk' => '25.0',
            'p' => '760',
            'ugm3' => '0.0095',
            'mgm3' => '0.0000',
        ],
        'sample' => [
            'kons' => '0.1428',
            'vol' => '15.0',
            'fr' => '500.000',
            'waktu' => '30',
            'sk' => '25.0',
            'p' => '760',
            'ugm3' => '0.1428',
            'mgm3' => '0.0001',
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
                'prepanalisa:formula-reference:pb:' . md5($workbookPath . '|' . (string) @filesize($workbookPath) . '|' . (string) @filemtime($workbookPath)),
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
        $absorbance = $this->absorbanceReferenceService->getReference('PB');
        $concIntercept = $this->normalizeFloat($absorbance['intercept'] ?? null, $this->normalizeFloat($reference['concIntercept'] ?? null, self::DEFAULT_REFERENCE['concIntercept']));
        $concSlope = $this->normalizeFloat($absorbance['slope'] ?? null, $this->normalizeFloat($reference['concSlope'] ?? null, self::DEFAULT_REFERENCE['concSlope']));

        $reference['concIntercept'] = $concIntercept;
        $reference['concSlope'] = $concSlope;
        $reference['formulaConc'] = sprintf(
            'cons = %s + %s * abs',
            rtrim(rtrim(number_format($concIntercept, 6, '.', ''), '0'), '.'),
            rtrim(rtrim(number_format($concSlope, 6, '.', ''), '0'), '.')
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
            $reference['lod'] = $this->buildLodRowFromModel($lod, (array) ($reference['lod'] ?? []), $reference);
            $reference['lodSourceMode'] = 'database';
        }

        return $reference;
    }

    private function extractReference(string $workbookPath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive tidak tersedia untuk membaca acuan Excel Pb.');
        }

        $zip = new ZipArchive();
        if ($zip->open($workbookPath) !== true) {
            throw new RuntimeException('File acuan Excel Pb tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            [$sheetName, $sheetPath] = $this->resolveWorksheet($zip);
            $cells = $this->readWorksheetCells($zip, $sheetPath, $sharedStrings);

            $concFormula = (string) ($cells['F16']['formula'] ?? ($cells['F17']['formula'] ?? ''));
            $kadarFormula = (string) ($cells['I31']['formula'] ?? ($cells['I32']['formula'] ?? ''));

            return [
                'sheetName' => $sheetName,
                'formulaConc' => $concFormula,
                'concIntercept' => $this->extractIntercept($concFormula),
                'concSlope' => $this->extractSlope($concFormula),
                'formulaKadar' => $kadarFormula,
                'lod' => $this->extractCalcRow($cells, 36),
                'sample' => $this->extractCalcRow($cells, 31),
            ];
        } finally {
            $zip->close();
        }
    }

    private function extractCalcRow(array $cells, int $row): array
    {
        $mgm3 = $this->toNullableFloat($cells['I' . $row]['value'] ?? null);
        $ugm3 = $mgm3 !== null ? $mgm3 * 1000 : null;

        return [
            'kons' => $cells['C' . $row]['value'] ?? null,
            'vol' => $cells['D' . $row]['value'] ?? null,
            'fr' => $cells['E' . $row]['value'] ?? null,
            'waktu' => $cells['F' . $row]['value'] ?? null,
            'sk' => $cells['G' . $row]['value'] ?? null,
            'p' => $cells['H' . $row]['value'] ?? null,
            'ugm3' => $ugm3,
            'mgm3' => $mgm3,
        ];
    }

    private function buildLodRowFromModel(ParameterLod $lod, array $fallback, array $reference): array
    {
        $kons = $this->normalizeFloat($lod->kons, (float) ($fallback['kons'] ?? 0.0095));
        $vol = $this->normalizeFloat($lod->vol, (float) ($fallback['vol'] ?? 15.0));
        $fr = $this->normalizeFloat($lod->fr, (float) ($fallback['fr'] ?? 500));
        $waktu = $this->normalizeFloat($lod->waktu, (float) ($fallback['waktu'] ?? 30));
        $sk = $this->normalizeFloat($lod->sk, (float) ($fallback['sk'] ?? 25));
        $p = $this->normalizeFloat($lod->pm, (float) ($fallback['p'] ?? 760));
        $constants = is_array($reference['constants'] ?? null) ? $reference['constants'] : [];
        $temperatureOffset = $this->normalizeFloat($constants['temperatureOffset'] ?? null, self::DEFAULT_REFERENCE['constants']['temperatureOffset']);
        $pressureFactor = $this->normalizeFloat($constants['pressureFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['pressureFactor']);
        $denominatorTemperature = $this->normalizeFloat($constants['denominatorTemperature'] ?? null, self::DEFAULT_REFERENCE['constants']['denominatorTemperature']);
        $denominator = $fr * $waktu * $denominatorTemperature * $p;
        $mgm3 = $denominator == 0.0
            ? null
            : ($kons * $vol * ($temperatureOffset + $sk) * $pressureFactor) / $denominator;
        $ugm3 = $mgm3 !== null ? $mgm3 * 1000 : null;

        return [
            'kons' => number_format($kons, 4, '.', ''),
            'vol' => number_format($vol, 1, '.', ''),
            'fr' => number_format($fr, 3, '.', ''),
            'waktu' => number_format($waktu, 0, '.', ''),
            'sk' => number_format($sk, 1, '.', ''),
            'p' => number_format($p, 0, '.', ''),
            'ugm3' => $ugm3 !== null ? number_format($ugm3, 4, '.', '') : (string) ($fallback['ugm3'] ?? '0.0095'),
            'mgm3' => $mgm3 !== null ? number_format($mgm3, 4, '.', '') : (string) ($fallback['mgm3'] ?? '0.0000'),
        ];
    }

    private function resolveWorkbookPath(): ?string
    {
        $candidates = [
            env('PREPANALISA_PB_REFERENCE_PATH'),
            storage_path('app/' . self::STORAGE_PATH),
            storage_path('app/private/' . self::STORAGE_PATH),
            ...self::FALLBACK_EXTERNAL_PATHS,
        ];

        try {
            $candidates[] = Storage::disk('local')->path(self::STORAGE_PATH);
        } catch (Throwable) {
            // Abaikan bila disk tidak mendukung absolute path.
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
            throw new RuntimeException('Metadata workbook acuan Excel Pb tidak lengkap.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if ($workbook === false || $rels === false) {
            throw new RuntimeException('Workbook acuan Excel Pb tidak valid.');
        }

        $workbook->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $rels->registerXPathNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $targets = [];
        foreach ($rels->xpath('//rel:Relationship') ?: [] as $relationship) {
            $id = (string) $relationship['Id'];
            $target = (string) $relationship['Target'];
            if ($id !== '' && $target !== '') {
                $targets[$id] = 'xl/' . ltrim($target, '/');
            }
        }

        foreach ($workbook->xpath('//a:sheets/a:sheet') ?: [] as $sheet) {
            $name = trim((string) $sheet['name']);
            $relationId = (string) $sheet->attributes('r', true)->id;
            if ($name === '' || $relationId === '' || !isset($targets[$relationId])) {
                continue;
            }

            if (stripos($name, 'pb') !== false || stripos($name, 'timbal') !== false) {
                return [$name, $targets[$relationId]];
            }
        }

        throw new RuntimeException('Sheet acuan Pb tidak ditemukan.');
    }

    private function readWorksheetCells(ZipArchive $zip, string $sheetPath, array $sharedStrings): array
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            throw new RuntimeException('Worksheet acuan Pb tidak dapat dibaca.');
        }

        $sheet = simplexml_load_string($xml);
        if ($sheet === false) {
            throw new RuntimeException('Worksheet acuan Pb tidak valid.');
        }

        $sheet->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $cells = [];

        foreach ($sheet->xpath('//a:sheetData/a:row/a:c') ?: [] as $cell) {
            $ref = strtoupper((string) $cell['r']);
            if ($ref === '') {
                continue;
            }

            $type = (string) $cell['t'];
            $value = isset($cell->v) ? (string) $cell->v : null;
            if ($type === 's' && $value !== null && $value !== '' && isset($sharedStrings[(int) $value])) {
                $value = $sharedStrings[(int) $value];
            }

            $cells[$ref] = [
                'value' => $value,
                'formula' => isset($cell->f) ? trim((string) $cell->f) : '',
            ];
        }

        return $cells;
    }

    private function extractSlope(string $formula): float
    {
        $normalized = preg_replace('/\s+/', '', strtoupper($formula));
        if ($normalized && preg_match('/\*([0-9]+(?:\.[0-9]+)?)/', $normalized, $matches)) {
            return (float) $matches[1];
        }

        return self::DEFAULT_REFERENCE['concSlope'];
    }

    private function extractIntercept(string $formula): float
    {
        $normalized = preg_replace('/\s+/', '', strtoupper($formula));
        if (!$normalized) {
            return self::DEFAULT_REFERENCE['concIntercept'];
        }

        if (preg_match('/([+-][0-9]+(?:\.[0-9]+)?)$/', $normalized, $matches)) {
            return (float) $matches[1];
        }

        return self::DEFAULT_REFERENCE['concIntercept'];
    }

    private function normalizeReference(array $payload, string $workbookPath): array
    {
        $reference = self::DEFAULT_REFERENCE;
        $reference['formulaConc'] = trim((string) ($payload['formulaConc'] ?? $reference['formulaConc']));
        $reference['concIntercept'] = $this->normalizeFloat($payload['concIntercept'] ?? null, self::DEFAULT_REFERENCE['concIntercept']);
        $reference['concSlope'] = $this->normalizeFloat($payload['concSlope'] ?? null, self::DEFAULT_REFERENCE['concSlope']);
        $reference['formulaKadar'] = trim((string) ($payload['formulaKadar'] ?? $reference['formulaKadar']));
        $reference['sheetName'] = trim((string) ($payload['sheetName'] ?? ''));
        $reference['sourcePath'] = $workbookPath;
        $reference['sourceMode'] = 'excel';
        $reference['lod'] = $this->normalizeRow($payload['lod'] ?? [], self::DEFAULT_REFERENCE['lod']);
        $reference['sample'] = $this->normalizeRow($payload['sample'] ?? [], self::DEFAULT_REFERENCE['sample']);

        return $reference;
    }

    private function normalizeRow(mixed $row, array $fallback): array
    {
        $row = is_array($row) ? $row : [];

        return [
            'kons' => $this->formatDecimal($row['kons'] ?? null, $fallback['kons'], 4),
            'vol' => $this->formatDecimal($row['vol'] ?? null, $fallback['vol'], 1),
            'fr' => $this->formatDecimal($row['fr'] ?? null, $fallback['fr'], 3),
            'waktu' => $this->formatDecimal($row['waktu'] ?? null, $fallback['waktu'], 0),
            'sk' => $this->formatDecimal($row['sk'] ?? null, $fallback['sk'], 1),
            'p' => $this->formatDecimal($row['p'] ?? null, $fallback['p'], 0),
            'ugm3' => $this->formatDecimal($row['ugm3'] ?? null, $fallback['ugm3'], 4),
            'mgm3' => $this->formatDecimal($row['mgm3'] ?? null, $fallback['mgm3'], 4),
        ];
    }

    private function fallbackReference(?string $workbookPath): array
    {
        $reference = self::DEFAULT_REFERENCE;
        $reference['sourcePath'] = $workbookPath ?? '';

        return $reference;
    }

    private function formatDecimal(mixed $value, string $fallback, int $precision): string
    {
        $number = $this->toNullableFloat($value);
        if ($number === null) {
            return $fallback;
        }

        return number_format($number, $precision, '.', '');
    }

    private function normalizeFloat(mixed $value, float $fallback): float
    {
        $number = $this->toNullableFloat($value);
        return $number ?? $fallback;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        return is_numeric($value) ? (float) $value : null;
    }
}
