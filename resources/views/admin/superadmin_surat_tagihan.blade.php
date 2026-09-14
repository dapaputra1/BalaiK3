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
  'title' => 'Alur Kerja - Surat Tagihan',
  'subtitle' => 'Siapkan surat tagihan resmi setelah LHU ditandatangani, lalu kirim ke pemohon untuk ACC sebelum tahap kode billing.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4">
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
  </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mt-3">
  <div class="card-body p-0">
    <div class="px-4 pb-4 pt-4">
      @forelse($orders as $order)
        <div
          class="border rounded-4 p-3 mb-3 shadow-sm bg-white"
          data-card
          data-kode="{{ strtolower((string) ($order['kode'] ?? '')) }}"
          data-perusahaan="{{ strtolower((string) ($order['perusahaan'] ?? '')) }}"
        >
          <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
            <div>
              <div class="fw-semibold">{{ $order['kode'] ?? '-' }}</div>
              <div class="text-muted small">{{ $order['perusahaan'] ?? '-' }}</div>
              <div class="text-muted small">{{ $order['alamat'] ?? '-' }}</div>
            </div>
            @if(!empty($order['has_perubahan_pengujian']))
              <span class="badge text-bg-warning px-3 py-2">Ada Perubahan Setelah Pengujian</span>
            @else
              <span class="badge text-bg-success px-3 py-2">Data Penawaran = Pengujian</span>
            @endif
          </div>

          <div class="row g-3 mt-1">
            <div class="col-12 col-lg-6">
              <div class="fw-semibold mb-2">Permohonan Awal (Penawaran)</div>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Parameter</th>
                      <th class="text-center" style="width: 90px;">Qty</th>
                      <th class="text-end" style="width: 150px;">Harga</th>
                      <th class="text-end" style="width: 150px;">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse(($order['penawaran_items'] ?? []) as $item)
                      @php
                        $qty = (int) ($item['qty'] ?? 0);
                        $harga = (float) ($item['harga'] ?? 0);
                        $sub = $qty * $harga;
                      @endphp
                      <tr>
                        <td>{{ $item['nama'] ?? '-' }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-end">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada data.</td>
                      </tr>
                    @endforelse
                  </tbody>
                  <tfoot class="table-light">
                    <tr>
                      <th colspan="3" class="text-end">Subtotal</th>
                      <th class="text-end">Rp {{ number_format((float) ($order['subtotal_penawaran'] ?? 0), 0, ',', '.') }}</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <div class="col-12 col-lg-6">
              <div class="fw-semibold mb-2">Setelah Pengujian</div>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Parameter</th>
                      <th class="text-center" style="width: 90px;">Qty</th>
                      <th class="text-end" style="width: 150px;">Harga</th>
                      <th class="text-end" style="width: 150px;">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse(($order['pengujian_items'] ?? []) as $item)
                      @php
                        $qty = (int) ($item['qty'] ?? 0);
                        $harga = (float) ($item['harga'] ?? 0);
                        $sub = $qty * $harga;
                      @endphp
                      <tr>
                        <td>{{ $item['nama'] ?? '-' }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-end">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada data.</td>
                      </tr>
                    @endforelse
                  </tbody>
                  <tfoot class="table-light">
                    <tr>
                      <th colspan="3" class="text-end">Subtotal</th>
                      <th class="text-end">Rp {{ number_format((float) ($order['subtotal_pengujian'] ?? 0), 0, ',', '.') }}</th>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <div class="border rounded-3 p-3 bg-light mt-3">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
              <div>
                <div class="small text-muted">Dokumen Surat Tagihan</div>
                <div class="fw-semibold">Surat pengantar penagihan sebelum kode billing diterbitkan</div>
                @if(!empty($order['generated_at']))
                  <div class="small text-muted mt-1">Dibuat: {{ $order['generated_at'] }}</div>
                @endif
                <a href="{{ $order['surat_tagihan_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm mt-2">
                  Lihat Surat Tagihan
                </a>
              </div>

              <div class="d-flex gap-2 flex-wrap">
                <button
                  type="button"
                  class="btn btn-primary btn-sm"
                  data-submit-btn
                  data-submit-url="{{ $order['submit_url'] ?? '' }}"
                >
                  Kirim Surat Tagihan
                </button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-4">Belum ada data surat tagihan.</div>
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
      const show = (!kodeVal || kode.includes(kodeVal))
        && (!perusahaanVal || perusahaan.includes(perusahaanVal));
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

  const postJson = async (url) => {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
      },
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(data.message || 'Permintaan gagal diproses.');
    }
    return data;
  };

  document.addEventListener('click', async (event) => {
    const submitBtn = event.target.closest('[data-submit-btn]');
    if (!submitBtn) return;

    const submitUrl = submitBtn.getAttribute('data-submit-url') || '';
    if (!submitUrl) return;

    const proceed = window.Swal
      ? await window.Swal.fire({
          icon: 'question',
          title: 'Kirim Surat Tagihan?',
          text: 'Surat tagihan akan dikirim ke pemohon. Setelah pemohon ACC, petugas bisa kirim kode billing.',
          showCancelButton: true,
          confirmButtonText: 'Ya, kirim',
          cancelButtonText: 'Batal',
        }).then((r) => r.isConfirmed)
      : confirm('Kirim surat tagihan sekarang?');

    if (!proceed) return;

    window.WorkflowLoading?.setButtonLoading(submitBtn, true);
    try {
      const data = await postJson(submitUrl);
      await notify('success', data.message || 'Surat tagihan berhasil dikirim.');
      window.location.reload();
    } catch (error) {
      await notify('error', error.message || 'Gagal mengirim surat tagihan.');
      window.WorkflowLoading?.releaseButton(submitBtn);
    }
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
