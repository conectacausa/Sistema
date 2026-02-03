<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Nome / Ciclo</th>
        <th>Situação</th>
        <th>Contemplados</th>
        <th>Pendentes</th>
        <th>Orçamento</th>
        <th class="text-end">Ações</th>
      </tr>
    </thead>

    <tbody>
      @forelse($processos as $p)
        @php
          $status = (int)($p->status ?? 0);
          $st = match ($status) {
            1 => ['Ativo', 'badge badge-success'],
            2 => ['Encerrado', 'badge badge-secondary'],
            default => ['Rascunho', 'badge badge-warning'],
          };

          $orc = (float)($p->orcamento_total ?? (($p->orcamento_mensal ?? 0) * ($p->meses_duracao ?? 0)));
          $orcBR = 'R$ ' . number_format($orc, 2, ',', '.');

          $pend = (int)($p->pendentes_count ?? 0);
          $cont = (int)($p->contemplados_count ?? 0);
        @endphp

        <tr>
          <td>{{ $p->ciclo }}</td>
          <td><span class="{{ $st[1] }}">{{ $st[0] }}</span></td>
          <td>{{ $cont }}</td>
          <td>{{ $pend }}</td>
          <td>{{ $orcBR }}</td>

          <td class="text-end">
            @if($pend > 0)
              <a class="btn btn-warning btn-sm"
                 href="{{ route('beneficios.bolsa.aprovacoes.index', ['sub'=>$sub, 'processo_id'=>$p->id]) }}"
                 title="Aprovar solicitações pendentes">
                Aprovar
              </a>
            @endif

            <a class="btn btn-info btn-sm"
               href="{{ route('beneficios.bolsa.documentos.index', ['sub'=>$sub, 'processo_id'=>$p->id]) }}"
               title="Fila de documentos e pagamento">
              Documentos
            </a>

            <a class="btn btn-primary btn-sm"
               href="{{ route('beneficios.bolsa.edit', ['sub'=>$sub, 'id'=>$p->id]) }}"
               title="Editar processo">
              <i data-feather="edit"></i>
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center text-muted py-4">Nenhum processo encontrado.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($processos, 'links'))
  <div class="mt-3">
    {{ $processos->withQueryString()->links() }}
  </div>
@endif

<script>
  if (window.feather) feather.replace();
</script>
