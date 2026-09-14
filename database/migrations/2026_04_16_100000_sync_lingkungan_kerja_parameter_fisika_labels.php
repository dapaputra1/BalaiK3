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
            'GLTAR' => ['name' => 'Getaran Lengan Tangan', 'price' => '100000.00'],
            'GMTAR' => ['name' => 'Getaran Mekanik', 'price' => '125000.00'],
            'GSTAR' => ['name' => 'Getaran Seluruh Tubuh', 'price' => '100000.00'],
            'ISBB' => ['name' => 'Iklim Kerja - ISBB', 'price' => '75000.00'],
            'ISBB1' => ['name' => 'Iklim Kerja - ISBB & Kecepatan Udara', 'price' => '100000.00'],
            'DB' => ['name' => 'Kebisingan Sesaat', 'price' => '50000.00'],
            'NDISE' => ['name' => 'Noise Dosimeter', 'price' => '350000.00'],
            'PKP4M' => ['name' => 'Pemetaan Kebisingan (per 400 m2)', 'price' => '4000000.00'],
            'PLNCA' => ['name' => 'Pencahayaan Lokal', 'price' => '50000.00'],
            'PUP1M' => ['name' => 'Pencahayaan Umum (per 100 m2)', 'price' => '200000.00'],
            'MLDAN' => ['name' => 'Medan Listrik', 'price' => '100000.00'],
            'MMDAN' => ['name' => 'Medan Magnet', 'price' => '100000.00'],
            'ULTRA' => ['name' => 'Ultraviolet', 'price' => '100000.00'],
            'SDHTK' => ['name' => 'Sanitasi dan Higiene Tempat Kerja', 'price' => '500000.00'],
        ];

        foreach ($updates as $shortCode => $data) {
            DB::table('service_parameters')
                ->where('short_code', $shortCode)
                ->update([
                    'name' => $data['name'],
                    'price' => $data['price'],
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
            'DB' => ['name' => 'KEBISINGAN SESAAT', 'price' => '50000.00'],
            'NDISE' => ['name' => 'NOISE DOSIMETER', 'price' => '750000.00'],
            'PUP1M' => ['name' => 'PENCAHAYAAN UMUM - per 100 m2', 'price' => '200000.00'],
            'PLNCA' => ['name' => 'PENCAHAYAAN LOKAL', 'price' => '50000.00'],
            'ISBB' => ['name' => 'IKLIM KERJA - ISBB', 'price' => '75000.00'],
            'ISBB1' => ['name' => 'IKLIM KERJA - ISBB & KECEPATAN ALIRAN UDARA', 'price' => '100000.00'],
            'GLTAR' => ['name' => 'GETARAN LENGAN TANGAN', 'price' => '100000.00'],
            'GSTAR' => ['name' => 'GETARAN SELURUH TUBUH', 'price' => '100000.00'],
            'GMTAR' => ['name' => 'GETARAN MEKANIK', 'price' => '125000.00'],
            'MMDAN' => ['name' => 'MEDAN MAGNET', 'price' => '100000.00'],
            'MLDAN' => ['name' => 'MEDAN LISTRIK', 'price' => '100000.00'],
            'ULTRA' => ['name' => 'ULTRAVIOLET', 'price' => '100000.00'],
            'PKP4M' => ['name' => 'PEMETAAN KEBISINGAN - per 400 m2', 'price' => '4000000.00'],
            'SDHTK' => ['name' => 'SANITASI DAN HIGIENE TEMPAT KERJA', 'price' => '500000.00'],
        ];

        foreach ($rollback as $shortCode => $data) {
            DB::table('service_parameters')
                ->where('short_code', $shortCode)
                ->update([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'updated_at' => now(),
                ]);
        }
    }
};
