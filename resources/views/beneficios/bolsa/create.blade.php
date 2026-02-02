{{-- resources/views/beneficios/bolsa/create.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name', 'ConecttaRH') }} | Bolsa de Estudos</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
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
    .nav-tabs.tabs-vertical .nav-link.disabled {
      opacity: .55;
      pointer-events: none;
      cursor: not-allowed;
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
            <h4 class="page-title">Bolsa de Estudos</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => $sub]) }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item">
                    <a href="{{ route('beneficios.bolsa.index', ['sub' => $sub]) }}">Bolsa de Estudos</a>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page">Novo Processo</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <section class="content">

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

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Cadastro do Processo</h4>
              </div>

              <div class="box-body">

                <form method="POST" action="{{ route('beneficios.bolsa.store', ['sub' => $sub]) }}">
                  @csrf

                  <div class="vtabs">
                    <ul class="nav nav-tabs tabs-vertical" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-processo" role="tab">
                          <span><i data-feather="lock" class="me-10"></i>Processo</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link disabled" href="javascript:void(0)">
                          <span><i data-feather="home" class="me-10"></i>Unidades</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link disabled" href="javascript:void(0)">
                          <span><i data-feather="user" class="me-10"></i>Solicitantes</span>
                        </a>
                      </li>
                    </ul>

                    <div class="tab-content">
                      <div class="tab-pane active" id="tab-processo" role="tabpanel">
                        <div class="p-15">

                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Ciclo</label>
                                <input type="text"
                                       name="ciclo"
                                       class="form-control @error('ciclo') is-invalid @enderror"
                                       value="{{ old('ciclo') }}"
                                       placeholder="Ex.: 2026/1"
                                       maxlength="60"
                                       required>
                                @error('ciclo')
                                  <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-12">
                              <div class="form-group">
                                <label class="form-label">Edital</label>
                                <textarea name="edital"
                                          class="textarea"
                                          placeholder="Descreva o edital..."
                                          style="width:100%;height:220px;font-size:14px;line-height:18px;border:1px solid #dddddd;padding:10px;">{{ old('edital') }}</textarea>
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Início das Inscrições</label>
                                <input type="datetime-local"
                                       name="inscricoes_inicio_at"
                                       class="form-control"
                                       value="{{ old('inscricoes_inicio_at') }}">
                              </div>
                            </div>
                            <div class="col-md-6 col-12">
                              <div class="form-group">
                                <label class="form-label">Fim das Inscrições</label>
                                <input type="datetime-local"
                                       name="inscricoes_fim_at"
                                       class="form-control"
                                       value="{{ old('inscricoes_fim_at') }}">
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Orçamento</label>
                                <input type="number" step="0.01" min="0"
                                       name="orcamento_total"
                                       class="form-control"
                                       value="{{ old('orcamento_total') }}"
                                       placeholder="0,00">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Duração (meses)</label>
                                <input type="number" min="0"
                                       name="meses_duracao"
                                       class="form-control"
                                       value="{{ old('meses_duracao') }}"
                                       placeholder="0">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Orçamento Mensal</label>
                                <input type="number" step="0.01" min="0"
                                       name="orcamento_mensal"
                                       class="form-control"
                                       value="{{ old('orcamento_mensal') }}"
                                       placeholder="0,00">
                              </div>
                            </div>

                            <div class="col-md-3 col-12">
                              <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                  <option value="0" {{ old('status','0')=='0' ? 'selected' : '' }}>Rascunho</option>
                                  <option value="1" {{ old('status')=='1' ? 'selected' : '' }}>Ativo</option>
                                  <option value="2" {{ old('status')=='2' ? 'selected' : '' }}>Encerrado</option>
                                </select>
                              </div>
                            </div>
                          </div>

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

<script src="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js') }}"></script>

<script>
  if (window.feather) feather.replace();

  // Locale PT-BR inline + init
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined' && $.fn.wysihtml5) {
      $.fn.wysihtml5.locale = $.fn.wysihtml5.locale || {};
      $.fn.wysihtml5.locale["pt-BR"] = {
        font_styles: { normal: "Texto normal", h1: "Título 1", h2: "Título 2", h3: "Título 3" },
        emphasis: { bold: "Negrito", italic: "Itálico", underline: "Sublinhado" },
        lists: { unordered: "Lista", ordered: "Lista numerada", outdent: "Diminuir recuo", indent: "Aumentar recuo" },
        link: { insert: "Inserir link", cancel: "Cancelar" },
        image: { insert: "Inserir imagem", cancel: "Cancelar" },
        html: { edit: "Editar HTML" },
        colours: { black: "Preto", silver: "Prata", gray: "Cinza", maroon: "Marrom", red: "Vermelho", purple: "Roxo", green: "Verde", olive: "Oliva", navy: "Azul marinho", blue: "Azul", orange: "Laranja" }
      };

      $('.textarea').wysihtml5({ locale: "pt-BR" });
    }
  });
</script>

</body>
</html>
