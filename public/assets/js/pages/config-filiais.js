(function () {
    'use strict';

    // =====================================================
    // CONFIGURAÇÕES
    // =====================================================
    const SCREEN_ID = 5;
    const PER_PAGE = 50;

    const API = {
        filiais: '/api/filiais',
        deleteFilial: (id) => `/api/filiais/${id}`,
        paises: '/api/paises',
        estados: (paisId) => `/api/paises/${paisId}/estados`,
        cidades: (estadoId) => `/api/estados/${estadoId}/cidades`
    };

    const ROUTES = {
        nova: '/config/filiais/nova',
        editar: (id) => `/config/filiais/${id}/editar`
    };

    // =====================================================
    // ELEMENTOS DOM
    // =====================================================
    const elNova = document.getElementById('btnNovaFilial');
    const elQ = document.getElementById('filtroRazaoCnpj');
    const elPais = document.getElementById('filtroPais');
    const elEstado = document.getElementById('filtroEstado');
    const elCidade = document.getElementById('filtroCidade');

    const elTBody = document.getElementById('tabelaFiliaisBody');
    const elInfo = document.getElementById('paginacaoInfo');
    const elPrev = document.getElementById('btnPrev');
    const elNext = document.getElementById('btnNext');

    // =====================================================
    // ESTADO INTERNO
    // =====================================================
    const state = {
        page: 1,
        lastPage: 1,
        total: 0,
        filtros: {
            q: '',
            pais_id: '',
            estado_id: '',
            cidade_id: ''
        }
    };

    // =====================================================
    // HELPERS
    // =====================================================
    const debounce = (fn, delay = 300) => {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(null, args), delay);
        };
    };

    const maskCnpj = (value) => {
        if (!value) return '';
        const v = String(value).replace(/\D/g, '').padStart(14, '0');
        return v.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
    };

    const escapeHtml = (str) =>
        String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    const setOptions = (select, items, placeholder) => {
        select.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        select.appendChild(opt);

        (items || []).forEach(item => {
            const o = document.createElement('option');
            o.value = item.id;
            o.textContent = item.nome;
            select.appendChild(o);
        });
    };

    const getSwal = () => {
        if (typeof window.swal === 'function') return window.swal;
        if (typeof window.sweetAlert === 'function') return window.sweetAlert;
        return null;
    };

    // aplica classes bootstrap nos botões do SweetAlert v1 (compatível)
    const applySwalButtonStyles = () => {
        try {
            const btns = document.querySelectorAll('.swal-button');
            if (!btns || !btns.length) return;

            // normalmente: [0]=cancel, [1]=confirm
            if (btns[0]) {
                btns[0].classList.add('btn', 'btn-primary');
            }
            if (btns[1]) {
                btns[1].classList.add('btn', 'btn-danger');
            }
        } catch (e) {
            // ignora
        }
    };

    // =====================================================
    // API HELPERS
    // =====================================================
    const apiGet = async (url) => {
        const res = await fetch(`${url}?screen_id=${SCREEN_ID}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'include'
        });
        if (!res.ok) throw new Error(`Erro ${res.status}`);
        return res.json();
    };

    const apiDelete = async (url) => {
        const tokenEl = document.querySelector('meta[name="csrf-token"]');
        const token = tokenEl ? tokenEl.content : '';

        const res = await fetch(`${url}?screen_id=${SCREEN_ID}`, {
            method: 'DELETE',
            headers: {
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                'Accept': 'application/json'
            },
            credentials: 'include'
        });
        if (!res.ok) {
            let body = '';
            try { body = await res.text(); } catch (_) {}
            throw new Error(`Erro ${res.status} ${body}`);
        }
        return res.json().catch(() => ({}));
    };

    // =====================================================
    // RENDERIZAÇÃO
    // =====================================================
    const renderTabela = (rows) => {
        if (!rows || !rows.length) {
            elTBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        Nenhuma filial encontrada
                    </td>
                </tr>`;
            return;
        }

        elTBody.innerHTML = rows.map(r => `
            <tr>
                <td>${escapeHtml(r.nome_fantasia || r.razao_social)}</td>
                <td>${maskCnpj(r.cnpj)}</td>
                <td>${escapeHtml(r.cidade?.nome || '')}</td>
                <td>${escapeHtml(r.estado?.sigla || '')}</td>
                <td>${escapeHtml(r.pais?.nome || '')}</td>
                <td>
                    <button class="btn btn-sm btn-primary btnEditar" data-id="${r.id}">Editar</button>
                    <button class="btn btn-sm btn-danger btnExcluir" data-id="${r.id}">Excluir</button>
                </td>
            </tr>
        `).join('');
    };

    const renderPaginacao = () => {
        const start = state.total ? ((state.page - 1) * PER_PAGE + 1) : 0;
        const end = Math.min(state.page * PER_PAGE, state.total);

        elInfo.textContent = `Mostrando ${start}–${end} de ${state.total}`;
        elPrev.disabled = state.page <= 1;
        elNext.disabled = state.page >= state.lastPage;
    };

    // =====================================================
    // LOADERS
    // =====================================================
    const carregarFiliais = async () => {
        const params = new URLSearchParams({
            page: String(state.page),
            per_page: String(PER_PAGE),
            q: state.filtros.q || '',
            pais_id: state.filtros.pais_id || '',
            estado_id: state.filtros.estado_id || '',
            cidade_id: state.filtros.cidade_id || ''
        });

        try {
            const res = await fetch(`${API.filiais}?${params.toString()}&screen_id=${SCREEN_ID}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'include'
            });

            const json = await res.json();

            renderTabela(json.data || []);
            state.total = Number(json.meta?.total || 0);
            state.lastPage = Number(json.meta?.last_page || 1);
            state.page = Number(json.meta?.current_page || 1);

            renderPaginacao();
        } catch (e) {
            console.error(e);
            elTBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        Erro ao carregar filiais
                    </td>
                </tr>`;
        }
    };

    const carregarPaises = async () => {
        const res = await apiGet(API.paises);
        setOptions(elPais, res.data || [], 'Lista de País');
    };

    const carregarEstados = async (paisId) => {
        elEstado.disabled = true;
        setOptions(elEstado, [], 'Carregando...');
        const res = await apiGet(API.estados(paisId));
        setOptions(elEstado, res.data || [], 'Lista de Estado');
        elEstado.disabled = false;
    };

    const carregarCidades = async (estadoId) => {
        elCidade.disabled = true;
        setOptions(elCidade, [], 'Carregando...');
        const res = await apiGet(API.cidades(estadoId));
        setOptions(elCidade, res.data || [], 'Lista de Cidade');
        elCidade.disabled = false;
    };

    // =====================================================
    // EVENTOS
    // =====================================================
    if (elNova) {
        elNova.addEventListener('click', () => {
            window.location.href = ROUTES.nova;
        });
    }

    if (elQ) {
        elQ.addEventListener('input', debounce(() => {
            state.filtros.q = (elQ.value || '').trim();
            state.page = 1;
            carregarFiliais();
        }));
    }

    if (elPais) {
        elPais.addEventListener('change', async () => {
            state.filtros.pais_id = elPais.value || '';
            state.filtros.estado_id = '';
            state.filtros.cidade_id = '';
            state.page = 1;

            elEstado.disabled = true;
            elCidade.disabled = true;

            setOptions(elEstado, [], 'Lista de Estado');
            setOptions(elCidade, [], 'Lista de Cidade');

            if (elPais.value) {
                await carregarEstados(elPais.value);
            }

            carregarFiliais();
        });
    }

    if (elEstado) {
        elEstado.addEventListener('change', async () => {
            state.filtros.estado_id = elEstado.value || '';
            state.filtros.cidade_id = '';
            state.page = 1;

            setOptions(elCidade, [], 'Lista de Cidade');
            elCidade.disabled = true;

            if (elEstado.value) {
                await carregarCidades(elEstado.value);
            }

            carregarFiliais();
        });
    }

    if (elCidade) {
        elCidade.addEventListener('change', () => {
            state.filtros.cidade_id = elCidade.value || '';
            state.page = 1;
            carregarFiliais();
        });
    }

    if (elPrev) {
        elPrev.addEventListener('click', () => {
            if (state.page > 1) {
                state.page--;
                carregarFiliais();
            }
        });
    }

    if (elNext) {
        elNext.addEventListener('click', () => {
            if (state.page < state.lastPage) {
                state.page++;
                carregarFiliais();
            }
        });
    }

    // =====================================================
    // AÇÕES DA TABELA (EDITAR / EXCLUIR)
    // =====================================================
    if (elTBody) {
        elTBody.addEventListener('click', (e) => {
            const btnEdit = e.target.closest('.btnEditar');
            const btnDel = e.target.closest('.btnExcluir');

            if (btnEdit) {
                window.location.href = ROUTES.editar(btnEdit.dataset.id);
                return;
            }

            if (btnDel) {
                const swalFn = getSwal();
                if (!swalFn) {
                    console.error('SweetAlert v1 não carregado (swal/sweetAlert undefined).');
                    return;
                }

                const id = btnDel.dataset.id;

                // SweetAlert v1 compatível: 2 botões via array
                swalFn({
                    title: 'Excluir filial?',
                    text: 'Tem certeza que deseja excluir esta filial?',
                    icon: 'warning',
                    dangerMode: true,
                    buttons: ['Cancelar', 'Sim']
                }).then(async (confirmado) => {
                    if (!confirmado) return;

                    // Feedback "processando" (v1 não tem loading elegante nativo)
                    swalFn({
                        title: 'Excluindo...',
                        text: 'Aguarde',
                        icon: 'info',
                        buttons: false,
                        closeOnClickOutside: false,
                        closeOnEsc: false
                    });

                    try {
                        await apiDelete(API.deleteFilial(id));

                        swalFn({
                            title: 'Excluída',
                            text: 'Filial excluída com sucesso.',
                            icon: 'success',
                            timer: 1500,
                            buttons: false
                        });

                        carregarFiliais();
                    } catch (err) {
                        console.error(err);
                        swalFn({
                            title: 'Erro',
                            text: 'Não foi possível excluir a filial.',
                            icon: 'error'
                        });
                    }
                });

                // aplica classes bootstrap depois que o modal renderizar
                setTimeout(applySwalButtonStyles, 50);
                setTimeout(applySwalButtonStyles, 150);
            }
        });
    }

    // =====================================================
    // INIT
    // =====================================================
    (async function init() {
        if (elEstado) elEstado.disabled = true;
        if (elCidade) elCidade.disabled = true;

        await carregarPaises();
        await carregarFiliais();
    })();

})();
