@php
  $totalAtual = 0;
  $totalIdeal = 0;

  if (!empty($groups)) {
    foreach ($groups as $filialNome => $setores) {
      foreach ($setores as $setorNome => $linhas) {
        foreach ($linhas as $r) {
          $totalAtual += (int) ($r->quadro_atual ?? 0);
          $totalIdeal += (int) ($r->quadro_ideal ?? 0);
        }
      }
    }
  }

  $saldoTotal = $totalIdeal - $totalAtual;

  // Por enquanto sem regra -> 0
  $vagasAbertas = 0;

  $pctAtual = $totalIdeal > 0 ? (int) round(($totalAtual / $totalIdeal) * 100) : 0;
  $pctAtual = max(0, min(100, $pctAtual));

  $pctIdeal = 100;

  $pctSaldo = $totalIdeal > 0 ? (int) round((abs($saldoTotal) / $totalIdeal) * 100) : 0;
  $pctSaldo = max(0, min(100, $pctSaldo));

  $pctVagas = 0;

  $saldoTextClass = $totalAtual > $totalIdeal ? 'text-danger' : 'text-warning';
  $saldoBarClass  = $totalAtual > $totalIdeal ? 'bg-danger' : 'bg-warning';
@endphp

<div class="row">
  <div class="col-12">
    <div class="box">
      <div class="row g-0 py-2">

        <div class="col-12 col-lg-3">
          <div class="box-body be-1 border-light">
            <div class="flexbox mb-1">
              <span>
                <span class="icon-User fs-40"><span class="path1"></span><span class="path2"></span></span><br>
                Quadro Atual
              </span>
              <span class="text-primary fs-40">{{ $totalAtual }}</span>
            </div>
            <div class="progress progress-xxs mt-10 mb-0">
              <div class="progress-bar" role="progressbar"
                   style="width: {{ $pctAtual }}%; height: 4px;"
                   aria-valuenow="{{ $pctAtual }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-3 hidden-down">
          <div class="box-body be-1 border-light">
            <div class="flexbox mb-1">
              <span>
                <span class="icon-Selected-file fs-40"><span class="path1"></span><span class="path2"></span></span><br>
                Quadro Disponível
              </span>
              <span class="text-info fs-40">{{ $totalIdeal }}</span>
            </div>
            <div class="progress progress-xxs mt-10 mb-0">
              <div class="progress-bar bg-info" role="progressbar"
                   style="width: {{ $pctIdeal }}%; height: 4px;"
                   aria-valuenow="{{ $pctIdeal }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-3 d-none d-lg-block">
          <div class="box-body be-1 border-light">
            <div class="flexbox mb-1">
              <span>
                <span class="icon-Info-circle fs-40"><span class="path1"></span><span class="path2"></span><span class="path3"></span></span><br>
                Saldo
              </span>
              <span class="{{ $saldoTextClass }} fs-40">{{ $saldoTotal }}</span>
            </div>
            <div class="progress progress-xxs mt-10 mb-0">
              <div class="progress-bar {{ $saldoBarClass }}" role="progressbar"
                   style="width: {{ $pctSaldo }}%; height: 4px;"
                   aria-valuenow="{{ $pctSaldo }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>

        <div class="col-12 col-lg-3 d-none d-lg-block">
          <div class="box-body">
            <div class="flexbox mb-1">
              <span>
                <span class="icon-Group-folders fs-40"><span class="path1"></span><span class="path2"></span></span><br>
                Vagas Abertas
              </span>
              <span class="text-danger fs-40">{{ $vagasAbertas }}</span>
            </div>
            <div class="progress progress-xxs mt-10 mb-0">
              <div class="progress-bar bg-danger" role="progressbar"
                   style="width: {{ $pctVagas }}%; height: 4px;"
                   aria-valuenow="{{ $pctVagas }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

@include('cargos.headcount._table', ['groups' => $groups])
