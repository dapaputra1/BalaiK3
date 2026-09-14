@extends('layouts.app_admin')

@push('styles')
<style>
    .platform-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
    }

    .platform-badge i {
        font-size: 13px;
    }

    .platform-badge-instagram { background: linear-gradient(135deg, #f58529, #dd2a7b 58%, #8134af); }
    .platform-badge-facebook { background: #1877f2; }
    .platform-badge-youtube { background: #ff0000; }
    .platform-badge-tiktok { background: #101010; }

    .medsos-link {
        color: #18456f;
        text-decoration: none;
        font-weight: 600;
        word-break: break-all;
    }

    .medsos-link:hover {
        text-decoration: underline;
    }

    .medsos-preview-state {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eef5fb;
        color: #18456f;
        font-size: 12px;
        font-weight: 700;
    }

    .medsos-preview-state.is-ready {
        background: #e9f8ef;
        color: #1b8f51;
    }

    .medsos-thumb-box {
        width: 116px;
        height: 150px;
        margin: 0 auto;
        border: 1px solid #d7e2ed;
        border-radius: 12px;
        overflow: hidden;
        background: #f2f6fb;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .medsos-thumb-box iframe {
        width: 100%;
        height: 100%;
        border: 0;
    }

    .medsos-thumb-empty {
        color: #8ca0b4;
        font-size: 11px;
        font-weight: 600;
        text-align: center;
        line-height: 1.3;
    }
</style>
@endpush

@section('content_admin')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Kelola Medsos</h5>
        <small class="text-muted">Kelola link postingan Instagram, Facebook, YouTube, dan TikTok yang tampil di halaman home user.</small>
    </div>
    <button class="btn text-white" style="background-color:#15406A; border-color:#15406A;" data-bs-toggle="modal" data-bs-target="#medsosModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i> Tambah Konten
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
            <table class="table table-striped table-bordered align-middle" id="medsosTable">
                <thead>
                    <tr>
                        <th>Tanggal Update</th>
                        <th>Platform</th>
                        <th>Link Uploadan</th>
                        <th>Upload Oleh</th>
                        <th class="text-center">Preview</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $row)
                        <tr>
                            <td data-order="{{ optional($row->updated_at)->timestamp ?? 0 }}">{{ $row->formatted_date }}</td>
                            <td>
                                <span class="platform-badge platform-badge-{{ $row->platform }}">
                                    <i class="{{ $row->platform_icon }}"></i>
                                    {{ $row->platform_label }}
                                </span>
                            </td>
                            <td style="min-width: 260px;">
                                <a href="{{ $row->post_url }}" target="_blank" rel="noopener noreferrer" class="medsos-link">
                                    {{ \Illuminate\Support\Str::limit($row->post_url, 70) }}
                                </a>
                            </td>
                            <td>{{ $row->uploader_name }}</td>
                            <td class="text-center">
                                @if($row->has_embed)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary btn-preview"
                                            data-bs-toggle="modal"
                                            data-bs-target="#medsosPreviewModal"
                                            data-platform="{{ $row->platform_label }}"
                                            data-preview-url="{{ $row->embed_url }}"
                                            data-post-url="{{ $row->post_url }}">
                                        Lihat preview
                                    </button>
                                @else
                                    <span class="medsos-preview-state">
                                        <i class="bi bi-exclamation-circle"></i> Periksa link
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm px-3 text-white"
                                        style="background-color:#15406A; border-color:#15406A;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#medsosModal"
                                        data-mode="edit"
                                        data-id="{{ $row->id }}"
                                        data-platform="{{ $row->platform }}"
                                        data-url="{{ $row->post_url }}">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <form action="{{ route('superadmin.medsos.destroy', $row) }}" method="POST" class="d-inline delete-form ms-2">
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
                            <td colspan="6" class="text-center text-muted">Belum ada konten media sosial</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="medsosModal" tabindex="-1" aria-labelledby="medsosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" id="medsosForm" class="modal-content">
            @csrf
            <input type="hidden" name="_method" value="POST">

            <div class="modal-header">
                <h5 class="modal-title" id="medsosModalLabel">Konten Media Sosial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Platform</label>
                    <select class="form-select" name="platform" required>
                        <option value="">Pilih platform</option>
                        @foreach($platforms as $key => $platform)
                            <option value="{{ $key }}">{{ $platform['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Link Uploadan</label>
                    <input type="url" class="form-control" name="post_url" placeholder="https://..." required>
                    <small class="text-muted d-block mt-2">
                        Gunakan link publik yang lengkap.
                        Instagram: post/reel, Facebook: post/reel/video, YouTube: watch/shorts/youtu.be, TikTok: link video.
                    </small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="medsosPreviewModal" tabindex="-1" aria-labelledby="medsosPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="medsosPreviewModalLabel">Preview Konten</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="ratio ratio-1x1 rounded overflow-hidden border" style="max-width: 420px; margin: 0 auto;" id="medsosPreviewFrameWrap">
                    <iframe id="medsosPreviewFrame" src="" title="Preview konten media sosial" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
                <div class="text-center mt-3">
                    <a id="medsosPreviewLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">Buka postingan asli</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        $('#medsosTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[0, 'desc']],
            columnDefs: [
                { orderable: false, targets: [4, 5] },
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

    const medsosModal = document.getElementById('medsosModal');
    const medsosForm = document.getElementById('medsosForm');
    const medsosMethod = medsosForm.querySelector('input[name="_method"]');
    const medsosPlatform = medsosForm.querySelector('select[name="platform"]');
    const medsosUrl = medsosForm.querySelector('input[name="post_url"]');
    const medsosModalLabel = document.getElementById('medsosModalLabel');
    const previewModal = document.getElementById('medsosPreviewModal');
    const previewFrame = document.getElementById('medsosPreviewFrame');
    const previewLink = document.getElementById('medsosPreviewLink');

    medsosModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';

        medsosForm.reset();

        if (mode === 'edit') {
            medsosModalLabel.textContent = 'Edit Konten Media Sosial';
            medsosForm.action = "{{ route('superadmin.medsos.index') }}/" + (button.getAttribute('data-id') || '');
            medsosMethod.value = 'PUT';
            medsosPlatform.value = button.getAttribute('data-platform') || '';
            medsosUrl.value = button.getAttribute('data-url') || '';

            if (imageUrl) {
                currentImageTag.src = imageUrl;
                currentImageWrap.classList.add('is-visible');
            }
        } else {
            medsosModalLabel.textContent = 'Tambah Konten Media Sosial';
            medsosForm.action = "{{ route('superadmin.medsos.store') }}";
            medsosMethod.value = 'POST';
        }
    });

    if (previewModal) {
        previewModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const platform = button?.getAttribute('data-platform') || 'Media Sosial';
            const previewUrl = button?.getAttribute('data-preview-url') || '';
            const postUrl = button?.getAttribute('data-post-url') || '#';

            const titleNode = document.getElementById('medsosPreviewModalLabel');
            if (titleNode) {
                titleNode.textContent = `Preview ${platform}`;
            }

            previewFrame.src = previewUrl;
            previewLink.href = postUrl;
        });

        previewModal.addEventListener('hidden.bs.modal', () => {
            previewFrame.src = '';
        });
    }

    document.querySelectorAll('.btn-delete').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();

            const form = button.closest('form');
            Swal.fire({
                title: 'Hapus konten medsos?',
                text: 'Data yang dihapus tidak dapat dikembalikan.',
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
