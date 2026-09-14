@extends('layouts.app_admin')

@section('content_admin')
@php
  // Data dummy; ganti dengan data dari controller bila sudah tersedia
  $rencanaDefaults = collect([
    [
      'kode' => 'DOC-2025-01',
      'judul' => 'Kaji Ulang SOP Pemeriksaan Lapangan',
      'jadwal' => '18 Jan 2025',
      'penanggung_jawab' => 'Budi Santoso',
      'materi' => 'Evaluasi efektivitas SOP inspeksi, pembaruan daftar cek, dan kebutuhan pelatihan',
      'lampiran' => [
        ['nama' => 'Rencana Jadwal', 'url' => '#'],
        ['nama' => 'Daftar Materi', 'url' => '#'],
      ],
    ],
    [
      'kode' => 'DOC-2025-02',
      'judul' => 'Penjadwalan Audit Internal',
      'jadwal' => '24 Jan 2025',
      'penanggung_jawab' => 'Rina Wijaya',
      'materi' => 'Sinkronisasi jadwal audit internal & koordinasi reviewer eksternal',
      'lampiran' => [
        ['nama' => 'Draft Jadwal', 'url' => '#'],
      ],
    ],
  ]);

  $revisiDefaults = collect([
    [
      'dokumen' => 'SOP Pemeriksaan Lapangan',
      'versi' => 'v2.0',
      'analisis' => 'Temuan ketidaksesuaian pada tahapan pengecekan alat ukur',
      'perubahan' => 'Menambah checklist kalibrasi dan verifikasi alat ukur sebelum inspeksi',
      'paraf' => 'Budi Santoso',
      'tanggal' => '19 Jan 2025',
      'berkas' => '#',
    ],
    [
      'dokumen' => 'Form Laporan Inspeksi',
      'versi' => 'v1.3',
      'analisis' => 'Kolom verifikasi supervisor belum ada',
      'perubahan' => 'Menambah kolom tanda tangan supervisor & tanggal verifikasi',
      'paraf' => 'Rina Wijaya',
      'tanggal' => '20 Jan 2025',
      'berkas' => '#',
    ],
  ]);

  $hasilDefaults = collect([
    [
      'pertemuan' => 'Rapat Kaji Ulang Januari 2025',
      'notulensi' => 'Ringkasan pembahasan tentang penyesuaian SOP lapangan dan kebutuhan pelatihan ulang untuk teknisi junior.',
      'keputusan' => 'Menerbitkan dokumen revisi SOP versi 2.1 dan form laporan inspeksi versi 1.3.',
      'tindakan' => [
        ['aksi' => 'Sosialisasi SOP baru ke seluruh teknisi', 'pic' => 'Lead Teknisi', 'batas_waktu' => '25 Jan 2025', 'status' => 'On Track'],
        ['aksi' => 'Upload template form inspeksi revisi', 'pic' => 'QA Admin', 'batas_waktu' => '22 Jan 2025', 'status' => 'Proses'],
      ],
    ],
    [
      'pertemuan' => 'Review Distribusi Dokumen',
      'notulensi' => 'Menetapkan kanal distribusi digital dan penanggung jawab penerimaan di tiap unit.',
      'keputusan' => 'Gunakan portal internal dengan notifikasi email terjadwal.',
      'tindakan' => [
        ['aksi' => 'Aktifkan notifikasi email distribusi', 'pic' => 'IT Support', 'batas_waktu' => '23 Jan 2025', 'status' => 'Menunggu'],
      ],
    ],
  ]);

  $distribusiDefaults = collect([
    ['dokumen' => 'SOP Pemeriksaan Lapangan', 'versi' => '2.1', 'tujuan' => 'Tim Inspeksi Lapangan', 'metode' => 'Portal + Email', 'tanggal' => '20 Jan 2025', 'pic' => 'QA Admin', 'status' => 'Dikirim'],
    ['dokumen' => 'Form Laporan Inspeksi', 'versi' => '1.3', 'tujuan' => 'Supervisor Site', 'metode' => 'Portal', 'tanggal' => '21 Jan 2025', 'pic' => 'QA Admin', 'status' => 'Dikirim'],
    ['dokumen' => 'Notulensi Kaji Ulang Jan 2025', 'versi' => '-', 'tujuan' => 'Manajemen & QA', 'metode' => 'Email', 'tanggal' => '20 Jan 2025', 'pic' => 'Sekretariat', 'status' => 'Proses'],
  ]);

  $permintaanDefaults = collect([
    [
      'kode' => 'PMH-021',
      'pelanggan' => 'PT Sejahtera Abadi',
      'lokasi' => 'Plant Karawang',
      'parameter' => [
        ['nama_pelayanan' => 'KEBISINGAN SESAT', 'qty' => 4, 'checked' => false, 'harga' => 350000],
        ['nama_pelayanan' => 'PENCAHAYAAN UMUM – per 100m2', 'qty' => 2, 'checked' => true, 'harga' => 200000],
        [
          'nama_pelayanan' => 'IKLIM KERJA – ISBB',
          'qty' => 1,
          'checked' => false,
          'not_testable' => true,
          'reason' => 'Peralatan dan metode uji tidak sesuai spesifikasi',
          'harga' => 75000,
        ],
      ],
    ],
    [
      'kode' => 'PMH-022',
      'pelanggan' => 'CV Prima Teknik',
      'lokasi' => 'Workshop Bandung',
      'parameter' => [
        ['nama_pelayanan' => 'GETARAN MEKANIK', 'qty' => 3, 'checked' => true, 'harga' => 125000],
        ['nama_pelayanan' => 'IKLIM KERJA – ISBB & KECEPATAN ALIRAN UDARA', 'qty' => 1, 'checked' => true, 'harga' => 100000],
      ],
    ],
  ]);

  $rencana = isset($rencana_kajiulang) && $rencana_kajiulang instanceof \Illuminate\Support\Collection
    ? $rencana_kajiulang
    : $rencanaDefaults;
  $revisi = isset($revisi_kajiulang) && $revisi_kajiulang instanceof \Illuminate\Support\Collection
    ? $revisi_kajiulang
    : $revisiDefaults;
  $hasil = isset($hasil_kajiulang) && $hasil_kajiulang instanceof \Illuminate\Support\Collection
    ? $hasil_kajiulang
    : $hasilDefaults;
  $distribusi = isset($distribusi_kajiulang) && $distribusi_kajiulang instanceof \Illuminate\Support\Collection
    ? $distribusi_kajiulang
    : $distribusiDefaults;
  $permintaan = isset($permintaan_kajiulang) && $permintaan_kajiulang instanceof \Illuminate\Support\Collection
    ? $permintaan_kajiulang
    : $permintaanDefaults;
  $alasanTidakUji = [
    'Metode pengambilan contoh uji belum dikembangkan',
    'Peralatan dan metode uji tidak sesuai spesifikasi',
    'Alasan lain',
  ];

  $countRencana = $rencana->count();
  $countRevisi = $revisi->count();
  $countHasil = $hasil->count();
  $countDistribusi = $distribusi->count();

  $badgeStatus = function ($status) {
    return match (strtolower($status)) {
      'dikirim', 'on track', 'selesai' => 'text-bg-success',
      'proses', 'proses revisi' => 'text-bg-primary',
      'menunggu' => 'text-bg-warning',
      default => 'text-bg-secondary',
    };
  };
@endphp

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Kaji Ulang',
  'subtitle' => 'Tinjau kesiapan permintaan dan parameter sebelum lanjut ke penawaran.',
  'total' => $permintaan->count(),
])

<div class="card border-0 shadow-sm rounded-4 mb-4 kajiulang-search-card">
  <div class="card-body p-3 p-lg-4">
    <div class="row g-2">
      <div class="col-12 col-md-4">
        <div class="kajiulang-search-input-wrap">
          <i class="bi bi-search kajiulang-search-icon"></i>
          <input type="text" class="form-control form-control-sm kajiulang-search-input" placeholder="Cari berdasarkan kode pesanan" data-search-kode>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="kajiulang-search-input-wrap">
          <i class="bi bi-search kajiulang-search-icon"></i>
          <input type="text" class="form-control form-control-sm kajiulang-search-input" placeholder="Cari berdasarkan pelanggan" data-search-pelanggan>
        </div>
      </div>
      <div class="col-12 col-md-2 d-flex align-items-end">
        <button type="button" class="btn btn-outline-secondary w-100 btn-sm kajiulang-search-reset" data-search-reset><i class="bi bi-arrow-clockwise me-1"></i>Reset</button>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4 kajiulang-main-card">
  <div class="card-body p-0">
    <div class="d-flex justify-content-between align-items-center px-4 pt-4 mb-3">
      <div>
        <h5 class="mb-0 fw-semibold kajiulang-main-title">Kesiapan Permintaan & Parameter</h5>
      </div>
    </div>

    <div class="row g-3 px-3 pb-3" data-request-list>
        @forelse($permintaan as $req)
          @php
            $params = collect($req['parameter'] ?? []);
            $nonTestableCount = $params->where('not_testable', true)->count();
            $testableParams = $params->filter(fn ($param) => !($param['not_testable'] ?? false));
            $totalTestable = $testableParams->count();
            $checkedParam = $testableParams->where('checked', true)->count();
            $reasonComplete = $params->every(function ($param) {
              if (!($param['not_testable'] ?? false)) {
                return true;
              }
              if (($param['reason'] ?? '') === 'Alasan lain') {
                return !empty($param['other_reason'] ?? '');
              }
              return !empty($param['reason'] ?? '');
            });
            $isReady = ($totalTestable === 0 || $checkedParam === $totalTestable) && $reasonComplete;
            $tanggalPengajuan = $req['tanggal_pengajuan'] ?? $req['tanggal'] ?? '-';
            $accordionId = 'kajiulang-item-' . ($req['id'] ?? $req['kode'] ?? $loop->index);
            $accordionHeadingId = $accordionId . '-heading';
            $accordionCollapseId = $accordionId . '-collapse';
            $createdAtIso = $req['created_at'] ?? $req['tanggal_pengajuan'] ?? $req['tanggal'] ?? '';
            $catatanMp = trim((string) ($req['catatan_mp'] ?? ''));
            $catatanMt = trim((string) ($req['catatan_mt'] ?? ''));
          @endphp
        <div class="col-12">
          <div
            class="border rounded-4 p-0 shadow-sm bg-white h-100 kajiulang-request-card"
            data-request-card
            data-request-id="{{ $req['kode'] ?? '' }}"
            data-request-db-id="{{ $req['id'] ?? '' }}"
            data-kode="{{ strtolower($req['kode'] ?? '') }}"
            data-pelanggan="{{ strtolower($req['pelanggan'] ?? '') }}"
          >
            <h2 class="accordion-header" id="{{ $accordionHeadingId }}">
              <button
                class="kajiulang-accordion-btn collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $accordionCollapseId }}"
                aria-expanded="false"
                aria-controls="{{ $accordionCollapseId }}"
              >
                <div class="kajiulang-accordion-left">
                  <span class="kajiulang-accordion-kode">{{ $req['kode'] ?? '-' }}</span>
                  <span class="kajiulang-accordion-nama">{{ $req['pelanggan'] ?? '-' }}</span>
                </div>
                <div class="kajiulang-accordion-right">
                  <span class="kajiulang-accordion-time">
                    <i class="bi bi-clock"></i>
                    <span data-relative-time data-created-at="{{ $createdAtIso }}">-</span>
                  </span>
                  <i class="bi bi-chevron-down kajiulang-accordion-chevron"></i>
                </div>
              </button>
            </h2>
            <div
              id="{{ $accordionCollapseId }}"
              class="collapse"
              aria-labelledby="{{ $accordionHeadingId }}"
            >
            <div class="kajiulang-request-header">
              <div class="kajiulang-request-header-left">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                  <h6 class="mb-0 kajiulang-request-id">ID Pesanan: <strong>{{ $req['kode'] ?? '-' }}</strong></h6>
                </div>
                <div class="kajiulang-company-line mt-2">
                  <i class="bi bi-building me-1"></i>Nama Perusahaan: <strong>{{ $req['pelanggan'] ?? '-' }}</strong>
                </div>
                @if($catatanMp !== '' || $catatanMt !== '')
                  <div class="kajiulang-disposisi-notes">
                    @if($catatanMp !== '')
                      <div class="kajiulang-disposisi-note-item">
                        <span class="kajiulang-disposisi-note-label">Catatan MP</span>
                        <span class="kajiulang-disposisi-note-value">{{ $catatanMp }}</span>
                      </div>
                    @endif
                    @if($catatanMt !== '')
                      <div class="kajiulang-disposisi-note-item">
                        <span class="kajiulang-disposisi-note-label">Catatan MT</span>
                        <span class="kajiulang-disposisi-note-value">{{ $catatanMt }}</span>
                      </div>
                    @endif
                  </div>
                @endif
              </div>
              <div class="text-lg-end text-start mt-2 mt-lg-0">
                <div class="kajiulang-date-label">Tanggal Pengajuan</div>
                <div class="kajiulang-date-value">{{ $tanggalPengajuan }}</div>
                <span class="badge mt-2 {{ $isReady ? 'text-bg-success' : 'text-bg-warning' }}" data-ready-badge data-request="{{ $req['kode'] ?? '' }}">
                  {{ $isReady ? 'Sudah di kaji ulang' : 'Belum di kaji ulang' }}
                </span>
              </div>
            </div>
            <div class="table-responsive kajiulang-table-wrap">
              <table class="table table-sm align-middle mb-0 kajiulang-table">
                <thead>
                  <tr>
                    <th style="width:18%;">Kategori</th>
                    <th style="width:32%;">Parameter</th>
                    <th class="text-center" style="width:80px;">Qty</th>
                    <th class="text-end" style="width:120px;">Harga</th>
                    <th class="text-center" style="width:220px;">Cek / Alasan</th>
                  </tr>
                  </thead>
                  <tbody>
                    @foreach($params as $param)
                    @php
                      $notTestable = $param['not_testable'] ?? false;
                      $rawReason = $param['reason'] ?? '';
                      $otherReason = $param['other_reason'] ?? '';
                      $reason = $rawReason;
                      if ($notTestable && $rawReason && !in_array($rawReason, $alasanTidakUji, true)) {
                        $reason = 'Alasan lain';
                        $otherReason = $rawReason;
                      }
                      $paramDbId = $param['id'] ?? null;
                      $paramId = ($req['id'] ?? $req['kode'] ?? 'req') . '-' . ($paramDbId ?? $loop->index);
                      $kategori = $param['kategori']
                        ?? $param['category']
                        ?? $param['kategori_pelayanan']
                        ?? $param['nama_kategori']
                        ?? '-';
                      $namaPelayanan = $param['nama_pelayanan'] ?? $param['nama'] ?? '-';
                      $qty = $param['qty'] ?? 1;
                      $harga = $param['harga'] ?? 0;
                      $paramSub = $param['deskripsi'] ?? $param['keterangan'] ?? '';
                      $qtySatuan = trim((string)($param['satuan'] ?? $param['unit'] ?? ''));
                    @endphp
                    <tr>
                      <td class="small kajiulang-cell-category">{{ $kategori }}</td>
                      <td class="small kajiulang-cell-parameter">
                        <div class="kajiulang-param-name">{{ $namaPelayanan }}</div>
                        @if(!empty($paramSub))
                          <div class="kajiulang-param-sub">{{ $paramSub }}</div>
                        @endif
                      </td>
                      <td class="text-center kajiulang-cell-qty">{{ $qty }}{{ $qtySatuan ? ' ' . $qtySatuan : '' }}</td>
                      <td class="text-end kajiulang-cell-price">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                      <td class="text-center">
                        <div
                          class="d-flex align-items-center justify-content-center gap-2 kajiulang-action-wrap"
                          data-param-control
                          data-param-id="{{ $paramId }}"
                          data-param-db-id="{{ $paramDbId }}"
                          data-request-id="{{ $req['kode'] ?? '' }}"
                          data-not-testable="{{ $notTestable ? 'true' : 'false' }}"
                        >
                          <input
                            type="checkbox"
                            class="form-check-input custom-check-outline"
                            data-param-checkbox
                            data-param-id="{{ $paramId }}"
                            data-param-db-id="{{ $paramDbId }}"
                            data-request-id="{{ $req['kode'] ?? '' }}"
                            {{ ($param['checked'] ?? false) && !$notTestable ? 'checked' : '' }}
                          >
                          <button
                            type="button"
                            class="btn btn-sm d-inline-flex align-items-center justify-content-center kajiulang-toggle-not-testable {{ $notTestable ? 'btn-danger' : 'btn-outline-danger' }}"
                            data-toggle-not-testable
                            data-param-id="{{ $paramId }}"
                            data-request-id="{{ $req['kode'] ?? '' }}"
                          >
                            &times;
                          </button>
                        </div>
                        <div
                          class="mt-2 small text-start kajiulang-reason-card {{ $notTestable ? '' : 'd-none' }}"
                          data-not-testable-wrapper
                          data-param-id="{{ $paramId }}"
                        >
                          <div class="kajiulang-reason-label">Pilih Alasan Penolakan</div>
                          <select
                            class="form-select form-select-sm mt-1 kajiulang-reason-select"
                            data-not-testable-select
                            data-param-id="{{ $paramId }}"
                            data-request-id="{{ $req['kode'] ?? '' }}"
                          >
                            <option value="">Pilih alasan</option>
                            @foreach($alasanTidakUji as $alasan)
                              <option value="{{ $alasan }}" {{ $reason === $alasan ? 'selected' : '' }}>{{ $alasan }}</option>
                            @endforeach
                          </select>
                          <div class="kajiulang-reason-label mt-2">Komentar Tambahan</div>
                          <textarea
                            class="form-control form-control-sm mt-1 kajiulang-reason-textarea {{ $reason === 'Alasan lain' ? '' : 'd-none' }}"
                            placeholder="Berikan alasan mendetail..."
                            rows="2"
                            data-not-testable-other
                            data-param-id="{{ $paramId }}"
                            data-request-id="{{ $req['kode'] ?? '' }}"
                          >{{ $reason === 'Alasan lain' ? $otherReason : '' }}</textarea>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="kajiulang-request-footer">
              <div>
                <div class="small text-muted mb-1" data-count-text data-request="{{ $req['kode'] ?? '' }}">
                  {{ $checkedParam }} dari {{ $totalTestable }} parameter dapat diuji sudah dicentang
                </div>
                <div class="small text-muted" data-non-testable-text data-request="{{ $req['kode'] ?? '' }}">
                  @if($nonTestableCount > 0)
                    Parameter tidak bisa diuji: {{ $nonTestableCount }}
                  @else
                    Semua parameter dapat diuji
                  @endif
                </div>
                <div class="small text-muted mt-2 kajiulang-item-count">
                  Menampilkan {{ $params->count() }} item pesanan untuk diverifikasi.
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                <button
                  type="button"
                  class="btn btn-sm btn-kajiulang-send {{ $isReady ? '' : 'disabled' }}"
                  data-send-penawaran
                  data-request="{{ $req['kode'] ?? '' }}"
                  data-request-db-id="{{ $req['id'] ?? '' }}"
                  data-send-url="{{ !empty($req['id']) ? route('superadmin.kajiulang.send-penawaran', $req['id']) : '' }}"
                >
                  <i class="bi bi-send-fill me-1"></i>Kirim ke Admin untuk Penawaran
                </button>
              </div>
            </div>
            </div>
          </div>
        </div>
        @empty
        <div class="col-12 text-center text-muted py-3" data-empty-permintaan>Belum ada permintaan pelanggan.</div>
      @endforelse
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
  .kajiulang-search-card,
  .kajiulang-main-card {
    font-family: 'Poppins', sans-serif;
    font-size: 0.92rem;
    border: 1px solid #dfe6f1;
    background: #f8fafd;
  }

  .kajiulang-search-label {
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    color: #7b8fa9 !important;
  }

  .kajiulang-search-input {
    border-radius: 11px;
    border-color: #d8e2ef;
    min-height: 38px;
    font-size: 0.82rem;
    padding-left: 2.05rem;
  }

  .kajiulang-search-input-wrap {
    position: relative;
  }

  .kajiulang-search-icon {
    position: absolute;
    left: 0.78rem;
    top: 50%;
    transform: translateY(-50%);
    color: #8da2ba;
    font-size: 0.82rem;
    pointer-events: none;
  }

  .kajiulang-search-input:focus {
    border-color: #15406a;
    box-shadow: 0 0 0 0.14rem rgba(21, 64, 106, 0.15);
  }

  .kajiulang-search-reset {
    min-height: 38px;
    border-radius: 11px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #15406a;
    border-color: #cfd9e8;
  }

  .kajiulang-main-title {
    color: #15406a;
    font-size: 0.95rem;
  }

  .kajiulang-overall-badge {
    font-size: 0.68rem;
    font-weight: 600;
    border-radius: 999px;
    padding: 0.38rem 0.66rem;
  }

  .kajiulang-request-card {
    border-color: #dfe7f2 !important;
    border-radius: 14px !important;
    overflow: hidden;
  }

  .kajiulang-accordion-btn {
    width: 100%;
    border: 1px solid #15406A;
    background: #15406A;
    color: #fff;
    border-radius: 14px;
    padding: 0.82rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    transition: all 0.25s ease;
  }

  .kajiulang-accordion-btn:hover {
    background: #11365a;
    border-color: #11365a;
  }

  .kajiulang-accordion-left {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.15rem;
    min-width: 0;
  }

  .kajiulang-accordion-kode {
    font-size: 0.98rem;
    font-weight: 700;
    line-height: 1.2;
  }

  .kajiulang-accordion-nama {
    font-size: 0.84rem;
    font-weight: 400;
    color: #fff;
    opacity: 0.96;
  }

  .kajiulang-accordion-right {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    white-space: nowrap;
  }

  .kajiulang-accordion-time {
    display: inline-flex;
    align-items: center;
    gap: 0.34rem;
    font-size: 10px;
    font-weight: 600;
    color: #fff;
  }

  .kajiulang-accordion-chevron {
    font-size: 0.9rem;
    color: #fff;
    transition: transform 0.25s ease;
  }

  .kajiulang-accordion-btn:not(.collapsed) .kajiulang-accordion-chevron {
    transform: rotate(180deg);
  }

  .kajiulang-request-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.95rem 1.15rem;
    border-bottom: 1px solid #ebf0f7;
    background: #fff;
  }

  .kajiulang-draft-chip {
    background: #fff0e8;
    color: #f16522;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.56rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
  }

  .kajiulang-request-id {
    color: #102f53;
    font-size: 0.94rem;
    font-weight: 700;
  }

  .kajiulang-company-line {
    color: #2a4668;
    font-size: 0.8rem;
    font-weight: 500;
  }

  .kajiulang-disposisi-notes {
    margin-top: 0.55rem;
    display: grid;
    gap: 0.4rem;
  }

  .kajiulang-disposisi-note-item {
    display: grid;
    gap: 0.15rem;
    padding: 0.42rem 0.5rem;
    border-radius: 8px;
    border: 1px dashed #d5dfed;
    background: #f7fafe;
  }

  .kajiulang-disposisi-note-label {
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #607895;
  }

  .kajiulang-disposisi-note-value {
    font-size: 0.76rem;
    line-height: 1.35;
    color: #17395d;
    white-space: pre-line;
  }

  .kajiulang-date-label {
    font-size: 0.68rem;
    color: #7e90a9;
  }

  .kajiulang-date-value {
    color: #15406a;
    font-size: 0.88rem;
    font-weight: 700;
    line-height: 1.2;
  }

  .kajiulang-table thead th {
    background: #f2f6fb;
    color: #6880a0;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-size: 0.6rem;
    border-bottom: 1px solid #e5edf7;
    padding: 0.68rem 0.9rem;
    font-weight: 700;
  }

  .kajiulang-table tbody td {
    border-color: #edf1f7;
    padding: 0.72rem 0.9rem;
    font-size: 0.8rem;
    vertical-align: top;
    color: #18395e;
  }

  .kajiulang-cell-category {
    font-weight: 600;
    color: #163b61;
  }

  .kajiulang-param-name {
    color: #102f52;
    font-weight: 700;
    line-height: 1.3;
  }

  .kajiulang-param-sub {
    font-size: 0.7rem;
    color: #7f92ab;
    margin-top: 0.12rem;
  }

  .kajiulang-cell-qty {
    font-weight: 500;
  }

  .kajiulang-cell-price {
    color: #102f52;
    font-weight: 700;
  }

  .kajiulang-action-wrap {
    min-height: 28px;
  }

  .custom-check-outline {
    width: 26px;
    height: 26px;
    margin: 0;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    appearance: none;
    -webkit-appearance: none;
    border: 1px solid #dbe4f1;
    border-radius: 999px;
    background-color: #eef3fa;
    background-position: center;
    background-repeat: no-repeat;
    background-size: 56%;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%2395a4b8' stroke-width='2'%3e%3cpath d='M3 8.5 6.5 12l6.5-8'/%3e%3c/svg%3e");
    transition: all 0.15s ease;
  }

  .custom-check-outline:checked {
    background-color: #16b783;
    border-color: #16b783;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%23ffffff' stroke-width='2'%3e%3cpath d='M3 8.5 6.5 12l6.5-8'/%3e%3c/svg%3e");
  }

  .custom-check-outline:focus {
    box-shadow: 0 0 0 0.16rem rgba(21, 64, 106, 0.18);
    outline: none;
  }

  .custom-check-outline:disabled {
    opacity: 0.55;
    cursor: not-allowed;
  }

  .kajiulang-toggle-not-testable {
    width: 26px;
    height: 26px;
    border-radius: 999px;
    padding: 0;
    line-height: 1;
    font-size: 0.95rem;
  }

  .kajiulang-toggle-not-testable.btn-outline-danger {
    border-color: #dbe4f1;
    background: #eef3fa;
    color: #97a8be;
  }

  .kajiulang-toggle-not-testable.btn-danger {
    border-color: #ef4444;
    background: #ef4444;
    color: #fff;
  }

  .kajiulang-reason-card {
    border: 1px solid #f4caca;
    border-radius: 12px;
    background: #fff;
    padding: 0.7rem;
    max-width: 260px;
    margin-inline: auto;
  }

  .kajiulang-reason-label {
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #ef4444;
    font-size: 0.6rem;
    font-weight: 700;
  }

  .kajiulang-reason-select,
  .kajiulang-reason-textarea {
    border-color: #dbe4f1;
    border-radius: 9px;
    font-size: 0.78rem;
    color: #1e4369;
  }

  .kajiulang-reason-textarea {
    min-height: 76px;
    resize: vertical;
  }

  .kajiulang-request-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 0.85rem;
    border-top: 1px solid #ebf0f7;
    background: #fff;
    padding: 0.85rem 1.15rem 1rem;
  }

  .kajiulang-item-count {
    font-size: 0.77rem;
    color: #7388a6 !important;
  }

  .btn-kajiulang-draft {
    border: 1px solid #d2deee;
    background: #fff;
    color: #15406a;
    border-radius: 11px;
    padding: 0.5rem 1.1rem;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .btn-kajiulang-draft:disabled {
    opacity: 0.85;
    color: #365a7f;
  }

  .btn-kajiulang-send {
    background-color: #15406a;
    border-color: #15406a;
    color: #fff;
    border-radius: 11px;
    padding: 0.5rem 1.1rem;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .btn-kajiulang-send:hover,
  .btn-kajiulang-send:focus {
    background-color: #0f3355;
    border-color: #0f3355;
    color: #fff;
  }

  .btn-kajiulang-send.disabled,
  .btn-kajiulang-send:disabled {
    background-color: #15406a;
    border-color: #15406a;
    color: #fff;
    opacity: 0.55;
  }

  @media (max-width: 991.98px) {
    .kajiulang-request-header {
      flex-direction: column;
      gap: 0.55rem;
    }

    .kajiulang-request-footer {
      flex-direction: column;
      align-items: stretch;
    }

    .kajiulang-request-footer > div:last-child {
      justify-content: flex-start !important;
    }
  }

  @media (max-width: 767.98px) {
    .kajiulang-table-wrap {
      overflow-x: auto;
    }

    .kajiulang-table {
      min-width: 860px;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  (() => {
    const updateRequestStatus = (reqId) => {
      const controls = document.querySelectorAll(`[data-param-control][data-request-id="${reqId}"]`);
      let totalTestable = 0;
      let checked = 0;
      let nonTestableCount = 0;
      let reasonsValid = true;

      controls.forEach((ctrl) => {
        const paramId = ctrl.getAttribute('data-param-id');
        const isNonTestable = ctrl.getAttribute('data-not-testable') === 'true';
        const checkbox = document.querySelector(`[data-param-checkbox][data-param-id="${paramId}"]`);
        const reasonSelect = document.querySelector(`[data-not-testable-select][data-param-id="${paramId}"]`);
        const otherInput = document.querySelector(`[data-not-testable-other][data-param-id="${paramId}"]`);

        if (isNonTestable) {
          nonTestableCount += 1;
          if (!reasonSelect || !reasonSelect.value) {
            reasonsValid = false;
          } else if (reasonSelect.value === 'Alasan lain') {
            if (!otherInput || !otherInput.value.trim()) {
              reasonsValid = false;
            }
          }
        } else {
          totalTestable += 1;
          if (checkbox && checkbox.checked) {
            checked += 1;
          }
        }
      });

      const nonTestableText = document.querySelector(`[data-non-testable-text][data-request="${reqId}"]`);
      const badge = document.querySelector(`[data-ready-badge][data-request="${reqId}"]`);
      const countText = document.querySelector(`[data-count-text][data-request="${reqId}"]`);
      const readyMsg = document.querySelector(`[data-ready-message][data-request="${reqId}"]`);
      const sendBtn = document.querySelector(`[data-send-penawaran][data-request="${reqId}"]`);

      const isReady = (totalTestable === 0 || checked === totalTestable) && reasonsValid;

      if (badge) {
        badge.classList.toggle('text-bg-success', isReady);
        badge.classList.toggle('text-bg-warning', !isReady);
        badge.textContent = isReady ? 'Sudah di kaji ulang' : 'Belum di kaji ulang';
      }

      if (countText) {
        countText.textContent = `${checked} dari ${totalTestable} parameter dapat diuji sudah dicentang`;
      }

      if (nonTestableText) {
        nonTestableText.textContent = nonTestableCount > 0
          ? `Parameter tidak bisa diuji: ${nonTestableCount}`
          : 'Semua parameter dapat diuji';
      }

      if (readyMsg) {
        readyMsg.classList.toggle('d-none', !isReady);
      }

      if (sendBtn) {
        sendBtn.classList.toggle('disabled', !isReady);
      }

      return isReady;
    };

    const updateOverall = () => {
      const badges = document.querySelectorAll('[data-ready-badge]');
      const overall = document.querySelector('[data-overall-ready]');
      if (!overall || badges.length === 0) return;

      const allReady = Array.from(badges).every((badge) => badge.classList.contains('text-bg-success'));
      const someReady = Array.from(badges).some((badge) => badge.classList.contains('text-bg-success'));

      if (allReady) {
        overall.textContent = 'Semua permintaan siap';
        overall.className = 'badge text-bg-success';
      } else if (someReady) {
        overall.textContent = 'Sebagian siap';
        overall.className = 'badge text-bg-warning';
      } else {
        overall.textContent = 'Menunggu cek/alasan';
        overall.className = 'badge text-bg-secondary';
      }
    };

    const setNotTestable = (paramId, reqId, active) => {
      const ctrl = document.querySelector(`[data-param-control][data-param-id="${paramId}"]`);
      const checkbox = document.querySelector(`[data-param-checkbox][data-param-id="${paramId}"]`);
      const toggle = document.querySelector(`[data-toggle-not-testable][data-param-id="${paramId}"]`);
      const wrapper = document.querySelector(`[data-not-testable-wrapper][data-param-id="${paramId}"]`);

      if (ctrl) ctrl.setAttribute('data-not-testable', active ? 'true' : 'false');
      if (checkbox) {
        checkbox.checked = active ? false : checkbox.checked;
      }
      if (toggle) {
        toggle.classList.toggle('btn-outline-danger', !active);
        toggle.classList.toggle('btn-danger', active);
      }
      if (wrapper) {
        wrapper.classList.toggle('d-none', !active);
        if (!active) {
          const select = wrapper.querySelector('[data-not-testable-select]');
          const otherInput = wrapper.querySelector('[data-not-testable-other]');
          if (select) select.value = '';
          if (otherInput) {
            otherInput.value = '';
            otherInput.classList.add('d-none');
          }
        }
      }

      updateRequestStatus(reqId);
      updateOverall();
    };

    document.querySelectorAll('[data-param-checkbox]').forEach((cb) => {
      cb.addEventListener('change', () => {
        const reqId = cb.getAttribute('data-request-id');
        const paramId = cb.getAttribute('data-param-id');
        const ctrl = document.querySelector(`[data-param-control][data-param-id="${paramId}"]`);
        const isNonTestable = ctrl && ctrl.getAttribute('data-not-testable') === 'true';

        if (cb.checked && isNonTestable) {
          setNotTestable(paramId, reqId, false);
          return;
        }

        updateRequestStatus(reqId);
        updateOverall();
      });
    });

    document.querySelectorAll('[data-toggle-not-testable]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const reqId = btn.getAttribute('data-request-id');
        const paramId = btn.getAttribute('data-param-id');
        const ctrl = document.querySelector(`[data-param-control][data-param-id="${paramId}"]`);
        const isActive = ctrl && ctrl.getAttribute('data-not-testable') === 'true';
        setNotTestable(paramId, reqId, !isActive);
      });
    });

    document.querySelectorAll('[data-not-testable-select]').forEach((select) => {
      select.addEventListener('change', () => {
        const reqId = select.getAttribute('data-request-id');
        const paramId = select.getAttribute('data-param-id');
        const otherInput = document.querySelector(`[data-not-testable-other][data-param-id="${paramId}"]`);
        const needsOther = select.value === 'Alasan lain';
        if (otherInput) {
          otherInput.classList.toggle('d-none', !needsOther);
          if (!needsOther) otherInput.value = '';
        }
        updateRequestStatus(reqId);
        updateOverall();
      });
    });

    document.querySelectorAll('[data-not-testable-other]').forEach((input) => {
      input.addEventListener('input', () => {
        const reqId = input.getAttribute('data-request-id');
        updateRequestStatus(reqId);
        updateOverall();
      });
    });

    document.querySelectorAll('[data-send-penawaran]').forEach((btn) => {
      btn.addEventListener('click', async () => {
        if (btn.classList.contains('disabled')) return;

        const reqId = btn.getAttribute('data-request');
        const card = btn.closest('[data-request-card]');
        const sendUrl = btn.getAttribute('data-send-url');
        if (!card || !sendUrl) return;

        const controls = card.querySelectorAll('[data-param-control]');
        const parameters = Array.from(controls).map((ctrl) => {
          const paramId = ctrl.getAttribute('data-param-id');
          const paramDbId = ctrl.getAttribute('data-param-db-id');
          const isNonTestable = ctrl.getAttribute('data-not-testable') === 'true';
          const checkbox = card.querySelector(`[data-param-checkbox][data-param-id="${paramId}"]`);
          const reasonSelect = card.querySelector(`[data-not-testable-select][data-param-id="${paramId}"]`);
          const otherInput = card.querySelector(`[data-not-testable-other][data-param-id="${paramId}"]`);

          return {
            id: Number(paramDbId),
            checked: Boolean(checkbox?.checked),
            not_testable: isNonTestable,
            reason: reasonSelect?.value || '',
            other_reason: otherInput?.value || '',
          };
        }).filter((item) => Number.isFinite(item.id));

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const defaultBtnLabel = btn.getAttribute('data-default-label') || btn.innerHTML;
        btn.setAttribute('data-default-label', defaultBtnLabel);
        btn.classList.add('disabled');
        btn.textContent = 'Mengirim...';

        try {
          const response = await fetch(sendUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ parameters }),
          });

          const result = await response.json().catch(() => ({}));

          if (!response.ok) {
            throw new Error(result.message || 'Gagal mengirim ke penawaran.');
          }

          if (window.Swal) {
            await Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: result.message || 'Permohonan dikirim ke admin penawaran.',
              timer: 1200,
              showConfirmButton: false,
            });
          } else {
            alert(result.message || 'Permohonan dikirim ke admin penawaran.');
          }
          window.location.reload();
        } catch (err) {
          btn.classList.remove('disabled');
          btn.innerHTML = btn.getAttribute('data-default-label') || 'Kirim ke Admin untuk Penawaran';

          if (window.Swal) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: err.message || 'Terjadi kesalahan.',
            });
          } else {
            alert(err.message || 'Terjadi kesalahan.');
          }
        }
      });
    });

    const formatRelativeTime = (input) => {
      if (!input) return '-';
      const d = new Date(input);
      if (Number.isNaN(d.getTime())) return '-';
      const diff = Math.max(0, Math.floor((Date.now() - d.getTime()) / 1000));
      if (diff < 60) return 'baru saja';
      if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`;
      if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`;
      if (diff < 2592000) return `${Math.floor(diff / 86400)} hari yang lalu`;
      if (diff < 31536000) return `${Math.floor(diff / 2592000)} bulan yang lalu`;
      return `${Math.floor(diff / 31536000)} tahun yang lalu`;
    };

    const updateRelativeTimes = () => {
      document.querySelectorAll('[data-relative-time]').forEach((el) => {
        const createdAt = el.getAttribute('data-created-at');
        el.textContent = formatRelativeTime(createdAt);
      });
    };

    // Initial state
    document.querySelectorAll('[data-request-card]').forEach((card) => {
      const reqId = card.getAttribute('data-request-id');
      updateRequestStatus(reqId);
    });
    updateRelativeTimes();
    updateOverall();

    const kodeInput = document.querySelector('[data-search-kode]');
    const pelangganInput = document.querySelector('[data-search-pelanggan]');
    const resetBtn = document.querySelector('[data-search-reset]');
    const cards = document.querySelectorAll('[data-request-card]');

    const filterCards = () => {
      const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
      const pelangganVal = (pelangganInput?.value || '').toLowerCase().trim();
      let visible = 0;

      cards.forEach((card) => {
        const kode = card.getAttribute('data-kode') || '';
        const pelanggan = card.getAttribute('data-pelanggan') || '';
        const matchKode = !kodeVal || kode.includes(kodeVal);
        const matchPelanggan = !pelangganVal || pelanggan.includes(pelangganVal);
        const show = matchKode && matchPelanggan;
        card.classList.toggle('d-none', !show);
        if (show) visible += 1;
      });

    };

    kodeInput?.addEventListener('input', filterCards);
    pelangganInput?.addEventListener('input', filterCards);
    resetBtn?.addEventListener('click', () => {
      if (kodeInput) kodeInput.value = '';
      if (pelangganInput) pelangganInput.value = '';
      filterCards();
    });

    filterCards();
  })();
</script>
@endpush
