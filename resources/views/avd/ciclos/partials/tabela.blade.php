<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Título</th>
        <th>Início</th>
        <th>Fim</th>
        <th>Tipo</th>
        <th>Status</th>
        <th style="width:220px;">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($itens as $c)
        @php
          $editUrl = route('avd.ciclos.edit', ['sub' => request()->route('sub'), 'id' => $c->id]);
        @endphp
        <tr>
          <td>{{ $c->titulo }}</td>
          <td>{{ $c->inicio_em ? \Carbon\Carbon::parse($c->inicio_em)->format('d/m/Y H:i') : '-' }}</td>
          <td>{{ $c->fim_em ? \Carbon\Carbon::parse($c->fim_em)->format('d/m/Y H:i') : '-' }}</td>
          <td>{{ $c->tipo }}°</td>
          <td>{{ ucfirst(str_replace('_',' ', $c->status)) }}</td>
          <td class="d-flex gap-5">
            <a href="{{ $editUrl }}" class="btn btn-sm btn-outline-primary" title="Editar">
              <i data-feather="edit"></i>
            </a>

            <form method="POST"
                  action="{{ route('avd.ciclos.destroy', ['sub'=>request()->route('sub'),'id'=>$c->id]) }}"
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

            <form method="POST" action="{{ route('avd.ciclos.iniciar', ['sub'=>request()->route('sub'),'id'=>$c->id]) }}">
              @csrf
              <button type="submit" class="btn btn-sm btn-success" title="Iniciar">
                ▶️
              </button>
            </form>

            <form method="POST" action="{{ route('avd.ciclos.encerrar', ['sub'=>request()->route('sub'),'id'=>$c->id]) }}">
              @csrf
              <button type="submit" class="btn btn-sm btn-warning" title="Encerrar">
                ⛔
              </button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center">Nenhum ciclo encontrado.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="d-flex justify-content-end">
  {!! $itens->links() !!}
</div>
