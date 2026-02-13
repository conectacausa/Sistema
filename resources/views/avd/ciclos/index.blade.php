@extends('layouts.app')

@section('title', 'Avaliação de Desempenho')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">@yield('title')</h3>
      <div class="d-inline-block align-items-center">
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <a href="{{ route('dashboard', ['sub' => request()->route('sub')]) }}">
                <i class="mdi mdi-home-outline"></i>
              </a>
            </li>
            <li class="breadcrumb-item">AVD</li>
            <li class="breadcrumb-item active">@yield('title')</li>
          </ol>
        </nav>
      </div>
    </div>

    <a href="{{ route('avd.ciclos.create', ['sub' => request()->route('sub')]) }}"
       class="waves-effect waves-light btn mb-5 bg-gradient-success">
      ➕ Criar avaliação
    </a>
  </div>
</div>

<section class="content">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Título</label>
              <input id="filtro-q" class="form-control" value="{{ $q ?? '' }}" placeholder="Buscar...">
            </div>
            <div class="col-md-3">
              <label class="form-label">Status</label>
              <select id="filtro-status" class="form-select">
                <option value="">Todos</option>
                @foreach(['aguardando','iniciada','encerrada','em_consenso'] as $s)
                  <option value="{{ $s }}" @selected(($status ?? '') === $s)>{{ ucfirst(str_replace('_',' ', $s)) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label">Filial/Unidade</label>
              <select id="filtro-filial" class="form-select">
                <option value="">Todas</option>
                @foreach($filiais as $f)
                  <option value="{{ $f->id }}" @selected((string)($filial ?? '') === (string)$f->id)>
                    {{ $f->nome_fantasia ?? $f->razao_social }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Ciclos</h4>
        </div>
        <div class="box-body">
          <div id="tabela-wrapper">
            @include('avd.ciclos.partials.tabela', ['itens' => $itens])
          </div>
        </div>
      </div>

    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
  const q = document.getElementById('filtro-q');
  const st = document.getElementById('filtro-status');
  const fi = document.getElementById('filtro-filial');
  const wrapper = document.getElementById('tabela-wrapper');
  if (!wrapper) return;

  function renderFeather(){ if (window.feather) feather.replace(); }

  let t=null;
  function fetchTabela() {
    const url = new URL(window.location.href);
    url.searchParams.set('q', (q?.value||''));
    url.searchParams.set('status', (st?.value||''));
    url.searchParams.set('filial_id', (fi?.value||''));
    url.searchParams.set('ajax','1');

    fetch(url.toString(), { headers: { 'X-Requested-With':'XMLHttpRequest' }})
      .then(r => r.text())
      .then(html => { wrapper.innerHTML = html; renderFeather(); })
      .catch(()=>{});
  }

  [q, st, fi].forEach(el => {
    if (!el) return;
    el.addEventListener('input', () => { clearTimeout(t); t=setTimeout(fetchTabela, 250); });
    el.addEventListener('change', () => fetchTabela());
  });

  renderFeather();
})();
</script>
@endpush
