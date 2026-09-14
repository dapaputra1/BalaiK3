<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        DB::table('service_parameters')
            ->where('short_code', 'SARAT')
            ->update([
                'name' => 'Serat Asbes',
                'price' => '250000.00',
                'is_active' => false,
                'updated_at' => now(),
            ]);

        foreach ([
            'PSAKU' => 'Pengujian Serat Asbes - Kuantitatif',
            'PSAKL' => 'Pengujian Serat Asbes - Kualitatif',
        ] as $shortCode => $name) {
            DB::table('service_parameters')
                ->where('short_code', $shortCode)
                ->update([
                    'name' => $name,
                    'price' => '250000.00',
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        DB::table('service_parameters')
            ->where('short_code', 'SARAT')
            ->update([
                'name' => 'Serat Asbes',
                'price' => '250000.00',
                'is_active' => true,
                'updated_at' => now(),
            ]);

        foreach ([
            'PSAKU' => 'Pengujian Serat Asbes - Kuantitatif',
            'PSAKL' => 'Pengujian Serat Asbes - Kualitatif',
        ] as $shortCode => $name) {
            DB::table('service_parameters')
                ->where('short_code', $shortCode)
                ->update([
                    'name' => $name,
                    'price' => '250000.00',
                    'is_active' => false,
                    'updated_at' => now(),
                ]);
        }
    }
};
