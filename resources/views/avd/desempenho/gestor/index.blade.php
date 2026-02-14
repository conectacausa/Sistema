@extends('layouts.app')

@section('title', 'Minhas Avaliações')

@section('content')
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">

          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h3 class="m-0">Minhas Avaliações</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 p-0 bg-transparent">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard', ['sub'=>$sub]) }}">Dashboard</a></li>
                  <li class="breadcrumb-item active">Pendências</li>
                </ol>
              </nav>
            </div>
          </div>

          <div class="table-responsive" style="width:100%;">
            <table class="table table-hover align-middle" style="width:100%;">
              <thead>
                <tr>
                  <th>Ciclo</th>
                  <th>Colaborador</th>
                  <th>Tipo</th>
                  <th>Status</th>
                  <th class="text-end">Ação</th>
                </tr>
              </thead>
              <tbody>
                @forelse($rows as $r)
                  <tr>
                    <td>{{ $r->titulo }}</td>
                    <td>{{ $r->colaborador_nome ?? '-' }}</td>
                    <td>{{ ucfirst($r->tipo) }}</td>
                    <td><span class="badge bg-warning">{{ ucfirst($r->status) }}</span></td>
                    <td class="text-end">
                      <a href="{{ route('avd.public.show', ['sub'=>$sub, 'token'=>$r->token]) }}" class="btn btn-primary btn-sm">
                        Avaliar
                      </a>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="5" class="text-muted py-3">Nenhuma avaliação pendente.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
@endsection
