<?php

namespace App\Services;

use App\Models\BillingPaymentGuide;
use App\Support\SafeDocumentUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BillingGuideService
{
    private const DISK = 'local';

    public function getActiveGuide(): ?BillingPaymentGuide
    {
        if (!Schema::hasTable('billing_payment_guides')) {
            return null;
        }

        $guide = BillingPaymentGuide::query()
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if (!$guide || !$guide->file_path || !Storage::disk(self::DISK)->exists($guide->file_path)) {
            return null;
        }

        return $guide;
    }

    public function getViewData(bool $canDownload = false): array
    {
        $guide = $this->getActiveGuide();

        return [
            'exists' => (bool) $guide,
            'file_name' => $guide?->file_name,
            'uploaded_by' => $guide?->uploaded_by,
            'created_at' => optional($guide?->created_at)->format('d M Y H:i'),
            'updated_at' => optional($guide?->updated_at)->format('d M Y H:i'),
            'preview_url' => $guide ? route('billing-guide.preview') : null,
            'open_url' => $guide ? route('billing-guide.preview') : null,
            'download_url' => $guide && $canDownload ? route('billing-guide.download') : null,
        ];
    }

    public function upload(UploadedFile $file, int $uploadedBy): BillingPaymentGuide
    {
        $this->ensureTable();
        SafeDocumentUpload::validatePdfOrFail($file, 'guide_file');

        $guide = BillingPaymentGuide::query()
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if ($guide?->file_path && Storage::disk(self::DISK)->exists($guide->file_path)) {
            Storage::disk(self::DISK)->delete($guide->file_path);
        }

        $storedName = 'panduan_pembayaran_kode_billing_' . now()->format('Ymd_His') . '.pdf';
        $path = $file->storeAs('billing-guides', $storedName, self::DISK);

        if ($guide) {
            $guide->update([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'uploaded_by' => $uploadedBy,
            ]);

            return $guide->fresh();
        }

        return BillingPaymentGuide::query()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'uploaded_by' => $uploadedBy,
        ]);
    }

    public function deleteActiveGuide(): void
    {
        $this->ensureTable();

        $guide = BillingPaymentGuide::query()
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if (!$guide) {
            abort(404, 'Panduan pembayaran belum tersedia.');
        }

        if ($guide->file_path && Storage::disk(self::DISK)->exists($guide->file_path)) {
            Storage::disk(self::DISK)->delete($guide->file_path);
        }

        $guide->delete();
    }

    public function previewResponse()
    {
        $guide = $this->getExistingGuideOrFail();
        $fullPath = Storage::disk(self::DISK)->path($guide->file_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $guide->file_name . '"',
        ]);
    }

    public function downloadResponse()
    {
        $guide = $this->getExistingGuideOrFail();
        $fullPath = Storage::disk(self::DISK)->path($guide->file_path);

        return response()->download($fullPath, $guide->file_name, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function getExistingGuideOrFail(): BillingPaymentGuide
    {
        $this->ensureTable();

        $guide = BillingPaymentGuide::query()
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if (!$guide || !$guide->file_path || !Storage::disk(self::DISK)->exists($guide->file_path)) {
            abort(404, 'Panduan pembayaran belum tersedia.');
        }

        return $guide;
    }

    private function ensureTable(): void
    {
        if (!Schema::hasTable('billing_payment_guides')) {
            abort(500, 'Struktur database panduan pembayaran belum tersedia. Jalankan migrate terbaru.');
        }
    }
}
