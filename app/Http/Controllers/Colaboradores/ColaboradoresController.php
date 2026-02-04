<?php

namespace App\Http\Controllers\Colaboradores;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColaboradoresController extends Controller
{
    /*
    |----------------------------------------------------------------------
    | LISTAGEM
    |----------------------------------------------------------------------
    */
    public function index(Request $request, string $sub)
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);

        $q    = trim((string) $request->get('q', ''));
        $ajax = (string) $request->get('ajax', '') === '1';

        $query = DB::table('colaboradores as c')
            ->select([
                'c.id',
                'c.nome',
                'c.cpf',
                'c.data_admissao',
            ])
            ->whereNull('c.deleted_at');

        // multi-tenant
        if ($empresaId > 0) {
            $query->where('c.empresa_id', $empresaId);
        }

        // filtro
        if ($q !== '') {
            $qDigits = preg_replace('/\D+/', '', $q);

            $query->where(function ($w) use ($q, $qDigits) {
                $w->whereRaw('LOWER(c.nome) LIKE ?', ['%' . mb_strtolower($q) . '%']);

                if ($qDigits !== '') {
                    // CPF armazenado sem máscara
                    $w->orWhere('c.cpf', 'like', '%' . $qDigits . '%');
                }
            });
        }

        $colaboradores = $query
            ->orderBy('c.nome')
            ->paginate(10)
            ->appends(['q' => $q]);

        // AJAX (partial)
        if ($ajax || $request->ajax()) {
            return view('colaboradores.partials.tabela', [
                'colaboradores' => $colaboradores,
            ]);
        }

        // Página completa
        return view('colaboradores.index', [
            'q' => $q,
            'colaboradores' => $colaboradores,
        ]);
    }
}
