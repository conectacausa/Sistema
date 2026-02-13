{{-- resources/views/avd/ciclos/partials/tabela.blade.php --}}

<div class="table-responsive">
  <table class="table table-hover align-middle">
    <thead class="bg-primary">
      <tr>
        <th>Título</th>
        <th>Data início</th>
        <th>Data fim</th>
        <th>Tipo</th>
        <th class="text-center">Participantes</th>
        <th class="text-center">Respondentes</th>
        <th>Status</th>
        <th style="width:220px;" class="text-end">Ações</th>
      </tr>
    </thead>

    <tbody>
      @forelse($itens as $c)
        @php
          // Fallbacks caso ainda não existam colunas contadoras no select do controller.
          $qtdParticipantes = $c->qtd_participantes ?? null;
          $qtdRespondentes  = $c->qtd_respondentes ?? null;

          // Datas
          $inicio = !empty($c->inicio_em) ? \Carbon\Carbon::parse($c->inicio_em)->format('d/m/Y H:i') : '-';
          $fim    = !empty($c->fim_em) ? \Carbon\Carbon::parse($c->fim_em)->format('d/m/Y H:i') : '-';

          // Tipo
          $tipoLabel = ($c->tipo ?? '180') === '360' ? '360°' : '180°';

          // Status
          $status = $c->status ?? 'aguardando';
          $statusLabel = ucfirst(str_replace('_',' ', $status));

          $badgeClass = match($status) {
            'aguardando' => 'badge-light',
            'iniciada' => 'badge-primary',
            'encerrada' => 'badge-dark',
            'em_consenso' => 'badge-warning',
            default => 'badge-secondary',
          };

          $sub = request()->route('sub');
        @endphp

        <tr>
          <td>
            <strong>{{ $c->titulo }}</strong>
          </td>

          <td>{{ $inicio }}</td>
          <td>{{ $fim }}</td>

          <td>{{ $tipoLabel }}</td>

          <td class="text-center">
            @if($qtdParticipantes !== null)
              {{ (int)$qtdParticipantes }}
            @else
              <span class="text-muted">—</span>
            @endif
          </td>

          <td class="text-center">
            @if($qtdRespondentes !== null)
              {{ (int)$qtdRespondentes }}
            @else
              <span class="text-muted">—</span>
            @endif
          </td>

          <td>
            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
          </td>

          <td class="text-end">
            <div class="d-inline-flex gap-1">

              {{-- Editar --}}
              <a href="{{ route('avd.ciclos.edit', ['sub' => $sub, 'id' => $c->id]) }}"
                 class="btn btn-sm btn-outline-primary"
                 title="Editar">
                <i data-feather="edit"></i>
              </a>

              {{-- Excluir (form padrão recomendado) --}}
              <form method="POST"
                    action="{{ route('avd.ciclos.destroy', ['sub' => $sub, 'id' => $c->id]) }}"
                    class="d-inline js-form-delete">
                @csrf
                @method('DELETE')

                <button type="button"
                        class="btn btn-sm btn-outline-danger js-btn-delete"
                        title="Excluir"
                        data-title="Confirmar exclusão"
                        data-text="Deseja realmente excluir este registro?"
                        data-confirm="Sim, excluir"
                        data-cancel="Cancelar">
                  <i data-feather="trash-2"></i>
                </button>
              </form>

              {{-- Iniciar --}}
              <form method="POST"
                    action="{{ route('avd.ciclos.iniciar', ['sub' => $sub, 'id' => $c->id]) }}"
                    class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm btn-outline-success"
                        title="Iniciar"
                        @disabled(($c->status ?? '') === 'iniciada' || ($c->status ?? '') === 'encerrada')>
                  <i data-feather="play"></i>
                </button>
              </form>

              {{-- Encerrar --}}
              <form method="POST"
                    action="{{ route('avd.ciclos.encerrar', ['sub' => $sub, 'id' => $c->id]) }}"
                    class="d-inline">
                @csrf
                <button type="submit"
                        class="btn btn-sm btn-outline-warning"
                        title="Encerrar"
                        @disabled(($c->status ?? '') === 'encerrada')>
                  <i data-feather="slash"></i>
                </button>
              </form>

            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center text-muted py-4">
            Nenhum ciclo encontrado.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($itens, 'links'))
  <div class="d-flex justify-content-end mt-3">
    {!! $itens->links() !!}
  </div>
@endif

<script>
  // garante ícones após render via AJAX
  if (window.feather) feather.replace();
</script>
