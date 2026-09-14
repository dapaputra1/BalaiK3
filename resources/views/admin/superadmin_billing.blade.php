@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
  $statusCounts = $orders->countBy('status_key');
  $paymentGuide = $paymentGuide ?? ['exists' => false];
  $canUploadPaymentGuide = $canUploadPaymentGuide ?? false;
  $paymentGuideUploadUrl = $paymentGuideUploadUrl ?? '';
  $paymentGuideDeleteUrl = $paymentGuideDeleteUrl ?? '';
  $isPaymentGuideReady = !empty($paymentGuide['exists']);
@endphp

<style>
  :root {
    --billing-primary: #0f4c81;
    --billing-primary-soft: #e8f2fb;
    --billing-success-soft: #eaf8f0;
    --billing-border: #dbe6f1;
  }
  .billing-card {
    border: 1px solid var(--billing-border);
    border-radius: 16px;
    box-shadow: 0 8px 18px rgba(16, 37, 62, 0.06);
    background: #fff;
  }
  .billing-meta {
    background: #f7fbff;
    border: 1px dashed #bfd5e7;
    border-radius: 12px;
    padding: 10px 12px;
  }
  .billing-table thead th {
    background: #f6f9fc;
    border-bottom: 1px solid #e6eef6;
  }
  .billing-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 5px 10px;
    font-size: 12px;
    font-weight: 600;
  }
  .billing-pill.belum_mengirim {
    background: #fff4dd;
    color: #7a4b00;
  }
  .billing-pill.menunggu_kode_billing {
    background: #e8f2fb;
    color: #0d4a79;
  }
  .billing-pill.pelanggan_sudah_bayar {
    background: #fff1f2;
    color: #9f1239;
  }
  .billing-pill.selesai {
    background: var(--billing-success-soft);
    color: #0d6a3c;
  }
  .billing-upload {
    border-radius: 10px;
  }
  .billing-action-wrap {
    background: #fbfdff;
    border: 1px solid #e6edf4;
    border-radius: 12px;
    padding: 12px;
  }
  .billing-doc-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    margin-bottom: 8px;
  }
  .billing-doc-actions .btn {
    width: 100%;
  }
  .billing-renewal-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fff7e6;
    color: #8a5a00;
    border: 1px solid #f1d28a;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 600;
  }
  .btn-billing-primary {
    background: var(--billing-primary);
    border-color: var(--billing-primary);
    color: #fff;
  }
  .btn-billing-primary:hover,
  .btn-billing-primary:focus {
    background: #0d406d;
    border-color: #0d406d;
    color: #fff;
  }
  .btn-billing-outline {
    border-color: var(--billing-primary);
    color: var(--billing-primary);
  }
  .btn-billing-outline:hover,
  .btn-billing-outline:focus {
    background: var(--billing-primary);
    border-color: var(--billing-primary);
    color: #fff;
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
  .billing-guide-hero {
    border: 1px solid #dbe6f1;
    border-radius: 16px;
    background: linear-gradient(135deg, #f7fbff 0%, #ffffff 100%);
  }
  .billing-guide-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #fff1f2;
    color: #be123c;
    font-size: 24px;
    box-shadow: inset 0 0 0 1px #fecdd3;
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Tahap Kode Billing',
  'subtitle' => 'Lihat surat tagihan yang sudah di-ACC, upload kode billing, kirim ke pemohon, lalu verifikasi pembayaran.',
  'total' => $orders->count(),
])

@include('admin.partials.payment_guide_card', [
  'paymentGuide' => $paymentGuide,
  'canUploadPaymentGuide' => $canUploadPaymentGuide,
  'paymentGuideUploadUrl' => $paymentGuideUploadUrl,
  'paymentGuideDeleteUrl' => $paymentGuideDeleteUrl,
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
      <button type="button" class="btn btn-outline-primary btn-sm active d-inline-flex align-items-center gap-2" data-filter-status="belum_mengirim">
        Belum Mengirim Kode Billing
        @if((int) ($statusCounts['belum_mengirim'] ?? 0) > 0)
          <span class="badge rounded-pill bg-danger">{{ (int) ($statusCounts['belum_mengirim'] ?? 0) }}</span>
        @endif
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2" data-filter-status="menunggu_kode_billing">
        Menunggu Kode Billing
        @if((int) ($statusCounts['menunggu_kode_billing'] ?? 0) > 0)
          <span class="badge rounded-pill bg-danger">{{ (int) ($statusCounts['menunggu_kode_billing'] ?? 0) }}</span>
        @endif
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2" data-filter-status="pelanggan_sudah_bayar">
        Pelanggan Sudah Bayar
        @if((int) ($statusCounts['pelanggan_sudah_bayar'] ?? 0) > 0)
          <span class="badge rounded-pill bg-danger">{{ (int) ($statusCounts['pelanggan_sudah_bayar'] ?? 0) }}</span>
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
      $statusKey = $order['status_key'] ?? 'belum_mengirim';
      $penawaranItems = collect($order['penawaran_items'] ?? []);
      $pengujianItems = collect($order['pengujian_items'] ?? []);
      $statusLabel = match ($statusKey) {
        'menunggu_kode_billing' => 'Menunggu Konfirmasi Pembayaran',
        'pelanggan_sudah_bayar' => 'Pelanggan Sudah Bayar',
        'selesai' => 'Selesai (Lanjut Kuitansi)',
        default => 'Belum Mengirim Kode Billing',
      };
    @endphp
    <div
      class="billing-card p-3"
      data-card
      data-status="{{ $statusKey }}"
      data-kode="{{ strtolower((string) ($order['kode'] ?? '')) }}"
      data-perusahaan="{{ strtolower((string) ($order['perusahaan'] ?? '')) }}"
    >
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
        <div>
          <div class="fw-semibold">{{ $order['kode'] ?? '-' }}</div>
          <div class="text-muted small">{{ $order['perusahaan'] ?? '-' }}</div>
          <div class="text-muted small">{{ $order['alamat'] ?? '-' }}</div>
          @if(!empty($order['is_billing_renewal_request']))
            <div class="mt-2">
              <span class="billing-renewal-badge">
                <i class="bi bi-arrow-repeat"></i>
                Permintaan billing ulang
              </span>
            </div>
          @endif
        </div>
        <span class="billing-pill {{ $statusKey }}" data-status-pill>
          <i class="bi bi-dot"></i>
          {{ $statusLabel }}
        </span>
      </div>

      <div class="row g-3 mb-3">
        @if($statusKey === 'pelanggan_sudah_bayar')
          <div class="col-12">
            <div class="billing-meta">
              <div class="fw-semibold small mb-2">Setelah Pengujian (Dasar Tagihan)</div>
              <div class="table-responsive">
                <table class="table table-sm align-middle billing-table mb-0">
                  <thead>
                    <tr>
                      <th>Kategori</th>
                      <th>Parameter</th>
                      <th class="text-center" style="width:72px;">Qty</th>
                      <th class="text-end" style="width:120px;">Harga</th>
                      <th class="text-end" style="width:120px;">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($pengujianItems as $item)
                      @php
                        $qty = (int) ($item['qty'] ?? 0);
                        $harga = (float) ($item['harga'] ?? 0);
                      @endphp
                      <tr>
                        <td>{{ $item['kategori'] ?? '-' }}</td>
                        <td>{{ $item['nama'] ?? '-' }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-end">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($qty * $harga, 0, ',', '.') }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <div class="d-flex justify-content-end pt-2">
                <div class="fw-semibold">
                  Total:
                  Rp {{ number_format((float) ($order['subtotal_pengujian'] ?? 0), 0, ',', '.') }}
                </div>
              </div>
            </div>
          </div>
        @else
          <div class="col-12 col-lg-6">
            <div class="billing-meta">
              <div class="fw-semibold small mb-2">Permohonan Awal</div>
              <div class="table-responsive">
                <table class="table table-sm align-middle billing-table mb-0">
                  <thead>
                    <tr>
                      <th>Parameter</th>
                      <th class="text-center" style="width:72px;">Qty</th>
                      <th class="text-end" style="width:120px;">Harga</th>
                      <th class="text-end" style="width:120px;">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($penawaranItems as $item)
                      @php
                        $qty = (int) ($item['qty'] ?? 0);
                        $harga = (float) ($item['harga'] ?? 0);
                      @endphp
                      <tr>
                        <td>{{ $item['nama'] ?? '-' }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-end">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($qty * $harga, 0, ',', '.') }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted">Tidak ada data.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <div class="d-flex justify-content-end pt-2">
                <div class="fw-semibold">
                  Total:
                  Rp {{ number_format((float) ($order['subtotal_penawaran'] ?? 0), 0, ',', '.') }}
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-6">
            <div class="billing-meta">
              <div class="fw-semibold small mb-2">Setelah Pengujian (Dasar Tagihan)</div>
              <div class="table-responsive">
                <table class="table table-sm align-middle billing-table mb-0">
                  <thead>
                    <tr>
                      <th>Parameter</th>
                      <th class="text-center" style="width:72px;">Qty</th>
                      <th class="text-end" style="width:120px;">Harga</th>
                      <th class="text-end" style="width:120px;">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($pengujianItems as $item)
                      @php
                        $qty = (int) ($item['qty'] ?? 0);
                        $harga = (float) ($item['harga'] ?? 0);
                      @endphp
                      <tr>
                        <td>{{ $item['nama'] ?? '-' }}</td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-end">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($qty * $harga, 0, ',', '.') }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted">Tidak ada data.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
              <div class="d-flex justify-content-end pt-2">
                <div class="fw-semibold">
                  Total:
                  Rp {{ number_format((float) ($order['subtotal_pengujian'] ?? 0), 0, ',', '.') }}
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>

      <div class="billing-action-wrap">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
          <div class="small text-muted flex-grow-1">
            @if(!empty($order['invoice_verified_at'])) Surat tagihan di-ACC pelanggan: {{ $order['invoice_verified_at'] }}<br>@endif
            @if(!empty($order['billing_uploaded_at'])) Upload kode billing: {{ $order['billing_uploaded_at'] }} @endif
            @if(!empty($order['billing_expires_at']))<br>Berlaku sampai: {{ $order['billing_expires_at'] }}@endif
            @if(!empty($order['payment_proof_uploaded_at']))<br>Bukti pembayaran diupload: {{ $order['payment_proof_uploaded_at'] }}@endif
            @if(!empty($order['paid_by_user_at']))<br>Dikonfirmasi bayar oleh pemohon: {{ $order['paid_by_user_at'] }}@endif
            @if(!empty($order['verified_at']))<br>Terverifikasi: {{ $order['verified_at'] }}@endif
          </div>

          <div class="d-flex flex-column gap-2" style="min-width: 320px; width: 100%; max-width: 420px;">
            <div class="billing-doc-actions">
              <a href="{{ $order['surat_tagihan_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-billing-outline btn-sm">
                <i class="bi bi-eye"></i> Lihat Surat Tagihan
              </a>
              @if(!empty($order['billing_url']))
                <a href="{{ $order['billing_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm">
                  <i class="bi bi-file-earmark-text"></i> Lihat Kode Billing
                </a>
              @endif
              @if(!empty($order['payment_proof_url']))
                <a href="{{ $order['payment_proof_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-receipt"></i> Lihat Bukti Pembayaran
                </a>
              @endif
            </div>
            @if(($order['status_key'] ?? '') === 'belum_mengirim')
              <div class="d-flex gap-2 flex-wrap">
                <input
                  type="file"
                  class="form-control form-control-sm billing-upload"
                  accept=".pdf,.doc,.docx"
                  data-upload-input
                  data-upload-url="{{ $order['upload_url'] ?? '' }}"
                >
              </div>
              <button
                type="button"
                class="btn btn-billing-primary btn-sm"
                data-send-btn
                data-send-url="{{ $order['send_url'] ?? '' }}"
                data-payment-guide-ready="{{ $isPaymentGuideReady ? '1' : '0' }}"
                @if(empty($order['billing_url'])) disabled @endif
              >
                Kirim Kode Billing ke Pemohon
              </button>
            @elseif(($order['status_key'] ?? '') === 'menunggu_kode_billing')
              <div class="small text-muted">
                Menunggu konfirmasi pembayaran dari pemohon. Jika ada pengajuan kode billing ulang, upload dan kirim ulang dari sini.
              </div>
              <div class="d-flex gap-2 flex-wrap">
                <input type="file" class="form-control form-control-sm billing-upload" accept=".pdf,.doc,.docx" data-upload-input>
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm"
                  data-upload-btn
                  data-upload-url="{{ $order['upload_url'] ?? '' }}"
                >
                  Upload Ulang Kode Billing
                </button>
              </div>
              <button
                type="button"
                class="btn btn-billing-primary btn-sm"
                data-send-btn
                data-send-url="{{ $order['send_url'] ?? '' }}"
                data-payment-guide-ready="{{ $isPaymentGuideReady ? '1' : '0' }}"
              >
                Kirim Ulang Kode Billing
              </button>
            @elseif(($order['status_key'] ?? '') === 'pelanggan_sudah_bayar')
              <button
                type="button"
                class="btn btn-success btn-sm"
                data-verify-btn
                data-verify-url="{{ $order['verify_url'] ?? '' }}"
              >
                Verifikasi Pembayaran
              </button>
            @else
              <div class="alert alert-success mb-0 small">
                Tahap kode billing selesai. Permohonan lanjut ke tahap kuitansi.
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="text-center text-muted py-4">Belum ada permohonan pada tahap kode billing.</div>
  @endforelse
</div>

<div class="text-center text-muted d-none mt-3" data-search-empty>Belum ada permohonan pada status ini.</div>

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
  const guideFileInput = document.querySelector('[data-payment-guide-file-input]');
  const guideUploadTrigger = document.querySelector('[data-payment-guide-upload-trigger]');
  const guideDeleteTrigger = document.querySelector('[data-payment-guide-delete-trigger]');
  let activeStatus = 'belum_mengirim';

  const notify = async (type, message) => {
    if (window.Swal) {
      await window.Swal.fire({
        icon: type,
        title: type === 'success' ? 'Berhasil' : (type === 'warning' ? 'Peringatan' : 'Gagal'),
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

  const uploadBilling = async (fileInput) => {
    const file = fileInput?.files?.[0];
    const uploadUrl = fileInput?.getAttribute('data-upload-url') || '';

    if (!uploadUrl) return;
    if (!file) {
      await notify('warning', 'Pilih file kode billing terlebih dahulu.');
      return;
    }

    const formData = new FormData();
    formData.append('billing_file', file);

    fileInput.disabled = true;
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
      if (!response.ok) throw new Error(payload.message || 'Upload kode billing gagal.');
      await notify('success', payload.message || 'Kode billing berhasil diupload.');
      window.location.reload();
    } catch (error) {
      await notify('error', error.message || 'Upload kode billing gagal.');
      fileInput.disabled = false;
      fileInput.value = '';
    }
  };

  const uploadPaymentGuide = async () => {
    const file = guideFileInput?.files?.[0];
    const uploadUrl = guideFileInput?.getAttribute('data-upload-url') || '';

    if (!uploadUrl) return;
    if (!file) {
      await notify('warning', 'Pilih file panduan pembayaran terlebih dahulu.');
      return;
    }

    const formData = new FormData();
    formData.append('guide_file', file);

    if (guideUploadTrigger) {
      window.WorkflowLoading?.setButtonLoading(guideUploadTrigger, true);
      guideUploadTrigger.disabled = true;
    }
    if (guideFileInput) {
      guideFileInput.disabled = true;
    }

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
      if (!response.ok) {
        throw new Error(payload.message || 'Upload panduan pembayaran gagal.');
      }

      await notify('success', payload.message || 'Panduan pembayaran berhasil disimpan.');
      window.location.reload();
    } catch (error) {
      await notify('error', error.message || 'Upload panduan pembayaran gagal.');
      if (guideFileInput) {
        guideFileInput.disabled = false;
        guideFileInput.value = '';
      }
      if (guideUploadTrigger) {
        window.WorkflowLoading?.releaseButton(guideUploadTrigger);
        guideUploadTrigger.disabled = false;
      }
    }
  };

  const deletePaymentGuide = async () => {
    const deleteUrl = guideDeleteTrigger?.getAttribute('data-delete-url') || '';

    if (!deleteUrl) return;

    const proceed = window.Swal
      ? await window.Swal.fire({
          icon: 'warning',
          title: 'Hapus panduan pembayaran?',
          text: 'File panduan pembayaran akan dihapus.',
          showCancelButton: true,
          confirmButtonText: 'Hapus',
          cancelButtonText: 'Batal',
        }).then((r) => r.isConfirmed)
      : confirm('Hapus panduan pembayaran sekarang?');
    if (!proceed) return;

    if (guideDeleteTrigger) {
      window.WorkflowLoading?.setButtonLoading(guideDeleteTrigger, true);
      guideDeleteTrigger.disabled = true;
    }

    try {
      const response = await fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(payload.message || 'Hapus panduan pembayaran gagal.');
      }

      await notify('success', payload.message || 'Panduan pembayaran berhasil dihapus.');
      window.location.reload();
    } catch (error) {
      await notify('error', error.message || 'Hapus panduan pembayaran gagal.');
      if (guideDeleteTrigger) {
        window.WorkflowLoading?.releaseButton(guideDeleteTrigger);
        guideDeleteTrigger.disabled = false;
      }
    }
  };

  const postSimple = async (url, question, successMsg, button = null) => {
    if (!url) return;
    const proceed = window.Swal
      ? await window.Swal.fire({
          icon: 'question',
          title: question,
          showCancelButton: true,
          confirmButtonText: 'Ya',
          cancelButtonText: 'Batal',
        }).then((r) => r.isConfirmed)
      : confirm(question);
    if (!proceed) return;

    if (button) {
      window.WorkflowLoading?.setButtonLoading(button, true);
    }

    try {
      const formData = new FormData();
      formData.append('_token', csrf);

      const response = await fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: formData,
      });
      const payload = await response.json().catch(() => ({}));
      if (!response.ok) throw new Error(payload.message || 'Permintaan gagal.');

      await notify('success', payload.message || successMsg);
      window.location.reload();
    } catch (error) {
      if (button) {
        window.WorkflowLoading?.releaseButton(button);
      }
      throw error;
    }
  };

  statusButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      statusButtons.forEach((x) => x.classList.remove('active'));
      btn.classList.add('active');
      activeStatus = (btn.getAttribute('data-filter-status') || '').toLowerCase();
      filterCards();
    });
  });

  document.addEventListener('click', async (event) => {
    const sendBtn = event.target.closest('[data-send-btn]');
    if (sendBtn) {
      if (sendBtn.getAttribute('data-payment-guide-ready') !== '1') {
        await notify('warning', 'Upload panduan pembayaran terlebih dahulu sebelum mengirim kode billing ke pemohon.');
        return;
      }

      try {
        await postSimple(sendBtn.getAttribute('data-send-url') || '', 'Kirim kode billing ke pemohon?', 'Kode billing berhasil dikirim.', sendBtn);
      } catch (error) {
        await notify('error', error.message || 'Gagal mengirim kode billing.');
      }
      return;
    }

    const verifyBtn = event.target.closest('[data-verify-btn]');
    if (verifyBtn) {
      try {
        await postSimple(verifyBtn.getAttribute('data-verify-url') || '', 'Verifikasi pembayaran dan lanjut ke tahap kuitansi?', 'Pembayaran berhasil diverifikasi.', verifyBtn);
      } catch (error) {
        await notify('error', error.message || 'Gagal verifikasi pembayaran.');
      }
      return;
    }

    if (event.target.closest('[data-payment-guide-upload-trigger]')) {
      guideFileInput?.click();
      return;
    }

    if (event.target.closest('[data-payment-guide-delete-trigger]')) {
      await deletePaymentGuide();
    }
  });

  document.addEventListener('change', async (event) => {
    const uploadInput = event.target.closest('[data-upload-input]');
    if (uploadInput) {
      await uploadBilling(uploadInput);
      return;
    }

    if (event.target === guideFileInput) {
      await uploadPaymentGuide();
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
