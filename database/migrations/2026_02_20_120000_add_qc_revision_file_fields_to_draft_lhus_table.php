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
            if (!Schema::hasColumn('draft_lhus', 'qc_revision_file_path')) {
                $table->string('qc_revision_file_path')->nullable()->after('lhu_user_revision_note');
            }
            if (!Schema::hasColumn('draft_lhus', 'qc_revision_file_name')) {
                $table->string('qc_revision_file_name')->nullable()->after('qc_revision_file_path');
            }
            if (!Schema::hasColumn('draft_lhus', 'qc_revision_file_uploaded_by')) {
                $table->integer('qc_revision_file_uploaded_by')->nullable()->after('qc_revision_file_name');
            }
            if (!Schema::hasColumn('draft_lhus', 'qc_revision_file_uploaded_at')) {
                $table->timestamp('qc_revision_file_uploaded_at')->nullable()->after('qc_revision_file_uploaded_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('draft_lhus')) {
            return;
        }

        Schema::table('draft_lhus', function (Blueprint $table) {
            foreach ([
                'qc_revision_file_path',
                'qc_revision_file_name',
                'qc_revision_file_uploaded_by',
                'qc_revision_file_uploaded_at',
            ] as $column) {
                if (Schema::hasColumn('draft_lhus', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
