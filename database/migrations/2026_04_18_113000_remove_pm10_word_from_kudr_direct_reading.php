<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->where('short_code', 'DPM25')
            ->update([
                'name' => 'Debu KUDR - Direct Reading',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('service_parameters')
            ->where('service_category_id', 1)
            ->where('short_code', 'DPM25')
            ->update([
                'name' => 'Debu PM10 KUDR - Direct Reading',
                'updated_at' => now(),
            ]);
    }
};
