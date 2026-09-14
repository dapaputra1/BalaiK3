<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSuketSetting;
use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\SuketK3;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

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

        // Ambil riwayat permohonan Suket milik user yang sedang login
        $suketQuery = SuketK3::with(['permohonan.company', 'qcUser'])
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
            'catatan' => $request->input('catatan', 'Permohonan diajukan oleh pemohon via Portal Web Balai K3.'),
            'qc_status' => 'pending',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('user.suket.index')->with('success', "Permohonan Suket K3 untuk Order {$suket->nomor_order} berhasil diajukan dan sedang diteruskan ke Tim Penguji K3 (Tahap 2: Evaluasi Dokumen).");
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
        $roleAllowedStages = match ($currentRole) {
            'pcu', 'penguji_k3' => [2, 3],
            'qc' => ['qc'],
            'mp', 'kepala_balai' => [4],
            'admin' => [2, 4, 5, 6, 'all'],
            default => [2, 3, 'qc', 4, 5, 6, 'all'], // superadmin
        };

        $requestedStage = $request->query('stage');
        if ($requestedStage && in_array($requestedStage, array_map('strval', $roleAllowedStages), true)) {
            $activeStage = $requestedStage;
        } else {
            $activeStage = (string) reset($roleAllowedStages);
        }

        $search = $request->query('search');

        // Query Suket K3 untuk Internal
        $suketQuery = SuketK3::with(['permohonan.company', 'user', 'creator', 'signer', 'publisher', 'qcUser'])
            ->latest('updated_at');

        // Filter berdasarkan Stage aktif
        if ($activeStage === '2') {
            $suketQuery->whereIn('status_tahap', [1, 2]);
        } elseif ($activeStage === '3') {
            $suketQuery->where('status_tahap', 3)->where(function ($q) {
                $q->whereNull('qc_status')->orWhere('qc_status', '!=', 'approved');
            });
        } elseif ($activeStage === 'qc') {
            $suketQuery->where('status_tahap', 3)->whereNotNull('draft_file_path');
        } elseif ($activeStage === '4') {
            $suketQuery->where('status_tahap', 4);
        } elseif ($activeStage === '5') {
            $suketQuery->where('status_tahap', 5);
        } elseif ($activeStage === '6') {
            $suketQuery->where('status_tahap', 6);
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
                $q->whereNull('qc_status')->orWhere('qc_status', '!=', 'approved');
            })->count(),
            'qc' => SuketK3::where('status_tahap', 3)->whereNotNull('draft_file_path')->count(),
            4 => SuketK3::where('status_tahap', 4)->count(),
            5 => SuketK3::where('status_tahap', 5)->count(),
            6 => SuketK3::where('status_tahap', 6)->count(),
            'all' => SuketK3::count(),
        ];

        $totalActive = SuketK3::where('status_tahap', '<', 6)->count();
        $totalDone = SuketK3::where('status_tahap', 6)->count();

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
            'catatan' => $request->input('catatan', 'Pendaftaran suket nomor order ' . ($permohonan?->kode ?? $orderCode)),
            'qc_status' => 'pending',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('suket.index')->with('success', "Pengajuan Suket untuk Nomor Order {$suket->nomor_order} berhasil didaftarkan.");
    }

    /**
     * Preview Dokumen Secara Inline (Tanpa Harus Download Terlebih Dahulu)
     */
    public function previewDocument(Request $request, SuketK3 $suket, string $type)
    {
        $this->ensureAccess();

        $user = auth()->user();
        if ($user && $user->role === 'user') {
            if ($suket->user_id !== $user->id && $suket->permohonan?->user_id !== $user->id) {
                abort(403, 'Akses ditolak ke dokumen permohonan ini.');
            }
        }

        if ($type === 'draft') {
            if (!$suket->draft_file_path || !Storage::disk(self::PRIVATE_DISK)->exists($suket->draft_file_path)) {
                $this->saveAutoGeneratedDraft($suket);
            }

            if (str_ends_with(strtolower((string) $suket->draft_file_path), '.pdf')) {
                $fullPath = Storage::disk(self::PRIVATE_DISK)->path($suket->draft_file_path);
                return response()->file($fullPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="' . basename($suket->draft_file_path) . '"',
                ]);
            }

            // Jika Word .doc, render view HTML langsung ke browser
            $logoAsset = $this->resolveWordHeaderLogoAsset();
            $logoAssetBase64 = $logoAsset ? base64_encode($logoAsset['binary']) : null;
            $kepalaBalai = User::whereIn('role', ['kepala_balai', 'mp'])->first();

            return response()->view('admin.word.suket_k3_permenaker', [
                'suket' => $suket,
                'logoAssetBase64' => $logoAssetBase64,
                'kepalaBalaiNama' => $kepalaBalai?->name ?? 'Dr. H. Agus Triyono, S.T., M.Kes.',
                'kepalaBalaiNip' => $kepalaBalai?->nip ?? '19750812 200212 1 001',
                'isPreviewMode' => true,
            ]);
        }

        $path = match ($type) {
            'signed', 'final' => $suket->signed_file_path ?: $suket->draft_file_path,
            'lhu' => $suket->lhu_file_path,
            'foto' => $suket->foto_pengujian_path,
            'denah' => $suket->denah_lokasi_path,
            default => null,
        };

        if (!$path || !Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        $fullPath = Storage::disk(self::PRIVATE_DISK)->path($path);
        $mime = Storage::disk(self::PRIVATE_DISK)->mimeType($path) ?: 'application/octet-stream';
        $filename = basename($path);

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Transisi Status Antar 6 Tahap (State Machine dengan RBAC & File Hooks)
     */
    public function advanceStage(Request $request, SuketK3 $suket)
    {
        $this->ensureAccess();

        $request->validate([
            'action' => ['required', 'in:next,revision,upload_draft'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['nullable', 'date'],
            'signed_document' => ['nullable', 'file', 'extensions:pdf,doc,docx,jpg,jpeg,png', 'max:20480'],
            'revised_draft' => ['nullable', 'file', 'extensions:doc,docx,pdf', 'max:20480'],
        ], [
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

        // Validasi khusus saat mau lanjut dari Tahap 3 ke Tahap 4
        if ($request->action === 'next' && $currentStage === 3) {
            if ($suket->qc_status !== 'approved' && $userRole !== 'superadmin') {
                return redirect()->back()->with('error', 'Dokumen draf Suket wajib melalui review dan disetujui (Approved) oleh Tim QC terlebih dahulu sebelum diajukan ke Penandatanganan Kepala Balai.');
            }
        }

        // Validasi khusus saat mau lanjut dari Tahap 4 ke Tahap 5
        if ($request->action === 'next' && $currentStage === 4) {
            if (!$request->hasFile('signed_document') && empty($suket->signed_file_path)) {
                return redirect()->back()->with('error', 'Silakan pilih dan unggah berkas dokumen Surat Keterangan K3 yang telah ditandatangani oleh Kepala Balai terlebih dahulu.');
            }
        }

        // Validasi khusus saat mau terbit di Tahap 5
        if ($request->action === 'next' && $currentStage === 5) {
            $nomorSurat = trim((string) $request->input('nomor_surat'));
            if ($nomorSurat === '' && empty($suket->nomor_surat)) {
                return redirect()->back()->with('error', 'Nomor Surat Keterangan resmi wajib diisi pada Tahap Penerbitan.');
            }
        }

        DB::transaction(function () use ($request, $suket, $currentStage, $user) {
            if ($request->action === 'next' && $currentStage < 6) {
                $nextStage = $currentStage + 1;
                $suket->status_tahap = $nextStage;

                // Hook spesifik per tahapan
                if ($currentStage === 1) {
                    if ($request->filled('catatan')) {
                        $suket->catatan = $request->input('catatan');
                    }
                } elseif ($currentStage === 2) {
                    $suket->catatan_evaluasi = $request->input('catatan', 'Dokumen evaluasi disetujui oleh Penguji K3.');
                } elseif ($currentStage === 3) {
                    // Cek jika petugas mengunggah dokumen revisi manual
                    if ($request->hasFile('revised_draft')) {
                        $file = $request->file('revised_draft');
                        $ext = strtolower($file->getClientOriginalExtension());
                        $path = $file->storeAs('suket_docs/' . $suket->id, 'Draft_Revisi_' . time() . '.' . $ext, self::PRIVATE_DISK);
                        $suket->draft_file_path = $path;
                        $suket->draft_file_name = $file->getClientOriginalName();
                    } elseif (!$suket->draft_file_path) {
                        $this->saveAutoGeneratedDraft($suket);
                    }
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
                } elseif ($currentStage === 5) {
                    if ($request->filled('nomor_surat')) {
                        $suket->nomor_surat = $request->input('nomor_surat');
                    }
                    if ($request->filled('tanggal_surat')) {
                        $suket->tanggal_surat = $request->input('tanggal_surat');
                    } else {
                        $suket->tanggal_surat = now()->toDateString();
                    }
                    $suket->published_at = now();
                    $suket->published_by = $user->id;

                    // Auto-replace / perbarui nomor surat di berkas Word yang di-generate
                    $this->saveAutoGeneratedDraft($suket);
                }
            } elseif ($request->action === 'upload_draft') {
                if ($request->hasFile('revised_draft')) {
                    $file = $request->file('revised_draft');
                    $ext = strtolower($file->getClientOriginalExtension());
                    $path = $file->storeAs('suket_docs/' . $suket->id, 'Draft_Revisi_' . time() . '.' . $ext, self::PRIVATE_DISK);
                    $suket->draft_file_path = $path;
                    $suket->draft_file_name = $file->getClientOriginalName();
                }
            } elseif ($request->action === 'revision' && $currentStage > 1) {
                $suket->status_tahap = $currentStage - 1;
                if ($currentStage === 4) {
                    $suket->qc_status = 'pending';
                }
            }

            if ($currentStage === 6 && $request->action === 'next') {
                // Konfirmasi pengiriman ke pelanggan (langsung via web Balai K3 tanpa nomor resi)
                $suket->sent_to_customer_at = now();
                $suket->sent_to_customer_by = $user->id;
                $suket->metode_pengiriman = 'Portal Digital Web Balai K3';
            }

            if ($request->filled('catatan')) {
                $suket->catatan = $request->input('catatan');
            }

            $suket->updated_by = $user->id;
            $suket->save();
        });

        $stageLabel = SuketK3::STAGES[$suket->status_tahap]['label'] ?? "Tahap {$suket->status_tahap}";
        return redirect()->route('suket.index', ['stage' => $suket->status_tahap])
            ->with('success', "Status Suket {$suket->nomor_order} berhasil dialihkan ke: {$stageLabel}");
    }

    /**
     * Aksi Khusus Gerbang QC (sebelum Tahap 4 Penandatanganan)
     * Role: qc, superadmin
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
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($request->action === 'approve') {
            $suket->qc_status = 'approved';
            $suket->qc_note = $request->input('catatan', 'Disetujui oleh QC. Dokumen draf suket sesuai dengan standar Permenaker No. 5/2018.');
            $suket->qc_by = $user->id;
            $suket->qc_at = now();
            // Lanjut ke tahap 4: Penandatanganan
            $suket->status_tahap = 4;
            $suket->save();

            return redirect()->route('suket.index', ['stage' => 4])->with('success', "QC Suket {$suket->nomor_order} DISETUJUI. Lanjut ke Tahap 4 (Penandatanganan Surat Keterangan).");
        } else {
            $suket->qc_status = 'revision';
            $suket->qc_note = $request->input('catatan', 'Perlu perbaikan draf dokumen.');
            $suket->qc_by = $user->id;
            $suket->qc_at = now();
            // Kembalikan ke tahap 3: Penyusunan
            $suket->status_tahap = 3;
            $suket->save();

            return redirect()->route('suket.index', ['stage' => 3])->with('warning', "QC Suket {$suket->nomor_order} meminta REVISI. Dokumen dikembalikan ke Penguji K3 (Tahap 3).");
        }
    }

    /**
     * Auto Generate Draf Suket K3 Standar Permenaker 05/2018 (Word Document)
     */
    public function generateDraft(SuketK3 $suket)
    {
        $this->ensureAccess();

        $logoAsset = $this->resolveWordHeaderLogoAsset();
        $logoAssetBase64 = $logoAsset ? base64_encode($logoAsset['binary']) : null;

        // Ambil data pejabat Kepala Balai
        $kepalaBalai = User::whereIn('role', ['kepala_balai', 'mp'])->first();

        $html = view('admin.word.suket_k3_permenaker', [
            'suket' => $suket,
            'logoAssetBase64' => $logoAssetBase64,
            'kepalaBalaiNama' => $kepalaBalai?->name ?? 'Dr. H. Agus Triyono, S.T., M.Kes.',
            'kepalaBalaiNip' => $kepalaBalai?->nip ?? '19750812 200212 1 001',
        ])->render();

        // Simpan file draf ke private disk agar otomatis terlampir
        $filename = 'Suket_Permenaker_05_2018_' . preg_replace('/[^A-Za-z0-9\-]+/', '_', $suket->nomor_order) . '.doc';
        $path = 'suket_docs/' . $suket->id . '/' . $filename;
        Storage::disk(self::PRIVATE_DISK)->put($path, "\xEF\xBB\xBF" . $html);

        $suket->draft_file_path = $path;
        $suket->draft_file_name = $filename;
        $suket->save();

        return response("\xEF\xBB\xBF" . $html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
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
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'suket_' . $request->type . '_' . $suket->id . '_' . time() . '.' . $ext;
        $path = $file->storeAs('suket_docs/' . $suket->id, $filename, self::PRIVATE_DISK);

        if ($request->type === 'draft') {
            $suket->draft_file_path = $path;
            $suket->draft_file_name = $file->getClientOriginalName();
        } elseif ($request->type === 'signed' || $request->type === 'final') {
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

        $path = match ($type) {
            'draft' => $suket->draft_file_path,
            'signed', 'final' => $suket->signed_file_path,
            'lhu' => $suket->lhu_file_path,
            'foto' => $suket->foto_pengujian_path,
            'denah' => $suket->denah_lokasi_path,
            default => null,
        };

        $name = match ($type) {
            'draft' => $suket->draft_file_name,
            'signed', 'final' => $suket->signed_file_name,
            'lhu' => $suket->lhu_file_name,
            'foto' => $suket->foto_pengujian_name,
            'denah' => $suket->denah_lokasi_name,
            default => null,
        };

        if (!$path || !Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        return Storage::disk(self::PRIVATE_DISK)->download($path, $name ?: basename($path));
    }

    /**
     * Helper internal: Auto generate draft jika belum ada
     */
    private function saveAutoGeneratedDraft(SuketK3 $suket): void
    {
        $logoAsset = $this->resolveWordHeaderLogoAsset();
        $logoAssetBase64 = $logoAsset ? base64_encode($logoAsset['binary']) : null;
        $kepalaBalai = User::whereIn('role', ['kepala_balai', 'mp'])->first();

        $html = view('admin.word.suket_k3_permenaker', [
            'suket' => $suket,
            'logoAssetBase64' => $logoAssetBase64,
            'kepalaBalaiNama' => $kepalaBalai?->name ?? 'Dr. H. Agus Triyono, S.T., M.Kes.',
            'kepalaBalaiNip' => $kepalaBalai?->nip ?? '19750812 200212 1 001',
        ])->render();

        $filename = 'Draft_Suket_' . preg_replace('/[^A-Za-z0-9\-]+/', '_', $suket->nomor_order) . '.doc';
        $path = 'suket_docs/' . $suket->id . '/' . $filename;
        Storage::disk(self::PRIVATE_DISK)->put($path, "\xEF\xBB\xBF" . $html);

        $suket->draft_file_path = $path;
        $suket->draft_file_name = $filename;
        $suket->save();
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
        $allowed = ['admin', 'superadmin', 'mp', 'pcu', 'kepala_balai', 'penguji_k3', 'qc', 'user'];

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
        return response()->json(['message' => 'Availability updated', 'enabled' => true]);
    }
}
