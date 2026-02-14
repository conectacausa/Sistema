@extends('layouts.public')

@section('title', 'Avaliação')

@section('content')
<section class="content">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-10">
      <div class="box">
        <div class="box-body">

          <h3 class="m-0 mb-1">{{ $avaliacao->ciclo_titulo }}</h3>
          <div class="text-muted mb-3">Preencha a avaliação e envie no final.</div>

          <form method="POST" action="{{ route('avd.public.submit', ['sub'=>$sub, 'token'=>$token]) }}">
            @csrf

            @foreach($pilares as $p)
              <div class="mb-4">
                <h5 class="mb-2">{{ $p->nome }}</h5>

                @php
                  $pergs = $perguntas->where('pilar_id', $p->id);
                @endphp

                @foreach($pergs as $pg)
                  <div class="border rounded p-3 mb-2">
                    <div class="fw-semibold mb-2">{{ $pg->texto }}</div>

                    @php
                      $tipo = $pg->tipo_resposta ?? '1_5';
                      $max = $tipo === '1_10' ? 10 : 5;
                    @endphp

                    <div class="row g-2 align-items-end">
                      <div class="col-12 col-md-3">
                        <label class="form-label">Resposta</label>
                        <select class="form-select" name="respostas[{{ $pg->id }}][valor]" required>
                          <option value="">Selecione</option>
                          @for($i=1; $i<=$max; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                          @endfor
                        </select>
                      </div>

                      @if((int)$pg->exige_justificativa === 1)
                        <div class="col-12 col-md-9">
                          <label class="form-label">Justificativa (obrigatória)</label>
                          <input type="text" class="form-control" name="respostas[{{ $pg->id }}][justificativa]" required>
                        </div>
                      @else
                        <div class="col-12 col-md-9">
                          <label class="form-label">Justificativa (opcional)</label>
                          <input type="text" class="form-control" name="respostas[{{ $pg->id }}][justificativa]">
                        </div>
                      @endif

                      @if((int)$pg->permite_comentario === 1)
                        <div class="col-12">
                          <label class="form-label">Comentário (opcional)</label>
                          <textarea class="form-control" name="respostas[{{ $pg->id }}][comentario]" rows="2"></textarea>
                        </div>
                      @endif
                    </div>
                  </div>
                @endforeach

                @if($pergs->count() === 0)
                  <div class="text-muted">Nenhuma pergunta neste pilar.</div>
                @endif
              </div>
            @endforeach

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">Enviar avaliação</button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection
