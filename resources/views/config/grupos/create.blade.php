<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Grupo de Permissão</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  {{-- {Incluir aqui o arquivo de header} --}}
  @includeIf('partials.header')

  {{-- {Incluir menu aqui} --}}
  @includeIf('partials.menu')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="container-full">

      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Novo Grupo de Permissão</h4>

            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item">
                    <a href="{{ route('config.grupos.index') }}">Grupos de Permissão</a>
                  </li>
                  <li class="breadcrumb-item" aria-current="page">Novo Grupo</li>
                </ol>
              </nav>
            </div>
          </div>

          {{-- (Sem botão aqui, conforme pedido) --}}
        </div>
      </div>

      <!-- Main content -->
      <section class="content">

        <div class="row">
          <div class="col-12">
            <div class="box">

              <div class="box-header with-border">
                <h4 class="box-title">Dados do Grupo</h4>
              </div>

              <div class="box-body">
                <form method="POST" action="{{ route('config.grupos.store') }}">
                  @csrf

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-label">Nome do Grupo</label>

                        <input type="text"
                               name="nome_grupo"
                               class="form-control @error('nome_grupo') is-invalid @enderror"
                               placeholder="Nome do Grupo"
                               value="{{ old('nome_grupo') }}"
                               maxlength="160"
                               required>

                        @error('nome_grupo')
                          <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  </div>

                  {{-- Botão Salvar verde à direita --}}
                  <div class="d-flex justify-content-end">
                    <button type="submit"
                            class="waves-effect waves-light btn bg-gradient-success">
                      Salvar
                    </button>
                  </div>

                </form>
              </div>

            </div>
          </div>
        </div>

      </section>
      <!-- /.content -->

    </div>
  </div>
  <!-- /.content-wrapper -->

  {{-- {Incluir footer aqui} --}}
  @includeIf('partials.footer')

</div>
<!-- ./wrapper -->


<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- Coup Admin App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

</body>
</html>
