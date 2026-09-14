@extends('layouts.app_admin')

@section('content_admin')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Feedback Pengguna</h5>
        <small class="text-muted">Kelola dan balas feedback</small>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Total Feedback</div>
                <div class="h5 mb-0">{{ $stats['total'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Belum Dibalas</div>
                <div class="h5 mb-0" style="color:orange;">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Terbalas</div>
                <div class="h5 mb-0 text-success">{{ $stats['replied'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<form method="GET" class="card border-0 shadow-sm mb-3">
    <div class="card-body row g-2">
        <div class="col-sm-6 col-md-3">
            <label class="form-label small text-muted mb-1">Filter Rating</label>
            <select name="rating" class="form-select form-select-sm">
                <option value="">Semua</option>
                @for($i=5;$i>=1;$i--)
                    <option value="{{ $i }}" {{ ($filters['rating'] ?? null)==$i ? 'selected' : '' }}>{{ $i }} ★</option>
                @endfor
            </select>
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label small text-muted mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="all" {{ ($filters['status'] ?? 'all')==='all' ? 'selected' : '' }}>Semua</option>
                <option value="pending" {{ ($filters['status'] ?? 'all')==='pending' ? 'selected' : '' }}>Belum dibalas</option>
                <option value="replied" {{ ($filters['status'] ?? 'all')==='replied' ? 'selected' : '' }}>Terbalas</option>
            </select>
        </div>
        <div class="col-sm-12 col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary btn-sm" style="background-color:#15406A; color:white;">Terapkan</button>
            <a href="{{ route('superadmin.feedback.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @forelse($feedbacks as $item)
    @php
        $created = \Carbon\Carbon::parse($item->created_at)->timezone('Asia/Jakarta');
        $replyTime = $item->reply?->created_at
            ? \Carbon\Carbon::parse($item->reply->created_at)->timezone('Asia/Jakarta')
            : null;
    @endphp

    <div class="border rounded-3 p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start gap-3">
            {{-- KIRI: rating + user + pesan --}}
            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi {{ $i <= ($item->rating ?? 0) ? 'bi-star-fill' : 'bi-star text-muted' }}" style="{{ $i <= ($item->rating ?? 0) ? 'color:#15406A;' : '' }}"></i>
                    @endfor
                    <br>
                    <span class="fw-semibold">{{ $item->user->name ?? 'User #' . $item->user_id }}</span>
                    <span class="text-muted small">{{ $item->user->email ?? '' }}</span>
                </div>

                {{-- Pesan TANPA GAP --}}
                <p class="mb-0">{{ $item->message }}</p>
            </div>

            {{-- KANAN: tanggal + tombol --}}
            <div class="d-flex flex-column align-items-end gap-2">
                <span class="text-muted small">{{ $created->format('d M Y H:i') }} WIB</span>

                <button class="btn btn-sm text-white"
                        style="background-color:#15406A; border-color:#15406A;"
                        data-bs-toggle="modal"
                        data-bs-target="#replyModal"
                        data-id="{{ $item->id }}"
                        data-user="{{ $item->user->name ?? 'User' }}"
                        data-message="{{ $item->message }}"
                        data-rating="{{ $item->rating }}"
                        data-reply="{{ $item->reply->reply_message ?? '' }}">
                    <i class="bi bi-reply-fill"></i> {{ $item->reply ? 'Edit' : 'Balas' }}
                </button>
            </div>
        </div>

        {{-- Balasan --}}
        @if($item->reply)
            <div class="p-3 rounded-3 bg-light mt-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-semibold" style="color:#15406A;">Balasan Petugas</span>
                    <div class="text-end">
                        @if($item->reply->admin?->name)
                            <span class="text-muted small me-2">({{ $item->reply->admin->name }})</span>
                        @endif
                        @if($replyTime)
                            <span class="text-muted small">{{ $replyTime->format('d M Y H:i') }} WIB</span>
                        @endif
                    </div>
                </div>
                <p class="mb-0" style="color:#15406A;">{{ $item->reply->reply_message }}</p>
            </div>
        @endif
    </div>
@empty
    <div class="text-center text-muted py-4">Belum ada feedback.</div>
@endforelse

    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" id="replyForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Balas Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <div class="small text-muted">Pengguna</div>
                        <div id="replyUser" class="fw-semibold"></div>
                    </div>
                    <div class="mb-2">
                        <div class="small text-muted">Rating</div>
                        <div id="replyRating"></div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted">Pesan</div>
                        <div id="replyMessage" class="border rounded p-2 bg-light"></div>
                    </div>
                    <div class="mb-3">
                        <label style="width: 50vw;" class="form-label" >Balasan</label>
                        <textarea name="reply_message" id="replyContent" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background-color:#15406A; border-color:#15406A;">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const replyModal = document.getElementById('replyModal');
    const replyForm = document.getElementById('replyForm');
    const replyUser = document.getElementById('replyUser');
    const replyRating = document.getElementById('replyRating');
    const replyMessage = document.getElementById('replyMessage');
    const replyContent = document.getElementById('replyContent');

    replyModal?.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        if (!button) return;
        const id = button.getAttribute('data-id');
        const user = button.getAttribute('data-user');
        const rating = button.getAttribute('data-rating');
        const message = button.getAttribute('data-message');
        const reply = button.getAttribute('data-reply') || '';

        replyForm.action = "{{ url('/superadmin/feedback') }}/" + id + "/reply";
        replyUser.textContent = user || '-';
        replyRating.innerHTML = '';
        const ratingVal = parseInt(rating, 10) || 0;
        for (let i = 1; i <= 5; i++) {
            const icon = document.createElement('i');
            icon.className = 'bi ' + (i <= ratingVal ? 'bi-star-fill' : 'bi-star text-muted');
            if (i <= ratingVal) icon.style.color = '#15406A';
            replyRating.appendChild(icon);
        }
        replyMessage.textContent = message || '-';
        replyContent.value = reply;
    });
</script>
@endpush
@endsection
