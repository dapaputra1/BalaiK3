<?php

namespace App\Http\Controllers;

use App\Models\DokumenPenawaran;
use App\Models\Notifikasi;
use App\Models\Permohonan;
use App\Models\PermohonanParameter;
use App\Models\PermohonanStep;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PenawaranController extends Controller
{
    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

    public function index()
    {
        $step = WorkflowStep::where('kode', 'penawaran')->first();

        $penawaranList = collect();
        if ($step) {
            $penawaranList = Permohonan::with(['company', 'parameters.serviceParameter.category', 'dokumenPenawaran'])
                ->with(['steps' => function ($query) use ($step) {
                    $query->where('step_id', $step->id)
                        ->select('id', 'permohonan_id', 'step_id', 'started_at', 'updated_at', 'created_at');
                }])
                ->whereHas('steps', function ($query) use ($step) {
                    $query->where('step_id', $step->id)
                        ->whereIn('status', ['pending', 'in_progress']);
                })
                ->oldest()
                ->get()
                ->map(function ($permohonan) use ($step) {
                    $company = $permohonan->company;
                    $stepRecord = $permohonan->steps->firstWhere('step_id', $step->id);
                    $enteredAt = $stepRecord?->started_at
                        ?? $stepRecord?->updated_at
                        ?? $stepRecord?->created_at
                        ?? $permohonan->created_at;
                    $params = $permohonan->parameters->map(function ($param) {
                        $rejected = $param->status === 'rejected';
                        $category = $param->serviceParameter?->category;
                        $categoryLabel = $category?->short_code ?: ($category?->name ?? '-');
                        return [
                            'id' => $param->id,
                            'nama' => $param->parameter_name,
                            'kategori' => $categoryLabel,
                            'bisa_uji' => !$rejected,
                            'alasan' => $rejected ? ($param->note ?? '') : '',
                            'qty' => $param->qty,
                            'harga' => (float) $param->price,
                        ];
                    })->values();
                    $categories = $permohonan->parameters
                        ->map(fn ($param) => $param->serviceParameter?->category?->name)
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                    $perihal = count($categories) > 0 ? implode(', ', $categories) : '...........................';
                    $signerName = $company?->authority_same
                        ? ($company?->responsible_name ?? '-')
                        : ($company?->authority_name ?? ($company?->responsible_name ?? '-'));
                    $signerRole = $company?->authority_role ?? '-';

                    $latestDoc = $permohonan->dokumenPenawaran
                        ->sortByDesc('created_at')
                        ->first();
                    $docStatus = $latestDoc?->status;
                    $docNomorSurat = $latestDoc?->nomor_surat;
                    $docCatatan = $latestDoc?->catatan;
                    $docPath = $latestDoc?->signed_file_path ?: $latestDoc?->file_path;
                    $docUrl = ($latestDoc && $docPath) ? route('penawaran.documents.show', $latestDoc) : null;

                    return [
                        'permohonan_id' => $permohonan->id,
                        'kode' => sprintf('PNW-%s-%04d', now()->format('Y'), $permohonan->id),
                        'pelanggan' => $company?->company_name ?? '-',
                        'pic' => 'Admin',
                        'status' => 'Draft',
                        'berlaku_hingga' => now()->addDays(7)->format('d M Y'),
                        'catatan' => $docCatatan ?? '',
                        'parameter' => $params,
                        'request_date' => optional($permohonan->created_at)->toIso8601String(),
                        'entered_at' => optional($enteredAt)->toIso8601String(),
                        'perihal' => $perihal,
                        'signer_name' => $signerName,
                        'signer_role' => $signerRole,
                        'doc_status' => $docStatus,
                        'doc_url' => $docUrl,
                        'nomor_surat' => $docNomorSurat,
                        'sampling_date' => optional($permohonan->jadwal_mulai)->format('Y-m-d'),
                    ];
                });
        }

        return view('admin.superadmin_penawaran', [
            'penawaran_list' => [
                'unsent' => $penawaranList->filter(function ($row) {
                    return !in_array($row['doc_status'], ['sent', 'received', 'signed', 'rejected'], true);
                })->values(),
                'sent' => $penawaranList->filter(function ($row) {
                    return $row['doc_status'] === 'sent';
                })->values(),
            ],
        ]);
    }

    public function send(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate([
            'parameters' => ['required', 'string'],
            'nomor_surat' => ['required', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'sampling_date' => ['nullable', 'date'],
            'document' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $step = WorkflowStep::where('kode', 'penawaran')->first();
        if (!$step) {
            return response()->json(['message' => 'Tahapan penawaran belum tersedia.'], 422);
        }

        $hasPenawaran = PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $step->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if (!$hasPenawaran) {
            return response()->json(['message' => 'Permohonan tidak berada pada tahap penawaran.'], 422);
        }

        $parameters = json_decode($data['parameters'], true);
        if (!is_array($parameters) || count($parameters) === 0) {
            return response()->json(['message' => 'Data parameter tidak valid.'], 422);
        }

        $document = $data['document'];
        SafeDocumentUpload::validateOrFail($document, 'document');
        $documentUrl = null;

        DB::transaction(function () use ($parameters, $permohonan, $step, $document, $data, &$documentUrl) {
            foreach ($parameters as $param) {
                $paramId = (int) ($param['id'] ?? 0);
                if ($paramId <= 0) {
                    continue;
                }

                $row = PermohonanParameter::where('id', $paramId)
                    ->where('permohonan_id', $permohonan->id)
                    ->first();

                if (!$row) {
                    continue;
                }

                $qty = isset($param['qty']) ? (int) $param['qty'] : $row->qty;
                $price = isset($param['harga']) ? (float) $param['harga'] : $row->price;

                $row->update([
                    'qty' => $qty > 0 ? $qty : $row->qty,
                    'price' => $price >= 0 ? $price : $row->price,
                ]);
            }

            $folder = 'penawaran/' . $permohonan->id;
            $filename = 'penawaran_' . now()->format('Ymd_His') . '.pdf';
            $path = $document->storeAs($folder, $filename, self::PRIVATE_DISK);

            DokumenPenawaran::create([
                'permohonan_id' => $permohonan->id,
                'nomor_surat' => $data['nomor_surat'],
                'catatan' => $data['catatan'] ?? null,
                'file_path' => $path,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $latestDoc = DokumenPenawaran::where('permohonan_id', $permohonan->id)
                ->latest('id')
                ->first();
            $documentUrl = $latestDoc ? route('penawaran.documents.show', $latestDoc) : null;

            if (!empty($data['sampling_date'])) {
                $permohonan->update([
                    'jadwal_mulai' => $data['sampling_date'],
                ]);
            }

            PermohonanStep::where('permohonan_id', $permohonan->id)
                ->where('step_id', $step->id)
                ->update([
                    'status' => 'in_progress',
                    'note' => 'Penawaran dikirim ke pemohon',
                    'updated_by' => auth()->id(),
                ]);

            Notifikasi::create([
                'user_id' => $permohonan->user_id,
                'title' => 'Penawaran baru tersedia',
                'message' => 'Silakan cek penawaran untuk permohonan ' . $permohonan->kode . '.',
                'url' => url('/riwayat_pelayanan?kode=' . $permohonan->kode),
            ]);
        });

        return response()->json([
            'message' => 'Penawaran berhasil dikirim ke pemohon.',
            'document_url' => $documentUrl,
        ]);
    }

    public function showDocument(Request $request, DokumenPenawaran $dokumen)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $isOwner = (int) $dokumen->permohonan?->user_id === (int) $user->id;
        $isInternal = $user->role !== 'user';
        if (!$isOwner && !$isInternal) {
            abort(403);
        }

        $path = $dokumen->signed_file_path ?: $dokumen->file_path;
        $disk = $this->resolveDisk($path);
        if (!$path || $disk === null) {
            abort(404);
        }

        $fullPath = Storage::disk($disk)->path($path);
        $name = basename($path);
        $forceDownload = $request->boolean('download');
        if ($forceDownload) {
            return response()->download($fullPath, $name);
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            return response()->file($fullPath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $name . '"',
            ]);
        }

        return response()->download($fullPath, $name);
    }

    public function saveDraft(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate([
            'parameters' => ['required', 'array'],
            'nomor_surat' => ['nullable', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'sampling_date' => ['nullable', 'date'],
        ]);

        $step = WorkflowStep::where('kode', 'penawaran')->first();
        if (!$step) {
            return response()->json(['message' => 'Tahapan penawaran belum tersedia.'], 422);
        }

        $hasPenawaran = PermohonanStep::where('permohonan_id', $permohonan->id)
            ->where('step_id', $step->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if (!$hasPenawaran) {
            return response()->json(['message' => 'Permohonan tidak berada pada tahap penawaran.'], 422);
        }

        $parameters = $data['parameters'];
        if (count($parameters) === 0) {
            return response()->json(['message' => 'Data parameter tidak valid.'], 422);
        }

        DB::transaction(function () use ($parameters, $permohonan, $data) {
            foreach ($parameters as $param) {
                $paramId = (int) ($param['id'] ?? 0);
                if ($paramId <= 0) {
                    continue;
                }

                $row = PermohonanParameter::where('id', $paramId)
                    ->where('permohonan_id', $permohonan->id)
                    ->first();

                if (!$row) {
                    continue;
                }

                $qty = isset($param['qty']) ? (int) $param['qty'] : $row->qty;
                $price = isset($param['harga']) ? (float) $param['harga'] : $row->price;

                $row->update([
                    'qty' => $qty > 0 ? $qty : $row->qty,
                    'price' => $price >= 0 ? $price : $row->price,
                ]);
            }

            $draft = DokumenPenawaran::where('permohonan_id', $permohonan->id)
                ->where('status', 'draft')
                ->orderByDesc('created_at')
                ->first();

            if ($draft) {
                $draft->update([
                    'nomor_surat' => $data['nomor_surat'] ?? null,
                    'catatan' => $data['catatan'] ?? null,
                ]);
            } else {
                DokumenPenawaran::create([
                    'permohonan_id' => $permohonan->id,
                    'nomor_surat' => $data['nomor_surat'] ?? null,
                    'catatan' => $data['catatan'] ?? null,
                    'file_path' => '',
                    'status' => 'draft',
                ]);
            }

            if (!empty($data['sampling_date'])) {
                $permohonan->update([
                    'jadwal_mulai' => $data['sampling_date'],
                ]);
            }
        });

        return response()->json([
            'message' => 'Draft penawaran tersimpan.',
        ]);
    }

    public function downloadPdf(Request $request, Permohonan $permohonan)
    {
        $data = $request->validate([
            'mode' => ['required', 'in:full,non_testable'],
            'nomor_surat' => ['required', 'string', 'max:150'],
            'kode' => ['nullable', 'string', 'max:150'],
            'pelanggan' => ['nullable', 'string', 'max:255'],
            'request_date' => ['nullable', 'string', 'max:100'],
            'perihal' => ['nullable', 'string', 'max:255'],
            'signer_name' => ['nullable', 'string', 'max:255'],
            'signer_role' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'parameters' => ['required', 'array', 'min:1'],
            'parameters.*.nama' => ['required', 'string', 'max:255'],
            'parameters.*.kategori' => ['nullable', 'string', 'max:255'],
            'parameters.*.qty' => ['nullable', 'integer', 'min:0'],
            'parameters.*.harga' => ['nullable', 'numeric', 'min:0'],
            'parameters.*.subtotal' => ['nullable', 'numeric', 'min:0'],
            'parameters.*.bisa_uji' => ['required', 'boolean'],
            'parameters.*.alasan' => ['nullable', 'string', 'max:2000'],
        ]);

        $mode = $data['mode'];
        $parameters = collect($data['parameters'])
            ->map(function (array $param) {
                $qty = max((int) ($param['qty'] ?? 0), 0);
                $harga = max((float) ($param['harga'] ?? 0), 0);
                $subtotal = isset($param['subtotal'])
                    ? max((float) $param['subtotal'], 0)
                    : ($qty * $harga);

                return [
                    'nama' => trim((string) ($param['nama'] ?? '-')),
                    'kategori' => trim((string) ($param['kategori'] ?? '-')) ?: '-',
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $subtotal,
                    'bisa_uji' => (bool) ($param['bisa_uji'] ?? false),
                    'alasan' => trim((string) ($param['alasan'] ?? '')) ?: '-',
                ];
            })
            ->values();

        $testable = $parameters->where('bisa_uji', true)->values();
        $nonTestable = $parameters->where('bisa_uji', false)->values();

        if ($mode === 'full' && $testable->isEmpty()) {
            return response()->json([
                'message' => 'Semua parameter tidak bisa diuji. Gunakan dokumen Tidak Bisa Diuji.',
            ], 422);
        }

        if ($mode === 'non_testable' && $nonTestable->isEmpty()) {
            return response()->json([
                'message' => 'Parameter tidak bisa diuji tidak ditemukan.',
            ], 422);
        }

        $permohonan->loadMissing('company');

        $kode = trim((string) ($data['kode'] ?? '')) ?: sprintf('PNW-%s-%04d', now()->format('Y'), $permohonan->id);
        $pelanggan = trim((string) ($data['pelanggan'] ?? '')) ?: ($permohonan->company?->company_name ?? '-');
        $perihal = trim((string) ($data['perihal'] ?? '')) ?: '...........................';
        $signerName = trim((string) ($data['signer_name'] ?? '')) ?: '.................................';
        $signerRole = trim((string) ($data['signer_role'] ?? '')) ?: '(Jabatan)';
        $nomorSurat = trim((string) $data['nomor_surat']);
        $catatan = trim((string) ($data['catatan'] ?? ''));

        $tanggalPenawaran = Carbon::now()->locale('id')->translatedFormat('j F Y');
        $requestDateText = '..................';
        if (!empty($data['request_date'])) {
            try {
                $requestDateText = Carbon::parse($data['request_date'])->locale('id')->translatedFormat('j F Y');
            } catch (\Throwable $e) {
                $requestDateText = trim((string) $data['request_date']);
            }
        }

        $total = (int) round($testable->sum('subtotal'));
        $pdfData = [
            'mode' => $mode,
            'kode' => $kode,
            'nomorSurat' => $nomorSurat,
            'pelanggan' => $pelanggan,
            'perihal' => $perihal,
            'signerName' => $signerName,
            'signerRole' => $signerRole,
            'catatan' => $catatan,
            'tanggalPenawaran' => $tanggalPenawaran,
            'requestDateText' => $requestDateText,
            'paragraphText' => $mode === 'non_testable'
                ? 'Sehubungan dengan permintaan saudara tanggal ' . $requestDateText . ' perihal pengujian ' . $perihal . ', berikut kami sampaikan daftar parameter yang belum dapat diuji saat ini :'
                : 'Sehubungan dengan permintaan saudara tanggal ' . $requestDateText . ' perihal pengujian ' . $perihal . ', dengan ini kami sampaikan penawaran biaya pengujian sebagai berikut:',
            'testableGroups' => $testable->groupBy('kategori'),
            'nonTestableGroups' => $nonTestable->groupBy('kategori'),
            'nonTestableCount' => $nonTestable->count(),
            'total' => $total,
            'totalTerbilang' => $total > 0 ? trim($this->terbilang($total)) . ' rupiah' : 'nol rupiah',
            'logoDataUri' => $this->imageToDataUri(public_path('images/Logo Kemnaker.png')),
        ];

        $pdf = Pdf::loadView('admin.pdf.penawaran', $pdfData)->setPaper('a4');
        $filename = ($mode === 'non_testable' ? 'parameter_tidak_bisa_diuji_' : 'penawaran_')
            . str_replace([' ', '/'], ['_', '-'], strtolower($kode))
            . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function resolveDisk(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            return self::PRIVATE_DISK;
        }
        if (Storage::disk(self::LEGACY_DISK)->exists($path)) {
            return self::LEGACY_DISK;
        }
        return null;
    }

    private function imageToDataUri(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function terbilang(int $nilai): string
    {
        $nilai = abs($nilai);
        $angka = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($nilai < 12) {
            return $angka[$nilai];
        }
        if ($nilai < 20) {
            return $this->terbilang($nilai - 10) . ' belas';
        }
        if ($nilai < 100) {
            return trim($this->terbilang((int) floor($nilai / 10)) . ' puluh ' . $this->terbilang($nilai % 10));
        }
        if ($nilai < 200) {
            return trim('seratus ' . $this->terbilang($nilai - 100));
        }
        if ($nilai < 1000) {
            return trim($this->terbilang((int) floor($nilai / 100)) . ' ratus ' . $this->terbilang($nilai % 100));
        }
        if ($nilai < 2000) {
            return trim('seribu ' . $this->terbilang($nilai - 1000));
        }
        if ($nilai < 1000000) {
            return trim($this->terbilang((int) floor($nilai / 1000)) . ' ribu ' . $this->terbilang($nilai % 1000));
        }
        if ($nilai < 1000000000) {
            return trim($this->terbilang((int) floor($nilai / 1000000)) . ' juta ' . $this->terbilang($nilai % 1000000));
        }

        return (string) $nilai;
    }
}
