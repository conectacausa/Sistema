@extends('layouts.app')

@section('title', 'Transporte - Veículos')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">Veículos</h3>
    </div>
    <a href="{{ route('beneficios.transporte.veiculos.create', ['sub'=>$sub]) }}" class="btn btn-success">Novo Veículo</a>
  </div>
</div>

<section class="content">
  <div class="row"><div class="col-12">

    <div class="box">
      <div class="box-header with-border"><h4 class="box-title">Veículos</h4></div>
      <div class="box-body">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="bg-primary">
              <tr>
                <th>Placa</th>
                <th>Modelo</th>
                <th>Tipo</th>
                <th>Capacidade</th>
                <th>Inspeção (meses)</th>
                <th>Status</th>
                <th style="width:120px;">Ações</th>
              </tr>
            </thead>
            <tbody>
              @forelse($veiculos as $v)
                <tr>
                  <td>{{ $v->placa ?? '-' }}</td>
                  <td>{{ $v->modelo ?? '-' }}</td>
                  <td>{{ $v->tipo }}</td>
                  <td>{{ $v->capacidade_passageiros ?? '-' }}</td>
                  <td>{{ $v->inspecao_cada_meses }}</td>
                  <td>{{ $v->status }}</td>
                  <td class="text-nowrap">
                    <a class="btn btn-primary btn-sm" href="{{ route('beneficios.transporte.veiculos.edit', ['sub'=>$sub,'id'=>$v->id]) }}"><i data-feather="edit"></i></a>
                    <form method="POST" action="{{ route('beneficios.transporte.veiculos.destroy', ['sub'=>$sub,'id'=>$v->id]) }}" class="d-inline js-form-delete">
                      @csrf @method('DELETE')
                      <button type="button" class="btn btn-danger btn-sm js-btn-delete"
                              data-title="Confirmar exclusão" data-text="Deseja realmente excluir este registro?"
                              data-confirm="Sim, excluir" data-cancel="Cancelar">
                        <i data-feather="trash-2"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center text-muted">Nenhum veículo.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="mt-15">{{ $veiculos->links() }}</div>
      </div>
    </div>

  </div></div>
</section>
@endsection

@push('scripts')
<script> if (window.feather) feather.replace(); </script>
@endpush
