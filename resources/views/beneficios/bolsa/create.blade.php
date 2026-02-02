{{-- resources/views/beneficios/bolsa/create.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name') }} | Bolsa de Estudos</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">
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
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
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
                          <i data-feather="lock"></i> Processo
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-unidades" role="tab">
                          <i data-feather="users"></i> Unidades
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-solicitantes" role="tab">
                          <i data-feather="user"></i> Solicitantes
                        </a>
                      </li>
                    </ul>

                    <div class="tab-content">

                      {{-- TAB 1 - PROCESSO --}}
                      <div class="tab-pane active" id="tab-processo" role="tabpanel">
                        <div class="row">
                          <div class="col-12">

                            {{-- Linha 1 - Ciclo --}}
                            <div class="row">
                              <div class="col-md-12">
                                <div class="form-group">
                                  <label class="form-label">Ciclo</label>
                                  <input type="text"
                                         name="ciclo"
                                         class="form-control"
                                         value="{{ old('ciclo') }}"
                                         placeholder="Ex.: 2026/1"
                                         required>
                                </div>
                              </div>
                            </div>

                            {{-- Linha 2 - Edital (WYSIHTML5) --}}
                            <div class="row">
                              <div class="col-md-12">
                                <div class="form-group">
                                  <label class="form-label">Edital</label>
                                  <textarea name="edital"
                                            class="textarea"
                                            placeholder="Descreva o edital..."
                                            style="width: 100%; height: 220px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;">{{ old('edital') }}</textarea>
                                </div>
                              </div>
                            </div>

                            {{-- Linha 3 - Datas (início/fim inscrições) --}}
                            <div class="row">
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label class="form-label">Início das Inscrições</label>
                                  <input type="datetime-local"
                                         name="inscricoes_inicio_at"
                                         class="form-control"
                                         value="{{ old('inscricoes_inicio_at') }}">
                                </div>
                              </div>
                              <div class="col-md-6">
                                <div class="form-group">
                                  <label class="form-label">Fim das Inscrições</label>
                                  <input type="datetime-local"
                                         name="inscricoes_fim_at"
                                         class="form-control"
                                         value="{{ old('inscricoes_fim_at') }}">
                                </div>
                              </div>
                            </div>

                            {{-- Linha 4 - Orçamento / Duração / Orçamento Mensal / Status --}}
                            <div class="row">
                              <div class="col-md-3">
                                <div class="form-group">
                                  <label class="form-label">Orçamento</label>
                                  <input type="number"
                                         step="0.01"
                                         min="0"
                                         name="orcamento_total"
                                         class="form-control"
                                         value="{{ old('orcamento_total') }}"
                                         placeholder="0,00">
                                  <small class="text-muted">Opcional (pode usar só mensal)</small>
                                </div>
                              </div>

                              <div class="col-md-3">
                                <div class="form-group">
                                  <label class="form-label">Duração (meses)</label>
                                  <input type="number"
                                         min="0"
                                         name="meses_duracao"
                                         class="form-control"
                                         value="{{ old('meses_duracao') }}"
                                         placeholder="0">
                                </div>
                              </div>

                              <div class="col-md-3">
                                <div class="form-group">
                                  <label class="form-label">Orçamento Mensal</label>
                                  <input type="number"
                                         step="0.01"
                                         min="0"
                                         name="orcamento_mensal"
                                         class="form-control"
                                         value="{{ old('orcamento_mensal') }}"
                                         placeholder="0,00">
                                </div>
                              </div>

                              <div class="col-md-3">
                                <div class="form-group">
                                  <label class="form-label">Status</label>
                                  <select name="status" class="form-select form-control">
                                    <option value="0" @selected(old('status','0')==='0')>Rascunho</option>
                                    <option value="1" @selected(old('status')==='1')>Ativo</option>
                                    <option value="2" @selected(old('status')==='2')>Encerrado</option>
                                  </select>
                                </div>
                              </div>
                            </div>

                          </div>
                        </div>
                      </div>

                      {{-- TAB 2 - UNIDADES --}}
                      <div class="tab-pane" id="tab-unidades" role="tabpanel">
                        <div class="alert alert-info mb-0">
                          Salve o processo primeiro para vincular unidades.
                        </div>
                      </div>

                      {{-- TAB 3 - SOLICITANTES --}}
                      <div class="tab-pane" id="tab-solicitantes" role="tabpanel">
                        <div class="alert alert-info mb-0">
                          Salve o processo primeiro para adicionar solicitantes.
                        </div>
                      </div>

                    </div>
                  </div>

                  {{-- Botão salvar FORA das abas --}}
                  <div class="mt-20 text-end">
                    <button type="submit" class="btn btn-success">
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

  @include('partials.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/chat-popup.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>

<script src="{{ asset('assets/js/demo.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>

{{-- WYSIHTML5 (referência do seu exemplo) --}}
<script src="{{ asset('assets/vendor_plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js') }}"></script>

<script>
  if (window.feather) feather.replace();

  document.addEventListener('DOMContentLoaded', function () {
    // Inicializa WYSIHTML5 no edital
    if (typeof $ !== 'undefined' && $('.textarea').length) {
      $('.textarea').wysihtml5();
    }
  });
</script>

</body>
</html>
