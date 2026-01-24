<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>Conectta RH | Cargos</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">
<div class="wrapper">
  <div id="loader"></div>

  @includeIf('partials.header')
  @includeIf('layouts.partials.header')
  @includeIf('includes.header')

  @includeIf('partials.menu')
  @includeIf('layouts.partials.menu')
  @includeIf('includes.menu')

  <div class="content-wrapper">
    <div class="container-full">

      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Cargos</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Cadastro</li>
                  <li class="breadcrumb-item active" aria-current="page">Cargos</li>
                </ol>
              </nav>
            </div>
          </div>

          @if(!empty($podeCadastrar) && $podeCadastrar)
            <a href="{{ route('cargos.cargos.create') }}" class="waves-effect waves-light btn mb-5 bg-gradient-success">
              Novo Cargo
            </a>
          @endif
        </div>
      </div>

      <section class="content">

        {{-- Filtros --}}
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
                      <label class="form-label">Cargo ou CBO</label>
                      <input
                        id="filtro-q"
                        type="text"
                        class="form-control"
                        placeholder="Cargo ou CBO"
                        value="{{ request('q') }}"
                        autocomplete="off"
                      >
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Filial</label>
                      <select id="filtro-filial" class="form-control">
                        <option value="">Selecione</option>
                        @foreach(($filiais ?? []) as $filial)
                          <option value="{{ $filial->id }}">
                            {{ $filial->nome_fantasia }}
                          </option>
                        @endforeach
                         </select>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="form-group">
                      <label class="form-label">Setor</label>
                      <select id="filtro-setor" class="form-control">
                        <option value="">Selecione</option>
                        @foreach(($setores ?? []) as $setor)
                          <option value="{{ $setor->id }}" @selected((int)request('setor_id') === (int)$setor->id)>
                            {{ $setor->nome }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>

              </div>{{-- box-body --}}
            </div>
          </div>
        </div>

        {{-- Tabela --}}
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Cargos</h4>
              </div>
              <div class="box-body">
                <div id="cargos-table-wrap">
                  @include('cargos.cargos._table', ['cargos' => $cargos, 'podeEditar' => $podeEditar])
                </div>
              </div>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>

  @includeIf('partials.footer')
  @includeIf('layouts.partials.footer')
  @includeIf('includes.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
(function () {
  const inputQ   = document.getElementById('filtro-q');
  const selFilial= document.getElementById('filtro-filial');
  const selSetor = document.getElementById('filtro-setor');
  const wrap     = document.getElementById('cargos-table-wrap');

  let timer = null;

  function buildUrl(pageUrl) {
    const base = pageUrl || "{{ route('cargos.cargos.index') }}";
    const url  = new URL(base, window.location.origin);

    const q = (inputQ.value || '').trim();
    const filial = selFilial.value || '';
    const setor  = selSetor.value || '';

    if (q.length) url.searchParams.set('q', q);
    if (filial)   url.searchParams.set('filial_id', filial);
    if (setor)    url.searchParams.set('setor_id', setor);

    url.searchParams.set('ajax', '1');
    return url.toString();
  }

  function updateBrowserUrl() {
    const clean = new URL("{{ route('cargos.cargos.index') }}", window.location.origin);

    const q = (inputQ.value || '').trim();
    const filial = selFilial.value || '';
    const setor  = selSetor.value || '';

    if (q.length) clean.searchParams.set('q', q);
    if (filial)   clean.searchParams.set('filial_id', filial);
    if (setor)    clean.searchParams.set('setor_id', setor);

    window.history.replaceState({}, '', clean.toString());
  }

  async function fetchTable(pageUrl) {
    try {
      const res = await fetch(buildUrl(pageUrl), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      const html = await res.text();
      wrap.innerHTML = html;
      updateBrowserUrl();
    } catch (e) {
      console.error(e);
    }
  }

  async function carregarSetoresPorFilial() {
    const filial = selFilial.value || '';
    selSetor.innerHTML = '<option value="">Selecione</option>';

    if (!filial) {
      fetchTable(null);
      return;
    }

    try {
      const url = new URL("{{ route('cargos.setores_por_filial') }}", window.location.origin);
      url.searchParams.set('filial_id', filial);

      const res = await fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await res.json();

      for (const item of data) {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.nome;
        selSetor.appendChild(opt);
      }

      // ao trocar filial, limpa setor e filtra
      selSetor.value = '';
      fetchTable(null);
    } catch (e) {
      console.error(e);
      fetchTable(null);
    }
  }

  inputQ.addEventListener('keyup', function () {
    clearTimeout(timer);
    timer = setTimeout(() => fetchTable(null), 250);
  });

  selFilial.addEventListener('change', carregarSetoresPorFilial);
  selSetor.addEventListener('change', () => fetchTable(null));

  document.addEventListener('click', function (e) {
    const a = e.target.closest('#cargos-table-wrap .pagination a');
    if (!a) return;
    e.preventDefault();
    fetchTable(a.getAttribute('href'));
  });
})();
</script>

</body>
</html>
