<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonan_companies', function (Blueprint $table) {
            $table->string('responsible_signature_path')->nullable()->after('authority_role');
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_companies', function (Blueprint $table) {
            $table->dropColumn('responsible_signature_path');
        });
    }
};
