<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $hasFormula = DB::table('absorbance_formulas')
            ->where('parameter_key', 'PB')
            ->exists();

        if (!$hasFormula) {
            DB::table('absorbance_formulas')->insert([
                'parameter_key' => 'PB',
                'intercept' => 0.000000000000,
                'slope' => 222.222000000000,
                'effective_date' => $now->toDateString(),
                'notes' => 'Nilai awal acuan Excel Timbal (Pb).',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $hasParameter = DB::table('service_parameters')->where('id', 91)->exists();
        $hasLod = DB::table('parameter_lods')
            ->where('service_parameter_id', 91)
            ->exists();

        if ($hasParameter && !$hasLod) {
            DB::table('parameter_lods')->insert([
                'service_parameter_id' => 91,
                'kons' => 0.0095,
                'vol' => 15.0,
                'waktu' => 30,
                'fr' => 500.0,
                'sk' => 25.0,
                'pm' => 760,
                'factor_ppm' => 0.0,
                'factor_ugm3' => 1000.0,
                'sample_kons' => 0.0095,
                'sample_vol' => 15.0,
                'sample_waktu' => 30,
                'sample_fr' => 500.0,
                'sample_sk' => 25.0,
                'sample_pm' => 760,
                'sample_ppm' => 0.0,
                'sample_ugm3' => 0.0095,
                'notes' => 'Nilai awal LOD acuan Excel Timbal (Pb).',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('parameter_lods')
            ->where('service_parameter_id', 91)
            ->delete();

        DB::table('absorbance_formulas')
            ->where('parameter_key', 'PB')
            ->delete();
    }
};
