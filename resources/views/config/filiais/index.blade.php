@extends('layouts.app')

@section('title', 'Filiais')

@section('content')
<div class="container-full">
  <!-- Content Header -->
  <div class="content-header">
    <div class="d-flex align-items-center">
      <div class="me-auto">
        <h4 class="page-title">Filiais</h4>
        <div class="d-inline-block align-items-center">
          <nav>
            <ol class="breadcrumb">
              <li class="breadcrumb-item">
                <a href="#"><i class="mdi mdi-home-outline"></i></a>
              </li>
              <li class="breadcrumb-item">Configuração</li>
              <li class="breadcrumb-item" aria-current="page">Filiais</li>
            </ol>
          </nav>
        </div>
      </div>

      <button id="btnNovaFilial" type="button"
        class="waves-effect waves-light btn mb-5 bg-gradient-success">
        Nova Filial
      </button>
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
                  <input id="filtroRazaoCnpj" type="text"
                    class="form-control" placeholder="Razão Social ou CNPJ">
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

    <!-- Tabela -->
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

            <div class="d-flex justify-content-between mt-3">
              <div id="paginacaoInfo" class="text-muted"></div>
              <div>
                <button id="btnPrev" class="btn btn-outline-primary">Anterior</button>
                <button id="btnNext" class="btn btn-outline-primary">Próxima</button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

  </section>
</div>
@endsection

@push('scripts')
<script>
  window.__SCREEN_ID__ = 5;
</script>
<script src="{{ asset('assets/js/pages/config-filiais.js') }}"></script>
@endpush
