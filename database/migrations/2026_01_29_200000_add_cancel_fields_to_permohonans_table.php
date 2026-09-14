<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('cancel_reason', 255)->nullable()->after('status_global');
            $table->text('cancel_note')->nullable()->after('cancel_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancel_note');
        });
    }

    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancel_note', 'cancelled_at']);
        });
    }
};
