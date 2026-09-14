<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $hasFormula = DB::table('absorbance_formulas')
            ->where('parameter_key', 'SB')
            ->exists();

        if (!$hasFormula) {
            DB::table('absorbance_formulas')->insert([
                'parameter_key' => 'SB',
                'intercept' => 0.003500000000,
                'slope' => 285.714285714286,
                'effective_date' => $now->toDateString(),
                'notes' => 'Nilai awal acuan Excel Antimon (Sb).',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('absorbance_formulas')
            ->where('parameter_key', 'SB')
            ->delete();
    }
};
