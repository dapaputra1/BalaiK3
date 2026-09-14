<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('service_categories') || !Schema::hasTable('service_parameters')) {
            return;
        }

        $lingkunganKerjaCategoryId = DB::table('service_categories')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) = ?', ['lingkungan kerja'])
                    ->orWhereRaw('LOWER(short_code) = ?', ['lk']);
            })
            ->value('id');

        if (!$lingkunganKerjaCategoryId) {
            return;
        }

        DB::table('service_parameters')
            ->where('service_category_id', $lingkunganKerjaCategoryId)
            ->whereRaw('LOWER(name) = ?', ['serat asbes'])
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        $parameters = [
            [
                'short_code' => 'PSAKU',
                'name' => 'Pengujian Serat Asbes - Kuantitatif',
            ],
            [
                'short_code' => 'PSAKL',
                'name' => 'Pengujian Serat Asbes - Kualitatif',
            ],
        ];

        foreach ($parameters as $parameter) {
            $existingParameter = DB::table('service_parameters')
                ->where('short_code', $parameter['short_code'])
                ->first();

            if ($existingParameter) {
                DB::table('service_parameters')
                    ->where('short_code', $parameter['short_code'])
                    ->update([
                        'service_category_id' => $lingkunganKerjaCategoryId,
                        'name' => $parameter['name'],
                        'price' => '250000.00',
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('service_parameters')->insert([
                'service_category_id' => $lingkunganKerjaCategoryId,
                'name' => $parameter['name'],
                'short_code' => $parameter['short_code'],
                'price' => '250000.00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_categories') || !Schema::hasTable('service_parameters')) {
            return;
        }

        $lingkunganKerjaCategoryId = DB::table('service_categories')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) = ?', ['lingkungan kerja'])
                    ->orWhereRaw('LOWER(short_code) = ?', ['lk']);
            })
            ->value('id');

        if (!$lingkunganKerjaCategoryId) {
            return;
        }

        DB::table('service_parameters')
            ->whereIn('short_code', ['PSAKU', 'PSAKL'])
            ->delete();

        DB::table('service_parameters')
            ->where('service_category_id', $lingkunganKerjaCategoryId)
            ->whereRaw('LOWER(name) = ?', ['serat asbes'])
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }
};
