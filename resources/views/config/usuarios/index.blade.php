<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name') }} | Usuários</title>

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
          </div>

          {{-- botão simples, sem CSS extra --}}
          @if(!empty($podeCadastrar) && $podeCadastrar)
            <a href="{{ route('config.usuarios.create') }}"
               class="btn btn-success">
              Novo Usuário
            </a>
          @endif
        </div>
      </div>

      <section class="content">

        {{-- filtros simples --}}
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-body">
                <form method="GET">
                  <div class="row">
                    <div class="col-md-10">
                      <input type="text"
                             name="q"
                             class="form-control"
                             placeholder="Nome ou CPF"
                             value="{{ $busca ?? '' }}">
                    </div>
                    <div class="col-md-2">
                      <select name="status" class="form-select"
                              onchange="this.form.submit()">
                        <option value="">Todas</option>
                        @foreach($situacoes ?? [] as $st)
                          <option value="{{ $st }}"
                            {{ ($situacaoSelecionada ?? '') === $st ? 'selected' : '' }}>
                            {{ ucfirst($st) }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        {{-- tabela --}}
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-body table-responsive">
                <table class="table">
                  <thead class="bg-primary">
                    <tr>
                      <th>Nome</th>
                      <th>CPF</th>
                      <th>Grupo</th>
                      <th>Status</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                  @forelse($usuarios ?? [] as $u)
                    <tr>
                      <td>{{ $u->nome_completo }}</td>
                      <td>{{ $u->cpf }}</td>
                      <td>{{ $u->grupo_permissao ?? '-' }}</td>
                      <td>{{ ucfirst($u->status) }}</td>
                      <td>
                        {{-- ações vazias por enquanto --}}
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

      </section>

    </div>
  </div>

  @include('partials.footer')

</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>
</body>
</html>
