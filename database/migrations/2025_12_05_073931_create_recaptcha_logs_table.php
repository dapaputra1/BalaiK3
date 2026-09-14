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
        Schema::create('recaptcha_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('ip_address', 50)->nullable();
            $table->boolean('success')->nullable();
            $table->float('score')->nullable();
            $table->string('action', 100)->nullable();
            $table->text('message')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recaptcha_logs');
    }
};
