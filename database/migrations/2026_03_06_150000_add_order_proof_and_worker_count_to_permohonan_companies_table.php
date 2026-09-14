<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonan_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('permohonan_companies', 'worker_count')) {
                $table->unsignedInteger('worker_count')->nullable()->after('company_address');
            }
            if (!Schema::hasColumn('permohonan_companies', 'order_proof_path')) {
                $table->string('order_proof_path')->nullable()->after('worker_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_companies', function (Blueprint $table) {
            if (Schema::hasColumn('permohonan_companies', 'order_proof_path')) {
                $table->dropColumn('order_proof_path');
            }
            if (Schema::hasColumn('permohonan_companies', 'worker_count')) {
                $table->dropColumn('worker_count');
            }
        });
    }
};
