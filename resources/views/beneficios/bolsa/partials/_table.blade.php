{{-- resources/views/beneficios/bolsa/partials/_table.blade.php --}}
<div class="row">
  <div class="col-12">
    <div class="box">
      <div class="box-header with-border">
        <h4 class="box-title">Bolsa de Estudos</h4>
      </div>

      <div class="box-body">
        <div class="table-responsive">
          <table class="table">
            <thead class="bg-primary">
              <tr>
                <th>Nome / Ciclo</th>
                <th>Situação</th>
                <th>Contemplados</th>
                <th>Orçamento</th>
                <th class="text-end">Ações</th>
              </tr>
            </thead>

            <tbody>
              @forelse($processos as $p)
                @php
                  $statusLabel = match ((int)$p->status) {
                    1 => ['Ativo', 'badge badge-success'],
                    2 => ['Encerrado', 'badge badge-dark'],
                    default => ['Rascunho', 'badge badge-secondary'],
                  };

                  $orcamento = (float)($p->orcamento_mensal ?? 0);
                  $orcamentoBRL = 'R$ ' . number_format($orcamento, 2, ',', '.');

                  $cont = (int)($p->contemplados_count ?? 0);
                  $pend = (int)($p->pendentes_count ?? 0);
                @endphp

                <tr>
                  <td>
                    <div class="fw-600">{{ $p->edital ?: 'Bolsa de Estudos' }}</div>
                    <div class="text-muted">{{ $p->ciclo }}</div>
                  </td>

                  <td><span class="{{ $statusLabel[1] }}">{{ $statusLabel[0] }}</span></td>

                  <td>
                    {{ $cont }}
                    @if($pend > 0)
                      <span class="ms-1 badge badge-warning">+{{ $pend }} pendente(s)</span>
                    @endif
                  </td>

                  <td>{{ $orcamentoBRL }}</td>

                  <td class="text-end">
                    <div class="d-inline-flex gap-1">

                      @if($pend > 0)
                        <a href="{{ route('beneficios.bolsa.aprovacoes', ['sub' => $sub, 'id' => $p->id]) }}"
                           class="btn btn-warning btn-sm"
                           title="Aprovações pendentes">
                          <i data-feather="users"></i>
                        </a>
                      @endif

                      <a href="{{ route('beneficios.bolsa.edit', ['sub' => $sub, 'id' => $p->id]) }}"
                         class="btn btn-primary btn-sm"
                         title="Editar">
                        <i data-feather="edit"></i>
                      </a>

                      <form method="POST"
                            action="{{ route('beneficios.bolsa.destroy', ['sub' => $sub, 'id' => $p->id]) }}"
                            class="d-inline js-form-delete">
                        @csrf
                        @method('DELETE')

                        <button type="button"
                                class="btn btn-danger btn-sm js-btn-delete"
                                data-title="Confirmar exclusão"
                                data-text="Deseja realmente excluir este registro?"
                                data-confirm="Sim, excluir"
                                data-cancel="Cancelar"
                                title="Excluir">
                          <i data-feather="trash-2"></i>
                        </button>
                      </form>

                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">
                    Nenhum ciclo encontrado.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>

          @if(method_exists($processos, 'links'))
            <div class="mt-3">
              {{ $processos->appends(request()->query())->links() }}
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
