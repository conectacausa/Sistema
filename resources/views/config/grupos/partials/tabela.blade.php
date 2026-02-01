<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Nome Grupo</th>
        <th style="width:140px;">Usuários</th>
        <th style="width:120px;">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($grupos as $g)
        <tr>
          <td>{{ $g->nome_grupo }}</td>
          <td>{{ $g->usuarios_count ?? 0 }}</td>
          <td>
            <a href="{{ route('config.grupos.edit', ['sub' => request()->route('sub'), 'id' => $g->id]) }}"
               class="waves-effect waves-circle btn btn-circle btn-primary btn-xs"
               title="Editar">
              <i data-feather="edit-2"></i>
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

<script>
  // garante que o feather redesenhe ícones quando esta partial for carregada via AJAX
  if (window.feather) window.feather.replace();
</script>
