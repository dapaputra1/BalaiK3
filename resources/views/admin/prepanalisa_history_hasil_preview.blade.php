@php
  $normalizeDataset = function ($dataset) {
    if (!is_array($dataset)) {
      return ['columns' => [], 'rows' => []];
    }

    if (array_key_exists('rows', $dataset) && is_array($dataset['rows'])) {
      $columns = [];
      if (isset($dataset['columns']) && is_array($dataset['columns'])) {
        foreach ($dataset['columns'] as $column) {
          $text = trim((string) $column);
          if ($text !== '') {
            $columns[] = $text;
          }
        }
      }

      return [
        'columns' => array_values($columns),
        'rows' => array_values($dataset['rows']),
      ];
    }

    return [
      'columns' => [],
      'rows' => array_values($dataset),
    ];
  };

  $stringify = function ($value) {
    if (is_array($value)) {
      return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
    if (is_bool($value)) {
      return $value ? 'true' : 'false';
    }
    return trim((string) $value);
  };

  $normalizeKey = fn ($value) => strtolower(preg_replace('/\s+/', '', trim((string) $value)));
  $parameterName = trim((string) ($parameter ?? '-'));
  $parameterKey = strtolower($parameterName);
  $categoryKey = $normalizeKey($kategori ?? '');

  $isAmbienCategory = in_array($categoryKey, ['a', 'ambien', 'ambient', 'udaraambien'], true);
  $isLKCategory = in_array($categoryKey, ['lk', 'lingkungankerja'], true);
  $isSO2Ambien = (str_contains($parameterKey, 'so2') || str_contains($parameterKey, 'sulfur dioksida')) && ($isAmbienCategory || str_contains($parameterKey, 'ambien'));
  $isSO2Emisi = (str_contains($parameterKey, 'so2') || str_contains($parameterKey, 'sulfur dioksida')) && !$isSO2Ambien;
  $isHFEmisi = (preg_match('/\bhf\b/i', $parameterName) === 1 || str_contains($parameterKey, 'hidrogen fluorida') || str_contains($parameterKey, 'hydrogen fluoride')) && !$isAmbienCategory && !$isLKCategory;
  $isHCLEmisi = (preg_match('/\bhcl\b/i', $parameterName) === 1 || str_contains($parameterKey, 'hidrogen klorida') || str_contains($parameterKey, 'hydrogen chloride')) && !$isAmbienCategory && !$isLKCategory;
  $isHgEmisi = (preg_match('/\bhg\b/i', $parameterName) === 1 || str_contains($parameterKey, 'merkuri') || str_contains($parameterKey, 'mercury')) && !$isAmbienCategory && !$isLKCategory;
  $isNh3Keyword = str_contains($parameterKey, 'nh3') || str_contains($parameterKey, 'amonia') || str_contains($parameterKey, 'ammonia');
  $isNH3Emisi = $isNh3Keyword && !$isAmbienCategory && !$isLKCategory;
  $isEmisiAcidGasLike = $isSO2Emisi || $isHFEmisi || $isHCLEmisi || $isHgEmisi || $isNH3Emisi;
  $emisiGasLabel = $isHFEmisi ? 'HF' : ($isHCLEmisi ? 'HCL' : ($isHgEmisi ? 'Hg' : ($isNH3Emisi ? 'NH3' : 'SO2')));
  $isNO2Ambien = str_contains($parameterKey, 'no2') && ($isAmbienCategory || str_contains($parameterKey, 'ambien'));
  $isNO2Gas = str_contains($parameterKey, 'no2') && !$isNO2Ambien;
  $isNH3 = $isNh3Keyword && !$isNH3Emisi;
  $isH2S = str_contains($parameterKey, 'h2s') || str_contains($parameterKey, 'hidrogen sulfida') || str_contains($parameterKey, 'hydrogen sulfide');
  $isOX = str_contains($parameterKey, 'ox') || str_contains($parameterKey, 'oksidan') || str_contains($parameterKey, 'oxidant');
  $debuKey = $normalizeKey($parameterName);
  $isDebuPm25 = str_contains($debuKey, 'pm2,5') || str_contains($debuKey, 'pm2.5') || str_contains($debuKey, 'pm25');
  $isDebuPm10 = str_contains($debuKey, 'pm10');
  $isDebuPerseorangan = str_contains($debuKey, 'debuperseorangan') || str_contains($debuKey, 'debupeseorangan');
  $isDebuPm = $isDebuPm25 || $isDebuPm10 || $isDebuPerseorangan;
  $isHCAmbien = preg_match('/\bhc\b/i', $parameterName) === 1 && $isAmbienCategory;
  $isBenzeneAmbien = (str_contains($parameterKey, 'benzene') || str_contains($parameterKey, 'benzena') || str_contains($parameterKey, 'benzen')) && $isAmbienCategory;
  $isBenzeneLK = (str_contains($parameterKey, 'benzene') || str_contains($parameterKey, 'benzena') || str_contains($parameterKey, 'benzen')) && $isLKCategory;
  $isTolueneAmbien = (str_contains($parameterKey, 'toluene') || str_contains($parameterKey, 'toluena') || str_contains($parameterKey, 'toluen')) && $isAmbienCategory;
  $isTolueneLK = (str_contains($parameterKey, 'toluene') || str_contains($parameterKey, 'toluena') || str_contains($parameterKey, 'toluen')) && $isLKCategory;
  $isXyleneAmbien = (str_contains($parameterKey, 'xylene') || str_contains($parameterKey, 'xilena')) && $isAmbienCategory;
  $isXyleneLK = (str_contains($parameterKey, 'xylene') || str_contains($parameterKey, 'xilena')) && $isLKCategory;
  $isHcLike = $isHCAmbien || $isBenzeneAmbien || $isBenzeneLK || $isTolueneAmbien || $isTolueneLK || $isXyleneAmbien || $isXyleneLK;
  $isNo2Like = $isNO2Gas || $isNO2Ambien || $isSO2Ambien || $isNH3 || $isH2S || $isOX;

  $gasLabel = 'NO2';
  if ($isEmisiAcidGasLike) {
    $gasLabel = $emisiGasLabel;
  } elseif ($isSO2Ambien) {
    $gasLabel = 'SO2';
  } elseif ($isOX) {
    $gasLabel = 'Ox';
  } elseif ($isNH3) {
    $gasLabel = 'NH3';
  } elseif ($isH2S) {
    $gasLabel = 'H2S';
  }

  $compoundLabel = 'Benzene';
  if (str_contains($parameterKey, 'toluene') || str_contains($parameterKey, 'toluena') || str_contains($parameterKey, 'toluen')) {
    $compoundLabel = 'Toluene';
  } elseif (str_contains($parameterKey, 'xylene') || str_contains($parameterKey, 'xilena')) {
    $compoundLabel = 'Xylene';
  }

  $categoryMeta = [
    'sample_label' => 'Sampel',
    'heading_label' => 'SAMPEL',
  ];
  if ($isLKCategory || $categoryKey === 'lk') {
    $categoryMeta = [
      'sample_label' => 'Lingkungan Kerja',
      'heading_label' => 'LINGKUNGAN KERJA',
    ];
  } elseif ($isAmbienCategory || in_array($categoryKey, ['a', 'ambient', 'ambien'], true)) {
    $categoryMeta = [
      'sample_label' => 'Ambien',
      'heading_label' => 'KUALITAS UDARA AMBIENT',
    ];
  } elseif (in_array($categoryKey, ['e', 'emisi'], true)) {
    $categoryMeta = [
      'sample_label' => 'Emisi',
      'heading_label' => 'EMISI',
    ];
  } elseif (trim((string) ($kategori ?? '')) !== '' && trim((string) ($kategori ?? '')) !== '-') {
    $categoryMeta = [
      'sample_label' => trim((string) $kategori),
      'heading_label' => strtoupper(trim((string) $kategori)),
    ];
  }

  $formatDate = function ($value) {
    if (!$value) {
      return '-';
    }
    try {
      $date = $value instanceof \Carbon\CarbonInterface ? $value : \Illuminate\Support\Carbon::parse($value);
      return $date->locale('id')->translatedFormat('d F Y');
    } catch (\Throwable $e) {
      return '-';
    }
  };

  $input = $normalizeDataset($hasilBaca ?? []);
  $hitung = $normalizeDataset($hasilPerhitungan ?? []);
  $inputRows = collect($input['rows'] ?? [])->values();
  $calcRows = collect($hitung['rows'] ?? [])->values();

  $getCols = function ($row) {
    return is_array($row) && is_array($row['cols'] ?? null)
      ? array_values($row['cols'])
      : [];
  };

  $getCell = function ($row, array $keys = [], ?int $index = null) use ($getCols, $stringify) {
    if (!is_array($row)) {
      return $stringify($row);
    }

    foreach ($keys as $key) {
      if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
        return $stringify($row[$key]);
      }
    }

    if ($index !== null) {
      $cols = $getCols($row);
      if (array_key_exists($index, $cols)) {
        return $stringify($cols[$index]);
      }
    }

    return '';
  };

  $getLabel = function ($row) use ($getCell) {
    return trim($getCell($row, ['label'], 0));
  };

  $normalizeLabel = fn ($value) => strtolower(trim((string) $value));
  $isBlankLabel = fn ($value) => in_array($normalizeLabel($value), ['blk', 'blanko'], true);
  $isAverageLabel = fn ($value) => in_array($normalizeLabel($value), ['rata-rata', 'rata rata'], true);

  $sampleCount = $inputRows
    ->filter(function ($row) use ($getLabel, $isBlankLabel, $isAverageLabel) {
      $label = $getLabel($row);
      return $label !== '' && !$isBlankLabel($label) && !$isAverageLabel($label);
    })
    ->count();

  $specialCalcTypes = ['no2-mdl', 'so2-mdl', 'debu-mdl', 'hc-lod'];
  $normalCalcRows = $calcRows->filter(function ($row) use ($specialCalcTypes) {
    $type = is_array($row) ? (string) ($row['row_type'] ?? '') : '';
    return !in_array($type, $specialCalcTypes, true);
  })->values();
  $tailCalcRows = $calcRows->filter(function ($row) use ($specialCalcTypes) {
    $type = is_array($row) ? (string) ($row['row_type'] ?? '') : '';
    return in_array($type, $specialCalcTypes, true);
  })->values();

  $inferHeaders = function ($dataset) {
    $columns = $dataset['columns'] ?? [];
    if (!empty($columns)) {
      return array_values($columns);
    }

    $first = collect($dataset['rows'] ?? [])->first();
    if (!is_array($first)) {
      return ['Data'];
    }

    $headers = array_values(array_filter(array_keys($first), function ($key) {
      return !is_int($key) && !in_array((string) $key, ['cols', 'row_type'], true);
    }));

    if (!empty($headers)) {
      return $headers;
    }

    $cols = is_array($first['cols'] ?? null) ? array_values($first['cols']) : [];
    if (!empty($cols)) {
      $result = [];
      for ($i = 1; $i <= count($cols); $i++) {
        $result[] = 'Kolom ' . $i;
      }
      return $result;
    }

    return ['Data'];
  };

  $fallbackInputHeaders = $inferHeaders($input);
  $fallbackCalcHeaders = $inferHeaders($hitung);
  $tanggalAnalisisLabel = $formatDate($tanggal_analisis ?? null);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Preview Hasil Analisis</title>
  <style>
    body { font-family: Arial, sans-serif; color:#000; margin:0; padding:24px 0 40px; background:#eef2f7; font-size:11px; }
    .toolbar { display:flex; justify-content:flex-end; width:min(210mm, calc(100vw - 32px)); margin:0 auto 16px; position:sticky; top:12px; z-index:10; }
    .toolbar button { border:1px solid #0f172a; background:#0f172a; color:#fff; border-radius:8px; padding:10px 16px; font-size:13px; cursor:pointer; }
    .toolbar button:hover { background:#1e293b; }
    .document { width:min(210mm, calc(100vw - 32px)); min-height:297mm; margin:0 auto; box-sizing:border-box; background:#fff; border:1px solid #dbe1e8; border-radius:14px; padding:28px; box-shadow:0 8px 24px rgba(15, 23, 42, 0.08); }
    .header { margin-bottom:12px; }
    .header-row { display:flex; align-items:center; justify-content:center; gap:10px; }
    .header-row .title-wrap { text-align:left; }
    .title { font-weight:700; font-size:12px; margin:6px 0; }
    .sub { font-size:11px; }
    .meta { font-size:11px; margin:8px 0 12px; }
    .meta table { width:100%; border-collapse:collapse; }
    .meta td { padding:2px 4px; vertical-align:top; }
    .meta td:first-child { white-space:nowrap; }
    .print-table { width:100%; border-collapse:collapse; font-size:11px; margin:6px 0 10px; }
    .print-table th, .print-table td { border:1px solid #000; padding:3px 4px; text-align:center; }
    .flex { display:flex; justify-content:space-between; font-size:11px; margin-top:8px; }
    .signature { display:flex; justify-content:space-between; margin-top:28px; font-size:11px; }
    .signature .block { width:45%; text-align:center; }
    .logo-img { width:32px; display:block; }
    .signature-img { display:block; margin:6px auto 2px; max-height:52px; max-width:160px; object-fit:contain; }
    .sign-space { height:40px; }
    .empty { font-style:italic; color:#444; }
    @media print {
      @page { size:A4 portrait; margin:12mm; }
      body { margin:0; padding:0; background:#fff; }
      .toolbar { display:none; }
      .document { width:auto; min-height:auto; border:0; border-radius:0; box-shadow:none; padding:0; }
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <button type="button" onclick="window.print()">Cetak / Save PDF</button>
  </div>

  <div class="document">
    <div class="header">
      <div class="header-row">
        <img src="{{ asset('images/Logo Kemnaker.png') }}" class="logo-img" alt="Logo">
        <div class="title-wrap">
          <div class="title">HASIL ANALISIS DAN PERHITUNGAN</div>
          <div class="sub">(Dengan UV/VIS Spektrofotometer)</div>
        </div>
      </div>
    </div>

    <div class="meta">
      <table>
        <tr><td>1.</td><td>Parameter / Jenis sampel</td><td>:</td><td>{{ $parameterName !== '' ? $parameterName : '-' }}</td></tr>
        <tr><td>2.</td><td>Jumlah sampel</td><td>:</td><td>{{ $categoryMeta['sample_label'] }} = {{ $sampleCount > 0 ? $sampleCount : '-' }}</td></tr>
        <tr><td>3.</td><td>Keterangan wadah sampel</td><td>:</td><td>Baik</td></tr>
        <tr><td>4.</td><td>Tanggal penerimaan sampel</td><td>:</td><td>{{ $tanggalAnalisisLabel }}</td></tr>
        <tr><td>5.</td><td>Tanggal analisis</td><td>:</td><td>{{ $tanggalAnalisisLabel }}</td></tr>
        <tr><td>6.</td><td>Analis</td><td>:</td><td>{{ $analis ?? '-' }}</td></tr>
      </table>
    </div>

    @if($isDebuPm)
      <table class="print-table">
        <thead>
          <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">No.sampel</th>
            <th rowspan="2">Jam Timbang</th>
            <th colspan="2">Hasil penimbangan (gr)</th>
            <th rowspan="2">Selisih (gr)</th>
            <th rowspan="2">Brt Db-Blk (gr)</th>
            <th rowspan="2">Ket</th>
          </tr>
          <tr>
            <th>Data awal</th>
            <th>Data akhir</th>
          </tr>
        </thead>
        <tbody>
          @php $seq = 1; @endphp
          @forelse($inputRows as $row)
            @php
              $label = $getLabel($row);
              $isAvg = $isAverageLabel($label);
              $noCell = $isBlankLabel($label) ? '' : (string) $seq;
              if (!$isAvg && !$isBlankLabel($label)) {
                $seq++;
              }
            @endphp
            <tr>
              <td>{{ $isAvg ? '' : $noCell }}</td>
              <td>{{ $label }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['jam'], 1) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['awal'], 2) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['akhir'], 3) }}</td>
              <td>{{ $getCell($row, ['selisih'], 4) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['brt'], 5) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['ket'], 6) }}</td>
            </tr>
          @empty
            <tr><td colspan="8" class="empty">Belum ada data hasil baca.</td></tr>
          @endforelse
        </tbody>
      </table>
    @elseif($isHcLike)
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>No.sampel</th>
            <th>Vol. CS2 (ml)</th>
            <th>RT {{ $compoundLabel }} (mnt)</th>
            <th>L.Area {{ $compoundLabel }}</th>
            <th>{{ $compoundLabel }} (mg/ml)</th>
          </tr>
        </thead>
        <tbody>
          @php $seq = 1; @endphp
          @forelse($inputRows as $row)
            @php
              $label = $getLabel($row);
              $isAvg = $isAverageLabel($label);
              $noCell = $isBlankLabel($label) ? '' : (string) $seq;
              if (!$isAvg && !$isBlankLabel($label)) {
                $seq++;
              }
            @endphp
            <tr>
              <td>{{ $isAvg ? '' : $noCell }}</td>
              <td>{{ $label }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['vol_cs2'], 1) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['rt_benzene'], 2) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['area_benzene'], 3) }}</td>
              <td>{{ $getCell($row, ['benzene'], 4) }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="empty">Belum ada data hasil baca.</td></tr>
          @endforelse
        </tbody>
      </table>
    @elseif($isNo2Like || $isEmisiAcidGasLike)
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>No. Sampel</th>
            <th>Waktu Pembacaan</th>
            <th>Vol. Spl {{ $gasLabel }} (ml)</th>
            <th>Hasil pembacaan</th>
            <th>Kand spl (mg/L)</th>
            <th>P.enceran (x)</th>
            <th>knd - bl {{ $gasLabel }} (mg/L)</th>
          </tr>
        </thead>
        <tbody>
          @php $seq = 1; @endphp
          @forelse($inputRows as $row)
            @php
              $label = $getLabel($row);
              $isAvg = $isAverageLabel($label);
              $noCell = $isBlankLabel($label) ? '' : (string) $seq;
              if (!$isAvg && !$isBlankLabel($label)) {
                $seq++;
              }
            @endphp
            <tr>
              <td>{{ $isAvg ? '' : $noCell }}</td>
              <td>{{ $label }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['waktu'], 2) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['volume', 'vol'], 1) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['abs'], 3) }}</td>
              <td>{{ $getCell($row, ['kand-spl', 'kand'], 4) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['encer'], 5) }}</td>
              <td>{{ $isAvg ? '' : $getCell($row, ['knd-bl', 'kndbl'], 6) }}</td>
            </tr>
          @empty
            <tr><td colspan="8" class="empty">Belum ada data hasil baca.</td></tr>
          @endforelse
        </tbody>
      </table>
    @else
      <table class="print-table">
        <thead>
          <tr>
            @foreach($fallbackInputHeaders as $header)
              <th>{{ $header }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @forelse($inputRows as $row)
            <tr>
              @foreach($fallbackInputHeaders as $index => $header)
                <td>{{ $getCell($row, [$header], $index) }}</td>
              @endforeach
            </tr>
          @empty
            <tr><td colspan="{{ count($fallbackInputHeaders) }}" class="empty">Belum ada data hasil baca.</td></tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <div class="meta" style="margin-top:10px;">
      <div>HASIL PERHITUNGAN {{ $categoryMeta['heading_label'] }} {{ $kode ?? '-' }}</div>
      <div>TANGGAL : {{ $tanggalAnalisisLabel }}</div>
    </div>

    <div class="flex">
      <div>Perusahaan: {{ $perusahaan ?? '-' }}</div>
      <div>Verifikasi : Diterima / Dikembalikan</div>
    </div>

    @if($isEmisiAcidGasLike)
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Lokasi</th>
            <th>Kons {{ $emisiGasLabel }} (mg)</th>
            <th>Vol spl (ml)</th>
            <th>FR (lpm)</th>
            <th>Waktu (mnt)</th>
            @if($isHFEmisi)
              <th>Titr HF (ml)</th>
            @endif
            <th>TM (C)</th>
            <th>P (mmHg)</th>
            <th>Kadar {{ $emisiGasLabel }} (mg/m3)</th>
          </tr>
        </thead>
        <tbody>
          @php $seq = 1; @endphp
          @foreach($normalCalcRows as $row)
            <tr>
              <td>{{ $seq++ }}</td>
              <td>{{ $getCell($row, [], 0) }}</td>
              <td>{{ $getCell($row, ['kons'], 1) }}</td>
              <td>{{ $getCell($row, ['vol'], 2) }}</td>
              <td>{{ $getCell($row, ['fr'], 3) }}</td>
              <td>{{ $getCell($row, ['waktu'], 4) }}</td>
              @if($isHFEmisi)
                <td>{{ $getCell($row, ['titr_hf'], 5) }}</td>
                <td>{{ $getCell($row, ['tm'], 6) }}</td>
                <td>{{ $getCell($row, ['p'], 7) }}</td>
                <td>{{ $getCell($row, ['kadar_ugm3'], 8) }}</td>
              @else
                <td>{{ $getCell($row, ['tm'], 5) }}</td>
                <td>{{ $getCell($row, ['p'], 6) }}</td>
                <td>{{ $getCell($row, ['kadar_ugm3'], 7) }}</td>
              @endif
            </tr>
          @endforeach
          @foreach($tailCalcRows as $row)
            <tr>
              <td></td>
              <td>{{ $getCell($row, [], 0) }}</td>
              <td>{{ $getCell($row, ['mdl-kons', 'kons'], 1) }}</td>
              <td>{{ $getCell($row, ['mdl-vol', 'vol'], 2) }}</td>
              <td>{{ $getCell($row, ['fr'], 3) }}</td>
              <td>{{ $getCell($row, ['waktu'], 4) }}</td>
              @if($isHFEmisi)
                <td>{{ $getCell($row, ['titr_hf'], 5) }}</td>
                <td>{{ $getCell($row, ['tm'], 6) }}</td>
                <td>{{ $getCell($row, ['p'], 7) }}</td>
                <td>{{ $getCell($row, ['kadar_ugm3'], 8) }}</td>
              @else
                <td>{{ $getCell($row, ['tm'], 5) }}</td>
                <td>{{ $getCell($row, ['p'], 6) }}</td>
                <td>{{ $getCell($row, ['kadar_ugm3'], 7) }}</td>
              @endif
            </tr>
          @endforeach
          @if($normalCalcRows->isEmpty() && $tailCalcRows->isEmpty())
            <tr><td colspan="{{ $isHFEmisi ? 10 : 9 }}" class="empty">Belum ada data hasil perhitungan.</td></tr>
          @endif
        </tbody>
      </table>
    @elseif($isDebuPm)
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Lokasi</th>
            <th>Berat debu (Gram)</th>
            <th>FR (lpm)</th>
            <th>Waktu (mnt)</th>
            <th>Sk (oC)</th>
            <th>P mmHg</th>
            <th>Kadar Debu (mg/m3)</th>
          </tr>
        </thead>
        <tbody>
          @php $seq = 1; @endphp
          @foreach($normalCalcRows as $row)
            <tr>
              <td>{{ $seq++ }}</td>
              <td>{{ $getCell($row, ['lokasi'], 0) }}</td>
              <td>{{ $getCell($row, ['berat'], 1) }}</td>
              <td>{{ $getCell($row, ['fr'], 2) }}</td>
              <td>{{ $getCell($row, ['waktu'], 3) }}</td>
              <td>{{ $getCell($row, ['sk'], 4) }}</td>
              <td>{{ $getCell($row, ['p'], 5) }}</td>
              <td>{{ $getCell($row, ['kadar'], 6) }}</td>
            </tr>
          @endforeach
          @foreach($tailCalcRows as $row)
            <tr>
              <td></td>
              <td>{{ $getCell($row, [], 0) }}</td>
              <td>{{ $getCell($row, ['berat'], 1) }}</td>
              <td>{{ $getCell($row, ['fr'], 2) }}</td>
              <td>{{ $getCell($row, ['waktu'], 3) }}</td>
              <td>{{ $getCell($row, ['sk'], 4) }}</td>
              <td>{{ $getCell($row, ['p'], 5) }}</td>
              <td>{{ $getCell($row, ['kadar'], 6) }}</td>
            </tr>
          @endforeach
          @if($normalCalcRows->isEmpty() && $tailCalcRows->isEmpty())
            <tr><td colspan="8" class="empty">Belum ada data hasil perhitungan.</td></tr>
          @endif
        </tbody>
      </table>
    @elseif($isHcLike)
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Lokasi</th>
            <th>Konsentrasi</th>
            <th>Volume Spl (ml)</th>
            <th>Waktu (mnt)</th>
            <th>FR (lpm)</th>
            <th>Sk C</th>
            <th>P mmHg</th>
            <th>{{ $isLKCategory ? 'Kadar (ppm)' : 'Kadar ' . $compoundLabel . ' (ppm)' }}</th>
            <th>{{ $isLKCategory ? 'Kadar (ug/m3)' : 'Kadar ' . $compoundLabel . ' (ug/m3)' }}</th>
            <th>{{ $isLKCategory ? 'Kadar (mg/m3)' : 'Kadar ' . $compoundLabel . ' (mg/m3)' }}</th>
          </tr>
        </thead>
        <tbody>
          @php $seq = 1; @endphp
          @foreach($normalCalcRows as $row)
            <tr>
              <td>{{ $seq++ }}</td>
              <td>{{ $getCell($row, [], 0) }}</td>
              <td>{{ $getCell($row, ['kons'], 1) }}</td>
              <td>{{ $getCell($row, ['vol'], 2) }}</td>
              <td>{{ $getCell($row, ['waktu'], 3) }}</td>
              <td>{{ $getCell($row, ['fr'], 4) }}</td>
              <td>{{ $getCell($row, ['sk'], 5) }}</td>
              <td>{{ $getCell($row, ['p'], 6) }}</td>
              <td>{{ $getCell($row, ['kadar_ppm'], 7) }}</td>
              <td>{{ $getCell($row, ['kadar_ugm3'], 8) }}</td>
              <td>{{ $getCell($row, ['kadar_mgm3'], 9) }}</td>
            </tr>
          @endforeach
          @foreach($tailCalcRows as $row)
            <tr>
              <td></td>
              <td>{{ $getCell($row, [], 0) }}</td>
              <td>{{ $getCell($row, ['kons'], 1) }}</td>
              <td>{{ $getCell($row, ['vol'], 2) }}</td>
              <td>{{ $getCell($row, ['waktu'], 3) }}</td>
              <td>{{ $getCell($row, ['fr'], 4) }}</td>
              <td>{{ $getCell($row, ['sk'], 5) }}</td>
              <td>{{ $getCell($row, ['p'], 6) }}</td>
              <td>{{ $getCell($row, ['kadar_ppm'], 7) }}</td>
              <td>{{ $getCell($row, ['kadar_ugm3'], 8) }}</td>
              <td>{{ $getCell($row, ['kadar_mgm3'], 9) }}</td>
            </tr>
          @endforeach
          @if($normalCalcRows->isEmpty() && $tailCalcRows->isEmpty())
            <tr><td colspan="11" class="empty">Belum ada data hasil perhitungan.</td></tr>
          @endif
        </tbody>
      </table>
    @elseif($isNo2Like)
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Lokasi</th>
            <th>Konsentrasi</th>
            <th>Volume Spl (ml)</th>
            <th>Waktu (mnt)</th>
            <th>FR (lpm)</th>
            <th>Sk C</th>
            <th>P mmHg</th>
            <th>Kadar (ppm)</th>
            <th>Kadar (ug/m3)</th>
            <th>Kadar (mg/m3)</th>
          </tr>
        </thead>
        <tbody>
          @php $seq = 1; @endphp
          @foreach($normalCalcRows as $row)
            <tr>
              <td>{{ $seq++ }}</td>
              <td>{{ $getCell($row, [], 0) }}</td>
              <td>{{ $getCell($row, ['kons'], 1) }}</td>
              <td>{{ $getCell($row, ['vol'], 2) }}</td>
              <td>{{ $getCell($row, ['waktu'], 3) }}</td>
              <td>{{ $getCell($row, ['fr'], 4) }}</td>
              <td>{{ $getCell($row, ['sk'], 5) }}</td>
              <td>{{ $getCell($row, ['pm', 'p'], 6) }}</td>
              <td>{{ $getCell($row, ['kadar_ppm'], 7) }}</td>
              <td>{{ $getCell($row, ['kadar_ugm3'], 8) }}</td>
              <td>{{ $getCell($row, ['kadar_mgm3'], 9) }}</td>
            </tr>
          @endforeach
          @foreach($tailCalcRows as $row)
            <tr>
              <td></td>
              <td>{{ $getCell($row, [], 0) }}</td>
              <td>{{ $getCell($row, ['kons'], 1) }}</td>
              <td>{{ $getCell($row, ['vol'], 2) }}</td>
              <td>{{ $getCell($row, ['waktu'], 3) }}</td>
              <td>{{ $getCell($row, ['fr'], 4) }}</td>
              <td>{{ $getCell($row, ['sk'], 5) }}</td>
              <td>{{ $getCell($row, ['pm', 'p'], 6) }}</td>
              <td>{{ $getCell($row, ['kadar_ppm'], 7) }}</td>
              <td>{{ $getCell($row, ['kadar_ugm3'], 8) }}</td>
              <td>{{ $getCell($row, ['kadar_mgm3'], 9) }}</td>
            </tr>
          @endforeach
          @if($normalCalcRows->isEmpty() && $tailCalcRows->isEmpty())
            <tr><td colspan="11" class="empty">Belum ada data hasil perhitungan.</td></tr>
          @endif
        </tbody>
      </table>
    @else
      <table class="print-table">
        <thead>
          <tr>
            @foreach($fallbackCalcHeaders as $header)
              <th>{{ $header }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @forelse($calcRows as $row)
            <tr>
              @foreach($fallbackCalcHeaders as $index => $header)
                <td>{{ $getCell($row, [$header], $index) }}</td>
              @endforeach
            </tr>
          @empty
            <tr><td colspan="{{ count($fallbackCalcHeaders) }}" class="empty">Belum ada data hasil perhitungan.</td></tr>
          @endforelse
        </tbody>
      </table>
    @endif

    <div class="signature">
      <div class="block">
        <div>Manajer Teknis,</div>
        <div class="sign-space"></div>
        <div>&nbsp;</div>
      </div>
      <div class="block">
        <div>Surabaya, {{ $tanggalAnalisisLabel }}</div>
        <div>Analis Hitung,</div>
        @if(!empty($analis_signature_url))
          <img src="{{ $analis_signature_url }}" alt="TTD Analis Hitung" class="signature-img">
        @else
          <div class="sign-space"></div>
        @endif
        <div>( {{ $analis ?? '-' }} )</div>
      </div>
    </div>
  </div>
</body>
</html>
