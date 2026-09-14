@extends('layouts.app')

@section('content')

<div class="container my-5">
    <div class="row gx-4 gy-4">

        {{-- Kolom kiri: feedback & saran --}}
        <div class="col-12 col-lg-8">

            {{-- Card atas: judul & deskripsi --}}
            <div class="card shadow-sm rounded-3 mb-4 border-0 reveal">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-2">Feedback & Saran</h5>
                    <p class="card-text text-muted mb-0">
                        Kami sangat menghargai masukan Anda. Pendapat Anda membantu kami untuk terus berkembang
                        dan memberikan layanan yang lebih baik.
                    </p>
                </div>
            </div>

            {{-- Card bawah: form rating + pesan --}}
            <div class="card shadow-sm rounded-3 border-0 reveal">
                <div class="card-body">
                    {{-- Pertanyaan --}}
                    <form id="feedbackForm" action="{{ route('feedback.store') }}" method="POST">
                        @csrf
                        <h5 class="card-title text-center fw-semibold mb-3">
                            Bagaimana pengalaman Anda dengan layanan kami?
                        </h5>

                        {{-- Bintang Rating --}}
                        <div class="d-flex justify-content-center mb-3">
                            <span class="star fs-3 mx-1" data-value="1" style="cursor: pointer;"><i class="bi bi-star"></i></span>
                            <span class="star fs-3 mx-1" data-value="2" style="cursor: pointer;"><i class="bi bi-star"></i></span>
                            <span class="star fs-3 mx-1" data-value="3" style="cursor: pointer;"><i class="bi bi-star"></i></span>
                            <span class="star fs-3 mx-1" data-value="4" style="cursor: pointer;"><i class="bi bi-star"></i></span>
                            <span class="star fs-3 mx-1" data-value="5" style="cursor: pointer;"><i class="bi bi-star"></i></span>
                        </div>

                        <p id="rating-text" class="text-center text-muted mb-3">
                            Pilih rating
                        </p>

                        <input type="hidden" id="rating" name="rating" required>

                        {{-- Pesan --}}
                        <div class="mb-4">
                            <textarea id="message"
                                    name="message"
                                    class="form-control border-primary"
                                    rows="4"
                                    placeholder="Sampaikan saran, keluhan, atau pujian Anda..."
                                    required></textarea>
                        </div>

                        {{-- reCAPTCHA --}}
                        <div class="d-flex justify-content-center mb-3">
                            {!! NoCaptcha::display() !!}
                            @error('g-recaptcha-response')
                                <small class="text-danger d-block text-center">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary px-5" style="background-color:#15406A !important; color:white !important;">
                                Kirim
                            </button>
                        </div>
                    </form>

                    @if(!auth()->check())
                    <script>
                    document.addEventListener("DOMContentLoaded", function () {

                        document.getElementById("feedbackForm").addEventListener("submit", function(e) {
                            e.preventDefault();

                            Swal.fire({
                                icon: 'warning',
                                title: 'Tidak bisa mengirim feedback!',
                                text: 'Silakan login terlebih dahulu.',
                                confirmButtonText: 'Tutup',
                            })

                        });

                    });
                    </script>
                    @endif

                    @if(session('success'))
                    <script>
                    Swal.fire({
                        title: "Berhasil!",
                        text: "{{ session('success') }}",
                        icon: "success"
                    });
                    </script>
                    @endif



                    {!! NoCaptcha::renderJs() !!}

                    <script>
                        document.addEventListener("DOMContentLoaded", function () {

                            const form = document.getElementById('feedbackForm');
                            const stars = document.querySelectorAll('.star');
                            const ratingInput = document.getElementById('rating');
                            const ratingText = document.getElementById('rating-text');
                            const ratingWords = ["Sangat Buruk", "Buruk", "Cukup", "Baik", "Sangat Puas"];

                            // ⭐ Klik bintang
                            stars.forEach(star => {
                                star.addEventListener('click', function () {
                                    let val = parseInt(this.getAttribute('data-value'));
                                    ratingInput.value = val;

                                    stars.forEach((s) => {
                                        let icon = s.querySelector('i');
                                        icon.classList.remove('text-warning', 'bi-star-fill');
                                        icon.classList.add('bi-star');
                                    });

                                    for (let i = 0; i < val; i++) {
                                        let icon = stars[i].querySelector('i');
                                        icon.classList.remove('bi-star');
                                        icon.classList.add('bi-star-fill', 'text-warning');
                                    }

                                    ratingText.innerText = ratingWords[val - 1];
                                });
                            });

                            // 📨 Submit pakai AJAX
                            form.addEventListener('submit', async function(e) {
                                e.preventDefault();

                                let rating = ratingInput.value;
                                let message = document.getElementById('message').value;
                                let captcha = document.querySelector('[name="g-recaptcha-response"]').value;

                                if (!rating) {
                                    return Swal.fire({
                                        icon: 'warning',
                                        title: 'Rating belum dipilih!',
                                        text: 'Silakan pilih rating.',
                                    });
                                }

                                if (!message.trim()) {
                                    return Swal.fire({
                                        icon: 'warning',
                                        title: 'Pesan kosong!',
                                        text: 'Silakan isi pesan feedback.',
                                    });
                                }

                                if (!captcha) {
                                    return Swal.fire({
                                        icon: 'warning',
                                        title: 'Verifikasi tidak valid!',
                                        text: 'Silakan selesaikan reCAPTCHA.',
                                    });
                                }

                                let response = await fetch(form.action, {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify({
                                        rating: rating,
                                        message: message,
                                        "g-recaptcha-response": captcha
                                    })
                                });

                                let result = await response.json();

                                if (response.ok) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Berhasil!",
                                        text: result.message,
                                        timer: 1200,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location.reload();
                                    });

                                    form.reset();
                                    ratingInput.value = "";
                                    ratingText.innerText = "Pilih rating";
                                    grecaptcha.reset();
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Gagal!",
                                        text: result.message ?? "Terjadi kesalahan.",
                                    });

                                    grecaptcha.reset();
                                }
                            });
                        });
                        </script>


                </div>
            </div>
        </div>

        {{-- Kolom kanan: info kontak + peta --}}
        <div class="col-12 col-lg-4 d-flex flex-column gap-4">

            {{-- Card info kontak --}}
            <div class="card shadow-sm rounded-3 border-0">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Informasi Kontak</h6>

                    <ul class="list-unstyled mb-0" style="line-height:1.6; font-size:14px;">
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-telephone-fill fs-5"></i>
                            <span>085111380122</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-envelope-fill fs-5"></i>
                            <span>balai.k3surabaya@gmail.com</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-instagram fs-5"></i>
                            <span>balaik3surabaya</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-facebook fs-5"></i>
                            <span>Balai K3 Surabaya</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-tiktok fs-5"></i>
                            <span>Balai K3 Surabaya</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Card peta --}}
            <div class="card shadow-sm rounded-3 border-0 overflow-hidden">
                {{-- Sesuaikan ukuran iframe agar proporsional --}}
                <div class="ratio ratio-4x3">
                    {{-- Pakai query place spesifik agar pin merah lokasi tampil otomatis --}}
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d31656.889720295017!2d112.68182357431638!3d-7.341406199999984!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7fb512f9cd5a3%3A0xd2ef53ce7dac7cd9!2sBalai%20K3%20Surabaya%2C%20Kementerian%20Ketenagakerjaan%20R.I!5e0!3m2!1sid!2sus!4v1778033818202!5m2!1sid!2sus"
                        width="600"
                        height="450"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>

        </div>
    </div> <br>

    
    {{-- Riwayat Feedback Pengguna --}}
    @if(isset($feedbacks) && $feedbacks->count())
        <div class="card shadow-sm rounded-3 border-0 mt-4">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Riwayat Feedback Anda</h6>

                @foreach($feedbacks as $feedback)
                    @php
                        $feedbackTime = \Carbon\Carbon::parse($feedback->created_at)->timezone('Asia/Jakarta');
                    @endphp

                    <div class="border rounded-3 p-3 mb-3 feedback-history-card">
                        {{-- Header: kiri (rating + pesan) | kanan (tanggal + menu) --}}
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            {{-- Kiri: rating + pesan (biar ga ada gap) --}}
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= ($feedback->rating ?? 0) ? 'bi-star-fill text-warning' : 'bi-star text-muted' }}"></i>
                                    @endfor
                                </div>

                                {{-- Pesan langsung nempel bawah rating --}}
                                <p class="mb-0">{{ $feedback->message }}</p>
                            </div>

                            {{-- Kanan: tanggal + dropdown --}}
                            <div class="d-flex flex-column align-items-end gap-2">
                                <span class="text-muted small">{{ $feedbackTime->format('d M Y H:i') }} WIB</span>

                                <div class="dropdown">
                                    <button class="btn btn-sm text-white"
                                            style="background-color:#15406A; border-color:#15406A;"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical" style="color:white !important;"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item"
                                            href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editFeedbackModal"
                                            data-id="{{ $feedback->id }}"
                                            data-rating="{{ $feedback->rating }}"
                                            data-message="{{ $feedback->message }}">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger btn-delete-feedback" style="color:red !important;"
                                            href="#"
                                            data-form="delete-feedback-{{ $feedback->id }}">
                                                <i class="bi bi-trash" style="color:red !important;"></i> Hapus
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Form delete --}}
                        <form id="delete-feedback-{{ $feedback->id }}"
                            action="{{ route('feedback.destroy', $feedback) }}"
                            method="POST"
                            class="delete-feedback-form d-none">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="confirm" value="1">
                        </form>

                        {{-- Balasan petugas --}}
                        @if($feedback->reply)
                            @php
                                $replyTime = $feedback->reply->created_at
                                    ? \Carbon\Carbon::parse($feedback->reply->created_at)->timezone('Asia/Jakarta')
                                    : null;
                            @endphp

                            <div class="p-3 rounded-3 bg-light mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-primary">Balasan Petugas</span>
                                    @if($replyTime)
                                        <span class="text-muted small">{{ $replyTime->format('d M Y H:i') }} WIB</span>
                                    @endif
                                </div>
                                <p class="mb-0">{{ $feedback->reply->reply_message }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<!-- Modal Edit Feedback -->
<div class="modal fade" id="editFeedbackModal" tabindex="-1" aria-labelledby="editFeedbackLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" id="editFeedbackForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFeedbackLabel">Edit Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="width: 50vw;">Rating</label>
                        <select class="form-select" name="rating" id="editRating" required>
                            <option value="1">1 - Sangat Buruk</option>
                            <option value="2">2 - Buruk</option>
                            <option value="3">3 - Cukup</option>
                            <option value="4">4 - Baik</option>
                            <option value="5">5 - Sangat Puas</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan</label>
                        <textarea class="form-control" name="message" id="editMessage" rows="4" required></textarea>
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


{{-- Optional: CSS tambahan untuk meniru style card di screenshot --}}
<style>
    /* Menonjolkan card dengan radius lebih halus */
    .card {
        border-radius: 12px;
        font-family: 'Poppins', sans-serif;
    }

    /* Ubah warna seluruh font */
    body, .card, .card * {
        color: #15406A !important;
    }

    /* Ubah warna button menjadi biru seperti di home */
    .btn.btn-light {
        background-color: #15406A !important;
        border-color: #15406A !important;
        color: white !important;
    }
</style>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Prefill edit modal
        const editModal = document.getElementById('editFeedbackModal');
        const editForm = document.getElementById('editFeedbackForm');
        const editRating = document.getElementById('editRating');
        const editMessage = document.getElementById('editMessage');

        editModal?.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            if (!button) return;
            editForm.action = "{{ url('/feedback') }}/" + button.getAttribute('data-id');
            editRating.value = button.getAttribute('data-rating');
            editMessage.value = button.getAttribute('data-message');
        });

        // Delete confirm
        document.querySelectorAll('.btn-delete-feedback').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const formId = link.getAttribute('data-form');
                const form = document.getElementById(formId);
                if (!form) return;
                Swal.fire({
                    title: 'Hapus feedback?',
                    text: 'Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33'
                }).then(result => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    });
</script>


@endsection
