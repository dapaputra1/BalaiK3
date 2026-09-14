<?php

namespace App\Services\Rumus\Emisi;

use App\Services\Rumus\Abstracts\BasePrepanalisaRumusService;

class EmisiSo2Service extends BasePrepanalisaRumusService
{
    public function apply(array $payload): array
    {
        $rows = $payload['hasil_perhitungan'] ?? null;
        if (!is_array($rows)) {
            return $payload;
        }

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            $kadar = self::calculateKadar([
                'kons' => $row['kons'] ?? null,
                'vol' => $row['vol'] ?? null,
                'fr' => $row['fr'] ?? null,
                'waktu' => $row['waktu'] ?? null,
                'tm' => $row['tm'] ?? null,
                'p' => $row['p'] ?? null,
            ]);
            if ($kadar !== null) {
                $row['kadar_mg_m3'] = $kadar;
                $rows[$index] = $row;
            }
        }

        $payload['hasil_perhitungan'] = $rows;
        return $payload;
    }

    public static function calculateKadar(array $input): ?float
    {
        $service = new self();
        $kons = $service->toFloat($input['kons'] ?? null);
        $vol = $service->toFloat($input['vol'] ?? null);
        $fr = $service->toFloat($input['fr'] ?? null);
        $waktu = $service->toFloat($input['waktu'] ?? null);
        $tm = $service->toFloat($input['tm'] ?? null);
        $p = $service->toFloat($input['p'] ?? null);

        if ($kons === null || $vol === null || $fr === null || $waktu === null || $tm === null || $p === null) {
            return null;
        }

        // kons*vol*(273+tm)*0.67*760*1000/(waktu*fr*10*298*p)
        $numerator = $kons * $vol * (273 + $tm) * 0.67 * 760 * 1000;
        $denominator = $waktu * $fr * 10 * 298 * $p;
        if ($denominator == 0.0) {
            return null;
        }

        return $numerator / $denominator;
    }

    private function toFloat($value): ?float
    {
        return $this->toNullableFloat($value);
    }
}
