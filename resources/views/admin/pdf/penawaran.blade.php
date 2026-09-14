<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <style>
    @page { margin: 14mm 12mm; }
    body { font-family: "Times New Roman", serif; color: #111; font-size: 13px; margin: 0; }
    .print-layout { width: 100%; }
    .letter-header { display: table; width: 100%; }
    .logo-wrap, .header-wrap { display: table-cell; vertical-align: middle; }
    .logo-wrap {
      width: 70px;
      padding-left: 0;
      text-align: center;
    }
    .logo { width: 60px; height: 60px; object-fit: contain; display: block; margin: 0 auto; }
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
    .header-line { border-top: 2px solid #000; margin: 10px 0 18px; }
    .line { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: -0.02em; }
    .header-wrap .line:first-of-type { font-size: 12px; font-weight: 500; }
    .header-wrap .line:nth-of-type(2) { font-size: 13px; font-weight: 700; letter-spacing: -0.03em; }
    .header-wrap .line:nth-of-type(3) { font-size: 13px; letter-spacing: -0.03em; }
    .header-wrap .line:nth-of-type(4) { font-size: 15.6px; color: #163e67; line-height: 1.02; margin-top: 2px; letter-spacing: -0.05em; white-space: nowrap; }
    .addr { font-size: 11px; margin-top: 3px; font-family: Arial, sans-serif; text-align: left; }
    .addr .icon { color: #163e67; font-weight: 700; margin: 0 2px; }
    .meta-top { width: 100%; margin-bottom: 14px; }
    .meta-left, .meta-right { display: inline-block; vertical-align: top; }
    .meta-left { width: 68%; }
    .meta-right { width: 31%; text-align: right; }
    .meta-row { margin-bottom: 4px; }
    .meta-label { display: inline-block; width: 72px; }
    .meta-colon { display: inline-block; width: 10px; }
    .kepada { margin: 14px 0; }
    .paragraph { line-height: 1.5; text-indent: 28px; margin: 10px 0; }
    .paragraph.no-indent { text-indent: 0; }
    .section-title { margin-top: 10px; font-style: italic; }
    .param-group { border: 1px solid #000; border-radius: 6px; padding: 8px 10px; margin-top: 8px; }
    .param-title { font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 6px; }
    .param-table { width: 100%; border-collapse: collapse; }
    .param-table th, .param-table td { border: 1px solid #000; padding: 6px 7px; }
    .param-table th { background: #f1f1f1; text-align: center; }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .simple-list { margin: 6px 0 0 18px; padding: 0; }
    .simple-list li { margin-bottom: 6px; }
    .item-title { font-weight: bold; }
    .item-meta { font-size: 12px; margin-top: 2px; }
    .note { margin-top: 12px; }
    .note ul { margin: 6px 0 0 18px; padding: 0; }
    .note li { margin-bottom: 5px; }
    .closing { margin-top: 14px; text-align: center; }
    .signatures { width: 100%; margin-top: 22px; }
    .signatures td { width: 50%; text-align: center; vertical-align: top; }
    .sign-space { height: 60px; }
  </style>
</head>
<body>
  <div class="print-layout">
        <div class="letter-header">
          <div class="logo-wrap">
            @if($logoDataUri)
              <img src="{{ $logoDataUri }}" alt="Logo" class="logo">
            @endif
          </div>
          <div class="header-wrap">
            <div class="line">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
            <div class="line">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
            <div class="line">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
            <div class="line">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
            <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id</div>
          </div>
        </div>
        <div class="header-line"></div>

        <div class="meta-top">
          <div class="meta-left">
            <div class="meta-row"><span class="meta-label">Nomor</span><span class="meta-colon">:</span><span>{{ $nomorSurat }}</span></div>
            <div class="meta-row"><span class="meta-label">Sifat</span><span class="meta-colon">:</span><span>Penting</span></div>
            <div class="meta-row"><span class="meta-label">Perihal</span><span class="meta-colon">:</span><span>{{ $mode === 'non_testable' ? 'Parameter Tidak Bisa Diuji' : 'Rincian Kegiatan' }}</span></div>
          </div>
          <div class="meta-right">Surabaya, {{ $tanggalPenawaran }}</div>
        </div>

        <div class="kepada">
          <div>Kepada Yth. :</div>
          <div>{{ $pelanggan ?: '........................................' }}</div>
          <div>Di-Tempat.</div>
        </div>

        <div class="paragraph">{{ $paragraphText }}</div>

        @if($mode === 'non_testable')
          <div class="paragraph no-indent">
            Jumlah parameter yang tidak bisa diuji: <strong>{{ $nonTestableCount }}</strong> parameter.
          </div>
          <div class="section-title">( Daftar Parameter Tidak Bisa Diuji )</div>
          @php($nonTestableNumber = 1)
          <ul class="simple-list">
            @forelse($nonTestableGroups as $kategori => $items)
              @foreach($items as $item)
                <li>
                  <div class="item-title">{{ $nonTestableNumber }}. [{{ $kategori }}] {{ $item['nama'] }}</div>
                  <div class="item-meta">Alasan: {{ $item['alasan'] }}</div>
                </li>
                @php($nonTestableNumber++)
              @endforeach
            @empty
              <li>Tidak ada parameter tidak bisa diuji.</li>
            @endforelse
          </ul>
        @else
          <div class="section-title">( Rincian Parameter )</div>
          <div class="param-group">
            <div class="param-title">Parameter Bisa Diuji</div>
            <table class="param-table">
              <thead>
                <tr>
                  <th style="width:12%;">Kategori</th>
                  <th>Parameter</th>
                  <th style="width:10%;">Qty</th>
                  <th style="width:16%;">Harga</th>
                  <th style="width:18%;">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                @foreach($testableGroups as $kategori => $items)
                  @foreach($items as $item)
                    <tr>
                      @if($loop->first)
                        <td rowspan="{{ count($items) }}" class="text-center">{{ $kategori }}</td>
                      @endif
                      <td>{{ $item['nama'] }}</td>
                      <td class="text-center">{{ $item['qty'] }}</td>
                      <td class="text-end">Rp {{ number_format((float) $item['harga'], 0, ',', '.') }}</td>
                      <td class="text-end">Rp {{ number_format((float) $item['subtotal'], 0, ',', '.') }}</td>
                    </tr>
                  @endforeach
                @endforeach
              </tbody>
            </table>
          </div>

          @if($nonTestableGroups->isNotEmpty())
            <div class="param-group">
              <div class="param-title">Parameter Tidak Bisa Diuji</div>
              <table class="param-table">
                <thead>
                  <tr>
                    <th style="width:12%;">Kategori</th>
                    <th style="width:38%;">Parameter</th>
                    <th>Alasan</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($nonTestableGroups as $kategori => $items)
                    @foreach($items as $item)
                      <tr>
                        @if($loop->first)
                          <td rowspan="{{ count($items) }}" class="text-center">{{ $kategori }}</td>
                        @endif
                        <td>{{ $item['nama'] }}</td>
                        <td>{{ $item['alasan'] }}</td>
                      </tr>
                    @endforeach
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

          <div class="paragraph no-indent">
            Total biaya pengujian sebesar <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
            ({{ ucfirst($totalTerbilang) }}).
          </div>

          <div class="note">
            <strong>Catatan :</strong>
            <ul>
              <li>(*) Coret yang Tidak Perlu</li>
              <li>Transportasi (Penjemputan) menjadi tanggungan perusahaan (customer);</li>
              <li>Pelunasan paling lambat 7 (Tujuh) hari kerja setelah tanggal pelaksanaan sampling;</li>
              <li>Setelah persetujuan ditandatangani, mohon dikirim kembali via email di balaik3surabaya@kemnaker.go.id paling lambat 5 (hari) hari setelah penawaran ini diterima;</li>
              <li>Apabila terjadi perubahan jadwal sampling agar dikonfirmasi 2 (dua) hari sebelumnya;</li>
              <li>Pembatalan secara sepihak akan dikenakan biaya bahan kimia.</li>
            </ul>
          </div>
        @endif

        @if($catatan !== '')
          <div class="paragraph no-indent"><strong>Catatan Khusus:</strong> {!! nl2br(e($catatan)) !!}</div>
        @endif

        <div class="closing">Demikian atas perhatian dan kerjasamanya kami sampaikan terima kasih.</div>

        <table class="signatures">
          <tr>
            <td>
              <div>Setuju/Tidak Setuju (*)</div>
              <div>{{ $mode === 'non_testable' ? 'Terhadap Informasi' : 'Terhadap Penawaran' }}</div>
              <div class="sign-space"></div>
              <div>{{ $signerName }}</div>
              <div>{{ $signerRole }}</div>
            </td>
            <td>
              <div>Kepala Balai K3 Surabaya</div>
              <div class="sign-space"></div>
              <div>{{ $kepalaBalaiNama ?? '-' }}</div>
              @if((auth()->user()?->role ?? null) !== 'superadmin')
                <div>NIP. {{ $kepalaBalaiNip ?? '-' }}</div>
              @endif
            </td>
          </tr>
        </table>
  </div>
</body>
</html>
