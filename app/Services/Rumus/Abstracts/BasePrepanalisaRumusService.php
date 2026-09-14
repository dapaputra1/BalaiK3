<?php

namespace App\Services\Rumus\Abstracts;

use App\Services\Rumus\Contracts\PrepanalisaRumusServiceInterface;

abstract class BasePrepanalisaRumusService implements PrepanalisaRumusServiceInterface
{
    protected function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }
}

