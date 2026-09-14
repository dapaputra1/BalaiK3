<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen_penawaran', function (Blueprint $table) {
            $table->string('nomor_surat', 150)->nullable()->after('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::table('dokumen_penawaran', function (Blueprint $table) {
            $table->dropColumn('nomor_surat');
        });
    }
};
