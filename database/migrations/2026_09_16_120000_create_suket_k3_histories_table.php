<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('suket_k3_histories')) {
            Schema::create('suket_k3_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('suket_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('action', 50)->index();
                $table->unsignedTinyInteger('stage_before')->nullable();
                $table->unsignedTinyInteger('stage_after')->nullable();
                $table->string('nomor_surat')->nullable();
                $table->text('catatan')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('suket_k3_histories');
    }
};
