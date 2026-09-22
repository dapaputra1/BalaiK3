@extends('layouts.app_admin')

@section('title', 'Pengujian Faktor Ergonomi (SNI 9011:2021)')

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
        .table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
            padding-top: 12px;
            padding-bottom: 12px;
        }
        .table tbody td {
            font-size: 13px;
            vertical-align: middle;
            padding: 12px 14px;
        }
    </style>

    {{-- Header Section --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-navy border border-primary-subtle px-2.5 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                    SNI 9011:2021
                </span>
                <span class="text-muted small">Form Gotrak & Evaluasi Postur Kerja</span>
            </div>
            <h4 class="fw-bold text-navy mb-1">Pengujian Faktor Ergonomi</h4>
            <p class="text-muted small mb-0">Daftar rekapitulasi evaluasi potensi bahaya ergonomi tenaga kerja dan formulir Gotrak.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('ergo.create') }}" class="btn btn-primary d-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm">
                <i class="bi bi-plus-circle"></i>
                <span>Tambah Pengujian Baru</span>
            </a>
        </div>
    </div>

    {{-- Main Card Content --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Perusahaan</th>
                            <th>Tenaga Kerja</th>
                            <th>Jabatan</th>
                            <th class="text-center">Skor Akhir</th>
                            <th class="text-center">Tingkat Risiko</th>
                            <th class="text-center">Foto Dokumentasi</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessments as $item)
                            <tr>
                                <td class="ps-4 text-secondary whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($item->assessment_date)->isoFormat('D MMMM Y') }}
                                </td>
                                <td class="fw-bold text-dark">{{ $item->company_name }}</td>
                                <td class="text-dark">{{ $item->worker_name }}</td>
                                <td class="text-muted">{{ $item->position ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="fw-bold fs-6 text-navy">{{ $item->total_score }}</span>
                                </td>
                                <td class="text-center">
                                    @if($item->risk_level === 'Aman')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 11px;">
                                            <i class="bi bi-shield-check me-1"></i> Aman
                                        </span>
                                    @elseif($item->risk_level === 'Perlu Pengamatan Lanjut')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 11px;">
                                            <i class="bi bi-exclamation-triangle me-1"></i> Perlu Pengamatan Lanjut
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold" style="font-size: 11px;">
                                            <i class="bi bi-x-octagon me-1"></i> Berbahaya
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-3 fw-normal" style="font-size: 11px;">
                                        <i class="bi bi-camera me-1"></i> {{ $item->photos_count ?? 0 }} Foto
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Tombol Detail -->
                                        <a href="{{ route('ergo.result', $item->id) }}" class="btn btn-sm btn-outline-primary p-1.5 rounded-3" title="Lihat Hasil & LHU">
                                            <i class="bi bi-eye fs-6"></i>
                                        </a>

                                        <!-- Tombol Edit -->
                                        <a href="{{ route('ergo.edit', $item->id) }}" class="btn btn-sm btn-outline-warning p-1.5 rounded-3" title="Edit Data Pengujian">
                                            <i class="bi bi-pencil fs-6"></i>
                                        </a>

                                        <!-- Tombol Cetak PDF Langsung -->
                                        <a href="{{ route('ergo.pdf', $item->id) }}" target="_blank" class="btn btn-sm btn-outline-danger p-1.5 rounded-3" title="Unduh PDF">
                                            <i class="bi bi-file-earmark-pdf fs-6"></i>
                                        </a>

                                        <!-- Tombol Hapus -->
                                        <form action="{{ route('ergo.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengujian ini beserta lampiran fotonya?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary p-1.5 rounded-3 text-danger border-0" title="Hapus Data">
                                                <i class="bi bi-trash fs-6"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder2-open display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    <div>Belum ada data pengujian ergonomi yang tersimpan.</div>
                                    <a href="{{ route('ergo.create') }}" class="btn btn-sm btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i> Buat Pengujian Pertama
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($assessments, 'hasPages') && $assessments->hasPages())
                <div class="p-3 border-top bg-light-subtle d-flex justify-content-end">
                    {{ $assessments->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection