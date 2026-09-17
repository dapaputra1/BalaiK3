<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            if (!Schema::hasColumn('suket_k3s', 'catatan_revisi_pemohon')) {
                $table->text('catatan_revisi_pemohon')->nullable()->after('catatan_evaluasi');
            }
            if (!Schema::hasColumn('suket_k3s', 'revisi_pemohon_at')) {
                $table->timestamp('revisi_pemohon_at')->nullable()->after('catatan_revisi_pemohon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            $columns = ['catatan_revisi_pemohon', 'revisi_pemohon_at'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('suket_k3s', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
