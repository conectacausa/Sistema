(function () {
  function csrfToken() {
    var el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
  }

  function showAlert(type, message) {
    var area = document.getElementById('alert-area');
    if (!area) return;

    var div = document.createElement('div');
    div.className = 'alert alert-' + type + ' alert-dismissible';
    div.innerHTML =
      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
      (message || '');

    area.prepend(div);

    // auto-fecha (opcional) após 3s para não poluir
    setTimeout(function () {
      try {
        // bootstrap 5 remove via DOM
        div.remove();
      } catch (e) {}
    }, 3000);
  }

  var lock = new Map(); // key: tela_id|campo

  async function togglePerm(el) {
    var telaId = el.getAttribute('data-tela-id');
    var campo = el.getAttribute('data-campo');
    var valor = el.checked ? '1' : '0';

    var key = telaId + '|' + campo;
    if (lock.get(key)) return;
    lock.set(key, true);

    try {
      const res = await fetch(window.CON_TOGGLE_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken(),
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          tela_id: parseInt(telaId, 10),
          campo: campo,
          valor: valor
        })
      });

      const data = await res.json().catch(function () { return null; });

      if (!res.ok || !data || data.ok !== true) {
        // rollback visual
        el.checked = !el.checked;
        showAlert('danger', (data && data.message) ? data.message : 'Erro ao salvar permissão.');
        return;
      }

      showAlert('success', data.message || 'Permissão atualizada.');
    } catch (e) {
      el.checked = !el.checked;
      showAlert('danger', 'Falha de conexão ao salvar permissão.');
    } finally {
      lock.set(key, false);
    }
  }

  document.addEventListener('change', function (ev) {
    var target = ev.target;
    if (target && target.classList && target.classList.contains('js-perm')) {
      togglePerm(target);
    }
  });
})();
