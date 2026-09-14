<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jejaring_entries', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('category', 50)->index();
            $table->string('name', 150);
            $table->text('address')->nullable();
            $table->text('website')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(['category', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jejaring_entries');
    }
};
