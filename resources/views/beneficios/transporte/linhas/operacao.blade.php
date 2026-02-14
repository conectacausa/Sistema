@extends('layouts.app')

@section('title', 'Transporte - Operação da Linha')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">Operação - {{ $linha->nome }}</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard', ['sub'=>$sub]) }}"><i class="mdi mdi-home-outline"></i></a></li>
        <li class="breadcrumb-item">Benefícios</li>
        <li class="breadcrumb-item"><a href="{{ route('beneficios.transporte.linhas.index', ['sub'=>$sub]) }}">Transporte</a></li>
        <li class="breadcrumb-item active">Operação</li>
      </ol>
    </div>
    <a class="btn btn-primary" href="{{ route('beneficios.transporte.linhas.edit', ['sub'=>$sub,'id'=>$linha->id]) }}">
      <i data-feather="edit"></i>
    </a>
  </div>
</div>

<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">

          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
              </ul>
            </div>
          @endif

          <div class="vtabs">
            <ul class="nav nav-tabs tabs-vertical" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-paradas" role="tab">
                  <i data-feather="user"></i> Paradas
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-vinculos" role="tab">
                  <i data-feather="users"></i> Vínculos
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-encerrar" role="tab">
                  <i data-feather="lock"></i> Encerrar Vínculo
                </a>
              </li>
            </ul>

            <div class="tab-content">

              {{-- TAB PARADAS --}}
              <div class="tab-pane active" id="tab-paradas" role="tabpanel">
                <div class="row">
                  <div class="col-12">
                    <h4 class="mb-10">Cadastrar Parada</h4>

                    <form method="POST" action="{{ route('beneficios.transporte.linhas.parada.store', ['sub'=>$sub,'linhaId'=>$linha->id]) }}">
                      @csrf
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="form-label">Nome</label>
                            <input name="nome" class="form-control" required>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="form-label">Horário</label>
                            <input name="horario" class="form-control" placeholder="HH:MM">
                          </div>
                        </div>
                        <div class="col-md-2">
                          <div class="form-group">
                            <label class="form-label">Valor</label>
                            <input name="valor" class="form-control" value="0" required>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <div class="form-group">
                            <label class="form-label">Ordem</label>
                            <input name="ordem" class="form-control" value="0">
                          </div>
                        </div>
                        <div class="col-12">
                          <div class="form-group">
                            <label class="form-label">Endereço</label>
                            <input name="endereco" class="form-control">
                          </div>
                        </div>
                      </div>

                      <div class="d-flex justify-content-end mt-10">
                        <button class="btn btn-primary" type="submit">Salvar</button>
                      </div>
                    </form>

                    <hr>

                    <h4 class="mb-10">Paradas Cadastradas</h4>
                    <div class="table-responsive">
                      <table class="table table-hover">
                        <thead class="bg-primary">
                          <tr>
                            <th>Ordem</th>
                            <th>Nome</th>
                            <th>Horário</th>
                            <th>Valor</th>
                            <th style="width:120px;">Ações</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse($paradas as $p)
                            <tr>
                              <td>{{ $p->ordem }}</td>
                              <td>{{ $p->nome }}</td>
                              <td>{{ $p->horario }}</td>
                              <td>R$ {{ number_format((float)$p->valor, 2, ',', '.') }}</td>
                              <td class="text-nowrap">
                                {{-- Edit simples inline (mínimo de telas) --}}
                                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#editParada{{ $p->id }}">
                                  <i data-feather="edit"></i>
                                </button>

                                <form method="POST" action="{{ route('beneficios.transporte.linhas.parada.destroy', ['sub'=>$sub,'linhaId'=>$linha->id,'paradaId'=>$p->id]) }}" class="d-inline js-form-delete">
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

                            <tr class="collapse" id="editParada{{ $p->id }}">
                              <td colspan="5">
                                <form method="POST" action="{{ route('beneficios.transporte.linhas.parada.update', ['sub'=>$sub,'linhaId'=>$linha->id,'paradaId'=>$p->id]) }}">
                                  @csrf
                                  @method('PUT')
                                  <div class="row">
                                    <div class="col-md-4">
                                      <div class="form-group">
                                        <label class="form-label">Nome</label>
                                        <input name="nome" class="form-control" value="{{ $p->nome }}" required>
                                      </div>
                                    </div>
                                    <div class="col-md-4">
                                      <div class="form-group">
                                        <label class="form-label">Horário</label>
                                        <input name="horario" class="form-control" value="{{ $p->horario }}">
                                      </div>
                                    </div>
                                    <div class="col-md-2">
                                      <div class="form-group">
                                        <label class="form-label">Valor</label>
                                        <input name="valor" class="form-control" value="{{ $p->valor }}" required>
                                      </div>
                                    </div>
                                    <div class="col-md-2">
                                      <div class="form-group">
                                        <label class="form-label">Ordem</label>
                                        <input name="ordem" class="form-control" value="{{ $p->ordem }}">
                                      </div>
                                    </div>
                                    <div class="col-12">
                                      <div class="form-group">
                                        <label class="form-label">Endereço</label>
                                        <input name="endereco" class="form-control" value="{{ $p->endereco }}">
                                      </div>
                                    </div>
                                  </div>

                                  <div class="d-flex justify-content-end mt-10">
                                    <button class="btn btn-primary" type="submit">Salvar</button>
                                  </div>
                                </form>
                              </td>
                            </tr>

                          @empty
                            <tr><td colspan="5" class="text-center text-muted">Nenhuma parada cadastrada.</td></tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>

                  </div>
                </div>
              </div>

              {{-- TAB VÍNCULOS --}}
              <div class="tab-pane" id="tab-vinculos" role="tabpanel">
                <h4 class="mb-10">Vincular Colaborador</h4>
                <form method="POST" action="{{ route('beneficios.transporte.linhas.vinculo.store', ['sub'=>$sub,'linhaId'=>$linha->id]) }}">
                  @csrf

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label">Colaborador</label>
                        <select name="usuario_id" class="form-select" required>
                          <option value="">Selecione</option>
                          @foreach($usuarios as $u)
                            <option value="{{ $u->id }}">
                              {{ $u->nome_completo }}{{ $u->matricula ? ' - Mat: '.$u->matricula : '' }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">Parada</label>
                        <select name="parada_id" class="form-select">
                          <option value="">(Sem parada)</option>
                          @foreach($paradas as $p)
                            <option value="{{ $p->id }}">{{ $p->ordem }} - {{ $p->nome }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">Data início</label>
                        <input type="date" name="data_inicio" class="form-control" required>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-label">Número do Cartão</label>
                        <input name="numero_cartao" class="form-control" placeholder="Se controle por cartão">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-label">Ticket / Vale</label>
                        <input name="numero_vale_ticket" class="form-control" placeholder="Se controle por ticket">
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label class="form-label">Valor da Passagem</label>
                        <input name="valor_passagem" class="form-control" value="0" required>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="2"></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-end mt-10">
                    <button class="btn btn-primary" type="submit">Salvar</button>
                  </div>
                </form>

                <hr>

                <h4 class="mb-10">Vínculos</h4>
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead class="bg-primary">
                      <tr>
                        <th>Colaborador</th>
                        <th>Parada</th>
                        <th>Cartão</th>
                        <th>Ticket</th>
                        <th>Valor</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($vinculos as $v)
                        <tr>
                          <td>{{ $v->usuario->nome_completo ?? $v->usuario_id }}</td>
                          <td>{{ $v->parada->nome ?? '-' }}</td>
                          <td>{{ $v->numero_cartao ?? '-' }}</td>
                          <td>{{ $v->numero_vale_ticket ?? '-' }}</td>
                          <td>R$ {{ number_format((float)$v->valor_passagem, 2, ',', '.') }}</td>
                          <td>{{ $v->data_inicio }}</td>
                          <td>{{ $v->data_fim ?? '-' }}</td>
                          <td>
                            <span class="badge {{ $v->status === 'ativo' ? 'badge-success' : 'badge-secondary' }}">
                              {{ $v->status }}
                            </span>
                          </td>
                        </tr>
                      @empty
                        <tr><td colspan="8" class="text-center text-muted">Nenhum vínculo.</td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
                <div class="mt-15">{{ $vinculos->links() }}</div>
              </div>

              {{-- TAB ENCERRAR --}}
              <div class="tab-pane" id="tab-encerrar" role="tabpanel">
                <h4 class="mb-10">Encerrar vínculo (definir data fim)</h4>
                <p class="text-muted mb-10">Após a data fim, o colaborador não deve utilizar o transporte.</p>

                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead class="bg-primary">
                      <tr>
                        <th>Colaborador</th>
                        <th>Cartão</th>
                        <th>Ticket</th>
                        <th>Status</th>
                        <th style="width:260px;">Encerrar</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($vinculos as $v)
                        <tr>
                          <td>{{ $v->usuario->nome_completo ?? $v->usuario_id }}</td>
                          <td>{{ $v->numero_cartao ?? '-' }}</td>
                          <td>{{ $v->numero_vale_ticket ?? '-' }}</td>
                          <td>{{ $v->status }}</td>
                          <td>
                            @if($v->status === 'ativo')
                              <form method="POST" action="{{ route('beneficios.transporte.linhas.vinculo.encerrar', ['sub'=>$sub,'linhaId'=>$linha->id,'vinculoId'=>$v->id]) }}">
                                @csrf
                                @method('PUT')
                                <div class="d-flex gap-10">
                                  <input type="date" name="data_fim" class="form-control" required>
                                  <button class="btn btn-primary" type="submit">Encerrar</button>
                                </div>
                              </form>
                            @else
                              <span class="text-muted">Encerrado em {{ $v->data_fim }}</span>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                      @if($vinculos->count() === 0)
                        <tr><td colspan="5" class="text-center text-muted">Nenhum vínculo.</td></tr>
                      @endif
                    </tbody>
                  </table>
                </div>

              </div>

            </div>
          </div>

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
