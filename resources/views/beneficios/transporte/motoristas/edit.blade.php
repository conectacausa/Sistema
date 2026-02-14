@extends('layouts.app')

@section('title', 'Transporte - Editar Motorista')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">Editar Motorista</h3>
    </div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    @if($errors->any())
      <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('beneficios.transporte.motoristas.update', ['sub'=>$sub,'id'=>$motorista->id]) }}">
      @csrf
      @method('PUT')
      @include('beneficios.transporte.motoristas._form', ['motorista' => $motorista])
      <div class="d-flex justify-content-end mt-10">
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>

  </div></div></div></div>
</section>
@endsection

@push('scripts')
<script> if (window.feather) feather.replace(); </script>
@endpush
