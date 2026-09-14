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
            if (!Schema::hasColumn('draft_lhus', 'billing_file_path')) {
                $table->string('billing_file_path')->nullable()->after('invoice_generated_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_file_name')) {
                $table->string('billing_file_name')->nullable()->after('billing_file_path');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_uploaded_by')) {
                $table->integer('billing_uploaded_by')->nullable()->after('billing_file_name');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_uploaded_at')) {
                $table->timestamp('billing_uploaded_at')->nullable()->after('billing_uploaded_by');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_sent_by')) {
                $table->integer('billing_sent_by')->nullable()->after('billing_uploaded_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_sent_at')) {
                $table->timestamp('billing_sent_at')->nullable()->after('billing_sent_by');
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
                'billing_file_path',
                'billing_file_name',
                'billing_uploaded_by',
                'billing_uploaded_at',
                'billing_sent_by',
                'billing_sent_at',
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
