<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AppSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        if (!Schema::hasTable('app_settings')) {
            return $default;
        }

        $value = static::query()
            ->where('key', $key)
            ->value('value');

        return is_string($value) && $value !== '' ? $value : $default;
    }

    public static function assetUrl(string $key, string $fallbackRelativePath): string
    {
        $path = static::getValue($key, $fallbackRelativePath);

        return asset($path ?: $fallbackRelativePath);
    }
}
