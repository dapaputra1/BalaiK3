@php
  $nomorSurat = $suket->nomor_surat ?: ('.../SK-LK/BK3-SBY/' . \Carbon\Carbon::now()->format('m/Y'));
  $tanggalTerbit = $suket->tanggal_surat 
      ? \Carbon\Carbon::parse($suket->tanggal_surat)->locale('id')->translatedFormat('d F Y') 
      : \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
  
  $faktorList = is_array($suket->faktor_k3) ? $suket->faktor_k3 : [];
  $faktorLabels = [
      'fisika' => 'Faktor Fisika (Kebisingan, Iklim Kerja/ISDB, Penerangan, Getaran)',
      'kimia' => 'Faktor Kimia (Debu Total/Respirabel, Uap, Gas Kimia Berbahaya)',
      'biologi' => 'Faktor Biologi (Bakteri, Jamur, Angka Kuman Udara)',
      'ergonomi' => 'Faktor Ergonomi (Postur Kerja, Gerakan Berulang, Desain Kerja)',
      'psikologi' => 'Faktor Psikologi (Beban Kerja Mental, Potensi Stres Kerja)',
  ];

  $logoSrc = $logoSrc ?? 'cid:kemnaker-logo';
@endphp
<!DOCTYPE html>
<html lang="id" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
  <meta charset="UTF-8">
  <title>Surat Keterangan K3 Lingkungan Kerja - {{ $suket->perusahaan_nama }}</title>
  <!--[if gte mso 9]>
  <xml>
    <w:WordDocument>
      <w:View>Print</w:View>
      <w:Zoom>100</w:Zoom>
      <w:DoNotOptimizeForBrowser/>
    </w:WordDocument>
  </xml>
  <![endif]-->
  <style>
    @page WordSection1 {
      size: 595.3pt 841.9pt; /* A4 */
      margin: 40pt 45pt 45pt 45pt;
    }
    div.WordSection1 {
      page: WordSection1;
    }
    body {
      margin: 0;
      color: #000;
      font-family: "Times New Roman", Times, serif;
      font-size: 11pt;
      line-height: 1.35;
    }
    table {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
      width: 100%;
    }
    td {
      vertical-align: top;
      font-size: 11pt;
    }
    p {
      margin: 0 0 6pt 0;
    }
    .header-table td {
      text-align: center;
      padding: 0;
    }
    .instansi-1 {
      font-size: 12pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5pt;
    }
    .instansi-2 {
      font-size: 11pt;
      font-weight: bold;
      text-transform: uppercase;
    }
    .instansi-3 {
      font-size: 13pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5pt;
    }
    .instansi-alamat {
      font-size: 8.5pt;
      font-style: italic;
      color: #222;
      line-height: 1.2;
    }
    .double-line {
      border-top: 2.5pt solid #000;
      border-bottom: 0.75pt solid #000;
      height: 3pt;
      margin: 4pt 0 14pt 0;
    }
    .title-surat {
      text-align: center;
      margin-bottom: 14pt;
    }
    .title-surat h2 {
      font-size: 13pt;
      font-weight: bold;
      text-decoration: underline;
      margin: 0 0 3pt 0;
      letter-spacing: 0.5pt;
    }
    .title-surat .nomor {
      font-size: 10.5pt;
      margin-bottom: 3pt;
    }
    .title-surat .tentang {
      font-size: 11pt;
      font-weight: bold;
      text-transform: uppercase;
    }
    .table-data {
      margin: 10pt 0 14pt 0;
    }
    .table-data td {
      padding: 3pt 0;
    }
    .table-faktor {
      border: 1pt solid #000;
      margin: 8pt 0 14pt 0;
    }
    .table-faktor th, .table-faktor td {
      border: 1pt solid #000;
      padding: 5pt 7pt;
      font-size: 10pt;
    }
    .table-faktor th {
      background-color: #f2f2f2;
      text-align: center;
      font-weight: bold;
    }
    .ttd-box {
      margin-top: 20pt;
      float: right;
      width: 250pt;
      text-align: center;
    }
  </style>
</head>
<body>
<div class="WordSection1">

  {{-- KOP SURAT RESMI KEMNAKER / BALAI K3 SURABAYA --}}
  <table class="header-table">
    <tr>
      <td style="width: 75pt; text-align: left; vertical-align: middle;">
        @if(!empty($logoAssetBase64))
          <img src="data:image/png;base64,{{ $logoAssetBase64 }}" width="70" height="70" alt="Logo Kemnaker" style="display:block;">
        @else
          <img src="{{ $logoSrc }}" width="70" height="70" alt="Logo Kemnaker" style="display:block;">
        @endif
      </td>
      <td style="vertical-align: middle; text-align: center;">
        <div class="instansi-1">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
        <div class="instansi-2">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN DAN K3</div>
        <div class="instansi-3">BALAI KESELAMATAN DAN KESEHATAN KERJA SURABAYA</div>
        <div class="instansi-alamat">
          Jl. Dukuh Menanggal XII/No. 2, Gayungan, Surabaya, Jawa Timur 60234<br>
          Telepon: (031) 8280220 | Email: balaik3_sby@kemnaker.go.id | Website: balaik3surabaya.kemnaker.go.id
        </div>
      </td>
    </tr>
  </table>

  <div class="double-line"></div>

  {{-- JUDUL SURAT --}}
  <div class="title-surat">
    <h2>SURAT KETERANGAN</h2>
    <div class="nomor">Nomor: {{ $nomorSurat }}</div>
    <div class="tentang">TENTANG<br>HASIL PENGUJIAN KESELAMATAN DAN KESEHATAN KERJA LINGKUNGAN KERJA</div>
  </div>

  {{-- PEMBUKA --}}
  <p style="text-align: justify; text-indent: 28pt;">
    Berdasarkan hasil pemeriksaan dan pengujian Keselamatan dan Kesehatan Kerja (K3) Lingkungan Kerja yang telah dilaksanakan di tempat kerja sesuai dengan ketentuan <strong>Peraturan Menteri Ketenagakerjaan Republik Indonesia Nomor 5 Tahun 2018 tentang Keselamatan dan Kesehatan Kerja Lingkungan Kerja</strong>, Kepala Balai Keselamatan dan Kesehatan Kerja Surabaya dengan ini menerangkan bahwa:
  </p>

  {{-- DATA PERUSAHAAN --}}
  <table class="table-data" style="margin-left: 14pt;">
    <tr>
      <td style="width: 170pt;">1. Nama Perusahaan / Pemohon</td>
      <td style="width: 12pt;">:</td>
      <td><strong>{{ $suket->perusahaan_nama }}</strong></td>
    </tr>
    <tr>
      <td>2. Alamat / Lokasi Pengujian</td>
      <td>:</td>
      <td>{{ $suket->lokasi }}</td>
    </tr>
    <tr>
      <td>3. Nomor Order / Kode Permohonan</td>
      <td>:</td>
      <td>{{ $suket->nomor_order }}</td>
    </tr>
    <tr>
      <td>4. Dasar Dokumen Pengujian</td>
      <td>:</td>
      <td>
        @if($suket->lhu_source === 'auto')
          Laporan Hasil Uji (LHU) Resmi Terverifikasi Balai K3 Surabaya
        @else
          Laporan Hasil Uji (LHU) Pengujian K3 Mandiri / Laboratorium Terakreditasi
        @endif
      </td>
    </tr>
  </table>

  {{-- RUANG LINGKUP FAKTOR PENGUJIAN --}}
  <p style="text-align: justify;">
    Telah dilakukan pengujian dan evaluasi terhadap faktor-faktor lingkungan kerja dengan ruang lingkup sebagai berikut:
  </p>

  <table class="table-faktor">
    <thead>
      <tr>
        <th style="width: 25pt;">No</th>
        <th style="width: 200pt;">Faktor Lingkungan Kerja</th>
        <th style="width: 120pt;">Standar Regulasi</th>
        <th>Hasil Evaluasi Teknis K3</th>
      </tr>
    </thead>
    <tbody>
      @php $no = 1; @endphp
      @forelse($faktorList as $fKey)
        <tr>
          <td style="text-align: center;">{{ $no++ }}</td>
          <td><strong>{{ $faktorLabels[$fKey] ?? ucfirst($fKey) }}</strong></td>
          <td style="text-align: center;">Permenaker No. 5/2018</td>
          <td>
            @if(!empty($suket->catatan_evaluasi))
              {{ $suket->catatan_evaluasi }}
            @else
              Telah diuji & dievaluasi sesuai Nilai Ambang Batas (NAB).
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td style="text-align: center;">1</td>
          <td>Faktor Fisika & Kimia Lingkungan Kerja</td>
          <td style="text-align: center;">Permenaker No. 5/2018</td>
          <td>Memenuhi ketentuan Nilai Ambang Batas (NAB).</td>
        </tr>
      @endforelse
    </tbody>
  </table>

  {{-- KESIMPULAN --}}
  <p style="text-align: justify; text-indent: 28pt;">
    Berdasarkan telaah dokumen teknis, foto pengujian lapangan, serta denah penempatan titik ukur, kondisi lingkungan kerja pada area yang diuji dinyatakan:
  </p>

  <div style="margin: 10pt 0; text-align: center; border: 1pt solid #000; padding: 8pt; background-color: #f9f9f9;">
    <strong style="font-size: 12pt; letter-spacing: 0.5pt;">MEMENUHI PERSYARATAN KESELAMATAN DAN KESEHATAN KERJA LINGKUNGAN KERJA</strong><br>
    <span style="font-size: 9.5pt; font-style: italic;">Sesuai dengan Nilai Ambang Batas (NAB) dan Standar Higiene Industri Permenaker No. 5 Tahun 2018</span>
  </div>

  {{-- CATATAN & MASA BERLAKU --}}
  <p style="text-align: justify;">
    <strong>Ketentuan dan Masa Berlaku:</strong>
  </p>
  <ol style="margin-top: 0; padding-left: 20pt; text-align: justify;">
    <li>Perusahaan wajib mempertahankan dan memelihara kondisi lingkungan kerja yang telah memenuhi syarat K3 serta melakukan pengendalian teknis secara berkesinambungan.</li>
    <li>Surat Keterangan ini berlaku selama <strong>1 (satu) tahun</strong> terhitung sejak tanggal diterbitkan, sepanjang tidak terdapat perubahan tata letak mesin, proses produksi, bahan kimia yang digunakan, maupun modifikasi lingkungan kerja yang signifikan.</li>
    <li>Surat Keterangan ini dapat dicabut kembali apabila di kemudian hari ditemukan ketidaksesuaian penerapan norma K3 di tempat kerja.</li>
  </ol>

  <p style="text-align: justify; margin-top: 10pt;">
    Demikian Surat Keterangan ini dibuat dan diterbitkan untuk dapat dipergunakan sebagaimana mestinya.
  </p>

  {{-- TANDA TANGAN KEPALA BALAI --}}
  <table style="margin-top: 25pt;">
    <tr>
      <td style="width: 55%;"></td>
      <td style="width: 45%; text-align: center;">
        <div>Ditetapkan di: Surabaya</div>
        <div>Pada tanggal: {{ $tanggalTerbit }}</div>
        <div style="font-weight: bold; margin-top: 6pt; text-transform: uppercase;">
          KEPALA BALAI KESELAMATAN DAN<br>KESEHATAN KERJA SURABAYA
        </div>
        
        <div style="height: 55pt; margin: 8pt 0; line-height: 55pt;">
          @if(!empty($suket->signed_at))
            <div style="display: inline-block; border: 1pt dashed #28a745; padding: 4pt 12pt; color: #1e7e34; font-size: 9pt; line-height: normal;">
              <span style="font-weight: bold;">&#10003; TERTANDATANGANI SECARA ELEKTRONIK (TTE)</span><br>
              <span>Balai K3 Surabaya - Kemnaker RI</span><br>
              <span style="font-size: 8pt;">Pada: {{ \Carbon\Carbon::parse($suket->signed_at)->format('d/m/Y H:i') }} WIB</span>
            </div>
          @else
            <span style="font-style: italic; color: #888; font-size: 9pt;">[Draft Dokumen - Menunggu Pengesahan]</span>
          @endif
        </div>

        <div style="font-weight: bold; text-decoration: underline;">
          {{ $kepalaBalaiNama ?? 'Dr. H. Agus Triyono, S.T., M.Kes.' }}
        </div>
        <div>NIP. {{ $kepalaBalaiNip ?? '19750812 200212 1 001' }}</div>
      </td>
    </tr>
  </table>

</div>
</body>
</html>
