@extends('layouts.app')

@section('title', 'Transporte')

@section('content_header')
<div class="d-flex align-items-center">
  <div class="me-auto">
    <h4 class="page-title">Transporte</h4>
    <div class="d-inline-block align-items-center">
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="{{ route('dashboard', ['sub' => $sub]) }}">
              <i class="mdi mdi-home-outline"></i>
            </a>
          </li>
          <li class="breadcrumb-item">Benefícios</li>
          <li class="breadcrumb-item active" aria-current="page">Transporte</li>
        </ol>
      </nav>
    </div>
  </div>

  <a href="{{ route('beneficios.transporte.linhas.create', ['sub' => $sub]) }}"
     class="waves-effect waves-light btn mb-5 bg-gradient-success">
    Nova Linha
  </a>
</div>
@endsection

@section('content')
<section class="content">

  <!-- Filtros -->
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Filtros</h4>
        </div>
        <div class="box-body">
          <form id="formFiltros" method="GET" action="{{ route('beneficios.transporte.linhas.index', ['sub' => $sub]) }}">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="form-label">Nome Linha, Veículo ou Motorista</label>
                  <input type="text"
                         class="form-control"
                         name="q"
                         id="filtro_q"
                         value="{{ $q ?? '' }}"
                         placeholder="Linha, Veículo ou Motorista">
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Tipo</label>
                  <select class="form-select" name="tipo" id="filtro_tipo">
                    <option value="">Todos</option>
                    <option value="fretada" {{ ($tipo ?? '') === 'fretada' ? 'selected' : '' }}>Fretada</option>
                    <option value="publica" {{ ($tipo ?? '') === 'publica' ? 'selected' : '' }}>Pública</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group">
                  <label class="form-label">Filial</label>
                  <select class="form-select" name="filial_id" id="filtro_filial">
                    <option value="0">Todas</option>
                    @foreach(($filiais ?? []) as $f)
                      <option value="{{ $f->id }}" {{ (int)($filialId ?? 0) === (int)$f->id ? 'selected' : '' }}>
                        {{ $f->nome_fantasia ?? $f->nome ?? ('Filial #'.$f->id) }}
                      </option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            {{-- sem botão: auto submit --}}
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabela Linhas -->
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-header with-border">
          <h4 class="box-title">Linhas</h4>
        </div>

        <div class="box-body">
          <div class="table-responsive">
            <table class="table">
              <thead class="bg-primary">
                <tr>
                  <th>Nome</th>
                  <th>Capacidade</th>
                  <th>Disponibilidade</th>
                  <th class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                @forelse($linhas as $l)
                  @php
                    $cap = (int) ($l->capacidade ?? 0);
                    $vinc = (int) ($l->vinculados_ativos ?? 0);
                    $disp = $cap - $vinc;
                    if ($disp < 0) $disp = 0;
                  @endphp
                  <tr>
                    <td>
                      <div class="fw-600">{{ $l->nome }}</div>
                      <div class="text-muted small">
                        {{ ($l->tipo_linha ?? '-') === 'fretada' ? 'Fretada' : (($l->tipo_linha ?? '-') === 'publica' ? 'Pública' : '-') }}
                        • {{ $l->motorista_nome ?? 'Sem motorista' }}
                        • {{ trim(($l->veiculo_modelo ?? '').' '.($l->veiculo_placa ?? '')) ?: 'Sem veículo' }}
                      </div>
                    </td>
                    <td>{{ $cap }}</td>
                    <td>
                      <span class="{{ $disp === 0 ? 'text-danger fw-600' : 'text-success fw-600' }}">{{ $disp }}</span>
                      <div class="text-muted small">Vinculados: {{ $vinc }}</div>
                    </td>
                    <td class="text-end">
                      <a href="{{ route('beneficios.transporte.linhas.operacao', ['sub' => $sub, 'id' => $l->id]) }}"
                         class="btn btn-sm btn-primary">
                        Operação
                      </a>

                      <a href="{{ route('beneficios.transporte.linhas.edit', ['sub' => $sub, 'id' => $l->id]) }}"
                         class="btn btn-sm btn-info">
                        <i data-feather="edit"></i>
                      </a>

                      <form method="POST"
                            action="{{ route('beneficios.transporte.linhas.destroy', ['sub' => $sub, 'id' => $l->id]) }}"
                            class="d-inline js-form-delete">
                        @csrf
                        @method('DELETE')

                        <button type="button"
                                class="btn btn-danger btn-sm js-btn-delete"
                                data-title="Confirmar exclusão"
                                data-text="Deseja realmente excluir este registro?"
                                data-confirm="Sim, excluir"
                                data-cancel="Cancelar">
                          <i data-feather="trash-2"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                      Nenhuma linha encontrada com os filtros atuais.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end mt-3">
            {!! $linhas->links() !!}
          </div>

        </div>
      </div>
    </div>
  </div>

</section>
@endsection

@push('scripts')
<script>
(function () {
  if (window.feather) feather.replace();

  function toast(type, msg) {
    if (window.toastr) {
      toastr.options = { closeButton:true, progressBar:true, timeOut:3500 };
      toastr[type || 'info'](msg);
    }
  }

  @if(session('success'))
    toast('success', @json(session('success')));
  @endif
  @if(session('error'))
    toast('error', @json(session('error')));
  @endif

  const form = document.getElementById('formFiltros');
  if (!form) return;

  const q = document.getElementById('filtro_q');
  const tipo = document.getElementById('filtro_tipo');
  const filial = document.getElementById('filtro_filial');

  let t = null;
  const submitDebounced = () => {
    clearTimeout(t);
    t = setTimeout(() => form.submit(), 350);
  };

  if (q) q.addEventListener('keyup', submitDebounced);
  if (tipo) tipo.addEventListener('change', submitDebounced);
  if (filial) filial.addEventListener('change', submitDebounced);
})();
</script>
@endpush
