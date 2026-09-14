<?php

namespace App\Services\FormulaReference;

use App\Models\ParameterLod;
use App\Support\ServiceSystemCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class Nh3ExcelReferenceService
{
    private const STORAGE_PATH = 'reference_nh3.xlsx';
    private const FALLBACK_EXTERNAL_PATH = 'C:\Users\user\OneDrive\Documents\NH3.xlsx';

    private const DEFAULT_REFERENCE = [
        'factorPpm' => 1.438,
        'factorUgm3' => 695.3,
        'formulaPpm' => 'kons * (vol / 10) * (273 + sk) * 1.438 * 760 / (waktu * fr * 298 * p)',
        'formulaUgm3' => 'ppm * 695.3',
        'sheetName' => '',
        'sourcePath' => '',
        'sourceMode' => 'fallback',
        'mdlAmbien' => [
            'kons' => '0.0843',
            'vol' => '10.0',
            'fr' => '1.000',
            'waktu' => '60',
            'sk' => '25.0',
            'p' => '760',
            'ppm' => '0.0020',
            'ugm3' => '1.4048',
        ],
        'mdlLk' => [
            'kons' => '0.1240',
            'vol' => '10.0',
            'fr' => '1.000',
            'waktu' => '30',
            'sk' => '25.0',
            'p' => '760',
            'ppm' => '0.0059',
            'ugm3' => '4.1327',
        ],
    ];

    public function getReference(): array
    {
        $databaseReference = $this->databaseReference();
        if ($databaseReference !== null) {
            return $databaseReference;
        }

        $workbookPath = $this->resolveWorkbookPath();
        if ($workbookPath === null) {
            return $this->fallbackReference(null);
        }

        $signature = md5($workbookPath . '|' . (string) @filesize($workbookPath) . '|' . (string) @filemtime($workbookPath));

        return Cache::rememberForever('prepanalisa:formula-reference:nh3:' . $signature, function () use ($workbookPath) {
            try {
                return $this->normalizeReference($this->extractReference($workbookPath), $workbookPath);
            } catch (Throwable $e) {
                report($e);

                return $this->fallbackReference($workbookPath);
            }
        });
    }

    private function databaseReference(): ?array
    {
        $lodRows = ParameterLod::query()
            ->with('serviceParameter.category')
            ->where('is_active', true)
            ->whereHas('serviceParameter', function ($query) {
                $query->whereIn('system_code', [
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'NH3'),
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'NH31'),
                ]);
            })
            ->get();

        if ($lodRows->isEmpty()) {
            return null;
        }

        $reference = self::DEFAULT_REFERENCE;
        $factorPpm = $this->normalizeFloat($reference['factorPpm'] ?? null, self::DEFAULT_REFERENCE['factorPpm']);
        $factorUgm3 = $this->normalizeFloat($reference['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);

        foreach ($lodRows as $lod) {
            $categoryCode = strtoupper((string) ($lod->serviceParameter?->category?->system_code ?? ''));
            $target = $categoryCode === ServiceSystemCode::CATEGORY_AMB
                ? 'mdlAmbien'
                : ($categoryCode === ServiceSystemCode::CATEGORY_LK ? 'mdlLk' : null);
            if ($target === null) {
                continue;
            }

            $factorPpm = $this->normalizeFloat($lod->factor_ppm, $factorPpm);
            $factorUgm3 = $this->normalizeFloat($lod->factor_ugm3, $factorUgm3);

            $reference[$target] = $this->buildMdlRowFromModel(
                $lod,
                (array) ($reference[$target] ?? []),
                $factorPpm,
                $factorUgm3
            );
        }

        $reference['sourcePath'] = 'parameter_lods';
        $reference['sourceMode'] = 'database';

        return $reference;
    }

    private function buildMdlRowFromModel(
        ParameterLod $lod,
        array $fallback,
        float $factorPpm,
        float $factorUgm3
    ): array {
        $kons = $this->normalizeFloat($lod->kons, (float) ($fallback['kons'] ?? 0.0843));
        $vol = $this->normalizeFloat($lod->vol, (float) ($fallback['vol'] ?? 10.0));
        $fr = $this->normalizeFloat($lod->fr, (float) ($fallback['fr'] ?? 1.0));
        $waktu = $this->normalizeFloat($lod->waktu, (float) ($fallback['waktu'] ?? 60));
        $sk = $this->normalizeFloat($lod->sk, (float) ($fallback['sk'] ?? 25.0));
        $p = $this->normalizeFloat($lod->pm, (float) ($fallback['p'] ?? 760));

        $ppm = null;
        $ugm3 = null;
        $denominator = $fr * $waktu * 298 * $p;
        if ($denominator != 0.0) {
            $ppm = ($kons * ($vol / 10) * (273 + $sk) * $factorPpm * 760) / $denominator;
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

    public function calculate(float $kons, float $vol, float $fr, float $waktu, float $sk, float $p): array
    {
        $reference = $this->getReference();
        $factorPpm = $this->normalizeFloat($reference['factorPpm'] ?? null, self::DEFAULT_REFERENCE['factorPpm']);
        $factorUgm3 = $this->normalizeFloat($reference['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $denominator = $fr * $waktu * 298 * $p;

        if ($denominator == 0.0) {
            return [
                'kadar_ppm' => null,
                'kadar_ugm3' => null,
            ];
        }

        $ppm = ($kons * ($vol / 10) * (273 + $sk) * $factorPpm * 760) / $denominator;
        $ugm3 = $ppm * $factorUgm3;

        return [
            'kadar_ppm' => $ppm,
            'kadar_ugm3' => $ugm3,
        ];
    }

    private function resolveWorkbookPath(): ?string
    {
        $candidates = [
            env('PREPANALISA_NH3_REFERENCE_PATH'),
            storage_path('app/' . self::STORAGE_PATH),
            storage_path('app/private/' . self::STORAGE_PATH),
            self::FALLBACK_EXTERNAL_PATH,
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

    private function extractReference(string $workbookPath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive tidak tersedia untuk membaca acuan Excel NH3.');
        }

        $zip = new ZipArchive();
        if ($zip->open($workbookPath) !== true) {
            throw new RuntimeException('File acuan Excel NH3 tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            [$sheetName, $sheetPath] = $this->resolveWorksheet($zip);
            $cells = $this->readWorksheetCells($zip, $sheetPath, $sharedStrings);

            return [
                'sheetName' => $sheetName,
                'factorPpm' => $this->extractPpmFactor($cells['I32']['formula'] ?? ($cells['I29']['formula'] ?? '')),
                'factorUgm3' => $this->extractUgm3Factor($cells['J32']['formula'] ?? ($cells['J29']['formula'] ?? '')),
                'formulaPpm' => $cells['I32']['formula'] ?? ($cells['I29']['formula'] ?? ''),
                'formulaUgm3' => $cells['J32']['formula'] ?? ($cells['J29']['formula'] ?? ''),
                'mdlAmbien' => $this->extractRow($cells, 32),
                'mdlLk' => $this->extractRow($cells, 39),
            ];
        } finally {
            $zip->close();
        }
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
            throw new RuntimeException('Metadata workbook acuan Excel NH3 tidak lengkap.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if ($workbook === false || $rels === false) {
            throw new RuntimeException('Workbook acuan Excel NH3 tidak valid.');
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
            if (stripos($name, 'nh3') !== false) {
                return [$name, $targets[$relationId]];
            }
        }

        throw new RuntimeException('Sheet acuan NH3 tidak ditemukan.');
    }

    private function readWorksheetCells(ZipArchive $zip, string $sheetPath, array $sharedStrings): array
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            throw new RuntimeException('Worksheet acuan NH3 tidak dapat dibaca.');
        }

        $sheet = simplexml_load_string($xml);
        if ($sheet === false) {
            throw new RuntimeException('Worksheet acuan NH3 tidak valid.');
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

    private function extractPpmFactor(string $formula): float
    {
        if (preg_match('/\([A-Z0-9]+\+[A-Z0-9]+\)\*([0-9]+(?:\.[0-9]+)?)\*760\//', preg_replace('/\s+/', '', strtoupper($formula)), $matches)) {
            return (float) $matches[1];
        }

        return self::DEFAULT_REFERENCE['factorPpm'];
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

        $reference['factorPpm'] = $this->normalizeFloat($payload['factorPpm'] ?? null, self::DEFAULT_REFERENCE['factorPpm']);
        $reference['factorUgm3'] = $this->normalizeFloat($payload['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $reference['formulaPpm'] = trim((string) ($payload['formulaPpm'] ?? $reference['formulaPpm']));
        $reference['formulaUgm3'] = trim((string) ($payload['formulaUgm3'] ?? $reference['formulaUgm3']));
        $reference['sheetName'] = trim((string) ($payload['sheetName'] ?? ''));
        $reference['sourcePath'] = $workbookPath;
        $reference['sourceMode'] = 'excel';
        $reference['mdlAmbien'] = $this->normalizeRow($payload['mdlAmbien'] ?? [], self::DEFAULT_REFERENCE['mdlAmbien']);
        $reference['mdlLk'] = $this->normalizeRow($payload['mdlLk'] ?? [], self::DEFAULT_REFERENCE['mdlLk']);

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
