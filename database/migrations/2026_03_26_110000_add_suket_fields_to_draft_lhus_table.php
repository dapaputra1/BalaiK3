<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('draft_lhus')) {
            Schema::table('draft_lhus', function (Blueprint $table) {
                if (!Schema::hasColumn('draft_lhus', 'suket_file_path')) {
                    $table->string('suket_file_path')->nullable()->after('billing_verified_at');
                }
                if (!Schema::hasColumn('draft_lhus', 'suket_file_name')) {
                    $table->string('suket_file_name')->nullable()->after('suket_file_path');
                }
                if (!Schema::hasColumn('draft_lhus', 'suket_uploaded_by')) {
                    $table->unsignedInteger('suket_uploaded_by')->nullable()->after('suket_file_name');
                }
                if (!Schema::hasColumn('draft_lhus', 'suket_uploaded_at')) {
                    $table->timestamp('suket_uploaded_at')->nullable()->after('suket_uploaded_by');
                }
            });
        }

        if (Schema::hasTable('workflow_steps')) {
            DB::table('workflow_steps')->updateOrInsert(
                ['kode' => 'penerbitan_suket'],
                ['nama' => 'Penerbitan Suket', 'urutan' => 17]
            );

            DB::table('workflow_steps')
                ->where('kode', 'penyerahan_lhu')
                ->update(['urutan' => 18]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('draft_lhus')) {
            Schema::table('draft_lhus', function (Blueprint $table) {
                foreach (['suket_file_path', 'suket_file_name', 'suket_uploaded_by', 'suket_uploaded_at'] as $column) {
                    if (Schema::hasColumn('draft_lhus', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('workflow_steps')) {
            DB::table('workflow_steps')->where('kode', 'penerbitan_suket')->delete();
            DB::table('workflow_steps')
                ->where('kode', 'penyerahan_lhu')
                ->update(['urutan' => 17]);
        }
    }
};
