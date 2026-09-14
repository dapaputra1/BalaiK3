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
            ->where('short_code', 'PMPGU')
            ->update([
                'name' => 'Pengujian Mikroba (Patogen)',
                'price' => '1500000.00',
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        DB::table('service_parameters')
            ->where('short_code', 'PMPGU')
            ->update([
                'name' => 'PENGUJIAN MIKROBA (PATOGEN)',
                'price' => '1500000.00',
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
};
