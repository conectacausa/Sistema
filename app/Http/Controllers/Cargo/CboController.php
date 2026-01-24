<?php

namespace App\Http\Controllers\Cargo;

use App\Http\Controllers\Controller;
use App\Models\Cbo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CboController extends Controller
{
    private int $telaId = 6;

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $cbos = Cbo::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('cbo', 'ilike', "%{$q}%")
                      ->orWhere('titulo', 'ilike', "%{$q}%");
            })
            ->orderBy('titulo')
            ->paginate(50)
            ->withQueryString();

        $podeCadastrar = $this->podeCadastrar(Auth::user()?->permissao_id);

        // AJAX do filtro/paginação: retorna só a tabela
        if ($request->boolean('ajax')) {
            return view('cargos.cbo._table', compact('cbos'))->render();
        }

        return view('cargos.cbo.index', compact('cbos', 'podeCadastrar'));
    }

    public function create()
    {
        $user = Auth::user();

        // Se não tem permissão de cadastro, volta para lista
        if (!$this->podeCadastrar($user?->permissao_id)) {
            return redirect()->route('cargos.cbo.index');
        }

        return view('cargos.cbo.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Se não tem permissão de cadastro, volta para lista
        if (!$this->podeCadastrar($user?->permissao_id)) {
            return redirect()->route('cargos.cbo.index');
        }

        $data = $request->validate([
            'cbo'       => ['required', 'string', 'max:10'],
            'titulo'    => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
        ], [
            'cbo.required'    => 'Informe o código do CBO.',
            'titulo.required' => 'Informe o título.',
        ]);

        // Normaliza o código (opcional, mas ajuda)
        $data['cbo'] = trim($data['cbo']);

        // Checa duplicidade
        $exists = Cbo::query()->where('cbo', $data['cbo'])->exists();
        if ($exists) {
            return back()
                ->withErrors(['cbo' => 'Este CBO já existe. Verifique o código.'])
                ->withInput();
        }

        Cbo::create([
            'cbo'       => $data['cbo'],
            'titulo'    => $data['titulo'],
            'descricao' => $data['descricao'] ?? null,
            // 'validacao' => null,  // se quiser usar depois
        ]);

        return redirect()->route('cargos.cbo.index');
    }

    public function checkCodigo(Request $request)
    {
        $user = Auth::user();

        if (!$this->podeCadastrar($user?->permissao_id)) {
            return response()->json([
                'allowed' => false,
                'exists'  => false,
                'message' => 'Sem autorização para cadastro.'
            ], 403);
        }

        $cbo = trim((string) $request->query('cbo', ''));
        if ($cbo === '') {
            return response()->json(['allowed' => true, 'exists' => false]);
        }

        $exists = Cbo::query()->where('cbo', $cbo)->exists();

        return response()->json([
            'allowed' => true,
            'exists'  => $exists,
            'message' => $exists ? 'Este CBO já existe. Verifique o código.' : null,
        ]);
    }

    private function podeCadastrar(?int $permissaoId): bool
    {
        $permissaoId = (int) ($permissaoId ?? 0);
        if ($permissaoId <= 0) return false;

        return DB::table('permissao_modulo_tela')
            ->where('permissao_id', $permissaoId)
            ->where('tela_id', $this->telaId)
            ->where('ativo', true)
            ->where('cadastro', true)
            ->exists();
    }
}
