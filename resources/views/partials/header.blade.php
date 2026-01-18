@php
    use Illuminate\Support\Str;

    // Tenant/config vindos do middleware
    $tenant = app()->bound('tenant') ? app('tenant') : null;
    $config = app()->bound('tenant.config') ? app('tenant.config') : null;

    $user = auth()->user();

    // Subdomínio atual (rota ou sessão)
    $sub = request()->route('sub') ?? session('tenant_subdominio');

    // Converte caminho do banco em URL pública
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

        // Ex: tenants/1/logo.png -> storage/tenants/1/logo.png
        if (Str::startsWith($path, ['tenants/', 'tenant/', 'uploads/'])) {
            return asset('storage/' . $path);
        }

        return asset($path);
    };

    /*
    |--------------------------------------------------------------------------
    | Logos vindas do banco (seus exemplos)
    |--------------------------------------------------------------------------
    */
    $dbSquareLight = $config->logo_quadrado_light ?? null; // storage/tenants/1/logo_quadrada_azul.png
    $dbSquareDark  = $config->logo_quadrado_dark  ?? null; // storage/tenants/1/logo_quadrada_branca.png

    $dbHorizLight  = $config->logo_horizontal_light ?? null;
    $dbHorizDark   = $config->logo_horizontal_dark  ?? null;

    // Fallbacks
    $fallbackSquareLight = asset('assets/images/logo-letter.png');
    $fallbackSquareDark  = asset('assets/images/logo-letter.png'); // no original era o mesmo (ok)

    $fallbackHorizLight  = asset('assets/images/logo-dark-text.png');
    $fallbackHorizDark   = asset('assets/images/logo-light-text.png');

    // URLs finais
    $logoSquareLight = $toUrl($dbSquareLight) ?: $fallbackSquareLight;
    $logoSquareDark  = $toUrl($dbSquareDark)  ?: $fallbackSquareDark;

    $logoHorizLight  = $toUrl($dbHorizLight)  ?: $fallbackHorizLight;
    $logoHorizDark   = $toUrl($dbHorizDark)   ?: $fallbackHorizDark;

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
            <!-- logo mini (quadrada) - NÃO depende mais do CSS do template -->
            <div class="logo-mini w-30">
                <img
                    id="tenantLogoMini"
                    src="{{ $logoSquareLight }}"
                    data-logo-light="{{ $logoSquareLight }}"
                    data-logo-dark="{{ $logoSquareDark }}"
                    alt="logo"
                    style="display:inline-block;"
                    onerror="this.onerror=null;this.src='{{ $fallbackSquareLight }}';"
                >
            </div>

            <!-- logo horizontal (mantém padrão original do template, pois já funciona) -->
            <div class="logo-lg">
                <span class="light-logo">
                    <img src="{{ $logoHorizLight }}" alt="logo"
                         onerror="this.onerror=null;this.src='{{ $fallbackHorizLight }}';">
                </span>
                <span class="dark-logo">
                    <img src="{{ $logoHorizDark }}" alt="logo"
                         onerror="this.onerror=null;this.src='{{ $fallbackHorizDark }}';">
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
                    <a href="#" class="waves-effect waves-light nav-link push-btn btn-outline no-border btn-primary-light"
                       data-toggle="push-menu" role="button">
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
                    <a href="#" data-provide="fullscreen" class="waves-effect waves-light nav-link btn-outline no-border full-screen btn-warning-light" title="Full Screen">
                        <i data-feather="maximize"></i>
                    </a>
                </li>

                <!-- User Account-->
                <li class="dropdown user user-menu">
                    <a href="#" class="waves-effect waves-light dropdown-toggle no-border p-5" data-bs-toggle="dropdown" title="User">
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

<script>
(function () {
    function isDarkMode() {
        const body = document.body;
        const cls = body.className || '';

        // muitos templates alternam essas classes
        const byClass =
            cls.includes('dark') ||
            cls.includes('dark-skin') ||
            cls.includes('theme-dark') ||
            cls.includes('skin-dark');

        // fallback: estado do checkbox (muitos templates usam ele)
        const toggle = document.getElementById('toggle_left_sidebar_skin');
        const byToggle = toggle ? !!toggle.checked : false;

        return byClass || byToggle;
    }

    function applyMiniLogo() {
        const img = document.getElementById('tenantLogoMini');
        if (!img) return;

        const light = img.getAttribute('data-logo-light');
        const dark  = img.getAttribute('data-logo-dark');

        img.src = isDarkMode() ? (dark || light) : (light || dark);
    }

    // aplica ao carregar
    document.addEventListener('DOMContentLoaded', function () {
        applyMiniLogo();

        // reaplica quando o usuário troca o tema
        const toggle = document.getElementById('toggle_left_sidebar_skin');
        if (toggle) {
            toggle.addEventListener('change', function () {
                // alguns templates aplicam classe com pequeno delay
                setTimeout(applyMiniLogo, 50);
                setTimeout(applyMiniLogo, 250);
            });
        }

        // reaplica em qualquer clique (cobre templates que trocam tema via JS sem change)
        document.addEventListener('click', function (e) {
            const t = e.target;
            if (!t) return;
            if (t.id === 'toggle_left_sidebar_skin' || (t.closest && t.closest('#toggle_left_sidebar_skin'))) {
                setTimeout(applyMiniLogo, 50);
                setTimeout(applyMiniLogo, 250);
            }
        });
    });

    // reaplica também se classes do body mudarem depois (fallback simples)
    setInterval(applyMiniLogo, 1500);
})();
</script>
