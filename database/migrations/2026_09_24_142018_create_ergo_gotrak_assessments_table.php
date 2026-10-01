<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ergo_gotrak_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('ergo_assessments')->onDelete('cascade');
            $table->unsignedBigInteger('worker_id')->nullable();
            $table->string('body_part_key');          // leher, siku, lengan, tangan, dll.
            $table->string('body_part_name');         // LEHER, SIKU, LENGAN, dll.
            $table->string('side')->nullable();       // Kanan, Kiri, Keduanya, atau null
            $table->unsignedTinyInteger('frequency')->default(1);  // 1: Tidak pernah, 2: Terkadang, 3: Sering, 4: Selalu
            $table->unsignedTinyInteger('severity')->default(1);   // 1: Tidak ada masalah, 2: Tidak nyaman, 3: Sakit, 4: Sakit parah
            $table->unsignedTinyInteger('score')->default(1);      // frequency * severity (1 - 16)
            $table->string('risk_category')->default('Risiko Rendah'); // Risiko Rendah (1-4), Risiko Sedang (6), Risiko Tinggi (>=8)
            $table->text('cause_description')->nullable(); // Bagian pekerjaan penyebab keluhan jika skor >= 8
            $table->timestamps();

            $table->index(['assessment_id', 'body_part_key']);
        });

        Schema::table('ergo_assessments', function (Blueprint $table) {
            $table->text('gotrak_summary_narrative')->nullable()->after('lhu_analysis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ergo_assessments', function (Blueprint $table) {
            $table->dropColumn('gotrak_summary_narrative');
        });
        Schema::dropIfExists('ergo_gotrak_assessments');
    }
};
