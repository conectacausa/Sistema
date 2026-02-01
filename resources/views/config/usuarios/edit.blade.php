{{-- resources/views/config/usuarios/edit.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Usuários</title>

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  <style>
    /* remover hover “estranho” (não muda cor no hover) */
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

    /* garantir tabela 100% */
    .table-responsive{ width: 100%; }
    table.w-100{ width: 100% !important; }

    /* ajuste visual leve para tabs verticais ocuparem bem */
    .vtabs .tabs-vertical{
      min-width: 220px;
    }
    .vtabs .tabs-vertical .nav-link{
      display:flex;
      align-items:center;
      gap:10px;
    }
    .vtabs .tabs-vertical .nav-link i{
      width: 18px;
      height: 18px;
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

      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Usuários</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="#"><i class="mdi mdi-home-outline"></i></a></li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item"><a href="{{ route('config.usuarios.index') }}">Usuários</a></li>
                  <li class="breadcrumb-item" aria-current="page">Editar Usuário</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <section class="content">

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

              <form method="POST"
                    action="{{ route('config.usuarios.update', ['id' => $usuario->id]) }}"
                    enctype="multipart/form-data"
                    id="formUsuario">
                @csrf
                @method('PUT')

                {{-- PADRÃO: conteúdo principal dentro de .row > .col-12 > .box > .box-body --}}
                <div class="box-body">

                  {{-- TABS VERTICAIS (padrão do projeto) --}}
                  <div class="vtabs">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist">

                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab_usuario" role="tab" aria-selected="true">
                          <i data-feather="user"></i>
                          <span>Usuário</span>
                        </a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_acesso" role="tab" aria-selected="false">
                          <i data-feather="lock"></i>
                          <span>Acesso</span>
                        </a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab_lotacao" role="tab" aria-selected="false">
                          <i data-feather="users"></i>
                          <span>Lotação</span>
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
                                      <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                              {{-- Linha 2 - CPF --}}
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">CPF</label>
                                    <input type="text"
                                           class="form-control @error('cpf') is-invalid @enderror"
                                           name="cpf"
                                           id="cpf"
                                           value="{{ old('cpf', $cpfMask) }}"
                                           placeholder="000.000.000-00">
                                    @error('cpf')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                              {{-- Linha 3 - E-mail e Telefone --}}
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">E-mail</label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           name="email"
                                           value="{{ old('email', $usuario->email ?? '') }}"
                                           placeholder="email@dominio.com">
                                    @error('email')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Telefone</label>
                                    <input type="text"
                                           class="form-control @error('telefone') is-invalid @enderror"
                                           name="telefone"
                                           id="telefone"
                                           value="{{ old('telefone', $telMask) }}"
                                           placeholder="(00)00000-0000">
                                    @error('telefone')
                                      <div class="invalid-feedback">{{ $message }}</div>
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
                                    <div class="text-danger mt-5">{{ $message }}</div>
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

                      {{-- TAB: Acesso --}}
                      <div class="tab-pane" id="tab_acesso" role="tabpanel">
                        <div class="p-15">
                          <h3>Acesso</h3>

                          <div class="row">
                            <div class="col-12 col-lg-8">

                              {{-- Grupo de Permissão --}}
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Grupo de Permissão</label>
                                    <select name="permissao_id"
                                            class="form-select @error('permissao_id') is-invalid @enderror">
                                      <option value="">Selecione</option>
                                      @foreach(($permissoes ?? []) as $p)
                                        <option value="{{ $p->id }}"
                                          @selected((int)old('permissao_id', $usuario->permissao_id ?? 0) === (int)$p->id)>
                                          {{ $p->nome_grupo }}
                                        </option>
                                      @endforeach
                                    </select>
                                    @error('permissao_id')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                              {{-- Expiração e Situação --}}
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Data / Hora Expiração</label>
                                    <input type="datetime-local"
                                           class="form-control @error('data_expiracao') is-invalid @enderror"
                                           name="data_expiracao"
                                           value="{{ old('data_expiracao', $dataExpValue) }}">
                                    @error('data_expiracao')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Situação</label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                                      <option value="ativo" @selected(old('status', $usuario->status ?? 'ativo') === 'ativo')>Ativo</option>
                                      <option value="inativo" @selected(old('status', $usuario->status ?? 'ativo') === 'inativo')>Inativo</option>
                                    </select>
                                    @error('status')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                            </div>
                          </div>

                        </div>
                      </div>

                      {{-- TAB: Lotação --}}
                      <div class="tab-pane" id="tab_lotacao" role="tabpanel">
                        <div class="p-15">
                          <h3>Lotação</h3>

                          {{-- (mantido) seleção principal de Filial/Setor (IDs preservados para o JS existente) --}}
                          <div class="row">
                            <div class="col-12">
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Filial</label>
                                    <select name="filial_id"
                                            id="filial_id"
                                            class="form-select @error('filial_id') is-invalid @enderror">
                                      <option value="">Selecione</option>
                                      @foreach(($filiais ?? []) as $f)
                                        <option value="{{ $f->id }}"
                                          @selected((string)old('filial_id', $filialId) === (string)$f->id)>
                                          {{ $f->nome }}
                                        </option>
                                      @endforeach
                                    </select>
                                    @error('filial_id')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Setor</label>
                                    <select name="setor_id"
                                            id="setor_id"
                                            class="form-select @error('setor_id') is-invalid @enderror"
                                            disabled>
                                      <option value="">Selecione</option>
                                    </select>
                                    @error('setor_id')
                                      <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                  </div>
                                </div>
                              </div>

                              <hr class="my-15">

                              {{-- filtros da tabela de lotações (mantido) --}}
                              <div class="row mb-15">
                                <div class="col-md-6">
                                  <label class="form-label">Filial</label>
                                  <select id="filtroFilialLotacao" class="form-select">
                                    <option value="">Selecione</option>
                                    @foreach(($filiais ?? []) as $f)
                                      <option value="{{ $f->id }}">{{ $f->nome }}</option>
                                    @endforeach
                                  </select>
                                </div>

                                <div class="col-md-6">
                                  <label class="form-label">Setor</label>
                                  <select id="filtroSetorLotacao" class="form-select" disabled>
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

                    </div> {{-- tab-content --}}
                  </div> {{-- vtabs --}}

                </div> {{-- box-body --}}

                <!-- BOTÃO SALVAR FORA DAS TABS (no final do form/box) -->
                <div class="box-footer text-end">
                  <button type="submit" class="btn btn-success btn-nohover" id="btnSalvarUsuario">
                    Salvar
                  </button>
                </div>

              </form>
            </div>
          </div>
        </div>

      </section>
    </div>
  </div>

  @include('partials.footer')
</div>

<!-- Vendor JS -->
<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<!-- Coup Admin App -->
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
  (function () {
    // Toastr helper
    function toast(type, msg) {
      if (window.toastr && typeof window.toastr[type] === 'function') {
        toastr.options = {
          closeButton: true,
          progressBar: true,
          positionClass: "toast-top-right",
          timeOut: "3500"
        };
        toastr[type](msg);
      }
    }

    function featherRefresh() {
      if (window.feather) feather.replace();
    }

    @if(session('success'))
      toast('success', @json(session('success')));
    @endif
    @if(session('error'))
      toast('error', @json(session('error')));
    @endif
    @if($errors && $errors->any())
      toast('error', 'Verifique os campos do formulário.');
    @endif

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
      featherRefresh(); // padrão após renderização dinâmica
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
        toast('error', 'Não foi possível carregar os setores desta filial.');
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
      featherRefresh(); // padrão após renderização dinâmica
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
          filtroSetorLotacao.appendChild(opt);
        });

        filtroSetorLotacao.disabled = false;
        featherRefresh(); // padrão após renderização dinâmica
      } catch (e) {
        toast('error', 'Não foi possível carregar os setores (Lotação).');
      }
    }

    if (filtroFilialLotacao) {
      filtroFilialLotacao.addEventListener('change', function () {
        carregarSetoresLotacao(this.value);
      });
    }

    if (filtroSetorLotacao) {
      filtroSetorLotacao.addEventListener('change', function () {
        const filial = filtroFilialLotacao ? filtroFilialLotacao.value : '';
        const setor = this.value;
        if (!filial || !setor) {
          resetLotacaoTable();
          return;
        }
        // Próximo passo: carregar tabela de lotações via AJAX
        resetLotacaoTable('Carregamento da tabela de lotações será habilitado no próximo passo.');
      });
    }

    // render inicial feather
    featherRefresh();
  })();
</script>
</body>
</html>
