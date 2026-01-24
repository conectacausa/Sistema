<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>Conectta RH | CBOs</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">

<div class="wrapper">
  <div id="loader"></div>

  {{-- Header --}}
  @includeIf('partials.header')
  @includeIf('layouts.partials.header')
  @includeIf('includes.header')

  {{-- Menu --}}
  @includeIf('partials.menu')
  @includeIf('layouts.partials.menu')
  @includeIf('includes.menu')

  <div class="content-wrapper">
    <div class="container-full">

      <div class="content-header">
        <div class="d-flex align-items-center">
          <div class="me-auto">
            <h4 class="page-title">CBOs</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard') }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Cadastro</li>
                  <li class="breadcrumb-item"><a href="{{ route('cargos.cbo.index') }}">CBOs</a></li>
                  <li class="breadcrumb-item active" aria-current="page">Novo</li>
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
                <h4 class="box-title">Cadastro CBO</h4>
              </div>

              <form method="POST" action="{{ route('cargos.cbo.store') }}">
                @csrf

                <div class="box-body">
                  @if ($errors->any())
                    <div class="alert alert-danger">
                      <ul style="margin:0;padding-left:18px;">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif

                  <div class="row">
                    <div class="col-md-2">
                      <div class="form-group">
                        <label class="form-label">Código</label>
                        <input
                          id="cbo-codigo"
                          name="cbo"
                          type="text"
                          class="form-control"
                          placeholder="CBO"
                          value="{{ old('cbo') }}"
                          required
                        >
                        <small id="cbo-erro" class="text-danger" style="display:none;"></small>
                      </div>
                    </div>

                    <div class="col-md-10">
                      <div class="form-group">
                        <label class="form-label">Título</label>
                        <input
                          name="titulo"
                          type="text"
                          class="form-control"
                          placeholder="Título"
                          value="{{ old('titulo') }}"
                          required
                        >
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="form-label">Descrição</label>
                        <textarea
                          name="descricao"
                          class="form-control"
                          placeholder="Descrição"
                          rows="4"
                        >{{ old('descricao') }}</textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="box-footer d-flex justify-content-end">
                  <a href="{{ route('cargos.cbo.index') }}" class="btn btn-secondary me-2">Voltar</a>
                  <button id="btn-salvar" type="submit" class="btn bg-gradient-success">Salvar</button>
                </div>

              </form>
            </div>
          </div>
        </div>
      </section>

    </div>
  </div>

  {{-- Footer --}}
  @includeIf('partials.footer')
  @includeIf('layouts.partials.footer')
  @includeIf('includes.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

<script>
(function () {
  const input = document.getElementById('cbo-codigo');
  const erro  = document.getElementById('cbo-erro');
  const btn   = document.getElementById('btn-salvar');

  async function check() {
    const codigo = (input.value || '').trim();
    erro.style.display = 'none';
    erro.textContent = '';
    btn.disabled = false;

    if (!codigo) return;

    try {
      const url = new URL("{{ route('cargos.cbo.check') }}", window.location.origin);
      url.searchParams.set('cbo', codigo);

      const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

      if (!res.ok) return; // se der 403 etc, o backend já bloqueia

      const data = await res.json();

      if (data.exists) {
        erro.textContent = data.message || 'Este CBO já existe.';
        erro.style.display = 'block';
        btn.disabled = true;
      }
    } catch (e) {
      console.error(e);
    }
  }

  input.addEventListener('blur', check);
})();
</script>

</body>
</html>
