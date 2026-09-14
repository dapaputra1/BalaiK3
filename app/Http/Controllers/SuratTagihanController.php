<?php

namespace App\Http\Controllers;

use App\Models\DraftLhu;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuratTagihanController extends Controller
{
    public function index()
    {
        $this->ensureAccess();

        $suratTagihanStep = $this->ensureWorkflowStep('surat_tagihan', 'Surat Tagihan', 16);
        $query = Permohonan::with([
            'company',
            'parameters',
            'pengujian.lokasi.dokumen.parameters.serviceParameter',
            'draftLhu',
        ]);

        $query->where(function ($q) use ($suratTagihanStep) {
            $q->whereHas('steps', function ($inner) use ($suratTagihanStep) {
                $inner->where('step_id', $suratTagihanStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            })->orWhere('status_global', 'surat_tagihan');
        });

        $permohonans = $query->oldest()->get();
        $rolePrefix = auth()->user()?->role === 'admin' ? 'admin' : 'superadmin';

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($rolePrefix) {
            $company = $permohonan->company;
            $penawaran = $this->aggregatePenawaran($permohonan);
            $pengujian = $this->aggregatePengujian($permohonan, $penawaran);
            $hasPerubahan = $this->hasParameterDifference($penawaran['items'], $pengujian['items']);
            $draft = $permohonan->draftLhu;

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'alamat' => $company?->company_address ?? '-',
                'penawaran_items' => $penawaran['items']->values()->all(),
                'pengujian_items' => $pengujian['items']->values()->all(),
                'subtotal_penawaran' => (float) $penawaran['subtotal'],
                'subtotal_pengujian' => (float) $pengujian['subtotal'],
                'has_perubahan_pengujian' => $hasPerubahan,
                'surat_tagihan_url' => route($rolePrefix . '.surat-tagihan.show', $permohonan->id),
                'submit_url' => route($rolePrefix . '.surat-tagihan.submit', $permohonan->id),
                'generated_at' => optional($draft?->surat_tagihan_generated_at)->format('d M Y H:i'),
            ];
        });

        return view('admin.superadmin_surat_tagihan', [
            'orders' => $orders,
            'routePrefix' => $rolePrefix,
        ]);
    }

    public function show(Permohonan $permohonan)
    {
        $this->ensureAccess();

        $permohonan->loadMissing([
            'company',
            'parameters',
            'pengujian.lokasi.dokumen.parameters.serviceParameter',
        ]);

        $penawaran = $this->aggregatePenawaran($permohonan);
        $pengujian = $this->aggregatePengujian($permohonan, $penawaran);
        $company = $permohonan->company;
        $items = $pengujian['items'] instanceof Collection ? $pengujian['items']->values()->all() : [];
        $total = (float) ($pengujian['subtotal'] ?? 0);

        return view('admin.surat_tagihan_preview', [
            'kode' => $permohonan->kode ?? '-',
            'tanggal' => now()->format('d-m-Y'),
            'perusahaan' => $company?->company_name ?? '-',
            'alamat' => $company?->company_address ?? '-',
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function submitToInvoice(Permohonan $permohonan)
    {
        $this->ensureAccess();
        $this->ensureSuratTagihanColumns();

        $signedPath = $permohonan->draftLhu?->signed_file_path;
        if (!$signedPath) {
            return response()->json(['message' => 'Upload LHU TTD terlebih dahulu sebelum lanjut ke Surat Tagihan.'], 422);
        }

        $suratTagihanStep = $this->ensureWorkflowStep('surat_tagihan', 'Surat Tagihan', 16);
        $billingStep = $this->ensureWorkflowStep('kode_billing', 'Kode Billing', 17);
        $targetUserIds = $this->resolveCustomerNotificationUserIds($permohonan);

        DB::transaction(function () use ($permohonan, $suratTagihanStep, $billingStep, $targetUserIds) {
            DraftLhu::firstOrCreate(
                ['permohonan_id' => $permohonan->id],
                ['created_by' => auth()->id()]
            )->update([
                'surat_tagihan_generated_by' => auth()->id(),
                'surat_tagihan_generated_at' => now(),
                'invoice_generated_by' => null,
                'invoice_generated_at' => null,
                'invoice_verified_by_user_id' => null,
                'invoice_verified_by_user_at' => null,
                'updated_by' => auth()->id(),
            ]);

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $suratTagihanStep->id],
                [
                    'status' => 'approved',
                    'note' => 'Surat tagihan selesai dibuat',
                    'started_at' => now(),
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]
            );

            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $billingStep->id],
                [
                    'status' => 'pending',
                    'note' => 'Surat tagihan dikirim ke pemohon, menunggu ACC sebelum upload kode billing',
                    'started_at' => now(),
                    'finished_at' => null,
                    'updated_by' => auth()->id(),
                ]
            );

            $permohonan->update([
                'status_global' => 'billing',
                'status_lab' => 'billing',
            ]);

            foreach ($targetUserIds as $userId) {
                Notifikasi::create([
                    'user_id' => $userId,
                    'title' => 'Surat Tagihan Tersedia',
                    'message' => 'Surat tagihan permohonan ' . $permohonan->kode . ' sudah tersedia. Setelah Anda ACC, petugas akan mengirimkan kode billing.',
                    'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
                ]);
            }

            User::query()
                ->whereIn('role', ['admin', 'superadmin'])
                ->select('id')
                ->get()
                ->each(function (User $user) use ($permohonan) {
                    Notifikasi::create([
                        'user_id' => $user->id,
                        'title' => 'Surat Tagihan Dikirim',
                        'message' => 'Surat tagihan permohonan ' . $permohonan->kode . ' sudah dikirim ke pemohon dan menunggu ACC.',
                        'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
                    ]);
                });
        });

        return response()->json(['message' => 'Surat tagihan dikirim ke pelanggan. Menunggu ACC pelanggan sebelum petugas kirim kode billing.']);
    }

    private function ensureAccess(): void
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['admin', 'superadmin'], true)) {
            abort(403);
        }
    }

    private function ensureWorkflowStep(string $kode, string $nama, int $urutan): WorkflowStep
    {
        return WorkflowStep::firstOrCreate(
            ['kode' => $kode],
            ['nama' => $nama, 'urutan' => $urutan]
        );
    }

    private function ensureSuratTagihanColumns(): void
    {
        if (
            !Schema::hasTable('draft_lhus')
            || !Schema::hasColumn('draft_lhus', 'surat_tagihan_generated_by')
            || !Schema::hasColumn('draft_lhus', 'surat_tagihan_generated_at')
        ) {
            abort(500, 'Struktur database surat tagihan belum tersedia. Jalankan migrate terbaru.');
        }
    }

    private function aggregatePenawaran(Permohonan $permohonan): array
    {
        $rows = $permohonan->parameters->map(function ($param) {
            return [
                'service_parameter_id' => $param->service_parameter_id,
                'nama' => $param->parameter_name ?? '-',
                'qty' => (int) ($param->qty ?? 0),
                'harga' => (float) ($param->price ?? 0),
            ];
        });

        $items = $this->aggregateRows($rows);
        $subtotal = $items->sum(fn ($item) => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));

        return [
            'items' => $items,
            'subtotal' => (float) $subtotal,
        ];
    }

    private function aggregatePengujian(Permohonan $permohonan, array $penawaran): array
    {
        $penawaranMap = collect($penawaran['items'])
            ->mapWithKeys(function ($item) {
                return [$this->buildParamKey($item['service_parameter_id'] ?? null, $item['nama'] ?? '-') => $item];
            });

        $rows = collect();
        if ($permohonan->pengujian) {
            $permohonan->pengujian->lokasi->each(function ($lokasi) use (&$rows) {
                $lokasi->dokumen->each(function ($dokumen) use (&$rows) {
                    $dokumen->parameters->each(function ($param) use (&$rows) {
                        $rows->push([
                            'service_parameter_id' => $param->service_parameter_id,
                            'nama' => $param->serviceParameter?->name ?? '-',
                            'qty' => (int) ($param->qty ?? 0),
                            'harga' => (float) ($param->serviceParameter?->price ?? 0),
                        ]);
                    });
                });
            });
        }

        $items = $this->aggregateRows($rows, function ($row) use ($penawaranMap) {
            $key = $this->buildParamKey($row['service_parameter_id'] ?? null, $row['nama'] ?? '-');
            if ($penawaranMap->has($key)) {
                return (float) ($penawaranMap->get($key)['harga'] ?? 0);
            }

            return (float) ($row['harga'] ?? 0);
        });

        $subtotal = $items->sum(fn ($item) => ($item['qty'] ?? 0) * ($item['harga'] ?? 0));
        return [
            'items' => $items,
            'subtotal' => (float) $subtotal,
        ];
    }

    private function aggregateRows(Collection $rows, ?callable $resolvePrice = null): Collection
    {
        $grouped = [];

        foreach ($rows as $row) {
            $nama = trim((string) ($row['nama'] ?? '-'));
            $qty = (int) ($row['qty'] ?? 0);
            $harga = $resolvePrice ? (float) $resolvePrice($row) : (float) ($row['harga'] ?? 0);
            $id = $row['service_parameter_id'] ?? null;
            $key = $this->buildParamKey($id, $nama);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'service_parameter_id' => $id,
                    'nama' => $nama !== '' ? $nama : '-',
                    'qty' => 0,
                    'harga' => $harga,
                ];
            }

            $grouped[$key]['qty'] += $qty;
            if ($grouped[$key]['harga'] <= 0 && $harga > 0) {
                $grouped[$key]['harga'] = $harga;
            }
        }

        return collect(array_values($grouped))->map(function ($item) {
            $item['qty'] = (int) ($item['qty'] ?? 0);
            $item['harga'] = (float) ($item['harga'] ?? 0);
            return $item;
        })->sortBy('nama')->values();
    }

    private function buildParamKey($serviceParameterId, string $name): string
    {
        if ($serviceParameterId) {
            return 'id:' . $serviceParameterId;
        }

        return 'name:' . strtolower(trim($name));
    }

    private function hasParameterDifference(Collection $penawaranItems, Collection $pengujianItems): bool
    {
        $serialize = function (Collection $items): array {
            return $items
                ->map(function ($item) {
                    return [
                        'service_parameter_id' => (int) ($item['service_parameter_id'] ?? 0),
                        'nama' => trim((string) ($item['nama'] ?? '')),
                        'qty' => (int) ($item['qty'] ?? 0),
                    ];
                })
                ->sortBy(fn ($item) => sprintf('%s-%s', $item['service_parameter_id'], $item['nama']))
                ->values()
                ->all();
        };

        return $serialize($penawaranItems) !== $serialize($pengujianItems);
    }

    private function resolveCustomerNotificationUserIds(Permohonan $permohonan): array
    {
        $userIds = collect();

        if (!empty($permohonan->user_id)) {
            $userIds->push((int) $permohonan->user_id);
        }

        $companyEmail = trim((string) ($permohonan->company?->company_email ?? ''));
        if ($companyEmail !== '') {
            $matchedIds = User::query()
                ->where('role', 'user')
                ->where('email', $companyEmail)
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

            $userIds = $userIds->merge($matchedIds);
        }

        return $userIds
            ->filter(fn ($id) => (int) $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
