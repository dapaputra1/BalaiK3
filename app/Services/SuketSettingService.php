<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;

class SuketSettingService
{
    private const KEY = 'suket_penerbitan_enabled';
    private const DEFAULT_ENABLED = true;

    private static ?bool $cachedEnabled = null;

    public function isEnabled(): bool
    {
        if (self::$cachedEnabled !== null) {
            return self::$cachedEnabled;
        }

        if (!Schema::hasTable('app_settings')) {
            return self::$cachedEnabled = self::DEFAULT_ENABLED;
        }

        $rawValue = AppSetting::query()
            ->where('key', self::KEY)
            ->value('value');

        if ($rawValue === null) {
            return self::$cachedEnabled = self::DEFAULT_ENABLED;
        }

        return self::$cachedEnabled = filter_var($rawValue, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
            ?? self::DEFAULT_ENABLED;
    }

    public function updateEnabled(bool $enabled): void
    {
        AppSetting::query()->updateOrCreate(
            ['key' => self::KEY],
            ['value' => $enabled ? '1' : '0']
        );

        self::$cachedEnabled = $enabled;
    }
}
