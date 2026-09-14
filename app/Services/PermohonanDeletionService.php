<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\Permohonan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PermohonanDeletionService
{
    private const PRIVATE_DISK = 'local';
    private const LEGACY_DISK = 'public';

    public function delete(Permohonan $permohonan): void
    {
        $permohonan->loadMissing([
            'company',
            'dokumenPenawaran',
            'spt',
            'draftLhu',
            'pengujian.lokasi.dokumen.files',
        ]);

        $kode = (string) ($permohonan->kode ?? '');
        $filePaths = $this->collectFilePaths($permohonan);

        DB::transaction(function () use ($permohonan, $kode) {
            if ($kode !== '') {
                Notifikasi::query()
                    ->where(function ($query) use ($kode) {
                        $query->where('title', 'like', '%' . $kode . '%')
                            ->orWhere('message', 'like', '%' . $kode . '%')
                            ->orWhere('url', 'like', '%' . $kode . '%')
                            ->orWhere('url', 'like', '%' . urlencode($kode) . '%');
                    })
                    ->delete();
            }

            $permohonan->delete();
        });

        $this->deleteFiles($filePaths);
    }

    private function collectFilePaths(Permohonan $permohonan): Collection
    {
        $draft = $permohonan->draftLhu;

        return collect([
            $permohonan->company?->order_proof_path,
            $permohonan->company?->responsible_signature_path,
            $permohonan->spt?->signed_file_path,
            $draft?->final_file_path,
            $draft?->signed_file_path,
            $draft?->invoice_file_path,
            $draft?->billing_file_path,
            $draft?->billing_payment_proof_path,
            $draft?->suket_file_path,
            $draft?->qc_revision_file_path,
        ])
            ->merge(
                $permohonan->dokumenPenawaran
                    ->flatMap(fn ($dokumen) => [$dokumen->file_path, $dokumen->signed_file_path])
            )
            ->merge(
                collect($permohonan->pengujian?->lokasi ?? [])
                    ->flatMap(fn ($lokasi) => $lokasi->dokumen ?? [])
                    ->flatMap(fn ($dokumen) => $dokumen->files ?? [])
                    ->pluck('file_path')
            )
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(fn (string $path) => trim($path))
            ->unique()
            ->values();
    }

    private function deleteFiles(Collection $paths): void
    {
        $paths->each(function (string $path) {
            $disk = $this->resolveDisk($path);
            if ($disk !== null) {
                Storage::disk($disk)->delete($path);
            }
        });
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
}
