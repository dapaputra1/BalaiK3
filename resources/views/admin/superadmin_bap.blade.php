@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
  $serviceCategories = \App\Models\ServiceCategory::pluck('name', 'id');
  $serviceCategoryShortByName = \App\Models\ServiceCategory::select('name', 'short_code')
    ->get()
    ->mapWithKeys(function ($cat) {
      $key = strtolower(trim((string) $cat->name));
      return [$key => $cat->short_code ?: $cat->name];
    });
  $serviceParameters = \App\Models\ServiceParameter::select('name', 'service_category_id')->get();
  $mapJenisPengukuran = function ($paramName) use ($serviceCategories, $serviceParameters) {
    $needle = strtolower((string)
     $paramName);
    $match = $serviceParameters->first(function ($row) use ($needle) {
      return strpos(strtolower($row->name), $needle) !== false;
    });
    if (!$match) {
      return '-';
    }
    return $serviceCategories[$match->service_category_id] ?? '-';
  };
  $badgeStatus = fn($status) => match (strtolower($status)) {
    'belum mengirim bap' => 'text-bg-secondary',
    'menunggu persetujuan' => 'text-bg-warning',
    'sudah disetujui' => 'text-bg-success',
    default => 'text-bg-secondary',
  };
  $kategoriSingkat = function ($label) use ($serviceCategoryShortByName) {
    $text = strtolower(trim((string) $label));
    if ($text === '' || $text === '-') return '-';
    return $serviceCategoryShortByName[$text] ?? ($label ?: '-');
  };
@endphp

<style>
  .btn-primary {
    background-color: #15406A !important;
    border-color: #15406A !important;
  }
  .btn-primary:hover,
  .btn-primary:focus {
    background-color: #0f2f53 !important;
    border-color: #0f2f53 !important;
  }
  .text-bg-primary {
    background-color: #15406A !important;
    color: #fff !important;
  }
  .btn-outline-primary {
    color: #15406A !important;
    border-color: #15406A !important;
  }
  .btn-outline-primary:hover,
  .btn-outline-primary:focus {
    background-color: #15406A !important;
    color: #fff !important;
    border-color: #15406A !important;
  }
  .btn-outline-primary.active,
  .btn-outline-primary:active {
    background-color: #15406A !important;
    color: #fff !important;
    border-color: #15406A !important;
  }
  .table td {
    vertical-align: top;
  }
  .doc-block {
    border: 1px dashed #c7dcf7;
    border-radius: 8px;
    padding: 8px;
    background: #f8fbff;
  }
  .doc-label {
    font-size: 12px;
    font-weight: 600;
    color: #123A63;
    margin-bottom: 6px;
  }
  .doc-row {
    min-height: 28px;
    display: flex;
    align-items: center;
  }
  .bap-arrival-time {
    font-size: 11px;
    line-height: 1.2;
    color: #6c757d;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
  }
  .bap-arrival-time i {
    font-size: 11px;
    margin-right: 4px;
  }
  .bap-header-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 6px;
    position: relative;
    padding-right: 220px;
  }
  .bap-order-code {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
  }
  .bap-company-name {
    font-size: 13px;
    font-weight: 400;
    line-height: 1.2;
  }
  .bap-accordion-trigger {
    align-items: flex-start;
  }
  .bap-accordion-trigger::after {
    margin-top: 2px;
  }
  .bap-meta-row {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    position: absolute;
    top: 0;
    right: 34px;
  }
  .bap-status-badge {
    font-size: 10px;
    line-height: 1.2;
    padding: 0.35rem 0.55rem;
  }
  [data-filter-status] {
    font-size: 13px;
    font-weight: 400;
  }
  [data-filter-status] .badge {
    font-size: 13px;
    line-height: 1;
    font-weight: 400;
  }
  @media (max-width: 575.98px) {
    .bap-header-content {
      padding-right: 0;
    }
    .bap-meta-row {
      position: static;
      align-items: flex-start;
      width: auto;
    }
  }
  #bapAccordion .accordion-button {
    background-color: #15406a;
    color: #fff;
  }
  #bapAccordion .accordion-button:not(.collapsed) {
    background-color: #15406a;
    color: #fff;
    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
  }
  #bapAccordion .accordion-button::after {
    filter: brightness(0) invert(1);
  }
  #bapAccordion .accordion-button .text-muted,
  #bapAccordion .accordion-button .bap-arrival-time,
  #bapAccordion .accordion-button .bap-arrival-time span,
  #bapAccordion .accordion-button .bap-arrival-time i {
    color: #fff !important;
  }
  .workflow-search-wrap {
    background: #eef2f7;
    border: 1px solid #d7e1ee;
    border-radius: 16px;
    padding: 10px;
  }
  .workflow-search-wrap .input-group-text {
    background: #fff;
    border: 1px solid #b7c8de;
    border-right: 0;
    color: #6f88a8;
    border-radius: 14px 0 0 14px;
    padding-left: 14px;
    padding-right: 10px;
  }
  .workflow-search-wrap .form-control {
    border: 1px solid #b7c8de;
    border-left: 0;
    border-radius: 0 14px 14px 0;
    min-height: 42px;
    font-size: 13px;
    color: #345173;
  }
  .workflow-search-wrap .form-control:focus {
    box-shadow: none;
    border-color: #a7bed9;
  }
  .workflow-search-wrap .input-group:focus-within .input-group-text {
    border-color: #a7bed9;
  }
  .workflow-search-wrap .btn-reset-search {
    border: 1px solid #15406A;
    color: #15406A;
    background: #fff;
    border-radius: 14px;
    min-height: 42px;
    font-weight: 600;
    font-size: 13px;
  }
  .workflow-search-wrap .btn-reset-search:hover,
  .workflow-search-wrap .btn-reset-search:focus {
    background: #15406A;
    color: #fff;
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Berita Acara Pengujian',
  'subtitle' => 'Ringkasan hasil pengujian untuk pembuatan BAP per parameter dan lokasi.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body">
    <div class="workflow-search-wrap">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Cari berdasarkan kode pesanan" data-search-kode>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Cari berdasarkan pelanggan" data-search-perusahaan>
          </div>
        </div>
        <div class="col-12 col-md-2">
          <button type="button" class="btn btn-reset-search w-100" data-search-reset><i class="bi bi-arrow-clockwise me-1"></i>Reset</button>
        </div>
      </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3">
      <button type="button" class="btn btn-outline-primary btn-sm active" data-filter-status="belum mengirim bap">
        Belum Terkirim
        <span class="badge text-bg-danger ms-1 d-none" data-status-count="belum mengirim bap">0</span>
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm" data-filter-status="menunggu persetujuan">
        Menunggu Persetujuan
        <span class="badge text-bg-danger ms-1 d-none" data-status-count="menunggu persetujuan">0</span>
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm" data-filter-status="sudah disetujui">
        Sudah di ACC User
        <span class="badge text-bg-danger ms-1 d-none" data-approved-unread-count>0</span>
      </button>
    </div>
  </div>
</div>

<div class="accordion" id="bapAccordion">
  @forelse($orders as $order)
  @php
    $routePrefix = auth()->user()?->role === 'pcu'
      ? 'pcu'
      : (auth()->user()?->role === 'admin' ? 'admin' : 'superadmin');
    $permohonanId = $order['permohonan_id'] ?? null;
    $ketuaTimNama = $order['ketua_tim_nama'] ?? '-';
    $ketuaTimTtd = $order['ketua_tim_ttd'] ?? '';
    $bapStatusLabel = $order['bap_status_label'] ?? 'Belum mengirim BAP';
    $bapStatusKey = strtolower(trim($bapStatusLabel));
    $headingId = 'bapHeading' . $permohonanId;
    $collapseId = 'bapCollapse' . $permohonanId;
    $masukAtUnix = (int) ($order['masuk_at_unix'] ?? 0);
  @endphp
  <div
    class="accordion-item border-0 shadow-sm rounded-4 mb-3 overflow-hidden"
    data-card
    data-kode="{{ strtolower($order['kode']) }}"
    data-perusahaan="{{ strtolower($order['perusahaan']) }}"
    data-perusahaan-nama="{{ $order['perusahaan'] }}"
    data-order-kode="{{ $order['kode'] }}"
    data-tanggal-pengujian="{{ $order['tanggal_pengujian'] ?? '-' }}"
    data-tanggal-pengujian-mulai="{{ $order['tanggal_pengujian_mulai'] ?? '-' }}"
    data-tanggal-pengujian-selesai="{{ $order['tanggal_pengujian_selesai'] ?? '-' }}"
    data-lokasi="{{ $order['lokasi'] ?? '-' }}"
    data-penanggung="{{ $order['penanggung_jawab'] ?? '-' }}"
    data-pcu='@json($order['pcu'] ?? [])'
    data-alamat-perusahaan="{{ $order['alamat_perusahaan'] ?? '-' }}"
    data-ketua-tim-nama="{{ $ketuaTimNama }}"
    data-ketua-tim-ttd="{{ $ketuaTimTtd }}"
    data-penanggung-ttd="{{ $order['penanggung_jawab_ttd'] ?? '' }}"
    data-parameter='@json($order['parameter'] ?? [])'
    data-parameter-order='@json($order['parameter_order'] ?? [])'
    data-parameter-pengujian='@json($order['parameter'] ?? [])'
    data-lokasi-rows="{{ e(json_encode($order['lokasi_rows'] ?? [])) }}"
    data-jenis-perusahaan="{{ $order['jenis_perusahaan'] ?? '-' }}"
    data-save-url="{{ $permohonanId ? route($routePrefix . '.bap.draft', $permohonanId) : '' }}"
    data-submit-url="{{ $permohonanId ? route($routePrefix . '.bap.submit', $permohonanId) : '' }}"
    data-mark-viewed-url="{{ $permohonanId ? route($routePrefix . '.bap.mark-viewed', $permohonanId) : '' }}"
    data-bap-status="{{ $bapStatusKey }}"
    data-permohonan-id="{{ $permohonanId ?? 0 }}"
    data-admin-unread="{{ !empty($order['admin_unread']) ? '1' : '0' }}"
  >
    <h2 class="accordion-header" id="{{ $headingId }}">
      <button
        class="accordion-button collapsed fw-semibold bap-accordion-trigger"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#{{ $collapseId }}"
        aria-expanded="false"
        aria-controls="{{ $collapseId }}"
      >
        <div class="bap-header-content pe-3">
          <div>
            <div class="bap-order-code">{{ $order['kode'] }}</div>
            <div class="bap-company-name">{{ $order['perusahaan'] }}</div>
          </div>
          <div class="bap-meta-row">
            <div class="bap-arrival-time">
              <i class="bi bi-clock"></i>
              <span data-relative-time data-time-unix="{{ $masukAtUnix ?: '' }}">-</span>
            </div>
            <span class="badge {{ $badgeStatus($bapStatusLabel) }} bap-status-badge">{{ $bapStatusLabel }}</span>
          </div>
        </div>
      </button>
    </h2>
    <div
      id="{{ $collapseId }}"
      class="accordion-collapse collapse"
      aria-labelledby="{{ $headingId }}"
      data-bs-parent="#bapAccordion"
    >
      <div class="accordion-body bg-white">
        <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
          <div class="small text-muted">Ringkasan dokumen dan parameter BAP.</div>
        </div>

        <div class="table-responsive mb-3">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:6%;">No</th>
                <th style="width:14%;">Lokasi</th>
                <th style="width:22%;">Dokumen</th>
                <th style="width:22%;">Parameter</th>
                <th style="width:10%;">Metode</th>
                <th style="width:10%;">Sesuai</th>
                <th style="width:16%;">Catatan</th>
              </tr>
            </thead>
            <tbody>
              @php $lokasiRows = collect($order['lokasi_rows'] ?? []); @endphp
              @forelse($lokasiRows as $idx => $row)
                @php
                  $dokumenList = collect($row['dokumen_list'] ?? []);
                  $docCount = max(1, $dokumenList->count());
                @endphp
                @if($dokumenList->isEmpty())
                  <tr>
                    <td class="text-muted">{{ $idx + 1 }}</td>
                    <td class="small text-muted">{{ $row['lokasi'] ?? '-' }}</td>
                    <td><span class="text-muted small">Belum ada dokumen.</span></td>
                    <td><span class="text-muted small">Belum ada parameter.</span></td>
                    <td><span class="text-muted small">-</span></td>
                    <td><span class="text-muted small">-</span></td>
                    <td><span class="text-muted small">-</span></td>
                  </tr>
                @else
                  @foreach($dokumenList as $docIndex => $doc)
                    <tr>
                      @if($docIndex === 0)
                        <td class="text-muted" rowspan="{{ $docCount }}">{{ $idx + 1 }}</td>
                        <td class="small text-muted" rowspan="{{ $docCount }}">{{ $row['lokasi'] ?? '-' }}</td>
                      @endif
                      <td>
                        <div class="doc-block">
                          <div class="doc-label">{{ $doc['nama'] ?? 'Dokumen' }}</div>
                          <div class="d-flex flex-column gap-2 small">
                            @forelse(($doc['files'] ?? []) as $file)
                              <div class="doc-row">
                                @if(!empty($file['url']))
                                  <a href="{{ $file['url'] }}" target="_blank" rel="noopener">
                                    {{ $file['name'] ?? 'File' }}
                                  </a>
                                @else
                                  {{ $file['name'] ?? 'File' }}
                                @endif
                              </div>
                            @empty
                              <div class="doc-row text-muted">Belum ada file.</div>
                            @endforelse
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="doc-block">
                          <div class="doc-label">{{ $doc['nama'] ?? 'Dokumen' }}</div>
                          <div class="d-flex flex-column gap-2 small">
                            @forelse($doc['parameter'] ?? [] as $param)
                              <div class="doc-row">{{ $kategoriSingkat($param['kategori'] ?? '-') }} - {{ $param['nama'] ?? '-' }}</div>
                            @empty
                              <div class="doc-row text-muted">Belum ada parameter.</div>
                            @endforelse
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="doc-block">
                          <div class="doc-label">{{ $doc['nama'] ?? 'Dokumen' }}</div>
                          <div class="d-flex flex-column gap-2 small">
                            @forelse($doc['parameter'] ?? [] as $param)
                              <div class="doc-row">{{ !empty($param['is_direct']) ? 'Direct' : 'Indirect' }}</div>
                            @empty
                              <div class="doc-row text-muted">-</div>
                            @endforelse
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="doc-block">
                          <div class="doc-label">{{ $doc['nama'] ?? 'Dokumen' }}</div>
                          <div class="d-flex flex-column gap-2 small">
                            @forelse($doc['parameter'] ?? [] as $param)
                              <div class="doc-row">
                                <span class="badge {{ !empty($param['sesuai']) ? 'text-bg-success' : 'text-bg-warning' }}">
                                  {{ !empty($param['sesuai']) ? 'Sesuai' : 'Tidak' }}
                                </span>
                              </div>
                            @empty
                              <div class="doc-row text-muted">-</div>
                            @endforelse
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="doc-block">
                          <div class="doc-label">{{ $doc['nama'] ?? 'Dokumen' }}</div>
                          <div class="d-flex flex-column gap-2 small">
                            @forelse($doc['parameter'] ?? [] as $param)
                              <div class="doc-row">
                                <input
                                  type="text"
                                  class="form-control form-control-sm"
                                  value="{{ $param['catatan'] ?? '' }}"
                                  placeholder="Catatan"
                                  data-catatan-input
                                  data-param-id="{{ $param['id'] ?? '' }}"
                                >
                              </div>
                            @empty
                              <div class="doc-row text-muted">-</div>
                            @endforelse
                          </div>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                @endif
              @empty
                <tr>
                  <td class="text-muted">1</td>
                  <td class="small text-muted">-</td>
                  <td class="text-muted">Belum ada dokumen.</td>
                  <td class="text-muted">-</td>
                  <td class="text-muted">-</td>
                  <td class="text-muted">-</td>
                  <td class="text-muted">-</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div class="d-flex gap-2">
            <div class="dropdown">
              <button class="btn btn-outline-primary btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download"></i> Unduh Berkas
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="#" role="button" data-bap-download>
                    <i class="bi bi-file-earmark-text"></i> Unduh Form BAP
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="#" role="button" data-rincian-sampel-download>
                    <i class="bi bi-file-earmark-spreadsheet"></i> Unduh Form Rincian Pengambilan Sampel
                  </a>
                </li>
                <li>
                  <a class="dropdown-item d-flex align-items-center gap-2" href="#" role="button" data-penambahan-pengurangan-download>
                    <i class="bi bi-file-earmark-plus"></i> Unduh Form Penambahan/Pengurangan Pengujian
                  </a>
                </li>
              </ul>
            </div>
              <!-- <button
                class="btn btn-outline-primary btn-sm"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#detailPermohonanModal"
                data-kode="{{ $order['kode'] ?? '-' }}"
                data-pelanggan="{{ $order['perusahaan'] ?? '-' }}"
                data-jenis="{{ collect($order['layanan_penawaran'] ?? [])->pluck('nama')->filter()->take(2)->implode(', ') ?: '-' }}"
                data-tahap="{{ $order['tahap'] ?? '-' }}"
                data-progress="{{ (int) ($order['progress'] ?? 0) }}"
                data-status="{{ $order['status_label'] ?? '-' }}"
                data-tanggal="{{ $order['tanggal_permohonan'] ?? '-' }}"
                data-perusahaan="{{ $order['perusahaan'] ?? '-' }}"
                data-penanggung="{{ $order['penanggung_jawab'] ?? '-' }}"
                data-email="{{ $order['email_perusahaan'] ?? '-' }}"
                data-telepon="{{ $order['telepon_perusahaan'] ?? '-' }}"
                data-alamat="{{ $order['alamat_perusahaan'] ?? '-' }}"
                data-jenis-perusahaan="{{ $order['jenis_perusahaan'] ?? '-' }}"
                data-provinsi="{{ $order['provinsi_perusahaan'] ?? '-' }}"
                data-kota="{{ $order['kota_perusahaan'] ?? '-' }}"
                data-penandatangan-sama="{{ ($order['penandatangan_sama'] ?? false) ? '1' : '0' }}"
                data-nama-penandatangan="{{ $order['penandatangan_nama'] ?? '-' }}"
                data-jabatan-penandatangan="{{ $order['penandatangan_jabatan'] ?? '-' }}"
                data-layanan='@json($order['layanan'] ?? [])'
                data-layanan-penawaran='@json($order['layanan_penawaran'] ?? [])'
                data-layanan-pengujian='@json($order['layanan_pengujian'] ?? [])'
                data-has-perubahan-pengujian="{{ !empty($order['has_perubahan_pengujian']) ? '1' : '0' }}"
                data-subtotal-penawaran="{{ $order['subtotal_penawaran'] ?? 0 }}"
                data-subtotal-pengujian="{{ $order['subtotal_pengujian'] ?? 0 }}"
                data-subtotal="{{ $order['subtotal'] ?? 0 }}"
                data-total="{{ $order['total'] ?? 0 }}"
                data-dokumen='@json($order['dokumen'] ?? [])'
                data-ketua-pcu='@json($order['ketua_pcu'] ?? [])'
                data-pcu='@json($order['pcu'] ?? [])'
                data-analis='@json($order['analis'] ?? [])'
              >
                Detail Permohonan
              </button> -->
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-action-save>Simpan Draft</button>
            <button class="btn btn-primary btn-sm" type="button" data-action-submit>Kirim ke Pemohon</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="text-center text-muted py-4">Belum ada berita acara pengujian.</div>
  @endforelse
</div>

<div class="text-center text-muted d-none mt-3" data-search-empty>Tidak ada order yang cocok.</div>

<!-- Modal Detail Permohonan -->
<div class="modal fade" id="detailPermohonanModal" tabindex="-1" aria-labelledby="detailPermohonanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailPermohonanModalLabel">Detail Permohonan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="border rounded-4 p-4 mb-4 shadow-sm bg-white">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Id Permohonan</div>
              <div class="h6 mb-0" id="detailKode">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Tanggal Permohonan</div>
              <div class="h6 mb-0" id="detailTanggal">-</div>
            </div>
            <div class="col-12">
              <div class="fw-semibold text-muted small mb-1">Progress Keseluruhan</div>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height: 10px;">
                  <div class="progress-bar" id="detailProgressBar" style="width: 0%"></div>
                </div>
                <span class="small fw-semibold text-primary" id="detailProgressText">0%</span>
              </div>
            </div>
            <div class="col-12"><hr class="my-2"></div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Nama Perusahaan</div>
              <div class="h6 mb-0" id="detailPerusahaan">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Nama Penanggung Jawab</div>
              <div class="h6 mb-0" id="detailPenanggung">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Email Perusahaan</div>
              <div class="h6 mb-0" id="detailEmail">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Nomor Telepon (WA)</div>
              <div class="h6 mb-0" id="detailTelepon">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Jenis Perusahaan</div>
              <div class="h6 mb-0" id="detailJenisPerusahaan">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Provinsi</div>
              <div class="h6 mb-0" id="detailProvinsi">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Kota</div>
              <div class="h6 mb-0" id="detailKota">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Nama Penandatangan</div>
              <div class="h6 mb-0" id="detailNamaPenandatangan">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Jabatan Penandatangan</div>
              <div class="h6 mb-0" id="detailJabatanPenandatangan">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Penandatangan = Penanggung Jawab</div>
              <div class="h6 mb-0" id="detailPenandatanganSama">-</div>
            </div>
            <div class="col-12">
              <div class="fw-semibold text-muted small">Alamat Perusahaan</div>
              <div class="h6 mb-0" id="detailAlamat">-</div>
            </div>
          </div>
        </div>

        <div class="border rounded-4 p-4 shadow-sm bg-white">
          <h6 class="fw-semibold mb-3">Rincian Layanan</h6>
          <div id="detailServicesActiveWrap">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:50px;">No</th>
                    <th>Layanan</th>
                    <th>Kategori</th>
                    <th style="width:80px;">Qty</th>
                    <th class="text-end" style="width:160px;">Harga Satuan</th>
                    <th class="text-end" style="width:160px;">Subtotal</th>
                  </tr>
                </thead>
                <tbody id="detailServicesBody">
                  <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div id="detailServicesCompareWrap" class="d-none">
            <div class="row g-3">
              <div class="col-12 col-lg-6">
                <div class="small fw-semibold text-muted mb-2">Parameter Saat Penawaran</div>
                <div class="table-responsive">
                  <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width:50px;">No</th>
                        <th>Parameter</th>
                        <th>Kategori</th>
                        <th style="width:80px;">Qty</th>
                      </tr>
                    </thead>
                    <tbody id="detailServicesPenawaranBody">
                      <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="small fw-semibold text-muted mb-2">Parameter Setelah Pengujian</div>
                <div class="table-responsive">
                  <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width:50px;">No</th>
                        <th>Parameter</th>
                        <th>Kategori</th>
                        <th style="width:80px;">Qty</th>
                      </tr>
                    </thead>
                    <tbody id="detailServicesPengujianBody">
                      <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-3 d-none" id="detailSubtotalCompareRow">
            <div class="col-12 col-lg-6">
              <div class="border rounded-3 px-3 py-2">
                <div class="small text-muted">Subtotal Penawaran</div>
                <div class="fw-semibold text-end" id="detailSubtotalPenawaran">Rp 0</div>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="border rounded-3 px-3 py-2">
                <div class="small text-muted">Subtotal Setelah Pengujian</div>
                <div class="fw-semibold text-end" id="detailSubtotalPengujian">Rp 0</div>
              </div>
            </div>
          </div>
          <div class="row justify-content-end mt-3">
            <div class="col-md-6 col-lg-5">
              <dl class="row mb-0">
                <dt class="col-6 fw-semibold">Total:</dt>
                <dd class="col-6 text-end fw-semibold text-primary" id="detailTotal">Rp 0</dd>
              </dl>
            </div>
          </div>
        </div>

        <div class="border rounded-4 p-4 shadow-sm bg-white mt-4">
          <h6 class="fw-semibold mb-3">Dokumen</h6>
          <div id="detailDocuments"></div>
        </div>

        <div class="border rounded-4 p-4 shadow-sm bg-white mt-4">
          <h6 class="fw-semibold mb-3">Petugas yang Menangani</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="text-muted small fw-semibold">Ketua PCU</div>
              <ul class="list-unstyled mb-0" id="detailKetuaPcu">
                <li class="text-muted small">Belum ada ketua PCU.</li>
              </ul>
            </div>
            <div class="col-md-4">
              <div class="text-muted small fw-semibold">PCU</div>
              <ul class="list-unstyled mb-0" id="detailPcu">
                <li class="text-muted small">Belum ada PCU.</li>
              </ul>
            </div>
            <div class="col-md-4">
              <div class="text-muted small fw-semibold">Analis</div>
              <ul class="list-unstyled mb-0" id="detailAnalis">
                <li class="text-muted small">Belum ada analis.</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@php
  $categoryIndex = collect($serviceCategories)->map(function ($name, $id) {
      return [
          'id' => $id,
          'name' => $name,
      ];
  })->values();
@endphp
<script>
(() => {
  const parameterIndex = @json(
    $serviceParameters->map(function ($row) use ($serviceCategories) {
      return [
        'name' => $row->name,
        'category' => $serviceCategories[$row->service_category_id] ?? '-',
      ];
    })->values()
  );

  const categoryIndex = @json($categoryIndex);

  const normalizeCategoryName = (value) => {
    const text = String(value || '').toLowerCase().trim();
    return text.replace(/\s+/g, ' ');
  };

  const getJenisPengukuran = (paramName, fallbackCategory = '') => {
    const fallback = normalizeCategoryName(fallbackCategory);
    if (fallback) {
      const exact = categoryIndex.find((item) => normalizeCategoryName(item.name) === fallback);
      return exact?.name || fallbackCategory;
    }
    const needle = String(paramName || '').toLowerCase();
    if (!needle) return '-';
    const match = parameterIndex.find((item) => {
      const name = String(item.name || '').toLowerCase();
      return name.includes(needle) || needle.includes(name);
    });
    return match?.category || '-';
  };


  const kodeInput = document.querySelector('[data-search-kode]');
  const perusahaanInput = document.querySelector('[data-search-perusahaan]');
  const resetBtn = document.querySelector('[data-search-reset]');
  const statusButtons = document.querySelectorAll('[data-filter-status]');
  const statusCountEls = document.querySelectorAll('[data-status-count]');
  const approvedUnreadCountEls = document.querySelectorAll('[data-approved-unread-count]');
  const cards = document.querySelectorAll('[data-card]');
  const cardsContainer = cards.length ? cards[0].parentElement : null;
  const emptyState = document.querySelector('[data-search-empty]');
  let activeStatus = 'belum mengirim bap';
  const viewedPermohonanIds = new Set();

  const formatRelativeTime = (unixTime) => {
    const ts = Number.parseInt(String(unixTime || ''), 10);
    if (Number.isNaN(ts) || ts <= 0) return '-';
    const seconds = Math.max(0, Math.floor(Date.now() / 1000) - ts);
    if (seconds < 60) return 'baru saja';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} mnt yang lalu`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} jam yang lalu`;
    return `${Math.floor(seconds / 86400)} hari yang lalu`;
  };

  const refreshRelativeTimes = () => {
    document.querySelectorAll('[data-relative-time]').forEach((node) => {
      const ts = node.getAttribute('data-time-unix');
      node.textContent = formatRelativeTime(ts);
    });
  };

  cards.forEach((card, index) => {
    card.setAttribute('data-initial-order', String(index));
  });

  const detailModal = document.getElementById('detailPermohonanModal');
  if (detailModal) {
    detailModal.addEventListener('show.bs.modal', (event) => {
      const trigger = event.relatedTarget;
      if (!trigger) return;
      const sourceCard = trigger.closest('[data-card]');

      const data = {
        kode: trigger.getAttribute('data-kode'),
        pelanggan: trigger.getAttribute('data-pelanggan'),
        jenis_pengujian: trigger.getAttribute('data-jenis'),
        tahap: trigger.getAttribute('data-tahap'),
        progress: trigger.getAttribute('data-progress'),
        status: trigger.getAttribute('data-status'),
        tanggal: trigger.getAttribute('data-tanggal'),
        perusahaan: trigger.getAttribute('data-perusahaan'),
        penanggung: trigger.getAttribute('data-penanggung'),
        email: trigger.getAttribute('data-email'),
        telepon: trigger.getAttribute('data-telepon'),
        alamat: trigger.getAttribute('data-alamat'),
        jenisPerusahaan: trigger.getAttribute('data-jenis-perusahaan'),
        provinsi: trigger.getAttribute('data-provinsi'),
        kota: trigger.getAttribute('data-kota'),
        penandatanganSama: trigger.getAttribute('data-penandatangan-sama') === '1',
        namaPenandatangan: trigger.getAttribute('data-nama-penandatangan'),
        jabatanPenandatangan: trigger.getAttribute('data-jabatan-penandatangan'),
        layanan: trigger.getAttribute('data-layanan'),
        layananPenawaran: trigger.getAttribute('data-layanan-penawaran'),
        layananPengujian: trigger.getAttribute('data-layanan-pengujian'),
        hasPerubahanPengujian: trigger.getAttribute('data-has-perubahan-pengujian') === '1',
        subtotalPenawaran: trigger.getAttribute('data-subtotal-penawaran'),
        subtotalPengujian: trigger.getAttribute('data-subtotal-pengujian'),
        subtotal: trigger.getAttribute('data-subtotal'),
        total: trigger.getAttribute('data-total'),
        dokumen: trigger.getAttribute('data-dokumen'),
        ketuaPcu: trigger.getAttribute('data-ketua-pcu'),
        pcu: trigger.getAttribute('data-pcu'),
        analis: trigger.getAttribute('data-analis'),
      };

      const setText = (selector, value) => {
        const el = detailModal.querySelector(selector);
        if (el) el.textContent = value || '-';
      };

      setText('#detailKode', data.kode);
      setText('#detailPelanggan', data.pelanggan);
      setText('#detailJenis', data.jenis_pengujian);
      setText('#detailTahap', data.tahap);
      setText('#detailStatus', data.status);
      setText('#detailTanggal', data.tanggal);
      setText('#detailPerusahaan', data.perusahaan);
      setText('#detailPenanggung', data.penanggung);
      setText('#detailEmail', data.email);
      setText('#detailTelepon', data.telepon);
      setText('#detailJenisPerusahaan', data.jenisPerusahaan);
      setText('#detailProvinsi', data.provinsi);
      setText('#detailKota', data.kota);
      setText('#detailNamaPenandatangan', data.namaPenandatangan);
      setText('#detailJabatanPenandatangan', data.jabatanPenandatangan);
      setText('#detailPenandatanganSama', data.penandatanganSama ? 'Ya' : 'Tidak');
      setText('#detailAlamat', data.alamat);

      const progressBar = detailModal.querySelector('#detailProgressBar');
      const progressText = detailModal.querySelector('#detailProgressText');
      const pct = Number.isFinite(Number(data.progress)) ? Math.max(0, Math.min(100, Number(data.progress))) : 0;
      if (progressBar) progressBar.style.width = `${pct}%`;
      if (progressText) progressText.textContent = `${pct}%`;

      const formatRupiah = (num) => {
        const n = Number(num) || 0;
        return n.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
      };

      let layananPenawaran = [];
      let layananPengujian = [];
      let layananAktif = [];
      try {
        layananPenawaran = JSON.parse(data.layananPenawaran || '[]');
      } catch (err) {
        console.warn('Gagal parse layanan penawaran', err);
      }
      try {
        layananPengujian = JSON.parse(data.layananPengujian || '[]');
      } catch (err) {
        console.warn('Gagal parse layanan pengujian', err);
      }
      try {
        layananAktif = JSON.parse(data.layanan || '[]');
      } catch (err) {
        console.warn('Gagal parse layanan', err);
      }
      if (!Array.isArray(layananAktif) || layananAktif.length === 0) {
        layananAktif = data.hasPerubahanPengujian ? layananPengujian : layananPenawaran;
      }

      const renderActiveRows = (items) => {
        const servicesBody = detailModal.querySelector('#detailServicesBody');
        if (!servicesBody) return;
        if (!Array.isArray(items) || items.length === 0) {
          servicesBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
          return;
        }
        servicesBody.innerHTML = items.map((item, idx) => {
          const qty = Number(item.qty) || 0;
          const harga = Number(item.harga) || 0;
          const subtotal = Number(item.subtotal) || qty * harga;
          return `
            <tr>
              <td>${idx + 1}</td>
              <td>${item.nama || '-'}</td>
              <td>${item.kategori || '-'}</td>
              <td>${qty}</td>
              <td class="text-end">${formatRupiah(harga)}</td>
              <td class="text-end">${formatRupiah(subtotal)}</td>
            </tr>
          `;
        }).join('');
      };

      const renderSimpleRows = (selector, items) => {
        const body = detailModal.querySelector(selector);
        if (!body) return;
        if (!Array.isArray(items) || items.length === 0) {
          body.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
          return;
        }
        body.innerHTML = items.map((item, idx) => `
          <tr>
            <td>${idx + 1}</td>
            <td>${item.nama || '-'}</td>
            <td>${item.kategori || '-'}</td>
            <td>${Number(item.qty) || 0}</td>
          </tr>
        `).join('');
      };

      const subtotalCompareRow = detailModal.querySelector('#detailSubtotalCompareRow');
      const activeWrap = detailModal.querySelector('#detailServicesActiveWrap');
      const compareWrap = detailModal.querySelector('#detailServicesCompareWrap');

      if (data.hasPerubahanPengujian) {
        if (activeWrap) activeWrap.classList.add('d-none');
        if (compareWrap) compareWrap.classList.remove('d-none');
        renderSimpleRows('#detailServicesPenawaranBody', layananPenawaran);
        renderSimpleRows('#detailServicesPengujianBody', layananPengujian);
        if (subtotalCompareRow) subtotalCompareRow.classList.remove('d-none');
        setText('#detailSubtotalPenawaran', formatRupiah(data.subtotalPenawaran));
        setText('#detailSubtotalPengujian', formatRupiah(data.subtotalPengujian));
      } else {
        if (activeWrap) activeWrap.classList.remove('d-none');
        if (compareWrap) compareWrap.classList.add('d-none');
        renderActiveRows(layananAktif);
        if (subtotalCompareRow) subtotalCompareRow.classList.add('d-none');
        setText('#detailSubtotalPenawaran', 'Rp 0');
        setText('#detailSubtotalPengujian', 'Rp 0');
      }
      setText('#detailTotal', formatRupiah(data.total));

      const docList = detailModal.querySelector('#detailDocuments');
      if (docList) {
        let docs = [];
        try {
          docs = JSON.parse(data.dokumen || '[]');
        } catch (err) {
          console.warn('Gagal parse dokumen', err);
        }

        if (!Array.isArray(docs) || docs.length === 0) {
          docList.innerHTML = '<div class="text-muted">Tidak ada dokumen.</div>';
        } else {
          docList.innerHTML = docs.map((doc, idx) => {
            const action = doc.action || '';
            if (action) {
              return `
                <button
                  type="button"
                  class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 w-100 bg-white"
                  data-detail-doc-action="${action}"
                  data-detail-doc-index="${idx}"
                >
                  <span class="fw-semibold text-secondary">${doc.nama || 'Unduh Dokumen'}</span>
                  <span class="fs-5 text-primary">&#128190;</span>
                </button>
              `;
            }

            return `
              <a href="${doc.url || '#'}" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 text-decoration-none">
                <span class="fw-semibold text-secondary">${doc.nama || 'Unduh Dokumen'}</span>
                <span class="fs-5 text-primary">&#128190;</span>
              </a>
            `;
          }).join('');

          docList.querySelectorAll('[data-detail-doc-action]').forEach((btn) => {
            btn.addEventListener('click', () => {
              if (!sourceCard) return;
              const action = btn.getAttribute('data-detail-doc-action') || '';
              const selectorMap = {
                bap: '[data-bap-download]',
                rincian: '[data-rincian-sampel-download]',
                penambahan: '[data-penambahan-pengurangan-download]',
              };
              const selector = selectorMap[action];
              if (!selector) return;
              const sourceBtn = sourceCard.querySelector(selector);
              sourceBtn?.click();
            });
          });
        }
      }

      const renderList = (selector, items, emptyLabel) => {
        const list = detailModal.querySelector(selector);
        if (!list) return;
        list.innerHTML = '';
        if (!items || items.length === 0) {
          const li = document.createElement('li');
          li.className = 'text-muted small';
          li.textContent = emptyLabel;
          list.appendChild(li);
          return;
        }
        items.forEach((name) => {
          const li = document.createElement('li');
          li.className = 'border-bottom py-1';
          li.textContent = name;
          list.appendChild(li);
        });
      };

      let ketuaPcu = [];
      let pcu = [];
      let analis = [];
      try { ketuaPcu = JSON.parse(data.ketuaPcu || '[]'); } catch (err) { ketuaPcu = []; }
      try { pcu = JSON.parse(data.pcu || '[]'); } catch (err) { pcu = []; }
      try { analis = JSON.parse(data.analis || '[]'); } catch (err) { analis = []; }
      renderList('#detailKetuaPcu', ketuaPcu, 'Belum ada ketua PCU.');
      renderList('#detailPcu', pcu, 'Belum ada PCU.');
      renderList('#detailAnalis', analis, 'Belum ada analis.');
    });
  }

  document.querySelectorAll('[data-bap-download]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const card = btn.closest('[data-card]');
      if (!card) return;
      const w = window.open('about:blank', '_blank');
      if (!w) {
        alert('Popup diblokir. Izinkan popup untuk membuka dokumen.');
        return;
      }
      const perusahaan = card.getAttribute('data-perusahaan-nama') || '-';
      const alamat = card.getAttribute('data-alamat-perusahaan') || '-';
      const penanggungNama = card.getAttribute('data-penanggung') || '.................................';
      const penanggungTtd = card.getAttribute('data-penanggung-ttd') || '';
      const penandatanganNama = card.getAttribute('data-penandatangan-nama') || '';
      const jenisPerusahaan = card.getAttribute('data-jenis-perusahaan') || '-';
      const kode = card.getAttribute('data-order-kode') || '';
      const tanggalMulaiRaw = card.getAttribute('data-tanggal-pengujian-mulai')
        || card.getAttribute('data-tanggal-pengujian') || '-';
      const tanggalSelesaiRaw = card.getAttribute('data-tanggal-pengujian-selesai')
        || tanggalMulaiRaw;
      const ketuaTimNama = card.getAttribute('data-ketua-tim-nama') || '......................................................';
      const ketuaTimTtd = card.getAttribute('data-ketua-tim-ttd') || '';
      const formatTanggal = (value) => {
        const months = [
          'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const parts = (value || '').split('-');
        if (parts.length !== 3) return value || '-';
        const year = parts[0];
        const monthIndex = Number(parts[1]) - 1;
        const day = Number(parts[2]);
        if (Number.isNaN(monthIndex) || monthIndex < 0 || monthIndex > 11) {
          return value || '-';
        }
        if (Number.isNaN(day)) return value || '-';
        return `${day} ${months[monthIndex]} ${year}`;
      };
      const tanggal = formatTanggal(tanggalMulaiRaw);
      const pcu = (() => {
        try {
          return JSON.parse(card.getAttribute('data-pcu') || '[]');
        } catch (err) {
          return [];
        }
      })();
      const pcuList = pcu.length > 0 ? pcu : ['......................................................'];
      let bapParams = [];
      try {
        bapParams = JSON.parse(card.getAttribute('data-parameter-pengujian') || card.getAttribute('data-parameter') || '[]');
      } catch (err) {
        bapParams = [];
      }
      const samplingTypes = Array.from(
        new Set(
          (Array.isArray(bapParams) ? bapParams : [])
            .map((item) => getJenisPengukuran(item?.nama, item?.kategori || ''))
            .filter((val) => val && val !== '-')
        )
      );
      const samplingText = (() => {
        if (samplingTypes.length === 2) return `${samplingTypes[0]} dan ${samplingTypes[1]}`;
        if (samplingTypes.length > 0) return samplingTypes.join(', ');
        return 'Emisi, Ambien dan Lingkungan Kerja';
      })();
      const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;

      w.document.write(`
        <html>
          <head>
            <title>BAP ${kode}</title>
            <style>
              @page { margin: 12mm 16mm 18mm 16mm; }
              html, body { height: 100%; }
              body { font-family: "Times New Roman", serif; padding: 12mm 16mm 18mm 16mm; color: #111; }
              .page { min-height: 100%; position: relative; padding-top: 8mm; }
              .page-body { padding-bottom: 70px; }
              .page-footer {
                position: fixed;
                left: 16mm;
                right: 16mm;
                bottom: 24px;
              }
              @media print {
                html, body { height: auto; }
                body { padding: 0; }
                .page { min-height: 0; page-break-after: avoid; }
                .page-body { padding-bottom: 60px; }
              }
              .letter-header { display: table; width: 100%; margin: 0; }
              .header-shell {
                width: 88%;
                margin: 0 auto;
              }
              .logo-wrap,
              .header-wrap {
                display: table-cell;
                vertical-align: middle;
              }
              .logo-wrap {
                width: 70px;
                padding-left: 0;
                text-align: center;
              }
              .logo { width: 60px; height: 60px; object-fit: contain; display: block; margin: 0 auto; }
              .header-wrap {
                text-align: left;
                font-family: "Arial Narrow", Arial, sans-serif;
                border-left: 2px solid #163e67;
                padding-left: 12px;
                padding-right: 0;
                line-height: 1.08;
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
              .header-wrap .line:first-of-type { font-size: 12px; font-weight: 500; }
              .header-wrap .line:nth-of-type(2) { font-size: 13px; font-weight: 700; letter-spacing: -0.03em; }
              .header-wrap .line:nth-of-type(3) { font-size: 13px; letter-spacing: -0.03em; }
              .header-wrap .line:nth-of-type(4) { font-size: 15.6px; font-weight: 700; color: #163e67; line-height: 1.02; margin-top: 2px; letter-spacing: -0.05em; white-space: nowrap; }
              .addr {
                font-size: 11px;
                margin-top: 3px;
                font-family: Arial, sans-serif;
                text-align: left;
              }
              .addr .icon { color: #163e67; font-weight: 700; margin: 0 2px; }
              .header-line { border-top: 2px solid #000; width: 100%; margin: 10px 0 18px; }
              .document-content { width: 88%; margin: 0 auto; }
              .title { text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 0.5px; margin-bottom: 16px; }
              .paragraph { font-size: 13px; line-height: 1.6; text-align: justify; }
              .list { margin: 14px 0 18px 24px; font-size: 13px; }
              .list li { margin-bottom: 6px; }
              .data-list { margin: 10px 0 16px; font-size: 13px; }
              .data-list .row { display: flex; gap: 6px; margin-bottom: 8px; }
              .data-list .label { width: 170px; }
              .data-list .colon { width: 10px; }
              .signatures { display: flex; justify-content: space-between; margin-top: 22px; font-size: 13px; }
              .sign-col { width: 45%; text-align: center; }
              .sign-space { height: 70px; }
              .signature-img { display: block; margin: 6px auto 2px; max-height: 70px; max-width: 220px; object-fit: contain; }
              .sign-name { margin-top: 4px; font-weight: bold; }
              .footer-line { border-top: 1px solid #000; margin-top: 12px; }
              .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 4px; }
            </style>
          </head>
          <body>
            <div class="page">
              <div class="page-body">
                <div class="header-shell">
                  <div class="letter-header">
                    <div class="logo-wrap">
                      <img src="${logoUrl}" alt="Logo" class="logo" id="printLogo">
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
                </div>
                <div class="document-content">
                  <div class="title">BERITA ACARA PENGAMBILAN SAMPEL</div>

                  <div class="paragraph">
                    Pada hari ini ${tanggal}, kami nama :
                  </div>
                  <ol class="list">
                    ${pcuList.map((name) => `<li>${name}</li>`).join('')}
                  </ol>

                  <div class="paragraph">
                    Petugas pengambil sampel udara dari BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA,
                    telah melakukan pengambilan sampel: ${samplingText} dengan
                    rincian sebagaimana pada lampiran dari :
                  </div>

                  <div class="data-list">
                    <div class="row"><span class="label">Nama Perusahaan</span><span class="colon">:</span><span>${perusahaan}</span></div>
                    <div class="row"><span class="label">Alamat Perusahaan</span><span class="colon">:</span><span>${alamat}</span></div>
                    <div class="row"><span class="label">Jenis Perusahaan</span><span class="colon">:</span><span>${jenisPerusahaan}</span></div>
                  </div>

                  <div class="paragraph">
                    Demikian Berita Acara ini dibuat dengan sebenar - benarnya dan tanpa paksaan.
                  </div>

                  <div class="signatures">
                    <div class="sign-col">
                      <div>Mengetahui :</div>
                      <div>Pimpinan Perusahaan/</div>
                      <div>Yang Mewakili</div>
                      ${penanggungTtd ? `<img src="${penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                      <div class="sign-name">(${penanggungNama || '.................................'})</div>
                    </div>
                    <div class="sign-col">
                      <div>Petugas Pengambil Sampel</div>
                      <div>Ketua Tim</div>
                      ${ketuaTimTtd ? `<img src="${ketuaTimTtd}" alt="Tanda tangan Ketua Tim" class="signature-img">` : '<div class="sign-space"></div>'}
                      <div class="sign-name">(${ketuaTimNama})</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="page-footer">
                <div class="footer-line"></div>
                <div class="footer-meta">
                  <span>Tgl. terbit: 24 Desember 2024</span>
                  <span>No.: /F/7.3.12/BK3-SBY</span>
                </div>
              </div>
            </div>
            <script>
              const logo = document.getElementById('printLogo');
              const doPrint = () => setTimeout(() => window.print(), 250);
              if (logo) {
                logo.addEventListener('load', doPrint, { once: true });
                logo.addEventListener('error', doPrint, { once: true });
              } else {
                doPrint();
              }
            <\/script>
          </body>
        </html>
      `);
      w.document.close();
    });
  });

  document.querySelectorAll('[data-rincian-sampel-download]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const card = btn.closest('[data-card]');
      if (!card) return;
      const w = window.open('about:blank', '_blank');
      if (!w) {
        alert('Popup diblokir. Izinkan popup untuk membuka dokumen.');
        return;
      }
      const perusahaan = card.getAttribute('data-perusahaan-nama') || '-';
      const lokasi = card.getAttribute('data-lokasi') || '-';
      const penanggungNama = card.getAttribute('data-penanggung') || '.................................';
      const penanggungTtd = card.getAttribute('data-penanggung-ttd') || '';
      const ketuaTimNama = card.getAttribute('data-ketua-tim-nama') || '......................................................';
      const ketuaTimTtd = card.getAttribute('data-ketua-tim-ttd') || '';
      const tanggalMulaiRaw = card.getAttribute('data-tanggal-pengujian-mulai')
        || card.getAttribute('data-tanggal-pengujian') || '-';
      const tanggalSelesaiRaw = card.getAttribute('data-tanggal-pengujian-selesai')
        || tanggalMulaiRaw;
      let params = [];
      try {
        params = JSON.parse(card.getAttribute('data-parameter') || '[]');
      } catch (err) {
        params = [];
      }
      const catatanInputs = Array.from(card.querySelectorAll('[data-catatan-input]'));
      const catatanByParamId = new Map();
      catatanInputs.forEach((input) => {
        const id = input.getAttribute('data-param-id');
        if (!id) return;
        catatanByParamId.set(String(id), (input.value || '').trim());
      });
      const formatTanggal = (value) => {
        const months = [
          'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const parts = (value || '').split('-');
        if (parts.length !== 3) return value || '-';
        const year = parts[0];
        const monthIndex = Number(parts[1]) - 1;
        const day = Number(parts[2]);
        if (Number.isNaN(monthIndex) || monthIndex < 0 || monthIndex > 11) {
          return value || '-';
        }
        if (Number.isNaN(day)) return value || '-';
        return `${day} ${months[monthIndex]} ${year}`;
      };
      const formatTanggalRentang = (mulai, selesai) => {
        const mulaiFmt = formatTanggal(mulai);
        const selesaiFmt = formatTanggal(selesai);
        if (!mulai || !selesai || mulai === '-' || selesai === '-') return mulaiFmt;
        if (mulai === selesai) return mulaiFmt;
        return `${mulaiFmt} - ${selesaiFmt}`;
      };
      const tanggal = formatTanggalRentang(tanggalMulaiRaw, tanggalSelesaiRaw);
      const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;
      let lokasiRows = [];
      try {
        lokasiRows = JSON.parse(decodeHtml(card.getAttribute('data-lokasi-rows') || '[]')) || [];
      } catch (err) {
        lokasiRows = [];
      }
      if (!Array.isArray(lokasiRows) || lokasiRows.length === 0) {
        lokasiRows = [{
          lokasi,
          dokumen_list: [{
            parameter: params,
          }],
        }];
      }

      const groupedByJenis = new Map();
      lokasiRows.forEach((row) => {
        const rowLokasi = row?.lokasi || lokasi || '-';
        const allParams = [];
        (row?.dokumen_list || []).forEach((doc) => {
          (doc?.parameter || []).forEach((param) => {
            if (!param) return;
            allParams.push(param);
          });
        });
        allParams.forEach((item) => {
          const jenis = getJenisPengukuran(item?.nama, item?.kategori || '') || '-';
          if (!groupedByJenis.has(jenis)) {
            groupedByJenis.set(jenis, {
              jenis,
              lokasiSet: new Set(),
              paramKeys: new Set(),
              params: [],
              noteKeys: new Set(),
              notes: [],
            });
          }
          const group = groupedByJenis.get(jenis);
          if (rowLokasi) {
            group.lokasiSet.add(rowLokasi);
          }
          const paramNama = (item?.nama || '-').trim() || '-';
          const paramKey = paramNama.toLowerCase();
          if (!group.paramKeys.has(paramKey)) {
            group.paramKeys.add(paramKey);
            group.params.push(paramNama);
          }
          const id = item?.id ? String(item.id) : '';
          const note = (id && catatanByParamId.has(id)) ? catatanByParamId.get(id) : '';
          if (note) {
            const noteKey = note.toLowerCase();
            if (!group.noteKeys.has(noteKey)) {
              group.noteKeys.add(noteKey);
              group.notes.push(note);
            }
          }
        });
      });
      if (groupedByJenis.size === 0) {
        const fallbackLokasi = lokasi || '-';
        const fallbackParams = params.length ? params : [{ nama: '-', id: null }];
        fallbackParams.forEach((item) => {
          const jenis = getJenisPengukuran(item?.nama, item?.kategori || '') || '-';
          if (!groupedByJenis.has(jenis)) {
            groupedByJenis.set(jenis, {
              jenis,
              lokasiSet: new Set(),
              paramKeys: new Set(),
              params: [],
              noteKeys: new Set(),
              notes: [],
            });
          }
          const group = groupedByJenis.get(jenis);
          if (fallbackLokasi) {
            group.lokasiSet.add(fallbackLokasi);
          }
          const paramNama = (item?.nama || '-').trim() || '-';
          const paramKey = paramNama.toLowerCase();
          if (!group.paramKeys.has(paramKey)) {
            group.paramKeys.add(paramKey);
            group.params.push(paramNama);
          }
          const id = item?.id ? String(item.id) : '';
          const note = (id && catatanByParamId.has(id)) ? catatanByParamId.get(id) : '';
          if (note) {
            const noteKey = note.toLowerCase();
            if (!group.noteKeys.has(noteKey)) {
              group.noteKeys.add(noteKey);
              group.notes.push(note);
            }
          }
        });
      }
      const groupedRows = Array.from(groupedByJenis.values());
      const rowsHtml = groupedRows.map((group, idx) => `
          <tr>
            <td class="col-no">${idx + 1}</td>
            <td>${(Array.from(group.lokasiSet).filter((val) => val && val !== '-').join(', ')) || lokasi || '-'}</td>
            <td>${group.jenis || '-'}</td>
            <td>${group.params.length ? group.params.join(', ') : '-'}</td>
            <td><div class="note-text">${group.notes.join('<br>')}</div></td>
          </tr>
      `).join('');

      w.document.write(`
        <html>
          <head>
            <title>Rincian Pengambilan Sampel</title>
            <style>
              @page { margin: 18px 2cm 50px 2cm; }
              html, body { height: 100%; }
              body { font-family: "Times New Roman", serif; padding: 18px 2cm; color: #111; }
              .page { min-height: 100%; position: relative; }
              .page-body { padding-bottom: 50px; }
              .page-footer {
                position: fixed;
                left: 2cm;
                right: 2cm;
                bottom: 18px;
              }
              @media print {
                html, body { height: auto; }
                body { padding: 0; }
                .page { min-height: 0; page-break-after: avoid; }
                .page-body { padding-bottom: 44px; }
              }
              .doc-header {
                display: grid;
                grid-template-columns: 110px 1fr 120px;
                border: 1px solid #000;
              }
              .doc-header > div { border-right: 1px solid #000; }
              .doc-header > div:last-child { border-right: none; }
              .logo-cell {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 10px;
              }
              .logo-cell img { width: 64px; height: 64px; object-fit: contain; }
              .head-text {
                text-align: left;
                padding: 8px 8px 7px;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.1;
                font-family: "Arial Narrow", Arial, sans-serif;
                border-left: 2px solid #163e67;
              }
              .head-text .kop-dir { white-space: nowrap; display: inline-block; font-size: 12px; }
              .head-text .kop-main { color: #163e67; font-size: 13px; line-height: 1.02; display: inline-block; margin-top: 2px; white-space: nowrap; }
              .head-text .kop-contact { font-size: 10px; font-weight: 500; font-family: Arial, sans-serif; text-transform: none; }
              .meta-cell {
                display: grid;
                grid-template-rows: 1fr 1fr;
                font-size: 12px;
              }
              .meta-cell div {
                border-bottom: 1px solid #000;
                padding: 6px 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
              }
              .meta-cell div:last-child { border-bottom: none; }
              .title {
                text-align: center;
                font-weight: bold;
                font-size: 13px;
                margin: 10px 0 8px;
                letter-spacing: 0.6px;
              }
              .info-line { font-size: 12px; margin: 4px 0; }
              .info-line .label { display: inline-block; width: 160px; }
              .info-line .colon { display: inline-block; width: 10px; }
              .info-line .fill { display: inline-block; border-bottom: 1px solid #000; width: 260px; height: 12px; vertical-align: baseline; }
              .note-text {
                min-height: 12px;
                font-size: 12px;
                padding: 2px 0;
              }
              .info-line .value { display: inline-block; min-width: 260px; }
              table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
              th, td { border: 1px solid #000; padding: 4px 5px; height: 20px; }
              th { text-align: center; font-weight: bold; }
              .col-no { width: 6%; text-align: center; }
              .col-lokasi { width: 20%; }
              .col-jenis { width: 28%; }
              .col-parameter { width: 32%; }
              .col-ket { width: 20%; }
              .signatures { display: flex; justify-content: space-between; margin-top: 22px; font-size: 12px; }
              .sign-col { width: 45%; text-align: center; }
              .sign-space { height: 56px; }
              .signature-img { display: block; margin: 6px auto 2px; max-height: 56px; max-width: 200px; object-fit: contain; }
              .sign-name { margin-top: 4px; font-weight: bold; }
              .notes { font-size: 11px; border-top: 2px solid #000; padding-top: 6px; margin-top: 16px; }
              .footer-line { border-top: 1px solid #000; margin-top: 8px; }
              .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 6px; }
            </style>
          </head>
          <body>
            <div class="page">
              <div class="page-body">
                <div class="doc-header">
                  <div class="logo-cell">
                    <img src="${logoUrl}" alt="Logo" id="printLogo">
                  </div>
                  <div class="head-text">
                    KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA<br>
                    <span class="kop-dir">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</span><br>
                    DAN KESELAMATAN DAN KESEHATAN KERJA<br>
                    <span class="kop-main">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</span><br>
                    <span class="kop-contact">Jl. Dukuh Menanggal No. 122 Surabaya, (031) 8280440, balaik3surabaya@kemnaker.go.id</span>
                  </div>
                  <div class="meta-cell">
                    <div>Page : 1/1</div>
                    <div>Rev/Terb. : -/1</div>
                  </div>
                </div>

                <div class="title">RINCIAN&nbsp;&nbsp;PENGAMBILAN&nbsp;&nbsp;SAMPEL</div>

                <div class="info-line">
                  <span class="label">Nama Perusahaan</span><span class="colon">:</span>
                  <span class="value">${perusahaan}</span>
                </div>
                <div class="info-line">
                  <span class="label">Tanggal Pengujian</span><span class="colon">:</span>
                  <span class="value">${tanggal}</span>
                </div>

                <table>
                <thead>
                  <tr>
                    <th class="col-no">No.</th>
                    <th class="col-lokasi">Lokasi</th>
                    <th class="col-jenis">Jenis Pengukuran</th>
                    <th class="col-parameter">Parameter</th>
                    <th class="col-ket">Keterangan</th>
                  </tr>
                </thead>
                  <tbody>
                    ${rowsHtml}
                  </tbody>
                </table>

                <div class="signatures">
                  <div class="sign-col">
                    <div>Mengetahui :</div>
                    <div>Pimpinan Perusahaan/</div>
                    <div>Yang Mewakili</div>
                    ${penanggungTtd ? `<img src="${penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                    <div class="sign-name">(${penanggungNama || '.................................'})</div>
                  </div>
                  <div class="sign-col">
                  <div>Petugas Pengambil Sampel</div>
                  <div>Ketua Tim</div>
                  ${ketuaTimTtd ? `<img src="${ketuaTimTtd}" alt="Tanda tangan Ketua Tim" class="signature-img">` : '<div class="sign-space"></div>'}
                  <div class="sign-name">(${ketuaTimNama})</div>
                </div>
                </div>

                <div class="notes">
                  Keterangan : jenis pengukuran; E /A /LK, lokasi: namalokasi/cerobong, parameter: di isi semua parameter (fisik, kimia), jam pengukuran: (ke 1, 2, 3), keterangan: di isi informasi penting di perusahaan
                </div>
              </div>

              <div class="page-footer">
                <div class="footer-meta">
                  <span>Tgl. terbit: 24 Desember 2024</span>
                  <span>No. : F/7.3.13/BK3-SBY</span>
                </div>
                <div class="footer-line"></div>
              </div>
            </div>
            <script>
              const logo = document.getElementById('printLogo');
              const doPrint = () => setTimeout(() => window.print(), 250);
              if (logo) {
                logo.addEventListener('load', doPrint, { once: true });
                logo.addEventListener('error', doPrint, { once: true });
              } else {
                doPrint();
              }
            <\/script>
          </body>
        </html>
      `);
      w.document.close();
    });
  });

  const decodeHtml = (value) => {
    if (!value) return '';
    const textarea = document.createElement('textarea');
    textarea.innerHTML = value;
    return textarea.value;
  };

  const parseList = (raw) => {
    try {
      const decoded = decodeHtml(raw || '[]');
      return JSON.parse(decoded) || [];
    } catch (error) {
      return [];
    }
  };

  const normalizeName = (value) => String(value || '').trim().toLowerCase();
  const buildQtyMap = (items) => {
    const map = new Map();
    (items || []).forEach((item) => {
      const name = String(item?.nama || '').trim();
      if (!name) return;
      const key = normalizeName(name);
      const qty = Number(item?.qty || 0) || 0;
      const kategori = String(item?.kategori || '').trim();
      map.set(key, {
        key,
        name,
        kategori: kategori || map.get(key)?.kategori || '-',
        qty: (map.get(key)?.qty || 0) + qty,
      });
    });
    return map;
  };

  const computeChanges = (orderParams, testParams) => {
    const orderMap = buildQtyMap(orderParams);
    const testMap = buildQtyMap(testParams);

    const orderedKeys = [];
    const seen = new Set();
    (testParams || []).forEach((item) => {
      const key = normalizeName(item?.nama);
      if (!key || seen.has(key)) return;
      seen.add(key);
      orderedKeys.push(key);
    });
    (orderParams || []).forEach((item) => {
      const key = normalizeName(item?.nama);
      if (!key || seen.has(key)) return;
      seen.add(key);
      orderedKeys.push(key);
    });

    return orderedKeys.map((key) => {
      const before = orderMap.get(key)?.qty || 0;
      const after = testMap.get(key)?.qty || 0;
      if (before === after) return null;
      const name = orderMap.get(key)?.name || testMap.get(key)?.name || '-';
      const tambah = after > before ? after - before : 0;
      const kurang = before > after ? before - after : 0;
      return {
        key,
        name,
        kategori: orderMap.get(key)?.kategori || testMap.get(key)?.kategori || '-',
        tambah,
        kurang,
        total: after,
      };
    }).filter(Boolean);
  };

  const collectChangedLocations = (lokasiRows, changedKeys) => {
    const locationMap = new Map();
    (lokasiRows || []).forEach((row) => {
      const lokasi = String(row?.lokasi || '').trim() || '-';
      const rowKeys = new Set();
      (row?.dokumen_list || []).forEach((doc) => {
        (doc?.parameter || []).forEach((param) => {
          const key = normalizeName(param?.nama);
          if (!changedKeys.has(key) || rowKeys.has(key)) return;
          rowKeys.add(key);
          if (!locationMap.has(key)) {
            locationMap.set(key, new Set());
          }
          locationMap.get(key).add(lokasi);
        });
      });
    });
    return locationMap;
  };

  document.querySelectorAll('[data-card]').forEach((card) => {
    const orderParams = (parseList(card.getAttribute('data-parameter-order')) || [])
      .map((item) => ({ ...item, method: item?.method || 'indirect' }));
    const overallTestParams = parseList(card.getAttribute('data-parameter-pengujian') || card.getAttribute('data-parameter'))
      .map((param) => ({
        ...param,
        method: param?.is_direct ? 'direct' : 'indirect',
      }));
    const changes = computeChanges(orderParams, overallTestParams);
    const downloadBtn = card.querySelector('[data-penambahan-pengurangan-download]');
    const menuItem = downloadBtn?.closest('li') || downloadBtn;
    if (menuItem) {
      menuItem.classList.toggle('d-none', changes.length === 0);
    }
  });

  document.querySelectorAll('[data-penambahan-pengurangan-download]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const card = btn.closest('[data-card]');
      if (!card) return;
      const w = window.open('about:blank', '_blank');
      if (!w) {
        alert('Popup diblokir. Izinkan popup untuk membuka dokumen.');
        return;
      }
      try {
      const perusahaan = card.getAttribute('data-perusahaan-nama') || '-';
      const alamat = card.getAttribute('data-alamat-perusahaan') || '-';
      const penanggungNama = card.getAttribute('data-penanggung') || '.................................';
      const penanggungTtd = card.getAttribute('data-penanggung-ttd') || '';
      const penandatanganNama = card.getAttribute('data-penandatangan-nama') || '';
      const penandatanganJabatan = card.getAttribute('data-penandatangan-jabatan') || '';
      const ketuaTimNama = card.getAttribute('data-ketua-tim-nama') || '......................................................';
      const ketuaTimTtd = card.getAttribute('data-ketua-tim-ttd') || '';
      const tanggalMulaiRaw = card.getAttribute('data-tanggal-pengujian-mulai')
        || card.getAttribute('data-tanggal-pengujian') || '-';
      const tanggalSelesaiRaw = card.getAttribute('data-tanggal-pengujian-selesai')
        || tanggalMulaiRaw;
      const formatTanggal = (value) => {
        const months = [
          'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const parts = (value || '').split('-');
        if (parts.length !== 3) return value || '-';
        const year = parts[0];
        const monthIndex = Number(parts[1]) - 1;
        const day = Number(parts[2]);
        if (Number.isNaN(monthIndex) || monthIndex < 0 || monthIndex > 11) {
          return value || '-';
        }
        if (Number.isNaN(day)) return value || '-';
        return `${day} ${months[monthIndex]} ${year}`;
      };
      const formatTanggalRentang = (mulai, selesai) => {
        const mulaiFmt = formatTanggal(mulai);
        const selesaiFmt = formatTanggal(selesai);
        if (!mulai || !selesai || mulai === '-' || selesai === '-') return mulaiFmt;
        if (mulai === selesai) return mulaiFmt;
        return `${mulaiFmt} - ${selesaiFmt}`;
      };
      const tanggal = formatTanggalRentang(tanggalMulaiRaw, tanggalSelesaiRaw);
      const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;
      const orderParams = (parseList(card.getAttribute('data-parameter-order')) || [])
        .map((item) => ({ ...item, method: item?.method || 'indirect' }));
      const overallTestParams = parseList(card.getAttribute('data-parameter-pengujian') || card.getAttribute('data-parameter'))
        .map((param) => ({
          ...param,
          method: param?.is_direct ? 'direct' : 'indirect',
        }));
      const changes = computeChanges(orderParams, overallTestParams);
      const lokasiRows = parseList(card.getAttribute('data-lokasi-rows'));
      const changedKeys = new Set(changes.map((item) => item.key));
      const locationMap = collectChangedLocations(lokasiRows, changedKeys);

      let counter = 1;
      const rowsHtml = changes.length
        ? changes.map((item) => {
          const lokasiList = Array.from(locationMap.get(item.key) || []).filter(Boolean);
          const lokasi = lokasiList.length ? lokasiList.join(', ') : (card.getAttribute('data-lokasi') || '-');
          return `
            <tr>
              <td class="col-no">${counter++}</td>
              <td>${getJenisPengukuran(item.name, item.kategori || '')}</td>
              <td>${lokasi}</td>
              <td>${item.name || '-'}</td>
              <td>${item.tambah ? item.tambah : ''}</td>
              <td>${item.kurang ? item.kurang : ''}</td>
              <td>${typeof item.total === 'number' ? item.total : '-'}</td>
            </tr>
          `;
        }).join('')
        : `
          <tr>
            <td class="col-no">1</td>
            <td>-</td>
            <td>-</td>
            <td colspan="4" style="text-align:center;">Tidak ada penambahan/pengurangan</td>
          </tr>
        `;

      w.document.write(`
        <html>
          <head>
            <title>Penambahan/Pengurangan Pengujian</title>
            <style>
              @page { margin: 18px 2cm 50px 2cm; }
              html, body { height: 100%; }
              body { font-family: "Times New Roman", serif; padding: 18px 2cm; color: #111; }
              .page { min-height: 100%; position: relative; }
              .page-body { padding-bottom: 50px; }
              .page-footer {
                position: fixed;
                left: 2cm;
                right: 2cm;
                bottom: 18px;
              }
              @media print {
                html, body { height: auto; }
                body { padding: 0; }
                .page { min-height: 0; page-break-after: avoid; }
                .page-body { padding-bottom: 44px; }
              }
              .doc-header {
                display: grid;
                grid-template-columns: 110px 1fr 120px;
                border: 1px solid #000;
              }
              .doc-header > div { border-right: 1px solid #000; }
              .doc-header > div:last-child { border-right: none; }
              .logo-cell {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 10px;
              }
              .logo-cell img { width: 64px; height: 64px; object-fit: contain; }
              .head-text {
                text-align: left;
                padding: 8px 8px 7px;
                font-size: 12px;
                font-weight: 700;
                line-height: 1.1;
                font-family: "Arial Narrow", Arial, sans-serif;
                border-left: 2px solid #163e67;
              }
              .head-text .kop-dir { white-space: nowrap; display: inline-block; font-size: 12px; }
              .head-text .kop-main { color: #163e67; font-size: 13px; line-height: 1.02; display: inline-block; margin-top: 2px; white-space: nowrap; }
              .head-text .kop-contact { font-size: 10px; font-weight: 500; font-family: Arial, sans-serif; text-transform: none; }
              .meta-cell {
                display: grid;
                grid-template-rows: 1fr 1fr;
                font-size: 12px;
              }
              .meta-cell div {
                border-bottom: 1px solid #000;
                padding: 6px 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
              }
              .meta-cell div:last-child { border-bottom: none; }
              .title {
                text-align: center;
                font-weight: bold;
                font-size: 13px;
                margin: 10px 0 10px;
                letter-spacing: 0.6px;
                text-decoration: underline;
              }
              .info-list { margin-top: 6px; font-size: 12px; }
              .info-row { display: flex; gap: 8px; margin: 6px 0; }
              .info-row .num { width: 22px; text-align: right; }
              .info-row .label { width: 180px; }
              .info-row .colon { width: 10px; }
              .info-row .value { flex: 1; }
              .paragraph { font-size: 12px; line-height: 1.6; margin: 10px 0; text-align: justify; }
              table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
              th, td { border: 1px solid #000; padding: 4px 5px; height: 20px; }
              th { text-align: center; font-weight: bold; }
              .col-no { width: 6%; text-align: center; }
              .col-jenis { width: 22%; }
              .col-lokasi { width: 14%; }
              .col-parameter { width: 14%; }
              .col-tambah { width: 16%; }
              .col-kurang { width: 16%; }
              .col-jumlah { width: 12%; }
              .signatures { display: flex; justify-content: space-between; margin-top: 16px; font-size: 12px; }
              .sign-col { width: 45%; text-align: center; }
              .sign-space { height: 56px; }
              .signature-img { display: block; margin: 6px auto 2px; max-height: 56px; max-width: 200px; object-fit: contain; }
              .sign-name { margin-top: 4px; font-weight: bold; }
              .footer-line { border-top: 1px solid #000; margin-top: 8px; }
              .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 6px; }
            </style>
          </head>
          <body>
            <div class="page">
              <div class="page-body">
                <div class="doc-header">
                  <div class="logo-cell">
                    <img src="${logoUrl}" alt="Logo" id="printLogo">
                  </div>
                  <div class="head-text">
                    KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA<br>
                    <span class="kop-dir">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</span><br>
                    DAN KESELAMATAN DAN KESEHATAN KERJA<br>
                    <span class="kop-main">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</span><br>
                    <span class="kop-contact">Jl. Dukuh Menanggal No. 122 Surabaya, (031) 8280440, balaik3surabaya@kemnaker.go.id</span>
                  </div>
                  <div class="meta-cell">
                    <div>Page : 1/1</div>
                    <div>Rev/Terb. : 1/1</div>
                  </div>
                </div>

                <div class="title">PENAMBAHAN / PENGURANGAN PEKERJAAN PENGUJIAN</div>

                <div class="info-list">
                  <div class="info-row">
                    <div class="label">Nama Perusahaan</div>
                    <div class="colon">:</div>
                    <div class="value">${perusahaan}</div>
                  </div>
                  <div class="info-row">
                    <div class="label">Alamat Perusahaan</div>
                    <div class="colon">:</div>
                    <div class="value">${alamat}</div>
                  </div>
                  <div class="info-row">
                    <div class="label">Tanggal Pengukuran</div>
                    <div class="colon">:</div>
                    <div class="value">${tanggal}</div>
                  </div>
                </div>

                <div class="paragraph">
                  Bahwa dengan persetujuan manajer Mutu dan atau Manajer Teknis serta Bapak/Ibu
                  ${penanggungNama || penandatanganNama || '..........................'} sebagai penanggung jawab ${perusahaan}, Maka Petugas
                  Pengambil Sampel Balai Higiene dan Keselamatan Kerja Surabaya melakukan pengambilan
                  sampel yang merupakan penambahan / pengurangan dari pekerjaan yang telah disetujui
                  sebelumnya, yang meliputi :
                </div>

                <table>
                  <thead>
                    <tr>
                      <th class="col-no">No.</th>
                      <th class="col-jenis">Jenis Pengukuran</th>
                      <th class="col-lokasi">Lokasi</th>
                      <th class="col-parameter">Parameter</th>
                      <th class="col-tambah">Penambahan</th>
                      <th class="col-kurang">Pengurangan</th>
                      <th class="col-jumlah">Jumlah</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${rowsHtml}
                  </tbody>
                </table>

                <div class="signatures">
                  <div class="sign-col">
                    <div>Mengetahui :</div>
                    <div>Pimpinan Perusahaan/</div>
                    <div>Yang Mewakili</div>
                    ${penanggungTtd ? `<img src="${penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                    <div class="sign-name">(${penanggungNama || '.................................'})</div>
                  </div>
                  <div class="sign-col">
                  <div>Surabaya, ${tanggal}</div>
                  <div>Petugas Pengambil Sampel</div>
                  <div>Ketua Tim</div>
                  ${ketuaTimTtd ? `<img src="${ketuaTimTtd}" alt="Tanda tangan Ketua Tim" class="signature-img">` : '<div class="sign-space"></div>'}
                  <div class="sign-name">(${ketuaTimNama})</div>
                </div>
                </div>
              </div>

              <div class="page-footer">
                <div class="footer-meta">
                  <span>Tgl. terbit: 24 Desember 2024</span>
                  <span>No. : F/7.3.14/BK3-SBY</span>
                </div>
                <div class="footer-line"></div>
              </div>
            </div>
            <script>
              const logo = document.getElementById('printLogo');
              const doPrint = () => setTimeout(() => window.print(), 250);
              if (logo) {
                logo.addEventListener('load', doPrint, { once: true });
                logo.addEventListener('error', doPrint, { once: true });
              } else {
                doPrint();
              }
            <\/script>
          </body>
        </html>
      `);
      w.document.close();
      } catch (error) {
        console.error(error);
        alert('Gagal membuat dokumen penambahan/pengurangan.');
      }
    });
  });

  const notify = (type, text) => {
    if (window.Swal) {
      const icon = type === 'error' ? 'error' : 'success';
      return Swal.fire({
        icon,
        title: type === 'error' ? 'Gagal' : 'Berhasil',
        text,
        confirmButtonText: 'OK',
      });
    }
    alert(text);
    return Promise.resolve();
  };

  const confirmAction = async (text) => {
    if (window.Swal) {
      const result = await Swal.fire({
        title: 'Konfirmasi',
        text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
      });
      return result.isConfirmed;
    }
    return confirm(text);
  };

  const collectPayload = (card) => {
    const items = [];
    const inputs = Array.from(card.querySelectorAll('[data-catatan-input]'));
    inputs.forEach((input) => {
      const paramId = input.getAttribute('data-param-id');
      if (!paramId) return;
      items.push({
        pengujian_param_id: Number(paramId),
        catatan: input.value || '',
      });
    });
    return { items };
  };

  const sendPayload = async (card, url) => {
    const payload = collectPayload(card);
    const formData = new FormData();
    formData.append('payload', JSON.stringify(payload));
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf },
      body: formData,
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(data.message || 'Gagal menyimpan BAP.');
    }
    return data;
  };

  const setCardLoading = (card, isLoading, activeBtn = null) => {
    const buttons = card.querySelectorAll('button[data-action-save], button[data-action-submit]');
    if (isLoading) {
      window.WorkflowLoading?.lockGroup(buttons, activeBtn);
      return;
    }
    window.WorkflowLoading?.releaseGroup(buttons);
  };

  const isApprovedStatus = (card) => {
    const status = (card?.getAttribute('data-bap-status') || '').toLowerCase().trim();
    return status === 'sudah disetujui';
  };

  const isAdminUnread = (card) => (card?.getAttribute('data-admin-unread') || '') === '1';

  const setAdminUnread = (card, isUnread) => {
    if (!card) return;
    card.setAttribute('data-admin-unread', isUnread ? '1' : '0');
  };

  const markBapAsViewed = async (card) => {
    if (!card || !isApprovedStatus(card) || !isAdminUnread(card)) return;
    const permohonanId = card.getAttribute('data-permohonan-id') || '';
    const markViewedUrl = card.getAttribute('data-mark-viewed-url') || '';
    if (!permohonanId || !markViewedUrl || viewedPermohonanIds.has(permohonanId)) return;

    viewedPermohonanIds.add(permohonanId);
    setAdminUnread(card, false);
    updateStatusCounts();

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
      const response = await fetch(markViewedUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data.message || 'Gagal menandai notifikasi BAP.');
      }
    } catch (error) {
      setAdminUnread(card, true);
      updateStatusCounts();
      viewedPermohonanIds.delete(permohonanId);
      console.error(error);
    }
  };

  const markApprovedCardsAsViewed = async () => {
    const unreadApprovedCards = Array.from(cards).filter((card) => isApprovedStatus(card) && isAdminUnread(card));
    if (!unreadApprovedCards.length) return;
    await Promise.all(unreadApprovedCards.map((card) => markBapAsViewed(card)));
  };

  document.querySelectorAll('[data-card]').forEach((card) => {
    const saveBtn = card.querySelector('[data-action-save]');
    const submitBtn = card.querySelector('[data-action-submit]');
    const saveUrl = card.getAttribute('data-save-url') || '';
    const submitUrl = card.getAttribute('data-submit-url') || '';

    if (saveBtn && saveUrl) {
      saveBtn.addEventListener('click', async () => {
        try {
          setCardLoading(card, true, saveBtn);
          const data = await sendPayload(card, saveUrl);
          await notify('success', data.message || 'Draft BAP tersimpan.');
        } catch (error) {
          await notify('error', error.message || 'Gagal menyimpan draft BAP.');
        } finally {
          setCardLoading(card, false);
        }
      });
    }

    if (submitBtn && submitUrl) {
      submitBtn.addEventListener('click', async () => {
        const ok = await confirmAction('Kirim BAP ke pemohon?');
        if (!ok) return;
        try {
          setCardLoading(card, true, submitBtn);
          const data = await sendPayload(card, submitUrl);
          await notify('success', data.message || 'BAP berhasil dikirim ke pemohon.');
          window.location.reload();
        } catch (error) {
          await notify('error', error.message || 'Gagal mengirim BAP.');
        } finally {
          setCardLoading(card, false);
        }
      });
    }
  });

  const updateStatusCounts = () => {
    const counts = {};
    let approvedUnreadCount = 0;
    cards.forEach((card) => {
      const status = (card.getAttribute('data-bap-status') || '').toLowerCase().trim();
      if (!status) return;
      counts[status] = (counts[status] || 0) + 1;
      if (status === 'sudah disetujui' && isAdminUnread(card)) {
        approvedUnreadCount += 1;
      }
    });
    statusCountEls.forEach((el) => {
      const status = (el.getAttribute('data-status-count') || '').toLowerCase().trim();
      const count = counts[status] || 0;
      el.textContent = String(count);
      el.classList.toggle('d-none', count === 0);
    });
    approvedUnreadCountEls.forEach((el) => {
      el.textContent = String(approvedUnreadCount);
      el.classList.toggle('d-none', approvedUnreadCount === 0);
    });
  };

  const reorderCardsForActiveStatus = () => {
    if (!cardsContainer) return;
    const statusKey = (activeStatus || '').toLowerCase().trim();
    const sorted = Array.from(cards);
    if (statusKey === 'sudah disetujui') {
      sorted.sort((a, b) => {
        const aId = Number(a.getAttribute('data-permohonan-id') || 0);
        const bId = Number(b.getAttribute('data-permohonan-id') || 0);
        return bId - aId;
      });
    } else {
      sorted.sort((a, b) => {
        const aOrder = Number(a.getAttribute('data-initial-order') || 0);
        const bOrder = Number(b.getAttribute('data-initial-order') || 0);
        return aOrder - bOrder;
      });
    }
    sorted.forEach((card) => cardsContainer.appendChild(card));
  };

  const filterCards = () => {
    reorderCardsForActiveStatus();
    const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
    const perusahaanVal = (perusahaanInput?.value || '').toLowerCase().trim();
    let visible = 0;

    cards.forEach((card) => {
      const kode = card.getAttribute('data-kode') || '';
      const perusahaan = card.getAttribute('data-perusahaan') || '';
      const status = (card.getAttribute('data-bap-status') || '').toLowerCase().trim();
      const hideActions = status === 'menunggu persetujuan' || status === 'sudah disetujui';
      card.querySelectorAll('[data-action-save], [data-action-submit]').forEach((btn) => {
        btn.classList.toggle('d-none', hideActions);
        btn.disabled = hideActions;
      });
      card.querySelectorAll('[data-catatan-input]').forEach((input) => {
        input.disabled = hideActions;
      });
      const matchKode = !kodeVal || kode.includes(kodeVal);
      const matchPerusahaan = !perusahaanVal || perusahaan.includes(perusahaanVal);
      const matchStatus = !activeStatus || status === activeStatus;
      const show = matchKode && matchPerusahaan && matchStatus;
      card.classList.toggle('d-none', !show);
      if (show) visible += 1;
    });

    if (emptyState) {
      const hasCards = cards.length > 0;
      emptyState.classList.toggle('d-none', !hasCards || visible > 0);
    }
  };

  kodeInput?.addEventListener('input', filterCards);
  perusahaanInput?.addEventListener('input', filterCards);
  resetBtn?.addEventListener('click', () => {
    if (kodeInput) kodeInput.value = '';
    if (perusahaanInput) perusahaanInput.value = '';
    filterCards();
  });

  statusButtons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      statusButtons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');
      activeStatus = btn.getAttribute('data-filter-status') || '';
      if ((activeStatus || '').toLowerCase().trim() === 'sudah disetujui') {
        await markApprovedCardsAsViewed();
      }
      filterCards();
    });
  });

  updateStatusCounts();
  refreshRelativeTimes();
  filterCards();
})();
</script>
@endpush
