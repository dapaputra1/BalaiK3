<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (!Schema::hasColumn('permohonans', 'jadwal_sent_to_user_at')) {
                $table->timestamp('jadwal_sent_to_user_at')->nullable()->after('jadwal_pengumuman');
            }
            if (!Schema::hasColumn('permohonans', 'jadwal_user_approved_at')) {
                $table->timestamp('jadwal_user_approved_at')->nullable()->after('jadwal_sent_to_user_at');
            }
            if (!Schema::hasColumn('permohonans', 'jadwal_user_approved_by')) {
                $table->unsignedBigInteger('jadwal_user_approved_by')->nullable()->after('jadwal_user_approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $drops = [];
            foreach (['jadwal_sent_to_user_at', 'jadwal_user_approved_at', 'jadwal_user_approved_by'] as $column) {
                if (Schema::hasColumn('permohonans', $column)) {
                    $drops[] = $column;
                }
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
