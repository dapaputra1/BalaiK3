<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE absorbance_formulas MODIFY intercept DECIMAL(20,12) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE absorbance_formulas MODIFY slope DECIMAL(18,12) NOT NULL');
        }

        $exists = DB::table('absorbance_formulas')
            ->where('parameter_key', 'BENZENE')
            ->exists();

        if (!$exists) {
            DB::table('absorbance_formulas')->insert([
                'parameter_key' => 'BENZENE',
                'intercept' => 3625144.000000000000,
                'slope' => 0.000000275851,
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
            ->where('parameter_key', 'BENZENE')
            ->delete();

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE absorbance_formulas MODIFY intercept DECIMAL(12,6) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE absorbance_formulas MODIFY slope DECIMAL(12,6) NOT NULL');
        }
    }
};
