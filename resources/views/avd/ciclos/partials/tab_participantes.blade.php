<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Participantes</h4>
</div>

<div class="row g-2 mb-3">
  <div class="col-md-6">
    <label class="form-label">Vincular individual</label>
    <select id="avd-colab-select" class="form-select">
      <option value="">Selecione um colaborador...</option>
      @foreach($colaboradores as $c)
        <option value="{{ $c->id }}">{{ $c->nome }} ({{ $c->cpf }})</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">WhatsApp (opcional)</label>
    <input id="avd-colab-whatsapp" class="form-control" placeholder="55...">
  </div>
  <div class="col-md-3 d-flex align-items-end">
    <button type="button" class="btn btn-success w-100" id="avd-btn-vincular-colab">
      Vincular
    </button>
  </div>
</div>

<hr>

<div class="row g-2 mb-3">
  <div class="col-md-9">
    <label class="form-label">Vincular em lote (por filial)</label>
    <select id="avd-filial-lote" class="form-select">
      <option value="">Selecione uma filial...</option>
      @foreach($filiais as $f)
        <option value="{{ $f->id }}">{{ $f->nome_fantasia ?? $f->razao_social }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3 d-flex align-items-end">
    <button type="button" class="btn btn-primary w-100" id="avd-btn-vincular-lote">
      Vincular lote
    </button>
  </div>
</div>

<div class="table-responsive">
  <table class="table">
    <thead class="bg-primary">
      <tr>
        <th>Nome</th>
        <th>Filial</th>
        <th>WhatsApp</th>
        <th>Links</th>
        <th>Status</th>
        <th style="width:120px;">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($participantes as $p)
        <tr data-row="{{ $p->id }}">
          <td>{{ $p->colaborador_nome }}</td>
          <td>{{ $p->filial_nome ?? '-' }}</td>
          <td>
            <input class="form-control form-control-sm avd-whatsapp-edit"
                   data-pid="{{ $p->id }}"
                   value="{{ $p->whatsapp ?? '' }}"
                   placeholder="55...">
          </td>
          <td>
            <div class="d-flex flex-column gap-1">
              <button type="button" class="btn btn-sm btn-outline-secondary avd-copy"
                data-copy="{{ url('/avaliacao/'.$p->token_auto) }}">Copiar link auto</button>

              <button type="button" class="btn btn-sm btn-outline-secondary avd-copy"
                data-copy="{{ url('/avaliacao/'.$p->token_gestor) }}">Copiar link gestor</button>

              @if($ciclo->tipo === '360' && $p->token_pares)
                <button type="button" class="btn btn-sm btn-outline-secondary avd-copy"
                  data-copy="{{ url('/avaliacao/'.$p->token_pares) }}">Copiar link pares</button>
              @endif
            </div>
          </td>
          <td>{{ $p->status }}</td>
          <td>
            <button type="button"
              class="btn btn-danger btn-sm avd-btn-remover"
              data-url="{{ route('avd.ciclos.participantes.remover', ['sub'=>request()->route('sub'),'id'=>$ciclo->id,'pid'=>$p->id]) }}">
              <i data-feather="trash-2"></i>
            </button>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center">Nenhum colaborador vinculado.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  function refresh(){
    fetch(`{{ route('avd.ciclos.tab.participantes', ['sub'=>request()->route('sub'),'id'=>$ciclo->id]) }}`, {
      headers: {'X-Requested-With':'XMLHttpRequest'}
    }).then(r=>r.text()).then(html=>{
      document.getElementById('avd-tab-participantes-wrapper').innerHTML = html;
      if(window.feather) feather.replace();
    });
  }

  document.getElementById('avd-btn-vincular-colab')?.addEventListener('click', function(){
    const colaborador_id = document.getElementById('avd-colab-select')?.value;
    const whatsapp = document.getElementById('avd-colab-whatsapp')?.value || '';
    if(!colaborador_id) return;

    fetch(`{{ route('avd.ciclos.participantes.vincular', ['sub'=>request()->route('sub'),'id'=>$ciclo->id]) }}`, {
      method:'POST',
      headers:{
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
        'Content-Type':'application/json'
      },
      body: JSON.stringify({ colaborador_id, whatsapp })
    }).then(()=>refresh());
  });

  document.getElementById('avd-btn-vincular-lote')?.addEventListener('click', function(){
    const filial_id = document.getElementById('avd-filial-lote')?.value;
    if(!filial_id) return;

    fetch(`{{ route('avd.ciclos.participantes.vincular_lote', ['sub'=>request()->route('sub'),'id'=>$ciclo->id]) }}`, {
      method:'POST',
      headers:{
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
        'Content-Type':'application/json'
      },
      body: JSON.stringify({ filial_id })
    }).then(()=>refresh());
  });

  document.querySelectorAll('.avd-btn-remover').forEach(btn=>{
    btn.addEventListener('click', function(){
      const url = this.dataset.url;
      fetch(url, {
        method:'DELETE',
        headers:{ 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
      }).then(()=>refresh());
    });
  });

  document.querySelectorAll('.avd-whatsapp-edit').forEach(inp=>{
    inp.addEventListener('change', function(){
      const pid = this.dataset.pid;
      const whatsapp = this.value || '';

      fetch(`{{ route('avd.ciclos.participantes.atualizar', ['sub'=>request()->route('sub'),'id'=>$ciclo->id,'pid'=>0]) }}`.replace('/0/atualizar','/'+pid+'/atualizar'), {
        method:'PUT',
        headers:{
          'X-Requested-With':'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf,
          'Content-Type':'application/json'
        },
        body: JSON.stringify({ whatsapp })
      });
    });
  });

  document.querySelectorAll('.avd-copy').forEach(b=>{
    b.addEventListener('click', async function(){
      const text = this.dataset.copy || '';
      if(!text) return;
      try {
        await navigator.clipboard.writeText(text);
      } catch(e) {}
    });
  });

  if(window.feather) feather.replace();
})();
</script>
