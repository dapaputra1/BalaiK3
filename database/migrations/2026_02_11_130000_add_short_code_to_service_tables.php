<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('service_categories', 'short_code')) {
                $table->string('short_code', 20)->nullable()->after('name');
                $table->unique('short_code', 'service_categories_short_code_unique');
            }
        });

        Schema::table('service_parameters', function (Blueprint $table) {
            if (!Schema::hasColumn('service_parameters', 'short_code')) {
                $table->string('short_code', 30)->nullable()->after('name');
                $table->unique('short_code', 'service_parameters_short_code_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_parameters', function (Blueprint $table) {
            if (Schema::hasColumn('service_parameters', 'short_code')) {
                $table->dropUnique('service_parameters_short_code_unique');
                $table->dropColumn('short_code');
            }
        });

        Schema::table('service_categories', function (Blueprint $table) {
            if (Schema::hasColumn('service_categories', 'short_code')) {
                $table->dropUnique('service_categories_short_code_unique');
                $table->dropColumn('short_code');
            }
        });
    }
};
