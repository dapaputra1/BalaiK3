<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->where('short_code', 'DB')
            ->update([
                'name' => 'Kebisingan Sesaat - Tanpa Analisis Frekuensi',
                'price' => 50000,
                'is_active' => 1,
                'updated_at' => $timestamp,
            ]);

        DB::table('service_parameters')->updateOrInsert(
            [
                'service_category_id' => 1,
                'short_code' => 'DBAF',
            ],
            [
                'name' => 'Kebisingan Sesaat - Dengan Analisis Frekuensi',
                'price' => 75000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );
    }

    public function down(): void
    {
        $timestamp = now();

        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->where('short_code', 'DB')
            ->update([
                'name' => 'Kebisingan Sesaat',
                'price' => 50000,
                'is_active' => 1,
                'updated_at' => $timestamp,
            ]);

        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->where('short_code', 'DBAF')
            ->delete();
    }
};
