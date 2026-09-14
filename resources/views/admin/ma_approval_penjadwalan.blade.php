@extends('layouts.app_admin')

@section('content_admin')
  @php
    $orders = $orders ?? collect();
    $isSuperadminView = auth()->user()?->role === 'superadmin';
    $flashSuccess = session('success');
    $flashError = session('error');
  @endphp

  <style>
    .ma-filter-card {
      border: 1px solid #d8e1ec;
      border-radius: 18px;
      background: linear-gradient(180deg, #f7faff 0%, #eef4fb 100%);
      box-shadow: 0 10px 24px rgba(21, 64, 106, 0.08);
    }
    .ma-search-input-wrap {
      position: relative;
    }
    .ma-search-input-wrap i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #6f88a8;
      font-size: 0.9rem;
      pointer-events: none;
    }
    .ma-search-input {
      min-height: 42px;
      border-radius: 12px;
      border: 1px solid #bfd0e4;
      padding-left: 36px;
      font-size: 0.83rem;
    }
    .ma-search-input:focus {
      border-color: #15406A;
      box-shadow: 0 0 0 0.18rem rgba(21, 64, 106, 0.14);
    }
    .ma-reset-btn {
      min-height: 42px;
      border-radius: 12px;
      font-weight: 700;
    }
    .ma-accordion .accordion-item {
      border: 1px solid #d8e3f0;
      border-radius: 18px !important;
      overflow: hidden;
      box-shadow: 0 10px 28px rgba(21, 64, 106, 0.08);
    }
    .ma-accordion .accordion-button {
      background: #15406A;
      color: #fff;
      padding: 1rem 1.1rem;
      box-shadow: none;
    }
    .ma-accordion .accordion-button:not(.collapsed) {
      background: #15406A;
      color: #fff;
    }
    .ma-accordion .accordion-button::after {
      filter: brightness(0) invert(1);
      margin-left: 0.55rem;
      flex-shrink: 0;
    }
    .ma-order-main {
      flex: 1 1 auto;
      min-width: 0;
    }
    .ma-order-title {
      font-size: 1rem;
      font-weight: 700;
      line-height: 1.25;
    }
    .ma-order-subtitle {
      font-size: 0.82rem;
      opacity: 0.92;
      margin-top: 0.15rem;
    }
    .ma-order-sent {
      margin-left: auto;
      padding-left: 0.8rem;
      padding-right: 0.2rem;
      font-size: 0.75rem;
      white-space: nowrap;
      display: flex;
      align-items: center;
      text-align: right;
    }
    .ma-order-sent-main {
      display: inline-flex;
      align-items: center;
      justify-content: flex-end;
      gap: 0.35rem;
      font-weight: 600;
    }
    .ma-panel {
      border: 1px solid #dce5f1;
      border-radius: 16px;
      background: #fff;
      padding: 0.88rem 0.9rem;
      height: auto;
    }
    .ma-panel-title {
      font-size: 0.82rem;
      font-weight: 700;
      color: #15406A;
      margin-bottom: 0.65rem;
      display: flex;
      align-items: center;
      gap: 0.42rem;
    }
    .ma-info-list {
      display: grid;
      gap: 0.62rem;
    }
    .ma-info-list.ma-info-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      column-gap: 1rem;
      row-gap: 0.72rem;
    }
    .ma-info-row {
      display: grid;
      gap: 0.1rem;
    }
    .ma-info-label {
      font-size: 0.69rem;
      color: #667b95;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .ma-info-value {
      font-size: 0.8rem;
      color: #1d3551;
      font-weight: 600;
      word-break: break-word;
      line-height: 1.4;
    }
    .ma-chip-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
    }
    .ma-chip {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      border-radius: 999px;
      padding: 0.3rem 0.58rem;
      background: #edf4fb;
      color: #15406A;
      font-size: 0.72rem;
      font-weight: 600;
    }
    .ma-params-table th {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      color: #5f7894;
    }
    .ma-params-table td {
      vertical-align: top;
      font-size: 0.77rem;
    }
    .ma-params-table th,
    .ma-params-table td {
      padding-top: 0.52rem;
      padding-bottom: 0.52rem;
    }
    .ma-approve-box {
      border: 1px solid #d8e3f0;
      border-radius: 16px;
      background: linear-gradient(180deg, #f9fbff 0%, #eef4fb 100%);
      padding: 0.88rem;
    }
    .ma-approve-title {
      font-size: 0.8rem;
      line-height: 1.35;
    }
    .ma-approve-note {
      font-size: 0.74rem;
      line-height: 1.5;
    }
    .ma-approve-btn {
      min-height: 38px;
      border-radius: 12px;
      font-weight: 700;
      padding-inline: 1rem;
      font-size: 0.82rem;
    }
    .ma-approve-check.form-check {
      margin-bottom: .65rem;
    }
    .ma-approve-check .form-check-input {
      margin-top: .2rem;
    }
    .ma-approve-check .form-check-label {
      font-size: .74rem;
      color: #395572;
      line-height: 1.35;
    }
    .ma-empty {
      border: 1px dashed #c3d3e6;
      border-radius: 18px;
      padding: 2rem 1rem;
      text-align: center;
      color: #607792;
      background: #f8fbff;
    }
    @media (max-width: 991.98px) {
      .ma-panel {
        padding: 0.8rem 0.82rem;
      }
      .ma-panel-title {
        font-size: 0.78rem;
      }
      .ma-info-label {
        font-size: 0.66rem;
      }
      .ma-info-value {
        font-size: 0.76rem;
      }
      .ma-info-list.ma-info-grid {
        grid-template-columns: 1fr;
        column-gap: 0;
      }
      .ma-chip {
        font-size: 0.69rem;
      }
      .ma-params-table th {
        font-size: 0.64rem;
      }
      .ma-params-table td {
        font-size: 0.74rem;
      }
      .ma-approve-title {
        font-size: 0.77rem;
      }
      .ma-approve-note,
      .ma-approve-check .form-check-label {
        font-size: 0.71rem;
      }
      .ma-approve-btn {
        font-size: 0.78rem;
      }
    }
  </style>

  @include('admin.partials.workflow_header', [
    'title' => $isSuperadminView ? 'Alur Kerja - Monitor Approval MA' : 'Alur Kerja - Approval MA',
    'subtitle' => $isSuperadminView
      ? 'Pantau checkpoint Manajemen Administrasi setelah penjadwalan penyelia dan sebelum admin memproses SPT.'
      : 'Checkpoint Manajemen Administrasi setelah penjadwalan penyelia dan sebelum admin memproses SPT.',
    'total' => $orders->count(),
  ])

  <div class="card border-0 shadow-sm rounded-4 mb-4 ma-filter-card">
    <div class="card-body p-3 p-lg-4">
      <div class="row g-2">
        <div class="col-12 col-md-4">
          <div class="ma-search-input-wrap">
            <i class="bi bi-upc-scan"></i>
            <input type="text" class="form-control ma-search-input" placeholder="Cari kode permohonan" data-search-kode>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="ma-search-input-wrap">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control ma-search-input" placeholder="Cari perusahaan atau lokasi" data-search-keyword>
          </div>
        </div>
        <div class="col-12 col-md-2 d-flex">
          <button type="button" class="btn btn-outline-primary w-100 ma-reset-btn" data-search-reset>
            <i class="bi bi-arrow-clockwise me-1"></i>Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  @if($orders->isEmpty())
    <div class="ma-empty">
      <div class="fw-semibold mb-1">Belum ada penjadwalan yang menunggu ACC MA.</div>
      <div class="small">Data akan muncul di sini setelah penyelia meneruskan penjadwalan.</div>
    </div>
  @else
    <div class="accordion ma-accordion" id="maApprovalAccordion">
      @foreach($orders as $item)
        @php
          $accordionId = 'ma-approval-' . ($item['permohonan_id'] ?? $loop->index);
          $params = collect($item['parameter'] ?? []);
          $pcu = collect($item['pcu'] ?? [])->filter()->values();
        @endphp

        <div
          class="accordion-item mb-3"
          data-order-card
          data-kode="{{ strtolower($item['kode'] ?? '') }}"
          data-keyword="{{ strtolower(($item['pelanggan'] ?? '') . ' ' . ($item['lokasi'] ?? '')) }}"
        >
          <h2 class="accordion-header" id="{{ $accordionId }}-heading">
            <button
              class="accordion-button collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#{{ $accordionId }}-collapse"
              aria-expanded="false"
              aria-controls="{{ $accordionId }}-collapse"
            >
              <div class="d-flex align-items-center w-100 gap-3">
                <div class="ma-order-main">
                  <div class="ma-order-title">{{ $item['pelanggan'] ?? '-' }}</div>
                  <div class="ma-order-subtitle">
                    {{ $item['kode'] ?? '-' }} | {{ $item['tanggal_mulai'] ?? '-' }} s/d {{ $item['tanggal_selesai'] ?? '-' }}
                  </div>
                </div>
                <div class="ma-order-sent">
                  <div class="ma-order-sent-main">
                    <i class="bi bi-clock-history"></i>
                    <span data-relative-time data-sent-at="{{ $item['penjadwalan_sent_at_iso'] ?? '' }}">-</span>
                  </div>
                </div>
              </div>
            </button>
          </h2>
          <div id="{{ $accordionId }}-collapse" class="accordion-collapse collapse" aria-labelledby="{{ $accordionId }}-heading" data-bs-parent="#maApprovalAccordion">
            <div class="accordion-body p-3">
              <div class="row g-3 align-items-start">
                <div class="col-12">
                  <div class="ma-panel">
                    <div class="ma-panel-title">
                      <i class="bi bi-building"></i>
                      <span>Informasi Jadwal</span>
                    </div>
                    <div class="ma-info-list ma-info-grid">
                      <div class="ma-info-row">
                        <div class="ma-info-label">Perusahaan</div>
                        <div class="ma-info-value">{{ $item['pelanggan'] ?? '-' }}</div>
                      </div>
                      <div class="ma-info-row">
                        <div class="ma-info-label">Lokasi</div>
                        <div class="ma-info-value">{{ $item['lokasi'] ?? '-' }}</div>
                      </div>
                      <div class="ma-info-row">
                        <div class="ma-info-label">Alamat</div>
                        <div class="ma-info-value">{{ $item['alamat'] ?? '-' }}</div>
                      </div>
                      <div class="ma-info-row">
                        <div class="ma-info-label">Tanggal</div>
                        <div class="ma-info-value">{{ $item['tanggal_mulai'] ?? '-' }} s/d {{ $item['tanggal_selesai'] ?? '-' }}</div>
                      </div>
                      <div class="ma-info-row">
                        <div class="ma-info-label">Catatan</div>
                        <div class="ma-info-value">{{ $item['catatan'] ?: '-' }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="ma-panel">
                    <div class="ma-panel-title">
                      <i class="bi bi-list-check"></i>
                      <span>Parameter Pengujian</span>
                    </div>
                    <div class="table-responsive">
                      <table class="table table-sm align-middle mb-0 ma-params-table">
                        <thead>
                          <tr>
                            <th>Kategori</th>
                            <th>Parameter</th>
                            <th class="text-center">Qty</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse($params as $param)
                            <tr>
                              <td>{{ $param['kategori'] ?? '-' }}</td>
                              <td>{{ $param['nama'] ?? '-' }}</td>
                              <td class="text-center">{{ $param['qty'] ?? 0 }}</td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="3" class="text-center text-muted py-3">Belum ada parameter.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="ma-panel">
                    <div class="ma-panel-title">
                      <i class="bi bi-people"></i>
                      <span>Tim PCU</span>
                    </div>
                    <div class="ma-info-row mb-2">
                      <div class="ma-info-label">Ketua Tim PCU</div>
                      <div class="ma-info-value">{{ $item['ketua_pcu'] ?? '-' }}</div>
                    </div>
                    <div class="ma-chip-list">
                      @forelse($pcu as $person)
                        <span class="ma-chip"><i class="bi bi-person-badge"></i>{{ $person }}</span>
                      @empty
                        <span class="text-muted small">Belum ada tim PCU.</span>
                      @endforelse
                    </div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="ma-approve-box">
                    <div class="fw-semibold text-dark mb-1 ma-approve-title">{{ $isSuperadminView ? 'Aksi Approval MA' : 'ACC Manajemen Administrasi' }}</div>
                    <div class="text-muted mb-3 ma-approve-note">Setelah di-ACC, permohonan akan masuk antrean admin untuk pembuatan dan pengiriman SPT.</div>
                    <form method="POST" action="{{ $item['approve_url'] }}" data-ma-approve-form>
                      @csrf
                      <div class="form-check ma-approve-check">
                        <input class="form-check-input" type="checkbox" value="1" id="maApproveCheck{{ $item['permohonan_id'] ?? $loop->index }}" name="confirm_approval" required data-ma-approve-check>
                        <label class="form-check-label" for="maApproveCheck{{ $item['permohonan_id'] ?? $loop->index }}">
                          Saya menyetujui data penjadwalan ini untuk diteruskan ke admin.
                        </label>
                      </div>
                      <button type="submit" class="btn btn-primary w-100 ma-approve-btn" data-loading-text="Meneruskan ke admin...">
                        <i class="bi bi-check2-circle me-1"></i>{{ $isSuperadminView ? 'Setujui Sebagai MA' : 'ACC dan Teruskan ke Admin' }}
                      </button>
                    </form>
                    <form
                      method="POST"
                      action="{{ route((auth()->user()?->role === 'superadmin' ? 'superadmin' : 'ma') . '.approval-ma.return-to-penjadwalan', $item['permohonan_id']) }}"
                      class="mt-2"
                      data-return-penjadwalan-form
                    >
                      @csrf
                      <input type="hidden" name="reason" value="" data-return-reason-input>
                      <button type="submit" class="btn btn-outline-danger w-100 ma-approve-btn">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan ke Penjadwalan
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  <script>
    (() => {
      const cards = Array.from(document.querySelectorAll('[data-order-card]'));
      const kodeInput = document.querySelector('[data-search-kode]');
      const keywordInput = document.querySelector('[data-search-keyword]');
      const resetButton = document.querySelector('[data-search-reset]');

      if (!cards.length) {
        return;
      }

      const normalize = (value) => (value || '').toString().toLowerCase().trim();

      const applyFilter = () => {
        const kode = normalize(kodeInput?.value);
        const keyword = normalize(keywordInput?.value);

        cards.forEach((card) => {
          const matchKode = !kode || normalize(card.dataset.kode).includes(kode);
          const matchKeyword = !keyword || normalize(card.dataset.keyword).includes(keyword);
          card.style.display = matchKode && matchKeyword ? '' : 'none';
        });
      };

      kodeInput?.addEventListener('input', applyFilter);
      keywordInput?.addEventListener('input', applyFilter);
      resetButton?.addEventListener('click', () => {
        if (kodeInput) kodeInput.value = '';
        if (keywordInput) keywordInput.value = '';
        applyFilter();
      });

      const formatRelativeTime = (iso) => {
        if (!iso) return '-';
        const date = new Date(iso);
        if (Number.isNaN(date.getTime())) return '-';
        const diffMs = Date.now() - date.getTime();
        if (diffMs < 0) return 'baru saja';
        const minute = 60 * 1000;
        const hour = 60 * minute;
        const day = 24 * hour;
        if (diffMs < minute) return 'baru saja';
        if (diffMs < hour) return `${Math.floor(diffMs / minute)} menit yang lalu`;
        if (diffMs < day) return `${Math.floor(diffMs / hour)} jam yang lalu`;
        return `${Math.floor(diffMs / day)} hari yang lalu`;
      };
      const renderRelativeTimes = () => {
        document.querySelectorAll('[data-relative-time]').forEach((el) => {
          const iso = el.getAttribute('data-sent-at') || '';
          el.textContent = formatRelativeTime(iso);
        });
      };
      renderRelativeTimes();
      window.setInterval(renderRelativeTimes, 60000);

      @if($flashSuccess)
        window.Swal?.fire({
          icon: 'success',
          title: 'Berhasil',
          text: @json($flashSuccess),
        });
      @endif

      @if($flashError)
        window.Swal?.fire({
          icon: 'error',
          title: 'Gagal',
          text: @json($flashError),
        });
      @endif

      const approveForms = Array.from(document.querySelectorAll('[data-ma-approve-form]'));
      approveForms.forEach((form) => {
        form.addEventListener('submit', async (event) => {
          const submitter = event.submitter || form.querySelector('button[type="submit"]');
          const check = form.querySelector('[data-ma-approve-check]');
          if (!check || !check.checked) {
            event.preventDefault();
            window.WorkflowLoading?.releaseButton(submitter);
            if (window.Swal) {
              await window.Swal.fire({
                icon: 'warning',
                title: 'Persetujuan wajib',
                text: 'Centang persetujuan terlebih dahulu sebelum mengirim.',
              });
            } else {
              alert('Centang persetujuan terlebih dahulu sebelum mengirim.');
            }
            return;
          }

          event.preventDefault();

          const confirmed = window.Swal
            ? await window.Swal.fire({
                icon: 'question',
                title: 'Teruskan ke admin?',
                text: 'Apakah Anda yakin ingin menyetujui dan mengirim data ini ke admin?',
                showCancelButton: true,
                confirmButtonText: 'Ya, teruskan',
                cancelButtonText: 'Batal',
              }).then((result) => result.isConfirmed)
            : confirm('Apakah Anda yakin ingin menyetujui dan mengirim data ini ke admin?');

          if (!confirmed) {
            window.WorkflowLoading?.releaseButton(submitter);
            return;
          }

          window.WorkflowLoading?.setButtonLoading(submitter, true);
          form.submit();
        });
      });

      document.querySelectorAll('[data-return-penjadwalan-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
          event.preventDefault();

          const submitter = event.submitter || form.querySelector('button[type="submit"]');
          const reasonInput = form.querySelector('[data-return-reason-input]');
          let reason = (reasonInput?.value || '').trim();

          if (window.Swal) {
            const result = await window.Swal.fire({
              icon: 'warning',
              title: 'Kembalikan ke Penjadwalan',
              input: 'textarea',
              inputLabel: 'Alasan pengembalian',
              inputPlaceholder: 'Tulis alasan dikembalikan ke penjadwalan...',
              inputValue: reason,
              showCancelButton: true,
              confirmButtonText: 'Kembalikan',
              cancelButtonText: 'Batal',
              inputValidator: (value) => !(value || '').trim() ? 'Alasan wajib diisi.' : null,
            });

            if (!result.isConfirmed) {
              window.WorkflowLoading?.releaseButton(submitter);
              return;
            }

            reason = (result.value || '').trim();
          } else {
            reason = (window.prompt('Masukkan alasan dikembalikan ke penjadwalan:') || reason).trim();
            if (!reason) {
              window.WorkflowLoading?.releaseButton(submitter);
              return;
            }
          }

          if (reasonInput) {
            reasonInput.value = reason;
          }

          window.WorkflowLoading?.setButtonLoading(submitter, true);
          form.submit();
        });
      });
    })();
  </script>
@endsection
