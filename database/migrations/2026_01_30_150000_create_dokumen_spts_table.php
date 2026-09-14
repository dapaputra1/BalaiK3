<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_spts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permohonan_id');
            $table->string('nomor_surat', 150);
            $table->string('tempat_terbit', 100)->default('Surabaya');
            $table->date('tanggal_terbit')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->unique('permohonan_id');
            $table->foreign('permohonan_id')->references('id')->on('permohonans')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_spts');
    }
};
