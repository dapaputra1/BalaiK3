@extends('layouts.app')

@section('seo_title', $article->title . ' | Berita Balai K3 Surabaya')
@section('seo_description', \Illuminate\Support\Str::limit($article->excerpt, 155))
@section('seo_keywords', 'berita Balai K3 Surabaya, ' . $article->title . ', informasi K3')
@section('seo_image', $article->image_url)
@section('seo_type', 'article')
@section('seo_json_ld')
@php echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $article->title,
    'description' => \Illuminate\Support\Str::limit($article->excerpt, 155),
    'image' => [$article->image_url],
    'datePublished' => optional($article->created_at)->toAtomString(),
    'dateModified' => optional($article->updated_at)->toAtomString(),
    'author' => [
        '@type' => 'Person',
        'name' => $article->uploader_name,
    ],
    'publisher' => [
        '@type' => 'GovernmentOrganization',
        'name' => 'Balai K3 Surabaya',
        'logo' => [
            '@type' => 'ImageObject',
            'url' => asset('images/Logo2.png'),
        ],
    ],
    'mainEntityOfPage' => route('berita.show', $article->slug),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); @endphp
@endsection

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap');

    .news-detail-page {
        --news-nav-offset: 112px;
        --news-nav-lift: 28px;
        min-height: 100vh;
        background: linear-gradient(
            180deg,
            #2f5b86 0%,
            #476f98 10%,
            #6f92b3 20%,
            #8aa8c3 29%,
            #cfdce9 38%,
            #eef3f7 46%,
            #f6f6f6 54%,
            #f6f6f6 100%
        );
        color: #17324d;
        font-family: 'Poppins', sans-serif;
        margin-top: calc((var(--news-nav-offset) + var(--news-nav-lift)) * -1);
        padding-top: 0;
    }

    .news-detail-hero {
        padding: 146px 0 82px;
        background: transparent;
    }

    .news-detail-shell {
        width: min(1016px, calc(100% - 64px));
        margin: 0 auto;
    }

    .news-detail-hero .news-detail-shell {
        transform: translateY(20px);
    }

    .news-detail-back {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #ffffff;
        text-decoration: none;
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 14px;
        width: max-content;
    }

    .news-detail-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 0 16px;
        border-radius: 999px;
        background: #43b981;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 14px;
    }

    .news-detail-title {
        font-family: 'Montserrat', sans-serif;
        margin: 0 0 14px;
        color: #ffffff;
        font-size: 29px;
        font-weight: 700;
        line-height: 1.18;
        text-shadow: 0 6px 16px rgba(18, 45, 76, .16);
    }

    .news-detail-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 12px 0 14px;
        border-top: 1px solid rgba(255, 255, 255, .78);
        border-bottom: 1px solid rgba(255, 255, 255, .78);
        color: #ffffff;
    }

    .news-detail-author,
    .news-detail-stats {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .news-detail-author-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: transparent;
        border: 1px solid rgba(255, 255, 255, .78);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        box-shadow: none;
    }

    .news-detail-author span,
    .news-detail-stats span {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 600;
    }

    .news-detail-main {
        width: min(1016px, calc(100% - 64px));
        margin: 0 auto 0;
        transform: translateY(-20px);
        padding-bottom: 70px;
    }

    .news-detail-image {
        width: 100%;
        border-radius: 22px;
        overflow: hidden;
        margin-bottom: 22px;
    }

    .news-detail-image img {
        width: 100%;
        display: block;
        object-fit: cover;
    }

    .news-detail-body {
        color: #222;
        text-align: justify;
        text-justify: inter-word;
    }

    .news-detail-body p {
        margin: 0 0 22px;
        color: inherit;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.8;
        white-space: pre-line;
    }

    .news-detail-body ul,
    .news-detail-body ol {
        margin: 0 0 22px;
        padding-left: 24px;
        color: inherit;
    }

    .news-detail-body li {
        margin-bottom: 10px;
        font-size: 14px;
        font-weight: 500;
        line-height: 1.8;
        text-align: justify;
        text-justify: inter-word;
    }

    .news-detail-body li:last-child {
        margin-bottom: 0;
    }

    .news-related-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: 34px 0 18px;
    }

    .news-related-head h2 {
        font-family: 'Montserrat', sans-serif;
        margin: 0;
        color: #18456f;
        font-size: 18px;
        font-weight: 700;
    }

    .news-related-head a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #43b981;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .news-related-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 20px;
    }

    .news-related-card {
        background: #ffffff;
        border: 1px solid #dde3ea;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(17, 46, 76, .06);
        overflow: hidden;
        min-height: 414px;
    }

    .news-related-media {
        margin: 18px 18px 0;
        border-radius: 12px;
        overflow: hidden;
        height: 190px;
        display: block;
    }

    .news-related-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .news-related-body {
        padding: 16px 18px 22px;
    }

    .news-related-date {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #43b981;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .news-related-body h3 {
        font-family: 'Montserrat', sans-serif;
        margin: 0 0 16px;
        color: #18456f;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.28;
    }

    .news-related-body p {
        margin: 0 0 24px;
        color: #333;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.55;
    }

    .news-related-body a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #43b981;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
    }

    @media (max-width: 1199.98px) {
        .news-related-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .news-detail-hero {
            padding: 136px 0 66px;
        }

        .news-detail-shell,
        .news-detail-main {
            width: auto;
            margin-left: 18px;
            margin-right: 18px;
        }

        .news-detail-main {
            transform: translateY(-14px);
        }

        .news-detail-hero .news-detail-shell {
            transform: translateY(14px);
        }

        .news-detail-title {
            font-size: 24px;
        }

        .news-detail-meta {
            flex-direction: column;
            align-items: flex-start;
        }

        .news-related-head {
            flex-direction: column;
            align-items: flex-start;
        }

        .news-related-grid {
            grid-template-columns: 1fr;
        }

        .news-related-card {
            min-height: auto;
        }
    }
</style>

<div class="news-detail-page">
    <section class="news-detail-hero">
        <div class="news-detail-shell">
            <a href="{{ route('berita') }}" class="news-detail-back">
                <i class="bi bi-arrow-left"></i>
                <span>Kembali ke Berita</span>
            </a>

            <span class="news-detail-badge">Berita Terbaru</span>
            <h1 class="news-detail-title">{{ $article->title }}</h1>

            <div class="news-detail-meta">
                <div class="news-detail-author">
                    <span class="news-detail-author-badge"><i class="bi bi-person-fill"></i></span>
                    <span>{{ $article->uploader_name }}</span>
                </div>
                <div class="news-detail-stats">
                    <span><i class="bi bi-calendar4-event"></i>{{ $article->formatted_date }}</span>
                    <span><i class="bi bi-eye"></i>{{ $article->views_label }}</span>
                </div>
            </div>
        </div>
    </section>

    <section class="news-detail-main">
        <div class="news-detail-image">
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}">
        </div>

        <div class="news-detail-body">
            @foreach ($article->content_blocks as $block)
                @if (($block['type'] ?? 'paragraph') === 'list')
                    @if (!empty($block['ordered']))
                        <ol>
                            @foreach (($block['items'] ?? []) as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <ul>
                            @foreach (($block['items'] ?? []) as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <p>{{ $block['content'] ?? '' }}</p>
                @endif
            @endforeach
        </div>

        @if($relatedArticles->isNotEmpty())
            <div class="news-related-head">
                <h2>Berita Lainnya</h2>
                <a href="{{ route('berita') }}">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="news-related-grid">
                @foreach ($relatedArticles as $related)
                    <article class="news-related-card">
                        <a href="{{ route('berita.show', $related->slug) }}" class="news-related-media">
                            <img src="{{ $related->image_url }}" alt="{{ $related->title }}">
                        </a>
                        <div class="news-related-body">
                            <span class="news-related-date"><i class="bi bi-calendar4-event"></i>{{ $related->formatted_date }}</span>
                            <h3>{{ $related->title }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit($related->excerpt, 78) }}</p>
                            <a href="{{ route('berita.show', $related->slug) }}">Selengkapnya <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
