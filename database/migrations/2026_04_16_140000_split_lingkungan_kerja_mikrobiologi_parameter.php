<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('service_parameters') || !DB::table('service_categories')->where('id', 1)->exists()) {
            return;
        }

        DB::table('service_parameters')
            ->where('short_code', 'PMKGU')
            ->update([
                'name' => 'Pengujian Mikrobiologi Koloni Jamur',
                'short_code' => 'PMKJM',
                'price' => '500000.00',
                'is_active' => true,
                'updated_at' => now(),
            ]);

        $bakteri = DB::table('service_parameters')
            ->where('short_code', 'PMKBA')
            ->first();

        if ($bakteri) {
            DB::table('service_parameters')
                ->where('short_code', 'PMKBA')
                ->update([
                    'service_category_id' => 1,
                    'name' => 'Pengujian Mikrobiologi Bakteri',
                    'price' => '500000.00',
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        } else {
            $insertData = [
                'service_category_id' => 1,
                'name' => 'Pengujian Mikrobiologi Bakteri',
                'short_code' => 'PMKBA',
                'price' => '500000.00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (!DB::table('service_parameters')->where('id', 116)->exists()) {
                $insertData['id'] = 116;
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
            ->where('short_code', 'PMKJM')
            ->update([
                'name' => 'Pengujian Mikrobiologi Koloni Jamur dan Bakteri',
                'short_code' => 'PMKGU',
                'price' => '500000.00',
                'is_active' => true,
                'updated_at' => now(),
            ]);

        DB::table('service_parameters')
            ->where('short_code', 'PMKBA')
            ->delete();
    }
};
