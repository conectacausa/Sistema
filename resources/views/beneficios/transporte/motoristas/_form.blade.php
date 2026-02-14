@php
  $m = $motorista;
@endphp

<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label class="form-label">Nome</label>
      <input name="nome" class="form-control" value="{{ old('nome', $m->nome ?? '') }}" required>
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      <label class="form-label">CPF</label>
      <input name="cpf" class="form-control" value="{{ old('cpf', $m->cpf ?? '') }}">
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="ativo" {{ old('status', $m->status ?? 'ativo')=='ativo'?'selected':'' }}>Ativo</option>
        <option value="inativo" {{ old('status', $m->status ?? 'ativo')=='inativo'?'selected':'' }}>Inativo</option>
      </select>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-label">CNH Nº</label>
      <input name="cnh_numero" class="form-control" value="{{ old('cnh_numero', $m->cnh_numero ?? '') }}">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-label">Categoria</label>
      <input name="cnh_categoria" class="form-control" value="{{ old('cnh_categoria', $m->cnh_categoria ?? '') }}">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-label">Validade</label>
      <input type="date" name="cnh_validade" class="form-control" value="{{ old('cnh_validade', $m->cnh_validade ?? '') }}">
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-label">Telefone</label>
      <input name="telefone" class="form-control" value="{{ old('telefone', $m->telefone ?? '') }}">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-label">E-mail</label>
      <input name="email" class="form-control" value="{{ old('email', $m->email ?? '') }}">
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-group">
      <label class="form-label">Observações</label>
      <input name="observacoes" class="form-control" value="{{ old('observacoes', $m->observacoes ?? '') }}">
    </div>
  </div>
</div>
