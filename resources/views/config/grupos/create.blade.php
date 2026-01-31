<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Novo Grupo de Permissão</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
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
            <h4 class="page-title">Novo Grupo de Permissão</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item">Grupos de Permissão</li>
                  <li class="breadcrumb-item active">Novo Grupo</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>

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

                  {{-- Salvar verde à direita --}}
                  <div class="d-flex justify-content-end mt-3">
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

    </div>
  </div>

  @includeIf('partials.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>
</body>
</html>
