<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Nome Grupo</th>
        <th>Usuários</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($grupos as $g)
        <tr>
          <td>{{ $g->nome_grupo }}</td>
          <td>{{ (int) ($g->usuarios_count ?? 0) }}</td>
          <td>
            @if($g->status)
              <span class="badge badge-success">Ativo</span>
            @else
              <span class="badge badge-danger">Inativo</span>
            @endif
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

<div class="mt-3">
  {{ $grupos->links() }}
</div>
