<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            if (!Schema::hasColumn('suket_k3s', 'evaluasi_status')) {
                $table->string('evaluasi_status', 30)->default('pending')->after('catatan_evaluasi');
            }
            if (!Schema::hasColumn('suket_k3s', 'evaluasi_by')) {
                $table->unsignedBigInteger('evaluasi_by')->nullable()->after('evaluasi_status');
            }
            if (!Schema::hasColumn('suket_k3s', 'evaluasi_at')) {
                $table->timestamp('evaluasi_at')->nullable()->after('evaluasi_by');
            }
        });

        if (!Schema::hasTable('suket_k3_comments')) {
            Schema::create('suket_k3_comments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('suket_id')->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('target', 20)->default('internal'); // 'internal' or 'pemohon'
                $table->text('comment');
                $table->timestamps();

                $table->index(['suket_id', 'target']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('suket_k3_comments');

        Schema::table('suket_k3s', function (Blueprint $table) {
            $columns = ['evaluasi_status', 'evaluasi_by', 'evaluasi_at'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('suket_k3s', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
