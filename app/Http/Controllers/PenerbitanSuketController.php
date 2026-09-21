<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSuketSetting;
use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\SuketK3;
use App\Models\SuketK3Comment;
use App\Models\SuketK3History;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use App\Support\TerbilangHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenerbitanSuketController extends Controller
{
    use InteractsWithSuketSetting;

    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

    /**
     * Portal Pemohon: Daftar Permohonan Suket Milik User Sendiri (Tahap 1 Khusus Pemohon)
     */
    public function userIndex(Request $request)
    {
        $user = auth()->user();
        $search = $request->query('search');

        // Ambil riwayat permohonan Suket milik user yang sedang login (hanya muat komentar khusus pemohon)
        $suketQuery = SuketK3::with(['permohonan.company', 'qcUser', 'pemohonComments.user', 'evaluator', 'histories.user', 'tagihanSender', 'billingSender', 'kuitansiGenerator'])
            ->where('user_id', $user->id)
            ->latest('updated_at');

        if ($search) {
            $suketQuery->where(function ($q) use ($search) {
                $q->where('nomor_order', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        $sukets = $suketQuery->paginate(10)->withQueryString();

        // Ambil daftar nomor order HANYA milik user ini yang telah dibuat / selesai diuji
        $userOrders = Permohonan::with(['company', 'draftLhu'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($p) {
                $hasLhu = !empty($p->draftLhu?->signed_file_path) || !empty($p->draftLhu?->final_file_path);
                return [
                    'kode' => $p->kode,
                    'perusahaan' => $p->company?->company_name ?? 'Perusahaan #' . $p->id,
                    'lokasi' => $p->jadwal_lokasi ?: ($p->company?->company_city ?? '-'),
                    'has_lhu' => $hasLhu,
                    'lhu_name' => $p->draftLhu?->signed_file_name ?: ($p->draftLhu?->final_file_name ?? null),
                ];
            });

        return view('user.suket.index', [
            'sukets' => $sukets,
            'userOrders' => $userOrders,
            'faktorOptions' => SuketK3::FAKTOR_OPTIONS,
            'search' => $search,
        ]);
    }

    /**
     * Portal Pemohon: Simpan Permohonan Suket Baru oleh Pemohon
     */
    public function userStore(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'nomor_order' => ['required', 'string'],
            'faktor_k3' => ['required', 'array', 'min:1'],
            'faktor_k3.*' => ['in:fisika,kimia,biologi,ergonomi,psikologi'],
            'lhu_source' => ['required', 'in:auto,manual'],
            'lhu_file' => ['nullable', 'file', 'extensions:pdf', 'max:20480'],
            'foto_pengujian' => ['nullable', 'file', 'extensions:jpg,jpeg,png,pdf', 'max:20480'],
            'denah_lokasi' => ['nullable', 'file', 'extensions:jpg,jpeg,png,pdf', 'max:20480'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'nomor_order.required' => 'Nomor order / kode permohonan wajib dipilih.',
            'faktor_k3.required' => 'Pilih minimal satu faktor lingkungan kerja yang diuji.',
            'faktor_k3.min' => 'Pilih minimal satu faktor lingkungan kerja yang diuji.',
            'lhu_file.extensions' => 'Dokumen LHU harus berupa file PDF.',
            'foto_pengujian.extensions' => 'Foto pengujian harus berupa file JPG, JPEG, PNG, atau PDF.',
            'denah_lokasi.extensions' => 'Denah lokasi harus berupa file JPG, JPEG, PNG, atau PDF.',
        ]);

        $orderCode = trim($request->input('nomor_order'));
        $lhuSource = $request->input('lhu_source', 'auto');

        // Pastikan nomor order benar milik akun user pemohon ini
        $permohonan = Permohonan::with(['company', 'draftLhu'])
            ->where('user_id', $user->id)
            ->where(function ($q) use ($orderCode) {
                $q->where('kode', $orderCode)->orWhere('id', $orderCode);
            })
            ->first();

        if (!$permohonan) {
            return redirect()->back()->withInput()->with('error', 'Nomor Order yang Anda pilih tidak valid atau bukan milik akun Anda.');
        }

        $companyName = $permohonan->company?->company_name ?? 'Perusahaan ' . $permohonan->kode;
        $location = $permohonan->jadwal_lokasi ?: ($permohonan->company?->company_city ?? 'Lokasi Uji K3');

        // Handle LHU
        $lhuPath = null;
        $lhuName = null;
        if ($lhuSource === 'auto') {
            if ($permohonan->draftLhu) {
                $lhuPath = $permohonan->draftLhu->signed_file_path ?: $permohonan->draftLhu->final_file_path;
                $lhuName = $permohonan->draftLhu->signed_file_name ?: ($permohonan->draftLhu->final_file_name ?? 'LHU_' . $permohonan->kode . '.pdf');
            }
        } elseif ($request->hasFile('lhu_file')) {
            $file = $request->file('lhu_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $lhuName = $file->getClientOriginalName();
            $lhuPath = $file->storeAs('suket_docs/lhu', 'lhu_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
        }

        // Handle Foto
        $fotoPath = null;
        $fotoName = null;
        if ($request->hasFile('foto_pengujian')) {
            $file = $request->file('foto_pengujian');
            $ext = strtolower($file->getClientOriginalExtension());
            $fotoName = $file->getClientOriginalName();
            $fotoPath = $file->storeAs('suket_docs/foto', 'foto_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
        }

        // Handle Denah
        $denahPath = null;
        $denahName = null;
        if ($request->hasFile('denah_lokasi')) {
            $file = $request->file('denah_lokasi');
            $ext = strtolower($file->getClientOriginalExtension());
            $denahName = $file->getClientOriginalName();
            $denahPath = $file->storeAs('suket_docs/denah', 'denah_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
        }

        $suket = SuketK3::create([
            'user_id' => $user->id,
            'permohonan_id' => $permohonan->id,
            'nomor_order' => $permohonan->kode,
            'status_tahap' => 2,
            'faktor_k3' => $request->input('faktor_k3'),
            'lhu_source' => $lhuSource,
            'lhu_file_path' => $lhuPath,
            'lhu_file_name' => $lhuName,
            'foto_pengujian_path' => $fotoPath,
            'foto_pengujian_name' => $fotoName,
            'denah_lokasi_path' => $denahPath,
            'denah_lokasi_name' => $denahName,
            'perusahaan_nama' => $companyName,
            'lokasi' => $location,
            'catatan' => $request->filled('catatan') 
                ? trim((string) $request->input('catatan')) 
                : ('Pengajuan suket didaftarkan melalui Nomor Order ' . $permohonan->kode),
            'qc_status' => null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $suket->recordHistory(
            action: 'created',
            stageBefore: 1,
            stageAfter: 2,
            catatan: 'Permohonan Suket K3 diajukan oleh pemohon dan diteruskan ke Tim Penguji K3 (Evaluasi Dokumen).',
            userId: $user->id
        );

        return redirect()->route('user.suket.index')->with('success', "Permohonan Suket K3 untuk Order {$suket->nomor_order} berhasil diajukan dan sedang diteruskan ke Tim Penguji K3 (Tahap 2: Evaluasi Dokumen).");
    }

    /**
     * User Pemohon: Mengirimkan Catatan Revisi & Dokumen Pembaharuan Evaluasi Tahap 2
     */
    public function userSubmitRevision(Request $request, SuketK3 $suket)
    {
        $user = auth()->user();
        if ($user->role !== 'user' && $user->role !== 'superadmin') {
            abort(403, 'Hanya pemohon yang dapat mengirimkan revisi evaluasi.');
        }

        if ($suket->user_id !== $user->id && $suket->permohonan?->user_id !== $user->id && $user->role !== 'superadmin') {
            abort(403, 'Akses ditolak ke permohonan suket ini.');
        }

        $request->validate([
            'catatan_revisi' => ['required', 'string', 'max:2000'],
            'lhu_file' => ['nullable', 'file', 'extensions:pdf', 'max:20480'],
            'denah_lokasi' => ['nullable', 'file', 'extensions:pdf,jpg,jpeg,png', 'max:20480'],
            'foto_pengujian' => ['nullable', 'file', 'extensions:pdf,jpg,jpeg,png', 'max:20480'],
        ], [
            'catatan_revisi.required' => 'Penjelasan / tanggapan revisi wajib diisi.',
            'lhu_file.extensions' => 'Dokumen LHU pengganti harus berformat PDF.',
            'lhu_file.max' => 'Ukuran berkas LHU maksimal 20MB.',
            'denah_lokasi.extensions' => 'Dokumen denah lokasi harus berformat PDF, JPG, JPEG, atau PNG.',
            'foto_pengujian.extensions' => 'Dokumen foto pengujian harus berformat PDF, JPG, JPEG, atau PNG.',
        ]);

        $catatanRevisi = trim((string) $request->input('catatan_revisi'));

        DB::transaction(function () use ($request, $suket, $catatanRevisi, $user) {
            // Handle LHU jika pemohon mengunggah revisi LHU
            if ($request->hasFile('lhu_file')) {
                $file = $request->file('lhu_file');
                $ext = strtolower($file->getClientOriginalExtension());
                $suket->lhu_source = 'manual';
                $suket->lhu_file_name = $file->getClientOriginalName();
                $suket->lhu_file_path = $file->storeAs('suket_docs/lhu', 'lhu_rev_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
            }

            // Handle Denah Lokasi jika pemohon mengunggah denah baru
            if ($request->hasFile('denah_lokasi')) {
                $file = $request->file('denah_lokasi');
                $ext = strtolower($file->getClientOriginalExtension());
                $suket->denah_lokasi_name = $file->getClientOriginalName();
                $suket->denah_lokasi_path = $file->storeAs('suket_docs/denah', 'denah_rev_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
            }

            // Handle Foto Pengujian jika pemohon mengunggah foto baru
            if ($request->hasFile('foto_pengujian')) {
                $file = $request->file('foto_pengujian');
                $ext = strtolower($file->getClientOriginalExtension());
                $suket->foto_pengujian_name = $file->getClientOriginalName();
                $suket->foto_pengujian_path = $file->storeAs('suket_docs/foto', 'foto_rev_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
            }

            // Simpan catatan tanggapan & penjelasan revisi pemohon ke kolom dedicated
            $suket->catatan_revisi_pemohon = $catatanRevisi;
            $suket->revisi_pemohon_at = now();

            // Reset evaluasi_status ke pending agar kembali dievaluasi oleh Penguji K3
            $suket->evaluasi_status = 'pending';
            $suket->updated_by = $user->id;
            $suket->save();

            // Catat ke log histori suket
            $suket->recordHistory(
                action: 'user_revised',
                stageBefore: 2,
                stageAfter: 2,
                catatan: 'Pemohon mengirimkan tanggapan dan berkas revisi evaluasi: ' . Str::limit($catatanRevisi, 120),
                userId: $user->id
            );
        });

        return redirect()->route('user.suket.index')
            ->with('success', 'Tanggapan dan berkas revisi evaluasi berhasil dikirimkan ke Penguji K3 untuk ditelaah ulang.');
    }

    /**
     * User Pemohon: ACC / Konfirmasi Surat Tagihan (Tahap 6 ke Tahap 7)
     */
    public function userAccTagihan(Request $request, SuketK3 $suket)
    {
        $user = auth()->user();
        if ($user->role !== 'user' && $user->role !== 'superadmin') {
            abort(403, 'Hanya pemohon yang dapat menyetujui Surat Tagihan.');
        }

        if ($suket->user_id !== $user->id && $suket->permohonan?->user_id !== $user->id && $user->role !== 'superadmin') {
            abort(403, 'Akses ditolak ke permohonan suket ini.');
        }

        if ((int) $suket->status_tahap !== 6) {
            return redirect()->back()->with('error', 'Status berkas suket saat ini bukan pada tahap Surat Tagihan.');
        }

        DB::transaction(function () use ($suket, $user, $request) {
            $suket->surat_tagihan_acc_at = now();
            $suket->surat_tagihan_acc_by = $user->id;
            $suket->status_tahap = 7; // Otomatis berlanjut ke Tahap 7 (Kode Billing)
            $suket->updated_by = $user->id;
            $suket->save();

            $suket->recordHistory(
                action: 'tagihan_acced',
                stageBefore: 6,
                stageAfter: 7,
                catatan: $request->input('catatan', 'Pemohon menyetujui (ACC) Surat Tagihan. Lanjut ke proses penerbitan Kode Billing SIMPONI.'),
                userId: $user->id
            );
        });

        return redirect()->route('user.suket.index')
            ->with('success', 'Surat Tagihan berhasil disetujui (ACC). Berkas diteruskan ke Bendahara untuk penerbitan Kode Billing SIMPONI.');
    }

    /**
     * User Pemohon: Mengunggah Bukti Pembayaran SIMPONI / PNBP (Tahap 7)
     */
    public function userUploadPaymentProof(Request $request, SuketK3 $suket)
    {
        $user = auth()->user();
        if ($user->role !== 'user' && $user->role !== 'superadmin') {
            abort(403, 'Hanya pemohon yang dapat mengunggah bukti pembayaran.');
        }

        if ($suket->user_id !== $user->id && $suket->permohonan?->user_id !== $user->id && $user->role !== 'superadmin') {
            abort(403, 'Akses ditolak ke permohonan suket ini.');
        }

        if ((int) $suket->status_tahap !== 7) {
            return redirect()->back()->with('error', 'Status berkas suket saat ini bukan pada tahap Kode Billing.');
        }

        $request->validate([
            'payment_proof' => ['required', 'file', 'extensions:pdf,jpg,jpeg,png', 'max:10240'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'payment_proof.required' => 'Silakan pilih berkas bukti pembayaran.',
            'payment_proof.extensions' => 'Berkas bukti pembayaran harus berformat PDF, JPG, JPEG, atau PNG.',
            'payment_proof.max' => 'Ukuran berkas bukti pembayaran maksimal 10MB.',
        ]);

        $file = $request->file('payment_proof');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'Bukti_Bayar_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
        $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);

        DB::transaction(function () use ($suket, $path, $file, $user, $request) {
            if ($suket->billing_proof_path && Storage::disk(self::PRIVATE_DISK)->exists($suket->billing_proof_path)) {
                Storage::disk(self::PRIVATE_DISK)->delete($suket->billing_proof_path);
            }

            $suket->billing_proof_path = $path;
            $suket->billing_proof_name = $file->getClientOriginalName();
            $suket->billing_paid_at = now();
            $suket->billing_proof_status = 'pending';
            $suket->billing_proof_rejected_at = null;
            $suket->billing_proof_rejected_by = null;
            $suket->billing_proof_reject_note = null;
            $suket->updated_by = $user->id;
            $suket->save();

            $suket->recordHistory(
                action: 'payment_proof_uploaded',
                stageBefore: 7,
                stageAfter: 7,
                catatan: $request->input('catatan', 'Pemohon mengunggah bukti pembayaran SIMPONI/PNBP: ' . $file->getClientOriginalName()),
                userId: $user->id
            );
        });

        return redirect()->route('user.suket.index')
            ->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi pembayaran oleh Bendahara Balai K3.');
    }

    /**
     * Halaman Utama Internal Petugas: Monitoring Tahap 2 s/d Tahap 6
     */
    public function index(Request $request)
    {
        $this->ensureAccess();

        $user = auth()->user();
        $currentRole = $user?->role;

        // Jika user pemohon mengakses /suket-k3, alihkan ke portal permohonan pemohon
        if ($currentRole === 'user') {
            return redirect()->route('user.suket.index');
        }

        // Tentukan tahap yang diizinkan untuk role saat ini
        // Tentukan tahap yang diizinkan untuk role saat ini
        $roleAllowedStages = match ($currentRole) {
            'pcu', 'penguji_k3' => [2, 3],
            'qc' => ['qc'],
            'mp', 'kepala_balai' => [4],
            'bendahara' => [6, 7, 8],
            'admin' => [2, 3, 4, 5, 6, 7, 8, 9, 'all'],
            default => [2, 3, 'qc', 4, 5, 6, 7, 8, 9, 'all'], // superadmin
        };

        $requestedStage = $request->query('stage');
        if ($requestedStage && in_array($requestedStage, array_map('strval', $roleAllowedStages), true)) {
            $activeStage = $requestedStage;
        } else {
            $activeStage = (string) reset($roleAllowedStages);
        }

        $search = $request->query('search');

        // Query Suket K3 untuk Internal
        $suketQuery = SuketK3::with(['permohonan.company', 'user', 'creator', 'signer', 'publisher', 'qcUser', 'comments.user', 'lhuComments.user', 'suketComments.user', 'evaluator', 'histories.user', 'tagihanSender', 'tagihanAccUser', 'billingSender', 'billingVerifier', 'kuitansiGenerator', 'kuitansiSender'])
            ->latest('updated_at');

        // Filter berdasarkan Stage aktif
        if ($activeStage === '2') {
            $suketQuery->whereIn('status_tahap', [1, 2]);
        } elseif ($activeStage === '3') {
            $suketQuery->where('status_tahap', 3)->where(function ($q) {
                $q->whereNull('qc_status')->orWhere('qc_status', 'revision');
            });
        } elseif ($activeStage === 'qc') {
            $suketQuery->where('status_tahap', 3)->where('qc_status', 'pending');
        } elseif ($activeStage === '4') {
            $suketQuery->where('status_tahap', 4);
        } elseif ($activeStage === '5') {
            $suketQuery->where('status_tahap', 5);
        } elseif ($activeStage === '6') {
            $suketQuery->where('status_tahap', 6);
        } elseif ($activeStage === '7') {
            $suketQuery->where('status_tahap', 7);
        } elseif ($activeStage === '8') {
            $suketQuery->where('status_tahap', 8);
        } elseif ($activeStage === '9') {
            $suketQuery->where('status_tahap', 9);
        }

        if ($search) {
            $suketQuery->where(function ($q) use ($search) {
                $q->where('nomor_order', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('perusahaan_nama', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $sukets = $suketQuery->paginate(15)->withQueryString();

        // Hitung badge counter per tahap untuk internal staff
        $stageCounts = [
            2 => SuketK3::whereIn('status_tahap', [1, 2])->count(),
            3 => SuketK3::where('status_tahap', 3)->where(function ($q) {
                $q->whereNull('qc_status')->orWhere('qc_status', 'revision');
            })->count(),
            'qc' => SuketK3::where('status_tahap', 3)->where('qc_status', 'pending')->count(),
            4 => SuketK3::where('status_tahap', 4)->count(),
            5 => SuketK3::where('status_tahap', 5)->count(),
            6 => SuketK3::where('status_tahap', 6)->count(),
            7 => SuketK3::where('status_tahap', 7)->count(),
            8 => SuketK3::where('status_tahap', 8)->count(),
            9 => SuketK3::where('status_tahap', 9)->count(),
            'all' => SuketK3::count(),
        ];

        $totalActive = SuketK3::where('status_tahap', '<', 9)->count();
        $totalDone = SuketK3::where('status_tahap', 9)->count();

        return view('admin.suket.index', [
            'sukets' => $sukets,
            'stages' => SuketK3::STAGES,
            'stageCounts' => $stageCounts,
            'totalActive' => $totalActive,
            'totalDone' => $totalDone,
            'activeStage' => $activeStage,
            'roleAllowedStages' => array_map('strval', $roleAllowedStages),
            'search' => $search,
            'currentRole' => $currentRole,
            'faktorOptions' => SuketK3::FAKTOR_OPTIONS,
        ]);
    }

    /**
     * Input Utama untuk Admin/Internal jika ingin mendaftarkan suket secara manual
     */
    public function storeByOrder(Request $request)
    {
        $this->ensureAccess();

        $request->validate([
            'nomor_order' => ['required', 'string'],
            'faktor_k3' => ['required', 'array', 'min:1'],
            'faktor_k3.*' => ['in:fisika,kimia,biologi,ergonomi,psikologi'],
            'lhu_source' => ['required', 'in:auto,manual'],
            'perusahaan_nama' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'lhu_file' => ['nullable', 'file', 'max:20480'],
            'foto_pengujian' => ['nullable', 'file', 'max:20480'],
            'denah_lokasi' => ['nullable', 'file', 'max:20480'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $orderCode = trim($request->input('nomor_order'));
        $lhuSource = $request->input('lhu_source', 'auto');

        $permohonan = Permohonan::with(['company', 'draftLhu'])
            ->where('kode', $orderCode)
            ->orWhere('id', $orderCode)
            ->first();

        $companyName = $request->input('perusahaan_nama') 
            ?: ($permohonan?->company?->company_name ?? 'Perusahaan ' . $orderCode);
        $location = $request->input('lokasi') 
            ?: ($permohonan?->jadwal_lokasi ?: ($permohonan?->company?->company_city ?? 'Lokasi Uji K3'));

        $lhuPath = null;
        $lhuName = null;
        if ($lhuSource === 'auto' && $permohonan?->draftLhu) {
            $lhuPath = $permohonan->draftLhu->signed_file_path ?: $permohonan->draftLhu->final_file_path;
            $lhuName = $permohonan->draftLhu->signed_file_name ?: ($permohonan->draftLhu->final_file_name ?? 'LHU_' . $orderCode . '.pdf');
        }

        if ($request->hasFile('lhu_file')) {
            $file = $request->file('lhu_file');
            $ext = strtolower($file->getClientOriginalExtension());
            $lhuName = $file->getClientOriginalName();
            $lhuPath = $file->storeAs('suket_docs/lhu', 'lhu_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
            $lhuSource = 'manual';
        }

        $fotoPath = null;
        $fotoName = null;
        if ($request->hasFile('foto_pengujian')) {
            $file = $request->file('foto_pengujian');
            $ext = strtolower($file->getClientOriginalExtension());
            $fotoName = $file->getClientOriginalName();
            $fotoPath = $file->storeAs('suket_docs/foto', 'foto_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
        }

        $denahPath = null;
        $denahName = null;
        if ($request->hasFile('denah_lokasi')) {
            $file = $request->file('denah_lokasi');
            $ext = strtolower($file->getClientOriginalExtension());
            $denahName = $file->getClientOriginalName();
            $denahPath = $file->storeAs('suket_docs/denah', 'denah_' . time() . '_' . uniqid() . '.' . $ext, self::PRIVATE_DISK);
        }

        $suket = SuketK3::create([
            'user_id' => $permohonan?->user_id ?? auth()->id(),
            'permohonan_id' => $permohonan?->id,
            'nomor_order' => $permohonan?->kode ?? $orderCode,
            'status_tahap' => 1,
            'faktor_k3' => $request->input('faktor_k3'),
            'lhu_source' => $lhuSource,
            'lhu_file_path' => $lhuPath,
            'lhu_file_name' => $lhuName,
            'foto_pengujian_path' => $fotoPath,
            'foto_pengujian_name' => $fotoName,
            'denah_lokasi_path' => $denahPath,
            'denah_lokasi_name' => $denahName,
            'perusahaan_nama' => $companyName,
            'lokasi' => $location,
            'catatan' => $request->filled('catatan') 
                ? trim((string) $request->input('catatan')) 
                : ('Pengajuan suket didaftarkan melalui Nomor Order ' . ($permohonan?->kode ?? $orderCode)),
            'qc_status' => null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $suket->recordHistory(
            action: 'created',
            stageBefore: 1,
            stageAfter: 1,
            catatan: 'Pengajuan suket K3 didaftarkan secara manual oleh petugas.',
            userId: auth()->id()
        );

        return redirect()->route('suket.index')->with('success', "Pengajuan Suket untuk Nomor Order {$suket->nomor_order} berhasil didaftarkan.");
    }

    /**
     * Preview Dokumen Secara Inline (Tanpa Harus Download Terlebih Dahulu)
     */
    public function previewDocument(Request $request, SuketK3 $suket, string $type)
    {
        $this->ensureAccess();

        $user = auth()->user();
        $userRole = $user?->role;
        if ($userRole === 'user') {
            if ($suket->user_id !== $user->id && $suket->permohonan?->user_id !== $user->id) {
                abort(403, 'Akses ditolak ke permohonan suket ini.');
            }
            if (in_array($type, ['signed', 'final', 'draft', 'draft_pdf'], true) && empty($suket->sent_to_customer_at)) {
                abort(403, 'Dokumen Surat Keterangan K3 belum resmi diserahkan ke akun Anda. Mohon menunggu proses verifikasi dan penyerahan oleh Balai K3.');
            }
        }

        if (in_array($type, ['signed', 'final', 'draft', 'draft_pdf'], true)) {
            $path = match ($type) {
                'signed', 'final' => $suket->signed_file_path ?: $suket->draft_file_path,
                'draft', 'draft_pdf' => $suket->draft_file_path,
            };

            // Jika belum ada file draft tapi diminta draft, generate terlebih dahulu (.docx)
            if (!$path && in_array($type, ['draft', 'draft_pdf'], true)) {
                $this->saveAutoGeneratedDraft($suket);
                $path = $suket->draft_file_path;
            } elseif ($path && !empty($suket->nomor_surat) && str_ends_with(strtolower($path), '.docx')) {
                app(\App\Services\SuketDocxService::class)->syncDocxMetadata($suket);
            }

            // 1. Jika berkas fisik adalah PDF: stream sebagai PDF inline
            if ($path && Storage::disk(self::PRIVATE_DISK)->exists($path) && str_ends_with(strtolower($path), '.pdf')) {
                $fullPath = Storage::disk(self::PRIVATE_DISK)->path($path);
                return response()->file($fullPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
                ]);
            }

            // 2. Jika berkas fisik adalah gambar (jpg/png scan): stream gambar inline
            if ($path && Storage::disk(self::PRIVATE_DISK)->exists($path) && preg_match('/\.(jpg|jpeg|png)$/i', $path)) {
                $fullPath = Storage::disk(self::PRIVATE_DISK)->path($path);
                $mime = Storage::disk(self::PRIVATE_DISK)->mimeType($path) ?: 'image/jpeg';
                return response()->file($fullPath, [
                    'Content-Type' => $mime,
                    'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
                ]);
            }

            // 3. Jika berkas fisik adalah DOCX hasil unggahan revisi penguji (Draft_Revisi_...)
            $isCustomUploadedRevision = $path && (str_contains($path, 'Draft_Revisi_') || str_contains($path, 'suket_draft_'));
            if ($isCustomUploadedRevision && Storage::disk(self::PRIVATE_DISK)->exists($path) && str_ends_with(strtolower($path), '.docx')) {
                $fullPath = Storage::disk(self::PRIVATE_DISK)->path($path);
                $docxService = app(\App\Services\SuketDocxService::class);

                if ($type === 'draft_pdf' || ($type === 'draft' && $request->query('format') === 'pdf')) {
                    $pdfBinary = $docxService->renderDocxToPdf($fullPath, $suket);
                    if ($pdfBinary) {
                        return response($pdfBinary, 200, [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'inline; filename="Draft_Suket_' . $suket->nomor_order . '.pdf"',
                        ]);
                    }
                } else {
                    $htmlOutput = $docxService->renderDocxToHtmlPreview($fullPath, $suket);
                    if ($htmlOutput) {
                        return response($htmlOutput, 200, [
                            'Content-Type' => 'text/html; charset=UTF-8',
                        ]);
                    }
                }
            }

            // 4. Jika diminta output PDF (untuk PDF.js Annotator Review QC / Draf PDF preview fallback)
            if ($type === 'draft_pdf' || ($type === 'draft' && $request->query('format') === 'pdf')) {
                $logoAsset = $this->resolveWordHeaderLogoAsset();
                $logoAssetBase64 = $logoAsset ? base64_encode($logoAsset['binary']) : null;
                $kepalaBalai = User::whereIn('role', ['kepala_balai', 'mp'])->first();

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.word.suket_k3_permenaker', [
                    'suket' => $suket,
                    'logoAssetBase64' => $logoAssetBase64,
                    'kepalaBalaiNama' => $kepalaBalai?->name ?? 'Dr. H. Agus Triyono, S.T., M.Kes.',
                    'kepalaBalaiNip' => $kepalaBalai?->nip ?? '19750812 200212 1 001',
                    'isPreviewMode' => true,
                ])->setPaper('a4', 'portrait');

                return response($pdf->output(), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="Draft_Suket_' . $suket->nomor_order . '.pdf"',
                ]);
            }

            // 5. Fallback template HTML bawaan jika format Word HTML lama atau file belum ada
            $logoAsset = $this->resolveWordHeaderLogoAsset();
            $logoAssetBase64 = $logoAsset ? base64_encode($logoAsset['binary']) : null;
            $kepalaBalai = User::whereIn('role', ['kepala_balai', 'mp'])->first();

            return response()->view('admin.word.suket_k3_permenaker', [
                'suket' => $suket,
                'logoAssetBase64' => $logoAssetBase64,
                'kepalaBalaiNama' => $kepalaBalai?->name ?? 'Dr. H. Agus Triyono, S.T., M.Kes.',
                'kepalaBalaiNip' => $kepalaBalai?->nip ?? '19750812 200212 1 001',
                'isPreviewMode' => true,
            ], 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]);
        }

        // Khusus jenis Tagihan & Kuitansi (Hybrid: Berkas Upload atau Auto-Generated PDF)
        if ($type === 'tagihan') {
            if ($suket->surat_tagihan_file_path && Storage::disk(self::PRIVATE_DISK)->exists($suket->surat_tagihan_file_path)) {
                $fullPath = Storage::disk(self::PRIVATE_DISK)->path($suket->surat_tagihan_file_path);
                return response()->file($fullPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($suket->surat_tagihan_file_path) . '"',
                ]);
            }

            // Fallback auto generate PDF on the fly
            $pdf = $this->generateSuratTagihanPdf($suket);
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Surat_Tagihan_' . $suket->nomor_order . '.pdf"',
            ]);
        }

        if ($type === 'kuitansi') {
            if ($suket->kuitansi_file_path && Storage::disk(self::PRIVATE_DISK)->exists($suket->kuitansi_file_path)) {
                $fullPath = Storage::disk(self::PRIVATE_DISK)->path($suket->kuitansi_file_path);
                return response()->file($fullPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($suket->kuitansi_file_path) . '"',
                ]);
            }

            // Fallback auto generate PDF on the fly
            $pdf = $this->generateKuitansiPdf($suket);
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Kuitansi_' . $suket->nomor_order . '.pdf"',
            ]);
        }

        $path = match ($type) {
            'lhu' => $suket->effective_lhu_path ?: $suket->lhu_file_path,
            'foto' => $suket->foto_pengujian_path,
            'denah' => $suket->denah_lokasi_path,
            'tagihan' => $suket->surat_tagihan_file_path,
            'billing' => $suket->billing_file_path,
            'guide', 'panduan', 'billing_guide' => $suket->effectiveBillingGuidePath(),
            'proof', 'payment_proof' => $suket->billing_proof_path,
            'kuitansi' => $suket->kuitansi_file_path,
            default => null,
        };

        $disk = self::PRIVATE_DISK;
        if ($path && !Storage::disk($disk)->exists($path) && Storage::disk(self::LEGACY_DISK)->exists($path)) {
            $disk = self::LEGACY_DISK;
        }

        if (!$path || !Storage::disk($disk)->exists($path)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        $fullPath = Storage::disk($disk)->path($path);
        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        $filename = basename($path);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Tambah Sorotan Kesalahan / Catatan Evaluasi Dokumen LHU atau Draf Suket K3 (QC)
     */
    public function addComment(Request $request, SuketK3 $suket)
    {
        $this->ensureAccess();

        $request->validate([
            'bagian' => ['nullable', 'string', 'max:255'],
            'highlight_text' => ['nullable', 'string', 'max:2000'],
            'comment' => ['required', 'string', 'max:2000'],
            'target' => ['nullable', 'in:internal,pemohon'],
            'tipe' => ['nullable', 'in:kesalahan,saran,catatan'],
            'document_type' => ['nullable', 'in:lhu,suket'],
        ], [
            'comment.required' => 'Catatan / penjelasan koreksi wajib diisi.',
        ]);

        $user = auth()->user();
        $docType = $request->input('document_type', 'lhu');
        $target = $request->input('target', ($docType === 'suket' ? 'internal' : 'pemohon'));

        $commentModel = $suket->comments()->create([
            'user_id' => $user?->id,
            'target' => $target,
            'bagian' => $request->filled('bagian') ? trim((string) $request->input('bagian')) : null,
            'highlight_text' => $request->filled('highlight_text') ? trim((string) $request->input('highlight_text')) : null,
            'tipe' => $request->input('tipe', 'kesalahan'),
            'document_type' => $docType,
            'comment' => trim((string) $request->input('comment')),
        ]);

        $docLabel = $docType === 'suket' ? 'Draf Suket K3 (QC)' : 'LHU';

        $suket->recordHistory(
            action: 'comment_added',
            stageBefore: $suket->status_tahap,
            stageAfter: $suket->status_tahap,
            catatan: "Catatan / sorotan evaluasi {$docLabel} ditambahkan: " . Str::limit($commentModel->comment, 80),
            userId: $user?->id
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Sorotan kesalahan / catatan evaluasi {$docLabel} berhasil ditambahkan.",
                'comment' => [
                    'id' => $commentModel->id,
                    'bagian' => $commentModel->bagian,
                    'highlight_text' => $commentModel->highlight_text,
                    'comment' => $commentModel->comment,
                    'document_type' => $commentModel->document_type,
                    'created_at_human' => $commentModel->created_at?->diffForHumans() ?? 'Baru saja',
                    'delete_url' => route('suket.comment.delete', [$suket->id, $commentModel->id]),
                ],
            ]);
        }

        return redirect()->back()->with('success', "Sorotan kesalahan / catatan evaluasi {$docLabel} berhasil ditambahkan.");
    }

    /**
     * Hapus Sorotan Kesalahan Evaluasi Dokumen LHU
     */
    public function deleteComment(Request $request, SuketK3 $suket, SuketK3Comment $comment)
    {
        $this->ensureAccess();

        if ($comment->suket_id === $suket->id) {
            $comment->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sorotan catatan evaluasi berhasil dihapus.',
                ]);
            }

            return redirect()->back()->with('success', 'Sorotan catatan evaluasi berhasil dihapus.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan tidak sesuai dengan berkas suket.',
            ], 422);
        }

        return redirect()->back()->with('error', 'Catatan tidak sesuai dengan berkas suket.');
    }

    /**
     * Transisi Status Antar 6 Tahap (State Machine dengan RBAC & File Hooks)
     */
    public function advanceStage(Request $request, SuketK3 $suket)
    {
        $this->ensureAccess();

        $request->validate([
            'action' => ['required', 'in:next,revision,upload_draft,reject_evaluasi,send_to_qc,send_tagihan,acc_tagihan,send_billing,upload_guide,upload_payment_proof,verify_payment,reject_payment,send_kuitansi,sync_from_permohonan'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['nullable', 'date'],
            'signed_document' => ['nullable', 'file', 'extensions:pdf,doc,docx,jpg,jpeg,png', 'max:20480'],
            'revised_draft' => ['nullable', 'file', 'extensions:doc,docx,pdf', 'max:20480'],
            'surat_tagihan_nominal' => ['nullable'],
            'surat_tagihan_file' => ['nullable', 'file', 'max:20480'],
            'billing_kode' => ['nullable', 'string', 'max:100'],
            'billing_expires_at' => ['nullable', 'date'],
            'billing_file' => ['nullable', 'file', 'max:20480'],
            'billing_guide_file' => ['nullable', 'file', 'extensions:pdf', 'max:20480'],
            'payment_proof' => ['nullable', 'file', 'max:20480'],
            'kuitansi_nomor' => ['nullable', 'string', 'max:100'],
            'kuitansi_file' => ['nullable', 'file', 'max:20480'],
        ], [
            'billing_guide_file.extensions' => 'Dokumen panduan pembayaran harus berupa berkas PDF.',
            'signed_document.extensions' => 'Dokumen tanda tangan harus berupa file bertipe: PDF, DOC, DOCX, JPG, JPEG, atau PNG.',
            'signed_document.max' => 'Ukuran berkas tanda tangan tidak boleh lebih dari 20MB.',
            'revised_draft.extensions' => 'Dokumen draf revisi harus berupa file bertipe: DOC, DOCX, atau PDF.',
            'revised_draft.max' => 'Ukuran berkas draf revisi tidak boleh lebih dari 20MB.',
        ]);

        $user = auth()->user();
        $userRole = $user?->role;
        $currentStage = (int) $suket->status_tahap;

        // Otorisasi role untuk memproses tahap ini
        if (!$suket->canRoleProcess($userRole)) {
            return redirect()->back()->with('error', "Role [{$userRole}] tidak memiliki kewenangan untuk memproses tahap ini.");
        }

        // AKSI KHUSUS TAHAP 2: Penolakan / Minta Revisi LHU ke Pemohon
        if ($request->action === 'reject_evaluasi' && $currentStage === 2) {
            $catatanTolak = trim((string) $request->input('catatan'));
            if ($catatanTolak === '') {
                return redirect()->back()->with('error', 'Alasan / instruksi permintaan revisi ke pemohon wajib dituliskan.');
            }

            DB::transaction(function () use ($suket, $catatanTolak, $user) {
                $suket->evaluasi_status = 'rejected';
                $suket->evaluasi_by = $user->id;
                $suket->evaluasi_at = now();
                $suket->catatan_evaluasi = $catatanTolak;
                $suket->updated_by = $user->id;
                $suket->save();

                $suket->recordHistory(
                    action: 'evaluasi_rejected',
                    stageBefore: 2,
                    stageAfter: 2,
                    catatan: 'Evaluator meminta revisi ke pemohon: ' . $catatanTolak,
                    userId: $user->id
                );
            });

            return redirect()->route('suket.index', ['stage' => 2])
                ->with('warning', "Permintaan revisi berhasil dikirimkan ke pemohon untuk Suket {$suket->nomor_order}.");
        }

        // AKSI KHUSUS TAHAP 3: Kirim draf ke Tim QC (bukan langsung ke Penandatanganan Kepala Balai)
        if (($request->action === 'send_to_qc' || $request->action === 'next') && $currentStage === 3) {
            $catatanPengantar = $request->filled('catatan')
                ? trim((string) $request->input('catatan'))
                : 'Draf dokumen Suket K3 telah disusun dan diajukan ke Tim QC untuk peninjauan kelayakan.';

            DB::transaction(function () use ($suket, $catatanPengantar, $user, $request) {
                // Cek jika penguji mengunggah revisi draf sekaligus
                if ($request->hasFile('revised_draft')) {
                    $file = $request->file('revised_draft');
                    \App\Support\SafeDocumentUpload::validateOrFail($file, 'revised_draft');
                    $ext = strtolower($file->getClientOriginalExtension());
                    $filename = 'Draft_Revisi_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                    $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);

                    if ($suket->draft_file_path && $suket->draft_file_path !== $path && Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
                        Storage::disk(self::PRIVATE_DISK)->delete($suket->draft_file_path);
                    }

                    $suket->draft_file_path = $path;
                    $suket->draft_file_name = $file->getClientOriginalName();
                }

                // Pastikan draf file sudah tersimpan jika belum ada
                if (!$suket->draft_file_path || !Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
                    $this->saveAutoGeneratedDraft($suket);
                }

                $suket->qc_status = 'pending';
                $suket->qc_note = null;
                $suket->catatan = $catatanPengantar;
                $suket->updated_by = $user->id;
                $suket->save();

                $suket->recordHistory(
                    action: 'send_to_qc',
                    stageBefore: 3,
                    stageAfter: 3,
                    catatan: $catatanPengantar,
                    userId: $user->id
                );
            });

            return redirect()->route('suket.index', ['stage' => 3])
                ->with('success', "Draf Suket {$suket->nomor_order} berhasil diajukan ke Tim QC untuk review kelayakan.");
        }

        // Validasi khusus saat mau lanjut dari Tahap 4 ke Tahap 5
        if ($request->action === 'next' && $currentStage === 4) {
            if (!$request->hasFile('signed_document') && empty($suket->signed_file_path)) {
                return redirect()->back()->with('error', 'Silakan pilih dan unggah berkas dokumen Surat Keterangan K3 yang telah ditandatangani oleh Kepala Balai terlebih dahulu.');
            }
        }

        DB::transaction(function () use ($request, $suket, $currentStage, $user) {
            if ($request->action === 'next' && $currentStage <= 9) {
                if (!in_array($currentStage, [6, 7], true)) {
                    $nextStage = min(9, $currentStage + 1);
                    $suket->status_tahap = $nextStage;
                }

                // Hook spesifik per tahapan
                if ($currentStage === 1) {
                    if ($request->filled('catatan')) {
                        $suket->catatan = $request->input('catatan');
                    }
                } elseif ($currentStage === 2) {
                    // Evaluasi Disetujui -> Lanjut ke Tahap 3
                    $suket->evaluasi_status = 'approved';
                    $suket->qc_status = null;
                    $suket->evaluasi_by = $user->id;
                    $suket->evaluasi_at = now();
                    $suket->catatan_evaluasi = $request->input('catatan', 'Dokumen evaluasi disetujui oleh Penguji K3.');

                    $suket->recordHistory(
                        action: 'evaluasi_approved',
                        stageBefore: 2,
                        stageAfter: 3,
                        catatan: $suket->catatan_evaluasi,
                        userId: $user->id
                    );
                } elseif ($currentStage === 4) {
                    // Cek jika admin / Kepala Balai mengunggah file hasil scan TTD / TTE
                    if ($request->hasFile('signed_document')) {
                        $file = $request->file('signed_document');
                        $ext = strtolower($file->getClientOriginalExtension());
                        $path = $file->storeAs('suket_docs/' . $suket->id, 'Signed_' . time() . '.' . $ext, self::PRIVATE_DISK);
                        $suket->signed_file_path = $path;
                        $suket->signed_file_name = $file->getClientOriginalName();
                    }
                    $suket->signed_at = now();
                    $suket->signed_by = $user->id;

                    $suket->recordHistory(
                        action: 'signed',
                        stageBefore: 4,
                        stageAfter: 5,
                        catatan: $request->input('catatan', 'Surat Keterangan K3 telah ditandatangani dan disahkan oleh Kepala Balai.'),
                        nomorSurat: $suket->nomor_surat,
                        userId: $user->id
                    );
                } elseif ($currentStage === 5) {
                    // Finalisasi Penerbitan Suket Resmi -> Lanjut ke Tahap 6 (Surat Tagihan)
                    if ($request->filled('nomor_surat')) {
                        $suket->nomor_surat = trim((string) $request->input('nomor_surat'));
                    }
                    if ($request->filled('tanggal_surat')) {
                        $suket->tanggal_surat = $request->input('tanggal_surat');
                    }
                    $suket->published_at = now();
                    $suket->published_by = $user->id;
                    $suket->status_tahap = 6; // Lanjut ke Tahap 6: Surat Tagihan

                    // Simpan draf otomatis jika belum ada berkas draf, atau sinkronkan jika sudah ada
                    if (empty($suket->draft_file_path) || !Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
                        $this->saveAutoGeneratedDraft($suket);
                    } else {
                        app(\App\Services\SuketDocxService::class)->syncDocxMetadata($suket);
                    }

                    $suket->recordHistory(
                        action: 'published',
                        stageBefore: 5,
                        stageAfter: 6,
                        catatan: $request->input('catatan', 'Surat Keterangan K3 resmi diterbitkan. Lanjut ke Tahap 6 (Surat Tagihan).'),
                        nomorSurat: $suket->nomor_surat,
                        userId: $user->id
                    );
                } elseif ($currentStage === 6) {
                    // Simpan Surat Tagihan & Kirim ke Pemohon (Tetap di Tahap 6 menunggu ACC pemohon)
                    if ($request->filled('surat_tagihan_nominal')) {
                        $rawNominal = str_replace(['.', ','], ['', '.'], (string) $request->input('surat_tagihan_nominal'));
                        $suket->surat_tagihan_nominal = (float) $rawNominal;
                    }
                    if ($request->hasFile('surat_tagihan_file')) {
                        $file = $request->file('surat_tagihan_file');
                        $ext = strtolower($file->getClientOriginalExtension());
                        $filename = 'Tagihan_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                        $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);
                        $suket->surat_tagihan_file_path = $path;
                        $suket->surat_tagihan_file_name = $file->getClientOriginalName();
                    } else {
                        $this->saveAutoGeneratedTagihan($suket);
                    }
                    $suket->surat_tagihan_sent_at = now();
                    $suket->surat_tagihan_sent_by = $user->id;
                    $suket->status_tahap = 6; // Tetap di Tahap 6: Menunggu ACC pemohon

                    $suket->recordHistory(
                        action: 'tagihan_sent',
                        stageBefore: 6,
                        stageAfter: 6,
                        catatan: $request->input('catatan', 'Surat tagihan suket diterbitkan dan dikirimkan ke pemohon. Menunggu persetujuan (ACC) pemohon.'),
                        userId: $user->id
                    );
                } elseif ($currentStage === 7) {
                    // Simpan Kode Billing & Panduan Pembayaran (Tetap di Tahap 7 menunggu pembayaran pemohon)
                    if ($request->filled('billing_kode')) {
                        $suket->billing_kode = trim((string) $request->input('billing_kode'));
                    }
                    if ($request->filled('billing_expires_at')) {
                        $suket->billing_expires_at = $request->input('billing_expires_at');
                    } elseif (!$suket->billing_expires_at) {
                        $suket->billing_expires_at = now()->addHours(24);
                    }
                    if ($request->hasFile('billing_file')) {
                        $file = $request->file('billing_file');
                        $ext = strtolower($file->getClientOriginalExtension());
                        $filename = 'Billing_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                        $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);
                        $suket->billing_file_path = $path;
                        $suket->billing_file_name = $file->getClientOriginalName();
                    }
                    if ($request->hasFile('billing_guide_file')) {
                        $gfile = $request->file('billing_guide_file');
                        $gext = strtolower($gfile->getClientOriginalExtension());
                        $gfilename = 'Panduan_' . time() . '_' . Str::slug(pathinfo($gfile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $gext;
                        $gpath = $gfile->storeAs('suket_docs/' . $suket->id, $gfilename, self::PRIVATE_DISK);
                        $suket->billing_guide_path = $gpath;
                        $suket->billing_guide_name = $gfile->getClientOriginalName();
                    }
                    $suket->billing_sent_at = now();
                    $suket->billing_sent_by = $user->id;
                    $suket->status_tahap = 7; // Tetap di Tahap 7: Menunggu pembayaran pemohon

                    $suket->recordHistory(
                        action: 'billing_sent',
                        stageBefore: 7,
                        stageAfter: 7,
                        catatan: 'Kode billing [' . $suket->billing_kode . '] dan Panduan Pembayaran dikirim ke pemohon.',
                        userId: $user->id
                    );
                } elseif ($currentStage === 8) {
                    // Manual advance dari Tahap 8 ke Tahap 9 (Penyerahan Suket)
                    if (!$suket->kuitansi_generated_at) {
                        $suket->kuitansi_nomor = $suket->kuitansi_nomor ?: ('KWT/BK3-SBY/' . now()->format('Ymd') . '/' . $suket->id);
                        $suket->kuitansi_generated_at = now();
                        $suket->kuitansi_generated_by = $user->id;
                        $suket->kuitansi_sent_at = now();
                        $suket->kuitansi_sent_by = $user->id;
                    }
                    if ($request->hasFile('kuitansi_file')) {
                        $file = $request->file('kuitansi_file');
                        $ext = strtolower($file->getClientOriginalExtension());
                        $filename = 'Kuitansi_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                        $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);
                        $suket->kuitansi_file_path = $path;
                        $suket->kuitansi_file_name = $file->getClientOriginalName();
                    } elseif (empty($suket->kuitansi_file_path) || !Storage::disk(self::PRIVATE_DISK)->exists($suket->kuitansi_file_path)) {
                        $this->saveAutoGeneratedKuitansi($suket);
                    }
                    $suket->status_tahap = 9;

                    $suket->recordHistory(
                        action: 'kuitansi_sent',
                        stageBefore: 8,
                        stageAfter: 9,
                        catatan: $request->input('catatan', 'Kuitansi resmi diterbitkan. Lanjut ke Tahap 9 (Penyerahan Suket).'),
                        userId: $user->id
                    );
                } elseif ($currentStage === 9) {
                    // Konfirmasi penyerahan ke pemohon (Final)
                    $suket->sent_to_customer_at = now();
                    $suket->sent_to_customer_by = $user->id;
                    $suket->metode_pengiriman = 'Portal Digital Web Balai K3';

                    $suket->recordHistory(
                        action: 'sent_to_customer',
                        stageBefore: 9,
                        stageAfter: 9,
                        catatan: $request->input('catatan', 'Surat Keterangan K3 resmi diserahkan ke akun pemohon via Portal Web Balai K3.'),
                        nomorSurat: $suket->nomor_surat,
                        userId: $user->id
                    );
                }
            } elseif ($request->action === 'upload_draft') {
                if ($request->hasFile('revised_draft')) {
                    $file = $request->file('revised_draft');
                    \App\Support\SafeDocumentUpload::validateOrFail($file, 'revised_draft');
                    $ext = strtolower($file->getClientOriginalExtension());
                    $filename = 'Draft_Revisi_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                    $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);

                    if ($suket->draft_file_path && $suket->draft_file_path !== $path && Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
                        Storage::disk(self::PRIVATE_DISK)->delete($suket->draft_file_path);
                    }

                    $suket->draft_file_path = $path;
                    $suket->draft_file_name = $file->getClientOriginalName();

                    if ($ext === 'docx' && !empty($suket->nomor_surat)) {
                        app(\App\Services\SuketDocxService::class)->syncDocxMetadata($suket);
                    }

                    $suket->recordHistory(
                        action: 'upload_draft',
                        stageBefore: 3,
                        stageAfter: 3,
                        catatan: $request->input('catatan', 'Penguji mengunggah berkas draf revisi: ' . $file->getClientOriginalName()),
                        userId: $user->id
                    );
                }
            } elseif ($request->action === 'revision' && $currentStage > 1) {
                $suket->status_tahap = $currentStage - 1;
                if ($currentStage === 4) {
                    $suket->qc_status = 'pending';
                }
                $suket->recordHistory(
                    action: 'stage_returned',
                    stageBefore: $currentStage,
                    stageAfter: $suket->status_tahap,
                    catatan: $request->input('catatan', 'Tahapan dikembalikan ke tahap sebelumnya.'),
                    userId: $user->id
                );
            } elseif ($request->action === 'send_tagihan') {
                if ($request->filled('surat_tagihan_nominal')) {
                    $rawNominal = str_replace(['.', ','], ['', '.'], (string) $request->input('surat_tagihan_nominal'));
                    $suket->surat_tagihan_nominal = (float) $rawNominal;
                }
                if ($request->hasFile('surat_tagihan_file')) {
                    $file = $request->file('surat_tagihan_file');
                    $ext = strtolower($file->getClientOriginalExtension());
                    $filename = 'Tagihan_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                    $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);
                    $suket->surat_tagihan_file_path = $path;
                    $suket->surat_tagihan_file_name = $file->getClientOriginalName();
                } else {
                    $this->saveAutoGeneratedTagihan($suket);
                }
                $suket->surat_tagihan_sent_at = now();
                $suket->surat_tagihan_sent_by = $user->id;

                $suket->recordHistory(
                    action: 'tagihan_sent',
                    stageBefore: 6,
                    stageAfter: 6,
                    catatan: $request->input('catatan', 'Surat tagihan suket diterbitkan dan dikirim ke pemohon.'),
                    userId: $user->id
                );
            } elseif ($request->action === 'acc_tagihan') {
                $suket->surat_tagihan_acc_at = now();
                $suket->surat_tagihan_acc_by = $user->id;
                $suket->status_tahap = 7;

                $suket->recordHistory(
                    action: 'tagihan_acced',
                    stageBefore: 6,
                    stageAfter: 7,
                    catatan: $request->input('catatan', 'Surat tagihan disetujui (ACC). Lanjut ke Tahap 7 (Kode Billing).'),
                    userId: $user->id
                );
            } elseif ($request->action === 'send_billing') {
                $suket->billing_kode = trim((string) $request->input('billing_kode'));
                if ($request->filled('billing_expires_at')) {
                    $suket->billing_expires_at = $request->input('billing_expires_at');
                } elseif (!$suket->billing_expires_at) {
                    $suket->billing_expires_at = now()->addHours(24);
                }
                if ($request->hasFile('billing_file')) {
                    $file = $request->file('billing_file');
                    $ext = strtolower($file->getClientOriginalExtension());
                    $filename = 'Billing_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                    $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);
                    $suket->billing_file_path = $path;
                    $suket->billing_file_name = $file->getClientOriginalName();
                }
                if ($request->hasFile('billing_guide_file')) {
                    $gfile = $request->file('billing_guide_file');
                    $gext = strtolower($gfile->getClientOriginalExtension());
                    $gfilename = 'Panduan_' . time() . '_' . Str::slug(pathinfo($gfile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $gext;
                    $gpath = $gfile->storeAs('suket_docs/' . $suket->id, $gfilename, self::PRIVATE_DISK);
                    $suket->billing_guide_path = $gpath;
                    $suket->billing_guide_name = $gfile->getClientOriginalName();
                }
                $suket->billing_sent_at = now();
                $suket->billing_sent_by = $user->id;
                $suket->billing_proof_status = 'pending';
                $suket->billing_proof_rejected_at = null;
                $suket->billing_proof_rejected_by = null;
                $suket->billing_proof_reject_note = null;

                $suket->recordHistory(
                    action: 'billing_sent',
                    stageBefore: 7,
                    stageAfter: 7,
                    catatan: 'Kode billing [' . $suket->billing_kode . '] dan Panduan Pembayaran dikirim ke pemohon.',
                    userId: $user->id
                );
            } elseif ($request->action === 'upload_guide') {
                if ($request->hasFile('billing_guide_file')) {
                    $gfile = $request->file('billing_guide_file');
                    $gext = strtolower($gfile->getClientOriginalExtension());
                    $gfilename = 'Panduan_' . time() . '_' . Str::slug(pathinfo($gfile->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $gext;
                    $gpath = $gfile->storeAs('suket_docs/' . $suket->id, $gfilename, self::PRIVATE_DISK);
                    $suket->billing_guide_path = $gpath;
                    $suket->billing_guide_name = $gfile->getClientOriginalName();

                    $suket->recordHistory(
                        action: 'guide_uploaded',
                        stageBefore: 7,
                        stageAfter: 7,
                        catatan: 'Petugas mengunggah berkas panduan pembayaran: ' . $gfile->getClientOriginalName(),
                        userId: $user->id
                    );
                }
            } elseif ($request->action === 'upload_payment_proof') {
                if ($request->hasFile('payment_proof')) {
                    $file = $request->file('payment_proof');
                    $ext = strtolower($file->getClientOriginalExtension());
                    $filename = 'Bukti_Bayar_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                    $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);
                    $suket->billing_proof_path = $path;
                    $suket->billing_proof_name = $file->getClientOriginalName();
                    $suket->billing_paid_at = now();
                    $suket->billing_proof_status = 'pending';
                    $suket->billing_proof_rejected_at = null;
                    $suket->billing_proof_rejected_by = null;
                    $suket->billing_proof_reject_note = null;
                }
                $suket->recordHistory(
                    action: 'payment_proof_uploaded',
                    stageBefore: 7,
                    stageAfter: 7,
                    catatan: $request->input('catatan', 'Bukti pembayaran diunggah.'),
                    userId: $user->id
                );
            } elseif ($request->action === 'verify_payment') {
                $suket->billing_verified_at = now();
                $suket->billing_verified_by = $user->id;
                $suket->billing_proof_status = 'approved';
                $suket->status_tahap = 8;

                $suket->recordHistory(
                    action: 'payment_verified',
                    stageBefore: 7,
                    stageAfter: 8,
                    catatan: $request->input('catatan', 'Pembayaran diverifikasi oleh Bendahara/Admin. Lanjut ke Tahap 8 (Kuitansi).'),
                    userId: $user->id
                );
            } elseif ($request->action === 'reject_payment') {
                $alasanTolak = trim((string) ($request->input('reject_proof_note') ?: $request->input('catatan')));
                if ($alasanTolak === '') {
                    $alasanTolak = 'Bukti pembayaran tidak sesuai atau tidak valid. Silakan upload ulang.';
                }
                $suket->billing_proof_status = 'rejected';
                $suket->billing_proof_rejected_at = now();
                $suket->billing_proof_rejected_by = $user->id;
                $suket->billing_proof_reject_note = $alasanTolak;
                $suket->billing_verified_at = null;
                $suket->billing_verified_by = null;

                $suket->recordHistory(
                    action: 'payment_rejected',
                    stageBefore: 7,
                    stageAfter: 7,
                    catatan: 'Bukti pembayaran DITOLAK oleh Bendahara/Admin: ' . $alasanTolak,
                    userId: $user->id
                );
            } elseif ($request->action === 'send_kuitansi') {
                $kwtNo = $request->filled('kuitansi_nomor')
                    ? trim((string) $request->input('kuitansi_nomor'))
                    : ('KWT/BK3-SBY/' . now()->format('Ymd') . '/' . $suket->id);
                $suket->kuitansi_nomor = $kwtNo;

                if ($request->hasFile('kuitansi_file')) {
                    $file = $request->file('kuitansi_file');
                    $ext = strtolower($file->getClientOriginalExtension());
                    $filename = 'Kuitansi_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $ext;
                    $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);
                    $suket->kuitansi_file_path = $path;
                    $suket->kuitansi_file_name = $file->getClientOriginalName();
                } else {
                    $this->saveAutoGeneratedKuitansi($suket);
                }
                $suket->kuitansi_generated_at = now();
                $suket->kuitansi_generated_by = $user->id;
                $suket->kuitansi_sent_at = now();
                $suket->kuitansi_sent_by = $user->id;
                $suket->status_tahap = 9;

                $suket->recordHistory(
                    action: 'kuitansi_sent',
                    stageBefore: 8,
                    stageAfter: 9,
                    catatan: 'Kuitansi resmi [' . $kwtNo . '] diterbitkan dan diteruskan ke pelanggan. Lanjut ke Tahap 9 (Penyerahan Suket).',
                    userId: $user->id
                );
            } elseif ($request->action === 'sync_from_permohonan') {
                $draft = $suket->permohonan?->draftLhu;
                if ($draft) {
                    if ($draft->surat_tagihan_generated_at) {
                        $suket->surat_tagihan_sent_at = $draft->surat_tagihan_generated_at;
                        $suket->surat_tagihan_sent_by = $draft->surat_tagihan_generated_by ?: $user->id;
                        $suket->surat_tagihan_acc_at = $draft->surat_tagihan_generated_at;
                        $suket->surat_tagihan_acc_by = $user->id;
                    }
                    if ($draft->billing_sent_at || $draft->billing_file_path) {
                        $suket->billing_kode = $draft->billing_kode ?: ($draft->billing_file_name ?? 'SIMPONI-PNBP');
                        $suket->billing_file_path = $draft->billing_file_path;
                        $suket->billing_file_name = $draft->billing_file_name;
                        $suket->billing_sent_at = $draft->billing_sent_at ?: now();
                        $suket->billing_sent_by = $draft->billing_sent_by ?: $user->id;
                        $suket->billing_expires_at = $draft->billing_expires_at;
                        $suket->billing_proof_path = $draft->billing_payment_proof_path;
                        $suket->billing_proof_name = $draft->billing_payment_proof_name;
                        $suket->billing_paid_at = $draft->billing_paid_at;
                        $suket->billing_verified_at = $draft->billing_verified_at ?: now();
                        $suket->billing_verified_by = $draft->billing_verified_by ?: $user->id;
                    }
                    if ($draft->invoice_generated_at || $draft->invoice_file_path) {
                        $suket->kuitansi_nomor = 'KWT/BK3-SBY/' . ($suket->nomor_order);
                        $suket->kuitansi_file_path = $draft->invoice_file_path;
                        $suket->kuitansi_file_name = $draft->invoice_file_name;
                        $suket->kuitansi_generated_at = $draft->invoice_generated_at ?: now();
                        $suket->kuitansi_generated_by = $draft->invoice_generated_by ?: $user->id;
                        $suket->kuitansi_sent_at = $draft->invoice_generated_at ?: now();
                        $suket->kuitansi_sent_by = $user->id;
                    }
                    $suket->status_tahap = 9;
                    $suket->recordHistory(
                        action: 'synced_financial',
                        stageBefore: $currentStage,
                        stageAfter: 9,
                        catatan: 'Data administrasi keuangan (Surat Tagihan, Billing, Kuitansi) disinkronkan dari Alur Kerja Permohonan ' . $suket->nomor_order . '. Berkas siap diserahkan.',
                        userId: $user->id
                    );
                }
            }

            if ($request->filled('catatan')) {
                $suket->catatan = $request->input('catatan');
            }

            $suket->updated_by = $user->id;
            $suket->save();
        });

        if ($request->action === 'upload_draft') {
            return redirect()->route('suket.index', ['stage' => 3])
                ->with('success', "Berkas draf revisi Suket ({$suket->draft_file_name}) berhasil diunggah dan menggantikan draf sebelumnya.");
        }

        if ($request->action === 'upload_guide') {
            return redirect()->route('suket.index', ['stage' => 7])
                ->with('success', "Berkas Panduan Pembayaran ({$suket->billing_guide_name}) berhasil disimpan.");
        }

        if ($request->action === 'reject_payment') {
            return redirect()->route('suket.index', ['stage' => 7])
                ->with('warning', "Bukti transfer permohonan {$suket->nomor_order} DITOLAK. Pemohon telah diminta mengunggah ulang bukti transfer yang benar.");
        }

        if ($request->action === 'send_tagihan' || ($currentStage === 6 && $suket->status_tahap === 6)) {
            return redirect()->route('suket.index', ['stage' => 6])
                ->with('success', "Surat Tagihan untuk nomor order {$suket->nomor_order} berhasil dikirim ke pemohon. Menunggu persetujuan (ACC) pemohon.");
        }

        if ($request->action === 'send_billing' || ($currentStage === 7 && $suket->status_tahap === 7 && $request->action !== 'reject_payment')) {
            return redirect()->route('suket.index', ['stage' => 7])
                ->with('success', "Kode Billing dan Panduan Pembayaran untuk {$suket->nomor_order} berhasil dikirim ke pemohon. Menunggu pembayaran pemohon.");
        }

        $stageLabel = SuketK3::STAGES[$suket->status_tahap]['label'] ?? "Tahap {$suket->status_tahap}";
        return redirect()->route('suket.index', ['stage' => $suket->status_tahap])
            ->with('success', "Status Suket {$suket->nomor_order} berhasil dialihkan ke: {$stageLabel}");
    }

    /**
     * Aksi Khusus Gerbang QC (sebelum Tahap 4 Penandatanganan)
     * Role: qc, superadmin, admin
     */
    public function qcReview(Request $request, SuketK3 $suket)
    {
        $this->ensureAccess();

        $user = auth()->user();
        $userRole = $user?->role;

        if (!$suket->canRoleProcessQc($userRole)) {
            return redirect()->back()->with('error', 'Hanya role QC atau Superadmin yang berhak melakukan review QC.');
        }

        $request->validate([
            'action' => ['required', 'in:approve,revision,reject'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->action === 'approve') {
            DB::transaction(function () use ($request, $suket, $user) {
                // QC mengisi/menetapkan Nomor Surat dan Tanggal Surat resmi
                if ($request->filled('nomor_surat')) {
                    $suket->nomor_surat = trim((string) $request->input('nomor_surat'));
                } elseif (empty($suket->nomor_surat)) {
                    $suket->nomor_surat = '566/SK-LK/BK3-SBY/' . now()->format('m/Y');
                }

                if ($request->filled('tanggal_surat')) {
                    $suket->tanggal_surat = $request->input('tanggal_surat');
                } elseif (!$suket->tanggal_surat) {
                    $suket->tanggal_surat = now()->toDateString();
                }

                // Kelola berkas draf fisik: jika belum ada dibuat otomatis, jika sudah ada disinkronkan nomor suratnya
                $isCustomUploaded = !empty($suket->draft_file_path) && (
                    str_contains($suket->draft_file_path, 'Draft_Revisi_') ||
                    str_contains($suket->draft_file_path, 'suket_draft_')
                );

                if (empty($suket->draft_file_path) || !Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
                    $this->saveAutoGeneratedDraft($suket);
                } elseif ($isCustomUploaded) {
                    // Dokumen draf revisi hasil upload penguji: auto-replace Nomor & Tanggal Surat in-place tanpa merusak format Word
                    app(\App\Services\SuketDocxService::class)->syncDocxMetadata($suket);
                } else {
                    // Dokumen draf otomatis standar: generate ulang agar nomor & tanggal terpasang rapi
                    $this->saveAutoGeneratedDraft($suket);
                }

                $qcNote = $request->filled('catatan')
                    ? trim((string) $request->input('catatan'))
                    : "Disetujui oleh QC. Nomor surat resmi [{$suket->nomor_surat}] telah ditetapkan dan diteruskan ke Penandatanganan Kepala Balai.";

                $suket->qc_status = 'approved';
                $suket->qc_note = $qcNote;
                $suket->qc_by = $user->id;
                $suket->qc_at = now();
                $suket->status_tahap = 4; // Lanjut ke Tahap 4: Penandatanganan
                $suket->updated_by = $user->id;
                $suket->save();

                $suket->recordHistory(
                    action: 'qc_approved',
                    stageBefore: 3,
                    stageAfter: 4,
                    catatan: $qcNote,
                    nomorSurat: $suket->nomor_surat,
                    userId: $user->id
                );
            });

            return redirect()->route('suket.index', ['stage' => 4])
                ->with('success', "QC Suket {$suket->nomor_order} DISETUJUI dengan Nomor Surat [{$suket->nomor_surat}]. Lanjut ke Tahap 4 (Penandatanganan Kepala Balai).");
        } else {
            $catatanRevisi = $request->filled('catatan')
                ? trim((string) $request->input('catatan'))
                : 'Perlu perbaikan redaksional/klausul draf dokumen oleh Penguji K3.';

            DB::transaction(function () use ($suket, $catatanRevisi, $user) {
                $suket->qc_status = 'revision';
                $suket->qc_note = $catatanRevisi;
                $suket->qc_by = $user->id;
                $suket->qc_at = now();
                $suket->status_tahap = 3; // Kembalikan ke tahap 3: Penyusunan
                $suket->updated_by = $user->id;
                $suket->save();

                // Simpan juga ke komentar internal dokumen suket
                $suket->comments()->create([
                    'user_id' => $user->id,
                    'target' => 'internal',
                    'document_type' => 'suket',
                    'comment' => "[QC Mengembalikan Berkas ke Penyusunan]: {$catatanRevisi}",
                ]);

                $suket->recordHistory(
                    action: 'qc_returned',
                    stageBefore: 3,
                    stageAfter: 3,
                    catatan: $catatanRevisi,
                    nomorSurat: $suket->nomor_surat,
                    userId: $user->id
                );
            });

            return redirect()->route('suket.index', ['stage' => 3])
                ->with('warning', "QC Suket {$suket->nomor_order} meminta REVISI. Dokumen dikembalikan ke Penguji K3 (Tahap 3) untuk diperbaiki.");
        }
    }

    /**
     * Auto Generate Draf Suket K3 Standar Permenaker 05/2018 (Word DOCX Document)
     */
    public function generateDraft(SuketK3 $suket)
    {
        $this->ensureAccess();

        $docxBinary = app(\App\Services\SuketDocxService::class)->generateDocx($suket);
        $filename = 'Suket_Permenaker_05_2018_' . preg_replace('/[^A-Za-z0-9\-]+/', '_', $suket->nomor_order) . '.docx';
        $path = 'suket_docs/' . $suket->id . '/' . $filename;

        // Bersihkan draf lama jika ada
        if ($suket->draft_file_path && $suket->draft_file_path !== $path && Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
            Storage::disk(self::PRIVATE_DISK)->delete($suket->draft_file_path);
        }

        Storage::disk(self::PRIVATE_DISK)->put($path, $docxBinary);

        $suket->draft_file_path = $path;
        $suket->draft_file_name = $filename;
        $suket->save();

        return response($docxBinary, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Upload berkas draf, berkas ttd, foto, atau denah
     */
    public function uploadDocument(Request $request, SuketK3 $suket)
    {
        $this->ensureAccess();

        $request->validate([
            'document_file' => ['required', 'file', 'max:20480'],
            'type' => ['required', 'in:draft,signed,final,lhu,foto,denah'],
        ]);

        $file = $request->file('document_file');

        if ($request->type === 'draft') {
            \App\Support\SafeDocumentUpload::validateOrFail($file, 'document_file');
        } elseif (in_array($request->type, ['signed', 'final', 'foto', 'denah'], true)) {
            \App\Support\SafeDocumentUpload::validatePengujianOrFail($file, 'document_file');
        } elseif ($request->type === 'lhu') {
            \App\Support\SafeDocumentUpload::validatePdfOrFail($file, 'document_file');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'suket_' . $request->type . '_' . $suket->id . '_' . time() . '.' . $ext;
        $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);

        if ($request->type === 'draft') {
            if ($suket->draft_file_path && $suket->draft_file_path !== $path && Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
                Storage::disk(self::PRIVATE_DISK)->delete($suket->draft_file_path);
            }
            $suket->draft_file_path = $path;
            $suket->draft_file_name = $file->getClientOriginalName();
            if ($ext === 'docx' && !empty($suket->nomor_surat)) {
                app(\App\Services\SuketDocxService::class)->syncDocxMetadata($suket);
            }
        } elseif ($request->type === 'signed' || $request->type === 'final') {
            if ($suket->signed_file_path && $suket->signed_file_path !== $path && Storage::disk(self::PRIVATE_DISK)->exists($suket->signed_file_path)) {
                Storage::disk(self::PRIVATE_DISK)->delete($suket->signed_file_path);
            }
            $suket->signed_file_path = $path;
            $suket->signed_file_name = $file->getClientOriginalName();
            if (!$suket->signed_at) {
                $suket->signed_at = now();
                $suket->signed_by = auth()->id();
            }
        } elseif ($request->type === 'lhu') {
            $suket->lhu_file_path = $path;
            $suket->lhu_file_name = $file->getClientOriginalName();
            $suket->lhu_source = 'manual';
        } elseif ($request->type === 'foto') {
            $suket->foto_pengujian_path = $path;
            $suket->foto_pengujian_name = $file->getClientOriginalName();
        } elseif ($request->type === 'denah') {
            $suket->denah_lokasi_path = $path;
            $suket->denah_lokasi_name = $file->getClientOriginalName();
        }

        $suket->updated_by = auth()->id();
        $suket->save();

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
    }

    /**
     * Download/Lihat berkas lampiran suket (LHU, Foto, Denah, Draft, Signed)
     */
    public function downloadDocument(SuketK3 $suket, string $type)
    {
        $this->ensureAccess();

        $user = auth()->user();
        if ($user && $user->role === 'user') {
            if ($suket->user_id !== $user->id && $suket->permohonan?->user_id !== $user->id) {
                abort(403, 'Akses ditolak ke dokumen permohonan ini.');
            }
            if (in_array($type, ['signed', 'final', 'draft'], true) && empty($suket->sent_to_customer_at)) {
                abort(403, 'Dokumen Surat Keterangan K3 belum resmi diserahkan ke akun Anda. Mohon menunggu proses verifikasi dan penyerahan oleh Balai K3.');
            }
        }

        $path = match ($type) {
            'draft' => $suket->draft_file_path,
            'signed', 'final' => $suket->signed_file_path,
            'lhu' => $suket->effective_lhu_path ?: $suket->lhu_file_path,
            'foto' => $suket->foto_pengujian_path,
            'denah' => $suket->denah_lokasi_path,
            'tagihan' => $suket->surat_tagihan_file_path,
            'billing' => $suket->billing_file_path,
            'guide', 'panduan', 'billing_guide' => $suket->effectiveBillingGuidePath(),
            'proof', 'payment_proof' => $suket->billing_proof_path,
            'kuitansi' => $suket->kuitansi_file_path,
            default => null,
        };

        // Jika unduh draft dan berkas fisik belum ada, generate .docx standar otomatis
        if ($type === 'draft' && (!$path || !Storage::disk(self::PRIVATE_DISK)->exists($path))) {
            $this->saveAutoGeneratedDraft($suket);
            $path = $suket->draft_file_path;
        } elseif ($type === 'draft' && $path && !empty($suket->nomor_surat) && str_ends_with(strtolower($path), '.docx')) {
            app(\App\Services\SuketDocxService::class)->syncDocxMetadata($suket);
        }

        // Khusus Tagihan & Kuitansi: Auto-generate file jika belum tersedia secara fisik
        if ($type === 'tagihan' && (!$path || !Storage::disk(self::PRIVATE_DISK)->exists($path))) {
            $path = $this->saveAutoGeneratedTagihan($suket);
        } elseif ($type === 'kuitansi' && (!$path || !Storage::disk(self::PRIVATE_DISK)->exists($path))) {
            $path = $this->saveAutoGeneratedKuitansi($suket);
        }

        $name = match ($type) {
            'draft' => $suket->draft_file_name,
            'signed', 'final' => $suket->signed_file_name,
            'lhu' => $suket->lhu_file_name ?: basename($path ?? 'LHU.pdf'),
            'foto' => $suket->foto_pengujian_name,
            'denah' => $suket->denah_lokasi_name,
            'tagihan' => $suket->surat_tagihan_file_name ?: ('Surat_Tagihan_' . $suket->nomor_order . '.pdf'),
            'billing' => $suket->billing_file_name ?: ('Kode_Billing_' . $suket->nomor_order . '.pdf'),
            'guide', 'panduan', 'billing_guide' => $suket->effectiveBillingGuideName() ?: ('Panduan_Pembayaran_' . $suket->nomor_order . '.pdf'),
            'proof', 'payment_proof' => $suket->billing_proof_name ?: ('Bukti_Pembayaran_' . $suket->nomor_order . '.pdf'),
            'kuitansi' => $suket->kuitansi_file_name ?: ('Kuitansi_' . $suket->nomor_order . '.pdf'),
            default => null,
        };

        $disk = self::PRIVATE_DISK;
        if ($path && !Storage::disk($disk)->exists($path) && Storage::disk(self::LEGACY_DISK)->exists($path)) {
            $disk = self::LEGACY_DISK;
        }

        if (!$path || !Storage::disk($disk)->exists($path)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        return Storage::disk($disk)->download($path, $name ?: basename($path));
    }

    /**
     * Helper internal: Auto generate draft jika belum ada
     */
    private function saveAutoGeneratedDraft(SuketK3 $suket): void
    {
        $docxBinary = app(\App\Services\SuketDocxService::class)->generateDocx($suket);
        $filename = 'Draft_Suket_' . preg_replace('/[^A-Za-z0-9\-]+/', '_', $suket->nomor_order) . '.docx';
        $path = 'suket_docs/' . $suket->id . '/' . $filename;

        // Bersihkan draf lama jika ada
        if ($suket->draft_file_path && $suket->draft_file_path !== $path && Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
            Storage::disk(self::PRIVATE_DISK)->delete($suket->draft_file_path);
        }

        Storage::disk(self::PRIVATE_DISK)->put($path, $docxBinary);

        $suket->draft_file_path = $path;
        $suket->draft_file_name = $filename;
        $suket->save();
    }

    /**
     * Helper: Generate instance DomPDF untuk Surat Tagihan Suket K3
     */
    public function generateSuratTagihanPdf(SuketK3 $suket)
    {
        $nominalTagihan = (float) ($suket->surat_tagihan_nominal ?: ($suket->permohonan?->total_biaya ?: 2500000));
        $terbilang = TerbilangHelper::make($nominalTagihan);
        $nomorTagihan = 'TAG/BK3-SBY/' . ($suket->surat_tagihan_sent_at ? \Carbon\Carbon::parse($suket->surat_tagihan_sent_at)->format('Ymd') : now()->format('Ymd')) . '/' . $suket->id;
        $tanggalTagihan = \Carbon\Carbon::parse($suket->surat_tagihan_sent_at ?? now())->translatedFormat('d F Y');

        $bendahara = User::whereIn('role', ['admin', 'superadmin', 'keuangan'])->first();
        $bendaharaNama = $bendahara?->name ?? 'Bendahara Penerimaan';
        $bendaharaNip = $bendahara?->nip ?? '19850315 201012 1 002';

        $logoAsset = $this->resolveWordHeaderLogoAsset();
        $logoBase64 = $logoAsset ? base64_encode($logoAsset['binary']) : null;

        return Pdf::loadView('admin.pdf.surat_tagihan_suket', [
            'suket' => $suket,
            'nominalTagihan' => $nominalTagihan,
            'terbilang' => $terbilang,
            'nomorTagihan' => $nomorTagihan,
            'tanggalTagihan' => $tanggalTagihan,
            'bendaharaNama' => $bendaharaNama,
            'bendaharaNip' => $bendaharaNip,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Helper: Generate instance DomPDF untuk Kuitansi Pembayaran Suket K3
     */
    public function generateKuitansiPdf(SuketK3 $suket)
    {
        $nominalKuitansi = (float) ($suket->surat_tagihan_nominal ?: ($suket->permohonan?->total_biaya ?: 2500000));
        $terbilang = TerbilangHelper::make($nominalKuitansi);
        $kuitansiNumber = $suket->kuitansi_nomor ?: ('KWT/BK3-SBY/' . ($suket->kuitansi_generated_at ? \Carbon\Carbon::parse($suket->kuitansi_generated_at)->format('Ymd') : now()->format('Ymd')) . '/' . $suket->id);
        $tanggalKuitansi = \Carbon\Carbon::parse($suket->billing_verified_at ?? $suket->kuitansi_generated_at ?? now())->translatedFormat('d F Y');

        $bendahara = User::whereIn('role', ['admin', 'superadmin', 'keuangan'])->first();
        $bendaharaNama = $bendahara?->name ?? 'Bendahara Penerimaan';
        $bendaharaNip = $bendahara?->nip ?? '19850315 201012 1 002';

        $logoAsset = $this->resolveWordHeaderLogoAsset();
        $logoBase64 = $logoAsset ? base64_encode($logoAsset['binary']) : null;

        return Pdf::loadView('admin.pdf.kuitansi_suket', [
            'suket' => $suket,
            'nominalKuitansi' => $nominalKuitansi,
            'terbilang' => $terbilang,
            'kuitansiNumber' => $kuitansiNumber,
            'tanggalKuitansi' => $tanggalKuitansi,
            'bendaharaNama' => $bendaharaNama,
            'bendaharaNip' => $bendaharaNip,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Helper internal: Simpan berkas PDF Surat Tagihan otomatis ke storage
     */
    public function saveAutoGeneratedTagihan(SuketK3 $suket): string
    {
        $pdf = $this->generateSuratTagihanPdf($suket);
        $filename = 'Surat_Tagihan_' . preg_replace('/[^A-Za-z0-9\-]+/', '_', $suket->nomor_order) . '.pdf';
        $path = 'suket_docs/' . $suket->id . '/' . $filename;

        // Hapus file tagihan lama jika ada
        if ($suket->surat_tagihan_file_path && $suket->surat_tagihan_file_path !== $path && Storage::disk(self::PRIVATE_DISK)->exists($suket->surat_tagihan_file_path)) {
            Storage::disk(self::PRIVATE_DISK)->delete($suket->surat_tagihan_file_path);
        }

        Storage::disk(self::PRIVATE_DISK)->put($path, $pdf->output());

        $suket->surat_tagihan_file_path = $path;
        $suket->surat_tagihan_file_name = $filename;
        $suket->save();

        return $path;
    }

    /**
     * Helper internal: Simpan berkas PDF Kuitansi otomatis ke storage
     */
    public function saveAutoGeneratedKuitansi(SuketK3 $suket): string
    {
        $pdf = $this->generateKuitansiPdf($suket);
        $filename = 'Kuitansi_' . preg_replace('/[^A-Za-z0-9\-]+/', '_', $suket->nomor_order) . '.pdf';
        $path = 'suket_docs/' . $suket->id . '/' . $filename;

        // Hapus file kuitansi lama jika ada
        if ($suket->kuitansi_file_path && $suket->kuitansi_file_path !== $path && Storage::disk(self::PRIVATE_DISK)->exists($suket->kuitansi_file_path)) {
            Storage::disk(self::PRIVATE_DISK)->delete($suket->kuitansi_file_path);
        }

        Storage::disk(self::PRIVATE_DISK)->put($path, $pdf->output());

        $suket->kuitansi_file_path = $path;
        $suket->kuitansi_file_name = $filename;
        $suket->save();

        return $path;
    }

    /**
     * Helper logo asset
     */
    private function resolveWordHeaderLogoAsset(): ?array
    {
        $candidates = [
            'images/Logo Kemnaker.png',
            'images/Logo.png',
        ];

        foreach ($candidates as $relativePath) {
            $fullPath = public_path($relativePath);
            if (!is_file($fullPath) || !is_readable($fullPath)) {
                continue;
            }

            $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                default => 'application/octet-stream',
            };

            $binary = @file_get_contents($fullPath);
            if ($binary === false) {
                continue;
            }

            return [
                'mime' => $mime,
                'binary' => $binary,
                'filename' => 'word-header-logo.' . ($ext !== '' ? $ext : 'png'),
            ];
        }

        return null;
    }

    /**
     * Proteksi Hak Akses
     */
    private function ensureAccess(): void
    {
        $role = auth()->user()?->role;
        $allowed = ['admin', 'superadmin', 'mp', 'pcu', 'kepala_balai', 'penguji_k3', 'qc', 'bendahara', 'user'];

        if (!in_array($role, $allowed, true)) {
            abort(403, 'Akses ditolak. Silakan login dengan akun yang memiliki hak akses.');
        }
    }

    /* -------------------------------------------------------------
     * Legacy compatibility methods for existing routes / features
     * -----------------------------------------------------------*/
    public function show(Permohonan $permohonan)
    {
        $this->ensureAccess();
        $path = $permohonan->draftLhu?->suket_file_path;
        if (!$path || !Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            abort(404);
        }
        return response()->file(Storage::disk(self::PRIVATE_DISK)->path($path));
    }

    public function upload(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();
        return response()->json(['message' => 'Legacy upload ok']);
    }

    public function submitToPenyerahan(Permohonan $permohonan)
    {
        $this->ensureAccess();
        return response()->json(['message' => 'Legacy submit ok']);
    }

    public function updateAvailability(Request $request)
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);
        $enabled = $request->boolean('enabled');
        \App\Models\AppSetting::query()->updateOrCreate(
            ['key' => 'suket_penerbitan_enabled'],
            ['value' => $enabled ? '1' : '0']
        );
        return response()->json(['message' => 'Availability updated', 'enabled' => $enabled]);
    }
}
