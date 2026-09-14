<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $hasFormula = DB::table('absorbance_formulas')
            ->where('parameter_key', 'CR')
            ->exists();

        if (!$hasFormula) {
            DB::table('absorbance_formulas')->insert([
                'parameter_key' => 'CR',
                'intercept' => 0.024800000000,
                'slope' => 40.322580645160,
                'effective_date' => $now->toDateString(),
                'notes' => 'Nilai awal acuan Excel Kromium (Cr).',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('absorbance_formulas')
            ->where('parameter_key', 'CR')
            ->delete();
    }
};
