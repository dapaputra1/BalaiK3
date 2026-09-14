<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengujian_lokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengujian_id')->constrained('pengujian')->cascadeOnDelete();
            $table->string('nama_lokasi');
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengujian_lokasi');
    }
};
