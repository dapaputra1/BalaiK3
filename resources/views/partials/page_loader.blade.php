<style>
    .page-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: grid;
        place-items: center;
        background:
            radial-gradient(circle at 50% 38%, rgba(255, 255, 255, 0.16), transparent 28%),
            linear-gradient(135deg, #0f3b63 0%, #15406a 52%, #0b2d4f 100%);
        opacity: 1;
        visibility: visible;
        transition: opacity 0.45s ease, visibility 0.45s ease;
    }

    .page-loader.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .page-loader__mark {
        position: relative;
        width: clamp(138px, 24vw, 194px);
        aspect-ratio: 1;
        display: grid;
        place-items: center;
    }

    .page-loader__ring,
    .page-loader__ring::before,
    .page-loader__ring::after {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        pointer-events: none;
    }

    .page-loader__ring {
        border: 3px solid rgba(255, 255, 255, 0.24);
        border-top-color: #ffffff;
        border-right-color: rgba(126, 214, 255, 0.95);
        animation: page-loader-spin 1s linear infinite;
    }

    .page-loader__ring::before {
        content: "";
        inset: 12px;
        border: 2px solid rgba(255, 255, 255, 0.16);
        border-bottom-color: rgba(255, 255, 255, 0.88);
        animation: page-loader-spin 1.35s linear infinite reverse;
    }

    .page-loader__ring::after {
        content: "";
        inset: -10px;
        border: 1px solid rgba(255, 255, 255, 0.18);
    }

    .page-loader__logo-wrap {
        width: 74%;
        aspect-ratio: 1;
        display: grid;
        place-items: center;
        padding: 17%;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 22px 54px rgba(0, 0, 0, 0.22);
    }

    .page-loader__logo {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
        transform: translateY(-2%);
    }

    @keyframes page-loader-spin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .page-loader,
        .page-loader__ring,
        .page-loader__ring::before {
            animation: none;
            transition-duration: 0.01ms;
        }
    }
</style>

<div class="page-loader" id="pageLoader" role="status" aria-live="polite" aria-label="Memuat halaman">
    <div class="page-loader__mark" aria-hidden="true">
        <span class="page-loader__ring"></span>
        <span class="page-loader__logo-wrap">
            <img class="page-loader__logo" src="{{ asset('images/Logo Kemnaker.png') }}" alt="">
        </span>
    </div>
</div>

<script>
    (() => {
        const loader = document.getElementById('pageLoader');
        if (!loader) return;

        const startedAt = performance.now();
        const minVisibleMs = 450;
        let suppressUnloadUntil = 0;
        let unloadFallbackTimer = null;

        const hideLoader = () => {
            const elapsed = performance.now() - startedAt;
            const delay = Math.max(0, minVisibleMs - elapsed);

            window.setTimeout(() => {
                loader.classList.add('is-hidden');
            }, delay);
        };

        const showLoader = () => {
            loader.classList.remove('is-hidden');
        };

        const suppressNextUnload = (duration = 8000) => {
            suppressUnloadUntil = Date.now() + duration;
        };

        window.PageLoader = {
            show: showLoader,
            hide: hideLoader,
            suppressNextUnload,
        };

        document.addEventListener('click', (event) => {
            const link = event.target.closest?.('a');
            if (!link) return;

            const isDownloadLink = link.hasAttribute('download')
                || link.hasAttribute('data-download-once')
                || link.getAttribute('data-page-loader') === 'skip';

            if (isDownloadLink) {
                suppressNextUnload();
            }
        }, true);

        if (document.readyState === 'complete') {
            hideLoader();
        } else {
            window.addEventListener('load', hideLoader, { once: true });
        }

        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                loader.classList.add('is-hidden');
            }
        });

        window.addEventListener('beforeunload', () => {
            if (Date.now() < suppressUnloadUntil) {
                return;
            }

            showLoader();
            window.clearTimeout(unloadFallbackTimer);
            unloadFallbackTimer = window.setTimeout(hideLoader, 9000);
        });

        window.addEventListener('focus', () => {
            if (Date.now() < suppressUnloadUntil || !document.hidden) {
                window.clearTimeout(unloadFallbackTimer);
                hideLoader();
            }
        });
    })();
</script>
