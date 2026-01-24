<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Filial</th>
        <th>Setor</th>
        <th>Cargo</th>
        <th style="width:140px;" class="text-center">Quadro Atual</th>
        <th style="width:140px;" class="text-center">Quadro Ideal</th>
        <th style="width:140px;" class="text-center">Saldo</th>
        <th style="width:140px;" class="text-center">Vagas</th>
      </tr>
    </thead>

    <tbody>
      @php
        $grandHasData = !empty($groups) && method_exists($groups, 'isNotEmpty')
            ? $groups->isNotEmpty()
            : !empty($groups);
      @endphp

      @if(!$grandHasData)
        <tr>
          <td colspan="7" class="text-center">Nenhum registro encontrado.</td>
        </tr>
      @else

        @foreach($groups as $filialNome => $setores)
          @php
            $totalFilialAtual = 0;
            $totalFilialIdeal = 0;
          @endphp

          @foreach($setores as $setorNome => $linhas)
            @php
              $totalSetorAtual = 0;
              $totalSetorIdeal = 0;
            @endphp

            @foreach($linhas as $r)
              @php
                $ideal = (int) ($r->quadro_ideal ?? 0);
                $atual = (int) ($r->quadro_atual ?? 0);

                $saldo = $ideal - $atual;

                $saldoClass = '';
                if ($atual > $ideal) {
                  $saldoClass = 'text-danger fw-bold';
                }

                $totalSetorAtual += $atual;
                $totalSetorIdeal += $ideal;

                $totalFilialAtual += $atual;
                $totalFilialIdeal += $ideal;
              @endphp

              <tr>
                <td>{{ $r->filial }}</td>
                <td>{{ $r->setor }}</td>
                <td>{{ $r->cargo }}</td>

                <td class="text-center">{{ $atual }}</td>
                <td class="text-center">{{ $ideal }}</td>
                <td class="text-center {{ $saldoClass }}">{{ $saldo }}</td>

                <td class="text-center"></td>
              </tr>
            @endforeach

            {{-- Total do setor --}}
            @php
              $saldoSetor = $totalSetorIdeal - $totalSetorAtual;
              $saldoSetorClass = $totalSetorAtual > $totalSetorIdeal ? 'text-danger fw-bold' : 'fw-bold';
            @endphp
            <tr class="bg-light">
              <td></td>
              <td><strong>Total do setor: {{ $setorNome }}</strong></td>
              <td></td>

              <td class="text-center fw-bold">{{ $totalSetorAtual }}</td>
              <td class="text-center fw-bold">{{ $totalSetorIdeal }}</td>
              <td class="text-center {{ $saldoSetorClass }}">{{ $saldoSetor }}</td>

              <td></td>
            </tr>

          @endforeach

          {{-- Total da filial (texto PRETO e NEGRITO como solicitado) --}}
          @php
            $saldoFilial = $totalFilialIdeal - $totalFilialAtual;
            $saldoFilialClass = $totalFilialAtual > $totalFilialIdeal ? 'text-danger fw-bold' : 'fw-bold';
          @endphp
          <tr class="bg-light">
            <td><strong class="text-dark">Total da filial: {{ $filialNome }}</strong></td>
            <td></td>
            <td></td>

            <td class="text-center fw-bold text-dark">{{ $totalFilialAtual }}</td>
            <td class="text-center fw-bold text-dark">{{ $totalFilialIdeal }}</td>
            <td class="text-center {{ $saldoFilialClass }} text-dark">{{ $saldoFilial }}</td>

            <td></td>
          </tr>

        @endforeach

      @endif
    </tbody>
  </table>
</div>
