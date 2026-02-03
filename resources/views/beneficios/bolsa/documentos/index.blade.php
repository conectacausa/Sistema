<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name','ConecttaRH') }} | Documentos Bolsa</title>

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
            <h4 class="page-title">Documentos - Bolsa de Estudos</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => $sub]) }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item">Bolsa de Estudos</li>
                  <li class="breadcrumb-item active" aria-current="page">Documentos</li>
                </ol>
              </nav>
            </div>
          </div>

          <a class="waves-effect waves-light btn mb-5 bg-gradient-primary"
             href="{{ route('beneficios.bolsa.edit', ['sub'=>$sub,'id'=>$processo->id]) }}">
            Voltar ao Processo
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

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">{{ $processo->ciclo }}</h4>
              </div>

              <div class="box-body">
                <div class="vtabs">
                  <ul class="nav nav-tabs tabs-vertical" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" data-bs-toggle="tab" href="#tab-docs" role="tab">
                        <span><i data-feather="lock" class="me-10"></i>Aprovar Documentos</span>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" data-bs-toggle="tab" href="#tab-pay" role="tab">
                        <span><i data-feather="users" class="me-10"></i>Pagamento</span>
                      </a>
                    </li>
                  </ul>

                  <div class="tab-content">

                    {{-- TAB DOCUMENTOS --}}
                    <div class="tab-pane active" id="tab-docs" role="tabpanel">
                      <div class="p-15">
                        <div class="table-responsive">
                          <table class="table">
                            <thead class="bg-primary">
                              <tr>
                                <th style="min-width:240px;">Solicitante</th>
                                <th>Tipo</th>
                                <th>Competência</th>
                                <th>Título</th>
                                <th>Enviado em</th>
                                <th class="text-end">Ações</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse($docsPendentes as $d)
                                @php
                                  $tipo = ((int)$d->tipo === 1) ? 'Comprovante' : 'Documento';
                                  $comp = !empty($d->competencia) ? \Carbon\Carbon::parse($d->competencia)->format('m/Y') : '—';
                                @endphp
                                <tr>
                                  <td>{{ $d->colaborador_nome ?? '—' }}</td>
                                  <td>{{ $tipo }}</td>
                                  <td>{{ $comp }}</td>
                                  <td>{{ $d->titulo }}</td>
                                  <td>{{ !empty($d->created_at) ? \Carbon\Carbon::parse($d->created_at)->format('d/m/Y H:i') : '—' }}</td>
                                  <td class="text-end">
                                    <a href="{{ route('beneficios.bolsa.documentos.show', ['sub'=>$sub,'processo_id'=>$processo->id,'doc_id'=>$d->id]) }}"
                                       class="btn btn-primary btn-sm">
                                      <i data-feather="edit"></i>
                                    </a>

                                    <form method="POST"
                                          action="{{ route('beneficios.bolsa.documentos.aprovar', ['sub'=>$sub,'processo_id'=>$processo->id,'doc_id'=>$d->id]) }}"
                                          class="d-inline">
                                      @csrf
                                      <button type="submit" class="btn bg-gradient-success btn-sm">
                                        Aprovar
                                      </button>
                                    </form>

                                    <button type="button"
                                            class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalReprovarDoc"
                                            data-doc-id="{{ $d->id }}">
                                      <i data-feather="trash-2"></i>
                                    </button>
                                  </td>
                                </tr>
                              @empty
                                <tr>
                                  <td colspan="6" class="text-center text-muted py-4">Nenhum documento aguardando aprovação.</td>
                                </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>

                        <div class="mt-3">
                          {{ $docsPendentes->withQueryString()->links() }}
                        </div>
                      </div>
                    </div>

                    {{-- TAB PAGAMENTO --}}
                    <div class="tab-pane" id="tab-pay" role="tabpanel">
                      <div class="p-15">
                        <div class="table-responsive">
                          <table class="table">
                            <thead class="bg-primary">
                              <tr>
                                <th style="min-width:240px;">Solicitante</th>
                                <th>Competência</th>
                                <th>Vencimento</th>
                                <th>Valor Previsto</th>
                                <th class="text-end">Ações</th>
                              </tr>
                            </thead>
                            <tbody>
                              @forelse($competenciasPagamento as $c)
                                <tr>
                                  <td>{{ $c->colaborador_nome ?? '—' }}</td>
                                  <td>{{ !empty($c->competencia) ? \Carbon\Carbon::parse($c->competencia)->format('m/Y') : '—' }}</td>
                                  <td>{{ !empty($c->vencimento) ? \Carbon\Carbon::parse($c->vencimento)->format('d/m/Y') : '—' }}</td>
                                  <td>R$ {{ number_format((float)($c->valor_previsto ?? 0), 2, ',', '.') }}</td>
                                  <td class="text-end">
                                    <form method="POST"
                                          action="{{ route('beneficios.bolsa.competencias.pagar', ['sub'=>$sub,'processo_id'=>$processo->id,'competencia_id'=>$c->id]) }}"
                                          class="d-inline">
                                      @csrf
                                      <button type="submit" class="btn bg-gradient-success btn-sm">
                                        Marcar como Pago
                                      </button>
                                    </form>
                                  </td>
                                </tr>
                              @empty
                                <tr>
                                  <td colspan="5" class="text-center text-muted py-4">Nenhuma competência pronta para pagamento.</td>
                                </tr>
                              @endforelse
                            </tbody>
                          </table>
                        </div>

                        <div class="mt-3">
                          {{ $competenciasPagamento->withQueryString()->links() }}
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

{{-- Modal reprovar documento --}}
<div class="modal fade" id="modalReprovarDoc" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formReprovarDoc" action="#">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Reprovar Documento</h5>
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

  // Modal reprovar: monta action dinamicamente
  const modal = document.getElementById('modalReprovarDoc');
  const form = document.getElementById('formReprovarDoc');

  modal?.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const docId = btn?.getAttribute('data-doc-id');
    if (!docId) return;

    form.action = @json(route('beneficios.bolsa.documentos.reprovar', ['sub'=>$sub,'processo_id'=>$processo->id,'doc_id'=>0]))
      .replace(/\/0\/reprovar$/, '/' + docId + '/reprovar');
  });
</script>

</body>
</html>
