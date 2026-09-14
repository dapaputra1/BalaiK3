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
            if (!Schema::hasColumn('draft_lhus', 'invoice_file_path')) {
                $table->string('invoice_file_path')->nullable()->after('signed_uploaded_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'invoice_file_name')) {
                $table->string('invoice_file_name')->nullable()->after('invoice_file_path');
            }
            if (!Schema::hasColumn('draft_lhus', 'invoice_generated_by')) {
                $table->integer('invoice_generated_by')->nullable()->after('invoice_file_name');
            }
            if (!Schema::hasColumn('draft_lhus', 'invoice_generated_at')) {
                $table->timestamp('invoice_generated_at')->nullable()->after('invoice_generated_by');
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
            foreach (['invoice_file_path', 'invoice_file_name', 'invoice_generated_by', 'invoice_generated_at'] as $column) {
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
