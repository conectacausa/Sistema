<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">

  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>Conectta RH</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition theme-primary bg-img" style="background-image: url('{{ asset('assets/images/auth-bg/bg-1.jpg') }}')">

  <div class="container h-p100">
    <div class="row align-items-center justify-content-md-center h-p100">
      <div class="col-12">
        <div class="row justify-content-center g-0">
          <div class="col-lg-5 col-md-5 col-12">
            <div class="bg-white rounded10 shadow-lg">
              <div class="content-top-agile p-20 pb-0">
                <img src="{{ asset('assets/images/logo-light-text2.png') }}" alt="logo">
              </div>

              <div class="p-40">
                <form action="{{ route('login.post') }}" method="post">
                  @csrf

                  <div class="form-group">
                    <div class="input-group mb-3">
                      <span class="input-group-text bg-transparent"><i class="ti-user"></i></span>
                      <input
                        type="text"
                        name="cpf"
                        id="cpf"
                        class="form-control ps-15 bg-transparent"
                        placeholder="CPF"
                        value="{{ old('cpf') }}"
                        required
                      >
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="input-group mb-3">
                      <span class="input-group-text bg-transparent"><i class="ti-lock"></i></span>
                      <input
                        type="password"
                        name="password"
                        class="form-control ps-15 bg-transparent"
                        placeholder="Senha"
                        required
                      >
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-6">
                      <div class="checkbox">
                        <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">Lembrar</label>
                      </div>
                    </div>

                    <div class="col-6">
                      <div class="fog-pwd text-end">
                        <a href="javascript:void(0)" class="hover-warning">
                          <i class="ion ion-locked"></i> Recuperar Senha?
                        </a><br>
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

  <!-- Vendor JS -->
  <script src="{{ asset('assets/js/vendors.min.js') }}"></script>
  <script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
  <script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

  <!-- seu template já tem toastr/notification aqui -->
  <script src="{{ asset('assets/js/pages/toastr.js') }}"></script>
  <script src="{{ asset('assets/js/pages/notification.js') }}"></script>

  <script>
    // máscara CPF (enquanto digita)
    document.getElementById('cpf')?.addEventListener('input', function (e) {
      let v = e.target.value.replace(/\D/g, '').slice(0, 11);
      v = v.replace(/(\d{3})(\d)/, '$1.$2');
      v = v.replace(/(\d{3})(\d)/, '$1.$2');
      v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
      e.target.value = v;
    });

    // toastr (mensagens vindas do backend)
    window.addEventListener('load', function () {
      @if(session('toastr_error'))
        toastr.error(@json(session('toastr_error')));
      @endif

      @if(session('toastr_success'))
        toastr.success(@json(session('toastr_success')));
      @endif

      @if($errors->any())
        toastr.error(@json($errors->first()));
      @endif
    });
  </script>

</body>
</html>
