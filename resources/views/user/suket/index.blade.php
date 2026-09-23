@extends('layouts.app')

@section('content')
<div class="container py-4 mt-5 history-shell" style="font-family: 'Poppins', sans-serif;">
    <style>
        .history-shell {
            font-family: 'Poppins', sans-serif;
        }
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
        /* Pastikan modal selalu berada di atas navbar (#mainNavbar memiliki z-index: 9999) */
        .modal {
            z-index: 100050 !important;
        }
        .modal-backdrop {
            z-index: 100040 !important;
        }
        body.modal-open #mainNavbar {
            z-index: 1000 !important;
            opacity: 0.1 !important;
            pointer-events: none !important;
        }

        /* Modal Ajukan Suket: Dialog & Scrollable Rules */
        #modalAjukanSuket .modal-dialog {
            max-width: 760px;
            margin: 24px auto !important;
            max-height: calc(100vh - 48px);
        }
        #modalAjukanSuket .modal-content {
            max-height: calc(100vh - 48px);
            display: flex;
            flex-direction: column;
            border-radius: 20px !important;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.25) !important;
        }
        #modalAjukanSuket .modal-header {
            flex-shrink: 0;
            padding: 16px 24px;
        }
        #modalAjukanSuket .modal-body {
            flex: 1 1 auto;
            overflow-y: auto !important;
            min-height: 0 !important;
            max-height: calc(100vh - 190px) !important;
            padding: 20px 24px !important;
        }
        #modalAjukanSuket .modal-footer {
            flex-shrink: 0;
            padding: 14px 24px;
            background: #ffffff;
            border-top: 1px solid #e9ecef;
        }

        /* Custom smooth scrollbar for modal body */
        #modalAjukanSuket .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        #modalAjukanSuket .modal-body::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        #modalAjukanSuket .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        #modalAjukanSuket .modal-body::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Modal Preview Dokumen */
        #previewDocModal .modal-dialog {
            margin: 20px auto !important;
        }
        #previewDocModal .modal-content {
            max-height: calc(100vh - 40px);
            border-radius: 20px !important;
        }

        /* Stepper Tracking timeline like riwayat_pelayanan */
        .track-wrap {
            position: relative;
            padding-top: 10px;
            padding-bottom: 8px;
        }
        .track-steps {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            gap: 6px;
        }
        .track-step {
            position: relative;
            text-align: center;
            flex: 1;
            min-width: 80px;
        }
        .track-step .line {
            height: 6px;
            border-radius: 4px;
            background: #e9ecef;
            margin: 0 auto 8px auto;
            width: 100%;
        }
        .track-step .circle {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #e9ecef;
            margin: 0 auto 6px auto;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            color: #6c757d;
            transition: all 0.2s ease;
        }
        .track-step.is-active .line {
            background: #15406A;
        }
        .track-step.is-active .circle {
            background: #15406A;
            color: #ffffff;
            box-shadow: 0 0 0 3px rgba(21, 64, 106, 0.25);
        }
        .track-step.is-complete .line {
            background: #198754;
        }
        .track-step.is-complete .circle {
            background: #198754;
            color: #ffffff;
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.25);
        }
        .track-step .step-label {
            font-size: 11px;
            line-height: 1.25;
            color: #6c757d;
            font-weight: 500;
        }
        .track-step.is-active .step-label {
            color: #15406A;
            font-weight: 700;
        }
        .track-step.is-complete .step-label {
            color: #198754;
            font-weight: 600;
        }
        .track-step.is-rejected .line {
            background: #dc3545;
        }
        .track-step.is-rejected .circle {
            background: #dc3545;
            color: #ffffff;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.25);
        }
        .track-step.is-rejected .step-label {
            color: #dc3545;
            font-weight: 700;
        }

        /* History Card */
        .history-card {
            border: 1px solid #e9ecef;
            border-radius: 20px !important;
            background: #ffffff;
            transition: all 0.25s ease;
        }
        .history-card:hover {
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08) !important;
            border-color: #cbd5e1;
        }
        .filter-card {
            border-radius: 18px !important;
        }

        @media (max-width: 767.98px) {
            .track-steps {
                overflow-x: auto;
                padding-bottom: 8px;
            }
            .track-step {
                min-width: 100px;
            }
        }
    </style>

    {{-- Breadcrumb & Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/riwayat_pelayanan" class="text-decoration-none text-muted">Pelayanan</a></li>
                    <li class="breadcrumb-item active text-navy fw-semibold" aria-current="page">Permohonan Suket K3</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-medical text-navy me-2"></i>Permohonan Surat Keterangan (Suket) K3 Lingkungan Kerja
            </h4>
            <p class="text-muted small mb-0">
                Layanan pengajuan dan monitoring penerbitan Surat Keterangan K3 Lingkungan Kerja resmi berstandar <strong>Permenaker No. 5 Tahun 2018</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-primary rounded-pill px-3 py-2 btn-sm fw-semibold shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalAjukanSuket">
                <i class="bi bi-plus-circle-fill"></i> Ajukan Suket Baru
            </button>
            <a href="/riwayat_pelayanan" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Riwayat Pelayanan
            </a>
        </div>
    </div>

    {{-- Alert Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 text-success me-3"></i>
                <div><strong>Berhasil!</strong> {{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-danger me-3"></i>
                <div><strong>Perhatian:</strong> {{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-octagon-fill fs-5 text-danger mt-1"></i>
                <div>
                    <strong>Perhatian - Terjadi Kesalahan Input Formulir:</strong>
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

    {{-- Filter Toolbar (Seperti riwayat_pelayanan.blade.php) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 filter-card bg-white">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap gap-2 align-items-center filter-toolbar">
                <button type="button" class="btn btn-outline-primary btn-sm active px-3 rounded-pill" data-user-filter="all">
                    Semua Permohonan
                    <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $sukets->total() }}</span>
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill" data-user-filter="process">
                    Sedang Diproses
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill" data-user-filter="done">
                    Tuntas Diserahkan
                </button>
                <div class="ms-auto filter-search" style="min-width: 250px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 bg-light" placeholder="Cari nomor order / surat..." id="userSearchInput">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Riwayat Permohonan Suket (Cards Layout seperti riwayat_pelayanan) --}}
    <div class="row g-3" id="userSuketList">
        @forelse($sukets as $suket)
            @php
                $isDelivered = !empty($suket->sent_to_customer_at);
                $filterCategory = $isDelivered ? 'done' : 'process';
                $stageNumber = (int) $suket->status_tahap;
                $labelTahap = \App\Models\SuketK3::STAGES[$stageNumber]['label'] ?? "Tahap {$stageNumber}";
                $fList = is_array($suket->faktor_k3) ? $suket->faktor_k3 : [];

                // Tentukan status stepper untuk 9 tahapan
                $steps = [
                    1 => 'Permohonan',
                    2 => 'Evaluasi Dokumen',
                    3 => 'Penyusunan Suket',
                    4 => 'Penandatanganan Suket',
                    5 => 'Penerbitan Suket',
                    6 => 'Surat Tagihan',
                    7 => 'Kode Billing',
                    8 => 'Kuitansi',
                    9 => 'Penyerahan Suket',
                ];
            @endphp
            <div class="col-12 user-suket-card-item" 
                 data-category="{{ $filterCategory }}" 
                 data-order="{{ strtolower($suket->nomor_order ?? '') }}"
                 data-surat="{{ strtolower($suket->nomor_surat ?? '') }}"
                 data-company="{{ strtolower($suket->perusahaan_nama ?? '') }}">
                
                <div class="history-card p-4 shadow-sm">
                    {{-- Head Row: Info Utama & Status Badge --}}
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3 pb-3 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h5 class="fw-bold text-dark mb-0 fs-6">{{ $suket->nomor_order }}</h5>
                                <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill small">
                                    <i class="bi bi-calendar3 me-1"></i>{{ $suket->created_at ? $suket->created_at->format('d M Y, H:i') : '-' }}
                                </span>
                            </div>
                            <div class="fw-semibold text-navy small mb-1">{{ $suket->perusahaan_nama ?: '-' }}</div>
                            <div class="text-muted small" style="font-size: 11px;">
                                <i class="bi bi-geo-alt me-1"></i>{{ $suket->lokasi ?: 'Lokasi Pengujian' }}
                            </div>
                        </div>
                        <div class="text-end">
                            @if($isDelivered)
                                <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold shadow-sm mb-1 d-inline-block">
                                    <i class="bi bi-patch-check-fill me-1"></i>Tuntas Diserahkan
                                </span>
                                @if(!empty($suket->nomor_surat))
                                    <div class="small fw-bold text-success" style="font-size: 11.5px;">
                                        No: {{ $suket->nomor_surat }}
                                    </div>
                                @endif
                            @elseif($suket->isEvaluasiRejected())
                                <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold shadow-sm mb-1 d-inline-block" 
                                      style="cursor: pointer;" 
                                      data-bs-toggle="modal" 
                                      data-bs-target="#modalUserEvaluasiLhu{{ $suket->id }}"
                                      title="Klik untuk membuka dokumen LHU dan melihat bagian yang disorot salah"
                                >
                                    <i class="bi bi-x-circle me-1"></i>Evaluasi Perlu Revisi
                                </span>
                                <div class="small text-danger fw-semibold" style="font-size: 11px;">
                                    Terdapat {{ $suket->pemohonComments ? $suket->pemohonComments->count() : 0 }} Poin Catatan Penguji K3 (Klik untuk lihat)
                                </div>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold mb-1 d-inline-block"
                                      style="cursor: pointer;"
                                      data-bs-toggle="modal"
                                      data-bs-target="#modalUserEvaluasiLhu{{ $suket->id }}"
                                      title="Klik untuk melihat dokumen LHU dan progres evaluasi"
                                >
                                    <i class="bi bi-clock-history me-1"></i>Tahap {{ $stageNumber }}: {{ $labelTahap }}
                                </span>
                                @if(!empty($suket->nomor_surat))
                                    <div class="small fw-bold text-primary" style="font-size: 11.5px;">
                                        No: {{ $suket->nomor_surat }}
                                    </div>
                                @else
                                    <div class="small text-muted" style="font-size: 11px;">
                                        Sedang Diproses Balai K3
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Horizontal Stepper Tracking (6 Tahap) --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="small fw-bold text-dark">
                                <i class="bi bi-diagram-3 text-navy me-1"></i>Progress Penerbitan Suket
                            </div>
                            <div class="small text-muted" style="font-size: 11px;">
                                @if($isDelivered)
                                    <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Tuntas 100%</span>
                                @else
                                    <span>Tahap {{ $stageNumber }} dari 9</span>
                                @endif
                            </div>
                        </div>

                        <div class="track-wrap">
                            <div class="track-steps">
                                @foreach($steps as $sNum => $sName)
                                    @php
                                        // Status per step:
                                        if ($isDelivered) {
                                            $stepClass = 'is-complete';
                                        } else {
                                            if ($sNum < $stageNumber) {
                                                $stepClass = 'is-complete';
                                            } elseif ($sNum == $stageNumber) {
                                                $stepClass = ($suket->isEvaluasiRejected() && $sNum === 2) ? 'is-rejected' : 'is-active';
                                            } else {
                                                $stepClass = '';
                                            }
                                        }
                                    @endphp
                                    <div class="track-step {{ $stepClass }}"
                                         @if($sNum === 2)
                                             style="cursor: pointer;"
                                             data-bs-toggle="modal"
                                             data-bs-target="#modalUserEvaluasiLhu{{ $suket->id }}"
                                             title="Klik untuk melihat dokumen dan hasil evaluasi LHU"
                                         @endif
                                    >
                                        <div class="line"></div>
                                        <div class="circle">
                                            @if($stepClass === 'is-complete')
                                                <i class="bi bi-check-lg"></i>
                                            @elseif($stepClass === 'is-rejected')
                                                <i class="bi bi-x-lg"></i>
                                            @else
                                                {{ $sNum }}
                                            @endif
                                        </div>
                                        <div class="step-label">
                                            {{ $sName }}
                                            @if($sNum === 2)
                                                <span class="badge {{ $suket->isEvaluasiRejected() ? 'bg-danger' : 'bg-primary-subtle text-primary border' }} rounded-pill d-block mt-1" style="font-size: 8.5px;">
                                                    <i class="bi bi-eye me-1"></i>Buka
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Ruang Lingkup & Dokumen Terlampir --}}
                    <div class="row g-3 align-items-center pt-2 border-top">
                        <div class="col-md-7">
                            <div class="small text-muted mb-1" style="font-size: 11px;">Ruang Lingkup Faktor K3 Diuji:</div>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($fList as $fItem)
                                    <span class="badge bg-light text-navy border px-2 py-1 rounded" style="font-size: 11px;">
                                        <i class="bi bi-check2 text-primary me-1"></i>{{ ucfirst($fItem) }}
                                    </span>
                                @empty
                                    <span class="text-muted small">-</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Action Buttons & Document Gate --}}
                        <div class="col-md-5">
                            <div class="d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                                {{-- Tombol Buka Evaluasi LHU --}}
                                <button 
                                    type="button" 
                                    class="btn btn-sm rounded-pill px-3 {{ $suket->isEvaluasiRejected() ? 'btn-danger shadow-sm' : 'btn-outline-primary' }}"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalUserEvaluasiLhu{{ $suket->id }}"
                                    title="Buka Dokumen LHU & Hasil Evaluasi"
                                >
                                    <i class="bi bi-file-earmark-check me-1"></i>
                                    @if($suket->isEvaluasiRejected())
                                        Lihat Hasil Evaluasi ({{ $suket->pemohonComments ? $suket->pemohonComments->count() : 0 }})
                                    @else
                                        Evaluasi Dokumen LHU
                                    @endif
                                </button>

                                @if($suket->isEvaluasiRejected())
                                    <button 
                                        type="button" 
                                        class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark shadow-sm"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalRevisiPemohon{{ $suket->id }}"
                                        title="Kirim Catatan Tanggapan / Berkas Revisi"
                                    >
                                        <i class="bi bi-pencil-square me-1"></i>Kirim Revisi
                                    </button>
                                @endif

                                {{-- GATE KEAMANAN TAHAP 9: HANYA TAMPIL JIKA SUKET SUDAH DISERAHKAN (sent_to_customer_at) --}}
                                @if($isDelivered && ($suket->signed_file_path || $suket->draft_file_path))
                                    <button 
                                        type="button" 
                                        class="btn btn-success btn-sm rounded-pill px-3 fw-semibold shadow-sm"
                                        onclick="openUserPreview('{{ route('user.suket.preview-doc', [$suket->id, 'signed']) }}', 'Surat Keterangan K3 Resmi - {{ $suket->nomor_order }}')"
                                    >
                                        <i class="bi bi-eye-fill me-1"></i>Lihat Suket Resmi
                                    </button>
                                    <a 
                                        href="{{ route('user.suket.download-doc', [$suket->id, 'signed']) }}" 
                                        class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold"
                                        title="Unduh Berkas Suket Resmi"
                                        download
                                    >
                                        <i class="bi bi-download me-1"></i>Unduh Suket
                                    </a>
                                @elseif($stageNumber === 6)
                                    @if($suket->isTagihanSent() && !$suket->isTagihanAcc())
                                        <form id="formQuickAccTagihan{{ $suket->id }}" action="{{ route('user.suket.acc-tagihan', $suket->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-sm" onclick="confirmAccTagihan('{{ $suket->id }}', '{{ $suket->nomor_order }}', '{{ $suket->surat_tagihan_nominal ? number_format($suket->surat_tagihan_nominal, 0, ',', '.') : '-' }}', '{{ addslashes($suket->perusahaan_nama) }}')">
                                                <i class="bi bi-check-circle-fill me-1"></i>ACC Tagihan
                                            </button>
                                        </form>
                                    @elseif($suket->isTagihanAcc())
                                        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" disabled>
                                            <i class="bi bi-check-circle me-1"></i>Tagihan Di-ACC
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="Surat Tagihan sedang diproses oleh petugas keuangan">
                                            <i class="bi bi-hourglass-split me-1 text-primary"></i>Menunggu Tagihan
                                        </button>
                                    @endif
                                @elseif($stageNumber === 7)
                                    @if($suket->isBillingSent())
                                        @if(!$suket->isBillingPaid() || $suket->isBillingProofRejected())
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-warning rounded-pill px-3 fw-semibold shadow-sm text-dark"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalUploadBukti{{ $suket->id }}"
                                            >
                                                <i class="bi bi-upload me-1"></i>Upload Bukti Bayar
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3" disabled>
                                                <i class="bi bi-clock-history me-1"></i>Menunggu Verifikasi
                                            </button>
                                        @endif
                                    @else
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="Kode Billing sedang disiapkan oleh bendahara">
                                            <i class="bi bi-hourglass-split me-1 text-primary"></i>Menunggu Billing
                                        </button>
                                    @endif
                                @elseif($stageNumber === 8)
                                    @if($suket->isKuitansiSent())
                                        <div class="d-flex align-items-center gap-1">
                                            <a 
                                                href="{{ route('user.suket.preview-doc', [$suket->id, 'kuitansi']) }}" 
                                                target="_blank" 
                                                class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold shadow-sm"
                                            >
                                                <i class="bi bi-eye me-1"></i>Lihat Kuitansi
                                            </a>
                                            <a 
                                                href="{{ route('user.suket.download-doc', [$suket->id, 'kuitansi']) }}" 
                                                class="btn btn-sm btn-info rounded-pill px-3 text-white fw-semibold shadow-sm"
                                                download
                                            >
                                                <i class="bi bi-download me-1"></i>Unduh
                                            </a>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled title="Kuitansi resmi sedang disiapkan oleh bendahara">
                                            <i class="bi bi-hourglass-split me-1 text-primary"></i>Menunggu Kuitansi
                                        </button>
                                    @endif
                                @else
                                    {{-- JIKA BELUM DISERAHKAN: INDIKATOR DALAM PROSES --}}
                                    <button 
                                        type="button" 
                                        class="btn btn-outline-secondary btn-sm rounded-pill px-3" 
                                        disabled
                                        title="Dokumen Suket resmi hanya dapat dibuka setelah proses penyerahan tuntas."
                                    >
                                        <i class="bi bi-hourglass-split me-1 text-primary"></i>Sedang Diproses
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Alert Box Informasi Status Alur & Catatan Evaluator --}}
                    @if($suket->isEvaluasiRejected())
                        <div class="alert alert-danger border-0 rounded-3 p-3 mt-3 mb-0">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2 pb-2 border-bottom border-danger-subtle">
                                <div class="fw-bold d-flex align-items-center gap-2 text-danger fs-6 mb-0">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Hasil Evaluasi LHU Memerlukan Perbaikan / Revisi
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-danger text-white rounded-pill px-2 py-1 small">
                                        {{ $suket->pemohonComments ? $suket->pemohonComments->count() : 0 }} Poin Sorotan Kesalahan
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-danger bg-white rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalUserEvaluasiLhu{{ $suket->id }}">
                                        <i class="bi bi-eye-fill me-1"></i>Lihat Sorotan Dokumen
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalRevisiPemohon{{ $suket->id }}">
                                        <i class="bi bi-pencil-square me-1"></i>Kirim Tanggapan / Revisi
                                    </button>
                                </div>
                            </div>

                            @if($suket->catatan_evaluasi)
                                <div class="p-2 bg-white bg-opacity-75 rounded border border-danger-subtle mb-3 text-dark small">
                                    <strong>Kesimpulan Penguji K3:</strong> {{ $suket->catatan_evaluasi }}
                                </div>
                            @endif

                            @if($suket->catatan_revisi_pemohon)
                                <div class="p-2 bg-warning bg-opacity-15 rounded border border-warning-subtle mb-3 text-dark small">
                                    <strong class="text-warning-emphasis"><i class="bi bi-chat-left-quote-fill me-1"></i>Penjelasan / Tanggapan Terakhir yang Anda Kirim:</strong>
                                    <div class="mt-1" style="white-space: pre-wrap;">{{ $suket->catatan_revisi_pemohon }}</div>
                                </div>
                            @endif

                            {{-- DAFTAR HIGHLIGHT / SOROTAN KESALAHAN LHU DARI PENGUJI --}}
                            @if($suket->pemohonComments && $suket->pemohonComments->count() > 0)
                                <div class="mb-1">
                                    <div class="small fw-bold text-dark mb-2">
                                        <i class="bi bi-highlighter text-danger me-1"></i>Daftar Bagian LHU yang Disorot Salah oleh Penguji K3:
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($suket->pemohonComments as $cm)
                                            <div class="card border rounded-3 p-3 bg-white shadow-xs border-danger-subtle" id="user-comment-card-{{ $suket->id }}-{{ $cm->id }}">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="badge bg-danger text-white" style="font-size: 10px;">
                                                            Sorotan #{{ $loop->iteration }}
                                                        </span>
                                                        @if($cm->bagian)
                                                            <span class="badge bg-light text-dark border" style="font-size: 11px;">
                                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $cm->bagian }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0" style="font-size: 10.5px;" data-bs-toggle="modal" data-bs-target="#modalUserEvaluasiLhu{{ $suket->id }}" onclick="setTimeout(() => window.LhuAnnotator.scrollToHighlight({{ $suket->id }}, {{ $cm->id }}, '{{ addslashes($cm->bagian ?? '') }}'), 400)">
                                                            <i class="bi bi-geo-alt-fill me-1"></i>Tunjukkan di Dokumen
                                                        </button>
                                                        <span class="text-muted" style="font-size: 10px;">
                                                            {{ $cm->created_at ? $cm->created_at->diffForHumans() : '' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                @if($cm->highlight_text)
                                                    <div class="p-2 rounded bg-warning bg-opacity-25 border border-warning my-2">
                                                        <div class="text-muted small fw-bold" style="font-size: 10px; text-transform: uppercase;">
                                                            <i class="bi bi-highlighter text-warning-emphasis me-1"></i>Bagian / Data yang Salah:
                                                        </div>
                                                        <div class="font-monospace small text-dark fw-bold mt-1">
                                                            <mark class="bg-warning text-dark px-1 rounded">{{ $cm->highlight_text }}</mark>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="small text-dark mt-1">
                                                    <strong class="text-danger"><i class="bi bi-arrow-right-circle-fill me-1"></i>Instruksi Perbaikan:</strong>
                                                    <div class="mt-1 text-muted" style="white-space: pre-wrap;">{{ $cm->comment }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="mt-3 pt-2 border-top border-danger-subtle d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <span class="small text-muted"><i class="bi bi-info-circle me-1"></i>Silakan telaah poin catatan di atas atau buka dokumen LHU, lalu kirimkan jawaban atau berkas perbaikan.</span>
                                <button type="button" class="btn btn-sm btn-danger rounded-pill px-4 py-1 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRevisiPemohon{{ $suket->id }}">
                                    <i class="bi bi-send-check me-1"></i>Kirim Revisi / Tanggapan Sekarang
                                </button>
                            </div>
                        </div>
                    @elseif($isDelivered)
                        <div class="alert alert-success border-0 rounded-3 p-2 px-3 mt-3 mb-0 small d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-check-circle-fill text-success me-1"></i>
                                Dokumen Surat Keterangan K3 resmi telah diterbitkan dan diserahkan pada <strong>{{ \Carbon\Carbon::parse($suket->sent_to_customer_at)->locale('id')->isoFormat('D MMMM Y') }}</strong>.
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light border rounded-3 p-2 px-3 mt-3 mb-0 small text-muted d-flex align-items-center">
                            <i class="bi bi-info-circle text-navy me-2 fs-6"></i>
                            <div>
                                @if($stageNumber === 2)
                                    Permohonan Anda saat ini sedang dalam <strong>Tahap 2: Evaluasi Dokumen LHU</strong> oleh tim Penguji K3 Lingkungan Kerja Balai K3 Surabaya.
                                @elseif($stageNumber === 3)
                                    Permohonan Anda saat ini dalam <strong>Tahap 3: Penyusunan Suket</strong>. 
                                    @if($suket->evaluasi_status === 'approved' && $suket->catatan_evaluasi)
                                        <div class="mt-1 small text-success"><i class="bi bi-check2-circle me-1"></i><strong>Hasil Evaluasi Dokumen LHU:</strong> Disetujui ({{ $suket->catatan_evaluasi }})</div>
                                    @endif
                                    @if($suket->nomor_surat)
                                        <div class="mt-1">Nomor Surat Keterangan resmi telah disematkan (<strong>{{ $suket->nomor_surat }}</strong>) dan sedang melalui proses review Quality Control (QC).</div>
                                    @else
                                        <div class="mt-1">Draf surat keterangan sedang disusun sesuai standar Permenaker No. 5/2018.</div>
                                    @endif
                                @elseif($stageNumber === 4)
                                    Permohonan Anda berada pada <strong>Tahap 4: Penandatanganan Suket</strong>. Dokumen Surat Keterangan resmi (No: <strong>{{ $suket->nomor_surat }}</strong>) sedang dalam proses penandatanganan dan pengesahan oleh Kepala Balai K3 Surabaya.
                                @elseif($stageNumber === 5)
                                    Permohonan Anda berada pada <strong>Tahap 5: Penerbitan Suket</strong>. Dokumen resmi telah disahkan oleh Kepala Balai dan sedang dalam proses finalisasi penerbitan surat sebelum surat tagihan diterbitkan.
                                @elseif($stageNumber === 6)
                                    @if($suket->isTagihanSent())
                                        Permohonan Anda berada pada <strong>Tahap 6: Surat Tagihan</strong>. Silakan periksa rincian tagihan di bawah dan klik tombol <strong>ACC Surat Tagihan</strong> untuk melanjutkan ke penerbitan Kode Billing SIMPONI.
                                    @else
                                        Permohonan Anda berada pada <strong>Tahap 6: Surat Tagihan</strong>. Surat Tagihan PNBP resmi sedang disiapkan oleh Petugas Keuangan/Bendahara Balai K3. Anda dapat memeriksa berkas tagihan dan melakukan konfirmasi ACC setelah tagihan diterbitkan.
                                    @endif
                                @elseif($stageNumber === 7)
                                    @if($suket->isBillingSent())
                                        Permohonan Anda berada pada <strong>Tahap 7: Kode Billing</strong>. Silakan lakukan pembayaran PNBP menggunakan Kode Billing SIMPONI resmi sebelum batas waktu kedaluwarsa, lalu unggah bukti pembayaran di bawah.
                                    @else
                                        Permohonan Anda berada pada <strong>Tahap 7: Kode Billing</strong>. Kode Billing SIMPONI dan panduan pembayaran sedang disiapkan oleh Bendahara Balai K3. Anda dapat melakukan pembayaran dan mengunggah bukti bayar setelah kode billing diterbitkan.
                                    @endif
                                @elseif($stageNumber === 8)
                                    @if($suket->isKuitansiSent())
                                        Permohonan Anda berada pada <strong>Tahap 8: Kuitansi</strong>. Pembayaran Anda telah terverifikasi dan kuitansi lunas telah diterbitkan. Berkas sedang dalam persiapan penyerahan digital.
                                    @else
                                        Permohonan Anda berada pada <strong>Tahap 8: Kuitansi</strong>. Pembayaran Anda telah diverifikasi oleh Bendahara. Kuitansi resmi ber-watermark LUNAS sedang disiapkan sebelum diteruskan ke akun Anda.
                                    @endif
                                @elseif($stageNumber === 9)
                                    Permohonan Anda berada pada <strong>Tahap 9: Penyerahan Suket</strong>. @if($isDelivered) Surat Keterangan K3 resmi Anda telah diserahkan dan dapat diunduh di atas. @else Berkas sedang diserahkan oleh Administrator ke portal akun Anda. @endif
                                @else
                                    Permohonan Anda saat ini berada pada <strong>Tahap {{ $stageNumber }}: {{ $labelTahap }}</strong>. Berkas resmi Suket akan otomatis dapat dilihat dan diunduh di sini setelah tuntas diserahkan oleh tim Balai K3.
                                @endif
                            </div>
                        </div>

                        {{-- INTERACTIVE CARD TAHAP 6: SURAT TAGIHAN DENGAN DESAIN MODERN --}}
                        @if($stageNumber === 6 || $suket->isTagihanSent())
                            @if($suket->isTagihanAcc())
                                <div class="card border-0 rounded-4 p-3 mt-3 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-left: 5px solid #10b981 !important;">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle shadow-xs" style="width: 32px; height: 32px; font-size: 1.1rem;">
                                                <i class="bi bi-check2-circle"></i>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-success-emphasis">Surat Tagihan Suket K3 (Telah Disetujui)</div>
                                                <small class="text-success text-opacity-75">Konfirmasi persetujuan resmi telah Anda berikan</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-success bg-opacity-90 text-white px-3 py-1 rounded-pill small shadow-xs">
                                            <i class="bi bi-check-circle-fill me-1"></i> Telah Di-ACC
                                        </span>
                                    </div>
                                    <div class="row g-2 p-2 rounded-3 bg-white bg-opacity-65 border border-success border-opacity-20 small text-dark mb-2">
                                        <div class="col-sm-6">
                                            <span class="text-muted"><i class="bi bi-cash-stack text-success me-1"></i> Total Tagihan Resmi:</span><br>
                                            <strong class="fs-6 text-success-emphasis">{{ $suket->surat_tagihan_nominal ? ('Rp ' . number_format($suket->surat_tagihan_nominal, 0, ',', '.')) : 'Sesuai tarif PNBP resmi' }}</strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted"><i class="bi bi-calendar-check text-success me-1"></i> Waktu Persetujuan (ACC):</span><br>
                                            <strong>{{ \Carbon\Carbon::parse($suket->surat_tagihan_acc_at)->format('d F Y, H:i') }} WIB</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-1">
                                        @if($suket->isTagihanSent())
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <a href="{{ route('user.suket.preview-doc', [$suket->id, 'tagihan']) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                                    <i class="bi bi-eye me-1"></i> Lihat PDF Tagihan
                                                </a>
                                                <a href="{{ route('user.suket.download-doc', [$suket->id, 'tagihan']) }}" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" download>
                                                    <i class="bi bi-file-earmark-pdf me-1"></i> Unduh Berkas (.pdf)
                                                </a>
                                            </div>
                                        @endif
                                        <span class="text-muted small ms-auto d-flex align-items-center gap-1">
                                            <i class="bi bi-arrow-right-circle text-success fs-6"></i> Permohonan otomatis beralih ke <strong>Tahap 7 (Kode Billing SIMPONI)</strong>
                                        </span>
                                    </div>
                                </div>
                            @elseif($suket->isTagihanSent())
                                <div class="card border-0 rounded-4 p-3 mt-3 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #f0f7ff 0%, #e0effe 100%); border-left: 5px solid #2563eb !important;">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-xs" style="width: 34px; height: 34px; font-size: 1.1rem;">
                                                <i class="bi bi-envelope-paper-fill"></i>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-primary-emphasis">Surat Tagihan Suket K3 Menunggu Persetujuan Anda</div>
                                                <small class="text-primary text-opacity-75">Silakan periksa berkas rincian tagihan dan konfirmasi persetujuan (ACC)</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill small fw-semibold shadow-xs">
                                            <i class="bi bi-hourglass-split me-1"></i> Menunggu Konfirmasi ACC Anda
                                        </span>
                                    </div>
                                    <div class="row g-2 p-2 rounded-3 bg-white bg-opacity-75 border border-primary border-opacity-20 small text-dark mb-2">
                                        <div class="col-sm-6">
                                            <span class="text-muted"><i class="bi bi-cash-stack text-primary me-1"></i> Total Tagihan Resmi PNBP:</span><br>
                                            <strong class="fs-5 text-primary">{{ $suket->surat_tagihan_nominal ? ('Rp ' . number_format($suket->surat_tagihan_nominal, 0, ',', '.')) : 'Sesuai penetapan resmi' }}</strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted"><i class="bi bi-clock-history text-primary me-1"></i> Tanggal Diterbitkan Petugas:</span><br>
                                            <strong>{{ $suket->surat_tagihan_sent_at ? \Carbon\Carbon::parse($suket->surat_tagihan_sent_at)->format('d F Y, H:i') . ' WIB' : 'Hari ini' }}</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <a href="{{ route('user.suket.preview-doc', [$suket->id, 'tagihan']) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                                <i class="bi bi-eye me-1"></i> Lihat Rincian PDF
                                            </a>
                                            <a href="{{ route('user.suket.download-doc', [$suket->id, 'tagihan']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold" download>
                                                <i class="bi bi-file-earmark-pdf me-1"></i> Unduh (.pdf)
                                            </a>
                                        </div>
                                        <form id="formAccTagihan{{ $suket->id }}" action="{{ route('user.suket.acc-tagihan', $suket->id) }}" method="POST" class="d-inline ms-auto">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-4 shadow-sm fw-semibold" onclick="confirmAccTagihan('{{ $suket->id }}', '{{ $suket->nomor_order }}', '{{ $suket->surat_tagihan_nominal ? number_format($suket->surat_tagihan_nominal, 0, ',', '.') : '-' }}', '{{ addslashes($suket->perusahaan_nama) }}')">
                                                <i class="bi bi-check-circle-fill me-1"></i> ACC Surat Tagihan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @else
                                <div class="card border-0 rounded-4 p-3 mt-3 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 5px solid #94a3b8 !important;">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center bg-secondary text-white rounded-circle shadow-xs" style="width: 34px; height: 34px; font-size: 1.1rem;">
                                                <i class="bi bi-envelope-paper"></i>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark">Surat Tagihan Sedang Disiapkan oleh Petugas Keuangan</div>
                                                <small class="text-muted">Rincian penetapan tarif resmi PNBP sedang diverifikasi</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary border px-3 py-1 rounded-pill small fw-semibold">
                                            <i class="bi bi-hourglass-split me-1"></i> Menunggu Penerbitan Tagihan
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        <i class="bi bi-info-circle me-1 text-primary"></i> Petugas Keuangan / Bendahara Penerimaan Balai K3 sedang menyusun Surat Tagihan resmi untuk permohonan ini. Tombol persetujuan (ACC) dan berkas tagihan akan otomatis tersedia begitu tagihan dikirimkan ke akun Anda.
                                    </p>
                                </div>
                            @endif
                        @endif

                        {{-- INTERACTIVE CARD TAHAP 7: KODE BILLING --}}
                        @if($stageNumber === 7 || $suket->isBillingSent())
                            @if(!$suket->isBillingSent())
                                <div class="card border-0 rounded-4 p-3 mt-3 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 5px solid #f59e0b !important;">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center bg-warning text-dark rounded-circle shadow-xs" style="width: 34px; height: 34px; font-size: 1.1rem;">
                                                <i class="bi bi-upc"></i>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark">Kode Billing SIMPONI Sedang Disiapkan</div>
                                                <small class="text-muted">Menunggu penerbitan kode billing & panduan bayar oleh Bendahara</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill small fw-semibold">
                                            <i class="bi bi-clock-history me-1"></i> Menunggu Penerbitan Billing
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        <i class="bi bi-info-circle me-1 text-warning"></i> Surat Tagihan telah disetujui. Saat ini Bendahara Balai K3 sedang memproses pembuatan Kode Billing SIMPONI dan panduan pembayaran. Anda dapat mengunduh berkas kode billing dan mengunggah bukti pembayaran setelah billing diterbitkan oleh Bendahara.
                                    </p>
                                </div>
                            @else
                                <div class="card border-warning border-opacity-50 bg-warning bg-opacity-10 rounded-3 p-3 mt-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="fw-bold text-dark">
                                            <i class="bi bi-upc me-1"></i> Kode Billing SIMPONI / PNBP
                                        </div>
                                        @if($suket->isBillingProofRejected())
                                            <span class="badge bg-danger rounded-pill">
                                                <i class="bi bi-exclamation-octagon me-1"></i> Bukti Ditolak
                                            </span>
                                        @elseif($suket->isBillingVerified())
                                            <span class="badge bg-success rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i> Pembayaran Terverifikasi
                                            </span>
                                        @elseif($suket->isBillingProofPending())
                                            <span class="badge bg-info text-white rounded-pill">
                                                <i class="bi bi-clock-history me-1"></i> Menunggu Verifikasi Bendahara
                                            </span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill">
                                                <i class="bi bi-hourglass-split me-1"></i> Menunggu Pembayaran
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Notifikasi Penolakan Bukti jika ada --}}
                                    @if($suket->isBillingProofRejected())
                                        <div class="alert alert-danger border-danger-subtle rounded-3 p-3 mb-3 small">
                                            <div class="fw-bold text-danger mb-1">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Bukti Pembayaran Anda Ditolak oleh Bendahara/Admin
                                            </div>
                                            <div>Alasan Penolakan: <strong>{{ $suket->billing_proof_reject_note }}</strong></div>
                                            <div class="mt-1 text-muted">
                                                Silakan periksa kembali bukti transfer Anda dan klik tombol <strong>"Upload Ulang Bukti Pembayaran"</strong> di bawah untuk mengirimkan bukti yang benar.
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row g-2 mb-2 small">
                                        <div class="col-sm-6">
                                            <span class="text-muted">Kode Billing:</span><br>
                                            <strong class="fs-5 text-dark font-monospace">{{ $suket->billing_kode ?: 'Sedang Diproses Bendahara' }}</strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="text-muted">Batas Waktu Pembayaran:</span><br>
                                            <strong class="text-danger">{{ $suket->billing_expires_at ? \Carbon\Carbon::parse($suket->billing_expires_at)->format('d M Y, H:i') : 'Sesuai masa aktif billing' }}</strong>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        @if($suket->billing_file_path)
                                            <a href="{{ route('user.suket.download-doc', [$suket->id, 'billing']) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3" download>
                                                <i class="bi bi-file-earmark-pdf me-1"></i> Unduh Salinan Billing
                                            </a>
                                        @endif

                                        @if($suket->hasBillingGuide())
                                            <a href="{{ route('user.suket.download-doc', [$suket->id, 'guide']) }}" class="btn btn-sm btn-outline-info rounded-pill px-3" target="_blank" download>
                                                <i class="bi bi-book me-1"></i> Unduh Panduan Pembayaran
                                            </a>
                                        @endif

                                        @if($suket->isBillingProofRejected())
                                            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalUploadBukti{{ $suket->id }}">
                                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Ulang Bukti Pembayaran
                                            </button>
                                        @elseif(!$suket->isBillingPaid())
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUploadBukti{{ $suket->id }}">
                                                <i class="bi bi-upload me-1"></i> Upload Bukti Pembayaran
                                            </button>
                                        @elseif($suket->isBillingProofPending())
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-2 rounded-pill small">
                                                <i class="bi bi-clock-history me-1"></i> Bukti terunggah ({{ $suket->billing_proof_name ?? 'Bukti_Bayar' }}) &bull; Menunggu verifikasi bendahara
                                            </span>
                                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#modalUploadBukti{{ $suket->id }}" title="Upload Ulang Bukti Bayar">
                                                <i class="bi bi-pencil me-1"></i> Ganti Bukti
                                            </button>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
                                                <i class="bi bi-check2-circle me-1"></i> Bukti bayar terverifikasi sah
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{-- INTERACTIVE CARD TAHAP 8: KUITANSI --}}
                        @if($stageNumber >= 8)
                            @if($suket->isKuitansiSent())
                                <div class="card border-info border-opacity-50 bg-info bg-opacity-10 rounded-3 p-3 mt-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="fw-bold text-navy">
                                            <i class="bi bi-receipt me-1"></i> Kuitansi Lunas Resmi
                                        </div>
                                        <span class="badge bg-success rounded-pill">
                                            <i class="bi bi-patch-check-fill me-1"></i> Lunas
                                        </span>
                                    </div>
                                    <div class="small text-dark mb-2">
                                        Nomor Kuitansi: <strong class="text-navy">{{ $suket->kuitansi_nomor ?: '-' }}</strong> &bull;
                                        Tanggal: {{ $suket->kuitansi_sent_at ? \Carbon\Carbon::parse($suket->kuitansi_sent_at)->format('d M Y') : ($suket->billing_verified_at ? \Carbon\Carbon::parse($suket->billing_verified_at)->format('d M Y') : 'Hari ini') }}
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <a href="{{ route('user.suket.preview-doc', [$suket->id, 'kuitansi']) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-xs">
                                            <i class="bi bi-eye me-1"></i> Lihat Kuitansi PDF
                                        </a>
                                        <a href="{{ route('user.suket.download-doc', [$suket->id, 'kuitansi']) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" download>
                                            <i class="bi bi-download me-1"></i> Unduh Berkas Kuitansi Resmi
                                        </a>
                                    </div>
                                </div>
                            @elseif($stageNumber === 8)
                                <div class="card border-0 rounded-4 p-3 mt-3 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 5px solid #06b6d4 !important;">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex align-items-center justify-content-center bg-info text-white rounded-circle shadow-xs" style="width: 34px; height: 34px; font-size: 1.1rem;">
                                                <i class="bi bi-receipt"></i>
                                            </span>
                                            <div>
                                                <div class="fw-bold text-dark">Kuitansi Resmi Sedang Diproses</div>
                                                <small class="text-muted">Pembayaran telah terverifikasi sah oleh Bendahara</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-info-subtle text-info border px-3 py-1 rounded-pill small fw-semibold">
                                            <i class="bi bi-clock-history me-1"></i> Sedang Diproses Bendahara
                                        </span>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        <i class="bi bi-info-circle me-1 text-info"></i> Pembayaran Anda telah terverifikasi sah. Kuitansi resmi ber-watermark LUNAS sedang disiapkan oleh Bendahara Balai K3 dan akan segera diteruskan ke akun Anda sebelum penyerahan digital Surat Keterangan K3.
                                    </p>
                                </div>
                            @endif
                        @endif

                        {{-- MODAL UPLOAD BUKTI BAYAR TAHAP 7 --}}
                        <div class="modal fade modal-upload-bukti-user" id="modalUploadBukti{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <form action="{{ route('user.suket.upload-payment-proof', $suket->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-dark">
                                                <i class="bi bi-upload text-primary me-2"></i>{{ $suket->isBillingProofRejected() ? 'Upload Ulang Bukti Pembayaran' : 'Upload Bukti Pembayaran SIMPONI' }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-3">
                                            @if($suket->isBillingProofRejected())
                                                <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                                                    <i class="bi bi-exclamation-triangle me-1"></i> <strong>Catatan Penolakan Sebelumnya:</strong><br>
                                                    {{ $suket->billing_proof_reject_note }}
                                                </div>
                                            @endif
                                            <p class="small text-muted mb-3">
                                                Unggah bukti setoran / transaksi pembayaran NTPN SIMPONI untuk nomor order <strong>{{ $suket->nomor_order }}</strong>.
                                            </p>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold text-dark">Pilih Berkas Bukti Pembayaran (PDF / JPG / PNG, Maks 10MB) <span class="text-danger">*</span></label>
                                                <input type="file" name="payment_proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-dark">Catatan / Keterangan Pembayaran (Opsional)</label>
                                                <textarea name="catatan" rows="2" class="form-control form-control-sm" placeholder="Contoh: Pembayaran melalui teller Bank Mandiri NTPN: ..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                <i class="bi bi-cloud-arrow-up me-1"></i> Kirim Bukti Pembayaran
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- MODAL EVALUASI DOKUMEN SIDE-BY-SIDE (SISI USER / PEMOHON) --}}
            {{-- MODAL EVALUASI DOKUMEN SIDE-BY-SIDE (SISI USER / PEMOHON) --}}
            <div class="modal fade text-start modal-evaluasi-lhu-user" id="modalUserEvaluasiLhu{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 95vw;">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="height: 90vh;">
                        <div class="modal-header py-2 px-3 bg-light border-bottom">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-primary px-2 py-1 rounded-pill">
                                    <i class="bi bi-file-earmark-check me-1"></i>Hasil Evaluasi Dokumen LHU (Tahap 2)
                                </span>
                                <h6 class="modal-title fw-bold text-dark mb-0">
                                    {{ $suket->nomor_order }} - {{ $suket->perusahaan_nama }}
                                </h6>
                                @if($suket->isEvaluasiRejected())
                                    <span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle me-1"></i>Evaluasi Perlu Revisi</span>
                                @elseif($suket->evaluasi_status === 'approved')
                                    <span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Telah Disetujui</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-clock me-1"></i>Dalam Proses Telaah</span>
                                @endif
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($suket->hasLhuDocument())
                                    <a href="{{ route('user.suket.download-doc', [$suket->id, 'lhu']) }}" class="btn btn-xs btn-outline-secondary rounded px-2 py-1" download>
                                        <i class="bi bi-download me-1"></i>Unduh Berkas LHU
                                    </a>
                                @endif
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                        </div>
                        <div class="modal-body p-0" style="height: calc(90vh - 56px);">
                            <div class="row g-0 h-100">
                                {{-- SISI KIRI: DOKUMEN LHU DENGAN HIGHLIGHT KUNING & MARKER NOMOR --}}
                                <div class="col-lg-7 d-flex flex-column h-100 border-end bg-dark bg-opacity-10">
                                    <div class="d-flex justify-content-between align-items-center p-2 bg-white border-bottom flex-shrink-0">
                                        <span class="small fw-semibold text-muted">
                                            <i class="bi bi-file-earmark-pdf text-danger me-1"></i>Dokumen LHU (Disorot Bagian yang Memerlukan Revisi)
                                        </span>
                                        <span class="badge bg-warning text-dark" style="font-size: 10px;">
                                            <i class="bi bi-highlighter me-1"></i>{{ $suket->pemohonComments ? $suket->pemohonComments->count() : 0 }} Titik Sorotan
                                        </span>
                                    </div>

                                    <div class="flex-grow-1 position-relative overflow-y-auto p-3 d-flex flex-column align-items-center" id="evalUserPdfWrap{{ $suket->id }}" style="background-color: #525659; min-height: 0;">
                                        @if($suket->hasLhuDocument())
                                            <div id="evalUserPdfContainer{{ $suket->id }}" class="w-100 d-flex flex-column align-items-center"></div>
                                        @else
                                            <div class="text-center p-5 text-white my-auto">
                                                <i class="bi bi-file-earmark-pdf fs-1 text-warning mb-3 d-block"></i>
                                                <h6 class="fw-bold">Dokumen LHU Sedang Dipersiapkan</h6>
                                                <p class="small text-white-50 mb-0">Berkas LHU belum diunggah atau masih dalam proses oleh tim Balai K3.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- SISI KANAN: PANEL CATATAN EVALUASI & INSTRUKSI REVISI --}}
                                <div class="col-lg-5 d-flex flex-column h-100 bg-white">
                                    <div class="p-3 border-bottom bg-light flex-shrink-0">
                                        <h6 class="fw-bold text-dark mb-1">
                                            <i class="bi bi-clipboard2-check text-primary me-2"></i>Catatan & Sorotan Evaluator K3
                                        </h6>
                                        <p class="small text-muted mb-0" style="font-size: 11.5px;">
                                            Tinjau bagian yang disorot kuning di sebelah kiri beserta instruksi perbaikannya di bawah ini.
                                        </p>
                                    </div>

                                    {{-- Feed Sorotan Kesalahan untuk User --}}
                                    <div class="p-3 flex-grow-1 overflow-y-auto" style="min-height: 0;">
                                        @if($suket->catatan_evaluasi)
                                            <div class="p-2 rounded bg-light border mb-3 small text-dark">
                                                <strong>Kesimpulan Penguji K3:</strong>
                                                <div class="mt-1">{{ $suket->catatan_evaluasi }}</div>
                                            </div>
                                        @endif

                                        @if($suket->pemohonComments && $suket->pemohonComments->count() > 0)
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($suket->pemohonComments as $cm)
                                                    <div class="card border rounded-3 p-3 bg-white shadow-xs border-danger-subtle" id="user-modal-comment-card-{{ $suket->id }}-{{ $cm->id }}">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                                <span class="badge bg-danger text-white" style="font-size: 10px;">
                                                                    Sorotan #{{ $loop->iteration }}
                                                                </span>
                                                                @if($cm->bagian)
                                                                    <span class="badge bg-light text-dark border" style="font-size: 10.5px;">
                                                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $cm->bagian }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0" style="font-size: 10px;" onclick="window.LhuAnnotator.scrollToHighlight({{ $suket->id }}, {{ $cm->id }}, '{{ addslashes($cm->bagian ?? '') }}')">
                                                                <i class="bi bi-geo-alt-fill me-1"></i>Tunjukkan di Dokumen
                                                            </button>
                                                        </div>

                                                        @if($cm->highlight_text)
                                                            <div class="p-2 rounded bg-warning bg-opacity-25 border border-warning my-2">
                                                                <div class="text-muted small fw-bold" style="font-size: 10px; text-transform: uppercase;">
                                                                    <i class="bi bi-highlighter text-warning-emphasis me-1"></i>Bagian / Data yang Salah:
                                                                </div>
                                                                <div class="font-monospace small text-dark fw-bold mt-1">
                                                                    <mark class="bg-warning text-dark px-1 rounded">{{ $cm->highlight_text }}</mark>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="small text-dark mt-1">
                                                            <strong class="text-danger"><i class="bi bi-arrow-right-circle-fill me-1"></i>Instruksi Perbaikan:</strong>
                                                            <div class="mt-1 text-muted" style="white-space: pre-wrap;">{{ $cm->comment }}</div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-5 text-muted">
                                                <i class="bi bi-check-circle fs-2 text-success mb-2 d-block"></i>
                                                <p class="small mb-0">Tidak ada catatan kesalahan spesifik pada berkas LHU.</p>
                                            </div>
                                        @endif
                                    </div>

                                    @if($suket->isEvaluasiRejected())
                                        <div class="p-3 border-top bg-light flex-shrink-0">
                                            <button type="button" class="btn btn-warning w-100 rounded-pill fw-bold text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRevisiPemohon{{ $suket->id }}">
                                                <i class="bi bi-pencil-square me-1"></i>Kirim Tanggapan / Dokumen Revisi Pemohon
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL REVISI / TANGGAPAN PEMOHON --}}
            @if($suket->isEvaluasiRejected())
            <div class="modal fade text-start" id="modalRevisiPemohon{{ $suket->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <form action="{{ route('user.suket.submit-revision', $suket->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-header bg-warning bg-opacity-10 border-bottom py-3 px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">
                                        <i class="bi bi-pencil-square me-1"></i>Tanggapan & Revisi Pemohon
                                    </span>
                                    <h6 class="modal-title fw-bold text-dark mb-0">
                                        Revisi Order {{ $suket->nomor_order }} - {{ $suket->perusahaan_nama }}
                                    </h6>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4">
                                {{-- Catatan Evaluasi Penguji Sebagai Referensi --}}
                                @if($suket->catatan_evaluasi)
                                    <div class="alert alert-danger bg-danger-subtle border-danger-subtle rounded-3 p-3 mb-3 text-dark small">
                                        <div class="fw-bold text-danger mb-1">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Instruksi Permintaan Revisi dari Penguji K3:
                                        </div>
                                        <div>{{ $suket->catatan_evaluasi }}</div>
                                    </div>
                                @endif

                                {{-- DAFTAR TITIK SOROTAN KESALAHAN LHU / SCAN DARI PENGUJI K3 --}}
                                @if($suket->pemohonComments && $suket->pemohonComments->count() > 0)
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="small fw-bold text-dark">
                                                <i class="bi bi-highlighter text-danger me-1"></i>Daftar Poin Sorotan Kesalahan LHU ({{ $suket->pemohonComments->count() }} Poin):
                                            </div>
                                            <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#modalUserEvaluasiLhu{{ $suket->id }}">
                                                <i class="bi bi-eye-fill me-1"></i>Buka & Lihat di Dokumen LHU
                                            </button>
                                        </div>
                                        <div class="d-flex flex-column gap-2 overflow-y-auto pe-1" style="max-height: 220px;">
                                            @foreach($suket->pemohonComments as $cm)
                                                <div class="card border rounded-3 p-2 px-3 bg-white shadow-xs border-danger-subtle">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <div class="d-flex align-items-center gap-1 flex-wrap">
                                                            <span class="badge bg-danger text-white" style="font-size: 9.5px;">
                                                                Sorotan #{{ $loop->iteration }}
                                                            </span>
                                                            @if($cm->bagian)
                                                                <span class="badge bg-light text-dark border" style="font-size: 10px;">
                                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $cm->bagian }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <span class="text-muted" style="font-size: 10px;">
                                                            {{ $cm->created_at ? $cm->created_at->diffForHumans() : '' }}
                                                        </span>
                                                    </div>

                                                    @if($cm->highlight_text)
                                                        <div class="p-1 px-2 rounded bg-warning bg-opacity-25 border border-warning my-1 small">
                                                            <span class="text-muted fw-bold" style="font-size: 10px; text-transform: uppercase;">
                                                                <i class="bi bi-highlighter text-warning-emphasis me-1"></i>Data/Area Disorot:
                                                            </span>
                                                            @if(str_starts_with($cm->highlight_text, '[BOX:'))
                                                                <span class="badge bg-warning text-dark fw-bold ms-1" style="font-size: 10px;">
                                                                    <i class="bi bi-bounding-box-circles me-1"></i>Kotak Area Gambar/Scan LHU
                                                                </span>
                                                            @else
                                                                <span class="font-monospace fw-bold text-dark ms-1">"{{ $cm->highlight_text }}"</span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="small text-dark mt-1">
                                                        <strong class="text-danger" style="font-size: 11px;"><i class="bi bi-arrow-right-circle-fill me-1"></i>Instruksi:</strong>
                                                        <span class="text-secondary" style="font-size: 11.5px;">{{ $cm->comment }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark small">
                                        Penjelasan / Tanggapan Perbaikan dari Pemohon <span class="text-danger">*</span>
                                    </label>
                                    <textarea 
                                        name="catatan_revisi" 
                                        rows="4" 
                                        class="form-control" 
                                        placeholder="Tuliskan penjelasan mengenai bagian yang telah diperbaiki, jawaban atas pertanyaan evaluator K3, atau keterangan berkas baru yang Anda unggah..." 
                                        required
                                    >{{ old('catatan_revisi', $suket->catatan_revisi_pemohon) }}</textarea>
                                    <div class="form-text small text-muted">
                                        Jelaskan secara rinci tindakan perbaikan yang telah Anda lakukan sesuai poin sorotan evaluator.
                                    </div>
                                </div>

                                <div class="p-3 bg-light rounded-3 border mb-2">
                                    <div class="fw-bold text-dark small mb-1">
                                        <i class="bi bi-paperclip me-1 text-primary"></i>Unggah Berkas Pembaharuan / Dokumen Pengganti (Opsional)
                                    </div>
                                    <div class="text-muted small mb-3">
                                        Hanya unggah berkas berikut jika ada perubahan dokumen LHU, denah lokasi, atau foto pengujian.
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label small fw-semibold text-dark mb-1">
                                                Unggah File LHU Baru (PDF) <span class="text-muted fw-normal">(Opsional)</span>
                                            </label>
                                            <input type="file" name="lhu_file" class="form-control form-control-sm" accept=".pdf">
                                            <div class="form-text small text-muted">
                                                Berkas LHU hasil perbaikan (maks. 20MB, format .pdf). Kosongkan jika tidak mengganti file LHU.
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">
                                                Unggah Denah Lokasi Baru <span class="text-muted fw-normal">(Opsional)</span>
                                            </label>
                                            <input type="file" name="denah_lokasi" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                            <div class="form-text small text-muted">
                                                Format: PDF / Gambar JPG / PNG (maks. 20MB).
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">
                                                Unggah Foto Pengujian Baru <span class="text-muted fw-normal">(Opsional)</span>
                                            </label>
                                            <input type="file" name="foto_pengujian" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                            <div class="form-text small text-muted">
                                                Format: PDF / Gambar JPG / PNG (maks. 20MB).
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer bg-light py-2 px-4 border-top d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" onclick="return confirm('Kirimkan tanggapan dan berkas revisi ke Tim Penguji K3?')">
                                    <i class="bi bi-send-check me-1"></i>Kirim Revisi ke Penguji K3
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-medical text-navy fs-1"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Belum Ada Permohonan Suket K3</h5>
                    <p class="text-muted small mx-auto mb-4" style="max-width: 480px;">
                        Anda belum memiliki riwayat pengajuan Surat Keterangan K3. Klik tombol di bawah ini untuk memulai pengajuan berdasarkan hasil pengujian nomor order Anda.
                    </p>
                    <div>
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjukanSuket">
                            <i class="bi bi-plus-circle me-1"></i> Ajukan Permohonan Suket Pertama
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($sukets->hasPages())
        <div class="mt-4 bg-white p-3 rounded-4 shadow-sm border">
            {{ $sukets->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- ========================================================================= --}}
{{-- MODAL AJUKAN PERMOHONAN SUKET BARU --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="modalAjukanSuket" tabindex="-1" aria-labelledby="modalAjukanSuketLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="{{ route('user.suket.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-navy text-white p-3 px-4 flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-plus-fill fs-4 text-warning"></i>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-white" id="modalAjukanSuketLabel">Form Pengajuan Suket K3 Baru</h6>
                        <small class="text-white-50" style="font-size: 11px;">Permenaker No. 5 Tahun 2018 &bull; Balai K3 Surabaya</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3 px-4">
                {{-- 1. PILIH NOMOR ORDER PEMOHON --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        1. Nomor Order / Kode Permohonan Anda <span class="text-danger">*</span>
                    </label>
                    <select 
                        name="nomor_order" 
                        id="user_nomor_order" 
                        class="form-select form-select-sm rounded-3 @error('nomor_order') is-invalid @enderror" 
                        required
                    >
                        <option value="" disabled {{ !old('nomor_order', request('nomor_order')) ? 'selected' : '' }}>-- Pilih Nomor Order Pengujian Anda --</option>
                        @forelse($userOrders as $ord)
                            <option 
                                value="{{ $ord['kode'] }}" 
                                {{ old('nomor_order', request('nomor_order')) === $ord['kode'] ? 'selected' : '' }}
                            >
                                {{ $ord['kode'] }} &mdash; {{ $ord['perusahaan'] }} ({{ $ord['lokasi'] }}) {{ $ord['has_lhu'] ? '[LHU Siap]' : '' }}
                            </option>
                        @empty
                            <option value="" disabled>Belum ada nomor order pada akun Anda.</option>
                        @endforelse
                    </select>
                    @error('nomor_order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text" style="font-size: 11px;">
                        Laporan Hasil Uji (LHU) resmi pada nomor order tersebut dapat ditarik secara otomatis ke dalam berkas suket.
                    </div>
                </div>

                {{-- 2. RUANG LINGKUP FAKTOR K3 --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        2. Ruang Lingkup Faktor K3 yang Diuji <span class="text-danger">*</span>
                    </label>
                    <div class="row g-2">
                        @foreach($faktorOptions as $fKey => $fDesc)
                            <div class="col-md-6">
                                <label class="d-flex align-items-center gap-2 p-2 px-3 border rounded-3 bg-light w-100 mb-0" style="cursor: pointer;">
                                    <input 
                                        class="form-check-input mt-0 flex-shrink-0" 
                                        type="checkbox" 
                                        name="faktor_k3[]" 
                                        value="{{ $fKey }}" 
                                        id="modal_f_{{ $fKey }}"
                                        @checked(is_array(old('faktor_k3')) && in_array($fKey, old('faktor_k3')))
                                    >
                                    <span class="small fw-semibold text-dark" style="font-size: 12px;">{{ $fDesc }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('faktor_k3')
                        <div class="text-danger small mt-1" style="font-size: 11px;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 3. SUMBER DOKUMEN LHU --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        3. Sumber Dokumen Laporan Hasil Uji (LHU) <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <label class="d-flex align-items-center gap-2 p-2 px-3 border rounded-3 bg-light flex-fill mb-0" style="cursor: pointer;">
                            <input 
                                class="form-check-input mt-0" 
                                type="radio" 
                                name="lhu_source" 
                                id="modal_source_auto" 
                                value="auto" 
                                checked 
                                onchange="toggleModalLhu(this.value)"
                            >
                            <span class="small fw-semibold text-dark" style="font-size: 12px;">
                                <i class="bi bi-cloud-arrow-down text-primary me-1"></i>Tarik Otomatis dari Nomor Order
                            </span>
                        </label>
                        <label class="d-flex align-items-center gap-2 p-2 px-3 border rounded-3 bg-light flex-fill mb-0" style="cursor: pointer;">
                            <input 
                                class="form-check-input mt-0" 
                                type="radio" 
                                name="lhu_source" 
                                id="modal_source_manual" 
                                value="manual" 
                                onchange="toggleModalLhu(this.value)"
                            >
                            <span class="small fw-semibold text-dark" style="font-size: 12px;">
                                <i class="bi bi-upload text-secondary me-1"></i>Upload Berkas LHU Manual (PDF)
                            </span>
                        </label>
                    </div>
                    
                    <div id="modal_manual_lhu_box" class="mt-2 p-2 px-3 border border-dashed rounded-3 bg-light" style="display: none;">
                        <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 11px;">Unggah Berkas LHU (PDF, maks 20MB)</label>
                        <input type="file" name="lhu_file" class="form-control form-control-sm" accept=".pdf">
                    </div>
                </div>

                {{-- 4. LAMPIRAN FOTO & DENAH --}}
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark mb-1">
                        4. Dokumen Pendukung Pengujian Lapangan (Opsional)
                    </label>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="p-2 px-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 12px;">
                                    <i class="bi bi-camera me-1 text-primary"></i> Foto Pengujian Lapangan
                                </label>
                                <input type="file" name="foto_pengujian" class="form-control form-control-sm" accept="image/*,.pdf">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 px-3 border rounded-3 bg-light">
                                <label class="form-label small fw-semibold text-dark mb-1" style="font-size: 12px;">
                                    <i class="bi bi-map me-1 text-warning"></i> Denah Lokasi Pengujian
                                </label>
                                <input type="file" name="denah_lokasi" class="form-control form-control-sm" accept="image/*,.pdf">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 5. CATATAN TAMBAHAN --}}
                <div class="mb-1">
                    <label class="form-label small fw-bold text-dark mb-1">5. Catatan Tambahan (Opsional)</label>
                    <textarea 
                        name="catatan" 
                        class="form-control form-control-sm rounded-3" 
                        rows="2" 
                        placeholder="Tuliskan catatan atau pesan tambahan bila diperlukan..."
                    >{{ old('catatan') }}</textarea>
                </div>
            </div>

            {{-- STICKY FOOTER: BUTTON SELALU TERLIHAT DAN TIDAK TERPOTONG --}}
            <div class="modal-footer border-top p-3 px-4 bg-white d-flex justify-content-between align-items-center flex-shrink-0">
                <span class="small text-muted" style="font-size: 11px;">
                    <span class="text-danger">*</span> Wajib diisi &bull; Otomatis diproses
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i>Kirim Permohonan Suket
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL DOCUMENT PREVIEW TANPA DOWNLOAD --}}
<div class="modal fade" id="previewDocModal" tabindex="-1" aria-labelledby="previewDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 94vw; margin: 24px auto;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="height: 86vh; max-height: calc(100vh - 48px);">
            <div class="modal-header bg-navy text-white p-3 px-4 d-flex justify-content-between align-items-center flex-shrink-0">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-3">
                    <i class="bi bi-file-earmark-text-fill fs-4 text-warning flex-shrink-0"></i>
                    <h6 class="modal-title fw-bold mb-0 text-white text-truncate" id="previewDocModalLabel">Pratinjau Dokumen</h6>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-semibold" onclick="printUserPreview()" title="Cetak Dokumen">
                        <i class="bi bi-printer me-1"></i>Cetak
                    </button>
                    <a href="#" id="previewUserDirectLink" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-semibold" title="Buka di Tab Baru">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Tab Baru
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative d-flex align-items-center justify-content-center" style="flex: 1 1 auto; background: #525659; overflow: hidden;">
                <div id="previewSpinner" class="position-absolute text-center text-white">
                    <div class="spinner-border text-light mb-2" role="status"></div>
                    <div class="small fw-semibold text-white-50">Menyiapkan pratinjau dokumen resmi...</div>
                </div>
                <iframe id="previewIframe" src="about:blank" class="w-100 h-100 border-0" style="display: none; background: #525659;" onload="onDocLoaded()"></iframe>
            </div>
            <div class="modal-footer border-top p-2 px-3 bg-light d-flex justify-content-between align-items-center flex-shrink-0">
                <span class="small text-muted" style="font-size: 11px;">
                    <i class="bi bi-shield-check me-1 text-success"></i>Pratinjau resmi portal Balai K3 Surabaya &bull; Standar Permenaker RI No. 5 Tahun 2018
                </span>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleModalLhu(val) {
        const box = document.getElementById('modal_manual_lhu_box');
        if (!box) return;
        box.style.display = (val === 'manual') ? 'block' : 'none';
    }

    function openUserPreview(url, title) {
        const modalEl = document.getElementById('previewDocModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const iframe = document.getElementById('previewIframe');
        const spinner = document.getElementById('previewSpinner');
        const label = document.getElementById('previewDocModalLabel');
        const directLink = document.getElementById('previewUserDirectLink');

        if (label) label.textContent = title || 'Pratinjau Dokumen';
        if (directLink) directLink.href = url;
        if (spinner) spinner.style.display = 'block';
        if (iframe) {
            iframe.style.display = 'none';
            iframe.src = url;
        }
        modal.show();
    }

    function onDocLoaded() {
        const spinner = document.getElementById('previewSpinner');
        const iframe = document.getElementById('previewIframe');
        if (iframe && iframe.src && !iframe.src.endsWith('about:blank')) {
            if (spinner) spinner.style.display = 'none';
            iframe.style.display = 'block';
        }
    }

    function printUserPreview() {
        const iframe = document.getElementById('previewIframe');
        if (iframe && iframe.contentWindow) {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                // Jika cross-origin atau terhalang browser, buka url langsung untuk print
                if (iframe.src && !iframe.src.endsWith('about:blank')) {
                    window.open(iframe.src, '_blank');
                }
            }
        }
    }

    // Interactive filter tab and search
    document.addEventListener('DOMContentLoaded', function() {
        // Pindahkan modals ke document.body agar tidak terpotong navbar dan tidak terkungkung stacking context .user-page-shell
        const modalAjukan = document.getElementById('modalAjukanSuket');
        const modalPreview = document.getElementById('previewDocModal');
        if (modalAjukan && modalAjukan.parentElement !== document.body) {
            document.body.appendChild(modalAjukan);
        }
        if (modalPreview && modalPreview.parentElement !== document.body) {
            document.body.appendChild(modalPreview);
        }
        document.querySelectorAll('.modal-evaluasi-lhu-user').forEach(function(m) {
            if (m.parentElement !== document.body) {
                document.body.appendChild(m);
            }
        });
        document.querySelectorAll('.modal-upload-bukti-user').forEach(function(m) {
            if (m.parentElement !== document.body) {
                document.body.appendChild(m);
            }
        });

        const filterBtns = document.querySelectorAll('[data-user-filter]');
        const searchInput = document.getElementById('userSearchInput');
        const cards = document.querySelectorAll('.user-suket-card-item');

        let activeFilter = 'all';
        let searchQuery = '';

        function applyFilters() {
            cards.forEach(card => {
                const cat = card.getAttribute('data-category');
                const ord = card.getAttribute('data-order') || '';
                const srt = card.getAttribute('data-surat') || '';
                const cmp = card.getAttribute('data-company') || '';

                const matchesFilter = (activeFilter === 'all') || (cat === activeFilter);
                const matchesSearch = !searchQuery || ord.includes(searchQuery) || srt.includes(searchQuery) || cmp.includes(searchQuery);

                if (matchesFilter && matchesSearch) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                activeFilter = this.getAttribute('data-user-filter');
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                searchQuery = this.value.trim().toLowerCase();
                applyFilters();
            });
        }

        @if($errors->any())
            const modalEl = document.getElementById('modalAjukanSuket');
            if (modalEl) {
                new bootstrap.Modal(modalEl).show();
            }
        @endif

        {{-- SweetAlert2 Notifikasi Sukses ACC Surat Tagihan --}}
        @if(session('success') && (str_contains(session('success'), 'Surat Tagihan berhasil disetujui') || str_contains(session('success'), 'Kode Billing')))
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    iconColor: '#10b981',
                    title: '<div class="fw-bold text-dark fs-5 mt-2">Surat Tagihan Berhasil Di-ACC!</div>',
                    html: `
                        <div class="text-muted small mt-2 px-2" style="line-height: 1.6;">
                            <div class="p-2 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25 text-success-emphasis mb-2">
                                <i class="bi bi-check2-circle me-1"></i> Konfirmasi persetujuan tagihan telah tercatat resmi di sistem Balai K3.
                            </div>
                            Permohonan Anda kini telah <strong>otomatis beralih ke Tahap 7 (Kode Billing SIMPONI)</strong>.<br>
                            Silakan unduh panduan pembayaran dan tunggu Bendahara menerbitkan kode billing resmi Anda.
                        </div>
                    `,
                    confirmButtonText: '<i class="bi bi-arrow-right-circle me-1"></i> Mengerti & Lanjutkan',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        confirmButton: 'btn btn-primary rounded-pill px-4 py-2'
                    },
                    buttonsStyling: false
                });
            }
        @endif
    });

    /**
     * Konfirmasi ACC Surat Tagihan dengan SweetAlert2 Modern
     */
    function confirmAccTagihan(suketId, orderNo, nominal, perusahaan) {
        if (typeof Swal === 'undefined') {
            if (confirm('Apakah Anda menyetujui (ACC) Surat Tagihan sebesar Rp ' + nominal + ' untuk nomor order ' + orderNo + '?')) {
                const form = document.getElementById('formAccTagihan' + suketId) || document.getElementById('formQuickAccTagihan' + suketId);
                if (form) form.submit();
            }
            return;
        }

        Swal.fire({
            title: '<div class="fw-bold text-dark mt-2" style="font-size: 20px;">Konfirmasi Persetujuan Surat Tagihan</div>',
            html: `
                <div class="text-start mt-3" style="font-size: 13.5px; line-height: 1.6;">
                    <div class="p-3 rounded-4 mb-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Nomor Order:</span>
                            <span class="badge bg-primary px-2 py-1 rounded-pill fw-semibold">${orderNo}</span>
                        </div>
                        ${perusahaan ? `
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Nama Perusahaan:</span>
                            <strong class="text-dark">${perusahaan}</strong>
                        </div>` : ''}
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-success border-opacity-25 mt-2">
                            <span class="text-success-emphasis fw-semibold">Total Tagihan Resmi (PNBP):</span>
                            <span class="fs-5 fw-bold text-success">Rp ${nominal}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2 text-muted small p-2 rounded-3 bg-light border">
                        <i class="bi bi-shield-check text-success fs-5 mt-1"></i>
                        <div>
                            Dengan menekan <strong>"Ya, Setujui (ACC)"</strong>, berkas Anda akan <strong>otomatis diteruskan ke Tahap 7 (Kode Billing)</strong> untuk pembayaran PNBP SIMPONI.
                        </div>
                    </div>
                </div>
            `,
            icon: 'question',
            iconColor: '#10b981',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-circle-fill me-1"></i> Ya, Setujui (ACC) Tagihan',
            cancelButtonText: '<i class="bi bi-x me-1"></i> Batal / Periksa Lagi',
            customClass: {
                popup: 'rounded-4 shadow-lg border-0',
                confirmButton: 'btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm',
                cancelButton: 'btn btn-light rounded-pill px-4 py-2 fw-semibold text-secondary'
            },
            buttonsStyling: false,
            reverseButtons: true,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const form = document.getElementById('formAccTagihan' + suketId) || document.getElementById('formQuickAccTagihan' + suketId);
                if (form) {
                    form.submit();
                }
            }
        });
    }
</script>

<link rel="stylesheet" href="{{ asset('vendor/pdfjs/pdf_viewer.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/lhu-annotator.css') }}">
<script src="{{ asset('vendor/pdfjs/pdf.min.js') }}"></script>
<script src="{{ asset('js/lhu-annotator.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @foreach($sukets as $suketItem)
        @if($suketItem->hasLhuDocument())
            const userModalEl{{ $suketItem->id }} = document.getElementById('modalUserEvaluasiLhu{{ $suketItem->id }}');
            if (userModalEl{{ $suketItem->id }}) {
                userModalEl{{ $suketItem->id }}.addEventListener('shown.bs.modal', function () {
                    window.LhuAnnotator.init({
                        suketId: {{ $suketItem->id }},
                        containerId: 'evalUserPdfContainer{{ $suketItem->id }}',
                        pdfUrl: '{{ route('user.suket.preview-doc', [$suketItem->id, 'lhu']) }}',
                        comments: @json($suketItem->pemohonComments ?? []),
                        readOnly: true,
                    });
                });
            }
        @endif
    @endforeach
});
</script>
@endsection
