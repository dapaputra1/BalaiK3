@extends('layouts.app_admin')

@section('title', 'Penerbitan Suket K3 Lingkungan Kerja')

@section('content_admin')
<div class="container-fluid px-0">

    {{-- Alert Notification --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                    <i class="bi bi-shield-check me-1"></i>Menu Khusus Internal
                </span>
                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">
                    Role Anda: <strong class="text-dark">{{ ucfirst($currentRole) }}</strong>
                </span>
            </div>
            <h3 class="fw-bold text-dark mb-1">Penerbitan Surat Keterangan (Suket) K3 Lingkungan Kerja</h3>
            <p class="text-muted mb-0 small">
                Pengelolaan dan monitoring 6 alur penerbitan Suket K3 Lingkungan Kerja dari nomor order hingga penyerahan pelanggan.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('suket.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Segarkan Data
            </a>
        </div>
    </div>

    {{-- SECTION 1: Form Submit Pengajuan Berdasarkan Nomor Order --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div class="d-flex align-items-start gap-3">
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
                            <i class="bi bi-file-earmark-plus-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Input Pengajuan Suket Baru</h5>
                            <p class="text-muted small mb-0">
                                Cukup masukkan atau pilih <strong>Nomor Order / Kode Permohonan</strong> yang telah diuji untuk memulai 6 tahapan penerbitan suket.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <form action="{{ route('suket.store-order') }}" method="POST" class="row g-2">
                        @csrf
                        <div class="col-sm-8">
                            <label class="form-label small fw-semibold text-secondary mb-1">Nomor Order / Kode Permohonan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted">
                                    <i class="bi bi-hash"></i>
                                </span>
                                <input
                                    type="text"
                                    name="nomor_order"
                                    list="orderList"
                                    class="form-control border-start-0 @error('nomor_order') is-invalid @enderror"
                                    placeholder="Contoh: PMH-20260401-0001"
                                    required
                                    autocomplete="off"
                                >
                                <datalist id="orderList">
                                    @foreach($availableOrders as $ord)
                                        <option value="{{ $ord['kode'] }}">{{ $ord['perusahaan'] }} ({{ $ord['lokasi'] }})</option>
                                    @endforeach
                                </datalist>
                                @error('nomor_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-sm-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold shadow-sm" style="background-color: #15406A; border-color: #15406A;">
                                <i class="bi bi-send me-1"></i> Submit Suket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 2: 6 Tahapan Status Pipeline Stepper --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Alur Status Suket (6 Tahap)
            </h6>
            <div class="small text-muted">
                Aktif Berjalan: <span class="badge bg-primary rounded-pill">{{ $totalActive }}</span> |
                Selesai / Terkirim: <span class="badge bg-success rounded-pill">{{ $totalDone }}</span>
            </div>
        </div>

        <div class="row g-3">
            @foreach($stages as $num => $stg)
                @php
                    $count = $stageCounts[$num] ?? 0;
                    $isFiltered = $activeStage == $num;
                    $isMyRole = in_array($currentRole, $stg['roles'], true) || $currentRole === 'superadmin';
                @endphp
                <div class="col-6 col-md-4 col-xl-2">
                    <a href="{{ route('suket.index', ['stage' => $isFiltered ? null : $num]) }}" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 transition-hover {{ $isFiltered ? 'border-2 border-primary bg-primary text-white' : 'bg-white text-dark' }}" style="transition: all 0.2s ease;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge {{ $isFiltered ? 'bg-white text-primary' : 'bg-' . $stg['badge'] . '-subtle text-' . $stg['badge'] }} rounded-pill px-2 py-1 small">
                                    Tahap {{ $num }}
                                </span>
                                <span class="fw-bold fs-5 {{ $isFiltered ? 'text-white' : 'text-dark' }}">{{ $count }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi {{ $stg['icon'] }} {{ $isFiltered ? 'text-white' : 'text-' . $stg['badge'] }} fs-5"></i>
                                <div class="fw-bold small lh-sm">{{ $stg['label'] }}</div>
                            </div>
                            <div class="small mt-auto pt-2 {{ $isFiltered ? 'text-white-50' : 'text-muted' }}" style="font-size: 11px;">
                                @if($isMyRole)
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle px-1 rounded">Kewenangan Anda</span>
                                @else
                                    <span>{{ implode(', ', array_map('strtoupper', $stg['roles'])) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    {{-- SECTION 3: Daftar Monitoring Berkas & Aksi --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold text-dark mb-0">Daftar Pengajuan Suket K3</h6>
                @if($activeStage)
                    <span class="badge bg-primary rounded-pill">
                        Filter: {{ $stages[$activeStage]['label'] ?? "Tahap $activeStage" }}
                        <a href="{{ route('suket.index') }}" class="text-white ms-1 text-decoration-none">&times;</a>
                    </span>
                @endif
            </div>

            <form action="{{ route('suket.index') }}" method="GET" class="d-flex gap-2">
                @if($activeStage)
                    <input type="hidden" name="stage" value="{{ $activeStage }}">
                @endif
                <div class="input-group input-group-sm" style="width: 260px;">
                    <input type="text" name="search" class="form-control rounded-start-3" placeholder="Cari No. Order / Perusahaan..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary rounded-end-3" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                @if($search)
                    <a href="{{ route('suket.index') }}" class="btn btn-sm btn-outline-danger rounded-3" title="Reset Pencarian">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase fw-semibold">
                    <tr>
                        <th style="width: 50px;" class="ps-4">No</th>
                        <th>Nomor Order & Tanggal</th>
                        <th>Perusahaan & Lokasi</th>
                        <th>Status Alur (6 Tahap)</th>
                        <th>Dokumen Terkait</th>
                        <th>Catatan / Keterangan</th>
                        <th class="text-end pe-4" style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($sukets as $idx => $suket)
                        @php
                            $stgInfo = $stages[$suket->status_tahap] ?? [
                                'label' => 'Tahap ' . $suket->status_tahap,
                                'badge' => 'secondary',
                                'roles' => []
                            ];
                            $canProcess = $suket->canRoleProcess($currentRole);
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $sukets->firstItem() + $idx }}</td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $suket->nomor_order }}</div>
                                <div class="text-muted" style="font-size: 11px;">
                                    Didaftarkan: {{ $suket->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $suket->perusahaan_nama ?: '-' }}</div>
                                <div class="text-muted" style="font-size: 11px;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $suket->lokasi ?: '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-{{ $stgInfo['badge'] }} px-2 py-1 rounded-pill">
                                        Tahap {{ $suket->status_tahap }}: {{ $stgInfo['label'] }}
                                    </span>
                                </div>
                                {{-- Mini Visual Progress Bar (1 to 6) --}}
                                <div class="progress" style="height: 6px; width: 140px; background-color: #e9ecef;">
                                    <div
                                        class="progress-bar bg-{{ $stgInfo['badge'] }}"
                                        role="progressbar"
                                        style="width: {{ ($suket->status_tahap / 6) * 100 }}%"
                                        aria-valuenow="{{ $suket->status_tahap }}"
                                        aria-valuemin="1"
                                        aria-valuemax="6">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @if($suket->draft_file_path)
                                        <a href="{{ route('suket.download-doc', [$suket->id, 'draft']) }}" class="text-decoration-none text-primary" style="font-size: 11px;">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Draf Suket
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">- Belum ada draf -</span>
                                    @endif

                                    @if($suket->signed_file_path)
                                        <a href="{{ route('suket.download-doc', [$suket->id, 'signed']) }}" class="text-decoration-none text-success" style="font-size: 11px;">
                                            <i class="bi bi-patch-check-fill text-success me-1"></i>Suket Bertanda Tangan
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-muted" style="max-width: 220px; font-size: 12px;">
                                    {{ $suket->catatan ?: '-' }}
                                </div>
                                @if($suket->resi_pengiriman)
                                    <div class="mt-1 small text-success fw-semibold">
                                        <i class="bi bi-truck me-1"></i>Resi: {{ $suket->resi_pengiriman }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    {{-- Tombol Lanjut Tahap jika role berwenang --}}
                                    @if($canProcess && $suket->status_tahap < 6)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary rounded-pill px-3"
                                            style="background-color: #15406A; border-color: #15406A;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAdvance{{ $suket->id }}"
                                        >
                                            @if($suket->status_tahap === 1)
                                                <i class="bi bi-arrow-right me-1"></i> Ke Evaluasi
                                            @elseif($suket->status_tahap === 2)
                                                <i class="bi bi-check2-circle me-1"></i> Selesai Evaluasi
                                            @elseif($suket->status_tahap === 3)
                                                <i class="bi bi-send-check me-1"></i> Ajukan ke Ka. Balai
                                            @elseif($suket->status_tahap === 4)
                                                <i class="bi bi-pen me-1"></i> Tanda Tangani
                                            @elseif($suket->status_tahap === 5)
                                                <i class="bi bi-box-seam me-1"></i> Kirim Pelanggan
                                            @endif
                                        </button>
                                    @elseif($suket->status_tahap == 6)
                                        <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">
                                            <i class="bi bi-check-all me-1"></i>Tuntas
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill" title="Kewenangan: {{ implode(', ', $stgInfo['roles']) }}">
                                            Menunggu {{ implode('/', array_map('strtoupper', $stgInfo['roles'])) }}
                                        </span>
                                    @endif

                                    {{-- Tombol Aksi Tambahan: Upload Berkas --}}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-circle"
                                        style="width: 32px; height: 32px; padding: 0;"
                                        title="Upload Dokumen Pendukung"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalUpload{{ $suket->id }}"
                                    >
                                        <i class="bi bi-upload"></i>
                                    </button>
                                </div>

                                {{-- MODAL ADVANCE STAGE --}}
                                <div class="modal fade text-start" id="modalAdvance{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <form action="{{ route('suket.advance', $suket->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        Proses Tahap {{ $suket->status_tahap }}: {{ $stgInfo['label'] }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                                                        <strong>Nomor Order:</strong> {{ $suket->nomor_order }}<br>
                                                        <strong>Perusahaan:</strong> {{ $suket->perusahaan_nama }}<br>
                                                        <strong>Tahap Berikutnya:</strong>
                                                        <span class="fw-bold text-primary">
                                                            {{ $stages[$suket->status_tahap + 1]['label'] ?? 'Selesai' }}
                                                        </span>
                                                    </div>

                                                    <input type="hidden" name="action" value="next">

                                                    @if($suket->status_tahap === 5)
                                                        {{-- Input pengiriman ke pelanggan --}}
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Nomor Resi / Bukti Kirim</label>
                                                            <input type="text" name="resi_pengiriman" class="form-control" placeholder="Contoh: JNE12345678 / Serah Langsung">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Metode Pengiriman</label>
                                                            <select name="metode_pengiriman" class="form-select">
                                                                <option value="Kurir Ekspedisi">Kurir Ekspedisi (JNE/TIKI/POS)</option>
                                                                <option value="Diserahkan Langsung">Diserahkan Langsung di Balai</option>
                                                                <option value="Email Digital">Kirim Email / Salinan Digital</option>
                                                            </select>
                                                        </div>
                                                    @endif

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Catatan Tahap (Opsional)</label>
                                                        <textarea name="catatan" rows="3" class="form-control" placeholder="Masukkan catatan atau instruksi jika ada..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4" style="background-color: #15406A; border-color: #15406A;">
                                                        Konfirmasi & Teruskan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- MODAL UPLOAD DOKUMEN --}}
                                <div class="modal fade text-start" id="modalUpload{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <form action="{{ route('suket.upload-doc', $suket->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">Upload Dokumen Suket</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Jenis Dokumen</label>
                                                        <select name="type" class="form-select" required>
                                                            <option value="draft">Draf Laporan / Surat Keterangan</option>
                                                            <option value="signed">Surat Keterangan Bertanda Tangan (TTD)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Pilih Berkas (PDF/DOCX, maks 10MB)</label>
                                                        <input type="file" name="document_file" class="form-control" accept=".pdf,.doc,.docx" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4" style="background-color: #15406A; border-color: #15406A;">
                                                        Unggah Berkas
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <strong>Belum ada permohonan suket yang aktif.</strong><br>
                                <span class="small">Gunakan form di atas dengan memasukkan Nomor Order untuk memulai pengajuan suket baru.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sukets->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex justify-content-end">
                {{ $sukets->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
