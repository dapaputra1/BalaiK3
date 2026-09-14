<?php

namespace App\Services\Rumus\Ambien;

use App\Services\FormulaReference\DebuExcelReferenceService;
use App\Services\Rumus\Abstracts\BasePrepanalisaRumusService;

class DebuService extends BasePrepanalisaRumusService
{
    public function __construct(
        private readonly DebuExcelReferenceService $referenceService
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

        $systemCode = (string) ($payload['service_parameter_system_code'] ?? '');
        $reference = $this->referenceService->getReference($systemCode);

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $rows[$index] = $this->normalizeCalcRow($row, $reference, $systemCode);
        }

        $payload['hasil_perhitungan'] = $columns !== null
            ? ['columns' => $columns, 'rows' => $rows]
            : $rows;

        return $payload;
    }

    private function normalizeCalcRow(array $row, array $reference, string $systemCode): array
    {
        $rowType = (string) ($row['row_type'] ?? '');
        if (!in_array($rowType, ['debu-calc', 'debu-mdl'], true)) {
            return $row;
        }

        $isMdl = $rowType === 'debu-mdl';
        $berat = $this->toNullableFloat($row['berat'] ?? null);
        $fr = $this->toNullableFloat($row['fr'] ?? null);
        $waktu = $this->toNullableFloat($row['waktu'] ?? null);
        $sk = $this->toNullableFloat($row['sk'] ?? null);
        $p = $this->toNullableFloat($row['p'] ?? null);

        if ($berat === null || $fr === null || $waktu === null || $sk === null || $p === null) {
            return $this->clearCalculatedValues($row);
        }

        $calculated = $this->referenceService->calculate($berat, $fr, $waktu, $sk, $p, $systemCode);
        $kadar = $calculated['kadar_mgm3'] ?? null;

        if ($kadar === null) {
            return $this->clearCalculatedValues($row);
        }

        $row['kadar'] = $this->formatDecimal($kadar, 4);
        if ($isMdl) {
            $row['berat'] = $this->formatDecimal($berat, 4);
            $row['fr'] = $this->formatDecimal($fr, 2);
            $row['waktu'] = $this->formatDecimal($waktu, 0);
            $row['sk'] = $this->formatDecimal($sk, 1);
            $row['p'] = $this->formatDecimal($p, 0);
        }

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][2] = $row['berat'] ?? ($row['cols'][2] ?? '');
            $row['cols'][3] = $row['fr'] ?? ($row['cols'][3] ?? '');
            $row['cols'][4] = $row['waktu'] ?? ($row['cols'][4] ?? '');
            $row['cols'][5] = $row['sk'] ?? ($row['cols'][5] ?? '');
            $row['cols'][6] = $row['p'] ?? ($row['cols'][6] ?? '');
            $row['cols'][7] = $row['kadar'];
        }

        return $row;
    }

    private function clearCalculatedValues(array $row): array
    {
        $row['kadar'] = '';

        if (isset($row['cols']) && is_array($row['cols'])) {
            $row['cols'][7] = '';
        }

        return $row;
    }

    private function formatDecimal(float $value, int $precision): string
    {
        return number_format($value, $precision, '.', '');
    }
}
