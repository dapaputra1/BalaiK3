
@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
  $routePrefix = $routePrefix ?? 'superadmin';
@endphp

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Draft LHU',
  'subtitle' => 'Draft LHU berbasis dokumen. Akses editor dibuka setelah indirect selesai diverifikasi.',
  'total' => $orders->count(),
])

<style>
  .lhu-doc-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; background: #fff; }
  .lhu-doc-panel { border: 1px solid #dbe3ef; border-radius: 12px; padding: 12px; }
  .lhu-table input { min-width: 120px; }
  .lhu-col-number {
    width: 64px;
    min-width: 64px;
    max-width: 64px;
  }
  .lhu-col-number input {
    text-align: center;
  }
  .lhu-table thead th {
    background: #f8f9fa;
    font-weight: 400;
    vertical-align: middle;
  }
  .lhu-table thead tr:first-child th {
    background: #f8f9fa;
  }
  .lhu-table thead input.form-control {
    font-weight: 400;
    background: #fff;
    border-color: #d8dde3;
  }
  .lhu-table thead th.lhu-group-header {
    text-align: justify;
    text-align-last: center;
    text-justify: inter-word;
  }
  .lhu-meteo-table thead th {
    font-weight: 400;
    text-align: justify;
    text-align-last: center;
    text-justify: inter-word;
    background: #f8f9fa;
    vertical-align: middle;
  }
  #draftLhuHasilModal .doc-block {
    border: 1px dashed #c7dcf7;
    border-radius: 8px;
    padding: 8px;
    background: #f8fbff;
  }
  #draftLhuHasilModal .doc-label {
    font-size: 12px;
    font-weight: 600;
    color: #123A63;
    margin-bottom: 6px;
  }
  #draftLhuHasilModal .doc-row {
    min-height: 28px;
    display: flex;
    align-items: center;
  }
  #draftLhuHasilModal .table-responsive {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    background: #fff;
  }
  #draftLhuHasilModal table {
    width: 100%;
    margin-bottom: 0;
    table-layout: fixed;
  }
  #draftLhuHasilModal th,
  #draftLhuHasilModal td {
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  #draftLhuAccordion .accordion-button {
    background-color: #15406a !important;
    color: #fff;
  }
  #draftLhuAccordion .accordion-button:not(.collapsed) {
    background-color: #15406a !important;
    color: #fff;
    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
  }
  #draftLhuAccordion .accordion-button::after {
    filter: brightness(0) invert(1);
  }
  .draft-lhu-header-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 4px;
    position: relative;
    padding-right: 170px;
  }
  .draft-lhu-order-code {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
  }
  .draft-lhu-company-name {
    font-size: 13px;
    font-weight: 400;
    line-height: 1.2;
  }
  .draft-lhu-meta-row {
    position: absolute;
    right: 34px;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    line-height: 1.2;
    color: #fff;
    white-space: nowrap;
  }
  .draft-lhu-meta-row i {
    font-size: 11px;
    margin-right: 4px;
  }
  .draft-lhu-search-wrap {
    background: #eef2f7;
    border: 1px solid #d7e1ee;
    border-radius: 16px;
    padding: 10px;
    margin-bottom: 16px;
  }
  .draft-lhu-search-wrap .input-group-text {
    background: #fff;
    border-color: #b8cbe2;
    border-right: 0;
    border-radius: 12px 0 0 12px;
    color: #6c7f96;
  }
  .draft-lhu-search-wrap .form-control {
    border-color: #b8cbe2;
    border-left: 0;
    border-radius: 0 12px 12px 0;
    min-height: 40px;
    box-shadow: none !important;
    font-size: 14px;
  }
  .draft-lhu-search-wrap .form-control:focus {
    border-color: #95b6da;
  }
  .draft-lhu-search-wrap .btn-search-reset {
    min-height: 40px;
    border-radius: 12px;
    border: 1px solid #15406A;
    color: #15406A;
    background: #fff;
    font-weight: 600;
  }
  .draft-lhu-search-wrap .btn-search-reset:hover {
    background: #f3f8ff;
  }
  .draft-lhu-empty {
    text-align: center;
    color: #6c757d;
    padding: 12px 0;
  }
  .btn-hasil-pengujian {
    background: #15406A;
    border-color: #15406A;
    color: #fff;
    font-weight: 600;
  }
  .btn-hasil-pengujian:hover,
  .btn-hasil-pengujian:focus {
    background: #0f3253;
    border-color: #0f3253;
    color: #fff;
  }
  .btn-hasil-pengujian:disabled {
    background: #8aa3bd;
    border-color: #8aa3bd;
    color: #f8f9fa;
    opacity: 1;
  }
  @media (max-width: 575.98px) {
    .draft-lhu-header-content {
      padding-right: 0;
    }
    .draft-lhu-meta-row {
      position: static;
      margin-top: 2px;
      transform: none;
    }
    .draft-lhu-search-wrap .form-control,
    .draft-lhu-search-wrap .btn-search-reset {
      min-height: 38px;
    }
  }
</style>

<div class="draft-lhu-search-wrap">
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

<div class="accordion" id="draftLhuAccordion">
  @forelse($orders as $order)
    @php $accId = 'draftLhu-' . ($order['permohonan_id'] ?? uniqid()); @endphp
    <div class="accordion-item border-0 shadow-sm rounded-4 mb-3 overflow-hidden"
         data-card
         data-kode="{{ strtolower((string) ($order['kode'] ?? '')) }}"
         data-perusahaan="{{ strtolower((string) ($order['perusahaan'] ?? '')) }}">
      <h2 class="accordion-header" id="{{ $accId }}-header">
        <button class="accordion-button collapsed rounded-4 bg-primary text-white fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accId }}-collapse" style="background-color:#15406A !important;">
          <div class="draft-lhu-header-content pe-3">
            <div class="draft-lhu-order-code">{{ $order['kode'] ?? '-' }}</div>
            <div class="draft-lhu-company-name">{{ $order['perusahaan'] ?? '-' }}</div>
            <div class="draft-lhu-meta-row">
              <i class="bi bi-clock"></i>
              <span data-relative-time data-time-unix="{{ (int) ($order['masuk_at_unix'] ?? 0) }}">-</span>
            </div>
          </div>
        </button>
      </h2>
      <div id="{{ $accId }}-collapse" class="accordion-collapse collapse" data-bs-parent="#draftLhuAccordion">
        <div class="accordion-body">
          <div class="row g-2 mb-3">
            <div class="col-md-4"><div class="text-muted small">Lokasi</div><div class="fw-semibold">{{ $order['lokasi'] ?? '-' }}</div></div>
            <div class="col-md-4"><div class="text-muted small">Indirect</div><div class="fw-semibold">{{ ($order['has_indirect'] ?? false) ? 'Ada' : 'Tidak ada' }}</div></div>
            <div class="col-md-4">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                  <div class="text-muted small">Status Indirect</div>
                  <div class="fw-semibold">
                    @if(!($order['has_indirect'] ?? false))
                      Tidak ada
                    @elseif($order['indirect_done'] ?? false)
                      Selesai
                    @else
                      Belum selesai
                    @endif
                  </div>
                </div>
                <button type="button" class="btn btn-sm btn-hasil-pengujian" data-open-hasil-pengujian-order data-order-id="{{ $order['permohonan_id'] }}">
                  Lihat Hasil Pengujian
                </button>
              </div>
            </div>
          </div>

          @if(!empty($order['revisi_notes']) || !empty($order['qc_revisi_file_url']))
            <div class="alert alert-warning border border-warning-subtle rounded-3 py-2 px-3 mb-3">
              <div class="fw-semibold text-danger mb-1">Catatan Revisi QC LHU</div>
              @if(!empty($order['revisi_notes']))
                <ul class="mb-1 ps-3">
                  @foreach(($order['revisi_notes'] ?? []) as $note)
                    <li class="small">{{ $note }}</li>
                  @endforeach
                </ul>
              @endif
              @if(!empty($order['qc_revisi_file_url']))
                <div class="small">
                  Dokumen revisi:
                  <a href="{{ $order['qc_revisi_file_url'] }}" target="_blank" rel="noopener">
                    {{ $order['qc_revisi_file_name'] ?? 'Lihat dokumen revisi' }}
                  </a>
                  @if(!empty($order['qc_revisi_file_uploaded_at']))
                    <span class="text-muted">({{ $order['qc_revisi_file_uploaded_at'] }})</span>
                  @endif
                </div>
              @endif
            </div>
          @endif

          @if(!empty($order['locked']))
            <div class="alert alert-warning">Indirect belum selesai diverifikasi. Editor LHU terkunci.</div>
          @endif

          <div class="lhu-doc-panel mb-3"
               data-order-editor
               data-order-id="{{ $order['permohonan_id'] }}"
               data-locked="{{ !empty($order['locked']) ? '1' : '0' }}"
               data-save-url="{{ $order['save_url'] ?? '' }}"
               data-ai-url="{{ $order['ai_summary_url'] ?? '' }}"
               data-word-url="{{ $order['word_url'] ?? '' }}"
               data-word-all-url="{{ $order['word_all_url'] ?? '' }}">

            <div class="d-flex justify-content-between align-items-center mb-2">
              <div class="fw-semibold">Dokumen LHU</div>
              <button type="button" class="btn btn-outline-primary btn-sm" data-add-doc @if(!empty($order['locked'])) disabled @endif>Tambah Dokumen</button>
            </div>

            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
              <button type="button" class="btn btn-outline-success btn-sm" data-save-docs @if(!empty($order['locked'])) disabled @endif>Simpan Draft</button>
              <button type="button" class="btn btn-outline-secondary btn-sm" data-download-word-all @if(!empty($order['locked'])) disabled @endif>Generate Word Gabungan</button>
              <button type="button" class="btn btn-outline-danger btn-sm" data-reset-docs @if(!empty($order['locked'])) disabled @endif>Reset Tabel</button>
            </div>

            <div class="d-grid gap-3" data-doc-list></div>

            <script type="application/json" data-documents-initial>@json($order['documents'] ?? [])</script>
            <script type="application/json" data-catalog-initial>@json($order['selection_catalog'] ?? ['locations'=>[],'parameters'=>[],'location_param_map'=>[]])</script>
            <script type="application/json" data-indirect-rows-initial>@json($order['indirect_rows'] ?? [])</script>
            <script type="application/json" data-detail-rows-initial>@json($order['detail_rows'] ?? [])</script>
          </div>

          <div class="border rounded-3 p-3 mt-3 bg-light" data-final-lhu-section>
            <div class="fw-semibold mb-2">Upload LHU Jadi (1 file per permohonan)</div>
            <div class="row g-2 align-items-end">
              <div class="col-12"><input type="file" class="form-control form-control-sm" data-final-lhu-input data-upload-url="{{ $order['final_lhu_upload_url'] ?? '' }}" accept=".pdf,.doc,.docx"></div>
            </div>
            <div class="small mt-2" data-final-lhu-info>
              @if(!empty($order['final_lhu_name']))
                File saat ini: <a href="{{ $order['final_lhu_show_url'] ?? '#' }}" target="_blank" rel="noopener">{{ $order['final_lhu_name'] }}</a>
              @else
                <span class="text-muted">Belum ada file LHU jadi.</span>
              @endif
            </div>
          </div>

          <div class="d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-primary btn-sm" data-submit-url="{{ route($routePrefix . '.draft-lhu.submit', $order['permohonan_id']) }}" @if((!empty($order['locked'])) || empty($order['final_lhu_ready'])) disabled @endif>Lanjut ke QC LHU</button>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-muted py-4">Belum ada data draft LHU.</div></div>
  @endforelse
</div>
<div class="draft-lhu-empty d-none" data-search-empty>Tidak ada order yang cocok.</div>

<div class="modal fade" id="draftLhuHasilModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Hasil Pengujian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="small text-muted mb-2" data-hasil-meta>-</div>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:5%;">No</th>
                <th style="width:14%;">Lokasi</th>
                <th style="width:20%;">Dokumen</th>
                <th style="width:20%;">Parameter</th>
                <th style="width:11%;">Metode</th>
                <th style="width:12%;">Status</th>
                <th style="width:18%;">Catatan</th>
              </tr>
            </thead>
            <tbody data-hasil-body></tbody>
          </table>
        </div>
        <div class="text-muted small d-none mt-2" data-hasil-empty>Belum ada data hasil pengujian untuk dokumen ini.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const notify = window.notify || (async (type, message) => {
    if (window.Swal) {
      const iconMap = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
      const titleMap = { success: 'Berhasil', error: 'Gagal', warning: 'Peringatan', info: 'Info' };
      await window.Swal.fire({
        icon: iconMap[type] || 'info',
        title: titleMap[type] || 'Info',
        text: String(message || ''),
        confirmButtonText: 'OK',
      });
      return;
    }
    alert(String(message || ''));
  });
  const confirmAction = window.confirmAction || (async (message) => {
    if (window.Swal) {
      const res = await window.Swal.fire({
        icon: 'question',
        title: 'Konfirmasi',
        text: String(message || ''),
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
      });
      return !!res.isConfirmed;
    }
    return confirm(String(message || ''));
  });
  const parseJsonSafe = (raw, fallback) => {
    try {
      const parsed = JSON.parse(raw || '');
      return parsed ?? fallback;
    } catch (_e) {
      return fallback;
    }
  };
  const escapeHtml = (value) => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');

  const clone = (obj) => JSON.parse(JSON.stringify(obj));
  const formatRelativeTime = (unix) => {
    const ts = Number(unix || 0);
    if (!ts) return '-';
    const now = Math.floor(Date.now() / 1000);
    let diff = now - ts;
    if (diff < 0) diff = 0;
    if (diff < 60) return 'baru saja';
    if (diff < 3600) {
      const m = Math.floor(diff / 60);
      return `${m} menit yang lalu`;
    }
    if (diff < 86400) {
      const h = Math.floor(diff / 3600);
      return `${h} jam yang lalu`;
    }
    const d = Math.floor(diff / 86400);
    return `${d} hari yang lalu`;
  };
  const updateRelativeTimes = () => {
    document.querySelectorAll('[data-relative-time]').forEach((el) => {
      const ts = Number(el.getAttribute('data-time-unix') || 0);
      el.textContent = formatRelativeTime(ts);
    });
  };
  updateRelativeTimes();
  const kodeInput = document.querySelector('[data-search-kode]');
  const perusahaanInput = document.querySelector('[data-search-perusahaan]');
  const resetSearchBtn = document.querySelector('[data-search-reset]');
  const cards = document.querySelectorAll('[data-card]');
  const emptyState = document.querySelector('[data-search-empty]');
  const filterCards = () => {
    const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
    const perusahaanVal = (perusahaanInput?.value || '').toLowerCase().trim();
    let visible = 0;
    cards.forEach((card) => {
      const kode = (card.getAttribute('data-kode') || '').toLowerCase();
      const perusahaan = (card.getAttribute('data-perusahaan') || '').toLowerCase();
      const show = (!kodeVal || kode.includes(kodeVal)) && (!perusahaanVal || perusahaan.includes(perusahaanVal));
      card.classList.toggle('d-none', !show);
      if (show) visible += 1;
    });
    if (emptyState) {
      emptyState.classList.toggle('d-none', visible > 0 || cards.length === 0);
    }
  };
  kodeInput?.addEventListener('input', filterCards);
  perusahaanInput?.addEventListener('input', filterCards);
  resetSearchBtn?.addEventListener('click', () => {
    if (kodeInput) kodeInput.value = '';
    if (perusahaanInput) perusahaanInput.value = '';
    filterCards();
  });
  filterCards();
  const parseColumnGroup = (raw) => {
    const text = String(raw || '');
    const parts = text.split('::');
    if (parts.length >= 2) {
      const parent = String(parts[0] || '').trim();
      const child = String(parts.slice(1).join('::') || '').trim();
      if (parent) {
        return { grouped: true, parent, child: child || 'Subkolom' };
      }
    }
    return { grouped: false, parent: '', child: String(text || '').trim() };
  };
  const isParameterColumnLabel = (raw) => {
    const meta = parseColumnGroup(raw);
    const base = String(meta.child || '').trim().toLowerCase();
    return base === 'parameter';
  };
  const isNabColumnLabel = (raw) => {
    const meta = parseColumnGroup(raw);
    const base = String(meta.child || '').trim().toLowerCase();
    return base === 'nab';
  };
  const normalizeName = (value) => String(value || '').trim().toLowerCase().replace(/\s+/g, ' ');
  const isNumberColumnLabel = (raw) => {
    const meta = parseColumnGroup(raw);
    const base = String(meta.child || '').trim().toLowerCase();
    return base === 'no' || base === 'no.';
  };
  const isIndirectCategoryColumnLabel = (raw) => {
    const meta = parseColumnGroup(raw);
    return String(meta.child || '').trim().toLowerCase() === 'kategori';
  };
  const isIndirectAnalysisColumnLabel = (raw) => {
    const meta = parseColumnGroup(raw);
    const base = String(meta.child || '').trim().toLowerCase();
    return base === 'hasil analisa' || base === 'kadar';
  };
  const sanitizeTable = (table) => {
    const src = table && typeof table === 'object' ? table : {};
    const columns = Array.isArray(src.columns) ? src.columns.map((c) => String(c || '').trim()).filter(Boolean) : [];
    const cols = columns.length ? columns : ['Parameter', 'Hasil Pengukuran'];
    const rows = Array.isArray(src.rows) ? src.rows.filter(Array.isArray).map((r) => cols.map((_, i) => String(r[i] || ''))) : [];
    return { columns: cols, rows: rows.length ? rows : [cols.map(() => '')] };
  };
  const hasMeaningfulTableRows = (table, options = {}) => {
    const t = sanitizeTable(table);
    const columns = Array.isArray(t.columns) ? t.columns : [];
    const rows = Array.isArray(t.rows) ? t.rows : [];
    const includeNab = !!options.includeNab;
    const valueIndexes = columns
      .map((col, idx) => ({ col, idx }))
      .filter(({ col }) => {
        if (isNumberColumnLabel(col) || isParameterColumnLabel(col)) return false;
        if (!includeNab && isNabColumnLabel(col)) return false;
        return true;
      })
      .map(({ idx }) => idx);
    const effectiveIndexes = valueIndexes.length ? valueIndexes : columns.map((_, idx) => idx);
    return rows.some((row) => effectiveIndexes.some((idx) => String(row?.[idx] || '').trim() !== ''));
  };

  const states = new Map();

  const typeLabel = (type) => ({1:'Tipe 1',2:'Tipe 2',3:'Tipe 3'})[Number(type)] || 'Tipe';
  const nextDocNumber = (documents) => {
    const used = (documents || [])
      .map((d) => {
        const m = String(d?.title || '').match(/^Dokumen\s+(\d+)$/i);
        return m ? Number(m[1]) : null;
      })
      .filter((n) => Number.isInteger(n) && n > 0);
    let n = 1;
    while (used.includes(n)) n += 1;
    return n;
  };

  const buildDefaultTable = (doc, catalog) => {
    const type = Number(doc.type || 1);
    if (type === 1) {
      const rows = (doc.parameter_ids || []).map((pid) => [catalog.parameters.find((p) => Number(p.id) === Number(pid))?.name || '-', '', '']);
      return { columns: ['Parameter', 'Hasil Pengukuran', 'NAB'], rows: rows.length ? rows : [['', '', '']] };
    }
    if (type === 2) {
      const rows = (doc.lokasi_ids || []).map((lid) => [catalog.locations.find((l) => Number(l.id) === Number(lid))?.name || '-', '', '']);
      return { columns: ['Lokasi', 'Hasil Pengukuran', 'NAB'], rows: rows.length ? rows : [['', '', '']] };
    }
    return { columns: ['Lokasi', 'Parameter', 'Hasil Pengukuran', 'NAB'], rows: [['', '', '', '']] };
  };
  const ensureDirectTableHasNab = (doc, table) => {
    const type = Number(doc?.type || 1);
    const normalized = sanitizeTable(table || {});
    const targetColumns = type === 1
      ? ['Parameter', 'Hasil Pengukuran', 'NAB']
      : (type === 2 ? ['Lokasi', 'Hasil Pengukuran', 'NAB'] : ['Lokasi', 'Parameter', 'Hasil Pengukuran', 'NAB']);
    const columns = Array.isArray(normalized.columns) && normalized.columns.length
      ? [...normalized.columns]
      : [...targetColumns];
    let nabIndex = columns.findIndex((col) => isNabColumnLabel(col));
    if (nabIndex < 0) {
      columns.push('NAB');
      nabIndex = columns.length - 1;
    }

    const sourceRows = Array.isArray(normalized.rows) ? normalized.rows : [];
    const rows = sourceRows.map((row) => {
      const next = Array.isArray(row) ? [...row] : [];
      while (next.length < columns.length) next.push('');
      return next.slice(0, columns.length);
    });
    if (!rows.length) {
      rows.push(Array.from({ length: columns.length }, () => ''));
    }
    rows.forEach((row) => {
      if (typeof row[nabIndex] === 'undefined') row[nabIndex] = '';
    });

    return { columns, rows };
  };
  const ensureIndirectTableHasNab = (table, nabValues = {}) => {
    const normalized = sanitizeTable(table || {});
    const baseColumns = Array.isArray(normalized.columns) && normalized.columns.length
      ? [...normalized.columns]
      : ['No', 'Parameter', 'Hasil Analisa', 'NAB'];
    const sourceColumns = baseColumns
      .filter((col) => !isIndirectCategoryColumnLabel(col))
      .map((col) => {
        if (isNumberColumnLabel(col)) return 'No';
        if (isIndirectAnalysisColumnLabel(col)) return 'Hasil Analisa';
        if (isParameterColumnLabel(col)) return 'Parameter';
        return String(col || '').trim();
      });
    const columns = [];
    sourceColumns.forEach((col) => {
      if (!col) return;
      if ((col === 'No' || col === 'Parameter' || col === 'Hasil Analisa' || col === 'NAB') && columns.includes(col)) return;
      columns.push(col);
    });
    let parameterIndex = columns.findIndex((col) => isParameterColumnLabel(col));
    if (parameterIndex < 0) parameterIndex = 2;
    let nabIndex = columns.findIndex((col) => isNabColumnLabel(col));
    if (nabIndex < 0) {
      columns.push('NAB');
      nabIndex = columns.length - 1;
    }
    const cleanNabValues = nabValues && typeof nabValues === 'object' ? nabValues : {};
    const sourceRows = Array.isArray(normalized.rows) ? normalized.rows : [];
    const rows = sourceRows.map((row) => {
      const rawRow = Array.isArray(row) ? [...row] : [];
      const next = columns.map((col) => {
        const sourceIndex = sourceColumns.findIndex((sourceCol) => sourceCol === col);
        return String(rawRow[sourceIndex] || '');
      });
      while (next.length < columns.length) next.push('');
      const paramKey = normalizeName(next[parameterIndex] || '');
      if (paramKey && Object.prototype.hasOwnProperty.call(cleanNabValues, paramKey)) {
        next[nabIndex] = String(cleanNabValues[paramKey] || '');
      } else {
        next[nabIndex] = String(next[nabIndex] || '');
      }
      return next.slice(0, columns.length);
    });
    return { columns, rows };
  };
  const syncIndirectNabValuesFromTable = (doc) => {
    const table = ensureIndirectTableHasNab(doc?.indirect_table || {}, doc?.indirect_nab_values || {});
    const parameterIndex = table.columns.findIndex((col) => isParameterColumnLabel(col));
    const nabIndex = table.columns.findIndex((col) => isNabColumnLabel(col));
    if (parameterIndex < 0 || nabIndex < 0) return;
    doc.indirect_nab_values = doc.indirect_nab_values && typeof doc.indirect_nab_values === 'object'
      ? doc.indirect_nab_values
      : {};
    table.rows.forEach((row) => {
      const key = normalizeName(row?.[parameterIndex] || '');
      if (!key) return;
      doc.indirect_nab_values[key] = String(row?.[nabIndex] || '');
    });
    doc.indirect_table = table;
  };
  const normalizeDocumentForState = (doc) => {
    const normalizedDoc = {
      ...doc,
      table: ensureDirectTableHasNab(doc, doc?.table || {}),
      indirect_nab_values: doc?.indirect_nab_values && typeof doc.indirect_nab_values === 'object'
        ? { ...doc.indirect_nab_values }
        : {},
      indirect_table_manual: !!doc?.indirect_table_manual,
    };
    if (normalizedDoc.indirect_table_manual) {
      normalizedDoc.indirect_table = ensureIndirectTableHasNab(doc?.indirect_table || {}, normalizedDoc.indirect_nab_values);
      syncIndirectNabValuesFromTable(normalizedDoc);
    } else {
      normalizedDoc.indirect_table = hasMeaningfulTableRows(doc?.indirect_table || {}, { includeNab: true })
        ? ensureIndirectTableHasNab(doc?.indirect_table || {}, normalizedDoc.indirect_nab_values)
        : { columns: ['No', 'Parameter', 'Hasil Analisa', 'NAB'], rows: [] };
    }
    return normalizedDoc;
  };
  const normalizeDocumentsForState = (documents) => (Array.isArray(documents) ? documents : []).map((d) => normalizeDocumentForState({
    ...d,
    is_new: false,
  }));
  const buildMeteorologyTemplate = (type) => {
    if (Number(type) === 1) {
      return {
        type: 1,
        table: {
          columns: ['No', 'Parameter', 'Hasil Pengujian::1', 'Hasil Pengujian::2', 'Hasil Pengujian::3', 'Satuan'],
          rows: [
            ['1', 'Suhu Udara', '29,0', '29,0', '29,0', 'oC'],
            ['2', 'Kelembaban Nisbi (RH)', '76,0', '76,0', '76,0', '%'],
            ['3', 'Kecepatan Angin', '0,98', '0,98', '0,98', 'm/dtk'],
            ['4', 'Arah Angin Ke', 'Barat', 'Barat', 'Barat', '-'],
            ['5', 'Cuaca', 'Cerah', 'Cerah', 'Cerah', '-'],
          ],
        },
      };
    }
    return {
      type: 2,
      table: {
        columns: ['No.', 'Parameter', 'Satuan', 'Hasil Pengukuran'],
        rows: [
          ['1.', 'Suhu Udara', 'oC', '29,0'],
          ['2.', 'Kelembaban ( RH )', '%', '76,0'],
        ],
      },
    };
  };

  const buildIndirectTable = (doc, rows) => {
    const lokasi = (doc.lokasi_ids || []).map(Number);
    const params = (doc.parameter_ids || []).map(Number);
    const filtered = (rows || []).filter((r) => lokasi.includes(Number(r.lokasi_id)) && params.includes(Number(r.parameter_id)));
    if (!filtered.length) {
      return { columns: ['No', 'Parameter', 'Hasil Analisa', 'NAB'], rows: [] };
    }

    const parameterLocations = new Map();
    filtered.forEach((entry) => {
      const parameter = String(entry?.parameter_name || '-').trim() || '-';
      const lokasiName = String(entry?.lokasi_name || '-').trim() || '-';
      if (!parameterLocations.has(parameter)) parameterLocations.set(parameter, new Set());
      parameterLocations.get(parameter).add(lokasiName);
    });

    const isUnitHeader = (raw) => {
      const label = String(raw || '').trim().toLowerCase();
      return label.includes('satuan') || label.includes('unit');
    };
    const isValueHeader = (raw) => {
      const label = String(raw || '').trim().toLowerCase();
      return label.includes('kadar')
        || label.includes('hasil')
        || label.includes('konsentrasi')
        || label.includes('concentration')
        || label.includes('conc');
    };
    const isMetaHeader = (raw) => {
      const label = String(raw || '').trim().toLowerCase();
      return !label
        || label === 'no'
        || label === 'no.'
        || label.includes('parameter')
        || label.includes('lokasi')
        || label.includes('sampel')
        || label.includes('sample')
        || label.includes('koding')
        || label.includes('metode')
        || label.includes('satuan')
        || label.includes('unit');
    };
    const extractMeasurements = (entry) => {
      const dataset = entry?.dataset;
      if (!dataset || !Array.isArray(dataset.columns) || !Array.isArray(dataset.rows)) return [];
      if (!dataset.columns.length || !dataset.rows.length) return [];

      const columns = dataset.columns.map((c) => String(c || ''));
      const valueIndexes = [];
      const unitIndexes = [];
      columns.forEach((col, idx) => {
        if (isUnitHeader(col)) {
          unitIndexes.push(idx);
          return;
        }
        if (isValueHeader(col)) valueIndexes.push(idx);
      });
      if (!valueIndexes.length) {
        columns.forEach((col, idx) => {
          if (!isMetaHeader(col)) valueIndexes.push(idx);
        });
      }

      const values = [];
      (dataset.rows || []).forEach((row) => {
        if (!Array.isArray(row)) return;
        let unit = '';
        unitIndexes.some((uIdx) => {
          const raw = String(row[uIdx] || '').trim();
          if (!raw) return false;
          unit = raw;
          return true;
        });
        valueIndexes.forEach((vIdx) => {
          const raw = String(row[vIdx] || '').trim();
          if (!raw) return;
          const header = String(columns[vIdx] || '').trim() || 'Kadar';
          let text = raw;
          if (unit && !raw.toLowerCase().includes(unit.toLowerCase())) {
            text = `${raw} ${unit}`;
          }
          values.push({ header, value: text.trim() });
        });
      });
      return values;
    };

    const parameterOrder = [];
    const grouped = new Map();
    const headerOrder = [];
    filtered.forEach((entry) => {
      const parameter = String(entry?.parameter_name || '-').trim() || '-';
      const lokasiName = String(entry?.lokasi_name || '-').trim() || '-';
      if (!grouped.has(parameter)) {
        grouped.set(parameter, new Map());
        parameterOrder.push(parameter);
      }
      const needLokasiPrefix = (parameterLocations.get(parameter)?.size || 0) > 1;
      let values = extractMeasurements(entry);
      if (!values.length) {
        const fallback = String(entry?.hasil || '').trim();
        if (fallback) values = [{ header: 'Hasil Analisa', value: fallback }];
      }
      values.forEach((item) => {
        const header = isIndirectAnalysisColumnLabel(item?.header || '')
          ? 'Hasil Analisa'
          : (String(item?.header || 'Hasil Analisa').trim() || 'Hasil Analisa');
        const val = String(item?.value || '').trim();
        if (!val) return;
        if (!headerOrder.includes(header)) headerOrder.push(header);
        const display = needLokasiPrefix && lokasiName !== '-' ? `${lokasiName}: ${val}` : val;
        const byHeader = grouped.get(parameter);
        if (!byHeader.has(header)) byHeader.set(header, []);
        const bucket = byHeader.get(header);
        if (!bucket.includes(display)) bucket.push(display);
      });
    });

    const effectiveHeaders = (headerOrder.length ? headerOrder : ['Hasil Analisa']).map((header) => (
      isIndirectAnalysisColumnLabel(header) ? 'Hasil Analisa' : header
    ));
    const columns = ['No', 'Parameter', ...effectiveHeaders, 'NAB'];
    const nabValues = doc?.indirect_nab_values && typeof doc.indirect_nab_values === 'object'
      ? doc.indirect_nab_values
      : {};
    const tableRows = parameterOrder.map((parameter, idx) => {
      const byHeader = grouped.get(parameter) || new Map();
      const cells = effectiveHeaders.map((header) => String((byHeader.get(header) || []).join(' | ')));
      const nabKey = normalizeName(parameter);
      return [String(idx + 1), parameter, ...cells, String(nabValues?.[nabKey] || '')];
    });
    return { columns, rows: tableRows };
  };
  const collectDocPengujianRows = (doc, state) => {
    const lokasiIds = (doc?.lokasi_ids || []).map(Number);
    const parameterIds = (doc?.parameter_ids || []).map(Number);
    const aliasMap = state?.catalog?.location_alias_map || {};
    const rawLokasiIds = new Set();
    lokasiIds.forEach((id) => {
      const aliases = Array.isArray(aliasMap?.[id]) ? aliasMap[id].map(Number) : [id];
      aliases.forEach((aliasId) => rawLokasiIds.add(Number(aliasId)));
    });
    return (state?.detailRows || [])
      .filter((row) => {
        const rawLokasi = Number(row?.lokasi_id || 0);
        const canonicalLokasi = Number(row?.canonical_lokasi_id || rawLokasi);
        const paramId = Number(row?.parameter_id || 0);
        const lokasiMatch = lokasiIds.includes(canonicalLokasi) || rawLokasiIds.has(rawLokasi);
        const paramMatch = parameterIds.includes(paramId);
        return lokasiMatch && paramMatch;
      });
  };
  const collectOrderPengujianRows = (state) => Array.isArray(state?.detailRows)
    ? state.detailRows
    : [];
  const renderDocBlock = (title, lines) => {
    const safeLines = Array.isArray(lines) ? lines : [];
    const lineHtml = safeLines.length
      ? safeLines.map((line) => `<div class="doc-row">${line}</div>`).join('')
      : '<div class="doc-row text-muted">-</div>';
    return `<div class="doc-block"><div class="doc-label">${escapeHtml(title || 'Dokumen')}</div>${lineHtml}</div>`;
  };
  const formatParameterLabel = (row, state) => {
    const pid = Number(row?.parameter_id || 0);
    const param = (state?.catalog?.parameters || []).find((item) => Number(item?.id || 0) === pid);
    const cat = String(param?.category_short || '').trim();
    const name = String(row?.parameter || '-');
    if (cat && cat !== '-') return `${escapeHtml(cat)} - ${escapeHtml(name)}`;
    return escapeHtml(name);
  };
  const hasilModalEl = document.getElementById('draftLhuHasilModal');
  const hasilMetaEl = hasilModalEl?.querySelector('[data-hasil-meta]');
  const hasilBodyEl = hasilModalEl?.querySelector('[data-hasil-body]');
  const hasilEmptyEl = hasilModalEl?.querySelector('[data-hasil-empty]');
  const hasilModal = hasilModalEl && window.bootstrap?.Modal
    ? new window.bootstrap.Modal(hasilModalEl)
    : null;
  const renderDocFilesBlock = (row) => {
    const files = Array.isArray(row?.dokumen_files) ? row.dokumen_files : [];
    const filesHtml = files.length
      ? files.map((file) => {
          const name = escapeHtml(file?.name || 'File');
          const url = String(file?.url || '').trim();
          return `<div class="doc-row">${url ? `<a href="${escapeHtml(url)}" target="_blank" rel="noopener">${name}</a>` : name}</div>`;
        }).join('')
      : '<div class="doc-row text-muted">Belum ada file.</div>';
    return `<div class="doc-block"><div class="doc-label">${escapeHtml(row?.dokumen_label || 'Dokumen')}</div>${filesHtml}</div>`;
  };
  const openHasilModal = (metaText, entries, state) => {
    if (!hasilModalEl || !hasilBodyEl || !hasilEmptyEl) return;
    const normalizedEntries = Array.isArray(entries) ? entries : [];
    if (hasilMetaEl) hasilMetaEl.textContent = metaText || '-';
    hasilBodyEl.innerHTML = '';
    if (!normalizedEntries.length) {
      hasilEmptyEl.classList.remove('d-none');
    } else {
      hasilEmptyEl.classList.add('d-none');
    }
    const byLokasi = new Map();
    normalizedEntries.forEach((row) => {
      const lokasiKey = `${row?.lokasi_id || ''}::${row?.lokasi || '-'}`;
      if (!byLokasi.has(lokasiKey)) {
        byLokasi.set(lokasiKey, {
          lokasi: String(row?.lokasi || '-'),
          docs: new Map(),
        });
      }
      const lokasiNode = byLokasi.get(lokasiKey);
      const docKey = `${row?.dokumen_id || ''}::${row?.dokumen_label || 'Dokumen'}`;
      if (!lokasiNode.docs.has(docKey)) {
        lokasiNode.docs.set(docKey, {
          dokumen_label: String(row?.dokumen_label || 'Dokumen'),
          dokumen_files: Array.isArray(row?.dokumen_files) ? row.dokumen_files : [],
          rows: [],
        });
      }
      lokasiNode.docs.get(docKey).rows.push(row);
    });

    Array.from(byLokasi.values()).forEach((lokasiGroup, idx) => {
      const docBlocks = [];
      const paramBlocks = [];
      const metodeBlocks = [];
      const statusBlocks = [];
      const noteBlocks = [];
      Array.from(lokasiGroup.docs.values()).forEach((docGroup) => {
        const fileLines = (docGroup.dokumen_files || []).map((file) => {
          const name = escapeHtml(file?.name || 'File');
          const url = String(file?.url || '').trim();
          return url
            ? `<a href="${escapeHtml(url)}" target="_blank" rel="noopener">${name}</a>`
            : name;
        });
        docBlocks.push(renderDocBlock(docGroup.dokumen_label, fileLines));
        paramBlocks.push(renderDocBlock(docGroup.dokumen_label, docGroup.rows.map((row) => formatParameterLabel(row, state))));
        metodeBlocks.push(renderDocBlock(docGroup.dokumen_label, docGroup.rows.map((row) => escapeHtml(row?.metode || '-'))));
        statusBlocks.push(renderDocBlock(docGroup.dokumen_label, docGroup.rows.map((row) => {
          const raw = String(row?.review_status || '').trim().toLowerCase();
          if (raw === 'revisi') return 'Revisi';
          if (raw === 'approved' || raw === 'sesuai') return 'Terverifikasi';
          return '-';
        })));
        noteBlocks.push(renderDocBlock(docGroup.dokumen_label, docGroup.rows.map((row) => escapeHtml(row?.catatan || '-'))));
      });

      hasilBodyEl.innerHTML += `
        <tr>
          <td>${idx + 1}</td>
          <td class="small text-muted">${escapeHtml(lokasiGroup.lokasi || '-')}</td>
          <td>${docBlocks.join('')}</td>
          <td>${paramBlocks.join('')}</td>
          <td>${metodeBlocks.join('')}</td>
          <td>${statusBlocks.join('')}</td>
          <td>${noteBlocks.join('')}</td>
        </tr>
      `;
    });

    if (hasilModal) {
      hasilModal.show();
    } else {
      hasilModalEl.classList.add('show');
      hasilModalEl.style.display = 'block';
    }
  };

  const resolveMethodFlags = (doc, catalog) => {
    const methodMap = catalog?.location_param_method_map || {};
    const lokasiIds = (doc?.lokasi_ids || []).map(Number);
    const parameterIds = (doc?.parameter_ids || []).map(Number);
    let hasDirect = false;
    let hasIndirect = false;

    lokasiIds.forEach((lokasiId) => {
      parameterIds.forEach((paramId) => {
        const pair = methodMap?.[lokasiId]?.[paramId];
        if (pair?.has_direct) hasDirect = true;
        if (pair?.has_indirect) hasIndirect = true;
      });
    });

    return { hasDirect, hasIndirect };
  };

  const buildUsedParamCount = (documents, exceptIndex = -1) => {
    const used = new Map();
    (documents || []).forEach((doc, idx) => {
      if (idx === exceptIndex) return;
      const type = Number(doc?.type || 1);
      const uniqueParams = Array.from(new Set((doc?.parameter_ids || []).map(Number)));
      if (type === 2) {
        const lokasiCount = Math.max(0, Array.from(new Set((doc?.lokasi_ids || []).map(Number))).length);
        uniqueParams.forEach((pid) => used.set(pid, (used.get(pid) || 0) + lokasiCount));
        return;
      }
      uniqueParams.forEach((pid) => used.set(pid, (used.get(pid) || 0) + 1));
    });
    return used;
  };

  const isParamAvailableForSelectedLocation = (doc, catalog, paramId) => {
    const locationParamMap = catalog?.location_param_map || {};
    const lokasiIds = (doc?.lokasi_ids || []).map(Number);
    const pid = Number(paramId || 0);
    if (!pid) return false;
    if (!lokasiIds.length) return true;
    return lokasiIds.some((lokasiId) => ((locationParamMap?.[lokasiId] || []).map(Number)).includes(pid));
  };
  const isLocationAvailableForSelectedParameter = (doc, catalog, lokasiId) => {
    const locationParamMap = catalog?.location_param_map || {};
    const parameterIds = (doc?.parameter_ids || []).map(Number);
    const lid = Number(lokasiId || 0);
    if (!lid) return false;
    if (!parameterIds.length) return true;
    const availableParams = (locationParamMap?.[lid] || []).map(Number);
    return parameterIds.some((pid) => availableParams.includes(pid));
  };
  const getLocationNamesForParam = (catalog, paramId) => {
    const pid = Number(paramId || 0);
    if (!pid) return [];
    const locations = (catalog?.locations || []).map((loc) => ({
      id: Number(loc?.id || 0),
      name: String(loc?.name || '-'),
    }));
    const locationParamMap = catalog?.location_param_map || {};
    return locations
      .filter((loc) => ((locationParamMap?.[loc.id] || []).map(Number)).includes(pid))
      .map((loc) => loc.name);
  };

  const syncDirectTableFromSelection = (doc, catalog) => {
    if (doc?.table_manual) return;
    const locations = catalog?.locations || [];
    const parameters = catalog?.parameters || [];
    const methodMap = catalog?.location_param_method_map || {};
    const type = Number(doc?.type || 1);
    const lokasiIds = (doc?.lokasi_ids || []).map(Number);
    const parameterIds = (doc?.parameter_ids || []).map(Number);

    if (type === 1) {
      const rows = parameterIds
        .filter((pid) => {
          const lokasiId = lokasiIds[0];
          return !!methodMap?.[lokasiId]?.[pid]?.has_direct;
        })
        .map((pid) => [parameters.find((p) => Number(p.id) === pid)?.name || '-', '', '']);
      doc.table = {
        columns: ['Parameter', 'Hasil Pengukuran', 'NAB'],
        rows: rows.length ? rows : [['', '', '']],
      };
      return;
    }

    if (type === 2) {
      const paramId = parameterIds[0];
      const rows = lokasiIds
        .filter((lid) => !!methodMap?.[lid]?.[paramId]?.has_direct)
        .map((lid) => [locations.find((l) => Number(l.id) === lid)?.name || '-', '', '']);
      doc.table = {
        columns: ['Lokasi', 'Hasil Pengukuran', 'NAB'],
        rows: rows.length ? rows : [['', '', '']],
      };
      return;
    }

    const rows = [];
    lokasiIds.forEach((lid) => {
      parameterIds.forEach((pid) => {
        if (!methodMap?.[lid]?.[pid]?.has_direct) return;
        rows.push([
          locations.find((l) => Number(l.id) === lid)?.name || '-',
          parameters.find((p) => Number(p.id) === pid)?.name || '-',
          '',
          '',
        ]);
      });
    });
    doc.table = {
      columns: ['Lokasi', 'Parameter', 'Hasil Pengukuran', 'NAB'],
      rows: rows.length ? rows : [['', '', '', '']],
    };
  };

  const renderTable = (el, table, editable = false, docIndex = -1) => {
    if (!el) return;
    const t = sanitizeTable(table);
    const groups = t.columns.map((c) => parseColumnGroup(c));
    const hasGrouped = groups.some((g) => g.grouped);
    const allowStructureEdit = editable === true || editable === 'indirect-edit';

    let thead = '<thead>';
    if (hasGrouped) {
      const topCells = [];
      const bottomCells = [];
      let currentGroup = null;

      groups.forEach((g, i) => {
        const rawCol = String(t.columns[i] || '');
        const colName = rawCol.trim().toLowerCase();
        const protectedCol = colName === 'parameter'
          || colName === 'lokasi'
          || isNabColumnLabel(rawCol)
          || (editable === 'indirect-edit' && (isNumberColumnLabel(rawCol) || isIndirectAnalysisColumnLabel(rawCol)));
        const nestedBlocked = editable === 'indirect-edit' && (isNumberColumnLabel(rawCol) || isIndirectAnalysisColumnLabel(rawCol));
        const canDeleteCol = !protectedCol;
        const canSplitCol = !g.grouped && !protectedCol && !nestedBlocked;

        if (g.grouped) {
          if (currentGroup && currentGroup.parent === g.parent) {
            currentGroup.span += 1;
          } else {
            currentGroup = { parent: g.parent, span: 1 };
            topCells.push(currentGroup);
          }
          bottomCells.push(`<th>${allowStructureEdit ? `<div class="d-flex align-items-center gap-1"><input class="form-control form-control-sm" data-col="${i}" data-doc-index="${docIndex}" value="${String(g.child).replace(/"/g, '&quot;')}">${canDeleteCol ? `<button type="button" class="btn btn-outline-danger btn-sm px-2" data-del-col="${i}" data-doc-index="${docIndex}" title="Hapus kolom"><i class="bi bi-trash"></i></button>` : ''}</div>` : g.child}</th>`);
        } else {
          topCells.push({
            raw: `<th rowspan="2">${allowStructureEdit ? `<div class="d-flex align-items-center gap-1"><input class="form-control form-control-sm" data-col="${i}" data-doc-index="${docIndex}" value="${String(rawCol).replace(/"/g, '&quot;')}">${canSplitCol ? `<button type="button" class="btn btn-outline-secondary btn-sm px-2" data-split-col="${i}" data-doc-index="${docIndex}" title="Bagi jadi 2 subkolom"><i class="bi bi-layout-split"></i></button>` : ''}${canDeleteCol ? `<button type="button" class="btn btn-outline-danger btn-sm px-2" data-del-col="${i}" data-doc-index="${docIndex}" title="Hapus kolom"><i class="bi bi-trash"></i></button>` : ''}</div>` : rawCol}</th>`,
          });
          currentGroup = null;
        }
      });
      const top = '<tr>' + topCells.map((cell) => cell.raw || `<th class="lhu-group-header" colspan="${cell.span}">${cell.parent}</th>`).join('') + '</tr>';
      const bottom = '<tr>' + bottomCells.join('') + '</tr>';
      thead += top + bottom;
    } else {
      let row = '<tr>';
      t.columns.forEach((c, i) => {
        const colName = String(c || '').trim().toLowerCase();
        const protectedCol = colName === 'parameter'
          || colName === 'lokasi'
          || isNabColumnLabel(c)
          || (editable === 'indirect-edit' && (isNumberColumnLabel(c) || isIndirectAnalysisColumnLabel(c)));
        const nestedBlocked = editable === 'indirect-edit' && (isNumberColumnLabel(c) || isIndirectAnalysisColumnLabel(c));
        const canDeleteCol = !protectedCol;
        const canSplitCol = !protectedCol && !nestedBlocked;
        const numberClass = isNumberColumnLabel(c) ? ' class="lhu-col-number"' : '';
        row += `<th${numberClass}>${allowStructureEdit ? `<div class="d-flex align-items-center gap-1"><input class="form-control form-control-sm" data-col="${i}" data-doc-index="${docIndex}" value="${String(c).replace(/"/g, '&quot;')}">${canSplitCol ? `<button type="button" class="btn btn-outline-secondary btn-sm px-2" data-split-col="${i}" data-doc-index="${docIndex}" title="Bagi jadi 2 subkolom"><i class="bi bi-layout-split"></i></button>` : ''}${canDeleteCol ? `<button type="button" class="btn btn-outline-danger btn-sm px-2" data-del-col="${i}" data-doc-index="${docIndex}" title="Hapus kolom"><i class="bi bi-trash"></i></button>` : ''}</div>` : c}</th>`;
      });
      row += '</tr>';
      thead += row;
    }
    thead += '</thead>';

    let tbody = '<tbody>';
      t.rows.forEach((row, rIdx) => {
      tbody += '<tr>';
      t.columns.forEach((_, cIdx) => {
        const val = String(row[cIdx] || '').replace(/"/g, '&quot;');
        const isParameterColumn = isParameterColumnLabel(t.columns[cIdx]);
        const isNumberColumn = isNumberColumnLabel(t.columns[cIdx]);
        const isNabColumn = isNabColumnLabel(t.columns[cIdx]);
        const alignClass = isParameterColumn ? '' : ' text-center';
        const tdClass = `${isParameterColumn ? '' : 'text-center'}${isNumberColumn ? (isParameterColumn ? 'lhu-col-number' : ' lhu-col-number') : ''}`.trim();
        const canEditCell = editable === true || editable === 'indirect-edit' || (editable === 'nab-only' && isNabColumn);
        const tableScope = editable === true ? 'direct' : 'indirect';
        tbody += `<td class="${tdClass}">${canEditCell ? `<input class="form-control form-control-sm${alignClass}" data-cell-r="${rIdx}" data-cell-c="${cIdx}" data-doc-index="${docIndex}" data-table-scope="${tableScope}" value="${val}">` : val}</td>`;
      });
      tbody += '</tr>';
    });
    tbody += '</tbody>';
    el.innerHTML = thead + tbody;
  };
  const renderMeteorologyTable = (el, meteorologi, docIndex = -1) => {
    if (!el) return;
    if (!meteorologi || !meteorologi.table) {
      el.innerHTML = '<div class="small text-muted">Belum ada data meteorologis.</div>';
      return;
    }
    const t = sanitizeTable(meteorologi.table || {});
    const groups = t.columns.map((c) => parseColumnGroup(c));
    const hasGrouped = groups.some((g) => g.grouped);
    let thead = '<thead>';
    if (hasGrouped) {
      const topCells = [];
      const bottomCells = [];
      let currentGroup = null;
      groups.forEach((g, i) => {
        const rawCol = String(t.columns[i] || '');
        if (g.grouped) {
          if (currentGroup && currentGroup.parent === g.parent) {
            currentGroup.span += 1;
          } else {
            currentGroup = { parent: g.parent, span: 1 };
            topCells.push(currentGroup);
          }
          const numberClass = isNumberColumnLabel(g.child) ? ' class="lhu-col-number"' : '';
          bottomCells.push(`<th${numberClass}>${String(g.child || '')}</th>`);
        } else {
          const numberClass = isNumberColumnLabel(rawCol) ? ' class="lhu-col-number"' : '';
          topCells.push({ raw: `<th${numberClass} rowspan="2">${String(rawCol || '')}</th>` });
          currentGroup = null;
        }
      });
      const top = '<tr>' + topCells.map((cell) => cell.raw || `<th colspan="${cell.span}">${cell.parent}</th>`).join('') + '</tr>';
      const bottom = '<tr>' + bottomCells.join('') + '</tr>';
      thead += top + bottom;
    } else {
      let row = '<tr>';
      t.columns.forEach((c) => {
        const numberClass = isNumberColumnLabel(c) ? ' class="lhu-col-number"' : '';
        row += `<th${numberClass}>${String(c || '')}</th>`;
      });
      row += '</tr>';
      thead += row;
    }
    thead += '</thead>';

    let tbody = '<tbody>';
    t.rows.forEach((row, rIdx) => {
      tbody += '<tr>';
      t.columns.forEach((_, cIdx) => {
        const val = String(row[cIdx] || '').replace(/"/g, '&quot;');
        const isNumberColumn = isNumberColumnLabel(t.columns[cIdx]);
        tbody += `<td${isNumberColumn ? ' class="lhu-col-number"' : ''}><input class="form-control form-control-sm${isNumberColumn ? ' text-center' : ''}" data-meteo-cell-r="${rIdx}" data-meteo-cell-c="${cIdx}" data-doc-index="${docIndex}" value="${val}"></td>`;
      });
      tbody += '</tr>';
    });
    tbody += '</tbody>';
    el.innerHTML = `<table class="table table-sm table-bordered align-middle mb-0 lhu-meteo-table">${thead}${tbody}</table>`;
  };

  const renderChecks = (listEl, items, selectedIds, docIndex, key = 'id', label = 'name', labelFormatter = null) => {
    listEl.innerHTML = '';
    items.forEach((item) => {
      const id = Number(item[key]);
      const checked = selectedIds.includes(id) ? 'checked' : '';
      const text = typeof labelFormatter === 'function' ? labelFormatter(item) : (item[label] || '-');
      listEl.innerHTML += `<label class="d-block small"><input type="checkbox" data-check-id="${id}" data-doc-index="${docIndex}" ${checked}> ${text}</label>`;
    });
  };
  const renderEditor = (root, state) => {
    const list = root.querySelector('[data-doc-list]');
    const wordAllBtn = root.querySelector('[data-download-word-all]');
    if (!list) return;
    if (!state.documents.length) {
      state.selected = -1;
      list.innerHTML = '<div class="text-muted small">Belum ada dokumen. Klik Tambah Dokumen.</div>';
      if (wordAllBtn) wordAllBtn.disabled = true;
      return;
    }
    if (state.selected < 0 || state.selected >= state.documents.length) state.selected = 0;
    if (wordAllBtn) wordAllBtn.disabled = !!state.locked;

    list.innerHTML = '';
    state.documents.forEach((doc, idx) => {
      const activeClass = idx === state.selected ? 'border-primary shadow-sm' : '';
      const wrapper = document.createElement('div');
      wrapper.className = `lhu-doc-card ${activeClass}`;
      wrapper.setAttribute('data-doc-card', String(idx));
      wrapper.setAttribute('data-doc-index', String(idx));
      wrapper.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
          <div class="fw-semibold">${doc.title || ('Dokumen ' + (idx + 1))} <span class="text-muted small">(${typeLabel(doc.type)})</span></div>
          <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-success btn-sm" data-save-doc data-doc-index="${idx}" ${state.locked ? 'disabled' : ''}>Simpan Dokumen</button>
            <button type="button" class="btn btn-outline-danger btn-sm" data-delete-doc data-doc-index="${idx}" ${state.locked ? 'disabled' : ''}>Hapus</button>
          </div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label small text-muted mb-1">Judul Dokumen</label>
            <input type="text" class="form-control form-control-sm" data-doc-title data-doc-index="${idx}" value="${String(doc.title || '').replace(/"/g, '&quot;')}">
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted mb-1">Tipe LHU</label>
            <select class="form-select form-select-sm" data-doc-type data-doc-index="${idx}" ${doc.is_new ? '' : 'disabled'}>
              <option value="1" ${Number(doc.type)===1?'selected':''}>1 Dokumen 1 Lokasi Multiple Parameter</option>
              <option value="2" ${Number(doc.type)===2?'selected':''}>1 Dokumen 1 Parameter Multiple Lokasi</option>
              <option value="3" ${Number(doc.type)===3?'selected':''}>Multiple Lokasi Multiple Parameter</option>
            </select>
          </div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <label class="form-label small text-muted mb-1">Lokasi</label>
            <div class="border rounded p-2" style="max-height:120px;overflow:auto" data-location-list data-doc-index="${idx}"></div>
          </div>
          <div class="col-md-6">
            <label class="form-label small text-muted mb-1">Parameter</label>
            <div class="border rounded p-2" style="max-height:120px;overflow:auto" data-parameter-list data-doc-index="${idx}"></div>
          </div>
        </div>
        <div class="table-responsive mb-2" data-direct-section data-doc-index="${idx}">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div class="fw-semibold">Tabel Direct (Editable)</div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-add-col data-table-scope="direct" data-doc-index="${idx}">Tambah Kolom</button>
            </div>
          </div>
          <table class="table table-sm table-bordered align-middle lhu-table" data-direct-table data-doc-index="${idx}"></table>
        </div>
        <div class="table-responsive mb-2" data-indirect-section data-doc-index="${idx}">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <div>
              <div class="fw-semibold mb-1">Hasil Analisa Indirect (Verifikasi)</div>
              <div class="small text-muted mb-1">Tabel indirect bisa disesuaikan manual dan reset akan mengembalikan ke kondisi awal atau hasil simpan terakhir.</div>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-add-col data-table-scope="indirect" data-doc-index="${idx}">Tambah Kolom</button>
            </div>
          </div>
          <table class="table table-sm table-bordered align-middle" data-indirect-table data-doc-index="${idx}"></table>
        </div>
        <div class="border rounded p-2 bg-light mb-2" data-meteo-section data-doc-index="${idx}">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">Data Meteorologis pada Saat Pengujian</div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" data-set-meteo data-meteo-type="1" data-doc-index="${idx}">Template 1</button>
              <button type="button" class="btn btn-outline-secondary btn-sm" data-set-meteo data-meteo-type="2" data-doc-index="${idx}">Template 2</button>
              <button type="button" class="btn btn-outline-danger btn-sm" data-clear-meteo data-doc-index="${idx}">Hapus</button>
            </div>
          </div>
          <div class="table-responsive" data-meteo-table data-doc-index="${idx}"></div>
        </div>
        <div class="border rounded p-2 bg-light mb-2">
          <div class="fw-semibold mb-2">Analisa (Opsional)</div>
          <textarea class="form-control form-control-sm" rows="3" placeholder="Analisa (opsional)" data-ai-analisa data-doc-index="${idx}">${doc.ai?.analisa || ''}</textarea>
        </div>
        <div class="border rounded p-2 bg-light">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-semibold">Kesimpulan & Saran AI</div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-primary btn-sm" data-generate-ai data-doc-index="${idx}">Generate AI</button>
              <button type="button" class="btn btn-outline-secondary btn-sm" data-download-word data-doc-index="${idx}">Generate Word</button>
            </div>
          </div>
          <textarea class="form-control form-control-sm mb-2" rows="3" placeholder="Kesimpulan" data-ai-kesimpulan data-doc-index="${idx}">${doc.ai?.kesimpulan || ''}</textarea>
          <textarea class="form-control form-control-sm" rows="3" placeholder="Saran" data-ai-saran data-doc-index="${idx}">${doc.ai?.saran || ''}</textarea>
        </div>
      `;
      list.appendChild(wrapper);
    });

    state.documents.forEach((doc, idx) => {
      if (!doc.table || !doc.table.columns) doc.table = buildDefaultTable(doc, state.catalog);
      doc.table = ensureDirectTableHasNab(doc, doc.table);
      doc.indirect_nab_values = doc.indirect_nab_values && typeof doc.indirect_nab_values === 'object'
        ? doc.indirect_nab_values
        : {};
      const locEl = list.querySelector(`[data-location-list][data-doc-index="${idx}"]`);
      const paramEl = list.querySelector(`[data-parameter-list][data-doc-index="${idx}"]`);
      const directSectionEl = list.querySelector(`[data-direct-section][data-doc-index="${idx}"]`);
      const indirectSectionEl = list.querySelector(`[data-indirect-section][data-doc-index="${idx}"]`);
      const meteoTableEl = list.querySelector(`[data-meteo-table][data-doc-index="${idx}"]`);
      const meteoButtons = list.querySelectorAll(`[data-set-meteo][data-doc-index="${idx}"]`);
      const selectedLocNow = (doc.lokasi_ids || []).map(Number);
      const visibleLocations = (state.catalog.locations || []).filter((item) => {
        const lid = Number(item?.id || 0);
        return isLocationAvailableForSelectedParameter(doc, state.catalog, lid) || selectedLocNow.includes(lid);
      });
      renderChecks(locEl, visibleLocations, selectedLocNow, idx);
      const selectedNow = (doc.parameter_ids || []).map(Number);
      const usedByOthers = buildUsedParamCount(state.documents, idx);
      const usedAllDocs = buildUsedParamCount(state.documents);
      const visibleParams = (state.catalog.parameters || []).filter((item) => {
        const pid = Number(item?.id || 0);
        const total = Math.max(1, Number(item?.total_qty || 1));
        const remaining = total - (usedByOthers.get(pid) || 0);
        const available = isParamAvailableForSelectedLocation(doc, state.catalog, pid);
        return (remaining > 0 || selectedNow.includes(pid)) && available;
      });
      renderChecks(paramEl, visibleParams, selectedNow, idx, 'id', 'name', (item) => {
        const pid = Number(item?.id || 0);
        const total = Math.max(1, Number(item?.total_qty || 1));
        const remaining = Math.max(0, total - (usedAllDocs.get(pid) || 0));
        const name = String(item?.name || '-');
        const cat = String(item?.category_short || '-');
        const method = String(item?.method_label || '-');
        const locations = getLocationNamesForParam(state.catalog, pid);
        const locationsInfo = locations.length ? locations.join(', ') : '-';
        return `${name} (${cat}) - ${method}<br><span class="text-muted">Sisa: ${remaining} | Lokasi: ${locationsInfo}</span>`;
      });
      const paramById = new Map((state.catalog.parameters || []).map((p) => [Number(p.id), p]));
      const selectedParamIds = (doc.parameter_ids || []).map(Number);
      const selectedParam = selectedParamIds.length ? paramById.get(selectedParamIds[0]) : null;
      const selectedCat = String(selectedParam?.category_short || '');
      if (paramEl && selectedCat && selectedCat !== '-') {
        paramEl.querySelectorAll('input[type="checkbox"][data-check-id]').forEach((cb) => {
          const pid = Number(cb.getAttribute('data-check-id') || '0');
          const p = paramById.get(pid);
          const cat = String(p?.category_short || '');
          const isChecked = cb.checked;
          cb.disabled = !isChecked && cat !== selectedCat;
        });
      }
      const flags = resolveMethodFlags(doc, state.catalog);
      doc.indirect_table = doc.indirect_table_manual
        ? ensureIndirectTableHasNab(doc.indirect_table || {}, doc.indirect_nab_values)
        : buildIndirectTable(doc, state.indirectRows);
      const hasIndirectRows = Array.isArray(doc.indirect_table?.rows) && doc.indirect_table.rows.length > 0;
      if (directSectionEl) directSectionEl.hidden = !flags.hasDirect;
      if (indirectSectionEl) indirectSectionEl.hidden = !flags.hasIndirect || !hasIndirectRows;
      const activeMeteoType = Number(doc?.meteorologi?.type || 0);
      meteoButtons.forEach((btn) => {
        btn.classList.toggle('btn-primary', Number(btn.getAttribute('data-meteo-type') || '0') === activeMeteoType);
        btn.classList.toggle('btn-outline-secondary', Number(btn.getAttribute('data-meteo-type') || '0') !== activeMeteoType);
      });
      renderMeteorologyTable(meteoTableEl, doc?.meteorologi || null, idx);
      renderTable(list.querySelector(`[data-direct-table][data-doc-index="${idx}"]`), doc.table, true, idx);
      renderTable(list.querySelector(`[data-indirect-table][data-doc-index="${idx}"]`), doc.indirect_table, 'indirect-edit', idx);
    });
  };

  document.querySelectorAll('[data-order-editor]').forEach((root) => {
    const orderId = root.getAttribute('data-order-id') || '';
    const locked = root.getAttribute('data-locked') === '1';
    const docs = parseJsonSafe(root.querySelector('[data-documents-initial]')?.textContent, []);
    const catalog = parseJsonSafe(root.querySelector('[data-catalog-initial]')?.textContent, {});
    const indirectRows = parseJsonSafe(root.querySelector('[data-indirect-rows-initial]')?.textContent, []);
    const detailRows = parseJsonSafe(root.querySelector('[data-detail-rows-initial]')?.textContent, []);
    const normalizedDocs = normalizeDocumentsForState(docs);

    const state = {
      locked,
      saveUrl: root.getAttribute('data-save-url') || '',
      aiUrl: root.getAttribute('data-ai-url') || '',
      wordUrl: root.getAttribute('data-word-url') || '',
      wordAllUrl: root.getAttribute('data-word-all-url') || '',
      catalog,
      indirectRows,
      detailRows,
      documents: clone(normalizedDocs),
      initialDocuments: clone(normalizedDocs),
      selected: normalizedDocs.length ? 0 : -1,
    };
    states.set(orderId, state);
    renderEditor(root, state);

	    const resolveIndex = (el) => {
	      const raw = el?.getAttribute('data-doc-index');
	      if (raw !== null && raw !== undefined && raw !== '') return Number(raw);
	      const holder = el?.closest?.('[data-doc-index]');
	      const fromHolder = holder?.getAttribute?.('data-doc-index');
	      if (fromHolder !== null && fromHolder !== undefined && fromHolder !== '') return Number(fromHolder);
	      return state.selected;
	    };

	    const waitForNextPaint = () => new Promise((resolve) => {
	      window.requestAnimationFrame(() => {
	        window.requestAnimationFrame(resolve);
	      });
	    });

    root.addEventListener('change', async (e) => {
      const idx = resolveIndex(e.target);
      const doc = state.documents[idx];
      if (!doc) return;
      if (e.target.matches('[data-doc-type]')) {
        state.selected = idx;
        doc.type = Number(e.target.value || '1');
        doc.lokasi_ids = [];
        doc.parameter_ids = [];
        doc.table_manual = false;
        doc.indirect_table_manual = false;
        doc.indirect_nab_values = {};
        doc.indirect_table = { columns: ['No', 'Parameter', 'Hasil Analisa', 'NAB'], rows: [] };
        doc.table = buildDefaultTable(doc, state.catalog);
        renderEditor(root, state);
      }
      if (e.target.closest('[data-location-list]') && e.target.matches('input[type="checkbox"]')) {
        state.selected = idx;
        const id = Number(e.target.getAttribute('data-check-id') || '0');
        doc.lokasi_ids = Array.from(root.querySelectorAll(`[data-location-list][data-doc-index="${idx}"] input:checked`)).map((el) => Number(el.getAttribute('data-check-id') || '0'));
        if (Number(doc.type) === 1 && doc.lokasi_ids.length > 1) doc.lokasi_ids = [id];
        if (Number(doc.type) === 2 && (doc.parameter_ids || []).length === 1) {
          const paramId = Number((doc.parameter_ids || [])[0] || 0);
          const paramById = new Map((state.catalog.parameters || []).map((p) => [Number(p.id), p]));
          const usedByOthers = buildUsedParamCount(state.documents, idx);
          const total = Math.max(1, Number(paramById.get(paramId)?.total_qty || 1));
          const remainingForDoc = Math.max(0, total - (usedByOthers.get(paramId) || 0));
          if (doc.lokasi_ids.length > remainingForDoc) {
            doc.lokasi_ids = doc.lokasi_ids.filter((lid) => lid !== id);
            e.target.checked = false;
            await notify('warning', 'Jumlah lokasi melebihi sisa kuota parameter.');
          }
        }
        doc.parameter_ids = (doc.parameter_ids || [])
          .map(Number)
          .filter((pid) => isParamAvailableForSelectedLocation(doc, state.catalog, pid));
        doc.table_manual = false;
        doc.indirect_table_manual = false;
        syncDirectTableFromSelection(doc, state.catalog);
        renderEditor(root, state);
      }
      if (e.target.closest('[data-parameter-list]') && e.target.matches('input[type="checkbox"]')) {
        state.selected = idx;
        const id = Number(e.target.getAttribute('data-check-id') || '0');
        const checkedIds = Array.from(root.querySelectorAll(`[data-parameter-list][data-doc-index="${idx}"] input:checked`))
          .map((el) => Number(el.getAttribute('data-check-id') || '0'));
        const paramById = new Map((state.catalog.parameters || []).map((p) => [Number(p.id), p]));
        const pickedCats = Array.from(new Set(checkedIds
          .map((pid) => String(paramById.get(pid)?.category_short || ''))
          .filter((cat) => cat && cat !== '-')));
        if (pickedCats.length > 1) {
          e.target.checked = false;
          await notify('warning', 'Parameter harus dari kategori yang sama.');
          return;
        }
        const usedByOthers = buildUsedParamCount(state.documents, idx);
        const exceeded = checkedIds.find((pid) => {
          const total = Math.max(1, Number(paramById.get(pid)?.total_qty || 1));
          const remaining = total - (usedByOthers.get(pid) || 0);
          return remaining <= 0;
        });
        if (exceeded) {
          e.target.checked = false;
          await notify('warning', 'Jumlah parameter sudah habis terpakai di dokumen lain.');
          return;
        }
        doc.parameter_ids = checkedIds;
        if (Number(doc.type) === 2 && doc.parameter_ids.length > 1) doc.parameter_ids = [id];
        if (Number(doc.type) === 2 && doc.parameter_ids.length === 1) {
          const paramId = Number((doc.parameter_ids || [])[0] || 0);
          const total = Math.max(1, Number(paramById.get(paramId)?.total_qty || 1));
          const remainingForDoc = Math.max(0, total - (usedByOthers.get(paramId) || 0));
          const uniqLokasi = Array.from(new Set((doc.lokasi_ids || []).map(Number)));
          if (uniqLokasi.length > remainingForDoc) {
            doc.lokasi_ids = uniqLokasi.slice(0, remainingForDoc);
            await notify('warning', 'Lokasi disesuaikan dengan sisa kuota parameter.');
          }
        }
        doc.lokasi_ids = (doc.lokasi_ids || [])
          .map(Number)
          .filter((lid) => isLocationAvailableForSelectedParameter(doc, state.catalog, lid));
        doc.table_manual = false;
        doc.indirect_table_manual = false;
        syncDirectTableFromSelection(doc, state.catalog);
        renderEditor(root, state);
      }
    });

    root.addEventListener('input', (e) => {
      const idx = resolveIndex(e.target);
      const doc = state.documents[idx];
      if (!doc) return;
      if (e.target.matches('[data-doc-title]')) {
        state.selected = idx;
        doc.title = e.target.value || '';
      }
      if (e.target.matches('[data-ai-kesimpulan]')) {
        state.selected = idx;
        doc.ai = doc.ai || {};
        doc.ai.kesimpulan = e.target.value || '';
      }
      if (e.target.matches('[data-ai-analisa]')) {
        state.selected = idx;
        doc.ai = doc.ai || {};
        doc.ai.analisa = e.target.value || '';
      }
      if (e.target.matches('[data-ai-saran]')) {
        state.selected = idx;
        doc.ai = doc.ai || {};
        doc.ai.saran = e.target.value || '';
      }
      if (e.target.matches('[data-col]')) {
        const colIdx = Number(e.target.getAttribute('data-col') || '0');
        const tableScope = e.target.closest('[data-indirect-section]') ? 'indirect' : 'direct';
        const targetTable = tableScope === 'indirect'
          ? (doc.indirect_table = ensureIndirectTableHasNab(doc.indirect_table || buildIndirectTable(doc, state.indirectRows), doc.indirect_nab_values))
          : doc.table;
        if (tableScope === 'indirect') {
          doc.indirect_table_manual = true;
        } else {
          doc.table_manual = true;
        }
        const oldLabel = String(targetTable.columns[colIdx] || '');
        const nextLabel = String(e.target.value || '').trim();
        if (isNabColumnLabel(oldLabel)) {
          targetTable.columns[colIdx] = 'NAB';
          e.target.value = 'NAB';
          return;
        }
        if (oldLabel.includes('::') && !nextLabel.includes('::')) {
          const parent = String(oldLabel.split('::')[0] || '').trim();
          targetTable.columns[colIdx] = parent ? `${parent}::${nextLabel || 'Subkolom'}` : (nextLabel || '');
        } else {
          targetTable.columns[colIdx] = nextLabel;
        }
        if (tableScope === 'indirect') syncIndirectNabValuesFromTable(doc);
      }
      if (e.target.matches('[data-cell-r][data-cell-c]')) {
        const r = Number(e.target.getAttribute('data-cell-r') || '0');
        const c = Number(e.target.getAttribute('data-cell-c') || '0');
        const tableScope = String(e.target.getAttribute('data-table-scope') || 'direct');
        if (tableScope === 'indirect') {
          doc.indirect_table_manual = true;
          doc.indirect_table = ensureIndirectTableHasNab(doc.indirect_table || buildIndirectTable(doc, state.indirectRows), doc.indirect_nab_values);
          doc.indirect_table.rows[r][c] = e.target.value || '';
          syncIndirectNabValuesFromTable(doc);
          return;
        }
        doc.table_manual = true;
        doc.table.rows[r][c] = e.target.value || '';
      }
      if (e.target.matches('[data-meteo-cell-r][data-meteo-cell-c]')) {
        const r = Number(e.target.getAttribute('data-meteo-cell-r') || '0');
        const c = Number(e.target.getAttribute('data-meteo-cell-c') || '0');
        if (!doc.meteorologi || !doc.meteorologi.table) return;
        doc.meteorologi.table = sanitizeTable(doc.meteorologi.table);
        doc.meteorologi.table.rows[r][c] = e.target.value || '';
      }
    });

    root.addEventListener('click', async (e) => {
      const clickedCard = e.target.closest('[data-doc-card]');
      if (clickedCard) {
        const idx = Number(clickedCard.getAttribute('data-doc-card') || '-1');
        if (idx >= 0 && idx !== state.selected) {
          state.selected = idx;
          const interactive = e.target.closest('input,select,textarea,button,label,a');
          if (!interactive) {
            renderEditor(root, state);
            return;
          }
        }
      }

      const targetIdx = resolveIndex(e.target);
      const doc = state.documents[targetIdx];
      const addDocBtn = e.target.closest('[data-add-doc]');
      if (addDocBtn) {
        e.preventDefault();
        if (state.locked) return;
        const number = nextDocNumber(state.documents);
        state.documents.push(normalizeDocumentForState({ id: `doc-${Date.now()}`, title: `Dokumen ${number}`, type: 1, lokasi_ids: [], parameter_ids: [], table: { columns: ['Parameter', 'Hasil Pengukuran', 'NAB'], rows: [['', '', '']] }, indirect_nab_values: {}, indirect_table: { columns: ['No', 'Parameter', 'Hasil Analisa', 'NAB'], rows: [] }, indirect_table_manual: false, ai: null, meteorologi: null, is_new: true }));
        state.selected = state.documents.length - 1;
        renderEditor(root, state);
        return;
      }
      if (e.target.closest('[data-reset-docs]')) {
        e.preventDefault();
        if (state.locked) return;
        const ok = await confirmAction('Reset tabel dan isi editor ke kondisi awal atau hasil simpan terakhir?');
        if (!ok) return;
        state.documents = clone(state.initialDocuments);
        state.selected = state.documents.length ? Math.min(state.selected, state.documents.length - 1) : -1;
        renderEditor(root, state);
        return;
      }
      const deleteDocBtn = e.target.closest('[data-delete-doc]');
      if (deleteDocBtn) {
        e.preventDefault();
        if (state.locked) return;
        const idx = Number(deleteDocBtn.getAttribute('data-doc-index') || state.selected);
        if (idx < 0 || !state.documents[idx]) return;
        const ok = await confirmAction('Hapus dokumen ini?');
        if (!ok) return;
        state.documents.splice(idx, 1);
        if (state.selected === idx) {
          state.selected = Math.min(idx, state.documents.length - 1);
        } else if (state.selected > idx) {
          state.selected -= 1;
        }
        state.selected = Math.min(state.selected, state.documents.length - 1);
        renderEditor(root, state);
        return;
      }
      if (e.target.closest('[data-add-col]') && doc) {
        state.selected = targetIdx;
        const trigger = e.target.closest('[data-add-col]');
        const tableScope = String(trigger?.getAttribute('data-table-scope') || 'direct');
        const targetTable = tableScope === 'indirect'
          ? (doc.indirect_table = ensureIndirectTableHasNab(doc.indirect_table || buildIndirectTable(doc, state.indirectRows), doc.indirect_nab_values))
          : doc.table;
        if (tableScope === 'indirect') {
          doc.indirect_table_manual = true;
        } else {
          doc.table_manual = true;
        }
        targetTable.columns.push('Kolom Baru');
        targetTable.rows = targetTable.rows.map((r) => [...r, '']);
        if (tableScope === 'indirect') syncIndirectNabValuesFromTable(doc);
        renderEditor(root, state);
        return;
      }
      const delColBtn = e.target.closest('[data-del-col]');
      if (delColBtn && doc) {
        state.selected = targetIdx;
        const idx = Number(delColBtn.getAttribute('data-del-col') || '-1');
        const tableScope = delColBtn.closest('[data-indirect-section]') ? 'indirect' : 'direct';
        const targetTable = tableScope === 'indirect'
          ? (doc.indirect_table = ensureIndirectTableHasNab(doc.indirect_table || buildIndirectTable(doc, state.indirectRows), doc.indirect_nab_values))
          : doc.table;
        if (idx < 0 || idx >= (targetTable?.columns || []).length) return;
        const colName = String(targetTable.columns[idx] || '').trim().toLowerCase();
        if (
          colName === 'parameter'
          || colName === 'lokasi'
          || colName === 'nab'
          || (tableScope === 'indirect' && (isNumberColumnLabel(targetTable.columns[idx] || '') || isIndirectAnalysisColumnLabel(targetTable.columns[idx] || '')))
        ) {
          await notify('warning', 'Kolom ini tidak dapat dihapus.');
          return;
        }
        if ((targetTable?.columns || []).length <= 1) {
          await notify('warning', 'Minimal harus ada 1 kolom.');
          return;
        }
        if (tableScope === 'indirect') {
          doc.indirect_table_manual = true;
        } else {
          doc.table_manual = true;
        }
        targetTable.columns.splice(idx, 1);
        targetTable.rows = (targetTable.rows || []).map((row) => {
          const next = Array.isArray(row) ? [...row] : [];
          next.splice(idx, 1);
          return next;
        });
        if (!targetTable.rows.length) targetTable.rows.push(targetTable.columns.map(() => ''));
        if (tableScope === 'indirect') syncIndirectNabValuesFromTable(doc);
        renderEditor(root, state);
        return;
      }
      const splitColBtn = e.target.closest('[data-split-col]');
      if (splitColBtn && doc) {
        state.selected = targetIdx;
        const idx = Number(splitColBtn.getAttribute('data-split-col') || '-1');
        const tableScope = splitColBtn.closest('[data-indirect-section]') ? 'indirect' : 'direct';
        const targetTable = tableScope === 'indirect'
          ? (doc.indirect_table = ensureIndirectTableHasNab(doc.indirect_table || buildIndirectTable(doc, state.indirectRows), doc.indirect_nab_values))
          : doc.table;
        if (idx < 0 || idx >= (targetTable?.columns || []).length) return;
        const colName = String(targetTable.columns[idx] || '').trim();
        const lowerName = colName.toLowerCase();
        if (!colName || lowerName === 'parameter' || lowerName === 'lokasi' || lowerName === 'nab') return;
        if (colName.includes('::')) {
          await notify('warning', 'Kolom ini sudah berupa subkolom.');
          return;
        }
        if (tableScope === 'indirect') {
          doc.indirect_table_manual = true;
        } else {
          doc.table_manual = true;
        }
        targetTable.columns.splice(idx, 1, `${colName}::1`, `${colName}::2`);
        targetTable.rows = (targetTable.rows || []).map((row) => {
          const next = Array.isArray(row) ? [...row] : [];
          const oldValue = String(next[idx] || '');
          next.splice(idx, 1, oldValue, '');
          return next;
        });
        if (!targetTable.rows.length) targetTable.rows.push(targetTable.columns.map(() => ''));
        if (tableScope === 'indirect') syncIndirectNabValuesFromTable(doc);
        renderEditor(root, state);
        return;
      }
      const setMeteoBtn = e.target.closest('[data-set-meteo]');
      if (setMeteoBtn && doc) {
        state.selected = targetIdx;
        const type = Number(setMeteoBtn.getAttribute('data-meteo-type') || '0');
        if (![1, 2].includes(type)) return;
        doc.meteorologi = buildMeteorologyTemplate(type);
        renderEditor(root, state);
        return;
      }
      const clearMeteoBtn = e.target.closest('[data-clear-meteo]');
      if (clearMeteoBtn && doc) {
        state.selected = targetIdx;
        doc.meteorologi = null;
        renderEditor(root, state);
        return;
      }
      const delRowBtn = e.target.closest('[data-del-row]');
      if (delRowBtn && doc) {
        state.selected = targetIdx;
        const idx = Number(delRowBtn.getAttribute('data-del-row') || '-1');
        doc.table_manual = true;
        if (idx >= 0) doc.table.rows.splice(idx, 1);
        if (!doc.table.rows.length) doc.table.rows.push(doc.table.columns.map(() => ''));
        renderEditor(root, state);
        return;
      }
      if (e.target.closest('[data-save-docs]')) {
        if (state.locked) return;
        const payload = { documents: state.documents.map((d) => ({ id: d.id, title: d.title, type: Number(d.type), lokasi_ids: d.lokasi_ids || [], parameter_ids: d.parameter_ids || [], table: d.table || {columns:[],rows:[]}, indirect_nab_values: d.indirect_nab_values || {}, indirect_table: d.indirect_table || {columns:[],rows:[]}, indirect_table_manual: !!d.indirect_table_manual, ai: d.ai || null, meteorologi: d.meteorologi || null })) };
        const resp = await fetch(state.saveUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ payload }) });
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok) {
          console.error('Save draft failed', data);
          return notify('error', data.message || 'Gagal menyimpan draft.');
        }
        if (Array.isArray(data.documents)) {
          state.documents = normalizeDocumentsForState(data.documents);
          state.initialDocuments = clone(state.documents);
        } else {
          state.documents.forEach((d) => d.is_new = false);
          state.initialDocuments = clone(state.documents);
        }
        notify('success', data.message || 'Draft tersimpan.');
        renderEditor(root, state);
        return;
      }
      if (e.target.closest('[data-download-word-all]')) {
        const wordAllBtn = e.target.closest('[data-download-word-all]');
        if (state.locked) return;
        if (!state.documents.length) {
          await notify('warning', 'Tambahkan minimal satu dokumen terlebih dahulu.');
          return;
        }
        if (!state.wordAllUrl) {
          await notify('error', 'URL Word gabungan tidak tersedia.');
          return;
        }
        const originalText = wordAllBtn?.textContent || 'Generate Word Gabungan';
        if (wordAllBtn) {
          wordAllBtn.disabled = true;
          wordAllBtn.textContent = 'Generating...';
        }
        try {
          const payload = {
            documents: state.documents.map((d) => ({
              id: d.id,
              title: d.title,
              type: Number(d.type),
              lokasi_ids: d.lokasi_ids || [],
              parameter_ids: d.parameter_ids || [],
              table: d.table || { columns: [], rows: [] },
              indirect_nab_values: d.indirect_nab_values || {},
              indirect_table: d.indirect_table || { columns: [], rows: [] },
              indirect_table_manual: !!d.indirect_table_manual,
              ai: d.ai || null,
              meteorologi: d.meteorologi || null,
            })),
          };
          const resp = await fetch(state.saveUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ payload }),
          });
          const data = await resp.json().catch(() => ({}));
          if (!resp.ok) {
            await notify('error', data.message || 'Gagal menyimpan draft sebelum generate Word gabungan.');
            return;
          }
          if (Array.isArray(data.documents)) {
            state.documents = normalizeDocumentsForState(data.documents);
            state.initialDocuments = clone(state.documents);
            state.selected = state.documents.length ? Math.max(0, Math.min(state.selected, state.documents.length - 1)) : -1;
          } else {
            state.documents.forEach((d) => { d.is_new = false; });
            state.initialDocuments = clone(state.documents);
          }
          renderEditor(root, state);
          window.open(state.wordAllUrl, '_blank');
        } finally {
          if (wordAllBtn) {
            wordAllBtn.disabled = !!state.locked || !state.documents.length;
            wordAllBtn.textContent = originalText;
          }
        }
        return;
      }
      if (e.target.closest('[data-save-doc]') && doc) {
        if (state.locked) return;
        const payload = { document: { id: doc.id, title: doc.title, type: Number(doc.type), lokasi_ids: doc.lokasi_ids || [], parameter_ids: doc.parameter_ids || [], table: doc.table || {columns:[],rows:[]}, indirect_nab_values: doc.indirect_nab_values || {}, indirect_table: doc.indirect_table || {columns:[],rows:[]}, indirect_table_manual: !!doc.indirect_table_manual, ai: doc.ai || null, meteorologi: doc.meteorologi || null } };
        const resp = await fetch(state.saveUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ payload }) });
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok) return notify('error', data.message || 'Gagal menyimpan dokumen.');
        if (Array.isArray(data.documents)) {
          state.documents = normalizeDocumentsForState(data.documents);
          state.initialDocuments = clone(state.documents);
          state.selected = Math.max(0, state.documents.findIndex((d) => String(d.id) === String(doc.id)));
        } else {
          doc.is_new = false;
          state.initialDocuments = clone(state.documents);
        }
        notify('success', data.message || 'Dokumen berhasil disimpan.');
        renderEditor(root, state);
        return;
      }
	      if (e.target.closest('[data-generate-ai]') && doc) {
	        const aiBtn = e.target.closest('[data-generate-ai]');
	        state.selected = targetIdx;
	        if (aiBtn?.disabled) return;
	        const originalText = aiBtn?.textContent || 'Generate AI';
        if (aiBtn) {
          aiBtn.disabled = true;
          aiBtn.textContent = 'Generating...';
        }
        try {
          const savePayload = {
            document: {
              id: doc.id,
              title: doc.title,
              type: Number(doc.type),
              lokasi_ids: doc.lokasi_ids || [],
              parameter_ids: doc.parameter_ids || [],
              table: doc.table || { columns: [], rows: [] },
              indirect_nab_values: doc.indirect_nab_values || {},
              indirect_table: doc.indirect_table || { columns: [], rows: [] },
              indirect_table_manual: !!doc.indirect_table_manual,
              ai: doc.ai || null,
              meteorologi: doc.meteorologi || null,
            },
          };
          const saveResp = await fetch(state.saveUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ payload: savePayload }),
          });
          const saveData = await saveResp.json().catch(() => ({}));
          if (!saveResp.ok) {
            return notify('error', saveData.message || 'Gagal menyimpan dokumen sebelum generate AI.');
          }
          if (Array.isArray(saveData.documents)) {
            state.documents = normalizeDocumentsForState(saveData.documents);
            state.initialDocuments = clone(state.documents);
            const foundIndex = state.documents.findIndex((d) => String(d.id) === String(doc.id));
            state.selected = foundIndex >= 0 ? foundIndex : targetIdx;
          } else {
            doc.is_new = false;
            state.initialDocuments = clone(state.documents);
          }
	          renderEditor(root, state);
	          const selectedDoc = state.documents[state.selected] || doc;
	          const resp = await fetch(state.aiUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ document_id: selectedDoc.id }) });
	          const data = await resp.json().catch(() => ({}));
	          if (!resp.ok) {
	            return notify('error', data.message || 'Gagal generate AI.');
	          }
	          const aiPayload = data?.data && typeof data.data === 'object' ? data.data : null;
	          if (!aiPayload) {
	            return notify('error', 'Hasil AI tidak valid atau kosong.');
	          }
	          const selectedDocId = String(selectedDoc.id || '');
	          const selectedDocIndex = state.documents.findIndex((item) => String(item.id || '') === selectedDocId);
	          if (selectedDocIndex >= 0) {
	            state.documents[selectedDocIndex] = {
	              ...state.documents[selectedDocIndex],
	              ai: aiPayload,
	            };
	            state.selected = selectedDocIndex;
	          } else {
	            selectedDoc.ai = aiPayload;
	          }
              state.initialDocuments = clone(state.documents);
	          renderEditor(root, state);
	          const activeDocIndex = state.selected;
	          const kesimpulanEl = root.querySelector(`[data-ai-kesimpulan][data-doc-index="${activeDocIndex}"]`);
	          const saranEl = root.querySelector(`[data-ai-saran][data-doc-index="${activeDocIndex}"]`);
	          const analisaEl = root.querySelector(`[data-ai-analisa][data-doc-index="${activeDocIndex}"]`);
	          if (kesimpulanEl) kesimpulanEl.value = String(aiPayload.kesimpulan || '');
	          if (saranEl) saranEl.value = String(aiPayload.saran || '');
	          if (analisaEl && aiPayload.analisa !== undefined) analisaEl.value = String(aiPayload.analisa || '');
	          await waitForNextPaint();
	          if (aiBtn) {
	            aiBtn.disabled = false;
	            aiBtn.textContent = originalText;
	          }
	          notify('success', data.message || 'Kesimpulan dan saran AI berhasil dibuat.');
	        } catch (_err) {
	          if (aiBtn) {
	            aiBtn.disabled = false;
	            aiBtn.textContent = originalText;
	          }
	          return notify('error', 'Gagal generate AI.');
	        }
	        return;
	      }
      if (e.target.closest('[data-download-word]') && doc) {
        const wordBtn = e.target.closest('[data-download-word]');
        state.selected = targetIdx;
        if (state.locked) return;
        if (wordBtn?.disabled) return;
        const originalWordBtnText = wordBtn?.textContent || 'Generate Word';
        if (wordBtn) {
          wordBtn.disabled = true;
          wordBtn.textContent = 'Generating...';
        }
        const currentDocId = String(doc.id || '');
        try {
          const payload = {
            document: {
              id: doc.id,
              title: doc.title,
              type: Number(doc.type),
              lokasi_ids: doc.lokasi_ids || [],
              parameter_ids: doc.parameter_ids || [],
              table: doc.table || { columns: [], rows: [] },
              indirect_nab_values: doc.indirect_nab_values || {},
              indirect_table: doc.indirect_table || { columns: [], rows: [] },
              indirect_table_manual: !!doc.indirect_table_manual,
              ai: doc.ai || null,
              meteorologi: doc.meteorologi || null,
            },
          };
          const resp = await fetch(state.saveUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: JSON.stringify({ payload }),
          });
          const data = await resp.json().catch(() => ({}));
          if (!resp.ok) {
            return notify('error', data.message || 'Gagal menyimpan dokumen sebelum generate Word.');
          }
          if (Array.isArray(data.documents)) {
            state.documents = normalizeDocumentsForState(data.documents);
            state.initialDocuments = clone(state.documents);
            const foundIndex = state.documents.findIndex((d) => String(d.id) === currentDocId);
            state.selected = foundIndex >= 0 ? foundIndex : Math.min(targetIdx, state.documents.length - 1);
          } else {
            doc.is_new = false;
            state.initialDocuments = clone(state.documents);
          }
          renderEditor(root, state);
          const selectedDoc = state.documents[state.selected];
          const documentId = selectedDoc?.id || doc.id;
          window.open(`${state.wordUrl}?document_id=${encodeURIComponent(documentId)}`, '_blank');
        } finally {
          if (wordBtn) {
            wordBtn.disabled = false;
            wordBtn.textContent = originalWordBtnText;
          }
        }
        return;
      }
    });

    const orderHasilBtn = root.closest('.accordion-body')?.querySelector('[data-open-hasil-pengujian-order]');
    if (orderHasilBtn) {
      const orderRows = collectOrderPengujianRows(state);
      orderHasilBtn.disabled = orderRows.length < 1;
    }
  });

  document.addEventListener('click', async (e) => {
    if (e.target.closest('[data-open-hasil-pengujian-order]')) {
      const btn = e.target.closest('[data-open-hasil-pengujian-order]');
      const orderId = btn?.getAttribute('data-order-id') || '';
      const state = states.get(orderId);
      if (!state) return;
      const rows = collectOrderPengujianRows(state);
      const meta = `Order ${orderId || '-'} | Hasil Pengujian`;
      openHasilModal(meta, rows, state);
      return;
    }
    if (e.target.matches('[data-submit-url]')) {
      const ok = await confirmAction('Lanjutkan ke QC LHU?');
      if (!ok) return;
      const submitBtn = e.target;
      window.WorkflowLoading?.setButtonLoading(submitBtn, true);
      try {
        const resp = await fetch(submitBtn.getAttribute('data-submit-url') || '', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
        const data = await resp.json().catch(() => ({}));
        if (!resp.ok) throw new Error(data.message || 'Gagal submit QC.');
        notify('success', data.message || 'Berhasil ke QC.');
        window.location.reload();
      } catch (error) {
        await notify('error', error.message || 'Gagal submit QC.');
        window.WorkflowLoading?.releaseButton(submitBtn);
      }
    }
  });

  const uploadFinalLhuFromInput = async (fileInput, triggerBtn = null) => {
    const box = fileInput?.closest?.('[data-final-lhu-section]');
    const file = fileInput?.files?.[0];
    const uploadUrl = (fileInput?.getAttribute('data-upload-url') || triggerBtn?.getAttribute('data-upload-url') || '').trim();
    if (!file || !uploadUrl || !box) {
      if (!file) await notify('error', 'Pilih file LHU jadi terlebih dahulu.');
      return;
    }

    const info = box.querySelector('[data-final-lhu-info]');
    const submitBtn = box.closest('.accordion-body')?.querySelector('[data-submit-url]');
    if (fileInput) fileInput.disabled = true;

    const fd = new FormData();
    fd.append('final_lhu_file', file);
    try {
      const resp = await fetch(uploadUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: fd
      });
      const data = await resp.json().catch(() => ({}));
      if (!resp.ok) {
        throw new Error(data.message || 'Upload gagal.');
      }

      const fileName = String(data.name || file.name || 'File LHU jadi');
      const fileUrl = String(data.url || '').trim();
      if (info) {
        info.innerHTML = fileUrl
          ? `File saat ini: <a href="${escapeHtml(fileUrl)}" target="_blank" rel="noopener">${escapeHtml(fileName)}</a>`
          : `File saat ini: ${escapeHtml(fileName)}`;
      }
      if (submitBtn) submitBtn.disabled = false;
      if (fileInput) fileInput.value = '';
      await notify('success', data.message || 'LHU jadi berhasil diupload.');
    } catch (err) {
      await notify('error', err.message || 'Upload gagal.');
    } finally {
      if (fileInput) fileInput.disabled = false;
    }
  };

  document.addEventListener('change', async (e) => {
    const fileInput = e.target.closest?.('[data-final-lhu-input]');
    if (!fileInput) return;
    await uploadFinalLhuFromInput(fileInput);
  });
})();
</script>
@endpush
