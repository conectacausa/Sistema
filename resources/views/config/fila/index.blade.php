@extends('layouts.app')

@section('title', 'Fila de Mensagens')

@section('content')
<section class="content">
  <div class="row">
    <div class="col-12">

      {{-- BOX: FILTROS (separado, sem botões) --}}
      <div class="box">
        <div class="box-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h3 class="m-0">Fila de Mensagens</h3>
          </div>

          <form id="formFiltros" method="GET" action="{{ route('config.fila.index', ['sub' => $sub]) }}">
            <div class="row g-2">
              <div class="col-12 col-lg-4">
                <input type="text"
                       name="q"
                       id="filtro-q"
                       class="form-control"
                       placeholder="Buscar (destinatário, assunto, mensagem...)"
                       value="{{ $q }}">
              </div>

              <div class="col-6 col-lg-2">
                <select name="canal" class="form-select filtro-auto">
                  <option value="">Canal (todos)</option>
                  <option value="whatsapp" @selected($canal==='whatsapp')>WhatsApp</option>
                  <option value="email" @selected($canal==='email')>E-mail</option>
                  <option value="push" @selected($canal==='push')>Push</option>
                </select>
              </div>

              <div class="col-6 col-lg-2">
                <select name="status" class="form-select filtro-auto">
                  <option value="">Status (todos)</option>
                  <option value="queued" @selected($status==='queued')>Em fila</option>
                  <option value="processing" @selected($status==='processing')>Processando</option>
                  <option value="sent" @selected($status==='sent')>Enviado</option>
                  <option value="failed" @selected($status==='failed')>Falhou</option>
                  <option value="canceled" @selected($status==='canceled')>Cancelado</option>
                </select>
              </div>

              <div class="col-6 col-lg-2">
                <input type="number"
                       name="prioridade"
                       class="form-control filtro-auto"
                       placeholder="Prioridade"
                       value="{{ $prioridade }}">
              </div>

              <div class="col-6 col-lg-1">
                <input type="date"
                       name="dt_ini"
                       class="form-control filtro-auto"
                       value="{{ $dtIni }}">
              </div>

              <div class="col-6 col-lg-1">
                <input type="date"
                       name="dt_fim"
                       class="form-control filtro-auto"
                       value="{{ $dtFim }}">
              </div>
            </div>

            {{-- sem botões por padrão --}}
          </form>
        </div>
      </div>

      {{-- BOX: TABELA --}}
      <div class="box">
        <div class="box-body">

          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
              <thead>
                <tr>
                  <th style="width: 70px;">ID</th>
                  <th style="width: 120px;">Canal</th>
                  <th>Destinatário</th>
                  <th>Assunto</th>
                  <th style="width: 110px;">Status</th>
                  <th style="width: 110px;">Prioridade</th>
                  <th style="width: 130px;">Tentativas</th>
                  <th style="width: 170px;">Disponível</th>
                  <th style="width: 170px;">Criado</th>
                  <th style="width: 120px;" class="text-end">Ações</th>
                </tr>
              </thead>

              <tbody>
                @forelse($itens as $row)
                  <tr>
                    <td>#{{ $row->id }}</td>
                    <td>{{ strtoupper($row->canal) }}</td>
                    <td>
                      <div class="fw-600">{{ $row->destinatario ?? '-' }}</div>
                      @if(!empty($row->destinatario_nome))
                        <div class="text-muted">{{ $row->destinatario_nome }}</div>
                      @endif
                    </td>
                    <td class="text-truncate" style="max-width: 320px;">
                      {{ $row->assunto ?? '-' }}
                    </td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->prioridade }}</td>
                    <td>{{ $row->attempts }}/{{ $row->max_attempts }}</td>
                    <td>
                      @if(!empty($row->available_at))
                        {{ \Carbon\Carbon::parse($row->available_at)->format('d/m/Y H:i') }}
                      @else
                        -
                      @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                      @if(in_array($row->status, ['queued','failed','processing']))
                        <form method="POST"
                              action="{{ route('config.fila.cancelar', ['sub' => $sub, 'id' => $row->id]) }}"
                              class="d-inline js-form-delete">
                          @csrf

                          <button type="button"
                                  class="btn btn-danger btn-sm js-btn-delete"
                                  data-title="Confirmar exclusão"
                                  data-text="Deseja realmente cancelar esta mensagem da fila?"
                                  data-confirm="Sim, cancelar"
                                  data-cancel="Cancelar">
                            <i data-feather="trash-2"></i>
                          </button>
                        </form>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="10" class="text-center py-4">
                      Nenhum registro encontrado.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @if(method_exists($itens, 'links'))
            <div class="mt-3 d-flex justify-content-end">
              {!! $itens->links() !!}
            </div>
          @endif

        </div>
      </div>

    </div>
  </div>
</section>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Feather
    if (window.feather) feather.replace();

    const form = document.getElementById('formFiltros');
    if (!form) return;

    // Auto-submit (select/date/number etc)
    document.querySelectorAll('#formFiltros .filtro-auto').forEach((el) => {
      el.addEventListener('change', () => form.submit());
    });

    // Texto com debounce
    const q = document.getElementById('filtro-q');
    let t = null;

    if (q) {
      q.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => form.submit(), 500);
      });
    }
  });
</script>
@endpush
@endsection

