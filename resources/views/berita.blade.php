@extends('layouts.app')

@section('seo_title', 'Berita Balai K3 Surabaya | Informasi Kegiatan dan Layanan Terbaru')
@section('seo_description', 'Ikuti berita terbaru Balai K3 Surabaya mengenai kegiatan, layanan, program, dan informasi keselamatan serta kesehatan kerja.')
@section('seo_keywords', 'berita Balai K3 Surabaya, informasi K3, kegiatan Balai K3, layanan K3 terbaru')
@section('seo_image', $featured?->image_url ?? asset('images/header2.jpg'))
@section('seo_json_ld')
@php echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Berita Balai K3 Surabaya',
    'url' => route('berita'),
    'description' => 'Kumpulan berita terbaru Balai K3 Surabaya.',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); @endphp
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap');

    .news-page {
        --news-nav-offset: 112px;
        --news-nav-lift: 28px;
        --card-hover-lift: -4px;
        --card-hover-scale: 1.008;
        --card-active-lift: -2px;
        --card-active-scale: .998;
        --img-hover-scale: 1.05;
        --img-active-scale: 1.025;
        --card-shadow-hover: 0 12px 24px rgba(17, 46, 76, .12);
        --card-shadow-active: 0 8px 16px rgba(17, 46, 76, .10);
        min-height: 100vh;
        background: #f6f6f6;
        color: #17324d;
        font-family: 'Poppins', sans-serif;
        margin-top: calc((var(--news-nav-offset) + var(--news-nav-lift)) * -1);
        padding-top: 0;
    }

    .visually-hidden-seo {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .news-hero {
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
        padding: 138px 0 184px;
    }

    .news-shell {
        width: min(1080px, calc(100% - 64px));
        margin: 0 auto;
    }

    .news-topbar {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
        transform: translateY(12px);
    }

    .news-heading {
        padding-top: 16px;
        transform: none;
    }

    .news-heading h1 {
        font-family: 'Montserrat', sans-serif;
        margin: 0 0 12px;
        color: #ffffff;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.08;
        text-shadow: 0 6px 16px rgba(20, 44, 76, .18);
    }

    .news-heading p {
        margin: 0;
        color: rgba(255, 255, 255, .96);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.38;
    }

    .news-search {
        width: min(342px, 100%);
        position: relative;
        flex: 0 0 auto;
    }

    .news-search i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #a9a9a9;
        font-size: 18px;
    }

    .news-search input {
        width: 100%;
        height: 50px;
        border: 0;
        outline: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, .96);
        color: #2a2a2a;
        font-size: 13px;
        font-weight: 500;
        padding: 0 20px 0 46px;
        box-shadow: 0 6px 16px rgba(20, 44, 76, .10);
    }

    .news-search input::placeholder {
        color: #b7b7b7;
    }

    .news-content {
        margin-top: -146px;
        padding-bottom: 72px;
    }

    .news-feature {
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(0, .98fr);
        align-items: stretch;
        gap: 34px;
        padding: 26px 18px;
        background: #ffffff;
        border: 1px solid #dde3ea;
        border-radius: 22px;
        box-shadow: 0 4px 12px rgba(17, 46, 76, .08);
    }

    .news-feature-media {
        border-radius: 18px;
        overflow: hidden;
        min-height: 326px;
    }

    .news-feature-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .news-feature-body {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        padding: 0 14px 4px 0;
        height: 100%;
    }

    .news-feature-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .news-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        padding: 0 12px;
        border-radius: 999px;
        background: #4aba85;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
    }

    .news-date {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #4aba85;
        font-size: 12px;
        font-weight: 600;
    }

    .news-feature-body h2 {
        font-family: 'Montserrat', sans-serif;
        margin: 0 0 18px;
        color: #18456f;
        font-size: 24px;
        font-weight: 700;
        line-height: 1.2;
    }

    .news-feature-body p {
        margin: 0 0 14px;
        color: #2a2a2a;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.52;
    }

    .news-feature-excerpt {
        margin: 0;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 9;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .news-read-more {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 14px;
        color: #4aba85;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .news-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 28px 26px;
        margin-top: 40px;
    }

    .news-grid.zoom-strong {
        --card-hover-lift: -7px;
        --card-hover-scale: 1.015;
        --card-active-lift: -3px;
        --card-active-scale: .995;
        --img-hover-scale: 1.085;
        --img-active-scale: 1.04;
        --card-shadow-hover: 0 16px 30px rgba(17, 46, 76, .16);
        --card-shadow-active: 0 10px 20px rgba(17, 46, 76, .12);
    }

    .news-card {
        background: #ffffff;
        border: 1px solid #dde3ea;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(17, 46, 76, .06);
        overflow: hidden;
        min-height: 378px;
        display: flex;
        flex-direction: column;
        transform: translateY(0) scale(1);
        transition: transform .38s cubic-bezier(0.22, 1, 0.36, 1), box-shadow .38s ease;
    }

    .news-card-media {
        margin: 18px 18px 0;
        border-radius: 12px;
        overflow: hidden;
        height: 190px;
        display: block;
    }

    .news-card-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transform: scale(1);
        transition: transform .62s cubic-bezier(0.22, 1, 0.36, 1), filter .4s ease;
    }

    .news-card:hover,
    .news-card:focus-within {
        transform: translateY(var(--card-hover-lift)) scale(var(--card-hover-scale));
        box-shadow: var(--card-shadow-hover);
    }

    .news-card:hover .news-card-media img,
    .news-card:focus-within .news-card-media img {
        transform: scale(var(--img-hover-scale));
        filter: saturate(1.03);
    }

    .news-card:active {
        transform: translateY(var(--card-active-lift)) scale(var(--card-active-scale));
        box-shadow: var(--card-shadow-active);
    }

    .news-card:active .news-card-media img {
        transform: scale(var(--img-active-scale));
        filter: saturate(1.01);
    }

    @media (prefers-reduced-motion: reduce) {
        .news-card,
        .news-card-media img {
            transition: none;
        }
    }

    .news-card-body {
        padding: 16px 18px 22px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .news-card-date {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #4aba85;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .news-card h3 {
        font-family: 'Montserrat', sans-serif;
        margin: 0 0 16px;
        color: #18456f;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.28;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: calc(1.28em * 2);
    }

    .news-card p {
        margin: 0 0 24px;
        color: #333;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        min-height: calc(1.5em * 3);
    }

    .news-card-read-more {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #4aba85;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        margin-top: auto;
    }

    .news-card.search-only {
        display: none;
    }

    .news-empty {
        display: none;
        margin-top: 18px;
        padding: 14px 16px;
        border: 1px dashed #cad4df;
        border-radius: 12px;
        background: #fff;
        color: #5b6f84;
        font-size: 13px;
        font-weight: 500;
        text-align: center;
    }

    .news-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 40px;
    }

    .news-page-dot,
    .news-page-arrow,
    .news-page-ellipsis {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d9dee4;
        background: #fff;
        color: #707070;
        font-size: 16px;
        font-weight: 600;
        text-decoration: none;
    }

    .news-page-dot.active {
        border-color: #4aba85;
        background: #4aba85;
        color: #fff;
    }

    .news-page-ellipsis {
        border-color: transparent;
        background: transparent;
        width: auto;
    }

    @media (max-width: 1199.98px) {
        .news-shell {
            width: min(1040px, calc(100% - 56px));
        }

        .news-feature {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .news-feature-media {
            min-height: 290px;
        }

        .news-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .news-hero {
            padding: 124px 0 148px;
        }

        .news-shell {
            width: auto;
            margin: 0 18px;
        }

        .news-topbar {
            flex-direction: column;
            align-items: stretch;
            gap: 18px;
            transform: translateY(8px);
        }

        .news-heading {
            padding-top: 12px;
            transform: none;
        }

        .news-heading h1 {
            font-size: 20px;
        }

        .news-heading p {
            font-size: 12px;
        }

        .news-search {
            width: 100%;
        }

        .news-content {
            margin-top: -116px;
            padding-bottom: 52px;
        }

        .news-feature {
            padding: 16px;
            border-radius: 18px;
        }

        .news-feature-media {
            min-height: 228px;
            border-radius: 14px;
        }

        .news-feature-body {
            padding: 0;
        }

        .news-feature-body h2 {
            font-size: 24px;
        }

        .news-feature-body p {
            font-size: 14px;
        }

        .news-feature-excerpt {
            -webkit-line-clamp: 7;
        }

        .news-grid {
            grid-template-columns: 1fr;
            gap: 18px;
            margin-top: 28px;
        }

        .news-card {
            min-height: auto;
        }
    }
</style>

<div class="news-page">
    <h1 class="visually-hidden-seo">Berita Balai K3 Surabaya</h1>
    <section class="news-hero">
        <div class="news-shell">
            <div class="news-topbar">
                <div class="news-heading">
                    <h1>Berita</h1>
                    <p>Informasi terbaru seputar kegiatan, layanan, dan program Balai K3 Surabaya.</p>
                </div>
                <div class="news-search">
                    <i class="bi bi-search"></i>
                    <input id="newsSearchInput" type="text" placeholder="Cari Berita...">
                </div>
            </div>
        </div>
    </section>

    <section class="news-shell news-content">
        @if($featured)
            <article id="newsFeatureCard" class="news-feature">
                <a href="{{ route('berita.show', $featured->slug) }}" class="news-feature-media">
                    <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}">
                </a>
                <div class="news-feature-body">
                    <div class="news-feature-meta">
                        <span class="news-badge">Berita Terbaru</span>
                        <span class="news-date"><i class="bi bi-calendar4-event"></i>{{ $featured->formatted_date }}</span>
                    </div>
                    <h2>{{ $featured->title }}</h2>
                    <p class="news-feature-excerpt">{{ \Illuminate\Support\Str::limit($featured->excerpt, 900) }}</p>
                    <a href="{{ route('berita.show', $featured->slug) }}" class="news-read-more">Baca Selengkapnya <i class="bi bi-arrow-right"></i></a>
                </div>
            </article>
        @endif

        <div id="newsGrid" class="news-grid">
            @if($featured)
                <article class="news-card search-only"
                    data-title="{{ \Illuminate\Support\Str::lower($featured->title) }}"
                    data-excerpt="{{ \Illuminate\Support\Str::lower(strip_tags($featured->content)) }}"
                    data-date="{{ \Illuminate\Support\Str::lower($featured->formatted_date) }}">
                    <a href="{{ route('berita.show', $featured->slug) }}" class="news-card-media">
                        <img src="{{ $featured->image_url }}" alt="{{ $featured->title }}">
                    </a>
                    <div class="news-card-body">
                        <span class="news-card-date"><i class="bi bi-calendar4-event"></i>{{ $featured->formatted_date }}</span>
                        <h3>{{ $featured->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($featured->excerpt, 102) }}</p>
                        <a href="{{ route('berita.show', $featured->slug) }}" class="news-card-read-more">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                    </div>
                </article>
            @endif
            @foreach ($newsCards as $card)
                <article class="news-card"
                    data-title="{{ \Illuminate\Support\Str::lower($card->title) }}"
                    data-excerpt="{{ \Illuminate\Support\Str::lower(strip_tags($card->content)) }}"
                    data-date="{{ \Illuminate\Support\Str::lower($card->formatted_date) }}">
                    <a href="{{ route('berita.show', $card->slug) }}" class="news-card-media">
                        <img src="{{ $card->image_url }}" alt="{{ $card->title }}">
                    </a>
                    <div class="news-card-body">
                        <span class="news-card-date"><i class="bi bi-calendar4-event"></i>{{ $card->formatted_date }}</span>
                        <h3>{{ $card->title }}</h3>
                        <p>{{ \Illuminate\Support\Str::limit($card->excerpt, 120) }}</p>
                        <a href="{{ route('berita.show', $card->slug) }}" class="news-card-read-more">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                    </div>
                </article>
            @endforeach
        </div>

        @if(!$featured && $newsCards->isEmpty())
            <div class="news-empty" style="display:block; margin-top:0;">
                Belum ada berita yang dipublikasikan.
            </div>
        @endif

        <div id="newsSearchEmpty" class="news-empty">
            Berita tidak ditemukan.
        </div>

        @if(($articlesCount ?? 0) > 7)
            <div id="newsPagination" class="news-pagination">
                <a href="#" class="news-page-arrow"><i class="bi bi-chevron-left"></i></a>
                <a href="#" class="news-page-dot active">1</a>
                <a href="#" class="news-page-dot">2</a>
                <a href="#" class="news-page-dot">3</a>
                <span class="news-page-ellipsis">...</span>
                <a href="#" class="news-page-arrow"><i class="bi bi-chevron-right"></i></a>
            </div>
        @endif
    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('newsSearchInput');
        const featureCard = document.getElementById('newsFeatureCard');
        const grid = document.getElementById('newsGrid');
        const emptyState = document.getElementById('newsSearchEmpty');
        const pagination = document.getElementById('newsPagination');
        const cards = Array.from(grid ? grid.querySelectorAll('.news-card') : []);

        if (!input || !grid || cards.length === 0) return;

        const applySearch = () => {
            const query = input.value.trim().toLowerCase();
            if (!query) {
                if (featureCard) featureCard.style.display = '';
                if (pagination) pagination.style.display = '';
                if (emptyState) emptyState.style.display = 'none';

                cards.forEach((card) => {
                    card.style.display = card.classList.contains('search-only') ? 'none' : '';
                    grid.appendChild(card);
                });
                return;
            }

            if (featureCard) featureCard.style.display = 'none';
            if (pagination) pagination.style.display = 'none';

            const matched = cards.filter((card) => {
                const title = card.dataset.title || '';
                const excerpt = card.dataset.excerpt || '';
                const date = card.dataset.date || '';
                return title.includes(query) || excerpt.includes(query) || date.includes(query);
            });

            matched.sort((a, b) => {
                const aTitle = a.dataset.title || '';
                const bTitle = b.dataset.title || '';
                const aPrefix = aTitle.startsWith(query) ? 0 : 1;
                const bPrefix = bTitle.startsWith(query) ? 0 : 1;
                if (aPrefix !== bPrefix) return aPrefix - bPrefix;
                return aTitle.localeCompare(bTitle);
            });

            cards.forEach((card) => {
                card.style.display = 'none';
            });

            matched.forEach((card) => {
                card.style.display = '';
                grid.appendChild(card);
            });

            if (emptyState) {
                emptyState.style.display = matched.length > 0 ? 'none' : 'block';
            }
        };

        input.addEventListener('input', applySearch);
    });
</script>
@endsection
