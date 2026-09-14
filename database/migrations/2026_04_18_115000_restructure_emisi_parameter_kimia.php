<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();

        $priceUpdates = [
            'CL2' => 350000,
            'H2S2' => 350000,
            'HCL' => 350000,
            'HF' => 350000,
            'HG' => 350000,
            'NH32' => 350000,
            'SO22' => 350000,
            'NO22' => 350000,
            'OPASI' => 450000,
        ];

        foreach ($priceUpdates as $shortCode => $price) {
            DB::table('service_parameters')
                ->where('service_category_id', 3)
                ->where('short_code', $shortCode)
                ->update([
                    'price' => $price,
                    'updated_at' => $timestamp,
                ]);
        }

        DB::table('service_parameters')->updateOrInsert(
            ['short_code' => 'COSTB'],
            [
                'service_category_id' => 3,
                'name' => 'CO',
                'price' => 350000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::table('service_parameters')->updateOrInsert(
            ['short_code' => 'CO2STB'],
            [
                'service_category_id' => 3,
                'name' => 'CO2',
                'price' => 350000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::table('service_parameters')->updateOrInsert(
            ['short_code' => 'HCEM'],
            [
                'service_category_id' => 3,
                'name' => 'HC',
                'price' => 450000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );
    }

    public function down(): void
    {
        $timestamp = now();

        $priceRollbacks = [
            'CL2' => 150000,
            'H2S2' => 150000,
            'HCL' => 150000,
            'HF' => 150000,
            'HG' => 150000,
            'NH32' => 150000,
            'SO22' => 150000,
            'NO22' => 150000,
            'OPASI' => 350000,
        ];

        foreach ($priceRollbacks as $shortCode => $price) {
            DB::table('service_parameters')
                ->where('service_category_id', 3)
                ->where('short_code', $shortCode)
                ->update([
                    'price' => $price,
                    'updated_at' => $timestamp,
                ]);
        }

        DB::table('service_parameters')
            ->whereIn('short_code', ['COSTB', 'CO2STB', 'HCEM'])
            ->delete();
    }
};
