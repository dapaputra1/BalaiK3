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
  $logoSrc = $logoSrc ?? 'cid:kemnaker-logo';
  $normalizeRichText = function ($html) {
      $normalized = (string) $html;
      $normalized = str_replace(['&nbsp;', '&#160;'], ' ', $normalized);
      $normalized = str_replace("\u{00A0}", ' ', $normalized);
      return $normalized;
  };
@endphp
<!DOCTYPE html>
<html lang="id" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
  <meta charset="UTF-8">
  <title></title>
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
      size: 595.3pt 841.9pt;
      margin: 34pt 45pt 51pt 45pt;
    }
    div.WordSection1 {
      page: WordSection1;
    }
    body {
      margin: 0;
      color: #000;
      font-family: "Times New Roman", serif;
      font-size: 10.5pt;
      line-height: 1.35;
    }
    table {
      border-collapse: collapse;
      mso-table-lspace: 0pt;
      mso-table-rspace: 0pt;
    }
    td {
      vertical-align: top;
    }
    p {
      margin: 0;
    }
    .page-table {
      width: 100%;
      height: 756pt;
      table-layout: fixed;
    }
    .page-content-row {
      height: 704pt;
      mso-height-rule: exactly;
    }
    .page-content {
      vertical-align: top;
      padding: 0;
      height: 704pt;
    }
    .page-footer-row {
      height: 52pt;
      mso-height-rule: exactly;
    }
    .page-footer-cell {
      vertical-align: bottom;
      padding: 0;
      height: 52pt;
    }
    .header-table {
      width: 100%;
      margin: 0;
    }
    .logo-cell {
      width: 90pt;
      text-align: center;
      vertical-align: middle;
      padding: 0 9pt 0 0;
    }
    .logo {
      width: 75pt;
      height: 75pt;
    }
    .header-cell {
      border-left: 1.5pt solid #163e67;
      padding-left: 9pt;
      padding-right: 5pt;
      font-family: "Arial Narrow", Arial, sans-serif;
      line-height: 1.05;
      vertical-align: middle;
    }
    .head-line {
      font-weight: 700;
      text-transform: uppercase;
      margin: 0;
    }
    .head-line-1 {
      font-size: 9pt;
      font-weight: 500;
    }
    .head-line-2 {
      font-size: 9.75pt;
      font-weight: 700;
    }
    .head-line-3 {
      font-size: 10.5pt;
      font-weight: 700;
    }
    .head-line-4 {
      font-size: 12pt;
      color: #163e67;
      line-height: 1.05;
      margin-top: 2pt;
    }
    .address {
      font-size: 8.25pt;
      margin-top: 2pt;
      font-family: Arial, sans-serif;
    }
    .header-line {
      border-top: 1.5pt solid #000;
      height: 1pt;
      line-height: 1pt;
      margin: 7.5pt 0 13.5pt 0;
    }
    .title {
      text-align: center;
      margin: 18pt 0 12pt 0;
    }
    .title h2 {
      font-size: 14pt;
      margin: 0;
      text-decoration: underline;
    }
    .title .nomor {
      font-size: 10.5pt;
      margin-top: 3pt;
    }
    .line-table {
      width: 100%;
      margin-bottom: 6pt;
    }
    .line-table td {
      font-size: 10.5pt;
      padding: 0;
    }
    .section-label {
      width: 66pt;
      font-weight: 700;
    }
    .line-separator {
      width: 10.5pt;
      text-align: center;
      font-weight: 700;
    }
    .line-content {
      padding-left: 3pt;
    }
    .statement-table {
      width: 100%;
    }
    .statement-table td {
      padding: 0;
      font-size: 10.5pt;
    }
    .statement-table tr + tr td {
      padding-top: 4.5pt;
    }
    .statement-no {
      width: 13.5pt;
      white-space: nowrap;
      padding-right: 1.5pt;
    }
    .statement-text {
      text-align: justify;
    }
    .statement-text p {
      margin: 0 0 4pt 0;
    }
    .command-title {
      text-align: center;
      font-weight: 700;
      margin: 13.5pt 0 6pt 0;
    }
    .note-box-table {
      width: 271pt;
      margin-top: 15pt;
    }
    .note-box-table td {
      border: 1.5pt solid #000;
      padding: 4pt 7.5pt 7.5pt 7.5pt;
      font-size: 9pt;
      font-weight: 700;
      line-height: 1.2;
    }
    .signature-table {
      width: 100%;
      margin-top: 7.5pt;
    }
    .signature-cell {
      width: 159pt;
      text-align: center;
      font-size: 10.5pt;
    }
    .signature-meta {
      text-align: left;
      padding-left: 19.5pt;
    }
    .signature-space {
      height: 60pt;
      line-height: 60pt;
    }
    .page-footer {
      border-top: 0.75pt solid #000;
      padding-top: 4.5pt;
      font-size: 9pt;
    }
    .footer-table {
      width: 100%;
      font-size: 9pt;
    }
    .footer-right {
      text-align: right;
    }
    .bsre-note {
      text-align: center;
      font-size: 9pt;
      margin-top: 3pt;
      white-space: nowrap;
    }
    .page-break {
      page-break-before: always;
      height: 1pt;
      line-height: 1pt;
      font-size: 1pt;
    }
    .lampiran-header-wrap {
      width: 305pt;
      margin-left: auto;
      margin-right: 0;
      margin-bottom: 9pt;
    }
    .lampiran-header {
      width: 100%;
      font-size: 10.5pt;
    }
    .lampiran-label {
      width: 58.5pt;
      white-space: nowrap;
    }
    .lampiran-colon {
      width: 9pt;
      text-align: center;
      white-space: nowrap;
    }
    .lampiran-value {
      white-space: nowrap;
    }
    .lampiran-title {
      text-align: center;
      margin: 3pt 0 10.5pt 0;
    }
    .lampiran-title h3 {
      font-size: 12pt;
      font-weight: 400;
      margin: 0;
    }
    .lampiran-table {
      width: 100%;
      font-size: 10pt;
    }
    .lampiran-table th,
    .lampiran-table td {
      border: 0.75pt solid #000;
      padding: 4.5pt 5.25pt;
    }
    .lampiran-table th {
      text-align: center;
      font-weight: 700;
    }
  </style>
</head>
<body>
  <div class="WordSection1">
    <table class="page-table">
      <tr class="page-content-row" style="height:704pt;mso-height-rule:exactly;">
        <td class="page-content">
    <table class="header-table">
      <tr>
        <td class="logo-cell">
          @if($logoSrc !== '')
            <img class="logo" src="{{ $logoSrc }}" alt="Logo Kementerian Ketenagakerjaan" width="100" height="100" style="width:75pt;height:75pt;max-width:75pt;max-height:75pt;display:block;margin:0 auto;">
          @endif
        </td>
        <td class="header-cell">
          <p class="head-line head-line-1">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</p>
          <p class="head-line head-line-2">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</p>
          <p class="head-line head-line-3">DAN KESELAMATAN DAN KESEHATAN KERJA</p>
          <p class="head-line head-line-4">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN<br>KERJA SURABAYA</p>
          <p class="address">Jl. Dukuh Menanggal No. 122 Surabaya, (031) 8280440, balaik3surabaya@kemnaker.go.id</p>
        </td>
      </tr>
    </table>
    <div class="header-line">&nbsp;</div>

    <div class="title">
      <h2>SURAT PERINTAH TUGAS</h2>
      <div class="nomor">No. {{ $spt->nomor_surat }}</div>
    </div>

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
            -
          @endif
        </td>
      </tr>
    </table>

    <div class="command-title">Memerintahkan/Menugaskan:</div>

    <table class="line-table">
      <tr>
        <td class="section-label">Kepada</td>
        <td class="line-separator">:</td>
        <td class="line-content">nama - nama terlampir</td>
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
            -
          @endif
        </td>
      </tr>
    </table>

    <table class="note-box-table">
      <tr>
        <td>ASN Balai Hiperkes dan KK Surabaya Kemnaker tidak menerima gratifikasi dalam pelaksanaan tugas sesuai ketentuan yang berlaku.</td>
      </tr>
    </table>

    <table class="signature-table">
      <tr>
        <td>&nbsp;</td>
        <td class="signature-cell">
          <div class="signature-meta">Ditetapkan&nbsp;&nbsp;&nbsp;&nbsp;: {{ $spt->tempat_terbit }}</div>
          <div class="signature-meta">Pada tanggal&nbsp;&nbsp;: {{ $tanggalTtdLabel }}</div>
          <div style="margin-top:20pt;">Kuasa Pengguna Anggaran</div>
          <div>Balai Hiperkes dan KK Surabaya</div>
          <div class="signature-space">&nbsp;</div>
          <div style="font-weight:700;">{{ $kepalaBalaiNama ?? '-' }}</div>
        </td>
      </tr>
    </table>
        </td>
      </tr>
      <tr class="page-footer-row" style="height:52pt;mso-height-rule:exactly;">
        <td class="page-footer-cell">
          <div class="page-footer">
            <table class="footer-table">
              <tr>
                <td>Tgl. terbit: {{ $tanggalFooterLabel }}</td>
                <td class="footer-right">No. : F/7.3.1/BK3-SBY</td>
              </tr>
            </table>
            <div class="bsre-note">Dokumen ini telah ditandatangani secara elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), BSSN</div>
          </div>
        </td>
      </tr>
    </table>

    <p class="page-break">&nbsp;</p>

    <table class="page-table">
      <tr class="page-content-row" style="height:704pt;mso-height-rule:exactly;">
        <td class="page-content">
    <div class="lampiran-header-wrap">
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
          <th style="width:32pt;">No.</th>
          <th>Nama</th>
          <th style="width:110pt;">NIP</th>
          <th style="width:86pt;">Pangkat/Gol. Ruang</th>
          <th>Jabatan</th>
          <th style="width:78pt;">Keterangan</th>
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
        </td>
      </tr>
      <tr class="page-footer-row" style="height:52pt;mso-height-rule:exactly;">
        <td class="page-footer-cell">
          <div class="page-footer">
            <table class="footer-table">
              <tr>
                <td>Tgl. terbit: {{ $tanggalFooterLabel }}</td>
                <td class="footer-right">No. : F/7.3.1/BK3-SBY</td>
              </tr>
            </table>
            <div class="bsre-note">Dokumen ini telah ditandatangani secara elektronik yang diterbitkan oleh Balai Sertifikasi Elektronik (BSrE), BSSN</div>
          </div>
        </td>
      </tr>
    </table>
  </div>
</body>
</html>
