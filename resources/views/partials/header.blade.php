@php
    use Illuminate\Support\Str;

    // Tenant/config vindos do middleware
    $tenant = app()->bound('tenant') ? app('tenant') : null;
    $config = app()->bound('tenant.config') ? app('tenant.config') : null;

    $user = auth()->user();

    // Subdomínio atual (rota ou sessão)
    $sub = request()->route('sub') ?? session('tenant_subdominio');

    /**
     * Converte caminho do banco em URL pública.
     * Aceita:
     * - URL absoluta (http/https)
     * - storage/app/public/...
     * - public/...
     * - storage/...
     * - tenants/... (ou qualquer caminho relativo dentro do storage/public)
     */
    $toUrl = function ($path) {
        if (!$path) return null;

        $path = trim((string) $path);

        // Normaliza separadores (Windows -> Linux)
        $path = str_replace('\\', '/', $path);

        // URL absoluta
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // Remove barras iniciais
        $path = ltrim($path, '/');

        // 1) storage/app/public/... -> storage/...
        if (Str::startsWith($path, 'storage/app/public/')) {
            $path = Str::replaceFirst('storage/app/public/', 'storage/', $path);
        }

        // 2) public/... -> storage/...
        // (muito comum quando se usa Storage::putFile e salva o path "public/..")
        if (Str::startsWith($path, 'public/')) {
            $path = Str::replaceFirst('public/', 'storage/', $path);
        }

        // 3) Se já começa com storage/, ok
        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        // 4) Se vier algo tipo "tenants/1/logo.png" ou "tenants/..."
        // assume que está dentro do storage público
        if (Str::startsWith($path, ['tenants/', 'tenant/', 'uploads/'])) {
            return asset('storage/' . $path);
        }

        // 5) Qualquer outro caminho relativo: tenta como está
        return asset($path);
    };

    /*
    |--------------------------------------------------------------------------
    | Logos da empresa (Light / Dark) com fallback
    |--------------------------------------------------------------------------
    */

    // Fallbacks quadrados (ajuste esses arquivos conforme existirem no seu template)
    $fallbackSquareLight = asset('assets/images/logo-letter.png');
    $fallbackSquareDark  = asset('assets/images/logo-letter-dark.png');

    // Fallbacks horizontais (originais do template)
    $fallbackHorizLight  = asset('assets/images/logo-dark-text.png');
    $fallbackHorizDark   = asset('assets/images/logo-light-text.png');

    // Logos vindas do banco (normalizadas)
    $logoSquareLight = $toUrl($config->logo_quadrado_light ?? null) ?: $fallbackSquareLight;
    $logoSquareDark  = $toUrl($config->logo_quadrado_dark  ?? null) ?: $fallbackSquareDark;

    $logoHorizLight  = $toUrl($config->logo_horizontal_light ?? null) ?: $fallbackHorizLight;
    $logoHorizDark   = $toUrl($config->logo_horizontal_dark  ?? null) ?: $fallbackHorizDark;

    /*
    |--------------------------------------------------------------------------
    | Avatar do usuário
    |--------------------------------------------------------------------------
    */

    $avatarUser = $toUrl($user->foto ?? null);

    $sexo = 'NI';
    if ($user && $user->colaborador) {
        $sexo = strtoupper((string) ($user->colaborador->sexo ?? 'NI'));
    }

    if ($sexo === 'F') {
        $avatarDefault = asset('assets/images/avatar/avatar-2.png');
    } else {
        $avatarDefault = asset('assets/images/avatar/avatar-15.png');
    }

    $avatarFinal = $avatarUser ?: $avatarDefault;
@endphp

<header class="main-header">
    <div class="d-flex align-items-center logo-box justify-content-start">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="logo">
            <!-- logo quadrada -->
            <div class="logo-mini w-30">
                <span class="light-logo">
                    <img src="{{ $logoSquareLight }}" alt="logo">
                </span>
                <span class="dark-logo">
                    <img src="{{ $logoSquareDark }}" alt="logo">
                </span>
            </div>

            <!-- logo horizontal -->
            <div class="logo-lg">
                <span class="light-logo">
                    <img src="{{ $logoHorizLight }}" alt="logo">
                </span>
                <span class="dark-logo">
                    <img src="{{ $logoHorizDark }}" alt="logo">
                </span>
            </div>
        </a>
    </div>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <div class="app-menu">
            <ul class="header-megamenu nav">
                <li class="btn-group nav-item">
                    <a href="#"
                       class="waves-effect waves-light nav-link push-btn btn-outline no-border btn-primary-light"
                       data-toggle="push-menu"
                       role="button">
                        <i data-feather="align-left"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="navbar-custom-menu r-side">
            <ul class="nav navbar-nav">

                <!-- Toggle Dark / Light-->
                <li class="dropdown notifications-menu btn-group">
                    <label class="switch">
                        <a class="waves-effect waves-light btn-outline no-border nav-link svg-bt-icon btn-info-light">
                            <input type="checkbox" data-mainsidebarskin="toggle" id="toggle_left_sidebar_skin">
                            <span class="switch-on"><i data-feather="moon"></i></span>
                            <span class="switch-off"><i data-feather="sun"></i></span>
                        </a>
                    </label>
                </li>

                <!-- Full Screen-->
                <li class="btn-group nav-item d-lg-inline-flex d-none">
                    <a href="#"
                       data-provide="fullscreen"
                       class="waves-effect waves-light nav-link btn-outline no-border full-screen btn-warning-light"
                       title="Full Screen">
                        <i data-feather="maximize"></i>
                    </a>
                </li>

                <!-- User Account-->
                <li class="dropdown user user-menu">
                    <a href="#"
                       class="waves-effect waves-light dropdown-toggle no-border p-5"
                       data-bs-toggle="dropdown"
                       title="User">
                        <img class="avatar avatar-pill" src="{{ $avatarFinal }}" alt="">
                    </a>

                    <ul class="dropdown-menu animated flipInX">
                        <li class="user-body">
                            <a class="dropdown-item" href="{{ url('perfil') }}">
                                <i class="ti-user text-muted me-2"></i>Perfil
                            </a>

                            <a class="dropdown-item" href="{{ url('configuracao') }}">
                                <i class="ti-settings text-muted me-2"></i> Configuração
                            </a>

                            <div class="dropdown-divider"></div>

                            <form method="POST" action="{{ route('logout', ['sub' => $sub]) }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
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
