<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaApprovalController extends Controller
{
    public function index()
    {
        $this->ensureAccess();

        if (!Schema::hasColumn('permohonans', 'ma_approved_at')) {
            return view('admin.ma_approval_penjadwalan', [
                'orders' => collect(),
            ])->with('error', 'Kolom approval MA belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $routePrefix = auth()->user()?->role === 'superadmin' ? 'superadmin' : 'ma';

        $orders = Permohonan::with([
            'company',
            'parameters.serviceParameter.category',
            'assignments.user',
        ])
            ->whereNotNull('jadwal_mulai')
            ->whereNotNull('jadwal_selesai')
            ->whereNotNull('penjadwalan_sent_at')
            ->whereNull('ma_approved_at')
            ->whereNull('spt_sent_at')
            ->oldest()
            ->get()
            ->map(function (Permohonan $permohonan) use ($routePrefix) {
                $company = $permohonan->company;
                $params = $permohonan->parameters
                    ->filter(fn ($param) => $param->status === 'approved')
                    ->map(function ($param) {
                        $category = $param->serviceParameter?->category;
                        $categoryLabel = $category?->short_code ?: ($category?->name ?? '-');

                        return [
                            'kategori' => $categoryLabel,
                            'nama' => $param->parameter_name,
                            'qty' => (int) ($param->qty ?? 0),
                        ];
                    })
                    ->values();

                $pcuAssignments = $permohonan->assignments
                    ->where('role', 'pcu')
                    ->map(function ($assignment) {
                        return [
                            'name' => $assignment->user?->name,
                            'is_leader' => (bool) ($assignment->is_leader ?? false),
                        ];
                    })
                    ->filter(fn ($row) => !empty($row['name']))
                    ->values();
                $ketuaPcu = $pcuAssignments->firstWhere('is_leader', true);
                $ketuaPcuNama = $ketuaPcu['name'] ?? ($pcuAssignments->first()['name'] ?? '-');

                return [
                    'permohonan_id' => $permohonan->id,
                    'kode' => $permohonan->kode,
                    'pelanggan' => $company?->company_name ?? '-',
                    'lokasi' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                    'alamat' => $company?->company_address ?? '-',
                    'tanggal_mulai' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? '-',
                    'tanggal_selesai' => optional($permohonan->jadwal_selesai)->format('Y-m-d') ?? '-',
                    'catatan' => $permohonan->jadwal_catatan ?? '',
                    'pengumuman_jadwal' => $permohonan->jadwal_pengumuman ?? '',
                    'parameter' => $params->all(),
                    'pcu' => $pcuAssignments->pluck('name')->all(),
                    'ketua_pcu' => $ketuaPcuNama,
                    'penjadwalan_sent_at' => optional($permohonan->penjadwalan_sent_at)->toDateTimeString(),
                    'penjadwalan_sent_at_iso' => optional($permohonan->penjadwalan_sent_at)->toIso8601String(),
                    'penjadwalan_sent_at_label' => optional($permohonan->penjadwalan_sent_at)->format('d M Y, H:i'),
                    'approve_url' => route($routePrefix . '.approval-ma.approve', $permohonan),
                ];
            });

        return view('admin.ma_approval_penjadwalan', [
            'orders' => $orders,
        ]);
    }

    public function approve(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        $request->validate([
            'confirm_approval' => ['accepted'],
        ], [
            'confirm_approval.accepted' => 'Centang persetujuan terlebih dahulu sebelum mengirim.',
        ]);

        if (!Schema::hasColumn('permohonans', 'ma_approved_at')) {
            return redirect()->back()->with('error', 'Kolom approval MA belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        if (!$permohonan->penjadwalan_sent_at) {
            return redirect()->back()->with('error', 'Penjadwalan belum diteruskan dari penyelia.');
        }

        if ($permohonan->spt_sent_at) {
            return redirect()->back()->with('success', 'SPT sudah diteruskan ke PCU sebelumnya.');
        }

        if ($permohonan->ma_approved_at) {
            return redirect()->back()->with('success', 'Permohonan ini sudah di-ACC oleh MA.');
        }

        DB::transaction(function () use ($permohonan) {
            $permohonan->update([
                'ma_approved_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'ACC MA berhasil disimpan. Permohonan siap diproses admin.');
    }

    public function returnToPenjadwalan(Request $request, Permohonan $permohonan)
    {
        $this->ensureAccess();

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Alasan pengembalian ke penjadwalan wajib diisi.',
            'reason.max' => 'Alasan pengembalian maksimal 2000 karakter.',
        ]);

        if (!$permohonan->penjadwalan_sent_at) {
            return redirect()->back()->with('error', 'Permohonan belum berada pada tahap setelah penjadwalan.');
        }

        $this->sendBackToPenjadwalan($permohonan, trim((string) $data['reason']), 'Approval MA');

        return redirect()->back()->with('success', 'Permohonan berhasil dikembalikan ke penjadwalan.');
    }

    private function ensureAccess(): void
    {
        $role = auth()->user()?->role;

        if (!in_array($role, ['ma', 'superadmin'], true)) {
            abort(403);
        }
    }

    private function sendBackToPenjadwalan(Permohonan $permohonan, string $reason, string $source): void
    {
        $penjadwalanStep = WorkflowStep::where('kode', 'penjadwalan')->first();
        $message = 'Dikembalikan ke penjadwalan dari ' . $source . ': ' . $reason;

        DB::transaction(function () use ($permohonan, $penjadwalanStep, $message) {
            $permohonan->update([
                'jadwal_sent_to_user_at' => null,
                'jadwal_user_approved_at' => null,
                'jadwal_user_approved_by' => null,
                'penjadwalan_sent_at' => null,
                'ma_approved_at' => null,
                'spt_sent_at' => null,
                'status_global' => 'penjadwalan',
                'status_dokumen' => 'penjadwalan',
                'status_lab' => 'penjadwalan',
            ]);

            if ($penjadwalanStep) {
                PermohonanStep::updateOrCreate(
                    ['permohonan_id' => $permohonan->id, 'step_id' => $penjadwalanStep->id],
                    [
                        'status' => 'in_progress',
                        'note' => $message,
                        'started_at' => now(),
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]
                );
            }
        });
    }
}
