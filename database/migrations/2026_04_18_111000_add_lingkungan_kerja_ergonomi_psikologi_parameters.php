<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        DB::table('service_parameters')->updateOrInsert(
            [
                'short_code' => 'ANTRO',
            ],
            [
                'service_category_id' => 1,
                'name' => 'Pengukuran Antropometri Tenaga Kerja dan Rekomendasi Alat dan Sarana Kerja',
                'price' => 50000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::table('service_parameters')->updateOrInsert(
            [
                'short_code' => 'OBSERG',
            ],
            [
                'service_category_id' => 1,
                'name' => 'Observasi Ergonomi',
                'price' => 250000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::table('service_parameters')->updateOrInsert(
            [
                'short_code' => 'UKKJA',
            ],
            [
                'service_category_id' => 1,
                'name' => 'Uji Kelelahan Kerja',
                'price' => 50000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );
    }

    public function down(): void
    {
        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->whereIn('short_code', ['ANTRO', 'OBSERG', 'UKKJA'])
            ->delete();
    }
};
