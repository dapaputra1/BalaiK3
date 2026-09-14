<?php

use App\Models\ServicePackage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $updates = [
                'EMS_BOILER_GAS' => 1650000,
                'EMS_BOILER' => 3000000,
                'EMS_BOILER_BB_LAINNYA' => 7950000,
                'EMS_INCINERATOR' => 7500000,
            ];

            foreach ($updates as $shortCode => $price) {
                ServicePackage::query()
                    ->where('short_code', $shortCode)
                    ->update(['price' => $price]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $updates = [
                'EMS_BOILER_GAS' => 2250000,
                'EMS_BOILER' => 3850000,
                'EMS_BOILER_BB_LAINNYA' => 13300000,
                'EMS_INCINERATOR' => 12750000,
            ];

            foreach ($updates as $shortCode => $price) {
                ServicePackage::query()
                    ->where('short_code', $shortCode)
                    ->update(['price' => $price]);
            }
        });
    }
};
