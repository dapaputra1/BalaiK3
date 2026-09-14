@extends('layouts.app_admin')

@section('content_admin')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-semibold mb-0">Daftar Permohonan</h4>
</div>

@if(session('success'))
  <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('success') }}</div>
@endif

@if(session('error'))
  <div class="alert alert-danger border-0 shadow-sm rounded-4">{{ session('error') }}</div>
@endif

<div class="card shadow-sm border-0 rounded-4">
  <div class="card-body p-4">

    @php
      $rows = collect($permohonans ?? []);
      $tahapOptions = $rows->pluck('tahap')->filter()->unique()->sort()->values();
      $statusOptions = $rows->pluck('status')->filter()->unique()->sort()->values();
      $provinsiOptions = $rows->pluck('provinsi')->filter()->unique()->sort()->values();
      $kotaOptions = $rows->pluck('kota')->filter()->unique()->sort()->values();
    @endphp

    <div class="border rounded-4 p-3 mb-4 bg-light-subtle">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-6 col-lg-3">
          <label class="form-label small text-muted mb-1">Cari Pemohon</label>
          <input type="text" class="form-control form-control-sm" placeholder="Kode / nama perusahaan" data-filter-keyword>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
          <label class="form-label small text-muted mb-1">Tahap</label>
          <select class="form-select form-select-sm" data-filter-tahap>
            <option value="">Semua tahap</option>
            @foreach($tahapOptions as $tahap)
              <option value="{{ strtolower((string) $tahap) }}">{{ $tahap }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
          <label class="form-label small text-muted mb-1">Status</label>
          <select class="form-select form-select-sm" data-filter-status>
            <option value="">Semua status</option>
            @foreach($statusOptions as $status)
              <option value="{{ strtolower((string) $status) }}">{{ $status }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
          <label class="form-label small text-muted mb-1">Provinsi</label>
          <select class="form-select form-select-sm" data-filter-provinsi>
            <option value="">Semua provinsi</option>
            @foreach($provinsiOptions as $provinsi)
              <option value="{{ strtolower((string) $provinsi) }}">{{ $provinsi }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
          <label class="form-label small text-muted mb-1">Kota</label>
          <select class="form-select form-select-sm" data-filter-kota>
            <option value="">Semua kota</option>
            @foreach($kotaOptions as $kota)
              <option value="{{ strtolower((string) $kota) }}">{{ $kota }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-6 col-md-2 col-lg-1">
          <label class="form-label small text-muted mb-1">Min %</label>
          <input type="number" min="0" max="100" class="form-control form-control-sm" placeholder="0" data-filter-progress-min>
        </div>
        <div class="col-6 col-md-2 col-lg-1">
          <label class="form-label small text-muted mb-1">Max %</label>
          <input type="number" min="0" max="100" class="form-control form-control-sm" placeholder="100" data-filter-progress-max>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
          <label class="form-label small text-muted mb-1">Tanggal Dari</label>
          <input type="date" class="form-control form-control-sm" data-filter-date-from>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
          <label class="form-label small text-muted mb-1">Tanggal Sampai</label>
          <input type="date" class="form-control form-control-sm" data-filter-date-to>
        </div>
        <div class="col-12 col-lg-4 d-flex gap-2 justify-content-start flex-wrap">
          <button type="button" class="btn btn-outline-secondary btn-sm px-3 text-nowrap" data-filter-reset>Reset</button>
          <button type="button" class="btn btn-success btn-sm px-3 text-nowrap" data-filter-export data-export-url="{{ route('superadmin.permohonan.export') }}">Export Excel</button>
        </div>
      </div>
      <div class="small text-muted mt-2">
        Total data tampil: <span class="fw-semibold" data-filter-count>{{ $rows->count() }}</span>
      </div>
    </div>

    <div class="table-responsive">
      <table id="permohonanTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
          <tr>
            <th>ID Permohonan</th>
            <th>Pelanggan</th>
            <th>Progress</th>
            <th>Tahapan</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>

        <tbody>
          @foreach($rows as $p)
            <tr
              data-kode="{{ strtolower((string) ($p->kode ?? '')) }}"
              data-pelanggan="{{ strtolower((string) ($p->pelanggan ?? '')) }}"
              data-tahap="{{ strtolower((string) ($p->tahap ?? '')) }}"
              data-status="{{ strtolower((string) ($p->status ?? '')) }}"
              data-progress="{{ (int) ($p->progress ?? 0) }}"
              data-tanggal-iso="{{ optional($p->tanggal ?? null)->format('Y-m-d') }}"
              data-provinsi="{{ strtolower((string) ($p->provinsi ?? '')) }}"
              data-kota="{{ strtolower((string) ($p->kota ?? '')) }}"
            >
              <td class="fw-semibold text-nowrap">{{ $p->kode }}</td>
              <td>{{ $p->pelanggan }}</td>

              {{-- Progress: tampilkan angka agar bisa di-sort --}}
              <td class="text-nowrap">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <div class="progress w-100" style="height: 8px;">
                    <div class="progress-bar" role="progressbar"
                         style="width: {{ (int)$p->progress }}%;"
                         aria-valuenow="{{ (int)$p->progress }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                  </div>
                  <small class="text-muted" style="min-width:42px;">{{ (int)$p->progress }}%</small>
                </div>
              </td>

              <td class="text-nowrap">{{ $p->tahap ?? '-' }}</td>

              <td class="text-nowrap" data-order="{{ optional($p->tanggal ?? null)->format('Y-m-d H:i:s') }}">
                {{ optional($p->tanggal ?? null)->format('d M Y') }}
              </td>

              <td class="text-nowrap">
                <div class="d-inline-flex align-items-center gap-1">
                  <button
                    type="button"
                    class="btn btn-outline-secondary btn-sm view-detail"
                    data-bs-toggle="modal"
                    data-bs-target="#permohonanDetailModal"
            data-kode="{{ $p->kode }}"
            data-pelanggan="{{ $p->pelanggan }}"
            data-jenis="{{ $p->jenis_pengujian }}"
            data-tahap="{{ $p->tahap }}"
            data-progress="{{ (int) $p->progress }}"
            data-status="{{ $p->status }}"
            data-tanggal="{{ optional($p->tanggal ?? null)->format('d M Y') }}"
                    data-perusahaan="{{ $p->perusahaan ?? $p->pelanggan ?? '-' }}"
                    data-penanggung="{{ $p->penanggung_jawab ?? '-' }}"
                    data-email="{{ $p->email ?? '-' }}"
            data-telepon="{{ $p->telepon ?? '-' }}"
            data-alamat="{{ $p->alamat ?? '-' }}"
            data-jenis-perusahaan="{{ $p->jenis_perusahaan ?? '-' }}"
            data-provinsi="{{ $p->provinsi ?? '-' }}"
            data-kota="{{ $p->kota ?? '-' }}"
            data-lokasi="{{ $p->lokasi ?? '-' }}"
            data-lokasi-rows="{{ e(json_encode($p->lokasi_rows ?? [])) }}"
            data-tanggal-pengujian="{{ $p->tanggal_pengujian ?? '-' }}"
            data-tanggal-pengujian-mulai="{{ $p->tanggal_pengujian_mulai ?? '-' }}"
            data-tanggal-pengujian-selesai="{{ $p->tanggal_pengujian_selesai ?? '-' }}"
            data-penandatangan-sama="{{ !empty($p->penandatangan_sama) ? 1 : 0 }}"
            data-nama-penandatangan="{{ $p->nama_penandatangan ?? '-' }}"
            data-jabatan-penandatangan="{{ $p->jabatan_penandatangan ?? '-' }}"
            data-penanggung-ttd="{{ $p->penanggung_jawab_ttd ?? '' }}"
            data-ketua-tim-nama="{{ $p->ketua_tim_nama ?? '-' }}"
            data-layanan='@json($p->layanan ?? [])'
            data-layanan-penawaran='@json($p->layanan_penawaran ?? [])'
            data-layanan-pengujian='@json($p->layanan_pengujian ?? [])'
            data-has-perubahan-pengujian="{{ !empty($p->has_perubahan_pengujian) ? 1 : 0 }}"
            data-subtotal-penawaran="{{ $p->subtotal_penawaran ?? 0 }}"
            data-subtotal-pengujian="{{ $p->subtotal_pengujian ?? 0 }}"
            data-subtotal="{{ $p->subtotal ?? 0 }}"
            data-total="{{ $p->total ?? 0 }}"
            data-parameter-order='@json($p->parameter_order ?? [])'
            data-parameter-pengujian='@json($p->parameter_pengujian ?? [])'
            data-dokumen='@json($p->dokumen ?? [])'
            data-ketua-pcu='@json($p->ketua_pcu ?? [])'
            data-pcu='@json($p->pcu ?? [])'
            data-analis='@json($p->analis ?? [])'
            >
              &#128065;
            </button>
                  @if(auth()->user()?->role === 'superadmin')
                    <form method="POST" action="{{ route('superadmin.permohonan.destroy', $p->id) }}" onsubmit="return confirm('Hapus permohonan {{ $p->kode }}? Seluruh data permohonan dan alur kerjanya akan dihapus permanen.');" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus permohonan">
                        Hapus
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Modal Detail Permohonan -->
<div class="modal fade" id="permohonanDetailModal" tabindex="-1" aria-labelledby="permohonanDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="permohonanDetailModalLabel">Detail Permohonan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="border rounded-4 p-4 mb-4 shadow-sm bg-white">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Id Permohonan</div>
              <div class="h6 mb-0" id="detailKode">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Tanggal Permohonan</div>
              <div class="h6 mb-0" id="detailTanggal">-</div>
            </div>

            <div class="col-12">
              <div class="fw-semibold text-muted small mb-1">Progress Keseluruhan</div>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height: 10px;">
                  <div class="progress-bar" id="detailProgressBar" style="width: 0%"></div>
                </div>
                <span class="text-muted small" id="detailProgressText">0%</span>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Nama Perusahaan</div>
              <div class="h6 mb-0" id="detailPerusahaan">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Nama Penanggung Jawab</div>
              <div class="h6 mb-0" id="detailPenanggung">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Email Perusahaan</div>
              <div class="h6 mb-0" id="detailEmail">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Nomor Telepon (WA)</div>
              <div class="h6 mb-0" id="detailTelepon">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Jenis Perusahaan</div>
              <div class="h6 mb-0" id="detailJenisPerusahaan">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Provinsi</div>
              <div class="h6 mb-0" id="detailProvinsi">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Kota</div>
              <div class="h6 mb-0" id="detailKota">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Nama Penandatangan</div>
              <div class="h6 mb-0" id="detailNamaPenandatangan">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Jabatan Penandatangan</div>
              <div class="h6 mb-0" id="detailJabatanPenandatangan">-</div>
            </div>
            <div class="col-12 col-md-6">
              <div class="fw-semibold text-muted small">Penandatangan = Penanggung Jawab</div>
              <div class="h6 mb-0" id="detailPenandatanganSama">-</div>
            </div>
            <div class="col-12">
              <div class="fw-semibold text-muted small">Alamat Perusahaan</div>
              <div class="h6 mb-0" id="detailAlamat">-</div>
            </div>
          </div>
        </div>

        <div class="border rounded-4 p-4 shadow-sm bg-white">
          <h6 class="fw-semibold mb-3">Rincian Layanan</h6>
          <div id="detailServicesActiveWrap">
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:50px;">No</th>
                    <th>Layanan</th>
                    <th>Kategori</th>
                    <th style="width:80px;">Qty</th>
                    <th class="text-end" style="width:160px;">Harga Satuan</th>
                    <th class="text-end" style="width:160px;">Subtotal</th>
                  </tr>
                </thead>
                <tbody id="detailServicesBody">
                  <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div id="detailServicesCompareWrap" class="d-none">
            <div class="row g-3">
              <div class="col-12 col-lg-6">
                <div class="small fw-semibold text-muted mb-2">Parameter Saat Penawaran</div>
                <div class="table-responsive">
                  <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width:50px;">No</th>
                        <th>Parameter</th>
                        <th>Kategori</th>
                        <th style="width:80px;">Qty</th>
                      </tr>
                    </thead>
                    <tbody id="detailServicesPenawaranBody">
                      <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="small fw-semibold text-muted mb-2">Parameter Setelah Pengujian</div>
                <div class="table-responsive">
                  <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th style="width:50px;">No</th>
                        <th>Parameter</th>
                        <th>Kategori</th>
                        <th style="width:80px;">Qty</th>
                      </tr>
                    </thead>
                    <tbody id="detailServicesPengujianBody">
                      <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="row mt-3 d-none" id="detailSubtotalCompareRow">
            <div class="col-12 col-lg-6">
              <div class="border rounded-3 px-3 py-2">
                <div class="small text-muted">Subtotal Penawaran</div>
                <div class="fw-semibold text-end" id="detailSubtotalPenawaran">Rp 0</div>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="border rounded-3 px-3 py-2">
                <div class="small text-muted">Subtotal Setelah Pengujian</div>
                <div class="fw-semibold text-end" id="detailSubtotalPengujian">Rp 0</div>
              </div>
            </div>
          </div>
          <div class="row justify-content-end mt-3">
            <div class="col-md-6 col-lg-5">
              <dl class="row mb-0">
                <dt class="col-6 fw-semibold">Total:</dt>
                <dd class="col-6 text-end fw-semibold text-primary" id="detailTotal">Rp 0</dd>
              </dl>
            </div>
          </div>
        </div>

        <div class="border rounded-4 p-4 shadow-sm bg-white mt-4">
          <h6 class="fw-semibold mb-3">Dokumen</h6>
          <div id="detailDocuments"></div>
        </div>

        <div class="border rounded-4 p-4 shadow-sm bg-white mt-4">
          <h6 class="fw-semibold mb-3">Petugas yang Menangani</h6>
          <div class="row g-3">
            <div class="col-md-4">
              <div class="text-muted small fw-semibold">Ketua PCU</div>
              <ul class="list-unstyled mb-0" id="detailKetuaPcu">
                <li class="text-muted small">Belum ada ketua PCU.</li>
              </ul>
            </div>
            <div class="col-md-4">
              <div class="text-muted small fw-semibold">PCU</div>
              <ul class="list-unstyled mb-0" id="detailPcu">
                <li class="text-muted small">Belum ada PCU.</li>
              </ul>
            </div>
            <div class="col-md-4">
              <div class="text-muted small fw-semibold">Analis</div>
              <ul class="list-unstyled mb-0" id="detailAnalis">
                <li class="text-muted small">Belum ada analis.</li>
              </ul>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
  {{-- DataTables Bootstrap 5 CSS --}}
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
  <style>
    #permohonanTable th,
    #permohonanTable td {
      text-align: center;
      vertical-align: middle;
    }
    .dataTables_wrapper .pagination .page-link,
    .dt-container .pagination .page-link {
      color: #15406A !important;
    }
    .dataTables_wrapper .pagination .page-item.active .page-link,
    .dt-container .pagination .page-item.active .page-link {
      background-color: #15406A !important;
      border-color: #15406A !important;
      color: #fff !important;
    }
    .dataTables_wrapper .pagination .page-link:hover,
    .dataTables_wrapper .pagination .page-link:focus,
    .dt-container .pagination .page-link:hover,
    .dt-container .pagination .page-link:focus {
      color: #15406A !important;
      border-color: #15406A !important;
      box-shadow: none !important;
    }
  </style>
@endpush

@push('scripts')
  {{-- jQuery + DataTables + Bootstrap 5 integration --}}
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(function () {
      const table = $('#permohonanTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[4, 'desc'], [0, 'desc']], // terbaru dulu, lalu ID terbesar jika tanggal sama
        columnDefs: [
          { orderable: false, targets: [5] }, // Aksi tidak bisa sort
        ],
        language: {
          search: "Search:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          infoEmpty: "Showing 0 to 0 of 0 entries",
          zeroRecords: "No matching records found",
          paginate: { previous: "Previous", next: "Next" }
        }
      });

      const keywordInput = document.querySelector('[data-filter-keyword]');
      const tahapSelect = document.querySelector('[data-filter-tahap]');
      const statusSelect = document.querySelector('[data-filter-status]');
      const provinsiSelect = document.querySelector('[data-filter-provinsi]');
      const kotaSelect = document.querySelector('[data-filter-kota]');
      const progressMinInput = document.querySelector('[data-filter-progress-min]');
      const progressMaxInput = document.querySelector('[data-filter-progress-max]');
      const dateFromInput = document.querySelector('[data-filter-date-from]');
      const dateToInput = document.querySelector('[data-filter-date-to]');
      const resetBtn = document.querySelector('[data-filter-reset]');
      const exportBtn = document.querySelector('[data-filter-export]');
      const countEl = document.querySelector('[data-filter-count]');

      const normalize = (value) => (value || '').toString().toLowerCase().trim();
      const parseOptionalNumber = (value) => {
        if (value === null || value === undefined) return null;
        const text = value.toString().trim();
        if (text === '') return null;
        const num = Number(text);
        return Number.isNaN(num) ? null : num;
      };
      const getFilterPayload = () => ({
        keyword: normalize(keywordInput?.value),
        tahap: normalize(tahapSelect?.value),
        status: normalize(statusSelect?.value),
        provinsi: normalize(provinsiSelect?.value),
        kota: normalize(kotaSelect?.value),
        progress_min: parseOptionalNumber(progressMinInput?.value),
        progress_max: parseOptionalNumber(progressMaxInput?.value),
        date_from: normalize(dateFromInput?.value),
        date_to: normalize(dateToInput?.value),
      });
      const hasAnyFilter = (payload) => Object.entries(payload).some(([key, value]) => {
        if (key === 'progress_min' || key === 'progress_max') return value !== null;
        return (value || '') !== '';
      });

      const filterFn = function (_settings, _data, dataIndex) {
        const rowNode = table.row(dataIndex).node();
        if (!rowNode) return true;

        const keyword = normalize(keywordInput?.value);
        const tahap = normalize(tahapSelect?.value);
        const status = normalize(statusSelect?.value);
        const provinsi = normalize(provinsiSelect?.value);
        const kota = normalize(kotaSelect?.value);
        const progressMin = parseOptionalNumber(progressMinInput?.value);
        const progressMax = parseOptionalNumber(progressMaxInput?.value);
        const dateFrom = normalize(dateFromInput?.value);
        const dateTo = normalize(dateToInput?.value);

        const rowKode = normalize(rowNode.getAttribute('data-kode'));
        const rowPelanggan = normalize(rowNode.getAttribute('data-pelanggan'));
        const rowTahap = normalize(rowNode.getAttribute('data-tahap'));
        const rowStatus = normalize(rowNode.getAttribute('data-status'));
        const rowProvinsi = normalize(rowNode.getAttribute('data-provinsi'));
        const rowKota = normalize(rowNode.getAttribute('data-kota'));
        const rowProgress = Number(rowNode.getAttribute('data-progress') || 0);
        const rowDate = normalize(rowNode.getAttribute('data-tanggal-iso'));

        const passKeyword = !keyword || rowKode.includes(keyword) || rowPelanggan.includes(keyword);
        const passTahap = !tahap || rowTahap === tahap;
        const passStatus = !status || rowStatus === status;
        const passProvinsi = !provinsi || rowProvinsi === provinsi;
        const passKota = !kota || rowKota === kota;
        const passProgressMin = progressMin === null || rowProgress >= Math.max(0, Math.min(100, progressMin));
        const passProgressMax = progressMax === null || rowProgress <= Math.max(0, Math.min(100, progressMax));
        const passDateFrom = !dateFrom || (rowDate && rowDate >= dateFrom);
        const passDateTo = !dateTo || (rowDate && rowDate <= dateTo);

        return passKeyword
          && passTahap
          && passStatus
          && passProvinsi
          && passKota
          && passProgressMin
          && passProgressMax
          && passDateFrom
          && passDateTo;
      };

      $.fn.dataTable.ext.search.push(filterFn);

      const redraw = () => {
        table.draw();
        if (countEl) countEl.textContent = String(table.rows({ filter: 'applied' }).count());
      };

      [keywordInput, tahapSelect, statusSelect, provinsiSelect, kotaSelect, progressMinInput, progressMaxInput, dateFromInput, dateToInput]
        .forEach((input) => {
          if (!input) return;
          input.addEventListener('change', redraw);
          if (input.tagName === 'INPUT' && input.getAttribute('type') === 'text') {
            input.addEventListener('input', redraw);
          }
        });

      resetBtn?.addEventListener('click', () => {
        [keywordInput, tahapSelect, statusSelect, provinsiSelect, kotaSelect, progressMinInput, progressMaxInput, dateFromInput, dateToInput]
          .forEach((input) => {
            if (!input) return;
            input.value = '';
          });
        redraw();
      });

      exportBtn?.addEventListener('click', async () => {
        const exportUrl = exportBtn.getAttribute('data-export-url') || '';
        if (!exportUrl) return;

        const payload = getFilterPayload();
        const hasFilter = hasAnyFilter(payload);
        let shouldExport = true;

        if (!hasFilter) {
          if (window.Swal) {
            const result = await window.Swal.fire({
              icon: 'question',
              title: 'Export semua permohonan?',
              text: 'Filter belum diisi. Lanjut export seluruh data?',
              showCancelButton: true,
              confirmButtonText: 'Ya, export semua',
              cancelButtonText: 'Batal',
            });
            shouldExport = Boolean(result.isConfirmed);
          } else {
            shouldExport = confirm('Filter belum diisi. Export semua permohonan?');
          }
        }

        if (!shouldExport) return;

        const params = new URLSearchParams();
        Object.entries(payload).forEach(([key, value]) => {
          if (value === null || value === '') return;
          params.set(key, String(value));
        });

        window.location.href = params.toString()
          ? `${exportUrl}?${params.toString()}`
          : exportUrl;
      });

      redraw();
    });

    (() => {
      const modalEl = document.getElementById('permohonanDetailModal');
      if (!modalEl) return;

      modalEl.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const data = {
          kode: trigger.getAttribute('data-kode'),
          pelanggan: trigger.getAttribute('data-pelanggan'),
          jenis_pengujian: trigger.getAttribute('data-jenis'),
          tahap: trigger.getAttribute('data-tahap'),
          progress: trigger.getAttribute('data-progress'),
          status: trigger.getAttribute('data-status'),
          tanggal: trigger.getAttribute('data-tanggal'),
          perusahaan: trigger.getAttribute('data-perusahaan'),
          penanggung: trigger.getAttribute('data-penanggung'),
          email: trigger.getAttribute('data-email'),
          telepon: trigger.getAttribute('data-telepon'),
          alamat: trigger.getAttribute('data-alamat'),
          jenisPerusahaan: trigger.getAttribute('data-jenis-perusahaan'),
          provinsi: trigger.getAttribute('data-provinsi'),
          kota: trigger.getAttribute('data-kota'),
          lokasi: trigger.getAttribute('data-lokasi'),
          lokasiRows: trigger.getAttribute('data-lokasi-rows'),
          tanggalPengujian: trigger.getAttribute('data-tanggal-pengujian'),
          tanggalPengujianMulai: trigger.getAttribute('data-tanggal-pengujian-mulai'),
          tanggalPengujianSelesai: trigger.getAttribute('data-tanggal-pengujian-selesai'),
          penandatanganSama: trigger.getAttribute('data-penandatangan-sama') === '1',
          namaPenandatangan: trigger.getAttribute('data-nama-penandatangan'),
          jabatanPenandatangan: trigger.getAttribute('data-jabatan-penandatangan'),
          penanggungTtd: trigger.getAttribute('data-penanggung-ttd'),
          ketuaTimNama: trigger.getAttribute('data-ketua-tim-nama'),
          layanan: trigger.getAttribute('data-layanan'),
          layananPenawaran: trigger.getAttribute('data-layanan-penawaran'),
          layananPengujian: trigger.getAttribute('data-layanan-pengujian'),
          hasPerubahanPengujian: trigger.getAttribute('data-has-perubahan-pengujian') === '1',
          subtotalPenawaran: trigger.getAttribute('data-subtotal-penawaran'),
          subtotalPengujian: trigger.getAttribute('data-subtotal-pengujian'),
          subtotal: trigger.getAttribute('data-subtotal'),
          total: trigger.getAttribute('data-total'),
          parameterOrder: trigger.getAttribute('data-parameter-order'),
          parameterPengujian: trigger.getAttribute('data-parameter-pengujian'),
          dokumen: trigger.getAttribute('data-dokumen'),
          ketuaPcu: trigger.getAttribute('data-ketua-pcu'),
          pcu: trigger.getAttribute('data-pcu'),
          analis: trigger.getAttribute('data-analis'),
        };

        const setText = (selector, value) => {
          const el = modalEl.querySelector(selector);
          if (el) el.textContent = value || '-';
        };

        setText('#detailKode', data.kode);
        setText('#detailPelanggan', data.pelanggan);
        setText('#detailJenis', data.jenis_pengujian);
        setText('#detailTahap', data.tahap);
        setText('#detailStatus', data.status);
        setText('#detailTanggal', data.tanggal);
        setText('#detailPerusahaan', data.perusahaan);
        setText('#detailPenanggung', data.penanggung);
        setText('#detailEmail', data.email);
        setText('#detailTelepon', data.telepon);
        setText('#detailJenisPerusahaan', data.jenisPerusahaan);
        setText('#detailProvinsi', data.provinsi);
        setText('#detailKota', data.kota);
        setText('#detailNamaPenandatangan', data.namaPenandatangan);
        setText('#detailJabatanPenandatangan', data.jabatanPenandatangan);
        setText('#detailPenandatanganSama', data.penandatanganSama ? 'Ya' : 'Tidak');
        setText('#detailAlamat', data.alamat);

        const progressBar = modalEl.querySelector('#detailProgressBar');
        const progressText = modalEl.querySelector('#detailProgressText');
        const pct = Number.isFinite(Number(data.progress)) ? Math.max(0, Math.min(100, Number(data.progress))) : 0;
        if (progressBar) progressBar.style.width = `${pct}%`;
        if (progressText) progressText.textContent = `${pct}%`;

        const formatRupiah = (num) => {
          const n = Number(num) || 0;
          return n.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
        };

        const decodeHtml = (value) => {
          if (!value) return '';
          const textarea = document.createElement('textarea');
          textarea.innerHTML = value;
          return textarea.value;
        };

        const parseList = (raw) => {
          try {
            const decoded = decodeHtml(raw || '[]');
            return JSON.parse(decoded) || [];
          } catch (error) {
            return [];
          }
        };

        const formatTanggalDokumen = (value) => {
          const months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
          ];
          const parts = (value || '').split('-');
          if (parts.length !== 3) return value || '-';
          const year = parts[0];
          const monthIndex = Number(parts[1]) - 1;
          const day = Number(parts[2]);
          if (Number.isNaN(monthIndex) || monthIndex < 0 || monthIndex > 11 || Number.isNaN(day)) {
            return value || '-';
          }
          return `${day} ${months[monthIndex]} ${year}`;
        };

        const formatTanggalRentang = (mulai, selesai) => {
          const mulaiFmt = formatTanggalDokumen(mulai);
          const selesaiFmt = formatTanggalDokumen(selesai);
          if (!mulai || !selesai || mulai === '-' || selesai === '-') return mulaiFmt;
          if (mulai === selesai) return mulaiFmt;
          return `${mulaiFmt} - ${selesaiFmt}`;
        };

        const computeChanges = (orderParams, testParams) => {
          const normalizeName = (value) => String(value || '').trim().toLowerCase();
          const buildQtyMap = (items) => {
            const map = new Map();
            (items || []).forEach((item) => {
              const name = String(item?.nama || '').trim();
              if (!name) return;
              const key = normalizeName(name);
              const qty = Number(item?.qty || 0) || 0;
              map.set(key, {
                name,
                qty: (map.get(key)?.qty || 0) + qty,
              });
            });
            return map;
          };

          const orderMap = buildQtyMap(orderParams);
          const testMap = buildQtyMap(testParams);
          const orderedKeys = Array.from(new Set([
            ...Array.from(orderMap.keys()),
            ...Array.from(testMap.keys()),
          ]));

          return orderedKeys.map((key) => {
            const before = orderMap.get(key)?.qty || 0;
            const after = testMap.get(key)?.qty || 0;
            if (before === after) return null;
            const name = orderMap.get(key)?.name || testMap.get(key)?.name || '-';
            return {
              name,
              tambah: after > before ? after - before : 0,
              kurang: before > after ? before - after : 0,
              total: after,
            };
          }).filter(Boolean);
        };

        const computeChangedKeys = (orderParams, testParams) => {
          const normalizeName = (value) => String(value || '').trim().toLowerCase();
          const buildQtyMap = (items) => {
            const map = new Map();
            (items || []).forEach((item) => {
              const name = String(item?.nama || '').trim();
              if (!name) return;
              const key = normalizeName(name);
              const qty = Number(item?.qty || 0) || 0;
              map.set(key, {
                name,
                qty: (map.get(key)?.qty || 0) + qty,
              });
            });
            return map;
          };

          const orderMap = buildQtyMap(orderParams);
          const testMap = buildQtyMap(testParams);
          return new Set([
            ...Array.from(orderMap.keys()),
            ...Array.from(testMap.keys()),
          ].filter((key) => ((orderMap.get(key)?.qty || 0) !== (testMap.get(key)?.qty || 0))));
        };

        const openPrintWindow = (html) => {
          const w = window.open('about:blank', '_blank');
          if (!w) {
            alert('Popup diblokir. Izinkan popup untuk membuka dokumen.');
            return null;
          }
          w.document.write(html);
          w.document.close();
          return w;
        };

        let layananPenawaran = [];
        let layananPengujian = [];
        let layananAktif = [];
        try {
          layananPenawaran = JSON.parse(data.layananPenawaran || '[]');
        } catch (err) {
          console.warn('Gagal parse layanan penawaran', err);
        }
        try {
          layananPengujian = JSON.parse(data.layananPengujian || '[]');
        } catch (err) {
          console.warn('Gagal parse layanan pengujian', err);
        }
        try {
          layananAktif = JSON.parse(data.layanan || '[]');
        } catch (err) {
          console.warn('Gagal parse layanan', err);
        }
        if (!Array.isArray(layananAktif) || layananAktif.length === 0) {
          layananAktif = data.hasPerubahanPengujian ? layananPengujian : layananPenawaran;
        }

        const renderActiveRows = (items) => {
          const servicesBody = modalEl.querySelector('#detailServicesBody');
          if (!servicesBody) return;
          if (!Array.isArray(items) || items.length === 0) {
            servicesBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
            return;
          }
          servicesBody.innerHTML = items.map((item, idx) => {
            const qty = Number(item.qty) || 0;
            const harga = Number(item.harga) || 0;
            const subtotal = Number(item.subtotal) || qty * harga;
            return `
              <tr>
                <td>${idx + 1}</td>
                <td>${item.nama || '-'}</td>
                <td>${item.kategori || '-'}</td>
                <td>${qty}</td>
                <td class="text-end">${formatRupiah(harga)}</td>
                <td class="text-end">${formatRupiah(subtotal)}</td>
              </tr>
            `;
          }).join('');
        };

        const renderSimpleRows = (selector, items) => {
          const body = modalEl.querySelector(selector);
          if (!body) return;
          if (!Array.isArray(items) || items.length === 0) {
            body.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
            return;
          }
          body.innerHTML = items.map((item, idx) => `
            <tr>
              <td>${idx + 1}</td>
              <td>${item.nama || '-'}</td>
              <td>${item.kategori || '-'}</td>
              <td>${Number(item.qty) || 0}</td>
            </tr>
          `).join('');
        };

        const subtotalCompareRow = modalEl.querySelector('#detailSubtotalCompareRow');
        const activeWrap = modalEl.querySelector('#detailServicesActiveWrap');
        const compareWrap = modalEl.querySelector('#detailServicesCompareWrap');

        if (data.hasPerubahanPengujian) {
          if (activeWrap) activeWrap.classList.add('d-none');
          if (compareWrap) compareWrap.classList.remove('d-none');
          renderSimpleRows('#detailServicesPenawaranBody', layananPenawaran);
          renderSimpleRows('#detailServicesPengujianBody', layananPengujian);
          if (subtotalCompareRow) subtotalCompareRow.classList.remove('d-none');
          setText('#detailSubtotalPenawaran', formatRupiah(data.subtotalPenawaran));
          setText('#detailSubtotalPengujian', formatRupiah(data.subtotalPengujian));
        } else {
          if (activeWrap) activeWrap.classList.remove('d-none');
          if (compareWrap) compareWrap.classList.add('d-none');
          renderActiveRows(layananAktif);
          if (subtotalCompareRow) subtotalCompareRow.classList.add('d-none');
          setText('#detailSubtotalPenawaran', 'Rp 0');
          setText('#detailSubtotalPengujian', 'Rp 0');
        }

        setText('#detailTotal', formatRupiah(data.total));

        const docList = modalEl.querySelector('#detailDocuments');
        if (docList) {
          let docs = [];
          try {
            docs = JSON.parse(data.dokumen || '[]');
          } catch (err) {
            console.warn('Gagal parse dokumen', err);
          }

          if (!Array.isArray(docs) || docs.length === 0) {
            docList.innerHTML = '<div class="text-muted">Tidak ada dokumen.</div>';
          } else {
            docList.innerHTML = docs.map((doc, idx) => {
              const action = doc.action || '';
              if (action) {
                return `
                  <button
                    type="button"
                    class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 w-100 bg-white"
                    data-detail-doc-action="${action}"
                    data-detail-doc-index="${idx}"
                  >
                    <span class="fw-semibold text-secondary">${doc.nama || 'Unduh Dokumen'}</span>
                    <span class="fs-5 text-primary">&#128190;</span>
                  </button>
                `;
              }

              return `
                <a href="${doc.url || '#'}" target="_blank" rel="noopener noreferrer" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 text-decoration-none">
                  <span class="fw-semibold text-secondary">${doc.nama || 'Unduh Dokumen'}</span>
                  <span class="fs-5 text-primary">&#128190;</span>
                </a>
              `;
            }).join('');

            docList.querySelectorAll('[data-detail-doc-action]').forEach((btn) => {
              btn.addEventListener('click', () => {
                const action = btn.getAttribute('data-detail-doc-action') || '';
                const orderParams = parseList(data.parameterOrder || '[]');
                const testParams = parseList(data.parameterPengujian || '[]');
                const lokasiRows = parseList(data.lokasiRows || '[]');
                const changes = computeChanges(orderParams, testParams);
                if (action === 'penambahan' && changes.length === 0) return;
                const tanggalMulaiRaw = data.tanggalPengujianMulai || data.tanggalPengujian || '-';
                const tanggalSelesaiRaw = data.tanggalPengujianSelesai || tanggalMulaiRaw;
                const tanggal = formatTanggalRentang(tanggalMulaiRaw, tanggalSelesaiRaw);
                const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;

                if (action === 'bap') {
                  const samplingTypes = Array.from(new Set(
                    (Array.isArray(testParams) ? testParams : [])
                      .map((item) => item?.kategori || '-')
                      .filter((item) => item && item !== '-')
                  ));
                  const samplingText = (() => {
                    if (samplingTypes.length === 2) return `${samplingTypes[0]} dan ${samplingTypes[1]}`;
                    if (samplingTypes.length > 0) return samplingTypes.join(', ');
                    return 'Emisi, Ambien dan Lingkungan Kerja';
                  })();
                  openPrintWindow(`
                    <html>
                      <head>
                        <title>BAP ${data.kode || ''}</title>
                        <style>
                          @page { margin: 24px 2cm 70px 2cm; }
                          html, body { height: 100%; }
                          body { font-family: "Times New Roman", serif; padding: 24px 2cm; color: #111; }
                          .page { min-height: 100%; position: relative; }
                          .page-body { padding-bottom: 70px; }
                          .page-footer { position: fixed; left: 2cm; right: 2cm; bottom: 24px; }
                          @media print { html, body { height: auto; } body { padding: 0; } .page { min-height: 0; page-break-after: avoid; } .page-body { padding-bottom: 60px; } }
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
                          .header-text div { font-size: 14px; font-weight: 700; text-transform: uppercase; }
                          .header-text div:first-child { font-size: 12px; font-weight: 500; }
                          .header-text div:nth-child(2) { font-size: 14px; font-weight: 700; white-space: nowrap; }
                          .header-text div:nth-child(4) { font-size: 17px; font-weight: 700; color: #163e67; line-height: 1.05; margin-top: 3px; white-space: nowrap; }
                          .header-text .addr {
                            font-size: 11px;
                            font-weight: 400;
                            margin-top: 3px;
                            text-transform: none;
                            font-family: Arial, sans-serif;
                          }
                          .header-text .addr .icon { color: #163e67; font-weight: 700; margin: 0 2px; }
                          .header-line { border-top: 2px solid #000; margin: 10px 0 18px; }
                          .title { text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 0.5px; margin-bottom: 16px; }
                          .paragraph { font-size: 13px; line-height: 1.6; }
                          .list { margin: 14px 0 18px 30px; font-size: 13px; }
                          .list li { margin-bottom: 6px; }
                          .data-list { margin: 10px 0 16px 20px; font-size: 13px; }
                          .data-list .row { display: flex; gap: 6px; margin-bottom: 8px; }
                          .data-list .label { width: 170px; }
                          .data-list .colon { width: 10px; }
                          .signatures { display: flex; justify-content: space-between; margin-top: 22px; font-size: 13px; }
                          .sign-col { width: 45%; text-align: center; }
                          .sign-space { height: 70px; }
                          .signature-img { display: block; margin: 6px auto 2px; max-height: 70px; max-width: 220px; object-fit: contain; }
                          .sign-name { margin-top: 4px; font-weight: bold; }
                          .footer-line { border-top: 1px solid #000; margin-top: 12px; }
                          .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 4px; }
                        </style>
                      </head>
                      <body>
                        <div class="page">
                          <div class="page-body">
                            <div class="letter-header">
                              <img src="${logoUrl}" alt="Logo" class="logo" id="printLogo">
                              <div class="header-text">
                                <div>KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
                                <div>DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
                                <div>DAN KESELAMATAN DAN KESEHATAN KERJA</div>
                                <div>BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
                                <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id</div>
                              </div>
                            </div>
                            <div class="header-line"></div>
                            <div class="title">BERITA ACARA PENGAMBILAN SAMPEL</div>
                            <div class="paragraph">Pada hari ini ${formatTanggalDokumen(tanggalMulaiRaw)}, kami nama :</div>
                            <ol class="list">
                              ${(JSON.parse(data.pcu || '[]').length ? JSON.parse(data.pcu || '[]') : ['......................................................']).map((name) => `<li>${name}</li>`).join('')}
                            </ol>
                            <div class="paragraph">Petugas pengambil sampel udara dari BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA, telah melakukan pengambilan sampel: ${samplingText} dengan rincian sebagaimana pada lampiran dari :</div>
                            <div class="data-list">
                              <div class="row"><span class="label">1. Nama Perusahaan</span><span class="colon">:</span><span>${data.perusahaan || '-'}</span></div>
                              <div class="row"><span class="label">2. Alamat Perusahaan</span><span class="colon">:</span><span>${data.alamat || '-'}</span></div>
                              <div class="row"><span class="label">3. Jenis Perusahaan</span><span class="colon">:</span><span>${data.jenisPerusahaan || '-'}</span></div>
                              <div class="row"><span class="label">4. Lokasi Pengujian</span><span class="colon">:</span><span>${data.lokasi || '-'}</span></div>
                            </div>
                            <div class="paragraph">Demikian Berita Acara ini dibuat dengan sebenar - benarnya dan tanpa paksaan.</div>
                            <div class="signatures">
                              <div class="sign-col">
                                <div>Mengetahui :</div>
                                <div>Pimpinan Perusahaan/</div>
                                <div>Yang Mewakili</div>
                                ${data.penanggungTtd ? `<img src="${data.penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                                <div class="sign-name">(${data.penanggung || '.................................'})</div>
                              </div>
                              <div class="sign-col">
                                <div>Petugas Pengambil Sampel</div>
                                <div>Ketua Tim</div>
                                <div class="sign-space"></div>
                                <div class="sign-name">(${data.ketuaTimNama || '......................................................'})</div>
                              </div>
                            </div>
                          </div>
                          <div class="page-footer">
                            <div class="footer-line"></div>
                            <div class="footer-meta">
                              <span>Tgl. terbit: 24 Desember 2024</span>
                              <span>No.: /F/7.3.12/BK3-SBY</span>
                            </div>
                          </div>
                        </div>
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
                  return;
                }

                if (action === 'rincian') {
                  const groupedByJenis = new Map();
                  (Array.isArray(lokasiRows) ? lokasiRows : []).forEach((row) => {
                    const rowLokasi = row?.lokasi || data.lokasi || '-';
                    const allParams = [];
                    (row?.dokumen_list || []).forEach((doc) => {
                      (doc?.parameter || []).forEach((param) => allParams.push(param));
                    });
                    allParams.forEach((item) => {
                      const jenis = item?.kategori || '-';
                      if (!groupedByJenis.has(jenis)) {
                        groupedByJenis.set(jenis, { lokasiSet: new Set(), params: [], notes: [] });
                      }
                      const group = groupedByJenis.get(jenis);
                      group.lokasiSet.add(rowLokasi);
                      if (item?.nama) group.params.push(item.nama);
                      if (item?.catatan) group.notes.push(item.catatan);
                    });
                  });
                  const rowsHtml = Array.from(groupedByJenis.entries()).map(([jenis, group], idx) => `
                      <tr>
                        <td class="col-no">${idx + 1}</td>
                        <td>${Array.from(group.lokasiSet).join(', ') || data.lokasi || '-'}</td>
                        <td>${jenis || '-'}</td>
                        <td>${Array.from(new Set(group.params)).join(', ') || '-'}</td>
                        <td><div class="note-text">${Array.from(new Set(group.notes)).join('<br>')}</div></td>
                      </tr>
                  `).join('') || `
                    <tr>
                      <td class="col-no">1</td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                      <td><div class="note-text"></div></td>
                    </tr>
                  `;
                  openPrintWindow(`
                    <html>
                      <head>
                        <title>Rincian Pengambilan Sampel</title>
                        <style>
                          @page { margin: 18px 2cm 50px 2cm; }
                          html, body { height: 100%; }
                          body { font-family: "Times New Roman", serif; padding: 18px 2cm; color: #111; }
                          .page { min-height: 100%; position: relative; }
                          .page-body { padding-bottom: 50px; }
                          .page-footer { position: fixed; left: 2cm; right: 2cm; bottom: 18px; }
                          @media print { html, body { height: auto; } body { padding: 0; } .page { min-height: 0; page-break-after: avoid; } .page-body { padding-bottom: 44px; } }
                          .doc-header { display: grid; grid-template-columns: 110px 1fr 120px; border: 1px solid #000; }
                          .doc-header > div { border-right: 1px solid #000; }
                          .doc-header > div:last-child { border-right: none; }
                          .logo-cell { display: flex; align-items: center; justify-content: center; padding: 10px; }
                          .logo-cell img { width: 64px; height: 64px; object-fit: contain; }
                          .head-text {
                            text-align: left;
                            padding: 8px 8px 7px;
                            font-size: 12px;
                            font-weight: 700;
                            line-height: 1.1;
                            font-family: "Arial Narrow", Arial, sans-serif;
                            border-left: 2px solid #163e67;
                          }
                          .head-text .kop-dir { white-space: nowrap; display: inline-block; font-size: 12px; }
                          .head-text .kop-main { color: #163e67; font-size: 13px; line-height: 1.02; display: inline-block; margin-top: 2px; white-space: nowrap; }
                          .head-text .kop-contact { font-size: 10px; font-weight: 500; font-family: Arial, sans-serif; text-transform: none; }
                          .meta-cell { display: grid; grid-template-rows: 1fr 1fr; font-size: 12px; }
                          .meta-cell div { border-bottom: 1px solid #000; padding: 6px 8px; display: flex; align-items: center; justify-content: center; text-align: center; }
                          .meta-cell div:last-child { border-bottom: none; }
                          .title { text-align: center; font-weight: bold; font-size: 13px; margin: 10px 0 8px; letter-spacing: 0.6px; }
                          .info-line { font-size: 12px; margin: 4px 0; }
                          .info-line .label { display: inline-block; width: 160px; }
                          .info-line .colon { display: inline-block; width: 10px; }
                          .info-line .value { display: inline-block; min-width: 260px; }
                          .note-text { min-height: 12px; font-size: 12px; padding: 2px 0; }
                          table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
                          th, td { border: 1px solid #000; padding: 4px 5px; height: 20px; }
                          th { text-align: center; font-weight: bold; }
                          .col-no { width: 6%; text-align: center; }
                          .signatures { display: flex; justify-content: space-between; margin-top: 22px; font-size: 12px; }
                          .sign-col { width: 45%; text-align: center; }
                          .sign-space { height: 56px; }
                          .signature-img { display: block; margin: 6px auto 2px; max-height: 56px; max-width: 200px; object-fit: contain; }
                          .sign-name { margin-top: 4px; font-weight: bold; }
                          .notes { font-size: 11px; border-top: 2px solid #000; padding-top: 6px; margin-top: 16px; }
                          .footer-line { border-top: 1px solid #000; margin-top: 8px; }
                          .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 6px; }
                        </style>
                      </head>
                      <body>
                        <div class="page">
                          <div class="page-body">
                            <div class="doc-header">
                              <div class="logo-cell"><img src="${logoUrl}" alt="Logo" id="printLogo"></div>
                              <div class="head-text">
                                KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA<br>
                                <span class="kop-dir">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</span><br>
                                DAN KESELAMATAN DAN KESEHATAN KERJA<br>
                                <span class="kop-main">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</span><br>
                                <span class="kop-contact">Jl. Dukuh Menanggal No. 122 Surabaya, (031) 8280440, balaik3surabaya@kemnaker.go.id</span>
                              </div>
                              <div class="meta-cell"><div>Page : 1/1</div><div>Rev/Terb. : -/1</div></div>
                            </div>
                            <div class="title">RINCIAN&nbsp;&nbsp;PENGAMBILAN&nbsp;&nbsp;SAMPEL</div>
                            <div class="info-line"><span class="label">Nama Perusahaan</span><span class="colon">:</span><span class="value">${data.perusahaan || '-'}</span></div>
                            <div class="info-line"><span class="label">Tanggal Pengujian</span><span class="colon">:</span><span class="value">${tanggal}</span></div>
                            <table>
                              <thead>
                                <tr><th class="col-no">No.</th><th>Lokasi</th><th>Jenis Pengukuran</th><th>Parameter</th><th>Keterangan</th></tr>
                              </thead>
                              <tbody>${rowsHtml}</tbody>
                            </table>
                            <div class="signatures">
                              <div class="sign-col">
                                <div>Mengetahui :</div>
                                <div>Pimpinan Perusahaan/</div>
                                <div>Yang Mewakili</div>
                                ${data.penanggungTtd ? `<img src="${data.penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                                <div class="sign-name">(${data.penanggung || '.................................'})</div>
                              </div>
                              <div class="sign-col">
                                <div>Petugas Pengambil Sampel</div>
                                <div>Ketua Tim</div>
                                <div class="sign-space"></div>
                                <div class="sign-name">(${data.ketuaTimNama || '......................................................'})</div>
                              </div>
                            </div>
                            <div class="notes">Keterangan : jenis pengukuran; E /A /LK, lokasi: namalokasi/cerobong, parameter: di isi semua parameter (fisik, kimia), jam pengukuran: (ke 1, 2, 3), keterangan: di isi informasi penting di perusahaan</div>
                          </div>
                          <div class="page-footer">
                            <div class="footer-meta"><span>Tgl. terbit: 24 Desember 2024</span><span>No. : F/7.3.13/BK3-SBY</span></div>
                            <div class="footer-line"></div>
                          </div>
                        </div>
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
                  return;
                }

                const overallTestParams = parseList(data.parameterPengujian || '[]');
                const changedKeys = computeChangedKeys(orderParams, overallTestParams);
                const changesByLokasi = [];
                const renderedKeys = new Set();
                (lokasiRows || []).forEach((row) => {
                  const rowMap = new Map();
                  (row?.dokumen_list || []).forEach((doc) => {
                    (doc?.parameter || []).forEach((param) => {
                      const key = String(param?.nama || '').trim().toLowerCase();
                      if (!changedKeys.has(key)) return;
                      const existing = rowMap.get(key) || {
                        name: param?.nama || '-',
                        total: 0,
                      };
                      existing.total += Number(param?.qty || 0) || 0;
                      rowMap.set(key, existing);
                    });
                  });
                  const rowChanges = Array.from(rowMap.entries()).map(([key, item]) => {
                    renderedKeys.add(key);
                    return item;
                  });
                  if (rowChanges.length > 0) {
                    changesByLokasi.push({
                      lokasi: row?.lokasi || '-',
                      changes: rowChanges,
                    });
                  }
                });
                if (changesByLokasi.length === 0 || renderedKeys.size < changedKeys.size) {
                  const overallChanges = computeChanges(orderParams, overallTestParams)
                    .filter((item) => !renderedKeys.has(String(item?.name || '').trim().toLowerCase()));
                  if (overallChanges.length > 0) {
                    changesByLokasi.push({
                      lokasi: data.lokasi || '-',
                      changes: overallChanges,
                    });
                  }
                }
                let counter = 1;
                const rowsHtml = changesByLokasi.length
                  ? changesByLokasi.map((row) => row.changes.map((item) => `
                    <tr>
                      <td class="col-no">${counter++}</td>
                      <td>${getJenisPengukuran(item.name, item.kategori || '')}</td>
                      <td>${row.lokasi}</td>
                      <td>${item.name || '-'}</td>
                      <td></td>
                      <td></td>
                      <td>${item.tambah ? item.tambah : (item.kurang ? item.kurang : (typeof item.total === 'number' ? item.total : ''))}</td>
                    </tr>
                  `).join('')).join('')
                  : `
                    <tr>
                      <td class="col-no"></td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                      <td></td>
                      <td></td>
                      <td></td>
                    </tr>
                  `;
                openPrintWindow(`
                  <html>
                    <head>
                      <title>Penambahan/Pengurangan Pekerjaan Pengujian</title>
                      <style>
                        @page { margin: 18px 2cm 50px 2cm; }
                        html, body { height: 100%; }
                        body { font-family: "Times New Roman", serif; padding: 18px 2cm; color: #111; }
                        .page { min-height: 100%; position: relative; }
                        .page-body { padding-bottom: 50px; }
                        .page-footer {
                          position: fixed;
                          left: 2cm;
                          right: 2cm;
                          bottom: 18px;
                        }
                        @media print {
                          html, body { height: auto; }
                          body { padding: 0; }
                          .page { min-height: 0; page-break-after: avoid; }
                          .page-body { padding-bottom: 44px; }
                        }
                        .doc-header { display: grid; grid-template-columns: 110px 1fr 120px; border: 1px solid #000; }
                        .doc-header > div { border-right: 1px solid #000; }
                        .doc-header > div:last-child { border-right: none; }
                        .logo-cell { display: flex; align-items: center; justify-content: center; padding: 10px; }
                        .logo-cell img { width: 64px; height: 64px; object-fit: contain; }
                        .head-text {
                          text-align: left;
                          padding: 8px 8px 7px;
                          font-size: 12px;
                          font-weight: 700;
                          line-height: 1.1;
                          font-family: "Arial Narrow", Arial, sans-serif;
                          border-left: 2px solid #163e67;
                        }
                        .head-text .kop-dir { white-space: nowrap; display: inline-block; font-size: 12px; }
                          .head-text .kop-main { color: #163e67; font-size: 13px; line-height: 1.02; display: inline-block; margin-top: 2px; white-space: nowrap; }
                        .head-text .kop-contact { font-size: 10px; font-weight: 500; font-family: Arial, sans-serif; text-transform: none; }
                        .meta-cell { display: grid; grid-template-rows: 1fr 1fr; font-size: 12px; }
                        .meta-cell div { border-bottom: 1px solid #000; padding: 6px 8px; display: flex; align-items: center; justify-content: center; text-align: center; }
                        .meta-cell div:last-child { border-bottom: none; }
                        .title { text-align: center; font-weight: bold; font-size: 13px; margin: 10px 0 10px; letter-spacing: 0.6px; text-decoration: underline; }
                        .info-list { margin-top: 6px; font-size: 12px; }
                        .info-row { display: flex; gap: 8px; margin: 6px 0; }
                        .info-row .label { width: 180px; }
                        .info-row .colon { width: 10px; }
                        .info-row .value { flex: 1; }
                        .paragraph { font-size: 12px; line-height: 1.6; margin: 10px 0; text-align: justify; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
                        th, td { border: 1px solid #000; padding: 4px 5px; height: 20px; }
                        th { text-align: center; font-weight: bold; }
                        .col-no { width: 6%; text-align: center; }
                        .col-jenis { width: 22%; }
                        .col-lokasi { width: 14%; }
                        .col-parameter { width: 14%; }
                        .col-tambah { width: 16%; }
                        .col-kurang { width: 16%; }
                        .col-jumlah { width: 12%; }
                        .signatures { display: flex; justify-content: space-between; margin-top: 16px; font-size: 12px; }
                        .sign-col { width: 45%; text-align: center; }
                        .sign-space { height: 56px; }
                        .signature-img { display: block; margin: 6px auto 2px; max-height: 56px; max-width: 200px; object-fit: contain; }
                        .sign-name { margin-top: 4px; font-weight: bold; }
                        .footer-line { border-top: 1px solid #000; margin-top: 8px; }
                        .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 6px; }
                      </style>
                    </head>
                    <body>
                      <div class="page">
                        <div class="page-body">
                          <div class="doc-header">
                            <div class="logo-cell"><img src="${logoUrl}" alt="Logo" id="printLogo"></div>
                            <div class="head-text">
                              KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA<br>
                              <span class="kop-dir">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</span><br>
                              DAN KESELAMATAN DAN KESEHATAN KERJA<br>
                              <span class="kop-main">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</span><br>
                              <span class="kop-contact">Jl. Dukuh Menanggal No. 122 Surabaya, (031) 8280440, balaik3surabaya@kemnaker.go.id</span>
                            </div>
                            <div class="meta-cell"><div>Page : 1/1</div><div>Rev/Terb. : 1/1</div></div>
                          </div>
                          <div class="title">PENAMBAHAN / PENGURANGAN PEKERJAAN PENGUJIAN</div>
                          <div class="info-list">
                            <div class="info-row"><span class="label">Nama Perusahaan</span><span class="colon">:</span><span class="value">${data.perusahaan || '-'}</span></div>
                            <div class="info-row"><span class="label">Alamat Perusahaan</span><span class="colon">:</span><span class="value">${data.alamat || '-'}</span></div>
                            <div class="info-row"><span class="label">Tanggal Pengukuran</span><span class="colon">:</span><span class="value">${tanggal}</span></div>
                          </div>
                          <div class="paragraph">
                            Bahwa dengan persetujuan manajer Mutu dan atau Manajer Teknis serta Bapak/Ibu
                            ${data.penanggung || '..........................'} sebagai penanggung jawab ${data.perusahaan || '-'}, Maka Petugas
                            Pengambil Sampel Balai Higiene dan Keselamatan Kerja Surabaya melakukan pengambilan
                            sampel yang merupakan penambahan / pengurangan dari pekerjaan yang telah disetujui
                            sebelumnya, yang meliputi :
                          </div>
                          <table>
                            <thead>
                              <tr>
                                <th class="col-no">No.</th>
                                <th class="col-jenis">Jenis Pengukuran</th>
                                <th class="col-lokasi">Lokasi</th>
                                <th class="col-parameter">Parameter</th>
                                <th class="col-tambah">Penambahan</th>
                                <th class="col-kurang">Pengurangan</th>
                                <th class="col-jumlah">Jumlah</th>
                              </tr>
                            </thead>
                            <tbody>${rowsHtml}</tbody>
                          </table>
                          <div class="signatures">
                            <div class="sign-col">
                              <div>Mengetahui :</div>
                              <div>Pimpinan Perusahaan/</div>
                              <div>Yang Mewakili</div>
                              ${data.penanggungTtd ? `<img src="${data.penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                              <div class="sign-name">(${data.penanggung || '.................................'})</div>
                            </div>
                            <div class="sign-col">
                              <div>Surabaya, ${tanggal}</div>
                              <div>Petugas Pengambil Sampel</div>
                              <div>Ketua Tim</div>
                              <div class="sign-space"></div>
                              <div class="sign-name">(${data.ketuaTimNama || '......................................................'})</div>
                            </div>
                          </div>
                        </div>
                        <div class="page-footer">
                          <div class="footer-meta"><span>Tgl. terbit: 24 Desember 2024</span><span>No. : F/7.3.13/BK3-SBY</span></div>
                          <div class="footer-line"></div>
                        </div>
                      </div>
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
              });
            });
          }
        }

        const renderList = (selector, items, emptyLabel) => {
          const list = modalEl.querySelector(selector);
          if (!list) return;
          list.innerHTML = '';
          if (!Array.isArray(items) || items.length === 0) {
            const li = document.createElement('li');
            li.className = 'text-muted small';
            li.textContent = emptyLabel;
            list.appendChild(li);
            return;
          }
          items.forEach((name) => {
            const li = document.createElement('li');
            li.className = 'border-bottom py-1';
            li.textContent = name;
            list.appendChild(li);
          });
        };

        let ketuaPcu = [];
        let pcu = [];
        let analis = [];
        try { ketuaPcu = JSON.parse(data.ketuaPcu || '[]'); } catch (err) { ketuaPcu = []; }
        try { pcu = JSON.parse(data.pcu || '[]'); } catch (err) { pcu = []; }
        try { analis = JSON.parse(data.analis || '[]'); } catch (err) { analis = []; }
        renderList('#detailKetuaPcu', ketuaPcu, 'Belum ada ketua PCU.');
        renderList('#detailPcu', pcu, 'Belum ada PCU.');
        renderList('#detailAnalis', analis, 'Belum ada analis.');
      });
    })();
  </script>
@endpush
