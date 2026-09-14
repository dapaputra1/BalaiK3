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
            if (!Schema::hasColumn('draft_lhus', 'signed_file_path')) {
                $table->string('signed_file_path')->nullable()->after('final_uploaded_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'signed_file_name')) {
                $table->string('signed_file_name')->nullable()->after('signed_file_path');
            }
            if (!Schema::hasColumn('draft_lhus', 'signed_uploaded_by')) {
                $table->integer('signed_uploaded_by')->nullable()->after('signed_file_name');
            }
            if (!Schema::hasColumn('draft_lhus', 'signed_uploaded_at')) {
                $table->timestamp('signed_uploaded_at')->nullable()->after('signed_uploaded_by');
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
            foreach (['signed_file_path', 'signed_file_name', 'signed_uploaded_by', 'signed_uploaded_at'] as $column) {
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
