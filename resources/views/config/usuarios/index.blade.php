<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name') }} | Usuários</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  <style>
    /* Remove efeito hover do botão Novo Usuário */
    .btn-nohover,
    .btn-nohover:hover,
    .btn-nohover:focus,
    .btn-nohover:active {
      background: inherit !important;
      color: inherit !important;
      box-shadow: none !important;
      transform: none !important;
      filter: none !important;
    }
  </style>
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  {{-- HEADER --}}
  @include('partials.header')

  {{-- MENU --}}
  @include('partials.menu')

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="container-full">

      <!-- Content Header -->
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Usuários</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item active">Usuários</li>
                </ol>
              </nav>
            </div>
          </div>

          {{-- Botão Novo Usuário --}}
          @if(!empty($podeCadastrar) && $podeCadastrar)
            <a href="{{ route('config.usuarios.create') }}"
               class="waves-effect waves-light btn bg-gradient-success btn-nohover">
              Novo Usuário
            </a>
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
                <form method="GET" id="filtersForm">
                  <div class="row">
                    <div class="col-md-10">
                      <div class="form-group">
                        <label class="form-label">Nome ou CPF</label>
                        <input type="text"
                               name="q"
                               value="{{ $busca }}"
                               class="form-control"
                               placeholder="Nome ou CPF">
                      </div>
                    </div>

                    <div class="col-md-2">
                      <div class="form-group">
                        <label class="form-label">Situação</label>
                        <select name="status"
                                class="form-select"
                                onchange="this.form.submit()">
                          <option value="">Todas</option>
                          @foreach($situacoes as $st)
                            <option value="{{ $st }}" {{ $situacaoSelecionada === $st ? 'selected' : '' }}>
                              {{ ucfirst($st) }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>

        <!-- Tabela Usuários -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Usuários</h4>
              </div>

              <div class="box-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class="bg-primary">
                      <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Grupo de Permissão</th>
                        <th>Situação</th>
                        <th style="width:150px">Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($usuarios as $u)
                        <tr>
                          <td>{{ $u->nome_completo }}</td>
                          <td>{{ $u->cpf_formatado }}</td>
                          <td>{{ $u->grupo_permissao }}</td>
                          <td>
                            <span class="badge {{ $u->status === 'ativo' ? 'badge-success' : 'badge-danger' }}">
                              {{ ucfirst($u->status) }}
                            </span>
                          </td>
                          <td>
                            @if(!empty($podeEditar) && $podeEditar)
                              {{-- Editar --}}
                              <a href="{{ route('config.usuarios.edit', $u->id) }}"
                                 class="btn btn-sm btn-outline-primary"
                                 title="Editar">
                                <i data-feather="edit"></i>
                              </a>

                              {{-- Inativar --}}
                              @if($u->status === 'ativo')
                                <form method="POST"
                                      action="{{ route('config.usuarios.inativar', $u->id) }}"
                                      style="display:inline"
                                      onsubmit="return confirm('Confirma inativar este usuário?')">
                                  @csrf
                                  <button type="submit"
                                          class="btn btn-sm btn-outline-danger"
                                          title="Inativar">
                                    <i data-feather="user-x"></i>
                                  </button>
                                </form>
                              @endif
                            @endif
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center">
                            Nenhum usuário encontrado.
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="mt-3">
                  {{ $usuarios->links() }}
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

  {{-- FOOTER --}}
  @include('partials.footer')

</div>
<!-- ./wrapper -->

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.feather) {
      feather.replace();
    }
  });
</script>

</body>
</html>
