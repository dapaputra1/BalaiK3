@extends('layouts.app_admin')

@push('styles')
<style>
    #beritaModal .modal-content {
        border: 0;
        overflow: hidden;
    }

    #beritaModal .modal-body {
        max-height: min(70vh, 720px);
        overflow-y: auto;
    }

    #beritaModal .modal-footer {
        position: sticky;
        bottom: 0;
        z-index: 2;
        background: #fff;
        border-top: 1px solid #dee2e6;
    }
</style>
@endpush

@section('content_admin')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Kelola Berita</h5>
        <small class="text-muted">Tambah, ubah, dan hapus berita yang tampil di halaman user</small>
    </div>
    <button class="btn text-white" style="background-color:#15406A; border-color:#15406A;" data-bs-toggle="modal" data-bs-target="#beritaModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i> Tambah Berita
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-3">
        {{ $errors->first() }}
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="beritaTable">
                <thead>
                    <tr>
                        <th>Tanggal Upload</th>
                        <th>Judul</th>
                        <th>Isi Berita</th>
                        <th>Upload Oleh</th>
                        <th class="text-center">Views</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beritas as $row)
                        <tr>
                            <td data-order="{{ optional($row->created_at)->timestamp ?? 0 }}">{{ $row->formatted_date }}</td>
                            <td class="fw-semibold">{{ $row->title }}</td>
                            <td style="min-width: 320px;">{{ \Illuminate\Support\Str::limit($row->excerpt, 140) }}</td>
                            <td>{{ $row->uploader_name }}</td>
                            <td class="text-center">{{ $row->views_total }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm px-3 text-white"
                                        style="background-color:#15406A; border-color:#15406A;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#beritaModal"
                                        data-mode="edit"
                                        data-id="{{ $row->id }}"
                                        data-title="{{ $row->title }}"
                                        data-content="{{ $row->content }}"
                                        data-upload-date="{{ optional($row->created_at)->format('Y-m-d\TH:i') }}"
                                        data-image-url="{{ $row->image_url }}">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <form action="{{ route('superadmin.berita.destroy', $row) }}" method="POST" class="d-inline delete-form ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm px-3 btn-delete text-white" style="background-color:#dc3545; border-color:#dc3545;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada berita</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="beritaModal" tabindex="-1" aria-labelledby="beritaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" id="beritaForm" class="modal-content" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="POST">

            <div class="modal-header">
                <h5 class="modal-title" id="beritaModalLabel">Berita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Judul Berita</label>
                    <input type="text" class="form-control" name="title" placeholder="Masukkan judul berita" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Isi Berita</label>
                    <textarea class="form-control" name="content" rows="10" placeholder="Masukkan isi berita" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal Upload</label>
                    <input type="datetime-local" class="form-control" name="upload_date" required>
                    <small class="text-muted d-block mt-2">Tanggal ini akan dipakai sebagai tanggal publikasi berita di website.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gambar Berita</label>
                    <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp">
                    <small class="text-muted d-block mt-2">Format: JPG, JPEG, PNG, WEBP. Ukuran file maksimal 20 MB. Dimensi gambar akan otomatis disesuaikan ke format berita 1600x900 px.</small>
                    <small class="text-muted d-block mt-1">Kosongkan jika tidak ingin mengubah gambar saat edit.</small>
                </div>

                <div class="d-none" id="beritaImagePreviewWrap">
                    <label class="form-label">Gambar Saat Ini</label>
                    <div>
                        <img id="beritaImagePreview" src="" alt="Preview berita" class="img-fluid rounded border" style="max-height: 220px;">
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <small class="text-muted">Lengkapi data lalu klik tombol simpan.</small>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send-check me-1"></i>Simpan Berita
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        $('#beritaTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [5] },
                { className: 'text-center', targets: [4, 5] },
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });
    });

    const beritaModal = document.getElementById('beritaModal');
    const beritaForm = document.getElementById('beritaForm');
    const beritaMethod = beritaForm.querySelector('input[name="_method"]');
    const beritaTitle = beritaForm.querySelector('input[name="title"]');
    const beritaContent = beritaForm.querySelector('textarea[name="content"]');
    const beritaUploadDate = beritaForm.querySelector('input[name="upload_date"]');
    const beritaImageInput = beritaForm.querySelector('input[name="image"]');
    const beritaModalLabel = document.getElementById('beritaModalLabel');
    const beritaImagePreviewWrap = document.getElementById('beritaImagePreviewWrap');
    const beritaImagePreview = document.getElementById('beritaImagePreview');

    const getCurrentDateTimeLocal = () => {
        const now = new Date();
        const offset = now.getTimezoneOffset();
        const local = new Date(now.getTime() - (offset * 60 * 1000));
        return local.toISOString().slice(0, 16);
    };

    const setPreviewImage = (url) => {
        if (!url) {
            beritaImagePreviewWrap.classList.add('d-none');
            beritaImagePreview.src = '';
            return;
        }

        beritaImagePreviewWrap.classList.remove('d-none');
        beritaImagePreview.src = url;
    };

    beritaModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';

        beritaForm.reset();
        setPreviewImage('');

        if (mode === 'edit') {
            beritaModalLabel.textContent = 'Edit Berita';
            beritaForm.action = "{{ route('superadmin.berita.index') }}/" + (button.getAttribute('data-id') || '');
            beritaMethod.value = 'PUT';
            beritaTitle.value = button.getAttribute('data-title') || '';
            beritaContent.value = button.getAttribute('data-content') || '';
            beritaUploadDate.value = button.getAttribute('data-upload-date') || getCurrentDateTimeLocal();
            setPreviewImage(button.getAttribute('data-image-url') || '');
        } else {
            beritaModalLabel.textContent = 'Tambah Berita';
            beritaForm.action = "{{ route('superadmin.berita.store') }}";
            beritaMethod.value = 'POST';
            beritaUploadDate.value = getCurrentDateTimeLocal();
        }

        if (beritaImageInput) {
            beritaImageInput.value = '';
        }
    });

    document.querySelectorAll('.btn-delete').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();

            const form = button.closest('form');
            Swal.fire({
                title: 'Hapus berita?',
                text: 'Berita yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection
