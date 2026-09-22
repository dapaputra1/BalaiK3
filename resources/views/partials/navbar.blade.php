<nav id="mainNavbar" class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid k3-navbar-shell">

        <div class="d-flex align-items-center navbar-brand-block">
           <img
               src="/images/Logo2.png"
               alt="Logo"
               class="brand-logo"
               width="42"
               height="42"
               decoding="async">
            <span class="brand-separator" aria-hidden="true"></span>
            <div class="brand-text lh-1">
                <div>Direktorat Jenderal Pembinaan</div>
                <div>Pengawasan Ketenagakerjaan dan K3</div>
                <div class="brand-title">Balai K3 Surabaya</div>
            </div>
        </div>

        <button
            class="navbar-toggler border-0 ms-auto"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation" 
            id="navbarToggler"
        >
            <span class="menu-icon" aria-hidden="true">
                <span class="line line-1"></span>
                <span class="line line-2"></span>
                <span class="line line-3"></span>
            </span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center gap-lg-4 gap-2">

                <li class="nav-item menu-nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="/">Beranda</a>
                </li>

                <li class="nav-item dropdown menu-nav-item service-nav-item">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('daftar_pelayanan') || request()->is('riwayat_pelayanan') ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="serviceDropdownTrigger">
                        <span>Pelayanan</span>
                        <i class="bi bi-chevron-down service-caret" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu service-dropdown-menu" aria-labelledby="serviceDropdownTrigger">
                        <li><a class="dropdown-item" href="/daftar_pelayanan">Daftar Pelayanan</a></li>
                        <li><a class="dropdown-item" href="/riwayat_pelayanan">Riwayat Pelayanan</a></li>
                        <li><a class="dropdown-item" href="{{ route('user.suket.index') }}">Penerbitan Suket K3</a></li>
                    </ul>
                </li>

                <li class="nav-item menu-nav-item">
                    <a class="nav-link {{ request()->is('berita*') ? 'active' : '' }}" href="/berita">Berita</a>
                </li>

                <li class="nav-item dropdown menu-nav-item about-nav-item">
                    <a class="nav-link dropdown-toggle {{ request()->is('visi_misi') || request()->is('struktur') || request()->is('video_profil') || request()->is('alur_pelayanan') || request()->is('sarana_prasarana') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown" aria-expanded="false" id="aboutDropdownTrigger">
                        <span>Tentang Kami</span>
                        <i class="bi bi-chevron-down about-caret" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu about-dropdown-menu" aria-labelledby="aboutDropdownTrigger">
                        <li><a class="dropdown-item" href="/visi_misi">Visi & Misi</a></li>
                        <li><a class="dropdown-item" href="/struktur">Struktur Organisasi</a></li>
                        <li><a class="dropdown-item" href="/video_profil">Video Profil</a></li>
                        <li><a class="dropdown-item" href="/alur_pelayanan">Alur Pelayanan</a></li>
                        <li><a class="dropdown-item" href="/sarana_prasarana">Sarana Prasarana</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown menu-nav-item network-nav-item">
                    <a class="nav-link dropdown-toggle {{ request()->is('jejaring*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown" aria-expanded="false" id="networkDropdownTrigger">
                        <span>Jejaring</span>
                        <i class="bi bi-chevron-down network-caret" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu network-dropdown-menu" aria-labelledby="networkDropdownTrigger">
                        <li><a class="dropdown-item" href="{{ route('jejaring.universitas') }}">Universitas</a></li>
                        <li><a class="dropdown-item" href="{{ route('jejaring.pjk3') }}">PJK3</a></li>
                        <li><a class="dropdown-item" href="{{ route('jejaring.perusahaan') }}">Perusahaan</a></li>
                        <li><a class="dropdown-item" href="{{ route('jejaring.instansi-wilayah-kerja') }}">Instansi Wilayah Kerja</a></li>
                        <li><a class="dropdown-item" href="{{ route('jejaring.instansi') }}">Instansi</a></li>
                    </ul>
                </li>

                <li class="nav-item menu-nav-item menu-contact-item">
                    <a class="nav-link {{ request()->routeIs('kontak') ? 'active' : '' }}" href="/kontak">Kontak</a>
                </li>

                @guest
                    <li class="nav-item auth-nav-item auth-login-item">
                        <a href="/login" class="btn auth-btn auth-btn-primary px-4 auth-login-btn">Login</a>
                    </li>
                    <li class="nav-item auth-nav-item">
                        <a href="/register" class="btn auth-btn auth-btn-outline">Daftar</a>
                    </li>
                @endguest

                @auth
                    <li class="nav-item auth-icon-item">
                        <a href="/notifikasi" class="position-relative icon-link" aria-label="Notifikasi">
                            <i class="bi bi-bell auth-nav-icon auth-bell-icon"></i>
                            @if(($unreadNotifCount ?? 0) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $unreadNotifCount }}
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            @endif
                        </a>
                    </li>

                    <li class="nav-item dropdown auth-icon-item">
                        <a class="nav-link dropdown-toggle profile-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person auth-nav-icon auth-profile-icon"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li class="dropdown-item-text fw-semibold">{{ auth()->user()->name }}</li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth

            </ul>
        </div>

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&display=swap');

            #mainNavbar {
                z-index: 9999;
                padding: 10px 28px;
                margin-top: 10px;
                font-family: 'Montserrat', sans-serif;
                background: transparent;
            }

            #mainNavbar .k3-navbar-shell {
                background: #ffffff;
                border-radius: 999px;
                border: none;
                box-shadow: none;
                transition:
                    box-shadow .45s cubic-bezier(0.22, 1, 0.36, 1),
                    padding .45s cubic-bezier(0.22, 1, 0.36, 1),
                    min-height .45s cubic-bezier(0.22, 1, 0.36, 1);
                padding: 12px 24px;
                min-height: 86px;
                max-width: 1140px;
                margin: 0 auto;
            }

            #mainNavbar.is-scrolled .k3-navbar-shell {
                box-shadow: 0 4px 14px rgba(15, 40, 71, 0.10);
                padding-top: 8px;
                padding-bottom: 8px;
                min-height: 72px;
            }

            .navbar-brand-block {
                min-width: 0;
                gap: 10px;
                margin-left: 16px;
            }

            .brand-logo {
                height: 42px;
                width: 42px;
                object-fit: contain;
                transition: width .45s cubic-bezier(0.22, 1, 0.36, 1), height .45s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .brand-separator {
                width: 2px;
                height: 44px;
                background: #8ea0b2;
                border-radius: 999px;
                flex-shrink: 0;
                transition: height .45s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .brand-text {
                color: #18456d;
                min-width: 0;
                font-weight: 600;
            }

            .brand-text div {
                font-size: 11px;
                line-height: 1.25;
                transition: font-size .45s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .brand-text .brand-title {
                margin-top: 4px;
                font-size: 13px;
                font-weight: 700;
                line-height: 1;
                color: #15406a;
                transition: font-size .45s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .navbar-nav {
                font-size: 13px;
                font-weight: 700;
                gap: 0 !important;
            }

            #navbarNav {
                padding-left: 42px;
                padding-right: 8px;
            }

            .nav-link {
                color: #18456d !important;
                position: relative;
                padding: 8px 0 !important;
                line-height: 1;
                font-size: 13px;
                font-weight: 700;
                border-bottom: 3px solid transparent;
                transition: padding .45s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .about-nav-item .nav-link,
            .service-nav-item .nav-link,
            .network-nav-item .nav-link {
                display: inline-flex;
                align-items: center;
                gap: 7px;
            }

            .about-caret,
            .service-caret,
            .network-caret {
                font-size: 10px;
                line-height: 1;
                transition:
                    transform .32s cubic-bezier(0.22, 1, 0.36, 1),
                    opacity .2s ease;
            }

            .show > .nav-link .about-caret,
            .show > .nav-link .network-caret {
                transform: rotate(180deg);
            }

            .show > .nav-link .service-caret {
                transform: rotate(180deg);
            }

            .about-nav-item.is-opening .about-caret,
            .about-nav-item.is-open .about-caret,
            .network-nav-item.is-opening .network-caret,
            .network-nav-item.is-open .network-caret {
                transform: rotate(180deg);
            }

            .service-nav-item.is-opening .service-caret,
            .service-nav-item.is-open .service-caret {
                transform: rotate(180deg);
            }

            .nav-link::after {
                content: none;
            }

            .nav-link:hover::after,
            .menu-nav-item:hover > .nav-link::after,
            .nav-link.active::after,
            .show > .nav-link::after {
                transform: none;
            }

            .nav-link:hover,
            .menu-nav-item:hover > .nav-link,
            .nav-link.active,
            .show > .nav-link {
                border-bottom-color: #18456d;
            }

            .dropdown-toggle::after { display: none !important; }

            .about-dropdown-menu,
            .service-dropdown-menu,
            .network-dropdown-menu {
                margin-top: 12px !important;
                padding: 16px 10px;
                min-width: 248px;
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%) !important;
                border-radius: 20px;
                border: 2px solid #39b68f;
                background: #f6f7f6;
                box-shadow: 0 12px 28px rgba(21, 64, 106, 0.12);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                filter: blur(6px);
                transform-origin: top center;
                transition:
                    opacity .24s ease,
                    transform .32s cubic-bezier(0.22, 1, 0.36, 1),
                    filter .32s cubic-bezier(0.22, 1, 0.36, 1),
                    visibility .24s ease;
            }

            .about-dropdown-menu .dropdown-item,
            .service-dropdown-menu .dropdown-item,
            .network-dropdown-menu .dropdown-item {
                border-radius: 12px;
                padding: 10px 22px;
                color: #18456d;
                font-size: 13px;
                font-weight: 600;
                white-space: nowrap;
                transition:
                    background-color .2s ease,
                    color .2s ease,
                    transform .22s ease;
            }

            .about-dropdown-menu .dropdown-item:hover,
            .about-dropdown-menu .dropdown-item:focus,
            .network-dropdown-menu .dropdown-item:hover,
            .network-dropdown-menu .dropdown-item:focus {
                background: rgba(57, 182, 143, 0.10);
                color: #15406a;
                transform: translateX(4px);
            }

            .service-dropdown-menu .dropdown-item:hover,
            .service-dropdown-menu .dropdown-item:focus {
                background: rgba(57, 182, 143, 0.10);
                color: #15406a;
                transform: translateX(4px);
            }

            .about-nav-item .dropdown-menu.show {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                filter: blur(0);
                transform: translateX(-50%) translateY(0) scale(1) !important;
                animation: aboutDropdownReveal .28s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .service-nav-item .dropdown-menu.show {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                filter: blur(0);
                transform: translateX(-50%) translateY(0) scale(1) !important;
                animation: aboutDropdownReveal .28s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .network-nav-item .dropdown-menu.show {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                filter: blur(0);
                transform: translateX(-50%) translateY(0) scale(1) !important;
                animation: aboutDropdownReveal .28s cubic-bezier(0.22, 1, 0.36, 1);
            }

            .about-nav-item .dropdown-menu:not(.show),
            .about-nav-item.is-opening .about-dropdown-menu {
                transform: translateX(-50%) translateY(-10px) scale(0.96) !important;
            }

            .service-nav-item .dropdown-menu:not(.show),
            .service-nav-item.is-opening .service-dropdown-menu {
                transform: translateX(-50%) translateY(-10px) scale(0.96) !important;
            }

            .network-nav-item .dropdown-menu:not(.show),
            .network-nav-item.is-opening .network-dropdown-menu {
                transform: translateX(-50%) translateY(-10px) scale(0.96) !important;
            }

            .about-nav-item.is-opening .about-dropdown-menu,
            .about-nav-item.is-open .about-dropdown-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                filter: blur(0);
                transform: translateX(-50%) translateY(0) scale(1) !important;
            }

            .service-nav-item.is-opening .service-dropdown-menu,
            .service-nav-item.is-open .service-dropdown-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                filter: blur(0);
                transform: translateX(-50%) translateY(0) scale(1) !important;
            }

            .network-nav-item.is-opening .network-dropdown-menu,
            .network-nav-item.is-open .network-dropdown-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                filter: blur(0);
                transform: translateX(-50%) translateY(0) scale(1) !important;
            }

            .dropdown-submenu {
                position: relative;
            }

            .dropdown-submenu-toggle {
                width: 100%;
                border: 0;
                background: transparent;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                text-align: left;
            }

            .dropdown-submenu-toggle .submenu-caret {
                font-size: 10px;
                line-height: 1;
                transition: transform .24s ease;
            }

            .dropdown-submenu.show > .dropdown-submenu-toggle .submenu-caret {
                transform: rotate(90deg);
            }

            .dropdown-submenu-menu {
                display: block;
                margin-top: 0 !important;
                padding: 10px 8px;
                min-width: 220px;
                left: calc(100% + 10px) !important;
                right: auto !important;
                top: -10px !important;
                transform: translateY(6px) scale(0.97) !important;
                border-radius: 18px;
                border: 2px solid #39b68f;
                background: #f6f7f6;
                box-shadow: 0 12px 24px rgba(21, 64, 106, 0.12);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                filter: blur(6px);
                transition:
                    opacity .22s ease,
                    transform .28s cubic-bezier(0.22, 1, 0.36, 1),
                    filter .28s cubic-bezier(0.22, 1, 0.36, 1),
                    visibility .22s ease;
            }

            .dropdown-submenu.show > .dropdown-submenu-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                filter: blur(0);
                transform: translateY(0) scale(1) !important;
            }

            .network-dropdown-menu .dropdown-submenu-menu .dropdown-item {
                padding: 10px 18px;
            }

            @keyframes aboutDropdownReveal {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(-8px) scale(0.96);
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0) scale(1);
                }
            }

            .icon-link,
            .profile-toggle {
                color: #18456d !important;
            }

            .auth-icon-item + .auth-icon-item {
                margin-left: 10px;
            }

            .auth-nav-icon {
                font-size: 21px;
                line-height: 1;
            }

            .auth-profile-icon {
                font-size: 22px;
            }

            .auth-bell-icon {
                font-size: 18px;
            }

            .auth-btn {
                border-radius: 999px;
                font-weight: 700;
                font-size: 12px;
                padding-top: 7px;
                padding-bottom: 7px;
                transition: padding .45s cubic-bezier(0.22, 1, 0.36, 1);
            }

            #mainNavbar.is-scrolled .brand-logo {
                width: 36px;
                height: 36px;
            }

            #mainNavbar.is-scrolled .brand-separator {
                height: 36px;
            }

            #mainNavbar.is-scrolled .brand-text div {
                font-size: 10px;
            }

            #mainNavbar.is-scrolled .brand-text .brand-title {
                font-size: 12px;
            }

            #mainNavbar.is-scrolled .nav-link {
                padding-top: 6px !important;
                padding-bottom: 6px !important;
            }

            #mainNavbar.is-scrolled .auth-btn {
                padding-top: 6px;
                padding-bottom: 6px;
            }

            .auth-nav-item + .auth-nav-item {
                margin-left: 8px;
            }

            .auth-login-item {
                margin-left: 14px;
            }

            .auth-login-btn {
                margin-left: 2px;
            }

            .menu-nav-item + .menu-nav-item {
                margin-left: 26px;
            }

            .menu-nav-item {
                transform: translateX(-18px);
            }

            .menu-contact-item {
                margin-right: 10px;
            }

            .auth-btn-primary {
                background-color: #15406a;
                border: 1px solid #15406a;
                color: #fff;
            }

            .auth-btn-primary:hover,
            .auth-btn-primary:focus {
                background: #fff;
                border: 1px solid #15406a;
                color: #15406a;
            }

            .auth-btn-outline {
                border: 1px solid #15406a;
                color: #15406a;
                background: #fff;
            }

            .auth-btn-outline:hover,
            .auth-btn-outline:focus {
                background-color: #15406a;
                border-color: #15406a;
                color: #fff;
            }

            #navbarToggler {
                width: 44px;
                height: 36px;
                border-radius: 10px;
                padding: 0;
                align-items: center;
                justify-content: center;
                box-shadow: none !important;
                background: #fff;
            }

            #navbarToggler .menu-icon {
                width: 18px;
                height: 14px;
                position: relative;
                display: inline-block;
            }

            #navbarToggler .line {
                position: absolute;
                left: 0;
                width: 100%;
                height: 2px;
                background: #18456d;
                border-radius: 2px;
                transition: transform .3s ease, opacity .25s ease, top .3s ease;
            }

            #navbarToggler .line-1 { top: 0; }
            #navbarToggler .line-2 { top: 6px; }
            #navbarToggler .line-3 { top: 12px; }

            #navbarToggler.is-open .line-1 { top: 6px; transform: rotate(45deg); }
            #navbarToggler.is-open .line-2 { opacity: 0; }
            #navbarToggler.is-open .line-3 { top: 6px; transform: rotate(-45deg); }

            #navbarNav {
                transition: height .35s ease, opacity .35s ease, transform .35s ease;
            }

            #navbarNav.collapsing {
                opacity: 0;
                transform: translateY(-6px);
            }

            #navbarNav.collapse.show {
                opacity: 1;
                transform: translateY(0);
            }

            @media (max-width: 991.98px) {
                #mainNavbar {
                    padding: 0 14px 8px;
                    margin-top: 0;
                    top: 14px;
                    overflow: visible;
                }

                #mainNavbar .k3-navbar-shell {
                    position: relative;
                    overflow: visible;
                    border-radius: 24px;
                    padding: 12px 16px;
                }

                .brand-logo {
                    height: 40px;
                    width: 40px;
                }

                .brand-separator { height: 40px; }
                .navbar-brand-block { margin-left: 4px; }

                .brand-text div { font-size: 10px; }
                .brand-text .brand-title { font-size: 14px; }

                #mainNavbar .container-fluid {
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) auto;
                    align-items: center;
                    row-gap: 0;
                }

                #mainNavbar .navbar-collapse {
                    position: absolute;
                    top: calc(100% + 10px);
                    left: 0;
                    right: 0;
                    z-index: 30;
                    grid-column: 1 / -1;
                    padding: 14px 16px 16px;
                    background: #ffffff;
                    border-radius: 24px;
                    box-shadow: 0 16px 30px rgba(15, 40, 71, 0.14);
                }

                #mainNavbar .navbar-nav {
                    width: 100%;
                    align-items: center !important;
                    gap: .65rem !important;
                }

                #navbarNav {
                    padding-right: 0;
                    transform-origin: top center;
                }

                #navbarNav.collapse:not(.show) {
                    display: none;
                }

                #navbarNav.collapsing,
                #navbarNav.collapse.show {
                    display: block;
                }

                .auth-nav-item + .auth-nav-item {
                    margin-left: 0;
                }

                .auth-icon-item + .auth-icon-item {
                    margin-left: 0;
                }

                .menu-contact-item {
                    margin-right: 0;
                }

                .menu-nav-item + .menu-nav-item {
                    margin-left: 0;
                }

                .menu-nav-item {
                    transform: none;
                }

                .about-nav-item .nav-link,
                .service-nav-item .nav-link,
                .network-nav-item .nav-link {
                    justify-content: center;
                }

                .about-dropdown-menu,
                .service-dropdown-menu,
                .network-dropdown-menu {
                    margin-top: 8px !important;
                    position: static !important;
                    inset: auto !important;
                    left: auto !important;
                    right: auto !important;
                    transform: none !important;
                    min-width: 100%;
                    width: 100%;
                }

                .about-nav-item .dropdown-menu.show,
                .service-nav-item .dropdown-menu.show,
                .network-nav-item .dropdown-menu.show,
                .about-nav-item .dropdown-menu:not(.show),
                .service-nav-item .dropdown-menu:not(.show),
                .network-nav-item .dropdown-menu:not(.show),
                .about-nav-item.is-opening .about-dropdown-menu,
                .about-nav-item.is-open .about-dropdown-menu,
                .service-nav-item.is-opening .service-dropdown-menu,
                .service-nav-item.is-open .service-dropdown-menu,
                .network-nav-item.is-opening .network-dropdown-menu,
                .network-nav-item.is-open .network-dropdown-menu {
                    transform: none !important;
                }

                .dropdown-submenu-toggle {
                    justify-content: center;
                    gap: 10px;
                    text-align: center;
                }

                .dropdown-submenu.show > .dropdown-submenu-toggle .submenu-caret {
                    transform: rotate(180deg);
                }

                .dropdown-submenu-menu {
                    display: none;
                    position: static !important;
                    inset: auto !important;
                    left: auto !important;
                    right: auto !important;
                    top: auto !important;
                    min-width: 100%;
                    width: 100%;
                    margin-top: 8px !important;
                    border-width: 1px;
                    box-shadow: none;
                    opacity: 1;
                    visibility: visible;
                    pointer-events: auto;
                    filter: none;
                    transform: none !important;
                }

                .dropdown-submenu.show > .dropdown-submenu-menu {
                    display: block;
                }

                .auth-login-item,
                .auth-login-btn {
                    margin-left: 0;
                }

                #mainNavbar .nav-item,
                #mainNavbar .nav-link {
                    width: 100%;
                    text-align: center !important;
                }

                #mainNavbar .menu-nav-item > .nav-link {
                    display: inline-flex;
                    width: auto;
                    margin: 0 auto;
                    padding-left: 14px !important;
                    padding-right: 14px !important;
                    justify-content: center;
                }

                .auth-btn {
                    width: 100%;
                }
            }

            @media (min-width: 992px) {
                #navbarNav {
                    padding-left: 56px;
                }

                #navbarToggler { display: none !important; }
            }
        </style>
    </div>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const navbar = document.getElementById('mainNavbar');
        const navbarNav = document.getElementById('navbarNav');
        const navbarToggler = document.getElementById('navbarToggler');
        const topLevelDropdownItems = Array.from(document.querySelectorAll('.menu-nav-item.dropdown'));
        const submenuItems = Array.from(document.querySelectorAll('.dropdown-submenu'));
        if (!navbarNav || !navbarToggler || !navbar) return;

        if (navbarNav.classList.contains('show')) {
            navbarToggler.classList.add('is-open');
            navbarToggler.setAttribute('aria-expanded', 'true');
        }

        navbarNav.addEventListener('show.bs.collapse', function () {
            navbarToggler.classList.add('is-open');
            navbarToggler.setAttribute('aria-expanded', 'true');
        });

        navbarNav.addEventListener('hide.bs.collapse', function () {
            navbarToggler.classList.remove('is-open');
            navbarToggler.setAttribute('aria-expanded', 'false');
        });

        const shrinkEnterThreshold = 24;
        const shrinkLeaveThreshold = 6;
        let isShrunk = window.scrollY > shrinkEnterThreshold;

        const syncNavbarScrollState = function () {
            const currentY = window.scrollY;

            if (!isShrunk && currentY > shrinkEnterThreshold) {
                isShrunk = true;
                navbar.classList.add('is-scrolled');
                return;
            }

            if (isShrunk && currentY < shrinkLeaveThreshold) {
                isShrunk = false;
                navbar.classList.remove('is-scrolled');
            }
        };

        if (isShrunk) {
            navbar.classList.add('is-scrolled');
        }

        syncNavbarScrollState();
        window.addEventListener('scroll', syncNavbarScrollState, { passive: true });

        const closeSubmenu = (submenu) => {
            const toggle = submenu.querySelector(':scope > .dropdown-submenu-toggle');
            const menu = submenu.querySelector(':scope > .dropdown-submenu-menu');
            submenu.classList.remove('show');
            toggle?.setAttribute('aria-expanded', 'false');
            menu?.classList.remove('show');
        };

        const closeSiblingSubmenus = (submenu) => {
            const parent = submenu.parentElement;
            if (!parent) return;
            Array.from(parent.children).forEach((child) => {
                if (child !== submenu && child.classList?.contains('dropdown-submenu')) {
                    closeSubmenu(child);
                }
            });
        };

        const openSubmenu = (submenu) => {
            const toggle = submenu.querySelector(':scope > .dropdown-submenu-toggle');
            const menu = submenu.querySelector(':scope > .dropdown-submenu-menu');
            closeSiblingSubmenus(submenu);
            submenu.classList.add('show');
            toggle?.setAttribute('aria-expanded', 'true');
            menu?.classList.add('show');
        };

        const resetNestedSubmenus = (root) => {
            root.querySelectorAll('.dropdown-submenu').forEach((submenu) => closeSubmenu(submenu));
        };

        topLevelDropdownItems.forEach((item) => {
            item.addEventListener('show.bs.dropdown', function () {
                item.classList.add('is-opening');
                item.classList.remove('is-open');
                resetNestedSubmenus(item);
            });

            item.addEventListener('shown.bs.dropdown', function () {
                item.classList.remove('is-opening');
                item.classList.add('is-open');
            });

            item.addEventListener('hide.bs.dropdown', function () {
                item.classList.remove('is-opening');
                item.classList.remove('is-open');
                resetNestedSubmenus(item);
            });
        });

        submenuItems.forEach((submenu) => {
            const toggle = submenu.querySelector(':scope > .dropdown-submenu-toggle');
            const menu = submenu.querySelector(':scope > .dropdown-submenu-menu');
            if (!toggle || !menu) return;

            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (submenu.classList.contains('show')) {
                    closeSubmenu(submenu);
                    return;
                }

                openSubmenu(submenu);
            });

            submenu.addEventListener('mouseenter', function () {
                if (window.matchMedia('(min-width: 992px)').matches) {
                    openSubmenu(submenu);
                }
            });

            submenu.addEventListener('mouseleave', function () {
                if (window.matchMedia('(min-width: 992px)').matches) {
                    closeSubmenu(submenu);
                }
            });

            menu.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        });

        const canUseHoverDropdown = window.matchMedia('(min-width: 992px)').matches;
        if (canUseHoverDropdown) {
            const closeTimers = new WeakMap();

            const clearCloseTimer = (item) => {
                const timer = closeTimers.get(item);
                if (timer) {
                    clearTimeout(timer);
                    closeTimers.delete(item);
                }
            };
            const closeDropdown = (item) => {
                const toggle = item.querySelector('[data-bs-toggle="dropdown"]');
                const menu = item.querySelector('.dropdown-menu');
                if (!toggle || !menu) return;
                item.classList.remove('show', 'is-opening', 'is-open');
                toggle.classList.remove('show');
                toggle.setAttribute('aria-expanded', 'false');
                menu.classList.remove('show');
                resetNestedSubmenus(item);
            };
            const scheduleClose = (item) => {
                clearCloseTimer(item);
                const timer = setTimeout(() => closeDropdown(item), 180);
                closeTimers.set(item, timer);
            };
            const openDropdown = (item) => {
                const toggle = item.querySelector('[data-bs-toggle="dropdown"]');
                const menu = item.querySelector('.dropdown-menu');
                if (!toggle || !menu) return;
                clearCloseTimer(item);
                topLevelDropdownItems.forEach((other) => {
                    if (other !== item) {
                        clearCloseTimer(other);
                        closeDropdown(other);
                    }
                });
                item.classList.remove('is-opening');
                item.classList.add('show', 'is-open');
                toggle.classList.add('show');
                toggle.setAttribute('aria-expanded', 'true');
                menu.classList.add('show');
            };

            topLevelDropdownItems.forEach((item) => {
                const toggle = item.querySelector('[data-bs-toggle="dropdown"]');
                const menu = item.querySelector('.dropdown-menu');
                item.addEventListener('mouseenter', () => openDropdown(item));
                item.addEventListener('mouseleave', () => scheduleClose(item));
                toggle?.addEventListener('focus', () => openDropdown(item));
                menu?.addEventListener('mouseenter', () => openDropdown(item));
                menu?.addEventListener('mouseleave', () => scheduleClose(item));
            });
        }
    });
</script>
