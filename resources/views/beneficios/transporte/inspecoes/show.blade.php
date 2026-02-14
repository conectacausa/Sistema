@extends('layouts.app')

@section('title', 'Transporte - Visualizar Inspeção')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Inspeção #{{ $inspecao->id }}</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    <div class="row">
      <div class="col-md-3"><b>Data:</b> {{ $inspecao->data_inspecao }}</div>
      <div class="col-md-3"><b>Status:</b> {{ $inspecao->status }}</div>
      <div class="col-md-3"><b>Veículo:</b> {{ $inspecao->veiculo->placa ?? '-' }}</div>
      <div class="col-md-3"><b>Linha:</b> {{ $inspecao->linha->nome ?? '-' }}</div>
    </div>

    <hr>
    <h4>Checklist</h4>
    <pre class="mb-0">{{ json_encode($inspecao->checklist_json ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>

  </div></div></div></div>
</section>
@endsection
