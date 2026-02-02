{{-- resources/views/beneficios/bolsa/edit.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Bolsa de Estudos</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  <style>
    .vtabs { display: flex; width: 100%; }
    .vtabs > .nav.tabs-vertical { flex: 0 0 260px; min-width: 260px; }
    .vtabs > .tab-content { flex: 1 1 auto; width: 100%; }
    @media (max-width: 991.98px){
      .vtabs { display: block; }
      .vtabs > .nav.tabs-vertical { min-width: 100%; flex: 0 0 auto; }
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('error') }}
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                          <span><i data-feather="user" class="me-10"></i>Solicitantes</span>
                        </a>
                      </li>
                    </ul>

                    <div class="tab-content">

                      {{-- TAB 1 - PROCESSO --}}
                      <div class="tab-pane active" id="tab-processo" role="tabpanel">
                        <div class="p-15">

                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Ciclo</label>
                                <input type="text"
                                       name="ciclo"
                                       class="form-control"
                                       value="{{ old('ciclo', $processo->ciclo) }}"
                                       maxlength="60"
                                       required>
                              </div>
                            </div>
                          </div>

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

                          <div class="row">
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Início das Inscrições</label>
                                <input type="datetime-local"
                                       name="inscricoes_inicio_at"
                                       class="form-control"
                                       value="{{ old('inscricoes_inicio_at', $processo->inscricoes_inicio_at ? \Carbon\Carbon::parse($processo->inscricoes_inicio_at)->format('Y-m-d\TH:i') : null) }}">
                              </div>
                            </div>
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Fim das Inscrições</label>
                                <input type="datetime-local"
                                       name="inscricoes_fim_at"
                                       class="form-control"
                                       value="{{ old('inscricoes_fim_at', $processo->inscricoes_fim_at ? \Carbon\Carbon::parse($processo->inscricoes_fim_at)->format('Y-m-d\TH:i') : null) }}">
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Orçamento</label>
                                <input type="number" step="0.01" min="0"
                                       name="orcamento_total"
                                       class="form-control"
                                       value="{{ old('orcamento_total', $processo->orcamento_total ?? null) }}">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Duração (meses)</label>
                                <input type="number" min="0"
                                       name="meses_duracao"
                                       class="form-control"
                                       value="{{ old('meses_duracao', $processo->meses_duracao ?? 0) }}">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Orçamento Mensal</label>
                                <input type="number" step="0.01" min="0"
                                       name="orcamento_mensal"
                                       class="form-control"
                                       value="{{ old('orcamento_mensal', $processo->orcamento_mensal ?? 0) }}">
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
                                  @endphp
                                  <tr>
                                    <td>{{ $u->filial_nome_fantasia ?? $u->nome_fantasia ?? '—' }}</td>
                                    <td>{{ (int)($u->inscritos_count ?? 0) }}</td>
                                    <td>{{ (int)($u->aprovados_count ?? 0) }}</td>
                                    <td>{{ $vBR }}</td>
                                    <td class="text-end">
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
                                    <td>{{ $s->filial_nome_fantasia ?? $s->filial_nome ?? '—' }}</td>
                                    <td><span class="{{ $stLabel[1] }}">{{ $stLabel[0] }}</span></td>
                                    <td class="text-end">
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
                                    <td colspan="6" class="text-center text-muted py-4">Nenhum solicitante cadastrado.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>

                        </div>
                      </div>

                    </div>
                  </div>

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
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
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
      <form method="POST" id="formAddSolicitante"
            action="{{ route('beneficios.bolsa.solicitantes.store', ['sub' => $sub, 'id' => $processo->id]) }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Adicionar Solicitante</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          <div class="row">

            {{-- Matrícula + busca --}}
            <div class="col-md-4 col-12">
              <div class="form-group">
                <label class="form-label">Matrícula</label>
                <input type="text" id="matricula" name="matricula"
                       class="form-control" placeholder="Digite a matrícula" required>
                <small id="matriculaHelp" class="text-muted"></small>
              </div>
            </div>

            <div class="col-md-8 col-12">
              <div class="form-group">
                <label class="form-label">Nome</label>
                <input type="text" id="colaborador_nome" class="form-control" readonly>
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group">
                <label class="form-label">Filial</label>
                <input type="text" id="filial_nome" class="form-control" readonly>
              </div>
            </div>

            <div class="col-md-6 col-12">
              <div class="form-group">
                <label class="form-label">Valor total mensalidade</label>
                <input type="number" step="0.01" min="0" name="valor_total_mensalidade"
                       class="form-control" placeholder="0,00" required>
              </div>
            </div>

            {{-- Entidade (busca + cria se não existir) --}}
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label class="form-label">Entidade (Universidade)</label>
                <input type="text" id="entidade_nome" name="entidade_nome"
                       class="form-control" list="entidadesList"
                       placeholder="Digite para buscar ou criar" required>
                <datalist id="entidadesList"></datalist>
                <small class="text-muted">Se não existir, será cadastrada como <b>aprovado = 0</b>.</small>
              </div>
            </div>

            {{-- Curso (dependente da entidade + cria se não existir) --}}
            <div class="col-md-6 col-12">
              <div class="form-group">
                <label class="form-label">Curso</label>
                <input type="text" id="curso_nome" name="curso_nome"
                       class="form-control" list="cursosList"
                       placeholder="Digite para buscar ou criar" required>
                <datalist id="cursosList"></datalist>
                <small class="text-muted">Se não existir para a entidade, será cadastrado como <b>aprovado = 0</b>.</small>
              </div>
            </div>

          </div>

          {{-- Hidden IDs preenchidos automaticamente --}}
          <input type="hidden" id="colaborador_id" name="colaborador_id">
          <input type="hidden" id="filial_id" name="filial_id">
          <input type="hidden" id="entidade_id" name="entidade_id">
          <input type="hidden" id="curso_id" name="curso_id">

        </div>

        <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" id="btnSalvarSolicitante" class="btn bg-gradient-success" disabled>Salvar</button>
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

<script src="{{ asset('assets/js/app-delete-confirm.js') }}"></script>
<script src="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js') }}"></script>

<script>
  if (window.feather) feather.replace();

  // URLs AJAX
  window.CON_SUB = @json($sub);
  window.CON_LOOKUP_COLAB = @json(route('beneficios.bolsa.colaborador.lookup', ['sub' => $sub]));
  window.CON_SEARCH_ENTIDADES = @json(route('beneficios.bolsa.entidades.search', ['sub' => $sub]));
  window.CON_SEARCH_CURSOS = @json(route('beneficios.bolsa.cursos.search', ['sub' => $sub]));

  // WYSIHTML5 PT-BR inline
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $.fn.wysihtml5) {
      $.fn.wysihtml5.locale = $.fn.wysihtml5.locale || {};
      $.fn.wysihtml5.locale["pt-BR"] = {
        font_styles: { normal: "Texto normal", h1: "Título 1", h2: "Título 2", h3: "Título 3" },
        emphasis: { bold: "Negrito", italic: "Itálico", underline: "Sublinhado" },
        lists: { unordered: "Lista", ordered: "Lista numerada", outdent: "Diminuir recuo", indent: "Aumentar recuo" },
        link: { insert: "Inserir link", cancel: "Cancelar" },
        image: { insert: "Inserir imagem", cancel: "Cancelar" },
        html: { edit: "Editar HTML" },
        colours: { black: "Preto", silver: "Prata", gray: "Cinza", maroon: "Marrom", red: "Vermelho", purple: "Roxo", green: "Verde", olive: "Oliva", navy: "Azul marinho", blue: "Azul", orange: "Laranja" }
      };
      $('.textarea').wysihtml5({ locale: "pt-BR" });
    }
  });

  // --- Helpers ---
  function debounce(fn, wait){
    let t = null;
    return function(...args){
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    }
  }

  function setSubmitEnabled(){
    const okColab = !!document.getElementById('colaborador_id').value;
    const okFilial = !!document.getElementById('filial_id').value;
    const entNome = (document.getElementById('entidade_nome').value || '').trim();
    const cursoNome = (document.getElementById('curso_nome').value || '').trim();
    document.getElementById('btnSalvarSolicitante').disabled = !(okColab && okFilial && entNome && cursoNome);
  }

  // --- Colaborador por matrícula ---
  const matriculaEl = document.getElementById('matricula');
  const helpEl = document.getElementById('matriculaHelp');

  const lookupColab = debounce(async () => {
    const m = (matriculaEl.value || '').trim();
    helpEl.textContent = '';
    document.getElementById('colaborador_id').value = '';
    document.getElementById('filial_id').value = '';
    document.getElementById('colaborador_nome').value = '';
    document.getElementById('filial_nome').value = '';

    setSubmitEnabled();

    if (m.length < 2) return;

    try{
      const url = new URL(window.CON_LOOKUP_COLAB, window.location.origin);
      url.searchParams.set('matricula', m);

      const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Falha na busca');

      const data = await res.json();
      if (!data || !data.ok) {
        helpEl.textContent = data?.message || 'Colaborador não encontrado.';
        helpEl.className = 'text-danger';
        setSubmitEnabled();
        return;
      }

      document.getElementById('colaborador_id').value = data.colaborador.id;
      document.getElementById('filial_id').value = data.filial?.id || '';
      document.getElementById('colaborador_nome').value = data.colaborador.nome || '';
      document.getElementById('filial_nome').value = data.filial?.nome || '';

      helpEl.textContent = 'Colaborador localizado.';
      helpEl.className = 'text-success';

      setSubmitEnabled();
    }catch(e){
      console.error(e);
      helpEl.textContent = 'Erro ao buscar colaborador.';
      helpEl.className = 'text-danger';
      setSubmitEnabled();
    }
  }, 350);

  matriculaEl.addEventListener('input', lookupColab);

  // --- Entidades (datalist + id hidden) ---
  const entidadesMap = new Map(); // nome -> id
  const entidadeNomeEl = document.getElementById('entidade_nome');
  const entidadeIdEl = document.getElementById('entidade_id');
  const entidadesListEl = document.getElementById('entidadesList');

  const cursosMap = new Map(); // nome -> id
  const cursoNomeEl = document.getElementById('curso_nome');
  const cursoIdEl = document.getElementById('curso_id');
  const cursosListEl = document.getElementById('cursosList');

  function clearCursos(){
    cursosMap.clear();
    cursosListEl.innerHTML = '';
    cursoIdEl.value = '';
    cursoNomeEl.value = '';
  }

  const searchEntidades = debounce(async () => {
    const q = (entidadeNomeEl.value || '').trim();
    entidadeIdEl.value = '';
    entidadesMap.clear();
    entidadesListEl.innerHTML = '';
    clearCursos();
    setSubmitEnabled();

    if (q.length < 2) return;

    try{
      const url = new URL(window.CON_SEARCH_ENTIDADES, window.location.origin);
      url.searchParams.set('q', q);

      const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();

      (data.items || []).forEach(it => {
        entidadesMap.set(it.nome, it.id);
        const opt = document.createElement('option');
        opt.value = it.nome;
        entidadesListEl.appendChild(opt);
      });
    }catch(e){ console.error(e); }
  }, 300);

  entidadeNomeEl.addEventListener('input', searchEntidades);

  entidadeNomeEl.addEventListener('change', () => {
    const nome = (entidadeNomeEl.value || '').trim();
    const id = entidadesMap.get(nome);
    entidadeIdEl.value = id ? String(id) : '';
    clearCursos(); // sempre refaz cursos ao “fixar” entidade
    setSubmitEnabled();
  });

  // --- Cursos (dependente da entidade) ---
  const searchCursos = debounce(async () => {
    const q = (cursoNomeEl.value || '').trim();
    cursoIdEl.value = '';
    cursosMap.clear();
    cursosListEl.innerHTML = '';
    setSubmitEnabled();

    if (q.length < 2) return;

    const entidadeId = entidadeIdEl.value; // se vazio, pode criar curso depois, mas busca precisa do id
    if (!entidadeId) return;

    try{
      const url = new URL(window.CON_SEARCH_CURSOS, window.location.origin);
      url.searchParams.set('entidade_id', entidadeId);
      url.searchParams.set('q', q);

      const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();

      (data.items || []).forEach(it => {
        cursosMap.set(it.nome, it.id);
        const opt = document.createElement('option');
        opt.value = it.nome;
        cursosListEl.appendChild(opt);
      });
    }catch(e){ console.error(e); }
  }, 300);

  cursoNomeEl.addEventListener('input', searchCursos);

  cursoNomeEl.addEventListener('change', () => {
    const nome = (cursoNomeEl.value || '').trim();
    const id = cursosMap.get(nome);
    cursoIdEl.value = id ? String(id) : '';
    setSubmitEnabled();
  });

  // habilita/desabilita salvar ao abrir modal
  document.getElementById('modalAddSolicitante').addEventListener('shown.bs.modal', () => {
    setSubmitEnabled();
  });
</script>

</body>
</html>
