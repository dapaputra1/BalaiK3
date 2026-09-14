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

    .struktur-page {
        background: transparent;
        font-family: 'Poppins', sans-serif;
    }

    .struktur-hero {
        position: relative;
        min-height: 390px;
        overflow: hidden;
        background:
            linear-gradient(180deg, rgba(22, 58, 96, 0.46) 0%, rgba(20, 54, 87, 0.54) 100%),
            url('{{ asset('images/header2.jpg') }}') center -32px/cover no-repeat;
    }

    .struktur-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.08) 0, rgba(255, 255, 255, 0) 90px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.04) 0, rgba(255, 255, 255, 0) 62%);
        pointer-events: none;
    }

    .struktur-hero::after {
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

    .struktur-hero-ribbon {
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

    .struktur-hero-ribbon.is-back {
        bottom: 28px;
        height: 26px;
        background: linear-gradient(90deg, rgba(92, 192, 163, 0.65), rgba(163, 223, 191, 0.92));
        transform: rotate(-2.2deg);
    }

    .struktur-hero-inner {
        position: relative;
        z-index: 2;
        max-width: 1180px;
        margin: 0 auto;
        min-height: 390px;
        display: flex;
        align-items: flex-start;
        padding: 22px 24px 72px;
    }

    .struktur-hero-copy {
        max-width: 460px;
        color: #ffffff;
        text-shadow: 0 4px 12px rgba(0, 0, 0, 0.26);
    }

    .struktur-breadcrumb {
        margin: 0 0 20px;
        font-size: 15px;
        font-weight: 600;
        line-height: 1.35;
        color: rgba(255, 255, 255, 0.96);
    }

    .struktur-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .struktur-title {
        margin: 0;
        font-size: 30px;
        font-weight: 700;
        line-height: 0.98;
        letter-spacing: 0.01em;
    }

    .struktur-title-line {
        width: 88px;
        height: 5px;
        margin-top: 16px;
        border-radius: 999px;
        background: #3fd1a5;
        box-shadow: 0 2px 10px rgba(63, 209, 165, 0.38);
    }

    .struktur-content {
        position: relative;
        z-index: 3;
        padding: 118px 20px 140px;
        background: #ffffff;
    }

    .struktur-card {
        width: min(100%, 1020px);
        margin: 0 auto;
        background: #ffffff;
        box-shadow: 0 18px 38px rgba(19, 49, 81, 0.22);
    }

    .struktur-card img {
        display: block;
        width: 100%;
        height: auto;
    }

    @media (max-width: 991.98px) {
        .struktur-hero {
            min-height: 360px;
            background-position: center -24px;
            background-size: cover;
        }

        .struktur-hero-inner {
            min-height: 360px;
            align-items: flex-start;
            padding: 20px 20px 62px;
        }

        .struktur-breadcrumb {
            font-size: 15px;
        }

        .struktur-title {
            font-size: 30px;
        }

        .struktur-content {
            padding-top: 88px;
            padding-bottom: 110px;
        }

        .struktur-card {
            width: min(100%, 860px);
        }
    }

    @media (max-width: 575.98px) {
        .struktur-hero {
            min-height: 300px;
            background-position: center -16px;
            background-size: cover;
        }

        .struktur-hero-inner {
            min-height: 300px;
            align-items: flex-start;
            padding: 18px 16px 46px;
        }

        .struktur-breadcrumb {
            margin-bottom: 16px;
            font-size: 15px;
        }

        .struktur-title {
            font-size: 30px;
        }

        .struktur-title-line {
            width: 74px;
            margin-top: 12px;
        }

        .struktur-hero-ribbon {
            bottom: 28px;
            height: 24px;
        }

        .struktur-hero-ribbon.is-back {
            bottom: 18px;
            height: 16px;
        }

        .struktur-content {
            padding: 66px 14px 90px;
        }

        .struktur-card {
            width: min(100%, 420px);
        }
    }
</style>

<div class="struktur-page">
    <section class="struktur-hero">
        <div class="struktur-hero-inner">
            <div class="struktur-hero-copy">
                <p class="struktur-breadcrumb"><a href="{{ route('home') }}">Beranda</a> / Struktur Organisasi</p>
                <h1 class="struktur-title">STRUKTUR ORGANISASI</h1>
                <div class="struktur-title-line"></div>
            </div>
        </div>
        <div class="struktur-hero-ribbon"></div>
        <div class="struktur-hero-ribbon is-back"></div>
    </section>

    <section class="struktur-content">
        <div class="struktur-card">
            <img src="{{ asset('images/strukturorganisasi.jpg') }}" alt="Struktur Organisasi Balai K3 Surabaya">
        </div>
    </section>
</div>
@endsection
