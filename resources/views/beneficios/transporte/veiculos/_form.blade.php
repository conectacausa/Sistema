@php $v = $veiculo; @endphp

<div class="row">
  <div class="col-md-3">
    <label class="form-label">Tipo</label>
    <input name="tipo" class="form-control" value="{{ old('tipo', $v->tipo ?? 'onibus') }}" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Placa</label>
    <input name="placa" class="form-control" value="{{ old('placa', $v->placa ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Modelo</label>
    <input name="modelo" class="form-control" value="{{ old('modelo', $v->modelo ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Marca</label>
    <input name="marca" class="form-control" value="{{ old('marca', $v->marca ?? '') }}">
  </div>
</div>

<div class="row">
  <div class="col-md-3">
    <label class="form-label">Ano</label>
    <input name="ano" class="form-control" value="{{ old('ano', $v->ano ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Capacidade</label>
    <input name="capacidade_passageiros" class="form-control" value="{{ old('capacidade_passageiros', $v->capacidade_passageiros ?? '') }}">
  </div>
  <div class="col-md-3">
    <label class="form-label">Inspeção (meses)</label>
    <input name="inspecao_cada_meses" class="form-control" value="{{ old('inspecao_cada_meses', $v->inspecao_cada_meses ?? 6) }}" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
      @php $st = old('status', $v->status ?? 'ativo'); @endphp
      <option value="ativo" {{ $st=='ativo'?'selected':'' }}>Ativo</option>
      <option value="inativo" {{ $st=='inativo'?'selected':'' }}>Inativo</option>
      <option value="manutencao" {{ $st=='manutencao'?'selected':'' }}>Manutenção</option>
    </select>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <label class="form-label">Renavam</label>
    <input name="renavam" class="form-control" value="{{ old('renavam', $v->renavam ?? '') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Chassi</label>
    <input name="chassi" class="form-control" value="{{ old('chassi', $v->chassi ?? '') }}">
  </div>
  <div class="col-md-4">
    <label class="form-label">Observações</label>
    <input name="observacoes" class="form-control" value="{{ old('observacoes', $v->observacoes ?? '') }}">
  </div>
</div>
