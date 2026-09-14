<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_parameters')
            ->where('service_category_id', 3)
            ->whereIn('short_code', ['COSTB', 'CO2STB', 'CL2', 'H2S2', 'HCL', 'HF', 'NH32', 'HG', 'SO22', 'NO22'])
            ->update([
                'price' => 150000,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('service_parameters')
            ->where('service_category_id', 3)
            ->whereIn('short_code', ['COSTB', 'CO2STB', 'CL2', 'H2S2', 'HCL', 'HF', 'NH32', 'HG', 'SO22', 'NO22'])
            ->update([
                'price' => 350000,
                'updated_at' => now(),
            ]);
    }
};
