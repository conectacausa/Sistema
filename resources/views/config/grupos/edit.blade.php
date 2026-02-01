<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Grupo de Permissão</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  <style>
    .vtabs { display: flex; width: 100%; }
    .vtabs > .nav.tabs-vertical { flex: 0 0 260px; min-width: 260px; }
    .vtabs > .tab-content { flex: 1 1 auto; width: 100%; }
    @media (max-width: 991.98px){
      .vtabs { display: block; }
      .vtabs > .nav.tabs-vertical { min-width: 100%; flex: 0 0 auto; }
    }
  </style>

  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  @includeIf('partials.header')
  @includeIf('partials.menu')

  <div class="content-wrapper">
    <div class="container-full">

      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Grupo de Permissão</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => request()->route('sub')]) }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item" aria-current="page">Grupo de Permissão</li>
                  <li class="breadcrumb-item" aria-current="page">Editar Grupo</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <section class="content">

        {{-- ✅ Alerts dismissable (padrão do exemplo) --}}
        @if(session('success'))
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('success') }}
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            Verifique os campos e tente novamente.
          </div>
        @endif

        {{-- ✅ Área para alerts do AJAX --}}
        <div id="alert-area"></div>

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Editar Grupo de Permissão</h4>
              </div>

              <div class="box-body">

                <form id="form-grupo" method="POST"
                      action="{{ route('config.grupos.update', ['sub' => request()->route('sub'), 'id' => $grupo->id]) }}">
                  @csrf
                  @method('PUT')

                  <div class="vtabs">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#grupo" role="tab">
                          <span><i class="ion-person me-15"></i>Grupo</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#usuarios" role="tab">
                          <span><i class="ion-person-stalker me-15"></i>Usuários</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#permissoes" role="tab">
                          <span><i class="ion-locked me-15"></i>Permissões</span>
                        </a>
                      </li>
                    </ul>

                    <div class="tab-content">

                      <div class="tab-pane active" id="grupo" role="tabpanel">
                        <div class="p-15">
                          <h3>Dados do Grupo</h3>

                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Nome do Grupo</label>
                                <input type="text"
                                       name="nome_grupo"
                                       class="form-control @error('nome_grupo') is-invalid @enderror"
                                       value="{{ old('nome_grupo', $grupo->nome_grupo) }}"
                                       maxlength="160"
                                       required>
                                @error('nome_grupo')
                                  <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Observações</label>
                                <textarea name="observacoes"
                                          class="form-control @error('observacoes') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Observações">{{ old('observacoes', $grupo->observacoes) }}</textarea>
                                @error('observacoes')
                                  <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                  <option value="1" {{ old('status', $grupo->status ? '1' : '0') == '1' ? 'selected' : '' }}>Ativo</option>
                                  <option value="0" {{ old('status', $grupo->status ? '1' : '0') == '0' ? 'selected' : '' }}>Inativo</option>
                                </select>
                              </div>
                            </div>

                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Vê Salários</label>
                                <select name="salarios" class="form-control @error('salarios') is-invalid @enderror" required>
                                  <option value="1" {{ old('salarios', $grupo->salarios ? '1' : '0') == '1' ? 'selected' : '' }}>Sim</option>
                                  <option value="0" {{ old('salarios', $grupo->salarios ? '1' : '0') == '0' ? 'selected' : '' }}>Não</option>
                                </select>
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>

                      {{-- ✅ Usuários: removida coluna de desvincular --}}
                      <div class="tab-pane" id="usuarios" role="tabpanel">
                        <div class="p-15">
                          <h3>Usuários</h3>

                          <div class="table-responsive">
                            <table class="table">
                              <thead class="bg-primary">
                                <tr>
                                  <th style="min-width:240px;">Nome</th>
                                  <th>Filial / Setor</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($usuarios as $u)
                                  <tr>
                                    <td>{{ $u->nome_completo }}</td>
                                    <td>{!! $u->lotacoes_html ?: '-' !!}</td>
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="2" class="text-center">Nenhum usuário vinculado a este grupo.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                          </div>

                        </div>
                      </div>

                      {{-- ✅ Permissões com auto-save --}}
                      <div class="tab-pane" id="permissoes" role="tabpanel">
                        <div class="p-15">
                          <h3>Permissões</h3>

                          @forelse($modulos as $m)
                            <div class="mb-4">
                              <h5 class="mb-2">{{ $m->nome }}</h5>

                              <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                  <thead class="bg-primary">
                                    <tr>
                                      <th style="width: 55%;">Tela</th>
                                      <th class="text-center" style="width: 15%;">Ler</th>
                                      <th class="text-center" style="width: 15%;">Cadastrar</th>
                                      <th class="text-center" style="width: 15%;">Editar</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @php
                                      $telasDoModulo = $telasPorModulo[$m->id] ?? collect();
                                    @endphp

                                    @forelse($telasDoModulo as $t)
                                      @php
                                        $p = $permissoesExistentes->get($t->id);

                                        $checkedAtivo = old("perm.{$t->id}.ativo", $p?->ativo ? 1 : 0) ? true : false;
                                        $checkedCadastro = old("perm.{$t->id}.cadastro", $p?->cadastro ? 1 : 0) ? true : false;
                                        $checkedEditar = old("perm.{$t->id}.editar", $p?->editar ? 1 : 0) ? true : false;

                                        $idAtivo = "perm_{$t->id}_ativo";
                                        $idCadastro = "perm_{$t->id}_cadastro";
                                        $idEditar = "perm_{$t->id}_editar";
                                      @endphp

                                      <tr>
                                        <td>
                                          <div class="fw-600">{{ $t->nome_tela }}</div>
                                          <small class="text-muted">{{ $t->slug }}</small>
                                        </td>

                                        <td class="text-center">
                                          <input type="checkbox"
                                                 id="{{ $idAtivo }}"
                                                 class="chk-col-primary js-perm"
                                                 data-tela-id="{{ $t->id }}"
                                                 data-campo="ativo"
                                                 {{ $checkedAtivo ? 'checked' : '' }}>
                                          <label for="{{ $idAtivo }}"></label>
                                        </td>

                                        <td class="text-center">
                                          <input type="checkbox"
                                                 id="{{ $idCadastro }}"
                                                 class="chk-col-primary js-perm"
                                                 data-tela-id="{{ $t->id }}"
                                                 data-campo="cadastro"
                                                 {{ $checkedCadastro ? 'checked' : '' }}>
                                          <label for="{{ $idCadastro }}"></label>
                                        </td>

                                        <td class="text-center">
                                          <input type="checkbox"
                                                 id="{{ $idEditar }}"
                                                 class="chk-col-primary js-perm"
                                                 data-tela-id="{{ $t->id }}"
                                                 data-campo="editar"
                                                 {{ $checkedEditar ? 'checked' : '' }}>
                                          <label for="{{ $idEditar }}"></label>
                                        </td>
                                      </tr>
                                    @empty
                                      <tr>
                                        <td colspan="4" class="text-center">Nenhuma tela cadastrada para este módulo.</td>
                                      </tr>
                                    @endforelse
                                  </tbody>
                                </table>
                              </div>
                            </div>
                          @empty
                            <div class="alert alert-warning alert-dismissible">
                              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              Nenhum módulo está vinculado a esta empresa.
                            </div>
                          @endforelse

                        </div>
                      </div>

                    </div>
                  </div>

                  <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="waves-effect waves-light btn bg-gradient-success">
                      Salvar
                    </button>
                  </div>

                </form>

              </div>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>

  @includeIf('partials.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

{{-- ✅ JS do auto-save das permissões --}}
<script>
  window.CON_GROUP_ID = {{ (int)$grupo->id }};
  window.CON_SUB = @json((string)request()->route('sub'));
  window.CON_TOGGLE_URL = @json(route('config.grupos.permissoes.toggle', ['sub' => request()->route('sub'), 'id' => $grupo->id]));
</script>
<script src="{{ asset('assets/js/pages/config-grupos-edit.js') }}"></script>

</body>
</html>
