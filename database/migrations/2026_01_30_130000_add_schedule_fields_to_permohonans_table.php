<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            // Jadwal pengujian (tanpa jam).
            $table->date('jadwal_mulai')->nullable()->after('status_global');
            $table->date('jadwal_selesai')->nullable()->after('jadwal_mulai');
            $table->string('jadwal_lokasi', 255)->nullable()->after('jadwal_selesai');
            $table->text('jadwal_catatan')->nullable()->after('jadwal_lokasi');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn([
                'jadwal_mulai',
                'jadwal_selesai',
                'jadwal_lokasi',
                'jadwal_catatan',
            ]);
        });
    }
};
