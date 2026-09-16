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
                $canT3 = in_array($currentRole, ['pcu', 'penguji_k3', 'admin', 'superadmin'], true);
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
                            <span>PENGUJI / ADMIN</span>
                        @endif
                    </div>
                </a>
            </div>

            {{-- Review QC Suket --}}
            @php
                $isQc = $activeStage == 'qc';
                $canQcRole = in_array($currentRole, ['qc', 'superadmin'], true);
                $qcPendingCount = $stageCounts['qc'] ?? \App\Models\SuketK3::where('status_tahap', 3)->where('qc_status', 'pending')->count();
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
                                {{-- Status Evaluasi Dokumen (Tahap 2) atau QC (Tahap 3 ke atas) --}}
                                <div>
                                    @if($suket->status_tahap === 2)
                                        @if($suket->evaluasi_status === 'rejected')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1" style="font-size: 10px;">
                                                <i class="bi bi-x-circle me-1"></i>Evaluasi Ditolak
                                            </span>
                                        @elseif($suket->evaluasi_status === 'approved')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-1" style="font-size: 10px;">
                                                <i class="bi bi-check-circle me-1"></i>Evaluasi Disetujui
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-1" style="font-size: 10px;">
                                                <i class="bi bi-clock me-1"></i>Perlu Evaluasi
                                            </span>
                                        @endif
                                        @if($suket->comments && $suket->comments->count() > 0)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-1" style="font-size: 10px;">
                                                <i class="bi bi-chat-left-text me-1"></i>{{ $suket->comments->count() }}
                                            </span>
                                        @endif
                                    @else
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
                                    @if($suket->evaluasi_status === 'rejected')
                                        <span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i>Ditolak Penguji:</span> {{ Str::limit($suket->catatan_evaluasi, 80) }}<br>
                                    @elseif($suket->catatan_evaluasi)
                                        <strong>Evaluasi:</strong> {{ Str::limit($suket->catatan_evaluasi, 80) }}<br>
                                    @endif
                                    @if($suket->qc_note)
                                        <strong>QC:</strong> {{ Str::limit($suket->qc_note, 80) }}<br>
                                    @endif
                                    @if(!$suket->catatan_evaluasi && !$suket->qc_note)
                                        {{ Str::limit($suket->catatan ?: ('Pengajuan suket didaftarkan melalui Nomor Order ' . $suket->nomor_order), 80) }}
                                    @else
                                        <div class="mt-1 text-muted" style="font-size: 10px;">
                                            <i class="bi bi-info-circle me-1"></i>{{ Str::limit($suket->catatan ?: ('Pengajuan suket didaftarkan melalui Nomor Order ' . $suket->nomor_order), 70) }}
                                        </div>
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

                                    {{-- TAHAP 3: Upload Lampiran Revisi Draf Word (Hanya di Penyusunan, bukan saat QC Pending) --}}
                                    @if($suket->status_tahap === 3 && $suket->qc_status !== 'pending' && in_array($currentRole, ['pcu', 'penguji_k3', 'admin', 'superadmin']))
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

                                    {{-- GERBANG REVIEW QC (Hanya jika Tahap 3 & Sedang Menunggu Review QC) --}}
                                    @if($suket->status_tahap === 3 && $suket->qc_status === 'pending')
                                        @if($canQc)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-warning text-dark rounded-pill px-3 py-1 fw-semibold"
                                                style="font-size: 11px;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalQc{{ $suket->id }}"
                                            >
                                                <i class="bi bi-shield-check me-1"></i>Review QC
                                            </button>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill" style="font-size: 10px;">
                                                <i class="bi bi-clock me-1"></i>Menunggu Review QC
                                            </span>
                                        @endif
                                    @endif

                                    {{-- TOMBOL PROSES TAHAP (Advance Stage) --}}
                                    @if($canProcess && $suket->status_tahap < 6)
                                        @if($suket->status_tahap === 2)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary rounded-pill px-3 py-1"
                                                style="background-color: #15406A; border-color: #15406A; font-size: 11px;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEvaluasiSideBySide{{ $suket->id }}"
                                            >
                                                <i class="bi bi-layout-split me-1"></i> Evaluasi Dokumen
                                            </button>
                                        @elseif($suket->status_tahap === 3)
                                            {{-- Tombol Kirim ke QC hanya muncul saat belum diajukan ke QC (Penyusunan Suket) --}}
                                            @if($suket->qc_status !== 'pending')
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-primary rounded-pill px-3 py-1"
                                                    style="background-color: #15406A; border-color: #15406A; font-size: 11px;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalAdvance{{ $suket->id }}"
                                                >
                                                    <i class="bi bi-send me-1"></i> Kirim ke QC
                                                </button>
                                            @endif
                                        @elseif($suket->status_tahap === 4)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary rounded-pill px-3 py-1"
                                                style="background-color: #15406A; border-color: #15406A; font-size: 11px;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalAdvance{{ $suket->id }}"
                                            >
                                                <i class="bi bi-pen me-1"></i> Pengesahan TTD
                                            </button>
                                        @elseif($suket->status_tahap === 5)
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary rounded-pill px-3 py-1"
                                                style="background-color: #15406A; border-color: #15406A; font-size: 11px;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalAdvance{{ $suket->id }}"
                                            >
                                                <i class="bi bi-award me-1"></i> Terbitkan Suket
                                            </button>
                                        @endif
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
                                    @elseif(!($suket->status_tahap === 3 && $suket->qc_status === 'pending'))
                                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill" style="font-size: 10px;" title="Kewenangan: {{ implode(', ', $stgInfo['roles']) }}">
                                            Menunggu {{ implode('/', array_map('strtoupper', $stgInfo['roles'])) }}
                                        </span>
                                    @endif

                                    {{-- TOMBOL RIWAYAT & LOG PERUBAHAN --}}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1"
                                        style="font-size: 11px;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalHistory{{ $suket->id }}"
                                        title="Lihat Log Riwayat Perubahan Suket"
                                    >
                                        <i class="bi bi-clock-history me-1"></i>Log Riwayat
                                    </button>
                                </div>

                                {{-- MODAL EVALUASI DOKUMEN SIDE-BY-SIDE (TAHAP 2) --}}
                                @if($suket->status_tahap === 2)
                                <div class="modal fade text-start" id="modalEvaluasiSideBySide{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 95vw;">
                                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="height: 90vh;">
                                            <div class="modal-header py-2 px-3 bg-light border-bottom">
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <span class="badge bg-primary px-2 py-1 rounded-pill">
                                                        <i class="bi bi-file-earmark-check me-1"></i>Evaluasi Dokumen LHU (Tahap 2)
                                                    </span>
                                                    <h6 class="modal-title fw-bold text-dark mb-0">
                                                        {{ $suket->nomor_order }} - {{ $suket->perusahaan_nama }}
                                                    </h6>
                                                    @if($suket->evaluasi_status === 'rejected')
                                                        <span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle me-1"></i>Status: Ditolak / Perlu Revisi</span>
                                                    @elseif($suket->evaluasi_status === 'approved')
                                                        <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Status: Telah Disetujui</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-clock me-1"></i>Status: Menunggu Evaluasi</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                            </div>
                                            <div class="modal-body p-0" style="height: calc(90vh - 56px);">
                                                <div class="row g-0 h-100">
                                                    {{-- SISI KIRI: PREVIEW DOKUMEN LHU (Ala Google Docs/Word) --}}
                                                    <div class="col-lg-7 d-flex flex-column h-100 border-end bg-dark bg-opacity-10">
                                                        <div class="d-flex justify-content-between align-items-center p-2 bg-white border-bottom flex-shrink-0">
                                                            <div class="d-flex align-items-center gap-1">
                                                                <span class="small fw-semibold text-muted me-2"><i class="bi bi-file-earmark-text text-danger me-1"></i>Dokumen Uji:</span>
                                                                @if($suket->hasLhuDocument())
                                                                    <button type="button" class="btn btn-xs btn-outline-danger active rounded px-2 py-1" onclick="switchEvalDoc('{{ route('suket.preview-doc', [$suket->id, 'lhu']) }}', 'evalIframe{{ $suket->id }}', 'evalImg{{ $suket->id }}', 'pdf')">
                                                                        <i class="bi bi-file-earmark-pdf me-1"></i>LHU
                                                                    </button>
                                                                @endif
                                                                @if($suket->foto_pengujian_path)
                                                                    <button type="button" class="btn btn-xs btn-outline-info rounded px-2 py-1" onclick="switchEvalDoc('{{ route('suket.preview-doc', [$suket->id, 'foto']) }}', 'evalIframe{{ $suket->id }}', 'evalImg{{ $suket->id }}', 'image')">
                                                                        <i class="bi bi-image me-1"></i>Foto
                                                                    </button>
                                                                @endif
                                                                @if($suket->denah_lokasi_path)
                                                                    <button type="button" class="btn btn-xs btn-outline-warning rounded px-2 py-1" onclick="switchEvalDoc('{{ route('suket.preview-doc', [$suket->id, 'denah']) }}', 'evalIframe{{ $suket->id }}', 'evalImg{{ $suket->id }}', 'image')">
                                                                        <i class="bi bi-map me-1"></i>Denah
                                                                    </button>
                                                                @endif
                                                            </div>
                                                            <div>
                                                                @if($suket->hasLhuDocument())
                                                                    <a href="{{ route('suket.preview-doc', [$suket->id, 'lhu']) }}" target="_blank" class="btn btn-xs btn-light border rounded px-2 py-1 text-muted" title="Buka di Tab Baru">
                                                                        <i class="bi bi-box-arrow-up-right me-1"></i>Tab Baru
                                                                    </a>
                                                                    <a href="{{ route('suket.download-doc', [$suket->id, 'lhu']) }}" class="btn btn-xs btn-outline-secondary rounded px-2 py-1" title="Unduh File LHU" download>
                                                                        <i class="bi bi-download me-1"></i>Unduh
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="flex-grow-1 position-relative overflow-y-auto p-3 d-flex flex-column align-items-center" id="evalPdfWrap{{ $suket->id }}" style="background-color: #525659; min-height: 0;">
                                                            {{-- Floating Action Button ala Google Docs (Muncul otomatis saat teks diseleksi kursor) --}}
                                                            <div id="gdocsFloatingBtn{{ $suket->id }}" class="gdocs-floating-btn" style="display: none;">
                                                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 shadow-lg fw-bold d-flex align-items-center gap-1" onmousedown="event.preventDefault(); event.stopPropagation();" onclick="window.LhuAnnotator.onFloatingCommentClick({{ $suket->id }})">
                                                                    <i class="bi bi-chat-left-text-fill"></i> Tulis Komentar
                                                                </button>
                                                            </div>

                                                            @if($suket->hasLhuDocument())
                                                                {{-- PDF Container dengan PDF.js & TextLayer --}}
                                                                <div id="evalPdfContainer{{ $suket->id }}" class="w-100 d-flex flex-column align-items-center"></div>

                                                                {{-- Fallback iframe & img --}}
                                                                <iframe id="evalIframe{{ $suket->id }}" src="" class="w-100 h-100 border-0 rounded" style="display: none; min-height: 75vh;"></iframe>
                                                                <img id="evalImg{{ $suket->id }}" src="" class="img-fluid mx-auto" style="display: none; max-height: 100%; object-fit: contain;">
                                                            @else
                                                                <div class="text-center p-4 text-white">
                                                                    <i class="bi bi-exclamation-triangle fs-1 text-warning mb-2 d-block"></i>
                                                                    <h6>Dokumen LHU Belum Terlampir</h6>
                                                                    <p class="small mb-0 text-white-50">Berkas LHU belum diunggah oleh pemohon atau di-generate otomatis.</p>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- SISI KANAN: PANEL SOROTAN KESALAHAN & KEPUTUSAN EVALUASI --}}
                    <div class="col-lg-5 d-flex flex-column h-100 bg-white" style="min-height: 0;">
                        {{-- Header Sorotan (Tetap di atas) --}}
                        <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center flex-shrink-0">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-highlighter text-danger me-2"></i>Sorotan Kesalahan & Evaluasi LHU
                                </h6>
                                <div class="small text-muted" style="font-size: 11px;">
                                    Sorot teks/parameter di dokumen (ala Google Docs) untuk menulis komentar koreksi.
                                </div>
                            </div>
                            <span class="badge bg-danger rounded-pill" id="commentBadgeCount{{ $suket->id }}">
                                {{ $suket->comments ? $suket->comments->count() : 0 }} Poin Sorotan
                            </span>
                        </div>

                        {{-- Body Panel Kanan (Scrollable Terpadu - Tidak Terpotong) --}}
                        <div class="p-3 flex-grow-1 overflow-y-auto d-flex flex-column gap-3" style="min-height: 0;" id="evalSidePanelScroll{{ $suket->id }}">
                            {{-- 1. Feed Sorotan Kesalahan --}}
                            <div id="commentFeedList{{ $suket->id }}">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold small text-dark">
                                        <i class="bi bi-list-check text-danger me-1"></i>Daftar Poin Sorotan ({{ $suket->comments ? $suket->comments->count() : 0 }}):
                                    </span>
                                </div>

                                @if($suket->comments && $suket->comments->count() > 0)
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($suket->comments as $cmt)
                                            <div class="card border rounded-3 p-2 shadow-xs border-danger-subtle bg-white" id="comment-card-{{ $suket->id }}-{{ $cmt->id }}">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="d-flex align-items-center gap-1 flex-wrap">
                                                        <span class="badge bg-danger text-white" style="font-size: 10px;">
                                                            <i class="bi bi-exclamation-octagon-fill me-1"></i>Poin #{{ $loop->iteration }}
                                                        </span>
                                                        @if($cmt->bagian)
                                                            <span class="badge bg-light text-dark border" style="font-size: 10px;">
                                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $cmt->bagian }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0" style="font-size: 10px;" onclick="window.LhuAnnotator.scrollToHighlight({{ $suket->id }}, {{ $cmt->id }}, '{{ addslashes($cmt->bagian ?? '') }}')" title="Tunjukkan di dokumen">
                                                            <i class="bi bi-geo-alt-fill me-1"></i>Tunjukkan
                                                        </button>
                                                        <form action="{{ route('suket.comment.delete', [$suket->id, $cmt->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus sorotan kesalahan ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link text-danger p-0 ms-1" title="Hapus Sorotan" style="font-size: 12px; line-height: 1;">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                @if($cmt->highlight_text)
                                                    <div class="p-2 rounded bg-warning bg-opacity-25 border border-warning-subtle my-1">
                                                        <div class="text-muted" style="font-size: 9.5px; font-weight: 600; text-transform: uppercase;">
                                                            <i class="bi bi-highlighter text-warning-emphasis me-1"></i>Bagian / Data yang Disorot Salah:
                                                        </div>
                                                        <div class="font-monospace small text-dark fw-semibold mt-1" style="font-size: 11px;">
                                                            <mark class="bg-warning text-dark px-1 rounded">{{ $cmt->highlight_text }}</mark>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="small text-dark mt-1" style="font-size: 11.5px;">
                                                    <strong>Catatan Perbaikan:</strong> {{ $cmt->comment }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-light border border-dashed text-center py-3 px-3 text-muted mb-0 rounded-3">
                                        <i class="bi bi-highlighter fs-3 text-secondary mb-1 d-block"></i>
                                        <div class="small fw-semibold text-dark">Belum ada poin sorotan kesalahan</div>
                                        <div class="small mt-1 text-muted" style="font-size: 11.5px;">
                                            Seleksi teks di dokumen LHU di sebelah kiri lalu klik tombol <span class="badge bg-primary text-white"><i class="bi bi-chat-left-text-fill me-1"></i>Tulis Komentar</span>.
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- 2. Form Tambah Sorotan Bagian yang Salah --}}
                            <div class="card border rounded-3 p-3 bg-white shadow-xs border-danger-subtle" id="cardAddComment{{ $suket->id }}">
                                <form id="formAddComment{{ $suket->id }}" action="{{ route('suket.comment', $suket->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="target" value="pemohon">
                                    <div class="fw-bold small text-dark mb-2 d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-plus-circle-fill text-danger me-1"></i>Tambah Sorotan Bagian yang Salah:</span>
                                        <span class="badge bg-danger-subtle text-danger" style="font-size: 9px;">Google Docs Highlight</span>
                                    </div>

                                    {{-- Chip Pratinjau Teks yang Diseleksi / Disorot --}}
                                    <div id="selectedHighlightBanner{{ $suket->id }}" class="alert alert-warning py-1 px-2 mb-2 rounded border border-warning-subtle small justify-content-between align-items-center" style="display: none;">
                                        <div class="text-truncate me-2" style="font-size: 11px;">
                                            <i class="bi bi-highlighter text-danger me-1"></i><strong>Teks Disorot:</strong> <mark class="bg-warning text-dark px-1 rounded font-monospace" id="selectedHighlightPreview{{ $suket->id }}"></mark>
                                        </div>
                                        <button type="button" class="btn-close btn-close-sm" onclick="window.LhuAnnotator.clearActiveSelection({{ $suket->id }})" title="Batalkan Sorotan"></button>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Posisi Bagian / Halaman <span class="text-danger">*</span></label>
                                        <input type="text" name="bagian" id="bagianInput{{ $suket->id }}" class="form-control form-control-sm" placeholder="Contoh: Halaman 2 - Tabel Kebisingan Titik 1" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Kutipan Teks / Nilai Data yang Salah</label>
                                        <textarea name="highlight_text" id="highlightTextInput{{ $suket->id }}" rows="2" class="form-control form-control-sm font-monospace" placeholder="Kutipan teks / nilai data yang salah di dokumen LHU (otomatis terisi saat teks diseleksi di dokumen)..."></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">Catatan / Instruksi Perbaikan <span class="text-danger">*</span></label>
                                        <textarea name="comment" id="commentInput{{ $suket->id }}" rows="2" class="form-control form-control-sm" placeholder="Tuliskan kenapa salah dan bagaimana seharusnya (instruksi perbaikan)..." required></textarea>
                                    </div>
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1" style="font-size: 11px;">
                                            <i class="bi bi-highlighter me-1"></i>Simpan Sorotan
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- 3. Form Keputusan Evaluasi (Setujui / Tolak) --}}
                            <div class="card border rounded-3 p-3 bg-light shadow-xs border-secondary-subtle">
                                <form action="{{ route('suket.advance', $suket->id) }}" method="POST">
                                    @csrf
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        <i class="bi bi-clipboard-check me-1"></i>Kesimpulan Akhir Telaah Teknis K3:
                                    </label>
                                    <textarea name="catatan" rows="2" class="form-control form-control-sm mb-2" placeholder="Tuliskan kesimpulan evaluasi hasil uji berdasarkan Permenaker No. 5/2018 (atau alasan umum jika ditolak)..." required>{{ $suket->catatan_evaluasi }}</textarea>
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <button type="submit" name="action" value="reject_evaluasi" class="btn btn-sm btn-danger rounded-pill px-3 py-1 fw-semibold" style="font-size: 11px;" onclick="return confirm('Apakah Anda yakin ingin MENOLAK dokumen LHU ini dan mengirimkan daftar poin sorotan kesalahan di atas ke pemohon untuk diperbaiki?')">
                                            <i class="bi bi-x-circle me-1"></i>Tolak / Minta Revisi LHU
                                        </button>
                                        <button type="submit" name="action" value="next" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold" style="font-size: 11px;">
                                            <i class="bi bi-check-circle me-1"></i>Setujui & Lanjut Tahap 3
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

                                {{-- MODAL ADVANCE STAGE --}}
                                @if(!($suket->status_tahap === 3 && $suket->qc_status === 'pending'))
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
                                                            <label class="form-label small fw-semibold text-dark">Hasil Telaah / Evaluasi Teknis K3 <span class="text-danger">*</span></label>
                                                            <textarea 
                                                                name="catatan" 
                                                                rows="3" 
                                                                class="form-control" 
                                                                placeholder="Tuliskan kesimpulan evaluasi hasil uji berdasarkan Permenaker No. 5/2018..."
                                                                required
                                                            >{{ $suket->catatan_evaluasi }}</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 3: PENGAJUAN DRAF KE TIM QC --}}
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
                                                                Draf telah disusun otomatis sesuai template Permenaker No. 5/2018. Silakan ajukan draf ini ke <strong>Tim QC</strong> untuk peninjauan kelayakan dan tata naskah.
                                                                Nomor Surat resmi akan ditetapkan saat proses verifikasi QC sebelum pengesahan Kepala Balai di Tahap 4.
                                                            </p>
                                                        </div>

                                                        @if($suket->qc_status === 'revision')
                                                            <div class="alert alert-warning d-flex align-items-start gap-2 p-2 mb-3 rounded-3 small">
                                                                <i class="bi bi-exclamation-triangle-fill text-warning fs-6 mt-1"></i>
                                                                <div>
                                                                    <strong>Catatan Arahan Revisi QC Sebelumnya:</strong><br>
                                                                    {{ $suket->qc_note ?: 'Mohon lakukan perbaikan draf dokumen sesuai klausul rekomendasi sebelum diajukan kembali ke QC.' }}
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Catatan Pengantar ke Tim QC (Opsional)</label>
                                                            <textarea name="catatan" rows="2" class="form-control" placeholder="Tuliskan catatan pengantar draf suket untuk Tim QC...">{{ $suket->catatan }}</textarea>
                                                        </div>
                                                    @endif

                                                    {{-- FORM KHUSUS TAHAP 4: PENANDATANGANAN KEPALA BALAI --}}
                                                    @if($suket->status_tahap === 4)
                                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                                            <h6 class="fw-bold text-dark mb-2">
                                                                <i class="bi bi-pen-fill text-danger me-2"></i>Pengesahan & Upload Dokumen Bertanda Tangan (TTD / TTE)
                                                            </h6>
                                                            <p class="small text-muted mb-3">
                                                                Kepala Balai atau Admin dapat meninjau draf dokumen (Nomor Surat: <strong>{{ $suket->nomor_surat }}</strong>), lalu mengunggah berkas Surat Keterangan yang telah ditandatangani secara basah (scan) atau bersertifikat elektronik (TTE).
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

                                                    {{-- FORM KHUSUS TAHAP 5: PENERBITAN SUKET RESMI --}}
                                                    @if($suket->status_tahap === 5)
                                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <div>
                                                                    <span class="text-muted small">Nomor Surat Resmi Suket K3:</span>
                                                                    <div class="fs-5 fw-bold text-primary">{{ $suket->nomor_surat ?: '-' }}</div>
                                                                </div>
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                                    <i class="bi bi-patch-check-fill me-1"></i>Telah Ditandatangani
                                                                </span>
                                                            </div>
                                                            <p class="small text-muted mb-0">
                                                                Dokumen Surat Keterangan K3 telah ditandatangani oleh Kepala Balai. Klik tombol di bawah untuk mengonfirmasi penerbitan suket resmi sebelum diserahkan ke pelanggan di Tahap 6.
                                                            </p>
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
                                                        @if($suket->status_tahap === 3)
                                                            <i class="bi bi-send me-1"></i> Kirim ke Tim QC
                                                        @elseif($suket->status_tahap === 5)
                                                            <i class="bi bi-award me-1"></i> Terbitkan Suket Resmi
                                                        @elseif($suket->status_tahap === 6)
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
                                @endif

                                {{-- MODAL UPLOAD REVISI DRAF WORD (Tahap 3 - Penyusunan Suket) --}}
                                @if($suket->status_tahap === 3 && $suket->qc_status !== 'pending')
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

                                {{-- MODAL GERBANG QC (KHUSUS ROLE QC / SUPERADMIN - Tahap 3 Pending QC) --}}
                                @if($canQc && $suket->status_tahap === 3 && $suket->qc_status === 'pending')
                                <div class="modal fade text-start" id="modalQc{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
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
                                                        Periksa kelayakan draf surat keterangan nomor order <strong>{{ $suket->nomor_order }}</strong> sebelum diajukan ke Kepala Balai untuk penandatanganan.
                                                    </p>

                                                    <div class="d-flex gap-2 mb-3">
                                                        <button 
                                                            type="button" 
                                                            class="btn btn-sm btn-outline-primary"
                                                            onclick="openDocPreview('{{ route('suket.preview-doc', [$suket->id, 'draft']) }}', 'Draf Suket: {{ $suket->nomor_order }}', 'html')"
                                                        >
                                                            <i class="bi bi-eye me-1"></i>Preview Draf Suket
                                                        </button>
                                                        @if($suket->hasLhuDocument())
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
                                                        <div class="d-flex gap-3 flex-wrap p-2 bg-light rounded-3 border">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="action" id="qc_app_{{ $suket->id }}" value="approve" checked onchange="toggleQcNomorSurat('{{ $suket->id }}', true)">
                                                                <label class="form-check-label text-success fw-bold small" for="qc_app_{{ $suket->id }}">
                                                                    <i class="bi bi-check-circle me-1"></i>Setujui & Tetapkan Nomor Surat (Lanjut ke TTD)
                                                                </label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="action" id="qc_rej_{{ $suket->id }}" value="revision" onchange="toggleQcNomorSurat('{{ $suket->id }}', false)">
                                                                <label class="form-check-label text-danger fw-bold small" for="qc_rej_{{ $suket->id }}">
                                                                    <i class="bi bi-arrow-return-left me-1"></i>Kembalikan ke Penyusunan (Perlu Revisi)
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- FORM INPUT NOMOR SURAT RESMI DI QC --}}
                                                    <div class="p-3 bg-white rounded-3 mb-3 border shadow-sm" id="qcNomorWrap{{ $suket->id }}">
                                                        <div class="row g-2">
                                                            <div class="col-md-8">
                                                                <label class="form-label small fw-bold text-dark">
                                                                    Nomor Surat Keterangan Resmi <span class="text-danger" id="qcNomorStar{{ $suket->id }}">*</span>
                                                                </label>
                                                                <input 
                                                                    type="text" 
                                                                    name="nomor_surat" 
                                                                    id="qcNomorInput{{ $suket->id }}" 
                                                                    class="form-control fw-bold text-primary" 
                                                                    placeholder="Contoh: 566/SK-LK/BK3-SBY/IX/2026"
                                                                    value="{{ $suket->nomor_surat ?: ('566/SK-LK/BK3-SBY/' . \Carbon\Carbon::now()->format('m/Y')) }}"
                                                                    required
                                                                >
                                                                <div class="form-text small text-muted" style="font-size: 11px;">
                                                                    <i class="bi bi-info-circle text-primary me-1"></i>
                                                                    Nomor surat resmi yang ditetapkan Tim QC akan langsung tercantum pada draf dokumen yang diajukan ke Kepala Balai.
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label small fw-bold text-dark">
                                                                    Tanggal Surat <span class="text-danger">*</span>
                                                                </label>
                                                                <input 
                                                                    type="date" 
                                                                    name="tanggal_surat" 
                                                                    id="qcTanggalInput{{ $suket->id }}"
                                                                    class="form-control" 
                                                                    value="{{ $suket->tanggal_surat ? \Carbon\Carbon::parse($suket->tanggal_surat)->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d') }}"
                                                                    required
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Catatan / Arahan Tim QC</label>
                                                        <textarea name="catatan" id="qcCatatan{{ $suket->id }}" rows="3" class="form-control" placeholder="Tuliskan catatan verifikasi kelayakan QC atau arahan perbaikan draf jika dikembalikan...">{{ $suket->qc_note }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-semibold" id="qcSubmitBtn{{ $suket->id }}">
                                                        <i class="bi bi-check2-circle me-1"></i> Simpan Keputusan QC
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- MODAL RIWAYAT & LOG PERUBAHAN SUKET --}}
                                <div class="modal fade text-start" id="modalHistory{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                                            <div class="modal-header border-bottom py-3 px-4 bg-light">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-clock-history fs-5 text-primary"></i>
                                                    <div>
                                                        <h6 class="modal-title fw-bold text-dark mb-0">Riwayat & Log Perubahan Suket</h6>
                                                        <div class="small text-muted" style="font-size: 11px;">
                                                            Order: <strong class="text-dark">{{ $suket->nomor_order }}</strong> &bull; {{ $suket->perusahaan_nama }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                                                @if($suket->histories->isEmpty())
                                                    <div class="text-center py-4 text-muted">
                                                        <i class="bi bi-hourglass-split fs-2 text-secondary d-block mb-2"></i>
                                                        <span class="small">Belum ada riwayat aktivitas yang tercatat untuk berkas suket ini.</span>
                                                    </div>
                                                @else
                                                    <div class="position-relative ps-3" style="border-left: 2px solid #e2e8f0; margin-left: 12px;">
                                                        @foreach($suket->histories as $history)
                                                            <div class="position-relative mb-4 ps-3">
                                                                {{-- Bullet Dot --}}
                                                                <div class="position-absolute rounded-circle bg-{{ $history->action_badge }}" style="width: 12px; height: 12px; left: -23px; top: 5px; border: 2px solid #fff; box-shadow: 0 0 0 1px #cbd5e1;"></div>
                                                                
                                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1 mb-1">
                                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                                        <span class="badge bg-{{ $history->action_badge }} rounded-pill px-2 py-1" style="font-size: 10px;">
                                                                            {{ $history->action_label }}
                                                                        </span>
                                                                        @if($history->stage_before || $history->stage_after)
                                                                            <span class="small text-muted" style="font-size: 11px;">
                                                                                {{ $history->stage_before_label ?? '-' }} <i class="bi bi-arrow-right text-primary mx-1"></i> {{ $history->stage_after_label ?? '-' }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="small text-muted" style="font-size: 11px;">
                                                                        <i class="bi bi-clock me-1"></i>{{ $history->created_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                                                        <span class="text-secondary">({{ $history->created_at?->diffForHumans() }})</span>
                                                                    </div>
                                                                </div>

                                                                <div class="bg-light p-3 rounded-3 border">
                                                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 11px;">
                                                                        <span class="text-dark fw-semibold">
                                                                            <i class="bi bi-person-fill text-secondary me-1"></i>{{ $history->user?->name ?? 'Sistem Balai K3' }}
                                                                            @if($history->user?->role)
                                                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-1 ms-1" style="font-size: 9px;">
                                                                                    {{ strtoupper(str_replace('_', ' ', $history->user->role)) }}
                                                                                </span>
                                                                            @endif
                                                                        </span>
                                                                        @if($history->nomor_surat)
                                                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">
                                                                                <i class="bi bi-award me-1"></i>No: {{ $history->nomor_surat }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                    @if($history->catatan)
                                                                        <div class="text-secondary small mt-1" style="font-size: 11px; white-space: pre-line;">
                                                                            {{ $history->catatan }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer border-0 pt-0 bg-light">
                                                <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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

function switchEvalDoc(url, iframeId, imgId, type) {
    const iframe = document.getElementById(iframeId);
    const img = document.getElementById(imgId);
    if (!iframe || !img) return;

    if (type === 'image') {
        iframe.style.display = 'none';
        img.src = url;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
        iframe.src = url;
        iframe.style.display = 'block';
    }
}

function toggleQcNomorSurat(suketId, isApprove) {
    const wrap = document.getElementById('qcNomorWrap' + suketId);
    const input = document.getElementById('qcNomorInput' + suketId);
    const tglInput = document.getElementById('qcTanggalInput' + suketId);
    const star = document.getElementById('qcNomorStar' + suketId);
    const tglStar = document.getElementById('qcTglStar' + suketId);
    const catatan = document.getElementById('qcCatatan' + suketId);

    if (isApprove) {
        if (wrap) {
            wrap.style.opacity = '1';
            wrap.style.pointerEvents = 'auto';
        }
        if (input) input.required = true;
        if (tglInput) tglInput.required = true;
        if (star) star.style.display = 'inline';
        if (tglStar) tglStar.style.display = 'inline';
        if (catatan) catatan.required = false;
    } else {
        if (wrap) {
            wrap.style.opacity = '0.5';
            wrap.style.pointerEvents = 'none';
        }
        if (input) input.required = false;
        if (tglInput) tglInput.required = false;
        if (star) star.style.display = 'none';
        if (tglStar) tglStar.style.display = 'none';
        if (catatan) {
            catatan.required = true;
            catatan.focus();
        }
    }
}
</script>

<link rel="stylesheet" href="{{ asset('vendor/pdfjs/pdf_viewer.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/lhu-annotator.css') }}">
<script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
<script src="{{ asset('js/lhu-annotator.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @foreach($sukets as $suketItem)
        @if($suketItem->status_tahap === 2 && $suketItem->hasLhuDocument())
            const modalEl{{ $suketItem->id }} = document.getElementById('modalEvaluasiSideBySide{{ $suketItem->id }}');
            if (modalEl{{ $suketItem->id }}) {
                modalEl{{ $suketItem->id }}.addEventListener('shown.bs.modal', function () {
                    window.LhuAnnotator.init({
                        suketId: {{ $suketItem->id }},
                        containerId: 'evalPdfContainer{{ $suketItem->id }}',
                        pdfUrl: '{{ route('suket.preview-doc', [$suketItem->id, 'lhu']) }}',
                        comments: @json($suketItem->comments ?? []),
                        readOnly: false,
                        floatingBtnId: 'gdocsFloatingBtn{{ $suketItem->id }}',
                        highlightBannerId: 'selectedHighlightBanner{{ $suketItem->id }}',
                        highlightPreviewId: 'selectedHighlightPreview{{ $suketItem->id }}',
                        highlightTextInputId: 'highlightTextInput{{ $suketItem->id }}',
                        bagianInputId: 'bagianInput{{ $suketItem->id }}',
                        commentInputId: 'commentInput{{ $suketItem->id }}',
                    });
                });
            }
        @endif
    @endforeach
});
</script>
@endsection
