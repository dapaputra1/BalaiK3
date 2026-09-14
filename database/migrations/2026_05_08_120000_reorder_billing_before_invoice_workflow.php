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

        DB::table('workflow_steps')->where('kode', 'surat_tagihan')->update([
            'nama' => 'Surat Tagihan',
            'urutan' => 16,
        ]);
        DB::table('workflow_steps')->where('kode', 'kode_billing')->update([
            'nama' => 'Kode Billing',
            'urutan' => 17,
        ]);
        DB::table('workflow_steps')->where('kode', 'invoice')->update([
            'nama' => 'Kuitansi',
            'urutan' => 18,
        ]);
        DB::table('workflow_steps')->where('kode', 'penerbitan_suket')->update([
            'nama' => 'Penerbitan Suket',
            'urutan' => 19,
        ]);
        DB::table('workflow_steps')->where('kode', 'penyerahan_lhu')->update([
            'nama' => 'Penyerahan LHU',
            'urutan' => 20,
        ]);

        if (
            Schema::hasTable('draft_lhus')
            && Schema::hasColumn('draft_lhus', 'invoice_generated_at')
            && Schema::hasColumn('draft_lhus', 'invoice_generated_by')
            && Schema::hasColumn('draft_lhus', 'billing_verified_at')
        ) {
            DB::table('draft_lhus')
                ->whereNotNull('invoice_generated_at')
                ->whereNull('billing_verified_at')
                ->update([
                    'invoice_generated_at' => null,
                    'invoice_generated_by' => null,
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('workflow_steps')) {
            return;
        }

        DB::table('workflow_steps')->where('kode', 'surat_tagihan')->update([
            'nama' => 'Surat Tagihan',
            'urutan' => 16,
        ]);
        DB::table('workflow_steps')->where('kode', 'invoice')->update([
            'nama' => 'Invoice',
            'urutan' => 17,
        ]);
        DB::table('workflow_steps')->where('kode', 'kode_billing')->update([
            'nama' => 'Kode Billing',
            'urutan' => 18,
        ]);
        DB::table('workflow_steps')->where('kode', 'penerbitan_suket')->update([
            'nama' => 'Penerbitan Suket',
            'urutan' => 19,
        ]);
        DB::table('workflow_steps')->where('kode', 'penyerahan_lhu')->update([
            'nama' => 'Penyerahan LHU',
            'urutan' => 20,
        ]);
    }
};
