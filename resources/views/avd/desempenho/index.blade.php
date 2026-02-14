@extends('layouts.app')

@section('title', 'Avaliação de Desempenho')

@section('content')
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">

          {{-- Header + Breadcrumb (1 linha só) --}}
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h3 class="m-0">Avaliação de Desempenho</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 p-0 bg-transparent">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard', ['sub'=>$sub]) }}">Dashboard</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Ciclos</li>
                </ol>
              </nav>
            </div>

            <a href="{{ route('avd.ciclos.create', ['sub'=>$sub]) }}" class="btn btn-primary">
              Adicionar Ciclo
            </a>
          </div>

          {{-- Filtros --}}
          <form id="formFiltro" class="row g-2 align-items-end mb-3">
            <div class="col-12 col-md-4">
              <label class="form-label">Título</label>
              <input type="text" name="q" class="form-control" placeholder="Buscar pelo título...">
            </div>

            <div class="col-12 col-md-3">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="">Todos</option>
                <option value="aguardando">Aguardando</option>
                <option value="iniciada">Iniciada</option>
                <option value="encerrada">Encerrada</option>
                <option value="em_consenso">Em consenso</option>
              </select>
            </div>

            <div class="col-12 col-md-3">
              <label class="form-label">Unidade (Filial ID)</label>
              <input type="number" name="filial_id" class="form-control" placeholder="Ex.: 1">
              <small class="text-muted">Filtro simples por ID (por enquanto).</small>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
              <button type="submit" class="btn btn-primary w-100">Filtrar</button>
              <button type="button" id="btnLimpar" class="btn btn-outline-secondary w-100">Limpar</button>
            </div>
          </form>

          {{-- Grid --}}
          <div id="gridAVD">
            @include('avd.desempenho.partials.table', [
              'rows' => collect(),
              'participantes' => [],
              'respondentes' => [],
              'sub' => $sub
            ])
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

<script>
(function(){
  const grid = document.getElementById('gridAVD');
  const form = document.getElementById('formFiltro');
  const btnLimpar = document.getElementById('btnLimpar');

  async function loadGrid(){
    const params = new URLSearchParams(new FormData(form));
    const url = "{{ route('avd.ciclos.grid', ['sub'=>$sub]) }}" + "?" + params.toString();
    grid.innerHTML = '<div class="text-muted py-3">Carregando...</div>';

    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
    grid.innerHTML = await res.text();

    if (window.feather) feather.replace();
  }

  form.addEventListener('submit', function(e){
    e.preventDefault();
    loadGrid();
  });

  btnLimpar.addEventListener('click', function(){
    form.reset();
    loadGrid();
  });

  // primeira carga
  loadGrid();
})();
</script>
@endsection
