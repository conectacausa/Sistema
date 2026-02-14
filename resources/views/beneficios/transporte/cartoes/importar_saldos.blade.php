@extends('layouts.app')

@section('title', 'Transporte - Importar Saldos')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Importar Saldos</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ route('beneficios.transporte.cartoes.importar_saldos', ['sub'=>$sub]) }}">
      @csrf
      <div class="row">
        <div class="col-md-6">
          <label class="form-label">Arquivo (CSV)</label>
          <input type="file" name="arquivo" class="form-control" required>
          <small class="text-muted">Formato: numero_cartao;saldo (ou numero_cartao,saldo)</small>
        </div>
        <div class="col-md-3">
          <label class="form-label">Data referência (opcional)</label>
          <input type="date" name="data_referencia" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-primary w-100" type="submit">Importar</button>
        </div>
      </div>
    </form>

  </div></div></div></div>
</section>
@endsection
