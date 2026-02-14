@extends('layouts.app')

@section('title', 'Transporte - Blocos de Ticket')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Blocos de Ticket</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('beneficios.transporte.tickets.blocos.store', ['sub'=>$sub]) }}">
      @csrf
      <div class="row">
        <div class="col-md-3">
          <label class="form-label">Código do bloco</label>
          <input name="codigo_bloco" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Qtd tickets</label>
          <input name="quantidade_tickets" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Viagens por ticket</label>
          <input name="viagens_por_ticket" class="form-control" value="1" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-primary w-100" type="submit">Salvar</button>
        </div>
      </div>
    </form>

    <hr>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="bg-primary">
          <tr>
            <th>Código</th>
            <th>Qtd</th>
            <th>Viagens/ticket</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($blocos as $b)
            <tr>
              <td>{{ $b->codigo_bloco ?? '-' }}</td>
              <td>{{ $b->quantidade_tickets }}</td>
              <td>{{ $b->viagens_por_ticket }}</td>
              <td>{{ $b->status }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted">Nenhum bloco.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-15">{{ $blocos->links() }}</div>

  </div></div></div></div>
</section>
@endsection
