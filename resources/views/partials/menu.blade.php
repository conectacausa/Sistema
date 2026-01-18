@php
    use Illuminate\Support\Facades\DB;

    // Empresa/Tenant atual (middleware)
    $empresa = app()->bound('tenant') ? app('tenant') : null;

    // Usuário logado
    $user = auth()->user();

    /**
     * IMPORTANTE:
     * Pelo print, sua permissão está na tabela "permissoes" (grupo Admin).
     * Você precisa ter o ID da permissão no usuário.
     *
     * Ajuste aqui se no seu User o campo tiver outro nome:
     * ex.: $user->permissao_id, $user->grupo_id, $user->permissoes_id etc.
     */
    $permissaoId = $user->permissao_id ?? null;

    // Helper para link
    $routeOrUrl = function ($slug) {
        if (!$slug) return '#';
        if (preg_match('/^https?:\/\//i', $slug)) return $slug;
        if (str_starts_with($slug, '/')) return url($slug);
        return url($slug);
    };

    // Detecta colunas (pra não quebrar quando não existir)
    $schema = DB::getSchemaBuilder();
    $telasHasGrupoId = $schema->hasColumn('telas', 'grupo_id');
    $telasHasAtivo   = $schema->hasColumn('telas', 'ativo');
    $telasHasOrdem   = $schema->hasColumn('telas', 'ordem');

    $vinculoHasAtivo = $schema->hasColumn('vinculo_modulos_empresas', 'ativo');
    $vinculoHasOrdem = $schema->hasColumn('vinculo_modulos_empresas', 'ordem');

    $pmtHasAtivo     = $schema->hasColumn('permissao_modulo_tela', 'ativo');

    // Carrega módulos + telas permitidas
    $modulos = collect();

    if ($empresa && $permissaoId) {

        // 1) módulos liberados para a empresa
        $modulosBase = DB::table('modulos')
            ->join('vinculo_modulos_empresas', 'vinculo_modulos_empresas.modulo_id', '=', 'modulos.id')
            ->where('vinculo_modulos_empresas.empresa_id', $empresa->id);

        if ($vinculoHasAtivo) {
            $modulosBase->where('vinculo_modulos_empresas.ativo', true);
        }

        // ordenação (se não tiver ordem, ordena por id)
        if ($vinculoHasOrdem) {
            $modulosBase->orderBy('vinculo_modulos_empresas.ordem', 'asc');
        } else {
            $modulosBase->orderBy('modulos.id', 'asc');
        }

        // Seleção segura (modulos agora tem nome, mas se algum ambiente ainda não tiver, evita quebrar)
        $modulosEmpresa = $modulosBase->select('modulos.id', 'modulos.nome')->get();

        $modulos = collect($modulosEmpresa)->map(function ($m) use (
            $permissaoId,
            $telasHasGrupoId,
            $telasHasAtivo,
            $telasHasOrdem,
            $pmtHasAtivo
        ) {

            // 2) telas do módulo que o usuário tem permissão
            $telasQ = DB::table('telas')
                ->join('permissao_modulo_tela', 'permissao_modulo_tela.tela_id', '=', 'telas.id')
                ->where('permissao_modulo_tela.permissao_id', $permissaoId)
                ->where('telas.modulo_id', $m->id);

            if ($pmtHasAtivo) {
                $telasQ->where('permissao_modulo_tela.ativo', true);
            }
            if ($telasHasAtivo) {
                $telasQ->where('telas.ativo', true);
            }
            if ($telasHasOrdem) {
                $telasQ->orderBy('telas.ordem', 'asc');
            } else {
                $telasQ->orderBy('telas.id', 'asc');
            }

            // Campos reais do seu print:
            // - nome_tela
            // - slug
            // - icone (pode ser null)
            $select = [
                'telas.id',
                'telas.nome_tela',
                'telas.slug',
                'telas.icone',
            ];

            if ($telasHasGrupoId) {
                $select[] = 'telas.grupo_id';
            }

            $telas = collect($telasQ->select($select)->get())->map(function ($t) use ($telasHasGrupoId) {
                return (object)[
                    'id' => $t->id,
                    'nome' => $t->nome_tela,          // <-- corrigido
                    'slug' => $t->slug,
                    'icone' => $t->icone ?: 'circle', // fallback de ícone
                    'grupo_id' => $telasHasGrupoId ? ($t->grupo_id ?? null) : null,
                ];
            });

            // Por enquanto, se você ainda não tem grupos, tudo fica sem grupo
            $telasSemGrupo = $telas->filter(fn ($t) => empty($t->grupo_id))->values();

            $grupos = collect();
            if ($telasHasGrupoId) {
                $grupos = $telas->filter(fn ($t) => !empty($t->grupo_id))
                    ->groupBy('grupo_id')
                    ->map(function ($telasDoGrupo, $grupoId) {
                        // Se você criar tabela grupos_telas no futuro, dá pra buscar nome/icone aqui
                        return [
                            'id' => $grupoId,
                            'nome' => 'Grupo ' . $grupoId,
                            'icone' => 'grid',
                            'telas' => collect($telasDoGrupo)->values(),
                        ];
                    })
                    ->values();
            }

            return (object)[
                'id' => $m->id,
                'nome' => $m->nome ?? ('Módulo ' . $m->id),
                'telas_sem_grupo' => $telasSemGrupo,
                'grupos' => $grupos,
            ];
        })->filter(function ($m) {
            // só mostra módulos que tenham pelo menos 1 tela
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

          {{-- DASHBOARD (sempre antes do primeiro módulo) --}}
          <li title="Dashboard">
            <a href="{{ url('/') }}">
              <i data-feather="monitor"></i>
              <span>Dashboard</span>
            </a>
          </li>

          {{-- Se não houver empresa/permissão, mostra aviso --}}
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

              {{-- Header do módulo --}}
              <li class="header">{{ $modulo->nome }}</li>

              {{-- Telas sem grupo --}}
              @foreach($modulo->telas_sem_grupo as $tela)
                <li title="{{ $tela->nome }}">
                  <a href="{{ $routeOrUrl($tela->slug) }}">
                    <i data-feather="{{ $tela->icone }}"></i>
                    <span>{{ $tela->nome }}</span>
                  </a>
                </li>
              @endforeach

              {{-- Grupos (só se existir grupo_id em telas) --}}
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
