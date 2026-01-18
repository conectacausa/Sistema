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
    $fallbackSquare = asset('assets/images/logo-letter.png');
    $fallbackHorizL = asset('assets/images/logo-dark-text.png');
    $fallbackHorizD = asset('assets/images/logo-light-text.png');

    // URLs finais (EXATAMENTE como você quer)
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
            <!-- logo mini (quadrada) - estrutura original -->
            <div class="logo-mini w-30" id="tenantLogoMiniWrap">
                <span class="light-logo" id="miniLightSpan">
                    <img
                        id="miniLightImg"
                        src="{{ $logoSquareLight }}"
                        data-src="{{ $logoSquareLight }}"
                        alt="logo"
                        onerror="this.onerror=null;this.src='{{ $fallbackSquare }}';"
                    >
                </span>

                <span class="dark-logo" id="miniDarkSpan">
                    <img
                        id="miniDarkImg"
                        src="{{ $logoSquareDark }}"
                        data-src="{{ $logoSquareDark }}"
                        alt="logo"
                        onerror="this.onerror=null;this.src='{{ $fallbackSquare }}';"
                    >
                </span>
            </div>

            <!-- logo horizontal (como no template; está perfeito) -->
            <div class="logo-lg" id="tenantLogoLg">
                <span class="light-logo" id="lgLightSpan">
                    <img src="{{ $logoHorizLight }}" alt="logo"
                         onerror="this.onerror=null;this.src='{{ $fallbackHorizL }}';">
                </span>
                <span class="dark-logo" id="lgDarkSpan">
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
    function isVisible(el) {
        if (!el) return false;
        const cs = window.getComputedStyle(el);
        if (cs.display === 'none' || cs.visibility === 'hidden' || cs.opacity === '0') return false;
        return el.getClientRects().length > 0;
    }

    function forceDisplay(el, value) {
        if (!el) return;
        // Vence CSS do template mesmo com !important
        el.style.setProperty('display', value, 'important');
        el.style.setProperty('visibility', value === 'none' ? 'hidden' : 'visible', 'important');
        el.style.setProperty('opacity', value === 'none' ? '0' : '1', 'important');
    }

    function applyMiniByLg() {
        const lg = document.getElementById('tenantLogoLg');
        if (!lg) return;

        const lgLight = document.getElementById('lgLightSpan') || lg.querySelector('.light-logo');
        const lgDark  = document.getElementById('lgDarkSpan')  || lg.querySelector('.dark-logo');

        const miniLightSpan = document.getElementById('miniLightSpan');
        const miniDarkSpan  = document.getElementById('miniDarkSpan');

        const miniLightImg = document.getElementById('miniLightImg');
        const miniDarkImg  = document.getElementById('miniDarkImg');

        // Garante que os src estão sempre os corretos (mesmo se algum JS mexer)
        if (miniLightImg && miniLightImg.dataset && miniLightImg.dataset.src) {
            if (miniLightImg.src !== miniLightImg.dataset.src) miniLightImg.src = miniLightImg.dataset.src;
        }
        if (miniDarkImg && miniDarkImg.dataset && miniDarkImg.dataset.src) {
            if (miniDarkImg.src !== miniDarkImg.dataset.src) miniDarkImg.src = miniDarkImg.dataset.src;
        }

        const lightOn = isVisible(lgLight) && !isVisible(lgDark);
        const darkOn  = isVisible(lgDark)  && !isVisible(lgLight);

        if (lightOn) {
            // LIGHT: mostra AZUL (logo_quadrado_light)
            forceDisplay(miniLightSpan, 'inline-block');
            forceDisplay(miniDarkSpan, 'none');
            return;
        }

        if (darkOn) {
            // DARK: mostra BRANCA (logo_quadrado_dark)
            forceDisplay(miniLightSpan, 'none');
            forceDisplay(miniDarkSpan, 'inline-block');
            return;
        }

        // Fallback: se ambos visíveis/ocultos por alguma transição,
        // tenta inferir por classe dark no body
        const cls = (document.body.className || '').toLowerCase();
        const seemsDark = cls.includes('dark');

        forceDisplay(miniLightSpan, seemsDark ? 'none' : 'inline-block');
        forceDisplay(miniDarkSpan,  seemsDark ? 'inline-block' : 'none');
    }

    function bind() {
        applyMiniByLg();

        const toggle = document.getElementById('toggle_left_sidebar_skin');
        if (toggle) {
            toggle.addEventListener('change', function () {
                setTimeout(applyMiniByLg, 10);
                setTimeout(applyMiniByLg, 100);
                setTimeout(applyMiniByLg, 300);
            });
        }

        // Observa mudanças no body e no logo-lg
        const obs = new MutationObserver(function () {
            applyMiniByLg();
        });

        obs.observe(document.body, { attributes: true, attributeFilter: ['class', 'style'] });

        const lg = document.getElementById('tenantLogoLg');
        if (lg) {
            obs.observe(lg, { attributes: true, subtree: true, attributeFilter: ['class', 'style'] });
        }

        // Fallback: alguns templates reaplicam CSS/JS depois
        setInterval(applyMiniByLg, 800);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
</script>
