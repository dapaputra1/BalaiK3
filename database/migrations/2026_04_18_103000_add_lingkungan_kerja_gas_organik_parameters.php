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
                'service_category_id' => 1,
                'short_code' => 'HCOH',
            ],
            [
                'name' => 'HCOH',
                'price' => 250000,
                'is_active' => 1,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]
        );

        DB::table('service_parameters')->updateOrInsert(
            [
                'service_category_id' => 1,
                'short_code' => 'MEK',
            ],
            [
                'name' => 'Metil Etil Keton',
                'price' => 250000,
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
            ->whereIn('short_code', ['HCOH', 'MEK'])
            ->delete();
    }
};
