<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $hasFormula = DB::table('absorbance_formulas')
            ->where('parameter_key', 'ZN')
            ->exists();

        if (!$hasFormula) {
            DB::table('absorbance_formulas')->insert([
                'parameter_key' => 'ZN',
                'intercept' => 0.145400000000,
                'slope' => 6.877579092160,
                'effective_date' => $now->toDateString(),
                'notes' => 'Nilai awal acuan Excel Seng (Zn).',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('absorbance_formulas')
            ->where('parameter_key', 'ZN')
            ->delete();
    }
};
