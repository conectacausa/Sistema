<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Filial</th>
        <th>Setor</th>
        <th>Cargo</th>
        <th style="width:140px;">Quadro Atual</th>
        <th style="width:140px;">Quadro Ideal</th>
        <th style="width:140px;">Saldo</th>
        <th style="width:140px;">Vagas</th>
      </tr>
    </thead>

    <tbody>
      @php
        $grandHasData = !empty($groups) && method_exists($groups, 'isNotEmpty') ? $groups->isNotEmpty() : !empty($groups);
      @endphp

      @if(!$grandHasData)
        <tr>
          <td colspan="7" class="text-center">Nenhum registro encontrado.</td>
        </tr>
      @else

        @foreach($groups as $filialNome => $setores)
          @php $totalFilialIdeal = 0; @endphp

          @foreach($setores as $setorNome => $linhas)
            @php
              $totalSetorIdeal = 0;
            @endphp

            @foreach($linhas as $r)
              @php
                $ideal = (int) ($r->quadro_ideal ?? 0);
                $totalSetorIdeal += $ideal;
                $totalFilialIdeal += $ideal;

                // placeholders (futuros)
                $atual = null; // em branco por enquanto
                $saldo = null; // em branco por enquanto
                $saldoClass = '';
                if ($atual !== null && $ideal !== null) {
                  $saldo = $ideal - $atual;
                  if ($atual > $ideal) $saldoClass = 'text-danger fw-bold';
                }
              @endphp

              <tr>
                <td>{{ $r->filial }}</td>
                <td>{{ $r->setor }}</td>
                <td>{{ $r->cargo }}</td>
                <td></td>
                <td>{{ $ideal }}</td>
                <td class="{{ $saldoClass }}">{{ $saldo === null ? '' : $saldo }}</td>
                <td></td>
              </tr>
            @endforeach

            {{-- Total do setor --}}
            <tr class="bg-light">
              <td></td>
              <td><strong>Total do setor: {{ $setorNome }}</strong></td>
              <td></td>
              <td></td>
              <td><strong>{{ $totalSetorIdeal }}</strong></td>
              <td></td>
              <td></td>
            </tr>

          @endforeach

          {{-- Total da filial --}}
          <tr class="bg-secondary text-white">
            <td><strong>Total da filial: {{ $filialNome }}</strong></td>
            <td></td>
            <td></td>
            <td></td>
            <td><strong>{{ $totalFilialIdeal }}</strong></td>
            <td></td>
            <td></td>
          </tr>
        @endforeach

      @endif
    </tbody>
  </table>
</div>
