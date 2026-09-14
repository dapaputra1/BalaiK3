<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('baps', function (Blueprint $table) {
            if (!Schema::hasColumn('baps', 'sent_to_user_at')) {
                $table->timestamp('sent_to_user_at')->nullable()->after('sent_to_verifikasi_at');
            }
            if (!Schema::hasColumn('baps', 'user_approved_at')) {
                $table->timestamp('user_approved_at')->nullable()->after('sent_to_user_at');
            }
            if (!Schema::hasColumn('baps', 'user_approved_by')) {
                $table->integer('user_approved_by')->nullable()->after('user_approved_at');
            }
        });

        if (Schema::hasColumn('baps', 'user_approved_by')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE `baps` MODIFY `user_approved_by` INT NULL');
            }
            Schema::table('baps', function (Blueprint $table) {
                $table->foreign('user_approved_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('baps', function (Blueprint $table) {
            if (Schema::hasColumn('baps', 'user_approved_by')) {
                $table->dropForeign(['user_approved_by']);
            }
            $dropColumns = [];
            if (Schema::hasColumn('baps', 'sent_to_user_at')) {
                $dropColumns[] = 'sent_to_user_at';
            }
            if (Schema::hasColumn('baps', 'user_approved_at')) {
                $dropColumns[] = 'user_approved_at';
            }
            if (Schema::hasColumn('baps', 'user_approved_by')) {
                $dropColumns[] = 'user_approved_by';
            }
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
