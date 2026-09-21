<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            $table->string('billing_guide_path')->nullable()->after('billing_file_name');
            $table->string('billing_guide_name')->nullable()->after('billing_guide_path');
            $table->string('billing_proof_status', 20)->nullable()->default('pending')->after('billing_proof_name');
            $table->timestamp('billing_proof_rejected_at')->nullable()->after('billing_proof_status');
            $table->unsignedBigInteger('billing_proof_rejected_by')->nullable()->after('billing_proof_rejected_at');
            $table->text('billing_proof_reject_note')->nullable()->after('billing_proof_rejected_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suket_k3s', function (Blueprint $table) {
            $table->dropColumn([
                'billing_guide_path',
                'billing_guide_name',
                'billing_proof_status',
                'billing_proof_rejected_at',
                'billing_proof_rejected_by',
                'billing_proof_reject_note',
            ]);
        });
    }
};
