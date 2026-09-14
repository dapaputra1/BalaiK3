<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $document_title ?? 'INVOICE' }} {{ $kode ?? '-' }}</title>
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
      border-bottom: 2px solid #111827;
      padding-bottom: 10px;
    }
    .kop-table {
      width: 100%;
      border-collapse: collapse;
    }
    .kop-table td {
      border: 0;
      padding: 0;
      vertical-align: middle;
    }
    .kop-logo-cell {
      width: 76px;
      text-align: center;
    }
    .kop img {
      width: 64px;
      height: 64px;
      object-fit: contain;
    }
    .kop .center {
      text-align: left;
      line-height: 1.16;
      font-size: 12px;
      font-family: "Arial Narrow", Arial, sans-serif;
      border-left: 2px solid #163e67;
      padding-left: 10px;
      min-width: 0;
    }
    .kop .center .up {
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0;
      white-space: normal;
    }
    .kop .center .up:first-child {
      font-size: 11px;
      font-weight: 500;
    }
    .kop .center .up:nth-child(2) { font-size: 12px; }
    .kop .center .up:nth-child(4) {
      font-size: 14px;
      color: #163e67;
      line-height: 1.08;
      margin-top: 3px;
    }
    .kop .center .addr {
      margin-top: 3px;
      font-size: 9px;
      color: #334155;
      font-family: Arial, sans-serif;
      line-height: 1.2;
    }
    .kop .center .addr .icon {
      color: #163e67;
      font-weight: 700;
      margin: 0 2px;
    }
    .title-row {
      margin-top: 20px;
    }
    .title-table {
      width: 100%;
      border-collapse: collapse;
    }
    .title-table td {
      border: 0;
      padding: 0;
      vertical-align: bottom;
    }
    .title-badge-cell {
      text-align: right;
      white-space: nowrap;
    }
    .title {
      font-size: 30px;
      font-weight: 800;
      letter-spacing: .5px;
      color: var(--brand);
      margin: 0;
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
      font-size: 14px;
    }
    .meta-table {
      width: 100%;
      border-collapse: collapse;
    }
    .meta-table td {
      border: 0;
      padding: 4px 0;
      vertical-align: top;
    }
    .meta-table .label {
      width: 96px;
      color: var(--muted);
    }
    .meta-table .colon {
      width: 12px;
      text-align: center;
    }
    .meta-table .value {
      font-weight: 700;
      color: var(--ink);
    }
    .meta-table .gap {
      width: 26px;
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
    tfoot td {
      border-top: 2px solid #1e293b;
      border-bottom: none;
      font-weight: 700;
      background: #f8fafc;
    }
    .total-card {
      margin-top: 18px;
      text-align: right;
    }
    .total-inner {
      min-width: 320px;
      width: 320px;
      margin-left: auto;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 14px 16px;
      background: #fcfdff;
      text-align: left;
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
    .footnote {
      margin-top: 22px;
      color: var(--muted);
      font-size: 12px;
    }
    @media print {
      body { background: #fff; padding: 0; }
      .page { box-shadow: none; border: none; border-radius: 0; max-width: none; }
      .toolbar { display: none; }
      .doc { padding: 18mm 14mm; }
    }
    @if(!empty($isPdf))
      body { background: #fff; padding: 0; }
      .page { box-shadow: none; border: none; border-radius: 0; max-width: none; }
      .toolbar { display: none; }
      .doc { padding: 18mm 14mm; }
    @endif
  </style>
</head>
<body>
  <div class="page">
    @empty($isPdf)
      <div class="toolbar">
        <button type="button" class="btn" onclick="window.close()">Tutup</button>
        <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save PDF</button>
      </div>
    @endempty

    <div class="doc">
      <div class="kop">
        <table class="kop-table">
          <tr>
            <td class="kop-logo-cell">
              <img src="{{ $logoDataUri ?? asset('images/Logo Kemnaker.png') }}" alt="Logo">
            </td>
            <td>
              <div class="center">
                <div class="up">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
                <div class="up">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
                <div class="up">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
                <div class="up">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
                <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id</div>
              </div>
            </td>
          </tr>
        </table>
      </div>

      <div class="title-row">
        <table class="title-table">
          <tr>
            <td><h1 class="title">{{ $document_title ?? 'INVOICE' }}</h1></td>
            <td class="title-badge-cell"><span class="badge">Parameter Setelah Pengujian</span></td>
          </tr>
        </table>
      </div>

      <div class="meta">
        <table class="meta-table">
          <tr>
            <td class="label">No Order</td>
            <td class="colon">:</td>
            <td class="value">{{ $kode ?? '-' }}</td>
            <td class="gap"></td>
            <td class="label">Tanggal</td>
            <td class="colon">:</td>
            <td class="value">{{ $tanggal ?? '-' }}</td>
          </tr>
          <tr>
            <td class="label">Perusahaan</td>
            <td class="colon">:</td>
            <td class="value">{{ $perusahaan ?? '-' }}</td>
            <td class="gap"></td>
            <td class="label">Alamat</td>
            <td class="colon">:</td>
            <td class="value">{{ $alamat ?? '-' }}</td>
          </tr>
        </table>
      </div>

      <div class="section-title">Rincian Biaya Pengujian</div>
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
              <td colspan="4" class="center" style="color:#64748b;">Tidak ada data parameter.</td>
            </tr>
          @endforelse
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="num">TOTAL</td>
            <td class="num">Rp {{ number_format((float) ($total ?? 0), 0, ',', '.') }}</td>
          </tr>
        </tfoot>
      </table>

      <div class="total-card">
        <div class="total-inner">
          <div class="label">Grand Total</div>
          <div class="value">Rp {{ number_format((float) ($total ?? 0), 0, ',', '.') }}</div>
        </div>
      </div>

      <div class="footnote">
        Dokumen ini digenerate otomatis dari data parameter setelah pengujian.
      </div>
    </div>
  </div>
</body>
</html>
