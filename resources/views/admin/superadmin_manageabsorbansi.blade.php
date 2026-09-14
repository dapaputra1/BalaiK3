@extends('layouts.app_admin')

@push('styles')
<style>
    #absorbansiModal .modal-content {
        max-height: calc(100vh - 2rem);
    }

    #absorbansiModal .modal-body {
        max-height: calc(100vh - 11rem);
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    #absorbansiModal .modal-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
        z-index: 2;
    }
</style>
@endpush

@section('content_admin')
@php
    $formatNumber = function ($value, int $maxDecimals = 6): string {
        if ($value === null || $value === '') {
            return '-';
        }

        $formatted = number_format((float) $value, $maxDecimals, ',', '.');
        return rtrim(rtrim($formatted, '0'), ',');
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">Kelola Rumus</h5>
        <small class="text-muted">Kelola rumus aktif untuk NO2, OX, Pb, Cd, Cr, As, Hg, Co, Sb, Tl, Cu, Zn, Benzene, Toluene, dan Xylene tanpa perlu upload ulang Excel. Khusus Pb, Cd, Cr, As, Hg, Co, Sb, Tl, Cu, dan Zn, isikan Nilai Y dan Nilai X sesuai kurva.</small>
    </div>
    <button class="btn text-white" style="background-color:#15406A; border-color:#15406A;" data-bs-toggle="modal" data-bs-target="#absorbansiModal" data-mode="create">
        <i class="bi bi-plus-circle me-1"></i> Tambah Rumus
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-3">{{ $errors->first() }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" id="absorbansiTable">
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th class="text-end">Nilai 1</th>
                        <th class="text-end">Nilai 2</th>
                        <th>Rumus</th>
                        <th>Tanggal Berlaku</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $interceptRaw = (float) $row->intercept;
                            $slopeRaw = (float) $row->slope;
                            $isBtxFormula = in_array($row->parameter_key, ['BENZENE', 'TOLUENE', 'XYLENE'], true);
                            $isCurveFormula = in_array($row->parameter_key, ['PB', 'CD', 'CR', 'AS', 'HG', 'CO', 'SB', 'TL', 'CU', 'ZN'], true);
                            $curveYRaw = $isCurveFormula ? ($interceptRaw != 0.0 ? $interceptRaw : ($slopeRaw != 0.0 ? (1 / $slopeRaw) : 0.0)) : $interceptRaw;
                            $curveXRaw = $isCurveFormula ? ($slopeRaw != 0.0 ? $slopeRaw : ($interceptRaw != 0.0 ? (1 / $interceptRaw) : 0.0)) : $slopeRaw;
                            $formulaLabel = $isBtxFormula
                                ? 'y = ' . $formatNumber($interceptRaw, 12) . ' | x = ' . $formatNumber($slopeRaw, 12) . ' | conc = area * x'
                                : ($isCurveFormula
                                    ? 'y = ' . $formatNumber($curveYRaw, 6) . 'x | x = ' . $formatNumber($curveXRaw, 6) . ' | cons = x * abs'
                                    : 'cons = ' . $formatNumber($interceptRaw, 6) . ' + ' . $formatNumber($slopeRaw, 6) . ' * abs');
                            $value1Label = ($isBtxFormula || $isCurveFormula) ? 'Nilai Y' : 'Nilai Dasar';
                            $value2Label = ($isBtxFormula || $isCurveFormula) ? 'Nilai X' : 'Pengali Absorbansi';
                            $value1Display = $isCurveFormula ? $curveYRaw : $row->intercept;
                            $value2Display = $isCurveFormula ? $curveXRaw : $row->slope;
                        @endphp
                        <tr>
                            <td>{{ $parameterOptions[$row->parameter_key] ?? $row->parameter_key }}</td>
                            <td class="text-end">
                                <div>{{ $formatNumber($value1Display, $isBtxFormula ? 12 : 6) }}</div>
                                <small class="text-muted">{{ $value1Label }}</small>
                            </td>
                            <td class="text-end">
                                <div>{{ $formatNumber($value2Display, $isBtxFormula ? 12 : 6) }}</div>
                                <small class="text-muted">{{ $value2Label }}</small>
                            </td>
                            <td><code>{{ $formulaLabel }}</code></td>
                            <td>{{ optional($row->effective_date)->format('d-m-Y') ?? '-' }}</td>
                            <td>
                                @if($row->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-nowrap">
                                    <button
                                        class="btn btn-sm px-3 text-white"
                                        style="background-color:#15406A; border-color:#15406A;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#absorbansiModal"
                                        data-mode="edit"
                                        data-id="{{ $row->id }}"
                                        data-parameter-key="{{ $row->parameter_key }}"
                                        data-intercept="{{ $isCurveFormula ? $curveYRaw : $row->intercept }}"
                                        data-slope="{{ $isCurveFormula ? $curveXRaw : $row->slope }}"
                                        data-effective-date="{{ optional($row->effective_date)->format('Y-m-d') }}"
                                        data-is-active="{{ $row->is_active ? 1 : 0 }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('superadmin.absorbansi.destroy', $row) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="confirm" value="1">
                                        <button type="button" class="btn btn-sm px-3 btn-delete text-white" style="background-color:#dc3545; border-color:#dc3545;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data rumus.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="absorbansiModal" tabindex="-1" aria-labelledby="absorbansiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="absorbansiModalLabel">Rumus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="absorbansiForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Parameter</label>
                            <select class="form-select" name="parameter_key" required>
                                <option value="" disabled selected>Pilih parameter</option>
                                @foreach($parameterOptions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
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
                        <div class="col-md-6">
                            <label class="form-label" data-label-intercept>Nilai Dasar</label>
                            <input type="number" class="form-control" name="intercept" step="0.000000000001" inputmode="decimal" required>
                            <small class="text-muted" data-help-intercept>Nilai awal saat absorbansi = 0.</small>
                        </div>
                        <div class="col-md-6" data-slope-group>
                            <label class="form-label" data-label-slope>Pengali Absorbansi</label>
                            <input type="number" class="form-control" name="slope" step="0.000000000001" inputmode="decimal" required>
                            <small class="text-muted" data-help-slope>Angka pengali untuk nilai absorbansi.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Berlaku</label>
                            <input type="date" class="form-control" name="effective_date">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Preview Perhitungan</label>
                            <div class="form-control bg-light" data-formula-preview>Konsentrasi = 0 + (0 x Absorbansi)</div>
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
    const absorbansiModal = document.getElementById('absorbansiModal');
    const absorbansiForm = document.getElementById('absorbansiForm');
    const absorbansiMethodInput = absorbansiForm.querySelector('input[name="_method"]');
    const absorbansiModalTitle = document.getElementById('absorbansiModalLabel');
    const interceptInput = absorbansiForm.querySelector('[name="intercept"]');
    const slopeInput = absorbansiForm.querySelector('[name="slope"]');
    const parameterInput = absorbansiForm.querySelector('[name="parameter_key"]');
    const formulaPreview = absorbansiForm.querySelector('[data-formula-preview]');
    const interceptLabel = absorbansiForm.querySelector('[data-label-intercept]');
    const slopeLabel = absorbansiForm.querySelector('[data-label-slope]');
    const interceptHelp = absorbansiForm.querySelector('[data-help-intercept]');
    const slopeHelp = absorbansiForm.querySelector('[data-help-slope]');
    const slopeGroup = absorbansiForm.querySelector('[data-slope-group]');

    const formatDisplayNumber = (value, maxDecimals = 6) => {
        const numericValue = Number(value);
        if (!Number.isFinite(numericValue)) {
            return '0';
        }

        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: maxDecimals,
        }).format(numericValue);
    };

    const updateFieldContext = () => {
        const parameterKey = (parameterInput?.value || '').toUpperCase();
        const isBtxFormula = parameterKey === 'BENZENE' || parameterKey === 'TOLUENE' || parameterKey === 'XYLENE';
        const isCurveFormula = parameterKey === 'PB' || parameterKey === 'CD' || parameterKey === 'CR' || parameterKey === 'AS' || parameterKey === 'HG' || parameterKey === 'CO' || parameterKey === 'SB' || parameterKey === 'TL' || parameterKey === 'CU' || parameterKey === 'ZN';
        const compoundLabel = parameterKey === 'TOLUENE'
            ? 'Toluene'
            : (parameterKey === 'XYLENE' ? 'Xylene' : 'Benzene');

        interceptLabel.textContent = (isBtxFormula || isCurveFormula) ? 'Nilai Y' : 'Nilai Dasar';
        interceptHelp.textContent = isBtxFormula
            ? `Nilai Y acuan kalibrasi ${compoundLabel}.`
            : (isCurveFormula
                ? 'Isi Nilai Y sesuai kurva kalibrasi.'
                : 'Nilai awal saat absorbansi = 0.');

        if (slopeGroup) {
            slopeGroup.classList.toggle('d-none', isBtxFormula);
        }

        if (slopeInput) {
            slopeInput.disabled = isBtxFormula;
            slopeInput.required = !isBtxFormula;
        }

        if (!isBtxFormula) {
            slopeLabel.textContent = isCurveFormula ? 'Nilai X' : 'Pengali Absorbansi';
            slopeHelp.textContent = isCurveFormula
                ? 'Isi Nilai X sesuai kurva kalibrasi.'
                : 'Angka pengali untuk nilai absorbansi.';
        }
    };

    const updatePreview = () => {
        const parameterKey = (parameterInput?.value || '').toUpperCase();
        const intercept = Number(interceptInput?.value || 0);
        const slope = Number(slopeInput?.value || 0);
        const safeIntercept = Number.isFinite(intercept) ? intercept : 0;
        const safeSlope = Number.isFinite(slope) ? slope : 0;
        const activeElement = document.activeElement;

        updateFieldContext();

        if (parameterKey === 'BENZENE' || parameterKey === 'TOLUENE' || parameterKey === 'XYLENE') {
            const xValue = safeIntercept !== 0 ? (1 / safeIntercept) : 0;
            if (slopeInput && activeElement !== slopeInput) {
                slopeInput.value = safeIntercept !== 0 ? xValue.toFixed(12) : '';
            }
            const compoundLabel = parameterKey === 'TOLUENE'
                ? 'Toluene'
                : (parameterKey === 'XYLENE' ? 'Xylene' : 'Benzene');
            formulaPreview.textContent = `${compoundLabel} = L.Area x ${formatDisplayNumber(xValue, 12)} | Y = ${formatDisplayNumber(safeIntercept, 12)}`;
            return;
        }

        if (parameterKey === 'PB' || parameterKey === 'CD' || parameterKey === 'CR' || parameterKey === 'AS' || parameterKey === 'HG' || parameterKey === 'CO' || parameterKey === 'SB' || parameterKey === 'TL' || parameterKey === 'CU' || parameterKey === 'ZN') {
            const derivedXValue = safeIntercept !== 0 ? (1 / safeIntercept) : 0;
            if (slopeInput && activeElement !== slopeInput) {
                slopeInput.value = safeIntercept !== 0 ? derivedXValue.toFixed(3) : '';
            }
            const currentSlope = Number(slopeInput?.value || 0);
            const yValue = safeIntercept !== 0 ? safeIntercept : (currentSlope !== 0 ? (1 / currentSlope) : 0);
            const xValue = currentSlope !== 0 ? currentSlope : derivedXValue;
            formulaPreview.textContent = `Kurva Excel: y = ${formatDisplayNumber(yValue, 6)}x | Sistem: Konsentrasi = ${formatDisplayNumber(xValue, 3)} x Absorbansi`;
            return;
        }

        formulaPreview.textContent = `Konsentrasi = ${formatDisplayNumber(safeIntercept, 6)} + (${formatDisplayNumber(safeSlope, 6)} x Absorbansi)`;
    };

    $(function () {
        $('#absorbansiTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            order: [[0, 'asc'], [4, 'desc']],
            columnDefs: [
                { orderable: false, targets: [6] },
                { className: 'text-end', targets: [1, 2] },
                { className: 'text-center', targets: [6] },
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

        document.querySelectorAll('.btn-delete').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
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

    absorbansiModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';

        if (mode === 'edit') {
            absorbansiModalTitle.textContent = 'Edit Rumus';
            absorbansiForm.action = "{{ route('superadmin.absorbansi.update', ['absorbansi' => '__ID__']) }}".replace('__ID__', button.getAttribute('data-id'));
            absorbansiMethodInput.value = 'PUT';
            absorbansiForm.querySelector('[name="parameter_key"]').value = button.getAttribute('data-parameter-key') || '';
            absorbansiForm.querySelector('[name="intercept"]').value = button.getAttribute('data-intercept') || '';
            absorbansiForm.querySelector('[name="slope"]').value = button.getAttribute('data-slope') || '';
            absorbansiForm.querySelector('[name="effective_date"]').value = button.getAttribute('data-effective-date') || '';
            absorbansiForm.querySelector('[name="is_active"]').value = button.getAttribute('data-is-active') || '1';
        } else {
            absorbansiModalTitle.textContent = 'Tambah Rumus';
            absorbansiForm.action = "{{ route('superadmin.absorbansi.store') }}";
            absorbansiMethodInput.value = 'POST';
            absorbansiForm.reset();
            absorbansiForm.querySelector('[name="is_active"]').value = '1';
        }

        updatePreview();
    });

    interceptInput?.addEventListener('input', updatePreview);
    slopeInput?.addEventListener('input', updatePreview);
    parameterInput?.addEventListener('change', updatePreview);
</script>
@endpush
@endsection
