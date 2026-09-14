<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $hasFormula = DB::table('absorbance_formulas')
            ->where('parameter_key', 'HG')
            ->exists();

        if (!$hasFormula) {
            DB::table('absorbance_formulas')->insert([
                'parameter_key' => 'HG',
                'intercept' => 0.003600000000,
                'slope' => 277.777777777778,
                'effective_date' => $now->toDateString(),
                'notes' => 'Nilai awal acuan Excel Merkuri (Hg).',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('absorbance_formulas')
            ->where('parameter_key', 'HG')
            ->delete();
    }
};
