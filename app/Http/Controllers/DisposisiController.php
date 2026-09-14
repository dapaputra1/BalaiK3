<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\StepApproval;
use App\Models\WorkflowStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DisposisiController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role ?? '';
        $steps = $this->getSteps();
        $disposisiStep = $steps['disposisi'];
        if (!$disposisiStep) {
            return view('admin.superadmin_disposisi', [
                'disposisi_mp' => collect(),
                'disposisi_mt' => collect(),
            ]);
        }

        $baseQuery = Permohonan::with([
            'company',
            'parameters',
            'approvals' => function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->whereIn('role', ['mp', 'mt']);
            },
        ])
            ->whereHas('steps', function ($query) use ($disposisiStep) {
                $query->where('step_id', $disposisiStep->id)
                    ->whereIn('status', ['pending', 'in_progress']);
            });

        $mpQuery = clone $baseQuery;
        $mtQuery = clone $baseQuery;

        $mpQuery->whereHas('approvals', function ($query) use ($disposisiStep) {
            $query->where('step_id', $disposisiStep->id)
                ->where('role', 'mp')
                ->where('status', 'pending');
        });

        $mtQuery->whereHas('approvals', function ($query) use ($disposisiStep) {
            $query->where('step_id', $disposisiStep->id)
                ->where('role', 'mt')
                ->where('status', 'pending');
        })->whereHas('approvals', function ($query) use ($disposisiStep) {
            $query->where('step_id', $disposisiStep->id)
                ->where('role', 'mp')
                ->where('status', 'approved');
        });

        $disposisi_mp = $mpQuery->oldest()->get()->map(function ($permohonan) use ($disposisiStep) {
            return $this->mapDisposisiItem($permohonan, 'mp', (int) $disposisiStep->id);
        });

        $disposisi_mt = $mtQuery->oldest()->get()->map(function ($permohonan) use ($disposisiStep) {
            return $this->mapDisposisiItem($permohonan, 'mt', (int) $disposisiStep->id);
        });

        if ($role === 'mp') {
            $disposisi_mt = collect();
        } elseif ($role === 'mt') {
            $disposisi_mp = collect();
        }

        return view('admin.superadmin_disposisi', [
            'disposisi_mp' => $disposisi_mp,
            'disposisi_mt' => $disposisi_mt,
        ]);
    }

    public function approve(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate([
            'role' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:2000'],
            'g-recaptcha-response' => ['required', 'captcha'],
        ]);

        $authRole = auth()->user()->role ?? '';
        if ($authRole === 'superadmin') {
            $role = $request->input('role') ?: 'mp';
            if (!in_array($role, ['mp', 'mt'], true)) {
                return back()->withErrors(['role' => 'Role persetujuan tidak valid.']);
            }
        } elseif (in_array($authRole, ['mp', 'mt'], true)) {
            $role = $authRole;
        } else {
            return back()->withErrors(['role' => 'Role tidak valid untuk persetujuan.']);
        }

        $steps = $this->getSteps();
        $disposisiStep = $steps['disposisi'];
        $kajiUlangStep = $steps['kaji_ulang'];

        $approval = StepApproval::where('permohonan_id', $permohonan->id)
            ->where('step_id', $disposisiStep->id)
            ->where('role', $role)
            ->first();

        if ($approval) {
            $approval->update([
                'status' => 'approved',
                'note' => $data['note'] ?? null,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        }

        $mpApproved = StepApproval::where('permohonan_id', $permohonan->id)
            ->where('step_id', $disposisiStep->id)
            ->where('role', 'mp')
            ->where('status', 'approved')
            ->exists();

        $mtApproved = StepApproval::where('permohonan_id', $permohonan->id)
            ->where('step_id', $disposisiStep->id)
            ->where('role', 'mt')
            ->where('status', 'approved')
            ->exists();

        if ($mpApproved && $mtApproved) {
            PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $disposisiStep->id)
                ->update([
                    'status' => 'approved',
                    'finished_at' => now(),
                    'updated_by' => auth()->id(),
                ]);

            PermohonanStep::firstOrCreate(
                ['permohonan_id' => $permohonan->id, 'step_id' => $kajiUlangStep->id],
                ['status' => 'pending', 'started_at' => now()]
            );

            $permohonan->update(['status_global' => 'kaji_ulang']);
        }

        return back()->with('success', 'Persetujuan disposisi berhasil disimpan.');
    }

    public function showCustomerDocument(Permohonan $permohonan, string $type)
    {
        $path = match ($type) {
            'order-proof' => $permohonan->company?->order_proof_path,
            'signature' => $permohonan->company?->responsible_signature_path,
            default => null,
        };

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $mime = Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';
        $filename = basename($path);

        return Storage::disk('local')->response($path, $filename, [
            'Content-Type' => $mime,
        ]);
    }

    private function mapDisposisiItem(Permohonan $permohonan, string $role, int $disposisiStepId): array
    {
        $company = $permohonan->company;
        $stepApprovals = $permohonan->approvals
            ->where('step_id', $disposisiStepId)
            ->keyBy('role');
        $catatanMp = trim((string) ($stepApprovals->get('mp')?->note ?? ''));
        $catatanMt = trim((string) ($stepApprovals->get('mt')?->note ?? ''));
        $params = $permohonan->parameters->map(function ($param) {
            return [
                'nama' => $param->parameter_name,
                'qty' => $param->qty,
                'harga_satuan' => (float) $param->price,
            ];
        })->values();

        $dokumen = collect();
        if (!empty($company?->order_proof_path)) {
            $dokumen->push([
                'nama' => 'Bukti Pemesanan Pelanggan',
                'url' => route('superadmin.disposisi.customer-document', [
                    'permohonan' => $permohonan->id,
                    'type' => 'order-proof',
                ]),
            ]);
        }
        return [
            'id' => $permohonan->id,
            'kode' => $permohonan->kode,
            'pelanggan' => $company?->company_name ?? '-',
            'penanggung_jawab' => $company?->responsible_name ?? '-',
            'email_perusahaan' => $company?->company_email ?? '-',
            'telepon_perusahaan' => $company?->company_phone ?? '-',
            'alamat_perusahaan' => $company?->company_address ?? '-',
            'jenis_perusahaan' => $company?->company_type ?? '-',
            'provinsi_perusahaan' => $company?->company_province ?? '-',
            'kota_perusahaan' => $company?->company_city ?? '-',
            'jumlah_pekerja' => (int) ($company?->worker_count ?? 0),
            'penandatangan_sama' => (bool) ($company?->authority_same ?? false),
            'penandatangan_nama' => $company?->authority_name ?? '-',
            'penandatangan_jabatan' => $company?->authority_role ?? '-',
            'jenis' => 'Pengujian',
            'tahapan' => 'Disposisi',
            'status' => $role === 'mp' ? 'Menunggu MP' : 'Menunggu MT',
            'catatan' => $role === 'mt' ? $catatanMp : $catatanMt,
            'catatan_mp' => $catatanMp,
            'catatan_mt' => $catatanMt,
            'tanggal' => optional($permohonan->created_at)->format('d M Y'),
            'acc' => $role === 'mp' ? 'Persetujuan MP' : 'Persetujuan MT',
            'acc_mp' => $role === 'mp' ? false : true,
            'acc_mt' => $role === 'mt' ? false : true,
            'dokumen' => $dokumen->values()->all(),
            'parameter' => $params,
        ];
    }

    private function getSteps(): array
    {
        $steps = WorkflowStep::all()->keyBy('kode');
        return [
            'disposisi' => $steps->get('disposisi'),
            'kaji_ulang' => $steps->get('kaji_ulang'),
        ];
    }
}
