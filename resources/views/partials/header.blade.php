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
    $logoSquareLight = $toUrl($config->logo_quadrado_light ?? null); // light (ex: storage/tenants/1/logo_quadrada_azul.png)
    $logoSquareDark  = $toUrl($config->logo_quadrado_dark ?? null);  // dark  (ex: storage/tenants/1/logo_quadrada_branca.png)

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

    // URLs finais garantidas (NUNCA vazias)
    $squareLightFinal = $logoSquareLight ?: $fallbackSquare;
    $squareDarkFinal  = $logoSquareDark  ?: $fallbackSquare;
    $horizLightFinal  = $logoHorizLight  ?: $fallbackHorizL;
    $horizDarkFinal   = $logoHorizDark   ?: $fallbackHorizD;
@endphp

<header class="main-header">
	<div class="d-flex align-items-center logo-box justify-content-start">	
		<!-- Logo -->
		<a href="{{ url('/') }}" class="logo">
		  <!-- logo-->
		  <div class="logo-mini w-30">
              <span class="light-logo"><img src="{{ $squareDarkFinal }}" alt="logo"></span>
			  <span class="dark-logo"><img src="{{ $squareLightFinal }}" alt="logo"></span>
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
        const dark  = img.getAttribute('data-dark') || '';
        const fallback = img.getAttribute('data-fallback') || '';

        const toggle = document.getElementById('toggle_left_sidebar_skin');
        const checked = toggle ? !!toggle.checked : false;

        // Mantemos a regra que funcionou no seu template:
        // checked = LIGHT (invertido), unchecked = DARK
        const isDark = !checked;

        // Se estiver em LIGHT, a prioridade ABSOLUTA é o light do banco.
        // Se estiver em DARK, a prioridade ABSOLUTA é o dark do banco.
        const desired = isDark ? (dark || light || fallback) : (light || dark || fallback);

        // Se por algum motivo o template/JS colocou fallback em LIGHT,
        // aqui nós forçamos de volta para o desejado.
        if (desired && img.src !== desired) {
            img.src = desired;
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

        // Observa mudanças (alguns templates mexem no DOM após o toggle)
        const obs = new MutationObserver(function () {
            applyMini();
        });

        if (document.body) {
            obs.observe(document.body, { attributes: true, attributeFilter: ['class', 'style'] });
        }

        // reforço para vencer JS do template que reescreve o src
        setInterval(applyMini, 700);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
</script>
