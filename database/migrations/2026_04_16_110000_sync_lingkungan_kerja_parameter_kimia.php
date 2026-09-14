<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        $updates = [
            'BENZ' => ['name' => 'Benzene', 'price' => '250000.00', 'is_active' => true],
            'ETANO' => ['name' => 'Etanol', 'price' => '250000.00', 'is_active' => true],
            'METAN' => ['name' => 'Metanol', 'price' => '250000.00', 'is_active' => true],
            'TOLU' => ['name' => 'Toluene', 'price' => '250000.00', 'is_active' => true],
            'XELE' => ['name' => 'Xylene', 'price' => '250000.00', 'is_active' => true],
            'H2S' => ['name' => 'H2S', 'price' => '150000.00', 'is_active' => true],
            'NH3' => ['name' => 'NH3', 'price' => '50000.00', 'is_active' => true],
            'NO2' => ['name' => 'NO2', 'price' => '50000.00', 'is_active' => true],
            'SO2' => ['name' => 'SO2', 'price' => '150000.00', 'is_active' => true],
            'HC' => ['name' => 'HC', 'price' => '300000.00', 'is_active' => true],
            'OX' => ['name' => 'OX', 'price' => '150000.00', 'is_active' => true],
            'DPBUP' => ['name' => 'Debu Perseorangan', 'price' => '450000.00', 'is_active' => true],
            'DPM10' => ['name' => 'Debu PM10 (24 jam)', 'price' => '1250000.00', 'is_active' => true],
            'DPM25' => ['name' => 'Debu PM2,5 (24 jam)', 'price' => '1250000.00', 'is_active' => true],
            'DSBUS' => ['name' => 'Debu Silica', 'price' => '300000.00', 'is_active' => true],
            'KDLAR' => ['name' => 'Kadar Debu Logam (AAS)', 'price' => '150000.00', 'is_active' => true],
            'KDLAS' => ['name' => 'Kadar Debu Logam (AAS) - As', 'price' => '150000.00', 'is_active' => true],
            'KDLCD' => ['name' => 'Kadar Debu Logam (AAS) - Cd', 'price' => '150000.00', 'is_active' => true],
            'KDLCO' => ['name' => 'Kadar Debu Logam (AAS) - Co', 'price' => '150000.00', 'is_active' => true],
            'KDLCR' => ['name' => 'Kadar Debu Logam (AAS) - Cr', 'price' => '150000.00', 'is_active' => true],
            'KDLCU' => ['name' => 'Kadar Debu Logam (AAS) - Cu', 'price' => '150000.00', 'is_active' => true],
            'KDLHG' => ['name' => 'Kadar Debu Logam (AAS) - Hg', 'price' => '150000.00', 'is_active' => true],
            'KDLPB' => ['name' => 'Kadar Debu Logam (AAS) - Pb', 'price' => '150000.00', 'is_active' => true],
            'KDLSB' => ['name' => 'Kadar Debu Logam (AAS) - Sb', 'price' => '150000.00', 'is_active' => true],
            'KDLTL' => ['name' => 'Kadar Debu Logam (AAS) - Tl', 'price' => '150000.00', 'is_active' => true],
            'KDLZN' => ['name' => 'Kadar Debu Logam (AAS) - Zn', 'price' => '150000.00', 'is_active' => true],
            'KDTLR' => ['name' => 'Kadar Debu Total (LVS)', 'price' => '150000.00', 'is_active' => true],
            'SARAT' => ['name' => 'Serat Asbes', 'price' => '250000.00', 'is_active' => true],
            'PSAKU' => ['name' => 'Pengujian Serat Asbes - Kuantitatif', 'price' => '250000.00', 'is_active' => false],
            'PSAKL' => ['name' => 'Pengujian Serat Asbes - Kualitatif', 'price' => '250000.00', 'is_active' => false],
        ];

        foreach ($updates as $shortCode => $data) {
            DB::table('service_parameters')
                ->where('short_code', $shortCode)
                ->update([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'is_active' => $data['is_active'],
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        $rollback = [
            'ETANO' => ['name' => 'ETANOL', 'price' => '250000.00', 'is_active' => true],
            'METAN' => ['name' => 'METANOL', 'price' => '250000.00', 'is_active' => true],
            'NH3' => ['name' => 'NH3', 'price' => '150000.00', 'is_active' => true],
            'NO2' => ['name' => 'NO2', 'price' => '150000.00', 'is_active' => true],
            'HC' => ['name' => 'HC', 'price' => '250000.00', 'is_active' => true],
            'DPBUP' => ['name' => 'DEBU PESEORANGAN', 'price' => '450000.00', 'is_active' => true],
            'DPM10' => ['name' => 'DEBU PM 10 (24 JAM)', 'price' => '1250000.00', 'is_active' => false],
            'DPM25' => ['name' => 'DEBU PM 2,5 (24 JAM)', 'price' => '1250000.00', 'is_active' => true],
            'DSBUS' => ['name' => 'DEBU SILICA', 'price' => '300000.00', 'is_active' => true],
            'KDLAR' => ['name' => 'KADAR DEBU LOGAM (AAS)', 'price' => '150000.00', 'is_active' => false],
            'KDTLR' => ['name' => 'KADAR DEBU TOTAL (LVS)', 'price' => '150000.00', 'is_active' => true],
            'SARAT' => ['name' => 'SERAT ASBES', 'price' => '250000.00', 'is_active' => false],
            'PSAKU' => ['name' => 'Pengujian Serat Asbes - Kuantitatif', 'price' => '250000.00', 'is_active' => true],
            'PSAKL' => ['name' => 'Pengujian Serat Asbes - Kualitatif', 'price' => '250000.00', 'is_active' => true],
        ];

        foreach ($rollback as $shortCode => $data) {
            DB::table('service_parameters')
                ->where('short_code', $shortCode)
                ->update([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'is_active' => $data['is_active'],
                    'updated_at' => now(),
                ]);
        }
    }
};
