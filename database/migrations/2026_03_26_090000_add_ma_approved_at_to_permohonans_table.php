<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('permohonans', 'ma_approved_at')) {
            Schema::table('permohonans', function (Blueprint $table) {
                $table->timestamp('ma_approved_at')->nullable()->after('penjadwalan_sent_at');
            });
        }

        $nowSql = DB::getDriverName() === 'sqlite' ? "datetime('now')" : 'NOW()';
        DB::table('permohonans')
            ->whereNotNull('spt_sent_at')
            ->whereNull('ma_approved_at')
            ->update([
                'ma_approved_at' => DB::raw("COALESCE(spt_sent_at, penjadwalan_sent_at, {$nowSql})"),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('permohonans', 'ma_approved_at')) {
            Schema::table('permohonans', function (Blueprint $table) {
                $table->dropColumn('ma_approved_at');
            });
        }
    }
};
