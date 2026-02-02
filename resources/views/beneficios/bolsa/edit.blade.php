<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Bolsa de Estudos</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  <!-- ✅ Bootstrap WYSIHTML5 CSS (do seu assets.zip) -->
  <link rel="stylesheet" href="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">

  <style>
    .vtabs { display:flex; width:100%; }
    .vtabs > .nav.tabs-vertical { flex:0 0 260px; min-width:260px; }
    .vtabs > .tab-content { flex:1 1 auto; width:100%; }
    @media (max-width: 991.98px){
      .vtabs { display:block; }
      .vtabs > .nav.tabs-vertical { min-width:100%; flex:0 0 auto; }
    }
  </style>

  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">
<div class="wrapper">
  <div id="loader"></div>

  @includeIf('partials.header')
  @includeIf('partials.menu')

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
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('error') }}
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            Verifique os campos e tente novamente.
          </div>
        @endif

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Cadastro do Processo</h4>
              </div>

              <div class="box-body">

                {{-- ✅ Form principal (Salvar fora das tabs) --}}
                <form method="POST" action="{{ route('beneficios.bolsa.update', ['sub' => $sub, 'id' => $processo->id]) }}">
                  @csrf
                  @method('PUT')

                  <div class="vtabs">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist">

                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-processo" role="tab">
                          <span><i data-feather="lock" class="me-10"></i>Processo</span>
                        </a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-unidades" role="tab">
                          <span><i data-feather="home" class="me-10"></i>Unidades</span>
                        </a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-solicitantes" role="tab">
                          <span><i data-feather="users" class="me-10"></i>Solicitantes</span>
                        </a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-documentos" role="tab">
                          <span><i data-feather="file-text" class="me-10"></i>Documentos</span>
                        </a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-config" role="tab">
                          <span><i data-feather="settings" class="me-10"></i>Configuração</span>
                        </a>
                      </li>

                    </ul>

                    <div class="tab-content">

                      {{-- TAB 1 - PROCESSO --}}
                      <div class="tab-pane active" id="tab-processo" role="tabpanel">
                        <div class="p-15">

                          {{-- Linha 1 - Titulo/Ciclo --}}
                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Título / Ciclo</label>
                                <input type="text"
                                       name="ciclo"
                                       class="form-control"
                                       value="{{ old('ciclo', $processo->ciclo) }}"
                                       maxlength="60"
                                       required>
                              </div>
                            </div>
                          </div>

                          {{-- Linha 2 - Edital (WYSIHTML5) --}}
                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Edital</label>
                                <textarea name="edital"
                                          class="textarea"
                                          style="width:100%;height:220px;font-size:14px;line-height:18px;border:1px solid #dddddd;padding:10px;">{{ old('edital', $processo->edital) }}</textarea>
                              </div>
                            </div>
                          </div>

                          {{-- Linha 3 - Inicio/Fim/Status/Data Base --}}
                          <div class="row">
                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Início das Inscrições</label>
                                <input type="datetime-local"
                                       name="inscricoes_inicio_at"
                                       class="form-control"
                                       value="{{ old('inscricoes_inicio_at', $processo->inscricoes_inicio_at ? \Carbon\Carbon::parse($processo->inscricoes_inicio_at)->format('Y-m-d\TH:i') : null) }}">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Fim das Inscrições</label>
                                <input type="datetime-local"
                                       name="inscricoes_fim_at"
                                       class="form-control"
                                       value="{{ old('inscricoes_fim_at', $processo->inscricoes_fim_at ? \Carbon\Carbon::parse($processo->inscricoes_fim_at)->format('Y-m-d\TH:i') : null) }}">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                  <option value="0" {{ (string)old('status', $processo->status)==='0' ? 'selected' : '' }}>Rascunho</option>
                                  <option value="1" {{ (string)old('status', $processo->status)==='1' ? 'selected' : '' }}>Ativo</option>
                                  <option value="2" {{ (string)old('status', $processo->status)==='2' ? 'selected' : '' }}>Encerrado</option>
                                </select>
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Data Base</label>
                                <input type="date"
                                       name="data_base"
                                       class="form-control"
                                       value="{{ old('data_base', !empty($processo->data_base) ? \Carbon\Carbon::parse($processo->data_base)->format('Y-m-d') : null) }}">
                              </div>
                            </div>
                          </div>

                          {{-- Linha 4 - Valor Mês / Quant. Meses / Total / Usado / Saldo --}}
                          <div class="row">
                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Valor Mês</label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       id="valor_mes"
                                       name="orcamento_mensal"
                                       class="form-control"
                                       value="{{ old('orcamento_mensal', $processo->orcamento_mensal ?? 0) }}">
                              </div>
                            </div>

                            <div class="col-md-2 col-12">
                              <div class="form-group">
                                <label class="form-label">Quant. Meses</label>
                                <input type="number"
                                       min="0"
                                       id="quant_meses"
                                       name="meses_duracao"
                                       class="form-control"
                                       value="{{ old('meses_duracao', $processo->meses_duracao ?? 0) }}">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Valor Total</label>
                                <input type="text"
                                       id="valor_total_calc"
                                       class="form-control"
                                       value="R$ 0,00"
                                       disabled>
                                <input type="hidden"
                                       name="orcamento_total"
                                       id="orcamento_total_hidden"
                                       value="{{ old('orcamento_total', $processo->orcamento_total ?? 0) }}">
                              </div>
                            </div>

                            <div class="col-md-2 col-12">
                              <div class="form-group">
                                <label class="form-label">Valor Usado</label>
                                <input type="text"
                                       id="valor_usado"
                                       class="form-control"
                                       value="R$ 0,00"
                                       disabled>
                                <input type="hidden" id="valor_usado_hidden" value="0">
                              </div>
                            </div>

                            <div class="col-md-2 col-12">
                              <div class="form-group">
                                <label class="form-label">Saldo</label>
                                <input type="text"
                                       id="saldo_calc"
                                       class="form-control"
                                       value="R$ 0,00"
                                       disabled>
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>

                      {{-- TAB 2 - UNIDADES --}}
                      <div class="tab-pane" id="tab-unidades" role="tabpanel">
                        <div class="p-15">
                          <div class="d-flex justify-content-between align-items-center mb-15">
                            <h3 class="mb-0">Unidades vinculadas</h3>

                            <button type="button"
                                    class="waves-effect waves-light btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAddUnidade">
                              Adicionar Unidade
                            </button>
                          </div>

                          <div class="table-responsive">
                            <table class="table">
                              <thead class="bg-primary">
                                <tr>
                                  <th style="min-width:260px;">Unidade</th>
                                  <th>Inscritos</th>
                                  <th>Aprovados</th>
                                  <th>Soma Limite (Aprovados)</th>
                                  <th class="text-end">Ações</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse(($unidades ?? []) as $u)
                                  @php
                                    $v = (float)($u->soma_limite_aprovados ?? 0);
                                    $vBR = 'R$ ' . number_format($v, 2, ',', '.');
                                    $vinculoId = $u->vinculo_id ?? $u->id;
                                  @endphp
                                  <tr>
                                    <td>{{ $u->filial_nome_fantasia ?? '—' }}</td>
                                    <td>{{ (int)($u->inscritos_count ?? 0) }}</td>
                                    <td>{{ (int)($u->aprovados_count ?? 0) }}</td>
                                    <td>{{ $vBR }}</td>
                                    <td class="text-end">
                                      {{-- ✅ EXCLUIR via form padrão + intent --}}
                                      <form method="POST"
                                            action="{{ route('beneficios.bolsa.unidades.destroy', ['sub' => $sub, 'id' => $processo->id, 'vinculo_id' => $vinculoId]) }}"
                                            class="d-inline js-form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="_delete_intent" value="1">

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
                                    <td colspan="5" class="text-center text-muted py-4">Nenhuma unidade vinculada.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                      {{-- TAB 3 - SOLICITANTES --}}
                      <div class="tab-pane" id="tab-solicitantes" role="tabpanel">
                        <div class="p-15">
                          <div class="d-flex justify-content-between align-items-center mb-15">
                            <h3 class="mb-0">Solicitantes</h3>

                            <button type="button"
                                    class="waves-effect waves-light btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAddSolicitante">
                              Adicionar Solicitante
                            </button>
                          </div>

                          <div class="table-responsive">
                            <table class="table">
                              <thead class="bg-primary">
                                <tr>
                                  <th style="min-width:240px;">Colaborador</th>
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
                                    <td>{{ $s->filial_nome_fantasia ?? '—' }}</td>
                                    <td><span class="{{ $stLabel[1] }}">{{ $stLabel[0] }}</span></td>
                                    <td class="text-end">
                                      <form method="POST"
                                            action="{{ route('beneficios.bolsa.solicitantes.destroy', ['sub' => $sub, 'id' => $processo->id, 'solicitacao_id' => $s->id]) }}"
                                            class="d-inline js-form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="_delete_intent" value="1">

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
                                    <td colspan="6" class="text-center text-muted py-4">Nenhum solicitante cadastrado.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                      {{-- TAB 4 - DOCUMENTOS --}}
                      <div class="tab-pane" id="tab-documentos" role="tabpanel">
                        <div class="p-15">
                          <h3>Documentos</h3>
                          <div class="alert alert-info">
                            Área reservada para anexos e documentos do processo.
                          </div>
                        </div>
                      </div>

                      {{-- TAB 5 - CONFIG --}}
                      <div class="tab-pane" id="tab-config" role="tabpanel">
                        <div class="p-15">
                          <h3>Configuração</h3>
                          <div class="alert alert-info">
                            Área reservada para parâmetros extras do processo (campos futuros).
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>

                  {{-- ✅ Salvar fora das abas --}}
                  <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="waves-effect waves-light btn bg-gradient-success">
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

  @includeIf('partials.footer')
</div>

{{-- MODAL - ADICIONAR UNIDADE --}}
<div class="modal fade" id="modalAddUnidade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('beneficios.bolsa.unidades.store', ['sub' => $sub, 'id' => $processo->id]) }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Adicionar Unidade</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Unidade (Filial)</label>
            <select name="filial_id" class="form-control" required>
              <option value="">Selecione...</option>
              @foreach(($filiais ?? []) as $f)
                <option value="{{ $f->id }}">{{ $f->nome_fantasia ?? $f->razao_social }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn bg-gradient-success">Salvar</button>
        </div>

      </form>
    </div>
  </div>
</div>

{{-- MODAL - ADICIONAR SOLICITANTE --}}
<div class="modal fade" id="modalAddSolicitante" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST"
            id="formAddSolicitante"
            action="{{ route('beneficios.bolsa.solicitantes.store', ['sub' => $sub, 'id' => $processo->id]) }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Adicionar Solicitante</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="row">
            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Matrícula</label>
                <input type="text" id="matricula" class="form-control" placeholder="Digite a matrícula" autocomplete="off">
              </div>
            </div>

            <div class="col-md-8 col-12">
              <div class="form-group">
                <label class="form-label">Colaborador</label>
                <input type="text" id="colaborador_nome" class="form-control" disabled>
                <input type="hidden" name="colaborador_id" id="colaborador_id">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-8 col-12">
              <div class="form-group">
                <label class="form-label">Filial</label>
                <input type="text" id="filial_nome" class="form-control" disabled>
                <input type="hidden" name="filial_id" id="filial_id">
              </div>
            </div>

            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Valor Total Mensalidade</label>
                <input type="number" step="0.01" min="0" name="valor_total_mensalidade" class="form-control" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label class="form-label">Entidade</label>
                <input type="text" id="entidade_nome" name="entidade_nome" class="form-control" list="dlEntidades" placeholder="Digite para buscar..." autocomplete="off" required>
                <datalist id="dlEntidades"></datalist>
                <input type="hidden" id="entidade_id" name="entidade_id">
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group">
                <label class="form-label">Curso</label>
                <input type="text" id="curso_nome" name="curso_nome" class="form-control" list="dlCursos" placeholder="Digite para buscar..." autocomplete="off" required>
                <datalist id="dlCursos"></datalist>
                <input type="hidden" id="curso_id" name="curso_id">
              </div>
            </div>
          </div>

          <div id="modalAddSolicitanteAlert" class="mt-2"></div>

        </div>

        <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn bg-gradient-success">Salvar</button>
        </div>

      </form>
    </div>
  </div>
</div>

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- ✅ WYSIHTML5/CKEditor do seu template (mesmo do forms_editors.html) -->
<script src="{{ asset('assets/vendor_components/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js') }}"></script>
<script src="{{ asset('assets/js/pages/editor.js') }}"></script>

<!-- Coup Admin App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<!-- Sweatalert delete padrão -->
<script src="{{ asset('assets/js/app-delete-confirm.js') }}"></script>

<script>
  if (window.feather) feather.replace();

  // ====== WYSIHTML5 “pt-BR” (não há locale nativo neste plugin do template) ======
  // Então traduzimos tooltips após inicialização.
  function traduzTooltipsWysi(){
    const map = {
      "Bold": "Negrito",
      "Italic": "Itálico",
      "Underline": "Sublinhado",
      "Insert unordered list": "Lista (marcadores)",
      "Insert ordered list": "Lista (numerada)",
      "Outdent": "Diminuir recuo",
      "Indent": "Aumentar recuo",
      "Insert link": "Inserir link",
      "Insert image": "Inserir imagem",
      "View source": "Ver código",
      "Font styles": "Estilos de fonte",
      "Font size": "Tamanho da fonte",
      "Text color": "Cor do texto"
    };

    // tenta pegar botões do toolbar
    document.querySelectorAll('.wysihtml5-toolbar [title]').forEach(el => {
      const t = (el.getAttribute('title') || '').trim();
      if (map[t]) el.setAttribute('title', map[t]);
    });
  }

  // ====== Cálculos Tab Processo ======
  function formatBRL(v){
    try {
      return new Intl.NumberFormat('pt-BR', { style:'currency', currency:'BRL' }).format(v);
    } catch(e){
      const n = (v || 0).toFixed(2).replace('.', ',');
      return 'R$ ' + n;
    }
  }

  function calcTotais(){
    const valorMes = parseFloat(document.getElementById('valor_mes')?.value || '0') || 0;
    const qtdMeses = parseInt(document.getElementById('quant_meses')?.value || '0', 10) || 0;
    const total = valorMes * qtdMeses;

    const usado = parseFloat(document.getElementById('valor_usado_hidden')?.value || '0') || 0;
    const saldo = total - usado;

    const totalEl = document.getElementById('valor_total_calc');
    const saldoEl = document.getElementById('saldo_calc');
    const usadoEl = document.getElementById('valor_usado');
    const hiddenTotal = document.getElementById('orcamento_total_hidden');

    if (totalEl) totalEl.value = formatBRL(total);
    if (hiddenTotal) hiddenTotal.value = total.toFixed(2);
    if (usadoEl) usadoEl.value = formatBRL(usado);
    if (saldoEl) saldoEl.value = formatBRL(saldo);
  }

  // ====== Modal solicitante: busca matrícula + autocomplete ======
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const ROUTE_COLAB = @json(route('beneficios.bolsa.colaborador_por_matricula', ['sub' => $sub]));
  const ROUTE_ENT   = @json(route('beneficios.bolsa.entidades.search', ['sub' => $sub]));
  const ROUTE_CUR   = @json(route('beneficios.bolsa.cursos.search', ['sub' => $sub]));

  let tMat = null, tEnt = null, tCur = null;

  function setModalAlert(html){
    const el = document.getElementById('modalAddSolicitanteAlert');
    if (el) el.innerHTML = html || '';
  }

  async function fetchJSON(url){
    const res = await fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': CSRF
      }
    });
    return await res.json();
  }

  async function buscaColaborador(){
    const mat = (document.getElementById('matricula')?.value || '').trim();
    if (!mat) return;

    try {
      const j = await fetchJSON(ROUTE_COLAB + '?matricula=' + encodeURIComponent(mat));
      if (!j.ok) {
        document.getElementById('colaborador_id').value = '';
        document.getElementById('colaborador_nome').value = '';
        document.getElementById('filial_id').value = '';
        document.getElementById('filial_nome').value = '';
        setModalAlert('<div class="alert alert-warning py-2 mb-0">'+ (j.message || 'Colaborador não encontrado.') +'</div>');
        return;
      }

      document.getElementById('colaborador_id').value = j.colaborador?.id || '';
      document.getElementById('colaborador_nome').value = j.colaborador?.nome || '';
      document.getElementById('filial_id').value = j.filial?.id || '';
      document.getElementById('filial_nome').value = j.filial?.nome || '';
      setModalAlert('');
    } catch(e) {
      setModalAlert('<div class="alert alert-danger py-2 mb-0">Erro ao buscar colaborador.</div>');
    }
  }

  async function carregaEntidades(){
    const q = (document.getElementById('entidade_nome')?.value || '').trim();
    const dl = document.getElementById('dlEntidades');
    if (!dl) return;

    try {
      const j = await fetchJSON(ROUTE_ENT + '?q=' + encodeURIComponent(q));
      dl.innerHTML = '';
      (j.items || []).forEach(it => {
        const opt = document.createElement('option');
        opt.value = it.nome;
        opt.dataset.id = it.id;
        dl.appendChild(opt);
      });
    } catch(e){}
  }

  async function carregaCursos(){
    const entidadeId = (document.getElementById('entidade_id')?.value || '').trim();
    const q = (document.getElementById('curso_nome')?.value || '').trim();
    const dl = document.getElementById('dlCursos');
    if (!dl) return;

    dl.innerHTML = '';
    if (!entidadeId) return;

    try {
      const j = await fetchJSON(ROUTE_CUR + '?entidade_id=' + encodeURIComponent(entidadeId) + '&q=' + encodeURIComponent(q));
      (j.items || []).forEach(it => {
        const opt = document.createElement('option');
        opt.value = it.nome;
        opt.dataset.id = it.id;
        dl.appendChild(opt);
      });
    } catch(e){}
  }

  function resolveDatalistId(inputId, datalistId, hiddenId){
    const input = document.getElementById(inputId);
    const dl = document.getElementById(datalistId);
    const hidden = document.getElementById(hiddenId);
    if (!input || !dl || !hidden) return;

    const val = (input.value || '').trim();
    hidden.value = '';

    // se o valor bater com option, pega o id (quando tiver)
    const opt = Array.from(dl.options).find(o => (o.value || '').trim() === val);
    if (opt && opt.dataset && opt.dataset.id) hidden.value = opt.dataset.id;
  }

  document.addEventListener('DOMContentLoaded', function(){
    // Cálculo financeiro
    document.getElementById('valor_mes')?.addEventListener('input', calcTotais);
    document.getElementById('quant_meses')?.addEventListener('input', calcTotais);
    calcTotais();

    // WYSIHTML5 tooltip pt
    setTimeout(traduzTooltipsWysi, 500);

    // matrícula debounce
    document.getElementById('matricula')?.addEventListener('input', function(){
      clearTimeout(tMat);
      tMat = setTimeout(buscaColaborador, 350);
    });

    // entidade debounce + resolve id
    document.getElementById('entidade_nome')?.addEventListener('input', function(){
      clearTimeout(tEnt);
      document.getElementById('entidade_id').value = '';
      document.getElementById('curso_id').value = '';
      // ao trocar entidade, limpar curso
      document.getElementById('curso_nome').value = '';
      tEnt = setTimeout(carregaEntidades, 250);
    });
    document.getElementById('entidade_nome')?.addEventListener('change', function(){
      resolveDatalistId('entidade_nome','dlEntidades','entidade_id');
      // após escolher entidade, sugerir cursos
      carregaCursos();
    });

    // curso debounce + resolve id
    document.getElementById('curso_nome')?.addEventListener('input', function(){
      clearTimeout(tCur);
      document.getElementById('curso_id').value = '';
      tCur = setTimeout(carregaCursos, 250);
    });
    document.getElementById('curso_nome')?.addEventListener('change', function(){
      resolveDatalistId('curso_nome','dlCursos','curso_id');
    });

    // validar antes de enviar: precisa colaborador/filial preenchidos
    document.getElementById('formAddSolicitante')?.addEventListener('submit', function(e){
      const colId = (document.getElementById('colaborador_id')?.value || '').trim();
      const filialId = (document.getElementById('filial_id')?.value || '').trim();
      if (!colId || !filialId) {
        e.preventDefault();
        setModalAlert('<div class="alert alert-warning py-2 mb-0">Informe uma matrícula válida para preencher colaborador e filial.</div>');
      }
    });

    // ao abrir modal, limpar alert e campos de ids
    const modalEl = document.getElementById('modalAddSolicitante');
    if (modalEl) {
      modalEl.addEventListener('shown.bs.modal', function(){
        setModalAlert('');
        document.getElementById('matricula').focus();
      });
      modalEl.addEventListener('hidden.bs.modal', function(){
        setModalAlert('');
      });
    }
  });
</script>

</body>
</html>
