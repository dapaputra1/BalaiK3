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
            ->whereIn('short_code', ['H2S', 'NH3', 'NO2', 'SO2', 'OX'])
            ->update([
                'price' => 150000,
                'is_active' => 1,
                'updated_at' => $timestamp,
            ]);

        DB::table('service_parameters')->updateOrInsert(
            [
                'service_category_id' => 1,
                'short_code' => 'CL2LK',
            ],
            [
                'name' => 'Cl2',
                'price' => 150000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::table('service_parameters')->updateOrInsert(
            [
                'service_category_id' => 1,
                'short_code' => 'COLK',
            ],
            [
                'name' => 'CO',
                'price' => 150000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::table('service_parameters')->updateOrInsert(
            [
                'service_category_id' => 1,
                'short_code' => 'CO2LK',
            ],
            [
                'name' => 'CO2',
                'price' => 150000,
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
            ->where('short_code', 'NH3')
            ->update([
                'price' => 50000,
                'updated_at' => $timestamp,
            ]);

        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->where('short_code', 'NO2')
            ->update([
                'price' => 50000,
                'updated_at' => $timestamp,
            ]);

        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->whereIn('short_code', ['CL2LK', 'COLK', 'CO2LK'])
            ->delete();
    }
};
