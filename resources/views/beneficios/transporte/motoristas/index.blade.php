@extends('layouts.app')

@section('title', 'Transporte - Motoristas')

@section('content')
<div class="content-header">
  <div class="d-flex align-items-center">
    <div class="me-auto">
      <h3 class="m-0">Motoristas</h3>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard', ['sub'=>$sub]) }}"><i class="mdi mdi-home-outline"></i></a></li>
        <li class="breadcrumb-item">Benefícios</li>
        <li class="breadcrumb-item active">Transporte</li>
      </ol>
    </div>
    <a href="{{ route('beneficios.transporte.motoristas.create', ['sub'=>$sub]) }}" class="btn btn-success">Novo Motorista</a>
  </div>
</div>

<section class="content">
  <div class="row">
    <div class="col-12">

      <div class="box">
        <div class="box-header with-border"><h4 class="box-title">Filtros</h4></div>
        <div class="box-body">
          <form method="GET" action="{{ route('beneficios.transporte.motoristas.index', ['sub'=>$sub]) }}">
            <div class="row">
              <div class="col-md-10">
                <label class="form-label">Buscar</label>
                <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Nome do motorista">
              </div>
              <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="submit">Filtrar</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="box">
        <div class="box-header with-border"><h4 class="box-title">Motoristas</h4></div>
        <div class="box-body">
          @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="bg-primary">
                <tr>
                  <th>Nome</th>
                  <th>CPF</th>
                  <th>CNH</th>
                  <th>Validade</th>
                  <th>Status</th>
                  <th style="width:120px;">Ações</th>
                </tr>
              </thead>
              <tbody>
                @forelse($motoristas as $m)
                  <tr>
                    <td>{{ $m->nome }}</td>
                    <td>{{ $m->cpf ?? '-' }}</td>
                    <td>{{ $m->cnh_categoria ? ($m->cnh_categoria.' - '.$m->cnh_numero) : ($m->cnh_numero ?? '-') }}</td>
                    <td>{{ $m->cnh_validade ?? '-' }}</td>
                    <td>{{ $m->status }}</td>
                    <td class="text-nowrap">
                      <a class="btn btn-primary btn-sm" href="{{ route('beneficios.transporte.motoristas.edit', ['sub'=>$sub,'id'=>$m->id]) }}">
                        <i data-feather="edit"></i>
                      </a>

                      <form method="POST" action="{{ route('beneficios.transporte.motoristas.destroy', ['sub'=>$sub,'id'=>$m->id]) }}" class="d-inline js-form-delete">
                        @csrf
                        @method('DELETE')
                        <button type="button"
                                class="btn btn-danger btn-sm js-btn-delete"
                                data-title="Confirmar exclusão"
                                data-text="Deseja realmente excluir este registro?"
                                data-confirm="Sim, excluir"
                                data-cancel="Cancelar">
                          <i data-feather="trash-2"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-center text-muted">Nenhum motorista.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="mt-15">{{ $motoristas->links() }}</div>
        </div>
      </div>

    </div>
  </div>
</section>
@endsection

@push('scripts')
<script> if (window.feather) feather.replace(); </script>
@endpush
