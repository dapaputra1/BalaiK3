<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ergo_assessments', function (Blueprint $table) {
            $table->string('lhu_doc_number')->nullable();
            $table->string('company_pic')->nullable();
            $table->text('lhu_analysis')->nullable();
            $table->text('lhu_conclusion')->nullable();
            $table->longText('lhu_recommendation')->nullable();
            $table->string('signer_name')->nullable()->default('OKTOFA S. PAMUNGKAS S.T., M.Kes');
            $table->string('signer_nip')->nullable()->default('19791003 200912 1 002');
            $table->string('signer_position')->nullable()->default('Manajer Teknis');
        });
    }

    public function down(): void
    {
        Schema::table('ergo_assessments', function (Blueprint $table) {
            $table->dropColumn([
                'lhu_doc_number',
                'company_pic',
                'lhu_analysis',
                'lhu_conclusion',
                'lhu_recommendation',
                'signer_name',
                'signer_nip',
                'signer_position',
            ]);
        });
    }
};
