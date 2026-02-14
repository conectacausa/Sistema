@extends('layouts.app')

@section('title', 'Transporte - Editar Linha')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">Editar Linha</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard', ['sub'=>$sub]) }}"><i class="mdi mdi-home-outline"></i></a></li>
        <li class="breadcrumb-item">Benefícios</li>
        <li class="breadcrumb-item"><a href="{{ route('beneficios.transporte.linhas.index', ['sub'=>$sub]) }}">Transporte</a></li>
        <li class="breadcrumb-item active">Editar</li>
      </ol>
    </div>
    <a class="btn btn-info" href="{{ route('beneficios.transporte.linhas.operacao', ['sub'=>$sub,'id'=>$linha->id]) }}">Operação</a>
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

          <form method="POST" action="{{ route('beneficios.transporte.linhas.update', ['sub'=>$sub,'id'=>$linha->id]) }}">
            @csrf
            @method('PUT')

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Nome</label>
                  <input type="text" name="nome" class="form-control" value="{{ old('nome', $linha->nome) }}" required>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label class="form-label">Tipo</label>
                  <select name="tipo_linha" class="form-select" required>
                    <option value="fretada" {{ old('tipo_linha',$linha->tipo_linha)=='fretada'?'selected':'' }}>Fretada</option>
                    <option value="publica" {{ old('tipo_linha',$linha->tipo_linha)=='publica'?'selected':'' }}>Pública</option>
                  </select>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label class="form-label">Controle</label>
                  <select name="controle_acesso" class="form-select" required>
                    <option value="cartao" {{ old('controle_acesso',$linha->controle_acesso)=='cartao'?'selected':'' }}>Cartão</option>
                    <option value="ticket" {{ old('controle_acesso',$linha->controle_acesso)=='ticket'?'selected':'' }}>Ticket</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Motorista</label>
                  <select name="motorista_id" class="form-select" required>
                    @foreach($motoristas as $m)
                      <option value="{{ $m->id }}" {{ old('motorista_id',$linha->motorista_id)==$m->id?'selected':'' }}>{{ $m->nome }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Veículo</label>
                  <select name="veiculo_id" class="form-select" required>
                    @foreach($veiculos as $v)
                      <option value="{{ $v->id }}" {{ old('veiculo_id',$linha->veiculo_id)==$v->id?'selected':'' }}>
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
                    <option value="ativo" {{ old('status',$linha->status)=='ativo'?'selected':'' }}>Ativo</option>
                    <option value="inativo" {{ old('status',$linha->status)=='inativo'?'selected':'' }}>Inativo</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label class="form-label">Filiais</label>
                  <select name="filiais[]" class="form-select" multiple required>
                    @foreach($filiais as $f)
                      <option value="{{ $f->id }}" {{ in_array($f->id, old('filiais',$filiaisSelecionadas ?? [])) ? 'selected' : '' }}>
                        {{ $f->nome ?? $f->nome_fantasia ?? ('Filial #'.$f->id) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="form-group">
                  <label class="form-label">Observações</label>
                  <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes',$linha->observacoes) }}</textarea>
                </div>
              </div>
            </div>

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
