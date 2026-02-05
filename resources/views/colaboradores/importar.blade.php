<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>{{ config('app.name','ConecttaRH') }} | Importar Colaboradores</title>

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
            <h4 class="page-title">Importar Colaboradores</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => request()->route('sub')]) }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Colaboradores</li>
                  <li class="breadcrumb-item" aria-current="page">Importar</li>
                </ol>
              </nav>
            </div>
          </div>

          <a href="{{ route('colaboradores.importar.modelo', ['sub' => request()->route('sub')]) }}"
             class="waves-effect waves-light btn mb-5 bg-gradient-primary">
            Baixar Modelo
          </a>
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
                <h4 class="box-title">Enviar arquivo</h4>
              </div>

              <div class="box-body">
                <form method="POST"
                      action="{{ route('colaboradores.importar.store', ['sub' => request()->route('sub')]) }}"
                      enctype="multipart/form-data">
                  @csrf

                  <div class="row">
                    <div class="col-12 col-lg-8">
                      <div class="form-group">
                        <label class="form-label">Arquivo Excel (.xlsx)</label>
                        <input type="file" name="arquivo" class="form-control" accept=".xlsx,.xls" required>
                        <small class="text-muted">
                          Campos esperados: <strong>nome</strong>, <strong>cpf</strong>, (opcionais: sexo, data_admissao, matricula)
                        </small>
                      </div>
                    </div>

                    <div class="col-12 col-lg-4 d-flex align-items-end">
                      <button type="submit" class="btn btn-success w-100">
                        Enviar para Importação
                      </button>
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
                <h4 class="box-title">Histórico de Importações</h4>
              </div>

              <div class="box-body">

                <div class="table-responsive">
                  <table class="table table-bordered table-striped align-middle mb-0">
                    <thead>
                      <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Arquivo</th>
                        <th style="width: 140px;">Status</th>
                        <th style="width: 120px;" class="text-center">Linhas</th>
                        <th style="width: 120px;" class="text-center">Importados</th>
                        <th style="width: 120px;" class="text-center">Ignorados</th>
                        <th style="width: 120px;" class="text-center">Rejeitados</th>
                        <th style="width: 170px;">Início</th>
                        <th style="width: 170px;">Fim</th>
                        <th style="width: 170px;">Ações</th>
                      </tr>
                    </thead>

                    <tbody>
                      @forelse($importacoes as $imp)
                        <tr>
                          <td>{{ $imp->id }}</td>
                          <td>
                            <div class="fw-600">{{ $imp->arquivo_nome ?? 'Arquivo' }}</div>
                            <small class="text-muted">{{ $imp->arquivo_path }}</small>
                          </td>

                          <td>
                            @php
                              $st = (string) ($imp->status ?? '');
                            @endphp

                            @if($st === 'queued')
                              <span class="badge badge-pill badge-info">Fila</span>
                            @elseif($st === 'processing')
                              <span class="badge badge-pill badge-warning">Processando</span>
                            @elseif($st === 'done')
                              <span class="badge badge-pill badge-success">Concluído</span>
                            @elseif($st === 'failed')
                              <span class="badge badge-pill badge-danger">Falhou</span>
                            @else
                              <span class="badge badge-pill badge-secondary">{{ $st ?: 'N/D' }}</span>
                            @endif

                            @if(!empty($imp->mensagem_erro))
                              <div class="mt-1">
                                <small class="text-danger">{{ $imp->mensagem_erro }}</small>
                              </div>
                            @endif
                          </td>

                          <td class="text-center">{{ (int) ($imp->total_linhas ?? 0) }}</td>
                          <td class="text-center">{{ (int) ($imp->importados ?? 0) }}</td>
                          <td class="text-center">{{ (int) ($imp->ignorados ?? 0) }}</td>
                          <td class="text-center">{{ (int) ($imp->rejeitados_count ?? 0) }}</td>

                          <td>{{ $imp->started_at ? \Carbon\Carbon::parse($imp->started_at)->format('d/m/Y H:i') : '-' }}</td>
                          <td>{{ $imp->finished_at ? \Carbon\Carbon::parse($imp->finished_at)->format('d/m/Y H:i') : '-' }}</td>

                          <td>
                            @if(!empty($imp->rejeitados_path) && (int)($imp->rejeitados_count ?? 0) > 0)
                              <a href="{{ route('colaboradores.importar.rejeitados', ['sub' => request()->route('sub'), 'id' => $imp->id]) }}"
                                 class="btn btn-sm btn-outline-danger">
                                Baixar rejeitados
                              </a>
                            @else
                              <span class="text-muted">-</span>
                            @endif
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="10" class="text-center text-muted">
                            Nenhuma importação encontrada.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="mt-3">
                  {{ $importacoes->links() }}
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
(function(){
  if (window.feather) window.feather.replace();
})();
</script>

</body>
</html>
