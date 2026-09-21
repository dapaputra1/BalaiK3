<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            // Tahap 6: Surat Tagihan
            $table->string('surat_tagihan_file_path')->nullable()->after('published_by');
            $table->string('surat_tagihan_file_name')->nullable()->after('surat_tagihan_file_path');
            $table->decimal('surat_tagihan_nominal', 15, 2)->nullable()->after('surat_tagihan_file_name');
            $table->timestamp('surat_tagihan_sent_at')->nullable()->after('surat_tagihan_nominal');
            $table->unsignedBigInteger('surat_tagihan_sent_by')->nullable()->after('surat_tagihan_sent_at');
            $table->timestamp('surat_tagihan_acc_at')->nullable()->after('surat_tagihan_sent_by');
            $table->unsignedBigInteger('surat_tagihan_acc_by')->nullable()->after('surat_tagihan_acc_at');

            // Tahap 7: Kode Billing
            $table->string('billing_kode', 100)->nullable()->after('surat_tagihan_acc_by');
            $table->string('billing_file_path')->nullable()->after('billing_kode');
            $table->string('billing_file_name')->nullable()->after('billing_file_path');
            $table->timestamp('billing_sent_at')->nullable()->after('billing_file_name');
            $table->unsignedBigInteger('billing_sent_by')->nullable()->after('billing_sent_at');
            $table->dateTime('billing_expires_at')->nullable()->after('billing_sent_by');
            $table->string('billing_proof_path')->nullable()->after('billing_expires_at');
            $table->string('billing_proof_name')->nullable()->after('billing_proof_path');
            $table->timestamp('billing_paid_at')->nullable()->after('billing_proof_name');
            $table->timestamp('billing_verified_at')->nullable()->after('billing_paid_at');
            $table->unsignedBigInteger('billing_verified_by')->nullable()->after('billing_verified_at');

            // Tahap 8: Kuitansi
            $table->string('kuitansi_nomor', 100)->nullable()->after('billing_verified_by');
            $table->string('kuitansi_file_path')->nullable()->after('kuitansi_nomor');
            $table->string('kuitansi_file_name')->nullable()->after('kuitansi_file_path');
            $table->timestamp('kuitansi_generated_at')->nullable()->after('kuitansi_file_name');
            $table->unsignedBigInteger('kuitansi_generated_by')->nullable()->after('kuitansi_generated_at');
            $table->timestamp('kuitansi_sent_at')->nullable()->after('kuitansi_generated_by');
            $table->unsignedBigInteger('kuitansi_sent_by')->nullable()->after('kuitansi_sent_at');
        });

        // Migrasikan berkas Suket eksisting yang sudah berada di Tahap 6 (Penyerahan Suket lama) menjadi Tahap 9
        DB::table('suket_k3s')
            ->where('status_tahap', 6)
            ->update(['status_tahap' => 9]);

        // Migrasikan riwayat status_tahap jika ada
        DB::table('suket_k3_histories')
            ->where('stage_after', 6)
            ->where('action', 'sent_to_customer')
            ->update(['stage_after' => 9]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('suket_k3s')
            ->where('status_tahap', 9)
            ->update(['status_tahap' => 6]);

        DB::table('suket_k3_histories')
            ->where('stage_after', 9)
            ->where('action', 'sent_to_customer')
            ->update(['stage_after' => 6]);

        Schema::table('suket_k3s', function (Blueprint $table) {
            $table->dropColumn([
                'surat_tagihan_file_path',
                'surat_tagihan_file_name',
                'surat_tagihan_nominal',
                'surat_tagihan_sent_at',
                'surat_tagihan_sent_by',
                'surat_tagihan_acc_at',
                'surat_tagihan_acc_by',
                'billing_kode',
                'billing_file_path',
                'billing_file_name',
                'billing_sent_at',
                'billing_sent_by',
                'billing_expires_at',
                'billing_proof_path',
                'billing_proof_name',
                'billing_paid_at',
                'billing_verified_at',
                'billing_verified_by',
                'kuitansi_nomor',
                'kuitansi_file_path',
                'kuitansi_file_name',
                'kuitansi_generated_at',
                'kuitansi_generated_by',
                'kuitansi_sent_at',
                'kuitansi_sent_by',
            ]);
        });
    }
};
