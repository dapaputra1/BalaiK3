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
            <a href="{{ route('ergo.docx', $assessment->id) }}" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-1.5 text-white">
                <i class="bi bi-file-earmark-word"></i>
                <span>Unduh LHU (Word / DOCX)</span>
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
                        @if(!empty($assessment->job_tasks))
                            <div class="mb-1"><strong class="text-dark">Deskripsi Tugas:</strong> {{ $assessment->job_tasks }}</div>
                        @endif
                        @if(!empty($assessment->job_duration))
                            <div class="mb-1 p-2 bg-white rounded border border-slate-200 mt-2">
                                <strong class="text-dark d-block mb-0.5"><i class="bi bi-clock-history text-primary me-1"></i> Alokasi Waktu & Aktivitas:</strong>
                                <span class="text-muted">{{ $assessment->job_duration }}</span>
                            </div>
                        @endif
                        @if(!empty($assessment->assessor_name))
                            <div class="mb-1 p-2 bg-light rounded border mt-2">
                                <strong class="text-dark d-block mb-0.5"><i class="bi bi-pen text-primary me-1"></i> Penilai (SNI 9011:2021):</strong>
                                <div class="text-secondary small">{{ $assessment->assessor_role ?? 'Penguji K3' }}</div>
                                <div class="fw-bold text-dark">{{ $assessment->assessor_name }}</div>
                                @if(!empty($assessment->assessor_nip))
                                    <div class="text-muted" style="font-size: 11px;">NIP/REG: {{ $assessment->assessor_nip }}</div>
                                @endif
                            </div>
                        @endif
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
                        <span class="fs-4 fw-bold text-dark">{{ $assessment->mmh_total_score ?? ($assessment->mmh_score ?? 0) }}</span>
                        @if(isset($assessment->mmh_step3_score))
                            <span class="text-muted d-block" style="font-size: 10px;">(Lgkh 2: {{ $assessment->mmh_step2_score ?? 0 }} | Lgkh 3: {{ $assessment->mmh_step3_score }})</span>
                        @endif
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="score-box bg-primary-subtle border border-primary-subtle">
                        <span class="text-navy fw-semibold small d-block">Total Skor Akhir</span>
                        <span class="fs-4 fw-bold text-navy">{{ $assessment->total_score }}</span>
                        @if(isset($assessment->shift_hours) && (float)$assessment->shift_hours > 8)
                            @php
                                $otHours = (float)$assessment->shift_hours - 8;
                                $otBonus = $otHours * 0.5;
                            @endphp
                            <span class="text-primary d-block fw-semibold" style="font-size: 10px;">(Termasuk Lembur +{{ $otBonus }} skor dari {{ $otHours }} jam kelebihan)</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Hasil Evaluasi Keluhan GOTRAK (SNI 9011:2021) --}}
        @if(isset($gotrakAssessments) && count($gotrakAssessments) > 0)
            @php
                $freqLabels = [1 => 'Tidak pernah', 2 => 'Terkadang (1-3x/th)', 3 => 'Sering (1-3x/bln)', 4 => 'Selalu (tiap hari)'];
                $sevLabels = [1 => 'Tidak ada masalah', 2 => 'Tidak nyaman', 3 => 'Sakit', 4 => 'Sakit parah'];
                $activeGotrak = $gotrakAssessments->filter(function($item) {
                    return (int)$item->score > 1;
                });
            @endphp
            <div class="pt-3 border-top mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill"></i> Hasil Evaluasi Keluhan Otot Rangka (GOTRAK / SNI 9011:2021)
                    </h6>
                    <span class="badge bg-light text-secondary border fw-normal">{{ $activeGotrak->count() }} Keluhan Aktif</span>
                </div>

                @if(!empty($assessment->gotrak_summary_narrative))
                    <div class="p-3 bg-light rounded-3 border small text-secondary mb-3">
                        <strong class="d-block text-dark mb-1"><i class="bi bi-info-circle me-1"></i> Ringkasan Analisis GOTRAK:</strong>
                        {{ $assessment->gotrak_summary_narrative }}
                    </div>
                @endif

                <div class="table-responsive rounded-3 border">
                    <table class="table table-sm table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 40px;">No.</th>
                                <th>Bagian Tubuh</th>
                                <th class="text-center">Sisi</th>
                                <th>Frekuensi</th>
                                <th>Keparahan</th>
                                <th class="text-center">Skor</th>
                                <th class="text-center">Kategori Risiko</th>
                                <th>Pekerjaan Penyebab</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeGotrak as $g)
                                <tr>
                                    <td class="text-center fw-semibold text-secondary">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $g->body_part_name }}</td>
                                    <td class="text-center">{{ $g->side ?? '-' }}</td>
                                    <td>{{ $freqLabels[$g->frequency] ?? $g->frequency }}</td>
                                    <td>{{ $sevLabels[$g->severity] ?? $g->severity }}</td>
                                    <td class="text-center fw-bold">{{ $g->score }}</td>
                                    <td class="text-center">
                                        @if($g->risk_category === 'Risiko Rendah')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill">Rendah</span>
                                        @elseif($g->risk_category === 'Risiko Sedang')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 rounded-pill">Sedang</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 rounded-pill">Tinggi</span>
                                        @endif
                                    </td>
                                    <td>{{ $g->cause_description ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-3 italic">
                                        Tidak ada keluhan rasa sakit yang dialami pekerja (Semua bagian tubuh berada pada Risiko Rendah).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

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