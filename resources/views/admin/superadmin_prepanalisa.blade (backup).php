@extends('layouts.app_admin')

@section('content_admin')
@php
  // Data contoh; ganti dengan data controller saat backend siap
  $orders = collect([
    [
      'kode' => 'ORD-24010',
      'perusahaan' => 'PT Surya Energi',
      'lokasi_induk' => 'Plant Gresik',
      'parameter' => [
        [
          'nama' => 'Iklim Kerja ISBB',
          'metode' => 'SNI 5510',
          'lokasi' => [
            ['nama' => 'Lokasi 1', 'sampling_at' => '2025-01-14 09:00', 'hasil' => 'SK 37.0 | SB 33.7 | SG 46.3 | RH 79 | ISBB 23.5', 'koding' => 'LOC-24010-IK-01'],
            ['nama' => 'Lokasi 2', 'sampling_at' => '2025-01-14 11:00', 'hasil' => 'SK 35.9 | SB 32.8 | SG 39.2 | RH 80 | ISBB 23.1', 'koding' => 'LOC-24010-IK-02'],
          ],
        ],
        [
          'nama' => 'Pengukuran Cahaya',
          'metode' => 'Lux Meter',
          'lokasi' => [
            ['nama' => 'Lokasi 1', 'sampling_at' => '2025-01-14 13:00', 'hasil' => 'Setempat: 125-136 lux | Umum: 120-132 lux', 'koding' => 'LOC-24010-CH-01'],
          ],
        ],
      ],
    ],
    [
      'kode' => 'ORD-24011',
      'perusahaan' => 'PT Bumi Beton',
      'lokasi_induk' => 'Workshop Sidoarjo',
      'parameter' => [
        [
          'nama' => 'Pengukuran Kebisingan',
          'metode' => 'SNI 7231',
          'lokasi' => [
            ['nama' => 'Lokasi 1', 'sampling_at' => '2025-01-15 10:15', 'hasil' => '73.8 | 73.7 | 73.7 | 73.7 | 73.6 dB; Sumber: Mesin', 'koding' => 'LOC-24011-KB-01'],
          ],
        ],
      ],
    ],
  ]);
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
  .text-bg-primary {
    background-color: #15406A !important;
    color: #fff !important;
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
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-semibold mb-0">Alur Kerja - Preparasi Analisa</h4>
    <div class="text-muted small">Review hasil sampling & ID koding per lokasi sebelum analisa lab dilanjutkan.</div>
  </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body">
    <div class="row g-2">
      <div class="col-12 col-md-4">
        <label class="form-label small text-muted mb-1">Cari kode order</label>
        <input type="text" class="form-control form-control-sm" placeholder="Misal: ORD-24010" data-search-kode>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label small text-muted mb-1">Cari nama perusahaan</label>
        <input type="text" class="form-control form-control-sm" placeholder="Misal: PT Surya" data-search-perusahaan>
      </div>
      <div class="col-12 col-md-2 d-flex align-items-end">
        <button type="button" class="btn btn-outline-secondary w-100 btn-sm" data-search-reset>Reset</button>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  @forelse($orders as $order)
  <div class="col-12" data-card data-kode="{{ strtolower($order['kode']) }}" data-perusahaan="{{ strtolower($order['perusahaan']) }}">
    <div class="card border-0 shadow-sm rounded-4 h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
          <div>
            <div class="fw-semibold">{{ $order['kode'] }} - {{ $order['perusahaan'] }}</div>
            <div class="small text-muted">Lokasi induk: {{ $order['lokasi_induk'] }}</div>
          </div>
        </div>

        @foreach($order['parameter'] as $pIdx => $param)
          @php $locations = $param['lokasi'] ?? []; @endphp
          <div class="border rounded-4 p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <div class="fw-semibold">{{ $param['nama'] }}</div>
                <div class="small text-muted">Metode: {{ $param['metode'] ?? '-' }}</div>
              </div>
              <span class="badge text-bg-secondary">Lokasi: {{ count($locations) }}</span>
            </div>

            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 30%;">Parameter</th>
                    <th style="width: 24%;">Lokasi</th>
                    <th style="width: 18%;">ID Koding</th>
                    <th style="width: 14%;">Tanggal Sampling</th>
                    <th style="width: 14%;">Tanggal Analisis</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($locations as $idx => $loc)
                  <tr>
                    <td class="fw-semibold">{{ $param['nama'] ?? '-' }}</td>
                    <td class="fw-semibold">{{ $loc['nama'] ?? '-' }}</td>
                    <td class="fw-semibold"><code>{{ $loc['koding'] ?? '-' }}</code></td>
                    <td class="small text-muted">{{ $loc['sampling_at'] ?? '-' }}</td>
                    <td class="small text-muted">-</td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted small">Belum ada lokasi untuk parameter ini.</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        @endforeach

        <div class="d-flex justify-content-end align-items-center mt-3 gap-2 flex-wrap">
          <div class="d-flex align-items-center gap-2">
            <input type="file" class="form-control form-control-sm" style="max-width: 240px;">
            <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" type="button">
              <i class="bi bi-upload"></i> Upload Dokumen Analisa
            </button>
          </div>
          <button class="btn btn-primary btn-sm" type="button">Kirim ke Verifikasi</button>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12 text-center text-muted py-4">Belum ada data preparasi analisa.</div>
  @endforelse
</div>

<div class="alert alert-warning d-none mt-3" data-search-empty>Tidak ada order yang cocok.</div>
@endsection

@push('scripts')
<script>
(() => {
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
      const kode = card.getAttribute('data-kode') || '';
      const perusahaan = card.getAttribute('data-perusahaan') || '';
      const matchKode = !kodeVal || kode.includes(kodeVal);
      const matchPerusahaan = !perusahaanVal || perusahaan.includes(perusahaanVal);
      const show = matchKode && matchPerusahaan;
      card.classList.toggle('d-none', !show);
      if (show) visible += 1;
    });

    if (emptyState) {
      emptyState.classList.toggle('d-none', visible > 0);
    }
  };

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
