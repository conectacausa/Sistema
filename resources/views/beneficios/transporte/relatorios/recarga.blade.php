@extends('layouts.app')

@section('title', 'Transporte - Relatório de Recarga')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Relatório de Recarga</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    <form method="GET" action="{{ route('beneficios.transporte.relatorios.recarga', ['sub'=>$sub]) }}">
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
          <button class="btn btn-primary w-100" type="submit">Gerar</button>
        </div>
      </div>
    </form>

    @if($resultado)
      <hr>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="bg-primary">
            <tr>
              <th>Cartão</th>
              <th>Qtde usos</th>
              <th>Total (se houver valor no log)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($resultado as $r)
              <tr>
                <td>{{ $r->numero_cartao }}</td>
                <td>{{ $r->qtd }}</td>
                <td>R$ {{ number_format((float)$r->total, 2, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

  </div></div></div></div>
</section>
@endsection
