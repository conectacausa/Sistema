{{-- resources/views/config/usuarios/edit.blade.php --}}
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

    /* remover hover “estranho” */
    .btn-nohover:hover, .btn-nohover:focus, .btn-nohover:active {
      filter: none !important;
      opacity: 1 !important;
      transform: none !important;
      box-shadow: none !important;
    }

    /* FOTO */
    .user-photo-box{
      width: 100%;
      border: 1px dashed rgba(0,0,0,.15);
      border-radius: 8px;
      padding: 10px;
      background: rgba(0,0,0,.02);
    }
    .user-photo-preview{
      width: 100%;
      height: 260px;
      border-radius: 8px;
      object-fit: cover;
      background: #f5f6f7;
      display:block;
    }
    .user-photo-actions{
      margin-top: 10px;
      display:flex;
      gap:10px;
      align-items:center;
      justify-content:space-between;
    }

    .table-responsive{ width: 100%; }
    table.w-100{ width: 100% !important; }
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
            <h4 class="page-title">Usuários</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => request()->route('sub')]) }}">
                      <i class="mdi mdi-home-outline"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item"><a href="{{ route('config.usuarios.index') }}">Usuários</a></li>
                  <li class="breadcrumb-item" aria-current="page">Editar Usuário</li>
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

        @if(session('error'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('error') }}
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
                <h4 class="box-title">Cadastro de Usuário</h4>
              </div>

              @php
                $filialId = $filialId ?? null;
                $setorId  = $setorId ?? null;

                $cpfDigits = preg_replace('/\D+/', '', (string)($usuario->cpf ?? ''));
                $cpfMask = (strlen($cpfDigits) === 11)
                  ? substr($cpfDigits,0,3).'.'.substr($cpfDigits,3,3).'.'.substr($cpfDigits,6,3).'-'.substr($cpfDigits,9,2)
                  : (string)($usuario->cpf ?? '');

                $telDigits = preg_replace('/\D+/', '', (string)($usuario->telefone ?? ''));
                $telMask = $usuario->telefone ?? '';
                if (strlen($telDigits) === 11) {
                  $telMask = '('.substr($telDigits,0,2).')'.substr($telDigits,2,5).'-'.substr($telDigits,7,4);
                } elseif (strlen($telDigits) === 10) {
                  $telMask = '('.substr($telDigits,0,2).')'.substr($telDigits,2,4).'-'.substr($telDigits,6,4);
                }

                $dataExp = $usuario->data_expiracao ?? null;
                $dataExpValue = '';
                if ($dataExp) {
                  try { $dataExpValue = \Carbon\Carbon::parse($dataExp)->format('Y-m-d\TH:i'); } catch (\Throwable $e) {}
                }

                $fotoUrl = !empty($usuario->foto)
                  ? asset('storage/'.$usuario->foto)
                  : asset('assets/images/avatar/avatar-1.png');
              @endphp

              <div class="box-body">

                <form method="POST"
                      action="{{ route('config.usuarios.update', ['id' => $usuario->id]) }}"
                      enctype="multipart/form-data"
                      id="formUsuario">
                  @csrf
                  @method('PUT')

                  <div class="vtabs">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab_usuario" role="tab">
                          <span><i class="ion-person me-15"></i>Usuário</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_lotacao" role="tab">
                          <span><i class="ion-person-stalker me-15"></i>Lotação</span>
                        </a>
                      </li>
                    </ul>

                    <div class="tab-content">

                      {{-- TAB: Usuário --}}
                      <div class="tab-pane active" id="tab_usuario" role="tabpanel">
                        <div class="p-15">
                          <h3>Usuário</h3>

                          <div class="row">
                            {{-- COL: campos --}}
                            <div class="col-12 col-lg-8">

                              {{-- Linha 1 - Nome Completo --}}
                              <div class="row">
                                <div class="col-12">
                                  <div class="form-group">
                                    <label class="form-label">Nome Completo</label>
                                    <input type="text"
                                           class="form-control @error('nome_completo') is-invalid @enderror"
                                           name="nome_completo"
                                           value="{{ old('nome_completo', $usuario->nome_completo ?? '') }}"
                                           placeholder="Nome completo">
                                    @error('nome_completo')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                              {{-- Linha 2 - CPF e Grupo --}}
                              <div class="row">
                                <div class="col-md-6 col-12">
                                  <div class="form-group">
                                    <label class="form-label">CPF</label>
                                    <input type="text"
                                           class="form-control @error('cpf') is-invalid @enderror"
                                           name="cpf"
                                           id="cpf"
                                           value="{{ old('cpf', $cpfMask) }}"
                                           placeholder="000.000.000-00">
                                    @error('cpf')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>

                                <div class="col-md-6 col-12">
                                  <div class="form-group">
                                    <label class="form-label">Grupo de Permissão</label>
                                    <select name="permissao_id"
                                            class="form-control @error('permissao_id') is-invalid @enderror">
                                      <option value="">Selecione</option>
                                      @foreach(($permissoes ?? []) as $p)
                                        <option value="{{ $p->id }}"
                                          @selected((int)old('permissao_id', $usuario->permissao_id ?? 0) === (int)$p->id)>
                                          {{ $p->nome_grupo }}
                                        </option>
                                      @endforeach
                                    </select>
                                    @error('permissao_id')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                              {{-- Linha 3 - Filial e Setor --}}
                              <div class="row">
                                <div class="col-md-6 col-12">
                                  <div class="form-group">
                                    <label class="form-label">Filial</label>
                                    <select name="filial_id"
                                            id="filial_id"
                                            class="form-control @error('filial_id') is-invalid @enderror">
                                      <option value="">Selecione</option>
                                      @foreach(($filiais ?? []) as $f)
                                        <option value="{{ $f->id }}"
                                          @selected((string)old('filial_id', $filialId) === (string)$f->id)>
                                          {{ $f->nome }}
                                        </option>
                                      @endforeach
                                    </select>
                                    @error('filial_id')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>

                                <div class="col-md-6 col-12">
                                  <div class="form-group">
                                    <label class="form-label">Setor</label>
                                    <select name="setor_id"
                                            id="setor_id"
                                            class="form-control @error('setor_id') is-invalid @enderror"
                                            disabled>
                                      <option value="">Selecione</option>
                                    </select>
                                    @error('setor_id')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                              {{-- Linha 4 - E-mail e Telefone --}}
                              <div class="row">
                                <div class="col-md-6 col-12">
                                  <div class="form-group">
                                    <label class="form-label">E-mail</label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           name="email"
                                           value="{{ old('email', $usuario->email ?? '') }}"
                                           placeholder="email@dominio.com">
                                    @error('email')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>

                                <div class="col-md-6 col-12">
                                  <div class="form-group">
                                    <label class="form-label">Telefone</label>
                                    <input type="text"
                                           class="form-control @error('telefone') is-invalid @enderror"
                                           name="telefone"
                                           id="telefone"
                                           value="{{ old('telefone', $telMask) }}"
                                           placeholder="(00)00000-0000">
                                    @error('telefone')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                              {{-- Linha 5 - Expiração e Situação --}}
                              <div class="row">
                                <div class="col-md-6 col-12">
                                  <div class="form-group">
                                    <label class="form-label">Data / Hora Expiração</label>
                                    <input type="datetime-local"
                                           class="form-control @error('data_expiracao') is-invalid @enderror"
                                           name="data_expiracao"
                                           value="{{ old('data_expiracao', $dataExpValue) }}">
                                    @error('data_expiracao')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>

                                <div class="col-md-6 col-12">
                                  <div class="form-group">
                                    <label class="form-label">Situação</label>
                                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                                      <option value="ativo" @selected(old('status', $usuario->status ?? 'ativo') === 'ativo')>Ativo</option>
                                      <option value="inativo" @selected(old('status', $usuario->status ?? 'ativo') === 'inativo')>Inativo</option>
                                    </select>
                                    @error('status')
                                      <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                            </div>

                            {{-- COL: Foto --}}
                            <div class="col-12 col-lg-4">
                              <div class="form-group">
                                <label class="form-label">Foto</label>

                                <div class="user-photo-box">
                                  <img src="{{ $fotoUrl }}" id="fotoPreview" alt="Foto do usuário" class="user-photo-preview">

                                  <div class="user-photo-actions">
                                    <input type="file"
                                           name="foto"
                                           id="foto"
                                           class="form-control @error('foto') is-invalid @enderror"
                                           accept="image/*">
                                  </div>

                                  @error('foto')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                  @enderror
                                </div>

                                <small class="text-muted d-block mt-5">
                                  Formatos: JPG, PNG, WEBP (até 2MB)
                                </small>
                              </div>
                            </div>

                          </div>

                        </div>
                      </div>

                      {{-- TAB: Lotação --}}
                      <div class="tab-pane" id="tab_lotacao" role="tabpanel">
                        <div class="p-15">
                          <h3>Lotações</h3>

                          <div class="row">
                            <div class="col-12">

                              <div class="row mb-15">
                                <div class="col-md-6 col-12">
                                  <label class="form-label">Filial</label>
                                  <select id="filtroFilialLotacao" class="form-control">
                                    <option value="">Selecione</option>
                                    @foreach(($filiais ?? []) as $f)
                                      <option value="{{ $f->id }}">{{ $f->nome }}</option>
                                    @endforeach
                                  </select>
                                </div>

                                <div class="col-md-6 col-12">
                                  <label class="form-label">Setor</label>
                                  <select id="filtroSetorLotacao" class="form-control" disabled>
                                    <option value="">Selecione</option>
                                  </select>
                                </div>
                              </div>

                              <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle w-100">
                                  <thead class="bg-primary">
                                    <tr>
                                      <th style="width: 28%;">Filial</th>
                                      <th style="width: 28%;">Setor</th>
                                      <th style="width: 34%;">Cargo</th>
                                      <th class="text-center" style="width: 10%;">Vínculo</th>
                                    </tr>
                                  </thead>
                                  <tbody id="tabelaLotacoes">
                                    <tr>
                                      <td colspan="4" class="text-center text-muted">
                                        Selecione a Filial e o Setor para carregar as lotações.
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              </div>

                            </div>
                          </div>

                        </div>
                      </div>

                    </div>
                  </div>

                  {{-- ✅ Salvar fora das abas (no final do form) --}}
                  <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="waves-effect waves-light btn bg-gradient-success btn-nohover" id="btnSalvarUsuario">
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

<script>
  (function () {

    // Preview foto
    const inputFoto = document.getElementById('foto');
    const preview = document.getElementById('fotoPreview');
    if (inputFoto && preview) {
      inputFoto.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) { preview.src = e.target.result; };
        reader.readAsDataURL(file);
      });
    }

    // Máscaras (se inputmask existir no vendors)
    if (window.jQuery) {
      const $ = window.jQuery;
      if ($.fn.inputmask) {
        $('#cpf').inputmask('999.999.999-99');
        $('#telefone').inputmask('(99)99999-9999');
      }
    }

    // Carregar setores ao escolher filial (endpoint já existente no projeto)
    const filialSelect = document.getElementById('filial_id');
    const setorSelect  = document.getElementById('setor_id');
    const setorPreSelecionado = @json(old('setor_id', $setorId));

    function setSetorOptions(items, selectedId) {
      if (!setorSelect) return;
      setorSelect.innerHTML = '<option value="">Selecione</option>';
      (items || []).forEach(function (s) {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.nome;
        if (String(selectedId) === String(s.id)) opt.selected = true;
        setorSelect.appendChild(opt);
      });
      setorSelect.disabled = false;

      // padrão do projeto após AJAX
      if (window.feather) feather.replace();
    }

    async function carregarSetoresParaSelect(filialId, selectedId) {
      if (!filialId || !setorSelect) {
        if (setorSelect) {
          setorSelect.innerHTML = '<option value="">Selecione</option>';
          setorSelect.disabled = true;
        }
        return;
      }

      try {
        const url = @json(url('/cargos/setores-por-filial')) + '?filial_id=' + encodeURIComponent(filialId);
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        if (!res.ok) throw new Error('Falha ao buscar setores');
        const data = await res.json();
        setSetorOptions(data, selectedId);
      } catch (e) {
        if (setorSelect) {
          setorSelect.innerHTML = '<option value="">Selecione</option>';
          setorSelect.disabled = true;
        }
        // alert simples no padrão
        const alertArea = document.getElementById('alert-area');
        if (alertArea) {
          alertArea.innerHTML = `
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              Não foi possível carregar os setores desta filial.
            </div>
          `;
        }
      }
    }

    if (filialSelect) {
      filialSelect.addEventListener('change', function () {
        carregarSetoresParaSelect(this.value, null);
      });

      // on load
      const filialInicial = filialSelect.value;
      if (filialInicial) {
        carregarSetoresParaSelect(filialInicial, setorPreSelecionado);
      }
    }

    // Lotação: carregar setores do filtro
    const filtroFilialLotacao = document.getElementById('filtroFilialLotacao');
    const filtroSetorLotacao  = document.getElementById('filtroSetorLotacao');

    function resetLotacaoTable(msg) {
      const tbody = document.getElementById('tabelaLotacoes');
      if (!tbody) return;
      tbody.innerHTML = `
        <tr>
          <td colspan="4" class="text-center text-muted">${msg || 'Selecione a Filial e o Setor para carregar as lotações.'}</td>
        </tr>
      `;
      if (window.feather) feather.replace();
    }

    async function carregarSetoresLotacao(filialId) {
      if (!filtroSetorLotacao) return;
      filtroSetorLotacao.innerHTML = '<option value="">Selecione</option>';
      filtroSetorLotacao.disabled = true;
      resetLotacaoTable();

      if (!filialId) return;

      try {
        const url = @json(url('/cargos/setores-por-filial')) + '?filial_id=' + encodeURIComponent(filialId);
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
        if (!res.ok) throw new Error('Falha ao buscar setores');
        const data = await res.json();

        (data || []).forEach(function (s) {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = s.nome;
          filtroSetorLotacao.appendChil
