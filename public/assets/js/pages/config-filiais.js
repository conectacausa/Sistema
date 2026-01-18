(function () {
  const SCREEN_ID = window.__SCREEN_ID__ || 5;

  // Rotas (páginas)
  const ROUTE_NOVA = '/config/filiais/nova';
  const ROUTE_EDIT = (id) => `/config/filiais/${id}/editar`;

  // APIs
  const API_PAISES = '/api/paises';
  const API_ESTADOS = (paisId) => `/api/paises/${paisId}/estados`;
  const API_CIDADES = (estadoId) => `/api/estados/${estadoId}/cidades`;

  // Listagem + paginação
  const API_FILIAIS = '/api/filiais';
  const API_DELETE_FILIAL = (id) => `/api/filiais/${id}`;

  const PER_PAGE = 50;

  // Elements
  const elNova = document.getElementById('btnNovaFilial');
  const elQ = document.getElementById('filtroRazaoCnpj');
  const elPais = document.getElementById('filtroPais');
  const elEstado = document.getElementById('filtroEstado');
  const elCidade = document.getElementById('filtroCidade');

  const elTBody = document.getElementById('tabelaFiliaisBody');
  const elInfo = document.getElementById('paginacaoInfo');
  const elPrev = document.getElementById('btnPrev');
  const elNext = document.getElementById('btnNext');

  const state = {
    page: 1,
    total: 0,
    lastPage: 1,
    loading: false,
    filtros: {
      q: '',
      pais_id: '',
      estado_id: '',
      cidade_id: ''
    }
  };

  function debounce(fn, wait) {
    let t;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  }

  async function apiGet(url) {
    const res = await fetch(url, {
      headers: { 'Accept': 'application/json' },
      credentials: 'include'
    });
    if (!res.ok) throw new Error('Falha ao buscar dados');
    return res.json();
  }

  async function apiDelete(url) {
    const res = await fetch(url, {
      method: 'DELETE',
      headers: { 'Accept': 'application/json' },
      credentials: 'include'
    });
    if (!res.ok) throw new Error('Falha ao excluir');
    return res.json().catch(() => ({}));
  }

  function setOptions(selectEl, items, placeholder) {
    selectEl.innerHTML = '';
    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = placeholder;
    selectEl.appendChild(opt0);

    items.forEach(it => {
      const opt = document.createElement('option');
      opt.value = String(it.id);
      opt.textContent = it.nome ?? it.descricao ?? it.sigla ?? `#${it.id}`;
      selectEl.appendChild(opt);
    });
  }

  // CNPJ com máscara (00.000.000/0000-00)
  function maskCnpj(cnpj) {
    const digits = String(cnpj || '').replace(/\D/g, '').padStart(14, '0').slice(-14);
    return digits.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
  }

  function escapeHtml(str) {
    return String(str ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  function renderRows(rows) {
    if (!rows || rows.length === 0) {
      elTBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center text-muted">Nenhuma filial encontrada.</td>
        </tr>
      `;
      return;
    }

    elTBody.innerHTML = rows.map(r => {
      const nomeFantasia = escapeHtml(r.nome_fantasia || r.razao_social || '');
      const cnpj = maskCnpj(r.cnpj);
      const cidade = escapeHtml((r.cidade && r.cidade.nome) || r.cidade_nome || '');
      const uf = escapeHtml((r.estado && r.estado.sigla) || r.uf || '');
      const pais = escapeHtml((r.pais && r.pais.nome) || r.pais_nome || '');

      return `
        <tr>
          <td>${nomeFantasia}</td>
          <td>${cnpj}</td>
          <td>${cidade}</td>
          <td>${uf}</td>
          <td>${pais}</td>
          <td>
            <button type="button" class="btn btn-sm btn-primary me-1 btnEditar" data-id="${r.id}">Editar</button>
            <button type="button" class="btn btn-sm btn-danger btnExcluir" data-id="${r.id}">Excluir</button>
          </td>
        </tr>
      `;
    }).join('');
  }

  function renderPagination() {
    const start = state.total === 0 ? 0 : ((state.page - 1) * PER_PAGE) + 1;
    const end = Math.min(state.page * PER_PAGE, state.total);

    elInfo.textContent = `Mostrando ${start}–${end} de ${state.total}`;
    elPrev.disabled = state.page <= 1 || state.loading;
    elNext.disabled = state.page >= state.lastPage || state.loading;
  }

  function buildFiliaisUrl() {
    const params = new URLSearchParams();
    params.set('screen_id', String(SCREEN_ID));
    params.set('page', String(state.page));
    params.set('per_page', String(PER_PAGE));

    if (state.filtros.q) params.set('q', state.filtros.q);
    if (state.filtros.pais_id) params.set('pais_id', state.filtros.pais_id);
    if (state.filtros.estado_id) params.set('estado_id', state.filtros.estado_id);
    if (state.filtros.cidade_id) params.set('cidade_id', state.filtros.cidade_id);

    return `${API_FILIAIS}?${params.toString()}`;
  }

  async function carregarFiliais() {
    state.loading = true;
    renderPagination();

    try {
      const data = await apiGet(buildFiliaisUrl());

      const rows = data.data ?? data.rows ?? [];
      const meta = data.meta ?? {};
      state.total = Number(meta.total ?? data.total ?? rows.length ?? 0);
      state.lastPage = Number(meta.last_page ?? data.last_page ?? 1);
      state.page = Number(meta.current_page ?? data.current_page ?? state.page);

      renderRows(rows);
      renderPagination();

    } catch (e) {
      elTBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center text-danger">Erro ao carregar filiais.</td>
        </tr>
      `;
      state.total = 0;
      state.lastPage = 1;
      renderPagination();
    } finally {
      state.loading = false;
      renderPagination();
    }
  }

  async function carregarPaises() {
    const data = await apiGet(`${API_PAISES}?screen_id=${encodeURIComponent(SCREEN_ID)}`);
    const items = data.data ?? data;
    setOptions(elPais, items, 'Lista de País');
  }

  async function onChangePais() {
    const paisId = elPais.value || '';
    state.filtros.pais_id = paisId;

    // Reset estado/cidade
    state.filtros.estado_id = '';
    state.filtros.cidade_id = '';
    elEstado.value = '';
    elCidade.value = '';
    elCidade.disabled = true;
    setOptions(elCidade, [], 'Lista de Cidade');

    if (!paisId) {
      elEstado.disabled = true;
      setOptions(elEstado, [], 'Lista de Estado');
      state.page = 1;
      await carregarFiliais();
      return;
    }

    // (3) Estado só habilita após País + lista filtrada
    elEstado.disabled = true;
    setOptions(elEstado, [], 'Carregando...');
    const data = await apiGet(`${API_ESTADOS(paisId)}?screen_id=${encodeURIComponent(SCREEN_ID)}`);
    const items = data.data ?? data;
    setOptions(elEstado, items, 'Lista de Estado');
    elEstado.disabled = false;

    state.page = 1;
    await carregarFiliais();
  }

  async function onChangeEstado() {
    const estadoId = elEstado.value || '';
    state.filtros.estado_id = estadoId;

    // Reset cidade
    state.filtros.cidade_id = '';
    elCidade.value = '';

    if (!estadoId) {
      elCidade.disabled = true;
      setOptions(elCidade, [], 'Lista de Cidade');
      state.page = 1;
      await carregarFiliais();
      return;
    }

    // (4) Cidade só habilita após Estado + lista filtrada
    elCidade.disabled = true;
    setOptions(elCidade, [], 'Carregando...');
    const data = await apiGet(`${API_CIDADES(estadoId)}?screen_id=${encodeURIComponent(SCREEN_ID)}`);
    const items = data.data ?? data;
    setOptions(elCidade, items, 'Lista de Cidade');
    elCidade.disabled = false;

    state.page = 1;
    await carregarFiliais();
  }

  async function onChangeCidade() {
    state.filtros.cidade_id = elCidade.value || '';
    state.page = 1;
    await carregarFiliais();
  }

  const onTypingQ = debounce(async function () {
    state.filtros.q = (elQ.value || '').trim();
    state.page = 1;
    await carregarFiliais();
  }, 250);

  async function onTableClick(ev) {
    const btnEdit = ev.target.closest('.btnEditar');
    const btnDel = ev.target.closest('.btnExcluir');

    if (btnEdit) {
      const id = btnEdit.getAttribute('data-id');
      window.location.href = ROUTE_EDIT(id);
      return;
    }

    if (btnDel) {
      const id = btnDel.getAttribute('data-id');

      const result = await Swal.fire({
        title: 'Excluir filial?',
        text: 'Tem certeza que deseja excluir esta filial?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar'
      });

      if (!result.isConfirmed) return;

      try {
        await apiDelete(`${API_DELETE_FILIAL(id)}?screen_id=${encodeURIComponent(SCREEN_ID)}`);
        await Swal.fire({ title: 'Excluída!', text: 'A filial foi excluída.', icon: 'success' });

        // Se apagou o último item da página, tenta voltar 1 página
        const beforeTotal = state.total;
        await carregarFiliais();
        if (beforeTotal > 0 && state.total === (beforeTotal - 1) && state.page > state.lastPage) {
          state.page = state.lastPage;
          await carregarFiliais();
        }

      } catch (e) {
        await Swal.fire({ title: 'Erro', text: 'Não foi possível excluir a filial.', icon: 'error' });
      }
    }
  }

  async function onPrev() {
    if (state.page <= 1) return;
    state.page -= 1;
    await carregarFiliais();
  }

  async function onNext() {
    if (state.page >= state.lastPage) return;
    state.page += 1;
    await carregarFiliais();
  }

  function onNovaFilial() {
    window.location.href = ROUTE_NOVA;
  }

  async function init() {
    elNova.addEventListener('click', onNovaFilial);

    elQ.addEventListener('input', onTypingQ);
    elPais.addEventListener('change', onChangePais);
    elEstado.addEventListener('change', onChangeEstado);
    elCidade.addEventListener('change', onChangeCidade);

    elTBody.addEventListener('click', onTableClick);

    elPrev.addEventListener('click', onPrev);
    elNext.addEventListener('click', onNext);

    elEstado.disabled = true;
    elCidade.disabled = true;

    try { await carregarPaises(); } catch (e) {}
    await carregarFiliais();
  }

  init();
})();
