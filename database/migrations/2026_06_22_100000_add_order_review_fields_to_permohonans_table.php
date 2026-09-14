<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('order_review_status')->default('approved')->after('status_global');
            $table->json('order_review_original_parameters')->nullable()->after('order_review_status');
            $table->text('order_review_note')->nullable()->after('order_review_original_parameters');
            $table->integer('order_reviewed_by')->nullable()->after('order_review_note');
            $table->timestamp('order_reviewed_at')->nullable()->after('order_reviewed_by');
            $table->timestamp('order_review_sent_at')->nullable()->after('order_reviewed_at');
            $table->timestamp('order_review_customer_approved_at')->nullable()->after('order_review_sent_at');
            $table->integer('order_review_customer_approved_by')->nullable()->after('order_review_customer_approved_at');

            $table->index('order_review_status');
            $table->foreign('order_reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('order_review_customer_approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropForeign(['order_reviewed_by']);
            $table->dropForeign(['order_review_customer_approved_by']);
            $table->dropIndex(['order_review_status']);
            $table->dropColumn([
                'order_review_status',
                'order_review_original_parameters',
                'order_review_note',
                'order_reviewed_by',
                'order_reviewed_at',
                'order_review_sent_at',
                'order_review_customer_approved_at',
                'order_review_customer_approved_by',
            ]);
        });
    }
};
