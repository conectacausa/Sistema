@php
  $cfg = config('tenant.config');
  $isDark = false;
  $logo = $isDark ? ($cfg?->logo_horizontal_dark ?? null) : ($cfg?->logo_horizontal_light ?? null);
  $defaultLogo = asset('assets/images/logo-light-text2.png');

  $cpfValue = old('cpf', request()->cookie('remember_cpf', ''));
@endphp

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>ConecttaRH - Login</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
  <link rel="stylesheet" href="/assets/plugins/toastr/toastr.min.css">
</head>

<body class="hold-transition theme-primary bg-img" style="background-image: url({{ asset('assets/images/auth-bg/bg-1.jpg') }})">
  <div class="container h-p100">
    <div class="row align-items-center justify-content-md-center h-p100">
      <div class="col-12">
        <div class="row justify-content-center g-0">
          <div class="col-lg-5 col-md-5 col-12">
            <div class="bg-white rounded10 shadow-lg">
              <div class="content-top-agile p-20 pb-0">
                <img src="{{ $logo ? asset($logo) : $defaultLogo }}" alt="logo">
              </div>

              <div class="p-40">
                <form action="{{ route('login.post') }}" method="post">
                  @csrf

                  <div class="form-group">
                    <div class="input-group mb-3">
                      <span class="input-group-text bg-transparent"><i class="ti-user"></i></span>
                      <input
                        id="cpf"
                        name="cpf"
                        type="text"
                        class="form-control ps-15 bg-transparent"
                        placeholder="CPF"
                        value="{{ $cpfValue }}"
                        autocomplete="username"
                        required
                      >
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="input-group mb-3">
                      <span class="input-group-text bg-transparent"><i class="ti-lock"></i></span>
                      <input
                        name="password"
                        type="password"
                        class="form-control ps-15 bg-transparent"
                        placeholder="Senha"
                        autocomplete="current-password"
                        required
                      >
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-6">
                      <div class="checkbox">
                        <input type="checkbox" id="remember_cpf" name="remember_cpf" value="1" {{ request()->cookie('remember_cpf') ? 'checked' : '' }}>
                        <label for="remember_cpf">Lembrar</label>
                      </div>
                    </div>

                    <div class="col-6">
                      <div class="fog-pwd text-end">
                        <a href="javascript:void(0)" class="hover-warning">
                          <i class="ion ion-locked"></i> Recuperar Senha?
                        </a>
                      </div>
                    </div>

                    <div class="col-12 text-center">
                      <button type="submit" class="btn btn-danger mt-10">Entrar</button>
                    </div>
                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
  <script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
  <script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
  <script src="{{ asset('assets/js/pages/toastr.js') }}"></script>
  <script src="{{ asset('assets/js/pages/notification.js') }}"></script>

  <script>
    // Máscara CPF digitando: 000.000.000-00
    const cpf = document.getElementById('cpf');
    cpf.addEventListener('input', () => {
      let v = cpf.value.replace(/\D/g, '').slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, '$1.$2');
      v = v.replace(/(\d{3})(\d)/, '$1.$2');
      v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      cpf.value = v;
    });

    @if(session('toastr'))
      (function () {
        const t = @json(session('toastr'));
        toastr.options.closeButton = true;
        toastr.options.progressBar = true;
        toastr.options.timeOut = 4000;
        toastr[t.type || 'info'](t.message);
      })();
    @endif
  </script>
</body>
</html>
