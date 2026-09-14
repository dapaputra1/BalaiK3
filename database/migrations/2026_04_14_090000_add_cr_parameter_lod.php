<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('parameter_lods')->updateOrInsert(
            ['service_parameter_id' => 96],
            [
                'kons' => 0.0024,
                'vol' => 10.0,
                'waktu' => 60.0,
                'fr' => 4.0,
                'sk' => 25.0,
                'pm' => 760.0,
                'factor_ppm' => 0.0,
                'factor_ugm3' => 1000.0,
                'sample_kons' => 0.0024,
                'sample_vol' => 10.0,
                'sample_waktu' => 60.0,
                'sample_fr' => 4.0,
                'sample_sk' => 25.0,
                'sample_pm' => 760.0,
                'sample_ppm' => 0.0,
                'sample_ugm3' => 0.1000,
                'notes' => 'Nilai awal LOD acuan Excel Kromium (Cr).',
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('parameter_lods')
            ->where('service_parameter_id', 96)
            ->delete();
    }
};
