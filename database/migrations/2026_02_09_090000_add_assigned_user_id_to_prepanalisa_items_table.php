<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prepanalisa_items', function (Blueprint $table) {
            if (Schema::hasColumn('prepanalisa_items', 'assigned_user_id')) {
                $table->dropColumn('assigned_user_id');
            }
            $table->integer('assigned_user_id')->nullable()->after('data_hasil_perhitungan');
        });

        Schema::table('prepanalisa_items', function (Blueprint $table) {
            $table->foreign('assigned_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('prepanalisa_items', function (Blueprint $table) {
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn('assigned_user_id');
        });
    }
};
