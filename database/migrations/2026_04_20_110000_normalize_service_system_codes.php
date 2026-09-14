<?php

use App\Support\ServiceSystemCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeCategorySystemCodes();
        $this->normalizeParameterSystemCodes();
    }

    public function down(): void
    {
        // Intentionally left blank: normalization is data corrective.
    }

    private function normalizeCategorySystemCodes(): void
    {
        $targetMap = [
            1 => ServiceSystemCode::CATEGORY_LK,
            2 => ServiceSystemCode::CATEGORY_AMB,
            3 => ServiceSystemCode::CATEGORY_EMS,
            4 => ServiceSystemCode::CATEGORY_KES,
            5 => ServiceSystemCode::CATEGORY_PLT,
        ];

        foreach ($targetMap as $id => $systemCode) {
            DB::table('service_categories')
                ->where('id', $id)
                ->update(['system_code' => $systemCode]);
        }
    }

    private function normalizeParameterSystemCodes(): void
    {
        $categoryCodes = DB::table('service_categories')
            ->pluck('system_code', 'id');

        $rows = DB::table('service_parameters')
            ->orderBy('id')
            ->get(['id', 'service_category_id', 'name', 'short_code']);

        foreach ($rows as $row) {
            $categoryCode = (string) ($categoryCodes[$row->service_category_id] ?? 'CAT');
            $systemCode = ServiceSystemCode::legacyParameterCode(
                $categoryCode,
                $row->short_code,
                $row->name,
                (int) $row->id
            );

            DB::table('service_parameters')
                ->where('id', $row->id)
                ->update(['system_code' => $systemCode]);
        }
    }
};
