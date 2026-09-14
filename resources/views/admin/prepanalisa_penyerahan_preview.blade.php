<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Formulir Penyerahan Sampel Faktor Kimia</title>
  <style>
    body { font-family: Arial, sans-serif; color:#000; margin:36px; }
    .center { text-align:center; }
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
    .doc-title { text-align:center; font-weight:700; margin:12px 0; text-decoration:underline; font-size:15px; }
    .meta { margin:14px 0 10px; font-size:11px; }
    .meta table { width:100%; border-collapse:collapse; }
    .meta td { padding:2px 4px; vertical-align:top; }
    .meta td:first-child { width:180px; white-space:nowrap; }
    .form-table { width:100%; border-collapse:collapse; font-size:12px; }
    .form-table th, .form-table td { border:1px solid #000; padding:4px; }
    .form-table th { text-align:center; }
    .box-check {
      width: 14px;
      height: 14px;
      pointer-events: none;
      accent-color: #198754;
    }
    .note { margin-top:12px; font-size:12px; }
    .sign { margin-top:26px; display:flex; justify-content:space-between; font-size:12px; }
    .sign .block { width:45%; text-align:center; }
    .receivers { display:flex; flex-wrap:wrap; justify-content:center; gap:14px; margin-top:8px; }
    .receiver-item { width:170px; text-align:center; }
    .receiver-name { margin-top:4px; }
    .signature-img { display:block; margin:8px auto 4px; max-height:58px; max-width:180px; object-fit:contain; }
    .sign-space { height:58px; }
    .foot { margin-top:28px; font-size:11px; display:flex; justify-content:space-between; }
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
  <div class="doc-title">FORMULIR PENYERAHAN SAMPEL FAKTOR KIMIA</div>
  <div class="meta">
    <table>
      <tr><td>Kode Customer</td><td>:</td><td>{{ $kodeCustomer }}</td></tr>
      <tr><td>Tanggal Sampling</td><td>:</td><td>{{ $tanggalSampling }}</td></tr>
      <tr><td>Tanggal Penerimaan</td><td>:</td><td>{{ $tanggalPenerimaan }}</td></tr>
      <tr><td>Tanggal Penyerahan</td><td>:</td><td>{{ $tanggalPenyerahan }}</td></tr>
    </table>
  </div>
  <table class="form-table">
    <thead>
      <tr>
        <th style="width:5%;">No.</th>
        <th>Parameter</th>
        <th style="width:12%;">Jumlah Sampel</th>
        <th style="width:22%;">Kode Sampel</th>
        <th colspan="2" style="width:18%;">Kelengkapan Penyerahan Sampel</th>
      </tr>
      <tr>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th style="width:9%;">Admin</th>
        <th style="width:9%;">Analis</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $idx => $row)
        <tr>
          <td class="center">{{ $idx + 1 }}</td>
          <td>{{ $row['parameter'] }}</td>
          <td class="center">{{ $row['jumlah'] }}</td>
          <td>{{ $row['kode'] }}</td>
          <td class="center">
            <input type="checkbox" class="box-check" @checked(!empty($row['admin_checked']))>
          </td>
          <td class="center">
            <input type="checkbox" class="box-check" @checked(!empty($row['analis_checked']))>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="center">-</td>
        </tr>
      @endforelse
    </tbody>
  </table>
  <div class="note">Catatan Tambahan :</div>
  <div class="sign">
    <div class="block">
      <div>Yang Menerima,</div>
      <div>Penyelia / Analis</div>
      @php($penerimaList = collect($analisPenerima ?? []))
      @if($penerimaList->isNotEmpty())
        <div class="receivers">
          @foreach($penerimaList as $penerima)
            <div class="receiver-item">
              @if(!empty($penerima['ttd_url']))
                <img src="{{ $penerima['ttd_url'] }}" alt="TTD {{ $penerima['nama'] ?? 'Analis' }}" class="signature-img">
              @else
                <div class="sign-space"></div>
              @endif
              <div class="receiver-name">( {{ $penerima['nama'] ?? '-' }} )</div>
            </div>
          @endforeach
        </div>
      @else
        <div class="sign-space"></div>
        <div>( ........................................ )</div>
      @endif
    </div>
    <div class="block">
      <div>Yang Menyerahkan,</div>
      <div>Petugas Administrasi</div>
      @if(!empty($petugasAdministrasiTtdUrl))
        <img src="{{ $petugasAdministrasiTtdUrl }}" alt="TTD Petugas Administrasi" class="signature-img">
      @else
        <div class="sign-space"></div>
      @endif
      <div>( {{ $petugasAdministrasi }} )</div>
    </div>
  </div>
  <div class="foot">
    <div>Tgl. terbit: 24 Desember 2024</div>
    <div>No.: F/7.4.2/BK3-SBY</div>
  </div>
</body>
</html>
