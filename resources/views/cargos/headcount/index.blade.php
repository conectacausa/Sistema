<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>Conectta RH | Headcount</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  {{-- CSS para TAGS no padrão do template (cor primária) --}}
  <link rel="stylesheet" href="{{ asset('assets/css/custom-tagsinput.css') }}">
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

      {{-- HEADER --}}
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Headcount</h4>
            <nav>
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                </li>
                <li class="breadcrumb-item">Cargos</li>
                <li class="breadcrumb-item active">QLP</li>
              </ol>
            </nav>
          </div>
        </div>
      </div>

      <section class="content">

        {{-- FILTROS --}}
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Filtros</h4>
              </div>

              <div class="box-body">

                {{-- Cargo / CBO --}}
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

                {{-- Filial / Setor / Liberação --}}
                <div class="row">

                  {{-- FILIAL --}}
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Filial</label>
                      <select
                        id="filtro-filial"
                        class="form-control"
                        multiple
                        data-role="tagsinput"
                      >
                        @foreach($filiais as $filial)
                          <option
                            value="{{ $filial->id }}"
                            @selected(in_array((int)$filial->id, (array)request('filial_id', [])))
                          >
                            {{ $filial->nome_fantasia }}
                          </option>
                        @endforeach
                      </select>
                      <small class="text-muted">Opcional. Se vazio, mostra todas.</small>
                    </div>
                  </div>

                  {{-- SETOR --}}
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Setor</label>
                      <select
                        id="filtro-setor"
                        class="form-control"
                        multiple
                        data-role="tagsinput"
                      >
                        @foreach($setores as $setor)
                          <option
                            value="{{ $setor->id }}"
                            @selected(in_array((int)$setor->id, (array)request('setor_id', [])))
                          >
                            {{ $setor->nome }}
                          </option>
                        @endforeach
                      </select>
                      <small class="text-muted">Dependente das filiais selecionadas.</small>
                    </div>
                  </div>

                  {{-- LIBERAÇÃO --}}
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Liberação</label>
                      <select id="filtro-liberacao" class="form-control">
                        @foreach($liberacoes as $l)
                          <option value="{{ $l->ym }}" @selected($ym === $l->ym)>
                            {{ $l->ym }}
                          </option>
                        @endforeach
                      </select>
                      <small class="text-muted">Obrigatório.</small>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- TABELA --}}
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Quadro de Lotação Previsto</h4>
              </div>
              <div class="box-body">
                <div id="headcount-table-wrap">
                  @include('cargos.headcount._table', ['groups' => $groups])
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

{{-- JS --}}
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

{{-- ATIVA O TAGSINPUT DO TEMPLATE --}}
<script src="{{ asset('assets/js/pages/advanced-form-element.js') }}"></script>

<script>
(function () {
  const inputQ    = document.getElementById('filtro-q');
  const selFilial = document.getElementById('filtro-filial');
  const selSetor  = document.getElementById('filtro-setor');
  const selLib    = document.getElementById('filtro-liberacao');
  const wrap      = document.getElementById('headcount-table-wrap');

  let timer = null;

  function getMultiValues(selectEl) {
    return Array.from(selectEl.selectedOptions).map(o => o.value).filter(Boolean);
  }

  function buildUrl() {
    const url = new URL("{{ route('cargos.headcount.index') }}", window.location.origin);

    const q = inputQ.value.trim();
    const filiais = getMultiValues(selFilial);
    const setores = getMultiValues(selSetor);
    const lib = selLib.value;

    if (q) url.searchParams.set('q', q);
    filiais.forEach(f => url.searchParams.append('filial_id[]', f));
    setores.forEach(s => url.searchParams.append('setor_id[]', s));
    if (lib) url.searchParams.set('liberacao', lib);

    url.searchParams.set('ajax', '1');
    return url.toString();
  }

  async function fetchTable() {
    const res = await fetch(buildUrl(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    wrap.innerHTML = await res.text();
  }

  inputQ.addEventListener('keyup', () => {
    clearTimeout(timer);
    timer = setTimeout(fetchTable, 300);
  });

  selFilial.addEventListener('change', fetchTable);
  selSetor.addEventListener('change', fetchTable);
  selLib.addEventListener('change', fetchTable);

})();
</script>

</body>
</html>
