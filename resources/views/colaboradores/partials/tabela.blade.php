@php
  function mask_cpf($cpf) {
    $digits = preg_replace('/\D+/', '', (string) $cpf);
    if (strlen($digits) !== 11) return $cpf;

    return substr($digits, 0, 3) . '.' .
           substr($digits, 3, 3) . '.' .
           substr($digits, 6, 3) . '-' .
           substr($digits, 9, 2);
  }
@endphp

<div class="table-responsive">
  <table class="table table-hover table-striped align-middle mb-0">
    <thead>
      <tr>
        <th>Nome</th>
        <th style="width: 180px;">CPF</th>
        <th style="width: 180px;">Data de Admissão</th>
      </tr>
    </thead>
    <tbody>
      @forelse($colaboradores as $c)
        <tr>
          <td>{{ $c->nome ?? '-' }}</td>
          <td>{{ mask_cpf($c->cpf) ?? '-' }}</td>
          <td>
            @if(!empty($c->data_admissao))
              {{ \Carbon\Carbon::parse($c->data_admissao)->format('d/m/Y') }}
            @else
              -
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="3" class="text-center py-4">
            Nenhum colaborador encontrado.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(method_exists($colaboradores, 'links'))
  <div class="mt-3">
    {!! $colaboradores->links() !!}
  </div>
@endif
