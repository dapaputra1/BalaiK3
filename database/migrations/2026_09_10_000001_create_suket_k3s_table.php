<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('suket_k3s')) {
            Schema::create('suket_k3s', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('permohonan_id')->nullable()->index();
                $table->string('nomor_order')->index();
                $table->unsignedTinyInteger('status_tahap')->default(1)->index();
                // 1: Permohonan
                // 2: Evaluasi Dokumen
                // 3: Penyusunan Laporan/Suket
                // 4: Penandatanganan Surat Keterangan
                // 5: Penerbitan Laporan/Suket
                // 6: Kirim ke Pelanggan
                
                $table->string('perusahaan_nama')->nullable();
                $table->string('lokasi')->nullable();
                
                $table->text('catatan')->nullable();
                $table->text('catatan_evaluasi')->nullable();
                
                $table->string('draft_file_path')->nullable();
                $table->string('draft_file_name')->nullable();
                
                $table->string('signed_file_path')->nullable();
                $table->string('signed_file_name')->nullable();
                $table->timestamp('signed_at')->nullable();
                $table->unsignedBigInteger('signed_by')->nullable();
                
                $table->timestamp('published_at')->nullable();
                $table->unsignedBigInteger('published_by')->nullable();
                
                $table->timestamp('sent_to_customer_at')->nullable();
                $table->unsignedBigInteger('sent_to_customer_by')->nullable();
                $table->string('resi_pengiriman')->nullable();
                $table->string('metode_pengiriman')->nullable();
                
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('suket_k3s');
    }
};
