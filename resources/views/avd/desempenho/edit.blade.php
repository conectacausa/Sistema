@extends('layouts.app')

@section('title', $id ? 'Editar Ciclo - AVD' : 'Criar Ciclo - AVD')

@section('content')
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">

          {{-- Header + Breadcrumb (1 linha só) --}}
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h3 class="m-0">{{ $id ? 'Editar Ciclo' : 'Criar Ciclo' }}</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 p-0 bg-transparent">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard', ['sub'=>$sub]) }}">Dashboard</a></li>
                  <li class="breadcrumb-item"><a href="{{ route('avd.ciclos.index', ['sub'=>$sub]) }}">Avaliação de Desempenho</a></li>
                  <li class="breadcrumb-item active" aria-current="page">{{ $id ? 'Editar' : 'Criar' }}</li>
                </ol>
              </nav>
            </div>
          </div>

          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="m-0 ps-3">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
              </ul>
            </div>
          @endif

          @php
            $action = $id
              ? route('avd.ciclos.update', ['sub'=>$sub, 'id'=>$id])
              : route('avd.ciclos.store', ['sub'=>$sub]);

            $tipo = old('tipo', $ciclo->tipo ?? '180');
          @endphp

          <form method="POST" action="{{ $action }}" id="formCiclo">
            @csrf
            @if($id) @method('PUT') @endif

            <div class="vtabs">
              <ul class="nav nav-tabs tabs-vertical" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" data-bs-toggle="tab" href="#tab-dados" role="tab">
                    <i data-feather="file-text"></i> Dados
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-unidades" role="tab">
                    <i data-feather="home"></i> Unidades
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" data-bs-toggle="tab" href="#tab-colaboradores" role="tab">
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
                    <i data-feather="zap"></i> Automações
                  </a>
                </li>
              </ul>

              <div class="tab-content">

                {{-- TAB: DADOS --}}
                <div class="tab-pane active" id="tab-dados" role="tabpanel">
                  <div class="row g-3">
                    <div class="col-12 col-md-6">
                      <label class="form-label">Título do ciclo</label>
                      <input type="text" name="titulo" class="form-control"
                             value="{{ old('titulo', $ciclo->titulo ?? '') }}" required>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Início</label>
                      <input type="datetime-local" name="inicio_em" class="form-control"
                             value="{{ old('inicio_em', isset($ciclo->inicio_em) ? \Carbon\Carbon::parse($ciclo->inicio_em)->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Fim</label>
                      <input type="datetime-local" name="fim_em" class="form-control"
                             value="{{ old('fim_em', isset($ciclo->fim_em) ? \Carbon\Carbon::parse($ciclo->fim_em)->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Tipo</label>
                      <select name="tipo" id="tipoCiclo" class="form-select" required>
                        <option value="180" {{ $tipo==='180'?'selected':'' }}>180°</option>
                        <option value="360" {{ $tipo==='360'?'selected':'' }}>360°</option>
                      </select>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Margem divergência</label>
                      <div class="input-group">
                        <select name="divergencia_tipo" class="form-select" style="max-width:140px;">
                          @php $dt = old('divergencia_tipo', $ciclo->divergencia_tipo ?? 'percent'); @endphp
                          <option value="percent" {{ $dt==='percent'?'selected':'' }}>%</option>
                          <option value="pontos" {{ $dt==='pontos'?'selected':'' }}>pontos</option>
                        </select>
                        <input type="number" step="0.01" name="divergencia_valor" class="form-control"
                               value="{{ old('divergencia_valor', $ciclo->divergencia_valor ?? 0) }}">
                      </div>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Permitir iniciar manualmente?</label>
                      <select name="permitir_inicio_manual" class="form-select">
                        @php $pim = old('permitir_inicio_manual', $ciclo->permitir_inicio_manual ?? 1); @endphp
                        <option value="1" {{ (string)$pim==='1'?'selected':'' }}>Sim</option>
                        <option value="0" {{ (string)$pim==='0'?'selected':'' }}>Não</option>
                      </select>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Permitir reabrir após encerrado?</label>
                      <select name="permitir_reabrir" class="form-select">
                        @php $pr = old('permitir_reabrir', $ciclo->permitir_reabrir ?? 0); @endphp
                        <option value="0" {{ (string)$pr==='0'?'selected':'' }}>Não</option>
                        <option value="1" {{ (string)$pr==='1'?'selected':'' }}>Sim</option>
                      </select>
                    </div>

                    <div class="col-12">
                      <div class="row g-2">
                        <div class="col-12 col-md-4">
                          <label class="form-label">Peso Autoavaliação (%)</label>
                          <input type="number" step="0.01" name="peso_auto" class="form-control"
                                 value="{{ old('peso_auto', $ciclo->peso_auto ?? 30) }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                          <label class="form-label">Peso Gestor (%)</label>
                          <input type="number" step="0.01" name="peso_gestor" class="form-control"
                                 value="{{ old('peso_gestor', $ciclo->peso_gestor ?? 70) }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                          <label class="form-label">Peso Pares (%)</label>
                          <input type="number" step="0.01" name="peso_pares" id="pesoPares" class="form-control"
                                 value="{{ old('peso_pares', $ciclo->peso_pares ?? 0) }}" required>
                          <small class="text-muted">No tipo 180°, este campo fica 0 e desabilitado.</small>
                        </div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label class="form-label">Status</label>
                      <input type="text" class="form-control" value="{{ $ciclo->status ?? 'aguardando' }}" disabled>
                      <small class="text-muted">Status é controlado pelo sistema (iniciar/encerrar).</small>
                    </div>
                  </div>
                </div>

                {{-- TAB: UNIDADES --}}
                <div class="tab-pane" id="tab-unidades" role="tabpanel">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="m-0">Unidades vinculadas</h5>
                    @if($id)
                      <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#modalUnidades">
                        Adicionar
                      </button>
                    @else
                      <span class="text-muted">Salve o ciclo para vincular unidades.</span>
                    @endif
                  </div>

                  <div class="table-responsive" style="width:100%;">
                    <table class="table table-hover align-middle" style="width:100%;">
                      <thead>
                        <tr>
                          <th>Nome fantasia</th>
                          <th>CNPJ</th>
                          <th class="text-end">Ações</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($unidades ?? [] as $u)
                          <tr>
                            <td>{{ $u->nome_fantasia }}</td>
                            <td>{{ $u->cnpj }}</td>
                            <td class="text-end">
                              <button type="button"
                                      class="btn btn-link p-0 text-danger js-del-unidade"
                                      data-filial="{{ $u->filial_id }}"
                                      title="Desvincular">
                                <i data-feather="trash-2"></i>
                              </button>
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="3" class="text-muted py-3">Nenhuma unidade vinculada.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                {{-- TAB: COLABORADORES --}}
                <div class="tab-pane" id="tab-colaboradores" role="tabpanel">
                  <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                    <h5 class="m-0">Colaboradores vinculados</h5>

                    @if($id)
                      <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalColabIndividual">
                          Vincular individual
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalColabLote">
                          Vincular em lote (por filial)
                        </button>
                      </div>
                    @else
                      <span class="text-muted">Salve o ciclo para vincular colaboradores.</span>
                    @endif
                  </div>

                  <div class="table-responsive" style="width:100%;">
                    <table class="table table-hover align-middle" style="width:100%;">
                      <thead>
                        <tr>
                          <th>Nome</th>
                          <th>Filial</th>
                          <th>WhatsApp</th>
                          <th>Nota auto</th>
                          <th>Nota gestor</th>
                          <th>Nota pares</th>
                          <th>Nota final</th>
                          <th>Status</th>
                          <th class="text-end">Ações</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($participantes ?? [] as $p)
                          <tr>
                            <td>{{ $p->colaborador_nome ?? ('#'.$p->colaborador_id) }}</td>
                            <td>{{ $p->filial_nome ?? '-' }}</td>
                            <td>
                              <div class="d-flex gap-2 align-items-center">
                                <input type="text" class="form-control form-control-sm js-whatsapp"
                                       value="{{ $p->whatsapp ?? '' }}"
                                       data-pid="{{ $p->id }}"
                                       style="max-width:180px;">
                                <button type="button" class="btn btn-link p-0 js-save-whatsapp" data-pid="{{ $p->id }}" title="Salvar WhatsApp">
                                  <i data-feather="save"></i>
                                </button>
                              </div>
                            </td>
                            <td>{{ $p->nota_auto ?? '-' }}</td>
                            <td>{{ $p->nota_gestor ?? '-' }}</td>
                            <td>{{ $p->nota_pares ?? '-' }}</td>
                            <td>{{ $p->nota_final ?? '-' }}</td>
                            <td>
                              <span class="badge bg-secondary">{{ ucfirst($p->status ?? 'pendente') }}</span>
                            </td>
                            <td class="text-end">
                              <button type="button" class="btn btn-link p-0 text-danger js-del-part" data-pid="{{ $p->id }}" title="Remover">
                                <i data-feather="trash-2"></i>
                              </button>
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="9" class="text-muted py-3">Nenhum colaborador vinculado.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                </div>

                {{-- TAB: PILARES --}}
                <div class="tab-pane" id="tab-pilares" role="tabpanel">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="m-0">Pilares</h5>
                    @if($id)
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPilar">
                        Adicionar pilar
                      </button>
                    @else
                      <span class="text-muted">Salve o ciclo para cadastrar pilares.</span>
                    @endif
                  </div>

                  <div class="table-responsive" style="width:100%;">
                    <table class="table table-hover align-middle" style="width:100%;">
                      <thead>
                        <tr>
                          <th>Pilar</th>
                          <th>Peso (%)</th>
                          <th>Ordem</th>
                          <th class="text-end">Ações</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($pilares ?? [] as $pl)
                          <tr>
                            <td>{{ $pl->nome }}</td>
                            <td>{{ $pl->peso }}</td>
                            <td>{{ $pl->ordem }}</td>
                            <td class="text-end">
                              <button type="button" class="btn btn-link p-0 me-2 js-edit-pilar"
                                      data-id="{{ $pl->id }}"
                                      data-nome="{{ $pl->nome }}"
                                      data-peso="{{ $pl->peso }}"
                                      data-ordem="{{ $pl->ordem }}"
                                      title="Editar">
                                <i data-feather="edit"></i>
                              </button>
                              <button type="button" class="btn btn-link p-0 text-danger js-del-pilar"
                                      data-id="{{ $pl->id }}" title="Excluir">
                                <i data-feather="trash-2"></i>
                              </button>
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="4" class="text-muted py-3">Nenhum pilar cadastrado.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>

                  <small class="text-muted">Regra: a soma dos pilares deve ser 100% (validação por melhoria futura).</small>
                </div>

                {{-- TAB: PERGUNTAS --}}
                <div class="tab-pane" id="tab-perguntas" role="tabpanel">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="m-0">Perguntas</h5>
                    @if($id)
                      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPergunta">
                        Adicionar pergunta
                      </button>
                    @else
                      <span class="text-muted">Salve o ciclo para cadastrar perguntas.</span>
                    @endif
                  </div>

                  <div class="table-responsive" style="width:100%;">
                    <table class="table table-hover align-middle" style="width:100%;">
                      <thead>
                        <tr>
                          <th>Pilar</th>
                          <th>Pergunta</th>
                          <th>Peso</th>
                          <th>Tipo</th>
                          <th class="text-end">Ações</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($perguntas ?? [] as $pg)
                          <tr>
                            <td>{{ $pg->pilar_nome ?? ('#'.$pg->pilar_id) }}</td>
                            <td>{{ $pg->texto }}</td>
                            <td>{{ $pg->peso }}</td>
                            <td>{{ $pg->tipo_resposta }}</td>
                            <td class="text-end">
                              <button type="button" class="btn btn-link p-0 me-2 js-edit-pergunta"
                                      data-id="{{ $pg->id }}"
                                      data-pilar="{{ $pg->pilar_id }}"
                                      data-texto="{{ e($pg->texto) }}"
                                      data-peso="{{ $pg->peso }}"
                                      data-tipo="{{ $pg->tipo_resposta }}"
                                      data-exige="{{ (int)$pg->exige_justificativa }}"
                                      data-coment="{{ (int)$pg->permite_comentario }}"
                                      title="Editar">
                                <i data-feather="edit"></i>
                              </button>
                              <button type="button" class="btn btn-link p-0 text-danger js-del-pergunta"
                                      data-id="{{ $pg->id }}" title="Excluir">
                                <i data-feather="trash-2"></i>
                              </button>
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="5" class="text-muted py-3">Nenhuma pergunta cadastrada.</td></tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>

                  <small class="text-muted">Regra: o peso das perguntas do pilar deve somar 100% (melhoria futura).</small>
                </div>

                {{-- TAB: AUTOMAÇÕES --}}
                <div class="tab-pane" id="tab-automacoes" role="tabpanel">
                  <div class="row g-3">
                    <div class="col-12 col-md-6">
                      <label class="form-label">Mensagem autoavaliação</label>
                      <textarea name="msg_auto" class="form-control" rows="3">{{ old('msg_auto', $ciclo->msg_auto ?? '') }}</textarea>
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label">Mensagem gestor</label>
                      <textarea name="msg_gestor" class="form-control" rows="3">{{ old('msg_gestor', $ciclo->msg_gestor ?? '') }}</textarea>
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label">Mensagem pares</label>
                      <textarea name="msg_pares" class="form-control" rows="3">{{ old('msg_pares', $ciclo->msg_pares ?? '') }}</textarea>
                    </div>
                    <div class="col-12 col-md-6">
                      <label class="form-label">Mensagem consenso</label>
                      <textarea name="msg_consenso" class="form-control" rows="3">{{ old('msg_consenso', $ciclo->msg_consenso ?? '') }}</textarea>
                    </div>
                    <div class="col-12">
                      <label class="form-label">Mensagem lembrete</label>
                      <textarea name="msg_lembrete" class="form-control" rows="3">{{ old('msg_lembrete', $ciclo->msg_lembrete ?? '') }}</textarea>
                      <small class="text-muted">
                        Shortcodes: {nome} {empresa} {link} {data_limite}
                      </small>
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Lembrar a cada (dias)</label>
                      <input type="number" name="lembrete_cada_dias" class="form-control"
                             value="{{ old('lembrete_cada_dias', $ciclo->lembrete_cada_dias ?? '') }}">
                    </div>

                    <div class="col-12 col-md-3">
                      <label class="form-label">Parar lembrete após responder?</label>
                      @php $pl = old('parar_lembrete_apos_responder', $ciclo->parar_lembrete_apos_responder ?? 1); @endphp
                      <select name="parar_lembrete_apos_responder" class="form-select">
                        <option value="1" {{ (string)$pl==='1'?'selected':'' }}>Sim</option>
                        <option value="0" {{ (string)$pl==='0'?'selected':'' }}>Não</option>
                      </select>
                    </div>
                  </div>
                </div>

              </div>
            </div>

            {{-- Salvar fora das abas --}}
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

{{-- MODAL: Unidades --}}
<div class="modal fade" id="modalUnidades" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vincular unidade</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Selecione</label>
        <select id="unidadeModo" class="form-select mb-2">
          <option value="uma">Uma unidade</option>
          <option value="todas">Todas</option>
        </select>

        <input type="number" id="unidadeFilialId" class="form-control"
               placeholder="Filial ID (ex.: 1)">
        <small class="text-muted">No modo “Todas” o campo acima é ignorado.</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnVincularUnidade">Vincular</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL: Colaborador Individual --}}
<div class="modal fade" id="modalColabIndividual" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vincular colaborador</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Colaborador ID</label>
        <input type="number" id="colabId" class="form-control" placeholder="Ex.: 10">
        <small class="text-muted">Por enquanto vincula por ID (melhoria: buscar por nome/cpf).</small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnVincularColab">Vincular</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL: Colaborador Lote --}}
<div class="modal fade" id="modalColabLote" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vincular em lote (por filial)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Filial ID</label>
        <input type="number" id="loteFilialId" class="form-control" placeholder="Ex.: 1">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnVincularLote">Vincular</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL: Pilar --}}
<div class="modal fade" id="modalPilar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pilar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pilarId" value="0">
        <div class="mb-2">
          <label class="form-label">Nome</label>
          <input type="text" id="pilarNome" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label">Peso (%)</label>
          <input type="number" step="0.01" id="pilarPeso" class="form-control">
        </div>
        <div>
          <label class="form-label">Ordem</label>
          <input type="number" id="pilarOrdem" class="form-control" value="0">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnSalvarPilar">Salvar</button>
      </div>
    </div>
  </div>
</div>

{{-- MODAL: Pergunta --}}
<div class="modal fade" id="modalPergunta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pergunta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="perguntaId" value="0">
        <div class="row g-2">
          <div class="col-12 col-md-4">
            <label class="form-label">Pilar</label>
            <select id="perguntaPilar" class="form-select">
              <option value="">Selecione</option>
              @foreach($pilares ?? [] as $pl)
                <option value="{{ $pl->id }}">{{ $pl->nome }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Peso</label>
            <input type="number" step="0.01" id="perguntaPeso" class="form-control" value="0">
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Tipo resposta</label>
            <select id="perguntaTipo" class="form-select">
              <option value="1_5">1 a 5</option>
              <option value="1_10">1 a 10</option>
              <option value="custom">Personalizada</option>
            </select>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Ordem</label>
            <input type="number" id="perguntaOrdem" class="form-control" value="0">
          </div>
          <div class="col-12">
            <label class="form-label">Texto</label>
            <textarea id="perguntaTexto" class="form-control" rows="3"></textarea>
          </div>
          <div class="col-12 col-md-4">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="perguntaExige">
              <label class="form-check-label" for="perguntaExige">Obrigatório justificar?</label>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="perguntaComent" checked>
              <label class="form-check-label" for="perguntaComent">Permitir comentário?</label>
            </div>
          </div>
        </div>

        <small class="text-muted d-block mt-2">
          Para “Personalizada”, as opções/pesos ficam como melhoria futura (por enquanto salva o tipo).
        </small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnSalvarPergunta">Salvar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const tipoCiclo = document.getElementById('tipoCiclo');
  const pesoPares = document.getElementById('pesoPares');

  function aplicarRegraTipo(){
    if (tipoCiclo.value === '180') {
      pesoPares.value = 0;
      pesoPares.setAttribute('disabled', 'disabled');
    } else {
      pesoPares.removeAttribute('disabled');
    }
  }
  tipoCiclo.addEventListener('change', aplicarRegraTipo);
  aplicarRegraTipo();

  // Helpers
  async function postJson(url, data, method='POST'){
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(data)
    });
    if (!res.ok) throw new Error(await res.text());
    return res.json();
  }

  // UNIDADES
  const btnVincularUnidade = document.getElementById('btnVincularUnidade');
  if (btnVincularUnidade) {
    btnVincularUnidade.addEventListener('click', async () => {
      const modo = document.getElementById('unidadeModo').value;
      const filialId = parseInt(document.getElementById('unidadeFilialId').value || '0', 10);

      try{
        await postJson("{{ $id ? route('avd.ciclos.unidades.vincular', ['sub'=>$sub, 'id'=>$id]) : '#' }}", {
          modo, filial_id: filialId
        });
        location.reload();
      } catch(e){
        alert('Erro ao vincular unidade.');
      }
    });
  }

  document.querySelectorAll('.js-del-unidade').forEach(btn => {
    btn.addEventListener('click', async () => {
      const filial = btn.getAttribute('data-filial');
      if (!confirm('Desvincular unidade?')) return;
      try{
        await fetch("{{ $id ? url('') : '' }}{{ $id ? route('avd.ciclos.unidades.desvincular', ['sub'=>$sub,'id'=>$id,'filial_id'=>0]) : '' }}".replace('/0', '/' + filial), {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        location.reload();
      } catch(e){
        alert('Erro ao desvincular.');
      }
    });
  });

  // COLABORADORES
  const btnVincularColab = document.getElementById('btnVincularColab');
  if (btnVincularColab) {
    btnVincularColab.addEventListener('click', async () => {
      const colaboradorId = parseInt(document.getElementById('colabId').value || '0', 10);
      try{
        await postJson("{{ $id ? route('avd.ciclos.participantes.vincular', ['sub'=>$sub, 'id'=>$id]) : '#' }}", {
          modo: 'individual',
          colaborador_id: colaboradorId
        });
        location.reload();
      } catch(e){
        alert('Erro ao vincular colaborador.');
      }
    });
  }

  const btnVincularLote = document.getElementById('btnVincularLote');
  if (btnVincularLote) {
    btnVincularLote.addEventListener('click', async () => {
      const filialId = parseInt(document.getElementById('loteFilialId').value || '0', 10);
      try{
        await postJson("{{ $id ? route('avd.ciclos.participantes.vincular', ['sub'=>$sub, 'id'=>$id]) : '#' }}", {
          modo: 'lote_filial',
          filial_id: filialId
        });
        location.reload();
      } catch(e){
        alert('Erro ao vincular lote.');
      }
    });
  }

  document.querySelectorAll('.js-del-part').forEach(btn => {
    btn.addEventListener('click', async () => {
      const pid = btn.getAttribute('data-pid');
      if (!confirm('Remover colaborador do ciclo?')) return;
      try{
        await fetch("{{ $id ? route('avd.ciclos.participantes.remover', ['sub'=>$sub,'id'=>$id,'pid'=>0]) : '' }}".replace('/0', '/' + pid), {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        location.reload();
      } catch(e){
        alert('Erro ao remover.');
      }
    });
  });

  document.querySelectorAll('.js-save-whatsapp').forEach(btn => {
    btn.addEventListener('click', async () => {
      const pid = btn.getAttribute('data-pid');
      const input = document.querySelector('.js-whatsapp[data-pid="'+pid+'"]');
      const whatsapp = (input?.value || '').trim();

      try{
        await postJson("{{ $id ? route('avd.ciclos.participantes.whatsapp', ['sub'=>$sub,'id'=>$id,'pid'=>0]) : '' }}".replace('/0/', '/' + pid + '/'), {
          whatsapp
        }, 'PUT');
        alert('WhatsApp atualizado.');
      } catch(e){
        alert('Erro ao atualizar WhatsApp.');
      }
    });
  });

  // PILARES
  const btnSalvarPilar = document.getElementById('btnSalvarPilar');
  if (btnSalvarPilar) {
    btnSalvarPilar.addEventListener('click', async () => {
      const pilar_id = parseInt(document.getElementById('pilarId').value || '0', 10);
      const nome = document.getElementById('pilarNome').value.trim();
      const peso = parseFloat(document.getElementById('pilarPeso').value || '0');
      const ordem = parseInt(document.getElementById('pilarOrdem').value || '0', 10);

      try{
        await postJson("{{ $id ? route('avd.ciclos.pilares.salvar', ['sub'=>$sub, 'id'=>$id]) : '#' }}", {
          pilar_id, nome, peso, ordem
        });
        location.reload();
      } catch(e){
        alert('Erro ao salvar pilar.');
      }
    });
  }

  document.querySelectorAll('.js-edit-pilar').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('pilarId').value = btn.getAttribute('data-id');
      document.getElementById('pilarNome').value = btn.getAttribute('data-nome');
      document.getElementById('pilarPeso').value = btn.getAttribute('data-peso');
      document.getElementById('pilarOrdem').value = btn.getAttribute('data-ordem');
      const modal = new bootstrap.Modal(document.getElementById('modalPilar'));
      modal.show();
      if (window.feather) feather.replace();
    });
  });

  document.querySelectorAll('.js-del-pilar').forEach(btn => {
    btn.addEventListener('click', async () => {
      const pilarId = btn.getAttribute('data-id');
      if (!confirm('Excluir pilar?')) return;

      try{
        await fetch("{{ $id ? route('avd.ciclos.pilares.excluir', ['sub'=>$sub,'id'=>$id,'pilar_id'=>0]) : '' }}".replace('/0', '/' + pilarId), {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        location.reload();
      } catch(e){
        alert('Erro ao excluir pilar.');
      }
    });
  });

  // PERGUNTAS
  const btnSalvarPergunta = document.getElementById('btnSalvarPergunta');
  if (btnSalvarPergunta) {
    btnSalvarPergunta.addEventListener('click', async () => {
      const pergunta_id = parseInt(document.getElementById('perguntaId').value || '0', 10);
      const pilar_id = parseInt(document.getElementById('perguntaPilar').value || '0', 10);
      const texto = document.getElementById('perguntaTexto').value.trim();
      const peso = parseFloat(document.getElementById('perguntaPeso').value || '0');
      const tipo_resposta = document.getElementById('perguntaTipo').value;
      const ordem = parseInt(document.getElementById('perguntaOrdem').value || '0', 10);
      const exige_justificativa = document.getElementById('perguntaExige').checked;
      const permite_comentario = document.getElementById('perguntaComent').checked;

      try{
        await postJson("{{ $id ? route('avd.ciclos.perguntas.salvar', ['sub'=>$sub, 'id'=>$id]) : '#' }}", {
          pergunta_id, pilar_id, texto, peso, tipo_resposta, ordem, exige_justificativa, permite_comentario
        });
        location.reload();
      } catch(e){
        alert('Erro ao salvar pergunta.');
      }
    });
  }

  document.querySelectorAll('.js-edit-pergunta').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('perguntaId').value = btn.getAttribute('data-id');
      document.getElementById('perguntaPilar').value = btn.getAttribute('data-pilar');
      document.getElementById('perguntaTexto').value = btn.getAttribute('data-texto');
      document.getElementById('perguntaPeso').value = btn.getAttribute('data-peso');
      document.getElementById('perguntaTipo').value = btn.getAttribute('data-tipo');
      document.getElementById('perguntaExige').checked = btn.getAttribute('data-exige') === '1';
      document.getElementById('perguntaComent').checked = btn.getAttribute('data-coment') === '1';
      const modal = new bootstrap.Modal(document.getElementById('modalPergunta'));
      modal.show();
      if (window.feather) feather.replace();
    });
  });

  document.querySelectorAll('.js-del-pergunta').forEach(btn => {
    btn.addEventListener('click', async () => {
      const perguntaId = btn.getAttribute('data-id');
      if (!confirm('Excluir pergunta?')) return;

      try{
        await fetch("{{ $id ? route('avd.ciclos.perguntas.excluir', ['sub'=>$sub,'id'=>$id,'pergunta_id'=>0]) : '' }}".replace('/0', '/' + perguntaId), {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        });
        location.reload();
      } catch(e){
        alert('Erro ao excluir pergunta.');
      }
    });
  });

  if (window.feather) feather.replace();
})();
</script>
@endsection
