<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('service_parameters') || !Schema::hasTable('service_categories')) {
            return;
        }

        $rows = [
            [
                'preferred_id' => 116,
                'service_category_id' => 1,
                'name' => 'Debu Total Partikulat',
                'short_code' => 'DEBULK',
                'price' => '1200000.00',
            ],
            [
                'preferred_id' => 117,
                'service_category_id' => 2,
                'name' => 'Debu Total Partikulat',
                'short_code' => 'DEBUAM',
                'price' => '1200000.00',
            ],
        ];

        foreach ($rows as $row) {
            if (!DB::table('service_categories')->where('id', $row['service_category_id'])->exists()) {
                continue;
            }

            $existing = DB::table('service_parameters')
                ->where('short_code', $row['short_code'])
                ->first();

            if ($existing) {
                DB::table('service_parameters')
                    ->where('short_code', $row['short_code'])
                    ->update([
                        'service_category_id' => $row['service_category_id'],
                        'name' => $row['name'],
                        'price' => $row['price'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            $insertData = [
                'service_category_id' => $row['service_category_id'],
                'name' => $row['name'],
                'short_code' => $row['short_code'],
                'price' => $row['price'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (!DB::table('service_parameters')->where('id', $row['preferred_id'])->exists()) {
                $insertData['id'] = $row['preferred_id'];
            }

            DB::table('service_parameters')->insert($insertData);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        DB::table('service_parameters')
            ->whereIn('short_code', ['DEBULK', 'DEBUAM'])
            ->delete();
    }
};
