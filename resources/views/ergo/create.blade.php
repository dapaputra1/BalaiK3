@extends('layouts.app_admin')

@section('title', 'Input Pengujian Ergonomi — Balai K3 Surabaya')

@push('styles')
    <!-- Tailwind CSS (Scoped to Ergo Content) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- MediaPipe Pose untuk AI Deteksi Sudut -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/pose/pose.js" crossorigin="anonymous"></script>

    <style>
        .radio-custom input:checked + label {
            background-color: #15406A;
            color: #ffffff;
            border-color: #15406A;
        }
        .gotrak-panel {
            border: 2px solid #000000;
            background-color: #ffffff;
            padding: 8px 10px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 10;
        }
        .gotrak-title-bar {
            border-bottom: 2px solid #000000;
            font-weight: 800;
            text-transform: uppercase;
            padding-bottom: 4px;
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #000000;
        }
        .gotrak-check-label {
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            margin-bottom: 3px;
            font-size: 10px;
            color: #000000;
        }
        .gotrak-check-label input[type="radio"], 
        .gotrak-check-label input[type="checkbox"] {
            accent-color: #15406A;
            width: 13px;
            height: 13px;
        }
    </style>
@endpush

@section('content_admin')
<div class="container-fluid px-0 space-y-6">
    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border-2 border-rose-300 text-xs text-rose-800 font-semibold space-y-1">
        <div class="flex items-center gap-1.5 font-bold text-rose-900 text-sm">
            <i class="ph-bold ph-warning-circle text-lg"></i> Gagal Menyimpan Data:
        </div>
        <p>{{ session('error') }}</p>
    </div>
    @endif

    @if ($errors->any())
    <div class="p-4 rounded-xl bg-amber-50 border-2 border-amber-300 text-xs text-amber-900 font-semibold">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- ================= HEADER HALAMAN ================= -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-[#e8f1f9] text-[#153e67] border border-[#d1e3f3]">
                    Formulir F/7.3.10/BK3-SBY
                </span>
                <span class="text-xs text-slate-400 font-medium">Revisi: -/1</span>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Input Data Pengujian Faktor Ergonomi</h1>
            <p class="text-xs text-slate-500">Lembar pengamatan lapangan berbasis SNI 9011:2021 dan formulir keluhan Gotrak.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('ergo.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-xs">
                Batal
            </a>
            <button type="submit" form="ergoForm" id="btnTopSubmit" class="px-4 py-2 rounded-lg bg-[#153e67] hover:bg-[#0f2e4d] text-white text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <i class="ph-bold ph-floppy-disk"></i> Simpan Data Asesmen
            </button>
        </div>
    </div>

    <!-- ================= LIVE SUMMARY CARD ================= -->
    <div class="bg-white rounded-xl border-2 border-[#153e67] p-5 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center pb-3 border-b border-slate-200 gap-2">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Hasil Evaluasi Potensi Bahaya (SNI 9011:2021)</h3>
                <p class="text-[11px] text-slate-500">Kalkulasi otomatis rincian skor risiko dan evaluasi tingkat bahaya tempat kerja.</p>
            </div>
            <div id="riskBadge" class="px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1.5">
                <span id="riskLabel">Tempat Kerja Aman</span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs text-center">
            <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">
                <span class="text-slate-500 block text-[11px] mb-1">Tubuh Bagian Atas</span>
                <span id="scoreUpper" class="text-sm font-bold text-slate-800">0</span>
            </div>
            <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">
                <span class="text-slate-500 block text-[11px] mb-1">Punggung & Bawah</span>
                <span id="scoreLower" class="text-sm font-bold text-slate-800">0</span>
            </div>
            <div class="p-2 bg-slate-50 rounded-lg border border-slate-200">
                <span class="text-slate-500 block text-[11px] mb-1">Beban Manual (MMH)</span>
                <span id="scoreMMH" class="text-sm font-bold text-slate-800">0</span>
            </div>
            <div class="p-2 bg-[#e8f1f9] rounded-lg border border-[#d1e3f3]">
                <span class="text-[#153e67] block text-[11px] font-semibold mb-1">Total Skor Bahaya</span>
                <span id="scoreTotal" class="text-base font-black text-[#153e67]">0</span>
            </div>
        </div>

        <div id="gotrakHighRiskAlert" class="hidden p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
            <span class="font-bold block">⚠️ Ditemukan Keluhan Gotrak Tingkat Tinggi (Nilai &ge; 8):</span>
            <p class="text-[11px] text-rose-700">
                Pekerja mengalami keluhan sakit berat pada bagian: <strong id="highRiskJointsList">-</strong>. Wajib melengkapi data pada <strong>Tabel Riwayat Cedera</strong>.
            </p>
        </div>
    </div>

    <form id="ergoForm" action="{{ route('ergo.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- ================= BAGIAN 1: DATA UMUM PERUSAHAAN ================= -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-[#fbfcfd] flex items-center gap-2.5">
                <span class="w-6 h-6 rounded bg-[#153e67] text-white flex items-center justify-center text-xs font-bold">1</span>
                <h2 class="text-sm font-bold text-slate-900">Data Umum Perusahaan & Sampling Pengujian</h2>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
                    <label class="font-semibold text-slate-700">1. Nama Perusahaan <span class="text-rose-500">*</span></label>
                    <input type="text" name="company_name" required placeholder="Contoh: Perumda Air Minum Surya Sembada Kota Surabaya" class="md:col-span-2 border border-slate-300 rounded-lg p-2.5 text-xs focus:border-[#153e67] outline-none">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
                    <label class="font-semibold text-slate-700">2. Alamat Perusahaan</label>
                    <input type="text" name="address" placeholder="Contoh: Jl. Mayjend Prof. Dr. Moestopo No. 2 Surabaya" class="md:col-span-2 border border-slate-300 rounded-lg p-2.5 text-xs focus:border-[#153e67] outline-none">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
                    <label class="font-semibold text-slate-700">3. Jenis Perusahaan</label>
                    <input type="text" name="company_type" placeholder="Contoh: Pengolahan Air Bersih (PDAM)" class="md:col-span-2 border border-slate-300 rounded-lg p-2.5 text-xs focus:border-[#153e67] outline-none">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
                    <label class="font-semibold text-slate-700">4. Tanggal Sampling</label>
                    <input type="date" name="assessment_date" value="{{ date('Y-m-d') }}" class="md:col-span-2 border border-slate-300 rounded-lg p-2.5 text-xs focus:border-[#153e67] outline-none">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
                    <label class="font-semibold text-slate-700">5. Durasi Shift Kerja Harian</label>
                    <div class="md:col-span-2 flex items-center gap-2">
                        <input type="number" id="shift_hours" name="shift_hours" value="8" min="1" max="24" step="0.5" 
                               class="w-24 border border-slate-300 rounded-lg p-2 text-xs font-bold text-[#153e67] outline-none text-center">
                        <span class="text-xs text-slate-500">Jam/hari (Otomatis menambah +0.5 per jam jika &gt; 8 jam)</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 items-center">
                    <label class="font-semibold text-slate-700">6. Metode yang Digunakan</label>
                    <input type="text" value="SNI 9011:2021" readonly class="md:col-span-2 bg-slate-100 border border-slate-300 rounded-lg p-2.5 text-xs font-bold text-slate-700 select-none">
                </div>
            </div>
        </div>

        <!-- ================= BAGIAN 2: PROFIL PEKERJA & URAIAN TUGAS ================= -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-[#fbfcfd] flex items-center gap-2.5">
                <span class="w-6 h-6 rounded bg-[#153e67] text-white flex items-center justify-center text-xs font-bold">2</span>
                <h2 class="text-sm font-bold text-slate-900">Profil Tenaga Kerja & Pola Tugas</h2>
            </div>

            <div class="p-6 space-y-5 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1.5">Nama Tenaga Kerja <span class="text-rose-500">*</span></label>
                        <input type="text" name="worker_name" required placeholder="Contoh: M. Jazuli" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs focus:border-[#153e67] outline-none">
                    </div>
                    <div>
                        <label class="font-semibold text-slate-700 block mb-1.5">Posisi / Jabatan <span class="text-rose-500">*</span></label>
                        <input type="text" name="position" required placeholder="Contoh: Analis Fisika Kimia" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs focus:border-[#153e67] outline-none">
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-3">
                    <span class="font-bold text-slate-800 block">Deskripsikan tugas yang dilakukan dan durasi waktu per shift:</span>
                    <div class="space-y-2">
                        <div>
                            <span class="text-slate-600 block mb-1">a. Deskripsi Tugas:</span>
                            <textarea name="job_tasks" rows="2" placeholder="Uraikan tugas operasional yang dilaksanakan..." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs bg-white outline-none focus:border-[#153e67]"></textarea>
                        </div>
                        <div>
                            <span class="text-slate-600 block mb-1">b. Alokasi Waktu:</span>
                            <input type="text" name="job_duration" placeholder="Contoh: Dalam 1 hari kerja melakukan analisa dengan durasi 2-3 jam" class="w-full border border-slate-300 rounded-lg p-2 text-xs bg-white outline-none focus:border-[#153e67]">
                        </div>
                    </div>
                </div>

                <div>
                    <span class="font-semibold text-slate-700 block mb-2">Manakah yang merupakan tangan dominan Anda?</span>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['Kanan', 'Kiri', 'Keduanya'] as $hand)
                            <div class="radio-custom">
                                <input type="radio" name="dominant_hand" value="{{ $hand }}" id="dh_{{ $hand }}" class="hidden" {{ $hand === 'Kanan' ? 'checked' : '' }}>
                                <label for="dh_{{ $hand }}" class="flex items-center justify-center p-2 rounded-lg border border-slate-300 cursor-pointer text-center transition">
                                    {{ $hand }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <span class="font-semibold text-slate-700 block mb-2">Sudah berapa lama Anda bekerja pada posisi/jabatan saat ini?</span>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        @foreach(['Kurang dari 3 bulan', '3 bulan - 1 tahun', '1 - 5 tahun', '5 - 10 tahun', 'Lebih dari 10 tahun'] as $val)
                            <div class="radio-custom">
                                <input type="radio" name="work_duration_level" value="{{ $val }}" id="dur_{{ Str::slug($val) }}" class="hidden" {{ $val === '1 - 5 tahun' ? 'checked' : '' }}>
                                <label for="dur_{{ Str::slug($val) }}" class="flex items-center justify-center p-2 rounded-lg border border-slate-300 cursor-pointer text-center transition">
                                    {{ $val }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-slate-200">
                    <div>
                        <span class="font-semibold text-slate-700 block mb-2">Frekuensi kelelahan mental setelah bekerja?</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['Tidak pernah', 'Kadang-kadang', 'Sering', 'Selalu'] as $idx => $opt)
                                <div class="radio-custom">
                                    <input type="radio" name="mental_fatigue" value="{{ $opt }}" id="m_{{ $idx }}" class="hidden" {{ $idx === 0 ? 'checked' : '' }}>
                                    <label for="m_{{ $idx }}" class="flex items-center justify-center p-2 rounded-lg border border-slate-300 cursor-pointer transition text-center">
                                        {{ $opt }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-700 block mb-2">Frekuensi kelelahan fisik setelah bekerja?</span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['Tidak pernah', 'Kadang-kadang', 'Sering', 'Selalu'] as $idx => $opt)
                                <div class="radio-custom">
                                    <input type="radio" name="physical_fatigue" value="{{ $opt }}" id="f_{{ $idx }}" class="hidden" {{ $idx === 1 ? 'checked' : '' }}>
                                    <label for="f_{{ $idx }}" class="flex items-center justify-center p-2 rounded-lg border border-slate-300 cursor-pointer transition text-center">
                                        {{ $opt }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-lg bg-amber-50/70 border border-amber-200 space-y-2">
                    <span class="font-bold text-slate-900 block">
                        Pernahkah Anda mengalami rasa sakit/nyeri atau ketidaknyamanan yang berhubungan dengan pekerjaan dalam satu tahun terakhir?
                    </span>
                    <div class="flex gap-4 pt-1">
                        <label class="flex-1 flex items-center justify-center gap-2 p-2.5 bg-white rounded-lg border border-slate-300 cursor-pointer transition has-[:checked]:border-[#153e67] has-[:checked]:bg-[#153e67] has-[:checked]:text-white font-bold">
                            <input type="radio" name="has_pain_last_year" value="1" onchange="toggleGotrak(true)" class="hidden"> Ya
                        </label>
                        <label class="flex-1 flex items-center justify-center gap-2 p-2.5 bg-white rounded-lg border border-slate-300 cursor-pointer transition has-[:checked]:border-slate-800 has-[:checked]:bg-slate-800 has-[:checked]:text-white font-bold">
                            <input type="radio" name="has_pain_last_year" value="0" checked onchange="toggleGotrak(false)" class="hidden"> Tidak
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= BAGIAN 3: NORDIC BODY MAP (GOTRAK) ================= -->
        <div id="gotrak_section" class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden transition-all duration-300 opacity-40 pointer-events-none">
            <div class="px-6 py-4 border-b border-slate-200 bg-[#fbfcfd] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded bg-[#153e67] text-white flex items-center justify-center text-xs font-bold">3</span>
                    <h2 class="text-sm font-bold text-slate-900">Pemetaan Keluhan Bagian Tubuh (Nordic Body Map / Gotrak)</h2>
                </div>
                <span class="text-[11px] font-bold text-rose-600 bg-rose-50 border border-rose-200 px-2.5 py-0.5 rounded">
                    Gotrak Diagram
                </span>
            </div>

            <div class="p-6 space-y-6">
                <p class="text-slate-600 text-xs font-medium">
                    Catatan: 'sakit' dapat berupa nyeri, kaku, mati rasa, kesemutan, atau rasa terbakar. Setiap kotak dihubungkan langsung dengan garis penunjuk ke bagian tubuh terkait:
                </p>

                <div class="border-2 border-black p-4 sm:p-6 bg-white overflow-x-auto">
                    <div id="gotrakArea" class="min-w-[1020px] grid grid-cols-11 gap-4 items-stretch relative">
                        
                        <svg id="pointerSvg"
                             class="absolute inset-0 w-full h-full pointer-events-none z-20 overflow-visible"
                             xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <marker id="arrowHead" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto">
                                    <path d="M 1 1.5 L 8 4.5 L 1 7.5 L 2.8 4.5 Z" fill="#153e67" />
                                </marker>
                            </defs>
                        </svg>

                        @php
                            $leftBoxes = [
                                'leher' => ['title' => 'LEHER', 'has_side' => false, 'id' => 'box_leher'],
                                'siku' => ['title' => 'SIKU', 'has_side' => true, 'id' => 'box_siku'],
                                'lengan' => ['title' => 'LENGAN', 'has_side' => true, 'id' => 'box_lengan'],
                                'tangan' => ['title' => 'TANGAN', 'has_side' => true, 'id' => 'box_tangan'],
                                'paha' => ['title' => 'PAHA', 'has_side' => true, 'id' => 'box_paha'],
                                'betis' => ['title' => 'BETIS', 'has_side' => true, 'id' => 'box_betis'],
                            ];
                            $rightBoxes = [
                                'bahu' => ['title' => 'BAHU', 'has_side' => true, 'id' => 'box_bahu'],
                                'punggung_atas' => ['title' => 'PUNGGUNG ATAS', 'has_side' => false, 'id' => 'box_punggung_atas'],
                                'punggung_bawah' => ['title' => 'PUNGGUNG BAWAH', 'has_side' => false, 'id' => 'box_punggung_bawah'],
                                'pinggul' => ['title' => 'PINGGUL', 'has_side' => true, 'id' => 'box_pinggul'],
                                'lutut' => ['title' => 'LUTUT', 'has_side' => true, 'id' => 'box_lutut'],
                                'kaki' => ['title' => 'KAKI', 'has_side' => true, 'id' => 'box_kaki'],
                            ];
                            $freqOptions = [1 => 'Tidak pernah', 2 => 'Terkadang', 3 => 'Sering', 4 => 'Selalu'];
                            $sevOptions = [1 => 'Tidak ada masalah', 2 => 'Tidak nyaman', 3 => 'Sakit', 4 => 'Sakit parah'];
                        @endphp

                        <!-- KOLOM SISI KIRI -->
                        <div class="col-span-4 flex flex-col justify-between py-1 pr-2 space-y-4">
                            @foreach($leftBoxes as $key => $box)
                                <div id="{{ $box['id'] }}" class="gotrak-panel">
                                    <div class="gotrak-title-bar">
                                        <span>{{ $box['title'] }}</span>
                                        @if($box['has_side'])
                                            <div class="flex gap-2 text-[10px] font-normal normal-case">
                                                <label class="flex items-center gap-1 cursor-pointer"><input type="checkbox" name="gotrak[{{ $key }}][side][]" value="Kanan"> Kanan</label>
                                                <label class="flex items-center gap-1 cursor-pointer"><input type="checkbox" name="gotrak[{{ $key }}][side][]" value="Kiri"> Kiri</label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-[10px]">
                                        <div>
                                            <span class="font-bold block mb-1 text-slate-900">Seberapa sering?</span>
                                            @foreach($freqOptions as $val => $f)
                                                <label class="gotrak-check-label">
                                                    <input type="radio" name="gotrak[{{ $key }}][freq]" value="{{ $val }}" {{ $val === 1 ? 'checked' : '' }}> {{ $f }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <div>
                                            <span class="font-bold block mb-1 text-slate-900">Seberapa parah?</span>
                                            @foreach($sevOptions as $val => $s)
                                                <label class="gotrak-check-label">
                                                    <input type="radio" name="gotrak[{{ $key }}][severity]" value="{{ $val }}" {{ $val === 1 ? 'checked' : '' }}> {{ $s }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- KOLOM TENGAH -->
                        <div id="bodyCenterCol" class="col-span-3 relative flex items-center justify-center select-none py-1">
                            <svg id="silhouetteCanvas" viewBox="0 0 300 900" class="w-full h-auto max-h-[900px] drop-shadow-sm" xmlns="http://www.w3.org/2000/svg">
                                <image href="{{ asset('images/ergo-checklist/nordic_body_clean.png') }}" x="0" y="0" width="300" height="900" preserveAspectRatio="none" />
                                <circle id="anchor_box_leher" cx="150" cy="148" r="1" opacity="0" />
                                <circle id="anchor_box_siku" cx="88" cy="310" r="1" opacity="0" />
                                <circle id="anchor_box_lengan" cx="78" cy="370" r="1" opacity="0" />
                                <circle id="anchor_box_tangan" cx="65" cy="445" r="1" opacity="0" />
                                <circle id="anchor_box_paha" cx="136" cy="520" r="1" opacity="0" />
                                <circle id="anchor_box_betis" cx="134" cy="660" r="1" opacity="0" />
                                <circle id="anchor_box_bahu" cx="210" cy="175" r="1" opacity="0" />
                                <circle id="anchor_box_punggung_atas" cx="150" cy="225" r="1" opacity="0" />
                                <circle id="anchor_box_punggung_bawah" cx="150" cy="330" r="1" opacity="0" />
                                <circle id="anchor_box_pinggul" cx="150" cy="415" r="1" opacity="0" />
                                <circle id="anchor_box_lutut" cx="176" cy="595" r="1" opacity="0" />
                                <circle id="anchor_box_kaki" cx="165" cy="810" r="1" opacity="0" />
                            </svg>
                        </div>

                        <!-- KOLOM SISI KANAN -->
                        <div class="col-span-4 flex flex-col justify-between py-1 pl-2 space-y-4">
                            @foreach($rightBoxes as $key => $box)
                                <div id="{{ $box['id'] }}" class="gotrak-panel">
                                    <div class="gotrak-title-bar">
                                        <span>{{ $box['title'] }}</span>
                                        @if($box['has_side'])
                                            <div class="flex gap-2 text-[10px] font-normal normal-case">
                                                <label class="flex items-center gap-1 cursor-pointer"><input type="checkbox" name="gotrak[{{ $key }}][side][]" value="Kanan"> Kanan</label>
                                                <label class="flex items-center gap-1 cursor-pointer"><input type="checkbox" name="gotrak[{{ $key }}][side][]" value="Kiri"> Kiri</label>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-[10px]">
                                        <div>
                                            <span class="font-bold block mb-1 text-slate-900">Seberapa sering?</span>
                                            @foreach($freqOptions as $val => $f)
                                                <label class="gotrak-check-label">
                                                    <input type="radio" name="gotrak[{{ $key }}][freq]" value="{{ $val }}" {{ $val === 1 ? 'checked' : '' }}> {{ $f }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <div>
                                            <span class="font-bold block mb-1 text-slate-900">Seberapa parah?</span>
                                            @foreach($sevOptions as $val => $s)
                                                <label class="gotrak-check-label">
                                                    <input type="radio" name="gotrak[{{ $key }}][severity]" value="{{ $val }}" {{ $val === 1 ? 'checked' : '' }}> {{ $s }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

                <!-- TABEL RIWAYAT CEDERA -->
                <div class="space-y-2 pt-3 border-t border-slate-200">
                    <p class="text-xs text-black font-semibold">
                        Pada setiap bagian tubuh dengan keterangan "sakit" atau "sakit parah", atau "selalu" merasakan "tidak nyaman", jelaskan pekerjaan yang menurut Anda menyebabkan masalah tersebut, dan apakah sebelumnya Anda pernah mengalami cedera di bagian tubuh tersebut:
                    </p>
                    <div class="border-2 border-black rounded overflow-hidden">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-slate-100 font-bold border-b-2 border-black text-[11px] text-black">
                                <tr>
                                    <th class="py-2 px-3 border-r-2 border-black w-1/4">Bagian Tubuh</th>
                                    <th class="py-2 px-3 border-r-2 border-black w-1/4 text-center">Pernah Mengalami Cedera?</th>
                                    <th class="py-2 px-3">Kemungkinan Pekerjaan yang Menyebabkan Masalah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-black bg-white">
                                @for($i = 0; $i < 4; $i++)
                                    <tr>
                                        <td class="p-2 border-r-2 border-black">
                                            <input type="text" name="injury[{{ $i }}][part]" placeholder="Contoh: Punggung Bawah" class="w-full border border-slate-300 rounded p-1 text-xs outline-none focus:border-[#153e67]">
                                        </td>
                                        <td class="p-2 border-r-2 border-black text-center">
                                            <div class="flex justify-center gap-4 text-xs">
                                                <label class="flex items-center gap-1 cursor-pointer"><input type="radio" name="injury[{{ $i }}][history]" value="Ya" class="accent-[#153e67]"> Ya</label>
                                                <label class="flex items-center gap-1 cursor-pointer"><input type="radio" name="injury[{{ $i }}][history]" value="Tidak" checked class="accent-[#153e67]"> Tidak</label>
                                            </div>
                                        </td>
                                        <td class="p-2">
                                            <input type="text" name="injury[{{ $i }}][cause]" placeholder="Uraikan faktor kerja..." class="w-full border border-slate-300 rounded p-1 text-xs outline-none focus:border-[#153e67]">
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- ================= BAGIAN 4: DAFTAR PERIKSA 31 BUTIR SNI 9011:2021 ================= -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-[#fbfcfd] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded bg-[#153e67] text-white flex items-center justify-center text-xs font-bold">4</span>
                    <h2 class="text-sm font-bold text-slate-900">Daftar Periksa Potensi Bahaya Faktor Ergonomi (Lengkap 31 Butir)</h2>
                </div>
                <span class="text-[11px] font-semibold text-slate-500">SNI 9011:2021</span>
            </div>

            <div class="p-6 space-y-6 text-xs">
                @php
                    $ergoItemsA = [
                        1 => ['img' => 'image21.png', 'title' => 'Leher: Memuntir atau Menekuk', 'desc' => 'Leher memuntir > 20°, atau menekuk ke depan > 20° / ke belakang > 5°.'],
                        2 => ['img' => 'image17.png', 'title' => 'Bahu: Lengan / Siku Tidak Ditopang', 'desc' => 'Lengan/siku tidak ditopang, dengan posisi di atas tinggi perut.'],
                        3 => ['img' => 'image20.png', 'title' => 'Rotasi Lengan Bawah Secara Cepat', 'desc' => 'Gerakan pronasi atau supinasi berulang dengan cepat.'],
                        4 => ['img' => 'image19.png', 'title' => 'Pergelangan Tangan Menekuk', 'desc' => 'Pergelangan menekuk ke depan (fleksi) atau ke samping.'],
                        5 => ['img' => 'placeholder',  'title' => 'Gerakan Lengan Sedang', 'desc' => 'Gerakan lengan yang stabil dan ritmis dengan jeda yang teratur.'],
                        6 => ['img' => 'placeholder',  'title' => 'Gerakan Lengan Intensif', 'desc' => 'Gerakan lengan cepat yang berlangsung terus-menerus tanpa jeda.'],
                        7 => ['img' => 'image16.png', 'title' => 'Penggunaan Keyboard (Berselang)', 'desc' => 'Mengetik di komputer secara berselang (diselingi jeda).'],
                        8 => ['img' => 'placeholder',  'title' => 'Mengetik Secara Intensif', 'desc' => 'Mengetik secara konstan dalam waktu lama.'],
                        9 => ['img' => 'image5.png',  'title' => 'Menggenggam Kuat (Power Grip)', 'desc' => 'Menggenggam benda dengan gaya lebih dari 5 kg.'],
                        10 => ['img' => 'placeholder', 'title' => 'Menjepit dengan Jari (Pinch Grip)', 'desc' => 'Memencet objek dengan ujung jari dengan gaya lebih dari 1 kg.'],
                        11 => ['img' => 'image4.png',  'title' => 'Tekanan Kontak Benda Keras', 'desc' => 'Kulit tertekan oleh benda yang keras atau runcing.'],
                        12 => ['img' => 'placeholder', 'title' => 'Menggunakan Tangan Memukul', 'desc' => 'Menggunakan tangan untuk memukul (berfungsi seperti palu).'],
                        13 => ['img' => 'image3.png',  'title' => 'Getaran Lokal (Hand-Arm)', 'desc' => 'Paparan getaran lokal pada tangan dan lengan.'],
                        14 => ['img' => 'placeholder', 'title' => 'Ritme Kerja Tidak Terkontrol', 'desc' => 'Ritme kerja dipacu oleh mesin (conveyor).'],
                        15 => ['img' => 'placeholder', 'title' => 'Kondisi Pencahayaan', 'desc' => 'Pencahayaan kurang memadai atau silau.'],
                        16 => ['img' => 'placeholder', 'title' => 'Temperatur Ekstrem', 'desc' => 'Suhu area kerja terlalu tinggi (panas) atau rendah (dingin).'],
                    ];
                    
                    $ergoItemsB = [
                        17 => ['img' => 'image25.png', 'title' => 'Tubuh Membungkuk Sedang', 'desc' => 'Tubuh membungkuk antara 20° hingga 45°.'],
                        18 => ['img' => 'image26.png', 'title' => 'Tubuh Membungkuk Berat', 'desc' => 'Tubuh membungkuk ke depan lebih dari 45°.'],
                        19 => ['img' => 'image24.png', 'title' => 'Tubuh Menekuk ke Belakang', 'desc' => 'Tubuh menekuk ke belakang (ekstensi) hingga 30°.'],
                        20 => ['img' => 'image15.png', 'title' => 'Pemuntiran Torso', 'desc' => 'Batang tubuh berputar saat memindahkan barang.'],
                        21 => ['img' => 'image1.png',  'title' => 'Gerakan Abduksi Paha', 'desc' => 'Gerakan paha menjauhi tubuh ke samping.'],
                        22 => ['img' => 'image14.png', 'title' => 'Posisi Berlutut atau Jongkok', 'desc' => 'Bekerja dalam posisi berlutut/jongkok terus menerus.'],
                        23 => ['img' => 'image13.png', 'title' => 'Pergelangan Kaki Menekuk', 'desc' => 'Pergelangan kaki menekuk ke atas/bawah berulang.'],
                        24 => ['img' => 'image11.png', 'title' => 'Aktivitas Pedal / Pijakan Labil', 'desc' => 'Menginjak pedal kaki atau pijakan kaki tidak stabil.'],
                        25 => ['img' => 'image6.png',  'title' => 'Duduk Lama Tanpa Sandaran', 'desc' => 'Duduk lama tanpa penopang punggung memadai.'],
                        26 => ['img' => 'image9.png',  'title' => 'Berdiri Diam Dalam Jangka Lama', 'desc' => 'Berdiri statis lama tanpa tumpuan kaki.'],
                        27 => ['img' => 'image2.png',  'title' => 'Tubuh Bawah Tertekan', 'desc' => 'Paha/lutut tertekan permukaan benda keras.'],
                        28 => ['img' => 'image7.png',  'title' => 'Lutut Menendang/Memukul', 'desc' => 'Menggunakan lutut untuk menghentak.'],
                        29 => ['img' => 'image8.png',  'title' => 'Getaran Seluruh Tubuh', 'desc' => 'Paparan getaran mekanis pada seluruh tubuh (WBV).'],
                        30 => ['img' => 'placeholder', 'title' => 'Mendorong Beban Sedang', 'desc' => 'Mendorong/menarik troli beban sedang.'],
                        31 => ['img' => 'placeholder', 'title' => 'Mendorong Beban Berat', 'desc' => 'Mendorong/menarik beban berat butuh tenaga penuh.'],
                    ];
                @endphp

                <!-- SUB A -->
                <div class="space-y-3">
                    <span class="font-bold text-[#153e67] uppercase tracking-wider block bg-slate-100 p-2 rounded">
                        A. Potensi Bahaya Tubuh Bagian Atas (Butir 1 – 16)
                    </span>
                    @foreach($ergoItemsA as $no => $item)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 p-3 rounded-lg border border-slate-200 items-center">
                            <div class="md:col-span-3 text-center bg-slate-50 p-1.5 rounded border border-slate-100 flex justify-center items-center h-20">
                                @if($item['img'] !== 'placeholder')
                                    <img src="{{ asset('images/ergo-checklist/'.$item['img']) }}" class="max-h-full object-contain" alt="{{ $item['title'] }}" onerror="this.style.display='none'">
                                @else
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Tanpa Gambar</span>
                                @endif
                            </div>
                            <div class="md:col-span-5">
                                <span class="font-bold text-slate-900 block mb-0.5">{{ $no }}. {{ $item['title'] }}</span>
                                <p class="text-slate-500">{{ $item['desc'] }}</p>
                            </div>
                            <div class="md:col-span-4">
                                <select name="ergo_items[{{ $no }}]" class="w-full border border-slate-300 rounded-lg p-2 text-xs bg-white outline-none focus:border-[#153e67]">
                                    <option value="0">0% – 25% Waktu (Skor 0)</option>
                                    <option value="1">25% – 50% Waktu (Skor 1)</option>
                                    <option value="2">50% – 100% Waktu (Skor 2)</option>
                                    <option value="3">&gt; 100% / Sangat Parah (Skor 3)</option>
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- SUB B -->
                <div class="space-y-3 pt-4 border-t border-slate-200">
                    <span class="font-bold text-[#153e67] uppercase tracking-wider block bg-slate-100 p-2 rounded">
                        B. Potensi Bahaya Punggung & Tubuh Bawah (Butir 17 – 31)
                    </span>
                    @foreach($ergoItemsB as $no => $item)
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 p-3 rounded-lg border border-slate-200 items-center">
                            <div class="md:col-span-3 text-center bg-slate-50 p-1.5 rounded border border-slate-100 flex justify-center items-center h-20">
                                @if($item['img'] !== 'placeholder')
                                    <img src="{{ asset('images/ergo-checklist/'.$item['img']) }}" class="max-h-full object-contain" alt="{{ $item['title'] }}" onerror="this.style.display='none'">
                                @else
                                    <span class="text-[10px] text-slate-400 font-bold uppercase">Tanpa Gambar</span>
                                @endif
                            </div>
                            <div class="md:col-span-5">
                                <span class="font-bold text-slate-900 block mb-0.5">{{ $no }}. {{ $item['title'] }}</span>
                                <p class="text-slate-500">{{ $item['desc'] }}</p>
                            </div>
                            <div class="md:col-span-4">
                                <select name="ergo_items[{{ $no }}]" class="w-full border border-slate-300 rounded-lg p-2 text-xs bg-white outline-none focus:border-[#153e67]">
                                    <option value="0">0% – 25% Waktu (Skor 0)</option>
                                    <option value="1">25% – 50% Waktu (Skor 1)</option>
                                    <option value="2">50% – 100% Waktu (Skor 2)</option>
                                    <option value="3">&gt; 100% / Sangat Parah (Skor 3)</option>
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- SUB C: MMH -->
                <div class="space-y-3 pt-4 border-t border-slate-200">
                    <span class="font-bold text-[#153e67] uppercase tracking-wider block bg-slate-100 p-2 rounded">
                        C. Pengangkatan Beban Manual (MMH)
                    </span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-3 border border-slate-200 rounded-lg bg-slate-50">
                            <label class="font-bold text-slate-800 block mb-1">Berat Beban & Jarak Angkat</label>
                            <select name="mmh_weight_score" class="w-full border border-slate-300 rounded p-2 text-xs bg-white outline-none">
                                <option value="0">&lt; 7 kg dengan jarak dekat (Skor 0)</option>
                                <option value="1">7 – 13 kg jarak dekat (Skor 1)</option>
                                <option value="2">14 – 23 kg jarak dekat (Skor 2)</option>
                                <option value="3">&gt; 23 kg jarak dekat / rotasi (Skor 3)</option>
                            </select>
                        </div>
                        <div class="p-3 border border-slate-200 rounded-lg bg-slate-50">
                            <label class="font-bold text-slate-800 block mb-1">Jarak Angkut / Membawa Benda</label>
                            <select name="mmh_distance_score" class="w-full border border-slate-300 rounded p-2 text-xs bg-white outline-none">
                                <option value="0">Tidak membawa beban / &lt; 3 meter (Skor 0)</option>
                                <option value="1">Membawa beban 3 – 9 meter (Skor 1)</option>
                                <option value="2">Membawa beban &gt; 9 meter (Skor 2)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= BAGIAN 5: MULTI-UPLOAD, WEBCAM & INTERACTIVE CANVAS ================= -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-[#fbfcfd] flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="w-6 h-6 rounded bg-[#153e67] text-white flex items-center justify-center text-xs font-bold">5</span>
                    <h2 class="text-sm font-bold text-slate-900">Dokumentasi Foto, Kamera Langsung & Edit Sudut Interaktif</h2>
                </div>
                <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">Multi-Upload + Webcam + Drag</span>
            </div>

            <div class="p-6 space-y-6 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="relative border-2 border-dashed border-slate-300 rounded-xl p-5 text-center bg-slate-50 hover:bg-slate-100 transition flex flex-col justify-center items-center">
                        <input type="file" id="multiImageUploader" name="ergo_photos[]" accept="image/*" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="handleMultipleImages(event)">
                        <i class="ph-bold ph-upload-simple text-2xl text-[#153e67] mb-1"></i>
                        <p class="font-bold text-slate-800 text-xs">Unggah Berkas Foto (Bisa Banyak)</p>
                        <p class="text-slate-500 text-[10px]">Pilih file dari perangkat Anda</p>
                    </div>

                    <button type="button" onclick="openWebcamModal()" class="border-2 border-dashed border-[#153e67] rounded-xl p-5 text-center bg-blue-50/50 hover:bg-blue-50 transition flex flex-col justify-center items-center cursor-pointer">
                        <i class="ph-bold ph-camera text-2xl text-[#153e67] mb-1"></i>
                        <p class="font-bold text-[#153e67] text-xs">Ambil Foto Langsung dari Kamera</p>
                        <p class="text-blue-500 text-[10px]">Gunakan kamera perangkat (Webcam)</p>
                    </button>
                </div>

                <!-- Input Tersembunyi untuk JSON Sudut -->
                <input type="hidden" name="annotated_photos_json" id="annotatedPhotosJson">

                <!-- Daftar Thumbnail Foto -->
                <div id="thumbnailContainer" class="hidden space-y-2">
                    <span class="font-bold text-slate-700 block">Daftar Foto Dokumentasi (Klik untuk edit sudut / hapus):</span>
                    <div id="thumbnailList" class="flex flex-wrap gap-3"></div>
                </div>

                <!-- Area Kanvas Interaktif untuk Foto Aktif -->
                <div id="activeCanvasWrapper" class="hidden space-y-3 p-4 border border-slate-200 rounded-xl bg-slate-50">
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <span id="activePhotoTitle" class="font-bold text-slate-800">Sedang Mengedit Foto: -</span>
                        <div class="flex items-center gap-2">
                            <label class="px-3 py-1 bg-white border border-slate-300 rounded cursor-pointer hover:bg-slate-50 text-[11px] font-semibold text-slate-700">
                                <i class="ph-bold ph-arrow-counter-clockwise"></i> Ganti Foto Ini
                                <input type="file" accept="image/*" class="hidden" onchange="replaceActivePhoto(event)">
                            </label>
                            <button type="button" onclick="deleteActivePhoto()" class="px-3 py-1 bg-rose-50 border border-rose-200 text-rose-700 rounded hover:bg-rose-100 text-[11px] font-semibold">
                                <i class="ph-bold ph-trash"></i> Hapus Foto
                            </button>
                        </div>
                    </div>
                    <div class="relative overflow-hidden flex justify-center bg-black/5 rounded-lg border border-slate-300 p-2">
                        <canvas id="interactivePoseCanvas" class="max-h-[450px] object-contain cursor-crosshair"></canvas>
                    </div>
                    <p class="text-[10px] text-amber-700 italic text-center">💡 Klik & seret lingkaran kuning pada sendi di gambar untuk menggeser garis sudut secara manual.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-200">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Pengambil Contoh Uji (Surveyor K3)</label>
                        <input type="text" name="sampler_name" placeholder="Nama lengkap petugas penguji..." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs outline-none focus:border-[#153e67]">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Metode Pengendalian yang Sudah Ada</label>
                        <input type="text" name="existing_control" value="Adanya waktu istirahat/peregangan" class="w-full border border-slate-300 rounded-lg p-2 text-xs outline-none focus:border-[#153e67]">
                    </div>
                </div>

                <div class="pt-4 flex justify-end gap-2 border-t border-slate-200">
                    <a href="{{ route('ergo.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-semibold">Batal</a>
                    <button type="submit" id="btnBottomSubmit" class="px-5 py-2 bg-[#153e67] hover:bg-[#0f2e4d] text-white rounded-lg font-bold shadow-sm transition flex items-center gap-1.5">
                        <i class="ph-bold ph-floppy-disk"></i> Simpan Data Pengujian
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<!-- ================= MODAL WEBCAM LIVE CAMERA ================= -->
<div id="webcamModal" class="fixed inset-0 bg-black/70 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
        <div class="flex justify-between items-center border-b border-slate-200 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Ambil Foto Langsung dari Kamera</h3>
            <button type="button" onclick="closeWebcamModal()" class="text-slate-400 hover:text-slate-600 font-bold text-base">&times;</button>
        </div>
        <div class="relative bg-black rounded-lg overflow-hidden flex justify-center items-center aspect-video">
            <video id="webcamVideo" autoplay playsinline class="w-full h-full object-cover"></video>
        </div>
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" onclick="closeWebcamModal()" class="px-4 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700">Batal</button>
            <button type="button" onclick="captureWebcamSnapshot()" class="px-5 py-2 bg-[#153e67] text-white rounded-lg text-xs font-bold hover:bg-[#0f2e4d] flex items-center gap-1.5">
                <i class="ph-bold ph-camera"></i> Ambil Foto
            </button>
        </div>
    </div>
</div>

<!-- ================= JAVASCRIPT LENGKAP ================= -->
<script>
    function toggleGotrak(show) {
        const sec = document.getElementById('gotrak_section');
        if (show) {
            sec.classList.remove('opacity-40', 'pointer-events-none');
            setTimeout(drawDynamicPointers, 200);
        } else {
            sec.classList.add('opacity-40', 'pointer-events-none');
        }
    }

    const boxIds = [
        'box_leher', 'box_siku', 'box_lengan', 'box_tangan', 'box_paha', 'box_betis',
        'box_bahu', 'box_punggung_atas', 'box_punggung_bawah', 'box_pinggul', 'box_lutut', 'box_kaki'
    ];

    function drawDynamicPointers() {
        const svg = document.getElementById('pointerSvg');
        const container = document.getElementById('gotrakArea');
        if (!svg || !container) return;
        const contRect = container.getBoundingClientRect();
        if (contRect.width <= 0) return;
        svg.setAttribute('viewBox', `0 0 ${contRect.width} ${contRect.height}`);

        let elements = `<defs><marker id="arrowHead" markerWidth="9" markerHeight="9" refX="8" refY="4.5" orient="auto"><path d="M 1 1.5 L 8 4.5 L 1 7.5 L 2.8 4.5 Z" fill="#153e67" /></marker></defs>`;
        boxIds.forEach(boxId => {
            const boxEl = document.getElementById(boxId);
            const anchorEl = document.getElementById('anchor_' + boxId);
            if (!boxEl || !anchorEl) return;
            const boxRect = boxEl.getBoundingClientRect();
            const anchorRect = anchorEl.getBoundingClientRect();
            const isLeft = boxRect.left < anchorRect.left;
            const startX = isLeft ? (boxRect.right - contRect.left) : (boxRect.left - contRect.left);
            const startY = (boxRect.top + boxRect.height / 2) - contRect.top;
            const endX = (anchorRect.left + anchorRect.width / 2) - contRect.left;
            const endY = (anchorRect.top + anchorRect.height / 2) - contRect.top;
            elements += `<line x1="${startX}" y1="${startY}" x2="${endX}" y2="${endY}" stroke="#153e67" stroke-width="1.6" stroke-linecap="round" marker-end="url(#arrowHead)" /><circle cx="${startX}" cy="${startY}" r="3" fill="#153e67" stroke="#ffffff" stroke-width="1" />`;
        });
        svg.innerHTML = elements;
    }

    function calculateErgoAssessment() {
        const hoursInput = document.getElementById('shift_hours');
        const totalHours = hoursInput ? parseFloat(hoursInput.value) || 8 : 8;
        const overtimeBonus = totalHours > 8 ? (totalHours - 8) * 0.5 : 0;

        let scoreUpper = 0;
        for (let i = 1; i <= 16; i++) {
            const sel = document.querySelector(`select[name="ergo_items[${i}]"]`);
            if (sel) scoreUpper += parseInt(sel.value) || 0;
        }

        let scoreLower = 0;
        for (let i = 17; i <= 31; i++) {
            const sel = document.querySelector(`select[name="ergo_items[${i}]"]`);
            if (sel) scoreLower += parseInt(sel.value) || 0;
        }

        const mmhWeight = document.querySelector('select[name="mmh_weight_score"]');
        const mmhDist = document.querySelector('select[name="mmh_distance_score"]');
        const scoreMMH = (mmhWeight ? parseInt(mmhWeight.value) || 0 : 0) + (mmhDist ? parseInt(mmhDist.value) || 0 : 0);
        const totalScore = scoreUpper + scoreLower + scoreMMH + overtimeBonus;

        document.getElementById('scoreUpper').innerText = scoreUpper;
        document.getElementById('scoreLower').innerText = scoreLower;
        document.getElementById('scoreMMH').innerText = scoreMMH;
        document.getElementById('scoreTotal').innerText = totalScore.toFixed(1);

        const badge = document.getElementById('riskBadge');
        const label = document.getElementById('riskLabel');
        if (badge && label) {
            badge.className = "px-3.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider inline-flex items-center gap-1.5 border ";
            if (totalScore < 2) {
                badge.classList.add("bg-emerald-100", "text-emerald-800", "border-emerald-300");
                label.innerText = "Tempat Kerja Aman (Skor < 2)";
            } else if (totalScore <= 6) {
                badge.classList.add("bg-amber-100", "text-amber-800", "border-amber-300");
                label.innerText = "Perlu Pengamatan Lanjut (Skor 3 - 6)";
            } else {
                badge.classList.add("bg-rose-100", "text-rose-800", "border-rose-300");
                label.innerText = "Kondisi Berbahaya (Skor >= 7)";
            }
        }
        evaluateGotrakComplaints();
    }

    function evaluateGotrakComplaints() {
        const jointKeys = ['leher', 'siku', 'lengan', 'tangan', 'paha', 'betis', 'bahu', 'punggung_atas', 'punggung_bawah', 'pinggul', 'lutut', 'kaki'];
        const highRiskJoints = [];
        jointKeys.forEach(joint => {
            const freq = document.querySelector(`input[name="gotrak[${joint}][freq]"]:checked`);
            const sev = document.querySelector(`input[name="gotrak[${joint}][severity]"]:checked`);
            if (freq && sev) {
                if ((parseInt(freq.value) * parseInt(sev.value)) >= 8) {
                    highRiskJoints.push(joint.replace('_', ' ').toUpperCase());
                }
            }
        });
        const alertBox = document.getElementById('gotrakHighRiskAlert');
        const listSpan = document.getElementById('highRiskJointsList');
        if (alertBox && listSpan) {
            if (highRiskJoints.length > 0) {
                alertBox.classList.remove('hidden');
                listSpan.innerText = highRiskJoints.join(', ');
            } else {
                alertBox.classList.add('hidden');
            }
        }
    }

    window.addEventListener('load', () => {
        setTimeout(drawDynamicPointers, 200);
        calculateErgoAssessment();
    });

    window.addEventListener('resize', () => {
        clearTimeout(window.__nbmResize);
        window.__nbmResize = setTimeout(drawDynamicPointers, 80);
    });

    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById('ergoForm');
        if (form) {
            form.addEventListener('change', (e) => {
                if (e.target.matches('select[name^="ergo_items"], select[name^="mmh"], input[name^="gotrak"], #shift_hours')) {
                    calculateErgoAssessment();
                }
            });
        }
    });

    // =========================================================================
    // MULTI-PHOTO, WEBCAM & INTERACTIVE CANVAS LOGIC
    // =========================================================================
    let uploadedPhotos = []; 
    let activePhotoIndex = null;
    let draggedJointKey = null;
    let pendingImageFile = null;

    const pose = new Pose({ locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/pose/${file}` });
    pose.setOptions({ modelComplexity: 1, smoothLandmarks: true, minDetectionConfidence: 0.5 });
    pose.onResults(handleSinglePoseResult);

    function handleMultipleImages(event) {
        const files = Array.from(event.target.files);
        if (files.length === 0) return;
        files.forEach(file => processImageFile(file));
    }

    function processImageFile(file, replaceIndex = null) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.src = e.target.result;
            img.onload = function() {
                pendingImageFile = {
                    file: file,
                    name: file.name || `Kamera-${Date.now()}.jpg`,
                    imageObj: img,
                    landmarks: null,
                    replaceIndex: replaceIndex
                };
                pose.send({ image: img });
            };
        };
        reader.readAsDataURL(file);
    }

    function handleSinglePoseResult(results) {
        if (!pendingImageFile) return;
        const img = pendingImageFile.imageObj;
        const W = img.naturalWidth;
        const H = img.naturalHeight;

        let defaultJoints = {};
        if (results.poseLandmarks) {
            const lm = results.poseLandmarks;
            const isRight = lm[12].visibility > lm[11].visibility;
            defaultJoints = {
                ear: { x: (isRight ? lm[8] : lm[7]).x * W, y: (isRight ? lm[8] : lm[7]).y * H },
                shoulder: { x: (isRight ? lm[12] : lm[11]).x * W, y: (isRight ? lm[12] : lm[11]).y * H },
                elbow: { x: (isRight ? lm[14] : lm[13]).x * W, y: (isRight ? lm[14] : lm[13]).y * H },
                wrist: { x: (isRight ? lm[16] : lm[15]).x * W, y: (isRight ? lm[16] : lm[15]).y * H },
                hip: { x: (isRight ? lm[24] : lm[23]).x * W, y: (isRight ? lm[24] : lm[23]).y * H },
                knee: { x: (isRight ? lm[26] : lm[25]).x * W, y: (isRight ? lm[26] : lm[25]).y * H },
                ankle: { x: (isRight ? lm[28] : lm[27]).x * W, y: (isRight ? lm[28] : lm[27]).y * H }
            };
        } else {
            defaultJoints = {
                ear: { x: W * 0.5, y: H * 0.2 }, shoulder: { x: W * 0.5, y: H * 0.3 },
                elbow: { x: W * 0.6, y: H * 0.45 }, wrist: { x: W * 0.65, y: H * 0.6 },
                hip: { x: W * 0.5, y: H * 0.55 }, knee: { x: W * 0.52, y: H * 0.75 },
                ankle: { x: W * 0.52, y: H * 0.9 }
            };
        }

        pendingImageFile.landmarks = defaultJoints;

        if (pendingImageFile.replaceIndex !== null) {
            uploadedPhotos[pendingImageFile.replaceIndex] = pendingImageFile;
            setActivePhoto(pendingImageFile.replaceIndex);
        } else {
            uploadedPhotos.push(pendingImageFile);
            setActivePhoto(uploadedPhotos.length - 1);
        }

        pendingImageFile = null;
        document.getElementById('thumbnailContainer').classList.remove('hidden');
        document.getElementById('activeCanvasWrapper').classList.remove('hidden');
        renderThumbnails();
    }

    function renderThumbnails() {
        const listDiv = document.getElementById('thumbnailList');
        listDiv.innerHTML = '';
        uploadedPhotos.forEach((photo, idx) => {
            const canvasThumb = document.createElement('canvas');
            canvasThumb.width = 80; canvasThumb.height = 60;
            const ctxThumb = canvasThumb.getContext('2d');
            ctxThumb.drawImage(photo.imageObj, 0, 0, 80, 60);

            const wrapper = document.createElement('div');
            wrapper.className = `relative group border-2 rounded-lg p-1 cursor-pointer transition ${idx === activePhotoIndex ? 'border-[#153e67] bg-blue-50' : 'border-slate-300 bg-white'}`;
            wrapper.onclick = () => setActivePhoto(idx);
            wrapper.appendChild(canvasThumb);

            const label = document.createElement('span');
            label.className = 'block text-[9px] text-center truncate max-w-[80px] mt-0.5 font-semibold text-slate-700';
            label.innerText = photo.name;
            wrapper.appendChild(label);

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'absolute -top-2 -right-2 bg-rose-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] shadow hover:bg-rose-700 opacity-0 group-hover:opacity-100 transition';
            delBtn.innerHTML = '&times;';
            delBtn.onclick = (e) => { e.stopPropagation(); removePhoto(idx); };
            wrapper.appendChild(delBtn);

            listDiv.appendChild(wrapper);
        });
    }

    function setActivePhoto(idx) {
        if (uploadedPhotos.length === 0) {
            activePhotoIndex = null;
            document.getElementById('thumbnailContainer').classList.add('hidden');
            document.getElementById('activeCanvasWrapper').classList.add('hidden');
            return;
        }
        activePhotoIndex = idx;
        renderThumbnails();
        document.getElementById('activePhotoTitle').innerText = `Sedang Mengedit Foto: ${uploadedPhotos[idx].name}`;
        redrawActiveCanvas();
    }

    function removePhoto(idx) {
        uploadedPhotos.splice(idx, 1);
        if (activePhotoIndex >= uploadedPhotos.length) {
            activePhotoIndex = uploadedPhotos.length - 1;
        }
        setActivePhoto(activePhotoIndex);
    }

    function replaceActivePhoto(event) {
        const file = event.target.files[0];
        if (!file || activePhotoIndex === null) return;
        processImageFile(file, activePhotoIndex);
        event.target.value = '';
    }

    function deleteActivePhoto() {
        if (activePhotoIndex !== null) {
            removePhoto(activePhotoIndex);
        }
    }

    // ================= WEBCAM LOGIC =================
    let videoStream = null;

    async function openWebcamModal() {
        const modal = document.getElementById('webcamModal');
        const video = document.getElementById('webcamVideo');
        modal.classList.remove('hidden');
        try {
            videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = videoStream;
        } catch (err) {
            alert('Tidak dapat mengakses kamera perangkat: ' + err.message);
            closeWebcamModal();
        }
    }

    function closeWebcamModal() {
        const modal = document.getElementById('webcamModal');
        modal.classList.add('hidden');
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }
    }

    function captureWebcamSnapshot() {
        const video = document.getElementById('webcamVideo');
        const canvasSnap = document.createElement('canvas');
        canvasSnap.width = video.videoWidth || 640;
        canvasSnap.height = video.videoHeight || 480;
        const ctxSnap = canvasSnap.getContext('2d');
        ctxSnap.drawImage(video, 0, 0, canvasSnap.width, canvasSnap.height);

        canvasSnap.toBlob((blob) => {
            const file = new File([blob], `Webcam-${Date.now()}.jpg`, { type: 'image/jpeg' });
            processImageFile(file);
            closeWebcamModal();
        }, 'image/jpeg', 0.9);
    }

    // ================= INTERACTIVE CANVAS RENDER & DRAG =================
    const canvas = document.getElementById('interactivePoseCanvas');
    const ctx = canvas.getContext('2d');

    function redrawActiveCanvas() {
        if (activePhotoIndex === null || !uploadedPhotos[activePhotoIndex]) return;
        const photo = uploadedPhotos[activePhotoIndex];
        canvas.width = photo.imageObj.naturalWidth;
        canvas.height = photo.imageObj.naturalHeight;

        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(photo.imageObj, 0, 0);

        const lm = photo.landmarks;
        const W = canvas.width;

        if (lm) {
            const neckAng = findVerticalAngle(lm.ear, lm.shoulder);
            const torsoAng = findVerticalAngle(lm.shoulder, lm.hip);
            const elbowAng = findAngle(lm.shoulder, lm.elbow, lm.wrist);
            const kneeAng = findAngle(lm.hip, lm.knee, lm.ankle);

            ctx.strokeStyle = '#facc15'; ctx.fillStyle = '#facc15';
            ctx.lineWidth = Math.max(4, Math.round(W / 200));
            ctx.font = `bold ${Math.max(18, Math.round(W / 30))}px Arial`;

            ctx.setLineDash([8, 6]);
            ctx.beginPath(); ctx.moveTo(lm.hip.x, lm.hip.y); ctx.lineTo(lm.hip.x, lm.hip.y - (W * 0.25)); ctx.stroke();
            ctx.setLineDash([]);

            [[lm.ear, lm.shoulder], [lm.shoulder, lm.hip], [lm.shoulder, lm.elbow], [lm.elbow, lm.wrist], [lm.hip, lm.knee], [lm.knee, lm.ankle]].forEach(([pA, pB]) => {
                ctx.beginPath(); ctx.moveTo(pA.x, pA.y); ctx.lineTo(pB.x, pB.y); ctx.stroke();
            });

            Object.keys(lm).forEach(key => {
                const p = lm[key];
                ctx.beginPath();
                ctx.arc(p.x, p.y, Math.max(8, Math.round(W / 100)), 0, 2 * Math.PI);
                ctx.fillStyle = (draggedJointKey === key) ? '#ef4444' : '#facc15';
                ctx.fill();
                ctx.lineWidth = 2; ctx.strokeStyle = '#000000'; ctx.stroke();
            });

            const drawTxt = (txt, p) => {
                ctx.shadowColor = 'black'; ctx.shadowBlur = 6;
                ctx.fillStyle = '#facc15';
                ctx.fillText(txt + '°', p.x + 15, p.y - 10);
                ctx.shadowBlur = 0;
            };

            drawTxt(neckAng, { x: (lm.ear.x+lm.shoulder.x)/2, y: (lm.ear.y+lm.shoulder.y)/2 });
            drawTxt(torsoAng, { x: (lm.shoulder.x+lm.hip.x)/2, y: (lm.shoulder.y+lm.hip.y)/2 });
            drawTxt(elbowAng, lm.elbow);
            drawTxt(kneeAng, lm.knee);
        }
    }

    function findAngle(p1, p2, p3) {
        let radians = Math.atan2(p3.y - p2.y, p3.x - p2.x) - Math.atan2(p1.y - p2.y, p1.x - p2.x);
        let angle = Math.abs((radians * 180.0) / Math.PI);
        if (angle > 180.0) angle = 360 - angle;
        return Math.round(angle);
    }

    function findVerticalAngle(pTop, pBottom) {
        let dx = pTop.x - pBottom.x;
        let dy = pBottom.y - pTop.y;
        return Math.round(Math.atan2(Math.abs(dx), Math.abs(dy)) * (180.0 / Math.PI));
    }

    canvas.addEventListener('mousedown', (e) => {
        if (activePhotoIndex === null) return;
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const mouseX = (e.clientX - rect.left) * scaleX;
        const mouseY = (e.clientY - rect.top) * scaleY;

        const lm = uploadedPhotos[activePhotoIndex].landmarks;
        for (let key in lm) {
            const p = lm[key];
            const dist = Math.hypot(p.x - mouseX, p.y - mouseY);
            if (dist < 30 * scaleX) {
                draggedJointKey = key;
                break;
            }
        }
    });

    canvas.addEventListener('mousemove', (e) => {
        if (!draggedJointKey || activePhotoIndex === null) return;
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;
        const mouseX = (e.clientX - rect.left) * scaleX;
        const mouseY = (e.clientY - rect.top) * scaleY;

        uploadedPhotos[activePhotoIndex].landmarks[draggedJointKey] = { x: mouseX, y: mouseY };
        redrawActiveCanvas();
    });

    window.addEventListener('mouseup', () => {
        draggedJointKey = null;
        if (activePhotoIndex !== null) redrawActiveCanvas();
    });

    // ================= RENDERING GAMBAR TERANOTASI DAN SUBMIT FORM =================
    function renderAnnotatedBlob(photo) {
        return new Promise((resolve) => {
            const exportCanvas = document.createElement('canvas');
            exportCanvas.width = photo.imageObj.naturalWidth;
            exportCanvas.height = photo.imageObj.naturalHeight;
            const eCtx = exportCanvas.getContext('2d');

            eCtx.drawImage(photo.imageObj, 0, 0);

            const lm = photo.landmarks;
            const W = exportCanvas.width;

            if (lm) {
                const neckAng = findVerticalAngle(lm.ear, lm.shoulder);
                const torsoAng = findVerticalAngle(lm.shoulder, lm.hip);
                const elbowAng = findAngle(lm.shoulder, lm.elbow, lm.wrist);
                const kneeAng = findAngle(lm.hip, lm.knee, lm.ankle);

                eCtx.strokeStyle = '#facc15';
                eCtx.fillStyle = '#facc15';
                eCtx.lineWidth = Math.max(4, Math.round(W / 200));
                eCtx.font = `bold ${Math.max(18, Math.round(W / 30))}px Arial`;

                eCtx.setLineDash([8, 6]);
                eCtx.beginPath();
                eCtx.moveTo(lm.hip.x, lm.hip.y);
                eCtx.lineTo(lm.hip.x, lm.hip.y - (W * 0.25));
                eCtx.stroke();
                eCtx.setLineDash([]);

                [[lm.ear, lm.shoulder], [lm.shoulder, lm.hip], [lm.shoulder, lm.elbow], [lm.elbow, lm.wrist], [lm.hip, lm.knee], [lm.knee, lm.ankle]].forEach(([pA, pB]) => {
                    eCtx.beginPath();
                    eCtx.moveTo(pA.x, pA.y);
                    eCtx.lineTo(pB.x, pB.y);
                    eCtx.stroke();
                });

                Object.keys(lm).forEach(key => {
                    const p = lm[key];
                    eCtx.beginPath();
                    eCtx.arc(p.x, p.y, Math.max(7, Math.round(W / 100)), 0, 2 * Math.PI);
                    eCtx.fillStyle = '#facc15';
                    eCtx.fill();
                    eCtx.lineWidth = 2;
                    eCtx.strokeStyle = '#000000';
                    eCtx.stroke();
                });

                const drawTxt = (txt, p) => {
                    eCtx.shadowColor = 'black';
                    eCtx.shadowBlur = 6;
                    eCtx.fillStyle = '#facc15';
                    eCtx.fillText(txt + '°', p.x + 15, p.y - 10);
                    eCtx.shadowBlur = 0;
                };

                drawTxt(neckAng, { x: (lm.ear.x + lm.shoulder.x) / 2, y: (lm.ear.y + lm.shoulder.y) / 2 });
                drawTxt(torsoAng, { x: (lm.shoulder.x + lm.hip.x) / 2, y: (lm.shoulder.y + lm.hip.y) / 2 });
                drawTxt(elbowAng, lm.elbow);
                drawTxt(kneeAng, lm.knee);
            }

            exportCanvas.toBlob((blob) => {
                resolve(new File([blob], photo.name.replace(/\.[^/.]+$/, "") + "-annotated.jpg", { type: 'image/jpeg' }));
            }, 'image/jpeg', 0.92);
        });
    }

    document.getElementById('ergoForm').addEventListener('submit', async function(e) {
        if (uploadedPhotos.length > 0) {
            e.preventDefault();

            const topBtn = document.getElementById('btnTopSubmit');
            const bottomBtn = document.getElementById('btnBottomSubmit');
            if (topBtn) {
                topBtn.disabled = true;
                topBtn.innerHTML = `<i class="ph-bold ph-spinner animate-spin"></i> Menyimpan...`;
            }
            if (bottomBtn) {
                bottomBtn.disabled = true;
                bottomBtn.innerHTML = `<i class="ph-bold ph-spinner animate-spin"></i> Menyimpan Garis Sudut...`;
            }

            const inputEl = document.getElementById('multiImageUploader');
            const dt = new DataTransfer();

            for (let photo of uploadedPhotos) {
                const annotatedFile = await renderAnnotatedBlob(photo);
                dt.items.add(annotatedFile);
            }

            inputEl.files = dt.files;

            const payload = uploadedPhotos.map(p => ({
                name: p.name,
                landmarks: p.landmarks
            }));
            document.getElementById('annotatedPhotosJson').value = JSON.stringify(payload);

            this.submit();
        }
    });
</script>
</div>
@endsection