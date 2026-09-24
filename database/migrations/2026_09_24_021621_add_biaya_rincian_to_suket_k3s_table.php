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
        Schema::table('suket_k3s', function (Blueprint $table) {
            if (!Schema::hasColumn('suket_k3s', 'biaya_rincian')) {
                $table->json('biaya_rincian')->nullable()->after('surat_tagihan_nominal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            if (Schema::hasColumn('suket_k3s', 'biaya_rincian')) {
                $table->dropColumn('biaya_rincian');
            }
        });
    }
};
