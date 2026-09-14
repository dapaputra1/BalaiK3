@extends('layouts.app_admin')

@section('content_admin')
  @php
    $pcuOptions = isset($pcu_options) ? collect($pcu_options)->map(function ($row) {
      return ['id' => $row->id, 'name' => $row->name];
    })->values()->all() : [];
    $canEdit = in_array(optional(auth()->user())->role, ['superadmin', 'penyelia'], true);
    $jadwal = $penjadwalan ?? collect();
    $waitingUserApproval = $waiting_user_approval ?? collect();
    $approvedUserSchedule = $approved_user_schedule ?? collect();
    $revisedByUserCards = $jadwal->filter(function ($item) {
      $message = trim((string) ($item['return_message'] ?? ''));
      return str_starts_with($message, 'Dikembalikan ke penjadwalan dari user');
    })->values();
    $jadwalButuhPenjadwalan = $jadwal->reject(function ($item) {
      $message = trim((string) ($item['return_message'] ?? ''));
      return str_starts_with($message, 'Dikembalikan ke penjadwalan dari user');
    })->values();
    $menungguKonfirmasiCards = $waitingUserApproval->concat($revisedByUserCards)->values();
    $allJadwalCards = $jadwalButuhPenjadwalan
      ->concat($menungguKonfirmasiCards)
      ->concat($approvedUserSchedule)
      ->values();
    $jadwalPengumuman = $penjadwalan_pengumuman ?? $jadwal;
    $pengumumanSource = collect($jadwalPengumuman)->map(function ($item) {
      $params = collect($item['parameter'] ?? []);
      $kategoriLabels = $params->pluck('kategori')
        ->filter()
        ->map(function ($kategori) {
          $text = trim((string) $kategori);
          return match (strtoupper($text)) {
            'LK' => 'Lingkungan Kerja',
            'A' => 'Ambien',
            default => $text,
          };
        })
        ->unique()
        ->values()
        ->all();
      $pcu = collect($item['pcu'] ?? [])->map(function ($row) {
        return is_array($row) ? $row : ['id' => null, 'name' => $row, 'is_leader' => false];
      });
      $pcuNames = $pcu->pluck('name')->filter()->values()->all();
      $orderedPcu = array_values(array_unique(array_filter(array_merge(
        [trim((string) ($item['ketua_pcu_nama'] ?? ''))],
        $pcuNames
      ))));

      return [
        'id' => (string) ($item['permohonan_id'] ?? ($item['kode'] ?? '')),
        'pelanggan' => $item['pelanggan'] ?? '-',
        'tanggal' => $item['tanggal'] ?? '',
        'tanggal_selesai' => $item['tanggal_selesai'] ?? '',
        'kategori_labels' => $kategoriLabels,
        'pelaksana' => $orderedPcu,
      ];
    })->values();
  @endphp

  @include('admin.partials.workflow_header', [
    'title' => 'Alur Kerja - Penjadwalan Pengujian',
    'subtitle' => 'Daftar jadwal pengujian, lokasi, parameter, dan petugas PCU.',
    'total' => $allJadwalCards->count(),
  ])

  @php
    $flashSuccess = session('success');
    $flashError = session('error');
  @endphp


  <div class="card border-0 shadow-sm rounded-4 mb-4 penjadwalan-search-card">
    <div class="card-body p-3 p-lg-4">
      <div class="row g-2">
        <div class="col-12 col-md-4">
          <div class="penjadwalan-search-input-wrap">
            <i class="bi bi-calendar-event penjadwalan-search-icon"></i>
            <input type="date" class="form-control form-control-sm penjadwalan-search-input" data-search-tanggal>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="penjadwalan-search-input-wrap">
            <i class="bi bi-search penjadwalan-search-icon"></i>
            <input type="text" class="form-control form-control-sm penjadwalan-search-input" placeholder="Cari berdasarkan pelanggan" data-search-pelanggan>
          </div>
        </div>
        <div class="col-12 col-md-2 d-flex align-items-end">
          <button type="button" class="btn btn-outline-secondary w-100 btn-sm penjadwalan-search-reset" data-search-reset>
            <i class="bi bi-arrow-clockwise me-1"></i>Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 mb-3">
    <button type="button" class="btn btn-deep-blue btn-sm active penjadwalan-stage-filter-btn" data-stage-filter="butuh_penjadwalan">
      Butuh Penjadwalan
      @if($jadwalButuhPenjadwalan->count() > 0)
        <span class="badge rounded-pill bg-danger ms-1">{{ $jadwalButuhPenjadwalan->count() }}</span>
      @endif
    </button>
    <button type="button" class="btn btn-outline-deep-blue btn-sm penjadwalan-stage-filter-btn" data-stage-filter="menunggu_acc_user">
      Menunggu Konfirmasi Pelanggan
      @if($menungguKonfirmasiCards->count() > 0)
        <span class="badge rounded-pill bg-danger ms-1">{{ $menungguKonfirmasiCards->count() }}</span>
      @endif
    </button>
    <button type="button" class="btn btn-outline-deep-blue btn-sm penjadwalan-stage-filter-btn" data-stage-filter="sudah_acc_user">
      Sudah di ACC User
      @if($approvedUserSchedule->count() > 0)
        <span class="badge rounded-pill bg-danger ms-1">{{ $approvedUserSchedule->count() }}</span>
      @endif
    </button>
  </div>

  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
          <div class="fw-semibold">Form Pengumuman Jadwal Sampling</div>
        </div>
        <div class="d-flex justify-content-md-end">
          <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#pengumumanFormModal"
          >
            <i class="bi bi-file-earmark-text me-1"></i>Buat Form
          </button>
        </div>
      </div>
    </div>
  </div>
  <script type="application/json" id="pengumumanSourceData">@json($pengumumanSource)</script>

  <div class="accordion penjadwalan-accordion" id="penjadwalanAccordion">
    @forelse($allJadwalCards as $item)
      @php
        $routePrefix = auth()->user()?->role === 'penyelia' ? 'penyelia' : 'superadmin';
        $params = collect($item['parameter'] ?? []);
        $pcu = collect($item['pcu'] ?? [])->map(function ($row) {
          return is_array($row) ? $row : ['id' => null, 'name' => $row];
        });
        $tanggalAkhir = $item['tanggal_selesai'] ?? ($item['tanggal'] ?? '');
        $pcuNames = $pcu->pluck('name')->filter()->values()->all();
        $pcuIds = $pcu->pluck('id')->filter()->values()->all();
        $ketuaPcuId = $item['ketua_pcu_id'] ?? ($pcuIds[0] ?? '');
        $ketuaPcuNama = $item['ketua_pcu_nama'] ?? ($pcuNames[0] ?? '-');
        $permohonanId = $item['permohonan_id'] ?? ($item['id'] ?? '');
        $accordionId = 'penjadwalan-item-' . ($permohonanId ?: ($loop->index + 1));
        $accordionHeadingId = $accordionId . '-heading';
        $accordionCollapseId = $accordionId . '-collapse';
        $provinsi = $item['provinsi'] ?? '-';
        $kota = $item['kota'] ?? '-';
        $alamat = $item['alamat'] ?? '-';
        $createdAtIso = $item['created_at'] ?? '';
        $kategoriLabels = $params->pluck('kategori')
          ->filter()
          ->map(function ($kategori) {
            $text = trim((string) $kategori);
            return match (strtoupper($text)) {
              'LK' => 'Lingkungan Kerja',
              'A' => 'Ambien',
              default => $text,
            };
          })
          ->unique()
          ->values()
          ->all();
        $totalParameterQty = $params->sum(function ($param) {
          return max(1, (int) ($param['qty'] ?? 1));
        });
        $isSentToMa = !empty($item['penjadwalan_sent_at']);
        $returnMessage = trim((string) ($item['return_message'] ?? ''));
        $waitingAccUser = !empty($item['waiting_user_approval']);
        $userApproved = !empty($item['user_approved']);
        $revisedByUser = str_starts_with($returnMessage, 'Dikembalikan ke penjadwalan dari user');
        $stageKey = $userApproved
          ? 'sudah_acc_user'
          : (($waitingAccUser || $revisedByUser) ? 'menunggu_acc_user' : 'butuh_penjadwalan');
      @endphp

      <div
        class="accordion-item border rounded-4 shadow-sm bg-white mb-3 overflow-hidden"
        data-jadwal-card
        data-jadwal-id="{{ $permohonanId ?: ($item['kode'] ?? '') }}"
        data-kode="{{ strtolower($item['kode'] ?? '') }}"
        data-pelanggan="{{ strtolower($item['pelanggan'] ?? '') }}"
        data-pelanggan-label="{{ $item['pelanggan'] ?? '-' }}"
        data-lokasi="{{ $item['lokasi'] ?? '' }}"
        data-status="{{ $item['status_global'] ?? '' }}"
        data-penjadwalan-sent="{{ $item['penjadwalan_sent_at'] ?? '' }}"
        data-provinsi="{{ $provinsi }}"
        data-kota="{{ $kota }}"
        data-alamat="{{ $alamat }}"
        data-tanggal="{{ $item['tanggal'] ?? '' }}"
        data-tanggal-akhir="{{ $tanggalAkhir }}"
        data-catatan="{{ $item['catatan'] ?? '' }}"
        data-pengumuman-jadwal="{{ $item['pengumuman_jadwal'] ?? '' }}"
        data-return-message="{{ $returnMessage }}"
        data-stage="{{ $stageKey }}"
        data-kategori-labels="{{ implode('||', $kategoriLabels) }}"
        data-pcu-names="{{ implode('||', $pcuNames) }}"
        data-pcu-ids="{{ implode('||', $pcuIds) }}"
        data-ketua-pcu-id="{{ $ketuaPcuId }}"
        data-ketua-pcu-nama="{{ $ketuaPcuNama }}"
        data-created-at="{{ $createdAtIso }}"
        data-assign-url="{{ $permohonanId ? route('superadmin.penjadwalan.assign', $permohonanId) : '' }}"
        data-send-user-url="{{ $permohonanId ? route($routePrefix . '.penjadwalan.send-to-user', $permohonanId) : '' }}"
        data-cancel-url="{{ $permohonanId ? route($routePrefix . '.penjadwalan.cancel', $permohonanId) : '' }}"
      >
        <h2 class="accordion-header" id="{{ $accordionHeadingId }}">
          <button
            class="accordion-button collapsed penjadwalan-accordion-button"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#{{ $accordionCollapseId }}"
            aria-expanded="false"
            aria-controls="{{ $accordionCollapseId }}"
          >
            <div class="penjadwalan-accordion-head">
              <div class="penjadwalan-accordion-company">{{ $item['pelanggan'] ?? '-' }}</div>
              <div class="penjadwalan-accordion-schedule">
                <i class="bi bi-calendar3"></i>
                <span data-view-accordion-tanggal>{{ $item['tanggal'] ?: '-' }} s/d {{ $tanggalAkhir ?: '-' }}</span>
              </div>
            </div>
            <span class="penjadwalan-accordion-age">
              <i class="bi bi-clock"></i>
              <span data-relative-time data-created-at="{{ $createdAtIso }}">-</span>
            </span>
          </button>
        </h2>
        <div
          id="{{ $accordionCollapseId }}"
          class="accordion-collapse collapse"
          aria-labelledby="{{ $accordionHeadingId }}"
          data-bs-parent="#penjadwalanAccordion"
        >
          <div class="accordion-body p-3 penjadwalan-accordion-body">
          @if($returnMessage !== '')
            <div class="alert alert-warning border-0 rounded-4 mb-3">
              <div class="fw-semibold mb-1"><i class="bi bi-arrow-counterclockwise me-1"></i>Perlu Revisi Penjadwalan</div>
              <div class="small mb-0">{{ $returnMessage }}</div>
            </div>
          @endif
          <div class="row g-4 align-items-start">
            <div class="col-12 col-xl-8">
              <div class="d-flex flex-column gap-4">
                <div class="penjadwalan-panel penjadwalan-company-panel">
                  <div class="penjadwalan-section-head">
                    <div class="penjadwalan-section-title">
                      <i class="bi bi-grid-1x2"></i>
                      <span>Informasi Perusahaan</span>
                    </div>
                  </div>
                  <div class="penjadwalan-company-body">
                    <div class="penjadwalan-company-icon">
                      <i class="bi bi-building-fill"></i>
                    </div>
                    <div class="penjadwalan-company-content">
                      <div class="penjadwalan-company-label">Nama Perusahaan</div>
                      <h4 class="penjadwalan-company-name mb-0" data-view-pelanggan>{{ $item['pelanggan'] ?? '-' }}</h4>
                      <div class="row g-3 mt-1">
                        <div class="col-12 col-md-7">
                          <div class="penjadwalan-meta-label">Alamat Operasional</div>
                          <div class="penjadwalan-meta-value">
                            <i class="bi bi-geo-alt"></i>
                            <span data-view-alamat>{{ $alamat }}</span>
                          </div>
                        </div>
                      <div class="col-12 col-md-5">
                        <div class="penjadwalan-meta-label">Informasi Jadwal</div>
                        <div class="penjadwalan-meta-stack">
                          <div class="penjadwalan-meta-inline">
                            <i class="bi bi-calendar3"></i>
                            <span data-view-tanggal-waktu>{{ $item['tanggal'] ?: '-' }} s/d {{ $tanggalAkhir ?: '-' }}</span>
                          </div>
                        </div>
                      </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="penjadwalan-panel penjadwalan-parameter-panel">
                  <div class="penjadwalan-section-head">
                    <div class="penjadwalan-section-title">
                      <i class="bi bi-grid-3x3-gap"></i>
                      <span>Daftar Parameter Pengujian</span>
                    </div>
                    <span class="penjadwalan-total-badge">{{ $totalParameterQty }} Parameter Total</span>
                  </div>
                  <div class="table-responsive">
                  <table class="table table-sm align-middle mb-0 penjadwalan-parameter-table">
                    <thead>
                      <tr>
                        <th style="width:180px;">Kategori</th>
                        <th>Parameter</th>
                        <th class="text-center" style="width:90px;">Qty</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                        $groupedParams = $params->groupBy(fn ($item) => (string) ($item['kategori'] ?? '-'));
                      @endphp
                      @forelse($groupedParams as $kategori => $groupItems)
                        @foreach($groupItems as $groupIdx => $param)
                          <tr data-param-kategori="{{ $kategori }}">
                            @if($groupIdx === 0)
                              <td rowspan="{{ $groupItems->count() }}" class="align-top penjadwalan-category-cell">
                                {{ $kategori }}
                              </td>
                              @endif
                              <td class="penjadwalan-parameter-name">{{ $param['nama'] ?? '-' }}</td>
                              <td class="text-center penjadwalan-qty-cell">{{ $param['qty'] ?? 1 }}</td>
                            </tr>
                        @endforeach
                      @empty
                        <tr>
                          <td colspan="3" class="text-muted text-center small py-4">Belum ada parameter.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                  </div>
                  <div class="penjadwalan-parameter-foot">
                    Menampilkan seluruh parameter yang telah dipilih untuk penjadwalan saat ini.
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-xl-4">
              <div class="penjadwalan-panel penjadwalan-verification-panel h-100">
                <div class="penjadwalan-section-head">
                  <div class="penjadwalan-section-title">
                    <i class="bi bi-shield-check"></i>
                    <span>Informasi Pelaksana</span>
                  </div>
                </div>
                <div class="penjadwalan-info-group">
                  <div class="penjadwalan-field-label">Petugas PCU</div>
                  <div class="penjadwalan-info-box">
                    <ul class="list-unstyled mb-0 penjadwalan-pcu-list" data-view-pcu>
                      @forelse($pcu as $row)
                        <li>{{ $row['name'] ?? '-' }}</li>
                      @empty
                        <li class="is-empty">Belum ada petugas PCU.</li>
                      @endforelse
                    </ul>
                  </div>
                </div>
                <div class="penjadwalan-info-group">
                  <div class="penjadwalan-field-label">Ketua PCU</div>
                  <div class="penjadwalan-info-box is-single" data-view-ketua-pcu>{{ $ketuaPcuNama ?: '-' }}</div>
                </div>
                <div class="penjadwalan-info-group">
                  <div class="penjadwalan-field-label">Catatan untuk Petugas</div>
                  <div class="penjadwalan-note-box" data-view-catatan>{{ $item['catatan'] ?? '-' }}</div>
                </div>
                @if($waitingAccUser || $userApproved || $revisedByUser)
                  <div class="alert {{ $userApproved ? 'alert-success' : 'alert-info' }} border-0 rounded-4 mt-3 mb-3">
                    <div class="fw-semibold mb-1">
                      {{ $userApproved ? 'Sudah ACC User' : ($revisedByUser ? 'Perlu Revisi dari User' : 'Menunggu Konfirmasi Pelanggan') }}
                    </div>
                    <div class="small mb-0">
                      {{ $userApproved ? 'User sudah menyetujui jadwal. Klik Teruskan ke MA untuk melanjutkan ke Approval MA.' : ($revisedByUser ? 'User meminta perubahan jadwal. Silakan edit jadwal lalu kirim ulang ke user.' : 'Jadwal sudah dikirim ke user dan sedang menunggu persetujuan.') }}
                    </div>
                  </div>
                @endif
                @if($canEdit)
                  <div class="penjadwalan-action-stack">
                    @if($isSentToMa)
                      <button type="button" class="btn penjadwalan-primary-btn w-100" disabled>Sudah Dikirim ke Approval MA</button>
                    @elseif($userApproved)
                      <form
                        method="POST"
                        action="{{ $permohonanId ? route($routePrefix . '.penjadwalan.send-to-spt', $permohonanId) : '#' }}"
                        data-confirm-send-to-admin
                        data-workflow-submit-form
                      >
                        @csrf
                        <button
                          type="submit"
                          class="btn penjadwalan-primary-btn w-100"
                          data-loading-text="Meneruskan ke Approval MA..."
                        >
                          Teruskan ke MA
                        </button>
                      </form>
                    @elseif($waitingAccUser)
                      <button type="button" class="btn penjadwalan-primary-btn w-100" disabled>Menunggu Konfirmasi Pelanggan</button>
                    @else
                      <form
                        method="POST"
                        action="{{ $permohonanId ? route($routePrefix . '.penjadwalan.send-to-user', $permohonanId) : '#' }}"
                        data-confirm-send-to-user
                        data-workflow-submit-form
                      >
                        @csrf
                        <button
                          type="submit"
                          class="btn penjadwalan-primary-btn w-100"
                          data-loading-text="Mengirim ke User..."
                        >
                          Kirim ke User
                        </button>
                      </form>
                    @endif
                    @if(!$isSentToMa && !$waitingAccUser && !$userApproved)
                      <div class="d-flex gap-2">
                        <button
                          type="button"
                          class="btn penjadwalan-secondary-btn flex-fill"
                          data-edit-jadwal="{{ $permohonanId ?: ($item['kode'] ?? '') }}"
                        >
                          Edit Jadwal
                        </button>
                      </div>
                    @endif
                  </div>
                @endif
                <div class="penjadwalan-help-box">
                  <div class="penjadwalan-help-title">
                    <i class="bi bi-info-circle"></i>
                    <span>Butuh bantuan?</span>
                  </div>
                  <p class="mb-0">Pastikan data jadwal, petugas, dan catatan sudah sesuai sebelum mengirim jadwal ke user. Setelah user ACC, petugas menekan Teruskan ke MA untuk melanjutkan ke Approval MA, lalu data masuk ke Dokumen SPT admin.</p>
                </div>
              </div>
            </div>
          </div>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center text-muted py-3">Belum ada jadwal pengujian.</div>
    @endforelse
  </div>


  <!-- Modal Edit Jadwal -->
  <div class="modal fade modal-custom" id="jadwalEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered edit-jadwal-modal-dialog">
      <div class="modal-content edit-jadwal-modal-content">
        <div class="modal-header edit-jadwal-modal-header">
          <h5 class="modal-title edit-jadwal-modal-title">Edit Jadwal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body edit-jadwal-modal-body">
          <input type="hidden" id="editId">
          <div class="edit-jadwal-layout">
            <div class="edit-jadwal-field edit-jadwal-span-2">
              <label class="form-label edit-jadwal-label mb-2">Lokasi</label>
              <input type="text" class="form-control edit-jadwal-input" id="editLokasi" placeholder="Lokasi">
            </div>

            <div class="edit-jadwal-field">
              <label class="form-label edit-jadwal-label mb-2">Tanggal Mulai</label>
              <input type="date" class="form-control edit-jadwal-input" id="editTanggalMulai">
            </div>

            <div class="edit-jadwal-field">
              <label class="form-label edit-jadwal-label mb-2">Tanggal Selesai</label>
              <input type="date" class="form-control edit-jadwal-input" id="editTanggalSelesai">
            </div>

            <div class="edit-jadwal-field edit-jadwal-span-2">
              <div class="d-inline-flex align-items-center gap-2 mb-2">
                <label class="form-label edit-jadwal-label mb-0">Petugas PCU</label>
                <button type="button" class="btn btn-sm edit-jadwal-add-btn" id="addPcu" aria-label="Tambah PCU" title="Tambah PCU">
                  <i class="bi bi-plus-lg"></i>
                </button>
              </div>
              <div class="d-flex flex-column gap-2" id="editPcuList"></div>
            </div>

            <div class="edit-jadwal-field">
              <label class="form-label edit-jadwal-label mb-2">Ketua Tim</label>
              <select class="form-select edit-jadwal-input" id="editKetuaPcu">
                <option value="">Pilih ketua tim...</option>
              </select>
              <div class="form-text edit-jadwal-help">Nama ketua tim akan diprioritaskan muncul pertama pada kolom pelaksana di form pengumuman.</div>
            </div>

            <div class="edit-jadwal-field">
              <label class="form-label edit-jadwal-label mb-2">Catatan untuk Petugas</label>
              <textarea class="form-control edit-jadwal-input edit-jadwal-textarea" rows="3" id="editCatatan" placeholder="Catatan internal untuk petugas PCU, tidak dikirim ke user"></textarea>
              <div class="form-text edit-jadwal-help">Catatan ini dipakai untuk kebutuhan internal petugas/PCU, bukan pesan yang ditampilkan ke user.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer edit-jadwal-modal-footer">
          <button type="button" class="btn btn-light border edit-jadwal-cancel-btn" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary btn-deep-blue edit-jadwal-save-btn" id="saveJadwal">Simpan</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade modal-custom" id="pengumumanFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content pengumuman-modal-content">
        <div class="modal-header pengumuman-modal-header">
          <div>
            <h5 class="modal-title pengumuman-modal-title">Form Pengumuman Jadwal Sampling</h5>
            <div class="pengumuman-modal-subtitle">Kelola dan publikasikan jadwal sampling mingguan untuk operasional perusahaan.</div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pengumuman-modal-body">
          <div class="pengumuman-panel pengumuman-panel-primary mb-4">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-lg-6">
                <label class="form-label pengumuman-label mb-2">Judul Pengumuman</label>
                <div class="pengumuman-input-wrap">
                  <i class="bi bi-card-text pengumuman-input-icon"></i>
                  <input type="text" class="form-control form-control-sm pengumuman-input" id="annJudul" placeholder="Contoh: Jadwal Sampling Udara Ambien - Minggu ke-3 Januari">
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <label class="form-label pengumuman-label mb-2">Rentang Tanggal Mingguan</label>
                <div class="pengumuman-date-row">
                  <div class="pengumuman-input-wrap">
                    <i class="bi bi-calendar4-week pengumuman-input-icon"></i>
                    <input type="date" class="form-control form-control-sm pengumuman-input" id="annRentangMulai">
                  </div>
                  <span class="pengumuman-date-separator">s/d</span>
                  <div class="pengumuman-input-wrap">
                    <i class="bi bi-calendar4-week pengumuman-input-icon"></i>
                    <input type="date" class="form-control form-control-sm pengumuman-input" id="annRentangSelesai">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="pengumuman-panel mb-4 d-none" id="annDetailPanel">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
              <div class="pengumuman-section-title">
                <i class="bi bi-table"></i>
                <span>Detail Jadwal Mingguan</span>
              </div>
              <button type="button" class="btn btn-sm pengumuman-add-row-btn" id="annAddRow">
                <i class="bi bi-plus-lg me-1"></i>Tambah Baris
              </button>
            </div>
            <div class="table-responsive pengumuman-table-wrap">
              <table class="table table-sm align-middle mb-0 pengumuman-table">
                <thead>
                  <tr>
                    <th style="width:24%;">Perusahaan</th>
                    <th style="width:22%;">Hari/Tgl Pelaksanaan</th>
                    <th style="width:20%;">Jenis Pengukuran</th>
                    <th style="width:18%;">Pelaksana</th>
                    <th style="width:12%;">Keterangan/Kendaraan</th>
                    <th style="width:4%;" class="text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody id="annRowsBody"></tbody>
              </table>
            </div>
          </div>
          <div class="pengumuman-panel mb-4" id="annRangeHint">
            <div class="small text-muted mb-0">Pilih terlebih dahulu rentang tanggal mingguan untuk menampilkan detail jadwal secara otomatis.</div>
          </div>

          <div class="row g-4 align-items-stretch">
            <div class="col-12 col-lg-8">
              <div class="pengumuman-panel h-100">
                <label class="form-label pengumuman-label mb-2">Catatan Tambahan</label>
                <textarea class="form-control form-control-sm pengumuman-textarea" rows="5" id="annCatatan" placeholder="Masukkan instruksi khusus atau catatan tambahan untuk tim di lapangan..."></textarea>
              </div>
            </div>
            <div class="col-12 col-lg-4">
              <div class="pengumuman-side-panel h-100">
                <div class="pengumuman-guide-title">
                  <i class="bi bi-info-circle-fill"></i>
                  <span>Panduan</span>
                </div>
                <p class="pengumuman-guide-text">Pastikan semua data perusahaan dan pelaksana sudah benar sebelum mengunduh form. Form yang diunduh akan berformat PDF siap cetak.</p>
                <button type="button" class="btn pengumuman-download-btn" id="downloadPengumumanForm">
                  <i class="bi bi-download me-1"></i>Unduh Form Pengumuman
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer pengumuman-modal-footer">
          <button type="button" class="btn btn-light border pengumuman-close-btn" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
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

      const tanggalInput = document.querySelector('[data-search-tanggal]');
      const pelangganInput = document.querySelector('[data-search-pelanggan]');
      const resetBtn = document.querySelector('[data-search-reset]');
      const cards = document.querySelectorAll('[data-jadwal-card]');
      const stageButtons = document.querySelectorAll('[data-stage-filter]');
      const relativeTimeNodes = document.querySelectorAll('[data-relative-time]');
      let activeStage = document.querySelector('[data-stage-filter].active')?.getAttribute('data-stage-filter') || 'butuh_penjadwalan';

      const formatRelativeTime = (isoString) => {
        if (!isoString) return '-';
        const date = new Date(isoString);
        if (Number.isNaN(date.getTime())) return '-';

        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        if (diffMs < 0) return 'baru saja';
        const diffMinutes = Math.floor(diffMs / 60000);

        if (diffMinutes < 1) return 'baru saja';
        if (diffMinutes < 60) return `${diffMinutes} menit yang lalu`;

        const diffHours = Math.floor(diffMinutes / 60);
        if (diffHours < 24) return `${diffHours} jam yang lalu`;

        const diffDays = Math.floor(diffHours / 24);
        if (diffDays < 30) return `${diffDays} hari yang lalu`;

        const diffMonths = Math.floor(diffDays / 30);
        if (diffMonths < 12) return `${diffMonths} bulan yang lalu`;

        const diffYears = Math.floor(diffMonths / 12);
        return `${diffYears} tahun yang lalu`;
      };

      const refreshRelativeTimes = () => {
        relativeTimeNodes.forEach((node) => {
          const isoString = node.getAttribute('data-created-at') || '';
          node.textContent = formatRelativeTime(isoString);
        });
      };

      const filterCards = () => {
        const tanggalVal = (tanggalInput?.value || '').toLowerCase().trim();
        const pelangganVal = (pelangganInput?.value || '').toLowerCase().trim();
        let visible = 0;

        cards.forEach((card) => {
          const tanggalMulai = (card.getAttribute('data-tanggal') || '').toLowerCase();
          const tanggalSelesai = (card.getAttribute('data-tanggal-akhir') || '').toLowerCase();
          const tanggalGabung = `${tanggalMulai} ${tanggalSelesai}`.trim();
          const pelanggan = card.getAttribute('data-pelanggan') || '';
          const stage = card.getAttribute('data-stage') || 'butuh_penjadwalan';
          const matchTanggal = !tanggalVal || tanggalGabung.includes(tanggalVal);
          const matchPelanggan = !pelangganVal || pelanggan.includes(pelangganVal);
          const matchStage = !activeStage || stage === activeStage;
          const show = matchTanggal && matchPelanggan && matchStage;

          const accordionItem = card.closest('.accordion-item');
          if (accordionItem) {
            accordionItem.classList.toggle('d-none', !show);
          } else {
            card.classList.toggle('d-none', !show);
          }
          if (show) visible += 1;
        });

      };

      tanggalInput?.addEventListener('input', filterCards);
      pelangganInput?.addEventListener('input', filterCards);
      stageButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          stageButtons.forEach((node) => {
            node.classList.remove('active');
            node.classList.remove('btn-deep-blue');
            node.classList.add('btn-outline-deep-blue');
          });
          btn.classList.add('active');
          btn.classList.remove('btn-outline-deep-blue');
          btn.classList.add('btn-deep-blue');
          activeStage = btn.getAttribute('data-stage-filter') || 'butuh_penjadwalan';
          filterCards();
        });
      });
      resetBtn?.addEventListener('click', () => {
        if (tanggalInput) tanggalInput.value = '';
        if (pelangganInput) pelangganInput.value = '';
        filterCards();
      });

      filterCards();
      refreshRelativeTimes();

      // Modal edit
      const modalEl = document.getElementById('jadwalEditModal');
      const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
      const formEls = {
        id: document.getElementById('editId'),
        lokasi: document.getElementById('editLokasi'),
        tglMulai: document.getElementById('editTanggalMulai'),
        tglSelesai: document.getElementById('editTanggalSelesai'),
        catatan: document.getElementById('editCatatan'),
        ketuaPcu: document.getElementById('editKetuaPcu'),
        pcuList: document.getElementById('editPcuList'),
      };
      const addPcuBtn = document.getElementById('addPcu');
      const annEls = {
        judul: document.getElementById('annJudul'),
        rentangMulai: document.getElementById('annRentangMulai'),
        rentangSelesai: document.getElementById('annRentangSelesai'),
        catatan: document.getElementById('annCatatan'),
        rowsBody: document.getElementById('annRowsBody'),
        addRowBtn: document.getElementById('annAddRow'),
        detailPanel: document.getElementById('annDetailPanel'),
        rangeHint: document.getElementById('annRangeHint'),
      };
      const downloadPengumumanFormBtn = document.getElementById('downloadPengumumanForm');
      const pcuOptions = @json($pcuOptions);
      const canEdit = @json($canEdit);
      const pembuatFormName = @json(optional(auth()->user())->name ?? '-');
      const pembuatFormSignature = @json(optional(auth()->user())->signature_path ? route('petugas.signature.me') : '');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

      const pengumumanStorageKey = 'penjadwalan:pengumuman:weekly';
      const readPengumumanDraft = () => {
        try {
          return JSON.parse(localStorage.getItem(pengumumanStorageKey) || '{}');
        } catch (err) {
          return {};
        }
      };
      const writePengumumanDraft = (payload) => {
        try {
          localStorage.setItem(pengumumanStorageKey, JSON.stringify(payload || {}));
        } catch (err) {
          // ignore
        }
      };
      const expandKategori = (raw) => {
        const text = String(raw || '').trim();
        const upper = text.toUpperCase();
        if (upper === 'LK') return 'Lingkungan Kerja';
        if (upper === 'A') return 'Ambien';
        return text;
      };
      const resolveKategoriText = (card) => {
        if (!card) return '-';
        const fromData = (card.getAttribute('data-kategori-labels') || '')
          .split('||')
          .map((item) => expandKategori(item))
          .filter(Boolean);
        if (fromData.length) {
          return [...new Set(fromData)].join(', ');
        }

        const fromTable = Array.from(card.querySelectorAll('.penjadwalan-category-cell') || [])
          .map((el) => expandKategori(el.textContent))
          .filter(Boolean);
        const unique = [...new Set(fromTable)];
        return unique.length ? unique.join(', ') : '-';
      };
      const getOrderedPcuNames = (card) => {
        if (!card) return [];
        const rawIds = (card.getAttribute('data-pcu-ids') || '')
          .split('||')
          .map((val) => String(val || '').trim());
        const rawNames = (card.getAttribute('data-pcu-names') || '')
          .split('||')
          .map((val) => String(val || '').trim());
        const ketuaId = String(card.getAttribute('data-ketua-pcu-id') || '').trim();
        const pairs = rawNames.map((name, idx) => ({
          id: rawIds[idx] || '',
          name,
        })).filter((item) => item.name);

        if (!pairs.length) return [];
        if (!ketuaId) return pairs.map((item) => item.name);

        const leader = pairs.find((item) => item.id === ketuaId);
        const members = pairs.filter((item) => item.id !== ketuaId);
        return leader
          ? [leader.name, ...members.map((item) => item.name)]
          : pairs.map((item) => item.name);
      };

      const toIndoDate = (dateValue) => {
        if (!dateValue) return '-';
        const d = new Date(`${dateValue}T00:00:00`);
        if (Number.isNaN(d.getTime())) return dateValue;
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
      };
      const toIndoDayDate = (dateValue) => {
        if (!dateValue) return '-';
        const d = new Date(`${dateValue}T00:00:00`);
        if (Number.isNaN(d.getTime())) return dateValue;
        return new Intl.DateTimeFormat('id-ID', {
          weekday: 'long',
          day: 'numeric',
          month: 'long',
          year: 'numeric',
        }).format(d);
      };
      const toIndoDateShort = (dateValue) => {
        if (!dateValue) return '-';
        const d = new Date(`${dateValue}T00:00:00`);
        if (Number.isNaN(d.getTime())) return dateValue;
        return new Intl.DateTimeFormat('id-ID', {
          day: 'numeric',
          month: 'long',
          year: 'numeric',
        }).format(d);
      };
      const toIndoRangeCompact = (startValue, endValue) => {
        if (!startValue) return '-';
        if (!endValue || startValue === endValue) return toIndoDateShort(startValue);
        const s = new Date(`${startValue}T00:00:00`);
        const e = new Date(`${endValue}T00:00:00`);
        if (Number.isNaN(s.getTime()) || Number.isNaN(e.getTime())) return `${startValue}-${endValue}`;
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        if (s.getFullYear() === e.getFullYear() && s.getMonth() === e.getMonth()) {
          return `${s.getDate()}-${e.getDate()} ${months[s.getMonth()]} ${s.getFullYear()}`;
        }
        if (s.getFullYear() === e.getFullYear()) {
          return `${s.getDate()} ${months[s.getMonth()]}-${e.getDate()} ${months[e.getMonth()]} ${s.getFullYear()}`;
        }
        return `${s.getDate()} ${months[s.getMonth()]} ${s.getFullYear()}-${e.getDate()} ${months[e.getMonth()]} ${e.getFullYear()}`;
      };

      const getCardById = (id) => document.querySelector(`[data-jadwal-card][data-jadwal-id="${id}"]`);
      const pengumumanSourceRaw = (() => {
        const node = document.getElementById('pengumumanSourceData');
        if (!node) return [];
        try {
          const parsed = JSON.parse(node.textContent || '[]');
          return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
          return [];
        }
      })();
      const pengumumanSourceMap = new Map(
        pengumumanSourceRaw
          .map((item) => [String(item?.id || '').trim(), item])
          .filter(([id]) => id !== '')
      );
      const getPengumumanSourceItem = (id) => pengumumanSourceMap.get(String(id || '').trim()) || null;
      const getPengumumanSourceItems = () => Array.from(pengumumanSourceMap.values());
      const getPengumumanKategoriText = (item) => {
        if (!item) return '-';
        const labels = Array.isArray(item.kategori_labels) ? item.kategori_labels : [];
        if (!labels.length) return '-';
        return labels.join(' / ');
      };
      const getPengumumanPelaksanaText = (item) => {
        if (!item) return '';
        const names = Array.isArray(item.pelaksana) ? item.pelaksana.filter(Boolean) : [];
        return names.join(', ');
      };
      const buildScheduleOptions = (selectedId = '') => {
        const options = ['<option value="">Pilih perusahaan...</option>'];
        getPengumumanSourceItems().forEach((item) => {
          const id = String(item?.id || '');
          const pelanggan = item?.pelanggan || '-';
          const selected = String(id) === String(selectedId) ? ' selected' : '';
          options.push(`<option value="${id}"${selected}>${pelanggan}</option>`);
        });
        return options.join('');
      };

      const createAnnRow = (initial = {}) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><select class="form-select form-select-sm" data-ann="jadwal">${buildScheduleOptions(initial.id || '')}</select></td>
          <td>
            <div class="d-flex align-items-center gap-1">
              <input type="date" class="form-control form-control-sm" data-ann="tanggal-mulai" value="${initial.tanggal_mulai || initial.tanggal || ''}" readonly title="Ubah tanggal melalui Edit Jadwal">
              <span class="small text-muted">s/d</span>
              <input type="date" class="form-control form-control-sm" data-ann="tanggal-selesai" value="${initial.tanggal_selesai || ''}" readonly title="Ubah tanggal melalui Edit Jadwal">
            </div>
          </td>
          <td><input type="text" class="form-control form-control-sm" data-ann="jenis" placeholder="Isi kegiatan..." value="${initial.jenis || ''}"></td>
          <td><input type="text" class="form-control form-control-sm" data-ann="pelaksana" placeholder="Daftar nama..." value="${initial.pelaksana || ''}"></td>
          <td><input type="text" class="form-control form-control-sm" data-ann="keterangan" placeholder="Ket/Kendaraan..." value="${initial.keterangan || ''}"></td>
          <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm" data-ann="hapus"><i class="bi bi-trash"></i></button></td>
        `;
        return tr;
      };

      const autoFillAnnRow = (tr) => {
        const select = tr.querySelector('[data-ann="jadwal"]');
        const sourceItem = getPengumumanSourceItem(select?.value || '');
        const tglMulaiInput = tr.querySelector('[data-ann="tanggal-mulai"]');
        const tglSelesaiInput = tr.querySelector('[data-ann="tanggal-selesai"]');
        const jenisInput = tr.querySelector('[data-ann="jenis"]');
        const pelaksanaInput = tr.querySelector('[data-ann="pelaksana"]');
        if (!sourceItem) {
          if (tglMulaiInput) tglMulaiInput.value = '';
          if (tglSelesaiInput) tglSelesaiInput.value = '';
          if (jenisInput) jenisInput.value = '';
          if (pelaksanaInput) pelaksanaInput.value = '';
          return;
        }
        if (tglMulaiInput && !tglMulaiInput.value) {
          tglMulaiInput.value = sourceItem.tanggal || '';
        }
        if (tglSelesaiInput && !tglSelesaiInput.value) {
          tglSelesaiInput.value = sourceItem.tanggal_selesai || '';
        }
        if (jenisInput && !jenisInput.value.trim()) {
          jenisInput.value = getPengumumanKategoriText(sourceItem);
        }
        if (pelaksanaInput && !pelaksanaInput.value.trim()) {
          pelaksanaInput.value = getPengumumanPelaksanaText(sourceItem);
        }
      };
      const buildInitialPengumumanRows = () => {
        return getPengumumanSourceItems().map((item) => ({
          id: String(item?.id || ''),
          tanggal_mulai: item?.tanggal || '',
          tanggal_selesai: item?.tanggal_selesai || '',
          jenis: getPengumumanKategoriText(item),
          pelaksana: getPengumumanPelaksanaText(item),
          keterangan: '',
        })).filter((row) => row.id);
      };
      const parseIsoDate = (value) => {
        const raw = String(value || '').trim();
        if (!raw || raw === '-') return null;

        const isoMatch = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (isoMatch) {
          const year = Number(isoMatch[1]);
          const month = Number(isoMatch[2]);
          const day = Number(isoMatch[3]);
          const d = new Date(year, month - 1, day);
          return Number.isNaN(d.getTime()) ? null : d;
        }

        const slashMatch = raw.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
        if (slashMatch) {
          const first = Number(slashMatch[1]);
          const second = Number(slashMatch[2]);
          const year = Number(slashMatch[3]);
          let month = first;
          let day = second;
          if (first > 12 && second <= 12) {
            month = second;
            day = first;
          }
          const d = new Date(year, month - 1, day);
          return Number.isNaN(d.getTime()) ? null : d;
        }

        const fallback = new Date(raw);
        return Number.isNaN(fallback.getTime()) ? null : fallback;
      };
      const isScheduleInRange = (item, rangeStart, rangeEnd) => {
        if (!item || !rangeStart || !rangeEnd) return false;
        const cardStart = parseIsoDate(item.tanggal || '');
        const cardEnd = parseIsoDate(item.tanggal_selesai || item.tanggal || '');
        if (!cardStart && !cardEnd) return false;
        const start = cardStart || cardEnd;
        const end = cardEnd || cardStart;
        if (!start || !end) return false;
        return start <= rangeEnd && end >= rangeStart;
      };
      const buildRowsBySelectedRange = () => {
        const rangeStart = parseIsoDate(annEls.rentangMulai?.value || '');
        const rangeEnd = parseIsoDate(annEls.rentangSelesai?.value || '');
        if (!rangeStart || !rangeEnd || rangeStart > rangeEnd) return [];

        return getPengumumanSourceItems().filter((item) => isScheduleInRange(item, rangeStart, rangeEnd)).map((item) => ({
          id: String(item?.id || ''),
          tanggal_mulai: item?.tanggal || '',
          tanggal_selesai: item?.tanggal_selesai || '',
          jenis: getPengumumanKategoriText(item),
          pelaksana: getPengumumanPelaksanaText(item),
          keterangan: '',
        })).filter((row) => row.id);
      };
      const refreshPengumumanRowsByRange = () => {
        const rangeStart = parseIsoDate(annEls.rentangMulai?.value || '');
        const rangeEnd = parseIsoDate(annEls.rentangSelesai?.value || '');
        const hasRange = Boolean(rangeStart && rangeEnd && rangeStart <= rangeEnd);
        const rows = hasRange ? buildRowsBySelectedRange() : [];
        const currentRows = collectAnnRows();
        const currentById = new Map(currentRows.map((row) => [String(row.id || ''), row]));

        if (annEls.detailPanel) annEls.detailPanel.classList.toggle('d-none', !hasRange);
        if (annEls.rangeHint) annEls.rangeHint.classList.toggle('d-none', hasRange);

        annEls.rowsBody.innerHTML = '';
        if (hasRange && rows.length === 0) {
          annEls.rowsBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted small py-3">Tidak ada jadwal pada rentang tanggal ini.</td></tr>';
          return;
        }
        rows.forEach((row) => {
          const currentRow = currentById.get(String(row.id || ''));
          const merged = {
            ...row,
            keterangan: currentRow?.keterangan || '',
          };
          const tr = createAnnRow(merged);
          annEls.rowsBody.appendChild(tr);
          autoFillAnnRow(tr);
        });
      };
      const syncPengumumanDraftWithCards = () => {
        const draft = readPengumumanDraft();
        const baseRows = buildInitialPengumumanRows();
        const draftRows = Array.isArray(draft.rows) ? draft.rows : [];
        const mergedRows = baseRows.map((baseRow) => {
          const draftRow = draftRows.find((row) => String(row.id || '') === String(baseRow.id || ''));
          if (!draftRow) return baseRow;
          return {
            ...draftRow,
            id: baseRow.id,
            tanggal_mulai: baseRow.tanggal_mulai,
            tanggal_selesai: baseRow.tanggal_selesai,
            jenis: baseRow.jenis || draftRow.jenis || '',
            pelaksana: baseRow.pelaksana || draftRow.pelaksana || '',
          };
        });

        const extraRows = draftRows.filter((draftRow) => {
          return !baseRows.some((baseRow) => String(baseRow.id || '') === String(draftRow.id || ''));
        });

        writePengumumanDraft({
          ...draft,
          rows: [...mergedRows, ...extraRows],
        });
      };

      const buildOptionsHtml = (options, selectedValue) => {
        const items = ['<option value="">Pilih nama...</option>'];
        options.forEach((opt) => {
          const selected = String(opt.id) === String(selectedValue) ? ' selected' : '';
          items.push(`<option value="${opt.id}"${selected}>${opt.name}</option>`);
        });
        return items.join('');
      };

      const refreshKetuaPcuOptions = (selectedId = null) => {
        if (!formEls.ketuaPcu) return;
        const selectedPcuIds = Array.from(formEls.pcuList?.querySelectorAll('select') || [])
          .map((select) => String(select.value || '').trim())
          .filter(Boolean);

        const allowedOptions = pcuOptions.filter((opt) => selectedPcuIds.includes(String(opt.id)));
        const fallbackId = selectedPcuIds[0] || '';
        const finalSelected = selectedId && selectedPcuIds.includes(String(selectedId))
          ? String(selectedId)
          : fallbackId;

        const items = ['<option value="">Pilih ketua PCU...</option>'];
        allowedOptions.forEach((opt) => {
          const isSelected = String(opt.id) === finalSelected ? ' selected' : '';
          items.push(`<option value="${opt.id}"${isSelected}>${opt.name}</option>`);
        });
        formEls.ketuaPcu.innerHTML = items.join('');
      };

      const hasPcuAssigned = (card) => {
        if (!card) return false;
        const rawIds = card.getAttribute('data-pcu-ids') || '';
        const rawNames = card.getAttribute('data-pcu-names') || '';
        const list = (rawIds || rawNames).split('||').map((val) => val.trim()).filter(Boolean);
        return list.length > 0;
      };
      const isSentToMa = (card) => {
        if (!card) return false;
        return String(card.getAttribute('data-penjadwalan-sent') || '').trim() !== '';
      };

      const syncSendButtonState = (card) => {
        if (!card) return;
        const form = card.querySelector('[data-confirm-send-to-admin]');
        const btn = form?.querySelector('button[type="submit"]');
        if (!btn) return;
        const allowed = hasPcuAssigned(card) && !isSentToMa(card);
        btn.disabled = !allowed;
        btn.setAttribute('aria-disabled', allowed ? 'false' : 'true');
        if (allowed) {
          btn.removeAttribute('title');
        } else {
          btn.setAttribute('title', isSentToMa(card) ? 'Data sudah dikirim ke Approval MA.' : 'Pilih petugas PCU terlebih dahulu.');
        }
      };
      const renderPcuInfo = (card) => {
        if (!card) return;
        const pcuContainer = card.querySelector('[data-view-pcu]');
        if (!pcuContainer) return;

        const pcuNames = getOrderedPcuNames(card);
        pcuContainer.innerHTML = '';

        if (!pcuNames.length) {
          const li = document.createElement('li');
          li.className = 'is-empty';
          li.textContent = 'Belum ada petugas PCU.';
          pcuContainer.appendChild(li);
          return;
        }

        pcuNames.forEach((name) => {
          const li = document.createElement('li');
          li.textContent = name;
          pcuContainer.appendChild(li);
        });
      };

      const createNameRow = (value, role) => {
        const row = document.createElement('div');
        row.className = 'd-flex gap-2 align-items-center';
        row.setAttribute('data-name-row', role);

        const select = document.createElement('select');
        select.className = 'form-select';
        select.innerHTML = buildOptionsHtml(pcuOptions, value || '');

        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger btn-sm';
        removeBtn.setAttribute('aria-label', 'Hapus PCU');
        removeBtn.setAttribute('title', 'Hapus PCU');
        removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
        removeBtn.setAttribute('data-remove-row', role);

        row.appendChild(select);
        row.appendChild(removeBtn);
        return row;
      };
      const refreshPcuSelectOptions = () => {
        const selects = Array.from(formEls.pcuList?.querySelectorAll('select') || []);
        const selectedValues = selects
          .map((select) => String(select.value || '').trim())
          .filter(Boolean);

        selects.forEach((select) => {
          const currentValue = String(select.value || '').trim();
          Array.from(select.options).forEach((option) => {
            const optionValue = String(option.value || '').trim();
            if (!optionValue) {
              option.hidden = false;
              option.disabled = false;
              return;
            }

            const usedByAnother = optionValue !== currentValue && selectedValues.includes(optionValue);
            option.hidden = usedByAnother;
            option.disabled = usedByAnother;
          });
        });
      };

      const renderNameList = (container, names, role) => {
        if (!container) return;
        container.innerHTML = '';

        if (!names || names.length === 0) {
          container.appendChild(createNameRow('', role));
          return;
        }

        names.forEach((name) => container.appendChild(createNameRow(name, role)));
      };

      const openModal = (card) => {
        if (!modal || !card) return;

        const id = card.getAttribute('data-jadwal-id') || '';
        const lokasi = card.getAttribute('data-lokasi') || '';
        const tgl = card.getAttribute('data-tanggal') || '';
        const tglAkhir = card.getAttribute('data-tanggal-akhir') || '';
        const catatan = card.getAttribute('data-catatan') || '';
        const pcuIds = card.getAttribute('data-pcu-ids') || '';
        const ketuaPcuId = card.getAttribute('data-ketua-pcu-id') || '';

        formEls.id.value = id;
        formEls.lokasi.value = lokasi;
        formEls.tglMulai.value = tgl;
        formEls.tglSelesai.value = tglAkhir;
        formEls.catatan.value = catatan;

        renderNameList(
          formEls.pcuList,
          pcuIds ? pcuIds.split('||').filter(Boolean) : [],
          'pcu'
        );
        refreshPcuSelectOptions();
        refreshKetuaPcuOptions(ketuaPcuId);
        modal.show();
      };

      if (canEdit) {
        document.querySelectorAll('[data-edit-jadwal]').forEach((btn) => {
          btn.addEventListener('click', () => {
            const card = btn.closest('[data-jadwal-card]');
            openModal(card);
          });
        });
      }

      const saveBtn = document.getElementById('saveJadwal');
      saveBtn?.addEventListener('click', async () => {
        if (!canEdit) return;

        const id = formEls.id.value;
        const card = document.querySelector(`[data-jadwal-card][data-jadwal-id="${id}"]`);
        if (!card) return;

        const lokasi = formEls.lokasi.value || '-';
        const tglMulai = formEls.tglMulai.value || '';
        const tglSelesai = formEls.tglSelesai.value || '';
        const catatan = formEls.catatan.value || '';
        const pengumumanJadwal = card.getAttribute('data-pengumuman-jadwal') || '';
        const ketuaPcu = formEls.ketuaPcu?.value?.trim() || '';
        const pcuLines = Array.from(formEls.pcuList?.querySelectorAll('select') || [])
          .map((select) => select.value.trim())
          .filter(Boolean);
        const uniquePcuLines = [...new Set(pcuLines)];
        const assignUrl = card.getAttribute('data-assign-url') || '';

        if (uniquePcuLines.length !== pcuLines.length) {
          Swal.fire({
            icon: 'warning',
            title: 'Petugas PCU ganda',
            text: 'Pilih petugas PCU yang berbeda untuk setiap baris.',
          });
          return;
        }

        try {
          if (assignUrl) {
            const response = await fetch(assignUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
              },
              body: JSON.stringify({
                pcu: uniquePcuLines,
                ketua_pcu: ketuaPcu || null,
                lokasi,
                tanggal_mulai: tglMulai || null,
                tanggal_selesai: tglSelesai || null,
                catatan,
                pengumuman_jadwal: pengumumanJadwal,
              }),
            });

            if (!response.ok) {
              const result = await response.json().catch(() => ({}));
              throw new Error(result.message || 'Gagal menyimpan penugasan.');
            }

            const result = await response.json().catch(() => ({}));
            const pcuNames = (result.pcu || []).map((row) => row.name).filter(Boolean);
            const ketuaPcuIdSaved = result.ketua_pcu_id || '';
            const ketuaPcuNamaSaved = result.ketua_pcu_nama || '-';
            const lokasiSaved = result.lokasi || lokasi;
            const tglMulaiSaved = result.tanggal_mulai || tglMulai;
            const tglSelesaiSaved = result.tanggal_selesai || tglSelesai;
            const catatanSaved = result.catatan ?? catatan;
            const pengumumanSaved = result.pengumuman_jadwal ?? pengumumanJadwal;

            card.setAttribute('data-pcu-ids', uniquePcuLines.join('||'));
            card.setAttribute('data-pcu-names', pcuNames.join('||'));
            card.setAttribute('data-ketua-pcu-id', ketuaPcuIdSaved);
            card.setAttribute('data-ketua-pcu-nama', ketuaPcuNamaSaved);
            card.setAttribute('data-lokasi', lokasiSaved);
            card.setAttribute('data-tanggal', tglMulaiSaved || '');
            card.setAttribute('data-tanggal-akhir', tglSelesaiSaved || '');
            card.setAttribute('data-catatan', catatanSaved || '-');
            card.setAttribute('data-pengumuman-jadwal', pengumumanSaved || '-');

            const ketuaPcuContainer = card.querySelector('[data-view-ketua-pcu]');
            renderPcuInfo(card);
            if (ketuaPcuContainer) {
              ketuaPcuContainer.textContent = ketuaPcuNamaSaved || '-';
            }
            syncPengumumanDraftWithCards();

          }
        } catch (err) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: err.message || 'Gagal menyimpan penugasan.',
            });
          } else {
            alert(err.message || 'Gagal menyimpan penugasan.');
          }
          return;
        }

        const lokasiView = card.getAttribute('data-lokasi') || lokasi;
        const tglMulaiView = card.getAttribute('data-tanggal') || tglMulai || '-';
        const tglSelesaiView = card.getAttribute('data-tanggal-akhir') || tglSelesai || '-';
        const catatanView = card.getAttribute('data-catatan') || catatan || '-';
        const locView = card.querySelector('[data-view-lokasi]');
        const addrMetaView = card.querySelector('[data-view-alamat-meta]');
        const addrView = card.querySelector('[data-view-alamat]');
        const dateView = card.querySelector('[data-view-tanggal-waktu]');
        const accordionDateView = card.querySelector('[data-view-accordion-tanggal]');
        const catView = card.querySelector('[data-view-catatan]');
        const pengumumanView = card.querySelector('[data-view-pengumuman-jadwal]');

        if (locView) locView.textContent = lokasiView;
        if (addrMetaView) {
          const prov = card.getAttribute('data-provinsi') || '-';
          const kota = card.getAttribute('data-kota') || '-';
          addrMetaView.textContent = `Provinsi/Kota: ${prov} / ${kota}`;
        }
        if (addrView) {
          const alamat = card.getAttribute('data-alamat') || '-';
          addrView.textContent = `Alamat: ${alamat}`;
        }
        if (dateView) dateView.textContent = `${tglMulaiView} s/d ${tglSelesaiView}`;
        if (accordionDateView) accordionDateView.textContent = `${tglMulaiView} s/d ${tglSelesaiView}`;
        if (catView) catView.textContent = catatanView;
        if (pengumumanView) {
          const text = card.getAttribute('data-pengumuman-jadwal') || pengumumanJadwal || '-';
          pengumumanView.textContent = text;
        }

        syncSendButtonState(card);
        filterCards();
        modal.hide();
      });

      const handleAddRow = (role) => {
        if (role === 'pcu') {
          formEls.pcuList?.appendChild(createNameRow('', 'pcu'));
          refreshPcuSelectOptions();
          refreshKetuaPcuOptions(formEls.ketuaPcu?.value || null);
        }
      };

      addPcuBtn?.addEventListener('click', () => handleAddRow('pcu'));

      modalEl?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-remove-row]');
        if (!btn) return;

        const role = btn.getAttribute('data-remove-row');
        const row = btn.closest('[data-name-row]');
        if (row && role) {
          row.remove();
          if (role === 'pcu' && formEls.pcuList?.children.length === 0) {
            formEls.pcuList.appendChild(createNameRow('', 'pcu'));
          }
          refreshKetuaPcuOptions(formEls.ketuaPcu?.value || null);
        }
      });

      modalEl?.addEventListener('change', (event) => {
        if (event.target.closest('#editPcuList select')) {
          refreshPcuSelectOptions();
          refreshKetuaPcuOptions(formEls.ketuaPcu?.value || null);
        }
      });

      const initPengumumanForm = () => {
        syncPengumumanDraftWithCards();
        const draft = readPengumumanDraft();
        annEls.judul.value = draft.judul || '';
        annEls.rentangMulai.value = draft.rentang_mulai || '';
        annEls.rentangSelesai.value = draft.rentang_selesai || '';
        annEls.catatan.value = draft.catatan || '';
        refreshPengumumanRowsByRange();
      };

      annEls.addRowBtn?.addEventListener('click', () => {
        const tr = createAnnRow({});
        annEls.rowsBody?.appendChild(tr);
      });

      annEls.rowsBody?.addEventListener('change', (event) => {
        const tr = event.target.closest('tr');
        if (!tr) return;
        if (event.target.matches('[data-ann="jadwal"]')) {
          autoFillAnnRow(tr);
        }
      });

      let isRedirectingFromAnnDate = false;
      const openEditFromPengumumanRow = (tr) => {
        if (!tr) return;
        if (!canEdit) {
          Swal.fire({
            icon: 'info',
            title: 'Akses edit tidak tersedia',
            text: 'Tanggal jadwal hanya bisa diubah melalui fitur Edit Jadwal.',
          });
          return;
        }

        const jadwalId = tr.querySelector('[data-ann="jadwal"]')?.value || '';
        if (!jadwalId) {
          Swal.fire({
            icon: 'warning',
            title: 'Pilih perusahaan dulu',
            text: 'Pilih perusahaan pada baris ini sebelum mengubah tanggal.',
          });
          return;
        }

        const targetCard = getCardById(jadwalId);
        if (!targetCard) {
          Swal.fire({
            icon: 'error',
            title: 'Jadwal tidak ditemukan',
            text: 'Data jadwal untuk baris ini tidak tersedia.',
          });
          return;
        }

        const pengumumanModalEl = document.getElementById('pengumumanFormModal');
        const pengumumanModal = (window.bootstrap && pengumumanModalEl)
          ? window.bootstrap.Modal.getInstance(pengumumanModalEl)
          : null;

        if (pengumumanModalEl?.classList.contains('show') && pengumumanModal) {
          pengumumanModalEl.addEventListener('hidden.bs.modal', () => {
            openModal(targetCard);
          }, { once: true });
          pengumumanModal.hide();
          return;
        }

        openModal(targetCard);
      };

      annEls.rowsBody?.addEventListener('click', (event) => {
        const dateInput = event.target.closest('[data-ann="tanggal-mulai"], [data-ann="tanggal-selesai"]');
        if (!dateInput) return;
        if (isRedirectingFromAnnDate) return;
        isRedirectingFromAnnDate = true;
        event.preventDefault();
        const tr = dateInput.closest('tr');
        openEditFromPengumumanRow(tr);
        setTimeout(() => { isRedirectingFromAnnDate = false; }, 250);
      });

      annEls.rowsBody?.addEventListener('focusin', (event) => {
        const dateInput = event.target.closest('[data-ann="tanggal-mulai"], [data-ann="tanggal-selesai"]');
        if (!dateInput) return;
        if (isRedirectingFromAnnDate) return;
        isRedirectingFromAnnDate = true;
        dateInput.blur();
        const tr = dateInput.closest('tr');
        openEditFromPengumumanRow(tr);
        setTimeout(() => { isRedirectingFromAnnDate = false; }, 250);
      });

      annEls.rowsBody?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-ann="hapus"]');
        if (!btn) return;
        const tr = btn.closest('tr');
        if (!tr) return;
        tr.remove();
        if (!annEls.rowsBody.querySelector('tr')) {
          annEls.rowsBody.appendChild(createAnnRow({}));
        }
      });
      annEls.rentangMulai?.addEventListener('change', refreshPengumumanRowsByRange);
      annEls.rentangSelesai?.addEventListener('change', refreshPengumumanRowsByRange);

      const collectAnnRows = () => {
        return Array.from(annEls.rowsBody?.querySelectorAll('tr') || [])
          .map((tr) => ({
            id: tr.querySelector('[data-ann="jadwal"]')?.value || '',
            tanggal_mulai: tr.querySelector('[data-ann="tanggal-mulai"]')?.value || '',
            tanggal_selesai: tr.querySelector('[data-ann="tanggal-selesai"]')?.value || '',
            jenis: tr.querySelector('[data-ann="jenis"]')?.value || '',
            pelaksana: tr.querySelector('[data-ann="pelaksana"]')?.value || '',
            keterangan: tr.querySelector('[data-ann="keterangan"]')?.value || '',
          }))
          .filter((row) => row.id);
      };

      document.getElementById('pengumumanFormModal')?.addEventListener('show.bs.modal', () => {
        initPengumumanForm();
      });

      initPengumumanForm();

      if (downloadPengumumanFormBtn) downloadPengumumanFormBtn.onclick = () => {
        const rows = collectAnnRows();
        if (!rows.length) {
          Swal.fire({ icon: 'warning', title: 'Data belum ada', text: 'Tambahkan minimal 1 baris jadwal.' });
          return;
        }

        const judul = annEls.judul?.value || 'JADWAL SAMPLING';
        const rentangMulaiRaw = annEls.rentangMulai?.value || '';
        const rentangSelesaiRaw = annEls.rentangSelesai?.value || '';
        const rentangTanggal = (rentangMulaiRaw && rentangSelesaiRaw)
          ? `Tanggal ${toIndoDate(rentangMulaiRaw)} s/d ${toIndoDate(rentangSelesaiRaw)}`
          : '';
        const pengumuman = annEls.catatan?.value || '-';
        const today = new Date();

        writePengumumanDraft({
          judul,
          rentang_mulai: rentangMulaiRaw,
          rentang_selesai: rentangSelesaiRaw,
          catatan: pengumuman,
          rows,
        });

        const w = window.open('', 'pengumuman_jadwal_window', 'width=980,height=900');
        if (!w) return;
        const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;
        const notes = (pengumuman || '')
          .split('\n')
          .map((line) => line.trim())
          .filter(Boolean)
          .map((line) => `<li>${line}</li>`)
          .join('');
        const tableRows = rows.map((row, idx) => {
          const card = getCardById(row.id);
          const sourceItem = getPengumumanSourceItem(row.id);
          const perusahaan = card?.getAttribute('data-pelanggan-label') || sourceItem?.pelanggan || '-';
          const mulai = row.tanggal_mulai || row.tanggal || '';
          const selesai = row.tanggal_selesai || '';
          const hariTanggalText = toIndoRangeCompact(mulai, selesai);
          const jenis = row.jenis || (card ? resolveKategoriText(card) : getPengumumanKategoriText(sourceItem));
          const pelaksana = row.pelaksana || (card ? getOrderedPcuNames(card).join(', ') : getPengumumanPelaksanaText(sourceItem)) || '-';
          const keterangan = row.keterangan || '-';
          return `
            <tr>
              <td style="text-align:center;">${idx + 1}</td>
              <td>${perusahaan}</td>
              <td style="text-align:center;">${hariTanggalText}</td>
              <td style="text-align:center;">${jenis}</td>
              <td style="text-align:center;">${pelaksana}</td>
              <td style="text-align:center;">${keterangan}</td>
            </tr>
          `;
        }).join('');

        w.document.write(`
          <html>
            <head>
              <title>Pengumuman Jadwal Sampling</title>
              <style>
                body { font-family: "Times New Roman", serif; padding: 18px 22px; color: #111; font-size: 13px; }
                .header-wrap { display: flex; gap: 10px; align-items: center; }
                .logo-wrap { width: 90px; text-align: center; }
                .logo-wrap img { width: 72px; height: 72px; object-fit: contain; }
                .header-text {
                  flex: 1;
                  line-height: 1.12;
                  text-align: left;
                  font-family: "Arial Narrow", Arial, sans-serif;
                  border-left: 2px solid #163e67;
                  padding-left: 12px;
                }
                .header-text .h1,
                .header-text .h2,
                .header-text .h4 { font-size: 12px; font-weight: 700; text-transform: uppercase; }
                .header-text .h2 { white-space: nowrap; }
                .header-text .h1 { font-weight: 500; }
                .header-text .h5 {
                  font-size: 17px;
                  font-weight: 700;
                  text-transform: uppercase;
                  line-height: 1.05;
                  margin-top: 3px;
                  color: #163e67;
                  white-space: nowrap;
                }
                .header-text .addr {
                  font-size: 11px;
                  margin-top: 3px;
                  font-family: Arial, sans-serif;
                }
                .header-text .addr .icon {
                  color: #163e67;
                  font-weight: 700;
                  margin: 0 2px;
                }
                .line-top { border-top: 3px solid #000; margin-top: 6px; }
                .line-bottom { border-top: 1px solid #000; margin-top: 2px; }
                .title { text-align: center; margin: 18px 0 2px; font-size: 18px; font-weight: 700; text-transform: uppercase; }
                .subtitle { text-align: center; margin-bottom: 14px; font-size: 15px; font-weight: 700; }
                table { width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 13px; }
                th, td { border: 1px solid #000; padding: 6px 7px; vertical-align: middle; }
                th { text-align: center; font-weight: 700; }
                .notes { margin-top: 14px; font-size: 11px; }
                .notes ul { margin: 3px 0 0 15px; }
                .notes li { margin: 1px 0; }
                .sign { margin-top: 44px; margin-left: auto; width: 230px; text-align: center; font-size: 12px; line-height: 1.35; }
                .sig-space { height: 64px; }
                .signature-img { display: block; margin: 8px auto 4px; max-height: 62px; max-width: 170px; object-fit: contain; }
                .name { font-size: 12px; margin-top: 8px; font-weight: 700; }
                @media print { @page { size: A4 portrait; margin: 10mm; } body { padding: 0; } }
              </style>
            </head>
            <body>
              <div class="header-wrap">
                <div class="logo-wrap"><img id="printLogo" src="${logoUrl}" alt="Logo" onerror="this.onerror=null;this.src='${window.location.origin}/images/Logo%20Kemnaker.png';"></div>
                <div class="header-text">
                  <div class="h1">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
                  <div class="h2">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
                  <div class="h4">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
                  <div class="h5">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
                  <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id</div>
                </div>
              </div>
              <div class="line-top"></div>
              <div class="line-bottom"></div>
              <div class="title">${judul}</div>
              <div class="subtitle">${rentangTanggal || '-'}</div>
              <table>
                <thead>
                  <tr>
                    <th style="width:6%;">No</th>
                    <th style="width:26%;">Nama Perusahaan (Tempat)</th>
                    <th style="width:18%;">Hari/Tgl Pelaksanaan</th>
                    <th style="width:20%;">Jenis<br>Pengukura/Kegiat<br>an</th>
                    <th style="width:15%;">Pelaksana</th>
                    <th style="width:15%;">Keterangan/Kendaraan</th>
                  </tr>
                </thead>
                <tbody>
                  ${tableRows}
                </tbody>
              </table>
              <div class="notes">
                <div><strong>Catatan:</strong></div>
                <ul>${notes || '<li>-</li>'}</ul>
              </div>
              <div class="sign">
                Surabaya, ${today.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}<br>
                Penyelia PCU
                ${pembuatFormSignature ? `<img id="printSignature" src="${pembuatFormSignature}" alt="TTD Pembuat Form" class="signature-img">` : '<div class="sig-space"></div>'}
                <div class="name">${pembuatFormName || '-'}</div>
              </div>
              <script>
                (function () {
                  const ids = ['printLogo', 'printSignature'];
                  const imgs = ids
                    .map((id) => document.getElementById(id))
                    .filter(Boolean);
                  let remaining = imgs.length;
                  const done = () => setTimeout(() => window.print(), 250);
                  if (!remaining) return done();
                  const onFinish = () => {
                    remaining -= 1;
                    if (remaining <= 0) done();
                  };
                  imgs.forEach((img) => {
                    if (img.complete) {
                      onFinish();
                      return;
                    }
                    img.addEventListener('load', onFinish, { once: true });
                    img.addEventListener('error', onFinish, { once: true });
                  });
                  setTimeout(done, 1500);
                })();
              <\/script>
            </body>
          </html>
        `);
        w.document.close();
      };

      cards.forEach((card) => {
        renderPcuInfo(card);
        syncSendButtonState(card);
      });

      const escapeHtml = (value) => String(value ?? '-')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

      document.querySelectorAll('[data-confirm-send-to-admin]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
          event.preventDefault();
          const submitter = event.submitter || form.querySelector('button[type="submit"]');
          window.WorkflowLoading?.releaseButton(submitter);
          const card = form.closest('[data-jadwal-card]');
          if (isSentToMa(card)) {
            Swal.fire({
              icon: 'info',
              title: 'Sudah dikirim ke MA',
              text: 'Data ini sudah dikirim ke Approval MA dan tidak perlu dikirim ulang.',
            });
            return;
          }
          if (!hasPcuAssigned(card)) {
            Swal.fire({
              icon: 'warning',
              title: 'Petugas PCU belum dipilih',
              text: 'Pilih petugas PCU terlebih dahulu sebelum mengirim ke Approval MA.',
            });
            return;
          }

          const perusahaan = card?.getAttribute('data-pelanggan-label') || '-';
          const lokasi = card?.getAttribute('data-lokasi') || '-';
          const tanggalMulai = card?.getAttribute('data-tanggal') || '-';
          const tanggalSelesai = card?.getAttribute('data-tanggal-akhir') || '-';
          const tanggalRingkas = toIndoRangeCompact(tanggalMulai, tanggalSelesai);
          const ketuaPcu = card?.getAttribute('data-ketua-pcu-nama') || '-';
          const catatan = card?.getAttribute('data-catatan') || '-';
          const pcuNames = getOrderedPcuNames(card);
          const pcuText = pcuNames.length ? pcuNames.join(', ') : '-';
          const parameterRows = Array.from(card?.querySelectorAll('.penjadwalan-parameter-table tbody tr') || []);
          const parameterList = parameterRows
            .map((row) => {
              const kategoriRaw = row.getAttribute('data-param-kategori') || '';
              const kategori = expandKategori(kategoriRaw) || '-';
              const nama = row.querySelector('.penjadwalan-parameter-name')?.textContent?.trim() || '';
              const qty = row.querySelector('.penjadwalan-qty-cell')?.textContent?.trim() || '';
              if (!nama) return '';
              return `<li>${escapeHtml(kategori)} - ${escapeHtml(nama)}${qty ? ` (Qty: ${escapeHtml(qty)})` : ''}</li>`;
            })
            .filter(Boolean)
            .join('');
          const parameterHtml = parameterList
            ? `<ul style="margin:0; padding-left:18px; line-height:1.45;">${parameterList}</ul>`
            : '<span>-</span>';

          const result = await Swal.fire({
            icon: 'info',
            title: 'Pengecekan Data Penjadwalan',
            width: 680,
            customClass: {
              popup: 'swal-penjadwalan-check',
              title: 'swal-penjadwalan-check-title',
            },
            html: `
              <div style="text-align:left; font-size:14px; color:#24364d; line-height:1.45;">
                <div style="display:grid; grid-template-columns: 170px 1fr; gap:6px 10px; margin-bottom:10px;">
                  <div style="font-weight:600; color:#15406A;">Nama PT</div><div>${escapeHtml(perusahaan)}</div>
                  <div style="font-weight:600; color:#15406A;">Lokasi</div><div>${escapeHtml(lokasi)}</div>
                  <div style="font-weight:600; color:#15406A;">Tanggal</div><div>${escapeHtml(tanggalRingkas)}</div>
                  <div style="font-weight:600; color:#15406A;">Petugas PCU</div><div>${escapeHtml(pcuText)}</div>
                  <div style="font-weight:600; color:#15406A;">Ketua PCU</div><div>${escapeHtml(ketuaPcu)}</div>
                  <div style="font-weight:600; color:#15406A;">Catatan</div><div>${escapeHtml(catatan)}</div>
                  <div style="font-weight:600; color:#15406A;">Parameter Uji</div><div>${parameterHtml}</div>
                </div>
                <div style="border-top:1px solid #d9e3f0; padding-top:10px;">
                  <div style="font-size:13px; color:#52667f; margin-bottom:10px;">
                    Alur berikutnya: <strong>Approval MA</strong> lalu <strong>Dokumen SPT (Admin)</strong>.
                  </div>
                  <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer; margin:0;">
                    <input type="checkbox" id="confirmDataCheck" style="margin-top:3px;">
                    <span style="font-size:13px;">Saya memastikan user sudah menyetujui jadwal dan data siap diteruskan ke Approval MA.</span>
                  </label>
                </div>
              </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Kirim ke Approval MA',
            cancelButtonText: 'Batal',
            preConfirm: () => {
              const checked = document.getElementById('confirmDataCheck')?.checked;
              if (!checked) {
                Swal.showValidationMessage('Centang konfirmasi pengecekan data terlebih dahulu.');
                return false;
              }
              return true;
            }
          });

          if (result.isConfirmed) {
            window.WorkflowLoading?.setButtonLoading(submitter, true);
            form.submit();
          }
        });
      });

      document.querySelectorAll('[data-confirm-send-to-user]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
          event.preventDefault();
          const submitter = event.submitter || form.querySelector('button[type="submit"]');
          window.WorkflowLoading?.releaseButton(submitter);
          const card = form.closest('[data-jadwal-card]');
          if (!card) return;

          if (!hasPcuAssigned(card)) {
            Swal.fire({
              icon: 'warning',
              title: 'Petugas PCU belum dipilih',
              text: 'Pilih petugas PCU terlebih dahulu sebelum mengirim jadwal ke user.',
            });
            return;
          }

          const result = await Swal.fire({
            icon: 'question',
            title: 'Kirim jadwal ke user?',
            text: 'Jadwal yang sudah ditentukan akan muncul di riwayat pelayanan user dan menunggu ACC dari user.',
            showCancelButton: true,
            confirmButtonText: 'Kirim ke User',
            cancelButtonText: 'Batal',
          });

          if (!result.isConfirmed) {
            return;
          }

          window.WorkflowLoading?.setButtonLoading(submitter, true);
          form.submit();
        });
      });

      document.querySelectorAll('[data-cancel-jadwal]').forEach((btn) => {
        btn.addEventListener('click', async () => {
          if (!canEdit) return;
          const card = btn.closest('[data-jadwal-card]');
          if (!card) return;
          const cancelUrl = card.getAttribute('data-cancel-url') || '';
          if (!cancelUrl) return;

          const reasonResult = await Swal.fire({
            icon: 'warning',
            title: 'Batalkan Penjadwalan',
            input: 'text',
            inputLabel: 'Alasan pembatalan',
            inputPlaceholder: 'Contoh: Permintaan pelanggan',
            showCancelButton: true,
            confirmButtonText: 'Lanjut',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
              if (!value || !value.trim()) return 'Alasan wajib diisi';
              return null;
            },
          });
          if (!reasonResult.isConfirmed) return;

          const noteResult = await Swal.fire({
            title: 'Catatan (opsional)',
            input: 'textarea',
            inputPlaceholder: 'Catatan tambahan',
            showCancelButton: true,
            confirmButtonText: 'Batalkan Jadwal',
            cancelButtonText: 'Kembali',
          });
          if (!noteResult.isConfirmed) return;

          try {
            const response = await fetch(cancelUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
              },
              body: JSON.stringify({
                reason: reasonResult.value.trim(),
                note: noteResult.value || null,
              }),
            });

            if (!response.ok) {
              const result = await response.json().catch(() => ({}));
              throw new Error(result.message || 'Gagal membatalkan penjadwalan.');
            }

            await Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: 'Jadwal dibatalkan. Silakan revisi tanggal penjadwalan.',
            });
            card.setAttribute('data-tanggal', '');
            card.setAttribute('data-tanggal-akhir', '');
            const dateView = card.querySelector('[data-view-tanggal-waktu]');
            const accordionDateView = card.querySelector('[data-view-accordion-tanggal]');
            if (dateView) dateView.textContent = '- s/d -';
            if (accordionDateView) accordionDateView.textContent = '- s/d -';
            openModal(card);
          } catch (err) {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: err.message || 'Gagal membatalkan penjadwalan.',
            });
          }
        });
      });
    })();
  </script>
@endpush

@push('styles')
  <style>
    .penjadwalan-search-card {
      font-family: 'Poppins', sans-serif;
      border: 1px solid #dfe6f1;
      background: #f8fafd;
    }

    .swal-penjadwalan-check {
      font-family: 'Poppins', sans-serif;
      border-radius: 14px !important;
      padding: 1rem 1.1rem 1.15rem !important;
    }

    .swal-penjadwalan-check-title {
      font-size: 1.85rem !important;
      line-height: 1.15 !important;
      color: #1d2f46 !important;
      margin-bottom: 0.2rem !important;
    }

    .penjadwalan-search-label {
      font-size: 0.72rem;
      font-weight: 600;
      letter-spacing: 0.03em;
      color: #7b8fa9 !important;
    }

    .penjadwalan-search-input-wrap {
      position: relative;
    }

    .penjadwalan-search-icon {
      position: absolute;
      left: 0.78rem;
      top: 50%;
      transform: translateY(-50%);
      color: #8da2ba;
      font-size: 0.82rem;
      pointer-events: none;
    }

    .penjadwalan-search-input {
      border-radius: 11px;
      border-color: #d8e2ef;
      min-height: 40px;
      font-size: 0.86rem;
      padding-left: 2.05rem;
    }

    .penjadwalan-search-input:focus {
      border-color: #15406a;
      box-shadow: 0 0 0 0.14rem rgba(21, 64, 106, 0.15);
    }

    .penjadwalan-search-reset {
      min-height: 40px;
      border-radius: 11px;
      font-size: 0.84rem;
      font-weight: 600;
      color: #15406a;
      border-color: #cfd9e8;
    }

    .modal-custom .modal-content {
      border-radius: 16px;
    }

    .penjadwalan-shell {
      font-family: 'Poppins', sans-serif;
      background: #f7fafe;
      border: 1px solid #dce6f1;
      border-radius: 22px;
      padding: 1.25rem;
      box-shadow: 0 14px 34px rgba(21, 64, 106, 0.08);
    }

    .penjadwalan-accordion {
      --bs-accordion-border-width: 0;
      --bs-accordion-border-color: transparent;
      --bs-accordion-bg: #fff;
      --bs-accordion-btn-focus-box-shadow: 0 0 0 0.18rem rgba(21, 64, 106, 0.18);
      --bs-accordion-active-bg: #f4f8fc;
      --bs-accordion-active-color: #15406A;
    }

    .penjadwalan-accordion .accordion-item {
      border: 1px solid #dbe6f3 !important;
      background: #fff;
      box-shadow: 0 8px 20px rgba(15, 47, 83, 0.08);
    }

    .penjadwalan-accordion-button {
      background: #15406A;
      color: #ffffff;
      font-weight: 600;
      padding: 0.95rem 1.1rem;
    }

    .penjadwalan-accordion-button:not(.collapsed) {
      color: #ffffff;
      background: #15406A;
      box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.18);
    }

    .penjadwalan-accordion-head {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: center;
      width: 100%;
      gap: 0.28rem;
      padding-right: 0.75rem;
    }

    .penjadwalan-accordion-company {
      font-size: 1.02rem;
      font-weight: 700;
      color: #ffffff;
      line-height: 1.2;
    }

    .penjadwalan-accordion-schedule {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      color: rgba(255, 255, 255, 0.92);
      font-size: 0.82rem;
      font-weight: 400;
    }

    .penjadwalan-accordion-schedule i,
    .penjadwalan-accordion-schedule span {
      font-weight: 400 !important;
    }

    .penjadwalan-accordion-age {
      display: inline-flex;
      align-items: center;
      gap: 0.32rem;
      margin-left: auto;
      margin-right: 0.48rem;
      color: #ffffff;
      background: transparent;
      border: 0;
      border-radius: 0;
      padding: 0;
      font-size: 10px;
      font-weight: 600;
      white-space: nowrap;
      line-height: 1;
    }

    .penjadwalan-accordion-age i {
      font-size: 10px;
      line-height: 1;
    }

    .penjadwalan-accordion-button::after {
      filter: brightness(0) invert(1);
    }

    .penjadwalan-accordion-body {
      animation: penjadwalanFadeIn 0.28s ease;
      background: #fff;
    }

    @keyframes penjadwalanFadeIn {
      from { opacity: 0; transform: translateY(-4px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .penjadwalan-panel {
      background: #fff;
      border: 1px solid #dce5ef;
      border-radius: 18px;
      padding: 1rem 1.05rem;
      box-shadow: 0 8px 20px rgba(21, 64, 106, 0.04);
    }

    .penjadwalan-company-panel {
      padding-bottom: 0.85rem;
    }

    .penjadwalan-section-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 0.75rem;
      margin-bottom: 1rem;
    }

    .penjadwalan-section-title {
      display: inline-flex;
      align-items: center;
      gap: 0.55rem;
      color: #0f2f53;
      font-size: 0.86rem;
      font-weight: 700;
    }

    .penjadwalan-section-title i {
      color: #15406A;
      font-size: 0.85rem;
    }

    .penjadwalan-company-body {
      display: flex;
      gap: 1rem;
      align-items: flex-start;
      min-height: 0;
    }

    .penjadwalan-company-icon {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      background: #eef4fb;
      color: #15406A;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      flex: 0 0 auto;
    }

    .penjadwalan-company-content {
      min-width: 0;
      flex: 1 1 auto;
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }

    .penjadwalan-company-label,
    .penjadwalan-meta-label {
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #15406A;
      font-size: 0.57rem;
      font-weight: 700;
    }

    .penjadwalan-company-name {
      color: #0f2f53;
      font-size: 1.05rem;
      font-weight: 700;
      line-height: 1.35;
      margin-bottom: 0;
    }

    .penjadwalan-meta-value,
    .penjadwalan-meta-inline {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      color: #425b78;
      font-size: 0.78rem;
      line-height: 1.6;
    }

    .penjadwalan-meta-value i,
    .penjadwalan-meta-inline i {
      color: #15406A;
      font-size: 0.74rem;
      flex: 0 0 auto;
    }

    .penjadwalan-meta-stack {
      display: flex;
      flex-direction: column;
      gap: 0.2rem;
    }

    .penjadwalan-company-panel .row.g-3 {
      --bs-gutter-x: 1rem;
      --bs-gutter-y: 0.45rem;
      margin-top: 0.05rem !important;
    }

    .penjadwalan-order-chip {
      display: inline-flex;
      align-items: center;
      min-height: 30px;
      padding: 0.38rem 0.75rem;
      border-radius: 999px;
      background: #eef4fb;
      color: #15406A;
      font-size: 0.7rem;
      font-weight: 600;
    }

    .penjadwalan-info-group + .penjadwalan-info-group {
      margin-top: 0.95rem;
    }

    .penjadwalan-field-label {
      color: #203d5f;
      font-size: 0.72rem;
      font-weight: 600;
      margin-bottom: 0.42rem;
    }

    .penjadwalan-info-box,
    .penjadwalan-note-box {
      border: 1px solid #dbe5ef;
      background: #f9fbff;
      border-radius: 12px;
      padding: 0.8rem 0.85rem;
      color: #49617d;
      font-size: 0.78rem;
      line-height: 1.65;
      min-height: 50px;
    }

    .penjadwalan-info-box.is-single {
      display: flex;
      align-items: center;
      min-height: 46px;
    }

    .penjadwalan-note-box {
      min-height: 90px;
      white-space: pre-line;
    }

    .penjadwalan-pcu-list {
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
    }

    .penjadwalan-pcu-list li {
      color: #49617d;
      font-size: 0.78rem;
    }

    .penjadwalan-pcu-list li.is-empty {
      color: #8798ac;
    }

    .penjadwalan-stage-filter-btn {
      border-radius: 10px !important;
      min-height: 38px;
      font-size: 0.82rem;
      font-weight: 700;
      padding: 0.45rem 1rem;
      box-shadow: none !important;
      border-width: 1px !important;
      transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    .penjadwalan-stage-filter-btn.btn-outline-deep-blue {
      background-color: #ffffff !important;
      border-color: #15406A !important;
      color: #15406A !important;
    }

    .penjadwalan-stage-filter-btn.btn-outline-deep-blue:hover,
    .penjadwalan-stage-filter-btn.btn-outline-deep-blue:focus,
    .penjadwalan-stage-filter-btn.btn-outline-deep-blue:active,
    .penjadwalan-stage-filter-btn.btn-outline-deep-blue.active {
      background-color: #15406A !important;
      border-color: #15406A !important;
      color: #ffffff !important;
    }

    .penjadwalan-stage-filter-btn.btn-deep-blue {
      background-color: #15406A !important;
      border-color: #15406A !important;
      color: #ffffff !important;
    }

    .penjadwalan-stage-filter-btn.btn-deep-blue:hover,
    .penjadwalan-stage-filter-btn.btn-deep-blue:focus,
    .penjadwalan-stage-filter-btn.btn-deep-blue:active,
    .penjadwalan-stage-filter-btn.btn-deep-blue.active {
      background-color: #15406A !important;
      border-color: #15406A !important;
      color: #ffffff !important;
    }

    .penjadwalan-stage-filter-btn .badge {
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

    .penjadwalan-stage-filter-btn .badge.text-bg-danger {
      background-color: #e53950 !important;
      color: #ffffff !important;
      border-color: #e53950 !important;
    }

    .penjadwalan-action-stack {
      display: flex;
      flex-direction: column;
      gap: 0.7rem;
      margin-top: 1rem;
    }

    .penjadwalan-primary-btn,
    .penjadwalan-secondary-btn,
    .penjadwalan-danger-btn {
      min-height: 44px;
      border-radius: 12px;
      font-size: 0.82rem;
      font-weight: 600;
      padding: 0.7rem 1rem;
    }

    .penjadwalan-primary-btn {
      background: #15406A;
      border: 1px solid #15406A;
      color: #fff;
    }

    .penjadwalan-primary-btn:hover,
    .penjadwalan-primary-btn:focus {
      background: #0f3358;
      border-color: #0f3358;
      color: #fff;
    }

    .penjadwalan-secondary-btn {
      background: #fff;
      border: 1px solid #15406A;
      color: #15406A;
    }

    .penjadwalan-secondary-btn:hover,
    .penjadwalan-secondary-btn:focus {
      background: #15406A;
      border-color: #15406A;
      color: #fff;
    }

    .penjadwalan-danger-btn {
      background: #fff6f7;
      border: 1px solid #f0c4c9;
      color: #c74b59;
    }

    .penjadwalan-danger-btn:hover,
    .penjadwalan-danger-btn:focus {
      background: #ffedf0;
      border-color: #e6b0b8;
      color: #ba3748;
    }

    .penjadwalan-help-box {
      margin-top: 1rem;
      border: 1px solid #e8d4c8;
      background: #fff8f4;
      border-radius: 14px;
      padding: 0.9rem 0.95rem;
      color: #617286;
      font-size: 0.74rem;
      line-height: 1.7;
    }

    .penjadwalan-help-title {
      display: inline-flex;
      align-items: center;
      gap: 0.45rem;
      color: #0f2f53;
      font-size: 0.8rem;
      font-weight: 700;
      margin-bottom: 0.35rem;
    }

    .penjadwalan-help-title i {
      color: #15406A;
      font-size: 0.82rem;
    }

    .penjadwalan-total-badge {
      display: inline-flex;
      align-items: center;
      padding: 0.32rem 0.65rem;
      border-radius: 999px;
      background: #eef4fb;
      color: #15406A;
      font-size: 0.66rem;
      font-weight: 700;
    }

    .penjadwalan-parameter-table thead th {
      background: #f4f8fc;
      color: #5e7692;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      font-size: 0.62rem;
      font-weight: 700;
      border-bottom: 1px solid #e3edf7;
      padding: 0.88rem 0.85rem;
    }

    .penjadwalan-parameter-table tbody td {
      border-color: #edf2f8;
      padding: 0.88rem 0.85rem;
      color: #213d5d;
      font-size: 0.8rem;
      vertical-align: top;
    }

    .penjadwalan-param-no {
      color: #6f84a1;
      width: 70px;
    }

    .penjadwalan-category-cell,
    .penjadwalan-parameter-name,
    .penjadwalan-qty-cell {
      font-weight: 600;
    }

    .penjadwalan-category-cell {
      color: #0f2f53;
    }

    .penjadwalan-qty-cell {
      color: #15406A;
    }

    .penjadwalan-parameter-foot {
      border-top: 1px solid #edf2f8;
      margin-top: 0.1rem;
      padding: 0.7rem 0.85rem 0.1rem;
      color: #7b8fa7;
      font-size: 0.72rem;
      font-style: italic;
    }

    .edit-jadwal-modal-content {
      font-family: 'Poppins', sans-serif;
      border: 1px solid #dce6f0;
      background: #fbfdff;
      box-shadow: 0 18px 42px rgba(21, 64, 106, 0.12);
    }

    .edit-jadwal-modal-dialog {
      max-width: 860px;
    }

    .edit-jadwal-modal-header {
      padding: 0.95rem 1.05rem 0.72rem;
      border-bottom: 1px solid #e5edf5;
    }

    .edit-jadwal-modal-title {
      color: #203247;
      font-size: 0.9rem;
      font-weight: 700;
      margin: 0;
    }

    .edit-jadwal-modal-body {
      padding: 0.95rem 1.05rem 0.85rem;
    }

    .edit-jadwal-layout {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.9rem 1rem;
      align-items: start;
    }

    .edit-jadwal-span-2 {
      grid-column: 1 / -1;
    }

    .edit-jadwal-field {
      min-width: 0;
    }

    .edit-jadwal-label {
      font-size: 0.72rem;
      font-weight: 400;
      color: #203247 !important;
      letter-spacing: 0.01em;
    }

    .edit-jadwal-input,
    #editPcuList .form-select,
    #editPcuList .form-control {
      min-height: 38px;
      border-radius: 10px;
      border-color: #d7e1eb;
      background: #ffffff;
      color: #223e5d;
      font-size: 0.8rem;
      box-shadow: none;
    }

    .edit-jadwal-input:focus,
    #editPcuList .form-select:focus,
    #editPcuList .form-control:focus {
      border-color: #15406A;
      box-shadow: 0 0 0 0.14rem rgba(21, 64, 106, 0.12);
    }

    .edit-jadwal-textarea {
      min-height: 74px;
      resize: none;
      padding-top: 0.58rem;
      padding-bottom: 0.58rem;
    }

    .edit-jadwal-help {
      margin-top: 0.28rem;
      color: #6b7d91;
      font-size: 0.68rem;
      line-height: 1.45;
    }

    .edit-jadwal-add-btn {
      width: 26px;
      height: 26px;
      padding: 0;
      border-radius: 999px;
      background: #15406A;
      border-color: #15406A;
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .edit-jadwal-add-btn:hover,
    .edit-jadwal-add-btn:focus {
      background: #0f2f53;
      border-color: #0f2f53;
      color: #fff;
    }

    .edit-jadwal-add-btn i {
      font-size: 0.66rem;
      line-height: 1;
    }

    #editPcuList [data-name-row] {
      display: grid !important;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 0.55rem;
      align-items: center;
    }

    #editPcuList [data-remove-row] {
      width: 36px;
      height: 36px;
      padding: 0;
      border-radius: 9px;
      border-color: #f0b8bf;
      color: #e04b5a;
      background: #fff7f8;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    #editPcuList [data-remove-row]:hover,
    #editPcuList [data-remove-row]:focus {
      border-color: #e89aa6;
      background: #ffecee;
      color: #cf3044;
    }

    .edit-jadwal-modal-footer {
      padding: 0.8rem 1.05rem 0.95rem;
      border-top: 1px solid #e5edf5;
      justify-content: flex-end;
      gap: 0.55rem;
    }

    .edit-jadwal-cancel-btn,
    .edit-jadwal-save-btn {
      min-width: 84px;
      min-height: 38px;
      border-radius: 10px;
      font-size: 0.78rem;
      font-weight: 600;
    }

    .pengumuman-modal-content {
      font-family: 'Poppins', sans-serif;
      border: 1px solid #dbe5f0;
      background: #f6f9fc;
      box-shadow: 0 22px 48px rgba(21, 64, 106, 0.12);
    }

    .pengumuman-modal-header {
      padding: 1.5rem 1.75rem 0.75rem;
      border-bottom: 0;
      align-items: flex-start;
    }

    .pengumuman-modal-title {
      margin: 0;
      color: #0f2f53;
      font-size: 1.12rem;
      font-weight: 700;
      line-height: 1.25;
    }

    .pengumuman-modal-subtitle {
      margin-top: 0.35rem;
      color: #59708c;
      font-size: 0.82rem;
      line-height: 1.6;
    }

    .pengumuman-modal-body {
      padding: 0 1.75rem 1.5rem;
    }

    .pengumuman-panel {
      background: #ffffff;
      border: 1px solid #d9e4ef;
      border-radius: 14px;
      padding: 1rem 1rem 1.05rem;
    }

    .pengumuman-panel-primary {
      background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .pengumuman-label {
      font-size: 0.72rem;
      font-weight: 600;
      color: #15406A !important;
      letter-spacing: 0.01em;
    }

    .pengumuman-input-wrap {
      position: relative;
    }

    .pengumuman-input-icon {
      position: absolute;
      left: 0.85rem;
      top: 50%;
      transform: translateY(-50%);
      color: #8ca0b8;
      font-size: 0.82rem;
      pointer-events: none;
    }

    .pengumuman-input,
    .pengumuman-textarea,
    .pengumuman-table .form-control,
    .pengumuman-table .form-select {
      border-radius: 10px;
      border-color: #d9e2ec;
      color: #24476d;
      font-size: 0.8rem;
      min-height: 40px;
      background-color: #f9fbfe;
      box-shadow: none;
    }

    .pengumuman-input {
      padding-left: 2.35rem;
    }

    .pengumuman-textarea {
      min-height: 104px;
      resize: vertical;
      padding: 0.85rem 0.95rem;
    }

    .pengumuman-input:focus,
    .pengumuman-textarea:focus,
    .pengumuman-table .form-control:focus,
    .pengumuman-table .form-select:focus {
      border-color: #15406A;
      background-color: #fff;
      box-shadow: 0 0 0 0.14rem rgba(21, 64, 106, 0.12);
    }

    .pengumuman-date-row {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
      gap: 0.75rem;
      align-items: center;
    }

    .pengumuman-date-separator {
      color: #6d829d;
      font-size: 0.76rem;
      font-weight: 600;
    }

    .pengumuman-section-title {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: #123a60;
      font-size: 0.9rem;
      font-weight: 700;
    }

    .pengumuman-section-title i {
      color: #15406A;
      font-size: 0.9rem;
    }

    .pengumuman-add-row-btn {
      background: #15406A;
      border-color: #15406A;
      color: #fff;
      border-radius: 10px;
      font-size: 0.76rem;
      font-weight: 600;
      padding: 0.48rem 0.8rem;
    }

    .pengumuman-add-row-btn:hover,
    .pengumuman-add-row-btn:focus {
      background: #0f2f53;
      border-color: #0f2f53;
      color: #fff;
    }

    .pengumuman-table-wrap {
      border: 1px solid #e1e8f1;
      border-radius: 12px;
      overflow: hidden;
      background: #fff;
    }

    .pengumuman-table {
      margin: 0;
    }

    .pengumuman-table thead th {
      background: #f4f8fc;
      color: #5f7896;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-size: 0.58rem;
      font-weight: 700;
      border-bottom: 1px solid #e4edf7;
      padding: 0.82rem 0.85rem;
      white-space: nowrap;
    }

    .pengumuman-table tbody td {
      padding: 0.72rem 0.85rem;
      border-color: #edf2f8;
      vertical-align: middle;
      background: #fff;
    }

    .pengumuman-table .form-control,
    .pengumuman-table .form-select {
      min-height: 36px;
      font-size: 0.76rem;
      padding: 0.42rem 0.68rem;
    }

    .pengumuman-table input[readonly] {
      background: #f3f7fb;
      color: #5a708c;
    }

    .pengumuman-table [data-ann="hapus"] {
      width: 30px;
      height: 30px;
      padding: 0;
      border-radius: 9px;
      border-color: #f1c6c6;
      color: #cf5353;
      background: #fff8f8;
    }

    .pengumuman-table [data-ann="hapus"]:hover,
    .pengumuman-table [data-ann="hapus"]:focus {
      background: #ffe8e8;
      border-color: #e5b0b0;
      color: #bf3f3f;
    }

    .pengumuman-side-panel {
      background: linear-gradient(180deg, #f7fbff 0%, #eef5fb 100%);
      border: 1px solid #d9e6f2;
      border-radius: 14px;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      gap: 1rem;
    }

    .pengumuman-guide-title {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      color: #123a60;
      font-size: 0.9rem;
      font-weight: 700;
    }

    .pengumuman-guide-title i {
      color: #15406A;
      font-size: 0.86rem;
    }

    .pengumuman-guide-text {
      margin: 0;
      color: #5e6f83;
      font-size: 0.78rem;
      line-height: 1.75;
    }

    .pengumuman-download-btn {
      width: 100%;
      background: #15406A;
      border: 1px solid #15406A;
      color: #fff;
      border-radius: 12px;
      font-size: 0.86rem;
      font-weight: 600;
      padding: 0.76rem 1rem;
      box-shadow: 0 12px 24px rgba(21, 64, 106, 0.2);
    }

    .pengumuman-download-btn:hover,
    .pengumuman-download-btn:focus {
      background: #0f2f53;
      border-color: #0f2f53;
      color: #fff;
    }

    .pengumuman-modal-footer {
      border-top: 0;
      padding: 0 1.75rem 1.5rem;
    }

    .pengumuman-close-btn {
      font-size: 0.8rem;
      border-radius: 10px;
      padding-inline: 0.95rem;
    }

    .modal-backdrop.show {
      opacity: 0.3;
    }

    .btn-deep-blue {
      background-color: #15406A !important;
      border-color: #15406A !important;
      color: #fff !important;
    }

    .btn-deep-blue:hover,
    .btn-deep-blue:focus {
      background-color: #0f2f53 !important;
      border-color: #0f2f53 !important;
      color: #fff !important;
    }

    .btn-outline-deep-blue {
      border-color: #15406A !important;
      color: #15406A !important;
      background-color: #ffffff !important;
    }

    .btn-outline-deep-blue:hover,
    .btn-outline-deep-blue:focus {
      border-color: #15406A !important;
      color: #ffffff !important;
      background-color: #15406A !important;
    }

    #annJudul::placeholder,
    #annCatatan::placeholder,
    #annRowsBody input::placeholder,
    #annRowsBody select::placeholder {
      font-size: 12px;
    }

    #annRentangMulai,
    #annRentangSelesai {
      font-size: 13px;
    }

    #annRentangMulai::placeholder,
    #annRentangSelesai::placeholder {
      font-size: 12px;
    }

    @media (max-width: 991.98px) {
      .penjadwalan-shell {
        padding: 1rem;
      }

      .penjadwalan-company-body {
        flex-direction: column;
      }

      .edit-jadwal-modal-header,
      .edit-jadwal-modal-body,
      .edit-jadwal-modal-footer {
        padding-left: 1rem;
        padding-right: 1rem;
      }

      .edit-jadwal-modal-dialog {
        max-width: 760px;
      }

      .pengumuman-modal-header,
      .pengumuman-modal-body,
      .pengumuman-modal-footer {
        padding-left: 1rem;
        padding-right: 1rem;
      }

      .pengumuman-panel,
      .pengumuman-side-panel {
        padding: 0.9rem;
      }
    }

    @media (max-width: 767.98px) {
      .penjadwalan-panel {
        padding: 0.9rem;
      }

      .penjadwalan-company-name {
        font-size: 0.96rem;
      }

      .penjadwalan-parameter-table {
        min-width: 720px;
      }

      .penjadwalan-primary-btn,
      .penjadwalan-secondary-btn,
      .penjadwalan-danger-btn {
        min-height: 40px;
        font-size: 0.78rem;
      }

      .edit-jadwal-modal-title {
        font-size: 0.92rem;
      }

      .edit-jadwal-layout {
        grid-template-columns: 1fr;
        gap: 0.85rem;
      }

      .edit-jadwal-span-2 {
        grid-column: auto;
      }

      .edit-jadwal-input,
      #editPcuList .form-select,
      #editPcuList .form-control {
        min-height: 40px;
        font-size: 0.82rem;
      }

      .edit-jadwal-modal-footer {
        gap: 0.55rem;
      }

      .edit-jadwal-cancel-btn,
      .edit-jadwal-save-btn {
        min-width: 88px;
        min-height: 42px;
        font-size: 0.8rem;
      }

      .pengumuman-modal-title {
        font-size: 1rem;
      }

      .pengumuman-modal-subtitle,
      .pengumuman-guide-text {
        font-size: 0.75rem;
      }

      .pengumuman-date-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
      }

      .pengumuman-date-separator {
        text-align: center;
      }

      .pengumuman-table {
        min-width: 920px;
      }
    }
  </style>
@endpush
