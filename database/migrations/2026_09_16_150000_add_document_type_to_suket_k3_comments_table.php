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
        Schema::table('suket_k3_comments', function (Blueprint $table) {
            $table->string('document_type', 30)->default('lhu')->after('tipe');
            $table->index(['suket_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suket_k3_comments', function (Blueprint $table) {
            $table->dropIndex(['suket_id', 'document_type']);
            $table->dropColumn('document_type');
        });
    }
};
