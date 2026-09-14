@extends('layouts.app_admin')

@section('content_admin')
@php
  // Contoh data; ganti dengan data nyata dari controller bila sudah tersedia.
  $disposisiMp = collect([
    [
      'kode' => 'PMH-001',
      'pelanggan' => 'PT Maju Jaya',
      'penanggung_jawab' => 'Rina Pratama',
      'email_perusahaan' => 'admin@majujaya.co.id',
      'telepon_perusahaan' => '0812-3456-7890',
      'alamat_perusahaan' => 'Jl. Industri No. 10, Surabaya',
      'penandatangan_sama' => true,
      'penandatangan_nama' => '',
      'penandatangan_jabatan' => '',
      'jenis' => 'K3 Listrik',
      'tahapan' => 'Review Dokumen',
      'status' => 'Menunggu MP',
      'catatan' => 'Cek kelengkapan dokumen awal',
      'tanggal' => '05 Jan 2025',
      'acc' => 'Menunggu Persetujuan MP',
      'acc_mp' => false,
      'dokumen' => [
        ['nama' => 'Surat Permintaan', 'url' => '#'],
        ['nama' => 'KTP Penanggung Jawab', 'url' => '#'],
        ['nama' => 'Billing Code', 'url' => '#'],
      ],
      'parameter' => [
        ['nama' => 'Pengujian Kebisingan', 'qty' => 2, 'harga_satuan' => 1800000],
        ['nama' => 'Pengujian Pencahayaan', 'qty' => 1, 'harga_satuan' => 900000],
      ],
    ],
    [
      'kode' => 'PMH-004',
      'pelanggan' => 'UD Membangun',
      'penanggung_jawab' => 'Dimas Arya',
      'email_perusahaan' => 'info@udmembangun.com',
      'telepon_perusahaan' => '0821-555-2233',
      'alamat_perusahaan' => 'Jl. Raya Kebonagung No. 5, Sidoarjo',
      'penandatangan_sama' => false,
      'penandatangan_nama' => 'Nadia Lestari',
      'penandatangan_jabatan' => 'Direktur',
      'jenis' => 'Kalibrasi Alat',
      'tahapan' => 'Penjadwalan',
      'status' => 'Menunggu MP',
      'catatan' => 'Jadwalkan kunjungan lapangan',
      'tanggal' => '10 Jan 2025',
      'acc' => 'Persetujuan MP',
      'acc_mp' => true,
      'dokumen' => [
        ['nama' => 'Surat Permintaan', 'url' => '#'],
        ['nama' => 'Dokumen Teknis Alat', 'url' => '#'],
      ],
      'parameter' => [
        ['nama' => 'Kalibrasi Alat Ukur', 'qty' => 1, 'harga_satuan' => 2500000],
      ],
    ],
  ]);

  $disposisiMt = collect([
    [
      'kode' => 'PMH-002',
      'pelanggan' => 'CV Sinar Abadi',
      'penanggung_jawab' => 'Agus Hidayat',
      'email_perusahaan' => 'admin@sinarabadi.co.id',
      'telepon_perusahaan' => '0857-2211-8899',
      'alamat_perusahaan' => 'Jl. Kertajaya Indah No. 12, Surabaya',
      'penandatangan_sama' => true,
      'penandatangan_nama' => '',
      'penandatangan_jabatan' => '',
      'jenis' => 'Laboratorium Kimia',
      'tahapan' => 'Penunjukan Analis',
      'status' => 'Menunggu MT',
      'catatan' => 'Tetapkan analis utama',
      'tanggal' => '08 Jan 2025',
      'acc' => 'Menunggu Persetujuan MT',
      'acc_mt' => false,
      'dokumen' => [
        ['nama' => 'Berita Acara Verifikasi', 'url' => '#'],
        ['nama' => 'Billing Code', 'url' => '#'],
      ],
      'parameter' => [
        ['nama' => 'Pengujian Kimia Air', 'qty' => 2, 'harga_satuan' => 1200000],
        ['nama' => 'Pengujian Logam Berat', 'qty' => 1, 'harga_satuan' => 1500000],
      ],
    ],
    [
      'kode' => 'PMH-003',
      'pelanggan' => 'PT Pelita Nusantara',
      'penanggung_jawab' => 'Lia Nirmala',
      'email_perusahaan' => 'cs@pelitanusantara.id',
      'telepon_perusahaan' => '0813-7777-9090',
      'alamat_perusahaan' => 'Jl. Gatot Subroto No. 18, Gresik',
      'penandatangan_sama' => false,
      'penandatangan_nama' => 'Fajar Wijaya',
      'penandatangan_jabatan' => 'Manajer Operasional',
      'jenis' => 'Audit Keselamatan',
      'tahapan' => 'Finalisasi',
      'status' => 'Menunggu MT',
      'catatan' => 'Laporan audit siap dikirim',
      'tanggal' => '03 Jan 2025',
      'acc' => 'Persetujuan MT',
      'acc_mt' => true,
      'dokumen' => [
        ['nama' => 'Laporan Hasil Audit', 'url' => '#'],
      ],
      'parameter' => [
        ['nama' => 'Audit SMK3', 'qty' => 1, 'harga_satuan' => 3500000],
      ],
    ],
  ]);

  // gunakan data dari controller jika ada
  if (isset($disposisi_mp) && $disposisi_mp instanceof \Illuminate\Support\Collection) {
    $disposisiMp = $disposisi_mp;
  }
  if (isset($disposisi_mt) && $disposisi_mt instanceof \Illuminate\Support\Collection) {
    $disposisiMt = $disposisi_mt;
  }

  $disposisiMp = $disposisiMp->where('acc_mp', false)->values();
  $disposisiMt = $disposisiMt->where('acc_mt', false)->values();
  $currentRole = auth()->user()->role ?? 'superadmin';
  $isSuperadminDisposisi = $currentRole === 'superadmin';
  $showMpPanel = $currentRole !== 'mt';
  $showMtPanel = $currentRole === 'mt';

  $countPendingMp = $disposisiMp->count();
  $countPendingMt = $disposisiMt->count();
  $countApprovedMp = 0;
  $countApprovedMt = 0;

  $badgeClass = function ($status) {
    return match (strtolower($status)) {
      'selesai', 'selesai mt', 'selesai mp' => 'text-bg-success',
      'diproses', 'diproses mt', 'diproses mp' => 'text-bg-primary',
      'menunggu', 'menunggu mt', 'menunggu mp' => 'text-bg-warning',
      default => 'text-bg-secondary',
    };
  };
@endphp

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Disposisi ke MP dan MT',
  'subtitle' => 'Monitoring disposisi permohonan.',
  'total' => $countPendingMp + $countPendingMt,
])

<!-- <div class="row g-3 mb-4">
  <div class="col-md-3 col-6">
    <div class="border rounded-4 p-3 shadow-sm h-100 bg-white">
      <div class="text-muted small mb-1">Menunggu MP</div>
      <div class="h5 mb-0">{{ $countPendingMp }} permohonan</div>
      <div class="text-success small">Prioritas Manajer Puncak</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="border rounded-4 p-3 shadow-sm h-100 bg-white">
      <div class="text-muted small mb-1">Menunggu MT</div>
      <div class="h5 mb-0">{{ $countPendingMt }} permohonan</div>
      <div class="text-primary small">Prioritas Manajer Teknis</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="border rounded-4 p-3 shadow-sm h-100 bg-white">
      <div class="text-muted small mb-1">Sudah Disetujui MP</div>
      <div class="h5 mb-0">{{ $countApprovedMp }} permohonan</div>
      <div class="text-muted small">Disetujui Manajer Puncak</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="border rounded-4 p-3 shadow-sm h-100 bg-white">
      <div class="text-muted small mb-1">Sudah Disetujui MT</div>
      <div class="h5 mb-0">{{ $countApprovedMt }} permohonan</div>
      <div class="text-muted small">Disetujui Manajer Teknis</div>
    </div>
  </div>
</div> -->


<div class="disposisi-shell">
  <div class="disposisi-toolbar">
    @if($isSuperadminDisposisi)
      <div class="btn-group disposisi-role-switch" role="group" aria-label="Pilih role disposisi">
        <button type="button" class="btn btn-deep-blue" data-role-switch="mp">
          Manajer Puncak
          <span class="count-badge">{{ $countPendingMp }}</span>
        </button>
        <button type="button" class="btn btn-outline-deep-blue" data-role-switch="mt">
          Manajer Teknis
          <span class="count-badge">{{ $countPendingMt }}</span>
        </button>
      </div>
    @else
      <span class="badge disposisi-role-badge">
        {{ $currentRole === 'mp' ? 'Persetujuan MP' : 'Persetujuan MT' }}
      </span>
    @endif
  </div>

  <div class="disposisi-search-wrap">
    <div class="disposisi-search-input">
      <i class="bi bi-search" aria-hidden="true"></i>
      <input type="text" class="form-control" placeholder="Cari ID Pesanan, Nama Perusahaan, atau status..." data-search-global>
    </div>
    <button type="button" class="btn btn-outline-secondary disposisi-reset-btn" data-search-reset>Reset</button>
  </div>

  <div @class(['d-none' => !$showMpPanel]) data-role-panel="mp">
    <div class="row g-3" data-role-grid>
      @forelse($disposisiMp as $item)
        <div
          class="col-12 col-md-6 col-xl-4"
          data-disposisi-card
          data-kode="{{ strtolower($item['kode'] ?? '') }}"
          data-pelanggan="{{ strtolower($item['pelanggan'] ?? '') }}"
          data-status="menunggu persetujuan"
        >
          <div class="disposisi-item-card">
            <div class="disposisi-item-head">
              <div class="disposisi-item-icon">
                <i class="bi bi-file-earmark-text"></i>
              </div>
              <span class="disposisi-status-badge">Menunggu Persetujuan</span>
            </div>
            @php
              $buktiDoc = collect($item['dokumen'] ?? [])->first(fn ($doc) => !empty($doc['url']));
            @endphp

            <div class="disposisi-label">ID PESANAN</div>
            <div class="disposisi-code">{{ $item['kode'] ?? '-' }}</div>

            <div class="row g-3 mt-0">
              <div class="col-7">
                <div class="disposisi-label">PERUSAHAAN</div>
                <div class="disposisi-value">{{ $item['pelanggan'] ?? '-' }}</div>
              </div>
              <div class="col-5">
                <div class="disposisi-label">TANGGAL</div>
                <div class="disposisi-value">{{ $item['tanggal'] ?? '-' }}</div>
              </div>
              <div class="col-7">
                <div class="disposisi-label">JUMLAH ITEM</div>
                <div class="disposisi-value">{{ count($item['parameter'] ?? []) }} Item</div>
              </div>
              <div class="col-5 d-flex align-items-end">
                @if($buktiDoc)
                  <a
                    href="{{ $buktiDoc['url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn disposisi-doc-mini-btn w-100"
                  >
                    Bukti Pemesanan
                  </a>
                @endif
              </div>
            </div>
            @if(!empty($item['catatan_mp']))
              <div class="disposisi-note-wrap mt-2">
                <div class="disposisi-note-title">Catatan MP</div>
                <div class="disposisi-note-text">{{ $item['catatan_mp'] }}</div>
              </div>
            @endif

            <div class="d-flex gap-2 mt-3">
              <button
                type="button"
                class="btn disposisi-detail-btn flex-fill"
                data-bs-toggle="modal"
                data-bs-target="#disposisiDetailModal"
                data-kode="{{ $item['kode'] ?? '-' }}"
                data-pelanggan="{{ $item['pelanggan'] ?? '-' }}"
                data-penanggung="{{ $item['penanggung_jawab'] ?? '-' }}"
                data-email="{{ $item['email_perusahaan'] ?? '-' }}"
                data-telepon="{{ $item['telepon_perusahaan'] ?? '-' }}"
                data-alamat="{{ $item['alamat_perusahaan'] ?? '-' }}"
                data-jenis-perusahaan="{{ $item['jenis_perusahaan'] ?? '-' }}"
                data-provinsi-perusahaan="{{ $item['provinsi_perusahaan'] ?? '-' }}"
                data-kota-perusahaan="{{ $item['kota_perusahaan'] ?? '-' }}"
                data-jumlah-pekerja="{{ $item['jumlah_pekerja'] ?? 0 }}"
                data-penandatangan-sama="{{ ($item['penandatangan_sama'] ?? false) ? '1' : '0' }}"
                data-penandatangan-nama="{{ $item['penandatangan_nama'] ?? '-' }}"
                data-penandatangan-jabatan="{{ $item['penandatangan_jabatan'] ?? '-' }}"
                data-tanggal="{{ $item['tanggal'] ?? '-' }}"
                data-dokumen='@json($item['dokumen'] ?? [])'
                data-parameter='@json($item['parameter'] ?? [])'
              >
                Lihat Detail
              </button>
              @if(!($item['acc_mp'] ?? false))
                <button
                  type="button"
                  class="btn btn-deep-blue disposisi-approve-btn flex-fill"
                  data-bs-toggle="modal"
                  data-bs-target="#approveModal"
                  data-role="mp"
                  data-id="{{ $item['id'] ?? '' }}"
                  data-kode="{{ $item['kode'] ?? '-' }}"
                  data-pelanggan="{{ $item['pelanggan'] ?? '-' }}"
                  data-jenis="{{ $item['jenis'] ?? '-' }}"
                  data-acc="{{ $item['acc'] ?? 'Persetujuan MP' }}"
                >
                  Setujui
                </button>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="text-center text-muted py-4">Tidak ada disposisi ke MP.</div>
        </div>
      @endforelse
    </div>
    <div class="alert alert-light border d-none mt-3 mb-0" role="alert" data-search-empty>
      Tidak ada hasil untuk pencarian ini.
    </div>
  </div>

  <div @class(['d-none' => !$showMtPanel]) data-role-panel="mt">
    <div class="row g-3" data-role-grid>
      @forelse($disposisiMt as $item)
        <div
          class="col-12 col-md-6 col-xl-4"
          data-disposisi-card
          data-kode="{{ strtolower($item['kode'] ?? '') }}"
          data-pelanggan="{{ strtolower($item['pelanggan'] ?? '') }}"
          data-status="menunggu persetujuan"
        >
          <div class="disposisi-item-card">
            <div class="disposisi-item-head">
              <div class="disposisi-item-icon">
                <i class="bi bi-file-earmark-text"></i>
              </div>
              <span class="disposisi-status-badge">Menunggu Persetujuan</span>
            </div>
            @php
              $buktiDoc = collect($item['dokumen'] ?? [])->first(fn ($doc) => !empty($doc['url']));
            @endphp

            <div class="disposisi-label">ID PESANAN</div>
            <div class="disposisi-code">{{ $item['kode'] ?? '-' }}</div>

            <div class="row g-3 mt-0">
              <div class="col-7">
                <div class="disposisi-label">PERUSAHAAN</div>
                <div class="disposisi-value">{{ $item['pelanggan'] ?? '-' }}</div>
              </div>
              <div class="col-5">
                <div class="disposisi-label">TANGGAL</div>
                <div class="disposisi-value">{{ $item['tanggal'] ?? '-' }}</div>
              </div>
              <div class="col-7">
                <div class="disposisi-label">JUMLAH ITEM</div>
                <div class="disposisi-value">{{ count($item['parameter'] ?? []) }} Item</div>
              </div>
              <div class="col-5 d-flex align-items-end">
                @if($buktiDoc)
                  <a
                    href="{{ $buktiDoc['url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn disposisi-doc-mini-btn w-100"
                  >
                    Bukti Pemesanan
                  </a>
                @endif
              </div>
            </div>
            @if(!empty($item['catatan_mp']))
              <div class="disposisi-note-wrap mt-2">
                <div class="disposisi-note-title">Catatan MP</div>
                <div class="disposisi-note-text">{{ $item['catatan_mp'] }}</div>
              </div>
            @endif

            <div class="d-flex gap-2 mt-3">
              <button
                type="button"
                class="btn disposisi-detail-btn flex-fill"
                data-bs-toggle="modal"
                data-bs-target="#disposisiDetailModal"
                data-kode="{{ $item['kode'] ?? '-' }}"
                data-pelanggan="{{ $item['pelanggan'] ?? '-' }}"
                data-penanggung="{{ $item['penanggung_jawab'] ?? '-' }}"
                data-email="{{ $item['email_perusahaan'] ?? '-' }}"
                data-telepon="{{ $item['telepon_perusahaan'] ?? '-' }}"
                data-alamat="{{ $item['alamat_perusahaan'] ?? '-' }}"
                data-jenis-perusahaan="{{ $item['jenis_perusahaan'] ?? '-' }}"
                data-provinsi-perusahaan="{{ $item['provinsi_perusahaan'] ?? '-' }}"
                data-kota-perusahaan="{{ $item['kota_perusahaan'] ?? '-' }}"
                data-jumlah-pekerja="{{ $item['jumlah_pekerja'] ?? 0 }}"
                data-penandatangan-sama="{{ ($item['penandatangan_sama'] ?? false) ? '1' : '0' }}"
                data-penandatangan-nama="{{ $item['penandatangan_nama'] ?? '-' }}"
                data-penandatangan-jabatan="{{ $item['penandatangan_jabatan'] ?? '-' }}"
                data-tanggal="{{ $item['tanggal'] ?? '-' }}"
                data-dokumen='@json($item['dokumen'] ?? [])'
                data-parameter='@json($item['parameter'] ?? [])'
              >
                Lihat Detail
              </button>
              @if(!($item['acc_mt'] ?? false))
                <button
                  type="button"
                  class="btn btn-deep-blue disposisi-approve-btn flex-fill"
                  data-bs-toggle="modal"
                  data-bs-target="#approveModal"
                  data-role="mt"
                  data-id="{{ $item['id'] ?? '' }}"
                  data-kode="{{ $item['kode'] ?? '-' }}"
                  data-pelanggan="{{ $item['pelanggan'] ?? '-' }}"
                  data-jenis="{{ $item['jenis'] ?? '-' }}"
                  data-acc="{{ $item['acc'] ?? 'Persetujuan MT' }}"
                >
                  Setujui
                </button>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="text-center text-muted py-4">Tidak ada disposisi ke MT.</div>
        </div>
      @endforelse
    </div>
    <div class="alert alert-light border d-none mt-3 mb-0" role="alert" data-search-empty>
      Tidak ada hasil untuk pencarian ini.
    </div>
  </div>
</div>

<!-- Modal Detail Disposisi -->
<div class="modal fade disposisi-detail-modal" id="disposisiDetailModal" tabindex="-1" aria-labelledby="disposisiDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="disposisiDetailModalLabel">Detail Permohonan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="border rounded-4 p-4 mb-3 shadow-sm bg-white detail-section">
          <div class="fw-semibold mb-3">Informasi Perusahaan</div>
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Nama Perusahaan</div>
              <div class="h6 mb-0" id="detailNamaPerusahaan">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Nama Penanggung Jawab</div>
              <div class="h6 mb-0" id="detailPenanggungJawab">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Email Perusahaan</div>
              <div class="h6 mb-0" id="detailEmailPerusahaan">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Nomor Telepon (WA)</div>
              <div class="h6 mb-0" id="detailTeleponPerusahaan">-</div>
            </div>
            <div class="col-12">
              <div class="text-muted small fw-semibold">Alamat Perusahaan</div>
              <div class="h6 mb-0" id="detailAlamatPerusahaan">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Jumlah Pekerja</div>
              <div class="h6 mb-0" id="detailJumlahPekerja">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Jenis Perusahaan</div>
              <div class="h6 mb-0" id="detailJenisPerusahaan">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Provinsi</div>
              <div class="h6 mb-0" id="detailProvinsiPerusahaan">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Kota/Kabupaten</div>
              <div class="h6 mb-0" id="detailKotaPerusahaan">-</div>
            </div>
            <div class="col-12">
              <div class="alert alert-primary py-2 mb-2 small">
                Apakah nama di atas memiliki wewenang dalam menandatangani surat perjanjian kerjasama/SPK?
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="detailPenandatanganSama" disabled>
                <label class="form-check-label" for="detailPenandatanganSama">Ya, sama dengan penanggung jawab</label>
              </div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Nama</div>
              <div class="h6 mb-0" id="detailPenandatanganNama">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Jabatan</div>
              <div class="h6 mb-0" id="detailPenandatanganJabatan">-</div>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Kode Permohonan</div>
              <div class="h6 mb-0" id="detailKode">-</div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small fw-semibold">Tanggal</div>
              <div class="h6 mb-0" id="detailTanggal">-</div>
            </div>
          </div>
        </div>

        <div class="border rounded-4 p-4 shadow-sm bg-white detail-section">
          <h6 class="fw-semibold mb-3">Parameter Pengujian</h6>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 detail-parameter-table">
              <thead class="table-light">
                <tr>
                  <th>Parameter</th>
                  <th class="text-center" style="width: 80px;">Qty</th>
                  <th class="text-end" style="width: 160px;">Harga Satuan</th>
                  <th class="text-end" style="width: 160px;">Subtotal</th>
                </tr>
              </thead>
              <tbody id="detailParameter">
                <tr>
                  <td colspan="4" class="text-muted text-center">Tidak ada parameter.</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="text-end fw-semibold">Total</td>
                  <td class="text-end fw-semibold" id="detailTotal">Rp 0</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <div class="border rounded-4 p-4 shadow-sm bg-white mt-3 detail-section">
          <h6 class="fw-semibold mb-3">Dokumen</h6>
          <div id="detailDokumen"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Persetujuan -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Setujui Permintaan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="border rounded-4 p-3 shadow-sm bg-light mb-3">
          <div class="d-flex justify-content-between">
            <div>
              <div class="text-muted small">Kode Permohonan</div>
              <div class="fw-semibold" id="approveKode">-</div>
            </div>
            <div class="text-end">
              <div class="text-muted small">Role</div>
              <div class="fw-semibold" id="approveRole">-</div>
            </div>
          </div>
          <div class="mt-2">
            <div class="text-muted small">Pelanggan</div>
            <div class="fw-semibold" id="approvePelanggan">-</div>
          </div>
        </div>
        <div class="text-muted small mb-2">Aksi ini akan menandai permintaan sebagai sudah disetujui.</div>
        <div class="alert alert-info mb-0 py-2" data-approve-info>Pastikan dokumen dan parameter sudah sesuai sebelum persetujuan.</div>
        <div class="mt-3">
          <label class="form-label small text-muted mb-1" for="approveNote">Tambahan Catatan (Opsional)</label>
          <textarea class="form-control" id="approveNote" rows="3" placeholder="Tulis catatan tambahan bila diperlukan"></textarea>
        </div>
        <div class="mt-3">
          <label class="form-label small text-muted mb-1">Captcha</label>
          {!! NoCaptcha::display() !!}
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="approveConfirmBtn">Setujui Sekarang</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  (() => {
    const parseJson = (value) => {
      try {
        return JSON.parse(value || '[]');
      } catch (err) {
        console.warn('Gagal parse JSON', err);
        return [];
      }
    };

    const detailModal = document.getElementById('disposisiDetailModal');
    if (detailModal) {
      detailModal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const data = {
          kode: trigger.getAttribute('data-kode'),
          pelanggan: trigger.getAttribute('data-pelanggan'),
          penanggung: trigger.getAttribute('data-penanggung'),
          email: trigger.getAttribute('data-email'),
          telepon: trigger.getAttribute('data-telepon'),
          alamat: trigger.getAttribute('data-alamat'),
          jenisPerusahaan: trigger.getAttribute('data-jenis-perusahaan'),
          provinsiPerusahaan: trigger.getAttribute('data-provinsi-perusahaan'),
          kotaPerusahaan: trigger.getAttribute('data-kota-perusahaan'),
          jumlahPekerja: trigger.getAttribute('data-jumlah-pekerja'),
          penandatanganSama: trigger.getAttribute('data-penandatangan-sama') === '1',
          penandatanganNama: trigger.getAttribute('data-penandatangan-nama'),
          penandatanganJabatan: trigger.getAttribute('data-penandatangan-jabatan'),
          tanggal: trigger.getAttribute('data-tanggal'),
          dokumen: parseJson(trigger.getAttribute('data-dokumen')),
          parameter: parseJson(trigger.getAttribute('data-parameter')),
        };

        const setText = (selector, value) => {
          const el = detailModal.querySelector(selector);
          if (el) el.textContent = value || '-';
        };

        setText('#detailKode', data.kode);
        setText('#detailTanggal', data.tanggal);

        const setInfo = (selector, value) => {
          const el = detailModal.querySelector(selector);
          if (el) el.textContent = value || '-';
        };

        setInfo('#detailNamaPerusahaan', data.pelanggan);
        setInfo('#detailPenanggungJawab', data.penanggung);
        setInfo('#detailEmailPerusahaan', data.email);
        setInfo('#detailTeleponPerusahaan', data.telepon);
        setInfo('#detailAlamatPerusahaan', data.alamat);
        setInfo('#detailJenisPerusahaan', data.jenisPerusahaan);
        setInfo('#detailProvinsiPerusahaan', data.provinsiPerusahaan);
        setInfo('#detailKotaPerusahaan', data.kotaPerusahaan);
        setInfo('#detailJumlahPekerja', data.jumlahPekerja);
        setInfo('#detailPenandatanganNama', data.penandatanganNama);
        setInfo('#detailPenandatanganJabatan', data.penandatanganJabatan);

        const checkbox = detailModal.querySelector('#detailPenandatanganSama');
        if (checkbox) checkbox.checked = !!data.penandatanganSama;

        const paramBody = detailModal.querySelector('#detailParameter');
        if (paramBody) {
          const items = Array.isArray(data.parameter) ? data.parameter : [];
          let total = 0;
          if (items.length === 0) {
            paramBody.innerHTML = '<tr><td colspan="4" class="text-muted text-center">Tidak ada parameter.</td></tr>';
          } else {
            paramBody.innerHTML = items.map((item) => {
              const qty = Number(item.qty || 0);
              const harga = typeof item.harga_satuan === 'number'
                ? item.harga_satuan
                : Number(item.harga_satuan || 0);
              const subtotal = qty * harga;
              total += subtotal;
              const hargaText = harga ? harga.toLocaleString('id-ID') : '-';
              const subtotalText = subtotal ? subtotal.toLocaleString('id-ID') : '-';
              return `
                <tr>
                  <td>${item.nama || '-'}</td>
                  <td class="text-center">${qty || '-'}</td>
                  <td class="text-end">Rp ${hargaText}</td>
                  <td class="text-end">Rp ${subtotalText}</td>
                </tr>
              `;
            }).join('');
          }
          const totalEl = detailModal.querySelector('#detailTotal');
          if (totalEl) {
            totalEl.textContent = `Rp ${total.toLocaleString('id-ID')}`;
          }
        }

        const docWrap = detailModal.querySelector('#detailDokumen');
        if (docWrap) {
          const docs = Array.isArray(data.dokumen) ? data.dokumen : [];
          if (docs.length === 0) {
            docWrap.innerHTML = '<div class="text-muted">Tidak ada dokumen.</div>';
          } else {
            docWrap.innerHTML = docs.map((doc) => `
              <a href="${doc.url || '#'}" target="_blank" rel="noopener noreferrer" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 text-decoration-none">
                <span class="fw-semibold text-secondary">${doc.nama || 'Dokumen'}</span>
                <span class="fs-5 text-primary">&#128190;</span>
              </a>
            `).join('');
          }
        }
      });
    }

    const createMtCard = (data) => {
      const params = parseJson(data.parameter);
      const docs = parseJson(data.dokumen);
      const buktiDoc = Array.isArray(docs) ? docs.find((doc) => doc && doc.url) : null;
      const card = document.createElement('div');
      card.className = 'col-12 col-md-6 col-xl-4';
      card.setAttribute('data-disposisi-card', '');
      card.setAttribute('data-kode', (data.kode || '').toLowerCase());
      card.setAttribute('data-pelanggan', (data.pelanggan || '').toLowerCase());
      card.setAttribute('data-status', 'menunggu persetujuan');

      card.innerHTML = `
        <div class="disposisi-item-card">
          <div class="disposisi-item-head">
            <div class="disposisi-item-icon">
              <i class="bi bi-file-earmark-text"></i>
            </div>
            <span class="disposisi-status-badge">Menunggu Persetujuan</span>
          </div>

          <div class="disposisi-label">ID PESANAN</div>
          <div class="disposisi-code">${data.kode || '-'}</div>

          <div class="row g-3 mt-0">
            <div class="col-7">
              <div class="disposisi-label">PERUSAHAAN</div>
              <div class="disposisi-value">${data.pelanggan || '-'}</div>
            </div>
            <div class="col-5">
              <div class="disposisi-label">TANGGAL</div>
              <div class="disposisi-value">${data.tanggal || '-'}</div>
            </div>
            <div class="col-7">
              <div class="disposisi-label">JUMLAH ITEM</div>
              <div class="disposisi-value">${params.length} Item</div>
            </div>
            <div class="col-5 d-flex align-items-end">
              ${buktiDoc ? `
                <a href="${buktiDoc.url}" target="_blank" rel="noopener noreferrer" class="btn disposisi-doc-mini-btn w-100">
                  Bukti Pemesanan
                </a>
              ` : ''}
            </div>
          </div>

        <div class="d-flex gap-2 mt-3">
          <button
            type="button"
            class="btn disposisi-detail-btn flex-fill"
            data-bs-toggle="modal"
            data-bs-target="#disposisiDetailModal"
          >
            Lihat Detail
          </button>
          <button
            type="button"
            class="btn btn-deep-blue disposisi-approve-btn flex-fill"
            data-bs-toggle="modal"
            data-bs-target="#approveModal"
            data-role="mt"
          >
            Setujui
          </button>
        </div>
      </div>
      `;

      const detailBtn = card.querySelector('[data-bs-target="#disposisiDetailModal"]');
      if (detailBtn) {
        detailBtn.setAttribute('data-kode', data.kode || '-');
        detailBtn.setAttribute('data-pelanggan', data.pelanggan || '-');
        detailBtn.setAttribute('data-penanggung', data.penanggung || '-');
        detailBtn.setAttribute('data-email', data.email || '-');
        detailBtn.setAttribute('data-telepon', data.telepon || '-');
        detailBtn.setAttribute('data-alamat', data.alamat || '-');
        detailBtn.setAttribute('data-jenis-perusahaan', data.jenisPerusahaan || '-');
        detailBtn.setAttribute('data-provinsi-perusahaan', data.provinsiPerusahaan || '-');
        detailBtn.setAttribute('data-kota-perusahaan', data.kotaPerusahaan || '-');
        detailBtn.setAttribute('data-jumlah-pekerja', data.jumlahPekerja || '0');
        detailBtn.setAttribute('data-penandatangan-sama', data.penandatanganSama ? '1' : '0');
        detailBtn.setAttribute('data-penandatangan-nama', data.penandatanganNama || '-');
        detailBtn.setAttribute('data-penandatangan-jabatan', data.penandatanganJabatan || '-');
        detailBtn.setAttribute('data-tanggal', data.tanggal || '-');
        detailBtn.setAttribute('data-dokumen', data.dokumen || '[]');
        detailBtn.setAttribute('data-parameter', data.parameter || '[]');
      }

      const approveBtn = card.querySelector('[data-role="mt"]');
      if (approveBtn) {
        approveBtn.setAttribute('data-kode', data.kode || '-');
        approveBtn.setAttribute('data-pelanggan', data.pelanggan || '-');
        approveBtn.setAttribute('data-jenis', data.jenis || '-');
        approveBtn.setAttribute('data-acc', data.acc || 'Persetujuan MT');
      }

      return card;
    };

    const moveCardToMt = (trigger) => {
      const card = trigger.closest('[data-disposisi-card]');
      const detailBtn = card?.querySelector('[data-bs-target="#disposisiDetailModal"]');
      const mtPanel = document.querySelector('[data-role-panel="mt"]');
      const mtGrid = mtPanel?.querySelector('[data-role-grid]');
      if (!card || !detailBtn || !mtPanel || !mtGrid) return;

      const data = {
        kode: detailBtn.getAttribute('data-kode'),
        pelanggan: detailBtn.getAttribute('data-pelanggan'),
        penanggung: detailBtn.getAttribute('data-penanggung'),
        email: detailBtn.getAttribute('data-email'),
        telepon: detailBtn.getAttribute('data-telepon'),
        alamat: detailBtn.getAttribute('data-alamat'),
        jenisPerusahaan: detailBtn.getAttribute('data-jenis-perusahaan'),
        provinsiPerusahaan: detailBtn.getAttribute('data-provinsi-perusahaan'),
        kotaPerusahaan: detailBtn.getAttribute('data-kota-perusahaan'),
        jumlahPekerja: detailBtn.getAttribute('data-jumlah-pekerja'),
        penandatanganSama: detailBtn.getAttribute('data-penandatangan-sama') === '1',
        penandatanganNama: detailBtn.getAttribute('data-penandatangan-nama'),
        penandatanganJabatan: detailBtn.getAttribute('data-penandatangan-jabatan'),
        tanggal: detailBtn.getAttribute('data-tanggal'),
        dokumen: detailBtn.getAttribute('data-dokumen'),
        parameter: detailBtn.getAttribute('data-parameter'),
      };

      const newCard = createMtCard(data);
      mtGrid.appendChild(newCard);

      card.remove();
      filterCards();
    };

    const approveModal = document.getElementById('approveModal');
    let approveTrigger = null;

    if (approveModal) {
      approveModal.addEventListener('show.bs.modal', (event) => {
        approveTrigger = event.relatedTarget;
        if (!approveTrigger) return;

        const roleLabel = approveTrigger.getAttribute('data-role') === 'mt' ? 'Manajer Teknis' : 'Manajer Puncak';
        const kode = approveTrigger.getAttribute('data-kode') || '-';
        const pelanggan = approveTrigger.getAttribute('data-pelanggan') || '-';
        const info = approveTrigger.getAttribute('data-acc') || 'Persetujuan Permohonan';

        const setText = (selector, value) => {
          const el = approveModal.querySelector(selector);
          if (el) el.textContent = value || '-';
        };

        setText('#approveKode', kode);
        setText('#approvePelanggan', pelanggan);
        setText('#approveRole', roleLabel);

        const infoBox = approveModal.querySelector('[data-approve-info]');
        if (infoBox) infoBox.textContent = info;
        const approveNoteEl = approveModal.querySelector('#approveNote');
        if (approveNoteEl) approveNoteEl.value = '';
      });

      const confirmBtn = document.getElementById('approveConfirmBtn');
      if (confirmBtn) {
        confirmBtn.addEventListener('click', async () => {
          if (!approveTrigger) return;
          const role = approveTrigger.getAttribute('data-role');
          const id = approveTrigger.getAttribute('data-id');
          const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
          const note = (approveModal.querySelector('#approveNote')?.value || '').trim();
          if (!id) return;
          const captchaValue = document.querySelector('[name="g-recaptcha-response"]')?.value || '';
          if (!captchaValue) {
            if (typeof Swal !== 'undefined') {
              Swal.fire({ icon: 'warning', title: 'Captcha wajib', text: 'Silakan selesaikan reCAPTCHA terlebih dahulu.' });
            } else {
              alert('Silakan selesaikan reCAPTCHA terlebih dahulu.');
            }
            return;
          }

          try {
            const response = await fetch(`/superadmin/disposisi/${id}/approve`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token || '',
              },
              credentials: 'same-origin',
              body: JSON.stringify({ role, note, 'g-recaptcha-response': captchaValue }),
            });

            if (response.ok) {
              window.location.reload();
              return;
            }
          } catch (err) {
            console.warn('Gagal menyimpan persetujuan', err);
          }

          const modalInstance = bootstrap.Modal.getInstance(approveModal);
          if (modalInstance) modalInstance.hide();
        });
      }
    }

    const globalSearchInput = document.querySelector('[data-search-global]');
    const kodeInput = document.querySelector('[data-search-kode]');
    const pelangganInput = document.querySelector('[data-search-pelanggan]');
    const resetBtn = document.querySelector('[data-search-reset]');
    function filterCards() {
      const globalVal = (globalSearchInput?.value || '').toLowerCase().trim();
      const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
      const pelangganVal = (pelangganInput?.value || '').toLowerCase().trim();
      const hasSearchValue = !!(globalVal || kodeVal || pelangganVal);
      panels.forEach((panel) => {
        const cards = panel.querySelectorAll('[data-disposisi-card]');
        let visibleCount = 0;
        cards.forEach((card) => {
          const kode = card.getAttribute('data-kode') || '';
          const pelanggan = card.getAttribute('data-pelanggan') || '';
          const status = card.getAttribute('data-status') || '';
          const matchGlobal = !globalVal
            || kode.includes(globalVal)
            || pelanggan.includes(globalVal)
            || status.includes(globalVal);
          const matchKode = !kodeVal || kode.includes(kodeVal);
          const matchPelanggan = !pelangganVal || pelanggan.includes(pelangganVal);
          const isMatch = matchGlobal && matchKode && matchPelanggan;
          card.classList.toggle('d-none', !isMatch);
          if (isMatch) visibleCount += 1;
        });

        const emptyState = panel.querySelector('[data-search-empty]');
        if (emptyState) {
          const shouldShow = hasSearchValue && visibleCount === 0 && cards.length > 0;
          emptyState.classList.toggle('d-none', !shouldShow);
        }
      });
    }

    const switchButtons = document.querySelectorAll('[data-role-switch]');
    const panels = document.querySelectorAll('[data-role-panel]');
    switchButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const role = btn.getAttribute('data-role-switch');
        switchButtons.forEach((b) => {
          const isActive = b === btn;
          b.classList.toggle('btn-deep-blue', isActive);
          b.classList.toggle('btn-outline-deep-blue', !isActive);
        });
        panels.forEach((panel) => {
          panel.classList.toggle('d-none', panel.getAttribute('data-role-panel') !== role);
        });
        filterCards();
      });
    });

    globalSearchInput?.addEventListener('input', filterCards);
    kodeInput?.addEventListener('input', filterCards);
    pelangganInput?.addEventListener('input', filterCards);
    resetBtn?.addEventListener('click', () => {
      if (globalSearchInput) globalSearchInput.value = '';
      if (kodeInput) kodeInput.value = '';
      if (pelangganInput) pelangganInput.value = '';
      filterCards();
    });

    filterCards();
  })();
</script>
{!! NoCaptcha::renderJs() !!}
@endpush

@push('styles')
<style>
  .disposisi-shell {
    font-family: 'Poppins', sans-serif;
    background: #f3f6fb;
    border: 1px solid #dde5f0;
    border-radius: 24px;
    padding: 28px;
  }

  .disposisi-toolbar {
    display: flex;
    justify-content: center;
    margin-bottom: 1.25rem;
  }

  .disposisi-role-switch {
    background: #e4eaf2;
    border-radius: 12px;
    padding: 4px;
    gap: 4px;
  }

  .disposisi-role-switch .btn {
    border: 0 !important;
    border-radius: 10px !important;
    min-width: 180px;
    padding: 10px 18px;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: none !important;
  }

  .btn-deep-blue {
    background-color: #15406a !important;
    border-color: #15406a !important;
    color: #fff !important;
  }

  .btn-deep-blue:hover,
  .btn-deep-blue:focus {
    background-color: #12385d !important;
    border-color: #12385d !important;
    color: #fff !important;
  }

  .disposisi-role-switch .btn-deep-blue {
    background-color: #fff !important;
    border-color: #fff !important;
    color: #15406a !important;
    box-shadow: 0 3px 12px rgba(21, 64, 106, 0.18) !important;
  }

  .btn-outline-deep-blue {
    background: transparent !important;
    border-color: transparent !important;
    color: #63748f !important;
  }

  .btn-outline-deep-blue:hover,
  .btn-outline-deep-blue:focus {
    border-color: transparent !important;
    color: #15406a !important;
  }

  .disposisi-role-badge {
    background: #15406a;
    color: #fff;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 0.6rem 1rem;
  }

  .disposisi-search-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    margin-bottom: 1.25rem;
  }

  .disposisi-search-input {
    position: relative;
    width: min(700px, 100%);
  }

  .disposisi-search-input i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9aabc3;
    font-size: 0.9rem;
    pointer-events: none;
  }

  .disposisi-search-input .form-control {
    border: 1px solid #d9e2ef;
    border-radius: 12px;
    padding: 0.58rem 0.95rem 0.58rem 2.15rem;
    min-height: 40px;
    color: #15406a;
    font-size: 0.86rem;
    box-shadow: 0 2px 8px rgba(18, 44, 77, 0.06);
  }

  .disposisi-search-input .form-control:focus {
    border-color: #15406a;
    box-shadow: 0 0 0 0.15rem rgba(21, 64, 106, 0.15);
  }

  .disposisi-reset-btn {
    border-radius: 10px;
    min-height: 40px;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.45rem 0.9rem;
    color: #15406a;
    border-color: #cfd9e7;
    white-space: nowrap;
  }

  .disposisi-item-card {
    background: #fff;
    border: 1px solid #dde5f0;
    border-radius: 14px;
    padding: 12px;
    height: 100%;
    box-shadow: 0 4px 18px rgba(21, 64, 106, 0.08);
  }

  .disposisi-item-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 0.65rem;
  }

  .disposisi-item-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ecf2fb;
    color: #15406a;
    font-size: 1rem;
  }

  .disposisi-status-badge {
    display: inline-flex;
    align-items: center;
    background: #fbe8b4;
    color: #b06f00;
    border-radius: 999px;
    padding: 0.2rem 0.58rem;
    font-size: 0.66rem;
    font-weight: 600;
    white-space: nowrap;
  }

  .disposisi-label {
    font-size: 0.6rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #8fa1bb;
    font-weight: 700;
    margin-bottom: 0.16rem;
  }

  .disposisi-code {
    color: #102f53;
    font-size: 1.05rem;
    font-weight: 700;
    line-height: 1.35;
    margin-bottom: 0.72rem;
    word-break: break-word;
  }

  .disposisi-value {
    color: #0f2d4f;
    font-size: 0.86rem;
    font-weight: 700;
    line-height: 1.35;
  }

  .disposisi-item-card .row {
    --bs-gutter-x: 0.8rem;
    --bs-gutter-y: 0.55rem;
  }

  .disposisi-note-wrap {
    border: 1px dashed #cfdaea;
    background: #f6f9fd;
    border-radius: 8px;
    padding: 0.4rem 0.5rem;
  }

  .disposisi-note-title {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #5f7695;
    margin-bottom: 0.15rem;
  }

  .disposisi-note-text {
    font-size: 0.74rem;
    line-height: 1.35;
    color: #163b62;
    white-space: pre-line;
  }

  .disposisi-doc-mini-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #cfd9e7;
    border-radius: 8px;
    color: #15406a;
    background: #f6f9fd;
    font-size: 0.68rem;
    font-weight: 600;
    line-height: 1.2;
    padding: 0.28rem 0.5rem;
    min-height: 28px;
  }

  .disposisi-doc-mini-btn:hover,
  .disposisi-doc-mini-btn:focus {
    color: #12385d;
    border-color: #b9c9df;
    background: #edf3fa;
  }

  .disposisi-detail-btn,
  .disposisi-approve-btn {
    border-radius: 10px;
    font-weight: 600;
    min-height: 36px;
    font-size: 0.78rem;
  }

  .disposisi-detail-btn {
    background: #edf1f7;
    border-color: #edf1f7;
    color: #153f69;
  }

  .disposisi-detail-btn:hover,
  .disposisi-detail-btn:focus {
    background: #e2e8f2;
    border-color: #e2e8f2;
    color: #102f53;
  }

  .disposisi-detail-modal .modal-dialog {
    max-width: min(920px, calc(100% - 1.25rem));
  }

  .disposisi-detail-modal .modal-content {
    border: 1px solid #dbe5f2;
    border-radius: 16px;
    overflow: hidden;
  }

  .disposisi-detail-modal .modal-header,
  .disposisi-detail-modal .modal-footer {
    padding: 0.75rem 1rem;
    border-color: #e7edf6;
    background: #fff;
  }

  .disposisi-detail-modal .modal-title {
    font-size: 1rem;
    font-weight: 700;
    color: #12385d;
  }

  .disposisi-detail-modal .modal-body {
    background: #f6f8fc;
    padding: 0.85rem;
  }

  .disposisi-detail-modal .detail-section {
    border-color: #dfe7f3 !important;
    border-radius: 12px !important;
    padding: 0.9rem !important;
    margin-bottom: 0.75rem !important;
    box-shadow: 0 2px 10px rgba(21, 64, 106, 0.06) !important;
  }

  .disposisi-detail-modal .detail-section .fw-semibold {
    font-size: 0.92rem;
    color: #12385d;
  }

  .disposisi-detail-modal .detail-section .text-muted.small.fw-semibold {
    font-size: 0.68rem !important;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #8a9bb4 !important;
  }

  .disposisi-detail-modal .detail-section .h6 {
    font-size: 0.92rem;
    line-height: 1.35;
    color: #173f69;
  }

  .disposisi-detail-modal .alert {
    font-size: 0.78rem;
    padding: 0.45rem 0.6rem;
    margin-bottom: 0.5rem !important;
  }

  .disposisi-detail-modal .form-check-label {
    font-size: 0.84rem;
    color: #294e74;
  }

  .disposisi-detail-modal .detail-parameter-table {
    font-size: 0.83rem;
  }

  .disposisi-detail-modal .detail-parameter-table th {
    font-size: 0.74rem;
    color: #5d728f;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }

  .disposisi-detail-modal #detailDokumen a {
    font-size: 0.84rem;
    color: #1a456f;
    border-color: #dfe7f3 !important;
    background: #fbfdff;
    border-radius: 10px !important;
    padding: 0.55rem 0.7rem !important;
  }

  .disposisi-detail-modal .modal-footer .btn {
    font-size: 0.84rem;
    font-weight: 600;
    padding: 0.42rem 0.9rem;
    border-radius: 9px;
  }

  .count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0;
    line-height: 18px;
    margin-left: 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    background: #dc3545;
    color: #fff;
  }

  .btn-outline-deep-blue .count-badge {
    background: #dc3545;
    color: #fff;
  }

  @media (max-width: 991.98px) {
    .disposisi-shell {
      padding: 20px 16px;
      border-radius: 18px;
    }

    .disposisi-role-switch .btn {
      min-width: 148px;
      padding-inline: 12px;
      font-size: 0.82rem;
    }

    .disposisi-code {
      font-size: 0.96rem;
    }
  }

  @media (max-width: 575.98px) {
    .disposisi-search-wrap {
      flex-direction: column;
      align-items: stretch;
    }

    .disposisi-search-input {
      width: 100%;
    }

    .disposisi-detail-modal .modal-dialog {
      max-width: calc(100% - 0.75rem);
      margin: 0.35rem auto;
    }

    .disposisi-detail-modal .modal-body {
      padding: 0.6rem;
    }

    .disposisi-detail-modal .detail-section {
      padding: 0.72rem !important;
    }
  }
</style>
@endpush
