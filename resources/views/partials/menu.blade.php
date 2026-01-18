@php
    use Illuminate\Support\Facades\DB;

    $empresa = app()->bound('tenant') ? app('tenant') : null;
    $user = auth()->user();

    // AJUSTE se o campo no seu User tiver outro nome:
    $permissaoId = $user->permissao_id ?? null;

    $routeOrUrl = function ($slug) {
        if (!$slug) return '#';
        if (preg_match('/^https?:\/\//i', $slug)) return $slug;
        if (str_starts_with($slug, '/')) return url($slug);
        return url($slug);
    };

    $schema = DB::getSchemaBuilder();

    $hasVinculoModuloAtivo = $schema->hasColumn('vinculo_modulos_empresas', 'ativo');
    $hasVinculoModuloOrdem = $schema->hasColumn('vinculo_modulos_empresas', 'ordem');

    $hasPMTAtivo = $schema->hasColumn('permissao_modulo_tela', 'ativo');

    $hasGrupoAtivo = $schema->hasColumn('grupos_telas', 'ativo');
    $hasGrupoOrdem = $schema->hasColumn('grupos_telas', 'ordem');

    $hasVinculoGrupoTelaAtivo = $schema->hasColumn('vinculo_grupos_telas_telas', 'ativo');
    $hasVinculoGrupoTelaOrdem = $schema->hasColumn('vinculo_grupos_telas_telas', 'ordem');

    $hasTelaOrdem = $schema->hasColumn('telas', 'ordem');
    $hasTelaAtivo = $schema->hasColumn('telas', 'ativo');

    $modulos = collect();

    if ($empresa && $permissaoId) {

        // =========================
        // 1) Módulos da empresa
        // =========================
        $modQ = DB::table('modulos')
            ->join('vinculo_modulos_empresas', 'vinculo_modulos_empresas.modulo_id', '=', 'modulos.id')
            ->where('vinculo_modulos_empresas.empresa_id', $empresa->id);

        if ($hasVinculoModuloAtivo) {
            $modQ->where('vinculo_modulos_empresas.ativo', true);
        }

        if ($hasVinculoModuloOrdem && $schema->hasColumn('modulos', 'ordem')) {
            $modQ->orderBy('vinculo_modulos_empresas.ordem', 'asc')
                 ->orderBy('modulos.ordem', 'asc');
        } elseif ($hasVinculoModuloOrdem) {
            $modQ->orderBy('vinculo_modulos_empresas.ordem', 'asc');
        } elseif ($schema->hasColumn('modulos', 'ordem')) {
            $modQ->orderBy('modulos.ordem', 'asc');
        } else {
            $modQ->orderBy('modulos.id', 'asc');
        }

        $modulosBase = collect($modQ->select('modulos.id', 'modulos.nome')->get());

        // =========================
        // 2) Para cada módulo, buscar:
        //    - grupos (treeview)
        //    - telas sem grupo (item simples)
        // =========================
        $modulos = $modulosBase->map(function ($m) use (
            $permissaoId,
            $hasPMTAtivo,
            $hasGrupoAtivo,
            $hasGrupoOrdem,
            $hasVinculoGrupoTelaAtivo,
            $hasVinculoGrupoTelaOrdem,
            $hasTelaOrdem,
            $hasTelaAtivo
        ) {

            // ---------------------------------------
            // A) Grupos do módulo com telas permitidas
            // ---------------------------------------
            $gruposQ = DB::table('grupos_telas')
                ->where('grupos_telas.modulo_id', $m->id);

            if ($hasGrupoAtivo) {
                $gruposQ->where('grupos_telas.ativo', true);
            }

            if ($hasGrupoOrdem) {
                $gruposQ->orderBy('grupos_telas.ordem', 'asc');
            } else {
                $gruposQ->orderBy('grupos_telas.id', 'asc');
            }

            $gruposDoModulo = collect($gruposQ->select('grupos_telas.id', 'grupos_telas.nome', 'grupos_telas.icone')->get())
                ->map(function ($g) use (
                    $permissaoId,
                    $hasPMTAtivo,
                    $hasVinculoGrupoTelaAtivo,
                    $hasVinculoGrupoTelaOrdem,
                    $hasTelaOrdem,
                    $hasTelaAtivo,
                    $m
                ) {

                    // Telas vinculadas a este grupo
                    $telasGrupoQ = DB::table('telas')
                        ->join('vinculo_grupos_telas_telas', 'vinculo_grupos_telas_telas.tela_id', '=', 'telas.id')
                        ->join('permissao_modulo_tela', 'permissao_modulo_tela.tela_id', '=', 'telas.id')
                        ->where('telas.modulo_id', $m->id)
                        ->where('vinculo_grupos_telas_telas.grupo_tela_id', $g->id)
                        ->where('permissao_modulo_tela.permissao_id', $permissaoId);

                    if ($hasVinculoGrupoTelaAtivo) {
                        $telasGrupoQ->where('vinculo_grupos_telas_telas.ativo', true);
                    }

                    if ($hasPMTAtivo) {
                        $telasGrupoQ->where('permissao_modulo_tela.ativo', true);
                    }

                    if ($hasTelaAtivo) {
                        $telasGrupoQ->where('telas.ativo', true);
                    }

                    // Ordem: primeiro ordem do vínculo grupo-tela, depois ordem da tela, depois id
                    if ($hasVinculoGrupoTelaOrdem) {
                        $telasGrupoQ->orderBy('vinculo_grupos_telas_telas.ordem', 'asc');
                    }
                    if ($hasTelaOrdem) {
                        $telasGrupoQ->orderBy('telas.ordem', 'asc');
                    }
                    $telasGrupoQ->orderBy('telas.id', 'asc');

                    $telas = collect($telasGrupoQ->select('telas.id', 'telas.nome_tela', 'telas.slug', 'telas.icone')->get())
                        ->map(function ($t) {
                            return (object)[
                                'id' => $t->id,
                                'nome' => $t->nome_tela,
                                'slug' => $t->slug,
                                'icone' => $t->icone ?: 'circle',
                            ];
                        });

                    return (object)[
                        'id' => $g->id,
                        'nome' => $g->nome ?? ('Grupo ' . $g->id),
                        'icone' => $g->icone ?: 'grid',
                        'telas' => $telas,
                    ];
                })
                // remove grupos sem telas visíveis
                ->filter(fn ($g) => $g->telas->count() > 0)
                ->values();

            // ---------------------------------------
            // B) Telas SEM grupo (item simples)
            // ---------------------------------------
            $telasSemGrupoQ = DB::table('telas')
                ->join('permissao_modulo_tela', 'permissao_modulo_tela.tela_id', '=', 'telas.id')
                ->where('telas.modulo_id', $m->id)
                ->where('permissao_modulo_tela.permissao_id', $permissaoId);

            if ($hasPMTAtivo) {
                $telasSemGrupoQ->where('permissao_modulo_tela.ativo', true);
            }

            if ($hasTelaAtivo) {
                $telasSemGrupoQ->where('telas.ativo', true);
            }

            // Excluir telas que estão em algum grupo
            $telasSemGrupoQ->whereNotIn('telas.id', function ($sub) use ($hasVinculoGrupoTelaAtivo) {
                $sub->select('tela_id')
                    ->from('vinculo_grupos_telas_telas');

                if ($hasVinculoGrupoTelaAtivo) {
                    $sub->where('ativo', true);
                }
            });

            if ($hasTelaOrdem) {
                $telasSemGrupoQ->orderBy('telas.ordem', 'asc');
            } else {
                $telasSemGrupoQ->orderBy('telas.id', 'asc');
            }

            $telasSemGrupo = collect($telasSemGrupoQ->select('telas.id', 'telas.nome_tela', 'telas.slug', 'telas.icone')->get())
                ->map(function ($t) {
                    return (object)[
                        'id' => $t->id,
                        'nome' => $t->nome_tela,
                        'slug' => $t->slug,
                        'icone' => $t->icone ?: 'circle',
                    ];
                });

            return (object)[
                'id' => $m->id,
                'nome' => $m->nome ?? ('Módulo ' . $m->id),
                'grupos' => $gruposDoModulo,
                'telas_sem_grupo' => $telasSemGrupo,
            ];
        })->filter(function ($m) {
            // só exibe módulo se tiver algo dentro
            return $m->grupos->count() > 0 || $m->telas_sem_grupo->count() > 0;
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

          {{-- DASHBOARD FIXO --}}
          <li title="Dashboard">
            <a href="{{ url('/') }}">
              <i data-feather="monitor"></i>
              <span>Dashboard</span>
            </a>
          </li>

          @if(!$empresa || !$permissaoId)
            <li class="header">Acesso</li>
            <li>
              <a href="#">
                <i data-feather="alert-triangle"></i>
                <span>Permissão do usuário não definida</span>
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

              {{-- GRUPOS (TREEVIEW) --}}
              @foreach($modulo->grupos as $grupo)
                <li class="treeview">
                  <a href="#">
                    <i data-feather="{{ $grupo->icone }}"></i>
                    <span>{{ $grupo->nome }}</span>
                    <span class="pull-right-container">
                      <i class="fa fa-angle-right pull-right"></i>
                    </span>
                  </a>

                  <ul class="treeview-menu">
                    @foreach($grupo->telas as $tela)
                      <li>
                        <a href="{{ $routeOrUrl($tela->slug) }}">
                          <i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>
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
