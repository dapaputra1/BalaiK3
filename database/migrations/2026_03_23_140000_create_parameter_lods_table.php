<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('parameter_lods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_parameter_id')->constrained('service_parameters')->cascadeOnDelete();
            $table->decimal('kons', 12, 4);
            $table->decimal('vol', 12, 4);
            $table->decimal('waktu', 12, 2);
            $table->decimal('fr', 12, 4);
            $table->decimal('sk', 12, 4);
            $table->decimal('pm', 12, 2);
            $table->decimal('factor_ppm', 12, 6);
            $table->decimal('factor_ugm3', 12, 6);
            $table->decimal('sample_kons', 12, 4)->nullable();
            $table->decimal('sample_vol', 12, 4)->nullable();
            $table->decimal('sample_waktu', 12, 2)->nullable();
            $table->decimal('sample_fr', 12, 4)->nullable();
            $table->decimal('sample_sk', 12, 4)->nullable();
            $table->decimal('sample_pm', 12, 2)->nullable();
            $table->decimal('sample_ppm', 12, 4)->nullable();
            $table->decimal('sample_ugm3', 12, 4)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('service_parameter_id');
        });

        $so2AmbienParameterId = DB::table('service_parameters')
            ->join('service_categories', 'service_categories.id', '=', 'service_parameters.service_category_id')
            ->whereRaw('LOWER(service_parameters.name) = ?', ['so2'])
            ->where(function ($query) {
                $query->whereRaw('LOWER(service_categories.name) = ?', ['ambien'])
                    ->orWhereRaw('LOWER(service_categories.short_code) = ?', ['amb']);
            })
            ->value('service_parameters.id');

        if ($so2AmbienParameterId) {
            DB::table('parameter_lods')->insert([
                'service_parameter_id' => $so2AmbienParameterId,
                'kons' => 0.5218,
                'vol' => 10.0,
                'waktu' => 60,
                'fr' => 1.000,
                'sk' => 25.0,
                'pm' => 760,
                'factor_ppm' => 0.382,
                'factor_ugm3' => 2617.6,
                'sample_kons' => 0.0360,
                'sample_vol' => 10.0,
                'sample_waktu' => 60,
                'sample_fr' => 1.000,
                'sample_sk' => 29.0,
                'sample_pm' => 758,
                'sample_ppm' => 0.0002,
                'sample_ugm3' => 0.6,
                'notes' => 'Nilai awal LOD SO2 ambien.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parameter_lods');
    }
};
