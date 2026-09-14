@php
    $loginBackground = \App\Models\LoginBackground::activeUrl();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Pelayanan K3</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo2.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --login-primary: #0e5f49;
            --login-primary-dark: #0a4635;
            --login-accent: #cfe9de;
            --login-panel: rgba(255, 255, 255, 0.42);
            --login-text: #17342d;
            --login-muted: #5c6f68;
            --login-border: rgba(23, 52, 45, 0.12);
            --login-shadow: 0 32px 70px rgba(14, 46, 39, 0.22);
            --login-bg-position: center -80px;
            --login-bg-size: cover;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: var(--login-text);
            background: #dfeee8;
            overflow: hidden;
        }

        .login-shell {
            position: relative;
            height: 100vh;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
            justify-content: flex-end;
            padding: 24px 36px;
            overflow: hidden;
            background-image: url('{{ $loginBackground }}');
            background-position: var(--login-bg-position);
            background-repeat: no-repeat;
            background-size: var(--login-bg-size);
        }

        @media (min-width: 992px) {
            .login-shell {
                background-position: center -80px !important;
                background-size: cover !important;
            }
        }

        .mobile-register-banner,
        .mobile-register-hero {
            display: none;
        }

        .login-hero {
            position: relative;
            z-index: 1;
            flex: 1 1 auto;
            min-height: 100%;
            max-width: 58vw;
            padding: 0 3vw 0 0;
            color: #fff;
        }

        .login-hero-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100%;
            padding-top: 0;
        }

        .login-hero-top {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 8px;
            width: fit-content;
            max-width: 100%;
            margin-left: calc(-1 * min(2.4vw, 46px));
            isolation: isolate;
        }

        .login-hero-top::before {
            content: "";
            position: absolute;
            inset: -16px -24px -18px -16px;
            border-radius: 42px;
            background: radial-gradient(
                ellipse at 32% 38%,
                rgba(223, 240, 247, 0.42) 0%,
                rgba(223, 240, 247, 0.24) 48%,
                rgba(223, 240, 247, 0.1) 72%,
                rgba(223, 240, 247, 0) 100%
            );
            filter: blur(10px);
            z-index: -1;
            pointer-events: none;
        }

        .login-hero-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            max-width: 420px;
        }

        .login-hero-brand img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            padding: 0;
            flex-shrink: 0;
        }

        .login-hero-brand p {
            margin: 0;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #15406a;
        }

        .login-hero-brand h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 0.92;
            color: #15406a;
        }

        .login-side {
            position: relative;
            z-index: 1;
            width: min(430px, 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            align-self: center;
            transform: translateX(-44px);
        }

        .login-panel {
            width: 100%;
            border-radius: 26px;
            padding: 30px 26px 26px;
            background: var(--login-panel);
            border: 1px solid rgba(255, 255, 255, 0.44);
            backdrop-filter: blur(18px);
            box-shadow: 0 24px 56px rgba(9, 46, 36, 0.2);
        }

        .login-panel-header {
            text-align: center;
            margin-bottom: 22px;
        }

        .login-panel-header h2 {
            margin: 0 0 4px;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--login-primary-dark);
        }

        .login-panel-header p {
            display: none;
        }

        .login-alert {
            margin: 0 0 8px;
            border: 1px solid rgba(188, 61, 61, 0.24);
            border-radius: 10px;
            background-color: rgba(255, 238, 238, 0.82);
            color: #842029;
            font-size: 0.72rem;
            padding: 8px 10px;
        }

        .login-alert ul {
            margin: 0;
            padding-left: 16px;
        }

        .login-field {
            margin-bottom: 16px;
        }

        .login-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--login-primary-dark);
        }

        .login-input {
            width: 100%;
            height: 56px;
            border-radius: 16px;
            border: 1px solid rgba(177, 194, 202, 0.6);
            background: rgba(236, 242, 249, 0.9);
            font-size: 1rem;
            color: #1f252a;
            padding: 0 18px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .login-input::placeholder {
            color: #8a98a1;
        }

        .login-input:focus {
            outline: none;
            border-color: rgba(14, 95, 73, 0.42);
            box-shadow: 0 0 0 0.2rem rgba(14, 95, 73, 0.12);
            background: #fff;
        }

        .login-password-wrap {
            position: relative;
        }

        .login-password-toggle {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: #6a7d76;
            padding: 0;
            line-height: 1;
            font-size: 1rem;
        }

        .login-password-toggle:focus {
            outline: none;
            box-shadow: none;
        }

        .login-meta {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 8px;
            margin: 2px 0 18px;
            font-size: 0.88rem;
        }

        .login-link {
            color: var(--login-primary-dark);
            font-weight: 700;
            text-decoration: none;
        }

        .login-link:hover {
            color: var(--login-primary);
        }

        .login-captcha {
            margin-bottom: 22px;
            overflow: hidden;
            line-height: 0;
        }

        .login-captcha .g-recaptcha {
            display: inline-block;
            transform: scale(1);
            transform-origin: left top;
        }

        .login-captcha .text-danger {
            display: block;
            margin-top: 8px;
            font-size: 0.9rem;
        }

        .login-submit {
            width: 100%;
            height: 56px;
            border: 0;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--login-primary), #1b7a60);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            box-shadow: 0 18px 30px rgba(14, 95, 73, 0.22);
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        }

        .login-submit:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
            box-shadow: 0 22px 36px rgba(14, 95, 73, 0.28);
        }

        .login-submit:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(14, 95, 73, 0.16), 0 18px 30px rgba(14, 95, 73, 0.22);
        }

        .login-bottom {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid rgba(23, 52, 45, 0.08);
            text-align: center;
            color: var(--login-muted);
            font-size: 0.88rem;
            line-height: 1.5;
        }

        @media (max-width: 1199.98px) {
            .login-shell {
                padding: 20px 24px;
            }

            .login-hero {
                min-height: 100%;
                padding-right: 18px;
                max-width: 52vw;
            }

            .login-hero-top {
                margin-left: 0;
            }

            .login-hero-brand img {
                width: 40px;
                height: 40px;
            }

            .login-hero-brand h1 {
                font-size: 1.45rem;
            }

            .login-panel {
                padding: 24px 20px 22px;
            }

            .login-input {
                height: 52px;
            }

            .login-side {
                width: min(400px, 100%);
                transform: translateX(-24px);
            }
        }

        @media (min-width: 992px) and (max-width: 1439.98px) {
            :root {
                --login-bg-position: center -80px;
                --login-bg-size: cover;
            }
        }

        @media (min-width: 1440px) and (max-width: 1720px) {
            :root {
                --login-bg-position: center -80px;
                --login-bg-size: cover;
            }
        }

        @media (min-width: 1720px) {
            :root {
                --login-bg-position: center -80px;
                --login-bg-size: cover;
            }
        }

        @media (min-width: 992px) and (max-aspect-ratio: 16/10) {
            :root {
                --login-bg-position: center -80px;
                --login-bg-size: cover;
            }
        }

        @media (min-width: 992px) and (min-aspect-ratio: 16/9) {
            :root {
                --login-bg-position: center -80px;
                --login-bg-size: cover;
            }
        }

        @media (min-width: 768px) and (max-height: 860px) {
            .login-shell {
                padding: 14px 22px;
            }

            .login-hero {
                min-height: calc(100vh - 28px);
            }

            .login-hero-brand img {
                width: 42px;
                height: 42px;
            }

            .login-hero-brand h1 {
                font-size: 1.65rem;
            }

            .login-panel {
                padding: 24px 22px;
            }

            .login-panel-header {
                margin-bottom: 18px;
            }

            .login-panel-header p {
                display: none;
            }

            .login-field {
                margin-bottom: 14px;
            }

            .login-input {
                height: 52px;
            }

            .login-meta {
                margin-bottom: 16px;
            }

            .login-captcha {
                margin-bottom: 18px;
            }

            .login-submit {
                height: 52px;
            }

            .login-bottom {
                margin-top: 18px;
                padding-top: 14px;
            }
        }

        @media (max-width: 991.98px) {
            body {
                background-attachment: scroll;
                overflow-x: hidden;
                overflow-y: auto;
            }

            .login-shell {
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                height: auto;
                padding: 24px 18px;
                overflow: visible;
            }

            .login-hero {
                min-height: clamp(220px, 34vh, 300px);
                max-width: 100%;
                width: 100%;
                padding: 0;
            }

            .login-side {
                width: 100%;
                max-width: 430px;
                margin: 0 auto 32px;
                transform: none;
            }
        }

        @media (max-width: 767.98px) {
            body {
                overflow-y: auto;
                background-color: #f5f7fb;
            }

            .login-shell {
                display: block;
                min-height: 100vh;
                padding: 0;
                background: none;
                overflow: visible;
            }

            .login-hero {
                display: none;
            }

            .mobile-register-banner {
                display: flex;
                align-items: center;
                min-height: 92px;
                padding: 12px 18px;
                background-color: #143D66;
            }

            .mobile-register-hero {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                width: 100%;
                max-width: 330px;
            }

            .mobile-register-hero-logo {
                width: 42px;
                height: auto;
                flex-shrink: 0;
            }

            .mobile-register-hero-text {
                text-align: left;
                color: #fff;
            }

            .mobile-register-hero-text p {
                margin: 0 0 3px;
                font-size: 8.5px;
                font-weight: 500;
                line-height: 1.25;
                opacity: 0.9;
            }

            .mobile-register-hero-text p span {
                display: block;
            }

            .mobile-register-hero-text h3 {
                margin: 0;
                font-size: 14px;
                font-weight: 700;
                line-height: 1.1;
            }

            .login-side {
                width: 100%;
                max-width: 380px;
                min-width: 0;
                padding: 0 18px 34px;
                margin: 18px auto 0;
                align-items: center;
                justify-content: center;
                transform: none;
            }

            .login-panel {
                background-color: #ffffff;
                border: 1px solid #d9e2ef;
                border-radius: 10px;
                padding: 24px 16px 22px;
                box-shadow: 0 14px 34px rgba(20, 61, 102, 0.1);
            }

            .login-panel-header h2 {
                margin-bottom: 24px;
                font-size: 20px;
                color: #143D66;
            }

            .login-label {
                font-size: 12px;
            }

            .login-input {
                height: 42px;
                font-size: 12px;
                border-radius: 12px;
            }

            .login-alert {
                font-size: 12px;
                margin-bottom: 18px;
            }

            .login-password-toggle {
                font-size: 0.95rem;
            }

            .login-meta {
                margin: 6px 0 18px;
            }

            .login-link,
            .login-bottom {
                font-size: 13px;
            }

            .login-captcha {
                margin-bottom: 18px;
                overflow: visible;
                line-height: normal;
            }

            .login-captcha .g-recaptcha {
                transform: none;
            }

            .login-submit {
                font-size: 14px;
                height: 42px;
                margin-top: 6px;
                padding: 0;
            }

            .login-bottom {
                margin-top: 18px;
                padding-top: 0;
                border-top: 0;
                text-align: left;
                line-height: 1.6;
            }
        }

        @media (max-width: 575.98px) {
            .login-side {
                padding-left: 12px;
                padding-right: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-shell">
        <div class="mobile-register-banner">
            <div class="mobile-register-hero">
                <img src="{{ asset('images/Logo.png') }}" alt="Logo Balai K3 Surabaya" class="mobile-register-hero-logo">
                <div class="mobile-register-hero-text">
                    <p>
                        <span>Direktorat Jenderal Pembinaan</span>
                        <span>Pengawasan Ketenagakerjaan dan K3</span>
                    </p>
                    <h3>Balai K3 Surabaya</h3>
                </div>
            </div>
        </div>

        <section class="login-hero" aria-hidden="true">
            <div class="login-hero-content">
                <div class="login-hero-top">
                    <div class="login-hero-brand">
                        <img src="{{ asset('images/Logo2.png') }}" alt="Logo Balai K3 Surabaya">
                        <div>
                            <p>Kementerian Ketenagakerjaan RI</p>
                            <h1>Balai K3 Surabaya</h1>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="login-side">
            <div class="login-panel">
                <div class="login-panel-header">
                    <h2>Daftar</h2>
                    <p>Silakan isi data untuk membuat akun baru.</p>
                </div>

                @if($errors->any())
                    <div class="alert login-alert">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ url('register') }}" method="POST">
                    @csrf

                    <div class="login-field">
                        <label for="name" class="login-label">Username</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="login-input"
                            placeholder="Masukkan username"
                            value="{{ old('name') }}"
                            required
                        >
                    </div>

                    <div class="login-field">
                        <label for="email" class="login-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="login-input"
                            placeholder="Masukkan email"
                            value="{{ old('email') }}"
                            required
                        >
                    </div>

                    <div class="login-field">
                        <label for="passwordInput" class="login-label">Kata Sandi</label>
                        <div class="login-password-wrap">
                            <input
                                type="password"
                                name="password"
                                id="passwordInput"
                                class="login-input pe-5"
                                placeholder="Masukkan Kata Sandi"
                                required
                            >
                            <button type="button" class="login-password-toggle" onclick="togglePassword('passwordInput', 'toggleIconPassword')" aria-label="Tampilkan kata sandi">
                                <i class="bi bi-eye" id="toggleIconPassword"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-field">
                        <label for="passwordConfirmationInput" class="login-label">Konfirmasi Kata Sandi</label>
                        <div class="login-password-wrap">
                            <input
                                type="password"
                                name="password_confirmation"
                                id="passwordConfirmationInput"
                                class="login-input pe-5"
                                placeholder="Konfirmasi Kata Sandi"
                                required
                            >
                            <button type="button" class="login-password-toggle" onclick="togglePassword('passwordConfirmationInput', 'toggleIconPasswordConfirmation')" aria-label="Tampilkan konfirmasi kata sandi">
                                <i class="bi bi-eye" id="toggleIconPasswordConfirmation"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-meta">
                        <span>Sudah punya akun? <a href="/login" class="login-link">Masuk</a></span>
                    </div>

                    <div class="login-captcha">
                        {!! NoCaptcha::display() !!}
                        @error('g-recaptcha-response')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {!! NoCaptcha::renderJs() !!}

                    <button type="submit" class="login-submit">Daftar</button>
                </form>

            </div>
        </aside>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isVisible = input.type === 'password';

            input.type = isVisible ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isVisible);
            icon.classList.toggle('bi-eye-slash', isVisible);
        }
</script>
</body>
</html>
