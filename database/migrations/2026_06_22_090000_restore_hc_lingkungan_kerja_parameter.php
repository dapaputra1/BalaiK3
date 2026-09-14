<?php

use App\Support\ServiceSystemCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_categories') || !Schema::hasTable('service_parameters')) {
            return;
        }

        $category = DB::table('service_categories')
            ->where('system_code', ServiceSystemCode::CATEGORY_LK)
            ->orWhere('name', 'Lingkungan Kerja')
            ->orderBy('id')
            ->first();

        if (!$category) {
            return;
        }

        $systemCode = ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'HC');
        $now = now();
        $existing = DB::table('service_parameters')
            ->where(function ($query) use ($category, $systemCode) {
                $query->where('system_code', $systemCode)
                    ->orWhere(function ($query) use ($category) {
                        $query->where('service_category_id', $category->id)
                            ->where('short_code', 'HC');
                    });
            })
            ->orderBy('id')
            ->first();

        $data = [
            'service_category_id' => $category->id,
            'name' => 'HC',
            'system_code' => $systemCode,
            'short_code' => 'HC',
            'price' => '250000.00',
            'is_active' => 1,
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('service_parameters')
                ->where('id', $existing->id)
                ->update($data);

            return;
        }

        $data['created_at'] = $now;

        DB::table('service_parameters')->insert($data);
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        DB::table('service_parameters')
            ->where('system_code', ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'HC'))
            ->orWhere(function ($query) {
                $query->where('service_category_id', 1)
                    ->where('short_code', 'HC');
            })
            ->update([
                'is_active' => 0,
                'updated_at' => now(),
            ]);
    }
};
