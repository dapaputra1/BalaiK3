<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->timestamp('penjadwalan_sent_at')->nullable()->after('jadwal_catatan');
            $table->timestamp('spt_sent_at')->nullable()->after('penjadwalan_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn(['penjadwalan_sent_at', 'spt_sent_at']);
        });
    }
};
