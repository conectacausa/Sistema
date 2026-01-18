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
     * - tenants/... (assume dentro de storage público)
     */
    $toUrl = function ($path) {
        if (!$path) return null;

        $path = trim((string) $path);
        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // storage/app/public/... -> storage/...
        if (Str::startsWith($path, 'storage/app/public/')) {
            $path = Str::replaceFirst('storage/app/public/', 'storage/', $path);
        }

        // public/... -> storage/...
        if (Str::startsWith($path, 'public/')) {
            $path = Str::replaceFirst('public/', 'storage/', $path);
        }

        // Já está no formato público
        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        // Se vier "tenants/..." (ou semelhante), assume dentro do storage público
        if (Str::startsWith($path, ['tenants/', 'tenant/', 'uploads/'])) {
            return asset('storage/' . $path);
        }

        return asset($path);
    };

    /**
     * Adiciona cache-buster quando o path aponta para /public (ex: storage/...)
     * Isso resolve muito caso o browser tenha cacheado uma imagem antiga/404.
     */
    $withVersion = function ($url, $originalPathFromDb = null) {
        if (!$url) return null;

        // Não mexe em URL absoluta externa
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        // Tenta descobrir um path público para calcular filemtime
        $path = $originalPathFromDb ? trim((string) $originalPathFromDb) : null;
        $path = $path ? str_replace('\\', '/', $path) : null;
        $path = $path ? ltrim($path, '/') : null;

        if ($path) {
            // Normaliza para algo dentro de /public
            if (Str::startsWith($path, 'storage/app/public/')) {
                $path = Str::replaceFirst('storage/app/public/', 'storage/', $path);
            }
            if (Str::startsWith($path, 'public/')) {
                $path = Str::replaceFirst('public/', '', $path); // public/foo -> foo (dentro de /public)
            }

            // Se for "storage/..." está em /public/storage/...
            if (Str::startsWith($path, 'storage/')) {
                $full = public_path($path);
                if (is_file($full)) {
                    $v = @filemtime($full) ?: time();
                    return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $v;
                }
            }
        }

        // Fallback: versão por timestamp do config (se existir) ou time()
        $v = ($GLOBALS['__tenant_cfg_updated_at_ts'] ?? null) ?: time();
        return $url . (str_contains($url, '?') ? '&' : '?') . 'v=' . $v;
    };

    // Para o fallback do versionamento
    $GLOBALS['__tenant_cfg_updated_at_ts'] = $config?->updated_at?->timestamp ?? time();

    /*
    |--------------------------------------------------------------------------
    | Fallbacks do template
    |--------------------------------------------------------------------------
    */

    // Quadradas (ajuste se o arquivo dark não existir)
    $fallbackSquareLight = asset('assets/images/logo-letter.png');
    $fallbackSquareDark  = asset('assets/images/logo-letter-dark.png');

    // Horizontais (originais do template)
    $fallbackHorizLight  = asset('assets/images/logo-dark-text.png');
    $fallbackHorizDark   = asset('assets/images/logo-light-text.png');

    /*
    |--------------------------------------------------------------------------
    | Logos vindas do banco (com versionamento)
    |--------------------------------------------------------------------------
    */

    $dbSquareLight = $config->logo_quadrado_light ?? null; // ex: storage/tenants/1/logo_quadrada_azul.png
    $dbSquareDark  = $config->logo_quadrado_dark  ?? null; // ex: storage/tenants/1/logo_quadrada_branca.png

    $dbHorizLight  = $config->logo_horizontal_light ?? null;
    $dbHorizDark   = $config->logo_horizontal_dark  ?? null;

    $logoSquareLight = $withVersion($toUrl($dbSquareLight), $dbSquareLight) ?: $fallbackSquareLight;
    $logoSquareDark  = $withVersion($toUrl($dbSquareDark),  $dbSquareDark)  ?: $fallbackSquareDark;

    $logoHorizLight  = $withVersion($toUrl($dbHorizLight),  $dbHorizLight)  ?: $fallbackHorizLight;
    $logoHorizDark   = $withVersion($toUrl($dbHorizDark),   $dbHorizDark)   ?: $fallbackHorizDark;

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

    $avatarDefault = ($sexo === 'F')
        ? asset('assets/images/avatar/avatar-2.png')
        : asset('assets/images/avatar/avatar-15.png');

    $avatarFinal = $avatarUser ?: $avatarDefault;
@endphp

<header class="main-header">
    <div class="d-flex align-items-center logo-box justify-content-start">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="logo">
            <!-- Logo quadrada -->
            <div class="logo-mini w-30">
                <span class="light-logo">
                    <img
                        src="{{ $logoSquareLight }}"
                        alt="logo"
                        onerror="this.onerror=null;this.src='{{ $fallbackSquareLight }}';"
                    >
                </span>
                <span class="dark-logo">
                    <img
                        src="{{ $logoSquareDark }}"
                        alt="logo"
                        onerror="this.onerror=null;this.src='{{ $fallbackSquareDark }}';"
                    >
                </span>
            </div>

            <!-- Logo horizontal -->
            <div class="logo-lg">
                <span class="light-logo">
                    <img
                        src="{{ $logoHorizLight }}"
                        alt="logo"
                        onerror="this.onerror=null;this.src='{{ $fallbackHorizLight }}';"
                    >
                </span>
                <span class="dark-logo">
                    <img
                        src="{{ $logoHorizDark }}"
                        alt="logo"
                        onerror="this.onerror=null;this.src='{{ $fallbackHorizDark }}';"
                    >
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
