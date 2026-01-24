<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>Conectta RH | Headcount</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor_components/select2/dist/css/select2.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  {{-- FORÇA VISUAL DE TAG (pílula) no Select2 e usa cor primária do template --}}
  <style>
    .select2-container--default .select2-selection--multiple {
      min-height: 42px;
      padding: 4px;
      border-radius: 6px;
    }

    .select2-container--default
    .select2-selection--multiple
    .select2-selection__choice {
      background-color: var(--primary, var(--bs-primary, #0d6efd)) !important;
      border: none !important;
      color: #fff !important;
      padding: 5px 10px !important;
      margin-top: 6px !important;
      margin-right: 6px !important;
      border-radius: 4px !important;
      font-size: 13px !important;
      display: inline-flex;
      align-items: center;
    }

    .select2-container--default
    .select2-selection--multiple
    .select2-selection__choice__remove {
      color: #fff !important;
      margin-right: 6px;
      opacity: .85;
    }

    .select2-container--default
    .select2-selection--multiple
    .select2-selection__choice__remove:hover {
      opacity: 1;
    }

    .select2-container--default
    .select2-results__option--highlighted.select2-results__option--selectable {
      background-color: var(--primary, var(--bs-primary, #0d6efd)) !important;
      color: #fff !important;
    }
  </style>
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

                  {{-- FILIAL (select2 multiple -> TAGS) --}}
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Filial</label>
                      <select
                        id="filtro-filial"
                        class="form-control select2"
                        multiple
                        data-placeholder="Selecione"
                      >
                        @foreach(($filiais ?? []) as $filial)
                          <option value="{{ $filial->id }}"
                            @selected(in_array((int)$filial->id, (array)request('filial_id', [])))>
                            {{ $filial->nome_fantasia }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  {{-- SETOR (select2 multiple -> TAGS) --}}
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Setor</label>
                      <select
                        id="filtro-setor"
                        class="form-control select2"
                        multiple
                        data-placeholder="Selecione"
                      >
                        @foreach(($setores ?? []) as $setor)
                          <option value="{{ $setor->id }}"
                            @selected(in_array((int)$setor->id, (array)request('setor_id', [])))>
                            {{ $setor->nome }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  {{-- LIBERAÇÃO --}}
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Liberação</label>
                      <select id="filtro-liberacao" class="form-control">
                        @foreach(($liberacoes ?? []) as $l)
                          <option value="{{ $l->ym }}" @selected(($ym ?? '') === $l->ym)>
                            {{ $l->ym }}
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

{{-- JS base --}}
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>
<script src="{{ asset('assets/vendor_components/select2/dist/js/select2.full.min.js') }}"></script>

{{-- Se o template inicializa select2 aqui, ok manter --}}
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

  function buildUrl(pageUrl) {
    const base = pageUrl || "{{ route('cargos.headcount.index') }}";
    const url  = new URL(base, window.location.origin);

    const q = (inputQ.value || '').trim();
    const filiais = getMultiValues(selFilial);
    const setores = getMultiValues(selSetor);
    const lib = selLib.value || '';

    if (q.length) url.searchParams.set('q', q);
    for (const f of filiais) url.searchParams.append('filial_id[]', f);
    for (const s of setores) url.searchParams.append('setor_id[]', s);
    if (lib) url.searchParams.set('liberacao', lib);

    url.searchParams.set('ajax', '1');
    return url.toString();
  }

  async function fetchTable(pageUrl) {
    try {
      const res = await fetch(buildUrl(pageUrl), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      wrap.innerHTML = await res.text();
    } catch (e) {
      console.error(e);
    }
  }

  function initSelect2Tags() {
    if (!window.jQuery || !jQuery.fn.select2) return;

    // Se já estiver inicializado, destrói e recria
    const $filial = jQuery(selFilial);
    const $setor  = jQuery(selSetor);

    if ($filial.data('select2')) $filial.select2('destroy');
    if ($setor.data('select2'))  $setor.select2('destroy');

    $filial.select2({
      width: '100%',
      closeOnSelect: false,
      placeholder: 'Selecione'
    });

    $setor.select2({
      width: '100%',
      closeOnSelect: false,
      placeholder: 'Selecione'
    });

    // Eventos do Select2 (garante atualização com adicionar/remover tag)
    $filial.off('change.headcount').on('change.headcount', function () {
      carregarSetoresPorFiliais();
    });

    $setor.off('change.headcount').on('change.headcount', function () {
      fetchTable(null);
    });
  }

  async function carregarSetoresPorFiliais() {
    const filiais = getMultiValues(selFilial);
    const selectedBefore = new Set(getMultiValues(selSetor));

    // limpa options do setor
    selSetor.innerHTML = '';

    // se não tiver filiais selecionadas, setor fica vazio e tabela atualiza
    if (!filiais.length) {
      // reset select2 setor
      if (window.jQuery && jQuery.fn.select2) {
        jQuery(selSetor).val(null).trigger('change');
      }
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

      const stillSelected = [];
      for (const item of data) {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.nome;
        selSetor.appendChild(opt);

        if (selectedBefore.has(String(item.id))) {
          stillSelected.push(String(item.id));
        }
      }

      // Recria select2 do setor para refletir novas options
      if (window.jQuery && jQuery.fn.select2) {
        const $setor = jQuery(selSetor);
        if ($setor.data('select2')) $setor.select2('destroy');

        $setor.select2({
          width: '100%',
          closeOnSelect: false,
          placeholder: 'Selecione'
        });

        $setor.val(stillSelected).trigger('change');
        $setor.off('change.headcount').on('change.headcount', function () {
          fetchTable(null);
        });
      }

      fetchTable(null);
    } catch (e) {
      console.error(e);
      fetchTable(null);
    }
  }

  // ===== INIT =====
  initSelect2Tags();

  inputQ.addEventListener('keyup', function () {
    clearTimeout(timer);
    timer = setTimeout(() => fetchTable(null), 250);
  });

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
