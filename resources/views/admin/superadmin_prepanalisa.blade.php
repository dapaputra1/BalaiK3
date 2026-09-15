@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
  $formulaReferences = $formulaReferences ?? [];
  $debuAmbienReference = is_array($formulaReferences['debu_ambien'] ?? null) ? $formulaReferences['debu_ambien'] : [];
  $benzeneReference = is_array($formulaReferences['benzene'] ?? null) ? $formulaReferences['benzene'] : [];
  $tolueneReference = is_array($formulaReferences['toluene'] ?? null) ? $formulaReferences['toluene'] : [];
  $xyleneReference = is_array($formulaReferences['xylene'] ?? null) ? $formulaReferences['xylene'] : [];
  $h2sReference = is_array($formulaReferences['h2s'] ?? null) ? $formulaReferences['h2s'] : [];
  $hclEmisiReference = is_array($formulaReferences['hcl_emisi'] ?? null) ? $formulaReferences['hcl_emisi'] : [];
  $hfEmisiReference = is_array($formulaReferences['hf_emisi'] ?? null) ? $formulaReferences['hf_emisi'] : [];
  $hgEmisiReference = is_array($formulaReferences['hg_emisi'] ?? null) ? $formulaReferences['hg_emisi'] : [];
  $nh3Reference = is_array($formulaReferences['nh3'] ?? null) ? $formulaReferences['nh3'] : [];
  $no2Reference = is_array($formulaReferences['no2'] ?? null) ? $formulaReferences['no2'] : [];
  $pbReference = is_array($formulaReferences['pb'] ?? null) ? $formulaReferences['pb'] : [];
  $cdReference = is_array($formulaReferences['cd'] ?? null) ? $formulaReferences['cd'] : [];
  $asReference = is_array($formulaReferences['as'] ?? null) ? $formulaReferences['as'] : [];
  $hgAasReference = is_array($formulaReferences['hg_aas'] ?? null) ? $formulaReferences['hg_aas'] : [];
  $coReference = is_array($formulaReferences['co'] ?? null) ? $formulaReferences['co'] : [];
  $sbReference = is_array($formulaReferences['sb'] ?? null) ? $formulaReferences['sb'] : [];
  $tlReference = is_array($formulaReferences['tl'] ?? null) ? $formulaReferences['tl'] : [];
  $crReference = is_array($formulaReferences['cr'] ?? null) ? $formulaReferences['cr'] : [];
  $cuReference = is_array($formulaReferences['cu'] ?? null) ? $formulaReferences['cu'] : [];
  $znReference = is_array($formulaReferences['zn'] ?? null) ? $formulaReferences['zn'] : [];
  $absorbanceReferences = is_array($formulaReferences['absorbance'] ?? null) ? $formulaReferences['absorbance'] : [];
  $oxReference = is_array($formulaReferences['ox'] ?? null) ? $formulaReferences['ox'] : [];
  $parameterLodReferences = is_array($formulaReferences['parameter_lods'] ?? null) ? $formulaReferences['parameter_lods'] : [];
  $so2AmbienReference = is_array($formulaReferences['so2_ambien'] ?? null) ? $formulaReferences['so2_ambien'] : [];
  $routePrefix = $routePrefix ?? 'superadmin';
  $currentUserId = auth()->id();
  $currentUserRole = auth()->user()?->role;
  $assetDataUrl = function (?string $absolutePath): string {
    $path = $absolutePath ? (string) $absolutePath : '';
    if ($path === '' || !is_file($path)) {
      return '';
    }
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') {
      return '';
    }
    $mime = @mime_content_type($path) ?: 'image/png';
    return 'data:' . $mime . ';base64,' . base64_encode($raw);
  };
  $signatureDataUrl = function (?string $path): string {
    $relativePath = trim((string) ($path ?? ''));
    if ($relativePath === '') {
      return '';
    }
    foreach (['local', 'public'] as $disk) {
      try {
        if (!\Illuminate\Support\Facades\Storage::disk($disk)->exists($relativePath)) {
          continue;
        }
        $raw = \Illuminate\Support\Facades\Storage::disk($disk)->get($relativePath);
        if ($raw === '' || $raw === null) {
          return '';
        }
        $mime = \Illuminate\Support\Facades\Storage::disk($disk)->mimeType($relativePath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($raw);
      } catch (\Throwable $e) {
        continue;
      }
    }
    return '';
  };
  $printLogoDataUrl = $assetDataUrl(public_path('images/Logo Kemnaker.png'));
  $currentUserSignatureDataUrl = $signatureDataUrl(auth()->user()?->signature_path);
  $orderRows = collect();
  $formatKodingDisplay = function ($code) {
    $value = trim((string) $code);
    if ($value === '' || $value === '-') return '-';
    if (preg_match('/^\d+\.(\d{2}\.\d{2}\/.+)$/', $value, $m)) {
      return $m[1];
    }
    return $value;
  };
@endphp

<style>
  .btn-primary {
    background-color: #15406A !important;
    border-color: #15406A !important;
  }
  .btn-primary:hover,
  .btn-primary:focus {
    background-color: #0f2f53 !important;
    border-color: #0f2f53 !important;
  }
  .text-bg-primary {
    background-color: #15406A !important;
    color: #fff !important;
  }
  .btn-outline-primary {
    color: #15406A !important;
    border-color: #15406A !important;
  }
  .btn-outline-primary:hover,
  .btn-outline-primary:focus {
    background-color: #15406A !important;
    color: #fff !important;
    border-color: #15406A !important;
  }
  .btn-outline-primary.active,
  .btn-outline-primary:active {
    background-color: #15406A !important;
    color: #fff !important;
    border-color: #15406A !important;
  }
  .accordion-button::after {
    filter: invert(1);
  }
  .category-separator td {
    border-top: 3px solid #6c757d !important;
  }
  .prepanalisa-arrival-time {
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    color: rgba(255,255,255,.9);
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
  }
  .prepanalisa-arrival-time i {
    font-size: 11px;
    margin-right: 4px;
  }
  .prepanalisa-header-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    padding-right: 170px;
  }
  .prepanalisa-order-code {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
    color: #fff;
  }
  .prepanalisa-company-name {
    font-size: 13px;
    font-weight: 400;
    line-height: 1.2;
    color: rgba(255,255,255,.92);
    margin-top: 2px;
  }
  .prepanalisa-meta-row {
    position: absolute;
    right: 34px;
    top: 50%;
    transform: translateY(-50%);
  }
  #prepanalisaCompanyAccordion .accordion-button {
    background-color: #15406a !important;
    color: #fff;
  }
  #prepanalisaCompanyAccordion .accordion-button:not(.collapsed) {
    background-color: #15406a !important;
    color: #fff;
    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
  }
  #prepanalisaCompanyAccordion .accordion-item[data-card] {
    transition: transform .18s ease, box-shadow .18s ease;
    transform-origin: center center;
  }
  @media (hover: hover) and (pointer: fine) {
    #prepanalisaCompanyAccordion .accordion-item[data-card]:hover {
      transform: scale(1.008);
      box-shadow: 0 12px 24px rgba(15, 47, 83, 0.12) !important;
    }
  }
  @media (max-width: 575.98px) {
    .prepanalisa-header-content {
      padding-right: 0;
    }
    .prepanalisa-meta-row {
      position: static;
      transform: none;
      margin-top: 2px;
    }
  }
  .swal2-container {
    z-index: 2100 !important;
  }
  #parameterActionModal .modal-dialog {
    max-width: min(1800px, 98vw);
    margin: 0.75rem auto;
  }
  #parameterActionModal .modal-content {
    min-height: calc(100vh - 24px);
    max-height: calc(100vh - 24px);
    display: flex;
  }
  #parameterActionModal .modal-body {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
    min-height: 0;
    overflow-x: auto;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
  }
  #parameterActionModal [data-action-grid] {
    min-height: 260px !important;
    flex: 1 1 auto;
    min-width: 0;
    overflow: visible;
  }
  #parameterActionModal [data-action-grid] .table-responsive {
    flex: 1 1 auto;
    min-height: 0;
    max-height: none;
    overflow: visible;
  }
  #parameterActionModal [data-action-footer] {
    margin-top: 12px !important;
    padding-top: 12px;
    border-top: 1px solid #d7dee8;
    background: #fff;
    position: sticky;
    bottom: 0;
    z-index: 2;
  }
  #parameterActionModal .modal-header {
    padding: 12px 16px;
  }
  #parameterActionModal .modal-title {
    font-size: 16px;
    font-weight: 700;
  }
  #parameterActionModal .modal-body {
    padding: 14px 16px;
    font-size: 12.5px;
  }
  #parameterActionModal [data-action-order] {
    font-size: 12px;
  }
  #parameterActionModal [data-action-param] {
    font-size: 13px;
    line-height: 1.25;
    margin-bottom: 4px !important;
  }
  #parameterActionModal [data-action-category] {
    font-size: 11.5px;
    color: #6c757d;
    line-height: 1.3;
    margin-bottom: 12px !important;
  }
  #parameterActionModal [data-action-type] {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 10px;
    line-height: 1.2;
  }
  #parameterActionModal [data-action-reset-row] .btn,
  #parameterActionModal [data-action-cons] .btn,
  #parameterActionModal [data-action-footer] .btn {
    font-size: 12px;
    line-height: 1.2;
    padding: 6px 12px;
    border-radius: 10px;
  }
  #parameterActionModal [data-action-grid] .table {
    font-size: 12px;
  }
  #parameterActionModal [data-action-grid] .table th,
  #parameterActionModal [data-action-grid] .table td {
    padding: 6px 7px;
    vertical-align: middle;
  }
  #parameterActionModal [data-action-grid] .table thead th {
    font-size: 11.5px;
    white-space: nowrap;
  }
  #parameterActionModal .form-control,
  #parameterActionModal .form-select {
    font-size: 12px;
    min-height: 32px;
    padding-top: 0.32rem;
    padding-bottom: 0.32rem;
  }
  #parameterActionModal .small,
  #parameterActionModal .text-muted.small {
    font-size: 11.5px !important;
  }
  #parameterActionModal .alert {
    font-size: 11.5px;
    padding: 8px 10px;
  }
  #parameterActionModal [data-action-cons] {
    gap: 8px !important;
  }
  #parameterActionModal [data-pb-curve-panel] {
    display: grid;
    grid-template-columns: minmax(360px, 0.85fr) minmax(0, 1.45fr);
    gap: 12px;
    align-items: stretch;
    margin-bottom: 12px;
  }
  #parameterActionModal [data-pb-curve-panel] .pb-curve-card {
    border: 1px solid #d7dee8;
    border-radius: 14px;
    background: #f8fafc;
    padding: 12px;
    height: auto;
    align-self: start;
  }
  #parameterActionModal [data-pb-curve-panel] .pb-curve-chart-card {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
  }
  #parameterActionModal [data-pb-curve-panel] .pb-curve-table-card {
    min-width: 0;
  }
  #parameterActionModal [data-pb-curve-panel] .pb-curve-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 10px;
  }
  #parameterActionModal [data-pb-curve-panel] .pb-curve-title {
    font-size: 13px;
    font-weight: 700;
    color: #22324a;
  }
  #parameterActionModal [data-pb-curve-panel] .pb-curve-note {
    font-size: 11.5px;
    color: #5f6f82;
  }
  #parameterActionModal [data-pb-curve-formula] {
    display: inline-flex;
    flex-direction: row;
    align-items: center;
    gap: 10px 14px;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding: 6px 10px;
    border-radius: 10px;
    background: #eef4fb;
    color: #35506d !important;
    font-family: Consolas, Monaco, monospace;
    font-size: 11px !important;
    line-height: 1.35;
    text-align: left;
  }
  #parameterActionModal [data-pb-curve-formula] .pb-curve-metric {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex: 0 0 auto;
  }
  #parameterActionModal [data-pb-curve-formula] .pb-curve-metric input {
    width: 110px;
    min-width: 110px;
    font-family: inherit;
    font-size: 11px;
    text-align: right;
  }
  #parameterActionModal [data-pb-curve-latest] {
    font-size: 11px;
    color: #5f6f82;
    margin-top: 6px;
  }
  #parameterActionModal .pb-curve-axis-options {
    margin-top: 8px;
    padding: 8px 10px 10px;
    border: 1px solid #d7dee8;
    border-radius: 10px;
    background: #f8fbff;
  }
  #parameterActionModal .pb-curve-axis-title {
    font-size: 11px;
    font-weight: 700;
    color: #35506d;
    margin-bottom: 8px;
  }
  #parameterActionModal .pb-curve-axis-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
  }
  #parameterActionModal .pb-curve-axis-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
  }
  #parameterActionModal .pb-curve-axis-field span {
    font-size: 10.5px;
    color: #54657a;
  }
  #parameterActionModal .pb-curve-axis-field input {
    min-width: 0;
    font-size: 11px;
    text-align: right;
  }
  #parameterActionModal .pb-curve-axis-field input::placeholder {
    color: rgba(84, 101, 122, 0.16);
    opacity: 1;
  }
  #parameterActionModal [data-pb-curve-panel] .table {
    font-size: 11.5px;
    margin-bottom: 0;
    min-width: 720px;
  }
  #parameterActionModal [data-pb-curve-panel] .table th,
  #parameterActionModal [data-pb-curve-panel] .table td {
    padding: 5px 6px;
  }
  #parameterActionModal [data-pb-curve-panel] .table thead th {
    text-align: center;
    vertical-align: middle;
  }
  #parameterActionModal [data-pb-curve-panel] .table-responsive {
    max-height: none;
    overflow: visible;
    margin-bottom: 0;
  }
  #parameterActionModal [data-pb-curve-panel] .pb-curve-section td {
    background: #fff;
    font-weight: 700;
    text-align: center;
  }
  #parameterActionModal [data-pb-curve-panel] [data-pb-curve="standard_no"] {
    text-align: center;
    font-weight: 600;
  }
  #parameterActionModal [data-pb-curve-plot] {
    background: #fff;
    border: 1px solid #d7dee8;
    border-radius: 12px;
    padding: 6px;
    min-height: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
  }
  #parameterActionModal [data-pb-curve-plot] svg {
    display: block;
    width: 100%;
    height: clamp(255px, 28vw, 320px);
    max-height: none;
  }
  #parameterActionModal [data-pb-curve-overlay-card] {
    position: absolute;
    top: 18px;
    right: 18px;
    min-width: 146px;
    padding: 6px 10px;
    border: 1px solid #d7dee8;
    border-radius: 12px;
    background: rgba(248, 250, 252, 0.96);
    color: #334155;
    font-family: Consolas, Monaco, monospace;
    font-size: 10.5px;
    line-height: 1.25;
    cursor: grab;
    user-select: none;
    touch-action: none;
    z-index: 3;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
  }
  #parameterActionModal [data-pb-curve-overlay-card].is-dragging {
    cursor: grabbing;
  }
  #parameterActionModal [data-pb-curve-plot] .pb-curve-line {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
    animation: pbCurveDraw 2.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  }
  #parameterActionModal [data-pb-curve-plot] .pb-curve-point {
    opacity: 0;
    transform-box: fill-box;
    transform-origin: center;
    animation: pbCurvePointIn .75s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  }
  #parameterActionModal [data-pb-curve-plot] .pb-curve-point-label {
    opacity: 0;
    animation: pbCurveLabelIn .8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
  }
  #parameterActionModal [data-pb-curve-plot] .pb-curve-point-hit {
    cursor: pointer;
  }
  #parameterActionModal [data-pb-curve-plot] .pb-curve-tooltip {
    pointer-events: none;
  }
  @keyframes pbCurveDraw {
    to {
      stroke-dashoffset: 0;
    }
  }
  @keyframes pbCurvePointIn {
    from {
      opacity: 0;
      transform: scale(0.5);
    }
    to {
      opacity: 1;
      transform: scale(1);
    }
  }
  @keyframes pbCurveLabelIn {
    from {
      opacity: 0;
      transform: translateY(4px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  @keyframes pbCurvePanelIn {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  @media (prefers-reduced-motion: reduce) {
    #parameterActionModal [data-pb-curve-plot] .pb-curve-line,
    #parameterActionModal [data-pb-curve-plot] .pb-curve-point,
    #parameterActionModal [data-pb-curve-plot] .pb-curve-point-label,
    #parameterActionModal [data-pb-curve-plot] .pb-curve-equation {
      animation: none;
      opacity: 1;
      transform: none;
      stroke-dashoffset: 0;
    }
  }
  @media (max-width: 1199.98px) {
    #parameterActionModal [data-pb-curve-panel] {
      grid-template-columns: 1fr;
    }
    #parameterActionModal .pb-curve-axis-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    #parameterActionModal [data-pb-curve-plot] svg {
      height: 270px;
    }
  }
  .prepanalisa-search-wrap {
    background: #eef2f7;
    border: 1px solid #d7e1ee;
    border-radius: 16px;
    padding: 10px;
    margin-bottom: 16px;
  }
  .prepanalisa-search-wrap .input-group-text {
    background: #fff;
    border-color: #b8cbe2;
    border-right: 0;
    border-radius: 12px 0 0 12px;
    color: #6c7f96;
  }
  .prepanalisa-search-wrap .form-control {
    border-color: #b8cbe2;
    border-left: 0;
    border-radius: 0 12px 12px 0;
    min-height: 40px;
    box-shadow: none !important;
    font-size: 14px;
  }
  .prepanalisa-search-wrap .form-control:focus {
    border-color: #95b6da;
  }
  .prepanalisa-search-wrap .btn-search-reset {
    min-height: 40px;
    border-radius: 12px;
    border: 1px solid #15406A;
    color: #15406A;
    background: #fff;
    font-weight: 600;
  }
  .prepanalisa-search-wrap .btn-search-reset:hover {
    background: #f3f8ff;
  }
  .prepanalisa-grid-wrap {
    border: 1px solid #d7dee8;
    border-radius: 18px;
    background: #fff;
    overflow-x: auto;
    overflow-y: hidden;
  }
  .prepanalisa-grid-table {
    table-layout: auto;
    min-width: 1120px;
    font-size: 13px;
    margin-bottom: 0;
  }
  .prepanalisa-grid-table thead th {
    font-size: 12.5px;
    font-weight: 700;
    padding: 12px 14px;
    vertical-align: middle;
    white-space: nowrap;
  }
  .prepanalisa-grid-table tbody td {
    padding: 14px;
    vertical-align: top;
    font-size: 12.5px;
    line-height: 1.45;
  }
  .prepanalisa-grid-table td,
  .prepanalisa-grid-table th {
    border-color: #d7dee8;
  }
  .prepanalisa-grid-table td code {
    display: inline-block;
    font-size: 12.5px;
    color: #d63384;
    white-space: normal;
    word-break: break-word;
    font-weight: 600;
    line-height: 1.45;
  }
  .prepanalisa-grid-table .prepanalisa-doc-cell,
  .prepanalisa-grid-table .prepanalisa-koding-cell,
  .prepanalisa-grid-table .prepanalisa-parameter-cell,
  .prepanalisa-grid-table .prepanalisa-analis-cell {
    white-space: normal;
    line-height: 1.45;
  }
  .prepanalisa-grid-table .prepanalisa-doc-cell {
    font-size: 12.5px;
  }
  .prepanalisa-grid-table .prepanalisa-doc-cell .fw-semibold {
    font-size: 12.5px;
    margin-bottom: 2px;
  }
  .prepanalisa-grid-table .prepanalisa-lokasi-cell,
  .prepanalisa-grid-table .prepanalisa-kategori-cell,
  .prepanalisa-grid-table .prepanalisa-analis-cell {
    font-size: 12.5px;
  }
  .prepanalisa-grid-table .prepanalisa-koding-cell {
    min-width: 170px;
  }
  .prepanalisa-grid-table .prepanalisa-kategori-cell {
    min-width: 92px;
    white-space: nowrap;
  }
  .prepanalisa-grid-table .prepanalisa-parameter-cell {
    font-size: 12.5px;
    min-width: 220px;
  }
  .prepanalisa-grid-table .prepanalisa-analis-cell {
    min-width: 120px;
  }
  .prepanalisa-grid-table .prepanalisa-action-cell {
    min-width: 150px;
  }
  .prepanalisa-grid-table .prepanalisa-done-cell {
    min-width: 72px;
  }
  .prepanalisa-grid-table .prepanalisa-action-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
  }
  .prepanalisa-grid-table .prepanalisa-action-stack .btn.btn-sm {
    min-width: 110px;
    padding: 5px 10px;
    font-size: 12px;
    line-height: 1.2;
    border-radius: 10px;
  }
  .prepanalisa-grid-table .form-check-input {
    transform: scale(0.92);
    margin-top: 0;
    /* checkbox border hitam */
    border: 1px solid #000;
  }
  .prepanalisa-grid-table .badge {
    font-size: 10px;
    padding: 4px 6px;
  }
  @media (max-width: 991.98px) {
    .prepanalisa-grid-table {
      min-width: 1040px;
    }
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => 'Alur Kerja - Preparasi Analisa',
  'subtitle' => 'Review hasil sampling dan ID koding per lokasi sebelum analisa lab dilanjutkan.',
  'total' => $orders->count(),
])

@php
  $searchRows = [];
  $locationPayloadStore = [];
@endphp

<div class="prepanalisa-search-wrap">
  <div class="row g-2 align-items-center">
    <div class="col-12 col-md-8">
      <div class="input-group input-group-sm">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control" placeholder="Cari berdasarkan koding" data-search-koding>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-search-reset w-100 btn-sm" data-search-reset>
          <i class="bi bi-arrow-clockwise me-1"></i>Reset
        </button>
        <a href="{{ route($routePrefix . '.prepanalisa.history') }}" class="btn btn-primary w-100 btn-sm d-inline-flex align-items-center justify-content-center">
          <i class="bi bi-clock-history me-1"></i>Riwayat
        </a>
      </div>
    </div>
  </div>
</div>

<div class="alert alert-info border-0 shadow-sm rounded-4 small py-2 px-3">
  Alur kerja multi analis: <strong>1) Pilih</strong> parameter untuk claim item, <strong>2) Hitung</strong> untuk input perhitungan, <strong>3) Selesai</strong> aktifkan switch setelah pekerjaan selesai.
</div>

<div class="accordion" id="prepanalisaCompanyAccordion">
  @forelse($orders as $order)
    @php
      $orderKey = 'order-' . $loop->index;
      $orderCode = $order['kode'] ?? '-';
      $companyName = $order['perusahaan'] ?? '-';

      $orderRows = [];
      foreach (($order['parameter'] ?? []) as $param) {
        foreach (($param['lokasi'] ?? []) as $idx => $loc) {
          $orderRows[] = [
            'lokasi' => $loc['nama'] ?? '-',
            'dokumen_label' => $loc['dokumen_label'] ?? 'Dokumen',
            'dokumen_files' => $loc['dokumen_files'] ?? [],
            'koding' => $loc['koding'] ?? '-',
            'koding_display' => $formatKodingDisplay($loc['koding'] ?? '-'),
            'samples' => $loc['samples'] ?? [],
            'kategori' => $param['kategori'] ?? '-',
            'parameter' => $param['nama'] ?? '-',
            'order_kode' => $order['kode'] ?? '-',
            'permohonan_id' => $order['permohonan_id'] ?? null,
            'parameter_group_key' => implode('|', [
              $order['permohonan_id'] ?? '',
              $loc['service_parameter_id'] ?? $param['id'] ?? '',
            ]),
            'location_key' => $loc['koding'] ?? $loc['nama'] ?? $idx,
            'location_payload' => [
              'nama' => $loc['nama'] ?? '-',
              'koding' => $loc['koding'] ?? '-',
              'samples' => $loc['samples'] ?? [],
              'koding_item_id' => $loc['koding_item_id'] ?? null,
              'dokumen_id' => $loc['dokumen_id'] ?? null,
              'pengujian_dokumen_parameter_id' => $loc['pengujian_dokumen_parameter_id'] ?? null,
              'service_parameter_id' => $loc['service_parameter_id'] ?? null,
              'parameter_group_key' => implode('|', [
                $order['permohonan_id'] ?? '',
                $loc['service_parameter_id'] ?? $param['id'] ?? '',
              ]),
              'assigned_user_id' => $loc['assigned_user_id'] ?? null,
              'assigned_analis' => $loc['assigned_analis'] ?? '-',
              'is_done' => (bool) ($loc['is_done'] ?? false),
              'saved' => $loc['saved'] ?? null,
            ],
            'assigned_analis' => $loc['assigned_analis'] ?? '-',
            'is_done' => $loc['is_done'] ?? false,
            'verif_status' => $loc['verif_status'] ?? null,
            'verif_note' => $loc['verif_note'] ?? null,
            'sync_key' => implode('|', [
              $loc['koding_item_id'] ?? '',
              $loc['pengujian_dokumen_parameter_id'] ?? '',
              $loc['service_parameter_id'] ?? '',
            ]),
          ];
        }
      }
      $orderRows = collect($orderRows)
        ->sortBy(function ($row) {
          $lokasi = strtolower((string) ($row['lokasi'] ?? ''));
          $dokumen = strtolower((string) ($row['dokumen_label'] ?? ''));
          $kategori = strtolower((string) ($row['kategori'] ?? ''));
          $parameter = strtolower((string) ($row['parameter'] ?? ''));
          return $lokasi . '|' . $dokumen . '|' . $kategori . '|' . $parameter;
        })
        ->values();
      $searchRows = array_merge($searchRows, $orderRows->map(function ($row) use ($routePrefix, $currentUserId) {
        $payload = $row['location_payload'] ?? [];
        $assignedId = (int) ($payload['assigned_user_id'] ?? 0);
        $isMine = $assignedId > 0 && $assignedId === (int) $currentUserId;
        return array_merge($row, [
          'search_key' => $row['sync_key'] ?? '',
          'search_koding' => strtolower((string) ($row['koding'] ?? '')),
          'is_mine' => $isMine,
          'is_taken_by_other' => $assignedId > 0 && !$isMine,
          'assign_url' => route($routePrefix . '.prepanalisa.assign', $row['permohonan_id']),
          'reset_url' => route($routePrefix . '.prepanalisa.reset-action', $row['permohonan_id']),
          'save_url' => route($routePrefix . '.prepanalisa.draft', $row['permohonan_id']),
          'done_url' => route($routePrefix . '.prepanalisa.item-done', $row['permohonan_id']),
        ]);
      })->all());
      $locCounts = [];
      $docCounts = [];
      $catCounts = [];
      $parameterGroupCounts = [];
      foreach ($orderRows as $row) {
        if (!empty($row['sync_key'])) {
          $locationPayloadStore[$row['sync_key']] = $row['location_payload'] ?? [];
        }
        $locKey = $row['lokasi'];
        $docKey = $row['lokasi'] . '::' . $row['dokumen_label'];
        $catKey = $row['lokasi'] . '::' . $row['dokumen_label'] . '::' . ($row['kategori'] ?? '-');
        $locCounts[$locKey] = ($locCounts[$locKey] ?? 0) + 1;
        $docCounts[$docKey] = ($docCounts[$docKey] ?? 0) + 1;
        $catCounts[$catKey] = ($catCounts[$catKey] ?? 0) + 1;
        $parameterGroupKey = $row['parameter_group_key'] ?? '';
        $parameterGroupCounts[$parameterGroupKey] = ($parameterGroupCounts[$parameterGroupKey] ?? 0) + 1;
      }
      $printedLoc = [];
      $printedDoc = [];
      $printedCat = [];
      $samplingDate = $order['jadwal_mulai'] ?? null;
      $receiptDate = $order['jadwal_selesai'] ?? null;
      $samplingLabel = $samplingDate ? \Illuminate\Support\Carbon::parse($samplingDate)->format('d-m-Y') : '-';
      $receiptLabel = $receiptDate ? \Illuminate\Support\Carbon::parse($receiptDate)->format('d-m-Y') : '-';
      $permohonanIds = collect([$order['permohonan_id'] ?? null])->filter()->values();
      $docUrl = $permohonanIds->isNotEmpty()
        ? route($routePrefix . '.prepanalisa.penyerahan-preview', ['permohonan_ids' => $permohonanIds->implode(',')])
        : '';
      $masukAtUnix = (int) ($order['masuk_at_unix'] ?? 0);
    @endphp
    <div class="accordion-item border-0 shadow-sm rounded-4 mb-3" data-card>
      <h2 class="accordion-header" id="{{ $orderKey }}-header">
        <button class="accordion-button collapsed rounded-4 bg-primary text-white" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $orderKey }}-collapse" aria-expanded="false" aria-controls="{{ $orderKey }}-collapse" style="background-color:#15406A !important;">
          <div class="prepanalisa-header-content">
            <div class="prepanalisa-order-code">{{ $orderCode }}</div>
            <div class="prepanalisa-company-name">{{ $companyName }}</div>
            <div class="prepanalisa-meta-row">
              <div class="prepanalisa-arrival-time">
                <i class="bi bi-clock"></i>
                <span data-relative-time data-time-unix="{{ $masukAtUnix ?: '' }}">-</span>
              </div>
            </div>
          </div>
        </button>
      </h2>
      <div id="{{ $orderKey }}-collapse" class="accordion-collapse collapse" aria-labelledby="{{ $orderKey }}-header" data-bs-parent="#prepanalisaCompanyAccordion">
        <div class="accordion-body">
          <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <div class="small text-muted">Dokumen Penyerahan Sampel Faktor Kimia.</div>
            <button type="button" class="btn btn-outline-primary btn-sm" data-doc-preview data-doc-url="{{ $docUrl }}">
              Lihat Dokumen
            </button>
          </div>
          <div class="table-responsive prepanalisa-grid-wrap">
            <table class="table table-sm align-middle mb-0 prepanalisa-grid-table">
              <thead class="table-light">
                <tr>
                  <th style="width: 12%;">Lokasi</th>
                  <th style="width: 18%;">Dokumen</th>
                  <th style="width: 15%;">ID Koding</th>
                  <th style="width: 8%;" class="text-center">Kategori</th>
                  <th style="width: 22%;">Parameter</th>
                  <th style="width: 12%;" class="text-center">
                    <div class="d-inline-flex align-items-center gap-2">
                      <span>Aksi</span>
                      <button type="button"
                              class="btn btn-outline-danger btn-sm p-1 d-inline-flex align-items-center justify-content-center"
                              style="width:30px;height:30px;"
                              data-header-reset-aksi
                              title="Reset Aksi Parameter"
                              aria-label="Reset Aksi Parameter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                      </button>
                    </div>
                  </th>
                  <th style="width: 8%;">Nama Analis</th>
                  <th style="width: 5%;" class="text-center">Selesai</th>
                </tr>
              </thead>
              <tbody>
                @foreach($orderRows as $row)
                  @php
                    $locKey = $row['lokasi'];
                    $docKey = $row['lokasi'] . '::' . $row['dokumen_label'];
                    $catKey = $row['lokasi'] . '::' . $row['dokumen_label'] . '::' . ($row['kategori'] ?? '-');
                    $isCategoryStart = empty($printedCat[$catKey]);
                    $syncKey = $row['sync_key'] ?? '';
                  @endphp
                  <tr class="{{ $isCategoryStart && !$loop->first ? 'category-separator' : '' }}"
                      data-sync-key="{{ $syncKey }}"
                      data-parameter-group-key="{{ $row['parameter_group_key'] ?? '' }}">
                    @if(empty($printedLoc[$locKey]))
                      @php $printedLoc[$locKey] = true; @endphp
                      <td class="fw-semibold align-middle prepanalisa-lokasi-cell" rowspan="{{ $locCounts[$locKey] ?? 1 }}">{{ $row['lokasi'] }}</td>
                    @endif
                    @if(empty($printedDoc[$docKey]))
                      @php $printedDoc[$docKey] = true; @endphp
                      <td class="small text-muted align-middle prepanalisa-doc-cell" rowspan="{{ $docCounts[$docKey] ?? 1 }}">
                        <div class="fw-semibold text-dark">{{ $row['dokumen_label'] }}</div>
                        @if(!empty($row['dokumen_files']))
                          <div class="small text-muted">
                            @foreach($row['dokumen_files'] as $file)
                              @if(!empty($file['url']))
                                <div><a href="{{ $file['url'] }}" target="_blank">{{ $file['name'] ?? 'File' }}</a></div>
                              @else
                                <div>{{ $file['name'] ?? 'File' }}</div>
                              @endif
                            @endforeach
                          </div>
                        @else
                          <div class="small text-muted">Belum ada dokumen</div>
                        @endif
                      </td>
                    @endif
                    <td class="fw-semibold prepanalisa-koding-cell"><code>{{ $row['koding_display'] ?? '-' }}</code></td>
                    @if($isCategoryStart)
                      @php $printedCat[$catKey] = true; @endphp
                      <td class="text-center fw-semibold align-middle prepanalisa-kategori-cell" rowspan="{{ $catCounts[$catKey] ?? 1 }}">{{ $row['kategori'] }}</td>
                    @endif
                    <td class="fw-semibold prepanalisa-parameter-cell">
                      {{ $row['parameter'] }}
                      @if(($row['verif_status'] ?? '') === 'revisi')
                        <span class="badge text-bg-danger ms-1">Revisi</span>
                      @endif
                    </td>
                    <td class="text-center prepanalisa-action-cell">
                      @php
                        $assignedId = (int) ($row['location_payload']['assigned_user_id'] ?? 0);
                        $isMine = $assignedId > 0 && $assignedId === (int) $currentUserId;
                        $isTakenByOther = $assignedId > 0 && !$isMine;
                      @endphp
                      <div class="prepanalisa-action-stack">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                data-pilih-parameter
                                data-assign-url="{{ route($routePrefix . '.prepanalisa.assign', $row['permohonan_id']) }}"
                                {{ $assignedId ? 'disabled' : '' }}>
                          <i class="bi bi-hand-index-thumb me-1"></i>
                          {{ $assignedId ? 'Dipilih' : (($parameterGroupCounts[$row['parameter_group_key']] ?? 1) > 1 ? 'Pilih Semua' : 'Pilih') }}
                        </button>
                        <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                data-hitung-parameter
                                data-reset-url="{{ route($routePrefix . '.prepanalisa.reset-action', $row['permohonan_id']) }}"
                                data-bs-toggle="modal"
                                data-bs-target="#parameterActionModal"
                                data-order="{{ $row['koding_display'] ?? '-' }}"
                                data-param="{{ $row['parameter'] }}"
                                data-param-category="{{ $row['kategori'] }}"
                                data-save-url="{{ route($routePrefix . '.prepanalisa.draft', $row['permohonan_id']) }}"
                                data-location-key="{{ $row['location_key'] }}"
                                {{ $assignedId && !$isMine ? 'disabled' : '' }}
                                {{ !$assignedId ? 'disabled' : '' }}>
                          <i class="bi bi-calculator me-1"></i>
                          {{ ($parameterGroupCounts[$row['parameter_group_key']] ?? 1) > 1 ? 'Hitung Bersama' : 'Hitung' }}
                        </button>
                      </div>
                    </td>
                    <td class="prepanalisa-analis-cell">
                      <span data-assign-name>{{ $row['assigned_analis'] }}</span>
                    </td>
                    <td class="text-center prepanalisa-done-cell">
                      <div class="form-check form-switch d-inline-flex align-items-center m-0">
                        <input type="checkbox"
                               class="form-check-input"
                               data-prep-done
                               data-item-done-url="{{ route($routePrefix . '.prepanalisa.item-done', $row['permohonan_id']) }}"
                               data-koding-item-id="{{ $row['location_payload']['koding_item_id'] ?? '' }}"
                               data-doc-param-id="{{ $row['location_payload']['pengujian_dokumen_parameter_id'] ?? '' }}"
                               data-service-param-id="{{ $row['location_payload']['service_parameter_id'] ?? '' }}"
                               @if(!empty($row['is_done'])) checked @endif>
                      </div>
                    </td>
                  </tr>
                @endforeach
                @if($orderRows->isEmpty())
                  <tr>
                    <td colspan="8" class="text-center text-muted small">Belum ada data preparasi analisa.</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
          @php
            $noteList = collect($order['revisi_notes'] ?? []);
          @endphp
          @if($noteList->isNotEmpty())
            <div class="mt-3 p-2 border rounded-3 bg-light">
              <div class="fw-semibold text-danger mb-1">Catatan Revisi</div>
              <ul class="mb-0">
                @foreach($noteList as $note)
                  <li class="small text-danger">{{ $note }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <div class="d-flex justify-content-end align-items-center mt-3">
            <button type="button"
                    class="btn btn-primary btn-sm"
                    data-verify-submit
                    data-verify-url="{{ route($routePrefix . '.prepanalisa.submit') }}"
                    data-permohonan-ids="{{ $permohonanIds->implode(',') }}"
                    disabled>
              Teruskan QC
            </button>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="text-center text-muted py-4">Belum ada data preparasi analisa.</div>
  @endforelse
</div>

<div class="d-none mt-3" data-search-results>
  <div class="accordion-item border-0 shadow-sm rounded-4 mb-3">
    <div class="accordion-body">
      <div class="table-responsive prepanalisa-grid-wrap">
        <table class="table table-sm align-middle mb-0 prepanalisa-grid-table">
          <thead class="table-light">
            <tr>
              <th style="width: 12%;">Lokasi</th>
              <th style="width: 18%;">Dokumen</th>
              <th style="width: 15%;">ID Koding</th>
              <th style="width: 8%;" class="text-center">Kategori</th>
              <th style="width: 22%;">Parameter</th>
              <th style="width: 12%;" class="text-center">Aksi</th>
              <th style="width: 8%;">Nama Analis</th>
              <th style="width: 5%;" class="text-center">Selesai</th>
            </tr>
          </thead>
          <tbody>
            @foreach($searchRows as $row)
              <tr data-search-row
                  data-sync-key="{{ $row['search_key'] }}"
                  data-parameter-group-key="{{ $row['parameter_group_key'] ?? '' }}"
                  data-koding="{{ $row['search_koding'] }}">
                <td class="fw-semibold align-middle prepanalisa-lokasi-cell">{{ $row['lokasi'] }}</td>
                <td class="small text-muted align-middle prepanalisa-doc-cell">
                  <div class="fw-semibold text-dark">{{ $row['dokumen_label'] }}</div>
                  @if(!empty($row['dokumen_files']))
                    <div class="small text-muted">
                      @foreach($row['dokumen_files'] as $file)
                        @if(!empty($file['url']))
                          <div><a href="{{ $file['url'] }}" target="_blank">{{ $file['name'] ?? 'File' }}</a></div>
                        @else
                          <div>{{ $file['name'] ?? 'File' }}</div>
                        @endif
                      @endforeach
                    </div>
                  @else
                    <div class="small text-muted">Belum ada dokumen</div>
                  @endif
                </td>
                <td class="fw-semibold prepanalisa-koding-cell"><code>{{ $row['koding_display'] ?? '-' }}</code></td>
                <td class="text-center fw-semibold align-middle prepanalisa-kategori-cell">{{ $row['kategori'] }}</td>
                <td class="fw-semibold prepanalisa-parameter-cell">
                  {{ $row['parameter'] }}
                  @if(($row['verif_status'] ?? '') === 'revisi')
                    <span class="badge text-bg-danger ms-1">Revisi</span>
                  @endif
                </td>
                <td class="text-center prepanalisa-action-cell">
                  <div class="prepanalisa-action-stack">
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            data-pilih-parameter
                            data-assign-url="{{ $row['assign_url'] }}"
                            {{ !empty($row['location_payload']['assigned_user_id']) ? 'disabled' : '' }}>
                      <i class="bi bi-hand-index-thumb me-1"></i>
                      {{ !empty($row['location_payload']['assigned_user_id']) ? 'Dipilih' : 'Pilih' }}
                    </button>
                    <button type="button"
                            class="btn btn-outline-primary btn-sm"
                            data-hitung-parameter
                            data-reset-url="{{ $row['reset_url'] }}"
                            data-bs-toggle="modal"
                            data-bs-target="#parameterActionModal"
                            data-order="{{ $row['koding_display'] ?? '-' }}"
                            data-param="{{ $row['parameter'] }}"
                            data-param-category="{{ $row['kategori'] }}"
                            data-save-url="{{ $row['save_url'] }}"
                            data-location-key="{{ $row['location_key'] }}"
                            {{ !empty($row['is_taken_by_other']) ? 'disabled' : '' }}
                            {{ empty($row['location_payload']['assigned_user_id']) ? 'disabled' : '' }}>
                      <i class="bi bi-calculator me-1"></i> Hitung
                    </button>
                  </div>
                </td>
                <td class="prepanalisa-analis-cell">
                  <span data-assign-name>{{ $row['assigned_analis'] }}</span>
                </td>
                <td class="text-center prepanalisa-done-cell">
                  <div class="form-check form-switch d-inline-flex align-items-center m-0">
                    <input type="checkbox"
                           class="form-check-input"
                           data-prep-done
                           data-item-done-url="{{ $row['done_url'] }}"
                           data-koding-item-id="{{ $row['location_payload']['koding_item_id'] ?? '' }}"
                           data-doc-param-id="{{ $row['location_payload']['pengujian_dokumen_parameter_id'] ?? '' }}"
                           data-service-param-id="{{ $row['location_payload']['service_parameter_id'] ?? '' }}"
                           @if(!empty($row['is_done'])) checked @endif>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="text-center text-muted d-none mt-3" data-search-empty>Tidak ada ID koding yang cocok.</div>

<div class="modal fade" id="parameterActionModal" tabindex="-1" aria-labelledby="parameterActionModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="parameterActionModalLabel">Aksi Parameter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="small text-muted mb-2">ID Koding: <span data-action-order>-</span></div>
        <div class="fw-semibold" data-action-param>Parameter</div>
        <div class="mb-3" data-action-category>Kategori: -</div>
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
          <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-primary active" data-action-type="sk-pm">
              Input SK PM
            </button>
            <button type="button" class="btn btn-outline-primary d-none" data-action-type="std-kalibrasi">
              Std Kalibrasi
            </button>
            <button type="button" class="btn btn-outline-primary" data-action-type="hasil-baca">
              Input Hasil Baca
            </button>
            <button type="button" class="btn btn-outline-primary" data-action-type="hasil-perhitungan">
              Input Hasil Perhitungan
            </button>
          </div>
          <div class="d-none" data-action-reset-row>
            <button type="button" class="btn btn-sm btn-primary" data-action-reset>
              Reset Semua
            </button>
          </div>
        </div>
        <div class="small text-muted d-none mb-3" data-action-reference-meta>
          Input data tetap dilakukan manual di modal. Rumus dan nilai acuan SO2 Ambien mengikuti template perhitungan internal, bukan import isi Excel.
        </div>
        <div class="d-none" data-pb-curve-panel>
          <div class="pb-curve-card pb-curve-chart-card">
            <div class="pb-curve-head mb-2">
              <div>
                <div class="pb-curve-title" data-pb-curve-chart-title>Grafik Kurva Logam</div>
                <div class="pb-curve-note" data-pb-curve-chart-note>Grafik diletakkan di samping tabel agar pembacaan data dan visual lebih seimbang.</div>
              </div>
            </div>
            <div data-pb-curve-plot>
              <svg viewBox="0 0 560 340" preserveAspectRatio="xMidYMid meet" data-pb-curve-svg aria-label="Kurva kalibrasi logam"></svg>
              <div data-pb-curve-overlay-card>
                <div data-pb-overlay-y>y = 0,0000</div>
                <div data-pb-overlay-x>x = 0,0000</div>
                <div data-pb-overlay-r2>R² = -</div>
              </div>
            </div>
          </div>
          <div class="pb-curve-card pb-curve-table-card">
            <div class="pb-curve-head">
              <div>
                <div class="pb-curve-title" data-pb-curve-table-title>Tabel Standar Logam</div>
                <div class="pb-curve-note" data-pb-curve-table-note>Input volume dan hasil baca manual. Nilai kandungan default tetap bisa diubah.</div>
              </div>
              <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-success" data-pb-curve-export-excel>
                  Unduh Excel
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" data-pb-curve-export-pdf>
                  Unduh PDF
                </button>
              </div>
              <div class="small text-muted" data-pb-curve-formula>
                <label class="pb-curve-metric">
                  <span>Y =</span>
                  <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-curve-y placeholder="0,0000">
                </label>
                <label class="pb-curve-metric">
                  <span>X =</span>
                  <input type="text" class="form-control form-control-sm" data-pb-curve-x readonly tabindex="-1">
                </label>
                <span class="pb-curve-metric" data-pb-curve-r2>R² = -</span>
              </div>
              <div data-pb-curve-latest>Acuan aktif terbaru: Y = 0,0000 | X = 0,0000</div>
              <div class="d-none justify-content-end mt-2" data-pb-axis-toggle-row>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-pb-axis-toggle aria-expanded="false">
                  <i class="bi bi-sliders me-1"></i>Option
                  <i class="bi bi-chevron-down ms-1" data-pb-axis-toggle-chevron></i>
                </button>
              </div>
              <div class="pb-curve-axis-options" data-pb-axis-options>
                <div class="pb-curve-axis-title">Axis Options</div>
                <div class="pb-curve-axis-grid">
                  <label class="pb-curve-axis-field">
                    <span>X Min</span>
                    <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-axis="x-min" placeholder="0">
                  </label>
                  <label class="pb-curve-axis-field">
                    <span>X Max</span>
                    <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-axis="x-max" placeholder="10">
                  </label>
                  <label class="pb-curve-axis-field">
                    <span>X Major</span>
                    <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-axis="x-major" placeholder="2,5">
                  </label>
                  <label class="pb-curve-axis-field">
                    <span>X Minor</span>
                    <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-axis="x-minor" placeholder="2,5">
                  </label>
                  <label class="pb-curve-axis-field">
                    <span>Y Min</span>
                    <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-axis="y-min" placeholder="0">
                  </label>
                  <label class="pb-curve-axis-field">
                    <span>Y Max</span>
                    <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-axis="y-max" placeholder="0,3">
                  </label>
                  <label class="pb-curve-axis-field">
                    <span>Y Major</span>
                    <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-axis="y-major" placeholder="0,05">
                  </label>
                  <label class="pb-curve-axis-field">
                    <span>Y Minor</span>
                    <input type="text" class="form-control form-control-sm" inputmode="decimal" data-pb-axis="y-minor" placeholder="0,01">
                  </label>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width: 18%;">No sampel</th>
                    <th style="width: 14%;">Vol</th>
                    <th style="width: 16%;">Waktu baca</th>
                    <th style="width: 16%;">Hasil baca</th>
                    <th style="width: 20%;">Kandungan (mg/L)</th>
                    <th style="width: 16%;">Ket</th>
                  </tr>
                </thead>
                <tbody data-pb-curve-rows></tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="border rounded-3 d-none d-flex flex-column" data-action-grid style="min-height: 260px;">
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0" data-action-table>
              <thead class="table-light" data-action-head></thead>
              <tbody data-action-rows></tbody>
            </table>
          </div>
          @php
            $notes = collect($orderRows)
              ->filter(fn($row) => ($row['verif_status'] ?? '') === 'revisi' && !empty($row['verif_note']))
              ->map(fn($row) => $row['parameter'] . ' â€” ' . $row['verif_note'])
              ->values();
          @endphp
          @if($notes->isNotEmpty())
            <div class="mt-3 p-2 border rounded-3 bg-light">
              <div class="fw-semibold text-danger mb-1">Catatan Revisi</div>
              <ul class="mb-0">
                @foreach($notes as $note)
                  <li class="small text-danger">{{ $note }}</li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
        <div class="d-none mt-2" data-action-delete-hint>
          <div class="alert alert-warning py-2 mb-0 small">
            Mode hapus aktif. Pilih baris lalu klik tombol Hapus lagi untuk menghapus.
          </div>
        </div>
        <div class="d-none d-flex justify-content-between align-items-center mt-3 gap-3 flex-wrap" data-action-footer>
          <div class="d-none d-flex align-items-center gap-2 flex-wrap" data-action-cons>
            <span class="text-muted small" data-cons-label>Cons =</span>
            <input type="text" class="form-control form-control-sm" style="width:120px" placeholder="0.0000000" data-cons-a>
            <span class="text-muted small" data-cons-op>+</span>
            <input type="text" class="form-control form-control-sm" style="width:120px" placeholder="0.0000002" data-cons-b>
            <span class="text-muted small" data-cons-tail>Abs</span>
            <span class="text-muted small d-none" data-hc-y-label>Y =</span>
            <input type="text" class="form-control form-control-sm d-none" style="width:140px" data-hc-y readonly tabindex="-1">
            <span class="w-100 d-none" data-hc-break></span>
            <span class="text-muted small d-none" data-hc-x-label>X =</span>
            <input type="text" class="form-control form-control-sm d-none" style="width:120px" data-hc-x readonly tabindex="-1">
            <span class="text-muted small d-none" data-hc-note>Benzene = L.Area Ã— X</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-action-kandbl>
              Hitung Kand Spl
            </button>
          </div>
          <div class="d-flex flex-wrap gap-2 ms-auto">
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-action-blanko>
              Input Blanko
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-action-average>
              Hitung Rata Rata
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-action-delete>
              Hapus
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-action-calc>
              Hitung Kadar (ppm)
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-action-print>
              Cetak Hasil Analisis
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary d-none" data-action-close data-bs-dismiss="modal">
              Keluar
            </button>
            <button type="button" class="btn btn-sm btn-primary" data-action-save>
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const currentUserName = @json(optional(auth()->user())->name ?? '-');
  const currentUserId = Number(@json((int) (auth()->id() ?? 0)));
  const currentUserRole = @json((string) (auth()->user()?->role ?? ''));
  const currentUserSignature = @json($currentUserSignatureDataUrl);
  const stdKalibrasiExcelExportUrl = @json(route($routePrefix . '.prepanalisa.std-kalibrasi.export-excel'));
  const formulaReferencesUrl = @json($formulaReferencesUrl ?? null);
  const initialLocationPayloadStore = @json($locationPayloadStore);
  const locationPayloadStore = Object.assign({}, initialLocationPayloadStore || {});
  const kodingInput = document.querySelector('[data-search-koding]');
  const resetBtn = document.querySelector('[data-search-reset]');
  const cards = document.querySelectorAll('[data-card]');
  const accordion = document.getElementById('prepanalisaCompanyAccordion');
  const searchResults = document.querySelector('[data-search-results]');
  const searchRows = document.querySelectorAll('[data-search-row]');
  const emptyState = document.querySelector('[data-search-empty]');
  let selectedResetContext = null;

  const formatRelativeTime = (unixTime) => {
    const ts = Number.parseInt(String(unixTime || ''), 10);
    if (Number.isNaN(ts) || ts <= 0) return '-';
    const seconds = Math.max(0, Math.floor(Date.now() / 1000) - ts);
    if (seconds < 60) return 'baru saja';
    if (seconds < 3600) return `${Math.floor(seconds / 60)} mnt yang lalu`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} jam yang lalu`;
    return `${Math.floor(seconds / 86400)} hari yang lalu`;
  };

  const refreshRelativeTimes = () => {
    document.querySelectorAll('[data-relative-time]').forEach((node) => {
      const ts = node.getAttribute('data-time-unix');
      node.textContent = formatRelativeTime(ts);
    });
  };

  const filterCards = () => {
    const kodingVal = (kodingInput?.value || '').toLowerCase().trim();
    let visible = 0;

    if (!kodingVal) {
      accordion?.classList.remove('d-none');
      searchResults?.classList.add('d-none');
      searchRows.forEach((row) => row.classList.add('d-none'));
      if (emptyState) emptyState.classList.add('d-none');
      return;
    }

    accordion?.classList.add('d-none');
    searchResults?.classList.remove('d-none');

    searchRows.forEach((row) => {
      const koding = row.getAttribute('data-koding') || '';
      const show = koding.includes(kodingVal);
      row.classList.toggle('d-none', !show);
      if (show) visible += 1;
    });

    if (emptyState) {
      const hasRows = searchRows.length > 0;
      emptyState.classList.toggle('d-none', !hasRows || visible > 0);
    }
  };

  const getSyncKeyFromNode = (node) => {
    if (!node) return '';
    const row = node.closest('[data-sync-key]');
    return row?.getAttribute('data-sync-key') || '';
  };

  const getSyncedRows = (syncKey) => {
    if (!syncKey) return [];
    return Array.from(document.querySelectorAll(`[data-sync-key="${syncKey.replace(/"/g, '\\"')}"]`));
  };

  const clonePayload = (payload) => {
    if (!payload || typeof payload !== 'object') return {};
    try {
      return JSON.parse(JSON.stringify(payload));
    } catch (error) {
      return { ...payload };
    }
  };

  const getLocationPayload = (node) => {
    const syncKey = getSyncKeyFromNode(node) || node?.getAttribute('data-sync-key') || '';
    if (!syncKey) return {};
    return clonePayload(locationPayloadStore[syncKey] || {});
  };

  const getParameterGroupKey = (node) => {
    if (!node) return '';
    const row = node.closest('[data-parameter-group-key]');
    return row?.getAttribute('data-parameter-group-key') || '';
  };

  const getGroupSyncKeys = (groupKey) => {
    if (!groupKey) return [];
    const keys = [];
    document.querySelectorAll('[data-parameter-group-key]').forEach((row) => {
      if (row.getAttribute('data-parameter-group-key') !== groupKey) return;
      const syncKey = row.getAttribute('data-sync-key') || '';
      if (syncKey && !keys.includes(syncKey)) keys.push(syncKey);
    });
    return keys;
  };

  const getGroupPayloads = (node) => {
    const groupKey = getParameterGroupKey(node);
    const payloads = getGroupSyncKeys(groupKey)
      .map((syncKey) => clonePayload(locationPayloadStore[syncKey] || {}))
      .filter((payload) => payload?.koding_item_id && payload?.pengujian_dokumen_parameter_id);
    return payloads.length ? payloads : [getLocationPayload(node)].filter((payload) => payload?.koding_item_id);
  };

  const setSyncedLocationPayload = (syncKey, payload) => {
    if (syncKey) {
      locationPayloadStore[syncKey] = clonePayload(payload);
    }
    getSyncedRows(syncKey).forEach((row) => {
      setRowWorkState(row, payload);
    });
    const row = getSyncedRows(syncKey)[0];
    const groupKey = getParameterGroupKey(row);
    if (groupKey) refreshGroupWorkState(groupKey);
  };

  const syncDoneState = (syncKey, checked, disabled = null) => {
    getSyncedRows(syncKey).forEach((row) => {
      const doneInput = row.querySelector('[data-prep-done]');
      if (!doneInput) return;
      doneInput.checked = checked;
      if (disabled !== null) doneInput.disabled = disabled;
    });
  };

  kodingInput?.addEventListener('input', filterCards);
  resetBtn?.addEventListener('click', () => {
    if (kodingInput) kodingInput.value = '';
    filterCards();
  });

  filterCards();
  refreshRelativeTimes();

  const toAbsoluteUrl = (url) => {
    if (!url) return '';
    if (/^data:/i.test(url)) return url;
    if (/^https?:\/\//i.test(url)) return url;
    if (url.startsWith('/')) return `${window.location.origin}${url}`;
    return `${window.location.origin}/${url}`;
  };

  const notify = (type, text) => {
    if (window.Swal) {
      const iconMap = {
        success: 'success',
        error: 'error',
        info: 'info',
        warning: 'warning',
      };
      const titleMap = {
        success: 'Berhasil',
        error: 'Gagal',
        info: 'Info',
        warning: 'Peringatan',
      };
      const isActionModalOpen = !!(actionModal && actionModal.classList.contains('show'));
      return Swal.fire({
        icon: iconMap[type] || 'info',
        title: titleMap[type] || 'Info',
        text,
        confirmButtonText: 'OK',
        target: isActionModalOpen ? actionModal : document.body,
      });
    }
    if (window.notify) {
      return window.notify(type, text);
    }
    console.log(text);
    return Promise.resolve();
  };

  const updateVerifyState = (container) => {
    if (!container) return;
    const checks = Array.from(container.querySelectorAll('[data-prep-done]'));
    const btn = container.querySelector('[data-verify-submit]');
    if (!btn) return;
    if (!checks.length) {
      btn.disabled = true;
      return;
    }
    btn.disabled = checks.some((input) => !input.checked);
  };

  const setRowWorkState = (row, payload) => {
    if (!row) return;
    const assignedId = Number(payload?.assigned_user_id || 0);
    const assignedName = (payload?.assigned_analis || '-').toString().trim() || '-';
    const isMine = assignedId > 0 && assignedId === currentUserId;
    const isTakenByOther = assignedId > 0 && !isMine;

    const assignNameEl = row.querySelector('[data-assign-name]');
    if (assignNameEl) assignNameEl.textContent = assignedName;

    const pilihBtn = row.querySelector('[data-pilih-parameter]');
    const hitungBtn = row.querySelector('[data-hitung-parameter]');
    const doneInput = row.querySelector('[data-prep-done]');
    if (pilihBtn) {
      pilihBtn.disabled = assignedId > 0;
      pilihBtn.innerHTML = assignedId > 0
        ? '<i class="bi bi-check2-circle me-1"></i>Dipilih'
        : '<i class="bi bi-hand-index-thumb me-1"></i>Pilih';
    }
    if (hitungBtn) {
      hitungBtn.disabled = assignedId === 0 || isTakenByOther;
      if (isTakenByOther) {
        hitungBtn.title = `Parameter sedang dikerjakan ${assignedName}`;
      } else if (assignedId === 0) {
        hitungBtn.title = 'Pilih parameter terlebih dahulu';
      } else {
        hitungBtn.title = '';
      }
    }
    if (doneInput) {
      doneInput.disabled = assignedId === 0 || (isTakenByOther && currentUserRole !== 'superadmin');
      doneInput.checked = !!payload?.is_done;
    }
  };

  const refreshGroupWorkState = (groupKey) => {
    if (!groupKey) return;
    const syncKeys = getGroupSyncKeys(groupKey);
    const payloads = syncKeys
      .map((syncKey) => locationPayloadStore[syncKey] || {})
      .filter((payload) => payload?.koding_item_id);
    if (!payloads.length) return;

    const allMine = payloads.every((payload) => Number(payload?.assigned_user_id || 0) === currentUserId);
    const hasOther = payloads.some((payload) => {
      const assignedId = Number(payload?.assigned_user_id || 0);
      return assignedId > 0 && assignedId !== currentUserId;
    });
    const assignedCount = payloads.filter((payload) => Number(payload?.assigned_user_id || 0) > 0).length;
    const isGroup = payloads.length > 1;

    document.querySelectorAll('[data-parameter-group-key]').forEach((row) => {
      if (row.getAttribute('data-parameter-group-key') !== groupKey) return;
      const pilihBtn = row.querySelector('[data-pilih-parameter]');
      const hitungBtn = row.querySelector('[data-hitung-parameter]');
      const doneInput = row.querySelector('[data-prep-done]');

      if (pilihBtn) {
        pilihBtn.disabled = hasOther || allMine;
        const label = allMine
          ? 'Dipilih'
          : (assignedCount > 0 ? 'Lengkapi' : (isGroup ? 'Pilih Semua' : 'Pilih'));
        pilihBtn.innerHTML = allMine
          ? `<i class="bi bi-check2-circle me-1"></i>${label}`
          : `<i class="bi bi-hand-index-thumb me-1"></i>${label}`;
        if (hasOther) pilihBtn.title = 'Sebagian lokasi parameter sedang dikerjakan analis lain';
      }
      if (hitungBtn) {
        hitungBtn.disabled = !allMine;
        hitungBtn.innerHTML = `<i class="bi bi-calculator me-1"></i>${isGroup ? 'Hitung Bersama' : 'Hitung'}`;
        hitungBtn.title = allMine ? '' : 'Pilih seluruh lokasi parameter terlebih dahulu';
      }
      if (doneInput) {
        doneInput.disabled = !allMine && currentUserRole !== 'superadmin';
      }
    });
  };

  const setSelectedResetContext = (row, payload, resetUrl) => {
    document.querySelectorAll('#prepanalisaCompanyAccordion tr[data-reset-selected="1"]').forEach((item) => {
      item.removeAttribute('data-reset-selected');
      item.classList.remove('table-active');
    });
    if (!row) {
      selectedResetContext = null;
      return;
    }
    row.setAttribute('data-reset-selected', '1');
    row.classList.add('table-active');
    selectedResetContext = { row, payload, resetUrl };
  };

  document.querySelectorAll('.accordion-item').forEach((item) => {
    updateVerifyState(item);
  });

  document.addEventListener('change', async (event) => {
    const checkbox = event.target.closest('[data-prep-done]');
    if (!checkbox) return;
    const groupKey = getParameterGroupKey(checkbox);
    const groupPayloads = getGroupPayloads(checkbox);
    const syncKeys = getGroupSyncKeys(groupKey);
    const url = checkbox.getAttribute('data-item-done-url') || '';
    if (!url || !groupPayloads.length) return;
    const original = checkbox.checked;
    syncKeys.forEach((syncKey) => syncDoneState(syncKey, checkbox.checked, true));
    const formData = new FormData();
    formData.append('items', JSON.stringify(groupPayloads.map((payload) => ({
      koding_item_id: payload.koding_item_id,
      pengujian_dokumen_parameter_id: payload.pengujian_dokumen_parameter_id,
    }))));
    formData.append('is_done', checkbox.checked ? '1' : '0');
    formData.append('_token', getCsrfToken());
    try {
      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'same-origin',
      });
      if (!response.ok) {
        throw new Error('Gagal memperbarui status selesai.');
      }
      // no popup on success
    } catch (err) {
      checkbox.checked = !original;
      syncKeys.forEach((syncKey) => syncDoneState(syncKey, checkbox.checked));
      await notify('error', err.message || 'Gagal memperbarui status selesai.');
    } finally {
      syncKeys.forEach((syncKey) => {
        const payload = locationPayloadStore[syncKey] || {};
        payload.is_done = checkbox.checked;
        locationPayloadStore[syncKey] = payload;
        syncDoneState(syncKey, checkbox.checked, false);
      });
      refreshGroupWorkState(groupKey);
      document.querySelectorAll('#prepanalisaCompanyAccordion .accordion-item').forEach((item) => {
        updateVerifyState(item);
      });
    }
  });

  document.addEventListener('click', async (event) => {
    const pickBtn = event.target.closest('[data-pilih-parameter]');
    if (pickBtn) {
      event.preventDefault();
      if (pickBtn.disabled) return;

      const row = pickBtn.closest('tr');
      const assignUrl = pickBtn.getAttribute('data-assign-url') || '';
      const groupPayloads = getGroupPayloads(pickBtn);
      if (!assignUrl || !groupPayloads.length) {
        await notify('error', 'Data parameter tidak lengkap untuk dipilih.');
        return;
      }

      pickBtn.disabled = true;
      const formData = new FormData();
      formData.append('items', JSON.stringify(groupPayloads.map((payload) => ({
        koding_item_id: payload.koding_item_id,
        pengujian_dokumen_parameter_id: payload.pengujian_dokumen_parameter_id,
        service_parameter_id: payload.service_parameter_id,
        kode_koding: payload.koding || '',
      }))));
      formData.append('_token', getCsrfToken());
      try {
        const response = await fetch(assignUrl, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
          },
          credentials: 'same-origin',
        });
        let data = {};
        if (response.headers.get('content-type')?.includes('application/json')) {
          data = await response.json();
        }
        if (!response.ok) {
          throw new Error(data.message || 'Gagal memilih parameter.');
        }

        groupPayloads.forEach((payload) => {
          const syncKey = [
            payload.koding_item_id || '',
            payload.pengujian_dokumen_parameter_id || '',
            payload.service_parameter_id || '',
          ].join('|');
          const nextPayload = {
            ...payload,
            assigned_user_id: Number(data.assigned_user_id || currentUserId),
            assigned_analis: data.assigned_name || currentUserName,
            is_done: false,
          };
          setSyncedLocationPayload(syncKey, nextPayload);
          syncDoneState(syncKey, false);
        });
        const hitungBtn = row?.querySelector('[data-hitung-parameter]');
        if (row && hitungBtn) {
          const resetUrl = hitungBtn.getAttribute('data-reset-url') || '';
          setSelectedResetContext(row, getLocationPayload(row), resetUrl);
        }
        await notify('success', data.message || 'Parameter berhasil dipilih.');
      } catch (error) {
        pickBtn.disabled = false;
        await notify('error', error.message || 'Gagal memilih parameter.');
      }
      return;
    }

    const headerResetBtn = event.target.closest('[data-header-reset-aksi]');
    if (headerResetBtn) {
      if (!selectedResetContext) {
        await notify('warning', 'Pilih dulu baris parameter yang ingin direset.');
        return;
      }
      const { row, resetUrl } = selectedResetContext;
      const groupPayloads = getGroupPayloads(row);
      const canReset = groupPayloads.length > 0 && groupPayloads.every((payload) => {
        const assignedId = Number(payload?.assigned_user_id || 0);
        return assignedId > 0 && (assignedId === currentUserId || currentUserRole === 'superadmin');
      });
      if (!canReset) {
        await notify('warning', 'Parameter ini belum dipilih atau sedang dipilih analis lain.');
        return;
      }

      const confirmResult = await Swal.fire({
        icon: 'warning',
        title: 'Reset Aksi?',
        text: 'Data hitung parameter ini akan dikosongkan dan harus pilih lagi dari awal.',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal',
      });
      if (!confirmResult.isConfirmed) return;

      headerResetBtn.disabled = true;
      const formData = new FormData();
      formData.append('items', JSON.stringify(groupPayloads.map((payload) => ({
        koding_item_id: payload.koding_item_id,
        pengujian_dokumen_parameter_id: payload.pengujian_dokumen_parameter_id,
      }))));
      formData.append('_token', getCsrfToken());
      try {
        const response = await fetch(resetUrl, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
          },
          credentials: 'same-origin',
        });
        let data = {};
        if (response.headers.get('content-type')?.includes('application/json')) {
          data = await response.json();
        }
        if (!response.ok) {
          throw new Error(data.message || 'Gagal reset aksi.');
        }

        groupPayloads.forEach((payload) => {
          const syncKey = [
            payload.koding_item_id || '',
            payload.pengujian_dokumen_parameter_id || '',
            payload.service_parameter_id || '',
          ].join('|');
          const resetPayload = {
            ...payload,
            assigned_user_id: null,
            assigned_analis: '-',
            is_done: false,
            saved: null,
          };
          setSyncedLocationPayload(syncKey, resetPayload);
          syncDoneState(syncKey, false);
        });
        updateVerifyState(row?.closest('.accordion-item'));
        setSelectedResetContext(row, getLocationPayload(row), resetUrl);
        await notify('success', data.message || 'Aksi berhasil direset.');
      } catch (error) {
        await notify('error', error.message || 'Gagal reset aksi.');
      } finally {
        headerResetBtn.disabled = false;
      }
      return;
    }

    const btn = event.target.closest('[data-verify-submit]');
    if (!btn || btn.disabled) return;
    const url = btn.getAttribute('data-verify-url') || '';
    const permohonanIds = btn.getAttribute('data-permohonan-ids') || '';
    if (!url || !permohonanIds) return;
    btn.disabled = true;
    const formData = new FormData();
    formData.append('permohonan_ids', permohonanIds);
    formData.append('_token', getCsrfToken());
    try {
      const response = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'same-origin',
      });
      let data = {};
      if (response.headers.get('content-type')?.includes('application/json')) {
        data = await response.json();
      } else {
        data = { message: await response.text() };
      }
      if (!response.ok) {
        throw new Error(data.message || 'Gagal mengirim ke verifikasi.');
      }
      await notify('success', data.message || 'Berhasil dikirim ke verifikasi.');
      btn.disabled = true;
      window.location.reload();
    } catch (err) {
      await notify('error', err.message || 'Gagal mengirim ke verifikasi.');
      updateVerifyState(btn.closest('.accordion-item'));
    }
  });

  document.addEventListener('click', (event) => {
    const btn = event.target.closest('[data-doc-preview]');
    if (!btn) return;
    const url = btn.getAttribute('data-doc-url') || '';
    if (!url) return;
    const win = window.open(url, '_blank');
    if (!win) return;
    win.focus();
  });

  document.querySelectorAll('tr[data-sync-key]').forEach((row) => {
    const hitungBtn = row.querySelector('[data-hitung-parameter]');
    if (!hitungBtn) return;
    const payload = getLocationPayload(row);
    setRowWorkState(row, payload);
    row.addEventListener('click', () => {
      const latestPayload = getLocationPayload(row);
      const resetUrl = hitungBtn.getAttribute('data-reset-url') || '';
      setSelectedResetContext(row, latestPayload, resetUrl);
    });
  });
  Array.from(document.querySelectorAll('[data-parameter-group-key]'))
    .map((row) => row.getAttribute('data-parameter-group-key') || '')
    .filter((groupKey, index, keys) => groupKey && keys.indexOf(groupKey) === index)
    .forEach((groupKey) => refreshGroupWorkState(groupKey));

  const actionModal = document.getElementById('parameterActionModal');
  const actionOrder = actionModal?.querySelector('[data-action-order]');
  const actionParam = actionModal?.querySelector('[data-action-param]');
  const actionCategory = actionModal?.querySelector('[data-action-category]');
  const actionGrid = actionModal?.querySelector('[data-action-grid]');
  const resetActionRow = actionModal?.querySelector('[data-action-reset-row]');
  const actionHead = actionModal?.querySelector('[data-action-head]');
  const actionRows = actionModal?.querySelector('[data-action-rows]');
  const actionFooter = actionModal?.querySelector('[data-action-footer]');
  const deleteHint = actionModal?.querySelector('[data-action-delete-hint]');
  const actionCons = actionModal?.querySelector('[data-action-cons]');
  const consLabel = actionModal?.querySelector('[data-cons-label]');
  const consOp = actionModal?.querySelector('[data-cons-op]');
  const consTail = actionModal?.querySelector('[data-cons-tail]');
  const consAInput = actionModal?.querySelector('[data-cons-a]');
  const consBInput = actionModal?.querySelector('[data-cons-b]');
  const hcXLabel = actionModal?.querySelector('[data-hc-x-label]');
  const hcXInput = actionModal?.querySelector('[data-hc-x]');
  const hcNote = actionModal?.querySelector('[data-hc-note]');
  const hcYLabel = actionModal?.querySelector('[data-hc-y-label]');
  const hcYInput = actionModal?.querySelector('[data-hc-y]');
  const hcBreak = actionModal?.querySelector('[data-hc-break]');
  const saveBtn = actionModal?.querySelector('[data-action-save]');
  const resetActionBtn = actionModal?.querySelector('[data-action-reset]');
  const blankoBtn = actionModal?.querySelector('[data-action-blanko]');
  const averageBtn = actionModal?.querySelector('[data-action-average]');
  const skPmActionBtn = actionModal?.querySelector('[data-action-type="sk-pm"]');
  const deleteBtn = actionModal?.querySelector('[data-action-delete]');
  const kandblBtn = actionModal?.querySelector('[data-action-kandbl]');
  const calcBtn = actionModal?.querySelector('[data-action-calc]');
  const printBtn = actionModal?.querySelector('[data-action-print]');
  const closeBtn = actionModal?.querySelector('[data-action-close]');
  const referenceMeta = actionModal?.querySelector('[data-action-reference-meta]');
  const pbCurvePanel = actionModal?.querySelector('[data-pb-curve-panel]');
  const pbCurveFormula = actionModal?.querySelector('[data-pb-curve-formula]');
  const pbCurveYInput = actionModal?.querySelector('[data-pb-curve-y]');
  const pbCurveXInput = actionModal?.querySelector('[data-pb-curve-x]');
  const pbCurveR2 = actionModal?.querySelector('[data-pb-curve-r2]');
  const pbCurvePlot = actionModal?.querySelector('[data-pb-curve-plot]');
  const pbCurveRows = actionModal?.querySelector('[data-pb-curve-rows]');
  const pbCurveSvg = actionModal?.querySelector('[data-pb-curve-svg]');
  const pbCurveOverlayCard = actionModal?.querySelector('[data-pb-curve-overlay-card]');
  const pbCurveOverlayY = actionModal?.querySelector('[data-pb-overlay-y]');
  const pbCurveOverlayX = actionModal?.querySelector('[data-pb-overlay-x]');
  const pbCurveOverlayR2 = actionModal?.querySelector('[data-pb-overlay-r2]');
  const pbCurveLatest = actionModal?.querySelector('[data-pb-curve-latest]');
  const pbCurveChartTitle = actionModal?.querySelector('[data-pb-curve-chart-title]');
  const pbCurveChartNote = actionModal?.querySelector('[data-pb-curve-chart-note]');
  const pbCurveTableTitle = actionModal?.querySelector('[data-pb-curve-table-title]');
  const pbCurveTableNote = actionModal?.querySelector('[data-pb-curve-table-note]');
  const pbCurveExportExcelBtn = actionModal?.querySelector('[data-pb-curve-export-excel]');
  const pbCurveExportPdfBtn = actionModal?.querySelector('[data-pb-curve-export-pdf]');
  const pbCurveAxisToggleRow = actionModal?.querySelector('[data-pb-axis-toggle-row]');
  const pbCurveAxisToggleBtn = actionModal?.querySelector('[data-pb-axis-toggle]');
  const pbCurveAxisToggleChevron = actionModal?.querySelector('[data-pb-axis-toggle-chevron]');
  const pbCurveAxisOptions = actionModal?.querySelector('[data-pb-axis-options]');
  const pbCurveAxisInputs = {
    xMin: actionModal?.querySelector('[data-pb-axis="x-min"]'),
    xMax: actionModal?.querySelector('[data-pb-axis="x-max"]'),
    xMajor: actionModal?.querySelector('[data-pb-axis="x-major"]'),
    xMinor: actionModal?.querySelector('[data-pb-axis="x-minor"]'),
    yMin: actionModal?.querySelector('[data-pb-axis="y-min"]'),
    yMax: actionModal?.querySelector('[data-pb-axis="y-max"]'),
    yMajor: actionModal?.querySelector('[data-pb-axis="y-major"]'),
    yMinor: actionModal?.querySelector('[data-pb-axis="y-minor"]'),
  };
  const actionButtons = actionModal?.querySelectorAll('[data-action-type]') || [];
  const stdKalibrasiBtn = actionModal?.querySelector('[data-action-type="std-kalibrasi"]');
  let currentParamCategory = '';
  let currentLocation = null;
  let currentSaveUrl = '';
  const lastDraftPayloads = new Map();
  let currentSamples = [];
  let currentCalcCodes = [];
  let deleteMode = false;
  let selectedRow = null;
  const actionState = new Map();
  let currentStateKey = '';
  let saveTimer = null;
  let modalSessionSnapshot = null;
  let modalSessionType = 'sk-pm';
  const PB_STANDARD_DEFAULTS = [0.1, 0.3, 0.5, 1, 3, 5];
  const CD_STANDARD_DEFAULTS = [0.25, 0.5, 0.75, 1, 1.5];
  const AS_STANDARD_DEFAULTS = [1, 3, 5, 7, 10];
  const HG_STANDARD_DEFAULTS = [10, 20, 30, 40, 50];
  const CO_STANDARD_DEFAULTS = [0.1, 0.5, 1, 2.5, 5];
  const SB_STANDARD_DEFAULTS = [1, 3, 5, 10, 15];
  const TL_STANDARD_DEFAULTS = [3, 5, 7, 10, 15];
  const CR_STANDARD_DEFAULTS = [1, 2.5, 5, 7.5, 10];
  const CU_STANDARD_DEFAULTS = [0.1, 0.5, 1.5, 3, 5];
  const ZN_STANDARD_DEFAULTS = [0.2, 0.4, 0.6, 0.8, 1];
  let syncingPbCurveFormulaInputs = false;
  let pbCurveEquationPosition = null;
  let pbCurveEquationDragState = null;
  let pbCurveAxisExpanded = false;
  let modalSessionCommitted = false;
  const CURVE_CALIBRATION_TYPE = 'std-kalibrasi';

  const typeLabels = {
    'sk-pm': 'Input SK PM',
    'std-kalibrasi': 'Std Kalibrasi',
    'hasil-baca': 'Input Hasil Baca',
    'hasil-perhitungan': 'Input Hasil Perhitungan',
  };
  let currentType = 'sk-pm';

  const getSkPmLabels = () => (
    isHFEmisi()
      ? {
          button: 'Input TM / P',
          first: 'TM (&deg;C)',
          second: 'P (mmHg)',
          firstPlaceholder: 'TM (&deg;C)',
          secondPlaceholder: 'P (mmHg)',
        }
      : {
          button: 'Input SK PM',
          first: 'SK',
          second: 'PM',
          firstPlaceholder: 'SK',
          secondPlaceholder: 'PM',
        }
  );

  const getSkPmHeadHtml = () => {
    const labels = getSkPmLabels();
    return `
      <tr>
        <th style="width: 50%;">Koding Sample</th>
        <th style="width: 25%;">${labels.first}</th>
        <th style="width: 25%;">${labels.second}</th>
      </tr>
    `;
  };

  const getTypeLabel = (type) => {
    if (type === 'sk-pm') {
      return getSkPmLabels().button;
    }
    return typeLabels[type] || typeLabels['sk-pm'];
  };

  const syncSkPmActionButtonLabel = () => {
    if (!skPmActionBtn) return;
    skPmActionBtn.textContent = getSkPmLabels().button;
  };

  const isCurveCalibrationType = (type = currentType) => isCurveMetalAas() && type === CURVE_CALIBRATION_TYPE;

  const syncCurveActionTypeButtons = () => {
    const showStdKalibrasi = isCurveMetalAas();
    if (stdKalibrasiBtn) {
      stdKalibrasiBtn.classList.toggle('d-none', !showStdKalibrasi);
    }
    if (!showStdKalibrasi && currentType === CURVE_CALIBRATION_TYPE) {
      currentType = 'sk-pm';
    }
  };

  const cloneTypesState = (types) => {
    try {
      return JSON.parse(JSON.stringify(types || {}));
    } catch (err) {
      return {};
    }
  };

  const setReferenceUiVisible = (visible) => {
    if (referenceMeta) referenceMeta.classList.toggle('d-none', !visible);
  };

  const getActiveParamName = () => (actionParam?.textContent || '').toLowerCase();
  const getActiveParamCategory = () => (currentParamCategory || '').toLowerCase();
  const isAmbienCategory = () => {
    const c = getActiveParamCategory().replace(/\s+/g, '');
    return c === 'a' || c === 'ambien' || c === 'ambient' || c === 'udaraambien';
  };
  const isLKCategory = () => {
    const c = getActiveParamCategory().replace(/\s+/g, '');
    return c === 'lk' || c === 'lingkungankerja';
  };
  const isSO2Ambien = () => {
    const p = getActiveParamName();
    if (!(p.includes('so2') || p.includes('sulfur dioksida'))) return false;
    return isAmbienCategory() || isLKCategory() || p.includes('ambien');
  };

  const isSO2Emisi = () => {
    const p = getActiveParamName();
    if (!(p.includes('so2') || p.includes('sulfur dioksida'))) return false;
    return !isSO2Ambien();
  };
  const isHFEmisi = () => {
    const p = getActiveParamName();
    if (!(/\bhf\b/i.test(p) || p.includes('hidrogen fluorida') || p.includes('hydrogen fluoride'))) return false;
    return !(isAmbienCategory() || isLKCategory() || p.includes('ambien'));
  };
  const isHCLEmisi = () => {
    const p = getActiveParamName();
    if (!(/\bhcl\b/i.test(p) || p.includes('hidrogen klorida') || p.includes('hydrogen chloride'))) return false;
    return !(isAmbienCategory() || isLKCategory() || p.includes('ambien'));
  };
  const isHgEmisi = () => {
    const p = getActiveParamName();
    if (!(/\bhg\b/i.test(p) || p.includes('merkuri') || p.includes('mercury'))) return false;
    return !(isAmbienCategory() || isLKCategory() || p.includes('ambien'));
  };
  const isNh3Parameter = () => {
    const p = getActiveParamName();
    return p.includes('nh3') || p.includes('amonia') || p.includes('ammonia');
  };
  const isNH3Emisi = () => {
    const p = getActiveParamName();
    if (!isNh3Parameter()) return false;
    return !(isAmbienCategory() || isLKCategory() || p.includes('ambien'));
  };
  const isEmisiAcidGasLike = () => isSO2Emisi() || isHFEmisi() || isHCLEmisi() || isHgEmisi() || isNH3Emisi();
  const getEmisiAcidGasLabel = () => {
    if (isHFEmisi()) return 'HF';
    if (isHCLEmisi()) return 'HCL';
    if (isHgEmisi()) return 'Hg';
    if (isNH3Emisi()) return 'NH3';
    return 'SO2';
  };
  const isSO2HasilBaca = () => isEmisiAcidGasLike() || isSO2Ambien();

  const isNO2Ambien = () => {
    const p = getActiveParamName();
    if (!p.includes('no2')) return false;
    return isAmbienCategory() || isLKCategory() || p.includes('ambien');
  };

  const isHCAmbien = () => {
    const p = getActiveParamName();
    return /\bhc\b/i.test(p) && isAmbienCategory();
  };
  const isHCLk = () => {
    const p = getActiveParamName();
    return /\bhc\b/i.test(p) && isLKCategory();
  };
  const isBenzeneLK = () => {
    const p = getActiveParamName();
    return (p.includes('benzene') || p.includes('benzena') || p.includes('benzen')) && isLKCategory();
  };
  const isBenzeneAmbien = () => {
    const p = getActiveParamName();
    return (p.includes('benzene') || p.includes('benzena') || p.includes('benzen')) && isAmbienCategory();
  };
  const isTolueneLK = () => {
    const p = getActiveParamName();
    return (p.includes('toluene') || p.includes('toluena') || p.includes('toluen')) && isLKCategory();
  };
  const isTolueneAmbien = () => {
    const p = getActiveParamName();
    return (p.includes('toluene') || p.includes('toluena') || p.includes('toluen')) && isAmbienCategory();
  };
  const isXyleneLK = () => {
    const p = getActiveParamName();
    return (p.includes('xylene') || p.includes('xilena')) && isLKCategory();
  };
  const isXyleneAmbien = () => {
    const p = getActiveParamName();
    return (p.includes('xylene') || p.includes('xilena')) && isAmbienCategory();
  };
  const isHcLkParameter = () => isHCLk() || isBenzeneLK() || isTolueneLK() || isXyleneLK();
  const isHcLike = () => isHCAmbien() || isBenzeneAmbien() || isTolueneAmbien() || isXyleneAmbien() || isHcLkParameter();

  const isBTX = () => {
    const p = getActiveParamName();
    return p.includes('btx');
  };

  const isNO2Gas = () => {
    const p = getActiveParamName();
    return p.includes('no2') && !isNO2Ambien();
  };

  const isNH3 = () => isNh3Parameter() && !isNH3Emisi();

  const isH2S = () => {
    const p = getActiveParamName();
    return p.includes('h2s') || p.includes('hidrogen sulfida') || p.includes('hydrogen sulfide');
  };

  const isDebuPm25 = () => {
    const p = getActiveParamName().replace(/\s+/g, '');
    return p.includes('pm2,5') || p.includes('pm2.5') || p.includes('pm25');
  };

  const isDebuPm10 = () => {
    const p = getActiveParamName().replace(/\s+/g, '');
    return p.includes('pm10');
  };

  const isDebuPerseorangan = () => {
    const p = getActiveParamName().replace(/\s+/g, '');
    return p.includes('debuperseorangan') || p.includes('debupeseorangan');
  };

  const isKadarDebuTotal = () => {
    const p = getActiveParamName().replace(/\s+/g, '');
    return p.includes('kadardebutotal') || p.includes('kdtlr');
  };

  const isDebuPm = () => isDebuPm25() || isDebuPm10() || isDebuPerseorangan() || isKadarDebuTotal();

  const isOX = () => {
    const p = getActiveParamName();
    return p.includes('ox') || p.includes('oksidan') || p.includes('oxidant');
  };

  const getNo2LikeLabel = () => {
    if (isSO2Ambien()) return 'SO2';
    if (isOX()) return 'Ox';
    if (isNH3()) return 'NH3';
    if (isH2S()) return 'H2S';
    return 'NO2';
  };

  const getSo2HasilBacaLabel = () => {
    if (isEmisiAcidGasLike()) return getEmisiAcidGasLabel();
    if (isSO2Ambien()) return 'SO2';
    return 'SO2';
  };

  const isMetalAasLk = () => {
    const p = getActiveParamName();
    if (!isLKCategory()) return false;
    if (!(p.includes('kadar debu logam') && p.includes('aas'))) return false;
    return /\b(pb|cd|cr|as|hg|co|sb|tl|cu|zn)\b/.test(p);
  };

  const isPbMetalAas = () => isMetalAasLk() && /\bpb\b/.test(getActiveParamName());
  const isCdMetalAas = () => isMetalAasLk() && /\bcd\b/.test(getActiveParamName());
  const isAsMetalAas = () => isMetalAasLk() && /\bas\b/.test(getActiveParamName());
  const isHgMetalAas = () => isMetalAasLk() && /\bhg\b/.test(getActiveParamName());
  const isCoMetalAas = () => isMetalAasLk() && /\bco\b/.test(getActiveParamName());
  const isSbMetalAas = () => isMetalAasLk() && /\bsb\b/.test(getActiveParamName());
  const isTlMetalAas = () => isMetalAasLk() && /\btl\b/.test(getActiveParamName());
  const isCrMetalAas = () => isMetalAasLk() && /\bcr\b/.test(getActiveParamName());
  const isCuMetalAas = () => isMetalAasLk() && /\bcu\b/.test(getActiveParamName());
  const isZnMetalAas = () => isMetalAasLk() && /\bzn\b/.test(getActiveParamName());
  const isCurveMetalAas = () => isPbMetalAas() || isCdMetalAas() || isAsMetalAas() || isHgMetalAas() || isCoMetalAas() || isSbMetalAas() || isTlMetalAas() || isCrMetalAas() || isCuMetalAas() || isZnMetalAas();

  const getMetalAasLabel = () => {
    const p = getActiveParamName();
    if (/\bpb\b/.test(p)) return 'Pb';
    if (/\bcd\b/.test(p)) return 'Cd';
    if (/\bas\b/.test(p)) return 'As';
    if (/\bhg\b/.test(p)) return 'Hg';
    if (/\bco\b/.test(p)) return 'Co';
    if (/\bsb\b/.test(p)) return 'Sb';
    if (/\btl\b/.test(p)) return 'Tl';
    if (/\bcr\b/.test(p)) return 'Cr';
    if (/\bcu\b/.test(p)) return 'Cu';
    if (/\bzn\b/.test(p)) return 'Zn';
    return 'Logam';
  };

  const getCurrentMetalCurveReference = () => {
    if (isCdMetalAas()) return CD_REFERENCE;
    if (isAsMetalAas()) return AS_REFERENCE;
    if (isHgMetalAas()) return HG_AAS_REFERENCE;
    if (isCoMetalAas()) return CO_REFERENCE;
    if (isSbMetalAas()) return SB_REFERENCE;
    if (isTlMetalAas()) return TL_REFERENCE;
    if (isCrMetalAas()) return CR_REFERENCE;
    if (isCuMetalAas()) return CU_REFERENCE;
    if (isZnMetalAas()) return ZN_REFERENCE;
    return PB_REFERENCE;
  };

  const getCurrentMetalCurveAbsorbanceReference = () => {
    if (isCdMetalAas()) return ABSORBANCE_REFERENCES.CD || {};
    if (isAsMetalAas()) return ABSORBANCE_REFERENCES.AS || {};
    if (isHgMetalAas()) return ABSORBANCE_REFERENCES.HG || {};
    if (isCoMetalAas()) return ABSORBANCE_REFERENCES.CO || {};
    if (isSbMetalAas()) return ABSORBANCE_REFERENCES.SB || {};
    if (isTlMetalAas()) return ABSORBANCE_REFERENCES.TL || {};
    if (isCrMetalAas()) return ABSORBANCE_REFERENCES.CR || {};
    if (isCuMetalAas()) return ABSORBANCE_REFERENCES.CU || {};
    if (isZnMetalAas()) return ABSORBANCE_REFERENCES.ZN || {};
    return ABSORBANCE_REFERENCES.PB || {};
  };

  const getCurrentMetalStandardDefaults = () => {
    const referenceSeries = Array.isArray(getCurrentMetalCurveReference().standardSeries)
      ? getCurrentMetalCurveReference().standardSeries
      : [];
    if (referenceSeries.length > 0) {
      return referenceSeries;
    }
    if (isCdMetalAas()) return CD_STANDARD_DEFAULTS;
    if (isAsMetalAas()) return AS_STANDARD_DEFAULTS;
    if (isHgMetalAas()) return HG_STANDARD_DEFAULTS;
    if (isCoMetalAas()) return CO_STANDARD_DEFAULTS;
    if (isSbMetalAas()) return SB_STANDARD_DEFAULTS;
    if (isTlMetalAas()) return TL_STANDARD_DEFAULTS;
    if (isCrMetalAas()) return CR_STANDARD_DEFAULTS;
    if (isCuMetalAas()) return CU_STANDARD_DEFAULTS;
    return isZnMetalAas() ? ZN_STANDARD_DEFAULTS : PB_STANDARD_DEFAULTS;
  };

  const syncPbCurvePanelMeta = () => {
    const metalLabel = getMetalAasLabel();
    if (pbCurveChartTitle) pbCurveChartTitle.textContent = `Grafik Kurva ${metalLabel}`;
    if (pbCurveTableTitle) pbCurveTableTitle.textContent = `Tabel Standar ${metalLabel}`;
    if (pbCurveChartNote) pbCurveChartNote.textContent = 'Grafik diletakkan di samping tabel agar pembacaan data dan visual lebih seimbang.';
    if (pbCurveTableNote) pbCurveTableNote.textContent = 'Input volume dan hasil baca manual. Nilai kandungan default tetap bisa diubah.';
    if (pbCurveSvg) pbCurveSvg.setAttribute('aria-label', `Kurva kalibrasi ${metalLabel}`);
  };

  const getHcCompoundLabel = () => {
    const p = getActiveParamName();
    if (p.includes('toluene') || p.includes('toluena') || p.includes('toluen')) return 'Toluene';
    if (p.includes('xylene') || p.includes('xilena')) return 'Xylene';
    return 'Benzene';
  };

  const usesHcExcelReference = () => {
    const p = getActiveParamName();
    return /\bhc\b/i.test(p)
      || p.includes('benzene') || p.includes('benzena') || p.includes('benzen')
      || p.includes('toluene') || p.includes('toluena') || p.includes('toluen')
      || p.includes('xylene') || p.includes('xilena');
  };

  const getActiveHcReference = () => {
    const compoundKey = getHcCompoundLabel().toLowerCase();
    if (compoundKey === 'toluene') {
      return TOLUENE_REFERENCE;
    }
    if (compoundKey === 'xylene') {
      return XYLENE_REFERENCE;
    }

    return BENZENE_REFERENCE;
  };

  const setConsFormulaUi = () => {
    if (!consLabel || !consOp || !consTail) return;
    const showHc = isHcLike() && currentType === 'hasil-baca';
    if (hcYLabel) hcYLabel.classList.add('d-none');
    if (hcYInput) hcYInput.classList.add('d-none');
    if (hcBreak) hcBreak.classList.add('d-none');
    if (hcXLabel) hcXLabel.classList.toggle('d-none', !showHc);
    if (hcXInput) hcXInput.classList.toggle('d-none', !showHc);
    if (hcNote) hcNote.classList.add('d-none');
    if (consTail) consTail.classList.toggle('d-none', false);
    if (consOp) consOp.classList.toggle('d-none', showHc);
    if (consBInput) consBInput.classList.toggle('d-none', showHc);

    if (showHc) {
      const compoundLabel = getHcCompoundLabel();
      consLabel.textContent = 'Y =';
      consTail.textContent = '';
      consTail.classList.add('d-none');
      if (hcXLabel) hcXLabel.textContent = 'X = 1 / Y =';
      if (hcNote) hcNote.textContent = `${compoundLabel} = L.Area × X`;
      if (consAInput && consBInput) {
        const key = currentStateKey || '';
        if (consAInput.dataset.hcKey !== key) {
          const activeRef = getActiveHcReference();
          consAInput.value = usesHcExcelReference()
            ? normalizeNumericInputValue(activeRef.yValue) || ''
            : '';
          consAInput.placeholder = 'Nilai Y';
          consBInput.value = '';
          if (hcXInput) hcXInput.value = '';
          consAInput.dataset.hcKey = key;
        }
      }
      if (kandblBtn) kandblBtn.classList.add('d-none');
    } else if (isCurveMetalAas() && currentType === 'hasil-baca') {
      if (kandblBtn) kandblBtn.classList.remove('d-none');
      if (actionCons && kandblBtn && kandblBtn.parentElement !== actionCons) {
        actionCons.appendChild(kandblBtn);
      }
      consTail.classList.add('d-none');
      consLabel.textContent = 'X =';
      consOp.textContent = '';
      if (consBInput) consBInput.classList.remove('d-none');
      if (consAInput) consAInput.placeholder = 'Nilai X';
      if (consBInput) consBInput.placeholder = '0';
      ensurePbCurveYValue();
      syncPbCurveFormulaFields();
      updatePbCurvePanel();
    } else if (isNO2Ambien() && currentType === 'hasil-baca') {
      if (kandblBtn) kandblBtn.classList.add('d-none');
      consTail.classList.remove('d-none');
      consLabel.textContent = 'Cons =';
      consOp.textContent = 'x Abs +';
      consTail.textContent = '';
      if (consBInput) consBInput.classList.remove('d-none');
      if (consAInput && consBInput) {
        const key = currentStateKey || '';
        const reference = getAbsorbanceReference();
        if (consAInput.dataset.no2Key !== key) {
          consAInput.value = normalizeNumericInputValue(reference.slope) || '0.9517';
          consBInput.value = normalizeNumericInputValue(reference.intercept) || '0';
          consAInput.dataset.no2Key = key;
        }
      }
    } else if (isHgEmisi() && currentType === 'hasil-baca') {
      if (kandblBtn) kandblBtn.classList.remove('d-none');
      if (actionCons && kandblBtn && kandblBtn.parentElement !== actionCons) {
        actionCons.appendChild(kandblBtn);
      }
      consTail.classList.remove('d-none');
      consLabel.textContent = 'Cons =';
      consOp.textContent = 'x';
      consTail.textContent = 'Abs';
      if (consBInput) consBInput.classList.add('d-none');
      if (consAInput) {
        const key = currentStateKey || '';
        if (consAInput.dataset.hgKey !== key) {
          consAInput.value = normalizeNumericInputValue(HG_EMISI_REFERENCE.concSlope) || '97.087';
          consAInput.placeholder = '';
          consAInput.dataset.hgKey = key;
        }
      }
      if (consBInput) {
        consBInput.value = '';
      }
    } else if (isSO2Ambien() && currentType === 'hasil-baca') {
      if (kandblBtn) kandblBtn.classList.add('d-none');
      consTail.classList.remove('d-none');
      consLabel.textContent = 'Cons =';
      consOp.textContent = 'x';
      consTail.textContent = 'Hasil baca';
      if (consBInput) consBInput.classList.add('d-none');
      if (consAInput) {
        consAInput.value = '';
        consAInput.placeholder = '';
      }
      if (consBInput) {
        consBInput.value = '';
      }
    } else if (isOX() && currentType === 'hasil-baca') {
      if (kandblBtn) kandblBtn.classList.remove('d-none');
      if (actionCons && kandblBtn && kandblBtn.parentElement !== actionCons) {
        actionCons.appendChild(kandblBtn);
      }
      consTail.classList.remove('d-none');
      consLabel.textContent = 'Cons =';
      consOp.textContent = 'x Abs +';
      consTail.textContent = '';
      if (consAInput && consBInput) {
        const key = currentStateKey || '';
        const reference = getAbsorbanceReference();
        if (consAInput.dataset.oxKey !== key) {
          consAInput.value = normalizeNumericInputValue(reference.slope) || '0.8264';
          consBInput.value = normalizeNumericInputValue(reference.intercept) || '0.0244';
          consAInput.dataset.oxKey = key;
        }
      }
    } else {
      if (kandblBtn) kandblBtn.classList.remove('d-none');
      if (actionCons && kandblBtn && kandblBtn.parentElement !== actionCons) {
        actionCons.appendChild(kandblBtn);
      }
      consTail.classList.remove('d-none');
      consLabel.textContent = 'Cons =';
      consOp.textContent = '+';
      consTail.textContent = 'Abs';
      if (consAInput) {
        consAInput.value = '';
        consAInput.placeholder = '';
        consAInput.dataset.oxKey = '';
        consAInput.dataset.no2Key = '';
        consAInput.dataset.hcKey = '';
        consAInput.dataset.hgKey = '';
        consAInput.dataset.pbKey = '';
      }
      if (consBInput) {
        consBInput.value = '';
        consBInput.placeholder = '0.0000002';
      }
    }
    if (!showHc && kandblBtn) kandblBtn.textContent = 'Hitung Kand Spl';
    if (showHc) {
      updateHcCalc();
    }
  };

  const updateHcCalc = () => {
    if (!isHcLike() || currentType !== 'hasil-baca') return;
    const activeRef = getActiveHcReference();
    const y = toNum((consAInput?.value || '') || (usesHcExcelReference() ? activeRef.yValue : ''));
    const x = (!Number.isNaN(y) && y !== 0) ? (1 / y) : NaN;
    if (hcXInput) {
      hcXInput.value = Number.isNaN(x) ? '' : x.toFixed(12).replace(/0+$/, '').replace(/\.$/, '');
    }
  };

  const applyHcBenzene = () => {
    if (!isHcLike() || currentType !== 'hasil-baca') return;
    const activeRef = getActiveHcReference();
    const y = toNum((consAInput?.value || '') || (usesHcExcelReference() ? activeRef.yValue : ''));
    const x = (!Number.isNaN(y) && y !== 0) ? (1 / y) : NaN;
    if (!actionRows) return;
    actionRows.querySelectorAll('tr').forEach((row) => {
      const label = (row.querySelector('[data-hc="label"]')?.value || '').trim().toLowerCase();
      if (label === 'rata-rata') return;
      const area = toNum(row.querySelector('[data-hc="area_benzene"]')?.value);
      const out = row.querySelector('[data-hc="benzene"]');
      if (!out) return;
      if (Number.isNaN(area) || Number.isNaN(x)) {
        return;
      }
      out.value = (area * x).toFixed(5);
    });
    fillHcCalcFromHasilBaca(currentStateKey);
  };

  const applyNo2AmbienKandFromCons = () => {
    if (!(isNO2Ambien() && currentType === 'hasil-baca') || !actionRows) return;
    const slope = toNum(consAInput?.value);
    const intercept = toNum(consBInput?.value);
    const avgRow = Array.from(actionRows.querySelectorAll('tr')).find((row) => {
      const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
      return label === 'rata-rata';
    });
    const avg = toNum(avgRow?.querySelector('[data-no2="kand"]')?.value);
    actionRows.querySelectorAll('tr').forEach((row) => {
      const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
      if (!label || label === 'rata-rata') return;
      const abs = toNum(row.querySelector('[data-no2="abs"]')?.value);
      const kand = row.querySelector('[data-no2="kand"]');
      const kndbl = row.querySelector('[data-no2="kndbl"]');
      const kandVal = (Number.isNaN(abs) || Number.isNaN(slope) || Number.isNaN(intercept))
        ? NaN
        : ((slope * abs) + intercept);
      if (kand) kand.value = Number.isNaN(kandVal) ? '' : kandVal.toFixed(4);
      if (kndbl) {
        kndbl.value = (label === 'blk' || label === 'blanko' || Number.isNaN(kandVal) || Number.isNaN(avg))
          ? ''
          : (kandVal - avg).toFixed(4);
      }
    });
  };

  const applyPbMetalAasKandFromCons = () => {
    if (!isCurveMetalAas()) return;
    const formula = getPbConcentrationFormula();
    const slope = formula.consSlope;
    const intercept = formula.consIntercept;
    const applyToRoot = (root) => {
      if (!root) return;
      Array.from(root.querySelectorAll('tr')).forEach((row) => {
        const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
        if (!label || label === 'rata-rata') return;
        const abs = toNum(row.querySelector('[data-so2="abs"]')?.value);
        const kandInput = row.querySelector('[data-so2="kand-spl"]');
        const kand = (Number.isNaN(abs) || Number.isNaN(slope) || Number.isNaN(intercept))
          ? NaN
          : ((slope * abs) + intercept);
        if (kandInput) kandInput.value = Number.isNaN(kand) ? '' : kand.toFixed(4);
      });
    };
    const state = currentStateKey ? ensureActionState(currentStateKey) : null;
    if (state?.types?.['hasil-baca']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-baca'].rowsHtml;
      applyToRoot(temp);
      syncInputAttributes(temp);
      state.types['hasil-baca'].rowsHtml = temp.innerHTML;
    }
    if (actionRows && currentType === 'hasil-baca') {
      applyToRoot(actionRows);
    }
  };

  const getCalcCodes = () => (currentCalcCodes.length ? currentCalcCodes : currentSamples);

  const fillCalcLocationInputs = (root) => {
    if (!root) return;
    const codes = getCalcCodes();
    let idx = 0;
    Array.from(root.querySelectorAll('tr')).forEach((row) => {
      if (row.getAttribute('data-row-type') === 'so2-mdl') return;
      if (row.getAttribute('data-row-type') === 'no2-mdl') return;
      if (row.getAttribute('data-row-type') === 'debu-mdl') return;
      if (row.getAttribute('data-row-type') === 'hc-lod') return;
      const input = row.querySelector('td input');
      if (!input) return;
      input.value = formatKodingDisplay(input.value || codes[idx] || '');
      input.setAttribute('readonly', 'readonly');
      input.setAttribute('tabindex', '-1');
      idx += 1;
    });
  };

  const isNo2Like = () => isNO2Ambien() || isSO2Ambien() || isNH3() || isOX() || isH2S();

  const fillNo2SampleLabels = () => {
    if (!(isNo2Like() && !isSO2Ambien() && currentType === 'hasil-baca')) return;
    if (!actionRows) return;
    const rows = Array.from(actionRows.querySelectorAll('tr'));
    let sampleIdx = 0;
    rows.forEach((row) => {
      const input = row.querySelector('[data-no2="label"]');
      if (!input) return;
      const currentVal = (input.value || '').trim();
      const val = currentVal.toLowerCase();
      if (val === 'blk' || val === 'blanko' || val === 'rata-rata') return;
      if (currentVal) return;
      if (!currentSamples[sampleIdx]) return;
      input.value = currentSamples[sampleIdx];
      input.setAttribute('readonly', 'readonly');
      input.setAttribute('tabindex', '-1');
      sampleIdx += 1;
    });
  };

  const getCsrfToken = () => {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token?.getAttribute('content') || '';
  };

  const formatKodingDisplay = (code) => {
    const value = String(code || '').trim();
    if (!value || value === '-') return '-';
    const match = value.match(/^\d+\.(\d{2}\.\d{2}\/.+)$/);
    return match ? match[1] : value;
  };

  const normalizeBlankoLabelText = (value) => {
    const trimmed = String(value || '').trim();
    if (trimmed.toLowerCase() === 'blk') return 'Blanko';
    return value;
  };

  const normalizeBlankoLabels = (root) => {
    if (!root) return;
    root.querySelectorAll('tr:not([data-row-type]) td:first-child input[type="text"], [data-so2="label"], [data-no2="label"], [data-debu="label"], [data-hc="label"], [data-btx="label"]').forEach((input) => {
      if (!(input instanceof HTMLInputElement)) return;
      const normalized = normalizeBlankoLabelText(input.value);
      if (normalized !== input.value) {
        input.value = normalized;
      }
    });
  };

  const getHasilBacaHeadHtml = () => {
    if (isSO2HasilBaca()) {
      return `
        <tr>
          <th style="width: 6%;">No</th>
          <th style="width: 18%;">No. sampel</th>
          <th style="width: 12%;">Volume (ml)</th>
          <th style="width: 12%;">Waktu baca</th>
          <th style="width: 12%;">Hasil baca</th>
          <th style="width: 12%;">Kand spl (mg)</th>
          <th style="width: 12%;">P.enceran (x)</th>
          <th style="width: 16%;">knd - bl (mg)</th>
        </tr>
      `;
    }
    if (isMetalAasLk()) {
      const metalLabel = getMetalAasLabel();
      if (isCurveMetalAas()) {
        return `
          <tr>
            <th style="width: 6%;">No</th>
            <th style="width: 18%;">No. sampel</th>
            <th style="width: 12%;">Volume sampel</th>
            <th style="width: 14%;">Waktu pembacaan</th>
            <th style="width: 12%;">Hasil baca</th>
            <th style="width: 18%;">Kandungan ${metalLabel} (mg/L)</th>
            <th style="width: 20%;">knd - bl ${metalLabel} (mg)</th>
          </tr>
        `;
      }
      return `
        <tr>
          <th style="width: 6%;">No</th>
          <th style="width: 18%;">No. sampel</th>
          <th style="width: 14%;">Vol. Spl ${metalLabel} (ml)</th>
          <th style="width: 14%;">Waktu baca</th>
          <th style="width: 14%;">Hasil baca</th>
          <th style="width: 14%;">Kand spl (mg)</th>
          <th style="width: 20%;">knd - bl ${metalLabel} (mg)</th>
        </tr>
      `;
    }
    if (isHcLike()) {
      const compoundLabel = getHcCompoundLabel();
      return `
        <tr>
          <th style="width: 6%;">No</th>
          <th style="width: 18%;">No.sampel</th>
          <th style="width: 14%;">Vol. CS2 (ml)</th>
          <th style="width: 14%;">RT ${compoundLabel} (mnt)</th>
          <th style="width: 16%;">L.Area ${compoundLabel}</th>
          <th style="width: 16%;">${compoundLabel} (mg/ml)</th>
        </tr>
      `;
    }
    if (isBTX()) {
      return `
        <tr>
          <th style="width: 4%;">No</th>
          <th style="width: 12%;">No.sampel</th>
          <th style="width: 7%;">Vol. CS2 (ml)</th>
          <th style="width: 7%;">RT Benzene (mnt)</th>
          <th style="width: 8%;">L.Area Benzene</th>
          <th style="width: 7%;">Benzene (mg/ml)</th>
          <th style="width: 7%;">RT Toluen (mnt)</th>
          <th style="width: 8%;">L.Area Toluen</th>
          <th style="width: 7%;">Toluen (mg/ml)</th>
          <th style="width: 7%;">RT Xylene (mnt)</th>
          <th style="width: 8%;">L.Area Xylene</th>
          <th style="width: 7%;">Xylene (mg/ml)</th>
        </tr>
      `;
    }
    if (isDebuPm()) {
      return `
        <tr>
          <th rowspan="2" style="width: 6%;">No</th>
          <th rowspan="2" style="width: 18%;">No. sampel</th>
          <th rowspan="2" style="width: 12%;">Jam Timbang</th>
          <th colspan="2" style="width: 24%;">Hasil penimbangan (gr)</th>
          <th rowspan="2" style="width: 12%;">Selisih (gr)</th>
          <th rowspan="2" style="width: 12%;">Brt Db-Blanko (gr)</th>
          <th rowspan="2" style="width: 12%;">Ket</th>
        </tr>
        <tr>
          <th style="width: 12%;">Data awal</th>
          <th style="width: 12%;">Data akhir</th>
        </tr>
      `;
    }
    if (isNo2Like()) {
      return `
        <tr>
          <th style="width: 6%;">No</th>
          <th style="width: 18%;">No. Sampel</th>
          <th style="width: 12%;">Volume (ml)</th>
          <th style="width: 12%;">Waktu baca</th>
          <th style="width: 12%;">Hasil baca</th>
          <th style="width: 12%;">Kand spl (mg)</th>
          <th style="width: 12%;">P.enceran (x)</th>
          <th style="width: 16%;">knd - bl (mg)</th>
        </tr>
      `;
    }
    return `
      <tr>
        <th style="width: 20%;">No. Sample</th>
        <th style="width: 12%;">Waktu</th>
        <th style="width: 10%;">Volume</th>
        <th style="width: 10%;">RT (Mnt)</th>
        <th style="width: 10%;">L. Area</th>
        <th style="width: 12%;">Rata-rata</th>
        <th style="width: 13%;">Pengencer</th>
        <th style="width: 13%;">Kand BL</th>
      </tr>
    `;
  };

  const getCalcHeadHtml = () => {
    if (isEmisiAcidGasLike()) {
      return getSo2CalcHeadHtml();
    }
    if (isMetalAasLk() || isNO2Ambien() || isSO2Ambien() || isOX() || isNH3() || isH2S()) {
      const extraMgColumn = '<th style="width: 8%;">Kadar (mg/m3)</th>';
      return `
        <tr>
          <th style="width: 6%;">No</th>
          <th style="width: 14%;">Lokasi</th>
          <th style="width: 12%;">Konsentrasi</th>
          <th style="width: 12%;">Volume Spl (ml)</th>
          <th style="width: 10%;">Waktu (mnt)</th>
          <th style="width: 10%;">FR (lpm)</th>
            <th style="width: 10%;">Sk C</th>
          <th style="width: 10%;">P mmHg</th>
          <th style="width: 8%;">Kadar (ug/m3)</th>
          ${extraMgColumn}
        </tr>
      `;
    }
    if (isHcLike()) {
      const compoundLabel = getHcCompoundLabel();
      return `
        <tr>
          <th style="width: 6%;">No</th>
          <th style="width: 14%;">Lokasi</th>
          <th style="width: 12%;">Konsentrasi</th>
          <th style="width: 12%;">Volume Spl (ml)</th>
          <th style="width: 10%;">Waktu (mnt)</th>
          <th style="width: 10%;">FR (lpm)</th>
          <th style="width: 10%;">Sk C</th>
          <th style="width: 10%;">P mmHg</th>
          <th style="width: 8%;">Kadar ${compoundLabel} (ppm)</th>
          <th style="width: 8%;">Kadar ${compoundLabel} (ug/m3)</th>
          <th style="width: 8%;">Kadar ${compoundLabel} (mg/m3)</th>
        </tr>
      `;
    }
    if (isDebuPm()) {
      return `
        <tr>
          <th style="width: 6%;">No</th>
          <th style="width: 18%;">Lokasi</th>
          <th style="width: 14%;">Berat debu (Gram)</th>
          <th style="width: 12%;">FR (lpm)</th>
          <th style="width: 12%;">Waktu (mnt)</th>
          <th style="width: 12%;">Sk (oC)</th>
          <th style="width: 12%;">P mmHg</th>
          <th style="width: 14%;">Kadar Debu (mg/m3)</th>
        </tr>
      `;
    }
    return `
      <tr>
        <th style="width: 16%;">Lokasi</th>
        <th style="width: 12%;">Konsentrasi</th>
        <th style="width: 12%;">Volume Spl (ml)</th>
        <th style="width: 10%;">Waktu (mnt)</th>
        <th style="width: 10%;">FR (lpm)</th>
          <th style="width: 10%;">Sk C</th>
        <th style="width: 10%;">P mmHg</th>
        <th style="width: 10%;">Kadar (ppm)</th>
      </tr>
    `;
  };

  const extractHeaderColumns = (headEl) => {
    if (!headEl) return [];
    const rows = Array.from(headEl.querySelectorAll('tr'));
    if (!rows.length) return [];
    const clean = (text) => (text || '').replace(/\s+/g, ' ').trim();

    if (rows.length === 1) {
      return Array.from(rows[0].querySelectorAll('th'))
        .map((th) => clean(th.textContent))
        .filter((text) => text !== '');
    }

    const topCells = Array.from(rows[0].querySelectorAll('th'));
    const bottomCells = Array.from(rows[1].querySelectorAll('th'));
    let bottomIdx = 0;
    const flattened = [];

    topCells.forEach((th) => {
      const parent = clean(th.textContent);
      const colspan = Math.max(1, Number(th.getAttribute('colspan') || 1));
      const rowspan = Math.max(1, Number(th.getAttribute('rowspan') || 1));
      if (rowspan > 1 || bottomCells.length === 0) {
        for (let i = 0; i < colspan; i += 1) {
          if (parent) flattened.push(parent);
        }
        return;
      }
      for (let i = 0; i < colspan; i += 1) {
        const child = clean(bottomCells[bottomIdx]?.textContent || '');
        bottomIdx += 1;
        if (parent && child) {
          flattened.push(`${parent}::${child}`);
        } else if (child) {
          flattened.push(child);
        } else if (parent) {
          flattened.push(parent);
        }
      }
    });

    return flattened.filter((text) => text !== '');
  };
  const normalizeSavedDatasetRows = (dataset) => {
    if (Array.isArray(dataset)) return dataset;
    if (dataset && typeof dataset === 'object' && Array.isArray(dataset.rows)) {
      return dataset.rows;
    }
    return [];
  };

  const buildHasilBacaRowsHtml = (rows) => {
    if (!Array.isArray(rows)) return '';
    const temp = document.createElement('tbody');
    rows.forEach((rowData, idx) => {
      const labelRaw =
        (rowData && typeof rowData === 'object'
          ? rowData.label || rowData.koding || rowData.sample || ''
          : '') || '';
      const label = formatKodingDisplay(labelRaw);
      let rowHtml = '';
      if (isSO2HasilBaca()) {
        rowHtml = SO2.buildHasilRow(label, idx);
      } else if (isMetalAasLk()) {
        rowHtml = buildHasilRowMetalAas(label, idx);
      } else if (isHcLike()) {
        rowHtml = buildHasilRowHC(label, idx);
      } else if (isBTX()) {
        rowHtml = buildHasilRowBTX(label, idx);
      } else if (isDebuPm()) {
        rowHtml = buildHasilRowDebuPm25(label, idx);
      } else if (isNo2Like()) {
        rowHtml = buildHasilRowNO2(label, idx);
      } else {
        rowHtml = buildHasilRow(label, idx);
      }
      temp.insertAdjacentHTML('beforeend', rowHtml);
      const tr = temp.lastElementChild;
      if (!tr) return;
      if (Array.isArray(rowData)) {
        tr.querySelectorAll('input').forEach((input, i) => {
          if (rowData[i] !== undefined) input.value = rowData[i];
        });
        return;
      }
      if (rowData && typeof rowData === 'object') {
        tr.querySelectorAll('input').forEach((input) => {
          const key = input.getAttribute('data-no2')
            || input.getAttribute('data-so2')
            || input.getAttribute('data-pb')
            || input.getAttribute('data-debu')
            || input.getAttribute('data-hc')
            || input.getAttribute('data-btx');
          if (isCurveMetalAas() && key === 'volume') {
            return;
          }
          if (key && rowData[key] !== undefined) {
            input.value = rowData[key];
          }
        });
        if (rowData.cols && Array.isArray(rowData.cols)) {
          tr.querySelectorAll('input').forEach((input, i) => {
            if (isCurveMetalAas() && (input.getAttribute('data-so2') === 'volume')) {
              return;
            }
            if (rowData.cols[i] !== undefined) {
              input.value = rowData.cols[i];
            }
          });
        }
      }
      const firstInput = tr.querySelector('input');
      if (firstInput) {
        firstInput.value = formatKodingDisplay(firstInput.value);
      }
    });
    syncInputAttributes(temp);
    return temp.innerHTML;
  };

  const buildSkPmRowsHtml = (rows) => {
    if (!Array.isArray(rows)) return '';
    const temp = document.createElement('tbody');
    rows.forEach((rowData, idx) => {
      const label =
        (rowData && typeof rowData === 'object'
          ? rowData.label || rowData.sample || ''
          : '') || '';
      temp.insertAdjacentHTML('beforeend', buildSkPmRow(label, idx));
      const tr = temp.lastElementChild;
      if (!tr) return;
      if (Array.isArray(rowData)) {
        tr.querySelectorAll('input').forEach((input, i) => {
          if (rowData[i] !== undefined) input.value = rowData[i];
        });
        return;
      }
      if (rowData && typeof rowData === 'object') {
        if (rowData.cols && Array.isArray(rowData.cols)) {
          tr.querySelectorAll('input').forEach((input, i) => {
            if (rowData.cols[i] !== undefined) {
              input.value = rowData.cols[i];
            }
          });
        }
        tr.querySelectorAll('input').forEach((input) => {
          const key = input.getAttribute('data-skpm');
          if (key && rowData[key] !== undefined) {
            input.value = rowData[key];
          }
        });
      }
      const firstInput = tr.querySelector('input');
      if (firstInput) {
        firstInput.value = formatKodingDisplay(firstInput.value);
      }
    });
    syncInputAttributes(temp);
    return temp.innerHTML;
  };

  const buildHasilPerhitunganRowsHtml = (rows) => {
    if (!Array.isArray(rows)) return '';
    const temp = document.createElement('tbody');
    rows.forEach((rowData, idx) => {
      const rowType = rowData && typeof rowData === 'object' ? (rowData.row_type || '') : '';
      let rowHtml = '';
      if (rowType === 'so2-mdl') {
        rowHtml = SO2.buildCalcRowMdl();
      } else if (rowType === 'so2-calc') {
        rowHtml = SO2.buildCalcRow(rowData?.cols?.[0] || '', idx);
      } else if (rowType === 'debu-mdl') {
        rowHtml = buildCalcRowDebuMdl();
      } else if (rowType === 'no2-mdl') {
        rowHtml = isMetalAasLk() ? buildCalcRowMetalAasMdl() : buildCalcRowNO2Mdl();
      } else if (rowType === 'no2-calc') {
        rowHtml = buildCalcRowNO2(rowData?.cols?.[0] || '', idx);
      } else if (rowType === 'hc-lod') {
        rowHtml = buildCalcRowHcLod();
      } else if (rowType === 'hc-calc') {
        rowHtml = buildCalcRowHC(rowData?.cols?.[0] || '', idx);
      } else if (isMetalAasLk()) {
        rowHtml = buildCalcRowNO2(rowData?.cols?.[0] || '', idx);
      } else if (isEmisiAcidGasLike()) {
        rowHtml = SO2.buildCalcRow(rowData?.cols?.[0] || '', idx);
      } else if (isHcLike()) {
        rowHtml = buildCalcRowHC(rowData?.cols?.[0] || '', idx);
      } else if (isNO2Ambien() || isSO2Ambien() || isOX() || isNH3() || isH2S()) {
        rowHtml = buildCalcRowNO2(rowData?.cols?.[0] || '', idx);
      } else if (isDebuPm()) {
        rowHtml = buildCalcRowDebu(rowData?.cols?.[0] || '', idx);
      } else {
        rowHtml = buildCalcRow(rowData?.cols?.[0] || '');
      }
      temp.insertAdjacentHTML('beforeend', rowHtml);
      const tr = temp.lastElementChild;
      if (!tr) return;
      if (Array.isArray(rowData)) {
        tr.querySelectorAll('input').forEach((input, i) => {
          if (rowData[i] !== undefined) input.value = rowData[i];
        });
        return;
      }
      if (rowData && typeof rowData === 'object') {
        if (rowData.cols && Array.isArray(rowData.cols)) {
          tr.querySelectorAll('input').forEach((input, i) => {
            if (rowData.cols[i] !== undefined) {
              input.value = rowData.cols[i];
            }
          });
        }
        tr.querySelectorAll('input').forEach((input) => {
          const key = input.getAttribute('data-no2')
            || input.getAttribute('data-so2')
            || input.getAttribute('data-skpm')
            || input.getAttribute('data-debu')
            || input.getAttribute('data-hc');
          if (key && rowData[key] !== undefined) {
            input.value = rowData[key];
          }
        });
      }
      const firstInput = tr.querySelector('input');
      if (firstInput) {
        firstInput.value = formatKodingDisplay(firstInput.value);
      }
    });
    syncInputAttributes(temp);
    return temp.innerHTML;
  };

  const normalizeAnalysisLabel = (value) => formatKodingDisplay(value)
    .toString()
    .trim()
    .toLowerCase();

  const getAnalysisRowLabel = (row) => {
    if (!row || typeof row !== 'object') return '';
    const direct = row.label || row.koding || row.sample || row.lokasi || row.no_sampel || '';
    if (String(direct || '').trim()) return String(direct);
    if (Array.isArray(row.cols)) {
      const first = String(row.cols[0] || '').trim();
      const second = String(row.cols[1] || '').trim();
      if (first && !/^\d+$/.test(first)) return first;
      if (second && !/^\d+$/.test(second)) return second;
      return first || second;
    }
    return '';
  };

  const isSharedAnalysisRow = (row) => {
    if (!row || typeof row !== 'object') return false;
    const rowType = String(row.row_type || '').trim().toLowerCase();
    const pbRole = String(row.pb_role || '').trim().toLowerCase();
    if (pbRole === 'standard') {
      return true;
    }
    if (rowType.includes('mdl') || rowType.includes('lod') || rowType.includes('standard')) {
      return true;
    }
    const label = normalizeAnalysisLabel(getAnalysisRowLabel(row));
    return ['blk', 'blanko', 'rata-rata', 'rata rata', 'mdl', 'lod'].includes(label);
  };

  const filterRowsForLocation = (rows, location) => {
    const code = normalizeAnalysisLabel(location?.koding || '');
    if (!Array.isArray(rows) || !code) return Array.isArray(rows) ? rows : [];
    return rows.filter((row) => {
      if (isSharedAnalysisRow(row)) return true;
      return normalizeAnalysisLabel(getAnalysisRowLabel(row)) === code;
    });
  };

  const mergeSavedDataset = (locations, datasetKey) => {
    const datasets = locations
      .map((location) => location?.saved?.[datasetKey])
      .filter((dataset) => Array.isArray(dataset) || (dataset && typeof dataset === 'object'));
    if (!datasets.length) return null;

    let columns = [];
    const rows = [];
    const seen = new Set();
    datasets.forEach((dataset) => {
      if (!columns.length && Array.isArray(dataset?.columns)) {
        columns = dataset.columns.slice();
      }
      normalizeSavedDatasetRows(dataset).forEach((row) => {
        const key = JSON.stringify(row);
        if (seen.has(key)) return;
        seen.add(key);
        rows.push(row);
      });
    });

    return columns.length ? { columns, rows } : rows;
  };

  const buildGroupedLocation = (locations) => {
    const validLocations = (Array.isArray(locations) ? locations : [])
      .filter((location) => location?.koding_item_id && location?.pengujian_dokumen_parameter_id);
    const primary = clonePayload(validLocations[0] || {});
    if (!validLocations.length) return primary;

    const samples = validLocations
      .map((location) => location?.koding)
      .filter((code) => String(code || '').trim());
    const names = validLocations
      .map((location) => String(location?.nama || '').trim())
      .filter((name, index, list) => name && list.indexOf(name) === index);

    return {
      ...primary,
      nama: names.join(', ') || primary.nama || '-',
      samples,
      locations: validLocations.map((location) => clonePayload(location)),
      saved: {
        skpm: mergeSavedDataset(validLocations, 'skpm'),
        hasil_baca: mergeSavedDataset(validLocations, 'hasil_baca'),
        hasil_perhitungan: mergeSavedDataset(validLocations, 'hasil_perhitungan'),
      },
    };
  };

  const seedStateFromBackend = (location) => {
    if (!location?.saved || !currentStateKey) return;
    const state = ensureActionState(currentStateKey);
    if (!state) return;
    if (state.seeded) return;
    if (state.types && Object.keys(state.types).length) {
      state.seeded = true;
      return;
    }
    if (location.saved.skpm) {
      const skpmRows = normalizeSavedDatasetRows(location.saved.skpm);
      state.types['sk-pm'] = {
        headHtml: getSkPmHeadHtml(),
        rowsHtml: buildSkPmRowsHtml(skpmRows),
      };
    }
    if (location.saved.hasil_baca) {
      const hasilBacaRows = normalizeSavedDatasetRows(location.saved.hasil_baca);
      const pbSplit = isCurveMetalAas()
        ? extractPbCalibrationRows(hasilBacaRows)
        : { standards: [], samples: hasilBacaRows };
      if (isCurveMetalAas()) {
        state.types[CURVE_CALIBRATION_TYPE] = {
          headHtml: '',
          rowsHtml: '',
          pbCalibrationRows: pbSplit.standards,
          pbCurveY: '',
          pbCurveReferenceY: getDefaultPbCurveYValue(),
          pbAxisOptions: {},
          pbCurveEquationPosition: null,
        };
      }
      state.types['hasil-baca'] = {
        headHtml: getHasilBacaHeadHtml(),
        rowsHtml: buildHasilBacaRowsHtml(pbSplit.samples),
      };
    }
    if (location.saved.hasil_perhitungan) {
      const hasilPerhitunganRows = normalizeSavedDatasetRows(location.saved.hasil_perhitungan);
      state.types['hasil-perhitungan'] = {
        headHtml: getCalcHeadHtml(),
        rowsHtml: buildHasilPerhitunganRowsHtml(hasilPerhitunganRows),
      };
    }
    state.seeded = true;
  };

  const serializeDraftRows = (root) => {
    if (!root) return [];
    return Array.from(root.querySelectorAll('tr')).map((row) => {
      const obj = {};
      const inputs = Array.from(row.querySelectorAll('input'));
      inputs.forEach((input) => {
        const key = input.getAttribute('data-no2')
          || input.getAttribute('data-so2')
          || input.getAttribute('data-pb')
          || input.getAttribute('data-debu')
          || input.getAttribute('data-hc')
          || input.getAttribute('data-btx');
        if (key) {
          obj[key] = getInputPayloadValue(input);
        }
      });
      const labelInput =
        row.querySelector('[data-no2="label"]')
        || row.querySelector('[data-so2="label"]')
        || row.querySelector('[data-hc="label"]')
        || row.querySelector('[data-btx="label"]');
      if (labelInput) obj.label = labelInput.value;
      if (!Object.keys(obj).length) {
        obj.cols = inputs.map((input) => getInputPayloadValue(input));
      }
      return obj;
    });
  };

  const buildSavedRowsWrapper = (html = '') => {
    if (!html) return null;
    const wrapper = document.createElement('tbody');
    wrapper.innerHTML = html;
    return wrapper;
  };

  const extractHeaderColumnsFromHtml = (html = '') => {
    const thead = document.createElement('thead');
    thead.innerHTML = html;
    return extractHeaderColumns(thead);
  };

  const getCurveCalibrationState = (key = currentStateKey) => {
    const state = key ? ensureActionState(key) : null;
    if (!state) return null;
    return state.types?.[CURVE_CALIBRATION_TYPE] || state.types?.['hasil-baca'] || null;
  };

  const getCurveCalibrationRowsForPayload = () => {
    if (!isCurveMetalAas()) return [];
    if (currentType === CURVE_CALIBRATION_TYPE) {
      return getPbCalibrationRowsPayload();
    }
    const saved = getCurveCalibrationState();
    return Array.isArray(saved?.pbCalibrationRows) ? saved.pbCalibrationRows : [];
  };

  const getHasilBacaRowsForPayload = () => {
    if (currentType === 'hasil-baca' && actionRows) {
      return serializeDraftRows(actionRows);
    }
    const state = currentStateKey ? ensureActionState(currentStateKey) : null;
    const wrapper = buildSavedRowsWrapper(state?.types?.['hasil-baca']?.rowsHtml || '');
    return wrapper ? serializeDraftRows(wrapper) : [];
  };

  const getHasilBacaColumnsForPayload = () => {
    if (currentType === 'hasil-baca' && actionHead) {
      return extractHeaderColumns(actionHead);
    }
    const state = currentStateKey ? ensureActionState(currentStateKey) : null;
    return extractHeaderColumnsFromHtml(state?.types?.['hasil-baca']?.headHtml || getHasilBacaHeadHtml());
  };

  const buildHasilBacaPayload = () => {
    if (!currentLocation) return null;
    const rows = getHasilBacaRowsForPayload();
    const calibrationRows = getCurveCalibrationRowsForPayload();
    const columns = getHasilBacaColumnsForPayload();
    return {
      items: getCurrentLocations().map((location) => ({
          koding_item_id: location.koding_item_id || null,
          pengujian_dokumen_parameter_id: location.pengujian_dokumen_parameter_id || null,
          service_parameter_id: location.service_parameter_id || null,
          kode_koding: location.koding || null,
          parameter_name: actionParam?.textContent || '',
          hasil_baca: {
            columns,
            rows: filterRowsForLocation([...calibrationRows, ...rows], location),
          },
        })),
    };
  };

  const getCurrentLocations = () => {
    const locations = Array.isArray(currentLocation?.locations)
      ? currentLocation.locations
      : [currentLocation];
    return locations.filter((location) => location?.koding_item_id && location?.pengujian_dokumen_parameter_id);
  };

  const buildSkPmPayload = () => {
    if (!currentLocation) return null;
    if (!actionRows) return null;
    const rows = Array.from(actionRows.querySelectorAll('tr')).map((row) => {
      const inputs = Array.from(row.querySelectorAll('input'));
      return {
        label: inputs[0]?.value || '',
        sk: getInputPayloadValue(inputs[1]),
        pm: getInputPayloadValue(inputs[2]),
        cols: inputs.map((input) => getInputPayloadValue(input)),
      };
    });
    const columns = extractHeaderColumns(actionHead);
    return {
      items: getCurrentLocations().map((location) => ({
          koding_item_id: location.koding_item_id || null,
          pengujian_dokumen_parameter_id: location.pengujian_dokumen_parameter_id || null,
          service_parameter_id: location.service_parameter_id || null,
          kode_koding: location.koding || null,
          parameter_name: actionParam?.textContent || '',
          skpm: { columns, rows: filterRowsForLocation(rows, location) },
        })),
    };
  };

  const buildHasilPerhitunganPayload = () => {
    if (!currentLocation) return null;
    if (!actionRows) return null;
    const rows = Array.from(actionRows.querySelectorAll('tr')).map((row) => {
      const inputs = Array.from(row.querySelectorAll('input'));
      const obj = {
        cols: inputs.map((input) => getInputPayloadValue(input)),
      };
      const rowType = row.getAttribute('data-row-type');
      if (rowType) obj.row_type = rowType;
      inputs.forEach((input) => {
        const key = input.getAttribute('data-no2')
          || input.getAttribute('data-so2')
          || input.getAttribute('data-skpm')
          || input.getAttribute('data-debu')
          || input.getAttribute('data-hc');
        if (key) {
          obj[key] = getInputPayloadValue(input);
        }
      });
      return obj;
    });
    const columns = extractHeaderColumns(actionHead);
    return {
      items: getCurrentLocations().map((location) => ({
          koding_item_id: location.koding_item_id || null,
          pengujian_dokumen_parameter_id: location.pengujian_dokumen_parameter_id || null,
          service_parameter_id: location.service_parameter_id || null,
          kode_koding: location.koding || null,
          parameter_name: actionParam?.textContent || '',
          hasil_perhitungan: {
            columns,
            rows: filterRowsForLocation(rows, location),
          },
        })),
    };
  };

  const hasHasilBacaData = () => {
    if (!actionRows) return false;
    const rows = Array.from(actionRows.querySelectorAll('tr'));
    for (const row of rows) {
      const inputs = Array.from(row.querySelectorAll('input')).filter((input) => !input.hasAttribute('readonly') && !input.hasAttribute('disabled'));
      if (inputs.some((input) => input.value && input.value.toString().trim() !== '')) {
        return true;
      }
    }
    return false;
  };

  const saveDraftPayload = async (payload, {
    notify: shouldNotify = false,
    cacheKey = '',
    successMessage = 'Draft preparasi analisa berhasil disimpan.',
    errorMessage = 'Gagal menyimpan draft preparasi analisa.',
  } = {}) => {
    if (!currentSaveUrl || !currentLocation) return { status: 'skipped' };
    if (!payload) return { status: 'skipped' };
    const payloadStr = JSON.stringify(payload);
    const key = `${currentStateKey || 'global'}::${cacheKey || currentType || 'draft'}`;
    const lastPayload = lastDraftPayloads.get(key) || '';
    if (payloadStr === lastPayload) {
      if (shouldNotify) {
        await notify('info', 'Tidak ada perubahan untuk disimpan.');
      }
      return { status: 'unchanged' };
    }
    const formData = new FormData();
    formData.append('payload', payloadStr);
    formData.append('_token', getCsrfToken());
    try {
      const response = await fetch(currentSaveUrl, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'same-origin',
      });
      let data = {};
      if (response.headers.get('content-type')?.includes('application/json')) {
        try {
          data = await response.json();
        } catch (err) {
          data = {};
        }
      } else {
        try {
          data = { message: await response.text() };
        } catch (err) {
          data = {};
        }
      }
      if (!response.ok) {
        throw new Error(data.message || errorMessage);
      }
      lastDraftPayloads.set(key, payloadStr);
      if (shouldNotify) {
        await notify('success', data.message || successMessage);
      }
      return { status: 'success', data };
    } catch (err) {
      lastDraftPayloads.delete(key);
      if (shouldNotify) {
        await notify('error', err.message || errorMessage);
      }
      return { status: 'error', error: err };
    }
  };

  const saveDraftHasilBaca = async ({ notify: shouldNotify = false } = {}) => {
    if (!currentSaveUrl || !currentLocation) return { status: 'skipped' };
    if (currentType !== 'hasil-baca') return { status: 'skipped' };
    const payload = buildHasilBacaPayload();
    return saveDraftPayload(payload, {
      notify: shouldNotify,
      cacheKey: 'hasil-baca',
      successMessage: 'Draft hasil baca berhasil disimpan.',
      errorMessage: 'Gagal menyimpan draft hasil baca.',
    });
  };

  const saveDraftStdKalibrasi = async ({ notify: shouldNotify = false } = {}) => {
    if (!currentSaveUrl || !currentLocation) return { status: 'skipped' };
    if (currentType !== CURVE_CALIBRATION_TYPE) return { status: 'skipped' };
    const payload = buildHasilBacaPayload();
    return saveDraftPayload(payload, {
      notify: shouldNotify,
      cacheKey: CURVE_CALIBRATION_TYPE,
      successMessage: 'Draft standar kalibrasi berhasil disimpan.',
      errorMessage: 'Gagal menyimpan draft standar kalibrasi.',
    });
  };

  const saveDraftSkPm = async ({ notify: shouldNotify = false } = {}) => {
    if (!currentSaveUrl || !currentLocation) return { status: 'skipped' };
    if (currentType !== 'sk-pm') return { status: 'skipped' };
    const payload = buildSkPmPayload();
    return saveDraftPayload(payload, {
      notify: shouldNotify,
      cacheKey: 'sk-pm',
      successMessage: 'Draft SK/PM berhasil disimpan.',
      errorMessage: 'Gagal menyimpan draft SK/PM.',
    });
  };

  const saveDraftHasilPerhitungan = async ({ notify: shouldNotify = false } = {}) => {
    if (!currentSaveUrl || !currentLocation) return { status: 'skipped' };
    if (currentType !== 'hasil-perhitungan') return { status: 'skipped' };
    const payload = buildHasilPerhitunganPayload();
    return saveDraftPayload(payload, {
      notify: shouldNotify,
      cacheKey: 'hasil-perhitungan',
      successMessage: 'Draft hasil perhitungan berhasil disimpan.',
      errorMessage: 'Gagal menyimpan draft hasil perhitungan.',
    });
  };

  const saveDraftByType = async ({ notify: shouldNotify = false } = {}) => {
    if (currentType === CURVE_CALIBRATION_TYPE) {
      return saveDraftStdKalibrasi({ notify: shouldNotify });
    }
    if (currentType === 'hasil-baca') {
      return saveDraftHasilBaca({ notify: shouldNotify });
    }
    if (currentType === 'sk-pm') {
      return saveDraftSkPm({ notify: shouldNotify });
    }
    if (currentType === 'hasil-perhitungan') {
      return saveDraftHasilPerhitungan({ notify: shouldNotify });
    }
    return { status: 'skipped' };
  };

  const normalizeNo2RowNumbers = () => {
    if (!(isNo2Like() && !isSO2Ambien() && currentType === 'hasil-baca')) return;
    if (!actionRows) return;
    const rows = Array.from(actionRows.querySelectorAll('tr'));
    let seq = 1;
    rows.forEach((row) => {
      const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
      const cell = row.querySelector('td');
      if (!cell) return;
      if (label === 'blk' || label === 'blanko' || label === 'rata-rata') {
        cell.textContent = '';
        const cells = row.querySelectorAll('td');
        if (label === 'rata-rata') {
          if (cells[2]) cells[2].innerHTML = '';
          if (cells[3]) cells[3].innerHTML = '';
          if (cells[4]) cells[4].innerHTML = '';
          if (cells[6]) cells[6].innerHTML = '';
          return;
        }
        if (label === 'blk' || label === 'blanko') {
          if (cells[2] && !cells[2].querySelector('input')) {
            cells[2].innerHTML = '<input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-no2="vol">';
          }
          if (cells[3] && !cells[3].querySelector('input')) {
            cells[3].innerHTML = '<input type="text" class="form-control form-control-sm" inputmode="numeric" pattern="^(?:[01]\\\\d|2[0-3]):[0-5]\\\\d$" data-time-24 data-no2="waktu">';
          }
          if (cells[4] && !cells[4].querySelector('input')) {
            cells[4].innerHTML = '<input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-no2="abs">';
          }
          if (cells[6] && !cells[6].querySelector('input')) {
            cells[6].innerHTML = '<input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-no2="encer">';
          }
          return;
        }
      }
      cell.textContent = String(seq);
      seq += 1;
    });
  };

  const buildSkPmRow = (sampleCode = '', idx = 0) => {
    const labels = getSkPmLabels();
    const skValue = '';
    const pmValue = '';
    return `
      <tr>
        <td><input type="text" class="form-control form-control-sm" value="${sampleCode}" readonly></td>
        <td><input type="text" class="form-control form-control-sm" value="${skValue}" placeholder="${labels.firstPlaceholder}"></td>
        <td><input type="text" class="form-control form-control-sm" value="${pmValue}" placeholder="${labels.secondPlaceholder}"></td>
      </tr>
    `;
  };

  const buildHasilRow = (sampleCode = '', idx = 0) => {
    const displayCode = normalizeBlankoLabelText(sampleCode);
    const timeDefaults = ['08:15', '08:30'];
    const volumeDefaults = ['1.0', '1.0'];
    const rtDefaults = ['8.7710', '8.7710'];
    const areaDefaults = ['618', '618'];
    const avgDefaults = ['0.0000', '0.0000'];
    const diluentDefaults = ['0.0000', '0.0000'];
    const waktu = timeDefaults[idx] || '';
    const volume = volumeDefaults[idx] || '';
    const rt = rtDefaults[idx] || '';
    const area = areaDefaults[idx] || '';
    const rata = avgDefaults[idx] || '';
    const pengencer = diluentDefaults[idx] || '';
    return `
      <tr>
        <td><input type="text" class="form-control form-control-sm" value="${displayCode}" ></td>
        <td><input type="text" class="form-control form-control-sm" value="${waktu}" inputmode="numeric" pattern="^(?:[01]\\d|2[0-3]):[0-5]\\d$" data-time-24 placeholder="HH:MM"></td>
        <td><input type="text" class="form-control form-control-sm" value="${volume}"></td>
        <td><input type="text" class="form-control form-control-sm" value="${rt}" placeholder="RT (Mnt)"></td>
        <td><input type="text" class="form-control form-control-sm" value="${area}" placeholder="L. Area"></td>
        <td><input type="text" class="form-control form-control-sm" value="${rata}" placeholder="Rata-rata"></td>
        <td><input type="text" class="form-control form-control-sm" value="${pengencer}" placeholder="Pengencer"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Kand BL"></td>
      </tr>
    `;
  };

  const buildHasilRowDebuPm25 = (sampleCode = '', idx = 0) => {
    const displayCode = normalizeBlankoLabelText(sampleCode);
    const labelLower = String(displayCode || '').trim().toLowerCase();
    const showNo = labelLower && labelLower !== 'blk' && labelLower !== 'blanko' && labelLower !== 'rata-rata';
    return `
      <tr data-row-type="debu-hasil">
        <td class="text-center">${showNo ? (idx + 1) : ''}</td>
        <td><input type="text" class="form-control form-control-sm" value="${displayCode}" data-debu="label"></td>
        <td><input type="text" class="form-control form-control-sm" inputmode="numeric" pattern="^(?:[01]\\d|2[0-3]):[0-5]\\d$" data-time-24 data-debu="jam" placeholder="HH:MM"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="awal"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="akhir"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="selisih"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="brt"></td>
        <td><input type="text" class="form-control form-control-sm" data-debu="ket"></td>
      </tr>
    `;
  };

  const normalizeDebuJamInputs = (root) => {
    if (!root) return;
    root.querySelectorAll('[data-debu="jam"]').forEach((input) => {
      input.setAttribute('data-time-24', '');
      input.setAttribute('inputmode', 'numeric');
      input.setAttribute('pattern', '^(?:[01]\\d|2[0-3]):[0-5]\\d$');
      if (!input.getAttribute('placeholder')) input.setAttribute('placeholder', 'HH:MM');
    });
  };

  const normalizeGenericWaktuBacaInputs = (root) => {
    if (!root) return;
    root.querySelectorAll('tr').forEach((row) => {
      if (row.getAttribute('data-row-type')) return;
      const inputs = Array.from(row.querySelectorAll('input'));
      if (inputs.length !== 8) return;
      const rtInput = inputs[3];
      const waktuInput = inputs[1];
      if (!waktuInput || !rtInput) return;
      const rtPlaceholder = (rtInput.getAttribute('placeholder') || '').toLowerCase();
      if (!rtPlaceholder.includes('rt')) return;
      waktuInput.setAttribute('data-time-24', '');
      waktuInput.setAttribute('inputmode', 'numeric');
      waktuInput.setAttribute('pattern', '^(?:[01]\\d|2[0-3]):[0-5]\\d$');
      if (!waktuInput.getAttribute('placeholder')) waktuInput.setAttribute('placeholder', 'HH:MM');
    });
  };

  const buildHasilRowHC = (sampleCode = '', idx = 0) => {
    const displayCode = normalizeBlankoLabelText(sampleCode);
    const labelLower = String(displayCode || '').trim().toLowerCase();
    const showNo = labelLower && labelLower !== 'blk' && labelLower !== 'blanko' && labelLower !== 'rata-rata';
    const isSpecial = labelLower === 'rata-rata';
    const emptyCell = '<td></td>';
    return `
      <tr data-row-type="hc-hasil">
        <td class="text-center">${showNo ? (idx + 1) : ''}</td>
        <td><input type="text" class="form-control form-control-sm" value="${displayCode}" data-hc="label"></td>
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-hc="vol_cs2"></td>'}
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-hc="rt_benzene"></td>'}
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-hc="area_benzene"></td>'}
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-hc="benzene"></td>
      </tr>
    `;
  };

  const buildHasilRowBTX = (sampleCode = '', idx = 0) => {
    const displayCode = normalizeBlankoLabelText(sampleCode);
    const labelLower = String(displayCode || '').trim().toLowerCase();
    const showNo = labelLower && labelLower !== 'blk' && labelLower !== 'blanko' && labelLower !== 'rata-rata';
    const isSpecial = labelLower === 'rata-rata';
    const emptyCell = '<td></td>';
    return `
      <tr data-row-type="btx-hasil">
        <td class="text-center">${showNo ? (idx + 1) : ''}</td>
        <td><input type="text" class="form-control form-control-sm" value="${displayCode}" data-btx="label"></td>
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="vol_cs2"></td>'}
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="rt_benzene"></td>'}
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="area_benzene"></td>'}
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="benzene"></td>
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="rt_toluene"></td>'}
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="area_toluene"></td>'}
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="toluene"></td>
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="rt_xylene"></td>'}
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="area_xylene"></td>'}
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-btx="xylene"></td>
      </tr>
    `;
  };

  const buildHasilRowSO2 = (sampleCode = '', idx = 0) => {
    const displayCode = normalizeBlankoLabelText(sampleCode);
    const labelLower = String(displayCode || '').trim().toLowerCase();
    const showNo = labelLower && labelLower !== 'blanko' && labelLower !== 'rata-rata';
    return `
      <tr data-row-type="so2-hasil">
        <td class="text-center">${showNo ? (idx + 1) : ''}</td>
        <td><input type="text" class="form-control form-control-sm" value="${displayCode}" data-so2="label" readonly></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="volume"></td>
        <td><input type="text" class="form-control form-control-sm" inputmode="numeric" pattern="^(?:[01]\\d|2[0-3]):[0-5]\\d$" data-time-24 data-so2="waktu"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="abs"></td>
        <td><input type="number" class="form-control form-control-sm" data-so2="kand-spl" step="0.0001" inputmode="decimal"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal"></td>
        <td><input type="text" class="form-control form-control-sm" data-so2="knd-bl" readonly></td>
      </tr>
    `;
  };

  const buildHasilRowMetalAas = (sampleCode = '', idx = 0) => {
    const displayCode = normalizeBlankoLabelText(sampleCode);
    const labelLower = String(displayCode || '').trim().toLowerCase();
    const showNo = labelLower && labelLower !== 'blanko' && labelLower !== 'rata-rata';
    if (isCurveMetalAas()) {
      return `
        <tr data-row-type="so2-hasil">
          <td class="text-center">${showNo ? (idx + 1) : ''}</td>
          <td><input type="text" class="form-control form-control-sm" value="${displayCode}" data-so2="label" readonly></td>
          <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="volume"></td>
          <td><input type="text" class="form-control form-control-sm" inputmode="numeric" pattern="^(?:[01]\\d|2[0-3]):[0-5]\\d$" data-time-24 data-so2="waktu"></td>
          <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="abs"></td>
          <td><input type="number" class="form-control form-control-sm" data-so2="kand-spl" step="0.0001" inputmode="decimal"></td>
          <td><input type="text" class="form-control form-control-sm" data-so2="knd-bl" readonly></td>
        </tr>
      `;
    }
    return `
      <tr data-row-type="so2-hasil">
        <td class="text-center">${showNo ? (idx + 1) : ''}</td>
        <td><input type="text" class="form-control form-control-sm" value="${displayCode}" data-so2="label" readonly></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="volume"></td>
        <td><input type="text" class="form-control form-control-sm" inputmode="numeric" pattern="^(?:[01]\\d|2[0-3]):[0-5]\\d$" data-time-24 data-so2="waktu"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="abs"></td>
        <td><input type="number" class="form-control form-control-sm" data-so2="kand-spl" step="0.0001" inputmode="decimal"></td>
        <td><input type="text" class="form-control form-control-sm" data-so2="knd-bl" readonly></td>
      </tr>
    `;
  };

  const NO2_FACTOR = 0.9517;

  const buildHasilRowNO2 = (sampleCode = '', idx = 0) => {
    const displayCode = normalizeBlankoLabelText(sampleCode);
    const isReadonly = displayCode && String(displayCode).trim().length > 0;
    const readonlyAttr = isReadonly ? 'readonly' : '';
    const labelLower = String(displayCode || '').trim().toLowerCase();
    const showNo = labelLower && labelLower !== 'blk' && labelLower !== 'blanko' && labelLower !== 'rata-rata';
    const isSpecial = labelLower === 'rata-rata';
    const emptyCell = '<td></td>';
    const kandReadonly = labelLower === 'rata-rata' || isNO2Ambien() || isSO2Ambien();
    const kandAttr = kandReadonly ? 'readonly' : '';
    return `
      <tr data-row-type="no2-hasil">
        <td class="text-center">${showNo ? (idx + 1) : ''}</td>
        <td><input type="text" class="form-control form-control-sm" value="${displayCode}" data-no2="label" ${readonlyAttr}></td>
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-no2="vol"></td>'}
        ${isSpecial ? emptyCell : '<td><input type="text" class="form-control form-control-sm" inputmode="numeric" pattern="^(?:[01]\\\\d|2[0-3]):[0-5]\\\\d$" data-time-24 data-no2="waktu"></td>'}
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-no2="abs"></td>'}
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-no2="kand" ${kandAttr}></td>
        ${isSpecial ? emptyCell : '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-no2="encer"></td>'}
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-no2="kndbl" readonly></td>
      </tr>
    `;
  };

  const buildHasilRowNH3 = (sampleCode = '', idx = 0) => {
    const displayCode = normalizeBlankoLabelText(sampleCode);
    const isReadonly = displayCode && String(displayCode).trim().length > 0;
    const readonlyAttr = isReadonly ? 'readonly' : '';
    return `
      <tr data-row-type="nh3-hasil">
        <td class="text-center">${idx + 1}</td>
        <td><input type="text" class="form-control form-control-sm" value="${displayCode}" ${readonlyAttr}></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Volume (ml)"></td>
        <td><input type="text" class="form-control form-control-sm" inputmode="numeric" pattern="^(?:[01]\\d|2[0-3]):[0-5]\\d$" data-time-24></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Hasil baca"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Kand spl (ugr)"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="P.enceran (x)"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="knd - bl (ugr)"></td>
      </tr>
    `;
  };

  const getSo2CalcHeadHtml = () => {
    const gasLabel = getEmisiAcidGasLabel();
    const titrHeader = isHFEmisi()
      ? '<th style="width: 10%;">Titr HF (ml)</th>'
      : '';
    return `
      <tr>
        <th style="width: 6%;">No</th>
        <th style="width: 16%;">Lokasi</th>
        <th style="width: 12%;">Kons ${gasLabel} (mg)</th>
        <th style="width: 12%;">Vol spl (ml)</th>
        <th style="width: 10%;">FR (lpm)</th>
        <th style="width: 10%;">Waktu (mnt)</th>
        ${titrHeader}
          <th style="width: 10%;">TM (C)</th>
        <th style="width: 10%;">P (mmHg)</th>
        <th style="width: 14%;">Kadar ${gasLabel} (mg/m3)</th>
      </tr>
    `;
  };

  const stripAvgInputs = (row) => {
    if (!row) return;
    const firstCell = row.querySelector('td');
    if (firstCell) firstCell.textContent = '';
    const cells = row.querySelectorAll('td');
    if (cells[2]) cells[2].innerHTML = '';
    if (cells[3]) cells[3].innerHTML = '';
    if (cells[4]) cells[4].innerHTML = '';
  };

  const renumberSo2Rows = () => {
    if (!actionRows) return;
    const rows = Array.from(actionRows.querySelectorAll('tr'));
    let seq = 1;
    rows.forEach((row) => {
      const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
      if (row.getAttribute('data-row-type') === 'rata-rata' || label === 'rata-rata') {
        stripAvgInputs(row);
        return;
      }
      const cell = row.querySelector('td');
      if (!cell) return;
      if (label === 'blanko') {
        cell.textContent = '';
        return;
      }
      cell.textContent = String(seq);
      seq += 1;
    });
  };

  const normalizeSo2TimeInputs = () => {
    if (!actionRows) return;
    actionRows.querySelectorAll('input[type="time"]').forEach((input) => {
      const replacement = document.createElement('input');
      replacement.type = 'text';
      replacement.className = input.className;
      replacement.value = input.value || '';
      replacement.setAttribute('inputmode', 'numeric');
      replacement.setAttribute('pattern', '^(?:[01]\\d|2[0-3]):[0-5]\\d$');
      replacement.setAttribute('data-time-24', '');
      input.replaceWith(replacement);
    });
    actionRows.querySelectorAll('input[data-time-24]').forEach((input) => {
      input.removeAttribute('placeholder');
    });
  };

  const unlockSo2HasilBacaInputs = () => {
    if (!actionRows) return;
    actionRows.style.pointerEvents = 'auto';
    actionRows.querySelectorAll('input').forEach((input) => {
      if (input.matches('[data-so2="label"]') || input.matches('[data-so2="knd-bl"]')) {
        input.readOnly = true;
        input.disabled = false;
        input.setAttribute('readonly', 'readonly');
        input.setAttribute('tabindex', '-1');
        input.style.pointerEvents = 'auto';
        input.closest('td')?.style?.setProperty('pointer-events', 'auto');
        return;
      }
      input.readOnly = false;
      input.disabled = false;
      input.removeAttribute('readonly');
      input.removeAttribute('disabled');
      input.removeAttribute('aria-disabled');
      input.removeAttribute('tabindex');
      input.style.pointerEvents = 'auto';
      input.style.opacity = '1';
      input.closest('td')?.style?.setProperty('pointer-events', 'auto');
    });
  };

  const normalizeSo2CalcInputs = (root) => {
    if (!root) return;
    root.querySelectorAll('tr').forEach((row) => {
      if (row.getAttribute('data-row-type') === 'so2-mdl') return;
      const inputs = row.querySelectorAll('input');
      if (inputs[1] && !inputs[1].hasAttribute('data-so2')) inputs[1].setAttribute('data-so2', 'kons');
      if (inputs[2] && !inputs[2].hasAttribute('data-so2')) inputs[2].setAttribute('data-so2', 'vol');
      if (inputs[3] && !inputs[3].hasAttribute('data-so2')) inputs[3].setAttribute('data-so2', 'fr');
      if (inputs[4] && !inputs[4].hasAttribute('data-so2')) inputs[4].setAttribute('data-so2', 'waktu');
      if (inputs[5] && !inputs[5].hasAttribute('data-so2')) inputs[5].setAttribute('data-so2', 'tm');
      if (inputs[6] && !inputs[6].hasAttribute('data-so2')) inputs[6].setAttribute('data-so2', 'p');
      if (inputs[7] && !inputs[7].hasAttribute('data-so2')) inputs[7].setAttribute('data-so2', 'kadar_ugm3');
      const kons = row.querySelector('[data-so2="kons"]');
      const vol = row.querySelector('[data-so2="vol"]');
      const tm = row.querySelector('[data-so2="tm"]');
      const p = row.querySelector('[data-so2="p"]');
      if (kons) {
        kons.setAttribute('readonly', 'readonly');
        kons.setAttribute('tabindex', '-1');
        kons.removeAttribute('disabled');
      }
      if (vol) {
        vol.setAttribute('readonly', 'readonly');
        vol.setAttribute('tabindex', '-1');
        vol.removeAttribute('disabled');
      }
      if (tm) {
        tm.setAttribute('readonly', 'readonly');
        tm.setAttribute('tabindex', '-1');
        tm.removeAttribute('disabled');
      }
      if (p) {
        p.setAttribute('readonly', 'readonly');
        p.setAttribute('tabindex', '-1');
        p.removeAttribute('disabled');
      }
      const ugm3 = row.querySelector('[data-so2="kadar_ugm3"]');
      if (ugm3) {
        ugm3.setAttribute('readonly', 'readonly');
        ugm3.setAttribute('tabindex', '-1');
        ugm3.removeAttribute('disabled');
      }
    });
  };

  const getRowLabelValue = (row) => {
    if (!row) return '';
    const input =
      row.querySelector('[data-no2="label"]')
      || row.querySelector('[data-so2="label"]')
      || row.querySelector('[data-debu="label"]')
      || row.querySelector('[data-hc="label"]')
      || row.querySelector('[data-btx="label"]')
      || row.querySelector('input');
    return (input?.value || '').trim().toLowerCase();
  };

  const hasAverageRow = (root) => {
    if (!root) return false;
    return Array.from(root.querySelectorAll('tr')).some((row) => {
      if (row.getAttribute('data-row-type') === 'rata-rata') return true;
      return getRowLabelValue(row) === 'rata-rata';
    });
  };

  const removeAverageRow = (root) => {
    if (!root) return;
    Array.from(root.querySelectorAll('tr')).forEach((row) => {
      if (row.getAttribute('data-row-type') === 'rata-rata' || getRowLabelValue(row) === 'rata-rata') {
        row.remove();
      }
    });
    if (isSO2HasilBaca() && currentType === 'hasil-baca') {
      Array.from(root.querySelectorAll('tr')).forEach((row) => {
        const label = getRowLabelValue(row);
        if (!label || label === 'blanko' || label === 'rata-rata') return;
        const out = row.querySelector('[data-so2="knd-bl"]');
        if (out) out.value = '';
      });
    }
    if (isMetalAasLk() && currentType === 'hasil-baca') {
      Array.from(root.querySelectorAll('tr')).forEach((row) => {
        const label = getRowLabelValue(row);
        if (!label || label === 'blanko' || label === 'rata-rata') return;
        const out = row.querySelector('[data-so2="knd-bl"]');
        if (out) out.value = '';
      });
    }
    if (isNo2Like() && !isSO2Ambien() && currentType === 'hasil-baca') {
      Array.from(root.querySelectorAll('tr')).forEach((row) => {
        const label = getRowLabelValue(row);
        if (!label || label === 'blk' || label === 'blanko' || label === 'rata-rata') return;
        const out = row.querySelector('[data-no2="kndbl"]');
        if (out) out.value = '';
      });
    }
    if (isDebuPm()) {
      applyDebuBrtDbBlk(root);
    }
  };

  const removeEmptyBlankoRows = (root) => {
    if (!root) return;
    Array.from(root.querySelectorAll('tr')).forEach((row) => {
      const label = getRowLabelValue(row);
      if (label !== 'blanko' && label !== 'blk') return;
      const inputs = Array.from(row.querySelectorAll('input'));
      const isEmpty = inputs.slice(1).every((input) => !(input.value || '').trim());
      if (isEmpty) row.remove();
    });
  };

  const hasUnfilledSampleRows = (root) => {
    if (!root) return false;
    const rows = Array.from(root.querySelectorAll('tr'));
    return rows.some((row) => {
      const label = getRowLabelValue(row);
      if (!label || label === 'blanko' || label === 'blk' || label === 'rata-rata') return false;
      const editableInputs = Array.from(row.querySelectorAll('input')).filter((input) => {
        if (input.type === 'hidden') return false;
        if (input.disabled || input.readOnly) return false;
        return true;
      });
      if (!editableInputs.length) return false;
      return editableInputs.every((input) => !(input.value || '').trim());
    });
  };

  const updateDebuSelisih = (row) => {
    if (!row) return;
    const awal = toNum(row.querySelector('[data-debu="awal"]')?.value);
    const akhir = toNum(row.querySelector('[data-debu="akhir"]')?.value);
    const selisihInput = row.querySelector('[data-debu="selisih"]');
    if (!selisihInput) return;
    if (Number.isNaN(awal) || Number.isNaN(akhir)) {
      selisihInput.value = '';
      return;
    }
    selisihInput.value = (akhir - awal).toFixed(7);
  };

  const applyDebuBrtDbBlk = (root) => {
    if (!root) return;
    let avgRow = root.querySelector('[data-row-type="rata-rata"]');
    if (!avgRow) {
      avgRow = Array.from(root.querySelectorAll('tr')).find((row) => getRowLabelValue(row) === 'rata-rata');
    }
    const avgVal = toNum(avgRow?.querySelector('[data-debu="selisih"]')?.value);
    const hasAvg = !Number.isNaN(avgVal);
    root.querySelectorAll('tr').forEach((row) => {
      const label = (row.querySelector('[data-debu="label"]')?.value || '').trim().toLowerCase();
      if (!label || label === 'blk' || label === 'blanko' || label === 'rata-rata') return;
      const selisih = toNum(row.querySelector('[data-debu="selisih"]')?.value);
      const brt = row.querySelector('[data-debu="brt"]');
      if (!brt) return;
      if (!hasAvg || Number.isNaN(selisih)) {
        brt.value = '';
        return;
      }
      brt.value = (selisih - avgVal).toFixed(7);
    });
  };

  const clearDebuKadarIfIncomplete = (row) => {
    if (!row) return;
    const beratInput = row.querySelector('[data-debu="berat"]');
    const frInput = row.querySelector('[data-debu="fr"]');
    const waktuInput = row.querySelector('[data-debu="waktu"]');
    const skInput = row.querySelector('[data-debu="sk"]');
    const pInput = row.querySelector('[data-debu="p"]');
    const out = row.querySelector('[data-debu="kadar"]');
    if (!out) return;
    const required = [beratInput, frInput, waktuInput, skInput, pInput];
    const hasEmpty = required.some((input) => !(input?.value || '').trim());
    if (hasEmpty) {
      out.value = '';
      return;
    }
    const vals = required.map((input) => toNum(input?.value));
    if (vals.some((v) => Number.isNaN(v))) {
      out.value = '';
      return;
    }
    calcDebuKadarRow(row);
  };

  const setCalcInputReadOnly = (input) => {
    if (!input) return;
    input.setAttribute('readonly', 'readonly');
    input.setAttribute('tabindex', '-1');
    input.removeAttribute('disabled');
  };

  const setCalcInputEditable = (input) => {
    if (!input) return;
    input.removeAttribute('readonly');
    input.removeAttribute('tabindex');
    input.removeAttribute('disabled');
  };

  const lockCalcReferenceRow = (row) => {
    if (!row) return;
    row.querySelectorAll('input').forEach((input) => {
      setCalcInputReadOnly(input);
    });
  };

  const prepareDebuMdlRow = (row) => {
    if (!row) return;
    row.querySelectorAll('[data-debu="berat"], [data-debu="fr"], [data-debu="waktu"], [data-debu="sk"], [data-debu="p"]').forEach((input) => {
      setCalcInputEditable(input);
    });
    setCalcInputReadOnly(row.querySelector('[data-debu="kadar"]'));
  };

  const lockMdlRow = (root) => {
    if (!root) return;
    const mdlRow = root.querySelector('[data-row-type="so2-mdl"]');
    if (!mdlRow) return;
    lockCalcReferenceRow(mdlRow);
  };

  const getSo2HasilBacaData = (key) => {
    const data = { volume: [], kndbl: [] };
    let sourceRows = null;
    if (currentType === 'hasil-baca' && actionRows) {
      sourceRows = actionRows.querySelectorAll('tr');
    } else {
      const state = key ? ensureActionState(key) : null;
      const html = state?.types?.['hasil-baca']?.rowsHtml || '';
      if (!html) return data;
      const wrapper = document.createElement('tbody');
      wrapper.innerHTML = html;
      sourceRows = wrapper.querySelectorAll('tr');
    }
    if (!sourceRows) return data;
    sourceRows.forEach((row) => {
      const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
      if (!label || label === 'blanko' || label === 'rata-rata') return;
      const inputs = row.querySelectorAll('input');
      const volume = row.querySelector('[data-so2="volume"]')?.value || inputs[1]?.value || '';
      const kndbl = row.querySelector('[data-so2="knd-bl"]')?.value || inputs[6]?.value || '';
      const kandSpl = row.querySelector('[data-so2="kand-spl"]')?.value || inputs[4]?.value || '';
      const hasilBaca = inputs[3]?.value || '';
      const kons = kndbl || kandSpl || hasilBaca;
      data.volume.push(volume);
      data.kndbl.push(kons);
    });
    return data;
  };

  const getSo2SkPmData = (key) => {
    const data = { sk: [], pm: [] };
    let sourceRows = null;
    if (currentType === 'sk-pm' && actionRows) {
      sourceRows = actionRows.querySelectorAll('tr');
    } else {
      const state = key ? ensureActionState(key) : null;
      const html = state?.types?.['sk-pm']?.rowsHtml || '';
      if (!html) return data;
      const wrapper = document.createElement('tbody');
      wrapper.innerHTML = html;
      sourceRows = wrapper.querySelectorAll('tr');
    }
    if (!sourceRows) return data;
    sourceRows.forEach((row) => {
      const inputs = row.querySelectorAll('input');
      const sk = row.querySelector('[data-so2="sk"]')?.value || inputs[1]?.value || '';
      const pm = row.querySelector('[data-so2="pm"]')?.value || inputs[2]?.value || '';
      data.sk.push(sk);
      data.pm.push(pm);
    });
    return data;
  };

  const parseLocaleNumberString = (value) => {
    const raw = String(value ?? '').trim().replace(/\s+/g, '');
    if (!raw) return NaN;

    const hasComma = raw.includes(',');
    const hasDot = raw.includes('.');
    let normalized = raw;

    if (hasComma && hasDot) {
      if (raw.lastIndexOf(',') > raw.lastIndexOf('.')) {
        normalized = raw.replace(/\./g, '').replace(',', '.');
      } else {
        normalized = raw.replace(/,/g, '');
      }
    } else if (hasComma) {
      normalized = raw.replace(/\./g, '').replace(',', '.');
    } else if (hasDot) {
      // Anggap titik sebagai pemisah ribuan hanya bila polanya jelas (>= 2 grup ribuan),
      // contoh: 1.234.567. Nilai seperti 0.124 / .124 diperlakukan sebagai desimal.
      if (/^-?\d{1,3}(\.\d{3}){2,}$/.test(raw)) {
        normalized = raw.replace(/\./g, '');
      }
    }

    const parsed = Number(normalized);
    return Number.isFinite(parsed) ? parsed : NaN;
  };

  const countLocaleFractionDigits = (value) => {
    const raw = String(value ?? '').trim();
    if (!raw) return 0;
    const lastComma = raw.lastIndexOf(',');
    const lastDot = raw.lastIndexOf('.');
    const separatorIndex = Math.max(lastComma, lastDot);
    if (separatorIndex < 0) return 0;
    return raw.slice(separatorIndex + 1).replace(/\D/g, '').length;
  };

  const formatLocaleNumberString = (value, {
    integerOnly = false,
    minimumFractionDigits = 0,
    maximumFractionDigits = 4,
  } = {}) => {
    const parsed = parseLocaleNumberString(value);
    if (Number.isNaN(parsed)) {
      return String(value ?? '').trim();
    }

    const formatter = new Intl.NumberFormat('id-ID', {
      useGrouping: false,
      minimumFractionDigits: integerOnly ? 0 : minimumFractionDigits,
      maximumFractionDigits: integerOnly ? 0 : maximumFractionDigits,
    });

    return formatter.format(parsed);
  };

  const isIntegerLocaleField = (input) => {
    if (!(input instanceof HTMLInputElement)) return false;
    const placeholder = (input.getAttribute('placeholder') || '').toLowerCase();
    return input.matches('[data-debu="waktu"], [data-no2="waktu"], [data-hc="waktu"], [data-so2="waktu"], [data-skpm="pm"], [data-debu="p"], [data-hc="p"], [data-so2="p"]')
      || placeholder.includes('waktu')
      || placeholder.includes('p mmhg');
  };

  const isLocaleNumericField = (input) => {
    if (!(input instanceof HTMLInputElement)) return false;
    if (input.matches('[data-time-24]')) return false;
    const fieldKey = input.getAttribute('data-no2')
      || input.getAttribute('data-so2')
      || input.getAttribute('data-debu')
      || input.getAttribute('data-hc')
      || input.getAttribute('data-btx')
      || input.getAttribute('data-skpm');
    if (fieldKey === 'label') return false;
    return input.type === 'number'
      || input.hasAttribute('data-locale-number')
      || fieldKey !== null
      || input.inputMode === 'decimal';
  };

  const sanitizeNumericInput = (value, { integerOnly = false } = {}) => {
    const raw = String(value || '');
    if (integerOnly) {
      return raw.replace(/\D/g, '');
    }

    const isNegative = raw.trim().startsWith('-');
    const cleaned = raw.replace(/[^0-9.,-]/g, '').replace(/-/g, '');
    return isNegative ? `-${cleaned}` : cleaned;
  };

  const normalizeNumberForPayload = (value, { integerOnly = false } = {}) => {
    const parsed = parseLocaleNumberString(value);
    if (Number.isNaN(parsed)) {
      return String(value ?? '').trim();
    }
    if (integerOnly) {
      return String(Math.trunc(parsed));
    }
    const fractionDigits = Math.min(countLocaleFractionDigits(value), 6);
    return fractionDigits > 0 ? parsed.toFixed(fractionDigits) : String(parsed);
  };

  const getLocaleDisplayFractionDigits = (value, maxFractionDigits = 12) => {
    return Math.min(countLocaleFractionDigits(value), maxFractionDigits);
  };

  const getInputPayloadValue = (input) => {
    if (!(input instanceof HTMLInputElement)) {
      return input?.value ?? '';
    }
    if (!isLocaleNumericField(input)) {
      return input.value;
    }
    return normalizeNumberForPayload(input.value, {
      integerOnly: isIntegerLocaleField(input),
    });
  };

  const applyLocaleNumericFormatting = (root) => {
    if (!root) return;
    root.querySelectorAll('input[data-locale-number]').forEach((input) => {
      if (!(input instanceof HTMLInputElement)) return;
      if (!String(input.value || '').trim()) return;
      const fractionDigits = getLocaleDisplayFractionDigits(input.value);
      input.value = formatLocaleNumberString(input.value, {
        integerOnly: input.dataset.localeNumberMode === 'integer',
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits,
      });
    });
  };

  const prepareLocaleNumericInputs = (root) => {
    if (!root) return;
    root.querySelectorAll('input').forEach((input) => {
      if (!(input instanceof HTMLInputElement)) return;
      if (!isLocaleNumericField(input)) return;
      if (input.type === 'number') {
        input.type = 'text';
      }
      input.setAttribute('data-locale-number', '1');
      input.setAttribute('autocomplete', 'off');
      if (isIntegerLocaleField(input)) {
        input.dataset.localeNumberMode = 'integer';
        input.inputMode = 'numeric';
      } else {
        input.dataset.localeNumberMode = 'decimal';
        input.inputMode = 'decimal';
      }
    });
    applyLocaleNumericFormatting(root);
  };

  const toNum = (val) => {
    const n = parseLocaleNumberString(val);
    return Number.isFinite(n) ? n : NaN;
  };

  const calcSo2Kadar = (root) => {
    if (!root) return;
    const isHf = isHFEmisi();
    const isHcl = isHCLEmisi();
    const isHg = isHgEmisi();
    root.querySelectorAll('tr').forEach((row) => {
      const konsInput = row.querySelector('[data-so2="kons"]') || row.querySelector('[data-so2="mdl-kons"]');
      const volInput = row.querySelector('[data-so2="vol"]') || row.querySelector('[data-so2="mdl-vol"]');
      const frInput = row.querySelector('[data-so2="fr"]');
      const waktuInput = row.querySelector('[data-so2="waktu"]');
      const titrInput = row.querySelector('[data-so2="titr_hf"]');
      const tmInput = row.querySelector('[data-so2="tm"]');
      const pInput = row.querySelector('[data-so2="p"]');
      const kadarUgm3Input = row.querySelector('[data-so2="kadar_ugm3"]');
      if (!kadarUgm3Input) return;

      const kons = toNum(konsInput?.value);
      const vol = toNum(volInput?.value);
      const fr = toNum(frInput?.value);
      const waktu = toNum(waktuInput?.value);
      const titrHf = toNum(titrInput?.value);
      const tm = toNum(tmInput?.value);
      const p = toNum(pInput?.value);

      const requiredValues = isHf ? [kons, vol, fr, waktu, titrHf, tm, p] : [kons, vol, fr, waktu, tm, p];
      if (requiredValues.some(Number.isNaN)) {
        kadarUgm3Input.value = '';
        return;
      }

      if (isHf) {
        const result = calculateHfEmisiResult({ kons, vol, fr, waktu, titrHf, tm, p });
        kadarUgm3Input.value = Number.isFinite(result.mgm3) ? result.mgm3.toFixed(4) : '';
        return;
      }

      if (isHcl) {
        const result = calculateHclEmisiResult({ kons, vol, fr, waktu, tm, p });
        kadarUgm3Input.value = Number.isFinite(result.mgm3) ? result.mgm3.toFixed(4) : '';
        return;
      }

      if (isHg) {
        const result = calculateHgEmisiResult({ kons, vol, fr, waktu, tm, p });
        kadarUgm3Input.value = Number.isFinite(result.mgm3) ? result.mgm3.toFixed(4) : '';
        return;
      }

      const kadar = (kons * vol * (273 + tm) * 0.67 * 760 * 1000) / (waktu * fr * 10 * 298 * p);
      kadarUgm3Input.value = Number.isFinite(kadar) ? kadar.toFixed(4) : '';
    });
  };

  const fillSo2CalcFromSkPm = (key) => {
    const data = getSo2SkPmData(key);

    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      let sampleIndex = 0;
      Array.from(temp.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') === 'so2-mdl') return;
        const tm = row.querySelector('[data-so2="tm"]');
        const p = row.querySelector('[data-so2="p"]');
        if (tm) tm.value = data.sk[sampleIndex] ?? '';
        if (p) p.value = data.pm[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      recalculateCurrentCalcRoot(temp);
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }

    if (actionRows && currentType === 'hasil-perhitungan') {
      let sampleIndex = 0;
      Array.from(actionRows.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') === 'so2-mdl') return;
        const tm = row.querySelector('[data-so2="tm"]');
        const p = row.querySelector('[data-so2="p"]');
        if (tm) tm.value = data.sk[sampleIndex] ?? '';
        if (p) p.value = data.pm[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      recalculateCurrentCalcRoot(actionRows);
    }
  };

  const fillNo2CalcFromSkPm = (key) => {
    const data = getSo2SkPmData(key);

    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      let sampleIndex = 0;
      Array.from(temp.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'no2-calc') return;
        const inputs = row.querySelectorAll('input');
        const sk = inputs[5];
        const p = inputs[6];
        if (sk) sk.value = data.sk[sampleIndex] ?? '';
        if (p) p.value = data.pm[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      recalculateCurrentCalcRoot(temp);
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }

    if (actionRows && currentType === 'hasil-perhitungan') {
      let sampleIndex = 0;
      Array.from(actionRows.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'no2-calc') return;
        const inputs = row.querySelectorAll('input');
        const sk = inputs[5];
        const p = inputs[6];
        if (sk) sk.value = data.sk[sampleIndex] ?? '';
        if (p) p.value = data.pm[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      recalculateCurrentCalcRoot(actionRows);
    }
  };

  const lockSkPmInputs = (root) => {
    if (!root) return;
    root.querySelectorAll('[data-skpm]').forEach((input) => {
      const rowType = input.closest('tr')?.getAttribute('data-row-type') || '';
      if (isSO2Ambien() && rowType === 'no2-mdl') {
        input.removeAttribute('readonly');
        input.removeAttribute('tabindex');
        return;
      }
      input.setAttribute('readonly', 'readonly');
      input.setAttribute('tabindex', '-1');
    });
  };

  const fillCalcFromSkPmGeneric = (key) => {
    const data = getSo2SkPmData(key);

    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      let sampleIndex = 0;
      Array.from(temp.querySelectorAll('tr')).forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType === 'so2-mdl' || rowType === 'no2-mdl' || rowType === 'debu-mdl' || rowType === 'hc-lod') return;
        const sk = row.querySelector('[data-skpm="sk"]');
        const pm = row.querySelector('[data-skpm="pm"]');
        if (!sk && !pm) return;
        if (sk) sk.value = data.sk[sampleIndex] ?? '';
        if (pm) pm.value = data.pm[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      if (isSO2Ambien()) {
        applySo2AmbienCalcDefaults(temp);
      }
      recalculateCurrentCalcRoot(temp);
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }

    if (actionRows && currentType === 'hasil-perhitungan') {
      let sampleIndex = 0;
      Array.from(actionRows.querySelectorAll('tr')).forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType === 'so2-mdl' || rowType === 'no2-mdl' || rowType === 'debu-mdl' || rowType === 'hc-lod') return;
        const sk = row.querySelector('[data-skpm="sk"]');
        const pm = row.querySelector('[data-skpm="pm"]');
        if (!sk && !pm) return;
        if (sk) sk.value = data.sk[sampleIndex] ?? '';
        if (pm) pm.value = data.pm[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      lockSkPmInputs(actionRows);
      if (isSO2Ambien()) {
        applySo2AmbienCalcDefaults(actionRows);
      }
      recalculateCurrentCalcRoot(actionRows);
    }
  }; 
  const getNo2HasilBacaData = (key) => {
    const data = { kndbl: [] };
    let sourceRows = null;
    if (currentType === 'hasil-baca' && actionRows) {
      sourceRows = actionRows.querySelectorAll('tr');
    } else {
      const state = key ? ensureActionState(key) : null;
      const html = state?.types?.['hasil-baca']?.rowsHtml || '';
      if (!html) return data;
      const wrapper = document.createElement('tbody');
      wrapper.innerHTML = html;
      sourceRows = wrapper.querySelectorAll('tr');
    }
    if (!sourceRows) return data;
    sourceRows.forEach((row) => {
      const label = (
        row.querySelector('[data-no2="label"]')?.value
        || row.querySelector('[data-so2="label"]')?.value
        || ''
      ).trim().toLowerCase();
      if (!label || label === 'blk' || label === 'blanko' || label === 'rata-rata') return;
      const kndbl =
        row.querySelector('[data-no2="kndbl"]')?.value
        || row.querySelector('[data-so2="knd-bl"]')?.value
        || '';
      data.kndbl.push(kndbl);
    });
    return data;
  };

  const fillNo2CalcFromHasilBaca = (key) => {
    const data = getNo2HasilBacaData(key);
    if (!data.kndbl.length) return;

    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      let sampleIndex = 0;
      Array.from(temp.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'no2-calc') return;
        const kons = row.querySelector('[data-no2="kons"]');
        const ppm = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3 = row.querySelector('[data-no2="kadar_ugm3"]');
        const konsVal = data.kndbl[sampleIndex] ?? '';
        if (kons) kons.value = konsVal;
        if (!String(konsVal).trim()) {
          if (ppm) ppm.value = '';
          if (ugm3) ugm3.value = '';
        }
        sampleIndex += 1;
      });
      if (isSO2Ambien()) {
        applySo2AmbienCalcDefaults(temp);
        recalculateSo2AmbienRows(temp);
      }
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }

    if (actionRows && currentType === 'hasil-perhitungan') {
      let sampleIndex = 0;
      Array.from(actionRows.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'no2-calc') return;
        const kons = row.querySelector('[data-no2="kons"]');
        const ppm = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3 = row.querySelector('[data-no2="kadar_ugm3"]');
        const konsVal = data.kndbl[sampleIndex] ?? '';
        if (kons) kons.value = konsVal;
        if (!String(konsVal).trim()) {
          if (ppm) ppm.value = '';
          if (ugm3) ugm3.value = '';
        }
        sampleIndex += 1;
      });
      if (isSO2Ambien()) {
        applySo2AmbienCalcDefaults(actionRows);
        recalculateSo2AmbienRows(actionRows);
      }
    }
    syncCalcLocationsFromHasilBaca(key);
  };

  const getPbMetalAasHasilBacaData = (key) => {
    const data = { volume: [], konsentrasi: [] };
    let sourceRows = null;
    if (currentType === 'hasil-baca' && actionRows) {
      sourceRows = actionRows.querySelectorAll('tr');
    } else {
      const state = key ? ensureActionState(key) : null;
      const html = state?.types?.['hasil-baca']?.rowsHtml || '';
      if (!html) return data;
      const wrapper = document.createElement('tbody');
      wrapper.innerHTML = html;
      sourceRows = wrapper.querySelectorAll('tr');
    }
    if (!sourceRows) return data;
    sourceRows.forEach((row) => {
      const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
      if (!label || label === 'blk' || label === 'blanko' || label === 'rata-rata') return;
      data.volume.push(row.querySelector('[data-so2="volume"]')?.value || '');
      data.konsentrasi.push(
        row.querySelector('[data-so2="knd-bl"]')?.value || ''
      );
    });
    return data;
  };

  const recalculatePbMetalAasRows = (root) => {
    if (!isCurveMetalAas() || !root) return;
    const lodReference = getCurrentMetalLodReference();
    root.querySelectorAll('tr').forEach((row) => {
      const rowType = row.getAttribute('data-row-type');
      if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
      const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
      const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
      const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
      if (rowType === 'no2-mdl') {
        const konsInput = row.querySelector('[data-no2="kons"]');
        const volInput = row.querySelector('[data-no2="vol"]');
        const waktuInput = row.querySelector('[data-no2="waktu"]');
        const frInput = row.querySelector('[data-no2="fr"]');
        const skInput = row.querySelector('[data-skpm="sk"]');
        const pmInput = row.querySelector('[data-skpm="pm"]');
        if (konsInput) konsInput.value = lodReference.kons || '';
        if (volInput) volInput.value = lodReference.vol || '';
        if (waktuInput) waktuInput.value = lodReference.waktu || '';
        if (frInput) frInput.value = lodReference.fr || '';
        if (skInput) skInput.value = lodReference.sk || '';
        if (pmInput) pmInput.value = lodReference.p || '';
        if (ppmInput) ppmInput.value = '';
        if (ugm3Input) ugm3Input.value = lodReference.ugm3 || '';
        if (mgm3Input) mgm3Input.value = lodReference.mgm3 || '';
        return;
      }
      const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
      const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
      const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
      const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
      const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
      const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
      if ([kons, vol, fr, waktu, sk, p].some((value) => Number.isNaN(value))) {
        if (ppmInput) ppmInput.value = '';
        if (ugm3Input) ugm3Input.value = '';
        if (mgm3Input) mgm3Input.value = '';
        return;
      }
      const result = calculatePbMetalAasResult({ kons, vol, fr, waktu, sk, p });
      if (ppmInput) ppmInput.value = '';
      if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
      if (mgm3Input) mgm3Input.value = Number.isNaN(result.mgm3) ? '' : result.mgm3.toFixed(4);
    });
  };

  const fillPbMetalAasCalcFromHasilBaca = (key) => {
    const data = getPbMetalAasHasilBacaData(key);
    if (!data.konsentrasi.length && !data.volume.length) return;

    const applyToRoot = (root) => {
      if (!root) return;
      let sampleIndex = 0;
      Array.from(root.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'no2-calc') return;
        const kons = row.querySelector('[data-no2="kons"]');
        const vol = row.querySelector('[data-no2="vol"]');
        const ppm = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3 = row.querySelector('[data-no2="kadar_ugm3"]');
        const mgm3 = row.querySelector('[data-no2="kadar_mgm3"]');
        const konsVal = data.konsentrasi[sampleIndex] ?? '';
        const volVal = data.volume[sampleIndex] ?? '';
        if (kons) kons.value = konsVal;
        if (vol && !String(vol.value || '').trim()) {
          vol.value = volVal;
        }
        const effectiveKonsVal = kons ? String(kons.value || '').trim() : String(konsVal || '').trim();
        const effectiveVolVal = vol ? String(vol.value || '').trim() : String(volVal || '').trim();
        if (!effectiveKonsVal || !effectiveVolVal) {
          if (ppm) ppm.value = '';
          if (ugm3) ugm3.value = '';
          if (mgm3) mgm3.value = '';
        }
        sampleIndex += 1;
      });
      recalculatePbMetalAasRows(root);
    };

    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      applyToRoot(temp);
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }

    if (actionRows && currentType === 'hasil-perhitungan') {
      applyToRoot(actionRows);
    }

    syncCalcLocationsFromHasilBaca(key);
  };

  const getGenericHasilBacaData = (key) => {
    const data = { kndbl: [] };
    let sourceRows = null;
    if (currentType === 'hasil-baca' && actionRows) {
      sourceRows = actionRows.querySelectorAll('tr');
    } else {
      const state = key ? ensureActionState(key) : null;
      const html = state?.types?.['hasil-baca']?.rowsHtml || '';
      if (!html) return data;
      const wrapper = document.createElement('tbody');
      wrapper.innerHTML = html;
      sourceRows = wrapper.querySelectorAll('tr');
    }
    if (!sourceRows) return data;
    sourceRows.forEach((row) => {
      const label =
        (row.querySelector('[data-no2="label"]')?.value ||
          row.querySelector('[data-so2="label"]')?.value ||
          row.querySelector('input')?.value ||
          '')
          .trim()
          .toLowerCase();
      if (!label || label === 'blk' || label === 'blanko' || label === 'rata-rata') return;
      const kndbl =
        row.querySelector('[data-no2="kndbl"]')?.value ||
        row.querySelector('[data-so2="knd-bl"]')?.value ||
        Array.from(row.querySelectorAll('input')).slice(-1)[0]?.value ||
        '';
      data.kndbl.push(kndbl);
    });
    return data;
  };

  const getDebuHasilBacaData = (key) => {
    const data = { brt: [] };
    let sourceRows = null;
    if (currentType === 'hasil-baca' && actionRows) {
      sourceRows = actionRows.querySelectorAll('tr');
    } else {
      const state = key ? ensureActionState(key) : null;
      const html = state?.types?.['hasil-baca']?.rowsHtml || '';
      if (!html) return data;
      const wrapper = document.createElement('tbody');
      wrapper.innerHTML = html;
      sourceRows = wrapper.querySelectorAll('tr');
    }
    if (!sourceRows) return data;
    sourceRows.forEach((row) => {
      const label = (row.querySelector('[data-debu="label"]')?.value || '').trim().toLowerCase();
      if (!label || label === 'blk' || label === 'blanko' || label === 'rata-rata') return;
      const brt = row.querySelector('[data-debu="brt"]')?.value || '';
      data.brt.push(brt);
    });
    return data;
  };

  const getHcHasilBacaData = (key) => {
    const data = { kons: [], labels: [] };
    let sourceRows = null;
    if (currentType === 'hasil-baca' && actionRows) {
      sourceRows = actionRows.querySelectorAll('tr');
    } else {
      const state = key ? ensureActionState(key) : null;
      const html = state?.types?.['hasil-baca']?.rowsHtml || '';
      if (!html) return data;
      const wrapper = document.createElement('tbody');
      wrapper.innerHTML = html;
      sourceRows = wrapper.querySelectorAll('tr');
    }
    if (!sourceRows) return data;
    sourceRows.forEach((row) => {
      const label =
        (row.querySelector('[data-hc="label"]')?.value ||
          row.querySelector('input')?.value ||
          '')
          .trim();
      const lower = label.toLowerCase();
      if (!label || lower === 'blk' || lower === 'blanko' || lower === 'rata-rata') return;
      const benzene = row.querySelector('[data-hc="benzene"]')?.value || '';
      data.labels.push(label);
      data.kons.push(benzene);
    });
    return data;
  };

  const getHasilBacaSampleLabels = (key) => {
    const labels = [];
    let sourceRows = null;
    if (currentType === 'hasil-baca' && actionRows) {
      sourceRows = actionRows.querySelectorAll('tr');
    } else {
      const state = key ? ensureActionState(key) : null;
      const html = state?.types?.['hasil-baca']?.rowsHtml || '';
      if (!html) return labels;
      const wrapper = document.createElement('tbody');
      wrapper.innerHTML = html;
      sourceRows = wrapper.querySelectorAll('tr');
    }
    if (!sourceRows) return labels;
    sourceRows.forEach((row) => {
      const label =
        (row.querySelector('[data-no2="label"]')?.value
          || row.querySelector('[data-so2="label"]')?.value
          || row.querySelector('[data-debu="label"]')?.value
          || row.querySelector('[data-hc="label"]')?.value
          || row.querySelector('[data-btx="label"]')?.value
          || row.querySelector('input')?.value
          || '')
          .trim();
      const lower = label.toLowerCase();
      if (!label || lower === 'blk' || lower === 'blanko' || lower === 'rata-rata') return;
      labels.push(label);
    });
    return labels;
  };

  const syncCalcLocationsFromHasilBaca = (key) => {
    const labels = getHasilBacaSampleLabels(key);
    if (!labels.length) return;

    const applyToRoot = (root) => {
      if (!root) return;
      let sampleIndex = 0;
      Array.from(root.querySelectorAll('tr')).forEach((row) => {
        const rowType = row.getAttribute('data-row-type') || '';
        if (rowType === 'so2-mdl' || rowType === 'no2-mdl' || rowType === 'debu-mdl' || rowType === 'hc-lod') return;
        let locationInput = null;
        if (rowType === 'so2-calc' || rowType === 'no2-calc' || rowType === 'debu-calc' || rowType === 'hc-calc') {
          locationInput = row.querySelector('td:nth-child(2) input');
        } else {
          locationInput = row.querySelector('td:first-child input');
        }
        if (!locationInput) return;
        locationInput.value = labels[sampleIndex] ?? locationInput.value;
        sampleIndex += 1;
      });
    };

    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      applyToRoot(temp);
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }
    if (actionRows && currentType === 'hasil-perhitungan') {
      applyToRoot(actionRows);
    }
  };

  const fillDebuCalcFromHasilBaca = (key) => {
    const data = getDebuHasilBacaData(key);
    if (!data.brt.length) return;
    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      let sampleIndex = 0;
      Array.from(temp.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'debu-calc') return;
        const berat = row.querySelector('[data-debu="berat"]');
        if (berat) berat.value = data.brt[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }
    if (actionRows && currentType === 'hasil-perhitungan') {
      let sampleIndex = 0;
      Array.from(actionRows.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'debu-calc') return;
        const berat = row.querySelector('[data-debu="berat"]');
        if (berat) berat.value = data.brt[sampleIndex] ?? '';
        sampleIndex += 1;
      });
    }
    syncCalcLocationsFromHasilBaca(key);
  };

  const fillHcCalcFromHasilBaca = (key) => {
    const data = getHcHasilBacaData(key);
    if (!data.kons.length) return;
    const map = new Map();
    data.labels.forEach((label, idx) => {
      map.set(label.toLowerCase(), data.kons[idx] ?? '');
    });
    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      let seqIndex = 0;
      Array.from(temp.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'hc-calc') return;
        const label = row.querySelectorAll('input')[0]?.value || '';
        const keyLabel = label.toLowerCase();
        const kons = row.querySelector('[data-hc="kons"]');
        if (!kons) return;
        if (map.has(keyLabel)) {
          kons.value = map.get(keyLabel) ?? '';
        } else if (data.kons[seqIndex] !== undefined) {
          kons.value = data.kons[seqIndex] ?? '';
          seqIndex += 1;
        }
      });
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }
    if (actionRows && currentType === 'hasil-perhitungan') {
      let seqIndex = 0;
      Array.from(actionRows.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') !== 'hc-calc') return;
        const label = row.querySelectorAll('input')[0]?.value || '';
        const keyLabel = label.toLowerCase();
        const kons = row.querySelector('[data-hc="kons"]');
        if (!kons) return;
        if (map.has(keyLabel)) {
          kons.value = map.get(keyLabel) ?? '';
        } else if (data.kons[seqIndex] !== undefined) {
          kons.value = data.kons[seqIndex] ?? '';
          seqIndex += 1;
        }
      });
    }
    syncCalcLocationsFromHasilBaca(key);
  };

  const hcCalcRowsHaveSavedResults = (root) => {
    if (!root) return false;
    return Array.from(root.querySelectorAll('tr[data-row-type="hc-calc"]')).some((row) => {
      const ppm = String(row.querySelector('[data-hc="kadar_ppm"]')?.value || '').trim();
      const ugm3 = String(row.querySelector('[data-hc="kadar_ugm3"]')?.value || '').trim();
      const mgm3 = String(row.querySelector('[data-hc="kadar_mgm3"]')?.value || '').trim();
      return ppm !== '' || ugm3 !== '' || mgm3 !== '';
    });
  };

  const resetDebuCalcInputs = (key) => {
    if (!key) return;
    const state = ensureActionState(key);
    const clearRows = (root) => {
      if (!root) return;
      Array.from(root.querySelectorAll('tr')).forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType === 'debu-mdl') return;
        if (rowType && rowType !== 'debu-calc') return;
        row.querySelectorAll('input').forEach((input) => {
          if (input.getAttribute('data-debu') === 'lokasi') return;
          input.value = '';
        });
      });
    };
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      clearRows(temp);
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }
    if (actionRows && currentType === 'hasil-perhitungan') {
      clearRows(actionRows);
    }
  };

  const fillGenericCalcFromHasilBaca = (key) => {
    const data = getGenericHasilBacaData(key);
    if (!data.kndbl.length) return;
    const state = key ? ensureActionState(key) : null;
    if (state?.types?.['hasil-perhitungan']?.rowsHtml) {
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      let sampleIndex = 0;
      Array.from(temp.querySelectorAll('tr')).forEach((row) => {
        const rowType = row.getAttribute('data-row-type') || '';
        if (rowType === 'so2-mdl' || rowType === 'no2-mdl') return;
        const kons = row.querySelector('[data-no2="kons"]') || row.querySelector('[data-so2="kons"]');
        if (kons) kons.value = data.kndbl[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }

    if (actionRows && currentType === 'hasil-perhitungan') {
      let sampleIndex = 0;
      Array.from(actionRows.querySelectorAll('tr')).forEach((row) => {
        const rowType = row.getAttribute('data-row-type') || '';
        if (rowType === 'so2-mdl' || rowType === 'no2-mdl') return;
        const kons = row.querySelector('[data-no2="kons"]') || row.querySelector('[data-so2="kons"]');
        if (kons) kons.value = data.kndbl[sampleIndex] ?? '';
        sampleIndex += 1;
      });
    }
    syncCalcLocationsFromHasilBaca(key);
  };

  const fillSo2CalcFromHasilBaca = (key) => {
    const data = getSo2HasilBacaData(key);
    if (!data.volume.length && !data.kndbl.length) return;

    const state = key ? ensureActionState(key) : null;
    if (state) {
      if (!state.types['hasil-perhitungan']) {
        state.types['hasil-perhitungan'] = {
          headHtml: getSo2CalcHeadHtml(),
          rowsHtml: getCalcCodes().map((code, idx) => SO2.buildCalcRow(code, idx)).join('') + SO2.buildCalcRowMdl(),
        };
      }
      const temp = document.createElement('tbody');
      temp.innerHTML = state.types['hasil-perhitungan'].rowsHtml;
      normalizeSo2CalcInputs(temp);
      let sampleIndex = 0;
      Array.from(temp.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') === 'so2-mdl') return;
        const kons = row.querySelector('[data-so2="kons"]');
        const vol = row.querySelector('[data-so2="vol"]');
        if (kons) kons.value = data.kndbl[sampleIndex] ?? '';
        if (vol) vol.value = data.volume[sampleIndex] ?? '';
        sampleIndex += 1;
      });
      syncInputAttributes(temp);
      state.types['hasil-perhitungan'].rowsHtml = temp.innerHTML;
    }

    if (actionRows && currentType === 'hasil-perhitungan') {
      normalizeSo2CalcInputs(actionRows);
      let sampleIndex = 0;
      Array.from(actionRows.querySelectorAll('tr')).forEach((row) => {
        if (row.getAttribute('data-row-type') === 'so2-mdl') return;
        const kons = row.querySelector('[data-so2="kons"]');
        const vol = row.querySelector('[data-so2="vol"]');
        if (kons) kons.value = data.kndbl[sampleIndex] ?? '';
        if (vol) vol.value = data.volume[sampleIndex] ?? '';
        sampleIndex += 1;
      });
    }
    if (actionRows && currentType === 'hasil-perhitungan') {
      calcSo2Kadar(actionRows);
    }
    syncCalcLocationsFromHasilBaca(key);
  };


  const buildCalcRow = (sampleCode = '') => {
    return `
      <tr>
        <td><input type="text" class="form-control form-control-sm" value="${sampleCode}" placeholder="Lokasi" readonly tabindex="-1"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Konsentrasi" data-no2="kons"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Volume Spl (ml)"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Waktu (mnt)"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="FR (lpm)"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Sk C" data-skpm="sk" readonly tabindex="-1"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="P mmHg" data-skpm="pm" readonly tabindex="-1"></td>
        <td><input type="text" class="form-control form-control-sm" placeholder="Kadar (ppm)"></td>
      </tr>
    `;
  };

  const buildCalcRowHC = (lokasi = '', idx = 0) => {
    return `
      <tr data-row-type="hc-calc">
        <td class="text-center">${idx + 1}</td>
        <td><input type="text" class="form-control form-control-sm" value="${lokasi}" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Konsentrasi" data-hc="kons"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Volume Spl (ml)" data-hc="vol"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Waktu (mnt)" data-hc="waktu"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="FR (lpm)" data-hc="fr"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Sk C" data-hc="sk" data-skpm="sk" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="P mmHg" data-hc="p" data-skpm="pm" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Kadar (ppm)" data-hc="kadar_ppm"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Kadar (ug/m3)" data-hc="kadar_ugm3" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Kadar (mg/m3)" data-hc="kadar_mgm3" readonly tabindex="-1"></td>
      </tr>
    `;
  };

  const buildCalcRowHcLod = () => {
    return `
      <tr data-row-type="hc-lod">
        <td class="text-center">LOD</td>
        <td><input type="text" class="form-control form-control-sm" value="LOD" readonly></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="0.0013" data-hc="kons" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="1" data-hc="vol" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="480" data-hc="waktu" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="0.200" data-hc="fr" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="35.7" data-hc="sk" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="760" data-hc="p" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-hc="kadar_ppm" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-hc="kadar_ugm3" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-hc="kadar_mgm3" readonly tabindex="-1"></td>
      </tr>
    `;
  };

  const buildCalcRowNO2 = (lokasi = '', idx = 0) => {
    const extraMgField = '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Kadar (mg/m3)" data-no2="kadar_mgm3" readonly tabindex="-1"></td>';
    return `
      <tr data-row-type="no2-calc">
        <td class="text-center">${idx + 1}</td>
        <td><input type="text" class="form-control form-control-sm" value="${lokasi}" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Konsentrasi" data-no2="kons"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Volume Spl (ml)" data-no2="vol"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Waktu (mnt)" data-no2="waktu"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="FR (lpm)" data-no2="fr"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Sk (C)" data-skpm="sk" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="P mmHg" data-skpm="pm" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" placeholder="Kadar (ug/m3)" data-no2="kadar_ugm3" readonly tabindex="-1"></td>
        ${extraMgField}
      </tr>
    `;
  };

  const formatMetalLodValue = (value, decimals, { trimInteger = false } = {}) => {
    const num = toNum(value);
    if (Number.isNaN(num)) return '';
    if (trimInteger && Number.isInteger(num)) return String(num);
    return num.toFixed(decimals);
  };

  const formatMetalLodLocaleValue = (value, decimals, { trimInteger = false } = {}) => {
    const normalized = formatMetalLodValue(value, decimals, { trimInteger });
    if (!normalized) return '';
    const fractionDigits = trimInteger && !normalized.includes('.')
      ? 0
      : Math.min(countLocaleFractionDigits(normalized), 4);
    return formatLocaleNumberString(normalized, {
      integerOnly: trimInteger && fractionDigits === 0,
      minimumFractionDigits: fractionDigits,
      maximumFractionDigits: fractionDigits,
    });
  };

  const getCurrentMetalLodReference = () => {
    const curveLod = isCurveMetalAas() ? (getCurrentMetalCurveReference().lod || {}) : {};
    const parameterLod = getCurrentParameterLodReference();
    const resolveNumber = (primary, fallback) => {
      const primaryNum = toNum(primary);
      if (!Number.isNaN(primaryNum)) return primaryNum;
      return toNum(fallback);
    };
    const resolvedVol = (() => {
      const parameterVol = toNum(parameterLod.vol);
      if (!Number.isNaN(parameterVol) && parameterVol > 0) {
        return parameterVol;
      }
      return toNum(curveLod.vol);
    })();
    const resolved = {
      kons: resolveNumber(parameterLod.kons, curveLod.kons),
      vol: resolvedVol,
      fr: resolveNumber(parameterLod.fr, curveLod.fr),
      waktu: resolveNumber(parameterLod.waktu, curveLod.waktu),
      sk: resolveNumber(parameterLod.sk, curveLod.sk),
      p: resolveNumber(parameterLod.pm ?? parameterLod.p, curveLod.pm ?? curveLod.p),
    };
    const result = calculatePbMetalAasResult(resolved);
    const fallbackUgm3 = toNum(parameterLod.sample_ugm3 || parameterLod.ugm3 || curveLod.ugm3);
    const fallbackMgm3 = toNum(parameterLod.mgm3 || curveLod.mgm3);

    return {
      kons: formatMetalLodLocaleValue(resolved.kons, 4),
      vol: formatMetalLodLocaleValue(resolved.vol, 1, { trimInteger: true }),
      fr: formatMetalLodLocaleValue(resolved.fr, 2),
      waktu: formatMetalLodLocaleValue(resolved.waktu, 1),
      sk: formatMetalLodLocaleValue(resolved.sk, 1),
      p: formatMetalLodLocaleValue(resolved.p, 0, { trimInteger: true }),
      ugm3: !Number.isNaN(result.ugm3)
        ? formatMetalLodLocaleValue(result.ugm3, 4)
        : (!Number.isNaN(fallbackUgm3) ? formatMetalLodLocaleValue(fallbackUgm3, 4) : ''),
      mgm3: !Number.isNaN(result.mgm3)
        ? formatMetalLodLocaleValue(result.mgm3, 4)
        : (!Number.isNaN(fallbackMgm3)
          ? formatMetalLodLocaleValue(fallbackMgm3, 4)
          : (!Number.isNaN(fallbackUgm3) ? formatMetalLodLocaleValue(fallbackUgm3 / 1000, 4) : '')),
    };
  };

  const buildCalcRowMetalAasMdl = () => {
    const lod = getCurrentMetalLodReference();
    return `
      <tr data-row-type="no2-mdl">
        <td class="text-center">-</td>
        <td><input type="text" class="form-control form-control-sm" value="LOD" readonly></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${lod.kons}" data-no2="kons" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${lod.vol}" data-no2="vol" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${lod.waktu}" data-no2="waktu" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${lod.fr}" data-no2="fr" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${lod.sk}" data-skpm="sk" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${lod.p}" data-skpm="pm" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${lod.ugm3}" data-no2="kadar_ugm3" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${lod.mgm3}" data-no2="kadar_mgm3" readonly tabindex="-1"></td>
      </tr>
    `;
  };

  const buildCalcRowDebu = (lokasi = '', idx = 0) => {
    return `
      <tr data-row-type="debu-calc">
        <td class="text-center">${idx + 1}</td>
        <td><input type="text" class="form-control form-control-sm" value="${lokasi}" data-debu="lokasi" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="berat"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="fr"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="waktu"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="sk" data-skpm="sk" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="p" data-skpm="pm" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="kadar"></td>
      </tr>
    `;
  };

  const buildCalcRowDebuMdl = () => {
    return `
      <tr data-row-type="debu-mdl">
        <td class="text-center">-</td>
        <td><input type="text" class="form-control form-control-sm" value="LOD" readonly></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="berat"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="fr"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="waktu"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="sk" data-skpm="sk"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="p" data-skpm="pm"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-debu="kadar" readonly tabindex="-1"></td>
      </tr>
    `;
  };

  const buildCalcRowSO2 = (lokasi = '', idx = 0) => {
    const titrCell = isHFEmisi()
      ? '<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="titr_hf"></td>'
      : '';
    return `
      <tr data-row-type="so2-calc">
        <td class="text-center">${idx + 1}</td>
        <td><input type="text" class="form-control form-control-sm" value="${lokasi}" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="kons"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="vol"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="fr"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="waktu"></td>
        ${titrCell}
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="tm"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="p"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" data-so2="kadar_ugm3" readonly tabindex="-1"></td>
      </tr>
    `;
  };

  const buildCalcRowSO2Mdl = () => {
    const fallbackLod = getCurrentParameterLodReference();
    const titrCell = isHFEmisi()
      ? `<td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${HF_EMISI_REFERENCE.lod?.titr_hf || '26.6'}" data-so2="titr_hf" readonly tabindex="-1"></td>`
      : '';
    const mdlKonsValue = isHFEmisi()
      ? (HF_EMISI_REFERENCE.lod?.kons || '0.2876')
      : (isHCLEmisi()
        ? (HCL_EMISI_REFERENCE.lod?.kons || '0.0017')
        : (isHgEmisi() ? (HG_EMISI_REFERENCE.lod?.kons || '0.0561') : (fallbackLod.kons || '')));
    const mdlVolValue = isHFEmisi()
      ? (HF_EMISI_REFERENCE.lod?.vol || '100.0')
      : (isHCLEmisi()
        ? (HCL_EMISI_REFERENCE.lod?.vol || '100.0')
        : (isHgEmisi() ? (HG_EMISI_REFERENCE.lod?.vol || '100.0') : (fallbackLod.vol || '')));
    const mdlFrValue = isHFEmisi()
      ? (HF_EMISI_REFERENCE.lod?.fr || '2.000')
      : (isHCLEmisi()
        ? (HCL_EMISI_REFERENCE.lod?.fr || '2.000')
        : (isHgEmisi() ? (HG_EMISI_REFERENCE.lod?.fr || '20.000') : (fallbackLod.fr || '')));
    const mdlWaktuValue = isHFEmisi()
      ? (HF_EMISI_REFERENCE.lod?.waktu || '20')
      : (isHCLEmisi()
        ? (HCL_EMISI_REFERENCE.lod?.waktu || '20')
        : (isHgEmisi() ? (HG_EMISI_REFERENCE.lod?.waktu || '20') : (fallbackLod.waktu || '')));
    const mdlTmValue = isHFEmisi()
      ? (HF_EMISI_REFERENCE.lod?.tm || '25.0')
      : (isHCLEmisi()
        ? (HCL_EMISI_REFERENCE.lod?.tm || '25.0')
        : (isHgEmisi() ? (HG_EMISI_REFERENCE.lod?.tm || '25.0') : (fallbackLod.sk || '')));
    const mdlPValue = isHFEmisi()
      ? (HF_EMISI_REFERENCE.lod?.p || '760')
      : (isHCLEmisi()
        ? (HCL_EMISI_REFERENCE.lod?.p || '760')
        : (isHgEmisi() ? (HG_EMISI_REFERENCE.lod?.p || '760') : (fallbackLod.pm || '')));
    const mdlKadarValue = isHFEmisi()
      ? (HF_EMISI_REFERENCE.lod?.kadar || '1.4840')
      : (isHCLEmisi()
        ? (HCL_EMISI_REFERENCE.lod?.kadar || '0.8740')
        : (isHgEmisi() ? (HG_EMISI_REFERENCE.lod?.kadar || '0.0000') : ''));
    return `
      <tr data-row-type="so2-mdl">
        <td class="text-center">-</td>
        <td><input type="text" class="form-control form-control-sm" value="LOD" readonly></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${mdlKonsValue}" data-so2="mdl-kons" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${mdlVolValue}" data-so2="mdl-vol" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${mdlFrValue}" readonly tabindex="-1" data-so2="fr"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${mdlWaktuValue}" readonly tabindex="-1" data-so2="waktu"></td>
        ${titrCell}
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${mdlTmValue}" readonly tabindex="-1" data-so2="tm"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${mdlPValue}" readonly tabindex="-1" data-so2="p"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${mdlKadarValue}" data-so2="kadar_ugm3" readonly tabindex="-1"></td>
      </tr>
    `;
  };

  const normalizeNumericInputValue = (value) => {
    const raw = String(value ?? '').trim();
    if (!raw) return '';
    const numeric = Number(raw.replace(',', '.'));
    return Number.isFinite(numeric) ? String(numeric) : raw;
  };

  const SO2_AMBIEN_REFERENCE = Object.assign({
    kons: '0.5218',
    vol: '10.0',
    waktu: '60',
    fr: '1.000',
    sk: '25.0',
    pm: '760',
    factorPpm: 0.382,
    factorUgm3: 2617.6,
    formulaPpm: '',
    formulaUgm3: '',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    sample: {
      kons: '0.0360',
      vol: '10.0',
      waktu: '60',
      fr: '1.000',
      sk: '29.0',
      pm: '758',
      ppm: '0.0002',
      ugm3: '0.6',
    },
  }, @json($so2AmbienReference));

  const HF_EMISI_REFERENCE = Object.assign({
    formulaKadar: '(kons * (vol / 25) * 760 * (25 + titr_hf) * (273 + tm)) / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    constants: {
      volumeDivisor: 25,
      pressureFactor: 760,
      titrOffset: 25,
      temperatureOffset: 273,
      denominatorTemperature: 298,
    },
    lod: {
      kons: '0.2876',
      vol: '100.0',
      fr: '2.000',
      waktu: '20',
      titr_hf: '26.6',
      tm: '25.0',
      p: '760',
      kadar: '1.4840',
    },
    sample: {
      kons: '0.2470',
      vol: '98.0',
      fr: '2.000',
      waktu: '5',
      titr_hf: '26.9',
      tm: '36.2',
      p: '753',
      kadar: '5.2625',
    },
  }, @json($hfEmisiReference));
  const HCL_EMISI_REFERENCE = Object.assign({
    formulaKadar: '(kons * vol * 1.0282 * 1000 * (273 + tm) * 760) / (waktu * fr * 5 * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    constants: {
      correctionFactor: 1.0282,
      massFactor: 1000,
      temperatureOffset: 273,
      pressureFactor: 760,
      divisorFactor: 5,
      denominatorTemperature: 298,
    },
    lod: {
      kons: '0.0017',
      vol: '100.0',
      fr: '2.000',
      waktu: '20',
      tm: '25.0',
      p: '760',
      kadar: '0.8740',
    },
    sample: {
      kons: '0.0021',
      vol: '94.0',
      fr: '2.000',
      waktu: '5',
      tm: '36.2',
      p: '753',
      kadar: '4.2511',
    },
  }, @json($hclEmisiReference));
  const HG_EMISI_REFERENCE = Object.assign({
    formulaConc: 'conc = 97.087 * abs',
    concSlope: 97.087,
    formulaKadar: '(kons * vol * (273 + tm) * 760) / (fr * waktu * 298 * 1000 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
      massDivisor: 1000,
    },
    lod: {
      kons: '0.0561',
      vol: '100.0',
      fr: '20.000',
      waktu: '20',
      tm: '25.0',
      p: '760',
      kadar: '0.0000',
    },
    sample: {
      kons: '3.5194',
      vol: '100.0',
      fr: '25.000',
      waktu: '5',
      tm: '31.4',
      p: '750',
      kadar: '0.0029',
    },
  }, @json($hgEmisiReference));

  const PB_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 222.222 * abs',
    concIntercept: 0,
    concSlope: 222.222,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [0, 0.2, 0.4, 1, 3, 5],
    lod: {
      kons: '0.0095',
      vol: '15.0',
      fr: '500.000',
      waktu: '30',
      sk: '25.0',
      p: '760',
      ugm3: '0.0095',
      mgm3: '0.0000',
    },
  }, @json($pbReference));

  const CD_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 8.70322 * abs',
    concIntercept: 0,
    concSlope: 8.70322019147,
    curveY: 0.1149,
    curveX: 8.70322019147,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [0.25, 0.5, 0.75, 1, 1.5],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($cdReference));

  const CU_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 22.371365 * abs',
    concIntercept: 0,
    concSlope: 22.37136465324,
    curveY: 0.0447,
    curveX: 22.37136465324,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [0.1, 0.5, 1.5, 3, 5],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($cuReference));

  const CR_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 40.322581 * abs',
    concIntercept: 0,
    concSlope: 40.32258064516,
    curveY: 0.0248,
    curveX: 40.32258064516,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [1, 2.5, 5, 7.5, 10],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($crReference));

  const AS_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 39.370079 * abs',
    concIntercept: 0,
    concSlope: 39.370078740157,
    curveY: 0.0254,
    curveX: 39.370078740157,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [1, 3, 5, 7, 10],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($asReference));

  const HG_AAS_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 277.777778 * abs',
    concIntercept: 0,
    concSlope: 277.777777777778,
    curveY: 0.0036,
    curveX: 277.777777777778,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [10, 20, 30, 40, 50],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($hgAasReference));

  const CO_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 28.901734 * abs',
    concIntercept: 0,
    concSlope: 28.901734104046,
    curveY: 0.0346,
    curveX: 28.901734104046,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [0.1, 0.5, 1, 2.5, 5],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($coReference));

  const SB_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 285.714286 * abs',
    concIntercept: 0,
    concSlope: 285.714285714286,
    curveY: 0.0035,
    curveX: 285.714285714286,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [1, 3, 5, 10, 15],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($sbReference));

  const TL_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 178.571429 * abs',
    concIntercept: 0,
    concSlope: 178.571428571429,
    curveY: 0.0056,
    curveX: 178.571428571429,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [3, 5, 7, 10, 15],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($tlReference));

  const ZN_REFERENCE = Object.assign({
    formulaConc: 'cons = 0 + 6.877579 * abs',
    concIntercept: 0,
    concSlope: 6.87757909216,
    curveY: 0.1454,
    curveX: 6.87757909216,
    formulaKadar: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    absorbanceSourceMode: 'fallback',
    lodSourceMode: 'fallback',
    constants: {
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    standardSeries: [0.2, 0.4, 0.6, 0.8, 1],
    lod: {
      kons: '',
      vol: '',
      fr: '',
      waktu: '',
      sk: '',
      p: '',
      ugm3: '',
      mgm3: '',
    },
  }, @json($znReference));
  const ABSORBANCE_REFERENCE_URL_TEMPLATE = @json(route('superadmin.absorbansi.reference', ['parameter' => '__PARAM__']));

  const ABSORBANCE_REFERENCES = Object.assign({
    NO2: {
      parameter_key: 'NO2',
      label: 'NO2',
      intercept: 0,
      slope: 0.9517,
      formula: 'cons = 0 + 0.9517 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    OX: {
      parameter_key: 'OX',
      label: 'OX',
      intercept: 0.0244,
      slope: 0.8264,
      formula: 'cons = 0.0244 + 0.8264 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    PB: {
      parameter_key: 'PB',
      label: 'Pb',
      intercept: 0,
      slope: 222.222,
      formula: 'cons = 0 + 222.222 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    CD: {
      parameter_key: 'CD',
      label: 'Cd',
      intercept: 0,
      slope: 8.70322019147,
      formula: 'cons = 0 + 8.70322 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    CU: {
      parameter_key: 'CU',
      label: 'Cu',
      intercept: 0,
      slope: 22.37136465324,
      formula: 'cons = 0 + 22.371365 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    CR: {
      parameter_key: 'CR',
      label: 'Cr',
      intercept: 0,
      slope: 40.32258064516,
      formula: 'cons = 0 + 40.322581 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    AS: {
      parameter_key: 'AS',
      label: 'As',
      intercept: 0,
      slope: 39.370078740157,
      formula: 'cons = 0 + 39.370079 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    HG: {
      parameter_key: 'HG',
      label: 'Hg',
      intercept: 0,
      slope: 277.777777777778,
      formula: 'cons = 0 + 277.777778 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    CO: {
      parameter_key: 'CO',
      label: 'Co',
      intercept: 0,
      slope: 28.901734104046,
      formula: 'cons = 0 + 28.901734 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    SB: {
      parameter_key: 'SB',
      label: 'Sb',
      intercept: 0,
      slope: 285.714285714286,
      formula: 'cons = 0 + 285.714286 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    TL: {
      parameter_key: 'TL',
      label: 'Tl',
      intercept: 0,
      slope: 178.571428571429,
      formula: 'cons = 0 + 178.571429 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
    ZN: {
      parameter_key: 'ZN',
      label: 'Zn',
      intercept: 0,
      slope: 6.87757909216,
      formula: 'cons = 0 + 6.877579 * abs',
      effective_date: null,
      source_mode: 'fallback',
    },
  }, @json($absorbanceReferences));

  const NO2_REFERENCE = Object.assign({
    factorVolume: 24.45,
    molecularWeight: 46,
    factorUgm3: 1881,
    formulaPpm: 'kons * vol * (273 + sk) * 760 * 24.45 / (fr * waktu * 298 * p * 46)',
    formulaUgm3: 'ppm * 1881',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    lodAmbien: {
      kons: '0.0078',
      vol: '10.0',
      fr: '0.400',
      waktu: '60',
      sk: '25.0',
      p: '760',
      ppm: '0.0017',
      ugm3: '3.2493',
    },
    lodLk: {
      kons: '0.0078',
      vol: '10.0',
      fr: '0.400',
      waktu: '60',
      sk: '25.0',
      p: '760',
      ppm: '0.0017',
      ugm3: '3.2493',
    },
  }, @json($no2Reference));

  const BENZENE_REFERENCE = Object.assign({
    yValue: 3625144,
    xValue: 0.0000003,
    formulaY: 'Y = 3625144',
    formulaX: 'X = 1 / Y',
    formulaConc: 'conc = area * x',
    factorMass: 1000,
    factorVolume: 24.45,
    pressureFactor: 760,
    denominatorTemperature: 298,
    molecularWeight: 78,
    factorUgm3: 3190.2,
    formulaPpm: 'kons * vol * (273 + sk) * 1000 * 24.45 * 760 / (fr * waktu * 298 * 78 * p)',
    formulaUgm3: 'ppm * 3190.2',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    lod: {
      kons: '0.00394',
      vol: '1.0',
      fr: '0.200',
      waktu: '480',
      sk: '31.3',
      p: '760',
      ppm: '0.0131',
      ugm3: '41.9000',
    },
  }, @json($benzeneReference));
  const TOLUENE_REFERENCE = Object.assign({
    yValue: 4714408,
    xValue: 0.000000212116,
    formulaY: 'Y = 4714408',
    formulaX: 'X = 1 / Y',
    formulaConc: 'conc = area * x',
    factorMass: 1000,
    factorVolume: 24.45,
    pressureFactor: 760,
    denominatorTemperature: 298,
    molecularWeight: 92,
    factorUgm3: 3762.8,
    formulaPpm: 'kons * vol * (273 + sk) * 1000 * 24.45 * 760 / (fr * waktu * 298 * 92 * p)',
    formulaUgm3: 'ppm * 3762.8',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    lod: {
      kons: '0.00299',
      vol: '1.0',
      fr: '0.200',
      waktu: '480',
      sk: '31.3',
      p: '760',
      ppm: '0.0085',
      ugm3: '31.7974',
    },
  }, @json($tolueneReference));
  const XYLENE_REFERENCE = Object.assign({
    yValue: 4714408,
    xValue: 0.000000212116,
    formulaY: 'Y = 4714408',
    formulaX: 'X = 1 / Y',
    formulaConc: 'conc = area * x',
    factorMass: 1000,
    factorVolume: 24.45,
    pressureFactor: 760,
    denominatorTemperature: 298,
    molecularWeight: 106,
    factorUgm3: 4335.4,
    formulaPpm: 'kons * vol * (273 + sk) * 1000 * 24.45 * 760 / (fr * waktu * 298 * 106 * p)',
    formulaUgm3: 'ppm * 4335.4',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    lod: {
      kons: '0.00341',
      vol: '1.0',
      fr: '0.200',
      waktu: '480',
      sk: '31.3',
      p: '760',
      ppm: '0.0084',
      ugm3: '36.2647',
    },
  }, @json($xyleneReference));

  const DEBU_AMBIEN_REFERENCE = Object.assign({
    berat: '0.0001',
    fr: '500.00',
    waktu: '60',
    sk: '25.0',
    p: '760',
    formulaSelisih: 'akhir - awal',
    formulaBerat: 'selisih - rata_rata_blanko',
    formulaKadar: 'berat * 1000000 * (273 + sk) * 760 / (waktu * fr * 298 * p)',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    constants: {
      massFactor: 1000000,
      temperatureOffset: 273,
      pressureFactor: 760,
      denominatorTemperature: 298,
    },
    sample: {
      berat: '0.0090',
      fr: '500',
      waktu: '60',
      sk: '32.0',
      p: '758',
      kadar: '0.3079',
    },
  }, @json($debuAmbienReference));
  const DEBU_TOTAL_REFERENCE = Object.assign({}, DEBU_AMBIEN_REFERENCE, {
    berat: '0.0001',
    fr: '10.00',
    waktu: '60',
    sk: '25.0',
    p: '760',
    sample: {
      berat: '0.0004833',
      fr: '10',
      waktu: '60',
      sk: '25.4',
      p: '758',
      kadar: '0.8088',
    },
  });

  DEBU_AMBIEN_REFERENCE.berat = normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.berat);
  DEBU_AMBIEN_REFERENCE.fr = normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.fr);
  DEBU_AMBIEN_REFERENCE.waktu = normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.waktu);
  DEBU_AMBIEN_REFERENCE.sk = normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.sk);
  DEBU_AMBIEN_REFERENCE.p = normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.p);
  DEBU_AMBIEN_REFERENCE.sample = Object.assign({}, DEBU_AMBIEN_REFERENCE.sample || {}, {
    berat: normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.sample?.berat),
    fr: normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.sample?.fr),
    waktu: normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.sample?.waktu),
    sk: normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.sample?.sk),
    p: normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.sample?.p),
    kadar: normalizeNumericInputValue(DEBU_AMBIEN_REFERENCE.sample?.kadar),
  });
  DEBU_TOTAL_REFERENCE.berat = normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.berat);
  DEBU_TOTAL_REFERENCE.fr = normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.fr);
  DEBU_TOTAL_REFERENCE.waktu = normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.waktu);
  DEBU_TOTAL_REFERENCE.sk = normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.sk);
  DEBU_TOTAL_REFERENCE.p = normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.p);
  DEBU_TOTAL_REFERENCE.sample = Object.assign({}, DEBU_TOTAL_REFERENCE.sample || {}, {
    berat: normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.sample?.berat),
    fr: normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.sample?.fr),
    waktu: normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.sample?.waktu),
    sk: normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.sample?.sk),
    p: normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.sample?.p),
    kadar: normalizeNumericInputValue(DEBU_TOTAL_REFERENCE.sample?.kadar),
  });

  const NH3_REFERENCE = Object.assign({
    factorPpm: 1.438,
    factorUgm3: 695.3,
    formulaPpm: 'kons * (vol / 10) * (273 + sk) * 1.438 * 760 / (waktu * fr * 298 * p)',
    formulaUgm3: 'ppm * 695.3',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    mdlAmbien: {
      kons: '0.0843',
      vol: '10.0',
      fr: '1.000',
      waktu: '60',
      sk: '25.0',
      p: '760',
      ppm: '0.0020',
      ugm3: '1.4048',
    },
    mdlLk: {
      kons: '0.1240',
      vol: '10.0',
      fr: '1.000',
      waktu: '30',
      sk: '25.0',
      p: '760',
      ppm: '0.0059',
      ugm3: '4.1327',
    },
  }, @json($nh3Reference));

  const OX_REFERENCE = Object.assign({
    consIntercept: 0.0244,
    consSlope: 0.8264,
    factorUgm3: 1963.2,
    formulaCons: 'cons = 0.0244 + 0.8264 * abs',
    formulaPpm: 'kons * vol * (273 + sk) * 760 / (fr * waktu * 298 * p)',
    formulaUgm3: 'ppm * 1963.2',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    lodAmbien: {
      kons: '0.00744',
      vol: '10.0',
      fr: '1.000',
      waktu: '30',
      sk: '25.0',
      p: '760',
      ppm: '0.002480',
      ugm3: '4.8687',
    },
    lodLk: {
      kons: '0.00506',
      vol: '10.0',
      fr: '1.000',
      waktu: '30',
      sk: '25.0',
      p: '760',
      ppm: '0.001687',
      ugm3: '3.3113',
    },
  }, @json($oxReference));

  NH3_REFERENCE.mdlAmbien = Object.assign({}, NH3_REFERENCE.mdlAmbien || {}, {
    kons: normalizeNumericInputValue(NH3_REFERENCE.mdlAmbien?.kons),
    vol: normalizeNumericInputValue(NH3_REFERENCE.mdlAmbien?.vol),
    fr: normalizeNumericInputValue(NH3_REFERENCE.mdlAmbien?.fr),
    waktu: normalizeNumericInputValue(NH3_REFERENCE.mdlAmbien?.waktu),
    sk: normalizeNumericInputValue(NH3_REFERENCE.mdlAmbien?.sk),
    p: normalizeNumericInputValue(NH3_REFERENCE.mdlAmbien?.p),
    ppm: normalizeNumericInputValue(NH3_REFERENCE.mdlAmbien?.ppm),
    ugm3: normalizeNumericInputValue(NH3_REFERENCE.mdlAmbien?.ugm3),
  });
  NH3_REFERENCE.mdlLk = Object.assign({}, NH3_REFERENCE.mdlLk || {}, {
    kons: normalizeNumericInputValue(NH3_REFERENCE.mdlLk?.kons),
    vol: normalizeNumericInputValue(NH3_REFERENCE.mdlLk?.vol),
    fr: normalizeNumericInputValue(NH3_REFERENCE.mdlLk?.fr),
    waktu: normalizeNumericInputValue(NH3_REFERENCE.mdlLk?.waktu),
    sk: normalizeNumericInputValue(NH3_REFERENCE.mdlLk?.sk),
    p: normalizeNumericInputValue(NH3_REFERENCE.mdlLk?.p),
    ppm: normalizeNumericInputValue(NH3_REFERENCE.mdlLk?.ppm),
    ugm3: normalizeNumericInputValue(NH3_REFERENCE.mdlLk?.ugm3),
  });

  const H2S_REFERENCE = Object.assign({
    factorVolume: 24.45,
    molecularWeight: 34,
    factorUgm3: 1390.6,
    formulaPpm: 'kons * vol * (273 + sk) * 760 * 24.45 / (fr * waktu * 298 * p * 34)',
    formulaUgm3: 'ppm * 1390.6',
    sheetName: '',
    sourcePath: '',
    sourceMode: 'fallback',
    lodAmbien: {
      kons: '0.0002',
      vol: '10.0',
      fr: '1.500',
      waktu: '60',
      sk: '25.0',
      p: '760',
      ppm: '0.0000',
      ugm3: '0.0222',
    },
    lodLk: {
      kons: '0.0002',
      vol: '10.0',
      fr: '1.500',
      waktu: '30',
      sk: '25.0',
      p: '760',
      ppm: '0.0000',
      ugm3: '0.0444',
    },
  }, @json($h2sReference));
  const PARAMETER_LOD_REFERENCES = Object.assign({}, @json($parameterLodReferences));

  H2S_REFERENCE.lodAmbien = Object.assign({}, H2S_REFERENCE.lodAmbien || {}, {
    kons: normalizeNumericInputValue(H2S_REFERENCE.lodAmbien?.kons),
    vol: normalizeNumericInputValue(H2S_REFERENCE.lodAmbien?.vol),
    fr: normalizeNumericInputValue(H2S_REFERENCE.lodAmbien?.fr),
    waktu: normalizeNumericInputValue(H2S_REFERENCE.lodAmbien?.waktu),
    sk: normalizeNumericInputValue(H2S_REFERENCE.lodAmbien?.sk),
    p: normalizeNumericInputValue(H2S_REFERENCE.lodAmbien?.p),
    ppm: normalizeNumericInputValue(H2S_REFERENCE.lodAmbien?.ppm),
    ugm3: normalizeNumericInputValue(H2S_REFERENCE.lodAmbien?.ugm3),
  });
  H2S_REFERENCE.lodLk = Object.assign({}, H2S_REFERENCE.lodLk || {}, {
    kons: normalizeNumericInputValue(H2S_REFERENCE.lodLk?.kons),
    vol: normalizeNumericInputValue(H2S_REFERENCE.lodLk?.vol),
    fr: normalizeNumericInputValue(H2S_REFERENCE.lodLk?.fr),
    waktu: normalizeNumericInputValue(H2S_REFERENCE.lodLk?.waktu),
    sk: normalizeNumericInputValue(H2S_REFERENCE.lodLk?.sk),
    p: normalizeNumericInputValue(H2S_REFERENCE.lodLk?.p),
    ppm: normalizeNumericInputValue(H2S_REFERENCE.lodLk?.ppm),
    ugm3: normalizeNumericInputValue(H2S_REFERENCE.lodLk?.ugm3),
  });
  Object.keys(PARAMETER_LOD_REFERENCES).forEach((key) => {
    const row = PARAMETER_LOD_REFERENCES[key] || {};
    PARAMETER_LOD_REFERENCES[key] = Object.assign({}, row, {
      kons: normalizeNumericInputValue(row.kons),
      vol: normalizeNumericInputValue(row.vol),
      waktu: normalizeNumericInputValue(row.waktu),
      fr: normalizeNumericInputValue(row.fr),
      sk: normalizeNumericInputValue(row.sk),
      pm: normalizeNumericInputValue(row.pm),
      sample_kons: normalizeNumericInputValue(row.sample_kons),
      sample_vol: normalizeNumericInputValue(row.sample_vol),
      sample_waktu: normalizeNumericInputValue(row.sample_waktu),
      sample_fr: normalizeNumericInputValue(row.sample_fr),
      sample_sk: normalizeNumericInputValue(row.sample_sk),
      sample_pm: normalizeNumericInputValue(row.sample_pm),
      sample_ppm: normalizeNumericInputValue(row.sample_ppm),
      sample_ugm3: normalizeNumericInputValue(row.sample_ugm3),
    });
  });

  const mergeReferenceObject = (target, payload, nestedKeys = []) => {
    if (!target || !payload || typeof payload !== 'object') return;
    Object.entries(payload).forEach(([key, value]) => {
      if (nestedKeys.includes(key) && value && typeof value === 'object' && !Array.isArray(value)) {
        target[key] = Object.assign({}, target[key] || {}, value);
        return;
      }
      target[key] = value;
    });
  };

  const loadedFormulaReferenceKeys = new Set();
  const formulaReferencePromises = new Map();
  let pendingFormulaModalTrigger = null;

  const getFormulaReferenceKeysForTrigger = (trigger) => {
    const param = String(trigger?.getAttribute('data-param') || '').trim().toLowerCase();
    const category = String(trigger?.getAttribute('data-param-category') || '').replace(/\s+/g, '').toLowerCase();
    const isAmbienOrLk = ['a', 'ambien', 'ambient', 'udaraambien', 'lk', 'lingkungankerja'].includes(category)
      || param.includes('ambien');

    if (param.includes('kadar debu logam') && param.includes('aas')) {
      const metalMatch = param.match(/\b(pb|cd|cr|as|hg|co|sb|tl|cu|zn)\b/);
      if (metalMatch) {
        return [metalMatch[1] === 'hg' ? 'hg_aas' : metalMatch[1]];
      }
    }
    if (param.includes('btx')) return ['benzene', 'toluene', 'xylene'];
    if (param.includes('toluene') || param.includes('toluena') || param.includes('toluen')) return ['toluene'];
    if (param.includes('xylene') || param.includes('xilena')) return ['xylene'];
    if (/\bhc\b/.test(param) || param.includes('benzene') || param.includes('benzena') || param.includes('benzen')) return ['benzene'];
    if ((param.includes('so2') || param.includes('sulfur dioksida')) && isAmbienOrLk) return ['so2_ambien'];
    if (/\bhf\b/.test(param) || param.includes('hidrogen fluorida') || param.includes('hydrogen fluoride')) return ['hf_emisi'];
    if (/\bhcl\b/.test(param) || param.includes('hidrogen klorida') || param.includes('hydrogen chloride')) return ['hcl_emisi'];
    if ((/\bhg\b/.test(param) || param.includes('merkuri') || param.includes('mercury')) && !isAmbienOrLk) return ['hg_emisi'];
    if (param.includes('nh3') || param.includes('amonia') || param.includes('ammonia')) return ['nh3'];
    if (param.includes('h2s') || param.includes('hidrogen sulfida') || param.includes('hydrogen sulfide')) return ['h2s'];
    if (param.includes('no2')) return ['no2'];
    if (param.includes('oksidan') || param.includes('oxidant') || /\box\b/.test(param)) return ['ox'];
    if (param.includes('debu') || param.includes('kdtlr') || /pm\s*2[,.]?5|pm\s*10/.test(param)) return ['debu_ambien'];

    return [];
  };

  const areFormulaReferencesLoaded = (keys) => keys.every((key) => loadedFormulaReferenceKeys.has(key));

  const hydrateFormulaReferences = (requestedKeys) => {
    const keys = Array.from(new Set(requestedKeys || [])).filter((key) => !loadedFormulaReferenceKeys.has(key));
    if (!keys.length) return Promise.resolve(true);
    if (!formulaReferencesUrl) return Promise.resolve(false);

    const requestKey = keys.slice().sort().join(',');
    if (formulaReferencePromises.has(requestKey)) {
      return formulaReferencePromises.get(requestKey);
    }

    const requestUrl = new URL(formulaReferencesUrl, window.location.origin);
    keys.forEach((key) => requestUrl.searchParams.append('keys[]', key));

    const request = fetch(requestUrl.toString(), {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })
      .then(async (response) => {
        if (!response.ok) {
          throw new Error('Referensi formula gagal dimuat.');
        }

        const payload = await response.json();
        if (!payload || typeof payload !== 'object') {
          throw new Error('Referensi formula tidak valid.');
        }

        mergeReferenceObject(SO2_AMBIEN_REFERENCE, payload.so2_ambien, ['sample']);
        mergeReferenceObject(HF_EMISI_REFERENCE, payload.hf_emisi, ['lod', 'sample', 'constants']);
        mergeReferenceObject(HCL_EMISI_REFERENCE, payload.hcl_emisi, ['lod', 'sample', 'constants']);
        mergeReferenceObject(HG_EMISI_REFERENCE, payload.hg_emisi, ['lod', 'sample', 'constants']);
        mergeReferenceObject(PB_REFERENCE, payload.pb, ['lod', 'constants']);
        mergeReferenceObject(CD_REFERENCE, payload.cd, ['lod', 'constants']);
        mergeReferenceObject(CU_REFERENCE, payload.cu, ['lod', 'constants']);
        mergeReferenceObject(CR_REFERENCE, payload.cr, ['lod', 'constants']);
        mergeReferenceObject(AS_REFERENCE, payload.as, ['lod', 'constants']);
        mergeReferenceObject(HG_AAS_REFERENCE, payload.hg_aas, ['lod', 'constants']);
        mergeReferenceObject(CO_REFERENCE, payload.co, ['lod', 'constants']);
        mergeReferenceObject(SB_REFERENCE, payload.sb, ['lod', 'constants']);
        mergeReferenceObject(TL_REFERENCE, payload.tl, ['lod', 'constants']);
        mergeReferenceObject(ZN_REFERENCE, payload.zn, ['lod', 'constants']);
        mergeReferenceObject(BENZENE_REFERENCE, payload.benzene, ['lod']);
        mergeReferenceObject(TOLUENE_REFERENCE, payload.toluene, ['lod']);
        mergeReferenceObject(XYLENE_REFERENCE, payload.xylene, ['lod']);
        mergeReferenceObject(DEBU_AMBIEN_REFERENCE, payload.debu_ambien, ['sample', 'constants']);
        mergeReferenceObject(NH3_REFERENCE, payload.nh3, ['mdlAmbien', 'mdlLk']);
        mergeReferenceObject(OX_REFERENCE, payload.ox, ['lodAmbien', 'lodLk']);
        mergeReferenceObject(H2S_REFERENCE, payload.h2s, ['lodAmbien', 'lodLk']);
        mergeReferenceObject(NO2_REFERENCE, payload.no2, ['lodAmbien', 'lodLk']);

        // Absorbance dan LOD parameter sudah dimuat segar saat render.
        keys.forEach((key) => loadedFormulaReferenceKeys.add(key));
        return true;
      })
      .catch((error) => {
        console.warn('Gagal memuat referensi rumus prepanalisa.', error);
        return false;
      })
      .finally(() => {
        formulaReferencePromises.delete(requestKey);
      });

    formulaReferencePromises.set(requestKey, request);
    return request;
  };

  const getCurrentParameterLodReference = () => {
    const serviceParamId = String(currentLocation?.service_parameter_id || '').trim();
    if (!serviceParamId) return {};
    return PARAMETER_LOD_REFERENCES[serviceParamId] || {};
  };
  OX_REFERENCE.lodAmbien = Object.assign({}, OX_REFERENCE.lodAmbien || {}, {
    kons: normalizeNumericInputValue(OX_REFERENCE.lodAmbien?.kons),
    vol: normalizeNumericInputValue(OX_REFERENCE.lodAmbien?.vol),
    fr: normalizeNumericInputValue(OX_REFERENCE.lodAmbien?.fr),
    waktu: normalizeNumericInputValue(OX_REFERENCE.lodAmbien?.waktu),
    sk: normalizeNumericInputValue(OX_REFERENCE.lodAmbien?.sk),
    p: normalizeNumericInputValue(OX_REFERENCE.lodAmbien?.p),
    ppm: normalizeNumericInputValue(OX_REFERENCE.lodAmbien?.ppm),
    ugm3: normalizeNumericInputValue(OX_REFERENCE.lodAmbien?.ugm3),
  });
  OX_REFERENCE.lodLk = Object.assign({}, OX_REFERENCE.lodLk || {}, {
    kons: normalizeNumericInputValue(OX_REFERENCE.lodLk?.kons),
    vol: normalizeNumericInputValue(OX_REFERENCE.lodLk?.vol),
    fr: normalizeNumericInputValue(OX_REFERENCE.lodLk?.fr),
    waktu: normalizeNumericInputValue(OX_REFERENCE.lodLk?.waktu),
    sk: normalizeNumericInputValue(OX_REFERENCE.lodLk?.sk),
    p: normalizeNumericInputValue(OX_REFERENCE.lodLk?.p),
    ppm: normalizeNumericInputValue(OX_REFERENCE.lodLk?.ppm),
    ugm3: normalizeNumericInputValue(OX_REFERENCE.lodLk?.ugm3),
  });
  ABSORBANCE_REFERENCES.NO2 = Object.assign({}, ABSORBANCE_REFERENCES.NO2 || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.NO2?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.NO2?.slope ?? 0.9517),
  });
  ABSORBANCE_REFERENCES.OX = Object.assign({}, ABSORBANCE_REFERENCES.OX || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.OX?.intercept ?? 0.0244),
    slope: Number(ABSORBANCE_REFERENCES.OX?.slope ?? 0.8264),
  });
  ABSORBANCE_REFERENCES.PB = Object.assign({}, ABSORBANCE_REFERENCES.PB || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.PB?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.PB?.slope ?? 222.222),
    curve_y: Number(ABSORBANCE_REFERENCES.PB?.curve_y ?? (ABSORBANCE_REFERENCES.PB?.slope ? (1 / Number(ABSORBANCE_REFERENCES.PB.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.PB?.curve_x ?? ABSORBANCE_REFERENCES.PB?.slope ?? 222.222),
  });
  ABSORBANCE_REFERENCES.CD = Object.assign({}, ABSORBANCE_REFERENCES.CD || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.CD?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.CD?.slope ?? 8.70322019147),
    curve_y: Number(ABSORBANCE_REFERENCES.CD?.curve_y ?? (ABSORBANCE_REFERENCES.CD?.slope ? (1 / Number(ABSORBANCE_REFERENCES.CD.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.CD?.curve_x ?? ABSORBANCE_REFERENCES.CD?.slope ?? 8.70322019147),
  });
  ABSORBANCE_REFERENCES.CU = Object.assign({}, ABSORBANCE_REFERENCES.CU || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.CU?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.CU?.slope ?? 22.37136465324),
    curve_y: Number(ABSORBANCE_REFERENCES.CU?.curve_y ?? (ABSORBANCE_REFERENCES.CU?.slope ? (1 / Number(ABSORBANCE_REFERENCES.CU.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.CU?.curve_x ?? ABSORBANCE_REFERENCES.CU?.slope ?? 22.37136465324),
  });
  ABSORBANCE_REFERENCES.AS = Object.assign({}, ABSORBANCE_REFERENCES.AS || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.AS?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.AS?.slope ?? 39.370078740157),
    curve_y: Number(ABSORBANCE_REFERENCES.AS?.curve_y ?? (ABSORBANCE_REFERENCES.AS?.slope ? (1 / Number(ABSORBANCE_REFERENCES.AS.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.AS?.curve_x ?? ABSORBANCE_REFERENCES.AS?.slope ?? 39.370078740157),
  });
  ABSORBANCE_REFERENCES.HG = Object.assign({}, ABSORBANCE_REFERENCES.HG || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.HG?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.HG?.slope ?? 277.777777777778),
    curve_y: Number(ABSORBANCE_REFERENCES.HG?.curve_y ?? (ABSORBANCE_REFERENCES.HG?.slope ? (1 / Number(ABSORBANCE_REFERENCES.HG.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.HG?.curve_x ?? ABSORBANCE_REFERENCES.HG?.slope ?? 277.777777777778),
  });
  ABSORBANCE_REFERENCES.CO = Object.assign({}, ABSORBANCE_REFERENCES.CO || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.CO?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.CO?.slope ?? 28.901734104046),
    curve_y: Number(ABSORBANCE_REFERENCES.CO?.curve_y ?? (ABSORBANCE_REFERENCES.CO?.slope ? (1 / Number(ABSORBANCE_REFERENCES.CO.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.CO?.curve_x ?? ABSORBANCE_REFERENCES.CO?.slope ?? 28.901734104046),
  });
  ABSORBANCE_REFERENCES.SB = Object.assign({}, ABSORBANCE_REFERENCES.SB || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.SB?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.SB?.slope ?? 285.714285714286),
    curve_y: Number(ABSORBANCE_REFERENCES.SB?.curve_y ?? (ABSORBANCE_REFERENCES.SB?.slope ? (1 / Number(ABSORBANCE_REFERENCES.SB.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.SB?.curve_x ?? ABSORBANCE_REFERENCES.SB?.slope ?? 285.714285714286),
  });
  ABSORBANCE_REFERENCES.TL = Object.assign({}, ABSORBANCE_REFERENCES.TL || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.TL?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.TL?.slope ?? 178.571428571429),
    curve_y: Number(ABSORBANCE_REFERENCES.TL?.curve_y ?? (ABSORBANCE_REFERENCES.TL?.slope ? (1 / Number(ABSORBANCE_REFERENCES.TL.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.TL?.curve_x ?? ABSORBANCE_REFERENCES.TL?.slope ?? 178.571428571429),
  });
  ABSORBANCE_REFERENCES.CR = Object.assign({}, ABSORBANCE_REFERENCES.CR || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.CR?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.CR?.slope ?? 40.32258064516),
    curve_y: Number(ABSORBANCE_REFERENCES.CR?.curve_y ?? (ABSORBANCE_REFERENCES.CR?.slope ? (1 / Number(ABSORBANCE_REFERENCES.CR.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.CR?.curve_x ?? ABSORBANCE_REFERENCES.CR?.slope ?? 40.32258064516),
  });
  ABSORBANCE_REFERENCES.ZN = Object.assign({}, ABSORBANCE_REFERENCES.ZN || {}, {
    intercept: Number(ABSORBANCE_REFERENCES.ZN?.intercept ?? 0),
    slope: Number(ABSORBANCE_REFERENCES.ZN?.slope ?? 6.87757909216),
    curve_y: Number(ABSORBANCE_REFERENCES.ZN?.curve_y ?? (ABSORBANCE_REFERENCES.ZN?.slope ? (1 / Number(ABSORBANCE_REFERENCES.ZN.slope)) : 0)),
    curve_x: Number(ABSORBANCE_REFERENCES.ZN?.curve_x ?? ABSORBANCE_REFERENCES.ZN?.slope ?? 6.87757909216),
  });
  PB_REFERENCE.lod = Object.assign({}, PB_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(PB_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(PB_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(PB_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(PB_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(PB_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(PB_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(PB_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(PB_REFERENCE.lod?.mgm3),
  });
  CD_REFERENCE.lod = Object.assign({}, CD_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(CD_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(CD_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(CD_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(CD_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(CD_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(CD_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(CD_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(CD_REFERENCE.lod?.mgm3),
  });
  CU_REFERENCE.lod = Object.assign({}, CU_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(CU_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(CU_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(CU_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(CU_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(CU_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(CU_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(CU_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(CU_REFERENCE.lod?.mgm3),
  });
  AS_REFERENCE.lod = Object.assign({}, AS_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(AS_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(AS_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(AS_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(AS_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(AS_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(AS_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(AS_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(AS_REFERENCE.lod?.mgm3),
  });
  HG_AAS_REFERENCE.lod = Object.assign({}, HG_AAS_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(HG_AAS_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(HG_AAS_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(HG_AAS_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(HG_AAS_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(HG_AAS_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(HG_AAS_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(HG_AAS_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(HG_AAS_REFERENCE.lod?.mgm3),
  });
  CO_REFERENCE.lod = Object.assign({}, CO_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(CO_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(CO_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(CO_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(CO_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(CO_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(CO_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(CO_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(CO_REFERENCE.lod?.mgm3),
  });
  SB_REFERENCE.lod = Object.assign({}, SB_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(SB_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(SB_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(SB_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(SB_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(SB_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(SB_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(SB_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(SB_REFERENCE.lod?.mgm3),
  });
  TL_REFERENCE.lod = Object.assign({}, TL_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(TL_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(TL_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(TL_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(TL_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(TL_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(TL_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(TL_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(TL_REFERENCE.lod?.mgm3),
  });
  CR_REFERENCE.lod = Object.assign({}, CR_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(CR_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(CR_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(CR_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(CR_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(CR_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(CR_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(CR_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(CR_REFERENCE.lod?.mgm3),
  });
  ZN_REFERENCE.lod = Object.assign({}, ZN_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(ZN_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(ZN_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(ZN_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(ZN_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(ZN_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(ZN_REFERENCE.lod?.p),
    ugm3: normalizeNumericInputValue(ZN_REFERENCE.lod?.ugm3),
    mgm3: normalizeNumericInputValue(ZN_REFERENCE.lod?.mgm3),
  });
  const absorbanceReferenceFetchState = new Map();

  const getCurveMetalParameterKey = () => {
    if (isCdMetalAas()) return 'CD';
    if (isAsMetalAas()) return 'AS';
    if (isHgMetalAas()) return 'HG';
    if (isCoMetalAas()) return 'CO';
    if (isSbMetalAas()) return 'SB';
    if (isTlMetalAas()) return 'TL';
    if (isCrMetalAas()) return 'CR';
    if (isCuMetalAas()) return 'CU';
    if (isZnMetalAas()) return 'ZN';
    return 'PB';
  };

  const applyAbsorbanceReferencePayload = (parameterKey, payload = {}) => {
    const normalizedKey = String(parameterKey || '').toUpperCase();
    if (!normalizedKey || !ABSORBANCE_REFERENCES[normalizedKey]) return;

    const nextReference = Object.assign({}, ABSORBANCE_REFERENCES[normalizedKey] || {}, payload || {});
    nextReference.intercept = Number(nextReference.intercept ?? 0);
    nextReference.slope = Number(nextReference.slope ?? 0);
    nextReference.curve_y = Number(nextReference.curve_y ?? (nextReference.slope ? (1 / Number(nextReference.slope)) : 0));
    nextReference.curve_x = Number(nextReference.curve_x ?? nextReference.slope ?? 0);
    ABSORBANCE_REFERENCES[normalizedKey] = nextReference;

    const targetReference = normalizedKey === 'CD'
      ? CD_REFERENCE
      : (normalizedKey === 'AS'
        ? AS_REFERENCE
        : (normalizedKey === 'HG'
          ? HG_AAS_REFERENCE
        : (normalizedKey === 'CO'
          ? CO_REFERENCE
        : (normalizedKey === 'SB'
          ? SB_REFERENCE
        : (normalizedKey === 'TL'
        ? TL_REFERENCE
        : (normalizedKey === 'CR'
        ? CR_REFERENCE
        : (normalizedKey === 'CU'
        ? CU_REFERENCE
        : (normalizedKey === 'ZN' ? ZN_REFERENCE : PB_REFERENCE))))))));

    if (targetReference) {
      targetReference.concIntercept = Number(nextReference.intercept ?? targetReference.concIntercept ?? 0);
      targetReference.concSlope = Number(nextReference.curve_x ?? nextReference.slope ?? targetReference.concSlope ?? 0);
      targetReference.curveY = Number(nextReference.curve_y ?? targetReference.curveY ?? 0);
      targetReference.curveX = Number(nextReference.curve_x ?? nextReference.slope ?? targetReference.curveX ?? 0);
      targetReference.absorbanceSourceMode = String(nextReference.source_mode || nextReference.absorbanceSourceMode || targetReference.absorbanceSourceMode || 'database');
    }
  };

  const fetchLatestAbsorbanceReference = async (parameterKey) => {
    const normalizedKey = String(parameterKey || '').toUpperCase();
    if (!normalizedKey) return null;
    const pending = absorbanceReferenceFetchState.get(normalizedKey);
    if (pending) {
      return pending;
    }

    const request = fetch(ABSORBANCE_REFERENCE_URL_TEMPLATE.replace('__PARAM__', normalizedKey), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
      credentials: 'same-origin',
    })
      .then(async (response) => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          throw new Error(data.message || 'Gagal mengambil rumus terbaru.');
        }
        const reference = data.reference || {};
        applyAbsorbanceReferencePayload(normalizedKey, reference);
        return reference;
      })
      .finally(() => {
        absorbanceReferenceFetchState.delete(normalizedKey);
      });

    absorbanceReferenceFetchState.set(normalizedKey, request);
    return request;
  };
  NO2_REFERENCE.lodAmbien = Object.assign({}, NO2_REFERENCE.lodAmbien || {}, {
    kons: normalizeNumericInputValue(NO2_REFERENCE.lodAmbien?.kons),
    vol: normalizeNumericInputValue(NO2_REFERENCE.lodAmbien?.vol),
    fr: normalizeNumericInputValue(NO2_REFERENCE.lodAmbien?.fr),
    waktu: normalizeNumericInputValue(NO2_REFERENCE.lodAmbien?.waktu),
    sk: normalizeNumericInputValue(NO2_REFERENCE.lodAmbien?.sk),
    p: normalizeNumericInputValue(NO2_REFERENCE.lodAmbien?.p),
    ppm: normalizeNumericInputValue(NO2_REFERENCE.lodAmbien?.ppm),
    ugm3: normalizeNumericInputValue(NO2_REFERENCE.lodAmbien?.ugm3),
  });
  NO2_REFERENCE.lodLk = Object.assign({}, NO2_REFERENCE.lodLk || {}, {
    kons: normalizeNumericInputValue(NO2_REFERENCE.lodLk?.kons),
    vol: normalizeNumericInputValue(NO2_REFERENCE.lodLk?.vol),
    fr: normalizeNumericInputValue(NO2_REFERENCE.lodLk?.fr),
    waktu: normalizeNumericInputValue(NO2_REFERENCE.lodLk?.waktu),
    sk: normalizeNumericInputValue(NO2_REFERENCE.lodLk?.sk),
    p: normalizeNumericInputValue(NO2_REFERENCE.lodLk?.p),
    ppm: normalizeNumericInputValue(NO2_REFERENCE.lodLk?.ppm),
    ugm3: normalizeNumericInputValue(NO2_REFERENCE.lodLk?.ugm3),
  });
  BENZENE_REFERENCE.yValue = Number(BENZENE_REFERENCE.yValue ?? 3625144);
  BENZENE_REFERENCE.xValue = Number(BENZENE_REFERENCE.xValue ?? 0.0000003);
  BENZENE_REFERENCE.lod = Object.assign({}, BENZENE_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(BENZENE_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(BENZENE_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(BENZENE_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(BENZENE_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(BENZENE_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(BENZENE_REFERENCE.lod?.p),
    ppm: normalizeNumericInputValue(BENZENE_REFERENCE.lod?.ppm),
    ugm3: normalizeNumericInputValue(BENZENE_REFERENCE.lod?.ugm3),
  });
  TOLUENE_REFERENCE.yValue = Number(TOLUENE_REFERENCE.yValue ?? 4714408);
  TOLUENE_REFERENCE.xValue = Number(TOLUENE_REFERENCE.xValue ?? 0.000000212116);
  TOLUENE_REFERENCE.lod = Object.assign({}, TOLUENE_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(TOLUENE_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(TOLUENE_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(TOLUENE_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(TOLUENE_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(TOLUENE_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(TOLUENE_REFERENCE.lod?.p),
    ppm: normalizeNumericInputValue(TOLUENE_REFERENCE.lod?.ppm),
    ugm3: normalizeNumericInputValue(TOLUENE_REFERENCE.lod?.ugm3),
  });
  XYLENE_REFERENCE.yValue = Number(XYLENE_REFERENCE.yValue ?? 4714408);
  XYLENE_REFERENCE.xValue = Number(XYLENE_REFERENCE.xValue ?? 0.000000212116);
  XYLENE_REFERENCE.lod = Object.assign({}, XYLENE_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(XYLENE_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(XYLENE_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(XYLENE_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(XYLENE_REFERENCE.lod?.waktu),
    sk: normalizeNumericInputValue(XYLENE_REFERENCE.lod?.sk),
    p: normalizeNumericInputValue(XYLENE_REFERENCE.lod?.p),
    ppm: normalizeNumericInputValue(XYLENE_REFERENCE.lod?.ppm),
    ugm3: normalizeNumericInputValue(XYLENE_REFERENCE.lod?.ugm3),
  });

  SO2_AMBIEN_REFERENCE.kons = normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.kons);
  SO2_AMBIEN_REFERENCE.vol = normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.vol);
  SO2_AMBIEN_REFERENCE.waktu = normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.waktu);
  SO2_AMBIEN_REFERENCE.fr = normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.fr);
  SO2_AMBIEN_REFERENCE.sk = normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sk);
  SO2_AMBIEN_REFERENCE.pm = normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.pm);
  SO2_AMBIEN_REFERENCE.sample = Object.assign({}, SO2_AMBIEN_REFERENCE.sample || {}, {
    kons: normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sample?.kons),
    vol: normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sample?.vol),
    waktu: normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sample?.waktu),
    fr: normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sample?.fr),
    sk: normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sample?.sk),
    pm: normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sample?.pm),
    ppm: normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sample?.ppm),
    ugm3: normalizeNumericInputValue(SO2_AMBIEN_REFERENCE.sample?.ugm3),
  });
  HF_EMISI_REFERENCE.lod = Object.assign({}, HF_EMISI_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(HF_EMISI_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(HF_EMISI_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(HF_EMISI_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(HF_EMISI_REFERENCE.lod?.waktu),
    titr_hf: normalizeNumericInputValue(HF_EMISI_REFERENCE.lod?.titr_hf),
    tm: normalizeNumericInputValue(HF_EMISI_REFERENCE.lod?.tm),
    p: normalizeNumericInputValue(HF_EMISI_REFERENCE.lod?.p),
    kadar: normalizeNumericInputValue(HF_EMISI_REFERENCE.lod?.kadar),
  });
  HF_EMISI_REFERENCE.sample = Object.assign({}, HF_EMISI_REFERENCE.sample || {}, {
    kons: normalizeNumericInputValue(HF_EMISI_REFERENCE.sample?.kons),
    vol: normalizeNumericInputValue(HF_EMISI_REFERENCE.sample?.vol),
    fr: normalizeNumericInputValue(HF_EMISI_REFERENCE.sample?.fr),
    waktu: normalizeNumericInputValue(HF_EMISI_REFERENCE.sample?.waktu),
    titr_hf: normalizeNumericInputValue(HF_EMISI_REFERENCE.sample?.titr_hf),
    tm: normalizeNumericInputValue(HF_EMISI_REFERENCE.sample?.tm),
    p: normalizeNumericInputValue(HF_EMISI_REFERENCE.sample?.p),
    kadar: normalizeNumericInputValue(HF_EMISI_REFERENCE.sample?.kadar),
  });
  HCL_EMISI_REFERENCE.lod = Object.assign({}, HCL_EMISI_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(HCL_EMISI_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(HCL_EMISI_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(HCL_EMISI_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(HCL_EMISI_REFERENCE.lod?.waktu),
    tm: normalizeNumericInputValue(HCL_EMISI_REFERENCE.lod?.tm),
    p: normalizeNumericInputValue(HCL_EMISI_REFERENCE.lod?.p),
    kadar: normalizeNumericInputValue(HCL_EMISI_REFERENCE.lod?.kadar),
  });
  HCL_EMISI_REFERENCE.sample = Object.assign({}, HCL_EMISI_REFERENCE.sample || {}, {
    kons: normalizeNumericInputValue(HCL_EMISI_REFERENCE.sample?.kons),
    vol: normalizeNumericInputValue(HCL_EMISI_REFERENCE.sample?.vol),
    fr: normalizeNumericInputValue(HCL_EMISI_REFERENCE.sample?.fr),
    waktu: normalizeNumericInputValue(HCL_EMISI_REFERENCE.sample?.waktu),
    tm: normalizeNumericInputValue(HCL_EMISI_REFERENCE.sample?.tm),
    p: normalizeNumericInputValue(HCL_EMISI_REFERENCE.sample?.p),
    kadar: normalizeNumericInputValue(HCL_EMISI_REFERENCE.sample?.kadar),
  });
  HG_EMISI_REFERENCE.concSlope = Number(HG_EMISI_REFERENCE.concSlope ?? 97.087);
  HG_EMISI_REFERENCE.lod = Object.assign({}, HG_EMISI_REFERENCE.lod || {}, {
    kons: normalizeNumericInputValue(HG_EMISI_REFERENCE.lod?.kons),
    vol: normalizeNumericInputValue(HG_EMISI_REFERENCE.lod?.vol),
    fr: normalizeNumericInputValue(HG_EMISI_REFERENCE.lod?.fr),
    waktu: normalizeNumericInputValue(HG_EMISI_REFERENCE.lod?.waktu),
    tm: normalizeNumericInputValue(HG_EMISI_REFERENCE.lod?.tm),
    p: normalizeNumericInputValue(HG_EMISI_REFERENCE.lod?.p),
    kadar: normalizeNumericInputValue(HG_EMISI_REFERENCE.lod?.kadar),
  });
  HG_EMISI_REFERENCE.sample = Object.assign({}, HG_EMISI_REFERENCE.sample || {}, {
    kons: normalizeNumericInputValue(HG_EMISI_REFERENCE.sample?.kons),
    vol: normalizeNumericInputValue(HG_EMISI_REFERENCE.sample?.vol),
    fr: normalizeNumericInputValue(HG_EMISI_REFERENCE.sample?.fr),
    waktu: normalizeNumericInputValue(HG_EMISI_REFERENCE.sample?.waktu),
    tm: normalizeNumericInputValue(HG_EMISI_REFERENCE.sample?.tm),
    p: normalizeNumericInputValue(HG_EMISI_REFERENCE.sample?.p),
    kadar: normalizeNumericInputValue(HG_EMISI_REFERENCE.sample?.kadar),
  });

  const calculateHfEmisiResult = ({ kons, vol, fr, waktu, titrHf, tm, p }) => {
    const constants = HF_EMISI_REFERENCE.constants || {};
    const volumeDivisor = Number(constants.volumeDivisor || 25);
    const pressureFactor = Number(constants.pressureFactor || 760);
    const titrOffset = Number(constants.titrOffset || 25);
    const temperatureOffset = Number(constants.temperatureOffset || 273);
    const denominatorTemperature = Number(constants.denominatorTemperature || 298);
    const denominator = fr * waktu * denominatorTemperature * p;
    if (!denominator || !volumeDivisor) {
      return { mgm3: NaN };
    }

    const mgm3 = (kons * (vol / volumeDivisor) * pressureFactor * (titrOffset + titrHf) * (temperatureOffset + tm)) / denominator;

    return { mgm3 };
  };

  const calculateHclEmisiResult = ({ kons, vol, fr, waktu, tm, p }) => {
    const constants = HCL_EMISI_REFERENCE.constants || {};
    const correctionFactor = Number(constants.correctionFactor || 1.0282);
    const massFactor = Number(constants.massFactor || 1000);
    const temperatureOffset = Number(constants.temperatureOffset || 273);
    const pressureFactor = Number(constants.pressureFactor || 760);
    const divisorFactor = Number(constants.divisorFactor || 5);
    const denominatorTemperature = Number(constants.denominatorTemperature || 298);
    const denominator = waktu * fr * divisorFactor * denominatorTemperature * p;
    if (!denominator) {
      return { mgm3: NaN };
    }

    const mgm3 = (kons * vol * correctionFactor * massFactor * (temperatureOffset + tm) * pressureFactor) / denominator;

    return { mgm3 };
  };

  const calculateHgEmisiResult = ({ kons, vol, fr, waktu, tm, p }) => {
    const constants = HG_EMISI_REFERENCE.constants || {};
    const temperatureOffset = Number(constants.temperatureOffset || 273);
    const pressureFactor = Number(constants.pressureFactor || 760);
    const denominatorTemperature = Number(constants.denominatorTemperature || 298);
    const massDivisor = Number(constants.massDivisor || 1000);
    const denominator = fr * waktu * denominatorTemperature * massDivisor * p;
    if (!denominator) {
      return { mgm3: NaN };
    }

    const mgm3 = (kons * vol * (temperatureOffset + tm) * pressureFactor) / denominator;

    return { mgm3 };
  };

  const calculateSo2AmbienResult = ({ kons, vol, fr, waktu, sk, pm }) => {
    if ([kons, vol, fr, waktu, sk, pm].some((value) => Number.isNaN(value))) {
      return { ppm: NaN, ugm3: NaN, mgm3: NaN };
    }
    const denom = fr * waktu * 298 * pm;
    if (!denom) {
      return { ppm: NaN, ugm3: NaN, mgm3: NaN };
    }
    const ppm = (kons * (vol / 10) * (273 + sk) * Number(SO2_AMBIEN_REFERENCE.factorPpm || 0.382) * 760) / denom;
    const ugm3 = ppm * Number(SO2_AMBIEN_REFERENCE.factorUgm3 || 2617.6);
    return {
      ppm,
      ugm3,
      mgm3: ugm3 / 1000,
    };
  };

  const calculateNh3Result = ({ kons, vol, fr, waktu, sk, p }) => {
    if ([kons, vol, fr, waktu, sk, p].some((value) => Number.isNaN(value))) {
      return { ppm: NaN, ugm3: NaN };
    }
    const denom = fr * waktu * 298 * p;
    if (!denom) {
      return { ppm: NaN, ugm3: NaN };
    }
    const ppm = (kons * (vol / 10) * (273 + sk) * Number(NH3_REFERENCE.factorPpm || 1.438) * 760) / denom;
    const ugm3 = ppm * Number(NH3_REFERENCE.factorUgm3 || 695.3);
    return { ppm, ugm3 };
  };

  const calculateH2sResult = ({ kons, vol, fr, waktu, sk, p }) => {
    if ([kons, vol, fr, waktu, sk, p].some((value) => Number.isNaN(value))) {
      return { ppm: NaN, ugm3: NaN };
    }
    const denom = fr * waktu * 298 * p * Number(H2S_REFERENCE.molecularWeight || 34);
    if (!denom) {
      return { ppm: NaN, ugm3: NaN };
    }
    const ppm = (kons * vol * (273 + sk) * 760 * Number(H2S_REFERENCE.factorVolume || 24.45)) / denom;
    const ugm3 = ppm * Number(H2S_REFERENCE.factorUgm3 || 1390.6);
    return { ppm, ugm3 };
  };

  const calculateNo2Result = ({ kons, vol, fr, waktu, sk, p }) => {
    if ([kons, vol, fr, waktu, sk, p].some((value) => Number.isNaN(value))) {
      return { ppm: NaN, ugm3: NaN };
    }
    const denom = fr * waktu * 298 * p * Number(NO2_REFERENCE.molecularWeight || 46);
    if (!denom) {
      return { ppm: NaN, ugm3: NaN };
    }
    const ppm = (kons * vol * (273 + sk) * 760 * Number(NO2_REFERENCE.factorVolume || 24.45)) / denom;
    const ugm3 = ppm * Number(NO2_REFERENCE.factorUgm3 || 1881);
    return { ppm, ugm3 };
  };

  const calculateOxResult = ({ kons, vol, fr, waktu, sk, p }) => {
    if ([kons, vol, fr, waktu, sk, p].some((value) => Number.isNaN(value))) {
      return { ppm: NaN, ugm3: NaN };
    }
    const denom = fr * waktu * 298 * p;
    if (!denom) {
      return { ppm: NaN, ugm3: NaN };
    }
    const ppm = (kons * vol * (273 + sk) * 760) / denom;
    const ugm3 = ppm * Number(OX_REFERENCE.factorUgm3 || 1963.2);
    return { ppm, ugm3 };
  };

  const calculatePbMetalAasResult = ({ kons, vol, fr, waktu, sk, p }) => {
    if ([kons, vol, fr, waktu, sk, p].some((value) => Number.isNaN(value))) {
      return { ugm3: NaN, mgm3: NaN };
    }
    const constants = getCurrentMetalCurveReference().constants || {};
    const denom = fr * waktu * Number(constants.denominatorTemperature || 298) * p;
    if (!denom) {
      return { ugm3: NaN, mgm3: NaN };
    }
    const mgm3 = (kons * vol * (Number(constants.temperatureOffset || 273) + sk) * Number(constants.pressureFactor || 760)) / denom;
    return {
      ugm3: mgm3 * 1000,
      mgm3,
    };
  };

  const getAbsorbanceReference = () => {
    if (isOX()) return ABSORBANCE_REFERENCES.OX || {};
    if (isCurveMetalAas()) return getCurrentMetalCurveAbsorbanceReference();
    if (isNO2Ambien()) return ABSORBANCE_REFERENCES.NO2 || {};
    return {};
  };

  const getPbReferenceCurveModel = () => {
    const reference = getCurrentMetalCurveAbsorbanceReference();
    const refCurveY = toNum(reference.curve_y);
    const refCurveX = toNum(reference.curve_x);
    const refSlope = toNum(reference.slope);
    const curveSlope = !Number.isNaN(refCurveY) && refCurveY > 0
      ? refCurveY
      : (!Number.isNaN(refSlope) && refSlope > 0 ? (1 / refSlope) : NaN);
    const consSlope = !Number.isNaN(refCurveX) && refCurveX > 0
      ? refCurveX
      : (!Number.isNaN(curveSlope) && curveSlope > 0 ? (1 / curveSlope) : NaN);
    return {
      hasRegression: false,
      pointCount: 0,
      curveSlope,
      curveIntercept: 0,
      consSlope,
      consIntercept: 0,
      r2: NaN,
    };
  };

  const getDefaultPbCurveYValue = () => {
    const referenceModel = getPbReferenceCurveModel();
    return (!Number.isNaN(referenceModel.curveSlope) && referenceModel.curveSlope > 0)
      ? referenceModel.curveSlope
      : NaN;
  };

  const getCurveMetalXDisplayDigits = () => 4;

  const getCurrentCurveAxisDefaults = () => {
    if (isCrMetalAas()) return { xMin: 0, xMax: 10, xMajor: 2.5, xMinor: 2.5, yMin: 0, yMax: 0.3, yMajor: 0.05, yMinor: 0.01 };
    if (isAsMetalAas()) return { xMin: 0, xMax: 10, xMajor: 2, xMinor: 2, yMin: 0, yMax: 0.3, yMajor: 0.05, yMinor: 0.01 };
    if (isHgMetalAas()) return { xMin: 0, xMax: 50, xMajor: 10, xMinor: 10, yMin: 0, yMax: 0.21, yMajor: 0.03, yMinor: 0.01 };
    if (isCoMetalAas()) return { xMin: 0, xMax: 5, xMajor: 0.5, xMinor: 0.5, yMin: 0, yMax: 0.18, yMajor: 0.03, yMinor: 0.01 };
    if (isSbMetalAas()) return { xMin: 0, xMax: 15, xMajor: 5, xMinor: 5, yMin: 0, yMax: 0.06, yMajor: 0.01, yMinor: 0.01 };
    if (isTlMetalAas()) return { xMin: 0, xMax: 15, xMajor: 3, xMinor: 2, yMin: 0, yMax: 0.09, yMajor: 0.015, yMinor: 0.015 };
    if (isCuMetalAas()) return { xMin: 0, xMax: 6, xMajor: 1, xMinor: 1, yMin: 0, yMax: 0.16, yMajor: 0.02, yMinor: 0.01 };
    if (isZnMetalAas()) return { xMin: 0, xMax: 1.2, xMajor: 0.2, xMinor: 0.2, yMin: 0, yMax: 0.16, yMajor: 0.02, yMinor: 0.01 };
    if (isCdMetalAas()) return { xMin: 0, xMax: 1.5, xMajor: 0.25, xMinor: 0.25, yMin: 0, yMax: 0.18, yMajor: 0.02, yMinor: 0.01 };
    return { xMin: 0, xMax: 6, xMajor: 1, xMinor: 1, yMin: 0, yMax: 0.06, yMajor: 0.01, yMinor: 0.01 };
  };

  const normalizeCurveAxisNumber = (value) => {
    const parsed = toNum(value);
    return Number.isNaN(parsed) ? null : parsed;
  };

  const formatCurveAxisNumber = (value) => {
    const parsed = normalizeCurveAxisNumber(value);
    if (parsed === null) return '';
    return formatLocaleNumberString(parsed, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
  };

  const formatCurveAxisPlaceholderNumber = (value) => {
    const parsed = normalizeCurveAxisNumber(value);
    if (parsed === null) return '';
    return formatLocaleNumberString(parsed, { minimumFractionDigits: 0, maximumFractionDigits: 4 });
  };

  const getCurveAxisOptions = () => {
    const defaults = getCurrentCurveAxisDefaults();
    const next = {
      xMin: normalizeCurveAxisNumber(pbCurveAxisInputs.xMin?.value) ?? defaults.xMin,
      xMax: normalizeCurveAxisNumber(pbCurveAxisInputs.xMax?.value) ?? defaults.xMax,
      xMajor: normalizeCurveAxisNumber(pbCurveAxisInputs.xMajor?.value) ?? defaults.xMajor,
      xMinor: normalizeCurveAxisNumber(pbCurveAxisInputs.xMinor?.value) ?? defaults.xMinor,
      yMin: normalizeCurveAxisNumber(pbCurveAxisInputs.yMin?.value) ?? defaults.yMin,
      yMax: normalizeCurveAxisNumber(pbCurveAxisInputs.yMax?.value) ?? defaults.yMax,
      yMajor: normalizeCurveAxisNumber(pbCurveAxisInputs.yMajor?.value) ?? defaults.yMajor,
      yMinor: normalizeCurveAxisNumber(pbCurveAxisInputs.yMinor?.value) ?? defaults.yMinor,
    };

    if (next.xMax <= next.xMin) next.xMax = defaults.xMax > next.xMin ? defaults.xMax : (next.xMin + 1);
    if (next.yMax <= next.yMin) next.yMax = defaults.yMax > next.yMin ? defaults.yMax : (next.yMin + 0.1);
    if (next.xMajor <= 0) next.xMajor = defaults.xMajor;
    if (next.xMinor <= 0) next.xMinor = defaults.xMinor;
    if (next.yMajor <= 0) next.yMajor = defaults.yMajor;
    if (next.yMinor <= 0) next.yMinor = defaults.yMinor;

    return next;
  };

  const getCurveAxisInputStatePayload = () => {
    const map = {
      xMin: 'xMin',
      xMax: 'xMax',
      xMajor: 'xMajor',
      xMinor: 'xMinor',
      yMin: 'yMin',
      yMax: 'yMax',
      yMajor: 'yMajor',
      yMinor: 'yMinor',
    };

    return Object.entries(map).reduce((carry, [inputKey, stateKey]) => {
      carry[stateKey] = getInputPayloadValue(pbCurveAxisInputs[inputKey]);
      return carry;
    }, {});
  };

  const isCurveAxisStateDefaultLike = (saved = null) => {
    if (!saved || typeof saved !== 'object') return false;
    const defaults = getCurrentCurveAxisDefaults();
    const keys = ['xMin', 'xMax', 'xMajor', 'xMinor', 'yMin', 'yMax', 'yMajor', 'yMinor'];
    return keys.every((key) => {
      const savedValue = normalizeCurveAxisNumber(saved[key]);
      const defaultValue = normalizeCurveAxisNumber(defaults[key]);
      return savedValue !== null && defaultValue !== null && Math.abs(savedValue - defaultValue) < 0.000001;
    });
  };

  const applyCurveAxisOptionsToInputs = (saved = null) => {
    const defaults = getCurrentCurveAxisDefaults();
    const source = isCurveAxisStateDefaultLike(saved) ? {} : (saved || {});
    const map = {
      xMin: 'xMin',
      xMax: 'xMax',
      xMajor: 'xMajor',
      xMinor: 'xMinor',
      yMin: 'yMin',
      yMax: 'yMax',
      yMajor: 'yMajor',
      yMinor: 'yMinor',
    };

    Object.entries(map).forEach(([inputKey, stateKey]) => {
      const input = pbCurveAxisInputs[inputKey];
      if (!input) return;
      input.placeholder = formatCurveAxisPlaceholderNumber(defaults[stateKey]);
      const rawValue = source[stateKey];
      input.value = String(rawValue ?? '').trim() === ''
        ? ''
        : formatCurveAxisNumber(rawValue);
    });
  };

  const getCurveAxisStatePayload = () => {
    const options = getCurveAxisOptions();
    return {
      xMin: options.xMin,
      xMax: options.xMax,
      xMajor: options.xMajor,
      xMinor: options.xMinor,
      yMin: options.yMin,
      yMax: options.yMax,
      yMajor: options.yMajor,
      yMinor: options.yMinor,
    };
  };

  const buildCurveAxisTickValues = (min, max, majorStep) => {
    if (![min, max, majorStep].every((value) => Number.isFinite(value))) return null;
    if (majorStep <= 0 || max <= min) return null;
    const decimals = Math.max(
      0,
      ((String(majorStep).split('.')[1] || '').length),
      ((String(min).split('.')[1] || '').length),
      ((String(max).split('.')[1] || '').length)
    );
    const ticks = [];
    const maxSteps = Math.min(200, Math.ceil((max - min) / majorStep) + 2);
    for (let index = 0; index < maxSteps; index += 1) {
      const rawValue = min + (majorStep * index);
      if (rawValue > (max + (majorStep / 1000))) break;
      ticks.push(Number(rawValue.toFixed(decimals + 2)));
    }
    if (!ticks.length || Math.abs(ticks[ticks.length - 1] - max) > 0.000001) {
      ticks.push(Number(max.toFixed(decimals + 2)));
    }
    return ticks.filter((value, index, arr) => index === 0 || Math.abs(value - arr[index - 1]) > 0.000001);
  };

  const mergeCurveTickValues = (...tickGroups) => {
    const merged = [];
    tickGroups.flat().forEach((value) => {
      const parsed = Number(value);
      if (!Number.isFinite(parsed)) return;
      if (merged.some((item) => Math.abs(item - parsed) <= 0.000001)) return;
      merged.push(parsed);
    });
    return merged.sort((left, right) => left - right);
  };

  const formatCurveFixedNumber = (value, digits = 4) => {
    const parsed = Number(value);
    if (!Number.isFinite(parsed)) return '';
    return parsed.toFixed(digits).replace('.', ',');
  };

  const filterCurveTickLabelsBySpacing = (ticks = [], toPixel, minSpacing = 46) => {
    const list = Array.isArray(ticks) ? ticks : [];
    if (list.length <= 2) return list;
    const firstValue = list[0];
    const lastValue = list[list.length - 1];
    const kept = [firstValue];
    let lastPx = toPixel(firstValue);

    for (let index = 1; index < list.length - 1; index += 1) {
      const value = list[index];
      const px = toPixel(value);
      if ((px - lastPx) >= minSpacing) {
        kept.push(value);
        lastPx = px;
      }
    }

    if (!kept.some((value) => Math.abs(value - lastValue) <= 0.000001)) {
      kept.push(lastValue);
    }

    return kept;
  };

  const pickPreferredCurveTickLabels = (primaryTicks = [], fallbackTicks = [], toPixel, minSpacing = 46) => {
    const primary = filterCurveTickLabelsBySpacing(primaryTicks, toPixel, minSpacing);
    const picked = [...primary];

    (Array.isArray(fallbackTicks) ? fallbackTicks : []).forEach((value) => {
      const parsed = Number(value);
      if (!Number.isFinite(parsed)) return;
      if (picked.some((item) => Math.abs(item - parsed) <= 0.000001)) return;
      const px = toPixel(parsed);
      const overlaps = picked.some((item) => Math.abs(toPixel(item) - px) < minSpacing);
      if (!overlaps) {
        picked.push(parsed);
      }
    });

    return picked.sort((left, right) => left - right);
  };

  const formatCurveAxisTickLabel = (value, maximumFractionDigits = 4) => formatLocaleNumberString(value, {
    minimumFractionDigits: maximumFractionDigits,
    maximumFractionDigits,
  });

  const syncPbCurveLatestReferenceLabel = () => {
    if (!pbCurveLatest) return;
    const referenceModel = getPbReferenceCurveModel();
    const xDigits = getCurveMetalXDisplayDigits();
    const yLabel = (!Number.isNaN(referenceModel.curveSlope) && referenceModel.curveSlope > 0)
      ? formatLocaleNumberString(referenceModel.curveSlope, { minimumFractionDigits: 4, maximumFractionDigits: 4 })
      : '0,0000';
    const xLabel = (!Number.isNaN(referenceModel.consSlope) && referenceModel.consSlope > 0)
      ? formatLocaleNumberString(referenceModel.consSlope, { minimumFractionDigits: xDigits, maximumFractionDigits: xDigits })
      : '0,0000';
    pbCurveLatest.textContent = `Acuan aktif terbaru: Y = ${yLabel} | X = ${xLabel}`;
  };

  const getPbCurveYValue = () => {
    const manualY = toNum(pbCurveYInput?.value);
    if (!Number.isNaN(manualY) && manualY > 0) {
      return manualY;
    }
    return getDefaultPbCurveYValue();
  };

  const resolveSavedPbCurveYValue = (savedValue = null, savedReferenceValue = null) => {
    const savedCurveY = toNum(savedValue);
    if (Number.isNaN(savedCurveY) || savedCurveY <= 0) {
      return NaN;
    }

    const currentReferenceY = getDefaultPbCurveYValue();
    const savedReferenceY = toNum(savedReferenceValue);

    if (Number.isNaN(savedReferenceY) || savedReferenceY <= 0) {
      return currentReferenceY;
    }

    if (Math.abs(savedCurveY - savedReferenceY) < 0.0000005) {
      return currentReferenceY;
    }

    return savedCurveY;
  };

  const applySavedPbCurveState = (saved = null) => {
    const curveState = saved || getCurveCalibrationState() || {};
    const savedRows = Array.isArray(curveState.pbCalibrationRows) && curveState.pbCalibrationRows.length
      ? curveState.pbCalibrationRows
      : getDefaultPbCalibrationRows();
    const restoredCurveY = resolveSavedPbCurveYValue(curveState.pbCurveY, curveState.pbCurveReferenceY);
    renderPbCalibrationPanel(savedRows);
    applyCurveAxisOptionsToInputs(curveState.pbAxisOptions || null);
    restorePbCurveEquationPosition(curveState.pbCurveEquationPosition || null);
    if (pbCurveYInput) {
      pbCurveYInput.value = !Number.isNaN(restoredCurveY) && restoredCurveY > 0
        ? formatLocaleNumberString(restoredCurveY, { minimumFractionDigits: 4, maximumFractionDigits: 4 })
        : '';
    }
    ensurePbCurveYValue(restoredCurveY);
    updatePbCurvePanel();
  };

  const refreshCurveMetalReferenceInModal = async () => {
    if (!(isCurveMetalAas() && (currentType === 'hasil-baca' || currentType === CURVE_CALIBRATION_TYPE))) return;

    const parameterKey = getCurveMetalParameterKey();
    try {
      await fetchLatestAbsorbanceReference(parameterKey);
      applySavedPbCurveState(getCurveCalibrationState());
    } catch (error) {
      console.error(error);
    }
  };

  const syncPbCurveFormulaFields = ({ formatY = false } = {}) => {
    const curveY = getPbCurveYValue();
    const curveX = (!Number.isNaN(curveY) && curveY > 0) ? (1 / curveY) : NaN;
    const xDigits = getCurveMetalXDisplayDigits();
    syncPbCurveLatestReferenceLabel();
    if (pbCurveYInput && formatY && !Number.isNaN(curveY) && curveY > 0) {
      pbCurveYInput.value = formatLocaleNumberString(curveY, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
    }
    if (pbCurveXInput) {
      pbCurveXInput.value = Number.isNaN(curveX)
        ? ''
        : formatLocaleNumberString(curveX, { minimumFractionDigits: xDigits, maximumFractionDigits: xDigits });
    }
    if (consAInput) {
      consAInput.value = Number.isNaN(curveX)
        ? ''
        : formatLocaleNumberString(curveX, { minimumFractionDigits: xDigits, maximumFractionDigits: xDigits });
    }
    if (consBInput) {
      consBInput.value = '0';
    }
  };

  const ensurePbCurveYValue = (preferredValue = null) => {
    if (!pbCurveYInput) return;
    const currentValue = String(pbCurveYInput.value || '').trim();
    if (currentValue) {
      syncPbCurveFormulaFields();
      return;
    }
    const preferred = toNum(preferredValue);
    const fallback = (!Number.isNaN(preferred) && preferred > 0)
      ? preferred
      : getDefaultPbCurveYValue();
    if (!Number.isNaN(fallback) && fallback > 0) {
      pbCurveYInput.value = formatLocaleNumberString(fallback, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
    }
    syncPbCurveFormulaFields();
  };

  const syncPbCurveOverlayCardPosition = () => {
    if (!pbCurvePlot || !pbCurveOverlayCard) return;
    const plotRect = pbCurvePlot.getBoundingClientRect();
    const cardWidth = pbCurveOverlayCard.offsetWidth || 194;
    const cardHeight = pbCurveOverlayCard.offsetHeight || 64;
    const maxX = Math.max(12, plotRect.width - cardWidth - 12);
    const maxY = Math.max(12, plotRect.height - cardHeight - 12);
    const nextX = pbCurveEquationPosition?.x ?? maxX;
    const nextY = pbCurveEquationPosition?.y ?? 18;
    const clampedX = Math.max(12, Math.min(nextX, maxX));
    const clampedY = Math.max(12, Math.min(nextY, maxY));
    pbCurveOverlayCard.style.left = `${clampedX}px`;
    pbCurveOverlayCard.style.top = `${clampedY}px`;
    pbCurveOverlayCard.style.right = 'auto';
    pbCurveOverlayCard.style.bottom = 'auto';
    pbCurveEquationPosition = { x: clampedX, y: clampedY };
  };

  const getPbCurveEquationPositionState = () => {
    const x = Number(pbCurveEquationPosition?.x);
    const y = Number(pbCurveEquationPosition?.y);
    if (!Number.isFinite(x) || !Number.isFinite(y)) {
      return null;
    }
    return { x, y };
  };

  const restorePbCurveEquationPosition = (saved = null) => {
    const x = Number(saved?.x);
    const y = Number(saved?.y);
    pbCurveEquationPosition = (Number.isFinite(x) && Number.isFinite(y))
      ? { x, y }
      : null;
  };

  const getPbCurveRegressionModel = (rows = null) => {
    const sourceRows = Array.isArray(rows) ? rows : getPbCalibrationRowsPayload();
    const points = sourceRows
      .map((row) => ({
        x: toNum(row?.kandungan),
        y: toNum(row?.absorbansi),
      }))
      .filter((row) => !Number.isNaN(row.x) && !Number.isNaN(row.y));
    const fallback = getPbReferenceCurveModel();
    const manualCurveY = getPbCurveYValue();
    const curveSlope = (!Number.isNaN(manualCurveY) && manualCurveY > 0)
      ? manualCurveY
      : fallback.curveSlope;
    const consSlope = (!Number.isNaN(curveSlope) && curveSlope > 0)
      ? (1 / curveSlope)
      : fallback.consSlope;
    let r2 = NaN;
    if (points.length >= 2 && !Number.isNaN(curveSlope) && curveSlope > 0) {
      const ssTot = points.reduce((total, point) => total + (point.y ** 2), 0);
      const ssRes = points.reduce((total, point) => {
        const fitted = curveSlope * point.x;
        return total + ((point.y - fitted) ** 2);
      }, 0);
      r2 = ssTot === 0 ? 1 : (1 - (ssRes / ssTot));
    }

    return {
      hasRegression: points.length >= 2 && !Number.isNaN(curveSlope) && curveSlope > 0,
      pointCount: points.length,
      curveSlope,
      curveIntercept: 0,
      consSlope,
      consIntercept: 0,
      r2,
      points,
    };
  };

  const getPbConcentrationFormula = () => {
    const regression = getPbCurveRegressionModel();
    return {
      consSlope: regression.consSlope,
      consIntercept: 0,
    };
  };

  const getDefaultPbCalibrationRows = () => {
    return getCurrentMetalStandardDefaults().map((rawValue, index) => {
      const value = toNum(rawValue);
      return {
        pb_role: 'standard',
        standard_no: String(index + 1),
        volume: '',
        waktu_baca: '',
        kandungan: Number.isNaN(value) ? '' : value.toFixed(4),
        absorbansi: '',
        note: '',
      };
    });
  };

  const extractPbCalibrationRows = (rows = []) => {
    const standards = [];
    const samples = [];
    (Array.isArray(rows) ? rows : []).forEach((row) => {
      if (row && typeof row === 'object' && String(row.pb_role || '') === 'standard') {
        standards.push({
          pb_role: 'standard',
          standard_no: String(row.standard_no || ''),
          volume: row.volume ?? '',
          waktu_baca: row.waktu_baca ?? '',
          kandungan: row.kandungan ?? '',
          absorbansi: row.absorbansi ?? '',
          note: row.note ?? '',
        });
        return;
      }
      samples.push(row);
    });

    return {
      standards: standards.length ? standards : getDefaultPbCalibrationRows(),
      samples,
    };
  };

  const renderPbCalibrationPanel = (rows = null) => {
    if (!pbCurvePanel || !pbCurveRows) return;
    syncPbCurvePanelMeta();
    const dataRows = Array.isArray(rows) && rows.length ? rows : getDefaultPbCalibrationRows();
    pbCurveRows.innerHTML = `
      <tr class="pb-curve-section">
        <td>Standar</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      ${dataRows.map((row, idx) => `
      <tr data-pb-standard-row>
        <td class="text-center">
          <input type="text" class="form-control form-control-sm text-center" value="${row.standard_no || String(idx + 1)}" data-pb-curve="standard_no" readonly tabindex="-1">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-center" value="${row.volume || ''}" data-pb-curve="volume" inputmode="decimal">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-center" value="${row.waktu_baca || ''}" data-pb-curve="waktu_baca" inputmode="numeric" placeholder="HH:MM">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-center" value="${row.absorbansi || ''}" data-pb-curve="absorbansi" inputmode="decimal">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-center" value="${row.kandungan || ''}" data-pb-curve="kandungan" inputmode="decimal">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm" value="${row.note || ''}" data-pb-curve="note" placeholder="Keterangan">
        </td>
      </tr>
    `).join('')}`;
    prepareLocaleNumericInputs(pbCurvePanel);
    ensurePbCurveYValue();
    updatePbCurvePanel();
  };

  const getPbCalibrationRowsPayload = () => {
    if (!pbCurveRows) return [];
    return Array.from(pbCurveRows.querySelectorAll('[data-pb-standard-row]')).map((row) => ({
      pb_role: 'standard',
      standard_no: row.querySelector('[data-pb-curve="standard_no"]')?.value || '',
      volume: getInputPayloadValue(row.querySelector('[data-pb-curve="volume"]')),
      waktu_baca: row.querySelector('[data-pb-curve="waktu_baca"]')?.value || '',
      kandungan: getInputPayloadValue(row.querySelector('[data-pb-curve="kandungan"]')),
      absorbansi: getInputPayloadValue(row.querySelector('[data-pb-curve="absorbansi"]')),
      note: row.querySelector('[data-pb-curve="note"]')?.value || '',
    }));
  };

  const buildPbCurveSvg = (rows = []) => {
    if (!pbCurveSvg) return;
    const model = getPbCurveRegressionModel(rows);
    const axisOptions = getCurveAxisOptions();
    const points = rows
      .map((row) => ({
        x: toNum(row.kandungan),
        y: toNum(row.absorbansi),
        label: String(row.standard_no || ''),
      }))
      .filter((row) => !Number.isNaN(row.x) && !Number.isNaN(row.y));
    const xValues = points.map((item) => item.x);
    const yValues = points.map((item) => item.y);
    const isPbCurve = isPbMetalAas();
    const isCuCurve = isCuMetalAas();
    const isAsCurve = isAsMetalAas();
    const isHgCurve = isHgMetalAas();
    const isCoCurve = isCoMetalAas();
    const isSbCurve = isSbMetalAas();
    const isTlCurve = isTlMetalAas();
    const isCrCurve = isCrMetalAas();
    const isZnCurve = isZnMetalAas();
    const isCdCurve = isCdMetalAas();
    const isCompactCurve = isZnCurve || isCdCurve || isCuCurve || isCrCurve || isAsCurve || isHgCurve || isCoCurve || isSbCurve || isTlCurve;
    const minX = axisOptions.xMin;
    const maxX = axisOptions.xMax;
    const minY = axisOptions.yMin;
    const maxY = axisOptions.yMax;
    const domainX = Math.max(maxX - minX, 0.01);
    const width = 560;
    const height = 340;
    const left = 72;
    const top = 18;
    const right = 20;
    const bottom = 58;
    const plotWidth = width - left - right;
    const plotHeight = height - top - bottom;
    const lineStartY = !Number.isNaN(model.curveSlope) && !Number.isNaN(model.curveIntercept)
      ? model.curveIntercept
      : NaN;
    const lineEndX = isCompactCurve
      ? Math.min(Math.max(...xValues, minX), maxX)
      : maxX;
    const lineEndY = !Number.isNaN(model.curveSlope) && !Number.isNaN(model.curveIntercept)
      ? ((model.curveSlope * lineEndX) + model.curveIntercept)
      : NaN;
    const domainY = Math.max(maxY - minY, 0.01);
    const toPxX = (value) => left + (((value - minX) / domainX) * plotWidth);
    const toPxY = (value) => top + plotHeight - (((value - minY) / domainY) * plotHeight);
    const gridLines = [];
    const yTickValues = buildCurveAxisTickValues(minY, maxY, axisOptions.yMajor);
    const yTickCount = yTickValues ? (yTickValues.length - 1) : 5;
    for (let i = 0; i <= yTickCount; i += 1) {
      const y = top + ((plotHeight / yTickCount) * i);
      const value = yTickValues ? yTickValues[yTickCount - i] : (maxY - ((domainY / yTickCount) * i));
      gridLines.push(`
        <line x1="${left}" y1="${y}" x2="${width - right}" y2="${y}" stroke="#dce3ea" stroke-width="1" />
        <text x="${left - 8}" y="${y + 4}" text-anchor="end" font-size="11" fill="#6b7b8f">${formatCurveFixedNumber(value, 4)}</text>
      `);
    }
    const xAxisTickValues = buildCurveAxisTickValues(minX, maxX, axisOptions.xMajor);
    const xPointTickValues = mergeCurveTickValues(points.map((point) => point.x));
    const xTickValues = isCompactCurve
      ? mergeCurveTickValues(xAxisTickValues, xPointTickValues)
      : xAxisTickValues;
    const xTickCount = xTickValues ? (xTickValues.length - 1) : 5;
    const xTickDigits = getCurveMetalXDisplayDigits();
    const xVisibleTickLabels = isCompactCurve
      ? pickPreferredCurveTickLabels(xPointTickValues, xAxisTickValues, toPxX, 42)
      : filterCurveTickLabelsBySpacing(xTickValues || [], toPxX, 54);
    const xTicks = [];
    for (let i = 0; i <= xTickCount; i += 1) {
      const value = xTickValues ? xTickValues[i] : (minX + ((domainX / xTickCount) * i));
      const x = toPxX(value);
      const showLabel = !xTickValues || xVisibleTickLabels.some((tickValue) => Math.abs(tickValue - value) <= 0.000001);
      xTicks.push(`
        <line x1="${x}" y1="${top + plotHeight}" x2="${x}" y2="${top + plotHeight + 4}" stroke="#8c99a8" stroke-width="1" />
        ${showLabel ? `<text x="${x}" y="${height - 28}" text-anchor="middle" font-size="11" fill="#6b7b8f">${formatCurveFixedNumber(value, xTickDigits)}</text>` : ''}
      `);
    }
    const lineStartX = minX;
    const lineEndLimit = isPbCurve ? Math.max(...xValues, minX) : maxX;
    const safeLineEndX = Math.min(Math.max(lineEndLimit, minX), maxX);
    const linePath = !Number.isNaN(model.curveSlope) && !Number.isNaN(model.curveIntercept)
      ? `M ${toPxX(lineStartX)} ${toPxY(lineStartY)} L ${toPxX(isPbCurve ? safeLineEndX : lineEndX)} ${toPxY(isPbCurve ? ((model.curveSlope * safeLineEndX) + model.curveIntercept) : lineEndY)}`
      : '';
    const pointSvg = points.map((point, index) => `
      <g class="pb-curve-point-hit" data-pb-point="1" data-label="${point.label}" data-x="${point.x.toFixed(4)}" data-y="${point.y.toFixed(4)}" data-px="${toPxX(point.x).toFixed(2)}" data-py="${toPxY(point.y).toFixed(2)}">
        <circle class="pb-curve-point" cx="${toPxX(point.x)}" cy="${toPxY(point.y)}" r="4" fill="#1d4ed8" style="animation-delay:${0.55 + (index * 0.14)}s" />
        <text class="pb-curve-point-label" x="${toPxX(point.x) + 7}" y="${toPxY(point.y) - 6}" font-size="13" fill="#334155" style="animation-delay:${0.7 + (index * 0.14)}s">${point.label}</text>
      </g>
    `).join('');

    pbCurveSvg.innerHTML = `
      <rect x="0" y="0" width="${width}" height="${height}" fill="#ffffff" rx="10" />
      ${gridLines.join('')}
      <line x1="${left}" y1="${top}" x2="${left}" y2="${top + plotHeight}" stroke="#475569" stroke-width="1.5" />
      <line x1="${left}" y1="${top + plotHeight}" x2="${width - right}" y2="${top + plotHeight}" stroke="#475569" stroke-width="1.5" />
      ${xTicks.join('')}
      ${linePath ? `<path class="pb-curve-line" d="${linePath}" fill="none" stroke="#111827" stroke-width="2.5" />` : ''}
      ${pointSvg}
      <g class="pb-curve-tooltip d-none" data-pb-curve-tooltip>
        <rect x="0" y="0" width="118" height="52" rx="8" fill="#ffffff" stroke="#cbd5e1" />
        <text x="10" y="18" font-size="11" fill="#334155" data-pb-curve-tooltip-label></text>
        <text x="10" y="32" font-size="11" fill="#334155" data-pb-curve-tooltip-x></text>
        <text x="10" y="46" font-size="11" fill="#334155" data-pb-curve-tooltip-y></text>
      </g>
      <text x="${width / 2}" y="${height - 8}" text-anchor="middle" font-size="12" fill="#1f2937">Konsentrasi (ppm)</text>
      <text x="18" y="${height / 2}" text-anchor="middle" font-size="12" fill="#1f2937" transform="rotate(-90 18 ${height / 2})">Absorbansi</text>
    `;
  };

  const updatePbCurvePanel = () => {
    if (!pbCurvePanel || !pbCurveRows) return;
    ensurePbCurveYValue();
    const rows = getPbCalibrationRowsPayload();
    const model = getPbCurveRegressionModel(rows);
    if (pbCurveFormula) {
      const xDigits = getCurveMetalXDisplayDigits();
      const yLabel = Number.isNaN(model.curveSlope) ? '0' : formatLocaleNumberString(model.curveSlope, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
      const xLabel = Number.isNaN(model.consSlope) ? '0' : formatLocaleNumberString(model.consSlope, { minimumFractionDigits: xDigits, maximumFractionDigits: xDigits });
      const r2Label = Number.isNaN(model.r2) ? '-' : formatLocaleNumberString(model.r2, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
      const pointInfo = model.pointCount > 1
        ? `${model.pointCount} titik standar`
        : 'Isi minimal 2 titik standar';
      if (pbCurveYInput && document.activeElement !== pbCurveYInput) {
        pbCurveYInput.value = yLabel;
      }
      if (pbCurveXInput) {
        pbCurveXInput.value = xLabel;
      }
      if (pbCurveR2) {
        pbCurveR2.textContent = `R² = ${r2Label} | ${pointInfo}`;
      }
      if (pbCurveOverlayY) pbCurveOverlayY.textContent = `y = ${yLabel}`;
      if (pbCurveOverlayX) pbCurveOverlayX.textContent = `x = ${xLabel}`;
      if (pbCurveOverlayR2) pbCurveOverlayR2.textContent = `R² = ${r2Label}`;
    }
    syncPbCurveFormulaFields();
    buildPbCurveSvg(rows);
    syncPbCurveOverlayCardPosition();
  };

  const showPbCurvePanel = (show) => {
    if (!pbCurvePanel) return;
    if (show) syncPbCurvePanelMeta();
    pbCurvePanel.classList.toggle('d-none', !show);
  };

  const shouldUseCurveAxisToggle = () => isCurveMetalAas() && isCurveCalibrationType();

  const syncPbCurveAxisToggleUi = ({ forceExpanded = null } = {}) => {
    if (typeof forceExpanded === 'boolean') {
      pbCurveAxisExpanded = forceExpanded;
    }
    const useToggle = shouldUseCurveAxisToggle();
    const expanded = useToggle ? pbCurveAxisExpanded : true;

    if (pbCurveAxisToggleRow) {
      pbCurveAxisToggleRow.classList.toggle('d-none', !useToggle);
      if (useToggle) {
        pbCurveAxisToggleRow.classList.add('d-flex');
      } else {
        pbCurveAxisToggleRow.classList.remove('d-flex');
      }
    }
    if (pbCurveAxisOptions) {
      pbCurveAxisOptions.classList.toggle('d-none', !expanded);
    }
    if (pbCurveAxisToggleBtn) {
      pbCurveAxisToggleBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }
    if (pbCurveAxisToggleChevron) {
      pbCurveAxisToggleChevron.classList.toggle('bi-chevron-down', !expanded);
      pbCurveAxisToggleChevron.classList.toggle('bi-chevron-up', expanded);
    }
  };

  const applySo2AmbienCalcDefaults = (root) => {
    if (!isSO2Ambien() || !root) return;
    const sampleRef = SO2_AMBIEN_REFERENCE.sample || {};
    root.querySelectorAll('tr[data-row-type="no2-calc"]').forEach((row) => {
      const vol = row.querySelector('[data-no2="vol"]');
      const waktu = row.querySelector('[data-no2="waktu"]');
      const fr = row.querySelector('[data-no2="fr"]');

      if (vol && !String(vol.value || '').trim()) vol.value = sampleRef.vol || SO2_AMBIEN_REFERENCE.vol;
      if (waktu && !String(waktu.value || '').trim()) waktu.value = sampleRef.waktu || SO2_AMBIEN_REFERENCE.waktu;
      if (fr && !String(fr.value || '').trim()) fr.value = sampleRef.fr || SO2_AMBIEN_REFERENCE.fr;
    });
  };

  const recalculateSo2AmbienRows = (root) => {
    if (!isSO2Ambien() || !root) return;
    root.querySelectorAll('tr').forEach((row) => {
      const rowType = row.getAttribute('data-row-type');
      if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
      const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
      const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
      const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
      const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
      const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
      const pm = toNum(row.querySelector('[data-skpm="pm"]')?.value);
      const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
      const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
      const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');

      if ([kons, vol, fr, waktu, sk, pm].some((value) => Number.isNaN(value))) {
        if (ppmInput) ppmInput.value = '';
        if (ugm3Input) ugm3Input.value = '';
        if (mgm3Input) mgm3Input.value = '';
        return;
      }

      const result = calculateSo2AmbienResult({ kons, vol, fr, waktu, sk, pm });
      if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(4);
      if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
      if (mgm3Input) mgm3Input.value = Number.isNaN(result.mgm3) ? '' : result.mgm3.toFixed(4);
    });
  };

  const recalculateCurrentCalcRoot = (root) => {
    if (!root) return;
    ['so2-mdl', 'no2-mdl', 'debu-mdl', 'hc-lod'].forEach((rowType) => {
      root.querySelectorAll(`[data-row-type="${rowType}"]`).forEach((row) => {
        if (rowType === 'debu-mdl') {
          prepareDebuMdlRow(row);
          return;
        }
        lockCalcReferenceRow(row);
      });
    });

    if (isEmisiAcidGasLike()) {
      calcSo2Kadar(root);
      return;
    }

    if (isSO2Ambien()) {
      recalculateSo2AmbienRows(root);
      return;
    }

    if (isCurveMetalAas()) {
      recalculatePbMetalAasRows(root);
      return;
    }

    if (isNO2Ambien()) {
      root.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) {
          if (ppmInput) ppmInput.value = '';
          if (ugm3Input) ugm3Input.value = '';
          if (mgm3Input) mgm3Input.value = '';
          return;
        }
        const result = calculateNo2Result({ kons, vol, fr, waktu, sk, p });
        if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(4);
        if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
        if (mgm3Input) mgm3Input.value = Number.isNaN(result.ugm3) ? '' : (result.ugm3 / 1000).toFixed(4);
      });
      return;
    }

    if (isOX()) {
      root.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) {
          if (ppmInput) ppmInput.value = '';
          if (ugm3Input) ugm3Input.value = '';
          if (mgm3Input) mgm3Input.value = '';
          return;
        }
        const result = calculateOxResult({ kons, vol, fr, waktu, sk, p });
        if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(6);
        if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
        if (mgm3Input) mgm3Input.value = Number.isNaN(result.ugm3) ? '' : (result.ugm3 / 1000).toFixed(4);
      });
      return;
    }

    if (isNH3()) {
      root.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) {
          if (ppmInput) ppmInput.value = '';
          if (ugm3Input) ugm3Input.value = '';
          return;
        }
        const result = calculateNh3Result({ kons, vol, fr, waktu, sk, p });
        if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(4);
        if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
      });
      return;
    }

    if (isH2S()) {
      root.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) {
          if (ppmInput) ppmInput.value = '';
          if (ugm3Input) ugm3Input.value = '';
          return;
        }
        const result = calculateH2sResult({ kons, vol, fr, waktu, sk, p });
        if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(6);
        if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
      });
      return;
    }

    if (isHcLike()) {
      applyHcLodDefaults(root);
      root.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'hc-calc' && rowType !== 'hc-lod') return;
        calcHcKadarRow(row);
      });
      return;
    }

    if (isDebuPm()) {
      root.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'debu-calc' && rowType !== 'debu-mdl') return;
        calcDebuKadarRow(row);
      });
    }
  };

  const buildCalcRowNO2Mdl = () => {
    const isOxMdl = isOX();
    const isNh3Mdl = isNH3();
    const isH2SMdl = isH2S();
    const isNo2AmbienMdl = isNO2Ambien();
    const isSo2AmbienMdl = isSO2Ambien();
    const nh3Ref = isLKCategory() ? NH3_REFERENCE.mdlLk : NH3_REFERENCE.mdlAmbien;
    const h2sRef = isLKCategory() ? H2S_REFERENCE.lodLk : H2S_REFERENCE.lodAmbien;
    const oxRef = isLKCategory() ? OX_REFERENCE.lodLk : OX_REFERENCE.lodAmbien;
    const no2Ref = isLKCategory() ? NO2_REFERENCE.lodLk : NO2_REFERENCE.lodAmbien;
    const konsVal = isOxMdl
      ? oxRef.kons
      : (isNo2AmbienMdl
        ? no2Ref.kons
        : (isSo2AmbienMdl
          ? SO2_AMBIEN_REFERENCE.kons
          : (isNh3Mdl
            ? nh3Ref.kons
            : (isH2SMdl ? h2sRef.kons : ''))));
    const volVal = isNh3Mdl
      ? nh3Ref.vol
      : (isH2SMdl ? h2sRef.vol : (isOxMdl ? oxRef.vol : (isNo2AmbienMdl ? no2Ref.vol : SO2_AMBIEN_REFERENCE.vol)));
    const waktuVal = isOxMdl
      ? oxRef.waktu
      : (isNh3Mdl
        ? nh3Ref.waktu
        : (isH2SMdl ? h2sRef.waktu : (isNo2AmbienMdl ? no2Ref.waktu : '60')));
    const frVal = isNh3Mdl
      ? nh3Ref.fr
      : (isH2SMdl
        ? h2sRef.fr
        : (isOxMdl ? oxRef.fr : (isSo2AmbienMdl ? SO2_AMBIEN_REFERENCE.fr : (isNo2AmbienMdl ? no2Ref.fr : '0.400'))));
    const pmVal = isNh3Mdl
      ? nh3Ref.p
      : (isH2SMdl ? h2sRef.p : (isOxMdl ? oxRef.p : (isNo2AmbienMdl ? no2Ref.p : SO2_AMBIEN_REFERENCE.pm)));
    const skVal = isNh3Mdl
      ? nh3Ref.sk
      : (isH2SMdl ? h2sRef.sk : (isOxMdl ? oxRef.sk : (isNo2AmbienMdl ? no2Ref.sk : SO2_AMBIEN_REFERENCE.sk)));
    const ppmVal = isOxMdl
      ? (oxRef.ppm || '')
      : (isNo2AmbienMdl
        ? (no2Ref.ppm || '')
        : (isSo2AmbienMdl
        ? ''
        : (isNh3Mdl
          ? (nh3Ref.ppm || '')
          : (isH2SMdl ? (h2sRef.ppm || '') : '0.0006'))));
    const ugm3Val = isNh3Mdl
      ? (nh3Ref.ugm3 || '')
      : (isH2SMdl ? (h2sRef.ugm3 || '') : (isOxMdl ? (oxRef.ugm3 || '') : (isNo2AmbienMdl ? (no2Ref.ugm3 || '') : '')));
    const ugm3Num = Number(String(ugm3Val || '').replace(',', '.'));
    const mgm3Val = Number.isFinite(ugm3Num) ? (ugm3Num / 1000).toFixed(4) : '';
    return `
      <tr data-row-type="no2-mdl">
        <td class="text-center">-</td>
        <td><input type="text" class="form-control form-control-sm" value="LOD" readonly></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${konsVal}" data-no2="kons" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${volVal}" data-no2="vol" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${waktuVal}" data-no2="waktu" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${frVal}" data-no2="fr" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${skVal}" data-skpm="sk" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${pmVal}" data-skpm="pm" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${ugm3Val}" data-no2="kadar_ugm3" readonly tabindex="-1"></td>
        <td><input type="number" class="form-control form-control-sm" step="0.0001" inputmode="decimal" value="${mgm3Val}" data-no2="kadar_mgm3" readonly tabindex="-1"></td>
      </tr>
    `;
  };

  const applyNo2AmbienMdlDefaults = (root) => {
    if (!isNO2Ambien() || !root) return;
    const row = root.querySelector('[data-row-type="no2-mdl"]');
    if (!row) return;
    const no2Ref = isLKCategory() ? NO2_REFERENCE.lodLk : NO2_REFERENCE.lodAmbien;
    const kons = row.querySelector('[data-no2="kons"]');
    const vol = row.querySelector('[data-no2="vol"]');
    const fr = row.querySelector('[data-no2="fr"]');
    const waktu = row.querySelector('[data-no2="waktu"]');
    const sk = row.querySelector('[data-skpm="sk"]');
    const pm = row.querySelector('[data-skpm="pm"]');
    if (kons) kons.value = no2Ref.kons || '';
    if (vol) vol.value = no2Ref.vol || '';
    if (waktu) waktu.value = no2Ref.waktu || '';
    if (fr) fr.value = no2Ref.fr || '';
    if (sk) sk.value = no2Ref.sk || '';
    if (pm) pm.value = no2Ref.p || '';
    const konsNum = toNum(kons?.value);
    const volNum = toNum(vol?.value);
    const frNum = toNum(fr?.value);
    const waktuNum = toNum(waktu?.value);
    const skNum = toNum(sk?.value);
    const pmNum = toNum(pm?.value);
    const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
    const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
    const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
    if (ppmInput) {
      if ([konsNum, volNum, frNum, waktuNum, skNum, pmNum].some((v) => Number.isNaN(v))) {
        ppmInput.value = '';
        if (ugm3Input) ugm3Input.value = '';
        if (mgm3Input) mgm3Input.value = '';
      } else {
        const result = calculateNo2Result({
          kons: konsNum,
          vol: volNum,
          fr: frNum,
          waktu: waktuNum,
          sk: skNum,
          p: pmNum,
        });
        ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(4);
        if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
        if (mgm3Input) mgm3Input.value = Number.isNaN(result.ugm3) ? '' : (result.ugm3 / 1000).toFixed(4);
      }
    }
    lockCalcReferenceRow(row);
  };

  const applySo2AmbienMdlDefaults = (root) => {
    if (!isSO2Ambien() || !root) return;
    const row = root.querySelector('[data-row-type="no2-mdl"]');
    if (!row) return;
    const kons = row.querySelector('[data-no2="kons"]');
    const vol = row.querySelector('[data-no2="vol"]');
    const fr = row.querySelector('[data-no2="fr"]');
    const waktu = row.querySelector('[data-no2="waktu"]');
    const sk = row.querySelector('[data-skpm="sk"]');
    const pm = row.querySelector('[data-skpm="pm"]');
    const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
    const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
    const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
    if (kons) kons.value = SO2_AMBIEN_REFERENCE.kons;
    if (vol) vol.value = SO2_AMBIEN_REFERENCE.vol;
    if (waktu) waktu.value = SO2_AMBIEN_REFERENCE.waktu;
    if (fr) fr.value = SO2_AMBIEN_REFERENCE.fr;
    if (sk) {
      sk.value = SO2_AMBIEN_REFERENCE.sk;
    }
    if (pm) {
      pm.value = SO2_AMBIEN_REFERENCE.pm;
    }
    const konsNum = toNum(kons?.value);
    const volNum = toNum(vol?.value);
    const frNum = toNum(fr?.value);
    const waktuNum = toNum(waktu?.value);
    const skNum = toNum(sk?.value);
    const pmNum = toNum(pm?.value);
    if ([konsNum, volNum, frNum, waktuNum, skNum, pmNum].some((v) => Number.isNaN(v))) {
      if (ppmInput) ppmInput.value = '';
      if (ugm3Input) ugm3Input.value = '';
      if (mgm3Input) mgm3Input.value = '';
      return;
    }
    const result = calculateSo2AmbienResult({
      kons: konsNum,
      vol: volNum,
      fr: frNum,
      waktu: waktuNum,
      sk: skNum,
      pm: pmNum,
    });
    const ppm = result.ppm;
    if (ppmInput) ppmInput.value = Number.isNaN(ppm) ? '' : ppm.toFixed(4);
    if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
    if (mgm3Input) mgm3Input.value = Number.isNaN(result.mgm3) ? '' : result.mgm3.toFixed(4);
    lockCalcReferenceRow(row);
  };

  const applyOxMdlDefaults = (root) => {
    if (!isOX() || !root) return;
    const row = root.querySelector('[data-row-type="no2-mdl"]');
    if (!row) return;
    const oxRef = isLKCategory() ? OX_REFERENCE.lodLk : OX_REFERENCE.lodAmbien;
    const kons = row.querySelector('[data-no2="kons"]');
    const vol = row.querySelector('[data-no2="vol"]');
    const fr = row.querySelector('[data-no2="fr"]');
    const waktu = row.querySelector('[data-no2="waktu"]');
    const sk = row.querySelector('[data-skpm="sk"]');
    const pm = row.querySelector('[data-skpm="pm"]');
    const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
    const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
    if (kons) kons.value = oxRef.kons || '0.00744';
    if (vol) vol.value = oxRef.vol || '10.0';
    if (fr) fr.value = oxRef.fr || '1.000';
    if (waktu) waktu.value = oxRef.waktu || '30';
    if (sk) sk.value = oxRef.sk || '25.0';
    if (pm) pm.value = oxRef.p || '760';
    const result = calculateOxResult({
      kons: toNum(kons?.value),
      vol: toNum(vol?.value),
      fr: toNum(fr?.value),
      waktu: toNum(waktu?.value),
      sk: toNum(sk?.value),
      p: toNum(pm?.value),
    });
    if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(6);
    if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
    lockCalcReferenceRow(row);
  };

  const calcDebuKadarRow = (row) => {
    if (!row) return;
    const berat = toNum(row.querySelector('[data-debu="berat"]')?.value);
    const fr = toNum(row.querySelector('[data-debu="fr"]')?.value);
    const waktu = toNum(row.querySelector('[data-debu="waktu"]')?.value);
    const sk = toNum(row.querySelector('[data-debu="sk"]')?.value);
    const p = toNum(row.querySelector('[data-debu="p"]')?.value);
    const out = row.querySelector('[data-debu="kadar"]');
    if (!out) return;
    if ([berat, fr, waktu, sk, p].some((v) => Number.isNaN(v))) {
      out.value = '';
      return;
    }
    const constants = DEBU_AMBIEN_REFERENCE.constants || {};
    const massFactor = Number(constants.massFactor || 1000000);
    const temperatureOffset = Number(constants.temperatureOffset || 273);
    const pressureFactor = Number(constants.pressureFactor || 760);
    const denominatorTemperature = Number(constants.denominatorTemperature || 298);
    const denom = waktu * fr * denominatorTemperature * p;
    if (!denom) {
      out.value = '';
      return;
    }
    const kadar = (berat * massFactor * (temperatureOffset + sk) * pressureFactor) / denom;
    out.value = kadar.toFixed(4);
  };

  const applyNh3MdlDefaults = (root) => {
    if (!isNH3() || !root) return;
    const row = root.querySelector('[data-row-type="no2-mdl"]');
    if (!row) return;
    const nh3Ref = isLKCategory() ? NH3_REFERENCE.mdlLk : NH3_REFERENCE.mdlAmbien;
    const kons = row.querySelector('[data-no2="kons"]');
    const vol = row.querySelector('[data-no2="vol"]');
    const fr = row.querySelector('[data-no2="fr"]');
    const waktu = row.querySelector('[data-no2="waktu"]');
    const sk = row.querySelector('[data-skpm="sk"]');
    const pm = row.querySelector('[data-skpm="pm"]');
    const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
    const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
    if (kons) kons.value = nh3Ref.kons || '';
    if (vol) vol.value = nh3Ref.vol || '10.0';
    if (fr) fr.value = nh3Ref.fr || '1.000';
    if (waktu) waktu.value = nh3Ref.waktu || '60';
    if (sk) sk.value = nh3Ref.sk || '25.0';
    if (pm) pm.value = nh3Ref.p || '760';
    const result = calculateNh3Result({
      kons: toNum(kons?.value),
      vol: toNum(vol?.value),
      fr: toNum(fr?.value),
      waktu: toNum(waktu?.value),
      sk: toNum(sk?.value),
      p: toNum(pm?.value),
    });
    if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(6);
    if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
    lockCalcReferenceRow(row);
  };

  const applyH2SMdlDefaults = (root) => {
    if (!isH2S() || !root) return;
    const row = root.querySelector('[data-row-type="no2-mdl"]');
    if (!row) return;
    const h2sRef = isLKCategory() ? H2S_REFERENCE.lodLk : H2S_REFERENCE.lodAmbien;
    const kons = row.querySelector('[data-no2="kons"]');
    const vol = row.querySelector('[data-no2="vol"]');
    const fr = row.querySelector('[data-no2="fr"]');
    const waktu = row.querySelector('[data-no2="waktu"]');
    const sk = row.querySelector('[data-skpm="sk"]');
    const pm = row.querySelector('[data-skpm="pm"]');
    const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
    const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
    if (kons) kons.value = h2sRef.kons || '';
    if (vol) vol.value = h2sRef.vol || '10.0';
    if (fr) fr.value = h2sRef.fr || '1.500';
    if (waktu) waktu.value = h2sRef.waktu || '60';
    if (sk) sk.value = h2sRef.sk || '25.0';
    if (pm) pm.value = h2sRef.p || '760';
    const result = calculateH2sResult({
      kons: toNum(kons?.value),
      vol: toNum(vol?.value),
      fr: toNum(fr?.value),
      waktu: toNum(waktu?.value),
      sk: toNum(sk?.value),
      p: toNum(pm?.value),
    });
    if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(4);
    if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
    lockCalcReferenceRow(row);
  };

  const applyDebuMdlDefaults = (root) => {
    if (!isDebuPm() || !root) return;
    const row = root.querySelector('[data-row-type="debu-mdl"]');
    if (!row) return;
    const fallbackLod = getCurrentParameterLodReference();
    const reference = isKadarDebuTotal() ? DEBU_TOTAL_REFERENCE : DEBU_AMBIEN_REFERENCE;
    const berat = row.querySelector('[data-debu="berat"]');
    const fr = row.querySelector('[data-debu="fr"]');
    const waktu = row.querySelector('[data-debu="waktu"]');
    const sk = row.querySelector('[data-debu="sk"]');
    const p = row.querySelector('[data-debu="p"]');
    if (berat) berat.value = fallbackLod.kons || reference.berat || '0.0001';
    if (fr) fr.value = fallbackLod.fr || reference.fr || '500.00';
    if (waktu) waktu.value = fallbackLod.waktu || reference.waktu || '60';
    if (sk) sk.value = fallbackLod.sk || reference.sk || '25.0';
    if (p) p.value = fallbackLod.pm || reference.p || '760';
    prepareDebuMdlRow(row);
    clearDebuKadarIfIncomplete(row);
  };

  const applyHcLodDefaults = (root) => {
    if (!isHcLike() || !root) return;
    const lodRow = root.querySelector('[data-row-type="hc-lod"]');
    if (!lodRow) return;
    const kons = lodRow.querySelector('[data-hc="kons"]');
    const vol = lodRow.querySelector('[data-hc="vol"]');
    const waktu = lodRow.querySelector('[data-hc="waktu"]');
    const fr = lodRow.querySelector('[data-hc="fr"]');
    const sk = lodRow.querySelector('[data-hc="sk"]');
    const p = lodRow.querySelector('[data-hc="p"]');

    const lodRef = getActiveHcReference().lod || {};
    if (kons) kons.value = lodRef.kons || '0.00394';
    if (vol) vol.value = lodRef.vol || '1';
    if (waktu) waktu.value = lodRef.waktu || '480';
    if (fr) fr.value = lodRef.fr || '0.200';
    if (p) p.value = lodRef.p || '760';

    const skInputs = Array.from(root.querySelectorAll('tr[data-row-type="hc-calc"] [data-hc="sk"]'));
    const hasEmptySk = skInputs.some((el) => !String(el.value || '').trim());
    const skValues = skInputs
      .map((el) => toNum(el.value))
      .filter((v) => !Number.isNaN(v));
    const avgSk = skValues.length ? (skValues.reduce((sum, v) => sum + v, 0) / skValues.length) : NaN;
    if (sk) {
      sk.value = (!hasEmptySk && !Number.isNaN(avgSk)) ? avgSk.toFixed(1) : '';
    }
    calcHcKadarRow(lodRow);
    lockCalcReferenceRow(lodRow);
  };

  const calcHcKadarRow = (row) => {
    if (!row) return;
    const kons = toNum(row.querySelector('[data-hc="kons"]')?.value);
    const vol = toNum(row.querySelector('[data-hc="vol"]')?.value);
    const fr = toNum(row.querySelector('[data-hc="fr"]')?.value);
    const waktu = toNum(row.querySelector('[data-hc="waktu"]')?.value);
    const sk = toNum(row.querySelector('[data-hc="sk"]')?.value);
    const p = toNum(row.querySelector('[data-hc="p"]')?.value);
    const outPpm = row.querySelector('[data-hc="kadar_ppm"]');
    const outUgm3 = row.querySelector('[data-hc="kadar_ugm3"]');
    const outMgm3 = row.querySelector('[data-hc="kadar_mgm3"]');
    if (!outPpm || !outUgm3) return;
    if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) {
      outPpm.value = '';
      outUgm3.value = '';
      if (outMgm3) outMgm3.value = '';
      return;
    }
    const activeRef = getActiveHcReference();
    const factorMass = Number(activeRef.factorMass || 1000);
    const factorVolume = Number(activeRef.factorVolume || 24.45);
    const pressureFactor = Number(activeRef.pressureFactor || 760);
    const denominatorTemperature = Number(activeRef.denominatorTemperature || 298);
    const molecularWeight = Number(activeRef.molecularWeight || 78);
    const factorUgm3 = Number(activeRef.factorUgm3 || 3190.2);
    const denom = fr * waktu * denominatorTemperature * molecularWeight * p;
    if (!denom) {
      outPpm.value = '';
      outUgm3.value = '';
      if (outMgm3) outMgm3.value = '';
      return;
    }
    const numerator = kons * vol * (273 + sk) * factorMass * factorVolume * pressureFactor;
    const ppm = numerator / denom;
    const ugm3 = ppm * factorUgm3;
    outPpm.value = ppm.toFixed(4);
    outUgm3.value = ugm3.toFixed(4);
    if (outMgm3) outMgm3.value = (ugm3 / 1000).toFixed(4);
  };

  const SO2 = {
    buildHasilRow: buildHasilRowSO2,
    buildCalcRow: buildCalcRowSO2,
    buildCalcRowMdl: buildCalcRowSO2Mdl,
    normalizeTimeInputs: normalizeSo2TimeInputs,
    normalizeCalcInputs: normalizeSo2CalcInputs,
    lockMdlRow,
    getHasilBacaData: getSo2HasilBacaData,
    getSkPmData: getSo2SkPmData,
    calcKadar: calcSo2Kadar,
    fillFromSkPm: fillSo2CalcFromSkPm,
    fillFromHasilBaca: fillSo2CalcFromHasilBaca,
    applyFromHasilBaca: fillSo2CalcFromHasilBaca,
    applyFromSkPm: fillSo2CalcFromSkPm,
    applyCalc: calcSo2Kadar,
    normalizeTimeInputs: normalizeSo2TimeInputs,
    normalizeCalcInputs: normalizeSo2CalcInputs,
    lockMdlRow,
  };

  const setType = (type) => {
    syncCurveActionTypeButtons();
    if (type === CURVE_CALIBRATION_TYPE && !isCurveMetalAas()) {
      type = 'hasil-baca';
    }
    currentType = type;
    actionButtons.forEach((btn) => {
      btn.classList.toggle('active', btn.getAttribute('data-action-type') === type);
    });
    syncSkPmActionButtonLabel();
    const label = getTypeLabel(type);
    if (actionParam) {
      actionParam.dataset.actionLabel = label;
    }
    const showHasil = type === 'hasil-baca';
    const showStdKalibrasi = isCurveCalibrationType(type);
    const showPbBlankoTools = showHasil;
    if (blankoBtn) blankoBtn.classList.toggle('d-none', !showPbBlankoTools);
    if (averageBtn) averageBtn.classList.toggle('d-none', !showPbBlankoTools);
    if (deleteBtn) deleteBtn.classList.toggle('d-none', !showHasil);
    if (resetActionBtn) {
      const showReset = (type === 'hasil-baca' || type === 'sk-pm' || type === CURVE_CALIBRATION_TYPE);
      resetActionBtn.classList.toggle('d-none', !showReset);
    }
    if (actionCons) {
      const showCons = showHasil && !isKadarDebuTotal();
      actionCons.classList.toggle('d-none', !showCons);
      if (showCons) {
        actionCons.classList.add('d-flex');
      } else {
        actionCons.classList.remove('d-flex');
      }
    }
    showPbCurvePanel(showStdKalibrasi);
    if (showStdKalibrasi && isCurveMetalAas()) {
      syncPbCurveAxisToggleUi({ forceExpanded: false });
    } else {
      syncPbCurveAxisToggleUi();
    }
    if (resetActionRow) {
      const showReset = (type === 'hasil-baca' || type === 'sk-pm' || type === CURVE_CALIBRATION_TYPE);
      resetActionRow.classList.toggle('d-none', !showReset);
      if (!showReset) resetActionRow.classList.remove('d-flex');
    }
    setConsFormulaUi();
    const showCalc = type === 'hasil-perhitungan';
    if (calcBtn) calcBtn.classList.toggle('d-none', !showCalc);
    if (printBtn) printBtn.classList.toggle('d-none', !showCalc);
    if (closeBtn) closeBtn.classList.toggle('d-none', !showCalc);
  };

  const updateActionGridVisibility = (type = currentType) => {
    if (!actionGrid) return;
    const hideActionGrid = isCurveCalibrationType(type);
    actionGrid.classList.toggle('d-none', hideActionGrid);
    actionGrid.style.display = hideActionGrid ? 'none' : 'block';
    if (hideActionGrid && deleteHint) {
      deleteHint.classList.add('d-none');
    }
  };

  const showGrid = (type) => {
    if (actionGrid) {
      actionGrid.classList.remove('d-none');
      actionGrid.style.display = 'block';
    }
    updateActionGridVisibility(type);
    if (actionFooter) {
      actionFooter.classList.remove('d-none');
      actionFooter.classList.add('d-flex');
      actionFooter.style.display = 'flex';
    }
    if (type === CURVE_CALIBRATION_TYPE && isCurveMetalAas()) {
      if (actionHead) actionHead.innerHTML = '';
      if (actionRows) actionRows.innerHTML = '';
      applySavedPbCurveState(getCurveCalibrationState());
      return;
    }
    if (!actionHead || !actionRows) return;
    if (type === 'sk-pm') {
      actionHead.innerHTML = getSkPmHeadHtml();
    if (!currentSamples.length) {
      currentSamples = ['26.0001/A.XX.001'];
    }
      actionRows.innerHTML = currentSamples.map((code, idx) => buildSkPmRow(code, idx)).join('');
      normalizeBlankoLabels(actionRows);
      return;
    }

    if (type === 'hasil-perhitungan') {
      if (isEmisiAcidGasLike()) {
        actionHead.innerHTML = getSo2CalcHeadHtml();
        actionRows.innerHTML = getCalcCodes().map((code, idx) => SO2.buildCalcRow(code, idx)).join('') + SO2.buildCalcRowMdl();
        normalizeBlankoLabels(actionRows);
        fillCalcLocationInputs(actionRows);
        syncCalcLocationsFromHasilBaca(currentStateKey);
        return;
      }
      if (isHcLike()) {
        actionHead.innerHTML = getCalcHeadHtml();
        actionRows.innerHTML = getCalcCodes().map((code, idx) => buildCalcRowHC(code, idx)).join('');
        normalizeBlankoLabels(actionRows);
        if (!actionRows.querySelector('[data-row-type="hc-lod"]')) {
          actionRows.insertAdjacentHTML('beforeend', buildCalcRowHcLod());
        }
        fillCalcLocationInputs(actionRows);
        syncCalcLocationsFromHasilBaca(currentStateKey);
        fillCalcFromSkPmGeneric(currentStateKey);
        applyHcLodDefaults(actionRows);
        return;
      }
      if (isDebuPm()) {
        actionHead.innerHTML = getCalcHeadHtml();
        actionRows.innerHTML = getCalcCodes().map((code, idx) => buildCalcRowDebu(code, idx)).join('');
        normalizeBlankoLabels(actionRows);
        if (!actionRows.querySelector('[data-row-type="debu-mdl"]')) {
          actionRows.insertAdjacentHTML('beforeend', buildCalcRowDebuMdl());
        }
        fillCalcLocationInputs(actionRows);
        applyDebuMdlDefaults(actionRows);
        fillDebuCalcFromHasilBaca(currentStateKey);
        return;
      }
      if (isMetalAasLk()) {
        actionHead.innerHTML = getCalcHeadHtml();
        actionRows.innerHTML = getCalcCodes().map((code, idx) => buildCalcRowNO2(code, idx)).join('');
        normalizeBlankoLabels(actionRows);
        if (!actionRows.querySelector('[data-row-type="no2-mdl"]')) {
          actionRows.insertAdjacentHTML('beforeend', buildCalcRowMetalAasMdl());
        }
        fillCalcLocationInputs(actionRows);
        fillCalcFromSkPmGeneric(currentStateKey);
      if (isCurveMetalAas()) {
          fillPbMetalAasCalcFromHasilBaca(currentStateKey);
        } else {
          fillGenericCalcFromHasilBaca(currentStateKey);
        }
        syncCalcLocationsFromHasilBaca(currentStateKey);
        return;
      }
      if (isNO2Ambien() || isSO2Ambien() || isOX() || isNH3() || isH2S()) {
        actionHead.innerHTML = getCalcHeadHtml();
        actionRows.innerHTML = getCalcCodes().map((code, idx) => buildCalcRowNO2(code, idx)).join('');
        normalizeBlankoLabels(actionRows);
        if (!actionRows.querySelector('[data-row-type="no2-mdl"]')) {
          actionRows.insertAdjacentHTML('beforeend', buildCalcRowNO2Mdl());
        }
        fillCalcLocationInputs(actionRows);
        fillCalcFromSkPmGeneric(currentStateKey);
        fillNo2CalcFromHasilBaca(currentStateKey);
        applySo2AmbienCalcDefaults(actionRows);
        applyNo2AmbienMdlDefaults(actionRows);
        applySo2AmbienMdlDefaults(actionRows);
        syncCalcLocationsFromHasilBaca(currentStateKey);
        recalculateSo2AmbienRows(actionRows);
        applyOxMdlDefaults(actionRows);
        applyNh3MdlDefaults(actionRows);
        applyH2SMdlDefaults(actionRows);
        return;
      }
      actionHead.innerHTML = `
        <tr>
          <th style="width: 16%;">Lokasi</th>
          <th style="width: 12%;">Konsentrasi</th>
          <th style="width: 12%;">Volume Spl (ml)</th>
          <th style="width: 10%;">Waktu (mnt)</th>
          <th style="width: 10%;">FR (lpm)</th>
            <th style="width: 10%;">Sk C</th>
          <th style="width: 10%;">P mmHg</th>
          <th style="width: 10%;">Kadar (ppm)</th>
        </tr>
      `;
      actionRows.innerHTML = getCalcCodes().map((code) => buildCalcRow(code)).join('');
      normalizeBlankoLabels(actionRows);
      fillCalcLocationInputs(actionRows);
      syncCalcLocationsFromHasilBaca(currentStateKey);
      fillCalcFromSkPmGeneric(currentStateKey);
      if (isCurveMetalAas()) {
        fillPbMetalAasCalcFromHasilBaca(currentStateKey);
      } else {
        fillGenericCalcFromHasilBaca(currentStateKey);
      }
      return;
    }

      if (type === 'hasil-baca' && isSO2HasilBaca()) {
        const gasLabel = getSo2HasilBacaLabel();
        actionHead.innerHTML = `
          <tr>
            <th style="width: 6%;">No</th>
            <th style="width: 18%;">No. sampel</th>
            <th style="width: 12%;">Vol. Spl ${gasLabel} (ml)</th>
            <th style="width: 12%;">Waktu baca</th>
            <th style="width: 12%;">Hasil baca</th>
            <th style="width: 12%;">Kand spl (mg)</th>
            <th style="width: 12%;">P.enceran (x)</th>
            <th style="width: 16%;">knd - bl ${gasLabel} (mg)</th>
          </tr>
        `;
        actionRows.innerHTML = currentSamples.map((code, idx) => SO2.buildHasilRow(code, idx)).join('');
        normalizeBlankoLabels(actionRows);
        SO2.normalizeTimeInputs();
        unlockSo2HasilBacaInputs();
        return;
      }

    if (type === 'hasil-baca' && isMetalAasLk()) {
      actionHead.innerHTML = getHasilBacaHeadHtml();
      actionRows.innerHTML = currentSamples.map((code, idx) => buildHasilRowMetalAas(code, idx)).join('');
      normalizeBlankoLabels(actionRows);
      SO2.normalizeTimeInputs();
      unlockSo2HasilBacaInputs();
      renumberSo2Rows();
      return;
    }

    if (type === 'hasil-baca' && isDebuPm()) {
      actionHead.innerHTML = getHasilBacaHeadHtml();
      actionRows.innerHTML = currentSamples.map((code, idx) => buildHasilRowDebuPm25(code, idx)).join('');
      normalizeBlankoLabels(actionRows);
      normalizeDebuJamInputs(actionRows);
      return;
    }

    if (type === 'hasil-baca' && isBTX()) {
      actionHead.innerHTML = getHasilBacaHeadHtml();
      actionRows.innerHTML = currentSamples.map((code, idx) => buildHasilRowBTX(code, idx)).join('');
      normalizeBlankoLabels(actionRows);
      return;
    }

    if (type === 'hasil-baca' && isHcLike()) {
      actionHead.innerHTML = getHasilBacaHeadHtml();
      actionRows.innerHTML = currentSamples.map((code, idx) => buildHasilRowHC(code, idx)).join('');
      normalizeBlankoLabels(actionRows);
      updateHcCalc();
      return;
    }

    if (type === 'hasil-baca' && isNo2Like() && !isSO2Ambien()) {
      actionHead.innerHTML = `
        <tr>
          <th style="width: 6%;">No</th>
          <th style="width: 18%;">No. Sampel</th>
          <th style="width: 12%;">Volume (ml)</th>
          <th style="width: 12%;">Waktu baca</th>
          <th style="width: 12%;">Hasil baca</th>
          <th style="width: 12%;">Kand spl (mg)</th>
          <th style="width: 12%;">P.enceran (x)</th>
          <th style="width: 16%;">knd - bl (mg)</th>
        </tr>
      `;
      actionRows.innerHTML = currentSamples.map((code, idx) => buildHasilRowNO2(code, idx)).join('');
      normalizeBlankoLabels(actionRows);
      fillNo2SampleLabels();
      normalizeNo2RowNumbers();
      if (isNO2Ambien()) {
        applyNo2AmbienKandFromCons();
      }
      return;
    }

    actionHead.innerHTML = `
      <tr>
        <th style="width: 20%;">No. Sample</th>
        <th style="width: 12%;">Waktu</th>
        <th style="width: 10%;">Volume</th>
        <th style="width: 10%;">RT (Mnt)</th>
        <th style="width: 10%;">L. Area</th>
        <th style="width: 12%;">Rata-rata</th>
        <th style="width: 13%;">Pengencer</th>
        <th style="width: 13%;">Kand BL</th>
      </tr>
    `;
    actionRows.innerHTML = currentSamples.map((code, idx) => buildHasilRow(code, idx)).join('');
    normalizeBlankoLabels(actionRows);
  };

  const getStateKey = (order, param, location, locKeyOverride = '') => {
    const locKey = locKeyOverride || location?.koding || location?.nama || '';
    return `${order}::${param}::${locKey}`;
  };

  const ensureActionState = (key) => {
    if (!key) return null;
    if (!actionState.has(key)) {
      actionState.set(key, { currentType: 'sk-pm', types: {}, seeded: false });
    }
    return actionState.get(key);
  };

  const syncInputAttributes = (root) => {
    if (!root) return;
    root.querySelectorAll('input, select, textarea').forEach((el) => {
      if (el instanceof HTMLInputElement) {
        if (el.type === 'checkbox' || el.type === 'radio') {
          if (el.checked) {
            el.setAttribute('checked', 'checked');
          } else {
            el.removeAttribute('checked');
          }
        } else {
          el.setAttribute('value', el.value);
        }
        return;
      }
      if (el instanceof HTMLSelectElement) {
        Array.from(el.options).forEach((opt) => {
          if (opt.value === el.value) {
            opt.setAttribute('selected', 'selected');
          } else {
            opt.removeAttribute('selected');
          }
        });
        return;
      }
      if (el instanceof HTMLTextAreaElement) {
        el.textContent = el.value;
      }
    });
  };

  const saveCurrentTypeState = (key) => {
    const state = ensureActionState(key);
    if (!state) return;
    state.currentType = currentType;
    if (currentType === CURVE_CALIBRATION_TYPE) {
      state.types[currentType] = {
        headHtml: state.types[currentType]?.headHtml || '',
        rowsHtml: state.types[currentType]?.rowsHtml || '',
        pbCurveY: getInputPayloadValue(pbCurveYInput),
        pbCurveReferenceY: getDefaultPbCurveYValue(),
        pbCalibrationRows: getPbCalibrationRowsPayload(),
        pbAxisOptions: getCurveAxisInputStatePayload(),
        pbCurveEquationPosition: getPbCurveEquationPositionState(),
      };
      return;
    }
    if (!actionHead || !actionRows) return;
    syncInputAttributes(actionRows);
    state.types[currentType] = {
      headHtml: actionHead.innerHTML,
      rowsHtml: actionRows.innerHTML,
      pbCurveY: state.types[currentType]?.pbCurveY || '',
      pbCurveReferenceY: state.types[currentType]?.pbCurveReferenceY || '',
      pbCalibrationRows: state.types[currentType]?.pbCalibrationRows || [],
      pbAxisOptions: state.types[currentType]?.pbAxisOptions || getCurveAxisInputStatePayload(),
    };
  };

  const applyTypeState = (key, type) => {
    const state = key ? ensureActionState(key) : null;
    if (!state || !actionHead || !actionRows) return false;
    const saved = state.types?.[type];
    if (type === CURVE_CALIBRATION_TYPE) {
      if (!isCurveMetalAas()) return false;
      applySavedPbCurveState(saved || getCurveCalibrationState(key));
      prepareLocaleNumericInputs(pbCurvePanel);
      return true;
    }
    if (!saved) return false;
    if (isSO2HasilBaca() && type === 'hasil-baca' && !saved.rowsHtml.includes('data-so2="label"')) {
      return false;
    }
    if (isNO2Ambien() && type === 'hasil-baca' && !saved.rowsHtml.includes('data-no2="label"')) {
      return false;
    }
    if ((isNO2Ambien() || isSO2Ambien()) && type === 'hasil-perhitungan' && !saved.rowsHtml.includes('data-row-type="no2-calc"')) {
      return false;
    }
    if (isHcLike() && type === 'hasil-baca' && !saved.rowsHtml.includes('data-row-type="hc-hasil"')) {
      return false;
    }
    if (isNH3() && type === 'hasil-perhitungan' && !saved.rowsHtml.includes('data-row-type="no2-calc"')) {
      return false;
    }
    if (isH2S() && type === 'hasil-perhitungan' && !saved.rowsHtml.includes('data-row-type="no2-calc"')) {
      return false;
    }
    if (isDebuPm() && type === 'hasil-perhitungan' && !saved.rowsHtml.includes('data-row-type="debu-calc"')) {
      return false;
    }
    if (isHcLike() && type === 'hasil-perhitungan' && !saved.rowsHtml.includes('data-row-type="hc-calc"')) {
      return false;
    }
    if (isMetalAasLk() && type === 'hasil-baca' && saved.headHtml.includes('P.enceran')) {
      return false;
    }
    if (isMetalAasLk() && type === 'hasil-baca' && saved.rowsHtml.includes('data-row-type="no2-hasil"')) {
      return false;
    }
    if (isNo2Like() && !isSO2Ambien() && type === 'hasil-baca' && !saved.rowsHtml.includes('data-row-type="no2-hasil"')) {
      return false;
    }
    if (isSO2HasilBaca() && type === 'hasil-baca' && !saved.rowsHtml.includes('data-row-type="so2-hasil"')) {
      return false;
    }
    if (isEmisiAcidGasLike() && type === 'hasil-perhitungan' && !saved.rowsHtml.includes('data-row-type="so2-calc"')) {
      return false;
    }
    actionHead.innerHTML = type === 'sk-pm' ? getSkPmHeadHtml() : saved.headHtml;
    actionRows.innerHTML = saved.rowsHtml;
    normalizeBlankoLabels(actionRows);
    if (isOX() && type === 'hasil-perhitungan') {
      actionHead.innerHTML = getCalcHeadHtml();
      if (state.types?.[type]) {
        state.types[type].headHtml = actionHead.innerHTML;
      }
    }
    if (isNH3() && type === 'hasil-perhitungan' && actionRows) {
      applyNh3MdlDefaults(actionRows);
    }
    if (isH2S() && type === 'hasil-perhitungan' && actionRows) {
      applyH2SMdlDefaults(actionRows);
    }
    if ((isNO2Ambien() || isSO2Ambien()) && type === 'hasil-perhitungan' && actionRows) {
      applyNo2AmbienMdlDefaults(actionRows);
      applySo2AmbienMdlDefaults(actionRows);
      fillCalcFromSkPmGeneric(currentStateKey);
      fillNo2CalcFromHasilBaca(currentStateKey);
      applySo2AmbienCalcDefaults(actionRows);
      recalculateSo2AmbienRows(actionRows);
    }
    if (isDebuPm() && type === 'hasil-perhitungan' && actionRows) {
      if (!actionRows.querySelector('[data-row-type="debu-mdl"]')) {
        actionRows.insertAdjacentHTML('beforeend', buildCalcRowDebuMdl());
      }
      applyDebuMdlDefaults(actionRows);
    }
    if (isHcLike() && type === 'hasil-perhitungan') {
      const hasSavedHcResults = hcCalcRowsHaveSavedResults(actionRows);
      if (!actionRows?.querySelector('[data-row-type="hc-lod"]')) {
        actionRows?.insertAdjacentHTML('beforeend', buildCalcRowHcLod());
      }
      if (!hasSavedHcResults) {
        fillCalcFromSkPmGeneric(currentStateKey);
        applyHcLodDefaults(actionRows);
        fillHcCalcFromHasilBaca(currentStateKey);
      }
    }
    if (isHcLike() && type === 'hasil-baca') {
      updateHcCalc();
    }
    if (isMetalAasLk() && type === 'hasil-baca') {
      SO2.normalizeTimeInputs();
      unlockSo2HasilBacaInputs();
      renumberSo2Rows();
      if (isCurveMetalAas()) {
        applySavedPbCurveState(getCurveCalibrationState(key));
        applyPbMetalAasKandFromCons();
      }
    }
    if (isDebuPm() && type === 'hasil-baca') {
      normalizeDebuJamInputs(actionRows);
    }
    if (type === 'hasil-baca') {
      normalizeGenericWaktuBacaInputs(actionRows);
    }
    if (isNO2Ambien() && type === 'hasil-baca' && actionRows) {
      applyNo2AmbienKandFromCons();
    }
    if (type === 'hasil-perhitungan') {
      fillCalcLocationInputs(actionRows);
      syncCalcLocationsFromHasilBaca(currentStateKey);
    }
    prepareLocaleNumericInputs(actionRows);
    return true;
  };

  const restoreActionState = (key) => {
    const state = key ? ensureActionState(key) : null;
    if (!state || !actionHead || !actionRows) return false;
    const type = state.currentType || 'sk-pm';
    setType(type);
    showGrid(type);
    const restored = applyTypeState(key, type);
    if (isEmisiAcidGasLike() && (type === 'hasil-baca' || type === 'hasil-perhitungan')) {
      actionRows.querySelectorAll('input').forEach((input) => input.removeAttribute('placeholder'));
    }
    if (isOX() && type === 'hasil-perhitungan' && actionRows) {
      if (!actionRows.querySelector('[data-row-type="no2-mdl"]')) {
        actionRows.insertAdjacentHTML('beforeend', buildCalcRowNO2Mdl());
      }
      applyOxMdlDefaults(actionRows);
    }
    if (isSO2HasilBaca() && type === 'hasil-baca') {
      actionRows.querySelectorAll('[data-so2="label"]').forEach((input) => {
        input.setAttribute('readonly', 'readonly');
        input.setAttribute('tabindex', '-1');
      });
      SO2.normalizeTimeInputs();
      unlockSo2HasilBacaInputs();
      renumberSo2Rows();
    }
    if (isMetalAasLk() && type === 'hasil-baca') {
      actionRows.querySelectorAll('[data-so2="label"]').forEach((input) => {
        input.setAttribute('readonly', 'readonly');
        input.setAttribute('tabindex', '-1');
      });
      SO2.normalizeTimeInputs();
      unlockSo2HasilBacaInputs();
      renumberSo2Rows();
    }
    if (isNo2Like() && !isSO2Ambien() && currentType === 'hasil-baca' && !isNH3()) {
      fillNo2SampleLabels();
      normalizeNo2RowNumbers();
      if (isNO2Ambien()) {
        applyNo2AmbienKandFromCons();
      }
    }
      if (isEmisiAcidGasLike() && type === 'hasil-perhitungan') {
        SO2.normalizeCalcInputs(actionRows);
        if (!actionRows.querySelector('[data-row-type="so2-mdl"]')) {
          actionRows.insertAdjacentHTML('beforeend', SO2.buildCalcRowMdl());
        }
        fillCalcLocationInputs(actionRows);
        syncCalcLocationsFromHasilBaca(currentStateKey);
        SO2.lockMdlRow(actionRows);
        SO2.applyFromHasilBaca(currentStateKey);
        SO2.applyFromSkPm(currentStateKey);
        SO2.applyCalc(actionRows);
      }
      if ((isNO2Ambien() || isSO2Ambien()) && type === 'hasil-perhitungan') {
        fillCalcLocationInputs(actionRows);
        syncCalcLocationsFromHasilBaca(currentStateKey);
        fillCalcFromSkPmGeneric(currentStateKey);
        fillNo2CalcFromHasilBaca(currentStateKey);
      }
      if (!isEmisiAcidGasLike() && !isNO2Ambien() && !isSO2Ambien() && type === 'hasil-perhitungan') {
        fillCalcLocationInputs(actionRows);
        syncCalcLocationsFromHasilBaca(currentStateKey);
        fillCalcFromSkPmGeneric(currentStateKey);
        if (isCurveMetalAas()) {
          fillPbMetalAasCalcFromHasilBaca(currentStateKey);
        } else {
          fillGenericCalcFromHasilBaca(currentStateKey);
        }
      }
      prepareLocaleNumericInputs(actionRows);
      return restored;
    };

  actionModal?.addEventListener('show.bs.modal', (event) => {
    const trigger = event.relatedTarget;
    const formulaReferenceKeys = getFormulaReferenceKeysForTrigger(trigger);
    if (!areFormulaReferencesLoaded(formulaReferenceKeys)) {
      event.preventDefault();
      if (!(trigger instanceof HTMLElement)) return;

      pendingFormulaModalTrigger = trigger;
      const originalHtml = trigger.innerHTML;
      const originalTitle = trigger.getAttribute('title') || '';
      trigger.disabled = true;
      trigger.setAttribute('aria-busy', 'true');
      trigger.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Memuat referensi...';

      void hydrateFormulaReferences(formulaReferenceKeys).then(async (loaded) => {
        trigger.disabled = false;
        trigger.removeAttribute('aria-busy');
        trigger.innerHTML = originalHtml;
        trigger.setAttribute('title', originalTitle);

        if (!loaded) {
          if (pendingFormulaModalTrigger === trigger) {
            pendingFormulaModalTrigger = null;
          }
          await notify('error', 'Referensi formula gagal dimuat. Modal hitung tidak dibuka agar hasil tidak memakai acuan fallback.');
          return;
        }

        if (pendingFormulaModalTrigger !== trigger) return;
        pendingFormulaModalTrigger = null;
        window.bootstrap?.Modal.getOrCreateInstance(actionModal).show(trigger);
      });
      return;
    }

    const groupedLocations = getGroupPayloads(trigger);
    const location = buildGroupedLocation(groupedLocations);
    const groupedCodes = (location.samples || []).map((code) => formatKodingDisplay(code));
    const order = groupedCodes.length > 1
      ? groupedCodes.join(', ')
      : (trigger?.getAttribute('data-order') || '-');
    const param = trigger?.getAttribute('data-param') || '-';
    const paramCategory = trigger?.getAttribute('data-param-category') || '-';
    currentSaveUrl = trigger?.getAttribute('data-save-url') || '';
    const locKeyOverride = getParameterGroupKey(trigger)
      || trigger?.getAttribute('data-location-key')
      || '';
    if (actionOrder) actionOrder.textContent = order;
    if (actionParam) actionParam.textContent = param;
    if (actionCategory) actionCategory.textContent = `Kategori: ${paramCategory}`;
    currentParamCategory = paramCategory;
    currentLocation = location;
    currentStateKey = getStateKey(order, param, location, locKeyOverride);
    syncCurveActionTypeButtons();
    const state = ensureActionState(currentStateKey);
    seedStateFromBackend(location);
    if (state) {
      modalSessionSnapshot = cloneTypesState(state.types);
      modalSessionType = state.currentType || 'sk-pm';
      if (modalSessionType === CURVE_CALIBRATION_TYPE && !isCurveMetalAas()) {
        modalSessionType = 'sk-pm';
      }
      modalSessionCommitted = false;
    } else {
      modalSessionSnapshot = null;
      modalSessionType = 'sk-pm';
      modalSessionCommitted = false;
    }
    currentSamples = (Array.isArray(location.samples) ? location.samples : []).map((code) => formatKodingDisplay(code));
    currentCalcCodes = currentSamples.slice();
    if (!currentSamples.length) {
      const prefix = location.koding ? formatKodingDisplay(location.koding) : '26.0001/A.XX.001';
      currentSamples = [prefix];
    }
    if (currentCalcCodes.length) {
      currentCalcCodes = currentCalcCodes.filter((code) => code && String(code).trim());
    }
    if (!currentCalcCodes.length) {
      currentCalcCodes = currentSamples.slice();
    }
    const restored = restoreActionState(currentStateKey);
    if (!restored) {
      setType('sk-pm');
      showGrid('sk-pm');
    }
    if (currentType !== CURVE_CALIBRATION_TYPE && actionHead && actionRows && !actionHead.innerHTML.trim() && !actionRows.innerHTML.trim()) {
      setType('sk-pm');
      showGrid('sk-pm');
    }
    if (actionGrid) {
      updateActionGridVisibility(currentType);
    }
    setReferenceUiVisible(isSO2Ambien());
    if (actionFooter) {
      actionFooter.classList.remove('d-none');
      actionFooter.classList.add('d-flex');
      actionFooter.style.display = 'flex';
    }
    if (deleteHint) deleteHint.classList.add('d-none');
    deleteMode = false;
    if (deleteBtn) deleteBtn.classList.remove('active');
    prepareLocaleNumericInputs(actionRows);
    refreshCurveMetalReferenceInModal();
  });

  actionModal?.addEventListener('hide.bs.modal', () => {
    if (currentStateKey) {
      const state = ensureActionState(currentStateKey);
      if (state) {
        if (!modalSessionCommitted) {
          state.types = cloneTypesState(modalSessionSnapshot);
          state.currentType = modalSessionType || 'sk-pm';
        } else {
          saveCurrentTypeState(currentStateKey);
          modalSessionSnapshot = cloneTypesState(state.types);
          modalSessionType = state.currentType || currentType || 'sk-pm';
        }
      }
    }
  });

  actionButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const type = btn.getAttribute('data-action-type') || 'sk-pm';
      saveCurrentTypeState(currentStateKey);
      setType(type);
      showGrid(type);
      applyTypeState(currentStateKey, type);
      refreshCurveMetalReferenceInModal();
      if (isEmisiAcidGasLike() && (type === 'hasil-baca' || type === 'hasil-perhitungan')) {
        actionRows?.querySelectorAll('input').forEach((input) => input.removeAttribute('placeholder'));
      }
      if (isOX() && type === 'hasil-perhitungan' && actionRows) {
        if (!actionRows.querySelector('[data-row-type="no2-mdl"]')) {
          actionRows.insertAdjacentHTML('beforeend', buildCalcRowNO2Mdl());
        }
        applyOxMdlDefaults(actionRows);
      }
      if (isNH3() && type === 'hasil-perhitungan' && actionRows) {
        if (!actionRows.querySelector('[data-row-type="no2-mdl"]')) {
          actionRows.insertAdjacentHTML('beforeend', buildCalcRowNO2Mdl());
        }
        applyNh3MdlDefaults(actionRows);
      }
      if ((isNO2Ambien() || isSO2Ambien()) && type === 'hasil-perhitungan' && actionRows) {
        if (!actionRows.querySelector('[data-row-type="no2-mdl"]')) {
          actionRows.insertAdjacentHTML('beforeend', buildCalcRowNO2Mdl());
        }
        applyNo2AmbienMdlDefaults(actionRows);
        applySo2AmbienMdlDefaults(actionRows);
      }
    if (isH2S() && type === 'hasil-perhitungan' && actionRows) {
      if (!actionRows.querySelector('[data-row-type="no2-mdl"]')) {
        actionRows.insertAdjacentHTML('beforeend', buildCalcRowNO2Mdl());
      }
      applyH2SMdlDefaults(actionRows);
    }
    if (isSO2HasilBaca() && type === 'hasil-baca') {
        actionRows?.querySelectorAll('[data-so2="label"]').forEach((input) => {
          input.setAttribute('readonly', 'readonly');
          input.setAttribute('tabindex', '-1');
        });
        SO2.normalizeTimeInputs();
        unlockSo2HasilBacaInputs();
        renumberSo2Rows();
      }
      if (isMetalAasLk() && type === 'hasil-baca') {
        actionRows?.querySelectorAll('[data-so2="label"]').forEach((input) => {
          input.setAttribute('readonly', 'readonly');
          input.setAttribute('tabindex', '-1');
        });
        SO2.normalizeTimeInputs();
        unlockSo2HasilBacaInputs();
        renumberSo2Rows();
      }
      if (isCurveMetalAas() && type === CURVE_CALIBRATION_TYPE) {
        applySavedPbCurveState(getCurveCalibrationState(currentStateKey));
        applyPbMetalAasKandFromCons();
        fillPbMetalAasCalcFromHasilBaca(currentStateKey);
      }
      if (isEmisiAcidGasLike() && type === 'hasil-perhitungan') {
        SO2.normalizeCalcInputs(actionRows);
        SO2.lockMdlRow(actionRows);
        SO2.applyFromHasilBaca(currentStateKey);
        SO2.applyFromSkPm(currentStateKey);
        SO2.applyCalc(actionRows);
      }
      if (isDebuPm() && type === 'hasil-perhitungan') {
        fillDebuCalcFromHasilBaca(currentStateKey);
        applyDebuMdlDefaults(actionRows);
      }
      if ((isNO2Ambien() || isSO2Ambien()) && type === 'hasil-perhitungan') {
        fillCalcFromSkPmGeneric(currentStateKey);
        fillNo2CalcFromHasilBaca(currentStateKey);
      }
      if (isCurveMetalAas() && type === 'hasil-perhitungan') {
        fillCalcFromSkPmGeneric(currentStateKey);
        fillPbMetalAasCalcFromHasilBaca(currentStateKey);
      }
      if (isHcLike() && type === 'hasil-perhitungan') {
        const hasSavedHcResults = hcCalcRowsHaveSavedResults(actionRows);
        if (!hasSavedHcResults) {
          fillCalcFromSkPmGeneric(currentStateKey);
          fillHcCalcFromHasilBaca(currentStateKey);
          applyHcLodDefaults(actionRows);
        }
      }
      if (!isEmisiAcidGasLike() && !isNO2Ambien() && !isSO2Ambien() && !isHcLike() && !isCurveMetalAas() && type === 'hasil-perhitungan') {
        fillCalcFromSkPmGeneric(currentStateKey);
        fillGenericCalcFromHasilBaca(currentStateKey);
      }
      prepareLocaleNumericInputs(actionRows);
    });
  });
  actionRows?.addEventListener('keydown', (event) => {
    const input = event.target;
    if (input instanceof HTMLInputElement && input.matches('[data-so2="label"]')) {
      event.preventDefault();
      input.blur();
    }
    if (isNo2Like() && !isSO2Ambien() && currentType === 'hasil-baca' && !isNH3()) {
      fillNo2SampleLabels();
      normalizeNo2RowNumbers();
    }
    if (input instanceof HTMLInputElement && input.matches('[data-so2="mdl-vol"]')) {
      event.preventDefault();
      input.blur();
    }
  });

  actionRows?.addEventListener('blur', (event) => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || !input.matches('[data-time-24]')) return;
    const raw = input.value.trim();
    if (!raw) return;
    const digits = raw.replace(/\D/g, '');
    if (digits.length === 4) {
      input.value = `${digits.slice(0, 2)}:${digits.slice(2)}`;
    }
    if (!/^(?:[01]\d|2[0-3]):[0-5]\d$/.test(input.value)) {
      input.value = '';
    }
  }, true);

  actionRows?.addEventListener('keydown', (event) => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || !input.matches('[data-time-24]')) return;
    const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];
    if (allowed.includes(event.key)) return;
    if (event.key.length === 1 && !/[0-9]/.test(event.key)) {
      event.preventDefault();
    }
  });

  const getRowNavigableInputs = (row) => {
    if (!row) return [];
    return Array.from(row.querySelectorAll('input, select, textarea')).filter((field) => {
      if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) {
        return false;
      }
      if (field instanceof HTMLInputElement && field.type === 'hidden') return false;
      if (field.disabled) return false;
      if (field.readOnly) return false;
      if (field.hasAttribute('readonly')) return false;
      return true;
    });
  };

  const moveGridFocus = (currentField, direction = 1) => {
    if (!actionRows) return false;
    const row = currentField.closest('tr');
    if (!row) return false;
    const rows = Array.from(actionRows.querySelectorAll('tr'));
    const rowIndex = rows.indexOf(row);
    if (rowIndex < 0) return false;

    const currentInputs = getRowNavigableInputs(row);
    const currentIndex = currentInputs.indexOf(currentField);
    if (currentIndex < 0) return false;

    const focusField = (field) => {
      if (!field) return false;
      field.focus();
      if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
        const valueLength = String(field.value ?? '').length;
        if (typeof field.setSelectionRange === 'function') {
          try {
            field.setSelectionRange(valueLength, valueLength);
          } catch (_) {
            // Some input types do not support selection range.
          }
        }
      }
      return true;
    };

    const nextIndex = currentIndex + direction;
    if (nextIndex >= 0 && nextIndex < currentInputs.length) {
      return focusField(currentInputs[nextIndex]);
    }

    let scanRowIndex = rowIndex + (direction > 0 ? 1 : -1);
    while (scanRowIndex >= 0 && scanRowIndex < rows.length) {
      const inputs = getRowNavigableInputs(rows[scanRowIndex]);
      if (inputs.length) {
        return focusField(direction > 0 ? inputs[0] : inputs[inputs.length - 1]);
      }
      scanRowIndex += direction > 0 ? 1 : -1;
    }

    return false;
  };

  const moveGridFocusVertical = (currentField, direction = 1) => {
    if (!actionRows) return false;
    const row = currentField.closest('tr');
    if (!row) return false;
    const rows = Array.from(actionRows.querySelectorAll('tr'));
    const rowIndex = rows.indexOf(row);
    if (rowIndex < 0) return false;

    const currentInputs = getRowNavigableInputs(row);
    const currentIndex = currentInputs.indexOf(currentField);
    if (currentIndex < 0) return false;

    const focusField = (field) => {
      if (!field) return false;
      field.focus();
      if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement) {
        const valueLength = String(field.value ?? '').length;
        if (typeof field.setSelectionRange === 'function') {
          try {
            field.setSelectionRange(valueLength, valueLength);
          } catch (_) {
            // Some input types do not support selection range.
          }
        }
      }
      return true;
    };

    let scanRowIndex = rowIndex + direction;
    while (scanRowIndex >= 0 && scanRowIndex < rows.length) {
      const inputs = getRowNavigableInputs(rows[scanRowIndex]);
      if (inputs.length) {
        const targetIndex = Math.min(currentIndex, inputs.length - 1);
        return focusField(inputs[targetIndex]);
      }
      scanRowIndex += direction;
    }

    return false;
  };

  actionRows?.addEventListener('keydown', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLInputElement || target instanceof HTMLSelectElement || target instanceof HTMLTextAreaElement)) return;
    if (target.disabled || target.readOnly || target.hasAttribute('readonly')) return;
    if (event.ctrlKey || event.metaKey || event.altKey) return;

    let direction = 0;
    if (event.key === 'ArrowDown') {
      const moved = moveGridFocusVertical(target, 1);
      if (moved) event.preventDefault();
      return;
    }
    if (event.key === 'ArrowUp') {
      const moved = moveGridFocusVertical(target, -1);
      if (moved) event.preventDefault();
      return;
    }
    if (event.key === 'ArrowRight') direction = 1;
    if (event.key === 'ArrowLeft') direction = -1;
    if (event.key === 'Enter') direction = event.shiftKey ? -1 : 1;
    if (!direction) return;

    const moved = moveGridFocus(target, direction);
    if (moved) {
      event.preventDefault();
    }
  });

  const sanitizeTime24Digits = (rawDigits) => {
    const chars = String(rawDigits || '').replace(/\D/g, '').split('');
    const out = [];
    for (const ch of chars) {
      if (out.length === 0) {
        if (/[0-2]/.test(ch)) out.push(ch);
        continue;
      }
      if (out.length === 1) {
        const h1 = out[0];
        if ((h1 === '2' && /[0-3]/.test(ch)) || (h1 !== '2' && /[0-9]/.test(ch))) {
          out.push(ch);
        }
        continue;
      }
      if (out.length === 2) {
        if (/[0-5]/.test(ch)) out.push(ch);
        continue;
      }
      if (out.length === 3) {
        if (/[0-9]/.test(ch)) out.push(ch);
        break;
      }
      break;
    }
    return out.join('');
  };

  actionRows?.addEventListener('input', (event) => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || !input.matches('[data-time-24]')) return;
    const digits = sanitizeTime24Digits(input.value).slice(0, 4);
    if (digits.length <= 2) {
      input.value = digits;
      return;
    }
    input.value = `${digits.slice(0, 2)}:${digits.slice(2)}`;
  });

  const sanitizeIntegerInput = (value) => String(value || '').replace(/\D/g, '');

  actionRows?.addEventListener('input', (event) => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement)) return;

    if (input.matches('[data-locale-number]')) {
      input.value = sanitizeNumericInput(input.value, {
        integerOnly: input.dataset.localeNumberMode === 'integer',
      });
    }

    // Input SK/PM: hanya angka (desimal boleh), tidak boleh huruf.
    if (currentType === 'sk-pm') {
      const row = input.closest('tr');
      const cells = row ? Array.from(row.querySelectorAll('input')) : [];
      const isSkPmValueCell = cells.length >= 3 && (input === cells[1] || input === cells[2]);
      if (isSkPmValueCell) {
        input.value = sanitizeNumericInput(input.value, {
          integerOnly: false,
        });
      }
      return;
    }

    // Input hasil perhitungan: kolom Waktu dan P mmHg harus bilangan bulat.
    if (currentType !== 'hasil-perhitungan') return;
    const placeholder = (input.getAttribute('placeholder') || '').toLowerCase();
    const isWaktuField = input.matches('[data-debu="waktu"], [data-no2="waktu"], [data-hc="waktu"], [data-so2="waktu"]')
      || placeholder.includes('waktu');
    const isPField = input.matches('[data-debu="p"], [data-hc="p"], [data-so2="p"], [data-skpm="pm"]')
      || placeholder.includes('p mmhg');

    if (isWaktuField || isPField) {
      input.value = sanitizeIntegerInput(input.value);
    }
  });

  actionRows?.addEventListener('blur', (event) => {
    const input = event.target;
    if (input instanceof HTMLInputElement) {
      const normalizedLabel = normalizeBlankoLabelText(input.value);
      if (normalizedLabel !== input.value) {
        input.value = normalizedLabel;
      }
    }
    if (!(input instanceof HTMLInputElement) || !input.matches('[data-locale-number]')) return;
    if (!String(input.value || '').trim()) return;
    const fractionDigits = getLocaleDisplayFractionDigits(input.value);
    input.value = formatLocaleNumberString(input.value, {
      integerOnly: input.dataset.localeNumberMode === 'integer',
      minimumFractionDigits: fractionDigits,
      maximumFractionDigits: fractionDigits,
    });
  }, true);

  const updateNo2Ugm3 = (row) => {
    const ppmInput = row?.querySelector('[data-no2="kadar_ppm"]');
    const ugInput = row?.querySelector('[data-no2="kadar_ugm3"]');
    const mgInput = row?.querySelector('[data-no2="kadar_mgm3"]');
    if (!ppmInput || !ugInput) return;
    const kons = toNum(row?.querySelector('[data-no2="kons"]')?.value);
    const vol = toNum(row?.querySelector('[data-no2="vol"]')?.value);
    const fr = toNum(row?.querySelector('[data-no2="fr"]')?.value);
    const waktu = toNum(row?.querySelector('[data-no2="waktu"]')?.value);
    const sk = toNum(row?.querySelector('[data-skpm="sk"]')?.value);
    const p = toNum(row?.querySelector('[data-skpm="pm"]')?.value);
    const result = calculateNo2Result({ kons, vol, fr, waktu, sk, p });
    if (Number.isNaN(result.ppm) || Number.isNaN(result.ugm3)) {
      ppmInput.value = '';
      ugInput.value = '';
      if (mgInput) mgInput.value = '';
      return;
    }
    ppmInput.value = result.ppm.toFixed(4);
    ugInput.value = result.ugm3.toFixed(4);
    if (mgInput) mgInput.value = (result.ugm3 / 1000).toFixed(4);
  };

  actionRows?.addEventListener('input', (event) => {
    if (!(isNO2Ambien() && currentType === 'hasil-perhitungan')) return;
    const row = event.target.closest('tr');
    if (!row || row.getAttribute('data-row-type') !== 'no2-calc' && row.getAttribute('data-row-type') !== 'no2-mdl') return;
    updateNo2Ugm3(row);
  });

  actionRows?.addEventListener('input', (event) => {
    if (!(isCurveMetalAas() && currentType === 'hasil-perhitungan')) return;
    const row = event.target.closest('tr');
    if (!row || row.getAttribute('data-row-type') !== 'no2-calc' && row.getAttribute('data-row-type') !== 'no2-mdl') return;
    recalculatePbMetalAasRows(actionRows);
  });

  const parseRowsFromHtml = (rowsHtml) => {
    const temp = document.createElement('tbody');
    temp.innerHTML = rowsHtml || '';
    return Array.from(temp.querySelectorAll('tr')).map((row) => {
      const cells = Array.from(row.querySelectorAll('input')).map((input) => input.value ?? '');
      return { row, cells };
    });
  };

  const getSavedRows = (type) => {
    const state = currentStateKey ? ensureActionState(currentStateKey) : null;
    const html = state?.types?.[type]?.rowsHtml || '';
    return parseRowsFromHtml(html);
  };

  const hasEmptyCalcInputs = (rows) => {
    if (!rows || rows.length === 0) return true;
    return rows.some((item) => {
      const inputs = Array.from(item.row.querySelectorAll('input'));
      return inputs.some((input) => {
        if (input.hasAttribute('readonly') || input.hasAttribute('disabled')) {
          return false;
        }
        const val = (input.value ?? '').toString().trim();
        return val === '';
      });
    });
  };

  const formatPrintDate = () => {
    const d = new Date();
    const months = [
      'Januari','Februari','Maret','April','Mei','Juni',
      'Juli','Agustus','September','Oktober','November','Desember'
    ];
    return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
  };

  const buildNo2PrintTable = (rows, gasLabel = 'NO2', includeDilution = true) => {
    const isPbPrint = isCurveMetalAas();
    const volumeHeader = isPbPrint ? 'Vol. Spl (ml)' : `Vol. Spl ${gasLabel} (ml)`;
    let seq = 1;
    const body = rows.map((item) => {
      const inputs = item.row.querySelectorAll('input');
      const label = (inputs[0]?.value || '').trim();
      const rowType = item.row.getAttribute('data-row-type') || '';
      if (rowType === 'no2-mdl') return '';
      const lower = label.toLowerCase();
      if (lower === 'rata-rata') {
        const avgValue =
          item.row.querySelector('[data-no2="kand"]')?.value
          || item.row.querySelector('[data-so2="kand-spl"]')?.value
          || item.row.querySelector('[data-btx="benzene"]')?.value
          || item.row.querySelector('[data-hc="benzene"]')?.value
          || inputs[4]?.value
          || '';
        const cells = isPbPrint
          ? ['', label, '', '', '', avgValue, '']
          : includeDilution
          ? ['', label, '', '', '', avgValue, '', '']
          : ['', label, '', '', '', avgValue, ''];
        return `
          <tr>
            ${cells.map((c) => `<td>${c}</td>`).join('')}
          </tr>
        `;
      }
      const noCell = (lower === 'blk' || lower === 'blanko') ? '' : String(seq++);
      const cells = [
        noCell,
        label,
        inputs[2]?.value || '',
        inputs[1]?.value || '',
        inputs[3]?.value || '',
        inputs[4]?.value || '',
      ];
      if (!isPbPrint && includeDilution) {
        cells.push(inputs[5]?.value || '');
      }
      cells.push(inputs[isPbPrint ? 5 : (includeDilution ? 6 : 5)]?.value || '');
      return `
        <tr>
          ${cells.map((c) => `<td>${c}</td>`).join('')}
        </tr>
      `;
    }).join('');
    return {
      html: `
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>No. Sampel</th>
            <th>Waktu Pembacaan</th>
            <th>${volumeHeader}</th>
            <th>Hasil pembacaan</th>
            <th>Kand spl (mg/L)</th>
            ${(!isPbPrint && includeDilution) ? '<th>P.enceran (x)</th>' : ''}
            <th>${isPbPrint ? 'knd - bl (mg)' : `knd - bl ${gasLabel} (mg/L)`}</th>
          </tr>
        </thead>
        <tbody>
          ${body}
        </tbody>
      </table>
      `,
    };
  };

  const buildDebuPrintTable = (rows) => {
    let seq = 1;
    const body = rows.map((item) => {
      const row = item.row;
      const rowType = row.getAttribute('data-row-type') || '';
      if (rowType === 'debu-mdl') return '';
      const labelInput = row.querySelector('[data-debu="label"]') || row.querySelector('input');
      const label = (labelInput?.value || '').trim();
      const lower = label.toLowerCase();
      if (lower === 'rata-rata') {
        const avgValue = row.querySelector('[data-debu="selisih"]')?.value || '';
        const cells = ['', label, '', '', '', avgValue, '', ''];
        return `
          <tr>
            ${cells.map((c) => `<td>${c}</td>`).join('')}
          </tr>
        `;
      }
      const noCell = (lower === 'blk' || lower === 'blanko') ? '' : String(seq++);
      const cells = [
        noCell,
        label,
        row.querySelector('[data-debu="jam"]')?.value || '',
        row.querySelector('[data-debu="awal"]')?.value || '',
        row.querySelector('[data-debu="akhir"]')?.value || '',
        row.querySelector('[data-debu="selisih"]')?.value || '',
        row.querySelector('[data-debu="brt"]')?.value || '',
        row.querySelector('[data-debu="ket"]')?.value || '',
      ];
      return `
        <tr>
          ${cells.map((c) => `<td>${c}</td>`).join('')}
        </tr>
      `;
    }).join('');
    return {
      html: `
      <table class="print-table">
        <thead>
          <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">No.sampel</th>
            <th rowspan="2">Jam Timbang</th>
            <th colspan="2">Hasil penimbangan (gr)</th>
            <th rowspan="2">Selisih (gr)</th>
            <th rowspan="2">Brt Db-Blanko (gr)</th>
            <th rowspan="2">Ket</th>
          </tr>
          <tr>
            <th>Data awal</th>
            <th>Data akhir</th>
          </tr>
        </thead>
        <tbody>
          ${body}
        </tbody>
      </table>
      `,
    };
  };

  const buildHcPrintTable = (rows, compoundLabel = 'Benzene') => {
    let seq = 1;
    const body = rows.map((item) => {
      const row = item.row;
      const labelInput = row.querySelector('[data-hc="label"]') || row.querySelector('input');
      const label = (labelInput?.value || '').trim();
      const lower = label.toLowerCase();
      if (lower === 'rata-rata') {
        const avgValue = row.querySelector('[data-hc="benzene"]')?.value || '';
        const cells = ['', label, '', '', '', avgValue];
        return `
          <tr>
            ${cells.map((c) => `<td>${c}</td>`).join('')}
          </tr>
        `;
      }
      const noCell = (lower === 'blk' || lower === 'blanko') ? '' : String(seq++);
      const cells = [
        noCell,
        label,
        row.querySelector('[data-hc="vol_cs2"]')?.value || '',
        row.querySelector('[data-hc="rt_benzene"]')?.value || '',
        row.querySelector('[data-hc="area_benzene"]')?.value || '',
        row.querySelector('[data-hc="benzene"]')?.value || '',
      ];
      return `
        <tr>
          ${cells.map((c) => `<td>${c}</td>`).join('')}
        </tr>
      `;
    }).join('');
    return {
      html: `
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>No.sampel</th>
            <th>Vol. CS2 (ml)</th>
            <th>RT ${compoundLabel} (mnt)</th>
            <th>L.Area ${compoundLabel}</th>
            <th>${compoundLabel} (mg/ml)</th>
          </tr>
        </thead>
        <tbody>
          ${body}
        </tbody>
      </table>
      `,
    };
  };

  const buildNo2CalcPrintTable = (rows, gasLabel = 'NO2', includeMgCol = false) => {
    const isPbPrint = isCurveMetalAas();
    const konsHeader = isPbPrint ? 'Kons (mg/L)' : `Kons ${gasLabel} (mg/L)`;
    const volumeHeader = isPbPrint ? 'Vol spl (ml)' : `Vol spl ${gasLabel} (ml)`;
    let mdlRow = '';
    let seq = 1;
    const body = rows.map((item) => {
      const rowType = item.row.getAttribute('data-row-type') || '';
      const inputs = item.row.querySelectorAll('input');
      const isMdl = rowType === 'no2-mdl';
      const noCell = isMdl ? '' : String(seq++);
      const ugm3Raw = inputs[7]?.value || '';
      const mgm3Raw = inputs[8]?.value || '';
      const ugm3Num = toNum(ugm3Raw);
      const mgm3Val = !Number.isNaN(toNum(mgm3Raw))
        ? mgm3Raw
        : (!Number.isNaN(ugm3Num) ? (ugm3Num / 1000).toFixed(4) : '');
      const cells = [
        noCell,
        inputs[0]?.value || '',
        inputs[1]?.value || '',
        inputs[2]?.value || '',
        inputs[4]?.value || '',
        inputs[3]?.value || '',
        inputs[5]?.value || '',
        inputs[6]?.value || '',
        inputs[7]?.value || '',
        ...(includeMgCol ? [mgm3Val] : []),
      ];
      const rowHtml = `
        <tr>
          ${cells.map((c) => `<td>${c}</td>`).join('')}
        </tr>
      `;
      if (isMdl) {
        mdlRow = rowHtml;
        return '';
      }
      return rowHtml;
    }).join('');
    return `
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Lokasi</th>
            <th>${konsHeader}</th>
            <th>${volumeHeader}</th>
            <th>FR (lpm)</th>
            <th>Waktu (mnt)</th>
            <th>Sk (Â°C)</th>
            <th>P mmHg</th>
            <th>Kadar (ug/m3)</th>
            ${includeMgCol ? '<th>Kadar (mg/m3)</th>' : ''}
          </tr>
        </thead>
        <tbody>
          ${body}
          ${mdlRow}
        </tbody>
      </table>
    `;
  };

  const buildDebuCalcPrintTable = (rows) => {
    let mdlRow = '';
    let seq = 1;
    const body = rows.map((item) => {
      const row = item.row;
      const rowType = row.getAttribute('data-row-type') || '';
      const isMdl = rowType === 'debu-mdl';
      const noCell = isMdl ? '' : String(seq++);
      const lokasi = row.querySelector('[data-debu="lokasi"]')?.value || '';
      const lokasiCell = isMdl ? (row.querySelector('input')?.value || 'LOD') : lokasi;
      const cells = [
        noCell,
        lokasiCell,
        row.querySelector('[data-debu="berat"]')?.value || '',
        row.querySelector('[data-debu="fr"]')?.value || '',
        row.querySelector('[data-debu="waktu"]')?.value || '',
        row.querySelector('[data-debu="sk"]')?.value || '',
        row.querySelector('[data-debu="p"]')?.value || '',
        row.querySelector('[data-debu="kadar"]')?.value || '',
      ];
      const rowHtml = `
        <tr>
          ${cells.map((c) => `<td>${c}</td>`).join('')}
        </tr>
      `;
      if (isMdl) {
        mdlRow = rowHtml;
        return '';
      }
      return rowHtml;
    }).join('');
    return `
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Lokasi</th>
            <th>Berat debu (Gram)</th>
            <th>FR (lpm)</th>
            <th>Waktu (mnt)</th>
            <th>Sk (oC)</th>
            <th>P mmHg</th>
            <th>Kadar Debu (mg/m3)</th>
          </tr>
        </thead>
        <tbody>
          ${body}
          ${mdlRow}
        </tbody>
      </table>
    `;
  };

  const buildHcCalcPrintTable = (rows, compoundLabel = 'Benzene') => {
    let lodRow = '';
    let seq = 1;
    const body = rows.map((item) => {
      const row = item.row;
      const rowType = row.getAttribute('data-row-type') || '';
      const isLod = rowType === 'hc-lod';
      const lokasiInput = row.querySelectorAll('input')[1] || row.querySelector('input');
      const lokasi = lokasiInput?.value || '';
      const noCell = isLod ? 'LOD' : String(seq++);
      const cells = [
        noCell,
        isLod ? 'LOD' : lokasi,
        row.querySelector('[data-hc="kons"]')?.value || '',
        row.querySelector('[data-hc="vol"]')?.value || '',
        row.querySelector('[data-hc="fr"]')?.value || '',
        row.querySelector('[data-hc="waktu"]')?.value || '',
        row.querySelector('[data-hc="sk"]')?.value || '',
        row.querySelector('[data-hc="p"]')?.value || '',
        row.querySelector('[data-hc="kadar_ppm"]')?.value || '',
        row.querySelector('[data-hc="kadar_ugm3"]')?.value || '',
        row.querySelector('[data-hc="kadar_mgm3"]')?.value || '',
      ];
      const rowHtml = `
        <tr>
          ${cells.map((c) => `<td>${c}</td>`).join('')}
        </tr>
      `;
      if (isLod) {
        lodRow = rowHtml;
        return '';
      }
      return rowHtml;
    }).join('');
    return `
      <table class="print-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Lokasi</th>
            <th>Konsentrasi</th>
            <th>Volume Spl (ml)</th>
            <th>FR (lpm)</th>
            <th>Waktu (mnt)</th>
            <th>Sk C</th>
            <th>P mmHg</th>
            <th>Kadar ${compoundLabel} (ppm)</th>
            <th>Kadar ${compoundLabel} (ug/m3)</th>
            <th>Kadar ${compoundLabel} (mg/m3)</th>
          </tr>
        </thead>
        <tbody>
          ${body}
          ${lodRow}
        </tbody>
      </table>
    `;
  };

  const getPrintCategoryMeta = () => {
    if (isHcLike()) {
      return {
        sampleLabel: isLKCategory() ? 'Lingkungan Kerja' : 'Ambien',
        headingLabel: isLKCategory() ? 'LINGKUNGAN KERJA' : 'KUALITAS UDARA AMBIEN',
      };
    }
    const raw = String(currentParamCategory || '').trim();
    const normalized = raw.toLowerCase().replace(/\s+/g, '');
    if (isLKCategory() || normalized === 'lk') {
      return {
        sampleLabel: 'Lingkungan Kerja',
        headingLabel: 'LINGKUNGAN KERJA',
      };
    }
    if (isAmbienCategory() || normalized === 'a' || normalized === 'ambient' || normalized === 'ambien') {
      return {
        sampleLabel: 'Ambien',
        headingLabel: 'KUALITAS UDARA AMBIEN',
      };
    }
    if (normalized === 'e' || normalized === 'emisi') {
      return {
        sampleLabel: 'Emisi',
        headingLabel: 'EMISI',
      };
    }
    const fallback = raw && raw !== '-' ? raw : 'Sampel';
    return {
      sampleLabel: fallback,
      headingLabel: fallback.toUpperCase(),
    };
  };

  const sanitizePrintFileName = (value) => {
    const raw = String(value || '').trim();
    if (!raw) return 'hasil-analisis';
    return raw.replace(/[\\/:*?"<>|]+/g, '-').replace(/\s+/g, ' ').trim() || 'hasil-analisis';
  };

  const downloadBlobFile = (blob, fileName) => {
    const href = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = href;
    link.download = fileName;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(href), 1000);
  };

  const getStdKalibrasiFileName = (extension = 'pdf') => {
    const metalLabel = getMetalAasLabel();
    const kode = sanitizePrintFileName(currentLocation?.koding || actionOrder?.textContent || `std-kalibrasi-${metalLabel}`);
    return `${kode}-std-kalibrasi-${metalLabel.toLowerCase()}.${extension}`;
  };

  const getStdKalibrasiExportRows = () => getPbCalibrationRowsPayload().map((row, index) => ({
    no_sampel: row.standard_no || String(index + 1),
    volume: row.volume || '',
    waktu_baca: row.waktu_baca || '',
    hasil_baca: row.absorbansi || '',
    kandungan: row.kandungan || '',
    keterangan: row.note || '',
  }));

  const buildStdKalibrasiTableHtml = () => {
    const rows = getStdKalibrasiExportRows();
    return `
      <table class="print-table">
        <thead>
          <tr>
            <th>No sampel</th>
            <th>Vol</th>
            <th>Waktu baca</th>
            <th>Hasil baca</th>
            <th>Kandungan (mg/L)</th>
            <th>Ket</th>
          </tr>
        </thead>
        <tbody>
          ${rows.map((row) => `
            <tr>
              <td>${row.no_sampel}</td>
              <td>${row.volume}</td>
              <td>${row.waktu_baca}</td>
              <td>${row.hasil_baca}</td>
              <td>${row.kandungan}</td>
              <td>${row.keterangan}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    `;
  };

  const buildStdKalibrasiCurveOverlayCardHtml = (yValue, xValue, r2Value) => {
    const overlayPosition = getPbCurveEquationPositionState();
    const overlayStyle = overlayPosition
      ? `left:${overlayPosition.x}px; top:${overlayPosition.y}px;`
      : 'right:12px; top:18px;';
    const overlayYText = pbCurveOverlayY?.textContent || `y = ${yValue}`;
    const overlayXText = pbCurveOverlayX?.textContent || `x = ${xValue}`;
    const overlayR2Text = pbCurveOverlayR2?.textContent || r2Value;
    return `
      <div class="curve-overlay-card" style="${overlayStyle}">
        <div>${overlayYText}</div>
        <div>${overlayXText}</div>
        <div>${overlayR2Text}</div>
      </div>
    `;
  };

  const buildStdKalibrasiCurveSectionHtml = () => {
    const metalLabel = getMetalAasLabel();
    const svgMarkup = pbCurveSvg?.outerHTML || '';
    const yValue = pbCurveYInput?.value || '-';
    const xValue = pbCurveXInput?.value || '-';
    const r2Value = pbCurveR2?.textContent || 'R² = -';
    return `
      <div class="curve-section">
        <div class="curve-title">kurva kalibrasi ${metalLabel}</div>
        <div class="curve-grid">
          <div class="curve-chart">
            ${svgMarkup || '<div class="curve-empty">Grafik belum tersedia</div>'}
            ${buildStdKalibrasiCurveOverlayCardHtml(yValue, xValue, r2Value)}
          </div>
          <div class="curve-meta">
            <div>y = ${yValue}</div>
            <div>${r2Value}</div>
            <div class="curve-axis">
              <div>Y= <strong>${yValue}</strong> X</div>
              <div>X= <strong>${xValue}</strong> Y</div>
            </div>
          </div>
        </div>
      </div>
    `;
  };

  const getStdKalibrasiCurveImageSrc = () => {
    const svgMarkup = pbCurveSvg?.outerHTML || '';
    if (!svgMarkup) return '';
    return `data:image/svg+xml;base64,${btoa(unescape(encodeURIComponent(svgMarkup)))}`;
  };

  const buildStdKalibrasiExcelPayload = () => {
    const metalLabel = getMetalAasLabel();
    const sampleType = isLKCategory()
      ? 'LK'
      : (isAmbienCategory() ? 'Ambien' : (currentParamCategory || '-'));
    return {
      file_name: getStdKalibrasiFileName('xlsx'),
      parameter_name: actionParam?.textContent?.trim() || metalLabel,
      sample_type: sampleType,
      receipt_date: formatPrintDate(),
      analysis_date: formatPrintDate(),
      analis_name: (currentLocation?.assigned_analis || currentUserName || '-').trim(),
      curve_label: metalLabel,
      curve_y: pbCurveYInput?.value || '',
      curve_x: pbCurveXInput?.value || '',
      rows: getStdKalibrasiExportRows(),
    };
  };

  const buildStdKalibrasiPrintHtml = () => {
    const metalLabel = getMetalAasLabel();
    const fileName = getStdKalibrasiFileName('pdf');
    return {
      fileName,
      html: `
        <html>
          <head>
            <title>${fileName.replace(/\.pdf$/i, '')}</title>
            <base href="${window.location.origin}/">
            <style>
              body { font-family: Arial, sans-serif; color:#000; margin:32px; font-size:12px; }
              .header { margin-bottom: 18px; }
              .header-row { display:flex; align-items:center; justify-content:center; gap:10px; }
              .title-wrap { text-align:left; }
              .title { font-weight:700; font-size:14px; margin:6px 0; }
              .sub { font-size:12px; }
              .meta { margin: 10px 0 16px; font-size:12px; }
              .print-table { width:100%; border-collapse:collapse; font-size:12px; }
              .print-table th, .print-table td { border:1px solid #000; padding:4px 6px; text-align:center; }
              .print-table th { background:#f3f4f6; }
              .logo-img { width:32px; display:block; }
              .curve-section { border:1px solid #000; margin-top:14px; padding:14px; }
              .curve-title { text-align:center; font-weight:700; font-size:14px; margin-bottom:10px; text-transform:none; }
              .curve-grid { display:flex; gap:16px; align-items:flex-start; }
              .curve-chart { flex:1 1 auto; min-width:0; position:relative; }
              .curve-chart svg { width:100%; height:auto; display:block; }
              .curve-overlay-card { position:absolute; min-width:160px; max-width:220px; padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px; background:#fff; box-shadow:0 6px 18px rgba(15, 23, 42, 0.12); font-size:12px; color:#334155; line-height:1.45; }
              .curve-overlay-card div + div { margin-top:4px; }
              .curve-meta { width:180px; font-size:12px; display:flex; flex-direction:column; gap:8px; }
              .curve-axis { margin-top:12px; display:flex; flex-direction:column; gap:6px; }
              .curve-empty { min-height:180px; display:flex; align-items:center; justify-content:center; border:1px dashed #999; color:#666; }
            </style>
          </head>
          <body>
            <div class="header">
              <div class="header-row">
                <img src="${@json($printLogoDataUrl) || toAbsoluteUrl('/images/Logo%20Kemnaker.png')}" class="logo-img" alt="Logo">
                <div class="title-wrap">
                  <div class="title">STD KALIBRASI ${metalLabel.toUpperCase()}</div>
                  <div class="sub">ID Koding: ${currentLocation?.koding || '-'}</div>
                </div>
              </div>
            </div>
            <div class="meta">Parameter: ${actionParam?.textContent || metalLabel} | Tanggal: ${formatPrintDate()}</div>
            ${buildStdKalibrasiTableHtml()}
            ${buildStdKalibrasiCurveSectionHtml()}
          </body>
        </html>
      `,
    };
  };

  const exportStdKalibrasiExcel = () => {
    if (!isCurveMetalAas()) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const payload = buildStdKalibrasiExcelPayload();
    fetch(stdKalibrasiExcelExportUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      },
      body: JSON.stringify(payload),
    })
      .then(async (response) => {
        if (!response.ok) {
          throw new Error('Gagal menyiapkan file Excel std kalibrasi.');
        }
        const blob = await response.blob();
        downloadBlobFile(blob, payload.file_name || getStdKalibrasiFileName('xlsx'));
      })
      .catch(() => {
        notify('error', 'File Excel std kalibrasi tidak dapat diunduh.');
      });
  };

  const exportStdKalibrasiPdf = () => {
    if (!isCurveMetalAas()) return;
    const printPayload = buildStdKalibrasiPrintHtml();
    const win = window.open('', '_blank', 'width=1000,height=900');
    if (!win) return;
    win.document.open();
    win.document.write(printPayload.html);
    win.document.close();
    try {
      win.document.title = printPayload.fileName.replace(/\.pdf$/i, '');
    } catch (e) {}
    const doPrint = () => {
      try {
        win.document.title = printPayload.fileName.replace(/\.pdf$/i, '');
      } catch (e) {}
      win.focus();
      win.print();
    };
    const imgs = Array.from(win.document.images || []).filter((imgEl) => !imgEl.complete);
    if (imgs.length) {
      let pending = imgs.length;
      const done = () => {
        pending -= 1;
        if (pending <= 0) setTimeout(doPrint, 120);
      };
      imgs.forEach((imgEl) => {
        imgEl.onload = done;
        imgEl.onerror = done;
      });
      setTimeout(doPrint, 1000);
      return;
    }
    setTimeout(doPrint, 120);
  };

  printBtn?.addEventListener('click', () => {
    if (!(isEmisiAcidGasLike() || isNO2Gas() || isSO2Ambien() || isNH3() || isOX() || isH2S() || isDebuPm() || isHcLike() || isCurveMetalAas())) return;
    const hasilRows = getSavedRows('hasil-baca');
    const calcRows = getSavedRows('hasil-perhitungan');
    if (hasEmptyCalcInputs(calcRows)) {
      notify('warning', 'Lengkapi semua input di tabel hasil perhitungan sebelum cetak hasil analisis.');
      return;
    }
    const gasLabel = isEmisiAcidGasLike() ? getEmisiAcidGasLabel() : getNo2LikeLabel();
    const isDebu = isDebuPm();
    const isHc = isHcLike();
    const hcLabel = getHcCompoundLabel();
    const instrumentLabel = isHc
      ? '(Dengan Gas Chromatography - GC)'
      : (isCurveMetalAas()
        ? '(Dengan Atomic Absortion Spektrofotometer - AAS)'
        : '(Dengan UV/VIS Spektrofotometer)');
    const hasilPrint = isDebu
      ? buildDebuPrintTable(hasilRows)
      : isHc
        ? buildHcPrintTable(hasilRows, hcLabel)
        : buildNo2PrintTable(hasilRows, gasLabel, !isMetalAasLk());
    const orderCode = actionOrder?.textContent || '-';
    const paramName = actionParam?.textContent || 'NO2';
    const analisName = currentLocation?.assigned_analis || currentUserName || '-';
    const analisHitungName = currentUserName || analisName;
    const logoSrc = @json($printLogoDataUrl) || toAbsoluteUrl('/images/Logo%20Kemnaker.png');
    const analisSignature = currentUserSignature || '';
    const totalSamples = hasilRows.filter((r) => {
      const label = getRowLabelValue(r.row);
      return label && label !== 'blk' && label !== 'blanko' && label !== 'rata-rata';
    }).length;
    const categoryMeta = getPrintCategoryMeta();
    const printFileName = sanitizePrintFileName(currentLocation?.koding || orderCode || 'hasil-analisis');
    const printHtml = `
      <html>
        <head>
          <title>${printFileName}</title>
          <base href="${window.location.origin}/">
          <style>
            body { font-family: Arial, sans-serif; color:#000; margin:40px; font-size:12px; }
            .center { text-align:center; }
            .header { margin-bottom: 20px; }
            .header-row { display:flex; align-items:center; justify-content:center; gap:10px; }
            .header-row .title-wrap { text-align:left; }
            .title { font-weight:700; font-size:12px; margin:6px 0; }
            .sub { font-size:12px; }
            .meta { font-size:12px; margin:14px 0 12px; }
            .meta table { width:100%; border-collapse:collapse; }
            .meta td { padding:2px 4px; vertical-align:top; }
            .print-table { width:100%; border-collapse:collapse; font-size:12px; margin:6px 0 10px; }
            .print-table th, .print-table td { border:1px solid #000; padding:3px 4px; text-align:center; }
            .flex { display:flex; justify-content:space-between; font-size:12px; margin-top:8px; }
            .signature { display:flex; justify-content:space-between; margin-top:28px; font-size:12px; }
            .signature .block { width:45%; text-align:center; }
            .logo-img { width:32px; display:block; }
            .signature-img { display:block; margin:6px auto 2px; max-height:52px; max-width:160px; object-fit:contain; }
            .sign-space { height:40px; }
          </style>
        </head>
        <body>
          <div class="header">
            <div class="header-row">
              <img src="${logoSrc}" class="logo-img" alt="Logo">
              <div class="title-wrap">
                <div class="title">HASIL ANALISIS DAN PERHITUNGAN</div>
                <div class="sub">${instrumentLabel}</div>
              </div>
            </div>
          </div>
          <div class="meta">
            <table>
              <tr><td>1.</td><td>Parameter / Jenis sampel</td><td>:</td><td>${paramName}</td></tr>
              <tr><td>2.</td><td>Jumlah sampel</td><td>:</td><td>${categoryMeta.sampleLabel} = ${totalSamples || '-'}</td></tr>
              <tr><td>3.</td><td>Keterangan wadah sampel</td><td>:</td><td>Baik</td></tr>
              <tr><td>4.</td><td>Tanggal penerimaan sampel</td><td>:</td><td>${formatPrintDate()}</td></tr>
              <tr><td>5.</td><td>Tanggal analisis</td><td>:</td><td>${formatPrintDate()}</td></tr>
              <tr><td>6.</td><td>Analis</td><td>:</td><td>${analisName}</td></tr>
            </table>
          </div>
          ${hasilPrint.html}
          <div class="meta" style="margin-top:10px;">
            <div>HASIL PERHITUNGAN ${categoryMeta.headingLabel} ${orderCode}</div>
            <div>TANGGAL : ${formatPrintDate()}</div>
          </div>
          <div class="flex">
            <div></div>
            <div>Verifikasi : Diterima / Dikembalikan</div>
          </div>
          ${isDebu
            ? buildDebuCalcPrintTable(calcRows)
            : isHc
              ? buildHcCalcPrintTable(calcRows, hcLabel)
              : buildNo2CalcPrintTable(calcRows, gasLabel, true)}
            <div class="signature">
              <div class="block">
                <div>Manajer Teknis,</div>
                <div class="sign-space"></div>
                <div>&nbsp;</div>
              </div>
            <div class="block">
              <div>Surabaya, ${formatPrintDate()}</div>
              <div>Analis Hitung,</div>
              ${analisSignature ? `<img src="${analisSignature}" alt="TTD Analis Hitung" class="signature-img">` : '<div class="sign-space"></div>'}
              <div>( ${analisHitungName} )</div>
            </div>
          </div>
        </body>
      </html>
    `;
    const win = window.open('', '_blank', 'width=900,height=1200');
    const renderAndPrint = (doc, winRef, cleanup) => {
      doc.open();
      doc.write(printHtml);
      doc.close();
      try {
        doc.title = printFileName;
      } catch (e) {}
      const doPrint = () => {
        try {
          if (winRef?.document) winRef.document.title = printFileName;
        } catch (e) {}
        winRef?.focus?.();
        winRef?.print?.();
        if (cleanup) setTimeout(cleanup, 1000);
      };
      const imgs = Array.from(doc.images || []).filter((imgEl) => !imgEl.complete);
      if (imgs.length) {
        let pending = imgs.length;
        const done = () => {
          pending -= 1;
          if (pending <= 0) doPrint();
        };
        imgs.forEach((imgEl) => {
          imgEl.onload = done;
          imgEl.onerror = done;
        });
        setTimeout(doPrint, 1000);
        return;
      }
      setTimeout(doPrint, 120);
    };
    if (win) {
      renderAndPrint(win.document, win);
      return;
    }
    const frame = document.createElement('iframe');
    frame.style.position = 'fixed';
    frame.style.right = '0';
    frame.style.bottom = '0';
    frame.style.width = '0';
    frame.style.height = '0';
    frame.style.border = '0';
    document.body.appendChild(frame);
    const frameDoc = frame.contentWindow?.document;
    if (!frameDoc) return;
    renderAndPrint(frameDoc, frame.contentWindow, () => frame.remove());
  });
  actionRows?.addEventListener('input', (event) => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || !input.matches('[data-so2="mdl-vol"]')) return;
    input.value = '100.0';
  });
  actionRows?.addEventListener('input', () => {
    if (!(isSO2HasilBaca() && currentType === 'hasil-baca')) return;
    SO2.applyFromHasilBaca(currentStateKey);
    SO2.applyFromSkPm(currentStateKey);
    saveCurrentTypeState(currentStateKey);
  });

  consAInput?.addEventListener('input', () => {
    if (isHcLike() && currentType === 'hasil-baca') {
      updateHcCalc();
      applyHcBenzene();
    }
    if (isCurveMetalAas() && currentType === 'hasil-baca') {
      if (!syncingPbCurveFormulaInputs) {
        updatePbCurvePanel();
      }
      applyPbMetalAasKandFromCons();
    }
    if (isNO2Ambien() && currentType === 'hasil-baca') {
      applyNo2AmbienKandFromCons();
    }
  });

  consBInput?.addEventListener('input', () => {
    if (isHcLike() && currentType === 'hasil-baca') {
      updateHcCalc();
      applyHcBenzene();
    }
    if (isCurveMetalAas() && currentType === 'hasil-baca') {
      if (!syncingPbCurveFormulaInputs) {
        updatePbCurvePanel();
      }
      applyPbMetalAasKandFromCons();
    }
    if (isNO2Ambien() && currentType === 'hasil-baca') {
      applyNo2AmbienKandFromCons();
    }
  });

  actionRows?.addEventListener('input', () => {
    if (!currentStateKey) return;
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
      saveCurrentTypeState(currentStateKey);
    }, 300);
  });

  pbCurveRows?.addEventListener('input', () => {
    if (!(isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE && currentStateKey)) return;
    updatePbCurvePanel();
    applyPbMetalAasKandFromCons();
    fillPbMetalAasCalcFromHasilBaca(currentStateKey);
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
      saveCurrentTypeState(currentStateKey);
    }, 300);
  });

  pbCurveYInput?.addEventListener('input', () => {
    if (!(isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE)) return;
    syncPbCurveFormulaFields();
    updatePbCurvePanel();
    applyPbMetalAasKandFromCons();
    fillPbMetalAasCalcFromHasilBaca(currentStateKey);
    if (!currentStateKey) return;
    if (saveTimer) clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
      saveCurrentTypeState(currentStateKey);
    }, 300);
  });

  pbCurveYInput?.addEventListener('change', () => {
    if (!(isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE)) return;
    const curveY = getPbCurveYValue();
    if (!Number.isNaN(curveY) && curveY > 0) {
      pbCurveYInput.value = formatLocaleNumberString(curveY, { minimumFractionDigits: 4, maximumFractionDigits: 4 });
    }
    updatePbCurvePanel();
  });

  pbCurveExportExcelBtn?.addEventListener('click', () => {
    if (!(isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE)) return;
    exportStdKalibrasiExcel();
  });

  pbCurveExportPdfBtn?.addEventListener('click', () => {
    if (!(isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE)) return;
    exportStdKalibrasiPdf();
  });

  pbCurveAxisToggleBtn?.addEventListener('click', () => {
    if (!shouldUseCurveAxisToggle()) return;
    pbCurveAxisExpanded = !pbCurveAxisExpanded;
    syncPbCurveAxisToggleUi();
  });

  Object.values(pbCurveAxisInputs).forEach((input) => {
    if (!(input instanceof HTMLInputElement)) return;
    input.addEventListener('input', () => {
      if (!(isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE)) return;
      updatePbCurvePanel();
      if (!currentStateKey) return;
      if (saveTimer) clearTimeout(saveTimer);
      saveTimer = setTimeout(() => {
        saveCurrentTypeState(currentStateKey);
      }, 300);
    });
    input.addEventListener('change', () => {
      if (!(isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE)) return;
      const normalized = normalizeCurveAxisNumber(input.value);
      input.value = normalized === null ? '' : formatCurveAxisNumber(normalized);
      updatePbCurvePanel();
    });
  });

  pbCurveSvg?.addEventListener('click', (event) => {
    if (pbCurveEquationDragState?.didDrag) {
      pbCurveEquationDragState.didDrag = false;
      return;
    }
    const target = event.target instanceof Element ? event.target.closest('[data-pb-point]') : null;
    const tooltip = pbCurveSvg.querySelector('[data-pb-curve-tooltip]');
    if (!tooltip) return;
    if (!target) {
      tooltip.classList.add('d-none');
      return;
    }
    const labelNode = tooltip.querySelector('[data-pb-curve-tooltip-label]');
    const xNode = tooltip.querySelector('[data-pb-curve-tooltip-x]');
    const yNode = tooltip.querySelector('[data-pb-curve-tooltip-y]');
    const rawX = target.getAttribute('data-x') || '0';
    const rawY = target.getAttribute('data-y') || '0';
    const px = Number(target.getAttribute('data-px') || 0);
    const py = Number(target.getAttribute('data-py') || 0);
    const boxWidth = 118;
    const boxHeight = 52;
    const tooltipX = Math.max(12, Math.min(px + 10, 560 - boxWidth - 12));
    const tooltipY = Math.max(12, py - boxHeight - 10);
    const rect = tooltip.querySelector('rect');
    if (rect) {
      rect.setAttribute('x', String(tooltipX));
      rect.setAttribute('y', String(tooltipY));
    }
    if (labelNode) {
      labelNode.setAttribute('x', String(tooltipX + 10));
      labelNode.setAttribute('y', String(tooltipY + 18));
      labelNode.textContent = `Titik ${target.getAttribute('data-label') || ''}`;
    }
    if (xNode) {
      xNode.setAttribute('x', String(tooltipX + 10));
      xNode.setAttribute('y', String(tooltipY + 32));
      xNode.textContent = `Kons: ${rawX.replace('.', ',')}`;
    }
    if (yNode) {
      yNode.setAttribute('x', String(tooltipX + 10));
      yNode.setAttribute('y', String(tooltipY + 46));
      yNode.textContent = `Abs: ${rawY.replace('.', ',')}`;
    }
    tooltip.classList.remove('d-none');
  });

  pbCurveOverlayCard?.addEventListener('mousedown', (event) => {
    if (!pbCurvePlot || !pbCurveOverlayCard) return;
    const plotRect = pbCurvePlot.getBoundingClientRect();
    const cardRect = pbCurveOverlayCard.getBoundingClientRect();
    pbCurveEquationDragState = {
      offsetX: event.clientX - cardRect.left,
      offsetY: event.clientY - cardRect.top,
      didDrag: false,
      mouseDrag: true,
      plotLeft: plotRect.left,
      plotTop: plotRect.top,
      plotWidth: plotRect.width,
      plotHeight: plotRect.height,
    };
    pbCurveOverlayCard.classList.add('is-dragging');
    event.preventDefault();
  });

  window.addEventListener('mousemove', (event) => {
    if (!pbCurveEquationDragState?.mouseDrag || !pbCurveOverlayCard || !pbCurvePlot) return;
    const boxWidth = pbCurveOverlayCard.offsetWidth || 194;
    const boxHeight = pbCurveOverlayCard.offsetHeight || 64;
    const nextX = Math.max(12, Math.min(event.clientX - pbCurveEquationDragState.plotLeft - pbCurveEquationDragState.offsetX, pbCurveEquationDragState.plotWidth - boxWidth - 12));
    const nextY = Math.max(12, Math.min(event.clientY - pbCurveEquationDragState.plotTop - pbCurveEquationDragState.offsetY, pbCurveEquationDragState.plotHeight - boxHeight - 12));
    pbCurveOverlayCard.style.left = `${nextX}px`;
    pbCurveOverlayCard.style.top = `${nextY}px`;
    pbCurveEquationPosition = { x: nextX, y: nextY };
    pbCurveEquationDragState.didDrag = true;
  });

  window.addEventListener('mouseup', () => {
    if (!pbCurveEquationDragState?.mouseDrag || !pbCurveOverlayCard) return;
    pbCurveOverlayCard.classList.remove('is-dragging');
    if (pbCurveEquationDragState.didDrag && currentStateKey && isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE) {
      saveCurrentTypeState(currentStateKey);
    }
    pbCurveEquationDragState = null;
  });

  actionRows?.addEventListener('input', (event) => {
    if (currentType !== 'hasil-baca') return;
    const row = event.target?.closest?.('tr');
    if (!row) return;
    if (row.getAttribute('data-row-type') === 'rata-rata') return;
    if (hasAverageRow(actionRows)) {
      removeAverageRow(actionRows);
    }
  });

  actionRows?.addEventListener('input', (event) => {
    if (!(isDebuPm() && currentType === 'hasil-baca')) return;
    const row = event.target?.closest?.('tr');
    if (!row) return;
    const label = (row.querySelector('[data-debu="label"]')?.value || '').trim().toLowerCase();
    if (label === 'rata-rata') return;
    if (event.target?.matches?.('[data-debu="awal"], [data-debu="akhir"]')) {
      updateDebuSelisih(row);
      applyDebuBrtDbBlk(actionRows);
    }
    if (event.target?.matches?.('[data-debu="selisih"]')) {
      applyDebuBrtDbBlk(actionRows);
    }
  });

  actionRows?.addEventListener('input', (event) => {
    if (!(isHcLike() && currentType === 'hasil-baca')) return;
    const target = event.target;
    if (!target?.closest?.('tr')) return;
    updateHcCalc();
    if (target.matches?.('[data-hc="area_benzene"], [data-hc="label"]')) {
      applyHcBenzene();
    }
  });

  actionRows?.addEventListener('input', (event) => {
    if (!(isNO2Ambien() && currentType === 'hasil-baca')) return;
    const target = event.target;
    if (!(target instanceof HTMLInputElement)) return;
    if (!target.matches('[data-no2="abs"], [data-no2="label"]')) return;
    applyNo2AmbienKandFromCons();
  });

  actionRows?.addEventListener('input', (event) => {
    if (!(isDebuPm() && currentType === 'hasil-perhitungan')) return;
    const row = event.target?.closest?.('tr');
    if (!row) return;
    if (row.getAttribute('data-row-type') !== 'debu-calc' && row.getAttribute('data-row-type') !== 'debu-mdl') return;
    clearDebuKadarIfIncomplete(row);
  });

  actionRows?.addEventListener('input', (event) => {
    if (!(isHcLike() && currentType === 'hasil-perhitungan')) return;
    const row = event.target?.closest?.('tr');
    if (!row) return;
    const rowType = row.getAttribute('data-row-type');
    if (rowType === 'hc-calc') {
      applyHcLodDefaults(actionRows);
      return;
    }
    if (rowType === 'hc-lod') {
      calcHcKadarRow(row);
    }
  });

  actionRows?.addEventListener('change', (event) => {
    if (currentType !== 'hasil-baca') return;
    const row = event.target?.closest?.('tr');
    if (!row) return;
    if (row.getAttribute('data-row-type') === 'rata-rata') return;
    if (hasAverageRow(actionRows)) {
      removeAverageRow(actionRows);
    }
  });

  resetActionBtn?.addEventListener('click', () => {
    if (currentType === CURVE_CALIBRATION_TYPE) {
      if (pbCurveYInput) pbCurveYInput.value = '';
      applySavedPbCurveState({
        pbCalibrationRows: getDefaultPbCalibrationRows(),
        pbCurveY: '',
        pbCurveReferenceY: getDefaultPbCurveYValue(),
      });
      applyPbMetalAasKandFromCons();
      fillPbMetalAasCalcFromHasilBaca(currentStateKey);
      saveCurrentTypeState(currentStateKey);
      return;
    }
    if (!actionRows) return;
    if (currentType !== 'sk-pm' && currentType !== 'hasil-baca') return;
    if (!isDebuPm()) {
      removeAverageRow(actionRows);
      actionRows.querySelectorAll('tr').forEach((row) => {
        const label = getRowLabelValue(row);
        const rowType = String(row.getAttribute('data-row-type') || '').toLowerCase();
        if (rowType === 'rata-rata' || label === 'rata-rata' || label === 'blanko' || label === 'blk') {
          row.remove();
        }
      });
      actionRows.querySelectorAll('tr').forEach((row) => {
        const firstCell = row.querySelector('td');
        const fields = Array.from(row.querySelectorAll('input, select, textarea'));
        const firstDataField = fields.find((field) => {
          if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) return false;
          if (field instanceof HTMLInputElement && field.type === 'hidden') return false;
          return true;
        }) || null;
        fields.forEach((field) => {
          if (!(field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement)) return;
          if (field instanceof HTMLInputElement && field.type === 'hidden') return;
          if (firstCell && firstCell.contains(field)) return;
          if (currentType === 'hasil-baca' && field === firstDataField) return;
          if (currentType === 'hasil-baca' && field.matches('[data-so2="label"], [data-no2="label"], [data-hc="label"], [data-debu="label"], [data-btx="label"]')) return;
          if (field instanceof HTMLSelectElement) {
            field.selectedIndex = 0;
            return;
          }
          field.value = '';
        });
      });
      if (consAInput) consAInput.value = '';
      if (consBInput) consBInput.value = '';
      if (hcXInput) hcXInput.value = '';
      if (hcYInput) hcYInput.value = '';
      if (pbCurveYInput) pbCurveYInput.value = '';
      if (pbCurveXInput) pbCurveXInput.value = '';
      if (pbCurveR2) pbCurveR2.textContent = 'R² = -';
      actionRows.querySelectorAll('tr').forEach((tr) => tr.classList.remove('table-active'));
      selectedRow = null;
      deleteMode = false;
      deleteBtn?.classList.remove('active');
      if (deleteHint) deleteHint.classList.add('d-none');
      if ((isSO2HasilBaca() || isMetalAasLk()) && currentType === 'hasil-baca') {
        renumberSo2Rows();
      }
      saveCurrentTypeState(currentStateKey);
      return;
    }
    removeAverageRow(actionRows);
    actionRows.querySelectorAll('tr').forEach((row) => {
      const label = getRowLabelValue(row);
      if (label === 'blanko' || label === 'blk') {
        row.remove();
      }
    });
    actionRows.querySelectorAll('tr').forEach((row) => {
      const inputs = Array.from(row.querySelectorAll('input'));
      if (!inputs.length) return;
      if (currentType === 'sk-pm') {
        inputs.forEach((input, idx) => {
          if (idx === 0) return;
          input.value = '';
        });
        return;
      }
      const labelInput = row.querySelector('[data-debu="label"]') || inputs[0];
      inputs.forEach((input) => {
        if (input === labelInput) return;
        input.value = '';
      });
    });
    applyDebuBrtDbBlk(actionRows);
    saveCurrentTypeState(currentStateKey);
    resetDebuCalcInputs(currentStateKey);
  });

  actionRows?.addEventListener('focusin', () => {
    if (!((isSO2HasilBaca() || isMetalAasLk()) && currentType === 'hasil-baca')) return;
    unlockSo2HasilBacaInputs();
  });


  actionRows?.addEventListener('input', (event) => {
    if (!((isNO2Gas() && !isNO2Ambien()) && currentType === 'hasil-baca')) return;
    const target = event.target;
    if (target instanceof HTMLInputElement) {
      const row = target.closest('tr');
      if (row && !target.matches('[data-no2="kand"], [data-no2="kndbl"]')) {
        const kand = row.querySelector('[data-no2="kand"]');
        const kndbl = row.querySelector('[data-no2="kndbl"]');
        if (kand) kand.value = '';
        if (kndbl) kndbl.value = '';
      }
      if (row && target.matches('[data-no2="abs"]') && consAInput && consBInput) {
        const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
        if (label === 'blk' || label === 'blanko') {
          consAInput.value = target.value || '';
          consBInput.value = consBInput.value || '0';
        }
      }
    }
    const hasManualCons = !!(consAInput?.value || consBInput?.value);
    const rows = Array.from(actionRows.querySelectorAll('tr'));
    let totalBlanko = 0;
    let countBlanko = 0;

    rows.forEach((row) => {
      const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
      const abs = toNum(row.querySelector('[data-no2="abs"]')?.value);
      const kandInput = row.querySelector('[data-no2="kand"]');
      if (kandInput && !Number.isNaN(abs) && !hasManualCons) {
        kandInput.value = (abs * NO2_FACTOR).toFixed(4);
      }
      if (kandInput && (Number.isNaN(abs) || abs === null)) {
        kandInput.value = '';
      }
      if (label === 'blk' || label === 'blanko') {
        const kand = toNum(kandInput?.value);
        if (!Number.isNaN(kand)) {
          totalBlanko += kand;
          countBlanko += 1;
        }
      }
    });

    const avg = countBlanko ? (totalBlanko / countBlanko) : NaN;
    rows.forEach((row) => {
      const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
      if (!label || label === 'blk' || label === 'blanko' || label === 'rata-rata') return;
      const kand = toNum(row.querySelector('[data-no2="kand"]')?.value);
      const out = row.querySelector('[data-no2="kndbl"]');
      if (!Number.isNaN(kand) && !Number.isNaN(avg) && out) {
        out.value = (kand - avg).toFixed(4);
      } else if (out) {
        out.value = '';
      }
    });
    normalizeNo2RowNumbers();
  });

  actionRows?.addEventListener('input', (event) => {
    if (!(isCurveMetalAas() && currentType === 'hasil-baca')) return;
    const target = event.target;
    if (!(target instanceof HTMLInputElement)) return;
    const row = target.closest('tr');
    if (row && !target.matches('[data-so2="kand-spl"], [data-so2="knd-bl"]')) {
      const kand = row.querySelector('[data-so2="kand-spl"]');
      if (kand && !target.matches('[data-so2="abs"]')) kand.value = '';
    }
    applyPbMetalAasKandFromCons();
    fillPbMetalAasCalcFromHasilBaca(currentStateKey);
    renumberSo2Rows();
  });


  blankoBtn?.addEventListener('click', async () => {
    if (!actionRows) return;
    if (currentType === 'hasil-baca' && hasUnfilledSampleRows(actionRows)) {
      await notify('warning', 'Isi dulu semua baris sampel sebelum menambah blanko.');
      return;
    }
    const clearBlankoRow = (row) => {
      if (!row) return;
      const inputs = row.querySelectorAll('input');
      inputs.forEach((input, idx) => {
        if (idx === 0) return;
        input.value = '';
      });
    };
    if (isSO2HasilBaca() && currentType === 'hasil-baca') {
      removeAverageRow(actionRows);
      const idx = actionRows.querySelectorAll('tr').length;
      actionRows.insertAdjacentHTML('beforeend', SO2.buildHasilRow('Blanko', idx));
      const labels = actionRows.querySelectorAll('[data-so2="label"]');
      const newLabel = labels[labels.length - 1];
      if (newLabel) {
        newLabel.setAttribute('readonly', 'readonly');
        newLabel.setAttribute('tabindex', '-1');
      }
      clearBlankoRow(actionRows.querySelector('tr:last-child'));
      renumberSo2Rows();
      return;
    }
    if (isMetalAasLk() && currentType === 'hasil-baca') {
      removeAverageRow(actionRows);
      const idx = actionRows.querySelectorAll('tr').length;
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowMetalAas('Blanko', idx));
      clearBlankoRow(actionRows.querySelector('tr:last-child'));
      renumberSo2Rows();
      return;
    }
    if (isDebuPm() && currentType === 'hasil-baca') {
      removeAverageRow(actionRows);
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowDebuPm25('Blanko', actionRows.querySelectorAll('tr').length));
      const insertedRow = actionRows.querySelector('tr:last-child');
      if (insertedRow) {
        insertedRow.querySelectorAll('input').forEach((input, idx) => {
          if (idx === 0) return;
          input.value = '';
        });
      }
      return;
    }
    if (isBTX() && currentType === 'hasil-baca') {
      removeAverageRow(actionRows);
      const rowsCount = actionRows.querySelectorAll('tr').length;
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowBTX('Blanko', rowsCount));
      const insertedRow = actionRows.querySelector('tr:last-child');
      clearBlankoRow(insertedRow);
      return;
    }
    if (isHcLike() && currentType === 'hasil-baca') {
      removeAverageRow(actionRows);
      const rowsCount = actionRows.querySelectorAll('tr').length;
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowHC('Blanko', rowsCount));
      const insertedRow = actionRows.querySelector('tr:last-child');
      clearBlankoRow(insertedRow);
      return;
    }
    if (isNo2Like() && !isSO2Ambien() && currentType === 'hasil-baca') {
      removeAverageRow(actionRows);
      const rowsCount = actionRows.querySelectorAll('tr').length;
      const insertHtml = buildHasilRowNO2('Blanko', rowsCount);
      actionRows.insertAdjacentHTML('beforeend', insertHtml);
      const insertedRow = actionRows.querySelector('tr:last-child');
      clearBlankoRow(insertedRow);
      normalizeNo2RowNumbers();
      return;
    }
    removeAverageRow(actionRows);
    const blankoHtml = buildHasilRow('Blanko');
    actionRows.insertAdjacentHTML('beforeend', blankoHtml);
    const insertedRow = actionRows.querySelector('tr:last-child');
    clearBlankoRow(insertedRow);
  });

  averageBtn?.addEventListener('click', () => {
    if (!actionRows) return;
    removeEmptyBlankoRows(actionRows);
    if (hasAverageRow(actionRows)) return;
    if (isSO2HasilBaca() && currentType === 'hasil-baca') {
      let totalBlanko = 0;
      let countBlanko = 0;
      const rows = Array.from(actionRows.querySelectorAll('tr'));
      rows.forEach((row) => {
        const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
        if (label !== 'blanko') return;
        const kand = parseFloat((row.querySelector('[data-so2="kand-spl"]')?.value || '').toString().replace(',', '.'));
        if (!Number.isNaN(kand)) {
          totalBlanko += kand;
          countBlanko += 1;
        }
      });
      if (!countBlanko) return;
      const avg = totalBlanko / countBlanko;

      // sisipkan baris Rata-rata di bagian akhir
      actionRows.insertAdjacentHTML('beforeend', SO2.buildHasilRow('Rata-rata', actionRows.querySelectorAll('tr').length));
      const avgRow = actionRows.querySelector('tr:last-child');
      if (avgRow) {
        avgRow.setAttribute('data-row-type', 'rata-rata');
        const avgLabel = avgRow.querySelector('[data-so2="label"]');
        if (avgLabel) {
          avgLabel.setAttribute('readonly', 'readonly');
          avgLabel.setAttribute('tabindex', '-1');
        }
        const avgInput = avgRow.querySelector('[data-so2="kand-spl"]');
        if (avgInput) avgInput.value = avg.toFixed(4);
        stripAvgInputs(avgRow);
      }
      renumberSo2Rows();

      // hitung knd-bl per baris sampel = kand spl - rata2 blanko
      actionRows.querySelectorAll('tr').forEach((row) => {
        const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
        if (!label || label === 'blanko' || label === 'rata-rata') return;
        const kand = parseFloat((row.querySelector('[data-so2="kand-spl"]')?.value || '').toString().replace(',', '.'));
        if (Number.isNaN(kand)) return;
        const out = row.querySelector('[data-so2="knd-bl"]');
        if (out) out.value = (kand - avg).toFixed(4);
      });
      SO2.applyFromHasilBaca(currentStateKey);
      return;
    }
    if (isMetalAasLk() && currentType === 'hasil-baca') {
      let totalBlanko = 0;
      let countBlanko = 0;
      const rows = Array.from(actionRows.querySelectorAll('tr'));
      rows.forEach((row) => {
        const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
        if (label !== 'blanko') return;
        const kand = parseFloat((row.querySelector('[data-so2="kand-spl"]')?.value || '').toString().replace(',', '.'));
        if (!Number.isNaN(kand)) {
          totalBlanko += kand;
          countBlanko += 1;
        }
      });
      if (!countBlanko) return;
      const avg = totalBlanko / countBlanko;
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowMetalAas('Rata-rata', actionRows.querySelectorAll('tr').length));
      const avgRow = actionRows.querySelector('tr:last-child');
      if (avgRow) {
        avgRow.setAttribute('data-row-type', 'rata-rata');
        const avgLabel = avgRow.querySelector('[data-so2="label"]');
        if (avgLabel) {
          avgLabel.setAttribute('readonly', 'readonly');
          avgLabel.setAttribute('tabindex', '-1');
        }
        const avgInput = avgRow.querySelector('[data-so2="kand-spl"]');
        if (avgInput) avgInput.value = avg.toFixed(4);
        stripAvgInputs(avgRow);
      }
      renumberSo2Rows();
      actionRows.querySelectorAll('tr').forEach((row) => {
        const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
        if (!label || label === 'blanko' || label === 'rata-rata') return;
        const kand = parseFloat((row.querySelector('[data-so2="kand-spl"]')?.value || '').toString().replace(',', '.'));
        if (Number.isNaN(kand)) return;
        const out = row.querySelector('[data-so2="knd-bl"]');
        if (out) out.value = (kand - avg).toFixed(4);
      });
      fillGenericCalcFromHasilBaca(currentStateKey);
      return;
    }
    if (isDebuPm() && currentType === 'hasil-baca') {
      let total = 0;
      let count = 0;
      const rows = Array.from(actionRows.querySelectorAll('tr'));
      rows.forEach((row) => {
        const label = (row.querySelector('[data-debu="label"]')?.value || '').trim().toLowerCase();
        if (label !== 'blk' && label !== 'blanko') return;
        const selisih = toNum(row.querySelector('[data-debu="selisih"]')?.value);
        if (!Number.isNaN(selisih)) {
          total += selisih;
          count += 1;
        }
      });
      if (!count) return;
      const avg = total / count;
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowDebuPm25('Rata-rata', actionRows.querySelectorAll('tr').length));
      const avgRow = actionRows.querySelector('tr:last-child');
      if (avgRow) {
        avgRow.setAttribute('data-row-type', 'rata-rata');
        const avgInput = avgRow.querySelector('[data-debu="selisih"]');
        if (avgInput) avgInput.value = avg.toFixed(7);
        avgRow.querySelectorAll('input').forEach((input) => {
          if (input.matches('[data-debu="label"]')) return;
          if (input.matches('[data-debu="selisih"]')) return;
          input.value = '';
        });
      }
      applyDebuBrtDbBlk(actionRows);
      return;
    }
    if (isBTX() && currentType === 'hasil-baca') {
      let totalBenzene = 0;
      let totalToluene = 0;
      let totalXylene = 0;
      let count = 0;
      const rows = Array.from(actionRows.querySelectorAll('tr'));
      rows.forEach((row) => {
        const label = (row.querySelector('[data-btx="label"]')?.value || '').trim().toLowerCase();
        if (label !== 'blk' && label !== 'blanko') return;
        const benzene = toNum(row.querySelector('[data-btx="benzene"]')?.value);
        const toluene = toNum(row.querySelector('[data-btx="toluene"]')?.value);
        const xylene = toNum(row.querySelector('[data-btx="xylene"]')?.value);
        if (![benzene, toluene, xylene].some((v) => Number.isNaN(v))) {
          totalBenzene += benzene;
          totalToluene += toluene;
          totalXylene += xylene;
          count += 1;
        }
      });
      if (!count) return;
      const avgBenzene = totalBenzene / count;
      const avgToluene = totalToluene / count;
      const avgXylene = totalXylene / count;
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowBTX('Rata-rata', actionRows.querySelectorAll('tr').length));
      const avgRow = actionRows.querySelector('tr:last-child');
      if (avgRow) {
        avgRow.setAttribute('data-row-type', 'rata-rata');
        const benzeneInput = avgRow.querySelector('[data-btx="benzene"]');
        const tolueneInput = avgRow.querySelector('[data-btx="toluene"]');
        const xyleneInput = avgRow.querySelector('[data-btx="xylene"]');
        if (benzeneInput) benzeneInput.value = avgBenzene.toFixed(4);
        if (tolueneInput) tolueneInput.value = avgToluene.toFixed(4);
        if (xyleneInput) xyleneInput.value = avgXylene.toFixed(4);
        avgRow.querySelectorAll('input').forEach((input, idx) => {
          if (idx === 0) return;
          if (input.matches('[data-btx="benzene"], [data-btx="toluene"], [data-btx="xylene"]')) return;
          input.value = '';
        });
      }
      return;
    }
    if (isHcLike() && currentType === 'hasil-baca') {
      let total = 0;
      let count = 0;
      const rows = Array.from(actionRows.querySelectorAll('tr'));
      rows.forEach((row) => {
        const label = (row.querySelector('[data-hc="label"]')?.value || '').trim().toLowerCase();
        if (label !== 'blk' && label !== 'blanko') return;
        const benzene = toNum(row.querySelector('[data-hc="benzene"]')?.value);
        if (!Number.isNaN(benzene)) {
          total += benzene;
          count += 1;
        }
      });
      if (!count) return;
      const avg = total / count;
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowHC('Rata-rata', actionRows.querySelectorAll('tr').length));
      const avgRow = actionRows.querySelector('tr:last-child');
      if (avgRow) {
        avgRow.setAttribute('data-row-type', 'rata-rata');
        const avgInput = avgRow.querySelector('[data-hc="benzene"]');
        if (avgInput) avgInput.value = avg.toFixed(4);
        avgRow.querySelectorAll('input').forEach((input, idx) => {
          if (idx === 0) return;
          if (input.matches('[data-hc="benzene"]')) return;
          input.value = '';
        });
      }
      return;
    }
    if (isNo2Like() && !isSO2Ambien() && currentType === 'hasil-baca') {
      let totalBlanko = 0;
      let countBlanko = 0;
      const rows = Array.from(actionRows.querySelectorAll('tr'));
      rows.forEach((row) => {
        const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
        if (label !== 'blk' && label !== 'blanko') return;
        const kand = toNum(row.querySelector('[data-no2="kand"]')?.value);
        if (!Number.isNaN(kand)) {
          totalBlanko += kand;
          countBlanko += 1;
        }
      });
      if (!countBlanko) return;
      const avg = totalBlanko / countBlanko;
      actionRows.insertAdjacentHTML('beforeend', buildHasilRowNO2('Rata-rata', actionRows.querySelectorAll('tr').length));
      const avgRow = actionRows.querySelector('tr:last-child');
      if (avgRow) {
        avgRow.setAttribute('data-row-type', 'rata-rata');
        const avgInput = avgRow.querySelector('[data-no2="kand"]');
        if (avgInput) avgInput.value = avg.toFixed(4);
        const inputs = avgRow.querySelectorAll('input');
        inputs.forEach((input, idx) => {
          if (idx === 0) return;
          if (input.matches('[data-no2="kand"]')) return;
          input.value = '';
        });
      }
      normalizeNo2RowNumbers();
      actionRows.querySelectorAll('tr').forEach((row) => {
        const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
        if (!label || label === 'blk' || label === 'blanko' || label === 'rata-rata') return;
        const kand = toNum(row.querySelector('[data-no2="kand"]')?.value);
        const out = row.querySelector('[data-no2="kndbl"]');
        if (!Number.isNaN(kand) && out) {
          out.value = (kand - avg).toFixed(4);
        }
      });
      return;
    }
    if (hasAverageRow(actionRows)) return;
    let total = 0;
    let count = 0;
    actionRows.querySelectorAll('tr').forEach((row) => {
      const cells = row.querySelectorAll('input');
      const label = (cells[0]?.value || '').toLowerCase();
      if (label === 'blanko' || label === 'rata-rata') return;
      const val = parseFloat((cells[4]?.value || '').toString().replace(',', '.'));
      if (!Number.isNaN(val)) {
        total += val;
        count += 1;
      }
    });
    if (!count) return;
    const avg = count ? (total / count).toFixed(4) : '0.0000';
    actionRows.insertAdjacentHTML('beforeend', buildHasilRow('Rata-rata'));
    const lastRow = actionRows.lastElementChild;
    if (lastRow) {
      lastRow.setAttribute('data-row-type', 'rata-rata');
      const inputs = lastRow.querySelectorAll('input');
      if (inputs[5]) inputs[5].value = avg;
      inputs.forEach((input, idx) => {
        if (idx === 0) return;
        if (idx === 5) return;
        input.value = '';
      });
    }
  });

  actionRows?.addEventListener('click', (event) => {
    const row = event.target.closest('tr');
    if (!row) return;
    actionRows.querySelectorAll('tr').forEach((tr) => tr.classList.remove('table-active'));
    row.classList.add('table-active');
    selectedRow = row;
    if (!consAInput || !consBInput) return;
    if (currentType === 'hasil-baca') {
      if (isNO2Gas() && !isNO2Ambien()) {
        const label = (row.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
        if (label === 'rata-rata') return;
        const val = row.querySelector('[data-no2="abs"]')?.value;
        if (val !== undefined) {
          consAInput.value = val;
          consBInput.value = consBInput.value || '0';
        }
      } else if (isSO2HasilBaca()) {
        const label = (row.querySelector('[data-so2="label"]')?.value || '').trim().toLowerCase();
        if (label === 'blanko' || label === 'rata-rata') return;
        const val = row.querySelector('[data-so2="abs"]')?.value;
        if (val !== undefined) {
          consAInput.value = val;
          consBInput.value = consBInput.value || '0';
        }
      }
    }
  });

  deleteBtn?.addEventListener('click', () => {
    if (!actionRows || !selectedRow) return;
    const firstInput = selectedRow.querySelector('input');
    const value = (firstInput?.value || '').trim().toLowerCase();
    const isProtected = value && value !== 'blanko' && value !== 'blk' && value !== 'rata-rata';
    if (isProtected) return;
    selectedRow.remove();
    selectedRow = null;
    deleteMode = false;
    deleteBtn.classList.remove('active');
    if (deleteHint) deleteHint.classList.add('d-none');
    if ((isSO2HasilBaca() || isMetalAasLk()) && currentType === 'hasil-baca') {
      renumberSo2Rows();
    }
  });

  kandblBtn?.addEventListener('click', () => {
    const consA = parseFloat((consAInput?.value || '').replace(',', '.'));
    const consB = parseFloat((consBInput?.value || '').replace(',', '.'));
    if (isHcLike() && currentType === 'hasil-baca') {
      if (Number.isNaN(consA) || Number.isNaN(consB)) return;
      updateHcCalc();
      applyHcBenzene();
      return;
    }
    if (!selectedRow) return;
    if (isHgEmisi() && currentType === 'hasil-baca') {
      if (Number.isNaN(consA)) return;
      const abs = toNum(selectedRow.querySelector('[data-so2="abs"]')?.value);
      if (Number.isNaN(abs)) return;
      const kand = consA * abs;
      const out = selectedRow.querySelector('[data-so2="kand-spl"]');
      if (out) {
        out.value = kand.toFixed(4);
      }
      return;
    }
    if (Number.isNaN(consA) || Number.isNaN(consB)) return;
    if (isSO2HasilBaca() && currentType === 'hasil-baca') {
      const abs = toNum(selectedRow.querySelector('[data-so2="abs"]')?.value);
      if (Number.isNaN(abs)) return;
      const kand = consA * consB;
      const out = selectedRow.querySelector('[data-so2="kand-spl"]');
      if (out) {
        out.value = kand.toFixed(4);
      } else {
        const inputs = selectedRow.querySelectorAll('input');
        if (inputs[4]) inputs[4].value = kand.toFixed(4);
      }
      return;
    }
    if (isCurveMetalAas() && currentType === 'hasil-baca') {
      const abs = toNum(selectedRow.querySelector('[data-so2="abs"]')?.value);
      if (Number.isNaN(abs)) return;
      const formula = getPbConcentrationFormula();
      if (Number.isNaN(formula.consSlope) || Number.isNaN(formula.consIntercept)) return;
      const kand = (formula.consSlope * abs) + formula.consIntercept;
      const out = selectedRow.querySelector('[data-so2="kand-spl"]');
      if (out) out.value = kand.toFixed(4);
      applyPbMetalAasKandFromCons();
      fillPbMetalAasCalcFromHasilBaca(currentStateKey);
      return;
    }
    if (isOX() && currentType === 'hasil-baca') {
      const label = (selectedRow.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
      if (label === 'rata-rata') return;
      const abs = toNum(selectedRow.querySelector('[data-no2="abs"]')?.value);
      if (Number.isNaN(abs)) return;
      const kand = (consA * abs) + consB;
      const out = selectedRow.querySelector('[data-no2="kand"]');
      if (out) {
        out.value = kand.toFixed(4);
      } else {
        const inputs = selectedRow.querySelectorAll('input');
        if (inputs[4]) inputs[4].value = kand.toFixed(4);
      }
      return;
    }
    if ((isNO2Gas() && !isNO2Ambien()) && currentType === 'hasil-baca') {
      const label = (selectedRow.querySelector('[data-no2="label"]')?.value || '').trim().toLowerCase();
      if (label === 'rata-rata') return;
      const abs = toNum(selectedRow.querySelector('[data-no2="abs"]')?.value);
      if (Number.isNaN(abs)) return;
      const kand = consA * consB;
      const out = selectedRow.querySelector('[data-no2="kand"]');
      if (out) {
        out.value = kand.toFixed(4);
      } else {
        const inputs = selectedRow.querySelectorAll('input');
        if (inputs[4]) inputs[4].value = kand.toFixed(4);
      }
    }
  });

  calcBtn?.addEventListener('click', () => {
    if (isEmisiAcidGasLike() && currentType === 'hasil-perhitungan') {
      SO2.applyCalc(actionRows);
    }
    if (isNO2Ambien() && currentType === 'hasil-perhitungan') {
      if (!actionRows) return;
      actionRows.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) {
          if (ppmInput) ppmInput.value = '';
          if (ugm3Input) ugm3Input.value = '';
          return;
        }
        const result = calculateNo2Result({ kons, vol, fr, waktu, sk, p });
        if (Number.isNaN(result.ppm) || Number.isNaN(result.ugm3)) return;
        if (ppmInput) ppmInput.value = result.ppm.toFixed(4);
        if (ugm3Input) ugm3Input.value = result.ugm3.toFixed(4);
      });
    }
    if (isSO2Ambien() && currentType === 'hasil-perhitungan') {
      if (!actionRows) return;
      actionRows.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) {
          if (ppmInput) ppmInput.value = '';
          if (ugm3Input) ugm3Input.value = '';
          if (mgm3Input) mgm3Input.value = '';
          return;
        }
        const result = calculateSo2AmbienResult({
          kons,
          vol,
          fr,
          waktu,
          sk,
          pm: p,
        });
        if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(4);
        if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
        if (mgm3Input) mgm3Input.value = Number.isNaN(result.mgm3) ? '' : result.mgm3.toFixed(4);
      });
    }
    if (isOX() && currentType === 'hasil-perhitungan') {
      if (!actionRows) return;
      actionRows.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) return;
        const result = calculateOxResult({ kons, vol, fr, waktu, sk, p });
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        if (ppmInput) ppmInput.value = Number.isNaN(result.ppm) ? '' : result.ppm.toFixed(6);
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        if (ugm3Input) ugm3Input.value = Number.isNaN(result.ugm3) ? '' : result.ugm3.toFixed(4);
        const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
        if (mgm3Input) mgm3Input.value = Number.isNaN(result.ugm3) ? '' : (result.ugm3 / 1000).toFixed(4);
      });
    }
    if (isNH3() && currentType === 'hasil-perhitungan') {
      if (!actionRows) return;
      actionRows.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) return;
        const denom = fr * waktu * 298 * p;
        if (!denom) return;
        const ppm = (kons * (vol / 10) * (273 + sk) * 1.438 * 760) / denom;
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        if (ppmInput) ppmInput.value = ppm.toFixed(4);
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        if (ugm3Input) ugm3Input.value = (ppm * 695.3).toFixed(4);
        const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
        if (mgm3Input) mgm3Input.value = ((ppm * 695.3) / 1000).toFixed(4);
      });
    }
    if (isH2S() && currentType === 'hasil-perhitungan') {
      if (!actionRows) return;
      actionRows.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'no2-calc' && rowType !== 'no2-mdl') return;
        const kons = toNum(row.querySelector('[data-no2="kons"]')?.value);
        const vol = toNum(row.querySelector('[data-no2="vol"]')?.value);
        const fr = toNum(row.querySelector('[data-no2="fr"]')?.value);
        const waktu = toNum(row.querySelector('[data-no2="waktu"]')?.value);
        const sk = toNum(row.querySelector('[data-skpm="sk"]')?.value);
        const p = toNum(row.querySelector('[data-skpm="pm"]')?.value);
        if ([kons, vol, fr, waktu, sk, p].some((v) => Number.isNaN(v))) return;
        const denom = fr * waktu * 298 * p * 34;
        if (!denom) return;
        const ppm = (kons * vol * (273 + sk) * 760 * 24.45) / denom;
        const ppmInput = row.querySelector('[data-no2="kadar_ppm"]');
        if (ppmInput) ppmInput.value = ppm.toFixed(6);
        const ugm3Input = row.querySelector('[data-no2="kadar_ugm3"]');
        if (ugm3Input) ugm3Input.value = (ppm * 1390.6).toFixed(4);
        const mgm3Input = row.querySelector('[data-no2="kadar_mgm3"]');
        if (mgm3Input) mgm3Input.value = ((ppm * 1390.6) / 1000).toFixed(4);
      });
    }
    if (isHcLike() && currentType === 'hasil-perhitungan') {
      if (!actionRows) return;
      actionRows.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'hc-calc' && rowType !== 'hc-lod') return;
        calcHcKadarRow(row);
      });
    }
    if (isCurveMetalAas() && currentType === 'hasil-perhitungan') {
      if (!actionRows) return;
      recalculatePbMetalAasRows(actionRows);
    }
    if (isDebuPm() && currentType === 'hasil-perhitungan') {
      if (!actionRows) return;
      actionRows.querySelectorAll('tr').forEach((row) => {
        const rowType = row.getAttribute('data-row-type');
        if (rowType !== 'debu-calc' && rowType !== 'debu-mdl') return;
        calcDebuKadarRow(row);
      });
    }
  });

  saveBtn?.addEventListener('click', async (event) => {
    event.preventDefault();
    const data = [];
    if (actionRows) {
      actionRows.querySelectorAll('tr').forEach((row) => {
        const cells = row.querySelectorAll('input');
        data.push(Array.from(cells).map((input) => input.value));
      });
    }
    saveCurrentTypeState(currentStateKey);
    if (isCurveMetalAas() && currentType === CURVE_CALIBRATION_TYPE) {
      applyPbMetalAasKandFromCons();
      fillPbMetalAasCalcFromHasilBaca(currentStateKey);
    }
    if (isSO2HasilBaca() && currentType === 'hasil-baca') {
      const state = ensureActionState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-perhitungan';
      }
      SO2.applyFromHasilBaca(currentStateKey);
      SO2.applyFromSkPm(currentStateKey);
      saveCurrentTypeState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-baca';
      }
    }
    if (isNo2Like() && !isSO2Ambien() && currentType === 'hasil-baca') {
      const state = ensureActionState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-perhitungan';
      }
      fillNo2CalcFromHasilBaca(currentStateKey);
      if (isNH3() || isOX()) {
        fillGenericCalcFromHasilBaca(currentStateKey);
      }
      saveCurrentTypeState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-baca';
      }
    }
    if (isCurveMetalAas() && currentType === 'hasil-baca') {
      const state = ensureActionState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-perhitungan';
      }
      fillPbMetalAasCalcFromHasilBaca(currentStateKey);
      saveCurrentTypeState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-baca';
      }
    }
    if (isDebuPm() && currentType === 'hasil-baca') {
      const state = ensureActionState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-perhitungan';
      }
      fillDebuCalcFromHasilBaca(currentStateKey);
      saveCurrentTypeState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-baca';
      }
    }
    if (!isEmisiAcidGasLike() && !isNo2Like() && !isCurveMetalAas() && currentType === 'hasil-baca') {
      const state = ensureActionState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-perhitungan';
      }
      fillGenericCalcFromHasilBaca(currentStateKey);
      saveCurrentTypeState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-baca';
      }
    }
    if (isEmisiAcidGasLike() && currentType === 'sk-pm') {
      const state = ensureActionState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-perhitungan';
      }
      SO2.applyFromSkPm(currentStateKey);
      saveCurrentTypeState(currentStateKey);
      if (state) {
        state.currentType = 'sk-pm';
      }
    }
    if (currentType === 'sk-pm') {
      const state = ensureActionState(currentStateKey);
      if (state) {
        state.currentType = 'hasil-perhitungan';
      }
      fillCalcFromSkPmGeneric(currentStateKey);
      if (isCurveMetalAas()) {
        fillPbMetalAasCalcFromHasilBaca(currentStateKey);
      } else {
        fillNo2CalcFromHasilBaca(currentStateKey);
      }
      saveCurrentTypeState(currentStateKey);
      if (state) {
        state.currentType = 'sk-pm';
      }
    }
    if (isEmisiAcidGasLike() && currentType === 'hasil-perhitungan') {
      SO2.applyCalc(actionRows);
    }
    const saveResult = await saveDraftByType({ notify: true });
    if (saveResult?.status !== 'error') {
      const state = ensureActionState(currentStateKey);
      modalSessionCommitted = true;
      if (state) {
        modalSessionSnapshot = cloneTypesState(state.types);
        modalSessionType = state.currentType || currentType || 'sk-pm';
      }
    }
  });
})();
</script>
@endpush
