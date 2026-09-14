@php
    $loginBackground = \App\Models\LoginBackground::activeUrl();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo2.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --login-primary: #0e5f49;
            --login-primary-dark: #0a4635;
            --login-panel: rgba(255, 255, 255, 0.42);
            --login-text: #17342d;
            --login-muted: #5c6f68;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            background: #dfeee8;
            color: var(--login-text);
        }

        .reset-layout {
            position: relative;
            height: 100vh;
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: stretch;
            justify-content: flex-end;
            padding: 24px 36px;
            overflow: hidden;
            background-image: url('{{ $loginBackground }}');
            background-position: center -80px;
            background-repeat: no-repeat;
            background-size: cover;
        }

        @media (min-width: 992px) {
            .reset-layout {
                background-position: center -80px !important;
                background-size: cover !important;
            }
        }

        .mobile-reset-hero {
            display: none;
        }

        .left {
            position: relative;
            z-index: 1;
            flex: 1 1 auto;
            min-height: 100%;
            max-width: 58vw;
            padding: 0 3vw 0 0;
            color: #fff;
            background: transparent;
            display: block;
            text-align: left;
        }

        .left-content {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100%;
            padding-top: 0;
        }

        .left-top {
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

        .left-top::before {
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

        .desktop-reset-hero {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            max-width: 420px;
        }

        .desktop-reset-hero img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            margin: 0;
            padding: 0;
            flex-shrink: 0;
        }

        .desktop-reset-hero-text p {
            margin: 0;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #15406a;
        }

        .desktop-reset-hero-text h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 0.92;
            color: #15406a;
        }

        .right {
            position: relative;
            z-index: 1;
            width: min(430px, 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            align-self: center;
            transform: translateX(-44px);
            padding: 0;
            background: transparent;
        }

        .form-container {
            width: 100%;
        }

        .reset-panel {
            width: 100%;
            border-radius: 26px;
            padding: 30px 26px 26px;
            background: var(--login-panel);
            border: 1px solid rgba(255, 255, 255, 0.44);
            backdrop-filter: blur(18px);
            box-shadow: 0 24px 56px rgba(9, 46, 36, 0.2);
        }

        .reset-panel,
        .reset-panel * {
            font-family: 'Poppins', sans-serif;
        }

        .title {
            margin: 0 0 4px;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--login-primary-dark);
            text-align: center;
        }

        .subtitle {
            display: none;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--login-primary-dark);
        }

        input {
            width: 100%;
            height: 56px;
            padding: 0 18px;
            margin: 5px 0 16px;
            border: 1px solid rgba(177, 194, 202, 0.6);
            border-radius: 16px;
            outline: none;
            font-size: 1rem;
            background: rgba(236, 242, 249, 0.9);
            color: #1f252a;
        }

        input::placeholder {
            color: #8a98a1;
        }

        input:focus {
            border-color: rgba(14, 95, 73, 0.42);
            box-shadow: 0 0 0 0.2rem rgba(14, 95, 73, 0.12);
            background: #fff;
        }

        .btn {
            width: 100%;
            height: 56px;
            background: linear-gradient(135deg, var(--login-primary), #1b7a60);
            color: #fff;
            border: 0;
            border-radius: 16px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            box-shadow: 0 18px 30px rgba(14, 95, 73, 0.22);
        }

        .btn:hover {
            filter: brightness(1.02);
        }

        .alert {
            padding: 8px 10px;
            margin-bottom: 8px;
            border-radius: 10px;
            font-size: 0.72rem;
        }

        .alert.error {
            border: 1px solid rgba(208, 52, 44, 0.24);
            background: rgba(251, 228, 228, 0.82);
            color: #d0342c;
        }

        .mb-3 {
            margin-bottom: 8px;
        }

        .text-danger {
            display: block;
            margin-top: 6px;
            font-size: 0.72rem;
            color: #d0342c;
        }

        @media (max-width: 1199.98px) {
            .reset-layout {
                padding: 20px 24px;
            }

            .left {
                min-height: 100%;
                padding-right: 18px;
                max-width: 52vw;
            }

            .left-top {
                margin-left: 0;
            }

            .desktop-reset-hero img {
                width: 40px;
                height: 40px;
            }

            .desktop-reset-hero-text h1 {
                font-size: 1.45rem;
            }

            .right {
                width: min(400px, 100%);
                transform: translateX(-24px);
            }
        }

        @media (min-width: 768px) and (max-height: 860px) {
            .reset-layout {
                padding: 14px 22px;
            }

            .left {
                min-height: calc(100vh - 28px);
            }

            .desktop-reset-hero img {
                width: 42px;
                height: 42px;
            }

            .desktop-reset-hero-text h1 {
                font-size: 1.65rem;
            }

            .reset-panel {
                padding: 24px 22px;
            }

            .title {
                margin-bottom: 18px;
            }

            label {
                margin-bottom: 8px;
                font-size: 0.92rem;
            }

            input {
                height: 52px;
                font-size: 1rem;
            }

            .btn {
                height: 52px;
            }
        }

        @media (max-width: 991.98px) {
            body {
                background-attachment: scroll;
                overflow-x: hidden;
                overflow-y: auto;
            }

            .reset-layout {
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                height: auto;
                padding: 24px 18px;
                overflow: visible;
            }

            .left {
                min-height: clamp(220px, 34vh, 300px);
                max-width: 100%;
                width: 100%;
                padding: 0;
            }

            .right {
                width: 100%;
                max-width: 430px;
                margin: 0 auto 32px;
                transform: none;
            }

            .form-container {
                width: 100%;
            }
        }

        @media (max-width: 767.98px) {
            body {
                overflow-y: auto;
                background-color: #f5f7fb;
            }

            .reset-layout {
                display: block;
                height: auto;
                min-height: 100vh;
                width: 100%;
                max-width: none;
                padding: 0;
                background: none;
                overflow: visible;
            }

            .left {
                width: 100%;
                max-width: none;
                height: auto;
                min-height: 92px;
                padding: 12px 18px;
                background: #143D66;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .left-content {
                display: block;
                min-height: auto;
            }

            .desktop-reset-hero {
                display: none !important;
            }

            .mobile-reset-hero {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                width: 100%;
                max-width: 330px;
            }

            .mobile-reset-hero-logo {
                width: 42px;
                height: auto;
                flex-shrink: 0;
            }

            .mobile-reset-hero-text {
                text-align: left;
                color: #fff;
            }

            .mobile-reset-hero-text p {
                margin: 0 0 3px;
                font-size: 8.5px;
                font-weight: 500;
                line-height: 1.25;
                opacity: 0.9;
            }

            .mobile-reset-hero-text p span {
                display: block;
            }

            .mobile-reset-hero-text h3 {
                margin: 0;
                font-size: 14px;
                font-weight: 700;
                line-height: 1.1;
            }

            .right {
                width: 100%;
                max-width: none;
                padding: 0;
                align-items: center;
                justify-content: center;
                min-height: calc(100vh - 92px);
                transform: none;
            }

            .form-container {
                max-width: 380px;
                padding: 0 18px 24px;
                margin: 0 auto;
                width: 100%;
            }

            .reset-panel {
                background-color: #ffffff;
                border: 1px solid #d9e2ef;
                border-radius: 10px;
                padding: 24px 16px 22px;
                box-shadow: 0 14px 34px rgba(20, 61, 102, 0.1);
                width: 100%;
                overflow: hidden;
                backdrop-filter: none;
            }

            .title {
                font-size: 16px;
                margin-bottom: 8px;
                text-align: center;
                color: #0D4065;
            }

            .subtitle {
                display: block;
                color: #555;
                font-size: 11px;
                margin-bottom: 18px;
                text-align: center;
                line-height: 1.45;
            }

            label {
                font-size: 11px;
                margin-bottom: 6px;
                color: #0D4065;
            }

            input {
                height: 42px;
                font-size: 11px;
                padding: 10px 14px;
                margin-bottom: 16px;
                width: 100%;
                max-width: 100%;
                border-radius: 12px;
            }

            .alert {
                font-size: 12px;
                margin-bottom: 14px;
            }

            .mb-3 {
                margin-bottom: 14px !important;
            }

            .text-danger {
                font-size: 11px;
            }

            .btn {
                height: 42px;
                padding: 0 12px;
                font-size: 13px;
                margin-top: 6px;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="reset-layout">
        <div class="left">
            <div class="left-content">
                <div class="left-top">
                    <div class="desktop-reset-hero">
                        <img src="{{ asset('images/Logo2.png') }}" alt="Logo Balai K3 Surabaya">
                        <div class="desktop-reset-hero-text">
                            <p>Kementerian Ketenagakerjaan RI</p>
                            <h1>Balai K3 Surabaya</h1>
                        </div>
                    </div>
                </div>

                <div class="mobile-reset-hero">
                    <img src="{{ asset('images/Logo.png') }}" alt="Logo Balai K3 Surabaya" class="mobile-reset-hero-logo">
                    <div class="mobile-reset-hero-text">
                        <p>
                            <span>Direktorat Jenderal Pembinaan</span>
                            <span>Pengawasan Ketenagakerjaan dan K3</span>
                        </p>
                        <h3>Balai K3 Surabaya</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="right">
            <div class="form-container">
                <div class="reset-panel">
                    <div class="title">Reset Kata Sandi</div>
                    <div class="subtitle">Masukkan kata sandi baru anda untuk menyelesaikan proses reset</div>

                    @if($errors->any())
                        <div class="alert error">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" placeholder="Masukkan email" required>

                        <label for="password">Kata Sandi Baru</label>
                        <input id="password" type="password" name="password" placeholder="Masukkan kata sandi baru" required>

                        <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Konfirmasi kata sandi" required>

                        <button type="submit" class="btn">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
