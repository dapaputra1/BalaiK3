@php
  $company = $permohonan->company;
  $pcu = $permohonan->assignments
      ->where('role', 'pcu')
      ->map(fn ($a) => $a->user)
      ->filter();
  $tanggalTtdLabel = \Carbon\Carbon::parse($spt->tanggal_terbit ?? now())->locale('id')->translatedFormat('d F Y');
  $tanggalFooterLabel = '24 Desember 2024';
  $pcuRows = $pcu->values();
  $dasarViewItems = collect($dasarViewItems ?? []);
  $untukViewItems = collect($untukViewItems ?? []);
  $normalizeRichText = function ($html) {
      $normalized = (string) $html;
      $normalized = str_replace(['&nbsp;', '&#160;'], ' ', $normalized);
      $normalized = str_replace("\u{00A0}", ' ', $normalized);
      return $normalized;
  };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    @page { margin: 12mm 16mm 18mm 16mm; }
    body {
      font-family: "Times New Roman", serif;
      color: #000;
      margin: 0;
      font-size: 14px;
      line-height: 1.35;
    }
    .page {
      width: 100%;
    }
    .letter-header {
      display: table;
      width: 100%;
      margin: 0;
    }
    .logo-wrap,
    .header-wrap {
      display: table-cell;
      vertical-align: middle;
    }
    .logo-wrap {
      width: 120px;
      padding-left: 0;
      text-align: center;
    }
    .logo {
      width: 100px;
      max-width: 100%;
      height: auto;
      display: block;
      margin: 0 auto;
    }
    .header-wrap {
      text-align: left;
      border-left: 2px solid #163e67;
      padding-left: 12px;
      padding-right: 6px;
      line-height: 1.08;
      font-family: "Arial Narrow", Arial, sans-serif;
    }
    .header-wrap > .line,
    .header-wrap > .addr {
      display: block;
      width: 100%;
      text-align: left !important;
      margin-left: 0;
      margin-right: 0;
    }
    .line {
      font-size: 14px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: -0.02em;
    }
    .header-wrap .line:first-of-type {
      font-size: 12px;
      font-weight: 500;
    }
    .header-wrap .line:nth-of-type(2) {
      font-size: 13px;
      font-weight: 700;
    }
    .header-wrap .line:nth-of-type(4) {
      font-size: 16px;
      color: #163e67;
      line-height: 1.05;
      margin-top: 3px;
    }
    .addr {
      font-size: 11px;
      margin-top: 3px;
      font-family: Arial, sans-serif;
      text-align: left;
    }
    .addr .icon {
      color: #163e67;
      font-weight: 700;
      margin: 0 2px;
    }
    .header-line {
      border-top: 2px solid #000;
      width: 100%;
      margin: 10px 0 18px;
    }
    .spt-title {
      text-align: center;
      margin: 24px 0 16px;
    }
    .spt-title h2 {
      margin: 0;
      font-size: 18px;
      text-decoration: underline;
    }
    .spt-title .nomor {
      font-size: 14px;
      margin-top: 4px;
    }
    .section {
      margin-top: 12px;
    }
    .line-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    .line-table td {
      vertical-align: top;
      font-size: 14px;
      padding: 0;
    }
    .section-label {
      width: 88px;
      font-weight: 700;
    }
    .line-content {
      padding-left: 4px;
    }
    .line-separator {
      width: 14px;
      font-weight: 700;
      text-align: center;
    }
    .list {
      margin: 0;
      font-size: 14px;
      line-height: 1.45;
    }
    ol.list,
    ul.list {
      padding-left: 22px;
    }
    .list p {
      margin: 0 0 6px;
    }
    .list li {
      margin-bottom: 6px;
    }
    .ordered-names {
      margin: 0;
      padding-left: 22px;
    }
    .ordered-names li {
      margin-bottom: 6px;
    }
    .statement-table {
      width: 100%;
      border-collapse: collapse;
      border-spacing: 0;
    }
    .statement-table td {
      padding: 0;
      vertical-align: top;
    }
    .statement-table tr + tr td {
      padding-top: 6px;
    }
    .statement-no {
      width: 18px;
      white-space: nowrap;
      padding-right: 2px !important;
    }
    .statement-text {
      margin: 0;
      text-align: justify;
      text-justify: inter-word;
    }
    .statement-text p {
      margin: 0 0 6px;
    }
    .statement-text p:last-child {
      margin-bottom: 0;
    }
    .command-title {
      text-align: center;
      font-weight: 700;
      margin-top: 18px;
      margin-bottom: 8px;
    }
    .footer-wrap {
      margin-top: 20px;
      page-break-inside: avoid;
    }
    .note-box {
      border: 2px solid #000;
      padding: 10px;
      font-size: 12px;
      font-weight: 700;
      width: 58%;
    }
    .signature-block {
      width: 34%;
      margin-left: auto;
      margin-top: 10px;
      margin-right: 0;
      text-align: center;
    }
    .signature-block .signature-meta {
      text-align: left;
      padding-left: 26px;
    }
    .signature-space {
      height: 80px;
    }
    .page-footer {
      position: fixed;
      left: 0;
      right: 0;
      bottom: -10mm;
      border-top: 1px solid #000;
      padding-top: 6px;
    }
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
    }
    .meta-table td:last-child {
      text-align: right;
    }
    .bsre-note {
      font-size: 12px;
      text-align: center;
      margin-top: 4px;
    }
    .page-break {
      page-break-before: always;
    }
    .lampiran-title {
      text-align: center;
      margin: 4px 0 14px;
    }
    .lampiran-title h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 400;
    }
    .lampiran-subtitle {
      margin-top: 4px;
      font-size: 14px;
    }
    .lampiran-header {
      width: 100%;
      margin-bottom: 12px;
      font-size: 14px;
    }
    .lampiran-header td {
      padding: 1px 0;
      vertical-align: top;
    }
    .lampiran-colon {
      width: 12px;
      text-align: center;
      white-space: nowrap;
    }
    .lampiran-label {
      width: 78px;
      white-space: nowrap;
    }
    .lampiran-value {
      white-space: nowrap;
    }
    .lampiran-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
      margin-top: 8px;
    }
    .lampiran-table th,
    .lampiran-table td {
      border: 1px solid #000;
      padding: 6px 7px;
      vertical-align: top;
    }
    .lampiran-table th {
      text-align: center;
      font-weight: 700;
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="letter-header">
      <div class="logo-wrap">
        @if(!empty($logoDataUri))
          <img src="{{ $logoDataUri }}" alt="Logo Kementerian Ketenagakerjaan" class="logo">
        @endif
      </div>
      <div class="header-wrap">
        <div class="line">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
        <div class="line">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
        <div class="line">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
        <div class="line">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
        <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, (031) 8280440, balaik3surabaya@kemnaker.go.id</div>
      </div>
    </div>
    <div class="header-line"></div>

    <div class="spt-title">
      <h2>SURAT PERINTAH TUGAS</h2>
      <div class="nomor">No. {{ $spt->nomor_surat }}</div>
    </div>

    <div class="section">
      <table class="line-table">
        <tr>
          <td class="section-label">Menimbang</td>
          <td class="line-separator">:</td>
          <td class="line-content">
            <table class="statement-table">
              <tr>
                <td class="statement-no">a.</td>
                <td class="statement-text">Bahwa dalam rangka adanya permintaan dari perusahaan terkait pengujian K3, maka perlu ditugaskan petugas pengambil contoh uji untuk melaksanakan kegiatan yang dimaksud;</td>
              </tr>
              <tr>
                <td class="statement-no">b.</td>
                <td class="statement-text">Bahwa untuk itu perlu diterbitkan surat perintah tugas Kuasa Pengguna Anggaran.</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>

    <div class="section">
      <table class="line-table">
        <tr>
          <td class="section-label">Dasar</td>
          <td class="line-separator">:</td>
          <td class="line-content">
            @if($dasarViewItems->count() === 1)
              <div class="statement-text">{!! $normalizeRichText($dasarViewItems->first()) !!}</div>
            @elseif($dasarViewItems->count() > 1)
              <table class="statement-table">
                @foreach($dasarViewItems as $item)
                  <tr>
                    <td class="statement-no">{{ $loop->iteration }}.</td>
                    <td class="statement-text">{!! $normalizeRichText($item) !!}</td>
                  </tr>
                @endforeach
              </table>
            @else
              <div class="list">-</div>
            @endif
          </td>
        </tr>
      </table>
    </div>

    <div class="command-title">Memerintahkan/Menugaskan:</div>

    <div class="section">
      <table class="line-table">
        <tr>
          <td class="section-label">Kepada</td>
          <td class="line-separator">:</td>
          <td class="line-content">
            nama - nama terlampir
          </td>
        </tr>
      </table>

      <table class="line-table">
        <tr>
          <td class="section-label">Untuk</td>
          <td class="line-separator">:</td>
          <td class="line-content">
            @if($untukViewItems->count() === 1)
              <div class="statement-text">{!! $normalizeRichText($untukViewItems->first()) !!}</div>
            @elseif($untukViewItems->count() > 1)
              <table class="statement-table">
                @foreach($untukViewItems as $item)
                  <tr>
                    <td class="statement-no">{{ $loop->iteration }}.</td>
                    <td class="statement-text">{!! $normalizeRichText($item) !!}</td>
                  </tr>
                @endforeach
              </table>
            @else
              <div class="list">-</div>
            @endif
          </td>
        </tr>
      </table>
    </div>

    <div class="footer-wrap">
      <div class="note-box">
        ASN Balai Hiperkes dan KK Surabaya Kemnaker tidak menerima gratifikasi dalam pelaksanaan tugas sesuai ketentuan yang berlaku.
      </div>
      <div class="signature-block">
        <div class="signature-meta">Ditetapkan&nbsp;&nbsp;&nbsp;&nbsp;: {{ $spt->tempat_terbit }}</div>
        <div class="signature-meta">Pada tanggal&nbsp;&nbsp;: {{ $tanggalTtdLabel }}</div>
        <div style="margin-top:24px;">Kuasa Pengguna Anggaran</div>
        <div>Balai Hiperkes dan KK Surabaya</div>
        <div class="signature-space"></div>
        <div style="font-weight:700;">{{ $kepalaBalaiNama ?? '-' }}</div>
      </div>
    </div>

    <div class="page-footer">
      <table class="meta-table">
        <tr>
          <td>Tgl. terbit: {{ $tanggalFooterLabel }}</td>
          <td>No. : F/7.3.1/BK3-SBY</td>
        </tr>
      </table>
      <div class="bsre-note">Dokumen ini telah ditandatangani secara elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), BSSN</div>
    </div>
  </div>

  <div class="page page-break">
    <div style="width: 58%; margin-left: auto;">
      <table class="lampiran-header">
        <tr>
          <td class="lampiran-label">Lampiran</td>
          <td class="lampiran-colon">:</td>
          <td class="lampiran-value">Surat Perintah KPA Balai Hiperkes dan KK Surabaya</td>
        </tr>
        <tr>
          <td class="lampiran-label">Nomor</td>
          <td class="lampiran-colon">:</td>
          <td class="lampiran-value">{{ $spt->nomor_surat }}</td>
        </tr>
        <tr>
          <td class="lampiran-label">Tanggal</td>
          <td class="lampiran-colon">:</td>
          <td class="lampiran-value">{{ $tanggalTtdLabel }}</td>
        </tr>
      </table>
    </div>

    <div class="lampiran-title">
      <h3>DAFTAR PEJABAT/PEGAWAI YANG DIBERI PERINTAH TUGAS</h3>
    </div>

    <table class="lampiran-table">
      <thead>
        <tr>
          <th style="width:40px;">No.</th>
          <th>Nama</th>
          <th style="width:170px;">NIP</th>
          <th style="width:120px;">Pangkat/Gol. Ruang</th>
          <th>Jabatan</th>
          <th style="width:120px;">Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pcuRows as $person)
          <tr>
            <td style="text-align:center;">{{ $loop->iteration }}.</td>
            <td>{{ $person->name }}</td>
            <td>{{ $person->nip ?: '-' }}</td>
            <td style="text-align:center;">{{ $person->golongan ?: '-' }}</td>
            <td>{{ $person->jabatan ?: '-' }}</td>
            <td></td>
          </tr>
        @empty
          <tr>
            <td style="text-align:center;">1.</td>
            <td>-</td>
            <td>-</td>
            <td style="text-align:center;">-</td>
            <td>-</td>
            <td></td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="page-footer">
      <table class="meta-table">
        <tr>
          <td>Tgl. terbit: {{ $tanggalFooterLabel }}</td>
          <td>No. : F/7.3.1/BK3-SBY</td>
        </tr>
      </table>
      <div class="bsre-note">Dokumen ini telah ditandatangani secara elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), BSSN</div>
    </div>
  </div>
</body>
</html>
