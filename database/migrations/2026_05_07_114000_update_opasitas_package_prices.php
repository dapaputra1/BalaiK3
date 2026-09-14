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
                'EMS_BOILER' => 2900000,
                'EMS_BOILER_BB_LAINNYA' => 4550000,
                'EMS_INCINERATOR' => 4700000,
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
                'EMS_BOILER' => 3000000,
                'EMS_BOILER_BB_LAINNYA' => 4650000,
                'EMS_INCINERATOR' => 4800000,
            ];

            foreach ($updates as $shortCode => $price) {
                ServicePackage::query()
                    ->where('short_code', $shortCode)
                    ->update(['price' => $price]);
            }
        });
    }
};
