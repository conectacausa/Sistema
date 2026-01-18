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
              {{-- Não depende do CSS do template: 1 imagem trocada via JS --}}
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
    function getMiniImg() {
        return document.getElementById('logo-mini-img');
    }

    // Determina DARK do jeito mais confiável para esse template:
    // 1) pelo checkbox (é o que o template usa)
    // 2) fallback por classe no body/html (alguns templates aplicam classe)
    function isDark() {
        const toggle = document.getElementById('toggle_left_sidebar_skin');
        if (toggle) {
            // Em alguns templates, checked = DARK; em outros é invertido.
            // Então fazemos o "estado real" observando qual logo-lg está visível:
            // (logo-lg já funciona corretamente)
            const lg = document.querySelector('.logo-lg');
            if (lg) {
                const lightEl = lg.querySelector('.light-logo');
                const darkEl  = lg.querySelector('.dark-logo');

                const lightVisible = lightEl && window.getComputedStyle(lightEl).display !== 'none';
                const darkVisible  = darkEl  && window.getComputedStyle(darkEl).display !== 'none';

                if (darkVisible && !lightVisible) return true;
                if (lightVisible && !darkVisible) return false;
            }

            // fallback: usa o checkbox
            return !!toggle.checked;
        }

        const cls = ((document.body && document.body.className) || '') + ' ' + ((document.documentElement && document.documentElement.className) || '');
        return cls.toLowerCase().includes('dark');
    }

    function applyMini() {
        const img = getMiniImg();
        if (!img) return;

        const light = img.getAttribute('data-light');
        const dark  = img.getAttribute('data-dark');

        img.src = isDark() ? (dark || light) : (light || dark);
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

        // Observa mudanças no DOM porque o template pode alternar tema via JS
        const obs = new MutationObserver(function () {
            applyMini();
        });

        if (document.body) {
            obs.observe(document.body, { attributes: true, attributeFilter: ['class', 'style'], subtree: false });
        }

        const lg = document.querySelector('.logo-lg');
        if (lg) {
            obs.observe(lg, { attributes: true, attributeFilter: ['class', 'style'], subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
</script>
