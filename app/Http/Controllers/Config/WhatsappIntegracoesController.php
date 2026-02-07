<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\WhatsappIntegracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class WhatsappIntegracoesController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function normalizeBaseUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') return null;

        // remove trailing slash
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * Tela de configuração (1 por empresa)
     */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $integracao = WhatsappIntegracao::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->first();

        // Observação: view será criada no próximo passo.
        return view('config.whatsapp_integracoes.index', [
            'sub'        => $sub,
            'integracao' => $integracao,
        ]);
    }

    /**
     * Salvar/Atualizar configuração
     */
    public function store(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $data = $request->all();

        $v = Validator::make($data, [
            'ativo'         => ['nullable'],
            'base_url'      => ['required', 'string', 'max:255'],
            'api_key'       => ['required', 'string', 'max:255'],
            'instance_name' => ['nullable', 'string', 'max:150'],
        ], [
            'base_url.required' => 'Informe a URL base do Evolution.',
            'api_key.required'  => 'Informe a API KEY do Evolution.',
        ]);

        if ($v->fails()) {
            return redirect()
                ->back()
                ->withErrors($v)
                ->withInput();
        }

        $baseUrl = $this->normalizeBaseUrl($data['base_url'] ?? null);
        $ativo   = isset($data['ativo']) ? (bool)$data['ativo'] : true;

        $integracao = WhatsappIntegracao::query()
            ->firstOrNew(['empresa_id' => $empresaId]);

        $integracao->fill([
            'provider'       => 'evolution',
            'base_url'       => $baseUrl,
            'api_key'        => $data['api_key'], // Model criptografa
            'instance_name'  => trim((string)($data['instance_name'] ?? '')) ?: null,
            'ativo'          => $ativo,
        ]);

        $integracao->save();

        return redirect()
            ->route('config.whatsapp_integracoes', ['sub' => $sub])
            ->with('success', 'Configuração do WhatsApp salva com sucesso.');
    }

    /**
     * Testar conexão com Evolution (AJAX)
     * Faz um GET na base_url e valida retorno 200 e JSON esperado.
     */
    public function testConnection(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $integracao = WhatsappIntegracao::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at')
            ->first();

        if (!$integracao || !$integracao->base_url || !$integracao->getApiKeyPlain()) {
            return response()->json([
                'ok'      => false,
                'message' => 'Configuração incompleta. Informe Base URL e API KEY.',
            ], 422);
        }

        $url = rtrim($integracao->base_url, '/') . '/';

        try {
            $res = Http::timeout(10)
                ->withHeaders([
                    'apikey' => $integracao->getApiKeyPlain(),
                    'Accept' => 'application/json',
                ])
                ->get($url);

            $status = $res->status();
            $json   = null;

            try { $json = $res->json(); } catch (\Throwable $e) { $json = null; }

            if ($status === 200 && is_array($json) && ($json['status'] ?? null) == 200) {
                // Atualiza status básico (opcional)
                $integracao->status = 'connected';
                $integracao->save();

                return response()->json([
                    'ok'      => true,
                    'message' => 'Conexão OK com Evolution.',
                    'data'    => [
                        'version'     => $json['version'] ?? null,
                        'clientName'  => $json['clientName'] ?? null,
                        'manager'     => $json['manager'] ?? null,
                        'documentation' => $json['documentation'] ?? null,
                    ],
                ]);
            }

            return response()->json([
                'ok'      => false,
                'message' => 'Falha ao validar Evolution. Status HTTP: ' . $status,
                'body'    => $res->body(),
            ], 400);

        } catch (\Throwable $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Erro ao conectar no Evolution: ' . $e->getMessage(),
            ], 500);
        }
    }
}
