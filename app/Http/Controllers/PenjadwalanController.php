<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\PermohonanAssignment;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenjadwalanController extends Controller
{
    public function index()
    {
        $this->cancelNonTestableOnlyPenjadwalanOrders();

        $step = WorkflowStep::where('kode', 'penjadwalan')->first();

        $pcuOptions = User::where('role', 'pcu')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $user = auth()->user();
        $role = $user?->role;
        $hasUserScheduleApprovalColumns = $this->hasUserScheduleApprovalColumns();

        $penjadwalan = collect();
        $waitingUserApproval = collect();
        $approvedUserSchedule = collect();
        $penjadwalanPengumuman = collect();
        if ($step) {
            $baseQuery = Permohonan::with(['company', 'parameters.serviceParameter.category', 'assignments.user'])
                ->with(['steps' => function ($query) use ($step) {
                    $query->where('step_id', $step->id)->select('id', 'permohonan_id', 'step_id', 'started_at', 'updated_at', 'created_at', 'note');
                }])
                ->whereHas('steps', function ($query) use ($step) {
                    $query->where('step_id', $step->id);
                })
                ->whereNull('spt_sent_at')
                ->where(function ($query) {
                    $query->whereNull('status_global')
                        ->orWhere('status_global', 'penjadwalan');
                })
                ->when(in_array($role, ['pcu', 'analis'], true), function ($query) use ($user, $role) {
                    $query->whereHas('assignments', function ($q) use ($user, $role) {
                        $q->where('user_id', $user->id)->where('role', $role);
                    });
                });

            $mapItem = function ($permohonan) use ($step) {
                $company = $permohonan->company;
                $stepRecord = $permohonan->steps->firstWhere('step_id', $step->id);
                $enteredAtIso = optional($stepRecord?->started_at ?? $stepRecord?->updated_at ?? $permohonan->created_at)->toIso8601String();
                $params = $permohonan->parameters
                    ->filter(function ($param) {
                        return $param->status === 'approved';
                    })
                    ->map(function ($param) {
                    $category = $param->serviceParameter?->category;
                    $categoryLabel = $category?->short_code ?: ($category?->name ?? '-');
                    return [
                        'kategori' => $categoryLabel,
                        'nama' => $param->parameter_name,
                        'qty' => $param->qty,
                    ];
                })->values();

                $provinsi = $company?->company_province ?? '-';
                $kota = $company?->company_city ?? '-';
                $alamat = $company?->company_address ?? '-';
                $lokasi = $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-');

                $pcu = $permohonan->assignments
                    ->where('role', 'pcu')
                    ->values()
                    ->map(fn ($a) => [
                        'id' => $a->user_id,
                        'name' => $a->user?->name ?? '-',
                        'is_leader' => (bool) $a->is_leader,
                    ])
                    ->values();
                $ketuaPcu = $pcu->firstWhere('is_leader', true);
                $ketuaPcuId = $ketuaPcu['id'] ?? ($pcu->first()['id'] ?? null);
                $ketuaPcuNama = $ketuaPcu['name'] ?? ($pcu->first()['name'] ?? '-');
                $stepNote = trim((string) ($stepRecord?->note ?? ''));
                $returnMessage = str_starts_with($stepNote, 'Dikembalikan ke penjadwalan dari ')
                    ? $stepNote
                    : null;

                return [
                    'permohonan_id' => $permohonan->id,
                    'kode' => sprintf('JDG-%s-%04d', now()->format('Y'), $permohonan->id),
                    'pelanggan' => $company?->company_name ?? '-',
                    'lokasi' => $lokasi,
                    'status_global' => $permohonan->status_global ?? null,
                    'jadwal_sent_to_user_at' => optional($permohonan->jadwal_sent_to_user_at)->toDateTimeString(),
                    'jadwal_user_approved_at' => optional($permohonan->jadwal_user_approved_at)->toDateTimeString(),
                    'penjadwalan_sent_at' => optional($permohonan->penjadwalan_sent_at)->toDateTimeString(),
                    'provinsi' => $provinsi,
                    'kota' => $kota,
                    'alamat' => $alamat,
                    'tanggal' => optional($permohonan->jadwal_mulai)->format('Y-m-d') ?? '',
                    'tanggal_selesai' => optional($permohonan->jadwal_selesai)->format('Y-m-d') ?? '',
                    'parameter' => $params,
                    'pcu' => $pcu->toArray(),
                    'ketua_pcu_id' => $ketuaPcuId,
                    'ketua_pcu_nama' => $ketuaPcuNama,
                    'tim' => [],
                    'catatan' => $permohonan->jadwal_catatan ?? '',
                    'return_message' => $returnMessage,
                    'pengumuman_jadwal' => $permohonan->jadwal_pengumuman ?? '',
                    'waiting_user_approval' => !empty($permohonan->jadwal_sent_to_user_at) && empty($permohonan->jadwal_user_approved_at) && empty($permohonan->penjadwalan_sent_at),
                    'user_approved' => !empty($permohonan->jadwal_user_approved_at) && empty($permohonan->penjadwalan_sent_at),
                    'created_at' => $enteredAtIso,
                ];
            };

            $penjadwalanQuery = (clone $baseQuery)->whereNull('penjadwalan_sent_at');
            if ($hasUserScheduleApprovalColumns) {
                $penjadwalanQuery->whereNull('jadwal_sent_to_user_at');
            }
            $penjadwalan = $penjadwalanQuery
                ->oldest()
                ->get()
                ->map($mapItem);

            if ($hasUserScheduleApprovalColumns) {
                $waitingUserApproval = (clone $baseQuery)
                    ->whereNotNull('jadwal_sent_to_user_at')
                    ->whereNull('jadwal_user_approved_at')
                    ->whereNull('penjadwalan_sent_at')
                    ->oldest()
                    ->get()
                    ->map($mapItem);

                $approvedUserSchedule = (clone $baseQuery)
                    ->whereNotNull('jadwal_user_approved_at')
                    ->whereNull('penjadwalan_sent_at')
                    ->oldest()
                    ->get()
                    ->map($mapItem);
            } else {
                $waitingUserApproval = collect();
                $approvedUserSchedule = collect();
            }

            $penjadwalanPengumuman = (clone $baseQuery)
                ->oldest()
                ->get()
                ->map($mapItem);
        }

        return view('admin.superadmin_penjadwalan', [
            'penjadwalan' => $penjadwalan,
            'waiting_user_approval' => $waitingUserApproval,
            'approved_user_schedule' => $approvedUserSchedule,
            'penjadwalan_pengumuman' => $penjadwalanPengumuman,
            'pcu_options' => $pcuOptions,
        ]);
    }

    private function cancelNonTestableOnlyPenjadwalanOrders(): void
    {
        $invalidPermohonans = Permohonan::query()
            ->where('status_global', 'penjadwalan')
            ->whereHas('steps', function ($query) {
                $query->whereIn('status', ['pending', 'in_progress'])
                    ->whereHas('step', function ($stepQuery) {
                        $stepQuery->where('kode', 'penjadwalan');
                    });
            })
            ->whereDoesntHave('parameters', function ($query) {
                $query->where('status', 'approved')
                    ->where('qty', '>', 0);
            })
            ->get();

        if ($invalidPermohonans->isEmpty()) {
            return;
        }

        foreach ($invalidPermohonans as $permohonan) {
            DB::transaction(function () use ($permohonan) {
                $cancelNote = 'Semua parameter tidak bisa diuji. Permohonan dibatalkan otomatis.';

                $permohonan->update([
                    'status_global' => 'cancelled',
                    'cancel_reason' => 'Semua parameter tidak bisa diuji',
                    'cancel_note' => $cancelNote,
                    'cancelled_at' => now(),
                    'jadwal_mulai' => null,
                    'jadwal_selesai' => null,
                    'penjadwalan_sent_at' => null,
                    'ma_approved_at' => null,
                ]);

                $permohonan->steps()
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->update([
                        'status' => 'cancelled',
                        'note' => $cancelNote,
                        'updated_by' => auth()->id(),
                        'finished_at' => now(),
                    ]);
            });
        }
    }

    public function assign(Request $request, Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'penyelia'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'pcu' => ['nullable', 'array'],
            'pcu.*' => ['integer', 'exists:users,id'],
            'ketua_pcu' => ['nullable', 'integer', 'exists:users,id'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'pengumuman_jadwal' => ['nullable', 'string', 'max:3000'],
        ]);

        $requestedPcuIds = collect($data['pcu'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        $validPcuIds = User::whereIn('id', $requestedPcuIds->all())
            ->where('role', 'pcu')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $pcuIds = $requestedPcuIds
            ->filter(fn ($id) => in_array((int) $id, $validPcuIds, true))
            ->unique()
            ->values()
            ->all();
        $ketuaPcuId = (int) ($data['ketua_pcu'] ?? 0);
        if (!in_array($ketuaPcuId, $pcuIds, true)) {
            $ketuaPcuId = (int) ($pcuIds[0] ?? 0);
        }

        DB::transaction(function () use ($permohonan, $pcuIds, $ketuaPcuId, $data) {
            $permohonan->newQuery()
                ->whereKey($permohonan->id)
                ->lockForUpdate()
                ->first();

            PermohonanAssignment::where('permohonan_id', $permohonan->id)
                ->whereIn('role', ['pcu'])
                ->when(!empty($pcuIds), function ($query) use ($pcuIds) {
                    $query->whereNotIn('user_id', $pcuIds);
                })
                ->delete();

            $updateData = [
                'jadwal_lokasi' => $data['lokasi'] ?? $permohonan->jadwal_lokasi,
                'jadwal_mulai' => $data['tanggal_mulai'] ?? $permohonan->jadwal_mulai,
                'jadwal_selesai' => $data['tanggal_selesai'] ?? $permohonan->jadwal_selesai,
                'jadwal_catatan' => $data['catatan'] ?? $permohonan->jadwal_catatan,
                'jadwal_pengumuman' => $data['pengumuman_jadwal'] ?? $permohonan->jadwal_pengumuman,
                'penjadwalan_sent_at' => null,
                'ma_approved_at' => null,
            ];
            if ($this->hasUserScheduleApprovalColumns()) {
                $updateData['jadwal_sent_to_user_at'] = null;
                $updateData['jadwal_user_approved_at'] = null;
                $updateData['jadwal_user_approved_by'] = null;
            }
            $permohonan->update($updateData);

            foreach ($pcuIds as $id) {
                PermohonanAssignment::updateOrCreate(
                    [
                        'permohonan_id' => $permohonan->id,
                        'user_id' => $id,
                        'role' => 'pcu',
                    ],
                    [
                        'is_leader' => $ketuaPcuId > 0 && (int) $id === $ketuaPcuId,
                    ]
                );
            }
        });

        $pcuUserMap = User::whereIn('id', array_values(array_unique($pcuIds)))
            ->get(['id', 'name'])
            ->keyBy(fn ($user) => (int) $user->id);

        $pcu = collect($pcuIds)->map(function ($id) use ($pcuUserMap, $ketuaPcuId) {
            $user = $pcuUserMap->get((int) $id);
            return [
                'id' => (int) $id,
                'name' => $user?->name ?? '-',
                'is_leader' => $ketuaPcuId > 0 && (int) $id === $ketuaPcuId,
            ];
        })->values();
        $ketuaPcuNama = $pcu->firstWhere('is_leader', true)['name'] ?? ($pcu->first()['name'] ?? '-');

        return response()->json([
            'pcu' => $pcu,
            'ketua_pcu_id' => $ketuaPcuId ?: null,
            'ketua_pcu_nama' => $ketuaPcuNama,
            'lokasi' => $permohonan->jadwal_lokasi,
            'tanggal_mulai' => optional($permohonan->jadwal_mulai)->format('Y-m-d'),
            'tanggal_selesai' => optional($permohonan->jadwal_selesai)->format('Y-m-d'),
            'catatan' => $permohonan->jadwal_catatan,
            'pengumuman_jadwal' => $permohonan->jadwal_pengumuman,
        ]);
    }

    public function sendToUser(Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'penyelia'], true)) {
            abort(403);
        }
        $hasUserScheduleApprovalColumns = $this->hasUserScheduleApprovalColumns();

        if (!$permohonan->jadwal_mulai || !$permohonan->jadwal_selesai) {
            return redirect()->back()->with('error', 'Lengkapi jadwal sebelum dikirim ke pemohon.');
        }

        $hasPcu = $permohonan->assignments()->where('role', 'pcu')->exists();
        if (!$hasPcu) {
            return redirect()->back()->with('error', 'Pilih petugas PCU terlebih dahulu sebelum mengirim jadwal ke pemohon.');
        }

        if ($hasUserScheduleApprovalColumns && $permohonan->jadwal_sent_to_user_at && !$permohonan->jadwal_user_approved_at) {
            return redirect()->back()->with('success', 'Penjadwalan sudah dikirim ke pemohon dan sedang menunggu persetujuan.');
        }

        $step = WorkflowStep::where('kode', 'penjadwalan')->first();

        DB::transaction(function () use ($permohonan, $step, $hasUserScheduleApprovalColumns) {
            if ($step) {
                $permohonan->steps()
                    ->where('step_id', $step->id)
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->update([
                        'status' => 'in_progress',
                        'note' => 'Penjadwalan dikirim ke pemohon untuk persetujuan jadwal',
                        'finished_at' => null,
                        'updated_by' => auth()->id(),
                    ]);
            }

            $updateData = [
                'penjadwalan_sent_at' => null,
                'ma_approved_at' => null,
                'status_global' => 'penjadwalan',
            ];
            if ($hasUserScheduleApprovalColumns) {
                $updateData['jadwal_sent_to_user_at'] = now();
                $updateData['jadwal_user_approved_at'] = null;
                $updateData['jadwal_user_approved_by'] = null;
            }
            $permohonan->update($updateData);

            Notifikasi::create([
                'user_id' => $permohonan->user_id,
                'title' => 'Persetujuan Jadwal Pengujian',
                'message' => 'Jadwal pengujian untuk permohonan ' . $permohonan->kode . ' sudah tersedia. Silakan cek dan ACC jadwal di riwayat pelayanan.',
                'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
            ]);
        });

        return redirect()->back()->with('success', 'Penjadwalan berhasil dikirim ke pemohon untuk persetujuan.');
    }

    public function cancel(Request $request, Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'penyelia'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($permohonan, $data) {
            $updateData = [
                // Batalkan jadwal (untuk revisi), bukan membatalkan permohonan.
                'jadwal_mulai' => null,
                'jadwal_selesai' => null,
                'penjadwalan_sent_at' => null,
                'ma_approved_at' => null,
                'jadwal_catatan' => $data['note'] ?? $data['reason'],
                'status_global' => 'penjadwalan',
            ];
            if ($this->hasUserScheduleApprovalColumns()) {
                $updateData['jadwal_sent_to_user_at'] = null;
                $updateData['jadwal_user_approved_at'] = null;
                $updateData['jadwal_user_approved_by'] = null;
            }
            $permohonan->update($updateData);

            $permohonan->steps()
                ->whereHas('step', function ($query) {
                    $query->where('kode', 'penjadwalan');
                })
                ->update([
                    'status' => 'in_progress',
                    'note' => 'Jadwal dibatalkan untuk revisi: ' . ($data['note'] ?? $data['reason']),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Jadwal dibatalkan. Silakan revisi tanggal penjadwalan.',
        ]);
    }

    public function sendToSpt(Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'penyelia'], true)) {
            abort(403);
        }
        $hasUserScheduleApprovalColumns = $this->hasUserScheduleApprovalColumns();

        if ($permohonan->penjadwalan_sent_at) {
            return redirect()->back()->with('success', 'Penjadwalan sudah diteruskan sebelumnya ke MA.');
        }

        if ($hasUserScheduleApprovalColumns && !$permohonan->jadwal_sent_to_user_at) {
            return redirect()->back()->with('error', 'Kirim jadwal ke pemohon terlebih dahulu.');
        }

        if ($hasUserScheduleApprovalColumns && !$permohonan->jadwal_user_approved_at) {
            return redirect()->back()->with('error', 'Jadwal masih menunggu ACC dari pemohon.');
        }

        if (!$permohonan->jadwal_mulai || !$permohonan->jadwal_selesai) {
            return redirect()->back()->with('error', 'Lengkapi jadwal sebelum diteruskan ke admin.');
        }

        $step = WorkflowStep::where('kode', 'penjadwalan')->first();

        if ($step) {
            $permohonan->steps()
                ->where('step_id', $step->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->update([
                    'status' => 'approved',
                    'note' => 'Jadwal disetujui pemohon dan diteruskan ke MA untuk approval',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]);
        }

        $permohonan->update([
            'penjadwalan_sent_at' => now(),
            'ma_approved_at' => null,
            'status_global' => $permohonan->status_global ?: 'penjadwalan',
        ]);

        return redirect()->back()->with('success', 'Penjadwalan berhasil diteruskan ke Approval MA.');
    }

    private function hasUserScheduleApprovalColumns(): bool
    {
        static $hasColumns = null;

        if ($hasColumns !== null) {
            return $hasColumns;
        }

        $hasColumns = Schema::hasColumn('permohonans', 'jadwal_sent_to_user_at')
            && Schema::hasColumn('permohonans', 'jadwal_user_approved_at')
            && Schema::hasColumn('permohonans', 'jadwal_user_approved_by');

        return $hasColumns;
    }
}
