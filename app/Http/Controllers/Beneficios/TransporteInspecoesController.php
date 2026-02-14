<?php

namespace App\Http\Controllers\Beneficios;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransporteInspecoesController extends Controller
{
    private const T_INSPECOES = 'transporte_inspecoes';
    private const T_VEICULOS  = 'transporte_veiculos';

    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function now()
    {
        return now();
    }

    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $inspecoes = DB::table(self::T_INSPECOES . ' as i')
            ->leftJoin(self::T_VEICULOS . ' as v', 'v.id', '=', 'i.veiculo_id')
            ->select('i.*', 'v.placa as veiculo_placa', 'v.modelo as veiculo_modelo')
            ->where('i.empresa_id', $empresaId)
            ->whereNull('i.deleted_at')
            ->orderBy('i.id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('beneficios.transporte.inspecoes.index', compact('sub', 'inspecoes'));
    }

    public function create(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $veiculos = DB::table(self::T_VEICULOS)
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->orderBy('modelo')
            ->get();

        return view('beneficios.transporte.inspecoes.create', compact('sub', 'veiculos'));
    }

    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        // ✅ Campos mínimos (você pode expandir conforme o formulário do anexo)
        $v = Validator::make($request->all(), [
            'veiculo_id'    => 'required|integer|min:1',
            'data'          => 'required|date',
            'aprovado'      => 'required|boolean',
            'observacoes'   => 'nullable|string',
            'payload'       => 'nullable|array', // se sua view mandar campos do checklist em array
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $id = DB::table(self::T_INSPECOES)->insertGetId([
            'empresa_id'   => $empresaId,
            'veiculo_id'   => (int) $request->get('veiculo_id'),
            'data'         => $request->get('data'),
            'aprovado'     => (bool) $request->boolean('aprovado'),
            'observacoes'  => $request->get('observacoes'),
            'payload'      => $request->has('payload') ? json_encode($request->get('payload')) : null,
            'created_at'   => $this->now(),
            'updated_at'   => $this->now(),
        ]);

        return redirect()->route('beneficios.transporte.inspecoes.show', ['sub' => $sub, 'id' => $id])
            ->with('success', 'Inspeção registrada.');
    }

    public function show(Request $request, string $sub, int $id)
    {
        $empresaId = $this->empresaId();

        $inspecao = DB::table(self::T_INSPECOES . ' as i')
            ->leftJoin(self::T_VEICULOS . ' as v', 'v.id', '=', 'i.veiculo_id')
            ->select('i.*', 'v.placa as veiculo_placa', 'v.modelo as veiculo_modelo')
            ->where('i.empresa_id', $empresaId)
            ->where('i.id', $id)
            ->whereNull('i.deleted_at')
            ->first();

        abort_unless($inspecao, 404);

        // decodifica payload (se existir)
        $payload = [];
        if (!empty($inspecao->payload)) {
            $payload = json_decode($inspecao->payload, true) ?: [];
        }

        return view('beneficios.transporte.inspecoes.show', compact('sub', 'inspecao', 'payload'));
    }
}
