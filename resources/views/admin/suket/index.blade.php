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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5 text-danger mt-1"></i>
                <div>
                    <strong>Perhatian - Terjadi Kesalahan Input:</strong>
                    <ul class="mb-0 ps-3 mt-1 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
    .btn-outline-primary {
        color: #15406A !important;
        border-color: #15406A !important;
    }
    .btn-outline-primary:hover,
    .btn-outline-primary:focus,
    .btn-outline-primary.active {
        background-color: #15406A !important;
        color: #fff !important;
        border-color: #15406A !important;
    }
    .text-navy {
        color: #15406A !important;
    }
    .bg-navy {
        background-color: #15406A !important;
        color: #ffffff !important;
    }
    .stage-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #ffffff;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none !important;
        display: block;
    }
    .stage-card:hover {
        transform: translateY(-2px);
        border-color: #15406A;
        box-shadow: 0 8px 24px rgba(21, 64, 106, 0.12) !important;
    }
    .stage-card.is-active {
        background: #15406A !important;
        border-color: #15406A !important;
        box-shadow: 0 10px 25px rgba(21, 64, 106, 0.25) !important;
    }
    .stage-card.is-active .stage-title {
        color: #ffffff !important;
    }
    .stage-card.is-active .stage-count {
        color: #ffffff !important;
    }
    .stage-card.is-active .stage-icon {
        color: #ffffff !important;
    }
    .stage-card.is-active .stage-badge {
        background: rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
    }
    .stage-card.is-active .stage-sub {
        color: rgba(255, 255, 255, 0.8) !important;
    }
    .workflow-search-wrap .form-control:focus {
        border-color: #a7bed9;
        box-shadow: 0 0 0 0.2rem rgba(21, 64, 106, 0.15);
    }
</style>

@include('admin.partials.workflow_header', [
    'title' => 'Alur Kerja - Penerbitan Suket K3',
    'subtitle' => 'Pengelolaan dan pemrosesan internal Surat Keterangan K3 Lingkungan Kerja (Permenaker No. 5 Tahun 2018).',
    'total' => $totalActive + $totalDone,
])

    {{-- Page Header / Breadcrumb Bar --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                    <i class="bi bi-shield-check me-1"></i>Penerbitan Suket K3
                </span>
                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill">
                    Role Anda: <strong class="text-dark">{{ ucfirst(str_replace('_', ' ', $currentRole)) }}</strong>
                </span>
            </div>
            <h4 class="fw-bold text-dark mb-1">Penerbitan Surat Keterangan (Suket) K3 Lingkungan Kerja</h4>
            <p class="text-muted mb-0 small">
                Pemrosesan alur penerbitan resmi mulai dari <strong>Evaluasi Dokumen</strong> hingga <strong>Penyerahan Suket</strong> ke pemohon.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('suket.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i> Segarkan Data
            </a>
        </div>
    </div>

    {{-- SECTION: Tab / Tahapan Navigation Stepper (Tahap 2 s/d 6 & QC) --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-diagram-3-fill text-primary me-2"></i>Tahapan Pemrosesan Suket K3
            </h6>
            <div class="small text-muted">
                Aktif Berjalan: <span class="badge bg-primary rounded-pill">{{ $totalActive }}</span> &bull;
                Tuntas Diserahkan: <span class="badge bg-success rounded-pill">{{ $totalDone }}</span>
            </div>
        </div>

        <div class="row g-2">
            {{-- Tab "Semua Permohonan" --}}
            @php $isAll = empty($activeStage); @endphp
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('suket.index') }}" class="stage-card p-3 h-100 shadow-sm {{ $isAll ? 'is-active' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stage-badge badge bg-light text-secondary border px-2 py-1 rounded-pill small">
                            Semua
                        </span>
                        <span class="stage-count fw-bold fs-5 text-dark">{{ $totalActive + $totalDone }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-grid-fill stage-icon text-primary fs-5"></i>
                        <div class="stage-title fw-bold text-dark small lh-sm">Semua Permohonan</div>
                    </div>
                    <div class="stage-sub small mt-auto pt-2 text-muted" style="font-size: 11px;">
                        Daftar Keseluruhan
                    </div>
                </a>
            </div>

            {{-- Tahap 2: Evaluasi Dokumen --}}
            @php
                $isT2 = $activeStage == '2';
                $canT2 = in_array($currentRole, ['pcu', 'penguji_k3', 'admin', 'superadmin'], true);
            @endphp
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('suket.index', ['stage' => $isT2 ? null : 2]) }}" class="stage-card p-3 h-100 shadow-sm {{ $isT2 ? 'is-active' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stage-badge badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                            Tahap 2
                        </span>
                        <span class="stage-count fw-bold fs-5 text-dark">{{ $stageCounts[2] ?? 0 }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-file-earmark-check stage-icon text-primary fs-5"></i>
                        <div class="stage-title fw-bold text-dark small lh-sm">Evaluasi Dokumen</div>
                    </div>
                    <div class="stage-sub small mt-auto pt-2 text-muted" style="font-size: 11px;">
                        @if($canT2)
                            <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle px-1 rounded">Kewenangan Anda</span>
                        @else
                            <span>PENGUJI / ADMIN</span>
                        @endif
                    </div>
                </a>
            </div>

            {{-- Tahap 3: Penyusunan Suket --}}
            @php
                $isT3 = $activeStage == '3';
                $canT3 = in_array($currentRole, ['pcu', 'penguji_k3', 'superadmin'], true);
            @endphp
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('suket.index', ['stage' => $isT3 ? null : 3]) }}" class="stage-card p-3 h-100 shadow-sm {{ $isT3 ? 'is-active' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stage-badge badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill small">
                            Tahap 3
                        </span>
                        <span class="stage-count fw-bold fs-5 text-dark">{{ $stageCounts[3] ?? 0 }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-file-earmark-word stage-icon text-info fs-5"></i>
                        <div class="stage-title fw-bold text-dark small lh-sm">Penyusunan Suket</div>
                    </div>
                    <div class="stage-sub small mt-auto pt-2 text-muted" style="font-size: 11px;">
                        @if($canT3)
                            <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle px-1 rounded">Kewenangan Anda</span>
                        @else
                            <span>PENGUJI K3</span>
                        @endif
                    </div>
                </a>
            </div>

            {{-- Review QC Suket --}}
            @php
                $isQc = $activeStage == 'qc';
                $canQcRole = in_array($currentRole, ['qc', 'superadmin'], true);
                $qcPendingCount = \App\Models\SuketK3::where('status_tahap', 3)->where('qc_status', 'pending')->count();
            @endphp
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('suket.index', ['stage' => $isQc ? null : 'qc']) }}" class="stage-card p-3 h-100 shadow-sm {{ $isQc ? 'is-active' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stage-badge badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill small">
                            Gerbang QC
                        </span>
                        <span class="stage-count fw-bold fs-5 text-dark">{{ $qcPendingCount }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-shield-check stage-icon text-warning fs-5"></i>
                        <div class="stage-title fw-bold text-dark small lh-sm">Review QC Suket</div>
                    </div>
                    <div class="stage-sub small mt-auto pt-2 text-muted" style="font-size: 11px;">
                        @if($canQcRole)
                            <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle px-1 rounded">Kewenangan Anda</span>
                        @else
                            <span>TIM QC</span>
                        @endif
                    </div>
                </a>
            </div>

            {{-- Tahap 4: Penandatanganan Suket --}}
            @php
                $isT4 = $activeStage == '4';
                $canT4 = in_array($currentRole, ['mp', 'kepala_balai', 'admin', 'superadmin'], true);
            @endphp
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('suket.index', ['stage' => $isT4 ? null : 4]) }}" class="stage-card p-3 h-100 shadow-sm {{ $isT4 ? 'is-active' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stage-badge badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill small">
                            Tahap 4
                        </span>
                        <span class="stage-count fw-bold fs-5 text-dark">{{ $stageCounts[4] ?? 0 }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-pen stage-icon text-danger fs-5"></i>
                        <div class="stage-title fw-bold text-dark small lh-sm">Penandatanganan Suket</div>
                    </div>
                    <div class="stage-sub small mt-auto pt-2 text-muted" style="font-size: 11px;">
                        @if($canT4)
                            <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle px-1 rounded">Kewenangan Anda</span>
                        @else
                            <span>KEPALA BALAI / ADMIN</span>
                        @endif
                    </div>
                </a>
            </div>

            {{-- Tahap 5: Penerbitan Suket --}}
            @php
                $isT5 = $activeStage == '5';
                $canT5 = in_array($currentRole, ['admin', 'superadmin'], true);
            @endphp
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('suket.index', ['stage' => $isT5 ? null : 5]) }}" class="stage-card p-3 h-100 shadow-sm {{ $isT5 ? 'is-active' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stage-badge badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill small">
                            Tahap 5
                        </span>
                        <span class="stage-count fw-bold fs-5 text-dark">{{ $stageCounts[5] ?? 0 }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-award stage-icon text-secondary fs-5"></i>
                        <div class="stage-title fw-bold text-dark small lh-sm">Penerbitan Suket</div>
                    </div>
                    <div class="stage-sub small mt-auto pt-2 text-muted" style="font-size: 11px;">
                        @if($canT5)
                            <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle px-1 rounded">Kewenangan Anda</span>
                        @else
                            <span>ADMINISTRATOR</span>
                        @endif
                    </div>
                </a>
            </div>

            {{-- Tahap 6: Penyerahan Suket --}}
            @php
                $isT6 = $activeStage == '6';
                $canT6 = in_array($currentRole, ['admin', 'superadmin'], true);
            @endphp
            <div class="col-6 col-md-4 col-xl">
                <a href="{{ route('suket.index', ['stage' => $isT6 ? null : 6]) }}" class="stage-card p-3 h-100 shadow-sm {{ $isT6 ? 'is-active' : '' }}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="stage-badge badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">
                            Tahap 6
                        </span>
                        <span class="stage-count fw-bold fs-5 text-dark">{{ $stageCounts[6] ?? 0 }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-send-check stage-icon text-success fs-5"></i>
                        <div class="stage-title fw-bold text-dark small lh-sm">Penyerahan Suket</div>
                    </div>
                    <div class="stage-sub small mt-auto pt-2 text-muted" style="font-size: 11px;">
                        @if($canT6)
                            <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle px-1 rounded">Kewenangan Anda</span>
                        @else
                            <span>ADMINISTRATOR</span>
                        @endif
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- SECTION: Filter Bar & Daftar Berkas --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body">
            <div class="workflow-search-wrap">
                <form action="{{ route('suket.index') }}" method="GET">
                    @if($activeStage)
                        <input type="hidden" name="stage" value="{{ $activeStage }}">
                    @endif
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-md-8 col-lg-9">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari berdasarkan No. Order, Nama Perusahaan, Lokasi, atau Nomor Surat..." value="{{ $search }}">
                            </div>
                        </div>
                        <div class="col-6 col-md-2 col-lg-1.5 d-flex gap-1">
                            <button type="submit" class="btn btn-primary w-100 rounded-3">
                                <i class="bi bi-filter me-1"></i>Filter
                            </button>
                        </div>
                        <div class="col-6 col-md-2 col-lg-1.5">
                            <a href="{{ route('suket.index', $activeStage ? ['stage' => $activeStage] : []) }}" class="btn btn-outline-secondary w-100 rounded-3">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            @if($activeStage)
                <div class="d-flex align-items-center gap-2 mt-3 pt-2 border-top">
                    <span class="small text-muted">Tahapan Aktif:</span>
                    <span class="badge bg-navy px-3 py-1 rounded-pill small">
                        {{ $activeStage === 'qc' ? 'Gerbang Review QC Suket' : ($stages[$activeStage]['label'] ?? "Tahap $activeStage") }}
                    </span>
                    <a href="{{ route('suket.index') }}" class="btn btn-link btn-sm text-secondary text-decoration-none p-0 ms-2 small">
                        <i class="bi bi-x-circle me-1"></i>Tampilkan Semua Permohonan
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- SECTION: Daftar Monitoring Berkas & Aksi --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold text-dark mb-0">Daftar Berkas Permohonan Suket K3</h6>
                <span class="badge bg-light text-secondary border rounded-pill">Total: {{ $sukets->total() }} Berkas</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-secondary small text-uppercase fw-semibold">
                    <tr>
                        <th style="width: 40px;" class="ps-4">No</th>
                        <th>Nomor Order & Perusahaan</th>
                        <th>Faktor K3 Diuji</th>
                        <th>Status Alur & QC</th>
                        <th>Dokumen Terlampir (Preview & Unduh)</th>
                        <th>Catatan / Nomor Surat</th>
                        <th class="text-end pe-4" style="min-width: 220px;">Aksi</th>
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
                                    Pemohon: {{ $suket->user?->name ?? ($suket->creator?->name ?? '-') }} | {{ $suket->created_at->format('d/m/Y H:i') }}
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
                                {{-- Visual Progress Bar (2 to 6) --}}
                                <div class="progress mb-1" style="height: 6px; width: 130px; background-color: #e9ecef;">
                                    <div
                                        class="progress-bar bg-{{ $stgInfo['badge'] }}"
                                        role="progressbar"
                                        style="width: {{ (($suket->status_tahap - 1) / 5) * 100 }}%"
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
                                            <i class="bi bi-exclamation-triangle me-1"></i>QC Perlu Revisi
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
                                    {{-- Dokumen LHU --}}
                                    @if($suket->lhu_file_path)
                                        <div class="d-flex align-items-center gap-1">
                                            <button 
                                                type="button" 
                                                class="btn btn-xs btn-outline-danger px-2 py-0 rounded"
                                                onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'lhu']) }}', 'Dokumen LHU: {{ $suket->nomor_order }}', 'pdf')"
                                                title="Preview Dokumen LHU"
                                            >
                                                <i class="bi bi-eye me-1"></i>LHU ({{ $suket->lhu_source === 'auto' ? 'Auto' : 'Manual' }})
                                            </button>
                                            <a href="{{ route('suket.download-doc', [$suket->id, 'lhu']) }}" class="text-secondary" title="Unduh File LHU" download>
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted">- LHU belum terlampir -</span>
                                    @endif

                                    {{-- Foto & Denah --}}
                                    <div class="d-flex gap-2">
                                        @if($suket->foto_pengujian_path)
                                            <button 
                                                type="button" 
                                                class="btn btn-xs btn-outline-info px-2 py-0 rounded text-decoration-none"
                                                onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'foto']) }}', 'Foto Pengujian: {{ $suket->nomor_order }}', 'image')"
                                                title="Preview Foto Pengujian"
                                            >
                                                <i class="bi bi-image me-1"></i>Foto
                                            </button>
                                        @endif
                                        @if($suket->denah_lokasi_path)
                                            <button 
                                                type="button" 
                                                class="btn btn-xs btn-outline-warning px-2 py-0 rounded text-decoration-none"
                                                onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'denah']) }}', 'Denah Lokasi: {{ $suket->nomor_order }}', 'image')"
                                                title="Preview Denah Lokasi"
                                            >
                                                <i class="bi bi-map me-1"></i>Denah
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Draf Suket Permenaker --}}
                                    @if($suket->draft_file_path || $suket->status_tahap >= 3)
                                        <div class="d-flex align-items-center gap-1">
                                            <button 
                                                type="button" 
                                                class="btn btn-xs btn-outline-primary px-2 py-0 rounded"
                                                onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'draft']) }}', 'Draf Suket Permenaker: {{ $suket->nomor_order }}', 'html')"
                                                title="Preview Dokumen Draf Suket"
                                            >
                                                <i class="bi bi-eye me-1"></i>Draf Suket
                                            </button>
                                            <a href="{{ route('suket.download-doc', [$suket->id, 'draft']) }}" class="text-primary" title="Unduh Draf Word" download>
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Suket Resmi TTD --}}
                                    @if($suket->signed_file_path)
                                        <div class="d-flex align-items-center gap-1">
                                            <button 
                                                type="button" 
                                                class="btn btn-xs btn-success px-2 py-0 rounded text-white"
                                                onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'signed']) }}', 'Surat Keterangan K3 Resmi: {{ $suket->nomor_order }}', 'pdf')"
                                                title="Preview Suket Resmi TTD"
                                            >
                                                <i class="bi bi-patch-check-fill me-1"></i>Suket Sah
                                            </button>
                                            <a href="{{ route('suket.download-doc', [$suket->id, 'signed']) }}" class="text-success" title="Unduh Suket Resmi" download>
                                                <i class="bi bi-download"></i>
                                            </a>
                                        </div>
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
                                @if($suket->sent_to_customer_at)
                                    <div class="mt-1 text-success fw-semibold" style="font-size: 10px;">
                                        <i class="bi bi-check2-all me-1"></i>Terkirim ke Web: {{ \Carbon\Carbon::parse($suket->sent_to_customer_at)->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end align-items-center gap-1 flex-wrap">

                                    {{-- TAHAP 3: Upload Lampiran Revisi Draf Word --}}
                                    @if($suket->status_tahap === 3 && in_array($currentRole, ['pcu', 'penguji_k3', 'admin', 'superadmin']))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1"
                                            style="font-size: 11px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalUploadDraft{{ $suket->id }}"
                                            title="Upload Berkas Revisi Draf Word / Lampiran"
                                        >
                                            <i class="bi bi-upload me-1"></i>Upload Revisi Draf
                                        </button>
                                    @endif

                                    {{-- GERBANG REVIEW QC (Tahap 3 & Role QC / Superadmin) --}}
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

                                    {{-- TOMBOL PROSES TAHAP (Advance Stage) --}}
                                    @if($canProcess && $suket->status_tahap < 6)
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary rounded-pill px-3 py-1"
                                            style="background-color: #15406A; border-color: #15406A; font-size: 11px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalAdvance{{ $suket->id }}"
                                        >
                                            @if($suket->status_tahap === 2)
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
                                        @if(!$suket->sent_to_customer_at && in_array($currentRole, ['admin', 'superadmin']))
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-success rounded-pill px-3 py-1"
                                                style="font-size: 11px;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalAdvance{{ $suket->id }}"
                                            >
                                                <i class="bi bi-send-check me-1"></i>Penyerahan Suket
                                            </button>
                                        @else
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill" style="font-size: 11px;">
                                                <i class="bi bi-check-all me-1"></i>Tuntas Diserahkan
                                            </span>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill" style="font-size: 10px;" title="Kewenangan: {{ implode(', ', $stgInfo['roles']) }}">
                                            Menunggu {{ implode('/', array_map('strtoupper', $stgInfo['roles'])) }}
                                        </span>
                                    @endif
                                </div>

                                {{-- MODAL ADVANCE STAGE --}}
                                <div class="modal fade text-start" id="modalAdvance{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <form action="{{ route('suket.advance', $suket->id) }}" method="POST" enctype="multipart/form-data">
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
                                                                    {{ $stages[$suket->status_tahap + 1]['label'] ?? 'Tuntas Diserahkan' }}
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
                                                            <div class="d-flex flex-wrap gap-2 mb-3">
                                                                @if($suket->lhu_file_path)
                                                                    <button 
                                                                        type="button" 
                                                                        class="btn btn-sm btn-outline-primary"
                                                                        onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'lhu']) }}', 'Dokumen LHU: {{ $suket->nomor_order }}', 'pdf')"
                                                                    >
                                                                        <i class="bi bi-eye me-1"></i>Preview Dokumen LHU ({{ $suket->lhu_source === 'auto' ? 'Auto Order' : 'Manual' }})
                                                                    </button>
                                                                @else
                                                                    <span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>File LHU belum tersedia</span>
                                                                @endif

                                                                @if($suket->foto_pengujian_path)
                                                                    <button 
                                                                        type="button" 
                                                                        class="btn btn-sm btn-outline-info"
                                                                        onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'foto']) }}', 'Foto Pengujian: {{ $suket->nomor_order }}', 'image')"
                                                                    >
                                                                        <i class="bi bi-image me-1"></i>Preview Foto Pengujian
                                                                    </button>
                                                                @endif

                                                                @if($suket->denah_lokasi_path)
                                                                    <button 
                                                                        type="button" 
                                                                        class="btn btn-sm btn-outline-warning"
                                                                        onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'denah']) }}', 'Denah Lokasi: {{ $suket->nomor_order }}', 'image')"
                                                                    >
                                                                        <i class="bi bi-map me-1"></i>Preview Denah Lokasi
                                                                    </button>
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
                                                                <div class="d-flex gap-2">
                                                                    <button 
                                                                        type="button" 
                                                                        class="btn btn-sm btn-outline-primary rounded-pill"
                                                                        onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'draft']) }}', 'Draf Suket Permenaker 05/2018: {{ $suket->nomor_order }}', 'html')"
                                                                    >
                                                                        <i class="bi bi-eye me-1"></i>Preview Draf
                                                                    </button>
                                                                    <a href="{{ route('suket.download-doc', [$suket->id, 'draft']) }}" class="btn btn-sm btn-primary rounded-pill" download>
                                                                        <i class="bi bi-download me-1"></i>Download Template Word
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <p class="small text-muted mb-0">
                                                                Draf telah disusun otomatis sesuai template resmi. Jika telah disetujui Tim QC, klik tombol di bawah untuk mengajukan ke penandatanganan Kepala Balai.
                                                                @if($suket->qc_status !== 'approved')
                                                                    <br><span class="text-danger fw-bold"><i class="bi bi-info-circle me-1"></i>Perhatian: Sebelum diajukan ke TTD Kepala Balai, draf ini harus disetujui (Approved) oleh Tim QC terlebih dahulu.</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Pengantar ke Kepala Balai (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Catatan pengantar draf suket...">{{ $suket->catatan }}</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 4: PENANDATANGANAN KEPALA BALAI --}}
                                                    @if($suket->status_tahap === 4)
                                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                                            <h6 class="fw-bold text-dark mb-2">
                                                                <i class="bi bi-pen-fill text-danger me-2"></i>Pengesahan & Upload Dokumen Bertanda Tangan (TTD / TTE)
                                                            </h6>
                                                            <p class="small text-muted mb-3">
                                                                Kepala Balai atau Admin dapat meninjau draf dokumen, lalu mengunggah berkas Surat Keterangan yang telah ditandatangani secara basah (scan) atau bersertifikat elektronik (TTE).
                                                            </p>
                                                            <div class="d-flex gap-2 mb-3">
                                                                <button 
                                                                    type="button" 
                                                                    class="btn btn-sm btn-outline-primary rounded-pill"
                                                                    onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'draft']) }}', 'Draf Suket: {{ $suket->nomor_order }}', 'html')"
                                                                >
                                                                    <i class="bi bi-eye me-1"></i>Preview Draf Dokumen Suket
                                                                </button>
                                                                <a href="{{ route('suket.download-doc', [$suket->id, 'draft']) }}" class="btn btn-sm btn-outline-secondary rounded-pill" download>
                                                                    <i class="bi bi-download me-1"></i>Unduh Berkas Draf Word
                                                                </a>
                                                            </div>

                                                            @if($suket->signed_file_path)
                                                                <div class="alert alert-success d-flex align-items-center justify-content-between p-2 mb-3 rounded-3">
                                                                    <div class="small">
                                                                        <i class="bi bi-check-circle-fill text-success me-1"></i> 
                                                                        Berkas TTD telah terunggah: <strong>{{ $suket->signed_file_name ?? 'Dokumen_TTD' }}</strong>
                                                                    </div>
                                                                    <button 
                                                                        type="button" 
                                                                        class="btn btn-xs btn-outline-success rounded-pill px-2 py-0"
                                                                        onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'signed']) }}', 'Dokumen TTD: {{ $suket->nomor_order }}', 'pdf')"
                                                                    >
                                                                        <i class="bi bi-eye me-1"></i>Lihat Berkas
                                                                    </button>
                                                                </div>
                                                            @endif

                                                            <div class="mb-2">
                                                                <label class="form-label small fw-bold text-dark">
                                                                    Upload Berkas Suket Bertanda Tangan (PDF / DOC / DOCX / Gambar, Maks 20MB) 
                                                                    @if(!$suket->signed_file_path)
                                                                        <span class="text-danger">*</span>
                                                                    @else
                                                                        <span class="badge bg-light text-muted border ms-1" style="font-size: 10px;">Opsional jika ingin mengganti</span>
                                                                    @endif
                                                                </label>
                                                                <input type="file" name="signed_document" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" {{ $suket->signed_file_path ? '' : 'required' }}>
                                                                <div class="form-text small text-muted">
                                                                    Unggah dokumen Surat Keterangan K3 yang telah ditandatangani basah / scan / TTE oleh Kepala Balai.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Pengesahan (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Pengesahan tanda tangan Kepala Balai...">Dokumen Surat Keterangan K3 telah ditandatangani dan disahkan oleh Kepala Balai K3 Surabaya.</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 5: PENERBITAN SUKET (INPUT NOMOR SURAT RESMI) --}}
                                                    @if($suket->status_tahap === 5)
                                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-dark">
                                                                    Nomor Surat Keterangan Resmi <span class="text-danger">*</span>
                                                                </label>
                                                                <input 
                                                                    type="text" 
                                                                    name="nomor_surat" 
                                                                    class="form-control form-control-lg fw-bold text-primary" 
                                                                    placeholder="Contoh: 566/SK-LK/BK3-SBY/IX/2026"
                                                                    value="{{ $suket->nomor_surat ?: ('566/SK-LK/BK3-SBY/' . \Carbon\Carbon::now()->format('m/Y')) }}"
                                                                    required
                                                                >
                                                                <div class="form-text small text-muted mt-2">
                                                                    <i class="bi bi-info-circle text-primary me-1"></i>
                                                                    <strong>Auto-Replace:</strong> Nomor surat yang Anda simpan di sini akan secara otomatis memperbarui nomor surat pada draf dokumen Word resmi Suket K3 Balai K3.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Penerbitan (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Surat keterangan resmi telah diterbitkan...">Surat Keterangan K3 resmi diterbitkan dan siap diserahkan ke pelanggan.</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 6: PENYERAHAN SUKET KE PEMOHON --}}
                                                    @if($suket->status_tahap === 6)
                                                        <div class="p-3 bg-light rounded-3 mb-3 border text-center">
                                                            <div class="mb-3">
                                                                <i class="bi bi-send-check-fill text-success fs-1"></i>
                                                            </div>
                                                            <h6 class="fw-bold text-dark mb-1">Penyerahan Suket ke Portal Pemohon</h6>
                                                            <p class="small text-muted mb-0">
                                                                Surat Keterangan K3 resmi (No: <strong>{{ $suket->nomor_surat }}</strong>) akan diserahkan secara resmi dan dapat diunduh pemohon di portal web Balai K3.
                                                            </p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Penyerahan ke Pemohon (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Catatan konfirmasi penyerahan...">Dokumen Surat Keterangan K3 Lingkungan Kerja telah terbit dan diserahkan kepada pemohon melalui web Balai K3.</textarea>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4" style="background-color: #15406A; border-color: #15406A;">
                                                        @if($suket->status_tahap === 6)
                                                            <i class="bi bi-send-check me-1"></i> Serahkan Suket ke Pemohon
                                                        @else
                                                            Konfirmasi & Proses Lanjut
                                                        @endif
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- MODAL UPLOAD REVISI DRAF WORD (Tahap 3) --}}
                                @if($suket->status_tahap === 3)
                                <div class="modal fade text-start" id="modalUploadDraft{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <form action="{{ route('suket.advance', $suket->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="action" value="upload_draft">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-dark">
                                                        <i class="bi bi-upload text-primary me-2"></i>Upload Berkas Revisi Draf Word
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <p class="small text-muted mb-3">
                                                        Unggah kembali file draf Word hasil koreksi/penyuntingan manual dari template resmi untuk nomor order <strong>{{ $suket->nomor_order }}</strong>.
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Pilih Berkas Draf Revisi (.doc / .docx / .pdf, Maks 20MB) <span class="text-danger">*</span></label>
                                                        <input type="file" name="revised_draft" class="form-control" accept=".doc,.docx,.pdf" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Catatan Revisi (Opsional)</label>
                                                        <textarea name="catatan" rows="2" class="form-control" placeholder="Penyesuaian redaksional klausul permenaker...">Draf suket telah disesuaikan dan diperbarui oleh penguji K3.</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4" style="background-color: #15406A; border-color: #15406A;">
                                                        Simpan Draf Revisi
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

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

                                                    <div class="d-flex gap-2 mb-3">
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-sm btn-outline-primary"
                                                            onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'draft']) }}', 'Draf Suket: {{ $suket->nomor_order }}', 'html')"
                                                        >
                                                            <i class="bi bi-eye me-1"></i>Preview Draf Suket
                                                        </button>
                                                        @if($suket->lhu_file_path)
                                                            <button 
                                                                type="button" 
                                                                class="btn btn-sm btn-outline-secondary"
                                                                onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'lhu']) }}', 'LHU: {{ $suket->nomor_order }}', 'pdf')"
                                                            >
                                                                <i class="bi bi-file-earmark-pdf me-1"></i>Lihat LHU
                                                            </button>
                                                        @endif
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-dark">Keputusan Review QC <span class="text-danger">*</span></label>
                                                        <div class="d-flex gap-3">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="action" id="qc_app_{{ $suket->id }}" value="approve" checked>
                                                                <label class="form-check-label text-success fw-bold small" for="qc_app_{{ $suket->id }}">
                                                                    <i class="bi bi-check-circle me-1"></i>Setujui (Lanjut ke TTD)
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="action" id="qc_rej_{{ $suket->id }}" value="revision">
                                                                <label class="form-check-label text-danger fw-bold small" for="qc_rej_{{ $suket->id }}">
                                                                    <i class="bi bi-arrow-return-left me-1"></i>Kembalikan (Perlu Revisi)
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

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <strong>Tidak ada berkas permohonan suket pada tahap ini.</strong><br>
                                <span class="small">Permohonan diajukan oleh pemohon/pelanggan melalui portal pemohon web Balai K3.</span>
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

{{-- MODAL UNIVERSAL DOCUMENT VIEWER (PREVIEW TANPA DOWNLOAD) --}}
<div class="modal fade" id="modalDocPreview" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 90vw;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="height: 88vh;">
            <div class="modal-header border-bottom py-2 px-3 bg-light">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text fs-5 text-primary" id="previewDocIcon"></i>
                    <h6 class="modal-title fw-bold text-dark mb-0" id="previewDocTitle">Preview Dokumen</h6>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="previewDocDirectLink" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Buka di Tab Baru">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Tab Baru
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative bg-dark bg-opacity-10 d-flex align-items-center justify-content-center" style="height: calc(88vh - 55px);">
                {{-- Spinner Loading --}}
                <div id="previewDocLoading" class="position-absolute text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-muted mt-2">Memuat dokumen...</div>
                </div>

                {{-- Iframe for PDF & HTML Preview --}}
                <iframe id="previewDocIframe" src="about:blank" class="w-100 h-100 border-0" style="display: none;" onload="docPreviewLoaded()"></iframe>

                {{-- Image Viewer --}}
                <img id="previewDocImage" src="" alt="Preview Dokumen" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: contain; display: none;" onload="docPreviewLoaded()">
            </div>
        </div>
    </div>
</div>

<script>
function openDocPreview(url, title, type) {
    const modalEl = document.getElementById('modalDocPreview');
    const modalTitle = document.getElementById('previewDocTitle');
    const directLink = document.getElementById('previewDocDirectLink');
    const iframe = document.getElementById('previewDocIframe');
    const image = document.getElementById('previewDocImage');
    const loading = document.getElementById('previewDocLoading');
    const icon = document.getElementById('previewDocIcon');

    modalTitle.textContent = title;
    directLink.href = url;
    loading.style.display = 'block';
    iframe.style.display = 'none';
    image.style.display = 'none';

    if (type === 'image') {
        icon.className = 'bi bi-image fs-5 text-info';
        image.src = url;
    } else {
        icon.className = type === 'pdf' ? 'bi bi-file-earmark-pdf fs-5 text-danger' : 'bi bi-file-earmark-word fs-5 text-primary';
        iframe.src = url;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function docPreviewLoaded() {
    const loading = document.getElementById('previewDocLoading');
    const iframe = document.getElementById('previewDocIframe');
    const image = document.getElementById('previewDocImage');

    loading.style.display = 'none';
    if (image.src && image.src !== window.location.href && !image.src.endsWith('about:blank')) {
        if (image.naturalWidth > 0) {
            image.style.display = 'block';
            return;
        }
    }
    if (iframe.src && !iframe.src.endsWith('about:blank')) {
        iframe.style.display = 'block';
    }
}
</script>
@endsection
