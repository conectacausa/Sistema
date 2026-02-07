@extends('layouts.app')

@section('title', 'Fila de Mensagens')

@section('content')
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">

          <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="m-0">Fila de Mensagens</h3>
          </div>

          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          {{-- Filtros --}}
          <form method="GET" action="{{ route('config.fila.index', ['sub' => $sub]) }}" class="mb-3">
            <div class="row g-2">
              <div class="col-12 col-lg-4">
                <input type="text" name="q" class="form-control" placeholder="Buscar (destinatário, assunto, mensagem...)" value="{{ $q }}">
              </div>

              <div class="col-6 col-lg-2">
                <select name="canal" class="form-select">
                  <option value="">Canal (todos)</option>
                  <option value="whatsapp" @selected($canal==='whatsapp')>WhatsApp</option>
                  <option value="email" @selected($canal==='email')>E-mail</option>
                  <option value="push" @selected($canal==='push')>Push</option>
                </select>
              </div>

              <div class="col-6 col-lg-2">
                <select name="status" class="form-select">
                  <option value="">Status (todos)</option>
                  <option value="queued" @selected($status==='queued')>Em fila</option>
                  <option value="processing" @selected($status==='processing')>Processando</option>
                  <option value="sent" @selected($status==='sent')>Enviado</option>
                  <option value="failed" @selected($status==='failed')>Falhou</option>
                  <option value="canceled" @selected($status==='canceled')>Cancelado</option>
                </select>
              </div>

              <div class="col-6 col-lg-2">
                <input type="number" name="prioridade" class="form-control" placeholder="Prioridade" value="{{ $prioridade }}">
              </div>

              <div class="col-6 col-lg-1">
                <input type="date" name="dt_ini" class="form-control" value="{{ $dtIni }}">
              </div>

              <div class="col-6 col-lg-1">
                <input type="date" name="dt_fim" class="form-control" value="{{ $dtFim }}">
              </div>

              <div class="col-12 col-lg-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('config.fila.index', ['sub' => $sub]) }}" class="btn btn-outline-secondary">Limpar</a>
              </div>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th width="70">ID</th>
                  <th width="120">Canal</th>
                  <th>Destinatário</th>
                  <th>Assunto</th>
                  <th width="110">Status</th>
                  <th width="110">Prioridade</th>
                  <th width="130">Tentativas</th>
                  <th width="170">Disponível</th>
                  <th width="170">Criado</th>
                  <th width="120" class="text-end">Ações</th>
                </tr>
              </thead>
              <tbody>
                @forelse($itens as $row)
                  <tr>
                    <td>#{{ $row->id }}</td>
                    <td>{{ strtoupper($row->canal) }}</td>
                    <td>
                      <div class="fw-600">{{ $row->destinatario }}</div>
                      @if($row->destinatario_nome)
                        <div class="text-muted">{{ $row->destinatario_nome }}</div>
                      @endif
                    </td>
                    <td class="text-truncate" style="max-width: 320px;">
                      {{ $row->assunto ?? '-' }}
                    </td>
                    <td>{{ $row->status }}</td>
                    <td>{{ $row->prioridade }}</td>
                    <td>{{ $row->attempts }}/{{ $row->max_attempts }}</td>
                    <td>{{ $row->available_at ? \Carbon\Carbon::parse($row->available_at)->format('d/m/Y H:i') : '-' }}</td>
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
                    <td colspan="10" class="text-center text-muted py-4">Nenhum registro encontrado.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-end">
            {{ $itens->links() }}
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

{{-- Feather + delete-confirm global --}}
@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.feather) feather.replace();
  });
</script>
@endpush
@endsection
