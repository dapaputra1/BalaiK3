@extends('layouts.app_admin')

@section('content_admin')
@php
  $rows = $rows ?? collect();
  $years = $years ?? collect();
  $filters = $filters ?? ['year' => null, 'month' => null, 'day' => null];
  $routePrefix = $routePrefix ?? 'superadmin';
  $monthNames = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
  ];
@endphp

<style>
  #riwayatHasilModal .modal-dialog {
    max-width: min(980px, 92vw);
  }
  #riwayatHasilModal .modal-content {
    border: 0;
    border-radius: 22px;
    overflow: hidden;
    box-shadow: 0 28px 80px rgba(15, 23, 42, 0.18);
  }
  #riwayatHasilModal .modal-header {
    align-items: flex-start;
    padding: 1.25rem 1.5rem 1rem;
    border-bottom: 1px solid #e6ebf2;
    background:
      linear-gradient(135deg, rgba(15, 61, 110, 0.06), rgba(15, 61, 110, 0)),
      #ffffff;
  }
  #riwayatHasilModal .modal-title {
    font-weight: 700;
    color: #1f2937;
  }
  #riwayatHasilModal .modal-body {
    padding: 1.25rem 1.5rem 1.5rem;
    background: #f8fafc;
  }
  #riwayatHasilModal [data-hasil-meta] {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 0.6rem;
    padding: 0.45rem 0.75rem;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475467;
    font-size: 0.84rem;
    line-height: 1.35;
    max-width: 100%;
  }
  #riwayatHasilModal .btn-close {
    margin-top: 0.15rem;
  }
  #riwayatHasilModal .hasil-modal-shell {
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #fff;
    overflow: hidden;
  }
  #riwayatHasilModal .hasil-modal-caption {
    display: flex;
    align-items: center;
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #e9eef5;
    background: #fbfdff;
  }
  #riwayatHasilModal .hasil-modal-caption-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f3d6e;
    letter-spacing: 0.01em;
  }
  #riwayatHasilModal .hasil-table-wrap {
    max-height: min(62vh, 620px);
    overflow: auto;
  }
  #riwayatHasilModal .table {
    margin-bottom: 0;
    font-size: 13px;
    table-layout: auto;
    background: #fff;
  }
  #riwayatHasilModal .table th,
  #riwayatHasilModal .table td {
    border-color: #d9dee7;
    vertical-align: middle;
    padding: 10px 12px;
  }
  #riwayatHasilModal .table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #eef4fb;
    font-weight: 700;
    color: #1d2939;
    white-space: nowrap;
  }
  #riwayatHasilModal .table tbody tr:nth-child(odd) td {
    background: #ffffff;
  }
  #riwayatHasilModal .table tbody tr:nth-child(even) td {
    background: #f8fafc;
  }
  #riwayatHasilModal .table td {
    color: #334155;
    white-space: nowrap;
  }
  #riwayatHasilModal [data-hasil-empty] {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
    padding: 1.5rem;
    color: #667085;
    font-style: italic;
    text-align: center;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
  }
  #riwayatHasilModal.show {
    background: rgba(0, 0, 0, 0.35);
  }
  @media (max-width: 767.98px) {
    #riwayatHasilModal .modal-dialog {
      max-width: calc(100vw - 1rem);
      margin: 0.5rem auto;
    }
    #riwayatHasilModal .modal-header,
    #riwayatHasilModal .modal-body {
      padding-left: 1rem;
      padding-right: 1rem;
    }
    #riwayatHasilModal [data-hasil-meta] {
      border-radius: 14px;
      white-space: normal;
    }
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Riwayat Preparasi Analisa',
  'subtitle' => 'Daftar parameter yang sudah selesai dianalisis dan diteruskan ke tahap verifikasi.',
  'total' => $rows->count(),
  'backUrl' => route($routePrefix . '.prepanalisa.index'),
  'backLabel' => 'Preparasi Analisa',
])

<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body">
    <form method="GET" action="{{ route($routePrefix . '.prepanalisa.history') }}" class="row g-2 align-items-end" id="historyFilterForm">
      <div class="col-12 col-md-3">
        <label class="form-label small text-muted mb-1">Tahun</label>
        <select name="year" class="form-select form-select-sm" data-auto-filter>
          <option value="">Semua tahun</option>
          @foreach($years as $year)
            <option value="{{ $year }}" {{ (int) ($filters['year'] ?? 0) === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label small text-muted mb-1">Bulan</label>
        <select name="month" class="form-select form-select-sm" data-auto-filter>
          <option value="">Semua bulan</option>
          @foreach($monthNames as $monthNumber => $monthLabel)
            <option value="{{ $monthNumber }}" {{ (int) ($filters['month'] ?? 0) === (int) $monthNumber ? 'selected' : '' }}>{{ $monthLabel }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 col-md-2">
        <label class="form-label small text-muted mb-1">Hari</label>
        <input type="number" name="day" class="form-control form-control-sm" min="1" max="31" value="{{ $filters['day'] ?? '' }}" placeholder="1-31" data-auto-filter>
      </div>
      <div class="col-12 col-md-4 d-flex gap-2">
        <a href="{{ route($routePrefix . '.prepanalisa.history') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
        <a href="{{ route($routePrefix . '.prepanalisa.history.export', request()->query()) }}" class="btn btn-success btn-sm w-100">
          <i class="bi bi-file-earmark-excel me-1"></i>Unduh Excel
        </a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 16%;">Tanggal Selesai</th>
            <th style="width: 16%;">ID Koding</th>
            <th style="width: 16%;">Kategori</th>
            <th style="width: 24%;">Parameter</th>
            <th style="width: 14%;" class="text-center">Hasil</th>
            <th style="width: 14%;" class="text-center">Preview</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rows as $row)
            <tr>
              <td>{{ $row['tanggal_selesai_label'] ?? '-' }}</td>
              <td><code>{{ $row['kode_koding'] ?? '-' }}</code></td>
              <td>{{ $row['kategori'] ?? '-' }}</td>
              <td>{{ $row['parameter'] ?? '-' }}</td>
              <td class="text-center">
                <button type="button"
                        class="btn btn-outline-primary btn-sm"
                        data-hasil-btn
                        data-hasil-url="{{ route($routePrefix . '.prepanalisa.history.hasil.data', $row['item_id']) }}">
                  <i class="bi bi-eye me-1"></i>Hasil
                </button>
              </td>
              <td class="text-center">
                <a href="{{ route($routePrefix . '.prepanalisa.history.hasil', $row['item_id']) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-printer me-1"></i>Preview
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat sesuai filter.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="riwayatHasilModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Hasil Analisis</h5>
          <div class="small text-muted mt-1" data-hasil-meta>-</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="hasil-modal-shell">
          <div class="hasil-modal-caption">
            <div class="hasil-modal-caption-title">Ringkasan Hasil Perhitungan</div>
          </div>
          <div class="hasil-table-wrap">
            <table class="table table-sm align-middle" data-hasil-table>
              <thead data-hasil-head></thead>
              <tbody data-hasil-body>
                <tr><td class="text-center text-muted">Memuat data...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="small mt-2 d-none" data-hasil-empty>Belum ada data hasil perhitungan.</div>
      </div>
    </div>
  </div>
</div>

<script>
  (() => {
    const form = document.getElementById('historyFilterForm');
    if (!form) return;

    const filterInputs = form.querySelectorAll('[data-auto-filter]');
    let dayInputTimer = null;

    filterInputs.forEach((el) => {
      if (el.tagName === 'SELECT') {
        el.addEventListener('change', () => form.requestSubmit());
        return;
      }

      el.addEventListener('input', () => {
        window.clearTimeout(dayInputTimer);
        dayInputTimer = window.setTimeout(() => {
          form.requestSubmit();
        }, 500);
      });
    });

    const modalEl = document.getElementById('riwayatHasilModal');
    if (!modalEl) return;

    const bsModal = (typeof bootstrap !== 'undefined' && bootstrap.Modal)
      ? new bootstrap.Modal(modalEl)
      : null;
    const metaEl = modalEl.querySelector('[data-hasil-meta]');
    const headEl = modalEl.querySelector('[data-hasil-head]');
    const bodyEl = modalEl.querySelector('[data-hasil-body]');
    const tableEl = modalEl.querySelector('[data-hasil-table]');
    const emptyEl = modalEl.querySelector('[data-hasil-empty]');

    const escapeHtml = (value) => String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#39;');

    const renderLoading = () => {
      if (metaEl) metaEl.textContent = '-';
      if (headEl) headEl.innerHTML = '';
      if (bodyEl) bodyEl.innerHTML = '<tr><td class="text-center text-muted">Memuat data...</td></tr>';
      if (tableEl) tableEl.classList.remove('d-none');
      if (emptyEl) emptyEl.classList.add('d-none');
    };

    const openModal = () => {
      if (bsModal) {
        bsModal.show();
        return;
      }
      modalEl.classList.add('show');
      modalEl.style.display = 'block';
      modalEl.removeAttribute('aria-hidden');
      modalEl.setAttribute('aria-modal', 'true');
      document.body.classList.add('modal-open');
    };

    const closeModal = () => {
      if (bsModal) {
        bsModal.hide();
        return;
      }
      modalEl.classList.remove('show');
      modalEl.style.display = 'none';
      modalEl.setAttribute('aria-hidden', 'true');
      modalEl.removeAttribute('aria-modal');
      document.body.classList.remove('modal-open');
    };

    const renderError = (message) => {
      if (headEl) headEl.innerHTML = '';
      if (bodyEl) bodyEl.innerHTML = `<tr><td class="text-center text-danger">${escapeHtml(message)}</td></tr>`;
      if (tableEl) tableEl.classList.remove('d-none');
      if (emptyEl) emptyEl.classList.add('d-none');
    };

    const renderTable = (payload) => {
      const headers = Array.isArray(payload?.table?.headers) ? payload.table.headers : [];
      const rows = Array.isArray(payload?.table?.rows) ? payload.table.rows : [];

      if (metaEl) {
        const kode = payload?.kode || '-';
        const perusahaan = payload?.perusahaan || '-';
        const parameter = payload?.parameter || '-';
        metaEl.textContent = `${kode} | ${perusahaan} | ${parameter}`;
      }

      if (!headers.length || !rows.length) {
        if (tableEl) tableEl.classList.add('d-none');
        if (emptyEl) emptyEl.classList.remove('d-none');
        return;
      }

      if (headEl) {
        const headHtml = headers.map((header) => `<th>${escapeHtml(header)}</th>`).join('');
        headEl.innerHTML = `<tr>${headHtml}</tr>`;
      }

      if (bodyEl) {
        const bodyHtml = rows.map((row) => {
          const cells = Array.isArray(row) ? row : [row];
          const normalizedCells = [...cells];
          while (normalizedCells.length < headers.length) {
            normalizedCells.push('');
          }
          const tds = normalizedCells.slice(0, headers.length).map((cell) => `<td>${escapeHtml(cell)}</td>`).join('');
          return `<tr>${tds}</tr>`;
        }).join('');
        bodyEl.innerHTML = bodyHtml;
      }

      if (tableEl) tableEl.classList.remove('d-none');
      if (emptyEl) emptyEl.classList.add('d-none');
    };

    document.querySelectorAll('[data-hasil-btn]').forEach((button) => {
      button.addEventListener('click', async () => {
        const url = button.getAttribute('data-hasil-url');
        if (!url) return;

        renderLoading();
        openModal();

        try {
          const response = await fetch(url, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
          });
          if (!response.ok) {
            throw new Error('Gagal memuat hasil analisis.');
          }
          const payload = await response.json();
          renderTable(payload);
        } catch (error) {
          renderError(error?.message || 'Terjadi kesalahan saat memuat data.');
        }
      });
    });

    if (!bsModal) {
      modalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((el) => {
        el.addEventListener('click', closeModal);
      });
      modalEl.addEventListener('click', (event) => {
        if (event.target === modalEl) closeModal();
      });
      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modalEl.classList.contains('show')) {
          closeModal();
        }
      });
    }
  })();
</script>
@endsection
