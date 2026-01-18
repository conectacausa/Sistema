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

        // Garante formato público
        if (Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        if (Str::startsWith($path, 'storage/app/public/')) {
            $path = Str::replaceFirst('storage/app/public/', 'storage/', $path);
            return asset($path);
        }

        if (Str::startsWith($path, 'public/')) {
            $path = Str::replaceFirst('public/', 'storage/', $path);
            return asset($path);
        }

        // Ex: tenants/1/logo.png -> storage/tenants/1/logo.png
        if (Str::startsWith($path, ['tenants/', 'tenant/', 'uploads/'])) {
            return asset('storage/' . $path);
        }

        return asset($path);
    };

    // Logos vindas do banco (CONFIRMADAS)
    $logoSquareLight = $toUrl($config->logo_quadrado_light ?? null); // azul
    $logoSquareDark  = $toUrl($config->logo_quadrado_dark ?? null);  // branca

    $logoHorizLight  = $toUrl($config->logo_horizontal_light ?? null);
    $logoHorizDark   = $toUrl($config->logo_horizontal_dark ?? null);

    // Fallbacks do template (originais)
    $fallbackSquare = asset('assets/images/logo-letter.png');
    $fallbackHorizL = asset('assets/images/logo-dark-text.png');
    $fallbackHorizD = asset('assets/images/logo-light-text.png');

    // Avatar do usuário
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

    // URLs finais garantidas
    $squareLightFinal = $logoSquareLight ?: $fallbackSquare; // azul
    $squareDarkFinal  = $logoSquareDark  ?: $fallbackSquare; // branca
    $horizLightFinal  = $logoHorizLight  ?: $fallbackHorizL;
    $horizDarkFinal   = $logoHorizDark   ?: $fallbackHorizD;
@endphp

<header class="main-header">
	<div class="d-flex align-items-center logo-box justify-content-start">	
		<!-- Logo -->
		<a href="{{ url('/') }}" class="logo">
		  <!-- logo-->
		  <div class="logo-mini w-30">
              <img
                  id="logo-mini-img"
                  src="{{ $squareLightFinal }}"
                  data-light="{{ $squareLightFinal }}"
                  data-dark="{{ $squareDarkFinal }}"
                  alt="logo"
                  onerror="this.onerror=null;this.src='{{ $fallbackSquare }}';"
              >
		  </div>
		  <div class="logo-lg">
			  <span class="light-logo">
                  <img src="{{ $horizLightFinal }}" alt="logo">
              </span>
			  <span class="dark-logo">
                  <img src="{{ $horizDarkFinal }}" alt="logo">
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
    function applyMini() {
        const img = document.getElementById('logo-mini-img');
        if (!img) return;

        const light = img.getAttribute('data-light') || '';
        const dark  = img.getAttribute('data-dark')  || '';

        // IMPORTANTÍSSIMO: no seu template, o comportamento está invertido.
        // Você relatou que no modo claro estava vindo a branca (dark).
        // Então aqui: checked = LIGHT (invertido), unchecked = DARK.
        const toggle = document.getElementById('toggle_left_sidebar_skin');
        const checked = toggle ? !!toggle.checked : false;

        const isDark = !checked; // <-- INVERTIDO (ajuste definitivo)

        const next = isDark ? (dark || light) : (light || dark);

        // Evita setar vazio e cair em fallback
        if (next && img.src !== next) {
            img.src = next;
        }
    }

    function bind() {
        applyMini();

        const toggle = document.getElementById('toggle_left_sidebar_skin');
        if (toggle) {
            toggle.addEventListener('change', function () {
                setTimeout(applyMini, 10);
                setTimeout(applyMini, 100);
                setTimeout(applyMini, 300);
            });
        }

        // Alguns templates alteram tema via JS após o change
        const obs = new MutationObserver(function () {
            applyMini();
        });

        if (document.body) {
            obs.observe(document.body, { attributes: true, attributeFilter: ['class', 'style'] });
        }

        // fallback extra
        setInterval(applyMini, 1200);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
</script>
