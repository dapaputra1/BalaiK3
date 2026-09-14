@extends('layouts.app_admin')
@section('body_class', 'superadmin-dashboard-ui')

@section('content_admin')
<style>
    .dashboard-filter-card {
        border: 1px solid #d2dfef;
        background: #ffffff;
        border-radius: 14px;
    }
    .dashboard-filter-head {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .dashboard-filter-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e7f1fd;
        color: #0f3b63;
    }
    .dashboard-filter-title {
        color: #0f3b63;
        font-weight: 700;
        letter-spacing: .1px;
    }
    .dashboard-filter-subtitle {
        color: #5f6f82;
        font-size: 13px;
    }
    .dashboard-filter-description {
        color: #111111;
        font-size: 1.35rem;
        font-weight: 700;
    }
    .dashboard-kpi-icon {
        width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        padding: 0 !important;
    }
    .dashboard-kpi-card {
        transition: transform .18s ease, box-shadow .18s ease;
        transform-origin: center center;
    }
    @media (hover: hover) and (pointer: fine) {
        .dashboard-kpi-card:hover {
            transform: scale(1.01);
            box-shadow: 0 10px 18px rgba(21, 64, 106, 0.14) !important;
        }
    }
    .filter-mode-chip {
        cursor: pointer;
        border: 1px solid #d2dfef;
        border-radius: 999px;
        padding: 6px 12px;
        background: #fff;
        transition: all .2s ease;
        flex-shrink: 0;
    }
    .filter-mode-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: center;
        overflow-x: visible;
        padding-bottom: 2px;
    }
    .filter-controls-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: center;
        gap: 8px 12px;
    }
    .filter-mode-chip .form-check-input {
        margin-top: 0;
        border-color: #b5c7df;
    }
    .filter-mode-chip .form-check-input:checked + span {
        color: #0f3b63;
        font-weight: 600;
    }
    .filter-mode-chip:has(.form-check-input:checked) {
        border-color: #86b7fe;
        background: #eaf3ff;
        box-shadow: 0 0 0 1px #d6e8ff inset;
    }
    .dashboard-filter-input-wrap {
        min-width: 150px;
    }
    .dashboard-filter-input-wrap .form-control,
    .dashboard-filter-input-wrap .form-select {
        border-color: #cfdced;
    }
    .dashboard-filter-actions {
        display: flex;
        gap: 8px;
        padding-bottom: 1px;
    }
    .agenda-tooltip .tooltip-inner {
        text-align: left;
    }
    .agenda-tooltip .tooltip-inner i {
        color: #8fbafc;
    }
    .calendar-today {
        border: 2px solid #86b7fe !important;
        position: relative;
        background: transparent !important;
    }
    .calendar-today .today-chip {
        position: absolute;
        top: 6px;
        right: 6px;
        font-size: 10px;
        line-height: 1;
        padding: 3px 6px;
        border-radius: 999px;
        background: #dff5e1;
        color: #2f6f3f;
        font-weight: 600;
    }
    .status-chart-wrap {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) minmax(180px, 240px);
        gap: 26px;
        align-items: center;
    }
    .status-chart-canvas {
        min-height: 300px;
    }
    .status-chart-legend {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        max-height: 300px;
        overflow: auto;
    }
    .status-chart-legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .status-chart-legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 4px;
        flex: 0 0 12px;
    }
    .status-chart-legend-text {
        font-size: 12px;
        color: #4f5d6b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    @media (max-width: 991.98px) {
        .status-chart-wrap {
            grid-template-columns: 1fr;
        }
        .status-chart-canvas {
            min-height: 260px;
        }
        .status-chart-legend {
            max-height: 160px;
            grid-template-columns: repeat(2, minmax(140px, 1fr));
            gap: 10px 14px;
        }
    }
    body.superadmin-dashboard-ui .dashboard-ui-reveal {
        opacity: 0;
        transform: translateY(18px) scale(0.99);
        transition: opacity .52s ease, transform .62s cubic-bezier(.22,.61,.36,1);
        will-change: opacity, transform;
    }
    body.superadmin-dashboard-ui .dashboard-ui-reveal.dashboard-ui-reveal-left {
        transform: translateX(-18px);
    }
    body.superadmin-dashboard-ui .dashboard-ui-reveal.is-visible {
        opacity: 1;
        transform: translate(0, 0) scale(1);
    }
    body.superadmin-dashboard-ui #sidebar .brand img {
        transform-origin: center center;
    }
    body.superadmin-dashboard-ui #sidebar .brand img.logo-roll {
        animation: superadminLogoRoll .9s cubic-bezier(.22,.61,.36,1);
    }
    @keyframes superadminLogoRoll {
        0% { transform: translateX(0) rotate(0deg); }
        40% { transform: translateX(12px) rotate(360deg); }
        75% { transform: translateX(-4px) rotate(630deg); }
        100% { transform: translateX(0) rotate(720deg); }
    }
    @media (prefers-reduced-motion: reduce) {
        body.superadmin-dashboard-ui .dashboard-ui-reveal {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }
        body.superadmin-dashboard-ui #sidebar .brand img.logo-roll {
            animation: none !important;
        }
    }
</style>
<div class="row g-3 justify-content-center">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 dashboard-kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Total Pengguna</div>
                        <div class="h5 mb-0">{{ number_format($totalUsers ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary rounded-circle dashboard-kpi-icon">
                        <i class="bi bi-people-fill"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 dashboard-kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Petugas Aktif</div>
                        <div class="h5 mb-0">{{ number_format($activeAdmins ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <span class="badge bg-success-subtle text-success rounded-circle dashboard-kpi-icon">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 dashboard-kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Potential Buyer</div>
                        <div class="h5 mb-0">{{ number_format($potentialBuyers ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <span class="badge bg-warning-subtle text-warning rounded-circle dashboard-kpi-icon">
                        <i class="bi bi-cart-check"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card dashboard-filter-card shadow-sm border-0 mb-3 mt-3">
    <div class="card-body">
        <form method="GET" action="{{ url()->current() }}">
            <div class="d-flex flex-column gap-3">
                <div class="dashboard-filter-head">
                    <span class="dashboard-filter-icon">
                        <i class="bi bi-funnel-fill"></i>
                    </span>
                    <div>
                        <div class="dashboard-filter-title">Filter Data Dashboard</div>
                        <div class="dashboard-filter-subtitle">Pilih mode semua data, tahunan, bulanan, atau rentang tanggal berdasarkan tanggal permohonan dibuat.</div>
                    </div>
                </div>
                <div class="filter-controls-row">
                    <div class="filter-mode-row">
                        <label class="form-check filter-mode-chip d-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input" type="radio" name="filter_mode" value="all" @checked(($filterMode ?? 'year') === 'all')>
                            <span class="small">Semua Data</span>
                        </label>
                        <label class="form-check filter-mode-chip d-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input" type="radio" name="filter_mode" value="year" @checked(($filterMode ?? 'year') === 'year')>
                            <span class="small">Tahunan</span>
                        </label>
                        <label class="form-check filter-mode-chip d-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input" type="radio" name="filter_mode" value="month" @checked(($filterMode ?? 'year') === 'month')>
                            <span class="small">Bulanan</span>
                        </label>
                        <label class="form-check filter-mode-chip d-flex align-items-center gap-2 mb-0">
                            <input class="form-check-input" type="radio" name="filter_mode" value="range" @checked(($filterMode ?? 'year') === 'range')>
                            <span class="small">Rentang Tanggal</span>
                        </label>
                    </div>
                    <div id="filterYearGroup" class="dashboard-filter-input-wrap">
                        <label class="form-label small text-muted mb-1">Tahun</label>
                        <select class="form-select form-select-sm" name="year" id="filterYearInput">
                            @foreach(($availableYears ?? []) as $yearOption)
                                <option value="{{ $yearOption }}" @selected(($selectedYear ?? null) == $yearOption)>{{ $yearOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="filterMonthGroup" class="dashboard-filter-input-wrap">
                        <label class="form-label small text-muted mb-1">Bulan</label>
                        <input type="month" class="form-control form-control-sm" name="month" id="filterMonthInput" value="{{ optional($selectedMonth ?? null)->format('Y-m') }}">
                    </div>
                    <div id="filterStartGroup" class="dashboard-filter-input-wrap">
                        <label class="form-label small text-muted mb-1">Tanggal Mulai</label>
                        <input type="date" class="form-control form-control-sm" name="start_date" id="filterStartInput" value="{{ optional($startDate ?? null)->toDateString() }}">
                    </div>
                    <div id="filterEndGroup" class="dashboard-filter-input-wrap">
                        <label class="form-label small text-muted mb-1">Tanggal Akhir</label>
                        <input type="date" class="form-control form-control-sm" name="end_date" id="filterEndInput" value="{{ optional($endDate ?? null)->toDateString() }}">
                    </div>
                    <div class="dashboard-filter-actions">
                        <button type="submit" class="btn btn-sm btn-primary px-3">Terapkan</button>
                        <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="dashboard-filter-description text-center mb-3">
    {{ $filterDescription ?? '' }}
</div>

<div class="row g-3 justify-content-center mb-1">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 dashboard-kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Rasio IKM (sesuai filter dashboard)</div>
                        <div class="h4 mb-0">
                            @if(!is_null($rasioIkm ?? null))
                                {{ number_format((float) $rasioIkm, 1, ',', '.') }}%
                            @else
                                -
                            @endif
                        </div>
                        <div class="small text-muted">
                            @if(!is_null($avgIkm ?? null))
                                {{ number_format((float) $avgIkm, 2, ',', '.') }}/4
                            @else
                                Belum ada data
                            @endif
                        </div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary rounded-circle dashboard-kpi-icon">
                        <i class="bi bi-bar-chart-line-fill"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 dashboard-kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Rasio IKK (sesuai filter dashboard)</div>
                        <div class="h4 mb-0">
                            @if(!is_null($rasioIkk ?? null))
                                {{ number_format((float) $rasioIkk, 1, ',', '.') }}%
                            @else
                                -
                            @endif
                        </div>
                        <div class="small text-muted">
                            @if(!is_null($avgIkk ?? null))
                                {{ number_format((float) $avgIkk, 2, ',', '.') }}/4
                            @else
                                Belum ada data
                            @endif
                        </div>
                    </div>
                    <span class="badge bg-warning-subtle text-warning rounded-circle dashboard-kpi-icon">
                        <i class="bi bi-shield-check"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 justify-content-center">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 dashboard-kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Permohonan Diproses</div>
                        <div class="h5 mb-0">{{ number_format($permohonanDiproses ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <span class="badge bg-info-subtle text-info rounded-circle dashboard-kpi-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 dashboard-kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Permohonan Terselesaikan</div>
                        <div class="h5 mb-0">{{ number_format($permohonanSelesai ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <span class="badge bg-success-subtle text-success rounded-circle dashboard-kpi-icon">
                        <i class="bi bi-check-circle"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 dashboard-kpi-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">Permohonan Dibatalkan</div>
                        <div class="h5 mb-0">{{ number_format($permohonanDibatalkan ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <span class="badge bg-danger-subtle text-danger rounded-circle dashboard-kpi-icon">
                        <i class="bi bi-x-circle"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0" id="barChartTitle">Permohonan</h6>
                    <small class="text-muted" id="barChartSubtitle"></small>
                </div>
            </div>
            <div class="card-body">
                <canvas id="permohonanChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Distribusi Tahap</h6>
            </div>
            <div class="card-body">
                <div class="status-chart-wrap">
                    <div class="status-chart-canvas">
                        <canvas id="statusChart" height="300"></canvas>
                    </div>
                    <ul class="status-chart-legend" id="statusChartLegend"></ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Kalender Agenda Lapangan</h6>
                <div class="d-flex gap-2 align-items-center">
                    <button class="btn btn-sm btn-outline-secondary" id="prevMonth"><i class="bi bi-chevron-left"></i></button>
                    <div class="fw-semibold" id="calendarTitle"></div>
                    <button class="btn btn-sm btn-outline-secondary" id="nextMonth"><i class="bi bi-chevron-right"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>Minggu</th>
                                <th>Senin</th>
                                <th>Selasa</th>
                                <th>Rabu</th>
                                <th>Kamis</th>
                                <th>Jumat</th>
                                <th>Sabtu</th>
                            </tr>
                        </thead>
                        <tbody id="calendarBody"></tbody>
                    </table>
                </div>
                <ul class="mt-3 small text-muted mb-0">
                    <li>Hover pada agenda untuk melihat petugas PCU.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-3">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Diagram Rating Feedback Website</h6>
                <!-- <small class="text-muted">Data dummy</small> -->
            </div>
            <div class="card-body">
                <canvas id="ratingChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@php
    // Fallback dummy jika belum ada data
    $ratingSummary = $ratingSummary ?? [1 => 2, 2 => 4, 3 => 9, 4 => 22, 5 => 18];
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const permohonanCtx = document.getElementById('permohonanChart');
        const statusCtx = document.getElementById('statusChart');
        const statusLegendEl = document.getElementById('statusChartLegend');
        const ratingCtx = document.getElementById('ratingChart');
        const barChartTitle = document.getElementById('barChartTitle');
        const barChartSubtitle = document.getElementById('barChartSubtitle');
        const filterYearGroup = document.getElementById('filterYearGroup');
        const filterMonthGroup = document.getElementById('filterMonthGroup');
        const filterStartGroup = document.getElementById('filterStartGroup');
        const filterEndGroup = document.getElementById('filterEndGroup');
        const filterYearInput = document.getElementById('filterYearInput');
        const filterMonthInput = document.getElementById('filterMonthInput');
        const filterStartInput = document.getElementById('filterStartInput');
        const filterEndInput = document.getElementById('filterEndInput');
        const filterModeInputs = document.querySelectorAll('input[name=\"filter_mode\"]');
        const calendarTitle = document.getElementById('calendarTitle');
        const calendarBody = document.getElementById('calendarBody');
        const prevBtn = document.getElementById('prevMonth');
        const nextBtn = document.getElementById('nextMonth');

        const dataset = @json($dashboardDataset ?? []);

        const agendaEvents = @json($agendaEvents ?? []);

        const monthLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        let permohonanChart;
        let statusChart;
        let ratingChart;
        const today = new Date();
        const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;
        let currentMonth = today.getMonth();
        let currentYear = today.getFullYear();
        let dashboardRendered = false;

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const sidebar = document.getElementById('sidebar');
        const main = document.getElementById('main');
        const toggleBtn = document.getElementById('toggleSidebar');
        const topbar = document.querySelector('.topbar');
        const logo = sidebar?.querySelector('.brand img');
        const sidebarCollapseKey = 'superadminDashboardSidebarCollapsed';

        const toNumber = (raw) => {
            const text = String(raw ?? '').trim();
            if (!text) return Number.NaN;
            const normalized = text.includes(',')
                ? text.replace(/\./g, '').replace(',', '.')
                : text;
            return Number.parseFloat(normalized);
        };

        const animateCounters = () => {
            const targets = Array.from(document.querySelectorAll('.dashboard-kpi-card .h4, .dashboard-kpi-card .h5'));
            targets.forEach((el) => {
                if (el.dataset.countAnimated === '1') return;
                const source = (el.textContent || '').trim();
                if (!/\d/.test(source)) return;
                const match = source.match(/-?\d[\d.,]*/);
                if (!match) return;

                const numericChunk = match[0];
                const value = toNumber(numericChunk);
                if (!Number.isFinite(value)) return;

                const prefix = source.slice(0, match.index);
                const suffix = source.slice((match.index || 0) + numericChunk.length);
                const decimals = (numericChunk.split(',')[1] || '').length;
                const formatter = new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals,
                });

                const duration = 900;
                const startedAt = performance.now();
                const step = (now) => {
                    const progress = Math.min((now - startedAt) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = value * eased;
                    el.textContent = `${prefix}${formatter.format(current)}${suffix}`;
                    if (progress < 1) {
                        requestAnimationFrame(step);
                        return;
                    }
                    el.textContent = source;
                    el.dataset.countAnimated = '1';
                };
                requestAnimationFrame(step);
            });
        };

        const prepareReveal = (nodes, direction = 'up') => {
            nodes.forEach((node) => {
                node.classList.add('dashboard-ui-reveal');
                if (direction === 'left') {
                    node.classList.add('dashboard-ui-reveal-left');
                }
            });
        };

        const revealStagger = (nodes, delayStart = 0, step = 55) => {
            nodes.forEach((node, index) => {
                window.setTimeout(() => {
                    node.classList.add('is-visible');
                }, delayStart + (index * step));
            });
        };

        const renderDashboardData = () => {
            if (dashboardRendered) return;
            renderCharts();
            renderCalendar();
            initTooltips();
            animateCounters();
            dashboardRendered = true;
        };

        const applyDashboardSidebarToggle = () => {
            if (!toggleBtn || !sidebar || !main) return;

            const syncFromSavedState = () => {
                const isDesktop = window.matchMedia('(min-width: 992px)').matches;
                if (!isDesktop) {
                    sidebar.classList.remove('collapsed');
                    main.classList.remove('collapsed');
                    return;
                }

                sidebar.classList.remove('hidden');
                main.classList.remove('expanded');
                const collapsed = sessionStorage.getItem(sidebarCollapseKey) === '1';
                sidebar.classList.toggle('collapsed', collapsed);
                main.classList.toggle('collapsed', collapsed);
            };

            syncFromSavedState();

            toggleBtn.onclick = (event) => {
                event.preventDefault();
                const isDesktop = window.matchMedia('(min-width: 992px)').matches;
                if (!isDesktop) {
                    sidebar.classList.toggle('hidden');
                    main.classList.toggle('expanded');
                    return;
                }

                sidebar.classList.remove('hidden');
                main.classList.remove('expanded');
                const collapsed = sidebar.classList.toggle('collapsed');
                main.classList.toggle('collapsed', collapsed);
                sessionStorage.setItem(sidebarCollapseKey, collapsed ? '1' : '0');

                if (collapsed && logo) {
                    logo.classList.remove('logo-roll');
                    void logo.offsetWidth;
                    logo.classList.add('logo-roll');
                }
            };

            window.addEventListener('resize', syncFromSavedState);
        };

        const syncFilterMode = () => {
            const checked = document.querySelector('input[name="filter_mode"]:checked');
            const mode = checked ? checked.value : 'year';
            const isYearMode = mode === 'year';
            const isMonthMode = mode === 'month';
            const isRangeMode = mode === 'range';

            if (filterYearGroup) filterYearGroup.classList.toggle('d-none', !isYearMode);
            if (filterMonthGroup) filterMonthGroup.classList.toggle('d-none', !isMonthMode);
            if (filterStartGroup) filterStartGroup.classList.toggle('d-none', !isRangeMode);
            if (filterEndGroup) filterEndGroup.classList.toggle('d-none', !isRangeMode);
            if (filterYearInput) filterYearInput.disabled = !isYearMode;
            if (filterMonthInput) filterMonthInput.disabled = !isMonthMode;
            if (filterStartInput) filterStartInput.disabled = !isRangeMode;
            if (filterEndInput) filterEndInput.disabled = !isRangeMode;
        };

        const renderCharts = () => {
            const barData = dataset.barValues || [];
            const barLabels = dataset.barLabels || [];
            const statusLabels = dataset.statusLabels || [];
            const doughnutValues = dataset.statusValues || [];
            
            const ratingSummary = @json($ratingSummary);
            const ratingLabels = ['1 ★', '2 ★', '3 ★', '4 ★', '5 ★'];
            const ratingValues = [ratingSummary[1] || 0, ratingSummary[2] || 0, ratingSummary[3] || 0, ratingSummary[4] || 0, ratingSummary[5] || 0];

            if (permohonanChart) permohonanChart.destroy();
            if (statusChart) statusChart.destroy();
            if (ratingChart) ratingChart.destroy();

            if (permohonanCtx) {
                if (barChartTitle) barChartTitle.textContent = dataset.barTitle || 'Permohonan';
                if (barChartSubtitle) barChartSubtitle.textContent = dataset.barSubtitle || '';
                permohonanChart = new Chart(permohonanCtx, {
                    type: 'bar',
                    data: {
                        labels: barLabels,
                        datasets: [{
                            label: dataset.barTitle || 'Permohonan',
                            data: barData,
                            backgroundColor: '#15406A',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: { right: 8, left: 8, top: 4, bottom: 4 }
                        },
                        animation: {
                            duration: 1000,
                            easing: 'easeOutCubic'
                        },
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision:0 } }
                        }
                    }
                });
            }

            if (statusCtx) {
                const statusColorMap = {
                    'Disposisi': 'rgba(13, 110, 253, 0.8)',
                    'Kaji Ulang': 'rgba(111, 66, 193, 0.8)',
                    'Penawaran': 'rgba(108, 117, 125, 0.8)',
                    'Penjadwalan': 'rgba(255, 193, 7, 0.8)',
                    'Approval MA': 'rgba(59, 130, 246, 0.8)',
                    'Dokumen SPT': 'rgba(13, 148, 136, 0.8)',
                    'Pengujian': 'rgba(23, 162, 184, 0.8)',
                    'BAP': 'rgba(220, 53, 69, 0.8)',
                    'Verifikasi Pengujian': 'rgba(13, 202, 240, 0.8)',
                    'Koding': 'rgba(253, 126, 20, 0.8)',
                    'Preparasi Analisa': 'rgba(20, 184, 166, 0.8)',
                    'Verifikasi Hasil Analisa': 'rgba(99, 102, 241, 0.8)',
                    'Draft LHU': 'rgba(244, 63, 94, 0.8)',
                    'QC LHU': 'rgba(25, 135, 84, 0.8)',
                    'Penandatanganan LHU': 'rgba(147, 51, 234, 0.8)',
                    'Surat Tagihan': 'rgba(8, 145, 178, 0.8)',
                    'Kuitansi': 'rgba(14, 116, 144, 0.8)',
                    'Kode Billing': 'rgba(185, 28, 28, 0.8)',
                    'Penerbitan Suket': 'rgba(16, 185, 129, 0.8)',
                    'Penyerahan LHU': 'rgba(217, 119, 6, 0.8)',
                };
                const fallbackPalette = [
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(111, 66, 193, 0.8)',
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(13, 202, 240, 0.8)',
                    'rgba(108, 117, 125, 0.8)',
                ];

                const totalStatus = (doughnutValues || []).reduce((acc, value) => acc + Number(value || 0), 0);
                const totalPermohonan = Number(dataset.totalPermohonan || 0);
                const hasStatusData = totalStatus > 0;
                const noPermohonan = !hasStatusData && totalPermohonan === 0;
                const statusChartLabels = hasStatusData
                    ? statusLabels
                    : [noPermohonan ? 'Tidak ada permohonan' : 'Semua permohonan selesai'];
                const statusChartValues = hasStatusData
                    ? doughnutValues
                    : [1];
                const statusColors = hasStatusData
                    ? statusChartLabels.map((label, idx) => statusColorMap[label] || fallbackPalette[idx % fallbackPalette.length])
                    : [noPermohonan ? 'rgba(173, 181, 189, 0.85)' : 'rgba(25, 135, 84, 0.85)'];

                if (statusLegendEl) {
                    statusLegendEl.innerHTML = statusChartLabels.map((label, idx) => {
                        const safeLabel = String(label ?? '');
                        return `
                            <li class="status-chart-legend-item" title="${safeLabel.replace(/"/g, '&quot;')}">
                                <span class="status-chart-legend-dot" style="background:${statusColors[idx] || '#adb5bd'}"></span>
                                <span class="status-chart-legend-text">${safeLabel}</span>
                            </li>
                        `;
                    }).join('');
                }

                statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusChartLabels,
                        datasets: [{
                            data: statusChartValues,
                            backgroundColor: statusColors,
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1150,
                            easing: 'easeOutQuart'
                        },
                        plugins: {
                            legend: { display: false }
                        },
                        cutout: '60%'
                    }
                });
            }

            if (ratingCtx) {
                ratingChart = new Chart(ratingCtx, {
                    type: 'bar',
                    data: {
                        labels: ratingLabels,
                        datasets: [{
                            label: 'Jumlah Rating',
                            data: ratingValues,
                            backgroundColor: '#15406A',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 1000,
                            easing: 'easeOutCubic'
                        },
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });
            }

        };

        filterModeInputs.forEach((input) => {
            input.addEventListener('change', syncFilterMode);
        });

        syncFilterMode();

        const renderCalendar = () => {
            const firstDay = new Date(currentYear, currentMonth, 1);
            const startDay = firstDay.getDay(); // 0 Minggu ... 6 Sabtu
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const eventsInMonth = agendaEvents.filter(e => {
                const d = new Date(e.date);
                return d.getFullYear() === currentYear && d.getMonth() === currentMonth;
            });

            if (calendarTitle) {
                calendarTitle.textContent = `${monthLabels[currentMonth]} ${currentYear}`;
            }

            let cells = [];
            // padding awal
            for (let i = 0; i < startDay; i++) {
                cells.push('<td class="bg-light"></td>');
            }
            for (let day = 1; day <= daysInMonth; day++) {
                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
                const dayOfWeek = new Date(currentYear, currentMonth, day).getDay();
                const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                const dayEvents = eventsInMonth.filter(e => e.date === dateStr);
                const eventsHtml = dayEvents.map(ev => {
                    const pcu = ev.pcu && ev.pcu !== '-' ? ev.pcu : '-';
                    const company = ev.company && ev.company !== '-' ? ev.company : '-';
                    const city = ev.city && ev.city !== '-' ? ev.city : '-';
                    const daysValue = Number.isFinite(Number(ev.days)) ? Number(ev.days) : 1;
                    const daysLabel = daysValue > 1 ? `${daysValue} hari` : '1 hari';
                    const tooltip = [
                        `<i class='bi bi-building me-1'></i>${company}`,
                        `<i class='bi bi-geo-alt me-1'></i>${city}`,
                        `<i class='bi bi-person-badge me-1'></i>${pcu}`,
                        `<i class='bi bi-clock-history me-1'></i>${daysLabel}`,
                    ].join('<br>');
                    return `<div class="badge bg-primary-subtle text-primary w-100 text-start mb-1" data-bs-toggle="tooltip" data-bs-html="true" data-bs-custom-class="agenda-tooltip" title="${tooltip}">${ev.title}</div>`;
                }).join('');
                const isToday = dateStr === todayStr;
                const dayClass = isWeekend ? 'text-danger' : '';
                const todayBadge = isToday ? 'calendar-today' : '';
                const todayChip = isToday ? '<span class="today-chip">Today</span>' : '';
                cells.push(`
                    <td style="min-width: 130px; vertical-align: top;" class="${todayBadge}">
                        <div class="fw-semibold ${dayClass}">${day}</div>
                        ${todayChip}
                        <div class="mt-1">${eventsHtml || '<span class="text-muted small">-</span>'}</div>
                    </td>
                `);
            }

            while (cells.length % 7 !== 0) {
                cells.push('<td class="bg-light"></td>');
            }

            let rows = '';
            for (let i = 0; i < cells.length; i += 7) {
                rows += `<tr>${cells.slice(i, i + 7).join('')}</tr>`;
            }

            if (calendarBody) {
                calendarBody.innerHTML = rows;
            }
        };

        const initTooltips = () => {
            if (!window.bootstrap?.Tooltip) return;
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach((el) => {
                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el);
                }
            });
        };

        prevBtn?.addEventListener('click', () => {
            currentMonth -= 1;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear -= 1;
            }
            renderCalendar();
            initTooltips();
        });

        nextBtn?.addEventListener('click', () => {
            currentMonth += 1;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear += 1;
            }
            renderCalendar();
            initTooltips();
        });

        applyDashboardSidebarToggle();

        const navItems = [
            ...Array.from(document.querySelectorAll('#sidebar .brand, #sidebar .menu > *')),
        ];
        const headerItems = [
            ...Array.from(document.querySelectorAll('.dashboard-filter-card, .dashboard-filter-description')),
        ];
        const dataItems = Array.from(document.querySelectorAll('main .card.border-0.shadow-sm'))
            .filter((card) => !card.classList.contains('dashboard-filter-card'));

        prepareReveal(navItems, 'left');
        if (topbar) prepareReveal([topbar], 'up');
        prepareReveal(headerItems, 'up');
        prepareReveal(dataItems, 'up');

        if (prefersReducedMotion) {
            [ ...navItems, ...headerItems, ...dataItems, topbar ].filter(Boolean).forEach((node) => {
                node.classList.add('is-visible');
            });
            renderDashboardData();
            return;
        }

        revealStagger(navItems, 60, 42);
        if (topbar) revealStagger([topbar], 260, 0);
        revealStagger(headerItems, 700, 110);
        revealStagger(dataItems, 1120, 70);
        window.setTimeout(renderDashboardData, 1280);
    });
</script>
@endpush


@endsection
