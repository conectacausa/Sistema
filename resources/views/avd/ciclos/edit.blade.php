{{-- resources/views/avd/ciclos/edit.blade.php --}}
@extends('layouts.app')

@section('title', isset($ciclo) ? 'Editar Ciclo de Avaliação' : 'Criar Ciclo de Avaliação')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

    {{-- ESQUERDA: Título + Breadcrumb (1 linha) --}}
    <div class="d-flex align-items-center flex-wrap gap-10">
      <h3 class="m-0">@yield('title')</h3>

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb m-0 p-0">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard', ['sub' => request()->route('sub')]) }}">
              <i class="mdi mdi-home-outline"></i>
            </a>
          </li>
          <li class="breadcrumb-item">AVD</li>
          <li class="breadcrumb-item">
            <a href="{{ route('avd.ciclos.index', ['sub' => request()->route('sub')]) }}">Avaliação de Desempenho</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
        </ol>
      </nav>
    </div>

  </div>
</div>

<section class="content">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      {{ session('success') }}
    </div>
  @endif

  @php
    $isEdit = !empty($ciclo) && !empty($ciclo->id);
    $sub = request()->route('sub');

    $tipo = $ciclo->tipo ?? '180';
    $pesoAuto = old('peso_auto', $ciclo->peso_auto ?? 30);
    $pesoGestor = old('peso_gestor', $ciclo->peso_gestor ?? 70);
    $pesoPares = old('peso_pares', $ciclo->peso_pares ?? 0);

    if ($tipo === '180') {
      $pesoPares = 0;
    }
  @endphp

  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">
          <form method="POST"
                action="{{ $isEdit ? route('avd.ciclos.update', ['sub'=>$sub,'id'=>$ciclo->id]) : route('avd.ciclos.store', ['sub'=>$sub]) }}">
            @csrf
            @if($isEdit)
              @method('PUT')
            @endif

            <div class="vtabs">
              <ul class="nav nav-tabs tabs-vertical" role="tablist">

                {{-- 1) Renomeada: Cadastro do Ciclo -> Dados --}}
                <li class="nav-item">
                  <a class="nav-link active" data-bs-toggle="tab" href="#tab-ciclo" role="tab">
                    <i data-feather="file-text"></i> Dados
                  </a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-unidades" role="tab">
                    <i data-feather="map-pin"></i> Unidades
                  </a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-colab" role="tab">
                    <i data-feather="users"></i> Colaboradores
                  </a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-pilares" role="tab">
                    <i data-feather="layers"></i> Pilares
                  </a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-perguntas" role="tab">
                    <i data-feather="help-circle"></i> Perguntas
                  </a>
                </li>

                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-automacoes" role="tab">
                    <i data-feather="settings"></i> Automações
                  </a>
                </li>
              </ul>

              <div class="tab-content">

                {{-- TAB 1: Dados --}}
                <div class="tab-pane active" id="tab-ciclo" role="tabpanel">
                  <div class="row g-3">

                    <div class="col-12">
                      <label class="form-label">Título do ciclo</label>
                      <input type="text"
                             name="titulo"
                             class="form-control"
                             value="{{ old('titulo', $ciclo->titulo ?? '') }}"
                             required>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Data/hora início</label>
                      <input type="datetime-local"
                             name="inicio_em"
                             class="form-control"
                             value="{{ old('inicio_em', !empty($ciclo->inicio_em) ? \Carbon\Carbon::parse($ciclo->inicio_em)->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Data/hora fim</label>
                      <input type="datetime-local"
                             name="fim_em"
                             class="form-control"
                             value="{{ old('fim_em', !empty($ciclo->fim_em) ? \Carbon\Carbon::parse($ciclo->fim_em)->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Tipo</label>
                      <select name="tipo" id="avd-tipo" class="form-select">
                        <option value="180" @selected(old('tipo', $ciclo->tipo ?? '180') === '180')>180°</option>
                        <option value="360" @selected(old('tipo', $ciclo->tipo ?? '180') === '360')>360°</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Margem de divergência (tipo)</label>
                      <select name="divergencia_tipo" class="form-select">
                        <option value="percent" @selected(old('divergencia_tipo', $ciclo->divergencia_tipo ?? 'percent') === 'percent')>%</option>
                        <option value="pontos" @selected(old('divergencia_tipo', $ciclo->divergencia_tipo ?? 'percent') === 'pontos')>Pontos</option>
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Margem de divergência (valor)</label>
                      <input type="number"
                             step="0.01"
                             name="divergencia_valor"
                             class="form-control"
                             value="{{ old('divergencia_valor', $ciclo->divergencia_valor ?? 0) }}">
                    </div>

                    <div class="col-md-4">
                      <div class="form-check mt-4">
                        <input class="form-check-input"
                               type="checkbox"
                               name="permitir_inicio_manual"
                               id="permitir_inicio_manual"
                               value="1"
                               @checked(old('permitir_inicio_manual', (bool)($ciclo->permitir_inicio_manual ?? true)))>
                        <label class="form-check-label" for="permitir_inicio_manual">
                          Permitir iniciar manualmente?
                        </label>
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-check mt-4">
                        <input class="form-check-input"
                               type="checkbox"
                               name="permitir_reabrir"
                               id="permitir_reabrir"
                               value="1"
                               @checked(old('permitir_reabrir', (bool)($ciclo->permitir_reabrir ?? false)))>
                        <label class="form-check-label" for="permitir_reabrir">
                          Permitir reabrir após encerrado?
                        </label>
                      </div>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Status</label>
                      <input type="text" class="form-control" value="{{ $ciclo->status ?? 'aguardando' }}" disabled>
                      <small class="text-muted">Status é automático (aguardando/iniciada/encerrada/em consenso).</small>
                    </div>

                    <div class="col-12"><hr></div>

                    {{-- Pesos --}}
                    <div class="col-12">
                      <h5 class="mb-2">Pesos do cálculo</h5>
                      <small class="text-muted d-block mb-2">A soma deve ser 100. Em 180°, Pares fica 0 e bloqueado.</small>
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Autoavaliação (%)</label>
                      <input type="number" step="0.01" name="peso_auto" id="peso_auto" class="form-control" value="{{ $pesoAuto }}">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Gestor (%)</label>
                      <input type="number" step="0.01" name="peso_gestor" id="peso_gestor" class="form-control" value="{{ $pesoGestor }}">
                    </div>

                    <div class="col-md-4">
                      <label class="form-label">Pares (%)</label>
                      <input type="number"
                             step="0.01"
                             name="peso_pares"
                             id="peso_pares"
                             class="form-control"
                             value="{{ $pesoPares }}"
                             @disabled($tipo === '180')>
                    </div>

                    <div class="col-12">
                      <div id="peso-alerta" class="alert alert-warning d-none mb-0">
                        A soma dos pesos precisa ser <strong>100</strong>. Ajuste os campos.
                      </div>
                    </div>

                  </div>
                </div>

                {{-- TAB 2: Unidades --}}
                <div class="tab-pane" id="tab-unidades" role="tabpanel">
                  @if(!$isEdit)
                    <div class="alert alert-info">Salve o ciclo primeiro para vincular unidades.</div>
                  @else
                    <div id="avd-tab-unidades-wrapper">
                      @include('avd.ciclos.partials.tab_unidades')
                    </div>
                  @endif
                </div>

                {{-- TAB 3: Colaboradores --}}
                <div class="tab-pane" id="tab-colab" role="tabpanel">
                  @if(!$isEdit)
                    <div class="alert alert-info">Salve o ciclo primeiro para vincular colaboradores.</div>
                  @else
                    <div id="avd-tab-participantes-wrapper">
                      @include('avd.ciclos.partials.tab_participantes')
                    </div>
                  @endif
                </div>

                {{-- TAB 4: Pilares (placeholder) --}}
                <div class="tab-pane" id="tab-pilares" role="tabpanel">
                  <div class="alert alert-info mb-0">
                    Esta aba será implementada no próximo passo (pilares + regra soma = 100%).
                  </div>
                </div>

                {{-- TAB 5: Perguntas (placeholder) --}}
                <div class="tab-pane" id="tab-perguntas" role="tabpanel">
                  <div class="alert alert-info mb-0">
                    Esta aba será implementada no próximo passo (perguntas por pilar + pesos).
                  </div>
                </div>

                {{-- TAB 6: Automações (placeholder) --}}
                <div class="tab-pane" id="tab-automacoes" role="tabpanel">
                  <div class="alert alert-info mb-0">
                    Esta aba será implementada no próximo passo (mensagens WhatsApp + lembretes).
                  </div>
                </div>

              </div>
            </div>

            {{-- Salvar fora das abas --}}
            <div class="d-flex justify-content-end mt-4">
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
(function(){
  const tipoEl = document.getElementById('avd-tipo');
  const pesoParesEl = document.getElementById('peso_pares');
  const pesoAutoEl = document.getElementById('peso_auto');
  const pesoGestorEl = document.getElementById('peso_gestor');
  const alerta = document.getElementById('peso-alerta');

  function renderFeather(){ if(window.feather) feather.replace(); }

  function validarSomaPesos(){
    const a = parseFloat(pesoAutoEl?.value || '0') || 0;
    const g = parseFloat(pesoGestorEl?.value || '0') || 0;
    const p = (pesoParesEl && !pesoParesEl.disabled) ? (parseFloat(pesoParesEl.value || '0') || 0) : 0;

    const soma = (a + g + p);
    if(alerta){
      if(Math.abs(soma - 100) > 0.001) alerta.classList.remove('d-none');
      else alerta.classList.add('d-none');
    }
  }

  // Regra: 180 => pares=0 e desabilitado
  function aplicarRegraTipo(){
    const tipo = (tipoEl?.value || '180');
    if(tipo === '180'){
      if(pesoParesEl){
        pesoParesEl.value = '0';
        pesoParesEl.setAttribute('disabled','disabled');
      }
    } else {
      if(pesoParesEl){
        pesoParesEl.removeAttribute('disabled');
      }
    }
    validarSomaPesos();
  }

  tipoEl?.addEventListener('change', aplicarRegraTipo);
  [pesoAutoEl, pesoGestorEl, pesoParesEl].forEach(el => el?.addEventListener('input', validarSomaPesos));

  aplicarRegraTipo();
  renderFeather();
})();
</script>
@endpush
