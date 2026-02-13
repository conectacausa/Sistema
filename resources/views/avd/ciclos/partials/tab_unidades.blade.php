{{-- resources/views/avd/ciclos/partials/tab_unidades.blade.php --}}
@php
  $sub = request()->route('sub');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Unidades vinculadas</h4>

  <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-vincular-unidade">
    Adicionar
  </button>
</div>

<div class="table-responsive">
  <table class="table table-hover align-middle w-100">
    <thead class="bg-primary">
      <tr>
        <th>Nome fantasia</th>
        <th>CNPJ</th>
        <th style="width:120px;" class="text-end">Ações</th>
      </tr>
    </thead>
    <tbody>
      @forelse($unidadesVinculadas as $u)
        <tr data-row="{{ $u->id }}">
          <td>{{ $u->nome_fantasia ?? $u->razao_social }}</td>
          <td>{{ $u->cnpj }}</td>
          <td class="text-end">
            <button type="button"
              class="btn btn-sm btn-outline-danger avd-btn-desvincular-unidade"
              data-url="{{ route('avd.ciclos.unidades.desvincular', ['sub'=>$sub,'id'=>$ciclo->id,'vinculo_id'=>$u->id]) }}"
              title="Desvincular">
              <i data-feather="trash-2"></i>
            </button>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="text-center text-muted py-4">Nenhuma unidade vinculada.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- MODAL: Vincular unidade --}}
<div class="modal fade" id="modal-vincular-unidade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Vincular unidade</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>

      <div class="modal-body">
        <label class="form-label">Selecione</label>

        {{-- opções: "Todas" + filiais --}}
        <select id="avd-modal-filial-select" class="form-select">
          <option value="">Selecione...</option>
          <option value="all">Todas</option>
          @foreach($filiais as $f)
            <option value="{{ $f->id }}">{{ $f->nome_fantasia ?? $f->razao_social }}</option>
          @endforeach
        </select>

        <small class="text-muted d-block mt-2">
          “Todas” vincula todas as filiais disponíveis desta empresa.
        </small>

        <div id="avd-modal-unidades-alert" class="alert alert-warning d-none mt-3 mb-0">
          Selecione uma opção.
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="avd-btn-confirmar-vinculo-unidade">
          Vincular
        </button>
      </div>

    </div>
  </div>
</div>

<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const wrapperId = 'avd-tab-unidades-wrapper';
  const selectEl = document.getElementById('avd-modal-filial-select');
  const btnConfirm = document.getElementById('avd-btn-confirmar-vinculo-unidade');
  const alertEl = document.getElementById('avd-modal-unidades-alert');

  function renderFeather(){ if (window.feather) feather.replace(); }

  function refresh(){
    fetch(`{{ route('avd.ciclos.tab.unidades', ['sub'=>$sub,'id'=>$ciclo->id]) }}`, {
      headers: {'X-Requested-With':'XMLHttpRequest'}
    })
    .then(r=>r.text())
    .then(html=>{
      const w = document.getElementById(wrapperId);
      if(w) w.innerHTML = html;
      renderFeather();
    });
  }

  function postVincular(filial_id){
    return fetch(`{{ route('avd.ciclos.unidades.vincular', ['sub'=>$sub,'id'=>$ciclo->id]) }}`, {
      method: 'POST',
      headers: {
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': csrf,
        'Content-Type':'application/json'
      },
      body: JSON.stringify({ filial_id })
    });
  }

  btnConfirm?.addEventListener('click', function(){
    const val = (selectEl?.value || '').trim();
    if(!val){
      alertEl?.classList.remove('d-none');
      return;
    }
    alertEl?.classList.add('d-none');

    // fecha modal
    const modalEl = document.getElementById('modal-vincular-unidade');
    const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;

    // vincular todas
    if(val === 'all'){
      const ids = [
        @foreach($filiais as $f)
          {{ (int)$f->id }},
        @endforeach
      ];

      Promise.all(ids.map(id => postVincular(id)))
        .then(()=>{
          modal?.hide();
          refresh();
        })
        .catch(()=>{
          modal?.hide();
          refresh();
        });

      return;
    }

    // vincular individual
    postVincular(val)
      .then(()=>{
        modal?.hide();
        refresh();
      })
      .catch(()=>{
        modal?.hide();
        refresh();
      });
  });

  // desvincular
  document.querySelectorAll('.avd-btn-desvincular-unidade').forEach(b=>{
    b.addEventListener('click', function(){
      const url = this.dataset.url;
      fetch(url, {
        method:'DELETE',
        headers:{
          'X-Requested-With':'XMLHttpRequest',
          'X-CSRF-TOKEN': csrf
        }
      }).then(()=>refresh());
    });
  });

  // reset do select ao abrir
  document.getElementById('modal-vincular-unidade')?.addEventListener('show.bs.modal', function(){
    if(selectEl) selectEl.value = '';
    alertEl?.classList.add('d-none');
  });

  renderFeather();
})();
</script>
