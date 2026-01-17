@extends('layouts.app')

@section('title', (config('tenant.empresa')?->nome_fantasia ?? 'ConecttaRH') . ' - Dashboard')

@section('content')
  {{-- Por enquanto: cole aqui o conteúdo do seu dashboard HTML (somente a parte de dentro do <section class="content">) --}}
  <div class="row">
    <div class="col-12">
      <div class="box rounded-4 b-1">
        <div class="box-body">
          <h3>Dashboard</h3>
          <p>Em breve vamos adicionar os cards e gráficos.</p>
        </div>
      </div>
    </div>
  </div>
@endsection
