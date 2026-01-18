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
    | Logos do banco (seus valores)
    |--------------------------------------------------------------------------
    */
    $dbSquareLight = $config->logo_quadrado_light ?? null; // storage/tenants/1/logo_quadrada_azul.png
    $dbSquareDark  = $config->logo_quadrado_dark  ?? null; // storage/tenants/1/logo_quadrada_branca.png

    $dbHorizLight  = $config->logo_horizontal_light ?? null;
    $dbHorizDark   = $config->logo_horizontal_dark  ?? null;

    // Fallbacks do template
    $fallbackSquare = asset('assets/images/logo-letter.png'); // fallback único como no template original
    $fallbackHorizL = asset('assets/images/logo-dark-text.png');
    $fallbackHorizD = asset('assets/images/logo-light-text.png');

    // URLs finais
    $logoSquareLight = $toUrl($dbSquareLight) ?: $fallbackSquare; // AZUL
    $logoSquareDark  = $toUrl($dbSquareDark)  ?: $fallbackSquare; // BRANCA

    $logoHorizLight  = $toUrl($dbHorizLight)  ?: $fallbackHorizL;
    $logoHorizDark   = $toUrl($dbHorizDark)   ?: $fallbackHorizD;

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
            <!-- logo mini (quadrada) -->
            <div class="logo-mini w-30">
                <img
                    id="tenantLogoMini"
                    src="{{ $logoSquareLight }}"
                    data-logo-light="{{ $logoSquareLight }}"
                    data-logo-dark="{{ $logoSquareDark }}"
                    alt="logo"
                    onerror="this.onerror=null;this.src='{{ $fallbackSquare }}';"
                >
            </div>

            <!-- logo horizontal (mantém o comportamento perfeito do template) -->
            <div class="logo-lg" id="tenantLogoLg">
                <span class="light-logo">
                    <img src="{{ $logoHorizLight }}" alt="logo"
                         onerror="this.onerror=null;this.src='{{ $fallbackHorizL }}';">
                </span>
                <span class="dark-logo">
                    <img src="{{ $logoHorizDark }}" alt="logo"
                         onerror="this.onerror=null;this.src='{{ $fallbackHorizD }}';">
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
    function isElementVisible(el) {
        if (!el) return false;
        // cobre display:none, visibility:hidden e elementos fora do layout
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') return false;
        return el.getClientRects().length > 0;
    }

    function applyMiniLogoFromLg() {
        const mini = document.getElementById('tenantLogoMini');
        const lg = document.getElementById('tenantLogoLg');
        if (!mini || !lg) return;

        const lightSpan = lg.querySelector('.light-logo');
        const darkSpan  = lg.querySelector('.dark-logo');

        // Se o span light do logo-lg está visível, estamos em light mode
        const lightVisible = isElementVisible(lightSpan);
        const darkVisible  = isElementVisible(darkSpan);

        const lightUrl = mini.getAttribute('data-logo-light');
        const darkUrl  = mini.getAttribute('data-logo-dark');

        // Prioridade: segue exatamente o que está visível no logo-lg
        if (lightVisible && !darkVisible) {
            if (mini.src !== lightUrl) mini.src = lightUrl;
            return;
        }
        if (darkVisible && !lightVisible) {
            if (mini.src !== darkUrl) mini.src = darkUrl;
            return;
        }

        // Fallback (se ambos visíveis/ocultos por algum CSS estranho):
        // tenta inferir pelo body class “dark” se existir
        const bodyCls = (document.body.className || '').toLowerCase();
        const seemsDark = bodyCls.includes('dark');
        mini.src = seemsDark ? (darkUrl || lightUrl) : (lightUrl || darkUrl);
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyMiniLogoFromLg();

        // Quando troca o tema, alguns templates atualizam o DOM com delay
        const toggle = document.getElementById('toggle_left_sidebar_skin');
        if (toggle) {
            toggle.addEventListener('change', function () {
                setTimeout(applyMiniLogoFromLg, 10);
                setTimeout(applyMiniLogoFromLg, 100);
                setTimeout(applyMiniLogoFromLg, 300);
            });
        }

        // Observa mudanças de classe/estilo no logo-lg e no body
        const obs = new MutationObserver(function () {
            applyMiniLogoFromLg();
        });

        obs.observe(document.body, { attributes: true, attributeFilter: ['class'] });

        const lg = document.getElementById('tenantLogoLg');
        if (lg) {
            obs.observe(lg, { attributes: true, subtree: true, attributeFilter: ['class', 'style'] });
        }

        // Fallback extra (cobre templates que mexem em CSS via JS sem mutation clara)
        setInterval(applyMiniLogoFromLg, 1200);
    });
})();
</script>
