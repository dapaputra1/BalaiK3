@php
    $paymentGuide = $paymentGuide ?? [];
    $canUploadPaymentGuide = $canUploadPaymentGuide ?? false;
    $paymentGuideUploadUrl = $paymentGuideUploadUrl ?? '';
    $paymentGuideDeleteUrl = $paymentGuideDeleteUrl ?? '';
    $paymentGuideOpenUrl = $paymentGuide['open_url'] ?? '#';
@endphp

<div class="billing-guide-hero p-3 p-lg-4 mb-3" data-payment-guide-card>
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div class="d-flex align-items-start gap-3">
            <div class="billing-guide-icon">
                <i class="bi bi-file-earmark-pdf"></i>
            </div>
            <div>
                <div class="fw-semibold fs-5">Panduan Pembayaran</div>
                @if(!empty($paymentGuide['exists']))
                    <div class="small text-muted">Dokumen aktif saat ini</div>
                    <div class="fw-semibold mt-1" data-payment-guide-file-name>{{ $paymentGuide['file_name'] ?? '-' }}</div>
                    <div class="small text-muted mt-1">
                        Diperbarui: {{ $paymentGuide['updated_at'] ?? '-' }}
                    </div>
                @else
                    <div class="small text-muted mt-1" data-payment-guide-empty-text>Belum ada panduan pembayaran</div>
                    <div class="small text-muted mt-1">Upload file PDF agar bisa dipakai bersama pada seluruh flow kode billing.</div>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap align-items-center">
            @if($canUploadPaymentGuide)
                <input
                    type="file"
                    class="d-none"
                    accept="application/pdf,.pdf"
                    data-payment-guide-file-input
                    data-upload-url="{{ $paymentGuideUploadUrl }}"
                >
                <button type="button" class="btn btn-billing-primary btn-sm" data-payment-guide-upload-trigger>
                    <i class="bi bi-upload"></i>
                    {{ !empty($paymentGuide['exists']) ? 'Ganti File' : 'Upload PDF' }}
                </button>
                @if(!empty($paymentGuide['exists']))
                    <button
                        type="button"
                        class="btn btn-outline-danger btn-sm"
                        data-payment-guide-delete-trigger
                        data-delete-url="{{ $paymentGuideDeleteUrl }}"
                    >
                        <i class="bi bi-trash"></i> Hapus File
                    </button>
                @endif
            @endif

            <a
                href="{{ !empty($paymentGuide['exists']) ? $paymentGuideOpenUrl : '#' }}"
                target="_blank"
                rel="noopener"
                class="btn btn-outline-secondary btn-sm {{ !empty($paymentGuide['exists']) ? '' : 'disabled' }}"
                @if(empty($paymentGuide['exists'])) aria-disabled="true" tabindex="-1" @endif
            >
                <i class="bi bi-eye"></i> Lihat Dokumen
            </a>
        </div>
    </div>
</div>
