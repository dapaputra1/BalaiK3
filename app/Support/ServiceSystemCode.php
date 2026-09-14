<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ServiceSystemCode
{
    public const CATEGORY_LK = 'LK';
    public const CATEGORY_AMB = 'AMB';
    public const CATEGORY_EMS = 'EMS';
    public const CATEGORY_KES = 'KES';
    public const CATEGORY_PLT = 'PLT';

    public static function normalizeSegment(?string $value, string $fallback = 'CODE'): string
    {
        $normalized = Str::upper(Str::ascii((string) $value));
        $normalized = preg_replace('/[^A-Z0-9]+/', '_', $normalized) ?? '';
        $normalized = preg_replace('/_+/', '_', $normalized) ?? '';
        $normalized = trim($normalized, '_');

        return $normalized !== '' ? $normalized : $fallback;
    }

    public static function legacyCategoryCode(?string $shortCode, ?string $name = null, ?int $legacyId = null): string
    {
        return self::normalizeSegment(
            $shortCode ?: $name ?: ($legacyId ? 'CAT_' . $legacyId : 'CAT'),
            'CAT'
        );
    }

    public static function categoryCodeBase(?string $shortCode, ?string $name = null): string
    {
        return self::normalizeSegment($shortCode ?: $name ?: 'CAT', 'CAT');
    }

    public static function legacyParameterCode(
        string $categorySystemCode,
        ?string $shortCode = null,
        ?string $name = null,
        ?int $legacyId = null
    ): string {
        $categoryCode = self::normalizeSegment($categorySystemCode, 'CAT');
        $shortCodeSegment = self::normalizeSegment($shortCode, '');

        if ($categoryCode === self::CATEGORY_KES && in_array($shortCodeSegment, ['ANTRO', 'ANTKES'], true)) {
            return $categoryCode . '__ANTRO_KES';
        }

        $suffix = $shortCodeSegment;
        if ($suffix === '') {
            $suffix = self::normalizeSegment($name, '');
        }
        if ($suffix === '') {
            $suffix = 'PARAM_' . ($legacyId ?: 'NEW');
        }

        return $categoryCode . '__' . $suffix;
    }

    public static function parameterCodeBase(
        ?string $categorySystemCode,
        ?string $shortCode = null,
        ?string $name = null
    ): string {
        $categoryCode = self::normalizeSegment($categorySystemCode, 'CAT');
        $suffix = self::normalizeSegment($shortCode, '');
        if ($suffix === '') {
            $suffix = self::normalizeSegment($name, 'PARAM');
        }

        return $categoryCode . '__' . $suffix;
    }

    public static function parameter(string $categorySystemCode, string $legacyShortCode): string
    {
        return self::normalizeSegment($categorySystemCode, 'CAT')
            . '__'
            . self::normalizeSegment($legacyShortCode, 'PARAM');
    }

    public static function uniqueForTable(
        string $table,
        string $column,
        string $baseCode,
        ?int $ignoreId = null
    ): string {
        $baseCode = self::normalizeSegment($baseCode, 'CODE');
        $candidate = $baseCode;
        $suffix = 2;

        while (self::tableHasCode($table, $column, $candidate, $ignoreId)) {
            $candidate = $baseCode . '_' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private static function tableHasCode(
        string $table,
        string $column,
        string $candidate,
        ?int $ignoreId = null
    ): bool {
        $query = DB::table($table)->where($column, $candidate);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
