@extends('layouts.app')

@section('title', 'Transporte - Inspeções')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto"><h3 class="m-0">Inspeções</h3></div>
    <a href="{{ route('beneficios.transporte.inspecoes.create', ['sub'=>$sub]) }}" class="btn btn-success">Nova Inspeção</a>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12"><div class="box"><div class="box-body">

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="table-responsive">
      <table class="table table-hover">
        <thead class="bg-primary">
          <tr>
            <th>Data</th>
            <th>Veículo</th>
            <th>Linha</th>
            <th>Status</th>
            <th style="width:120px;">Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($inspecoes as $i)
            <tr>
              <td>{{ $i->data_inspecao }}</td>
              <td>{{ $i->veiculo->placa ?? '-' }}</td>
              <td>{{ $i->linha->nome ?? '-' }}</td>
              <td>{{ $i->status }}</td>
              <td>
                <a class="btn btn-info btn-sm" href="{{ route('beneficios.transporte.inspecoes.show', ['sub'=>$sub,'id'=>$i->id]) }}">Ver</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">Nenhuma inspeção.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-15">{{ $inspecoes->links() }}</div>

  </div></div></div></div>
</section>
@endsection

@push('scripts')
<script> if (window.feather) feather.replace(); </script>
@endpush
