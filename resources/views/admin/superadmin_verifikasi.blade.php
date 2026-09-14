@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
  $routePrefix = $routePrefix ?? 'superadmin';
  $formatKodingDisplay = function ($code) {
    $value = trim((string) $code);
    if ($value === '' || $value === '-') return '-';
    $pos = strpos($value, '.');
    if ($pos === false) return $value;
    $trimmed = substr($value, $pos + 1);
    return $trimmed !== '' ? $trimmed : $value;
  };
  $badgeStatus = fn($status) => match (strtolower($status)) {
    'menunggu' => 'text-bg-warning',
    'revisi diminta' => 'text-bg-danger',
    'sudah direvisi' => 'text-bg-info',
    'siap ke draft lhu' => 'text-bg-success',
    default => 'text-bg-secondary',
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
  .form-check-input:not(:checked) {
    background-color: #dc3545;
    border-color: #dc3545;
  }
  #verifHasilModal .modal-dialog {
    max-width: min(1800px, 98vw);
    margin: 0.75rem auto;
  }
  #verifHasilModal .modal-content {
    min-height: 90vh;
  }
  #verifHasilModal .modal-body {
    max-height: calc(100vh - 120px);
    overflow-y: auto;
  }
  #verifHasilModal .nav-tabs {
    border-bottom: 0;
    gap: 0.5rem;
  }
  #verifHasilModal .nav-tabs .nav-link {
    border: 1px solid #15406A;
    color: #15406A;
    border-radius: 0.5rem;
    font-weight: 500;
  }
  #verifHasilModal .nav-tabs .nav-link.active {
    background-color: #15406A;
    color: #fff;
    border-color: #15406A;
  }
  #verifHasilModal .hasil-grid {
    min-height: 420px;
    border: 1px solid #dee2e6;
    border-radius: 0.75rem;
    padding: 0.5rem;
  }
  #verifHasilModal .table-responsive {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: auto;
    background: #fff;
  }
  #verifHasilModal table {
    table-layout: fixed;
    width: 100%;
    margin-bottom: 0;
  }
  #verifHasilModal th,
  #verifHasilModal td {
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  #verifAccordion .accordion-button {
    background-color: #15406a !important;
    color: #fff;
  }
  #verifAccordion .accordion-button:not(.collapsed) {
    background-color: #15406a !important;
    color: #fff;
    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
  }
  #verifAccordion .accordion-button::after {
    filter: brightness(0) invert(1);
  }
  .verif-order-code {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
  }
  .verif-company-name {
    font-size: 13px;
    font-weight: 400;
    line-height: 1.2;
  }
  .verif-header-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 2px;
    position: relative;
    padding-right: 185px;
  }
  .verif-meta-right {
    position: absolute;
    right: 34px;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
  }
  .verif-arrival-time {
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    color: rgba(255,255,255,.95);
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
  }
  .verif-arrival-time i {
    font-size: 11px;
    margin-right: 4px;
  }
  .verif-status-badge {
    font-size: 10px;
    line-height: 1.1;
  }
  .verif-search-wrap {
    background: #eef2f7;
    border: 1px solid #d7e1ee;
    border-radius: 16px;
    padding: 10px;
  }
  .verif-search-wrap .input-group-text {
    background: #fff;
    border-color: #b8cbe2;
    border-right: 0;
    border-radius: 12px 0 0 12px;
    color: #6c7f96;
  }
  .verif-search-wrap .form-control {
    border-color: #b8cbe2;
    border-left: 0;
    border-radius: 0 12px 12px 0;
    min-height: 40px;
    box-shadow: none !important;
    font-size: 14px;
  }
  .verif-search-wrap .form-control:focus {
    border-color: #95b6da;
  }
  .verif-search-wrap .btn-search-reset {
    min-height: 40px;
    border-radius: 12px;
    border: 1px solid #15406A;
    color: #15406A;
    background: #fff;
    font-weight: 600;
  }
  .verif-search-wrap .btn-search-reset:hover {
    background: #f3f8ff;
  }
  @media (max-width: 575.98px) {
    .verif-header-content {
      padding-right: 0;
    }
    .verif-meta-right {
      position: static;
      transform: none;
      align-items: flex-start;
      margin-top: 2px;
    }
    .verif-arrival-time {
      margin-top: 0;
    }
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Verifikasi Hasil Analis',
  'subtitle' => 'Dokumen analisa per parameter, status sesuai/tidak, revisi dengan pesan.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body">
    <div class="verif-search-wrap">
      <div class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Cari berdasarkan kode pesanan" data-search-kode>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" placeholder="Cari berdasarkan pelanggan" data-search-perusahaan>
          </div>
        </div>
        <div class="col-12 col-md-2">
          <button type="button" class="btn btn-search-reset w-100 btn-sm" data-search-reset>
            <i class="bi bi-arrow-clockwise me-1"></i>Reset
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="accordion" id="verifAccordion">
  @forelse($orders as $order)
  @php
    $rows = $order['rows'] ?? collect();
    $rows = $rows instanceof \Illuminate\Support\Collection ? $rows : collect($rows);
    $statuses = $rows->pluck('verif_status')->map(fn($s) => strtolower((string) $s));
    $orderStatus = 'Menunggu';
    if ($statuses->contains('revised')) $orderStatus = 'Sudah direvisi';
    elseif ($statuses->contains('revisi')) $orderStatus = 'Revisi diminta';
    elseif ($statuses->isNotEmpty() && $statuses->every(fn($s) => $s === 'approved')) $orderStatus = 'Siap ke Draft LHU';

    $locCounts = [];
    $docCounts = [];
    foreach ($rows as $row) {
      $locKey = $row['lokasi'] ?? '-';
      $docKey = ($row['lokasi'] ?? '-') . '::' . ($row['dokumen_label'] ?? '-');
      $locCounts[$locKey] = ($locCounts[$locKey] ?? 0) + 1;
      $docCounts[$docKey] = ($docCounts[$docKey] ?? 0) + 1;
    }
    $printedLoc = [];
    $printedDoc = [];
    $accId = 'verif-' . (($order['permohonan_id'] ?? $loop->index) . '-' . $loop->index);
  @endphp
  <div class="accordion-item border-0 shadow-sm rounded-4 mb-3 overflow-hidden" data-card data-kode="{{ strtolower($order['kode']) }}" data-perusahaan="{{ strtolower($order['perusahaan']) }}">
    <h2 class="accordion-header" id="{{ $accId }}-header">
      <button
        class="accordion-button collapsed fw-semibold"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#{{ $accId }}-collapse"
        aria-expanded="false"
        aria-controls="{{ $accId }}-collapse"
      >
        <div class="verif-header-content pe-3">
          <div>
            <div class="verif-order-code">{{ $order['kode'] }}</div>
            <div class="verif-company-name">{{ $order['perusahaan'] }}</div>
          </div>
          <div class="verif-meta-right">
            <div class="verif-arrival-time">
              <i class="bi bi-clock"></i>
              <span data-relative-time data-time-unix="{{ (int) ($order['masuk_at_unix'] ?? 0) }}">-</span>
            </div>
            <span class="badge verif-status-badge {{ $badgeStatus($orderStatus) }}" data-order-status-badge>{{ $orderStatus }}</span>
          </div>
        </div>
      </button>
    </h2>
    <div id="{{ $accId }}-collapse" class="accordion-collapse collapse" aria-labelledby="{{ $accId }}-header" data-bs-parent="#verifAccordion">
      <div class="accordion-body bg-white" data-verif-card>

        <div class="table-responsive mb-3">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:18%;">Lokasi</th>
                <th style="width:18%;">Dokumen</th>
                <th style="width:10%;">Koding</th>
                <th style="width:12%;">Kategori</th>
                <th style="width:18%;">Parameter</th>
                <th style="width:14%;">Dokumen Hasil</th>
                <th style="width:10%;" class="text-center">Sesuai</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $row)
                @php
                  $locKey = $row['lokasi'] ?? '-';
                  $docKey = ($row['lokasi'] ?? '-') . '::' . ($row['dokumen_label'] ?? '-');
                  $isApproved = strtolower((string) ($row['verif_status'] ?? '')) === 'approved';
                @endphp
                <tr>
                  @if(empty($printedLoc[$locKey]))
                    @php $printedLoc[$locKey] = true; @endphp
                    <td class="fw-semibold align-middle" rowspan="{{ $locCounts[$locKey] ?? 1 }}">{{ $row['lokasi'] ?? '-' }}</td>
                  @endif
                  @if(empty($printedDoc[$docKey]))
                    @php $printedDoc[$docKey] = true; @endphp
                    <td class="small text-muted align-middle" rowspan="{{ $docCounts[$docKey] ?? 1 }}">
                      <div class="fw-semibold text-dark">{{ $row['dokumen_label'] ?? '-' }}</div>
                      @if(!empty($row['dokumen_files']))
                        <div class="small text-muted">
                          @foreach($row['dokumen_files'] as $file)
                            @if(!empty($file['url']))
                              <div><a href="{{ $file['url'] }}" target="_blank">{{ $file['name'] ?? 'File' }}</a></div>
                            @else
                              <div>{{ $file['name'] ?? 'File' }}</div>
                            @endif
                          @endforeach
                        </div>
                      @else
                        <div class="small text-muted">Belum ada dokumen</div>
                      @endif
                    </td>
                  @endif
                  <td class="fw-semibold"><code>{{ $formatKodingDisplay($row['koding'] ?? '-') }}</code></td>
                  <td>{{ $row['kategori'] ?? '-' }}</td>
                  <td class="fw-semibold">{{ $row['parameter'] ?? '-' }}</td>
                  <td>
                    @if(!empty($row['prepanalisa_item_id']))
                      <button type="button"
                              class="btn btn-outline-primary btn-sm"
                              data-open-hasil
                              data-hasil='@json($row['saved'] ?? null)'
                              data-param="{{ $row['parameter'] ?? '-' }}"
                              data-order="{{ $order['kode'] ?? '-' }}">
                        Lihat
                      </button>
                    @else
                      <span class="text-muted small">-</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <div class="form-check form-switch d-inline-flex align-items-center m-0">
                      <input type="checkbox"
                             class="form-check-input"
                             data-verif-switch
                             data-item-id="{{ $row['prepanalisa_item_id'] ?? '' }}"
                             @if($isApproved) checked @endif>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center text-muted small">Tidak ada dokumen untuk diverifikasi.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
          <button class="btn btn-outline-danger btn-sm" type="button" data-open-revisi data-permohonan-id="{{ $order['permohonan_id'] }}">Minta Revisi</button>
          <button class="btn btn-primary btn-sm" type="button" data-approve data-permohonan-id="{{ $order['permohonan_id'] }}">Selesaikan Verifikasi</button>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="text-center text-muted py-4">Tidak ada dokumen untuk diverifikasi.</div>
  @endforelse
</div>
<div class="text-center text-muted d-none mt-3" data-search-empty>Data verifikasi tidak ditemukan.</div>


<!-- Modal Revisi -->
<div class="modal fade" id="verifRevisiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Pesan Revisi Analisa</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2 small text-muted">Order: <span id="verifOrderId">-</span></div>
        <label class="form-label small">Isi pesan revisi</label>
        <textarea class="form-control" id="verifRevisiMsg" rows="4" placeholder="Jelaskan alasan revisi"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" data-send-verif-revisi>Kirim Revisi</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Hasil Analisa -->
<div class="modal fade" id="verifHasilModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Aksi Parameter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-muted mb-2">Order: <span id="hasilOrderLabel">-</span></div>
        <div class="fw-semibold mb-3" id="hasilParamLabel">-</div>
        <ul class="nav nav-tabs mb-3" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#hasilSkpm" type="button">SK/PM</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#hasilBaca" type="button">Hasil Baca</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#hasilHitung" type="button">Hasil Perhitungan</button></li>
        </ul>
        <div class="tab-content hasil-grid">
          <div class="tab-pane fade show active" id="hasilSkpm">
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle">
                <thead class="table-light" id="hasilSkpmHead"></thead>
                <tbody id="hasilSkpmBody"></tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="hasilBaca">
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle">
                <thead class="table-light" id="hasilBacaHead"></thead>
                <tbody id="hasilBacaBody"></tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="hasilHitung">
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle">
                <thead class="table-light" id="hasilHitungHead"></thead>
                <tbody id="hasilHitungBody"></tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="text-muted small" id="hasilEmptyNote">Tidak ada data.</div>
        <div class="d-flex justify-content-end mt-3">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Keluar</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const kodeInput = document.querySelector('[data-search-kode]');
  const perusahaanInput = document.querySelector('[data-search-perusahaan]');
  const resetBtn = document.querySelector('[data-search-reset]');
  const cards = document.querySelectorAll('[data-card]');
  const emptyState = document.querySelector('[data-search-empty]');
  const revisiModalEl = document.getElementById('verifRevisiModal');
  const revisiModal = revisiModalEl && window.bootstrap ? new window.bootstrap.Modal(revisiModalEl) : null;
  const hasilModalEl = document.getElementById('verifHasilModal');
  const hasilModal = hasilModalEl && window.bootstrap ? new window.bootstrap.Modal(hasilModalEl) : null;
  const hasilParamLabel = document.getElementById('hasilParamLabel');
  const hasilOrderLabel = document.getElementById('hasilOrderLabel');
  const hasilSkpmHead = document.getElementById('hasilSkpmHead');
  const hasilSkpmBody = document.getElementById('hasilSkpmBody');
  const hasilBacaHead = document.getElementById('hasilBacaHead');
  const hasilBacaBody = document.getElementById('hasilBacaBody');
  const hasilHitungHead = document.getElementById('hasilHitungHead');
  const hasilHitungBody = document.getElementById('hasilHitungBody');
  const hasilSkpmTabBtn = hasilModalEl?.querySelector('[data-bs-target="#hasilSkpm"]') || null;
  const hasilBacaTabBtn = hasilModalEl?.querySelector('[data-bs-target="#hasilBaca"]') || null;
  const hasilHitungTabBtn = hasilModalEl?.querySelector('[data-bs-target="#hasilHitung"]') || null;
  const hasilEmptyNote = document.getElementById('hasilEmptyNote');
  const revisiOrder = document.getElementById('verifOrderId');
  const revisiMsg = document.getElementById('verifRevisiMsg');
  const sendRevisiBtn = document.querySelector('[data-send-verif-revisi]');

  const notify = (type, text) => {
    if (window.Swal) {
      const iconMap = { success: 'success', error: 'error', info: 'info', warning: 'warning' };
      const titleMap = { success: 'Berhasil', error: 'Gagal', info: 'Info', warning: 'Peringatan' };
      return Swal.fire({
        icon: iconMap[type] || 'info',
        title: titleMap[type] || 'Info',
        text,
        confirmButtonText: 'OK',
      });
    }
    alert(text);
    return Promise.resolve();
  };

  const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token?.getAttribute('content') || '';
  };

  const formatRelativeTime = (unix) => {
    const ts = Number(unix || 0);
    if (!ts) return '-';
    const now = Math.floor(Date.now() / 1000);
    let diff = now - ts;
    if (diff < 0) diff = 0;
    if (diff < 60) return 'baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`;
    return `${Math.floor(diff / 86400)} hari yang lalu`;
  };

  const updateRelativeTimes = () => {
    document.querySelectorAll('[data-relative-time]').forEach((node) => {
      const ts = Number(node.getAttribute('data-time-unix') || 0);
      node.textContent = formatRelativeTime(ts);
    });
  };

  const filterCards = () => {
    const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
    const perusahaanVal = (perusahaanInput?.value || '').toLowerCase().trim();
    let visible = 0;

    cards.forEach((card) => {
      const kode = card.getAttribute('data-kode') || '';
      const perusahaan = card.getAttribute('data-perusahaan') || '';
      const matchKode = !kodeVal || kode.includes(kodeVal);
      const matchPerusahaan = !perusahaanVal || perusahaan.includes(perusahaanVal);
      const show = matchKode && matchPerusahaan;
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

  filterCards();
  updateRelativeTimes();

  let pendingPermohonanId = null;

  const collectItems = (card) => {
    const switches = Array.from(card.querySelectorAll('[data-verif-switch]'));
    return switches
      .map((input) => ({
        item_id: parseInt(input.getAttribute('data-item-id') || '0', 10),
        sesuai: input.checked,
      }))
      .filter((item) => item.item_id);
  };

  const badgeClassByStatus = (statusText) => {
    const key = String(statusText || '').toLowerCase();
    if (key === 'siap ke draft lhu') return 'text-bg-success';
    if (key === 'revisi diminta') return 'text-bg-danger';
    if (key === 'sudah direvisi') return 'text-bg-info';
    if (key === 'menunggu') return 'text-bg-warning';
    return 'text-bg-secondary';
  };

  const updateCardStatusBadge = (card) => {
    if (!card) return;
    const badge = card.querySelector('[data-order-status-badge]');
    if (!badge) return;

    const switches = Array.from(card.querySelectorAll('[data-verif-switch]'));
    if (!switches.length) {
      badge.textContent = 'Menunggu';
      badge.className = `badge ${badgeClassByStatus('Menunggu')}`;
      return;
    }

    const allApproved = switches.every((input) => input.checked);
    const statusText = allApproved ? 'Siap ke Draft LHU' : 'Menunggu';
    badge.textContent = statusText;
    badge.className = `badge ${badgeClassByStatus(statusText)}`;
  };

  document.addEventListener('click', (event) => {
    const openRevisi = event.target.closest('[data-open-revisi]');
    if (openRevisi) {
      const card = openRevisi.closest('[data-verif-card]');
      pendingPermohonanId = openRevisi.getAttribute('data-permohonan-id');
      const kode = openRevisi.closest('[data-card]')?.getAttribute('data-kode') || '-';
      if (revisiOrder) revisiOrder.textContent = kode;
      if (revisiMsg) revisiMsg.value = '';
      revisiModal?.show();
      return;
    }

    const approveBtn = event.target.closest('[data-approve]');
    if (approveBtn) {
      const card = approveBtn.closest('[data-verif-card]');
      const permohonanId = approveBtn.getAttribute('data-permohonan-id');
      if (!card || !permohonanId) return;
      const items = collectItems(card);
      if (items.some((item) => !item.sesuai)) {
        notify('warning', 'Masih ada parameter yang belum sesuai.');
        return;
      }
      submitVerifikasi(permohonanId, items, '');
    }
  });

  document.addEventListener('change', (event) => {
    const verifSwitch = event.target.closest('[data-verif-switch]');
    if (!verifSwitch) return;
    const card = verifSwitch.closest('[data-verif-card]');
    updateCardStatusBadge(card);
  });

  sendRevisiBtn?.addEventListener('click', () => {
    if (!pendingPermohonanId) return;
    const card = Array.from(document.querySelectorAll('[data-approve]'))
      .find((btn) => btn.getAttribute('data-permohonan-id') === pendingPermohonanId)
      ?.closest('[data-verif-card]');
    if (!card) return;
    const items = collectItems(card);
    const note = (revisiMsg?.value || '').trim();
    if (!items.length) return;
    if (!items.some((item) => !item.sesuai)) {
      notify('warning', 'Tidak ada parameter yang ditandai tidak sesuai.');
      return;
    }
    if (!note) {
      notify('warning', 'Alasan revisi wajib diisi.');
      return;
    }
    submitVerifikasi(pendingPermohonanId, items, note);
  });

  document.querySelectorAll('[data-verif-card]').forEach((card) => {
    updateCardStatusBadge(card);
  });

  const submitVerifikasi = async (permohonanId, items, note) => {
    const formData = new FormData();
    formData.append('permohonan_id', permohonanId);
    formData.append('items', JSON.stringify(items));
    formData.append('note', note || '');
    formData.append('_token', getCsrfToken());

    try {
      const response = await fetch("{{ route($routePrefix . '.verifikasi.submit') }}", {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'same-origin',
      });
      const data = response.headers.get('content-type')?.includes('application/json')
        ? await response.json()
        : { message: await response.text() };
      if (!response.ok) {
        throw new Error(data.message || 'Gagal menyimpan verifikasi.');
      }
      await notify('success', data.message || 'Verifikasi tersimpan.');
      window.location.reload();
    } catch (err) {
      await notify('error', err.message || 'Gagal menyimpan verifikasi.');
    }
  };

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const normalizeDataset = (dataset) => {
    if (Array.isArray(dataset)) {
      return { columns: [], rows: dataset };
    }

    if (dataset && typeof dataset === 'object' && Array.isArray(dataset.rows)) {
      const columns = Array.isArray(dataset.columns)
        ? dataset.columns
          .map((col) => String(col ?? '').trim())
          .filter((col) => col !== '')
        : [];
      return { columns, rows: dataset.rows };
    }

    return { columns: [], rows: [] };
  };

  const splitHeaderLabel = (label) => {
    const raw = String(label ?? '').trim();
    if (!raw) return { group: '', child: '' };
    const parts = raw.split('::').map((part) => part.trim());
    if (parts.length >= 2 && parts[0] && parts[1]) {
      return { group: parts[0], child: parts.slice(1).join('::') };
    }
    return { group: raw, child: '' };
  };

  const findHeaderIndex = (headers, matchers = []) => {
    if (!Array.isArray(headers) || !headers.length) return -1;
    const lowered = headers.map((h) => String(h ?? '').toLowerCase().trim());
    return lowered.findIndex((label) => matchers.some((matcher) => label.includes(matcher)));
  };

  const deriveSkPmFromPerhitungan = (hasilPerhitunganRaw) => {
    const dataset = normalizeDataset(hasilPerhitunganRaw);
    const rows = Array.isArray(dataset.rows) ? dataset.rows : [];
    if (!rows.length) return null;

    const headers = Array.isArray(dataset.columns) ? dataset.columns : [];
    const lokasiIdx = findHeaderIndex(headers, ['lokasi', 'koding', 'sampel', 'sample']);
    const skIdx = findHeaderIndex(headers, ['sk']);
    const pmIdx = findHeaderIndex(headers, ['p mmhg', 'pm']);

    const mappedRows = rows
      .map((row) => {
        const rowType = String(row?.row_type ?? '').toLowerCase();
        if (rowType.includes('mdl')) return null;

        if (row && typeof row === 'object' && !Array.isArray(row)) {
          const label = row.label ?? row.lokasi ?? row.koding ?? row.sample ?? row.no_sampel ?? row.cols?.[lokasiIdx >= 0 ? lokasiIdx : 0] ?? '';
          const sk = row.sk ?? row.SK ?? row.sk_c ?? row.cols?.[skIdx] ?? '';
          const pm = row.pm ?? row.p ?? row.P ?? row.pmhg ?? row.cols?.[pmIdx] ?? '';
          if (`${label}${sk}${pm}`.trim() === '') return null;
          return [label, sk, pm];
        }

        if (Array.isArray(row)) {
          const label = row[lokasiIdx >= 0 ? lokasiIdx : 0] ?? '';
          const sk = row[skIdx >= 0 ? skIdx : 5] ?? '';
          const pm = row[pmIdx >= 0 ? pmIdx : 6] ?? '';
          if (`${label}${sk}${pm}`.trim() === '') return null;
          return [label, sk, pm];
        }

        return null;
      })
      .filter(Boolean);

    if (!mappedRows.length) return null;
    return {
      columns: ['Koding Sample', 'SK', 'PM'],
      rows: mappedRows,
    };
  };

  const isNoHeader = (header) => {
    const lower = String(header ?? '').toLowerCase().trim();
    return lower === 'no' || lower === 'no.' || lower === 'nomor';
  };

  const isSampleHeader = (header) => {
    const lower = String(header ?? '').toLowerCase().trim();
    return lower.includes('sampel') || lower.includes('sample') || lower.includes('koding');
  };

  const pickCellValue = (row, header, index, rowIndex = 0, headers = []) => {
    if (Array.isArray(row)) return row?.[index] ?? '';
    if (!row || typeof row !== 'object') return '';
    const lowerHeader = String(header ?? '').toLowerCase().trim();
    const hasNoColumn = headers.some((h) => isNoHeader(h));
    const noColumnIndex = headers.findIndex((h) => isNoHeader(h));
    const shiftedCols = Array.isArray(row.cols) && hasNoColumn && row.cols.length === Math.max(0, headers.length - 1);
    const hasCols = Array.isArray(row.cols) && row.cols.length > 0;
    const resolveColValue = () => {
      if (!hasCols) return undefined;
      if (shiftedCols && index === noColumnIndex) return undefined;
      const colIndex = shiftedCols && index > noColumnIndex ? index - 1 : index;
      if (colIndex < 0) return undefined;
      return Object.prototype.hasOwnProperty.call(row.cols, colIndex)
        ? (row.cols?.[colIndex] ?? '')
        : undefined;
    };

    if (isNoHeader(header)) {
      if (Object.prototype.hasOwnProperty.call(row, header)) return row?.[header] ?? '';
      if (row.no !== undefined) return row.no;
      if (row.nomor !== undefined) return row.nomor;
      if (row.urutan !== undefined) return row.urutan;
      if (shiftedCols) return rowIndex + 1;
      return rowIndex + 1;
    }

    const colValue = resolveColValue();
    if (colValue !== undefined) {
      return colValue;
    }

    if (isSampleHeader(header)) {
      if (Object.prototype.hasOwnProperty.call(row, header)) return row?.[header] ?? '';
      if (row.no_sampel !== undefined) return row.no_sampel;
      if (row.sample !== undefined) return row.sample;
      if (row.koding !== undefined) return row.koding;
      if (row.label !== undefined) return row.label;
    }

    // Semantic mapping for HC/BTX style datasets whose row keys are normalized.
    if (lowerHeader.includes('vol') && lowerHeader.includes('cs2') && row.vol_cs2 !== undefined) return row.vol_cs2;
    if (lowerHeader.includes('rt benzene') && row.rt_benzene !== undefined) return row.rt_benzene;
    if ((lowerHeader.includes('l.area benzene') || lowerHeader.includes('l area benzene')) && row.area_benzene !== undefined) return row.area_benzene;
    if ((lowerHeader.includes('benzene') && lowerHeader.includes('mg/ml')) || lowerHeader === 'benzene') {
      if (row.benzene !== undefined) return row.benzene;
    }
    if (lowerHeader.includes('rt toluene') && row.rt_toluene !== undefined) return row.rt_toluene;
    if ((lowerHeader.includes('l.area toluene') || lowerHeader.includes('l area toluene')) && row.area_toluene !== undefined) return row.area_toluene;
    if ((lowerHeader.includes('toluene') && lowerHeader.includes('mg/ml')) || lowerHeader === 'toluene') {
      if (row.toluene !== undefined) return row.toluene;
    }
    if (lowerHeader.includes('rt xylene') && row.rt_xylene !== undefined) return row.rt_xylene;
    if ((lowerHeader.includes('l.area xylene') || lowerHeader.includes('l area xylene')) && row.area_xylene !== undefined) return row.area_xylene;
    if ((lowerHeader.includes('xylene') && lowerHeader.includes('mg/ml')) || lowerHeader === 'xylene') {
      if (row.xylene !== undefined) return row.xylene;
    }

    if (Object.prototype.hasOwnProperty.call(row, header)) return row?.[header] ?? '';
    if (Object.prototype.hasOwnProperty.call(row, index)) return row?.[index] ?? '';
    if (hasCols) {
      if (shiftedCols && index > noColumnIndex) return row.cols?.[index - 1] ?? '';
      return row.cols?.[index] ?? '';
    }
    const orderedKeys = Object.keys(row).filter((key) => {
      if (key === 'row_type' || key === 'cols') return false;
      const value = row[key];
      return value === null || ['string', 'number', 'boolean'].includes(typeof value);
    });
    if (orderedKeys[index] !== undefined) return row?.[orderedKeys[index]] ?? '';
    return '';
  };

  const buildTable = (headEl, bodyEl, rawDataset) => {
    if (!headEl || !bodyEl) return false;
    headEl.innerHTML = '';
    bodyEl.innerHTML = '';

    const dataset = normalizeDataset(rawDataset);
    const rows = Array.isArray(dataset.rows) ? dataset.rows : [];
    if (rows.length === 0) return false;

    const first = rows[0] || {};
    const headers = dataset.columns.length
      ? dataset.columns
      : (Array.isArray(first) ? first.map((_, i) => `Kolom ${i + 1}`) : Object.keys(first));
    if (!headers.length) return false;

    const firstHeader = String(headers[0] ?? '').trim().toLowerCase();
    const secondHeader = String(headers[1] ?? '').trim().toLowerCase();
    const thirdHeader = String(headers[2] ?? '').trim().toLowerCase();
    const firstIsNumbering = firstHeader === 'no' || firstHeader === 'no.' || firstHeader === 'nomor';
    const secondIsParameter = secondHeader === 'parameter';
    const firstIsSample = isSampleHeader(headers[0] ?? '');
    const secondIsSk = secondHeader === 'sk';
    const thirdIsPm = thirdHeader === 'pm' || thirdHeader.includes('mmhg');
    const isSkpmShape = headers.length === 3 && firstIsSample && secondIsSk && thirdIsPm;

    const widths = headers.map((_, idx) => {
      if (isSkpmShape) {
        if (idx === 0) return '50%';
        return '25%';
      }
      if (idx === 0 && firstIsNumbering) return '6%';
      if (idx === 0 && firstIsSample) return '40%';
      if (idx === 0) return '16%';
      if (idx === 1 && secondIsParameter) return '18%';
      return `${Math.max(8, Math.floor(76 / Math.max(1, headers.length - 2)))}%`;
    });

    const headerMeta = headers.map((header, idx) => ({ ...splitHeaderLabel(header), idx }));
    const hasNestedHeader = headerMeta.some((meta) => meta.child !== '');

    if (!hasNestedHeader) {
      headEl.innerHTML = `<tr>${headerMeta.map((meta) => `<th style="width:${widths[meta.idx]}">${escapeHtml(meta.group)}</th>`).join('')}</tr>`;
    } else {
      const topCells = [];
      const bottomCells = [];
      let cursor = 0;
      while (cursor < headerMeta.length) {
        const current = headerMeta[cursor];
        if (!current.child) {
          topCells.push(`<th rowspan="2" style="width:${widths[current.idx]}">${escapeHtml(current.group)}</th>`);
          cursor += 1;
          continue;
        }

        const group = current.group;
        let span = 0;
        const childCells = [];
        while (cursor < headerMeta.length) {
          const next = headerMeta[cursor];
          if (next.child && next.group === group) {
            span += 1;
            childCells.push(`<th style="width:${widths[next.idx]}">${escapeHtml(next.child)}</th>`);
            cursor += 1;
            continue;
          }
          break;
        }
        topCells.push(`<th colspan="${span}">${escapeHtml(group)}</th>`);
        bottomCells.push(...childCells);
      }
      headEl.innerHTML = `<tr>${topCells.join('')}</tr><tr>${bottomCells.join('')}</tr>`;
    }

    rows.forEach((row, rowIndex) => {
      const cells = headers.map((h, idx) => pickCellValue(row, h, idx, rowIndex, headers));
      bodyEl.innerHTML += `<tr>${cells.map((c, idx) => {
        const isSampleCol = idx === 0 && firstIsSample;
        const cellStyle = isSampleCol
          ? `width:${widths[idx]}; white-space:normal; overflow:visible; text-overflow:clip; word-break:break-word;`
          : `width:${widths[idx]}`;
        return `<td title="${escapeHtml(c)}" style="${cellStyle}">${escapeHtml(c)}</td>`;
      }).join('')}</tr>`;
    });
    return true;
  };

  const activateHasilTab = (tabButton) => {
    if (!tabButton) return;
    if (window.bootstrap?.Tab) {
      window.bootstrap.Tab.getOrCreateInstance(tabButton).show();
      return;
    }
    tabButton.click();
  };

  document.addEventListener('click', (event) => {
    const openHasil = event.target.closest('[data-open-hasil]');
    if (!openHasil) return;
    const raw = openHasil.getAttribute('data-hasil');
    let data = null;
    try { data = raw ? JSON.parse(raw) : null; } catch { data = null; }
    if (hasilParamLabel) hasilParamLabel.textContent = openHasil.getAttribute('data-param') || '-';
    if (hasilOrderLabel) hasilOrderLabel.textContent = openHasil.getAttribute('data-order') || '-';

    let okSkpm = buildTable(hasilSkpmHead, hasilSkpmBody, data?.skpm || []);
    if (!okSkpm) {
      const derivedSkPm = deriveSkPmFromPerhitungan(data?.hasil_perhitungan || []);
      if (derivedSkPm) {
        okSkpm = buildTable(hasilSkpmHead, hasilSkpmBody, derivedSkPm);
      }
    }
    const okBaca = buildTable(hasilBacaHead, hasilBacaBody, data?.hasil_baca || []);
    const okHitung = buildTable(hasilHitungHead, hasilHitungBody, data?.hasil_perhitungan || []);

    if (hasilEmptyNote) {
      hasilEmptyNote.classList.toggle('d-none', okSkpm || okBaca || okHitung);
    }

    if (okSkpm) {
      activateHasilTab(hasilSkpmTabBtn);
    } else if (okBaca) {
      activateHasilTab(hasilBacaTabBtn);
    } else if (okHitung) {
      activateHasilTab(hasilHitungTabBtn);
    }

    hasilModal?.show();
  });
})();
</script>
@endpush
