<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absorbance_formulas', function (Blueprint $table) {
            $table->id();
            $table->string('parameter_key', 20);
            $table->decimal('intercept', 12, 6)->default(0);
            $table->decimal('slope', 12, 6);
            $table->date('effective_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['parameter_key', 'is_active']);
        });

        DB::table('absorbance_formulas')->insert([
            [
                'parameter_key' => 'NO2',
                'intercept' => 0,
                'slope' => 0.9517,
                'effective_date' => now()->toDateString(),
                'notes' => 'Nilai awal default sistem.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'parameter_key' => 'OX',
                'intercept' => 0.0244,
                'slope' => 0.8264,
                'effective_date' => now()->toDateString(),
                'notes' => 'Nilai awal acuan Excel OX.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('absorbance_formulas');
    }
};
