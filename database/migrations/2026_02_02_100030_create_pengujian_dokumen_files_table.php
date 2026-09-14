<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengujian_dokumen_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_id')->constrained('pengujian_dokumen')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->integer('uploaded_by')->nullable();
            $table->timestamps();
        });

        Schema::table('pengujian_dokumen_files', function (Blueprint $table) {
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengujian_dokumen_files', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });
        Schema::dropIfExists('pengujian_dokumen_files');
    }
};
