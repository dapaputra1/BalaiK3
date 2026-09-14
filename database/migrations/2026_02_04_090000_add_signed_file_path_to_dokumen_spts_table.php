<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen_spts', function (Blueprint $table) {
            $table->string('signed_file_path')->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('dokumen_spts', function (Blueprint $table) {
            $table->dropColumn('signed_file_path');
        });
    }
};
