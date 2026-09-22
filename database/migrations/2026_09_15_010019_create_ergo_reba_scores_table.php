<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Data Perusahaan
        Schema::create('ergo_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('sector')->nullable(); // Jenis / Sektor Perusahaan
            $table->timestamps();
        });

        // 2. Data Utama Pengujian / Asesmen
        Schema::create('ergo_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('ergo_companies')->onDelete('cascade');
            $table->string('lhu_number')->nullable(); // No Dokumen Pengujian LHU
            $table->string('pic_name')->nullable();    // Penanggung Jawab Perusahaan
            $table->unsignedBigInteger('surveyor_id')->nullable(); // Petugas Penguji Balai K3
            $table->string('department')->nullable();
            $table->date('assessment_date');
            $table->decimal('shift_hours', 4, 1)->default(8.0);
            $table->string('method')->default('SNI 9011:2021');
            $table->text('existing_control')->nullable(); // Pengendalian yang sudah ada
            $table->enum('status', ['draft', 'submitted', 'approved'])->default('draft');
            $table->timestamps();
        });

        // 3. Profil Pekerja yang Diuji
        Schema::create('ergo_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('ergo_assessments')->onDelete('cascade');
            $table->string('name');
            $table->string('position')->nullable(); // Jabatan / Posisi
            $table->text('job_tasks')->nullable();  // Uraian Tugas
            $table->string('job_duration')->nullable();
            $table->string('dominant_hand')->default('Kanan');
            $table->string('work_duration_level')->nullable(); // Masa kerja
            $table->string('mental_fatigue')->nullable();
            $table->string('physical_fatigue')->nullable();
            $table->boolean('has_pain_last_year')->default(false);
            $table->timestamps();
        });

        // 4. Skor Penilaian SNI 9011:2021
        Schema::create('ergo_reba_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('ergo_workers')->onDelete('cascade');
            
            // Parameter Skor
            $table->integer('upper_body_score')->default(0); // Tubuh Bagian Atas (Butir 1-16)
            $table->integer('lower_body_score')->default(0); // Punggung & Bawah (Butir 17-31)
            $table->integer('mmh_score')->default(0);        // Beban Manual (MMH)
            
            // Hasil Akhir
            $table->decimal('final_score', 5, 2)->default(0);
            $table->string('risk_level')->nullable();        // Aman / Perlu Pengamatan / Berbahaya
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Tabel Multi-Foto & Koordinat JSON Sudut Interaktif
        Schema::create('ergo_assessment_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('ergo_assessments')->onDelete('cascade');
            $table->string('photo_name')->nullable();
            $table->string('file_path');                     // Lokasi file gambar di storage
            $table->json('landmarks_json')->nullable();      // Titik koordinat hasil geser kanvas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ergo_assessment_photos');
        Schema::dropIfExists('ergo_reba_scores');
        Schema::dropIfExists('ergo_workers');
        Schema::dropIfExists('ergo_assessments');
        Schema::dropIfExists('ergo_companies');
    }
};