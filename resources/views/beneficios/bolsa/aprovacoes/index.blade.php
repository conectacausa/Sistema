<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Aprovações Bolsa</title>

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
            <h4 class="page-title">Aprovações - Bolsa de Estudos</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => $sub]) }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item">Bolsa de Estudos</li>
                  <li class="breadcrumb-item active" aria-current="page">Aprovações</li>
                </ol>
              </nav>
            </div>
          </div>
          <a class="waves-effect waves-light btn mb-5 bg-gradient-primary"
             href="{{ route('beneficios.bolsa.edit', ['sub' => $sub, 'id' => $processo->id]) }}">
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
                <h4 class="box-title">Filtros</h4>
              </div>
              <div class="box-body">
                <form method="GET" action="{{ route('beneficios.bolsa.aprovacoes.index', ['sub'=>$sub,'processo_id'=>$processo->id]) }}">
                  <div class="row">
                    <div class="col-md-12 col-12">
                      <div class="form-group">
                        <label class="form-label">Buscar (colaborador / entidade / curso)</label>
                        <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Digite para buscar...">
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Pendentes de aprovação - {{ $processo->ciclo }}</h4>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class="bg-primary">
                      <tr>
                        <th style="min-width:240px;">Colaborador</th>
                        <th>Entidade</th>
                        <th>Curso</th>
                        <th>Mensalidade</th>
                        <th>Data Solicitação</th>
                        <th class="text-end">Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($solicitacoes as $s)
                        <tr>
                          <td>{{ $s->colaborador_nome ?? '—' }}</td>
                          <td>{{ $s->entidade_nome ?? '—' }}</td>
                          <td>{{ $s->curso_nome ?? '—' }}</td>
                          <td>R$ {{ number_format((float)($s->valor_total_mensalidade ?? 0), 2, ',', '.') }}</td>
                          <td>{{ !empty($s->solicitacao_at) ? \Carbon\Carbon::parse($s->solicitacao_at)->format('d/m/Y H:i') : '—' }}</td>
                          <td class="text-end">
                            <a href="{{ route('beneficios.bolsa.aprovacoes.show', ['sub'=>$sub,'processo_id'=>$processo->id,'solicitacao_id'=>$s->id]) }}"
                               class="btn btn-primary btn-sm">
                              <i data-feather="edit"></i>
                            </a>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="6" class="text-center text-muted py-4">Nenhuma solicitação pendente.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="mt-3">
                  {{ $solicitacoes->withQueryString()->links() }}
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
