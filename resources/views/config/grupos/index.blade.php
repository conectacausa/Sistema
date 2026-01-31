<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Grupo de Permissão</title>

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
  @includeIf('partials.header')

  {{-- Incluir menu aqui --}}
  @includeIf('partials.menu')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="container-full">

      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Grupos de Permissão</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item" aria-current="page">Grupos de Permissão</li>
                </ol>
              </nav>
            </div>
          </div>

          @if(\Illuminate\Support\Facades\Route::has('config.grupos.create'))
            <a href="{{ route('config.grupos.create') }}"
               class="waves-effect waves-light btn mb-5 bg-gradient-success">
              Novo Grupo
            </a>
          @else
            <button type="button"
                    class="waves-effect waves-light btn mb-5 bg-gradient-success"
                    disabled
                    title="Rota config.grupos.create ainda não criada">
              Novo Grupo
            </button>
          @endif
        </div>
      </div>

      <!-- Main content -->
      <section class="content">

        <!-- Filtros -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Filtros</h4>
              </div>

              <div class="box-body">
                <form method="GET" action="{{ route('config.grupos.index') }}">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-label">Nome do Grupo</label>
                        <input
                          type="text"
                          name="nome_grupo"
                          value="{{ request('nome_grupo') }}"
                          class="form-control"
                          placeholder="Nome do Grupo">
                      </div>
                    </div>
                  </div>

                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                      Filtrar
                    </button>

                    <a href="{{ route('config.grupos.index') }}"
                       class="btn btn-outline-secondary waves-effect waves-light">
                      Limpar
                    </a>
                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>

        <!-- Tabela -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Grupo de Permissão</h4>
              </div>

              <div class="box-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class="bg-primary">
                      <tr>
                        <th>Nome Grupo</th>
                        <th>Usuários</th>
                        <th>Ações</th>
                      </tr>
                    </thead>

                    <tbody>
                      @forelse ($grupos as $g)
                        <tr>
                          <td>
                            {{ $g->nome_grupo }}
                            @if(isset($g->status) && !$g->status)
                              <span class="badge badge-danger ms-2">Inativo</span>
                            @endif
                          </td>

                          <td>{{ $g->usuarios_count ?? 0 }}</td>

                          <td>
                            <div class="d-flex gap-1">
                              @if(\Illuminate\Support\Facades\Route::has('config.grupos.edit'))
                                <a href="{{ route('config.grupos.edit', $g->id) }}"
                                   class="btn btn-sm btn-primary">
                                  Editar
                                </a>
                              @else
                                <button class="btn btn-sm btn-primary" disabled
                                        title="Rota config.grupos.edit ainda não criada">
                                  Editar
                                </button>
                              @endif

                              @if(\Illuminate\Support\Facades\Route::has('config.grupos.destroy'))
                                <form action="{{ route('config.grupos.destroy', $g->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Confirma a exclusão deste grupo?');"
                                      style="display:inline;">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="btn btn-sm btn-danger">
                                    Excluir
                                  </button>
                                </form>
                              @else
                                <button class="btn btn-sm btn-danger" disabled
                                        title="Rota config.grupos.destroy ainda não criada">
                                  Excluir
                                </button>
                              @endif
                            </div>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="3" class="text-center">
                            Nenhum grupo encontrado.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="mt-3">
                  {{ $grupos->links() }}
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
  @includeIf('partials.footer')

</div>
<!-- ./wrapper -->

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- Coup Admin App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

</body>
</html>
