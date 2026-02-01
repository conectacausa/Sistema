{{-- FORM CONTINUA O MESMO --}}
<form method="POST"
      action="{{ route('config.grupos.update', ['sub' => request()->route('sub'), 'id' => $grupo->id]) }}">
@csrf
@method('PUT')

<div class="row">

  {{-- MENU LATERAL --}}
  <div class="col-lg-3 col-md-4">
    <div class="list-group">
      <a href="#grupo" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
        <i class="ion-person me-10"></i> Grupo
      </a>
      <a href="#usuarios" class="list-group-item list-group-item-action" data-bs-toggle="tab">
        <i class="ion-home me-10"></i> Usuários
      </a>
      <a href="#permissoes" class="list-group-item list-group-item-action" data-bs-toggle="tab">
        <i class="ion-lock-combination me-10"></i> Permissões
      </a>
    </div>
  </div>

  {{-- CONTEÚDO --}}
  <div class="col-lg-9 col-md-8">
    <div class="tab-content">

      {{-- ================= GRUPO ================= --}}
      <div class="tab-pane fade show active" id="grupo">
        <h3 class="mb-20">Dados do Grupo</h3>

        <div class="form-group">
          <label>Nome do Grupo</label>
          <input type="text" class="form-control"
                 name="nome_grupo"
                 value="{{ old('nome_grupo', $grupo->nome_grupo) }}">
        </div>

        <div class="form-group">
          <label>Observações</label>
          <textarea class="form-control"
                    name="observacoes"
                    rows="3">{{ old('observacoes', $grupo->observacoes) }}</textarea>
        </div>

        <div class="row">
          <div class="col-md-6">
            <label>Status</label>
            <select name="status" class="form-control">
              <option value="1" {{ $grupo->status ? 'selected' : '' }}>Ativo</option>
              <option value="0" {{ !$grupo->status ? 'selected' : '' }}>Inativo</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Vê Salários</label>
            <select name="salarios" class="form-control">
              <option value="1" {{ $grupo->salarios ? 'selected' : '' }}>Sim</option>
              <option value="0" {{ !$grupo->salarios ? 'selected' : '' }}>Não</option>
            </select>
          </div>
        </div>
      </div>

      {{-- ================= USUÁRIOS ================= --}}
      <div class="tab-pane fade" id="usuarios">
        <h3 class="mb-20">Usuários</h3>

        <table class="table">
          <thead class="bg-primary">
            <tr>
              <th>Nome</th>
              <th>Filial / Setor</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            @foreach($usuarios as $u)
              <tr>
                <td>{{ $u->nome_completo }}</td>
                <td>{!! $u->lotacoes_html ?: '-' !!}</td>
                <td>
                  <button class="btn btn-sm btn-outline-danger" disabled>
                    Desvincular
                  </button>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- ================= PERMISSÕES ================= --}}
      <div class="tab-pane fade" id="permissoes">
        <h3 class="mb-20">Permissões</h3>

        @foreach($modulos as $m)
          <h5 class="mt-30">{{ $m->nome }}</h5>

          <table class="table table-striped">
            <thead class="bg-primary">
              <tr>
                <th>Tela</th>
                <th class="text-center">Ler</th>
                <th class="text-center">Cadastrar</th>
                <th class="text-center">Editar</th>
              </tr>
            </thead>
            <tbody>
              @foreach(($telasPorModulo[$m->id] ?? []) as $t)
                @php $p = $permissoesExistentes[$t->id] ?? null; @endphp
                <tr>
                  <td>{{ $t->nome_tela }}</td>

                  @foreach(['ativo','cadastro','editar'] as $acao)
                    <td class="text-center">
                      <input type="checkbox"
                             class="chk-col-primary"
                             name="perm[{{ $t->id }}][{{ $acao }}]"
                             {{ $p?->$acao ? 'checked' : '' }}>
                      <label></label>
                    </td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        @endforeach
      </div>

    </div>
  </div>
</div>

{{-- BOTÃO SALVAR FORA --}}
<div class="d-flex justify-content-end mt-30">
  <button class="btn bg-gradient-success">
    Salvar
  </button>
</div>

</form>
