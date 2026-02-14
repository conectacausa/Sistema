@extends('layouts.app')

@section('title', 'Transporte')

@section('content_header')
<div class="d-flex align-items-center">
  <div class="me-auto">
    <h4 class="page-title">Transporte</h4>
    <div class="d-inline-block align-items-center">
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard', ['sub' => $sub]) }}">
              <i class="mdi mdi-home-outline"></i>
            </a>
          </li>
          <li class="breadcrumb-item">Benefícios</li>
          <li class="breadcrumb-item">Transporte</li>
          <li class="breadcrumb-item active" aria-current="page">Nova Linha</li>
        </ol>
      </nav>
    </div>
  </div>
</div>
@endsection

@section('content')
<section class="content">

  <div class="row">
    <div class="col-12">
      <div class="box">

        <div class="box-header with-border">
          <h4 class="box-title">Cadastro de Linha</h4>
        </div>

        <div class="box-body">

          <form method="POST"
                action="{{ route('beneficios.transporte.linhas.store', ['sub' => $sub]) }}"
                id="formLinha">
            @csrf

            <div class="row">
              <div class="col-md-9">
                <div class="form-group">
                  <label class="form-label">Linha</label>
                  <div class="input-group">
                    <input type="text"
                           class="form-control"
                           placeholder="Nome Linha"
                           name="nome"
                           value="{{ old('nome') }}">
                  </div>
                </div>
              </div>

              <div class="col-md-3">
                <div class="form-group">
                  <label class="form-label">Tipo</label>
                  <div class="input-group">
                    <select class="form-select" name="tipo_linha">
                      <option value="fretada" {{ old('tipo_linha','fretada') === 'fretada' ? 'selected' : '' }}>Fretada</option>
                      <option value="publica" {{ old('tipo_linha') === 'publica' ? 'selected' : '' }}>Pública</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Controle</label>
                  <div class="input-group">
                    <select class="form-select" name="controle_acesso">
                      <option value="cartao" {{ old('controle_acesso','cartao') === 'cartao' ? 'selected' : '' }}>Cartão</option>
                      <option value="ticket" {{ old('controle_acesso') === 'ticket' ? 'selected' : '' }}>Ticket</option>
                    </select>
                  </div>
                </div>
              </div>

              {{-- ✅ Filial = dropdown simples --}}
              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Filial</label>
                  <div class="input-group">
                    <select class="form-select" name="filial_id" id="filial_id">
                      <option value="">Selecione...</option>
                      @foreach(($filiais ?? []) as $f)
                        @php $label = $f->nome_fantasia ?? $f->nome ?? ('Filial #'.$f->id); @endphp
                        <option value="{{ $f->id }}" {{ (string)old('filial_id') === (string)$f->id ? 'selected' : '' }}>
                          {{ $label }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Situação</label>
                  <div class="input-group">
                    <select class="form-select" name="status">
                      <option value="ativo" {{ old('status','ativo') === 'ativo' ? 'selected' : '' }}>Ativa</option>
                      <option value="inativo" {{ old('status') === 'inativo' ? 'selected' : '' }}>Inativa</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Veículo</label>
                  <div class="input-group">
                    <select class="form-select" name="veiculo_id" id="veiculo_id">
                      <option value="">Selecione...</option>
                      @foreach(($veiculos ?? []) as $v)
                        @php
                          $label = trim(($v->modelo ?? '').' '.($v->placa ?? ''));
                          if ($label === '') $label = 'Veículo #'.$v->id;
                        @endphp
                        <option value="{{ $v->id }}" {{ (string)old('veiculo_id') === (string)$v->id ? 'selected' : '' }}>
                          {{ $label }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Motorista</label>
                  <div class="input-group">
                    <select class="form-select" name="motorista_id" id="motorista_id">
                      <option value="">Selecione...</option>
                      @foreach(($motoristas ?? []) as $m)
                        <option value="{{ $m->id }}" {{ (string)old('motorista_id') === (string)$m->id ? 'selected' : '' }}>
                          {{ $m->nome ?? ('Motorista #'.$m->id) }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
            </div>

            {{-- ✅ Botões no canto direito --}}
            <div class="mt-4 d-flex justify-content-end gap-2">
              <a href="{{ route('beneficios.transporte.linhas.index', ['sub' => $sub]) }}"
                 class="btn btn-secondary">
                Voltar
              </a>

              <button type="submit" class="btn btn-primary">
                Salvar
              </button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>

</section>
@endsection

@push('scripts')
{{-- Select2 --}}
<link rel="stylesheet" href="{{ asset('assets/vendor_components/select2/dist/css/select2.min.css') }}">
<script src="{{ asset('assets/vendor_components/select2/dist/js/select2.full.min.js') }}"></script>

<style>
  /* Mantém o Select2 com visual de input do template */
  .select2-container { width: 100% !important; }

  .select2-container .select2-selection--single{
    height: calc(2.25rem + 2px) !important;
    border: 1px solid #d7dce3 !important;
    border-radius: .25rem !important;
    background-color: #fff !important;
  }
  .select2-container .select2-selection--single .select2-selection__rendered{
    line-height: calc(2.25rem) !important;
    padding-left: .75rem !important;
    color: #495057 !important;
  }
  .select2-container .select2-selection--single .select2-selection__arrow{
    height: calc(2.25rem + 2px) !important;
    right: 6px !important;
  }
  .select2-dropdown{
    border: 1px solid #d7dce3 !important;
    border-radius: .25rem !important;
  }
  .input-group .select2-container--default{
    flex: 1 1 auto;
  }
</style>

<script>
(function () {
  if (window.feather) feather.replace();

  if (window.jQuery && jQuery.fn.select2) {
    $('#veiculo_id, #motorista_id').select2({
      width: '100%',
      placeholder: 'Selecione...',
      allowClear: true
    });
  }
})();
</script>
@endpush
