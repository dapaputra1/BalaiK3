@extends('layouts.app_admin')

@section('content_admin')
<style>
    .service-parameter-page .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .service-parameter-page .header-actions {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .service-parameter-page .btn-header-action {
        min-width: 190px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        font-weight: 500;
        font-size: .95rem;
        line-height: 1.2;
        border-radius: .6rem;
        padding: .58rem .95rem;
        white-space: nowrap;
    }

    .service-parameter-page .btn-header-action i {
        font-size: 1rem;
    }

    .service-parameter-page .card {
        border-radius: .75rem;
        border-color: #d8dee8;
    }

    .service-parameter-page .card .card-body {
        padding: 1rem;
    }

    .service-parameter-page .package-card {
        border-radius: 1rem;
        border-color: #d8e2f0;
        overflow: hidden;
    }

    .service-parameter-page .package-card .card-body {
        padding: 1.1rem 1.1rem .95rem;
    }

    .service-parameter-page .table {
        margin-bottom: 0;
        font-size: .95rem;
    }

    .service-parameter-page .table thead th {
        white-space: nowrap;
        font-weight: 600;
        vertical-align: middle;
        font-size: .9rem;
        padding: .65rem .75rem;
    }

    .service-parameter-page .table td {
        vertical-align: middle;
        padding: .55rem .7rem;
    }

    .service-parameter-page .table-actions {
        width: 130px;
    }

    .service-parameter-page .table-action-btn {
        min-width: 36px;
        height: 34px;
        padding: .25rem .55rem !important;
        font-size: .9rem;
        border-radius: .45rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .service-parameter-page .badge {
        font-size: .8rem;
        font-weight: 600;
        padding: .35em .55em;
    }

    .service-parameter-page .dataTables_wrapper .dataTables_filter input,
    .service-parameter-page .dataTables_wrapper .dataTables_length select {
        min-height: 38px;
        border-radius: .5rem;
    }

    .service-parameter-page .dataTables_wrapper .row:first-child {
        margin-bottom: .65rem;
    }

    .service-parameter-page .dataTables_wrapper .row:last-child {
        margin-top: .65rem;
    }

    .service-parameter-page .dataTables_wrapper .dataTables_info {
        padding-top: .5rem;
    }

    .service-parameter-page .package-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
    }

    .service-parameter-page .package-table thead th {
        background: #edf3ff;
        color: #445d84;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-size: .72rem;
        font-weight: 700;
        border-bottom: 1px solid #d8e3f5;
        border-top: 1px solid #d8e3f5;
        padding: .95rem .85rem;
    }

    .service-parameter-page .package-table tbody td {
        padding: 1rem .85rem;
        border-bottom: 1px solid #e5ecf7;
        background: #fff;
        vertical-align: middle;
        font-size: .92rem;
        color: #213d5d;
    }

    .service-parameter-page .package-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .service-parameter-page .package-name-cell {
        min-width: 220px;
        font-weight: 600;
        color: #0f2f53;
        line-height: 1.35;
    }

    .service-parameter-page .package-code-cell {
        color: #7188a7;
        letter-spacing: .02em;
        font-size: .86rem;
        white-space: nowrap;
    }

    .service-parameter-page .package-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: .28rem .78rem;
        border-radius: 999px;
        background: #dfe8ff;
        color: #163f95;
        font-size: .78rem;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }

    .service-parameter-page .package-price-cell {
        min-width: 120px;
        color: #0b37a0;
        font-weight: 700;
        line-height: 1.25;
    }

    .service-parameter-page .package-price-cell .currency {
        display: block;
        font-size: .9rem;
    }

    .service-parameter-page .package-detail-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
        min-width: 160px;
    }

    .service-parameter-page .package-detail-chip {
        display: inline-flex;
        align-items: center;
        padding: .18rem .42rem;
        border-radius: .38rem;
        background: #f4f7fc;
        border: 1px solid #e3eaf5;
        color: #415b7d;
        font-size: .7rem;
        line-height: 1.1;
    }

    .service-parameter-page .package-status-pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .33rem .82rem;
        border-radius: 999px;
        background: #8cf0e0;
        color: #0f6459;
        font-size: .78rem;
        font-weight: 700;
        line-height: 1;
    }

    .service-parameter-page .package-status-pill::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
        opacity: .85;
    }

    .service-parameter-page .package-status-pill.is-inactive {
        background: #e6ebf2;
        color: #657487;
    }

    .service-parameter-page .package-action-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
    }

    .service-parameter-page .package-action-link {
        border: 0;
        background: transparent;
        color: #0b52d4;
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
        font-size: .95rem;
    }

    .service-parameter-page .package-action-link.delete {
        color: #ef4444;
    }

    .service-parameter-page .package-action-link:hover {
        background: #eef4ff;
    }

    .service-parameter-page .package-action-link.delete:hover {
        background: #fff0f0;
    }

    .service-parameter-page .package-card .dataTables_wrapper .dataTables_length label,
    .service-parameter-page .package-card .dataTables_wrapper .dataTables_filter label,
    .service-parameter-page .package-card .dataTables_wrapper .dt-length label,
    .service-parameter-page .package-card .dataTables_wrapper .dt-search label {
        font-size: .86rem;
        color: #566b88;
        display: flex;
        align-items: center;
        gap: .6rem;
        margin-bottom: 0;
    }

    .service-parameter-page .package-card .dataTables_wrapper .dataTables_length select,
    .service-parameter-page .package-card .dataTables_wrapper .dataTables_filter input,
    .service-parameter-page .package-card .dataTables_wrapper .dt-length select,
    .service-parameter-page .package-card .dataTables_wrapper .dt-search input {
        min-height: 32px;
        border-radius: .65rem;
        border-color: #d7e1ef;
        font-size: .86rem;
        box-shadow: none;
    }

    .service-parameter-page .package-card .dataTables_wrapper .dataTables_filter input,
    .service-parameter-page .package-card .dataTables_wrapper .dt-search input {
        min-width: 260px;
        padding-left: .85rem;
    }

    .service-parameter-page .package-card .dataTables_wrapper .row:first-child,
    .service-parameter-page .package-card .dataTables_wrapper .dt-layout-row:first-child {
        margin-bottom: .9rem;
        align-items: center;
    }

    .service-parameter-page .package-card .dataTables_wrapper .row:last-child,
    .service-parameter-page .package-card .dataTables_wrapper .dt-layout-row:last-child {
        margin-top: .85rem;
        align-items: center;
    }

    .service-parameter-page .package-card .dataTables_wrapper .dataTables_info,
    .service-parameter-page .package-card .dataTables_wrapper .dt-info {
        color: #667a95;
        font-size: .82rem;
        padding-top: 0;
    }

    .service-parameter-page .package-card .dataTables_wrapper .dataTables_paginate,
    .service-parameter-page .package-card .dataTables_wrapper .dt-paging {
        display: flex;
        justify-content: flex-end;
        width: 100%;
    }

    .service-parameter-page .package-card .dataTables_wrapper .pagination {
        gap: .35rem;
        margin-bottom: 0;
    }

    .service-parameter-page .package-card .dataTables_wrapper .page-link {
        min-width: 34px;
        height: 34px;
        border-radius: .6rem !important;
        padding: .35rem .72rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-color: #d7e1ef;
        color: #48617f;
        font-size: .84rem;
        line-height: 1;
    }

    .service-parameter-page .package-card .dataTables_wrapper .page-item.active .page-link {
        background: #0d46b8;
        border-color: #0d46b8;
    }

    .service-parameter-page .custom-table-shell {
        border: 1px solid #dce5f1;
        border-radius: 1rem;
        background: #fff;
        overflow: hidden;
    }

    .service-parameter-page .custom-table-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1rem .85rem;
        flex-wrap: wrap;
        border-bottom: 1px solid #e6edf7;
    }

    .service-parameter-page .custom-table-toolbar-left,
    .service-parameter-page .custom-table-toolbar-right {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .service-parameter-page .custom-table-label {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        font-size: .82rem;
        color: #566b88;
        white-space: nowrap;
    }

    .service-parameter-page .custom-table-select,
    .service-parameter-page .custom-table-input {
        min-height: 34px;
        border: 1px solid #d5dfed;
        border-radius: .7rem;
        background: #fff;
        color: #213d5d;
        font-size: .82rem;
        padding: .4rem .75rem;
        outline: none;
        box-shadow: none;
    }

    .service-parameter-page .custom-table-select {
        min-width: 74px;
        padding-right: 2rem;
    }

    .service-parameter-page .custom-table-input {
        min-width: 220px;
    }

    .service-parameter-page .custom-table-input.is-search {
        min-width: 250px;
    }

    .service-parameter-page .custom-table-scroll {
        overflow-x: auto;
    }

    .service-parameter-page .custom-admin-table {
        width: 100%;
        min-width: 760px;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0;
        background: #fff;
    }

    .service-parameter-page .custom-admin-table.packages-table {
        min-width: 1040px;
    }

    .service-parameter-page .custom-admin-table thead th {
        background: #edf3ff;
        color: #445d84;
        text-transform: uppercase;
        letter-spacing: .06em;
        font-size: .68rem;
        font-weight: 700;
        padding: .82rem .78rem;
        border-bottom: 1px solid #d8e3f5;
        white-space: nowrap;
        vertical-align: middle;
    }

    .service-parameter-page .custom-admin-table tbody td {
        padding: .82rem .78rem;
        border-bottom: 1px solid #e6edf7;
        vertical-align: middle;
        font-size: .78rem;
        color: #213d5d;
        background: #fff;
    }

    .service-parameter-page .custom-admin-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .service-parameter-page .custom-admin-table tbody tr.is-hidden {
        display: none;
    }

    .service-parameter-page .custom-table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: .9rem 1rem 1rem;
        flex-wrap: wrap;
        border-top: 1px solid #e6edf7;
    }

    .service-parameter-page .custom-table-info {
        font-size: .8rem;
        color: #667a95;
    }

    .service-parameter-page .custom-table-pagination {
        display: flex;
        align-items: center;
        gap: .35rem;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin-left: auto;
    }

    .service-parameter-page .custom-page-btn {
        min-width: 34px;
        height: 34px;
        border: 1px solid #d7e1ef;
        background: #fff;
        color: #48617f;
        border-radius: .65rem;
        font-size: .8rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .35rem .7rem;
    }

    .service-parameter-page .custom-page-btn.is-active {
        background: #0d46b8;
        border-color: #0d46b8;
        color: #fff;
    }

    .service-parameter-page .custom-page-btn:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .service-parameter-page .custom-status-pill {
        display: inline-flex;
        align-items: center;
        gap: .34rem;
        padding: .32rem .68rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        line-height: 1;
    }

    .service-parameter-page .custom-status-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        opacity: .85;
    }

    .service-parameter-page .custom-status-pill.is-active {
        background: #dff7ea;
        color: #1f8f58;
    }

    .service-parameter-page .custom-status-pill.is-inactive {
        background: #e7edf3;
        color: #67778b;
    }

    .service-parameter-page .custom-action-group {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        justify-content: center;
    }

    .service-parameter-page .custom-action-link {
        border: 0;
        background: transparent;
        color: #0b52d4;
        width: 26px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
        font-size: .82rem;
    }

    .service-parameter-page .custom-action-link.delete {
        color: #ef4444;
    }

    .service-parameter-page .custom-action-link:hover {
        background: #eef4ff;
    }

    .service-parameter-page .custom-action-link.delete:hover {
        background: #fff0f0;
    }

    .service-parameter-page .custom-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        padding: .24rem .66rem;
        border-radius: 999px;
        background: #dfe8ff;
        color: #163f95;
        font-size: .7rem;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
    }

    .service-parameter-page .custom-detail-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }

    .service-parameter-page .custom-detail-chip {
        display: inline-flex;
        align-items: center;
        padding: .18rem .42rem;
        border-radius: .38rem;
        background: #f4f7fc;
        border: 1px solid #e3eaf5;
        color: #415b7d;
        font-size: .64rem;
        line-height: 1.1;
    }

    .service-parameter-page .custom-price {
        color: #0b37a0;
        font-weight: 700;
        line-height: 1.25;
        font-size: .78rem;
    }

    .service-parameter-page .custom-price .currency {
        display: block;
        font-size: .74rem;
    }

    .service-parameter-page .modal {
        z-index: 2000;
    }

    .service-parameter-page .modal-backdrop {
        z-index: 1990;
    }

    .service-parameter-page .modal-dialog {
        margin: 1.5rem auto;
    }

    .service-parameter-page .parameter-name-cell {
        font-weight: 700;
        color: #0f2f53;
        font-size: .8rem;
    }

    .service-parameter-page .parameter-category-filter {
        width: 190px;
        min-width: 190px;
        max-width: 190px;
        min-height: 38px;
        font-size: .9rem;
    }

    .service-parameter-page #parametersTable_wrapper .dataTables_filter,
    .service-parameter-page #parametersTable_wrapper .dt-search {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: .5rem;
        flex-wrap: nowrap;
        width: 100%;
    }

    .service-parameter-page #parametersTable_wrapper .dataTables_filter label,
    .service-parameter-page #parametersTable_wrapper .dt-search label {
        margin-bottom: 0;
        white-space: nowrap;
        flex: 0 0 auto;
        display: inline-block;
    }

    .service-parameter-page #parametersTable_wrapper .dataTables_filter input,
    .service-parameter-page #parametersTable_wrapper .dt-search input {
        margin-left: 0 !important;
        width: 220px !important;
        min-width: 220px;
        flex: 0 0 220px;
    }

    .service-parameter-page #parametersTable_wrapper .parameter-category-filter {
        order: 0;
    }

    .service-parameter-page #parametersTable_wrapper .dataTables_filter label,
    .service-parameter-page #parametersTable_wrapper .dt-search label {
        order: 1;
    }

    .service-parameter-page #parametersTable_wrapper .dataTables_filter input,
    .service-parameter-page #parametersTable_wrapper .dt-search input {
        order: 2;
    }

    @media (max-width: 767.98px) {
        .service-parameter-page .btn-header-action {
            min-width: auto;
            width: 100%;
        }

        .service-parameter-page .parameter-category-filter {
            width: 100%;
            max-width: 100%;
            min-width: 100%;
        }

        .service-parameter-page .package-card .dataTables_wrapper .dataTables_filter input,
        .service-parameter-page .package-card .dataTables_wrapper .dt-search input {
            min-width: 100%;
        }

        .service-parameter-page .custom-table-input,
        .service-parameter-page .custom-table-input.is-search,
        .service-parameter-page .custom-table-select {
            min-width: 100%;
            width: 100%;
        }

        .service-parameter-page .custom-table-footer {
            align-items: flex-start;
        }

        .service-parameter-page .custom-table-pagination {
            margin-left: 0;
            justify-content: flex-start;
        }

        .service-parameter-page #parametersTable_wrapper .dataTables_filter,
        .service-parameter-page #parametersTable_wrapper .dt-search {
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .service-parameter-page #parametersTable_wrapper .dataTables_filter input,
        .service-parameter-page #parametersTable_wrapper .dt-search input {
            width: 100% !important;
            min-width: 100%;
            flex: 1 1 100%;
        }
    }
</style>
<div class="service-parameter-page">
@php
    $sortedParameters = collect($parameters)->sortBy('id')->values();
@endphp
<div class="page-header">
    <div>
        <h5 class="mb-1">Kelola Kategori dan Parameter</h5>
        <small class="text-muted d-block">Tambah, ubah, dan hapus kategori serta parameter pelayanan</small>
    </div>
    <div class="header-actions">
        <button class="btn text-white btn-header-action" style="background-color:#15406A; border-color:#15406A;" data-bs-toggle="modal" data-bs-target="#categoryModal" data-mode="create">
            <i class="bi bi-folder-plus me-1"></i> Tambah Kategori
        </button>
        <button class="btn text-white btn-header-action" style="background-color:#0f6a5f; border-color:#0f6a5f;" data-bs-toggle="modal" data-bs-target="#parameterModal" data-mode="create">
            <i class="bi bi-plus-circle me-1"></i> Tambah Parameter
        </button>
        @if($emisiCategory)
            <button class="btn text-white btn-header-action" style="background-color:#7a4e00; border-color:#7a4e00;" data-bs-toggle="modal" data-bs-target="#packageModal" data-mode="create">
                <i class="bi bi-box-seam me-1"></i> Tambah Paket Emisi
            </button>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mb-3">
        {{ $errors->first() }}
    </div>
@endif

<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Kategori</h6>
                <div class="custom-table-shell" id="categoriesTableShell">
                    <div class="custom-table-toolbar">
                        <div class="custom-table-toolbar-left">
                            <label class="custom-table-label">
                                Show
                                <select class="custom-table-select" data-table-length>
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                entries
                            </label>
                        </div>
                        <div class="custom-table-toolbar-right">
                            <input type="search" class="custom-table-input is-search" data-table-search placeholder="Cari kategori...">
                        </div>
                    </div>
                    <div class="custom-table-scroll">
                        <table class="custom-admin-table" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th class="text-center">Singkatan</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $row)
                                    <tr>
                                        <td>{{ $row->name }}</td>
                                        <td class="text-center">{{ $row->short_code ?? '-' }}</td>
                                        <td class="text-center">
                                            @if($row->is_active)
                                                <span class="custom-status-pill is-active">Aktif</span>
                                            @else
                                                <span class="custom-status-pill is-inactive">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-action-group">
                                                <button class="custom-action-link"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#categoryModal"
                                                        data-mode="edit"
                                                        data-id="{{ $row->id }}"
                                                        data-name="{{ $row->name }}"
                                                        data-short-code="{{ $row->short_code ?? '' }}"
                                                        data-status="{{ $row->is_active ? 1 : 0 }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('superadmin.service-categories.destroy', $row) }}" method="POST" class="delete-form d-inline-flex">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="confirm" value="1">
                                                    <button type="submit" class="custom-action-link delete btn-delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada kategori</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="custom-table-footer">
                        <div class="custom-table-info" data-table-info></div>
                        <div class="custom-table-pagination" data-table-pagination></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Parameter</h6>
                <div class="custom-table-shell" id="parametersTableShell">
                    <div class="custom-table-toolbar">
                        <div class="custom-table-toolbar-left">
                            <label class="custom-table-label">
                                Show
                                <select class="custom-table-select" data-table-length>
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                entries
                            </label>
                        </div>
                        <div class="custom-table-toolbar-right">
                            <select class="custom-table-select" id="parameterCategoryFilter">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->name }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <input type="search" class="custom-table-input is-search" data-table-search placeholder="Cari parameter...">
                        </div>
                    </div>
                    <div class="custom-table-scroll">
                        <table class="custom-admin-table" id="parametersTable">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th>Nama</th>
                                    <th class="text-center">Singkatan</th>
                                    <th>Kategori</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sortedParameters as $row)
                                    <tr>
                                        <td class="text-center">{{ $row->id }}</td>
                                        <td class="parameter-name-cell">{{ $row->name }}</td>
                                        <td class="text-center">{{ $row->short_code ?? '-' }}</td>
                                        <td>{{ $row->category?->name ?? '-' }}</td>
                                        <td class="text-end">
                                            <div class="custom-price">
                                                <span class="currency">Rp</span>
                                                <span>{{ number_format($row->price, 2, ',', '.') }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($row->is_active)
                                                <span class="custom-status-pill is-active">Aktif</span>
                                            @else
                                                <span class="custom-status-pill is-inactive">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-action-group">
                                                <button class="custom-action-link"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#parameterModal"
                                                        data-mode="edit"
                                                        data-id="{{ $row->id }}"
                                                        data-name="{{ $row->name }}"
                                                        data-short-code="{{ $row->short_code ?? '' }}"
                                                        data-category-id="{{ $row->service_category_id }}"
                                                        data-price="{{ $row->price }}"
                                                        data-status="{{ $row->is_active ? 1 : 0 }}">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('superadmin.service-parameters.destroy', $row) }}" method="POST" class="delete-form d-inline-flex">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="confirm" value="1">
                                                    <button type="submit" class="custom-action-link delete btn-delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada parameter</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="custom-table-footer">
                        <div class="custom-table-info" data-table-info></div>
                        <div class="custom-table-pagination" data-table-pagination></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($emisiCategory)
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card shadow-sm package-card">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Paket Emisi</h6>
                <div class="custom-table-shell" id="packagesTableShell">
                    <div class="custom-table-toolbar">
                        <div class="custom-table-toolbar-left">
                            <label class="custom-table-label">
                                Show
                                <select class="custom-table-select" data-table-length>
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                entries
                            </label>
                        </div>
                        <div class="custom-table-toolbar-right">
                            <input type="search" class="custom-table-input is-search" data-table-search placeholder="Cari paket emisi...">
                        </div>
                    </div>
                    <div class="custom-table-scroll">
                        <table class="custom-admin-table packages-table package-table" id="packagesTable">
                            <thead>
                                <tr>
                                    <th>Nama Paket</th>
                                    <th class="text-center">Kode</th>
                                    <th class="text-center">Badge</th>
                                    <th class="text-end">Harga Paket</th>
                                    <th>Rincian</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($packages as $package)
                                    @php
                                        $itemsPayload = $package->items
                                            ->sortBy('sort_order')
                                            ->values()
                                            ->map(function ($item) {
                                                return [
                                                    'parameter_id' => $item->service_parameter_id,
                                                    'label' => $item->label,
                                                ];
                                            })
                                            ->all();
                                        $detailList = $package->items
                                            ->sortBy('sort_order')
                                            ->values()
                                            ->map(fn ($item) => $item->label ?: ($item->parameter?->name ?? '-'))
                                            ->filter()
                                            ->values();
                                    @endphp
                                    <tr>
                                        <td class="package-name-cell">{{ $package->name }}</td>
                                        <td class="package-code-cell">{{ $package->short_code }}</td>
                                        <td>
                                            <span class="package-pill">{{ $package->badge ?: '-' }}</span>
                                        </td>
                                        <td class="package-price-cell">
                                            <span class="currency">Rp</span>
                                            <span>{{ number_format($package->price, 2, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            <div class="package-detail-chips">
                                                @foreach($detailList as $detail)
                                                    <span class="package-detail-chip">{{ $detail }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($package->is_active)
                                                <span class="package-status-pill">Aktif</span>
                                            @else
                                                <span class="package-status-pill is-inactive">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="package-action-group">
                                                <button class="package-action-link"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#packageModal"
                                                        data-mode="edit"
                                                        data-id="{{ $package->id }}"
                                                        data-name="{{ $package->name }}"
                                                        data-short-code="{{ $package->short_code }}"
                                                        data-badge="{{ $package->badge ?? '' }}"
                                                        data-subtitle="{{ $package->subtitle ?? '' }}"
                                                        data-price="{{ $package->price }}"
                                                        data-unit="{{ $package->unit }}"
                                                        data-sort-order="{{ $package->sort_order }}"
                                                        data-status="{{ $package->is_active ? 1 : 0 }}"
                                                        data-items='@json($itemsPayload)'>
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <form action="{{ route('superadmin.service-packages.destroy', $package) }}" method="POST" class="delete-form d-inline-flex">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="confirm" value="1">
                                                    <button type="submit" class="package-action-link delete btn-delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Belum ada paket emisi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="custom-table-footer">
                        <div class="custom-table-info" data-table-info></div>
                        <div class="custom-table-pagination" data-table-pagination></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal Kategori -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="categoryForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="name" placeholder="Nama kategori" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Singkatan</label>
                        <input type="text" class="form-control" name="short_code" placeholder="Contoh: LK" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active" required>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
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

<!-- Modal Parameter -->
<div class="modal fade" id="parameterModal" tabindex="-1" aria-labelledby="parameterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="parameterModalLabel">Parameter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="parameterForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="service_category_id" required>
                            <option value="" disabled selected>Pilih kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="name" placeholder="Nama parameter" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Singkatan</label>
                        <input type="text" class="form-control" name="short_code" placeholder="Contoh: DPM10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="price" min="0" step="0.01" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active" required>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
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

@if($emisiCategory)
<div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="packageModalLabel">Paket Emisi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="packageForm">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="service_category_id" value="{{ $emisiCategory->id }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Paket</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kode Paket</label>
                            <input type="text" class="form-control" name="short_code" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Badge</label>
                            <input type="text" class="form-control" name="badge" placeholder="Genset / Boiler">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Subjudul</label>
                            <input type="text" class="form-control" name="subtitle">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Harga</label>
                            <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control" name="unit" value="per lokasi" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Urut</label>
                            <input type="number" class="form-control" name="sort_order" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="is_active" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Rincian Paket</strong>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addPackageItemRow">
                            <i class="bi bi-plus-lg"></i> Tambah Baris
                        </button>
                    </div>
                    <div id="packageItemsWrap" class="d-grid gap-2"></div>
                    <div class="small text-muted mt-2">Pilih parameter Emisi, lalu opsional isi label tampilan custom.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="packageItemTemplate">
    <div class="row g-2 align-items-center package-item-row">
        <div class="col-md-5">
            <select class="form-select" name="item_parameter_ids[]">
                <option value="">Pilih parameter</option>
                @foreach($emisiParameters as $parameter)
                    <option value="{{ $parameter->id }}">{{ $parameter->short_code }} - {{ $parameter->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" class="form-control" name="item_labels[]" placeholder="Label custom (opsional)">
        </div>
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-outline-danger btn-sm remove-package-item-row"><i class="bi bi-trash"></i></button>
        </div>
    </div>
</template>
@endif

@push('scripts')
<script>
    const categoryModal = document.getElementById('categoryModal');
    const categoryForm = document.getElementById('categoryForm');
    const categoryMethodInput = categoryForm.querySelector('input[name="_method"]');
    const categoryNameInput = categoryForm.querySelector('input[name="name"]');
    const categoryShortInput = categoryForm.querySelector('input[name="short_code"]');
    const categoryStatusSelect = categoryForm.querySelector('select[name="is_active"]');
    const categoryModalTitle = document.getElementById('categoryModalLabel');

    const parameterModal = document.getElementById('parameterModal');
    const parameterForm = document.getElementById('parameterForm');
    const parameterMethodInput = parameterForm.querySelector('input[name="_method"]');
    const parameterNameInput = parameterForm.querySelector('input[name="name"]');
    const parameterShortInput = parameterForm.querySelector('input[name="short_code"]');
    const parameterPriceInput = parameterForm.querySelector('input[name="price"]');
    const parameterCategorySelect = parameterForm.querySelector('select[name="service_category_id"]');
    const parameterStatusSelect = parameterForm.querySelector('select[name="is_active"]');
    const parameterModalTitle = document.getElementById('parameterModalLabel');
    const packageModal = document.getElementById('packageModal');
    const packageForm = document.getElementById('packageForm');
    const packageItemsWrap = document.getElementById('packageItemsWrap');
    const packageItemTemplate = document.getElementById('packageItemTemplate');
    const addPackageItemRowBtn = document.getElementById('addPackageItemRow');
    const packageModalTitle = document.getElementById('packageModalLabel');

    function initCustomTable(shellId, options = {}) {
        const shell = document.getElementById(shellId);
        if (!shell) return null;

        const table = shell.querySelector('table');
        const tbody = table?.querySelector('tbody');
        const infoEl = shell.querySelector('[data-table-info]');
        const paginationEl = shell.querySelector('[data-table-pagination]');
        const searchInput = shell.querySelector('[data-table-search]');
        const lengthSelect = shell.querySelector('[data-table-length]');
        const rows = Array.from(tbody?.querySelectorAll('tr') || []).filter((row) => row.children.length > 1);
        const emptyRow = Array.from(tbody?.querySelectorAll('tr') || []).find((row) => row.children.length === 1) || null;
        const noResultsRow = document.createElement('tr');
        const noResultsCell = document.createElement('td');
        noResultsCell.colSpan = table?.tHead?.rows?.[0]?.cells?.length || 1;
        noResultsCell.className = 'text-center text-muted';
        noResultsCell.textContent = 'Data tidak ditemukan';
        noResultsRow.appendChild(noResultsCell);

        const state = {
            page: 1,
            pageSize: Number(lengthSelect?.value || 10),
            search: '',
        };

        const normalize = (value) => String(value || '')
            .toLowerCase()
            .replace(/\s+/g, ' ')
            .trim();

        const getFilteredRows = () => {
            return rows.filter((row) => {
                const rowText = normalize(row.textContent);
                if (state.search && !rowText.includes(state.search)) {
                    return false;
                }

                if (typeof options.rowFilter === 'function' && !options.rowFilter(row)) {
                    return false;
                }

                return true;
            });
        };

        const renderPagination = (totalItems, totalPages) => {
            if (!paginationEl) return;
            paginationEl.innerHTML = '';
            if (totalPages <= 1) return;

            const makeButton = (label, page, { active = false, disabled = false } = {}) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'custom-page-btn' + (active ? ' is-active' : '');
                button.innerHTML = label;
                button.disabled = disabled;
                button.addEventListener('click', () => {
                    if (disabled || page === state.page) return;
                    state.page = page;
                    render();
                });
                return button;
            };

            const makeEllipsis = () => {
                const span = document.createElement('span');
                span.className = 'custom-page-btn';
                span.textContent = '...';
                span.setAttribute('aria-hidden', 'true');
                return span;
            };

            const pageNumbers = [];
            const addPage = (page) => {
                if (page >= 1 && page <= totalPages && !pageNumbers.includes(page)) {
                    pageNumbers.push(page);
                }
            };

            paginationEl.appendChild(makeButton('<i class="bi bi-chevron-left"></i>', Math.max(1, state.page - 1), { disabled: state.page === 1 }));

            if (totalPages <= 7) {
                for (let page = 1; page <= totalPages; page += 1) {
                    pageNumbers.push(page);
                }
            } else {
                addPage(1);
                addPage(state.page - 1);
                addPage(state.page);
                addPage(state.page + 1);
                addPage(totalPages);

                if (state.page <= 3) {
                    addPage(2);
                    addPage(3);
                }

                if (state.page >= totalPages - 2) {
                    addPage(totalPages - 1);
                    addPage(totalPages - 2);
                }
            }

            pageNumbers.sort((a, b) => a - b);

            pageNumbers.forEach((page, index) => {
                const previousPage = pageNumbers[index - 1];
                if (typeof previousPage === 'number' && page - previousPage > 1) {
                    paginationEl.appendChild(makeEllipsis());
                }
                paginationEl.appendChild(makeButton(String(page), page, { active: page === state.page }));
            });

            paginationEl.appendChild(makeButton('<i class="bi bi-chevron-right"></i>', Math.min(totalPages, state.page + 1), { disabled: state.page === totalPages }));
        };

        const render = () => {
            const filteredRows = getFilteredRows();
            const totalItems = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(totalItems / state.pageSize));
            state.page = Math.min(state.page, totalPages);
            const start = totalItems === 0 ? 0 : ((state.page - 1) * state.pageSize) + 1;
            const end = totalItems === 0 ? 0 : Math.min(state.page * state.pageSize, totalItems);

            rows.forEach((row) => row.classList.add('is-hidden'));
            filteredRows.slice(start - 1, end).forEach((row) => row.classList.remove('is-hidden'));

            if (emptyRow) {
                emptyRow.style.display = rows.length === 0 ? '' : 'none';
            }

            if (rows.length > 0) {
                if (totalItems === 0) {
                    if (!tbody.contains(noResultsRow)) {
                        tbody.appendChild(noResultsRow);
                    }
                } else if (tbody.contains(noResultsRow)) {
                    noResultsRow.remove();
                }
            }

            if (infoEl) {
                infoEl.textContent = totalItems === 0
                    ? 'Showing 0 to 0 of 0 entries'
                    : `Showing ${start} to ${end} of ${totalItems} entries`;
            }

            renderPagination(totalItems, totalPages);
        };

        searchInput?.addEventListener('input', () => {
            state.search = normalize(searchInput.value);
            state.page = 1;
            render();
        });

        lengthSelect?.addEventListener('change', () => {
            state.pageSize = Number(lengthSelect.value || 10);
            state.page = 1;
            render();
        });

        options.onInit?.({ render, state, rows });
        render();

        return { render, state, rows };
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCustomTable('categoriesTableShell');

        const parameterCategoryFilter = document.getElementById('parameterCategoryFilter');
        const parametersTable = initCustomTable('parametersTableShell', {
            rowFilter: (row) => {
                const selectedCategory = String(parameterCategoryFilter?.value || '').trim().toLowerCase();
                if (!selectedCategory) return true;
                const categoryCell = row.children[3];
                return String(categoryCell?.textContent || '').trim().toLowerCase() === selectedCategory;
            },
        });

        parameterCategoryFilter?.addEventListener('change', () => {
            if (!parametersTable) return;
            parametersTable.state.page = 1;
            parametersTable.render();
        });

        initCustomTable('packagesTableShell');

        document.addEventListener('submit', (e) => {
            const formEl = e.target.closest('.delete-form');

            if (!formEl || formEl.dataset.confirmed === '1') {
                return;
            }

            e.preventDefault();

            const submitDelete = () => {
                formEl.dataset.confirmed = '1';
                formEl.submit();
            };

            if (typeof Swal === 'undefined') {
                if (window.confirm('Tindakan ini tidak dapat dibatalkan. Lanjut hapus data?')) {
                    submitDelete();
                }

                return;
            }

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
                    submitDelete();
                }
            });
        });
    });

    categoryModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';

        if (mode === 'edit') {
            categoryModalTitle.textContent = 'Edit Kategori';
            categoryForm.action = "{{ route('superadmin.service-categories.store') }}/" + button.getAttribute('data-id');
            categoryMethodInput.value = 'PUT';
            categoryNameInput.value = button.getAttribute('data-name');
            categoryShortInput.value = button.getAttribute('data-short-code') || '';
            categoryStatusSelect.value = button.getAttribute('data-status');
        } else {
            categoryModalTitle.textContent = 'Tambah Kategori';
            categoryForm.action = "{{ route('superadmin.service-categories.store') }}";
            categoryMethodInput.value = 'POST';
            categoryNameInput.value = '';
            categoryShortInput.value = '';
            categoryStatusSelect.value = '1';
        }
    });

    parameterModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';

        if (mode === 'edit') {
            parameterModalTitle.textContent = 'Edit Parameter';
            parameterForm.action = "{{ route('superadmin.service-parameters.store') }}/" + button.getAttribute('data-id');
            parameterMethodInput.value = 'PUT';
            parameterNameInput.value = button.getAttribute('data-name');
            parameterShortInput.value = button.getAttribute('data-short-code') || '';
            parameterPriceInput.value = button.getAttribute('data-price');
            parameterCategorySelect.value = button.getAttribute('data-category-id');
            parameterStatusSelect.value = button.getAttribute('data-status');
        } else {
            parameterModalTitle.textContent = 'Tambah Parameter';
            parameterForm.action = "{{ route('superadmin.service-parameters.store') }}";
            parameterMethodInput.value = 'POST';
            parameterNameInput.value = '';
            parameterShortInput.value = '';
            parameterPriceInput.value = '';
            parameterCategorySelect.value = '';
            parameterStatusSelect.value = '1';
        }
    });

    function addPackageItemRow(item = {}) {
        if (!packageItemsWrap || !packageItemTemplate) {
            return;
        }

        const fragment = packageItemTemplate.content.cloneNode(true);
        const row = fragment.querySelector('.package-item-row');
        const parameterSelect = row.querySelector('select[name="item_parameter_ids[]"]');
        const labelInput = row.querySelector('input[name="item_labels[]"]');
        const removeBtn = row.querySelector('.remove-package-item-row');

        if (parameterSelect && item.parameter_id) {
            parameterSelect.value = String(item.parameter_id);
        }

        if (labelInput && item.label) {
            labelInput.value = item.label;
        }

        removeBtn?.addEventListener('click', () => {
            row.remove();
        });

        packageItemsWrap.appendChild(fragment);
    }

    addPackageItemRowBtn?.addEventListener('click', () => addPackageItemRow());

    packageModal?.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const mode = button?.getAttribute('data-mode') || 'create';
        const methodInput = packageForm.querySelector('input[name="_method"]');

        packageItemsWrap.innerHTML = '';

        if (mode === 'edit') {
            packageModalTitle.textContent = 'Edit Paket Emisi';
            packageForm.action = "{{ route('superadmin.service-packages.store') }}/" + button.getAttribute('data-id');
            methodInput.value = 'PUT';
            packageForm.querySelector('input[name="name"]').value = button.getAttribute('data-name') || '';
            packageForm.querySelector('input[name="short_code"]').value = button.getAttribute('data-short-code') || '';
            packageForm.querySelector('input[name="badge"]').value = button.getAttribute('data-badge') || '';
            packageForm.querySelector('input[name="subtitle"]').value = button.getAttribute('data-subtitle') || '';
            packageForm.querySelector('input[name="price"]').value = button.getAttribute('data-price') || '';
            packageForm.querySelector('input[name="unit"]').value = button.getAttribute('data-unit') || 'per lokasi';
            packageForm.querySelector('input[name="sort_order"]').value = button.getAttribute('data-sort-order') || '0';
            packageForm.querySelector('select[name="is_active"]').value = button.getAttribute('data-status') || '1';

            const items = JSON.parse(button.getAttribute('data-items') || '[]');
            if (items.length === 0) {
                addPackageItemRow();
            } else {
                items.forEach((item) => addPackageItemRow(item));
            }
        } else {
            packageModalTitle.textContent = 'Tambah Paket Emisi';
            packageForm.action = "{{ route('superadmin.service-packages.store') }}";
            methodInput.value = 'POST';
            packageForm.querySelector('input[name="name"]').value = '';
            packageForm.querySelector('input[name="short_code"]').value = '';
            packageForm.querySelector('input[name="badge"]').value = '';
            packageForm.querySelector('input[name="subtitle"]').value = '';
            packageForm.querySelector('input[name="price"]').value = '';
            packageForm.querySelector('input[name="unit"]').value = 'per lokasi';
            packageForm.querySelector('input[name="sort_order"]').value = '0';
            packageForm.querySelector('select[name="is_active"]').value = '1';
            addPackageItemRow();
        }
    });
</script>
@endpush
</div>
@endsection
