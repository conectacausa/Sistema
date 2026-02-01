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
</head>
<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  {{-- Header --}}
  @includeIf('partials.header')

  {{-- Menu --}}
  @includeIf('partials.menu')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="container-full">

      <!-- Content Header (Page header) -->
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

      <!-- Main content -->
      <section class="content">

        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger">
            Verifique os campos e tente novamente.
          </div>
        @endif

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Editar Grupo de Permissão</h4>
              </div>

              <div class="box-body">

                <form method="POST" action="{{ route('config.grupos.update', ['sub' => request()->route('sub'), 'id' => $grupo->id]) }}">
                  @csrf
                  @method('PUT')

                  <!-- Nav tabs -->
                  <div class="vtabs w-100">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist" style="min-width: 220px;">
                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#grupo" role="tab">
                          <span><i class="ion-person me-15"></i>Grupo</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#usuarios" role="tab">
                          <span><i class="ion-home me-15"></i>Usuários</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#permissoes" role="tab">
                          <span><i class="ion-home me-15"></i>Permissões</span>
                        </a>
                      </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content w-100" style="flex: 1 1 auto;">

                      {{-- ABA GRUPO --}}
                      <div class="tab-pane fade show active" id="grupo" role="tabpanel">
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

                      {{-- ABA USUÁRIOS --}}
                      <div class="tab-pane fade" id="usuarios" role="tabpanel">
                        <div class="p-15">
                          <h3>Usuários</h3>

                          <div class="table-responsive">
                            <table class="table">
                              <thead class="bg-primary">
                                <tr>
                                  <th>Nome</th>
                                  <th>Filial / Setor</th>
                                  <th>Ações</th>
                                </tr>
                              </thead>
                              <tbody>
                                @forelse($usuarios as $u)
                                  <tr>
                                    <td>{{ $u->nome_completo }}</td>
                                    <td>{!! $u->lotacoes_html ?: '-' !!}</td>
                                    <td>
                                      <button type="button" class="btn btn-sm btn-outline-danger" disabled>
                                        Desvincular
                                      </button>
                                    </td>
                                  </tr>
                                @empty
                                  <tr>
                                    <td colspan="3" class="text-center">Nenhum usuário vinculado a este grupo.</td>
                                  </tr>
                                @endforelse
                              </tbody>
                            </table>
                            <small class="text-muted">
                              * O botão “Desvincular” será ativado quando criarmos a ação de desvincular.
                            </small>
                          </div>

                        </div>
                      </div>

                      {{-- ABA PERMISSÕES --}}
                      <div class="tab-pane fade" id="permissoes" role="tabpanel">
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

                                        {{-- ✅ Checkbox estilizado do template (input + label) --}}
                                        <td class="text-center">
                                          <input type="checkbox"
                                                 id="{{ $idAtivo }}"
                                                 class="chk-col-primary"
                                                 name="perm[{{ $t->id }}][ativo]"
                                                 value="1"
                                                 {{ $checkedAtivo ? 'checked' : '' }}>
                                          <label for="{{ $idAtivo }}"></label>
                                        </td>

                                        <td class="text-center">
                                          <input type="checkbox"
                                                 id="{{ $idCadastro }}"
                                                 class="chk-col-primary"
                                                 name="perm[{{ $t->id }}][cadastro]"
                                                 value="1"
                                                 {{ $checkedCadastro ? 'checked' : '' }}>
                                          <label for="{{ $idCadastro }}"></label>
                                        </td>

                                        <td class="text-center">
                                          <input type="checkbox"
                                                 id="{{ $idEditar }}"
                                                 class="chk-col-primary"
                                                 name="perm[{{ $t->id }}][editar]"
                                                 value="1"
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
                            <div class="alert alert-warning">
                              Nenhum módulo está vinculado a esta empresa.
                            </div>
                          @endforelse

                        </div>
                      </div>

                    </div>
                  </div>

                  {{-- ✅ BOTÃO SALVAR FORA DAS ABAS --}}
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
      <!-- /.content -->

    </div>
  </div>
  <!-- /.content-wrapper -->

  @includeIf('partials.footer')
</div>
<!-- ./wrapper -->

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- Coup Admin App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

</body>
</html>
