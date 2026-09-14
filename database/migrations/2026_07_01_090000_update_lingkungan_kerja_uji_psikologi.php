<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_parameters')
            ->where('short_code', 'UKKJA')
            ->update([
                'name' => 'Uji Psikologi Kerja',
                'price' => 150000,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('service_parameters')
            ->where('short_code', 'UKKJA')
            ->update([
                'name' => 'Uji Kelelahan Kerja',
                'price' => 50000,
                'updated_at' => now(),
            ]);
    }
};
