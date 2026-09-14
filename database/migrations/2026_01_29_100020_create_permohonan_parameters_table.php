<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonans')->cascadeOnDelete();
            $table->foreignId('service_parameter_id')->nullable()->constrained('service_parameters')->nullOnDelete();
            $table->string('parameter_name');
            $table->string('location_name')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 15, 2)->default(0);
            $table->text('note')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_parameters');
    }
};
