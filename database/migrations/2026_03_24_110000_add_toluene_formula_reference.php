<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('absorbance_formulas')
            ->where('parameter_key', 'TOLUENE')
            ->exists();

        if (!$exists) {
            DB::table('absorbance_formulas')->insert([
                'parameter_key' => 'TOLUENE',
                'intercept' => 4714408.000000000000,
                'slope' => 0.000000212116,
                'effective_date' => now()->toDateString(),
                'notes' => 'Nilai awal acuan Excel BTX.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('absorbance_formulas')
            ->where('parameter_key', 'TOLUENE')
            ->delete();
    }
};
