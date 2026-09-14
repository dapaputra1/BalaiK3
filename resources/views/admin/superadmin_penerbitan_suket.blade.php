@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
  $suketEnabled = $suketEnabled ?? true;
  $canManageSuketSetting = $canManageSuketSetting ?? false;
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
  .suket-setting-card {
    border: 1px solid #d9e4f0;
    background: linear-gradient(180deg, #ffffff 0%, #f7fafe 100%);
  }
  .suket-switch-wrap {
    min-width: 220px;
  }
  .suket-switch-wrap .form-check-input {
    width: 4rem;
    height: 2rem;
    cursor: pointer;
  }
  .suket-switch-wrap .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(21, 64, 106, 0.18);
  }
  .suket-switch-wrap .form-check-label {
    font-size: 1rem;
    margin-left: 10px;
  }
  .suket-setting-note {
    font-size: 12px;
    color: #5f728b;
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Penerbitan Suket',
  'subtitle' => 'Upload surat keterangan berdasarkan LHU TTD lalu teruskan ke Penyerahan LHU.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4 mb-3 suket-setting-card">
  <div class="card-body d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <div class="fw-semibold">Status penerbitan suket</div>
      <div class="small text-muted">
        @if($suketEnabled)
          Saat ini aktif. Setelah kuitansi, permohonan tetap masuk ke tahap penerbitan suket.
        @else
          Saat ini nonaktif. Setelah kuitansi, permohonan akan langsung melewati tahap penerbitan suket dan masuk ke penyerahan LHU.
        @endif
      </div>
      <div class="suket-setting-note mt-2">
        On dipakai jika surat keterangan memang sedang diterbitkan. Off dipakai jika tahap suket belum berjalan, sehingga sistem langsung lanjut ke penyerahan LHU.
      </div>
    </div>
    @if($canManageSuketSetting)
      <div class="form-check form-switch m-0 suket-switch-wrap">
        <input
          class="form-check-input"
          type="checkbox"
          role="switch"
          id="suketAvailabilitySwitch"
          data-suket-availability
          @checked($suketEnabled)
        >
        <label class="form-check-label fw-semibold" for="suketAvailabilitySwitch">
          {{ $suketEnabled ? 'On' : 'Off' }}
        </label>
      </div>
    @else
      <span class="badge {{ $suketEnabled ? 'text-bg-success' : 'text-bg-secondary' }}">
        {{ $suketEnabled ? 'On' : 'Off' }}
      </span>
    @endif
  </div>
</div>

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
              <span class="badge text-bg-primary px-3 py-2">Penerbitan Suket</span>
            </div>
          </div>

          <div class="row g-3 mt-2">
            <div class="col-12 col-md-4">
              <div class="text-muted small">Lokasi</div>
              <div class="fw-semibold">{{ $order['lokasi'] ?? '-' }}</div>
            </div>
            <div class="col-12 col-md-8">
              <div class="text-muted small">LHU TTD</div>
              @if(!empty($order['signed_lhu_url']))
                <div class="fw-semibold">
                  <a href="{{ $order['signed_lhu_url'] }}" target="_blank" rel="noopener">
                    {{ $order['signed_lhu_name'] ?? 'Lihat LHU TTD' }}
                  </a>
                </div>
              @else
                <div class="fw-semibold text-danger">File LHU TTD belum tersedia</div>
              @endif
            </div>
          </div>

          <div class="border rounded-3 p-3 mt-3 bg-light">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
              <div>
                <div class="small text-muted">Surat Keterangan</div>
                <div class="fw-semibold" data-suket-name>
                  {{ $order['suket_name'] ?? ($suketEnabled ? 'Belum ada file surat keterangan' : 'Tahap suket dilewati') }}
                </div>
                <div class="small text-muted" data-suket-time>
                  @if(!empty($order['suket_uploaded_at']))
                    Diupload: {{ $order['suket_uploaded_at'] }}
                  @elseif(!$suketEnabled)
                    Fitur penerbitan suket sedang nonaktif.
                  @endif
                </div>
                @if(!empty($order['suket_url']))
                  <a href="{{ $order['suket_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mt-2" data-suket-link>
                    Lihat Surat Keterangan
                  </a>
                @else
                  <a href="#" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mt-2 d-none" data-suket-link>
                    Lihat Surat Keterangan
                  </a>
                @endif
              </div>

              <div class="d-flex flex-column gap-2" style="min-width: 280px;">
                @if($suketEnabled)
                  <input type="file"
                         class="form-control form-control-sm"
                         data-upload-input
                         data-upload-url="{{ $order['upload_url'] ?? '' }}"
                         accept=".pdf,.doc,.docx">
                @else
                  <div class="small text-muted">Upload surat keterangan dilewati karena fitur sedang off.</div>
                @endif
                <button
                  type="button"
                  class="btn btn-sm {{ !empty($order['can_submit']) ? 'btn-primary' : 'btn-outline-primary' }}"
                  data-submit-btn
                  data-submit-url="{{ $order['submit_url'] ?? '' }}"
                  data-can-submit="{{ !empty($order['can_submit']) ? '1' : '0' }}"
                >
                  {{ $suketEnabled ? 'Lanjut ke Penyerahan LHU' : 'Lewati ke Penyerahan LHU' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-4">Belum ada data penerbitan surat keterangan.</div>
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
  const availabilitySwitch = document.querySelector('[data-suket-availability]');
  const availabilityLabel = document.querySelector('label[for="suketAvailabilitySwitch"]');

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

  const uploadSuket = async (input) => {
    const card = input?.closest?.('[data-card]');
    if (!card) return;
    const file = input?.files?.[0];
    const url = input?.getAttribute('data-upload-url') || '';
    if (!url) return;
    if (!file) {
      notify('warning', 'Pilih file surat keterangan terlebih dahulu.');
      return;
    }

    const formData = new FormData();
    formData.append('suket_file', file);

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
        throw new Error(data.message || 'Gagal upload surat keterangan.');
      }

      const nameNode = card.querySelector('[data-suket-name]');
      const timeNode = card.querySelector('[data-suket-time]');
      const linkNode = card.querySelector('[data-suket-link]');
      const submitBtn = card.querySelector('[data-submit-btn]');

      if (nameNode) nameNode.textContent = data.name || 'Surat keterangan';
      if (timeNode) timeNode.textContent = data.uploaded_at ? `Diupload: ${data.uploaded_at}` : '';
      if (linkNode && data.url) {
        linkNode.href = data.url;
        linkNode.classList.remove('d-none');
      }
      if (submitBtn) {
        submitBtn.dataset.canSubmit = '1';
        submitBtn.classList.remove('btn-outline-primary');
        submitBtn.classList.add('btn-primary');
      }

      await notify('success', data.message || 'Surat keterangan berhasil diupload.');
    } catch (error) {
      await notify('error', error.message || 'Gagal upload surat keterangan.');
    } finally {
      input.disabled = false;
      input.value = '';
    }
  };

  const updateAvailability = async (input) => {
    const enabled = !!input?.checked;
    const confirmText = enabled
      ? 'Jika diaktifkan, permohonan setelah kuitansi akan masuk lagi ke tahap penerbitan suket.'
      : 'Jika dinonaktifkan, permohonan setelah kuitansi akan langsung melewati tahap penerbitan suket dan masuk ke penyerahan LHU.';

    const confirmed = window.Swal
      ? await window.Swal.fire({
          icon: 'warning',
          title: enabled ? 'Aktifkan penerbitan suket?' : 'Nonaktifkan penerbitan suket?',
          text: confirmText,
          showCancelButton: true,
          confirmButtonText: enabled ? 'Ya, aktifkan' : 'Ya, nonaktifkan',
          cancelButtonText: 'Batal',
        })
      : { isConfirmed: confirm(confirmText) };

    if (!confirmed.isConfirmed) {
      input.checked = !enabled;
      return;
    }

    input.disabled = true;
    try {
      const response = await fetch(@json(route('superadmin.suket.settings.availability')), {
        method: 'PUT',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ enabled }),
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data.message || 'Gagal memperbarui status penerbitan suket.');
      }

      if (availabilityLabel) {
        availabilityLabel.textContent = enabled ? 'On' : 'Off';
      }

      await notify('success', data.message || 'Status penerbitan suket berhasil diperbarui.');
      window.location.reload();
    } catch (error) {
      input.checked = !enabled;
      if (availabilityLabel) {
        availabilityLabel.textContent = input.checked ? 'On' : 'Off';
      }
      await notify('error', error.message || 'Gagal memperbarui status penerbitan suket.');
    } finally {
      input.disabled = false;
    }
  };

  const submitNext = async (button) => {
    const url = button?.getAttribute('data-submit-url') || '';
    if (!url) return;
    if ((button?.dataset?.canSubmit || '0') !== '1') {
      notify('warning', 'Upload surat keterangan terlebih dahulu.');
      return;
    }

    const confirmed = window.Swal
      ? await window.Swal.fire({
          icon: 'question',
          title: 'Lanjut ke Penyerahan LHU?',
          text: 'Setelah disetujui, permohonan akan masuk ke tahap Penyerahan LHU.',
          showCancelButton: true,
          confirmButtonText: 'Lanjutkan',
          cancelButtonText: 'Batal',
        })
      : { isConfirmed: confirm('Lanjut ke Penyerahan LHU?') };

    if (!confirmed.isConfirmed) return;

    window.WorkflowLoading?.setButtonLoading(button, true);
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
        throw new Error(data.message || 'Gagal melanjutkan ke Penyerahan LHU.');
      }

      await notify('success', data.message || 'Permohonan diteruskan ke Penyerahan LHU.');
      const card = button.closest('[data-card]');
      if (card) {
        card.remove();
        filterCards();
      }
    } catch (error) {
      await notify('error', error.message || 'Gagal melanjutkan ke Penyerahan LHU.');
      window.WorkflowLoading?.releaseButton(button);
    }
  };

  kodeInput?.addEventListener('input', filterCards);
  perusahaanInput?.addEventListener('input', filterCards);
  resetBtn?.addEventListener('click', () => {
    if (kodeInput) kodeInput.value = '';
    if (perusahaanInput) perusahaanInput.value = '';
    filterCards();
  });

  document.querySelectorAll('[data-upload-input]').forEach((input) => {
    input.addEventListener('change', () => uploadSuket(input));
  });

  document.querySelectorAll('[data-submit-btn]').forEach((button) => {
    button.addEventListener('click', () => submitNext(button));
  });

  availabilitySwitch?.addEventListener('change', () => updateAvailability(availabilitySwitch));

  filterCards();
})();
</script>
@endpush
