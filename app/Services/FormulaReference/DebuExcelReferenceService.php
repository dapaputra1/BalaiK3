<?php

namespace App\Services\FormulaReference;

use App\Models\ParameterLod;
use App\Support\ServiceSystemCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class DebuExcelReferenceService
{
    private const STORAGE_PATH = 'reference_debu.xlsx';
    private const FALLBACK_EXTERNAL_PATH = 'C:\Users\user\OneDrive\Documents\Debu.xlsx';

    private const DEFAULT_REFERENCE = [
        'berat' => '0.0001',
        'fr' => '500.00',
        'waktu' => '60',
        'sk' => '25.0',
        'p' => '760',
        'formulaSelisih' => 'akhir - awal',
        'formulaBerat' => 'selisih - rata_rata_blanko',
        'formulaKadar' => 'berat * 1000000 * (273 + sk) * 760 / (waktu * fr * 298 * p)',
        'sheetName' => '',
        'sourcePath' => '',
        'sourceMode' => 'fallback',
        'constants' => [
            'massFactor' => 1000000.0,
            'temperatureOffset' => 273.0,
            'pressureFactor' => 760.0,
            'denominatorTemperature' => 298.0,
        ],
        'sample' => [
            'berat' => '0.0090',
            'fr' => '500',
            'waktu' => '60',
            'sk' => '32.0',
            'p' => '758',
            'kadar' => '0.3079',
        ],
    ];

    private const TOTAL_DUST_REFERENCE = [
        'berat' => '0.0001',
        'fr' => '10.00',
        'waktu' => '60',
        'sk' => '25.0',
        'p' => '760',
        'sample' => [
            'berat' => '0.0004833',
            'fr' => '10',
            'waktu' => '60',
            'sk' => '25.4',
            'p' => '758',
            'kadar' => '0.8088',
        ],
    ];

    public function getReference(?string $systemCode = null): array
    {
        $databaseReference = $this->databaseReference($systemCode);
        if ($databaseReference !== null) {
            return $databaseReference;
        }

        $workbookPath = $this->resolveWorkbookPath();
        if ($workbookPath === null) {
            return $this->fallbackReference(null, $systemCode);
        }

        $signature = md5($workbookPath . '|' . (string) @filesize($workbookPath) . '|' . (string) @filemtime($workbookPath) . '|' . (string) $systemCode);

        return Cache::rememberForever('prepanalisa:formula-reference:debu-ambien:' . $signature, function () use ($workbookPath, $systemCode) {
            try {
                return $this->normalizeReference($this->extractReference($workbookPath), $workbookPath, $systemCode);
            } catch (Throwable $e) {
                report($e);

                return $this->fallbackReference($workbookPath, $systemCode);
            }
        });
    }

    private function databaseReference(?string $systemCode = null): ?array
    {
        $query = ParameterLod::query()
            ->with('serviceParameter')
            ->where('is_active', true);

        if ($systemCode !== null && $systemCode !== '') {
            $query->whereHas('serviceParameter', function ($query) use ($systemCode) {
                $query->where('system_code', $systemCode);
            });
        } else {
            $query->whereHas('serviceParameter', function ($query) {
                $query->whereIn('system_code', [
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'DPM10'),
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'DPM25'),
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'DPM11'),
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'DPM21'),
                ]);
            });
        }

        $lod = $query
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if (!$lod) {
            return null;
        }

        $reference = $this->baseReference($systemCode);
        $reference['berat'] = $this->formatDecimal($lod->kons, $reference['berat'], 4);
        $reference['fr'] = $this->formatDecimal($lod->fr, $reference['fr'], 2);
        $reference['waktu'] = $this->formatDecimal($lod->waktu, $reference['waktu'], 0);
        $reference['sk'] = $this->formatDecimal($lod->sk, $reference['sk'], 1);
        $reference['p'] = $this->formatDecimal($lod->pm, $reference['p'], 0);
        $reference['sourcePath'] = 'parameter_lods:' . $lod->id;
        $reference['sourceMode'] = 'database';

        return $reference;
    }

    public function calculate(float $berat, float $fr, float $waktu, float $sk, float $p, ?string $systemCode = null): array
    {
        $reference = $this->getReference($systemCode);
        $constants = is_array($reference['constants'] ?? null) ? $reference['constants'] : [];

        $massFactor = $this->normalizeFloat($constants['massFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['massFactor']);
        $temperatureOffset = $this->normalizeFloat($constants['temperatureOffset'] ?? null, self::DEFAULT_REFERENCE['constants']['temperatureOffset']);
        $pressureFactor = $this->normalizeFloat($constants['pressureFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['pressureFactor']);
        $denominatorTemperature = $this->normalizeFloat($constants['denominatorTemperature'] ?? null, self::DEFAULT_REFERENCE['constants']['denominatorTemperature']);

        $denominator = $waktu * $fr * $denominatorTemperature * $p;
        if ($denominator == 0.0) {
            return ['kadar_mgm3' => null];
        }

        return [
            'kadar_mgm3' => ($berat * $massFactor * ($temperatureOffset + $sk) * $pressureFactor) / $denominator,
        ];
    }

    private function resolveWorkbookPath(): ?string
    {
        $candidates = [
            env('PREPANALISA_DEBU_REFERENCE_PATH'),
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
        $extension = strtolower(pathinfo($workbookPath, PATHINFO_EXTENSION));
        if ($extension === 'xls') {
            return $this->extractReferenceViaPowerShell($workbookPath);
        }

        return $this->extractReferenceFromXlsx($workbookPath);
    }

    private function extractReferenceFromXlsx(string $workbookPath): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('ZipArchive tidak tersedia untuk membaca acuan Excel Debu.');
        }

        $zip = new ZipArchive();
        if ($zip->open($workbookPath) !== true) {
            throw new RuntimeException('File acuan Excel Debu tidak dapat dibuka.');
        }

        try {
            $sharedStrings = $this->readSharedStrings($zip);
            [$sheetName, $sheetPath] = $this->resolveWorksheet($zip);
            $cells = $this->readWorksheetCells($zip, $sheetPath, $sharedStrings);

            return [
                'sheetName' => $sheetName,
                'formulaSelisih' => $cells['F16']['formula'] ?? '',
                'formulaBerat' => $cells['G16']['formula'] ?? '',
                'formulaKadar' => $cells['H35']['formula'] ?? ($cells['H33']['formula'] ?? ($cells['H31']['formula'] ?? '')),
                'mdl' => [
                    'berat' => $cells['C35']['value'] ?? ($cells['C33']['value'] ?? null),
                    'fr' => $cells['D35']['value'] ?? ($cells['D33']['value'] ?? null),
                    'waktu' => $cells['E35']['value'] ?? ($cells['E33']['value'] ?? null),
                    'sk' => $cells['F35']['value'] ?? ($cells['F33']['value'] ?? null),
                    'p' => $cells['G35']['value'] ?? ($cells['G33']['value'] ?? null),
                    'kadar' => $cells['H35']['value'] ?? ($cells['H33']['value'] ?? null),
                ],
                'sample' => [
                    'berat' => $cells['C31']['value'] ?? ($cells['C30']['value'] ?? null),
                    'fr' => $cells['D31']['value'] ?? ($cells['D30']['value'] ?? null),
                    'waktu' => $cells['E31']['value'] ?? ($cells['E30']['value'] ?? null),
                    'sk' => $cells['F31']['value'] ?? ($cells['F30']['value'] ?? null),
                    'p' => $cells['G31']['value'] ?? ($cells['G30']['value'] ?? null),
                    'kadar' => $cells['H31']['value'] ?? ($cells['H30']['value'] ?? null),
                ],
                'constants' => $this->extractFormulaConstants($cells['H35']['formula'] ?? ($cells['H33']['formula'] ?? ($cells['H31']['formula'] ?? ''))),
            ];
        } finally {
            $zip->close();
        }
    }

    private function extractReferenceViaPowerShell(string $workbookPath): array
    {
        $escapedPath = str_replace("'", "''", $workbookPath);
        $script = <<<'POWERSHELL'
$path = '__PATH__'
$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$wb = $excel.Workbooks.Open($path, 0, $true)
try {
  $ws = $null
  foreach ($sheet in $wb.Worksheets) {
    $name = [string]$sheet.Name
    if ($name -notmatch 'debu') { continue }
    $marker = ([string]$sheet.Cells.Item(24,1).Text) + ' ' + ([string]$sheet.Cells.Item(7,4).Text) + ' ' + ([string]$sheet.Cells.Item(51,1).Text)
    if ($name -match '\(2\)' -or $marker -match 'LINGKUNGAN KERJA|Lk') { $ws = $sheet; break }
    if ($null -eq $ws) { $ws = $sheet }
  }
  if ($null -eq $ws) { throw 'Sheet Debu tidak ditemukan.' }
  $result = @{
    sheetName = [string]$ws.Name
    formulaSelisih = [string]$ws.Cells.Item(16,6).Formula
    formulaBerat = [string]$ws.Cells.Item(16,7).Formula
    formulaKadar = [string]$ws.Cells.Item(35,8).Formula
    mdl = @{
      berat = [string]$ws.Cells.Item(35,3).Value2
      fr = [string]$ws.Cells.Item(35,4).Value2
      waktu = [string]$ws.Cells.Item(35,5).Value2
      sk = [string]$ws.Cells.Item(35,6).Value2
      p = [string]$ws.Cells.Item(35,7).Value2
      kadar = [string]$ws.Cells.Item(35,8).Value2
    }
    sample = @{
      berat = [string]$ws.Cells.Item(31,3).Value2
      fr = [string]$ws.Cells.Item(31,4).Value2
      waktu = [string]$ws.Cells.Item(31,5).Value2
      sk = [string]$ws.Cells.Item(31,6).Value2
      p = [string]$ws.Cells.Item(31,7).Value2
      kadar = [string]$ws.Cells.Item(31,8).Value2
    }
  }
  $result | ConvertTo-Json -Compress -Depth 5
} finally {
  $wb.Close($false)
  $excel.Quit()
  [System.Runtime.Interopservices.Marshal]::ReleaseComObject($wb) | Out-Null
  [System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
  [gc]::Collect(); [gc]::WaitForPendingFinalizers()
}
POWERSHELL;

        $script = str_replace('__PATH__', $escapedPath, $script);
        $scriptPath = tempnam(sys_get_temp_dir(), 'debu-ref-');
        if ($scriptPath === false) {
            throw new RuntimeException('Gagal menyiapkan parser Debu.');
        }

        $ps1Path = $scriptPath . '.ps1';
        @rename($scriptPath, $ps1Path);
        file_put_contents($ps1Path, $script);

        try {
            $output = shell_exec('powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File ' . escapeshellarg($ps1Path));
        } finally {
            @unlink($ps1Path);
        }

        $decoded = is_string($output) ? json_decode(trim($output), true) : null;
        if (!is_array($decoded)) {
            throw new RuntimeException('Output parser Excel Debu tidak valid.');
        }

        $decoded['constants'] = $this->extractFormulaConstants((string) ($decoded['formulaKadar'] ?? ''));

        return $decoded;
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
            throw new RuntimeException('Metadata workbook acuan Excel Debu tidak lengkap.');
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if ($workbook === false || $rels === false) {
            throw new RuntimeException('Workbook acuan Excel Debu tidak valid.');
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
            if (stripos($name, 'debu') !== false && str_contains($name, '(2)')) {
                return [$name, $targets[$relationId]];
            }
        }

        foreach ($workbook->xpath('//a:sheets/a:sheet') ?: [] as $sheet) {
            $name = trim((string) $sheet['name']);
            $relationId = (string) $sheet->attributes('r', true)->id;
            if ($name === '' || $relationId === '' || !isset($targets[$relationId])) {
                continue;
            }
            if (stripos($name, 'debu') !== false) {
                return [$name, $targets[$relationId]];
            }
        }

        foreach ($workbook->xpath('//a:sheets/a:sheet') ?: [] as $sheet) {
            $name = trim((string) $sheet['name']);
            $relationId = (string) $sheet->attributes('r', true)->id;
            if ($name !== '' && $relationId !== '' && isset($targets[$relationId])) {
                return [$name, $targets[$relationId]];
            }
        }

        throw new RuntimeException('Sheet acuan Debu tidak ditemukan.');
    }

    private function readWorksheetCells(ZipArchive $zip, string $sheetPath, array $sharedStrings): array
    {
        $xml = $zip->getFromName($sheetPath);
        if ($xml === false) {
            throw new RuntimeException('Worksheet acuan Debu tidak dapat dibaca.');
        }

        $sheet = simplexml_load_string($xml);
        if ($sheet === false) {
            throw new RuntimeException('Worksheet acuan Debu tidak valid.');
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

    private function extractFormulaConstants(string $formula): array
    {
        $defaults = self::DEFAULT_REFERENCE['constants'];
        $normalized = preg_replace('/\s+/', '', strtoupper($formula));
        if (!$normalized) {
            return $defaults;
        }

        $massFactor = $defaults['massFactor'];
        $temperatureOffset = $defaults['temperatureOffset'];
        $pressureFactor = $defaults['pressureFactor'];
        $denominatorTemperature = $defaults['denominatorTemperature'];

        if (preg_match('/\*([0-9]+(?:\.[0-9]+)?)\*\(([0-9]+(?:\.[0-9]+)?)\+[A-Z0-9]+\)\*([0-9]+(?:\.[0-9]+)?)/', $normalized, $matches)) {
            $massFactor = (float) $matches[1];
            $temperatureOffset = (float) $matches[2];
            $pressureFactor = (float) $matches[3];
        }

        if (preg_match('/\/\([A-Z0-9]+\*[A-Z0-9]+\*([0-9]+(?:\.[0-9]+)?)\*[A-Z0-9]+\)/', $normalized, $matches)) {
            $denominatorTemperature = (float) $matches[1];
        }

        return [
            'massFactor' => $massFactor,
            'temperatureOffset' => $temperatureOffset,
            'pressureFactor' => $pressureFactor,
            'denominatorTemperature' => $denominatorTemperature,
        ];
    }

    private function normalizeReference(array $payload, string $workbookPath, ?string $systemCode = null): array
    {
        $reference = $this->baseReference($systemCode);
        $mdl = is_array($payload['mdl'] ?? null) ? $payload['mdl'] : [];
        $sample = is_array($payload['sample'] ?? null) ? $payload['sample'] : [];
        $constants = is_array($payload['constants'] ?? null) ? $payload['constants'] : [];

        $reference['berat'] = $this->formatDecimal($mdl['berat'] ?? null, $reference['berat'], 4);
        $reference['fr'] = $this->formatDecimal($mdl['fr'] ?? null, $reference['fr'], 2);
        $reference['waktu'] = $this->formatDecimal($mdl['waktu'] ?? null, $reference['waktu'], 0);
        $reference['sk'] = $this->formatDecimal($mdl['sk'] ?? null, $reference['sk'], 1);
        $reference['p'] = $this->formatDecimal($mdl['p'] ?? null, $reference['p'], 0);
        $reference['formulaSelisih'] = trim((string) ($payload['formulaSelisih'] ?? $reference['formulaSelisih']));
        $reference['formulaBerat'] = trim((string) ($payload['formulaBerat'] ?? $reference['formulaBerat']));
        $reference['formulaKadar'] = trim((string) ($payload['formulaKadar'] ?? $reference['formulaKadar']));
        $reference['sheetName'] = trim((string) ($payload['sheetName'] ?? ''));
        $reference['sourcePath'] = $workbookPath;
        $reference['sourceMode'] = 'excel';
        $reference['constants'] = [
            'massFactor' => $this->normalizeFloat($constants['massFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['massFactor']),
            'temperatureOffset' => $this->normalizeFloat($constants['temperatureOffset'] ?? null, self::DEFAULT_REFERENCE['constants']['temperatureOffset']),
            'pressureFactor' => $this->normalizeFloat($constants['pressureFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['pressureFactor']),
            'denominatorTemperature' => $this->normalizeFloat($constants['denominatorTemperature'] ?? null, self::DEFAULT_REFERENCE['constants']['denominatorTemperature']),
        ];
        $reference['sample'] = [
            'berat' => $this->formatDecimal($sample['berat'] ?? null, $reference['sample']['berat'], 4),
            'fr' => $this->formatDecimal($sample['fr'] ?? null, $reference['sample']['fr'], 0),
            'waktu' => $this->formatDecimal($sample['waktu'] ?? null, $reference['sample']['waktu'], 0),
            'sk' => $this->formatDecimal($sample['sk'] ?? null, $reference['sample']['sk'], 1),
            'p' => $this->formatDecimal($sample['p'] ?? null, $reference['sample']['p'], 0),
            'kadar' => $this->formatDecimal($sample['kadar'] ?? null, $reference['sample']['kadar'], 4),
        ];

        return $reference;
    }

    private function fallbackReference(?string $workbookPath, ?string $systemCode = null): array
    {
        $reference = $this->baseReference($systemCode);
        $reference['sourcePath'] = $workbookPath ?? '';

        return $reference;
    }

    private function baseReference(?string $systemCode = null): array
    {
        if (!$this->isTotalDust($systemCode)) {
            return self::DEFAULT_REFERENCE;
        }

        $reference = array_replace_recursive(self::DEFAULT_REFERENCE, self::TOTAL_DUST_REFERENCE);
        $reference['sourceMode'] = 'fallback';

        return $reference;
    }

    private function isTotalDust(?string $systemCode): bool
    {
        return $systemCode === ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDTLR');
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
