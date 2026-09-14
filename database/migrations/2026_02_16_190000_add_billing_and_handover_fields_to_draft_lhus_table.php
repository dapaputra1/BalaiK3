<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('draft_lhus')) {
            return;
        }

        Schema::table('draft_lhus', function (Blueprint $table) {
            if (!Schema::hasColumn('draft_lhus', 'billing_paid_by_user_id')) {
                $table->integer('billing_paid_by_user_id')->nullable()->after('billing_sent_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_paid_by_user_at')) {
                $table->timestamp('billing_paid_by_user_at')->nullable()->after('billing_paid_by_user_id');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_verified_by')) {
                $table->integer('billing_verified_by')->nullable()->after('billing_paid_by_user_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_verified_at')) {
                $table->timestamp('billing_verified_at')->nullable()->after('billing_verified_by');
            }
            if (!Schema::hasColumn('draft_lhus', 'lhu_sent_to_user_by')) {
                $table->integer('lhu_sent_to_user_by')->nullable()->after('billing_verified_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'lhu_sent_to_user_at')) {
                $table->timestamp('lhu_sent_to_user_at')->nullable()->after('lhu_sent_to_user_by');
            }
            if (!Schema::hasColumn('draft_lhus', 'lhu_user_approved_by')) {
                $table->integer('lhu_user_approved_by')->nullable()->after('lhu_sent_to_user_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'lhu_user_approved_at')) {
                $table->timestamp('lhu_user_approved_at')->nullable()->after('lhu_user_approved_by');
            }
            if (!Schema::hasColumn('draft_lhus', 'lhu_user_revision_at')) {
                $table->timestamp('lhu_user_revision_at')->nullable()->after('lhu_user_approved_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'lhu_user_revision_note')) {
                $table->text('lhu_user_revision_note')->nullable()->after('lhu_user_revision_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('draft_lhus')) {
            return;
        }

        Schema::table('draft_lhus', function (Blueprint $table) {
            $dropColumns = [];
            foreach ([
                'billing_paid_by_user_id',
                'billing_paid_by_user_at',
                'billing_verified_by',
                'billing_verified_at',
                'lhu_sent_to_user_by',
                'lhu_sent_to_user_at',
                'lhu_user_approved_by',
                'lhu_user_approved_at',
                'lhu_user_revision_at',
                'lhu_user_revision_note',
            ] as $column) {
                if (Schema::hasColumn('draft_lhus', $column)) {
                    $dropColumns[] = $column;
                }
            }
            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
