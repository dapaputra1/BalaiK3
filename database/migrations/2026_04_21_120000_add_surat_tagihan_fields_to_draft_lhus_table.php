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
            if (!Schema::hasColumn('draft_lhus', 'surat_tagihan_generated_by')) {
                $table->integer('surat_tagihan_generated_by')->nullable()->after('signed_uploaded_at');
            }

            if (!Schema::hasColumn('draft_lhus', 'surat_tagihan_generated_at')) {
                $table->timestamp('surat_tagihan_generated_at')->nullable()->after('surat_tagihan_generated_by');
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

            foreach (['surat_tagihan_generated_by', 'surat_tagihan_generated_at'] as $column) {
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
