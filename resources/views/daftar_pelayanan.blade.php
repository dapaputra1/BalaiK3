@extends('layouts.app')

@section('seo_title', 'Daftar Pelayanan Balai K3 Surabaya | Tarif PNBP dan Layanan K3')
@section('seo_description', 'Lihat daftar pelayanan Balai K3 Surabaya beserta kategori layanan, parameter pengujian, dan tarif PNBP untuk kebutuhan keselamatan dan kesehatan kerja.')
@section('seo_keywords', 'daftar pelayanan Balai K3 Surabaya, tarif PNBP K3, pengujian lingkungan kerja, pelatihan K3')
@section('seo_image', asset('images/header3.jpg'))
@section('seo_json_ld')
@php echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'Daftar Pelayanan Balai K3 Surabaya',
    'itemListElement' => $categories->values()->map(fn ($category, $index) => [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $category->name,
        'description' => $categoryMeta[$category->name]['description'] ?? null,
    ])->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); @endphp
@endsection

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

@php
    $uiMeta = [
        'Ambien' => ['icon' => 'bi bi-wind', 'card_icon' => 'bi bi-wind'],
        'Emisi' => ['icon' => 'bi bi-cloud-fill', 'card_icon' => 'bi bi-cloud-fill'],
        'Kesehatan' => ['icon' => 'bi bi-heart-pulse-fill', 'card_icon' => 'bi bi-heart-pulse-fill'],
        'Lingkungan Kerja' => ['icon' => 'bi bi-building-fill', 'card_icon' => 'bi bi-building-fill'],
        'Pelatihan' => ['icon' => 'bi bi-person-video3', 'card_icon' => 'bi bi-person-video3'],
    ];
@endphp

<style>
    html,
    html body,
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    html {
        background: #f6f6f6 !important;
    }

    body {
        background: #f6f6f6 !important;
        font-family: 'Poppins', sans-serif;
    }

    body::before {
        display: none !important;
    }

    .service-page {
        --service-nav-offset: 112px;
        --service-nav-lift: 28px;
        position: relative;
        min-height: 100vh;
        margin-top: calc((var(--service-nav-offset) + var(--service-nav-lift)) * -1);
        padding: 72px 0 120px;
        background: #f6f6f6;
        overflow: hidden;
    }

    .service-page::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 300px;
        background:
            radial-gradient(circle at 20% 10%, rgba(255, 255, 255, .16), transparent 42%),
            linear-gradient(
                180deg,
                #2f5b86 0%,
                #436a93 24%,
                #6488ac 49%,
                #86a4bf 69%,
                #b7cbdb 84%,
                #dce7f0 94%,
                #f6f6f6 100%
            );
        z-index: 0;
        pointer-events: none;
    }

    .service-shell {
        width: min(100% - 40px, 980px);
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .sticky-btn {
        position: fixed;
        right: 24px;
        bottom: 24px;
        width: 58px;
        height: 58px;
        z-index: 100;
        background: #214f80;
        border: 2px solid #ffffff;
        box-shadow: 0 10px 24px rgba(16, 52, 86, 0.28);
        display: inline-flex;
    }

    .sticky-btn.is-draggable {
        touch-action: none;
        user-select: none;
        -webkit-user-select: none;
    }

    .sticky-btn.is-dragging {
        cursor: grabbing;
    }

    .sticky-btn.is-snapping {
        transition: left .18s ease, top .18s ease;
    }

    .cart-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        background: #dc3545;
        color: #fff;
        font-size: 12px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
    }

    .cart-bump {
        animation: cart-bump 0.4s ease;
    }

    .btn-bump {
        animation: btn-bump 0.3s ease;
    }

    @keyframes cart-bump {
        0% { transform: scale(1); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1); }
    }

    @keyframes btn-bump {
        0% { transform: scale(1); }
        50% { transform: scale(1.06); }
        100% { transform: scale(1); }
    }

    .service-hero-card {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(300px, 0.8fr);
        align-items: stretch;
        min-height: 192px;
        margin-top: 116px;
        border-radius: 26px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 24px 50px rgba(19, 49, 81, 0.18);
    }

    .service-hero-copy {
        padding: 30px 34px 18px 38px;
    }

    .service-hero-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 10px;
        background: linear-gradient(180deg, #42b98d, #33a976);
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 6px 14px rgba(55, 173, 123, 0.22);
    }

    .service-hero-title {
        margin: 14px 0 10px;
        color: #214f80;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.02em;
        text-align: justify;
        text-shadow: 0 4px 8px rgba(0, 0, 0, 0.16);
    }

    .service-hero-desc {
        margin: 0;
        color: #161616;
        font-size: 12px;
        line-height: 1.6;
        max-width: 610px;
    }

    .service-hero-highlight {
        color: #37b487;
        font-weight: 500;
    }

    .service-hero-media {
        position: relative;
        min-height: 100%;
        background:
            linear-gradient(90deg, rgba(255, 255, 255, 0.28) 0, rgba(255, 255, 255, 0) 24%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.34), rgba(255, 255, 255, 0.12)),
            url('{{ asset('images/header3.jpg') }}') center center/cover no-repeat;
    }

    .service-main {
        padding-top: 34px;
    }

    .service-section-title {
        margin: 0;
        padding-left: 18px;
        color: #214f80;
        font-size: 18px;
        font-weight: 700;
        line-height: 1.2;
    }

    .service-section-copy {
        margin: 6px 0 0;
        padding-left: 18px;
        color: #171717;
        font-size: 11px;
        line-height: 1.5;
    }

    .service-category-grid {
        --category-gap: 10px;
        --category-card-width: 164px;
        --category-loop-distance: 860px;
        margin-top: 14px;
        width: min(100%, 860px);
        margin-left: auto;
        margin-right: auto;
        padding: 6px 4px 14px;
        overflow: hidden;
        overflow-y: visible;
        position: relative;
    }

    .service-category-marquee {
        display: flex;
        align-items: stretch;
        gap: var(--category-gap);
        width: max-content;
        will-change: transform;
        animation: service-category-marquee 22s linear infinite;
    }

    .service-category-grid:hover .service-category-marquee,
    .service-category-grid.is-paused .service-category-marquee {
        animation-play-state: paused;
    }

    .service-category-track {
        display: flex;
        flex-wrap: nowrap;
        gap: var(--category-gap);
        min-width: max-content;
    }

    .service-category-card {
        flex: 0 0 var(--category-card-width);
        width: var(--category-card-width);
        border: 0;
        border-radius: 14px;
        background: #ffffff;
        padding: 11px 10px 10px;
        box-shadow: 0 8px 0 rgba(26, 62, 100, 0.12), 0 12px 24px rgba(16, 47, 79, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-align: center;
        min-height: 74px;
        transform-origin: center center;
        transition: transform 0.24s ease, box-shadow 0.24s ease;
    }

    .service-category-card:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 12px 0 rgba(26, 62, 100, 0.12), 0 22px 32px rgba(16, 47, 79, 0.16);
        z-index: 2;
    }

    .service-category-grid.is-paused {
        cursor: default;
    }

    @keyframes service-category-marquee {
        from {
            transform: translate3d(0, 0, 0);
        }
        to {
            transform: translate3d(calc(-1 * var(--category-loop-distance)), 0, 0);
        }
    }

    .service-category-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: #f4f6f8;
        color: #214f80;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        line-height: 1;
    }

    .service-category-label {
        color: #214f80;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
    }

    .service-accordion-list {
        margin-top: 22px;
        display: grid;
        gap: 14px;
        width: min(100%, 860px);
        margin-left: auto;
        margin-right: auto;
    }

    .service-accordion-item {
        border-radius: 15px;
    }

    .service-accordion-toggle {
        width: 100%;
        min-height: 48px;
        border: 0;
        border-radius: 12px;
        background: #15406a;
        padding: 0 14px 0 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        text-align: left;
        box-shadow: 0 6px 0 rgba(26, 62, 100, 0.1);
    }

    .service-accordion-toggle::after {
        display: none;
    }

    .service-accordion-toggle:not(.collapsed) {
        background: #15406a;
        color: #ffffff;
        border-radius: 12px 12px 0 0;
        box-shadow: none;
    }

    .service-accordion-toggle:focus,
    .service-accordion-toggle:not(.collapsed):focus {
        background: #15406a;
        color: #ffffff;
        box-shadow: none;
    }

    .service-accordion-main {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .service-accordion-icon {
        width: 25px;
        height: 25px;
        border-radius: 999px;
        background: #ffffff;
        color: #214f80;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        line-height: 1;
        flex-shrink: 0;
    }

    .service-accordion-title {
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.2;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.14);
    }

    .service-accordion-arrow {
        color: #ffffff;
        font-size: 11px;
        line-height: 1;
        transition: transform 0.22s ease;
        flex-shrink: 0;
    }

    .service-accordion-toggle:not(.collapsed) .service-accordion-arrow {
        transform: rotate(180deg);
    }

    .service-accordion-panel {
        background: #ffffff;
        border-radius: 0 0 12px 12px;
        box-shadow: 0 18px 34px rgba(19, 49, 81, 0.14);
        overflow: hidden;
    }

    .service-accordion-desc {
        padding: 12px 14px 10px;
        color: #4e5965;
        font-size: 11px;
        line-height: 1.55;
        border-bottom: 1px solid #e9edf1;
    }

    .service-parameter-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto auto;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-top: 1px solid #edf1f4;
    }

    .service-parameter-row:first-child {
        border-top: 0;
    }

    .service-parameter-name {
        color: #18202a;
        font-size: 11px;
        line-height: 1.5;
    }

    .service-parameter-price {
        color: #214f80;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .service-parameter-group {
        border-top: 1px solid #edf1f4;
    }

    .service-parameter-group-title {
        padding: 10px 14px 6px;
        color: #18202a;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .service-parameter-subrow {
        padding-left: 28px;
    }

    .service-parameter-table {
        border-top: 1px solid #edf1f4;
    }

    .service-parameter-table-title {
        padding: 12px 14px 8px;
        color: #214f80;
        font-size: 12px;
        font-weight: 700;
    }

    .service-parameter-table-header,
    .service-parameter-table-row {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(0, 1.3fr) 118px 26px;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border-top: 1px solid #edf1f4;
    }

    .service-parameter-table-header {
        background: #edf4fb;
        color: #214f80;
        font-size: 11px;
        font-weight: 700;
    }

    .service-parameter-table-row:not(.service-parameter-table-group-header) {
        position: relative;
        z-index: 0;
        background-color: #ffffff;
        transition:
            transform 180ms ease,
            background-color 180ms ease,
            box-shadow 180ms ease;
    }

    @media (hover: hover) {
        .service-parameter-table-row:not(.service-parameter-table-group-header):hover {
            z-index: 1;
            transform: scale(1.012);
            background-color: rgba(31, 111, 181, 0.09);
            box-shadow: 0 5px 14px rgba(31, 111, 181, 0.16);
        }

        .service-parameter-table-row:not(.service-parameter-table-group-header):hover .service-parameter-name,
        .service-parameter-table-row:not(.service-parameter-table-group-header):hover .service-parameter-unit,
        .service-parameter-table-row:not(.service-parameter-table-group-header):hover .service-parameter-price {
            font-weight: 700;
        }

        .service-parameter-table-row:not(.service-parameter-table-group-header):hover .service-parameter-unit {
            color: #000000;
        }
    }

    .service-parameter-table-row:not(.service-parameter-table-group-header):focus-within {
        z-index: 1;
        transform: scale(1.012);
        background-color: rgba(31, 111, 181, 0.09);
        box-shadow: 0 5px 14px rgba(31, 111, 181, 0.16);
    }

    .service-parameter-table-row:not(.service-parameter-table-group-header):focus-within .service-parameter-name,
    .service-parameter-table-row:not(.service-parameter-table-group-header):focus-within .service-parameter-unit,
    .service-parameter-table-row:not(.service-parameter-table-group-header):focus-within .service-parameter-price {
        font-weight: 700;
    }

    .service-parameter-table-row:not(.service-parameter-table-group-header):focus-within .service-parameter-unit {
        color: #000000;
    }

    .service-parameter-unit {
        color: #44505c;
        font-size: 11px;
        line-height: 1.5;
        min-width: 0;
        text-align: left;
        justify-self: stretch;
        transition: color 180ms ease;
    }

    .service-parameter-table-group-title {
        padding: 12px 16px 10px;
        color: #18202a;
        font-size: 11px;
        font-weight: 700;
        background: #ffffff;
    }

    .service-parameter-table-group-header .service-parameter-table-group-title {
        padding: 0;
        background: transparent;
    }

    .service-parameter-table-group {
        --service-border-angle: 0deg;
        margin: 10px 12px;
        border: 1px solid transparent;
        border-radius: 14px;
        overflow: hidden;
        background:
            linear-gradient(#ffffff, #ffffff) padding-box,
            conic-gradient(
                from var(--service-border-angle),
                #e4ebf3 0deg 270deg,
                #9edbff 300deg,
                #1685e5 328deg,
                #d3efff 350deg,
                #e4ebf3 360deg
            ) border-box;
        box-shadow:
            0 6px 16px rgba(28, 64, 102, 0.06),
            0 0 12px rgba(22, 133, 229, 0.12);
        animation:
            service-border-orbit 4.8s linear infinite,
            service-border-pulse 2.4s ease-in-out infinite;
    }

    @property --service-border-angle {
        syntax: "<angle>";
        inherits: false;
        initial-value: 0deg;
    }

    @keyframes service-border-orbit {
        to {
            --service-border-angle: 360deg;
        }
    }

    @keyframes service-border-pulse {
        50% {
            box-shadow:
                0 6px 16px rgba(28, 64, 102, 0.06),
                0 0 18px rgba(22, 133, 229, 0.28);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .service-parameter-table-group {
            animation: none;
        }
    }

    .service-parameter-table-subrow {
        padding-right: 16px;
    }

    .service-parameter-table-subrow .service-parameter-name {
        position: relative;
        padding-left: 26px;
    }

    .service-parameter-table-subrow .service-parameter-name::before {
        content: "";
        position: absolute;
        left: 6px;
        top: 50%;
        width: 6px;
        height: 6px;
        border-radius: 999px;
        background: #8da0b3;
        transform: translateY(-50%);
    }

    .service-parameter-name-wrap {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .service-parameter-info-trigger {
        width: 18px;
        height: 18px;
        min-width: 18px;
        border-radius: 999px;
        border: 1px solid rgba(33, 79, 128, 0.42);
        color: #214f80;
        background: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        transition: background .18s ease, color .18s ease, transform .18s ease;
    }

    .service-parameter-info-trigger:hover,
    .service-parameter-info-trigger:focus-visible {
        background: #214f80;
        color: #ffffff;
        transform: translateY(-1px);
        outline: none;
    }

    .service-order-btn {
        width: 26px;
        min-width: 26px;
        height: 26px;
        border: 1px solid transparent;
        border-radius: 999px;
        background: #214f80;
        color: #ffffff;
        font-size: 10px;
        font-weight: 600;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 18px rgba(33, 79, 128, 0.2);
        transition:
            background-color 180ms ease,
            border-color 180ms ease,
            color 180ms ease,
            box-shadow 180ms ease;
    }

    .service-order-btn:hover,
    .service-order-btn:focus-visible {
        background: #ffffff;
        border-color: #214f80;
        color: #214f80;
        box-shadow: 0 6px 14px rgba(33, 79, 128, 0.2);
        outline: none;
    }

    .service-empty {
        padding: 12px 14px 16px;
        color: #7a838d;
        font-size: 11px;
        text-align: center;
    }

    .service-package-section {
        padding: 16px 16px 18px;
        border-bottom: 1px solid #e9edf1;
        background:
            radial-gradient(circle at top right, rgba(70, 137, 192, 0.12), transparent 34%),
            linear-gradient(180deg, #f8fbfe 0%, #f3f8fc 100%);
    }

    .service-package-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .service-package-heading {
        margin: 0;
        color: #173b62;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
    }

    .service-package-copy {
        margin: 6px 0 0;
        color: #516171;
        font-size: 10px;
        line-height: 1.5;
        max-width: 660px;
    }

    .service-package-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 10px;
    }

    .service-package-card {
        width: 100%;
        border: 1px solid #d8e3ee;
        border-radius: 13px;
        background: #ffffff;
        padding: 11px;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 7px;
        box-shadow: 0 10px 24px rgba(18, 53, 86, 0.08);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease;
    }

    .service-package-card:hover {
        transform: translateY(-2px);
        border-color: #9db8d3;
        box-shadow: 0 14px 30px rgba(18, 53, 86, 0.12);
    }

    .service-package-card.is-active {
        border-color: #2f6ea0;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 1) 0%, rgba(239, 248, 255, 1) 100%);
        box-shadow: 0 18px 34px rgba(20, 64, 104, 0.16);
    }

    .service-package-card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .service-package-badge,
    .service-package-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 20px;
        padding: 0 8px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 700;
        letter-spacing: .01em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .service-package-badge {
        background: #e9f1f9;
        color: #214f80;
    }

    .service-package-card-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .service-package-info-trigger {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #ffffff;
        color: #214f80;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        border: 1.5px solid rgba(33, 79, 128, 0.45);
        box-shadow: 0 4px 10px rgba(33, 79, 128, 0.12);
        cursor: pointer;
        transition: transform .18s ease, background .18s ease, color .18s ease;
    }

    .service-package-info-trigger:hover,
    .service-package-info-trigger:focus-visible {
        background: #214f80;
        color: #ffffff;
        transform: translateY(-1px);
        outline: none;
    }

    .swal2-container {
        z-index: 20000 !important;
    }

    .swal-package-info-popup {
        width: min(560px, calc(100vw - 32px)) !important;
        padding: 18px 18px 14px !important;
        border-radius: 18px !important;
        z-index: 20001 !important;
    }

    .swal-package-info-title {
        font-size: 16px !important;
        line-height: 1.2 !important;
        margin-bottom: 8px !important;
    }

    .swal-package-info-html {
        margin: 0 !important;
        font-size: 12.5px !important;
        line-height: 1.45 !important;
        text-align: left !important;
    }

    .swal-package-info-confirm {
        min-width: 96px;
        border-radius: 12px !important;
        padding: 10px 16px !important;
        font-size: 14px !important;
    }

    .swal-package-info-close {
        color: #6b7280 !important;
        font-size: 24px !important;
        top: 12px !important;
        right: 12px !important;
        transition: color .18s ease, transform .18s ease;
    }

    .swal-package-info-close:hover,
    .swal-package-info-close:focus {
        color: #214f80 !important;
        transform: scale(1.04);
        box-shadow: none !important;
    }

    .service-package-status {
        background: #e5f7ee;
        color: #1b8f59;
    }

    .service-package-title {
        margin: 0;
        color: #17283a;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.2;
    }

    .service-package-subtitle {
        margin: 1px 0 0;
        color: #57697a;
        font-size: 9px;
        line-height: 1.25;
    }

    .service-package-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .service-package-chip {
        display: inline-flex;
        align-items: center;
        min-height: 22px;
        padding: 0 8px;
        border-radius: 7px;
        background: #eff5fb;
        color: #24425f;
        font-size: 9px;
        font-weight: 600;
        line-height: 1.3;
    }

    .service-package-footer {
        margin-top: auto;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 8px;
    }

    .service-package-price-wrap {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .service-package-price-label {
        color: #6c7b89;
        font-size: 8px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .service-package-price {
        color: #173b62;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.1;
    }

    .service-package-unit {
        color: #607182;
        font-size: 9px;
        line-height: 1.3;
    }

    .service-package-select {
        min-width: 98px;
        border: 0;
        border-radius: 9px;
        background: #214f80;
        color: #ffffff;
        padding: 7px 10px;
        font-size: 10px;
        font-weight: 700;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        box-shadow: 0 10px 22px rgba(33, 79, 128, 0.22);
    }

    .service-package-card.is-active .service-package-select {
        background: #163f69;
    }

    @media (max-width: 1199.98px) {
        .service-category-grid {
            --category-card-width: 293.33px;
            width: min(100%, 900px);
        }
    }

    @media (max-width: 991.98px) {
        .service-page::before {
            height: 288px;
        }

        .service-page {
            padding-top: 72px;
        }

        .service-hero-card {
            grid-template-columns: 1fr;
            margin-top: 94px;
        }

        .service-hero-copy {
            padding: 28px 26px 16px;
        }

        .service-hero-media {
            min-height: 188px;
        }

        .service-category-grid {
            --category-gap: 14px;
            --category-card-width: 403px;
            gap: 14px;
            width: min(100%, 820px);
        }

        .service-accordion-list {
            gap: 10px;
            margin-top: 18px;
        }
    }

    @media (max-width: 575.98px) {
        .service-page::before {
            height: 256px;
        }

        .service-shell {
            width: min(100% - 24px, 1150px);
        }

        .service-page {
            padding-top: 88px;
            padding-bottom: 92px;
        }

        .sticky-btn {
            right: 14px;
            bottom: 14px;
            width: 52px;
            height: 52px;
        }

        .service-hero-card {
            margin-top: 84px;
            border-radius: 22px;
        }

        .service-hero-copy {
            padding: 24px 18px 14px;
        }

        .service-hero-title {
            font-size: 14px;
            text-align: left;
            line-height: 1.3;
        }

        .service-hero-desc,
        .service-section-copy {
            font-size: 11px;
        }

        .service-main {
            padding-top: 26px;
        }

        .service-section-title {
            padding-left: 10px;
            font-size: 16px;
        }

        .service-section-copy {
            padding-left: 10px;
        }

        .service-category-grid {
            margin-top: 10px;
            width: calc(100% + 8px);
            margin-left: -4px;
            margin-right: -4px;
            padding: 4px 4px 14px;
            overflow-x: auto;
            overflow-y: visible;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x mandatory;
        }

        .service-category-grid::-webkit-scrollbar {
            display: none;
        }

        .service-category-marquee {
            animation: none;
        }

        .service-category-track[aria-hidden="true"] {
            display: none;
        }

        .service-category-card {
            flex: 0 0 136px;
            width: 136px;
            max-width: 136px;
            min-height: 78px;
            padding: 12px 10px 10px;
            scroll-snap-align: center;
            transform: scale(0.94);
            opacity: 0.84;
            transition: transform 0.22s ease, opacity 0.22s ease, box-shadow 0.22s ease;
        }

        .service-category-card.service-card-active {
            transform: scale(1.03);
            opacity: 1;
            box-shadow: 0 12px 0 rgba(26, 62, 100, 0.12), 0 20px 28px rgba(16, 47, 79, 0.16);
        }

        .service-category-card.service-card-pressed {
            transform: scale(0.88);
        }

        .service-category-card.service-card-active.service-card-pressed {
            transform: scale(0.95);
        }

        .service-category-icon {
            width: 32px;
            height: 32px;
            font-size: 17px;
        }

        .service-category-label {
            font-size: 11px;
        }

        .service-accordion-list {
            margin-top: 14px;
            gap: 10px;
        }

        .service-accordion-toggle {
            min-height: 46px;
            padding: 0 12px 0 9px;
            border-radius: 12px;
        }

        .service-accordion-toggle:not(.collapsed) {
            border-radius: 12px 12px 0 0;
        }

        .service-accordion-main {
            gap: 9px;
        }

        .service-accordion-icon {
            width: 23px;
            height: 23px;
            font-size: 12px;
        }

        .service-accordion-title {
            font-size: 12px;
        }

        .service-accordion-panel {
            border-radius: 0 0 12px 12px;
        }

        .service-accordion-desc {
            padding: 10px 12px 8px;
            font-size: 10px;
        }

        .service-package-section {
            padding: 12px 12px 14px;
        }

        .service-package-header {
            display: block;
        }

        .service-package-heading {
            font-size: 13px;
        }

        .service-package-copy {
            font-size: 10px;
        }

        .service-package-grid {
            grid-template-columns: 1fr;
            gap: 10px;
            margin-top: 12px;
        }

        .service-package-card {
            padding: 14px;
            border-radius: 16px;
            gap: 10px;
        }

        .service-package-title {
            font-size: 14px;
        }

        .service-package-subtitle {
            font-size: 11px;
        }

        .service-package-chip {
            min-height: 24px;
            font-size: 10px;
        }

        .service-package-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .service-package-price {
            font-size: 18px;
        }

        .service-package-select {
            width: 100%;
            min-width: 0;
        }

        .service-parameter-row {
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-areas:
                "name action"
                "price action";
            align-items: center;
            column-gap: 10px;
            row-gap: 3px;
            padding: 10px 12px;
        }

        .service-parameter-name {
            grid-area: name;
            font-size: 10px;
        }

        .service-parameter-price {
            grid-area: price;
            font-size: 10px;
        }

        .service-order-btn {
            grid-area: action;
            justify-self: end;
            align-self: center;
            width: 26px;
        }

        .service-parameter-table-header,
        .service-parameter-table-row {
            grid-template-columns: minmax(0, 1fr) auto;
            grid-template-areas:
                "name action"
                "unit action"
                "price action";
            align-items: start;
            column-gap: 10px;
            row-gap: 3px;
            padding: 10px 12px;
        }

        .service-parameter-table-header {
            display: block;
            padding: 10px 12px;
            background: #edf4fb;
            border-top: 1px solid #edf1f4;
        }

        .service-parameter-table-header > :not(:first-child) {
            display: none;
        }

        .service-parameter-table-header > :first-child {
            display: block;
            color: #214f80;
            font-size: 11px;
            font-weight: 700;
        }

        .service-parameter-table-row .service-parameter-name,
        .service-parameter-table-row .service-parameter-unit,
        .service-parameter-table-row .service-parameter-price,
        .service-parameter-table-row .service-order-btn {
            grid-area: auto;
        }

        .service-parameter-table-row .service-parameter-name {
            grid-area: name;
            font-size: 10px;
        }

        .service-parameter-table-row .service-parameter-unit {
            grid-area: unit;
            font-size: 10px;
            line-height: 1.4;
        }

        .service-parameter-table-row .service-parameter-price {
            grid-area: price;
            font-size: 10px;
        }

        .service-parameter-table-row .service-order-btn {
            grid-area: action;
            justify-self: end;
            align-self: center;
        }

        .service-parameter-table-group {
            margin: 8px 10px;
            border-radius: 12px;
        }

        .service-parameter-table-group-header {
            grid-template-areas:
                "name action"
                "price action";
        }

        .service-parameter-table-group-header .service-parameter-unit {
            display: none;
        }

        .service-parameter-table-subrow {
            padding-right: 12px;
        }

        .service-parameter-table-subrow .service-parameter-name::before {
            left: 8px;
        }
    }
</style>

<div class="service-page">
    @auth
        <a href="/keranjang" class="btn btn-primary shadow rounded-circle sticky-btn d-flex align-items-center justify-content-center" id="cartButton">
            <i class="bi bi-cart3 fs-4"></i>
            <span class="cart-badge" id="cartBadge">0</span>
        </a>
    @endauth

    <div class="service-shell">
        <section class="service-hero-card">
            <div class="service-hero-copy">
                <span class="service-hero-badge">Informasi Publik</span>
                <h1 class="service-hero-title">Peraturan Menteri Keuangan Republik Indonesia Nomor 6 /PMK.02/2023 Tentang Jenis dan Tarif Atas Jenis Penerimaan Negara Bukan Pajak yang Bersifat Volatil yang Berlaku pada Kementerian Ketenagakerjaan</h1>
            </div>
            <div class="service-hero-media" aria-hidden="true"></div>
        </section>

        <section class="service-main">
            <h2 class="service-section-title">Kategori Layanan</h2>
            <p class="service-section-copy">
                Klik ikon atau tekan bar kategori untuk melihat tabel layanan.
                @auth
                    Setelah itu, klik â€œPesanâ€ untuk mengajukan layanan.
                @else
                    Fitur pemesanan dan keranjang akan muncul setelah login.
                @endauth
            </p>

            <div class="service-category-grid">
                @if($categories->isNotEmpty())
                    <div class="service-category-marquee">
                        @for($copyIndex = 0; $copyIndex < 2; $copyIndex++)
                            <div class="service-category-track" @if($copyIndex === 1) aria-hidden="true" @endif>
                                @foreach($categories as $category)
                                    @php
                                        $meta = $categoryMeta[$category->name] ?? ['icon' => 'bi bi-grid', 'description' => ''];
                                        $ui = $uiMeta[$category->name] ?? [];
                                        $iconClass = $ui['card_icon'] ?? ($ui['icon'] ?? $meta['icon']);
                                        $collapseId = 'collapseCategory' . $loop->index;
                                    @endphp
                                    <button type="button" class="service-category-card" data-service-target="{{ $collapseId }}">
                                        <span class="service-category-icon"><i class="{{ $iconClass }}"></i></span>
                                        <span class="service-category-label">{{ $category->name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                @else
                    <div class="text-muted">Belum ada kategori pelayanan.</div>
                @endif
            </div>

            <div class="service-accordion-list accordion" id="accordionPelayanan">
                @forelse($categories as $category)
                    @php
                        $meta = $categoryMeta[$category->name] ?? ['icon' => 'bi bi-grid', 'description' => ''];
                        $ui = $uiMeta[$category->name] ?? [];
                        $iconClass = $ui['icon'] ?? $meta['icon'];
                        $headingId = 'headingCategory' . $loop->index;
                        $collapseId = 'collapseCategory' . $loop->index;
                        $renderSections = isset($category->display_sections) && is_array($category->display_sections)
                            ? $category->display_sections
                            : null;

                        if ($category->name === 'Ambien') {
                            $ambienFisikaMeta = [
                                'DB24' => ['name' => 'Kebisingan 24 Jam (7x ukur)', 'unit' => 'per titik per pengujian', 'price' => 350000],
                                'DB' => ['name' => 'Kebisingan Sesaat - Tanpa Analisis Frekuensi', 'unit' => 'per titik per pengujian', 'price' => 50000],
                                'DBAF' => ['name' => 'Kebisingan Sesaat - Dengan Analisis Frekuensi', 'unit' => 'per titik per pengujian', 'price' => 75000],
                                'MLDA1' => ['name' => 'Medan Listrik', 'unit' => 'per titik per pengujian', 'price' => 100000],
                            ];

                            $ambienKimiaMeta = [
                                ['short_code' => 'COAM', 'name' => 'CO', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                ['short_code' => 'CO2AM', 'name' => 'CO2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                ['short_code' => 'H2S1', 'name' => 'H2S', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                ['short_code' => 'NH31', 'name' => 'NH3', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                ['short_code' => 'NO21', 'name' => 'NO2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                ['short_code' => 'SO21', 'name' => 'SO2', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                ['short_code' => 'HC1', 'name' => 'HC', 'unit' => 'per sampel', 'price' => 250000],
                                ['short_code' => 'OX1', 'name' => 'OX', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                ['short_code' => 'DPM11', 'name' => 'Debu PM10 (24 jam)', 'unit' => 'per parameter per sampel uji', 'price' => 1250000],
                                ['short_code' => 'DPM21', 'name' => 'Debu PM2,5 (24 jam)', 'unit' => 'per parameter per sampel uji', 'price' => 1250000],
                                ['short_code' => 'TSPT2', 'name' => 'Suspended Particulate (TSP) 24 jam', 'unit' => 'per parameter per sampel uji', 'price' => 1250000],
                                ['short_code' => 'KDAPB', 'name' => 'Kadar Debu Logam (AAS) - Pb', 'unit' => 'per parameter per sampel per pengujian', 'price' => 150000],
                            ];

                            $ambienParametersByCode = collect($category->parameters ?? [])->keyBy('short_code');
                            $globalParametersByCode = collect($categories ?? [])
                                ->flatMap(fn ($cat) => $cat->parameters ?? collect())
                                ->keyBy('short_code');

                            $ambienFisikaRows = collect($ambienFisikaMeta)
                                ->map(function ($metaRow, $shortCode) use ($ambienParametersByCode, $globalParametersByCode) {
                                    $sourceParameter = $ambienParametersByCode->get($shortCode) ?? $globalParametersByCode->get($shortCode);
                                    if (!$sourceParameter) {
                                        return null;
                                    }

                                    $row = clone $sourceParameter;
                                    $row->name = $metaRow['name'];
                                    $row->unit = $metaRow['unit'];
                                    $row->price = $metaRow['price'];
                                    $row->short_code = $shortCode;

                                    return $row;
                                })
                                ->filter()
                                ->values();

                            $mapAmbienRows = function ($configRows) use ($ambienParametersByCode) {
                                return collect($configRows)
                                    ->map(function ($config) use ($ambienParametersByCode) {
                                        if (isset($config['children']) && is_array($config['children'])) {
                                            $sourceParameter = !empty($config['short_code'])
                                                ? $ambienParametersByCode->get($config['short_code'])
                                                : null;
                                            $children = collect($config['children'])
                                                ->map(function ($childConfig) use ($ambienParametersByCode) {
                                                    $sourceParameter = !empty($childConfig['short_code'])
                                                        ? $ambienParametersByCode->get($childConfig['short_code'])
                                                        : null;
                                                    $parameter = $sourceParameter ? clone $sourceParameter : (object) ['id' => null];
                                                    $parameter->name = $childConfig['name'];
                                                    $parameter->unit = $childConfig['unit'];
                                                    $parameter->price = $childConfig['price'];
                                                    $parameter->short_code = $childConfig['short_code'] ?? null;

                                                    return $parameter;
                                                })
                                                ->values()
                                                ->all();

                                            $parameter = $sourceParameter ? clone $sourceParameter : (object) ['id' => null];
                                            $parameter->name = $config['name'];
                                            $parameter->unit = $config['unit'] ?? '';
                                            $parameter->price = $config['price'] ?? collect($config['children'])->sum('price');
                                            $parameter->short_code = $config['short_code'] ?? null;
                                            $parameter->children = $children;

                                            return $parameter;
                                        }

                                        $sourceParameter = $ambienParametersByCode->get($config['short_code']);
                                        $parameter = $sourceParameter ? clone $sourceParameter : (object) ['id' => null];
                                        $parameter->name = $config['name'];
                                        $parameter->unit = $config['unit'];
                                        $parameter->price = $config['price'];
                                        $parameter->short_code = $config['short_code'];

                                        return $parameter;
                                    })
                                    ->values();
                            };

                            $ambienKimiaRows = $mapAmbienRows($ambienKimiaMeta);

                            $renderSections = [
                                [
                                    'title' => null,
                                    'column_label' => 'Parameter Fisika',
                                    'rows' => $ambienFisikaRows,
                                ],
                                [
                                    'title' => null,
                                    'column_label' => 'Parameter Kimia',
                                    'rows' => $ambienKimiaRows,
                                ],
                            ];
                        } elseif ($category->name === 'Emisi') {
                            $emisiParametersByCode = collect($category->parameters ?? [])->keyBy('short_code');

                            $mapEmisiRows = function ($configRows) use ($emisiParametersByCode) {
                                return collect($configRows)
                                    ->map(function ($config) use ($emisiParametersByCode) {
                                        if (isset($config['children']) && is_array($config['children'])) {
                                            $sourceParameter = !empty($config['short_code'])
                                                ? $emisiParametersByCode->get($config['short_code'])
                                                : null;

                                            $children = collect($config['children'])
                                                ->map(function ($childConfig) use ($emisiParametersByCode) {
                                                    if (!empty($childConfig['short_code'])) {
                                                        $sourceParameter = $emisiParametersByCode->get($childConfig['short_code']);
                                                        if (!$sourceParameter) {
                                                            return null;
                                                        }

                                                        $parameter = clone $sourceParameter;
                                                    } else {
                                                        $parameter = (object) ['id' => null];
                                                    }

                                                    $parameter->name = $childConfig['name'];
                                                    $parameter->unit = $childConfig['unit'];
                                                    $parameter->price = $childConfig['price'];

                                                    return $parameter;
                                                })
                                                ->filter()
                                                ->values()
                                                ->all();

                                            if ($children === []) {
                                                return null;
                                            }

                                            $parameter = $sourceParameter ? clone $sourceParameter : (object) ['id' => null];
                                            $parameter->name = $config['name'];
                                            $parameter->unit = $config['unit'] ?? '';
                                            $parameter->price = $config['price'] ?? collect($config['children'])->sum('price');
                                            $parameter->hide_group_price = $config['hide_group_price'] ?? false;
                                            $parameter->hide_child_prices = $config['hide_child_prices'] ?? false;
                                            $parameter->show_children_as_info = $config['show_children_as_info'] ?? false;
                                            $parameter->info_title = $config['info_title'] ?? null;
                                            $parameter->children = $children;

                                            return $parameter;
                                        }

                                        $sourceParameter = $emisiParametersByCode->get($config['short_code']);
                                        if (!$sourceParameter) {
                                            return null;
                                        }

                                        $parameter = clone $sourceParameter;
                                        $parameter->name = $config['name'];
                                        $parameter->unit = $config['unit'];
                                        $parameter->price = $config['price'];

                                        return $parameter;
                                    })
                                    ->filter()
                                    ->values();
                            };

                            $emisiKimiaSections = [
                                [
                                    'title' => 'Emisi Sumber Tidak Bergerak',
                                    'rows' => [
                                        [
                                            'name' => 'Kadar Debu Logam (AAS)',
                                            'hide_group_price' => true,
                                            'children' => [
                                                ['name' => 'Kadar Debu Logam (AAS) - Pb', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['name' => 'Kadar Debu Logam (AAS) - Cd', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['name' => 'Kadar Debu Logam (AAS) - Cr', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['name' => 'Kadar Debu Logam (AAS) - Tl', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['name' => 'Kadar Debu Logam (AAS) - Sb', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['name' => 'Kadar Debu Logam (AAS) - Zn', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['name' => 'Kadar Debu Logam (AAS) - As', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['name' => 'Kadar Debu Logam (AAS) - Co', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['name' => 'Kadar Debu Logam (AAS) - Cu', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                                ['short_code' => 'HG', 'name' => 'Kadar Debu Logam (AAS) - Hg', 'unit' => 'per sampel per parameter', 'price' => 150000],
                                            ],
                                        ],
                                        [
                                            'short_code' => 'DEBU',
                                            'name' => 'Debu Total Partikulat dengan Isokinetik',
                                            'unit' => 'per sampel per cerobong',
                                            'price' => 1200000,
                                            'hide_child_prices' => true,
                                            'show_children_as_info' => true,
                                            'info_title' => 'Rincian Debu Total Partikulat dengan Isokinetik',
                                            'children' => [
                                                ['name' => 'Penentuan Suhu dan Tekanan, Kadar Air', 'unit' => 'Per Cerobong', 'price' => 200000],
                                                ['name' => 'Penentuan Komposisi Gas Buang (CO, CO2 dan O2)', 'unit' => 'Per Cerobong', 'price' => 450000],
                                                ['name' => 'Penentuan Laju Alir Cerobong', 'unit' => 'Per Cerobong', 'price' => 300000],
                                                ['name' => 'Perhitungan Persentase Isokinetik', 'unit' => 'Per Cerobong', 'price' => 250000],
                                            ],
                                        ],
                                        ['short_code' => 'DEBU', 'name' => 'Debu Total Partikulat', 'unit' => '3 sampel per cerobong', 'price' => 450000],
                                        ['short_code' => 'COSTB', 'name' => 'CO', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                        ['short_code' => 'CO2STB', 'name' => 'CO2', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                        ['short_code' => 'CL2', 'name' => 'CL2', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                        ['short_code' => 'H2S2', 'name' => 'H2S', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                        ['short_code' => 'HCL', 'name' => 'HCL', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                        ['short_code' => 'HF', 'name' => 'HF', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                        ['short_code' => 'NH32', 'name' => 'NH3', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                        ['short_code' => 'SO22', 'name' => 'SO2', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                        ['short_code' => 'NO22', 'name' => 'NO2', 'unit' => '3 sampel per cerobong', 'price' => 150000],
                                    ],
                                ],
                                [
                                    'title' => 'Emisi Sumber Bergerak',
                                    'rows' => [
                                        [
                                            'short_code' => 'OPASI',
                                            'name' => 'Kompresi',
                                            'unit' => 'per kendaraan',
                                            'price' => 450000,
                                            'show_children_as_info' => true,
                                            'info_title' => 'Rincian Kompresi',
                                            'children' => [
                                                ['short_code' => 'OPASI', 'name' => 'Opasitas', 'unit' => 'per kendaraan', 'price' => 450000],
                                            ],
                                        ],
                                        [
                                            'name' => 'Cetus Api',
                                            'hide_group_price' => true,
                                            'children' => [
                                                ['short_code' => 'CO', 'name' => 'CO', 'unit' => 'per kendaraan', 'price' => 400000],
                                                ['short_code' => 'HCEM', 'name' => 'HC', 'unit' => 'per kendaraan', 'price' => 400000],
                                            ],
                                        ],
                                    ],
                                ],
                            ];

                            $renderSections = collect($emisiKimiaSections)
                                ->map(function ($section) use ($mapEmisiRows) {
                                    $rows = $mapEmisiRows($section['rows']);

                                    if ($rows->isEmpty()) {
                                        return null;
                                    }

                                    return [
                                        'title' => $section['title'],
                                        'column_label' => 'Parameter Kimia',
                                        'rows' => $rows,
                                    ];
                                })
                                ->filter()
                                ->values()
                                ->all();
                        }
                    @endphp
                    <div class="service-accordion-item accordion-item border-0">
                        <h2 class="accordion-header" id="{{ $headingId }}">
                            <button class="service-accordion-toggle accordion-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#{{ $collapseId }}"
                                aria-expanded="false"
                                aria-controls="{{ $collapseId }}">
                                <span class="service-accordion-main">
                                    <span class="service-accordion-icon"><i class="{{ $iconClass }}"></i></span>
                                    <span class="service-accordion-title">{{ $category->name }}</span>
                                </span>
                                <i class="bi bi-caret-down-fill service-accordion-arrow"></i>
                            </button>
                        </h2>

                        <div id="{{ $collapseId }}"
                            class="accordion-collapse collapse"
                            aria-labelledby="{{ $headingId }}"
                            data-bs-parent="#accordionPelayanan">
                            <div class="service-accordion-panel">
                                @if(!empty($meta['description']))
                                    <div class="service-accordion-desc">{{ $meta['description'] }}</div>
                                @endif

                                @if($category->name === 'Emisi')
                                    @php
                                        $emisiPackageOptions = collect($emisiPackages ?? [])->values()->all();
                                    @endphp
                                    <div class="service-package-section" data-package-picker>
                                        <div class="service-package-header">
                                            <div>
                                                <h3 class="service-package-heading">Pilihan Paket Emisi</h3>
                                                <p class="service-package-copy">
                                                    Pilih kartu paket untuk melihat ringkasan kombinasi parameter yang umum dipakai.
                                                    Setelah itu, Anda tetap bisa membandingkan rincian parameter satuan di tabel emisi di bawah.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="service-package-grid">
                                            @foreach($emisiPackageOptions as $package)
                                                <button
                                                    type="button"
                                                    class="service-package-card"
                                                    data-package-card
                                                    data-package-title="{{ $package['title'] }}"
                                                    data-package-meta="{{ $package['subtitle'] }}"
                                                    data-package-price="{{ $package['price'] }}"
                                                    data-package-unit="{{ $package['unit'] }}"
                                                    data-package-ids="{{ implode(',', $package['parameter_ids'] ?? []) }}"
                                                    data-package-params='@json($package['parameters'])'
                                                    data-package-info='@json($package["info"] ?? null)'>
                                                    <div class="service-package-card-top">
                                                        <span class="service-package-badge">{{ $package['badge'] }}</span>
                                                        <span class="service-package-card-actions">
                                                            @if(!empty($package['info']))
                                                                <span
                                                                    class="service-package-info-trigger"
                                                                    role="button"
                                                                    tabindex="0"
                                                                    aria-label="Lihat rincian {{ $package['title'] }}"
                                                                    title="Lihat rincian {{ $package['title'] }}">
                                                                    <span aria-hidden="true">i</span>
                                                                </span>
                                                            @endif
                                                            <span class="service-package-status">Tersedia</span>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h4 class="service-package-title">{{ $package['title'] }}</h4>
                                                        <p class="service-package-subtitle">{{ $package['subtitle'] }}</p>
                                                    </div>
                                                    <div class="service-package-chip-list">
                                                        @foreach($package['parameters'] as $parameterLabel)
                                                            <span class="service-package-chip">{{ $parameterLabel }}</span>
                                                        @endforeach
                                                    </div>
                                                    <div class="service-package-footer">
                                                        <div class="service-package-price-wrap">
                                                            <span class="service-package-price-label">Tarif Paket</span>
                                                            <strong class="service-package-price">{{ $package['price'] }}</strong>
                                                            <span class="service-package-unit">{{ ucfirst($package['unit']) }}</span>
                                                        </div>
                                                        <span class="service-package-select">Pilih Paket</span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>

                                    </div>
                                @endif

                                @if(isset($renderSections) && is_array($renderSections))
                                    @foreach($renderSections as $section)
                                        <div class="service-parameter-table">
                                            @if(!empty($section['title']))
                                                <div class="service-parameter-table-title">{{ $section['title'] }}</div>
                                            @endif
                                            <div class="service-parameter-table-header">
                                                <div>{{ $section['column_label'] ?? 'Parameter' }}</div>
                                                <div>Satuan</div>
                                                <div>Tarif</div>
                                                @auth
                                                    <div></div>
                                                @endauth
                                            </div>

                                            @forelse($section['rows'] as $parameter)
                                                @if(isset($parameter->children) && is_array($parameter->children) && data_get($parameter, 'show_children_as_info', false))
                                                    @php
                                                        $parameterId = data_get($parameter, 'id');
                                                        $parameterPrice = data_get($parameter, 'price');
                                                        $parameterIds = isset($parameter->merged_ids) && is_array($parameter->merged_ids)
                                                            ? implode(',', $parameter->merged_ids)
                                                            : (string) ($parameterId ?? '');
                                                        $infoItems = collect($parameter->children)
                                                            ->map(function ($childParameter) {
                                                                $label = trim((string) ($childParameter->name ?? ''));
                                                                $unit = trim((string) ($childParameter->unit ?? ''));
                                                                $price = data_get($childParameter, 'price');
                                                                $parts = array_values(array_filter([
                                                                    $label,
                                                                    $unit,
                                                                    !is_null($price) ? 'Rp. ' . number_format((float) $price, 0, ',', '.') . ',-' : null,
                                                                ], fn ($value) => $value !== null && $value !== ''));

                                                                return implode(' - ', $parts);
                                                            })
                                                            ->filter()
                                                            ->values()
                                                            ->all();
                                                        $infoPayload = [
                                                            'title' => data_get($parameter, 'info_title', $parameter->name),
                                                            'items' => $infoItems,
                                                        ];
                                                    @endphp
                                                    <div class="service-parameter-table-row">
                                                        <div class="service-parameter-name">
                                                            <span class="service-parameter-name-wrap">
                                                                <span>{{ $parameter->name }}</span>
                                                                @if($infoItems !== [])
                                                                    <span
                                                                        class="service-parameter-info-trigger"
                                                                        role="button"
                                                                        tabindex="0"
                                                                        data-info-trigger
                                                                        data-info-payload='@json($infoPayload)'
                                                                        aria-label="Lihat rincian {{ $parameter->name }}"
                                                                        title="Lihat rincian {{ $parameter->name }}">
                                                                        <span aria-hidden="true">i</span>
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="service-parameter-unit">{{ $parameter->unit ?? '' }}</div>
                                                        <div class="service-parameter-price">
                                                            @if(!is_null($parameterPrice))
                                                                Rp. {{ number_format($parameterPrice, 0, ',', '.') }},-
                                                            @endif
                                                        </div>
                                                        @auth
                                                            @if(!empty($parameterId) || !empty($parameterIds))
                                                            <button class="service-order-btn add-to-cart-btn"
                                                                data-id="{{ $parameterId }}"
                                                                data-ids="{{ $parameterIds }}"
                                                                data-name="{{ $parameter->name }}"
                                                                data-price="{{ $parameterPrice }}"
                                                                aria-label="Pesan {{ $parameter->name }}"
                                                                title="Pesan {{ $parameter->name }}">
                                                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                                                <span class="visually-hidden">Pesan</span>
                                                            </button>
                                                            @endif
                                                        @endauth
                                                    </div>
                                                @elseif(isset($parameter->children) && is_array($parameter->children))
                                                    <div class="service-parameter-table-group">
                                                        @php
                                                            $parameterId = data_get($parameter, 'id');
                                                            $parameterPrice = data_get($parameter, 'price');
                                                            $showGroupPrice = !data_get($parameter, 'hide_group_price', false) && !is_null($parameterPrice);
                                                            $hideChildPrices = data_get($parameter, 'hide_child_prices', false);
                                                            $parameterIds = isset($parameter->merged_ids) && is_array($parameter->merged_ids)
                                                                ? implode(',', $parameter->merged_ids)
                                                                : (string) ($parameterId ?? '');
                                                            $hasGroupAction = $showGroupPrice || !empty($parameterId) || !empty($parameterIds);
                                                        @endphp
                                                        <div class="service-parameter-table-row service-parameter-table-group-header">
                                                            <div class="service-parameter-name service-parameter-table-group-title">{{ $parameter->name }}</div>
                                                            <div class="service-parameter-unit">{{ $parameter->unit ?? '' }}</div>
                                                            <div class="service-parameter-price">
                                                                @if($showGroupPrice)
                                                                    Rp. {{ number_format($parameterPrice, 0, ',', '.') }},-
                                                                @endif
                                                            </div>
                                                            @auth
                                                                @if(!empty($parameterId) || !empty($parameterIds))
                                                                <button class="service-order-btn add-to-cart-btn"
                                                                    data-id="{{ $parameterId }}"
                                                                    data-ids="{{ $parameterIds }}"
                                                                    data-name="{{ $parameter->name }}"
                                                                    data-price="{{ $parameterPrice }}"
                                                                    aria-label="Pesan {{ $parameter->name }}"
                                                                    title="Pesan {{ $parameter->name }}">
                                                                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                                                    <span class="visually-hidden">Pesan</span>
                                                                </button>
                                                                @endif
                                                            @endauth
                                                        </div>
                                                        @foreach($parameter->children as $childParameter)
                                                            @php
                                                                $parameterIds = isset($childParameter->merged_ids) && is_array($childParameter->merged_ids)
                                                                    ? implode(',', $childParameter->merged_ids)
                                                                    : (string) $childParameter->id;
                                                            @endphp
                                                            <div class="service-parameter-table-row service-parameter-table-subrow">
                                                                <div class="service-parameter-name">{{ $childParameter->name }}</div>
                                                                <div class="service-parameter-unit">{{ $childParameter->unit }}</div>
                                                                <div class="service-parameter-price">
                                                                    @unless($hideChildPrices)
                                                                        Rp. {{ number_format($childParameter->price, 0, ',', '.') }},-
                                                                    @endunless
                                                                </div>
                                                                @auth
                                                                    @if(!empty($childParameter->id) || !empty($parameterIds))
                                                                    <button class="service-order-btn add-to-cart-btn"
                                                                        data-id="{{ $childParameter->id }}"
                                                                        data-ids="{{ $parameterIds }}"
                                                                        data-name="{{ $childParameter->name }}"
                                                                        data-price="{{ $childParameter->price }}"
                                                                        aria-label="Pesan {{ $childParameter->name }}"
                                                                        title="Pesan {{ $childParameter->name }}">
                                                                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                                                        <span class="visually-hidden">Pesan</span>
                                                                    </button>
                                                                    @endif
                                                                @endauth
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    @php
                                                        $parameterIds = isset($parameter->merged_ids) && is_array($parameter->merged_ids)
                                                            ? implode(',', $parameter->merged_ids)
                                                            : (string) $parameter->id;
                                                    @endphp
                                                    <div class="service-parameter-table-row">
                                                        <div class="service-parameter-name">{{ $parameter->name }}</div>
                                                        <div class="service-parameter-unit">{{ $parameter->unit }}</div>
                                                        <div class="service-parameter-price">Rp. {{ number_format($parameter->price, 0, ',', '.') }},-</div>
                                                        @auth
                                                            @if(!empty($parameter->id) || !empty($parameterIds))
                                                            <button class="service-order-btn add-to-cart-btn"
                                                                data-id="{{ $parameter->id }}"
                                                                data-ids="{{ $parameterIds }}"
                                                                data-name="{{ $parameter->name }}"
                                                                data-price="{{ $parameter->price }}"
                                                                aria-label="Pesan {{ $parameter->name }}"
                                                                title="Pesan {{ $parameter->name }}">
                                                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                                                <span class="visually-hidden">Pesan</span>
                                                            </button>
                                                            @endif
                                                        @endauth
                                                    </div>
                                                @endif
                                            @empty
                                                <div class="service-empty">Belum ada parameter</div>
                                            @endforelse
                                        </div>
                                    @endforeach
                                @else
                                    @forelse($category->parameters as $parameter)
                                        @if(isset($parameter->children) && is_array($parameter->children))
                                            <div class="service-parameter-group">
                                                <div class="service-parameter-group-title">{{ $parameter->name }}</div>
                                                @foreach($parameter->children as $childParameter)
                                                    @php
                                                        $parameterIds = isset($childParameter->merged_ids) && is_array($childParameter->merged_ids)
                                                            ? implode(',', $childParameter->merged_ids)
                                                            : (string) $childParameter->id;

                                                        $childLabel = $childParameter->name;
                                                        if (str_starts_with($childLabel, $parameter->name . ' - ')) {
                                                            $childLabel = substr($childLabel, strlen($parameter->name . ' - '));
                                                        }
                                                    @endphp
                                                    <div class="service-parameter-row service-parameter-subrow">
                                                        <div class="service-parameter-name">{{ $childLabel }}</div>
                                                        <div class="service-parameter-price">Rp. {{ number_format($childParameter->price, 0, ',', '.') }},-</div>
                                                        @auth
                                                            @if(!empty($childParameter->id) || !empty($parameterIds))
                                                            <button class="service-order-btn add-to-cart-btn"
                                                                data-id="{{ $childParameter->id }}"
                                                                data-ids="{{ $parameterIds }}"
                                                                data-name="{{ $childParameter->name }}"
                                                                data-price="{{ $childParameter->price }}"
                                                                aria-label="Pesan {{ $childParameter->name }}"
                                                                title="Pesan {{ $childParameter->name }}">
                                                                <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                                                <span class="visually-hidden">Pesan</span>
                                                            </button>
                                                            @endif
                                                        @endauth
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            @php
                                                $parameterIds = isset($parameter->merged_ids) && is_array($parameter->merged_ids)
                                                    ? implode(',', $parameter->merged_ids)
                                                    : (string) $parameter->id;
                                            @endphp
                                            <div class="service-parameter-row">
                                                <div class="service-parameter-name">{{ $parameter->name }}</div>
                                                <div class="service-parameter-price">Rp. {{ number_format($parameter->price, 0, ',', '.') }},-</div>
                                                @auth
                                                    @if(!empty($parameter->id) || !empty($parameterIds))
                                                    <button class="service-order-btn add-to-cart-btn"
                                                        data-id="{{ $parameter->id }}"
                                                        data-ids="{{ $parameterIds }}"
                                                        data-name="{{ $parameter->name }}"
                                                        data-price="{{ $parameter->price }}"
                                                        aria-label="Pesan {{ $parameter->name }}"
                                                        title="Pesan {{ $parameter->name }}">
                                                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                                                        <span class="visually-hidden">Pesan</span>
                                                    </button>
                                                    @endif
                                                @endauth
                                            </div>
                                        @endif
                                    @empty
                                        <div class="service-empty">Belum ada parameter</div>
                                    @endforelse
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted">Belum ada kategori pelayanan.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>

<script>
    const isAuthenticated = @json(auth()->check());
    const loginUrl = @json(route('login'));
    const cartBadge = document.getElementById('cartBadge');
    const cartButton = document.getElementById('cartButton');

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    function setBadge(count) {
        if (!cartBadge) {
            return;
        }

        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'flex' : 'none';
    }

    function bumpCart() {
        if (!cartButton) {
            return;
        }

        cartButton.classList.remove('cart-bump');
        void cartButton.offsetWidth;
        cartButton.classList.add('cart-bump');
    }

    function initMobileCartDrag() {
        if (!cartButton) {
            return;
        }

        const mobileCartMedia = window.matchMedia('(max-width: 991.98px)');
        const mainNavbar = document.getElementById('mainNavbar');
        const cartScreenPadding = 12;
        const dragThreshold = 16;
        let activePointerId = null;
        let startX = 0;
        let startY = 0;
        let startLeft = 0;
        let startTop = 0;
        let moved = false;
        let suppressNextClick = false;

        const getMinLeft = () => cartScreenPadding;
        const getMaxLeft = () => Math.max(getMinLeft(), window.innerWidth - cartButton.offsetWidth - cartScreenPadding);
        const getMinTop = () => {
            const viewportQuarter = window.innerHeight * 0.25;
            const navbarBottom = mainNavbar ? mainNavbar.getBoundingClientRect().bottom : 0;
            return Math.max(cartScreenPadding, Math.ceil(viewportQuarter), Math.ceil(navbarBottom + cartScreenPadding));
        };
        const getMaxTop = () => Math.max(getMinTop(), window.innerHeight - cartButton.offsetHeight - cartScreenPadding);

        const clampLeft = (value) => Math.min(getMaxLeft(), Math.max(getMinLeft(), value));
        const clampTop = (value) => Math.min(getMaxTop(), Math.max(getMinTop(), value));

        const resolveSnapLeft = (left) => {
            const buttonCenter = left + (cartButton.offsetWidth / 2);
            return buttonCenter < (window.innerWidth / 2) ? getMinLeft() : getMaxLeft();
        };

        const applyMobilePosition = (left = null, top = null) => {
            if (!mobileCartMedia.matches) {
                cartButton.classList.remove('is-draggable', 'is-dragging', 'is-snapping');
                cartButton.style.left = '';
                cartButton.style.top = '';
                cartButton.style.right = '';
                cartButton.style.bottom = '';
                cartButton.dataset.cartLeft = '';
                cartButton.dataset.cartTop = '';
                cartButton.dataset.cartSide = '';
                return;
            }

            const rect = cartButton.getBoundingClientRect();
            const fallbackLeft = rect.left;
            const fallbackTop = rect.top;
            const nextLeft = clampLeft(left ?? Number(cartButton.dataset.cartLeft || fallbackLeft));
            const nextTop = clampTop(top ?? Number(cartButton.dataset.cartTop || fallbackTop));
            cartButton.classList.add('is-draggable');
            cartButton.style.left = `${nextLeft}px`;
            cartButton.style.top = `${nextTop}px`;
            cartButton.style.right = 'auto';
            cartButton.style.bottom = 'auto';
            cartButton.dataset.cartLeft = String(nextLeft);
            cartButton.dataset.cartTop = String(nextTop);
        };

        const finishDrag = () => {
            cartButton.classList.remove('is-dragging');
            activePointerId = null;
            startX = 0;
            startY = 0;
            startTop = 0;
        };

        const snapToSide = (left, top) => {
            const targetLeft = resolveSnapLeft(left);
            const targetTop = clampTop(top);
            cartButton.classList.add('is-snapping');
            cartButton.dataset.cartSide = targetLeft === getMinLeft() ? 'left' : 'right';
            applyMobilePosition(targetLeft, targetTop);

            window.setTimeout(() => {
                cartButton.classList.remove('is-snapping');
            }, 180);
        };

        cartButton.addEventListener('pointerdown', (event) => {
            if (!mobileCartMedia.matches || (event.pointerType === 'mouse' && event.button !== 0)) {
                return;
            }

            activePointerId = event.pointerId;
            startX = event.clientX;
            startY = event.clientY;
            cartButton.classList.remove('is-snapping');
            startLeft = Number(cartButton.dataset.cartLeft || cartButton.getBoundingClientRect().left);
            startTop = Number(cartButton.dataset.cartTop || cartButton.getBoundingClientRect().top);
            moved = false;
            cartButton.setPointerCapture(event.pointerId);
        });

        cartButton.addEventListener('pointermove', (event) => {
            if (event.pointerId !== activePointerId || !mobileCartMedia.matches) {
                return;
            }

            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;

            if (!moved) {
                if (Math.hypot(deltaX, deltaY) < dragThreshold) {
                    return;
                }

                moved = true;
                cartButton.classList.add('is-dragging');
            }

            cartButton.style.left = `${clampLeft(startLeft + deltaX)}px`;
            cartButton.style.top = `${clampTop(startTop + deltaY)}px`;
            cartButton.dataset.cartLeft = cartButton.style.left.replace('px', '');
            cartButton.dataset.cartTop = cartButton.style.top.replace('px', '');
            event.preventDefault();
        });

        cartButton.addEventListener('pointerup', (event) => {
            if (event.pointerId !== activePointerId) {
                return;
            }

            if (cartButton.hasPointerCapture(event.pointerId)) {
                cartButton.releasePointerCapture(event.pointerId);
            }

            suppressNextClick = moved;
            window.setTimeout(() => {
                suppressNextClick = false;
            }, 250);

            if (moved) {
                snapToSide(
                    Number(cartButton.dataset.cartLeft || cartButton.getBoundingClientRect().left),
                    Number(cartButton.dataset.cartTop || cartButton.getBoundingClientRect().top)
                );
            }

            finishDrag();
        });

        cartButton.addEventListener('pointercancel', (event) => {
            if (event.pointerId !== activePointerId) {
                return;
            }

            if (cartButton.hasPointerCapture(event.pointerId)) {
                cartButton.releasePointerCapture(event.pointerId);
            }

            if (moved) {
                snapToSide(
                    Number(cartButton.dataset.cartLeft || cartButton.getBoundingClientRect().left),
                    Number(cartButton.dataset.cartTop || cartButton.getBoundingClientRect().top)
                );
            }

            finishDrag();
        });

        cartButton.addEventListener('click', (event) => {
            if (!suppressNextClick) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
        });

        const syncCartButtonBounds = () => {
            if (!mobileCartMedia.matches) {
                applyMobilePosition();
                return;
            }

            const currentLeft = Number(cartButton.dataset.cartLeft || cartButton.getBoundingClientRect().left);
            const currentTop = Number(cartButton.dataset.cartTop || cartButton.getBoundingClientRect().top);
            const side = cartButton.dataset.cartSide;
            const snappedLeft = side === 'left'
                ? getMinLeft()
                : side === 'right'
                    ? getMaxLeft()
                    : resolveSnapLeft(currentLeft);

            cartButton.dataset.cartSide = snappedLeft === getMinLeft() ? 'left' : 'right';
            applyMobilePosition(snappedLeft, currentTop);
        };

        mobileCartMedia.addEventListener('change', syncCartButtonBounds);
        window.addEventListener('resize', syncCartButtonBounds, { passive: true });
        window.requestAnimationFrame(syncCartButtonBounds);
    }

    async function fetchCartCount() {
        const response = await fetch('/cart', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            setBadge(0);
            return;
        }

        const data = await response.json();
        setBadge(data.count || 0);
    }

    async function addToCart(serviceParameterId, button) {
        const response = await fetch('/cart/items', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ service_parameter_id: serviceParameterId }),
        });

        if (response.status === 401) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Perlu login',
                    text: 'Silakan login untuk menambahkan layanan.',
                    icon: 'info',
                    confirmButtonText: 'Login'
                }).then(() => {
                    window.location.href = loginUrl;
                });
            } else {
                window.location.href = loginUrl;
            }
            return;
        }

        if (!response.ok) {
            let message = 'Gagal menambahkan paket ke keranjang.';
            try {
                const payload = await response.json();
                if (payload?.message) {
                    message = payload.message;
                }
            } catch (error) {
                // ignore parse errors and keep default message
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Gagal',
                    text: message,
                    icon: 'error',
                    confirmButtonText: 'Tutup'
                });
            }
            return;
        }

        const data = await response.json();
        setBadge(data.count || 0);

        button.classList.remove('btn-bump');
        void button.offsetWidth;
        button.classList.add('btn-bump');
        bumpCart();
    }

    async function addMultipleToCart(serviceParameterIds, button) {
        let latestCount = null;

        for (const serviceParameterId of serviceParameterIds) {
            const response = await fetch('/cart/items', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({ service_parameter_id: serviceParameterId }),
            });

            if (response.status === 401) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Perlu login',
                        text: 'Silakan login untuk menambahkan layanan.',
                        icon: 'info',
                        confirmButtonText: 'Login'
                    }).then(() => {
                        window.location.href = loginUrl;
                    });
                } else {
                    window.location.href = loginUrl;
                }
                return;
            }

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            latestCount = data.count || 0;
        }

        if (latestCount !== null) {
            setBadge(latestCount);
        }

        button.classList.remove('btn-bump');
        void button.offsetWidth;
        button.classList.add('btn-bump');
        bumpCart();
    }

    function parseRupiahToNumber(value) {
        const normalized = String(value || '')
            .replace(/[^0-9,]/g, '')
            .replace(/\./g, '')
            .replace(',', '.');
        const parsed = Number.parseFloat(normalized);
        return Number.isFinite(parsed) ? parsed : 0;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showInfoModal(info) {
        if (!info || !Array.isArray(info.items) || info.items.length === 0 || typeof Swal === 'undefined') {
            return;
        }

        const infoHtml = `<div style="text-align:left;"><ul style="margin:0;padding-left:18px;">${info.items
            .map((item) => `<li style="margin:0 0 6px;">${escapeHtml(item)}</li>`)
            .join('')}</ul></div>`;

        Swal.fire({
            title: info.title || 'Rincian',
            html: infoHtml,
            width: 560,
            padding: '1.125rem',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                popup: 'swal-package-info-popup',
                title: 'swal-package-info-title',
                htmlContainer: 'swal-package-info-html',
                confirmButton: 'swal-package-info-confirm',
                closeButton: 'swal-package-info-close',
            }
        });
    }

    async function addPackageToCart(packagePayload, button) {
        const response = await fetch('/cart/packages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(packagePayload),
        });

        if (response.status === 401) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Perlu login',
                    text: 'Silakan login untuk menambahkan layanan.',
                    icon: 'info',
                    confirmButtonText: 'Login'
                }).then(() => {
                    window.location.href = loginUrl;
                });
            } else {
                window.location.href = loginUrl;
            }
            return;
        }

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        setBadge(data.count || 0);

        button.classList.remove('btn-bump');
        void button.offsetWidth;
        button.classList.add('btn-bump');
        bumpCart();
    }

    document.querySelectorAll('.add-to-cart-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (!isAuthenticated) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Perlu login',
                        text: 'Silakan login untuk menambahkan layanan.',
                        icon: 'info',
                        confirmButtonText: 'Login'
                    }).then(() => {
                        window.location.href = loginUrl;
                    });
                } else {
                    window.location.href = loginUrl;
                }
                return;
            }

            const ids = (btn.getAttribute('data-ids') || btn.getAttribute('data-id') || '')
                .split(',')
                .map((value) => parseInt(value.trim(), 10))
                .filter((value) => Number.isInteger(value) && value > 0);

            if (ids.length === 0) {
                return;
            }

            if (ids.length === 1) {
                addToCart(ids[0], btn);
                return;
            }

            addMultipleToCart(ids, btn);
        });
    });

    document.querySelectorAll('[data-package-picker]').forEach((picker) => {
        const cards = Array.from(picker.querySelectorAll('[data-package-card]'));
        const resolvePackageIds = (card) => (card?.dataset.packageIds || '')
            .split(',')
            .map((value) => parseInt(value.trim(), 10))
            .filter((value) => Number.isInteger(value) && value > 0);

        const setActiveCard = (card) => {
            cards.forEach((item) => item.classList.toggle('is-active', item === card));
        };

        cards.forEach((card) => {
            card.addEventListener('click', () => {
                setActiveCard(card);
            });

            const infoTrigger = card.querySelector('.service-package-info-trigger');
            const showPackageInfo = () => {
                let info = null;

                try {
                    info = JSON.parse(card.dataset.packageInfo || 'null');
                } catch (error) {
                    info = null;
                }

                showInfoModal(info);
            };

            infoTrigger?.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                showPackageInfo();
            });

            infoTrigger?.addEventListener('keydown', (event) => {
                if (event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                showPackageInfo();
            });

            const selectTrigger = card.querySelector('.service-package-select');
            selectTrigger?.addEventListener('click', async (event) => {
                event.preventDefault();
                event.stopPropagation();
                setActiveCard(card);

                if (!isAuthenticated) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Perlu login',
                            text: 'Silakan login untuk menambahkan layanan.',
                            icon: 'info',
                            confirmButtonText: 'Login'
                        }).then(() => {
                            window.location.href = loginUrl;
                        });
                    } else {
                        window.location.href = loginUrl;
                    }
                    return;
                }

                const ids = resolvePackageIds(card);
                if (ids.length === 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Paket belum tersedia',
                            text: 'Rincian parameter paket ini belum terhubung ke data parameter.',
                            icon: 'warning',
                            confirmButtonText: 'Tutup'
                        });
                    }
                    return;
                }

                const packagePayload = {
                    package_name: card.dataset.packageTitle || 'Paket Emisi',
                    package_price: parseRupiahToNumber(card.dataset.packagePrice || '0'),
                    package_details: JSON.parse(card.dataset.packageParams || '[]'),
                    service_parameter_ids: ids,
                };

                await addPackageToCart(packagePayload, selectTrigger);
            });
        });

        if (cards[0]) {
            setActiveCard(cards[0]);
        }
    });

    document.querySelectorAll('[data-info-trigger]').forEach((trigger) => {
        const openInfo = () => {
            let info = null;

            try {
                info = JSON.parse(trigger.dataset.infoPayload || 'null');
            } catch (error) {
                info = null;
            }

            showInfoModal(info);
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            openInfo();
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            openInfo();
        });
    });

    initMobileCartDrag();

    const mobileCategoryMedia = window.matchMedia('(max-width: 575.98px)');
    const categoryGrid = document.querySelector('.service-category-grid');
    const categoryMarquee = categoryGrid ? categoryGrid.querySelector('.service-category-marquee') : null;
    const primaryCategoryTrack = categoryGrid ? categoryGrid.querySelector('.service-category-track:not([aria-hidden="true"])') : null;
    const categoryCards = primaryCategoryTrack ? Array.from(primaryCategoryTrack.querySelectorAll('.service-category-card')) : [];

    const bindCategoryCard = (button) => {
        const releasePressedState = () => {
            window.setTimeout(() => {
                button.classList.remove('service-card-pressed');
            }, 180);
        };

        button.addEventListener('pointerdown', () => {
            if (!mobileCategoryMedia.matches) {
                return;
            }

            button.classList.add('service-card-pressed');
        });

        button.addEventListener('pointerup', releasePressedState);
        button.addEventListener('pointercancel', releasePressedState);
        button.addEventListener('pointerleave', releasePressedState);

        button.addEventListener('click', () => {
            if (mobileCategoryMedia.matches) {
                button.classList.add('service-card-pressed');
                releasePressedState();
            }

            const targetId = button.getAttribute('data-service-target');
            const target = document.getElementById(targetId);
            if (!target) {
                return;
            }

            const collapse = bootstrap.Collapse.getOrCreateInstance(target, { toggle: false });
            collapse.show();

            setTimeout(() => {
                const top = target.previousElementSibling?.getBoundingClientRect().top ?? 0;
                window.scrollTo({
                    top: window.scrollY + top - 120,
                    behavior: 'smooth'
                });
            }, 180);
        });
    };

    document.querySelectorAll('[data-service-target]').forEach(bindCategoryCard);

    function updateCategoryMarqueeMetrics() {
        if (!categoryGrid || !primaryCategoryTrack || !categoryCards.length) {
            return;
        }

        if (mobileCategoryMedia.matches) {
            categoryGrid.style.removeProperty('--category-loop-distance');
            categoryGrid.classList.remove('is-paused');
            return;
        }

        const gridWidth = categoryGrid.clientWidth;
        const gapValue = Number.parseFloat(window.getComputedStyle(categoryGrid).getPropertyValue('--category-gap')) || 10;
        const columns = window.matchMedia('(max-width: 991.98px)').matches
            ? 2
            : window.matchMedia('(max-width: 1199.98px)').matches
                ? 3
                : 5;
        const cardWidth = (gridWidth - (gapValue * (columns - 1))) / columns;

        categoryGrid.style.setProperty('--category-card-width', `${cardWidth}px`);
        categoryGrid.style.setProperty('--category-loop-distance', `${primaryCategoryTrack.scrollWidth + gapValue}px`);
    }

    function syncMobileCategoryFocus() {
        if (!mobileCategoryMedia.matches || !categoryGrid || !categoryCards.length) {
            categoryCards.forEach((card) => card.classList.remove('service-card-active'));
            return;
        }

        const gridRect = categoryGrid.getBoundingClientRect();
        const gridCenter = gridRect.left + (gridRect.width / 2);
        let activeCard = null;
        let nearestDistance = Number.POSITIVE_INFINITY;

        categoryCards.forEach((card) => {
            const rect = card.getBoundingClientRect();
            const cardCenter = rect.left + (rect.width / 2);
            const distance = Math.abs(cardCenter - gridCenter);

            if (distance < nearestDistance) {
                nearestDistance = distance;
                activeCard = card;
            }
        });

        categoryCards.forEach((card) => {
            card.classList.toggle('service-card-active', card === activeCard);
        });
    }

    if (categoryGrid && categoryCards.length) {
        let focusTicking = false;
        let scrollStopTimer = null;

        const clearMobileCategoryFocus = () => {
            categoryCards.forEach((card) => card.classList.remove('service-card-active'));
        };

        const requestCategoryFocusSync = () => {
            if (focusTicking) {
                return;
            }

            focusTicking = true;
            window.requestAnimationFrame(() => {
                syncMobileCategoryFocus();
                focusTicking = false;
            });

            if (scrollStopTimer) {
                window.clearTimeout(scrollStopTimer);
            }

            scrollStopTimer = window.setTimeout(() => {
                if (mobileCategoryMedia.matches) {
                    clearMobileCategoryFocus();
                }
            }, 140);
        };

        categoryGrid.addEventListener('mouseenter', () => {
            if (!mobileCategoryMedia.matches) {
                categoryGrid.classList.add('is-paused');
            }
        });
        categoryGrid.addEventListener('mouseleave', () => {
            categoryGrid.classList.remove('is-paused');
        });
        categoryGrid.addEventListener('scroll', requestCategoryFocusSync, { passive: true });
        window.addEventListener('resize', () => {
            updateCategoryMarqueeMetrics();
            requestCategoryFocusSync();
        });
        mobileCategoryMedia.addEventListener('change', () => {
            if (!mobileCategoryMedia.matches) {
                clearMobileCategoryFocus();
            }

            updateCategoryMarqueeMetrics();
            requestCategoryFocusSync();
        });
        updateCategoryMarqueeMetrics();
        requestCategoryFocusSync();
    }

    if (isAuthenticated) {
        const scheduleCartCountFetch = () => {
            if ('requestIdleCallback' in window) {
                window.requestIdleCallback(fetchCartCount, { timeout: 1200 });
                return;
            }

            window.setTimeout(fetchCartCount, 500);
        };

        if (document.readyState === 'complete') {
            scheduleCartCountFetch();
        } else {
            window.addEventListener('load', scheduleCartCountFetch, { once: true });
        }
    } else {
        setBadge(0);
    }
</script>
@endsection
