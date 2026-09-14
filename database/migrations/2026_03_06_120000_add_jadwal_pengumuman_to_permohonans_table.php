<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            if (!Schema::hasColumn('permohonans', 'jadwal_pengumuman')) {
                $table->text('jadwal_pengumuman')->nullable()->after('jadwal_catatan');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('permohonans', 'jadwal_pengumuman')) {
            return;
        }

        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn('jadwal_pengumuman');
        });
    }
};
