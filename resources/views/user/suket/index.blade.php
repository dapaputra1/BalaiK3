@extends('layouts.app')

@section('content')
<div class="container py-4 mt-5 history-shell" style="font-family: 'Poppins', sans-serif;">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="/riwayat_pelayanan" class="text-decoration-none">Pelayanan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permohonan Suket K3</li>
                </ol>
            </nav>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-medical text-primary me-2"></i>Permohonan Surat Keterangan (Suket) K3 Lingkungan Kerja
            </h3>
            <p class="text-muted small mb-0">
                Layanan pengajuan Surat Keterangan K3 Lingkungan Kerja berdasarkan evaluasi hasil pengujian laboratorium (LHU) sesuai standar <strong>Permenaker No. 5 Tahun 2018</strong>.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="/riwayat_pelayanan" class="btn btn-outline-secondary rounded-pill px-3 py-2 btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Pelayanan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 text-success me-3"></i>
                <div>
                    <strong>Berhasil!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-danger me-3"></i>
                <div>
                    <strong>Perhatian:</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
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

    <div class="row g-4 mb-5">
        {{-- FORM PENGAJUAN (TAHAP 1 PEMOHON) --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
                            <i class="bi bi-file-earmark-plus-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Form Pengajuan Suket K3 Baru</h5>
                            <p class="text-muted small mb-0">
                                Isi formulir berikut dengan memilih nomor order pengujian Anda, ruang lingkup faktor K3, dan lampiran pendukung.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('user.suket.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        {{-- 1. PILIH NOMOR ORDER PEMOHON --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark mb-1">
                                1. Nomor Order / Kode Permohonan Anda <span class="text-danger">*</span>
                            </label>
                            <p class="text-muted small mb-2">Pilih dari pesanan pengujian yang terdaftar di akun Anda.</p>
                            <select 
                                name="nomor_order" 
                                id="user_nomor_order" 
                                class="form-select rounded-3 @error('nomor_order') is-invalid @enderror" 
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
                            <div class="form-text small text-muted">
                                Dokumen LHU yang telah diterbitkan pada nomor order tersebut dapat ditarik secara otomatis.
                            </div>
                        </div>

                        {{-- 2. RUANG LINGKUP FAKTOR K3 --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark mb-1">
                                2. Ruang Lingkup Faktor K3 yang Diuji <span class="text-danger">*</span>
                            </label>
                            <p class="text-muted small mb-2">Pilih minimal 1 faktor lingkungan kerja yang telah diuji di lokasi perusahaan Anda:</p>
                            <div class="row g-2">
                                @foreach($faktorOptions as $fKey => $fDesc)
                                    <div class="col-md-6">
                                        <div class="form-check p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="faktor_k3[]" 
                                                value="{{ $fKey }}" 
                                                id="user_f_{{ $fKey }}"
                                                @checked(is_array(old('faktor_k3')) && in_array($fKey, old('faktor_k3')))
                                            >
                                            <label class="form-check-label small fw-semibold text-dark" for="user_f_{{ $fKey }}">
                                                {{ $fDesc }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('faktor_k3')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- 3. SUMBER DOKUMEN LHU --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark mb-1">
                                3. Sumber Dokumen Laporan Hasil Uji (LHU) <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-column flex-sm-row gap-3 mt-1">
                                <div class="form-check p-3 border rounded-3 bg-light bg-opacity-50 flex-fill">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="lhu_source" 
                                        id="user_source_auto" 
                                        value="auto" 
                                        checked
                                        onchange="toggleUserLhu(this.value)"
                                    >
                                    <label class="form-check-label small fw-semibold text-dark" for="user_source_auto">
                                        Tarik Otomatis dari Nomor Order Balai K3
                                    </label>
                                </div>
                                <div class="form-check p-3 border rounded-3 bg-light bg-opacity-50 flex-fill">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="lhu_source" 
                                        id="user_source_manual" 
                                        value="manual"
                                        onchange="toggleUserLhu(this.value)"
                                    >
                                    <label class="form-check-label small fw-semibold text-dark" for="user_source_manual">
                                        Upload Manual File LHU (PDF)
                                    </label>
                                </div>
                            </div>
                            
                            <div id="user_manual_lhu_box" class="mt-3 p-3 border border-dashed rounded-3 bg-light" style="display: none;">
                                <label class="form-label small fw-bold text-dark mb-1">Unggah Berkas LHU (PDF, maks 20MB)</label>
                                <input type="file" name="lhu_file" class="form-control" accept=".pdf">
                                <div class="form-text small text-muted">Lampirkan file PDF LHU resmi yang telah bertanda tangan.</div>
                            </div>
                        </div>

                        {{-- 4. LAMPIRAN FOTO & DENAH --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark mb-1">
                                4. Dokumen Pendukung Pengujian
                            </label>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                        <label class="form-label small fw-semibold text-dark mb-1">
                                            <i class="bi bi-camera me-1"></i> Foto Pengujian Lapangan
                                        </label>
                                        <input type="file" name="foto_pengujian" class="form-control form-control-sm" accept="image/*,.pdf">
                                        <div class="form-text small text-muted">Format: JPG, PNG, atau PDF (maks 20MB).</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100">
                                        <label class="form-label small fw-semibold text-dark mb-1">
                                            <i class="bi bi-map me-1"></i> Denah Lokasi Pengujian
                                        </label>
                                        <input type="file" name="denah_lokasi" class="form-control form-control-sm" accept="image/*,.pdf">
                                        <div class="form-text small text-muted">Format: JPG, PNG, atau PDF (maks 20MB).</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 5. CATATAN TAMBAHAN --}}
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-dark mb-1">5. Catatan Pemohon (Opsional)</label>
                            <textarea 
                                name="catatan" 
                                class="form-control rounded-3" 
                                rows="2" 
                                placeholder="Tuliskan keterangan atau catatan tambahan bila diperlukan..."
                            >{{ old('catatan') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                                <i class="bi bi-send-fill me-2"></i>Kirim Permohonan Suket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- INFO ALUR 6 TAHAP --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom p-4">
                    <h5 class="fw-bold text-dark mb-1">Alur Proses Penerbitan Suket K3</h5>
                    <p class="text-muted small mb-0">Tahapan resmi penerbitan Surat Keterangan K3 Lingkungan Kerja Balai K3 Surabaya.</p>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="badge rounded-circle bg-primary p-2 fs-6">1</div>
                            <div>
                                <div class="fw-bold text-dark small">Tahap 1: Permohonan</div>
                                <div class="text-muted small">Pemohon mengajukan permohonan melalui form di samping dengan melampirkan LHU, foto, dan denah.</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="badge rounded-circle bg-secondary p-2 fs-6">2</div>
                            <div>
                                <div class="fw-bold text-dark small">Tahap 2: Evaluasi Dokumen</div>
                                <div class="text-muted small">Tim Penguji K3 mengevaluasi kesesuaian dokumen LHU, denah lokasi, dan foto pengujian lapangan.</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="badge rounded-circle bg-secondary p-2 fs-6">3</div>
                            <div>
                                <div class="fw-bold text-dark small">Tahap 3: Penyusunan Laporan / Suket</div>
                                <div class="text-muted small">Penguji K3 menyusun draf Surat Keterangan berbasis format resmi <strong>Permenaker No. 5 Tahun 2018</strong>.</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="badge rounded-circle bg-info text-white p-2 fs-6"><i class="bi bi-shield-check"></i></div>
                            <div>
                                <div class="fw-bold text-dark small">Gerbang QC Review</div>
                                <div class="text-muted small">Tim QC memverifikasi keabsahan draf sebelum diteruskan ke Kepala Balai.</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="badge rounded-circle bg-secondary p-2 fs-6">4</div>
                            <div>
                                <div class="fw-bold text-dark small">Tahap 4: Penandatanganan Suket</div>
                                <div class="text-muted small">Kepala Balai K3 membubuhkan tanda tangan elektronik / pengesahan dokumen resmi.</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="badge rounded-circle bg-secondary p-2 fs-6">5</div>
                            <div>
                                <div class="fw-bold text-dark small">Tahap 5: Penerbitan Suket</div>
                                <div class="text-muted small">Admin memberikan Nomor Surat Keterangan resmi Balai K3 Surabaya.</div>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="badge rounded-circle bg-success p-2 fs-6">6</div>
                            <div>
                                <div class="fw-bold text-dark small">Tahap 6: Diteruskan ke Pelanggan</div>
                                <div class="text-muted small">Dokumen resmi langsung tersedia dan dapat diunduh oleh pemohon di portal ini.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL RIWAYAT PERMOHONAN SUKET SAYA --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-5">
        <div class="card-header bg-white border-bottom p-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-1">Riwayat Pengajuan Suket K3 Saya</h5>
                <p class="text-muted small mb-0">Pantau proses evaluasi dan unduh dokumen Surat Keterangan yang telah terbit.</p>
            </div>
            <form action="{{ route('user.suket.index') }}" method="GET" class="d-flex gap-2">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control form-control-sm rounded-pill px-3" 
                    placeholder="Cari nomor order / surat..." 
                    value="{{ $search }}"
                >
                <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light table-light">
                        <tr class="small text-secondary fw-semibold">
                            <th class="ps-4">No</th>
                            <th>Nomor Order</th>
                            <th>Faktor K3</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status Tahapan</th>
                            <th>Nomor Surat Resmi</th>
                            <th class="text-end pe-4">Aksi / Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($sukets as $idx => $suket)
                            <tr>
                                <td class="ps-4 text-muted">{{ $sukets->firstItem() + $idx }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $suket->nomor_order }}</div>
                                    <div class="text-muted small">{{ $suket->perusahaan_nama }}</div>
                                </td>
                                <td>
                                    @if(is_array($suket->faktor_k3))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($suket->faktor_k3 as $fak)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-capitalize px-2 py-1">
                                                    {{ $fak }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-muted">
                                    {{ $suket->created_at ? $suket->created_at->locale('id')->isoFormat('D MMMM Y, HH:mm') : '-' }}
                                </td>
                                <td>
                                    @php
                                        $badgeColor = match($suket->status_tahap) {
                                            1 => 'secondary',
                                            2 => 'info',
                                            3 => 'warning',
                                            4 => 'primary',
                                            5 => 'indigo',
                                            6 => 'success',
                                            default => 'secondary'
                                        };
                                        $labelTahap = \App\Models\SuketK3::STAGES[$suket->status_tahap]['label'] ?? "Tahap {$suket->status_tahap}";
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }} px-2 py-1 rounded-pill">
                                        Tahap {{ $suket->status_tahap }}: {{ $labelTahap }}
                                    </span>
                                </td>
                                <td>
                                    @if(!empty($suket->nomor_surat))
                                        <span class="fw-semibold text-dark">{{ $suket->nomor_surat }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Menunggu Penerbitan</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        @if($suket->status_tahap >= 6 && ($suket->signed_file_path || $suket->draft_file_path))
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-success fw-semibold"
                                                onclick="openDocumentPreview('{{ route('user.suket.preview-doc', [$suket->id, 'signed']) }}', 'Surat Keterangan K3 Resmi - {{ $suket->nomor_order }}')"
                                            >
                                                <i class="bi bi-eye-fill me-1"></i> Lihat Suket
                                            </button>
                                            <a 
                                                href="{{ route('user.suket.download-doc', [$suket->id, 'signed']) }}" 
                                                class="btn btn-sm btn-outline-success"
                                                title="Unduh Berkas Resmi"
                                            >
                                                <i class="bi bi-download"></i>
                                            </a>
                                        @else
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-outline-secondary"
                                                disabled
                                                title="Suket sedang diproses oleh Tim Balai K3"
                                            >
                                                <i class="bi bi-hourglass-split me-1"></i> Sedang Diproses
                                            </button>
                                        @endif

                                        @if($suket->lhu_file_path)
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-outline-primary"
                                                onclick="openDocumentPreview('{{ route('user.suket.preview-doc', [$suket->id, 'lhu']) }}', 'Dokumen LHU - {{ $suket->nomor_order }}')"
                                                title="Lihat Berkas LHU"
                                            >
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-file-earmark-x fs-1 text-secondary mb-2 d-block"></i>
                                    Belum ada pengajuan Surat Keterangan K3. Silakan ajukan melalui formulir di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sukets->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $sukets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL DOCUMENT PREVIEW TANPA DOWNLOAD --}}
<div class="modal fade" id="previewDocModal" tabindex="-1" aria-labelledby="previewDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light border-bottom py-3">
                <h5 class="modal-title fw-bold text-dark" id="previewDocModalLabel">Preview Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="min-height: 520px; background-color: #f8f9fa;">
                <iframe id="previewDocIframe" src="" style="width: 100%; height: 75vh; border: none;"></iframe>
            </div>
            <div class="modal-footer border-top bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                <a id="previewDocDownloadBtn" href="#" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-download me-1"></i> Unduh File
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleUserLhu(source) {
        const box = document.getElementById('user_manual_lhu_box');
        if (box) {
            box.style.display = source === 'manual' ? 'block' : 'none';
        }
    }

    function openDocumentPreview(url, title) {
        const modalEl = document.getElementById('previewDocModal');
        const modalTitle = document.getElementById('previewDocModalLabel');
        const iframe = document.getElementById('previewDocIframe');
        const downloadBtn = document.getElementById('previewDocDownloadBtn');

        if (modalTitle) modalTitle.innerText = title || 'Preview Dokumen';
        if (iframe) iframe.src = url;
        if (downloadBtn) {
            downloadBtn.href = url.replace('/preview/', '/download/');
        }

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endsection
