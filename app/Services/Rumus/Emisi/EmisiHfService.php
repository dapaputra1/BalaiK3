<?php

namespace App\Services\Rumus\Emisi;

use App\Services\FormulaReference\HfEmisiExcelReferenceService;
use App\Services\Rumus\Abstracts\BasePrepanalisaRumusService;

class EmisiHfService extends BasePrepanalisaRumusService
{
    public function __construct(
        private readonly HfEmisiExcelReferenceService $referenceService
    ) {
    }

    public function apply(array $payload): array
    {
        if (isset($payload['hasil_perhitungan']) && is_array($payload['hasil_perhitungan'])) {
            $payload['hasil_perhitungan'] = $this->normalizeHasilPerhitungan($payload['hasil_perhitungan']);
        }

        return $payload;
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
        if (!in_array($rowType, ['so2-calc', 'so2-mdl'], true)) {
            return $row;
        }

        $isLod = $rowType === 'so2-mdl';
        $kons = $this->toNullableFloat($row['kons'] ?? $row['mdl-kons'] ?? null);
        $vol = $this->toNullableFloat($row['vol'] ?? $row['mdl-vol'] ?? null);
        $fr = $this->toNullableFloat($row['fr'] ?? null);
        $waktu = $this->toNullableFloat($row['waktu'] ?? null);
        $titrHf = $this->toNullableFloat($row['titr_hf'] ?? null);
        $tm = $this->toNullableFloat($row['tm'] ?? null);
        $p = $this->toNullableFloat($row['p'] ?? null);

        if ($isLod) {
            $kons ??= $this->toNullableFloat($lodReference['kons'] ?? null);
            $vol ??= $this->toNullableFloat($lodReference['vol'] ?? null);
            $fr ??= $this->toNullableFloat($lodReference['fr'] ?? null);
            $waktu ??= $this->toNullableFloat($lodReference['waktu'] ?? null);
            $titrHf ??= $this->toNullableFloat($lodReference['titr_hf'] ?? null);
            $tm ??= $this->toNullableFloat($lodReference['tm'] ?? null);
            $p ??= $this->toNullableFloat($lodReference['p'] ?? null);
        }

        if ($kons === null || $vol === null || $fr === null || $waktu === null || $titrHf === null || $tm === null || $p === null) {
            return $this->clearCalculatedValues($row);
        }

        $calculated = $this->referenceService->calculate($kons, $vol, $fr, $waktu, $titrHf, $tm, $p);
        $kadar = $calculated['kadar_mgm3'] ?? null;

        if ($kadar === null) {
            return $this->clearCalculatedValues($row);
        }

        $row['kadar_ugm3'] = $this->formatDecimal($kadar, 4);
        $row['kadar_mg_m3'] = $row['kadar_ugm3'];

        if ($isLod) {
            $row['mdl-kons'] = $this->formatDecimal($kons, 4);
            $row['mdl-vol'] = $this->formatDecimal($vol, 1);
            $row['fr'] = $this->formatDecimal($fr, 3);
            $row['waktu'] = $this->formatDecimal($waktu, 0);
            $row['titr_hf'] = $this->formatDecimal($titrHf, 1);
            $row['tm'] = $this->formatDecimal($tm, 1);
            $row['p'] = $this->formatDecimal($p, 0);
        } else {
            $row['kons'] = $this->formatDecimal($kons, 4);
            $row['vol'] = $this->formatDecimal($vol, 1);
            $row['fr'] = $this->formatDecimal($fr, 3);
            $row['waktu'] = $this->formatDecimal($waktu, 0);
            $row['titr_hf'] = $this->formatDecimal($titrHf, 1);
            $row['tm'] = $this->formatDecimal($tm, 1);
            $row['p'] = $this->formatDecimal($p, 0);
        }

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][1] = $isLod ? ($row['mdl-kons'] ?? '') : ($row['kons'] ?? '');
            $row['cols'][2] = $isLod ? ($row['mdl-vol'] ?? '') : ($row['vol'] ?? '');
            $row['cols'][3] = $row['fr'] ?? '';
            $row['cols'][4] = $row['waktu'] ?? '';
            $row['cols'][5] = $row['titr_hf'] ?? '';
            $row['cols'][6] = $row['tm'] ?? '';
            $row['cols'][7] = $row['p'] ?? '';
            $row['cols'][8] = $row['kadar_ugm3'];
        }

        return $row;
    }

    private function clearCalculatedValues(array $row): array
    {
        $row['kadar_ugm3'] = '';
        $row['kadar_mg_m3'] = '';

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][8] = '';
        }

        return $row;
    }

    private function formatDecimal(float $value, int $precision): string
    {
        return number_format($value, $precision, '.', '');
    }
}
