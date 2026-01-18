@php
    use Illuminate\Support\Facades\DB;

    // Empresa/Tenant atual (middleware)
    $empresa = app()->bound('tenant') ? app('tenant') : null;

    // Usuário logado
    $user = auth()->user();

    // >>> AJUSTE SE NECESSÁRIO: de onde vem a permissão do usuário?
    // Se seu user tiver outro campo, troque aqui.
    $permissaoId = $user->permissao_id ?? null;

    // Helpers
    $routeOrUrl = function ($slug) {
        if (!$slug) return '#';
        if (preg_match('/^https?:\/\//i', $slug)) return $slug;
        if (str_starts_with($slug, '/')) return url($slug);
        return url($slug);
    };

    // Carrega módulos liberados para a empresa
    $modulos = collect();

    if ($empresa && $permissaoId) {

        // 1) módulos que a empresa tem acesso
        $modulos = DB::table('modulos')
            ->join('vinculo_modulos_empresas', 'vinculo_modulos_empresas.modulo_id', '=', 'modulos.id')
            ->where('vinculo_modulos_empresas.empresa_id', $empresa->id)
            ->where(function ($q) {
                // se existir a coluna ativo no vínculo
                $q->where('vinculo_modulos_empresas.ativo', true)->orWhereNull('vinculo_modulos_empresas.ativo');
            })
            ->orderByRaw('COALESCE(vinculo_modulos_empresas.ordem, modulos.ordem, 0) asc')
            ->select(
                'modulos.id',
                'modulos.nome',
                DB::raw('COALESCE(modulos.icone, \'grid\') as icone')
            )
            ->get();

        $modulos = collect($modulos)->map(function ($m) use ($permissaoId) {

            // 2) telas do módulo que o usuário tem permissão
            // Permissão -> Tela: permissao_modulo_tela(permissao_id, tela_id, ativo)
            $telas = DB::table('telas')
                ->join('permissao_modulo_tela', 'permissao_modulo_tela.tela_id', '=', 'telas.id')
                ->where('permissao_modulo_tela.permissao_id', $permissaoId)
                ->where(function ($q) {
                    // se existir a coluna ativo no vínculo
                    $q->where('permissao_modulo_tela.ativo', true)->orWhereNull('permissao_modulo_tela.ativo');
                })
                ->where('telas.modulo_id', $m->id)
                ->where(function ($q) {
                    // se existir telas.ativo
                    $q->where('telas.ativo', true)->orWhereNull('telas.ativo');
                })
                ->orderByRaw('COALESCE(telas.ordem, 0) asc')
                ->select(
                    'telas.id',
                    'telas.nome',
                    'telas.slug',
                    DB::raw('COALESCE(telas.icone, \'circle\') as icone'),
                    'telas.grupo_id'
                )
                ->get();

            $telas = collect($telas);

            // Telas sem grupo
            $telasSemGrupo = $telas->filter(fn ($t) => empty($t->grupo_id))->values();

            // Telas com grupo
            $grupos = $telas->filter(fn ($t) => !empty($t->grupo_id))
                ->groupBy('grupo_id')
                ->map(function ($telasDoGrupo, $grupoId) {
                    // Aqui você tem 2 cenários:
                    // A) existe tabela grupos_telas com nome/icone
                    // B) não existe -> mostramos "Grupo {id}" e ícone padrão
                    $grupo = null;
                    if (DB::getSchemaBuilder()->hasTable('grupos_telas')) {
                        $grupo = DB::table('grupos_telas')->where('id', $grupoId)->first();
                    }

                    return [
                        'id' => $grupoId,
                        'nome' => $grupo->nome ?? ("Grupo " . $grupoId),
                        'icone' => $grupo->icone ?? 'grid',
                        'telas' => collect($telasDoGrupo)->values(),
                    ];
                })
                ->values();

            return (object) [
                'id' => $m->id,
                'nome' => $m->nome ?? 'Módulo',
                'icone' => $m->icone ?? 'grid',
                'telas_sem_grupo' => $telasSemGrupo,
                'grupos' => $grupos,
            ];
        })->filter(function ($m) {
            // Só mostra módulo que tem pelo menos 1 tela visível
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

                    {{-- DASHBOARD (SEMPRE ANTES DO PRIMEIRO MÓDULO) --}}
                    <li title="Dashboard">
                        <a href="{{ url('/') }}">
                            <i data-feather="monitor"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    {{-- Se não houver empresa/permissão, não renderiza módulos --}}
                    @if(!$empresa || !$permissaoId)
                        <li class="header">Acesso</li>
                        <li>
                            <a href="#">
                                <i data-feather="alert-triangle"></i>
                                <span>Sem permissão configurada</span>
                            </a>
                        </li>
                    @else

                        @foreach($modulos as $modulo)

                            {{-- HEADER DO MÓDULO --}}
                            <li class="header">{{ $modulo->nome }}</li>

                            {{-- TELAS SEM GRUPO --}}
                            @foreach($modulo->telas_sem_grupo as $tela)
                                <li title="{{ $tela->nome }}">
                                    <a href="{{ $routeOrUrl($tela->slug) }}">
                                        <i data-feather="{{ $tela->icone }}"></i>
                                        <span>{{ $tela->nome }}</span>
                                    </a>
                                </li>
                            @endforeach

                            {{-- GRUPOS --}}
                            @foreach($modulo->grupos as $grupo)
                                <li class="treeview">
                                    <a href="#">
                                        <i data-feather="{{ $grupo['icone'] }}"></i>
                                        <span>{{ $grupo['nome'] }}</span>
                                        <span class="pull-right-container">
                                            <i class="fa fa-angle-right pull-right"></i>
                                        </span>
                                    </a>

                                    <ul class="treeview-menu">
                                        @foreach($grupo['telas'] as $tela)
                                            <li>
                                                <a href="{{ $routeOrUrl($tela->slug) }}">
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

                        @if($modulos->count() === 0)
                            <li class="header">Módulos</li>
                            <li>
                                <a href="#">
                                    <i data-feather="info"></i>
                                    <span>Nenhuma tela liberada para sua permissão</span>
                                </a>
                            </li>
                        @endif

                    @endif

                </ul>
            </div>
        </div>
    </section>
</aside>
