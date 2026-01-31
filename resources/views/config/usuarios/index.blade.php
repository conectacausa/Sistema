<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ config('app.name') }} | Usuários</title>

    <link rel="stylesheet" href="{{ asset('assets/css/vendors_css.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/skin_color.css') }}">

    <style>
        /* Remove hover do botão Novo */
        .btn-nohover,
        .btn-nohover:hover,
        .btn-nohover:focus {
            background: linear-gradient(45deg,#28a745,#20c997)!important;
            color:#fff!important;
            box-shadow:none!important;
            transform:none!important;
        }
    </style>
</head>

<body class="hold-transition light-skin sidebar-mini theme-primary fixed">
<div class="wrapper">

@include('partials.header')
@include('partials.menu')

<div class="content-wrapper">
<div class="container-full">

<div class="content-header d-flex justify-content-between">
    <h4>Usuários</h4>

    @if($podeCadastrar)
        <a href="{{ route('config.usuarios.create') }}"
           class="btn bg-gradient-success btn-nohover">
            Novo Usuário
        </a>
    @endif
</div>

<section class="content">

<form id="filtersForm" method="GET">
<div class="row mb-3">
    <div class="col-md-10">
        <input type="text" name="q" class="form-control"
               placeholder="Nome ou CPF" value="{{ $busca }}">
    </div>
    <div class="col-md-2">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">Todas</option>
            @foreach($situacoes as $st)
                <option value="{{ $st }}" {{ $situacaoSelecionada===$st?'selected':'' }}>
                    {{ ucfirst($st) }}
                </option>
            @endforeach
        </select>
    </div>
</div>
</form>

<div class="box">
<div class="box-body">
<table class="table">
<thead class="bg-primary">
<tr>
    <th>Nome</th>
    <th>CPF</th>
    <th>Grupo</th>
    <th>Situação</th>
    <th width="150">Ações</th>
</tr>
</thead>
<tbody>
@foreach($usuarios as $u)
<tr>
    <td>{{ $u->nome_completo }}</td>
    <td>{{ $u->cpf_formatado }}</td>
    <td>{{ $u->grupo_permissao }}</td>
    <td>
        <span class="badge {{ $u->status==='ativo'?'badge-success':'badge-danger' }}">
            {{ ucfirst($u->status) }}
        </span>
    </td>
    <td>
        @if($podeEditar)
            <a href="{{ route('config.usuarios.edit',$u->id) }}"
               class="btn btn-sm btn-outline-primary">
                <i data-feather="edit"></i>
            </a>

            @if($u->status==='ativo')
            <form method="POST"
                  action="{{ route('config.usuarios.inativar',$u->id) }}"
                  style="display:inline"
                  onsubmit="return confirm('Inativar este usuário?')">
                @csrf
                <button class="btn btn-sm btn-outline-danger">
                    <i data-feather="user-x"></i>
                </button>
            </form>
            @endif
        @endif
    </td>
</tr>
@endforeach
</tbody>
</table>

{{ $usuarios->links() }}
</div>
</div>

</section>
</div>
</div>

@include('partials.footer')
</div>

<script src="{{ asset('assets/js/vendors.min.js') }}"></script>
<script src="{{ asset('assets/icons/feather-icons/feather.min.js') }}"></script>
<script>feather.replace()</script>
</body>
</html>
