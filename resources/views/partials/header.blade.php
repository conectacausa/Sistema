@php
  // Espera que o middleware de tenant coloque isso em config('tenant.empresa') / config('tenant.config')
  $empresa = config('tenant.empresa');
  $cfg = config('tenant.config');

  $isDark = false; // se seu template tiver um toggle, você pode trocar isso depois
  $logoMini = $isDark ? ($cfg?->logo_quadrado_dark ?? null) : ($cfg?->logo_quadrado_light ?? null);
  $logoLg   = $isDark ? ($cfg?->logo_horizontal_dark ?? null) : ($cfg?->logo_horizontal_light ?? null);

  $defaultLogoMini = asset('assets/images/logo-light-text2.png'); // ajuste para sua logo padrão
  $defaultLogoLg   = asset('assets/images/logo-light-text2.png'); // ajuste para sua logo padrão

  $user = auth()->user();

  $avatar = null;
  if ($user?->foto) {
    $avatar = asset($user->foto);
  } else {
    $sexo = $user?->colaborador?->sexo ?? 'NI';
    $avatar = ($sexo === 'F')
      ? asset('assets/images/avatar/avatar-2.png')
      : asset('assets/images/avatar/avatar-15.png');
  }
@endphp

<header class="main-header">
  <a href="{{ route('dashboard') }}" class="logo">
    <div class="logo-mini">
      <img src="{{ $logoMini ? asset($logoMini) : $defaultLogoMini }}" class="logo-mini-w30" alt="logo">
    </div>
    <div class="logo-lg">
      <img src="{{ $logoLg ? asset($logoLg) : $defaultLogoLg }}" class="logo-lg" alt="logo">
    </div>
  </a>

  <nav class="navbar navbar-static-top">
    <div class="navbar-custom-menu r-side">
      <ul class="nav navbar-nav">
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
            <img src="{{ $avatar }}" class="avatar avatar-pill" alt="User Image">
            <span class="hidden-xs">{{ $user?->nome_completo ?? 'Usuário' }}</span>
          </a>
          <ul class="dropdown-menu">
            <li class="user-header">
              <img src="{{ $avatar }}" class="img-circle" alt="User Image">
              <p>{{ $user?->nome_completo ?? 'Usuário' }}</p>
            </li>
            <li class="user-footer">
              <div class="pull-left">
                <a href="#" class="btn btn-default btn-flat">Perfil</a>
              </div>
              <div class="pull-right">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="btn btn-default btn-flat" type="submit">Sair</button>
                </form>
              </div>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</header>
