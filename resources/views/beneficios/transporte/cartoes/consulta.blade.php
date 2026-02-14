@extends('layouts.app')

@section('title', 'Transporte - Consulta Cartão')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Consulta de Cartão</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    <form method="GET" action="{{ route('beneficios.transporte.cartoes.consulta', ['sub'=>$sub]) }}">
      <div class="row">
        <div class="col-md-4">
          <label class="form-label">Filial</label>
          <select name="filial_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($filiais as $f)
              <option value="{{ $f->id }}" {{ (int)$filialId === (int)$f->id ? 'selected' : '' }}>
                {{ $f->nome ?? $f->nome_fantasia ?? ('Filial #'.$f->id) }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-md-5">
          <label class="form-label">Número do Cartão</label>
          <input name="numero_cartao" class="form-control" value="{{ $numeroCartao ?? '' }}" required>
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-primary w-100" type="submit">Buscar</button>
        </div>
      </div>
    </form>

    @if($resultado)
      <hr>
      <div class="row">
        <div class="col-md-4"><b>Cartão:</b> {{ $resultado['numero_cartao'] }}</div>
        <div class="col-md-4"><b>Saldo:</b> R$ {{ number_format((float)($resultado['saldo'] ?? 0), 2, ',', '.') }}</div>
        <div class="col-md-4"><b>Data ref:</b> {{ $resultado['data_referencia'] ?? '-' }}</div>
      </div>

      <hr>
      <h4>Vínculo</h4>
      @if($resultado['usuario'])
        <p class="mb-0">
          <b>Colaborador:</b> {{ $resultado['usuario']->nome_completo }}
          {!! $resultado['usuario']->matricula ? ' | <b>Matrícula:</b> '.$resultado['usuario']->matricula : '' !!}
          {!! $resultado['usuario']->cpf ? ' | <b>CPF:</b> '.$resultado['usuario']->cpf : '' !!}
        </p>
      @else
        <p class="text-muted mb-0">Nenhum colaborador ativo vinculado a este cartão nesta filial.</p>
      @endif
    @endif

  </div></div></div></div>
</section>
@endsection
