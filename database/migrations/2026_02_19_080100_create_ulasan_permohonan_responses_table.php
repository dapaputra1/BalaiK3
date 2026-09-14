<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ulasan_permohonan_responses');

        Schema::create('ulasan_permohonan_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                ->constrained('ulasan_permohonan_questions')
                ->cascadeOnDelete();
            $table->foreignId('permohonan_id')
                ->nullable()
                ->constrained('permohonans')
                ->nullOnDelete();
            $table->integer('user_id')->nullable();
            $table->unsignedTinyInteger('rating_value')->nullable();
            $table->text('text_answer')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['question_id', 'created_at']);
            $table->index(['permohonan_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan_permohonan_responses');
    }
};
