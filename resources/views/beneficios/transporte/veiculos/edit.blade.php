{{-- resources/views/beneficios/transporte/veiculos/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Transporte - Editar Veículo')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">Editar Veículo</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="{{ route('dashboard', ['sub'=>$sub]) }}"><i class="mdi mdi-home-outline"></i></a>
        </li>
        <li class="breadcrumb-item">Benefícios</li>
        <li class="breadcrumb-item"><a href="{{ route('beneficios.transporte.veiculos.index', ['sub'=>$sub]) }}">Transporte</a></li>
        <li class="breadcrumb-item active">Editar</li>
      </ol>
    </div>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    @if($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('beneficios.transporte.veiculos.update', ['sub'=>$sub,'id'=>$veiculo->id]) }}">
      @csrf
      @method('PUT')

      @include('beneficios.transporte.veiculos._form', ['veiculo' => $veiculo])

      <div class="d-flex justify-content-end mt-10">
        <button class="btn btn-primary" type="submit">Salvar</button>
      </div>
    </form>

  </div></div></div></div>
</section>
@endsection

@push('scripts')
<script>
  if (window.feather) feather.replace();
</script>
@endpush
