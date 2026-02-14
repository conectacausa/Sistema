@extends('layouts.app')

@section('title', 'Resultados - AVD')

@section('content')
<section class="content">
  <div class="row">
    <div class="col-12">
      <div class="box">
        <div class="box-body">

          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <h3 class="m-0">Resultados</h3>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0 p-0 bg-transparent">
                  <li class="breadcrumb-item"><a href="{{ route('dashboard', ['sub'=>$sub]) }}">Dashboard</a></li>
                  <li class="breadcrumb-item active">Resultados</li>
                </ol>
              </nav>
            </div>
          </div>

          <form class="row g-2 align-items-end mb-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Ciclo</label>
              <select name="ciclo_id" class="form-select" onchange="this.form.submit()">
                <option value="0">Selecione...</option>
                @foreach($ciclos as $c)
                  <option value="{{ $c->id }}" {{ (int)$cicloId === (int)$c->id ? 'selected' : '' }}>
                    #{{ $c->id }} — {{ $c->titulo }} ({{ $c->status }})
                  </option>
                @endforeach
              </select>
            </div>
          </form>

          <div class="table-responsive" style="width:100%;">
            <table class="table table-hover align-middle" style="width:100%;">
              <thead>
                <tr>
                  <th>Colaborador</th>
                  <th>Filial</th>
                  <th>Auto</th>
                  <th>Gestor</th>
                  <th>Pares</th>
                  <th>Final</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @forelse($rows as $r)
                  <tr>
                    <td>{{ $r->colaborador ?? '-' }}</td>
                    <td>{{ $r->filial ?? '-' }}</td>
                    <td>{{ $r->nota_auto ?? '-' }}</td>
                    <td>{{ $r->nota_gestor ?? '-' }}</td>
                    <td>{{ $r->nota_pares ?? '-' }}</td>
                    <td><strong>{{ $r->nota_final ?? '-' }}</strong></td>
                    <td>
                      <span class="badge bg-secondary">{{ ucfirst($r->status ?? 'pendente') }}</span>
                      @if($r->divergente) <span class="badge bg-danger">Divergente</span> @endif
                      @if($r->consenso_necessario) <span class="badge bg-info">Consenso</span> @endif
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="7" class="text-muted py-3">Selecione um ciclo para ver resultados.</td></tr>
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
