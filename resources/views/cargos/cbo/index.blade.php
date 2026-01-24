<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>Conectta RH | CBOs</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  {{-- Header --}}
  @includeIf('partials.header')
  @includeIf('layouts.partials.header')
  @includeIf('includes.header')

  {{-- Menu --}}
  @includeIf('partials.menu')
  @includeIf('layouts.partials.menu')
  @includeIf('includes.menu')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="container-full">

      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">CBOs</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Cadastro</li>
                  <li class="breadcrumb-item" aria-current="page">CBOs</li>
                </ol>
              </nav>
            </div>
          </div>

          @if(!empty($podeCadastrar) && $podeCadastrar)
            <a href="{{ route('cargos.cbo.create') }}" class="waves-effect waves-light btn mb-5 bg-gradient-success">
              Novo CBO
            </a>
          @endif
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
                      <label class="form-label">CBO ou Título</label>
                      <input
                        id="filtro-q"
                        type="text"
                        class="form-control"
                        placeholder="CBO ou Título"
                        value="{{ request('q') }}"
                        autocomplete="off"
                      >
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabela -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">CBOs</h4>
              </div>
              <div class="box-body">
                <div id="cbo-table-wrap">
                  @include('cargos.cbo._table', ['cbos' => $cbos])
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

  {{-- Footer --}}
  @includeIf('partials.footer')
  @includeIf('layouts.partials.footer')
  @includeIf('includes.footer')
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
  const input = document.getElementById('filtro-q');
  const wrap  = document.getElementById('cbo-table-wrap');

  let timer = null;

  function buildUrl(pageUrl) {
    const base = pageUrl || "{{ route('cargos.cbo.index') }}";
    const url  = new URL(base, window.location.origin);
    const q    = (input.value || '').trim();

    if (q.length) url.searchParams.set('q', q);
    url.searchParams.set('ajax', '1');
    return url.toString();
  }

  async function fetchTable(pageUrl) {
    try {
      const res = await fetch(buildUrl(pageUrl), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const html = await res.text();
      wrap.innerHTML = html;

      // atualiza URL do navegador (sem ajax=1)
      const clean = new URL("{{ route('cargos.cbo.index') }}", window.location.origin);
      const q = (input.value || '').trim();
      if (q.length) clean.searchParams.set('q', q);
      window.history.replaceState({}, '', clean.toString());
    } catch (e) {
      console.error(e);
    }
  }

  input.addEventListener('keyup', function () {
    clearTimeout(timer);
    timer = setTimeout(() => fetchTable(null), 250);
  });

  document.addEventListener('click', function (e) {
    const a = e.target.closest('#cbo-table-wrap .pagination a');
    if (!a) return;
    e.preventDefault();
    fetchTable(a.getAttribute('href'));
  });
})();
</script>

</body>
</html>
