<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Cargo</th>
        <th>Lotação</th>
        @if(!empty($podeEditar) && $podeEditar)
          <th style="width:120px;">Ações</th>
        @endif
      </tr>
    </thead>

    <tbody>
      @forelse($cargos as $cargo)
        <tr>
          <td>
            <strong>{{ $cargo->titulo }}</strong><br>
            <small>CBO: {{ $cargo->cbo?->cbo ?? '-' }}</small>
          </td>
@php
  $lotacoes = $lotacoesPorCargo[$cargo->id] ?? collect();
@endphp
          <td>
              @if($lotacoes->isEmpty())
  <span class="text-muted">—</span>
@else
  @foreach($lotacoes as $l)
    <div>{{ $l->filial }} > {{ $l->setor }}</div>
  @endforeach
@endif
              </td>

          @if(!empty($podeEditar) && $podeEditar)
            <td>
              <a href="{{ route('cargos.cargos.edit', $cargo->id) }}" class="btn btn-sm btn-primary">
                Editar
              </a>
            </td>
          @endif
        </tr>
      @empty
        <tr>
          <td colspan="{{ (!empty($podeEditar) && $podeEditar) ? 3 : 2 }}" class="text-center">
            Nenhum registro encontrado.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($cargos, 'links'))
  <div class="d-flex justify-content-end mt-3">
    {{ $cargos->links() }}
  </div>
@endif
