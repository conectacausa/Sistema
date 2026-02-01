(function () {
  const $ = (sel) => document.querySelector(sel);

  const els = {
    q: $("#filtroRazaoCnpj"),
    pais: $("#filtroPais"),
    estado: $("#filtroEstado"),
    cidade: $("#filtroCidade"),

    tbody: $("#tabelaFiliaisBody"),
    resumo: $("#filiaisResumo"),
    pagInfo: $("#paginacaoInfo"),
    prev: $("#btnPrev"),
    next: $("#btnNext"),

    btnNova: $("#btnNovaFilial"),
  };

  const routes = window.FILIAIS_ROUTES || {};

  const state = {
    page: 1,
    perPage: 10,
    lastPage: 1,
    loading: false,
    debounceTimer: null,
    currentFilters: {
      q: "",
      pais_id: "",
      estado_id: "",
      cidade_id: ""
    }
  };

  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "";
  }

  function escapeHtml(s) {
    return String(s ?? "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  function setLoading(isLoading) {
    state.loading = isLoading;
  }

  function buildQuery() {
    const p = new URLSearchParams();
    p.set("page", String(state.page));
    p.set("per_page", String(state.perPage));

    const f = state.currentFilters;
    if (f.q) p.set("q", f.q);
    if (f.pais_id) p.set("pais_id", f.pais_id);
    if (f.estado_id) p.set("estado_id", f.estado_id);
    if (f.cidade_id) p.set("cidade_id", f.cidade_id);

    return p.toString();
  }

  async function fetchJson(url, opts) {
    const res = await fetch(url, opts);

    if (!res.ok) {
      try {
        const j = await res.json();
        const err = new Error(j?.message || `Erro HTTP ${res.status}`);
        err.detail = j?.detail;
        throw err;
      } catch (_) {
        const t = await res.text().catch(() => "");
        const err = new Error(`Erro HTTP ${res.status}`);
        err.detail = t ? t.substring(0, 180) : "";
        throw err;
      }
    }

    return res.json();
  }

  async function loadPaises() {
    try {
      const data = await fetchJson(routes.paises);
      const rows = data?.data || [];

      els.pais.innerHTML = `<option value="">Lista de País</option>` +
        rows.map(r => `<option value="${r.id}">${escapeHtml(r.nome)}</option>`).join("");
    } catch (_) {
      els.pais.innerHTML = `<option value="">Lista de País</option>`;
    }
  }

  async function loadEstados(paisId) {
    els.estado.disabled = true;
    els.estado.innerHTML = `<option value="">Lista de Estado</option>`;
    els.cidade.disabled = true;
    els.cidade.innerHTML = `<option value="">Lista de Cidade</option>`;

    if (!paisId) return;

    try {
      const url = routes.estados + "?" + new URLSearchParams({ pais_id: paisId }).toString();
      const data = await fetchJson(url);
      const rows = data?.data || [];

      els.estado.innerHTML = `<option value="">Lista de Estado</option>` +
        rows.map(r => `<option value="${r.id}">${escapeHtml(r.nome)} (${escapeHtml(r.uf)})</option>`).join("");

      els.estado.disabled = false;
    } catch (_) {
      // ignora
    }
  }

  async function loadCidades(estadoId) {
    els.cidade.disabled = true;
    els.cidade.innerHTML = `<option value="">Lista de Cidade</option>`;

    if (!estadoId) return;

    try {
      const url = routes.cidades + "?" + new URLSearchParams({ estado_id: estadoId }).toString();
      const data = await fetchJson(url);
      const rows = data?.data || [];

      els.cidade.innerHTML = `<option value="">Lista de Cidade</option>` +
        rows.map(r => `<option value="${r.id}">${escapeHtml(r.nome)}</option>`).join("");

      els.cidade.disabled = false;
    } catch (_) {
      // ignora
    }
  }

  function renderRow(item) {
    const id = item.id;
    const nome = escapeHtml(item.nome_fantasia || "");
    const cnpj = escapeHtml(item.cnpj || "");
    const cidade = escapeHtml(item.cidade_nome || "");
    const uf = escapeHtml(item.estado_uf || "");
    const pais = escapeHtml(item.pais_nome || "");

    return `
      <tr data-id="${id}">
        <td>${nome}</td>
        <td>${cnpj}</td>
        <td>${cidade}</td>
        <td>${uf}</td>
        <td>${pais}</td>
        <td class="text-nowrap">
          <button type="button" class="btn btn-sm btn-primary js-edit" data-id="${id}" title="Editar">
            <i data-feather="edit"></i>
          </button>

          <button type="button" class="btn btn-sm btn-danger js-del" data-id="${id}" title="Excluir">
            <i data-feather="trash-2"></i>
          </button>
        </td>
      </tr>
    `;
  }

  function renderTable(items) {
    if (!items || items.length === 0) {
      els.tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Nenhuma filial encontrada.</td></tr>`;
      return;
    }
    els.tbody.innerHTML = items.map(renderRow).join("");
  }

  function renderPagination(meta) {
    const total = meta?.total ?? 0;
    const from = meta?.from ?? 0;
    const to = meta?.to ?? 0;

    state.lastPage = meta?.last_page ?? 1;

    els.pagInfo.textContent = total
      ? `Mostrando ${from}–${to} de ${total}`
      : `Mostrando 0`;

    els.prev.disabled = state.page <= 1;
    els.next.disabled = state.page >= state.lastPage;

    if (total && from && to) {
      els.resumo.textContent = `Exibindo ${to - from + 1} item(ns) nesta página.`;
    } else {
      els.resumo.textContent = `Exibindo 0 item(ns) nesta página.`;
    }
  }

  async function loadGrid() {
    if (state.loading) return;
    setLoading(true);

    els.tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Carregando filiais...</td></tr>`;

    try {
      const url = routes.grid + "?" + buildQuery();
      const data = await fetchJson(url);

      renderTable(data?.data || []);

      // ✅ necessário após render via AJAX
      if (window.feather) feather.replace();

      renderPagination(data?.meta || {});
    } catch (e) {
      let msg = e.message || "Erro ao carregar";
      if (e.detail) msg = `${msg} - ${String(e.detail).substring(0, 160)}`;

      els.tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${escapeHtml(msg)}</td></tr>`;
      els.pagInfo.textContent = "";
      els.resumo.textContent = "";
    } finally {
      setLoading(false);
    }
  }

  function applyFiltersAndReload() {
    state.currentFilters.q = (els.q?.value || "").trim();
    state.currentFilters.pais_id = els.pais?.value || "";
    state.currentFilters.estado_id = els.estado?.value || "";
    state.currentFilters.cidade_id = els.cidade?.value || "";
    state.page = 1;
    loadGrid();
  }

  function debounceApplyFilters() {
    if (state.debounceTimer) clearTimeout(state.debounceTimer);
    state.debounceTimer = setTimeout(applyFiltersAndReload, 350);
  }

  async function onDelete(id) {
    if (!id) return;

    swal({
      title: "Confirmar exclusão?",
      text: "Esta ação não poderá ser desfeita.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      confirmButtonText: "Sim, excluir",
      cancelButtonText: "Cancelar",
      closeOnConfirm: false
    }, async function () {
      try {
        await fetchJson(routes.destroy(id), {
          method: "DELETE",
          headers: {
            "X-CSRF-TOKEN": csrfToken(),
            "Accept": "application/json"
          }
        });

        swal("Excluída!", "Filial removida com sucesso.", "success");

        await loadGrid();

        const hasRows = els.tbody.querySelectorAll("tr[data-id]").length > 0;
        if (!hasRows && state.page > 1) {
          state.page = Math.max(1, state.page - 1);
          loadGrid();
        }
      } catch (e) {
        let msg = e.message || "Não foi possível excluir.";
        if (e.detail) msg = `${msg} - ${String(e.detail).substring(0, 160)}`;
        swal("Erro", msg, "error");
      }
    });
  }

  function onEdit(id) {
    if (!id) return;
    const url = typeof routes.edit === "function" ? routes.edit(id) : (routes.edit + id);
    window.location.href = url;
  }

  function initEvents() {
    els.btnNova?.addEventListener("click", function () {
      window.location.href = routes.create;
    });

    els.q?.addEventListener("input", debounceApplyFilters);

    els.pais?.addEventListener("change", async function () {
      const paisId = els.pais.value || "";
      state.currentFilters.pais_id = paisId;
      state.currentFilters.estado_id = "";
      state.currentFilters.cidade_id = "";

      await loadEstados(paisId);
      applyFiltersAndReload();
    });

    els.estado?.addEventListener("change", async function () {
      const estadoId = els.estado.value || "";
      state.currentFilters.estado_id = estadoId;
      state.currentFilters.cidade_id = "";

      await loadCidades(estadoId);
      applyFiltersAndReload();
    });

    els.cidade?.addEventListener("change", function () {
      applyFiltersAndReload();
    });

    els.prev?.addEventListener("click", function () {
      if (state.page > 1) {
        state.page -= 1;
        loadGrid();
      }
    });

    els.next?.addEventListener("click", function () {
      if (state.page < state.lastPage) {
        state.page += 1;
        loadGrid();
      }
    });

    // Delegação (AJAX)
    document.addEventListener("click", function (ev) {
      const btn = ev.target.closest(".js-edit, .js-del");
      if (!btn) return;

      const id = btn.getAttribute("data-id");
      if (btn.classList.contains("js-edit")) onEdit(id);
      if (btn.classList.contains("js-del")) onDelete(id);
    });
  }

  async function init() {
    initEvents();
    await loadPaises();
    await loadGrid();

    // feather inicial também (por garantia)
    if (window.feather) feather.replace();
  }

  document.addEventListener("DOMContentLoaded", init);
})();
