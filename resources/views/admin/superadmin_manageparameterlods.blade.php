@extends('layouts.app_admin')

@push('styles')
<style>
    .lod-table {
        margin: 0;
        width: 100% !important;
        min-width: 1080px;
        table-layout: auto;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
    }

    .lod-table thead th {
        background: #f4f8fc;
        color: #5e7692;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-size: 0.62rem;
        font-weight: 700;
        border-top: 1px solid #e3edf7;
        border-bottom: 1px solid #e3edf7;
        border-right: 1px solid #edf2f8;
        padding: 0.88rem 0.85rem;
        white-space: nowrap;
        vertical-align: middle;
    }

    .lod-table thead th:first-child {
        border-left: 1px solid #e3edf7;
    }

    .lod-table thead th:last-child {
        border-right: 0;
    }

    .lod-table tbody td {
        padding: 0.88rem 0.85rem;
        border-color: #edf2f8;
        vertical-align: top;
        background: #fff;
        font-size: 0.8rem;
        color: #213d5d;
        border-right: 1px solid #edf2f8;
        border-bottom: 1px solid #edf2f8;
    }

    .lod-table tbody td:last-child {
        border-right: 0;
    }

    .lod-table tbody td:first-child {
        border-left: 1px solid #edf2f8;
    }

    .lod-table .lod-parameter-cell,
    .lod-table .lod-category-cell {
        white-space: normal;
        word-break: break-word;
        line-height: 1.45;
    }

    .lod-table .lod-parameter-cell {
        font-weight: 600;
        color: #0f2f53;
    }

    .lod-table .lod-category-cell {
        color: #49637f;
    }

    .lod-table .lod-value-cell {
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .lod-table .lod-status-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.34em 0.52em;
        border-radius: 999px;
    }

    .lod-table .lod-action-btn {
        min-width: 34px;
        height: 32px;
        padding: 0.2rem 0.5rem !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.45rem;
        font-size: 0.82rem;
    }

    .lod-table .lod-action-wrap {
        min-width: 86px;
    }

    .lod-card .dataTables_wrapper .dataTables_filter input,
    .lod-card .dataTables_wrapper .dataTables_length select {
        min-height: 38px;
        border-radius: 0.5rem;
    }

    .lod-card .dataTables_wrapper .row:first-child {
        margin-bottom: 0.75rem;
    }

    .lod-card .dataTables_wrapper .row:last-child {
        margin-top: 0.75rem;
        align-items: center;
    }

    .lod-card .dataTables_wrapper .row:last-child > div:last-child {
        display: flex;
        justify-content: flex-end;
    }

    .lod-card .dataTables_wrapper .dataTables_paginate {
        width: 100%;
        display: flex;
        justify-content: flex-end;
    }

    .lod-card .dataTables_wrapper .dt-layout-row:last-child {
        margin-top: 0.75rem;
        align-items: center;
    }

    .lod-card .dataTables_wrapper .dt-layout-row:last-child .dt-layout-end {
        margin-left: auto;
        display: flex;
        justify-content: flex-end;
    }

    .lod-card .dataTables_wrapper .dt-paging {
        width: 100%;
        display: flex;
        justify-content: flex-end;
    }

    .lod-card .dataTables_wrapper .pagination {
        margin-bottom: 0;
    }

    .lod-card .dataTables_wrapper .page-link {
        font-size: 0.82rem;
        padding: 0.42rem 0.78rem;
        min-height: 34px;
        line-height: 1.15;
    }

    .lod-card .dataTables_wrapper .page-item:first-child .page-link,
    .lod-card .dataTables_wrapper .page-item:last-child .page-link {
        padding-left: 0.68rem;
        padding-right: 0.68rem;
    }

    .lod-card .dataTables_wrapper .dt-scroll {
        border: 1px solid #e3edf7;
        border-radius: 0.85rem;
        overflow: hidden;
        background: #fff;
    }

    .lod-card .dataTables_wrapper .dt-scroll-head,
    .lod-card .dataTables_wrapper .dt-scroll-body {
        border: 0 !important;
    }

    .lod-card .dataTables_wrapper .dt-scroll-head table,
    .lod-card .dataTables_wrapper .dt-scroll-body table {
        margin: 0 !important;
    }

    .lod-filter-toolbar {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
        margin-bottom: 0.85rem;
    }

    .lod-filter-label {
        font-size: 0.88rem;
        font-weight: 600;
        color: #4b5d70;
        margin: 0;
    }

    .lod-filter-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .lod-filter-btn {
        border: 1px solid #cfd8e3;
        background: #fff;
        color: #15406A;
        border-radius: 999px;
        padding: 0.38rem 0.8rem;
        font-size: 0.86rem;
        line-height: 1.2;
        font-weight: 500;
        transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    .lod-filter-btn:hover {
        background: #edf4fb;
        border-color: #15406A;
        color: #15406A;
    }

    .lod-filter-btn.is-active {
        background: #15406A;
        border-color: #15406A;
        color: #fff;
    }

    #lodModal .modal-content {
        max-height: calc(100vh - 2rem);
    }

    #lodModal .modal-body {
        max-height: calc(100vh - 11rem);
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    #lodModal .modal-title {
        font-size: 1.05rem;
        font-weight: 600;
    }

    #lodModal .form-label,
    #lodModal h6 {
        font-size: 0.86rem;
        margin-bottom: 0.3rem;
    }

    #lodModal .form-control,
    #lodModal .form-select,
    #lodModal textarea {
        font-size: 0.92rem;
        padding-top: 0.45rem;
        padding-bottom: 0.45rem;
    }

    #lodModal .modal-body .row.g-3 {
        --bs-gutter-y: 0.75rem;
    }

    #lodModal .border.rounded.p-3 {
        padding: 0.85rem !important;
    }

    #lodModal h6.fw-semibold {
        font-size: 0.95rem;
        margin-bottom: 0.65rem !important;
    }

    #lodModal [data-debu-only] {
        display: none;
    }

    @media (max-width: 767.98px) {
        .lod-filter-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content_admin')
@php
    $formatLod = function ($value, int $maxDecimals = 4): string {
        if ($value === null || $value === '') {
            return '-';
        }

        $number = (float) $value;
        $formatted = number_format($number, $maxDecimals, ',', '.');
        $formatted = rtrim(rtrim($formatted, '0'), ',');

        return str_contains($formatted, ',') ? $formatted : ($formatted . ',0');
    };

    $orderedParameters = collect($parameters)
        ->sortBy(function ($parameter) {
            $category = strtolower((string) ($parameter->category?->name ?? ''));
            if (str_contains($category, 'ambien')) {
                return '1-' . strtolower((string) $parameter->name);
            }
            if (str_contains($category, 'lingkungan kerja')) {
                return '2-' . strtolower((string) $parameter->name);
            }
            return '3-' . $category . '-' . strtolower((string) $parameter->name);
        })
        ->values();

    $parameterGroups = $orderedParameters->groupBy(function ($parameter) {
        return $parameter->category?->name ?? 'Lainnya';
    });

    $lodCategories = $parameterGroups->keys()
        ->reject(function ($categoryName) {
            $normalized = strtolower(trim((string) $categoryName));
            return in_array($normalized, ['kesehatan', 'pelatihan'], true);
        })
        ->values();
@endphp
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Kelola LOD Parameter</h5>
        <small class="text-muted">Master LOD per parameter. Implementasi awal dipakai untuk SO2 ambien.</small>
    </div>
    <button class="btn text-white" style="background-color:#15406A; border-color:#15406A;" data-bs-toggle="modal" data-bs-target="#lodModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i> Tambah LOD
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
@endif

<div class="card shadow-sm lod-card">
    <div class="card-body">
        <div class="lod-filter-toolbar">
            <p class="lod-filter-label">Filter kategori:</p>
            <div class="lod-filter-buttons" id="lodCategoryFilters">
                <button type="button" class="btn lod-filter-btn is-active" data-category="">
                    Semua
                </button>
                @foreach($lodCategories as $categoryName)
                    <button type="button" class="btn lod-filter-btn" data-category="{{ $categoryName }}">
                        {{ $categoryName }}
                    </button>
                @endforeach
            </div>
        </div>
        <div>
            <table class="lod-table" id="lodTable">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Kategori</th>
                        <th class="text-end">Kons</th>
                        <th class="text-end">Vol</th>
                        <th class="text-end">Waktu</th>
                        <th class="text-end">FR</th>
                        <th class="text-end">Sk</th>
                        <th class="text-end">P</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lods as $row)
                        <tr>
                            <td class="lod-parameter-cell">{{ $row->serviceParameter?->name ?? '-' }}</td>
                            <td class="lod-category-cell">{{ $row->serviceParameter?->category?->name ?? '-' }}</td>
                            <td class="text-end lod-value-cell">{{ $formatLod($row->kons, 4) }}</td>
                            <td class="text-end lod-value-cell">{{ $formatLod($row->vol, 4) }}</td>
                            <td class="text-end lod-value-cell">{{ $formatLod($row->waktu, 2) }}</td>
                            <td class="text-end lod-value-cell">{{ $formatLod($row->fr, 4) }}</td>
                            <td class="text-end lod-value-cell">{{ $formatLod($row->sk, 4) }}</td>
                            <td class="text-end lod-value-cell">{{ $formatLod($row->pm, 2) }}</td>
                            <td class="text-center">
                                @if($row->is_active)
                                    <span class="badge bg-success lod-status-badge">Aktif</span>
                                @else
                                    <span class="badge bg-secondary lod-status-badge">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-nowrap lod-action-wrap">
                                    <button
                                        class="btn btn-sm px-3 text-white lod-action-btn"
                                        style="background-color:#15406A; border-color:#15406A;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#lodModal"
                                        data-mode="edit"
                                        data-id="{{ $row->id }}"
                                        data-service-parameter-id="{{ $row->service_parameter_id }}"
                                        data-parameter-name="{{ strtolower((string) ($row->serviceParameter?->name ?? '')) }}"
                                        data-kons="{{ $row->kons }}"
                                        data-vol="{{ $row->vol }}"
                                        data-waktu="{{ $row->waktu }}"
                                        data-fr="{{ $row->fr }}"
                                        data-sk="{{ $row->sk }}"
                                        data-pm="{{ $row->pm }}"
                                        data-status="{{ $row->is_active ? 1 : 0 }}"
                                        data-is-active="{{ $row->is_active ? 1 : 0 }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('superadmin.parameter-lods.destroy', $row) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="confirm" value="1">
                                        <button type="button" class="btn btn-sm px-3 btn-delete text-white lod-action-btn" style="background-color:#dc3545; border-color:#dc3545;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">Belum ada data LOD parameter</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="lodModal" tabindex="-1" aria-labelledby="lodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lodModalLabel">LOD Parameter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="lodForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Parameter</label>
                            <select class="form-select" name="service_parameter_id" required>
                                <option value="" disabled selected>Pilih parameter</option>
                                @foreach($parameterGroups as $categoryName => $groupedParameters)
                                    <optgroup label="{{ $categoryName }}">
                                        @foreach($groupedParameters as $parameter)
                                            <option value="{{ $parameter->id }}" data-parameter-name="{{ strtolower((string) $parameter->name) }}">
                                                {{ $parameter->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="is_active" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="border rounded p-3 bg-light-subtle">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-semibold mb-0">Nilai LOD</h6>
                                    <small class="text-muted d-none" data-debu-only>Mode Debu</small>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-3" data-field-wrap="kons">
                                        <label class="form-label" data-field-label="kons">Konsentrasi</label>
                                        <input type="text" class="form-control" name="kons" inputmode="decimal" data-lod-number data-max-decimals="4" data-default-decimals="4" data-debu-decimals="4" required>
                                    </div>
                                    <div class="col-md-3" data-field-wrap="vol">
                                        <label class="form-label" data-field-label="vol">Volume</label>
                                        <input type="text" class="form-control" name="vol" inputmode="decimal" data-lod-number data-max-decimals="4" data-default-decimals="4" data-debu-decimals="1" required>
                                    </div>
                                    <div class="col-md-3" data-field-wrap="waktu">
                                        <label class="form-label" data-field-label="waktu">Waktu</label>
                                        <input type="text" class="form-control" name="waktu" inputmode="decimal" data-lod-number data-max-decimals="2" data-default-decimals="2" data-debu-decimals="0" required>
                                    </div>
                                    <div class="col-md-3" data-field-wrap="fr">
                                        <label class="form-label" data-field-label="fr">FR</label>
                                        <input type="text" class="form-control" name="fr" inputmode="decimal" data-lod-number data-max-decimals="4" data-default-decimals="4" data-debu-decimals="2" required>
                                    </div>
                                    <div class="col-md-3" data-field-wrap="sk">
                                        <label class="form-label" data-field-label="sk">Sk / Suhu</label>
                                        <input type="text" class="form-control" name="sk" inputmode="decimal" data-lod-number data-max-decimals="4" data-default-decimals="4" data-debu-decimals="1" required>
                                    </div>
                                    <div class="col-md-3" data-field-wrap="pm">
                                        <label class="form-label" data-field-label="pm">P / Tekanan</label>
                                        <input type="text" class="form-control" name="pm" inputmode="decimal" data-lod-number data-max-decimals="2" data-default-decimals="2" data-debu-decimals="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    const lodModal = document.getElementById('lodModal');
    const lodForm = document.getElementById('lodForm');
    const lodMethodInput = lodForm.querySelector('input[name="_method"]');
    const lodModalTitle = document.getElementById('lodModalLabel');
    const lodNumberInputs = Array.from(lodForm.querySelectorAll('[data-lod-number]'));
    const parameterSelect = lodForm.querySelector('[name="service_parameter_id"]');
    const volWrap = lodForm.querySelector('[data-field-wrap="vol"]');
    const volInput = lodForm.querySelector('[name="vol"]');
    const konsLabel = lodForm.querySelector('[data-field-label="kons"]');
    const waktuLabel = lodForm.querySelector('[data-field-label="waktu"]');
    const debuOnlyNodes = lodForm.querySelectorAll('[data-debu-only]');

    const normalizeParameterText = (text) => String(text || '').toLowerCase();
    const isDebuParameterText = (text) => normalizeParameterText(text).includes('debu');
    const usesVolumeForDebuParameterText = (text) => {
        const normalized = normalizeParameterText(text);
        return normalized.includes('debu logam') && normalized.includes('aas');
    };

    const updateDecimalMode = (isDebuMode) => {
        lodNumberInputs.forEach((input) => {
            const key = isDebuMode ? 'data-debu-decimals' : 'data-default-decimals';
            const decimals = Number(input.getAttribute(key) || input.getAttribute('data-max-decimals') || '4');
            input.setAttribute('data-max-decimals', String(decimals));
        });
    };

    const applyFieldMode = (isDebuMode, keepVolumeField = false) => {
        if (volWrap) {
            volWrap.classList.toggle('d-none', isDebuMode && !keepVolumeField);
        }
        if (volInput) {
            volInput.required = !isDebuMode || keepVolumeField;
            if (isDebuMode && !keepVolumeField && !String(volInput.value || '').trim()) {
                volInput.value = '0,0';
            }
        }
        if (konsLabel) {
            konsLabel.textContent = isDebuMode ? 'Berat Debu (Gram)' : 'Konsentrasi';
        }
        if (waktuLabel) {
            waktuLabel.textContent = 'Waktu';
        }
        debuOnlyNodes.forEach((node) => node.classList.toggle('d-none', !isDebuMode));
        updateDecimalMode(isDebuMode);
    };

    const syncModeFromSelectedParameter = () => {
        const selected = parameterSelect?.selectedOptions?.[0];
        const paramName = selected?.getAttribute('data-parameter-name') || selected?.textContent || '';
        const isDebuMode = isDebuParameterText(paramName);
        const keepVolumeField = usesVolumeForDebuParameterText(paramName);
        applyFieldMode(isDebuMode, keepVolumeField);
        return isDebuMode;
    };

    const toNumber = (value) => {
        const raw = String(value || '').trim();
        if (!raw) return null;

        let normalized = raw.replace(/\s+/g, '');
        // Jika ada koma, anggap format Indonesia: 1.234,56
        if (normalized.includes(',')) {
            normalized = normalized.replace(/\./g, '').replace(',', '.');
        } else {
            // Jika tidak ada koma, pertahankan titik sebagai desimal (format backend: 1234.56)
            normalized = normalized.replace(/,/g, '');
        }

        normalized = normalized.replace(/[^0-9.-]/g, '');
        if (!normalized || normalized === '-' || normalized === '.') return null;
        const num = Number(normalized);
        return Number.isFinite(num) ? num : null;
    };

    const toBackendNumber = (num, maxDecimals) => {
        const fixed = num.toFixed(maxDecimals);
        let out = maxDecimals > 0 ? fixed.replace(/\.?0+$/, '') : fixed;
        if (out === '-0') out = '0';
        return out;
    };

    const formatLocaleNumber = (value, maxDecimals = 4) => {
        const num = typeof value === 'number' ? value : toNumber(value);
        if (num === null) return '';
        const fixed = num.toFixed(maxDecimals);
        let out = maxDecimals > 0 ? fixed.replace(/\.?0+$/, '') : fixed;
        if (!out.includes('.')) out += '.0';
        let [intPart, decPart] = out.split('.');
        intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return decPart ? `${intPart},${decPart}` : `${intPart},0`;
    };

    $(function () {
        const lodTable = $('#lodTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [9] },
                { className: 'text-end', targets: [2, 3, 4, 5, 6, 7] },
                { className: 'text-center', targets: [8, 9] },
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
            }
        });

        const lodCategoryFilters = document.getElementById('lodCategoryFilters');
        lodCategoryFilters?.querySelectorAll('[data-category]').forEach((button) => {
            button.addEventListener('click', () => {
                const selectedCategory = (button.getAttribute('data-category') || '').trim();

                lodCategoryFilters.querySelectorAll('.lod-filter-btn').forEach((item) => {
                    item.classList.remove('is-active');
                });
                button.classList.add('is-active');

                if (!selectedCategory) {
                    lodTable.column(1).search('').draw();
                    return;
                }

                lodTable.column(1).search(selectedCategory, false, true).draw();
            });
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const formEl = btn.closest('form');
                Swal.fire({
                    title: 'Hapus data?',
                    text: 'Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formEl.submit();
                    }
                });
            });
        });
    });

    lodModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';
        const fieldNames = [
            'service_parameter_id', 'kons', 'vol', 'waktu', 'fr', 'sk', 'pm',
            'is_active'
        ];

        if (mode === 'edit') {
            lodModalTitle.textContent = 'Edit LOD Parameter';
            lodForm.action = "{{ route('superadmin.parameter-lods.store') }}/" + button.getAttribute('data-id');
            lodMethodInput.value = 'PUT';

            fieldNames.forEach((name) => {
                const input = lodForm.querySelector(`[name="${name}"]`);
                if (!input) return;
                const dataName = 'data-' + name.replaceAll('_', '-');
                const fallback = name === 'is_active' ? button.getAttribute('data-status') : '';
                input.value = button.getAttribute(dataName) ?? fallback ?? '';
            });

            const parameterName = button?.getAttribute('data-parameter-name') || '';
            const isDebuMode = isDebuParameterText(parameterName);
            const keepVolumeField = usesVolumeForDebuParameterText(parameterName);
            applyFieldMode(isDebuMode, keepVolumeField);
            lodNumberInputs.forEach((input) => {
                const maxDecimals = Number(input.getAttribute('data-max-decimals') || '4');
                input.value = formatLocaleNumber(input.value, maxDecimals);
            });
        } else {
            lodModalTitle.textContent = 'Tambah LOD Parameter';
            lodForm.action = "{{ route('superadmin.parameter-lods.store') }}";
            lodMethodInput.value = 'POST';

            fieldNames.forEach((name) => {
                const input = lodForm.querySelector(`[name="${name}"]`);
                if (!input) return;
                input.value = name === 'is_active' ? '1' : '';
            });
            syncModeFromSelectedParameter();
        }
    });

    parameterSelect?.addEventListener('change', () => {
        const isDebuMode = syncModeFromSelectedParameter();
        lodNumberInputs.forEach((input) => {
            if (!String(input.value || '').trim()) return;
            const maxDecimals = Number(input.getAttribute('data-max-decimals') || '4');
            input.value = formatLocaleNumber(input.value, maxDecimals);
        });
        if (isDebuMode && volInput && !String(volInput.value || '').trim()) {
            volInput.value = '0,0';
        }
    });

    lodNumberInputs.forEach((input) => {
        input.addEventListener('blur', () => {
            const maxDecimals = Number(input.getAttribute('data-max-decimals') || '4');
            if (!String(input.value || '').trim()) return;
            const num = toNumber(input.value);
            if (num === null) return;
            input.value = formatLocaleNumber(num, maxDecimals);
        });
    });

    lodForm.addEventListener('submit', (event) => {
        for (const input of lodNumberInputs) {
            const raw = String(input.value || '').trim();
            if (!raw) continue;
            const num = toNumber(raw);
            if (num === null) {
                event.preventDefault();
                input.focus();
                Swal.fire({
                    icon: 'warning',
                    title: 'Format angka tidak valid',
                    text: 'Gunakan format angka dengan koma sebagai desimal, contoh: 25,0',
                });
                return;
            }

            const maxDecimals = Number(input.getAttribute('data-max-decimals') || '4');
            input.value = toBackendNumber(num, maxDecimals);
        }
    });
</script>
@endpush
@endsection
