<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recover safely if a previous failed migration left the table behind.
        Schema::dropIfExists('beritas');

        Schema::create('beritas', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        if (DB::table('beritas')->count() > 0) {
            return;
        }

        $uploaderId = DB::table('users')
            ->whereIn('role', ['superadmin', 'admin'])
            ->orderBy('id')
            ->value('id');

        $articles = [
            [
                'user_id' => $uploaderId,
                'title' => 'Pelatihan K3 Dasar untuk Industri Manufaktur',
                'slug' => 'pelatihan-k3-dasar-untuk-industri-manufaktur',
                'content' => "Program ini berlangsung selama tiga hari dengan rangkaian kegiatan yang mencakup pemaparan materi, praktik lapangan, serta simulasi penilaian risiko di lingkungan kerja. Peserta tidak hanya mendapatkan pemahaman teori mengenai keselamatan dan kesehatan kerja (K3), tetapi juga dilatih untuk mengidentifikasi potensi bahaya yang mungkin terjadi di tempat kerja serta menyusun langkah-langkah pengendalian yang tepat. Melalui kegiatan ini, peserta diharapkan mampu menerapkan prinsip-prinsip K3 secara efektif dalam aktivitas kerja sehari-hari.\n\nDalam sesi diskusi dan praktik, para narasumber menekankan pentingnya membangun budaya kerja yang aman dan berkelanjutan di lingkungan perusahaan maupun instansi. Hal ini meliputi penggunaan alat pelindung diri (APD) yang sesuai dengan standar keselamatan, penerapan prosedur kerja yang aman, serta peningkatan kesadaran pekerja terhadap potensi risiko yang ada. Selain itu, peserta juga diberikan pemahaman mengenai pentingnya sistem pelaporan insiden secara cepat dan tepat agar setiap kejadian dapat segera ditangani serta menjadi bahan evaluasi untuk mencegah terulangnya insiden serupa di masa mendatang.\n\nSebagai tindak lanjut dari kegiatan pelatihan ini, Balai K3 Surabaya juga menyediakan pendampingan pasca pelatihan melalui program klinik konsultasi K3. Program ini bertujuan untuk membantu peserta dan instansi dalam menerapkan hasil pelatihan secara konsisten di tempat kerja masing-masing. Melalui layanan konsultasi tersebut, peserta dapat memperoleh arahan dan solusi terkait berbagai tantangan dalam implementasi K3, sehingga upaya peningkatan keselamatan dan kesehatan kerja dapat berjalan secara berkelanjutan.",
                'image_path' => 'images/header2.jpg',
                'created_at' => Carbon::create(2026, 2, 26, 9, 0, 0),
                'updated_at' => Carbon::create(2026, 2, 26, 9, 0, 0),
            ],
            [
                'user_id' => $uploaderId,
                'title' => 'Penguatan Inspeksi Rutin Peralatan Kerja',
                'slug' => 'penguatan-inspeksi-rutin-peralatan-kerja',
                'content' => "Balai K3 melaksanakan penguatan inspeksi rutin peralatan kerja sebagai bagian dari upaya peningkatan keselamatan kerja di lingkungan industri dan pelayanan publik. Kegiatan ini difokuskan pada pemeriksaan kondisi teknis peralatan, evaluasi kepatuhan prosedur operasional, serta identifikasi dini terhadap potensi kegagalan fungsi yang dapat berdampak pada keselamatan pekerja.\n\nMelalui inspeksi yang dilakukan secara terjadwal, setiap unit kerja diharapkan mampu memastikan bahwa seluruh peralatan berada dalam kondisi laik pakai dan memenuhi standar K3 yang berlaku. Balai K3 juga mendorong penanggung jawab teknis untuk memperkuat dokumentasi hasil inspeksi, tindak lanjut perbaikan, dan pelaporan temuan lapangan agar proses pengendalian risiko berjalan lebih sistematis.\n\nKegiatan ini menjadi bagian dari komitmen berkelanjutan Balai K3 Surabaya dalam mendukung terciptanya budaya kerja yang aman, tertib, dan responsif terhadap risiko. Dengan inspeksi yang konsisten, perusahaan dan instansi diharapkan dapat menekan potensi insiden kerja serta menjaga keberlangsungan operasional secara lebih optimal.",
                'image_path' => 'images/header3.jpg',
                'created_at' => Carbon::create(2026, 2, 24, 10, 0, 0),
                'updated_at' => Carbon::create(2026, 2, 24, 10, 0, 0),
            ],
            [
                'user_id' => $uploaderId,
                'title' => 'Penguatan Budaya Pelaporan Insiden di Lingkungan Kerja',
                'slug' => 'penguatan-budaya-pelaporan-insiden-di-lingkungan-kerja',
                'content' => "Balai K3 Surabaya mendorong penguatan budaya pelaporan insiden di lingkungan kerja sebagai salah satu langkah preventif untuk menekan risiko kecelakaan kerja. Pelaporan yang cepat dan akurat menjadi dasar penting dalam proses investigasi, evaluasi, dan tindak lanjut perbaikan di lapangan.\n\nMelalui pembinaan ini, setiap unit kerja diharapkan memiliki mekanisme pencatatan insiden yang tertib, mudah diakses, dan dapat dipahami oleh seluruh pekerja. Balai K3 juga mengingatkan bahwa pelaporan bukan sekadar formalitas, tetapi bagian dari sistem manajemen keselamatan yang harus dijalankan secara konsisten.\n\nDengan sistem pelaporan yang baik, organisasi akan lebih cepat mengidentifikasi akar masalah dan menetapkan tindakan pencegahan yang relevan. Upaya ini diharapkan memperkuat budaya kerja yang terbuka, bertanggung jawab, dan berorientasi pada keselamatan jangka panjang.",
                'image_path' => 'images/header2.jpg',
                'created_at' => Carbon::create(2026, 2, 23, 8, 30, 0),
                'updated_at' => Carbon::create(2026, 2, 23, 8, 30, 0),
            ],
            [
                'user_id' => $uploaderId,
                'title' => 'Peningkatan Kompetensi Tenaga Kerja Melalui Pelatihan K3',
                'slug' => 'peningkatan-kompetensi-tenaga-kerja-melalui-pelatihan-k3',
                'content' => "Program pelatihan K3 yang diselenggarakan Balai K3 Surabaya diarahkan untuk meningkatkan kompetensi tenaga kerja dalam menerapkan prinsip keselamatan kerja secara praktis di lapangan. Materi yang diberikan menekankan aspek identifikasi bahaya, penggunaan alat pelindung diri, serta pengendalian risiko pada aktivitas operasional harian.\n\nPeserta juga mendapatkan simulasi kasus untuk memperkuat pemahaman terhadap prosedur tanggap darurat dan pengambilan keputusan yang cepat saat menghadapi situasi berisiko. Pendekatan ini diharapkan membantu perusahaan membangun SDM yang lebih siap, sigap, dan patuh terhadap standar keselamatan.\n\nPelatihan berkelanjutan menjadi salah satu strategi penting untuk membentuk budaya kerja yang aman dan produktif. Karena itu, Balai K3 terus mendorong kolaborasi dengan berbagai pihak agar peningkatan kompetensi tenaga kerja dapat berlangsung secara konsisten.",
                'image_path' => 'images/header3.jpg',
                'created_at' => Carbon::create(2026, 2, 22, 14, 0, 0),
                'updated_at' => Carbon::create(2026, 2, 22, 14, 0, 0),
            ],
        ];

        DB::table('beritas')->insert($articles);

        if (Schema::hasTable('berita_views')) {
            $views = [
                ['slug' => 'pelatihan-k3-dasar-untuk-industri-manufaktur', 'views_count' => 14, 'created_at' => now(), 'updated_at' => now()],
                ['slug' => 'penguatan-inspeksi-rutin-peralatan-kerja', 'views_count' => 8, 'created_at' => now(), 'updated_at' => now()],
                ['slug' => 'penguatan-budaya-pelaporan-insiden-di-lingkungan-kerja', 'views_count' => 6, 'created_at' => now(), 'updated_at' => now()],
                ['slug' => 'peningkatan-kompetensi-tenaga-kerja-melalui-pelatihan-k3', 'views_count' => 5, 'created_at' => now(), 'updated_at' => now()],
            ];

            foreach ($views as $view) {
                DB::table('berita_views')->updateOrInsert(
                    ['slug' => $view['slug']],
                    $view
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('berita_views') && Schema::hasTable('beritas')) {
            $slugs = DB::table('beritas')->pluck('slug');
            if ($slugs->isNotEmpty()) {
                DB::table('berita_views')->whereIn('slug', $slugs)->delete();
            }
        }

        Schema::dropIfExists('beritas');
    }
};
