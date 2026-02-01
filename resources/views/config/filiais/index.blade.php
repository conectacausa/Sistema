<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

    <title>Conectta RH | Filiais</title>

    <!-- Vendors Style-->
    <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

    <!-- Style-->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

    <!-- ✅ SweetAlert v1 (do template) -->
    <link rel="stylesheet" href="{{ asset('assets/vendor_components/sweetalert/sweetalert.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
    <div id="loader"></div>

    {{-- HEADER (fallback) --}}
    @php
        $headerCandidates = ['layouts.header','layout.header','includes.header','partials.header','components.header'];
    @endphp
    @foreach($headerCandidates as $v)
        @if(View::exists($v))
            @include($v)
            @break
        @endif
    @endforeach

    {{-- MENU (fallback) --}}
    @php
        $menuCandidates = ['layouts.menu','layout.menu','includes.menu','partials.menu','components.menu','layouts.sidebar','includes.sidebar','partials.sidebar'];
    @endphp
    @foreach($menuCandidates as $v)
        @if(View::exists($v))
            @include($v)
            @break
        @endif
    @endforeach

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="container-full">

            <!-- Content Header -->
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

                    <button type="button" id="btnNovaFilial" class="waves-effect waves-light btn mb-5 bg-gradient-success">
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
                                            <input type="text" id="filtroRazaoCnpj" class="form-control" placeholder="Razão Social ou CNPJ">
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

                            </div><!-- /box-body -->
                        </div><!-- /box -->
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

                                <!-- ✅ Resumo -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-muted" id="filiaisResumo"></div>
                                </div>

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
                                        <tbody id="tabelaFiliaisBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Carregando filiais...</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Paginação -->
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div id="paginacaoInfo" class="text-muted"></div>
                                    <div>
                                        <button id="btnPrev" class="btn btn-sm btn-secondary">Anterior</button>
                                        <button id="btnNext" class="btn btn-sm btn-secondary">Próximo</button>
                                    </div>
                                </div>

                            </div><!-- /box-body -->
                        </div><!-- /box -->
                    </div>
                </div>

            </section>
        </div>
    </div>

    {{-- FOOTER (fallback) --}}
    @php
        $footerCandidates = ['layouts.footer','layout.footer','includes.footer','partials.footer','components.footer'];
    @endphp
    @foreach($footerCandidates as $v)
        @if(View::exists($v))
            @include($v)
            @break
        @endif
    @endforeach

</div>
<!-- ./wrapper -->

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- Template JS -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<!-- ✅ SweetAlert v1 (do template) -->
<script src="{{ asset('assets/vendor_components/sweetalert/sweetalert.min.js') }}"></script>

<!-- ✅ Rotas para o JS -->
<script>
  window.FILIAIS_ROUTES = {
    create: "{{ route('config.filiais.create') }}",
    edit: (id) => "{{ url('/config/filiais') }}/" + id + "/editar",
    destroy: (id) => "{{ url('/config/filiais') }}/" + id,
    grid: "{{ route('config.filiais.grid') }}",
    paises: "{{ route('config.filiais.paises') }}",
    estados: "{{ route('config.filiais.estados') }}",
    cidades: "{{ route('config.filiais.cidades') }}"
  };
</script>

<!-- JS da tela -->
<script src="{{ asset('assets/js/pages/config-filiais.js') }}?v={{ filemtime(public_path('assets/js/pages/config-filiais.js')) }}"></script>

</body>
</html>
