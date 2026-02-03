<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Aprovar Bolsa</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

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
            <h4 class="page-title">Aprovar Solicitação</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => $sub]) }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item">Bolsa de Estudos</li>
                  <li class="breadcrumb-item active" aria-current="page">Aprovação</li>
                </ol>
              </nav>
            </div>
          </div>

          <a class="waves-effect waves-light btn mb-5 bg-gradient-primary"
             href="{{ route('beneficios.bolsa.aprovacoes.index', ['sub'=>$sub,'processo_id'=>$processo->id]) }}">
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
                <h4 class="box-title">{{ $processo->ciclo }} — Solicitação #{{ (int)$sol->id }}</h4>
              </div>

              <div class="box-body">
                <div class="vtabs">
                  <ul class="nav nav-tabs tabs-vertical" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" data-bs-toggle="tab" href="#tab-prof" role="tab">
                        <span><i data-feather="user" class="me-10"></i>Profissional</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" data-bs-toggle="tab" href="#tab-aprov" role="tab">
                        <span><i data-feather="lock" class="me-10"></i>Aprovação</span>
                      </a>
                    </li>
                  </ul>

                  <div class="tab-content">

                    <div class="tab-pane active" id="tab-prof" role="tabpanel">
                      <div class="p-15">

                        <div class="row">
                          <div class="col-md-6 col-12">
                            <div class="form-group">
                              <label class="form-label">Nome</label>
                              <input type="text" class="form-control" value="{{ $sol->colaborador_nome ?? '—' }}" disabled>
                            </div>
                          </div>
                          <div class="col-md-6 col-12">
                            <div class="form-group">
                              <label class="form-label">Filial</label>
                              <input type="text" class="form-control" value="{{ $sol->filial_nome ?? '—' }}" disabled>
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-3 col-12">
                            <div class="form-group">
                              <label class="form-label">Matrícula</label>
                              <input type="text" class="form-control" value="{{ $sol->colaborador_matricula ?? '—' }}" disabled>
                            </div>
                          </div>
                          <div class="col-md-3 col-12">
                            <div class="form-group">
                              <label class="form-label">Admissão</label>
                              <input type="text" class="form-control"
                                     value="{{ !empty($sol->colaborador_data_admissao) ? \Carbon\Carbon::parse($sol->colaborador_data_admissao)->format('d/m/Y') : '—' }}"
                                     disabled>
                            </div>
                          </div>
                          <div class="col-md-3 col-12">
                            <div class="form-group">
                              <label class="form-label">Telefone</label>
                              <input type="text" class="form-control" value="{{ $sol->colaborador_telefone ?? '—' }}" disabled>
                            </div>
                          </div>
                          <div class="col-md-3 col-12">
                            <div class="form-group">
                              <label class="form-label">E-mail</label>
                              <input type="text" class="form-control" value="{{ $sol->colaborador_email ?? '—' }}" disabled>
                            </div>
                          </div>
                        </div>

                        <hr>

                        <div class="row">
                          <div class="col-md-6 col-12">
                            <div class="form-group">
                              <label class="form-label">Entidade</label>
                              <input type="text" class="form-control" value="{{ $sol->entidade_nome ?? '—' }}" disabled>
                            </div>
                          </div>
                          <div class="col-md-6 col-12">
                            <div class="form-group">
                              <label class="form-label">Curso</label>
                              <input type="text" class="form-control" value="{{ $sol->curso_nome ?? '—' }}" disabled>
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-md-4 col-12">
                            <div class="form-group">
                              <label class="form-label">Valor Mensalidade</label>
                              <input type="text" id="valor_mensalidade" class="form-control"
                                     value="{{ number_format((float)($sol->valor_total_mensalidade ?? 0), 2, ',', '.') }}"
                                     disabled>
                            </div>
                          </div>
                          <div class="col-md-4 col-12">
                            <div class="form-group">
                              <label class="form-label">Data Solicitação</label>
                              <input type="text" class="form-control"
                                     value="{{ !empty($sol->solicitacao_at) ? \Carbon\Carbon::parse($sol->solicitacao_at)->format('d/m/Y H:i') : '—' }}"
                                     disabled>
                            </div>
                          </div>
                          <div class="col-md-4 col-12">
                            <div class="form-group">
                              <label class="form-label">Status</label>
                              <input type="text" class="form-control" value="Em análise" disabled>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>

                    <div class="tab-pane" id="tab-aprov" role="tabpanel">
                      <div class="p-15">

                        <div class="row">
                          <div class="col-md-4 col-12">
                            <div class="form-group">
                              <label class="form-label">% Concessão</label>
                              <input type="number" id="percentual" class="form-control" min="0" max="100" step="0.01" value="">
                            </div>
                          </div>

                          <div class="col-md-4 col-12">
                            <div class="form-group">
                              <label class="form-label">Valor Limite (calculado)</label>
                              <input type="text" id="valor_limite_calc" class="form-control" value="R$ 0,00" disabled>
                            </div>
                          </div>

                          <div class="col-md-4 col-12">
                            <div class="form-group">
                              <label class="form-label">Valor Concessão (calculado)</label>
                              <input type="text" id="valor_concessao_calc" class="form-control" value="R$ 0,00" disabled>
                            </div>
                          </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                          <form method="POST"
                                action="{{ route('beneficios.bolsa.aprovacoes.reprovar', ['sub'=>$sub,'processo_id'=>$processo->id,'solicitacao_id'=>$sol->id]) }}"
                                class="d-inline">
                            @csrf
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalReprovar">
                              Reprovar
                            </button>
                          </form>

                          <form method="POST"
                                action="{{ route('beneficios.bolsa.aprovacoes.aprovar', ['sub'=>$sub,'processo_id'=>$processo->id,'solicitacao_id'=>$sol->id]) }}"
                                class="d-inline">
                            @csrf
                            <input type="hidden" name="percentual" id="percentual_hidden" value="">
                            <button type="submit" class="btn bg-gradient-success" id="btnAprovar" disabled>
                              Aprovar
                            </button>
                          </form>
                        </div>

                      </div>
                    </div>

                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

      </section>

    </div>
  </div>

  @includeIf('partials.footer')
</div>

{{-- Modal Reprovar --}}
<div class="modal fade" id="modalReprovar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">

      <form method="POST"
            action="{{ route('beneficios.bolsa.aprovacoes.reprovar', ['sub'=>$sub,'processo_id'=>$processo->id,'solicitacao_id'=>$sol->id]) }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Reprovar Solicitação</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Justificativa</label>
            <textarea name="justificativa" class="form-control" rows="4" required></textarea>
          </div>
        </div>

        <div class="modal-footer d-flex justify-content-end gap-2">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Reprovar</button>
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

<script>
  if (window.feather) feather.replace();

  function brl(v){
    try { return new Intl.NumberFormat('pt-BR', {style:'currency', currency:'BRL'}).format(v); }
    catch(e){ return 'R$ ' + (v||0).toFixed(2).replace('.', ','); }
  }

  function parseBR(v){
    const s = String(v||'').replace(/\./g,'').replace(',','.');
    const n = parseFloat(s);
    return isNaN(n) ? 0 : n;
  }

  const mensalidade = parseBR(document.getElementById('valor_mensalidade')?.value || '0');
  const percentual = document.getElementById('percentual');
  const limiteEl = document.getElementById('valor_limite_calc');
  const concEl = document.getElementById('valor_concessao_calc');
  const hidden = document.getElementById('percentual_hidden');
  const btn = document.getElementById('btnAprovar');

  function recalc(){
    const p = parseFloat(percentual.value || '0');
    if (isNaN(p) || p < 0 || p > 100) {
      limiteEl.value = brl(0);
      concEl.value = brl(0);
      hidden.value = '';
      btn.disabled = true;
      return;
    }

    const limite = +(mensalidade * (p/100)).toFixed(2);
    limiteEl.value = brl(limite);
    concEl.value = brl(limite);
    hidden.value = String(p);
    btn.disabled = false;
  }

  percentual.addEventListener('input', recalc);
  recalc();
</script>

</body>
</html>
