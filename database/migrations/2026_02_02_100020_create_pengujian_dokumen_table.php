<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengujian_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_id')->constrained('pengujian_lokasi')->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengujian_dokumen');
    }
};
