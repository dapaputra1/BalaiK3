<?php

namespace Database\Seeders;

use App\Models\LoginBackground;
use Illuminate\Database\Seeder;

class LoginBackgroundSeeder extends Seeder
{
    public function run(): void
    {
        LoginBackground::query()->updateOrCreate(
            ['image_path' => 'images/bg.png'],
            [
                'name' => 'Background bawaan login',
                'is_active' => true,
            ]
        );

        LoginBackground::query()
            ->where('image_path', '!=', 'images/bg.png')
            ->update(['is_active' => false]);
    }
}
