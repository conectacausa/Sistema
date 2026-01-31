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

  <!-- Vendors Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">

  <!-- Style-->
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

  <meta name="csrf-token" content="{{ csrf_token() }}">
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
                  <li class="breadcrumb-item active" aria-current="page">Novo Usuário</li>
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
                      <a class="nav-link {{ empty($usuario) ? 'disabled' : '' }}"
                         data-bs-toggle="tab"
                         href="#lotacao"
                         role="tab"
                         aria-disabled="{{ empty($usuario) ? 'true' : 'false' }}"
                         style="{{ empty($usuario) ? 'pointer-events:none; opacity:.5;' : '' }}">
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

                        <form method="POST" action="{{ route('config.usuarios.store') }}" enctype="multipart/form-data" id="formUsuario">
                          @csrf

                          <div class="row">
                            {{-- Foto ocupando “canto” (linha 1 e 2) --}}
                            <div class="col-md-3">
                              <div class="form-group">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                                <small class="text-muted">JPG/PNG/WebP até 2MB</small>
                              </div>
                            </div>

                            <div class="col-md-9">
                              <div class="row">
                                {{-- Linha 1: Nome completo --}}
                                <div class="col-md-12">
                                  <div class="form-group">
                                    <label class="form-label">Nome Completo</label>
                                    <input type="text" name="nome_completo" class="form-control"
                                           value="{{ old('nome_completo', $usuario->nome_completo ?? '') }}"
                                           required>
                                  </div>
                                </div>

                                {{-- Linha 2: CPF / Grupo / Filial / Setor --}}
                                <div class="col-md-3">
                                  <div class="form-group">
                                    <label class="form-label">CPF</label>
                                    <input type="text" name="cpf" id="cpf" class="form-control"
                                           value="{{ old('cpf') }}" required>
                                  </div>
                                </div>

                                <div class="col-md-3">
                                  <div class="form-group">
                                    <label class="form-label">Grupo de Permissão</label>
                                    <select name="permissao_id" class="form-select" required>
                                      <option value="">Selecione</option>
                                      @foreach($permissoes as $p)
                                        <option value="{{ $p->id }}" {{ (string)old('permissao_id') === (string)$p->id ? 'selected' : '' }}>
                                          {{ $p->nome_grupo }}
                                        </option>
                                      @endforeach
                                    </select>
                                  </div>
                                </div>

                                <div class="col-md-3">
                                  <div class="form-group">
                                    <label class="form-label">Filial</label>
                                    <select name="filial_id" id="filial_id" class="form-select">
                                      <option value="">Selecione</option>
                                      @foreach($filiais as $f)
                                        <option value="{{ $f->id }}">{{ $f->nome }}</option>
                                      @endforeach
                                    </select>
                                  </div>
                                </div>

                                <div class="col-md-3">
                                  <div class="form-group">
                                    <label class="form-label">Setor</label>
                                    <select name="setor_id" id="setor_id" class="form-select" disabled>
                                      <option value="">Selecione a filial</option>
                                    </select>
                                  </div>
                                </div>

                              </div>
                            </div>
                          </div>

                          {{-- Linha 3: Email / Telefone --}}
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" id="telefone" class="form-control" value="{{ old('telefone') }}">
                              </div>
                            </div>
                          </div>

                          {{-- Linha 4: Expiração / Situação --}}
                          <div class="row">
                            <div class="col-md-3">
                              <div class="form-group">
                                <label class="form-label">Data de Expiração</label>
                                <input type="date" name="data_expiracao" class="form-control" value="{{ old('data_expiracao') }}">
                              </div>
                            </div>
                            <div class="col-md-3">
                              <div class="form-group">
                                <label class="form-label">Situação</label>
                                <select name="status" class="form-select" required>
                                  <option value="ativo" {{ old('status','ativo') === 'ativo' ? 'selected' : '' }}>Ativo</option>
                                  <option value="inativo" {{ old('status') === 'inativo' ? 'selected' : '' }}>Inativo</option>
                                </select>
                              </div>
                            </div>
                          </div>

                          <div class="mt-3">
                            <button type="submit" class="btn bg-gradient-success waves-effect waves-light">
                              Salvar
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>

                    {{-- TAB LOTAÇÃO --}}
                    <div class="tab-pane" id="lotacao" role="tabpanel">
                      <div class="p-15">
                        <h3>Lotações</h3>

                        @if(empty($usuario))
                          <div class="alert alert-warning">
                            Salve o usuário para habilitar as lotações.
                          </div>
                        @else
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
                        @endif
                      </div>
                    </div>

                  </div> {{-- tab-content --}}
                </div> {{-- vtabs --}}
              </div> {{-- box-body --}}
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
  // toastr com fallback
  function notifySuccess(msg) {
    if (window.toastr) toastr.success(msg);
    else alert(msg);
  }
  function notifyError(msg) {
    if (window.toastr) toastr.error(msg);
    else alert(msg);
  }

  // máscaras simples
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
    // feather
    if (window.feather) feather.replace();

    // máscara cpf/telefone
    const cpf = document.getElementById('cpf');
    if (cpf) cpf.addEventListener('input', () => cpf.value = maskCPF(cpf.value));

    const tel = document.getElementById('telefone');
    if (tel) tel.addEventListener('input', () => tel.value = maskPhone(tel.value));

    // Filial -> Setor (tab usuário)
    const filial = document.getElementById('filial_id');
    const setor = document.getElementById('setor_id');

    if (filial && setor) {
      filial.addEventListener('change', async function () {
        const filialId = this.value;
        setor.innerHTML = '';
        if (!filialId) {
          setor.disabled = true;
          setor.innerHTML = '<option value="">Selecione a filial</option>';
          return;
        }
        try {
          setor.disabled = true;
          setor.innerHTML = '<option value="">Carregando...</option>';
          const data = await fetchJSON(`{{ route('config.usuarios.setores_por_filial') }}?filial_id=${filialId}`);
          setor.innerHTML = '<option value="">Selecione</option>';
          data.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.nome;
            setor.appendChild(opt);
          });
          setor.disabled = false;
        } catch (e) {
          notifyError('Falha ao carregar setores.');
          setor.disabled = true;
          setor.innerHTML = '<option value="">Selecione a filial</option>';
        }
      });
    }

    // TAB LOTAÇÃO (só se houver usuário)
    const usuarioIdEl = document.getElementById('usuario_id');
    if (usuarioIdEl) {
      const usuarioId = usuarioIdEl.value;
      const lotFilial = document.getElementById('lot_filial');
      const lotSetor = document.getElementById('lot_setor');
      const tbody = document.getElementById('lotacoesBody');

      async function loadLotacoes() {
        const f = lotFilial.value || '';
        const s = lotSetor.value || '';
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Carregando...</td></tr>';

        try {
          const url = `{{ route('config.usuarios.lotacoes_grid') }}?usuario_id=${usuarioId}&filial_id=${f}&setor_id=${s}`;
          const rows = await fetchJSON(url);

          if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Nenhum registro.</td></tr>';
            return;
          }

          tbody.innerHTML = '';
          rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${r.filial_nome ?? '-'}</td>
              <td>${r.setor_nome ?? '-'}</td>
              <td>${r.cargo_titulo ?? '-'}</td>
              <td>
                <input type="checkbox" class="chkVinculo"
                  data-filial="${r.filial_id}"
                  data-setor="${r.setor_id}"
                  data-cargo="${r.cargo_id}"
                  ${r.vinculado ? 'checked' : ''} />
              </td>
            `;
            tbody.appendChild(tr);
          });
        } catch (e) {
          tbody.innerHTML = '<tr><td colspan="4" class="text-center">Erro ao carregar.</td></tr>';
          notifyError('Erro ao carregar lotações.');
        }
      }

      // Filial -> Setor (tab lotação)
      lotFilial.addEventListener('change', async function () {
        const filialId = this.value;
        lotSetor.innerHTML = '';
        if (!filialId) {
          lotSetor.disabled = true;
          lotSetor.innerHTML = '<option value="">Selecione a filial</option>';
          await loadLotacoes();
          return;
        }
        try {
          lotSetor.disabled = true;
          lotSetor.innerHTML = '<option value="">Carregando...</option>';
          const data = await fetchJSON(`{{ route('config.usuarios.setores_por_filial') }}?filial_id=${filialId}`);
          lotSetor.innerHTML = '<option value="">Todos</option>';
          data.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.nome;
            lotSetor.appendChild(opt);
          });
          lotSetor.disabled = false;
          await loadLotacoes();
        } catch (e) {
          notifyError('Falha ao carregar setores.');
          lotSetor.disabled = true;
          lotSetor.innerHTML = '<option value="">Selecione a filial</option>';
        }
      });

      lotSetor.addEventListener('change', loadLotacoes);

      // Toggle checkbox (delegação)
      document.addEventListener('change', async function (e) {
        if (!e.target.classList.contains('chkVinculo')) return;

        const chk = e.target;
        const payload = {
          usuario_id: usuarioId,
          filial_id: chk.dataset.filial,
          setor_id: chk.dataset.setor,
          cargo_id: chk.dataset.cargo,
          checked: chk.checked ? 1 : 0,
        };

        try {
          const r = await fetch(`{{ route('config.usuarios.toggle_lotacao') }}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload),
          });

          if (!r.ok) throw new Error('fail');
          notifySuccess('Vínculo atualizado.');
          // opcional: recarrega para garantir ordenação com vinculados primeiro
          await loadLotacoes();
        } catch (err) {
          chk.checked = !chk.checked;
          notifyError('Não foi possível atualizar o vínculo.');
        }
      });

      // Carrega inicialmente
      loadLotacoes();
    }

    // toastr flash do laravel
    @if(session('success'))
      notifySuccess(@json(session('success')));
    @endif
    @if(session('error'))
      notifyError(@json(session('error')));
    @endif
  });
</script>

</body>
</html>
