<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prepanalisa_items', function (Blueprint $table) {
            $table->string('verif_status', 20)->default('pending')->after('is_done');
            $table->text('verif_note')->nullable()->after('verif_status');
            $table->integer('verif_by')->nullable()->after('verif_note');
            $table->timestamp('verif_at')->nullable()->after('verif_by');
        });

        Schema::table('prepanalisa_items', function (Blueprint $table) {
            $table->foreign('verif_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prepanalisa_items', function (Blueprint $table) {
            $table->dropForeign(['verif_by']);
            $table->dropColumn(['verif_status', 'verif_note', 'verif_by', 'verif_at']);
        });
    }
};
