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

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    .foto-preview-box{
      width: 100%;
      min-height: 220px;
      border: 1px dashed rgba(0,0,0,.2);
      border-radius: 10px;
      display:flex;
      align-items:center;
      justify-content:center;
      background: rgba(0,0,0,.02);
      overflow:hidden;
    }
    .foto-preview-box img{
      width:100%;
      height:220px;
      object-fit: cover;
      display:block;
    }
    .foto-preview-placeholder{
      text-align:center;
      color:#7a7a7a;
      font-size: 12px;
      padding: 10px;
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

      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">Usuários</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a></li>
                  <li class="breadcrumb-item">Configuração</li>
                  <li class="breadcrumb-item"><a href="{{ route('config.usuarios.index') }}">Usuários</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Editar Usuário</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <section class="content">

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Cadastro de Usuário</h4>
              </div>

              <div class="box-body">
                <div class="vtabs">
                  <ul class="nav nav-tabs tabs-vertical" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" data-bs-toggle="tab" href="#usuarios" role="tab">
                        <span><i class="ion-person me-15"></i>Usuário</span>
                      </a>
                    </li>

                    <li class="nav-item">
                      <a class="nav-link" data-bs-toggle="tab" href="#lotacao" role="tab">
                        <span><i class="ion-home me-15"></i>Lotação</span>
                      </a>
                    </li>
                  </ul>

                  <div class="tab-content">
                    {{-- TAB USUÁRIO --}}
                    <div class="tab-pane active" id="usuarios" role="tabpanel">
                      <div class="p-15">
                        <h3>Usuário</h3>

                        @if ($errors->any())
                          <div class="alert alert-danger">
                            <ul class="mb-0">
                              @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                              @endforeach
                            </ul>
                          </div>
                        @endif

                        <form method="POST"
                              action="{{ route('config.usuarios.update', ['id' => $usuario->id]) }}"
                              enctype="multipart/form-data"
                              id="formUsuario">
                          @csrf
                          @method('PUT')

                          <div class="row">
                            {{-- COLUNA ESQUERDA --}}
                            <div class="col-md-9">
                              <div class="row">

                                {{-- Linha 1 - Nome Completo --}}
                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label class="form-label">Nome Completo</label>
                                    <input type="text"
                                           name="nome_completo"
                                           class="form-control"
                                           value="{{ old('nome_completo', $usuario->nome_completo) }}"
                                           required>
                                  </div>
                                </div>

                                {{-- Linha 2 - CPF e Grupo --}}
                                <div class="col-md-4">
                                  <div class="form-group">
                                    <label class="form-label">CPF</label>
                                    <input type="text"
                                           name="cpf"
                                           id="cpf"
                                           class="form-control"
                                           value="{{ old('cpf', $usuario->cpf) }}"
                                           required>
                                  </div>
                                </div>

                                <div class="col-md-8">
                                  <div class="form-group">
                                    <label class="form-label">Grupo de Permissão</label>
                                    <select name="permissao_id" class="form-select" required>
                                      <option value="">Selecione</option>
                                      @foreach($permissoes as $p)
                                        <option value="{{ $p->id }}"
                                          {{ (string)old('permissao_id', $usuario->permissao_id) === (string)$p->id ? 'selected' : '' }}>
                                          {{ $p->nome_grupo }}
                                        </option>
                                      @endforeach
                                    </select>
                                  </div>
                                </div>

                                {{-- Linha 3 - Filial e Setor --}}
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Filial</label>
                                    <select name="filial_id" id="filial_id" class="form-select">
                                      <option value="">Selecione</option>
                                      @foreach($filiais as $f)
                                        <option value="{{ $f->id }}" {{ (string)old('filial_id', $filialId) === (string)$f->id ? 'selected' : '' }}>
                                          {{ $f->nome }}
                                        </option>
                                      @endforeach
                                    </select>
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Setor</label>
                                    <select name="setor_id" id="setor_id" class="form-select" disabled>
                                      <option value="">Selecione a filial</option>
                                    </select>
                                  </div>
                                </div>

                                {{-- Linha 4 - E-mail e Telefone --}}
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">E-mail</label>
                                    <input type="email"
                                           name="email"
                                           class="form-control"
                                           value="{{ old('email', $usuario->email) }}">
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Telefone</label>
                                    <input type="text"
                                           name="telefone"
                                           id="telefone"
                                           class="form-control"
                                           value="{{ old('telefone', $usuario->telefone) }}">
                                  </div>
                                </div>

                                {{-- Linha 5 - Data/Hora Expiração e Situação --}}
                                @php
                                  $exp = old('data_expiracao', $usuario->data_expiracao ?? '');
                                  // converte "YYYY-MM-DD HH:MM:SS" para "YYYY-MM-DDTHH:MM"
                                  if ($exp && strpos($exp, ' ') !== false) {
                                      $exp = str_replace(' ', 'T', substr($exp, 0, 16));
                                  }
                                @endphp

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Data/Hora de Expiração</label>
                                    <input type="datetime-local"
                                           name="data_expiracao"
                                           class="form-control"
                                           value="{{ $exp }}">
                                  </div>
                                </div>

                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="form-label">Situação</label>
                                    <select name="status" class="form-select" required>
                                      <option value="ativo" {{ old('status', $usuario->status) === 'ativo' ? 'selected' : '' }}>Ativo</option>
                                      <option value="inativo" {{ old('status', $usuario->status) === 'inativo' ? 'selected' : '' }}>Inativo</option>
                                    </select>
                                  </div>
                                </div>

                                {{-- Botão Salvar à direita --}}
                                <div class="col-md-12">
                                  <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn bg-gradient-success waves-effect waves-light">
                                      Salvar
                                    </button>
                                  </div>
                                </div>

                              </div>
                            </div>

                            {{-- COLUNA DIREITA (FOTO) --}}
                            <div class="col-md-3">
                              <div class="form-group">
                                <label class="form-label">Foto</label>

                                @php
                                  $fotoUrl = '';
                                  if (!empty($usuario->foto)) {
                                    // foto salva no disk public
                                    $fotoUrl = asset('storage/' . ltrim($usuario->foto, '/'));
                                  }
                                @endphp

                                <div class="foto-preview-box mb-2" id="fotoPreviewBox">
                                  <div class="foto-preview-placeholder" id="fotoPlaceholder" style="{{ $fotoUrl ? 'display:none;' : '' }}">
                                    Nenhuma foto selecionada<br>
                                    <small>Selecione um arquivo para visualizar</small>
                                  </div>

                                  <img id="fotoPreviewImg" src="{{ $fotoUrl }}" alt="Preview" style="{{ $fotoUrl ? '' : 'display:none;' }}">
                                </div>

                                <input type="file"
                                       name="foto"
                                       id="foto"
                                       class="form-control"
                                       accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">JPG/PNG/WebP até 2MB</small>
                              </div>
                            </div>

                          </div>
                        </form>
                      </div>
                    </div>

                    {{-- TAB LOTAÇÃO --}}
                    <div class="tab-pane" id="lotacao" role="tabpanel">
                      <div class="p-15">
                        <h3>Lotações</h3>

                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="form-label">Filial</label>
                              <select id="lot_filial" class="form-select">
                                <option value="">Todas</option>
                                @foreach($filiais as $f)
                                  <option value="{{ $f->id }}">{{ $f->nome }}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>

                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="form-label">Setor</label>
                              <select id="lot_setor" class="form-select" disabled>
                                <option value="">Selecione a filial</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="table-responsive">
                          <table class="table">
                            <thead class="bg-primary">
                              <tr>
                                <th>Filial</th>
                                <th>Setor</th>
                                <th>Cargo</th>
                                <th>Vínculo</th>
                              </tr>
                            </thead>
                            <tbody id="lotacoesBody">
                              <tr>
                                <td colspan="4" class="text-center">Carregando...</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>

                        <input type="hidden" id="usuario_id" value="{{ $usuario->id }}">
                      </div>
                    </div>

                  </div>
                </div>
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
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
  function notifySuccess(msg) { if (window.toastr) toastr.success(msg); else alert(msg); }
  function notifyError(msg) { if (window.toastr) toastr.error(msg); else alert(msg); }

  function maskCPF(v) {
    v = (v || '').replace(/\D/g,'').slice(0,11);
    if (v.length <= 3) return v;
    if (v.length <= 6) return v.replace(/(\d{3})(\d+)/,'$1.$2');
    if (v.length <= 9) return v.replace(/(\d{3})(\d{3})(\d+)/,'$1.$2.$3');
    return v.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/,'$1.$2.$3-$4');
  }
  function maskPhone(v) {
    v = (v || '').replace(/\D/g,'').slice(0,11);
    if (v.length <= 2) return '('+v;
    if (v.length <= 7) return v.replace(/(\d{2})(\d+)/,'($1)$2');
    return v.replace(/(\d{2})(\d{5})(\d+)/,'($1)$2-$3');
  }

  async function fetchJSON(url) {
    const r = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } });
    if (!r.ok) throw new Error('Erro ao carregar');
    return await r.json();
  }

  document.addEventListener('DOMContentLoaded', function () {
    if (window.feather) feather.replace();

    // máscara cpf/telefone + normaliza preenchidos
    const cpf = document.getElementById('cpf');
    if (cpf) {
      cpf.value = maskCPF(cpf.value);
      cpf.addEventListener('input', () => cpf.value = maskCPF(cpf.value));
    }

    const tel = document.getElementById('telefone');
    if (tel) {
      tel.value = maskPhone(tel.value);
      tel.addEventListener('input', () => tel.value = maskPhone(tel.value));
    }

    // preview foto
    const fotoInput = document.getElementById('foto');
    const img = document.getElementById('fotoPreviewImg');
    const placeholder = document.getElementById('fotoPlaceholder');

    if (fotoInput && img && placeholder) {
      fotoInput.addEventListener('change', function () {
        const file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) return;

        const allowed = ['image/jpeg','image/png','image/webp'];
        if (!allowed.includes(file.type)) {
          notifyError('Formato inválido. Use JPG, PNG ou WebP.');
          fotoInput.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
          img.src = e.target.result;
          img.style.display = 'block';
          placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
      });
    }

    // Filial -> Setor (carregar e já selecionar setor salvo se existir)
    const filial = document.getElementById('filial_id');
    const setor = document.getElementById('setor_id');
    const setorSelecionado = @json(old('setor_id', $setorId));

    async function loadSetores(filialId, selectedId) {
      setor.innerHTML = '<option value="">Carregando...</option>';
      setor.disabled = true;

      if (!filialId) {
        setor.innerHTML = '<option value="">Selecione a filial</option>';
        setor.disabled = true;
        return;
      }

      try {
        const data = await fetchJSON(`{{ route('config.usuarios.setores_por_filial') }}?filial_id=${filialId}`);
        setor.innerHTML = '<option value="">Selecione</option>';
        data.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.id;
          opt.textContent = s.nome;
          if (String(selectedId) !== 'null' && String(selectedId) === String(s.id)) opt.selected = true;
          setor.appendChild(opt);
        });
        setor.disabled = false;
      } catch (e) {
        notifyError('Falha ao carregar setores.');
        setor.innerHTML = '<option value="">Selecione a filial</option>';
        setor.disabled = true;
      }
    }

    if (filial && setor) {
      // carregamento inicial (se já tiver filial)
      if (filial.value) loadSetores(filial.value, setorSelecionado);

      filial.addEventListener('change', function () {
        loadSetores(this.value, null);
      });
    }

    // toastr flash
    @if(session('success')) notifySuccess(@json(session('success'))); @endif
    @if(session('error')) notifyError(@json(session('error'))); @endif
  });
</script>

</body>
</html>
