{{-- resources/views/beneficios/bolsa/index.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name') }} | Bolsa de Estudos</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  {{-- Incluir aqui o arquivo de header --}}
  @include('layouts.header')

  {{-- Incluir menu aqui --}}
  @include('layouts.menu')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="container-full">

      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Bolsa de Estudos</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => $sub]) }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item active" aria-current="page">Bolsa de Estudos</li>
                </ol>
              </nav>
            </div>
          </div>

          <a href="{{ route('beneficios.bolsa.create', ['sub' => $sub]) }}"
             class="waves-effect waves-light btn mb-5 bg-gradient-success">
            Nova Bolsa
          </a>
        </div>
      </div>

      <!-- Main content -->
      <section class="content">

        {{-- Alerts --}}
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Filtros -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Filtros</h4>
              </div>

              <div class="box-body">
                <form method="GET" action="{{ route('beneficios.bolsa.index', ['sub' => $sub]) }}">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-label">Titulo / Ciclo</label>
                        <input type="text"
                               name="q"
                               class="form-control"
                               placeholder="Titulo / Ciclo"
                               value="{{ request('q') }}">
                      </div>
                    </div>

                    <div class="col-12 d-flex gap-2">
                      <button type="submit" class="btn btn-primary">
                        Filtrar
                      </button>

                      <a href="{{ route('beneficios.bolsa.index', ['sub' => $sub]) }}"
                         class="btn btn-outline-secondary">
                        Limpar
                      </a>
                    </div>
                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>

        <!--Tabela Bolsa -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Bolsa de Estudos</h4>
              </div>

              <div class="box-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class="bg-primary">
                      <tr>
                        <th>Nome / Ciclo</th>
                        <th>Situação</th>
                        <th>Contemplados</th>
                        <th>Orçamento</th>
                        <th class="text-end">Ações</th>
                      </tr>
                    </thead>

                    <tbody>
                      @forelse($processos as $p)
                        @php
                          // status: 0=rascunho/inativo, 1=ativo, 2=encerrado
                          $statusLabel = match ((int)$p->status) {
                            1 => ['Ativo', 'badge badge-success'],
                            2 => ['Encerrado', 'badge badge-dark'],
                            default => ['Rascunho', 'badge badge-secondary'],
                          };

                          $orcamento = (float)($p->orcamento_mensal ?? 0);
                          $orcamentoBRL = 'R$ ' . number_format($orcamento, 2, ',', '.');

                          $cont = (int)($p->contemplados_count ?? 0);
                          $pend = (int)($p->pendentes_count ?? 0);
                        @endphp

                        <tr>
                          <td>
                            <div class="fw-600">
                              {{ $p->edital ?: 'Bolsa de Estudos' }}
                            </div>
                            <div class="text-muted">
                              {{ $p->ciclo }}
                            </div>
                          </td>

                          <td>
                            <span class="{{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span>
                          </td>

                          <td>
                            {{ $cont }}
                            @if($pend > 0)
                              <span class="ms-1 badge badge-warning">+{{ $pend }} pendente(s)</span>
                            @endif
                          </td>

                          <td>{{ $orcamentoBRL }}</td>

                          <td class="text-end">
                            <div class="d-inline-flex gap-1">

                              {{-- Botão Aprovações: aparece se tiver pendente --}}
                              @if($pend > 0)
                                <a href="{{ route('beneficios.bolsa.aprovacoes', ['sub' => $sub, 'id' => $p->id]) }}"
                                   class="btn btn-warning btn-sm"
                                   title="Aprovações pendentes">
                                  <i data-feather="users"></i>
                                </a>
                              @endif

                              {{-- Editar --}}
                              <a href="{{ route('beneficios.bolsa.edit', ['sub' => $sub, 'id' => $p->id]) }}"
                                 class="btn btn-primary btn-sm"
                                 title="Editar">
                                <i data-feather="edit"></i>
                              </a>

                              {{-- Excluir (padrão Sweatalert do projeto) --}}
                              <form method="POST"
                                    action="{{ route('beneficios.bolsa.destroy', ['sub' => $sub, 'id' => $p->id]) }}"
                                    class="d-inline js-form-delete">
                                @csrf
                                @method('DELETE')

                                <button type="button"
                                        class="btn btn-danger btn-sm js-btn-delete"
                                        data-title="Confirmar exclusão"
                                        data-text="Deseja realmente excluir este registro?"
                                        data-confirm="Sim, excluir"
                                        data-cancel="Cancelar"
                                        title="Excluir">
                                  <i data-feather="trash-2"></i>
                                </button>
                              </form>

                            </div>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center text-muted py-4">
                            Nenhum ciclo encontrado.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>

                  @if(method_exists($processos, 'links'))
                    <div class="mt-3">
                      {{ $processos->appends(request()->query())->links() }}
                    </div>
                  @endif

                </div>
              </div>

            </div>
          </div>
        </div>

      </section>
      <!-- /.content -->

    </div>
  </div>
  <!-- /.content-wrapper -->

  {{-- Incluir footer aqui --}}
  @include('layouts.footer')
</div>
<!-- ./wrapper -->

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- Coup Admin App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

{{-- JS global de confirmação de exclusão (Sweatalert) --}}
<script src="{{ asset('assets/js/app-delete-confirm.js') }}"></script>

<script>
  if (window.feather) feather.replace();
</script>

</body>
</html>
