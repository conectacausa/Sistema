<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\WhatsappIntegracao;
use Illuminate\Http\Request;

class ConfiguracoesController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    /**
     * Página central de configurações (/config) - Tela ID 15
     */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $integracaoWhatsapp = WhatsappIntegracao::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->first();

        return view('config.index', [
            'sub' => $sub,
            'integracaoWhatsapp' => $integracaoWhatsapp,
            'telaId' => 15,
        ]);
    }
}
