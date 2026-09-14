<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('draft_lhus', function (Blueprint $table) {
            if (!Schema::hasColumn('draft_lhus', 'billing_payment_proof_path')) {
                $table->string('billing_payment_proof_path')->nullable()->after('billing_expires_at');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_payment_proof_name')) {
                $table->string('billing_payment_proof_name')->nullable()->after('billing_payment_proof_path');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_payment_proof_uploaded_by')) {
                $table->integer('billing_payment_proof_uploaded_by')->nullable()->after('billing_payment_proof_name');
            }
            if (!Schema::hasColumn('draft_lhus', 'billing_payment_proof_uploaded_at')) {
                $table->timestamp('billing_payment_proof_uploaded_at')->nullable()->after('billing_payment_proof_uploaded_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('draft_lhus', function (Blueprint $table) {
            $columns = [
                'billing_payment_proof_path',
                'billing_payment_proof_name',
                'billing_payment_proof_uploaded_by',
                'billing_payment_proof_uploaded_at',
            ];

            $existing = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn('draft_lhus', $column)));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
