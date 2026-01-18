@php
    use Illuminate\Support\Str;

    $config = app()->bound('tenant.config') ? app('tenant.config') : null;
    $user   = auth()->user();
    $sub    = request()->route('sub') ?? session('tenant_subdominio');

    $toUrl = function ($path) {
        if (!$path) return null;

        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        if (Str::startsWith($path, 'public/')) {
            return asset(Str::replaceFirst('public/', 'storage/', $path));
        }

        return asset('storage/' . $path);
    };

    // Logos do banco
    $logoQuadradoLight = $toUrl($config->logo_quadrado_light ?? null); // AZUL
    $logoQuadradoDark  = $toUrl($config->logo_quadrado_dark  ?? null); // BRANCA

    $logoHorizLight = $toUrl($config->logo_horizontal_light ?? null);
    $logoHorizDark  = $toUrl($config->logo_horizontal_dark  ?? null);

    // Fallbacks originais do template
    $fallbackSquare = asset('assets/images/logo-letter.png');
    $fallbackHorizL = asset('assets/images/logo-dark-text.png');
    $fallbackHorizD = asset('assets/images/logo-light-text.png');

    // Avatar
    $avatarUser = $toUrl($user->foto ?? null);
    $sexo = strtoupper($user->colaborador->sexo ?? 'NI');

    $avatarDefault = $sexo === 'F'
        ? asset('assets/images/avatar/avatar-2.png')
        : asset('assets/images/avatar/avatar-15.png');

    $avatarFinal = $avatarUser ?: $avatarDefault;
@endphp

<header class="main-header">
	<div class="d-flex align-items-center logo-box justify-content-start">	
		<!-- Logo -->
		<a href="{{ url('/') }}" class="logo">

            <!-- LOGO MINI (QUADRADA) -->
            <!-- O TEMPLATE SEMPRE MOSTRA .dark-logo -->
            <div class="logo-mini w-30">
                <span class="light-logo">
                    <img src="{{ $logoQuadradoDark ?: $fallbackSquare }}" alt="logo">
                </span>
                <span class="dark-logo">
                    <img src="{{ $logoQuadradoLight ?: $fallbackSquare }}" alt="logo">
                </span>
            </div>

            <!-- LOGO GRANDE (FUNCIONA NORMAL) -->
            <div class="logo-lg">
                <span class="light-logo">
                    <img src="{{ $logoHorizLight ?: $fallbackHorizL }}" alt="logo">
                </span>
                <span class="dark-logo">
                    <img src="{{ $logoHorizDark ?: $fallbackHorizD }}" alt="logo">
                </span>
            </div>

		</a>	
	</div>  

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top">
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
                    <a href="#" data-provide="fullscreen"
                       class="waves-effect waves-light nav-link btn-outline no-border full-screen btn-warning-light">
                        <i data-feather="maximize"></i>
                    </a>
                </li>

                <!-- User Account-->
                <li class="dropdown user user-menu">
                    <a href="#" class="waves-effect waves-light dropdown-toggle no-border p-5"
                       data-bs-toggle="dropdown">
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
