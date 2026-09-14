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
                        'EMS_BOILER_GAS' => 8,
                        'EMS_BOILER' => 9,
                        'EMS_BOILER_BB_LAINNYA' => 10,
                        'EMS_INCINERATOR' => 11,
                    ];

                    $package->update([
                        'sort_order' => $sortOrders[$package->short_code] ?? $package->sort_order,
                    ]);
                });

            $package = ServicePackage::query()->updateOrCreate(
                ['short_code' => 'EMS_GENSET_7'],
                [
                    'service_category_id' => ServicePackage::query()->where('short_code', 'EMS_GENSET_3')->value('service_category_id'),
                    'name' => 'Paket Genset 7',
                    'badge' => 'Genset',
                    'subtitle' => 'Kapasitas 1001 - 3000 KW (Minyak)',
                    'price' => 2250000,
                    'unit' => 'per lokasi',
                    'sort_order' => 7,
                    'is_active' => true,
                ]
            );

            if (!$package->service_category_id) {
                return;
            }

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
            $package = ServicePackage::query()->where('short_code', 'EMS_GENSET_7')->first();

            if ($package) {
                $package->items()->delete();
                $package->delete();
            }

            ServicePackage::query()
                ->whereIn('short_code', ['EMS_BOILER_GAS', 'EMS_BOILER', 'EMS_BOILER_BB_LAINNYA', 'EMS_INCINERATOR'])
                ->get()
                ->each(function (ServicePackage $existingPackage) {
                    $sortOrders = [
                        'EMS_BOILER_GAS' => 7,
                        'EMS_BOILER' => 8,
                        'EMS_BOILER_BB_LAINNYA' => 9,
                        'EMS_INCINERATOR' => 10,
                    ];

                    $existingPackage->update([
                        'sort_order' => $sortOrders[$existingPackage->short_code] ?? $existingPackage->sort_order,
                    ]);
                });
        });
    }
};
