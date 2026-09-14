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
            if (!Schema::hasColumn('draft_lhus', 'invoice_verified_by_user_id')) {
                $table->integer('invoice_verified_by_user_id')->nullable()->after('invoice_generated_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'invoice_verified_by_user_at')) {
                $table->timestamp('invoice_verified_by_user_at')->nullable()->after('invoice_verified_by_user_id');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_expires_at')) {
                $table->timestamp('billing_expires_at')->nullable()->after('billing_sent_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('draft_lhus')) {
            return;
        }

        Schema::table('draft_lhus', function (Blueprint $table) {
            foreach (['invoice_verified_by_user_id', 'invoice_verified_by_user_at', 'billing_expires_at'] as $column) {
                if (Schema::hasColumn('draft_lhus', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
