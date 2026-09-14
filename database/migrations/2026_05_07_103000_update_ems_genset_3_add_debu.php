<?php

use App\Models\ServicePackage;
use App\Models\ServicePackageItem;
use App\Models\ServiceParameter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $package = ServicePackage::query()->where('short_code', 'EMS_GENSET_3')->first();

            if (!$package) {
                return;
            }

            $package->update([
                'subtitle' => 'Kapasitas 501 - 1000 KW (Minyak)',
                'price' => 2250000,
            ]);

            $parameterIds = ServiceParameter::query()
                ->whereIn('short_code', ['SO22', 'NO22', 'COSTB', 'O2', 'DEBU'])
                ->pluck('id', 'short_code');

            $items = [
                ['short_code' => 'SO22', 'label' => 'SO2'],
                ['short_code' => 'NO22', 'label' => 'NOx'],
                ['short_code' => 'COSTB', 'label' => 'CO'],
                ['short_code' => 'O2', 'label' => 'O2'],
                ['short_code' => 'DEBU', 'label' => 'Total Partikulat'],
            ];

            $package->items()->delete();

            foreach ($items as $index => $item) {
                ServicePackageItem::create([
                    'service_package_id' => $package->id,
                    'service_parameter_id' => $parameterIds->get($item['short_code']),
                    'label' => $item['label'],
                    'sort_order' => $index + 1,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $package = ServicePackage::query()->where('short_code', 'EMS_GENSET_3')->first();

            if (!$package) {
                return;
            }

            $package->update([
                'subtitle' => 'Kapasitas 501 - 1000 KW (Minyak)',
                'price' => 1800000,
            ]);

            $parameterIds = ServiceParameter::query()
                ->whereIn('short_code', ['SO22', 'NO22', 'COSTB', 'O2'])
                ->pluck('id', 'short_code');

            $items = [
                ['short_code' => 'SO22', 'label' => 'SO2'],
                ['short_code' => 'NO22', 'label' => 'NOx'],
                ['short_code' => 'COSTB', 'label' => 'CO'],
                ['short_code' => 'O2', 'label' => 'O2'],
            ];

            $package->items()->delete();

            foreach ($items as $index => $item) {
                ServicePackageItem::create([
                    'service_package_id' => $package->id,
                    'service_parameter_id' => $parameterIds->get($item['short_code']),
                    'label' => $item['label'],
                    'sort_order' => $index + 1,
                ]);
            }
        });
    }
};
