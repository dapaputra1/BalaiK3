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
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('name', 100);
                $table->string('email', 150)->unique('email');
                $table->string('password');
                $table->boolean('is_active')->nullable()->default(true);
                $table->string('signature_path')->nullable();
                $table->enum('role', ['user', 'admin', 'superadmin', 'ma', 'mp', 'mt', 'penyelia', 'pcu', 'analis', 'qc', 'bendahara'])->nullable()->default('user');
                $table->dateTime('created_at')->nullable()->useCurrent();
                $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->nullable()->default(true);
                }
                if (!Schema::hasColumn('users', 'signature_path')) {
                    $table->string('signature_path')->nullable();
                }
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->nullable()->default('user');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
