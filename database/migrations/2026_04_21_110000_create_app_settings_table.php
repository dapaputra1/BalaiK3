<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('app_settings')->insert([
            [
                'key' => 'kepala_balai_nama',
                'value' => 'Oktofa S. Pamungkas, S.T, M.Kes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'kepala_balai_nip',
                'value' => '19791003 200912 1 002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
