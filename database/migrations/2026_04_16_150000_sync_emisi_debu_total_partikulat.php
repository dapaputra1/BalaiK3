<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $parameterId = DB::table('service_parameters')
            ->where('service_category_id', 3)
            ->where('short_code', 'DEBU')
            ->value('id');

        if (!$parameterId) {
            return;
        }

        DB::table('service_parameters')
            ->where('id', $parameterId)
            ->update([
                'name' => 'Debu Total Partikulat',
                'price' => 1200000,
                'updated_at' => now(),
            ]);

        $activeCartIds = DB::table('carts')
            ->where('status', 'active')
            ->pluck('id');

        if ($activeCartIds->isNotEmpty()) {
            DB::table('cart_items')
                ->where('service_parameter_id', $parameterId)
                ->whereIn('cart_id', $activeCartIds)
                ->update([
                    'price' => 1200000,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $parameterId = DB::table('service_parameters')
            ->where('service_category_id', 3)
            ->where('short_code', 'DEBU')
            ->value('id');

        if (!$parameterId) {
            return;
        }

        DB::table('service_parameters')
            ->where('id', $parameterId)
            ->update([
                'name' => 'DEBU',
                'price' => 150000,
                'updated_at' => now(),
            ]);

        $activeCartIds = DB::table('carts')
            ->where('status', 'active')
            ->pluck('id');

        if ($activeCartIds->isNotEmpty()) {
            DB::table('cart_items')
                ->where('service_parameter_id', $parameterId)
                ->whereIn('cart_id', $activeCartIds)
                ->update([
                    'price' => 150000,
                    'updated_at' => now(),
                ]);
        }
    }
};
