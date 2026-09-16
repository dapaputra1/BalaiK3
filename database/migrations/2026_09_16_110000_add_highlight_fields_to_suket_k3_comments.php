<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suket_k3_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('suket_k3_comments', 'bagian')) {
                $table->string('bagian', 255)->nullable()->after('target');
            }
            if (!Schema::hasColumn('suket_k3_comments', 'highlight_text')) {
                $table->text('highlight_text')->nullable()->after('bagian');
            }
            if (!Schema::hasColumn('suket_k3_comments', 'tipe')) {
                $table->string('tipe', 30)->default('kesalahan')->after('highlight_text');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suket_k3_comments', function (Blueprint $table) {
            $columns = ['bagian', 'highlight_text', 'tipe'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('suket_k3_comments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
