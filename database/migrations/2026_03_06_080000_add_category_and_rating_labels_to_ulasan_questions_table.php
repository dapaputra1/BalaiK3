<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ulasan_permohonan_questions', function (Blueprint $table) {
            $table->enum('category', ['ikm', 'ikk'])->default('ikm')->after('type');
            $table->json('rating_labels')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('ulasan_permohonan_questions', function (Blueprint $table) {
            $table->dropColumn(['category', 'rating_labels']);
        });
    }
};
