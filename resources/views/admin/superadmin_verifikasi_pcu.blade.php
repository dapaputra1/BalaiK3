@extends('layouts.app_admin')

@section('content_admin')
@php
  $orders = $orders ?? collect();
  $extraCategories = $extraCategories ?? [];
  $revisionOnly = !empty($revisionOnly);
  $isPcuView = auth()->user()?->role === 'pcu';
  $pengujianUploadMaxMb = (int) ($pengujianUploadMaxMb ?? 20);
  $pcuStageCounts = $orders->countBy(function ($order) {
    $stage = strtolower((string) ($order['pcu_verification_stage'] ?? ''));
    if (!in_array($stage, ['revisi', 'sesuai'], true)) {
      $stage = !empty($order['ready_for_bap']) ? 'sesuai' : 'revisi';
    }
    return $stage;
  });
  $pcuRevisiCount = (int) ($pcuStageCounts->get('revisi', 0));
  $pcuSesuaiCount = (int) ($pcuStageCounts->get('sesuai', 0));
@endphp

<style>
  .btn-primary {
    background-color: #0f2f53 !important;
    border-color: #0f2f53 !important;
  }
  .btn-primary:hover,
  .btn-primary:focus {
    background-color: #0b2340 !important;
    border-color: #0b2340 !important;
  }
  .text-bg-primary {
    background-color: #0f2f53 !important;
    color: #fff !important;
  }
  .btn-outline-primary {
    color: #0f2f53 !important;
    border-color: #0f2f53 !important;
  }
  .btn-outline-primary:hover,
  .btn-outline-primary:focus {
    background-color: #0f2f53 !important;
    color: #fff !important;
    border-color: #0f2f53 !important;
  }
  .btn-outline-primary.active,
  .btn-outline-primary:active {
    background-color: #0f2f53 !important;
    color: #fff !important;
    border-color: #0f2f53 !important;
  }
  .workflow-filter-count-badge {
    background-color: #dc3545 !important;
    color: #fff !important;
  }
  .table td {
    vertical-align: top;
  }
  #pengujianAccordion .accordion-body {
    padding: 18px 20px;
  }
  #pengujianAccordion [data-order-card] > .d-flex:first-child {
    margin-bottom: 12px !important;
  }
  #pengujianAccordion [data-order-card] .small.text-muted {
    font-size: 12px;
  }
  #pengujianAccordion [data-order-card] .btn.btn-sm {
    padding: 0.42rem 0.72rem;
    font-size: 12px;
    border-radius: 10px;
  }
  #pengujianAccordion .table-responsive[data-table-wrap] {
    border: 1px solid #e3ecf7;
    border-radius: 16px;
    padding: 10px 12px 12px;
    background: #fff;
  }
  #pengujianAccordion .table {
    font-size: 13px;
  }
  #pengujianAccordion .table > :not(caption) > * > * {
    padding: 8px 6px;
  }
  #pengujianAccordion .table thead th {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .01em;
    color: #26466a;
    text-transform: uppercase;
    white-space: nowrap;
  }
  #pengujianAccordion .table thead.table-light th,
  #pengujianAccordion .table .table-light th {
    background: #f6f9fd;
  }
  .req-switch-invalid {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
  }
  .auto-sesuai-switch .form-check-input:disabled {
    opacity: 1;
    cursor: not-allowed;
  }
  .doc-block {
    border: 1px dashed #c7dcf7;
    border-radius: 12px;
    padding: 10px;
    background: linear-gradient(180deg, #fbfdff 0%, #f3f8ff 100%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.75);
  }
  .doc-label {
    font-size: 12px;
    font-weight: 700;
    color: #123A63;
    margin-bottom: 2px;
  }
  .doc-row {
    min-height: 32px;
    display: flex;
    align-items: center;
  }
  .method-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 4px 10px;
    border: 1px solid #cfe0f5;
    border-radius: 999px;
    background: #f7fbff;
    color: #2d4f73;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.1;
  }
  .pcu-review-note {
    min-height: 38px;
    padding: 8px 10px;
    border: 1px solid #d7e5f6;
    border-radius: 10px;
    background: #f8fbff;
    color: #26466a;
    font-size: 12px;
    line-height: 1.45;
    white-space: normal;
    word-break: break-word;
  }
  .pcu-review-note.is-empty {
    color: #7a8ca3;
    font-style: italic;
  }
  .upload-row {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 8px 10px;
    border: 1px solid #d7e5f6;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(15, 47, 83, 0.05);
  }
  .upload-row-actions {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .upload-row-filename {
    flex: 1 1 auto;
    min-width: 0;
    padding: 0 2px;
    color: #244569;
    font-size: 12px;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .upload-row-tools {
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .upload-row-actions .form-control {
    min-height: 38px;
    border-radius: 10px;
    border-color: #c8d9ee;
    font-size: 12px;
    color: #244569;
    flex: 1 1 auto;
  }
  .upload-row-actions .form-control:focus {
    border-color: #9ebddd;
    box-shadow: 0 0 0 .15rem rgba(21, 64, 106, 0.12);
  }
  .upload-row-tools .btn {
    flex: 0 0 auto;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .upload-file-trigger,
  .existing-file-trigger {
    min-height: 34px;
    padding: 6px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
  }
  [data-existing-files] {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  [data-existing-file-row] {
    margin: 0;
  }
  [data-existing-file-row] [data-existing-file-name] {
    color: #123A63;
    font-size: 13px;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    min-width: 0;
  }
  .doc-upload-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding-top: 2px;
  }
  .doc-upload-toolbar .btn {
    border-radius: 10px;
    min-height: 34px;
    padding-inline: 10px;
    font-weight: 600;
    font-size: 12px;
  }
  .doc-upload-meta {
    margin-top: 2px;
    padding-left: 2px;
    font-size: 11px;
    line-height: 1.45;
    color: #5f7088 !important;
  }
  .btn-circle-sm {
    width: 24px;
    height: 24px;
    padding: 0;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .table-indirect th:nth-child(5),
  .table-indirect td:nth-child(5),
  .table-indirect th:nth-child(6),
  .table-indirect td:nth-child(6),
  .table-indirect th:nth-child(7),
  .table-indirect td:nth-child(7) {
    display: none;
  }
  .table-direct th:nth-child(4),
  .table-direct td:nth-child(4) {
    display: none;
  }
  .table-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 8px !important;
  }
  .table-section-title {
    font-size: 13px;
    font-weight: 700;
    color: #153d66;
  }
  .direct-param-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .table-direct tbody tr.direct-row td {
    border-top: none !important;
  }
  .table-direct tbody tr.direct-row-first td {
    border-top: 1px solid var(--bs-table-border-color) !important;
  }
  @if($revisionOnly)
  #pengujianAccordion .table-responsive[data-table-wrap] {
    overflow-x: hidden;
    overflow-y: visible;
    padding: 8px 8px 10px;
  }
  #pengujianAccordion .table {
    font-size: 11px;
  }
  #pengujianAccordion .table > :not(caption) > * > * {
    padding: 7px 8px;
  }
  #pengujianAccordion .table thead th {
    font-size: 9.5px;
    line-height: 1.2;
  }
  #pengujianAccordion .table-direct,
  #pengujianAccordion .table-indirect {
    table-layout: fixed;
    width: 100%;
    min-width: 0;
  }
  #pengujianAccordion .doc-block {
    padding: 9px 10px;
    border-radius: 10px;
    width: 100%;
  }
  #pengujianAccordion .doc-label {
    font-size: 10px;
    margin-bottom: 2px;
  }
  #pengujianAccordion .doc-row {
    min-height: 24px;
  }
  #pengujianAccordion .form-control.form-control-sm,
  #pengujianAccordion .form-select.form-select-sm {
    min-height: 31px;
    font-size: 10px;
  }
  #pengujianAccordion .upload-row {
    padding: 8px 9px;
    gap: 6px;
  }
  #pengujianAccordion .upload-row-actions {
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 8px;
  }
  #pengujianAccordion .upload-row-filename,
  #pengujianAccordion [data-existing-file-row] [data-existing-file-name] {
    font-size: 10px;
  }
  #pengujianAccordion .upload-row-filename {
    flex: 1 0 100%;
    width: 100%;
    padding: 0;
    white-space: normal;
    overflow: visible;
    text-overflow: clip;
  }
  #pengujianAccordion .doc-upload-toolbar .btn,
  #pengujianAccordion .upload-row-tools .btn,
  #pengujianAccordion .existing-file-trigger,
  #pengujianAccordion .upload-file-trigger {
    font-size: 10px;
  }
  #pengujianAccordion .doc-upload-toolbar {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    align-items: stretch;
  }
  #pengujianAccordion .doc-upload-toolbar .btn {
    width: 100%;
    justify-content: center;
    padding-inline: 8px;
  }
  #pengujianAccordion .pcu-review-note {
    min-height: 32px;
    padding: 8px 9px;
    font-size: 10px;
    line-height: 1.35;
  }
  #pengujianAccordion .method-chip {
    min-height: 22px;
    width: 100%;
    padding: 3px 6px;
    font-size: 9.5px;
    text-align: center;
    white-space: normal;
  }
  #pengujianAccordion .table-direct th,
  #pengujianAccordion .table-direct td,
  #pengujianAccordion .table-indirect th,
  #pengujianAccordion .table-indirect td {
    white-space: normal;
    word-break: normal;
    overflow-wrap: break-word;
  }
  #pengujianAccordion [data-doc-list] .doc-block,
  #pengujianAccordion [data-direct-doc-cell] .doc-block {
    min-width: 0;
  }
  #pengujianAccordion [data-param-docs] .doc-block,
  #pengujianAccordion [data-direct-param-cell] .doc-block {
    min-width: 0;
  }
  #pengujianAccordion [data-method-docs] .doc-block,
  #pengujianAccordion [data-direct-method-cell] .doc-block {
    min-width: 0;
  }
  #pengujianAccordion [data-method-docs] .doc-label,
  #pengujianAccordion [data-qty-docs] .doc-label,
  #pengujianAccordion [data-switch-docs] .doc-label,
  #pengujianAccordion [data-review-docs] .doc-label,
  #pengujianAccordion [data-note-docs] .doc-label,
  #pengujianAccordion [data-direct-method-cell] .doc-label,
  #pengujianAccordion [data-direct-lokasi-cell] .doc-label,
  #pengujianAccordion [data-direct-sesuai-cell] .doc-label,
  #pengujianAccordion [data-direct-review-cell] .doc-label,
  #pengujianAccordion [data-direct-note-cell] .doc-label {
    display: none;
  }
  #pengujianAccordion [data-method-docs] .doc-block,
  #pengujianAccordion [data-qty-docs] .doc-block,
  #pengujianAccordion [data-switch-docs] .doc-block,
  #pengujianAccordion [data-review-docs] .doc-block,
  #pengujianAccordion [data-note-docs] .doc-block,
  #pengujianAccordion [data-direct-method-cell] .doc-block,
  #pengujianAccordion [data-direct-lokasi-cell] .doc-block,
  #pengujianAccordion [data-direct-sesuai-cell] .doc-block,
  #pengujianAccordion [data-direct-review-cell] .doc-block,
  #pengujianAccordion [data-direct-note-cell] .doc-block {
    gap: 6px !important;
  }
  #pengujianAccordion [data-qty-docs] .doc-block {
    min-width: 0;
  }
  #pengujianAccordion [data-action-docs] .doc-block {
    min-width: 0;
  }
  #pengujianAccordion [data-switch-docs] .doc-block,
  #pengujianAccordion [data-review-docs] .doc-block,
  #pengujianAccordion [data-direct-sesuai-cell] .doc-block,
  #pengujianAccordion [data-direct-review-cell] .doc-block {
    min-width: 0;
  }
  #pengujianAccordion [data-review-docs] .doc-row,
  #pengujianAccordion [data-direct-review-cell] .doc-row {
    min-height: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  #pengujianAccordion [data-switch-docs] .doc-row,
  #pengujianAccordion [data-direct-sesuai-cell] .doc-row {
    min-height: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  #pengujianAccordion [data-param-docs] .doc-row,
  #pengujianAccordion [data-method-docs] .doc-row,
  #pengujianAccordion [data-qty-docs] .doc-row,
  #pengujianAccordion [data-action-docs] .doc-row {
    min-height: 36px;
    height: 36px;
    display: flex;
    align-items: center;
  }
  #pengujianAccordion [data-switch-docs] .form-check.doc-row,
  #pengujianAccordion [data-review-docs] .form-check.doc-row,
  #pengujianAccordion [data-direct-sesuai-cell] .form-check.doc-row,
  #pengujianAccordion [data-direct-review-cell] .form-check.doc-row {
    padding-left: 0;
    margin-bottom: 0;
  }
  #pengujianAccordion [data-switch-docs] .form-check.doc-row .form-check-input,
  #pengujianAccordion [data-review-docs] .form-check.doc-row .form-check-input,
  #pengujianAccordion [data-direct-sesuai-cell] .form-check.doc-row .form-check-input,
  #pengujianAccordion [data-direct-review-cell] .form-check.doc-row .form-check-input {
    float: none;
    margin-left: 0;
    margin-top: 0;
    transform: none;
    position: relative;
    top: 0;
  }
  #pengujianAccordion [data-review-docs] .form-check-input,
  #pengujianAccordion [data-direct-review-cell] .form-check-input {
    margin-top: 0;
  }
  #pengujianAccordion [data-note-docs] .doc-block,
  #pengujianAccordion [data-direct-note-cell] .doc-block {
    min-width: 0;
  }
  #pengujianAccordion [data-direct-lokasi-cell] .doc-block {
    min-width: 0;
  }
  #pengujianAccordion .table thead th {
    white-space: normal;
    line-height: 1.25;
  }
  #pengujianAccordion .pcu-review-note,
  #pengujianAccordion .upload-row-filename,
  #pengujianAccordion [data-existing-file-row] [data-existing-file-name],
  #pengujianAccordion .doc-upload-meta {
    white-space: normal;
    word-break: break-word;
    overflow-wrap: anywhere;
  }
  #pengujianAccordion .upload-row-actions {
    gap: 8px;
  }
  #pengujianAccordion .upload-row-tools {
    width: 100%;
    justify-content: flex-end;
    gap: 6px;
  }
  #pengujianAccordion .upload-row-tools .btn {
    width: 28px;
    height: 28px;
  }
  #pengujianAccordion .direct-param-list,
  #pengujianAccordion [data-doc-list],
  #pengujianAccordion [data-param-docs],
  #pengujianAccordion [data-method-docs],
  #pengujianAccordion [data-qty-docs],
  #pengujianAccordion [data-switch-docs],
  #pengujianAccordion [data-review-docs],
  #pengujianAccordion [data-note-docs] {
    gap: 8px !important;
  }
  #pengujianAccordion .btn-circle-sm {
    width: 22px;
    height: 22px;
  }
  @endif
  .param-add-divider {
    position: relative;
    display: flex;
    justify-content: center;
    margin-top: 8px;
    padding-top: 8px;
  }
  .param-add-divider::before {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    top: 50%;
    border-top: 1px dashed #c7dcf7;
    transform: translateY(-50%);
  }
  .param-add-divider .btn {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 9px;
    border-radius: 999px;
    background: #fff;
  }
  #pengujianAccordion .form-control-sm,
  #pengujianAccordion .form-select-sm {
    min-height: 34px;
    font-size: 12px;
    border-radius: 10px;
  }
  #pengujianAccordion .form-check-input {
    margin-top: 0;
     /* checkbox border hitam */
    border: 1px solid #000;
  }
  #pengujianAccordion [data-table-wrap="indirect"] [data-row-index],
  #pengujianAccordion [data-direct-doc-index] {
    font-size: 12px;
    font-weight: 600;
    color: #486581 !important;
  }
  .pengujian-arrival-time {
    font-size: 11px;
    line-height: 1.2;
    color: #6c757d;
    white-space: nowrap;
  }
  .pengujian-arrival-time i {
    font-size: 11px;
    margin-right: 4px;
  }
  .pcu-header-content {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 0;
    position: relative;
    padding-right: 200px;
  }
  .pcu-order-code {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.2;
    color: #fff;
  }
  .pcu-company-name {
    font-size: 13px;
    font-weight: 400;
    line-height: 1.2;
    color: rgba(255, 255, 255, 0.92);
    margin-top: 2px;
  }
  .pcu-meta-row {
    position: absolute;
    right: 34px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
  }
  .pcu-status-badge {
    font-size: 10px;
    line-height: 1;
    font-weight: 700;
    color: #fff;
    border-radius: 999px;
    padding: 4px 8px;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .pcu-status-badge.is-revisi {
    background: #dc3545;
  }
  .pcu-status-badge.is-sesuai {
    background: #198754;
  }
  #pengujianAccordion .accordion-button {
    background-color: #15406a;
    color: #fff;
  }
  #pengujianAccordion .accordion-button:not(.collapsed) {
    background-color: #15406a;
    color: #fff;
    box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.08);
  }
  #pengujianAccordion .accordion-button::after {
    filter: brightness(0) invert(1);
  }
  #pengujianAccordion .accordion-button .text-muted,
  #pengujianAccordion .accordion-button .pengujian-arrival-time,
  #pengujianAccordion .accordion-button .pengujian-arrival-time span,
  #pengujianAccordion .accordion-button .pengujian-arrival-time i {
    color: #fff !important;
  }
  .workflow-search-wrap {
    background: #eef2f7;
    border: 1px solid #d7e1ee;
    border-radius: 16px;
    padding: 10px;
    margin-bottom: 16px;
  }
  .workflow-search-wrap .input-group-text {
    background: #fff;
    border: 1px solid #b7c8de;
    border-right: 0;
    color: #6f88a8;
    border-radius: 14px 0 0 14px;
    padding-left: 14px;
    padding-right: 10px;
  }
  .workflow-search-wrap .form-control {
    border: 1px solid #b7c8de;
    border-left: 0;
    border-radius: 0 14px 14px 0;
    min-height: 42px;
    font-size: 13px;
    color: #345173;
  }
  .workflow-search-wrap .form-control:focus {
    box-shadow: none;
    border-color: #a7bed9;
  }
  .workflow-search-wrap .input-group:focus-within .input-group-text {
    border-color: #a7bed9;
  }
  .workflow-search-wrap .btn-reset-search {
    border: 1px solid #15406A;
    color: #15406A;
    background: #fff;
    border-radius: 14px;
    min-height: 42px;
    font-weight: 600;
    font-size: 13px;
  }
  .workflow-search-wrap .btn-reset-search:hover,
  .workflow-search-wrap .btn-reset-search:focus {
    background: #15406A;
    color: #fff;
  }
  .workflow-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 18px;
  }
  .revision-alert {
    border: 1px solid #f3c6cb;
    background: #fff5f6;
    color: #842029;
    border-radius: 14px;
    padding: 12px 14px;
  }
  .revision-alert .revision-list {
    margin: 8px 0 0;
    padding-left: 18px;
  }
  .revision-alert .revision-list li + li {
    margin-top: 4px;
  }
  .revision-badge {
    background: #dc3545;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 999px;
    padding: 4px 10px;
  }
  .camera-capture-shell {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    background: #0f172a;
    min-height: 280px;
  }
  .camera-capture-video,
  .camera-capture-preview {
    width: 100%;
    min-height: 280px;
    max-height: 62vh;
    object-fit: cover;
    display: block;
    background: #0f172a;
  }
  .camera-capture-preview.d-none,
  .camera-capture-video.d-none {
    display: none !important;
  }
  .camera-capture-hint {
    font-size: 12px;
    color: #5f7088;
  }
  @media (max-width: 576px) {
    .pcu-header-content {
      padding-right: 0;
    }
    .pcu-meta-row {
      position: static;
      transform: none;
      margin-top: 6px;
      align-items: flex-start;
    }
    #pengujianAccordion .accordion-body {
      padding: 14px 14px 16px;
    }
    #pengujianAccordion .table-responsive[data-table-wrap] {
      padding: 8px 8px 10px;
      border-radius: 14px;
    }
    .upload-row-actions {
      align-items: stretch;
    }
    .upload-row-tools {
      align-self: flex-end;
    }
    .doc-upload-toolbar {
      display: grid;
      grid-template-columns: 1fr 1fr;
    }
    .doc-upload-toolbar .btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>

@include('admin.partials.workflow_header', [
  'title' => $revisionOnly
    ? 'Alur Kerja - Verifikasi PCU'
    : 'Alur Kerja - Pengujian',
  'subtitle' => $revisionOnly
    ? 'Kelola tindak lanjut revisi dari verifikasi pengujian. Setelah perbaikan selesai, kirim ulang ke verifikasi pengujian untuk dicek kembali.'
    : 'Kelola lokasi, parameter, aksi, dan dokumen per perusahaan.',
  'total' => $orders->count(),
])

@php
  $cardPayloads = $orders->mapWithKeys(function ($order) {
      $permohonanId = (string) ($order['permohonan_id'] ?? '');
      if ($permohonanId === '') {
          return [];
      }

      return [
          $permohonanId => [
              'orderParams' => $order['parameter'] ?? [],
              'existing' => $order['existing'] ?? null,
              'detail' => $order['detail'] ?? [],
          ],
      ];
  })->all();
@endphp

<div class="workflow-search-wrap">
  <div class="row g-2 align-items-center">
    <div class="col-12 col-md-4">
      <div class="input-group input-group-sm">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control" placeholder="Cari berdasarkan kode pesanan" data-search-kode>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="input-group input-group-sm">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control" placeholder="Cari berdasarkan pelanggan" data-search-perusahaan>
      </div>
    </div>
    <div class="col-12 col-md-2">
      <button type="button" class="btn btn-reset-search w-100 btn-sm" data-search-reset><i class="bi bi-arrow-clockwise me-1"></i>Reset</button>
    </div>
  </div>
</div>
@if($revisionOnly)
  <div class="workflow-filter-row">
    <button type="button" class="btn btn-outline-primary btn-sm active" data-pcu-stage-filter="revisi">
      Revisi
      @if($pcuRevisiCount > 0)
        <span class="badge rounded-pill workflow-filter-count-badge ms-1">{{ $pcuRevisiCount }}</span>
      @endif
    </button>
    <button type="button" class="btn btn-outline-primary btn-sm" data-pcu-stage-filter="sesuai">
      Sesuai
      @if($pcuSesuaiCount > 0)
        <span class="badge rounded-pill workflow-filter-count-badge ms-1">{{ $pcuSesuaiCount }}</span>
      @endif
    </button>
  </div>
@endif

<div class="accordion" id="pengujianAccordion">
  @forelse($orders as $order)
  @php
    $routePrefix = auth()->user()?->role === 'pcu' ? 'pcu' : 'superadmin';
    $permohonanId = $order['permohonan_id'] ?? null;
    $orderParams = $order['parameter'] ?? [];
    $existing = $order['existing'] ?? null;
    $sptUrl = $order['spt_url'] ?? null;
    $detail = $order['detail'] ?? [];
    $jsonAttrFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    $orderParamsB64 = base64_encode(json_encode($orderParams, JSON_UNESCAPED_UNICODE) ?: '[]');
    $existingB64 = base64_encode(json_encode($existing, JSON_UNESCAPED_UNICODE) ?: 'null');
    $detailB64 = base64_encode(json_encode($detail, JSON_UNESCAPED_UNICODE) ?: '{}');
    $readyForBap = !empty($order['ready_for_bap']);
    $pcuVerificationStage = $order['pcu_verification_stage'] ?? ($readyForBap ? 'sesuai' : 'revisi');
    $pcuStatusLabel = strtolower((string) $pcuVerificationStage) === 'sesuai' ? 'Sesuai' : 'Revisi';
    $accordionHeadingId = 'pengujianHeading' . $permohonanId;
    $accordionCollapseId = 'pengujianCollapse' . $permohonanId;
    $masukAtUnix = (int) ($order['masuk_at_unix'] ?? 0);
  @endphp
  <div
    class="accordion-item border-0 shadow-sm rounded-4 mb-3 overflow-hidden"
    data-card
    data-kode="{{ strtolower($order['kode']) }}"
    data-perusahaan="{{ strtolower($order['perusahaan']) }}"
    data-permohonan-id="{{ $permohonanId }}"
    data-has-revision="{{ !empty($order['has_revision']) ? '1' : '0' }}"
    data-ready-for-bap="{{ $readyForBap ? '1' : '0' }}"
    data-pcu-stage="{{ strtolower($pcuVerificationStage) }}"
    data-order-params='@json($orderParams, $jsonAttrFlags)'
    data-order-params-b64="{{ $orderParamsB64 }}"
    data-existing='@json($existing, $jsonAttrFlags)'
    data-existing-b64="{{ $existingB64 }}"
    data-detail='@json($detail, $jsonAttrFlags)'
    data-detail-b64="{{ $detailB64 }}"
    data-save-url="{{ (!$readyForBap && $permohonanId) ? route($routePrefix . '.pengujian.draft', $permohonanId) : '' }}"
    data-submit-url="{{ $permohonanId ? ($readyForBap ? route($routePrefix . '.pengujian.forward-bap', $permohonanId) : route($routePrefix . '.pengujian.submit', $permohonanId)) : '' }}"
  >
    <h2 class="accordion-header" id="{{ $accordionHeadingId }}">
      <button
        class="accordion-button collapsed fw-semibold"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#{{ $accordionCollapseId }}"
        aria-expanded="false"
        aria-controls="{{ $accordionCollapseId }}"
      >
        <div class="pcu-header-content">
          <div class="pcu-order-code">{{ $order['kode'] }}</div>
          <div class="pcu-company-name">{{ $order['perusahaan'] }}</div>
          <div class="pcu-meta-row">
            <div class="pengujian-arrival-time">
              <i class="bi bi-clock"></i>
              <span data-relative-time data-time-unix="{{ $masukAtUnix ?: '' }}">-</span>
            </div>
            <div class="pcu-status-badge {{ strtolower((string) $pcuVerificationStage) === 'sesuai' ? 'is-sesuai' : 'is-revisi' }}">{{ $pcuStatusLabel }}</div>
          </div>
        </div>
      </button>
    </h2>
    <div
      id="{{ $accordionCollapseId }}"
      class="accordion-collapse collapse"
      aria-labelledby="{{ $accordionHeadingId }}"
      data-bs-parent="#pengujianAccordion"
    >
      <div class="accordion-body bg-white" data-order-card>
        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
          <div class="small text-muted">{{ $revisionOnly ? 'Ringkasan tindak lanjut verifikasi PCU.' : 'Kelola tabel pengujian untuk ' . $order['kode'] . '.' }}</div>
          <div class="d-flex gap-2 flex-wrap">
            @if(!empty($order['has_revision']))
              <span class="revision-badge">Revisi Penyelia</span>
            @endif
            @if($readyForBap)
              <span class="badge text-bg-success">Sesuai Penyelia</span>
            @endif
            @if($sptUrl)
              <a href="{{ $sptUrl }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                <i class="bi bi-file-earmark-text"></i> Lihat SPT TTD
              </a>
            @endif
            @unless($readyForBap)
              <button class="btn btn-outline-primary btn-sm" type="button" data-add-table="indirect" aria-label="Tambah Tabel Indirect" title="Tambah Tabel Indirect">
                <i class="bi bi-plus-square me-1"></i> Tabel Indirect
              </button>
              <button class="btn btn-outline-primary btn-sm" type="button" data-add-table="direct" aria-label="Tambah Tabel Direct" title="Tambah Tabel Direct">
                <i class="bi bi-plus-square me-1"></i> Tabel Direct
              </button>
            @endunless
            <!-- <button class="btn btn-outline-primary btn-sm" type="button" data-detail-open>Detail Permohonan</button> -->
          </div>
        </div>

        @if(!empty($order['has_revision']))
          <div class="revision-alert mb-3">
            <div class="fw-semibold">Catatan revisi dari penyelia</div>
            <div class="small">Perbaiki dokumen/parameter berikut sebelum dikirim ulang.</div>
            <ul class="revision-list small">
              @foreach(($order['revision_items'] ?? []) as $revision)
                <li>
                  <strong>{{ $revision['parameter'] ?? '-' }}</strong>
                  @if(!empty($revision['catatan']))
                    : {{ $revision['catatan'] }}
                  @endif
                </li>
              @endforeach
            </ul>
          </div>
        @endif

        @if($readyForBap)
          <div class="alert alert-success border-0 rounded-4 mb-3">
            <div class="fw-semibold">Data siap dilanjutkan ke BAP</div>
            <div class="small">Permohonan ini berasal dari alur sesuai sebelumnya dan dapat langsung diteruskan ke tahap BAP.</div>
          </div>
        @endif

        <div class="table-responsive mb-3 d-none" data-table-wrap="indirect">
          <div class="mb-2 table-section-header">
            <span class="table-section-title">Tabel Indirect</span>
            <button class="btn btn-outline-danger btn-sm" type="button" data-remove-table="indirect" aria-label="Hapus Tabel" title="Hapus Tabel">
              <i class="bi bi-trash me-1"></i> Tabel
            </button>
          </div>
          <table class="table table-sm align-middle mb-0 table-indirect">
            <thead class="table-light">
              <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 14%;">
                  <div class="d-flex align-items-center gap-2">
                    <span>Lokasi</span>
                    <button class="btn btn-outline-primary btn-sm btn-circle-sm" type="button" data-add-row onclick="window.Pengujian?.addRow?.(this)" aria-label="Tambah lokasi">
                      <i class="bi bi-plus"></i>
                    </button>
                  </div>
                </th>
                <th style="width: 17%;">Dokumen</th>
                <th style="width: 17%;">Parameter</th>
                <th style="width: 9%;">Metode</th>
                <th style="width: 7%;">Jumlah</th>
                <th style="width: 0;">Direct</th>
                <th style="width: 8%;">Sesuai</th>
                @if($revisionOnly)
                  <th style="width: 8%;">Verifikasi</th>
                  <th style="width: 18%;">Catatan Revisi</th>
                @endif
              </tr>
            </thead>
            <tbody data-order-table data-table-type="indirect">
              <tr data-row>
                <td class="text-muted" data-row-index>1</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <input type="text" class="form-control form-control-sm" value="Lokasi 1" data-location-input>
                    <button class="btn btn-outline-danger btn-sm" type="button" data-row-remove aria-label="Hapus lokasi">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                  <button class="btn btn-outline-secondary btn-sm mt-2" type="button" data-doc-add onclick="window.Pengujian?.addDoc?.(this)" aria-label="Tambah Dokumen" title="Tambah Dokumen">
                    <i class="bi bi-file-earmark-plus me-1"></i> Dokumen
                  </button>
                </td>
                <td>
                  <div class="d-flex flex-column gap-3" data-doc-list></div>
                  <div class="text-muted small" data-doc-empty>Belum ada dokumen.</div>
                </td>
                <td>
                  <div class="d-flex flex-column gap-3" data-param-docs></div>
                  <div class="text-muted small" data-param-empty>Belum ada dokumen.</div>
                </td>
                <td>
                  <div class="d-flex flex-column gap-3" data-method-docs></div>
                  <div class="text-muted small" data-method-empty>Belum ada dokumen.</div>
                </td>
                <td>
                  <div class="d-flex flex-column gap-3" data-qty-docs></div>
                  <div class="text-muted small" data-qty-empty>Belum ada dokumen.</div>
                </td>
                <td>
                  <div class="d-flex flex-column gap-3" data-action-docs></div>
                  <div class="text-muted small" data-action-empty>Belum ada dokumen.</div>
                </td>
                <td>
                  <div class="d-flex flex-column gap-3" data-switch-docs></div>
                  <div class="text-muted small" data-switch-empty>Belum ada dokumen.</div>
                </td>
                @if($revisionOnly)
                  <td>
                    <div class="d-flex flex-column gap-3" data-review-docs></div>
                    <div class="text-muted small" data-review-empty>Belum ada dokumen.</div>
                  </td>
                  <td>
                    <div class="d-flex flex-column gap-3" data-note-docs></div>
                    <div class="text-muted small" data-note-empty>Belum ada dokumen.</div>
                  </td>
                @endif
              </tr>
            </tbody>
          </table>
        </div>

        <div class="table-responsive mb-3 d-none" data-table-wrap="direct">
          <div class="mb-2 table-section-header">
            <span class="table-section-title">Tabel Direct</span>
            <button class="btn btn-outline-danger btn-sm" type="button" data-remove-table="direct" aria-label="Hapus Tabel" title="Hapus Tabel">
              <i class="bi bi-trash me-1"></i> Tabel
            </button>
          </div>
          <table class="table table-sm align-middle mb-0 table-direct">
            <thead class="table-light">
              <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 17%;">
                  <div class="d-flex align-items-center gap-2">
                    <span>Dokumen</span>
                    <button class="btn btn-outline-primary btn-sm btn-circle-sm" type="button" data-add-direct-doc aria-label="Tambah dokumen">
                      <i class="bi bi-plus"></i>
                    </button>
                  </div>
                </th>
                <th style="width: 17%;">Parameter</th>
                <th style="width: 10%;">Metode</th>
                <th style="width: 16%;">
                  <div class="d-flex align-items-center gap-2">
                    <span>Lokasi</span>
                  </div>
                </th>
                <th style="width: 8%;">Sesuai</th>
                @if($revisionOnly)
                  <th style="width: 8%;">Verifikasi</th>
                  <th style="width: 19%;">Catatan Revisi</th>
                @endif
              </tr>
            </thead>
            <tbody data-order-table data-table-type="direct" data-direct-table>
              <tr data-direct-empty>
                <td colspan="{{ $revisionOnly ? 8 : 6 }}" class="text-muted small">Belum ada dokumen.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-end align-items-center mt-3 gap-2">
          @unless($readyForBap)
            <button class="btn btn-outline-secondary btn-sm" type="button" data-action-save>Simpan Draft</button>
          @endunless
          <button class="btn btn-primary btn-sm" type="button" data-action-submit>
            {{ $readyForBap ? 'Lanjutkan Buat BAP' : (!empty($order['has_revision']) ? 'Kirim Ulang Setelah Revisi' : 'Kirim ke Verifikasi Pengujian') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="text-center text-muted py-4">Belum ada permintaan pengujian.</div>
  @endforelse
</div>

<div class="text-center text-muted d-none mt-3" data-search-empty>Tidak ada order yang cocok.</div>
<div data-extra-categories='@json($extraCategories)'></div>

<div class="modal fade" id="extraParamModal" tabindex="-1" aria-labelledby="extraParamModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="extraParamModalLabel">Tambahan Parameter Lain</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label small text-muted mb-1">Kategori</label>
          <select class="form-select form-select-sm" data-extra-category></select>
        </div>
        <div class="mb-2">
          <label class="form-label small text-muted mb-1">Parameter</label>
          <select class="form-select form-select-sm" data-extra-param></select>
        </div>
        <div class="text-muted small">Parameter tambahan akan otomatis menandai "Sesuai" menjadi merah.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary btn-sm" data-extra-save>Pilih Parameter</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="detailPermohonanModal" tabindex="-1" aria-labelledby="detailPermohonanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="detailPermohonanModalLabel">Detail Permohonan</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <div class="text-muted small">Kode</div>
            <div class="fw-semibold" data-detail-kode>-</div>
          </div>
          <div class="col-12 col-md-6">
            <div class="text-muted small">Perusahaan</div>
            <div class="fw-semibold" data-detail-perusahaan>-</div>
          </div>
          <div class="col-12">
            <div class="text-muted small">Alamat</div>
            <div data-detail-alamat>-</div>
          </div>
          <div class="col-12 col-md-6">
            <div class="text-muted small">Lokasi Pengujian</div>
            <div data-detail-lokasi>-</div>
          </div>
          <div class="col-12 col-md-6">
            <div class="text-muted small">Jadwal</div>
            <div data-detail-jadwal>-</div>
          </div>
          <div class="col-12">
            <div class="text-muted small">Catatan Sampling</div>
            <div data-detail-catatan>-</div>
          </div>
          <div class="col-12">
            <div class="text-muted small">PCU Bertugas</div>
            <div data-detail-pcu>-</div>
          </div>
          <div class="col-12">
            <div class="text-muted small mb-2">Parameter (ACC Penawaran)</div>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Parameter</th>
                  </tr>
                </thead>
                <tbody data-detail-params></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cameraCaptureModal" tabindex="-1" aria-labelledby="cameraCaptureModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h6 class="modal-title" id="cameraCaptureModalLabel">Foto Dokumen</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="camera-capture-shell mb-3">
          <video class="camera-capture-video" data-camera-video autoplay playsinline muted></video>
          <img class="camera-capture-preview d-none" data-camera-preview alt="Hasil foto dokumen">
        </div>
        <canvas class="d-none" data-camera-canvas></canvas>
        <div class="small text-muted" data-camera-status>Posisikan dokumen di depan kamera, lalu ambil foto.</div>
        <div class="camera-capture-hint mt-1">Gunakan posisi landscape atau tegakkan dokumen agar teks lebih jelas.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-outline-secondary btn-sm d-none" data-camera-retake>Foto Ulang</button>
        <button type="button" class="btn btn-primary btn-sm" data-camera-capture>Ambil Foto</button>
        <button type="button" class="btn btn-primary btn-sm d-none" data-camera-use>Gunakan Foto</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
(() => {
  const IS_REVISION_VIEW = @json($revisionOnly);
  const CARD_PAYLOADS = {!! \Illuminate\Support\Js::from($cardPayloads) !!};
  const kodeInput = document.querySelector('[data-search-kode]');
  const perusahaanInput = document.querySelector('[data-search-perusahaan]');
  const resetBtn = document.querySelector('[data-search-reset]');
  const pcuStageButtons = document.querySelectorAll('[data-pcu-stage-filter]');
  const cards = document.querySelectorAll('[data-card]');
  const emptyState = document.querySelector('[data-search-empty]');
  const detailModalEl = document.getElementById('detailPermohonanModal');
  const detailModal = detailModalEl && window.bootstrap ? new window.bootstrap.Modal(detailModalEl) : null;
  let activePcuStage = document.querySelector('[data-pcu-stage-filter].active')?.getAttribute('data-pcu-stage-filter') || '';

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

  const initSummaryReviewSwitches = () => {
    document.querySelectorAll('[data-summary-review-switch]').forEach((input) => {
      if (input.dataset.summaryBound === '1') {
        input.classList.toggle('req-switch-invalid', !input.checked);
        return;
      }

      const syncState = () => {
        input.classList.toggle('req-switch-invalid', !input.checked);
      };

      input.dataset.summaryBound = '1';
      input.addEventListener('change', syncState);
      syncState();
    });
  };

  const decodeJsonAttribute = (raw) => {
    return String(raw || '')
      .replace(/^\uFEFF/, '')
      .replace(/&quot;/g, '"')
      .replace(/&#34;/g, '"')
      .replace(/&#039;/g, "'")
      .replace(/&apos;/g, "'")
      .replace(/&amp;/g, '&')
      .trim();
  };

  const decodeBase64Utf8 = (raw) => {
    const normalized = String(raw || '').trim();
    if (!normalized) return '';
    try {
      const binary = window.atob(normalized);
      const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
      return new TextDecoder('utf-8').decode(bytes);
    } catch (error) {
      return '';
    }
  };

  const parseJsonAttribute = (raw, fallback = null) => {
    const normalized = decodeJsonAttribute(raw);
    if (!normalized || normalized === 'null') return fallback;
    try {
      return JSON.parse(normalized);
    } catch (error) {
      return fallback;
    }
  };

  const parseCardJson = (card, attrName, fallback = null) => {
    const permohonanId = String(card?.getAttribute('data-permohonan-id') || '');
    const payload = permohonanId ? (CARD_PAYLOADS?.[permohonanId] || null) : null;
    const payloadKeyMap = {
      'data-order-params': 'orderParams',
      'data-existing': 'existing',
      'data-detail': 'detail',
    };
    const payloadKey = payloadKeyMap[attrName] || '';
    if (payload && payloadKey && Object.prototype.hasOwnProperty.call(payload, payloadKey)) {
      const payloadValue = payload[payloadKey];
      if (payloadValue !== undefined) {
        return payloadValue ?? fallback;
      }
    }

    const encoded = card?.getAttribute(`${attrName}-b64`) || '';
    const decoded = decodeBase64Utf8(encoded);
    if (decoded) {
      try {
        return JSON.parse(decoded);
      } catch (error) {
        // Fall through to raw attribute parsing.
      }
    }
    return parseJsonAttribute(card?.getAttribute(attrName), fallback);
  };

  const filterCards = () => {
      const kodeVal = (kodeInput?.value || '').toLowerCase().trim();
      const perusahaanVal = (perusahaanInput?.value || '').toLowerCase().trim();
      let visible = 0;
  
      cards.forEach((card) => {
        const kode = card.getAttribute('data-kode') || '';
        const perusahaan = card.getAttribute('data-perusahaan') || '';
        const pcuStage = (card.getAttribute('data-pcu-stage') || '').toLowerCase().trim();
        const matchKode = !kodeVal || kode.includes(kodeVal);
        const matchPerusahaan = !perusahaanVal || perusahaan.includes(perusahaanVal);
        const matchPcuStage = !activePcuStage || pcuStage === activePcuStage;
        const show = matchKode && matchPerusahaan && matchPcuStage;
        card.classList.toggle('d-none', !show);
        if (show) visible += 1;
      });

    if (emptyState) {
      const hasCards = cards.length > 0;
      emptyState.classList.toggle('d-none', !hasCards || visible > 0);
    }
  };

  kodeInput?.addEventListener('input', filterCards);
  perusahaanInput?.addEventListener('input', filterCards);
    resetBtn?.addEventListener('click', () => {
      if (kodeInput) kodeInput.value = '';
      if (perusahaanInput) perusahaanInput.value = '';
      filterCards();
    });

    pcuStageButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        pcuStageButtons.forEach((item) => item.classList.remove('active'));
        btn.classList.add('active');
        activePcuStage = btn.getAttribute('data-pcu-stage-filter') || '';
        filterCards();
      });
    });

  filterCards();
  refreshRelativeTimes();
  initSummaryReviewSwitches();

  document.querySelectorAll('[data-detail-open]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const card = btn.closest('[data-card]');
      if (!card || !detailModalEl) return;
      const detail = parseCardJson(card, 'data-detail', {}) || {};

      detailModalEl.querySelector('[data-detail-kode]').textContent = detail.kode || '-';
      detailModalEl.querySelector('[data-detail-perusahaan]').textContent = detail.perusahaan || '-';
      detailModalEl.querySelector('[data-detail-alamat]').textContent = detail.alamat || '-';
      detailModalEl.querySelector('[data-detail-lokasi]').textContent = detail.lokasi || '-';
      const jadwal = `${detail.jadwal_mulai || '-'} s/d ${detail.jadwal_selesai || '-'}`;
      detailModalEl.querySelector('[data-detail-jadwal]').textContent = jadwal;
      detailModalEl.querySelector('[data-detail-catatan]').textContent = detail.catatan_sampling || '-';
      const pcuNames = Array.isArray(detail.pcu) ? detail.pcu : [];
      detailModalEl.querySelector('[data-detail-pcu]').textContent = pcuNames.length ? pcuNames.join(', ') : '-';

      const tbody = detailModalEl.querySelector('[data-detail-params]');
      const params = Array.isArray(detail.parameter) ? detail.parameter : [];
      if (tbody) {
        if (!params.length) {
          tbody.innerHTML = '<tr><td class="text-muted">Belum ada parameter.</td></tr>';
        } else {
          tbody.innerHTML = params.map((row) => `
            <tr>
              <td>${row.name || '-'}</td>
            </tr>
          `).join('');
        }
      }

      detailModal?.show();
    });
  });
})();
</script>
<script>
(() => {
  const IS_REVISION_VIEW = @json($revisionOnly);
  const CARD_PAYLOADS = {!! \Illuminate\Support\Js::from($cardPayloads) !!};
  const PENGUJIAN_UPLOAD_ACCEPT = '.pdf,.doc,.docx,.jpg,.jpeg,.png';
  const PENGUJIAN_UPLOAD_IMAGE_ACCEPT = '.jpg,.jpeg,.png,image/*';
  const PENGUJIAN_UPLOAD_TYPES_LABEL = 'PDF, DOC, DOCX, JPG, JPEG, PNG';
  const PENGUJIAN_UPLOAD_MAX_MB = {{ $pengujianUploadMaxMb }};
  const PENGUJIAN_UPLOAD_MAX_BYTES = PENGUJIAN_UPLOAD_MAX_MB * 1024 * 1024;
  const EXTRA_OPTION = '__extra__';
  const extraDataEl = document.querySelector('[data-extra-categories]');
  let extraCategories = [];
  try {
    extraCategories = JSON.parse(extraDataEl?.getAttribute('data-extra-categories') || '[]');
  } catch (error) {
    extraCategories = [];
  }

  const decodeJsonAttribute = (raw) => {
    return String(raw || '')
      .replace(/^\uFEFF/, '')
      .replace(/&quot;/g, '"')
      .replace(/&#34;/g, '"')
      .replace(/&#039;/g, "'")
      .replace(/&apos;/g, "'")
      .replace(/&amp;/g, '&')
      .trim();
  };

  const decodeBase64Utf8 = (raw) => {
    const normalized = String(raw || '').trim();
    if (!normalized) return '';
    try {
      const binary = window.atob(normalized);
      const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
      return new TextDecoder('utf-8').decode(bytes);
    } catch (error) {
      return '';
    }
  };

  const parseJsonAttribute = (raw, fallback = null) => {
    const normalized = decodeJsonAttribute(raw);
    if (!normalized || normalized === 'null') return fallback;
    try {
      return JSON.parse(normalized);
    } catch (error) {
      return fallback;
    }
  };

  const parseCardJson = (card, attrName, fallback = null) => {
    const permohonanId = String(card?.getAttribute('data-permohonan-id') || '');
    const payload = permohonanId ? (CARD_PAYLOADS?.[permohonanId] || null) : null;
    const payloadKeyMap = {
      'data-order-params': 'orderParams',
      'data-existing': 'existing',
      'data-detail': 'detail',
    };
    const payloadKey = payloadKeyMap[attrName] || '';
    if (payload && payloadKey && Object.prototype.hasOwnProperty.call(payload, payloadKey)) {
      const payloadValue = payload[payloadKey];
      if (payloadValue !== undefined) {
        return payloadValue ?? fallback;
      }
    }

    const encoded = card?.getAttribute(`${attrName}-b64`) || '';
    const decoded = decodeBase64Utf8(encoded);
    if (decoded) {
      try {
        return JSON.parse(decoded);
      } catch (error) {
        // Fall through to raw attribute parsing.
      }
    }

    return parseJsonAttribute(card?.getAttribute(attrName), fallback);
  };

  const extraModalEl = document.getElementById('extraParamModal');
  const extraCategorySelect = extraModalEl?.querySelector('[data-extra-category]');
  const extraParamSelect = extraModalEl?.querySelector('[data-extra-param]');
  const extraSaveBtn = extraModalEl?.querySelector('[data-extra-save]');
  const extraModal = extraModalEl && window.bootstrap ? new window.bootstrap.Modal(extraModalEl) : null;
  const cameraCaptureModalEl = document.getElementById('cameraCaptureModal');
  const cameraCaptureModal = cameraCaptureModalEl && window.bootstrap ? new window.bootstrap.Modal(cameraCaptureModalEl) : null;
  const cameraVideoEl = cameraCaptureModalEl?.querySelector('[data-camera-video]');
  const cameraPreviewEl = cameraCaptureModalEl?.querySelector('[data-camera-preview]');
  const cameraCanvasEl = cameraCaptureModalEl?.querySelector('[data-camera-canvas]');
  const cameraStatusEl = cameraCaptureModalEl?.querySelector('[data-camera-status]');
  const cameraCaptureBtn = cameraCaptureModalEl?.querySelector('[data-camera-capture]');
  const cameraRetakeBtn = cameraCaptureModalEl?.querySelector('[data-camera-retake]');
  const cameraUseBtn = cameraCaptureModalEl?.querySelector('[data-camera-use]');
  let pendingSelect = null;
  let pendingPrevValue = '';
  let cameraStream = null;
  let cameraObjectUrl = '';
  let cameraCapturedBlob = null;
  let cameraPendingInput = null;
  let cameraPendingRow = null;
  let cameraPendingAfterCapture = null;
  let cameraPendingAfterCancel = null;

  const escapeHtml = (value) => {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  };

  const formatParamLabel = (param) => {
    const name = param?.name ? String(param.name) : '';
    const category = param?.category ? String(param.category) : '';
    const baseLabel = category && name ? `${category} - ${name}` : (name || category || '');
    const qty = Number.parseInt(String(param?.qty ?? ''), 10);
    if (Number.isFinite(qty) && qty > 0) {
      return `${baseLabel} (qty : ${qty})`;
    }
    return baseLabel;
  };

  const getCategoryOptions = () => {
    if (!extraCategories.length) {
      return '<option value="" selected disabled>Tidak ada kategori</option>';
    }
    return extraCategories.map((category) => {
      return `<option value="${category.id}">${escapeHtml(category.short_code || category.name || '')}</option>`;
    }).join('');
  };

  const getParamOptions = (categoryId) => {
    const category = extraCategories.find((item) => String(item.id) === String(categoryId));
    const params = category?.parameters || [];
    if (!params.length) {
      return '<option value="" selected disabled>Tidak ada parameter</option>';
    }
    return params.map((param) => {
      const label = escapeHtml(formatParamLabel({ name: param.name, category: category?.short_code || category?.name || '' }));
      return `<option value="${escapeHtml(param.id)}">${label}</option>`;
    }).join('');
  };

  const openExtraModal = (select) => {
    if (!extraModal || !extraCategorySelect || !extraParamSelect) return;
    pendingSelect = select;
    pendingPrevValue = select?.dataset.prevValue || '';
    extraCategorySelect.innerHTML = getCategoryOptions();
    const selectedCategory = extraCategorySelect.value;
    extraParamSelect.innerHTML = getParamOptions(selectedCategory);
    extraModal.show();
  };

  if (extraCategorySelect && extraParamSelect) {
    extraCategorySelect.addEventListener('change', () => {
      extraParamSelect.innerHTML = getParamOptions(extraCategorySelect.value);
    });
  }

  if (extraSaveBtn) {
    extraSaveBtn.addEventListener('click', () => {
      if (!pendingSelect || !extraParamSelect || !extraParamSelect.value) return;
      const selectedOption = extraParamSelect.options[extraParamSelect.selectedIndex];
      pendingSelect.__applySelection?.({
        id: extraParamSelect.value,
        name: selectedOption?.textContent || '',
      });
      extraModal?.hide();
    });
  }

  if (extraModalEl) {
    extraModalEl.addEventListener('hidden.bs.modal', () => {
      if (!pendingSelect) return;
      if (pendingSelect.value === EXTRA_OPTION) {
        pendingSelect.value = pendingPrevValue || '';
        pendingSelect.__applySelection?.(pendingSelect.value);
      }
      pendingSelect = null;
      pendingPrevValue = '';
    });
  }

  const buildOptions = (params) => {
    const base = params.map((param) => {
      const value = escapeHtml(param.id);
      const label = escapeHtml(formatParamLabel(param));
      return `<option value="${value}">${label}</option>`;
    }).join('');
    return `${base}<option value="${EXTRA_OPTION}" style="background-color:#15406A;color:#fff;">Tambahan Parameter Lain</option>`;
  };

  const buildRow = (params, index) => {
    return `
      <tr data-row>
        <td class="text-muted" data-row-index>${index}</td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <input type="text" class="form-control form-control-sm" value="Lokasi ${index}" data-location-input>
            <button class="btn btn-outline-danger btn-sm" type="button" data-row-remove aria-label="Hapus lokasi">
              <i class="bi bi-trash"></i>
            </button>
          </div>
                  <button class="btn btn-outline-secondary btn-sm mt-2" type="button" data-doc-add onclick="window.Pengujian?.addDoc?.(this)" aria-label="Tambah Dokumen" title="Tambah Dokumen">
                    <i class="bi bi-file-earmark-plus me-1"></i> Dokumen
                  </button>
        </td>
        <td>
          <div class="d-flex flex-column gap-3" data-doc-list></div>
          <div class="text-muted small" data-doc-empty>Belum ada dokumen.</div>
        </td>
        <td>
          <div class="d-flex flex-column gap-3" data-param-docs></div>
          <div class="text-muted small" data-param-empty>Belum ada dokumen.</div>
        </td>
        <td>
          <div class="d-flex flex-column gap-3" data-method-docs></div>
          <div class="text-muted small" data-method-empty>Belum ada dokumen.</div>
        </td>
        <td>
          <div class="d-flex flex-column gap-3" data-qty-docs></div>
          <div class="text-muted small" data-qty-empty>Belum ada dokumen.</div>
        </td>
        <td>
          <div class="d-flex flex-column gap-3" data-action-docs></div>
          <div class="text-muted small" data-action-empty>Belum ada dokumen.</div>
        </td>
        <td>
          <div class="d-flex flex-column gap-3" data-switch-docs></div>
          <div class="text-muted small" data-switch-empty>Belum ada dokumen.</div>
        </td>
        ${IS_REVISION_VIEW ? `
        <td>
          <div class="d-flex flex-column gap-3" data-review-docs></div>
          <div class="text-muted small" data-review-empty>Belum ada dokumen.</div>
        </td>
        <td>
          <div class="d-flex flex-column gap-3" data-note-docs></div>
          <div class="text-muted small" data-note-empty>Belum ada dokumen.</div>
        </td>` : ''}
      </tr>
    `;
  };

  const updateRowIndexes = (tbody) => {
    tbody.querySelectorAll('[data-row]').forEach((row, index) => {
      const cell = row.querySelector('[data-row-index]');
      if (cell) {
        cell.textContent = String(index + 1);
      }
      const locationInput = row.querySelector('[data-location-input]');
      if (locationInput && !locationInput.value.trim()) {
        locationInput.value = `Lokasi ${index + 1}`;
      }
    });
  };

  const removeRow = (btn) => {
    const row = btn?.closest('[data-row]');
    const tbody = row?.closest('tbody');
    if (!row || !tbody) return;
    const rows = Array.from(tbody.querySelectorAll('[data-row]'));
    if (rows.length <= 1) {
      if (window.Swal) {
        Swal.fire({
          icon: 'warning',
          title: 'Tidak bisa dihapus',
          text: 'Minimal harus ada 1 lokasi.',
        });
      } else {
        alert('Minimal harus ada 1 lokasi.');
      }
      return;
    }
    row.remove();
    updateRowIndexes(tbody);
  };

  const setEmptyVisibility = (container, emptyEl) => {
    const hasItems = container.children.length > 0;
    if (emptyEl) {
      emptyEl.classList.toggle('d-none', hasItems);
    }
  };

  const updateParamOptions = (contextEl) => {
    const card = contextEl?.closest?.('[data-card]') || (contextEl?.matches?.('[data-card]') ? contextEl : null);
    if (!card) return;

    const visibleWraps = Array.from(card.querySelectorAll('[data-table-wrap]'))
      .filter((wrap) => !wrap.classList.contains('d-none'));

    const selects = visibleWraps.flatMap((wrap) =>
      Array.from(wrap.querySelectorAll('select[data-param-select]'))
    );

    const getBaseParams = (select) => {
      return Array.isArray(select?.__baseParams) ? select.__baseParams : [];
    };

    const getParamMeta = (select, paramId) => {
      return getBaseParams(select).find((param) => String(param.id) === String(paramId)) || null;
    };

    const getSelectUsage = (select) => {
      if (!select || !select.value || select.value === EXTRA_OPTION) return 0;

      const directRow = select.closest('tr[data-direct-group]');
      if (directRow) {
        const locationCount = directRow.querySelectorAll('[data-direct-location-row]').length;
        return Math.max(1, locationCount || 0);
      }

      const row = select.closest('[data-row]');
      const docId = select.dataset.docId || '';
      const rowId = select.dataset.paramRowId || '';
      const qtyInput = row?.querySelector(
        `[data-qty-docs] [data-doc-id="${docId}"][data-param-row-id="${rowId}"] input`
      );
      const qtyValue = Number.parseInt(String(qtyInput?.value || '1'), 10);
      return Number.isFinite(qtyValue) && qtyValue > 0 ? qtyValue : 1;
    };

    const getAvailableQty = (targetSelect, paramId) => {
      const meta = getParamMeta(targetSelect, paramId);
      const totalQty = Number.parseInt(String(meta?.qty ?? '0'), 10);
      if (!Number.isFinite(totalQty) || totalQty <= 0) {
        return 0;
      }

      let usedByOthers = 0;
      selects.forEach((select) => {
        if (select === targetSelect) return;
        if (String(select.value || '') !== String(paramId)) return;
        usedByOthers += getSelectUsage(select);
      });

      return Math.max(0, totalQty - usedByOthers);
    };

    selects.forEach((select) => {
      const currentValue = String(select.value || '');
      const currentOption = select.options[select.selectedIndex] || null;
      const currentLabel = currentOption?.textContent || '';
      const customValue = String(select.dataset.customValue || '');
      const customLabel = select.dataset.customLabel || '';
      const params = getBaseParams(select);

      const options = [
        '<option value="" disabled>Pilih parameter</option>',
      ];

      params.forEach((param) => {
        const value = String(param.id);
        const availableQty = getAvailableQty(select, value);
        const isCurrent = currentValue === value;
        if (!isCurrent && availableQty <= 0) {
          return;
        }

        const optionLabel = (isCurrent && customValue === value && customLabel)
          ? customLabel
          : formatParamLabel({
              ...param,
              qty: availableQty,
            });
        const label = escapeHtml(optionLabel);

        options.push(`<option value="${escapeHtml(value)}">${label}</option>`);
      });

      if (
        currentValue &&
        currentValue !== EXTRA_OPTION &&
        !params.some((param) => String(param.id) === currentValue)
      ) {
        options.push(`<option value="${escapeHtml(currentValue)}">${escapeHtml(customLabel || currentLabel || currentValue)}</option>`);
      }

      options.push(`<option value="${EXTRA_OPTION}" style="background-color:#15406A;color:#fff;">Tambahan Parameter Lain</option>`);

      select.innerHTML = options.join('');

      if (currentValue) {
        select.value = currentValue;
      } else {
        select.value = '';
      }
    });
  };

  const createParamRow = (params, rowId) => {
    const wrap = document.createElement('div');
    wrap.className = 'd-flex align-items-center gap-2 doc-row';
    wrap.dataset.paramRowId = rowId;

    const select = document.createElement('select');
    select.className = 'form-select form-select-sm';
    select.dataset.paramSelect = 'true';
    select.innerHTML = `<option value="" selected disabled>Pilih parameter</option>${buildOptions(params)}`;

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'btn btn-outline-danger btn-sm';
    removeBtn.innerHTML = '<i class="bi bi-trash"></i>';

    wrap.appendChild(select);
    wrap.appendChild(removeBtn);

    return { wrap, select, removeBtn };
  };

  const createQtyInput = (rowId, options = {}) => {
    const {
      min = '1',
      defaultValue = '1',
      placeholder = '',
    } = options;
    const wrap = document.createElement('div');
    wrap.className = 'doc-row';
    wrap.dataset.paramRowId = rowId;
    const input = document.createElement('input');
    input.type = 'number';
    input.min = String(min);
    input.value = defaultValue;
    if (placeholder) {
      input.placeholder = placeholder;
    }
    input.className = 'form-control form-control-sm';
    wrap.appendChild(input);
    return wrap;
  };

  const createActionCheckbox = (rowId) => {
    const wrap = document.createElement('div');
    wrap.className = 'form-check form-switch m-0 doc-row d-flex justify-content-center';
    wrap.dataset.paramRowId = rowId;
    wrap.innerHTML = '<input class="form-check-input" type="checkbox">';
    return wrap;
  };

  const createReqSwitch = (rowId, options = {}) => {
    const {
      disabled = true,
      automated = true,
    } = options;
    const wrap = document.createElement('div');
    wrap.className = 'form-check form-switch m-0 doc-row d-flex justify-content-center';
    wrap.dataset.paramRowId = rowId;
    if (automated) {
      wrap.classList.add('auto-sesuai-switch');
      wrap.title = 'Status sesuai dihitung otomatis dari perbandingan penawaran dan pengujian.';
    }
    const input = document.createElement('input');
    input.className = 'form-check-input';
    input.type = 'checkbox';
    input.checked = true;
    input.disabled = disabled;
    if (automated) {
      input.setAttribute('aria-label', 'Status sesuai dihitung otomatis');
    }
    wrap.appendChild(input);

    const setState = () => {
      input.classList.toggle('req-switch-invalid', !input.checked);
    };
    setState();
    input.addEventListener('change', setState);

    return wrap;
  };

  const createMethodIndicator = (rowId, label = 'Indirect') => {
    const wrap = document.createElement('div');
    wrap.className = 'doc-row d-flex justify-content-center';
    wrap.dataset.paramRowId = rowId;
    wrap.innerHTML = `<span class="method-chip">${label}</span>`;
    return wrap;
  };

  const applyReviewSwitchState = (wrap, state = '') => {
    const input = wrap?.querySelector('input');
    if (!input) return;
    const normalizedState = String(state || '').trim().toLowerCase();
    if (normalizedState === 'revisi') {
      input.checked = false;
      input.disabled = false;
      input.dataset.reviewStatus = 'revisi';
    } else {
      input.checked = true;
      input.disabled = normalizedState === 'sesuai';
      input.dataset.reviewStatus = normalizedState === 'sesuai' ? 'sesuai' : '';
    }
    input.classList.toggle('req-switch-invalid', !input.checked);
  };

  const createReviewSwitch = (rowId, state = '') => {
    const wrap = createReqSwitch(rowId, { disabled: false, automated: false });
    wrap.dataset.reviewParamRow = rowId;
    const input = wrap.querySelector('input');
    applyReviewSwitchState(wrap, state);
    input?.addEventListener('change', () => {
      if (!input || input.disabled) return;
      input.dataset.reviewStatus = input.checked ? 'sesuai' : 'revisi';
      input.classList.toggle('req-switch-invalid', !input.checked);
    });
    return wrap;
  };

  const createReviewNote = (rowId, note = '') => {
    const wrap = document.createElement('div');
    wrap.className = 'doc-row pcu-review-note';
    wrap.dataset.paramRowId = rowId;
    const value = String(note || '').trim();
    if (value) {
      wrap.textContent = value;
      wrap.title = value;
    } else {
      wrap.textContent = 'Tidak ada catatan revisi';
      wrap.classList.add('is-empty');
    }
    return wrap;
  };

  const createDocBlock = (docId, label) => {
    const block = document.createElement('div');
    block.className = 'd-flex flex-column gap-2 doc-block';
    block.dataset.docId = docId;
    block.innerHTML = `<div class="doc-label">${label}</div>`;
    return block;
  };

  const createParamAddControl = (buttonAttr) => {
    const wrap = document.createElement('div');
    wrap.className = 'param-add-divider';
    wrap.dataset.paramAddWrap = 'true';
    wrap.innerHTML = `
      <button class="btn btn-outline-primary btn-sm" type="button" ${buttonAttr} aria-label="Tambah Parameter" title="Tambah Parameter">
        <i class="bi bi-plus"></i>
        <span>Tambah Parameter</span>
      </button>
    `;
    return wrap;
  };

  const getFileExtension = (filename = '') => {
    const parts = String(filename).toLowerCase().split('.');
    return parts.length > 1 ? parts.pop() : '';
  };

  const validatePengujianFiles = async (files = []) => {
    if (!files.length) return true;

    for (const file of files) {
      const extension = getFileExtension(file.name);
      if (!['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'].includes(extension)) {
        await notify('error', `Format file harus ${PENGUJIAN_UPLOAD_TYPES_LABEL}.`);
        return false;
      }

      if ((file.size || 0) > PENGUJIAN_UPLOAD_MAX_BYTES) {
        await notify('error', `Ukuran file maksimal ${PENGUJIAN_UPLOAD_MAX_MB} MB per file.`);
        return false;
      }
    }

    return true;
  };

  const validatePengujianFileInput = async (input) => {
    const files = Array.from(input?.files || []);
    const valid = await validatePengujianFiles(files);
    if (!valid && input) {
      input.value = '';
    }
    return valid;
  };

  const setInputFiles = (input, files = []) => {
    if (!input) return;
    const transfer = new DataTransfer();
    files.forEach((file) => {
      if (file instanceof File) {
        transfer.items.add(file);
      }
    });
    input.files = transfer.files;
  };

  const getStoredInputFiles = (input) => {
    return Array.isArray(input?.__storedFiles) ? input.__storedFiles : [];
  };

  const restoreStoredInputFiles = (input, files = []) => {
    const safeFiles = Array.isArray(files) ? files.filter((file) => file instanceof File) : [];
    input.__storedFiles = safeFiles;
    setInputFiles(input, safeFiles);
  };

  const revokeUploadPreviewUrl = (input) => {
    const previewUrl = input?.dataset?.previewUrl || '';
    if (previewUrl) {
      URL.revokeObjectURL(previewUrl);
      delete input.dataset.previewUrl;
    }
    if (input?.dataset?.previewSignature) {
      delete input.dataset.previewSignature;
    }
  };

  const getUploadPreviewUrl = (input, file) => {
    if (!input || !file) return '';
    const signature = `${file.name}|${file.size}|${file.lastModified}|${file.type}`;
    if (input.dataset.previewUrl && input.dataset.previewSignature === signature) {
      return input.dataset.previewUrl;
    }
    revokeUploadPreviewUrl(input);
    const previewUrl = URL.createObjectURL(file);
    input.dataset.previewUrl = previewUrl;
    input.dataset.previewSignature = signature;
    return previewUrl;
  };

  const updateUploadRowPreview = (input) => {
    const row = input?.closest('[data-upload-row]');
    const filenameEl = row?.querySelector('[data-upload-filename]');
    const previewBtn = row?.querySelector('[data-upload-preview-btn]');
    const file = input?.files?.[0];

    if (!row || !previewBtn || !filenameEl) {
      return;
    }

    if (!file) {
      filenameEl.textContent = 'Belum ada file';
      previewBtn.disabled = true;
      previewBtn.classList.add('d-none');
      revokeUploadPreviewUrl(input);
      return;
    }

    filenameEl.textContent = file.name || 'File dipilih';
    previewBtn.disabled = false;
    previewBtn.classList.remove('d-none');
    previewBtn.setAttribute('title', file.name || 'Lihat file');
    previewBtn.setAttribute('aria-label', file.name || 'Lihat file');
    getUploadPreviewUrl(input, file);
  };

  const cleanupRowPreview = (row) => {
    row?.querySelectorAll('input[type="file"]').forEach((input) => {
      revokeUploadPreviewUrl(input);
      input.__storedFiles = [];
      input.__previousFiles = [];
    });
  };

  const cleanupDocPreviewUrls = (docItem) => {
    docItem?.querySelectorAll('input[type="file"]').forEach((input) => {
      revokeUploadPreviewUrl(input);
      input.__storedFiles = [];
      input.__previousFiles = [];
    });
  };

  const openSelectedFilePreview = (input) => {
    const file = input?.files?.[0];
    if (!file) return;
    const previewUrl = getUploadPreviewUrl(input, file);
    if (!previewUrl) return;
    window.open(previewUrl, '_blank', 'noopener');
  };

  const openFileUrl = (url) => {
    if (!url) return;
    window.open(url, '_blank', 'noopener');
  };

  const ensureUploadRow = (docItem, options = {}) => {
    const uploadWrap = docItem?.querySelector('[data-doc-upload]');
    if (!uploadWrap) return null;

    const rows = Array.from(uploadWrap.querySelectorAll('[data-upload-row]'));
    const reusableRow = rows.find((row) => {
      const input = row.querySelector('input[type="file"]');
      return !(input?.files && input.files.length);
    });
    if (reusableRow) {
      return reusableRow;
    }

    const row = createUploadRow(options);
    const toolbar = uploadWrap.querySelector('.doc-upload-toolbar');
    uploadWrap.insertBefore(row, toolbar || uploadWrap.lastChild);
    return row;
  };

  const createExistingFileRow = (docItem, file) => {
    const row = document.createElement('div');
    row.className = 'upload-row';
    row.dataset.existingFileRow = 'true';
    row.dataset.existingFileId = String(file.id || '');
    row.innerHTML = `
      <div class="upload-row-actions">
        <button class="btn btn-outline-secondary btn-sm existing-file-trigger" type="button" data-existing-replace-trigger>Pilih File</button>
        <div class="upload-row-filename" data-existing-file-name></div>
        <div class="upload-row-tools">
          <button class="btn btn-outline-primary btn-sm p-1" type="button" data-existing-view-btn aria-label="Lihat file" title="Lihat file">
            <i class="bi bi-eye"></i>
          </button>
          <button class="btn btn-outline-danger btn-sm p-1" type="button" data-existing-remove-btn aria-label="Hapus file" title="Hapus file">
            <i class="bi bi-trash"></i>
          </button>
        </div>
        <input type="file" class="d-none" data-existing-replace-input accept="${PENGUJIAN_UPLOAD_ACCEPT}">
      </div>
    `;

    const nameEl = row.querySelector('[data-existing-file-name]');
    const replaceTrigger = row.querySelector('[data-existing-replace-trigger]');
    const viewBtn = row.querySelector('[data-existing-view-btn]');
    const removeBtn = row.querySelector('[data-existing-remove-btn]');
    const replaceInput = row.querySelector('[data-existing-replace-input]');

    const setDisplayName = (name) => {
      if (nameEl) {
        nameEl.textContent = name || 'File';
      }
    };

    const removeExistingReference = () => {
      const current = JSON.parse(docItem.dataset.existingFiles || '[]');
      const filtered = current.filter((item) => String(item.id) !== String(file.id));
      docItem.dataset.existingFiles = JSON.stringify(filtered);
      return filtered;
    };

    setDisplayName(file.name || 'File');

    replaceTrigger?.addEventListener('click', () => {
      replaceInput?.click();
    });

    replaceInput?.addEventListener('change', async () => {
      const selectedFile = replaceInput.files?.[0];
      if (!selectedFile) {
        return;
      }

      const valid = await validatePengujianFiles([selectedFile]);
      if (!valid) {
        replaceInput.value = '';
        return;
      }

      replaceInput.__storedFiles = [selectedFile];
      setDisplayName(selectedFile.name || 'File');
      removeExistingReference();
      docItem.dispatchEvent(new CustomEvent('existing-files-updated', { bubbles: true }));
      await notify('success', `${selectedFile.name} berhasil ditambahkan.`);
    });

    viewBtn?.addEventListener('click', () => {
      const replacementFile = replaceInput?.files?.[0];
      if (replacementFile) {
        const previewUrl = getUploadPreviewUrl(replaceInput, replacementFile);
        if (previewUrl) {
          window.open(previewUrl, '_blank', 'noopener');
        }
        return;
      }
      openFileUrl(file.url || '');
    });

    removeBtn?.addEventListener('click', () => {
      revokeUploadPreviewUrl(replaceInput);
      replaceInput.value = '';
      replaceInput.__storedFiles = [];
      replaceInput.__previousFiles = [];
      removeExistingReference();
      row.remove();
      docItem.dispatchEvent(new CustomEvent('existing-files-updated', { bubbles: true }));
    });

    return row;
  };

  const stopCameraStream = () => {
    if (!cameraStream) return;
    cameraStream.getTracks().forEach((track) => track.stop());
    cameraStream = null;
  };

  const resetCameraPreview = () => {
    if (cameraObjectUrl) {
      URL.revokeObjectURL(cameraObjectUrl);
      cameraObjectUrl = '';
    }
    cameraCapturedBlob = null;
    if (cameraPreviewEl) {
      cameraPreviewEl.src = '';
      cameraPreviewEl.classList.add('d-none');
    }
    if (cameraVideoEl) {
      cameraVideoEl.classList.remove('d-none');
      cameraVideoEl.srcObject = null;
    }
    if (cameraCaptureBtn) {
      cameraCaptureBtn.disabled = false;
      cameraCaptureBtn.classList.remove('d-none');
    }
    cameraRetakeBtn?.classList.add('d-none');
    cameraUseBtn?.classList.add('d-none');
    if (cameraStatusEl) {
      cameraStatusEl.textContent = 'Posisikan dokumen di depan kamera, lalu ambil foto.';
    }
  };

  const startCameraStream = async () => {
    if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
      throw new Error('Kamera browser tidak tersedia di perangkat ini.');
    }

    stopCameraStream();
    resetCameraPreview();

    if (cameraStatusEl) {
      cameraStatusEl.textContent = 'Mengaktifkan kamera...';
    }

    const stream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: { ideal: 'environment' },
      },
      audio: false,
    });

    cameraStream = stream;

    if (cameraVideoEl) {
      cameraVideoEl.srcObject = stream;
      await cameraVideoEl.play().catch(() => {});
    }

    if (cameraStatusEl) {
      cameraStatusEl.textContent = 'Posisikan dokumen di depan kamera, lalu klik Ambil Foto.';
    }
  };

  const captureCameraPhoto = async () => {
    if (!cameraVideoEl || !cameraCanvasEl) {
      throw new Error('Komponen kamera tidak lengkap.');
    }

    const width = cameraVideoEl.videoWidth || 0;
    const height = cameraVideoEl.videoHeight || 0;
    if (!width || !height) {
      throw new Error('Kamera belum siap. Coba lagi.');
    }

    cameraCanvasEl.width = width;
    cameraCanvasEl.height = height;
    const ctx = cameraCanvasEl.getContext('2d');
    if (!ctx) {
      throw new Error('Gagal memproses hasil foto.');
    }

    ctx.drawImage(cameraVideoEl, 0, 0, width, height);

    const blob = await new Promise((resolve) => {
      cameraCanvasEl.toBlob(resolve, 'image/jpeg', 0.92);
    });

    if (!(blob instanceof Blob)) {
      throw new Error('Gagal mengambil foto dokumen.');
    }

    stopCameraStream();
    cameraCapturedBlob = blob;

    if (cameraObjectUrl) {
      URL.revokeObjectURL(cameraObjectUrl);
    }
    cameraObjectUrl = URL.createObjectURL(blob);

    if (cameraVideoEl) {
      cameraVideoEl.classList.add('d-none');
    }
    if (cameraPreviewEl) {
      cameraPreviewEl.src = cameraObjectUrl;
      cameraPreviewEl.classList.remove('d-none');
    }
    if (cameraStatusEl) {
      cameraStatusEl.textContent = 'Foto dokumen sudah diambil. Gunakan foto ini atau ambil ulang.';
    }
    cameraCaptureBtn?.classList.add('d-none');
    cameraRetakeBtn?.classList.remove('d-none');
    cameraUseBtn?.classList.remove('d-none');
  };

  const buildCameraFilename = () => {
    const now = new Date();
    const pad = (value) => String(value).padStart(2, '0');
    const stamp = `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
    return `dokumen_kamera_${stamp}.jpg`;
  };

  const cleanupPendingCameraRow = () => {
    if (cameraPendingRow && cameraPendingRow.dataset.cameraTemporary === 'true') {
      const input = cameraPendingRow.querySelector('input[type="file"]');
      const hasFile = !!(input?.files && input.files.length);
      if (!hasFile) {
        cameraPendingRow.remove();
      }
    }
  };

  const openCameraCapture = async ({ input, row, onComplete, onCancel } = {}) => {
    if (!cameraCaptureModal || !input || !row) {
      await notify('error', 'Fitur kamera tidak tersedia di halaman ini.');
      return;
    }

    cameraPendingInput = input;
    cameraPendingRow = row;
    cameraPendingAfterCapture = typeof onComplete === 'function' ? onComplete : null;
    cameraPendingAfterCancel = typeof onCancel === 'function' ? onCancel : null;
    row.dataset.cameraTemporary = 'true';

    cameraCaptureModal.show();

    try {
      await startCameraStream();
    } catch (error) {
      cleanupPendingCameraRow();
      cameraCaptureModal.hide();
      await notify('error', error.message || 'Kamera tidak dapat diakses.');
    }
  };

  cameraCaptureBtn?.addEventListener('click', async () => {
    try {
      cameraCaptureBtn.disabled = true;
      await captureCameraPhoto();
    } catch (error) {
      await notify('error', error.message || 'Gagal mengambil foto dokumen.');
    } finally {
      cameraCaptureBtn.disabled = false;
    }
  });

  cameraRetakeBtn?.addEventListener('click', async () => {
    try {
      await startCameraStream();
    } catch (error) {
      await notify('error', error.message || 'Kamera tidak dapat diaktifkan kembali.');
    }
  });

  cameraUseBtn?.addEventListener('click', async () => {
    if (!cameraPendingInput || !cameraCapturedBlob) {
      await notify('error', 'Belum ada foto dokumen yang dipilih.');
      return;
    }

    const file = new File([cameraCapturedBlob], buildCameraFilename(), { type: 'image/jpeg' });
    const transfer = new DataTransfer();
    transfer.items.add(file);
    cameraPendingInput.files = transfer.files;
    cameraPendingRow?.removeAttribute('data-camera-temporary');
    cameraPendingInput.dispatchEvent(new Event('change', { bubbles: true }));
    cameraPendingAfterCapture?.();
    cameraCaptureModal?.hide();
  });

  cameraCaptureModalEl?.addEventListener('hidden.bs.modal', () => {
    const shouldCancel = !cameraPendingInput || !cameraPendingInput.files || cameraPendingInput.files.length === 0;
    stopCameraStream();
    resetCameraPreview();
    cleanupPendingCameraRow();
    if (shouldCancel) {
      cameraPendingAfterCancel?.();
    }
    cameraPendingInput = null;
    cameraPendingRow = null;
    cameraPendingAfterCapture = null;
    cameraPendingAfterCancel = null;
  });

  const createUploadRow = (options = {}) => {
    const { accept, capture, label, required } = options;
    const row = document.createElement('div');
    row.className = 'upload-row';
    row.dataset.uploadRow = 'true';
    if (required) {
      row.dataset.uploadRequired = 'true';
    }
    row.innerHTML = `
      <div class="upload-row-actions">
        <button class="btn btn-outline-secondary btn-sm upload-file-trigger" type="button" data-upload-trigger>Pilih File</button>
        <div class="upload-row-filename" data-upload-filename>Belum ada file</div>
        <div class="upload-row-tools">
          <button class="btn btn-outline-primary btn-sm p-1 d-none" type="button" data-upload-preview-btn disabled aria-label="Lihat file" title="Lihat file">
            <i class="bi bi-eye"></i>
          </button>
          <button class="btn btn-outline-danger btn-sm p-1" type="button" data-upload-remove aria-label="Hapus file">
            <i class="bi bi-trash"></i>
          </button>
        </div>
        <input type="file" class="d-none">
      </div>
    `;
    const input = row.querySelector('input[type="file"]');
    const triggerBtn = row.querySelector('[data-upload-trigger]');
    const previewBtn = row.querySelector('[data-upload-preview-btn]');
    if (accept) {
      input.setAttribute('accept', accept);
    } else {
      input.setAttribute('accept', PENGUJIAN_UPLOAD_ACCEPT);
    }
    if (capture) input.setAttribute('capture', capture);
    if (label) input.setAttribute('aria-label', label);
    input.__storedFiles = [];
    input.__previousFiles = [];
    triggerBtn?.addEventListener('click', () => {
      input.__previousFiles = Array.from(input.files || getStoredInputFiles(input));
      input.click();
    });
    input.addEventListener('click', () => {
      input.__previousFiles = Array.from(input.files || getStoredInputFiles(input));
    });
    input.addEventListener('change', async () => {
      const previousFiles = Array.isArray(input.__previousFiles) ? input.__previousFiles : [];
      const currentFiles = Array.from(input.files || []);
      const previousSignature = previousFiles.map((file) => `${file.name}|${file.size}|${file.lastModified}`).join('||');
      const currentSignature = currentFiles.map((file) => `${file.name}|${file.size}|${file.lastModified}`).join('||');

      if (!currentFiles.length && previousFiles.length) {
        restoreStoredInputFiles(input, previousFiles);
        updateUploadRowPreview(input);
        input.__previousFiles = [];
        return;
      }

      const valid = await validatePengujianFileInput(input);
      if (!valid) {
        if (previousFiles.length) {
          restoreStoredInputFiles(input, previousFiles);
        } else {
          input.__storedFiles = [];
        }
        updateUploadRowPreview(input);
        input.__previousFiles = [];
        return;
      }
      input.__storedFiles = currentFiles;
      updateUploadRowPreview(input);
      if (currentFiles.length && currentSignature !== previousSignature) {
        const uploadedName = currentFiles[0]?.name || 'File';
        await notify('success', `${uploadedName} berhasil ditambahkan.`);
      }
      input.__previousFiles = [];
    });
    previewBtn?.addEventListener('click', () => {
      openSelectedFilePreview(input);
    });
    return row;
  };

  const createDocItem = (docId, label, options = {}) => {
    const { withInitialRow = true } = options;
    const docItem = document.createElement('div');
    docItem.className = 'd-flex flex-column gap-2 doc-block';
    docItem.dataset.docItem = 'true';
    docItem.dataset.docId = docId;
    docItem.innerHTML = `
      <div class="doc-label">${label}</div>
      <div class="d-flex flex-column gap-1 small text-muted" data-existing-files></div>
      <div class="d-flex flex-column gap-2" data-doc-upload>
        <div class="doc-upload-toolbar">
          <button class="btn btn-outline-secondary btn-sm" type="button" data-upload-add aria-label="Tambah File" title="Tambah File">
            <i class="bi bi-file-earmark-plus me-1"></i> File
          </button>
          <button class="btn btn-outline-secondary btn-sm px-2" type="button" data-upload-camera aria-label="Gunakan Kamera" title="Gunakan Kamera">
            <i class="bi bi-camera"></i>
          </button>
        </div>
        <div class="small text-muted doc-upload-meta">Jenis file: ${PENGUJIAN_UPLOAD_TYPES_LABEL}. Maksimal ${PENGUJIAN_UPLOAD_MAX_MB} MB per file.</div>
      </div>
    `;
    const uploadWrap = docItem.querySelector('[data-doc-upload]');
    if (withInitialRow) {
      const initialRow = createUploadRow();
      uploadWrap?.insertBefore(initialRow, uploadWrap.firstChild);
    }
    return docItem;
  };

  const renderExistingFiles = (docItem, files = []) => {
    const container = docItem.querySelector('[data-existing-files]');
    docItem.dataset.existingFiles = JSON.stringify(files || []);
    if (!container) return;
    container.innerHTML = '';
    if (!files.length) return;
    files.forEach((file) => {
      container.appendChild(createExistingFileRow(docItem, file));
    });
  };

  const attachDocUploadHandlers = (docItem) => {
    const uploadAdd = docItem.querySelector('[data-upload-add]');
    const uploadCamera = docItem.querySelector('[data-upload-camera]');
    const uploadWrap = docItem.querySelector('[data-doc-upload]');

    if (uploadAdd && uploadWrap) {
      uploadWrap.addEventListener('click', (event) => {
        const removeBtn = event.target.closest('[data-upload-remove]');
        if (!removeBtn) return;
        const row = removeBtn.closest('[data-upload-row]');
        if (!row) return;
        const rows = uploadWrap.querySelectorAll('[data-upload-row]');
        if (rows.length <= 1) {
          cleanupRowPreview(row);
          row.remove();
          return;
        }
        cleanupRowPreview(row);
        row.remove();
      });
      uploadAdd.addEventListener('click', () => {
        const row = createUploadRow({ required: true });
        uploadWrap.insertBefore(row, uploadAdd.parentElement);
      });
    }

    if (uploadCamera && uploadWrap) {
      uploadCamera.addEventListener('click', async () => {
        const row = createUploadRow({
          accept: PENGUJIAN_UPLOAD_IMAGE_ACCEPT,
          label: 'Kamera',
          required: true,
        });
        uploadWrap.insertBefore(row, uploadCamera.parentElement);
        const input = row.querySelector('input[type="file"]');
        await openCameraCapture({
          input,
          row,
        });
      });
    }
  };

  const setupRow = (row, params, tbody, tableType) => {
    if (!row || row.dataset.rowInit === 'true') return;
    const docList = row.querySelector('[data-doc-list]');
    const docEmpty = row.querySelector('[data-doc-empty]');
    const docAdd = row.querySelector('[data-doc-add]');
    const paramDocs = row.querySelector('[data-param-docs]');
    const paramEmpty = row.querySelector('[data-param-empty]');
    const methodDocs = row.querySelector('[data-method-docs]');
    const methodEmpty = row.querySelector('[data-method-empty]');
    const qtyDocs = row.querySelector('[data-qty-docs]');
    const qtyEmpty = row.querySelector('[data-qty-empty]');
    const actionDocs = row.querySelector('[data-action-docs]');
    const actionEmpty = row.querySelector('[data-action-empty]');
    const switchDocs = row.querySelector('[data-switch-docs]');
    const switchEmpty = row.querySelector('[data-switch-empty]');
    const reviewDocs = row.querySelector('[data-review-docs]');
    const reviewEmpty = row.querySelector('[data-review-empty]');
    const noteDocs = row.querySelector('[data-note-docs]');
    const noteEmpty = row.querySelector('[data-note-empty]');
    const locationInput = row.querySelector('[data-location-input]');

    if (!docList || !paramDocs || !methodDocs || !qtyDocs || !actionDocs || !switchDocs || !locationInput) {
      return;
    }
    if (IS_REVISION_VIEW && (!reviewDocs || !noteDocs)) {
      return;
    }

    const isDirectTable = tableType === 'direct';

    const setDocEmpty = () => {
      setEmptyVisibility(docList, docEmpty);
      setEmptyVisibility(paramDocs, paramEmpty);
      setEmptyVisibility(methodDocs, methodEmpty);
      setEmptyVisibility(qtyDocs, qtyEmpty);
      setEmptyVisibility(actionDocs, actionEmpty);
      setEmptyVisibility(switchDocs, switchEmpty);
      if (IS_REVISION_VIEW) {
        setEmptyVisibility(reviewDocs, reviewEmpty);
        setEmptyVisibility(noteDocs, noteEmpty);
      }
    };

    let syncDocHeightsFrame = null;
    let docHeightObserver = null;

    const syncDocHeights = () => {
      const items = Array.from(docList.querySelectorAll('[data-doc-item]'));
      items.forEach((item) => {
        const docId = item.dataset.docId;
        const targets = [
          item,
          paramDocs.querySelector(`[data-doc-id="${docId}"]`),
          methodDocs.querySelector(`[data-doc-id="${docId}"]`),
          qtyDocs.querySelector(`[data-doc-id="${docId}"]`),
          actionDocs.querySelector(`[data-doc-id="${docId}"]`),
          switchDocs.querySelector(`[data-doc-id="${docId}"]`),
          IS_REVISION_VIEW ? reviewDocs?.querySelector(`[data-doc-id="${docId}"]`) : null,
          IS_REVISION_VIEW ? noteDocs?.querySelector(`[data-doc-id="${docId}"]`) : null,
        ].filter(Boolean);
        targets.forEach((target) => {
          target.style.minHeight = '';
        });
        const maxHeight = Math.max(...targets.map((target) => target.offsetHeight));
        targets.forEach((target) => {
          target.style.minHeight = `${maxHeight}px`;
        });
      });
    };

    const scheduleSyncDocHeights = () => {
      if (syncDocHeightsFrame) {
        cancelAnimationFrame(syncDocHeightsFrame);
      }
      syncDocHeightsFrame = requestAnimationFrame(() => {
        syncDocHeightsFrame = null;
        syncDocHeights();
      });
    };

    const rebuildDocHeightObserver = () => {
      if (typeof ResizeObserver === 'undefined') {
        return;
      }

      docHeightObserver?.disconnect();
      docHeightObserver = new ResizeObserver(() => {
        scheduleSyncDocHeights();
      });

      const targets = [
        ...docList.querySelectorAll('[data-doc-item]'),
        ...paramDocs.querySelectorAll('[data-doc-id]'),
        ...methodDocs.querySelectorAll('[data-doc-id]'),
        ...qtyDocs.querySelectorAll('[data-doc-id]'),
        ...actionDocs.querySelectorAll('[data-doc-id]'),
        ...switchDocs.querySelectorAll('[data-doc-id]'),
        ...(IS_REVISION_VIEW ? Array.from(reviewDocs?.querySelectorAll('[data-doc-id]') || []) : []),
        ...(IS_REVISION_VIEW ? Array.from(noteDocs?.querySelectorAll('[data-doc-id]') || []) : []),
      ];

      targets.forEach((target) => {
        docHeightObserver.observe(target);
      });
    };

    const renumberDocs = () => {
      const items = Array.from(docList.querySelectorAll('[data-doc-item]'));
      items.forEach((item, index) => {
        const docId = item.dataset.docId;
        const label = `Dokumen ${index + 1}`;
        const labelEl = item.querySelector('.doc-label');
        if (labelEl) labelEl.textContent = label;
        const paramLabel = paramDocs.querySelector(`[data-doc-id="${docId}"] .doc-label`);
        if (paramLabel) paramLabel.textContent = label;
        const methodLabel = methodDocs.querySelector(`[data-doc-id="${docId}"] .doc-label`);
        if (methodLabel) methodLabel.textContent = label;
        const qtyLabel = qtyDocs.querySelector(`[data-doc-id="${docId}"] .doc-label`);
        if (qtyLabel) qtyLabel.textContent = label;
        const actionLabel = actionDocs.querySelector(`[data-doc-id="${docId}"] .doc-label`);
        if (actionLabel) actionLabel.textContent = label;
        const switchLabel = switchDocs.querySelector(`[data-doc-id="${docId}"] .doc-label`);
        if (switchLabel) switchLabel.textContent = label;
        if (IS_REVISION_VIEW) {
          const reviewLabel = reviewDocs?.querySelector(`[data-doc-id="${docId}"] .doc-label`);
          if (reviewLabel) reviewLabel.textContent = label;
          const noteLabel = noteDocs?.querySelector(`[data-doc-id="${docId}"] .doc-label`);
          if (noteLabel) noteLabel.textContent = label;
        }
      });
    };

    const addParamRowToDoc = (docId, values = {}) => {
      const shouldFocus = values.focus !== false;
      const rowId = `param-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`;
      const { wrap, select, removeBtn } = createParamRow(params, rowId);
      const qtyInput = createQtyInput(rowId);
      const actionWrap = createActionCheckbox(rowId);
      const switchWrap = createReqSwitch(rowId);
      const methodWrap = createMethodIndicator(rowId, isDirectTable ? 'Direct' : 'Indirect');
      const reviewWrap = IS_REVISION_VIEW ? createReviewSwitch(rowId, values.reviewStatus || '') : null;
      const noteWrap = IS_REVISION_VIEW ? createReviewNote(rowId, values.reviewNote || values.catatan || '') : null;

      const paramDocBlock = paramDocs.querySelector(`[data-doc-id="${docId}"]`);
      const methodDocBlock = methodDocs.querySelector(`[data-doc-id="${docId}"]`);
      const qtyDocBlock = qtyDocs.querySelector(`[data-doc-id="${docId}"]`);
      const actionDocBlock = actionDocs.querySelector(`[data-doc-id="${docId}"]`);
      const switchDocBlock = switchDocs.querySelector(`[data-doc-id="${docId}"]`);
      const reviewDocBlock = IS_REVISION_VIEW ? reviewDocs?.querySelector(`[data-doc-id="${docId}"]`) : null;
      const noteDocBlock = IS_REVISION_VIEW ? noteDocs?.querySelector(`[data-doc-id="${docId}"]`) : null;

      if (!paramDocBlock || !methodDocBlock || !qtyDocBlock || !actionDocBlock || !switchDocBlock) return;
      if (IS_REVISION_VIEW && (!reviewDocBlock || !noteDocBlock || !reviewWrap || !noteWrap)) return;

      wrap.dataset.docId = docId;
      qtyInput.dataset.docId = docId;
      actionWrap.dataset.docId = docId;
      switchWrap.dataset.docId = docId;
      select.dataset.docId = docId;
      select.dataset.paramRowId = rowId;
      select.__baseParams = params;
      select.__allowedParams = new Set(params.map((param) => String(param.id)));

      const addControlWrap = paramDocBlock.querySelector('[data-param-add-wrap]');
      if (addControlWrap) {
        paramDocBlock.insertBefore(wrap, addControlWrap);
      } else {
        paramDocBlock.appendChild(wrap);
      }
      methodDocBlock.appendChild(methodWrap);
      qtyDocBlock.appendChild(qtyInput);
      actionDocBlock.appendChild(actionWrap);
      switchDocBlock.appendChild(switchWrap);
      if (IS_REVISION_VIEW) {
        reviewDocBlock.appendChild(reviewWrap);
        noteDocBlock.appendChild(noteWrap);
      }

      setDocEmpty();
      updateParamOptions(paramDocBlock);

      const ensureOption = (value, label = '') => {
        if (!value || value === EXTRA_OPTION) return;
        const exists = Array.from(select.options).some((option) => option.value === value);
        if (exists) return;
        const extraOption = Array.from(select.options).find((option) => option.value === EXTRA_OPTION);
        const newOption = document.createElement('option');
        newOption.value = value;
        newOption.textContent = label || value;
        if (extraOption) {
          select.insertBefore(newOption, extraOption);
        } else {
          select.appendChild(newOption);
        }
      };

      const initialParam = values.paramId ?? values.param ?? '';
      if (initialParam) {
        ensureOption(String(initialParam), values.paramName || '');
        select.value = String(initialParam);
      }
      if (values.qty !== undefined && values.qty !== null && values.qty !== '') {
        const qtyField = qtyInput.querySelector('input');
        if (qtyField) qtyField.value = values.qty;
      }
      if (typeof values.action === 'boolean') {
        const actionInput = actionWrap.querySelector('input');
        actionInput?.toggleAttribute('checked', values.action);
        if (actionInput) {
          actionInput.checked = values.action;
        }
      }
      const actionInput = actionWrap.querySelector('input');
      if (actionInput) {
        actionInput.checked = isDirectTable;
        actionInput.disabled = true;
      }
      if (typeof values.switchOn === 'boolean') {
        const switchInput = switchWrap.querySelector('input');
        if (switchInput) {
          switchInput.checked = values.switchOn;
          switchInput.classList.toggle('req-switch-invalid', !values.switchOn);
        }
      }

      const updateSuitability = (value) => {
        const switchInput = switchWrap.querySelector('input');
        if (!switchInput) return;
        if (!value) {
          switchInput.checked = true;
          switchInput.classList.remove('req-switch-invalid');
          return;
        }
        if (String(select.dataset.customValue || '') === String(value)) {
          switchInput.checked = false;
          switchInput.classList.add('req-switch-invalid');
          return;
        }
        let isAllowed = !!select.__allowedParams?.has(value);
        if (isAllowed) {
          const paramMeta = (Array.isArray(select.__baseParams) ? select.__baseParams : [])
            .find((param) => String(param.id) === String(value));
          const totalAllowedQty = Number.parseInt(String(paramMeta?.qty ?? '0'), 10);
          const currentQtyInput = qtyInput.querySelector('input');
          const currentQty = Number.parseInt(String(currentQtyInput?.value || '1'), 10);

          const card = select.closest('[data-card]');
          const allSelects = Array.from(card?.querySelectorAll('select[data-param-select]') || []);
          let usedByOthers = 0;

          allSelects.forEach((otherSelect) => {
            if (otherSelect === select) return;
            if (String(otherSelect.value || '') !== String(value)) return;

            const directRow = otherSelect.closest('tr[data-direct-group]');
            if (directRow) {
              usedByOthers += directRow.querySelectorAll('[data-direct-location-row]').length || 1;
              return;
            }

            const otherRow = otherSelect.closest('[data-row]');
            const otherDocId = otherSelect.dataset.docId || '';
            const otherRowId = otherSelect.dataset.paramRowId || '';
            const otherQtyInput = otherRow?.querySelector(
              `[data-qty-docs] [data-doc-id="${otherDocId}"][data-param-row-id="${otherRowId}"] input`
            );
            const otherQty = Number.parseInt(String(otherQtyInput?.value || '1'), 10);
            usedByOthers += Number.isFinite(otherQty) && otherQty > 0 ? otherQty : 1;
          });

          const remainingQty = Number.isFinite(totalAllowedQty)
            ? Math.max(0, totalAllowedQty - usedByOthers)
            : 0;
          const normalizedCurrentQty = Number.isFinite(currentQty) && currentQty > 0 ? currentQty : 1;
          isAllowed = normalizedCurrentQty <= remainingQty;
        }
        switchInput.checked = !!isAllowed;
        switchInput.classList.toggle('req-switch-invalid', !isAllowed);
      };

      select.__applySelection = (value) => {
        if (value === EXTRA_OPTION) return;
        let resolved = value;
        let label = '';
        if (value && typeof value === 'object') {
          resolved = value.id ?? '';
          label = value.name ?? '';
        }
        if (!resolved) {
          select.value = '';
          delete select.dataset.customValue;
          delete select.dataset.customLabel;
        } else {
          ensureOption(String(resolved), label);
          if (label) {
            select.dataset.customValue = String(resolved);
            select.dataset.customLabel = label;
          } else if (String(select.dataset.customValue || '') === String(resolved)) {
            delete select.dataset.customValue;
            delete select.dataset.customLabel;
          }
          select.value = String(resolved);
        }
        select.dataset.prevValue = select.value || '';
        updateParamOptions(paramDocBlock);
        updateSuitability(select.value);
        select.dispatchEvent(new Event('change', { bubbles: true }));
      };

      const rememberPrevValue = () => {
        select.dataset.prevValue = select.value || '';
      };

      select.addEventListener('focus', rememberPrevValue);
      select.addEventListener('mousedown', rememberPrevValue);
      select.addEventListener('change', () => {
        if (select.value === EXTRA_OPTION) {
          openExtraModal(select);
          return;
        }
        if (String(select.dataset.customValue || '') !== String(select.value || '')) {
          delete select.dataset.customValue;
          delete select.dataset.customLabel;
        }
        select.dataset.prevValue = select.value || '';
        updateParamOptions(paramDocBlock);
        updateSuitability(select.value);
      });

      removeBtn.addEventListener('click', () => {
        wrap.remove();
        methodWrap.remove();
        qtyInput.remove();
        actionWrap.remove();
        switchWrap.remove();
        reviewWrap?.remove();
        noteWrap?.remove();
        setDocEmpty();
        updateParamOptions(paramDocBlock);
        scheduleSyncDocHeights();
      });

      if (select.value) {
        updateSuitability(select.value);
      }
      if (shouldFocus) {
        select.focus();
      }
    };

    const cloneDocParams = (sourceDocId, targetDocId) => {
      const sourceParamBlock = paramDocs.querySelector(`[data-doc-id="${sourceDocId}"]`);
      if (!sourceParamBlock) return;
      const sourceRows = Array.from(sourceParamBlock.querySelectorAll('[data-param-row-id]'));
      sourceRows.forEach((wrap) => {
        const select = wrap.querySelector('select');
        const rowId = wrap.dataset.paramRowId;
        const qtyInput = qtyDocs.querySelector(`[data-doc-id="${sourceDocId}"] [data-param-row-id="${rowId}"]`);
        const actionInput = actionDocs.querySelector(`[data-doc-id="${sourceDocId}"] [data-param-row-id="${rowId}"] input`);
        const switchInput = switchDocs.querySelector(`[data-doc-id="${sourceDocId}"] [data-param-row-id="${rowId}"] input`);
        const qtyValue = qtyInput?.querySelector('input')?.value || '1';

        addParamRowToDoc(targetDocId, {
          param: select?.value || '',
          qty: qtyValue,
          action: actionInput?.checked || false,
          switchOn: switchInput?.checked ?? true,
          focus: false,
        });
      });
    };

    const addDoc = (preset = {}) => {
      const docIndex = docList.querySelectorAll('[data-doc-item]').length + 1;
      const docKey = preset.key || `doc-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`;
      const label = preset.label || `Dokumen ${docIndex}`;
      const hasExistingFiles = Array.isArray(preset.files) && preset.files.length > 0;
      const docItem = createDocItem(docKey, label, {
        withInitialRow: !hasExistingFiles,
      });
      docItem.dataset.docKey = docKey;
      if (preset.id) {
        docItem.dataset.docDbId = preset.id;
      }

      const paramDocBlock = createDocBlock(docKey, label);
      const methodDocBlock = createDocBlock(docKey, label);
      const qtyDocBlock = createDocBlock(docKey, label);
      const actionDocBlock = createDocBlock(docKey, label);
      const switchDocBlock = createDocBlock(docKey, label);
      const reviewDocBlock = IS_REVISION_VIEW ? createDocBlock(docKey, label) : null;
      const noteDocBlock = IS_REVISION_VIEW ? createDocBlock(docKey, label) : null;
      renderExistingFiles(docItem, preset.files || []);

      docList.appendChild(docItem);
      paramDocs.appendChild(paramDocBlock);
      methodDocs.appendChild(methodDocBlock);
      qtyDocs.appendChild(qtyDocBlock);
      actionDocs.appendChild(actionDocBlock);
      switchDocs.appendChild(switchDocBlock);
      if (IS_REVISION_VIEW) {
        reviewDocs?.appendChild(reviewDocBlock);
        noteDocs?.appendChild(noteDocBlock);
      }
      paramDocBlock.appendChild(createParamAddControl('data-param-add'));

      setDocEmpty();
      renumberDocs();
      scheduleSyncDocHeights();

      const uploadAdd = docItem.querySelector('[data-upload-add]');
      const uploadCamera = docItem.querySelector('[data-upload-camera]');
      const uploadWrap = docItem.querySelector('[data-doc-upload]');
      const paramAdd = paramDocBlock.querySelector('[data-param-add]');

      if (uploadAdd && uploadWrap) {
        uploadWrap.addEventListener('click', (event) => {
          const removeBtn = event.target.closest('[data-upload-remove]');
          if (!removeBtn) return;
          const row = removeBtn.closest('[data-upload-row]');
          if (!row) return;
          const rows = uploadWrap.querySelectorAll('[data-upload-row]');
          if (rows.length <= 1) {
            cleanupRowPreview(row);
            row.remove();
            scheduleSyncDocHeights();
            return;
          }
          cleanupRowPreview(row);
          row.remove();
          scheduleSyncDocHeights();
        });
        uploadAdd.addEventListener('click', () => {
          const row = createUploadRow({ required: true });
          uploadWrap.insertBefore(row, uploadAdd.parentElement);
          scheduleSyncDocHeights();
        });
      }

      if (uploadCamera && uploadWrap) {
        uploadCamera.addEventListener('click', async () => {
          const row = createUploadRow({
            accept: PENGUJIAN_UPLOAD_IMAGE_ACCEPT,
            label: 'Kamera',
            required: true,
          });
          uploadWrap.insertBefore(row, uploadCamera.parentElement);
          const input = row.querySelector('input[type="file"]');
          await openCameraCapture({
            input,
            row,
            onComplete: () => scheduleSyncDocHeights(),
            onCancel: () => scheduleSyncDocHeights(),
          });
          scheduleSyncDocHeights();
        });
      }

      const docRemove = docItem.querySelector('[data-doc-remove]');
      if (docRemove) {
        docRemove.addEventListener('click', () => {
          cleanupDocPreviewUrls(docItem);
          docItem.remove();
          paramDocs.querySelector(`[data-doc-id="${docKey}"]`)?.remove();
          methodDocs.querySelector(`[data-doc-id="${docKey}"]`)?.remove();
          qtyDocs.querySelector(`[data-doc-id="${docKey}"]`)?.remove();
          actionDocs.querySelector(`[data-doc-id="${docKey}"]`)?.remove();
          switchDocs.querySelector(`[data-doc-id="${docKey}"]`)?.remove();
          reviewDocs?.querySelector(`[data-doc-id="${docKey}"]`)?.remove();
          noteDocs?.querySelector(`[data-doc-id="${docKey}"]`)?.remove();
          setDocEmpty();
          renumberDocs();
          rebuildDocHeightObserver();
          scheduleSyncDocHeights();
        });
      }

      if (paramAdd) {
        paramAdd.addEventListener('click', () => {
          addParamRowToDoc(docKey);
          scheduleSyncDocHeights();
        });
      }

      if (Array.isArray(preset.parameters) && preset.parameters.length) {
        preset.parameters.forEach((paramItem) => {
          addParamRowToDoc(docKey, {
            paramId: paramItem.parameter_id,
            paramName: paramItem.parameter_name,
            qty: paramItem.qty,
            action: paramItem.is_direct,
            switchOn: paramItem.is_sesuai,
            reviewStatus: paramItem.review_status,
            reviewNote: paramItem.catatan,
            focus: false,
          });
        });
        scheduleSyncDocHeights();
      } else if (preset.cloneFrom) {
        cloneDocParams(preset.cloneFrom, docKey);
        scheduleSyncDocHeights();
      }

      rebuildDocHeightObserver();
    };

    if (docAdd) {
      docAdd.addEventListener('click', () => {
        addDoc();
      });
    }

    setDocEmpty();
    row.dataset.rowInit = 'true';
    row.__addDoc = addDoc;
    rebuildDocHeightObserver();
    scheduleSyncDocHeights();
  };

  const renumberDirectDocs = (tbody) => {
    if (!tbody) return;
    const cells = Array.from(tbody.querySelectorAll('[data-direct-doc-index]'));
    cells.forEach((cell, idx) => {
      cell.textContent = String(idx + 1);
    });
  };

  const createDirectDocRow = (index, params, preset = {}) => {
    const docKey = `doc-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`;
    const groupId = `direct-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`;

    const createRow = (isFirst) => {
      const row = document.createElement('tr');
      row.classList.add('direct-row');
      if (isFirst) {
        row.classList.add('direct-row-first');
      }
      row.dataset.directDoc = 'true';
      row.dataset.directGroup = groupId;
      row.dataset.docKey = docKey;
      row.innerHTML = `
        ${isFirst ? `<td class="text-muted" data-direct-doc-index>${index}</td>` : ''}
        ${isFirst ? `<td data-direct-doc-cell></td>` : ''}
        <td data-direct-param-cell></td>
        <td data-direct-method-cell></td>
        <td data-direct-lokasi-cell></td>
        <td data-direct-sesuai-cell></td>
        ${IS_REVISION_VIEW ? '<td data-direct-review-cell></td><td data-direct-note-cell></td>' : ''}
      `;
      return row;
    };

    const firstRow = createRow(true);
    const docCell = firstRow.querySelector('[data-direct-doc-cell]');
    if (!docCell) return firstRow;

    const docLabel = preset.label || `Dokumen ${index}`;
    const hasExistingFiles = Array.isArray(preset.files) && preset.files.length > 0;
    const docItem = createDocItem(docKey, docLabel, {
      withInitialRow: !hasExistingFiles,
    });
    docItem.dataset.docKey = docKey;
    const paramAdd = docItem.querySelector('[data-param-add]');
    if (paramAdd) paramAdd.remove();
    attachDocUploadHandlers(docItem);
    docCell.appendChild(docItem);
    if (Array.isArray(preset.files) && preset.files.length) {
      renderExistingFiles(docItem, preset.files);
    }
    const uploadWrap = docItem.querySelector('[data-doc-upload]');
    if (uploadWrap) {
      const countSelectedFiles = () => {
        return Array.from(uploadWrap.querySelectorAll('input[type="file"]'))
          .reduce((total, input) => total + (input.files ? input.files.length : 0), 0);
      };
      const countExistingFiles = () => {
        return docItem.querySelectorAll('[data-existing-file-row]').length;
      };
      const getTotalFiles = () => countSelectedFiles() + countExistingFiles();
      const removeDirectGroup = () => {
        const tbody = firstRow.parentElement;
        const groupRows = Array.from(tbody?.querySelectorAll(`tr[data-direct-group="${groupId}"]`) || []);
        groupRows.forEach((r) => r.remove());
        renumberDirectDocs(tbody);
        const remaining = Array.from(tbody?.querySelectorAll('tr[data-direct-doc]') || []);
        if (tbody && remaining.length === 0) {
          tbody.innerHTML = `
            <tr data-direct-empty>
              <td colspan="${IS_REVISION_VIEW ? 8 : 6}" class="text-muted small">Belum ada dokumen.</td>
            </tr>
          `;
          const card = tbody.closest('[data-card]');
          const addBtn = card?.querySelector('[data-add-table="direct"]');
          if (addBtn) addBtn.classList.remove('d-none');
        }
      };

      uploadWrap.addEventListener('click', (event) => {
        const removeBtn = event.target.closest('[data-upload-remove]');
        if (!removeBtn) return;
        const row = removeBtn.closest('[data-upload-row]');
        if (row) {
          row.remove();
        }
        if (getTotalFiles() === 0) {
          removeDirectGroup();
          return;
        }
        const remainingRows = uploadWrap.querySelectorAll('[data-upload-row]');
        if (remainingRows.length === 0) {
          const rowNew = createUploadRow();
          uploadWrap.insertBefore(rowNew, uploadWrap.firstChild);
        }
      });

      docItem.addEventListener('existing-files-updated', () => {
        if (getTotalFiles() === 0) {
          removeDirectGroup();
        }
      });
    }

    const updateRowspans = () => {
      const rows = Array.from(firstRow.parentElement?.querySelectorAll(`tr[data-direct-group="${groupId}"]`) || []);
      const rowCount = rows.length || 1;
      const noCell = firstRow.querySelector('[data-direct-doc-index]');
      if (noCell) noCell.setAttribute('rowspan', String(rowCount));
      const docCellLocal = firstRow.querySelector('[data-direct-doc-cell]');
      if (docCellLocal) docCellLocal.setAttribute('rowspan', String(rowCount));
    };

    const addParameterBtn = document.createElement('button');
    addParameterBtn.type = 'button';
    addParameterBtn.className = 'btn btn-outline-primary btn-sm mt-2';
    addParameterBtn.innerHTML = '<i class="bi bi-plus me-1"></i> Tambah Parameter';
    addParameterBtn.setAttribute('aria-label', 'Tambah Parameter');
    addParameterBtn.setAttribute('title', 'Tambah Parameter');
    docCell.appendChild(addParameterBtn);

    const addParameterGroupRow = (paramPreset = {}) => {
      const existingRows = Array.from(firstRow.parentElement?.querySelectorAll(`tr[data-direct-group="${groupId}"][data-direct-param-group]`) || []);
      const isFirst = existingRows.length === 0;
      const row = isFirst ? firstRow : createRow(false);

      if (!isFirst && firstRow.parentElement) {
        const lastRow = existingRows[existingRows.length - 1];
        if (lastRow) {
          lastRow.after(row);
        } else {
          firstRow.parentElement.appendChild(row);
        }
      }

      row.dataset.directParamGroup = 'true';
      if (paramPreset.docId) {
        row.dataset.directDocId = String(paramPreset.docId);
      }

      const paramCell = row.querySelector('[data-direct-param-cell]');
      const methodCell = row.querySelector('[data-direct-method-cell]');
      const lokasiCell = row.querySelector('[data-direct-lokasi-cell]');
      const sesuaiCell = row.querySelector('[data-direct-sesuai-cell]');
      const reviewCell = IS_REVISION_VIEW ? row.querySelector('[data-direct-review-cell]') : null;
      const noteCell = IS_REVISION_VIEW ? row.querySelector('[data-direct-note-cell]') : null;
      if (!paramCell || !methodCell || !lokasiCell || !sesuaiCell) return;
      if (IS_REVISION_VIEW && (!reviewCell || !noteCell)) return;

      const groupLabelIndex = existingRows.length + 1;
      const paramBlock = document.createElement('div');
      paramBlock.className = 'doc-block';
      paramBlock.dataset.directParamBlock = `group-${groupLabelIndex}`;
      paramBlock.innerHTML = `<div class="doc-label">Parameter ${groupLabelIndex}</div>`;
      paramCell.appendChild(paramBlock);

      const methodBlock = document.createElement('div');
      methodBlock.className = 'doc-block';
      methodBlock.dataset.directMethodBlock = `group-${groupLabelIndex}`;
      methodBlock.innerHTML = '<div class="doc-label">Metode</div>';
      const methodList = document.createElement('div');
      methodList.className = 'direct-param-list';
      methodList.innerHTML = '<div class="doc-row d-flex justify-content-center"><span class="method-chip">Direct</span></div>';
      methodBlock.appendChild(methodList);
      methodCell.appendChild(methodBlock);

      const locBlock = document.createElement('div');
      locBlock.className = 'doc-block';
      locBlock.dataset.directLokasiBlock = `group-${groupLabelIndex}`;
      locBlock.innerHTML = '<div class="doc-label">Lokasi</div>';
      const locList = document.createElement('div');
      locList.className = 'direct-param-list';
      locList.dataset.directLokasiList = 'true';
      locBlock.appendChild(locList);
      lokasiCell.appendChild(locBlock);

      const sesuaiBlock = document.createElement('div');
      sesuaiBlock.className = 'doc-block';
      sesuaiBlock.dataset.directSesuaiBlock = `group-${groupLabelIndex}`;
      sesuaiBlock.innerHTML = '<div class="doc-label">Sesuai</div>';
      const sesuaiList = document.createElement('div');
      sesuaiList.className = 'direct-param-list';
      sesuaiList.dataset.directSesuaiList = 'true';
      sesuaiBlock.appendChild(sesuaiList);
      sesuaiCell.appendChild(sesuaiBlock);

      let reviewList = null;
      let noteList = null;
      let reviewBlock = null;
      let noteBlock = null;
      if (IS_REVISION_VIEW) {
        reviewBlock = document.createElement('div');
        reviewBlock.className = 'doc-block';
        reviewBlock.dataset.directReviewBlock = `group-${groupLabelIndex}`;
        reviewBlock.innerHTML = '<div class="doc-label">Verifikasi</div>';
        reviewList = document.createElement('div');
        reviewList.className = 'direct-param-list';
        reviewList.dataset.directReviewList = 'true';
        reviewBlock.appendChild(reviewList);
        reviewCell.appendChild(reviewBlock);

        noteBlock = document.createElement('div');
        noteBlock.className = 'doc-block';
        noteBlock.dataset.directNoteBlock = `group-${groupLabelIndex}`;
        noteBlock.innerHTML = '<div class="doc-label">Catatan Revisi</div>';
        noteList = document.createElement('div');
        noteList.className = 'direct-param-list';
        noteList.dataset.directNoteList = 'true';
        noteBlock.appendChild(noteList);
        noteCell.appendChild(noteBlock);
      }

      const rowId = `param-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`;
      const { wrap, select, removeBtn } = createParamRow(params, rowId);
      paramBlock.appendChild(wrap);
      select.__baseParams = params;
      select.__allowedParams = new Set(params.map((param) => String(param.id)));

      const addLocationBtn = document.createElement('button');
      addLocationBtn.type = 'button';
      addLocationBtn.className = 'btn btn-outline-secondary btn-sm mt-2';
      addLocationBtn.innerHTML = '<i class="bi bi-geo-alt me-1"></i> Tambah Lokasi';
      addLocationBtn.disabled = !paramPreset.paramId;
      paramBlock.appendChild(addLocationBtn);

      const syncRowHeight = () => {
        const blocks = [paramBlock, methodBlock, locBlock, sesuaiBlock];
        if (reviewBlock) blocks.push(reviewBlock);
        if (noteBlock) blocks.push(noteBlock);
        blocks.forEach((block) => {
          block.style.minHeight = '';
        });
        const maxHeight = Math.max(...blocks.map((block) => block.offsetHeight));
        blocks.forEach((block) => {
          block.style.minHeight = `${maxHeight}px`;
        });
      };

      const updateDirectSuitability = (value) => {
        const switchInputs = Array.from(
          sesuaiList.querySelectorAll('[data-direct-location-switch] input')
        );
        if (!switchInputs.length) return;

        if (!value) {
          switchInputs.forEach((switchInput) => {
            switchInput.checked = true;
            switchInput.classList.remove('req-switch-invalid');
          });
          return;
        }

        if (String(select.dataset.customValue || '') === String(value)) {
          switchInputs.forEach((switchInput) => {
            switchInput.checked = false;
            switchInput.classList.add('req-switch-invalid');
          });
          return;
        }

        if (!select.__allowedParams?.has(value)) {
          switchInputs.forEach((switchInput) => {
            switchInput.checked = false;
            switchInput.classList.add('req-switch-invalid');
          });
          return;
        }

        const paramMeta = (Array.isArray(select.__baseParams) ? select.__baseParams : [])
          .find((param) => String(param.id) === String(value));
        const totalAllowedQty = Number.parseInt(String(paramMeta?.qty ?? '0'), 10);

        const card = select.closest('[data-card]');
        const allSelects = Array.from(card?.querySelectorAll('select[data-param-select]') || []);
        let usedByOthers = 0;

        allSelects.forEach((otherSelect) => {
          if (otherSelect === select) return;
          if (String(otherSelect.value || '') !== String(value)) return;

          const directRow = otherSelect.closest('tr[data-direct-group]');
          if (directRow) {
            usedByOthers += directRow.querySelectorAll('[data-direct-location-row]').length || 1;
            return;
          }

          const otherRow = otherSelect.closest('[data-row]');
          const otherDocId = otherSelect.dataset.docId || '';
          const otherRowId = otherSelect.dataset.paramRowId || '';
          const qtyInput = otherRow?.querySelector(
            `[data-qty-docs] [data-doc-id="${otherDocId}"][data-param-row-id="${otherRowId}"] input`
          );
          const qtyValue = Number.parseInt(String(qtyInput?.value || '1'), 10);
          usedByOthers += Number.isFinite(qtyValue) && qtyValue > 0 ? qtyValue : 1;
        });

        const remainingForCurrentGroup = Number.isFinite(totalAllowedQty)
          ? Math.max(0, totalAllowedQty - usedByOthers)
          : 0;

        switchInputs.forEach((switchInput, index) => {
          const isAllowedForLocation = index < remainingForCurrentGroup;
          switchInput.checked = isAllowedForLocation;
          switchInput.classList.toggle('req-switch-invalid', !isAllowedForLocation);
        });
      };

      const ensureOption = (value, label = '') => {
        if (!value || value === EXTRA_OPTION) return;
        const exists = Array.from(select.options).some((option) => option.value === value);
        if (exists) return;
        const extraOption = Array.from(select.options).find((option) => option.value === EXTRA_OPTION);
        const newOption = document.createElement('option');
        newOption.value = value;
        newOption.textContent = label || value;
        if (extraOption) {
          select.insertBefore(newOption, extraOption);
        } else {
          select.appendChild(newOption);
        }
      };

      const addLocationItem = (locPreset = {}) => {
        const nextLocIndex = locList.querySelectorAll('[data-direct-location-row]').length + 1;
        const locRow = document.createElement('div');
        locRow.className = 'doc-row d-flex align-items-center gap-2';
        locRow.dataset.directLocationRow = 'true';
        if (locPreset.lokasiId) {
          locRow.dataset.directLokasiId = String(locPreset.lokasiId);
        }
        locRow.innerHTML = `
          <input type="text" class="form-control form-control-sm" placeholder="Nama lokasi" data-direct-location-input value="Lokasi ${nextLocIndex}">
          <button class="btn btn-outline-danger btn-sm" type="button" data-direct-location-remove aria-label="Hapus lokasi">
            <i class="bi bi-trash"></i>
          </button>
        `;

        const switchWrap = createReqSwitch(`switch-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`);
        switchWrap.dataset.directLocationSwitch = 'true';
        const switchInput = switchWrap.querySelector('input');
        if (typeof locPreset.switchOn === 'boolean' && switchInput) {
          switchInput.checked = locPreset.switchOn;
          switchInput.classList.toggle('req-switch-invalid', !locPreset.switchOn);
        }
        const reviewWrap = IS_REVISION_VIEW
          ? createReviewSwitch(`review-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`, locPreset.reviewStatus || '')
          : null;
        if (reviewWrap) {
          reviewWrap.dataset.directLocationReview = 'true';
        }
        const noteWrap = IS_REVISION_VIEW
          ? createReviewNote(`note-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 7)}`, locPreset.note || locPreset.catatan || '')
          : null;
        if (noteWrap) {
          noteWrap.dataset.directLocationNote = 'true';
        }

        const locInput = locRow.querySelector('[data-direct-location-input]');
        if (locPreset.name && locInput) {
          locInput.value = locPreset.name;
        }

        const removeBtn = locRow.querySelector('[data-direct-location-remove]');
        removeBtn?.addEventListener('click', () => {
          const locRows = Array.from(locList.querySelectorAll('[data-direct-location-row]'));
          const switchRows = Array.from(sesuaiList.querySelectorAll('[data-direct-location-switch]'));
          const reviewRows = IS_REVISION_VIEW ? Array.from(reviewList?.querySelectorAll('[data-direct-location-review]') || []) : [];
          const noteRows = IS_REVISION_VIEW ? Array.from(noteList?.querySelectorAll('[data-direct-location-note]') || []) : [];
          const idx = locRows.indexOf(locRow);
          if (idx > -1) {
            switchRows[idx]?.remove();
            reviewRows[idx]?.remove();
            noteRows[idx]?.remove();
          }
          locRow.remove();
          requestAnimationFrame(syncRowHeight);
        });

        locList.appendChild(locRow);
        sesuaiList.appendChild(switchWrap);
        reviewWrap && reviewList?.appendChild(reviewWrap);
        noteWrap && noteList?.appendChild(noteWrap);
        updateDirectSuitability(select.value);
        requestAnimationFrame(syncRowHeight);
      };

      if (paramPreset.paramId) {
        ensureOption(String(paramPreset.paramId), paramPreset.paramName || '');
        select.value = String(paramPreset.paramId);
      }

      updateParamOptions(paramBlock);
      updateDirectSuitability(select.value);
      const rememberPrevValue = () => {
        select.dataset.prevValue = select.value || '';
      };

      select.__applySelection = (value) => {
        if (value === EXTRA_OPTION) return;
        let resolved = value;
        let label = '';
        if (value && typeof value === 'object') {
          resolved = value.id ?? '';
          label = value.name ?? '';
        }
        if (!resolved) {
          select.value = '';
          delete select.dataset.customValue;
          delete select.dataset.customLabel;
        } else {
          ensureOption(String(resolved), label);
          if (label) {
            select.dataset.customValue = String(resolved);
            select.dataset.customLabel = label;
          } else if (String(select.dataset.customValue || '') === String(resolved)) {
            delete select.dataset.customValue;
            delete select.dataset.customLabel;
          }
          select.value = String(resolved);
        }
        select.dataset.prevValue = select.value || '';
        updateParamOptions(paramBlock);
        addLocationBtn.disabled = !select.value;
        updateDirectSuitability(select.value);
        select.dispatchEvent(new Event('change', { bubbles: true }));
      };

      select.addEventListener('focus', rememberPrevValue);
      select.addEventListener('mousedown', rememberPrevValue);
      select.addEventListener('change', () => {
        if (select.value === EXTRA_OPTION) {
          openExtraModal(select);
          return;
        }
        if (String(select.dataset.customValue || '') !== String(select.value || '')) {
          delete select.dataset.customValue;
          delete select.dataset.customLabel;
        }
        select.dataset.prevValue = select.value || '';
        updateParamOptions(paramBlock);
        addLocationBtn.disabled = !select.value;
        updateDirectSuitability(select.value);
      });

      addLocationBtn.addEventListener('click', () => {
        addLocationItem();
      });

      removeBtn.addEventListener('click', () => {
        const rows = Array.from(firstRow.parentElement?.querySelectorAll(`tr[data-direct-group="${groupId}"]`) || []);
        const hasDocCell = !!row.querySelector('[data-direct-doc-cell]');
        if (hasDocCell && rows.length > 1) {
          const nextRow = rows.find((r) => r !== row);
          if (nextRow) {
            const noCell = row.querySelector('[data-direct-doc-index]');
            const docCellLocal = row.querySelector('[data-direct-doc-cell]');
            if (noCell) nextRow.prepend(noCell);
            if (docCellLocal) nextRow.insertBefore(docCellLocal, nextRow.children[1] || null);
          }
        }
        row.remove();
        updateRowspans();
      });

      if (Array.isArray(paramPreset.locations) && paramPreset.locations.length) {
        paramPreset.locations.forEach((loc) => addLocationItem(loc));
      }

      if (typeof ResizeObserver !== 'undefined') {
        const ro = new ResizeObserver(syncRowHeight);
        ro.observe(paramBlock);
        ro.observe(locBlock);
        ro.observe(sesuaiBlock);
        if (reviewBlock) ro.observe(reviewBlock);
        if (noteBlock) ro.observe(noteBlock);
      }

      requestAnimationFrame(syncRowHeight);
      updateRowspans();
    };

    addParameterBtn.addEventListener('click', () => {
      addParameterGroupRow();
    });

    const initLocations = (payload = preset) => {
      const paramGroups = new Map();
      const rawLocations = Array.isArray(payload.locations) ? payload.locations : [];
      rawLocations.forEach((loc) => {
        const paramsByLocation = Array.isArray(loc.params) ? loc.params : [];
        paramsByLocation.forEach((paramItem) => {
          const key = String(paramItem.parameter_id || paramItem.parameter_name || '');
          if (!key) return;
          if (!paramGroups.has(key)) {
            paramGroups.set(key, {
              docId: loc.docId || null,
              paramId: paramItem.parameter_id || null,
              paramName: paramItem.parameter_name || '',
              locations: [],
            });
          }
          paramGroups.get(key).locations.push({
            lokasiId: loc.lokasiId || null,
            name: loc.name || '',
            switchOn: typeof paramItem.is_sesuai === 'boolean' ? paramItem.is_sesuai : true,
            reviewStatus: paramItem.review_status || '',
            note: paramItem.catatan || '',
          });
        });
      });

      if (paramGroups.size) {
        Array.from(paramGroups.values()).forEach((group) => {
          addParameterGroupRow(group);
        });
      }
    };
    firstRow.__initLocations = initLocations;

    return firstRow;
  };

  window.Pengujian = window.Pengujian || {};
  window.Pengujian.addRow = (btn) => {
    const card = btn?.closest('[data-card]');
    const tableWrap = btn?.closest('[data-table-wrap]');
    const tbody = tableWrap?.querySelector('[data-order-table]');
    if (!card || !tbody) return;
    const params = parseCardJson(card, 'data-order-params', []) || [];
    const nextIndex = tbody.querySelectorAll('[data-row]').length + 1;
    tbody.insertAdjacentHTML('beforeend', buildRow(params, nextIndex));
    const rows = tbody.querySelectorAll('[data-row]');
    const newRow = rows[rows.length - 1];
    const tableType = tbody.dataset.tableType || 'indirect';
    setupRow(newRow, params, tbody, tableType);
    updateRowIndexes(tbody);
  };
  window.Pengujian.addDocFromHeader = (btn) => {
    const card = btn?.closest('[data-card]');
    const tableWrap = btn?.closest('[data-table-wrap]');
    const tbody = tableWrap?.querySelector('[data-order-table]');
    const row = tbody?.querySelector('[data-row]');
    if (!row || !tbody) return;
    if (row.dataset.rowInit !== 'true') {
      const params = parseCardJson(card, 'data-order-params', []) || [];
      const tableType = tbody.dataset.tableType || 'indirect';
      setupRow(row, params, tbody, tableType);
    }
    row.__addDoc?.();
  };

  const parseExisting = (card) => {
    return parseCardJson(card, 'data-existing', null);
  };

  const filterExistingByType = (existing, type) => {
    if (!existing || !Array.isArray(existing.lokasi)) return null;
    const isDirect = type === 'direct';
    const lokasi = existing.lokasi.map((lokasiItem) => {
      const dokumen = (lokasiItem.dokumen || []).map((docItem) => {
        const filteredParams = (docItem.parameters || []).filter((param) => {
          return Boolean(param?.is_direct) === isDirect;
        });
        if (!filteredParams.length) return null;
        return {
          ...docItem,
          parameters: filteredParams,
        };
      }).filter(Boolean);
      if (!dokumen.length) return null;
      return {
        ...lokasiItem,
        dokumen,
      };
    }).filter(Boolean);
    if (!lokasi.length) return null;
    return {
      ...existing,
      lokasi,
    };
  };

  const hasExistingData = (existing) => {
    if (!existing || !Array.isArray(existing.lokasi)) return false;
    return existing.lokasi.some((lokasi) => Array.isArray(lokasi?.dokumen) && lokasi.dokumen.length > 0);
  };

  const buildPayloadFromExisting = (existing) => {
    if (!hasExistingData(existing)) {
      return { lokasi: [] };
    }

    return {
      lokasi: (existing.lokasi || []).map((lokasi) => ({
        id: lokasi?.id || null,
        nama: lokasi?.nama || '',
        dokumen: (lokasi?.dokumen || []).map((dokumen) => ({
          id: dokumen?.id || null,
          key: dokumen?.id ? `doc-${dokumen.id}` : `doc-${Math.random().toString(36).slice(2, 8)}`,
          label: dokumen?.label || 'Dokumen',
          existing_file_ids: (dokumen?.files || []).map((file) => file?.id).filter(Boolean),
          parameters: (dokumen?.parameters || []).map((param) => ({
            parameter_id: Number(param?.parameter_id || 0),
            qty: Number(param?.qty || 1),
            is_direct: !!param?.is_direct,
            is_sesuai: !!param?.is_sesuai,
          })).filter((param) => param.parameter_id > 0),
        })),
      })),
    };
  };

  const applyExistingData = (card, params, tbody, tableType) => {
    const existing = parseExisting(card);
    const filtered = filterExistingByType(existing, tableType);
    if (!filtered || !Array.isArray(filtered.lokasi) || !filtered.lokasi.length) return false;
    if (!tbody) return false;

    filtered.lokasi.forEach((lokasi, index) => {
      let row = null;
      if (index === 0) {
        row = tbody.querySelector('[data-row]');
      } else {
        const nextIndex = tbody.querySelectorAll('[data-row]').length + 1;
        tbody.insertAdjacentHTML('beforeend', buildRow(params, nextIndex));
        const rows = tbody.querySelectorAll('[data-row]');
        row = rows[rows.length - 1];
        setupRow(row, params, tbody, tableType);
      }

      if (!row) return;
      row.dataset.locationId = lokasi.id || '';
      const locationInput = row.querySelector('[data-location-input]');
      if (locationInput && lokasi.nama) {
        locationInput.value = lokasi.nama;
      }

      if (Array.isArray(lokasi.dokumen)) {
        lokasi.dokumen.forEach((doc) => {
          row.__addDoc?.({
            key: `doc-${doc.id}`,
            id: doc.id,
            label: doc.label,
            files: doc.files || [],
            parameters: doc.parameters || [],
          });
        });
      }
    });

    updateRowIndexes(tbody);
    return true;
  };

  const collectPayload = (card) => {
    const payload = { lokasi: [] };
    const existing = parseExisting(card);
    const lokasiMap = new Map();
    const getLokasiKey = (id, name) => {
      if (id) return `id:${id}`;
      return `name:${String(name || '').toLowerCase().trim()}`;
    };
    const ensureLokasiEntry = (id, name) => {
      const key = getLokasiKey(id, name);
      if (!lokasiMap.has(key)) {
        lokasiMap.set(key, {
          id: id || null,
          nama: name || '',
          dokumen: [],
        });
      }
      return lokasiMap.get(key);
    };
    const mergeDocInto = (lokasiEntry, doc) => {
      if (!doc) return;
      const docId = doc.id ? Number(doc.id) : null;
      let existing = null;
      if (docId) {
        existing = lokasiEntry.dokumen.find((item) => item.id === docId);
      }
      if (!existing) {
        existing = lokasiEntry.dokumen.find((item) => (!item.id && item.key === doc.key && item.label === doc.label));
      }
      if (!existing) {
        lokasiEntry.dokumen.push({
          id: docId,
          key: doc.key,
          label: doc.label,
          parameters: Array.isArray(doc.parameters) ? doc.parameters : [],
          existing_file_ids: Array.isArray(doc.existing_file_ids) ? doc.existing_file_ids : [],
        });
        return;
      }
      const existingFileIds = new Set(existing.existing_file_ids || []);
      (doc.existing_file_ids || []).forEach((id) => existingFileIds.add(id));
      existing.existing_file_ids = Array.from(existingFileIds);
      existing.parameters = (existing.parameters || []).concat(doc.parameters || []);
    };
    const tbodies = Array.from(card.querySelectorAll('[data-order-table]'));
    if (!tbodies.length) return payload;

    tbodies.forEach((tbody) => {
      const tableWrap = tbody.closest('[data-table-wrap]');
      if (tableWrap && tableWrap.classList.contains('d-none')) {
        return;
      }
      const tableType = tbody.dataset.tableType || 'indirect';
      if (tableType === 'direct') {
        const groups = new Map();
        Array.from(tbody.querySelectorAll('tr[data-direct-group]')).forEach((row) => {
          const groupId = row.dataset.directGroup || '';
          if (!groupId) return;
          if (!groups.has(groupId)) groups.set(groupId, []);
          groups.get(groupId).push(row);
        });

        const directLokasiMap = new Map();
        groups.forEach((rows) => {
          const firstRow = rows.find((r) => r.querySelector('[data-direct-doc-cell]')) || rows[0];
          const docItem = firstRow?.querySelector('[data-doc-item]');
          const docKey = firstRow?.dataset.docKey || docItem?.dataset.docKey || docItem?.dataset.docId || '';
          if (!docKey) return;
          const docLabel = docItem?.querySelector('.doc-label')?.textContent || 'Dokumen';
          let existingFiles = [];
          try {
            existingFiles = JSON.parse(docItem?.dataset.existingFiles || '[]') || [];
          } catch (error) {
            existingFiles = [];
          }

          rows.forEach((row) => {
            const paramBlock = row.querySelector('[data-direct-param-block]');
            const lokasiList = row.querySelector('[data-direct-lokasi-list]');
            const sesuaiList = row.querySelector('[data-direct-sesuai-list]');
            if (!paramBlock || !lokasiList || !sesuaiList) return;

            const paramSelect = paramBlock.querySelector('select[data-param-select]');
            const paramId = paramSelect?.value;
            if (!paramId || paramId === EXTRA_OPTION) return;

            const locRows = Array.from(lokasiList.querySelectorAll('[data-direct-location-row]'));
            const switchRows = Array.from(sesuaiList.querySelectorAll('[data-direct-location-switch]'));
            const docDbId = row.dataset.directDocId || null;

            locRows.forEach((locRow, idx) => {
              const locInput = locRow.querySelector('[data-direct-location-input]');
              const namaLokasi = (locInput?.value || '').trim();
              if (!namaLokasi) return;
              const lokasiIdRaw = locRow.dataset.directLokasiId || '';
              const lokasiId = lokasiIdRaw ? Number(lokasiIdRaw) : null;
              const switchInput = switchRows[idx]?.querySelector('input');

              const lokasiKey = `name:${namaLokasi.toLowerCase()}`;
              if (!directLokasiMap.has(lokasiKey)) {
                directLokasiMap.set(lokasiKey, {
                  id: lokasiId || null,
                  nama: namaLokasi,
                  dokumen: [],
                });
              } else if (lokasiId) {
                const existingLokasi = directLokasiMap.get(lokasiKey);
                if (existingLokasi && !existingLokasi.id) {
                  existingLokasi.id = lokasiId;
                }
              }

              const lokasiEntry = directLokasiMap.get(lokasiKey);
              mergeDocInto(lokasiEntry, {
                id: docDbId ? Number(docDbId) : null,
                key: docKey,
                label: docLabel,
                parameters: [{
                  parameter_id: Number(paramId),
                  qty: 1,
                  is_direct: true,
                  is_sesuai: !!switchInput?.checked,
                }],
                existing_file_ids: existingFiles.map((file) => file.id).filter(Boolean),
              });
            });
          });
        });
        Array.from(directLokasiMap.values()).forEach((lokasiEntry) => {
          const entry = ensureLokasiEntry(lokasiEntry.id, lokasiEntry.nama);
          lokasiEntry.dokumen.forEach((doc) => mergeDocInto(entry, doc));
        });
        return;
      }
      const rows = Array.from(tbody.querySelectorAll('[data-row]'));

      rows.forEach((row) => {
        const locationInput = row.querySelector('[data-location-input]');
        const namaLokasi = (locationInput?.value || '').trim();
        if (!namaLokasi) return;

        const lokasiId = row.dataset.locationId ? Number(row.dataset.locationId) : null;
        const lokasi = ensureLokasiEntry(lokasiId, namaLokasi);

        const docItems = Array.from(row.querySelectorAll('[data-doc-item]'));
        docItems.forEach((docItem) => {
          const docKey = docItem.dataset.docKey || docItem.dataset.docId;
          const docDbId = docItem.dataset.docDbId || null;
          const label = docItem.querySelector('.doc-label')?.textContent || '';
          let existingFiles = [];
          try {
            existingFiles = JSON.parse(docItem.dataset.existingFiles || '[]') || [];
          } catch (error) {
            existingFiles = [];
          }

          const paramBlock = row.querySelector(`[data-param-docs] [data-doc-id="${docKey}"]`);
          const params = [];
          if (paramBlock) {
            const paramRows = Array.from(paramBlock.querySelectorAll('[data-param-row-id]'));
            paramRows.forEach((wrap) => {
              const select = wrap.querySelector('select[data-param-select]');
              const paramId = select?.value;
              if (!paramId || paramId === EXTRA_OPTION) return;
              const rowId = wrap.dataset.paramRowId;
              const qtyInput = row.querySelector(`[data-qty-docs] [data-doc-id="${docKey}"][data-param-row-id="${rowId}"] input`);
              const switchInput = row.querySelector(`[data-switch-docs] [data-doc-id="${docKey}"][data-param-row-id="${rowId}"] input`);
              params.push({
                parameter_id: Number(paramId),
                qty: Number(qtyInput?.value || 1),
                is_direct: tableType === 'direct',
                is_sesuai: !!switchInput?.checked,
              });
            });
          }

          mergeDocInto(lokasi, {
            id: docDbId ? Number(docDbId) : null,
            key: docKey,
            label,
            parameters: params,
            existing_file_ids: existingFiles.map((file) => file.id).filter(Boolean),
          });
        });
      });
    });

    payload.lokasi = Array.from(lokasiMap.values());
    if (!payload.lokasi.length && hasExistingData(existing)) {
      return buildPayloadFromExisting(existing);
    }
    return payload;
  };

  const collectFiles = (card) => {
    const fileGroups = [];
    const docItems = Array.from(card.querySelectorAll('[data-doc-item]'));
    docItems.forEach((docItem) => {
      const docKey = docItem.dataset.docKey || docItem.dataset.docId;
      const inputs = Array.from(docItem.querySelectorAll('input[type="file"]'));
      inputs.forEach((input) => {
        const files = Array.from(input.files || []);
        files.forEach((file) => {
          fileGroups.push({ docKey, file });
        });
      });
    });
    return fileGroups;
  };

  const setCardLoading = (card, isLoading, activeBtn = null) => {
    const buttons = card.querySelectorAll('button[data-action-save], button[data-action-submit]');
    if (isLoading) {
      window.WorkflowLoading?.lockGroup(buttons, activeBtn);
      return;
    }
    window.WorkflowLoading?.releaseGroup(buttons);
  };

  const notify = (type, text) => {
    if (window.Swal) {
      const icon = type === 'error' ? 'error' : 'success';
      return Swal.fire({
        icon,
        title: type === 'error' ? 'Gagal' : 'Berhasil',
        text,
        confirmButtonText: 'OK',
      });
    }
    alert(text);
    return Promise.resolve();
  };

  const countDocFiles = (docItem) => {
    const inputs = Array.from(docItem.querySelectorAll('input[type="file"]'));
    const incomingCount = inputs.reduce((total, input) => {
      return total + (input.files ? input.files.length : 0);
    }, 0);
    let existingCount = docItem.querySelectorAll('[data-existing-file-row]').length;
    if (!existingCount) {
      try {
        const existingFiles = JSON.parse(docItem.dataset.existingFiles || '[]') || [];
        existingCount = existingFiles.length;
      } catch (error) {
        existingCount = 0;
      }
    }
    return existingCount + incomingCount;
  };

  const hasUnfilledRequiredUploads = (docItem) => {
    const rows = Array.from(docItem.querySelectorAll('[data-upload-row][data-upload-required="true"]'));
    return rows.some((row) => {
      const input = row.querySelector('input[type="file"]');
      return !input || !input.files || input.files.length === 0;
    });
  };

  const validateDocUploads = (card) => {
    const missing = [];
    const docItems = Array.from(card.querySelectorAll('[data-table-wrap]:not(.d-none) [data-doc-item]'));
    if (!docItems.length) {
      const existing = parseExisting(card);
      if (!hasExistingData(existing)) {
        return 'Upload dokumen terlebih dahulu.';
      }

      (existing.lokasi || []).forEach((lokasi) => {
        (lokasi?.dokumen || []).forEach((dokumen) => {
          const label = dokumen?.label || 'Dokumen';
          const totalFiles = Array.isArray(dokumen?.files) ? dokumen.files.length : 0;
          if (totalFiles < 1) {
            missing.push(`${lokasi?.nama || 'Lokasi'} - ${label}`);
          }
        });
      });

      if (!missing.length) {
        return null;
      }

      return `Dokumen berikut belum diupload file: ${missing.join(', ')}. Minimal 1 file per dokumen.`;
    }
    docItems.forEach((docItem) => {
      const label = docItem.querySelector('.doc-label')?.textContent || 'Dokumen';
      if (hasUnfilledRequiredUploads(docItem)) {
        missing.push(`${label} (baris upload belum lengkap)`);
        return;
      }
      if (countDocFiles(docItem) < 1) {
        missing.push(label);
      }
    });

    if (!missing.length) {
      return null;
    }

    return `Dokumen berikut belum diupload file: ${missing.join(', ')}. Minimal 1 file per dokumen.`;
  };

  const confirmAction = async (text) => {
    if (window.Swal) {
      const result = await Swal.fire({
        title: 'Konfirmasi',
        text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
      });
      return result.isConfirmed;
    }
    return confirm(text);
  };

  const parseApiErrorMessage = (data, fallback) => {
    if (!data || typeof data !== 'object') {
      return fallback;
    }

    const primary = (data.message || '').toString().trim();
    const errors = data.errors && typeof data.errors === 'object' ? data.errors : null;
    if (!errors) {
      return primary || fallback;
    }

    const firstField = Object.keys(errors)[0];
    const firstValue = firstField ? errors[firstField] : null;
    let firstDetail = '';
    if (Array.isArray(firstValue)) {
      firstDetail = (firstValue[0] || '').toString().trim();
    } else if (typeof firstValue === 'string') {
      firstDetail = firstValue.trim();
    }

    if (primary && firstDetail && primary !== firstDetail) {
      return `${primary} ${firstDetail}`;
    }
    return firstDetail || primary || fallback;
  };

  const sendPayload = async (card, url) => {
    const payload = collectPayload(card);
    const files = collectFiles(card);
    const formData = new FormData();
    formData.append('payload', JSON.stringify(payload));
    const fileMeta = [];
    files.forEach(({ docKey, file }, idx) => {
      if (!docKey) return;
      const safeKey = String(docKey).replace(/[^a-zA-Z0-9_-]/g, '');
      const field = `upload_${safeKey}_${idx}_${Math.random().toString(36).slice(2, 7)}`;
      formData.append(field, file);
      fileMeta.push({ field, docKey });
    });
    formData.append('files_meta', JSON.stringify(fileMeta));

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: formData,
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(parseApiErrorMessage(data, 'Gagal menyimpan data pengujian.'));
    }
    return data;
  };

  document.querySelectorAll('[data-card]').forEach((card) => {
    const params = parseCardJson(card, 'data-order-params', []) || [];
    const existing = parseExisting(card);

    const tableWraps = Array.from(card.querySelectorAll('[data-table-wrap]'));
    const addTableButtons = Array.from(card.querySelectorAll('[data-add-table]'));
    const removeTableButtons = Array.from(card.querySelectorAll('[data-remove-table]'));
    const saveBtn = card.querySelector('[data-action-save]');
    const submitBtn = card.querySelector('[data-action-submit]');
    const readyForBap = card.getAttribute('data-ready-for-bap') === '1';

    const hasVisibleTable = () => {
      if (readyForBap) return true;
      return tableWraps.some((wrap) => !wrap.classList.contains('d-none')) || hasExistingData(existing);
    };

    const hasFilledParams = () => {
      if (readyForBap) return true;
      if (hasExistingData(existing)) return true;
      return tableWraps.some((wrap) => {
        if (wrap.classList.contains('d-none')) return false;
        const selects = Array.from(wrap.querySelectorAll('select[data-param-select]'));
        return selects.some((select) => select.value && select.value !== EXTRA_OPTION);
      });
    };

    const allParamsSelected = () => {
      if (readyForBap) return true;
      if (hasExistingData(existing)) return true;
      let hasAny = false;
      let valid = true;
      tableWraps.forEach((wrap) => {
        if (wrap.classList.contains('d-none')) return;
        const selects = Array.from(wrap.querySelectorAll('select[data-param-select]'));
        selects.forEach((select) => {
          hasAny = true;
          if (!select.value || select.value === EXTRA_OPTION) {
            valid = false;
          }
        });
      });
      return hasAny && valid;
    };

    const hasDocUploads = () => {
      if (readyForBap) return true;
      if (hasExistingData(existing)) return true;
      return tableWraps.some((wrap) => {
        if (wrap.classList.contains('d-none')) return false;
        const docItems = Array.from(wrap.querySelectorAll('[data-doc-item]'));
        if (!docItems.length) return false;
        return docItems.every((docItem) => countDocFiles(docItem) > 0);
      });
    };

    const allRequiredUploadsFilled = () => {
      if (readyForBap) return true;
      if (hasExistingData(existing)) return true;
      let hasAny = false;
      let valid = true;
      tableWraps.forEach((wrap) => {
        if (wrap.classList.contains('d-none')) return;
        const docItems = Array.from(wrap.querySelectorAll('[data-doc-item]'));
        docItems.forEach((docItem) => {
          const requiredRows = Array.from(docItem.querySelectorAll('[data-upload-row][data-upload-required="true"]'));
          requiredRows.forEach((row) => {
            hasAny = true;
            const input = row.querySelector('input[type="file"]');
            if (!input || !input.files || input.files.length === 0) {
              valid = false;
            }
          });
        });
      });
      return !hasAny || valid;
    };

    const hasInvalidVerificationState = () => {
      if (readyForBap || !IS_REVISION_VIEW) return false;
      return tableWraps.some((wrap) => {
        if (wrap.classList.contains('d-none')) return false;
        return !!wrap.querySelector('[data-review-docs] .req-switch-invalid, [data-direct-review-cell] .req-switch-invalid');
      });
    };

    const getDirectMappingIssue = () => {
      if (readyForBap) return '';
      const directWrap = tableWraps.find((wrap) => {
        if (wrap.classList.contains('d-none')) return false;
        const tbody = wrap.querySelector('[data-order-table]');
        return (tbody?.dataset.tableType || '') === 'direct';
      });
      if (!directWrap) return '';

      const directRows = Array.from(directWrap.querySelectorAll('tr[data-direct-group]'));
      for (const row of directRows) {
        const paramBlock = row.querySelector('[data-direct-param-block]');
        const lokasiList = row.querySelector('[data-direct-lokasi-list]');
        if (!paramBlock || !lokasiList) continue;
        const select = paramBlock.querySelector('select[data-param-select]');
        const paramId = select?.value || '';
        if (!paramId || paramId === EXTRA_OPTION) continue;

        const locRows = Array.from(lokasiList.querySelectorAll('[data-direct-location-row]'));
        if (!locRows.length) {
          return 'Setiap parameter direct harus memiliki minimal 1 lokasi.';
        }
        for (const locRow of locRows) {
          const locInput = locRow.querySelector('[data-direct-location-input]');
          if (!String(locInput?.value || '').trim()) {
            return 'Nama lokasi pada parameter direct wajib diisi.';
          }
        }
      }
      return '';
    };

    const syncSubmitState = () => {
      if (!submitBtn) return;
      if (readyForBap) {
        submitBtn.disabled = false;
        submitBtn.setAttribute('aria-disabled', 'false');
        submitBtn.removeAttribute('title');
        return;
      }
      const hasTable = hasVisibleTable();
      const hasAnyParams = hasFilledParams();
      const paramsComplete = allParamsSelected();
      const docsComplete = hasDocUploads();
      const requiredUploadsComplete = allRequiredUploadsFilled();
      const invalidVerificationState = hasInvalidVerificationState();
      const directIssue = getDirectMappingIssue();
      const ready = hasTable && paramsComplete && docsComplete && requiredUploadsComplete && !invalidVerificationState && !directIssue;
      submitBtn.disabled = !ready;
      submitBtn.setAttribute('aria-disabled', ready ? 'false' : 'true');
      if (ready) {
        submitBtn.removeAttribute('title');
        return;
      }
      const title = hasTable
        ? (!hasAnyParams
          ? 'Isi minimal satu parameter terlebih dahulu.'
          : (!paramsComplete
            ? 'Lengkapi pilihan parameter pada semua baris.'
            : (!docsComplete
              ? 'Upload dokumen terlebih dahulu.'
              : (invalidVerificationState
                ? 'Masih ada switch merah pada kolom Verifikasi.'
                : (directIssue || 'Lengkapi semua baris upload file.')))))
        : 'Tambah tabel terlebih dahulu.';
      submitBtn.setAttribute('title', title);
    };

    const lockReadyForBapCard = () => {
      if (!readyForBap) return;
      const orderCard = card.querySelector('[data-order-card]');
      if (!orderCard) return;
      orderCard.querySelectorAll('input, select, textarea, button').forEach((el) => {
        if (el.matches('[data-action-submit]')) return;
        el.disabled = true;
      });
    };

    const initTableRows = (tbody) => {
      const tableType = tbody.dataset.tableType || 'indirect';
      if (tableType === 'direct') return;
      tbody.querySelectorAll('[data-row]').forEach((row) => setupRow(row, params, tbody, tableType));
    };

    const showTable = (type) => {
      const wrap = card.querySelector(`[data-table-wrap="${type}"]`);
      const tbody = wrap?.querySelector('[data-order-table]');
      if (!wrap || !tbody) return;
      wrap.classList.remove('d-none');
      if (type !== 'direct') {
        initTableRows(tbody);
        updateRowIndexes(tbody);
      }
      const btn = card.querySelector(`[data-add-table="${type}"]`);
      if (btn) btn.classList.add('d-none');
      syncSubmitState();
    };

    tableWraps.forEach((wrap) => {
      const tbody = wrap.querySelector('[data-order-table]');
      if (!tbody) return;
      initTableRows(tbody);
      const tableType = tbody.dataset.tableType || 'indirect';
      if (tableType !== 'direct') {
        const hasExisting = applyExistingData(card, params, tbody, tableType);
        if (hasExisting) {
          wrap.classList.remove('d-none');
          const btn = card.querySelector(`[data-add-table="${tableType}"]`);
          if (btn) btn.classList.add('d-none');
        }
      } else {
        const existing = parseExisting(card);
        if (existing?.lokasi?.length) {
          const directGroups = {};
          existing.lokasi.forEach((lokasi) => {
            (lokasi.dokumen || []).forEach((doc) => {
              const directParams = (doc.parameters || []).filter((param) => param?.is_direct);
              if (!directParams.length) return;
              const key = doc.label || `Dokumen`;
              if (!directGroups[key]) {
                directGroups[key] = {
                  label: doc.label || key,
                  files: doc.files || [],
                  locations: [],
                };
              }
              if (!directGroups[key].files.length && Array.isArray(doc.files)) {
                directGroups[key].files = doc.files;
              }
                directGroups[key].locations.push({
                  docId: doc.id || null,
                  lokasiId: lokasi.id || null,
                  name: lokasi.nama || lokasi.nama_lokasi || '',
                  params: directParams.map((param) => ({
                    parameter_id: param.parameter_id,
                    parameter_name: param.parameter_name,
                    qty: param.qty,
                    is_sesuai: param.is_sesuai,
                    review_status: param.review_status || '',
                    catatan: param.catatan || '',
                  })),
                });
            });
          });
          const groups = Object.values(directGroups);
          if (groups.length) {
            wrap.classList.remove('d-none');
            const btn = card.querySelector('[data-add-table="direct"]');
            if (btn) btn.classList.add('d-none');
            const empty = tbody.querySelector('[data-direct-empty]');
            empty?.remove();
            groups.forEach((group, idx) => {
              const row = createDirectDocRow(idx + 1, params, group);
              tbody.appendChild(row);
              row.__initLocations?.(group);
            });
          }
        }
      }
    });
    updateParamOptions(card);
    syncSubmitState();
    lockReadyForBapCard();

    addTableButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const type = btn.getAttribute('data-add-table') || 'indirect';
        showTable(type);
      });
    });

    card.querySelectorAll('[data-add-direct-doc]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const wrap = card.querySelector('[data-table-wrap="direct"]');
        const tbody = wrap?.querySelector('[data-direct-table]');
        if (!wrap || !tbody) return;
        wrap.classList.remove('d-none');
        const empty = tbody.querySelector('[data-direct-empty]');
        empty?.remove();
        const nextIndex = tbody.querySelectorAll('[data-direct-doc-index]').length + 1;
        const row = createDirectDocRow(nextIndex, params);
        tbody.appendChild(row);
        row.__initLocations?.();
        const addBtn = card.querySelector('[data-add-table="direct"]');
        if (addBtn) addBtn.classList.add('d-none');
        syncSubmitState();
      });
    });

    removeTableButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const type = btn.getAttribute('data-remove-table') || 'indirect';
        const wrap = card.querySelector(`[data-table-wrap="${type}"]`);
        if (!wrap) return;
        wrap.classList.add('d-none');
        const addBtn = card.querySelector(`[data-add-table="${type}"]`);
        if (addBtn) addBtn.classList.remove('d-none');
        syncSubmitState();
      });
    });

    const scheduleSubmitSync = () => {
      requestAnimationFrame(() => {
        updateParamOptions(card);
        syncSubmitState();
      });
    };

    card.addEventListener('change', (event) => {
      if (event.target.matches('select[data-param-select]')) {
        scheduleSubmitSync();
      }
      if (event.target.matches('[data-qty-docs] input[type="number"]')) {
        scheduleSubmitSync();
      }
      if (event.target.matches('input[type="file"]')) {
        scheduleSubmitSync();
      }
    });

    card.addEventListener('input', (event) => {
      if (event.target.matches('[data-qty-docs] input[type="number"]')) {
        scheduleSubmitSync();
      }
    });

    card.addEventListener('existing-files-updated', scheduleSubmitSync);

    card.addEventListener('click', (event) => {
      const trigger = event.target.closest(
        '[data-param-row-id] button,' +
        '[data-param-add],' +
        '[data-direct-param-add],' +
        '[data-add-row],' +
        '[data-row-remove],' +
        '[data-doc-remove],' +
        '[data-doc-add],' +
        '[data-direct-location-remove],' +
        '[data-upload-remove],' +
        '[data-upload-add],' +
        '[data-upload-camera],' +
        '[data-add-table],' +
        '[data-remove-table],' +
        '[data-add-direct-doc]'
      );
      if (trigger) {
        scheduleSubmitSync();
      }
    });

    card.addEventListener('click', (event) => {
      const removeBtn = event.target.closest('[data-row-remove]');
      if (removeBtn) {
        removeRow(removeBtn);
      }
    });

    const saveUrl = card.getAttribute('data-save-url') || '';
    const submitUrl = card.getAttribute('data-submit-url') || '';
    const hasRevision = card.getAttribute('data-has-revision') === '1';

    if (saveBtn && saveUrl) {
      saveBtn.addEventListener('click', async () => {
        const missingMessage = validateDocUploads(card);
        if (missingMessage) {
          await notify('error', missingMessage);
          return;
        }
        const directIssue = getDirectMappingIssue();
        if (directIssue) {
          await notify('error', directIssue);
          return;
        }
        try {
          setCardLoading(card, true, saveBtn);
          const data = await sendPayload(card, saveUrl);
          await notify('success', data.message || 'Draft pengujian tersimpan.');
        } catch (error) {
          await notify('error', error.message || 'Gagal menyimpan draft.');
        } finally {
          setCardLoading(card, false);
        }
      });
    }

    if (submitBtn && submitUrl) {
      submitBtn.addEventListener('click', async () => {
        if (readyForBap) {
          const ok = await confirmAction('Teruskan data sesuai dari penyelia ke BAP?');
          if (!ok) return;
          try {
            setCardLoading(card, true, submitBtn);
            const data = await sendPayload(card, submitUrl);
            await notify('success', data.message || 'Data sesuai berhasil diteruskan ke BAP.');
            window.location.reload();
          } catch (error) {
            await notify('error', error.message || 'Gagal meneruskan data ke BAP.');
          } finally {
            setCardLoading(card, false);
          }
          return;
        }
        if (!hasVisibleTable()) {
          await notify('error', `Tambah tabel terlebih dahulu sebelum ${hasRevision ? 'kirim ulang ke verifikasi pengujian' : 'mengirim ke verifikasi pengujian'}.`);
          return;
        }
        if (!hasFilledParams()) {
          await notify('error', `Isi minimal satu parameter sebelum ${hasRevision ? 'kirim ulang ke verifikasi pengujian' : 'mengirim ke verifikasi pengujian'}.`);
          return;
        }
        if (!allParamsSelected()) {
          await notify('error', 'Lengkapi pilihan parameter pada semua baris.');
          return;
        }
        if (!allRequiredUploadsFilled()) {
          await notify('error', 'Lengkapi semua baris upload file yang sudah ditambahkan.');
          return;
        }
        if (hasInvalidVerificationState()) {
          await notify('error', 'Masih ada switch merah pada kolom Verifikasi. Perbaiki dulu sebelum lanjut.');
          return;
        }
        const directIssue = getDirectMappingIssue();
        if (directIssue) {
          await notify('error', directIssue);
          return;
        }
        const missingMessage = validateDocUploads(card);
        if (missingMessage) {
          await notify('error', missingMessage);
          return;
        }
        const ok = await confirmAction(hasRevision
          ? 'Kirim ulang hasil perbaikan ke verifikasi pengujian untuk diperiksa kembali?'
          : 'Kirim hasil pengujian ke verifikasi pengujian?');
        if (!ok) return;
        const shouldAutoSaveBeforeSubmit = hasRevision && !readyForBap && !!saveUrl;
        try {
          setCardLoading(card, true, submitBtn);
          if (shouldAutoSaveBeforeSubmit) {
            await sendPayload(card, saveUrl);
          }
          const data = await sendPayload(card, submitUrl);
          await notify('success', data.message || (hasRevision ? 'Perbaikan pengujian berhasil dikirim ulang ke verifikasi pengujian.' : 'Data sesuai berhasil diteruskan ke BAP.'));
          if (hasRevision && data.redirect_url) {
            window.location.href = data.redirect_url;
            return;
          }
          window.location.reload();
        } catch (error) {
          await notify('error', error.message || (hasRevision ? 'Gagal mengirim ulang perbaikan ke verifikasi pengujian.' : 'Gagal mengirim ke verifikasi pengujian.'));
        } finally {
          setCardLoading(card, false);
        }
      });
    }
  });
})();
</script>
@endpush
