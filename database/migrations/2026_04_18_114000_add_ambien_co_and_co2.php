<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        DB::table('service_parameters')
            ->where('service_category_id', 2)
            ->whereIn('short_code', ['H2S1', 'NH31', 'NO21', 'SO21'])
            ->update([
                'price' => 150000,
                'is_active' => 1,
                'updated_at' => $timestamp,
            ]);

        DB::table('service_parameters')->updateOrInsert(
            [
                'short_code' => 'COAM',
            ],
            [
                'service_category_id' => 2,
                'name' => 'CO',
                'price' => 150000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::table('service_parameters')->updateOrInsert(
            [
                'short_code' => 'CO2AM',
            ],
            [
                'service_category_id' => 2,
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
            ->where('service_category_id', 2)
            ->where('short_code', 'NH31')
            ->update([
                'price' => 50000,
                'updated_at' => $timestamp,
            ]);

        DB::table('service_parameters')
            ->where('service_category_id', 2)
            ->where('short_code', 'NO21')
            ->update([
                'price' => 50000,
                'updated_at' => $timestamp,
            ]);

        DB::table('service_parameters')
            ->whereIn('short_code', ['COAM', 'CO2AM'])
            ->delete();
    }
};
