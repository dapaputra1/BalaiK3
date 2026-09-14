@extends('layouts.app')

@section('content')

<div class="container py-4 mt-5 history-shell" style="font-family: 'Poppins', sans-serif;">
  <style>
    .history-shell {
      font-family: 'Poppins', sans-serif;
    }
    .track-wrap { position: relative; padding-top: 26px; }
    .track-steps { margin-top: 10px; gap: 12px; }
    .track-step { position: relative; min-width: 90px; padding: 0 6px; text-align: center; flex: 1; }
    .track-step .line {
      height: 8px;
      border-radius: 6px;
      background: #e9ecef;
      margin: 0 auto 10px auto;
      width: 100%;
      max-width: 140px;
    }
    .track-step .circle {
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: #e9ecef;
      margin: 0 auto 6px auto;
      border: 2px solid #fff;
      box-shadow: 0 0 0 2px #e9ecef;
    }
    .track-step .duration-note {
      font-size: 11px;
      line-height: 1.2;
      color: #6c757d;
      min-height: 14px;
      margin-bottom: 6px;
    }
    .track-duration-analisa {
      position: absolute;
      top: -28px;
      left: calc(100% + 6px);
      transform: translateX(-50%);
      font-size: 12px;
      line-height: 1.2;
      color: #6c757d;
      font-weight: 700;
      white-space: nowrap;
      pointer-events: none;
      text-align: center;
    }
    #riwayatDetailModal {
      z-index: 10050;
    }
    #lhuUlasanModal {
      position: fixed;
      inset: 0;
      z-index: 20000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 24px;
      background: rgba(15, 23, 42, 0.46);
    }
    #lhuUlasanModal.is-open {
      display: flex;
    }
    #lhuUlasanModal .modal-dialog {
      width: min(860px, 100%);
      max-width: 860px;
      max-height: calc(100vh - 48px);
      margin: 0;
      pointer-events: auto;
    }
    #lhuUlasanModal .modal-content {
      max-height: calc(100vh - 48px);
      overflow: hidden;
    }
    #lhuUlasanModal .modal-body {
      overflow: auto;
    }
    #lhuUlasanModal .modal-header,
    #lhuUlasanModal .modal-footer,
    #lhuUlasanModal .modal-body,
    #lhuUlasanModal .form-select,
    #lhuUlasanModal .form-control,
    #lhuUlasanModal button,
    #lhuUlasanModal label,
    #lhuUlasanModal textarea,
    #lhuUlasanModal select {
      pointer-events: auto;
    }
    #cancelModal {
      z-index: 10060;
    }
    #riwayatDetailModal .modal-dialog {
      width: min(860px, calc(100vw - 32px));
      max-width: 860px;
      margin: 28px auto 0;
    }
    #riwayatDetailModal .modal-content {
      border: 0;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
    }
    #riwayatDetailModal .modal-header {
      padding: 18px 22px 8px;
    }
    #riwayatDetailModal .modal-title {
      font-size: 16px;
      line-height: 1.3;
      font-weight: 700;
    }
    #riwayatDetailModal .modal-body {
      padding: 14px 18px 18px;
      max-height: calc(100vh - 120px);
      overflow: auto;
    }
    #riwayatDetailModal .modal-body .border.rounded-4 {
      border-radius: 18px !important;
    }
    #riwayatDetailModal .modal-body .border.rounded-3 {
      border-radius: 14px !important;
    }
    #riwayatDetailModal .modal-body .border.rounded-4,
    #riwayatDetailModal .modal-body .border.rounded-3 {
      padding: 14px !important;
    }
    #riwayatDetailModal .modal-body .row.g-3 {
      --bs-gutter-x: 14px;
      --bs-gutter-y: 12px;
    }
    #riwayatDetailModal .modal-body .fw-semibold.text-muted.small {
      font-size: 11px !important;
      margin-bottom: 4px !important;
      line-height: 1.35;
    }
    #riwayatDetailModal .modal-body .h6 {
      font-size: 14px;
      line-height: 1.35;
      font-weight: 600;
      overflow-wrap: anywhere;
      word-break: break-word;
    }
    #riwayatDetailModal .modal-body h6.fw-semibold {
      font-size: 15px;
      line-height: 1.3;
    }
    #riwayatDetailModal .modal-body h6.fw-semibold.mb-3 {
      margin-bottom: 10px !important;
    }
    #riwayatDetailModal .modal-body .small {
      font-size: 11px !important;
      line-height: 1.45;
    }
    #riwayatDetailModal .modal-body hr.my-3 {
      margin-top: 12px !important;
      margin-bottom: 12px !important;
    }
    #riwayatDetailModal .modal-body .table {
      font-size: 11.5px;
    }
    #riwayatDetailModal .modal-body .table thead th,
    #riwayatDetailModal .modal-body .table tbody td,
    #riwayatDetailModal .modal-body .table tfoot th {
      padding: 8px 10px;
      vertical-align: middle;
    }
    #riwayatDetailModal #detailOrderReviewSection .badge {
      font-size: 10.5px;
      padding: 5px 10px;
      border-radius: 999px;
    }
    #riwayatDetailModal #detailOrderReviewSection .fw-semibold.mb-2 {
      font-size: 13px;
    }
    .modal-backdrop.show {
      z-index: 10040;
    }
    .swal2-container {
      z-index: 11000;
    }
    .param-table-wrap {
      border-radius: 10px;
      background: #f8fafc;
    }
    .param-table {
      margin-bottom: 0;
    }
    .param-table thead th {
      font-size: 14px;
      font-weight: 600;
      padding: 10px 12px;
      white-space: nowrap;
    }
    .param-table tbody td {
      font-size: 13px;
      padding: 9px 12px;
      vertical-align: middle;
    }
    .param-table tbody td.fw-semibold {
      font-weight: 600 !important;
    }
    .order-code {
      font-size: 17px;
      font-weight: 700;
      line-height: 1.2;
    }
    .order-actions .btn {
      border-radius: 8px;
      border-width: 2px;
      padding: 7px 14px;
      font-size: 12px;
      font-weight: 600;
      background: #fff;
      box-shadow: none !important;
      display: inline-flex;
      align-items: center;
      gap: 7px;
    }
    .order-actions .view-detail {
      color: #2f66ff;
      border-color: #cddcff;
    }
    .order-actions .view-detail:hover,
    .order-actions .view-detail:focus {
      color: #2f66ff;
      border-color: #b7cbff;
      background: #f8fbff;
    }
    .order-actions .cancel-order {
      color: #ef4444;
      border-color: #ffd1d1;
    }
    .order-actions .cancel-order:hover,
    .order-actions .cancel-order:focus {
      color: #ef4444;
      border-color: #ffc1c1;
      background: #fff8f8;
    }
    .order-actions .btn i {
      font-size: 12px;
    }
    [data-download-once].is-loading {
      pointer-events: none;
      cursor: wait;
      opacity: 0.72;
    }
    [data-download-once].is-loading .download-label {
      color: #6c757d !important;
    }
    .download-spinner {
      width: 1rem;
      height: 1rem;
      border: 2px solid rgba(13, 110, 253, 0.25);
      border-top-color: #0d6efd;
      border-radius: 50%;
      display: inline-block;
      animation: download-spin 0.75s linear infinite;
    }
    @keyframes download-spin {
      to { transform: rotate(360deg); }
    }
    .filter-toolbar .btn {
      border-radius: 10px;
      font-weight: 600;
    }
    .history-card {
      border-radius: 20px;
    }
    .history-mobile-back {
      display: none;
    }
    @media (max-width: 767.98px) {
      .history-shell {
        padding-top: 18px !important;
        padding-bottom: 20px !important;
        padding-left: 14px !important;
        padding-right: 14px !important;
        margin-top: 0 !important;
      }
      .history-header {
        flex-direction: column;
        align-items: stretch !important;
        gap: 12px;
        margin-bottom: 16px !important;
      }
      .history-title-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 4px;
      }
      .history-mobile-back {
        display: inline-flex;
        width: 26px;
        height: 26px;
        align-items: center;
        justify-content: center;
        border: 1.25px solid #18456d;
        border-radius: 50%;
        color: #18456d;
        text-decoration: none;
        flex-shrink: 0;
      }
      .history-mobile-back i {
        font-size: 10px;
        line-height: 1;
      }
      .history-header h4 {
        font-size: 15px;
        line-height: 1.25;
        margin-bottom: 0 !important;
      }
      .history-header .text-muted.small {
        font-size: 10px !important;
        line-height: 1.4;
        max-width: 260px;
        margin-left: 36px;
      }
      .history-back-link {
        display: none !important;
      }
      .history-back-link {
        width: 100%;
        justify-content: center;
        border-radius: 12px;
        padding: 9px 12px;
        font-size: 11px;
        gap: 8px !important;
      }
      .filter-card {
        border-radius: 18px !important;
        margin-bottom: 12px !important;
      }
      .filter-card .card-body {
        padding: 12px;
      }
      .filter-toolbar {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 6px;
        align-items: stretch !important;
      }
      .filter-toolbar [data-filter-group] {
        width: 100%;
        min-height: 32px;
        padding: 6px 6px;
        font-size: 9px;
        line-height: 1.1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
      }
      .filter-toolbar .filter-search {
        grid-column: 1 / -1;
        width: 100%;
        min-width: 0 !important;
        margin-left: 0 !important;
      }
      .filter-toolbar .filter-search .form-control {
        min-height: 36px;
        border-radius: 10px;
        font-size: 12px;
      }
      .history-list {
        row-gap: 12px !important;
      }
      .history-card {
        padding: 12px !important;
        border-radius: 18px !important;
      }
      .history-card-head {
        flex-direction: column;
        gap: 10px;
        align-items: stretch !important;
      }
      .history-card-side {
        text-align: left !important;
      }
      .history-card-main {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        column-gap: 8px;
        row-gap: 0;
        align-items: center;
      }
      .history-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 8px !important;
      }
      .history-badges .badge {
        margin-left: 0 !important;
        font-size: 9px;
        font-weight: 600;
        padding: 5px 7px;
      }
      .order-code {
        font-size: 14px;
        margin-bottom: 0 !important;
        word-break: break-word;
        grid-column: 1;
        grid-row: 1;
      }
      .order-total {
        font-size: 18px;
        line-height: 1.15;
      }
      .order-actions {
        gap: 6px !important;
        display: contents;
      }
      .order-actions .btn,
      .history-card .reorder-btn {
        width: 100%;
        justify-content: center;
        padding: 8px 10px;
        font-size: 11px;
        border-radius: 10px;
      }
      .order-actions .view-detail {
        grid-column: 2;
        grid-row: 1;
        width: auto;
        min-height: 28px;
        padding: 5px 9px;
        font-size: 9px;
        border-radius: 8px;
        border-width: 1.2px;
        justify-self: end;
        white-space: nowrap;
      }
      .order-actions .btn:not(.view-detail),
      .history-card .reorder-btn {
        grid-column: 1 / -1;
      }
      .history-card .small.text-muted.mt-2 {
        font-size: 10px !important;
        grid-column: 1;
        grid-row: 2;
        margin-top: -1px !important;
        margin-bottom: 0 !important;
        line-height: 1.1;
        align-self: center;
      }
      .history-card .alert {
        padding: 10px !important;
        border-radius: 12px;
      }
      .history-card .alert .fw-semibold.small,
      .history-card .alert .small {
        font-size: 10px !important;
        line-height: 1.4;
      }
      .history-card .alert .btn {
        width: 100%;
        min-height: 34px;
        justify-content: center;
        font-size: 11px;
      }
      .history-card .alert > div:last-child {
        width: 100%;
      }
      .progress-label {
        font-size: 11px !important;
      }
      .track-wrap {
        overflow-x: auto;
        padding-top: 16px;
        padding-bottom: 4px;
        margin-inline: -2px;
      }
      .track-wrap::-webkit-scrollbar {
        height: 4px;
      }
      .track-wrap::-webkit-scrollbar-thumb {
        background: rgba(20, 61, 102, 0.25);
        border-radius: 999px;
      }
      .track-steps {
        flex-wrap: nowrap !important;
        gap: 6px;
        min-width: max-content;
      }
      .track-step {
        min-width: 68px;
        max-width: 68px;
        padding: 0 2px;
        flex: 0 0 68px;
      }
      .track-step .line {
        height: 6px;
        margin-bottom: 7px;
        max-width: 56px;
      }
      .track-step .circle {
        width: 13px;
        height: 13px;
        margin-bottom: 4px;
      }
      .track-step .small {
        font-size: 9px !important;
        line-height: 1.3;
      }
      .track-duration-analisa {
        top: -16px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 9px;
      }
      .param-table-wrap {
        padding: 8px !important;
        border-radius: 14px;
      }
      .param-table thead th {
        font-size: 10px;
        padding: 7px 8px;
      }
      .param-table tbody td {
        font-size: 10px;
        padding: 7px 8px;
      }
      #riwayatDetailModal .modal-dialog {
        width: min(430px, 92vw);
        max-width: 92vw;
        margin: 8vh auto 0;
      }
      #riwayatDetailModal .modal-content {
        border-radius: 14px;
        overflow: hidden;
        max-height: 75vh;
      }
      #riwayatDetailModal .modal-header {
        padding: 10px 12px 7px;
      }
      #riwayatDetailModal .modal-title {
        font-size: 14px;
        line-height: 1.25;
      }
      #riwayatDetailModal .modal-body {
        padding: 9px 10px;
        max-height: calc(75vh - 88px);
        overflow: auto;
      }
      #riwayatDetailModal .modal-footer {
        padding: 7px 10px 10px;
      }
      #riwayatDetailModal .modal-footer .btn {
        min-width: 70px !important;
        font-size: 10px;
        padding: 5px 10px;
      }
      #riwayatDetailModal .modal-body .border.rounded-4,
      #riwayatDetailModal .modal-body .border.rounded-3 {
        border-radius: 12px !important;
        padding: 8px !important;
      }
      #riwayatDetailModal .modal-body .row.g-3 {
        --bs-gutter-y: 8px;
        --bs-gutter-x: 8px;
      }
      #riwayatDetailModal .modal-body .detail-two-col-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        row-gap: 6px;
        column-gap: 8px;
      }
      #riwayatDetailModal .modal-body .detail-two-col-row > [class*="col-"] {
        width: auto;
        padding: 1px 3px !important;
      }
      #riwayatDetailModal .modal-body .h6 {
        font-size: 10px;
        line-height: 1.25;
        margin-bottom: 0;
      }
      #riwayatDetailModal .modal-body .fw-semibold.text-muted.small {
        margin-bottom: 2px !important;
      }
      #riwayatDetailModal .modal-body h6.fw-semibold {
        font-size: 12px;
        line-height: 1.25;
      }
      #riwayatDetailModal .modal-body h6.fw-semibold.mb-3 {
        margin-bottom: 8px !important;
      }
      #riwayatDetailModal .modal-body .small {
        font-size: 9px !important;
      }
      #riwayatDetailModal .modal-body hr.my-3 {
        margin-top: 8px !important;
        margin-bottom: 8px !important;
      }
      #riwayatDetailModal .modal-body .table {
        font-size: 9px;
      }
      #riwayatDetailModal .modal-body .table thead th,
      #riwayatDetailModal .modal-body .table tbody td,
      #riwayatDetailModal .modal-body .table tfoot th {
        padding: 6px 7px;
        white-space: nowrap;
      }
      #riwayatDetailModal #detailBillingSection .d-flex.flex-column.gap-2 .btn,
      #riwayatDetailModal #detailPenawaranAction .d-flex.flex-wrap.gap-2.align-items-center .btn,
      #riwayatDetailModal #detailBapApproval .btn {
        width: 100%;
      }
      #riwayatDetailModal #detailPenawaranAction .d-flex.flex-wrap.gap-2.align-items-center input[type="file"] {
        width: 100%;
      }
      #riwayatDetailModal #detailBapApproval .row.g-2.align-items-end {
        --bs-gutter-y: 8px;
      }
      #riwayatDetailModal #detailDocs .small {
        font-size: 8.5px !important;
      }
      #riwayatDetailModal #detailDocs a,
      #riwayatDetailModal #detailDocs button {
        min-height: 32px;
        padding: 6px 9px !important;
        border-radius: 9px !important;
        margin-bottom: 6px !important;
      }
      #riwayatDetailModal #detailDocs .fw-semibold {
        font-size: 10.5px !important;
        line-height: 1.25;
        font-weight: 600 !important;
      }
      #riwayatDetailModal #detailDocs .fs-5 {
        font-size: 13px !important;
      }
    }
  </style>
  <div class="d-flex justify-content-between align-items-center mb-4 history-header">
    <div>
      <div class="history-title-row">
        <a href="/daftar_pelayanan" class="history-mobile-back" aria-label="Kembali ke daftar pelayanan">
          <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="fw-semibold mb-1">Riwayat Pelayanan</h4>
      </div>
      <div class="text-muted small">Pantau riwayat permintaan dan parameter yang pernah diajukan.</div>
    </div>
    <a href="/daftar_pelayanan" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 history-back-link">
      <i class="bi bi-arrow-left"></i> Kembali ke Daftar Pelayanan
    </a>
  </div>

  <div class="card border-0 shadow-sm rounded-4 mb-3 filter-card">
    <div class="card-body">
      <div class="d-flex flex-wrap gap-2 align-items-center filter-toolbar">
        <button type="button" class="btn btn-outline-primary btn-sm active" data-filter-group="ordered">Pelayanan Diorder</button>
        <button type="button" class="btn btn-outline-primary btn-sm" data-filter-group="completed">Pelayanan Selesai</button>
        <button type="button" class="btn btn-outline-primary btn-sm" data-filter-group="cancelled">Pelayanan Dibatalkan</button>
        <div class="ms-auto filter-search" style="min-width: 220px;">
          <input type="text" class="form-control form-control-sm" placeholder="Cari kode..." data-filter-kode>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 history-list" data-list-wrapper>
    @forelse($riwayat as $row)
      @php
        $params = collect($row['parameter'] ?? []);
        $subtotal = $params->sum(fn($p) => ($p['harga'] ?? 0) * ($p['qty'] ?? 1));
      @endphp
      <div class="col-12" data-card data-group="{{ $row['kategori'] ?? 'ordered' }}" data-tahap="{{ $row['tahap'] ?? '' }}" data-kode="{{ strtolower($row['kode'] ?? '') }}" data-perusahaan="{{ strtolower($row['perusahaan'] ?? '') }}">
      <div class="border rounded-4 p-3 shadow-sm bg-white history-card">
      @php
        $currentTahap = $row['tahap'] ?? '';
        $progressTahap = $row['progress_tahap'] ?? $currentTahap;
        $activeIndex = array_search($progressTahap, $tahapan);
        $tahapColor = $activeIndex !== false ? $colors[$activeIndex % count($colors)] : '#6c757d';
        $detailId = 'detail-' . \Illuminate\Support\Str::slug($row['kode'] ?? uniqid());
        $canCancel = (bool) ($row['can_cancel'] ?? false);
        $canPenawaranAction = (($row['penawaran_doc_status'] ?? '') === 'sent')
          && !empty($row['penawaran_doc_id'])
          && !empty($row['id']);
        $canScheduleAction = !empty($row['jadwal_can_approve']);
        $scheduleRejectUrl = $row['jadwal_reject_url'] ?? '';
        $scheduleStartLabel = $row['jadwal_mulai_label'] ?? '-';
        $scheduleEndLabel = $row['jadwal_selesai_label'] ?? '-';
        $schedulePcuCount = (int) ($row['jadwal_pcu_count'] ?? 0);
        $canBapAction = !empty($row['bap_can_approve']);
        $canOrderReviewAction = !empty($row['order_review_can_approve']);
        $canInvoiceAction = !empty($row['invoice_verify_url']) && !empty($row['invoice_url']);
        $canBillingAction = !empty($row['billing_alert']) && !empty($row['billing_url']);
        $isKuitansiPending = !empty($row['kuitansi_pending']);
        $canLhuAction = !empty($row['lhu_ready']) && !empty($row['lhu_url']);
      @endphp
      <div class="d-flex justify-content-between align-items-start mb-2 history-card-head">
        <div class="history-card-main">
          <h5 class="fw-semibold mb-2 order-code">{{ $row['kode'] ?? '-' }}</h5>
          <div class="d-flex flex-wrap gap-2 order-actions">
            <button
              type="button"
              class="btn btn-outline-primary btn-sm view-detail"
              data-bs-toggle="modal"
              data-bs-target="#riwayatDetailModal"
              data-kode="{{ $row['kode'] ?? '-' }}"
              data-perusahaan="{{ $row['perusahaan'] ?? '-' }}"
              data-penanggung="{{ $row['penanggung_jawab'] ?? '-' }}"
              data-email="{{ $row['email'] ?? '-' }}"
              data-telepon="{{ $row['telepon'] ?? '-' }}"
              data-alamat="{{ $row['alamat'] ?? '-' }}"
              data-jenis-perusahaan="{{ $row['jenis_perusahaan'] ?? '-' }}"
              data-provinsi="{{ $row['provinsi'] ?? '-' }}"
              data-kota="{{ $row['kota'] ?? '-' }}"
              data-jumlah-pekerja="{{ $row['jumlah_pekerja'] ?? 0 }}"
              data-tahap="{{ $currentTahap ?: '-' }}"
              data-tanggal="{{ $row['tanggal'] ?? '-' }}"
              data-jadwal-mulai-label="{{ $row['jadwal_mulai_label'] ?? '-' }}"
              data-jadwal-selesai-label="{{ $row['jadwal_selesai_label'] ?? '-' }}"
              data-jadwal-pcu-count="{{ $row['jadwal_pcu_count'] ?? 0 }}"
              data-jadwal-sent-to-user-at="{{ $row['jadwal_sent_to_user_at'] ?? '' }}"
              data-jadwal-can-approve="{{ !empty($row['jadwal_can_approve']) ? '1' : '0' }}"
              data-jadwal-approve-url="{{ $row['jadwal_approve_url'] ?? '' }}"
              data-jadwal-reject-url="{{ $row['jadwal_reject_url'] ?? '' }}"
              data-total="{{ $subtotal }}"
              data-order-review-status="{{ $row['order_review_status'] ?? '' }}"
              data-order-review-note="{{ $row['order_review_note'] ?? '' }}"
              data-order-review-has-comparison="{{ !empty($row['order_review_has_comparison']) ? '1' : '0' }}"
              data-order-review-original="{{ e(json_encode($row['order_review_original_parameters'] ?? [])) }}"
              data-order-review-final="{{ e(json_encode($row['order_review_final_parameters'] ?? [])) }}"
              data-params='@json($row['parameter'] ?? [])'
              data-dokumen='@json(collect($row['dokumen'] ?? [])->values())'
              data-penawaran-doc-id="{{ $row['penawaran_doc_id'] ?? '' }}"
              data-penawaran-doc-status="{{ $row['penawaran_doc_status'] ?? '' }}"
              data-penawaran-doc-url="{{ $row['penawaran_doc_url'] ?? '' }}"
              data-penawaran-catatan="{{ $row['penawaran_catatan'] ?? '' }}"
              data-permohonan-id="{{ $row['id'] ?? '' }}"
              data-cancel-reason="{{ $row['cancel_reason'] ?? '' }}"
              data-cancel-note="{{ $row['cancel_note'] ?? '' }}"
              data-cancelled-at="{{ $row['cancelled_at'] ?? '' }}"
              data-cancelled-at-iso="{{ $row['cancelled_at_iso'] ?? '' }}"
              data-bap-status="{{ $row['bap_status_label'] ?? 'Belum mengirim BAP' }}"
              data-bap-can-approve="{{ !empty($row['bap_can_approve']) ? '1' : '0' }}"
              data-bap-doc-ready="{{ !empty($row['bap_doc_ready']) ? '1' : '0' }}"
              data-bap-tanggal="{{ $row['bap_tanggal'] ?? '' }}"
              data-bap-tanggal-mulai="{{ $row['bap_tanggal_mulai'] ?? '' }}"
              data-bap-tanggal-selesai="{{ $row['bap_tanggal_selesai'] ?? '' }}"
              data-bap-lokasi="{{ $row['bap_lokasi'] ?? '-' }}"
              data-bap-jenis-perusahaan="{{ $row['bap_jenis_perusahaan'] ?? '-' }}"
              data-bap-alamat="{{ $row['bap_alamat'] ?? '-' }}"
              data-bap-pcu='@json($row['bap_pcu'] ?? [])'
              data-bap-ketua-tim-nama="{{ $row['bap_ketua_tim_nama'] ?? '-' }}"
              data-bap-ketua-tim-ttd="{{ $row['bap_ketua_tim_ttd'] ?? '' }}"
              data-bap-penanggung-ttd="{{ $row['bap_penanggung_ttd'] ?? '' }}"
              data-bap-penandatangan-nama="{{ $row['bap_penandatangan_nama'] ?? '-' }}"
              data-bap-penandatangan-jabatan="{{ $row['bap_penandatangan_jabatan'] ?? '-' }}"
              data-bap-lokasi-rows="{{ e(json_encode($row['bap_lokasi_rows'] ?? [])) }}"
              data-bap-parameter-order="{{ e(json_encode($row['bap_parameter_order'] ?? [])) }}"
              data-bap-parameter-pengujian="{{ e(json_encode($row['bap_parameter_pengujian'] ?? [])) }}"
              data-billing-url="{{ $row['billing_url'] ?? '' }}"
              data-invoice-url="{{ $row['invoice_url'] ?? '' }}"
              data-invoice-link-label="{{ $row['invoice_link_label'] ?? 'Lihat Surat Tagihan' }}"
              data-invoice-verify-url="{{ $row['invoice_verify_url'] ?? '' }}"
              data-invoice-verified-at="{{ $row['invoice_verified_at'] ?? '' }}"
              data-billing-confirm-url="{{ $row['billing_confirm_url'] ?? '' }}"
              data-billing-renewal-request-url="{{ $row['billing_renewal_request_url'] ?? '' }}"
              data-billing-payment-proof-url="{{ $row['billing_payment_proof_url'] ?? '' }}"
              data-billing-payment-proof-name="{{ $row['billing_payment_proof_name'] ?? '' }}"
              data-billing-payment-proof-uploaded-at="{{ $row['billing_payment_proof_uploaded_at'] ?? '' }}"
              data-billing-paid="{{ !empty($row['billing_paid_by_user']) ? '1' : '0' }}"
              data-billing-paid-at="{{ $row['billing_paid_by_user_at'] ?? '' }}"
              data-billing-verified-at="{{ $row['billing_verified_at'] ?? '' }}"
              data-billing-expires-at="{{ $row['billing_expires_at'] ?? '' }}"
              data-billing-expired="{{ !empty($row['billing_expired']) ? '1' : '0' }}"
              data-billing-sent="{{ !empty($row['billing_sent']) ? '1' : '0' }}"
              data-billing-renewal-requested="{{ !empty($row['billing_renewal_requested']) ? '1' : '0' }}"
              data-billing-renewal-note="{{ $row['billing_renewal_note'] ?? '' }}"
              data-lhu-url="{{ $row['lhu_url'] ?? '' }}"
              data-lhu-needs-ulasan="{{ !empty($row['lhu_needs_ulasan']) ? '1' : '0' }}"
              data-lhu-ulasan-submit-url="{{ $row['lhu_ulasan_submit_url'] ?? '' }}"
            >
              <i class="bi bi-eye-fill"></i> Lihat Detail
            </button>
            @if($canCancel)
              <button
                type="button"
                class="btn btn-outline-danger btn-sm cancel-order"
                data-stage="{{ strtolower($currentTahap) }}"
                data-kode="{{ $row['kode'] ?? '-' }}"
                data-id="{{ $row['id'] ?? '' }}"
              >
                <i class="bi bi-x-circle-fill"></i> Batalkan Pesanan
              </button>
            @endif
            @if(($row['kategori'] ?? '') === 'completed' && !empty($row['can_reorder']) && !empty($row['reorder_url']))
              <button
                type="button"
                class="btn btn-outline-success btn-sm reorder-btn"
                data-reorder-url="{{ $row['reorder_url'] }}"
              >
                Order Kembali
              </button>
            @endif
          </div>
          @if(!$canCancel && ($row['kategori'] ?? '') !== 'cancelled')
            <div class="small text-muted mt-2">Tidak bisa dibatalkan</div>
          @endif
        </div>
        <div class="text-end history-card-side">
	          <div class="mb-2 history-badges">
	            <span class="badge border-0" style="background: {{ $tahapColor }}; color: #fff;">{{ $currentTahap ?: 'Tahap belum dimulai' }}</span>
	            @if(($row['kategori'] ?? '') === 'completed')
	              <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">Selesai</span>
	            @elseif(!empty($row['workflow_badge']))
	              <span class="badge {{ $row['workflow_badge_class'] ?? 'bg-info-subtle text-info border border-info-subtle' }} ms-1">{{ $row['workflow_badge'] }}</span>
	            @elseif(!empty($row['billing_waiting_verification']))
	              <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-1">Verifikasi Pembayaran</span>
	            @elseif(!empty($row['lhu_badge']))
	              @php
	                $isRevisionBadge = ($row['lhu_badge'] ?? '') === 'Revisi diajukan';
              @endphp
              <span class="badge {{ $isRevisionBadge ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} ms-1">{{ $row['lhu_badge'] }}</span>
            @endif
          </div>
          <div class="fw-semibold order-total">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
        </div>
      </div>
      @if($canOrderReviewAction || $canPenawaranAction || $canScheduleAction || $canBapAction || $canInvoiceAction || $canBillingAction || $canLhuAction)
        <div class="mb-3 d-flex flex-column gap-2">
          @if($canOrderReviewAction)
            <div class="alert alert-warning py-2 px-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="fw-semibold small mb-0">Perbaikan Pesanan Menunggu Persetujuan</div>
                <div class="small mb-0">
                  Admin telah memeriksa nama parameter dan jumlah pesanan.
                  @if(!empty($row['order_review_note']))
                    Catatan: {{ $row['order_review_note'] }}
                  @endif
                </div>
              </div>
              <button
                type="button"
                class="btn btn-warning btn-sm fw-semibold order-review-approve-btn"
                data-approve-url="{{ $row['order_review_approve_url'] ?? '' }}"
                data-kode="{{ $row['kode'] ?? '-' }}"
              >Setujui Pesanan</button>
            </div>
          @endif
          @if($canPenawaranAction)
            <div class="alert alert-warning py-2 px-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="fw-semibold small mb-0">Penawaran Menunggu Persetujuan</div>
                <div class="small mb-0">Segera selesaikan persetujuan penawaran agar proses tidak tertunda.</div>
              </div>
              <button type="button" class="btn btn-warning btn-sm fw-semibold quick-open-detail">Selesaikan Sekarang</button>
            </div>
          @endif
          @if($canScheduleAction)
            <div class="alert alert-info py-2 px-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="fw-semibold small mb-0">Persetujuan Jadwal Pengujian</div>
                <div class="small mb-1">Petugas sudah mengirim jadwal. Anda bisa ACC jadwal atau ajukan tanggal mulai pengujian lain untuk diteruskan kembali ke penjadwalan.</div>
                <div class="small mb-0">
                  Tanggal mulai pengujian yang diajukan petugas: <span class="fw-semibold">{{ $scheduleStartLabel }}</span> s/d <span class="fw-semibold">{{ $scheduleEndLabel }}</span>.
                  Petugas PCU yang akan datang: <span class="fw-semibold">{{ $schedulePcuCount }}</span> orang.
                </div>
              </div>
              <div class="d-flex gap-2 flex-wrap">
                <button
                  type="button"
                  class="btn btn-outline-info btn-sm fw-semibold schedule-reject-btn"
                  data-reject-url="{{ $scheduleRejectUrl }}"
                >Ajukan Tanggal</button>
                <button
                  type="button"
                  class="btn btn-info btn-sm fw-semibold text-white schedule-approve-btn"
                  data-approve-url="{{ $row['jadwal_approve_url'] ?? '' }}"
                >ACC Jadwal</button>
              </div>
            </div>
          @endif
          @if($canBapAction)
            <div class="alert alert-danger py-2 px-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="fw-semibold small mb-0">Persetujuan BAP</div>
                <div class="small mb-0">Segera setujui BAP agar permohonan bisa lanjut ke tahap berikutnya.</div>
              </div>
              <button type="button" class="btn btn-danger btn-sm fw-semibold quick-open-detail">Selesaikan Sekarang</button>
            </div>
          @endif
          @if($canInvoiceAction)
            <div class="alert alert-primary py-2 px-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="fw-semibold small mb-0">ACC Surat Tagihan</div>
                <div class="small mb-0">ACC surat tagihan untuk melanjutkan ke tahap kode billing.</div>
              </div>
              <button type="button" class="btn btn-primary btn-sm fw-semibold quick-open-detail">ACC Sekarang</button>
            </div>
          @endif
          @if($canBillingAction)
            <div class="alert alert-info py-2 px-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="fw-semibold small mb-0">{{ !empty($row['billing_waiting_verification']) ? 'Pembayaran Sedang Diverifikasi Petugas' : 'Kode Billing Sudah Tersedia' }}</div>
                <div class="small mb-0">{{ !empty($row['billing_waiting_verification']) ? 'Konfirmasi pembayaran Anda sudah diterima. Mohon tunggu verifikasi petugas sebelum kuitansi diterbitkan.' : 'Segera lakukan pembayaran. Kuitansi akan tersedia setelah pembayaran diverifikasi petugas.' }}</div>
              </div>
              <button
                type="button"
                class="btn btn-info btn-sm fw-semibold text-white quick-open-detail"
              >{{ !empty($row['billing_waiting_verification']) ? 'Lihat Detail' : 'Lihat Kode Billing' }}</button>
            </div>
          @endif
          @if($isKuitansiPending)
            <div class="alert alert-success py-2 px-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="fw-semibold small mb-0">Pembayaran Terverifikasi</div>
                <div class="small mb-0">Petugas sedang menerbitkan kuitansi. Kuitansi akan muncul setelah diteruskan oleh petugas.</div>
              </div>
              <button type="button" class="btn btn-success btn-sm fw-semibold quick-open-detail">Lihat Detail</button>
            </div>
          @endif
          @if($canLhuAction)
            <div class="alert alert-success py-2 px-3 mb-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
              <div>
                <div class="fw-semibold small mb-0">LHU dan Surat Keterangan Sudah Diteruskan</div>
                <div class="small mb-0">Silakan lihat dokumen yang tersedia, lalu ACC LHU atau ajukan revisi beserta catatan.</div>
              </div>
              <div class="d-flex gap-2 flex-wrap">
                @if(!empty($row['suket_ready']) && !empty($row['suket_url']))
                  <a
                    href="{{ $row['suket_url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-outline-success btn-sm fw-semibold"
                  >Lihat Surat Keterangan</a>
                @endif
                <button
                  type="button"
                  class="btn btn-success btn-sm fw-semibold lhu-view-btn"
                  data-lhu-url="{{ $row['lhu_url'] }}"
                  data-lhu-needs-ulasan="{{ !empty($row['lhu_needs_ulasan']) ? '1' : '0' }}"
                  data-lhu-ulasan-submit-url="{{ $row['lhu_ulasan_submit_url'] ?? '' }}"
                  data-kode="{{ $row['kode'] ?? '-' }}"
                >Lihat LHU</button>
                @if(!empty($row['lhu_can_respond']))
                  <button
                    type="button"
                    class="btn btn-outline-success btn-sm fw-semibold lhu-approve-btn"
                    data-approve-url="{{ $row['lhu_approve_url'] ?? '' }}"
                  >ACC LHU</button>
                  <button
                    type="button"
                    class="btn btn-outline-danger btn-sm fw-semibold lhu-revise-btn"
                    data-revise-url="{{ $row['lhu_revise_url'] ?? '' }}"
                    data-kode="{{ $row['kode'] ?? '-' }}"
                  >Ajukan Revisi</button>
                @endif
              </div>
            </div>
          @endif
        </div>
      @endif
        <div class="mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="small fw-semibold progress-label">Progress Pelayanan</div>
          </div>
        <div class="track-wrap">
          <div class="track-steps d-flex justify-content-between flex-wrap">
            @foreach($tahapan as $index => $tahap)
              @php
                $isActive = $activeIndex !== false && $index <= $activeIndex;
                $c = $colors[$index % count($colors)];
                $lineColor = $isActive ? $c : '#e9ecef';
                $circleStyle = $isActive ? "background: {$c}; box-shadow: 0 0 0 4px {$c}33;" : '';
              @endphp
              <div class="text-center flex-fill track-step">
                @if($currentTahap === 'Analisa' && $tahap === 'Analisa')
                  <div class="track-duration-analisa">10 hari kerja</div>
                @endif
                <div class="line" style="background: {{ $lineColor }};"></div>
                <div class="circle" style="{{ $circleStyle }}"></div>
                <div class="small mt-1 {{ $isActive ? 'fw-semibold' : 'text-muted' }}" style="{{ $isActive ? 'color: ' . $c : '' }}">{{ $tahap }}</div>
              </div>
            @endforeach
          </div>
        </div>
      </div>
      <div class="border rounded-3 p-2 bg-light param-table-wrap">
            <div class="table-responsive">
              <table class="table table-sm align-middle param-table">
                <thead class="table-light">
                  <tr>
                    <th style="min-width:180px;">Kategori</th>
                    <th>Parameter</th>
                    <th style="width:80px;" class="text-center">Qty</th>
                    <th style="width:140px;" class="text-end">Harga</th>
                    <th style="width:150px;" class="text-end">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($params as $param)
                    @php
                      $qty = $param['qty'] ?? 1;
                      $harga = $param['harga'] ?? 0;
                      $sub = $qty * $harga;
                    @endphp
                    <tr>
                      <td>{{ $param['kategori'] ?? '-' }}</td>
                      <td>{{ $param['nama'] ?? '-' }}</td>
                      <td class="text-center">{{ $qty }}</td>
                      <td class="text-end">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                      <td class="text-end fw-semibold">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="5" class="text-center text-muted small">Tidak ada parameter.</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12 text-center text-muted py-4">Belum ada riwayat pelayanan.</div>
    @endforelse
  </div>
  <div class="alert alert-warning d-none mt-3" data-empty>Riwayat tidak ditemukan.</div>
  </div>
  <!-- Modal Detail Riwayat (ala superadmin_permohonan) -->
  <div class="modal fade" id="riwayatDetailModal" tabindex="-1" aria-labelledby="riwayatDetailModalLabel" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-lg" style="font-family: 'Poppins', sans-serif;">
      <div class="modal-content" style="font-family: 'Poppins', sans-serif;">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="riwayatDetailModalLabel">Detail Pemesanan</h5>
        </div>
        <div class="modal-body">
          <div class="border rounded-4 p-3 mb-3 shadow-sm bg-white">
            <div class="row g-3 detail-two-col-row">
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Kode Permohonan</div>
                <div class="h6 mb-0" id="detailKode">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Tanggal</div>
                <div class="h6 mb-0" id="detailTanggal">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Tahap</div>
                <div class="h6 mb-0" id="detailTahap">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Total</div>
                <div class="h6 mb-0 text-primary" id="detailTotal">Rp 0</div>
              </div>
            </div>

            <hr class="my-3">

            <div class="row g-3 detail-two-col-row">
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Nama Perusahaan</div>
                <div class="h6 mb-0" id="detailPerusahaan">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Penanggung Jawab</div>
                <div class="h6 mb-0" id="detailPenanggung">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Email</div>
                <div class="h6 mb-0" id="detailEmail">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">No. Telepon (WA)</div>
                <div class="h6 mb-0" id="detailTelepon">-</div>
              </div>
              <div class="col-12">
                <div class="fw-semibold text-muted small">Alamat</div>
                <div class="h6 mb-0" id="detailAlamat">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Jenis Perusahaan</div>
                <div class="h6 mb-0" id="detailJenisPerusahaan">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Provinsi</div>
                <div class="h6 mb-0" id="detailProvinsi">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Kota/Kabupaten</div>
                <div class="h6 mb-0" id="detailKota">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Jumlah Pekerja</div>
                <div class="h6 mb-0" id="detailJumlahPekerja">-</div>
              </div>
            </div>

            <div class="border rounded-3 p-3 bg-light mt-3 d-none" id="detailCancelSection">
              <div class="fw-semibold text-muted small">Alasan Pembatalan</div>
              <div class="h6 mb-1" id="detailCancelReason">-</div>
              <div class="small text-muted mb-1">Catatan</div>
              <div class="h6 mb-1" id="detailCancelNote">-</div>
              <div class="small text-muted">Dibatalkan pada: <span id="detailCancelledAt">-</span></div>
            </div>
          </div>

          <div class="border rounded-4 p-3 shadow-sm bg-white mt-3 d-none" id="detailOrderReviewSection">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
              <h6 class="fw-semibold mb-0">Perbandingan Verifikasi Pesanan</h6>
              <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pesanan Awal vs Pesanan Akhir</span>
            </div>
            <div class="row g-3">
              <div class="col-12 col-xl-6">
                <div class="border rounded-3 p-3 h-100 bg-light">
                  <div class="fw-semibold mb-2">Pesanan Awal Pelanggan</div>
                  <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th style="width:50px;">No</th>
                          <th style="min-width:150px;">Kategori</th>
                          <th>Parameter</th>
                          <th class="text-center" style="width:80px;">Qty</th>
                        </tr>
                      </thead>
                      <tbody id="detailOrderReviewOriginalBody">
                        <tr><td colspan="4" class="text-center text-muted py-3">Data pesanan awal tidak tersedia.</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="col-12 col-xl-6">
                <div class="border rounded-3 p-3 h-100 bg-light">
                  <div class="fw-semibold mb-2">Pesanan Akhir / Hasil Verifikasi</div>
                  <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                      <thead class="table-light">
                        <tr>
                          <th style="width:50px;">No</th>
                          <th style="min-width:150px;">Kategori</th>
                          <th>Parameter</th>
                          <th class="text-center" style="width:80px;">Qty</th>
                        </tr>
                      </thead>
                      <tbody id="detailOrderReviewFinalBody">
                        <tr><td colspan="4" class="text-center text-muted py-3">Data pesanan akhir tidak tersedia.</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="small text-muted mt-3 d-none" id="detailOrderReviewNoteWrap">
              Catatan admin: <span class="fw-semibold text-dark" id="detailOrderReviewNote">-</span>
            </div>
          </div>

          <div class="border rounded-4 p-3 shadow-sm bg-white">
            <h6 class="fw-semibold mb-3">Rincian Parameter</h6>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th style="width:50px;">No</th>
                    <th style="min-width:180px;">Kategori</th>
                    <th>Parameter</th>
                    <th class="text-center" style="width:80px;">Qty</th>
                    <th class="text-end" style="width:150px;">Harga</th>
                    <th class="text-end" style="width:150px;">Subtotal</th>
                  </tr>
                </thead>
                <tbody id="detailParamsBody">
                  <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada parameter.</td></tr>
                </tbody>
                <tfoot>
                  <tr class="table-light">
                    <th colspan="5" class="text-end">Total</th>
                    <th class="text-end" id="detailParamsTotal">Rp 0</th>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="border rounded-4 p-3 shadow-sm bg-white mt-3 d-none" id="detailScheduleSection">
            <h6 class="fw-semibold mb-3">Jadwal Pengujian</h6>
            <div class="row g-3 detail-two-col-row">
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Tanggal Mulai Pengujian</div>
                <div class="h6 mb-0" id="detailJadwalMulai">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Tanggal Selesai Pengujian</div>
                <div class="h6 mb-0" id="detailJadwalSelesai">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Jumlah Petugas PCU</div>
                <div class="h6 mb-0" id="detailJadwalPcuCount">-</div>
              </div>
              <div class="col-12 col-md-6">
                <div class="fw-semibold text-muted small">Dikirim ke User</div>
                <div class="h6 mb-0" id="detailJadwalSentAt">-</div>
              </div>
            </div>
          </div>

          <div class="alert alert-info border-0 rounded-4 p-3 shadow-sm mt-3 d-none mb-0" id="detailScheduleApproval">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
              <div class="fw-semibold">Persetujuan Jadwal Pengujian</div>
              <span class="badge bg-info text-dark" id="detailScheduleApprovalBadge">Menunggu ACC User</span>
            </div>
            <div class="small text-muted mb-3">
              Petugas sudah mengirim tanggal mulai pengujian. Anda bisa langsung ACC atau ajukan perubahan tanggal untuk diteruskan kembali ke penjadwalan.
            </div>
            <div class="small mb-3">
              Tanggal mulai pengujian: <span class="fw-semibold" id="detailScheduleApprovalDate">-</span><br>
              Petugas PCU yang akan datang: <span class="fw-semibold" id="detailScheduleApprovalPcuCount">-</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button
                type="button"
                class="btn btn-outline-info btn-sm fw-semibold schedule-reject-btn"
                id="detailScheduleRejectBtn"
                data-reject-url=""
              >Ajukan Tanggal</button>
              <button
                type="button"
                class="btn btn-info btn-sm fw-semibold text-white schedule-approve-btn"
                id="detailScheduleApproveBtn"
                data-approve-url=""
              >ACC Jadwal</button>
            </div>
          </div>

          <div class="border rounded-4 p-3 shadow-sm bg-white mt-3">
            <h6 class="fw-semibold mb-3">Dokumen</h6>
            <div id="detailDocs"></div>
          </div>

          <div class="border rounded-4 p-3 shadow-sm bg-white mt-3 d-none" id="detailBillingSection">
            <h6 class="fw-semibold mb-3">Surat Tagihan, Kode Billing, dan Kuitansi</h6>
            <div class="alert alert-warning small d-none" id="detailInvoiceWarning">
              Setelah ACC surat tagihan, petugas akan mengirim kode billing yang berlaku 24 jam. Mohon cek web secara berkala. Jika kode billing sudah muncul, pembayaran harus segera dilakukan sebelum masa berlaku habis.
            </div>
            <div class="alert alert-info small d-none" id="detailBillingRenewalInfo">
              Pengajuan kode billing ulang sudah dikirim. Mohon tunggu admin mengupload dokumen kode billing terbaru.
            </div>
            <div class="d-flex flex-column gap-2 mb-3">
              <a href="#" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm d-none" id="detailBillingInvoiceLink">Lihat Surat Tagihan</a>
              <button type="button" class="btn btn-primary btn-sm d-none" id="detailInvoiceVerifyBtn">ACC Surat Tagihan</button>
              <a href="#" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm d-none" id="detailBillingKodeLink">Lihat Kode Billing</a>
              <button type="button" class="btn btn-outline-warning btn-sm d-none" id="detailBillingRenewalBtn">Ajukan Kode Billing Ulang</button>
              @if(!empty($paymentGuide['exists']))
                <a
                  href="{{ $paymentGuide['open_url'] ?? '#' }}"
                  target="_blank"
                  rel="noopener"
                  class="btn btn-outline-secondary btn-sm d-none"
                  id="detailBillingGuideLink"
                >
                  Panduan Pembayaran (PDF)
                </a>
              @endif
            </div>
            <div class="small text-muted mb-2" id="detailInvoiceVerifiedInfo"></div>
            <div class="d-none" id="detailBillingPaymentSection">
              <div class="small text-muted mb-2" id="detailBillingPaidInfo"></div>
              <div class="small text-muted mb-3" id="detailBillingVerifiedInfo"></div>
              <div class="small text-danger mb-3 d-none" id="detailBillingExpiredInfo"></div>
              <div class="border rounded-3 p-3 bg-light mb-3">
                <label for="detailBillingPaymentProof" class="form-label small fw-semibold mb-2">Upload Bukti Pembayaran</label>
                <input type="file" class="form-control form-control-sm mb-2" id="detailBillingPaymentProof">
                <div class="small text-muted" id="detailBillingPaymentProofHelp">Wajib upload bukti pembayaran sebelum mencentang konfirmasi. Maksimal 10 MB.</div>
                <div class="small mt-2 d-none" id="detailBillingPaymentProofInfo">
                  File terupload: <a href="#" target="_blank" rel="noopener" id="detailBillingPaymentProofLink"></a>
                </div>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="1" id="detailBillingPaidCheckbox">
                <label class="form-check-label" for="detailBillingPaidCheckbox">Saya sudah melakukan pembayaran</label>
              </div>
              <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-primary btn-sm" id="detailBillingConfirmBtn">Kirim Konfirmasi</button>
              </div>
            </div>
          </div>

          <div class="border rounded-4 p-3 shadow-sm bg-white mt-3 d-none" id="detailPenawaranAction">
            <div class="alert alert-warning small mb-3">
              Dokumen penawaran sudah dikirim. Silakan tanda tangani (TTD) dokumen penawaran terlebih dahulu, lalu upload kembali dokumen yang sudah ditandatangani dan pilih tindakan.
            </div>
            <div class="border rounded-3 bg-light p-2 mb-3 d-none" id="detailPenawaranCatatanWrap">
              <div class="small text-muted mb-1">Catatan Khusus Penawaran</div>
              <div class="small fw-semibold text-dark" id="detailPenawaranCatatan">-</div>
            </div>
            <div class="small fw-semibold text-primary mb-2" id="penawaranUploadTrigger">
              Upload Dokumen TTD
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <input type="file" class="form-control form-control-sm" id="penawaranSignedFile" accept=".pdf,.doc,.docx">
              <button type="button" class="btn btn-success btn-sm" data-penawaran-accept>Terima Penawaran</button>
              <button type="button" class="btn btn-outline-danger btn-sm" data-penawaran-reject>Tolak Penawaran</button>
            </div>
            <div class="small text-muted mt-2" id="penawaranActionInfo">Maksimal ukuran file 5 MB (PDF/DOC/DOCX).</div>
          </div>

          <div class="border rounded-4 p-3 shadow-sm bg-white mt-3 d-none" id="detailBapApproval">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
              <div class="fw-semibold">Persetujuan BAP</div>
              <span class="badge bg-secondary" id="detailBapStatus">Belum mengirim BAP</span>
            </div>
            <div class="mb-3" id="detailBapDocs"></div>
            <div class="text-muted small mb-3">
              Dengan menyetujui, Anda menyatakan setuju dan dokumen akan ditandatangani otomatis menggunakan TTD saat checkout.
            </div>
            <div class="row g-2 align-items-end">
              <div class="col-12 col-md-6">
                <label class="form-label small text-muted mb-1">Captcha</label>
                {!! NoCaptcha::display() !!}
              </div>
              <div class="col-12 col-md-4">
                <button type="button" class="btn btn-success btn-sm w-100" data-bap-approve>Setujui BAP</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <div class="ms-auto">
            <button
              type="button"
              class="btn btn-danger btn-sm px-3 fw-semibold text-white"
              data-bs-dismiss="modal"
              aria-label="Tutup"
              style="min-width: 90px;"
            >Tutup</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal Batalkan Pesanan -->
  <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" style="font-family: 'Poppins', sans-serif; max-width: 520px;">
      <div class="modal-content" style="font-family: 'Poppins', sans-serif;">
        <div class="modal-header border-0 pb-0">
          <div>
            <div class="small text-muted mb-1">Pembatalan</div>
            <h6 class="mb-0">Pesanan <span id="cancelKode">-</span></h6>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="small text-muted mb-2">Pilih alasan pembatalan:</div>
          <div class="list-group list-group-flush">
            @php
              $alasanList = [
                'Perubahan kebutuhan',
                'Salah input data',
                'Jadwal tidak sesuai',
                'Biaya tidak sesuai',
                'Lainnya',
              ];
            @endphp
            @foreach($alasanList as $idx => $alasan)
              <label class="list-group-item px-0 d-flex align-items-center gap-2">
                <input type="radio" name="cancel_reason" value="{{ $alasan }}" class="form-check-input flex-shrink-0" data-cancel-reason>
                <span>{{ $alasan }}</span>
              </label>
            @endforeach
          </div>
          <div class="mt-3">
            <label class="form-label small text-muted">Catatan tambahan (opsional)</label>
            <textarea class="form-control" rows="2" data-cancel-note placeholder="Tuliskan detail jika perlu"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-danger btn-sm" data-cancel-submit>Kirim Pembatalan</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="lhuUlasanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Form Ulasan Permohonan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info py-2 px-3 small mb-3">
            Sebelum melihat LHU, silakan isi ulasan (gabungan IKM dan IKK) terlebih dahulu.
          </div>
          <div id="lhuUlasanQuestions"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn btn-primary" id="lhuUlasanSubmitBtn">Kirim Ulasan</button>
        </div>
      </div>
    </div>
  </div>
  </div>
  @php
    $serviceCategories = \App\Models\ServiceCategory::pluck('name', 'id');
    $serviceParameters = \App\Models\ServiceParameter::select('name', 'service_category_id')->get();
  @endphp
  <script>
    const parameterIndex = @json(
      $serviceParameters->map(function ($row) use ($serviceCategories) {
        return [
          'name' => $row->name,
          'category' => $serviceCategories[$row->service_category_id] ?? '-',
        ];
      })->values()
    );

    (function() {
      const groupButtons = document.querySelectorAll('[data-filter-group]');
      const cards = document.querySelectorAll('[data-card]');
      const empty = document.querySelector('[data-empty]');
      const kodeInput = document.querySelector('[data-filter-kode]');
      const urlParams = new URLSearchParams(window.location.search);
      const urlKode = urlParams.get('kode');
      const urlOpen = (urlParams.get('open') || '').toLowerCase();
      if (urlKode && kodeInput) {
        kodeInput.value = urlKode;
      }

      const applyFilter = (group) => {
        const kodeQuery = (kodeInput?.value || '').trim().toLowerCase();
        let visible = 0;
        cards.forEach((card) => {
          const cardGroup = (card.getAttribute('data-group') || '').toLowerCase();
          const cardKode = (card.getAttribute('data-kode') || '').toLowerCase();
          const matchGroup = !group || cardGroup === group;
          const matchKode = !kodeQuery || cardKode.includes(kodeQuery);
          const show = matchGroup && matchKode;
          card.classList.toggle('d-none', !show);
          if (show) visible += 1;
        });
        if (empty) empty.classList.toggle('d-none', visible > 0);
      };

      const setActiveGroupButton = (group) => {
        groupButtons.forEach((btn) => {
          const btnGroup = (btn.getAttribute('data-filter-group') || '').toLowerCase();
          btn.classList.toggle('active', btnGroup === group);
        });
      };

      groupButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          groupButtons.forEach((b) => b.classList.remove('active'));
          btn.classList.add('active');
          const group = (btn.getAttribute('data-filter-group') || '').toLowerCase();
          applyFilter(group);
        });
      });

      if (kodeInput) {
        kodeInput.addEventListener('input', () => {
          const activeGroupBtn = document.querySelector('[data-filter-group].active');
          const group = (activeGroupBtn?.getAttribute('data-filter-group') || '').toLowerCase();
          applyFilter(group);
        });
      }

      applyFilter('ordered');

      if (urlOpen === 'detail' && urlKode) {
        const targetKode = urlKode.trim().toLowerCase();
        const targetCard = Array.from(cards).find((card) => {
          const cardKode = (card.getAttribute('data-kode') || '').toLowerCase();
          return cardKode === targetKode;
        });

        if (targetCard) {
          const targetGroup = (targetCard.getAttribute('data-group') || 'ordered').toLowerCase();
          setActiveGroupButton(targetGroup);
          applyFilter(targetGroup);

          const detailBtn = targetCard.querySelector('.view-detail');
          if (detailBtn) {
            setTimeout(() => detailBtn.click(), 120);
          }
        }
      }
    })();

    // Modal detail ala superadmin_permohonan
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.quick-open-detail').forEach((btn) => {
        btn.addEventListener('click', () => {
          const card = btn.closest('[data-card]');
          const detailBtn = card?.querySelector('.view-detail');
          if (detailBtn) {
            detailBtn.click();
          }
        });
      });

      const notify = async (type, message) => {
        if (window.Swal) {
          await window.Swal.fire({
            icon: type,
            title: type === 'success' ? 'Berhasil' : (type === 'warning' ? 'Peringatan' : 'Gagal'),
            text: message || '',
          });
          return;
        }
        alert(message || '');
      };

      document.querySelectorAll('.schedule-approve-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
          const approveUrl = btn.getAttribute('data-approve-url') || '';
          if (!approveUrl) return;

          const confirmed = typeof Swal === 'undefined'
            ? confirm('Setujui jadwal pengujian ini?')
            : await Swal.fire({
                icon: 'question',
                title: 'Setujui Jadwal?',
                text: 'Setelah disetujui, petugas akan meneruskan jadwal ke Approval MA.',
                showCancelButton: true,
                confirmButtonText: 'ACC Jadwal',
                cancelButtonText: 'Batal',
              }).then((r) => r.isConfirmed);

          if (!confirmed) return;

          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          try {
            const response = await fetch(approveUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
              },
              body: JSON.stringify({}),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || 'Gagal menyetujui jadwal.');
            await notify('success', result.message || 'Jadwal berhasil disetujui.');
            window.location.reload();
          } catch (error) {
            await notify('error', error.message || 'Gagal menyetujui jadwal.');
          }
        });
      });

      document.querySelectorAll('.schedule-reject-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
          const rejectUrl = btn.getAttribute('data-reject-url') || '';
          if (!rejectUrl) return;

          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          let payload = null;

          if (window.Swal) {
            const result = await window.Swal.fire({
              icon: 'warning',
              title: 'Ajukan Tanggal Mulai Pengujian',
              html: `
                <div class="text-start">
                  <label class="form-label small fw-semibold mb-1" for="swalRequestedStart">Tanggal mulai pengujian</label>
                  <input id="swalRequestedStart" type="date" class="swal2-input" style="display:block;width:100%;margin:0 0 12px;">
                  <label class="form-label small fw-semibold mb-1" for="swalRequestedEnd">Tanggal selesai pengujian</label>
                  <input id="swalRequestedEnd" type="date" class="swal2-input" style="display:block;width:100%;margin:0 0 12px;">
                  <label class="form-label small fw-semibold mb-1" for="swalRequestedReason">Alasan</label>
                  <textarea id="swalRequestedReason" class="swal2-textarea" style="display:block;width:100%;margin:0;" placeholder="Tulis alasan perubahan tanggal mulai pengujian..."></textarea>
                </div>
              `,
              showCancelButton: true,
              confirmButtonText: 'Kirim Tanggal',
              cancelButtonText: 'Batal',
              focusConfirm: false,
              preConfirm: () => {
                const start = document.getElementById('swalRequestedStart')?.value || '';
                const end = document.getElementById('swalRequestedEnd')?.value || '';
                const reason = document.getElementById('swalRequestedReason')?.value || '';

                if (!start || !end) {
                  window.Swal.showValidationMessage('Rentang tanggal wajib diisi.');
                  return false;
                }

                if (new Date(end) < new Date(start)) {
                  window.Swal.showValidationMessage('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
                  return false;
                }

                return {
                  requested_start_date: start,
                  requested_end_date: end,
                  reason: reason.trim(),
                };
              },
            });

            if (!result.isConfirmed || !result.value) return;
            payload = result.value;
          } else {
            const start = (window.prompt('Masukkan tanggal mulai pengujian (YYYY-MM-DD):') || '').trim();
            const end = (window.prompt('Masukkan tanggal selesai pengujian (YYYY-MM-DD):') || '').trim();
            const reason = (window.prompt('Masukkan alasan perubahan tanggal mulai pengujian:') || '').trim();

            if (!start || !end) return;

            payload = {
              requested_start_date: start,
              requested_end_date: end,
              reason,
            };
          }

          try {
            const response = await fetch(rejectUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
              },
              body: JSON.stringify(payload),
            });
            const result = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(result.message || 'Gagal mengirim tanggal mulai pengujian.');
            await notify('success', result.message || 'Tanggal mulai pengujian berhasil dikirim.');
            window.location.reload();
          } catch (error) {
            await notify('error', error.message || 'Gagal mengirim tanggal mulai pengujian.');
          }
        });
      });

      const decodeHtml = (value) => {
        if (!value) return '';
        const textarea = document.createElement('textarea');
        textarea.innerHTML = value;
        return textarea.value;
      };

      const parseList = (raw) => {
        try {
          const decoded = decodeHtml(raw || '[]');
          return JSON.parse(decoded) || [];
        } catch (error) {
          return [];
        }
      };

      @php
        $ulasanQuestionsPayload = collect($ulasanQuestions ?? [])->map(function ($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'type' => $q->type,
                'category' => $q->category ?? 'ikm',
                'note' => $q->note,
                'rating_labels' => collect($q->rating_labels ?? [])->values()->all(),
            ];
        })->values();
      @endphp
      const ulasanQuestions = @json($ulasanQuestionsPayload);
      const ulasanModalEl = document.getElementById('lhuUlasanModal');
      const ulasanQuestionsWrap = document.getElementById('lhuUlasanQuestions');
      const ulasanSubmitBtn = document.getElementById('lhuUlasanSubmitBtn');
      const ulasanState = {
        lhuUrl: '',
        submitUrl: '',
        kode: '-',
      };

      const cleanupModalArtifacts = () => {
        document.querySelectorAll('.modal-backdrop').forEach((backdrop) => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('padding-right');
      };

      const showUlasanModal = () => {
        if (!ulasanModalEl) return;
        cleanupModalArtifacts();
        ulasanModalEl.classList.add('is-open');
        ulasanModalEl.style.display = 'flex';
        ulasanModalEl.removeAttribute('aria-hidden');
        ulasanModalEl.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
      };

      const hideUlasanModal = () => {
        if (!ulasanModalEl) return;
        ulasanModalEl.classList.remove('is-open');
        ulasanModalEl.style.display = 'none';
        ulasanModalEl.setAttribute('aria-hidden', 'true');
        ulasanModalEl.removeAttribute('aria-modal');
        cleanupModalArtifacts();
      };

      const renderUlasanQuestions = () => {
        if (!ulasanQuestionsWrap) return;
        if (!Array.isArray(ulasanQuestions) || ulasanQuestions.length === 0) {
          ulasanQuestionsWrap.innerHTML = '<div class="text-muted small">Pertanyaan ulasan belum tersedia.</div>';
          return;
        }

        const ikmQuestions = ulasanQuestions.filter((q) => (q.category || 'ikm') === 'ikm');
        const ikkQuestions = ulasanQuestions.filter((q) => (q.category || 'ikm') === 'ikk');

        let globalIndex = 0;
        const renderQuestion = (q) => {
          globalIndex += 1;
          const label = `<label class="form-label fw-semibold mb-1">${globalIndex}. ${q.question || '-'}</label>`;
          const note = q.note ? `<div class="text-muted small mb-2">${q.note}</div>` : '';

          if (q.type === 'rating') {
            const configuredLabels = Array.isArray(q.rating_labels) && q.rating_labels.length > 0
              ? q.rating_labels
              : ['Sangat buruk', 'Buruk', 'Baik', 'Sangat baik'];
            const visibleOptions = configuredLabels
              .map((text, idx) => ({
                value: idx + 1,
                text: typeof text === 'string' ? text.trim() : '',
              }))
              .filter((option) => option.text !== '');
            const fallbackOptions = ['Sangat buruk', 'Buruk', 'Baik', 'Sangat baik'].map((text, idx) => ({
              value: idx + 1,
              text,
            }));
            const options = (visibleOptions.length > 0 ? visibleOptions : fallbackOptions)
              .map((option) => `<option value=\"${option.value}\">${option.text}</option>`)
              .join('');

            return `
              <div class="mb-3 border rounded-3 p-3 bg-light-subtle">
                ${label}
                ${note}
                <select class="form-select form-select-sm" data-ulasan-answer data-question-id="${q.id}" data-question-type="rating">
                  <option value="">Pilih jawaban</option>
                  ${options}
                </select>
              </div>
            `;
          }

          return `
            <div class="mb-3 border rounded-3 p-3 bg-light-subtle">
              ${label}
              ${note}
              <textarea class="form-control form-control-sm" rows="3" data-ulasan-answer data-question-id="${q.id}" data-question-type="text" placeholder="Tulis jawaban Anda"></textarea>
            </div>
          `;
        };

        const renderSection = (title, description, sectionQuestions, badgeClass) => {
          if (!Array.isArray(sectionQuestions) || sectionQuestions.length === 0) return '';
          const items = sectionQuestions.map((q) => renderQuestion(q)).join('');
          return `
            <div class="mb-4">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge ${badgeClass}">${title}</span>
                <span class="small text-muted">${description}</span>
              </div>
              ${items}
            </div>
          `;
        };

        ulasanQuestionsWrap.innerHTML = [
          renderSection('IKM', 'Indeks Kepuasan Masyarakat', ikmQuestions, 'text-bg-primary'),
          renderSection('IKK', 'Indeks Kepatuhan/Kontrol Korupsi', ikkQuestions, 'text-bg-warning'),
        ].join('');
      };

      const openLhu = (url) => {
        if (!url) return;
        window.open(url, '_blank', 'noopener');
      };

      const openLhuWithUlasanCheck = async ({ url, needsUlasan, submitUrl, kode }) => {
        if (!url) return;

        if (!needsUlasan || !Array.isArray(ulasanQuestions) || ulasanQuestions.length === 0) {
          openLhu(url);
          return;
        }

        if (window.Swal) {
          await window.Swal.fire({
            icon: 'info',
            title: 'Isi Ulasan Dulu',
            text: 'Sebelum melihat LHU, Anda wajib mengisi formulir ulasan permohonan.',
            confirmButtonText: 'Lanjut Isi Ulasan',
          });
        } else {
          alert('Sebelum melihat LHU, Anda wajib mengisi formulir ulasan permohonan.');
        }

        ulasanState.lhuUrl = url;
        ulasanState.submitUrl = submitUrl || '';
        ulasanState.kode = kode || '-';
        const openUlasanForm = () => {
          if (ulasanSubmitBtn) ulasanSubmitBtn.disabled = false;
          renderUlasanQuestions();
          showUlasanModal();
        };

        const detailModalEl = document.getElementById('riwayatDetailModal');
        const detailModalInstance = (window.bootstrap && detailModalEl)
          ? window.bootstrap.Modal.getInstance(detailModalEl)
          : null;
        if (detailModalEl?.classList.contains('show') && detailModalInstance) {
          detailModalEl.addEventListener('hidden.bs.modal', () => {
            cleanupModalArtifacts();
            window.setTimeout(() => {
              openUlasanForm();
            }, 80);
          }, { once: true });
          detailModalInstance.hide();
          return;
        }

        openUlasanForm();
      };

      ulasanModalEl?.querySelectorAll('[data-bs-dismiss="modal"]').forEach((btn) => {
        btn.addEventListener('click', hideUlasanModal);
      });

      ulasanModalEl?.addEventListener('click', (event) => {
        if (event.target === ulasanModalEl) {
          hideUlasanModal();
        }
      });

      document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && ulasanModalEl?.classList.contains('is-open')) {
          hideUlasanModal();
        }
      });

      const normalizeName = (value) => String(value || '').trim().toLowerCase();
      const getJenisPengukuran = (paramName, fallbackCategory = '') => {
        const fallback = String(fallbackCategory || '').trim();
        if (fallback && fallback !== '-') return fallback;
        const needle = String(paramName || '').toLowerCase();
        if (!needle) return '-';
        const match = parameterIndex.find((item) => {
          const name = String(item.name || '').toLowerCase();
          return name.includes(needle) || needle.includes(name);
        });
        return match?.category || '-';
      };
      const buildQtyMap = (items) => {
        const map = new Map();
        (items || []).forEach((item) => {
          const name = String(item?.nama || '').trim();
          if (!name) return;
          const method = String(item?.method || 'indirect');
          const key = `${normalizeName(name)}::${method}`;
          const qty = Number(item?.qty || 0) || 0;
          const kategori = String(item?.kategori || '').trim();
          map.set(key, {
            key,
            name,
            method,
            kategori: kategori || map.get(key)?.kategori || '-',
            qty: (map.get(key)?.qty || 0) + qty,
          });
        });
        return map;
      };

      const computeChanges = (orderParams, testParams) => {
        const orderMap = buildQtyMap(orderParams);
        const testMap = buildQtyMap(testParams);

        const orderedKeys = [];
        const seen = new Set();
        (testParams || []).forEach((item) => {
          const key = `${normalizeName(item?.nama)}::${String(item?.method || 'indirect')}`;
          if (!key || seen.has(key)) return;
          seen.add(key);
          orderedKeys.push(key);
        });
        (orderParams || []).forEach((item) => {
          const key = `${normalizeName(item?.nama)}::${String(item?.method || 'indirect')}`;
          if (!key || seen.has(key)) return;
          seen.add(key);
          orderedKeys.push(key);
        });

        return orderedKeys.map((key) => {
          const before = orderMap.get(key)?.qty || 0;
          const after = testMap.get(key)?.qty || 0;
          if (before === after) return null;
          const name = orderMap.get(key)?.name || testMap.get(key)?.name || '-';
          const tambah = after > before ? after - before : 0;
          const kurang = before > after ? before - after : 0;
          return {
            key,
            name,
            method: orderMap.get(key)?.method || testMap.get(key)?.method || 'indirect',
            kategori: orderMap.get(key)?.kategori || testMap.get(key)?.kategori || '-',
            tambah,
            kurang,
            total: after,
          };
        }).filter(Boolean);
      };

      const collectChangedLocations = (lokasiRows, changedKeys) => {
        const locationMap = new Map();
        (lokasiRows || []).forEach((row) => {
          const lokasi = String(row?.lokasi || '').trim() || '-';
          const rowKeys = new Set();
          (row?.dokumen_list || []).forEach((doc) => {
            (doc?.parameter || []).forEach((param) => {
              const method = param?.is_direct ? 'direct' : String(param?.method || 'indirect');
              const key = `${normalizeName(param?.nama)}::${method}`;
              if (!changedKeys.has(key) || rowKeys.has(key)) return;
              rowKeys.add(key);
              if (!locationMap.has(key)) {
                locationMap.set(key, new Set());
              }
              locationMap.get(key).add(lokasi);
            });
          });
        });
        return locationMap;
      };

      const modalEl = document.getElementById('riwayatDetailModal');
      if (!modalEl) return;
      let cancelledAtTimer = null;

      modalEl.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

          const data = {
            kode: trigger.getAttribute('data-kode'),
            perusahaan: trigger.getAttribute('data-perusahaan'),
            penanggung: trigger.getAttribute('data-penanggung'),
            email: trigger.getAttribute('data-email'),
            telepon: trigger.getAttribute('data-telepon'),
            alamat: trigger.getAttribute('data-alamat'),
            jenisPerusahaan: trigger.getAttribute('data-jenis-perusahaan'),
            provinsi: trigger.getAttribute('data-provinsi'),
            kota: trigger.getAttribute('data-kota'),
            jumlahPekerja: trigger.getAttribute('data-jumlah-pekerja'),
            tahap: trigger.getAttribute('data-tahap'),
            tanggal: trigger.getAttribute('data-tanggal'),
            jadwalMulaiLabel: trigger.getAttribute('data-jadwal-mulai-label'),
            jadwalSelesaiLabel: trigger.getAttribute('data-jadwal-selesai-label'),
            jadwalPcuCount: trigger.getAttribute('data-jadwal-pcu-count'),
            jadwalSentToUserAt: trigger.getAttribute('data-jadwal-sent-to-user-at'),
            jadwalCanApprove: trigger.getAttribute('data-jadwal-can-approve') === '1',
            jadwalApproveUrl: trigger.getAttribute('data-jadwal-approve-url'),
            jadwalRejectUrl: trigger.getAttribute('data-jadwal-reject-url'),
            total: trigger.getAttribute('data-total'),
            orderReviewStatus: trigger.getAttribute('data-order-review-status'),
            orderReviewNote: trigger.getAttribute('data-order-review-note'),
            orderReviewHasComparison: trigger.getAttribute('data-order-review-has-comparison') === '1',
            orderReviewOriginal: trigger.getAttribute('data-order-review-original'),
            orderReviewFinal: trigger.getAttribute('data-order-review-final'),
            params: trigger.getAttribute('data-params'),
            dokumen: trigger.getAttribute('data-dokumen'),
            penawaranDocId: trigger.getAttribute('data-penawaran-doc-id'),
            penawaranDocStatus: trigger.getAttribute('data-penawaran-doc-status'),
            penawaranDocUrl: trigger.getAttribute('data-penawaran-doc-url'),
            penawaranCatatan: trigger.getAttribute('data-penawaran-catatan'),
            permohonanId: trigger.getAttribute('data-permohonan-id'),
            cancelReason: trigger.getAttribute('data-cancel-reason'),
            cancelNote: trigger.getAttribute('data-cancel-note'),
            cancelledAt: trigger.getAttribute('data-cancelled-at'),
            cancelledAtIso: trigger.getAttribute('data-cancelled-at-iso'),
            bapStatus: trigger.getAttribute('data-bap-status'),
            bapCanApprove: trigger.getAttribute('data-bap-can-approve') === '1',
            bapDocReady: trigger.getAttribute('data-bap-doc-ready') === '1',
            bapTanggal: trigger.getAttribute('data-bap-tanggal'),
            bapTanggalMulai: trigger.getAttribute('data-bap-tanggal-mulai'),
            bapTanggalSelesai: trigger.getAttribute('data-bap-tanggal-selesai'),
            bapLokasi: trigger.getAttribute('data-bap-lokasi'),
            bapJenisPerusahaan: trigger.getAttribute('data-bap-jenis-perusahaan'),
            bapAlamat: trigger.getAttribute('data-bap-alamat'),
            bapPcu: trigger.getAttribute('data-bap-pcu'),
            bapKetuaTimNama: trigger.getAttribute('data-bap-ketua-tim-nama'),
            bapKetuaTimTtd: trigger.getAttribute('data-bap-ketua-tim-ttd'),
            bapPenanggungTtd: trigger.getAttribute('data-bap-penanggung-ttd'),
            bapPenandatanganNama: trigger.getAttribute('data-bap-penandatangan-nama'),
            bapPenandatanganJabatan: trigger.getAttribute('data-bap-penandatangan-jabatan'),
            bapLokasiRows: trigger.getAttribute('data-bap-lokasi-rows'),
            bapParameterOrder: trigger.getAttribute('data-bap-parameter-order'),
            bapParameterPengujian: trigger.getAttribute('data-bap-parameter-pengujian'),
            billingUrl: trigger.getAttribute('data-billing-url'),
            invoiceUrl: trigger.getAttribute('data-invoice-url'),
            invoiceLinkLabel: trigger.getAttribute('data-invoice-link-label') || 'Lihat Surat Tagihan',
            invoiceVerifyUrl: trigger.getAttribute('data-invoice-verify-url'),
            invoiceVerifiedAt: trigger.getAttribute('data-invoice-verified-at'),
            billingConfirmUrl: trigger.getAttribute('data-billing-confirm-url'),
            billingRenewalRequestUrl: trigger.getAttribute('data-billing-renewal-request-url'),
            billingPaymentProofUrl: trigger.getAttribute('data-billing-payment-proof-url'),
            billingPaymentProofName: trigger.getAttribute('data-billing-payment-proof-name'),
            billingPaymentProofUploadedAt: trigger.getAttribute('data-billing-payment-proof-uploaded-at'),
            billingPaid: trigger.getAttribute('data-billing-paid') === '1',
            billingPaidAt: trigger.getAttribute('data-billing-paid-at'),
            billingVerifiedAt: trigger.getAttribute('data-billing-verified-at'),
            billingExpiresAt: trigger.getAttribute('data-billing-expires-at'),
            billingExpired: trigger.getAttribute('data-billing-expired') === '1',
            billingSent: trigger.getAttribute('data-billing-sent') === '1',
            billingRenewalRequested: trigger.getAttribute('data-billing-renewal-requested') === '1',
            billingRenewalNote: trigger.getAttribute('data-billing-renewal-note'),
            lhuUrl: trigger.getAttribute('data-lhu-url'),
            lhuNeedsUlasan: trigger.getAttribute('data-lhu-needs-ulasan') === '1',
            lhuUlasanSubmitUrl: trigger.getAttribute('data-lhu-ulasan-submit-url'),
          };

        const setText = (selector, value) => {
          const el = modalEl.querySelector(selector);
          if (el) el.textContent = value || '-';
        };

        const formatRupiah = (num) => {
          const n = Number(num) || 0;
          return n.toLocaleString('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
        };
        const formatCancelledAtText = (isoValue, fallbackValue) => {
          const fallback = fallbackValue || '-';
          if (!isoValue) return fallback;
          const cancelledDate = new Date(isoValue);
          if (Number.isNaN(cancelledDate.getTime())) return fallback;

          const absolute = cancelledDate
            .toLocaleString('id-ID', {
              day: '2-digit',
              month: 'short',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit',
            })
            .replace(/\./g, ':');

          const diffMs = Date.now() - cancelledDate.getTime();
          let relative = 'baru saja';
          if (diffMs >= 24 * 60 * 60 * 1000) {
            relative = `${Math.floor(diffMs / (24 * 60 * 60 * 1000))} hari yang lalu`;
          } else if (diffMs >= 60 * 60 * 1000) {
            relative = `${Math.floor(diffMs / (60 * 60 * 1000))} jam yang lalu`;
          } else if (diffMs >= 60 * 1000) {
            relative = `${Math.floor(diffMs / (60 * 1000))} menit yang lalu`;
          } else if (diffMs < 0) {
            relative = 'beberapa saat lagi';
          }
          return `${absolute} (${relative})`;
        };

        const isBapApproved = data.bapStatus === 'Sudah disetujui';
        const baseParams = parseList(data.params || '[]');
        const orderReviewOriginalItems = parseList(data.orderReviewOriginal || '[]').map((item) => ({
          nama: item?.nama || item?.parameter_name || '-',
          kategori: item?.kategori || '-',
          qty: Number(item?.qty) || 0,
        }));
        const orderReviewFinalItems = parseList(data.orderReviewFinal || '[]').map((item) => ({
          nama: item?.nama || item?.parameter_name || '-',
          kategori: item?.kategori || '-',
          qty: Number(item?.qty) || 0,
        }));
        const detailItemsSource = baseParams;
        const detailItems = (Array.isArray(detailItemsSource) ? detailItemsSource : []).map((item) => {
          const qty = Number(item?.qty) || 0;
          const harga = Number(item?.harga) || 0;
          return {
            nama: item?.nama || '-',
            kategori: item?.kategori || getJenisPengukuran(item?.nama, item?.kategori || ''),
            qty,
            harga,
            subtotal: qty * harga,
          };
        });
        const detailTotal = detailItems.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
        const displayedTotal = detailItems.length > 0 ? detailTotal : (Number(data.total) || 0);

        setText('#detailKode', data.kode);
        setText('#detailTanggal', data.tanggal);
        setText('#detailTahap', data.tahap);
        setText('#detailPerusahaan', data.perusahaan);
        setText('#detailPenanggung', data.penanggung);
        setText('#detailEmail', data.email);
        setText('#detailTelepon', data.telepon);
        setText('#detailAlamat', data.alamat);
        setText('#detailJenisPerusahaan', data.jenisPerusahaan);
        setText('#detailProvinsi', data.provinsi);
        setText('#detailKota', data.kota);
        setText('#detailJumlahPekerja', data.jumlahPekerja);
        setText('#detailTotal', formatRupiah(displayedTotal));
        setText('#detailParamsTotal', formatRupiah(displayedTotal));
        setText('#detailJadwalMulai', data.jadwalMulaiLabel);
        setText('#detailJadwalSelesai', data.jadwalSelesaiLabel);
        setText('#detailJadwalPcuCount', `${Number(data.jadwalPcuCount || 0)} orang`);
        setText('#detailJadwalSentAt', data.jadwalSentToUserAt);
        setText('#detailScheduleApprovalDate', `${data.jadwalMulaiLabel || '-'} s/d ${data.jadwalSelesaiLabel || '-'}`);
        setText('#detailScheduleApprovalPcuCount', `${Number(data.jadwalPcuCount || 0)} orang`);

        const renderOrderReviewBody = (selector, items, emptyText) => {
          const body = modalEl.querySelector(selector);
          if (!body) return;

          if (!Array.isArray(items) || items.length === 0) {
            body.innerHTML = `<tr><td colspan="4" class="text-center text-muted py-3">${emptyText}</td></tr>`;
            return;
          }

          body.innerHTML = items.map((item, idx) => `
            <tr>
              <td>${idx + 1}</td>
              <td>${item.kategori || '-'}</td>
              <td>${item.nama || '-'}</td>
              <td class="text-center">${item.qty}</td>
            </tr>
          `).join('');
        };

        const billingSection = modalEl.querySelector('#detailBillingSection');
        const scheduleSection = modalEl.querySelector('#detailScheduleSection');
        const scheduleApprovalSection = modalEl.querySelector('#detailScheduleApproval');
        const scheduleApprovalBadge = modalEl.querySelector('#detailScheduleApprovalBadge');
        const scheduleApproveBtn = modalEl.querySelector('#detailScheduleApproveBtn');
        const scheduleRejectBtn = modalEl.querySelector('#detailScheduleRejectBtn');
        const orderReviewSection = modalEl.querySelector('#detailOrderReviewSection');
        const orderReviewNoteWrap = modalEl.querySelector('#detailOrderReviewNoteWrap');
        const invoiceWarning = modalEl.querySelector('#detailInvoiceWarning');
        const billingInvoiceLink = modalEl.querySelector('#detailBillingInvoiceLink');
        const invoiceVerifyBtn = modalEl.querySelector('#detailInvoiceVerifyBtn');
        const invoiceVerifiedInfo = modalEl.querySelector('#detailInvoiceVerifiedInfo');
        const billingKodeLink = modalEl.querySelector('#detailBillingKodeLink');
        const billingRenewalBtn = modalEl.querySelector('#detailBillingRenewalBtn');
        const billingGuideLink = modalEl.querySelector('#detailBillingGuideLink');
        const billingRenewalInfo = modalEl.querySelector('#detailBillingRenewalInfo');
        const billingPaidInfo = modalEl.querySelector('#detailBillingPaidInfo');
        const billingVerifiedInfo = modalEl.querySelector('#detailBillingVerifiedInfo');
        const billingExpiredInfo = modalEl.querySelector('#detailBillingExpiredInfo');
        const billingPaymentProofInput = modalEl.querySelector('#detailBillingPaymentProof');
        const billingPaymentProofHelp = modalEl.querySelector('#detailBillingPaymentProofHelp');
        const billingPaymentProofInfo = modalEl.querySelector('#detailBillingPaymentProofInfo');
        const billingPaymentProofLink = modalEl.querySelector('#detailBillingPaymentProofLink');
        const billingPaidCheckbox = modalEl.querySelector('#detailBillingPaidCheckbox');
        const billingConfirmBtn = modalEl.querySelector('#detailBillingConfirmBtn');
        const billingPaymentSection = modalEl.querySelector('#detailBillingPaymentSection');
        // Section surat tagihan/billing muncul sejak surat tagihan dikirim ke pemohon.
        const hasBillingData = !!(
          data.invoiceUrl || data.invoiceVerifyUrl || data.invoiceVerifiedAt
          || data.billingSent || data.billingUrl || data.billingPaidAt || data.billingVerifiedAt || data.billingExpiresAt
        );
        const hasScheduleData = !!(
          (data.jadwalMulaiLabel && data.jadwalMulaiLabel !== '-')
          || (data.jadwalSelesaiLabel && data.jadwalSelesaiLabel !== '-')
          || Number(data.jadwalPcuCount || 0) > 0
          || data.jadwalSentToUserAt
        );
        const shouldShowOrderReviewSection = data.orderReviewHasComparison
          && (orderReviewOriginalItems.length > 0 || orderReviewFinalItems.length > 0);

        if (orderReviewSection) {
          orderReviewSection.classList.toggle('d-none', !shouldShowOrderReviewSection);
        }
        renderOrderReviewBody('#detailOrderReviewOriginalBody', orderReviewOriginalItems, 'Data pesanan awal tidak tersedia.');
        renderOrderReviewBody('#detailOrderReviewFinalBody', orderReviewFinalItems, 'Data pesanan akhir tidak tersedia.');
        if (orderReviewNoteWrap) {
          const hasOrderReviewNote = shouldShowOrderReviewSection && !!(data.orderReviewNote || '').trim();
          orderReviewNoteWrap.classList.toggle('d-none', !hasOrderReviewNote);
          if (hasOrderReviewNote) {
            setText('#detailOrderReviewNote', data.orderReviewNote);
          }
        }

        if (scheduleSection) {
          scheduleSection.classList.toggle('d-none', !hasScheduleData);
        }
        if (scheduleApprovalSection) {
          scheduleApprovalSection.classList.toggle('d-none', !data.jadwalCanApprove);
        }
        if (scheduleApprovalBadge) {
          scheduleApprovalBadge.textContent = data.jadwalCanApprove ? 'Menunggu ACC User' : 'Sudah Direspons';
        }
        if (scheduleApproveBtn) {
          scheduleApproveBtn.setAttribute('data-approve-url', data.jadwalApproveUrl || '');
          scheduleApproveBtn.classList.toggle('d-none', !data.jadwalCanApprove);
        }
        if (scheduleRejectBtn) {
          scheduleRejectBtn.setAttribute('data-reject-url', data.jadwalRejectUrl || '');
          scheduleRejectBtn.classList.toggle('d-none', !data.jadwalCanApprove);
        }
        if (billingSection) {
          billingSection.classList.toggle('d-none', !hasBillingData);
        }
        if (billingPaymentSection) {
          billingPaymentSection.classList.toggle('d-none', !data.billingSent);
        }
        if (billingInvoiceLink) {
          if (data.invoiceUrl) {
            billingInvoiceLink.classList.remove('d-none');
            billingInvoiceLink.setAttribute('href', data.invoiceUrl);
            billingInvoiceLink.textContent = data.invoiceLinkLabel || 'Lihat Surat Tagihan';
            if ((data.invoiceLinkLabel || '').toLowerCase().includes('kuitansi')) {
              billingInvoiceLink.setAttribute('data-download-once', '1');
              billingInvoiceLink.setAttribute('data-page-loader', 'skip');
              billingInvoiceLink.removeAttribute('target');
              billingInvoiceLink.removeAttribute('rel');
            } else {
              billingInvoiceLink.removeAttribute('data-download-once');
              billingInvoiceLink.removeAttribute('data-page-loader');
              billingInvoiceLink.setAttribute('target', '_blank');
              billingInvoiceLink.setAttribute('rel', 'noopener');
              billingInvoiceLink.classList.remove('is-loading', 'disabled');
              billingInvoiceLink.removeAttribute('aria-disabled');
            }
          } else {
            billingInvoiceLink.classList.add('d-none');
            billingInvoiceLink.removeAttribute('data-download-once');
            billingInvoiceLink.removeAttribute('data-page-loader');
            billingInvoiceLink.classList.remove('is-loading', 'disabled');
            billingInvoiceLink.removeAttribute('aria-disabled');
          }
        }
        if (invoiceWarning) {
          invoiceWarning.classList.toggle('d-none', !(data.invoiceUrl && !data.invoiceVerifiedAt));
        }
        if (invoiceVerifyBtn) {
          const canVerify = !!data.invoiceVerifyUrl;
          invoiceVerifyBtn.classList.toggle('d-none', !canVerify);
          invoiceVerifyBtn.disabled = !canVerify;
          invoiceVerifyBtn.onclick = async () => {
            if (!data.invoiceVerifyUrl) return;

            const proceed = window.Swal
              ? await window.Swal.fire({
                  icon: 'warning',
                  title: 'ACC surat tagihan?',
                  text: 'Setelah ACC surat tagihan, cek web secara berkala. Jika kode billing sudah muncul, pembayaran harus segera dilakukan karena masa berlakunya hanya 24 jam.',
                  showCancelButton: true,
                  confirmButtonText: 'ACC',
                  cancelButtonText: 'Batal',
                }).then((r) => r.isConfirmed)
              : confirm('ACC surat tagihan sekarang?');
            if (!proceed) return;

            invoiceVerifyBtn.disabled = true;
            try {
              const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
              const response = await fetch(data.invoiceVerifyUrl, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': csrf,
                  'Accept': 'application/json',
                },
              });
              const payload = await response.json().catch(() => ({}));
              if (!response.ok) throw new Error(payload.message || 'Gagal ACC surat tagihan.');
              await notify('success', payload.message || 'Surat tagihan berhasil di-ACC.');
              window.location.reload();
            } catch (error) {
              await notify('error', error.message || 'Gagal ACC surat tagihan.');
              invoiceVerifyBtn.disabled = false;
            }
          };
        }
        if (invoiceVerifiedInfo) {
          invoiceVerifiedInfo.textContent = data.invoiceVerifiedAt
            ? ('Surat tagihan di-ACC: ' + data.invoiceVerifiedAt)
            : 'Surat tagihan belum di-ACC.';
        }
        if (billingKodeLink) {
          if (data.billingUrl) {
            billingKodeLink.classList.remove('d-none');
            billingKodeLink.setAttribute('href', data.billingUrl);
            billingKodeLink.setAttribute('data-download-once', '1');
            billingKodeLink.setAttribute('data-page-loader', 'skip');
            billingKodeLink.removeAttribute('target');
            billingKodeLink.removeAttribute('rel');
          } else {
            billingKodeLink.classList.add('d-none');
            billingKodeLink.removeAttribute('data-download-once');
            billingKodeLink.removeAttribute('data-page-loader');
            billingKodeLink.classList.remove('is-loading', 'disabled');
            billingKodeLink.removeAttribute('aria-disabled');
          }
        }
        if (billingRenewalBtn) {
          const canRequestRenewal = !!data.billingRenewalRequestUrl;
          billingRenewalBtn.classList.toggle('d-none', !canRequestRenewal);
          billingRenewalBtn.disabled = !canRequestRenewal || !!data.billingRenewalRequested;
          billingRenewalBtn.textContent = data.billingRenewalRequested ? 'Pengajuan Kode Billing Ulang Terkirim' : 'Ajukan Kode Billing Ulang';
          billingRenewalBtn.onclick = async () => {
            if (!data.billingRenewalRequestUrl) return;
            if (data.billingRenewalRequested) return;

            const confirmedExpired = window.Swal
              ? await window.Swal.fire({
                  icon: 'question',
                  title: 'Ajukan kode billing ulang?',
                  text: 'Apakah kode billing/pembayaran sudah exp?',
                  showCancelButton: true,
                  confirmButtonText: 'Ya, sudah exp',
                  cancelButtonText: 'Batal',
                }).then((r) => r.isConfirmed)
              : confirm('Apakah kode billing/pembayaran sudah exp?');

            if (!confirmedExpired) return;

            billingRenewalBtn.disabled = true;
            try {
              const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
              const response = await fetch(data.billingRenewalRequestUrl, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': csrf,
                  'Accept': 'application/json',
                },
              });
              const payload = await response.json().catch(() => ({}));
              if (!response.ok) throw new Error(payload.message || 'Gagal mengajukan kode billing ulang.');
              data.billingRenewalRequested = true;
              await notify('success', payload.message || 'Pengajuan kode billing ulang berhasil dikirim ke admin.');
              window.location.reload();
            } catch (error) {
              await notify('error', error.message || 'Gagal mengajukan kode billing ulang.');
              billingRenewalBtn.disabled = false;
            }
          };
        }
        if (billingGuideLink) {
          const showBillingGuide = !!(
            data.billingUrl
            || data.billingSent
            || data.billingPaidAt
            || data.billingVerifiedAt
            || data.billingExpiresAt
            || data.billingRenewalRequestUrl
            || data.billingRenewalRequested
          );
          billingGuideLink.classList.toggle('d-none', !showBillingGuide);
        }
        if (billingRenewalInfo) {
          billingRenewalInfo.classList.toggle('d-none', !data.billingRenewalRequested);
          billingRenewalInfo.textContent = data.billingRenewalRequested
            ? 'Pengajuan kode billing ulang sudah dikirim. Mohon tunggu admin mengupload dokumen kode billing terbaru.'
            : '';
        }
        if (billingPaidInfo) {
          billingPaidInfo.textContent = data.billingPaidAt
            ? ('Konfirmasi bayar terkirim: ' + data.billingPaidAt)
            : 'Belum ada konfirmasi pembayaran.';
        }
        if (billingVerifiedInfo) {
          billingVerifiedInfo.textContent = data.billingVerifiedAt
            ? ('Pembayaran terverifikasi: ' + data.billingVerifiedAt + '. Menunggu kuitansi diteruskan petugas.')
            : '';
        }
        if (billingExpiredInfo) {
          if (data.billingExpired) {
            billingExpiredInfo.classList.remove('d-none');
            billingExpiredInfo.textContent = data.billingExpiresAt
              ? ('Kode billing expired pada: ' + data.billingExpiresAt + '. Mohon minta admin kirim ulang kode billing.')
              : 'Kode billing sudah expired. Mohon minta admin kirim ulang.';
          } else {
            billingExpiredInfo.classList.add('d-none');
            billingExpiredInfo.textContent = '';
          }
        }
        const hasUploadedPaymentProof = !!data.billingPaymentProofUrl;
        const canSubmitPaymentConfirmation = !!(data.billingSent && !data.billingPaid && data.billingConfirmUrl);
        const refreshBillingPaymentActionState = () => {
          const hasSelectedFile = !!billingPaymentProofInput?.files?.length;
          const hasProof = hasUploadedPaymentProof || hasSelectedFile;
          if (billingPaidCheckbox) {
            billingPaidCheckbox.disabled = !canSubmitPaymentConfirmation || !hasProof;
          }
          if (billingConfirmBtn) {
            billingConfirmBtn.disabled = !canSubmitPaymentConfirmation || !hasProof || !billingPaidCheckbox?.checked;
          }
        };
        if (billingPaymentProofInput) {
          billingPaymentProofInput.value = '';
          billingPaymentProofInput.disabled = !canSubmitPaymentConfirmation;
          billingPaymentProofInput.setAttribute('accept', '.pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.txt,.csv');
          billingPaymentProofInput.onchange = () => {
            refreshBillingPaymentActionState();
          };
        }
        if (billingPaymentProofHelp) {
          billingPaymentProofHelp.textContent = hasUploadedPaymentProof
            ? ('Bukti pembayaran sudah diupload' + (data.billingPaymentProofUploadedAt ? (': ' + data.billingPaymentProofUploadedAt) : '') + '.')
            : 'Wajib upload bukti pembayaran sebelum mencentang konfirmasi. Maksimal 10 MB.';
        }
        if (billingPaymentProofInfo) {
          billingPaymentProofInfo.classList.toggle('d-none', !hasUploadedPaymentProof);
        }
        if (billingPaymentProofLink) {
          if (hasUploadedPaymentProof) {
            billingPaymentProofLink.textContent = data.billingPaymentProofName || 'Lihat bukti pembayaran';
            billingPaymentProofLink.setAttribute('href', data.billingPaymentProofUrl);
          } else {
            billingPaymentProofLink.textContent = '';
            billingPaymentProofLink.setAttribute('href', '#');
          }
        }
        if (billingPaidCheckbox) {
          billingPaidCheckbox.checked = !!data.billingPaid;
          billingPaidCheckbox.onchange = () => {
            refreshBillingPaymentActionState();
          };
        }
        if (billingConfirmBtn) {
          refreshBillingPaymentActionState();
          billingConfirmBtn.onclick = async () => {
            if (!data.billingConfirmUrl) return;
            const paymentProofFile = billingPaymentProofInput?.files?.[0];
            if (!hasUploadedPaymentProof && !paymentProofFile) {
              await notify('warning', 'Upload bukti pembayaran terlebih dahulu.');
              return;
            }
            if (!billingPaidCheckbox?.checked) {
              await notify('warning', 'Centang konfirmasi pembayaran terlebih dahulu.');
              return;
            }

            billingConfirmBtn.disabled = true;
            try {
              const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
              const formData = new FormData();
              if (paymentProofFile) {
                formData.append('payment_proof', paymentProofFile);
              }
              const response = await fetch(data.billingConfirmUrl, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': csrf,
                  'Accept': 'application/json',
                },
                body: formData,
              });
              const payload = await response.json().catch(() => ({}));
              if (!response.ok) throw new Error(payload.message || 'Gagal mengirim konfirmasi pembayaran.');

              await notify('success', payload.message || 'Konfirmasi pembayaran berhasil dikirim.');
              window.location.reload();
            } catch (error) {
              await notify('error', error.message || 'Gagal mengirim konfirmasi pembayaran.');
              refreshBillingPaymentActionState();
            }
          };
        }

        const cancelSection = modalEl.querySelector('#detailCancelSection');
        const hasCancelInfo = !!(data.cancelReason || data.cancelNote || data.cancelledAt);
        if (cancelSection) {
          cancelSection.classList.toggle('d-none', !hasCancelInfo);
        }
        if (hasCancelInfo) {
          setText('#detailCancelReason', data.cancelReason);
          setText('#detailCancelNote', data.cancelNote || '-');
          const cancelledAtEl = modalEl.querySelector('#detailCancelledAt');
          const renderCancelledAt = () => {
            if (!cancelledAtEl) return;
            cancelledAtEl.textContent = formatCancelledAtText(data.cancelledAtIso, data.cancelledAt);
          };
          renderCancelledAt();
          if (cancelledAtTimer) {
            window.clearInterval(cancelledAtTimer);
            cancelledAtTimer = null;
          }
          if (data.cancelledAtIso) {
            cancelledAtTimer = window.setInterval(renderCancelledAt, 30000);
          }
        }

        const paramBody = modalEl.querySelector('#detailParamsBody');
        if (paramBody) {
          if (!Array.isArray(detailItems) || detailItems.length === 0) {
            paramBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada parameter.</td></tr>';
          } else {
            paramBody.innerHTML = detailItems.map((p, idx) => {
              return `
                <tr>
                  <td>${idx + 1}</td>
                  <td>${p.kategori || '-'}</td>
                  <td>${p.nama || '-'}</td>
                  <td class="text-center">${p.qty}</td>
                  <td class="text-end">${formatRupiah(p.harga)}</td>
                  <td class="text-end">${formatRupiah(p.subtotal)}</td>
                </tr>
              `;
            }).join('');
          }
        }

        const docList = modalEl.querySelector('#detailDocs');
        if (docList) {
          let docs = [];
          try { docs = JSON.parse(data.dokumen || '[]'); } catch (e) { docs = []; }
          const hasBapDoc = data.bapDocReady;
          const lokasiRows = parseList(data.bapLokasiRows || '[]');
          const orderParams = parseList(data.bapParameterOrder || '[]');
          const testParams = parseList(data.bapParameterPengujian || '[]');
          const changes = computeChanges(orderParams, testParams);
          if (!Array.isArray(docs)) {
            docs = [];
          }
          const customerUploads = docs.filter((doc) => (doc?.jenis || '') === 'upload_pelanggan');
          const downloadDocs = docs.filter((doc) => (doc?.jenis || '') !== 'upload_pelanggan');

          const renderDocLink = (doc) => {
            const isDownloadDoc = (doc?.jenis || '') !== 'upload_pelanggan';
            const isLhuDoc = ((doc.url || '') === (data.lhuUrl || '') && data.lhuUrl);
            const linkAttrs = isDownloadDoc
              ? ''
              : 'target="_blank" rel="noopener noreferrer"';
            const loadingAttrs = (isDownloadDoc && !isLhuDoc)
              ? 'data-download-once="1"'
              : '';

            return `
            <a
              href="${doc.url || '#'}"
              ${linkAttrs}
              ${loadingAttrs}
              class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 text-decoration-none ${isLhuDoc ? 'lhu-view-btn' : ''}"
              data-lhu-url="${isLhuDoc ? (doc.url || '') : ''}"
              data-lhu-needs-ulasan="${(isLhuDoc && data.lhuNeedsUlasan) ? '1' : '0'}"
              data-lhu-ulasan-submit-url="${isLhuDoc ? (data.lhuUlasanSubmitUrl || '') : ''}"
              data-kode="${data.kode || '-'}"
            >
              <span class="fw-semibold text-secondary download-label">${doc.nama || 'Dokumen'}</span>
              <span class="fs-5 text-primary download-icon">&#128190;</span>
            </a>
          `;
          };

          const items = [];
          items.push('<div class="small fw-semibold text-muted mb-2">Dokumen Upload-an Pelanggan</div>');
          if (customerUploads.length > 0) {
            items.push(...customerUploads.map(renderDocLink));
          } else {
            items.push('<div class="text-muted small mb-3">Belum ada dokumen upload pelanggan.</div>');
          }

          items.push('<div class="small fw-semibold text-muted mt-2 mb-2">Dokumen untuk Diunduh</div>');
          if (hasBapDoc && isBapApproved) {
            items.push(`
              <button type="button" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 w-100 bg-white bap-doc-btn" data-bap-preview>
                <span class="fw-semibold text-secondary">Dokumen BAP</span>
                <span class="fs-5 text-primary">&#128190;</span>
              </button>
            `);
            items.push(`
              <button type="button" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 w-100 bg-white bap-doc-btn" data-bap-rincian>
                <span class="fw-semibold text-secondary">Dokumen Rincian Pengambilan Sampel</span>
                <span class="fs-5 text-primary">&#128190;</span>
              </button>
            `);
            if (changes.length > 0) {
              items.push(`
                <button type="button" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 w-100 bg-white bap-doc-btn" data-bap-penambahan>
                  <span class="fw-semibold text-secondary">Form Penambahan/Pengurangan Pengujian</span>
                  <span class="fs-5 text-primary">&#128190;</span>
                </button>
              `);
            }
          }
          if (downloadDocs.length > 0) {
            items.push(...downloadDocs.map(renderDocLink));
          } else if (!(hasBapDoc && isBapApproved)) {
            items.push('<div class="text-muted small">Belum ada dokumen unduhan.</div>');
          }
          docList.innerHTML = items.join('');
        }

        const actionSection = modalEl.querySelector('#detailPenawaranAction');
        const catatanWrap = modalEl.querySelector('#detailPenawaranCatatanWrap');
        const catatanEl = modalEl.querySelector('#detailPenawaranCatatan');
        const fileInput = modalEl.querySelector('#penawaranSignedFile');
        const uploadTrigger = modalEl.querySelector('#penawaranUploadTrigger');
        const acceptBtn = modalEl.querySelector('[data-penawaran-accept]');
        const rejectBtn = modalEl.querySelector('[data-penawaran-reject]');
        const actionInfo = modalEl.querySelector('#penawaranActionInfo');
        const canAction = (data.penawaranDocStatus || '') === 'sent' && data.penawaranDocId && data.permohonanId;
        const penawaranCatatan = String(data.penawaranCatatan || '').trim();

        if (actionSection) {
          actionSection.classList.toggle('d-none', !canAction);
        }
        if (catatanWrap && catatanEl) {
          const showCatatan = canAction && penawaranCatatan !== '';
          catatanWrap.classList.toggle('d-none', !showCatatan);
          catatanEl.textContent = showCatatan ? penawaranCatatan : '-';
        }

        if (actionInfo) {
          if (canAction) {
              actionInfo.textContent = 'Maksimal ukuran file 5 MB (PDF/DOC/DOCX).';
          }
        }

        if (fileInput) {
          fileInput.value = '';
        }
        if (uploadTrigger) {
          uploadTrigger.onclick = null;
        }
        if (fileInput && actionInfo) {
          fileInput.onchange = () => {
            const selectedName = fileInput.files && fileInput.files.length > 0
              ? fileInput.files[0].name
              : '';
            if (selectedName) {
              actionInfo.textContent = `File terpilih: ${selectedName}. Maksimal 5 MB (PDF/DOC/DOCX).`;
              return;
            }
            actionInfo.textContent = 'Maksimal ukuran file 5 MB (PDF/DOC/DOCX).';
          };
        }

        if (acceptBtn) {
          acceptBtn.onclick = async () => {
            if (!canAction) return;
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Dokumen belum diunggah', text: 'Unggah dokumen penawaran yang sudah disetujui.' });
              } else {
                alert('Unggah dokumen penawaran yang sudah disetujui.');
              }
              return;
            }

            const confirmed = typeof Swal === 'undefined'
              ? confirm('Terima penawaran dan kirim dokumen ke admin?')
              : await Swal.fire({
                  icon: 'question',
                  title: 'Terima penawaran?',
                  text: 'Dokumen penawaran akan dikirim ke admin untuk proses penjadwalan.',
                  showCancelButton: true,
                  confirmButtonText: 'Ya, kirim',
                  cancelButtonText: 'Batal',
                }).then((r) => r.isConfirmed);

            if (!confirmed) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const formData = new FormData();
            formData.append('dokumen_id', data.penawaranDocId);
            formData.append('signed_document', fileInput.files[0]);

            try {
              const response = await fetch(`/riwayat_pelayanan/${data.permohonanId}/penawaran/accept`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: formData,
              });
              const result = await response.json().catch(() => ({}));
              if (!response.ok) throw new Error(result.message || 'Gagal mengirim dokumen penawaran.');

              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Penawaran disetujui', text: result.message || 'Dokumen berhasil dikirim.' })
                  .then(() => window.location.reload());
              } else {
                alert(result.message || 'Dokumen berhasil dikirim.');
                window.location.reload();
              }
            } catch (err) {
              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Gagal mengirim dokumen penawaran.' });
              } else {
                alert(err.message || 'Gagal mengirim dokumen penawaran.');
              }
            }
          };
        }

        if (rejectBtn) {
          rejectBtn.onclick = async () => {
            if (!canAction) return;
            let reason = '';
            if (typeof Swal !== 'undefined') {
              const result = await Swal.fire({
                icon: 'warning',
                title: 'Tolak penawaran',
                input: 'textarea',
                inputLabel: 'Alasan penolakan (opsional)',
                inputPlaceholder: 'Tuliskan alasan penolakan...',
                showCancelButton: true,
                confirmButtonText: 'Tolak',
                cancelButtonText: 'Batal',
              });
              if (!result.isConfirmed) return;
              reason = result.value || '';
            } else {
              const confirmed = confirm('Tolak penawaran ini?');
              if (!confirmed) return;
              reason = prompt('Alasan penolakan (opsional):') || '';
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
              const response = await fetch(`/riwayat_pelayanan/${data.permohonanId}/penawaran/reject`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrf,
                  'Accept': 'application/json',
                },
                body: JSON.stringify({ dokumen_id: data.penawaranDocId, note: reason }),
              });
              const result = await response.json().catch(() => ({}));
              if (!response.ok) throw new Error(result.message || 'Gagal menolak penawaran.');

              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Penawaran ditolak', text: result.message || 'Penawaran ditolak.' })
                  .then(() => window.location.reload());
              } else {
                alert(result.message || 'Penawaran ditolak.');
                window.location.reload();
              }
            } catch (err) {
              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Gagal menolak penawaran.' });
              } else {
                alert(err.message || 'Gagal menolak penawaran.');
              }
            }
          };
        }

        const bapSection = modalEl.querySelector('#detailBapApproval');
        const bapDocsEl = modalEl.querySelector('#detailBapDocs');
        const bapStatusEl = modalEl.querySelector('#detailBapStatus');
        const bapApproveBtn = modalEl.querySelector('[data-bap-approve]');

        if (bapStatusEl) {
          bapStatusEl.textContent = data.bapStatus || 'Belum mengirim BAP';
          bapStatusEl.className = 'badge ' + (data.bapStatus === 'Sudah disetujui'
            ? 'bg-success'
            : data.bapStatus === 'Menunggu persetujuan'
              ? 'bg-warning text-dark'
              : 'bg-secondary');
        }

        if (bapSection) {
          bapSection.classList.toggle('d-none', !data.bapCanApprove);
        }
        if (bapDocsEl) {
          const docItems = [];
          if (data.bapDocReady) {
            docItems.push(`<button type="button" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 w-100 bg-white bap-doc-btn" data-bap-preview><span class="fw-semibold text-secondary">Dokumen BAP</span><span class="fs-5 text-primary">&#128190;</span></button>`);
            docItems.push(`<button type="button" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 w-100 bg-white bap-doc-btn" data-bap-rincian><span class="fw-semibold text-secondary">Dokumen Rincian Pengambilan Sampel</span><span class="fs-5 text-primary">&#128190;</span></button>`);
            const changes = computeChanges(parseList(data.bapParameterOrder || '[]'), parseList(data.bapParameterPengujian || '[]'));
            if (changes.length > 0) {
              docItems.push(`<button type="button" class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 mb-2 w-100 bg-white bap-doc-btn" data-bap-penambahan><span class="fw-semibold text-secondary">Form Penambahan/Pengurangan Pengujian</span><span class="fs-5 text-primary">&#128190;</span></button>`);
            }
          }
          bapDocsEl.innerHTML = docItems.join('') || '<div class="text-muted small">Dokumen akan muncul setelah admin mengirim.</div>';
        }

        if (bapApproveBtn) {
          bapApproveBtn.onclick = async () => {
            if (!data.permohonanId) return;
            const captchaValue = document.querySelector('[name="g-recaptcha-response"]')?.value || '';
            if (!captchaValue) {
              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Captcha wajib', text: 'Silakan selesaikan reCAPTCHA terlebih dahulu.' });
              } else {
                alert('Silakan selesaikan reCAPTCHA terlebih dahulu.');
              }
              return;
            }

            const confirmed = typeof Swal === 'undefined'
              ? confirm('Setujui BAP? Dengan menyetujui, dokumen akan ditandatangani otomatis menggunakan TTD saat checkout.')
              : await Swal.fire({
                  icon: 'question',
                  title: 'Setujui BAP?',
                  text: 'Dengan menyetujui, dokumen akan ditandatangani otomatis menggunakan TTD saat checkout.',
                  showCancelButton: true,
                  confirmButtonText: 'Setujui',
                  cancelButtonText: 'Batal',
                }).then((r) => r.isConfirmed);

            if (!confirmed) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            try {
              const response = await fetch(`/riwayat_pelayanan/${data.permohonanId}/bap/approve`, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrf,
                  'Accept': 'application/json',
                },
                body: JSON.stringify({
                  'g-recaptcha-response': captchaValue,
                }),
              });
              const result = await response.json().catch(() => ({}));
              if (!response.ok) throw new Error(result.message || 'Gagal menyetujui BAP.');

              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message || 'BAP berhasil disetujui.' })
                  .then(() => window.location.reload());
              } else {
                alert(result.message || 'BAP berhasil disetujui.');
                window.location.reload();
              }
            } catch (err) {
              if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Gagal menyetujui BAP.' });
              } else {
                alert(err.message || 'Gagal menyetujui BAP.');
              }
            }
          };
        }

        const openBapPreview = () => {
            const perusahaan = data.perusahaan || '-';
            const alamat = data.bapAlamat || data.alamat || '-';
            const jenisPerusahaan = data.bapJenisPerusahaan || '-';
            const tanggalMulaiRaw = data.bapTanggalMulai || data.bapTanggal || data.tanggal || '-';
            const tanggalSelesaiRaw = data.bapTanggalSelesai || tanggalMulaiRaw;
            const lokasi = data.bapLokasi || '-';
            const ketuaTimNama = data.bapKetuaTimNama || '......................................................';
            const ketuaTimTtd = data.bapKetuaTimTtd || '';
            const penanggungTtd = data.bapPenanggungTtd || '';
            let pcu = [];
            try { pcu = JSON.parse(data.bapPcu || '[]'); } catch (err) { pcu = []; }
            const pcuList = pcu.length > 0 ? pcu : ['......................................................'];
            const formatTanggal = (value) => {
              const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
              ];
              const parts = (value || '').split('-');
              if (parts.length !== 3) return value || '-';
              const year = parts[0];
              const monthIndex = Number(parts[1]) - 1;
              const day = Number(parts[2]);
              if (Number.isNaN(monthIndex) || monthIndex < 0 || monthIndex > 11) {
                return value || '-';
              }
              if (Number.isNaN(day)) return value || '-';
              return `${day} ${months[monthIndex]} ${year}`;
            };
            const tanggal = formatTanggal(tanggalMulaiRaw);
            const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;
            const bapParams = parseList(data.bapParameterPengujian || '[]');
            const bapLokasiRows = parseList(data.bapLokasiRows || '[]');
            const typePool = [];
            (Array.isArray(bapParams) ? bapParams : []).forEach((item) => {
              typePool.push(getJenisPengukuran(item?.nama, item?.kategori || ''));
            });
            (Array.isArray(bapLokasiRows) ? bapLokasiRows : []).forEach((row) => {
              (row?.dokumen_list || []).forEach((doc) => {
                (doc?.parameter || []).forEach((item) => {
                  typePool.push(getJenisPengukuran(item?.nama, item?.kategori || ''));
                });
              });
            });
            const samplingTypes = Array.from(new Set(typePool.filter((val) => val && val !== '-')));
            const samplingText = (() => {
              if (samplingTypes.length === 2) return `${samplingTypes[0]} dan ${samplingTypes[1]}`;
              if (samplingTypes.length > 0) return samplingTypes.join(', ');
              return 'Emisi, Ambien dan Lingkungan Kerja';
            })();

            const w = window.open('', '_blank');
            if (!w) return;
            w.document.write(`
              <html>
                <head>
                  <title>BAP ${data.kode || ''}</title>
                  <style>
                    @page { margin: 12mm 16mm 18mm 16mm; }
                    html, body { height: 100%; }
                    body { font-family: "Times New Roman", serif; padding: 12mm 16mm 18mm 16mm; color: #111; }
                    .page { min-height: 100%; position: relative; padding-top: 8mm; }
                    .page-body { padding-bottom: 70px; }
                    .page-footer {
                      position: fixed;
                      left: 16mm;
                      right: 16mm;
                      bottom: 24px;
                    }
                    @media print {
                      html, body { height: auto; }
                      body { padding: 0; }
                      .page { min-height: 0; page-break-after: avoid; }
                      .page-body { padding-bottom: 60px; }
                    }
                    .letter-header { display: table; width: 100%; margin: 0; }
                    .header-shell {
                      width: 88%;
                      margin: 0 auto;
                    }
                    .logo-wrap, .header-wrap { display: table-cell; vertical-align: middle; }
                    .logo-wrap { width: 70px; padding-left: 0; text-align: center; }
                    .logo { width: 60px; height: 60px; object-fit: contain; display: block; margin: 0 auto; }
                    .header-wrap {
                      text-align: left;
                      font-family: "Arial Narrow", Arial, sans-serif;
                      border-left: 2px solid #163e67;
                      padding-left: 12px;
                      padding-right: 0;
                      line-height: 1.08;
                    }
                    .header-wrap > .line,
                    .header-wrap > .addr {
                      display: block;
                      width: 100%;
                      text-align: left !important;
                      margin-left: 0;
                      margin-right: 0;
                    }
                    .line { font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: -0.02em; }
                    .header-wrap .line:first-of-type { font-size: 12px; font-weight: 500; }
                    .header-wrap .line:nth-of-type(2) { font-size: 13px; font-weight: 700; letter-spacing: -0.03em; }
                    .header-wrap .line:nth-of-type(3) { font-size: 13px; letter-spacing: -0.03em; }
                    .header-wrap .line:nth-of-type(4) { font-size: 15.6px; font-weight: 700; color: #163e67; line-height: 1.02; margin-top: 2px; letter-spacing: -0.05em; white-space: nowrap; }
                    .addr { font-size: 11px; margin-top: 3px; font-family: Arial, sans-serif; text-align: left; }
                    .addr .icon { color: #163e67; font-weight: 700; margin: 0 2px; }
                    .header-line { border-top: 2px solid #000; width: 100%; margin: 10px 0 18px; }
                    .document-content { width: 88%; margin: 0 auto; }
                    .title { text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 0.5px; margin-bottom: 16px; }
                    .paragraph { font-size: 13px; line-height: 1.6; text-align: justify; }
                    .list { margin: 14px 0 18px 24px; font-size: 13px; }
                    .list li { margin-bottom: 6px; }
                    .data-list { margin: 10px 0 16px; font-size: 13px; }
                    .data-list .row { display: flex; gap: 6px; margin-bottom: 8px; }
                    .data-list .label { width: 170px; }
                    .data-list .colon { width: 10px; }
                    .signatures { display: flex; justify-content: space-between; margin-top: 22px; font-size: 13px; }
                    .sign-col { width: 45%; text-align: center; }
                    .sign-space { height: 70px; }
                    .signature-img { display: block; margin: 6px auto 2px; max-height: 70px; max-width: 220px; object-fit: contain; }
                    .sign-name { margin-top: 4px; font-weight: bold; }
                    .footer-line { border-top: 1px solid #000; margin-top: 12px; }
                    .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 4px; }
                  </style>
                </head>
                <body>
                  <div class="page">
                    <div class="page-body">
                      <div class="header-shell">
                        <div class="letter-header">
                          <div class="logo-wrap">
                            <img src="${logoUrl}" alt="Logo" class="logo" id="printLogo">
                          </div>
                          <div class="header-wrap">
                            <div class="line">KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA</div>
                            <div class="line">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</div>
                            <div class="line">DAN KESELAMATAN DAN KESEHATAN KERJA</div>
                            <div class="line">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</div>
                            <div class="addr">Jl. Dukuh Menanggal No. 122 Surabaya, Telp. (031) 8280440, Email: balaik3surabaya@kemnaker.go.id</div>
                          </div>
                        </div>
                        <div class="header-line"></div>
                      </div>

                      <div class="document-content">
                        <div class="title">BERITA ACARA PENGAMBILAN SAMPEL</div>

                        <div class="paragraph">
                          Pada hari ini ${tanggal}, kami nama :
                        </div>
                        <ol class="list">
                          ${pcuList.map((name) => `<li>${name}</li>`).join('')}
                        </ol>

                        <div class="paragraph">
                          Petugas pengambil sampel udara dari BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA,
                          telah melakukan pengambilan sampel: ${samplingText} dengan
                          rincian sebagaimana pada lampiran dari :
                        </div>

                        <div class="data-list">
                          <div class="row"><span class="label">Nama Perusahaan</span><span class="colon">:</span><span>${perusahaan}</span></div>
                          <div class="row"><span class="label">Alamat Perusahaan</span><span class="colon">:</span><span>${alamat}</span></div>
                          <div class="row"><span class="label">Jenis Perusahaan</span><span class="colon">:</span><span>${jenisPerusahaan}</span></div>
                        </div>

                        <div class="paragraph">
                          Demikian Berita Acara ini dibuat dengan sebenar - benarnya dan tanpa paksaan.
                        </div>

                        <div class="signatures">
                          <div class="sign-col">
                            <div>Mengetahui :</div>
                            <div>Pimpinan Perusahaan/</div>
                            <div>Yang Mewakili</div>
                            ${penanggungTtd ? `<img src="${penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                            <div class="sign-name">(${data.penanggung || '.................................'})</div>
                          </div>
                          <div class="sign-col">
                            <div>Petugas Pengambil Sampel</div>
                            <div>Ketua Tim</div>
                            ${ketuaTimTtd ? `<img src="${ketuaTimTtd}" alt="Tanda tangan Ketua Tim" class="signature-img">` : '<div class="sign-space"></div>'}
                            <div class="sign-name">(${ketuaTimNama})</div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="page-footer">
                      <div class="footer-line"></div>
                      <div class="footer-meta">
                        <span>Tgl. terbit: 24 Desember 2024</span>
                        <span>No.: /F/7.3.12/BK3-SBY</span>
                      </div>
                    </div>
                  </div>
                  <script>
                    const logo = document.getElementById('printLogo');
                    const doPrint = () => setTimeout(() => window.print(), 250);
                    if (logo) {
                      logo.addEventListener('load', doPrint, { once: true });
                      logo.addEventListener('error', doPrint, { once: true });
                    } else {
                      doPrint();
                    }
                  <\/script>
                </body>
              </html>
            `);
            w.document.close();
        };
        modalEl.querySelectorAll('[data-bap-preview]').forEach((btn) => {
          btn.onclick = (e) => {
            e.preventDefault();
            openBapPreview();
          };
        });

        const openRincian = () => {
          const perusahaan = data.perusahaan || '-';
          const lokasiFallback = data.bapLokasi || '-';
          const ketuaTimNama = data.bapKetuaTimNama || '......................................................';
          const ketuaTimTtd = data.bapKetuaTimTtd || '';
          const penanggungTtd = data.bapPenanggungTtd || '';
          const tanggalMulaiRaw = data.bapTanggalMulai || data.bapTanggal || data.tanggal || '-';
          const tanggalSelesaiRaw = data.bapTanggalSelesai || tanggalMulaiRaw;
          const params = parseList(data.bapParameterPengujian || '[]');
          const lokasiRows = parseList(data.bapLokasiRows || '[]');
          const formatTanggal = (value) => {
            const months = [
              'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const parts = (value || '').split('-');
            if (parts.length !== 3) return value || '-';
            const year = parts[0];
            const monthIndex = Number(parts[1]) - 1;
            const day = Number(parts[2]);
            if (Number.isNaN(monthIndex) || monthIndex < 0 || monthIndex > 11) {
              return value || '-';
            }
            if (Number.isNaN(day)) return value || '-';
            return `${day} ${months[monthIndex]} ${year}`;
          };
          const formatTanggalRentang = (mulai, selesai) => {
            const mulaiFmt = formatTanggal(mulai);
            const selesaiFmt = formatTanggal(selesai);
            if (!mulai || !selesai || mulai === '-' || selesai === '-') return mulaiFmt;
            if (mulai === selesai) return mulaiFmt;
            return `${mulaiFmt} - ${selesaiFmt}`;
          };
          const tanggal = formatTanggalRentang(tanggalMulaiRaw, tanggalSelesaiRaw);
          const logoUrl = `${window.location.origin}/images/Logo%20Kemnaker.png`;
          const groupedByJenis = new Map();
          const addToJenisGroup = (item, rowLokasi) => {
            if (!item) return;
            const jenis = getJenisPengukuran(item?.nama, item?.kategori || '') || '-';
            if (!groupedByJenis.has(jenis)) {
              groupedByJenis.set(jenis, {
                jenis,
                lokasiSet: new Set(),
                paramKeys: new Set(),
                params: [],
              });
            }
            const group = groupedByJenis.get(jenis);
            if (rowLokasi) {
              group.lokasiSet.add(rowLokasi);
            }
            const paramNama = (item?.nama || '-').trim() || '-';
            const paramKey = paramNama.toLowerCase();
            if (!group.paramKeys.has(paramKey)) {
              group.paramKeys.add(paramKey);
              group.params.push(paramNama);
            }
          };

          if (Array.isArray(lokasiRows) && lokasiRows.length > 0) {
            lokasiRows.forEach((row) => {
              const rowLokasi = row?.lokasi || lokasiFallback || '-';
              (row?.dokumen_list || []).forEach((doc) => {
                (doc?.parameter || []).forEach((param) => addToJenisGroup(param, rowLokasi));
              });
            });
          }

          if (groupedByJenis.size === 0) {
            const fallbackLokasi = lokasiFallback || '-';
            const fallbackParams = params.length ? params : [{ nama: '-', kategori: '' }];
            fallbackParams.forEach((param) => addToJenisGroup(param, fallbackLokasi));
          }

          const groupedRows = Array.from(groupedByJenis.values());
          const rowsHtml = groupedRows.map((group, idx) => `
              <tr>
                <td class="col-no">${idx + 1}</td>
                <td>${(Array.from(group.lokasiSet).filter((val) => val && val !== '-').join(', ')) || lokasiFallback || '-'}</td>
                <td>${group.jenis || '-'}</td>
                <td>${group.params.length ? group.params.join(', ') : '-'}</td>
                <td><div class="note-text"></div></td>
              </tr>
          `).join('');

          const w = window.open('', '_blank');
          if (!w) return;
          w.document.write(`
            <html>
              <head>
                <title>Rincian Pengambilan Sampel</title>
                <style>
                  @page { margin: 18px 2cm 50px 2cm; }
                  html, body { height: 100%; }
                  body { font-family: "Times New Roman", serif; padding: 18px 2cm; color: #111; }
                  .page { min-height: 100%; position: relative; }
                  .page-body { padding-bottom: 50px; }
                  .page-footer { position: fixed; left: 2cm; right: 2cm; bottom: 18px; }
                  @media print {
                    html, body { height: auto; }
                    body { padding: 0; }
                    .page { min-height: 0; page-break-after: avoid; }
                    .page-body { padding-bottom: 44px; }
                  }
                  .doc-header { display: grid; grid-template-columns: 110px 1fr 120px; border: 1px solid #000; }
                  .doc-header > div { border-right: 1px solid #000; }
                  .doc-header > div:last-child { border-right: none; }
                  .logo-cell { display: flex; align-items: center; justify-content: center; padding: 10px; }
                  .logo-cell img { width: 64px; height: 64px; object-fit: contain; }
                  .head-text {
                    text-align: left;
                    padding: 8px 8px 7px;
                    font-size: 12px;
                    font-weight: 700;
                    line-height: 1.1;
                    font-family: "Arial Narrow", Arial, sans-serif;
                    border-left: 2px solid #163e67;
                  }
                  .head-text .kop-dir { white-space: nowrap; display: inline-block; font-size: 12px; }
                  .head-text .kop-main { color: #163e67; font-size: 13px; line-height: 1.02; display: inline-block; margin-top: 2px; white-space: nowrap; }
                  .head-text .kop-contact { font-size: 10px; font-weight: 500; font-family: Arial, sans-serif; text-transform: none; }
                  .meta-cell { display: grid; grid-template-rows: 1fr 1fr; font-size: 12px; }
                  .meta-cell div { border-bottom: 1px solid #000; padding: 6px 8px; display: flex; align-items: center; justify-content: center; text-align: center; }
                  .meta-cell div:last-child { border-bottom: none; }
                  .title { text-align: center; font-weight: bold; font-size: 13px; margin: 10px 0 8px; letter-spacing: 0.6px; }
                  .info-line { font-size: 12px; margin: 4px 0; }
                  .info-line .label { display: inline-block; width: 160px; }
                  .info-line .colon { display: inline-block; width: 10px; }
                  .info-line .fill { display: inline-block; border-bottom: 1px solid #000; width: 260px; height: 12px; vertical-align: baseline; }
                  .info-line .value { display: inline-block; min-width: 260px; }
                  .note-text { min-height: 12px; font-size: 12px; padding: 2px 0; }
                  table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
                  th, td { border: 1px solid #000; padding: 4px 5px; height: 20px; }
                  th { text-align: center; font-weight: bold; }
                  .col-no { width: 6%; text-align: center; }
                  .col-lokasi { width: 20%; }
                  .col-jenis { width: 28%; }
                  .col-parameter { width: 32%; }
                  .col-ket { width: 20%; }
                  .signatures { display: flex; justify-content: space-between; margin-top: 22px; font-size: 12px; }
                  .sign-col { width: 45%; text-align: center; }
                  .sign-space { height: 56px; }
                  .signature-img { display: block; margin: 6px auto 2px; max-height: 56px; max-width: 200px; object-fit: contain; }
                  .sign-name { margin-top: 4px; font-weight: bold; }
                  .notes { font-size: 11px; border-top: 2px solid #000; padding-top: 6px; margin-top: 16px; }
                  .footer-line { border-top: 1px solid #000; margin-top: 8px; }
                  .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 6px; }
                </style>
              </head>
              <body>
                <div class="page">
                  <div class="page-body">
                    <div class="doc-header">
                      <div class="logo-cell"><img src="${logoUrl}" alt="Logo" id="printLogo"></div>
                      <div class="head-text">
                        KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA<br>
                        <span class="kop-dir">DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN</span><br>
                        DAN KESELAMATAN DAN KESEHATAN KERJA<br>
                        <span class="kop-main">BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA</span><br>
                        <span class="kop-contact">Jl. Dukuh Menanggal No. 122 Surabaya, (031) 8280440, balaik3surabaya@kemnaker.go.id</span>
                      </div>
                      <div class="meta-cell"><div>Page : 1/1</div><div>Rev/Terb. : -/1</div></div>
                    </div>

                    <div class="title">RINCIAN&nbsp;&nbsp;PENGAMBILAN&nbsp;&nbsp;SAMPEL</div>

                    <div class="info-line"><span class="label">Nama Perusahaan</span><span class="colon">:</span><span class="value">${perusahaan}</span></div>
                    <div class="info-line"><span class="label">Tanggal Pengujian</span><span class="colon">:</span><span class="value">${tanggal}</span></div>

                    <table>
                      <thead>
                        <tr>
                          <th class="col-no">No.</th>
                          <th class="col-lokasi">Lokasi</th>
                          <th class="col-jenis">Jenis Pengukuran</th>
                          <th class="col-parameter">Parameter</th>
                          <th class="col-ket">Keterangan</th>
                        </tr>
                      </thead>
                      <tbody>
                        ${rowsHtml}
                      </tbody>
                    </table>

                    <div class="signatures">
                      <div class="sign-col">
                        <div>Mengetahui :</div>
                        <div>Pimpinan Perusahaan/</div>
                        <div>Yang Mewakili</div>
                        ${penanggungTtd ? `<img src="${penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                        <div class="sign-name">(${data.penanggung || '.................................'})</div>
                      </div>
                      <div class="sign-col">
                      <div>Petugas Pengambil Sampel</div>
                      <div>Ketua Tim</div>
                      ${ketuaTimTtd ? `<img src="${ketuaTimTtd}" alt="Tanda tangan Ketua Tim" class="signature-img">` : '<div class="sign-space"></div>'}
                      <div class="sign-name">(${ketuaTimNama})</div>
                    </div>
                    </div>

                    <div class="notes">
                      Keterangan : jenis pengukuran; E /A /LK, lokasi: namalokasi/cerobong, parameter: di isi semua parameter (fisik, kimia), keterangan: di isi informasi penting di perusahaan
                    </div>
                  </div>

                  <div class="page-footer">
                    <div class="footer-meta"><span>Tgl. terbit: 24 Desember 2024</span><span>No. : F/7.3.13/BK3-SBY</span></div>
                    <div class="footer-line"></div>
                  </div>
                </div>
                <script>
                  const logo = document.getElementById('printLogo');
                  const doPrint = () => setTimeout(() => window.print(), 250);
                  if (logo) {
                    logo.addEventListener('load', doPrint, { once: true });
                    logo.addEventListener('error', doPrint, { once: true });
                  } else {
                    doPrint();
                  }
                <\/script>
              </body>
            </html>
          `);
          w.document.close();
        };
        modalEl.querySelectorAll('[data-bap-rincian]').forEach((btn) => {
          btn.onclick = (e) => {
            e.preventDefault();
            openRincian();
          };
        });

        const openPenambahan = () => {
          const perusahaan = data.perusahaan || '-';
          const alamat = data.bapAlamat || data.alamat || '-';
          const penanggungNama = data.penanggung || '.................................';
          const penandatanganNama = data.bapPenandatanganNama || data.penanggung || '.................................';
          const penandatanganJabatan = data.bapPenandatanganJabatan || '.......................';
          const ketuaTimNama = data.bapKetuaTimNama || '......................................................';
          const ketuaTimTtd = data.bapKetuaTimTtd || '';
          const penanggungTtd = data.bapPenanggungTtd || '';
          const tanggalMulaiRaw = data.bapTanggalMulai || data.bapTanggal || data.tanggal || '-';
          const tanggalSelesaiRaw = data.bapTanggalSelesai || tanggalMulaiRaw;
          const formatTanggal = (value) => {
            const months = [
              'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const parts = (value || '').split('-');
            if (parts.length !== 3) return value || '-';
            const year = parts[0];
            const monthIndex = Number(parts[1]) - 1;
            const day = Number(parts[2]);
            if (Number.isNaN(monthIndex) || monthIndex < 0 || monthIndex > 11) {
              return value || '-';
            }
            if (Number.isNaN(day)) return value || '-';
            return `${day} ${months[monthIndex]} ${year}`;
          };
          const formatTanggalRentang = (mulai, selesai) => {
            const mulaiFmt = formatTanggal(mulai);
            const selesaiFmt = formatTanggal(selesai);
            if (!mulai || !selesai || mulai === '-' || selesai === '-') return mulaiFmt;
            if (mulai === selesai) return mulaiFmt;
            return `${mulaiFmt} - ${selesaiFmt}`;
          };
          const tanggal = formatTanggalRentang(tanggalMulaiRaw, tanggalSelesaiRaw);
          const logoUrl = `${window.location.origin}/images/Logo2.png`;
          const orderParams = (parseList(data.bapParameterOrder || '[]') || [])
            .map((item) => ({ ...item, method: 'indirect' }));
          const overallTestParams = (parseList(data.bapParameterPengujian || '[]') || [])
            .map((param) => ({
              ...param,
              method: param?.is_direct ? 'direct' : 'indirect',
            }));
          const changes = computeChanges(orderParams, overallTestParams);
          const lokasiRows = parseList(data.bapLokasiRows || '[]');
          const changedKeys = new Set(changes.map((item) => item.key));
          const locationMap = collectChangedLocations(lokasiRows, changedKeys);

          let counter = 1;
          const rowsHtml = changes.length
            ? changes.map((item) => {
              const lokasiList = Array.from(locationMap.get(item.key) || []).filter(Boolean);
              const lokasi = lokasiList.length ? lokasiList.join(', ') : (data.bapLokasi || '-');
              return `
                <tr>
                  <td class="col-no">${counter++}</td>
                  <td>${getJenisPengukuran(item.name, item.kategori || '')}</td>
                  <td>${lokasi}</td>
                  <td>${item.name || '-'}</td>
                  <td>${item.tambah ? item.tambah : ''}</td>
                  <td>${item.kurang ? item.kurang : ''}</td>
                  <td>${typeof item.total === 'number' ? item.total : '-'}</td>
                </tr>
              `;
            }).join('')
            : `
              <tr>
                <td class="col-no">1</td>
                <td>-</td>
                <td>-</td>
                <td colspan="4" style="text-align:center;">Tidak ada penambahan/pengurangan</td>
              </tr>
            `;

          const w = window.open('', '_blank');
          if (!w) return;
          w.document.write(`
            <html>
              <head>
                <title>Penambahan/Pengurangan Pengujian</title>
                <style>
                  @page { margin: 18px 2cm 50px 2cm; }
                  html, body { height: 100%; }
                  body { font-family: "Times New Roman", serif; padding: 18px 2cm; color: #111; }
                  .page { min-height: 100%; position: relative; }
                  .page-body { padding-bottom: 50px; }
                  .page-footer { position: fixed; left: 2cm; right: 2cm; bottom: 18px; }
                  @media print {
                    html, body { height: auto; }
                    body { padding: 0; }
                    .page { min-height: 0; page-break-after: avoid; }
                    .page-body { padding-bottom: 44px; }
                  }
                  .doc-header { display: grid; grid-template-columns: 110px 1fr 120px; border: 1px solid #000; }
                  .doc-header > div { border-right: 1px solid #000; }
                  .doc-header > div:last-child { border-right: none; }
                  .logo-cell { display: flex; align-items: center; justify-content: center; padding: 10px; }
                  .logo-cell img { width: 64px; height: 64px; object-fit: contain; }
                  .head-text { text-align: center; padding: 10px 8px 8px; font-size: 12px; font-weight: bold; line-height: 1.25; }
                  .meta-cell { display: grid; grid-template-rows: 1fr 1fr; font-size: 12px; }
                  .meta-cell div { border-bottom: 1px solid #000; padding: 6px 8px; display: flex; align-items: center; justify-content: center; text-align: center; }
                  .meta-cell div:last-child { border-bottom: none; }
                  .title { text-align: center; font-weight: bold; font-size: 13px; margin: 10px 0 10px; letter-spacing: 0.6px; text-decoration: underline; }
                  .info-list { margin-top: 6px; font-size: 12px; }
                  .info-row { display: flex; gap: 8px; margin: 6px 0; }
                  .info-row .num { width: 22px; text-align: right; }
                  .info-row .label { width: 180px; }
                  .info-row .colon { width: 10px; }
                  .info-row .value { flex: 1; }
                  .paragraph { font-size: 12px; line-height: 1.6; margin: 10px 0; text-align: justify; }
                  table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
                  th, td { border: 1px solid #000; padding: 4px 5px; height: 20px; }
                  th { text-align: center; font-weight: bold; }
                  .col-no { width: 6%; text-align: center; }
                  .col-jenis { width: 22%; }
                  .col-lokasi { width: 14%; }
                  .col-parameter { width: 14%; }
                  .col-tambah { width: 16%; }
                  .col-kurang { width: 16%; }
                  .col-jumlah { width: 12%; }
                  .signatures { display: flex; justify-content: space-between; margin-top: 16px; font-size: 12px; }
                  .sign-col { width: 45%; text-align: center; }
                  .sign-space { height: 56px; }
                  .signature-img { display: block; margin: 6px auto 2px; max-height: 56px; max-width: 200px; object-fit: contain; }
                  .sign-name { margin-top: 4px; font-weight: bold; }
                  .footer-line { border-top: 1px solid #000; margin-top: 8px; }
                  .footer-meta { display: flex; justify-content: space-between; font-size: 11px; margin-top: 6px; }
                </style>
              </head>
              <body>
                <div class="page">
                  <div class="page-body">
                    <div class="doc-header">
                      <div class="logo-cell"><img src="${logoUrl}" alt="Logo" id="printLogo"></div>
                      <div class="head-text">
                        KEMENTERIAN KETENAGAKERJAAN RI<br>
                        DIREKTORAT JENDERAL<br>
                        PEMBINAAN PENGAWASAN KETENAGAKERJAAN<br>
                        DAN KESELAMATAN DAN KESEHATAN KERJA<br>
                        BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA<br>
                        SURABAYA
                      </div>
                      <div class="meta-cell"><div>Page : 1/1</div><div>Rev/Terb. : 1/1</div></div>
                    </div>

                    <div class="title">PENAMBAHAN / PENGURANGAN PEKERJAAN PENGUJIAN</div>

                    <div class="info-list">
                      <div class="info-row"><div class="num">1.</div><div class="label">Nama Perusahaan</div><div class="colon">:</div><div class="value">${perusahaan}</div></div>
                      <div class="info-row"><div class="num">2.</div><div class="label">Alamat Perusahaan</div><div class="colon">:</div><div class="value">${alamat}</div></div>
                      <div class="info-row"><div class="num">3.</div><div class="label">Tanggal Pengukuran</div><div class="colon">:</div><div class="value">${tanggal}</div></div>
                    </div>

                <div class="paragraph">
                  Bahwa dengan persetujuan manajer Mutu dan atau Manajer Teknis serta Bapak/Ibu
                  ${penanggungNama || penandatanganNama || '..........................'} sebagai penanggung jawab ${perusahaan}, Maka Petugas
                  Pengambil Sampel Balai Higiene dan Keselamatan Kerja Surabaya melakukan pengambilan
                  sampel yang merupakan penambahan / pengurangan dari pekerjaan yang telah disetujui
                  sebelumnya, yang meliputi :
                    </div>

                    <table>
                      <thead>
                        <tr>
                          <th class="col-no">No.</th>
                          <th class="col-jenis">Jenis Pengukuran</th>
                          <th class="col-lokasi">Lokasi</th>
                          <th class="col-parameter">Parameter</th>
                          <th class="col-tambah">Penambahan</th>
                          <th class="col-kurang">Pengurangan</th>
                          <th class="col-jumlah">Jumlah</th>
                        </tr>
                      </thead>
                      <tbody>
                        ${rowsHtml}
                      </tbody>
                    </table>

                    <div class="signatures">
                      <div class="sign-col">
                        <div>Mengetahui :</div>
                        <div>Pimpinan Perusahaan/</div>
                        <div>Yang Mewakili</div>
                        ${penanggungTtd ? `<img src="${penanggungTtd}" alt="Tanda tangan Penanggung Jawab" class="signature-img">` : '<div class="sign-space"></div>'}
                        <div class="sign-name">(${penanggungNama || '.................................'})</div>
                      </div>
                      <div class="sign-col">
                      <div>Surabaya, ${tanggal}</div>
                      <div>Petugas Pengambil Sampel</div>
                      <div>Ketua Tim</div>
                      ${ketuaTimTtd ? `<img src="${ketuaTimTtd}" alt="Tanda tangan Ketua Tim" class="signature-img">` : '<div class="sign-space"></div>'}
                      <div class="sign-name">(${ketuaTimNama})</div>
                    </div>
                    </div>
                  </div>

                  <div class="page-footer">
                    <div class="footer-meta"><span>Tgl. terbit: 24 Desember 2024</span><span>No. : F/7.3.14/BK3-SBY</span></div>
                    <div class="footer-line"></div>
                  </div>
                </div>
                <script>
                  const logo = document.getElementById('printLogo');
                  const doPrint = () => setTimeout(() => window.print(), 250);
                  if (logo) {
                    logo.addEventListener('load', doPrint, { once: true });
                    logo.addEventListener('error', doPrint, { once: true });
                  } else {
                    doPrint();
                  }
                <\/script>
              </body>
            </html>
          `);
          w.document.close();
        };
        modalEl.querySelectorAll('[data-bap-penambahan]').forEach((btn) => {
          btn.onclick = (e) => {
            e.preventDefault();
            openPenambahan();
          };
        });

        if (data.bapCanApprove && typeof grecaptcha !== 'undefined') {
          grecaptcha.reset();
        }
      });

      modalEl.addEventListener('hidden.bs.modal', () => {
        if (cancelledAtTimer) {
          window.clearInterval(cancelledAtTimer);
          cancelledAtTimer = null;
        }
      });

      document.addEventListener('click', (event) => {
        const downloadLink = event.target.closest('a[data-download-once]');
        if (!downloadLink) return;

        if (downloadLink.classList.contains('is-loading')) {
          event.preventDefault();
          event.stopPropagation();
          return;
        }

        const href = downloadLink.getAttribute('href') || '';
        if (!href || href === '#') {
          return;
        }

        downloadLink.classList.add('is-loading', 'disabled');
        downloadLink.setAttribute('aria-disabled', 'true');
        if (!downloadLink.dataset.originalHtml) {
          downloadLink.dataset.originalHtml = downloadLink.innerHTML;
        }

        const icon = downloadLink.querySelector('.download-icon');
        if (icon) {
          icon.innerHTML = '<span class="download-spinner" aria-hidden="true"></span>';
        } else {
          const label = downloadLink.textContent.trim() || 'Mengunduh';
          downloadLink.innerHTML = `<span class="download-label">${label}</span><span class="download-spinner ms-2" aria-hidden="true"></span>`;
        }

        const resetDownloadLink = () => {
          downloadLink.classList.remove('is-loading', 'disabled');
          downloadLink.removeAttribute('aria-disabled');
          if (downloadLink.dataset.originalHtml) {
            downloadLink.innerHTML = downloadLink.dataset.originalHtml;
            delete downloadLink.dataset.originalHtml;
          }
        };

        window.setTimeout(resetDownloadLink, 5000);
        window.addEventListener('focus', resetDownloadLink, { once: true });
        window.addEventListener('pageshow', resetDownloadLink, { once: true });
      }, true);

      document.addEventListener('click', async (event) => {
        const lhuBtn = event.target.closest('.lhu-view-btn');
        if (!lhuBtn) return;
        event.preventDefault();

        const url = lhuBtn.getAttribute('data-lhu-url') || lhuBtn.getAttribute('href') || '';
        const needsUlasan = lhuBtn.getAttribute('data-lhu-needs-ulasan') === '1';
        const submitUrl = lhuBtn.getAttribute('data-lhu-ulasan-submit-url') || '';
        const kode = lhuBtn.getAttribute('data-kode') || '-';

        await openLhuWithUlasanCheck({ url, needsUlasan, submitUrl, kode });
      });

      ulasanSubmitBtn?.addEventListener('click', async () => {
        if (!ulasanState.submitUrl) {
          await notify('error', 'URL kirim ulasan tidak tersedia.');
          return;
        }

        const answerInputs = Array.from(document.querySelectorAll('[data-ulasan-answer]'));
        const answers = {};
        let invalid = false;

        answerInputs.forEach((input) => {
          const questionId = input.getAttribute('data-question-id');
          const type = input.getAttribute('data-question-type');
          const value = String(input.value || '').trim();
          answers[questionId] = value;

          const isEmpty = value === '';
          input.classList.toggle('is-invalid', isEmpty);
          if (isEmpty) {
            invalid = true;
            return;
          }
          if (type === 'rating') {
            const rating = Number(value);
            const badRating = Number.isNaN(rating) || rating < 1 || rating > 4;
            input.classList.toggle('is-invalid', badRating);
            if (badRating) invalid = true;
          }
        });

        if (invalid) {
          await notify('warning', 'Semua pertanyaan ulasan wajib diisi.');
          return;
        }

        ulasanSubmitBtn.disabled = true;
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          const response = await fetch(ulasanState.submitUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
            },
            body: JSON.stringify({ answers }),
          });
          const payload = await response.json().catch(() => ({}));
          if (!response.ok) {
            throw new Error(payload.message || 'Gagal mengirim ulasan.');
          }

          hideUlasanModal();
          await notify('success', payload.message || 'Ulasan berhasil dikirim.');
          openLhu(ulasanState.lhuUrl);
          window.location.reload();
        } catch (error) {
          await notify('error', error.message || 'Gagal mengirim ulasan.');
          ulasanSubmitBtn.disabled = false;
        }
      });

      document.addEventListener('click', async (event) => {
        const orderReviewButton = event.target.closest('.order-review-approve-btn');
        if (orderReviewButton) {
          event.preventDefault();
          const url = orderReviewButton.getAttribute('data-approve-url') || '';
          const kode = orderReviewButton.getAttribute('data-kode') || '-';
          if (!url) return;

          const confirmation = window.Swal
            ? await window.Swal.fire({
                icon: 'question',
                title: 'Setujui Perbaikan Pesanan?',
                text: `Pastikan parameter dan jumlah pesanan ${kode} sudah sesuai. Setelah disetujui, pesanan akan diteruskan ke disposisi.`,
                showCancelButton: true,
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Periksa Lagi',
              })
            : { isConfirmed: window.confirm(`Setujui perbaikan pesanan ${kode}?`) };

          if (!confirmation?.isConfirmed) return;

          orderReviewButton.disabled = true;
          try {
            const response = await fetch(url, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
              },
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
              const validationMessage = Object.values(payload.errors || {}).flat()[0];
              throw new Error(validationMessage || payload.message || 'Gagal menyetujui perbaikan pesanan.');
            }

            if (window.Swal) {
              await window.Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: payload.message || 'Perbaikan pesanan berhasil disetujui.',
              });
            }
            window.location.reload();
          } catch (error) {
            await notify('error', error.message || 'Gagal menyetujui perbaikan pesanan.');
            orderReviewButton.disabled = false;
          }
          return;
        }

        const reorderBtn = event.target.closest('.reorder-btn');
        if (!reorderBtn) return;
        event.preventDefault();

        const url = reorderBtn.getAttribute('data-reorder-url') || '';
        if (!url) return;

        const proceed = window.Swal
          ? await window.Swal.fire({
              icon: 'question',
              title: 'Order Kembali?',
              text: 'Keranjang akan dikosongkan lalu diisi parameter setelah BAP ACC.',
              showCancelButton: true,
              confirmButtonText: 'Ya, lanjut',
              cancelButtonText: 'Batal',
            })
          : { isConfirmed: confirm('Keranjang akan dikosongkan lalu diisi parameter setelah BAP ACC. Lanjutkan?') };
        if (!proceed?.isConfirmed) return;

        reorderBtn.disabled = true;
        try {
          const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';
          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
            },
          });
          const payload = await response.json().catch(() => ({}));
          if (!response.ok) {
            throw new Error(payload.message || 'Gagal melakukan order kembali.');
          }

          if (window.Swal) {
            await window.Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: payload.message || 'Keranjang berhasil diperbarui.',
              confirmButtonText: 'Ke Keranjang',
            });
          }
          window.location.href = payload.redirect_url || '/keranjang';
        } catch (error) {
          await notify('error', error.message || 'Gagal melakukan order kembali.');
          reorderBtn.disabled = false;
        }
      });
    });

    // Aksi LHU pemohon
    document.addEventListener('click', async (e) => {
      const approveBtn = e.target.closest('.lhu-approve-btn');
      if (approveBtn) {
        const url = approveBtn.getAttribute('data-approve-url') || '';
        if (!url) return;
        const proceed = window.Swal
          ? await window.Swal.fire({
              icon: 'question',
              title: 'Setujui LHU?',
              text: 'LHU akan ditandai disetujui.',
              showCancelButton: true,
              confirmButtonText: 'Setujui',
              cancelButtonText: 'Batal',
            }).then((r) => r.isConfirmed)
          : confirm('Setujui LHU?');
        if (!proceed) return;

        approveBtn.disabled = true;
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
            },
          });
          const payload = await response.json().catch(() => ({}));
          if (!response.ok) throw new Error(payload.message || 'Gagal menyetujui LHU.');
          if (window.Swal) {
            await window.Swal.fire({ icon: 'success', title: 'Berhasil', text: payload.message || 'LHU berhasil disetujui.' });
          }
          window.location.reload();
        } catch (error) {
          if (window.Swal) {
            await window.Swal.fire({ icon: 'error', title: 'Gagal', text: error.message || 'Gagal menyetujui LHU.' });
          } else {
            alert(error.message || 'Gagal menyetujui LHU.');
          }
          approveBtn.disabled = false;
        }
        return;
      }

      const reviseBtn = e.target.closest('.lhu-revise-btn');
      if (reviseBtn) {
        const url = reviseBtn.getAttribute('data-revise-url') || '';
        const kode = reviseBtn.getAttribute('data-kode') || '-';
        if (!url) return;

        let note = '';
        if (window.Swal) {
          const result = await window.Swal.fire({
            title: 'Ajukan Revisi LHU',
            text: `Permohonan ${kode}`,
            input: 'textarea',
            inputLabel: 'Catatan revisi',
            inputPlaceholder: 'Tulis catatan revisi LHU...',
            inputAttributes: { 'aria-label': 'Catatan revisi' },
            showCancelButton: true,
            confirmButtonText: 'Kirim Revisi',
            cancelButtonText: 'Batal',
            inputValidator: (value) => !value ? 'Catatan revisi wajib diisi.' : undefined,
          });
          if (!result.isConfirmed) return;
          note = (result.value || '').trim();
        } else {
          note = (prompt('Tulis catatan revisi LHU:') || '').trim();
          if (!note) return;
        }

        reviseBtn.disabled = true;
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
          const response = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf,
              'Accept': 'application/json',
            },
            body: JSON.stringify({ note }),
          });
          const payload = await response.json().catch(() => ({}));
          if (!response.ok) throw new Error(payload.message || 'Gagal mengajukan revisi LHU.');
          if (window.Swal) {
            await window.Swal.fire({ icon: 'success', title: 'Berhasil', text: payload.message || 'Revisi LHU berhasil dikirim.' });
          }
          window.location.reload();
        } catch (error) {
          if (window.Swal) {
            await window.Swal.fire({ icon: 'error', title: 'Gagal', text: error.message || 'Gagal mengajukan revisi LHU.' });
          } else {
            alert(error.message || 'Gagal mengajukan revisi LHU.');
          }
          reviseBtn.disabled = false;
        }
      }
    });

    // Batalkan pesanan (pilih alasan)
    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.cancel-order');
      if (!btn) return;
      const stage = (btn.getAttribute('data-stage') || '').toLowerCase().trim();
      const kode = btn.getAttribute('data-kode') || 'pesanan';
      const permohonanId = btn.getAttribute('data-id') || '';
      const allowed = ['verifikasi pesanan', 'disposisi', 'kaji ulang', 'penawaran'];
      if (!allowed.includes(stage)) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'warning', title: 'Tidak bisa dibatalkan', text: 'Pesanan tidak bisa dibatalkan pada tahap ini.' });
        } else {
          alert('Pesanan tidak bisa dibatalkan pada tahap ini.');
        }
        return;
      }
      const cancelModalEl = document.getElementById('cancelModal');
      if (!cancelModalEl) return;
      cancelModalEl.setAttribute('data-permohonan-id', permohonanId);
      const kodeSpan = cancelModalEl.querySelector('#cancelKode');
      if (kodeSpan) kodeSpan.textContent = kode;
      cancelModalEl.querySelectorAll('[data-cancel-reason]').forEach(r => { r.checked = false; });
      const note = cancelModalEl.querySelector('[data-cancel-note]');
      if (note) note.value = '';
      const modal = window.bootstrap ? new window.bootstrap.Modal(cancelModalEl) : null;
      modal?.show();
    });

    document.addEventListener('click', (e) => {
      const submitBtn = e.target.closest('[data-cancel-submit]');
      if (!submitBtn) return;
      const cancelModalEl = document.getElementById('cancelModal');
      if (!cancelModalEl) return;
      const kode = cancelModalEl.querySelector('#cancelKode')?.textContent || 'pesanan';
      const permohonanId = cancelModalEl.getAttribute('data-permohonan-id');
      const reasonInput = cancelModalEl.querySelector('[data-cancel-reason]:checked');
      const note = cancelModalEl.querySelector('[data-cancel-note]')?.value || '';
      const reason = reasonInput ? reasonInput.value : '';
      if (!reason) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'warning', title: 'Alasan wajib', text: 'Pilih alasan pembatalan terlebih dahulu.' });
        } else {
          alert('Pilih alasan pembatalan terlebih dahulu.');
        }
        return;
      }
      if (!permohonanId) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Gagal', text: 'Permohonan tidak ditemukan.' });
        } else {
          alert('Permohonan tidak ditemukan.');
        }
        return;
      }
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      fetch(`/riwayat_pelayanan/${permohonanId}/cancel`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify({ reason, note }),
      }).then(async (res) => {
        if (!res.ok) {
          const data = await res.json().catch(() => ({}));
          throw new Error(data.message || 'Gagal membatalkan pesanan.');
        }
        const modalInstance = window.bootstrap ? window.bootstrap.Modal.getInstance(cancelModalEl) : null;
        modalInstance?.hide();
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'success', title: 'Berhasil', text: `Pesanan ${kode} berhasil dibatalkan.` })
            .then(() => window.location.reload());
        } else {
          alert(`Pesanan ${kode} berhasil dibatalkan.`);
          window.location.reload();
        }
      }).catch((err) => {
        if (typeof Swal !== 'undefined') {
          Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Gagal membatalkan pesanan.' });
        } else {
          alert(err.message || 'Gagal membatalkan pesanan.');
        }
      });
    });
  </script>
  {!! NoCaptcha::renderJs() !!}
@endsection
