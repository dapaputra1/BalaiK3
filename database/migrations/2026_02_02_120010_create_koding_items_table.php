<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('koding_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('koding_id')->constrained('kodings')->cascadeOnDelete();
            $table->foreignId('pengujian_dokumen_id')->constrained('pengujian_dokumen')->cascadeOnDelete();
            $table->string('kode')->nullable();
            $table->timestamps();

            $table->unique(['koding_id', 'pengujian_dokumen_id'], 'koding_items_unique_doc');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('koding_items');
    }
};
