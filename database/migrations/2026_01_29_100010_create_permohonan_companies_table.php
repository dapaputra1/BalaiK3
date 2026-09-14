<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonans')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('responsible_name');
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_type')->nullable();
            $table->string('company_province')->nullable();
            $table->string('company_city')->nullable();
            $table->text('company_address')->nullable();
            $table->boolean('authority_same')->default(false);
            $table->string('authority_name')->nullable();
            $table->string('authority_role')->nullable();
            $table->timestamps();

            $table->unique('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_companies');
    }
};
