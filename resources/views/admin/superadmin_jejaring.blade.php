@extends('layouts.app_admin')

@section('content_admin')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div>
        <h5 class="mb-0">Kelola Jejaring</h5>
        <small class="text-muted">Kelola data universitas, PJK3, perusahaan, instansi wilayah kerja, dan instansi.</small>
    </div>
    <button class="btn text-white" style="background-color:#15406A; border-color:#15406A;" data-bs-toggle="modal" data-bs-target="#jejaringModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i> Tambah Data
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
@endif
@if(($tableReady ?? false) === false)
    <div class="alert alert-warning">
        Tabel <code>jejaring_entries</code> belum ada. Jalankan <code>php artisan migrate</code> agar fitur kelola jejaring bisa dipakai.
    </div>
@endif

<div class="row g-3 mb-4">
    @foreach($categories as $key => $meta)
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-body">
                    <div class="text-muted small mb-1">{{ $meta['label'] }}</div>
                    <div class="fs-4 fw-semibold text-dark">{{ $countsByCategory[$key] ?? 0 }}</div>
                    <a href="{{ route($meta['route']) }}" class="small text-decoration-none" target="_blank" rel="noopener noreferrer">Lihat halaman publik</a>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="jejaringTable">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Website</th>
                        <th class="text-center">Urutan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Update</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $row)
                        <tr>
                            <td>{{ $row->category_label }}</td>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->address ?: '-' }}</td>
                            <td>
                                @if($row->website_url)
                                    <a href="{{ $row->website_url }}" target="_blank" rel="noopener noreferrer">{{ $row->website_url }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">{{ $row->sort_order }}</td>
                            <td class="text-center">
                                @if($row->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $row->updated_at ? $row->updated_at->locale('id')->translatedFormat('d M Y') : '-' }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-nowrap">
                                    <button
                                        class="btn btn-sm px-3 text-white"
                                        style="background-color:#15406A; border-color:#15406A;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#jejaringModal"
                                        data-mode="edit"
                                        data-id="{{ $row->id }}"
                                        data-category="{{ $row->category }}"
                                        data-name="{{ $row->name }}"
                                        data-address="{{ $row->address ?? '' }}"
                                        data-website="{{ $row->website ?? '' }}"
                                        data-sort-order="{{ $row->sort_order }}"
                                        data-status="{{ $row->is_active ? 1 : 0 }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('superadmin.jejaring.destroy', $row) }}" method="POST" class="delete-form d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm px-3 text-white" style="background-color:#dc3545; border-color:#dc3545;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="jejaringModal" tabindex="-1" aria-labelledby="jejaringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jejaringModalLabel">Data Jejaring</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="jejaringForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category" required>
                                <option value="" disabled selected>Pilih kategori</option>
                                @foreach($categories as $key => $meta)
                                    <option value="{{ $key }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Urutan Tampil</label>
                            <input type="number" class="form-control" name="sort_order" min="0" max="9999" value="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" name="name" placeholder="Masukkan nama lembaga/perusahaan/instansi" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="address" rows="4" placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Website</label>
                            <input type="text" class="form-control" name="website" placeholder="https://contoh.go.id">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="is_active" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        $('#jejaringTable').DataTable({
            pageLength: 10,
            order: [[0, 'asc'], [4, 'asc'], [1, 'asc']],
            columnDefs: [
                { orderable: false, targets: [7] }
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                paginate: {
                    previous: 'Sebelumnya',
                    next: 'Berikutnya'
                },
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data jejaring',
                infoEmpty: 'Belum ada data',
                infoFiltered: '(difilter dari _MAX_ data)'
            }
        });
    });

    const jejaringModal = document.getElementById('jejaringModal');
    const jejaringForm = document.getElementById('jejaringForm');
    const jejaringMethodInput = jejaringForm.querySelector('input[name="_method"]');
    const jejaringModalTitle = document.getElementById('jejaringModalLabel');

    jejaringModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';

        jejaringForm.reset();
        jejaringForm.querySelector('select[name="is_active"]').value = '1';
        jejaringForm.querySelector('input[name="sort_order"]').value = '0';

        if (mode === 'edit') {
            const id = button.getAttribute('data-id') || '';
            jejaringModalTitle.textContent = 'Edit Data Jejaring';
            jejaringForm.action = "{{ route('superadmin.jejaring.update', ['jejaringEntry' => '__ID__']) }}".replace('__ID__', id);
            jejaringMethodInput.value = 'PUT';
            jejaringForm.querySelector('select[name="category"]').value = button.getAttribute('data-category') || '';
            jejaringForm.querySelector('input[name="name"]').value = button.getAttribute('data-name') || '';
            jejaringForm.querySelector('textarea[name="address"]').value = button.getAttribute('data-address') || '';
            jejaringForm.querySelector('input[name="website"]').value = button.getAttribute('data-website') || '';
            jejaringForm.querySelector('input[name="sort_order"]').value = button.getAttribute('data-sort-order') || '0';
            jejaringForm.querySelector('select[name="is_active"]').value = button.getAttribute('data-status') || '1';
            return;
        }

        jejaringModalTitle.textContent = 'Tambah Data Jejaring';
        jejaringForm.action = "{{ route('superadmin.jejaring.store') }}";
        jejaringMethodInput.value = 'POST';
    });

    document.querySelectorAll('.delete-form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            Swal.fire({
                title: 'Hapus data jejaring?',
                text: 'Data yang dihapus tidak dapat dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
