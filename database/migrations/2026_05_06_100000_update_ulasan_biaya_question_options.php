<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ulasan_permohonan_questions')
            ->where('question', 'Bagaimana pendapat Saudara tentang kewajaran biaya/tarif pengujian dalam pelayanan?')
            ->update([
                'note' => 'Pilih salah satu opsi yang sesuai',
                'rating_labels' => json_encode([
                    'Harga tidak sesuai dengan apa yg tercantum dalam website',
                    '',
                    '',
                    'Harga sesuai dengan apa yg tercantum dalam website',
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('ulasan_permohonan_questions')
            ->where('question', 'Bagaimana pendapat Saudara tentang kewajaran biaya/tarif pengujian dalam pelayanan?')
            ->update([
                'note' => 'Skala 1-4',
                'rating_labels' => json_encode([
                    'Diatas pola tarif Nomor 6/PMK.02/2023',
                    'Dibawah pola tarif Nomor 6/PMK.02/2023',
                    'Sesuai pola tarif Nomor 6/PMK.02/2023',
                    'Gratis',
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
    }
};
