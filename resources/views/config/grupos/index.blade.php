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
            <h4 class="page-title">Grupos de Permissão</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item" aria-current="page">Grupos de Permissão</li>
                </ol>
              </nav>
            </div>
          </div>

          {{-- Botão "Novo Grupo" removido conforme pedido --}}
        </div>
      </div>

      <!-- Main content -->
      <section class="content">
        <!-- Filtros -->
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
                      <input
                        type="text"
                        id="filtro-nome-grupo"
                        name="nome_grupo"
                        class="form-control"
                        placeholder="Nome do Grupo"
                        autocomplete="off">
                      <small class="text-muted">Digite para filtrar automaticamente.</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!--Tabela Empresas -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Grupo de Permissão</h4>
              </div>
              <div class="box-body">
                <div id="grupos-tabela">
                  @include('config.grupos.partials.tabela', ['grupos' => $grupos])
                </div>
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

<script>
(function () {
  const input = document.getElementById('filtro-nome-grupo');
  const wrap  = document.getElementById('grupos-tabela');
  let timer = null;

  function carregar(url) {
    wrap.style.opacity = '0.6';

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.text())
      .then(html => {
        wrap.innerHTML = html;
      })
      .catch(err => console.error(err))
      .finally(() => {
        wrap.style.opacity = '1';
      });
  }

  function montarUrl(paginaUrl) {
    const u = new URL(paginaUrl || "{{ route('config.grupos.index') }}", window.location.origin);
    u.searchParams.set('ajax', '1');

    const nome = input.value.trim();
    if (nome !== '') u.searchParams.set('nome_grupo', nome);
    else u.searchParams.delete('nome_grupo');

    return u.toString();
  }

  // Atualiza ao digitar (debounce)
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(() => {
      carregar(montarUrl());
    }, 300);
  });

  // Paginação via AJAX
  document.addEventListener('click', function (ev) {
    const a = ev.target.closest('#grupos-tabela .pagination a');
    if (!a) return;
    ev.preventDefault();
    carregar(montarUrl(a.href));
  });
})();
</script>

</body>
</html>
