<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('service_categories') || !Schema::hasTable('service_parameters')) {
            return;
        }

        $emisiCategoryId = DB::table('service_categories')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) = ?', ['emisi'])
                    ->orWhereRaw('LOWER(short_code) = ?', ['ems']);
            })
            ->value('id');

        if (!$emisiCategoryId) {
            return;
        }

        $existingParameter = DB::table('service_parameters')
            ->where('short_code', 'SDPCR')
            ->first();

        if ($existingParameter) {
            DB::table('service_parameters')
                ->where('short_code', 'SDPCR')
                ->update([
                    'service_category_id' => $emisiCategoryId,
                    'name' => 'Penentuan Suhu dan Per Cerobong',
                    'price' => '200000.00',
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('service_parameters')->insert([
            'service_category_id' => $emisiCategoryId,
            'name' => 'Penentuan Suhu dan Per Cerobong',
            'short_code' => 'SDPCR',
            'price' => '200000.00',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_parameters')) {
            return;
        }

        DB::table('service_parameters')
            ->where('short_code', 'SDPCR')
            ->delete();
    }
};
