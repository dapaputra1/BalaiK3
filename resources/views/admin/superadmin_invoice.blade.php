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
  .btn-outline-primary.active,
  .btn-outline-primary:active {
    background-color: #15406A !important;
    color: #fff !important;
    border-color: #15406A !important;
  }
  [data-filter-status] {
    font-size: 13px;
    font-weight: 400;
  }
  [data-filter-status] .badge {
    font-size: 13px;
    line-height: 1;
    font-weight: 400;
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
  @media (min-width: 768px) {
    .border-end-md {
      border-right: 1px solid #dee2e6;
    }
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Kuitansi',
  'subtitle' => 'Setelah pembayaran terverifikasi, teruskan kuitansi ke pelanggan lalu lanjutkan ke tahap penerbitan suket.',
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
    <div class="d-flex flex-wrap gap-2 mt-3">
      <button type="button" class="btn btn-outline-primary btn-sm active" data-filter-status="belum_kirim">
        Belum Kirim Kuitansi
        <span class="badge text-bg-danger ms-1 d-none" data-status-count="belum_kirim">0</span>
      </button>
      <button type="button" class="btn btn-outline-primary btn-sm" data-filter-status="terkirim">
        Sudah Diteruskan
        <span class="badge text-bg-danger ms-1 d-none" data-status-count="terkirim">0</span>
      </button>
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
          data-invoice-status="{{ $order['kuitansi_status'] ?? 'belum_kirim' }}"
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

          {{-- ========================================================================= --}}
          {{-- [PERCOBAAN KUITANSI TTD BASAH] - Card Dokumen Kuitansi Resmi & Form Upload --}}
          {{-- ========================================================================= --}}
          <div class="border rounded-4 p-3 bg-light mt-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-check text-primary fs-5"></i>
                <span class="fw-semibold">Dokumen Kuitansi Resmi (TTD Basah)</span>
              </div>
              <div>
                @if(($order['kuitansi_status'] ?? 'belum_kirim') === 'terkirim')
                  <span class="badge text-bg-success px-3 py-2">
                    <i class="bi bi-check2-circle me-1"></i>Kuitansi Sudah Diteruskan ({{ $order['kuitansi_sent_at'] ?? '-' }})
                  </span>
                @else
                  @if(!empty($order['has_signed_invoice']))
                    <span class="badge text-bg-info text-white px-3 py-2" data-status-badge>
                      <i class="bi bi-check2 me-1"></i>Kuitansi Bertanda Tangan Siap Diteruskan
                    </span>
                  @else
                    <span class="badge text-bg-warning px-3 py-2" data-status-badge>
                      <i class="bi bi-exclamation-circle me-1"></i>Wajib Upload Kuitansi TTD Basah
                    </span>
                  @endif
                @endif
              </div>
            </div>

            <div class="row g-3">
              <!-- Langkah 1: Kuitansi Otomatis -->
              <div class="col-12 col-md-4 border-end-md">
                <div class="d-flex flex-column h-100 justify-content-between">
                  <div>
                    <div class="small text-muted fw-semibold text-uppercase mb-1">Langkah 1: Cetak Draft</div>
                    <div class="fw-semibold small">Kuitansi Otomatis Sistem</div>
                    <div class="text-muted small mt-1" style="font-size: 12px;">
                      Cetak kuitansi otomatis yang digenerate oleh sistem untuk ditandatangani basah.
                    </div>
                  </div>
                  <div class="mt-3">
                    <a href="{{ $order['invoice_url'] ?? '#' }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm w-100">
                      <i class="bi bi-printer me-1"></i>Lihat / Cetak Kuitansi
                    </a>
                  </div>
                </div>
              </div>

              <!-- Langkah 2: Upload Kuitansi TTD Basah -->
              <div class="col-12 col-md-5 border-end-md">
                <div class="d-flex flex-column h-100 justify-content-between">
                  <div>
                    <div class="small text-muted fw-semibold text-uppercase mb-1">Langkah 2: Scan & Upload</div>
                    <div class="fw-semibold small" data-signed-name>
                      @if(!empty($order['signed_invoice_name']))
                        <i class="bi bi-file-earmark-pdf text-danger me-1"></i>{{ $order['signed_invoice_name'] }}
                      @else
                        Belum ada file kuitansi bertanda tangan
                      @endif
                    </div>
                    <div class="text-muted small mt-1" style="font-size: 12px;" data-signed-time>
                      @if(!empty($order['signed_invoice_uploaded_at']))
                        Diupload: {{ $order['signed_invoice_uploaded_at'] }}
                      @else
                        Upload file PDF hasil scan kuitansi yang sudah ditandatangani basah.
                      @endif
                    </div>
                  </div>
                  <div class="mt-3 d-flex flex-column gap-2">
                    @if(($order['kuitansi_status'] ?? 'belum_kirim') !== 'terkirim')
                      <div class="input-group input-group-sm">
                        <input
                          type="file"
                          class="form-control form-control-sm"
                          accept=".pdf"
                          data-upload-input
                          data-upload-url="{{ $order['upload_url'] ?? '' }}"
                        >
                      </div>
                    @endif
                    <a
                      href="{{ $order['signed_invoice_url'] ?? '#' }}"
                      target="_blank"
                      rel="noopener"
                      class="btn btn-outline-success btn-sm w-100 {{ empty($order['signed_invoice_url']) ? 'd-none' : '' }}"
                      data-signed-link
                    >
                      <i class="bi bi-eye me-1"></i>Lihat Kuitansi Bertanda Tangan
                    </a>
                  </div>
                </div>
              </div>

              <!-- Langkah 3: Teruskan ke Pelanggan -->
              <div class="col-12 col-md-3">
                <div class="d-flex flex-column h-100 justify-content-between">
                  <div>
                    <div class="small text-muted fw-semibold text-uppercase mb-1">Langkah 3: Penerbitan</div>
                    <div class="fw-semibold small">Teruskan ke Pemohon</div>
                    <div class="text-muted small mt-1" style="font-size: 12px;">
                      Setelah diteruskan, pelanggan dapat mengunduh kuitansi resmi bertanda tangan dan alur lanjut ke Suket atau Penyerahan LHU.
                    </div>
                  </div>
                  <div class="mt-3">
                    @if(($order['kuitansi_status'] ?? 'belum_kirim') === 'terkirim')
                      <button type="button" class="btn btn-secondary btn-sm w-100" disabled>
                        <i class="bi bi-check2-circle me-1"></i>Sudah Diteruskan
                      </button>
                    @else
                      <button
                        type="button"
                        class="btn btn-sm w-100 {{ !empty($order['can_submit']) ? 'btn-primary' : 'btn-outline-primary' }}"
                        data-submit-btn
                        data-submit-url="{{ $order['submit_url'] ?? '' }}"
                        data-can-submit="{{ !empty($order['can_submit']) ? '1' : '0' }}"
                      >
                        <i class="bi bi-send me-1"></i>Teruskan Kuitansi
                      </button>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
          {{-- ========================================================================= --}}
          {{-- [/PERCOBAAN KUITANSI TTD BASAH] --}}
          {{-- ========================================================================= --}}
        </div>
      @empty
        <div class="text-center text-muted py-4">Belum ada data kuitansi.</div>
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
  const statusButtons = document.querySelectorAll('[data-filter-status]');
  const statusCountEls = document.querySelectorAll('[data-status-count]');
  const cards = document.querySelectorAll('[data-card]');
  const emptyState = document.querySelector('[data-search-empty]');
  let activeStatus = 'belum_kirim';

  const updateStatusCounts = () => {
    if (!statusCountEls.length) return;
    const counts = {};
    cards.forEach((card) => {
      const status = (card.getAttribute('data-invoice-status') || '').toLowerCase().trim();
      if (!status) return;
      counts[status] = (counts[status] || 0) + 1;
    });

    statusCountEls.forEach((el) => {
      const status = (el.getAttribute('data-status-count') || '').toLowerCase().trim();
      const count = counts[status] || 0;
      el.textContent = String(count);
      el.classList.toggle('d-none', count === 0);
    });
  };

  const filterCards = () => {
    const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
    const perusahaanVal = (perusahaanInput?.value || '').toLowerCase().trim();
    let visible = 0;
    cards.forEach((card) => {
      const kode = (card.getAttribute('data-kode') || '').toLowerCase();
      const perusahaan = (card.getAttribute('data-perusahaan') || '').toLowerCase();
      const status = (card.getAttribute('data-invoice-status') || 'belum_kirim').toLowerCase();
      const show = (!kodeVal || kode.includes(kodeVal))
        && (!perusahaanVal || perusahaan.includes(perusahaanVal))
        && (!activeStatus || status === activeStatus);
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

  const postJson = async (url, payload = {}) => {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(data.message || 'Permintaan gagal diproses.');
    }
    return data;
  };

  // =========================================================================
  // [PERCOBAAN KUITANSI TTD BASAH] - Handler upload file PDF kuitansi via AJAX
  // =========================================================================
  const uploadFile = async (input) => {
    const card = input.closest('[data-card]');
    if (!card) return;
    const file = input?.files?.[0];
    const url = input?.getAttribute('data-upload-url') || '';
    if (!url) return;
    if (!file) {
      notify('warning', 'Pilih file PDF kuitansi bertanda tangan terlebih dahulu.');
      return;
    }

    if (!file.name.toLowerCase().endsWith('.pdf') && file.type !== 'application/pdf') {
      notify('warning', 'File harus berformat PDF.');
      input.value = '';
      return;
    }

    const formData = new FormData();
    formData.append('signed_invoice_file', file);

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
        throw new Error(data.message || 'Gagal upload file kuitansi.');
      }

      const nameNode = card.querySelector('[data-signed-name]');
      const timeNode = card.querySelector('[data-signed-time]');
      const linkNode = card.querySelector('[data-signed-link]');
      const statusBadge = card.querySelector('[data-status-badge]');
      const submitBtn = card.querySelector('[data-submit-btn]');

      if (nameNode) {
        nameNode.innerHTML = `<i class="bi bi-file-earmark-pdf text-danger me-1"></i>${data.name || 'Kuitansi TTD'}`;
      }
      if (timeNode) {
        timeNode.textContent = data.uploaded_at ? `Diupload: ${data.uploaded_at}` : '';
      }
      if (linkNode && data.url) {
        linkNode.href = data.url;
        linkNode.classList.remove('d-none');
      }
      if (statusBadge) {
        statusBadge.className = 'badge text-bg-info text-white px-3 py-2';
        statusBadge.innerHTML = '<i class="bi bi-check2 me-1"></i>Kuitansi Bertanda Tangan Siap Diteruskan';
      }
      if (submitBtn) {
        submitBtn.dataset.canSubmit = '1';
        submitBtn.classList.remove('btn-outline-primary');
        submitBtn.classList.add('btn-primary');
      }

      await notify('success', data.message || 'File kuitansi bertanda tangan berhasil diupload.');
    } catch (error) {
      await notify('error', error.message || 'Gagal upload file kuitansi.');
    } finally {
      input.disabled = false;
      input.value = '';
    }
  };
  // =========================================================================
  // [/PERCOBAAN KUITANSI TTD BASAH] - Handler upload selesai
  // =========================================================================

  document.addEventListener('change', (event) => {
    const uploadInput = event.target.closest('[data-upload-input]');
    if (uploadInput) {
      uploadFile(uploadInput);
    }
  });

  document.addEventListener('click', async (event) => {
    const submitBtn = event.target.closest('[data-submit-btn]');
    if (submitBtn) {
      const submitUrl = submitBtn.getAttribute('data-submit-url') || '';
      if (!submitUrl) return;

      // [PERCOBAAN KUITANSI TTD BASAH] - Validasi wajib upload sebelum teruskan
      if ((submitBtn.dataset.canSubmit || '0') !== '1') {
        notify('warning', 'Upload PDF kuitansi bertanda tangan basah terlebih dahulu sebelum meneruskan ke pelanggan.');
        return;
      }

      let nextStep = 'penyerahan_lhu';

      // [PERCOBAAN KUITANSI TTD BASAH] - Modal konfirmasi penerusan kuitansi & opsi suket/penyerahan LHU
      if (window.Swal) {
        const swalResult = await window.Swal.fire({
          title: 'Teruskan Kuitansi ke Pelanggan',
          html: `
            <p class="text-muted small mb-3">
              Kuitansi bertanda tangan basah akan resmi diteruskan dan dapat diunduh oleh pelanggan di Riwayat Pelayanan.
            </p>
            <div class="text-start p-3 bg-light rounded-3 border" style="font-size: 13px;">
              <label class="fw-semibold small mb-2 d-block text-dark">Pilih alur selanjutnya untuk permohonan ini:</label>
              <div class="form-check mb-3 p-2 bg-white rounded border opacity-75">
                <input class="form-check-input ms-0 me-2" type="radio" name="swal_next_step" id="step_suket" value="suket" disabled>
                <label class="form-check-label w-100" for="step_suket" style="cursor: not-allowed;">
                  <span class="fw-semibold text-secondary d-flex align-items-center justify-content-between">
                    <span>1. Lanjut ke Penerbitan Suket</span>
                    <span class="badge text-bg-secondary" style="font-size: 10px;">Dalam Penyesuaian</span>
                  </span>
                  <span class="text-muted" style="font-size: 11px;">Fitur penerbitan suket sementara dinonaktifkan (sedang dalam penyesuaian).</span>
                </label>
              </div>
              <div class="form-check p-2 bg-white rounded border border-primary-subtle shadow-sm">
                <input class="form-check-input ms-0 me-2" type="radio" name="swal_next_step" id="step_penyerahan" value="penyerahan_lhu" checked>
                <label class="form-check-label w-100" for="step_penyerahan" style="cursor: pointer;">
                  <span class="fw-semibold text-primary d-block">2. Langsung ke Penyerahan LHU (Wajib)</span>
                  <span class="text-muted" style="font-size: 11px;">Permohonan langsung diteruskan menuju penyerahan LHU ke pemohon.</span>
                </label>
              </div>
            </div>
          `,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: '<i class="bi bi-send me-1"></i>Teruskan Sekarang',
          cancelButtonText: 'Batal',
          confirmButtonColor: '#15406A',
          focusConfirm: false,
          preConfirm: () => {
            const selected = document.querySelector('input[name="swal_next_step"]:checked')?.value;
            return selected || 'penyerahan_lhu';
          }
        });

        if (!swalResult.isConfirmed) return;
        nextStep = swalResult.value || 'penyerahan_lhu';
      } else {
        const confirmChoice = confirm('Teruskan kuitansi ke pelanggan dan lanjut ke Penyerahan LHU sekarang?');
        if (!confirmChoice) return;
      }

      window.WorkflowLoading?.setButtonLoading(submitBtn, true);
      try {
        const data = await postJson(submitUrl, { next_step: nextStep });
        await notify('success', data.message || 'Kuitansi berhasil diteruskan ke pelanggan.');
        window.location.reload();
      } catch (error) {
        await notify('error', error.message || 'Gagal meneruskan kuitansi ke pelanggan.');
        window.WorkflowLoading?.releaseButton(submitBtn);
      }
    }
  });

  kodeInput?.addEventListener('input', filterCards);
  perusahaanInput?.addEventListener('input', filterCards);
  resetBtn?.addEventListener('click', () => {
    if (kodeInput) kodeInput.value = '';
    if (perusahaanInput) perusahaanInput.value = '';
    filterCards();
  });
  statusButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      activeStatus = (btn.getAttribute('data-filter-status') || 'belum_kirim').toLowerCase();
      statusButtons.forEach((item) => item.classList.remove('active'));
      btn.classList.add('active');
      filterCards();
    });
  });

  updateStatusCounts();
  filterCards();
})();
</script>
@endpush
