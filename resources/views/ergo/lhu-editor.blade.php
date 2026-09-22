@extends('layouts.app_admin')

@section('title', 'Live Editor Laporan Hasil Uji — ' . $assessment->worker_name)

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
        .text-navy {
            color: #15406A !important;
        }
        .bg-navy {
            background-color: #15406A !important;
            color: #ffffff !important;
        }
    </style>

    {{-- Top Header Action Bar --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-navy border border-primary-subtle px-2.5 py-1 rounded-pill fw-bold" style="font-size: 11px;">
                    Live Editor LHU
                </span>
                <span class="text-muted small">Balai K3 Surabaya</span>
            </div>
            <h4 class="fw-bold text-navy mb-0">Penyuntingan Draf Laporan Hasil Uji Resmi</h4>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('ergo.result', $assessment->id) }}" class="btn btn-outline-secondary btn-sm rounded-3 d-flex align-items-center gap-1.5">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <button type="submit" form="lhuForm" class="btn btn-primary btn-sm rounded-3 d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-floppy"></i>
                <span>Simpan Perubahan</span>
            </button>
            <a href="{{ route('ergo.pdf', $assessment->id) }}" target="_blank" class="btn btn-danger btn-sm rounded-3 d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-file-earmark-pdf"></i>
                <span>Cetak / Unduh PDF</span>
            </a>
        </div>
    </div>

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

    {{-- Sheet Form Container --}}
    <form id="lhuForm" action="{{ route('ergo.lhu.update', $assessment->id) }}" method="POST" class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
        @csrf
        @method('PUT')

        {{-- Kop Surat --}}
        <div class="text-center pb-3 border-bottom border-dark border-2 mb-4">
            <div class="text-uppercase fw-bold small text-secondary">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
            <div class="text-uppercase fw-bold small text-secondary">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN DAN K3</div>
            <div class="text-uppercase fw-black text-navy fs-5 my-1">BALAI HIPERKES DAN KESELAMATAN KERJA SURABAYA</div>
            <div class="text-muted" style="font-size: 11px;">Jl. Dukuh Menanggal No. 122, Kec. Gayungan, Surabaya 60234 | Laman: balaik3surabaya@kemnaker.go.id</div>
        </div>

        {{-- Judul & Nomor Laporan --}}
        <div class="text-center mb-4">
            <h5 class="fw-bold text-dark text-uppercase text-decoration-underline mb-1">LAPORAN HASIL</h5>
            <div class="small fw-bold text-secondary mb-2">Pengujian Faktor Ergonomi di Tempat Kerja</div>
            <div class="d-flex justify-content-center align-items-center gap-2">
                <label class="form-label small fw-bold text-secondary mb-0">Nomor Laporan:</label>
                <input type="text" name="lhu_doc_number" value="{{ old('lhu_doc_number', $assessment->lhu_doc_number) }}" class="form-control form-control-sm text-center fw-bold w-auto" placeholder="Contoh: 123/LHU/BK3-SBY/2026">
            </div>
        </div>

        {{-- 1. Data Umum --}}
        <div class="mb-4">
            <div class="fw-bold text-dark text-uppercase small mb-2">1. Data Umum</div>
            <div class="card bg-light border p-3 rounded-3">
                <div class="row g-3 small">
                    <div class="col-12 col-md-6">
                        <span class="text-muted d-block">a. Perusahaan:</span>
                        <strong class="text-dark">{{ $assessment->company_name }}</strong>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="text-muted d-block">b. Alamat:</span>
                        <strong class="text-dark">{{ $assessment->company_address ?? '-' }}</strong>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold text-dark mb-1">c. Pengurus / Penanggung Jawab Perusahaan:</label>
                        <input type="text" name="company_pic" value="{{ old('company_pic', $assessment->company_pic) }}" class="form-control form-control-sm bg-white" placeholder="Nama PIC Perusahaan">
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Rekapitulasi Hasil Pengukuran --}}
        <div class="mb-4">
            <div class="fw-bold text-dark text-uppercase small mb-2">4. Hasil Pengukuran Ergonomi (Tabel Rekapitulasi)</div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle text-center small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Tenaga Kerja</th>
                            <th>Jabatan</th>
                            <th>Tubuh Atas</th>
                            <th>Punggung/Bawah</th>
                            <th>MMH</th>
                            <th>Total Skor</th>
                            <th>Interpretasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-semibold">{{ $assessment->worker_name }}</td>
                            <td>{{ $assessment->position ?? '-' }}</td>
                            <td class="fw-bold">{{ $assessment->upper_body_score ?? 0 }}</td>
                            <td class="fw-bold">{{ $assessment->lower_body_score ?? 0 }}</td>
                            <td class="fw-bold">{{ $assessment->mmh_score ?? 0 }}</td>
                            <td class="fw-bold text-navy fs-6">{{ $assessment->total_score }}</td>
                            <td>
                                @if($assessment->risk_level === 'Aman')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">Aman</span>
                                @elseif($assessment->risk_level === 'Perlu Pengamatan Lanjut')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill">Pengamatan Lanjut</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">Berbahaya</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 5. Analisis --}}
        <div class="mb-3">
            <div class="fw-bold text-dark text-uppercase small mb-1">5. Narasi Analisis Potensi Bahaya (Butir 5)</div>
            <div class="text-muted small mb-2">Uraikan secara spesifik sikap janggal leher, bahu, pergelangan tangan, dan keluhan Nordic Body Map:</div>
            <textarea name="lhu_analysis" rows="4" class="form-control small">{{ old('lhu_analysis', $assessment->lhu_analysis) }}</textarea>
        </div>

        {{-- 6. Kesimpulan --}}
        <div class="mb-3">
            <div class="fw-bold text-dark text-uppercase small mb-1">6. Kesimpulan (Butir 6)</div>
            <textarea name="lhu_conclusion" rows="2" class="form-control small">{{ old('lhu_conclusion', $assessment->lhu_conclusion) }}</textarea>
        </div>

        {{-- 7. Saran & Tindakan Perbaikan --}}
        <div class="mb-4">
            <div class="fw-bold text-dark text-uppercase small mb-1">7. Saran dan Tindakan Perbaikan (Butir 7)</div>
            <div class="text-muted small mb-2">Rekomendasi teknis ergonomi postur statis (kantor), postur dinamis, dan cara angkat beban:</div>
            <textarea name="lhu_recommendation" rows="5" class="form-control small">{{ old('lhu_recommendation', $assessment->lhu_recommendation) }}</textarea>
        </div>

        {{-- Pejabat Penandatangan --}}
        <div class="pt-3 border-top mb-4">
            <div class="fw-bold text-dark text-uppercase small mb-2">Pengesahan Manajer Teknis</div>
            <div class="card bg-light border p-3 rounded-3">
                <div class="row g-3 small">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold text-dark mb-1">Jabatan Penandatangan</label>
                        <input type="text" name="signer_position" value="{{ old('signer_position', $assessment->signer_position) }}" class="form-control form-control-sm bg-white" placeholder="Contoh: Manajer Teknis">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold text-dark mb-1">Nama Lengkap & Gelar</label>
                        <input type="text" name="signer_name" value="{{ old('signer_name', $assessment->signer_name) }}" class="form-control form-control-sm bg-white" placeholder="Nama Pejabat">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold text-dark mb-1">Nomor Induk Pegawai (NIP)</label>
                        <input type="text" name="signer_nip" value="{{ old('signer_nip', $assessment->signer_nip) }}" class="form-control form-control-sm bg-white" placeholder="NIP Pejabat">
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <button type="submit" class="btn btn-primary px-4 d-flex align-items-center gap-1.5 shadow-sm">
                <i class="bi bi-floppy"></i>
                <span>Simpan Draf Narasi LHU</span>
            </button>
        </div>

    </form>

</div>
@endsection