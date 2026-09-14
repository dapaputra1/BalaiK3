<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->where('short_code', 'HC')
            ->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->where('short_code', 'HC')
            ->update([
                'is_active' => 1,
                'updated_at' => now(),
            ]);
    }
};
