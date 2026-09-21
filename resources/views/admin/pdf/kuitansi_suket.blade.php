<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Kuitansi Pembayaran - {{ $suket->nomor_order }}</title>
  <style>
    @page {
      margin: 1.5cm 1.8cm 1.5cm 1.8cm;
      size: a4 portrait;
    }
    body {
      font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
      font-size: 10pt;
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
      font-size: 11pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .kop-text .instansi-2 {
      font-size: 9.5pt;
      font-weight: bold;
      text-transform: uppercase;
    }
    .kop-text .instansi-3 {
      font-size: 11pt;
      font-weight: bold;
      color: #15406a;
      text-transform: uppercase;
      margin-top: 2px;
    }
    .kop-text .alamat {
      font-size: 8pt;
      line-height: 1.25;
      margin-top: 3px;
      color: #374151;
    }

    .doc-header {
      text-align: center;
      margin-top: 6px;
      margin-bottom: 14px;
    }
    .doc-title {
      font-size: 13pt;
      font-weight: bold;
      text-transform: uppercase;
      text-decoration: underline;
      letter-spacing: 0.5px;
      color: #111827;
      margin-bottom: 3px;
    }
    .doc-number {
      font-size: 9.5pt;
      color: #374151;
      font-weight: bold;
    }

    .kuitansi-box {
      border: 1.5px solid #15406a;
      border-radius: 8px;
      padding: 14px 18px;
      background-color: #fafbfc;
      margin-bottom: 16px;
      position: relative;
    }

    .watermark-paid {
      position: absolute;
      top: 30%;
      left: 30%;
      font-size: 40pt;
      font-weight: 900;
      color: rgba(16, 185, 129, 0.12);
      border: 4px dashed rgba(16, 185, 129, 0.20);
      padding: 4px 20px;
      transform: rotate(-10deg);
      border-radius: 10px;
      letter-spacing: 4px;
      text-align: center;
    }

    .receipt-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .receipt-table td {
      padding: 5px 4px;
      vertical-align: top;
      font-size: 9.5pt;
    }
    .receipt-table .label {
      width: 28%;
      font-weight: 600;
      color: #374151;
    }
    .receipt-table .sep {
      width: 3%;
      text-align: center;
      font-weight: 600;
    }
    .receipt-table .val {
      width: 69%;
      color: #111827;
    }

    .terbilang-box {
      background-color: #e2e8f0;
      border-left: 4px solid #15406a;
      padding: 8px 12px;
      font-style: italic;
      font-weight: bold;
      color: #0f172a;
      font-size: 9.5pt;
      margin-top: 3px;
      margin-bottom: 8px;
    }

    .nominal-badge-wrap {
      margin-top: 10px;
      padding: 10px 14px;
      background: #ecfdf5;
      border: 1.5px solid #10b981;
      border-radius: 6px;
      display: inline-block;
      min-width: 240px;
    }
    .nominal-title {
      font-size: 8pt;
      font-weight: bold;
      color: #047857;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 2px;
    }
    .nominal-amount {
      font-size: 14pt;
      font-weight: 900;
      color: #065f46;
      font-family: "Courier New", Courier, monospace;
    }

    .status-stamp {
      float: right;
      border: 1.5px solid #059669;
      color: #059669;
      background: #d1fae5;
      font-size: 10pt;
      font-weight: bold;
      padding: 6px 14px;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 4px;
    }

    .sign-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 18px;
    }
    .sign-table td {
      vertical-align: top;
      font-size: 9.5pt;
    }

    .notes {
      margin-top: 18px;
      padding-top: 8px;
      border-top: 1px dashed #cbd5e1;
      font-size: 8pt;
      color: #64748b;
      line-height: 1.35;
    }
    .notes strong {
      color: #475569;
    }
  </style>
</head>
<body>

  {{-- KOP SURAT RESMI KEMNAKER / BALAI K3 SURABAYA --}}
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
        <div class="instansi-3">BALAI KESELAMATAN DAN KESEHATAN KERJA SURABAYA</div>
        <div class="alamat">
          Jl. Dukuh Menanggal XII/No. 2, Gayungan, Surabaya, Jawa Timur 60234<br>
          Telepon: (031) 8280220 | Email: balaik3_sby@kemnaker.go.id | Website: balaik3surabaya.kemnaker.go.id
        </div>
      </td>
    </tr>
  </table>

  {{-- JUDUL DOKUMEN --}}
  <div class="doc-header">
    <div class="doc-title">KUITANSI PEMBAYARAN RESMI (PNBP)</div>
    <div class="doc-number">Nomor: {{ $kuitansiNumber }}</div>
  </div>

  {{-- KUITANSI CARD --}}
  <div class="kuitansi-box">
    <div class="watermark-paid">LUNAS</div>

    <table class="receipt-table">
      <tr>
        <td class="label">Telah Diterima Dari</td>
        <td class="sep">:</td>
        <td class="val"><strong>{{ $suket->perusahaan_nama ?: ($suket->user->name ?? '-') }}</strong></td>
      </tr>
      <tr>
        <td class="label">Alamat / Lokasi Uji</td>
        <td class="sep">:</td>
        <td class="val">{{ $suket->lokasi ?: '-' }} (PIC / Pemohon: {{ $suket->pemohon ?: ($suket->user->name ?? '-') }})</td>
      </tr>
      <tr>
        <td class="label">Uang Sejumlah</td>
        <td class="sep">:</td>
        <td class="val">
          <div class="terbilang-box">
            # {{ $terbilang }} #
          </div>
        </td>
      </tr>
      <tr>
        <td class="label">Untuk Pembayaran</td>
        <td class="sep">:</td>
        <td class="val">
          Biaya Pelayanan Jasa Evaluasi Teknis, Pengujian, dan Penerbitan <strong>Surat Keterangan K3 (Suket K3)</strong>
          secara resmi sesuai ketentuan Penerimaan Negara Bukan Pajak (PNBP) Kementerian Ketenagakerjaan Republik Indonesia.
        </td>
      </tr>
      <tr>
        <td class="label">Nomor Order / Permohonan</td>
        <td class="sep">:</td>
        <td class="val"><code>{{ $suket->nomor_order }}</code></td>
      </tr>
      @if(!empty($suket->nomor_surat))
      <tr>
        <td class="label">Nomor Surat Keterangan</td>
        <td class="sep">:</td>
        <td class="val"><strong>{{ $suket->nomor_surat }}</strong></td>
      </tr>
      @endif
      <tr>
        <td class="label">Faktor K3 Lingkungan Kerja</td>
        <td class="sep">:</td>
        <td class="val">{{ is_array($suket->faktor_k3) ? implode(', ', array_map('ucfirst', $suket->faktor_k3)) : 'Lingkungan Kerja' }}</td>
      </tr>
      @if(!empty($suket->billing_kode))
      <tr>
        <td class="label">Kode Billing SIMPONI</td>
        <td class="sep">:</td>
        <td class="val"><strong>{{ $suket->billing_kode }}</strong></td>
      </tr>
      @endif
      @if(!empty($suket->billing_verified_at))
      <tr>
        <td class="label">Tanggal Verifikasi Lunas</td>
        <td class="sep">:</td>
        <td class="val">{{ \Carbon\Carbon::parse($suket->billing_verified_at)->translatedFormat('d F Y, H:i') }} WIB</td>
      </tr>
      @endif
    </table>

    <div>
      <div class="nominal-badge-wrap">
        <div class="nominal-title">Jumlah PNBP Diterima</div>
        <div class="nominal-amount">Rp {{ number_format($nominalKuitansi, 0, ',', '.') }},-</div>
      </div>
      <div class="status-stamp">
        &#10003; LUNAS &bull; VERIFIED
      </div>
      <div style="clear: both;"></div>
    </div>
  </div>

  {{-- TANDA TANGAN & PENGESAHAN --}}
  <table class="sign-table">
    <tr>
      <td style="width: 50%;">
        <div style="font-size: 8.5pt; color: #475569; padding-right: 18px;">
          <strong>Keterangan Kas Negara:</strong><br>
          Pembayaran PNBP telah disetorkan ke Kas Negara Republik Indonesia dan diverifikasi secara sah melalui Sistem Informasi PNBP Online (SIMPONI) Kementerian Keuangan RI.
        </div>
      </td>
      <td style="width: 50%; text-align: center;">
        <div>Surabaya, {{ $tanggalKuitansi }}</div>
        <div style="font-weight: bold; margin-top: 3px;">Bendahara Penerimaan / Petugas Keuangan</div>
        <div style="font-weight: bold;">Balai K3 Surabaya</div>
        
        {{-- Area tanda tangan / stempel dinas --}}
        <div style="height: 52px; line-height: 52px; color: #94a3b8; font-size: 8pt; font-style: italic;">
          [ Dokumen Resmi Diterbitkan Elektronik ]
        </div>

        <div style="font-weight: bold; text-decoration: underline;">{{ $bendaharaNama }}</div>
        <div style="font-size: 8.5pt; color: #4b5563;">NIP. {{ $bendaharaNip }}</div>
      </td>
    </tr>
  </table>

  {{-- CATATAN KAKI --}}
  <div class="notes">
    <strong>Perhatian:</strong> Kuitansi ini merupakan bukti penerimaan pembayaran yang sah atas Penerimaan Negara Bukan Pajak (PNBP) Balai Keselamatan dan Kesehatan Kerja Surabaya. Simpan kuitansi ini sebagai bukti sah administrasi pemohon.
  </div>

</body>
</html>
