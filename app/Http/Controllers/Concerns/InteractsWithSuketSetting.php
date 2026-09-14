<?php

namespace App\Http\Controllers\Concerns;

use App\Services\SuketSettingService;

trait InteractsWithSuketSetting
{
    protected function isSuketEnabled(): bool
    {
        return app(SuketSettingService::class)->isEnabled();
    }
}
