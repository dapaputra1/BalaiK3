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
  'title' => 'Alur Kerja - Penandatanganan LHU',
  'subtitle' => 'Lihat LHU hasil QC lalu upload dokumen LHU yang sudah ditandatangani.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-0">
    <div class="px-4 pt-4 pb-3">
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
    </div>

    <div class="px-4 pb-4">
      @forelse($orders as $order)
        <div
          class="border rounded-4 p-3 mb-3 shadow-sm bg-white"
          data-card
          data-kode="{{ strtolower((string) ($order['kode'] ?? '')) }}"
          data-perusahaan="{{ strtolower((string) ($order['perusahaan'] ?? '')) }}"
        >
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="fw-semibold">{{ $order['kode'] ?? '-' }}</span>
              </div>
              <div class="text-muted small">{{ $order['perusahaan'] ?? '-' }}</div>
            </div>
            <div class="text-end">
              <span class="badge text-bg-primary px-3 py-2">TTD LHU</span>
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-12 col-md-4">
              <div class="text-muted small">Lokasi</div>
              <div class="fw-semibold">{{ $order['lokasi'] ?? '-' }}</div>
            </div>
            <div class="col-12 col-md-8">
              <div class="text-muted small">LHU dari QC</div>
              @if(!empty($order['final_lhu_url']))
                <div class="fw-semibold">
                  <a href="{{ $order['final_lhu_url'] }}" target="_blank" rel="noopener">
                    {{ $order['final_lhu_name'] ?? 'Lihat LHU QC' }}
                  </a>
                </div>
              @else
                <div class="fw-semibold text-danger">File LHU dari QC belum tersedia</div>
              @endif
            </div>
          </div>

          <div class="border rounded-3 p-3 mt-3 bg-light">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <div class="small text-muted">LHU TTD</div>
                <div class="fw-semibold" data-signed-name>
                  {{ $order['signed_lhu_name'] ?? 'Belum ada file LHU TTD' }}
                </div>
                <div class="small text-muted" data-signed-time>
                  @if(!empty($order['signed_uploaded_at']))
                    Diupload: {{ $order['signed_uploaded_at'] }}
                  @endif
                </div>
                @if(!empty($order['signed_lhu_url']))
                  <a href="{{ $order['signed_lhu_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mt-2" data-signed-link>
                    Lihat LHU TTD
                  </a>
                @else
                  <a href="#" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mt-2 d-none" data-signed-link>
                    Lihat LHU TTD
                  </a>
                @endif
              </div>

              <div class="d-flex flex-column gap-2" style="min-width: 280px;">
                <input type="file"
                       class="form-control form-control-sm"
                       data-upload-input
                       data-upload-url="{{ $order['upload_url'] ?? '' }}"
                       accept=".pdf,.doc,.docx">
                <button
                  type="button"
                  class="btn btn-sm {{ !empty($order['can_submit']) ? 'btn-primary' : 'btn-outline-primary' }}"
                  data-submit-btn
                  data-submit-url="{{ $order['submit_url'] ?? '' }}"
                  data-can-submit="{{ !empty($order['can_submit']) ? '1' : '0' }}"
                >
                  Lanjut ke Surat Tagihan
                </button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-4">Belum ada data penandatanganan LHU.</div>
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

  const filterCards = () => {
    const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
    const perusahaanVal = (perusahaanInput?.value || '').toLowerCase().trim();
    let visible = 0;

    cards.forEach((card) => {
      const kode = (card.getAttribute('data-kode') || '').toLowerCase();
      const perusahaan = (card.getAttribute('data-perusahaan') || '').toLowerCase();
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

  const uploadSigned = async (input) => {
    const card = input?.closest?.('[data-card]');
    if (!card) return;
    const file = input?.files?.[0];
    const url = input?.getAttribute('data-upload-url') || '';
    if (!url) return;
    if (!file) {
      notify('warning', 'Pilih file LHU TTD terlebih dahulu.');
      return;
    }

    const formData = new FormData();
    formData.append('signed_lhu_file', file);

    input.disabled = true;
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: formData,
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data.message || 'Gagal upload LHU TTD.');
      }

      const nameNode = card.querySelector('[data-signed-name]');
      const timeNode = card.querySelector('[data-signed-time]');
      const linkNode = card.querySelector('[data-signed-link]');
      const submitBtn = card.querySelector('[data-submit-btn]');
      if (nameNode) nameNode.textContent = data.name || file.name;
      if (timeNode) timeNode.textContent = data.uploaded_at ? ('Diupload: ' + data.uploaded_at) : '';
      if (linkNode && data.url) {
        linkNode.href = data.url;
        linkNode.classList.remove('d-none');
      }
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.setAttribute('data-can-submit', '1');
        submitBtn.classList.remove('btn-outline-primary');
        submitBtn.classList.add('btn-primary');
      }
      if (input) input.value = '';

      await notify('success', data.message || 'LHU TTD berhasil diupload.');
    } catch (error) {
      await notify('error', error.message || 'Gagal upload LHU TTD.');
    } finally {
      input.disabled = false;
    }
  };

  const submitToSuratTagihan = async (btn) => {
    const url = btn.getAttribute('data-submit-url') || '';
    if (!url) return;
    const canSubmit = btn.getAttribute('data-can-submit') === '1';
    if (!canSubmit) {
      await notify('warning', 'Belum bisa lanjut ke Surat Tagihan. Upload file LHU TTD terlebih dahulu.');
      return;
    }

    const ok = window.Swal
      ? await window.Swal.fire({
          icon: 'question',
          title: 'Lanjut ke Surat Tagihan?',
          text: 'Permohonan akan dipindahkan dari TTD LHU ke Surat Tagihan.',
          showCancelButton: true,
          confirmButtonText: 'Ya, lanjut',
          cancelButtonText: 'Batal',
        }).then((r) => r.isConfirmed)
      : confirm('Lanjutkan permohonan ke tahap Surat Tagihan?');
    if (!ok) return;

    window.WorkflowLoading?.setButtonLoading(btn, true);
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data.message || 'Gagal lanjut ke tahap Surat Tagihan.');
      }

      await notify('success', data.message || 'Berhasil lanjut ke tahap Surat Tagihan.');
      window.location.reload();
    } catch (error) {
      await notify('error', error.message || 'Gagal lanjut ke tahap Surat Tagihan.');
      window.WorkflowLoading?.releaseButton(btn);
    }
  };

  kodeInput?.addEventListener('input', filterCards);
  perusahaanInput?.addEventListener('input', filterCards);
  resetBtn?.addEventListener('click', () => {
    if (kodeInput) kodeInput.value = '';
    if (perusahaanInput) perusahaanInput.value = '';
    filterCards();
  });

  document.addEventListener('change', (event) => {
    const uploadInput = event.target.closest('[data-upload-input]');
    if (uploadInput) {
      uploadSigned(uploadInput);
    }
  });

  document.addEventListener('click', (event) => {
    const submitBtn = event.target.closest('[data-submit-btn]');
    if (submitBtn) {
      submitToSuratTagihan(submitBtn);
    }
  });

  filterCards();
})();
</script>
@endpush
