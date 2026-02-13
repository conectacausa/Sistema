@extends('layouts.app')

@section('title', $ciclo ? 'Editar Avaliação' : 'Criar Avaliação')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">@yield('title')</h3>
    </div>
    <a href="{{ route('avd.ciclos.index', ['sub'=>request()->route('sub')]) }}"
       class="waves-effect waves-light btn mb-5 bg-gradient-secondary">
      Voltar
    </a>
  </div>
</div>

<section class="content">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      {{ session('success') }}
    </div>
  @endif

  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">

          <form method="POST"
                action="{{ $ciclo
                  ? route('avd.ciclos.update', ['sub'=>request()->route('sub'),'id'=>$ciclo->id])
                  : route('avd.ciclos.store',  ['sub'=>request()->route('sub')]) }}">
            @csrf
            @if($ciclo) @method('PUT') @endif

            <div class="vtabs">
              <ul class="nav nav-tabs tabs-vertical" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" data-bs-toggle="tab" href="#tab-ciclo" role="tab">
                    <i data-feather="lock"></i> Cadastro do Ciclo
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-unidades" role="tab">
                    <i data-feather="users"></i> Unidades
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-colab" role="tab">
                    <i data-feather="user"></i> Colaboradores
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-pilares" role="tab">
                    <i data-feather="users"></i> Pilares
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-perguntas" role="tab">
                    <i data-feather="users"></i> Perguntas
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-auto" role="tab">
                    <i data-feather="lock"></i> Automações
                  </a>
                </li>
              </ul>

              <div class="tab-content">

                {{-- TAB 1 --}}
                <div class="tab-pane active" id="tab-ciclo" role="tabpanel">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Título do ciclo</label>
                      <input class="form-control" name="titulo" value="{{ old('titulo', $ciclo->titulo ?? '') }}" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Início</label>
                      <input type="datetime-local" class="form-control" name="inicio_em"
                        value="{{ old('inicio_em', isset($ciclo->inicio_em) ? \Carbon\Carbon::parse($ciclo->inicio_em)->format('Y-m-d\TH:i') : '') }}">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label">Fim</label>
                      <input type="datetime-local" class="form-control" name="fim_em"
                        value="{{ old('fim_em', isset($ciclo->fim_em) ? \Carbon\Carbon::parse($ciclo->fim_em)->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">Tipo</label>
                      <select class="form-select" name="tipo" required>
                        <option value="180" @selected(old('tipo', $ciclo->tipo ?? '180')=='180')>180°</option>
                        <option value="360" @selected(old('tipo', $ciclo->tipo ?? '')=='360')>360°</option>
                      </select>
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">Divergência</label>
                      <div class="d-flex gap-2">
                        <select class="form-select" name="divergencia_tipo">
                          <option value="percent" @selected(old('divergencia_tipo', $ciclo->divergencia_tipo ?? 'percent')=='percent')>%</option>
                          <option value="pontos" @selected(old('divergencia_tipo', $ciclo->divergencia_tipo ?? '')=='pontos')>Pontos</option>
                        </select>
                        <input class="form-control" name="divergencia_valor"
                               value="{{ old('divergencia_valor', $ciclo->divergencia_valor ?? 0) }}">
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Pesos do cálculo (%)</label>
                      <div class="row g-2">
                        <div class="col-md-4">
                          <input class="form-control" name="peso_auto" placeholder="Auto"
                                 value="{{ old('peso_auto', $ciclo->peso_auto ?? 30) }}">
                        </div>
                        <div class="col-md-4">
                          <input class="form-control" name="peso_gestor" placeholder="Gestor"
                                 value="{{ old('peso_gestor', $ciclo->peso_gestor ?? 70) }}">
                        </div>
                        <div class="col-md-4">
                          <input class="form-control" name="peso_pares" placeholder="Pares"
                                 value="{{ old('peso_pares', $ciclo->peso_pares ?? 0) }}">
                        </div>
                      </div>
                      <small class="text-muted">No 180°, peso_pares normalmente fica 0.</small>
                    </div>

                    <div class="col-md-3">
                      <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="permitir_inicio_manual" value="1"
                          @checked(old('permitir_inicio_manual', $ciclo->permitir_inicio_manual ?? true))>
                        <label class="form-check-label">Permitir iniciar manualmente</label>
                      </div>
                      <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="permitir_reabrir" value="1"
                          @checked(old('permitir_reabrir', $ciclo->permitir_reabrir ?? false))>
                        <label class="form-check-label">Permitir reabrir após encerrado</label>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">Status</label>
                      <input class="form-control" value="{{ $ciclo->status ?? 'aguardando' }}" disabled>
                    </div>
                  </div>
                </div>

                {{-- TAB 2 --}}
                <div class="tab-pane" id="tab-unidades" role="tabpanel">
                  @if(!$ciclo)
                    <div class="alert alert-info">Salve o ciclo primeiro para vincular unidades.</div>
                  @else
                    {{-- Implementaremos: botão + modal + tabela vinculadas --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h4 class="m-0">Unidades vinculadas</h4>
                      <button type="button" class="btn btn-success">Vincular unidade</button>
                    </div>

                    <div class="table-responsive">
                      <table class="table">
                        <thead class="bg-primary">
                          <tr>
                            <th>Nome fantasia</th>
                            <th>CNPJ</th>
                            <th style="width:120px;">Ações</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse(($unidadesVinculadas ?? []) as $u)
                            <tr>
                              <td>{{ $u->nome_fantasia }}</td>
                              <td>{{ $u->cnpj }}</td>
                              <td>
                                <button class="btn btn-danger btn-sm">
                                  <i data-feather="trash-2"></i>
                                </button>
                              </td>
                            </tr>
                          @empty
                            <tr><td colspan="3" class="text-center">Nenhuma unidade vinculada.</td></tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  @endif
                </div>

                {{-- TAB 3 --}}
                <div class="tab-pane" id="tab-colab" role="tabpanel">
                  @if(!$ciclo)
                    <div class="alert alert-info">Salve o ciclo primeiro para vincular colaboradores.</div>
                  @else
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h4 class="m-0">Participantes</h4>
                      <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success">Vincular individual</button>
                        <button type="button" class="btn btn-primary">Vincular em lote</button>
                      </div>
                    </div>

                    <div class="table-responsive">
                      <table class="table">
                        <thead class="bg-primary">
                          <tr>
                            <th>Nome</th>
                            <th>Filial</th>
                            <th>Nota auto</th>
                            <th>Nota pares</th>
                            <th>Nota gestor</th>
                            <th>Nota final</th>
                            <th>Status</th>
                            <th style="width:160px;">Ações</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse(($participantes ?? []) as $p)
                            <tr>
                              <td>{{ $p->colaborador_nome }}</td>
                              <td>{{ $p->filial_nome ?? '-' }}</td>
                              <td>{{ $p->nota_auto ?? '-' }}</td>
                              <td>{{ $p->nota_pares ?? '-' }}</td>
                              <td>{{ $p->nota_gestor ?? '-' }}</td>
                              <td>{{ $p->nota_final ?? '-' }}</td>
                              <td>{{ $p->status }}</td>
                              <td>
                                <button class="btn btn-sm btn-outline-primary" title="Editar">
                                  <i data-feather="edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" title="Remover">
                                  <i data-feather="trash-2"></i>
                                </button>
                              </td>
                            </tr>
                          @empty
                            <tr><td colspan="8" class="text-center">Nenhum colaborador vinculado.</td></tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  @endif
                </div>

                {{-- TAB 4 --}}
                <div class="tab-pane" id="tab-pilares" role="tabpanel">
                  @if(!$ciclo)
                    <div class="alert alert-info">Salve o ciclo primeiro para cadastrar pilares.</div>
                  @else
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h4 class="m-0">Pilares</h4>
                      <button type="button" class="btn btn-success">Adicionar pilar</button>
                    </div>
                    <small class="text-muted">Regra: soma dos pilares = 100%</small>
                    <hr>
                    {{-- tabela de pilares --}}
                  @endif
                </div>

                {{-- TAB 5 --}}
                <div class="tab-pane" id="tab-perguntas" role="tabpanel">
                  @if(!$ciclo)
                    <div class="alert alert-info">Salve o ciclo primeiro para cadastrar perguntas.</div>
                  @else
                    <div class="alert alert-warning">
                      Estrutura por pilar. Regra: soma das perguntas do pilar = 100%.
                    </div>
                    {{-- aqui entra UI por pilar --}}
                  @endif
                </div>

                {{-- TAB 6 --}}
                <div class="tab-pane" id="tab-auto" role="tabpanel">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">Mensagem autoavaliação</label>
                      <textarea class="form-control" rows="4" name="msg_auto">{{ old('msg_auto', $ciclo->msg_auto ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Mensagem gestor</label>
                      <textarea class="form-control" rows="4" name="msg_gestor">{{ old('msg_gestor', $ciclo->msg_gestor ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Mensagem pares</label>
                      <textarea class="form-control" rows="4" name="msg_pares">{{ old('msg_pares', $ciclo->msg_pares ?? '') }}</textarea>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label">Mensagem consenso</label>
                      <textarea class="form-control" rows="4" name="msg_consenso">{{ old('msg_consenso', $ciclo->msg_consenso ?? '') }}</textarea>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Mensagem lembrete</label>
                      <textarea class="form-control" rows="3" name="msg_lembrete">{{ old('msg_lembrete', $ciclo->msg_lembrete ?? '') }}</textarea>
                      <small class="text-muted">Shortcodes: {nome}, {empresa}, {link}, {data_limite}</small>
                    </div>

                    <div class="col-md-3">
                      <label class="form-label">Lembrete a cada (dias)</label>
                      <input class="form-control" name="lembrete_cada_dias" value="{{ old('lembrete_cada_dias', $ciclo->lembrete_cada_dias ?? 0) }}">
                    </div>

                    <div class="col-md-3">
                      <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="parar_lembrete_apos_responder" value="1"
                          @checked(old('parar_lembrete_apos_responder', $ciclo->parar_lembrete_apos_responder ?? true))>
                        <label class="form-check-label">Parar após responder</label>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            {{-- ✅ BOTÃO SALVAR FORA DAS ABAS --}}
            <div class="mt-4 d-flex justify-content-end">
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
<script>
(function(){ if(window.feather) feather.replace(); })();
</script>
@endpush
