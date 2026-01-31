<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Usuários</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  {{-- HEADER (padrão projeto) --}}
  @include('partials.header')

  {{-- MENU (padrão projeto) --}}
  @include('partials.menu')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="container-full">

      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Usuários</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item" aria-current="page">Usuários</li>
                </ol>
              </nav>
            </div>
          </div>

          <a href="{{ route('config.usuarios.create') }}"
             class="waves-effect waves-light btn mb-5 bg-gradient-success">
            Novo Usuário
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
        @if(session('warning'))
          <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <!-- Filtros -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Filtros</h4>
              </div>

              <div class="box-body">
                <form id="filtersForm" method="GET" action="{{ route('config.usuarios.index') }}">
                  <div class="row">
                    <div class="col-md-10">
                      <div class="form-group">
                        <label class="form-label">Nome ou CPF</label>
                        <input
                          id="qInput"
                          type="text"
                          name="q"
                          value="{{ $busca }}"
                          class="form-control"
                          placeholder="Digite o nome ou CPF"
                          autocomplete="off"
                        >
                      </div>
                    </div>

                    <div class="col-md-2">
                      <div class="form-group">
                        <label class="form-label">Situação</label>
                        <select id="statusSelect" class="form-select" name="status">
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

                <small class="text-muted">
                  Os filtros são aplicados automaticamente.
                </small>
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
                        <th style="width: 120px;">Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php
                        $maskCpf = function ($cpf) {
                          $cpf = preg_replace('/\D+/', '', (string) $cpf);
                          if (strlen($cpf) !== 11) return $cpf;
                          return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
                        };
                      @endphp

                      @forelse($usuarios as $u)
                        <tr>
                          <td>{{ $u->nome_completo }}</td>
                          <td>{{ $maskCpf($u->cpf) }}</td>
                          <td>{{ $u->grupo_permissao ?? '-' }}</td>
                          <td>
                            @php $st = strtolower((string) $u->status); @endphp
                            <span class="badge {{ $st === 'ativo' ? 'badge-success' : 'badge-danger' }}">
                              {{ ucfirst($u->status) }}
                            </span>
                          </td>
                          <td class="d-flex gap-2">
                            <a href="{{ route('config.usuarios.edit', $u->id) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Editar">
                              <i data-feather="edit"></i>
                            </a>

                            <form method="POST"
                                  action="{{ route('config.usuarios.destroy', $u->id) }}"
                                  class="d-inline form-delete-usuario">
                              @csrf
                              @method('DELETE')
                              <button type="submit"
                                      class="btn btn-sm btn-outline-danger"
                                      title="Excluir">
                                <i data-feather="trash-2"></i>
                              </button>
                            </form>
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center">Nenhum usuário encontrado.</td>
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

  {{-- FOOTER (padrão projeto) --}}
  @include('partials.footer')
</div>
<!-- ./wrapper -->

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- Coup Admin App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.feather) feather.replace();

    // Confirm simples (mantendo padrão funcional)
    document.querySelectorAll('.form-delete-usuario').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (!confirm('Confirma a exclusão deste usuário?')) {
          e.preventDefault();
        }
      });
    });

    // Filtro automático
    const form = document.getElementById('filtersForm');
    const qInput = document.getElementById('qInput');
    const statusSelect = document.getElementById('statusSelect');

    let t = null;
    const submitDebounced = () => {
      clearTimeout(t);
      t = setTimeout(() => form.submit(), 450);
    };

    qInput.addEventListener('input', submitDebounced);
    statusSelect.addEventListener('change', () => form.submit());
  });
</script>

</body>
</html>
