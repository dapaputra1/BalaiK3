<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\ServicePackageItem;
use App\Models\ServiceParameter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ServicePackageSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('service_packages') || !Schema::hasTable('service_package_items')) {
            return;
        }

        $emisiCategory = ServiceCategory::query()->where('name', 'Emisi')->first();
        if (!$emisiCategory) {
            return;
        }

        $emisiParamsByShortCode = ServiceParameter::query()
            ->where('service_category_id', $emisiCategory->id)
            ->get()
            ->keyBy('short_code');

        $packages = [
            [
                'short_code' => 'EMS_GENSET_1',
                'name' => 'Paket Genset 1',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 101 - 500 KW (Minyak dan Gas)',
                'price' => 1350000,
                'unit' => 'per lokasi',
                'sort_order' => 1,
                'items' => [
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_2',
                'name' => 'Paket Genset 2',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 101 - 500 KW (Minyak dan Gas)',
                'price' => 2100000,
                'unit' => 'per lokasi',
                'sort_order' => 2,
                'items' => [
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'LAJUA', 'label' => 'Laju Alir'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_3',
                'name' => 'Paket Genset 3',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 501 - 1000 KW (Minyak)',
                'price' => 2250000,
                'unit' => 'per lokasi',
                'sort_order' => 3,
                'items' => [
                    ['short_code' => 'SO22', 'label' => 'SO2'],
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                    ['short_code' => 'DEBU', 'label' => 'Total Partikulat'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_4',
                'name' => 'Paket Genset 4',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 501 - 1000 KW (Minyak)',
                'price' => 3000000,
                'unit' => 'per lokasi',
                'sort_order' => 4,
                'items' => [
                    ['short_code' => 'SO22', 'label' => 'SO2'],
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                    ['short_code' => 'DEBU', 'label' => 'Total Partikulat'],
                    ['short_code' => 'LAJUA', 'label' => 'Laju Alir'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_5',
                'name' => 'Paket Genset 5',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 501 - 1000 KW (Gas)',
                'price' => 1800000,
                'unit' => 'per lokasi',
                'sort_order' => 5,
                'items' => [
                    ['short_code' => 'SO22', 'label' => 'SO2'],
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_6',
                'name' => 'Paket Genset 6',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 501 - 1000 KW (Gas)',
                'price' => 2550000,
                'unit' => 'per lokasi',
                'sort_order' => 6,
                'items' => [
                    ['short_code' => 'SO22', 'label' => 'SO2'],
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                    ['short_code' => 'LAJUA', 'label' => 'Laju Alir'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_7',
                'name' => 'Paket Genset 7',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 1001 - 3000 KW (Minyak)',
                'price' => 2250000,
                'unit' => 'per lokasi',
                'sort_order' => 7,
                'items' => [
                    ['short_code' => 'SO22', 'label' => 'SO2'],
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                    ['short_code' => 'DEBU', 'label' => 'Total Partikulat'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_8',
                'name' => 'Paket Genset 8',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 1001 - 3000 KW (Minyak)',
                'price' => 3000000,
                'unit' => 'per lokasi',
                'sort_order' => 8,
                'items' => [
                    ['short_code' => 'SO22', 'label' => 'SO2'],
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                    ['short_code' => 'DEBU', 'label' => 'Total Partikulat'],
                    ['short_code' => 'LAJUA', 'label' => 'Laju Alir'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_9',
                'name' => 'Paket Genset 9',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 1001 - 3000 KW (Gas)',
                'price' => 1800000,
                'unit' => 'per lokasi',
                'sort_order' => 9,
                'items' => [
                    ['short_code' => 'SO22', 'label' => 'SO2'],
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                ],
            ],
            [
                'short_code' => 'EMS_GENSET_10',
                'name' => 'Paket Genset 10',
                'badge' => 'Genset',
                'subtitle' => 'Kapasitas 1001 - 3000 KW (Gas)',
                'price' => 2550000,
                'unit' => 'per lokasi',
                'sort_order' => 10,
                'items' => [
                    ['short_code' => 'SO22', 'label' => 'SO2'],
                    ['short_code' => 'NO22', 'label' => 'NOx'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'O2', 'label' => 'O2'],
                    ['short_code' => 'LAJUA', 'label' => 'Laju Alir'],
                ],
            ],
            [
                'short_code' => 'EMS_BOILER_GAS',
                'name' => 'Paket Boiler Gas',
                'badge' => 'Boiler',
                'subtitle' => 'Kombinasi dasar pengujian emisi boiler berbahan bakar gas',
                'price' => 1650000,
                'unit' => 'per lokasi',
                'sort_order' => 11,
                'items' => [
                    ['short_code' => 'SO22'],
                    ['short_code' => 'NO22'],
                    ['short_code' => 'DEBU', 'label' => 'Laju Alir'],
                ],
            ],
            [
                'short_code' => 'EMS_BOILER',
                'name' => 'Paket Boiler',
                'badge' => 'Boiler',
                'subtitle' => 'Kombinasi umum untuk boiler dengan pengujian partikulat',
                'price' => 2900000,
                'unit' => 'per lokasi',
                'sort_order' => 12,
                'items' => [
                    ['short_code' => 'SO22'],
                    ['short_code' => 'NO22'],
                    ['short_code' => 'DEBU', 'label' => 'TP (+O2, Laju Alir)'],
                    ['short_code' => 'OPASI'],
                ],
            ],
            [
                'short_code' => 'EMS_BOILER_BB_LAINNYA',
                'name' => 'Paket Boiler BB Lainnya',
                'badge' => 'Boiler',
                'subtitle' => 'Kombinasi lengkap untuk bahan bakar non-standar atau kompleks',
                'price' => 4550000,
                'unit' => 'per lokasi',
                'sort_order' => 13,
                'items' => [
                    ['short_code' => 'NO22'],
                    ['short_code' => 'SO22'],
                    ['short_code' => 'DEBU', 'label' => 'TP (+O2, Laju Alir)'],
                    ['short_code' => 'NH32'],
                    ['short_code' => 'HF'],
                    ['short_code' => 'HCL'],
                    ['short_code' => 'HG'],
                    ['short_code' => 'CL2'],
                    ['short_code' => 'H2S2'],
                    ['short_code' => 'OPASI'],
                    ['label' => 'Cd'],
                    ['label' => 'As'],
                    ['label' => 'Zn'],
                    ['label' => 'Pb'],
                    ['label' => 'Sb'],
                ],
            ],
            [
                'short_code' => 'EMS_INCINERATOR',
                'name' => 'Paket Incinerator',
                'badge' => 'Incinerator',
                'subtitle' => 'Kombinasi parameter untuk pemantauan sumber emisi pembakaran limbah',
                'price' => 4700000,
                'unit' => 'per lokasi',
                'sort_order' => 14,
                'items' => [
                    ['short_code' => 'SO22'],
                    ['short_code' => 'NO22'],
                    ['short_code' => 'COSTB', 'label' => 'CO'],
                    ['short_code' => 'DEBU', 'label' => 'TP (+Laju Alir, O2)'],
                    ['short_code' => 'HF'],
                    ['short_code' => 'HCL'],
                    ['short_code' => 'HCEM', 'label' => 'HC'],
                    ['short_code' => 'HG'],
                    ['short_code' => 'OPASI'],
                    ['label' => 'As'],
                    ['label' => 'Cd'],
                    ['label' => 'Cr'],
                    ['label' => 'Pb'],
                    ['label' => 'Tl'],
                ],
            ],
        ];

        DB::transaction(function () use ($packages, $emisiCategory, $emisiParamsByShortCode) {
            foreach ($packages as $pkg) {
                $package = ServicePackage::query()->updateOrCreate(
                    ['short_code' => $pkg['short_code']],
                    [
                        'service_category_id' => $emisiCategory->id,
                        'name' => $pkg['name'],
                        'badge' => $pkg['badge'] ?? null,
                        'subtitle' => $pkg['subtitle'] ?? null,
                        'price' => $pkg['price'],
                        'unit' => $pkg['unit'] ?? 'per lokasi',
                        'sort_order' => $pkg['sort_order'] ?? 0,
                        'is_active' => true,
                    ]
                );

                $package->items()->delete();

                foreach (($pkg['items'] ?? []) as $index => $item) {
                    $shortCode = $item['short_code'] ?? null;
                    $parameterId = $shortCode ? ($emisiParamsByShortCode->get($shortCode)?->id) : null;

                    ServicePackageItem::create([
                        'service_package_id' => $package->id,
                        'service_parameter_id' => $parameterId,
                        'label' => $item['label'] ?? null,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        });
    }
}
