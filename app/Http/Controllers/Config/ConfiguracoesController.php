<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ConfiguracoesController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function evolutionBaseUrl(): string
    {
        // base fixa
        return rtrim(config('services.evolution.base_url', env('EVOLUTION_BASE_URL', 'https://evolution.conecttarh.com.br')), '/');
    }

    /**
     * Chave ADMIN da Evolution (para criar instância / gerar QR).
     * (não é o apikey da instância)
     */
    private function evolutionAdminKey(): string
    {
        return (string) env('EVOLUTION_ADMIN_API_KEY', '');
    }

    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')
            ->select([
                'id',
                'nome_fantasia',
                'razao_social',
                'wa_instance_id',
                'wa_instance_name',
                'wa_api_key',
                'wa_phone',
                'wa_connection_state',
                'wa_qrcode_base64',
                'updated_at',
            ])
            ->where('id', $empresaId)
            ->first();

        return view('config.configuracoes.index', [
            'empresa' => $empresa,
        ]);
    }

    /**
     * Endpoint usado pelo JS (poll) para saber se conectou.
     * REGRA: se existe QR no banco => NÃO está conectado.
     */
    public function whatsappStatus(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')
            ->select([
                'id',
                'wa_instance_name',
                'wa_instance_id',
                'wa_phone',
                'wa_connection_state',
                'wa_qrcode_base64',
            ])
            ->where('id', $empresaId)
            ->first();

        if (!$empresa || empty($empresa->wa_instance_name)) {
            return response()->json([
                'ok' => true,
                'hasInstance' => false,
            ]);
        }

        $baseUrl = $this->evolutionBaseUrl();
        $adminKey = $this->evolutionAdminKey();

        // Se existe QR salvo no banco, consideramos "pendente de conexão"
        $qrFromDb = (string) ($empresa->wa_qrcode_base64 ?? '');
        $needsQr = $qrFromDb !== '';

        $state = null;

        try {
            // Connection State (documentação oficial)
            // GET /instance/connectionState/{instance}
            $resp = Http::withHeaders([
                    'apikey' => $adminKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(15)
                ->get($baseUrl . '/instance/connectionState/' . $empresa->wa_instance_name);

            if ($resp->ok()) {
                $json = $resp->json();
                $state = data_get($json, 'instance.state');
            }
        } catch (\Throwable $e) {
            // mantém fallback abaixo
        }

        // Fallback: se não conseguiu buscar no Evolution, usa o que está no banco
        if (!$state) {
            $state = (string) ($empresa->wa_connection_state ?? '');
        }

        /**
         * ✅ Regra final de "connected":
         * - state precisa ser "open"
         * - E não pode existir QR pendente no banco
         */
        $connected = ($state === 'open') && !$needsQr;

        // Se conectou de verdade, limpa QR e atualiza estado
        if ($connected) {
            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_connection_state' => $state,
                    'wa_qrcode_base64'    => null,
                    'updated_at'          => now(),
                ]);
        } else {
            // Atualiza estado no banco, mas mantém QR se existir
            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_connection_state' => $state,
                    'updated_at'          => now(),
                ]);
        }

        return response()->json([
            'ok' => true,
            'hasInstance' => true,
            'connected' => $connected,
            'state' => $state,
            'needsQr' => !$connected, // se não conectado, consideramos que precisa de ação (QR)
            'qrCode' => $connected ? null : ($qrFromDb ?: null),
        ]);
    }

    /**
     * Cria instância no Evolution e salva no banco (sem mostrar token na tela).
     */
    public function criarInstancia(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')
            ->select(['id', 'nome_fantasia', 'razao_social', 'wa_instance_name'])
            ->where('id', $empresaId)
            ->first();

        if (!$empresa) {
            return back()->with('error', 'Empresa não encontrada.');
        }

        if (!empty($empresa->wa_instance_name)) {
            return back()->with('info', 'Esta empresa já possui instância criada.');
        }

        $instanceName = trim((string) ($empresa->nome_fantasia ?: $empresa->razao_social ?: ('empresa_' . $empresaId)));
        $instanceName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $instanceName);

        $baseUrl = $this->evolutionBaseUrl();
        $adminKey = $this->evolutionAdminKey();

        try {
            // Create Instance (admin key)
            // Obs: payload pode variar por versão — aqui usamos o mínimo típico
            $resp = Http::withHeaders([
                    'apikey' => $adminKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post($baseUrl . '/instance/create', [
                    'instanceName' => $instanceName,
                    'qrcode'       => true,
                ]);

            if (!$resp->ok()) {
                return back()->with('error', 'Falha ao criar instância no Evolution: ' . $resp->body());
            }

            $json = $resp->json();

            $instanceId = data_get($json, 'instance.instanceId')
                ?: data_get($json, 'instanceId')
                ?: null;

            $apiKey = data_get($json, 'instance.apikey')
                ?: data_get($json, 'apikey')
                ?: null;

            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_instance_name'     => $instanceName,
                    'wa_instance_id'       => $instanceId,
                    'wa_api_key'           => $apiKey,
                    'wa_connection_state'  => 'created',
                    'wa_qrcode_base64'     => null,
                    'updated_at'           => now(),
                ]);

            return back()->with('success', 'Instância criada com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao criar instância: ' . $e->getMessage());
        }
    }

    /**
     * Gera/atualiza QRCode no Evolution e salva no banco.
     */
    public function gerarQrCode(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')
            ->select(['id', 'wa_instance_name'])
            ->where('id', $empresaId)
            ->first();

        if (!$empresa || empty($empresa->wa_instance_name)) {
            return back()->with('error', 'Instância não encontrada para esta empresa.');
        }

        $baseUrl = $this->evolutionBaseUrl();
        $adminKey = $this->evolutionAdminKey();

        try {
            // Instance Connect (gera QR / pairing code dependendo da config)
            $resp = Http::withHeaders([
                    'apikey' => $adminKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->get($baseUrl . '/instance/connect/' . $empresa->wa_instance_name);

            if (!$resp->ok()) {
                return back()->with('error', 'Falha ao gerar QRCode: ' . $resp->body());
            }

            $json = $resp->json();

            // Em muitas instalações o retorno do "connect" traz um code/qr (varia por versão)
            // Aqui suportamos os nomes mais comuns:
            $qr = data_get($json, 'base64')
                ?: data_get($json, 'qrcode')
                ?: data_get($json, 'qr')
                ?: data_get($json, 'code')
                ?: null;

            if (!$qr) {
                // Mesmo sem qr, guardamos estado e avisamos
                DB::table('empresas')
                    ->where('id', $empresaId)
                    ->update([
                        'wa_connection_state' => 'waiting_qr',
                        'updated_at'          => now(),
                    ]);

                return back()->with('info', 'Evolution não retornou QRCode no momento. Tente novamente.');
            }

            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_qrcode_base64'     => $qr,
                    'wa_connection_state'  => 'waiting_qr',
                    'updated_at'           => now(),
                ]);

            return back()->with('success', 'QRCode atualizado! Escaneie com o WhatsApp.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao gerar QRCode: ' . $e->getMessage());
        }
    }
}
