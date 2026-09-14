<?php

namespace App\Services\FormulaReference;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class HclEmisiExcelReferenceService
{
    private const STORAGE_PATH = 'reference_hcl_emisi.xlsx';
    private const FALLBACK_EXTERNAL_PATH = 'C:\Users\user\OneDrive\Documents\HCL.xlsx';
    private const SCRIPT_PATH = 'scripts/hcl_emisi_reference_extract.ps1';

    private const DEFAULT_REFERENCE = [
        'formulaKadar' => '(kons * vol * 1.0282 * 1000 * (273 + tm) * 760) / (waktu * fr * 5 * 298 * p)',
        'sheetName' => '',
        'sourcePath' => '',
        'sourceMode' => 'fallback',
        'constants' => [
            'correctionFactor' => 1.0282,
            'massFactor' => 1000.0,
            'temperatureOffset' => 273.0,
            'pressureFactor' => 760.0,
            'divisorFactor' => 5.0,
            'denominatorTemperature' => 298.0,
        ],
        'lod' => [
            'kons' => '0.0017',
            'vol' => '100.0',
            'fr' => '2.000',
            'waktu' => '20',
            'tm' => '25.0',
            'p' => '760',
            'kadar' => '0.8740',
        ],
        'sample' => [
            'kons' => '0.0021',
            'vol' => '94.0',
            'fr' => '2.000',
            'waktu' => '5',
            'tm' => '36.2',
            'p' => '753',
            'kadar' => '4.2511',
        ],
    ];

    public function getReference(): array
    {
        $workbookPath = $this->resolveWorkbookPath();
        if ($workbookPath === null) {
            return $this->fallbackReference(null);
        }

        $signature = md5($workbookPath . '|' . (string) @filesize($workbookPath) . '|' . (string) @filemtime($workbookPath));

        return Cache::rememberForever('prepanalisa:formula-reference:hcl-emisi:' . $signature, function () use ($workbookPath) {
            try {
                return $this->normalizeReference($this->extractReference($workbookPath), $workbookPath);
            } catch (Throwable $e) {
                report($e);

                return $this->fallbackReference($workbookPath);
            }
        });
    }

    public function calculate(float $kons, float $vol, float $fr, float $waktu, float $tm, float $p): array
    {
        $reference = $this->getReference();
        $constants = is_array($reference['constants'] ?? null) ? $reference['constants'] : [];

        $correctionFactor = $this->normalizeFloat($constants['correctionFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['correctionFactor']);
        $massFactor = $this->normalizeFloat($constants['massFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['massFactor']);
        $temperatureOffset = $this->normalizeFloat($constants['temperatureOffset'] ?? null, self::DEFAULT_REFERENCE['constants']['temperatureOffset']);
        $pressureFactor = $this->normalizeFloat($constants['pressureFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['pressureFactor']);
        $divisorFactor = $this->normalizeFloat($constants['divisorFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['divisorFactor']);
        $denominatorTemperature = $this->normalizeFloat($constants['denominatorTemperature'] ?? null, self::DEFAULT_REFERENCE['constants']['denominatorTemperature']);

        $denominator = $waktu * $fr * $divisorFactor * $denominatorTemperature * $p;
        if ($denominator == 0.0) {
            return ['kadar_mgm3' => null];
        }

        $kadar = ($kons * $vol * $correctionFactor * $massFactor * ($temperatureOffset + $tm) * $pressureFactor) / $denominator;

        return ['kadar_mgm3' => $kadar];
    }

    private function resolveWorkbookPath(): ?string
    {
        $candidates = [
            env('PREPANALISA_HCL_EMISI_REFERENCE_PATH'),
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
        $scriptPath = base_path(self::SCRIPT_PATH);
        if (!is_file($scriptPath)) {
            throw new RuntimeException('Script extractor acuan HCL Emisi tidak ditemukan.');
        }

        $process = new Process([
            'powershell',
            '-NoProfile',
            '-ExecutionPolicy',
            'Bypass',
            '-File',
            $scriptPath,
            '-Path',
            $workbookPath,
        ]);
        $process->setTimeout(90);
        $process->run();

        if (!$process->isSuccessful()) {
            $message = trim($process->getErrorOutput() ?: $process->getOutput());
            throw new RuntimeException($message !== '' ? $message : 'Extractor acuan HCL Emisi gagal dijalankan.');
        }

        $payload = json_decode(trim($process->getOutput()), true);
        if (!is_array($payload)) {
            throw new RuntimeException('Output extractor acuan HCL Emisi tidak valid.');
        }

        return $payload;
    }

    private function normalizeReference(array $payload, string $workbookPath): array
    {
        $reference = self::DEFAULT_REFERENCE;
        $lod = is_array($payload['lod'] ?? null) ? $payload['lod'] : [];
        $sample = is_array($payload['sample'] ?? null) ? $payload['sample'] : [];
        $constants = is_array($payload['constants'] ?? null) ? $payload['constants'] : [];

        $reference['formulaKadar'] = trim((string) ($payload['formulaKadar'] ?? $reference['formulaKadar']));
        $reference['sheetName'] = trim((string) ($payload['sheetName'] ?? ''));
        $reference['sourcePath'] = $workbookPath;
        $reference['sourceMode'] = 'excel';
        $reference['constants'] = [
            'correctionFactor' => $this->normalizeFloat($constants['correctionFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['correctionFactor']),
            'massFactor' => $this->normalizeFloat($constants['massFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['massFactor']),
            'temperatureOffset' => $this->normalizeFloat($constants['temperatureOffset'] ?? null, self::DEFAULT_REFERENCE['constants']['temperatureOffset']),
            'pressureFactor' => $this->normalizeFloat($constants['pressureFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['pressureFactor']),
            'divisorFactor' => $this->normalizeFloat($constants['divisorFactor'] ?? null, self::DEFAULT_REFERENCE['constants']['divisorFactor']),
            'denominatorTemperature' => $this->normalizeFloat($constants['denominatorTemperature'] ?? null, self::DEFAULT_REFERENCE['constants']['denominatorTemperature']),
        ];
        $reference['lod'] = [
            'kons' => $this->formatDecimal($lod['kons'] ?? null, self::DEFAULT_REFERENCE['lod']['kons'], 4),
            'vol' => $this->formatDecimal($lod['vol'] ?? null, self::DEFAULT_REFERENCE['lod']['vol'], 1),
            'fr' => $this->formatDecimal($lod['fr'] ?? null, self::DEFAULT_REFERENCE['lod']['fr'], 3),
            'waktu' => $this->formatDecimal($lod['waktu'] ?? null, self::DEFAULT_REFERENCE['lod']['waktu'], 0),
            'tm' => $this->formatDecimal($lod['tm'] ?? null, self::DEFAULT_REFERENCE['lod']['tm'], 1),
            'p' => $this->formatDecimal($lod['p'] ?? null, self::DEFAULT_REFERENCE['lod']['p'], 0),
            'kadar' => $this->formatDecimal($lod['kadar'] ?? null, self::DEFAULT_REFERENCE['lod']['kadar'], 4),
        ];
        $reference['sample'] = [
            'kons' => $this->formatDecimal($sample['kons'] ?? null, self::DEFAULT_REFERENCE['sample']['kons'], 4),
            'vol' => $this->formatDecimal($sample['vol'] ?? null, self::DEFAULT_REFERENCE['sample']['vol'], 1),
            'fr' => $this->formatDecimal($sample['fr'] ?? null, self::DEFAULT_REFERENCE['sample']['fr'], 3),
            'waktu' => $this->formatDecimal($sample['waktu'] ?? null, self::DEFAULT_REFERENCE['sample']['waktu'], 0),
            'tm' => $this->formatDecimal($sample['tm'] ?? null, self::DEFAULT_REFERENCE['sample']['tm'], 1),
            'p' => $this->formatDecimal($sample['p'] ?? null, self::DEFAULT_REFERENCE['sample']['p'], 0),
            'kadar' => $this->formatDecimal($sample['kadar'] ?? null, self::DEFAULT_REFERENCE['sample']['kadar'], 4),
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
