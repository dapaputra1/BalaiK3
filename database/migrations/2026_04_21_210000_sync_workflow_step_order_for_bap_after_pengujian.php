<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('workflow_steps')) {
            return;
        }

        $orders = [
            'pengujian' => 6,
            'alur_bap' => 7,
            'verifikasi_pengujian' => 8,
            'verifikasi_pcu' => 9,
            'koding' => 10,
        ];

        foreach ($orders as $kode => $urutan) {
            DB::table('workflow_steps')
                ->where('kode', $kode)
                ->update(['urutan' => $urutan]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('workflow_steps')) {
            return;
        }

        $orders = [
            'pengujian' => 6,
            'verifikasi_pengujian' => 7,
            'verifikasi_pcu' => 8,
            'alur_bap' => 9,
            'koding' => 10,
        ];

        foreach ($orders as $kode => $urutan) {
            DB::table('workflow_steps')
                ->where('kode', $kode)
                ->update(['urutan' => $urutan]);
        }
    }
};
