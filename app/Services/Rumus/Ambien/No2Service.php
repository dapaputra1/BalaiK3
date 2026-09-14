<?php

namespace App\Services\Rumus\Ambien;

use App\Services\FormulaReference\AbsorbanceReferenceService;
use App\Services\FormulaReference\No2ExcelReferenceService;
use App\Services\Rumus\Abstracts\BasePrepanalisaRumusService;
use App\Support\ServiceSystemCode;

class No2Service extends BasePrepanalisaRumusService
{
    public function __construct(
        private readonly AbsorbanceReferenceService $absorbanceReferenceService,
        private readonly No2ExcelReferenceService $referenceService
    ) {
    }

    public function apply(array $payload): array
    {
        if (isset($payload['hasil_baca']) && is_array($payload['hasil_baca'])) {
            $payload['hasil_baca'] = $this->normalizeHasilBaca($payload['hasil_baca']);
        }

        if (isset($payload['hasil_perhitungan']) && is_array($payload['hasil_perhitungan'])) {
            $payload['hasil_perhitungan'] = $this->normalizeHasilPerhitungan(
                $payload['hasil_perhitungan'],
                (string) ($payload['service_category_system_code'] ?? '')
            );
        }

        return $payload;
    }

    private function normalizeHasilBaca(array $dataset): array
    {
        $columns = isset($dataset['columns']) && is_array($dataset['columns'])
            ? array_values($dataset['columns'])
            : null;
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

            $abs = $this->toNullableFloat($row['abs'] ?? null);
            if ($abs === null) {
                continue;
            }

            $kand = $this->absorbanceReferenceService->calculate('NO2', $abs);
            $row['conc'] = $kand;
            $row['kand_spl'] = $kand;
            $row['kand'] = $this->formatDecimal($kand, 4);
            if (isset($row['cols']) && is_array($row['cols'])) {
                $row['cols'][4] = $row['kand'];
            }

            if (in_array($label, ['blk', 'blanko'], true)) {
                $blankoValues[] = $kand;
                $row['kndbl'] = '';
                if (isset($row['cols']) && is_array($row['cols'])) {
                    $row['cols'][6] = '';
                }
            }

            $rows[$index] = $row;
        }

        $avgBlanko = count($blankoValues) > 0 ? array_sum($blankoValues) / count($blankoValues) : null;
        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = strtolower(trim((string) ($row['label'] ?? '')));
            if ($label === 'rata-rata') {
                if ($avgBlanko !== null) {
                    $row['conc'] = $avgBlanko;
                    $row['kand_spl'] = $avgBlanko;
                    $row['kand'] = $this->formatDecimal($avgBlanko, 4);
                    $row['kndbl'] = '';
                    if (isset($row['cols']) && is_array($row['cols'])) {
                        $row['cols'][4] = $row['kand'];
                        $row['cols'][6] = '';
                    }
                }
                $rows[$index] = $row;
                continue;
            }

            if (in_array($label, ['blk', 'blanko'], true)) {
                $rows[$index] = $row;
                continue;
            }

            $kand = $this->toNullableFloat($row['kand'] ?? ($row['kand_spl'] ?? null));
            $row['kndbl'] = ($kand !== null && $avgBlanko !== null)
                ? $this->formatDecimal($kand - $avgBlanko, 4)
                : '';
            if (isset($row['cols']) && is_array($row['cols'])) {
                $row['cols'][6] = $row['kndbl'];
            }
            $rows[$index] = $row;
        }

        return $columns !== null
            ? ['columns' => $columns, 'rows' => $rows]
            : $rows;
    }

    private function normalizeHasilPerhitungan(array $dataset, string $categorySystemCode): array
    {
        $columns = isset($dataset['columns']) && is_array($dataset['columns'])
            ? array_values($dataset['columns'])
            : null;
        $rows = isset($dataset['rows']) && is_array($dataset['rows'])
            ? array_values($dataset['rows'])
            : (array_is_list($dataset) ? array_values($dataset) : null);

        if (!is_array($rows)) {
            return $dataset;
        }

        $reference = $this->referenceService->getReference();
        $lodReference = strtoupper($categorySystemCode) === ServiceSystemCode::CATEGORY_LK
            ? ($reference['lodLk'] ?? [])
            : ($reference['lodAmbien'] ?? []);

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $rows[$index] = $this->normalizeCalcRow($row, $lodReference);
        }

        return $columns !== null
            ? ['columns' => $columns, 'rows' => $rows]
            : $rows;
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
        $ppm = $calculated['kadar_ppm'] ?? null;
        $ugm3 = $calculated['kadar_ugm3'] ?? null;

        if ($ppm === null || $ugm3 === null) {
            return $this->clearCalculatedValues($row);
        }

        $row['kadar_ppm'] = $this->formatDecimal($ppm, 4);
        $row['kadar_ugm3'] = $this->formatDecimal($ugm3, 4);
        $row['kadar_mgm3'] = $this->formatDecimal($ugm3 / 1000, 4);

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
