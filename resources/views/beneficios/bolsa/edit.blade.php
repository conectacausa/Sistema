{{-- resources/views/beneficios/bolsa/edit.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name') }} | Bolsa de Estudos</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">
<div class="wrapper">
  <div id="loader"></div>

  @include('partials.header')
  @include('partials.menu')

  <div class="content-wrapper">
    <div class="container-full">

      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Bolsa de Estudos</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => $sub]) }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item">
                    <a href="{{ route('beneficios.bolsa.index', ['sub' => $sub]) }}">Bolsa de Estudos</a>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page">Editar Processo</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <section class="content">

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Cadastro do Processo</h4>
              </div>

              <div class="box-body">

                <form method="POST" action="{{ route('beneficios.bolsa.update', ['sub' => $sub, 'id' => $processo->id]) }}">
                  @csrf
                  @method('PUT')

                  <div class="vtabs">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-processo" role="tab">
                          <i data-feather="lock"></i> Processo
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-unidades" role="tab">
                          <i data-feather="users"></i> Unidades
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-solicitantes" role="tab">
                          <i data-feather="user"></i> Solicitantes
                        </a>
                      </li>
                    </ul>

                    <div class="tab-content">

                      {{-- TAB 1 - PROCESSO --}}
                      <div class="tab-pane active" id="tab-processo" role="tabpanel">
                        <div class="row">
                          <div class="col-12">

                            {{-- Linha 1 - Ciclo --}}
                            <div class="row">
                              <div class="col-md-12">
                                <div class="form-group">
                                  <label class="form-label">Ciclo</label>
                                  <input type="text"
                                         name="ciclo"
                                         class="form-control"
                                         value="{{ old('ciclo', $processo->ciclo) }}"
                                         required>
                                </div>
                              </div>
                            </div>

                            {{-- Linha 2 - Edital (WYSIHTML5) --}}
                            <div class="row">
                              <div class="col-md-12">
                                <div class="form-group">
                                  <label class="form-label">Edital</label>
                                  <textarea name="edital"
                                            class="textarea"
                                            style="width: 100%; height: 220px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">{{ old('edital', $processo->edital) }}</textarea>
                                </div>
                              </div>
                            </div>

                            {{-- Linha 3 - Datas --}}
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label class="form-label">Início das Inscrições</label>
                                  <input type="datetime-local"
                                         name="inscricoes_inicio_at"
                                         class="form-control"
                                         value="{{ old('inscricoes_inicio_at', $processo->inscricoes_inicio_at ? \Carbon\Carbon::parse($processo->inscricoes_inicio_at)->format('Y-m-d\TH:i') : null) }}">
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label class="form-label">Fim das Inscrições</label>
                                  <input type="datetime-local"
                                         name="inscricoes_fim_at"
                                         class="form-control"
                                         value="{{ old('inscricoes_fim_at', $processo->inscricoes_fim_at ? \Carbon\Carbon::parse($processo->inscricoes_fim_at)->format('Y-m-d\TH:i') : null) }}">
                                </div>
                              </div>
                            </div>

                            {{-- Linha 4 - Orçamento / Duração / Orçamento Mensal / Status --}}
                            <div class="row">
                              <div class="col-md-3">
                                <div class="form-group">
                                  <label class="form-label">Orçamento</label>
                                  <input type="number"
                                         step="0.01"
                                         min="0"
                                         name="orcamento_total"
                                         class="form-control"
                                         value="{{ old('orcamento_total', $processo->orcamento_total ?? null) }}">
                                </div>
                              </div>

                              <div class="col-md-3">
                                <div class="form-group">
                                  <label class="form-label">Duração (meses)</label>
                                  <input type="number"
                                         min="0"
                                         name="meses_duracao"
                                         class="form-control"
                                         value="{{ old('meses_duracao', $processo->meses_duracao ?? 0) }}">
                                </div>
                              </div>

                              <div class="col-md-3">
                                <div class="form-group">
                                  <label class="form-label">Orçamento Mensal</label>
                                  <input type="number"
                                         step="0.01"
                                         min="0"
                                         name="orcamento_mensal"
                                         class="form-control"
                                         value="{{ old('orcamento_mensal', $processo->orcamento_mensal ?? 0) }}">
                                </div>
                              </div>

                              <div class="col-md-3">
                                <div class="form-group">
                                  <label class="form-label">Status</label>
                                  <select name="status" class="form-select form-control">
                                    <option value="0" @selected((string)old('status', $processo->status)==='0')>Rascunho</option>
                                    <option value="1" @selected((string)old('status', $processo->status)==='1')>Ativo</option>
                                    <option value="2" @selected((string)old('status', $processo->status)==='2')>Encerrado</option>
                                  </select>
                                </div>
                              </div>
                            </div>

                          </div>
                        </div>
                      </div>

                      {{-- TAB 2 - UNIDADES --}}
                      <div class="tab-pane" id="tab-unidades" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-15">
                          <h5 class="mb-0">Unidades vinculadas</h5>

                          <button type="button"
                                  class="btn btn-primary btn-sm"
                                  data-bs-toggle="modal"
                                  data-bs-target="#modalAddUnidade">
                            Adicionar Unidade
                          </button>
                        </div>

                        <div class="table-responsive">
                          <table class="table">
                            <thead class="bg-primary">
                              <tr>
                                <th>Unidade</th>
                                <th>Inscritos</th>
                                <th>Aprovados</th>
                                <th>Soma Limite (Aprovados)</th>
                                <th class="text-end">Ações</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse(($unidades ?? []) as $u)
                                <tr>
                                  <td>{{ $u->filial_nome_fantasia ?? $u->nome_fantasia ?? '—' }}</td>
                                  <td>{{ (int)($u->inscritos_count ?? 0) }}</td>
                                  <td>{{ (int)($u->aprovados_count ?? 0) }}</td>
                                  <td>
                                    @php
                                      $v = (float)($u->soma_limite_aprovados ?? 0);
                                      $vBR = 'R$ ' . number_format($v, 2, ',', '.');
                                    @endphp
                                    {{ $vBR }}
                                  </td>
                                  <td class="text-end">
                                    {{-- Excluir vínculo (padrão Sweatalert) --}}
                                    <form method="POST"
                                          action="{{ route('beneficios.bolsa.unidades.destroy', ['sub' => $sub, 'id' => $processo->id, 'vinculo_id' => $u->vinculo_id ?? $u->id]) }}"
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
                                <tr>
                                  <td colspan="5" class="text-center text-muted py-4">
                                    Nenhuma unidade vinculada.
                                  </td>
                                </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>
                      </div>

                      {{-- TAB 3 - SOLICITANTES --}}
                      <div class="tab-pane" id="tab-solicitantes" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-15">
                          <h5 class="mb-0">Solicitantes</h5>

                          <button type="button"
                                  class="btn btn-primary btn-sm"
                                  data-bs-toggle="modal"
                                  data-bs-target="#modalAddSolicitante">
                            Adicionar Solicitante
                          </button>
                        </div>

                        <div class="table-responsive">
                          <table class="table">
                            <thead class="bg-primary">
                              <tr>
                                <th>Colaborador</th>
                                <th>Curso</th>
                                <th>Entidade</th>
                                <th>Filial</th>
                                <th>Situação</th>
                                <th class="text-end">Ações</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse(($solicitantes ?? []) as $s)
                                @php
                                  $st = (int)($s->status ?? 0);
                                  $stLabel = match ($st) {
                                    1 => ['Reprovado', 'badge badge-danger'],
                                    2 => ['Aprovado', 'badge badge-success'],
                                    3 => ['Em análise', 'badge badge-warning'],
                                    default => ['Digitação', 'badge badge-secondary'],
                                  };
                                @endphp
                                <tr>
                                  <td>{{ $s->colaborador_nome ?? '—' }}</td>
                                  <td>{{ $s->curso_nome ?? '—' }}</td>
                                  <td>{{ $s->entidade_nome ?? '—' }}</td>
                                  <td>{{ $s->filial_nome_fantasia ?? $s->filial_nome ?? '—' }}</td>
                                  <td><span class="{{ $stLabel[1] }}">{{ $stLabel[0] }}</span></td>
                                  <td class="text-end">
                                    {{-- Excluir solicitação (padrão Sweatalert) --}}
                                    <form method="POST"
                                          action="{{ route('beneficios.bolsa.solicitantes.destroy', ['sub' => $sub, 'id' => $processo->id, 'solicitacao_id' => $s->id]) }}"
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
                                <tr>
                                  <td colspan="6" class="text-center text-muted py-4">
                                    Nenhum solicitante cadastrado.
                                  </td>
                                </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>
                      </div>

                    </div>
                  </div>

                  {{-- Botão salvar FORA das abas --}}
                  <div class="mt-20 text-end">
                    <button type="submit" class="btn btn-success">
                      Salvar
                    </button>
                  </div>

                </form>

              </div>
            </div>
          </div>
        </div>

      </section>

    </div>
  </div>

  @include('partials.footer')
</div>

{{-- MODAL - ADICIONAR UNIDADE --}}
<div class="modal fade" id="modalAddUnidade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('beneficios.bolsa.unidades.store', ['sub' => $sub, 'id' => $processo->id]) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Adicionar Unidade</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Unidade (Filial)</label>
            <select name="filial_id" class="form-select form-control" required>
              <option value="">Selecione...</option>
              @foreach(($filiais ?? []) as $f)
                <option value="{{ $f->id }}">
                  {{ $f->nome_fantasia ?? $f->razao_social }}
                </option>
              @endforeach
            </select>
          </div>
          <small class="text-muted">A unidade será vinculada a este processo.</small>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Adicionar</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- MODAL - ADICIONAR SOLICITANTE --}}
<div class="modal fade" id="modalAddSolicitante" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('beneficios.bolsa.solicitantes.store', ['sub' => $sub, 'id' => $processo->id]) }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Adicionar Solicitante</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Colaborador</label>
                {{-- Simples e funcional: input do código/ID do colaborador --}}
                <input type="number" name="colaborador_id" class="form-control" placeholder="ID do colaborador" required>
                <small class="text-muted">No próximo passo podemos trocar por busca/autocomplete.</small>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Filial</label>
                <select name="filial_id" class="form-select form-control" required>
                  <option value="">Selecione...</option>
                  @foreach(($filiais ?? []) as $f)
                    <option value="{{ $f->id }}">
                      {{ $f->nome_fantasia ?? $f->razao_social }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Curso</label>
                {{-- Simplificação funcional: curso_id --}}
                <input type="number" name="curso_id" class="form-control" placeholder="ID do curso" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label">Valor total mensalidade</label>
                <input type="number" step="0.01" min="0" name="valor_total_mensalidade" class="form-control" placeholder="0,00" required>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Adicionar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

{{-- SweetAlert global delete confirm --}}
<script src="{{ asset('assets/js/app-delete-confirm.js') }}"></script>

{{-- WYSIHTML5 (referência do seu exemplo) --}}
<script src="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js') }}"></script>

<script>
  if (window.feather) feather.replace();

  document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $('.textarea').length) {
      $('.textarea').wysihtml5();
    }
  });
</script>

</body>
</html>
