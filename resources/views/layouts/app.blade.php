<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        $routeName = request()->route()?->getName();
        $seoDefaults = [
            'home' => [
                'title' => 'Balai K3 Surabaya | Layanan K3, Pengujian, Pelatihan, dan Informasi Resmi',
                'description' => 'Website resmi Balai K3 Surabaya untuk layanan keselamatan dan kesehatan kerja, pengujian lingkungan kerja, pelatihan K3, berita, dan informasi pelayanan publik.',
            ],
            'daftar_pelayanan' => [
                'title' => 'Daftar Pelayanan Balai K3 Surabaya | Tarif PNBP dan Layanan K3',
                'description' => 'Lihat daftar pelayanan Balai K3 Surabaya beserta kategori layanan K3, parameter pengujian, dan tarif PNBP sesuai ketentuan yang berlaku.',
            ],
            'jejaring.universitas' => [
                'title' => 'Jejaring Universitas Balai K3 Surabaya',
                'description' => 'Daftar jejaring universitas Balai K3 Surabaya yang memuat nama universitas, alamat, dan tautan website resmi.',
            ],
            'jejaring.pjk3' => [
                'title' => 'Jejaring PJK3 Balai K3 Surabaya',
                'description' => 'Daftar jejaring PJK3 Balai K3 Surabaya yang memuat nama lembaga, alamat, dan tautan website resmi.',
            ],
            'jejaring.perusahaan' => [
                'title' => 'Jejaring Perusahaan Balai K3 Surabaya',
                'description' => 'Daftar jejaring perusahaan Balai K3 Surabaya yang memuat nama perusahaan, alamat, dan tautan website resmi.',
            ],
            'jejaring.instansi-wilayah-kerja' => [
                'title' => 'Jejaring Instansi Wilayah Kerja Balai K3 Surabaya',
                'description' => 'Daftar jejaring instansi wilayah kerja Balai K3 Surabaya yang memuat nama instansi, alamat, dan tautan website resmi.',
            ],
            'jejaring.instansi' => [
                'title' => 'Jejaring Instansi Balai K3 Surabaya',
                'description' => 'Daftar jejaring instansi Balai K3 Surabaya yang memuat nama instansi, alamat, dan tautan website resmi.',
            ],
            'berita' => [
                'title' => 'Berita Balai K3 Surabaya | Informasi Kegiatan dan Layanan Terbaru',
                'description' => 'Kumpulan berita terbaru Balai K3 Surabaya seputar kegiatan, layanan, program, dan informasi keselamatan serta kesehatan kerja.',
            ],
            'kontak' => [
                'title' => 'Kontak Balai K3 Surabaya | Feedback, Saran, dan Informasi Layanan',
                'description' => 'Hubungi Balai K3 Surabaya untuk pertanyaan layanan, feedback, saran, dan informasi terkait pelayanan keselamatan dan kesehatan kerja.',
            ],
            'visi_misi' => [
                'title' => 'Visi dan Misi Balai K3 Surabaya',
                'description' => 'Pelajari visi dan misi Balai K3 Surabaya dalam mendukung layanan keselamatan dan kesehatan kerja yang profesional, akurat, dan terpercaya.',
            ],
            'alur_pelayanan' => [
                'title' => 'Alur Pelayanan Balai K3 Surabaya',
                'description' => 'Pahami alur pelayanan Balai K3 Surabaya mulai dari permohonan, penjadwalan, pengujian, hingga penerbitan dokumen hasil layanan.',
            ],
            'struktur' => [
                'title' => 'Struktur Organisasi Balai K3 Surabaya',
                'description' => 'Informasi struktur organisasi Balai K3 Surabaya untuk mendukung transparansi layanan dan tata kelola kelembagaan.',
            ],
            'sarana_prasarana' => [
                'title' => 'Sarana dan Prasarana Balai K3 Surabaya',
                'description' => 'Lihat sarana dan prasarana Balai K3 Surabaya yang mendukung layanan pengujian, pemeriksaan, dan pelatihan K3.',
            ],
            'video_profil' => [
                'title' => 'Video Profil Balai K3 Surabaya',
                'description' => 'Tonton video profil Balai K3 Surabaya untuk mengenal layanan, fasilitas, dan komitmen terhadap keselamatan dan kesehatan kerja.',
            ],
        ];
        $defaultSeo = $seoDefaults[$routeName] ?? [
            'title' => 'Balai K3 Surabaya',
            'description' => 'Website resmi Balai K3 Surabaya.',
        ];
        $siteName = 'Balai K3 Surabaya';
        $secureUrl = static fn (?string $url): string => preg_replace('/^http:/i', 'https:', $url ?? '');
        $pageTitle = trim($__env->yieldContent('seo_title', $defaultSeo['title']));
        $metaDescription = trim($__env->yieldContent('seo_description', $defaultSeo['description']));
        $metaKeywords = trim($__env->yieldContent('seo_keywords', 'Balai K3 Surabaya, K3 Surabaya, keselamatan dan kesehatan kerja, layanan K3, pengujian lingkungan kerja, pelatihan K3'));
        $metaRobots = trim($__env->yieldContent('seo_robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'));
        $canonicalUrl = $secureUrl(request()->fullUrl());
        $shareImage = $secureUrl(trim($__env->yieldContent('seo_image', asset('images/Logo2.png'))));
        $siteUrl = $secureUrl(url('/'));
        $siteLogo = $secureUrl(asset('images/Logo2.png'));
        $seoType = trim($__env->yieldContent('seo_type', 'website'));
        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'GovernmentOrganization',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => $siteLogo,
            'image' => $siteLogo,
        ];
        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl,
            'inLanguage' => 'id-ID',
        ];
        $jsonLdSchemas = array_filter([
            $organizationSchema,
            $websiteSchema,
            trim($__env->yieldContent('seo_json_ld')),
        ]);
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="{{ $metaRobots }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="theme-color" content="#15406A">
    <meta name="google-site-verification" content="0-4qLLVHLt_1rdIHXQWpIS7oKl9tMIdolfFX7M2WqPo" />
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">
    <link rel="icon" type="image/png" href="{{ $siteLogo }}">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-container {
            z-index: 200000 !important;
        }
        :root {
            --brand-blue-dark: #15406A;
            --user-page-bg: #f4f7fb;
            --user-page-bg-soft: #e8f0fa;
            --user-page-accent: rgba(21, 64, 106, 0.10);
        }

        html {
            background: var(--user-page-bg);
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.92), transparent 30%),
                radial-gradient(circle at top right, rgba(94, 140, 190, 0.14), transparent 22%),
                linear-gradient(180deg, var(--user-page-bg) 0%, var(--user-page-bg-soft) 100%);
            background-attachment: fixed;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 12% 10%, var(--user-page-accent), transparent 18%),
                radial-gradient(circle at 88% 14%, rgba(57, 169, 127, 0.08), transparent 16%);
            pointer-events: none;
            z-index: -1;
        }

        .user-page-shell {
            position: relative;
        }

        .form-control:focus,
        .input-custom:focus,
        textarea:focus {
            border-color: var(--brand-blue-dark) !important;
            box-shadow: 0 0 0 0.2rem rgba(21, 64, 106, 0.2) !important;
        }
        textarea.form-control {
            border-color: var(--brand-blue-dark) !important;
        }
        /* Checkbox & radio border lebih tegas dan jelas */
        .form-check-input {
            border: 1px solid #000 !important;
        }
        .form-check-input:checked {
            background-color: var(--brand-blue-dark) !important;
            border-color: #000 !important;
        }

        .btn-primary,
        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active,
        .btn-primary.active,
        .btn-primary.show,
        .btn-secondary,
        .btn-secondary:hover,
        .btn-secondary:focus,
        .btn-secondary:active,
        .btn-secondary.active,
        .btn-secondary.show {
            background-color: var(--brand-blue-dark) !important;
            border-color: var(--brand-blue-dark) !important;
            color: #fff !important;
        }

        .btn-outline-primary,
        .btn-outline-primary:focus,
        .btn-outline-secondary,
        .btn-outline-secondary:focus {
            color: var(--brand-blue-dark) !important;
            border-color: var(--brand-blue-dark) !important;
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:active,
        .btn-outline-primary.active,
        .btn-outline-primary.show,
        .btn-outline-secondary:hover,
        .btn-outline-secondary:active,
        .btn-outline-secondary.active,
        .btn-outline-secondary.show {
            background-color: var(--brand-blue-dark) !important;
            border-color: var(--brand-blue-dark) !important;
            color: #fff !important;
        }

        /* Pastikan Modal selalu berada di atas navbar (#mainNavbar memiliki z-index: 9999) */
        .modal {
            z-index: 100050 !important;
        }
        .modal-backdrop {
            z-index: 100040 !important;
        }
        body.modal-open #mainNavbar {
            z-index: 1000 !important;
            opacity: 0.1 !important;
            pointer-events: none !important;
        }

        .site-popup {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(10, 20, 36, 0.62);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.25s ease, visibility 0.25s ease;
            z-index: 20000;
        }

        .site-popup.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .site-popup__dialog {
            position: relative;
            width: min(720px, calc(100vw - 32px));
            border-radius: 20px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 24px 60px rgba(7, 18, 34, 0.28);
        }

        .site-popup__slider {
            position: relative;
        }

        .site-popup__slides {
            position: relative;
        }

        .site-popup__slide {
            display: none;
        }

        .site-popup__slide.is-active {
            display: block;
        }

        .site-popup__nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 999px;
            background: rgba(21, 64, 106, 0.88);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            cursor: pointer;
        }

        .site-popup__nav--prev {
            left: 12px;
        }

        .site-popup__nav--next {
            right: 12px;
        }

        .site-popup__dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 12px 16px 0;
            background: #ffffff;
        }

        .site-popup__dot {
            width: 10px;
            height: 10px;
            border: 0;
            border-radius: 999px;
            background: #c3d2e3;
            padding: 0;
        }

        .site-popup__dot.is-active {
            background: var(--brand-blue-dark);
        }

        .site-popup__close {
            width: 100%;
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 18px;
            background: #f3f7fc;
            border-top: 1px solid rgba(21, 64, 106, 0.12);
            color: #ffffff;
            color: var(--brand-blue-dark);
            font-size: 16px;
            font-weight: 600;
            line-height: 1;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .site-popup__close:hover {
            background: #e6eef8;
        }

        .site-popup__image {
            display: block;
            width: 100%;
            height: auto;
            max-height: min(82vh, 860px);
            object-fit: contain;
            background: #ffffff;
        }

        @media (max-width: 576px) {
            .site-popup {
                padding: 24px;
            }

            .site-popup__dialog picture {
                display: block;
                line-height: 0;
            }

            .site-popup__dialog {
                width: min(320px, calc(100vw - 48px));
                border-radius: 0;
                overflow: visible;
                background: transparent;
                box-shadow: none;
            }

            .site-popup__image {
                max-height: 62vh;
                border-radius: 16px;
                background: transparent;
            }

            .site-popup__nav {
                width: 34px;
                height: 34px;
                font-size: 12px;
            }

            .site-popup__nav--prev {
                left: 8px;
            }

            .site-popup__nav--next {
                right: 8px;
            }

            .site-popup__dots {
                padding-top: 10px;
                background: transparent;
            }

            .site-popup__close {
                position: absolute;
                top: 14px;
                right: 14px;
                width: 36px;
                height: 36px;
                margin: 0;
                padding: 0;
                border-radius: 999px;
                border-top: 0;
                background: rgba(21, 64, 106, 0.92);
                color: #ffffff;
                font-size: 14px;
                z-index: 2;
            }

            .site-popup__close span {
                display: none;
            }

            .site-popup__close:hover {
                background: rgba(18, 53, 85, 0.98);
            }
        }
    </style>
    @foreach ($jsonLdSchemas as $schema)
        <script type="application/ld+json">{!! is_string($schema) ? $schema : json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
    @endforeach
</head>
<body class="{{ request()->routeIs('home') ? 'is-home' : '' }}">
    @include('partials.page_loader')
    
    @include('partials.navbar')

    <!-- FULL WIDTH tanpa space -->
    <div class="container-fluid p-0 user-page-shell">
        @yield('content')
    </div>

    @include('partials.footer')

    @if (request()->routeIs('home'))
        @php
            $homePopupSlides = \App\Models\HomePopup::resolvedSlides();
        @endphp
        <div class="site-popup" id="sitePopup" aria-hidden="true">
            <div class="site-popup__dialog" role="dialog" aria-modal="true" aria-label="Informasi popup website">
                <div class="site-popup__slider" id="sitePopupSlider">
                    <div class="site-popup__slides">
                        @foreach ($homePopupSlides as $index => $slide)
                            <div class="site-popup__slide {{ $index === 0 ? 'is-active' : '' }}" data-popup-slide="{{ $index }}">
                                <picture>
                                    <source media="(max-width: 576px)" srcset="{{ $slide['mobile_url'] }}">
                                    <img src="{{ $slide['desktop_url'] }}" alt="{{ $slide['name'] }}" class="site-popup__image">
                                </picture>
                            </div>
                        @endforeach
                    </div>
                    @if ($homePopupSlides->count() > 1)
                        <button type="button" class="site-popup__nav site-popup__nav--prev" id="sitePopupPrev" aria-label="Popup sebelumnya">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="site-popup__nav site-popup__nav--next" id="sitePopupNext" aria-label="Popup berikutnya">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    @endif
                </div>
                @if ($homePopupSlides->count() > 1)
                    <div class="site-popup__dots" id="sitePopupDots" aria-label="Navigasi popup">
                        @foreach ($homePopupSlides as $index => $slide)
                            <button type="button" class="site-popup__dot {{ $index === 0 ? 'is-active' : '' }}" data-popup-dot="{{ $index }}" aria-label="Popup {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
                <button type="button" class="site-popup__close" id="sitePopupClose" aria-label="Tutup popup">
                    <i class="bi bi-x-lg"></i>
                    <span>Tutup</span>
                </button>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @if (request()->routeIs('home'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const popup = document.getElementById('sitePopup');
                const closeButton = document.getElementById('sitePopupClose');
                const slides = Array.from(document.querySelectorAll('[data-popup-slide]'));
                const dots = Array.from(document.querySelectorAll('[data-popup-dot]'));
                const prevButton = document.getElementById('sitePopupPrev');
                const nextButton = document.getElementById('sitePopupNext');

                if (!popup || !closeButton) {
                    return;
                }

                const storageKey = 'home-popup-dismissed';
                let activeIndex = 0;

                const renderSlides = function (index) {
                    activeIndex = index;

                    slides.forEach(function (slide, slideIndex) {
                        slide.classList.toggle('is-active', slideIndex === activeIndex);
                    });

                    dots.forEach(function (dot, dotIndex) {
                        dot.classList.toggle('is-active', dotIndex === activeIndex);
                    });
                };

                const goToSlide = function (index) {
                    if (slides.length === 0) {
                        return;
                    }

                    const normalized = (index + slides.length) % slides.length;
                    renderSlides(normalized);
                };

                const closePopup = function () {
                    popup.classList.remove('is-visible');
                    popup.setAttribute('aria-hidden', 'true');
                    document.body.style.removeProperty('overflow');
                    sessionStorage.setItem(storageKey, '1');
                };

                if (sessionStorage.getItem(storageKey) === '1') {
                    return;
                }

                popup.classList.add('is-visible');
                popup.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                renderSlides(0);

                closeButton.addEventListener('click', closePopup);

                prevButton?.addEventListener('click', function () {
                    goToSlide(activeIndex - 1);
                });

                nextButton?.addEventListener('click', function () {
                    goToSlide(activeIndex + 1);
                });

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () {
                        goToSlide(Number(dot.getAttribute('data-popup-dot') || 0));
                    });
                });

                popup.addEventListener('click', function (event) {
                    if (event.target === popup) {
                        closePopup();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && popup.classList.contains('is-visible')) {
                        closePopup();
                    }
                });
            });
        </script>
    @endif
    @stack('scripts')
</body>
</html>
