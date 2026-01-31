<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name','ConecttaRH') }} | Grupo de Permissão</title>

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
            <h4 class="page-title">Grupos de Permissão</h4>
            <nav>
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                </li>
                <li class="breadcrumb-item">Configuração</li>
                <li class="breadcrumb-item active">Grupos de Permissão</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>

      <section class="content">

        <!-- FILTRO -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Filtros</h4>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label class="form-label">Nome do Grupo</label>
                  <input type="text"
                         id="filtro-nome"
                         class="form-control"
                         placeholder="Digite para filtrar"
                         autocomplete="off">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TABELA -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Grupo de Permissão</h4>
              </div>
              <div class="box-body">
                <div id="tabela-grupos">
                  @include('config.grupos.partials.table', ['grupos' => $grupos])
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
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
(function () {
    const input = document.getElementById('filtro-nome');
    const container = document.getElementById('tabela-grupos');
    let timer = null;

    function carregar(url) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => container.innerHTML = html);
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const url = new URL("{{ route('config.grupos.index') }}", window.location.origin);
            url.searchParams.set('ajax', '1');
            if (input.value.trim() !== '') {
                url.searchParams.set('nome_grupo', input.value.trim());
            }
            carregar(url.toString());
        }, 300);
    });

    // paginação ajax
    document.addEventListener('click', function (e) {
        const link = e.target.closest('#tabela-grupos .pagination a');
        if (!link) return;
        e.preventDefault();
        const url = new URL(link.href);
        url.searchParams.set('ajax', '1');
        carregar(url.toString());
    });
})();
</script>

</body>
</html>
