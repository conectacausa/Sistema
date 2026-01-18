@php
    /**
     * MENU DINÂMICO
     * - Módulos exibidos = módulos que a empresa (tenant) tem acesso
     * - Telas exibidas = telas que o usuário logado tem permissão
     */

    // Tenant vindo do middleware
    $tenant = app()->bound('tenant') ? app('tenant') : null;

    // Usuário logado
    $user = auth()->user();

    /**
     * Helpers
     */
    $routeOrUrl = function ($slug) {
        // Se vier slug vazio, joga pra #
        if (!$slug) return '#';

        // Se já for URL absoluta
        if (preg_match('/^https?:\/\//i', $slug)) return $slug;

        // Se slug já começa com /, usa url direto
        if (str_starts_with($slug, '/')) return url($slug);

        // Caso padrão: url relativa
        return url($slug);
    };

    /**
     * ---------------------------------------------------------
     * Carregando módulos da EMPRESA + telas permitidas ao USUÁRIO
     * ---------------------------------------------------------
     *
     * Ajuste aqui conforme seus Models/relacionamentos reais.
     *
     * A ideia é retornar algo assim:
     * $modulos = [
     *   [
     *     'nome' => 'RH',
     *     'telas_sem_grupo' => [...],
     *     'grupos' => [
     *        ['nome' => 'Cadastros', 'icone' => 'grid', 'telas' => [...]]
     *     ]
     *   ],
     * ]
     */

    // --- EXEMPLO DE OBTENÇÃO (AJUSTE CONFORME SEUS MODELS) ---
    // Pressupostos comuns:
    // - Empresa tem relacionamento modulos() (pivot empresa_modulo)
    // - Modulo tem telas()
    // - Tela pode ter grupo (grupo_id) e slug, icone, nome
    // - Usuário tem grupo_permissao_id ou permissões via pivot grupo_tela

    // Se não tiver tenant, não monta nada
    $modulos = collect();

    if ($tenant && $user) {

        // 1) módulos liberados para a empresa
        // Ex: $tenant->modulos()
        $modulosEmpresa = method_exists($tenant, 'modulos')
            ? $tenant->modulos()->where('ativo', 1)->orderBy('ordem')->get()
            : collect();

        // 2) telas permitidas ao usuário
        // Ajuste para sua regra real.
        // Exemplo A: usuário tem grupo_permissao -> telas permitidas
        $telasPermitidasIds = collect();

        if (method_exists($user, 'grupoPermissao') && $user->grupoPermissao) {
            // Ex: $user->grupoPermissao->telas()
            if (method_exists($user->grupoPermissao, 'telas')) {
                $telasPermitidasIds = $user->grupoPermissao->telas()->pluck('telas.id');
            }
        } elseif (method_exists($user, 'telas')) {
            // Exemplo B: usuário tem telas direto
            $telasPermitidasIds = $user->telas()->pluck('telas.id');
        }

        // 3) montar estrutura final por módulo
        $modulos = $modulosEmpresa->map(function ($modulo) use ($telasPermitidasIds) {

            // telas do módulo
            $telasModulo = method_exists($modulo, 'telas')
                ? $modulo->telas()->where('ativo', 1)->orderBy('ordem')->get()
                : collect();

            // filtra pelas permitidas
            if ($telasPermitidasIds->isNotEmpty()) {
                $telasModulo = $telasModulo->whereIn('id', $telasPermitidasIds->toArray());
            } else {
                // se não achou permissão (por regra), não mostra nada
                $telasModulo = collect();
            }

            // separa sem grupo
            $telasSemGrupo = $telasModulo->filter(function ($t) {
                return empty($t->grupo_id);
            })->values();

            // agrupa por grupo
            $telasComGrupo = $telasModulo->filter(function ($t) {
                return !empty($t->grupo_id);
            });

            $grupos = $telasComGrupo
                ->groupBy('grupo_id')
                ->map(function ($telas, $grupoId) {

                    // tenta pegar infos do grupo pelo relacionamento (ideal)
                    $grupoModel = $telas->first()->grupo ?? null;

                    $nomeGrupo = $grupoModel->nome ?? ($telas->first()->grupo_nome ?? 'Grupo');
                    $iconeGrupo = $grupoModel->icone ?? ($telas->first()->grupo_icone ?? 'grid');

                    return [
                        'id' => $grupoId,
                        'nome' => $nomeGrupo,
                        'icone' => $iconeGrupo,
                        'telas' => $telas->values(),
                    ];
                })
                ->values();

            return (object)[
                'nome' => $modulo->nome ?? 'Módulo',
                'telas_sem_grupo' => $telasSemGrupo,
                'grupos' => $grupos,
            ];
        })->filter(function ($m) {
            // remove módulos vazios
            return $m->telas_sem_grupo->count() > 0 || $m->grupos->count() > 0;
        })->values();
    }
@endphp

<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar-->
    <section class="sidebar position-relative">
        <div class="multinav">
            <div class="multinav-scroll" style="height: 100%;">
                <!-- sidebar menu-->
                <ul class="sidebar-menu" data-widget="tree">

                    @foreach($modulos as $modulo)

                        {{-- HEADER DO MÓDULO --}}
                        <li class="header">{{ $modulo->nome }}</li>

                        {{-- TELAS SEM GRUPO --}}
                        @foreach($modulo->telas_sem_grupo as $tela)
                            <li title="{{ $tela->nome }}">
                                <a href="{{ $routeOrUrl($tela->slug ?? '#') }}">
                                    <i data-feather="{{ $tela->icone ?? 'circle' }}"></i>
                                    <span>{{ $tela->nome }}</span>
                                </a>
                            </li>
                        @endforeach

                        {{-- GRUPOS DE TELAS --}}
                        @foreach($modulo->grupos as $grupo)
                            <li class="treeview">
                                <a href="#">
                                    <i data-feather="{{ $grupo['icone'] ?? 'grid' }}"></i>
                                    <span>{{ $grupo['nome'] }}</span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-right pull-right"></i>
                                    </span>
                                </a>

                                <ul class="treeview-menu">
                                    @foreach($grupo['telas'] as $tela)
                                        <li>
                                            <a href="{{ $routeOrUrl($tela->slug ?? '#') }}">
                                                <i class="icon-Commit">
                                                    <span class="path1"></span><span class="path2"></span>
                                                </i>
                                                {{ $tela->nome }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach

                    @endforeach

                </ul>
            </div>
        </div>
    </section>
</aside>
