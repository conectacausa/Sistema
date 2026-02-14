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

              <div class="col-md-4">
                <div class="form-group">
                  <label class="form-label">Filial</label>
                  <div class="input-group">
                    {{-- linha pode ter mais de uma filial: multiselect --}}
                    <select class="form-select" name="filiais[]" multiple id="filiais_select">
                      @foreach(($filiais ?? []) as $f)
                        @php
                          $label = $f->nome_fantasia ?? $f->nome ?? ('Filial #'.$f->id);
                          $selected = in_array((int)$f->id, array_map('intval', (array)old('filiais', [])), true);
                        @endphp
                        <option value="{{ $f->id }}" {{ $selected ? 'selected' : '' }}>
                          {{ $label }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <small class="text-muted">Selecione uma ou mais filiais vinculadas à linha.</small>
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
                    {{-- Select2 live search --}}
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
                    {{-- Select2 live search --}}
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

            {{-- Botão salvar fora "das abas" (aqui não tem abas, mas fica no final do box) --}}
            <div class="mt-3 d-flex gap-2">
              <button type="submit" class="btn btn-primary">
                Salvar
              </button>

              <a href="{{ route('beneficios.transporte.linhas.index', ['sub' => $sub]) }}"
                 class="btn btn-secondary">
                Voltar
              </a>
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
<script src="{{ asset('assets/vendor_components/select2/dist/js/select2.full.js') }}"></script>

{{-- Toastr (você citou esse caminho) --}}
<script src="{{ asset('assets/js/pages/toastr.js') }}"></script>

<script>
(function () {
  if (window.feather) feather.replace();

  // Select2 (live search)
  function initSelect2(el) {
    if (!el || !window.jQuery || !jQuery.fn.select2) return;
    $(el).select2({
      width: '100%',
      placeholder: 'Selecione...',
      allowClear: true
    });
  }

  initSelect2('#veiculo_id');
  initSelect2('#motorista_id');

  // Filiais multi-select (pode também usar select2 para facilitar)
  initSelect2('#filiais_select');

  // Toastr sessions
  function toast(type, msg) {
    if (window.toastr) {
      toastr.options = { closeButton:true, progressBar:true, timeOut:4000 };
      toastr[type || 'info'](msg);
    }
  }

  @if(session('success'))
    toast('success', @json(session('success')));
  @endif
  @if(session('error'))
    toast('error', @json(session('error')));
  @endif

  // Erros de validação (Laravel)
  @if($errors && $errors->any())
    toast('error', 'Revise os campos do formulário. Existem erros de validação.');
  @endif
})();
</script>
@endpush
