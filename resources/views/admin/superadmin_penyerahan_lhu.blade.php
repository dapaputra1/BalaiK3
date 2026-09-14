@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
  $statusCounts = $orders->countBy('status_key');
@endphp

<style>
  .handover-card {
    border: 1px solid #dbe6f1;
    border-radius: 16px;
    box-shadow: 0 8px 18px rgba(16, 37, 62, 0.06);
    background: #fff;
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
  'title' => 'Alur Kerja - Penyerahan LHU',
  'subtitle' => 'Kelola penyerahan LHU: belum diserahkan, menunggu, revisi, dan selesai.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4 mb-3">
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
    <div class="d-flex gap-2 flex-wrap mt-3">
      <button type="button" class="btn btn-outline-primary btn-sm active d-inline-flex align-items-center gap-2" data-filter-status="belum_diserahkan">
        Belum Diserahkan
        @if((int) ($statusCounts['belum_diserahkan'] ?? 0) > 0)
          <span class="badge rounded-pill bg-danger">{{ (int) ($statusCounts['belum_diserahkan'] ?? 0) }}</span>
        @endif
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2" data-filter-status="menunggu">
        Menunggu
        @if((int) ($statusCounts['menunggu'] ?? 0) > 0)
          <span class="badge rounded-pill bg-danger">{{ (int) ($statusCounts['menunggu'] ?? 0) }}</span>
        @endif
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2" data-filter-status="revisi">
        Revisi
        @if((int) ($statusCounts['revisi'] ?? 0) > 0)
          <span class="badge rounded-pill bg-danger">{{ (int) ($statusCounts['revisi'] ?? 0) }}</span>
        @endif
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2" data-filter-status="selesai">
        Selesai
      </button>
    </div>
  </div>
</div>

<div class="d-flex flex-column gap-3">
  @forelse($orders as $order)
    @php
      $statusKey = $order['status_key'] ?? 'belum_diserahkan';
      $statusLabel = match ($statusKey) {
        'menunggu' => 'Menunggu respon pemohon',
        'revisi' => 'Revisi dari pemohon',
        'selesai' => 'Selesai',
        default => 'Belum diserahkan',
      };
    @endphp
    <div
      class="handover-card p-3"
      data-card
      data-status="{{ $statusKey }}"
      data-kode="{{ strtolower((string) ($order['kode'] ?? '')) }}"
      data-perusahaan="{{ strtolower((string) ($order['perusahaan'] ?? '')) }}"
    >
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
        <div>
          <div class="fw-semibold">{{ $order['kode'] ?? '-' }}</div>
          <div class="text-muted small">{{ $order['perusahaan'] ?? '-' }}</div>
          <div class="text-muted small">{{ $order['alamat'] ?? '-' }}</div>
        </div>
        <span class="badge {{ $statusKey === 'selesai' ? 'text-bg-success' : ($statusKey === 'revisi' ? 'text-bg-danger' : ($statusKey === 'menunggu' ? 'text-bg-info' : 'text-bg-warning')) }}">
          {{ $statusLabel }}
        </span>
      </div>

      @if($statusKey === 'revisi' && !empty($order['revision_note']))
        <div class="alert alert-danger py-2 px-3 mb-2">
          <div class="small fw-semibold mb-1">Catatan revisi pemohon</div>
          <div class="small mb-0">{{ $order['revision_note'] }}</div>
          @if(!empty($order['revision_at']))
            <div class="small text-muted mt-1">Diajukan: {{ $order['revision_at'] }}</div>
          @endif
        </div>
      @endif

      <div class="border rounded-3 p-3 bg-light d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div class="d-flex flex-column gap-3">
          <div>
            <div class="small text-muted">LHU TTD</div>
            <div class="fw-semibold">{{ $order['signed_lhu_name'] ?? 'LHU TTD' }}</div>
            @if(!empty($order['signed_lhu_url']))
              <a href="{{ $order['signed_lhu_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mt-2">Lihat LHU TTD</a>
            @endif
          </div>
          <div>
            <div class="small text-muted">Surat Keterangan</div>
            <div class="fw-semibold">
              @if(empty($order['suket_required']) && empty($order['suket_url']))
                Tahap surat keterangan dilewati
              @else
                {{ $order['suket_name'] ?? 'Surat Keterangan' }}
              @endif
            </div>
            @if(!empty($order['suket_uploaded_at']))
              <div class="small text-muted mt-1">Diupload: {{ $order['suket_uploaded_at'] }}</div>
            @elseif(empty($order['suket_required']))
              <div class="small text-muted mt-1">Penerbitan suket sedang nonaktif, sehingga tahap ini dilewati.</div>
            @endif
            @if(!empty($order['suket_url']))
              <a href="{{ $order['suket_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mt-2">Lihat Surat Keterangan</a>
            @elseif(!empty($order['suket_required']))
              <div class="small text-danger mt-2">Dokumen surat keterangan belum tersedia.</div>
            @endif
          </div>
          @if(!empty($order['sent_at']))
            <div class="small text-muted">Terakhir dikirim ke pemohon: {{ $order['sent_at'] }}</div>
          @endif
          @if(!empty($order['approved_at']))
            <div class="small text-success">Disetujui pemohon: {{ $order['approved_at'] }}</div>
          @endif
        </div>
        <div>
          @if($statusKey === 'revisi')
            <div class="d-flex flex-column gap-2" style="min-width: 320px;">
              <div class="d-flex gap-2 flex-wrap">
                <input type="file" class="form-control form-control-sm" accept=".pdf,.doc,.docx" data-upload-input>
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm"
                  data-upload-btn
                  data-upload-url="{{ $order['upload_url'] ?? '' }}"
                >
                  Upload Dokumen LHU Revisi
                </button>
              </div>
              <button
                type="button"
                class="btn btn-primary btn-sm"
                data-send-btn
                data-send-url="{{ $order['send_url'] ?? '' }}"
                @if(empty($order['can_send_to_user'])) disabled @endif
              >
                Kirim Ulang LHU ke Pemohon
              </button>
              @if(empty($order['can_send_to_user']))
                <div class="small text-danger">Upload dokumen LHU revisi terbaru sebelum kirim ulang.</div>
              @endif
            </div>
          @elseif($statusKey === 'belum_diserahkan')
            <button
              type="button"
              class="btn btn-primary btn-sm"
              data-send-btn
              data-send-url="{{ $order['send_url'] ?? '' }}"
              @if(empty($order['can_send_to_user'])) disabled @endif
            >
              Teruskan LHU ke Pemohon
            </button>
          @elseif($statusKey === 'menunggu')
            <button type="button" class="btn btn-outline-secondary btn-sm" disabled>Menunggu Respon Pemohon</button>
          @else
            <button type="button" class="btn btn-success btn-sm" disabled>Selesai</button>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="text-center text-muted py-4">Belum ada data penyerahan LHU.</div>
  @endforelse
</div>

<div class="text-center text-muted d-none mt-3" data-search-empty>Tidak ada permohonan yang cocok.</div>
@endsection

@push('scripts')
<script>
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const kodeInput = document.querySelector('[data-search-kode]');
  const perusahaanInput = document.querySelector('[data-search-perusahaan]');
  const resetBtn = document.querySelector('[data-search-reset]');
  const statusButtons = document.querySelectorAll('[data-filter-status]');
  const cards = document.querySelectorAll('[data-card]');
  const emptyState = document.querySelector('[data-search-empty]');
  let activeStatus = 'belum_diserahkan';

  const notify = async (type, message) => {
    if (window.Swal) {
      await window.Swal.fire({
        icon: type,
        title: type === 'success' ? 'Berhasil' : 'Gagal',
        text: message || '',
      });
      return;
    }
    alert(message || '');
  };

  const filterCards = () => {
    const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
    const perusahaanVal = (perusahaanInput?.value || '').toLowerCase().trim();
    let visible = 0;

    cards.forEach((card) => {
      const kode = (card.getAttribute('data-kode') || '').toLowerCase();
      const perusahaan = (card.getAttribute('data-perusahaan') || '').toLowerCase();
      const status = (card.getAttribute('data-status') || '').toLowerCase();
      const show = (!activeStatus || status === activeStatus)
        && (!kodeVal || kode.includes(kodeVal))
        && (!perusahaanVal || perusahaan.includes(perusahaanVal));
      card.classList.toggle('d-none', !show);
      if (show) visible += 1;
    });

    if (emptyState) {
      const hasCards = cards.length > 0;
      emptyState.classList.toggle('d-none', !hasCards || visible > 0);
    }
  };

  const uploadLhuRevision = async (btn) => {
    const card = btn.closest('[data-card]');
    const fileInput = card?.querySelector('[data-upload-input]');
    const file = fileInput?.files?.[0];
    const uploadUrl = btn.getAttribute('data-upload-url') || '';
    if (!uploadUrl) return;

    if (!file) {
      await notify('error', 'Pilih file LHU revisi terlebih dahulu.');
      return;
    }

    const formData = new FormData();
    formData.append('signed_lhu_file', file);

    btn.disabled = true;
    try {
      const response = await fetch(uploadUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: formData,
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(payload.message || 'Upload dokumen LHU revisi gagal.');

      await notify('success', payload.message || 'Dokumen LHU revisi berhasil diupload.');
      window.location.reload();
    } catch (error) {
      await notify('error', error.message || 'Upload dokumen LHU revisi gagal.');
      btn.disabled = false;
    }
  };

  document.addEventListener('click', async (event) => {
    const uploadBtn = event.target.closest('[data-upload-btn]');
    if (uploadBtn) {
      uploadLhuRevision(uploadBtn);
      return;
    }

    const sendBtn = event.target.closest('[data-send-btn]');
    if (!sendBtn) return;

    const sendUrl = sendBtn.getAttribute('data-send-url') || '';
    if (!sendUrl) return;

    const proceed = window.Swal
      ? await window.Swal.fire({
          icon: 'question',
          title: 'Teruskan LHU ke pemohon?',
          showCancelButton: true,
          confirmButtonText: 'Ya, teruskan',
          cancelButtonText: 'Batal',
        }).then((r) => r.isConfirmed)
      : confirm('Teruskan LHU ke pemohon?');

    if (!proceed) return;

    sendBtn.disabled = true;
    try {
      const response = await fetch(sendUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(payload.message || 'Gagal meneruskan LHU.');

      await notify('success', payload.message || 'LHU dan surat keterangan berhasil diteruskan ke pemohon.');
      window.location.reload();
    } catch (error) {
      await notify('error', error.message || 'Gagal meneruskan LHU.');
      sendBtn.disabled = false;
    }
  });

  statusButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      statusButtons.forEach((x) => x.classList.remove('active'));
      btn.classList.add('active');
      activeStatus = (btn.getAttribute('data-filter-status') || '').toLowerCase();
      filterCards();
    });
  });

  kodeInput?.addEventListener('input', filterCards);
  perusahaanInput?.addEventListener('input', filterCards);
  resetBtn?.addEventListener('click', () => {
    if (kodeInput) kodeInput.value = '';
    if (perusahaanInput) perusahaanInput.value = '';
    filterCards();
  });

  filterCards();
})();
</script>
@endpush
