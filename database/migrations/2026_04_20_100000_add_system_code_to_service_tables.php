<?php

use App\Support\ServiceSystemCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('service_categories', 'system_code')) {
                $table->string('system_code', 40)->nullable()->after('name');
            }
        });

        Schema::table('service_parameters', function (Blueprint $table) {
            if (!Schema::hasColumn('service_parameters', 'system_code')) {
                $table->string('system_code', 80)->nullable()->after('name');
            }
        });

        $this->backfillCategorySystemCodes();
        $this->backfillParameterSystemCodes();

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE service_categories MODIFY system_code VARCHAR(40) NOT NULL");
            DB::statement("ALTER TABLE service_parameters MODIFY system_code VARCHAR(80) NOT NULL");
        }

        Schema::table('service_categories', function (Blueprint $table) {
            $table->unique('system_code', 'service_categories_system_code_unique');
        });

        Schema::table('service_parameters', function (Blueprint $table) {
            $table->unique('system_code', 'service_parameters_system_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('service_parameters', function (Blueprint $table) {
            if (Schema::hasColumn('service_parameters', 'system_code')) {
                $table->dropUnique('service_parameters_system_code_unique');
                $table->dropColumn('system_code');
            }
        });

        Schema::table('service_categories', function (Blueprint $table) {
            if (Schema::hasColumn('service_categories', 'system_code')) {
                $table->dropUnique('service_categories_system_code_unique');
                $table->dropColumn('system_code');
            }
        });
    }

    private function backfillCategorySystemCodes(): void
    {
        $rows = DB::table('service_categories')
            ->orderBy('id')
            ->get(['id', 'name', 'short_code', 'system_code']);

        $usedCodes = [];
        foreach ($rows as $row) {
            $currentCode = trim((string) ($row->system_code ?? ''));
            if ($currentCode !== '') {
                $usedCodes[$currentCode] = true;
            }
        }

        foreach ($rows as $row) {
            $currentCode = trim((string) ($row->system_code ?? ''));
            if ($currentCode !== '') {
                continue;
            }

            $candidate = ServiceSystemCode::legacyCategoryCode(
                $row->short_code,
                $row->name,
                (int) $row->id
            );

            $uniqueCode = $this->reserveUniqueCode($candidate, $usedCodes);

            DB::table('service_categories')
                ->where('id', $row->id)
                ->update(['system_code' => $uniqueCode]);
        }
    }

    private function backfillParameterSystemCodes(): void
    {
        $categoryCodes = DB::table('service_categories')
            ->pluck('system_code', 'id');

        $rows = DB::table('service_parameters')
            ->orderBy('id')
            ->get(['id', 'service_category_id', 'name', 'short_code', 'system_code']);

        $usedCodes = [];
        foreach ($rows as $row) {
            $currentCode = trim((string) ($row->system_code ?? ''));
            if ($currentCode !== '') {
                $usedCodes[$currentCode] = true;
            }
        }

        foreach ($rows as $row) {
            $currentCode = trim((string) ($row->system_code ?? ''));
            if ($currentCode !== '') {
                continue;
            }

            $categoryCode = (string) ($categoryCodes[$row->service_category_id] ?? 'CAT');
            $candidate = ServiceSystemCode::legacyParameterCode(
                $categoryCode,
                $row->short_code,
                $row->name,
                (int) $row->id
            );

            $uniqueCode = $this->reserveUniqueCode($candidate, $usedCodes);

            DB::table('service_parameters')
                ->where('id', $row->id)
                ->update(['system_code' => $uniqueCode]);
        }
    }

    private function reserveUniqueCode(string $baseCode, array &$usedCodes): string
    {
        $candidate = $baseCode;
        $suffix = 2;

        while (isset($usedCodes[$candidate])) {
            $candidate = $baseCode . '_' . $suffix;
            $suffix++;
        }

        $usedCodes[$candidate] = true;

        return $candidate;
    }
};
