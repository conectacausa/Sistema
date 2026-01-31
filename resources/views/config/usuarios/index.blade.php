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

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">
<div class="wrapper">
  <div id="loader"></div>

  @include('partials.header')
  @include('partials.menu')

  <div class="content-wrapper">
    <div class="container-full">

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

          {{-- Evita quebrar por name(): usa URL direta do slug --}}
          @if(!empty($podeCadastrar) && $podeCadastrar)
            <a href="{{ url('/config/usuarios/novo') }}"
               class="waves-effect waves-light btn mb-5 bg-gradient-success">
              Novo Usuário
            </a>
          @endif
        </div>
      </div>

      <section class="content">
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
                <form id="filtersForm" method="GET" action="{{ url('/config/usuarios') }}">
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

                <small class="text-muted">Os filtros são aplicados automaticamente.</small>
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
                        <th style="width: 160px;">Ações</th>
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
                        @php $st = strtolower((string) $u->status); @endphp

                        <tr>
                          <td>{{ $u->nome_completo }}</td>
                          <td>{{ $maskCpf($u->cpf) }}</td>
                          <td>{{ $u->grupo_permissao ?? '-' }}</td>
                          <td>
                            <span class="badge {{ $st === 'ativo' ? 'badge-success' : 'badge-danger' }}">
                              {{ ucfirst($u->status) }}
                            </span>
                          </td>

                          <td class="d-flex gap-2">
                            @if(!empty($podeEditar) && $podeEditar)
                              {{-- Editar --}}
                              <a href="{{ url('/config/usuarios/' . $u->id . '/editar') }}"
                                 class="btn btn-sm btn-outline-primary"
                                 title="Editar">
                                <i data-feather="edit"></i>
                              </a>

                              {{-- Inativar (somente se estiver ativo) --}}
                              @if($st === 'ativo')
                                <form method="POST"
                                      action="{{ url('/config/usuarios/' . $u->id . '/inativar') }}"
                                      class="d-inline form-inativar-usuario">
                                  @csrf
                                  <button type="submit"
                                          class="btn btn-sm btn-outline-danger"
                                          title="Inativar">
                                    <i data-feather="user-x"></i>
                                  </button>
                                </form>
                              @endif
                            @else
                              <span class="text-muted">—</span>
                            @endif
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
    </div>
  </div>

  @include('partials.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.feather) feather.replace();

    document.querySelectorAll('.form-inativar-usuario').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (!confirm('Confirma inativar este usuário?')) {
          e.preventDefault();
        }
      });
    });

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
