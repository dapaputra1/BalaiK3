<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Support\ServiceSystemCode;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'id' => 1,
                'name' => 'Lingkungan Kerja',
                'system_code' => ServiceSystemCode::CATEGORY_LK,
                'short_code' => 'LK',
                'is_active' => 1,
                'created_at' => '2025-12-17 23:12:18',
                'updated_at' => '2025-12-17 23:17:55',
            ],
            [
                'id' => 2,
                'name' => 'Ambien',
                'system_code' => ServiceSystemCode::CATEGORY_AMB,
                'short_code' => 'AMB',
                'is_active' => 1,
                'created_at' => '2025-12-18 06:32:36',
                'updated_at' => '2025-12-18 06:32:36',
            ],
            [
                'id' => 3,
                'name' => 'Emisi',
                'system_code' => ServiceSystemCode::CATEGORY_EMS,
                'short_code' => 'EMS',
                'is_active' => 1,
                'created_at' => '2025-12-18 06:32:36',
                'updated_at' => '2025-12-18 06:32:36',
            ],
            [
                'id' => 4,
                'name' => 'Kesehatan',
                'system_code' => ServiceSystemCode::CATEGORY_KES,
                'short_code' => 'KES',
                'is_active' => 1,
                'created_at' => '2025-12-18 06:32:36',
                'updated_at' => '2025-12-18 06:32:36',
            ],
            [
                'id' => 5,
                'name' => 'Pelatihan',
                'system_code' => ServiceSystemCode::CATEGORY_PLT,
                'short_code' => 'PLT',
                'is_active' => 1,
                'created_at' => '2025-12-18 06:32:36',
                'updated_at' => '2025-12-18 06:32:36',
            ],
        ];

        $categories = array_map(function (array $category) {
            unset($category['id']);

            return $category;
        }, $categories);

        ServiceCategory::upsert(
            $categories,
            ['system_code'],
            ['name', 'system_code', 'short_code', 'is_active', 'created_at', 'updated_at']
        );
    }
}
