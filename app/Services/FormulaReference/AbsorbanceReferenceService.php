<?php

namespace App\Services\FormulaReference;

use App\Models\AbsorbanceFormula;

class AbsorbanceReferenceService
{
    private const DEFAULT_REFERENCES = [
        'NO2' => [
            'parameter_key' => 'NO2',
            'label' => 'NO2',
            'intercept' => 0.0,
            'slope' => 0.9517,
            'value_1_label' => 'Nilai Dasar',
            'value_2_label' => 'Pengali Absorbansi',
            'formula' => 'cons = 0 + 0.9517 * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'OX' => [
            'parameter_key' => 'OX',
            'label' => 'OX',
            'intercept' => 0.0244,
            'slope' => 0.8264,
            'value_1_label' => 'Nilai Dasar',
            'value_2_label' => 'Pengali Absorbansi',
            'formula' => 'cons = 0.0244 + 0.8264 * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'PB' => [
            'parameter_key' => 'PB',
            'label' => 'Pb',
            'intercept' => 0.0,
            'slope' => 222.222,
            'curve_y' => 0.0045,
            'curve_x' => 222.222,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.0045x | x = 222.222 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'CD' => [
            'parameter_key' => 'CD',
            'label' => 'Cd',
            'intercept' => 0.0,
            'slope' => 8.70322019147,
            'curve_y' => 0.1149,
            'curve_x' => 8.70322019147,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.1149x | x = 8.70322 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'CU' => [
            'parameter_key' => 'CU',
            'label' => 'Cu',
            'intercept' => 0.0,
            'slope' => 22.37136465324,
            'curve_y' => 0.0447,
            'curve_x' => 22.37136465324,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.0447x | x = 22.371365 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'CR' => [
            'parameter_key' => 'CR',
            'label' => 'Cr',
            'intercept' => 0.0,
            'slope' => 40.32258064516,
            'curve_y' => 0.0248,
            'curve_x' => 40.32258064516,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.0248x | x = 40.322581 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'AS' => [
            'parameter_key' => 'AS',
            'label' => 'As',
            'intercept' => 0.0,
            'slope' => 39.370078740157,
            'curve_y' => 0.0254,
            'curve_x' => 39.370078740157,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.0254x | x = 39.370079 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'HG' => [
            'parameter_key' => 'HG',
            'label' => 'Hg',
            'intercept' => 0.0,
            'slope' => 277.777777777778,
            'curve_y' => 0.0036,
            'curve_x' => 277.777777777778,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.0036x | x = 277.777778 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'CO' => [
            'parameter_key' => 'CO',
            'label' => 'Co',
            'intercept' => 0.0,
            'slope' => 28.901734104046,
            'curve_y' => 0.0346,
            'curve_x' => 28.901734104046,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.0346x | x = 28.901734 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'SB' => [
            'parameter_key' => 'SB',
            'label' => 'Sb',
            'intercept' => 0.0,
            'slope' => 285.714285714286,
            'curve_y' => 0.0035,
            'curve_x' => 285.714285714286,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.0035x | x = 285.714286 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'TL' => [
            'parameter_key' => 'TL',
            'label' => 'Tl',
            'intercept' => 0.0,
            'slope' => 178.571428571429,
            'curve_y' => 0.0056,
            'curve_x' => 178.571428571429,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.0056x | x = 178.571429 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'ZN' => [
            'parameter_key' => 'ZN',
            'label' => 'Zn',
            'intercept' => 0.0,
            'slope' => 6.87757909216,
            'curve_y' => 0.1454,
            'curve_x' => 6.87757909216,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'y = 0.1454x | x = 6.877579 | cons = x * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'BENZENE' => [
            'parameter_key' => 'BENZENE',
            'label' => 'Benzene',
            'intercept' => 3625144.0,
            'slope' => 0.000000275851,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'conc = area * x',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'TOLUENE' => [
            'parameter_key' => 'TOLUENE',
            'label' => 'Toluene',
            'intercept' => 4714408.0,
            'slope' => 0.000000212116,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'conc = area * x',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
        'XYLENE' => [
            'parameter_key' => 'XYLENE',
            'label' => 'Xylene',
            'intercept' => 4714408.0,
            'slope' => 0.000000212116,
            'value_1_label' => 'Nilai Y',
            'value_2_label' => 'Nilai X',
            'formula' => 'conc = area * x',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ],
    ];

    public function getAllReferences(): array
    {
        $references = self::DEFAULT_REFERENCES;
        $rows = AbsorbanceFormula::query()
            ->where('is_active', true)
            ->whereIn('parameter_key', array_keys(self::DEFAULT_REFERENCES))
            ->orderByDesc('effective_date')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('parameter_key');

        foreach ($rows as $parameterKey => $items) {
            $row = $items->first();
            if (!$row) {
                continue;
            }

            $intercept = (float) $row->intercept;
            $slope = (float) $row->slope;
            $isCurveFormula = in_array($parameterKey, ['PB', 'CD', 'CU', 'CR', 'AS', 'HG', 'CO', 'SB', 'TL', 'ZN'], true);
            $curveY = $isCurveFormula
                ? ($intercept != 0.0 ? $intercept : ($slope != 0.0 ? (1 / $slope) : 0.0))
                : null;
            $curveX = $isCurveFormula
                ? ($slope != 0.0 ? $slope : ($intercept != 0.0 ? (1 / $intercept) : 0.0))
                : null;
            $references[$parameterKey] = [
                'parameter_key' => $parameterKey,
                'label' => match ($parameterKey) {
                    'BENZENE', 'TOLUENE', 'XYLENE' => ucfirst(strtolower($parameterKey)),
                    'PB' => 'Pb',
                    'CD' => 'Cd',
                    'CU' => 'Cu',
                    'CR' => 'Cr',
                    'AS' => 'As',
                    'HG' => 'Hg',
                    'CO' => 'Co',
                    'SB' => 'Sb',
                    'TL' => 'Tl',
                    'ZN' => 'Zn',
                    default => $parameterKey,
                },
                'intercept' => $isCurveFormula ? 0.0 : $intercept,
                'slope' => $isCurveFormula ? $curveX : $slope,
                'curve_y' => $curveY,
                'curve_x' => $curveX,
                'value_1_label' => in_array($parameterKey, ['PB', 'CD', 'CU', 'CR', 'AS', 'HG', 'CO', 'SB', 'TL', 'ZN', 'BENZENE', 'TOLUENE', 'XYLENE'], true) ? 'Nilai Y' : 'Nilai Dasar',
                'value_2_label' => in_array($parameterKey, ['PB', 'CD', 'CU', 'CR', 'AS', 'HG', 'CO', 'SB', 'TL', 'ZN', 'BENZENE', 'TOLUENE', 'XYLENE'], true) ? 'Nilai X' : 'Pengali Absorbansi',
                'formula' => $this->buildFormulaLabel($parameterKey, $intercept, $slope),
                'effective_date' => optional($row->effective_date)->format('Y-m-d'),
                'source_mode' => 'database',
            ];
        }

        return $references;
    }

    public function getReference(string $parameterKey): array
    {
        $parameterKey = strtoupper(trim($parameterKey));
        $references = $this->getAllReferences();

        return $references[$parameterKey] ?? [
            'parameter_key' => $parameterKey,
            'label' => $parameterKey,
            'intercept' => 0.0,
            'slope' => 0.0,
            'value_1_label' => 'Nilai Dasar',
            'value_2_label' => 'Pengali Absorbansi',
            'formula' => 'cons = 0 + 0 * abs',
            'effective_date' => null,
            'source_mode' => 'fallback',
        ];
    }

    public function calculate(string $parameterKey, float $abs): float
    {
        $reference = $this->getReference($parameterKey);
        $intercept = (float) ($reference['intercept'] ?? 0.0);
        $slope = (float) ($reference['slope'] ?? 0.0);

        return $intercept + ($slope * $abs);
    }

    private function buildFormulaLabel(string $parameterKey, float $intercept, float $slope): string
    {
        if (in_array($parameterKey, ['BENZENE', 'TOLUENE', 'XYLENE'], true)) {
            return sprintf(
                'y = %s | x = %s | conc = area * x',
                rtrim(rtrim(number_format($intercept, 12, ',', '.'), '0'), ','),
                rtrim(rtrim(number_format($slope, 12, ',', '.'), '0'), ',')
            );
        }

        if (in_array($parameterKey, ['PB', 'CD', 'CU', 'CR', 'AS', 'HG', 'CO', 'SB', 'TL', 'ZN'], true)) {
            $curveY = $intercept != 0.0 ? $intercept : ($slope != 0.0 ? (1 / $slope) : 0.0);
            $curveX = $slope != 0.0 ? $slope : ($intercept != 0.0 ? (1 / $intercept) : 0.0);

            return sprintf(
                'y = %sx | x = %s | cons = x * abs',
                rtrim(rtrim(number_format($curveY, 6, ',', '.'), '0'), ','),
                rtrim(rtrim(number_format($curveX, 6, ',', '.'), '0'), ',')
            );
        }

        return sprintf(
            'cons = %s + %s * abs',
            rtrim(rtrim(number_format($intercept, 6, ',', '.'), '0'), ','),
            rtrim(rtrim(number_format($slope, 6, ',', '.'), '0'), ',')
        );
    }
}
