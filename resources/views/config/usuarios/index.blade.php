<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Usuários</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  <style>
    /* ===============================
       1) REMOVER HOVER DO BOTÃO NOVO
       =============================== */
    a.btn-nohover,
    a.btn-nohover:hover,
    a.btn-nohover:focus,
    a.btn-nohover:active {
      background: linear-gradient(45deg, #28a745, #20c997) !important;
      color: #fff !important;
      box-shadow: none !important;
      transform: none !important;
      filter: none !important;
    }

    /* ======================================
       2) GARANTIR CLIQUE NOS BOTÕES DA TABELA
       ====================================== */
    .table td a.btn,
    .table td button {
      pointer-events: auto !important;
      cursor: pointer !important;
    }

    /* remover flex que quebra clique */
    .table td.actions-cell {
      display: table-cell !important;
      vertical-align: middle;
    }
  </style>
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
          </div>

          @if($podeCadastrar)
            <a href="{{ route('config.usuarios.create') }}"
               class="btn bg-gradient-success btn-nohover">
              Novo Usuário
            </a>
          @endif
        </div>
      </div>

      <section class="content">

        <!-- FILTROS -->
        <div class="box">
          <div class="box-body">
            <form id="filtersForm" method="GET" action="{{ route('config.usuarios.index') }}">
              <div class="row">
                <div class="col-md-10">
                  <input id="qInput" type="text" name="q"
                         class="form-control"
                         placeholder="Nome ou CPF"
                         value="{{ $busca }}">
                </div>
                <div class="col-md-2">
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
            </form>
          </div>
        </div>

        <!-- TABELA -->
        <div class="box">
          <div class="box-body">
            <table class="table">
              <thead class="bg-primary">
                <tr>
                  <th>Nome</th>
                  <th>CPF</th>
                  <th>Grupo</th>
                  <th>Situação</th>
                  <th style="width:160px">Ações</th>
                </tr>
              </thead>
              <tbody>
              @foreach($usuarios as $u)
                @php $st = strtolower($u->status); @endphp
                <tr>
                  <td>{{ $u->nome_completo }}</td>
                  <td>{{ preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $u->cpf) }}</td>
                  <td>{{ $u->grupo_permissao }}</td>
                  <td>
                    <span class="badge {{ $st==='ativo'?'badge-success':'badge-danger' }}">
                      {{ ucfirst($u->status) }}
                    </span>
                  </td>

                  <!-- IMPORTANTE: sem flex -->
                  <td class="actions-cell">
                    @if($podeEditar)
                      <a href="{{ route('config.usuarios.edit', $u->id) }}"
                         class="btn btn-sm btn-outline-primary"
                         title="Editar">
                        <i data-feather="edit"></i>
                      </a>

                      @if($st === 'ativo')
                        <form method="POST"
                              action="{{ route('config.usuarios.inativar', $u->id) }}"
                              style="display:inline;">
                          @csrf
                          <button type="submit"
                                  class="btn btn-sm btn-outline-danger"
                                  onclick="return confirm('Confirma inativar este usuário?')">
                            <i data-feather="user-x"></i>
                          </button>
                        </form>
                      @endif
                    @endif
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>

            {{ $usuarios->links() }}
          </div>
        </div>

      </section>
    </div>
  </div>

  @include('partials.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    feather.replace();

    // filtros automáticos
    let t;
    document.getElementById('qInput').addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => document.getElementById('filtersForm').submit(), 400);
    });
    document.getElementById('statusSelect').addEventListener('change', () => {
      document.getElementById('filtersForm').submit();
    });
  });
</script>
</body>
</html>
