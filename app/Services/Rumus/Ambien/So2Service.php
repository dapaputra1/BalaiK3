<?php

namespace App\Services\Rumus\Ambien;

use App\Services\FormulaReference\So2AmbienExcelReferenceService;
use App\Services\Rumus\Abstracts\BasePrepanalisaRumusService;

class So2Service extends BasePrepanalisaRumusService
{
    public function __construct(
        private readonly So2AmbienExcelReferenceService $referenceService
    ) {
    }

    public function apply(array $payload): array
    {
        $dataset = $payload['hasil_perhitungan'] ?? null;
        if (!is_array($dataset)) {
            return $payload;
        }

        $columns = isset($dataset['columns']) && is_array($dataset['columns'])
            ? array_values($dataset['columns'])
            : null;
        $rows = isset($dataset['rows']) && is_array($dataset['rows'])
            ? array_values($dataset['rows'])
            : (array_is_list($dataset) ? array_values($dataset) : null);

        if (!is_array($rows)) {
            return $payload;
        }

        $reference = $this->referenceService->getReference();

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $rows[$index] = $this->normalizeCalcRow($row, $reference);
        }

        $payload['hasil_perhitungan'] = $columns !== null
            ? ['columns' => $columns, 'rows' => $rows]
            : $rows;

        return $payload;
    }

    private function normalizeCalcRow(array $row, array $reference): array
    {
        $rowType = (string) ($row['row_type'] ?? '');
        if (!in_array($rowType, ['no2-calc', 'no2-mdl'], true)) {
            return $row;
        }

        $isMdl = $rowType === 'no2-mdl';
        $kons = $this->toNullableFloat($row['kons'] ?? null);
        $vol = $this->toNullableFloat($row['vol'] ?? null);
        $waktu = $this->toNullableFloat($row['waktu'] ?? null);
        $fr = $this->toNullableFloat($row['fr'] ?? null);
        $sk = $this->toNullableFloat($row['sk'] ?? null);
        $pm = $this->toNullableFloat($row['pm'] ?? null);

        if ($isMdl) {
            $kons ??= $this->toNullableFloat($reference['kons'] ?? null);
            $vol ??= $this->toNullableFloat($reference['vol'] ?? null);
            $waktu ??= $this->toNullableFloat($reference['waktu'] ?? null);
            $fr ??= $this->toNullableFloat($reference['fr'] ?? null);
            $sk ??= $this->toNullableFloat($reference['sk'] ?? null);
            $pm ??= $this->toNullableFloat($reference['pm'] ?? null);
        }

        if ($kons === null || $vol === null || $waktu === null || $fr === null || $sk === null || $pm === null) {
            return $this->clearCalculatedValues($row);
        }

        $calculated = $this->referenceService->calculate($kons, $vol, $fr, $waktu, $sk, $pm);
        $ppm = $calculated['kadar_ppm'] ?? null;
        $ugm3 = $calculated['kadar_ugm3'] ?? null;
        $mgm3 = $ugm3 !== null ? ($ugm3 / 1000) : null;

        if ($ppm === null || $ugm3 === null || $mgm3 === null) {
            return $this->clearCalculatedValues($row);
        }

        $row['kadar_ppm'] = $this->formatDecimal($ppm, 4);
        $row['kadar_ugm3'] = $this->formatDecimal($ugm3, 4);
        $row['kadar_mgm3'] = $this->formatDecimal($mgm3, 4);
        if ($isMdl) {
            $row['kons'] = $this->formatDecimal($kons, 4);
            $row['vol'] = $this->formatDecimal($vol, 1);
            $row['waktu'] = $this->formatDecimal($waktu, 0);
            $row['fr'] = $this->formatDecimal($fr, 3);
            $row['sk'] = $this->formatDecimal($sk, 1);
            $row['pm'] = $this->formatDecimal($pm, 0);
        }

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][2] = $row['kons'] ?? ($row['cols'][2] ?? '');
            $row['cols'][3] = $row['vol'] ?? ($row['cols'][3] ?? '');
            $row['cols'][4] = $row['waktu'] ?? ($row['cols'][4] ?? '');
            $row['cols'][5] = $row['fr'] ?? ($row['cols'][5] ?? '');
            $row['cols'][6] = $row['sk'] ?? ($row['cols'][6] ?? '');
            $row['cols'][7] = $row['pm'] ?? ($row['cols'][7] ?? '');
            $row['cols'][8] = $row['kadar_ppm'];
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
