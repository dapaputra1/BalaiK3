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
        Schema::table('ergo_reba_scores', function (Blueprint $table) {
            $table->decimal('mmh_step2_score', 4, 1)->default(0)->after('lower_body_score');
            $table->decimal('mmh_step3_score', 4, 1)->default(0)->after('mmh_step2_score');
            $table->decimal('mmh_total_score', 4, 1)->default(0)->after('mmh_step3_score');
            $table->json('mmh_step3_items')->nullable()->after('mmh_total_score');
        });

        Schema::table('ergo_assessments', function (Blueprint $table) {
            $table->string('assessor_role')->nullable()->default('Penguji K3/Ahli K3 Lingkungan Kerja Muda/ Madya/Utama')->after('existing_control');
            $table->string('assessor_name')->nullable()->after('assessor_role');
            $table->string('assessor_nip')->nullable()->after('assessor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ergo_reba_scores', function (Blueprint $table) {
            $table->dropColumn(['mmh_step2_score', 'mmh_step3_score', 'mmh_total_score', 'mmh_step3_items']);
        });

        Schema::table('ergo_assessments', function (Blueprint $table) {
            $table->dropColumn(['assessor_role', 'assessor_name', 'assessor_nip']);
        });
    }
};
