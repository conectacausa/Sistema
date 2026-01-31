<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name') }} | Usuários</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
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
                  <li class="breadcrumb-item" aria-current="page">Usuários</li>
                </ol>
              </nav>
            </div>
          </div>

          {{-- Botão Novo Usuário --}}
          @if(!empty($podeCadastrar) && $podeCadastrar)
            <a href="{{ route('config.usuarios.create') }}"
               class="waves-effect waves-light btn mb-5 bg-gradient-primary">
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
                        <input
                          type="text"
                          name="q"
                          id="filter-q"
                          value="{{ $busca }}"
                          class="form-control"
                          placeholder="Nome ou CPF"
                          autocomplete="off"
                        >
                      </div>
                    </div>
                
                    <div class="col-md-2">
                      <div class="form-group">
                        <label class="form-label">Situação</label>
                        <select
                          name="status"
                          id="filter-status"
                          class="form-select"
                        >
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
                        <th>Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($usuarios ?? [] as $u)
                        <tr>
                          <td>{{ $u->nome_completo }}</td>
                          <td>{{ $u->cpf ?? '-' }}</td>
                          <td>{{ $u->grupo_permissao ?? '-' }}</td>
                          <td>{{ ucfirst($u->status) }}</td>
                          <td>
                            {{-- ações serão reativadas depois --}}
                          </td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="5" class="text-center">
                            Nenhum usuário encontrado
                          </td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
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
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filtersForm');
            const inputQ = document.getElementById('filter-q');
            const selectStatus = document.getElementById('filter-status');
        
            let typingTimer = null;
            const debounceTime = 400; // ms
        
            // Digitação no campo Nome/CPF
            inputQ.addEventListener('input', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function () {
                    form.submit();
                }, debounceTime);
            });
        
            // Mudança no select Situação
            selectStatus.addEventListener('change', function () {
                form.submit();
            });
        });
</script>

</body>
</html>
