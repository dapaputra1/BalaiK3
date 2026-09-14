<?php

namespace App\Http\Controllers;

use App\Models\DokumenSpt;
use App\Models\DokumenSptDasarItem;
use App\Models\DokumenSptUntukItem;
use App\Models\PermohonanAssignment;
use App\Models\Permohonan;
use App\Models\PermohonanStep;
use App\Models\WorkflowStep;
use App\Support\SafeDocumentUpload;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DokumenSptController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user?->role;

        $permohonans = Permohonan::with(['company', 'assignments.user', 'spt.dasarItems', 'spt.untukItems'])
            ->whereNotNull('jadwal_mulai')
            ->whereNotNull('jadwal_selesai')
            ->whereNotNull('penjadwalan_sent_at')
            ->whereNull('spt_sent_at')
            ->when(Schema::hasColumn('permohonans', 'ma_approved_at'), function ($query) {
                $query->whereNotNull('ma_approved_at');
            })
            ->when(in_array($role, ['pcu', 'analis'], true), function ($query) use ($user, $role) {
                $query->whereHas('assignments', function ($q) use ($user, $role) {
                    $q->where('user_id', $user->id)->where('role', $role);
                });
            })
            ->oldest()
            ->get();

        return view('admin.superadmin_dokumen_spt', [
            'permohonans' => $permohonans,
        ]);
    }

    public function store(Request $request, Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'admin'], true)) {
            abort(403);
        }

        $this->ensureMaApproved($permohonan);

        $data = $request->validate([
            'nomor_surat' => ['required', 'string', 'max:150'],
            'tempat_terbit' => ['nullable', 'string', 'max:100'],
            'tanggal_terbit' => ['nullable', 'date'],
            'dasar_uraian' => ['nullable', 'string'],
            'untuk_uraian' => ['nullable', 'string'],
        ]);

        $dasarItems = $this->normalizeItems([$data['dasar_uraian'] ?? '']);
        $untukItems = $this->normalizeItems([$data['untuk_uraian'] ?? '']);

        DB::transaction(function () use ($permohonan, $data, $dasarItems, $untukItems) {
            $normalizedNomorSurat = $this->normalizeNomorSurat((string) $data['nomor_surat']);
            $spt = DokumenSpt::firstOrNew(['permohonan_id' => $permohonan->id]);
            $spt->fill([
                'nomor_surat' => $normalizedNomorSurat,
                'tempat_terbit' => $data['tempat_terbit'] ?? 'Surabaya',
                'tanggal_terbit' => $data['tanggal_terbit'] ?? now()->toDateString(),
                'created_by' => auth()->id(),
            ]);
            $spt->save();

            DokumenSptDasarItem::where('spt_id', $spt->id)->delete();
            DokumenSptUntukItem::where('spt_id', $spt->id)->delete();

            foreach ($dasarItems as $index => $item) {
                DokumenSptDasarItem::create([
                    'spt_id' => $spt->id,
                    'nomor' => null,
                    'uraian' => $item['uraian'],
                    'urutan' => $index + 1,
                ]);
            }

            foreach ($untukItems as $index => $item) {
                DokumenSptUntukItem::create([
                    'spt_id' => $spt->id,
                    'nomor' => null,
                    'uraian' => $item['uraian'],
                    'urutan' => $index + 1,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Dokumen SPT berhasil disimpan.');
    }

    public function preview(Request $request, Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'admin'], true)) {
            abort(403);
        }

        $this->ensureMaApproved($permohonan);

        $spt = DokumenSpt::with(['dasarItems', 'untukItems'])
            ->where('permohonan_id', $permohonan->id)
            ->firstOrFail();

        $permohonan->load(['company', 'assignments.user']);

        $downloadFormat = strtolower((string) $request->query('download', ''));
        $displayInline = $request->boolean('inline');
        $dasarViewItems = $this->expandStoredItems($spt->dasarItems->pluck('uraian'));
        $untukViewItems = $this->expandStoredItems($spt->untukItems->pluck('uraian'));

        if ($downloadFormat === 'pdf') {
            $filename = 'SPT-' . ($permohonan->kode ?: $permohonan->id) . '.pdf';
            $pdf = Pdf::loadView('admin.pdf.dokumen_spt', [
                'permohonan' => $permohonan,
                'spt' => $spt,
                'dasarViewItems' => $dasarViewItems,
                'untukViewItems' => $untukViewItems,
                'logoDataUri' => $this->imageToDataUri(public_path('images/Logo Kemnaker.png')),
            ])->setPaper('a4');

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => ($displayInline ? 'inline' : 'attachment') . '; filename="' . addslashes($filename) . '"',
            ]);
        }

        if ($downloadFormat === 'word') {
            $filename = 'SPT-' . ($permohonan->kode ?: $permohonan->id) . '.doc';
            $logoAsset = $this->resolveWordHeaderLogoAsset();
            $html = view('admin.word.dokumen_spt', [
                'permohonan' => $permohonan,
                'spt' => $spt,
                'dasarViewItems' => $dasarViewItems,
                'untukViewItems' => $untukViewItems,
                'logoSrc' => $logoAsset ? 'cid:word-header-logo' : '',
            ])->render();

            if ($logoAsset) {
                [$mhtml, $boundary] = $this->buildWordMhtml($html, $logoAsset);

                return response($mhtml, 200, [
                    'Content-Type' => 'multipart/related; boundary="' . $boundary . '"',
                    'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
                ]);
            }

            return response("\xEF\xBB\xBF" . $html, 200, [
                'Content-Type' => 'application/msword; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . addslashes($filename) . '"',
            ]);
        }

        $view = view('admin.superadmin_dokumen_spt_preview', [
            'permohonan' => $permohonan,
            'spt' => $spt,
            'dasarViewItems' => $dasarViewItems,
            'untukViewItems' => $untukViewItems,
            'autoPrintPdf' => false,
            'logoDataUri' => null,
        ]);

        return $view;
    }

    public function sendToPcu(Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'admin'], true)) {
            abort(403);
        }

        $this->ensureMaApproved($permohonan);

        if ($permohonan->spt_sent_at) {
            return redirect()->back()->with('success', 'SPT sudah diteruskan sebelumnya.');
        }

        if (!$permohonan->spt) {
            return redirect()->back()->with('error', 'Simpan dokumen SPT terlebih dahulu.');
        }

        $signedPath = $permohonan->spt?->signed_file_path;
        if (!$signedPath || !Storage::disk('local')->exists($signedPath)) {
            return redirect()->back()->with('error', 'Upload SPT bertanda tangan digital sebelum diteruskan ke PCU.');
        }

        $permohonan->update([
            'spt_sent_at' => now(),
            'status_dokumen' => in_array($permohonan->status_dokumen, [null, 'penjadwalan'], true)
                ? 'pengujian'
                : $permohonan->status_dokumen,
            'status_lab' => in_array($permohonan->status_lab, [null, 'penjadwalan'], true)
                ? 'pengujian'
                : $permohonan->status_lab,
            'status_global' => in_array($permohonan->status_global, [null, 'penjadwalan'], true)
                ? 'pengujian'
                : $permohonan->status_global,
        ]);

        return redirect()->back()->with('success', 'SPT berhasil diteruskan ke PCU.');
    }

    public function returnToPenjadwalan(Request $request, Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'admin'], true)) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ], [
            'reason.required' => 'Alasan pengembalian ke penjadwalan wajib diisi.',
            'reason.max' => 'Alasan pengembalian maksimal 2000 karakter.',
        ]);

        $this->sendBackToPenjadwalan($permohonan, trim((string) $data['reason']), 'Dokumen SPT');

        return redirect()->back()->with('success', 'Permohonan berhasil dikembalikan ke penjadwalan.');
    }

    public function uploadSigned(Request $request, Permohonan $permohonan)
    {
        $role = auth()->user()?->role;
        if (!in_array($role, ['superadmin', 'admin'], true)) {
            abort(403);
        }

        $this->ensureMaApproved($permohonan);

        $data = $request->validate([
            'signed_document' => ['required', 'file', 'max:5120'],
        ]);
        SafeDocumentUpload::validateOrFail($data['signed_document'], 'signed_document');

        $spt = DokumenSpt::where('permohonan_id', $permohonan->id)->first();
        if (!$spt) {
            return redirect()->back()->with('error', 'Simpan dokumen SPT terlebih dahulu.');
        }

        if ($spt->signed_file_path) {
            Storage::disk('local')->delete($spt->signed_file_path);
        }

        $folder = 'spt/' . $permohonan->id;
        $ext = strtolower((string) $data['signed_document']->getClientOriginalExtension());
        $filename = 'spt_signed_' . now()->format('Ymd_His') . '.' . ($ext !== '' ? $ext : 'pdf');
        $path = $data['signed_document']->storeAs($folder, $filename, 'local');

        $spt->update([
            'signed_file_path' => $path,
        ]);

        return redirect()->back()->with('success', 'SPT bertanda tangan digital berhasil diunggah.');
    }

    public function signed(Permohonan $permohonan)
    {
        $user = auth()->user();
        $role = $user?->role;

        if (!$user || !in_array($role, ['superadmin', 'admin', 'pcu'], true)) {
            abort(403);
        }

        if ($role === 'pcu') {
            $hasAccess = PermohonanAssignment::where('permohonan_id', $permohonan->id)
                ->where('user_id', $user->id)
                ->where('role', 'pcu')
                ->exists();

            if (!$hasAccess) {
                abort(403);
            }
        }

        $spt = DokumenSpt::where('permohonan_id', $permohonan->id)->firstOrFail();
        $path = $spt->signed_file_path;

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $filename = 'SPT-' . ($permohonan->kode ?: $permohonan->id) . ($ext !== '' ? '.' . $ext : '.pdf');
        $disposition = 'inline; filename="' . addslashes($filename) . '"';

        return Storage::disk('local')->response($path, $filename, [
            'Content-Disposition' => $disposition,
        ]);
    }

    private function normalizeItems(array $uraians): array
    {
        $items = [];
        foreach ($uraians as $uraian) {
            foreach ($this->extractRichTextItems((string) $uraian) as $itemHtml) {
                $cleaned = $this->sanitizeRichText($itemHtml);

                if ($cleaned === '') {
                    continue;
                }

                $items[] = [
                    'uraian' => $cleaned,
                ];
            }
        }

        return $items;
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

    private function expandStoredItems(Collection $items): array
    {
        $expanded = [];

        foreach ($items as $uraian) {
            foreach ($this->extractRichTextItems((string) $uraian) as $itemHtml) {
                $cleaned = $this->sanitizeRichText($itemHtml);

                if ($cleaned === '') {
                    continue;
                }

                $expanded[] = $cleaned;
            }
        }

        return $expanded;
    }

    private function sanitizeRichText(string $html): string
    {
        $clean = trim($html);
        if ($clean === '') {
            return '';
        }

        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = str_replace("\xc2\xa0", ' ', $clean);
        $clean = strip_tags($clean, '<b><strong><ol><ul><li><br><p>');
        $clean = preg_replace('/<(\/?)(b|strong|ol|ul|li|p)\b[^>]*>/i', '<$1$2>', $clean) ?? $clean;
        $clean = preg_replace('/<br\b[^>]*>/i', '<br>', $clean) ?? $clean;
        $clean = preg_replace('/[ \t\r\n]+/u', ' ', $clean) ?? $clean;
        $clean = preg_replace('/>\s+</u', '><', $clean) ?? $clean;
        $clean = preg_replace('/<p>\s*<\/p>/i', '', $clean) ?? $clean;
        $clean = preg_replace('/<li>\s*/i', '<li>', $clean) ?? $clean;
        $clean = preg_replace('/\s*<\/li>/i', '</li>', $clean) ?? $clean;
        $clean = preg_replace('/<p>\s*/i', '<p>', $clean) ?? $clean;
        $clean = preg_replace('/\s*<\/p>/i', '</p>', $clean) ?? $clean;
        $clean = preg_replace('/<br>\s*/i', '<br>', $clean) ?? $clean;

        return trim($clean);
    }

    private function extractRichTextItems(string $html): array
    {
        $clean = trim($html);
        if ($clean === '') {
            return [];
        }

        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = str_replace("\xc2\xa0", ' ', $clean);

        if (!preg_match('/<li\b/i', $clean)) {
            return [$clean];
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $clean . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        if ($loaded === false) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return [$clean];
        }

        $root = $dom->getElementsByTagName('div')->item(0);
        if (!$root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            return [$clean];
        }

        $xpath = new \DOMXPath($dom);
        $liNodes = $xpath->query('./ol/li | ./ul/li | .//ol/li | .//ul/li', $root);
        $items = [];

        if ($liNodes !== false) {
            foreach ($liNodes as $liNode) {
                $itemHtml = trim($this->innerHtml($liNode));
                if ($itemHtml !== '') {
                    $items[] = $itemHtml;
                }
            }
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $items !== [] ? $items : [$clean];
    }

    private function innerHtml(\DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $childNode) {
            $html .= $node->ownerDocument?->saveHTML($childNode) ?? '';
        }

        return $html;
    }

    private function ensureMaApproved(Permohonan $permohonan): void
    {
        if (!Schema::hasColumn('permohonans', 'ma_approved_at')) {
            return;
        }

        if (!$permohonan->penjadwalan_sent_at || !$permohonan->ma_approved_at) {
            abort(403, 'Permohonan belum disetujui MA.');
        }
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

    private function buildWordMhtml(string $html, array $logoAsset): array
    {
        $boundary = '----=_NextPart_' . md5((string) microtime(true));
        $eol = "\r\n";

        $mhtml = 'MIME-Version: 1.0' . $eol;
        $mhtml .= 'Content-Type: multipart/related; boundary="' . $boundary . '"; type="text/html"' . $eol . $eol;

        $mhtml .= '--' . $boundary . $eol;
        $mhtml .= 'Content-Type: text/html; charset="utf-8"' . $eol;
        $mhtml .= 'Content-Transfer-Encoding: quoted-printable' . $eol;
        $mhtml .= 'Content-Location: file:///C:/document.html' . $eol . $eol;
        $mhtml .= quoted_printable_encode("\xEF\xBB\xBF" . $html) . $eol;

        $mhtml .= '--' . $boundary . $eol;
        $mhtml .= 'Content-Type: ' . ($logoAsset['mime'] ?? 'image/png') . $eol;
        $mhtml .= 'Content-Transfer-Encoding: base64' . $eol;
        $mhtml .= 'Content-Location: ' . ($logoAsset['filename'] ?? 'word-header-logo.png') . $eol;
        $mhtml .= 'Content-ID: <word-header-logo>' . $eol . $eol;
        $mhtml .= chunk_split(base64_encode((string) ($logoAsset['binary'] ?? ''))) . $eol;

        $mhtml .= '--' . $boundary . '--';

        return [$mhtml, $boundary];
    }

    private function normalizeNomorSurat(string $nomorSurat): string
    {
        $trimmed = trim($nomorSurat);
        if (!preg_match('#^5\.12\s*/\s*([^/]+)\s*/\s*AS\.03\.01\s*/\s*([IVX]+)\s*/\s*(\d{4})$#i', $trimmed, $matches)) {
            return $trimmed;
        }

        $midRaw = preg_replace('/\D+/', '', (string) ($matches[1] ?? ''));
        $mid = str_pad(substr($midRaw, 0, 3), 3, '0', STR_PAD_LEFT);
        $romanMap = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $roman = $romanMap[(int) now()->format('n')] ?? 'I';
        $year = now()->format('Y');

        return "5.12/{$mid}/AS.03.01/{$roman}/{$year}";
    }
}
