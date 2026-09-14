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
            if (!Schema::hasColumn('draft_lhus', 'final_file_path')) {
                $table->string('final_file_path')->nullable()->after('tables');
            }
            if (!Schema::hasColumn('draft_lhus', 'final_file_name')) {
                $table->string('final_file_name')->nullable()->after('final_file_path');
            }
            if (!Schema::hasColumn('draft_lhus', 'final_uploaded_by')) {
                $table->integer('final_uploaded_by')->nullable()->after('final_file_name');
            }
            if (!Schema::hasColumn('draft_lhus', 'final_uploaded_at')) {
                $table->timestamp('final_uploaded_at')->nullable()->after('final_uploaded_by');
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
            foreach (['final_file_path', 'final_file_name', 'final_uploaded_by', 'final_uploaded_at'] as $column) {
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
