@php
    use Illuminate\Support\Str;

    // Tenant/config vindos do middleware (se existir)
    $tenant = app()->bound('tenant') ? app('tenant') : null;
    $config = app()->bound('tenant.config') ? app('tenant.config') : null;

    $user = auth()->user();

    // Converte um caminho do banco em URL:
    // - se for http/https, usa direto
    // - se vier com "/", remove e usa asset
    // - se vier "uploads/..." ou "storage/...", usa asset
    $toUrl = function ($path) {
        if (!$path) return null;
        $path = trim((string) $path);

        if (Str::startsWith($path, ['http://', 'https://'])) return $path;

        return asset(ltrim($path, '/'));
    };

    // Logos do banco (configuracoes)
    $logoSquareLight = $toUrl($config->logo_quadrado_light ?? null);
    $logoSquareDark  = $toUrl($config->logo_quadrado_dark ?? null);

    $logoHorizLight  = $toUrl($config->logo_horizontal_light ?? null);
    $logoHorizDark   = $toUrl($config->logo_horizontal_dark ?? null);

    // Fallbacks do template (iguais ao seu HTML)
    $fallbackSquare    = asset('assets/images/logo-letter.png');
    $fallbackHorizL    = asset('assets/images/logo-dark-text.png');
    $fallbackHorizD    = asset('assets/images/logo-light-text.png');

    // Avatar: se usuario tem foto usa, senão usa sexo do colaborador
    $avatarUser = $toUrl($user->foto ?? null);

    $sexo = 'NI';
    if ($user && $user->colaborador) {
        $sexo = strtoupper((string) ($user->colaborador->sexo ?? 'NI'));
    }

    $avatarDefault = asset('assets/images/avatar/avatar-15.png'); // masculino padrão
    if ($sexo === 'F') {
        $avatarDefault = asset('assets/images/avatar/avatar-2.png');
    } else {
        // M / NI / vazio -> masculino padrão
        $avatarDefault = asset('assets/images/avatar/avatar-15.png');
    }

    $avatarFinal = $avatarUser ?: $avatarDefault;
@endphp

<header class="main-header">
	<div class="d-flex align-items-center logo-box justify-content-start">
		<!-- Logo -->
		<a href="{{ url('/') }}" class="logo">
		  <!-- logo-->
		  <div class="logo-mini w-30">
			  <span class="light-logo"><img src="{{ $logoSquareLight ?: $fallbackSquare }}" alt="logo"></span>
			  <span class="dark-logo"><img src="{{ $logoSquareDark ?: $fallbackSquare }}" alt="logo"></span>
		  </div>
		  <div class="logo-lg">
			  <span class="light-logo"><img src="{{ $logoHorizLight ?: $fallbackHorizL }}" alt="logo"></span>
			  <span class="dark-logo"><img src="{{ $logoHorizDark ?: $fallbackHorizD }}" alt="logo"></span>
		  </div>
		</a>
	</div>
    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
	  <div class="app-menu">
		<ul class="header-megamenu nav">
			<li class="btn-group nav-item">
				<a href="#" class="waves-effect waves-light nav-link push-btn btn-outline no-border btn-primary-light" data-toggle="push-menu" role="button">
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
				<!-- Colocar a foto do usuário que está logado, se o usuário não tiver foto e for Masculino, usar a foto "assets/images/avatar/avatar-15.png", se for feminino usar a foto "assets/images/avatar/avatar-2.png", se não tiver a informação se Masculino ou Feminino usar a Masculino -->
				<img class="avatar avatar-pill" src="{{ $avatarFinal }}" alt="">
            </a>
            <ul class="dropdown-menu animated flipInX">
              <li class="user-body">
				 <a class="dropdown-item" href="{{ url('perfil') }}"><i class="ti-user text-muted me-2"></i>Perfil</a>
				 <a class="dropdown-item" href="{{ url('/configuracao') }}"><i class="ti-settings text-muted me-2"></i> Configuração</a>
				 <div class="dropdown-divider"></div>

                 <form method="POST" action="{{ route('logout') }}">
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
