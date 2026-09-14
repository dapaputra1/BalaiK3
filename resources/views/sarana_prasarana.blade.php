@extends('layouts.app')

@section('content')
<style>
    html,
    html body,
    body {
        margin: 0 !important;
        padding: 0 !important;
    }

    html {
        background: #35597f !important;
    }

    body {
        background: linear-gradient(180deg, #35597f 0 520px, #ffffff 520px 100%) !important;
        background-attachment: scroll !important;
    }

    body::before {
        display: none !important;
    }

    .sarana-page {
        background: transparent;
        font-family: 'Poppins', sans-serif;
    }

    .sarana-hero {
        position: relative;
        min-height: 390px;
        overflow: hidden;
        background:
            linear-gradient(180deg, rgba(22, 58, 96, 0.46) 0%, rgba(20, 54, 87, 0.54) 100%),
            url('{{ asset('images/header2.jpg') }}') center -32px/cover no-repeat;
    }

    .sarana-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.08) 0, rgba(255, 255, 255, 0) 90px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.04) 0, rgba(255, 255, 255, 0) 62%);
        pointer-events: none;
    }

    .sarana-hero::after {
        content: "";
        position: absolute;
        left: -4%;
        right: -4%;
        bottom: 4px;
        height: 86px;
        background:
            radial-gradient(160% 120% at 10% 100%, rgba(93, 183, 155, 0.78) 0 31%, transparent 32%),
            radial-gradient(130% 100% at 35% 100%, rgba(184, 226, 197, 0.93) 0 34%, transparent 35%),
            radial-gradient(128% 96% at 68% 100%, rgba(145, 215, 184, 0.82) 0 31%, transparent 32%),
            radial-gradient(155% 120% at 92% 100%, rgba(201, 238, 211, 0.97) 0 28%, transparent 29%);
        opacity: 0.98;
        pointer-events: none;
    }

    .sarana-hero-ribbon {
        position: absolute;
        left: -2%;
        right: -2%;
        bottom: 42px;
        height: 42px;
        background: linear-gradient(90deg, rgba(83, 180, 151, 0.78), rgba(214, 244, 219, 0.95));
        border-top: 2px solid rgba(255, 255, 255, 0.52);
        border-bottom: 2px solid rgba(96, 190, 158, 0.38);
        transform: rotate(2.8deg);
        opacity: 0.9;
        pointer-events: none;
    }

    .sarana-hero-ribbon.is-back {
        bottom: 28px;
        height: 26px;
        background: linear-gradient(90deg, rgba(92, 192, 163, 0.65), rgba(163, 223, 191, 0.92));
        transform: rotate(-2.2deg);
    }

    .sarana-hero-inner {
        position: relative;
        z-index: 2;
        max-width: 1180px;
        margin: 0 auto;
        min-height: 390px;
        display: flex;
        align-items: flex-start;
        padding: 22px 24px 72px;
    }

    .sarana-hero-copy {
        max-width: 560px;
        color: #ffffff;
        text-shadow: 0 4px 12px rgba(0, 0, 0, 0.26);
    }

    .sarana-breadcrumb {
        margin: 0 0 20px;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.35;
        color: rgba(255, 255, 255, 0.96);
    }

    .sarana-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .sarana-title {
        margin: 0;
        font-size: 30px;
        font-weight: 700;
        line-height: 0.98;
        letter-spacing: 0.01em;
    }

    .sarana-title-line {
        width: 88px;
        height: 5px;
        margin-top: 16px;
        border-radius: 999px;
        background: #3fd1a5;
        box-shadow: 0 2px 10px rgba(63, 209, 165, 0.38);
    }

    .sarana-content {
        position: relative;
        z-index: 3;
        padding: 88px 20px 124px;
        background: #ffffff;
    }

    .sarana-list {
        width: min(100%, 980px);
        margin: 0 auto;
        display: grid;
        gap: 20px;
    }

    .sarana-accordion-item {
        width: 100%;
    }

    .sarana-item {
        width: 100%;
        min-height: 58px;
        border: 0;
        border-radius: 16px;
        background: #15406a;
        box-shadow: 0 6px 0 rgba(21, 64, 106, 0.18);
        padding: 0 22px 0 26px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-align: left;
        cursor: pointer;
        transition: border-radius 0.2s ease;
    }

    .sarana-item-main {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .sarana-item-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #ffffff;
        color: #214f80;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sarana-item-icon i {
        font-size: 20px;
        line-height: 1;
    }

    .sarana-item-title {
        color: #ffffff;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.12);
    }

    .sarana-item-arrow {
        color: #ffffff;
        font-size: 18px;
        line-height: 1;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .sarana-panel {
        background: #ffffff;
        margin-top: -4px;
        border-radius: 0 0 20px 20px;
        box-shadow:
            0 18px 30px rgba(18, 65, 106, 0.22),
            0 0 0 2px rgba(130, 218, 210, 0.32),
            0 0 26px rgba(88, 196, 184, 0.36);
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        visibility: hidden;
        transition:
            max-height 0.36s ease,
            opacity 0.24s ease,
            visibility 0.24s ease;
    }

    .sarana-panel-inner {
        padding: 26px 38px 30px 38px;
    }

    .sarana-accordion-item.is-open .sarana-item {
        border-radius: 16px 16px 0 0;
    }

    .sarana-accordion-item.is-open .sarana-item-arrow {
        transform: rotate(180deg);
    }

    .sarana-accordion-item.is-open .sarana-panel {
        opacity: 1;
        visibility: visible;
    }

    .sarana-panel p,
    .sarana-panel li {
        margin: 0;
        color: #111111;
        font-size: 12px;
        line-height: 1.9;
    }

    .sarana-panel p + p {
        margin-top: 14px;
    }

    .sarana-panel strong {
        font-weight: 700;
    }

    .sarana-panel ul {
        margin: 14px 0 0;
        padding: 0 0 0 42px;
        list-style: none;
    }

    .sarana-panel li + li {
        margin-top: 8px;
    }

    @media (max-width: 991.98px) {
        .sarana-hero {
            min-height: 360px;
            background-position: center -24px;
            background-size: cover;
        }

        .sarana-hero-inner {
            min-height: 360px;
            align-items: flex-start;
            padding: 20px 20px 62px;
        }

        .sarana-breadcrumb {
            font-size: 15px;
        }

        .sarana-title {
            font-size: 30px;
        }

        .sarana-content {
            padding: 80px 18px 96px;
        }

        .sarana-list {
            width: min(100%, 860px);
            gap: 18px;
        }

        .sarana-item {
            min-height: 54px;
            padding: 0 20px 0 22px;
        }

        .sarana-item-main {
            gap: 14px;
        }

        .sarana-item-title {
            font-size: 13px;
        }

        .sarana-panel-inner {
            padding: 22px 28px 26px;
        }
    }

    @media (max-width: 575.98px) {
        .sarana-hero {
            min-height: 300px;
            background-position: center -16px;
            background-size: cover;
        }

        .sarana-hero-inner {
            min-height: 300px;
            align-items: flex-start;
            padding: 18px 16px 46px;
        }

        .sarana-breadcrumb {
            margin-bottom: 16px;
            font-size: 15px;
        }

        .sarana-title {
            font-size: 30px;
        }

        .sarana-title-line {
            width: 74px;
            margin-top: 12px;
        }

        .sarana-hero-ribbon {
            bottom: 28px;
            height: 24px;
        }

        .sarana-hero-ribbon.is-back {
            bottom: 18px;
            height: 16px;
        }

        .sarana-content {
            padding: 56px 14px 76px;
        }

        .sarana-list {
            width: min(100%, 620px);
            gap: 14px;
        }

        .sarana-item {
            min-height: 52px;
            padding: 0 16px;
            border-radius: 14px;
        }

        .sarana-item-main {
            gap: 12px;
        }

        .sarana-item-icon {
            width: 34px;
            height: 34px;
        }

        .sarana-item-icon i {
            font-size: 17px;
        }

        .sarana-item-title {
            font-size: 12px;
        }

        .sarana-item-arrow {
            font-size: 16px;
        }

        .sarana-panel-inner {
            padding: 18px 20px 22px;
            border-radius: 0 0 16px 16px;
        }

        .sarana-panel ul {
            padding-left: 22px;
        }

        .sarana-panel p,
        .sarana-panel li {
            font-size: 11px;
            line-height: 1.8;
        }
    }
</style>

<div class="sarana-page">
    <section class="sarana-hero">
        <div class="sarana-hero-inner">
            <div class="sarana-hero-copy">
                <p class="sarana-breadcrumb"><a href="{{ route('home') }}">Beranda</a> / Sarana dan Prasarana</p>
                <h1 class="sarana-title">Sarana dan Prasarana</h1>
                <div class="sarana-title-line"></div>
            </div>
        </div>
        <div class="sarana-hero-ribbon"></div>
        <div class="sarana-hero-ribbon is-back"></div>
    </section>

    <section class="sarana-content">
        <div class="sarana-list">
            <div class="sarana-accordion-item">
                <button type="button" class="sarana-item" aria-expanded="false">
                    <span class="sarana-item-main">
                        <span class="sarana-item-icon"><i class="bi bi-building"></i></span>
                        <span class="sarana-item-title">Gedung &amp; Fasilitas</span>
                    </span>
                    <i class="bi bi-caret-down-fill sarana-item-arrow"></i>
                </button>
                <div class="sarana-panel">
                    <div class="sarana-panel-inner">
                        <p><strong>Gedung kantor Balai K3 Surabaya</strong> dibangun secara bertahap pada tahun 1985 dan 2015 dengan luas bangunan 2.400 m&sup2;.</p>
                        <p><strong>Fasilitas yang tersedia meliputi:</strong></p>
                        <ul>
                            <li>Ruang pertemuan</li>
                            <li>Ruang laboratorium</li>
                            <li>Ruang pegawai</li>
                            <li>Ruang perpustakaan</li>
                            <li>Ruang komputer</li>
                            <li>Instalasi pengolahan air limbah</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="sarana-accordion-item">
                <button type="button" class="sarana-item" aria-expanded="false">
                    <span class="sarana-item-main">
                        <span class="sarana-item-icon"><i class="bi bi-tools"></i></span>
                        <span class="sarana-item-title">Perlengkapan Kerja</span>
                    </span>
                    <i class="bi bi-caret-down-fill sarana-item-arrow"></i>
                </button>
                <div class="sarana-panel">
                    <div class="sarana-panel-inner">
                        <p><strong>Perlengkapan kerja</strong> yang tersedia untuk mendukung operasional pelayanan meliputi:</p>
                        <ul>
                            <li>4 unit mobil operasional</li>
                            <li>Perabotan kantor</li>
                            <li>Komputer desktop dan laptop serta periferal</li>
                            <li>Scanner, printer, dan perangkat pendukung lainnya</li>
                            <li>LCD, overhead projector, dan mesin fotokopi</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="sarana-accordion-item">
                <button type="button" class="sarana-item" aria-expanded="false">
                    <span class="sarana-item-main">
                        <span class="sarana-item-icon"><i class="bi bi-eyeglasses"></i></span>
                        <span class="sarana-item-title">Peralatan Lapangan</span>
                    </span>
                    <i class="bi bi-caret-down-fill sarana-item-arrow"></i>
                </button>
                <div class="sarana-panel">
                    <div class="sarana-panel-inner">
                        <p><strong>Peralatan lapangan</strong> yang dimiliki antara lain:</p>
                        <ul>
                            <li>Sound level meter, luxmeter, vibrasimeter, dan UV meter</li>
                            <li>Heat stress apparatus dan personal dust sampler (PDS)</li>
                            <li>High/low volume dust sampler dan gas analyzer</li>
                            <li>Smoke meter</li>
                            <li>Peralatan sampling emisi cerobong dan sampling udara atmosfer</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="sarana-accordion-item">
                <button type="button" class="sarana-item" aria-expanded="false">
                    <span class="sarana-item-main">
                        <span class="sarana-item-icon"><i class="bi bi-heart-pulse-fill"></i></span>
                        <span class="sarana-item-title">Peralatan Kesehatan, Gizi Kerja, dll</span>
                    </span>
                    <i class="bi bi-caret-down-fill sarana-item-arrow"></i>
                </button>
                <div class="sarana-panel">
                    <div class="sarana-panel-inner">
                        <p><strong>Peralatan kesehatan dan gizi kerja</strong> yang tersedia meliputi:</p>
                        <ul>
                            <li>Audiometri dan spirometri</li>
                            <li>Hematology analyzer dan urine analyzer</li>
                            <li>Peralatan kimia klinik dan ECG</li>
                            <li>Reaction timer, Harvard step test, dan kalorimetri</li>
                            <li>Rontgen paru, projector snellen, dan alat pendukung lainnya</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="sarana-accordion-item">
                <button type="button" class="sarana-item" aria-expanded="false">
                    <span class="sarana-item-main">
                        <span class="sarana-item-icon"><i class="bi bi-plus-square-fill"></i></span>
                        <span class="sarana-item-title">Peralatan Keselamatan Kerja</span>
                    </span>
                    <i class="bi bi-caret-down-fill sarana-item-arrow"></i>
                </button>
                <div class="sarana-panel">
                    <div class="sarana-panel-inner">
                        <p><strong>Peralatan keselamatan kerja</strong> yang tersedia meliputi:</p>
                        <ul>
                            <li>Earth test</li>
                            <li>Tahanan isolasi</li>
                            <li>Peralatan pendukung pengujian keselamatan kerja lainnya</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const accordionItems = document.querySelectorAll('.sarana-accordion-item');

        function closeItem(item) {
            const button = item.querySelector('.sarana-item');
            const panel = item.querySelector('.sarana-panel');

            item.classList.remove('is-open');

            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }

            if (panel) {
                panel.style.maxHeight = panel.scrollHeight + 'px';
                requestAnimationFrame(function () {
                    panel.style.maxHeight = '0px';
                });
            }
        }

        function openItem(item) {
            const button = item.querySelector('.sarana-item');
            const panel = item.querySelector('.sarana-panel');

            item.classList.add('is-open');

            if (button) {
                button.setAttribute('aria-expanded', 'true');
            }

            if (panel) {
                panel.style.maxHeight = panel.scrollHeight + 'px';
            }
        }

        accordionItems.forEach(function (item) {
            const panel = item.querySelector('.sarana-panel');
            if (panel) {
                panel.style.maxHeight = item.classList.contains('is-open')
                    ? panel.scrollHeight + 'px'
                    : '0px';
            }
        });

        accordionItems.forEach(function (item) {
            const button = item.querySelector('.sarana-item');

            button.addEventListener('click', function () {
                const isOpen = item.classList.contains('is-open');

                accordionItems.forEach(function (entry) {
                    if (entry !== item) {
                        closeItem(entry);
                    }
                });

                if (isOpen) {
                    closeItem(item);
                } else {
                    openItem(item);
                }
            });
        });

        window.addEventListener('resize', function () {
            accordionItems.forEach(function (item) {
                if (item.classList.contains('is-open')) {
                    const panel = item.querySelector('.sarana-panel');
                    if (panel) {
                        panel.style.maxHeight = panel.scrollHeight + 'px';
                    }
                }
            });
        });
    });
</script>
@endsection
