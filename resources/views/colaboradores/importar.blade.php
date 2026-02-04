<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>{{ config('app.name','ConecttaRH') }} | Importar Colaboradores</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">
<div class="wrapper">
  <div id="loader"></div>

  @includeIf('partials.header')
  @includeIf('partials.menu')

  <div class="content-wrapper">
    <div class="container-full">

      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Importar Colaboradores</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => request()->route('sub')]) }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Cadastros</li>
                  <li class="breadcrumb-item">Colaboradores</li>
                  <li class="breadcrumb-item" aria-current="page">Importar</li>
                </ol>
              </nav>
            </div>
          </div>

          <a href="{{ route('colaboradores.importar.modelo', ['sub' => request()->route('sub')]) }}"
             class="waves-effect waves-light btn mb-5 bg-gradient-info">
            Baixar Modelo Excel
          </a>
        </div>
      </div>

      <section class="content">

        @if(session('success'))
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('error') }}
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Arquivo de Importação</h4>
              </div>

              <form method="POST"
                    action="{{ route('colaboradores.importar.store', ['sub' => request()->route('sub')]) }}"
                    enctype="multipart/form-data">
                @csrf

                <div class="box-body">
                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label">Selecione o arquivo (.xlsx)</label>
                        <input type="file" name="arquivo" class="form-control" accept=".xlsx" required>
                        <small class="text-muted">
                          Use o modelo. Campos mínimos: <b>nome</b> e <b>cpf</b>.
                        </small>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="box-footer text-end">
                  <button type="submit" class="waves-effect waves-light btn bg-gradient-primary">
                    Enviar para Importação
                  </button>
                </div>
              </form>

            </div>
          </div>
        </div>

      </section>

    </div>
  </div>

  @includeIf('partials.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
  if (window.feather) feather.replace();
</script>

</body>
</html>
