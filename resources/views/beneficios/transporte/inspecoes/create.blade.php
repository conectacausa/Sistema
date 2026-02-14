@extends('layouts.app')

@section('title', 'Transporte - Nova Inspeção')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Nova Inspeção</h3></div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    @if($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('beneficios.transporte.inspecoes.store', ['sub'=>$sub]) }}">
      @csrf

      <div class="row">
        <div class="col-md-4">
          <label class="form-label">Veículo</label>
          <select name="veiculo_id" class="form-select" required>
            <option value="">Selecione</option>
            @foreach($veiculos as $v)
              <option value="{{ $v->id }}">{{ $v->placa ?? '-' }} {{ $v->modelo ? ' - '.$v->modelo : '' }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Linha (opcional)</label>
          <select name="linha_id" class="form-select">
            <option value="">(Sem linha)</option>
            @foreach($linhas as $l)
              <option value="{{ $l->id }}">{{ $l->nome }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Data</label>
          <input type="datetime-local" name="data_inspecao" class="form-control" required>
        </div>
      </div>

      <div class="row mt-10">
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select" required>
            <option value="aprovado">Aprovado</option>
            <option value="reprovado">Reprovado</option>
            <option value="pendente">Pendente</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Validade até (opcional)</label>
          <input type="date" name="validade_ate" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Observações</label>
          <input name="observacoes" class="form-control">
        </div>
      </div>

      <hr>

      {{-- Exemplo de itens (substituir pelo modelo do seu anexo) --}}
      <h4 class="mb-10">Checklist</h4>
      <div class="row">
        <div class="col-md-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checklist_json[pneus_ok]" value="1" id="pneus_ok">
            <label class="form-check-label" for="pneus_ok">Pneus em boas condições</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checklist_json[luzes_ok]" value="1" id="luzes_ok">
            <label class="form-check-label" for="luzes_ok">Luzes funcionando</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checklist_json[extintor_ok]" value="1" id="extintor_ok">
            <label class="form-check-label" for="extintor_ok">Extintor válido</label>
          </div>
        </div>
      </div>

      {{-- BOTÃO SALVAR fora das abas (no final do form) --}}
      <div class="d-flex justify-content-end mt-15">
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>

  </div></div></div></div>
</section>
@endsection

@push('scripts')
<script> if (window.feather) feather.replace(); </script>
@endpush
