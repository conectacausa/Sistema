@extends('layouts.public')

@section('title', 'Avaliação bloqueada')

@section('content')
<section class="content">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="box">
        <div class="box-body">
          <h3 class="m-0 mb-2">Avaliação indisponível</h3>
          <div class="text-muted">{{ $motivo ?? 'Esta avaliação não pode ser respondida.' }}</div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
