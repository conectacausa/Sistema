<div class="table-responsive">
  <table class="table" id="docs_table">
    <thead class="bg-primary">
      <tr>
        <th style="min-width:220px;">Documento</th>
        <th>Tipo</th>
        <th>Solicitante</th>
        <th>Expira em</th>
        <th>Status</th>
        <th>Enviado em</th>
      </tr>
    </thead>
    <tbody>
      @forelse($documentos as $d)
        @php
          $tipo = match((int)$d->tipo) {
            1 => 'Comprovante',
            2 => 'Documento',
            3 => 'Atestado de Matrícula',
            4 => 'Contrato',
            default => '—'
          };

          $exp  = !empty($d->expira_em) ? \Carbon\Carbon::parse($d->expira_em)->format('d/m/Y') : '—';
          $env  = !empty($d->created_at) ? \Carbon\Carbon::parse($d->created_at)->format('d/m/Y H:i') : '—';

          $stLabel = match((int)$d->status) {
            0 => 'Aguardando',
            1 => 'Reprovado',
            2 => 'Aprovado',
            default => (string)$d->status
          };
        @endphp

        <tr>
          <td>{{ $d->titulo }}</td>
          <td>{{ $tipo }}</td>
          <td>{{ $d->colaborador_nome ?? '—' }}</td>
          <td>{{ $exp }}</td>
          <td>{{ $stLabel }}</td>
          <td>{{ $env }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center text-muted py-4">
            Nenhum documento encontrado.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-3">
  {{ $documentos->withQueryString()->links() }}
</div>
