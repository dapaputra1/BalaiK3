<?php

use App\Support\ServiceSystemCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        DB::table('service_parameters')
            ->where('short_code', 'PKP41')
            ->delete();
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        $exists = DB::table('service_parameters')
            ->where('short_code', 'PKP41')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('service_parameters')->insert([
            'service_category_id' => 2,
            'name' => 'PEMETAAN KEBISINGAN - per 400 m2',
            'system_code' => ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'PKP41'),
            'short_code' => 'PKP41',
            'price' => '4000000.00',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
