<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>CBO</th>
        <th>Título</th>
      </tr>
    </thead>
    <tbody>
      @forelse($cbos as $cbo)
        <tr>
          <td>{{ $cbo->cbo }}</td>
          <td>{{ $cbo->titulo }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="2" class="text-center">Nenhum registro encontrado.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($cbos, 'links'))
  <div class="d-flex justify-content-end mt-3">
    {{ $cbos->links() }}
  </div>
@endif
