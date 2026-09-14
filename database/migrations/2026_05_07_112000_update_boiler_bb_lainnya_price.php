<?php

use App\Models\ServicePackage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            ServicePackage::query()
                ->where('short_code', 'EMS_BOILER_BB_LAINNYA')
                ->update(['price' => 4650000]);
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            ServicePackage::query()
                ->where('short_code', 'EMS_BOILER_BB_LAINNYA')
                ->update(['price' => 7950000]);
        });
    }
};
