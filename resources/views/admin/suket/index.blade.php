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
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill fs-5 text-warning"></i>
                <div>{{ session('warning') }}</div>
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
                    <i class="bi bi-shield-check me-1"></i>Penerbitan Suket K3
                </span>
                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">
                    Role Anda: <strong class="text-dark">{{ ucfirst($currentRole) }}</strong>
                </span>
            </div>
            <h3 class="fw-bold text-dark mb-1">Penerbitan Surat Keterangan (Suket) K3 Lingkungan Kerja</h3>
            <p class="text-muted mb-0 small">
                Pengelolaan alur 6 tahap sesuai standar <strong>Permenaker No. 5 Tahun 2018</strong>: Permohonan, Evaluasi LHU & Foto, Draf Suket, QC & Pengesahan, Penomoran, hingga Penyerahan ke Pelanggan.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('suket.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Segarkan Data
            </a>
        </div>
    </div>

    {{-- SECTION 1: Form Pengajuan Suket Baru (Tahap 1: User / Pemohon / Admin) --}}
    @if(in_array($currentRole, ['user', 'admin', 'superadmin', 'pcu', 'penguji_k3']))
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
                    <i class="bi bi-file-earmark-plus-fill fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1">Tahap 1: Form Permohonan Suket K3 Lingkungan Kerja</h5>
                    <p class="text-muted small mb-0">
                        Pilih faktor pengujian K3 yang dimohonkan, sumber dokumen LHU (otomatis dari nomor order atau upload manual), serta lampirkan foto pengujian dan denah lokasi.
                    </p>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('suket.store-order') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    {{-- 1. Pilihan Faktor K3 Lingkungan Kerja --}}
                    <div class="col-12">
                        <label class="form-label small fw-bold text-dark mb-1">
                            1. Ruang Lingkup Faktor K3 yang Diuji <span class="text-danger">*</span>
                            <span class="text-muted fw-normal">(Pilih minimal 1 faktor sesuai pengujian di lapangan)</span>
                        </label>
                        <div class="row g-2 mt-1">
                            @foreach($faktorOptions as $fKey => $fDesc)
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            name="faktor_k3[]" 
                                            value="{{ $fKey }}" 
                                            id="f_{{ $fKey }}"
                                            @checked(is_array(old('faktor_k3')) && in_array($fKey, old('faktor_k3')))
                                        >
                                        <label class="form-check-label small fw-semibold text-dark" for="f_{{ $fKey }}">
                                            {{ $fDesc }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('faktor_k3')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- 2. Sumber Dokumen LHU --}}
                    <div class="col-12 mt-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            2. Sumber Dokumen Laporan Hasil Uji (LHU) <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="lhu_source" 
                                    id="source_auto" 
                                    value="auto" 
                                    checked
                                    onchange="toggleLhuSource(this.value)"
                                >
                                <label class="form-check-label small fw-semibold text-dark" for="source_auto">
                                    Tarik Otomatis dari Nomor Order Balai K3 (Sudah pernah uji & terbit LHU)
                                </label>
                            </div>
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="radio" 
                                    name="lhu_source" 
                                    id="source_manual" 
                                    value="manual"
                                    onchange="toggleLhuSource(this.value)"
                                >
                                <label class="form-check-label small fw-semibold text-dark" for="source_manual">
                                    Upload Manual File Dokumen LHU (PDF)
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Input Nomor Order / Kode Permohonan --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary mb-1">
                            Nomor Order / Kode Permohonan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted">
                                <i class="bi bi-hash"></i>
                            </span>
                            <input
                                type="text"
                                name="nomor_order"
                                id="nomor_order_input"
                                list="orderList"
                                class="form-control border-start-0 @error('nomor_order') is-invalid @enderror"
                                placeholder="Pilih atau ketik Nomor Order, contoh: PMH-20260401-0001"
                                value="{{ old('nomor_order', request('nomor_order')) }}"
                                required
                                autocomplete="off"
                            >
                            <datalist id="orderList">
                                @foreach($availableOrders as $ord)
                                    <option value="{{ $ord['kode'] }}">
                                        {{ $ord['perusahaan'] }} ({{ $ord['lokasi'] }}) {{ $ord['has_lhu'] ? '[LHU TTD Siap]' : '' }}
                                    </option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-text small text-muted">
                            Pilih kode pesanan di atas. Jika LHU sudah diterbitkan di alur pengujian, file akan ditarik otomatis.
                        </div>
                        @error('nomor_order')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Upload Manual File LHU (Kondisional) --}}
                    <div class="col-md-6" id="wrap_manual_lhu" style="display: none;">
                        <label class="form-label small fw-semibold text-secondary mb-1">
                            Upload File Dokumen LHU (PDF, maks 20MB) <span class="text-danger">*</span>
                        </label>
                        <input type="file" name="lhu_file" id="lhu_file_input" class="form-control" accept=".pdf">
                        <div class="form-text small text-muted">Unggah dokumen Laporan Hasil Uji resmi.</div>
                    </div>

                    {{-- Upload Foto Pengujian Lapangan --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary mb-1">
                            Foto Pengujian Lapangan (Opsional)
                        </label>
                        <input type="file" name="foto_pengujian" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text small text-muted">Foto pelaksanaan pengukuran titik uji di tempat kerja.</div>
                    </div>

                    {{-- Upload Denah Lokasi / Titik Uji --}}
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary mb-1">
                            Denah Lokasi / Titik Uji (Opsional)
                        </label>
                        <input type="file" name="denah_lokasi" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text small text-muted">Denah tata letak area kerja dan penempatan titik ukur.</div>
                    </div>

                    {{-- Catatan Pengajuan --}}
                    <div class="col-12">
                        <label class="form-label small fw-semibold text-secondary mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" rows="2" class="form-control" placeholder="Contoh: Pengujian rutin tahunan K3 lingkungan kerja unit produksi...">{{ old('catatan') }}</textarea>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="col-12 text-end mt-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm" style="background-color: #15406A; border-color: #15406A;">
                            <i class="bi bi-send me-1"></i> Ajukan Permohonan Suket K3
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- SECTION 2: 6 Tahapan Status Pipeline Stepper --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Alur Penerbitan Suket K3 Lingkungan Kerja (6 Tahap)
            </h6>
            <div class="small text-muted">
                Aktif Berjalan: <span class="badge bg-primary rounded-pill">{{ $totalActive }}</span> |
                Selesai / Terbit: <span class="badge bg-success rounded-pill">{{ $totalDone }}</span>
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
                <h6 class="fw-bold text-dark mb-0">Daftar Berkas Permohonan Suket K3</h6>
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
                <div class="input-group input-group-sm" style="width: 280px;">
                    <input type="text" name="search" class="form-control rounded-start-3" placeholder="Cari No. Order, Surat, PT..." value="{{ $search }}">
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
                        <th style="width: 40px;" class="ps-4">No</th>
                        <th>Nomor Order & Perusahaan</th>
                        <th>Faktor K3 Diuji</th>
                        <th>Status Alur & QC</th>
                        <th>Dokumen Terlampir</th>
                        <th>Catatan Evaluasi / Surat</th>
                        <th class="text-end pe-4" style="width: 250px;">Aksi</th>
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
                            $canQc = $suket->canRoleProcessQc($currentRole);
                            $fList = is_array($suket->faktor_k3) ? $suket->faktor_k3 : [];
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $sukets->firstItem() + $idx }}</td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $suket->nomor_order }}</div>
                                <div class="fw-semibold text-primary">{{ $suket->perusahaan_nama ?: '-' }}</div>
                                <div class="text-muted" style="font-size: 11px;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $suket->lokasi ?: '-' }}
                                </div>
                                <div class="text-muted" style="font-size: 10px;">
                                    Pemohon: {{ $suket->user?->name ?? ($suket->creator?->name ?? '-') }} | {{ $suket->created_at->format('d/m/Y') }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1" style="max-width: 220px;">
                                    @forelse($fList as $fItem)
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded" style="font-size: 10px;">
                                            {{ ucfirst($fItem) }}
                                        </span>
                                    @empty
                                        <span class="text-muted small">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-{{ $stgInfo['badge'] }} px-2 py-1 rounded-pill">
                                        Tahap {{ $suket->status_tahap }}: {{ $stgInfo['label'] }}
                                    </span>
                                </div>
                                {{-- Visual Progress Bar (1 to 6) --}}
                                <div class="progress mb-1" style="height: 6px; width: 130px; background-color: #e9ecef;">
                                    <div
                                        class="progress-bar bg-{{ $stgInfo['badge'] }}"
                                        role="progressbar"
                                        style="width: {{ ($suket->status_tahap / 6) * 100 }}%"
                                        aria-valuenow="{{ $suket->status_tahap }}"
                                        aria-valuemin="1"
                                        aria-valuemax="6">
                                    </div>
                                </div>
                                {{-- Status QC --}}
                                <div>
                                    @if($suket->qc_status === 'approved')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-1" style="font-size: 10px;">
                                            <i class="bi bi-check-circle me-1"></i>QC Approved
                                        </span>
                                    @elseif($suket->qc_status === 'revision')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1" style="font-size: 10px;">
                                            <i class="bi bi-exclamation-triangle me-1"></i>QC Revisi
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-1" style="font-size: 10px;">
                                            QC Pending
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1" style="font-size: 11px;">
                                    {{-- LHU --}}
                                    @if($suket->lhu_file_path)
                                        <a href="{{ route('suket.download-doc', [$suket->id, 'lhu']) }}" class="text-decoration-none text-primary" target="_blank">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Dokumen LHU ({{ $suket->lhu_source === 'auto' ? 'Auto' : 'Manual' }})
                                        </a>
                                    @else
                                        <span class="text-muted">- LHU belum terlampir -</span>
                                    @endif

                                    {{-- Foto & Denah --}}
                                    <div class="d-flex gap-2">
                                        @if($suket->foto_pengujian_path)
                                            <a href="{{ route('suket.download-doc', [$suket->id, 'foto']) }}" class="text-decoration-none text-secondary">
                                                <i class="bi bi-image text-info me-1"></i>Foto
                                            </a>
                                        @endif
                                        @if($suket->denah_lokasi_path)
                                            <a href="{{ route('suket.download-doc', [$suket->id, 'denah']) }}" class="text-decoration-none text-secondary">
                                                <i class="bi bi-map text-warning me-1"></i>Denah
                                            </a>
                                        @endif
                                    </div>

                                    {{-- Draft & Signed Suket --}}
                                    @if($suket->draft_file_path)
                                        <a href="{{ route('suket.download-doc', [$suket->id, 'draft']) }}" class="text-decoration-none text-primary fw-semibold">
                                            <i class="bi bi-file-earmark-word text-primary me-1"></i>Draf Suket Permenaker
                                        </a>
                                    @endif
                                    @if($suket->signed_file_path)
                                        <a href="{{ route('suket.download-doc', [$suket->id, 'signed']) }}" class="text-decoration-none text-success fw-semibold">
                                            <i class="bi bi-patch-check-fill text-success me-1"></i>Suket Resmi TTD/TTE
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($suket->nomor_surat)
                                    <div class="fw-bold text-success mb-1" style="font-size: 11px;">
                                        <i class="bi bi-award me-1"></i>No: {{ $suket->nomor_surat }}
                                    </div>
                                @endif
                                <div class="text-muted" style="max-width: 220px; font-size: 11px;">
                                    @if($suket->catatan_evaluasi)
                                        <strong>Evaluasi:</strong> {{ Str::limit($suket->catatan_evaluasi, 80) }}<br>
                                    @endif
                                    @if($suket->qc_note)
                                        <strong>QC:</strong> {{ Str::limit($suket->qc_note, 80) }}
                                    @endif
                                    @if(!$suket->catatan_evaluasi && !$suket->qc_note)
                                        {{ Str::limit($suket->catatan, 60) ?: '-' }}
                                    @endif
                                </div>
                                @if($suket->resi_pengiriman)
                                    <div class="mt-1 text-success fw-semibold" style="font-size: 11px;">
                                        <i class="bi bi-truck me-1"></i>Resi: {{ $suket->resi_pengiriman }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">

                                    {{-- Tombol Generate Draf Word Permenaker 05/2018 (Tahap 3 ke atas) --}}
                                    @if($suket->status_tahap >= 3 && in_array($currentRole, ['pcu', 'penguji_k3', 'admin', 'superadmin', 'qc']))
                                        <a 
                                            href="{{ route('suket.generate-draft', $suket->id) }}" 
                                            class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1"
                                            title="Auto-Generate Draft Word Standar Permenaker 05/2018"
                                            style="font-size: 11px;"
                                        >
                                            <i class="bi bi-file-earmark-word me-1"></i>Generate Draf
                                        </a>
                                    @endif

                                    {{-- Tombol Gerbang Review QC (sebelum Tahap 4) --}}
                                    @if($suket->status_tahap === 3 && $canQc)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-1 fw-semibold"
                                            style="font-size: 11px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalQc{{ $suket->id }}"
                                        >
                                            <i class="bi bi-shield-check me-1"></i>Review QC
                                        </button>
                                    @endif

                                    {{-- Tombol Lanjut Tahap jika role berwenang --}}
                                    @if($canProcess && $suket->status_tahap < 6)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary rounded-pill px-3 py-1"
                                            style="background-color: #15406A; border-color: #15406A; font-size: 11px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAdvance{{ $suket->id }}"
                                        >
                                            @if($suket->status_tahap === 1)
                                                <i class="bi bi-arrow-right me-1"></i> Ke Evaluasi
                                            @elseif($suket->status_tahap === 2)
                                                <i class="bi bi-check2-circle me-1"></i> Selesai Evaluasi
                                            @elseif($suket->status_tahap === 3)
                                                <i class="bi bi-send-check me-1"></i> Ajukan ke TTD
                                            @elseif($suket->status_tahap === 4)
                                                <i class="bi bi-pen me-1"></i> Pengesahan TTD
                                            @elseif($suket->status_tahap === 5)
                                                <i class="bi bi-award me-1"></i> Terbitkan Suket
                                            @endif
                                        </button>
                                    @elseif($suket->status_tahap === 6)
                                        @if(in_array($currentRole, ['admin', 'superadmin']))
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success rounded-pill px-2 py-1"
                                                style="font-size: 11px;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalAdvance{{ $suket->id }}"
                                            >
                                                <i class="bi bi-truck me-1"></i>Update Resi
                                            </button>
                                        @else
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 11px;">
                                                <i class="bi bi-check-all me-1"></i>Tuntas
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill" style="font-size: 10px;" title="Kewenangan: {{ implode(', ', $stgInfo['roles']) }}">
                                            Menunggu {{ implode('/', array_map('strtoupper', $stgInfo['roles'])) }}
                                        </span>
                                    @endif

                                    {{-- Tombol Upload Lampiran Tambahan --}}
                                    @if(in_array($currentRole, ['admin', 'superadmin', 'pcu', 'penguji_k3', 'user']))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-circle"
                                            style="width: 28px; height: 28px; padding: 0;"
                                            title="Upload Berkas Lampiran Tambahan"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalUpload{{ $suket->id }}"
                                        >
                                            <i class="bi bi-upload" style="font-size: 11px;"></i>
                                        </button>
                                    @endif
                                </div>

                                {{-- MODAL ADVANCE STAGE --}}
                                <div class="modal fade text-start" id="modalAdvance{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <form action="{{ route('suket.advance', $suket->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        Tahap {{ $suket->status_tahap }}: {{ $stgInfo['label'] }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                                                        <div class="row">
                                                            <div class="col-sm-6">
                                                                <strong>Nomor Order:</strong> {{ $suket->nomor_order }}<br>
                                                                <strong>Perusahaan:</strong> {{ $suket->perusahaan_nama }}<br>
                                                                <strong>Lokasi:</strong> {{ $suket->lokasi }}
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <strong>Tahap Berikutnya:</strong>
                                                                <span class="fw-bold text-primary">
                                                                    {{ $stages[$suket->status_tahap + 1]['label'] ?? 'Selesai / Kirim Pelanggan' }}
                                                                </span><br>
                                                                <strong>Status QC:</strong> 
                                                                <span class="badge {{ $suket->qc_status === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                                    {{ strtoupper($suket->qc_status) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <input type="hidden" name="action" value="next">

                                                    {{-- FORM KHUSUS TAHAP 2: EVALUASI DOKUMEN --}}
                                                    @if($suket->status_tahap === 2)
                                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                                            <h6 class="fw-bold text-dark mb-2">
                                                                <i class="bi bi-folder2-open text-primary me-2"></i>Dokumen Dasar Evaluasi Penguji K3:
                                                            </h6>
                                                            <div class="d-flex gap-3 mb-3">
                                                                @if($suket->lhu_file_path)
                                                                    <a href="{{ route('suket.download-doc', [$suket->id, 'lhu']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                        <i class="bi bi-file-earmark-pdf me-1"></i>Buka Dokumen LHU ({{ $suket->lhu_source === 'auto' ? 'Auto Order' : 'Manual' }})
                                                                    </a>
                                                                @else
                                                                    <span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>File LHU belum tersedia</span>
                                                                @endif

                                                                @if($suket->foto_pengujian_path)
                                                                    <a href="{{ route('suket.download-doc', [$suket->id, 'foto']) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                                        <i class="bi bi-image me-1"></i>Lihat Foto Pengujian
                                                                    </a>
                                                                @endif

                                                                @if($suket->denah_lokasi_path)
                                                                    <a href="{{ route('suket.download-doc', [$suket->id, 'denah']) }}" target="_blank" class="btn btn-sm btn-outline-warning">
                                                                        <i class="bi bi-map me-1"></i>Lihat Denah Lokasi
                                                                    </a>
                                                                @endif
                                                            </div>

                                                            <label class="form-label small fw-semibold text-dark">Hasil Telaah / Evaluasi Teknis K3 <span class="text-danger">*</span></label>
                                                            <textarea 
                                                                name="catatan" 
                                                                rows="3" 
                                                                class="form-control" 
                                                                placeholder="Tuliskan kesimpulan evaluasi hasil uji berdasarkan Permenaker No. 5/2018 (misal: Seluruh parameter NAB lingkungan kerja memenuhi syarat, tidak ditemukan anomali paparan...)"
                                                                required
                                                            >{{ $suket->catatan_evaluasi }}</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 3: PENYUSUNAN DRAF SUKET --}}
                                                    @if($suket->status_tahap === 3)
                                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 class="fw-bold text-dark mb-0">
                                                                    <i class="bi bi-file-earmark-word text-primary me-2"></i>Draf Surat Keterangan Standar Permenaker 05/2018
                                                                </h6>
                                                                <a href="{{ route('suket.generate-draft', $suket->id) }}" class="btn btn-sm btn-primary rounded-pill">
                                                                    <i class="bi bi-download me-1"></i>Download Template Draf Word
                                                                </a>
                                                            </div>
                                                            <p class="small text-muted mb-0">
                                                                Sistem telah men-generate draf surat otomatis memuat data perusahaan, nomor order, dan tabel faktor K3 yang dievaluasi.
                                                                @if($suket->qc_status !== 'approved')
                                                                    <br><span class="text-danger fw-bold"><i class="bi bi-info-circle me-1"></i>Perhatian: Sebelum diajukan ke TTD Kepala Balai, pastikan Tim QC telah menyetujui (Approved) draf ini.</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Penguji K3 (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Catatan pengantar draf suket...">{{ $suket->catatan }}</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 4: PENANDATANGANAN KEPALA BALAI --}}
                                                    @if($suket->status_tahap === 4)
                                                        <div class="alert alert-warning border-0 small mb-3">
                                                            <i class="bi bi-pen-fill me-1"></i>
                                                            Tahap ini adalah pengesahan oleh Kepala Balai. Admin atau Kepala Balai dapat memastikan tanda tangan elektronik (TTE) atau mengunggah berkas scan tanda tangan basah.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Pengesahan (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Pengesahan tanda tangan elektronik Kepala Balai...">Dokumen Surat Keterangan K3 telah ditandatangani dan disahkan.</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 5: PENERBITAN SUKET (INPUT NOMOR SURAT RESMI) --}}
                                                    @if($suket->status_tahap === 5)
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-dark">
                                                                Nomor Surat Keterangan Resmi <span class="text-danger">*</span>
                                                            </label>
                                                            <input 
                                                                type="text" 
                                                                name="nomor_surat" 
                                                                class="form-control form-control-lg" 
                                                                placeholder="Contoh: 566/SK-LK/BK3-SBY/IX/2026"
                                                                value="{{ $suket->nomor_surat ?: ('566/SK-LK/BK3-SBY/' . \Carbon\Carbon::now()->format('m/Y')) }}"
                                                                required
                                                            >
                                                            <div class="form-text small text-muted">
                                                                Nomor surat registrasi resmi Balai K3 Surabaya yang akan tertera pada dokumen terbit.
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Penerbitan (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Surat keterangan resmi telah diterbitkan...">Surat Keterangan K3 resmi diterbitkan dan siap diserahkan ke pelanggan.</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 6: KIRIM KE PELANGGAN --}}
                                                    @if($suket->status_tahap === 6)
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Nomor Resi / Bukti Kirim Fisik (Opsional)</label>
                                                            <input type="text" name="resi_pengiriman" class="form-control" placeholder="Contoh: JNE-99887766 / Serah Terima Langsung" value="{{ $suket->resi_pengiriman }}">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Metode Pengiriman</label>
                                                            <select name="metode_pengiriman" class="form-select">
                                                                <option value="Kurir Ekspedisi" @selected($suket->metode_pengiriman === 'Kurir Ekspedisi')>Kurir Ekspedisi (JNE / TIKI / POS)</option>
                                                                <option value="Diserahkan Langsung" @selected($suket->metode_pengiriman === 'Diserahkan Langsung')>Diserahkan Langsung di Loket Balai</option>
                                                                <option value="Portal Digital" @selected($suket->metode_pengiriman === 'Portal Digital')>Unduhan Mandiri Portal Pelanggan</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Penyerahan (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Catatan konfirmasi penyerahan...">Berkas suket resmi telah diserahkan kepada pemohon.</textarea>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4" style="background-color: #15406A; border-color: #15406A;">
                                                        Konfirmasi & Proses Lanjut
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- MODAL GERBANG QC (KHUSUS ROLE QC / SUPERADMIN) --}}
                                @if($canQc)
                                <div class="modal fade text-start" id="modalQc{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <form action="{{ route('suket.qc-review', $suket->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        <i class="bi bi-shield-check text-warning me-2"></i>Gerbang Quality Control (QC)
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="small text-muted mb-3">
                                                        Periksa kelayakan draf surat keterangan nomor order <strong>{{ $suket->nomor_order }}</strong> sebelum diajukan ke Kepala Balai.
                                                    </p>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-dark">Keputusan Review QC <span class="text-danger">*</span></label>
                                                        <div class="d-flex gap-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="action" id="qc_app_{{ $suket->id }}" value="approve" checked>
                                                                <label class="form-check-label text-success fw-bold small" for="qc_app_{{ $suket->id }}">
                                                                    <i class="bi bi-check-circle me-1"></i>Setujui (Lanjut Tahap 4)
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="action" id="qc_rej_{{ $suket->id }}" value="revision">
                                                                <label class="form-check-label text-danger fw-bold small" for="qc_rej_{{ $suket->id }}">
                                                                    <i class="bi bi-arrow-return-left me-1"></i>Kembalikan / Revisi
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Catatan / Arahan QC</label>
                                                        <textarea name="catatan" rows="3" class="form-control" placeholder="Tuliskan catatan verifikasi QC atau instruksi perbaikan draf...">{{ $suket->qc_note }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-semibold">
                                                        Simpan Keputusan QC
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- MODAL UPLOAD LAMPIRAN BERKAS TAMBAHAN --}}
                                <div class="modal fade text-start" id="modalUpload{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <form action="{{ route('suket.upload-doc', $suket->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">Upload Dokumen Lampiran</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Jenis Dokumen yang Diunggah</label>
                                                        <select name="type" class="form-select" required>
                                                            <option value="signed">Surat Keterangan Bertanda Tangan (TTD/TTE)</option>
                                                            <option value="draft">Draf Suket Hasil Revisi (DOC/PDF)</option>
                                                            <option value="lhu">Dokumen Laporan Hasil Uji (LHU)</option>
                                                            <option value="foto">Foto Pengujian Lapangan</option>
                                                            <option value="denah">Denah Lokasi / Titik Ukur</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Pilih Berkas (Maksimal 20MB)</label>
                                                        <input type="file" name="document_file" class="form-control" required>
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
                                <span class="small">Gunakan formulir di atas untuk mengajukan permohonan Suket K3 Lingkungan Kerja baru.</span>
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

<script>
function toggleLhuSource(val) {
    const wrapManual = document.getElementById('wrap_manual_lhu');
    const lhuInput = document.getElementById('lhu_file_input');
    if (val === 'manual') {
        wrapManual.style.display = 'block';
        lhuInput.required = true;
    } else {
        wrapManual.style.display = 'none';
        lhuInput.required = false;
    }
}
</script>
@endsection
