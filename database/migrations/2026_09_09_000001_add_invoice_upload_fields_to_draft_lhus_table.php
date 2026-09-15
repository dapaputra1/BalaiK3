<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// [PERCOBAAN KUITANSI TTD BASAH] - Migration penambahan kolom audit upload kuitansi basah
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('draft_lhus')) {
            return;
        }

        Schema::table('draft_lhus', function (Blueprint $table) {
            if (!Schema::hasColumn('draft_lhus', 'invoice_uploaded_by')) {
                $table->integer('invoice_uploaded_by')->nullable()->after('invoice_file_name');
            }
            if (!Schema::hasColumn('draft_lhus', 'invoice_uploaded_at')) {
                $table->timestamp('invoice_uploaded_at')->nullable()->after('invoice_uploaded_by');
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
            foreach (['invoice_uploaded_by', 'invoice_uploaded_at'] as $column) {
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
