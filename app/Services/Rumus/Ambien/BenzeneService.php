<?php

namespace App\Services\Rumus\Ambien;

use App\Services\FormulaReference\BenzeneExcelReferenceService;
use App\Services\Rumus\Abstracts\BasePrepanalisaRumusService;

class BenzeneService extends BasePrepanalisaRumusService
{
    public function __construct(
        private readonly BenzeneExcelReferenceService $referenceService
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

        $blankoValues = [];
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = strtolower(trim((string) ($row['label'] ?? '')));
            if ($label === 'rata-rata') {
                continue;
            }

            $area = $this->toNullableFloat($row['area_benzene'] ?? null);
            if ($area === null) {
                continue;
            }

            $benzene = $this->referenceService->calculateConcentration($area);
            $row['benzene'] = $this->formatDecimal($benzene, 5);
            if (isset($row['cols']) && is_array($row['cols'])) {
                $row['cols'][4] = $row['benzene'];
            }

            if (in_array($label, ['blk', 'blanko'], true)) {
                $blankoValues[] = $benzene;
            }

            $rows[$index] = $row;
        }

        if (count($blankoValues) > 0) {
            $avgBlanko = array_sum($blankoValues) / count($blankoValues);
            foreach ($rows as $index => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $label = strtolower(trim((string) ($row['label'] ?? '')));
                if ($label !== 'rata-rata') {
                    continue;
                }

                $row['benzene'] = $this->formatDecimal($avgBlanko, 4);
                if (isset($row['cols']) && is_array($row['cols'])) {
                    $row['cols'][4] = $row['benzene'];
                }
                $rows[$index] = $row;
            }
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
        if (!in_array($rowType, ['hc-calc', 'hc-lod'], true)) {
            return $row;
        }

        $isLod = $rowType === 'hc-lod';
        $kons = $this->toNullableFloat($row['kons'] ?? null);
        $vol = $this->toNullableFloat($row['vol'] ?? null);
        $fr = $this->toNullableFloat($row['fr'] ?? null);
        $waktu = $this->toNullableFloat($row['waktu'] ?? null);
        $sk = $this->toNullableFloat($row['sk'] ?? null);
        $p = $this->toNullableFloat($row['p'] ?? ($row['pm'] ?? null));

        if ($isLod) {
            $kons = $this->toNullableFloat($lodReference['kons'] ?? null);
            $vol = $this->toNullableFloat($lodReference['vol'] ?? null);
            $fr = $this->toNullableFloat($lodReference['fr'] ?? null);
            $waktu = $this->toNullableFloat($lodReference['waktu'] ?? null);
            $sk ??= $this->toNullableFloat($lodReference['sk'] ?? null);
            $p = $this->toNullableFloat($lodReference['p'] ?? null);
        }

        if ($kons === null || $vol === null || $fr === null || $waktu === null || $sk === null || $p === null) {
            return $this->clearCalculatedValues($row);
        }

        $calculated = $this->referenceService->calculate($kons, $vol, $fr, $waktu, $sk, $p);
        $ppm = $calculated['kadar_ppm'] ?? null;
        $ugm3 = $calculated['kadar_ugm3'] ?? null;

        if ($ppm === null || $ugm3 === null) {
            return $this->clearCalculatedValues($row);
        }

        $row['kadar_ppm'] = $this->formatDecimal($ppm, 4);
        $row['kadar_ugm3'] = $this->formatDecimal($ugm3, 4);
        $row['kadar_mgm3'] = $this->formatDecimal($ugm3 / 1000, 4);

        $row['p'] = $this->formatDecimal($p, 0);
        if (array_key_exists('pm', $row) || $row['p'] !== '') {
            $row['pm'] = $row['p'];
        }

        if ($isLod) {
            $row['kons'] = $this->formatDecimal($kons, 5);
            $row['vol'] = $this->formatDecimal($vol, 1);
            $row['fr'] = $this->formatDecimal($fr, 3);
            $row['waktu'] = $this->formatDecimal($waktu, 0);
            $row['sk'] = $this->formatDecimal($sk, 1);
        }

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][1] = $row['kons'] ?? ($row['cols'][1] ?? '');
            $row['cols'][2] = $row['vol'] ?? ($row['cols'][2] ?? '');
            $row['cols'][3] = $row['waktu'] ?? ($row['cols'][3] ?? '');
            $row['cols'][4] = $row['fr'] ?? ($row['cols'][4] ?? '');
            $row['cols'][5] = $row['sk'] ?? ($row['cols'][5] ?? '');
            $row['cols'][6] = $row['p'] ?? ($row['cols'][6] ?? '');
            $row['cols'][7] = $row['kadar_ppm'];
            $row['cols'][8] = $row['kadar_ugm3'];
            $row['cols'][9] = $row['kadar_mgm3'];
        }

        return $row;
    }

    private function clearCalculatedValues(array $row): array
    {
        $row['kadar_ppm'] = '';
        $row['kadar_ugm3'] = '';
        $row['kadar_mgm3'] = '';

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][7] = '';
            $row['cols'][8] = '';
            $row['cols'][9] = '';
        }

        return $row;
    }

    private function formatDecimal(float $value, int $precision): string
    {
        return number_format($value, $precision, '.', '');
    }
}
