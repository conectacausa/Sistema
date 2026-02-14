<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransporteBaseController extends Controller
{
    protected function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    protected function userId(): int
    {
        return (int) (auth()->user()->id ?? 0);
    }

    /**
     * Permissão por tela (tolerante a nomes de tabelas).
     * Se não conseguir identificar a estrutura, retorna false (seguro).
     */
    protected function canAccessTela(int $telaId): bool
    {
        $userId = $this->userId();
        $empresaId = $this->empresaId();

        if ($userId <= 0 || $empresaId <= 0) return false;

        // 1) Tentativa: usuário tem grupo_permissao_id direto
        $grupoId = (int) (auth()->user()->grupo_permissao_id ?? 0);

        // 2) Tentativa: tabela de vínculo usuario->grupo
        if ($grupoId <= 0) {
            $candidates = [
                ['table' => 'vinculo_usuario_grupo_permissao', 'user_col' => 'usuario_id', 'group_col' => 'grupo_permissao_id'],
                ['table' => 'vinculo_usuarios_grupos_permissao', 'user_col' => 'usuario_id', 'group_col' => 'grupo_permissao_id'],
            ];

            foreach ($candidates as $c) {
                if (Schema::hasTable($c['table'])) {
                    $row = DB::table($c['table'])
                        ->where($c['user_col'], $userId)
                        ->first();
                    if ($row && isset($row->{$c['group_col']})) {
                        $grupoId = (int) $row->{$c['group_col']};
                        break;
                    }
                }
            }
        }

        if ($grupoId <= 0) return false;

        // 3) Tentativa: vínculo grupo -> tela
        $pivotCandidates = [
            ['table' => 'vinculo_grupos_permissao_telas', 'group_col' => 'grupo_permissao_id', 'tela_col' => 'tela_id'],
            ['table' => 'vinculo_grupo_permissao_telas',  'group_col' => 'grupo_permissao_id', 'tela_col' => 'tela_id'],
            ['table' => 'grupo_permissao_telas',          'group_col' => 'grupo_permissao_id', 'tela_col' => 'tela_id'],
        ];

        foreach ($pivotCandidates as $p) {
            if (!Schema::hasTable($p['table'])) continue;

            $q = DB::table($p['table'])
                ->where($p['group_col'], $grupoId)
                ->where($p['tela_col'], $telaId);

            return $q->exists();
        }

        return false;
    }

    protected function requireTela(Request $request, string $sub, int $telaId)
    {
        if (!$this->canAccessTela($telaId)) {
            return redirect()->route('dashboard', ['sub' => $sub]);
        }
        return null;
    }
}
