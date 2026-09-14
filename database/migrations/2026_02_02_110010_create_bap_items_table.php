<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bap_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bap_id')->constrained('baps')->cascadeOnDelete();
            $table->foreignId('pengujian_dokumen_parameter_id')
                ->constrained('pengujian_dokumen_parameters')
                ->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['bap_id', 'pengujian_dokumen_parameter_id'], 'bap_items_unique_param');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bap_items');
    }
};
