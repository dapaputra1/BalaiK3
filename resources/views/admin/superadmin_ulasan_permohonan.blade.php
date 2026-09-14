@extends('layouts.app_admin')

@section('content_admin')
@php
    $routePrefix = $routePrefix ?? (request()->routeIs('admin.*') ? 'admin' : 'superadmin');
@endphp

<style>
    .badge-priority {
        background: #f0f4ff;
        color: #1b3f73;
        border: 1px solid #d6e3ff;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-semibold mb-0">Kelola Ulasan Permohonan (IKM & IKK)</h4>
        <div class="text-muted small">Form pemohon tetap satu, tetapi laporan dipisah menjadi 2 indeks: IKM dan IKK.</div>
    </div>
    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#ulasanAddModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Pertanyaan
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total Pertanyaan</div>
                <div class="h5 mb-0">{{ $stats['total_questions'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Pertanyaan Aktif</div>
                <div class="h5 mb-0 text-success">{{ $stats['active_questions'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Rasio IKM</div>
                <div class="h5 mb-0">
                    @if(!is_null($stats['ikm_ratio'] ?? null))
                        {{ number_format((float) $stats['ikm_ratio'], 1) }}%
                    @else
                        -
                    @endif
                </div>
                <div class="small text-muted">
                    @if(!is_null($stats['ikm_avg'] ?? null))
                        {{ number_format((float) $stats['ikm_avg'], 2) }}/4
                    @else
                        Belum ada data
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Rasio IKK</div>
                <div class="h5 mb-0">
                    @if(!is_null($stats['ikk_ratio'] ?? null))
                        {{ number_format((float) $stats['ikk_ratio'], 1) }}%
                    @else
                        -
                    @endif
                </div>
                <div class="small text-muted">
                    @if(!is_null($stats['ikk_avg'] ?? null))
                        {{ number_format((float) $stats['ikk_avg'], 2) }}/4
                    @else
                        Belum ada data
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-12 col-md-5">
                <label class="form-label small text-muted mb-1">Cari pertanyaan</label>
                <input type="text" class="form-control form-control-sm" placeholder="Misal: kesesuaian persyaratan" data-search-question>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted mb-1">Kategori</label>
                <select class="form-select form-select-sm" data-filter-category>
                    <option value="">Semua</option>
                    <option value="ikm">IKM</option>
                    <option value="ikk">IKK</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select class="form-select form-select-sm" data-filter-status>
                    <option value="">Semua</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100 btn-sm" data-search-reset>Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="mb-0 fw-semibold">Daftar Pertanyaan Ulasan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 30%;">Pertanyaan</th>
                    <th style="width: 8%;">IKM/IKK</th>
                    <th style="width: 10%;">Jenis</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 8%;">Urutan</th>
                    <th style="width: 9%;">Total</th>
                    <th style="width: 10%;">Rata-rata</th>
                    <th style="width: 9%;">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($questions as $item)
                    @php
                        $rowTypeLabel = $item->type === 'rating' ? 'Rating' : 'Teks';
                        $rowStatus = $item->is_active ? 'active' : 'inactive';
                        $category = $item->category === 'ikk' ? 'IKK' : 'IKM';
                        $ratingLabelsText = collect($item->rating_labels ?? [])->implode("\n");
                    @endphp
                    <tr
                        data-question-row
                        data-question-text="{{ strtolower($item->question) }}"
                        data-status="{{ $rowStatus }}"
                        data-category="{{ strtolower($category) }}"
                    >
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->question }}</div>
                            @if(!empty($item->note))
                                <div class="text-muted small">{{ $item->note }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ strtolower($category) === 'ikm' ? 'text-bg-primary' : 'text-bg-warning' }}">
                                {{ $category }}
                            </span>
                        </td>
                        <td><span class="badge badge-priority">{{ $rowTypeLabel }}</span></td>
                        <td>
                            @if($item->is_active)
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>{{ $item->sort_order }}</td>
                        <td>{{ $item->total_responses }}</td>
                        <td>
                            @if($item->type === 'rating')
                                {{ $item->avg_rating ? number_format((float) $item->avg_rating, 2) . '/4' : '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button
                                    class="btn btn-outline-primary btn-sm"
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#ulasanEditModal"
                                    data-id="{{ $item->id }}"
                                    data-question="{{ $item->question }}"
                                    data-type="{{ $item->type }}"
                                    data-category="{{ $item->category ?? 'ikm' }}"
                                    data-status="{{ $item->is_active ? '1' : '0' }}"
                                    data-sort-order="{{ $item->sort_order }}"
                                    data-note="{{ $item->note }}"
                                    data-rating-labels="{{ $ratingLabelsText }}"
                                    data-update-url="{{ route($routePrefix . '.ulasan-permohonan.update', $item) }}"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <form method="POST" action="{{ route($routePrefix . '.ulasan-permohonan.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="confirm" value="1">
                                    <button class="btn btn-outline-danger btn-sm" type="submit" onclick="return confirm('Hapus pertanyaan ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada pertanyaan ulasan.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="mb-0 fw-semibold">Ringkasan Per Pertanyaan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="ringkasanPertanyaanTable" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead class="table-light">
                <tr>
                    <th>Pertanyaan</th>
                    <th>IKM/IKK</th>
                    <th>Total Jawaban</th>
                    <th>Rata-rata</th>
                    <th>Rasio</th>
                </tr>
                </thead>
                <tbody>
                @forelse($questions as $item)
                    @php
                        $avgRating = $item->avg_rating ? (float) $item->avg_rating : null;
                        $ratio = !is_null($avgRating) ? ($avgRating / 4) * 100 : null;
                    @endphp
                    <tr>
                        <td class="ringkasan-pertanyaan-cell" data-order="{{ (int) ($item->sort_order ?? 0) }}">{{ $item->question }}</td>
                        <td>{{ strtoupper($item->category ?? 'ikm') }}</td>
                        <td>{{ $item->total_responses }}</td>
                        <td>{{ !is_null($avgRating) ? number_format($avgRating, 2) . '/4' : '-' }}</td>
                        <td>{{ !is_null($ratio) ? number_format($ratio, 1) . '%' : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada data hasil ulasan.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 pt-3">
        <h6 class="mb-0 fw-semibold">Hasil Ulasan Terbaru</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="ulasanTerbaruTable" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Kode Permohonan</th>
                    <th>Pemohon</th>
                    <th>Jumlah Jawaban</th>
                    <th>Rasio IKM</th>
                    <th>Rasio IKK</th>
                    <th style="width: 90px;">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentSubmissions as $submission)
                    <tr>
                        <td data-order="{{ \Carbon\Carbon::parse($submission['submitted_at'])->format('Y-m-d H:i:s') }}">
                            {{ \Carbon\Carbon::parse($submission['submitted_at'])->format('d M Y H:i') }}
                        </td>
                        <td>{{ $submission['permohonan_kode'] ?? '-' }}</td>
                        <td>{{ $submission['user_name'] ?? '-' }}</td>
                        <td>{{ $submission['total_answers'] ?? 0 }}</td>
                        <td>{{ !is_null($submission['ikm_ratio']) ? number_format((float) $submission['ikm_ratio'], 1) . '%' : '-' }}</td>
                        <td>{{ !is_null($submission['ikk_ratio']) ? number_format((float) $submission['ikk_ratio'], 1) . '%' : '-' }}</td>
                        <td>
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#ulasanDetailModal"
                                data-kode="{{ $submission['permohonan_kode'] ?? '-' }}"
                                data-user="{{ $submission['user_name'] ?? '-' }}"
                                data-submitted-at="{{ \Carbon\Carbon::parse($submission['submitted_at'])->format('d M Y H:i') }}"
                                data-answers="{{ e(json_encode($submission['answers'] ?? [])) }}"
                            >
                                Detail
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada hasil ulasan.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="ulasanDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title mb-0">Detail Hasil Ulasan</h6>
                    <div class="small text-muted">Ringkasan jawaban pemohon (urut nomor pertanyaan)</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="border rounded-3 p-2 bg-light-subtle h-100">
                            <div class="small text-muted">Kode Permohonan</div>
                            <div class="fw-semibold" id="detailSubmissionKode">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-2 bg-light-subtle h-100">
                            <div class="small text-muted">Pemohon</div>
                            <div class="fw-semibold" id="detailSubmissionUser">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded-3 p-2 bg-light-subtle h-100">
                            <div class="small text-muted">Waktu Submit</div>
                            <div class="fw-semibold" id="detailSubmissionDate">-</div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">No</th>
                                <th style="width: 90px;">IKM/IKK</th>
                                <th>Pertanyaan</th>
                                <th style="width: 35%;">Jawaban</th>
                            </tr>
                        </thead>
                        <tbody id="detailSubmissionAnswers">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada detail jawaban.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="ulasanAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route($routePrefix . '.ulasan-permohonan.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">Tambah Pertanyaan Ulasan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Pertanyaan</label>
                        <input type="text" name="question" class="form-control" placeholder="Masukkan pertanyaan" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category" required>
                            <option value="ikm" selected>IKM</option>
                            <option value="ikk">IKK</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Jenis</label>
                        <select class="form-select" name="type" required>
                            <option value="rating" selected>Rating (1-4)</option>
                            <option value="text">Teks</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="sort_order" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1" selected>Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Catatan</label>
                        <input type="text" class="form-control" name="note" placeholder="Opsional">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Label Opsi Rating (4 baris, untuk nilai 1-4)</label>
                        <textarea class="form-control" name="rating_labels_input" rows="4" placeholder="Contoh:
Tidak sesuai
Kurang sesuai
Sesuai
Sangat sesuai"></textarea>
                        <div class="small text-muted mt-1">Kosongkan jika ingin tampil angka 1-4 saja.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Batal</button>
                <button class="btn btn-primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="ulasanEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" id="ulasanEditForm" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h6 class="modal-title">Edit Pertanyaan Ulasan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Pertanyaan</label>
                        <input type="text" class="form-control" name="question" id="editQuestion" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="category" id="editCategory" required>
                            <option value="ikm">IKM</option>
                            <option value="ikk">IKK</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Jenis</label>
                        <select class="form-select" name="type" id="editType" required>
                            <option value="rating">Rating (1-4)</option>
                            <option value="text">Teks</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control" name="sort_order" id="editSortOrder" min="1" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active" id="editStatus" required>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label">Catatan</label>
                        <input type="text" class="form-control" name="note" id="editNote" placeholder="Opsional">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Label Opsi Rating (4 baris)</label>
                        <textarea class="form-control" name="rating_labels_input" id="editRatingLabels" rows="4"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Batal</button>
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<style>
    #ulasanTerbaruTable th,
    #ulasanTerbaruTable td,
    #ringkasanPertanyaanTable th,
    #ringkasanPertanyaanTable td {
        text-align: center;
        vertical-align: middle;
    }
    #ringkasanPertanyaanTable td.ringkasan-pertanyaan-cell {
        text-align: left;
    }
    #detailSubmissionAnswers td {
        vertical-align: middle;
    }
    .detail-answer-cell {
        white-space: normal;
        line-height: 1.35;
        text-align: center;
    }
    .detail-question-cell {
        text-align: left !important;
        white-space: normal;
        line-height: 1.35;
    }
    #ulasanDetailModal table th,
    #ulasanDetailModal table td {
        text-align: center;
        vertical-align: middle;
    }
    .dataTables_wrapper .pagination .page-link,
    .dt-container .pagination .page-link {
        color: #15406A !important;
    }
    .dataTables_wrapper .pagination .page-item.active .page-link,
    .dt-container .pagination .page-item.active .page-link {
        background-color: #15406A !important;
        border-color: #15406A !important;
        color: #fff !important;
    }
    .dataTables_wrapper .pagination .page-link:hover,
    .dataTables_wrapper .pagination .page-link:focus,
    .dt-container .pagination .page-link:hover,
    .dt-container .pagination .page-link:focus {
        color: #15406A !important;
        border-color: #15406A !important;
        box-shadow: none !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        $('#ringkasanPertanyaanTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[0, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                zeroRecords: "No matching records found",
                paginate: { previous: "Previous", next: "Next" }
            }
        });

        $('#ulasanTerbaruTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [6] },
            ],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                zeroRecords: "No matching records found",
                paginate: { previous: "Previous", next: "Next" }
            }
        });
    });

    (() => {
        const editModal = document.getElementById('ulasanEditModal');
        const editForm = document.getElementById('ulasanEditForm');
        const editQuestion = document.getElementById('editQuestion');
        const editType = document.getElementById('editType');
        const editCategory = document.getElementById('editCategory');
        const editStatus = document.getElementById('editStatus');
        const editSortOrder = document.getElementById('editSortOrder');
        const editNote = document.getElementById('editNote');
        const editRatingLabels = document.getElementById('editRatingLabels');
        const detailModal = document.getElementById('ulasanDetailModal');
        const detailKode = document.getElementById('detailSubmissionKode');
        const detailUser = document.getElementById('detailSubmissionUser');
        const detailDate = document.getElementById('detailSubmissionDate');
        const detailAnswersBody = document.getElementById('detailSubmissionAnswers');

        const decodeHtml = (value) => {
            if (!value) return '';
            const textarea = document.createElement('textarea');
            textarea.innerHTML = value;
            return textarea.value;
        };

        const parseAnswers = (raw) => {
            try {
                return JSON.parse(decodeHtml(raw || '[]')) || [];
            } catch (error) {
                return [];
            }
        };

        editModal?.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            if (!button) return;

            editForm.action = button.getAttribute('data-update-url') || '';
            editQuestion.value = button.getAttribute('data-question') || '';
            editType.value = button.getAttribute('data-type') || 'rating';
            editCategory.value = button.getAttribute('data-category') || 'ikm';
            editStatus.value = button.getAttribute('data-status') || '1';
            editSortOrder.value = button.getAttribute('data-sort-order') || '1';
            editNote.value = button.getAttribute('data-note') || '';
            editRatingLabels.value = button.getAttribute('data-rating-labels') || '';
        });

        detailModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) return;

            const kode = trigger.getAttribute('data-kode') || '-';
            const user = trigger.getAttribute('data-user') || '-';
            const submittedAt = trigger.getAttribute('data-submitted-at') || '-';
            const answers = parseAnswers(trigger.getAttribute('data-answers'));

            if (detailKode) detailKode.textContent = kode;
            if (detailUser) detailUser.textContent = user;
            if (detailDate) detailDate.textContent = submittedAt;

            if (!detailAnswersBody) return;
            if (!Array.isArray(answers) || answers.length === 0) {
                detailAnswersBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">Detail jawaban tidak tersedia.</td></tr>';
                return;
            }

            const sortedAnswers = [...answers].sort((a, b) => {
                const noA = Number(a?.no || 0);
                const noB = Number(b?.no || 0);
                return noA - noB;
            });

            detailAnswersBody.innerHTML = sortedAnswers.map((item) => `
                <tr>
                    <td>${item.no ?? '-'}</td>
                    <td>
                        <span class="badge ${(String(item.category || '').toUpperCase() === 'IKK') ? 'text-bg-warning' : 'text-bg-primary'}">
                            ${item.category ?? '-'}
                        </span>
                    </td>
                    <td class="detail-question-cell">${item.question ?? '-'}</td>
                    <td class="detail-answer-cell">${item.answer ?? '-'}</td>
                </tr>
            `).join('');
        });

        const searchInput = document.querySelector('[data-search-question]');
        const statusFilter = document.querySelector('[data-filter-status]');
        const categoryFilter = document.querySelector('[data-filter-category]');
        const resetButton = document.querySelector('[data-search-reset]');
        const rows = Array.from(document.querySelectorAll('[data-question-row]'));

        const applyFilter = () => {
            const search = (searchInput?.value || '').trim().toLowerCase();
            const status = statusFilter?.value || '';
            const category = categoryFilter?.value || '';

            rows.forEach((row) => {
                const text = row.getAttribute('data-question-text') || '';
                const rowStatus = row.getAttribute('data-status') || '';
                const rowCategory = row.getAttribute('data-category') || '';

                const passSearch = search === '' || text.includes(search);
                const passStatus = status === '' || rowStatus === status;
                const passCategory = category === '' || rowCategory === category;
                row.classList.toggle('d-none', !(passSearch && passStatus && passCategory));
            });
        };

        searchInput?.addEventListener('input', applyFilter);
        statusFilter?.addEventListener('change', applyFilter);
        categoryFilter?.addEventListener('change', applyFilter);
        resetButton?.addEventListener('click', () => {
            if (searchInput) searchInput.value = '';
            if (statusFilter) statusFilter.value = '';
            if (categoryFilter) categoryFilter.value = '';
            applyFilter();
        });
    })();
</script>
@endpush
@endsection
