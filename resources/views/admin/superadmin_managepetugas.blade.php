@extends('layouts.app_admin')

@section('content_admin')
@php
    $roleLabelMap = [
        'superadmin' => 'Superadmin',
        'admin' => 'Administrator',
        'ma' => 'Manajemen Administrasi',
        'mp' => 'MP',
        'mt' => 'MT',
        'penyelia' => 'Penyelia',
        'pcu' => 'PCU',
        'analis' => 'Analis',
        'qc' => 'QC',
        'user' => 'User',
    ];
    $roleFilterOptions = collect($allowedRoles ?? [])
        ->mapWithKeys(fn ($role) => [$role => $roleLabelMap[$role] ?? strtoupper($role)])
        ->sort()
        ->all();
@endphp
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Kelola Petugas</h5>
        <small class="text-muted">Tambah, ubah data petugas, role, dan data kepala balai</small>
    </div>
    <button class="btn text-white" style="background-color:#15406A; border-color:#15406A;" data-bs-toggle="modal" data-bs-target="#petugasModal" data-mode="create">
        <i class="bi bi-person-plus me-1"></i> Tambah Petugas
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

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h6 class="mb-0">Kelola Kepala Balai</h6>
                <small class="text-muted">Nama ini dipakai pada dokumen yang menampilkan tanda tangan elektronik kepala balai.</small>
            </div>
        </div>
        <form action="{{ route('superadmin.petugas.kepala-balai.update') }}" method="POST" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-10">
                <label class="form-label">Nama Kepala Balai</label>
                <input
                    type="text"
                    class="form-control"
                    name="nama"
                    value="{{ old('nama', $kepalaBalai['nama'] ?? '') }}"
                    placeholder="Nama kepala balai"
                    required
                >
            </div>
            <input type="hidden" name="nip" value="{{ old('nip', $kepalaBalai['nip'] ?? '') }}">
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn w-100 text-white" style="background-color:#15406A; border-color:#15406A;">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h6 class="mb-0">Daftar Petugas</h6>
                <small class="text-muted">Filter data berdasarkan role petugas.</small>
            </div>
            <div style="min-width: 240px;">
                <label for="roleFilter" class="form-label small mb-1">Filter Role</label>
                <select class="form-select form-select-sm" id="roleFilter">
                    <option value="">Semua Role</option>
                    @foreach($roleFilterOptions as $roleLabel)
                        <option value="{{ $roleLabel }}">
                            {{ $roleLabel }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="petugasTable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Jabatan</th>
                        <th>Gol.</th>
                        <th>Email</th>
                        <th class="text-center">TTD</th>
                        <th class="text-center">Role</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($petugas as $row)
                        <tr>
                            <td>{{ $row->name }}</td>
                            <td>{{ $row->nip ?? '-' }}</td>
                            <td>{{ $row->jabatan ?? '-' }}</td>
                            <td>{{ $row->golongan ?? '-' }}</td>
                            <td>{{ $row->email }}</td>
                            <td class="text-center">
                                @if($row->signature_url)
                                    <img src="{{ $row->signature_url }}" alt="TTD" style="max-height: 40px; background: #fff;">
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center"><span class="badge bg-info text-dark">{{ $roleLabelMap[$row->role] ?? strtoupper($row->role) }}</span></td>
                            <td class="text-center">
                                <button class="btn btn-sm px-3 text-white"
                                        style="background-color:#15406A; border-color:#15406A;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#petugasModal"
                                        data-mode="edit"
                                        data-id="{{ $row->id }}"
                                        data-name="{{ $row->name }}"
                                        data-nip="{{ $row->nip }}"
                                        data-jabatan="{{ $row->jabatan }}"
                                        data-golongan="{{ $row->golongan }}"
                                        data-email="{{ $row->email }}"
                                        data-role="{{ $row->role }}"
                                        data-signature="{{ $row->signature_url }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('superadmin.petugas.destroy', $row) }}" method="POST" class="d-inline delete-form ms-2">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="confirm" value="1">
                                    <button type="button" class="btn btn-sm px-3 btn-delete text-white"
                                            style="background-color:#dc3545; border-color:#dc3545;">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada petugas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Petugas -->
<div class="modal fade" id="petugasModal" tabindex="-1" aria-labelledby="petugasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="petugasModalLabel">Petugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="petugasForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="name" placeholder="Nama petugas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="email@domain.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control" name="nip" placeholder="Contoh: 19791003 200912 1 002">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" class="form-control" name="jabatan" placeholder="Jabatan petugas">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Golongan</label>
                        <input type="text" class="form-control" name="golongan" placeholder="Contoh: IVa">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="admin">Administrator</option>
                            <option value="ma">Manajemen Administrasi</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="mp">MP</option>
                            <option value="mt">MT</option>
                            <option value="penyelia">Penyelia</option>
                            <option value="pcu">PCU</option>
                            <option value="analis">Analis</option>
                            <option value="qc">QC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru <small class="text-muted">(opsional, kosongkan jika tidak diubah)</small></label>
                        <input type="password" class="form-control" name="password" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="password_confirmation" minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanda Tangan</label>
                        <div class="border rounded p-2 bg-light">
                            <canvas id="signatureCanvas" width="420" height="160" class="w-100" style="max-width: 420px; background: #fff;"></canvas>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-signature-clear>Hapus</button>
                            <span class="small text-muted">Gambar tanda tangan pada area di atas.</span>
                        </div>
                        <div class="mt-2 d-none" data-signature-preview-wrap>
                            <div class="small text-muted mb-1">Tanda tangan tersimpan</div>
                            <img src="" alt="Tanda tangan" class="border rounded" style="max-width: 220px; background: #fff;" data-signature-preview>
                        </div>
                        <input type="hidden" name="signature_data" id="signatureData">
                        <input type="hidden" name="signature_remove" id="signatureRemove" value="0">
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

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    const modal = document.getElementById('petugasModal');
    const form = document.getElementById('petugasForm');
    const methodInput = form.querySelector('input[name="_method"]');
    const nameInput = form.querySelector('input[name="name"]');
    const emailInput = form.querySelector('input[name="email"]');
    const nipInput = form.querySelector('input[name="nip"]');
    const jabatanInput = form.querySelector('input[name="jabatan"]');
    const golonganInput = form.querySelector('input[name="golongan"]');
    const roleSelect = form.querySelector('select[name="role"]');
    const passwordInput = form.querySelector('input[name="password"]');
    const passwordConfirmationInput = form.querySelector('input[name="password_confirmation"]');
    const modalTitle = document.getElementById('petugasModalLabel');
    const signatureCanvas = document.getElementById('signatureCanvas');
    const signatureData = document.getElementById('signatureData');
    const signatureRemove = document.getElementById('signatureRemove');
    const signatureClear = document.querySelector('[data-signature-clear]');
    const signaturePreviewWrap = document.querySelector('[data-signature-preview-wrap]');
    const signaturePreview = document.querySelector('[data-signature-preview]');
    const roleFilter = document.getElementById('roleFilter');
    const signatureCtx = signatureCanvas?.getContext('2d');
    let isDrawing = false;
    let hasDrawn = false;
    let petugasTable = null;

    const resetSignatureCanvas = () => {
        if (!signatureCanvas || !signatureCtx) return;
        signatureCtx.setTransform(1, 0, 0, 1, 0, 0);
        signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        signatureCtx.lineWidth = 2;
        signatureCtx.lineCap = 'round';
        signatureCtx.lineJoin = 'round';
        signatureCtx.strokeStyle = '#111';
        hasDrawn = false;
    };

    const setSignaturePreview = (url) => {
        if (!signaturePreviewWrap || !signaturePreview) return;
        if (url) {
            signaturePreview.src = url;
            signaturePreviewWrap.classList.remove('d-none');
        } else {
            signaturePreview.src = '';
            signaturePreviewWrap.classList.add('d-none');
        }
    };

    const getCanvasPoint = (event) => {
        const rect = signatureCanvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        return { x, y };
    };

    if (signatureCanvas) {
        signatureCanvas.addEventListener('pointerdown', (event) => {
            if (!signatureCtx) return;
            const { x, y } = getCanvasPoint(event);
            signatureCtx.beginPath();
            signatureCtx.moveTo(x, y);
            isDrawing = true;
            hasDrawn = true;
            if (signatureRemove) signatureRemove.value = '0';
            setSignaturePreview('');
            signatureCanvas.setPointerCapture(event.pointerId);
        });
        signatureCanvas.addEventListener('pointermove', (event) => {
            if (!isDrawing || !signatureCtx) return;
            const { x, y } = getCanvasPoint(event);
            signatureCtx.lineTo(x, y);
            signatureCtx.stroke();
        });
        const stopDrawing = (event) => {
            if (!isDrawing) return;
            isDrawing = false;
            signatureCanvas.releasePointerCapture(event.pointerId);
        };
        signatureCanvas.addEventListener('pointerup', stopDrawing);
        signatureCanvas.addEventListener('pointerleave', stopDrawing);
        signatureCanvas.addEventListener('pointercancel', stopDrawing);
    }

    $(function () {
        petugasTable = $('#petugasTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[6, 'asc'], [0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [5, 7] },
                { className: 'text-center', targets: [3, 5, 6, 7] },
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

    roleFilter?.addEventListener('change', () => {
        if (!petugasTable) return;

        const selectedRole = roleFilter.value.trim();

        if (selectedRole === '') {
            petugasTable
                .column(6)
                .search('')
                .order([[6, 'asc'], [0, 'asc']])
                .draw();
            return;
        }

        const escapedRole = $.fn.dataTable.util.escapeRegex(selectedRole);
        petugasTable
            .column(6)
            .search(`^${escapedRole}$`, true, false)
            .order([[0, 'asc']])
            .draw();
    });

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';

        if (mode === 'edit') {
            modalTitle.textContent = 'Edit Petugas';
            form.action = "{{ route('superadmin.petugas.index') }}/" + button.getAttribute('data-id');
            methodInput.value = 'PUT';
            nameInput.value = button.getAttribute('data-name');
            nipInput.value = button.getAttribute('data-nip') || '';
            jabatanInput.value = button.getAttribute('data-jabatan') || '';
            golonganInput.value = button.getAttribute('data-golongan') || '';
            emailInput.value = button.getAttribute('data-email');
            roleSelect.value = button.getAttribute('data-role');
            passwordInput.value = '';
            passwordConfirmationInput.value = '';
            passwordInput.removeAttribute('required');
            passwordConfirmationInput.removeAttribute('required');
            setSignaturePreview(button.getAttribute('data-signature'));
        } else {
            modalTitle.textContent = 'Tambah Petugas';
            form.action = "{{ route('superadmin.petugas.store') }}";
            methodInput.value = 'POST';
            nameInput.value = '';
            nipInput.value = '';
            jabatanInput.value = '';
            golonganInput.value = '';
            emailInput.value = '';
            roleSelect.value = 'admin';
            passwordInput.value = '';
            passwordConfirmationInput.value = '';
            passwordInput.setAttribute('required', 'required');
            passwordConfirmationInput.setAttribute('required', 'required');
            setSignaturePreview('');
        }

        if (signatureData) signatureData.value = '';
        if (signatureRemove) signatureRemove.value = '0';
        resetSignatureCanvas();
    });

    signatureClear?.addEventListener('click', () => {
        resetSignatureCanvas();
        if (signatureData) signatureData.value = '';
        if (signatureRemove) signatureRemove.value = '1';
        setSignaturePreview('');
    });

    form?.addEventListener('submit', () => {
        if (!signatureCanvas || !signatureData || !signatureRemove) return;
        if (hasDrawn) {
            signatureData.value = signatureCanvas.toDataURL('image/png');
            signatureRemove.value = '0';
        } else if (signatureData.value) {
            signatureData.value = '';
        }
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const formEl = btn.closest('form');
            Swal.fire({
                title: 'Hapus petugas?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    formEl.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection
