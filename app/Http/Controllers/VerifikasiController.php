<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\Prepanalisa;
use App\Models\PrepanalisaItem;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
    public function index()
    {
        $this->ensureAccess();
        $role = auth()->user()?->role;
        $routePrefix = in_array($role, ['admin', 'superadmin', 'qc'], true) ? $role : 'superadmin';

        $permohonans = Permohonan::with([
            'company',
            'pengujian.lokasi.dokumen.files',
            'pengujian.lokasi.dokumen.parameters.serviceParameter.category',
            'koding.items',
            'prepanalisa.items.assignedUser',
        ])->where('status_global', 'verifikasi')->oldest()->get();

        $verifikasiStep = WorkflowStep::where('kode', 'verifikasi')->first();
        $masukAtByPermohonan = collect();
        if ($verifikasiStep && $permohonans->isNotEmpty()) {
            $masukAtByPermohonan = PermohonanStep::whereIn('permohonan_id', $permohonans->pluck('id'))
                ->where('step_id', $verifikasiStep->id)
                ->get()
                ->keyBy('permohonan_id')
                ->map(function (PermohonanStep $step) {
                    return optional($step->started_at ?? $step->updated_at ?? $step->created_at)->timestamp;
                });
        }

        $orders = $permohonans->map(function (Permohonan $permohonan) use ($masukAtByPermohonan) {
            $company = $permohonan->company;
            $pengujian = $permohonan->pengujian;
            $koding = $permohonan->koding;
            $prepanalisa = $permohonan->prepanalisa;
            $kodingMap = $koding ? $koding->items->keyBy('pengujian_dokumen_parameter_id') : collect();
            $legacyKodingByDoc = $koding
                ? $koding->items
                    ->filter(function ($item) {
                        return empty($item->pengujian_dokumen_parameter_id) && !empty($item->pengujian_dokumen_id);
                    })
                    ->groupBy('pengujian_dokumen_id')
                    ->map(fn ($items) => $items->sortBy('id')->values())
                : collect();
            $legacyKodingCursorByDoc = [];
            $prepanalisaMap = $prepanalisa ? $prepanalisa->items->keyBy('pengujian_dokumen_parameter_id') : collect();

            $rows = [];
            if ($pengujian) {
                $pengujian->lokasi->sortBy('urutan')->each(function ($lokasi) use (&$rows, $kodingMap, $prepanalisaMap, $legacyKodingByDoc, &$legacyKodingCursorByDoc) {
                    $lokasi->dokumen->sortBy('urutan')->each(function ($dokumen) use (&$rows, $kodingMap, $prepanalisaMap, $lokasi, $legacyKodingByDoc, &$legacyKodingCursorByDoc) {
                        $files = $dokumen->files->map(function ($file) {
                            return [
                                'id' => $file->id,
                                'name' => $file->original_name,
                                'url' => $file->file_path ? route('pengujian.files.show', $file->id) : null,
                            ];
                        })->values();

                        $dokumen->parameters->sortBy('urutan')->each(function ($docParam) use (&$rows, $lokasi, $dokumen, $files, $prepanalisaMap, $kodingMap, $legacyKodingByDoc, &$legacyKodingCursorByDoc) {
                            if ($docParam->is_direct) {
                                return;
                            }
                            $service = $docParam->serviceParameter;
                            $preItem = null;
                            if ($prepanalisaMap instanceof \Illuminate\Support\Collection) {
                                $preItem = $prepanalisaMap->get($docParam->id);
                            }
                            $kode = '';
                            if ($kodingMap instanceof \Illuminate\Support\Collection) {
                                $item = $kodingMap->get($docParam->id);
                                if (!$item && $legacyKodingByDoc instanceof \Illuminate\Support\Collection) {
                                    $legacyPool = $legacyKodingByDoc->get($dokumen->id, collect());
                                    $cursor = (int) ($legacyKodingCursorByDoc[$dokumen->id] ?? 0);
                                    if ($legacyPool instanceof \Illuminate\Support\Collection && $cursor < $legacyPool->count()) {
                                        $item = $legacyPool->get($cursor);
                                        $legacyKodingCursorByDoc[$dokumen->id] = $cursor + 1;
                                    }
                                }
                                $kode = $item?->kode ?? '';
                            }
                            $kodeFinal = $kode ?: ($preItem?->kode_koding ?? '');
                            $rows[] = [
                                'lokasi' => $lokasi->nama_lokasi ?? '-',
                                'dokumen_label' => $dokumen->label ?? '-',
                                'dokumen_files' => $files,
                                'koding' => $kodeFinal ?: '-',
                                'kategori' => $service?->category?->name ?? '-',
                                'parameter' => $service?->name ?? '-',
                                'prepanalisa_item_id' => $preItem?->id,
                                'verif_status' => $preItem?->verif_status ?? 'pending',
                                'verif_note' => $preItem?->verif_note,
                                'saved' => $preItem ? [
                                    'skpm' => $preItem->data_skpm,
                                    'hasil_baca' => $preItem->data_hasil_baca,
                                    'hasil_perhitungan' => $preItem->data_hasil_perhitungan,
                                ] : null,
                            ];
                        });
                    });
                });
            }

            $rows = collect($rows)->sortBy(['lokasi', 'dokumen_label', 'parameter'])->values();

            return [
                'permohonan_id' => $permohonan->id,
                'kode' => $permohonan->kode,
                'perusahaan' => $company?->company_name ?? '-',
                'alamat' => $permohonan->jadwal_lokasi ?: ($company?->company_city ?? '-'),
                'masuk_at_unix' => (int) ($masukAtByPermohonan->get($permohonan->id) ?? optional($permohonan->created_at)->timestamp ?? 0),
                'rows' => $rows,
            ];
        });

        return view('admin.superadmin_verifikasi', [
            'orders' => $orders,
            'routePrefix' => $routePrefix,
        ]);
    }

    public function submit(Request $request)
    {
        $this->ensureAccess();

        $data = $request->validate([
            'permohonan_id' => ['required', 'integer'],
            'items' => ['required'],
            'note' => ['nullable', 'string'],
        ]);

        $items = $data['items'];
        if (is_string($items)) {
            $items = json_decode($items, true);
        }
        if (!is_array($items)) {
            return response()->json(['message' => 'Items verifikasi tidak valid.'], 422);
        }

        $items = collect($items)->map(function ($item) {
            return [
                'item_id' => (int) ($item['item_id'] ?? 0),
                'sesuai' => (bool) ($item['sesuai'] ?? false),
            ];
        })->filter(fn ($item) => $item['item_id'] > 0)->values();

        $permohonan = Permohonan::with('prepanalisa.items')->find($data['permohonan_id']);
        if (!$permohonan) {
            return response()->json(['message' => 'Permohonan tidak ditemukan.'], 404);
        }

        $hasNotSesuai = $items->contains(fn ($item) => !($item['sesuai'] ?? false));
        if ($hasNotSesuai && empty(trim((string) ($data['note'] ?? '')))) {
            return response()->json(['message' => 'Alasan revisi wajib diisi.'], 422);
        }

        $prepanalisa = $permohonan->prepanalisa;
        if (!$prepanalisa) {
            return response()->json(['message' => 'Prepanalisa tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($items, $hasNotSesuai, $data, $prepanalisa, $permohonan) {
            foreach ($items as $item) {
                $preItem = PrepanalisaItem::where('prepanalisa_id', $prepanalisa->id)
                    ->where('id', (int) $item['item_id'])
                    ->first();
                if (!$preItem) {
                    continue;
                }
                if ($item['sesuai']) {
                    $preItem->update([
                        'verif_status' => 'approved',
                        'verif_note' => null,
                        'verif_by' => auth()->id(),
                        'verif_at' => now(),
                    ]);
                } else {
                    $preItem->update([
                        'verif_status' => 'revisi',
                        'verif_note' => trim((string) ($data['note'] ?? '')),
                        'verif_by' => auth()->id(),
                        'verif_at' => now(),
                        'is_done' => false,
                    ]);
                }
            }

            if ($hasNotSesuai) {
                $prepanalisa->update([
                    'status' => 'revisi',
                    'updated_by' => auth()->id(),
                ]);
                $this->transitionBackToPrepanalisa($permohonan, $data['note'] ?? '');
            } else {
                $this->finishVerifikasi($permohonan);
            }
        });

        return response()->json([
            'message' => $hasNotSesuai
                ? 'Revisi berhasil dikirim ke analis.'
                : 'Verifikasi berhasil diselesaikan.',
        ]);
    }

    public function previewHasil(PrepanalisaItem $item)
    {
        $this->ensureAccess();

        $item->loadMissing(['prepanalisa.permohonan.company', 'serviceParameter.category']);

        $hasilBaca = $item->data_hasil_baca ?? [];
        $hasilPerhitungan = $item->data_hasil_perhitungan ?? [];

        $kode = $item->prepanalisa?->permohonan?->kode ?? '-';
        $perusahaan = $item->prepanalisa?->permohonan?->company?->company_name ?? '-';

        return view('admin.verifikasi_hasil_preview', [
            'kode' => $kode,
            'perusahaan' => $perusahaan,
            'parameter' => $item->serviceParameter?->name ?? '-',
            'kategori' => $item->serviceParameter?->category?->name ?? '-',
            'hasilBaca' => $hasilBaca,
            'hasilPerhitungan' => $hasilPerhitungan,
        ]);
    }

    private function finishVerifikasi(Permohonan $permohonan): void
    {
        $steps = WorkflowStep::whereIn('kode', ['verifikasi', 'pembuatan_lhu'])->get()->keyBy('kode');
        $verifikasiStep = $steps->get('verifikasi');
        if (!$verifikasiStep) {
            return;
        }

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $verifikasiStep->id)
            ->update([
                'status' => 'approved',
                'note' => 'Verifikasi selesai',
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        $draftStep = $steps->get('pembuatan_lhu');
        if ($draftStep) {
            PermohonanStep::firstOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $draftStep->id],
                ['status' => 'pending', 'started_at' => now()]
            );
        }

        // Tetap lanjut ke next step agar tidak tampil lagi di halaman verifikasi.
        $permohonan->update([
            'status_global' => 'pembuatan_lhu',
            'status_lab' => 'pembuatan_lhu',
        ]);
    }

    private function transitionBackToPrepanalisa(Permohonan $permohonan, string $note = ''): void
    {
        $steps = WorkflowStep::whereIn('kode', ['preparasi_analisa', 'verifikasi'])->get()->keyBy('kode');
        $prepanalisaStep = $steps->get('preparasi_analisa');
        $verifikasiStep = $steps->get('verifikasi');

        if ($verifikasiStep) {
            PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $verifikasiStep->id)
                ->update([
                    'status' => 'rejected',
                    'note' => $note ? ('Revisi: ' . $note) : 'Revisi diminta',
                    'updated_by' => auth()->id(),
                ]);
        }

        if ($prepanalisaStep) {
            PermohonanStep::updateOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $prepanalisaStep->id],
                ['status' => 'pending', 'started_at' => now(), 'updated_by' => auth()->id()]
            );
        }

        $permohonan->update([
            'status_global' => 'preparasi_analisa',
            'status_lab' => 'preparasi_analisa',
        ]);
    }

    private function ensureAccess(): void
    {
        $user = auth()->user();
        $role = $user?->role;
        if (!in_array($role, ['admin', 'superadmin', 'qc'], true)) {
            abort(403);
        }
    }
}
