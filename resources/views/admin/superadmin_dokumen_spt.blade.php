@extends('layouts.app_admin')

@section('content_admin')
  <style>
    #dokumenSptAccordion {
      font-family: 'Poppins', sans-serif;
    }
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
    .accordion-item {
      border-radius: 14px !important;
      border: 1px solid #dbe4f1 !important;
      background: #fff;
    }
    .accordion-body {
      padding: 0.95rem 1rem 0.9rem !important;
    }
    .accordion-body .small {
      font-size: 0.78rem;
      line-height: 1.45;
    }
    .spt-action-btn {
      background-color: #15406A !important;
      border-color: #15406A !important;
      color: #fff !important;
      border-radius: 8px;
      min-height: 34px;
      padding: 0.34rem 0.72rem;
      font-size: 0.84rem;
      font-weight: 600;
    }
    .spt-action-btn:hover,
    .spt-action-btn:focus,
    .spt-action-btn:active {
      background-color: #0f2f53 !important;
      border-color: #0f2f53 !important;
      color: #fff !important;
    }
    .spt-rich-editor {
      min-height: 74px;
      line-height: 1.4;
      overflow: auto;
      border-radius: 6px;
      border-color: #ccd8e8;
      font-size: 0.8rem;
      padding: 0.45rem 0.56rem;
    }
    .spt-rich-toolbar {
      display: flex;
      gap: 4px;
      justify-content: flex-end;
    }
    .spt-arrival-time {
      font-size: 10px;
      line-height: 1.2;
      margin-top: 4px;
    }
    .spt-arrival-time i {
      font-size: 10px;
      margin-right: 4px;
      vertical-align: middle;
    }
    .spt-rich-toolbar .btn {
      border: 1px solid #15406A;
      color: #15406A;
      background: #fff;
      border-radius: 6px;
      font-size: 0.74rem;
      min-height: 28px;
      min-width: 28px;
      padding: 0.2rem 0.46rem;
      line-height: 1;
    }
    .spt-rich-toolbar .btn:hover {
      background: #15406A;
      color: #fff;
    }
    #dokumenSptAccordion .accordion-button {
      background-color: #15406a;
      color: #fff;
      padding: 0.8rem 1rem;
    }
    #dokumenSptAccordion .accordion-button:not(.collapsed) {
      background-color: #15406a;
      color: #fff;
      box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
    }
    #dokumenSptAccordion .accordion-button::after {
      filter: brightness(0) invert(1);
    }
    #dokumenSptAccordion .accordion-button .text-muted,
    #dokumenSptAccordion .accordion-button .spt-arrival-time,
    #dokumenSptAccordion .accordion-button .spt-arrival-time span,
    #dokumenSptAccordion .accordion-button .spt-arrival-time i {
      color: #fff !important;
    }
    #dokumenSptAccordion .accordion-button > .d-flex > div > div:first-child {
      font-size: 1.02rem;
      font-weight: 700;
      line-height: 1.2;
    }
    #dokumenSptAccordion .accordion-button .small {
      font-size: 0.84rem !important;
      font-weight: 400 !important;
      opacity: 0.97;
      margin-top: 0.12rem;
    }
    .accordion-body h5 {
      font-size: 1.65rem;
      font-weight: 700;
      line-height: 1.15;
      margin-bottom: 0.2rem !important;
      color: #152f4c;
    }
    .accordion-body .fw-semibold[style*="font-size: 1.05rem"] {
      font-size: 1.45rem !important;
      line-height: 1.15;
      margin-bottom: 0.12rem;
      color: #152f4c;
    }
    .badge.text-uppercase {
      letter-spacing: 0;
      font-size: 0.64rem;
      border-radius: 999px;
      padding: 0.32rem 0.55rem;
    }
    #dokumenSptAccordion .accordion-button .spt-arrival-time .badge {
      font-size: 10px !important;
      line-height: 1.2 !important;
    }
    .spt-status-badge-wrap {
      margin-top: 4px;
    }
    .input-group .input-group-text,
    .input-group .form-control {
      min-height: 40px;
      font-size: 0.8rem;
      border-color: #d5dfec;
    }
    .input-group .input-group-text {
      background: #f8fbff;
      font-weight: 600;
      color: #4a617d;
    }
    .form-label.fw-semibold,
    .spt-form-label {
      font-size: 0.86rem;
      margin-bottom: 0.35rem !important;
      color: #33485f;
      font-weight: 700;
    }
    .fw-semibold.mb-2 {
      font-size: 1rem;
      color: #0f2f53;
    }
    .border-top.pt-3.mt-3 {
      padding-top: 0.95rem !important;
      margin-top: 0.9rem !important;
      border-color: #dce5f1 !important;
    }
    .btn.btn-primary:not(.spt-action-btn) {
      border-radius: 8px;
      min-height: 34px;
      font-size: 0.84rem;
      font-weight: 600;
      padding: 0.36rem 0.76rem;
    }
    .spt-filter-card {
      border: 1px solid #d8e1ec;
      border-radius: 16px;
      background: #f5f7fb;
      box-shadow: 0 2px 10px rgba(21, 64, 106, 0.08);
      padding: 0.75rem;
      margin-bottom: 0.9rem;
    }
    .spt-search-field {
      position: relative;
    }
    .spt-search-field i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #7a8fa8;
      font-size: 0.9rem;
      pointer-events: none;
    }
    .spt-search-field .form-control {
      border-radius: 12px;
      border: 1px solid #b8c9df;
      min-height: 40px;
      padding-left: 34px;
      background: #fff;
      font-size: 0.82rem;
    }
    .spt-reset-btn {
      border-radius: 12px;
      border: 1px solid #15406A;
      color: #15406A;
      background: #fff;
      min-height: 40px;
      font-weight: 700;
      font-size: 0.82rem;
    }
    .spt-reset-btn:hover,
    .spt-reset-btn:focus {
      background: #15406A;
      color: #fff;
      border-color: #15406A;
    }
    .spt-form-box {
      background: #f8fafd;
      border: 1px solid #dbe4f1;
      border-radius: 14px;
      padding: 0.7rem;
      margin-bottom: 0.75rem;
    }
    .spt-form-box .form-control {
      border-radius: 12px;
      border: 1px solid #d5dfec;
      min-height: 40px;
    }
    .spt-form-box .form-control:focus {
      box-shadow: none;
      border-color: #9db4d2;
    }
    .spt-static-input {
      display: flex;
      align-items: center;
      min-height: 40px;
      border: 1px solid #d5dfec;
      border-radius: 12px;
      background: #fff;
      padding: 0.36rem 0.66rem;
      font-size: 0.8rem;
      color: #2f455f;
      font-weight: 500;
    }
    .spt-editor-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 8px;
      min-height: 32px;
      margin-bottom: 6px;
    }
    .spt-editor-title {
      font-size: 0.8rem;
      font-weight: 700;
      color: #152f4c;
      text-transform: uppercase;
      margin: 0;
      letter-spacing: 0.02em;
    }
    .spt-upload-group .form-control,
    .spt-upload-group .btn,
    .spt-upload-link-btn {
      min-height: 40px;
      height: 40px;
    }
    .spt-upload-group .btn,
    .spt-upload-link-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
  </style>
  @include('admin.partials.workflow_header', [
    'title' => 'Alur Kerja - Dokumen SPT',
    'subtitle' => 'Daftar perusahaan yang sudah dijadwalkan untuk pembuatan Surat Perintah Tugas.',
    'total' => $permohonans->count(),
  ])

  @php
    $flashSuccess = session('success');
    $flashError = session('error');
    $uploadSuccess = $flashSuccess && str_contains($flashSuccess, 'berhasil diunggah');
  @endphp

  <div class="spt-filter-card">
    <div class="row g-2">
      <div class="col-12 col-md-4">
        <div class="spt-search-field">
          <i class="bi bi-search"></i>
          <input type="text" class="form-control form-control-sm" placeholder="Cari berdasarkan kode pesanan" data-search-kode>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="spt-search-field">
          <i class="bi bi-search"></i>
          <input type="text" class="form-control form-control-sm" placeholder="Cari berdasarkan pelanggan" data-search-pelanggan>
        </div>
      </div>
      <div class="col-12 col-md-2">
        <button type="button" class="btn w-100 spt-reset-btn" data-search-reset>
          <i class="bi bi-arrow-clockwise"></i> Reset
        </button>
      </div>
    </div>
  </div>

  <div class="accordion" id="dokumenSptAccordion">
    @forelse($permohonans as $permohonan)
      @php
        $role = auth()->user()?->role;
        $routePrefix = $role === 'admin' ? 'admin' : 'superadmin';
        $headingId = 'dokumenSptHeading' . $permohonan->id;
        $collapseId = 'dokumenSptCollapse' . $permohonan->id;
        $company = $permohonan->company;
        $spt = $permohonan->spt;
        $dasarItems = $spt?->dasarItems ?? collect();
        $untukItems = $spt?->untukItems ?? collect();
        $defaultDasarHtml = '<ol><li>Permenaker Nomor 1 Tahun 2022 Tentang Organisasi dan Tata Kerja Kementerian Ketenagakerjaan;</li><li>DIPA Balai Hiperkes dan KK Surabaya Tahun Anggaran 2026 Nomor 026.08.1.350080/2025 tanggal 1 Desember 2025.</li></ol>';
        $dasarHtml = $dasarItems->count() > 1
          ? '<ol><li>' . $dasarItems->pluck('uraian')->implode('</li><li>') . '</li></ol>'
          : ($dasarItems->first()->uraian ?? $defaultDasarHtml);
        $untukHtml = $untukItems->count() > 1
          ? '<ol><li>' . $untukItems->pluck('uraian')->implode('</li><li>') . '</li></ol>'
          : ($untukItems->first()->uraian ?? '');
        $nomorSurat = $spt?->nomor_surat ?? '';
        $signedPath = $spt?->signed_file_path ?? null;
        $signedFileName = $signedPath ? basename((string) $signedPath) : null;
        $nomorParts = $nomorSurat ? explode('/', $nomorSurat) : [];
        $nomorMid = $nomorParts[1] ?? '';
        $bulanRoman = null;
        $tahunNomor = null;
        $romanMap = [
          1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
          7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $bulanRoman = $romanMap[(int) now()->format('n')] ?? 'I';
        $tahunNomor = now()->format('Y');
        $pcuNames = $permohonan->assignments
            ->where('role', 'pcu')
            ->map(fn ($a) => $a->user?->name)
            ->filter()
            ->values()
            ->all();
        $statusLabel = $spt ? ($signedPath ? 'sudah ttd digital spt' : 'belum ttd digital spt') : 'belum membuat spt';
        $statusClass = $spt ? ($signedPath ? 'bg-success' : 'bg-warning text-dark') : 'bg-secondary';
        $masukAt = $permohonan->ma_approved_at ?? $permohonan->penjadwalan_sent_at;
        $masukTimestamp = optional($masukAt)->timestamp;
        $masukLabel = '-';
        if ($masukTimestamp) {
          $seconds = max(0, now()->timestamp - $masukTimestamp);
          if ($seconds < 60) {
            $masukLabel = 'baru saja';
          } elseif ($seconds < 3600) {
            $masukLabel = floor($seconds / 60) . ' mnt yang lalu';
          } elseif ($seconds < 86400) {
            $masukLabel = floor($seconds / 3600) . ' jam yang lalu';
          } else {
            $masukLabel = floor($seconds / 86400) . ' hari yang lalu';
          }
        }
      @endphp
      <div
        class="accordion-item border-0 shadow-sm rounded-4 mb-3 overflow-hidden"
        data-spt-card
        data-kode="{{ strtolower($permohonan->kode ?? '') }}"
        data-pelanggan="{{ strtolower($company?->company_name ?? '') }}"
      >
        <h2 class="accordion-header" id="{{ $headingId }}">
          <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
            <div class="d-flex flex-wrap w-100 justify-content-between align-items-start pe-3">
              <div>
                <div>{{ $permohonan->kode ?? '-' }}</div>
                <div class="small text-muted fw-normal">{{ $company?->company_name ?? '-' }}</div>
              </div>
              <div class="text-muted text-end spt-arrival-time">
                <i class="bi bi-clock"></i>
                <span data-relative-time data-time-unix="{{ $masukTimestamp ?: '' }}">{{ $masukLabel }}</span>
                <div class="spt-status-badge-wrap">
                  <span class="badge {{ $statusClass }} text-uppercase">{{ $statusLabel }}</span>
                </div>
              </div>
            </div>
          </button>
        </h2>
        <div id="{{ $collapseId }}" class="accordion-collapse collapse" aria-labelledby="{{ $headingId }}" data-bs-parent="#dokumenSptAccordion">
          <div class="accordion-body bg-white">
            <form
              method="POST"
              action="{{ route($routePrefix . '.dokumen-spt.store', $permohonan) }}"
              data-spt-form
              data-open-collapse-id="{{ $collapseId }}"
            >
              @csrf

              <div class="row g-3 mb-2">
                <div class="col-12 col-lg-6">
                  <div class="spt-form-box">
                    <label class="spt-form-label d-block">Nomor Surat</label>
                    <div class="input-group">
                      <span class="input-group-text">5.12/</span>
                      <input
                        type="text"
                        class="form-control"
                        placeholder="masukkan nomer surat"
                        maxlength="3"
                        inputmode="numeric"
                        pattern="[0-9]{3}"
                        value="{{ $nomorMid }}"
                        autocomplete="off"
                        data-spt-nomor-mid
                        required
                      >
                      <span class="input-group-text">/AS.03.01/</span>
                      <input
                        type="text"
                        class="form-control text-center"
                        value="{{ $bulanRoman }}"
                        readonly
                        style="max-width: 76px;"
                        data-spt-nomor-month
                      >
                      <span class="input-group-text">/</span>
                      <input
                        type="text"
                        class="form-control text-center"
                        value="{{ $tahunNomor }}"
                        readonly
                        style="max-width: 92px;"
                        data-spt-nomor-year
                      >
                    </div>
                    <div class="small text-muted mt-1">Format: 5.12/050/AS.03.01/Bulan/Tahun (kolom tengah 3 digit)</div>
                  </div>
                </div>
                <div class="col-12 col-lg-6">
                  <div class="spt-form-box">
                    <label class="spt-form-label d-block">Petugas</label>
                    <div class="spt-static-input">
                      <i class="bi bi-people me-2 text-muted"></i>
                      <span>{{ count($pcuNames) > 0 ? implode(', ', $pcuNames) : '-' }}</span>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="nomor_surat" value="" data-spt-nomor-full>
              </div>

              <div class="row g-3">
                <div class="col-12 col-lg-6">
                  <div data-rich-editor-wrap>
                    <div class="spt-editor-head">
                      <div class="spt-editor-title">Dasar</div>
                      <div class="spt-rich-toolbar">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-editor-action="bold"><strong>B</strong></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-editor-action="italic"><em>I</em></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-editor-action="underline"><u>U</u></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-editor-action="insertOrderedList">1. 2. 3.</button>
                      </div>
                    </div>
                    <div
                      class="form-control form-control-sm spt-rich-editor"
                      contenteditable="true"
                      data-rich-editor
                      data-placeholder="Uraian dasar"
                    ></div>
                    <input type="hidden" name="dasar_uraian" value="{{ $dasarHtml }}" data-rich-input>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div data-rich-editor-wrap>
                    <div class="spt-editor-head">
                      <div class="spt-editor-title">Untuk</div>
                      <div class="spt-rich-toolbar">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-editor-action="bold"><strong>B</strong></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-editor-action="italic"><em>I</em></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-editor-action="underline"><u>U</u></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-editor-action="insertOrderedList">1. 2. 3.</button>
                      </div>
                    </div>
                    <div
                      class="form-control form-control-sm spt-rich-editor"
                      contenteditable="true"
                      data-rich-editor
                      data-placeholder="Uraian untuk"
                    ></div>
                    <input type="hidden" name="untuk_uraian" value="{{ $untukHtml }}" data-rich-input>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end align-items-center gap-2 mt-4">
                <button
                  type="submit"
                  class="btn spt-action-btn btn-sm d-inline-flex align-items-center gap-1"
                  title="Simpan data dokumen SPT"
                >
                  <i class="bi bi-save"></i>
                  <span>Simpan SPT</span>
                </button>
                @if($spt)
                  <button
                    type="button"
                    class="btn spt-action-btn btn-sm d-inline-flex align-items-center gap-1"
                    title="Unduh dokumen SPT"
                    data-unduh-spt
                    data-spt-preview-url="{{ route($routePrefix . '.dokumen-spt.preview', $permohonan) }}"
                  >
                    <i class="bi bi-download"></i>
                    <span>Unduh SPT</span>
                  </button>
                  <a
                    href="{{ route($routePrefix . '.dokumen-spt.preview', $permohonan) }}?download=pdf&inline=1&v={{ now()->timestamp }}"
                    class="btn spt-action-btn btn-sm d-inline-flex align-items-center gap-1"
                    target="_blank"
                    rel="noopener"
                    title="Lihat pratinjau dokumen SPT"
                  >
                    <i class="bi bi-eye"></i>
                    <span>Lihat SPT</span>
                  </a>
                @endif
              </div>
            </form>

            <div class="border-top pt-3 mt-3">
              <div class="fw-semibold mb-2">Upload SPT TTD Digital</div>
              @if($spt)
                <form
                  method="POST"
                  action="{{ route($routePrefix . '.dokumen-spt.signed.store', $permohonan) }}"
                  enctype="multipart/form-data"
                  class="row g-2 align-items-end"
                  data-auto-upload-form
                  data-open-collapse-id="{{ $collapseId }}"
                >
                  @csrf
                  <div class="col-12 col-md-8">
                    <label class="form-label small text-muted mb-1">Dokumen SPT TTD (PDF/DOC/DOCX)</label>
                    <div class="input-group spt-upload-group">
                      <input
                        type="text"
                        class="form-control form-control-sm"
                        value="{{ $signedFileName ?? 'Belum ada file dipilih' }}"
                        readonly
                        data-upload-file-name
                      >
                      <button type="button" class="btn btn-outline-secondary btn-sm" data-upload-trigger>
                        Pilih File
                      </button>
                    </div>
                    <input
                      type="file"
                      name="signed_document"
                      class="d-none"
                      accept=".pdf,.doc,.docx"
                      required
                      data-auto-upload-input
                    >
                  </div>
                  <div class="col-12 col-md-4 d-flex flex-wrap gap-2">
                    @if($signedPath)
                      <a
                        href="{{ route('dokumen-spt.signed', $permohonan) }}"
                        class="btn btn-outline-primary btn-sm spt-upload-link-btn"
                        target="_blank"
                        rel="noopener"
                      >
                        Lihat SPT TTD
                      </a>
                    @endif
                  </div>
                </form>
                @if($uploadSuccess)
                  <div class="small text-success mt-2">SPT berhasil di upload.</div>
                @endif
                <div class="small text-muted mt-1">
                  @if($signedPath)
                    SPT TTD sudah diunggah.
                  @else
                    Belum ada SPT TTD.
                  @endif
                </div>
              @else
                <div class="small text-muted">Simpan SPT terlebih dahulu untuk mengunggah SPT TTD Digital.</div>
              @endif
            </div>

            <div class="d-flex justify-content-end align-items-center gap-2 mt-2">
              <form
                method="POST"
                action="{{ route($routePrefix . '.dokumen-spt.return-to-penjadwalan', $permohonan) }}"
                data-return-penjadwalan-form
              >
                @csrf
                <input type="hidden" name="reason" value="" data-return-reason-input>
                <button type="submit" class="btn btn-outline-danger">Kembalikan ke Penjadwalan</button>
              </form>
              @if($spt)
                <form
                  method="POST"
                  action="{{ route($routePrefix . '.dokumen-spt.send-to-pcu', $permohonan) }}"
                  data-confirm-send-to-pcu
                  data-workflow-submit-form
                >
                  @csrf
                  <button type="submit" class="btn btn-primary" data-loading-text="Meneruskan ke PCU..." @if(!$signedPath) disabled @endif>Teruskan ke PCU</button>
                </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center text-muted py-4">Belum ada penjadwalan selesai.</div>
    @endforelse
  </div>
@endsection

@push('scripts')
  <script>
    (() => {
      const flashSuccess = @json($flashSuccess);
      const flashError = @json($flashError);
      if (flashSuccess) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: flashSuccess,
        });
      }
      if (flashError) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: flashError,
        });
      }

      const formatRelativeTime = (unixTime) => {
        const ts = Number.parseInt(String(unixTime || ''), 10);
        if (Number.isNaN(ts) || ts <= 0) return '-';
        const seconds = Math.max(0, Math.floor(Date.now() / 1000) - ts);
        if (seconds < 60) return 'baru saja';
        if (seconds < 3600) return `${Math.floor(seconds / 60)} mnt yang lalu`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)} jam yang lalu`;
        return `${Math.floor(seconds / 86400)} hari yang lalu`;
      };

      const refreshRelativeTimes = () => {
        document.querySelectorAll('[data-relative-time]').forEach((node) => {
          const ts = node.getAttribute('data-time-unix');
          node.textContent = formatRelativeTime(ts);
        });
      };

      refreshRelativeTimes();

      const kodeInput = document.querySelector('[data-search-kode]');
      const pelangganInput = document.querySelector('[data-search-pelanggan]');
      const resetBtn = document.querySelector('[data-search-reset]');
      const sptCards = document.querySelectorAll('[data-spt-card]');

      const filterCards = () => {
        const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
        const pelangganVal = (pelangganInput?.value || '').toLowerCase().trim();
        sptCards.forEach((card) => {
          const kode = card.getAttribute('data-kode') || '';
          const pelanggan = card.getAttribute('data-pelanggan') || '';
          const show = (!kodeVal || kode.includes(kodeVal))
            && (!pelangganVal || pelanggan.includes(pelangganVal));
          card.classList.toggle('d-none', !show);
        });
      };

      kodeInput?.addEventListener('input', filterCards);
      pelangganInput?.addEventListener('input', filterCards);
      resetBtn?.addEventListener('click', () => {
        if (kodeInput) kodeInput.value = '';
        if (pelangganInput) pelangganInput.value = '';
        filterCards();
      });

      document.querySelectorAll('[data-confirm-send-to-pcu]').forEach((form) => {
        form.addEventListener('submit', (event) => {
          event.preventDefault();
          const submitter = event.submitter || form.querySelector('button[type="submit"]');
          Swal.fire({
            icon: 'question',
            title: 'Teruskan dokumen SPT?',
            text: 'Dokumen SPT bertanda tangan digital akan diteruskan ke PCU untuk akses pengujian.',
            showCancelButton: true,
            confirmButtonText: 'Ya, teruskan',
            cancelButtonText: 'Batal',
          }).then((result) => {
            if (result.isConfirmed) {
              window.WorkflowLoading?.setButtonLoading(submitter, true);
              form.submit();
            }
          });
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

      const sanitizeEditorHtml = (html) => {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html || '';
        wrapper.querySelectorAll('script, style').forEach((el) => el.remove());

        wrapper.querySelectorAll('*').forEach((el) => {
          const tag = el.tagName.toLowerCase();
          if (!['b', 'strong', 'i', 'em', 'u', 'ol', 'ul', 'li', 'br', 'p'].includes(tag)) {
            const text = document.createTextNode(el.textContent || '');
            el.replaceWith(text);
            return;
          }
          [...el.attributes].forEach((attr) => el.removeAttribute(attr.name));
        });

        const normalizeText = (node) => {
          node.nodeValue = (node.nodeValue || '')
            .replace(/\u00A0/g, ' ')
            .replace(/\s+/g, ' ');
        };

        const walker = document.createTreeWalker(wrapper, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        let currentNode = walker.nextNode();
        while (currentNode) {
          textNodes.push(currentNode);
          currentNode = walker.nextNode();
        }
        textNodes.forEach(normalizeText);

        wrapper.querySelectorAll('p').forEach((p) => {
          if ((p.textContent || '').trim() === '') {
            p.remove();
          }
        });

        wrapper.querySelectorAll('li').forEach((li) => {
          li.innerHTML = (li.innerHTML || '')
            .replace(/\u00A0/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
        });

        wrapper.innerHTML = wrapper.innerHTML
          .replace(/&nbsp;/gi, ' ')
          .replace(/>\s+</g, '><')
          .replace(/<p>\s*<\/p>/gi, '')
          .replace(/\s*<br>\s*/gi, '<br>')
          .trim();

        return wrapper.innerHTML;
      };

      const initRichEditor = (wrap) => {
        const editor = wrap?.querySelector('[data-rich-editor]');
        const hidden = wrap?.querySelector('[data-rich-input]');
        if (!wrap || !editor || !hidden) return;

        if (!editor.dataset.initialized) {
          const sanitized = sanitizeEditorHtml(hidden.value || '');
          editor.innerHTML = sanitized;
          hidden.value = sanitized;
          editor.dataset.initialized = '1';
        }

        const sync = () => {
          hidden.value = sanitizeEditorHtml(editor.innerHTML);
        };

        editor.addEventListener('input', sync);
        editor.addEventListener('blur', sync);

        wrap.querySelectorAll('[data-editor-action]').forEach((btn) => {
          btn.addEventListener('click', () => {
            const action = btn.getAttribute('data-editor-action');
            editor.focus();
            document.execCommand(action, false);
            sync();
          });
        });
      };

      const buildNomorSpt = (form) => {
        const midRaw = form.querySelector('[data-spt-nomor-mid]')?.value?.trim() || '';
        const month = form.querySelector('[data-spt-nomor-month]')?.value?.trim() || '';
        const year = form.querySelector('[data-spt-nomor-year]')?.value?.trim() || '';
        const midDigits = (midRaw.match(/\d+/g) || []).join('').slice(0, 3);
        const mid = midDigits ? midDigits.padStart(3, '0') : '';
        if (!mid) return '';
        return `5.12/${mid}/AS.03.01/${month}/${year}`;
      };

      document.querySelectorAll('[data-spt-form]').forEach((form) => {
        const fullInput = form.querySelector('[data-spt-nomor-full]');
        const midInput = form.querySelector('[data-spt-nomor-mid]');

        const updateNomor = () => {
          if (!fullInput) return;
          if (midInput) {
            const sanitized = ((midInput.value || '').match(/\d+/g) || []).join('').slice(0, 3);
            midInput.value = sanitized;
          }
          fullInput.value = buildNomorSpt(form);
        };
        midInput?.addEventListener('input', updateNomor);
        updateNomor();

        form.querySelectorAll('[data-rich-editor-wrap]').forEach((wrap) => initRichEditor(wrap));
        form.addEventListener('submit', () => {
          updateNomor();
          form.querySelectorAll('[data-rich-editor-wrap]').forEach((wrap) => {
            const editor = wrap.querySelector('[data-rich-editor]');
            const hidden = wrap.querySelector('[data-rich-input]');
            if (editor && hidden) {
              hidden.value = sanitizeEditorHtml(editor.innerHTML);
            }
          });

          const openCollapseId = form.getAttribute('data-open-collapse-id');
          if (openCollapseId) {
            sessionStorage.setItem('dokumenSptOpenCollapseId', openCollapseId);
          }
        });
      });

      document.querySelectorAll('[data-auto-upload-form]').forEach((form) => {
        const fileInput = form.querySelector('[data-auto-upload-input]');
        const triggerBtn = form.querySelector('[data-upload-trigger]');
        const fileNameNode = form.querySelector('[data-upload-file-name]');
        if (!fileInput) return;

        triggerBtn?.addEventListener('click', () => {
          fileInput.click();
        });

        fileInput.addEventListener('change', () => {
          if (!fileInput.files || fileInput.files.length === 0) return;
          if (fileNameNode) {
            fileNameNode.value = fileInput.files[0]?.name || '-';
          }
          const openCollapseId = form.getAttribute('data-open-collapse-id');
          if (openCollapseId) {
            sessionStorage.setItem('dokumenSptOpenCollapseId', openCollapseId);
          }
          form.submit();
        });
      });

      document.querySelectorAll('[data-unduh-spt]').forEach((button) => {
        button.addEventListener('click', () => {
          const baseUrl = button.getAttribute('data-spt-preview-url');
          if (!baseUrl) return;

          Swal.fire({
            icon: 'question',
            title: 'Pilih Format Unduhan',
            text: 'Silakan pilih format file SPT yang ingin diunduh.',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'PDF',
            denyButtonText: 'Word',
            cancelButtonText: 'Batal',
          }).then((result) => {
            if (result.isConfirmed) {
              window.open(`${baseUrl}?download=pdf&_=${Date.now()}`, '_blank', 'noopener');
            } else if (result.isDenied) {
              window.location.href = `${baseUrl}?download=word&_=${Date.now()}`;
            }
          });
        });
      });

      const restoreAccordionState = () => {
        const restoreCollapseId = sessionStorage.getItem('dokumenSptOpenCollapseId');
        if (!restoreCollapseId) return;

        const collapseNode = document.getElementById(restoreCollapseId);
        if (!collapseNode) {
          sessionStorage.removeItem('dokumenSptOpenCollapseId');
          return;
        }

        const trigger = document.querySelector(`[data-bs-target="#${restoreCollapseId}"]`);

        if (window.bootstrap?.Collapse) {
          const collapse = window.bootstrap.Collapse.getOrCreateInstance(collapseNode, { toggle: false });
          collapse.show();
        } else {
          collapseNode.classList.add('show');
          if (trigger) {
            trigger.classList.remove('collapsed');
            trigger.setAttribute('aria-expanded', 'true');
          }
        }

        sessionStorage.removeItem('dokumenSptOpenCollapseId');
      };

      if (document.readyState === 'complete') {
        restoreAccordionState();
      } else {
        window.addEventListener('load', restoreAccordionState, { once: true });
      }
    })();
  </script>
@endpush
