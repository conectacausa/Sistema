@extends('layouts.app')

@section('title', 'Transporte - Entregas de Ticket')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Entregas de Ticket</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('beneficios.transporte.tickets.entregas.store', ['sub'=>$sub]) }}">
      @csrf
      <div class="row">
        <div class="col-md-5">
          <label class="form-label">Colaborador</label>
          <select name="usuario_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($usuarios as $u)
              <option value="{{ $u->id }}">{{ $u->nome_completo }}{{ $u->matricula ? ' - Mat: '.$u->matricula : '' }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Bloco</label>
          <select name="bloco_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($blocos as $b)
              <option value="{{ $b->id }}">{{ $b->codigo_bloco ?? ('Bloco #'.$b->id) }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Data</label>
          <input type="date" name="data_entrega" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Qtd</label>
          <input name="quantidade_entregue" class="form-control" required>
        </div>
      </div>

      <div class="row mt-10">
        <div class="col-12">
          <label class="form-label">Observação</label>
          <input name="observacao" class="form-control">
        </div>
      </div>

      <div class="d-flex justify-content-end mt-10">
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>

    <hr>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="bg-primary">
          <tr>
            <th>Data</th>
            <th>Colaborador</th>
            <th>Bloco</th>
            <th>Qtd</th>
          </tr>
        </thead>
        <tbody>
          @forelse($entregas as $e)
            <tr>
              <td>{{ $e->data_entrega }}</td>
              <td>{{ $e->usuario->nome_completo ?? $e->usuario_id }}</td>
              <td>{{ $e->bloco->codigo_bloco ?? $e->bloco_id }}</td>
              <td>{{ $e->quantidade_entregue }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted">Nenhuma entrega.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-15">{{ $entregas->links() }}</div>

  </div></div></div></div>
</section>
@endsection
