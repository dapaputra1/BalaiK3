@extends('layouts.app_admin')

@section('title', 'Profil Saya - Pelayanan K3')

@push('styles')
<style>
    .profile-shell {
        max-width: 1020px;
        margin: 0 auto;
        padding-inline: 4px;
        font-size: 14px;
        line-height: 1.42;
    }

    .profile-hero {
        background: linear-gradient(135deg, #0f3b63 0%, #15406A 52%, #2b6aa4 100%);
        border-radius: 18px;
        padding: 20px 22px;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 14px 32px rgba(15, 59, 99, 0.14);
    }

    .profile-hero::before,
    .profile-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.08);
    }

    .profile-hero::before {
        width: 180px;
        height: 180px;
        top: -90px;
        right: -56px;
    }

    .profile-hero::after {
        width: 132px;
        height: 132px;
        bottom: -68px;
        left: -34px;
    }

    .profile-hero-content {
        position: relative;
        z-index: 1;
    }

    .profile-avatar {
        width: 62px;
        height: 62px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.18);
        font-size: 24px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }

    .profile-role-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.15);
        font-size: 11px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .profile-user-name {
        font-size: clamp(1.12rem, 1.02vw, 1.42rem);
        line-height: 1.25;
    }

    .profile-hero-note {
        font-size: 0.84rem;
    }

    .profile-active-email {
        font-size: 0.88rem;
        line-height: 1.4;
        word-break: break-word;
    }

    .profile-panel {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(19, 56, 92, 0.08);
        overflow: hidden;
    }

    .profile-panel .card-body {
        padding: 22px;
    }

    .profile-section-title {
        font-size: 0.94rem;
        font-weight: 600;
        color: #153b61;
        margin-bottom: 4px;
    }

    .profile-section-subtitle {
        color: #6b7a89;
        font-size: 0.82rem;
        margin-bottom: 0;
        line-height: 1.55;
    }

    .profile-form-label {
        font-weight: 600;
        color: #163b62;
        margin-bottom: 6px;
        font-size: 0.88rem;
    }

    .profile-input,
    .profile-select {
        min-height: 44px;
        border-radius: 12px;
        border: 1px solid #d7e1eb;
        background: #fbfdff;
        padding-left: 14px;
        padding-right: 14px;
        font-size: 0.9rem;
    }

    .profile-input[readonly] {
        background: #f2f6fa;
        color: #617182;
    }

    .profile-password-wrap {
        position: relative;
    }

    .profile-password-wrap .profile-input {
        padding-right: 40px;
    }

    .profile-password-toggle {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #73879a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        z-index: 2;
    }

    .profile-password-toggle:focus {
        outline: none;
        box-shadow: none;
    }

    .profile-info-card {
        border-radius: 16px;
        background: linear-gradient(180deg, #f9fbfd 0%, #f1f6fb 100%);
        border: 1px solid #dfe7ef;
        padding: 18px;
        height: 100%;
    }

    .profile-info-item + .profile-info-item {
        margin-top: 14px;
    }

    .profile-info-label {
        display: block;
        font-size: 11px;
        letter-spacing: 0.04em;
        color: #6a7c8f;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .profile-info-value {
        font-size: 13px;
        font-weight: 600;
        color: #173c61;
        line-height: 1.45;
    }

    .signature-pad {
        border: 1px dashed #b7c9da;
        border-radius: 14px;
        padding: 10px;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
    }

    .signature-canvas {
        width: 100%;
        max-width: 100%;
        height: 160px;
        display: block;
        border-radius: 12px;
        background: #fff;
        box-shadow: inset 0 0 0 1px rgba(21, 64, 106, 0.06);
        touch-action: none;
        cursor: crosshair;
    }

    .signature-preview {
        width: 100%;
        min-height: 160px;
        border-radius: 14px;
        border: 1px solid #dfe7ef;
        background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
        display: grid;
        place-items: center;
        padding: 12px;
        overflow: hidden;
    }

    .signature-preview img {
        max-width: 100%;
        max-height: 126px;
        object-fit: contain;
        background: #fff;
    }

    .signature-empty {
        width: 100%;
        max-width: 200px;
        color: #7b8c9d;
        text-align: center;
        font-size: 0.86rem;
        line-height: 1.5;
    }

    .profile-panel .form-text,
    .profile-action-bar .small {
        font-size: 0.8rem;
        line-height: 1.5;
    }

    .profile-save-btn {
        font-size: 0.86rem;
    }

    .profile-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-top: 14px;
        border-top: 1px solid #e8eef5;
        margin-top: 4px;
    }

    .profile-save-btn {
        min-width: 160px;
        min-height: 42px;
        border-radius: 12px;
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .profile-shell {
            font-size: 13px;
        }

        .profile-panel .card-body,
        .profile-hero {
            padding: 18px;
        }

        .profile-user-name {
            font-size: 1.08rem;
        }

        .profile-section-title {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 767.98px) {
        .profile-shell {
            padding-inline: 0;
        }

        .profile-hero {
            border-radius: 14px;
            padding: 16px;
        }

        .profile-avatar {
            width: 54px;
            height: 54px;
            font-size: 20px;
            border-radius: 14px;
        }

        .profile-role-badge {
            margin-bottom: 8px !important;
        }

        .profile-user-name {
            font-size: 1rem;
        }

        .profile-active-email {
            font-size: 0.84rem;
        }

        .profile-section-title {
            font-size: 0.88rem;
        }

        .profile-section-subtitle {
            font-size: 0.8rem;
        }

        .profile-action-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .profile-save-btn {
            width: 100%;
        }
    }
</style>
@endpush

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

    $roleLabel = $roleLabelMap[$user->role] ?? strtoupper((string) $user->role);
    $initials = collect(explode(' ', trim((string) $user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
        ->implode('');
@endphp

<div data-workflow-topbar data-title="Profil Saya" data-subtitle="Kelola identitas akun petugas, akses login, dan tanda tangan digital."></div>

<div class="profile-shell">
    <div class="profile-hero mb-4">
        <div class="profile-hero-content">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar">{{ $initials !== '' ? $initials : 'PG' }}</div>
                    <div>
                        <div class="profile-role-badge mb-2">
                            <i class="bi bi-shield-check"></i>
                            <span>{{ $roleLabel }}</span>
                        </div>
                        <h3 class="mb-1 fw-semibold profile-user-name">{{ $user->name }}</h3>
                        <p class="mb-0 text-white-50 profile-hero-note">Perbarui data profil tanpa perlu lewat halaman kelola petugas.</p>
                    </div>
                </div>
                <div class="text-lg-end">
                    <div class="small text-white-50 mb-1">Email login aktif</div>
                    <div class="fw-semibold profile-active-email">{{ $user->email }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">{{ $errors->first() }}</div>
    @endif

    <div class="card profile-panel">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="profile-info-card">
                        <div class="profile-section-title">Ringkasan Akun</div>
                        <p class="profile-section-subtitle">Informasi dasar akun petugas yang sedang digunakan.</p>

                        <div class="profile-info-item mt-4">
                            <span class="profile-info-label">Nama</span>
                            <div class="profile-info-value">{{ $user->name }}</div>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">Role</span>
                            <div class="profile-info-value">{{ $roleLabel }}</div>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">Email</span>
                            <div class="profile-info-value" style="word-break: break-word;">{{ $user->email }}</div>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">NIP</span>
                            <div class="profile-info-value">{{ $user->nip ?: '-' }}</div>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">Jabatan</span>
                            <div class="profile-info-value">{{ $user->jabatan ?: '-' }}</div>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">Golongan</span>
                            <div class="profile-info-value">{{ $user->golongan ?: '-' }}</div>
                        </div>
                        <div class="profile-info-item">
                            <span class="profile-info-label">Status Tanda Tangan</span>
                            <div class="profile-info-value">{{ $user->signature_url ? 'Sudah tersimpan' : 'Belum ada tanda tangan' }}</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="mb-4">
                        <div class="profile-section-title">Kelola Profil</div>
                        <p class="profile-section-subtitle">Edit nama, email, password opsional, dan tanda tangan digital.</p>
                    </div>

                    <form method="POST" action="{{ route('petugas.profile.update') }}" id="profileForm">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="profile-form-label" for="name">Nama Lengkap</label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control profile-input"
                                    value="{{ old('name', $user->name) }}"
                                    placeholder="Masukkan nama lengkap"
                                    required
                                >
                            </div>
                            <div class="col-md-6">
                                <label class="profile-form-label" for="email">Email</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control profile-input"
                                    value="{{ old('email', $user->email) }}"
                                    placeholder="nama@domain.com"
                                    required
                                >
                            </div>
                            <div class="col-md-6">
                                <label class="profile-form-label" for="password">Password Baru</label>
                                <div class="profile-password-wrap">
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control profile-input"
                                        placeholder="Kosongkan jika tidak diubah"
                                    >
                                    <button type="button" class="profile-password-toggle" data-toggle-password="password" aria-label="Tampilkan password baru">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Opsional. Isi hanya jika ingin mengganti password.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="profile-form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                                <div class="profile-password-wrap">
                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        class="form-control profile-input"
                                        placeholder="Ulangi password baru"
                                    >
                                    <button type="button" class="profile-password-toggle" data-toggle-password="password_confirmation" aria-label="Tampilkan konfirmasi password baru">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="profile-form-label" for="role_display">Role</label>
                                <input
                                    type="text"
                                    id="role_display"
                                    class="form-control profile-input"
                                    value="{{ $roleLabel }}"
                                    readonly
                                >
                            </div>
                            <div class="col-md-6">
                                <label class="profile-form-label" for="nip">NIP</label>
                                <input
                                    type="text"
                                    id="nip"
                                    name="nip"
                                    class="form-control profile-input"
                                    value="{{ old('nip', $user->nip) }}"
                                    placeholder="Contoh: 19791003 200912 1 002"
                                >
                            </div>
                            <div class="col-md-6">
                                <label class="profile-form-label" for="jabatan">Jabatan</label>
                                <input
                                    type="text"
                                    id="jabatan"
                                    name="jabatan"
                                    class="form-control profile-input"
                                    value="{{ old('jabatan', $user->jabatan) }}"
                                    placeholder="Jabatan petugas"
                                >
                            </div>
                            <div class="col-md-6">
                                <label class="profile-form-label" for="golongan">Golongan</label>
                                <input
                                    type="text"
                                    id="golongan"
                                    name="golongan"
                                    class="form-control profile-input"
                                    value="{{ old('golongan', $user->golongan) }}"
                                    placeholder="Contoh: IVa"
                                >
                            </div>
                        </div>

                        <div class="row g-4 mt-1">
                            <div class="col-xl-7">
                                <label class="profile-form-label">Tanda Tangan Baru</label>
                                <div class="signature-pad">
                                    <canvas id="signatureCanvas" class="signature-canvas" width="640" height="160"></canvas>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="signatureClear">
                                        <i class="bi bi-eraser me-1"></i> Bersihkan Area
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="signatureRemoveButton">
                                        <i class="bi bi-trash3 me-1"></i> Hapus Tanda Tangan Tersimpan
                                    </button>
                                    <span class="small text-muted">Gambar tanda tangan pada area di atas jika ingin mengganti.</span>
                                </div>
                            </div>
                            <div class="col-xl-5">
                                <label class="profile-form-label">Preview Tanda Tangan</label>
                                <div class="signature-preview" id="signaturePreviewWrap">
                                    <div class="signature-empty {{ $user->signature_url ? 'd-none' : '' }}" id="signatureEmpty">
                                        <i class="bi bi-pen d-block mb-2 fs-3"></i>
                                        Belum ada tanda tangan tersimpan.
                                    </div>
                                    @if($user->signature_url)
                                        <img src="{{ $user->signature_url }}" alt="Preview tanda tangan" id="signaturePreview">
                                    @else
                                        <img src="" alt="Preview tanda tangan" id="signaturePreview" class="d-none">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="signature_data" id="signatureData">
                        <input type="hidden" name="signature_remove" id="signatureRemove" value="0">

                        <div class="profile-action-bar">
                            <div class="small text-muted">
                                Password hanya diperbarui jika field diisi. Tanda tangan lama tetap dipakai jika Anda tidak mengganti atau menghapusnya.
                            </div>
                            <button type="submit" class="btn btn-primary profile-save-btn">
                                <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('profileForm');
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas?.getContext('2d');
        const signatureDataInput = document.getElementById('signatureData');
        const signatureRemoveInput = document.getElementById('signatureRemove');
        const clearButton = document.getElementById('signatureClear');
        const removeButton = document.getElementById('signatureRemoveButton');
        const preview = document.getElementById('signaturePreview');
        const previewEmpty = document.getElementById('signatureEmpty');

        if (!form || !canvas || !ctx || !signatureDataInput || !signatureRemoveInput) {
            return;
        }

        let isDrawing = false;
        let hasDrawn = false;

        const resizeCanvas = () => {
            const rect = canvas.getBoundingClientRect();
            const ratio = window.devicePixelRatio || 1;
            const width = Math.max(Math.round(rect.width * ratio), 1);
            const height = Math.max(Math.round(rect.height * ratio), 1);

            if (canvas.width === width && canvas.height === height) {
                return;
            }

            canvas.width = width;
            canvas.height = height;
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#111111';
            hasDrawn = false;
            signatureDataInput.value = '';
        };

        const prepareCanvas = () => {
            resizeCanvas();
            ctx.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#111111';
            hasDrawn = false;
        };

        const setPreviewState = (url) => {
            if (!preview) return;

            if (url) {
                preview.src = url;
                preview.classList.remove('d-none');
                previewEmpty?.classList.add('d-none');
                return;
            }

            preview.src = '';
            preview.classList.add('d-none');
            previewEmpty?.classList.remove('d-none');
        };

        const getPoint = (event) => {
            const rect = canvas.getBoundingClientRect();
            return {
                x: event.clientX - rect.left,
                y: event.clientY - rect.top,
            };
        };

        prepareCanvas();
        window.addEventListener('resize', prepareCanvas);

        document.querySelectorAll('[data-toggle-password]').forEach((button) => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-toggle-password');
                const input = targetId ? document.getElementById(targetId) : null;
                const icon = button.querySelector('i');
                if (!input || !icon) {
                    return;
                }

                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                icon.classList.toggle('bi-eye', !isHidden);
                icon.classList.toggle('bi-eye-slash', isHidden);
            });
        });

        canvas.addEventListener('pointerdown', (event) => {
            const point = getPoint(event);
            ctx.beginPath();
            ctx.moveTo(point.x, point.y);
            isDrawing = true;
            hasDrawn = true;
            signatureRemoveInput.value = '0';
            signatureDataInput.value = '';
            setPreviewState('');
            canvas.setPointerCapture(event.pointerId);
        });

        canvas.addEventListener('pointermove', (event) => {
            if (!isDrawing) {
                return;
            }

            const point = getPoint(event);
            ctx.lineTo(point.x, point.y);
            ctx.stroke();
        });

        const stopDrawing = (event) => {
            if (!isDrawing) {
                return;
            }

            isDrawing = false;
            if (typeof event.pointerId !== 'undefined') {
                canvas.releasePointerCapture(event.pointerId);
            }
        };

        canvas.addEventListener('pointerup', stopDrawing);
        canvas.addEventListener('pointerleave', stopDrawing);
        canvas.addEventListener('pointercancel', stopDrawing);

        clearButton?.addEventListener('click', () => {
            prepareCanvas();
            signatureDataInput.value = '';
        });

        removeButton?.addEventListener('click', () => {
            prepareCanvas();
            signatureDataInput.value = '';
            signatureRemoveInput.value = '1';
            setPreviewState('');
        });

        form.addEventListener('submit', () => {
            if (hasDrawn) {
                signatureDataInput.value = canvas.toDataURL('image/png');
                signatureRemoveInput.value = '0';
            }
        });
    })();
</script>
@endpush
