<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;

class KepalaBalaiService
{
    private static ?array $cachedProfile = null;

    public function getProfile(): array
    {
        if (self::$cachedProfile !== null) {
            return self::$cachedProfile;
        }

        if (!Schema::hasTable('app_settings')) {
            return self::$cachedProfile = $this->defaultProfile();
        }

        $values = AppSetting::query()
            ->whereIn('key', ['kepala_balai_nama', 'kepala_balai_nip'])
            ->pluck('value', 'key');

        return self::$cachedProfile = [
            'nama' => trim((string) ($values->get('kepala_balai_nama') ?? $this->defaultProfile()['nama'])),
            'nip' => trim((string) ($values->get('kepala_balai_nip') ?? $this->defaultProfile()['nip'])),
        ];
    }

    public function updateProfile(string $nama, string $nip): void
    {
        AppSetting::query()->updateOrCreate(
            ['key' => 'kepala_balai_nama'],
            ['value' => trim($nama)]
        );

        AppSetting::query()->updateOrCreate(
            ['key' => 'kepala_balai_nip'],
            ['value' => trim($nip)]
        );

        self::$cachedProfile = [
            'nama' => trim($nama),
            'nip' => trim($nip),
        ];
    }

    private function defaultProfile(): array
    {
        return [
            'nama' => 'Oktofa S. Pamungkas, S.T, M.Kes',
            'nip' => '19791003 200912 1 002',
        ];
    }
}
