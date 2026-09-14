<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prepanalisa_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prepanalisa_id')->constrained('prepanalisisas')->cascadeOnDelete();
            $table->foreignId('koding_item_id')->constrained('koding_items')->cascadeOnDelete();
            $table->foreignId('pengujian_dokumen_parameter_id')
                ->constrained('pengujian_dokumen_parameters')
                ->cascadeOnDelete();
            $table->foreignId('service_parameter_id')->constrained('service_parameters')->cascadeOnDelete();
            $table->string('kode_koding')->nullable();
            $table->json('data_skpm')->nullable();
            $table->json('data_hasil_baca')->nullable();
            $table->json('data_hasil_perhitungan')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->unique(
                ['prepanalisa_id', 'koding_item_id', 'pengujian_dokumen_parameter_id'],
                'prepanalisa_items_unique'
            );
        });

        Schema::table('prepanalisa_items', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prepanalisa_items', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });
        Schema::dropIfExists('prepanalisa_items');
    }
};
