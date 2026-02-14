@extends('layouts.app')

@section('title', 'Transporte - Usos do Cartão')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Usos do Cartão</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    <form method="GET" action="{{ route('beneficios.transporte.cartoes.usos', ['sub'=>$sub]) }}">
      <div class="row">
        <div class="col-md-4">
          <label class="form-label">Cartão</label>
          <input name="numero_cartao" class="form-control" value="{{ $numeroCartao ?? '' }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Data início</label>
          <input type="date" name="dt_ini" class="form-control" value="{{ $dtIni ?? '' }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">Data fim</label>
          <input type="date" name="dt_fim" class="form-control" value="{{ $dtFim ?? '' }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button class="btn btn-primary w-100" type="submit">Filtrar</button>
        </div>
      </div>
    </form>

    <hr>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="bg-primary">
          <tr>
            <th>Data/Hora</th>
            <th>Cartão</th>
            <th>Valor</th>
            <th>Origem</th>
          </tr>
        </thead>
        <tbody>
          @forelse($usos as $u)
            <tr>
              <td>{{ $u->data_hora_uso }}</td>
              <td>{{ $u->numero_cartao }}</td>
              <td>{{ $u->valor !== null ? 'R$ '.number_format((float)$u->valor,2,',','.') : '-' }}</td>
              <td>{{ $u->origem }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted">Nenhum uso encontrado.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-15">{{ $usos->links() }}</div>

  </div></div></div></div>
</section>
@endsection
