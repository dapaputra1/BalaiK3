<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengujian_dokumen_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('pengujian_dokumen')->cascadeOnDelete();
            $table->foreignId('service_parameter_id')->constrained('service_parameters')->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->boolean('is_direct')->default(false);
            $table->boolean('is_sesuai')->default(true);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengujian_dokumen_parameters');
    }
};
