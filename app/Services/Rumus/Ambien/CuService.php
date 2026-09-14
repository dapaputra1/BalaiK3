<?php

namespace App\Services\Rumus\Ambien;

use App\Services\FormulaReference\CuExcelReferenceService;
use App\Services\Rumus\Abstracts\BasePrepanalisaRumusService;

class CuService extends BasePrepanalisaRumusService
{
    public function __construct(
        private readonly CuExcelReferenceService $referenceService
    ) {
    }

    public function apply(array $payload): array
    {
        if (isset($payload['hasil_baca']) && is_array($payload['hasil_baca'])) {
            $payload['hasil_baca'] = $this->normalizeHasilBaca($payload['hasil_baca']);
        }

        if (isset($payload['hasil_perhitungan']) && is_array($payload['hasil_perhitungan'])) {
            $payload['hasil_perhitungan'] = $this->normalizeHasilPerhitungan($payload['hasil_perhitungan']);
        }

        return $payload;
    }

    private function normalizeHasilBaca(array $dataset): array
    {
        $columns = isset($dataset['columns']) && is_array($dataset['columns']) ? array_values($dataset['columns']) : null;
        $rows = isset($dataset['rows']) && is_array($dataset['rows'])
            ? array_values($dataset['rows'])
            : (array_is_list($dataset) ? array_values($dataset) : null);

        if (!is_array($rows)) {
            return $dataset;
        }

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = strtolower(trim((string) ($row['label'] ?? '')));
            if ($label === 'rata-rata' || $label === 'blk' || $label === 'blanko') {
                $rows[$index] = $row;
                continue;
            }

            $abs = $this->toNullableFloat($row['abs'] ?? null);
            $kand = $abs !== null ? $this->referenceService->calculateConcentration($abs) : null;
            $row['kand-spl'] = $kand !== null ? $this->formatDecimal($kand, 4) : '';
            if (isset($row['cols']) && is_array($row['cols'])) {
                $row['cols'][4] = $row['kand-spl'];
            }

            $rows[$index] = $row;
        }

        return $columns !== null ? ['columns' => $columns, 'rows' => $rows] : $rows;
    }

    private function normalizeHasilPerhitungan(array $dataset): array
    {
        $columns = isset($dataset['columns']) && is_array($dataset['columns']) ? array_values($dataset['columns']) : null;
        $rows = isset($dataset['rows']) && is_array($dataset['rows'])
            ? array_values($dataset['rows'])
            : (array_is_list($dataset) ? array_values($dataset) : null);

        if (!is_array($rows)) {
            return $dataset;
        }

        $lodReference = $this->referenceService->getReference()['lod'] ?? [];

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $rows[$index] = $this->normalizeCalcRow($row, $lodReference);
        }

        return $columns !== null ? ['columns' => $columns, 'rows' => $rows] : $rows;
    }

    private function normalizeCalcRow(array $row, array $lodReference): array
    {
        $rowType = (string) ($row['row_type'] ?? '');
        if (!in_array($rowType, ['no2-calc', 'no2-mdl'], true)) {
            return $row;
        }

        $isLod = $rowType === 'no2-mdl';
        $kons = $this->toNullableFloat($row['kons'] ?? null);
        $vol = $this->toNullableFloat($row['vol'] ?? null);
        $fr = $this->toNullableFloat($row['fr'] ?? null);
        $waktu = $this->toNullableFloat($row['waktu'] ?? null);
        $sk = $this->toNullableFloat($row['sk'] ?? null);
        $p = $this->toNullableFloat($row['pm'] ?? ($row['p'] ?? null));

        if ($isLod) {
            $kons ??= $this->toNullableFloat($lodReference['kons'] ?? null);
            $vol ??= $this->toNullableFloat($lodReference['vol'] ?? null);
            $fr ??= $this->toNullableFloat($lodReference['fr'] ?? null);
            $waktu ??= $this->toNullableFloat($lodReference['waktu'] ?? null);
            $sk ??= $this->toNullableFloat($lodReference['sk'] ?? null);
            $p ??= $this->toNullableFloat($lodReference['p'] ?? null);
        }

        if ($kons === null || $vol === null || $fr === null || $waktu === null || $sk === null || $p === null) {
            return $this->clearCalculatedValues($row);
        }

        $calculated = $this->referenceService->calculate($kons, $vol, $fr, $waktu, $sk, $p);
        $mgm3 = $calculated['kadar_mgm3'] ?? null;
        $ugm3 = $calculated['kadar_ugm3'] ?? null;

        if ($mgm3 === null || $ugm3 === null) {
            return $this->clearCalculatedValues($row);
        }

        $row['kadar_ppm'] = '';
        $row['kadar_ugm3'] = $this->formatDecimal($ugm3, 4);
        $row['kadar_mgm3'] = $this->formatDecimal($mgm3, 4);

        if ($isLod) {
            $row['kons'] = $this->formatDecimal($kons, 4);
            $row['vol'] = $this->formatDecimal($vol, 1);
            $row['fr'] = $this->formatDecimal($fr, 3);
            $row['waktu'] = $this->formatDecimal($waktu, 0);
            $row['sk'] = $this->formatDecimal($sk, 1);
            $row['pm'] = $this->formatDecimal($p, 0);
        }

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][2] = $row['kons'] ?? ($row['cols'][2] ?? '');
            $row['cols'][3] = $row['vol'] ?? ($row['cols'][3] ?? '');
            $row['cols'][4] = $row['waktu'] ?? ($row['cols'][4] ?? '');
            $row['cols'][5] = $row['fr'] ?? ($row['cols'][5] ?? '');
            $row['cols'][6] = $row['sk'] ?? ($row['cols'][6] ?? '');
            $row['cols'][7] = $row['pm'] ?? ($row['cols'][7] ?? '');
            $row['cols'][8] = '';
            $row['cols'][9] = $row['kadar_ugm3'];
            $row['cols'][10] = $row['kadar_mgm3'];
        }

        return $row;
    }

    private function clearCalculatedValues(array $row): array
    {
        $row['kadar_ppm'] = '';
        $row['kadar_ugm3'] = '';
        $row['kadar_mgm3'] = '';

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][8] = '';
            $row['cols'][9] = '';
            $row['cols'][10] = '';
        }

        return $row;
    }

    private function formatDecimal(float $value, int $precision): string
    {
        return number_format($value, $precision, '.', '');
    }
}
