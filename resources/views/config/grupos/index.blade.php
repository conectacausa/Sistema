<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>{{ config('app.name','ConecttaRH') }} | Grupos de Permissão</title>

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
            <h4 class="page-title">Grupos de Permissão</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => request()->route('sub')]) }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item" aria-current="page">Grupos de Permissão</li>
                </ol>
              </nav>
            </div>
          </div>

          <a href="{{ route('config.grupos.create', ['sub' => request()->route('sub')]) }}"
             class="waves-effect waves-light btn mb-5 bg-gradient-success">
            Novo Grupo
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

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Filtros</h4>
              </div>
              <div class="box-body">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">Nome do Grupo</label>
                      <input id="filtro-nome" type="text" class="form-control"
                             placeholder="Nome do Grupo" value="{{ $q ?? '' }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Grupo de Permissão</h4>
              </div>

              <div class="box-body">
                <div id="tabela-wrapper">
                  @include('config.grupos.partials.tabela', ['grupos' => $grupos])
                </div>
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
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
(function(){
  const input = document.getElementById('filtro-nome');
  const wrapper = document.getElementById('tabela-wrapper');
  if(!input || !wrapper) return;

  let t = null;

  function fetchTabela(){
    const q = input.value || '';
    const url = new URL(window.location.href);
    url.searchParams.set('q', q);
    url.searchParams.set('ajax', '1');

    fetch(url.toString(), { headers: { 'X-Requested-With':'XMLHttpRequest' }})
      .then(r => r.text())
      .then(html => { wrapper.innerHTML = html; })
      .catch(() => {});
  }

  input.addEventListener('input', function(){
    clearTimeout(t);
    t = setTimeout(fetchTabela, 250);
  });
})();
</script>

</body>
</html>
