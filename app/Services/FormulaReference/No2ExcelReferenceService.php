<?php

namespace App\Services\FormulaReference;

use App\Models\ParameterLod;
use App\Support\ServiceSystemCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class No2ExcelReferenceService
{
    private const STORAGE_PATH = 'reference_no2.xlsx';
    private const FALLBACK_EXTERNAL_PATHS = [
        'C:\Users\user\OneDrive\Documents\NO2.xlsx',
        'C:\Users\user\OneDrive\Documents\No2.xlsx',
    ];

    private const DEFAULT_REFERENCE = [
        'factorVolume' => 24.45,
        'molecularWeight' => 46.0,
        'factorUgm3' => 1881.0,
        'formulaPpm' => 'kons * vol * (273 + sk) * 760 * 24.45 / (fr * waktu * 298 * p * 46)',
        'formulaUgm3' => 'ppm * 1881',
        'sheetName' => '',
        'sourcePath' => '',
        'sourceMode' => 'fallback',
        'lodAmbien' => [
            'kons' => '0.0078',
            'vol' => '10.0',
            'fr' => '0.400',
            'waktu' => '60',
            'sk' => '25.0',
            'p' => '760',
            'ppm' => '0.0017',
            'ugm3' => '3.2493',
        ],
        'lodLk' => [
            'kons' => '0.0078',
            'vol' => '10.0',
            'fr' => '0.400',
            'waktu' => '60',
            'sk' => '25.0',
            'p' => '760',
            'ppm' => '0.0017',
            'ugm3' => '3.2493',
        ],
    ];

    public function getReference(): array
    {
        $workbookPath = $this->resolveWorkbookPath();
        $reference = $workbookPath === null
            ? $this->fallbackReference(null)
            : Cache::rememberForever(
                'prepanalisa:formula-reference:no2:' . md5($workbookPath . '|' . (string) @filesize($workbookPath) . '|' . (string) @filemtime($workbookPath)),
                function () use ($workbookPath) {
                    try {
                        return $this->normalizeReference($this->extractReference($workbookPath), $workbookPath);
                    } catch (Throwable $e) {
                        report($e);

                        return $this->fallbackReference($workbookPath);
                    }
                }
            );

        return $this->applyDatabaseLodOverride($reference);
    }

    public function calculate(float $kons, float $vol, float $fr, float $waktu, float $sk, float $p): array
    {
        $reference = $this->getReference();
        $factorVolume = $this->normalizeFloat($reference['factorVolume'] ?? null, self::DEFAULT_REFERENCE['factorVolume']);
        $molecularWeight = $this->normalizeFloat($reference['molecularWeight'] ?? null, self::DEFAULT_REFERENCE['molecularWeight']);
        $factorUgm3 = $this->normalizeFloat($reference['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $denominator = $fr * $waktu * 298 * $p * $molecularWeight;

        if ($denominator == 0.0) {
            return [
                'kadar_ppm' => null,
                'kadar_ugm3' => null,
            ];
        }

        $ppm = ($kons * $vol * (273 + $sk) * 760 * $factorVolume) / $denominator;
        $ugm3 = $ppm * $factorUgm3;

        return [
            'kadar_ppm' => $ppm,
            'kadar_ugm3' => $ugm3,
        ];
    }

    private function applyDatabaseLodOverride(array $reference): array
    {
        $lodRows = ParameterLod::query()
            ->with('serviceParameter.category')
            ->where('is_active', true)
            ->whereHas('serviceParameter', function ($query) {
                $query->whereIn('system_code', [
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'NO2'),
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'NO21'),
                ]);
            })
            ->get();

        if ($lodRows->isEmpty()) {
            return $reference;
        }

        $factorVolume = $this->normalizeFloat($reference['factorVolume'] ?? null, self::DEFAULT_REFERENCE['factorVolume']);
        $molecularWeight = $this->normalizeFloat($reference['molecularWeight'] ?? null, self::DEFAULT_REFERENCE['molecularWeight']);
        $factorUgm3 = $this->normalizeFloat($reference['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);

        foreach ($lodRows as $lod) {
            $categoryCode = strtoupper((string) ($lod->serviceParameter?->category?->system_code ?? ''));
            $target = $categoryCode === ServiceSystemCode::CATEGORY_AMB
                ? 'lodAmbien'
                : ($categoryCode === ServiceSystemCode::CATEGORY_LK ? 'lodLk' : null);
            if ($target === null) {
                continue;
            }

            $reference[$target] = $this->buildLodRowFromModel(
                $lod,
                (array) ($reference[$target] ?? []),
                $factorVolume,
                $molecularWeight,
                $factorUgm3
            );
        }

        $reference['sourcePath'] = 'parameter_lods';
        $reference['sourceMode'] = 'database';

        return $reference;
    }

    private function extractReference(string $workbookPath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive tidak tersedia untuk membaca acuan Excel NO2.');
        }

        $zip = new ZipArchive();
        if ($zip->open($workbookPath) !== true) {
            throw new RuntimeException('File acuan Excel NO2 tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            [$sheetName, $sheetPath] = $this->resolveWorksheet($zip);
            $cells = $this->readWorksheetCells($zip, $sheetPath, $sharedStrings);
            $ppmFormula = (string) ($cells['I33']['formula'] ?? ($cells['I32']['formula'] ?? ($cells['I30']['formula'] ?? '')));
            $ugm3Formula = (string) ($cells['J33']['formula'] ?? ($cells['J32']['formula'] ?? ($cells['J30']['formula'] ?? '')));

            return [
                'sheetName' => $sheetName,
                'factorVolume' => $this->extractFactorVolume($ppmFormula),
                'molecularWeight' => $this->extractMolecularWeight($ppmFormula),
                'factorUgm3' => $this->extractUgm3Factor($ugm3Formula),
                'formulaPpm' => $ppmFormula,
                'formulaUgm3' => $ugm3Formula,
                'lodAmbien' => $this->extractRow($cells, 33),
                'lodLk' => $this->extractRow($cells, 40),
            ];
        } finally {
            $zip->close();
        }
    }

    private function buildLodRowFromModel(
        ParameterLod $lod,
        array $fallback,
        float $factorVolume,
        float $molecularWeight,
        float $factorUgm3
    ): array {
        $kons = $this->normalizeFloat($lod->kons, (float) ($fallback['kons'] ?? 0.0078));
        $vol = $this->normalizeFloat($lod->vol, (float) ($fallback['vol'] ?? 10.0));
        $fr = $this->normalizeFloat($lod->fr, (float) ($fallback['fr'] ?? 0.4));
        $waktu = $this->normalizeFloat($lod->waktu, (float) ($fallback['waktu'] ?? 60));
        $sk = $this->normalizeFloat($lod->sk, (float) ($fallback['sk'] ?? 25.0));
        $p = $this->normalizeFloat($lod->pm, (float) ($fallback['p'] ?? 760));

        $ppm = null;
        $ugm3 = null;
        $denominator = $fr * $waktu * 298 * $p * $molecularWeight;
        if ($denominator != 0.0) {
            $ppm = ($kons * $vol * (273 + $sk) * 760 * $factorVolume) / $denominator;
            $ugm3 = $ppm * $factorUgm3;
        }

        return [
            'kons' => number_format($kons, 4, '.', ''),
            'vol' => number_format($vol, 1, '.', ''),
            'fr' => number_format($fr, 3, '.', ''),
            'waktu' => number_format($waktu, 0, '.', ''),
            'sk' => number_format($sk, 1, '.', ''),
            'p' => number_format($p, 0, '.', ''),
            'ppm' => $ppm !== null ? number_format($ppm, 4, '.', '') : (string) ($fallback['ppm'] ?? '0.0000'),
            'ugm3' => $ugm3 !== null ? number_format($ugm3, 4, '.', '') : (string) ($fallback['ugm3'] ?? '0.0000'),
        ];
    }

    private function resolveWorkbookPath(): ?string
    {
        $candidates = [
            env('PREPANALISA_NO2_REFERENCE_PATH'),
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

    private function extractRow(array $cells, int $row): array
    {
        return [
            'kons' => $cells['C' . $row]['value'] ?? null,
            'vol' => $cells['D' . $row]['value'] ?? null,
            'fr' => $cells['E' . $row]['value'] ?? null,
            'waktu' => $cells['F' . $row]['value'] ?? null,
            'sk' => $cells['G' . $row]['value'] ?? null,
            'p' => $cells['H' . $row]['value'] ?? null,
            'ppm' => $cells['I' . $row]['value'] ?? null,
            'ugm3' => $cells['J' . $row]['value'] ?? null,
        ];
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
            throw new RuntimeException('Metadata workbook acuan Excel NO2 tidak lengkap.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if ($workbook === false || $rels === false) {
            throw new RuntimeException('Workbook acuan Excel NO2 tidak valid.');
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
            if (stripos($name, 'no2') !== false || stripos($name, 'nitrogen') !== false) {
                return [$name, $targets[$relationId]];
            }
        }

        throw new RuntimeException('Sheet acuan NO2 tidak ditemukan.');
    }

    private function readWorksheetCells(ZipArchive $zip, string $sheetPath, array $sharedStrings): array
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            throw new RuntimeException('Worksheet acuan NO2 tidak dapat dibaca.');
        }

        $sheet = simplexml_load_string($xml);
        if ($sheet === false) {
            throw new RuntimeException('Worksheet acuan NO2 tidak valid.');
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

    private function extractFactorVolume(string $formula): float
    {
        if (preg_match('/\*([0-9]+(?:\.[0-9]+)?)\)\/\([A-Z]+[0-9]+\*[A-Z]+[0-9]+\*298\*[A-Z]+[0-9]+\*([0-9]+(?:\.[0-9]+)?)\)/', preg_replace('/\s+/', '', strtoupper($formula)), $matches)) {
            return (float) $matches[1];
        }

        return self::DEFAULT_REFERENCE['factorVolume'];
    }

    private function extractMolecularWeight(string $formula): float
    {
        if (preg_match('/\*[0-9]+(?:\.[0-9]+)?\)\/\([A-Z]+[0-9]+\*[A-Z]+[0-9]+\*298\*[A-Z]+[0-9]+\*([0-9]+(?:\.[0-9]+)?)\)/', preg_replace('/\s+/', '', strtoupper($formula)), $matches)) {
            return (float) $matches[1];
        }

        return self::DEFAULT_REFERENCE['molecularWeight'];
    }

    private function extractUgm3Factor(string $formula): float
    {
        if (preg_match('/\*([0-9]+(?:\.[0-9]+)?)$/', preg_replace('/\s+/', '', strtoupper($formula)), $matches)) {
            return (float) $matches[1];
        }

        return self::DEFAULT_REFERENCE['factorUgm3'];
    }

    private function normalizeReference(array $payload, string $workbookPath): array
    {
        $reference = self::DEFAULT_REFERENCE;
        $reference['factorVolume'] = $this->normalizeFloat($payload['factorVolume'] ?? null, self::DEFAULT_REFERENCE['factorVolume']);
        $reference['molecularWeight'] = $this->normalizeFloat($payload['molecularWeight'] ?? null, self::DEFAULT_REFERENCE['molecularWeight']);
        $reference['factorUgm3'] = $this->normalizeFloat($payload['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $reference['formulaPpm'] = trim((string) ($payload['formulaPpm'] ?? $reference['formulaPpm']));
        $reference['formulaUgm3'] = trim((string) ($payload['formulaUgm3'] ?? $reference['formulaUgm3']));
        $reference['sheetName'] = trim((string) ($payload['sheetName'] ?? ''));
        $reference['sourcePath'] = $workbookPath;
        $reference['sourceMode'] = 'excel';
        $reference['lodAmbien'] = $this->normalizeRow($payload['lodAmbien'] ?? [], self::DEFAULT_REFERENCE['lodAmbien']);
        $reference['lodLk'] = $this->normalizeRow($payload['lodLk'] ?? [], self::DEFAULT_REFERENCE['lodLk']);

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
            'ppm' => $this->formatDecimal($row['ppm'] ?? null, $fallback['ppm'], 4),
            'ugm3' => $this->formatDecimal($row['ugm3'] ?? null, $fallback['ugm3'], 4),
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
