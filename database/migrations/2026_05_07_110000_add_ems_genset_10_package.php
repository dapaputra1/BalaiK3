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
            ServicePackage::query()
                ->whereIn('short_code', ['EMS_BOILER_GAS', 'EMS_BOILER', 'EMS_BOILER_BB_LAINNYA', 'EMS_INCINERATOR'])
                ->get()
                ->each(function (ServicePackage $package) {
                    $sortOrders = [
                        'EMS_BOILER_GAS' => 11,
                        'EMS_BOILER' => 12,
                        'EMS_BOILER_BB_LAINNYA' => 13,
                        'EMS_INCINERATOR' => 14,
                    ];

                    $package->update([
                        'sort_order' => $sortOrders[$package->short_code] ?? $package->sort_order,
                    ]);
                });

            $package = ServicePackage::query()->updateOrCreate(
                ['short_code' => 'EMS_GENSET_10'],
                [
                    'service_category_id' => ServicePackage::query()->where('short_code', 'EMS_GENSET_6')->value('service_category_id'),
                    'name' => 'Paket Genset 10',
                    'badge' => 'Genset',
                    'subtitle' => 'Kapasitas 1001 - 3000 KW (Gas)',
                    'price' => 2550000,
                    'unit' => 'per lokasi',
                    'sort_order' => 10,
                    'is_active' => true,
                ]
            );

            if (!$package->service_category_id) {
                return;
            }

            $parameterIds = ServiceParameter::query()
                ->whereIn('short_code', ['SO22', 'NO22', 'COSTB', 'O2', 'LAJUA'])
                ->pluck('id', 'short_code');

            $items = [
                ['short_code' => 'SO22', 'label' => 'SO2'],
                ['short_code' => 'NO22', 'label' => 'NOx'],
                ['short_code' => 'COSTB', 'label' => 'CO'],
                ['short_code' => 'O2', 'label' => 'O2'],
                ['short_code' => 'LAJUA', 'label' => 'Laju Alir'],
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
            $package = ServicePackage::query()->where('short_code', 'EMS_GENSET_10')->first();

            if ($package) {
                $package->items()->delete();
                $package->delete();
            }

            ServicePackage::query()
                ->whereIn('short_code', ['EMS_BOILER_GAS', 'EMS_BOILER', 'EMS_BOILER_BB_LAINNYA', 'EMS_INCINERATOR'])
                ->get()
                ->each(function (ServicePackage $existingPackage) {
                    $sortOrders = [
                        'EMS_BOILER_GAS' => 10,
                        'EMS_BOILER' => 11,
                        'EMS_BOILER_BB_LAINNYA' => 12,
                        'EMS_INCINERATOR' => 13,
                    ];

                    $existingPackage->update([
                        'sort_order' => $sortOrders[$existingPackage->short_code] ?? $existingPackage->sort_order,
                    ]);
                });
        });
    }
};
