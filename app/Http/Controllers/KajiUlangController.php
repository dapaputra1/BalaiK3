<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanParameter;
use App\Models\PermohonanStep;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KajiUlangController extends Controller
{
    public function index()
    {
        $steps = $this->getSteps();
        $kajiUlangStep = $steps['kaji_ulang'] ?? null;
        $disposisiStep = $steps['disposisi'] ?? null;
        if (!$kajiUlangStep) {
            return view('admin.superadmin_kajiulang', [
                'permintaan_kajiulang' => collect(),
            ]);
        }

        $permohonanQuery = Permohonan::with(['company', 'parameters.serviceParameter.category'])
            ->with(['steps' => function ($query) use ($kajiUlangStep) {
                $query->where('step_id', $kajiUlangStep->id)->select('id', 'permohonan_id', 'step_id', 'started_at', 'updated_at', 'created_at');
            }])
            ->whereHas('steps', function ($query) use ($kajiUlangStep) {
                $query->where('step_id', $kajiUlangStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            });

        if ($disposisiStep) {
            $permohonanQuery->with(['approvals' => function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->where('status', 'approved')
                    ->whereIn('role', ['mp', 'mt']);
            }]);
        }

        $permohonans = $permohonanQuery
            ->oldest()
            ->get();

        $permohonanList = $permohonans->map(function ($permohonan) use ($kajiUlangStep) {
                $company = $permohonan->company;
                $stepRecord = $permohonan->steps->firstWhere('step_id', $kajiUlangStep->id);
                $approvalMap = collect($permohonan->approvals ?? [])->keyBy('role');
                $catatanMp = trim((string) ($approvalMap->get('mp')?->note ?? ''));
                $catatanMt = trim((string) ($approvalMap->get('mt')?->note ?? ''));
                $enteredAtIso = optional($stepRecord?->started_at ?? $stepRecord?->updated_at ?? $permohonan->created_at)->toIso8601String();
                $params = $permohonan->parameters->map(function ($param) {
                    $notTestable = $param->status === 'rejected';
                    $rawReason = $param->note ?? '';
                    $reason = $notTestable ? $rawReason : '';
                    $otherReason = '';

                    if ($notTestable && $rawReason !== '') {
                        $knownReasons = [
                            'Metode pengambilan contoh uji belum dikembangkan',
                            'Peralatan dan metode uji tidak sesuai spesifikasi',
                            'Alasan lain',
                        ];
                        if (!in_array($rawReason, $knownReasons, true)) {
                            $reason = 'Alasan lain';
                            $otherReason = $rawReason;
                        }
                    }

                    return [
                        'id' => $param->id,
                        'kategori' => $param->serviceParameter?->category?->name ?? null,
                        'nama_pelayanan' => $param->parameter_name,
                        'qty' => $param->qty,
                        'checked' => !$notTestable,
                        'not_testable' => $notTestable,
                        'reason' => $reason,
                        'other_reason' => $otherReason,
                        'harga' => (float) $param->price,
                    ];
                })->values();

                return [
                    'id' => $permohonan->id,
                    'kode' => $permohonan->kode,
                    'pelanggan' => $company?->company_name ?? '-',
                    'lokasi' => $company?->company_city ?? '-',
                    'tanggal_pengajuan' => optional($permohonan->created_at)->format('d M Y') ?? '-',
                    'created_at' => $enteredAtIso,
                    'catatan_mp' => $catatanMp,
                    'catatan_mt' => $catatanMt,
                    'parameter' => $params,
                ];
            });

        return view('admin.superadmin_kajiulang', [
            'permintaan_kajiulang' => $permohonanList,
        ]);
    }

    public function sendPenawaran(Request $request, Permohonan $permohonan)
    {
        $payload = $request->validate([
            'parameters' => ['required', 'array', 'min:1'],
            'parameters.*.id' => ['required', 'integer'],
            'parameters.*.checked' => ['nullable', 'boolean'],
            'parameters.*.not_testable' => ['nullable', 'boolean'],
            'parameters.*.reason' => ['nullable', 'string', 'max:1000'],
            'parameters.*.other_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $steps = $this->getSteps();
        $kajiUlangStep = $steps['kaji_ulang'];
        $penawaranStep = $steps['penawaran'];

        if (!$kajiUlangStep || !$penawaranStep) {
            return response()->json(['message' => 'Tahapan workflow belum lengkap.'], 422);
        }

        $hasKajiUlang = PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $kajiUlangStep->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if (!$hasKajiUlang) {
            return response()->json(['message' => 'Permohonan tidak berada pada tahap kaji ulang.'], 422);
        }

        $items = collect($payload['parameters']);
        $errors = [];

        DB::transaction(function () use ($items, $permohonan, $kajiUlangStep, $penawaranStep, &$errors) {
            foreach ($items as $item) {
                $param = PermohonanParameter::where('id', $item['id'])
                    ->where('permohonan_id', $permohonan->id)
                    ->first();

                if (!$param) {
                    $errors[] = 'Parameter tidak ditemukan pada permohonan ini.';
                    break;
                }

                $notTestable = filter_var($item['not_testable'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $checked = filter_var($item['checked'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if (!$notTestable && !$checked) {
                    $errors[] = 'Semua parameter yang dapat diuji harus dicentang.';
                    break;
                }

                $reason = null;
                if ($notTestable) {
                    $reason = trim((string) ($item['reason'] ?? ''));
                    if ($reason === 'Alasan lain') {
                        $reason = trim((string) ($item['other_reason'] ?? ''));
                    }
                    if ($reason === '') {
                        $errors[] = 'Alasan parameter tidak bisa diuji wajib diisi.';
                        break;
                    }
                }

                $param->update([
                    'status' => $notTestable ? 'rejected' : 'approved',
                    'note' => $reason,
                ]);
            }

            if (!empty($errors)) {
                return;
            }

            PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $kajiUlangStep->id)
                ->update([
                    'status' => 'approved',
                    'note' => 'Dikirim ke admin penawaran',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]);

            PermohonanStep::firstOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $penawaranStep->id],
                ['status' => 'pending', 'started_at' => now()]
            );

            $permohonan->update(['status_global' => 'penawaran']);
        });

        if (!empty($errors)) {
            return response()->json(['message' => $errors[0]], 422);
        }

        return response()->json(['message' => 'Permohonan berhasil dikirim ke penawaran.']);
    }

    public function approve(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $steps = $this->getSteps();
        $kajiUlangStep = $steps['kaji_ulang'];
        $penawaranStep = $steps['penawaran'];

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $kajiUlangStep->id)
            ->update([
                'status' => 'approved',
                'note' => $data['note'] ?? null,
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        PermohonanStep::firstOrCreate(
            ['permohonan_id' => $permohonan->id, 'step_id' => $penawaranStep->id],
            ['status' => 'pending', 'started_at' => now()]
        );

        $permohonan->update(['status_global' => 'penawaran']);

        return back()->with('success', 'Kaji ulang disetujui.');
    }

    public function reject(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        $steps = $this->getSteps();
        $kajiUlangStep = $steps['kaji_ulang'];

        PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $kajiUlangStep->id)
            ->update([
                'status' => 'rejected',
                'note' => $data['note'],
                'finished_at' => now(),
                'updated_by' => auth()->id(),
            ]);

        $permohonan->update(['status_global' => 'rejected']);

        return back()->with('success', 'Permohonan ditolak.');
    }

    private function getSteps(): array
    {
        $steps = WorkflowStep::all()->keyBy('kode');
        return [
            'disposisi' => $steps->get('disposisi'),
            'kaji_ulang' => $steps->get('kaji_ulang'),
            'penawaran' => $steps->get('penawaran'),
        ];
    }
}
