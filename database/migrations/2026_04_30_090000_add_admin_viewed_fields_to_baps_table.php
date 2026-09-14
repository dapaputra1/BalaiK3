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
            if (!Schema::hasColumn('baps', 'admin_viewed_at')) {
                $table->timestamp('admin_viewed_at')->nullable()->after('user_approved_by');
            }
            if (!Schema::hasColumn('baps', 'admin_viewed_by')) {
                $table->integer('admin_viewed_by')->nullable()->after('admin_viewed_at');
            }
        });

        if (Schema::hasColumn('baps', 'admin_viewed_by')) {
            DB::statement('ALTER TABLE `baps` MODIFY `admin_viewed_by` INT NULL');
            Schema::table('baps', function (Blueprint $table) {
                $table->foreign('admin_viewed_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('baps', function (Blueprint $table) {
            if (Schema::hasColumn('baps', 'admin_viewed_by')) {
                $table->dropForeign(['admin_viewed_by']);
            }

            $dropColumns = [];
            if (Schema::hasColumn('baps', 'admin_viewed_at')) {
                $dropColumns[] = 'admin_viewed_at';
            }
            if (Schema::hasColumn('baps', 'admin_viewed_by')) {
                $dropColumns[] = 'admin_viewed_by';
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
