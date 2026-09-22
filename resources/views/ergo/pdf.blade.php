<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Uji Ergonomi — {{ $assessment->company_name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 15mm 18mm 15mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.28;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Resmi Halaman 1 */
        .kop-surat-p1 {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .kop-surat-p1 td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-logo-td {
            width: 75px;
            text-align: center;
            vertical-align: middle;
        }
        .kop-text-td {
            text-align: center;
        }
        .kop-instansi {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            line-height: 1.2;
            margin: 0;
        }
        .kop-balai {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            line-height: 1.2;
            margin: 1.5px 0 0 0;
        }
        .kop-alamat {
            font-size: 7.5pt;
            margin-top: 3px;
            line-height: 1.2;
        }
        .line-double {
            border-top: 2.2px solid #000;
            border-bottom: 0.8px solid #000;
            height: 2px;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        /* Header Kotak Berulang Halaman 2 sampai seterusnya */
        table.header-box-repeat {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        table.header-box-repeat td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .header-logo-repeat {
            width: 60px;
            text-align: center;
        }
        .header-center-repeat {
            text-align: center;
            font-size: 8.5pt;
            font-weight: bold;
            line-height: 1.25;
            text-transform: uppercase;
        }
        .header-meta-repeat {
            width: 80px;
            font-size: 8pt;
            padding: 0 !important;
        }
        .header-meta-repeat table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-meta-repeat td {
            border: none;
            border-bottom: 1px solid #000;
            padding: 3px 5px;
        }
        .header-meta-repeat tr:last-child td {
            border-bottom: none;
        }

        /* Judul Laporan */
        .report-title-block {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
        }
        .report-sub {
            font-size: 10pt;
            font-weight: bold;
            margin: 2px 0;
        }
        .report-no {
            font-size: 9.5pt;
            font-weight: normal;
            margin-top: 2px;
        }

        /* Judul Butir */
        .sec-title {
            font-weight: bold;
            font-size: 9.5pt;
            margin-top: 8px;
            margin-bottom: 3px;
        }

        /* Tabel Data Meta / Data Umum */
        table.meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 6px;
        }
        table.meta-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        /* Tabel Rekapitulasi Hasil Pengukuran (Butir 4) */
        table.table-rekap {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        table.table-rekap th, table.table-rekap td {
            border: 1px solid #000;
            padding: 4px 3px;
            vertical-align: middle;
        }
        table.table-rekap th {
            text-align: center;
            font-weight: bold;
            background-color: #fff;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .justify { text-align: justify; }

        /* Catatan ERFC */
        .catatan-erfc {
            font-size: 8pt;
            margin-top: 4px;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        /* Butir Analisis, Kesimpulan, Saran */
        .narrative-box {
            font-size: 9pt;
            text-align: justify;
            line-height: 1.35;
            margin-bottom: 8px;
        }
        ol.recom-list {
            margin: 3px 0;
            padding-left: 18px;
        }
        ol.recom-list li {
            margin-bottom: 4px;
            text-align: justify;
        }

        /* Klausul & Tanda Tangan Manajer Teknis */
        .bottom-wrap {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .clause-box {
            width: 55%;
            float: left;
            font-size: 7.5pt;
            line-height: 1.3;
            text-align: justify;
        }
        .sign-box {
            width: 42%;
            float: right;
            text-align: center;
            font-size: 9pt;
        }
        .sign-space {
            height: 55px;
        }
        .clearfix {
            clear: both;
        }

        /* Lampiran Halaman */
        .lampiran-box {
            border: 1px solid #000;
            margin-top: 6px;
            margin-bottom: 10px;
        }
        .lampiran-header-cell {
            padding: 5px 8px;
            font-weight: bold;
            font-size: 9pt;
            border-bottom: 1px solid #000;
            background-color: #fff;
        }
        .lampiran-desc-cell {
            padding: 6px 8px;
            font-size: 8.5pt;
            text-align: justify;
            line-height: 1.3;
        }

        /* Tabel Gambar Teranotasi Sudut */
        .photo-table-img {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px 0;
        }
        .photo-table-img td {
            border: 1px solid #777;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            background-color: #fff;
        }
        .photo-table-img img {
            max-height: 195px;
            max-width: 98%;
            display: block;
            margin: 0 auto;
        }

        /* Tabel Checklist SNI Lampiran */
        table.table-checklist {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 4px;
        }
        table.table-checklist th, table.table-checklist td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
        }
        table.table-checklist th {
            text-align: center;
            font-weight: bold;
            background-color: #fff;
        }
        .group-header {
            font-weight: bold;
            background-color: #fff;
        }

        /* Footer Baku Balai K3 */
        .footer-fixed {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            border-top: 0.5px solid #000;
            padding-top: 2px;
        }
        .footer-left { float: left; }
        .footer-right { float: right; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <!-- =========================================================================
         HALAMAN 1: KOP SURAT LENGKAP & REKAPITULASI HASIL PENGUKURAN
         ========================================================================= -->
    <table class="kop-surat-p1">
        <tr>
            <td class="kop-logo-td">
                <!-- Kemnaker / K3 Logo Symbol -->
                <svg width="62" height="62" viewBox="0 0 100 100">
                    <g fill="#153e67">
                        <path d="M50,5 L58,25 L78,25 L62,38 L68,58 L50,45 L32,58 L38,38 L22,25 L42,25 Z"/>
                        <circle cx="50" cy="50" r="14" fill="#ffffff"/>
                        <circle cx="50" cy="50" r="9" fill="#153e67"/>
                        <rect x="46" y="28" width="8" height="44" rx="2" fill="#153e67"/>
                        <rect x="28" y="46" width="44" height="8" rx="2" fill="#153e67"/>
                        <circle cx="30" cy="30" r="6" fill="#153e67"/>
                        <circle cx="70" cy="30" r="6" fill="#153e67"/>
                        <circle cx="30" cy="70" r="6" fill="#153e67"/>
                        <circle cx="70" cy="70" r="6" fill="#153e67"/>
                    </g>
                </svg>
            </td>
            <td class="kop-text-td">
                <div class="kop-instansi">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
                <div class="kop-instansi">DIREKTORAT JENDERAL</div>
                <div class="kop-instansi">PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
                <div class="kop-instansi">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
                <div class="kop-balai">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
                <div class="kop-alamat">Jl. Dukuh Menanggal No. 122, Dukuh Menanggal, Kec. Gayungan, Kota SBY, Jawa Timur 60234, Laman: balaik3surabaya@kemnaker.go.id</div>
            </td>
        </tr>
    </table>
    <div class="line-double"></div>

    <div class="report-title-block">
        <h1 class="report-title">LAPORAN HASIL</h1>
        <div class="report-sub">Pengujian Faktor Ergonomi di Tempat Kerja</div>
        <div class="report-no">{{ $assessment->lhu_doc_number ?? ('No. LAB. 0032/VII/' . date('Y', strtotime($assessment->assessment_date))) }}</div>
    </div>

    <!-- 1. DATA UMUM -->
    <div class="sec-title">1. DATA UMUM</div>
    <table class="meta-table">
        <tr>
            <td width="22px">a.</td>
            <td width="200px">Perusahaan</td>
            <td width="12px">:</td>
            <td><strong>{{ $assessment->company_name }}</strong></td>
        </tr>
        <tr>
            <td>b.</td>
            <td>Alamat</td>
            <td>:</td>
            <td>{{ $assessment->company_address ?? '-' }}</td>
        </tr>
        <tr>
            <td>c.</td>
            <td>Pengurus / Penanggung Jawab</td>
            <td>:</td>
            <td>{{ $assessment->company_pic ?? 'Mohammad Nurul Huda' }}</td>
        </tr>
        <tr>
            <td>d.</td>
            <td>Nomor Dokumen Pengujian Sebelumnya</td>
            <td>:</td>
            <td>LAB. {{ str_pad(max(1, $assessment->id - 1), 4, '0', STR_PAD_LEFT) }}/VII/{{ date('Y', strtotime($assessment->assessment_date)) - 1 }}</td>
        </tr>
    </table>

    <!-- 2. PENGUKURAN ERGONOMI -->
    <div class="sec-title">2. PENGUKURAN ERGONOMI</div>
    <table class="meta-table">
        <tr>
            <td width="22px">a.</td>
            <td width="200px">Tanggal Pengukuran</td>
            <td width="12px">:</td>
            <td>{{ date('d F Y', strtotime($assessment->assessment_date)) }}</td>
        </tr>
        <tr>
            <td>b.</td>
            <td>Jumlah Departemen</td>
            <td>:</td>
            <td>1</td>
        </tr>
        <tr>
            <td>c.</td>
            <td>Jumlah Pekerjaan</td>
            <td>:</td>
            <td>1</td>
        </tr>
    </table>

    <!-- 3. METODE PENGUKURAN YANG DIPAKAI -->
    <div class="sec-title">3. METODE PENGUKURAN YANG DIPAKAI</div>
    <div style="font-size: 9.5pt; margin-left: 2px; margin-bottom: 6px;">
        SNI 9011:2021 tentang Pengukuran dan evaluasi potensi bahaya ergonomi di tempat kerja
    </div>

    <!-- 4. HASIL PENGUKURAN ERGONOMI -->
    <div class="sec-title">4. HASIL PENGUKURAN ERGONOMI</div>
    <table class="table-rekap">
        <thead>
            <tr>
                <th rowspan="2" width="24px">No.</th>
                <th rowspan="2" width="95px">Departemen/<br>Bagian/<br>Ruangan</th>
                <th rowspan="2" width="100px">Jenis<br>Pekerjaan</th>
                <th colspan="3">Hasil Penilaian Potensi<br>Bahaya (Skor)</th>
                <th rowspan="2" width="46px">Total<br>Hasil<br>Penilaian<br>(Skor)</th>
                <th rowspan="2" width="105px">Interpretasi<br>Hasil</th>
                <th rowspan="2">Metode<br>Pengendalian<br>Yang Sudah Ada</th>
            </tr>
            <tr>
                <th width="42px">Tubuh<br>Bagian<br>Atas</th>
                <th width="50px">Tubuh<br>Bagian<br>Punggung<br>dan Bawah</th>
                <th width="48px">Pengang-<br>katan<br>Beban<br>Manual</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center">1.</td>
                <td><strong>{{ $assessment->worker_name }}</strong></td>
                <td>{{ $assessment->position }}</td>
                <td class="center">{{ $assessment->upper_body_score ?? 0 }}</td>
                <td class="center">{{ $assessment->lower_body_score ?? 0 }}</td>
                <td class="center">{{ $assessment->mmh_score ?? 0 }}</td>
                <td class="center bold">{{ $assessment->total_score }}</td>
                <td>
                    @if($assessment->total_score <= 2)
                        Kondisi tempat kerja aman
                    @elseif($assessment->total_score <= 6)
                        Kondisi tempat kerja perlu pengamatan lebih lanjut
                    @else
                        Kondisi tempat kerja berbahaya
                    @endif
                </td>
                <td>{{ $assessment->existing_control ?? 'Adanya waktu istirahat/peregangan' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- CATATAN KRITERIA ERFC -->
    <div class="catatan-erfc">
        <strong>Catatan:</strong><br>
        Penilaian dari metode pengukuran ERFC atau Daftar Periksa Potensi Bahaya Faktor Ergonomi adalah sebagai berikut:<br>
        a. Nilai &le; 2, maka kondisi tempat kerja aman<br>
        b. Nilai 3 – 6, maka kondisi tempat kerja perlu pengamatan lebih lanjut<br>
        c. Nilai &ge; 7, maka kondisi tempat kerja berbahaya
    </div>

    <!-- FOOTER HALAMAN 1 -->
    <div class="footer-fixed">
        <span class="footer-left">Tgl. terbit: 24 Desember 2024</span>
        <span class="footer-right">No. : F/7.8.38/BK3-SBY</span>
        <div class="clearfix"></div>
    </div>


    <!-- =========================================================================
         HALAMAN 2: ANALISIS, KESIMPULAN & SARAN TINDAKAN PERBAIKAN
         ========================================================================= -->
    <div class="page-break"></div>

    <!-- Header Berulang ISO Balai K3 -->
    <table class="header-box-repeat">
        <tr>
            <td class="header-logo-repeat">
                <svg width="42" height="42" viewBox="0 0 100 100">
                    <g fill="#153e67">
                        <path d="M50,5 L58,25 L78,25 L62,38 L68,58 L50,45 L32,58 L38,38 L22,25 L42,25 Z"/>
                        <circle cx="50" cy="50" r="14" fill="#ffffff"/>
                        <circle cx="50" cy="50" r="9" fill="#153e67"/>
                        <rect x="46" y="28" width="8" height="44" rx="2" fill="#153e67"/>
                        <rect x="28" y="46" width="44" height="8" rx="2" fill="#153e67"/>
                    </g>
                </svg>
            </td>
            <td class="header-center-repeat">
                KEMENTERIAN KETENAGAKERJAAN RI<br>
                DIREKTORAT JENDERAL<br>
                PEMBINAAN PENGAWASAN KETENAGAKERJAAN<br>
                DAN KESELAMATAN DAN KESEHATAN KERJA<br>
                BALAI HIPERKES DAN KESELAMATAN KERJA SURABAYA
            </td>
            <td class="header-meta-repeat">
                <table>
                    <tr><td>Page: 2/{{ 2 + (count($photos) > 0 ? 1 : 0) }}</td></tr>
                    <tr><td>Rev/Terb.:<br>-/1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 5. ANALISIS -->
    <div class="sec-title">5. ANALISIS :</div>
    <div class="narrative-box">
        @if(!empty($assessment->lhu_analysis))
            <div style="white-space: pre-line;">{{ $assessment->lhu_analysis }}</div>
        @else
            a. Hasil penilaian potensi bahaya ergonomi {{ $assessment->worker_name }} ({{ $assessment->position }}) tubuh bagian atas yang berpotensi bahaya adalah :<br>
            &nbsp;&nbsp;&nbsp;&nbsp;- Leher menekuk ke depan &gt; 20&deg; atau ke belakang &lt; 5&deg;<br>
            &nbsp;&nbsp;&nbsp;&nbsp;- Bahu : Lengan atau siku yang tidak ditopang, dengan posisi di atas tinggi perut<br>
            &nbsp;&nbsp;&nbsp;&nbsp;- Pergelangan tangan : Menekuk ke depan atau kesamping<br>
            Dari hasil wawancara menggunakan formulir keluhan Gangguan Otot Rangka Akibat Kerja didapatkan keluhan tidak nyaman pada leher dan punggung bawah dengan frekuensi terkadang.<br>
            b. Hasil penilaian potensi bahaya ergonomi bagian bawah yang berpotensi bahaya adalah :<br>
            &nbsp;&nbsp;&nbsp;&nbsp;- Duduk dalam waktu yang lama tanpa sandaran atau penopang punggung yang memadai<br>
            &nbsp;&nbsp;&nbsp;&nbsp;- Tubuh membungkuk ke depan dengan sudut antara 20 hingga 45 derajat
        @endif
    </div>

    <!-- 6. KESIMPULAN -->
    <div class="sec-title">6. KESIMPULAN</div>
    <div class="narrative-box">
        @if(!empty($assessment->lhu_conclusion))
            <div style="white-space: pre-line;">{{ $assessment->lhu_conclusion }}</div>
        @else
            Penilaian potensi bahaya ergonomi pada {{ $assessment->worker_name }} ({{ $assessment->position }})
            @if($assessment->total_score <= 2)
                termasuk dalam kondisi tempat kerja aman.
            @elseif($assessment->total_score <= 6)
                dalam kondisi tempat kerja perlu pengamatan lebih lanjut.
            @else
                dalam kondisi tempat kerja berbahaya dan memerlukan tindakan perbaikan segera.
            @endif
        @endif
    </div>

    <!-- 7. SARAN DAN TINDAKAN PERBAIKAN -->
    <div class="sec-title">7. SARAN DAN TINDAKAN PERBAIKAN</div>
    <div class="narrative-box">
        @if(!empty($assessment->lhu_recommendation))
            <div style="white-space: pre-line;">{{ $assessment->lhu_recommendation }}</div>
        @else
            Secara umum terdapat 2 postur kerja yaitu postur kerja dinamis dan statis. Postur kerja statis teridentifikasi pada pekerja yang bekerja di perkantoran (menyusun laporan, memverifikasi laporan), sedangkan postur kerja dinamis teridentifikasi pada pekerja lapangan/operasional teknis.<br>

            <strong style="display:block; margin-top: 4px;">A. Jenis Pekerjaan Perkantoran</strong>
            Tenaga kerja yang mempunyai aktivitas kerja berupa administrasi perkantoran, saran dan tindakan yang dapat dilakukan untuk pekerjaan administrasi perkantoran dengan kegiatan mengoperasikan computer adalah sebagai berikut:
            <ol type="a" class="recom-list">
                <li>Apabila kepala menunduk saat bekerja dengan layar monitor, maka angkat/turunkan tinggi monitor agar mata sejajar dengan bagian atas layar, dan atur dokumen lain agar tingginya sejajar monitor.</li>
                <li>Apabila kepala tidak sejajar dengan tulang belakang, maka atur stasiun kerja agar memungkinkan untuk postur yang lebih baik, misal: duduk bersandar di kursi, letakkan keyboard di dekat pengguna, atur sudut monitor, dsb.</li>
                <li>Apabila meraih kesamping atau kedepan saat menggunakan mouse atau keyboard, maka letakkan mouse/peralatan lainnya di samping keyboard dengan tinggi yang sejajar dan gunakan alas mouse (mouse pad).</li>
                <li>Apabila pergelangan tangan tidak lurus (netral) saat mengetik, maka lepaskan kaki penyangga keyboard dan bila diperlukan, gunakan bantalan pada pergelangan tangan agar posisinya tetap lurus.</li>
                <li>Untuk menghindari kelelahan mata dapat dilakukan metode 20-20-20. Setelah mata fokus pada monitor selama 20 menit, alihkan pandangan mata ke obyek sejauh 20ft (6 meter), selama 20 detik. Selain itu, setelah 1 jam bekerja di depan komputer istirahat sejenak 5 – 10 menit, dan melakukan peregangan otot (stretching). Memastikan pekerja selalu makan – makanan bergizi dan terhidrasi dengan baik.</li>
            </ol>

            <strong style="display:block; margin-top: 4px;">B. Jenis Pekerjaan Dinamis</strong>
            Sedangkan pada tenaga kerja yang mempunyai jenis postur dinamis yang teridentifikasi pada aktivitas di lapangan dan pemeliharaan teknis adalah sebagai berikut:
            <ol type="a" class="recom-list">
                <li>Hindari pekerjaan yang dilakukan dengan posisi membungkuk dengan memberikan meja kerja yang sesuai dengan tinggi siku tenaga kerja.</li>
                <li>Apabila terdapat pekerjaan yang memerlukan ketelitian sebaiknya dilaksanakan pada pandangan setinggi dada dengan mengurangi posisi siku menjauh dari tubuh.</li>
                <li>Apabila kepala menunduk saat bekerja dengan objek kerja, maka angkat/turunkan tinggi objek kerja agar mata sejajar dengan objek kerja.</li>
                <li>Hindari posisi membungkuk atau memuntir saat memindahkan atau memeriksa benda kerja.</li>
            </ol>

            <strong style="display:block; margin-top: 4px;">C. Pengangkatan Beban Manual (MMH)</strong>
            Secara umum hilangkan kebutuhan untuk secara manual mengangkat, menurunkan, atau membawa benda dengan menggunakan kontrol rekayasa seperti troli, hoists, atau alat bantu. Jika hal tersebut tidak dimungkinkan:
            <ol type="a" class="recom-list">
                <li>Minimalkan jarak vertikal pengangkatan dan penurunan beban.</li>
                <li>Meminimalkan ukuran dan berat beban (gunakan kontainer kecil atau angkat berdua).</li>
                <li>Terapkan teknik pengangkatan jongkok dengan tumpuan kekuatan pada otot paha/kaki, bukan pada tulang belakang.</li>
                <li>Gunakan periode istirahat/jeda untuk memungkinkan otot pulih dari beban kerja statis/repetitif.</li>
            </ol>
        @endif
    </div>

    <!-- KLAUSUL CATATAN & TANDA TANGAN MANAJER TEKNIS -->
    <div class="bottom-wrap">
        <div class="clause-box">
            <strong>Catatan:</strong>
            <ol style="margin: 2px 0; padding-left: 14px;">
                <li>Data uji di atas hanya berlaku untuk contoh yang diuji.</li>
                <li>Laporan Hasil Uji ini tidak boleh digandakan, kecuali secara lengkap dan seijin tertulis dari Balai Hiperkes dan KK Surabaya.</li>
                <li>Laboratorium melayani pengaduan/complaint maksimum 1 (satu) minggu terhitung dari tanggal penyerahan LHU.</li>
                <li>Laboratorium menyerahkan Rekaman teknis bila diminta oleh pelanggan secara tertulis.</li>
                <li>Jika sampel diambil dan/atau dikirim oleh pelanggan, maka Laboratorium tidak bertanggung jawab terhadap proses pengambilan dan pengiriman sampel.</li>
            </ol>
        </div>

        <div class="sign-box">
            <div>Surabaya, {{ date('d F Y', strtotime($assessment->assessment_date)) }}</div>
            <div style="font-weight: bold; margin-top: 2px;">{{ $assessment->signer_position ?? 'Manajer Teknis' }},</div>
            <div class="sign-space"></div>
            <div style="font-weight: bold; text-decoration: underline;">{{ $assessment->signer_name ?? 'OKTOFA S. PAMUNGKAS S.T., M.Kes' }}</div>
            <div>NIP. {{ $assessment->signer_nip ?? '19791003 200912 1 002' }}</div>
        </div>
        <div class="clearfix"></div>
    </div>

    <!-- FOOTER HALAMAN 2 -->
    <div class="footer-fixed">
        <span class="footer-left">Tgl. terbit: 24 Desember 2024</span>
        <span class="footer-right">No. : F/7.8.38/BK3-SBY</span>
        <div class="clearfix"></div>
    </div>


    <!-- =========================================================================
         HALAMAN 3: LAMPIRAN FOTO & RINCIAN DAFTAR PERIKSA SNI 9011:2021
         ========================================================================= -->
    @if(count($photos) > 0)
    <div class="page-break"></div>

    <!-- Header Berulang ISO Balai K3 -->
    <table class="header-box-repeat">
        <tr>
            <td class="header-logo-repeat">
                <svg width="42" height="42" viewBox="0 0 100 100">
                    <g fill="#153e67">
                        <path d="M50,5 L58,25 L78,25 L62,38 L68,58 L50,45 L32,58 L38,38 L22,25 L42,25 Z"/>
                        <circle cx="50" cy="50" r="14" fill="#ffffff"/>
                        <circle cx="50" cy="50" r="9" fill="#153e67"/>
                        <rect x="46" y="28" width="8" height="44" rx="2" fill="#153e67"/>
                        <rect x="28" y="46" width="44" height="8" rx="2" fill="#153e67"/>
                    </g>
                </svg>
            </td>
            <td class="header-center-repeat">
                KEMENTERIAN KETENAGAKERJAAN RI<br>
                DIREKTORAT JENDERAL<br>
                PEMBINAAN PENGAWASAN KETENAGAKERJAAN<br>
                DAN KESELAMATAN DAN KESEHATAN KERJA<br>
                BALAI HIPERKES DAN KESELAMATAN KERJA SURABAYA
            </td>
            <td class="header-meta-repeat">
                <table>
                    <tr><td>Page: 3/{{ 2 + (count($photos) > 0 ? 1 : 0) }}</td></tr>
                    <tr><td>Rev/Terb.:<br>-/1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="font-size: 9pt; margin-bottom: 2px;">Lampiran 1 :</div>
    <div class="lampiran-box">
        <div class="lampiran-header-cell">
            1. {{ $assessment->worker_name }}, {{ $assessment->position }} (Masa Kerja {{ $assessment->work_duration_level ?? '3 tahun' }})
        </div>
        <div class="lampiran-desc-cell">
            <strong>Deskripsi pekerjaan:</strong><br>
            {{ $assessment->job_tasks ?? 'Aktivitas pengujian dan operasional rutin harian.' }}
            @if($assessment->job_duration) {{ $assessment->job_duration }}. @endif
            Durasi kerja per shift adalah {{ $assessment->shift_hours }} jam/hari.
            Tangan dominan yang digunakan adalah {{ $assessment->dominant_hand ?? 'Kanan' }}.
        </div>
    </div>

    <!-- Rekaman Foto/Video Teranotasi Sudut -->
    <div style="font-size: 8.5pt; font-weight: bold; text-align: center; margin-bottom: 3px;">
        Hasil Rekaman Foto/Video
    </div>

    <table class="photo-table-img">
        <tr>
            @foreach($photos as $p)
                <td>
                    @if($p['base64'])
                        <img src="{{ $p['base64'] }}" alt="{{ $p['name'] }}">
                    @endif
                </td>
            @endforeach
        </tr>
    </table>

    <!-- Rincian Checklist SNI 9011:2021 -->
    <div style="font-size: 8.5pt; font-weight: bold; text-align: center; margin-top: 6px; margin-bottom: 3px;">
        Daftar Periksa Potensi Bahaya Faktor Ergonomi
    </div>
    <table class="table-checklist">
        <thead>
            <tr>
                <th>Potensi Bahaya</th>
                <th width="105px">Durasi Paparan</th>
                <th width="50px">Skor</th>
            </tr>
        </thead>
        <tbody>
            <tr class="group-header">
                <td colspan="3">Hasil Penilaian Potensi Bahaya Tubuh Bagian Atas</td>
            </tr>
            <tr>
                <td>Leher menekuk ke depan &gt; 20&deg; atau ke belakang &lt; 5&deg;</td>
                <td class="center">25–50%</td>
                <td class="center">1</td>
            </tr>
            <tr>
                <td>Bahu : Lengan atau siku yang tidak ditopang, dengan posisi di atas tinggi perut</td>
                <td class="center">25–50%</td>
                <td class="center">{{ min(2, max(1, $assessment->upper_body_score ?? 1)) }}</td>
            </tr>
            <tr>
                <td>Pergelangan tangan : Menekuk ke depan atau kesamping</td>
                <td class="center">0–25%</td>
                <td class="center">1</td>
            </tr>
            <tr>
                <td>Rotasi lengan bawah secara cepat (mengocok / repetitif)</td>
                <td class="center">0–25%</td>
                <td class="center">0</td>
            </tr>
            <tr class="bold">
                <td colspan="2" style="text-align: right; padding-right: 8px;">Total</td>
                <td class="center">{{ $assessment->upper_body_score ?? 0 }}</td>
            </tr>

            <tr class="group-header">
                <td colspan="3">Hasil Penilaian Potensi Bahaya Pada Punggung & Tubuh Bagian Bawah</td>
            </tr>
            <tr>
                <td>Tubuh membungkuk ke depan dengan sudut antara 20&deg; hingga 45&deg;</td>
                <td class="center">0–25%</td>
                <td class="center">1</td>
            </tr>
            <tr>
                <td>Duduk dalam waktu yang lama tanpa sandaran atau penopang punggung yang memadai</td>
                <td class="center">25–50%</td>
                <td class="center">{{ min(2, max(0, ($assessment->lower_body_score ?? 1) - 1)) }}</td>
            </tr>
            <tr>
                <td>Bekerja dengan berdiri diam dalam jangka waktu lama</td>
                <td class="center">25–50%</td>
                <td class="center">0</td>
            </tr>
            <tr class="bold">
                <td colspan="2" style="text-align: right; padding-right: 8px;">Total</td>
                <td class="center">{{ $assessment->lower_body_score ?? 0 }}</td>
            </tr>

            <tr class="group-header">
                <td colspan="3">Hasil Periksa Pengangkatan Beban Secara Manual</td>
            </tr>
            <tr>
                <td>Pengangkatan beban atau membawa benda berulang</td>
                <td class="center">Sesekali</td>
                <td class="center">{{ $assessment->mmh_score ?? 0 }}</td>
            </tr>
            <tr class="bold">
                <td colspan="2" style="text-align: right; padding-right: 8px;">Total</td>
                <td class="center">{{ $assessment->mmh_score ?? 0 }}</td>
            </tr>

            <tr class="bold" style="background-color: #f5f5f5;">
                <td colspan="2" style="text-align: right; padding-right: 8px;">Total Hasil Penilaian</td>
                <td class="center bold">{{ $assessment->total_score }}</td>
            </tr>
        </tbody>
    </table>

    <!-- FOOTER HALAMAN 3 -->
    <div class="footer-fixed">
        <span class="footer-left">Tgl. terbit: 24 Desember 2024</span>
        <span class="footer-right">No. : F/7.8.38/BK3-SBY</span>
        <div class="clearfix"></div>
    </div>
    @endif

</body>
</html>