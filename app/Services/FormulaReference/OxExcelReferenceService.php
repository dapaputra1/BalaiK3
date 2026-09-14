<?php

namespace App\Services\FormulaReference;

use App\Models\ParameterLod;
use App\Support\ServiceSystemCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class OxExcelReferenceService
{
    private const STORAGE_PATH = 'reference_ox.xlsx';
    private const FALLBACK_EXTERNAL_PATH = 'C:\Users\user\OneDrive\Documents\OX.xlsx';

    private const DEFAULT_REFERENCE = [
        'consIntercept' => 0.0244,
        'consSlope' => 0.8264,
        'factorUgm3' => 1963.2,
        'formulaCons' => 'cons = 0.0244 + 0.8264 * abs',
        'formulaPpm' => 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
        'formulaUgm3' => 'ppm * 1963.2',
        'sheetName' => '',
        'sourcePath' => '',
        'sourceMode' => 'fallback',
        'lodAmbien' => [
            'kons' => '0.00744',
            'vol' => '10.0',
            'fr' => '1.000',
            'waktu' => '30',
            'sk' => '25.0',
            'p' => '760',
            'ppm' => '0.002480',
            'ugm3' => '4.8687',
        ],
        'lodLk' => [
            'kons' => '0.00506',
            'vol' => '10.0',
            'fr' => '1.000',
            'waktu' => '30',
            'sk' => '25.0',
            'p' => '760',
            'ppm' => '0.001687',
            'ugm3' => '3.3113',
        ],
    ];

    public function getReference(): array
    {
        $workbookPath = $this->resolveWorkbookPath();
        $reference = $workbookPath === null
            ? $this->fallbackReference(null)
            : Cache::rememberForever(
                'prepanalisa:formula-reference:ox:' . md5($workbookPath . '|' . (string) @filesize($workbookPath) . '|' . (string) @filemtime($workbookPath)),
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

    public function calculateAbsorbance(float $abs): float
    {
        $reference = $this->getReference();
        $intercept = $this->normalizeFloat($reference['consIntercept'] ?? null, self::DEFAULT_REFERENCE['consIntercept']);
        $slope = $this->normalizeFloat($reference['consSlope'] ?? null, self::DEFAULT_REFERENCE['consSlope']);

        return $intercept + ($slope * $abs);
    }

    public function calculate(float $kons, float $vol, float $fr, float $waktu, float $sk, float $p): array
    {
        $reference = $this->getReference();
        $factorUgm3 = $this->normalizeFloat($reference['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $denominator = $fr * $waktu * 298 * $p;

        if ($denominator == 0.0) {
            return [
                'kadar_ppm' => null,
                'kadar_ugm3' => null,
            ];
        }

        $ppm = ($kons * $vol * (273 + $sk) * 760) / $denominator;
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
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'OX'),
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'OX1'),
                ]);
            })
            ->get();

        if ($lodRows->isEmpty()) {
            return $reference;
        }

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
                $factorUgm3
            );
        }

        $reference['sourcePath'] = 'parameter_lods';
        $reference['sourceMode'] = 'database';

        return $reference;
    }

    private function buildLodRowFromModel(
        ParameterLod $lod,
        array $fallback,
        float $factorUgm3
    ): array {
        $kons = $this->normalizeFloat($lod->kons, (float) ($fallback['kons'] ?? 0.00744));
        $vol = $this->normalizeFloat($lod->vol, (float) ($fallback['vol'] ?? 10.0));
        $fr = $this->normalizeFloat($lod->fr, (float) ($fallback['fr'] ?? 1.0));
        $waktu = $this->normalizeFloat($lod->waktu, (float) ($fallback['waktu'] ?? 30));
        $sk = $this->normalizeFloat($lod->sk, (float) ($fallback['sk'] ?? 25.0));
        $p = $this->normalizeFloat($lod->pm, (float) ($fallback['p'] ?? 760));

        $ppm = null;
        $ugm3 = null;
        $denominator = $fr * $waktu * 298 * $p;
        if ($denominator != 0.0) {
            $ppm = ($kons * $vol * (273 + $sk) * 760) / $denominator;
            $ugm3 = $ppm * $factorUgm3;
        }

        return [
            'kons' => number_format($kons, 5, '.', ''),
            'vol' => number_format($vol, 1, '.', ''),
            'fr' => number_format($fr, 3, '.', ''),
            'waktu' => number_format($waktu, 0, '.', ''),
            'sk' => number_format($sk, 1, '.', ''),
            'p' => number_format($p, 0, '.', ''),
            'ppm' => $ppm !== null ? number_format($ppm, 6, '.', '') : (string) ($fallback['ppm'] ?? '0.000000'),
            'ugm3' => $ugm3 !== null ? number_format($ugm3, 4, '.', '') : (string) ($fallback['ugm3'] ?? '0.0000'),
        ];
    }

    private function resolveWorkbookPath(): ?string
    {
        $candidates = [
            env('PREPANALISA_OX_REFERENCE_PATH'),
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
            return $this->extractReferenceViaPowerShell($workbookPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($workbookPath) !== true) {
            throw new RuntimeException('File acuan Excel OX tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            [$sheetName, $sheetPath] = $this->resolveWorksheet($zip);
            $cells = $this->readWorksheetCells($zip, $sheetPath, $sharedStrings);
            $consFormula = trim((string) ($cells['B22']['value'] ?? ''));
            $absFormula = trim((string) ($cells['F16']['formula'] ?? ''));

            return [
                'sheetName' => $sheetName,
                'consIntercept' => $this->extractIntercept($absFormula, $consFormula),
                'consSlope' => $this->extractSlope($absFormula, $consFormula),
                'factorUgm3' => $this->extractUgm3Factor($cells['J33']['formula'] ?? ($cells['J31']['formula'] ?? '')),
                'formulaCons' => $consFormula !== '' ? $consFormula : $absFormula,
                'formulaPpm' => $cells['I33']['formula'] ?? ($cells['I30']['formula'] ?? ''),
                'formulaUgm3' => $cells['J33']['formula'] ?? ($cells['J31']['formula'] ?? ''),
                'lodAmbien' => $this->extractRow($cells, 33),
                'lodLk' => $this->extractRow($cells, 40),
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

    private function extractReferenceViaPowerShell(string $workbookPath): array
    {
        $runner = strtoupper(substr(PHP_OS_FAMILY, 0, 3)) === 'WIN' ? 'powershell.exe' : 'pwsh';
        $escapedPath = str_replace("'", "''", $workbookPath);
        $script = <<<'POWERSHELL'
Add-Type -AssemblyName System.IO.Compression.FileSystem
$path = '__PATH__'
function Read-EntryText([System.IO.Compression.ZipArchive]$zip, [string]$name) {
    $entry = $zip.Entries | Where-Object { $_.FullName -eq $name } | Select-Object -First 1
    if (-not $entry) { return $null }
    $stream = $entry.Open()
    try {
        $reader = New-Object System.IO.StreamReader($stream)
        try { return $reader.ReadToEnd() } finally { $reader.Dispose() }
    } finally {
        $stream.Dispose()
    }
}
function Get-SharedStrings([System.IO.Compression.ZipArchive]$zip) {
    $xmlText = Read-EntryText $zip 'xl/sharedStrings.xml'
    if (-not $xmlText) { return @() }
    [xml]$xml = $xmlText
    $result = @()
    foreach ($si in $xml.sst.si) {
        if ($si.t) {
            $result += [string]$si.t
            continue
        }
        $text = ''
        foreach ($run in $si.r) {
            $text += [string]$run.t
        }
        $result += $text
    }
    return ,$result
}
function Resolve-Sheet([System.IO.Compression.ZipArchive]$zip) {
    [xml]$workbook = Read-EntryText $zip 'xl/workbook.xml'
    [xml]$rels = Read-EntryText $zip 'xl/_rels/workbook.xml.rels'
    $targetMap = @{}
    $relsNs = New-Object System.Xml.XmlNamespaceManager($rels.NameTable)
    $relsNs.AddNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships')
    foreach ($rel in $rels.SelectNodes('//rel:Relationship', $relsNs)) {
        $targetMap[[string]$rel.Id] = 'xl/' + [string]$rel.Target
    }
    $ns = New-Object System.Xml.XmlNamespaceManager($workbook.NameTable)
    $ns.AddNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main')
    $ns.AddNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')
    foreach ($sheet in $workbook.SelectNodes('//a:sheets/a:sheet', $ns)) {
        $name = [string]$sheet.GetAttribute('name')
        $rid = [string]$sheet.GetAttribute('id', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')
        if ($name -match 'ox' -and $targetMap.ContainsKey($rid)) {
            return @{ name = $name; path = $targetMap[$rid] }
        }
    }
    throw 'Sheet acuan OX tidak ditemukan.'
}
function Read-Cells([System.IO.Compression.ZipArchive]$zip, [string]$sheetPath, [array]$sharedStrings) {
    [xml]$sheet = Read-EntryText $zip $sheetPath
    $cells = @{}
    foreach ($row in $sheet.worksheet.sheetData.row) {
        foreach ($cell in $row.c) {
            $ref = [string]$cell.r
            if (-not $ref) { continue }
            $value = if ($null -ne $cell.v) { [string]$cell.v } else { $null }
            if ([string]$cell.t -eq 's' -and $value -match '^\d+$') {
                $index = [int]$value
                if ($index -ge 0 -and $index -lt $sharedStrings.Count) {
                    $value = [string]$sharedStrings[$index]
                }
            }
            $cells[$ref.ToUpperInvariant()] = @{
                value = $value
                formula = if ($null -ne $cell.f) { [string]$cell.f } else { '' }
            }
        }
    }
    return $cells
}
$zip = [System.IO.Compression.ZipFile]::OpenRead($path)
try {
    $shared = Get-SharedStrings $zip
    $sheetInfo = Resolve-Sheet $zip
    $cells = Read-Cells $zip $sheetInfo.path $shared
    $result = @{
        sheetName = $sheetInfo.name
        formulaCons = if ($cells.ContainsKey('B22')) { $cells['B22'].value } else { '' }
        absFormula = if ($cells.ContainsKey('F16')) { $cells['F16'].formula } else { '' }
        formulaPpm = if ($cells.ContainsKey('I33')) { $cells['I33'].formula } elseif ($cells.ContainsKey('I30')) { $cells['I30'].formula } else { '' }
        formulaUgm3 = if ($cells.ContainsKey('J33')) { $cells['J33'].formula } elseif ($cells.ContainsKey('J31')) { $cells['J31'].formula } else { '' }
        lodAmbien = @{
            kons = if ($cells.ContainsKey('C33')) { $cells['C33'].value } else { $null }
            vol = if ($cells.ContainsKey('D33')) { $cells['D33'].value } else { $null }
            fr = if ($cells.ContainsKey('E33')) { $cells['E33'].value } else { $null }
            waktu = if ($cells.ContainsKey('F33')) { $cells['F33'].value } else { $null }
            sk = if ($cells.ContainsKey('G33')) { $cells['G33'].value } else { $null }
            p = if ($cells.ContainsKey('H33')) { $cells['H33'].value } else { $null }
            ppm = if ($cells.ContainsKey('I33')) { $cells['I33'].value } else { $null }
            ugm3 = if ($cells.ContainsKey('J33')) { $cells['J33'].value } else { $null }
        }
        lodLk = @{
            kons = if ($cells.ContainsKey('C40')) { $cells['C40'].value } else { $null }
            vol = if ($cells.ContainsKey('D40')) { $cells['D40'].value } else { $null }
            fr = if ($cells.ContainsKey('E40')) { $cells['E40'].value } else { $null }
            waktu = if ($cells.ContainsKey('F40')) { $cells['F40'].value } else { $null }
            sk = if ($cells.ContainsKey('G40')) { $cells['G40'].value } else { $null }
            p = if ($cells.ContainsKey('H40')) { $cells['H40'].value } else { $null }
            ppm = if ($cells.ContainsKey('I40')) { $cells['I40'].value } else { $null }
            ugm3 = if ($cells.ContainsKey('J40')) { $cells['J40'].value } else { $null }
        }
    }
    $result | ConvertTo-Json -Compress -Depth 6
} finally {
    $zip.Dispose()
}
POWERSHELL;

        $script = str_replace('__PATH__', $escapedPath, $script);
        $scriptPath = tempnam(sys_get_temp_dir(), 'ox-ref-');
        if ($scriptPath === false) {
            throw new RuntimeException('Gagal menyiapkan parser PowerShell OX.');
        }

        $ps1Path = $scriptPath . '.ps1';
        @rename($scriptPath, $ps1Path);
        file_put_contents($ps1Path, $script);

        try {
            $command = $runner
                . ' -NoProfile -NonInteractive -ExecutionPolicy Bypass -File '
                . escapeshellarg($ps1Path);
            $output = shell_exec($command);
        } finally {
            @unlink($ps1Path);
        }

        if (is_string($output)) {
            $output = preg_replace('/^\xEF\xBB\xBF/', '', trim($output));
        }

        $decoded = is_string($output) ? json_decode($output, true) : null;

        if (!is_array($decoded)) {
            throw new RuntimeException('PowerShell parser Excel OX tidak mengembalikan data yang valid.');
        }

        return [
            'sheetName' => trim((string) ($decoded['sheetName'] ?? '')),
            'consIntercept' => $this->extractIntercept(
                trim((string) ($decoded['absFormula'] ?? '')),
                trim((string) ($decoded['formulaCons'] ?? ''))
            ),
            'consSlope' => $this->extractSlope(
                trim((string) ($decoded['absFormula'] ?? '')),
                trim((string) ($decoded['formulaCons'] ?? ''))
            ),
            'factorUgm3' => $this->extractUgm3Factor(trim((string) ($decoded['formulaUgm3'] ?? ''))),
            'formulaCons' => trim((string) ($decoded['formulaCons'] ?? '')),
            'formulaPpm' => trim((string) ($decoded['formulaPpm'] ?? '')),
            'formulaUgm3' => trim((string) ($decoded['formulaUgm3'] ?? '')),
            'lodAmbien' => is_array($decoded['lodAmbien'] ?? null) ? $decoded['lodAmbien'] : [],
            'lodLk' => is_array($decoded['lodLk'] ?? null) ? $decoded['lodLk'] : [],
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
            throw new RuntimeException('Metadata workbook acuan Excel OX tidak lengkap.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if ($workbook === false || $rels === false) {
            throw new RuntimeException('Workbook acuan Excel OX tidak valid.');
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
            if (stripos($name, 'ox') !== false) {
                return [$name, $targets[$relationId]];
            }
        }

        throw new RuntimeException('Sheet acuan OX tidak ditemukan.');
    }

    private function readWorksheetCells(ZipArchive $zip, string $sheetPath, array $sharedStrings): array
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            throw new RuntimeException('Worksheet acuan OX tidak dapat dibaca.');
        }

        $sheet = simplexml_load_string($xml);
        if ($sheet === false) {
            throw new RuntimeException('Worksheet acuan OX tidak valid.');
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

    private function extractSlope(string $formula, string $formulaText): float
    {
        $normalizedFormula = preg_replace('/\s+/', '', strtoupper($formula));
        if (preg_match('/\(([0-9]+(?:\.[0-9]+)?)\*[A-Z]+[0-9]+\)\+([0-9]+(?:\.[0-9]+)?)/', $normalizedFormula, $matches)) {
            return (float) $matches[1];
        }

        $normalizedText = preg_replace('/\s+/', '', str_replace(',', '.', strtolower($formulaText)));
        if (preg_match('/cons=([0-9]+(?:\.[0-9]+)?)\+([0-9]+(?:\.[0-9]+)?)abs/', $normalizedText, $matches)) {
            return (float) $matches[2];
        }

        return self::DEFAULT_REFERENCE['consSlope'];
    }

    private function extractIntercept(string $formula, string $formulaText): float
    {
        $normalizedFormula = preg_replace('/\s+/', '', strtoupper($formula));
        if (preg_match('/\(([0-9]+(?:\.[0-9]+)?)\*[A-Z]+[0-9]+\)\+([0-9]+(?:\.[0-9]+)?)/', $normalizedFormula, $matches)) {
            return (float) $matches[2];
        }

        $normalizedText = preg_replace('/\s+/', '', str_replace(',', '.', strtolower($formulaText)));
        if (preg_match('/cons=([0-9]+(?:\.[0-9]+)?)\+([0-9]+(?:\.[0-9]+)?)abs/', $normalizedText, $matches)) {
            return (float) $matches[1];
        }

        return self::DEFAULT_REFERENCE['consIntercept'];
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
        $reference['consIntercept'] = $this->normalizeFloat($payload['consIntercept'] ?? null, self::DEFAULT_REFERENCE['consIntercept']);
        $reference['consSlope'] = $this->normalizeFloat($payload['consSlope'] ?? null, self::DEFAULT_REFERENCE['consSlope']);
        $reference['factorUgm3'] = $this->normalizeFloat($payload['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $reference['formulaCons'] = trim((string) ($payload['formulaCons'] ?? $reference['formulaCons']));
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
            'kons' => $this->formatDecimal($row['kons'] ?? null, $fallback['kons'], 5),
            'vol' => $this->formatDecimal($row['vol'] ?? null, $fallback['vol'], 1),
            'fr' => $this->formatDecimal($row['fr'] ?? null, $fallback['fr'], 3),
            'waktu' => $this->formatDecimal($row['waktu'] ?? null, $fallback['waktu'], 0),
            'sk' => $this->formatDecimal($row['sk'] ?? null, $fallback['sk'], 1),
            'p' => $this->formatDecimal($row['p'] ?? null, $fallback['p'], 0),
            'ppm' => $this->formatDecimal($row['ppm'] ?? null, $fallback['ppm'], 6),
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
