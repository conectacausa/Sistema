<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Unidades vinculadas</h4>

  <div class="d-flex gap-2">
    <select id="avd-filial-select" class="form-select" style="min-width:280px;">
      <option value="">Selecione uma unidade...</option>
      @foreach($filiais as $f)
        <option value="{{ $f->id }}">{{ $f->nome_fantasia ?? $f->razao_social }}</option>
      @endforeach
    </select>

    <button type="button" class="btn btn-success" id="avd-btn-vincular-unidade">
      Vincular unidade
    </button>
  </div>
</div>

<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Nome fantasia</th>
        <th>CNPJ</th>
        <th style="width:120px;">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($unidadesVinculadas as $u)
        <tr data-row="{{ $u->id }}">
          <td>{{ $u->nome_fantasia ?? $u->razao_social }}</td>
          <td>{{ $u->cnpj }}</td>
          <td>
            <button type="button"
              class="btn btn-danger btn-sm avd-btn-desvincular-unidade"
              data-url="{{ route('avd.ciclos.unidades.desvincular', ['sub'=>request()->route('sub'),'id'=>$ciclo->id,'vinculo_id'=>$u->id]) }}">
              <i data-feather="trash-2"></i>
            </button>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="text-center">Nenhuma unidade vinculada.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<script>
(function(){
  const btn = document.getElementById('avd-btn-vincular-unidade');
  const sel = document.getElementById('avd-filial-select');

  function refresh(){
    fetch(`{{ route('avd.ciclos.tab.unidades', ['sub'=>request()->route('sub'),'id'=>$ciclo->id]) }}`, {
      headers: {'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.text()).then(html=>{
      document.getElementById('avd-tab-unidades-wrapper').innerHTML = html;
      if(window.feather) feather.replace();
    });
  }

  btn?.addEventListener('click', function(){
    const filial_id = sel?.value;
    if(!filial_id) return;

    fetch(`{{ route('avd.ciclos.unidades.vincular', ['sub'=>request()->route('sub'),'id'=>$ciclo->id]) }}`, {
      method: 'POST',
      headers: {
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type':'application/json'
      },
      body: JSON.stringify({ filial_id })
    }).then(()=>refresh());
  });

  document.querySelectorAll('.avd-btn-desvincular-unidade').forEach(b=>{
    b.addEventListener('click', function(){
      const url = this.dataset.url;
      fetch(url, {
        method:'DELETE',
        headers:{
          'X-Requested-With':'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      }).then(()=>refresh());
    });
  });

  if(window.feather) feather.replace();
})();
</script>
