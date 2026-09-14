<?php

namespace App\Services\FormulaReference;

use App\Models\AbsorbanceFormula;
use App\Models\ParameterLod;
use App\Support\ServiceSystemCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class XyleneExcelReferenceService
{
    private const STORAGE_PATH = 'reference_btx.xlsx';
    private const FALLBACK_EXTERNAL_PATH = 'C:\Users\user\OneDrive\Documents\BTX.xlsx';

    private const DEFAULT_REFERENCE = [
        'yValue' => 4714408.0,
        'xValue' => 0.000000212116,
        'formulaY' => 'Y = 4714408',
        'formulaX' => 'X = 1 / Y',
        'formulaConc' => 'conc = area * x',
        'factorMass' => 1000.0,
        'factorVolume' => 24.45,
        'pressureFactor' => 760.0,
        'denominatorTemperature' => 298.0,
        'molecularWeight' => 106.0,
        'factorUgm3' => 4335.4,
        'formulaPpm' => 'kons * vol * (273 + sk) * 1000 * 24.45 * 760 / (fr * waktu * 298 * 106 * p)',
        'formulaUgm3' => 'ppm * 4335.4',
        'sheetName' => '',
        'sourcePath' => '',
        'sourceMode' => 'fallback',
        'lod' => [
            'kons' => '0.00341',
            'vol' => '1.0',
            'fr' => '0.200',
            'waktu' => '480',
            'sk' => '31.3',
            'p' => '760',
            'ppm' => '0.0084',
            'ugm3' => '36.2647',
        ],
    ];

    public function getReference(): array
    {
        $databaseReference = $this->getDatabaseReference();
        if ($databaseReference !== null) {
            return $this->applyDatabaseLodOverride($databaseReference);
        }

        $workbookPath = $this->resolveWorkbookPath();
        $reference = $workbookPath === null
            ? $this->fallbackReference(null)
            : Cache::rememberForever(
                'prepanalisa:formula-reference:xylene:' . md5($workbookPath . '|' . (string) @filesize($workbookPath) . '|' . (string) @filemtime($workbookPath)),
                function () use ($workbookPath) {
                    try {
                        return $this->normalizeReference($this->extractReferenceViaPowerShell($workbookPath), $workbookPath);
                    } catch (Throwable $e) {
                        report($e);

                        return $this->fallbackReference($workbookPath);
                    }
                }
            );

        return $this->applyDatabaseLodOverride($reference);
    }

    public function calculateConcentration(float $area): float
    {
        $reference = $this->getReference();
        $xValue = $this->normalizeFloat($reference['xValue'] ?? null, self::DEFAULT_REFERENCE['xValue']);

        return $area * $xValue;
    }

    public function calculate(float $kons, float $vol, float $fr, float $waktu, float $sk, float $p): array
    {
        $reference = $this->getReference();
        $factorMass = $this->normalizeFloat($reference['factorMass'] ?? null, self::DEFAULT_REFERENCE['factorMass']);
        $factorVolume = $this->normalizeFloat($reference['factorVolume'] ?? null, self::DEFAULT_REFERENCE['factorVolume']);
        $pressureFactor = $this->normalizeFloat($reference['pressureFactor'] ?? null, self::DEFAULT_REFERENCE['pressureFactor']);
        $denominatorTemperature = $this->normalizeFloat($reference['denominatorTemperature'] ?? null, self::DEFAULT_REFERENCE['denominatorTemperature']);
        $molecularWeight = $this->normalizeFloat($reference['molecularWeight'] ?? null, self::DEFAULT_REFERENCE['molecularWeight']);
        $factorUgm3 = $this->normalizeFloat($reference['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $denominator = $fr * $waktu * $denominatorTemperature * $molecularWeight * $p;

        if ($denominator == 0.0) {
            return ['kadar_ppm' => null, 'kadar_ugm3' => null];
        }

        $ppm = ($kons * $vol * (273 + $sk) * $factorMass * $factorVolume * $pressureFactor) / $denominator;
        $ugm3 = $ppm * $factorUgm3;

        return ['kadar_ppm' => $ppm, 'kadar_ugm3' => $ugm3];
    }

    private function getDatabaseReference(): ?array
    {
        $row = AbsorbanceFormula::query()
            ->where('parameter_key', 'XYLENE')
            ->where('is_active', true)
            ->orderByDesc('effective_date')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if (!$row) {
            return null;
        }

        $reference = self::DEFAULT_REFERENCE;
        $reference['yValue'] = $this->normalizeFloat($row->intercept, self::DEFAULT_REFERENCE['yValue']);
        $reference['xValue'] = $this->normalizeFloat($row->slope, self::DEFAULT_REFERENCE['xValue']);
        $reference['formulaY'] = 'Y = ' . rtrim(rtrim(number_format($reference['yValue'], 12, ',', '.'), '0'), ',');
        $reference['formulaX'] = 'X = ' . rtrim(rtrim(number_format($reference['xValue'], 12, ',', '.'), '0'), ',');
        $reference['formulaConc'] = 'conc = area * x';
        $reference['sourcePath'] = 'absorbance_formulas';
        $reference['sheetName'] = 'kelola_rumus';
        $reference['sourceMode'] = 'database';

        return $reference;
    }

    private function applyDatabaseLodOverride(array $reference): array
    {
        $row = ParameterLod::query()
            ->with('serviceParameter')
            ->where('is_active', true)
            ->whereHas('serviceParameter', function ($query) {
                $query->whereIn('system_code', [
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'XELE'),
                    ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'XELE1'),
                ]);
            })
            ->latest('updated_at')
            ->first();

        if (!$row) {
            return $reference;
        }

        $factorMass = $this->normalizeFloat($reference['factorMass'] ?? null, self::DEFAULT_REFERENCE['factorMass']);
        $factorVolume = $this->normalizeFloat($reference['factorVolume'] ?? null, self::DEFAULT_REFERENCE['factorVolume']);
        $pressureFactor = $this->normalizeFloat($reference['pressureFactor'] ?? null, self::DEFAULT_REFERENCE['pressureFactor']);
        $denominatorTemperature = $this->normalizeFloat($reference['denominatorTemperature'] ?? null, self::DEFAULT_REFERENCE['denominatorTemperature']);
        $molecularWeight = $this->normalizeFloat($reference['molecularWeight'] ?? null, self::DEFAULT_REFERENCE['molecularWeight']);
        $factorUgm3 = $this->normalizeFloat($reference['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);

        $kons = $this->normalizeFloat($row->kons, (float) ($reference['lod']['kons'] ?? 0.00341));
        $vol = $this->normalizeFloat($row->vol, (float) ($reference['lod']['vol'] ?? 1.0));
        $fr = $this->normalizeFloat($row->fr, (float) ($reference['lod']['fr'] ?? 0.2));
        $waktu = $this->normalizeFloat($row->waktu, (float) ($reference['lod']['waktu'] ?? 480));
        $sk = $this->normalizeFloat($row->sk, (float) ($reference['lod']['sk'] ?? 31.3));
        $p = $this->normalizeFloat($row->pm, (float) ($reference['lod']['p'] ?? 760));

        $ppm = null;
        $ugm3 = null;
        $denominator = $fr * $waktu * $denominatorTemperature * $molecularWeight * $p;
        if ($denominator != 0.0) {
            $ppm = ($kons * $vol * (273 + $sk) * $factorMass * $factorVolume * $pressureFactor) / $denominator;
            $ugm3 = $ppm * $factorUgm3;
        }

        $reference['lod'] = [
            'kons' => number_format($kons, 5, '.', ''),
            'vol' => number_format($vol, 1, '.', ''),
            'fr' => number_format($fr, 3, '.', ''),
            'waktu' => number_format($waktu, 0, '.', ''),
            'sk' => number_format($sk, 1, '.', ''),
            'p' => number_format($p, 0, '.', ''),
            'ppm' => $ppm !== null ? number_format($ppm, 4, '.', '') : (string) ($reference['lod']['ppm'] ?? '0.0000'),
            'ugm3' => $ugm3 !== null ? number_format($ugm3, 4, '.', '') : (string) ($reference['lod']['ugm3'] ?? '0.0000'),
        ];

        return $reference;
    }

    private function resolveWorkbookPath(): ?string
    {
        $candidates = [
            env('PREPANALISA_BTX_REFERENCE_PATH'),
            storage_path('app/' . self::STORAGE_PATH),
            storage_path('app/private/' . self::STORAGE_PATH),
            self::FALLBACK_EXTERNAL_PATH,
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

    private function extractReferenceViaPowerShell(string $workbookPath): array
    {
        $runner = 'powershell.exe';
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
    if ($sheet.Name -match 'btx') { $ws = $sheet; break }
  }
  if ($null -eq $ws) { throw 'Sheet BTX tidak ditemukan.' }
  $result = @{
    sheetName = [string]$ws.Name
    yValue = [string]$ws.Cells.Item(24,11).Value2
    xValue = [string]$ws.Cells.Item(25,11).Value2
    formulaY = 'Y = ' + [string]$ws.Cells.Item(24,11).Value2
    formulaX = [string]$ws.Cells.Item(25,11).Formula
    formulaConc = [string]$ws.Cells.Item(26,11).Formula
    formulaPpm = [string]$ws.Cells.Item(40,13).Formula
    lod = @{
      kons = [string]$ws.Cells.Item(40,5).Value2
      vol = [string]$ws.Cells.Item(40,6).Value2
      fr = [string]$ws.Cells.Item(40,7).Value2
      waktu = [string]$ws.Cells.Item(40,8).Value2
      sk = [string]$ws.Cells.Item(40,9).Value2
      p = [string]$ws.Cells.Item(40,10).Value2
      ppm = [string]$ws.Cells.Item(40,13).Value2
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
        $scriptPath = tempnam(sys_get_temp_dir(), 'xylene-ref-');
        if ($scriptPath === false) {
            throw new RuntimeException('Gagal menyiapkan parser Xylene.');
        }

        $ps1Path = $scriptPath . '.ps1';
        @rename($scriptPath, $ps1Path);
        file_put_contents($ps1Path, $script);

        try {
            $output = shell_exec($runner . ' -NoProfile -NonInteractive -ExecutionPolicy Bypass -File ' . escapeshellarg($ps1Path));
        } finally {
            @unlink($ps1Path);
        }

        $decoded = is_string($output) ? json_decode(trim($output), true) : null;
        if (!is_array($decoded)) {
            throw new RuntimeException('Output parser Xylene tidak valid.');
        }

        return [
            'sheetName' => trim((string) ($decoded['sheetName'] ?? '')),
            'yValue' => $this->toNullableFloat($decoded['yValue'] ?? null),
            'xValue' => $this->toNullableFloat($decoded['xValue'] ?? null),
            'formulaY' => trim((string) ($decoded['formulaY'] ?? '')),
            'formulaX' => trim((string) ($decoded['formulaX'] ?? '')),
            'formulaConc' => trim((string) ($decoded['formulaConc'] ?? '')),
            'factorMass' => 1000.0,
            'factorVolume' => 24.45,
            'pressureFactor' => 760.0,
            'denominatorTemperature' => 298.0,
            'molecularWeight' => 106.0,
            'factorUgm3' => 4335.4,
            'formulaPpm' => trim((string) ($decoded['formulaPpm'] ?? self::DEFAULT_REFERENCE['formulaPpm'])),
            'formulaUgm3' => self::DEFAULT_REFERENCE['formulaUgm3'],
            'lod' => is_array($decoded['lod'] ?? null) ? $decoded['lod'] : [],
        ];
    }

    private function normalizeReference(array $payload, string $workbookPath): array
    {
        $reference = self::DEFAULT_REFERENCE;
        $reference['yValue'] = $this->normalizeFloat($payload['yValue'] ?? null, self::DEFAULT_REFERENCE['yValue']);
        $reference['xValue'] = $this->normalizeFloat($payload['xValue'] ?? null, self::DEFAULT_REFERENCE['xValue']);
        $reference['formulaY'] = trim((string) ($payload['formulaY'] ?? $reference['formulaY']));
        $reference['formulaX'] = trim((string) ($payload['formulaX'] ?? $reference['formulaX']));
        $reference['formulaConc'] = trim((string) ($payload['formulaConc'] ?? $reference['formulaConc']));
        $reference['formulaPpm'] = trim((string) ($payload['formulaPpm'] ?? $reference['formulaPpm']));
        $reference['formulaUgm3'] = trim((string) ($payload['formulaUgm3'] ?? $reference['formulaUgm3']));
        $reference['sheetName'] = trim((string) ($payload['sheetName'] ?? ''));
        $reference['sourcePath'] = $workbookPath;
        $reference['sourceMode'] = 'excel';
        $reference['lod'] = [
            'kons' => $this->formatDecimal(data_get($payload, 'lod.kons'), self::DEFAULT_REFERENCE['lod']['kons'], 5),
            'vol' => $this->formatDecimal(data_get($payload, 'lod.vol'), self::DEFAULT_REFERENCE['lod']['vol'], 1),
            'fr' => $this->formatDecimal(data_get($payload, 'lod.fr'), self::DEFAULT_REFERENCE['lod']['fr'], 3),
            'waktu' => $this->formatDecimal(data_get($payload, 'lod.waktu'), self::DEFAULT_REFERENCE['lod']['waktu'], 0),
            'sk' => $this->formatDecimal(data_get($payload, 'lod.sk'), self::DEFAULT_REFERENCE['lod']['sk'], 1),
            'p' => $this->formatDecimal(data_get($payload, 'lod.p'), self::DEFAULT_REFERENCE['lod']['p'], 0),
            'ppm' => $this->formatDecimal(data_get($payload, 'lod.ppm'), self::DEFAULT_REFERENCE['lod']['ppm'], 4),
            'ugm3' => self::DEFAULT_REFERENCE['lod']['ugm3'],
        ];

        return $reference;
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
        return $number === null ? $fallback : number_format($number, $precision, '.', '');
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
