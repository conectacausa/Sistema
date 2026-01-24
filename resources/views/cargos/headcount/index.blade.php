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
            <h4 class="page-title">Headcount</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Cargos</li>
                  <li class="breadcrumb-item active" aria-current="page">QLP</li>
                </ol>
              </nav>
            </div>
          </div>
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
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Filial</label>
                      <select id="filtro-filial" class="form-control" multiple>
                        @foreach(($filiais ?? []) as $filial)
                          <option value="{{ $filial->id }}"
                            @selected(in_array((int)$filial->id, (array)request('filial_id', []), true))>
                            {{ $filial->nome_fantasia }}
                          </option>
                        @endforeach
                      </select>
                      <small class="text-muted">Você pode selecionar mais de uma filial.</small>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Setor</label>
                      <select id="filtro-setor" class="form-control" multiple>
                        @foreach(($setores ?? []) as $setor)
                          <option value="{{ $setor->id }}"
                            @selected(in_array((int)$setor->id, (array)request('setor_id', []), true))>
                            {{ $setor->nome }}
                          </option>
                        @endforeach
                      </select>
                      <small class="text-muted">Setores disponíveis dependem das filiais selecionadas.</small>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Liberação</label>
                      <select id="filtro-liberacao" class="form-control">
                        <option value="">Selecione</option>
                        @foreach(($liberacoes ?? []) as $l)
                          <option value="{{ $l->ym }}" @selected((string)request('liberacao', $ym ?? '') === (string)$l->ym)>
                            {{ $l->ym }}
                          </option>
                        @endforeach
                      </select>
                      <small class="text-muted">Mostra apenas meses que possuem liberação.</small>
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
                <h4 class="box-title">QLP</h4>
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

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
(function () {
  const inputQ    = document.getElementById('filtro-q');
  const selFilial = document.getElementById('filtro-filial');
  const selSetor  = document.getElementById('filtro-setor');
  const selLib    = document.getElementById('filtro-liberacao');
  const wrap      = document.getElementById('headcount-table-wrap');

  let timer = null;

  // Se o template já tem select2 (muito comum nesses vendors), ativa para virar "tags"
  function tryInitSelect2(el) {
    if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
      jQuery(el).select2({
        width: '100%',
        placeholder: 'Selecione',
        closeOnSelect: false
      });
      return true;
    }
    return false;
  }

  tryInitSelect2(selFilial);
  tryInitSelect2(selSetor);

  function getMultiValues(selectEl) {
    return Array.from(selectEl.selectedOptions).map(o => o.value).filter(Boolean);
  }

  function buildUrl(pageUrl) {
    const base = pageUrl || "{{ route('cargos.headcount.index') }}";
    const url  = new URL(base, window.location.origin);

    const q = (inputQ.value || '').trim();
    const filiais = getMultiValues(selFilial);
    const setores = getMultiValues(selSetor);
    const lib = selLib.value || '';

    if (q.length) url.searchParams.set('q', q);

    // multi params: filial_id[]=1&filial_id[]=2
    url.searchParams.delete('filial_id[]');
    url.searchParams.delete('setor_id[]');

    for (const f of filiais) url.searchParams.append('filial_id[]', f);
    for (const s of setores) url.searchParams.append('setor_id[]', s);

    if (lib) url.searchParams.set('liberacao', lib);

    url.searchParams.set('ajax', '1');
    return url.toString();
  }

  function updateBrowserUrl() {
    const clean = new URL("{{ route('cargos.headcount.index') }}", window.location.origin);

    const q = (inputQ.value || '').trim();
    const filiais = getMultiValues(selFilial);
    const setores = getMultiValues(selSetor);
    const lib = selLib.value || '';

    if (q.length) clean.searchParams.set('q', q);

    for (const f of filiais) clean.searchParams.append('filial_id[]', f);
    for (const s of setores) clean.searchParams.append('setor_id[]', s);

    if (lib) clean.searchParams.set('liberacao', lib);

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

  async function carregarSetoresPorFiliais() {
    // limpa setor quando muda filiais
    const filiais = getMultiValues(selFilial);

    // reset select
    selSetor.innerHTML = '';

    // se tiver select2, precisa limpar via jQuery também
    if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
      jQuery(selSetor).val(null).trigger('change');
    }

    if (!filiais.length) {
      // sem filiais, setores vazios e atualiza tabela
      fetchTable(null);
      return;
    }

    try {
      const url = new URL("{{ route('cargos.headcount.setores_por_filiais') }}", window.location.origin);
      for (const f of filiais) url.searchParams.append('filial_id[]', f);

      const res = await fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await res.json();

      // popula setores (união das filiais selecionadas)
      // (se quiser, dá pra agrupar por filial depois)
      for (const item of data) {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.nome;
        selSetor.appendChild(opt);
      }

      // reativa select2 se existir
      tryInitSelect2(selSetor);

      // filtra tabela
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

  selFilial.addEventListener('change', carregarSetoresPorFiliais);
  selSetor.addEventListener('change', () => fetchTable(null));
  selLib.addEventListener('change', () => fetchTable(null));

  document.addEventListener('click', function (e) {
    const a = e.target.closest('#headcount-table-wrap .pagination a');
    if (!a) return;
    e.preventDefault();
    fetchTable(a.getAttribute('href'));
  });
})();
</script>

</body>
</html>
