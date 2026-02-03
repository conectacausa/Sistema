<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Bolsa de Estudos</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  {{-- WYSIHTML5 --}}
  <link rel="stylesheet" href="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.css') }}">

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
                    <a href="{{ route('dashboard', ['sub' => request()->route('sub')]) }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item">Bolsa de Estudos</li>
                  <li class="breadcrumb-item active">Editar</li>
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
                <h4 class="box-title">Editar Processo</h4>
              </div>

              <div class="box-body">

                <form id="form-processo" method="POST"
                      action="{{ route('beneficios.bolsa.update', ['sub' => request()->route('sub'), 'id' => $processo->id]) }}">
                  @csrf
                  @method('PUT')

                  <div class="vtabs">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-processo" role="tab">
                          <span><i data-feather="user" class="me-10"></i>Processo</span>
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
                          <span><i data-feather="lock" class="me-10"></i>Configuração</span>
                        </a>
                      </li>
                    </ul>

                    <div class="tab-content">

                      {{-- TAB 1: PROCESSO --}}
                      <div class="tab-pane active" id="tab-processo" role="tabpanel">
                        <div class="p-15">

                          {{-- Linha 1 --}}
                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Título / Ciclo</label>
                                <input type="text" name="ciclo"
                                       class="form-control @error('ciclo') is-invalid @enderror"
                                       value="{{ old('ciclo', $processo->ciclo) }}"
                                       required>
                                @error('ciclo') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                              </div>
                            </div>
                          </div>

                          {{-- Linha 2 --}}
                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Edital</label>
                                <textarea id="edital_editor" name="edital"
                                          class="form-control @error('edital') is-invalid @enderror"
                                          rows="8">{{ old('edital', $processo->edital) }}</textarea>
                                @error('edital') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                              </div>
                            </div>
                          </div>

                          {{-- Linha 3 --}}
                          <div class="row">
                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Início Inscrições</label>
                                <input type="datetime-local" name="inscricoes_inicio_at"
                                       class="form-control"
                                       value="{{ old('inscricoes_inicio_at', $processo->inscricoes_inicio_at ? \Carbon\Carbon::parse($processo->inscricoes_inicio_at)->format('Y-m-d\TH:i') : '') }}">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Fim Inscrições</label>
                                <input type="datetime-local" name="inscricoes_fim_at"
                                       class="form-control"
                                       value="{{ old('inscricoes_fim_at', $processo->inscricoes_fim_at ? \Carbon\Carbon::parse($processo->inscricoes_fim_at)->format('Y-m-d\TH:i') : '') }}">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Status</label>
                                @php $st = (string)old('status', (string)($processo->status ?? '0')); @endphp
                                <select name="status" class="form-control">
                                  <option value="0" {{ $st==='0' ? 'selected' : '' }}>Rascunho</option>
                                  <option value="1" {{ $st==='1' ? 'selected' : '' }}>Ativo</option>
                                  <option value="2" {{ $st==='2' ? 'selected' : '' }}>Encerrado</option>
                                </select>
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Data Base</label>
                                <input type="date" name="data_base" class="form-control"
                                       value="{{ old('data_base', $processo->data_base ? \Carbon\Carbon::parse($processo->data_base)->format('Y-m-d') : '') }}">
                              </div>
                            </div>
                          </div>

                          {{-- Linha 4 --}}
                          @php
                            $valorMes = old('valor_mensal', $processo->orcamento_mensal ?? 0);
                            $meses = (int)old('meses_duracao', $processo->meses_duracao ?? 0);
                            $total = (float)$valorMes * (float)$meses;
                          @endphp
                          <div class="row">
                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Valor Mês</label>
                                <div class="input-group">
                                  <span class="input-group-text">R$</span>
                                  <input type="text" id="valor_mensal" name="valor_mensal" class="form-control"
                                         value="{{ is_numeric($valorMes) ? number_format((float)$valorMes, 2, ',', '.') : $valorMes }}">
                                </div>
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Quant. Meses</label>
                                <input type="number" id="meses_duracao" name="meses_duracao" class="form-control"
                                       min="0" max="120"
                                       value="{{ $meses }}">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Valor Total</label>
                                <div class="input-group">
                                  <span class="input-group-text">R$</span>
                                  <input type="text" id="valor_total" class="form-control" disabled
                                         value="{{ number_format((float)$total, 2, ',', '.') }}">
                                </div>
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Saldo</label>
                                <div class="input-group">
                                  <span class="input-group-text">R$</span>
                                  <input type="text" class="form-control" disabled value="{{ number_format((float)$total, 2, ',', '.') }}">
                                </div>
                                <small class="text-muted">Saldo = Valor Total - Valor Usado</small>
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>

                      {{-- TAB 2: UNIDADES --}}
                      <div class="tab-pane" id="tab-unidades" role="tabpanel">
                        <div class="p-15">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Unidades</h5>

                            <button type="button" class="btn bg-gradient-success"
                                    data-bs-toggle="modal" data-bs-target="#modalAddUnidade">
                              Adicionar Unidade
                            </button>
                          </div>

                          <div class="table-responsive">
                            <table class="table">
                              <thead class="bg-primary">
                                <tr>
                                  <th style="min-width:240px;">Unidade</th>
                                  <th>Inscritos</th>
                                  <th>Aprovados</th>
                                  <th>Soma Limite (Aprovados)</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($unidades as $u)
                                  <tr>
                                    <td>{{ $u->filial_nome }}</td>
                                    <td>{{ (int)$u->inscritos_count }}</td>
                                    <td>{{ (int)$u->aprovados_count }}</td>
                                    <td>R$ {{ number_format((float)$u->soma_limite_aprovados, 2, ',', '.') }}</td>
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Nenhuma unidade vinculada.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>

                        </div>
                      </div>

                      {{-- TAB 3: SOLICITANTES --}}
                      <div class="tab-pane" id="tab-solicitantes" role="tabpanel">
                        <div class="p-15">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Solicitantes</h5>

                            <button type="button" class="btn bg-gradient-success"
                                    data-bs-toggle="modal" data-bs-target="#modalAddSolicitante">
                              Adicionar Solicitante
                            </button>
                          </div>

                          <div class="table-responsive">
                            <table class="table">
                              <thead class="bg-primary">
                                <tr>
                                  <th style="min-width:240px;">Colaborador</th>
                                  <th>Filial</th>
                                  <th>Entidade</th>
                                  <th>Curso</th>
                                  <th>Situação</th>
                                  <th>Mensalidade</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($solicitantes as $s)
                                  @php
                                    $st = match((int)$s->status){
                                      0 => 'Digitação',
                                      1 => 'Reprovado',
                                      2 => 'Aprovado',
                                      3 => 'Em análise',
                                      default => (string)$s->status
                                    };
                                  @endphp
                                  <tr>
                                    <td>{{ $s->colaborador_nome }}</td>
                                    <td>{{ $s->filial_nome ?? '—' }}</td>
                                    <td>{{ $s->entidade_nome ?? '—' }}</td>
                                    <td>{{ $s->curso_nome ?? '—' }}</td>
                                    <td>{{ $st }}</td>
                                    <td>R$ {{ number_format((float)($s->valor_total_mensalidade ?? 0), 2, ',', '.') }}</td>
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Nenhuma solicitação encontrada.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>

                        </div>
                      </div>

                      {{-- TAB 4: DOCUMENTOS --}}
                      <div class="tab-pane" id="tab-documentos" role="tabpanel">
                        <div class="p-15">

                          <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Documentos</h5>
                            <button type="button" class="btn bg-gradient-success"
                                    data-bs-toggle="modal" data-bs-target="#modalAddDocumento">
                              Adicionar Documento
                            </button>
                          </div>

                          <div class="row align-items-end">
                            <div class="col-md-7 col-12">
                              <div class="form-group">
                                <label class="form-label">Filtrar</label>
                                <input type="text" id="doc_filter_input" class="form-control"
                                       value="{{ $docQ }}"
                                       placeholder="Buscar por título ou colaborador...">
                              </div>
                            </div>

                            <div class="col-md-5 col-12">
                              <div class="form-group">
                                <label class="form-label">Status</label>
                                <select id="doc_status_select" class="form-control">
                                  <option value="">Todos</option>
                                  <option value="0" {{ $docStatus==='0' ? 'selected' : '' }}>Aguardando aprovação</option>
                                  <option value="1" {{ $docStatus==='1' ? 'selected' : '' }}>Reprovado</option>
                                  <option value="2" {{ $docStatus==='2' ? 'selected' : '' }}>Aprovado</option>
                                </select>
                              </div>
                            </div>
                          </div>

                          <div id="docsWrapper">
                            @include('beneficios.bolsa.partials._docs_table', ['documentos' => $documentos])
                          </div>

                        </div>
                      </div>

                      {{-- TAB 5: CONFIGURAÇÃO --}}
                      <div class="tab-pane" id="tab-config" role="tabpanel">
                        <div class="p-15">
                          <h5 class="mb-3">Configuração</h5>

                          <div class="row">
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Enviar Lembrete do Recibo?</label>
                                @php
                                  $lem = (string)old('lembrete_recibo_ativo', (int)($processo->lembrete_recibo_ativo ?? 0));
                                @endphp
                                <select name="lembrete_recibo_ativo" class="form-control">
                                  <option value="0" {{ $lem==='0' ? 'selected' : '' }}>Não</option>
                                  <option value="1" {{ $lem==='1' ? 'selected' : '' }}>Sim</option>
                                </select>
                              </div>
                            </div>

                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Quantos dias antes do vencimento?</label>
                                <input type="number" name="lembrete_recibo_dias_antes" class="form-control"
                                       min="0" max="365"
                                       value="{{ old('lembrete_recibo_dias_antes', $processo->lembrete_recibo_dias_antes ?? '') }}">
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>

                    </div>
                  </div>

                  {{-- Botão salvar fora das abas --}}
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

{{-- MODAL: Adicionar Unidade --}}
<div class="modal fade" id="modalAddUnidade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('beneficios.bolsa.unidades.store', ['sub'=>request()->route('sub'),'id'=>$processo->id]) }}">
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
              @foreach($filiaisDisponiveis as $f)
                <option value="{{ $f->id }}">{{ $f->nome_fantasia ?: $f->razao_social }}</option>
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

{{-- MODAL: Adicionar Solicitante --}}
<div class="modal fade" id="modalAddSolicitante" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('beneficios.bolsa.solicitantes.store', ['sub'=>request()->route('sub'),'id'=>$processo->id]) }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Adicionar Solicitante</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="colaborador_id" id="sol_colaborador_id">

          <div class="row">
            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Matrícula</label>
                <input type="text" id="sol_matricula" class="form-control" placeholder="Digite a matrícula" autocomplete="off">
                <small id="sol_matricula_msg" class="text-danger d-none">Colaborador não encontrado.</small>
              </div>
            </div>

            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Nome</label>
                <input type="text" id="sol_nome" class="form-control" disabled>
              </div>
            </div>

            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Filial</label>
                <input type="text" id="sol_filial" class="form-control" disabled>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label class="form-label">Entidade</label>
                <input type="text" name="entidade_nome" id="sol_entidade" class="form-control" placeholder="Digite a entidade" required>
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group">
                <label class="form-label">Curso</label>
                <input type="text" name="curso_nome" id="sol_curso" class="form-control" placeholder="Digite o curso" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Valor total mensalidade</label>
                <div class="input-group">
                  <span class="input-group-text">R$</span>
                  <input type="text" name="valor_total_mensalidade" id="sol_mensalidade" class="form-control" required>
                </div>
              </div>
            </div>
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

{{-- MODAL: Adicionar Documento --}}
<div class="modal fade" id="modalAddDocumento" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST"
            action="{{ route('beneficios.bolsa.documentos.store', ['sub'=>request()->route('sub'),'id'=>$processo->id]) }}"
            enctype="multipart/form-data">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Adicionar Documento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="row">
            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-control" required>
                  <option value="2">Documento</option>
                  <option value="1">Comprovante</option>
                </select>
              </div>
            </div>

            <div class="col-md-8 col-12">
              <div class="form-group">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" required>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Expira em</label>
                <input type="date" name="expira_em" class="form-control">
              </div>
            </div>

            <div class="col-md-8 col-12">
              <div class="form-group">
                <label class="form-label">Arquivo (opcional)</label>
                <input type="file" name="arquivo" class="form-control">
              </div>
            </div>
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

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<script src="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js') }}"></script>

<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
  if (window.feather) feather.replace();

  // ----------------------------
  // WYSIHTML5 PT-BR
  // ----------------------------
  (function(){
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.wysihtml5) return;

    jQuery.fn.wysihtml5.locale = jQuery.fn.wysihtml5.locale || {};
    jQuery.fn.wysihtml5.locale["pt-BR"] = {
      font_styles: { normal: "Texto normal", h1: "Título 1", h2: "Título 2", h3: "Título 3" },
      emphasis: { bold: "Negrito", italic: "Itálico", underline: "Sublinhado" },
      lists: { unordered: "Lista", ordered: "Lista numerada", outdent: "Diminuir recuo", indent: "Aumentar recuo" },
      link: { insert: "Inserir link", cancel: "Cancelar" },
      image: { insert: "Inserir imagem", cancel: "Cancelar" },
      html: { edit: "Editar HTML" }
    };

    jQuery('#edital_editor').wysihtml5({
      locale: "pt-BR",
      toolbar: {
        "font-styles": true,
        "emphasis": true,
        "lists": true,
        "html": true,
        "link": true,
        "image": false
      }
    });
  })();

  // ----------------------------
  // Dinheiro + total automático
  // ----------------------------
  function parseBRL(str){
    str = String(str||'').replace('R$', '').trim();
    str = str.replace(/\./g, '').replace(',', '.');
    const n = parseFloat(str);
    return isNaN(n) ? 0 : n;
  }
  function formatBRL(n){
    try {
      return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
    } catch(e){
      return (n||0).toFixed(2).replace('.', ',');
    }
  }
  function recalcTotal(){
    const v = parseBRL(document.getElementById('valor_mensal')?.value);
    const m = parseInt(document.getElementById('meses_duracao')?.value || '0', 10) || 0;
    const total = v * m;
    const el = document.getElementById('valor_total');
    if (el) el.value = formatBRL(total);
  }
  document.getElementById('valor_mensal')?.addEventListener('blur', function(){
    const v = parseBRL(this.value);
    this.value = formatBRL(v);
    recalcTotal();
  });
  document.getElementById('meses_duracao')?.addEventListener('input', recalcTotal);
  recalcTotal();

  // ----------------------------
  // Modal solicitante: matrícula -> preencher nome/filial
  // ----------------------------
  window.CON_COLAB_URL = @json(route('beneficios.bolsa.colaborador_por_matricula', ['sub' => request()->route('sub')]));

  let solTimer = null;
  const $mat = document.getElementById('sol_matricula');
  const $msg = document.getElementById('sol_matricula_msg');
  const $cid = document.getElementById('sol_colaborador_id');
  const $nome = document.getElementById('sol_nome');
  const $filial = document.getElementById('sol_filial');

  function solSetNotFound(){
    $msg?.classList.remove('d-none');
    if ($cid) $cid.value = '';
    if ($nome) $nome.value = '';
    if ($filial) $filial.value = '';
  }
  function solSetFound(data){
    $msg?.classList.add('d-none');
    if ($cid) $cid.value = data.id || '';
    if ($nome) $nome.value = data.nome || '';
    if ($filial) $filial.value = data.filial_nome || '';
  }

  $mat?.addEventListener('input', function(){
    clearTimeout(solTimer);
    const matricula = this.value.trim();
    if (!matricula) { solSetNotFound(); $msg?.classList.add('d-none'); return; }

    solTimer = setTimeout(async () => {
      try{
        const url = new URL(window.CON_COLAB_URL, window.location.origin);
        url.searchParams.set('matricula', matricula);

        const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();

        if (json && json.ok) solSetFound(json.data);
        else solSetNotFound();
      }catch(e){
        solSetNotFound();
      }
    }, 350);
  });

  // ----------------------------
  // Documentos: filtro automático via AJAX (sem botão)
  // ----------------------------
  window.CON_DOCS_GRID_URL = @json(route('beneficios.bolsa.documentos_grid', ['sub'=>request()->route('sub'), 'id'=>$processo->id]));

  const docsWrapper = document.getElementById('docsWrapper');
  const docInput = document.getElementById('doc_filter_input');
  const docSelect = document.getElementById('doc_status_select');
  let docTimer = null;

  async function loadDocsGrid(urlStr){
    const res = await fetch(urlStr, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const html = await res.text();
    if (docsWrapper) {
      docsWrapper.innerHTML = html;
      if (window.feather) feather.replace();
    }
  }

  function buildDocsUrl(pageUrl){
    const url = new URL(pageUrl || window.CON_DOCS_GRID_URL, window.location.origin);
    const q = (docInput?.value || '').trim();
    const st = (docSelect?.value || '').trim();
    if (q) url.searchParams.set('doc_q', q); else url.searchParams.delete('doc_q');
    if (st !== '') url.searchParams.set('doc_status', st); else url.searchParams.delete('doc_status');
    return url.toString();
  }

  function scheduleDocsReload(){
    clearTimeout(docTimer);
    docTimer = setTimeout(() => loadDocsGrid(buildDocsUrl()), 350);
  }

  docInput?.addEventListener('input', scheduleDocsReload);
  docSelect?.addEventListener('change', scheduleDocsReload);

  // Paginação via AJAX
  document.addEventListener('click', function(e){
    const a = e.target?.closest('#docsWrapper a');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href) return;

    e.preventDefault();
    loadDocsGrid(buildDocsUrl(href));
  });
</script>

</body>
</html>
