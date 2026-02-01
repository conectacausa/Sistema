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
        @php
          $editUrl = route('config.grupos.edit', ['sub' => request()->route('sub'), 'id' => $g->id]);
        @endphp
        <tr>
          <td>{{ $g->nome_grupo }}</td>
          <td>{{ $g->usuarios_count ?? 0 }}</td>
          <td>
            <a href="{{ $editUrl }}"
               class="btn btn-sm btn-outline-primary"
               title="Editar"
               onclick="window.location.href='{{ $editUrl }}'; return false;">
              <i data-feather="edit"></i>
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
  if (window.feather) window.feather.replace();
</script>
