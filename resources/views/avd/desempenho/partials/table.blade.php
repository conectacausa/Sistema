<div class="table-responsive" style="width:100%;">
  <table class="table table-hover align-middle mb-0" style="width:100%;">
    <thead>
      <tr>
        <th>Título</th>
        <th>Início</th>
        <th>Fim</th>
        <th>Tipo</th>
        <th>Participantes</th>
        <th>Respondentes</th>
        <th>Status</th>
        <th class="text-end">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td>{{ $r->titulo }}</td>
          <td>{{ $r->inicio_em ? \Carbon\Carbon::parse($r->inicio_em)->format('d/m/Y H:i') : '-' }}</td>
          <td>{{ $r->fim_em ? \Carbon\Carbon::parse($r->fim_em)->format('d/m/Y H:i') : '-' }}</td>
          <td>{{ $r->tipo }}°</td>
          <td>{{ (int)($participantes[$r->id] ?? 0) }}</td>
          <td>{{ (int)($respondentes[$r->id] ?? 0) }}</td>
          <td>
            @php
              $badge = 'secondary';
              if ($r->status === 'aguardando') $badge = 'warning';
              if ($r->status === 'iniciada') $badge = 'primary';
              if ($r->status === 'encerrada') $badge = 'dark';
              if ($r->status === 'em_consenso') $badge = 'info';
            @endphp
            <span class="badge bg-{{ $badge }}">{{ ucfirst(str_replace('_',' ', $r->status)) }}</span>
          </td>
          <td class="text-end">
            {{-- Editar (padrão do projeto: ícone sem fundo) --}}
            <a href="{{ route('avd.ciclos.edit', ['sub'=>$sub, 'id'=>$r->id]) }}"
               class="btn btn-link p-0 me-2"
               title="Editar">
              <i data-feather="edit"></i>
            </a>

            {{-- Iniciar / Encerrar --}}
            <form method="POST" action="{{ route('avd.ciclos.iniciar', ['sub'=>$sub, 'id'=>$r->id]) }}" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-link p-0 me-2" title="Iniciar">
                <i data-feather="play"></i>
              </button>
            </form>

            <form method="POST" action="{{ route('avd.ciclos.encerrar', ['sub'=>$sub, 'id'=>$r->id]) }}" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-link p-0 me-2" title="Encerrar">
                <i data-feather="slash"></i>
              </button>
            </form>

            {{-- Excluir (padrão com SweetAlert) --}}
            <form method="POST" action="{{ route('avd.ciclos.destroy', ['sub'=>$sub, 'id'=>$r->id]) }}" class="d-inline js-form-delete">
              @csrf
              @method('DELETE')
              <button type="button"
                      class="btn btn-link p-0 text-danger js-btn-delete"
                      data-title="Confirmar exclusão"
                      data-text="Deseja realmente excluir este registro?"
                      data-confirm="Sim, excluir"
                      data-cancel="Cancelar"
                      title="Excluir">
                <i data-feather="trash-2"></i>
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-muted py-4">Nenhum ciclo encontrado.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<script>
if (window.feather) feather.replace();
</script>
