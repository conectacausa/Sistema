@extends('layouts.app')

@section('title', 'Transporte - Nova Linha')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">Nova Linha</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard', ['sub'=>$sub]) }}"><i class="mdi mdi-home-outline"></i></a></li>
        <li class="breadcrumb-item">Benefícios</li>
        <li class="breadcrumb-item"><a href="{{ route('beneficios.transporte.linhas.index', ['sub'=>$sub]) }}">Transporte</a></li>
        <li class="breadcrumb-item active">Nova Linha</li>
      </ol>
    </div>
  </div>
</div>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">

          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('beneficios.transporte.linhas.store', ['sub'=>$sub]) }}">
            @csrf

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Nome</label>
                  <input type="text" name="nome" class="form-control" value="{{ old('nome') }}" required>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label class="form-label">Tipo</label>
                  <select name="tipo_linha" class="form-select" required>
                    <option value="fretada" {{ old('tipo_linha','fretada')=='fretada'?'selected':'' }}>Fretada</option>
                    <option value="publica" {{ old('tipo_linha')=='publica'?'selected':'' }}>Pública</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label class="form-label">Controle</label>
                  <select name="controle_acesso" class="form-select" required>
                    <option value="cartao" {{ old('controle_acesso','cartao')=='cartao'?'selected':'' }}>Cartão</option>
                    <option value="ticket" {{ old('controle_acesso')=='ticket'?'selected':'' }}>Ticket</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Motorista</label>
                  <select name="motorista_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($motoristas as $m)
                      <option value="{{ $m->id }}" {{ old('motorista_id')==$m->id?'selected':'' }}>{{ $m->nome }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Veículo</label>
                  <select name="veiculo_id" class="form-select" required>
                    <option value="">Selecione</option>
                    @foreach($veiculos as $v)
                      <option value="{{ $v->id }}" {{ old('veiculo_id')==$v->id?'selected':'' }}>
                        {{ $v->placa ?? '-' }} {{ $v->modelo ? ' - '.$v->modelo : '' }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select">
                    <option value="ativo" {{ old('status','ativo')=='ativo'?'selected':'' }}>Ativo</option>
                    <option value="inativo" {{ old('status')=='inativo'?'selected':'' }}>Inativo</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label class="form-label">Filiais (obrigatório)</label>
                  <select name="filiais[]" class="form-select" multiple required>
                    @foreach($filiais as $f)
                      <option value="{{ $f->id }}" {{ collect(old('filiais', []))->contains($f->id) ? 'selected' : '' }}>
                        {{ $f->nome ?? $f->nome_fantasia ?? ('Filial #'.$f->id) }}
                      </option>
                    @endforeach
                  </select>
                  <small class="text-muted">Segure CTRL para selecionar mais de uma filial.</small>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label class="form-label">Observações</label>
                  <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes') }}</textarea>
                </div>
              </div>
            </div>

            {{-- BOTÃO SALVAR fora de abas (aqui não tem abas, mas mantém padrão no final) --}}
            <div class="d-flex justify-content-end mt-10">
              <button type="submit" class="btn btn-primary">Salvar</button>
            </div>

          </form>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  if (window.feather) feather.replace();
</script>
@endpush
