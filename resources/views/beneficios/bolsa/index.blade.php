{{-- resources/views/beneficios/bolsa/index.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name') }} | Bolsa de Estudos</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  @include('partials.header')
  @include('partials.menu')

  <div class="content-wrapper">
    <div class="container-full">

      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Bolsa de Estudos</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => $sub]) }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item active" aria-current="page">Bolsa de Estudos</li>
                </ol>
              </nav>
            </div>
          </div>

          <a href="{{ route('beneficios.bolsa.create', ['sub' => $sub]) }}"
             class="waves-effect waves-light btn mb-5 bg-gradient-success">
            Nova Bolsa
          </a>
        </div>
      </div>

      <section class="content">

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Filtros (sem botões) -->
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
                      <label class="form-label">Titulo / Ciclo</label>
                      <input type="text"
                             id="q"
                             name="q"
                             class="form-control"
                             placeholder="Digite para buscar..."
                             value="{{ request('q') }}">
                      <small class="text-muted">A lista atualiza automaticamente.</small>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        <!-- Tabela (carregada por AJAX ao digitar) -->
        <div id="js-processos-table">
          @include('beneficios.bolsa.partials._table', ['sub' => $sub, 'processos' => $processos])
        </div>

      </section>

    </div>
  </div>

  @include('partials.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script src="{{ asset('assets/js/app-delete-confirm.js') }}"></script>

<script>
  if (window.feather) feather.replace();

  (function () {
    const input = document.getElementById('q');
    const target = document.getElementById('js-processos-table');

    if (!input || !target) return;

    let timer = null;
    let lastQuery = input.value || '';
    let controller = null;

    function buildUrl(q) {
      const base = @json(route('beneficios.bolsa.grid', ['sub' => $sub]));
      const url = new URL(base, window.location.origin);
      if (q && q.trim() !== '') url.searchParams.set('q', q.trim());
      return url.toString();
    }

    async function fetchGrid(q) {
      const value = (q || '').trim();

      if (value === lastQuery.trim()) return;
      lastQuery = value;

      // cancela request anterior
      if (controller) controller.abort();
      controller = new AbortController();

      try {
        const res = await fetch(buildUrl(value), {
          method: 'GET',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          signal: controller.signal
        });

        if (!res.ok) throw new Error('Falha ao buscar registros');
        const html = await res.text();

        target.innerHTML = html;

        if (window.feather) feather.replace();
      } catch (e) {
        if (e.name === 'AbortError') return;
        // fallback silencioso (não trava a tela)
        console.error(e);
      }
    }

    function debounceFetch() {
      clearTimeout(timer);
      timer = setTimeout(() => fetchGrid(input.value), 300);
    }

    input.addEventListener('input', debounceFetch);

    // opcional: se veio com query na URL, já carrega
    if ((input.value || '').trim() !== '') {
      fetchGrid(input.value);
    }
  })();
</script>

</body>
</html>
