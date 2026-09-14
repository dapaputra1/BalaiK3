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
  .card-header {
    background: #f8fafc;
  }
  .qc-search-wrap {
    background: #eef2f7;
    border: 1px solid #d7e1ee;
    border-radius: 16px;
    padding: 10px;
    margin-bottom: 12px;
  }
  .qc-search-wrap .input-group-text {
    background: #fff;
    border-color: #b8cbe2;
    border-right: 0;
    border-radius: 12px 0 0 12px;
    color: #6c7f96;
  }
  .qc-search-wrap .form-control {
    border-color: #b8cbe2;
    border-left: 0;
    border-radius: 0 12px 12px 0;
    min-height: 40px;
    box-shadow: none !important;
    font-size: 14px;
  }
  .qc-search-wrap .form-control:focus {
    border-color: #95b6da;
  }
  .qc-search-wrap .btn-search-reset {
    min-height: 40px;
    border-radius: 12px;
    border: 1px solid #15406A;
    color: #15406A;
    background: #fff;
    font-weight: 600;
  }
  .qc-search-wrap .btn-search-reset:hover {
    background: #f3f8ff;
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - QC LHU',
  'subtitle' => 'Pemeriksaan kualitas LHU sebelum penandatanganan.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-0">
    <div class="px-4 pt-4 pb-3">
      <div class="card border-0 shadow-sm rounded-4 mb-0">
        <div class="card-body">
          <div class="qc-search-wrap">
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
          <div class="d-flex gap-2 mt-3 flex-wrap">
            <button type="button" class="btn btn-outline-primary btn-sm active" data-filter-status="belum_qc">Belum di QC</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-filter-status="diajukan_revisi">Diajukan Revisi</button>
            <button type="button" class="btn btn-outline-primary btn-sm" data-filter-status="selesai_qc">Selesai QC</button>
          </div>
        </div>
      </div>
    </div>

    <div class="px-4 pb-4">
      @forelse($orders as $order)
        <div
          class="border rounded-4 p-3 mb-3 shadow-sm bg-white"
          data-card
          data-kode="{{ strtolower((string) ($order['kode'] ?? '')) }}"
          data-perusahaan="{{ strtolower((string) ($order['perusahaan'] ?? '')) }}"
          data-qc-status="{{ strtolower((string) ($order['qc_status_filter'] ?? 'belum_qc')) }}"
        >
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-semibold">{{ $order['kode'] ?? '-' }}</span>
              </div>
              <div class="text-muted small">{{ $order['perusahaan'] ?? '-' }}</div>
            </div>
            <div class="text-end">
              <span class="badge {{ $order['qc_status_badge_class'] ?? 'text-bg-primary' }} px-3 py-2">
                {{ $order['qc_status_label'] ?? 'QC LHU' }}
              </span>
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-12 col-md-4">
              <div class="text-muted small">Lokasi</div>
              <div class="fw-semibold">{{ $order['lokasi'] ?? '-' }}</div>
            </div>
            <div class="col-12 col-md-8">
              <div class="text-muted small">LHU Jadi</div>
              @if(!empty($order['final_lhu_url']))
                <div class="fw-semibold">
                  <a href="{{ $order['final_lhu_url'] }}" target="_blank" rel="noopener">
                    {{ $order['final_lhu_name'] ?? 'Lihat LHU Jadi' }}
                  </a>
                </div>
              @else
                <div class="fw-semibold text-danger">File LHU jadi belum tersedia</div>
              @endif
            </div>
          </div>

          @if(!empty($order['revisi_note']) || !empty($order['revisi_file_url']))
            <div class="alert alert-warning py-2 px-3 mt-3 mb-0 small">
              <div class="fw-semibold mb-1">Catatan revisi terakhir</div>
              @if(!empty($order['revisi_note']))
                <div>{{ $order['revisi_note'] }}</div>
              @endif
              @if(!empty($order['revisi_file_url']))
                <div class="mt-2">
                  <a href="{{ $order['revisi_file_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm">
                    Lihat Dokumen Catatan Revisi
                  </a>
                  @if(!empty($order['revisi_file_name']))
                    <div class="text-muted mt-1">{{ $order['revisi_file_name'] }}</div>
                  @endif
                  @if(!empty($order['revisi_file_uploaded_at']))
                    <div class="text-muted">Diupload: {{ $order['revisi_file_uploaded_at'] }}</div>
                  @endif
                </div>
              @endif
            </div>
          @endif

          @if(!empty($order['can_review']))
            <div class="d-flex justify-content-end gap-2 flex-wrap mt-3">
              <button
                type="button"
                class="btn btn-outline-danger btn-sm"
                data-qc-action="revisi"
                data-submit-url="{{ $order['submit_url'] ?? '' }}"
              >
                Ajukan Revisi
              </button>
              <button
                type="button"
                class="btn btn-primary btn-sm"
                data-qc-action="approve"
                data-submit-url="{{ $order['submit_url'] ?? '' }}"
                @if(empty($order['final_lhu_url'])) disabled @endif
              >
                Setujui & Lanjut TTD LHU
              </button>
            </div>
          @endif
        </div>
      @empty
        <div class="text-center text-muted py-4">Belum ada data QC LHU.</div>
      @endforelse
    </div>
  </div>
</div>
<div class="text-center text-muted d-none mt-3" data-search-empty>Tidak ada order yang cocok.</div>
@endsection

@push('scripts')
<script>
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const kodeInput = document.querySelector('[data-search-kode]');
  const perusahaanInput = document.querySelector('[data-search-perusahaan]');
  const resetBtn = document.querySelector('[data-search-reset]');
  const cards = document.querySelectorAll('[data-card]');
  const emptyState = document.querySelector('[data-search-empty]');
  const filterButtons = document.querySelectorAll('[data-filter-status]');
  let activeStatusFilter = 'belum_qc';

  const filterCards = () => {
    const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
    const perusahaanVal = (perusahaanInput?.value || '').toLowerCase().trim();
    let visible = 0;

    cards.forEach((card) => {
      const kode = (card.getAttribute('data-kode') || '').toLowerCase();
      const perusahaan = (card.getAttribute('data-perusahaan') || '').toLowerCase();
      const status = (card.getAttribute('data-qc-status') || 'belum_qc').toLowerCase();
      const matchKode = !kodeVal || kode.includes(kodeVal);
      const matchPerusahaan = !perusahaanVal || perusahaan.includes(perusahaanVal);
      const matchStatus = !activeStatusFilter || status === activeStatusFilter;
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
    activeStatusFilter = 'belum_qc';
    filterButtons.forEach((btn) => {
      const isActive = (btn.getAttribute('data-filter-status') || '') === 'belum_qc';
      btn.classList.toggle('active', isActive);
    });
    filterCards();
  });
  filterButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const mode = btn.getAttribute('data-filter-status') || 'belum_qc';
      activeStatusFilter = mode;
      filterButtons.forEach((item) => item.classList.toggle('active', item === btn));
      filterCards();
    });
  });

  const notify = async (type, message) => {
    if (window.Swal) {
      const icon = type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success');
      await window.Swal.fire({
        icon,
        title: icon === 'success' ? 'Berhasil' : (icon === 'warning' ? 'Peringatan' : 'Gagal'),
        text: message || '',
      });
      return;
    }
    alert(message || '');
  };

  document.addEventListener('click', async (event) => {
    const btn = event.target.closest('[data-qc-action]');
    if (!btn) return;
    const action = btn.getAttribute('data-qc-action') || '';
    const url = btn.getAttribute('data-submit-url') || '';
    if (!action || !url) return;

    let note = '';
    let revisionFile = null;
    if (action === 'revisi') {
      if (window.Swal) {
        const result = await window.Swal.fire({
          icon: 'warning',
          title: 'Ajukan Revisi Draft LHU',
          html: `
            <div class="text-start">
              <label class="form-label small mb-1">Catatan revisi <span class="text-danger">*</span></label>
              <textarea id="qcRevisiNote" class="swal2-textarea" style="display:block; width:100%; margin:0 0 10px 0;" placeholder="Tuliskan alasan/catatan revisi..."></textarea>
              <label class="form-label small mb-1">Dokumen catatan revisi (opsional)</label>
              <input id="qcRevisiFile" type="file" class="swal2-file" style="display:block; width:100%; margin:0;" accept=".pdf,.doc,.docx" />
              <div class="text-muted small mt-1">Format: PDF/DOC/DOCX, maks 10 MB</div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: 'Kirim Revisi',
          cancelButtonText: 'Batal',
          focusConfirm: false,
          preConfirm: () => {
            const noteEl = document.getElementById('qcRevisiNote');
            const fileEl = document.getElementById('qcRevisiFile');
            const value = String(noteEl?.value || '').trim();
            if (!value) {
              window.Swal.showValidationMessage('Catatan revisi wajib diisi.');
              return false;
            }
            const file = fileEl?.files?.[0] || null;
            const maxSize = 10 * 1024 * 1024;
            if (file && file.size > maxSize) {
              window.Swal.showValidationMessage('Ukuran file maksimal 10 MB.');
              return false;
            }
            return { note: value, file };
          },
        });
        if (!result.isConfirmed) return;
        note = String(result.value?.note || '').trim();
        revisionFile = result.value?.file || null;
      } else {
        note = prompt('Catatan revisi (wajib diisi):') || '';
        if (!String(note).trim()) {
          alert('Catatan revisi wajib diisi.');
          return;
        }
      }
    } else {
      const ok = window.Swal
        ? await window.Swal.fire({
            icon: 'question',
            title: 'Setujui QC LHU?',
            text: 'Permohonan akan diteruskan ke tahap TTD LHU.',
            showCancelButton: true,
            confirmButtonText: 'Ya, setujui',
            cancelButtonText: 'Batal',
          }).then((r) => r.isConfirmed)
        : confirm('Setujui QC LHU dan lanjut ke TTD LHU?');
      if (!ok) return;
    }

    window.WorkflowLoading?.setButtonLoading(btn, true);
    try {
      const payload = new FormData();
      payload.append('action', action);
      payload.append('note', note);
      if (revisionFile) {
        payload.append('revision_file', revisionFile);
      }

      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: payload,
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data.message || 'Gagal memproses QC LHU.');
      }

      await notify('success', data.message || 'QC LHU berhasil diproses.');
      window.location.reload();
    } catch (error) {
      await notify('error', error.message || 'Gagal memproses QC LHU.');
      window.WorkflowLoading?.releaseButton(btn);
    }
  });

  filterCards();
})();
</script>
@endpush
