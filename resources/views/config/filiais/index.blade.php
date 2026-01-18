<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name') }} | Filiais</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  @includeIf('layouts.header')
  @includeIf('layouts.menu')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="container-full">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Filiais</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item" aria-current="page">Filiais</li>
                </ol>
              </nav>
            </div>
          </div>

          <button id="btnNovaFilial" type="button" class="waves-effect waves-light btn mb-5 bg-gradient-success">Nova Filial</button>
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
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label class="form-label">Razão Social ou CNPJ</label>
                      <input id="filtroRazaoCnpj" type="text" class="form-control" placeholder="Razão Social ou CNPJ">
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">País</label>
                      <select id="filtroPais" class="form-select">
                        <option value="">Lista de País</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Estado</label>
                      <select id="filtroEstado" class="form-select" disabled>
                        <option value="">Lista de Estado</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label">Cidade</label>
                      <select id="filtroCidade" class="form-select" disabled>
                        <option value="">Lista de Cidade</option>
                      </select>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!--Tabela Filiais -->
        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Filiais</h4>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class="bg-primary">
                      <tr>
                        <th>Nome Fantasia</th>
                        <th>CNPJ</th>
                        <th>Cidade</th>
                        <th>UF</th>
                        <th>País</th>
                        <th>Ações</th>
                      </tr>
                    </thead>
                    <tbody id="tabelaFiliaisBody"></tbody>
                  </table>
                </div>

                <div class="d-flex align-items-center justify-content-between mt-3">
                  <div id="paginacaoInfo" class="text-muted"></div>
                  <div class="btn-group" role="group" aria-label="Paginação">
                    <button id="btnPrev" type="button" class="btn btn-outline-primary">Anterior</button>
                    <button id="btnNext" type="button" class="btn btn-outline-primary">Próxima</button>
                  </div>
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

  @includeIf('layouts.footer')
</div>
<!-- ./wrapper -->

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- Coup Admin App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Config Filiais -->
<script>
  window.__SCREEN_ID__ = {{ (int)$screenId }};
</script>
<script src="{{ asset('assets/js/pages/config-filiais.js') }}"></script>

</body>
</html>
