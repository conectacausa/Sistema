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
                  <li class="breadcrumb-item" aria-current="page">Bolsa de Estudos</li>
                  <li class="breadcrumb-item active" aria-current="page">Editar</li>
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
                                <small class="text-muted">Saldo = Valor Total - Valor Usado (vamos evoluir depois)</small>
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>

                      {{-- TAB 2: UNIDADES (mantém como está; aqui só placeholder visual) --}}
                      <div class="tab-pane" id="tab-unidades" role="tabpanel">
                        <div class="p-15">
                          <h5 class="mb-3">Unidades</h5>
                          <div class="alert alert-info">Mantido conforme está no seu projeto. (Sem alterações aqui além do layout/abas.)</div>
                        </div>
                      </div>

                      {{-- TAB 3: SOLICITANTES (mantém como está; correção principal é AJAX matrícula) --}}
                      <div class="tab-pane" id="tab-solicitantes" role="tabpanel">
                        <div class="p-15">
                          <h5 class="mb-3">Solicitantes</h5>
                          <div class="alert alert-info">Mantido conforme está no seu projeto. (Correção aplicada no endpoint de matrícula.)</div>
                        </div>
                      </div>

                      {{-- TAB 4: DOCUMENTOS (✅ tabela + filtro + status + expiração) --}}
                      <div class="tab-pane" id="tab-documentos" role="tabpanel">
                        <div class="p-15">

                          <div class="row align-items-end">
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Filtrar</label>
                                <input type="text" id="doc_filter_input" class="form-control"
                                       value="{{ $docQ }}"
                                       placeholder="Buscar por título ou colaborador...">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
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

                            <div class="col-md-3 col-12">
                              <button type="button" class="btn btn-primary w-100" id="btn_doc_aplicar_filtro">
                                Aplicar Filtro
                              </button>
                            </div>
                          </div>

                          <div class="table-responsive">
                            <table class="table" id="docs_table">
                              <thead class="bg-primary">
                                <tr>
                                  <th style="min-width:220px;">Documento</th>
                                  <th>Tipo</th>
                                  <th>Solicitante</th>
                                  <th>Expira em</th>
                                  <th>Status</th>
                                  <th>Enviado em</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($documentos as $d)
                                  @php
                                    $tipo = ((int)$d->tipo === 1) ? 'Comprovante' : 'Documento';
                                    $exp  = !empty($d->expira_em) ? \Carbon\Carbon::parse($d->expira_em)->format('d/m/Y') : '—';
                                    $env  = !empty($d->created_at) ? \Carbon\Carbon::parse($d->created_at)->format('d/m/Y H:i') : '—';
                                    $stLabel = match((int)$d->status) {
                                      0 => 'Aguardando',
                                      1 => 'Reprovado',
                                      2 => 'Aprovado',
                                      default => (string)$d->status
                                    };
                                  @endphp
                                  <tr>
                                    <td>{{ $d->titulo }}</td>
                                    <td>{{ $tipo }}</td>
                                    <td>{{ $d->colaborador_nome ?? '—' }}</td>
                                    <td>{{ $exp }}</td>
                                    <td>{{ $stLabel }}</td>
                                    <td>{{ $env }}</td>
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Nenhum documento vinculado a este processo.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>

                          <div class="mt-3">
                            {{ $documentos->withQueryString()->links() }}
                          </div>

                        </div>
                      </div>

                      {{-- TAB 5: CONFIGURAÇÃO (✅ campos criados) --}}
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

                          <div class="alert alert-info">
                            Outros campos de configuração serão adicionados aqui futuramente.
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>

                  {{-- ✅ Botão salvar fora das abas --}}
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

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<script src="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js') }}"></script>

<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
  if (window.feather) feather.replace();

  // ----------------------------
  // 1) WYSIHTML5 em português
  // (o pacote do template não vem com locale pt-BR, então definimos aqui)
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
      html: { edit: "Editar HTML" },
      colours: { black: "Preto", silver: "Prata", gray: "Cinza", maroon: "Marrom", red: "Vermelho",
        purple: "Roxo", green: "Verde", olive: "Oliva", navy: "Marinho", blue: "Azul", orange: "Laranja" }
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
  // 2) Dinheiro (Valor Mês) e total automático
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
  // 3) Tab Documentos: filtros (reload com querystring)
  // ----------------------------
  document.getElementById('btn_doc_aplicar_filtro')?.addEventListener('click', function(){
    const q = document.getElementById('doc_filter_input')?.value || '';
    const st = document.getElementById('doc_status_select')?.value || '';

    const url = new URL(window.location.href);
    url.searchParams.set('doc_q', q);
    if (st === '') url.searchParams.delete('doc_status');
    else url.searchParams.set('doc_status', st);

    // volta para primeira página dos docs
    url.searchParams.delete('docs_page');

    window.location.href = url.toString();
  });

  // ----------------------------
  // 4) Endpoint matrícula (para seu modal existente)
  // Deixa as variáveis globais disponíveis
  // ----------------------------
  window.CON_SUB = @json((string)request()->route('sub'));
  window.CON_COLAB_URL = @json(route('beneficios.bolsa.colaborador_por_matricula', ['sub' => request()->route('sub')]));
</script>

</body>
</html>
