@extends('layouts.app_admin')

@section('content_admin')
@php
  // Data contoh; ganti dengan data controller saat backend siap
  $orders = collect([
    [
      'kode' => 'ORD-24001',
      'perusahaan' => 'PT Surya Energi',
      'alamat' => 'Jl. Industri No. 10, Gresik',
      'dokumen' => '#',
      'parameter' => [
        [
          'nama' => 'Emisi Cerobong (SO2)',
          'status' => 'Menunggu',
          'lokasi' => [
            ['nama' => 'Kantor', 'koding' => 'LOC-01-SO2', 'sampling_at' => '2025-02-01 09:00', 'analisis_at' => '2025-02-05'],
            ['nama' => 'Dapur', 'koding' => 'LOC-02-SO2', 'sampling_at' => '2025-02-01 11:00', 'analisis_at' => '2025-02-05'],
            ['nama' => 'Gudang', 'koding' => 'LOC-03-SO2', 'sampling_at' => '2025-02-01 13:00', 'analisis_at' => '2025-02-05'],
          ],
        ],
      ],
    ],
    [
      'kode' => 'ORD-24002',
      'perusahaan' => 'PT Bumi Beton',
      'alamat' => 'Jl. Raya Sidoarjo No. 5',
      'dokumen' => '#',
      'parameter' => [
        [
          'nama' => 'Emisi Debu',
          'status' => 'Menunggu',
          'lokasi' => [
            ['nama' => 'Workshop', 'koding' => 'LOC-01-DB', 'sampling_at' => '2025-02-02 10:15', 'analisis_at' => '2025-02-07'],
            ['nama' => 'Area B', 'koding' => 'LOC-02-DB', 'sampling_at' => '2025-02-02 11:30', 'analisis_at' => '2025-02-07'],
            ['nama' => 'Area C', 'koding' => 'LOC-03-DB', 'sampling_at' => '2025-02-02 13:00', 'analisis_at' => '2025-02-07'],
          ],
        ],
      ],
    ],
  ]);
  $badgeStatus = fn($status) => match (strtolower($status)) {
    'revisi diminta' => 'text-bg-danger',
    'disetujui' => 'text-bg-success',
    default => 'text-bg-warning',
  };
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

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Pembuatan LHU',
  'subtitle' => 'Unduh dan unggah dokumen LHU per order, serta lihat lokasi per parameter.',
  'total' => $orders->count(),
])

<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body">
    <div class="row g-2">
      <div class="col-12 col-md-4">
        <label class="form-label small text-muted mb-1">Cari kode order</label>
        <input type="text" class="form-control form-control-sm" placeholder="Misal: ORD-24001" data-search-kode>
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
        @php
          $statuses = collect($order['parameter'] ?? [])->pluck('status')->map(fn($s) => strtolower($s));
          $orderStatus = 'Menunggu';
          if ($statuses->contains('revisi diminta')) $orderStatus = 'Revisi diminta';
          elseif ($statuses->every(fn($s) => $s === 'disetujui')) $orderStatus = 'Disetujui';
        @endphp
        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
          <div>
            <div class="fw-semibold">{{ $order['kode'] }} - {{ $order['perusahaan'] }}</div>
            <div class="small text-muted">Alamat: {{ $order['alamat'] ?? '-' }}</div>
          </div>
          <span class="badge {{ $orderStatus === 'Revisi diminta' ? 'text-bg-danger' : ($orderStatus === 'Disetujui' ? 'text-bg-success' : 'text-bg-warning') }}">
            Status: {{ $orderStatus }}
          </span>
        </div>

        @foreach($order['parameter'] as $param)
          @php
            $locations = $param['lokasi'] ?? [];
            $rowspan = max(1, count($locations));
          @endphp
          <div class="border rounded-4 p-3 mb-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <div>
                <div class="fw-semibold">{{ $param['nama'] }}</div>
              </div>
              <span class="badge text-bg-secondary">Lokasi: {{ count($locations) }}</span>
            </div>

            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width: 26%;">Parameter</th>
                    <th style="width: 24%;">Lokasi</th>
                    <th style="width: 24%;">Tanggal Sampling</th>
                    <th style="width: 24%;">Tanggal Analisis</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($locations as $idx => $loc)
                  <tr>
                    @if($idx === 0)
                      <td class="fw-semibold" rowspan="{{ $rowspan }}">{{ $param['nama'] }}</td>
                    @endif
                    <td class="fw-semibold">{{ $loc['nama'] ?? '-' }}</td>
                    <td class="small text-muted">{{ $loc['sampling_at'] ?? '-' }}</td>
                    <td class="small text-muted">{{ $loc['analisis_at'] ?? '-' }}</td>
                  </tr>
                  @empty
                  <tr>
                    <td class="fw-semibold">{{ $param['nama'] ?? '-' }}</td>
                    <td class="fw-semibold">-</td>
                    <td class="small text-muted">-</td>
                    <td class="small text-muted">-</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        @endforeach

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
          <a href="{{ $order['dokumen'] ?? '#' }}" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" role="button" download>
            <i class="bi bi-download"></i> Unduh Dokumen Analis
          </a>
          <div class="d-flex align-items-center gap-2">
            <input type="file" class="form-control form-control-sm" style="max-width: 220px;">
            <button class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" type="button">
              <i class="bi bi-upload"></i> Upload LHU
            </button>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" type="button">Selesaikan LHU</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12 text-center text-muted py-4">Belum ada data LHU.</div>
  @endforelse
</div>

<div class="text-center text-muted d-none mt-3" data-search-empty>Tidak ada order yang cocok.</div>

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
      const hasCards = cards.length > 0;
      emptyState.classList.toggle('d-none', !hasCards || visible > 0);
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

