@extends('layouts.app')

@section('title', 'Transporte - Importar Custos')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Custos Mensais por Linha</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('beneficios.transporte.linhas.importar_custos', ['sub'=>$sub]) }}">
      @csrf
      <div class="row">
        <div class="col-md-5">
          <label class="form-label">Linha</label>
          <select name="linha_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($linhas as $l)
              <option value="{{ $l->id }}">{{ $l->nome }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Competência</label>
          <input type="date" name="competencia" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Valor total</label>
          <input name="valor_total" class="form-control" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-primary w-100" type="submit">Salvar</button>
        </div>
      </div>

      <div class="row mt-10">
        <div class="col-12">
          <label class="form-label">Observação</label>
          <input name="observacao" class="form-control">
        </div>
      </div>
    </form>

  </div></div></div></div>
</section>
@endsection
