<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Nome Grupo</th>
        <th style="width:140px;">Usuários</th>
        <th style="width:180px;">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($grupos as $g)
        <tr>
          <td>{{ $g->nome_grupo }}</td>
          <td>{{ $g->usuarios_count ?? 0 }}</td>
          <td>
            <a class="btn btn-sm btn-primary"
               href="{{ route('config.grupos.edit', ['sub' => request()->route('sub'), 'id' => $g->id]) }}">
              Editar
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" class="text-center">Nenhum grupo encontrado.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="d-flex justify-content-end">
  {!! $grupos->links() !!}
</div>
