@extends('layouts.app')
@section('title', 'Transporte')

@section('content')

<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h4 class="page-title">Transporte</h4>
      <div class="d-inline-block align-items-center">
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
            <li class="breadcrumb-item">Beneficios</li>
            <li class="breadcrumb-item" aria-current="page">Transporte</li>
            <li class="breadcrumb-item" aria-current="page">Editar Linha</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="box">

        <div class="box-header with-border">
          <h4 class="box-title">Edição de Linha</h4>
        </div>

        <form method="POST" action="{{ route('beneficios.transporte.linhas.update', ['sub' => $sub, 'id' => $linha->id]) }}">
          @csrf
          @method('PUT')

          <div class="box-body">

            <div class="row mt-3">
              <div class="col-md-9">
                <div class="row">
                  <div class="col-md-9">
                    <div class="form-group">
                      <label class="form-label">Linha</label>
                      <div class="input-group">
                        <input type="text" class="form-control" name="nome" value="{{ old('nome', $linha->nome) }}" placeholder="Nome Linha">
                      </div>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">Tipo</label>
                      <div class="input-group">
                        <select class="form-select" name="tipo_linha">
                          <option value="fretada" @selected(old('tipo_linha', $linha->tipo_linha) === 'fretada')>Fretado</option>
                          <option value="publica" @selected(old('tipo_linha', $linha->tipo_linha) === 'publica')>Publico</option>
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
                          <option value="cartao" @selected(old('controle_acesso', $linha->controle_acesso) === 'cartao')>Cartão</option>
                          <option value="ticket" @selected(old('controle_acesso', $linha->controle_acesso) === 'ticket')>Ticket</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Filial</label>
                      <div class="input-group">
                        <select class="form-select" name="filial_id" id="filial_id">
                          <option value="">Selecione...</option>
                          @foreach($filiais as $f)
                            <option value="{{ $f->id }}" @selected((int)old('filial_id', $filialAtualId) === (int)$f->id)>
                              {{ $f->nome_fantasia ?? $f->nome ?? ('Filial '.$f->id) }}
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
                          <option value="ativo" @selected(old('status', $linha->status) === 'ativo')>Ativa</option>
                          <option value="inativo" @selected(old('status', $linha->status) === 'inativo')>Inativa</option>
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
                          @foreach($veiculos as $v)
                            <option value="{{ $v->id }}" @selected((int)old('veiculo_id', $linha->veiculo_id) === (int)$v->id)>
                              {{ ($v->modelo ?? 'Veículo') }} {{ $v->placa ? ('- '.$v->placa) : '' }}
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
                          @foreach($motoristas as $m)
                            <option value="{{ $m->id }}" @selected((int)old('motorista_id', $linha->motorista_id) === (int)$m->id)>
                              {{ $m->nome ?? ('Motorista '.$m->id) }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-2">
                    <div class="form-group">
                      <label class="form-label">Capacidade</label>
                      <div class="input-group">
                        <input type="text" class="form-control" value="{{ $metrics['capacidade'] ?? 0 }}" readonly>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-2">
                    <div class="form-group">
                      <label class="form-label">Usuários</label>
                      <div class="input-group">
                        <input type="text" class="form-control" value="{{ $metrics['usuarios_ativos'] ?? 0 }}" readonly>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-2">
                    <div class="form-group">
                      <label class="form-label">Disponivel</label>
                      <div class="input-group">
                        <input type="text" class="form-control" value="{{ $metrics['disponivel'] ?? 0 }}" readonly>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">Valor Linha (mês)</label>
                      <div class="input-group">
                        <input type="text" class="form-control" value="R$ {{ number_format((float)($metrics['valor_linha_mes'] ?? 0), 2, ',', '.') }}" readonly>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="form-label">Valor Por Usuário (mês)</label>
                      <div class="input-group">
                        <input type="text" class="form-control" value="R$ {{ number_format((float)($metrics['valor_por_user'] ?? 0), 2, ',', '.') }}" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botões -->
              <div class="col-md-3">
                <div class="d-grid gap-2">
                  <button type="button" class="btn bg-gradient-primary w-250" data-bs-toggle="modal" data-bs-target="#modalParada">
                    Adicionar Parada
                  </button>

                  <button type="button" class="btn bg-gradient-primary w-250" data-bs-toggle="modal" data-bs-target="#modalColaborador">
                    Adicionar Colaborador
                  </button>

                  <button type="button" class="btn bg-gradient-primary w-250" data-bs-toggle="modal" data-bs-target="#modalPedido">
                    Adicionar Pedidos
                  </button>

                  <button type="submit" class="waves-effect waves-light btn mb-5 bg-gradient-success w-250">
                    Salvar
                  </button>
                </div>
              </div>
            </div>

          </div>
        </form>

        <!-- Tabs -->
        <div class="box-body">
          <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-bs-toggle="tab" href="#Paradas" role="tab">
                <span><i class="ion-home"></i></span> <span class="hidden-xs-down ms-15">Paradas</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#usuarios" role="tab">
                <span><i class="ion-person"></i></span> <span class="hidden-xs-down ms-15">Usuários</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" href="#financeiro" role="tab">
                <span><i class="ion-cash"></i></span> <span class="hidden-xs-down ms-15">Financeiro</span>
              </a>
            </li>
          </ul>

          <div class="tab-content tabcontent-border">

            <!-- Paradas -->
            <div class="tab-pane active" id="Paradas" role="tabpanel">
              <div class="p-15">
                <table class="table">
                  <thead class="bg-primary">
                    <tr>
                      <th>#</th>
                      <th>Identificação</th>
                      <th>Horário</th>
                      <th>Valor</th>
                      <th>Ação</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($paradas as $p)
                      <tr>
                        <td>{{ $p->ordem }}</td>
                        <td>{{ $p->identificacao }}</td>
                        <td>{{ $p->horario }}</td>
                        <td>R$ {{ number_format((float)($p->valor ?? 0), 2, ',', '.') }}</td>
                        <td>
                          <div class="clearfix">
                            {{-- (editar parada pode vir depois) --}}

                            <form method="POST"
                                  action="{{ route('beneficios.transporte.linhas.paradas.destroy', ['sub'=>$sub,'id'=>$linha->id,'parada_id'=>$p->id]) }}"
                                  class="d-inline js-form-delete">
                              @csrf
                              @method('DELETE')

                              <button type="button"
                                      class="btn btn-danger btn-sm js-btn-delete"
                                      data-title="Confirmar exclusão"
                                      data-text="Deseja realmente excluir este registro?"
                                      data-confirm="Sim, excluir"
                                      data-cancel="Cancelar">
                                <i data-feather="trash-2"></i>
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="5" class="text-center text-muted">Nenhuma parada cadastrada.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Usuários -->
            <div class="tab-pane" id="usuarios" role="tabpanel">
              <div class="p-15">
                <table class="table">
                  <thead class="bg-primary">
                    <tr>
                      <th>Colaborador</th>
                      <th>Cartão</th>
                      <th>Parada</th>
                      <th>Início</th>
                      <th>Fim</th>
                      <th>Ação</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($usuarios as $u)
                      @php
                        $hoje = now()->toDateString();
                        $ativo = ($u->data_inicio <= $hoje) && (empty($u->data_fim) || $u->data_fim >= $hoje);
                      @endphp
                      <tr>
                        <td>
                          {{ $u->colaborador_nome ?? '—' }}
                          @if($ativo)
                            <span class="badge badge-success ms-5">Ativo</span>
                          @else
                            <span class="badge badge-secondary ms-5">Inativo</span>
                          @endif
                        </td>
                        <td>{{ $u->cartao_numero ?? '—' }}</td>
                        <td>{{ $u->parada_nome ?? '—' }}</td>
                        <td>{{ $u->data_inicio ?? '—' }}</td>
                        <td>{{ $u->data_fim ?? '—' }}</td>
                        <td>
                          <button type="button"
                                  class="btn btn-primary btn-sm js-btn-editar-vinculo"
                                  data-bs-toggle="modal"
                                  data-bs-target="#modalEditarVinculo"
                                  data-id="{{ $u->id }}"
                                  data-parada="{{ $u->parada_id }}"
                                  data-inicio="{{ $u->data_inicio }}"
                                  data-fim="{{ $u->data_fim }}"
                                  data-cartao="{{ $u->cartao_numero }}"
                                  data-valor="{{ $u->valor_passagem ?? '' }}">
                            <i data-feather="edit"></i>
                          </button>

                          @if($ativo)
                            <button type="button"
                                    class="btn btn-warning btn-sm js-btn-inativar"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarVinculo"
                                    data-id="{{ $u->id }}"
                                    data-parada="{{ $u->parada_id }}"
                                    data-inicio="{{ $u->data_inicio }}"
                                    data-fim="{{ now()->toDateString() }}"
                                    data-cartao="{{ $u->cartao_numero }}"
                                    data-valor="{{ $u->valor_passagem ?? '' }}">
                              Inativar
                            </button>
                          @endif
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="6" class="text-center text-muted">Nenhum usuário vinculado.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Financeiro -->
            <div class="tab-pane" id="financeiro" role="tabpanel">
              <div class="p-15">
                <table class="table">
                  <thead class="bg-primary">
                    <tr>
                      <th>Pedido</th>
                      <th>Data</th>
                      <th>Valor</th>
                      <th>Situação</th>
                      <th>Ação</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($pedidos as $p)
                      <tr>
                        <td>{{ $p->codigo }}</td>
                        <td>{{ $p->data_pedido }}</td>
                        <td>R$ {{ number_format((float)($p->valor_total ?? 0), 2, ',', '.') }}</td>
                        <td>{{ $p->status }}</td>
                        <td>
                          <form method="POST"
                                action="{{ route('beneficios.transporte.linhas.pedidos.destroy', ['sub'=>$sub,'id'=>$linha->id,'pedido_id'=>$p->id]) }}"
                                class="d-inline js-form-delete">
                            @csrf
                            @method('DELETE')

                            <button type="button"
                                    class="btn btn-danger btn-sm js-btn-delete"
                                    data-title="Confirmar exclusão"
                                    data-text="Deseja realmente excluir este registro?"
                                    data-confirm="Sim, excluir"
                                    data-cancel="Cancelar">
                              <i data-feather="trash-2"></i>
                            </button>
                          </form>
                        </td>
                      </tr>
                    @empty
                      <tr><td colspan="5" class="text-center text-muted">Nenhum pedido no mês corrente.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</section>

{{-- MODAL: Adicionar Parada --}}
<div class="modal fade" id="modalParada" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('beneficios.transporte.linhas.paradas.store', ['sub'=>$sub,'id'=>$linha->id]) }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <h4 class="modal-title">Adicionar Parada</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="form-label">Parada</label>
              <div class="input-group">
                <input type="text" class="form-control" name="identificacao" placeholder="Identificação Parada" required>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="form-label">Endereço</label>
              <div class="input-group">
                <input type="text" class="form-control" name="endereco" placeholder="Endereço">
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Ordem</label>
              <div class="input-group">
                <input type="number" class="form-control" name="ordem" placeholder="Ordem" required>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Horário</label>
              <div class="input-group">
                <input type="time" class="form-control" name="horario" required>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Valor</label>
              <div class="input-group">
                <input type="text" class="form-control" name="valor" placeholder="0,00" required>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn bg-gradient-danger" data-bs-dismiss="modal">Fechar</button>
        <button type="submit" class="btn bg-gradient-success">Salvar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: Adicionar Colaborador --}}
<div class="modal fade" id="modalColaborador" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('beneficios.transporte.linhas.usuarios.store', ['sub'=>$sub,'id'=>$linha->id]) }}" class="modal-content">
      @csrf
      <div class="modal-header">
        <h4 class="modal-title">Adicionar Colaborador</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="form-label">Colaborador (ID por enquanto)</label>
              <div class="input-group">
                <input type="number" class="form-control" name="colaborador_id" placeholder="ID do colaborador" required>
              </div>
              <small class="text-muted">Depois trocamos por busca por nome/matrícula.</small>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Parada</label>
              <div class="input-group">
                <select class="form-select" name="parada_id" required>
                  <option value="">Selecione...</option>
                  @foreach($paradas as $p)
                    <option value="{{ $p->id }}">{{ $p->identificacao }} ({{ $p->horario }})</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Valor</label>
              <div class="input-group">
                <span class="input-group-addon">R$</span>
                <input type="text" class="form-control" name="valor_passagem" placeholder="0,00" required>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Cartão</label>
              <div class="input-group">
                <input type="text" class="form-control" name="cartao_numero" placeholder="Código Cartão">
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Inicio do Uso</label>
              <div class="input-group">
                <input type="date" class="form-control" name="data_inicio" required value="{{ now()->toDateString() }}">
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Fim do Uso</label>
              <div class="input-group">
                <input type="date" class="form-control" name="data_fim">
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn bg-gradient-danger" data-bs-dismiss="modal">Fechar</button>
        <button type="submit" class="btn bg-gradient-success">Salvar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: Editar/Inativar Vínculo --}}
<div class="modal fade" id="modalEditarVinculo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" id="formEditarVinculo" class="modal-content">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h4 class="modal-title">Editar vínculo</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Parada</label>
              <div class="input-group">
                <select class="form-select" name="parada_id" id="edit_parada" required>
                  <option value="">Selecione...</option>
                  @foreach($paradas as $p)
                    <option value="{{ $p->id }}">{{ $p->identificacao }} ({{ $p->horario }})</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Início</label>
              <div class="input-group">
                <input type="date" class="form-control" name="data_inicio" id="edit_inicio" required>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Fim</label>
              <div class="input-group">
                <input type="date" class="form-control" name="data_fim" id="edit_fim">
              </div>
              <small class="text-muted">Para inativar, preencha a data fim.</small>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Cartão</label>
              <div class="input-group">
                <input type="text" class="form-control" name="cartao_numero" id="edit_cartao">
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Valor</label>
              <div class="input-group">
                <span class="input-group-addon">R$</span>
                <input type="text" class="form-control" name="valor_passagem" id="edit_valor" required>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn bg-gradient-danger" data-bs-dismiss="modal">Fechar</button>
        <button type="submit" class="btn bg-gradient-success">Salvar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: Adicionar Pedido --}}
<div class="modal fade" id="modalPedido" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form method="POST" action="{{ route('beneficios.transporte.linhas.pedidos.store', ['sub'=>$sub,'id'=>$linha->id]) }}" class="modal-content" id="formPedido">
      @csrf
      <div class="modal-header">
        <h4 class="modal-title">Adicionar Pedido</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <label class="form-label">Código Pedido</label>
              <div class="input-group">
                <input type="text" class="form-control" name="codigo" placeholder="Número Pedido" required>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Data</label>
              <div class="input-group">
                <input type="date" class="form-control" name="data_pedido" value="{{ now()->toDateString() }}" required>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label class="form-label">Situação</label>
              <div class="input-group">
                <input type="text" class="form-control" name="status" value="aberto" required>
              </div>
            </div>
          </div>
        </div>

        <hr>

        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label class="form-label">Número Cartão</label>
              <div class="input-group">
                <input type="text" class="form-control" id="pedido_cartao" placeholder="Cartão">
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group">
              <label class="form-label">Colaborador (opcional ID)</label>
              <div class="input-group">
                <input type="number" class="form-control" id="pedido_colaborador" placeholder="ID">
              </div>
            </div>
          </div>

          <div class="col-md-2">
            <div class="form-group">
              <label class="form-label">Valor</label>
              <div class="input-group">
                <input type="text" class="form-control" id="pedido_valor" placeholder="0,00">
              </div>
            </div>
          </div>

          <div class="col-md-1">
            <div class="form-group">
              <button type="button" class="btn bg-gradient-success mt-25" id="btnAddItem">
                <i class="fa fa-plus"></i>
              </button>
            </div>
          </div>
        </div>

        <div class="table-responsive mt-2">
          <table class="table">
            <thead class="bg-primary">
              <tr>
                <th>Cartão</th>
                <th>Colaborador</th>
                <th>Valor</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="pedidoItens"></tbody>
          </table>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn bg-gradient-danger" data-bs-dismiss="modal">Fechar</button>
        <button type="submit" class="btn bg-gradient-success">Salvar</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (window.feather) feather.replace();

  // SweetAlert padrão (parada com usuário vinculado):
  @if(session('error') && str_contains(session('error'), 'usuários vinculados'))
    if (window.Swal) {
      Swal.fire({
        title: 'Atenção',
        text: @json(session('error')),
        icon: 'warning',
        confirmButtonText: 'OK'
      });
    }
  @endif

  // Modal editar/inativar vínculo
  document.querySelectorAll('.js-btn-editar-vinculo, .js-btn-inativar').forEach(btn => {
    btn.addEventListener('click', function() {
      const vinculoId = this.getAttribute('data-id');
      const parada    = this.getAttribute('data-parada');
      const inicio    = this.getAttribute('data-inicio');
      const fim       = this.getAttribute('data-fim');
      const cartao    = this.getAttribute('data-cartao');
      const valor     = this.getAttribute('data-valor');

      const form = document.getElementById('formEditarVinculo');
      form.setAttribute('action', "{{ route('beneficios.transporte.linhas.usuarios.update', ['sub'=>$sub,'id'=>$linha->id,'vinculo_id'=>999999]) }}".replace('999999', vinculoId));

      document.getElementById('edit_parada').value = parada || '';
      document.getElementById('edit_inicio').value = inicio || '';
      document.getElementById('edit_fim').value    = fim || '';
      document.getElementById('edit_cartao').value = cartao || '';
      document.getElementById('edit_valor').value  = valor || '';
    });
  });

  // Pedido: itens dinâmicos (itens[])
  const itensBody = document.getElementById('pedidoItens');

  function addItem(cartao, colaborador, valor) {
    const idx = itensBody.querySelectorAll('tr').length;

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        ${cartao || ''}
        <input type="hidden" name="itens[${idx}][cartao_numero]" value="${cartao || ''}">
      </td>
      <td>
        ${colaborador || ''}
        <input type="hidden" name="itens[${idx}][colaborador_id]" value="${colaborador || ''}">
      </td>
      <td>
        ${valor || ''}
        <input type="hidden" name="itens[${idx}][valor]" value="${valor || ''}">
      </td>
      <td>
        <button type="button" class="btn btn-danger btn-sm js-remove-item">
          <i data-feather="trash-2"></i>
        </button>
      </td>
    `;
    itensBody.appendChild(tr);
    if (window.feather) feather.replace();

    tr.querySelector('.js-remove-item').addEventListener('click', () => tr.remove());
  }

  document.getElementById('btnAddItem').addEventListener('click', function() {
    const cartao = document.getElementById('pedido_cartao').value.trim();
    const colab  = document.getElementById('pedido_colaborador').value.trim();
    const valor  = document.getElementById('pedido_valor').value.trim();

    if (!valor) {
      alert('Informe o valor do item.');
      return;
    }

    addItem(cartao, colab, valor);
    document.getElementById('pedido_cartao').value = '';
    document.getElementById('pedido_colaborador').value = '';
    document.getElementById('pedido_valor').value = '';
  });
});
</script>
@endpush

@endsection
