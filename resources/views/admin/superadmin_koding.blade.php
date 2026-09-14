@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
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
  .badge-method-direct {
    background-color: #1d4ed8;
    color: #fff;
  }
  .badge-method-indirect {
    background-color: #f59e0b;
    color: #111827;
  }
  .koding-arrival-time {
    font-size: 11px;
    line-height: 1.2;
    color: #6c757d;
    white-space: nowrap;
  }
  .koding-arrival-time i {
    font-size: 11px;
    margin-right: 4px;
  }
  .koding-header-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    padding-right: 170px;
  }
  .koding-order-code {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
    color: #fff;
  }
  .koding-company-name {
    font-size: 13px;
    font-weight: 400;
    line-height: 1.2;
    color: rgba(255,255,255,.92);
    margin-top: 2px;
  }
  .koding-meta-row {
    position: absolute;
    right: 34px;
    top: 50%;
    transform: translateY(-50%);
  }
  @media (max-width: 575.98px) {
    .koding-header-content {
      padding-right: 0;
    }
    .koding-meta-row {
      position: static;
      transform: none;
      margin-top: 2px;
    }
  }
  #kodingAccordion .accordion-button {
    background-color: #15406a;
    color: #fff;
  }
  #kodingAccordion .accordion-button:not(.collapsed) {
    background-color: #15406a;
    color: #fff;
    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
  }
  #kodingAccordion .accordion-button::after {
    filter: brightness(0) invert(1);
  }
  #kodingAccordion .accordion-button .text-muted,
  #kodingAccordion .accordion-button .koding-arrival-time,
  #kodingAccordion .accordion-button .koding-arrival-time span,
  #kodingAccordion .accordion-button .koding-arrival-time i {
    color: #fff !important;
  }
  .koding-search-wrap {
    background: #eef2f7;
    border: 1px solid #d7e1ee;
    border-radius: 16px;
    padding: 10px;
    margin-bottom: 12px;
  }
  .koding-search-wrap .input-group-text {
    background: #fff;
    border-color: #b8cbe2;
    border-right: 0;
    border-radius: 12px 0 0 12px;
    color: #6c7f96;
  }
  .koding-search-wrap .form-control {
    border-color: #b8cbe2;
    border-left: 0;
    border-radius: 0 12px 12px 0;
    min-height: 40px;
    box-shadow: none !important;
    font-size: 14px;
  }
  .koding-search-wrap .form-control:focus {
    border-color: #95b6da;
  }
  .koding-search-wrap .btn-search-reset {
    min-height: 40px;
    border-radius: 12px;
    border: 1px solid #15406A;
    color: #15406A;
    background: #fff;
    font-weight: 600;
  }
  .koding-search-wrap .btn-search-reset:hover {
    background: #f3f8ff;
  }
  .koding-location-box {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Koding ID Lokasi',
  'subtitle' => 'Input ID unik per lokasi sesuai qty parameter yang dipesan.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body">
    <div class="koding-search-wrap">
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
    <div class="mt-2 small text-dark bg-info bg-opacity-25 rounded-3 p-2">
      <div class="fw-semibold">Keterangan Format Koding</div>
      <div>`{id_user}.{tahun}.{urut}/{kategori}.{lokasi}.{parameter}[.sample]`</div>
      <div>Contoh standar: `003.26.02/LK.001.DPM10` atau custom: `j.01/046`</div>
      <div class="mt-1 text-primary fw-semibold">
        <i class="bi bi-pencil-square me-1"></i>
        Nomor koding dan format awal dapat diedit secara bebas sesuai kebutuhan pegawai/sampel yang datang.
      </div>
    </div>
  </div>
</div>

<div class="accordion" id="kodingAccordion">
  @forelse($orders as $order)
  @php
    $routePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'superadmin';
    $permohonanId = $order['permohonan_id'] ?? null;
    $lokasiRows = collect($order['lokasi_rows'] ?? []);
    $totalLokasi = (int) ($order['total_lokasi'] ?? $lokasiRows->count());
    $headingId = 'kodingHeading' . $permohonanId;
    $collapseId = 'kodingCollapse' . $permohonanId;
    $masukAtUnix = (int) ($order['masuk_at_unix'] ?? 0);
  @endphp
  <div
    class="accordion-item border-0 shadow-sm rounded-4 mb-3 overflow-hidden"
    data-card
    data-kode="{{ strtolower($order['kode']) }}"
    data-kode-display="{{ $order['kode'] }}"
    data-perusahaan="{{ strtolower($order['perusahaan']) }}"
    data-perusahaan-display="{{ $order['perusahaan'] }}"
    data-save-url="{{ $permohonanId ? route($routePrefix . '.koding.draft', $permohonanId) : '' }}"
    data-submit-url="{{ $permohonanId ? route($routePrefix . '.koding.submit', $permohonanId) : '' }}"
  >
    <h2 class="accordion-header" id="{{ $headingId }}">
      <button
        class="accordion-button collapsed fw-semibold"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#{{ $collapseId }}"
        aria-expanded="false"
        aria-controls="{{ $collapseId }}"
      >
        <div class="koding-header-content">
          <div class="koding-order-code">{{ $order['kode'] }}</div>
          <div class="koding-company-name">{{ $order['perusahaan'] }}</div>
          <div class="koding-meta-row">
            <div class="koding-arrival-time">
              <i class="bi bi-clock"></i>
              <span data-relative-time data-time-unix="{{ $masukAtUnix ?: '' }}">-</span>
            </div>
          </div>
        </div>
      </button>
    </h2>
    <div
      id="{{ $collapseId }}"
      class="accordion-collapse collapse"
      aria-labelledby="{{ $headingId }}"
      data-bs-parent="#kodingAccordion"
    >
      <div class="accordion-body bg-white">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="small text-muted">Input koding per parameter dan lokasi.</div>
          <span class="badge text-bg-primary">Total koding: {{ $totalLokasi }} lokasi</span>
        </div>

        <div class="table-responsive mb-3">
          <table class="table table-sm align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:16%;">Lokasi</th>
                <th style="width:24%;">Dokumen</th>
                <th style="width:34%;">Parameter</th>
                <th style="width:26%;">Input Koding</th>
              </tr>
            </thead>
            <tbody>
              @foreach($lokasiRows as $row)
                @php
                  $itemRows = collect($row['rows'] ?? []);
                  $rowCount = max(1, $itemRows->count());
                  $docGroups = $itemRows->groupBy(function ($item) {
                    $fileSignature = collect($item['files'] ?? [])
                      ->map(fn ($file) => ($file['url'] ?? '') . '|' . ($file['name'] ?? ''))
                      ->implode('||');
                    $docId = $item['doc_id'] ?? null;
                    if (!empty($docId)) {
                      return 'id:' . $docId;
                    }
                    return 'sig:' . ($item['doc_label'] ?? '-') . '::' . $fileSignature;
                  })->values();
                @endphp
                @foreach($docGroups as $docGroup)
                  @php
                    $docItem = $docGroup->first();
                    $docRowCount = max(1, $docGroup->count());
                  @endphp
                  @foreach($docGroup as $item)
                    <tr>
                      @if($loop->parent->first && $loop->first)
                        <td class="fw-semibold align-middle" rowspan="{{ $rowCount }}">
                          <div class="koding-location-box">
                            <input
                              type="text"
                              class="form-control form-control-sm"
                              value="{{ $row['lokasi'] }}"
                              placeholder="Input lokasi"
                              data-location-name
                              data-location-id="{{ $row['lokasi_id'] ?? '' }}"
                            >
                          </div>
                        </td>
                      @endif
                      @if($loop->first)
                        <td rowspan="{{ $docRowCount }}">
                          <div class="fw-semibold">{{ $docItem['doc_label'] ?? 'Dokumen' }}</div>
                          <div class="small text-muted d-flex flex-column gap-1 mt-1">
                            @forelse(($docItem['files'] ?? []) as $file)
                              <span>
                                @if(!empty($file['url']))
                                  <a href="{{ $file['url'] }}" target="_blank" rel="noopener">{{ $file['name'] ?? 'File' }}</a>
                                @else
                                  {{ $file['name'] ?? 'File' }}
                                @endif
                              </span>
                            @empty
                              <span class="text-muted">Belum ada file.</span>
                            @endforelse
                          </div>
                        </td>
                      @endif
                      <td>
                        @if(!empty($item['has_param']))
                          <div class="small text-muted d-flex flex-column gap-1">
                            <span>
                              <span class="fw-semibold">{{ $item['param_category'] ?? '-' }}</span>
                              <span> - {{ $item['param_name'] ?? '-' }}</span>
                              @php
                                $method = strtolower((string) ($item['method'] ?? ''));
                                $methodClass = $method === 'direct' ? 'badge-method-direct' : ($method === 'indirect' ? 'badge-method-indirect' : 'bg-light text-dark border');
                              @endphp
                              <span class="badge {{ $methodClass }} ms-1">{{ $item['method'] ?? '-' }}</span>
                            </span>
                          </div>
                        @else
                          <span class="text-muted">Belum ada parameter.</span>
                        @endif
                      </td>
                      <td>
                        <div class="d-flex flex-column gap-1">
                          <input
                            type="text"
                            class="form-control form-control-sm"
                            placeholder="Contoh: j.01/046"
                            value="{{ $item['kode'] ?? '' }}"
                            title="Nomor koding dapat diedit secara bebas"
                            data-koding-input
                            data-param-id="{{ $item['param_id'] ?? '' }}"
                            data-base-code="{{ $item['base_code'] ?? '' }}"
                            data-is-indirect="{{ !empty($item['is_indirect']) ? '1' : '0' }}"
                            @if(empty($item['has_param'])) disabled @endif
                          >
                          @if(!empty($item['is_indirect']))
                            <div class="input-group input-group-sm">
                              <span class="input-group-text">Sample</span>
                              <input type="text" class="form-control" placeholder="" data-sample-input>
                            </div>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @endforeach
                @endforeach
                @if($itemRows->isEmpty())
                  <tr>
                    <td class="fw-semibold">
                      <div class="koding-location-box">
                        <input
                          type="text"
                          class="form-control form-control-sm"
                          value="{{ $row['lokasi'] }}"
                          placeholder="Input lokasi"
                          data-location-name
                          data-location-id="{{ $row['lokasi_id'] ?? '' }}"
                        >
                      </div>
                    </td>
                    <td class="text-muted">-</td>
                    <td class="text-muted">-</td>
                    <td>
                      <input type="text" class="form-control form-control-sm" placeholder="Contoh: j.01/046" disabled>
                    </td>
                  </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <div class="small text-muted">Tandai koding selesai untuk lanjut ke tahap berikutnya.</div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-action-save>Simpan</button>
            <button class="btn btn-primary btn-sm" type="button" data-action-submit>Tandai Koding Selesai</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="text-center text-muted py-4">Belum ada order untuk dikoding.</div>
  @endforelse
</div>

<div class="text-center text-muted d-none mt-3" data-search-empty>Tidak ada order yang cocok.</div>
@endsection

@push('scripts')
<script>
(() => {
  const kodeInput = document.querySelector('[data-search-kode]');
  const perusahaanInput = document.querySelector('[data-search-perusahaan]');
  const resetBtn = document.querySelector('[data-search-reset]');
  const cards = document.querySelectorAll('[data-card]');
  const emptyState = document.querySelector('[data-search-empty]');

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
  refreshRelativeTimes();

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

  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  const normalizeText = (value) => String(value || '').replace(/\s+/g, ' ').trim();

  const getPreviewRows = (card) => {
    const rows = [];
    let currentLocation = '';
    let currentDocument = '';

    card.querySelectorAll('tbody tr').forEach((tr) => {
      const cells = Array.from(tr.children);
      if (cells.length === 4) {
        currentLocation = normalizeText(cells[0].querySelector('[data-location-name]')?.value || cells[0].innerText);
        currentDocument = normalizeText(cells[1].querySelector('.fw-semibold')?.innerText || cells[1].innerText);
        rows.push({
          lokasi: currentLocation,
          dokumen: currentDocument,
          parameter: normalizeText(cells[2].innerText),
          koding: normalizeText(cells[3].querySelector('[data-koding-input]')?.value || '-'),
        });
        return;
      }

      if (cells.length === 3) {
        currentDocument = normalizeText(cells[0].querySelector('.fw-semibold')?.innerText || cells[0].innerText);
        rows.push({
          lokasi: currentLocation,
          dokumen: currentDocument,
          parameter: normalizeText(cells[1].innerText),
          koding: normalizeText(cells[2].querySelector('[data-koding-input]')?.value || '-'),
        });
        return;
      }

      if (cells.length === 2) {
        rows.push({
          lokasi: currentLocation,
          dokumen: currentDocument,
          parameter: normalizeText(cells[0].innerText),
          koding: normalizeText(cells[1].querySelector('[data-koding-input]')?.value || '-'),
        });
      }
    });

    return rows.filter((row) => row.parameter && row.parameter !== 'Belum ada parameter.');
  };

  const confirmSubmissionPreview = async (card) => {
    const kode = escapeHtml(card.getAttribute('data-kode-display') || card.getAttribute('data-kode') || '-');
    const perusahaan = escapeHtml(card.getAttribute('data-perusahaan-display') || card.getAttribute('data-perusahaan') || '-');
    const rows = getPreviewRows(card);
    const previewRows = rows.slice(0, 12);
    const remaining = rows.length - previewRows.length;

    if (window.Swal) {
      const tableRows = previewRows.map((row, index) => `
        <tr>
          <td style="padding:6px 8px;border:1px solid #dee2e6;vertical-align:top;">${index + 1}</td>
          <td style="padding:6px 8px;border:1px solid #dee2e6;vertical-align:top;">${escapeHtml(row.lokasi || '-')}</td>
          <td style="padding:6px 8px;border:1px solid #dee2e6;vertical-align:top;">${escapeHtml(row.dokumen || '-')}</td>
          <td style="padding:6px 8px;border:1px solid #dee2e6;vertical-align:top;">${escapeHtml(row.parameter || '-')}</td>
          <td style="padding:6px 8px;border:1px solid #dee2e6;vertical-align:top;">${escapeHtml(row.koding || '-')}</td>
        </tr>
      `).join('');

      const result = await Swal.fire({
        title: 'Pengecekan Data Koding',
        width: 760,
        html: `
          <div style="text-align:left;font-size:13px;line-height:1.45;">
            <div style="margin-bottom:10px;">
              <div><strong>Kode Order:</strong> ${kode}</div>
              <div><strong>Pelanggan:</strong> ${perusahaan}</div>
              <div><strong>Total Baris:</strong> ${rows.length}</div>
            </div>
            <div style="max-height:320px;overflow:auto;border:1px solid #dee2e6;border-radius:8px;">
              <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead style="position:sticky;top:0;background:#f8f9fa;">
                  <tr>
                    <th style="padding:6px 8px;border:1px solid #dee2e6;">No</th>
                    <th style="padding:6px 8px;border:1px solid #dee2e6;">Lokasi</th>
                    <th style="padding:6px 8px;border:1px solid #dee2e6;">Dokumen</th>
                    <th style="padding:6px 8px;border:1px solid #dee2e6;">Parameter</th>
                    <th style="padding:6px 8px;border:1px solid #dee2e6;">ID Koding</th>
                  </tr>
                </thead>
                <tbody>${tableRows || '<tr><td colspan="5" style="padding:10px;border:1px solid #dee2e6;text-align:center;font-size:12px;">Tidak ada data untuk dicek.</td></tr>'}</tbody>
              </table>
            </div>
            ${remaining > 0 ? `<div style="margin-top:8px;font-size:11px;color:#6c757d;">Menampilkan 12 baris pertama dari ${rows.length} baris.</div>` : ''}
            <div style="margin-top:10px;font-size:12px;">Pastikan data sudah benar. Jika sudah sesuai, lanjutkan ke tahap berikutnya.</div>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Benar, Lanjutkan',
        cancelButtonText: 'Periksa Lagi',
      });
      return result.isConfirmed;
    }

    return confirmAction('Periksa data koding terlebih dahulu. Jika sudah benar, lanjutkan?');
  };

  const validateKodingInputs = (card) => {
    const inputs = Array.from(card.querySelectorAll('[data-koding-input]'));
    const locationInputs = Array.from(card.querySelectorAll('[data-location-name]'));
    let missingKode = 0;
    let missingSample = 0;
    let missingLocation = 0;
    let duplicateKode = 0;

    const usedKodeMap = new Map();

    inputs.forEach((input) => {
      if (input.disabled) return;
      const paramId = input.getAttribute('data-param-id');
      if (!paramId) return;

      const value = (input.value || '').trim();
      const invalid = value.length === 0;

      if (invalid) {
        missingKode += 1;
        input.classList.add('is-invalid');
      } else {
        input.classList.remove('is-invalid');
        const lowerVal = value.toLowerCase();
        if (usedKodeMap.has(lowerVal)) {
          usedKodeMap.get(lowerVal).push(input);
        } else {
          usedKodeMap.set(lowerVal, [input]);
        }
      }

      const isIndirect = input.getAttribute('data-is-indirect') === '1';
      if (isIndirect) {
        const sampleInput = input.closest('td')?.querySelector('[data-sample-input]');
        if (sampleInput) {
          const sampleValue = (sampleInput.value || '').trim();
          const sampleInvalid = sampleValue.length === 0;
          sampleInput.classList.toggle('is-invalid', sampleInvalid);
          if (sampleInvalid) missingSample += 1;
        }
      }
    });

    usedKodeMap.forEach((inputGroup) => {
      if (inputGroup.length > 1) {
        duplicateKode += (inputGroup.length - 1);
        inputGroup.forEach((input) => input.classList.add('is-invalid'));
      }
    });

    locationInputs.forEach((input) => {
      const invalid = (input.value || '').trim().length === 0;
      input.classList.toggle('is-invalid', invalid);
      if (invalid) missingLocation += 1;
    });

    return {
      missingKode,
      missingSample,
      missingLocation,
      duplicateKode,
    };
  };

  const collectPayload = (card) => {
    const items = [];
    const locations = [];
    const inputs = Array.from(card.querySelectorAll('[data-koding-input]'));
    const locationInputs = Array.from(card.querySelectorAll('[data-location-name]'));
    inputs.forEach((input) => {
      const paramId = input.getAttribute('data-param-id');
      if (!paramId) return;
      items.push({
        pengujian_dokumen_parameter_id: Number(paramId),
        kode: (input.value || '').trim(),
      });
    });
    locationInputs.forEach((input) => {
      const locationId = Number(input.getAttribute('data-location-id') || 0);
      if (!locationId) return;
      locations.push({
        id: locationId,
        name: (input.value || '').trim(),
      });
    });
    return { items, locations };
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
      throw new Error(data.message || 'Gagal menyimpan koding.');
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

  cards.forEach((card) => {
    const saveBtn = card.querySelector('[data-action-save]');
    const submitBtn = card.querySelector('[data-action-submit]');
    const saveUrl = card.getAttribute('data-save-url') || '';
    const submitUrl = card.getAttribute('data-submit-url') || '';

    if (saveBtn && saveUrl) {
      saveBtn.addEventListener('click', async () => {
        const validation = validateKodingInputs(card);
        if (validation.missingLocation > 0) {
          await notify('error', 'Nama lokasi wajib diisi sebelum simpan.');
          return;
        }
        if (validation.duplicateKode > 0) {
          await notify('error', 'Terdapat nomor koding duplikat. Mohon periksa kembali.');
          return;
        }
        try {
          setCardLoading(card, true, saveBtn);
          const data = await sendPayload(card, saveUrl);
          await notify('success', data.message || 'Koding berhasil disimpan.');
        } catch (error) {
          await notify('error', error.message || 'Gagal menyimpan koding.');
        } finally {
          setCardLoading(card, false);
        }
      });
    }

    if (submitBtn && submitUrl) {
      submitBtn.addEventListener('click', async () => {
        const validation = validateKodingInputs(card);
        if (validation.missingLocation > 0) {
          await notify('error', 'Nama lokasi wajib diisi sebelum lanjut.');
          return;
        }
        if (validation.missingKode > 0) {
          await notify('error', 'Masih ada koding kosong. Lengkapi semua ID koding sebelum lanjut.');
          return;
        }
        if (validation.duplicateKode > 0) {
          await notify('error', 'Terdapat nomor koding duplikat. Pastikan setiap koding unik.');
          return;
        }
        if (validation.missingSample > 0) {
          await notify('error', 'Nomor sampel untuk parameter indirect wajib diisi sebelum lanjut.');
          return;
        }
        const ok = await confirmSubmissionPreview(card);
        if (!ok) return;
        try {
          setCardLoading(card, true, submitBtn);
          const data = await sendPayload(card, submitUrl);
          await notify('success', data.message || 'Koding berhasil diteruskan.');
          window.location.reload();
        } catch (error) {
          await notify('error', error.message || 'Gagal meneruskan koding.');
        } finally {
          setCardLoading(card, false);
        }
      });
    }

    card.querySelectorAll('[data-location-name]').forEach((input) => {
      input.addEventListener('input', () => {
        if ((input.value || '').trim() !== '') {
          input.classList.remove('is-invalid');
        }
      });
    });

    const applyAutoCode = (input) => {
      if (!input || input.disabled) return;
      const base = input.getAttribute('data-base-code') || '';
      const isIndirect = input.getAttribute('data-is-indirect') === '1';
      const sampleInput = input.closest('td')?.querySelector('[data-sample-input]');
      const current = (input.value || '').trim();

      // Jika input masih kosong saat pertama kali dimuat, gunakan nilai awal dari database/backend
      if (!current && base && !input.dataset.userEdited) {
        input.value = base;
      }

      // Jika parameter bertipe indirect dan terdapat nilai input sampel
      if (isIndirect && sampleInput) {
        const sample = (sampleInput.value || '').trim();
        if (sample) {
          if (!input.dataset.userEdited && base) {
            input.value = `${base}.${sample}`;
          } else if (current && !current.endsWith(`.${sample}`)) {
            input.value = `${current}.${sample}`;
          }
        }
      }
    };

    card.querySelectorAll('[data-koding-input]').forEach((input) => {
      // Inisialisasi awal nilai koding saat halaman dimuat
      applyAutoCode(input);

      input.addEventListener('input', () => {
        // Tandai bahwa pegawai telah mengedit input secara manual
        input.dataset.userEdited = 'true';
        if ((input.value || '').trim() !== '') {
          input.classList.remove('is-invalid');
        }
      });
    });

    card.querySelectorAll('[data-sample-input]').forEach((sampleInput) => {
      sampleInput.addEventListener('input', () => {
        if ((sampleInput.value || '').trim() !== '') {
          sampleInput.classList.remove('is-invalid');
        }
        const input = sampleInput.closest('td')?.querySelector('[data-koding-input]');
        if (input) applyAutoCode(input);
      });
    });
  });
})();
</script>
@endpush