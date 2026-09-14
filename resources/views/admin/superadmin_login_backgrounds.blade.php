@extends('layouts.app_admin')

@section('content_admin')
@php
    $activeHomePopupCount = $homePopups->where('is_active', true)->count();
@endphp
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Kelola Gambar Aplikasi</h5>
        <small class="text-muted">Atur gambar login dan popup home desktop/mobile dari satu halaman.</small>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackgroundModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Background
        </button>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-1">Gambar Login</h6>
        <small class="text-muted">Upload background login baru dan pilih satu gambar yang aktif.</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 90px;">Preview</th>
                        <th>Nama</th>
                        <th style="width: 220px;" class="text-center">Status</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backgrounds as $background)
                        <tr>
                            <td>
                                <img src="{{ asset($background->image_path) }}" alt="{{ $background->name }}" style="width:70px;height:50px;object-fit:cover;border-radius:8px;border:1px solid #d7e2ed;">
                            </td>
                            <td>{{ $background->name }}</td>
                            <td class="text-center text-nowrap">
                                @if($background->is_active)
                                    <span class="badge text-bg-success">Aktif</span>
                                @else
                                    <form action="{{ route('superadmin.login-backgrounds.activate', $background) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-check2-circle me-1"></i> Aktifkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ asset($background->image_path) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary" title="Lihat">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary ms-1 edit-background-button"
                                        title="Edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editBackgroundModal"
                                        data-action="{{ route('superadmin.login-backgrounds.update', $background) }}"
                                        data-name="{{ $background->name }}"
                                        data-image-url="{{ asset($background->image_path) }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if(!$background->is_active)
                                    <form action="{{ route('superadmin.login-backgrounds.destroy', $background) }}" method="POST" class="d-inline ms-1 delete-background-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-1" disabled title="Background aktif tidak bisa dihapus.">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada background login.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h6 class="mb-1">Popup Home</h6>
            <small class="text-muted">Setiap popup punya gambar desktop dan mobile. Popup aktif akan tampil sebagai slide di home.</small>
        </div>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createHomePopupModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Popup
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 90px;">Urutan</th>
                        <th>Nama</th>
                        <th style="width: 150px;">Desktop</th>
                        <th style="width: 150px;">Mobile</th>
                        <th style="width: 140px;" class="text-center">Status</th>
                        <th style="width: 170px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homePopups as $popup)
                        <tr>
                            <td>{{ $popup->sort_order }}</td>
                            <td>{{ $popup->name }}</td>
                            <td>
                                <img src="{{ asset($popup->desktop_image_path) }}" alt="{{ $popup->name }} desktop" style="width:120px;height:68px;object-fit:cover;border-radius:8px;border:1px solid #d7e2ed;">
                            </td>
                            <td>
                                <img src="{{ asset($popup->mobile_image_path) }}" alt="{{ $popup->name }} mobile" style="width:68px;height:120px;object-fit:cover;border-radius:8px;border:1px solid #d7e2ed;">
                            </td>
                            <td class="text-center">
                                @if($popup->is_active)
                                    <span class="badge text-bg-success">Aktif</span>
                                @else
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <form action="{{ route('superadmin.login-backgrounds.home-popups.toggle', $popup) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            class="btn btn-sm {{ $popup->is_active ? 'btn-outline-warning' : 'btn-outline-primary' }}"
                                            title="{{ $popup->is_active ? ($activeHomePopupCount <= 1 ? 'Minimal harus ada 1 popup aktif.' : 'Nonaktifkan') : 'Aktifkan' }}"
                                            {{ $popup->is_active && $activeHomePopupCount <= 1 ? 'disabled' : '' }}>
                                        <i class="bi {{ $popup->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                    </button>
                                </form>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary ms-1 edit-home-popup-button"
                                        title="Edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editHomePopupModal"
                                        data-action="{{ route('superadmin.login-backgrounds.home-popups.update', $popup) }}"
                                        data-name="{{ $popup->name }}"
                                        data-sort-order="{{ $popup->sort_order }}"
                                        data-is-active="{{ $popup->is_active ? '1' : '0' }}"
                                        data-desktop-url="{{ asset($popup->desktop_image_path) }}"
                                        data-mobile-url="{{ asset($popup->mobile_image_path) }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('superadmin.login-backgrounds.home-popups.destroy', $popup) }}" method="POST" class="d-inline ms-1 delete-home-popup-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-outline-danger"
                                            title="{{ $popup->is_active && $activeHomePopupCount <= 1 ? 'Popup aktif terakhir tidak bisa dihapus.' : 'Hapus' }}"
                                            {{ $popup->is_active && $activeHomePopupCount <= 1 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada popup home.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="createBackgroundModal" tabindex="-1" aria-labelledby="createBackgroundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('superadmin.login-backgrounds.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createBackgroundModalLabel">Tambah Background Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Background</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="100" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">File Gambar</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                    <small class="text-muted d-block mt-2">Format JPG, PNG, atau WEBP. Maks 5MB. Resolusi minimal 800x450 piksel. Rekomendasi 1920x1080 piksel.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload me-1"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editBackgroundModal" tabindex="-1" aria-labelledby="editBackgroundModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" enctype="multipart/form-data" class="modal-content" id="editBackgroundForm">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editBackgroundModalLabel">Edit Background Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Preview Saat Ini</label>
                    <div>
                        <img src="" alt="Preview background login" id="editBackgroundPreview" style="width:180px;height:100px;object-fit:cover;border-radius:8px;border:1px solid #d7e2ed;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Background</label>
                    <input type="text" name="name" class="form-control" id="editBackgroundName" maxlength="100" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ganti File Gambar</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <small class="text-muted d-block mt-2">Kosongkan jika tidak ingin mengganti gambar. Format JPG, PNG, atau WEBP. Maks 5MB. Resolusi minimal 800x450 piksel. Rekomendasi 1920x1080 piksel.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="createHomePopupModal" tabindex="-1" aria-labelledby="createHomePopupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('superadmin.login-backgrounds.home-popups.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createHomePopupModalLabel">Tambah Popup Home</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nama Popup</label>
                        <input type="text" name="popup_name" class="form-control" value="{{ old('popup_name') }}" maxlength="100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0" max="9999">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar Desktop</label>
                        <input type="file" name="popup_desktop" class="form-control @error('popup_desktop') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                        <small class="text-muted d-block mt-2">Landscape. Minimal 800x450 piksel. Rekomendasi 1600x900 piksel.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gambar Mobile</label>
                        <input type="file" name="popup_mobile" class="form-control @error('popup_mobile') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
                        <small class="text-muted d-block mt-2">Portrait. Minimal 320x480 piksel. Rekomendasi 1080x1920 piksel.</small>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="createHomePopupActive" name="is_active" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="createHomePopupActive">Aktifkan popup ini</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan Popup
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editHomePopupModal" tabindex="-1" aria-labelledby="editHomePopupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" enctype="multipart/form-data" class="modal-content" id="editHomePopupForm">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editHomePopupModalLabel">Edit Popup Home</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Preview Desktop</label>
                        <div class="border rounded-3 p-2 bg-light">
                            <img src="" alt="Preview popup desktop" id="editHomePopupDesktopPreview" style="width:100%;height:170px;object-fit:contain;background:#fff;border-radius:8px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Preview Mobile</label>
                        <div class="border rounded-3 p-2 bg-light text-center">
                            <img src="" alt="Preview popup mobile" id="editHomePopupMobilePreview" style="width:auto;max-width:100%;height:170px;object-fit:contain;background:#fff;border-radius:8px;">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama Popup</label>
                        <input type="text" name="popup_name" class="form-control" id="editHomePopupName" maxlength="100" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Urutan</label>
                        <input type="number" name="sort_order" class="form-control" id="editHomePopupSortOrder" min="0" max="9999">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ganti Gambar Desktop</label>
                        <input type="file" name="popup_desktop" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <small class="text-muted d-block mt-2">Landscape. Minimal 800x450 piksel. Rekomendasi 1600x900 piksel.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ganti Gambar Mobile</label>
                        <input type="file" name="popup_mobile" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <small class="text-muted d-block mt-2">Portrait. Minimal 320x480 piksel. Rekomendasi 1080x1920 piksel.</small>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="editHomePopupActive" name="is_active">
                            <label class="form-check-label" for="editHomePopupActive">Popup aktif</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const flashSuccess = @json(session('success'));
        const flashError = @json($errors->first());
        const shouldOpenCreateBackgroundModal = @json($errors->has('image') || ($errors->has('name') && !$errors->has('popup_name')));
        const shouldOpenCreatePopupModal = @json($errors->has('popup_name') || $errors->has('popup_desktop') || $errors->has('popup_mobile'));

        if (shouldOpenCreateBackgroundModal) {
            const modalElement = document.getElementById('createBackgroundModal');
            if (modalElement && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        }

        if (shouldOpenCreatePopupModal) {
            const modalElement = document.getElementById('createHomePopupModal');
            if (modalElement && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        }

        if (flashSuccess && window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: flashSuccess,
                confirmButtonColor: '#15406A'
            });
        }

        if (flashError && window.Swal) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: flashError,
                confirmButtonColor: '#15406A'
            });
        }

        document.querySelectorAll('.edit-background-button').forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('editBackgroundForm');
                const nameInput = document.getElementById('editBackgroundName');
                const preview = document.getElementById('editBackgroundPreview');

                if (form) {
                    form.action = button.getAttribute('data-action') || '';
                }
                if (nameInput) {
                    nameInput.value = button.getAttribute('data-name') || '';
                }
                if (preview) {
                    preview.src = button.getAttribute('data-image-url') || '';
                }
            });
        });

        document.querySelectorAll('.edit-home-popup-button').forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('editHomePopupForm');
                const nameInput = document.getElementById('editHomePopupName');
                const sortOrderInput = document.getElementById('editHomePopupSortOrder');
                const activeInput = document.getElementById('editHomePopupActive');
                const desktopPreview = document.getElementById('editHomePopupDesktopPreview');
                const mobilePreview = document.getElementById('editHomePopupMobilePreview');

                if (form) {
                    form.action = button.getAttribute('data-action') || '';
                }
                if (nameInput) {
                    nameInput.value = button.getAttribute('data-name') || '';
                }
                if (sortOrderInput) {
                    sortOrderInput.value = button.getAttribute('data-sort-order') || '0';
                }
                if (activeInput) {
                    activeInput.checked = (button.getAttribute('data-is-active') || '0') === '1';
                }
                if (desktopPreview) {
                    desktopPreview.src = button.getAttribute('data-desktop-url') || '';
                }
                if (mobilePreview) {
                    mobilePreview.src = button.getAttribute('data-mobile-url') || '';
                }
            });
        });

        const bindDeleteConfirm = (selector, title, text) => {
            document.querySelectorAll(selector).forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!window.Swal) {
                        form.submit();
                        return;
                    }

                    const result = await Swal.fire({
                        icon: 'warning',
                        title,
                        text,
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d'
                    });

                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        };

        bindDeleteConfirm('.delete-background-form', 'Hapus background?', 'Data yang dihapus tidak dapat dikembalikan.');
        bindDeleteConfirm('.delete-home-popup-form', 'Hapus popup?', 'Slide popup yang dihapus tidak dapat dikembalikan.');
    });
</script>
@endpush
@endsection
