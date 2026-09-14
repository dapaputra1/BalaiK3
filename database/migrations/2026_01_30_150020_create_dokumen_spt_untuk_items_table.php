<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_spt_untuk_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spt_id');
            $table->string('nomor', 20)->nullable();
            $table->text('uraian');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->foreign('spt_id')->references('id')->on('dokumen_spts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_spt_untuk_items');
    }
};
