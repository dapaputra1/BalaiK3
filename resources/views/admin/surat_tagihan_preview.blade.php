<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Surat Tagihan {{ $kode ?? '-' }}</title>
  <style>
    :root {
      --ink: #0f172a;
      --muted: #64748b;
      --line: #dbe4ef;
      --brand: #15406a;
      --bg: #f3f7fb;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: "Segoe UI", Tahoma, Arial, sans-serif;
      color: var(--ink);
      background: var(--bg);
      padding: 24px;
    }
    .page {
      max-width: 980px;
      margin: 0 auto;
      background: #fff;
      border: 1px solid var(--line);
      border-radius: 16px;
      box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
      overflow: hidden;
    }
    .toolbar {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      padding: 14px 18px;
      background: #fff;
      border-bottom: 1px solid var(--line);
      position: sticky;
      top: 0;
      z-index: 5;
    }
    .btn {
      border: 1px solid var(--line);
      background: #fff;
      color: var(--ink);
      border-radius: 10px;
      padding: 8px 12px;
      font-size: 13px;
      cursor: pointer;
    }
    .btn-primary {
      border-color: var(--brand);
      background: var(--brand);
      color: #fff;
    }
    .doc {
      padding: 28px 34px 34px;
    }
    .kop {
      display: grid;
      grid-template-columns: 84px 1fr;
      align-items: center;
      gap: 8px;
      border-bottom: 2px solid #111827;
      padding-bottom: 10px;
    }
    .kop img {
      width: 72px;
      height: 72px;
      object-fit: contain;
      justify-self: center;
    }
    .kop .center {
      text-align: left;
      line-height: 1.12;
      font-size: 14px;
      font-family: "Arial Narrow", Arial, sans-serif;
      border-left: 2px solid #163e67;
      padding-left: 12px;
    }
    .kop .center .up {
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0;
    }
    .kop .center .up:first-child {
      font-size: 13px;
      font-weight: 500;
    }
    .kop .center .up:nth-child(2) { font-size: 14px; white-space: nowrap; }
    .kop .center .up:nth-child(4) {
      font-size: 17px;
      color: #163e67;
      line-height: 1.05;
      margin-top: 3px;
      white-space: nowrap;
    }
    .kop .center .addr {
      margin-top: 3px;
      font-size: 11px;
      color: #334155;
      font-family: Arial, sans-serif;
    }
    .kop .center .addr .icon {
      color: #163e67;
      font-weight: 700;
      margin: 0 2px;
    }
    .title-row {
      margin-top: 20px;
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      gap: 16px;
      flex-wrap: wrap;
    }
    .title {
      font-size: 28px;
      font-weight: 800;
      color: var(--brand);
      margin: 0;
      text-transform: uppercase;
    }
    .badge {
      display: inline-block;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 700;
      color: #0b3b67;
      background: #e8f1f9;
      border: 1px solid #cfe1f2;
    }
    .meta {
      margin-top: 16px;
      display: grid;
      grid-template-columns: repeat(2, minmax(220px, 1fr));
      gap: 10px 24px;
      font-size: 14px;
    }
    .meta .row {
      display: grid;
      grid-template-columns: 120px 10px 1fr;
      align-items: start;
      gap: 6px;
    }
    .meta .label { color: var(--muted); }
    .paragraph {
      margin-top: 18px;
      font-size: 14px;
      line-height: 1.7;
      text-align: justify;
    }
    .section-title {
      margin-top: 24px;
      margin-bottom: 10px;
      font-size: 15px;
      font-weight: 700;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    th, td {
      border-bottom: 1px solid var(--line);
      padding: 10px 10px;
      vertical-align: top;
    }
    th {
      text-align: left;
      font-size: 12px;
      letter-spacing: .3px;
      text-transform: uppercase;
      color: #475569;
      background: #f8fbff;
    }
    td.num, th.num { text-align: right; white-space: nowrap; }
    td.center, th.center { text-align: center; white-space: nowrap; }
    .total-card {
      margin-top: 18px;
      display: flex;
      justify-content: flex-end;
    }
    .total-inner {
      min-width: 320px;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 14px 16px;
      background: #fcfdff;
    }
    .total-inner .label {
      color: var(--muted);
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .4px;
    }
    .total-inner .value {
      margin-top: 4px;
      font-size: 24px;
      font-weight: 800;
      color: #0b3b67;
    }
    .signature {
      margin-top: 40px;
      width: 280px;
      margin-left: auto;
      text-align: center;
      font-size: 14px;
      line-height: 1.8;
    }
    @media print {
      body { background: #fff; padding: 0; }
      .page { box-shadow: none; border: none; border-radius: 0; max-width: none; }
      .toolbar { display: none; }
      .doc { padding: 18mm 14mm; }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="toolbar">
      <button type="button" class="btn" onclick="window.close()">Tutup</button>
      <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="doc">
      <div class="kop">
        <img src="{{ asset('images/Logo Kemnaker.png') }}" alt="Logo">
        <div class="center">
          <div class="up">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
          <div class="up">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
          <div class="up">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
          <div class="up">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
          <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id</div>
        </div>
      </div>

      <div class="title-row">
        <h1 class="title">Surat Tagihan</h1>
        <span class="badge">Tahap sebelum kode billing</span>
      </div>

      <div class="meta">
        <div class="row">
          <span class="label">No Order</span><span>:</span><strong>{{ $kode ?? '-' }}</strong>
        </div>
        <div class="row">
          <span class="label">Tanggal</span><span>:</span><strong>{{ $tanggal ?? '-' }}</strong>
        </div>
        <div class="row">
          <span class="label">Perusahaan</span><span>:</span><strong>{{ $perusahaan ?? '-' }}</strong>
        </div>
        <div class="row">
          <span class="label">Alamat</span><span>:</span><strong>{{ $alamat ?? '-' }}</strong>
        </div>
      </div>

      <div class="paragraph">
        Dengan hormat, bersama surat ini kami sampaikan tagihan layanan pengujian untuk permohonan
        <strong>{{ $kode ?? '-' }}</strong>. Rincian parameter dan nilai tagihan sementara tercantum di bawah ini
        sebagai dasar administrasi sebelum diterbitkan kode billing dan kuitansi pembayaran.
      </div>

      <div class="section-title">Rincian Layanan Pengujian</div>
      <table>
        <thead>
          <tr>
            <th>Parameter</th>
            <th class="center">Qty</th>
            <th class="num">Harga</th>
            <th class="num">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $item)
            @php
              $qty = (int) ($item['qty'] ?? 0);
              $harga = (float) ($item['harga'] ?? 0);
              $subtotal = $qty * $harga;
            @endphp
            <tr>
              <td>{{ $item['nama'] ?? '-' }}</td>
              <td class="center">{{ $qty }}</td>
              <td class="num">Rp {{ number_format($harga, 0, ',', '.') }}</td>
              <td class="num">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="center">Tidak ada rincian tagihan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>

      <div class="total-card">
        <div class="total-inner">
          <div class="label">Total Tagihan Sementara</div>
          <div class="value">Rp {{ number_format((float) ($total ?? 0), 0, ',', '.') }}</div>
        </div>
      </div>

      <div class="paragraph">
        Demikian surat tagihan ini kami sampaikan. Setelah surat tagihan di-ACC pemohon, petugas akan mengirimkan
        kode billing untuk pembayaran.
      </div>

      <div class="signature">
        Surabaya, {{ $tanggal ?? '-' }}<br>
        Petugas Administrasi,<br><br><br><br>
        ______________________
      </div>
    </div>
  </div>
</body>
</html>
