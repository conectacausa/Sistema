@extends('layouts.app')

@section('title', 'Transporte - Exportação Folha')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Exportação para Folha</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">
    <p class="text-muted mb-10">
      Aqui vamos gerar a planilha com matrícula, CPF, valor carregado e 6% do salário (conforme regra).
      A exportação final (XLSX/CSV) a gente fecha quando você confirmar os nomes dos campos de salário/matrícula/CPF na tabela <b>usuarios</b>.
    </p>

    <form method="GET" action="{{ route('beneficios.transporte.relatorios.exportacao_folha', ['sub'=>$sub]) }}">
      <div class="row">
        <div class="col-md-4">
          <label class="form-label">Data início</label>
          <input type="date" name="dt_ini" class="form-control" value="{{ $dtIni ?? '' }}">
        </div>
        <div class="col-md-4">
          <label class="form-label">Data fim</label>
          <input type="date" name="dt_fim" class="form-control" value="{{ $dtFim ?? '' }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button class="btn btn-primary w-100" type="submit">Preparar</button>
        </div>
      </div>
    </form>

  </div></div></div></div>
</section>
@endsection
