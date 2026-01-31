<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">
  <title>{{ config('app.name', 'ConecttaRH') }} | Novo Usuário</title>

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
            <h4 class="page-title">Novo Usuário</h4>
          </div>
          <a href="{{ route('config.usuarios.index') }}" class="btn btn-outline-secondary mb-5">Voltar</a>
        </div>
      </div>

      <section class="content">
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
                <h4 class="box-title">Dados do Usuário</h4>
              </div>

              <div class="box-body">
                <form method="POST" action="{{ route('config.usuarios.store') }}">
                  @csrf

                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label">Nome completo</label>
                        <input type="text" name="nome_completo" class="form-control" value="{{ old('nome_completo') }}" required>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">CPF</label>
                        <input type="text" name="cpf" class="form-control" value="{{ old('cpf') }}" required>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">Situação</label>
                        <select name="status" class="form-select" required>
                          <option value="ativo" {{ old('status','ativo')==='ativo'?'selected':'' }}>Ativo</option>
                          <option value="inativo" {{ old('status')==='inativo'?'selected':'' }}>Inativo</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="{{ old('telefone') }}">
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">Grupo de Permissão</label>
                        <select name="permissao_id" class="form-select" required>
                          <option value="">Selecione</option>
                          @foreach($permissoes as $p)
                            <option value="{{ $p->id }}" {{ (string)old('permissao_id')===(string)$p->id?'selected':'' }}>
                              {{ $p->nome_grupo }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="form-label">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                      </div>
                    </div>

                    <div class="col-12 mt-3">
                      <button type="submit" class="btn btn-success">Salvar</button>
                      <a href="{{ route('config.usuarios.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>

                  </div>
                </form>
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
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.feather) feather.replace();
  });
</script>
</body>
</html>
