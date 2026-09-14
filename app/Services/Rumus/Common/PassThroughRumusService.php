<?php

namespace App\Services\Rumus\Common;

use App\Services\Rumus\Abstracts\BasePrepanalisaRumusService;

class PassThroughRumusService extends BasePrepanalisaRumusService
{
    public function apply(array $payload): array
    {
        return $payload;
    }
}

