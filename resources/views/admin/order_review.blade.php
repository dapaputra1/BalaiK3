@extends('layouts.app_admin')

@section('content_admin')
@php
    $statusMeta = [
        'pending_admin' => ['label' => 'Menunggu Pemeriksaan', 'class' => 'review-status-pending-admin'],
        'pending_customer' => ['label' => 'Menunggu Persetujuan Pelanggan', 'class' => 'review-status-pending-customer'],
        'approved' => ['label' => 'Disetujui Pelanggan', 'class' => 'review-status-approved'],
    ];
    $pendingAdminCount = $permohonans->where('order_review_status', 'pending_admin')->count();
    $pendingCustomerCount = $permohonans->where('order_review_status', 'pending_customer')->count();
    $serviceParametersPayload = $serviceParameters->map(fn ($parameter) => [
        'id' => $parameter->id,
        'name' => $parameter->name,
        'category_id' => $parameter->service_category_id,
        'category' => $parameter->category?->name ?? '-',
        'price' => (float) $parameter->price,
    ])->values();
    $serviceCategoriesPayload = $serviceParametersPayload
        ->map(fn ($parameter) => [
            'id' => $parameter['category_id'],
            'name' => $parameter['category'],
        ])
        ->unique('id')
        ->sortBy('name')
        ->values();
    $serviceParameterLookup = $serviceParameters->keyBy('id');
    $reviewCards = $permohonans->map(function ($permohonan) use ($statusMeta, $serviceParameterLookup) {
        $status = $permohonan->order_review_status ?: 'approved';
        $meta = $statusMeta[$status] ?? ['label' => ucfirst($status), 'class' => 'text-bg-secondary'];
        $currentParameters = $permohonan->parameters->map(fn ($parameter) => [
            'service_parameter_id' => $parameter->service_parameter_id,
            'service_category_id' => $parameter->serviceParameter?->service_category_id,
            'parameter_name' => $parameter->parameter_name,
            'category_name' => $parameter->serviceParameter?->category?->name ?? '-',
            'qty' => (int) $parameter->qty,
            'price' => (float) $parameter->price,
        ])->values();
        $originalParameters = collect($permohonan->order_review_original_parameters ?? [])
            ->map(function ($parameter) use ($serviceParameterLookup) {
                $serviceParameter = $serviceParameterLookup->get((int) ($parameter['service_parameter_id'] ?? 0));

                return [
                    'service_parameter_id' => $parameter['service_parameter_id'] ?? null,
                    'service_category_id' => $serviceParameter?->service_category_id,
                    'parameter_name' => $parameter['parameter_name'] ?? '-',
                    'category_name' => $serviceParameter?->category?->name ?? '-',
                    'qty' => (int) ($parameter['qty'] ?? 0),
                    'price' => (float) ($parameter['price'] ?? 0),
                ];
            })
            ->values();

        return [
            'permohonan' => $permohonan,
            'status' => $status,
            'meta' => $meta,
            'current_parameters' => $currentParameters,
            'original_parameters' => $originalParameters,
            'can_edit' => in_array($status, ['pending_admin', 'pending_customer'], true),
        ];
    });
    $reviewPayloads = $reviewCards
        ->mapWithKeys(fn ($card) => [
            (string) $card['permohonan']->id => [
                'original' => $card['original_parameters']->values()->all(),
                'current' => $card['current_parameters']->values()->all(),
            ],
        ])
        ->all();
@endphp

@include('admin.partials.workflow_header', [
    'title' => 'Verifikasi Pesanan',
    'subtitle' => 'Periksa parameter dan jumlah sebelum pesanan diteruskan ke disposisi.',
    'total' => $pendingAdminCount,
])

<style>
    .order-review-arrival-time {
        font-size: 11px;
        line-height: 1.2;
        color: #6c757d;
        white-space: nowrap;
    }
    .order-review-arrival-time i {
        font-size: 11px;
        margin-right: 4px;
    }
    .order-review-header-content {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0;
        position: relative;
        padding-right: 190px;
    }
    .order-review-order-code {
        font-size: 15px;
        font-weight: 700;
        line-height: 1.2;
        color: #fff;
    }
    .order-review-company-name {
        font-size: 13px;
        font-weight: 400;
        line-height: 1.2;
        color: rgba(255,255,255,.92);
        margin-top: 2px;
    }
    .order-review-meta-row {
        position: absolute;
        right: 34px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }
    .order-review-status-badge {
        font-size: 10px;
        line-height: 1;
        font-weight: 700;
        color: #fff;
        border-radius: 999px;
        padding: 4px 8px;
        white-space: nowrap;
    }
    .review-status-approved.order-review-status-badge {
        background: #198754;
    }
    .review-status-pending-admin.order-review-status-badge {
        background: #7b8794;
    }
    .review-status-pending-customer.order-review-status-badge {
        background: #22c7ee;
        color: #0f2f53;
    }
    #orderReviewAccordion .accordion-button {
        background-color: #15406a;
        color: #fff;
    }
    #orderReviewAccordion .accordion-button:not(.collapsed) {
        background-color: #15406a;
        color: #fff;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
    }
    #orderReviewAccordion .accordion-button::after {
        filter: brightness(0) invert(1);
    }
    #orderReviewAccordion .accordion-button .text-muted,
    #orderReviewAccordion .accordion-button .order-review-arrival-time,
    #orderReviewAccordion .accordion-button .order-review-arrival-time span,
    #orderReviewAccordion .accordion-button .order-review-arrival-time i {
        color: #fff !important;
    }
    #orderReviewAccordion .order-review-meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .9rem;
        margin-bottom: 1rem;
    }
    #orderReviewAccordion .order-review-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }
    #orderReviewAccordion .order-review-actions .btn {
        flex: 1 1 220px;
    }
    #orderReviewAccordion {
        gap: 1rem !important;
    }
    .swal2-container.order-review-swal {
        z-index: 30000 !important;
    }
    #orderReviewAccordion .accordion-item {
        margin-bottom: 0 !important;
    }
    @media (max-width: 991.98px) {
        #orderReviewAccordion .order-review-meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 575.98px) {
        .order-review-header-content {
            padding-right: 0;
        }
        .order-review-meta-row {
            position: static;
            transform: none;
            margin-top: 4px;
            align-items: flex-start;
        }
        #orderReviewAccordion .order-review-meta {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <button type="button" class="btn btn-outline-primary btn-sm active" data-review-filter="all">
            Semua <span class="badge text-bg-light ms-1">{{ $permohonans->count() }}</span>
        </button>
        <button type="button" class="btn btn-outline-warning btn-sm" data-review-filter="pending_admin">
            Perlu Diperiksa <span class="badge text-bg-light ms-1">{{ $pendingAdminCount }}</span>
        </button>
        <button type="button" class="btn btn-outline-info btn-sm" data-review-filter="pending_customer">
            Menunggu Pelanggan <span class="badge text-bg-light ms-1">{{ $pendingCustomerCount }}</span>
        </button>
        <div class="ms-auto" style="min-width: 260px;">
            <input type="search" class="form-control form-control-sm" placeholder="Cari kode atau perusahaan..." data-review-search>
        </div>
    </div>
</div>

<div class="accordion d-flex flex-column gap-3" id="orderReviewAccordion" data-review-list>
    @forelse($reviewCards as $card)
        @php
            $permohonan = $card['permohonan'];
            $status = $card['status'];
            $meta = $card['meta'];
            $currentParameters = $card['current_parameters'];
            $canEdit = $card['can_edit'];
            $accordionId = 'order-review-'.$permohonan->id;
            $createdAt = optional($permohonan->created_at);
            $relativeCreatedAt = $createdAt?->locale('id')->diffForHumans() ?? '-';
            $fullCreatedAt = $createdAt?->translatedFormat('d M Y H:i') ?? '-';
        @endphp
        <div
            class="accordion-item border-0 shadow-sm rounded-4 mb-3 overflow-hidden"
            data-review-card
            data-status="{{ $status }}"
            data-search="{{ strtolower(($permohonan->kode ?? '') . ' ' . ($permohonan->company?->company_name ?? '')) }}"
        >
            <h2 class="accordion-header" id="heading-{{ $accordionId }}">
                <button
                    class="accordion-button collapsed fw-semibold"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $accordionId }}"
                    aria-expanded="false"
                    aria-controls="{{ $accordionId }}"
                >
                    <div class="order-review-header-content">
                        <div class="order-review-order-code">{{ $permohonan->kode }}</div>
                        <div class="order-review-company-name">{{ $permohonan->company?->company_name ?? '-' }}</div>
                        <div class="order-review-meta-row">
                            <div class="order-review-arrival-time" title="Tanggal masuk: {{ $fullCreatedAt }}">
                                <i class="bi bi-clock"></i>
                                <span>{{ $relativeCreatedAt }}</span>
                            </div>
                            <div class="order-review-status-badge {{ $meta['class'] }}">{{ $meta['label'] }}</div>
                        </div>
                    </div>
                </button>
            </h2>

            <div
                id="{{ $accordionId }}"
                class="accordion-collapse collapse"
                aria-labelledby="heading-{{ $accordionId }}"
                data-bs-parent="#orderReviewAccordion"
            >
                <div class="accordion-body bg-white">
                        @if($permohonan->order_review_note)
                            <div class="alert alert-light border py-2 px-3 small mb-3">
                                <span class="fw-semibold">Catatan:</span> {{ $permohonan->order_review_note }}
                            </div>
                        @endif

                        <div class="order-review-meta small">
                            <div>
                                <span class="small text-muted">Pelanggan</span>
                                <div class="fw-semibold">{{ $permohonan->user?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="small text-muted">Tanggal Masuk</span>
                                <div class="fw-semibold">{{ $fullCreatedAt }}</div>
                            </div>
                            <div>
                                <span class="small text-muted">Jumlah Parameter</span>
                                <div class="fw-semibold">{{ $currentParameters->count() }} parameter</div>
                            </div>
                            <div>
                                <span class="small text-muted">Pemeriksa</span>
                                <div class="fw-semibold">{{ $permohonan->orderReviewer?->name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="table-responsive border rounded-3 mb-3">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Parameter Saat Ini</th>
                                        <th class="text-center" style="width: 75px;">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($currentParameters as $parameter)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $parameter['parameter_name'] }}</div>
                                                <div class="small text-muted">{{ $parameter['category_name'] }}</div>
                                            </td>
                                            <td class="text-center">{{ $parameter['qty'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border rounded-3 bg-light-subtle px-3 py-2 mb-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <div class="small text-muted">Ringkasan Parameter</div>
                                    <div class="fw-semibold">{{ $currentParameters->count() }} parameter aktif</div>
                                </div>
                                <div class="text-end small text-muted">
                                    Buka detail untuk melihat pesanan awal dan hasil verifikasi.
                                </div>
                            </div>
                        </div>

                        <div class="order-review-actions">
                            <button
                                type="button"
                                class="btn btn-outline-secondary"
                                data-open-review
                                data-modal-mode="detail"
                                data-bs-toggle="modal"
                                data-bs-target="#orderReviewModal"
                                data-id="{{ $permohonan->id }}"
                                data-kode="{{ $permohonan->kode }}"
                                data-company="{{ $permohonan->company?->company_name ?? '-' }}"
                                data-customer="{{ $permohonan->user?->name ?? '-' }}"
                                data-created-at="{{ optional($permohonan->created_at)->format('d M Y H:i') }}"
                                data-reviewer="{{ $permohonan->orderReviewer?->name ?? '-' }}"
                                data-parameter-count="{{ $currentParameters->count() }}"
                                data-status="{{ $status }}"
                                data-status-label="{{ $meta['label'] }}"
                                data-permohonan-id="{{ $permohonan->id }}"
                                data-note="{{ $permohonan->order_review_note ?? '' }}"
                                data-url="{{ route('superadmin.order-review.send', $permohonan) }}"
                                data-approve-url="{{ route('superadmin.order-review.approve', $permohonan) }}"
                            >
                                Lihat Detail
                            </button>
                            @if($canEdit)
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-open-review
                                    data-modal-mode="edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#orderReviewModal"
                                    data-id="{{ $permohonan->id }}"
                                    data-kode="{{ $permohonan->kode }}"
                                    data-company="{{ $permohonan->company?->company_name ?? '-' }}"
                                    data-customer="{{ $permohonan->user?->name ?? '-' }}"
                                    data-created-at="{{ optional($permohonan->created_at)->format('d M Y H:i') }}"
                                    data-reviewer="{{ $permohonan->orderReviewer?->name ?? '-' }}"
                                    data-parameter-count="{{ $currentParameters->count() }}"
                                    data-status="{{ $status }}"
                                    data-status-label="{{ $meta['label'] }}"
                                    data-permohonan-id="{{ $permohonan->id }}"
                                    data-note="{{ $permohonan->order_review_note ?? '' }}"
                                    data-url="{{ route('superadmin.order-review.send', $permohonan) }}"
                                    data-approve-url="{{ route('superadmin.order-review.approve', $permohonan) }}"
                                >
                                    {{ $status === 'pending_customer' ? 'Edit dan Kirim Ulang' : 'Periksa Pesanan' }}
                                </button>
                            @endif
                        </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-5">Belum ada pesanan yang masuk ke verifikasi.</div>
    @endforelse
</div>

<div class="alert alert-light border d-none mt-3" data-review-empty>
    Tidak ada pesanan yang sesuai dengan filter.
</div>

<div class="modal fade" id="orderReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1"><span data-modal-heading>Verifikasi Pesanan</span> <span data-modal-kode></span></h5>
                    <div class="small text-muted" data-modal-company></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded-4 p-3 mb-3 bg-light-subtle">
                    <div class="row g-3 small">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="text-muted">Pelanggan</div>
                            <div class="fw-semibold" data-modal-customer>-</div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="text-muted">Tanggal Masuk</div>
                            <div class="fw-semibold" data-modal-created-at>-</div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="text-muted">Pemeriksa</div>
                            <div class="fw-semibold" data-modal-reviewer>-</div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="text-muted">Jumlah Parameter</div>
                            <div class="fw-semibold" data-modal-parameter-count>-</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-lg-5">
                        <div class="border rounded-4 p-3 h-100">
                            <h6 class="mb-3">Pesanan Awal Pelanggan</h6>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Parameter</th>
                                            <th class="text-center">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody data-original-body></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="border rounded-4 p-3 d-none" data-current-panel>
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <h6 class="mb-1">Parameter Saat Ini</h6>
                                    <div class="small text-muted">Hasil verifikasi terakhir yang tersimpan pada pesanan.</div>
                                </div>
                                <span class="badge text-bg-secondary" data-modal-status-label></span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kategori</th>
                                            <th>Parameter</th>
                                            <th class="text-center" style="width: 80px;">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody data-current-body></tbody>
                                </table>
                            </div>
                            <div class="alert alert-light border py-2 px-3 small mt-3 mb-0 d-none" data-note-preview-wrap>
                                <span class="fw-semibold">Catatan:</span> <span data-note-preview></span>
                            </div>
                        </div>

                        <div class="border rounded-4 p-3" data-edit-panel>
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <h6 class="mb-1">Hasil Verifikasi</h6>
                                    <div class="small text-muted">Nama parameter mengikuti master layanan agar data tetap sinkron.</div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-add-row>
                                    <i class="bi bi-plus-lg"></i> Tambah Parameter
                                </button>
                            </div>
                            <div data-edit-rows></div>
                            <label class="form-label small fw-semibold mt-2">Catatan untuk pelanggan</label>
                            <textarea class="form-control" rows="3" maxlength="2000" data-review-note placeholder="Jelaskan perubahan bila diperlukan..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" data-send-review>
                    Kirim Perbaikan ke Pelanggan
                </button>
                 <button type="button" class="btn btn-success" data-approve-review>
                    Langsung Verifikasi ke Disposisi
                </button>
            </div>
        </div>
    </div>
</div>

<template id="orderReviewRowTemplate">
    <div class="row g-2 align-items-end mb-2" data-edit-row>
        <div class="col-12 col-md-4">
            <label class="form-label small mb-1">Kategori</label>
            <select class="form-select form-select-sm" data-category-select></select>
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small mb-1">Parameter</label>
            <select class="form-select form-select-sm" data-parameter-select></select>
        </div>
        <div class="col-8 col-md-2">
            <label class="form-label small mb-1">Jumlah</label>
            <input type="number" class="form-control form-control-sm" min="1" max="9999" value="1" data-qty-input>
        </div>
        <div class="col-4 col-md-2">
            <button type="button" class="btn btn-outline-danger btn-sm w-100" data-remove-row>Hapus</button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const categories = @json($serviceCategoriesPayload);
    const parameters = @json($serviceParametersPayload);
    const reviewPayloads = @json($reviewPayloads);
    const modal = document.getElementById('orderReviewModal');
    const rowsContainer = modal?.querySelector('[data-edit-rows]');
    const originalBody = modal?.querySelector('[data-original-body]');
    const currentBody = modal?.querySelector('[data-current-body]');
    const noteInput = modal?.querySelector('[data-review-note]');
    const sendButton = modal?.querySelector('[data-send-review]');
    const approveButton = modal?.querySelector('[data-approve-review]');
    const addButton = modal?.querySelector('[data-add-row]');
    const currentPanel = modal?.querySelector('[data-current-panel]');
    const editPanel = modal?.querySelector('[data-edit-panel]');
    const notePreviewWrap = modal?.querySelector('[data-note-preview-wrap]');
    const notePreview = modal?.querySelector('[data-note-preview]');
    const template = document.getElementById('orderReviewRowTemplate');
    let activeUrl = '';
    let activeApproveUrl = '';
    let activeKode = '';
    let activeOriginal = [];
    let canEdit = false;

    const parameterMap = new Map(parameters.map((parameter) => [String(parameter.id), parameter]));
    const swalOverlayOptions = {
        target: document.body,
        heightAuto: false,
        customClass: {
            container: 'order-review-swal',
        },
    };

    const fillCategoryOptions = (select, selectedCategoryId = '') => {
        select.innerHTML = '';
        select.add(new Option('Pilih kategori', ''));
        categories.forEach((category) => {
            select.add(new Option(
                category.name,
                String(category.id),
                false,
                String(selectedCategoryId) === String(category.id),
            ));
        });
    };

    const fillParameterOptions = (select, categoryId, selectedParameterId = '') => {
        select.innerHTML = '';
        select.add(new Option(categoryId ? 'Pilih parameter' : 'Pilih kategori dulu', ''));

        if (!categoryId) {
            return;
        }

        parameters
            .filter((parameter) => String(parameter.category_id) === String(categoryId))
            .forEach((parameter) => {
                select.add(new Option(
                    parameter.name,
                    String(parameter.id),
                    false,
                    String(selectedParameterId) === String(parameter.id),
                ));
            });
    };

    const addRow = (item = {}) => {
        if (!rowsContainer || !template) return;
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('[data-edit-row]');
        const categorySelect = row.querySelector('[data-category-select]');
        const select = row.querySelector('[data-parameter-select]');
        const qty = row.querySelector('[data-qty-input]');
        const serviceParameterId = item.service_parameter_id || '';
        const mappedParameter = serviceParameterId ? parameterMap.get(String(serviceParameterId)) : null;
        const selectedCategoryId = item.service_category_id || mappedParameter?.category_id || '';

        fillCategoryOptions(categorySelect, selectedCategoryId);
        fillParameterOptions(select, selectedCategoryId, serviceParameterId);

        qty.value = Math.max(1, Number(item.qty) || 1);
        categorySelect.disabled = !canEdit;
        select.disabled = !canEdit;
        if (canEdit && !selectedCategoryId) {
            select.disabled = true;
        }
        qty.disabled = !canEdit;
        row.querySelector('[data-remove-row]').classList.toggle('d-none', !canEdit);
        rowsContainer.appendChild(fragment);
        updateActionState();
    };

    const normalizeSignature = (items = []) => {
        return items
            .map((item) => ({
                service_parameter_id: Number(item.service_parameter_id || 0),
                qty: Number(item.qty || 0),
            }))
            .filter((item) => item.service_parameter_id > 0 && item.qty > 0)
            .sort((a, b) => {
                if (a.service_parameter_id !== b.service_parameter_id) {
                    return a.service_parameter_id - b.service_parameter_id;
                }

                return a.qty - b.qty;
            });
    };

    const signaturesEqual = (left = [], right = []) => {
        const normalizedLeft = normalizeSignature(left);
        const normalizedRight = normalizeSignature(right);

        if (normalizedLeft.length !== normalizedRight.length) {
            return false;
        }

        return normalizedLeft.every((item, index) => {
            const other = normalizedRight[index];
            return item.service_parameter_id === other.service_parameter_id && item.qty === other.qty;
        });
    };

    const getEditedPayload = () => {
        if (!rowsContainer) return [];

        return Array.from(rowsContainer.querySelectorAll('[data-edit-row]')).map((row) => ({
            service_parameter_id: Number(row.querySelector('[data-parameter-select]')?.value || 0),
            qty: Number(row.querySelector('[data-qty-input]')?.value || 0),
        }));
    };

    const updateActionState = () => {
        if (!canEdit) {
            sendButton?.classList.add('d-none');
            approveButton?.classList.add('d-none');
            return;
        }

        const payload = getEditedPayload();
        const hasValidPayload = payload.length > 0
            && payload.every((item) => item.service_parameter_id > 0 && item.qty > 0)
            && new Set(payload.map((item) => item.service_parameter_id)).size === payload.length;
        const unchangedFromOriginal = hasValidPayload && signaturesEqual(payload, activeOriginal);

        sendButton?.classList.remove('d-none');
        approveButton?.classList.remove('d-none');
        sendButton.disabled = !hasValidPayload || unchangedFromOriginal;
        approveButton.disabled = !hasValidPayload || !unchangedFromOriginal || !activeApproveUrl;
        sendButton.title = unchangedFromOriginal
            ? 'Tidak ada perubahan dari pesanan awal pelanggan.'
            : '';
        approveButton.title = unchangedFromOriginal
            ? ''
            : 'Hasil verifikasi berubah. Kirim perbaikan ke pelanggan terlebih dahulu.';
    };

    modal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const status = trigger.dataset.status || '';
        const mode = trigger.dataset.modalMode || 'detail';
        canEdit = mode === 'edit' && ['pending_admin', 'pending_customer'].includes(status);
        activeUrl = trigger.dataset.url || '';
        activeApproveUrl = trigger.dataset.approveUrl || '';
        activeKode = trigger.dataset.kode || '-';
        const payload = reviewPayloads[String(trigger.dataset.permohonanId || '')] || { original: [], current: [] };
        const original = Array.isArray(payload.original) ? payload.original : [];
        const current = Array.isArray(payload.current) ? payload.current : [];
        activeOriginal = original;
        const statusLabel = trigger.dataset.statusLabel || '-';
        const noteValue = trigger.dataset.note || '';

        modal.querySelector('[data-modal-heading]').textContent = canEdit ? 'Verifikasi Pesanan' : 'Rekap Pesanan';
        modal.querySelector('[data-modal-kode]').textContent = trigger.dataset.kode || '';
        modal.querySelector('[data-modal-company]').textContent = trigger.dataset.company || '-';
        modal.querySelector('[data-modal-customer]').textContent = trigger.dataset.customer || '-';
        modal.querySelector('[data-modal-created-at]').textContent = trigger.dataset.createdAt || '-';
        modal.querySelector('[data-modal-reviewer]').textContent = trigger.dataset.reviewer || '-';
        modal.querySelector('[data-modal-parameter-count]').textContent = `${Number(trigger.dataset.parameterCount || 0)} parameter`;
        modal.querySelector('[data-modal-status-label]').textContent = statusLabel;
        noteInput.value = noteValue;
        noteInput.disabled = !canEdit;
        sendButton.classList.toggle('d-none', !canEdit);
        approveButton?.classList.toggle('d-none', !canEdit);
        addButton.classList.toggle('d-none', !canEdit);
        currentPanel?.classList.toggle('d-none', canEdit);
        editPanel?.classList.toggle('d-none', !canEdit);
        notePreviewWrap?.classList.toggle('d-none', !noteValue);
        if (notePreview) {
            notePreview.textContent = noteValue;
        }
        rowsContainer.innerHTML = '';
        originalBody.innerHTML = '';
        if (currentBody) {
            currentBody.innerHTML = '';
        }

        original.forEach((item) => {
            const row = document.createElement('tr');
            const nameCell = document.createElement('td');
            const quantityCell = document.createElement('td');
            const title = document.createElement('div');
            const category = document.createElement('div');
            title.className = 'fw-semibold';
            category.className = 'small text-muted';
            title.textContent = item.parameter_name || '-';
            category.textContent = item.category_name || '-';
            nameCell.append(title, category);
            quantityCell.className = 'text-center';
            quantityCell.textContent = String(Number(item.qty) || 0);
            row.append(nameCell, quantityCell);
            originalBody.appendChild(row);
        });
        if (!originalBody.children.length) {
            originalBody.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-3">Data awal tidak tersedia.</td></tr>';
        }

        current.forEach((item) => {
            if (currentBody) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.category_name || '-'}</td>
                    <td>${item.parameter_name || '-'}</td>
                    <td class="text-center">${Number(item.qty) || 0}</td>
                `;
                currentBody.appendChild(row);
            }
            if (canEdit) {
                addRow(item);
            }
        });
        if (currentBody && !currentBody.children.length) {
            currentBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-3">Belum ada parameter tersimpan.</td></tr>';
        }
        if (!current.length && canEdit) addRow();
        updateActionState();
    });

    addButton?.addEventListener('click', () => addRow());
    rowsContainer?.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-remove-row]');
        if (!removeButton || !canEdit) return;
        removeButton.closest('[data-edit-row]')?.remove();
        updateActionState();
    });

    rowsContainer?.addEventListener('change', (event) => {
        const categorySelect = event.target.closest('[data-category-select]');
        const parameterOrQty = event.target.closest('[data-parameter-select], [data-qty-input]');
        if (!categorySelect && !parameterOrQty) {
            return;
        }

        if (!categorySelect) {
            updateActionState();
            return;
        }

        const row = categorySelect.closest('[data-edit-row]');
        const parameterSelect = row?.querySelector('[data-parameter-select]');
        if (!parameterSelect) return;

        fillParameterOptions(parameterSelect, categorySelect.value, '');
        parameterSelect.disabled = !categorySelect.value || !canEdit;
        updateActionState();
    });
    rowsContainer?.addEventListener('input', (event) => {
        if (event.target.closest('[data-qty-input]')) {
            updateActionState();
        }
    });

    sendButton?.addEventListener('click', async () => {
        const payload = getEditedPayload();

        if (!payload.length || payload.some((item) => !item.service_parameter_id || item.qty < 1)) {
            await showMessage('warning', 'Parameter dan jumlah wajib diisi dengan benar.');
            return;
        }
        if (new Set(payload.map((item) => item.service_parameter_id)).size !== payload.length) {
            await showMessage('warning', 'Parameter yang sama tidak boleh dipilih lebih dari sekali.');
            return;
        }
        if (signaturesEqual(payload, activeOriginal)) {
            await showMessage('warning', 'Tidak ada perubahan dari pesanan awal pelanggan. Gunakan tombol verifikasi langsung ke disposisi.');
            updateActionState();
            return;
        }

        const confirmation = await confirmAction({
            title: 'Kirim Perbaikan ke Pelanggan?',
            text: `Perubahan pesanan ${activeKode} akan dikirim ke pelanggan untuk disetujui terlebih dahulu.`,
            confirmButtonText: 'Ya, Kirim Perbaikan',
            fallbackText: `Kirim perbaikan pesanan ${activeKode} ke pelanggan?`,
        });

        if (!confirmation) return;

        sendButton.disabled = true;
        approveButton.disabled = true;
        try {
            const response = await fetch(activeUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    parameters: payload,
                    note: noteInput.value.trim(),
                }),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                const validationMessage = Object.values(result.errors || {}).flat()[0];
                throw new Error(validationMessage || result.message || 'Gagal mengirim perbaikan pesanan.');
            }
            await showMessage('success', result.message || 'Perbaikan pesanan berhasil dikirim.');
            window.location.reload();
        } catch (error) {
            await showMessage('error', error.message || 'Gagal mengirim perbaikan pesanan.');
            updateActionState();
        }
    });

    approveButton?.addEventListener('click', async () => {
        if (!activeApproveUrl) return;

        const payload = getEditedPayload();
        if (!signaturesEqual(payload, activeOriginal)) {
            await showMessage('warning', 'Hasil verifikasi sudah berubah. Kirim perbaikan ke pelanggan terlebih dahulu.');
            updateActionState();
            return;
        }

        const confirmation = await confirmAction({
            title: 'Verifikasi Langsung?',
            text: `Pesanan ${activeKode} akan diterima dan langsung masuk ke disposisi.`,
            confirmButtonText: 'Ya, Lanjut Disposisi',
            fallbackText: `Verifikasi pesanan ${activeKode} dan lanjut ke disposisi?`,
        });

        if (!confirmation) return;

        approveButton.disabled = true;
        sendButton.disabled = true;
        try {
            const response = await fetch(activeApproveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) {
                const validationMessage = Object.values(result.errors || {}).flat()[0];
                throw new Error(validationMessage || result.message || 'Gagal memverifikasi pesanan.');
            }
            await showMessage('success', result.message || 'Pesanan diteruskan ke disposisi.');
            window.location.href = result.redirect_url || @json(route('superadmin.disposisi.index'));
        } catch (error) {
            await showMessage('error', error.message || 'Gagal memverifikasi pesanan.');
            updateActionState();
        }
    });

    const filterButtons = document.querySelectorAll('[data-review-filter]');
    const searchInput = document.querySelector('[data-review-search]');
    const cards = Array.from(document.querySelectorAll('[data-review-card]'));
    const emptyState = document.querySelector('[data-review-empty]');
    let activeFilter = 'all';

    const applyFilter = () => {
        const keyword = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;
        cards.forEach((card) => {
            const matchesStatus = activeFilter === 'all' || card.dataset.status === activeFilter;
            const matchesSearch = !keyword || (card.dataset.search || '').includes(keyword);
            const show = matchesStatus && matchesSearch;
            card.classList.toggle('d-none', !show);
            if (show) visible += 1;
        });
        emptyState?.classList.toggle('d-none', visible > 0);
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeFilter = button.dataset.reviewFilter || 'all';
            filterButtons.forEach((item) => item.classList.toggle('active', item === button));
            applyFilter();
        });
    });
    searchInput?.addEventListener('input', applyFilter);

    async function showMessage(icon, text) {
        if (window.Swal) {
            return window.Swal.fire({
                ...swalOverlayOptions,
                icon,
                title: icon === 'success' ? 'Berhasil' : (icon === 'warning' ? 'Periksa Data' : 'Gagal'),
                text,
            });
        }
        window.alert(text);
    }

    async function confirmAction({ title, text, confirmButtonText, fallbackText }) {
        if (window.Swal) {
            const result = await window.Swal.fire({
                ...swalOverlayOptions,
                icon: 'question',
                title,
                text,
                showCancelButton: true,
                confirmButtonText,
                cancelButtonText: 'Batal',
            });

            return Boolean(result?.isConfirmed);
        }

        return window.confirm(fallbackText || text || title);
    }
});
</script>
@endsection
