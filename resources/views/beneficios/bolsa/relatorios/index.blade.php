<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

  <title>{{ config('app.name','ConecttaRH') }} | Relatórios Bolsa</title>

  <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

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
            <h4 class="page-title">Relatórios - Bolsa de Estudos</h4>
            <div class="d-inline-block align-items-center">
              <nav>
                <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                    <a href="{{ route('dashboard', ['sub' => $sub]) }}"><i class="mdi mdi-home-outline"></i></a>
                  </li>
                  <li class="breadcrumb-item">Beneficios</li>
                  <li class="breadcrumb-item active" aria-current="page">Relatórios Bolsa</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <section class="content">

        @if(session('success'))
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('success') }}
          </div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            {{ session('error') }}
          </div>
        @endif

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Filtros</h4>
              </div>
              <div class="box-body">
                <form method="GET" action="{{ route('beneficios.bolsa.relatorios.index', ['sub'=>$sub]) }}">
                  <div class="row">
                    <div class="col-md-4 col-12">
                      <div class="form-group">
                        <label class="form-label">Ciclo (Processo)</label>
                        <select name="processo_id" class="form-control">
                          <option value="0">Todos</option>
                          @foreach($processos as $p)
                            <option value="{{ $p->id }}" {{ (int)($f['processo_id'] ?? 0) === (int)$p->id ? 'selected' : '' }}>
                              {{ $p->ciclo }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3 col-12">
                      <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                          <option value=""  {{ ($f['status'] ?? '')==='' ? 'selected':'' }}>Pronto + Pago</option>
                          <option value="2" {{ ($f['status'] ?? '')==='2' ? 'selected':'' }}>Pronto p/ pagamento</option>
                          <option value="3" {{ ($f['status'] ?? '')==='3' ? 'selected':'' }}>Pago</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-2 col-12">
                      <div class="form-group">
                        <label class="form-label">Pago de</label>
                        <input type="date" name="pago_de" class="form-control" value="{{ $f['pago_de'] ?? '' }}">
                      </div>
                    </div>

                    <div class="col-md-2 col-12">
                      <div class="form-group">
                        <label class="form-label">Pago até</label>
                        <input type="date" name="pago_ate" class="form-control" value="{{ $f['pago_ate'] ?? '' }}">
                      </div>
                    </div>

                    <div class="col-md-1 col-12 d-flex align-items-end">
                      <button class="btn btn-primary w-100" type="submit">Filtrar</button>
                    </div>
                  </div>
                </form>

                <div class="d-flex justify-content-end mt-2">
                  <a class="btn bg-gradient-success"
                     href="{{ route('beneficios.bolsa.relatorios.export_pagamentos', array_merge(['sub'=>$sub], $f)) }}">
                    Exportar Excel
                  </a>
                </div>

              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <div class="box">
              <div class="box-header with-border">
                <h4 class="box-title">Pagamentos</h4>
              </div>
              <div class="box-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead class="bg-primary">
                      <tr>
                        <th>Ciclo</th>
                        <th>Competência</th>
                        <th>Status</th>
                        <th>Colaborador</th>
                        <th>Filial</th>
                        <th>Entidade</th>
                        <th>Curso</th>
                        <th>Valor Previsto</th>
                        <th>Pago em</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($rows as $r)
                        <tr>
                          <td>{{ $r->ciclo }}</td>
                          <td>{{ !empty($r->competencia) ? \Carbon\Carbon::parse($r->competencia)->format('m/Y') : '—' }}</td>
                          <td>{{ ((int)$r->comp_status===2) ? 'Pronto p/ pagamento' : 'Pago' }}</td>
                          <td>{{ $r->colaborador_nome ?? '—' }}</td>
                          <td>{{ $r->filial_nome ?? '—' }}</td>
                          <td>{{ $r->entidade_nome ?? '—' }}</td>
                          <td>{{ $r->curso_nome ?? '—' }}</td>
                          <td>R$ {{ number_format((float)($r->valor_previsto ?? 0), 2, ',', '.') }}</td>
                          <td>{{ !empty($r->pago_at) ? \Carbon\Carbon::parse($r->pago_at)->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                      @empty
                        <tr>
                          <td colspan="9" class="text-center text-muted py-4">Sem dados para o filtro selecionado.</td>
                        </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>

                <div class="mt-3">
                  {{ $rows->withQueryString()->links() }}
                </div>
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
  if (window.feather) feather.replace();
</script>

</body>
</html>
