@extends('layouts.app_admin')

@section('content_admin')
@php
  $penawaranUnsent = collect($penawaran_list['unsent'] ?? []);
  $penawaranSent = collect($penawaran_list['sent'] ?? []);
@endphp

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Penawaran Pengujian',
  'subtitle' => 'Susun dan kirim penawaran ke pemohon berdasarkan parameter yang disetujui.',
  'total' => $penawaranUnsent->count() + $penawaranSent->count(),
])

<script>
  window.kepalaBalaiProfile = {
    nama: @json($kepalaBalaiNama ?? '-'),
  };
</script>

<div class="card border-0 shadow-sm rounded-4 mb-3 pnw-filter-card">
  <div class="card-body">
    <div class="row g-2">
      <div class="col-12 col-md-4">
        <div class="pnw-search-field">
          <i class="bi bi-search"></i>
          <input type="text" class="form-control form-control-sm" placeholder="Cari berdasarkan kode pesanan" data-search-kode>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="pnw-search-field">
          <i class="bi bi-search"></i>
          <input type="text" class="form-control form-control-sm" placeholder="Cari berdasarkan pelanggan" data-search-pelanggan>
        </div>
      </div>
      <div class="col-12 col-md-2 d-flex align-items-end">
        <button type="button" class="btn btn-outline-secondary w-100 btn-sm pnw-reset-btn" data-search-reset>
          <i class="bi bi-arrow-clockwise"></i> Reset
        </button>
      </div>
    </div>
  </div>
</div>

<div class="d-flex flex-wrap gap-2 mt-2 mb-3 pnw-status-tabs">
  <button type="button" class="btn btn-deep-blue btn-sm active" data-filter-status="unsent">
    Belum Dikirim
    <span class="badge text-bg-danger rounded-pill ms-1" data-count-unsent>{{ $penawaranUnsent->count() }}</span>
  </button>
  <button type="button" class="btn btn-outline-deep-blue btn-sm" data-filter-status="sent">
    Menunggu Persetujuan Pemohon
    <span class="badge text-bg-danger rounded-pill ms-1" data-count-sent>{{ $penawaranSent->count() }}</span>
  </button>
</div>

@forelse($penawaranUnsent as $idx => $penawaran)
  @php
    $params = collect($penawaran['parameter'] ?? []);
    $testable = $params->where('bisa_uji', true);
    $nonTestable = $params->where('bisa_uji', false);
    $subtotal = $testable->sum(fn ($p) => ($p['harga'] ?? 0) * ($p['qty'] ?? 1));
    $cardId = $penawaran['kode'] ?? ('pnw-'.$idx);
    $permohonanId = $penawaran['permohonan_id'] ?? $cardId;
    $waktuMasuk = '-';
    $waktuMasukFull = '-';
    $waktuMasukIso = $penawaran['entered_at'] ?? $penawaran['request_date'] ?? null;
    if (!empty($waktuMasukIso)) {
      try {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $waktuMasukAt = \Carbon\Carbon::parse($waktuMasukIso)->timezone($tz)->locale('id');
        $waktuMasuk = $waktuMasukAt->diffForHumans(now($tz));
        $waktuMasukFull = $waktuMasukAt->translatedFormat('d M Y H:i');
      } catch (\Throwable $e) {
        $waktuMasuk = '-';
        $waktuMasukFull = '-';
      }
    }
    $accordionId = 'pnw-unsent-'.$idx.'-'.preg_replace('/[^A-Za-z0-9_-]/', '', (string) $permohonanId);
  @endphp
  <div
    class="card border-0 shadow-sm rounded-4 mb-3 pnw-card"
    data-penawaran-card
    data-penawaran-id="{{ $permohonanId }}"
    data-pdf-url="{{ route('superadmin.penawaran.pdf', $permohonanId) }}"
    data-kode="{{ strtolower($penawaran['kode'] ?? '') }}"
    data-pelanggan="{{ strtolower($penawaran['pelanggan'] ?? '') }}"
    data-request-date="{{ $penawaran['request_date'] ?? '' }}"
    data-perihal="{{ $penawaran['perihal'] ?? '' }}"
    data-signer-name="{{ $penawaran['signer_name'] ?? '' }}"
    data-signer-role="{{ $penawaran['signer_role'] ?? '' }}"
    data-status="unsent"
  >
    <div class="card-body">
      <button
        class="pnw-accordion-btn collapsed"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#{{ $accordionId }}"
        aria-expanded="false"
        aria-controls="{{ $accordionId }}"
      >
        <div class="pnw-accordion-left">
          <span class="pnw-accordion-kode">{{ $penawaran['kode'] ?? '-' }}</span>
          <span class="pnw-accordion-nama">{{ $penawaran['pelanggan'] ?? '-' }}</span>
        </div>
        <div class="pnw-accordion-right">
          <span class="pnw-accordion-time" @if($waktuMasukFull !== '-') title="Masuk tahap penawaran: {{ $waktuMasukFull }}" @endif>
            <i class="bi bi-clock"></i>
            <span data-relative-time data-created-at="{{ $waktuMasukIso ?? '' }}">{{ $waktuMasuk }}</span>
          </span>
          <i class="bi bi-chevron-down pnw-accordion-chevron"></i>
        </div>
      </button>

      <div id="{{ $accordionId }}" class="collapse">
      <div class="row g-3 align-items-start pt-3">
        <div class="col-12 col-lg-8">
          <div class="pnw-panel-wrap mb-3">
            <div class="pnw-panel-title">
              <i class="bi bi-clipboard2-check-fill"></i>
              <span>Informasi Penawaran</span>
            </div>
            <div class="pnw-panel border rounded-4 p-3 bg-white">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label pnw-field-label">Nomor Surat Penawaran</label>
                  <div class="input-group input-group-sm">
                    <span class="input-group-text">5.12/</span>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="masukkan nomor surat"
                      maxlength="10"
                      autocomplete="off"
                      data-nomor-surat-mid
                      data-penawaran-id="{{ $permohonanId }}"
                    >
                    <span class="input-group-text">/AS.03.01/</span>
                    <input
                      type="text"
                      class="form-control text-center"
                      value="{{ ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][now()->month - 1] }}"
                      readonly
                      style="max-width: 60px;"
                      data-nomor-surat-month
                      data-penawaran-id="{{ $permohonanId }}"
                    >
                    <span class="input-group-text">/</span>
                    <input
                      type="text"
                      class="form-control text-center"
                      value="{{ now()->format('Y') }}"
                      readonly
                      style="max-width: 80px;"
                      data-nomor-surat-year
                      data-penawaran-id="{{ $permohonanId }}"
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="pnw-panel-wrap">
            <div class="pnw-panel-title d-flex justify-content-between align-items-center">
              <span class="d-flex align-items-center gap-2">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span>Parameter & Detail Layanan</span>
              </span>
            </div>
            <div class="pnw-panel border rounded-4 bg-white overflow-hidden">
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 pnw-table">
                  <thead class="pnw-table-head">
                    <tr>
                      <th style="width:12%;">Kategori</th>
                      <th style="width:28%;">Parameter</th>
                      <th style="width:16%;">Status</th>
                      <th style="width:12%;" class="text-center">Kuantitas</th>
                      <th style="width:16%;">Harga Satuan</th>
                      <th style="width:16%;" class="text-end">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($params as $param)
                      @php
                        $qty = $param['qty'] ?? 1;
                        $harga = $param['harga'] ?? 0;
                        $isTestable = (bool) ($param['bisa_uji'] ?? false);
                        $sub = $isTestable ? ($qty * $harga) : 0;
                      @endphp
                      <tr
                        @if($isTestable)
                          data-testable-row
                        @else
                          data-non-testable-row
                          class="table-warning"
                        @endif
                        data-penawaran-id="{{ $permohonanId }}"
                        data-param-id="{{ $param['id'] ?? '' }}"
                        data-param-kategori="{{ $param['kategori'] ?? '-' }}"
                        data-qty-value="{{ $qty }}"
                        data-harga-value="{{ $harga }}"
                      >
                        <td class="text-muted">
                          <span class="pnw-kategori">{{ $param['kategori'] ?? '-' }}</span>
                        </td>
                        <td>
                          <div class="fw-semibold" data-param-nama>{{ $param['nama'] ?? '-' }}</div>
                          @if(!$isTestable && !empty($param['alasan']))
                            <div class="small text-muted mt-1" data-param-alasan>Alasan: {{ $param['alasan'] }}</div>
                          @endif
                        </td>
                        <td>
                          @if($isTestable)
                            <span class="badge pnw-status-badge pnw-status-ok">Bisa Diuji</span>
                          @else
                            <span class="badge pnw-status-badge pnw-status-no">Tidak Bisa Diuji</span>
                          @endif
                        </td>
                        <td class="text-center">
                          @if($isTestable)
                            <input type="number" min="1" class="form-control form-control-sm text-center" value="{{ $qty }}" data-qty data-penawaran-id="{{ $permohonanId }}">
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td>
                          @if($isTestable)
                            <span
                              class="fw-semibold"
                              data-harga-value="{{ $harga }}"
                              data-penawaran-id="{{ $permohonanId }}"
                            >
                              {{ number_format($harga, 0, ',', '.') }}
                            </span>
                          @else
                            <span class="text-muted">-</span>
                          @endif
                        </td>
                        <td class="text-end fw-semibold">
                          <span data-subtotal data-penawaran-id="{{ $permohonanId }}">{{ number_format($sub, 0, ',', '.') }}</span>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="6" class="text-muted text-center small py-3">Belum ada parameter.</td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-4">
          <div class="border rounded-4 p-3 bg-light pnw-summary">
            <div class="fw-bold pnw-summary-title mb-3">Ringkasan Biaya</div>
            <div class="d-flex justify-content-between small mb-2">
              <span>Subtotal</span>
              <span class="fw-semibold">Rp <span data-subtotal-summary data-penawaran-id="{{ $permohonanId }}">{{ number_format($subtotal, 0, ',', '.') }}</span></span>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-end mb-3">
              <span class="fw-semibold">Total Akhir</span>
              <span class="pnw-total-akhir">Rp <span data-total data-penawaran-id="{{ $permohonanId }}">{{ number_format($subtotal, 0, ',', '.') }}</span></span>
            </div>
            <label class="form-label pnw-field-label">Catatan Khusus</label>
            <textarea
              class="form-control form-control-sm mb-3"
              rows="3"
              placeholder="Tambahkan catatan untuk klien..."
              data-catatan-khusus
              data-penawaran-id="{{ $permohonanId }}"
              data-draft-url="{{ route('superadmin.penawaran.draft', $permohonanId) }}"
            >{{ $penawaran['catatan'] ?? '' }}</textarea>
            <div class="row g-2 mb-2">
              <div class="col-6">
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm w-100"
                  data-print-rekap
                  data-penawaran-id="{{ $permohonanId }}"
                  data-draft-url="{{ route('superadmin.penawaran.draft', $permohonanId) }}"
                  data-pdf-url="{{ route('superadmin.penawaran.pdf', $permohonanId) }}"
                >
                  <i class="bi bi-download"></i> Unduh
                </button>
              </div>
              <div class="col-6">
                <label class="btn btn-outline-secondary btn-sm w-100 mb-0" for="upload-{{ $permohonanId }}">
                  <i class="bi bi-upload"></i> Unggah
                </label>
                <input type="file" class="d-none" id="upload-{{ $permohonanId }}" accept=".pdf" data-upload-penawaran data-penawaran-id="{{ $permohonanId }}">
              </div>
            </div>
            <a href="#" class="btn btn-outline-secondary btn-sm disabled w-100 mb-2" target="_blank" aria-label="Lihat dokumen" title="Lihat dokumen" data-link-penawaran data-penawaran-id="{{ $permohonanId }}">
              <i class="bi bi-eye"></i> Lihat Dokumen
            </a>
            <div class="small mt-1 text-muted" data-upload-info data-penawaran-id="{{ $permohonanId }}">Belum ada dokumen diunggah.</div>
            <div class="mt-3 d-none" data-send-wrap data-penawaran-id="{{ $permohonanId }}">
              <a
                href="#"
                class="btn btn-primary btn-sm disabled w-100"
                data-send-penawaran
                data-penawaran-id="{{ $permohonanId }}"
                data-send-url="{{ route('superadmin.penawaran.send', $permohonanId) }}"
                aria-disabled="true"
              >
                <i class="bi bi-send-fill me-1"></i> Kirim Penawaran
              </a>
            </div>
          </div>

          <div class="pnw-info-extra mt-3">
            <div class="d-flex align-items-start gap-2">
              <i class="bi bi-info-circle-fill"></i>
              <div>
                <div class="fw-semibold">Informasi Tambahan</div>
                <div class="small">Penawaran berlaku 30 hari kalender sejak terbit. Pastikan rincian sudah benar sebelum dikirim ke klien.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
@empty
@endforelse

@forelse($penawaranSent as $idx => $penawaran)
  @php
    $params = collect($penawaran['parameter'] ?? []);
    $testable = $params->where('bisa_uji', true);
    $nonTestable = $params->where('bisa_uji', false);
    $subtotal = $testable->sum(fn ($p) => ($p['harga'] ?? 0) * ($p['qty'] ?? 1));
    $cardId = $penawaran['kode'] ?? ('pnw-'.$idx);
    $permohonanId = $penawaran['permohonan_id'] ?? $cardId;
    $waktuMasuk = '-';
    $waktuMasukFull = '-';
    $waktuMasukIso = $penawaran['entered_at'] ?? $penawaran['request_date'] ?? null;
    if (!empty($waktuMasukIso)) {
      try {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $waktuMasukAt = \Carbon\Carbon::parse($waktuMasukIso)->timezone($tz)->locale('id');
        $waktuMasuk = $waktuMasukAt->diffForHumans(now($tz));
        $waktuMasukFull = $waktuMasukAt->translatedFormat('d M Y H:i');
      } catch (\Throwable $e) {
        $waktuMasuk = '-';
        $waktuMasukFull = '-';
      }
    }
    $accordionId = 'pnw-sent-'.$idx.'-'.preg_replace('/[^A-Za-z0-9_-]/', '', (string) $permohonanId);
  @endphp
  <div
    class="card border-0 shadow-sm rounded-4 mb-3 pnw-card"
    data-penawaran-card
    data-penawaran-id="{{ $permohonanId }}"
    data-pdf-url="{{ route('superadmin.penawaran.pdf', $permohonanId) }}"
    data-kode="{{ strtolower($penawaran['kode'] ?? '') }}"
    data-pelanggan="{{ strtolower($penawaran['pelanggan'] ?? '') }}"
    data-request-date="{{ $penawaran['request_date'] ?? '' }}"
    data-perihal="{{ $penawaran['perihal'] ?? '' }}"
    data-signer-name="{{ $penawaran['signer_name'] ?? '' }}"
    data-signer-role="{{ $penawaran['signer_role'] ?? '' }}"
    data-status="sent"
  >
    <div class="card-body">
      <button
        class="pnw-accordion-btn collapsed"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#{{ $accordionId }}"
        aria-expanded="false"
        aria-controls="{{ $accordionId }}"
      >
        <div class="pnw-accordion-left">
          <span class="pnw-accordion-kode">{{ $penawaran['kode'] ?? '-' }}</span>
          <span class="pnw-accordion-nama">{{ $penawaran['pelanggan'] ?? '-' }}</span>
        </div>
        <div class="pnw-accordion-right">
          <span class="pnw-accordion-time" @if($waktuMasukFull !== '-') title="Masuk tahap penawaran: {{ $waktuMasukFull }}" @endif>
            <i class="bi bi-clock"></i>
            <span data-relative-time data-created-at="{{ $waktuMasukIso ?? '' }}">{{ $waktuMasuk }}</span>
          </span>
          <i class="bi bi-chevron-down pnw-accordion-chevron"></i>
        </div>
      </button>

      <div id="{{ $accordionId }}" class="collapse">
      <div class="row g-3 pt-3">
        <div class="col-12 col-lg-8">
          @php
            $testableRows = $params->where('bisa_uji', true);
            $nonTestableRows = $params->where('bisa_uji', false);
          @endphp
          <div class="border rounded-4 p-3 mb-3 bg-light pnw-panel">
            <div class="d-flex align-items-center gap-2 mb-2 text-success">
              <i class="bi bi-check-circle-fill"></i>
              <span class="fw-semibold">Parameter Bisa Diuji</span>
            </div>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0 pnw-table">
                <thead class="table-success-subtle pnw-table-head">
                  <tr>
                    <th style="width:12%;">Kategori</th>
                    <th style="width:30%;">Parameter</th>
                    <th style="width:14%;">Status</th>
                    <th style="width:12%;" class="text-center">Qty</th>
                    <th style="width:14%;" class="text-end">Harga Satuan</th>
                    <th style="width:18%;" class="text-end">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $testableGrouped = $testableRows->groupBy(fn ($item) => (string) ($item['kategori'] ?? '-'));
                  @endphp
                  @forelse($testableGrouped as $kategori => $groupItems)
                    @foreach($groupItems as $groupIdx => $param)
                      @php
                        $qty = $param['qty'] ?? 1;
                        $harga = $param['harga'] ?? 0;
                        $sub = $qty * $harga;
                      @endphp
                      <tr
                        data-testable-row
                        data-penawaran-id="{{ $permohonanId }}"
                        data-param-id="{{ $param['id'] ?? '' }}"
                        data-param-kategori="{{ $param['kategori'] ?? '-' }}"
                        data-qty-value="{{ $qty }}"
                        data-harga-value="{{ $harga }}"
                      >
                        @if($groupIdx === 0)
                          <td rowspan="{{ $groupItems->count() }}" class="align-top">
                            <span class="badge text-bg-secondary" data-param-kategori-label>{{ $kategori }}</span>
                          </td>
                        @endif
                        <td><div class="fw-semibold" data-param-nama>{{ $param['nama'] ?? '-' }}</div></td>
                        <td><span class="badge text-bg-success">Bisa diuji</span></td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-end">{{ number_format($harga, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">{{ number_format($sub, 0, ',', '.') }}</td>
                      </tr>
                    @endforeach
                  @empty
                    <tr>
                      <td colspan="6" class="text-muted text-center small">Belum ada parameter yang bisa diuji.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>

          @if($nonTestableRows->isNotEmpty())
            <div class="border rounded-4 p-3 bg-white pnw-panel">
              <div class="d-flex align-items-center gap-2 mb-2 text-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span class="fw-semibold">Parameter Tidak Bisa Diuji</span>
              </div>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0 pnw-table">
                  <thead class="table-danger-subtle pnw-table-head">
                    <tr>
                      <th style="width:12%;">Kategori</th>
                      <th style="width:30%;">Parameter</th>
                      <th style="width:16%;">Status</th>
                      <th style="width:12%;" class="text-center">Qty</th>
                      <th style="width:12%;" class="text-end">Harga</th>
                      <th style="width:18%;" class="text-end">Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                    @php
                      $nonTestableGrouped = $nonTestableRows->groupBy(fn ($item) => (string) ($item['kategori'] ?? '-'));
                    @endphp
                    @foreach($nonTestableGrouped as $kategori => $groupItems)
                      @foreach($groupItems as $groupIdx => $param)
                        <tr
                          class="table-warning"
                          data-non-testable-row
                          data-penawaran-id="{{ $permohonanId }}"
                          data-param-id="{{ $param['id'] ?? '' }}"
                          data-param-kategori="{{ $param['kategori'] ?? '-' }}"
                          data-qty-value="{{ $param['qty'] ?? 1 }}"
                          data-harga-value="{{ $param['harga'] ?? 0 }}"
                        >
                          @if($groupIdx === 0)
                            <td rowspan="{{ $groupItems->count() }}" class="align-top">
                              <span class="badge text-bg-secondary" data-param-kategori-label>{{ $kategori }}</span>
                            </td>
                          @endif
                          <td>
                            <div class="fw-semibold" data-param-nama>{{ $param['nama'] ?? '-' }}</div>
                            @if(!empty($param['alasan']))
                              <div class="small text-muted" data-param-alasan>Alasan: {{ $param['alasan'] }}</div>
                            @endif
                          </td>
                          <td><span class="badge text-bg-danger">Tidak bisa diuji</span></td>
                          <td class="text-center text-muted">-</td>
                          <td class="text-end text-muted">-</td>
                          <td class="text-end fw-semibold">{{ number_format(0, 0, ',', '.') }}</td>
                        </tr>
                      @endforeach
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif
        </div>
        <div class="col-12 col-lg-4">
          <div class="border rounded-4 p-3 bg-light h-100 pnw-summary">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <div class="text-muted small">Ringkasan</div>
                <h6 class="fw-semibold mb-0">Kalkulasi Penawaran</h6>
              </div>
            </div>
            <div class="d-flex justify-content-between small mb-1">
              <span>Parameter bisa diuji</span>
              <span>{{ $testable->count() }} item</span>
            </div>
            <div class="d-flex justify-content-between small mb-1">
              <span>Parameter tidak bisa diuji</span>
              <span class="text-danger">{{ $nonTestable->count() }} item</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-semibold">
              <span>Total Biaya</span>
              <span>{{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            @if(filled($penawaran['catatan'] ?? ''))
              <div class="small text-muted mt-2">Catatan: {{ $penawaran['catatan'] }}</div>
            @endif
            <div class="d-grid gap-2 mt-3">
              <a
                href="{{ !empty($penawaran['doc_url']) ? $penawaran['doc_url'].'?download=1' : '#' }}"
                class="btn btn-outline-primary btn-sm {{ empty($penawaran['doc_url']) ? 'disabled' : '' }}"
                aria-label="Unduh dokumen penawaran"
                title="Unduh dokumen penawaran"
              >
                <i class="bi bi-download"></i> Unduh Dokumen
              </a>
            </div>
            <hr class="my-3">
            <div class="small text-muted">Status: menunggu persetujuan pemohon.</div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
@empty
@endforelse
<div class="text-center text-muted d-none" data-search-empty>Penawaran tidak ditemukan.</div>

<div class="modal fade" id="printPenawaranModal" tabindex="-1" aria-hidden="true" data-print-modal>
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form data-print-form>
        <div class="modal-header">
          <h5 class="modal-title">Cetak / Unduh Dokumen Penawaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <div class="small text-muted">Kode Penawaran</div>
            <div class="fw-semibold" data-print-kode>-</div>
            <div class="small text-muted mt-1" data-print-pelanggan>-</div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
              <div class="small text-muted">Nomor Surat</div>
              <div class="fw-semibold" data-print-nomor>-</div>
            </div>
          </div>
          <div class="border rounded-4 p-3 mb-3 bg-light">
            <div class="d-flex align-items-center gap-2 mb-2 text-success">
              <i class="bi bi-check-circle-fill"></i>
              <span class="fw-semibold">Parameter Bisa Diuji</span>
            </div>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-success-subtle">
                  <tr>
                    <th style="width:12%;">Kategori</th>
                    <th>Parameter</th>
                    <th style="width:10%;" class="text-center">Qty</th>
                    <th style="width:16%;" class="text-end">Harga</th>
                    <th style="width:18%;" class="text-end">Subtotal</th>
                  </tr>
                </thead>
                <tbody data-print-testable></tbody>
              </table>
            </div>
          </div>
          <div class="border rounded-4 p-3 bg-white" data-print-nontestable-wrap>
            <div class="d-flex align-items-center gap-2 mb-2 text-danger">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <span class="fw-semibold">Parameter Tidak Bisa Diuji</span>
            </div>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-danger-subtle">
                  <tr>
                    <th style="width:12%;">Kategori</th>
                    <th style="width:36%;">Parameter</th>
                    <th>Alasan</th>
                  </tr>
                </thead>
                <tbody data-print-nontestable></tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" data-print-action="full">
            <i class="bi bi-printer"></i> Dokumen
          </button>
          <button type="button" class="btn btn-danger" data-print-action="non_testable">
            <i class="bi bi-printer"></i> Tidak Bisa Diuji
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (() => {
    const formatRelativeTime = (input) => {
      if (!input) return '-';
      const date = new Date(input);
      if (Number.isNaN(date.getTime())) return '-';

      const diffSeconds = Math.floor((Date.now() - date.getTime()) / 1000);
      if (diffSeconds < 60) return 'baru saja';
      if (diffSeconds < 3600) return `${Math.floor(diffSeconds / 60)} mnt yang lalu`;
      if (diffSeconds < 86400) return `${Math.floor(diffSeconds / 3600)} jam yang lalu`;
      return `${Math.floor(diffSeconds / 86400)} hari yang lalu`;
    };

    const updateRelativeTimes = () => {
      document.querySelectorAll('[data-relative-time]').forEach((node) => {
        node.textContent = formatRelativeTime(node.getAttribute('data-created-at') || '');
      });
    };

    updateRelativeTimes();
    window.setInterval(updateRelativeTimes, 60000);

    const formatCurrency = (val) => {
      return new Intl.NumberFormat('id-ID').format(val);
    };
    const escapeHtml = (value) => String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
    const setButtonLoading = (button, isLoading, loadingText = 'Memproses...') => {
      if (!button) return;

      if (!button.dataset.originalHtml) {
        button.dataset.originalHtml = button.innerHTML;
      }

      if (isLoading) {
        button.disabled = true;
        button.classList.add('disabled');
        button.setAttribute('aria-disabled', 'true');
        button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${escapeHtml(loadingText)}`;
        return;
      }

      button.disabled = false;
      button.classList.remove('disabled');
      button.removeAttribute('aria-disabled');
      button.innerHTML = button.dataset.originalHtml;
    };

    const buildNomorSurat = (cardId) => {
      const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
      if (!card) return '';
      const midInput = card.querySelector(`[data-nomor-surat-mid][data-penawaran-id="${cardId}"]`);
      const monthInput = card.querySelector(`[data-nomor-surat-month][data-penawaran-id="${cardId}"]`);
      const yearInput = card.querySelector(`[data-nomor-surat-year][data-penawaran-id="${cardId}"]`);
      const mid = (midInput?.value || '').trim();
      const month = (monthInput?.value || '').trim();
      const year = (yearInput?.value || '').trim();
      if (!mid) return '';
      return `5.12/${mid}/AS.03.01/${month}/${year}`;
    };

    const getCatatanKhusus = (cardId) => {
      const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
      if (!card) return '';
      const input = card.querySelector(`[data-catatan-khusus][data-penawaran-id="${cardId}"]`);
      return (input?.value || '').trim();
    };

    const recalcCard = (cardId) => {
      const rows = document.querySelectorAll(`[data-subtotal][data-penawaran-id="${cardId}"]`);
      let total = 0;
      rows.forEach((row) => {
        const tr = row.closest('tr');
        const qtyInput = tr.querySelector(`[data-qty][data-penawaran-id="${cardId}"]`);
        const hargaNode = tr.querySelector(`[data-harga-value][data-penawaran-id="${cardId}"]`) ||
          tr.querySelector(`[data-harga][data-penawaran-id="${cardId}"]`);
        if (!qtyInput || !hargaNode) return;
        const qty = parseInt(qtyInput.value || '0', 10) || 0;
        const harga = parseInt(hargaNode.getAttribute('data-harga-value') || hargaNode.value || '0', 10) || 0;
        const sub = qty * harga;
        row.textContent = formatCurrency(sub);
        total += sub;
      });
      const totalEl = document.querySelector(`[data-total][data-penawaran-id="${cardId}"]`);
      if (totalEl) totalEl.textContent = formatCurrency(total);
      const subtotalEl = document.querySelector(`[data-subtotal-summary][data-penawaran-id="${cardId}"]`);
      if (subtotalEl) subtotalEl.textContent = formatCurrency(total);
    };

    document.querySelectorAll('[data-qty], [data-harga]').forEach((input) => {
      input.addEventListener('input', () => {
        const cardId = input.getAttribute('data-penawaran-id');
        recalcCard(cardId);
      });
      input.addEventListener('change', () => {
        const cardId = input.getAttribute('data-penawaran-id');
        recalcCard(cardId);
      });
    });

    const kodeInput = document.querySelector('[data-search-kode]');
    const pelangganInput = document.querySelector('[data-search-pelanggan]');
    const resetBtn = document.querySelector('[data-search-reset]');
    const statusButtons = document.querySelectorAll('[data-filter-status]');
    const cards = document.querySelectorAll('[data-penawaran-card]');
    const countUnsent = document.querySelector('[data-count-unsent]');
    const countSent = document.querySelector('[data-count-sent]');
    const emptyState = document.querySelector('[data-search-empty]');

    document.querySelectorAll('[data-nomor-surat-mid]').forEach((input) => {
      input.value = '';
    });

    const saveDraftPenawaran = async (cardId, draftUrlOverride = '', silent = true) => {
      const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
      if (!card) return false;

      const draftUrl = draftUrlOverride
        || card.querySelector(`[data-print-rekap][data-penawaran-id="${cardId}"]`)?.getAttribute('data-draft-url')
        || card.querySelector(`[data-catatan-khusus][data-penawaran-id="${cardId}"]`)?.getAttribute('data-draft-url')
        || '';
      if (!draftUrl) {
        if (!silent) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Draft tidak ditemukan', text: 'URL draft penawaran tidak tersedia.' });
          } else {
            alert('URL draft penawaran tidak tersedia.');
          }
        }
        return false;
      }

      const parameters = collectParameters(cardId);
      if (parameters.length === 0) {
        if (!silent) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Parameter kosong', text: 'Parameter penawaran tidak ditemukan.' });
          } else {
            alert('Parameter penawaran tidak ditemukan.');
          }
        }
        return false;
      }

      const nomorSurat = buildNomorSurat(cardId);
      const catatanKhusus = getCatatanKhusus(cardId);

      try {
        const response = await fetch(draftUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            parameters,
            nomor_surat: nomorSurat || null,
            catatan: catatanKhusus || null,
          }),
        });
        const result = await response.json().catch(() => ({}));
        if (!response.ok) {
          throw new Error(result.message || 'Gagal menyimpan draft penawaran.');
        }
        return true;
      } catch (err) {
        if (!silent) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Gagal menyimpan', text: err.message || 'Terjadi kesalahan saat menyimpan draft.' });
          } else {
            alert(err.message || 'Terjadi kesalahan saat menyimpan draft.');
          }
        } else {
          console.warn('Autosave catatan gagal:', err?.message || err);
        }
        return false;
      }
    };

    const catatanAutosaveTimers = new Map();
    document.querySelectorAll('[data-catatan-khusus]').forEach((input) => {
      input.addEventListener('input', () => {
        const cardId = input.getAttribute('data-penawaran-id');
        const draftUrl = input.getAttribute('data-draft-url') || '';
        if (!cardId || !draftUrl) return;
        if (catatanAutosaveTimers.has(cardId)) {
          clearTimeout(catatanAutosaveTimers.get(cardId));
        }
        const timer = setTimeout(() => {
          saveDraftPenawaran(cardId, draftUrl, true);
        }, 500);
        catatanAutosaveTimers.set(cardId, timer);
      });
    });

    const getActiveStatus = () => {
      const activeBtn = document.querySelector('[data-filter-status].active');
      return (activeBtn?.getAttribute('data-filter-status') || '').toLowerCase();
    };

    const filterCards = () => {
      const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
      const pelangganVal = (pelangganInput?.value || '').toLowerCase().trim();
      const statusVal = getActiveStatus();
      let visibleCount = 0;

      cards.forEach((card) => {
        const kode = card.getAttribute('data-kode') || '';
        const pelanggan = card.getAttribute('data-pelanggan') || '';
        const status = (card.getAttribute('data-status') || '').toLowerCase();
        const matchKode = !kodeVal || kode.includes(kodeVal);
        const matchPelanggan = !pelangganVal || pelanggan.includes(pelangganVal);
        const matchStatus = !statusVal || status === statusVal;
        const show = matchKode && matchPelanggan && matchStatus;
        card.classList.toggle('d-none', !show);
        if (show) visibleCount += 1;
      });

      if (emptyState) {
        const hasCards = cards.length > 0;
        emptyState.classList.toggle('d-none', !hasCards || visibleCount > 0);
      }

      if (countUnsent) {
        const totalUnsent = Array.from(cards).filter((card) => (card.getAttribute('data-status') || '').toLowerCase() === 'unsent').length;
        countUnsent.textContent = totalUnsent;
      }
      if (countSent) {
        const totalSent = Array.from(cards).filter((card) => (card.getAttribute('data-status') || '').toLowerCase() === 'sent').length;
        countSent.textContent = totalSent;
      }
    };

    kodeInput?.addEventListener('input', filterCards);
    pelangganInput?.addEventListener('input', filterCards);
    resetBtn?.addEventListener('click', () => {
      if (kodeInput) kodeInput.value = '';
      if (pelangganInput) pelangganInput.value = '';
      filterCards();
    });

    const headingEl = document.querySelector('[data-penawaran-heading]');
    const updateHeading = () => {
      if (!headingEl) return;
      const statusVal = getActiveStatus();
      headingEl.textContent = statusVal === 'sent'
        ? 'Penawaran Menunggu Persetujuan Pemohon'
        : 'Penawaran Belum Dikirim';
    };

    statusButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        statusButtons.forEach((b) => {
          b.classList.remove('active');
          if (b.classList.contains('btn-deep-blue')) {
            b.classList.remove('btn-deep-blue');
            b.classList.add('btn-outline-deep-blue');
          } else if (b.classList.contains('btn-outline-deep-blue')) {
            b.classList.remove('btn-outline-deep-blue');
          }
        });
        btn.classList.add('active');
        btn.classList.remove('btn-outline-deep-blue');
        btn.classList.add('btn-deep-blue');
        updateHeading();
        filterCards();
      });
    });

    updateHeading();
    filterCards();

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const collectParameters = (cardId) => {
      const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
      if (!card) return [];
      const rows = card.querySelectorAll('[data-param-id]');
      return Array.from(rows).map((row) => {
        const paramId = parseInt(row.getAttribute('data-param-id') || '0', 10);
        if (!paramId) return null;
        let qty = parseInt(row.getAttribute('data-qty-value') || '1', 10);
        let harga = parseFloat(row.getAttribute('data-harga-value') || '0');

        const qtyInput = row.querySelector(`[data-qty][data-penawaran-id="${cardId}"]`);
        const hargaNode = row.querySelector(`[data-harga-value][data-penawaran-id="${cardId}"]`);
        if (qtyInput) qty = parseInt(qtyInput.value || '1', 10);
        if (hargaNode) harga = parseFloat(hargaNode.getAttribute('data-harga-value') || '0');

        return { id: paramId, qty: qty > 0 ? qty : 1, harga: harga >= 0 ? harga : 0 };
      }).filter(Boolean);
    };

    const collectPdfParameters = (cardId) => {
      const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
      if (!card) return [];

      const rows = card.querySelectorAll('[data-param-id]');
      return Array.from(rows).map((row) => {
        const nama = row.querySelector('[data-param-nama]')?.textContent?.trim() || '-';
        const kategori = row.getAttribute('data-param-kategori')
          || row.querySelector('[data-param-kategori-label]')?.textContent?.trim()
          || '-';
        const qtyInput = row.querySelector(`[data-qty][data-penawaran-id="${cardId}"]`);
        const hargaNode = row.querySelector(`[data-harga-value][data-penawaran-id="${cardId}"]`);
        const alasanRaw = row.querySelector('[data-param-alasan]')?.textContent || '';
        const qty = qtyInput ? parseInt(qtyInput.value || '0', 10) || 0 : 0;
        const harga = hargaNode ? parseInt(hargaNode.getAttribute('data-harga-value') || '0', 10) || 0 : 0;
        const bisaUji = row.hasAttribute('data-testable-row');
        const alasan = alasanRaw ? alasanRaw.trim().replace(/^Alasan:\s*/i, '') : '';

        return {
          nama,
          kategori,
          qty,
          harga,
          subtotal: qty * harga,
          bisa_uji: bisaUji,
          alasan,
        };
      }).filter((item) => item.nama);
    };

    const downloadPdfFile = async (pdfUrl, payload, fallbackName = 'penawaran.pdf') => {
      const response = await fetch(pdfUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/pdf, application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const result = await response.json().catch(() => ({}));
        throw new Error(result.message || 'Gagal mengunduh dokumen PDF.');
      }

      const blob = await response.blob();
      const contentDisposition = response.headers.get('Content-Disposition') || '';
      const match = contentDisposition.match(/filename=\"?([^\";]+)\"?/i);
      const filename = match?.[1] || fallbackName;
      const blobUrl = window.URL.createObjectURL(blob);
      const link = document.createElement('a');

      link.href = blobUrl;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();

      window.setTimeout(() => window.URL.revokeObjectURL(blobUrl), 1000);
      return true;
    };

    const printModalEl = document.querySelector('[data-print-modal]');
    const printForm = printModalEl?.querySelector('[data-print-form]');
    const printModal = printModalEl && window.bootstrap ? new window.bootstrap.Modal(printModalEl) : null;

    const formatDateLabel = (dateStr) => {
      if (!dateStr) return '-';
      const d = new Date(`${dateStr}T00:00:00`);
      if (Number.isNaN(d.getTime())) return dateStr;
      const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
      ];
      return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
    };

    const fillPrintModal = (cardId, draftUrl) => {
      if (!printModalEl) return;
      const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
      if (!card) return;

      printModalEl.setAttribute('data-penawaran-id', cardId);
      printModalEl.setAttribute('data-draft-url', draftUrl || '');
      printModalEl.setAttribute('data-pdf-url', card.getAttribute('data-pdf-url') || '');

      const kode = card.querySelector('.pnw-accordion-kode')?.textContent?.trim()
        || card.querySelector('h5')?.textContent?.trim()
        || cardId;
      const pelanggan = card.querySelector('.pnw-accordion-nama')?.textContent?.trim()
        || card.querySelector('.text-muted.small')?.textContent?.replace('Penawaran untuk', '').trim()
        || card.getAttribute('data-pelanggan')
        || '-';
      const nomorSurat = buildNomorSurat(cardId);
      const kodeEl = printModalEl.querySelector('[data-print-kode]');
      const pelangganEl = printModalEl.querySelector('[data-print-pelanggan]');
      const nomorEl = printModalEl.querySelector('[data-print-nomor]');
      if (kodeEl) kodeEl.textContent = kode || '-';
      if (pelangganEl) pelangganEl.textContent = pelanggan ? `Perusahaan: ${pelanggan}` : '-';
      if (nomorEl) nomorEl.textContent = nomorSurat || '-';

      const testableBody = printModalEl.querySelector('[data-print-testable]');
      const nonTestableBody = printModalEl.querySelector('[data-print-nontestable]');
      const nonTestableWrap = printModalEl.querySelector('[data-print-nontestable-wrap]');
      const testableRows = card.querySelectorAll(`[data-testable-row][data-penawaran-id="${cardId}"]`);
      const nonTestableRows = card.querySelectorAll(`[data-non-testable-row][data-penawaran-id="${cardId}"]`);
      const allNonTestable = testableRows.length === 0 && nonTestableRows.length > 0;
      printModalEl.setAttribute('data-all-non-testable', allNonTestable ? '1' : '0');

      const fullBtn = printModalEl.querySelector('[data-print-action="full"]');
      const nonTestableBtn = printModalEl.querySelector('[data-print-action="non_testable"]');
      if (fullBtn) {
        fullBtn.disabled = false;
        fullBtn.classList.toggle('is-restricted', allNonTestable);
        fullBtn.setAttribute('aria-disabled', 'false');
        fullBtn.title = allNonTestable
          ? 'Semua parameter tidak bisa diuji. Gunakan tombol Tidak Bisa Diuji.'
          : '';
      }
      if (nonTestableBtn) {
        const hasNonTestable = nonTestableRows.length > 0;
        nonTestableBtn.disabled = !hasNonTestable;
        nonTestableBtn.classList.toggle('disabled', !hasNonTestable);
        nonTestableBtn.setAttribute('aria-disabled', hasNonTestable ? 'false' : 'true');
      }

      if (nonTestableWrap) {
        nonTestableWrap.classList.toggle('d-none', nonTestableRows.length === 0);
      }

      if (testableBody) {
        if (testableRows.length === 0) {
          testableBody.innerHTML = '<tr><td colspan="5" class="text-muted text-center small">Belum ada parameter yang bisa diuji.</td></tr>';
        } else {
          const grouped = [];
          const groupMap = new Map();

          testableRows.forEach((row) => {
            const nama = row.querySelector('[data-param-nama]')?.textContent?.trim() || '-';
            const kategori = row.getAttribute('data-param-kategori') || row.querySelector('[data-param-kategori-label]')?.textContent?.trim() || '-';
            const qtyInput = row.querySelector(`[data-qty][data-penawaran-id="${cardId}"]`);
            const hargaNode = row.querySelector(`[data-harga-value][data-penawaran-id="${cardId}"]`);
            const qty = qtyInput ? parseInt(qtyInput.value || '0', 10) || 0 : 0;
            const harga = hargaNode ? parseInt(hargaNode.getAttribute('data-harga-value') || '0', 10) || 0 : 0;
            const sub = qty * harga;

            if (!groupMap.has(kategori)) {
              const entry = { kategori, rows: [] };
              groupMap.set(kategori, entry);
              grouped.push(entry);
            }

            groupMap.get(kategori).rows.push({ nama, qty, harga, sub });
          });

          let rowsHtml = '';
          grouped.forEach((group) => {
            group.rows.forEach((item, idx) => {
              rowsHtml += `
                <tr>
                  ${idx === 0 ? `<td rowspan="${group.rows.length}" class="align-top fw-semibold">${group.kategori}</td>` : ''}
                  <td>${item.nama}</td>
                  <td class="text-center">${item.qty}</td>
                  <td class="text-end">Rp ${formatCurrency(item.harga)}</td>
                  <td class="text-end">Rp ${formatCurrency(item.sub)}</td>
                </tr>
              `;
            });
          });

          testableBody.innerHTML = rowsHtml;
        }
      }

      if (nonTestableBody) {
        if (nonTestableRows.length === 0) {
          nonTestableBody.innerHTML = '<tr><td colspan="3" class="text-muted text-center small">Tidak ada parameter yang tidak bisa diuji.</td></tr>';
        } else {
          const grouped = [];
          const groupMap = new Map();

          nonTestableRows.forEach((row) => {
            const nama = row.querySelector('[data-param-nama]')?.textContent?.trim() || '-';
            const kategori = row.getAttribute('data-param-kategori') || row.querySelector('[data-param-kategori-label]')?.textContent?.trim() || '-';
            const alasanRaw = row.querySelector('[data-param-alasan]')?.textContent || '';
            const alasan = alasanRaw ? alasanRaw.trim().replace(/^Alasan:\s*/i, '') : '-';

            if (!groupMap.has(kategori)) {
              const entry = { kategori, rows: [] };
              groupMap.set(kategori, entry);
              grouped.push(entry);
            }

            groupMap.get(kategori).rows.push({ nama, alasan });
          });

          let rowsHtml = '';
          grouped.forEach((group) => {
            group.rows.forEach((item, idx) => {
              rowsHtml += `
                <tr>
                  ${idx === 0 ? `<td rowspan="${group.rows.length}" class="align-top fw-semibold">${group.kategori}</td>` : ''}
                  <td>${item.nama}</td>
                  <td>${item.alasan}</td>
                </tr>
              `;
            });
          });

          nonTestableBody.innerHTML = rowsHtml;
        }
      }

      if (printModal) {
        printModal.show();
        return true;
      }
      return false;
    };

    const closePrintModal = () => {
      if (!printModalEl) return;
      if (printModal) {
        printModal.hide();
        return;
      }
      printModalEl.classList.remove('show');
      printModalEl.style.display = 'none';
      printModalEl.setAttribute('aria-hidden', 'true');
      printModalEl.removeAttribute('aria-modal');
      document.body.classList.remove('modal-open');
    };

    if (printModalEl) {
      printModalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((btn) => {
        btn.addEventListener('click', () => closePrintModal());
      });
    }

    // Upload dokumen penawaran
    document.querySelectorAll('[data-upload-penawaran]').forEach((input) => {
      input.addEventListener('change', () => {
        const cardId = input.getAttribute('data-penawaran-id');
        const sendBtn = document.querySelector(`[data-send-penawaran][data-penawaran-id="${cardId}"]`);
        const sendWrap = document.querySelector(`[data-send-wrap][data-penawaran-id="${cardId}"]`);
        const info = document.querySelector(`[data-upload-info][data-penawaran-id="${cardId}"]`);
        const linkBtn = document.querySelector(`[data-link-penawaran][data-penawaran-id="${cardId}"]`);
        if (input.files && input.files.length > 0) {
          sendBtn?.classList.remove('disabled');
          sendBtn?.removeAttribute('aria-disabled');
          if (info) {
            info.textContent = 'Dokumen siap dikirim.';
            info.classList.remove('text-muted');
            info.classList.add('text-success');
          }
          if (sendWrap) sendWrap.classList.remove('d-none');
          if (linkBtn) {
            const prevUrl = linkBtn.getAttribute('data-preview-url');
            if (prevUrl) {
              URL.revokeObjectURL(prevUrl);
            }
            const previewUrl = URL.createObjectURL(input.files[0]);
            linkBtn.href = previewUrl;
            linkBtn.setAttribute('data-preview-url', previewUrl);
            linkBtn.classList.remove('disabled');
            linkBtn.removeAttribute('aria-disabled');
          }
        } else {
          sendBtn?.classList.add('disabled');
          sendBtn?.setAttribute('aria-disabled', 'true');
          if (info) {
            info.textContent = 'Belum ada dokumen diunggah.';
            info.classList.add('text-muted');
            info.classList.remove('text-success');
          }
          if (sendWrap) sendWrap.classList.add('d-none');
          if (linkBtn) {
            const prevUrl = linkBtn.getAttribute('data-preview-url');
            if (prevUrl) {
              URL.revokeObjectURL(prevUrl);
              linkBtn.removeAttribute('data-preview-url');
            }
            linkBtn.classList.add('disabled');
            linkBtn.setAttribute('aria-disabled', 'true');
            linkBtn.href = '#';
          }
        }
      });
    });

    document.querySelectorAll('[data-send-penawaran]').forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        if (btn.classList.contains('disabled')) {
          Swal.fire({
            icon: 'warning',
            title: 'Dokumen belum diunggah',
            text: 'Unggah dokumen penawaran terlebih dahulu.',
          });
          return;
        }

        const cardId = btn.getAttribute('data-penawaran-id');
        const sendUrl = btn.getAttribute('data-send-url');
        const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
        const uploadInput = document.querySelector(`[data-upload-penawaran][data-penawaran-id="${cardId}"]`);
        const info = document.querySelector(`[data-upload-info][data-penawaran-id="${cardId}"]`);
        const linkBtn = document.querySelector(`[data-link-penawaran][data-penawaran-id="${cardId}"]`);

        if (!uploadInput || !uploadInput.files || uploadInput.files.length === 0) {
          Swal.fire({
            icon: 'warning',
            title: 'Dokumen belum diunggah',
            text: 'Unggah dokumen penawaran terlebih dahulu.',
          });
          return;
        }

        const parameters = collectParameters(cardId);
        if (parameters.length === 0) {
          Swal.fire({
            icon: 'error',
            title: 'Parameter kosong',
            text: 'Parameter penawaran tidak ditemukan.',
          });
          return;
        }

        const nomorSurat = buildNomorSurat(cardId);
        if (!nomorSurat) {
          Swal.fire({
            icon: 'warning',
            title: 'Nomor surat kosong',
            text: 'Isi nomor surat terlebih dahulu.',
          });
          return;
        }

        const formData = new FormData();
        formData.append('parameters', JSON.stringify(parameters));
        formData.append('nomor_surat', nomorSurat);
        formData.append('catatan', getCatatanKhusus(cardId));
        formData.append('document', uploadInput.files[0]);

        btn.classList.add('disabled');
        btn.setAttribute('aria-disabled', 'true');

        try {
          const response = await fetch(sendUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
            },
            body: formData,
          });

          const result = await response.json().catch(() => ({}));
          if (!response.ok) {
            throw new Error(result.message || 'Gagal mengirim penawaran.');
          }

          if (linkBtn && result.document_url) {
            linkBtn.href = result.document_url;
            linkBtn.classList.remove('disabled');
            linkBtn.removeAttribute('aria-disabled');
          }
          if (info) info.textContent = 'Penawaran berhasil dikirim ke pemohon.';

          await Swal.fire({
            icon: 'success',
            title: 'Penawaran terkirim',
            text: result.message || 'Penawaran berhasil dikirim ke pemohon.',
          });
          window.location.reload();
        } catch (err) {
          btn.classList.remove('disabled');
          btn.removeAttribute('aria-disabled');
          Swal.fire({
            icon: 'error',
            title: 'Gagal mengirim',
            text: err.message || 'Terjadi kesalahan saat mengirim penawaran.',
          });
        }
      });
    });

    const runPrintLegacy = async (cardId, draftUrlOverride = '', mode = 'full') => {
      const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
      if (!card) return false;

      const nomorSurat = buildNomorSurat(cardId);
      const isNonTestableOnly = mode === 'non_testable';
      if (!nomorSurat) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'warning',
            title: 'Nomor surat kosong',
            text: 'Isi nomor surat terlebih dahulu sebelum mencetak dokumen.',
          });
        } else {
          alert('Isi nomor surat terlebih dahulu sebelum mencetak dokumen.');
        }
        return false;
      }

      const parameters = collectParameters(cardId);
      if (!isNonTestableOnly && parameters.length === 0) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Parameter kosong',
            text: 'Parameter penawaran tidak ditemukan.',
          });
        } else {
          alert('Parameter penawaran tidak ditemukan.');
        }
        return false;
      }

      if (!isNonTestableOnly) {
        const draftSaved = await saveDraftPenawaran(cardId, draftUrlOverride, false);
        if (!draftSaved) {
          return false;
        }
      }

      const kode = card.querySelector('.pnw-accordion-kode')?.textContent?.trim()
        || card.querySelector('h5')?.textContent?.trim()
        || cardId;
      const pelanggan = card.querySelector('.pnw-accordion-nama')?.textContent?.trim()
        || card.querySelector('.text-muted.small')?.textContent?.replace('Penawaran untuk', '').trim()
        || card.getAttribute('data-pelanggan')
        || '';
      const requestDateRaw = card.getAttribute('data-request-date') || '';
      const perihal = card.getAttribute('data-perihal') || '...........................';
      const signerName = card.getAttribute('data-signer-name') || '.................................';
      const signerRole = card.getAttribute('data-signer-role') || '(Jabatan)';
      const catatanKhusus = getCatatanKhusus(cardId);
      const catatanKhususHtml = catatanKhusus
        ? `<div class="paragraph" style="text-indent:0;"><strong>Catatan Khusus:</strong> ${escapeHtml(catatanKhusus).replace(/\n/g, '<br>')}</div>`
        : '';
      const berlaku = card.querySelector('.text-muted.small')?.textContent?.split('Berlaku hingga:')[1]?.trim() || '';
      const rows = card.querySelectorAll(`[data-testable-row][data-penawaran-id="${cardId}"]`);
      const rowsNt = card.querySelectorAll(`[data-non-testable-row][data-penawaran-id="${cardId}"]`);
      const allNonTestable = rows.length === 0 && rowsNt.length > 0;
      if (!isNonTestableOnly && allNonTestable) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'warning',
            title: 'Dokumen tidak tersedia',
            text: 'Semua parameter tidak bisa diuji. Gunakan tombol "Tidak Bisa Diuji".',
          });
        } else {
          alert('Semua parameter tidak bisa diuji. Gunakan tombol "Tidak Bisa Diuji".');
        }
        return false;
      }
      const totalEl = card.querySelector(`[data-total][data-penawaran-id="${cardId}"]`);

      let nonTestableListItems = '';
      rowsNt.forEach((row, idx) => {
        const nama = row.querySelector('[data-param-nama]')?.textContent?.trim() || '-';
        const kategori = row.getAttribute('data-param-kategori') || row.querySelector('[data-param-kategori-label]')?.textContent?.trim() || '-';
        const alasanRaw = row.querySelector('[data-param-alasan]')?.textContent || '';
        const alasan = alasanRaw ? alasanRaw.trim().replace(/^Alasan:\s*/i, '') : '-';
        nonTestableListItems += `
          <li>
            <div class="item-title">${idx + 1}. [${kategori}] ${nama}</div>
            <div class="item-meta">Alasan: ${alasan}</div>
          </li>
        `;
      });

      if (isNonTestableOnly) {
        if (rowsNt.length === 0) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'warning',
              title: 'Tidak ada parameter',
              text: 'Parameter tidak bisa diuji tidak ditemukan.',
            });
          } else {
            alert('Parameter tidak bisa diuji tidak ditemukan.');
          }
          return false;
        }

        const months = [
          'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        const now = new Date();
        const tanggalPenawaran = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
        const nonTestableCount = rowsNt.length;
        let requestDateText = '..................';
        if (requestDateRaw) {
          const dReq = new Date(`${requestDateRaw}T00:00:00`);
          if (!Number.isNaN(dReq.getTime())) {
            requestDateText = `${dReq.getDate()} ${months[dReq.getMonth()]} ${dReq.getFullYear()}`;
          }
        }

        const nomorSuratText = nomorSurat;
        const paragraphText = `Sehubungan dengan permintaan saudara tanggal ${requestDateText} perihal pengujian ${perihal}, berikut kami sampaikan daftar parameter yang belum dapat diuji saat ini :`;
        const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;
        const w = window.open('', '_blank', 'width=900,height=650');
        if (!w) return false;
        w.document.write(`
          <html>
            <head>
              <title></title>
              <style>
                body { font-family: "Times New Roman", serif; padding: 24px 28px; color: #111; }
                .print-layout { width: 100%; border-collapse: collapse; }
                .print-layout thead { display: table-header-group; }
                .print-layout td { padding: 0; vertical-align: top; }
                .print-kop { padding-bottom: 2px; }
                .print-content { padding-top: 0; }
                .letter-header { display: flex; gap: 10px; align-items: center; }
                .logo { width: 72px; height: 72px; object-fit: contain; }
                .header-text {
                  text-align: left;
                  flex: 1;
                  font-family: "Arial Narrow", Arial, sans-serif;
                  border-left: 2px solid #163e67;
                  padding-left: 12px;
                  line-height: 1.12;
                }
                .header-text .line1 { font-size: 13px; font-weight: 500; letter-spacing: 0; }
                .header-text .line2 { font-size: 14px; font-weight: 700; white-space: nowrap; }
                .header-text .line3 { font-size: 14px; font-weight: bold; }
                .header-text .line4 { font-size: 14px; font-weight: bold; }
                .header-text .line5 { font-size: 17px; font-weight: 700; color: #163e67; line-height: 1.05; margin-top: 3px; white-space: nowrap; }
                .header-text .addr { font-size: 11px; margin-top: 3px; font-family: Arial, sans-serif; }
                .header-text .addr .icon { color: #163e67; font-weight: 700; margin: 0 2px; }
                .header-line { border-top: 2px solid #000; margin: 10px 0 18px; }
                .meta-top { display: flex; justify-content: space-between; font-size: 13px; }
                .meta-left .row { display: flex; gap: 6px; margin-bottom: 4px; }
                .meta-left .label { width: 70px; }
                .meta-left .colon { width: 10px; }
                .meta-right { text-align: right; min-width: 220px; }
                .kepada { margin: 14px 0; font-size: 13px; }
                .paragraph { font-size: 13px; line-height: 1.5; text-indent: 28px; margin: 10px 0; }
                .section-title { font-size: 13px; margin-top: 10px; font-style: italic; }
                .simple-list { margin: 6px 0 0 16px; padding: 0; font-size: 13px; }
                .simple-list li { margin-bottom: 6px; }
                .item-title { font-weight: bold; }
                .item-meta { font-size: 12px; margin-top: 2px; }
                .closing { font-size: 13px; margin-top: 12px; text-align: center; }
                .signatures { display: flex; justify-content: space-between; margin-top: 22px; font-size: 13px; }
                .sign-col { width: 45%; text-align: center; }
                .sign-space { height: 60px; }
                @media print {
                  @page { margin: 14mm 12mm; }
                  body { padding: 0; }
                }
              </style>
            </head>
            <body>
              <table class="print-layout">
                <thead>
                  <tr>
                    <td>
                      <div class="print-kop">
                        <div class="letter-header">
                          <img src="${logoUrl}" alt="Logo" class="logo" id="printLogo">
                          <div class="header-text">
                            <div class="line1">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
                            <div class="line2">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
                            <div class="line4">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
                            <div class="line5">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
                            <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id</div>
                          </div>
                        </div>
                        <div class="header-line"></div>
                      </div>
                    </td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <div class="print-content">
              <div class="meta-top">
                <div class="meta-left">
                  <div class="row"><span class="label">Nomor</span><span class="colon">:</span><span>${nomorSuratText}</span></div>
                  <div class="row"><span class="label">Sifat</span><span class="colon">:</span><span>Penting</span></div>
                  <div class="row"><span class="label">Perihal</span><span class="colon">:</span><span>Parameter Tidak Bisa Diuji</span></div>
                </div>
                <div class="meta-right">Surabaya, ${tanggalPenawaran}</div>
              </div>

              <div class="kepada">
                <div>Kepada Yth. :</div>
                <div>${pelanggan || '........................................'}</div>
                <div>Di-Tempat.</div>
              </div>

              <div class="paragraph">${paragraphText}</div>
              <div class="paragraph" style="text-indent:0;">
                Jumlah parameter yang tidak bisa diuji: <strong>${nonTestableCount}</strong> parameter.
              </div>
              <div class="section-title">( Daftar Parameter Tidak Bisa Diuji )</div>
              <ul class="simple-list">
                ${nonTestableListItems || '<li>Tidak ada parameter tidak bisa diuji.</li>'}
              </ul>
              ${catatanKhususHtml}

              <div class="closing">Demikian atas perhatian dan kerjasamanya kami sampaikan terima kasih.</div>

              <div class="signatures">
                <div class="sign-col">
                  <div>Setuju/Tidak Setuju (*)</div>
                  <div>Terhadap Informasi</div>
                  <div class="sign-space"></div>
                  <div>${signerName}</div>
                  <div>${signerRole}</div>
                </div>
                <div class="sign-col">
                  <div>Kepala Balai K3 Surabaya</div>
                    <div class="sign-space"></div>
                  <div>${window.kepalaBalaiProfile?.nama || '-'}</div>
                </div>
              </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <script>
                const logo = document.getElementById('printLogo');
                const doPrint = () => setTimeout(() => window.print(), 250);
                if (logo) {
                  logo.addEventListener('load', doPrint, { once: true });
                  logo.addEventListener('error', doPrint, { once: true });
                } else {
                  doPrint();
                }
              <\/script>
            </body>
          </html>
        `);
        w.document.close();
        return true;
      }

      const groupedTestable = [];
      const groupedTestableMap = new Map();
      rows.forEach((row) => {
        const nama = row.querySelector('[data-param-nama]')?.textContent?.trim() || '-';
        const kategori = row.getAttribute('data-param-kategori') || row.querySelector('[data-param-kategori-label]')?.textContent?.trim() || '-';
        const qtyInput = row.querySelector(`[data-qty][data-penawaran-id="${cardId}"]`);
        const hargaNode = row.querySelector(`[data-harga-value][data-penawaran-id="${cardId}"]`);
        const qty = qtyInput ? parseInt(qtyInput.value || '0', 10) || 0 : 0;
        const harga = hargaNode ? parseInt(hargaNode.getAttribute('data-harga-value') || '0', 10) || 0 : 0;
        const sub = qty * harga;

        if (!groupedTestableMap.has(kategori)) {
          const entry = { kategori, rows: [] };
          groupedTestableMap.set(kategori, entry);
          groupedTestable.push(entry);
        }

        groupedTestableMap.get(kategori).rows.push({ nama, qty, harga, sub });
      });

      let testableTableRows = '';
      groupedTestable.forEach((group) => {
        group.rows.forEach((item, idx) => {
          testableTableRows += `
            <tr>
              ${idx === 0 ? `<td rowspan="${group.rows.length}" class="text-center">${group.kategori}</td>` : ''}
              <td>${item.nama}</td>
              <td class="text-center">${item.qty}</td>
              <td class="text-end">Rp ${formatCurrency(item.harga)}</td>
              <td class="text-end">Rp ${formatCurrency(item.sub)}</td>
            </tr>
          `;
        });
      });

      const groupedNonTestable = [];
      const groupedNonTestableMap = new Map();
      rowsNt.forEach((row) => {
        const nama = row.querySelector('[data-param-nama]')?.textContent?.trim() || '-';
        const kategori = row.getAttribute('data-param-kategori') || row.querySelector('[data-param-kategori-label]')?.textContent?.trim() || '-';
        const alasanRaw = row.querySelector('[data-param-alasan]')?.textContent || '';
        const alasan = alasanRaw ? alasanRaw.trim().replace(/^Alasan:\s*/i, '') : '-';

        if (!groupedNonTestableMap.has(kategori)) {
          const entry = { kategori, rows: [] };
          groupedNonTestableMap.set(kategori, entry);
          groupedNonTestable.push(entry);
        }

        groupedNonTestableMap.get(kategori).rows.push({ nama, alasan });
      });

      let nonTestableTableRows = '';
      groupedNonTestable.forEach((group) => {
        group.rows.forEach((item, idx) => {
          nonTestableTableRows += `
            <tr>
              ${idx === 0 ? `<td rowspan="${group.rows.length}" class="text-center">${group.kategori}</td>` : ''}
              <td>${item.nama}</td>
              <td>${item.alasan}</td>
            </tr>
          `;
        });
      });

      const nonTestableSectionHtml = rowsNt.length > 0
        ? `
              <div class="param-group">
                <div class="param-title nontestable">Parameter Tidak Bisa Diuji</div>
                <div class="param-table-wrap">
                  <table class="param-table">
                    <thead>
                      <tr>
                        <th style="width:12%;">Kategori</th>
                        <th style="width:38%;">Parameter</th>
                        <th>Alasan</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${nonTestableTableRows}
                    </tbody>
                  </table>
                </div>
              </div>
          `
        : '';

      const totalText = totalEl?.textContent?.trim() || '0';
      const totalNumber = parseInt(totalText.replace(/\./g, '').replace(/,/g, ''), 10) || 0;
      const terbilang = (n) => {
        const angka = [
          '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh',
          'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];
        if (n < 12) return angka[n];
        if (n < 20) return `${terbilang(n - 10)} belas`;
        if (n < 100) return `${terbilang(Math.floor(n / 10))} puluh ${terbilang(n % 10)}`.trim();
        if (n < 200) return `seratus ${terbilang(n - 100)}`.trim();
        if (n < 1000) return `${terbilang(Math.floor(n / 100))} ratus ${terbilang(n % 100)}`.trim();
        if (n < 2000) return `seribu ${terbilang(n - 1000)}`.trim();
        if (n < 1000000) return `${terbilang(Math.floor(n / 1000))} ribu ${terbilang(n % 1000)}`.trim();
        if (n < 1000000000) return `${terbilang(Math.floor(n / 1000000))} juta ${terbilang(n % 1000000)}`.trim();
        if (n < 1000000000000) return `${terbilang(Math.floor(n / 1000000000))} miliar ${terbilang(n % 1000000000)}`.trim();
        return `${terbilang(Math.floor(n / 1000000000000))} triliun ${terbilang(n % 1000000000000)}`.trim();
      };
      const terbilangText = totalNumber > 0 ? `${terbilang(totalNumber)} rupiah` : 'nol rupiah';
      const now = new Date();
      const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
      ];
      const tanggalPenawaran = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
      let requestDateText = '..................';
      if (requestDateRaw) {
        const dReq = new Date(`${requestDateRaw}T00:00:00`);
        if (!Number.isNaN(dReq.getTime())) {
          requestDateText = `${dReq.getDate()} ${months[dReq.getMonth()]} ${dReq.getFullYear()}`;
        }
      }
      const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;
      const w = window.open('', '_blank', 'width=900,height=650');
      if (!w) return false;
      w.document.write(`
          <html>
            <head>
              <title></title>
              <style>
                body { font-family: "Times New Roman", serif; padding: 24px 28px; color: #111; }
                .print-layout { width: 100%; border-collapse: collapse; }
                .print-layout thead { display: table-header-group; }
                .print-layout td { padding: 0; vertical-align: top; }
                .print-kop { padding-bottom: 2px; }
                .print-content { padding-top: 0; }
                .letter-header { display: flex; gap: 10px; align-items: center; }
                .logo { width: 72px; height: 72px; object-fit: contain; }
                .header-text {
                  text-align: left;
                  flex: 1;
                  font-family: "Arial Narrow", Arial, sans-serif;
                  border-left: 2px solid #163e67;
                  padding-left: 12px;
                  line-height: 1.12;
                }
                .header-text .line1 { font-size: 13px; font-weight: 500; letter-spacing: 0; }
                .header-text .line2 { font-size: 14px; font-weight: 700; white-space: nowrap; }
                .header-text .line3 { font-size: 14px; font-weight: bold; }
                .header-text .line4 { font-size: 14px; font-weight: bold; }
                .header-text .line5 { font-size: 17px; font-weight: 700; color: #163e67; line-height: 1.05; margin-top: 3px; white-space: nowrap; }
                .header-text .addr { font-size: 11px; margin-top: 3px; font-family: Arial, sans-serif; }
                .header-text .addr .icon { color: #163e67; font-weight: 700; margin: 0 2px; }
                .header-line { border-top: 2px solid #000; margin: 10px 0 18px; }
                .meta-top { display: flex; justify-content: space-between; font-size: 13px; }
                .meta-left .row { display: flex; gap: 6px; margin-bottom: 4px; }
                .meta-left .label { width: 70px; }
                .meta-left .colon { width: 10px; }
                .meta-right { text-align: right; min-width: 220px; }
                .kepada { margin: 14px 0; font-size: 13px; }
                .kepada .line { border-bottom: 1px solid #000; display: inline-block; min-width: 300px; }
                .paragraph { font-size: 13px; line-height: 1.5; text-indent: 28px; margin: 10px 0; }
                .section-title { font-size: 13px; margin-top: 10px; font-style: italic; }
                .param-group { border: 1px solid #000; border-radius: 6px; padding: 8px 10px; margin-top: 8px; }
                .param-title { font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
                .param-title.testable { border-left: 4px solid #1b5e20; padding-left: 6px; }
                .param-title.nontestable { border-left: 4px solid #b71c1c; padding-left: 6px; }
                .param-table-wrap { margin-top: 6px; }
                .param-table {
                  width: 100%;
                  border-collapse: collapse;
                  font-size: 12px;
                }
                .param-table th,
                .param-table td {
                  border: 1px solid #000;
                  padding: 4px 6px;
                  vertical-align: top;
                }
                .param-table thead th {
                  background: #f1f1f1;
                  font-weight: bold;
                  text-align: center;
                }
                .param-table .text-center { text-align: center; }
                .param-table .text-end { text-align: right; }
                .param-empty { font-size: 12px; text-align: center; padding: 6px 0; }
                .summary-line { display: flex; justify-content: space-between; font-size: 12px; font-weight: bold; border-top: 1px solid #000; padding-top: 6px; margin-top: 6px; }
                .terbilang { font-size: 12px; text-align: center; margin: 6px 0 10px; font-style: italic; }
                .jadwal { font-size: 13px; margin: 6px 0; }
                .closing { font-size: 13px; margin-top: 12px; text-align: center; }
                .signatures { display: flex; justify-content: space-between; margin-top: 22px; font-size: 13px; }
                .sign-col { width: 45%; text-align: center; }
                .sign-space { height: 60px; }
                .note { font-size: 11px; margin-top: 10px; }
                .note ul { margin: 6px 0 0 16px; padding: 0; }
                .footer-line { border-top: 1px solid #000; margin-top: 12px; }
                .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 4px; }
                @media print {
                  @page { margin: 14mm 12mm; }
                  body { padding: 0; }
                  .param-table thead { display: table-header-group; }
                  .param-table tr { page-break-inside: avoid; }
                }
              </style>
            </head>
            <body>
              <table class="print-layout">
                <thead>
                  <tr>
                    <td>
                      <div class="print-kop">
                        <div class="letter-header">
                          <img src="${logoUrl}" alt="Logo" class="logo" id="printLogo">
                          <div class="header-text">
                            <div class="line1">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
                            <div class="line2">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
                            <div class="line4">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
                            <div class="line5">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
                            <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id</div>
                          </div>
                        </div>
                        <div class="header-line"></div>
                      </div>
                    </td>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>
                      <div class="print-content">
              <div class="meta-top">
                <div class="meta-left">
                  <div class="row"><span class="label">Nomor</span><span class="colon">:</span><span>${nomorSurat}</span></div>
                  <div class="row"><span class="label">Sifat</span><span class="colon">:</span><span>Penting</span></div>
                  <div class="row"><span class="label">Perihal</span><span class="colon">:</span><span>Rincian Kegiatan</span></div>
                </div>
                <div class="meta-right">Surabaya, ${tanggalPenawaran}</div>
              </div>

              <div class="kepada">
                <div>Kepada Yth. :</div>
                <div>${pelanggan || '........................................'}</div>
                <div>Di-Tempat.</div>
              </div>

              <div class="paragraph">
                Sehubungan dengan permintaan saudara tanggal ${requestDateText} perihal
                pengujian ${perihal}, maka bersama ini kami sampaikan rincian biaya pengujian berdasarkan
                Peraturan Menteri Keuangan Nomor 02 Tahun 2023 tentang Jenis dan Tarif Penerimaan Negara Bukan Pajak
                Yang Berlaku Pada Kementerian Ketenagakerjaan sebagai berikut :
              </div>

              <div class="section-title">( Rincian Parameter )</div>
              <div class="param-group">
                <div class="param-title testable">Parameter Bisa Diuji</div>
                <div class="param-table-wrap">
                  <table class="param-table">
                    <thead>
                      <tr>
                        <th style="width:12%;">Kategori</th>
                        <th>Parameter</th>
                        <th style="width:10%;">Qty</th>
                        <th style="width:18%;">Harga</th>
                        <th style="width:18%;">Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${testableTableRows || '<tr><td colspan="5" class="param-empty">Belum ada parameter yang bisa diuji.</td></tr>'}
                    </tbody>
                  </table>
                </div>
                <div class="summary-line">
                  <span>Jumlah Biaya Pengujian</span>
                  <span>Rp ${totalText},-</span>
                </div>
                <div class="terbilang">( ${terbilangText} )</div>
              </div>
              ${nonTestableSectionHtml}
              ${catatanKhususHtml}
              <div class="paragraph" style="text-indent:0;">
                Transportasi dan Akomodasi petugas pada saat pelaksanaan pengujian ditanggung oleh Perusahaan atau customer.
              </div>

              <div class="closing">Demikian atas perhatian dan kerjasamanya kami sampaikan terima kasih.</div>

                <div class="signatures">
                  <div class="sign-col">
                    <div>Setuju/Tidak Setuju (*)</div>
                    <div>Terhadap Rincian Biaya</div>
                    <div class="sign-space"></div>
                  <div>${signerName}</div>
                  <div>${signerRole}</div>
                  </div>
                  <div class="sign-col">
                    <div>Kepala Balai K3 Surabaya</div>
                    <div class="sign-space"></div>
                  <div>${window.kepalaBalaiProfile?.nama || '-'}</div>
                </div>
              </div>

              <div class="note">
                <strong>Catatan :</strong>
                <ul>
                  <li>(*) Coret yang Tidak Perlu</li>
                  <li>Transportasi (Penjemputan) menjadi tanggungan perusahaan (customer);</li>
                  <li>Pelunasan paling lambat 7 (Tujuh) hari kerja setelah tanggal pelaksanaan sampling;</li>
                  <li>Setelah persetujuan ditandatangani, mohon dikirim kembali via email di balaik3surabaya@kemnaker.go.id paling lambat 5 (hari) hari setelah penawaran ini diterima;</li>
                  <li>Apabila terjadi perubahan jadwal sampling agar dikonfirmasi 2 (dua) hari sebelumnya;</li>
                  <li>Pembatalan secara sepihak akan dikenakan biaya bahan kimia.</li>
                </ul>
              </div>

              <div class="footer-line"></div>
              <div class="footer-meta">
                <span>Tgl. terbit: 24 Desember 2024</span>
                <span>No.: /F/6.6.10/BK3-SBY</span>
              </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <script>
                const logo = document.getElementById('printLogo');
                const doPrint = () => setTimeout(() => window.print(), 250);
                if (logo) {
                  logo.addEventListener('load', doPrint, { once: true });
                  logo.addEventListener('error', doPrint, { once: true });
                } else {
                  doPrint();
                }
              <\/script>
            </body>
          </html>
        `);
      w.document.close();
      return true;
    };

    const runPrint = async (cardId, draftUrlOverride = '', mode = 'full') => {
      const card = document.querySelector(`[data-penawaran-card][data-penawaran-id="${cardId}"]`);
      if (!card) return false;

      const nomorSurat = buildNomorSurat(cardId);
      if (!nomorSurat) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'warning',
            title: 'Nomor surat kosong',
            text: 'Isi nomor surat terlebih dahulu sebelum mengunduh dokumen.',
          });
        } else {
          alert('Isi nomor surat terlebih dahulu sebelum mengunduh dokumen.');
        }
        return false;
      }

      const parameters = collectPdfParameters(cardId);
      if (parameters.length === 0) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Parameter kosong',
            text: 'Parameter penawaran tidak ditemukan.',
          });
        } else {
          alert('Parameter penawaran tidak ditemukan.');
        }
        return false;
      }

      const testableCount = parameters.filter((item) => item.bisa_uji).length;
      const nonTestableCount = parameters.filter((item) => !item.bisa_uji).length;

      if (mode === 'full' && testableCount === 0 && nonTestableCount > 0) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'warning',
            title: 'Dokumen tidak tersedia',
            text: 'Semua parameter tidak bisa diuji. Gunakan tombol "Tidak Bisa Diuji".',
          });
        } else {
          alert('Semua parameter tidak bisa diuji. Gunakan tombol "Tidak Bisa Diuji".');
        }
        return false;
      }

      if (mode === 'non_testable' && nonTestableCount === 0) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'warning',
            title: 'Tidak ada parameter',
            text: 'Parameter tidak bisa diuji tidak ditemukan.',
          });
        } else {
          alert('Parameter tidak bisa diuji tidak ditemukan.');
        }
        return false;
      }

      const draftSaved = await saveDraftPenawaran(cardId, draftUrlOverride, false);
      if (!draftSaved) {
        return false;
      }

      const pdfUrl = card.getAttribute('data-pdf-url')
        || document.querySelector(`[data-print-rekap][data-penawaran-id="${cardId}"]`)?.getAttribute('data-pdf-url')
        || '';
      if (!pdfUrl) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'URL PDF tidak tersedia',
            text: 'Endpoint PDF penawaran tidak ditemukan.',
          });
        } else {
          alert('Endpoint PDF penawaran tidak ditemukan.');
        }
        return false;
      }

      const kode = card.querySelector('.pnw-accordion-kode')?.textContent?.trim()
        || card.querySelector('h5')?.textContent?.trim()
        || cardId;
      const pelanggan = card.querySelector('.pnw-accordion-nama')?.textContent?.trim()
        || card.querySelector('.text-muted.small')?.textContent?.replace('Penawaran untuk', '').trim()
        || card.getAttribute('data-pelanggan')
        || '';
      const payload = {
        mode,
        nomor_surat: nomorSurat,
        kode,
        pelanggan,
        request_date: card.getAttribute('data-request-date') || '',
        perihal: card.getAttribute('data-perihal') || '...........................',
        signer_name: card.getAttribute('data-signer-name') || '.................................',
        signer_role: card.getAttribute('data-signer-role') || '(Jabatan)',
        catatan: getCatatanKhusus(cardId),
        parameters,
      };

      try {
        const fallbackName = mode === 'non_testable'
          ? `parameter_tidak_bisa_diuji_${kode}.pdf`
          : `penawaran_${kode}.pdf`;
        await downloadPdfFile(pdfUrl, payload, fallbackName);
        return true;
      } catch (err) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Gagal mengunduh',
            text: err.message || 'Terjadi kesalahan saat mengunduh PDF.',
          });
        } else {
          alert(err.message || 'Terjadi kesalahan saat mengunduh PDF.');
        }
        return false;
      }
    };

    if (printForm && printModalEl) {
      printModalEl.querySelectorAll('[data-print-action]').forEach((btn) => {
        btn.addEventListener('click', async () => {
          const cardId = printModalEl.getAttribute('data-penawaran-id');
          const draftUrl = printModalEl.getAttribute('data-draft-url') || '';
          const mode = btn.getAttribute('data-print-action') || 'full';
          if (!cardId) return;
          setButtonLoading(btn, true, 'Mengunduh...');
          try {
            const success = await runPrint(cardId, draftUrl, mode);
            if (success) {
              closePrintModal();
            }
          } finally {
            setButtonLoading(btn, false);
          }
        });
      });
    }

    // Cetak / unduh rekap parameter (bisa & tidak bisa diuji)
    document.querySelectorAll('[data-print-rekap]').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const cardId = btn.getAttribute('data-penawaran-id');
        if (!cardId) return;
        const draftUrl = btn.getAttribute('data-draft-url') || '';
        if (!printModalEl) {
          setButtonLoading(btn, true, 'Mengunduh...');
          try {
            await runPrint(cardId, draftUrl, 'full');
          } finally {
            setButtonLoading(btn, false);
          }
          return;
        }
        const opened = fillPrintModal(cardId, draftUrl);
        if (!opened) {
          setButtonLoading(btn, true, 'Mengunduh...');
          try {
            await runPrint(cardId, draftUrl, 'full');
          } finally {
            setButtonLoading(btn, false);
          }
        }
      });
    });
  })();
</script>
@endpush

@push('styles')
<style>
  .content-wrapper,
  .container-fluid {
    font-family: 'Poppins', sans-serif;
  }

  .pnw-filter-card {
    border: 1px solid #d8e1ec !important;
    background: #ffffff;
  }

  .pnw-search-field {
    position: relative;
  }

  .pnw-search-field > i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #7a90a8;
    font-size: 0.9rem;
    pointer-events: none;
  }

  .pnw-search-field .form-control {
    border-radius: 12px;
    min-height: 40px;
    padding-left: 2.05rem;
    font-size: 0.86rem;
    border-color: #c6d5e6;
  }

  .pnw-search-field .form-control::placeholder {
    color: #4f6680;
    opacity: 1;
  }

  .pnw-reset-btn {
    border-radius: 12px !important;
    min-height: 40px;
    font-size: 0.88rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.36rem;
  }

  .pnw-status-tabs {
    background: #e9eef5;
    border-radius: 14px;
    padding: 4px;
    display: inline-flex !important;
    gap: 6px !important;
  }

  .pnw-status-tabs .btn {
    border-radius: 10px !important;
    min-height: 38px;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.45rem 1rem;
    border: 1px solid transparent !important;
    box-shadow: none !important;
  }

  .pnw-status-tabs .btn-deep-blue {
    background: #15406A !important;
    color: #ffffff !important;
    border-color: #15406A !important;
  }

  .pnw-status-tabs .btn-outline-deep-blue {
    background: transparent !important;
    color: #70859d !important;
    border-color: transparent !important;
  }

  .pnw-status-tabs .badge {
    background: #e64b5f !important;
    color: #fff !important;
    border-radius: 999px !important;
    min-width: 18px;
    height: 18px;
    padding: 0 6px !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    font-weight: 700;
    line-height: 1;
  }

  .pnw-status-tabs .badge.text-bg-danger {
    background-color: #e53950 !important;
    color: #ffffff !important;
    border-color: #e53950 !important;
  }

  .pnw-section-title {
    color: #15406A;
    font-size: 0.98rem;
    font-weight: 700;
    letter-spacing: 0.01em;
  }

  .pnw-card {
    border: none !important;
    border-radius: 18px !important;
    background: transparent;
    box-shadow: none !important;
    overflow: visible;
  }

  .pnw-card > .card-body {
    padding: 0;
  }

  .pnw-accordion-btn {
    width: 100%;
    border: 1px solid #15406A;
    background: #15406A;
    border-radius: 14px;
    padding: 0.82rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    color: #ffffff;
    transition: all 0.25s ease;
  }

  .pnw-accordion-btn:hover {
    background: #11365a;
    border-color: #11365a;
  }

  .pnw-accordion-kode {
    font-size: 0.98rem;
    font-weight: 700;
    line-height: 1.2;
  }

  .pnw-accordion-left {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.16rem;
    min-width: 0;
  }

  .pnw-accordion-nama {
    font-size: 0.84rem;
    color: #ffffff;
    font-weight: 400;
    opacity: 0.96;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .pnw-accordion-right {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    white-space: nowrap;
  }

  .pnw-accordion-time {
    font-size: 10px;
    color: #ffffff;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }

  .pnw-accordion-chevron {
    font-size: 0.9rem;
    color: #ffffff;
    transition: transform 0.25s ease;
  }

  .pnw-accordion-btn:not(.collapsed) .pnw-accordion-chevron {
    transform: rotate(180deg);
  }

  .pnw-card-head {
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #edf2f7;
    margin-bottom: 0.9rem !important;
  }

  .pnw-card-overline {
    color: #607994 !important;
    font-size: 0.78rem !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.15rem;
  }

  .pnw-card-code {
    color: #15406A;
    font-size: 1.22rem;
    line-height: 1.2;
  }

  .pnw-card-meta {
    color: #5f7388 !important;
    font-size: 0.84rem !important;
    margin-top: 0.25rem;
  }

  .pnw-panel {
    border-color: #d9e3ee !important;
    background: #fcfdff !important;
  }

  .pnw-panel-wrap {
    margin-bottom: 1rem;
  }

  .pnw-panel-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    color: #1b2a3f;
    font-weight: 700;
    font-size: 0.9rem;
    margin-bottom: 0.7rem;
    padding: 0 0.2rem;
  }

  .pnw-panel-title i {
    color: #15406A;
    font-size: 0.92rem;
  }

  .pnw-field-label {
    color: #516882;
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 0.35rem;
  }

  .pnw-add-row {
    color: #15406A;
    font-size: 0.88rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .pnw-kategori {
    font-weight: 700;
    color: #15406A;
    font-size: 0.86rem;
    text-transform: uppercase;
  }

  .pnw-status-badge {
    font-size: 0.78rem;
    font-weight: 700;
    border-radius: 999px;
    padding: 0.34rem 0.58rem;
    border: 1px solid transparent;
  }

  .pnw-status-ok {
    color: #157347;
    background: #d1f5e0;
    border-color: #9de6c4;
  }

  .pnw-status-no {
    color: #b4233d;
    background: #fde2e7;
    border-color: #f7b6c4;
  }

  .pnw-table thead th {
    border-bottom: 1px solid #dce6f1;
    color: #5f7590;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding-top: 0.72rem;
    padding-bottom: 0.72rem;
    padding-left: 0.82rem;
    padding-right: 0.82rem;
    white-space: nowrap;
  }

  .pnw-table td {
    padding-top: 0.68rem;
    padding-bottom: 0.68rem;
    padding-left: 0.82rem;
    padding-right: 0.82rem;
    font-size: 0.88rem;
    color: #15314f;
    vertical-align: middle;
    line-height: 1.3;
  }

  .pnw-table .form-control-sm {
    min-height: 32px;
    font-size: 0.86rem;
  }

  .pnw-summary {
    border-color: rgba(21, 64, 106, 0.35) !important;
    background: #fff !important;
  }

  .pnw-summary hr {
    border-color: #d9e4f0;
  }

  .pnw-summary .fw-semibold {
    color: #173a60;
  }

  .pnw-summary [data-total] {
    color: #15406A;
    font-size: 1.7rem;
    font-weight: 800;
    line-height: 1;
  }

  .pnw-summary-title {
    color: #1f2d42;
    font-size: 1rem;
    font-weight: 700;
  }

  .pnw-total-akhir {
    color: #15406A;
    font-size: 1.1rem;
    font-weight: 800;
    line-height: 1;
  }

  .pnw-info-extra {
    border: 1px solid rgba(21, 64, 106, 0.2);
    background: #f5f9ff;
    border-radius: 14px;
    padding: 0.9rem 1rem;
    color: #465b75;
  }

  .pnw-info-extra i {
    color: #15406A;
    margin-top: 2px;
  }

  .pnw-info-extra .fw-semibold {
    color: #1f2e43;
    font-size: 0.92rem;
  }

  .pnw-summary .small,
  .pnw-info-extra .small {
    font-size: 0.84rem !important;
    line-height: 1.45;
  }

  .pnw-summary .btn {
    min-height: 34px;
    font-size: 0.85rem;
    padding-top: 0.36rem;
    padding-bottom: 0.36rem;
  }

  .pnw-summary textarea.form-control {
    font-size: 0.86rem;
    line-height: 1.4;
  }

  .pnw-panel .input-group .form-control,
  .pnw-panel .input-group-text {
    font-size: 0.9rem;
  }

  .badge.text-bg-success {
    background-color: rgba(21, 64, 106, 0.15) !important;
    color: #15406A !important;
    border: 1px solid rgba(21, 64, 106, 0.24);
  }

  .badge.text-bg-danger {
    background-color: rgba(183, 61, 83, 0.12) !important;
    color: #8a2337 !important;
    border: 1px solid rgba(183, 61, 83, 0.2);
  }

  .badge.text-bg-warning {
    background-color: rgba(21, 64, 106, 0.16) !important;
    color: #15406A !important;
    border: 1px solid rgba(21, 64, 106, 0.24);
  }

  .table-warning {
    --bs-table-bg: rgba(188, 58, 83, 0.04);
  }

  .table-success-subtle,
  .table-danger-subtle {
    --bs-table-bg: #f1f6fc;
  }

  .form-control,
  .input-group-text {
    border-color: #d4dfeb;
  }

  #printPenawaranModal .modal-dialog {
    margin: 1rem auto;
  }

  #printPenawaranModal .modal-content {
    max-height: calc(100vh - 2rem);
  }

  #printPenawaranModal .modal-body {
    overflow-y: auto;
    max-height: calc(100vh - 210px);
  }

  .btn-primary {
    background-color: #15406A !important;
    border-color: #15406A !important;
  }

  .btn-primary:hover,
  .btn-primary:focus {
    background-color: #0f3358 !important;
    border-color: #0f3358 !important;
  }

  .btn-outline-primary {
    color: #15406A !important;
    border-color: #15406A !important;
  }

  .btn-outline-primary:hover,
  .btn-outline-primary:focus {
    background-color: #15406A !important;
    border-color: #15406A !important;
    color: #fff !important;
  }

  .btn-outline-secondary {
    color: #15406A !important;
    border-color: #15406A !important;
  }

  .btn-outline-secondary:hover,
  .btn-outline-secondary:focus {
    background-color: #15406A !important;
    border-color: #15406A !important;
    color: #fff !important;
  }

  .btn-deep-blue {
    background-color: #15406A !important;
    border-color: #15406A !important;
    color: #fff !important;
  }

  .btn-deep-blue:hover,
  .btn-deep-blue:focus {
    background-color: #0f3358 !important;
    border-color: #0f3358 !important;
    color: #fff !important;
  }

  .btn-outline-deep-blue {
    border-color: #15406A !important;
    color: #15406A !important;
  }

  .btn-outline-deep-blue:hover,
  .btn-outline-deep-blue:focus {
    border-color: #15406A !important;
    color: #15406A !important;
  }

  @media (max-width: 991.98px) {
    .pnw-card-code {
      font-size: 1.08rem;
    }

    .pnw-table td,
    .pnw-table thead th {
      font-size: 0.82rem;
    }
  }
</style>
@endpush
