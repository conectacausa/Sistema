/**
 * Tela: Configuração > Filiais
 * Slug: config/filiais
 * Screen ID: 5
 */

(function () {
  'use strict';

  const SCREEN_ID = window.__SCREEN_ID__ || 5;

  const ROUTE_NOVA = '/config/filiais/nova';
  const ROUTE_EDIT = (id) => `/config/filiais/${id}/editar`;

  const API_BASE = '/api';
  const API_FILIAIS = `${API_BASE}/filiais`;
  const API_DELETE_FILIAL = (id) => `${API_BASE}/filiais/${id}`;

  const API_PAISES = `${API_BASE}/paises`;
  const API_ESTADOS = (paisId) => `${API_BASE}/paises/${paisId}/estados`;
  const API_CIDADES = (estadoId) => `${API_BASE}/estados/${estadoId}/cidades`;

  const PER_PAGE = 50;

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

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  async function apiGet(url) {
    const res = await fetch(url, {
      headers: { 'Accept': 'application/json' },
      credentials: 'include'
    });

    if (!res.ok) {
      const txt = await res.text();
      throw new Error(`GET ${url} -> ${res.status}: ${txt}`);
    }
    return res.json();
  }

  async function apiDelete(url) {
    const csrf = getCsrfToken();

    const res = await fetch(url, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {})
      },
      credentials: 'include'
    });

    if (!res.ok) {
      const txt = await res.text();
      throw new Error(`DELETE ${url} -> ${res.status}: ${txt}`);
    }
    return res.json().catch(() => ({}));
  }

  function setOptions(selectEl, items, placeholder) {
    selectEl.innerHTML = '';

    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = placeholder;
    selectEl.appendChild(opt0);

    (items || []).forEach(item => {
      const opt = document.createElement('option');
      opt.value = String(item.id);
      opt.textContent = item.nome || item.descricao || item.sigla || `#${item.id}`;
      selectEl.appendChild(opt);
    });
  }

  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function maskCnpj(cnpj) {
    const digits = String(cnpj || '').replace(/\D/g, '').padStart(14, '0').slice(-14);
    return digits.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
  }

  function renderRows(rows) {
    if (!rows || rows.length === 0) {
      elTBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center text-muted">Nenhuma filial encontrada.</td>
        </tr>`;
      return;
    }

    elTBody.innerHTML = rows.map(r => `
      <tr>
        <td>${escapeHtml(r.nome_fantasia || r.razao_social || '')}</td>
        <td>${maskCnpj(r.cnpj)}</td>
        <td>${escapeHtml(r.cidade?.nome || '')}</td>
        <td>${escapeHtml(r.estado?.sigla || '')}</td>
        <td>${escapeHtml(r.pais?.nome || '')}</td>
        <td>
          <button type="button" class="btn btn-sm btn-primary me-1 btnEditar" data-id="${r.id}">Editar</button>
          <button type="button" class="btn btn-sm btn-danger btnExcluir" data-id="${r.id}">Excluir</button>
        </td>
      </tr>
    `).join('');
  }

  function renderPagination() {
    const start = state.total === 0 ? 0 : ((state.page - 1) * PER_PAGE) + 1;
    const end = Math.min(state.page * PER_PAGE, state.total);

    elInfo.textContent = `Mostrando ${start}–${end} de ${state.total}`;
    elPrev.disabled = state.page <= 1 || state.loading;
    elNext.disabled = state.page >= state.lastPage || state.loading;
  }

  function buildFiliaisUrl() {
    const p = new URLSearchParams({
      screen_id: String(SCREEN_ID),
      page: String(state.page),
      per_page: String(PER_PAGE)
    });

    if (state.filtros.q) p.set('q', state.filtros.q);
    if (state.filtros.pais_id) p.set('pais_id', state.filtros.pais_id);
    if (state.filtros.estado_id) p.set('estado_id', state.filtros.estado_id);
    if (state.filtros.cidade_id) p.set('cidade_id', state.filtros.cidade_id);

    return `${API_FILIAIS}?${p.toString()}`;
  }

  async function carregarFiliais() {
    state.loading = true;
    renderPagination();

    try {
      const data = await apiGet(buildFiliaisUrl());
      const rows = data.data ?? [];
      const meta = data.meta ?? {};

      state.total = Number(meta.total ?? 0);
      state.lastPage = Number(meta.last_page ?? 1);
      state.page = Number(meta.current_page ?? state.page);

      renderRows(rows);
    } catch (err) {
      console.error('[Filiais] Erro ao carregar', err);
      elTBody.innerHTML = `
        <tr>
          <td colspan="6" class="text-center text-danger">Erro ao carregar filiais</td>
        </tr>`;
      state.total = 0;
      state.lastPage = 1;
    } finally {
      state.loading = false;
      renderPagination();
    }
  }

  async function carregarPaises() {
    const data = await apiGet(`${API_PAISES}?screen_id=${SCREEN_ID}`);
    setOptions(elPais, data.data || [], 'Lista de País');
  }

  async function onChangePais() {
    const paisId = elPais.value || '';
    state.filtros.pais_id = paisId;

    // reset estado/cidade
    state.filtros.estado_id = '';
    state.filtros.cidade_id = '';

    elEstado.disabled = true;
    elCidade.disabled = true;
    setOptions(elEstado, [], 'Lista de Estado');
    setOptions(elCidade, [], 'Lista de Cidade');

    if (paisId) {
      try {
        setOptions(elEstado, [], 'Carregando...');
        const data = await apiGet(`${API_ESTADOS(paisId)}?screen_id=${SCREEN_ID}`);
        setOptions(elEstado, data.data || [], 'Lista de Estado');
        elEstado.disabled = false;
      } catch (err) {
        console.error('[Estados] Erro ao carregar', err);
        await Swal.fire('Erro', 'Não foi possível carregar os estados deste país.', 'error');
        elEstado.disabled = true;
        setOptions(elEstado, [], 'Lista de Estado');
      }
    }

    state.page = 1;
    await carregarFiliais();
  }

  async function onChangeEstado() {
    const estadoId = elEstado.value || '';
    state.filtros.estado_id = estadoId;

    // reset cidade
    state.filtros.cidade_id = '';
    elCidade.disabled = true;
    setOptions(elCidade, [], 'Lista de Cidade');

    if (estadoId) {
      try {
        setOptions(elCidade, [], 'Carregando...');
        const data = await apiGet(`${API_CIDADES(estadoId)}?screen_id=${SCREEN_ID}`);
        setOptions(elCidade, data.data || [], 'Lista de Cidade');
        elCidade.disabled = false;
      } catch (err) {
        console.error('[Cidades] Erro ao carregar', err);
        await Swal.fire('Erro', 'Não foi possível carregar as cidades deste estado.', 'error');
        elCidade.disabled = true;
        setOptions(elCidade, [], 'Lista de Cidade');
      }
    }

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

  async function onTableClick(e) {
    const btnEdit = e.target.closest('.btnEditar');
    const btnDel = e.target.closest('.btnExcluir');

    if (btnEdit) {
      window.location.href = ROUTE_EDIT(btnEdit.dataset.id);
      return;
    }

    if (btnDel) {
      const id = btnDel.dataset.id;

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
        await apiDelete(API_DELETE_FILIAL(id));
        await Swal.fire('Excluída!', 'A filial foi excluída com sucesso.', 'success');
        await carregarFiliais();
      } catch (err) {
        console.error('[Excluir] Erro', err);
        await Swal.fire('Erro', 'Não foi possível excluir a filial.', 'error');
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

  async function init() {
    elNova.addEventListener('click', () => window.location.href = ROUTE_NOVA);

    // garante reset mesmo ao limpar colando/apagando
    elQ.addEventListener('input', onTypingQ);
    elQ.addEventListener('change', onTypingQ);
    elQ.addEventListener('keyup', onTypingQ);

    elPais.addEventListener('change', onChangePais);
    elEstado.addEventListener('change', onChangeEstado);
    elCidade.addEventListener('change', onChangeCidade);

    elTBody.addEventListener('click', onTableClick);

    elPrev.addEventListener('click', onPrev);
    elNext.addEventListener('click', onNext);

    elEstado.disabled = true;
    elCidade.disabled = true;

    await carregarPaises();
    await carregarFiliais();
  }

  init();
})();
