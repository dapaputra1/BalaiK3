<?php

namespace Database\Seeders;

use App\Models\Berita;
use App\Models\BeritaView;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BeritaSeeder extends Seeder
{
    public function run(): void
    {
        $uploaderId = User::query()
            ->whereIn('role', ['superadmin', 'admin'])
            ->orderBy('id')
            ->value('id')
            ?? User::query()->orderBy('id')->value('id');

        $legacySlugs = [
            'pelatihan-k3-dasar-untuk-industri-manufaktur',
            'penguatan-inspeksi-rutin-peralatan-kerja',
            'penguatan-budaya-pelaporan-insiden-di-lingkungan-kerja',
            'peningkatan-kompetensi-tenaga-kerja-melalui-pelatihan-k3',
        ];

        BeritaView::query()->whereIn('slug', $legacySlugs)->delete();
        Berita::query()->whereIn('slug', $legacySlugs)->delete();

        $articles = [
            [
                'slug' => 'balai-k3-surabaya-dukung-program-k3-peduli-mudik-untuk-wujudkan-perjalanan-aman-dan-selamat',
                'title' => 'Balai K3 Surabaya Dukung Program “K3 Peduli Mudik” untuk Wujudkan Perjalanan Aman dan Selamat',
                'content' => 'Surabaya — Balai Keselamatan dan Kesehatan Kerja (K3) Surabaya turut mendukung pelaksanaan program “K3 Peduli Mudik” sebagai bagian dari upaya peningkatan keselamatan transportasi selama periode mudik Lebaran. Kegiatan ini dilaksanakan di Terminal Purabaya dan menyasar para pengemudi bus sebagai garda terdepan dalam keselamatan perjalanan. Program ini dilaksanakan dalam dua periode, yaitu pada saat arus mudik dan arus balik, guna memastikan kondisi pengemudi tetap prima selama berlangsungnya mobilitas masyarakat yang tinggi.

Dalam kegiatan ini, Balai K3 Surabaya memberikan layanan pemeriksaan kesehatan dasar kepada para pengemudi bus. Pemeriksaan ini bertujuan untuk mengetahui kondisi kesehatan secara umum serta mendeteksi potensi gangguan yang dapat memengaruhi keselamatan berkendara. Selain itu, dilakukan pula serangkaian pengujian untuk mendeteksi dini gejala kelelahan, di antaranya:
•	Tes keseimbangan tubuh untuk menilai stabilitas fisik pengemudi 
•	Monitor test untuk mengukur respons visual dan konsentrasi 
•	Psychomotor Vigilance Test (PVT) untuk menilai tingkat kewaspadaan dan kecepatan reaksi 
Pengujian ini menjadi penting mengingat kelelahan merupakan salah satu faktor risiko utama yang dapat menyebabkan kecelakaan lalu lintas, khususnya pada perjalanan jarak jauh.
Kegiatan “K3 Peduli Mudik” merupakan bentuk pendekatan preventif dalam penerapan prinsip keselamatan dan kesehatan kerja di sektor transportasi. Dengan melakukan deteksi dini terhadap kondisi pengemudi, diharapkan potensi risiko kecelakaan dapat diminimalkan. Selain pemeriksaan, pengemudi juga diberikan edukasi singkat mengenai pentingnya menjaga kondisi fisik, waktu istirahat yang cukup, serta kewaspadaan selama berkendara.

Partisipasi Balai K3 Surabaya dalam kegiatan ini merupakan wujud komitmen dalam mendukung terciptanya perjalanan mudik yang aman, nyaman, dan selamat bagi masyarakat. Melalui sinergi dengan berbagai pihak, kegiatan ini diharapkan dapat meningkatkan kesadaran akan pentingnya keselamatan kerja di sektor transportasi, sekaligus melindungi pengemudi dan penumpang dari risiko kecelakaan.

Balai K3 Surabaya akan terus berperan aktif dalam berbagai program promotif dan preventif guna mendukung terciptanya budaya K3 di berbagai sektor, termasuk dalam momentum penting seperti mudik Lebaran.',
                'image_path' => 'images/berita/cdbca8d9-8157-4e24-a5ab-390b1f70ddb3.jpg',
                'created_at' => Carbon::create(2026, 3, 17, 15, 47, 0),
                'updated_at' => Carbon::create(2026, 4, 1, 8, 49, 44),
                'views_count' => 0,
                'views_created_at' => Carbon::create(2026, 4, 1, 8, 48, 19),
                'views_updated_at' => Carbon::create(2026, 4, 1, 8, 48, 19),
            ],
            [
                'slug' => 'evaluasi-pembinaan-ahli-k3-umum-di-balai-k3-surabaya-perkuat-kompetensi-dan-penerapan-k3-di-tempat-kerja',
                'title' => 'Evaluasi Pembinaan Ahli K3 Umum di Balai K3 Surabaya Perkuat Kompetensi dan Penerapan K3 di Tempat Kerja',
                'content' => 'Surabaya — Balai Keselamatan dan Kesehatan Kerja (K3) Surabaya menyelenggarakan kegiatan evaluasi pembinaan Ahli K3 Umum (AK3U) yang dilaksanakan oleh Penanggung Jawab (PJ) K3. Kegiatan ini merupakan bagian dari upaya berkelanjutan dalam meningkatkan kualitas pembinaan serta memperkuat implementasi keselamatan dan kesehatan kerja di tempat kerja.

Sebelumnya, kegiatan pembinaan Ahli K3 Umum telah dilaksanakan selama 12 hari, mulai tanggal 25 Februari hingga 12 Maret. Pembinaan ini dirancang untuk membekali peserta dengan pengetahuan, keterampilan, serta pemahaman komprehensif terkait penerapan norma K3 di berbagai sektor kerja.

Kegiatan evaluasi ini menjadi tahap penting untuk menilai efektivitas proses pembinaan yang telah dilakukan, sekaligus mengukur tingkat pemahaman dan kesiapan peserta dalam mengimplementasikan K3 di lingkungan kerja masing-masing. Melalui evaluasi yang dilakukan secara sistematis, diharapkan dapat diperoleh gambaran menyeluruh terkait capaian pembelajaran serta aspek yang perlu ditingkatkan ke depannya.
Ahli K3 Umum memiliki peran strategis dalam memastikan penerapan prinsip keselamatan dan kesehatan kerja berjalan secara optimal di tempat kerja. Oleh karena itu, peningkatan kompetensi melalui pembinaan dan evaluasi menjadi hal yang sangat penting. Kegiatan ini diharapkan dapat mendorong para peserta untuk tidak hanya memahami aspek teknis K3, tetapi juga mampu menjadi agen perubahan dalam membangun budaya K3 di lingkungan kerja.

Melalui penyelenggaraan evaluasi pembinaan ini, Balai K3 Surabaya menegaskan komitmennya dalam mendukung peningkatan kualitas sumber daya manusia di bidang K3. Diharapkan para Ahli K3 Umum yang telah mengikuti pembinaan dan evaluasi dapat terus meningkatkan kompetensi serta berperan aktif dalam menciptakan lingkungan kerja yang aman, sehat, dan produktif.

Balai K3 Surabaya akan terus berupaya menghadirkan program pembinaan yang berkualitas sebagai bagian dari kontribusi dalam memperkuat budaya keselamatan dan kesehatan kerja di Indonesia.',
                'image_path' => 'images/berita/46e634cf-2118-47e6-afc0-ec5f3fa1a910.jpg',
                'created_at' => Carbon::create(2026, 3, 11, 15, 35, 0),
                'updated_at' => Carbon::create(2026, 4, 1, 8, 36, 6),
                'views_count' => 1,
                'views_created_at' => Carbon::create(2026, 4, 1, 8, 35, 29),
                'views_updated_at' => Carbon::create(2026, 4, 1, 11, 26, 19),
            ],
            [
                'slug' => 'kunjungan-mahasiswa-universitas-kepanjen-malang-ke-balai-k3-surabaya-meningkatkan-pemahaman-praktis-laboratorium-k3',
                'title' => 'Kunjungan Mahasiswa Universitas Kepanjen Malang ke Balai K3 Surabaya: Meningkatkan Pemahaman Praktis Laboratorium K3',
                'content' => 'Surabaya — Balai Keselamatan dan Kesehatan Kerja (K3) Surabaya menerima kunjungan mahasiswa dari Universitas Kepanjen Malang dalam rangka kegiatan pembelajaran lapangan untuk meningkatkan pemahaman praktis di bidang keselamatan dan kesehatan kerja. Kunjungan ini menjadi bagian dari upaya penguatan kompetensi mahasiswa melalui pengenalan langsung terhadap proses pengujian dan analisis di laboratorium K3, yang selama ini lebih banyak dipelajari secara teoritis di bangku perkuliahan.

Dalam kegiatan ini, para mahasiswa mendapatkan pembekalan terkait fungsi dan peran laboratorium pengujian K3 sebagai salah satu elemen penting dalam pengendalian risiko di tempat kerja. Peserta diperkenalkan pada berbagai jenis pengujian yang dilakukan di Balai K3 Surabaya, mencakup pengujian dari berbagai faktor risiko, antara lain:
•	Faktor kimia, seperti pengujian paparan bahan berbahaya di lingkungan kerja 
•	Faktor fisika, meliputi kebisingan, pencahayaan, getaran, dan iklim kerja 
•	Faktor biologi, terkait potensi paparan mikroorganisme yang dapat memengaruhi kesehatan pekerja 

Selain pemaparan materi, mahasiswa juga diberikan kesempatan untuk mengenal secara langsung berbagai peralatan yang digunakan dalam pengujian K3 di laboratorium. Petugas laboratorium menjelaskan fungsi, cara kerja, serta standar penggunaan alat-alat tersebut dalam proses pengambilan dan analisis sampel. Hal ini memberikan gambaran nyata kepada mahasiswa mengenai prosedur kerja yang sesuai dengan standar keselamatan dan kualitas pengujian.

Melalui kegiatan ini, mahasiswa diharapkan dapat memahami pentingnya akurasi data dalam menentukan tingkat risiko serta sebagai dasar dalam pengambilan keputusan terkait pengendalian bahaya di tempat kerja.
Kegiatan kunjungan ini merupakan bentuk sinergi antara dunia pendidikan dan instansi pemerintah dalam mendukung pengembangan sumber daya manusia di bidang K3. Dengan adanya pembelajaran berbasis praktik, mahasiswa tidak hanya memperoleh pengetahuan, tetapi juga pengalaman langsung yang dapat meningkatkan kesiapan mereka dalam menghadapi dunia kerja.

Balai K3 Surabaya berkomitmen untuk terus mendukung kegiatan edukatif seperti kunjungan mahasiswa sebagai bagian dari upaya peningkatan kesadaran dan kompetensi di bidang keselamatan dan kesehatan kerja. Melalui kegiatan ini, diharapkan para mahasiswa dapat menjadi agen perubahan dalam penerapan K3 di masa depan serta turut berkontribusi dalam menciptakan lingkungan kerja yang aman, sehat, dan produktif.',
                'image_path' => 'images/berita/c773162c-6c31-4c47-9c97-9b4ddff4ec05.jpeg',
                'created_at' => Carbon::create(2026, 1, 28, 7, 57, 0),
                'updated_at' => Carbon::create(2026, 4, 1, 8, 13, 44),
                'views_count' => 1,
                'views_created_at' => Carbon::create(2026, 4, 1, 7, 57, 10),
                'views_updated_at' => Carbon::create(2026, 4, 1, 7, 59, 46),
            ],
            [
                'slug' => 'aktivasi-balai-k3-surabaya-perkuat-budaya-dan-layanan-k3-di-kawasan-timur-indonesia',
                'title' => 'Aktivasi Balai K3 Surabaya Perkuat Budaya dan Layanan K3 di Kawasan Timur Indonesia',
                'content' => 'Surabaya — Aktivasi Balai Keselamatan dan Kesehatan Kerja (K3) Surabaya menjadi langkah nyata dalam memperkuat budaya serta layanan K3, khususnya di wilayah Indonesia Timur. Inisiatif ini sejalan dengan semangat Re-Energizing OSH Services in East Indonesia sebagai upaya revitalisasi layanan K3 yang lebih adaptif dan berdampak. Menteri Ketenagakerjaan Republik Indonesia, Yassierli, menegaskan bahwa penguatan peran Balai K3 merupakan bagian penting dalam meningkatkan kualitas perlindungan tenaga kerja di Indonesia.

“Aktivasi Balai K3 Surabaya merupakan langkah strategis dalam memperkuat layanan keselamatan dan kesehatan kerja, khususnya di wilayah Indonesia Timur, sehingga mampu memberikan perlindungan yang lebih optimal bagi tenaga kerja,” ujar Menteri Ketenagakerjaan RI.
Sejalan dengan arahan Menteri Ketenagakerjaan, Balai K3 Surabaya diharapkan dapat menjalankan peran yang lebih strategis dalam memastikan tegaknya norma ketenagakerjaan dan K3 di lingkungan kerja. Tidak hanya terbatas pada kegiatan pengukuran dan pengujian, Balai K3 Surabaya didorong untuk “naik kelas” sebagai pengampu kinerja K3 di wilayah kerjanya, yang meliputi Jawa Timur, Bali, Nusa Tenggara Barat, dan Nusa Tenggara Timur.
“Balai K3 harus mampu berkembang menjadi pusat layanan yang tidak hanya bersifat teknis, tetapi juga berperan aktif dalam pembinaan dan penguatan budaya K3 di dunia kerja,” tambahnya.

Melalui aktivasi ini, penguatan layanan K3 dilakukan secara terintegrasi dengan mendorong kolaborasi lintas sektor. Sinergi antara pemerintah, dunia usaha, dan masyarakat menjadi faktor penting dalam mewujudkan implementasi K3 yang efektif. Pendekatan yang dilakukan tidak hanya berfokus pada penanganan risiko, tetapi juga pada upaya preventif dan promotif melalui edukasi, pembinaan, serta peningkatan kompetensi di bidang K3.

Balai K3 Surabaya berkomitmen untuk terus menghadirkan layanan yang profesional, inovatif, dan responsif terhadap kebutuhan dunia kerja.
“Melalui penguatan fungsi dan kolaborasi lintas sektor, kita ingin memastikan bahwa budaya K3 benar-benar terinternalisasi dalam setiap aktivitas kerja,” tegas Menteri Ketenagakerjaan RI.
Aktivasi ini menjadi langkah penguatan peran Balai K3 Surabaya dalam mendorong terciptanya lingkungan kerja yang aman, sehat, dan produktif, sekaligus mendukung pencapaian target nasional dalam menurunkan angka kecelakaan kerja di Indonesia.',
                'image_path' => 'images/berita/e99ed327-c18b-43d5-97c4-9e4bffb8e3a1.jpg',
                'created_at' => Carbon::create(2026, 1, 5, 15, 17, 0),
                'updated_at' => Carbon::create(2026, 4, 1, 8, 30, 15),
                'views_count' => 0,
                'views_created_at' => Carbon::create(2026, 4, 1, 8, 18, 5),
                'views_updated_at' => Carbon::create(2026, 4, 1, 8, 18, 5),
            ],
            [
                'slug' => 'pemeriksaan-iva-test-bagi-tenaga-kerja-perempuan-upaya-deteksi-dini-dan-perlindungan-kesehatan-reproduksi',
                'title' => 'Pemeriksaan IVA Test bagi Tenaga Kerja Perempuan: Upaya Deteksi Dini dan Perlindungan Kesehatan Reproduksi',
                'content' => 'Surabaya — Balai Keselamatan dan Kesehatan Kerja (K3) Surabaya bersama Direktorat Bina Pengujian K3 menyelenggarakan kegiatan pemeriksaan IVA (Inspeksi Visual dengan Asam Asetat) bagi tenaga kerja perempuan, bekerja sama dengan Dinas Kesehatan Kabupaten Sidoarjo. Kegiatan ini dilaksanakan pada tanggal 18–20 Desember dan diikuti oleh kurang lebih 250 tenaga kerja perempuan.
Kegiatan ini merupakan bagian dari upaya promotif dan preventif dalam mendukung perlindungan kesehatan reproduksi di lingkungan kerja, khususnya melalui deteksi dini terhadap risiko penyakit kanker serviks.

Pemeriksaan IVA Test merupakan metode skrining sederhana yang efektif untuk mendeteksi secara dini adanya perubahan pada leher rahim yang berpotensi berkembang menjadi kanker serviks. Melalui kegiatan ini, para peserta mendapatkan akses pemeriksaan kesehatan yang mudah, cepat, dan terjangkau. Selain pemeriksaan, peserta juga diberikan edukasi terkait pentingnya menjaga kesehatan reproduksi serta langkah-langkah pencegahan yang dapat dilakukan sejak dini.
Pelaksanaan kegiatan ini menunjukkan pentingnya kolaborasi lintas sektor dalam meningkatkan kualitas kesehatan tenaga kerja. Sinergi antara Balai K3 Surabaya, Direktorat Bina Pengujian K3, dan Dinas Kesehatan Kabupaten Sidoarjo menjadi kunci dalam menghadirkan layanan kesehatan yang komprehensif bagi pekerja.
Kegiatan ini tidak hanya berfokus pada pemeriksaan, tetapi juga sebagai bentuk kepedulian terhadap kesejahteraan pekerja perempuan, yang merupakan bagian penting dalam produktivitas kerja.

Balai K3 Surabaya berkomitmen untuk terus mendukung program-program kesehatan kerja yang berorientasi pada pencegahan dan peningkatan kualitas hidup tenaga kerja. Melalui kegiatan pemeriksaan IVA Test ini, diharapkan kesadaran tenaga kerja perempuan terhadap pentingnya deteksi dini semakin meningkat, sehingga dapat mencegah risiko penyakit yang lebih serius di masa mendatang.

Upaya ini sejalan dengan tujuan menciptakan lingkungan kerja yang tidak hanya aman, tetapi juga sehat dan produktif bagi seluruh pekerja.',
                'image_path' => 'images/berita/bcc90b50-47fe-4feb-9e85-376d5b07ce73.jpeg',
                'created_at' => Carbon::create(2025, 12, 10, 15, 14, 0),
                'updated_at' => Carbon::create(2026, 4, 1, 8, 16, 42),
                'views_count' => 0,
                'views_created_at' => Carbon::create(2026, 4, 1, 8, 15, 41),
                'views_updated_at' => Carbon::create(2026, 4, 1, 8, 15, 41),
            ],
        ];

        foreach ($articles as $article) {
            $berita = Berita::query()->updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'user_id' => $uploaderId,
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'image_path' => $article['image_path'],
                    'created_at' => $article['created_at'],
                    'updated_at' => $article['updated_at'],
                ]
            );

            BeritaView::query()->updateOrCreate(
                ['slug' => $berita->slug],
                [
                    'views_count' => $article['views_count'],
                    'created_at' => $article['views_created_at'],
                    'updated_at' => $article['views_updated_at'],
                ]
            );
        }
    }
}
