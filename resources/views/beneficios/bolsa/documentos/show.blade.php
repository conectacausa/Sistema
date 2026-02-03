<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name','ConecttaRH') }} | Documento</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

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
            <h4 class="page-title">Documento</h4>
          </div>
          <a class="waves-effect waves-light btn mb-5 bg-gradient-primary"
             href="{{ route('beneficios.bolsa.documentos.index', ['sub'=>$sub,'processo_id'=>$processo->id]) }}">
            Voltar
          </a>
        </div>
      </div>

      <section class="content">
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">{{ $processo->ciclo }} — Documento #{{ (int)$doc->id }}</h4>
              </div>

              <div class="box-body">
                @php
                  $tipo = ((int)$doc->tipo === 1) ? 'Comprovante' : 'Documento';
                  $comp = !empty($doc->competencia) ? \Carbon\Carbon::parse($doc->competencia)->format('m/Y') : '—';
                @endphp

                <div class="row">
                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label class="form-label">Solicitante</label>
                      <input class="form-control" value="{{ $doc->colaborador_nome ?? '—' }}" disabled>
                    </div>
                  </div>
                  <div class="col-md-3 col-12">
                    <div class="form-group">
                      <label class="form-label">Tipo</label>
                      <input class="form-control" value="{{ $tipo }}" disabled>
                    </div>
                  </div>
                  <div class="col-md-3 col-12">
                    <div class="form-group">
                      <label class="form-label">Competência</label>
                      <input class="form-control" value="{{ $comp }}" disabled>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <label class="form-label">Título</label>
                      <input class="form-control" value="{{ $doc->titulo }}" disabled>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div class="alert alert-info">
                      <div><strong>Arquivo:</strong> {{ $doc->arquivo_path }}</div>
                      <div class="mt-2 text-muted">Obs: a abertura do arquivo depende de como você está salvando (Storage/public). Aqui mantivemos o path para rastreabilidade.</div>
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                  <form method="POST"
                        action="{{ route('beneficios.bolsa.documentos.aprovar', ['sub'=>$sub,'processo_id'=>$processo->id,'doc_id'=>$doc->id]) }}"
                        class="d-inline">
                    @csrf
                    <button type="submit" class="btn bg-gradient-success">Aprovar</button>
                  </form>

                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalReprovar">
                    Reprovar
                  </button>
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

<div class="modal fade" id="modalReprovar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="{{ route('beneficios.bolsa.documentos.reprovar', ['sub'=>$sub,'processo_id'=>$processo->id,'doc_id'=>$doc->id]) }}">
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
</script>

</body>
</html>
