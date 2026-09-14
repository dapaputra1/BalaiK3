<?php

namespace App\Http\Controllers;

use App\Services\BillingGuideService;
use Illuminate\Http\Request;

class BillingGuideController extends Controller
{
    public function __construct(private BillingGuideService $billingGuideService)
    {
    }

    public function active()
    {
        $this->ensureAuthenticated();

        return response()->json($this->billingGuideService->getViewData(
            auth()->user()?->role === 'user'
        ));
    }

    public function upload(Request $request)
    {
        $this->ensureInternalAccess();

        $data = $request->validate([
            'guide_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ], [
            'guide_file.required' => 'File panduan pembayaran wajib dipilih.',
            'guide_file.uploaded' => 'File panduan pembayaran gagal diupload. Periksa ukuran file dan batas upload server.',
            'guide_file.file' => 'File panduan pembayaran tidak valid.',
            'guide_file.mimes' => 'Format file harus PDF.',
            'guide_file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $existing = $this->billingGuideService->getActiveGuide();
        $guide = $this->billingGuideService->upload($data['guide_file'], (int) auth()->id());
        $message = $existing
            ? 'Panduan pembayaran berhasil diganti.'
            : 'Panduan pembayaran berhasil diupload.';

        return response()->json([
            'message' => $message,
            'data' => [
                'exists' => true,
                'file_name' => $guide->file_name,
                'created_at' => optional($guide->created_at)->format('d M Y H:i'),
                'updated_at' => optional($guide->updated_at)->format('d M Y H:i'),
                'preview_url' => route('billing-guide.preview'),
            ],
        ]);
    }

    public function destroy()
    {
        $this->ensureInternalAccess();

        $this->billingGuideService->deleteActiveGuide();

        return response()->json([
            'message' => 'Panduan pembayaran berhasil dihapus.',
        ]);
    }

    public function preview()
    {
        $this->ensureAuthenticated();

        return $this->billingGuideService->previewResponse();
    }

    public function download()
    {
        $this->ensureAuthenticated();

        return $this->billingGuideService->downloadResponse();
    }

    private function ensureInternalAccess(): void
    {
        if (!in_array(auth()->user()?->role, ['admin', 'superadmin'], true)) {
            abort(403);
        }
    }

    private function ensureAuthenticated(): void
    {
        if (!auth()->check()) {
            abort(403);
        }
    }
}
