<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            if (!Schema::hasColumn('suket_k3s', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('suket_k3s', 'faktor_k3')) {
                $table->json('faktor_k3')->nullable()->after('status_tahap');
            }
            if (!Schema::hasColumn('suket_k3s', 'lhu_source')) {
                $table->string('lhu_source', 20)->default('auto')->after('faktor_k3');
            }
            if (!Schema::hasColumn('suket_k3s', 'lhu_file_path')) {
                $table->string('lhu_file_path')->nullable()->after('lhu_source');
                $table->string('lhu_file_name')->nullable()->after('lhu_file_path');
            }
            if (!Schema::hasColumn('suket_k3s', 'foto_pengujian_path')) {
                $table->string('foto_pengujian_path')->nullable()->after('lhu_file_name');
                $table->string('foto_pengujian_name')->nullable()->after('foto_pengujian_path');
            }
            if (!Schema::hasColumn('suket_k3s', 'denah_lokasi_path')) {
                $table->string('denah_lokasi_path')->nullable()->after('foto_pengujian_name');
                $table->string('denah_lokasi_name')->nullable()->after('denah_lokasi_path');
            }
            if (!Schema::hasColumn('suket_k3s', 'qc_status')) {
                $table->string('qc_status', 30)->default('pending')->after('catatan_evaluasi');
                $table->text('qc_note')->nullable()->after('qc_status');
                $table->unsignedBigInteger('qc_by')->nullable()->after('qc_note');
                $table->timestamp('qc_at')->nullable()->after('qc_by');
            }
            if (!Schema::hasColumn('suket_k3s', 'nomor_surat')) {
                $table->string('nomor_surat')->nullable()->after('signed_file_name');
                $table->date('tanggal_surat')->nullable()->after('nomor_surat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            $columns = [
                'user_id',
                'faktor_k3',
                'lhu_source',
                'lhu_file_path',
                'lhu_file_name',
                'foto_pengujian_path',
                'foto_pengujian_name',
                'denah_lokasi_path',
                'denah_lokasi_name',
                'qc_status',
                'qc_note',
                'qc_by',
                'qc_at',
                'nomor_surat',
                'tanggal_surat',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('suket_k3s', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
