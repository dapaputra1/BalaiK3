@extends('layouts.app_admin')

@section('title', 'Hasil Evaluasi Ergonomi — ' . $assessment->worker_name)

@section('content_admin')
<div class="container-fluid px-0">

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
        .btn-outline-primary:focus {
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
        .card-summary {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .score-box {
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
        @media print {
            .no-print { display: none !important; }
            #sidebar, .topbar { display: none !important; }
            .main { margin-left: 0 !important; width: 100% !important; }
        }
    </style>

    {{-- Top Action Bar --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 no-print">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('ergo.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 d-flex align-items-center gap-1">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Daftar</span>
            </a>
            <span class="badge bg-primary-subtle text-navy border border-primary-subtle px-2.5 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                SNI 9011:2021
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('ergo.edit', $assessment->id) }}" class="btn btn-outline-primary btn-sm rounded-3 d-flex align-items-center gap-1.5">
                <i class="bi bi-pencil"></i>
                <span>Edit Pengujian</span>
            </a>
            <a href="{{ route('ergo.lhu.edit', $assessment->id) }}" class="btn btn-outline-secondary btn-sm rounded-3 d-flex align-items-center gap-1.5">
                <i class="bi bi-file-earmark-text"></i>
                <span>Edit Draf LHU</span>
            </a>
            <a href="{{ route('ergo.pdf', $assessment->id) }}" target="_blank" class="btn btn-danger btn-sm rounded-3 d-flex align-items-center gap-1.5">
                <i class="bi bi-file-earmark-pdf"></i>
                <span>Unduh LHU (PDF)</span>
            </a>
        </div>
    </div>

    {{-- Main Document Card --}}
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
        
        {{-- Header Dokumen & Score Banner --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 pb-4 border-bottom mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-navy rounded-3 d-flex align-items-center justify-content-center fw-bold fs-4 text-white" style="width: 52px; height: 52px;">
                    K3
                </div>
                <div>
                    <h5 class="fw-bold text-navy mb-0">Laporan Hasil Uji (LHU) Faktor Ergonomi</h5>
                    <div class="text-muted small">Balai Keselamatan dan Kesehatan Kerja Surabaya</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 bg-light p-2.5 px-3 rounded-4 border">
                <div class="text-end">
                    <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 10px;">Tingkat Risiko</span>
                    <span class="fw-bold text-dark fs-6">{{ $assessment->risk_level }}</span>
                </div>
                <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold fs-4 px-3 py-2 border
                    @if($assessment->risk_level === 'Aman') bg-success-subtle text-success border-success-subtle
                    @elseif($assessment->risk_level === 'Perlu Pengamatan Lanjut') bg-warning-subtle text-warning-emphasis border-warning-subtle
                    @else bg-danger-subtle text-danger border-danger-subtle @endif">
                    {{ $assessment->total_score }}
                </div>
            </div>
        </div>

        {{-- Grid Info: Perusahaan & Tenaga Kerja --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6">
                <div class="card-summary p-3 h-100">
                    <h6 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-buildings"></i> Identitas Perusahaan
                    </h6>
                    <div class="small text-secondary space-y-2">
                        <div class="mb-1"><strong class="text-dark">Nama:</strong> {{ $assessment->company_name }}</div>
                        <div class="mb-1"><strong class="text-dark">Alamat:</strong> {{ $assessment->company_address ?? '-' }}</div>
                        <div class="mb-1"><strong class="text-dark">Sektor:</strong> {{ $assessment->company_sector ?? '-' }}</div>
                        <div class="mb-1"><strong class="text-dark">Tanggal Sampling:</strong> {{ \Carbon\Carbon::parse($assessment->assessment_date)->isoFormat('D MMMM Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card-summary p-3 h-100">
                    <h6 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-person"></i> Profil Tenaga Kerja
                    </h6>
                    <div class="small text-secondary space-y-2">
                        <div class="mb-1"><strong class="text-dark">Nama Pekerja:</strong> {{ $assessment->worker_name }}</div>
                        <div class="mb-1"><strong class="text-dark">Posisi / Jabatan:</strong> {{ $assessment->position ?? '-' }}</div>
                        <div class="mb-1"><strong class="text-dark">Durasi Shift:</strong> {{ $assessment->shift_hours }} jam / hari</div>
                        <div class="mb-1"><strong class="text-dark">Tangan Dominan:</strong> {{ $assessment->dominant_hand ?? 'Kanan' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rincian Skor Evaluasi --}}
        <div class="mb-4">
            <h6 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-calculator"></i> Rincian Skor Evaluasi (SNI 9011:2021)
            </h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="score-box bg-light border">
                        <span class="text-muted small d-block">Tubuh Bagian Atas</span>
                        <span class="fs-4 fw-bold text-dark">{{ $assessment->upper_body_score ?? 0 }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="score-box bg-light border">
                        <span class="text-muted small d-block">Punggung & Bawah</span>
                        <span class="fs-4 fw-bold text-dark">{{ $assessment->lower_body_score ?? 0 }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="score-box bg-light border">
                        <span class="text-muted small d-block">Beban Manual (MMH)</span>
                        <span class="fs-4 fw-bold text-dark">{{ $assessment->mmh_score ?? 0 }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="score-box bg-primary-subtle border border-primary-subtle">
                        <span class="text-navy fw-semibold small d-block">Total Skor Akhir</span>
                        <span class="fs-4 fw-bold text-navy">{{ $assessment->total_score }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lampiran Foto Dokumentasi --}}
        <div class="pt-3 border-top mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-camera"></i> Lampiran Foto Dokumentasi Postur Kerja
                </h6>
                <span class="badge bg-light text-secondary border fw-normal">{{ count($photos) }} Berkas</span>
            </div>

            @if(count($photos) > 0)
                <div class="row g-3">
                    @foreach($photos as $photo)
                        @php
                            $imageUrl = filter_var($photo->file_path, FILTER_VALIDATE_URL) 
                                ? $photo->file_path 
                                : asset('storage/' . ltrim($photo->file_path, '/'));
                        @endphp
                        <div class="col-12 col-sm-6 col-lg-4">
                            <div class="card border rounded-3 overflow-hidden h-100 shadow-xs">
                                <div class="bg-dark d-flex align-items-center justify-content-center" style="height: 220px;">
                                    <img src="{{ $imageUrl }}" 
                                         alt="{{ $photo->photo_name }}" 
                                         class="img-fluid mh-100 mw-100" 
                                         style="object-fit: contain;"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'text-danger p-3 small text-center\'><i class=\'bi bi-exclamation-triangle d-block fs-3 mb-1\'></i>Foto tidak ditemukan</div>';">
                                </div>
                                <div class="card-body p-2 px-3 d-flex justify-content-between align-items-center bg-white border-top">
                                    <span class="small fw-semibold text-truncate text-secondary" style="max-width: 180px;" title="{{ $photo->photo_name }}">
                                        {{ $photo->photo_name }}
                                    </span>
                                    @if(!empty($photo->landmarks_json))
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">
                                            <i class="bi bi-check me-1"></i> Anotasi Sudut
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-4 text-center text-muted border border-dashed rounded-3">
                    <i class="bi bi-image fs-3 d-block mb-1 text-secondary opacity-50"></i>
                    <small>Tidak ada lampiran foto untuk pengujian ini.</small>
                </div>
            @endif
        </div>

        {{-- Tindakan Pengendalian & Catatan --}}
        @if($assessment->existing_control || (!empty($assessment->notes)))
            <div class="pt-3 border-top">
                <h6 class="fw-bold text-navy mb-2 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check"></i> Rekomendasi & Pengendalian
                </h6>
                <div class="p-3 bg-light rounded-3 border small text-secondary">
                    {!! nl2br(e($assessment->existing_control ?? $assessment->notes)) !!}
                </div>
            </div>
        @endif

    </div>

</div>
@endsection