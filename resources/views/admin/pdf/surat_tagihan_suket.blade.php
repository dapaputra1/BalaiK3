<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Surat Tagihan - {{ $suket->nomor_order }}</title>
  <style>
    @page {
      margin: 1.5cm 1.8cm 1.5cm 1.8cm;
      size: a4 portrait;
    }
    body {
      font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
      font-size: 11pt;
      line-height: 1.35;
      color: #111827;
      margin: 0;
      padding: 0;
    }
    .kop-table {
      width: 100%;
      border-collapse: collapse;
      border-bottom: 2px solid #000;
      padding-bottom: 6px;
      margin-bottom: 16px;
    }
    .kop-table td {
      vertical-align: middle;
      padding: 0;
    }
    .kop-logo {
      width: 78px;
      text-align: center;
    }
    .kop-logo img {
      width: 68px;
      height: auto;
    }
    .kop-text {
      text-align: center;
      padding-left: 10px;
    }
    .kop-text .instansi-1 {
      font-size: 12pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .kop-text .instansi-2 {
      font-size: 10.5pt;
      font-weight: bold;
      text-transform: uppercase;
    }
    .kop-text .instansi-3 {
      font-size: 12pt;
      font-weight: bold;
      color: #15406a;
      text-transform: uppercase;
      margin-top: 2px;
    }
    .kop-text .alamat {
      font-size: 8pt;
      margin-top: 3px;
      color: #374151;
    }
    .doc-title {
      text-align: center;
      margin-top: 10px;
      margin-bottom: 18px;
    }
    .doc-title h2 {
      font-size: 13pt;
      font-weight: bold;
      text-transform: uppercase;
      text-decoration: underline;
      margin: 0;
      letter-spacing: 0.5px;
    }
    .doc-title .doc-num {
      font-size: 10pt;
      margin-top: 4px;
      color: #374151;
    }
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 14px;
      font-size: 10pt;
    }
    .meta-table td {
      padding: 2px 4px;
      vertical-align: top;
    }
    .meta-label {
      width: 150px;
      color: #4b5563;
    }
    .meta-colon {
      width: 10px;
    }
    .meta-val {
      font-weight: 600;
      color: #111827;
    }
    .content-p {
      font-size: 10pt;
      text-align: justify;
      margin-bottom: 12px;
      line-height: 1.45;
    }
    .table-items {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
      margin-bottom: 14px;
      font-size: 9.5pt;
    }
    .table-items th, .table-items td {
      border: 1px solid #9ca3af;
      padding: 6px 8px;
    }
    .table-items th {
      background-color: #f3f4f6;
      text-transform: uppercase;
      font-size: 8.5pt;
      text-align: center;
      color: #1f2937;
    }
    .table-items td.num {
      text-align: center;
      width: 30px;
    }
    .table-items td.price {
      text-align: right;
      white-space: nowrap;
    }
    .total-box {
      margin-top: 8px;
      margin-bottom: 16px;
      border: 1px solid #cbd5e1;
      background-color: #f8fafc;
      padding: 10px 12px;
      border-radius: 4px;
      font-size: 10pt;
    }
    .terbilang-box {
      font-style: italic;
      color: #1e3a8a;
      font-weight: bold;
      margin-top: 4px;
    }
    .notes-box {
      border-left: 3px solid #15406a;
      background-color: #f1f5f9;
      padding: 8px 10px;
      font-size: 8.5pt;
      color: #334155;
      margin-bottom: 20px;
    }
    .ttd-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
      font-size: 10pt;
    }
    .ttd-table td {
      vertical-align: top;
    }
    .ttd-kanan {
      width: 250px;
      text-align: center;
      margin-left: auto;
    }
  </style>
</head>
<body>

  {{-- KOP SURAT --}}
  <table class="kop-table">
    <tr>
      <td class="kop-logo">
        @if(!empty($logoBase64))
          <img src="data:image/png;base64,{{ $logoBase64 }}" alt="Logo">
        @elseif(file_exists(public_path('images/Logo Kemnaker.png')))
          <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/Logo Kemnaker.png'))) }}" alt="Logo">
        @endif
      </td>
      <td class="kop-text">
        <div class="instansi-1">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
        <div class="instansi-2">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN DAN K3</div>
        <div class="instansi-3">BALAI K3 SURABAYA</div>
        <div class="alamat">Jl. Dukuh Menanggal No. 122 Surabaya 60234 | Telp. (031) 8280440 | Email: balaik3surabaya@kemnaker.go.id</div>
      </td>
    </tr>
  </table>

  {{-- JUDUL SURAT --}}
  <div class="doc-title">
    <h2>SURAT TAGIHAN BIAYA PENERBITAN SUKET K3</h2>
    <div class="doc-num">Nomor: {{ $nomorTagihan }}</div>
  </div>

  {{-- DATA PEMOHON & PERMOHONAN --}}
  <table class="meta-table">
    <tr>
      <td class="meta-label">Nomor Order / Kode</td>
      <td class="meta-colon">:</td>
      <td class="meta-val">{{ $suket->nomor_order }}</td>
    </tr>
    <tr>
      <td class="meta-label">Tanggal Terbit Tagihan</td>
      <td class="meta-colon">:</td>
      <td class="meta-val">{{ $tanggalTagihan }}</td>
    </tr>
    <tr>
      <td class="meta-label">Ditujukan Kepada</td>
      <td class="meta-colon">:</td>
      <td class="meta-val">{{ $suket->perusahaan_nama ?: '-' }}</td>
    </tr>
    <tr>
      <td class="meta-label">Lokasi / Alamat Uji</td>
      <td class="meta-colon">:</td>
      <td class="meta-val">{{ $suket->lokasi ?: '-' }}</td>
    </tr>
    @if($suket->nomor_surat)
    <tr>
      <td class="meta-label">No. Surat Keterangan</td>
      <td class="meta-colon">:</td>
      <td class="meta-val">{{ $suket->nomor_surat }}</td>
    </tr>
    @endif
  </table>

  <p class="content-p">
    Sehubungan dengan permohonan penerbitan <strong>Surat Keterangan K3 Lingkungan Kerja</strong> berdasarkan hasil evaluasi teknis pengujian K3 (Permenaker No. 5 Tahun 2018), berikut kami sampaikan rincian tagihan Penerimaan Negara Bukan Pajak (PNBP) yang harus disetujui untuk penerbitan Kode Billing SIMPONI:
  </p>

  {{-- TABEL RINCIAN BIAYA --}}
  <table class="table-items">
    <thead>
      <tr>
        <th style="width: 25px;">No</th>
        <th>Uraian / Deskripsi Layanan K3</th>
        <th style="width: 140px;">Faktor K3 Diuji</th>
        <th style="width: 130px;">Jumlah (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @php
        $faktorList = is_array($suket->faktor_k3) ? array_map('ucfirst', $suket->faktor_k3) : ['Lingkungan Kerja'];
      @endphp
      <tr>
        <td class="num">1</td>
        <td>
          <strong>Penerbitan Surat Keterangan K3 Lingkungan Kerja</strong><br>
          <span style="font-size: 8pt; color: #4b5563;">
            Evaluasi berkas LHU, penyusunan naskah dinas, review tata naskah QC, dan pengesahan sah Kepala Balai K3 Surabaya.
          </span>
        </td>
        <td>{{ implode(', ', $faktorList) }}</td>
        <td class="price">Rp {{ number_format($nominalTagihan, 0, ',', '.') }}</td>
      </tr>
    </tbody>
    <tfoot>
      <tr>
        <th colspan="3" style="text-align: right; font-weight: bold;">TOTAL TAGIHAN PNBP</th>
        <th class="price" style="font-size: 10.5pt; font-weight: bold; color: #15406a;">
          Rp {{ number_format($nominalTagihan, 0, ',', '.') }}
        </th>
      </tr>
    </tfoot>
  </table>

  {{-- TERBILANG BOX --}}
  <div class="total-box">
    <div><strong>Terbilang:</strong></div>
    <div class="terbilang-box"># {{ $terbilang }} #</div>
  </div>

  {{-- PETUNJUK PEMBAYARAN --}}
  <div class="notes-box">
    <strong>Petunjuk Alur Pembayaran:</strong><br>
    1. Mohon lakukan persetujuan (ACC) Surat Tagihan ini melalui portal web Balai K3.<br>
    2. Setelah disetujui, Bendahara Balai K3 akan menerbitkan <strong>Kode Billing SIMPONI / PNBP Kementerian Keuangan</strong> beserta Berkas Panduan Pembayaran.<br>
    3. Pembayaran dapat disetorkan melalui Bank Persepsi, ATM, Internet Banking, atau m-Banking sesuai kode billing yang diterbitkan.
  </div>

  {{-- TANDA TANGAN BENDAHARA / ADMIN --}}
  <table class="ttd-table">
    <tr>
      <td style="width: 55%;"></td>
      <td class="ttd-kanan">
        Surabaya, {{ $tanggalTagihan }}<br>
        <strong>Bendahara Penerimaan / Petugas Keuangan<br>Balai K3 Surabaya</strong>
        <br><br><br><br>
        <strong><u>{{ $bendaharaNama }}</u></strong><br>
        <span>NIP. {{ $bendaharaNip }}</span>
      </td>
    </tr>
  </table>

</body>
</html>
