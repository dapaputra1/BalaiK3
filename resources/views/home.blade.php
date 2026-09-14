@extends('layouts.app')

@section('seo_title', 'Balai K3 Surabaya | Layanan K3, Pengujian Lingkungan Kerja, dan Pelatihan')
@section('seo_description', 'Balai K3 Surabaya menyediakan layanan keselamatan dan kesehatan kerja, pengujian lingkungan kerja, pelatihan K3, berita, serta informasi pelayanan publik resmi.')
@section('seo_keywords', 'Balai K3 Surabaya, K3 Surabaya, pengujian lingkungan kerja, pelatihan K3, layanan K3, kesehatan kerja')
@section('seo_image', asset('images/header1.jpg'))
@section('seo_json_ld')
@php echo json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Apa saja layanan yang tersedia di Balai K3 Surabaya?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Balai K3 Surabaya menyediakan layanan pengujian lingkungan kerja, kesehatan kerja, pelatihan, pemeriksaan, dan layanan pendukung K3 lainnya sesuai standar yang berlaku.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Bagaimana cara mengajukan permohonan layanan pengujian K3?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Permohonan dapat diajukan melalui alur pelayanan yang tersedia pada website atau dengan menghubungi kontak resmi Balai K3 Surabaya untuk informasi persyaratan dan penjadwalan.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Apakah Balai K3 Surabaya menyediakan edukasi atau sosialisasi tentang K3?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Balai K3 Surabaya juga menyediakan edukasi, sosialisasi, dan pelatihan K3 untuk mendukung peningkatan budaya keselamatan dan kesehatan kerja.',
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); @endphp
@endsection

@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap');

  body{
    margin: 0;
    /* background-color: #000; */
    color: #eee;
    /* font-family: Poppins; */
    font-size: 12px;
  }

  /* simple scroll-reveal animation */
  .reveal {
      opacity: 0;
      transform: translateY(40px);
      transition: opacity 1.2s ease, transform 1.2s ease;
  }
  .reveal.show {
      opacity: 1;
      transform: translateY(0);
  }


  /* slider section */
  .slider{
      height: 100svh;
      height: 100dvh;
      min-height: 0;
      max-height: 100svh;
      margin-top: calc(-15vh - 32px);
      width: 100%;
      overflow: hidden;
      position: relative;
  }
  .slider .list{
      width: 100%;
      height: 100%;
      position: relative;
  }
  .slider .list .item{
      width: 100%;
      height: 100%;
      position: absolute;
      inset: 0;
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      transition: opacity .65s ease, visibility .65s ease;
  }
  .slider .list .item:nth-child(1){
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
      z-index: 2;
  }
  .slider.is-transitioning .list .item:nth-child(2){
      opacity: 1;
      visibility: visible;
      z-index: 1;
  }
  .slider.is-transitioning .list .item:nth-child(2) .content{
      opacity: 0;
  }
  .slider .list .item.is-fading-out{
      opacity: 0;
      visibility: visible;
      pointer-events: none;
      z-index: 3;
  }
  .slider .list .item img{
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center top;
  }
  .slider .list .item .content{
      position: absolute;
      left: 12.5%;
      bottom: 29%;
      width: min(760px, calc(100% - 40px));
      color: #fff;
      z-index: 3;
  }
  .slider .list .item.slide-b .content{
      left: 11%;
      bottom: 27.5%;
  }
  .slider .list .item.slide-c .content{
      left: 14%;
      bottom: 30%;
  }
  .slider .list .item .content .title{
      margin: 0;
      font-family: 'Montserrat', sans-serif;
      font-size: 50px;
      line-height: 1.03;
      font-weight: 700;
      color: #34eab0;
  }
  .slider .list .item .content .type{
      margin-top: 12px;
      font-size: 24px;
      font-style: italic;
      line-height: 1.22;
      color: rgba(255,255,255,.96);
  }
  .slider .list .item .content .description{
      margin-top: 18px;
      max-width: 760px;
      font-size: 20px;
      line-height: 1.45;
      color: rgba(255,255,255,.96);
  }
  .slider .list .item .button{
      margin-top: 24px;
      display: flex;
  }
  .slider .list .item .button a{
      background: #39c79a;
      color: #fff;
      border-radius: 999px;
      padding: 12px 26px;
      font-size: clamp(14px, 1.1vw, 18px);
      font-weight: 700;
      text-decoration: none;
      box-shadow: 0 10px 24px rgba(57,199,154,.26);
      transition: transform .22s ease, box-shadow .22s ease;
  }
  .slider .list .item .button a:hover{
      transform: translateY(-2px);
      box-shadow: 0 14px 28px rgba(57,199,154,.32);
  }
  .hero-dots{
      position: absolute;
      left: 50%;
      transform: translateX(-50%);
      bottom: 58px;
      z-index: 4;
      display: flex;
      gap: 22px;
  }
  .hero-dot{
      width: 12px;
      height: 12px;
      border-radius: 50%;
      border: 0;
      background: rgba(255,255,255,.9);
      cursor: pointer;
      transition: transform .22s ease, background-color .22s ease;
  }
  .hero-dot.is-active{
      background: #34eab0;
      transform: scale(1.1);
  }
  .slider .list .item .content .title,
  .slider .list .item .content .type,
  .slider .list .item .content .description,
  .slider .list .item .content .button{
      will-change: transform, opacity, filter;
      backface-visibility: hidden;
  }

  .slider.is-animating .list .item:nth-child(1) .content .title{
      animation: heroTextInTitle .85s cubic-bezier(0.22, 1, 0.36, 1) both;
  }
  .slider.is-animating .list .item:nth-child(1) .content .type{
      animation: heroTextInBody .85s cubic-bezier(0.22, 1, 0.36, 1) .1s both;
  }
  .slider.is-animating .list .item:nth-child(1) .content .description{
      animation: heroTextInBody .9s cubic-bezier(0.22, 1, 0.36, 1) .18s both;
  }
  .slider.is-animating .list .item:nth-child(1) .content .button{
      animation: heroTextInButton .85s cubic-bezier(0.22, 1, 0.36, 1) .26s both;
  }

  .slider .list .item.is-fading-out .content .title{
      animation: heroTextOutTitle .5s ease both;
  }
  .slider .list .item.is-fading-out .content .type,
  .slider .list .item.is-fading-out .content .description,
  .slider .list .item.is-fading-out .content .button{
      animation: heroTextOutBody .44s ease both;
  }

  @keyframes heroTextInTitle{
      from{
          opacity: 0;
          filter: blur(6px);
          transform: translateY(24px) scale(.985);
      }
      to{
          opacity: 1;
          filter: blur(0);
          transform: translateY(0) scale(1);
      }
  }
  @keyframes heroTextInBody{
      from{
          opacity: 0;
          filter: blur(4px);
          transform: translateY(18px);
      }
      to{
          opacity: 1;
          filter: blur(0);
          transform: translateY(0);
      }
  }
  @keyframes heroTextInButton{
      from{
          opacity: 0;
          transform: translateY(14px) scale(.95);
      }
      to{
          opacity: 1;
          transform: translateY(0) scale(1);
      }
  }
  @keyframes heroTextOutTitle{
      from{
          opacity: 1;
          filter: blur(0);
          transform: translateY(0) scale(1);
      }
      to{
          opacity: 0;
          filter: blur(4px);
          transform: translateY(-14px) scale(.99);
      }
  }
  @keyframes heroTextOutBody{
      from{
          opacity: 1;
          filter: blur(0);
          transform: translateY(0);
      }
      to{
          opacity: 0;
          filter: blur(3px);
          transform: translateY(-10px);
      }
  }
  @media screen and (max-width: 991.98px){
      .slider{
          height: 100svh;
          height: 100dvh;
          min-height: 0;
          max-height: 100svh;
          margin-top: calc(-10vh - 20px);
      }
      .slider .list .item .content,
      .slider .list .item.slide-b .content,
      .slider .list .item.slide-c .content{
          left: 24px;
          right: 24px;
          bottom: 34%;
          width: auto;
      }
      .slider .list .item .content .title{
          font-size: clamp(30px, 7vw, 42px);
          line-height: 1.06;
      }
      .slider .list .item .content .type{
          font-size: clamp(16px, 3.9vw, 22px);
          line-height: 1.2;
      }
      .slider .list .item .content .description{
          font-size: clamp(14px, 3.3vw, 18px);
          line-height: 1.38;
      }
      .slider .list .item .button a{
          font-size: clamp(14px, 3.5vw, 16px);
          padding: 10px 20px;
      }
      .hero-dots{
          bottom: 34px;
          gap: 10px;
      }
      .hero-dot{
          width: 8px;
          height: 8px;
          min-width: 8px;
          min-height: 8px;
          padding: 0;
          border-radius: 50%;
          aspect-ratio: 1 / 1;
          appearance: none;
          -webkit-appearance: none;
          flex: 0 0 8px;
      }
  }

  
</style>

<div class="home-surface">
<h1 class="visually-hidden">Balai K3 Surabaya</h1>

<div class="slider">
    <div class="list">

        <div class="item slide-a">
            <img src="/images/header1.jpg" alt="Gedung dan layanan Balai K3 Surabaya">

            <div class="content">
                <div class="title">Selamat Datang</div>
                <div class="type">di Balai K3 Surabaya</div>
                <div class="description">
                    Balai K3 Surabaya hadir sebagai mitra strategis untuk membangun budaya kerja yang aman, sehat, dan produktif. Kami mendampingi perusahaan melalui layanan K3 yang terukur dan sesuai regulasi.
                </div>
                <div class="button">
                    <a href="/daftar_pelayanan">Jelajahi Layanan</a>
                </div>
            </div>
        </div>

        <div class="item slide-b">
            <img src="/images/header2.jpg" alt="Layanan terpadu K3 Balai K3 Surabaya">

            <div class="content">
                <div class="title">Layanan Terpadu</div>
                <div class="type">K3 untuk Dunia Usaha</div>
                <div class="description">
                    Mulai dari pelatihan, pengujian lingkungan kerja, hingga pemeriksaan kesehatan, kami membantu memastikan standar K3 diterapkan dengan baik di setiap sektor industri.
                </div>
                <div class="button">
                    <a href="/daftar_pelayanan">Jelajahi Layanan</a>
                </div>
            </div>
        </div>

        <div class="item slide-c">
            <img src="/images/header3.jpg" alt="Standar pengujian Balai K3 Surabaya berbasis ISO IEC 17025">

            <div class="content">
                <div class="title">Standar Teruji</div>
                <div class="type">Berbasis ISO/IEC 17025</div>
                <div class="description">
                    Dengan laboratorium terakreditasi dan tim profesional, Balai K3 Surabaya memberikan hasil pemeriksaan yang akurat dan dapat dipertanggungjawabkan untuk kebutuhan K3 Anda.
                </div>
                <div class="button">
                    <a href="/daftar_pelayanan">Jelajahi Layanan</a>
                </div>
            </div>
        </div>

    </div>

    <div class="hero-dots" aria-label="Slider pagination">
        <button class="hero-dot is-active" type="button" aria-label="Slide 1" data-slide="0"></button>
        <button class="hero-dot" type="button" aria-label="Slide 2" data-slide="1"></button>
        <button class="hero-dot" type="button" aria-label="Slide 3" data-slide="2"></button>
    </div>
</div>


<script>
  const slider = document.querySelector('.slider');
  const sliderList = slider.querySelector('.list');
  const heroDots = Array.from(slider.querySelectorAll('.hero-dot'));
  const totalSlides = heroDots.length;
  let currentSlide = 0;
  let autoplay = null;
  let isTransitioning = false;

  function renderDots() {
      heroDots.forEach((dot, index) => {
          dot.classList.toggle('is-active', index === currentSlide);
      });
  }

  function triggerSlideAnimation() {
      slider.classList.remove('is-animating');
      void slider.offsetWidth;
      slider.classList.add('is-animating');
  }

  function goNext() {
      if (isTransitioning) return Promise.resolve();

      isTransitioning = true;
      slider.classList.add('is-transitioning');

      return new Promise((resolve) => {
          const firstItem = sliderList.querySelector('.item:nth-child(1)');
          if (!firstItem) {
              isTransitioning = false;
              slider.classList.remove('is-transitioning');
              resolve();
              return;
          }

          firstItem.classList.add('is-fading-out');

          window.setTimeout(() => {
              sliderList.appendChild(firstItem);
              firstItem.classList.remove('is-fading-out');

              currentSlide = (currentSlide + 1) % totalSlides;
              renderDots();
              triggerSlideAnimation();

              slider.classList.remove('is-transitioning');
              isTransitioning = false;
              resolve();
          }, 700);
      });
  }

  async function goTo(index) {
      if (index === currentSlide || isTransitioning) return;

      const steps = (index - currentSlide + totalSlides) % totalSlides;
      for (let i = 0; i < steps; i++) {
          await goNext();
      }
  }

  function restartAutoplay() {
      clearInterval(autoplay);
      autoplay = setInterval(() => {
          if (!isTransitioning) {
              goNext();
          }
      }, 5500);
  }

  heroDots.forEach((dot, index) => {
      dot.addEventListener('click', async function() {
          await goTo(index);
          restartAutoplay();
      });
  });

  renderDots();
  triggerSlideAnimation();
  restartAutoplay();
</script>
<!-- resources/views/services.blade.php -->


    <style>
        body {
            background-color: #e6e6e6;
            font-family: 'Poppins', sans-serif;
        }

        .layanan-wrap {
            background: transparent;
            border-radius: 0;
            width: min(1280px, calc(100% - 96px));
            margin: 44px auto 0;
            padding: 30px 20px 34px;
        }

        .layanan-head {
            max-width: 1240px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: start;
            gap: 16px;
            color: #174772;
        }

        .layanan-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 20px;
            line-height: 1.05;
            margin: 0;
        }

        .layanan-underline {
            width: 145px;
            height: 4px;
            background: #174772;
            border-radius: 99px;
            margin-top: 10px;
        }

        .layanan-badge {
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.3;
            color: #18456d;
            margin-top: 52px;
            text-align: right;
            position: static;
        }

        .layanan-desc {
            max-width: 1240px;
            margin: -14px auto 28px;
            color: #232323;
            font-size: 13px;
            line-height: 1.45;
            font-weight: 500;
        }

        .layanan-grid {
            max-width: 1240px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 18px;
        }

        .layanan-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 0 rgba(0, 0, 0, 0.18), 0 2px 12px rgba(20, 40, 70, 0.1);
            padding: 18px 14px 16px;
            text-align: center;
            min-height: 220px;
            transform: scale(1);
            transform-origin: center bottom;
            transition:
                transform .35s cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow .35s cubic-bezier(0.22, 1, 0.36, 1);
            will-change: transform;
        }

        .layanan-card:hover {
            transform: scale(1.04);
            box-shadow: 0 10px 0 rgba(0, 0, 0, 0.16), 0 14px 24px rgba(20, 40, 70, 0.16);
        }

        .layanan-iconbox {
            width: 56px;
            height: 56px;
            margin: 0 auto 10px;
            border-radius: 11px;
            background: #eef2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #18456d;
            font-size: 30px;
        }

        .layanan-card h5 {
            margin: 0 0 8px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: #174772;
            font-size: 15px;
            line-height: 1.1;
        }

        .layanan-card p {
            margin: 0;
            font-size: 12px;
            line-height: 1.3;
            color: #242424;
            font-weight: 500;
        }

        .stats-wrap {
            width: min(1160px, calc(100% - 80px));
            margin: 46px auto 0;
            padding: 10px 0 12px;
            background: transparent;
        }

        .stats-title {
            margin: 0;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.06;
            color: #18456f;
        }

        .stats-underline {
            width: 320px;
            height: 4px;
            margin: 10px auto 14px;
            border-radius: 99px;
            background: #18456f;
        }

        .stats-subtitle {
            margin: 0 auto;
            text-align: center;
            color: #151515;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.35;
        }

        .stats-carousel-controls {
            display: none;
        }

        .stats-grid {
            margin: 40px auto 0;
            max-width: 820px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .stats-card {
            background: #f8f8f8;
            border-radius: 16px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .16);
            padding: 16px 12px 12px;
            text-align: center;
            border: 1px solid #e3e3e3;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .stats-card h4 {
            margin: 0 0 14px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #18456f;
        }

        .stats-ratio-card {
            display: flex;
            min-height: 238px;
            flex-direction: column;
            justify-content: center;
        }

        .stats-ratio-body {
            display: grid;
            justify-items: center;
            gap: 12px;
        }

        .stats-ratio-value {
            color: var(--index-color, #18456f);
            font-family: 'Montserrat', sans-serif;
            font-size: 46px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        .stats-ratio-category {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 36px;
            padding: 8px 14px;
            border-radius: 999px;
            background: var(--index-soft-color, #eef2f5);
            color: var(--index-color, #18456f);
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
            text-align: center;
        }

        .stats-ratio-score {
            margin: 0;
            color: #5d6874;
            font-size: 11px;
            font-weight: 600;
        }

        .stats-ratio-scale {
            width: min(260px, 100%);
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 5px;
            margin-top: 4px;
        }

        .stats-ratio-scale span {
            position: relative;
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: color-mix(in srgb, var(--scale-color) 18%, #edf2f7);
            opacity: 1;
        }

        .stats-ratio-scale span::after {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: calc(var(--segment-fill, 0) * 100%);
            border-radius: inherit;
            background: var(--scale-color);
            box-shadow: 0 0 12px color-mix(in srgb, var(--scale-color) 26%, transparent);
        }

        .stats-ratio-card.index-poor {
            --index-color: #c73f3a;
            --index-soft-color: #fdeceb;
        }

        .stats-ratio-card.index-fair {
            --index-color: #b88800;
            --index-soft-color: #fff5cf;
        }

        .stats-ratio-card.index-good {
            --index-color: #40ab83;
            --index-soft-color: #e8f7f1;
        }

        .stats-ratio-card.index-excellent {
            --index-color: #23845f;
            --index-soft-color: #e2f3ec;
        }

        .stats-ratio-card.index-muted {
            --index-color: #6b7280;
            --index-soft-color: #eef2f5;
        }

        .stats-ratio-card.index-poor .scale-poor,
        .stats-ratio-card.index-fair .scale-fair,
        .stats-ratio-card.index-good .scale-good,
        .stats-ratio-card.index-excellent .scale-excellent {
            opacity: 1;
        }

        .stats-pie {
            width: 172px;
            height: 172px;
            margin: 0 auto 12px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transform: rotate(-125deg);
            animation: statsPieEnter 900ms ease-out both, statsPieFloat 4s ease-in-out 900ms infinite;
            transition: transform .35s ease, box-shadow .25s ease, filter .25s ease;
            will-change: transform;
            cursor: pointer;
        }

        .stats-pie::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 50%;
            pointer-events: none;
            opacity: 0;
            transform: scale(1.008);
            transition: opacity .22s ease, transform .22s ease;
            background: conic-gradient(
                from 0deg,
                transparent 0deg var(--hover-start, 0deg),
                color-mix(in srgb, var(--stats-highlight, #1d4b78) 96%, white) var(--hover-start, 0deg) var(--hover-end, 0deg),
                transparent var(--hover-end, 0deg) 360deg
            );
            filter: none;
        }

        .stats-card:hover .stats-pie {
            box-shadow: none;
        }

        .stats-pie.is-hovered {
            box-shadow: none;
            filter: saturate(1.04);
        }

        .stats-pie.is-hovered::after {
            opacity: 1;
            transform: scale(1.018);
        }

        .stats-legend {
            margin: 16px 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            grid-template-columns: repeat(2, max-content);
            align-items: center;
            justify-content: center;
            gap: 6px 10px;
            color: #111;
            font-size: 10px;
            font-weight: 500;
        }

        .stats-legend li {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            padding: 3px 5px;
            border-radius: 8px;
            transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease;
        }

        .stats-legend li:hover {
            background: #f1f4f8;
            transform: translateY(-1px);
        }

        .stats-legend li.is-active {
            background: #eef4fb;
            box-shadow: 0 4px 10px rgba(23, 71, 114, .16);
            transform: translateY(-2px);
        }

        .stats-legend i {
            width: 16px;
            height: 16px;
            border-radius: 5px;
            display: inline-block;
        }

        .stats-green { background: #40ab83; }
        .stats-blue { background: #1d4b78; }
        .stats-gray { background: #b7b7b7; }
        .stats-orange { background: #f89538; }

        .stats-pie-label {
            display: none;
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 4;
            max-width: 118px;
            text-align: center;
            line-height: 1.15;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .01em;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, .3);
            white-space: normal;
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease, transform .2s ease;
            transform: translate(-50%, -50%) rotate(125deg) scale(.92);
        }

        .stats-pie.is-hovered .stats-pie-label {
            opacity: 1;
            transform: translate(-50%, -50%) rotate(125deg) scale(1);
        }

        .stats-cursor-tooltip {
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            min-width: 92px;
            max-width: 220px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(23, 50, 75, .96);
            color: #ffffff;
            box-shadow: 0 12px 28px rgba(24, 69, 111, .24);
            font-size: 11px;
            font-weight: 700;
            line-height: 1.3;
            pointer-events: none;
            opacity: 0;
            transform: translate(14px, 12px) scale(.96);
            transition: opacity .12s ease, transform .12s ease;
        }

        .stats-cursor-tooltip.is-visible {
            opacity: 1;
            transform: translate(14px, 12px) scale(1);
        }

        .stats-cursor-tooltip strong {
            display: block;
            margin-bottom: 2px;
            font-size: 11px;
            line-height: 1.25;
        }

        .stats-cursor-tooltip span {
            display: block;
            color: rgba(255, 255, 255, .82);
            font-size: 10px;
            font-weight: 600;
        }

        .parameter-stats-panel {
            max-width: 1080px;
            margin: 34px auto 0;
            padding: 22px;
            border: 1px solid #d9dfe6;
            border-radius: 16px;
            background: #f8f8f8;
            box-shadow: 0 6px 18px rgba(18, 53, 88, .08);
        }

        .parameter-stats-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 20px;
        }

        .parameter-stats-copy h4 {
            margin: 0 0 8px;
            color: #18456f;
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.15;
        }

        .parameter-stats-copy p {
            margin: 0;
            color: #202020;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.5;
        }

        .parameter-stats-updated {
            display: inline-block;
            margin-top: 8px;
            color: #5f6b76;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.4;
        }

        .parameter-total-pill {
            min-width: 158px;
            padding: 11px 14px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #dce3ea;
            text-align: right;
            box-shadow: 0 4px 12px rgba(24, 69, 111, .08);
        }

        .parameter-total-pill span {
            display: block;
            margin-bottom: 2px;
            color: #5f6b76;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .02em;
        }

        .parameter-total-pill strong {
            color: #18456f;
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        .parameter-overview-bars {
            display: grid;
            gap: 10px;
            margin-bottom: 18px;
        }

        .parameter-bar-row {
            display: grid;
            grid-template-columns: minmax(138px, 1fr) minmax(180px, 2.1fr) 74px;
            gap: 12px;
            align-items: center;
            color: #17324b;
            font-size: 12px;
            font-weight: 700;
        }

        .parameter-bar-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .parameter-bar-track {
            height: 12px;
            overflow: hidden;
            border-radius: 999px;
            background: #dfe5eb;
        }

        .parameter-bar-fill {
            display: block;
            width: var(--bar-animated-width, var(--bar-width, 0%));
            height: 100%;
            border-radius: inherit;
            background: var(--bar-color, #1d4b78);
            box-shadow: 0 0 16px color-mix(in srgb, var(--bar-color, #1d4b78) 26%, transparent);
        }

        .parameter-bar-value {
            text-align: right;
            color: #18456f;
            font-variant-numeric: tabular-nums;
        }

        .parameter-detail-grid {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: center;
            gap: 14px;
        }

        .parameter-category-card {
            position: relative;
            flex: 1 1 260px;
            max-width: 320px;
            overflow: hidden;
            padding: 16px 14px 14px;
            border: 1px solid #e1e7ed;
            border-radius: 14px;
            background: #ffffff;
            text-align: left;
            box-shadow: 0 4px 12px rgba(24, 69, 111, .07);
            transition: transform .24s ease, box-shadow .24s ease, border-color .24s ease;
        }

        .parameter-category-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--category-color, #1d4b78);
        }

        .parameter-category-card::after {
            content: '';
            position: absolute;
            right: -36px;
            top: -46px;
            width: 126px;
            height: 126px;
            border-radius: 50%;
            background: color-mix(in srgb, var(--category-color, #1d4b78) 12%, transparent);
            pointer-events: none;
        }

        .parameter-category-card:hover,
        .parameter-category-card.is-open {
            border-color: color-mix(in srgb, var(--category-color, #1d4b78) 38%, #e1e7ed);
            box-shadow: 0 10px 24px rgba(24, 69, 111, .12);
            transform: translateY(-2px);
        }

        .parameter-category-head {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .parameter-category-head h5 {
            margin: 0;
            color: #18456f;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }

        .parameter-category-subtitle {
            display: block;
            margin-top: 5px;
            color: #65717d;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.35;
        }

        .parameter-category-count {
            flex: 0 0 auto;
            padding: 5px 9px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--category-color, #1d4b78) 12%, white);
            color: var(--category-color, #1d4b78);
            font-size: 11px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .parameter-category-summary {
            position: relative;
            z-index: 1;
            display: grid;
            gap: 12px;
            margin-top: 14px;
        }

        .parameter-category-meter {
            height: 10px;
            overflow: hidden;
            border-radius: 999px;
            background: #e8edf2;
        }

        .parameter-category-meter span {
            display: block;
            width: var(--bar-animated-width, var(--bar-width, 0%));
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--category-color, #1d4b78), color-mix(in srgb, var(--category-color, #1d4b78) 68%, #40ab83));
            box-shadow: 0 0 16px color-mix(in srgb, var(--category-color, #1d4b78) 30%, transparent);
        }

        .parameter-preview-list {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .parameter-preview-list li {
            max-width: 100%;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 8px;
            border-radius: 999px;
            background: #f3f6f8;
            color: #17324b;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.15;
        }

        .parameter-preview-list span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .parameter-preview-list strong {
            color: var(--category-color, #1d4b78);
            font-size: 10px;
        }

        .parameter-detail-toggle {
            width: 100%;
            min-height: 36px;
            border: 0;
            border-radius: 10px;
            background: var(--category-color, #1d4b78);
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
            box-shadow: 0 8px 18px color-mix(in srgb, var(--category-color, #1d4b78) 24%, transparent);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }

        .parameter-detail-toggle:hover {
            transform: translateY(-1px);
            filter: saturate(1.08);
            box-shadow: 0 12px 22px color-mix(in srgb, var(--category-color, #1d4b78) 28%, transparent);
        }

        .parameter-detail-toggle:focus-visible {
            outline: 3px solid color-mix(in srgb, var(--category-color, #1d4b78) 28%, white);
            outline-offset: 3px;
        }

        .parameter-detail-toggle .parameter-toggle-close,
        .parameter-category-card.is-open .parameter-detail-toggle .parameter-toggle-open {
            display: none;
        }

        .parameter-detail-toggle .parameter-toggle-open,
        .parameter-detail-toggle .parameter-toggle-close {
            align-items: center;
            gap: 7px;
        }

        .parameter-category-card.is-open .parameter-detail-toggle .parameter-toggle-close {
            display: inline-flex;
        }

        .parameter-detail-toggle .parameter-toggle-open {
            display: inline-flex;
        }

        .parameter-detail-toggle .parameter-toggle-icon,
        .parameter-detail-toggle .parameter-toggle-chevron {
            font-size: 14px;
            line-height: 1;
        }

        .parameter-detail-toggle .parameter-toggle-chevron {
            transition: transform .24s ease;
        }

        .parameter-category-card.is-open .parameter-detail-toggle .parameter-toggle-chevron {
            transform: rotate(180deg);
        }

        .parameter-category-detail {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 170px minmax(0, 1fr);
            align-items: start;
            gap: 14px;
            max-height: 0;
            min-height: 0;
            opacity: 0;
            overflow: hidden;
            transform: translateY(-8px);
            transition: max-height .36s ease, opacity .28s ease, transform .32s ease, margin-top .32s ease;
        }

        .parameter-category-card.is-open .parameter-category-detail {
            max-height: 560px;
            min-height: 170px;
            opacity: 1;
            transform: translateY(0);
            margin-top: 16px;
        }

        .parameter-category-pie {
            width: 150px;
            height: 150px;
            margin: 0 auto;
            animation: none;
        }

        .parameter-category-card.is-open .parameter-category-pie {
            animation: statsPieEnter 720ms ease-out both, statsPieFloat 4s ease-in-out 720ms infinite;
        }

        .parameter-detail-list {
            grid-template-columns: 1fr;
            justify-content: stretch;
            gap: 7px;
            max-height: 214px;
            overflow-y: auto;
            padding-right: 3px;
            margin-top: 0;
        }

        .parameter-segment-list {
            max-height: 132px;
        }

        .parameter-segment-list.is-hidden-source {
            display: none;
        }

        .parameter-pie-labels {
            display: none;
        }

        .parameter-pie-segment-label {
            position: absolute;
            left: var(--label-x, 50%);
            top: var(--label-y, 50%);
            max-width: 92px;
            padding: 3px 6px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .92);
            color: #17324b;
            font-size: 9px;
            font-weight: 800;
            line-height: 1.15;
            text-align: center;
            box-shadow: 0 5px 12px rgba(24, 69, 111, .14);
            transform: translate(-50%, -50%);
            opacity: .96;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .parameter-detail-list li {
            display: grid;
            grid-template-columns: 16px minmax(0, 1fr) auto;
            align-items: center;
            gap: 8px;
            padding: 6px 7px;
            border-radius: 9px;
            background: #f5f7f9;
            text-align: left;
        }

        .parameter-detail-list li.is-active {
            background: #eef4fb;
        }

        .parameter-detail-name {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .parameter-detail-list strong {
            color: #18456f;
            font-size: 11px;
            font-weight: 800;
        }

        .parameter-detail-side {
            min-width: 0;
            width: 100%;
            align-self: start;
            display: grid;
            gap: 10px;
        }

        .parameter-parameter-blocks {
            min-height: 150px;
            max-height: 210px;
            overflow-y: auto;
            padding-top: 2px;
        }

        .parameter-parameter-list {
            display: none;
            gap: 7px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .parameter-parameter-list.is-active {
            display: grid;
        }

        .parameter-parameter-list li {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            align-items: center;
            padding: 7px 9px;
            border-radius: 9px;
            background: #f5f7f9;
            color: #17324b;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.25;
        }

        .parameter-parameter-list span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .parameter-parameter-list strong {
            color: #18456f;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .parameter-active-title {
            margin: 0;
            color: #18456f;
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.2;
        }

        .parameter-empty-sublist {
            padding: 12px 10px;
            border: 1px dashed #c9d3dd;
            border-radius: 10px;
            background: #ffffff;
            color: #65717d;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
        }

        .parameter-empty {
            display: grid;
            justify-items: center;
            gap: 8px;
            padding: 24px 16px;
            border: 1px dashed #c9d3dd;
            border-radius: 14px;
            background: #ffffff;
            color: #5f6b76;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
        }

        .parameter-empty i {
            color: #18456f;
            font-size: 28px;
        }

        @keyframes statsPieEnter {
            0% {
                opacity: 0;
                transform: rotate(-220deg) scale(0.78);
            }
            100% {
                opacity: 1;
                transform: rotate(-125deg) scale(1);
            }
        }

        @keyframes statsPieFloat {
            0%, 100% {
                transform: rotate(-125deg) translateY(0);
            }
            50% {
                transform: rotate(-121deg) translateY(-4px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .stats-pie {
                animation: none;
            }
        }

        .berita-wrap {
            width: min(1240px, calc(100% - 120px));
            margin: 42px auto 0;
            padding: 20px 20px 28px;
            background: #f8f8f8;
            border: 1px solid #d9dfe6;
            border-radius: 18px;
            box-shadow: 0 6px 18px rgba(18, 53, 88, .08);
        }

        .berita-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .berita-title {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            font-size: 25px;
            font-weight: 700;
            line-height: 1.08;
            color: #18456f;
        }

        .berita-underline {
            width: 110px;
            height: 4px;
            margin: 8px 0 8px;
            border-radius: 99px;
            background: #18456f;
        }

        .berita-subtitle {
            margin: 0;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.45;
            color: #222;
        }

        .berita-more {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 118px;
            min-height: 31px;
            padding: 0 20px;
            border-radius: 999px;
            background: #3caf83;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 6px 12px rgba(54, 150, 114, .24);
        }

        .berita-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.14fr) minmax(0, .98fr);
            gap: 26px;
            align-items: stretch;
        }

        .berita-feature {
            position: relative;
            min-height: 336px;
            overflow: hidden;
            border-radius: 18px;
            box-shadow: 0 4px 12px rgba(23, 71, 114, .15);
            background: #153d67;
        }

        .berita-feature a,
        .berita-card a {
            color: inherit;
            text-decoration: none;
        }

        .berita-feature img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .berita-feature::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(12, 40, 70, .06) 0%, rgba(13, 52, 96, .68) 70%, rgba(15, 61, 108, .86) 100%);
        }

        .berita-feature-content {
            position: absolute;
            inset: auto 22px 18px 22px;
            z-index: 1;
            color: #fff;
        }

        .berita-feature h4 {
            margin: 0 0 12px;
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.16;
        }

        .berita-meta {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
            color: #46c28f;
            font-size: 11px;
            font-weight: 500;
        }

        .berita-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .berita-side {
            display: grid;
            gap: 14px;
        }

        .berita-card {
            min-height: 103px;
            padding: 14px 18px;
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e6eaef;
            box-shadow: 0 4px 10px rgba(18, 53, 88, .14);
        }

        .berita-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 17px;
            padding: 0 8px;
            border-radius: 999px;
            background: #9be0bc;
            color: #fff;
            font-size: 8px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 8px;
        }

        .berita-card h5 {
            margin: 0 0 6px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
            color: #18456f;
        }

        .berita-card p {
            margin: 0 0 10px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.42;
            color: #5a5a5a;
        }

        .berita-card .berita-meta {
            gap: 16px;
            font-size: 10px;
        }

        .sosmed-wrap {
            width: min(1240px, calc(100% - 120px));
            margin: 44px auto 0;
            padding: 0;
            background: transparent;
            border: 0;
            border-radius: 0;
            box-shadow: none;
        }

        .sosmed-header {
            text-align: center;
        }

        .sosmed-title {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.08;
            color: #18456f;
        }

        .sosmed-underline {
            width: 170px;
            height: 4px;
            border-radius: 99px;
            margin: 8px auto 12px;
            background: #18456f;
        }

        .sosmed-subtitle {
            margin: 0;
            color: #1f1f1f;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.45;
        }

        .sosmed-platforms {
            width: min(760px, 100%);
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            margin: 22px auto 26px;
            padding: 8px 10px;
            border-radius: 999px;
            background: linear-gradient(180deg, #efefef 0%, #e4e4e4 100%);
            border: 1px solid #dedede;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7);
        }

        .sosmed-platform-pill {
            appearance: none;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            min-height: 42px;
            padding: 0 14px;
            border-radius: 999px;
            background: transparent;
            color: #aeb2b8;
            font-size: 11px;
            font-weight: 700;
            box-shadow: none;
            transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease, color .2s ease, border-color .2s ease;
            cursor: pointer;
        }

        .sosmed-platform-pill i {
            font-size: 14px;
        }

        .sosmed-platform-pill:hover {
            color: #7f8c99;
        }

        .sosmed-platform-pill.is-active {
            background: linear-gradient(180deg, #ffffff 0%, #f7f9fc 100%);
            border-color: #d7dde6;
            color: #1d4b78;
            box-shadow: 0 8px 16px rgba(18, 53, 88, .14);
            transform: translateY(-1px);
        }

        .sosmed-platform-pill.is-empty {
            opacity: 1;
            background: transparent;
            box-shadow: none;
        }

        .sosmed-platform-pill.is-empty.is-active {
            background: linear-gradient(180deg, #ffffff 0%, #f7f9fc 100%);
            border-color: #d7dde6;
            color: #1d4b78;
            box-shadow: 0 8px 16px rgba(18, 53, 88, .14);
            transform: translateY(-1px);
        }

        .sosmed-format-note {
            width: max-content;
            max-width: 100%;
            margin: 0 auto 18px;
            padding: 10px 16px;
            border-radius: 999px;
            background: #eef4f9;
            color: #4c6279;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .sosmed-panels {
            position: relative;
        }

        .sosmed-mobile-nav {
            display: none;
        }

        .sosmed-panel {
            display: none;
        }

        .sosmed-panel.is-active {
            display: block;
        }

        .sosmed-panel-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .sosmed-panel-header strong {
            display: block;
            color: #18456f;
            font-family: 'Montserrat', sans-serif;
            font-size: 20px;
            font-weight: 700;
        }

        .sosmed-panel-header span {
            display: inline-block;
            margin-top: 6px;
            color: #61768b;
            font-size: 12px;
            font-weight: 600;
        }

        .sosmed-carousel {
            --carousel-gap: 18px;
            --carousel-side-space: 32px;
            --visible-card-width: 310px;
            position: relative;
            width: min(100%, calc((var(--visible-card-width) * 3) + (var(--carousel-gap) * 2) + (var(--carousel-side-space) * 2)));
            margin: 0 auto;
            padding: 0 var(--carousel-side-space);
            box-sizing: border-box;
        }

        .sosmed-carousel-viewport {
            width: 100%;
            overflow: hidden;
        }

        .sosmed-carousel-hover-zone {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 88px;
            z-index: 1;
            background: transparent;
        }

        .sosmed-carousel-hover-zone.is-left {
            left: 22px;
        }

        .sosmed-carousel-hover-zone.is-right {
            right: 22px;
        }

        .sosmed-carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 999px;
            background: #ffffff;
            color: #18456f;
            box-shadow: 0 10px 20px rgba(18, 53, 88, .16);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2;
            transition: background-color .2s ease, color .2s ease, transform .2s ease;
        }

        .sosmed-carousel-btn:hover {
            background: #18456f;
            color: #ffffff;
            transform: translateY(-50%) scale(1.04);
        }

        .sosmed-carousel-btn.is-prev {
            left: 0;
        }

        .sosmed-carousel-btn.is-next {
            right: 0;
        }

        .sosmed-carousel.is-scrollable .sosmed-carousel-btn {
            display: inline-flex;
        }

        .sosmed-carousel-btn.is-hidden {
            display: none !important;
        }

        .sosmed-grid {
            display: flex;
            gap: var(--carousel-gap);
            align-items: stretch;
            justify-content: flex-start;
            width: max-content;
            min-width: 100%;
            box-sizing: border-box;
            padding: 0;
            transition: transform .35s ease;
            will-change: transform;
        }

        .sosmed-carousel:not(.is-scrollable) .sosmed-grid {
            width: 100%;
            justify-content: center;
        }

        .sosmed-grid > .sosmed-card {
            flex: 0 0 auto;
        }

        .sosmed-grid-instagram > .sosmed-card {
            width: min(310px, calc((100% - 36px) / 3));
        }

        .sosmed-grid-facebook > .sosmed-card,
        .sosmed-grid-tiktok > .sosmed-card {
            width: min(220px, calc((100% - 36px) / 3));
        }

        .sosmed-grid-youtube > .sosmed-card {
            width: min(380px, calc((100% - 36px) / 3));
        }

        .sosmed-card {
            --social-accent: #18456f;
            overflow: hidden;
            border-radius: 24px;
            background: #fff;
            border: 1px solid #e5e9ef;
            box-shadow: 0 14px 28px rgba(18, 53, 88, .10);
            display: flex;
            flex-direction: column;
        }

        .sosmed-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px 14px;
            border-bottom: 1px solid #eef3f7;
        }

        .sosmed-card-platform {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--social-accent);
            font-size: 15px;
            font-weight: 700;
        }

        .sosmed-card-platform i {
            font-size: 18px;
        }

        .sosmed-card-date {
            color: #728395;
            font-size: 12px;
            font-weight: 600;
            text-align: right;
        }

        .sosmed-embed-stage {
            padding: 18px 20px 0;
        }

        .sosmed-embed-shell {
            position: relative;
            overflow: hidden;
            width: 100%;
            border-radius: 22px;
            background: #dfe9f2;
            border: 1px solid #d6e0ea;
        }

        .sosmed-card-instagram .sosmed-embed-shell {
            --instagram-scale: 0.82;
            width: min(100%, calc(360px * var(--instagram-scale)));
            max-width: calc(360px * var(--instagram-scale));
            height: calc(610px * var(--instagram-scale));
            min-height: calc(610px * var(--instagram-scale));
            aspect-ratio: auto;
            margin: 0 auto;
            background: transparent;
            border: 0;
            border-radius: 12px;
            box-shadow: none;
            overflow: hidden;
        }

        .sosmed-card-instagram {
            background: transparent;
            border: 0;
            box-shadow: none;
            border-radius: 12px;
        }

        .sosmed-card-instagram .sosmed-embed-stage {
            padding: 0;
            display: flex;
            justify-content: center;
        }

        .sosmed-card-instagram .sosmed-embed-shell iframe {
            width: 360px;
            min-width: 360px;
            height: 610px;
            min-height: 610px;
            overflow: hidden;
            zoom: var(--instagram-scale);
        }

        .sosmed-card-facebook {
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .sosmed-card-facebook .sosmed-embed-stage {
            padding: 0;
        }

        .sosmed-card-facebook .sosmed-embed-shell {
            --facebook-scale: 0.86;
            max-width: 220px;
            aspect-ratio: 9 / 16;
            margin: 0 auto;
            overflow: hidden;
            isolation: isolate;
            border-radius: 12px;
            background: #000;
            border: 0;
            box-shadow: none;
        }

        .sosmed-card-facebook .sosmed-embed-shell iframe {
            width: calc(100% / var(--facebook-scale));
            height: calc(100% / var(--facebook-scale));
            transform: scale(var(--facebook-scale));
            transform-origin: top left;
            border-radius: inherit;
        }

        .sosmed-card-youtube .sosmed-embed-shell {
            aspect-ratio: 16 / 9;
            border-radius: 12px;
            border: 0;
            background: #000;
            box-shadow: 0 16px 32px rgba(14, 36, 61, .16);
        }

        .sosmed-card-tiktok .sosmed-embed-shell {
            --tiktok-scale: 0.86;
            max-width: 220px;
            aspect-ratio: 9 / 16;
            margin: 0 auto;
            border-radius: 18px;
            overflow: hidden;
            isolation: isolate;
        }

        .sosmed-card-tiktok .sosmed-embed-shell iframe {
            width: calc(100% / var(--tiktok-scale));
            height: calc(100% / var(--tiktok-scale));
            transform: scale(var(--tiktok-scale));
            transform-origin: top left;
            border-radius: inherit;
        }

        .sosmed-card-tiktok {
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .sosmed-card-tiktok .sosmed-embed-stage {
            padding: 0;
        }

        .sosmed-card-youtube {
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .sosmed-card-youtube .sosmed-embed-stage {
            padding: 0;
        }

        .sosmed-embed-shell iframe,
        .sosmed-embed-shell a {
            width: 100%;
            height: 100%;
            display: block;
            border: 0;
        }

        .sosmed-instagram-native {
            padding: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .sosmed-instagram-native .sosmed-embed-stage {
            padding: 0;
        }

        .ig-post-card {
            width: min(295px, 100%);
            margin: 0 auto;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #dce5ef;
            box-shadow: 0 10px 22px rgba(14, 36, 61, .18);
        }

        .ig-post-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px 10px;
            background: #ffffff;
        }

        .ig-post-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #f1b4cd;
            padding: 2px;
            background: #fff;
            flex: 0 0 auto;
        }

        .ig-post-avatar img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
            display: block;
        }

        .ig-post-user {
            min-width: 0;
        }

        .ig-post-user strong {
            display: block;
            color: #222b36;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ig-post-user span {
            display: block;
            margin-top: 1px;
            color: #7d8792;
            font-size: 10px;
            font-weight: 500;
            line-height: 1.15;
        }

        .ig-post-media {
            position: relative;
            aspect-ratio: 4 / 5;
            overflow: hidden;
            background: linear-gradient(180deg, #3d5f85 0%, #7392b1 100%);
        }

        .ig-post-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .ig-post-corner {
            position: absolute;
            top: 6px;
            width: 22px;
            height: 22px;
            border-radius: 4px;
            background: rgba(255, 255, 255, .95);
            padding: 2px;
            z-index: 2;
        }

        .ig-post-corner.left { left: 6px; }
        .ig-post-corner.right { right: 6px; }

        .ig-post-corner img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .ig-post-announce {
            position: absolute;
            left: 14px;
            top: 48px;
            z-index: 2;
            margin: 0;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            font-size: 44px;
            font-weight: 800;
            line-height: .95;
            letter-spacing: .01em;
            text-shadow: -2px 0 #2d3e75, 0 2px #2d3e75, 2px 0 #2d3e75, 0 -2px #2d3e75;
        }

        .ig-post-caption {
            margin: 0 10px;
            margin-top: -2px;
            padding: 8px 10px 11px;
            border-radius: 0 0 10px 10px;
            background: #1f4e79;
            color: #fff;
            text-align: center;
        }

        .ig-post-caption small,
        .ig-post-caption strong {
            display: block;
            line-height: 1.16;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ig-post-caption small {
            font-size: 8px;
            font-weight: 500;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .ig-post-caption strong {
            margin-top: 4px;
            font-family: 'Montserrat', sans-serif;
            font-size: 12px;
            font-weight: 800;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .ig-post-strip {
            height: 28px;
            margin: 0 10px;
            background: linear-gradient(90deg, #7090b1 0%, #9eb2c7 100%);
        }

        .ig-post-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 14px 12px;
            color: #232a33;
        }

        .ig-post-actions span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1;
        }

        .ig-post-actions i {
            font-size: 18px;
            line-height: 1;
        }

        .sosmed-empty {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 24px;
            text-align: center;
            background: linear-gradient(180deg, rgba(255, 255, 255, .66) 0%, rgba(232, 240, 248, .92) 100%);
        }

        .sosmed-empty i {
            color: var(--social-accent);
            font-size: 32px;
        }

        .sosmed-empty strong {
            margin: 0;
            color: #18456f;
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
        }

        .sosmed-empty p {
            margin: 0;
            color: #5d6e7e;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.5;
        }

        .sosmed-card-body {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 18px 20px 20px;
            flex: 1;
        }

        .sosmed-card-body h4 {
            margin: 0;
            color: #18456f;
            font-family: 'Montserrat', sans-serif;
            font-size: 19px;
            font-weight: 700;
            line-height: 1.28;
        }

        .sosmed-card-body p {
            margin: 0;
            color: #48596b;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.6;
        }

        .sosmed-card-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-top: auto;
            flex-wrap: wrap;
        }

        .sosmed-card-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            color: #6f8193;
            font-size: 12px;
            font-weight: 600;
        }

        .sosmed-card-meta span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .sosmed-card-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--social-accent);
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
        }

        .sosmed-empty-panel {
            max-width: 520px;
            margin: 0 auto;
            padding: 28px 22px;
            border-radius: 22px;
            border: 1px dashed #cad8e6;
            background: #f7fafc;
            text-align: center;
            color: #5f7387;
        }

        .sosmed-empty-panel i {
            font-size: 34px;
            color: #18456f;
            margin-bottom: 12px;
            display: block;
        }

        .sosmed-empty-panel strong {
            display: block;
            color: #18456f;
            font-family: 'Montserrat', sans-serif;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        @media (max-width: 1199.98px) {
            .layanan-wrap {
                width: min(1100px, calc(100% - 72px));
            }

            .layanan-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .stats-wrap {
                width: min(1040px, calc(100% - 56px));
            }

            .stats-grid {
                gap: 20px;
            }

            .stats-pie {
                width: 156px;
                height: 156px;
            }

            .parameter-stats-panel {
                padding: 20px;
            }

            .parameter-bar-row {
                grid-template-columns: minmax(120px, 1fr) minmax(150px, 1.6fr) 68px;
            }

            .berita-wrap {
                width: min(1040px, calc(100% - 92px));
            }

            .berita-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .sosmed-wrap {
                width: min(1080px, calc(100% - 72px));
            }

            .sosmed-grid-youtube {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .layanan-wrap {
                margin: 30px 12px 0;
                padding: 22px 14px 24px;
                width: auto;
            }

            .layanan-head {
                display: grid;
                grid-template-columns: 1fr;
                gap: 8px;
                justify-items: center;
                text-align: center;
            }

            .layanan-head > div:first-child {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: fit-content;
                max-width: 100%;
            }

            .layanan-title {
                font-size: 22px;
                line-height: 1.02;
                text-align: center;
            }

            .layanan-underline {
                width: 100%;
                height: 3px;
                margin-top: 7px;
                transform: scaleX(1);
                transform-origin: center;
                will-change: transform;
                transition: transform .58s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .layanan-wrap.is-scrolling .layanan-underline {
                transform: scaleX(0.62);
            }

            .layanan-badge {
                text-align: justify;
                font-size: 12px;
                line-height: 1.35;
                max-width: 320px;
                margin: 2px auto 0;
                position: static;
            }

            .layanan-desc {
                font-size: 14px;
                max-width: 320px;
                line-height: 1.55;
                text-align: justify;
                text-justify: inter-word;
                margin: 6px auto 20px;
            }

            .layanan-desc br {
                display: none;
            }

            .layanan-grid {
                display: flex;
                overflow-x: auto;
                overflow-y: visible;
                gap: 12px;
                padding: 18px 4px 10px;
                margin-top: -16px;
                scroll-snap-type: x proximity;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }

            .layanan-grid::-webkit-scrollbar {
                display: none;
            }

            .layanan-card {
                --wave-scale: 1;
                --wave-shift-y: 0px;
                flex: 0 0 calc(50% - 6px);
                min-width: 160px;
                min-height: 192px;
                padding: 12px 9px 10px;
                border-radius: 12px;
                scroll-snap-align: start;
                transform: translateY(var(--wave-shift-y)) scale(var(--wave-scale));
                transition:
                    transform .38s cubic-bezier(0.22, 1, 0.36, 1),
                    box-shadow .35s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .layanan-iconbox {
                width: 44px;
                height: 44px;
                border-radius: 10px;
                font-size: 22px;
                margin-bottom: 8px;
            }

            .layanan-card h5 {
                font-size: 15px;
                line-height: 1.18;
            }

            .layanan-card p {
                font-size: 11px;
            }

            .stats-wrap {
                width: auto;
                margin: 30px 12px 0;
            }

            .parameter-stats-panel {
                margin-top: 24px;
                padding: 16px 12px;
                border-radius: 14px;
            }

            .parameter-stats-head {
                display: grid;
                grid-template-columns: 1fr;
                gap: 12px;
                margin-bottom: 16px;
            }

            .parameter-stats-copy h4 {
                font-size: 15px;
            }

            .parameter-stats-copy p {
                font-size: 12px;
            }

            .parameter-stats-updated {
                font-size: 10px;
            }

            .parameter-total-pill {
                width: 100%;
                min-width: 0;
                text-align: left;
            }

            .parameter-overview-bars {
                gap: 12px;
            }

            .parameter-bar-row {
                grid-template-columns: 1fr auto;
                gap: 7px 10px;
            }

            .parameter-bar-track {
                grid-column: 1 / -1;
                height: 10px;
            }

            .parameter-detail-grid {
                display: grid;
                grid-template-columns: 1fr;
            }

            .parameter-category-card {
                max-width: none;
                padding: 14px 12px;
                border-radius: 12px;
            }

            .parameter-category-detail {
                grid-template-columns: 1fr;
                gap: 12px;
                justify-items: center;
                align-items: start;
            }

            .parameter-category-pie {
                width: 136px;
                height: 136px;
            }

            .parameter-detail-list {
                width: 100%;
                max-height: 190px;
            }

            .parameter-pie-segment-label {
                max-width: 78px;
                font-size: 8px;
                padding: 3px 5px;
            }

            .parameter-segment-list {
                max-height: 156px;
            }

            .parameter-parameter-blocks {
                width: 100%;
                min-height: 136px;
                max-height: 180px;
            }

            .berita-wrap {
                width: auto;
                margin: 28px 22px 0;
                padding: 18px 14px 18px;
                border-radius: 16px;
            }

            .berita-head {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                margin-bottom: 16px;
            }

            .berita-title {
                font-size: 22px;
            }

            .berita-underline {
                width: 92px;
            }

            .berita-more {
                min-width: 108px;
            }

            .berita-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .berita-feature {
                min-height: 260px;
                border-radius: 16px;
            }

            .berita-feature-content {
                inset: auto 16px 14px 16px;
            }

            .berita-feature h4 {
                font-size: 16px;
            }

            .berita-card {
                min-height: auto;
                padding: 13px 14px;
                border-radius: 14px;
            }

            .sosmed-wrap {
                width: auto;
                margin: 32px 22px 0;
                padding: 0;
            }

            .sosmed-mobile-nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
                margin: 18px 4px 14px;
            }

            .sosmed-mobile-title {
                margin: 0;
                color: #18456f;
                font-family: 'Montserrat', sans-serif;
                font-size: 16px;
                font-weight: 700;
                line-height: 1.1;
            }

            .sosmed-mobile-actions {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                flex-shrink: 0;
            }

            .sosmed-mobile-btn {
                width: 32px;
                height: 32px;
                border: 1px solid rgba(24, 69, 111, 0.14);
                border-radius: 999px;
                background: #ffffff;
                color: #18456f;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 6px 16px rgba(24, 69, 111, 0.12);
                transition: opacity .24s ease, transform .24s ease, background-color .24s ease;
            }

            .sosmed-mobile-btn i {
                font-size: 14px;
                line-height: 1;
            }

            .sosmed-mobile-btn.is-disabled {
                opacity: .35;
                pointer-events: none;
            }

            .sosmed-title {
                font-size: 20px;
            }

            .sosmed-underline {
                width: 136px;
            }

            .sosmed-platforms {
                display: none;
            }

            .sosmed-platform-pill {
                min-height: 42px;
                padding: 0 12px;
                font-size: 11px;
            }

            .sosmed-panels {
                display: flex;
                gap: 16px;
                overflow-x: auto;
                overflow-y: hidden;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }

            .sosmed-panels::-webkit-scrollbar {
                display: none;
            }

            .sosmed-panel,
            .sosmed-panel.is-active {
                display: block;
                flex: 0 0 100%;
                min-width: 100%;
                scroll-snap-align: start;
            }

            .sosmed-format-note {
                width: 100%;
                margin-bottom: 16px;
            }

            .sosmed-grid-instagram,
            .sosmed-grid-facebook,
            .sosmed-grid-youtube,
            .sosmed-grid-tiktok {
                gap: 14px;
                padding: 0;
            }

            .sosmed-grid-instagram > .sosmed-card,
            .sosmed-grid-youtube > .sosmed-card,
            .sosmed-grid-tiktok > .sosmed-card {
                width: min(86vw, 320px);
            }

            .sosmed-grid-facebook > .sosmed-card,
            .sosmed-grid-tiktok > .sosmed-card {
                width: min(92vw, 340px);
            }

            .sosmed-carousel {
                width: 100%;
                padding: 0;
            }

            .sosmed-carousel-viewport {
                overflow: visible;
                display: flex;
                justify-content: center;
            }

            .sosmed-grid {
                min-width: 0;
                width: 100%;
                display: grid;
                gap: 16px;
                justify-items: center;
            }

            .sosmed-grid > .sosmed-card {
                display: none;
                width: 100% !important;
                max-width: 100%;
                margin: 0 auto;
            }

            .sosmed-grid > .sosmed-card:nth-child(-n+2) {
                display: block;
            }

            .sosmed-grid-instagram > .sosmed-card:nth-child(-n+2) {
                width: min(86vw, 320px) !important;
                max-width: min(86vw, 320px);
                justify-self: center;
            }

            .sosmed-carousel-hover-zone {
                display: none;
            }

            .sosmed-carousel-btn {
                display: none !important;
            }

            .sosmed-card-top {
                padding: 16px 16px 12px;
                align-items: flex-start;
                flex-direction: column;
            }

            .sosmed-card-date {
                text-align: left;
            }

            .sosmed-embed-stage {
                padding: 14px 16px 0;
            }

            .sosmed-card-instagram .sosmed-embed-shell {
                --instagram-scale: 0.78;
                width: min(100%, calc(360px * var(--instagram-scale)));
                max-width: calc(360px * var(--instagram-scale));
                height: calc(610px * var(--instagram-scale));
                min-height: calc(610px * var(--instagram-scale));
                margin: 0 auto;
            }

            .sosmed-card-instagram .sosmed-embed-shell iframe {
                width: 360px;
                min-width: 360px;
                height: 610px;
                min-height: 610px;
            }

            .sosmed-card-instagram .sosmed-embed-stage {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            .sosmed-instagram-native .sosmed-embed-stage {
                padding: 0;
            }

            .sosmed-card-body {
                padding: 16px;
            }

            .sosmed-card-body h4 {
                font-size: 17px;
            }

            .sosmed-card-facebook .sosmed-embed-shell {
                --facebook-scale: 1;
                width: min(100%, 340px);
                max-width: min(100%, 340px);
            }

            .sosmed-card-instagram .sosmed-embed-shell,
            .sosmed-card-facebook .sosmed-embed-shell,
            .sosmed-card-tiktok .sosmed-embed-shell {
                max-width: 100%;
            }

            .sosmed-card-tiktok .sosmed-embed-shell {
                --tiktok-scale: 1;
                width: min(100%, 340px);
            }

            .sosmed-card-tiktok .sosmed-embed-shell iframe {
                width: 100%;
                height: 100%;
                transform: none;
            }

            .ig-post-card {
                width: min(295px, 100%);
            }

            .ig-post-announce {
                font-size: 36px;
                top: 44px;
            }

            .stats-title {
                font-size: 22px;
                line-height: 1.02;
            }

            .stats-underline {
                width: 92px;
                height: 3px;
                margin: 7px auto 10px;
                transform: scaleX(1);
                transform-origin: center;
                transition: transform .58s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .stats-wrap.is-scrolling .stats-underline {
                transform: scaleX(0.62);
            }

            .stats-subtitle {
                font-size: 13px;
            }

            .stats-carousel-controls {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin: 10px 2px 14px;
            }

            .stats-carousel-btn {
                width: 32px;
                height: 32px;
                border: 1px solid rgba(24, 69, 111, 0.14);
                border-radius: 999px;
                background: #ffffff;
                color: #18456f;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 6px 16px rgba(24, 69, 111, 0.12);
                transition: opacity .24s ease, transform .24s ease, background-color .24s ease;
            }

            .stats-carousel-btn i {
                font-size: 14px;
                line-height: 1;
            }

            .stats-carousel-btn.is-disabled {
                opacity: .35;
                pointer-events: none;
            }

            .stats-grid {
                display: flex;
                overflow-x: auto;
                overflow-y: visible;
                gap: 14px;
                margin-top: 20px;
                padding: 2px 2px 10px;
                scroll-snap-type: x proximity;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }

            .stats-grid::-webkit-scrollbar {
                display: none;
            }

            .stats-card {
                flex: 0 0 100%;
                min-width: 100%;
                padding: 16px 12px;
                border-radius: 14px;
                max-width: none;
                scroll-snap-align: start;
            }

            .stats-card h4 {
                font-size: 14px;
                margin-bottom: 12px;
            }

            .stats-ratio-card {
                min-height: 210px;
            }

            .stats-ratio-value {
                font-size: 38px;
            }

            .stats-ratio-category {
                min-height: 34px;
                padding: 7px 12px;
                font-size: 11px;
            }

            .stats-pie {
                width: 144px;
                height: 144px;
                margin-bottom: 14px;
            }

            .stats-legend {
                grid-template-columns: repeat(2, max-content);
                font-size: 10px;
                gap: 8px 12px;
            }

            .stats-legend i {
                width: 16px;
                height: 16px;
            }
        }
    </style>

    <section class="layanan-wrap reveal">
        <div class="layanan-head">
            <div>
                <h2 class="layanan-title">Layanan Kami</h2>
                <div class="layanan-underline"></div>
            </div>
            <div class="layanan-badge">Standar ISO/IEC 17025</div>
        </div>

        <p class="layanan-desc">
            Balai Kesehatan dan Keselamatan Kerja Surabaya menyediakan berbagai layanan pengujian<br>
            untuk memastikan lingkungan kerja yang aman dan sehat sesuai dengan standar K3.
        </p>

        <div class="layanan-grid">
            <article class="layanan-card">
                <div class="layanan-iconbox"><i class="bi bi-wind"></i></div>
                <h5>Ambien</h5>
                <p>Pengujian Kualitas udara ambien untuk memastikan kepatuhan terhadap regulasi lingkungan.</p>
            </article>
            <article class="layanan-card">
                <div class="layanan-iconbox"><i class="bi bi-cloud-fill"></i></div>
                <h5>Emisi</h5>
                <p>Pengujian emisi industri guna memastikan kepatuhan terhadap regulasi lingkungan.</p>
            </article>
            <article class="layanan-card">
                <div class="layanan-iconbox"><i class="bi bi-heart-pulse-fill"></i></div>
                <h5>Kesehatan</h5>
                <p>Layanan pemeriksaan dan pemantauan kesehatan tenaga kerja secara berkala.</p>
            </article>
            <article class="layanan-card">
                <div class="layanan-iconbox"><i class="bi bi-building"></i></div>
                <h5>Lingkungan Kerja</h5>
                <p>Penilaian kondisi lingkungan kerja untuk menciptakan area kerja yang aman dan nyaman.</p>
            </article>
            <article class="layanan-card">
                <div class="layanan-iconbox"><i class="bi bi-person-video3"></i></div>
                <h5>Pelatihan</h5>
                <p>Program pelatihan K3 untuk meningkatkan kompetensi dan kepatuhan tenaga kerja.</p>
            </article>
        </div>
    </section>

    <section id="data-statistik-layanan" class="stats-wrap reveal">
        <h3 class="stats-title">Data & Statistik Layanan</h3>
        <div class="stats-underline"></div>
        <p class="stats-subtitle">Berbagai indikator kinerja yang menunjukkan kualitas pelayanan dan transparansi Balai K3 Surabaya.</p>
        <div class="stats-carousel-controls" aria-label="Navigasi chart statistik">
            <button type="button" class="stats-carousel-btn" data-stats-scroll="prev" aria-label="Geser chart ke kiri">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button type="button" class="stats-carousel-btn" data-stats-scroll="next" aria-label="Geser chart ke kanan">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <div class="stats-grid">
            @php
                $indexScaleColors = [
                    'poor' => '#c73f3a',
                    'fair' => '#f2c94c',
                    'good' => '#40ab83',
                    'excellent' => '#23845f',
                ];
                $ikmIndex = $statsPie['ikm']['index'] ?? [];
                $ikkIndex = $statsPie['ikk']['index'] ?? [];
            @endphp

            <article class="stats-card stats-ratio-card index-{{ $ikmIndex['class'] ?? 'muted' }}">
                <h4>Indeks Kepuasan Pelanggan</h4>
                <div class="stats-ratio-body">
                    <div
                        class="stats-ratio-value"
                        data-count-up
                        data-count-value="{{ is_null($ikmIndex['ratio'] ?? null) ? '' : number_format((float) $ikmIndex['ratio'], 1, '.', '') }}"
                    >{{ $ikmIndex['ratio_label'] ?? '-' }}</div>
                    <div class="stats-ratio-category">{{ $ikmIndex['category'] ?? 'Belum ada data' }}</div>
                    <p class="stats-ratio-score">
                        Nilai indeks:
                        {{ is_null($ikmIndex['score'] ?? null) ? '-' : number_format((float) $ikmIndex['score'], 2, ',', '.') }}
                    </p>
                    <div class="stats-ratio-scale" aria-hidden="true" data-ratio-scale>
                        @foreach($indexScaleColors as $level => $color)
                            <span class="scale-{{ $level }}" style="--scale-color: {{ $color }};" data-scale-segment></span>
                        @endforeach
                    </div>
                </div>
            </article>

            <article class="stats-card stats-ratio-card index-{{ $ikkIndex['class'] ?? 'muted' }}">
                <h4>Indeks Persepsi Anti Korupsi</h4>
                <div class="stats-ratio-body">
                    <div
                        class="stats-ratio-value"
                        data-count-up
                        data-count-value="{{ is_null($ikkIndex['ratio'] ?? null) ? '' : number_format((float) $ikkIndex['ratio'], 1, '.', '') }}"
                    >{{ $ikkIndex['ratio_label'] ?? '-' }}</div>
                    <div class="stats-ratio-category">{{ $ikkIndex['category'] ?? 'Belum ada data' }}</div>
                    <p class="stats-ratio-score">
                        Nilai indeks:
                        {{ is_null($ikkIndex['score'] ?? null) ? '-' : number_format((float) $ikkIndex['score'], 2, ',', '.') }}
                    </p>
                    <div class="stats-ratio-scale" aria-hidden="true" data-ratio-scale>
                        @foreach($indexScaleColors as $level => $color)
                            <span class="scale-{{ $level }}" style="--scale-color: {{ $color }};" data-scale-segment></span>
                        @endforeach
                    </div>
                </div>
            </article>
        </div>

        @php
            $parameterStats = collect($parameterCategoryStats ?? []);
        @endphp
        <div class="parameter-stats-panel" data-parameter-stats-panel>
            <div class="parameter-stats-head">
                <div class="parameter-stats-copy">
                    <h4>Jumlah Parameter Selesai Uji per Kategori</h4>
                    <p>Total dihitung dari jumlah parameter/qty pada permohonan yang LHU-nya sudah selesai dan disetujui pemohon.</p>
                    <span class="parameter-stats-updated">Updated at {{ now()->format('d/m/Y') }}</span>
                </div>
                <div class="parameter-total-pill">
                    <span>Total Parameter</span>
                    <strong data-count-integer data-count-value="{{ (int) ($parameterCategoryTotal ?? 0) }}">{{ number_format((int) ($parameterCategoryTotal ?? 0), 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($parameterStats->isNotEmpty())
                <div class="parameter-overview-bars" aria-label="Diagram batang jumlah parameter selesai uji per kategori">
                    @foreach($parameterStats as $category)
                        <div class="parameter-bar-row">
                            <span class="parameter-bar-label">{{ $category['name'] }}</span>
                            <span class="parameter-bar-track" aria-hidden="true">
                                <span
                                    class="parameter-bar-fill"
                                    data-progress-fill
                                    data-progress-target="{{ number_format((float) $category['bar_percent'], 2, '.', '') }}"
                                    style="--bar-width: {{ $category['bar_percent'] }}%; --bar-color: {{ $category['color'] }};"
                                ></span>
                            </span>
                            <span class="parameter-bar-value"><span data-count-integer data-count-value="{{ (int) $category['total'] }}">{{ number_format((int) $category['total'], 0, ',', '.') }}</span> parameter</span>
                        </div>
                    @endforeach
                </div>

                <div class="parameter-detail-grid">
                    @foreach($parameterStats as $category)
                        @php
                            $detailId = 'parameter-category-detail-' . $loop->index;
                            $hasSubcategories = (bool) ($category['has_subcategories'] ?? false);
                            $details = collect($category['details'] ?? []);
                            $visibleDetails = $details->filter(fn ($detail) => (int) ($detail['total'] ?? 0) > 0)->values();
                            $chartDetails = $visibleDetails;
                            $previewDetails = ($hasSubcategories ? $chartDetails : $visibleDetails)->take(4);
                        @endphp
                        <article class="parameter-category-card" style="--category-color: {{ $category['color'] }};" data-pie-card data-parameter-card>
                            <div class="parameter-category-head">
                                <div>
                                    <h5>{{ $category['name'] }}</h5>
                                    <span class="parameter-category-subtitle">
                                        {{ $details->count() }} {{ $hasSubcategories ? 'subkategori' : 'jenis parameter' }}
                                    </span>
                                </div>
                                <span class="parameter-category-count" data-count-integer data-count-value="{{ (int) $category['total'] }}">{{ number_format((int) $category['total'], 0, ',', '.') }}</span>
                            </div>

                            <div class="parameter-category-summary">
                                <div class="parameter-category-meter" aria-hidden="true">
                                    <span
                                        data-progress-fill
                                        data-progress-target="{{ number_format((float) $category['bar_percent'], 2, '.', '') }}"
                                        style="--bar-width: {{ $category['bar_percent'] }}%;"
                                    ></span>
                                </div>

                                <ul class="parameter-preview-list" aria-label="Preview parameter terbanyak kategori {{ $category['name'] }}">
                                    @foreach($previewDetails as $detail)
                                        <li title="{{ $detail['name'] }}">
                                            <span>{{ $detail['name'] }}</span>
                                            <strong>{{ number_format((int) $detail['total'], 0, ',', '.') }}</strong>
                                        </li>
                                    @endforeach
                                    @if($previewDetails->isEmpty())
                                        <li>
                                            <span>Belum ada parameter selesai</span>
                                        </li>
                                    @endif
                                </ul>

                                <button type="button" class="parameter-detail-toggle" aria-expanded="false" aria-controls="{{ $detailId }}" data-parameter-detail-toggle>
                                    <span class="parameter-toggle-open"><i class="bi bi-pie-chart-fill parameter-toggle-icon"></i>Detail diagram</span>
                                    <span class="parameter-toggle-close"><i class="bi bi-x-lg parameter-toggle-icon"></i>Tutup detail</span>
                                    <i class="bi bi-chevron-down parameter-toggle-chevron"></i>
                                </button>
                            </div>

                            <div class="parameter-category-detail" id="{{ $detailId }}" aria-hidden="true">
                                <div class="stats-pie parameter-category-pie"
                                    style="background: {{ $category['gradient'] ?? 'conic-gradient(#dfe5eb 0 100%)' }};"
                                    aria-label="Diagram parameter kategori {{ $category['name'] }}">
                                    <div class="stats-pie-label"></div>
                                    @if($hasSubcategories && $visibleDetails->isNotEmpty())
                                        <div class="parameter-pie-labels" aria-hidden="true">
                                            @php
                                                $labelTotal = max(1, (int) $visibleDetails->sum('total'));
                                                $labelCursor = 0.0;
                                            @endphp
                                            @foreach($visibleDetails as $detail)
                                                @php
                                                    $labelValue = (int) ($detail['total'] ?? 0);
                                                    $labelSpan = ($labelValue / $labelTotal) * 360;
                                                    $labelMid = $labelCursor + ($labelSpan / 2);
                                                    $labelRadius = 35;
                                                    $labelX = 50 + (sin(deg2rad($labelMid)) * $labelRadius);
                                                    $labelY = 50 - (cos(deg2rad($labelMid)) * $labelRadius);
                                                    $labelCursor += $labelSpan;
                                                @endphp
                                                @if($labelSpan >= 42)
                                                    <span class="parameter-pie-segment-label" style="--label-x: {{ number_format($labelX, 2, '.', '') }}%; --label-y: {{ number_format($labelY, 2, '.', '') }}%;">{{ $detail['name'] }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="parameter-detail-side">
                                    <ul class="stats-legend parameter-detail-list{{ $hasSubcategories ? ' parameter-segment-list is-hidden-source' : '' }}">
                                        @foreach($chartDetails as $detail)
                                            @php
                                                $segmentId = $detailId . '-segment-' . $loop->index;
                                            @endphp
                                            <li
                                                data-value="{{ (int) $detail['total'] }}"
                                                data-color="{{ $detail['color'] }}"
                                                @if($hasSubcategories) data-parameter-target="{{ $segmentId }}" @endif>
                                                <i style="background: {{ $detail['color'] }};"></i>
                                                <span class="parameter-detail-name" title="{{ $detail['name'] }}">{{ $detail['name'] }}</span>
                                                <strong>{{ number_format((int) $detail['total'], 0, ',', '.') }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if($chartDetails->isEmpty())
                                        <div class="parameter-empty-sublist">Belum ada parameter selesai.</div>
                                    @endif

                                    @if($hasSubcategories && $chartDetails->isNotEmpty())
                                        <h6 class="parameter-active-title" data-parameter-active-title>{{ $visibleDetails->first()['name'] ?? 'Parameter' }}</h6>
                                        <div class="parameter-parameter-blocks">
                                            @foreach($chartDetails as $detail)
                                                @php
                                                    $segmentId = $detailId . '-segment-' . $loop->index;
                                                    $detailParameters = collect($detail['parameters'] ?? [])
                                                        ->filter(fn ($parameter) => (int) ($parameter['total'] ?? 0) > 0)
                                                        ->values();
                                                @endphp
                                                <ul class="parameter-parameter-list{{ $loop->first ? ' is-active' : '' }}" data-parameter-list="{{ $segmentId }}">
                                                    @forelse($detailParameters as $parameter)
                                                        <li>
                                                            <span title="{{ $parameter['name'] }}">{{ $parameter['name'] }}</span>
                                                            <strong>{{ number_format((int) ($parameter['total'] ?? 0), 0, ',', '.') }}</strong>
                                                        </li>
                                                    @empty
                                                        <li class="parameter-empty-sublist">Belum ada parameter pada subkategori ini.</li>
                                                    @endforelse
                                                </ul>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="parameter-empty">
                    <i class="bi bi-clipboard-data"></i>
                    <span>Belum ada parameter dari permohonan yang selesai uji.</span>
                </div>
            @endif
        </div>
    </section>

    <section class="berita-wrap reveal">
        <div class="berita-head">
            <div>
                <h3 class="berita-title">Berita</h3>
                <div class="berita-underline"></div>
                <p class="berita-subtitle">Informasi terkini tentang kegiatan dan program Balai K3 Surabaya.</p>
            </div>
            <a href="{{ route('berita') }}" class="berita-more">Selengkapnya</a>
        </div>

        <div class="berita-grid">
            @if($homeFeaturedBerita)
                <article class="berita-feature">
                    <a href="{{ route('berita.show', $homeFeaturedBerita->slug) }}" class="d-block h-100 text-decoration-none">
                        <img src="{{ $homeFeaturedBerita->image_url }}" alt="{{ $homeFeaturedBerita->title }}">
                        <div class="berita-feature-content">
                            <h4>{{ $homeFeaturedBerita->title }}</h4>
                            <div class="berita-meta">
                                <span><i class="bi bi-calendar4-event"></i>{{ $homeFeaturedBerita->formatted_date }}</span>
                                <span><i class="bi bi-eye"></i>{{ strtolower($homeFeaturedBerita->views_label) }}</span>
                            </div>
                        </div>
                    </a>
                </article>
            @else
                <article class="berita-feature d-flex align-items-center justify-content-center text-white">
                    <div class="text-center px-4">
                        <h4 class="mb-2">Belum ada berita</h4>
                        <div class="berita-meta justify-content-center">
                            <span>Konten berita akan tampil di sini setelah ditambahkan dari admin.</span>
                        </div>
                    </div>
                </article>
            @endif

            <div class="berita-side">
                @forelse($homeSideBeritas as $berita)
                    <article class="berita-card">
                        <a href="{{ route('berita.show', $berita->slug) }}" class="text-decoration-none">
                            <span class="berita-badge">Berita Terbaru</span>
                            <h5>{{ $berita->title }}</h5>
                            <p>{{ $berita->excerpt }}</p>
                            <div class="berita-meta">
                                <span><i class="bi bi-calendar4-event"></i>{{ $berita->formatted_date }}</span>
                                <span><i class="bi bi-eye"></i>{{ strtolower($berita->views_label) }}</span>
                            </div>
                        </a>
                    </article>
                @empty
                    <article class="berita-card">
                        <span class="berita-badge">Berita</span>
                        <h5>Belum ada berita tambahan</h5>
                        <p>Tambahkan berita melalui dashboard admin agar konten publik tampil di bagian ini.</p>
                        <div class="berita-meta">
                            <span><i class="bi bi-info-circle"></i>Menunggu pembaruan</span>
                        </div>
                    </article>
                @endforelse
            </div>
        </div>
    </section>

    <section class="sosmed-wrap reveal">
        @php
            $defaultSocialItem = collect($homeSocialPosts)->firstWhere('has_posts', true)
                ?? collect($homeSocialPosts)->firstWhere('platform', 'instagram');
            $defaultSocialPlatform = $defaultSocialItem['platform'] ?? 'instagram';
            $defaultSocialConfig = $defaultSocialItem['config'] ?? null;
        @endphp
        <div class="sosmed-header">
            <h3 class="sosmed-title">Sosial Media</h3>
            <div class="sosmed-underline"></div>
            <p class="sosmed-subtitle">Ikuti informasi terbaru dan kegiatan Balai K3 Surabaya melalui kanal media sosial resmi kami.</p>
        </div>

        <div class="sosmed-mobile-nav" aria-label="Navigasi media sosial mobile">
            <strong class="sosmed-mobile-title" data-sosmed-mobile-title>{{ $defaultSocialConfig['label'] ?? 'Instagram' }}</strong>
            <div class="sosmed-mobile-actions">
                <button type="button" class="sosmed-mobile-btn" data-sosmed-mobile-scroll="prev" aria-label="Geser platform ke kiri">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="sosmed-mobile-btn" data-sosmed-mobile-scroll="next" aria-label="Geser platform ke kanan">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="sosmed-platforms">
            @foreach($homeSocialPosts as $socialItem)
                <button
                    type="button"
                    class="sosmed-platform-pill{{ $socialItem['has_posts'] ? '' : ' is-empty' }}{{ $socialItem['platform'] === $defaultSocialPlatform ? ' is-active' : '' }}"
                    style="--social-accent: {{ $socialItem['config']['accent'] }};"
                    data-platform-tab="{{ $socialItem['platform'] }}">
                    <i class="{{ $socialItem['config']['icon'] }}"></i>
                    {{ $socialItem['config']['label'] }}
                </button>
            @endforeach
        </div>

        <div class="sosmed-panels">
            @foreach($homeSocialPosts as $socialItem)
                @php
                    $platform = $socialItem['platform'];
                    $config = $socialItem['config'];
                    $posts = $socialItem['posts'];
                @endphp
                <div class="sosmed-panel{{ $platform === $defaultSocialPlatform ? ' is-active' : '' }}" data-platform-panel="{{ $platform }}" data-platform-label="{{ $config['label'] }}">
                    @if($posts->isNotEmpty())
                        @php
                            $carouselWidth = match ($platform) {
                                'youtube' => '380px',
                                'tiktok' => '220px',
                                default => '310px',
                            };
                        @endphp
                        <div class="sosmed-carousel{{ $posts->count() > 3 ? ' is-scrollable' : '' }}" style="--visible-card-width: {{ $carouselWidth }};">
                            <button type="button" class="sosmed-carousel-btn is-prev" data-sosmed-scroll="prev" aria-label="Geser ke kiri">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <div class="sosmed-carousel-hover-zone is-left" data-sosmed-hover="prev" aria-hidden="true"></div>
                            <div class="sosmed-carousel-viewport" data-sosmed-viewport>
                                <div class="sosmed-grid sosmed-grid-{{ $platform }}" data-sosmed-track>
                                    @foreach($posts as $post)
                                        @if($platform === 'instagram')
                                            <article class="sosmed-card sosmed-card-{{ $platform }}" style="--social-accent: {{ $config['accent'] }};">
                                                <div class="sosmed-embed-stage">
                                                    <div class="sosmed-embed-shell">
                                                        @if($post->embed_url)
                                                            <iframe
                                                                src="{{ $post->embed_url }}"
                                                                title="Konten {{ $config['label'] }}"
                                                                loading="lazy"
                                                                scrolling="no"
                                                                allowfullscreen
                                                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                                        @else
                                                            <div class="sosmed-empty">
                                                                <i class="{{ $config['icon'] }}"></i>
                                                                <strong>Link {{ $config['label'] }} belum bisa ditampilkan</strong>
                                                                <p>Periksa kembali link publik yang diinput dari halaman admin.</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </article>
                                        @elseif($platform === 'youtube')
                                            <article class="sosmed-card sosmed-card-{{ $platform }}" style="--social-accent: {{ $config['accent'] }};">
                                                <div class="sosmed-embed-stage">
                                                    <div class="sosmed-embed-shell">
                                                        @if($post->embed_url)
                                                            <iframe
                                                                src="{{ $post->embed_url }}"
                                                                title="Konten {{ $config['label'] }}"
                                                                data-social-player="tiktok"
                                                                loading="lazy"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                                allowfullscreen
                                                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                                        @else
                                                            <div class="sosmed-empty">
                                                                <i class="{{ $config['icon'] }}"></i>
                                                                <strong>Link {{ $config['label'] }} belum bisa ditampilkan</strong>
                                                                <p>Periksa kembali link publik yang diinput dari halaman admin.</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </article>
                                        @elseif($platform === 'tiktok')
                                            <article class="sosmed-card sosmed-card-{{ $platform }}" style="--social-accent: {{ $config['accent'] }};">
                                                <div class="sosmed-embed-stage">
                                                    <div class="sosmed-embed-shell">
                                                        @if($post->embed_url)
                                                            <iframe
                                                                src="{{ $post->embed_url }}"
                                                                title="Konten {{ $config['label'] }}"
                                                                loading="lazy"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                                allowfullscreen
                                                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                                        @else
                                                            <div class="sosmed-empty">
                                                                <i class="{{ $config['icon'] }}"></i>
                                                                <strong>Link {{ $config['label'] }} belum bisa ditampilkan</strong>
                                                                <p>Periksa kembali link publik yang diinput dari halaman admin.</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </article>
                                        @elseif($platform === 'facebook')
                                            <article class="sosmed-card sosmed-card-{{ $platform }}" style="--social-accent: {{ $config['accent'] }};">
                                                <div class="sosmed-embed-stage">
                                                    <div class="sosmed-embed-shell">
                                                        @if($post->embed_url)
                                                            <iframe
                                                                src="{{ $post->embed_url }}"
                                                                title="Konten {{ $config['label'] }}"
                                                                loading="lazy"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                                allowfullscreen
                                                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                                        @else
                                                            <div class="sosmed-empty">
                                                                <i class="{{ $config['icon'] }}"></i>
                                                                <strong>Link {{ $config['label'] }} belum bisa ditampilkan</strong>
                                                                <p>Periksa kembali link publik yang diinput dari halaman admin.</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </article>
                                        @else
                                            <article class="sosmed-card sosmed-card-{{ $platform }}" style="--social-accent: {{ $config['accent'] }};">
                                                <div class="sosmed-card-top">
                                                    <div class="sosmed-card-platform">
                                                        <i class="{{ $config['icon'] }}"></i>
                                                        <span>{{ $config['label'] }}</span>
                                                    </div>
                                                    <div class="sosmed-card-date">
                                                        {{ $post->formatted_date }}
                                                    </div>
                                                </div>

                                                <div class="sosmed-embed-stage">
                                                    <div class="sosmed-embed-shell">
                                                        @if($post->embed_url)
                                                            <iframe
                                                                src="{{ $post->embed_url }}"
                                                                title="Konten {{ $config['label'] }}"
                                                                loading="lazy"
                                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                                allowfullscreen
                                                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                                        @else
                                                            <div class="sosmed-empty">
                                                                <i class="{{ $config['icon'] }}"></i>
                                                                <strong>Link {{ $config['label'] }} belum bisa ditampilkan</strong>
                                                                <p>Periksa kembali link publik yang diinput dari halaman admin.</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="sosmed-card-body">
                                                    <h4>Konten {{ $config['label'] }} terbaru</h4>
                                                    <p>Postingan terbaru dari kanal {{ $config['label'] }} resmi Balai K3 Surabaya.</p>

                                                    <div class="sosmed-card-actions">
                                                        <div class="sosmed-card-meta">
                                                            <span><i class="bi bi-person-circle"></i>{{ $post->uploader_name }}</span>
                                                            <span><i class="bi bi-arrow-repeat"></i>Update terbaru</span>
                                                        </div>

                                                        <a href="{{ $post->post_url }}" target="_blank" rel="noopener noreferrer" class="sosmed-card-link">
                                                            Buka Postingan <i class="bi bi-arrow-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </article>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            <div class="sosmed-carousel-hover-zone is-right" data-sosmed-hover="next" aria-hidden="true"></div>
                            <button type="button" class="sosmed-carousel-btn is-next" data-sosmed-scroll="next" aria-label="Geser ke kanan">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    @else
                        <div class="sosmed-empty-panel">
                            <i class="{{ $config['icon'] }}"></i>
                            <strong>Belum ada konten {{ $config['label'] }}</strong>
                            <p>Jika opsi {{ $config['label'] }} dipilih, kontennya akan muncul di area ini setelah admin menambahkan link publik yang valid.</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const socialTabs = Array.from(document.querySelectorAll('[data-platform-tab]'));
        const socialPanels = Array.from(document.querySelectorAll('[data-platform-panel]'));
        const socialPanelsContainer = document.querySelector('.sosmed-panels');
        const socialMobileTitle = document.querySelector('[data-sosmed-mobile-title]');
        const socialMobilePrev = document.querySelector('[data-sosmed-mobile-scroll="prev"]');
        const socialMobileNext = document.querySelector('[data-sosmed-mobile-scroll="next"]');
        const socialMobileMediaQuery = window.matchMedia('(max-width: 767.98px)');

        const activateSocialPlatform = (platform) => {
            socialTabs.forEach((tab) => {
                const isActive = tab.dataset.platformTab === platform;
                tab.classList.toggle('is-active', isActive);
            });

            socialPanels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.dataset.platformPanel === platform);
            });
        };

        socialTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activateSocialPlatform(tab.dataset.platformTab);
            });
        });

        const initialSocialTab = document.querySelector('[data-platform-tab].is-active') || socialTabs[0];
        if (initialSocialTab) {
            activateSocialPlatform(initialSocialTab.dataset.platformTab);
        }

        const getSocialPanelStep = () => {
            const firstPanel = socialPanelsContainer?.querySelector('[data-platform-panel]');
            if (!firstPanel || !socialPanelsContainer) {
                return 0;
            }

            const gap = Number.parseFloat(getComputedStyle(socialPanelsContainer).gap || '0') || 0;
            return firstPanel.getBoundingClientRect().width + gap;
        };

        const getCurrentSocialPanelIndex = () => {
            if (!socialPanelsContainer || socialPanels.length === 0) {
                return 0;
            }

            const currentScroll = socialPanelsContainer.scrollLeft;
            let nearestIndex = 0;
            let nearestDistance = Number.POSITIVE_INFINITY;

            socialPanels.forEach((panel, index) => {
                const distance = Math.abs(panel.offsetLeft - currentScroll);
                if (distance < nearestDistance) {
                    nearestDistance = distance;
                    nearestIndex = index;
                }
            });

            return nearestIndex;
        };

        const syncSocialMobileCarousel = () => {
            if (!socialPanelsContainer || !socialMobileTitle || !socialMobilePrev || !socialMobileNext) {
                return;
            }

            if (!socialMobileMediaQuery.matches) {
                socialMobilePrev.classList.remove('is-disabled');
                socialMobileNext.classList.remove('is-disabled');
                socialMobileTitle.textContent = initialSocialTab?.textContent?.trim() || 'Instagram';
                return;
            }

            const currentIndex = getCurrentSocialPanelIndex();
            const currentPanel = socialPanels[currentIndex];
            const maxIndex = Math.max(0, socialPanels.length - 1);

            socialMobileTitle.textContent = currentPanel?.dataset.platformLabel || 'Instagram';
            socialMobilePrev.classList.toggle('is-disabled', currentIndex <= 0);
            socialMobileNext.classList.toggle('is-disabled', currentIndex >= maxIndex);
        };

        const scrollSocialPanels = (direction) => {
            if (!socialPanelsContainer || !socialMobileMediaQuery.matches) {
                return;
            }

            socialPanelsContainer.scrollBy({
                left: getSocialPanelStep() * direction,
                behavior: 'smooth',
            });
        };

        socialMobilePrev?.addEventListener('click', () => scrollSocialPanels(-1));
        socialMobileNext?.addEventListener('click', () => scrollSocialPanels(1));
        socialPanelsContainer?.addEventListener('scroll', syncSocialMobileCarousel, { passive: true });
        window.addEventListener('resize', syncSocialMobileCarousel);
        socialMobileMediaQuery.addEventListener('change', syncSocialMobileCarousel);
        syncSocialMobileCarousel();

        document.querySelectorAll('.sosmed-carousel').forEach((carousel) => {
            const viewport = carousel.querySelector('[data-sosmed-viewport]');
            const track = carousel.querySelector('[data-sosmed-track]');
            const prevButton = carousel.querySelector('[data-sosmed-scroll="prev"]');
            const nextButton = carousel.querySelector('[data-sosmed-scroll="next"]');
            const prevHoverZone = carousel.querySelector('[data-sosmed-hover="prev"]');
            const nextHoverZone = carousel.querySelector('[data-sosmed-hover="next"]');
            const supportsHover = window.matchMedia('(hover: hover)').matches;
            let autoScrollDirection = 0;
            let autoScrollInterval = null;
            let currentIndex = 0;

            if (!track || !viewport) {
                return;
            }

            const getMetrics = () => {
                const firstCard = track.querySelector('.sosmed-card');
                if (!firstCard) {
                    return { step: 0, visibleCount: 1, maxIndex: 0 };
                }

                const gap = Number.parseFloat(getComputedStyle(track).gap || '0') || 0;
                const step = firstCard.getBoundingClientRect().width + gap;
                const visibleCount = Math.max(1, Math.floor((viewport.clientWidth + gap) / step));
                const totalCards = track.querySelectorAll('.sosmed-card').length;
                const maxIndex = Math.max(0, totalCards - visibleCount);

                return { step, visibleCount, maxIndex };
            };

            const updateCarousel = () => {
                const isMobileCarousel = window.matchMedia('(max-width: 767.98px)').matches;
                if (isMobileCarousel) {
                    currentIndex = 0;
                    track.style.transform = 'none';
                    prevButton?.classList.add('is-hidden');
                    nextButton?.classList.add('is-hidden');
                    return;
                }

                const { step, maxIndex } = getMetrics();
                currentIndex = Math.max(0, Math.min(currentIndex, maxIndex));
                track.style.transform = `translateX(-${currentIndex * step}px)`;
                prevButton?.classList.toggle('is-hidden', currentIndex <= 0);
                nextButton?.classList.toggle('is-hidden', currentIndex >= maxIndex);
            };

            const moveCarousel = (direction) => {
                const { maxIndex } = getMetrics();
                if (maxIndex <= 0) {
                    return;
                }

                currentIndex = Math.max(0, Math.min(currentIndex + direction, maxIndex));
                updateCarousel();
            };

            const stopAutoScroll = () => {
                autoScrollDirection = 0;

                if (autoScrollInterval) {
                    window.clearInterval(autoScrollInterval);
                    autoScrollInterval = null;
                }
            };

            const startAutoScroll = () => {
                if (autoScrollInterval || autoScrollDirection === 0) {
                    return;
                }

                autoScrollInterval = window.setInterval(() => {
                    if (autoScrollDirection === 0) {
                        return;
                    }

                    const { maxIndex } = getMetrics();
                    if (maxIndex <= 0) {
                        stopAutoScroll();
                        return;
                    }

                    const nextIndex = Math.max(0, Math.min(currentIndex + autoScrollDirection, maxIndex));
                    if (nextIndex === currentIndex) {
                        autoScrollDirection = 0;
                        stopAutoScroll();
                        return;
                    }

                    currentIndex = nextIndex;
                    updateCarousel();
                }, 380);
            };

            prevButton?.addEventListener('click', () => moveCarousel(-1));
            nextButton?.addEventListener('click', () => moveCarousel(1));
            window.addEventListener('resize', updateCarousel);

            if (supportsHover) {
                const bindHoverZone = (zone, direction) => {
                    if (!zone) {
                        return;
                    }

                    zone.addEventListener('mouseenter', () => {
                        const { maxIndex } = getMetrics();
                        if (maxIndex <= 0) {
                            stopAutoScroll();
                            return;
                        }

                        stopAutoScroll();
                        autoScrollDirection = direction;
                        startAutoScroll();
                    });

                    zone.addEventListener('mouseleave', stopAutoScroll);
                };

                bindHoverZone(prevHoverZone, -1);
                bindHoverZone(nextHoverZone, 1);
                carousel.addEventListener('mouseleave', stopAutoScroll);
            }

            updateCarousel();
        });

        const tiktokPlayers = Array.from(document.querySelectorAll('iframe[data-social-player="tiktok"]'));
        const pauseTikTokPlayer = (iframe) => {
            if (!iframe || !iframe.contentWindow) {
                return;
            }

            iframe.contentWindow.postMessage({
                type: 'pause',
                value: null,
                'x-tiktok-player': true,
            }, '*');
        };

        window.addEventListener('message', (event) => {
            if (!event.data || event.data['x-tiktok-player'] !== true) {
                return;
            }

            if (event.data.type !== 'onStateChange' || Number(event.data.value) !== 1) {
                return;
            }

            const activePlayer = tiktokPlayers.find((iframe) => iframe.contentWindow === event.source);
            if (!activePlayer) {
                return;
            }

            tiktokPlayers.forEach((iframe) => {
                if (iframe !== activePlayer) {
                    pauseTikTokPlayer(iframe);
                }
            });
        });

        const setParameterCardState = (card, expanded) => {
            const detail = card?.querySelector('.parameter-category-detail');
            const button = card?.querySelector('[data-parameter-detail-toggle]');
            if (!card || !detail || !button) return;

            card.classList.toggle('is-open', expanded);
            detail.setAttribute('aria-hidden', expanded ? 'false' : 'true');
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');

            if (expanded) {
                const activeSegment = card.querySelector('.parameter-segment-list li.is-active')
                    || card.querySelector('.parameter-segment-list li');
                if (activeSegment) {
                    activeSegment.classList.add('is-active');
                    syncParameterBreakdown(activeSegment);
                }
            }
        };

        const syncParameterBreakdown = (item) => {
            const target = item?.dataset.parameterTarget;
            if (!target) return;

            const side = item.closest('.parameter-detail-side');
            if (!side) return;

            side.querySelectorAll('[data-parameter-list]').forEach((list) => {
                list.classList.toggle('is-active', list.dataset.parameterList === target);
            });

            const activeTitle = side.querySelector('[data-parameter-active-title]');
            const label = item.querySelector('.parameter-detail-name')?.textContent?.trim();
            if (activeTitle && label) {
                activeTitle.textContent = label;
            }
        };

        document.querySelectorAll('[data-parameter-detail-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const card = button.closest('[data-parameter-card]');
                if (!card) return;

                const shouldExpand = !card.classList.contains('is-open');
                const panel = card.closest('.parameter-stats-panel');
                panel?.querySelectorAll('[data-parameter-card].is-open').forEach((openCard) => {
                    if (openCard !== card) {
                        setParameterCardState(openCard, false);
                    }
                });

                setParameterCardState(card, shouldExpand);
            });
        });

        const statsCursorTooltip = document.createElement('div');
        statsCursorTooltip.className = 'stats-cursor-tooltip';
        statsCursorTooltip.setAttribute('role', 'tooltip');
        statsCursorTooltip.setAttribute('aria-hidden', 'true');
        document.body.appendChild(statsCursorTooltip);

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const moveStatsTooltip = (event) => {
            if (!event || !statsCursorTooltip.classList.contains('is-visible')) return;

            const padding = 14;
            const width = statsCursorTooltip.offsetWidth || 160;
            const height = statsCursorTooltip.offsetHeight || 54;
            const preferRight = event.clientX + width + 28 < window.innerWidth;
            const x = preferRight
                ? event.clientX + padding
                : Math.max(8, event.clientX - width - padding);
            const y = Math.min(
                Math.max(8, event.clientY + 12),
                Math.max(8, window.innerHeight - height - 8)
            );

            statsCursorTooltip.style.left = `${x}px`;
            statsCursorTooltip.style.top = `${y}px`;
            statsCursorTooltip.style.transform = 'none';
        };

        const formatLegendValue = (value, unit = '') => {
            const numericValue = Number(value || 0);
            const formatted = Number.isInteger(numericValue)
                ? String(numericValue)
                : numericValue.toFixed(1);

            return unit ? `${formatted}${unit}` : formatted;
        };

        const showStatsTooltip = (event, label, valueText, percent) => {
            statsCursorTooltip.innerHTML = `<strong>${escapeHtml(label)}</strong><span>${escapeHtml(valueText)} (${escapeHtml(percent)}%)</span>`;
            statsCursorTooltip.classList.add('is-visible');
            statsCursorTooltip.setAttribute('aria-hidden', 'false');
            moveStatsTooltip(event);
        };

        const hideStatsTooltip = () => {
            statsCursorTooltip.classList.remove('is-visible');
            statsCursorTooltip.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('[data-pie-card]').forEach((card) => {
            const pie = card.querySelector('.stats-pie');
            const pieLabel = card.querySelector('.stats-pie-label');
            const legendItems = Array.from(card.querySelectorAll('.stats-legend li'));
            const total = legendItems.reduce((sum, item) => sum + Number(item.dataset.value || 0), 0);
            const mutedSliceColor = '#d7dbe0';

            if (pie) {
                pie.dataset.defaultBackground = pie.style.background;
            }

            const buildHoverGradient = (activeIndex) => {
                let current = 0;
                const stops = legendItems.map((item, index) => {
                    const value = Number(item.dataset.value || 0);
                    const span = total > 0 ? (value / total) * 100 : 0;
                    const start = current.toFixed(2);
                    const end = (current + span).toFixed(2);
                    const color = index === activeIndex ? (item.dataset.color || '#1d4b78') : mutedSliceColor;
                    current += span;

                    return `${color} ${start}% ${end}%`;
                });

                return `conic-gradient(${stops.join(', ')})`;
            };

            const getSliceMeta = (targetIndex) => {
                let current = 0;

                for (let index = 0; index < legendItems.length; index += 1) {
                    const value = Number(legendItems[index].dataset.value || 0);
                    const span = total > 0 ? (value / total) * 360 : 0;
                    const next = current + span;

                    if (index === targetIndex) {
                        return {
                            start: current,
                            end: next,
                            span,
                            mid: current + (span / 2),
                        };
                    }

                    current = next;
                }

                return null;
            };

            const setActive = (item, event = null) => {
                if (!item) return;

                const itemIndex = legendItems.indexOf(item);
                const sliceMeta = getSliceMeta(itemIndex);

                legendItems.forEach((entry) => entry.classList.remove('is-active'));
                item.classList.add('is-active');
                syncParameterBreakdown(item);

                if (pie) {
                    pie.classList.add('is-hovered');
                    pie.style.background = buildHoverGradient(itemIndex);
                    pie.style.setProperty('--hover-start', `${sliceMeta?.start ?? 0}deg`);
                    pie.style.setProperty('--hover-end', `${sliceMeta?.end ?? 0}deg`);
                }

                const value = Number(item.dataset.value || 0);
                const unit = item.dataset.unit || '';
                const color = item.dataset.color || '#1d4b78';
                const label = item.querySelector('.parameter-detail-name')?.textContent?.trim()
                    || item.textContent.trim();
                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                const formattedValue = formatLegendValue(value, unit);

                card.style.setProperty('--stats-highlight', color);
                if (event) {
                    showStatsTooltip(event, label, formattedValue, percent);
                }

                if (pieLabel && pie && sliceMeta) {
                    const angleRad = sliceMeta.mid * Math.PI / 180;
                    const radiusFactor = sliceMeta.span < 28 ? 0.43 : sliceMeta.span < 60 ? 0.38 : 0.3;
                    const orbit = (pie.clientWidth / 2) * radiusFactor;
                    const x = 50 + ((Math.sin(angleRad) * orbit) / pie.clientWidth) * 100;
                    const y = 50 - ((Math.cos(angleRad) * orbit) / pie.clientHeight) * 100;
                    const textColor = color.toLowerCase() === '#b7b7b7' ? '#17324b' : '#ffffff';

                    pieLabel.style.left = `${x}%`;
                    pieLabel.style.top = `${y}%`;
                    pieLabel.style.color = textColor;
                    const isParameterPie = card.classList.contains('parameter-category-card');
                    pieLabel.innerHTML = isParameterPie
                        ? `${label}<br>${value} (${percent}%)`
                        : `${formattedValue}<br>${label}`;
                }
            };

            const resetState = () => {
                legendItems.forEach((entry) => entry.classList.remove('is-active'));
                hideStatsTooltip();

                if (pie) {
                    pie.classList.remove('is-hovered');
                    pie.style.background = pie.dataset.defaultBackground || '';
                    pie.style.removeProperty('--hover-start');
                    pie.style.removeProperty('--hover-end');
                }

                if (pieLabel) {
                    pieLabel.style.left = '50%';
                    pieLabel.style.top = '50%';
                    pieLabel.style.color = '#ffffff';
                    pieLabel.innerHTML = '';
                }
            };

            legendItems.forEach((item) => {
                item.addEventListener('mouseenter', (event) => setActive(item, event));
                item.addEventListener('mousemove', (event) => {
                    setActive(item, event);
                    moveStatsTooltip(event);
                });
                item.addEventListener('click', (event) => setActive(item, event));
            });

            if (!pie || total <= 0 || legendItems.length === 0) {
                return;
            }

            const getSliceIndex = (event) => {
                const rect = pie.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const centerY = rect.top + rect.height / 2;
                const dx = event.clientX - centerX;
                const dy = event.clientY - centerY;
                const radius = rect.width / 2;

                if ((dx * dx) + (dy * dy) > radius * radius) {
                    return -1;
                }

                const baseAngle = ((Math.atan2(dy, dx) * 180 / Math.PI) + 90 + 360) % 360;
                const adjustedAngle = (baseAngle + 125) % 360;

                let current = 0;
                for (let index = 0; index < legendItems.length; index += 1) {
                    const value = Number(legendItems[index].dataset.value || 0);
                    const span = total > 0 ? (value / total) * 360 : 0;
                    const next = current + span;

                    if (adjustedAngle >= current && adjustedAngle < next) {
                        return index;
                    }

                    current = next;
                }

                return legendItems.length - 1;
            };

            pie.addEventListener('mousemove', (event) => {
                const sliceIndex = getSliceIndex(event);
                if (sliceIndex >= 0) {
                    setActive(legendItems[sliceIndex], event);
                }
            });

            pie.addEventListener('click', (event) => {
                const sliceIndex = getSliceIndex(event);
                if (sliceIndex >= 0) {
                    setActive(legendItems[sliceIndex], event);
                }
            });

            pie.addEventListener('mouseenter', () => {
                pie.classList.add('is-hovered');
            });

            pie.addEventListener('mouseleave', resetState);
            card.addEventListener('mouseleave', resetState);
        });
    });
</script>

<style>
    body {
        background-color: #e6e6e6;
        font-family: 'Poppins', sans-serif;
    }
</style>

    <style>
        .faq-section {
            padding: 50px 20px 74px;
        }

        .faq-container {
            max-width: 860px;
            margin: 0 auto;
        }

        .faq-heading {
            text-align: center;
            margin-bottom: 24px;
        }

        .faq-heading h2 {
            margin: 0;
            color: #15406A;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.05;
            letter-spacing: 0;
        }

        .faq-heading-line {
            width: 76px;
            height: 4px;
            border-radius: 999px;
            background: #15406A;
            margin: 10px auto 14px;
        }

        .faq-heading p {
            margin: 0;
            color: #111111;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.5;
        }

        .faq-list {
            display: grid;
            gap: 14px;
        }

        .faq-item {
            background: #ffffff;
            border: 1px solid #d2d6da;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .faq-question {
            width: 100%;
            border: 0;
            background: transparent;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 22px 14px 20px;
            text-align: left;
            color: #15406A;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.35;
            cursor: pointer;
        }

        .faq-icon {
            flex: 0 0 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #22b78f;
            font-size: 26px;
            font-weight: 300;
            line-height: 1;
            transform: translateY(-1px);
            transition: transform 0.2s ease;
        }

        .faq-item.is-open .faq-icon {
            transform: translateY(-1px) rotate(45deg);
        }

        .faq-answer {
            display: none;
            padding: 0 22px 16px 52px;
            color: #15406A;
            font-size: 12px;
            line-height: 1.7;
        }

        .faq-item.is-open .faq-answer {
            display: block;
        }

        @media (max-width: 767.98px) {
            .faq-section {
                padding: 34px 16px 56px;
            }

            .faq-heading {
                margin-bottom: 18px;
            }

            .faq-heading h2 {
                font-size: 20px;
            }

            .faq-heading p {
                font-size: 13px;
            }

            .faq-list {
                gap: 12px;
            }

            .faq-question {
                gap: 14px;
                padding: 12px 16px 12px 18px;
                font-size: 12px;
            }

            .faq-icon {
                flex-basis: 14px;
                font-size: 22px;
            }

            .faq-answer {
                padding: 0 16px 14px 46px;
                font-size: 12px;
            }
        }
    </style>

    <section class="faq-section reveal">
        <div class="faq-container">
            <div class="faq-heading">
                <h2>FAQ</h2>
                <div class="faq-heading-line"></div>
                <p>Temukan jawaban atas berbagai pertanyaan umum terkait layanan dan informasi di Balai K3 Surabaya.</p>
            </div>

            <div class="faq-list">
                <article class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span class="faq-icon">+</span>
                        <span>Apa saja layanan yang tersedia di Balai K3 Surabaya?</span>
                    </button>
                    <div class="faq-answer">
                        Balai K3 Surabaya menyediakan layanan pengujian lingkungan kerja, kesehatan kerja, pelatihan, pemeriksaan, dan layanan pendukung K3 lainnya sesuai standar yang berlaku.
                    </div>
                </article>

                <article class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span class="faq-icon">+</span>
                        <span>Bagaimana cara mengajukan permohonan layanan pengujian K3?</span>
                    </button>
                    <div class="faq-answer">
                        Permohonan dapat diajukan melalui alur pelayanan yang tersedia pada website atau dengan menghubungi kontak resmi Balai K3 Surabaya untuk informasi persyaratan dan penjadwalan.
                    </div>
                </article>

                <article class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span class="faq-icon">+</span>
                        <span>Apakah Balai K3 Surabaya menyediakan edukasi atau sosialisasi tentang K3?</span>
                    </button>
                    <div class="faq-answer">
                        Ya, Balai K3 Surabaya juga menyediakan edukasi, sosialisasi, dan pelatihan K3 untuk mendukung peningkatan budaya keselamatan dan kesehatan kerja.
                    </div>
                </article>

                <article class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span class="faq-icon">+</span>
                        <span>Siapa saja yang dapat menggunakan layanan Balai K3 Surabaya?</span>
                    </button>
                    <div class="faq-answer">
                        Layanan Balai K3 Surabaya dapat dimanfaatkan oleh perusahaan, institusi, instansi pemerintah, akademisi, dan masyarakat yang membutuhkan layanan terkait K3.
                    </div>
                </article>

                <article class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span class="faq-icon">+</span>
                        <span>Apakah mahasiswa bisa melakukan kunjungan atau studi ke Balai K3?</span>
                    </button>
                    <div class="faq-answer">
                        Mahasiswa dapat mengajukan kunjungan atau studi sesuai ketentuan yang berlaku dengan menghubungi pihak Balai K3 Surabaya untuk konfirmasi jadwal dan kebutuhan administrasi.
                    </div>
                </article>

                <article class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span class="faq-icon">+</span>
                        <span>Bagaimana cara mengetahui tarif layanan pengujian?</span>
                    </button>
                    <div class="faq-answer">
                        Informasi tarif layanan pengujian dapat diperoleh melalui daftar pelayanan, kontak resmi Balai K3 Surabaya, atau informasi yang diberikan saat proses permohonan layanan.
                    </div>
                </article>
            </div>
        </div>
    </section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.faq-question').forEach((button) => {
            button.addEventListener('click', function () {
                const item = this.closest('.faq-item');
                const isOpen = item.classList.contains('is-open');

                document.querySelectorAll('.faq-item').forEach((faqItem) => {
                    faqItem.classList.remove('is-open');
                    const faqButton = faqItem.querySelector('.faq-question');
                    if (faqButton) {
                        faqButton.setAttribute('aria-expanded', 'false');
                    }
                });

                if (!isOpen) {
                    item.classList.add('is-open');
                    this.setAttribute('aria-expanded', 'true');
                }
            });
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('show');
                } else {
                    entry.target.classList.remove('show');
                }
            });
        }, { threshold: 0.2 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        const layananWrap = document.querySelector('.layanan-wrap');
        const statsWrap = document.querySelector('.stats-wrap');
        const layananGrid = layananWrap?.querySelector('.layanan-grid');
        const layananCards = layananGrid ? Array.from(layananGrid.querySelectorAll('.layanan-card')) : [];
        const statsGrid = statsWrap?.querySelector('.stats-grid');
        const statsPrevButton = statsWrap?.querySelector('[data-stats-scroll="prev"]');
        const statsNextButton = statsWrap?.querySelector('[data-stats-scroll="next"]');
        const statsCounters = Array.from(document.querySelectorAll('[data-count-up]'));
        const parameterStatsPanel = document.querySelector('[data-parameter-stats-panel]');
        const parameterCounters = Array.from(document.querySelectorAll('[data-count-integer]'));
        const parameterProgressFills = Array.from(document.querySelectorAll('[data-progress-fill]'));
        const mobileMediaQuery = window.matchMedia('(max-width: 767.98px)');
        const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        let layananScrollTimer = null;
        let statsScrollTimer = null;
        let layananGridScrollTimer = null;
        let layananTouchStartX = 0;
        let layananTouchStartY = 0;
        let layananHorizontalDragActive = false;

        const formatStatsPercent = (value) => `${new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 1,
            maximumFractionDigits: 1,
        }).format(value)}%`;

        const formatIntegerCount = (value) => new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0,
        }).format(Math.round(value));

        const updateStatsScaleProgress = (counter, value) => {
            const scale = counter.closest('.stats-ratio-body')?.querySelector('[data-ratio-scale]');

            if (!scale) {
                return;
            }

            const boundedValue = Math.max(0, Math.min(100, value));
            const segments = Array.from(scale.querySelectorAll('[data-scale-segment]'));

            segments.forEach((segment, index) => {
                const start = index * 25;
                const fill = Math.max(0, Math.min((boundedValue - start) / 25, 1));
                segment.style.setProperty('--segment-fill', fill.toFixed(3));
            });
        };

        const applyFinalStatsCounterValue = (counter) => {
            const targetValue = Number.parseFloat(counter.dataset.countValue || '');

            if (!Number.isFinite(targetValue)) {
                return;
            }

            counter.textContent = formatStatsPercent(targetValue);
            updateStatsScaleProgress(counter, targetValue);
        };

        const resetStatsCounterValue = (counter) => {
            const targetValue = Number.parseFloat(counter.dataset.countValue || '');

            if (!Number.isFinite(targetValue)) {
                return;
            }

            if (counter._countAnimationFrame) {
                window.cancelAnimationFrame(counter._countAnimationFrame);
                counter._countAnimationFrame = null;
            }

            counter.dataset.countAnimated = 'false';
            counter.textContent = formatStatsPercent(0);
            updateStatsScaleProgress(counter, 0);
        };

        const animateStatsCounterValue = (counter) => {
            const targetValue = Number.parseFloat(counter.dataset.countValue || '');

            if (!Number.isFinite(targetValue) || counter.dataset.countAnimated === 'true') {
                return;
            }

            counter.dataset.countAnimated = 'true';

            if (reducedMotionQuery.matches) {
                applyFinalStatsCounterValue(counter);
                return;
            }

            const duration = 2200;
            const easing = (progress) => 1 - Math.pow(1 - progress, 3);
            const startTime = performance.now();

            const render = (now) => {
                const rawProgress = Math.min((now - startTime) / duration, 1);
                const currentValue = targetValue * easing(rawProgress);

                counter.textContent = formatStatsPercent(currentValue);
                updateStatsScaleProgress(counter, currentValue);

                if (rawProgress < 1) {
                    counter._countAnimationFrame = window.requestAnimationFrame(render);
                    return;
                }

                counter._countAnimationFrame = null;
                applyFinalStatsCounterValue(counter);
            };

            counter._countAnimationFrame = window.requestAnimationFrame(render);
        };

        const applyFinalIntegerCounterValue = (counter) => {
            const targetValue = Number.parseFloat(counter.dataset.countValue || '');

            if (!Number.isFinite(targetValue)) {
                return;
            }

            counter.textContent = formatIntegerCount(targetValue);
        };

        const resetIntegerCounterValue = (counter) => {
            const targetValue = Number.parseFloat(counter.dataset.countValue || '');

            if (!Number.isFinite(targetValue)) {
                return;
            }

            if (counter._countAnimationFrame) {
                window.cancelAnimationFrame(counter._countAnimationFrame);
                counter._countAnimationFrame = null;
            }

            counter.dataset.countAnimated = 'false';
            counter.textContent = formatIntegerCount(0);
        };

        const animateIntegerCounterValue = (counter) => {
            const targetValue = Number.parseFloat(counter.dataset.countValue || '');

            if (!Number.isFinite(targetValue) || counter.dataset.countAnimated === 'true') {
                return;
            }

            counter.dataset.countAnimated = 'true';

            if (reducedMotionQuery.matches) {
                applyFinalIntegerCounterValue(counter);
                return;
            }

            const duration = targetValue >= 100 ? 1900 : 1650;
            const easing = (progress) => 1 - Math.pow(1 - progress, 3);
            const startTime = performance.now();

            const render = (now) => {
                const rawProgress = Math.min((now - startTime) / duration, 1);
                const currentValue = targetValue * easing(rawProgress);

                counter.textContent = formatIntegerCount(currentValue);

                if (rawProgress < 1) {
                    counter._countAnimationFrame = window.requestAnimationFrame(render);
                    return;
                }

                counter._countAnimationFrame = null;
                applyFinalIntegerCounterValue(counter);
            };

            counter._countAnimationFrame = window.requestAnimationFrame(render);
        };

        const applyFinalProgressFill = (fill) => {
            const targetValue = Number.parseFloat(fill.dataset.progressTarget || '');

            if (!Number.isFinite(targetValue)) {
                return;
            }

            fill.style.setProperty('--bar-animated-width', `${targetValue}%`);
        };

        const resetProgressFill = (fill) => {
            const targetValue = Number.parseFloat(fill.dataset.progressTarget || '');

            if (!Number.isFinite(targetValue)) {
                return;
            }

            if (fill._progressAnimationFrame) {
                window.cancelAnimationFrame(fill._progressAnimationFrame);
                fill._progressAnimationFrame = null;
            }

            fill.dataset.progressAnimated = 'false';
            fill.style.setProperty('--bar-animated-width', '0%');
        };

        const animateProgressFill = (fill) => {
            const targetValue = Number.parseFloat(fill.dataset.progressTarget || '');

            if (!Number.isFinite(targetValue) || fill.dataset.progressAnimated === 'true') {
                return;
            }

            fill.dataset.progressAnimated = 'true';

            if (reducedMotionQuery.matches) {
                applyFinalProgressFill(fill);
                return;
            }

            const duration = 1800;
            const easing = (progress) => 1 - Math.pow(1 - progress, 3);
            const startTime = performance.now();

            const render = (now) => {
                const rawProgress = Math.min((now - startTime) / duration, 1);
                const currentValue = targetValue * easing(rawProgress);

                fill.style.setProperty('--bar-animated-width', `${currentValue}%`);

                if (rawProgress < 1) {
                    fill._progressAnimationFrame = window.requestAnimationFrame(render);
                    return;
                }

                fill._progressAnimationFrame = null;
                applyFinalProgressFill(fill);
            };

            fill._progressAnimationFrame = window.requestAnimationFrame(render);
        };

        const setLayananUnderlineIdle = () => {
            layananWrap?.classList.remove('is-scrolling');
        };

        const setStatsUnderlineIdle = () => {
            statsWrap?.classList.remove('is-scrolling');
        };

        const handleLayananUnderlineScroll = () => {
            if (!layananWrap) {
                return;
            }

            if (!mobileMediaQuery.matches) {
                setLayananUnderlineIdle();
                return;
            }

            layananWrap.classList.add('is-scrolling');
            window.clearTimeout(layananScrollTimer);
            layananScrollTimer = window.setTimeout(setLayananUnderlineIdle, 320);
        };

        const handleStatsUnderlineScroll = () => {
            if (!statsWrap) {
                return;
            }

            if (!mobileMediaQuery.matches) {
                setStatsUnderlineIdle();
                return;
            }

            statsWrap.classList.add('is-scrolling');
            window.clearTimeout(statsScrollTimer);
            statsScrollTimer = window.setTimeout(setStatsUnderlineIdle, 320);
        };

        const resetLayananCardWave = () => {
            layananCards.forEach((card) => {
                card.style.setProperty('--wave-scale', '1');
                card.style.setProperty('--wave-shift-y', '0px');
            });
        };

        const updateLayananCardWave = () => {
            if (!layananGrid || !mobileMediaQuery.matches || !layananHorizontalDragActive) {
                resetLayananCardWave();
                return;
            }

            const gridRect = layananGrid.getBoundingClientRect();
            const gridCenterX = gridRect.left + (gridRect.width / 2);
            const maxDistance = Math.max(gridRect.width * 0.55, 1);

            layananCards.forEach((card) => {
                const cardRect = card.getBoundingClientRect();
                const cardCenterX = cardRect.left + (cardRect.width / 2);
                const distanceRatio = Math.min(Math.abs(cardCenterX - gridCenterX) / maxDistance, 1);
                const intensity = 1 - distanceRatio;
                const scale = 0.92 + (intensity * 0.13);
                const shiftY = Math.round((distanceRatio * 10) - (intensity * 8));

                card.style.setProperty('--wave-scale', scale.toFixed(3));
                card.style.setProperty('--wave-shift-y', `${shiftY}px`);
            });
        };

        window.addEventListener('scroll', handleLayananUnderlineScroll, { passive: true });
        window.addEventListener('scroll', handleStatsUnderlineScroll, { passive: true });
        mobileMediaQuery.addEventListener('change', setLayananUnderlineIdle);
        mobileMediaQuery.addEventListener('change', setStatsUnderlineIdle);

        layananGrid?.addEventListener('touchstart', (event) => {
            if (!mobileMediaQuery.matches) {
                return;
            }

            const touch = event.touches?.[0];
            if (!touch) {
                return;
            }

            layananTouchStartX = touch.clientX;
            layananTouchStartY = touch.clientY;
            layananHorizontalDragActive = false;
        }, { passive: true });

        layananGrid?.addEventListener('touchmove', (event) => {
            if (!mobileMediaQuery.matches) {
                return;
            }

            const touch = event.touches?.[0];
            if (!touch) {
                return;
            }

            const deltaX = Math.abs(touch.clientX - layananTouchStartX);
            const deltaY = Math.abs(touch.clientY - layananTouchStartY);

            if (deltaX > 10 && deltaX > deltaY) {
                layananHorizontalDragActive = true;
                layananGrid.classList.add('is-scrolling');
                updateLayananCardWave();
            }
        }, { passive: true });

        layananGrid?.addEventListener('scroll', () => {
            if (!mobileMediaQuery.matches || !layananHorizontalDragActive) {
                layananGrid.classList.remove('is-scrolling');
                resetLayananCardWave();
                return;
            }

            layananGrid.classList.add('is-scrolling');
            updateLayananCardWave();
            window.clearTimeout(layananGridScrollTimer);
            layananGridScrollTimer = window.setTimeout(() => {
                layananGrid.classList.remove('is-scrolling');
                layananHorizontalDragActive = false;
                resetLayananCardWave();
            }, 180);
        }, { passive: true });

        layananGrid?.addEventListener('touchend', () => {
            window.clearTimeout(layananGridScrollTimer);
            layananGridScrollTimer = window.setTimeout(() => {
                layananGrid?.classList.remove('is-scrolling');
                layananHorizontalDragActive = false;
                resetLayananCardWave();
            }, 120);
        }, { passive: true });

        mobileMediaQuery.addEventListener('change', () => {
            layananGrid?.classList.remove('is-scrolling');
            layananHorizontalDragActive = false;
            resetLayananCardWave();
        });

        if (statsCounters.length) {
            statsCounters.forEach((counter) => {
                counter.dataset.countAnimated = 'false';
                counter._countAnimationFrame = null;
                resetStatsCounterValue(counter);
            });

            if (reducedMotionQuery.matches) {
                statsCounters.forEach(applyFinalStatsCounterValue);
            } else {
                const statsCounterObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        const counter = entry.target;

                        if (entry.isIntersecting && entry.intersectionRatio >= 0.35) {
                            animateStatsCounterValue(counter);
                            return;
                        }

                        if (!entry.isIntersecting) {
                            resetStatsCounterValue(counter);
                        }
                    });
                }, {
                    threshold: [0, 0.35],
                    rootMargin: '0px 0px -8% 0px',
                });

                statsCounters.forEach((counter) => {
                    const targetValue = Number.parseFloat(counter.dataset.countValue || '');

                    if (!Number.isFinite(targetValue)) {
                        return;
                    }

                    statsCounterObserver.observe(counter);
                });
            }
        }

        if (parameterStatsPanel && (parameterCounters.length || parameterProgressFills.length)) {
            parameterCounters.forEach((counter) => {
                counter.dataset.countAnimated = 'false';
                counter._countAnimationFrame = null;
                resetIntegerCounterValue(counter);
            });

            parameterProgressFills.forEach((fill) => {
                fill.dataset.progressAnimated = 'false';
                fill._progressAnimationFrame = null;
                resetProgressFill(fill);
            });

            if (reducedMotionQuery.matches) {
                parameterCounters.forEach(applyFinalIntegerCounterValue);
                parameterProgressFills.forEach(applyFinalProgressFill);
            } else {
                const parameterStatsObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting && entry.intersectionRatio >= 0.22) {
                            parameterCounters.forEach(animateIntegerCounterValue);
                            parameterProgressFills.forEach(animateProgressFill);
                            return;
                        }

                        if (!entry.isIntersecting) {
                            parameterCounters.forEach(resetIntegerCounterValue);
                            parameterProgressFills.forEach(resetProgressFill);
                        }
                    });
                }, {
                    threshold: [0, 0.22],
                    rootMargin: '0px 0px -10% 0px',
                });

                parameterStatsObserver.observe(parameterStatsPanel);
            }
        }

        if (statsGrid && statsPrevButton && statsNextButton) {
            const getStatsScrollStep = () => {
                const firstCard = statsGrid.querySelector('.stats-card');
                if (!firstCard) {
                    return statsGrid.clientWidth;
                }

                const gap = Number.parseFloat(getComputedStyle(statsGrid).gap || '0') || 0;
                return firstCard.getBoundingClientRect().width + gap;
            };

            const syncStatsButtons = () => {
                const isMobile = mobileMediaQuery.matches;
                const maxScrollLeft = Math.max(0, statsGrid.scrollWidth - statsGrid.clientWidth - 2);

                statsPrevButton.classList.toggle('is-disabled', !isMobile || statsGrid.scrollLeft <= 4);
                statsNextButton.classList.toggle('is-disabled', !isMobile || statsGrid.scrollLeft >= maxScrollLeft);
            };

            const scrollStatsGrid = (direction) => {
                if (!mobileMediaQuery.matches) {
                    return;
                }

                statsGrid.scrollBy({
                    left: getStatsScrollStep() * direction,
                    behavior: 'smooth',
                });
            };

            statsPrevButton.addEventListener('click', () => scrollStatsGrid(-1));
            statsNextButton.addEventListener('click', () => scrollStatsGrid(1));
            statsGrid.addEventListener('scroll', syncStatsButtons, { passive: true });
            window.addEventListener('resize', syncStatsButtons);
            mobileMediaQuery.addEventListener('change', syncStatsButtons);
            syncStatsButtons();
        }
    });
</script>

</div>

@endsection
