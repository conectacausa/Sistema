@php
    use Illuminate\Support\Str;

    // Tenant e configuração vindos do middleware (ou null se não existir)
    $tenant = app()->bound('tenant') ? app('tenant') : null;
    $config = app()->bound('tenant.config') ? app('tenant.config') : null;

    $user = auth()->user();

    /**
     * Normaliza caminhos vindos do banco:
     * - Se já for URL http(s), usa direto
     * - Se começar com "/", usa asset sem duplicar
     * - Caso contrário, usa asset($path)
     */
    $urlFromPath = function ($path) {
        if (!$path) return null;

        $path = trim((string) $path);

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Se vier "storage/..." ou "uploads/..." etc, asset resolve.
        if (Str::startsWith($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return asset($path);
    };

    // Logos do banco (light/dark)
    $logoSquareLight = $urlFromPath($config->logo_quadrado_light ?? null);
    $logoSquareDark  = $urlFromPath($config->logo_quadrado_dark ?? null);

    $logoHorizLight  = $urlFromPath($config->logo_horizontal_light ?? null);
    $logoHorizDark   = $urlFromPath($config->logo_horizontal_dark ?? null);

    // Fallbacks (do template)
    $fallbackSquare = asset('assets/images/logo-letter.png');
    $fallbackHorizLight = asset('assets/images/logo-dark-text.png');  // light theme
    $fallbackHorizDark  = asset('assets/images/logo-light-text.png'); // dark theme

    // Avatar:
    // 1) se usuário tem foto -> usa
    // 2) senão, usa sexo do colaborador (M/F/NI) para escolher avatar padrão
    $avatarUser = $urlFromPath($user->foto ?? null);

    $sexo = 'NI';
    if ($user && $user->colaborador_id) {
        try {
            $sexo = optional($user->colaborador)->sexo ?? 'NI';
        } catch (\Throwable $e) {
            // se não tiver relacionamento definido ainda, ignora
            $sexo = 'NI';
        }
    }

    $sexo = strtoupper((string) $sexo);
    $avatarDefault = asset('assets/images/avatar/avatar-15.png'); // masculino padrão

    if ($sexo === 'F') {
        $avatarDefault = asset('assets/images/avatar/avatar-2.png');
    } elseif ($sexo === 'NI' || $sexo === '' || $sexo === null) {
        $avatarDefault = asset('assets/images/avatar/avatar-15.png');
    }

    $avatarFinal = $avatarUser ?: $avatarDefault;
@endphp

<header class="main-header">
    <div class="d-flex align-items-center logo-box justify-content-start">
        <a href="{{ url('/') }}" class="logo">
            {{-- Logo mini (quadrada) --}}
            <div class="logo-mini-w30">
                <span class="light-logo">
                    <img src="{{ $logoSquareLight ?: $fallbackSquare }}" alt="logo">
                </span>
                <span class="dark-logo">
                    <img src="{{ $logoSquareDark ?: $fallbackSquare }}" alt="logo">
                </span>
            </div>

            {{-- Logo grande (horizontal) --}}
            <div class="logo-lg">
                <span class="light-logo">
                    <img src="{{ $logoHorizLight ?: $fallbackHorizLight }}" alt="logo">
                </span>
                <span class="dark-logo">
                    <img src="{{ $logoHorizDark ?: $fallbackHorizDark }}" alt="logo">
                </span>
            </div>
        </a>
    </div>

    <nav class="navbar navbar-static-top">
        <div class="app-menu">
            <ul class="header-megamenu nav">
                <li class="btn-group nav-item">
                    <a href="#"
                       class="waves-effect waves-light nav-link push-btn btn-outline no-border btn-primary-light"
                       data-toggle="push-menu" role="button">
                        <i data-feather="align-left"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="navbar-custom-menu r-side">
            <ul class="nav navbar-nav">

                {{-- Toggle Dark/Light --}}
                <li class="dropdown notifications-menu btn-group">
                    <label class="switch">
                        <a class="waves-effect waves-light btn-outline no-border nav-link svg-bt-icon btn-info-light">
                            <input type="checkbox" data-mainsidebarskin="toggle" id="toggle_left_sidebar_skin">
                            <span class="switch-on"><i data-feather="moon"></i></span>
                            <span class="switch-off"><i data-feather="sun"></i></span>
                        </a>
                    </label>
                </li>

                {{-- Fullscreen --}}
                <li class="btn-group nav-item d-lg-inline-flex d-none">
                    <a href="#"
                       data-provide="fullscreen"
                       class="waves-effect waves-light nav-link btn-outline no-border full-screen btn-warning-light"
                       title="Full Screen">
                        <i data-feather="maximize"></i>
                    </a>
                </li>

                {{-- Usuário --}}
                <li class="dropdown user user-menu">
                    <a href="#"
                       class="waves-effect waves-light dropdown-toggle no-border p-5"
                       data-bs-toggle="dropdown"
                       title="User">
                        <img class="avatar avatar-pill" src="{{ $avatarFinal }}" alt="avatar">
                    </a>

                    <ul class="dropdown-menu animated flipInX">
                        <li class="user-body">
                            <div class="d-flex align-items-center gap-10 px-3 py-2">
                                <img class="avatar avatar-lg avatar-pill" src="{{ $avatarFinal }}" alt="avatar">
                                <div>
                                    <div class="fw-600">
                                        {{ $user->nome_completo ?? 'Usuário' }}
                                    </div>
                                    <div class="text-fade fs-12">
                                        {{ $tenant->nome_fantasia ?? 'Empresa' }}
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="#">
                                <i class="ti-user text-muted me-2"></i> Perfil
                            </a>

                            <div class="dropdown-divider"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit">
                                    <i class="ti-lock text-muted me-2"></i> Sair
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</header>
