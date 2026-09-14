<?php

namespace App\Services\FormulaReference;

use App\Models\ParameterLod;
use App\Support\ServiceSystemCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class So2AmbienExcelReferenceService
{
    private const STORAGE_PATH = 'reference_so2_ambien.xls';
    private const SCRIPT_PATH = 'scripts/so2_ambien_reference_extract.ps1';

    private const DEFAULT_REFERENCE = [
        'kons' => '0.5218',
        'vol' => '10.0',
        'waktu' => '60',
        'fr' => '1.000',
        'sk' => '25.0',
        'pm' => '760',
        'factorPpm' => 0.382,
        'factorUgm3' => 2617.6,
        'formulaPpm' => '',
        'formulaUgm3' => '',
        'sheetName' => '',
        'sourcePath' => '',
        'sourceMode' => 'fallback',
        'sample' => [
            'kons' => '0.0360',
            'vol' => '10.0',
            'waktu' => '60',
            'fr' => '1.000',
            'sk' => '29.0',
            'pm' => '758',
            'ppm' => '0.0002',
            'ugm3' => '0.6',
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

        return Cache::rememberForever('prepanalisa:formula-reference:so2-ambien:' . $signature, function () use ($workbookPath) {
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
        $lod = ParameterLod::query()
            ->with('serviceParameter.category')
            ->where('is_active', true)
            ->whereHas('serviceParameter', function ($query) {
                $query->where('system_code', ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'SO21'));
            })
            ->first();

        if (!$lod) {
            return null;
        }

        $reference = self::DEFAULT_REFERENCE;
        $reference['kons'] = $this->formatDecimal($lod->kons, self::DEFAULT_REFERENCE['kons'], 4);
        $reference['vol'] = $this->formatDecimal($lod->vol, self::DEFAULT_REFERENCE['vol'], 1);
        $reference['waktu'] = $this->formatDecimal($lod->waktu, self::DEFAULT_REFERENCE['waktu'], 0);
        $reference['fr'] = $this->formatDecimal($lod->fr, self::DEFAULT_REFERENCE['fr'], 3);
        $reference['sk'] = $this->formatDecimal($lod->sk, self::DEFAULT_REFERENCE['sk'], 1);
        $reference['pm'] = $this->formatDecimal($lod->pm, self::DEFAULT_REFERENCE['pm'], 0);
        $reference['factorPpm'] = $this->normalizeFloat($lod->factor_ppm, self::DEFAULT_REFERENCE['factorPpm']);
        $reference['factorUgm3'] = $this->normalizeFloat($lod->factor_ugm3, self::DEFAULT_REFERENCE['factorUgm3']);
        $reference['sourcePath'] = 'parameter_lods:' . $lod->id;
        $reference['sourceMode'] = 'database';
        $reference['sample'] = [
            'kons' => $this->formatDecimal($lod->sample_kons, self::DEFAULT_REFERENCE['sample']['kons'], 4),
            'vol' => $this->formatDecimal($lod->sample_vol, self::DEFAULT_REFERENCE['sample']['vol'], 1),
            'waktu' => $this->formatDecimal($lod->sample_waktu, self::DEFAULT_REFERENCE['sample']['waktu'], 0),
            'fr' => $this->formatDecimal($lod->sample_fr, self::DEFAULT_REFERENCE['sample']['fr'], 3),
            'sk' => $this->formatDecimal($lod->sample_sk, self::DEFAULT_REFERENCE['sample']['sk'], 1),
            'pm' => $this->formatDecimal($lod->sample_pm, self::DEFAULT_REFERENCE['sample']['pm'], 0),
            'ppm' => $this->formatDecimal($lod->sample_ppm, self::DEFAULT_REFERENCE['sample']['ppm'], 4),
            'ugm3' => $this->formatDecimal($lod->sample_ugm3, self::DEFAULT_REFERENCE['sample']['ugm3'], 1),
        ];

        return $reference;
    }

    public function calculate(float $kons, float $vol, float $fr, float $waktu, float $sk, float $pm): array
    {
        $reference = $this->getReference();
        $factorPpm = $this->normalizeFloat($reference['factorPpm'] ?? null, self::DEFAULT_REFERENCE['factorPpm']);
        $factorUgm3 = $this->normalizeFloat($reference['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $denominator = $fr * $waktu * 298 * $pm;

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
            storage_path('app/' . self::STORAGE_PATH),
            storage_path('app/private/' . self::STORAGE_PATH),
        ];

        try {
            $candidates[] = Storage::disk('local')->path(self::STORAGE_PATH);
        } catch (Throwable) {
            // Abaikan jika adapter disk tidak bisa membentuk path absolut.
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
            throw new RuntimeException('Script extractor acuan SO2 Ambien tidak ditemukan.');
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
            throw new RuntimeException($message !== '' ? $message : 'Extractor acuan SO2 Ambien gagal dijalankan.');
        }

        $payload = json_decode(trim($process->getOutput()), true);
        if (!is_array($payload)) {
            throw new RuntimeException('Output extractor acuan SO2 Ambien tidak valid.');
        }

        return $payload;
    }

    private function normalizeReference(array $payload, string $workbookPath): array
    {
        $reference = self::DEFAULT_REFERENCE;
        $mdl = is_array($payload['mdl'] ?? null) ? $payload['mdl'] : [];
        $sample = is_array($payload['sample'] ?? null) ? $payload['sample'] : [];

        $reference['kons'] = $this->formatDecimal($mdl['kons'] ?? null, self::DEFAULT_REFERENCE['kons'], 4);
        $reference['vol'] = $this->formatDecimal($mdl['vol'] ?? null, self::DEFAULT_REFERENCE['vol'], 1);
        $reference['waktu'] = $this->formatDecimal($mdl['waktu'] ?? null, self::DEFAULT_REFERENCE['waktu'], 0);
        $reference['fr'] = $this->formatDecimal($mdl['fr'] ?? null, self::DEFAULT_REFERENCE['fr'], 3);
        $reference['sk'] = $this->formatDecimal($mdl['sk'] ?? null, self::DEFAULT_REFERENCE['sk'], 1);
        $reference['pm'] = $this->formatDecimal($mdl['pm'] ?? null, self::DEFAULT_REFERENCE['pm'], 0);
        $reference['factorPpm'] = $this->normalizeFloat($payload['factorPpm'] ?? null, self::DEFAULT_REFERENCE['factorPpm']);
        $reference['factorUgm3'] = $this->normalizeFloat($payload['factorUgm3'] ?? null, self::DEFAULT_REFERENCE['factorUgm3']);
        $reference['formulaPpm'] = trim((string) ($payload['formulaPpm'] ?? ''));
        $reference['formulaUgm3'] = trim((string) ($payload['formulaUgm3'] ?? ''));
        $reference['sheetName'] = trim((string) ($payload['sheetName'] ?? ''));
        $reference['sourcePath'] = $workbookPath;
        $reference['sourceMode'] = 'excel';
        $reference['sample'] = [
            'kons' => $this->formatDecimal($sample['kons'] ?? null, self::DEFAULT_REFERENCE['sample']['kons'], 4),
            'vol' => $this->formatDecimal($sample['vol'] ?? null, self::DEFAULT_REFERENCE['sample']['vol'], 1),
            'waktu' => $this->formatDecimal($sample['waktu'] ?? null, self::DEFAULT_REFERENCE['sample']['waktu'], 0),
            'fr' => $this->formatDecimal($sample['fr'] ?? null, self::DEFAULT_REFERENCE['sample']['fr'], 3),
            'sk' => $this->formatDecimal($sample['sk'] ?? null, self::DEFAULT_REFERENCE['sample']['sk'], 1),
            'pm' => $this->formatDecimal($sample['pm'] ?? null, self::DEFAULT_REFERENCE['sample']['pm'], 0),
            'ppm' => $this->formatDecimal($sample['ppm'] ?? null, self::DEFAULT_REFERENCE['sample']['ppm'], 4),
            'ugm3' => $this->formatDecimal($sample['ugm3'] ?? null, self::DEFAULT_REFERENCE['sample']['ugm3'], 1),
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
