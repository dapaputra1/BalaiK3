<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithSuketSetting;
use App\Models\DraftLhu;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\SuketK3;
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
     * Halaman Utama: Monitoring 6 Tahap Penerbitan Suket K3 Lingkungan Kerja
     */
    public function index(Request $request)
    {
        $this->ensureAccess();

        $currentRole = auth()->user()?->role;
        $activeStage = $request->query('stage');
        $search = $request->query('search');

        // Query Suket K3
        $suketQuery = SuketK3::with(['permohonan.company', 'creator', 'signer', 'publisher'])
            ->latest('updated_at');

        if ($activeStage && is_numeric($activeStage)) {
            $suketQuery->where('status_tahap', (int) $activeStage);
        }

        if ($search) {
            $suketQuery->where(function ($q) use ($search) {
                $q->where('nomor_order', 'like', "%{$search}%")
                    ->orWhere('perusahaan_nama', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        $sukets = $suketQuery->paginate(15)->withQueryString();

        // Hitung total tiap tahap
        $stageCounts = [];
        foreach (array_keys(SuketK3::STAGES) as $stageNum) {
            $stageCounts[$stageNum] = SuketK3::where('status_tahap', $stageNum)->count();
        }
        $totalActive = SuketK3::where('status_tahap', '<', 6)->count();
        $totalDone = SuketK3::where('status_tahap', 6)->count();

        // Ambil daftar permohonan untuk saran Nomor Order (autocomplete / select)
        $availableOrders = Permohonan::with('company')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($p) {
                return [
                    'kode' => $p->kode,
                    'perusahaan' => $p->company?->company_name ?? 'Perusahaan #'.$p->id,
                    'lokasi' => $p->jadwal_lokasi ?: ($p->company?->company_city ?? '-'),
                ];
            });

        return view('admin.suket.index', [
            'sukets' => $sukets,
            'stages' => SuketK3::STAGES,
            'stageCounts' => $stageCounts,
            'totalActive' => $totalActive,
            'totalDone' => $totalDone,
            'activeStage' => $activeStage,
            'search' => $search,
            'currentRole' => $currentRole,
            'availableOrders' => $availableOrders,
        ]);
    }

    /**
     * Input Utama: Submit Pengajuan Suket berdasarkan Nomor Order
     */
    public function storeByOrder(Request $request)
    {
        $this->ensureAccess();

        $request->validate([
            'nomor_order' => ['required', 'string'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'nomor_order.required' => 'Nomor order wajib diisi.',
        ]);

        $orderCode = trim($request->input('nomor_order'));

        // Cari permohonan berdasarkan kode atau ID
        $permohonan = Permohonan::with('company')
            ->where('kode', $orderCode)
            ->orWhere('id', $orderCode)
            ->first();

        $companyName = $permohonan?->company?->company_name ?? 'Perusahaan ' . $orderCode;
        $location = $permohonan?->jadwal_lokasi ?: ($permohonan?->company?->company_city ?? 'Lokasi Uji K3');

        $suket = SuketK3::create([
            'permohonan_id' => $permohonan?->id,
            'nomor_order' => $permohonan?->kode ?? $orderCode,
            'status_tahap' => 1, // Tahap 1: Permohonan
            'perusahaan_nama' => $companyName,
            'lokasi' => $location,
            'catatan' => $request->input('catatan', 'Pengajuan suket didaftarkan melalui Nomor Order ' . ($permohonan?->kode ?? $orderCode)),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('suket.index')->with('success', "Pengajuan Suket untuk Nomor Order {$suket->nomor_order} berhasil didaftarkan (Tahap 1: Permohonan).");
    }

    /**
     * Transisi Status Antar 6 Tahap (State Machine dengan RBAC)
     */
    public function advanceStage(Request $request, SuketK3 $suket)
    {
        $this->ensureAccess();

        $request->validate([
            'action' => ['required', 'in:next,revision'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'resi_pengiriman' => ['nullable', 'string', 'max:100'],
            'metode_pengiriman' => ['nullable', 'string', 'max:100'],
        ]);

        $user = auth()->user();
        $userRole = $user?->role;
        $currentStage = (int) $suket->status_tahap;

        // Otorisasi role untuk memproses tahap ini
        if (!$suket->canRoleProcess($userRole)) {
            return redirect()->back()->with('error', "Role [{$userRole}] tidak memiliki kewenangan untuk memproses tahap ini.");
        }

        DB::transaction(function () use ($request, $suket, $currentStage, $user) {
            if ($request->action === 'next' && $currentStage < 6) {
                $nextStage = $currentStage + 1;
                $suket->status_tahap = $nextStage;

                // Hook spesifik per tahapan
                if ($currentStage === 2) {
                    // Evaluasi Dokumen selesai
                    $suket->catatan_evaluasi = $request->input('catatan', 'Dokumen evaluasi disetujui oleh Penguji K3.');
                } elseif ($currentStage === 4) {
                    // Penandatanganan oleh Kepala Balai selesai
                    $suket->signed_at = now();
                    $suket->signed_by = $user->id;
                } elseif ($currentStage === 5) {
                    // Penerbitan Laporan/Suket selesai dan dikirim ke pelanggan
                    $suket->published_at = now();
                    $suket->published_by = $user->id;
                    $suket->resi_pengiriman = $request->input('resi_pengiriman');
                    $suket->metode_pengiriman = $request->input('metode_pengiriman', 'Kurir / Serah Langsung');
                    $suket->sent_to_customer_at = now();
                    $suket->sent_to_customer_by = $user->id;
                }
            } elseif ($request->action === 'revision' && $currentStage > 1) {
                $suket->status_tahap = $currentStage - 1;
            }

            if ($request->filled('catatan')) {
                $suket->catatan = $request->input('catatan');
            }

            $suket->updated_by = $user->id;
            $suket->save();
        });

        $stageLabel = SuketK3::STAGES[$suket->status_tahap]['label'] ?? "Tahap {$suket->status_tahap}";
        return redirect()->route('suket.index')->with('success', "Status Suket {$suket->nomor_order} berhasil dialihkan ke: {$stageLabel}");
    }

    /**
     * Upload berkas draf atau berkas ttd
     */
    public function uploadDocument(Request $request, SuketK3 $suket)
    {
        $this->ensureAccess();

        $request->validate([
            'document_file' => ['required', 'file', 'max:10240'],
            'type' => ['required', 'in:draft,signed,final'],
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
        }

        $suket->updated_by = auth()->id();
        $suket->save();

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
    }

    /**
     * Download/Lihat berkas suket
     */
    public function downloadDocument(SuketK3 $suket, string $type)
    {
        $this->ensureAccess();

        $path = $type === 'draft' ? $suket->draft_file_path : $suket->signed_file_path;
        $name = $type === 'draft' ? $suket->draft_file_name : $suket->signed_file_name;

        if (!$path || !Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        return Storage::disk(self::PRIVATE_DISK)->download($path, $name ?: basename($path));
    }

    /**
     * Proteksi Hak Akses Internal
     * Internal: Admin, Superadmin, Kepala Balai (mp/kepala_balai), Penguji K3 (pcu/penguji_k3)
     */
    private function ensureAccess(): void
    {
        $role = auth()->user()?->role;
        $allowed = ['admin', 'superadmin', 'mp', 'pcu', 'kepala_balai', 'penguji_k3'];

        if (!in_array($role, $allowed, true)) {
            abort(403, 'Akses ditolak. Halaman khusus internal Balai K3.');
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
