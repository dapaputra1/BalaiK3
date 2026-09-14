<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('bap_items', 'review_status')) {
            Schema::table('bap_items', function (Blueprint $table) {
                $table->string('review_status', 20)->nullable()->after('catatan');
            });
        }

        if (!Schema::hasColumn('bap_items', 'reviewed_by')) {
            Schema::table('bap_items', function (Blueprint $table) {
                $table->unsignedInteger('reviewed_by')->nullable()->after('review_status');
            });
        }

        if (!Schema::hasColumn('bap_items', 'reviewed_at')) {
            Schema::table('bap_items', function (Blueprint $table) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bap_items', function (Blueprint $table) {
            $drops = [];
            foreach (['review_status', 'reviewed_by', 'reviewed_at'] as $column) {
                if (Schema::hasColumn('bap_items', $column)) {
                    $drops[] = $column;
                }
            }
            if ($drops) {
                $table->dropColumn($drops);
            }
        });
    }
};
