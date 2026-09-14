<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonan_assignments', function (Blueprint $table) {
            $table->boolean('is_leader')->default(false)->after('role');
            $table->index(['permohonan_id', 'role', 'is_leader'], 'permohonan_assignments_leader_idx');
        });
    }

    public function down(): void
    {
        Schema::table('permohonan_assignments', function (Blueprint $table) {
            $table->dropIndex('permohonan_assignments_leader_idx');
            $table->dropColumn('is_leader');
        });
    }
};
