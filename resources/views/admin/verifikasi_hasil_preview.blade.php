<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dokumen Hasil Analisa</title>
  <style>
    body { font-family: Arial, sans-serif; color:#000; margin:32px; }
    .header { display:flex; align-items:center; gap:10px; border-bottom:2px solid #000; padding-bottom:8px; margin-bottom:10px; }
    .header-logo { width:72px; height:72px; object-fit:contain; }
    .header-title {
      font-family: "Arial Narrow", Arial, sans-serif;
      font-weight:700;
      text-transform:uppercase;
      line-height:1.12;
      border-left:2px solid #163e67;
      padding-left:12px;
      text-align:left;
    }
    .line-bold { font-weight:700; font-size:11px; white-space:nowrap; }
    .line-normal { font-weight:500; font-size:13px; }
    .line-main { color:#163e67; font-size:17px; line-height:1.05; white-space:nowrap; }
    .header-sub { font-size:11px; margin-top:3px; font-family: Arial, sans-serif; text-transform:none; }
    .header-sub .icon { color:#163e67; font-weight:700; margin:0 2px; }
    .doc-title { text-align:center; font-weight:700; margin:12px 0; text-decoration:underline; font-size:13px; }
    .meta { margin:14px 0 10px; font-size:11px; }
    .meta table { width:100%; border-collapse:collapse; }
    .meta td { padding:2px 4px; vertical-align:top; }
    .meta td:first-child { width:120px; white-space:nowrap; }
    .table { width:100%; border-collapse:collapse; font-size:11px; margin:10px 0; }
    .table th, .table td { border:1px solid #000; padding:4px; text-align:center; }
    .section-title { font-weight:700; margin-top:12px; }
  </style>
</head>
<body>
  <div class="header">
    <img class="header-logo" src="{{ asset('images/Logo Kemnaker.png') }}" alt="Logo Kementerian Ketenagakerjaan">
    <div>
      <div class="header-title">
        <span class="line-normal">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</span><br>
        <span class="line-bold">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</span><br>
        <span class="line-bold">DAN KESELAMATAN DAN KESEHATAN KERJA</span><br>
        <span class="line-main">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</span>
      </div>
      <div class="header-sub">
        Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id
      </div>
    </div>
  </div>
  <div class="doc-title">DOKUMEN HASIL ANALISA</div>
  <div class="meta">
    <table>
      <tr><td>Kode</td><td>:</td><td>{{ $kode }}</td></tr>
      <tr><td>Perusahaan</td><td>:</td><td>{{ $perusahaan }}</td></tr>
      <tr><td>Parameter</td><td>:</td><td>{{ $parameter }}</td></tr>
    </table>
  </div>

  <div class="section-title">Hasil Perhitungan</div>
  @php
    $stringify = function ($val) {
      if (is_array($val)) {
        return json_encode($val, JSON_UNESCAPED_UNICODE);
      }
      return (string) $val;
    };
    $hasilHitungRows = is_array($hasilPerhitungan) ? $hasilPerhitungan : [];
    $hasilHitungRows = collect($hasilHitungRows)->values();
    $rowTypes = $hasilHitungRows->map(fn($row) => is_array($row) ? ($row['row_type'] ?? '') : '')->filter()->values();
    $normalizeKey = fn ($value) => strtolower(preg_replace('/\s+/', '', trim((string) $value)));
    $parameterKey = strtolower(trim((string) $parameter));
    $categoryKey = $normalizeKey($kategori ?? '');
    $isAmbienCategory = in_array($categoryKey, ['a', 'ambien', 'ambient', 'udaraambien'], true);
    $isLKCategory = in_array($categoryKey, ['lk', 'lingkungankerja'], true);
    $isHfEmisi = preg_match('/\bhf\b/i', (string) $parameter) === 1 || str_contains($parameterKey, 'hidrogen fluorida') || str_contains($parameterKey, 'hydrogen fluoride');
    $isHclEmisi = preg_match('/\bhcl\b/i', (string) $parameter) === 1 || str_contains($parameterKey, 'hidrogen klorida') || str_contains($parameterKey, 'hydrogen chloride');
    $isHgEmisi = preg_match('/\bhg\b/i', (string) $parameter) === 1 || str_contains($parameterKey, 'merkuri') || str_contains($parameterKey, 'mercury');
    $isNh3Keyword = str_contains($parameterKey, 'nh3') || str_contains($parameterKey, 'amonia') || str_contains($parameterKey, 'ammonia');
    $isNh3Emisi = $isNh3Keyword && !$isAmbienCategory && !$isLKCategory;
    $isNo2 = $rowTypes->contains(fn($t) => str_contains($t, 'no2')) && !$isNh3Emisi;
    $isSo2 = $rowTypes->contains(fn($t) => str_contains($t, 'so2')) || $isHfEmisi || $isHclEmisi || $isHgEmisi || $isNh3Emisi;
    $emisiGasLabel = $isHfEmisi
      ? 'HF'
      : ($isHclEmisi ? 'HCL' : ($isHgEmisi ? 'Hg' : ($isNh3Emisi ? 'NH3' : 'SO2')));
    $isGeneric = !$isNo2 && !$isSo2;
    $headers = [];
    if ($isNo2) {
      $headers = ['Lokasi', 'Konsentrasi', 'Vol Spl (ml)', 'Waktu (mnt)', 'FR (lpm)', 'Sk C', 'P mmHg', 'Kadar NO2 (ppm)', 'Kadar NO2 (ug/m3)', 'Kadar NO2 (mg/m3)'];
    } elseif ($isSo2) {
      $headers = $isHfEmisi
        ? ['Lokasi', "Kons {$emisiGasLabel} (mg)", 'Vol spl (ml)', 'FR (lpm)', 'Waktu (mnt)', 'Titr HF (ml)', 'TM (C)', 'P (mmHg)', "Kadar {$emisiGasLabel} (mg/m3)"]
        : ['Lokasi', "Kons {$emisiGasLabel} (mg)", 'Vol spl (ml)', 'FR (lpm)', 'Waktu (mnt)', 'TM (C)', 'P (mmHg)', "Kadar {$emisiGasLabel} (mg/m3)"];
    } else {
      $headers = [];
      if ($hasilHitungRows->count()) {
        $firstHitung = $hasilHitungRows->first();
        if (is_array($firstHitung)) {
          $headers = array_keys($firstHitung);
        }
      }
    }
  @endphp
  <table class="table">
    <thead>
      <tr>
        @if(count($headers))
          @foreach($headers as $header)
            <th>{{ $header }}</th>
          @endforeach
        @else
          <th>Data</th>
        @endif
      </tr>
    </thead>
    <tbody>
      @forelse($hasilHitungRows as $row)
        <tr>
          @if($isNo2)
            @php
              $cols = is_array($row) ? ($row['cols'] ?? []) : [];
              $cells = [
                $cols[0] ?? ($row['lokasi'] ?? ''),
                $cols[1] ?? ($row['kons'] ?? ''),
                $cols[2] ?? ($row['vol'] ?? ''),
                $cols[3] ?? ($row['waktu'] ?? ''),
                $cols[4] ?? ($row['fr'] ?? ''),
                $cols[5] ?? ($row['sk'] ?? ''),
                $cols[6] ?? ($row['pm'] ?? ''),
                $cols[7] ?? ($row['kadar_ppm'] ?? ''),
                $cols[8] ?? ($row['kadar_ugm3'] ?? ''),
                $cols[9] ?? ($row['kadar_mgm3'] ?? ''),
              ];
            @endphp
            @foreach($cells as $cell)
              <td>{{ $stringify($cell) }}</td>
            @endforeach
          @elseif($isSo2)
            @php
              $cols = is_array($row) ? ($row['cols'] ?? []) : [];
              $isHf = $isHfEmisi;
              $cells = $isHf
                ? [
                    $cols[0] ?? ($row['lokasi'] ?? ''),
                    $cols[1] ?? ($row['kons'] ?? ''),
                    $cols[2] ?? ($row['vol'] ?? ''),
                    $cols[3] ?? ($row['fr'] ?? ''),
                    $cols[4] ?? ($row['waktu'] ?? ''),
                    $cols[5] ?? ($row['titr_hf'] ?? ''),
                    $cols[6] ?? ($row['tm'] ?? ''),
                    $cols[7] ?? ($row['p'] ?? ''),
                    $cols[8] ?? ($row['kadar_ugm3'] ?? ''),
                  ]
                : [
                    $cols[0] ?? ($row['lokasi'] ?? ''),
                    $cols[1] ?? ($row['kons'] ?? ''),
                    $cols[2] ?? ($row['vol'] ?? ''),
                    $cols[3] ?? ($row['fr'] ?? ''),
                    $cols[4] ?? ($row['waktu'] ?? ''),
                    $cols[5] ?? ($row['tm'] ?? ''),
                    $cols[6] ?? ($row['p'] ?? ''),
                    $cols[7] ?? ($row['kadar_ugm3'] ?? ''),
                  ];
            @endphp
            @foreach($cells as $cell)
              <td>{{ $stringify($cell) }}</td>
            @endforeach
          @elseif(count($headers))
            @foreach($headers as $header)
              <td>{{ $stringify(is_array($row) ? ($row[$header] ?? '') : $row) }}</td>
            @endforeach
          @else
            <td>{{ $stringify(is_array($row) ? $row : $row) }}</td>
          @endif
        </tr>
      @empty
        <tr><td>-</td></tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
