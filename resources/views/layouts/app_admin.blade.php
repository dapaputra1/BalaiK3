<!-- resources/views/layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $role = auth()->user()?->role;
        $roleLabels = [
            'superadmin' => 'Superadmin',
            'admin' => 'Administrator',
            'ma' => 'Manajemen Administrasi',
            'penyelia' => 'Penyelia',
            'pcu' => 'PCU',
            'analis' => 'Analis',
            'mp' => 'MP',
            'mt' => 'MT',
            'user' => 'User',
        ];
        $roleTitle = $role ? ($roleLabels[$role] ?? ucfirst($role)) : 'Administrator';
        $defaultTitle = $roleTitle . ' - Pelayanan K3';
    @endphp
    <title>@yield('title', $defaultTitle)</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Logo2.png') }}">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.bootstrap5.min.css">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Global Style -->
    <style>
        .swal2-container {
            z-index: 200000 !important;
        }
        :root {
            --brand-blue-dark: #15406A;
            --admin-topbar-height: 74px;
        }

        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            background:#f5f7fb;
            font-family:'Poppins', sans-serif;
            font-size:14px;
        }

        .form-control:focus,
        .input-custom:focus {
            border-color: var(--brand-blue-dark);
            box-shadow: 0 0 0 0.2rem rgba(21, 64, 106, 0.2);
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

        .workflow-loading-btn {
            position: relative;
            transition: transform 0.18s ease, opacity 0.18s ease, box-shadow 0.18s ease;
        }

        .workflow-loading-btn[disabled],
        .workflow-loading-btn.is-loading {
            cursor: wait;
            opacity: 0.9;
        }

        .workflow-loading-btn.is-loading {
            transform: translateY(0);
            box-shadow: 0 10px 24px rgba(21, 64, 106, 0.14);
        }

        .workflow-loading-content {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
        }

        .workflow-loading-spinner {
            width: 1rem;
            height: 1rem;
            display: inline-block;
            border-radius: 999px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            animation: workflow-btn-spin 0.8s linear infinite;
            flex: 0 0 auto;
        }

        @keyframes workflow-btn-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }
        #toggleSidebar {
            margin-left: 0 !important;
            flex: 0 0 auto;
        }
        .topbar-page-text {
            display: none;
            min-width: 0;
            line-height: 1.15;
        }
        .topbar-page-text.show {
            display: block;
        }
        .topbar-page-title {
            font-size: 15px;
            font-weight: 600;
            color: #0f3b63;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: min(52vw, 720px);
        }
        .topbar-page-subtitle {
            margin-top: 2px;
            font-size: 12px;
            color: #516274;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: min(52vw, 720px);
        }
        .topbar-page-subtitle a {
            color: #0f3b63;
            text-decoration: none;
            font-weight: 500;
        }
        .topbar-page-subtitle a:hover {
            color: #15406A;
            text-decoration: none;
        }
        .topbar-actions {
            flex: 0 0 auto;
        }

        #main {
            max-width: 100%;
            overflow-x: hidden;
            position: relative;
        }

        #main > main {
            max-width: 100%;
            overflow-x: hidden;
            padding-top: calc(var(--admin-topbar-height) + 1.5rem) !important;
        }

        .topbar .dropdown {
            position: relative;
        }

        .topbar .dropdown-menu {
            z-index: 2000;
            min-width: 220px;
            margin-top: 0.6rem;
            border: 1px solid #dfe7f0;
            box-shadow: 0 14px 32px rgba(15, 59, 99, 0.14);
        }

        body.modal-open {
            overflow: hidden;
            padding-right: 0 !important;
        }

        body.modal-open .topbar,
        body.modal-open #main,
        body.modal-open #main > main {
            padding-right: 0 !important;
        }

        .modal-backdrop {
            z-index: 1990;
        }

        .modal {
            z-index: 2000;
        }

        @media (max-width: 767.98px) {
            .topbar-page-title {
                font-size: 15px;
                max-width: 48vw;
            }
            .topbar-page-subtitle {
                font-size: 12px;
                max-width: 48vw;
            }
        }
    </style>

    {{-- STACK CSS (DataTables, page-specific styles) --}}
    @stack('styles')
</head>

<body class="@yield('body_class')">

@include('partials.page_loader')

@include('partials.navbar_admin')

    <div class="main" id="main">
        <div class="topbar">
            <div class="topbar-left">
                <i class="bi bi-list" id="toggleSidebar"></i>
                <div class="topbar-page-text" id="topbarPageText">
                    <div class="topbar-page-title" id="topbarPageTitle"></div>
                    <div class="topbar-page-subtitle" id="topbarPageSubtitle"></div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 topbar-actions">
                <i class="bi bi-bell"></i>
                @auth
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-dark d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person fs-4"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-item-text fw-semibold">{{ Auth::user()->name }}</li>
                        <li class="dropdown-item-text text-muted small text-uppercase">Role: {{ $roleLabels[Auth::user()->role] ?? ucfirst(Auth::user()->role) }}</li>
                        <li><hr class="dropdown-divider"></li>
                        @if(in_array(Auth::user()->role, ['superadmin', 'admin', 'ma', 'penyelia', 'pcu', 'analis', 'mp', 'mt', 'qc'], true))
                        <li>
                            <a href="{{ route('petugas.profile.edit') }}" class="dropdown-item d-flex align-items-center gap-2">
                                <i class="bi bi-person-circle"></i> Profil Saya
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @endif
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endauth
            </div>
        </div>

        <main class="p-4">
            @yield('content_admin')
        </main>
    </div>

<!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- STACK JS (DataTables, page-specific scripts) --}}
@stack('scripts')

    
    <script>
        const toggleBtn = document.getElementById('toggleSidebar');
        if (toggleBtn) {
            toggleBtn.onclick = function () {
                const sidebar = document.getElementById('sidebar');
                const main = document.getElementById('main');
                sidebar?.classList.toggle('hidden');
                main?.classList.toggle('expanded');
            }
        }

        (() => {
            const marker = document.querySelector('[data-workflow-topbar]');
            const wrap = document.getElementById('topbarPageText');
            const titleEl = document.getElementById('topbarPageTitle');
            const subtitleEl = document.getElementById('topbarPageSubtitle');
            if (!marker || !wrap || !titleEl || !subtitleEl) return;

            const title = (marker.getAttribute('data-title') || '').trim();
            const subtitle = (marker.getAttribute('data-subtitle') || '').trim();
            const backUrl = (marker.getAttribute('data-back-url') || '').trim();
            const backLabel = (marker.getAttribute('data-back-label') || 'Kembali').trim();
            if (!title) return;

            titleEl.textContent = title;
            subtitleEl.innerHTML = '';
            if (backUrl) {
                const backLink = document.createElement('a');
                backLink.href = backUrl;
                backLink.textContent = backLabel || 'Kembali';
                subtitleEl.appendChild(backLink);
            }
            if (subtitle) {
                if (backUrl) {
                    subtitleEl.appendChild(document.createTextNode(' < '));
                }
                subtitleEl.appendChild(document.createTextNode(subtitle));
            }
            subtitleEl.style.display = (backUrl || subtitle) ? 'block' : 'none';
            wrap.classList.add('show');
        })();

        (() => {
            const ensureButtonContent = (button) => {
                if (!button) return null;
                let content = button.querySelector('.workflow-loading-content');
                if (content) return content;

                content = document.createElement('span');
                content.className = 'workflow-loading-content';
                while (button.firstChild) {
                    content.appendChild(button.firstChild);
                }
                button.appendChild(content);
                return content;
            };

            const getDefaultLabel = (button) => {
                const explicit = (button?.getAttribute('data-loading-text') || '').trim();
                if (explicit) return explicit;
                const current = (button?.dataset?.originalLabel || button?.textContent || '').trim();
                return current || 'Memproses...';
            };

            const setButtonLoading = (button, isLoading, options = {}) => {
                if (!button) return;
                const content = ensureButtonContent(button);
                button.classList.add('workflow-loading-btn');

                if (isLoading) {
                    if (!button.dataset.originalHtml) {
                        button.dataset.originalHtml = content?.innerHTML || '';
                    }
                    if (!button.dataset.originalLabel) {
                        button.dataset.originalLabel = (content?.textContent || button.textContent || '').trim();
                    }
                    button.disabled = true;
                    button.dataset.loadingState = '1';
                    button.classList.add('is-loading');
                    button.setAttribute('aria-busy', 'true');
                    if (content) {
                        content.innerHTML = '';
                        const spinner = document.createElement('span');
                        spinner.className = 'workflow-loading-spinner';
                        spinner.setAttribute('aria-hidden', 'true');
                        const label = document.createElement('span');
                        label.textContent = options.label || getDefaultLabel(button);
                        content.append(spinner, label);
                    }
                    return;
                }

                if (content) {
                    if (button.dataset.originalHtml) {
                        content.innerHTML = button.dataset.originalHtml;
                    } else {
                        content.textContent = button.dataset.originalLabel || content.textContent || '';
                    }
                }
                button.dataset.loadingState = '0';
                button.classList.remove('is-loading');
                button.removeAttribute('aria-busy');
                if (options.keepDisabled !== true) {
                    button.disabled = false;
                }
            };

            const releaseGroup = (buttons) => {
                (Array.isArray(buttons) ? buttons : Array.from(buttons || [])).forEach((button) => {
                    setButtonLoading(button, false);
                });
            };

            const lockGroup = (buttons, activeButton = null, label = '') => {
                (Array.isArray(buttons) ? buttons : Array.from(buttons || [])).forEach((button) => {
                    if (button === activeButton) {
                        setButtonLoading(button, true, { label });
                        return;
                    }
                    button.classList.add('workflow-loading-btn');
                    button.disabled = true;
                });
            };

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (!form.matches('[data-workflow-submit-form]')) return;

                const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
                if (submitter) {
                    setButtonLoading(submitter, true);
                }
            }, true);

            window.WorkflowLoading = {
                setButtonLoading,
                releaseButton: (button, keepDisabled = false) => setButtonLoading(button, false, { keepDisabled }),
                lockGroup,
                releaseGroup,
            };
        })();
    </script>
    

</body>
</html>
