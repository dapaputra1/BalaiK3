@extends('layouts.app')

@section('seo_title', $categoryMeta['seo_title'])
@section('seo_description', $categoryMeta['seo_description'])
@section('seo_keywords', $categoryMeta['seo_keywords'])
@section('seo_json_ld')
@php echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $categoryMeta['seo_title'],
    'url' => route($categoryMeta['route']),
    'description' => $categoryMeta['seo_description'],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); @endphp
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Poppins:wght@400;500;600;700&display=swap');

    .network-page {
        --network-nav-offset: 112px;
        --network-nav-lift: 28px;
        margin-top: calc((var(--network-nav-offset) + var(--network-nav-lift)) * -1);
        padding-top: 0;
        min-height: 100vh;
        background:
            radial-gradient(circle at top left, rgba(255, 255, 255, 0.88), transparent 30%),
            linear-gradient(180deg, #dfeaf4 0%, #f3f7fb 33%, #ffffff 100%);
        font-family: 'Poppins', sans-serif;
        color: #17324d;
    }

    .network-shell {
        width: min(1120px, calc(100% - 56px));
        margin: 0 auto;
    }

    .network-hero {
        position: relative;
        overflow: hidden;
        padding: 144px 0 172px;
        background: linear-gradient(135deg, rgba(17, 61, 99, 0.96) 0%, rgba(30, 88, 138, 0.92) 52%, rgba(77, 150, 126, 0.84) 100%);
    }

    .network-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 16% 24%, rgba(255, 255, 255, 0.18), transparent 24%),
            radial-gradient(circle at 82% 18%, rgba(255, 255, 255, 0.10), transparent 20%);
        pointer-events: none;
    }

    .network-hero-inner {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .network-breadcrumb {
        margin: 0;
        color: rgba(255, 255, 255, 0.92);
        font-size: 13px;
        font-weight: 600;
    }

    .network-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .network-title {
        margin: 0;
        max-width: 680px;
        color: #ffffff;
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(20px, 2.5vw, 28px);
        font-weight: 800;
        line-height: 1.05;
    }

    .network-lead {
        margin: 0;
        max-width: 760px;
        color: rgba(255, 255, 255, 0.94);
        font-size: 12px;
        font-weight: 500;
        line-height: 1.6;
    }

    .network-content {
        margin-top: -94px;
        padding-bottom: 88px;
    }

    .network-card {
        position: relative;
        z-index: 2;
        border: 1px solid rgba(21, 64, 106, 0.08);
        border-radius: 28px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 28px 58px rgba(16, 45, 74, 0.14);
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .network-card-head {
        padding: 24px 34px 10px;
        border-bottom: 1px solid rgba(21, 64, 106, 0.08);
    }

    .network-card-title {
        margin: 0 0 8px;
        color: #15406a;
        font-family: 'Montserrat', sans-serif;
        font-size: 22px;
        font-weight: 700;
    }

    .network-card-text {
        margin: 0;
        color: #506477;
        font-size: 13px;
        line-height: 1.7;
    }

    .network-table-wrap {
        padding: 10px 24px 28px;
    }

    .network-table-shell {
        border: 1px solid rgba(21, 64, 106, 0.09);
        border-radius: 20px;
        overflow: hidden;
        background: #ffffff;
    }

    .network-table {
        margin: 0;
    }

    .network-table thead th {
        padding: 18px 20px;
        border: 0;
        background: #15406a;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.01em;
        vertical-align: middle;
        white-space: nowrap;
    }

    .network-table tbody td {
        padding: 18px 20px;
        border-color: rgba(21, 64, 106, 0.08);
        color: #27435f;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.65;
        vertical-align: top;
    }

    .network-table tbody tr:nth-child(even) td {
        background: rgba(239, 246, 251, 0.72);
    }

    .network-table a {
        color: #15406a;
        font-weight: 700;
        text-decoration: none;
    }

    .network-table a:hover,
    .network-table a:focus {
        color: #1e6a92;
        text-decoration: underline;
    }

    .network-empty {
        padding: 42px 24px !important;
        text-align: center;
        color: #5f7183 !important;
        background: linear-gradient(180deg, rgba(243, 247, 251, 0.9), rgba(255, 255, 255, 0.98));
    }

    .network-empty strong {
        display: block;
        margin-bottom: 6px;
        color: #15406a;
        font-size: 15px;
        font-weight: 700;
    }

    @media (max-width: 991.98px) {
        .network-page {
            --network-nav-offset: 100px;
            --network-nav-lift: 16px;
        }

        .network-shell {
            width: min(100% - 32px, 1120px);
        }

        .network-hero {
            padding: 128px 0 148px;
        }

        .network-content {
            margin-top: -82px;
        }

        .network-card-head {
            padding: 22px 22px 10px;
        }

        .network-table-wrap {
            padding: 8px 14px 22px;
        }
    }

    @media (max-width: 575.98px) {
        .network-shell {
            width: calc(100% - 24px);
        }

        .network-hero {
            padding: 118px 0 136px;
        }

        .network-card {
            border-radius: 22px;
        }

        .network-card-title {
            font-size: 19px;
        }

        .network-table thead th,
        .network-table tbody td {
            padding: 15px 14px;
            font-size: 12px;
        }
    }
</style>

<section class="network-page">
    <section class="network-hero">
        <div class="network-shell network-hero-inner">
            <p class="network-breadcrumb">
                <a href="{{ route('home') }}">Beranda</a> / <span>{{ $categoryMeta['heading'] }}</span>
            </p>
            <h1 class="network-title">{{ $categoryMeta['heading'] }}</h1>
            <p class="network-lead">{{ $categoryMeta['lead'] }}</p>
        </div>
    </section>

    <section class="network-content">
        <div class="network-shell">
            <div class="network-card">
                <div class="network-card-head">
                    <div>
                        <h2 class="network-card-title">Data {{ $categoryMeta['label'] }}</h2>
                    </div>
                </div>

                <div class="network-table-wrap">
                    <div class="table-responsive network-table-shell">
                        <table class="table network-table align-middle">
                            <thead>
                                <tr>
                                    <th style="width: 32%;">Nama {{ $categoryMeta['label'] }}</th>
                                    <th>Alamat</th>
                                    <th style="width: 24%;">Link Web</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($entries as $entry)
                                    <tr>
                                        <td>{{ $entry->name ?? '-' }}</td>
                                        <td>{{ $entry->address ?? '-' }}</td>
                                        <td>
                                            @if (!empty($entry->website_url))
                                                <a href="{{ $entry->website_url }}" target="_blank" rel="noopener noreferrer">
                                                    {{ $entry->website_url }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="network-empty">
                                            <strong>Data {{ strtolower($categoryMeta['label']) }} belum tersedia.</strong>
                                            Tambahkan data melalui menu kelola jejaring agar tabel ini langsung menampilkan pembaruan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
@endsection
