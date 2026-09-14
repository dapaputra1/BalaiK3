<style>
    .bk3-footer {
        background: #15406a;
        color: #ffffff;
        padding: 35px 44px 18px;
        font-family: 'Poppins', sans-serif;
    }

    .bk3-footer-shell {
        max-width: 1350px;
        margin: 0 auto;
    }

    .bk3-footer-grid {
        display: grid;
        grid-template-columns: minmax(0, 2.2fr) minmax(150px, 0.78fr) minmax(180px, 0.92fr) minmax(245px, 1fr);
        gap: 36px;
        align-items: start;
    }

    .bk3-footer-brand {
        padding-right: 22px;
    }

    .bk3-footer-brandhead {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 22px;
    }

    .bk3-footer-logo {
        width: 52px;
        max-width: 100%;
        display: block;
        filter: brightness(0) invert(1);
        flex: 0 0 auto;
    }

    .bk3-footer-brandcopy {
        padding-left: 8px;
        border-left: 1px solid rgba(255, 255, 255, 0.92);
        line-height: 1.12;
    }

    .bk3-footer-brandcopy span,
    .bk3-footer-brandcopy strong {
        display: block;
    }

    .bk3-footer-brandcopy span {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .bk3-footer-brandcopy strong {
        margin-top: 2px;
        font-size: 14px;
        font-weight: 700;
    }

    .bk3-footer-meta {
        display: grid;
        gap: 10px;
    }

    .bk3-footer-metaitem {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #ffffff;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.35;
    }

    .bk3-footer-metaitem i {
        font-size: 19px;
        line-height: 1;
        margin-top: 1px;
        flex: 0 0 20px;
    }

    .bk3-footer-coltitle {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 18px;
        color: #ffffff;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.1;
    }

    .bk3-footer-coltitle::before {
        content: "";
        width: 2px;
        height: 19px;
        border-radius: 999px;
        background: #22c59c;
        flex: 0 0 auto;
    }

    .bk3-footer-links {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 10px;
    }

    .bk3-footer-links a {
        color: #ffffff;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.25;
    }

    .bk3-footer-links a:hover {
        color: #d8ecff;
    }

    .bk3-footer-contactlist {
        display: grid;
        gap: 10px;
        margin-bottom: 18px;
    }

    .bk3-footer-contactitem {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #ffffff;
    }

    .bk3-footer-contacticon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #15406a;
        background: #ffffff;
        font-size: 16px;
        flex: 0 0 28px;
        margin-top: 1px;
    }

    .bk3-footer-contactlabel {
        display: block;
        margin-bottom: 1px;
        font-size: 10px;
        font-weight: 500;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.92);
    }

    .bk3-footer-contacttext,
    .bk3-footer-contacttext a {
        color: #ffffff;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.25;
    }

    .bk3-footer-socials {
        display: flex;
        align-items: center;
        gap: 11px;
        padding-top: 4px;
    }

    .bk3-footer-socials a {
        width: 27px;
        height: 27px;
        border-radius: 4px;
        background: #ffffff;
        color: #15406a;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        text-decoration: none;
    }

    .bk3-footer-divider {
        margin: 28px 0 12px;
        border: 0;
        border-top: 1px solid rgba(255, 255, 255, 0.9);
        opacity: 1;
    }

    .bk3-footer-copy {
        text-align: center;
        color: rgba(255, 255, 255, 0.95);
        font-size: 10px;
        font-weight: 400;
        line-height: 1.2;
    }

    @media (max-width: 991.98px) {
        .bk3-footer {
            padding: 30px 20px 18px;
        }

        .bk3-footer-grid {
            grid-template-columns: 1fr 1fr;
            gap: 28px 22px;
        }

        .bk3-footer-brand {
            grid-column: 1 / -1;
            padding-right: 0;
        }
    }

    @media (max-width: 575.98px) {
        .bk3-footer {
            padding: 22px 14px 16px;
        }

        .bk3-footer-grid {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 20px 14px;
        }

        .bk3-footer-brand,
        .bk3-footer-contact {
            grid-column: 1 / -1;
        }

        .bk3-footer-nav {
            grid-column: 1;
        }

        .bk3-footer-info {
            grid-column: 2;
        }

        .bk3-footer-brandhead {
            gap: 8px;
            margin-bottom: 12px;
        }

        .bk3-footer-logo {
            width: 40px;
        }

        .bk3-footer-brandcopy span {
            font-size: 10px;
        }

        .bk3-footer-brandcopy strong,
        .bk3-footer-coltitle {
            font-size: 12px;
        }

        .bk3-footer-metaitem,
        .bk3-footer-links a,
        .bk3-footer-contacttext,
        .bk3-footer-contacttext a {
            font-size: 10px;
        }

        .bk3-footer-coltitle {
            margin-bottom: 12px;
            gap: 6px;
        }

        .bk3-footer-coltitle::before {
            height: 15px;
        }

        .bk3-footer-links,
        .bk3-footer-meta,
        .bk3-footer-contactlist {
            gap: 8px;
        }

        .bk3-footer-metaitem,
        .bk3-footer-contactitem {
            gap: 8px;
        }

        .bk3-footer-metaitem i {
            font-size: 15px;
            flex-basis: 16px;
        }

        .bk3-footer-contacticon {
            width: 24px;
            height: 24px;
            font-size: 13px;
            flex-basis: 24px;
        }

        .bk3-footer-contactlabel {
            font-size: 8px;
        }

        .bk3-footer-socials {
            gap: 8px;
            padding-top: 2px;
        }

        .bk3-footer-socials a {
            width: 24px;
            height: 24px;
            font-size: 13px;
        }

        .bk3-footer-divider {
            margin: 20px 0 10px;
        }

        .bk3-footer-copy {
            font-size: 9px;
        }
    }
</style>

<footer class="bk3-footer">
    <div class="bk3-footer-shell">
        <div class="bk3-footer-grid">
            <div class="bk3-footer-brand">
                <div class="bk3-footer-brandhead">
                    <img
                        src="{{ asset('images/Logo.png') }}"
                        alt="Logo Kemnaker Balai K3 Surabaya"
                        class="bk3-footer-logo">
                    <div class="bk3-footer-brandcopy">
                        <span>Direktorat Jenderal Pembinaan</span>
                        <span>Pengawasan Ketenagakerjaan dan K3</span>
                        <strong>Balai K3 Surabaya</strong>
                    </div>
                </div>

                <div class="bk3-footer-meta">
                    <div class="bk3-footer-metaitem">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Jl. Dukuh Menanggal Sel. No.122, Dukuh Menanggal,<br>Kec. Gayungan, Surabaya, Jawa Timur 60234</span>
                    </div>
                    <div class="bk3-footer-metaitem">
                        <i class="bi bi-clock-fill"></i>
                        <span>Senin - Jumat : 07.30 - 16.00 WIB</span>
                    </div>
                </div>
            </div>

            <div class="bk3-footer-nav">
                <h6 class="bk3-footer-coltitle">Jelajahi</h6>
                <ul class="bk3-footer-links">
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('daftar_pelayanan') }}">Pelayanan</a></li>
                    <li><a href="{{ route('berita') }}">Berita</a></li>
                    <li><a href="{{ route('struktur') }}">Tentang Kami</a></li>
                    <li><a href="{{ route('kontak') }}">Kontak</a></li>
                </ul>
            </div>

            <div class="bk3-footer-info">
                <h6 class="bk3-footer-coltitle">Informasi</h6>
                <ul class="bk3-footer-links">
                    <li><a href="{{ url('/visi_misi') }}">Visi dan Misi</a></li>
                    <li><a href="{{ url('/sarana_prasarana') }}">Sarana &amp; Prasana</a></li>
                    <li><a href="{{ url('/struktur') }}">Struktur Organisasi</a></li>
                    <li><a href="{{ url('/alur_pelayanan') }}">Alur Pelayanan</a></li>
                    <li><a href="{{ route('home') }}#data-statistik-layanan">Data &amp; Statistik Layanan</a></li>
                </ul>
            </div>

            <div class="bk3-footer-contact">
                <h6 class="bk3-footer-coltitle">Kontak</h6>
                <div class="bk3-footer-contactlist">
                    <div class="bk3-footer-contactitem">
                        <span class="bk3-footer-contacticon"><i class="bi bi-telephone-fill"></i></span>
                        <div>
                            <span class="bk3-footer-contactlabel">Telepon</span>
                            <div class="bk3-footer-contacttext">085111380122</div>
                        </div>
                    </div>

                    <div class="bk3-footer-contactitem">
                        <span class="bk3-footer-contacticon"><i class="bi bi-envelope-fill"></i></span>
                        <div>
                            <span class="bk3-footer-contactlabel">Email</span>
                            <div class="bk3-footer-contacttext">
                                <a href="mailto:BalaiK3surabaya@kemnaker.go.id">BalaiK3surabaya@kemnaker.go.id</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bk3-footer-socials">
                    <a href="https://www.instagram.com/balaik3surabaya/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="https://www.facebook.com/Balai-Hiperkes-dan-KK-Surabaya" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="https://www.youtube.com/@HumasBalaiK3Surabaya" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                        <i class="bi bi-youtube"></i>
                    </a>
                    <a href="https://www.tiktok.com/@balai_k3.surabaya" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <i class="bi bi-tiktok"></i>
                    </a>
                </div>
            </div>
        </div>

        <hr class="bk3-footer-divider">

        <div class="bk3-footer-copy">
            @ 2026 Balai K3 Surabaya. All Rights Reserved.
        </div>
    </div>
</footer>
