<?php

namespace App\Services\Rumus\Contracts;

interface PrepanalisaRumusServiceInterface
{
    public function apply(array $payload): array;
}

