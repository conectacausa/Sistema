<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ConfiguracoesController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function evolutionBaseUrl(): string
    {
        // Use EVOLUTION_BASE_URL no .env do Laravel.
        // Para evitar 401 do Basic Auth do Nginx, use: http://127.0.0.1:8080
        return rtrim((string) config('services.evolution.base_url'), '/');
    }

    private function evolutionGlobalApikey(): string
    {
        // Use EVOLUTION_GLOBAL_APIKEY no .env do Laravel (apikey global do Evolution)
        return (string) config('services.evolution.global_apikey');
    }

    private function evolutionHeaders(): array
    {
        return [
            'apikey' => $this->evolutionGlobalApikey(),
            'Accept' => 'application/json',
        ];
    }

    private function makeInstanceName(string $empresaNome, int $empresaId): string
    {
        $base = trim($empresaNome) !== '' ? $empresaNome : ('empresa_' . $empresaId);
        $slug = Str::slug($base, '_');
        $slug = $slug ?: ('empresa_' . $empresaId);

        // garante unicidade por empresa, sem depender só do nome
        $slug = $slug . '_' . $empresaId;

        return Str::limit($slug, 50, '');
    }

    private function fetchConnectionState(string $instanceName): ?array
    {
        try {
            $resp = Http::timeout(12)
                ->withHeaders($this->evolutionHeaders())
                ->get($this->evolutionBaseUrl() . '/instance/connectionState/' . urlencode($instanceName));

            if (!$resp->successful()) return null;
            return $resp->json();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Tenta obter o "code" do /connect, que indica necessidade de pareamento/QR.
     * Retorna string vazia se não houver code.
     */
    private function fetchConnectCode(string $instanceName, ?string $telefone = null): ?string
    {
        try {
            $url = $this->evolutionBaseUrl() . '/instance/connect/' . urlencode($instanceName);

            $telefone = preg_replace('/\D+/', '', (string) $telefone);
            if ($telefone) {
                $url .= '?number=' . urlencode($telefone);
            }

            $resp = Http::timeout(12)
                ->withHeaders($this->evolutionHeaders())
                ->get($url);

            if (!$resp->successful()) return null;

            $data = $resp->json();
            $code = (string) (data_get($data, 'code') ?? '');

            return $code;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Página central de configurações (/config) - Tela ID 15
     */
    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();

        // Se já existe instância, busca status na carga da página (best-effort)
        if ($empresa && !empty($empresa->wa_instance_name)) {
            $instanceName = (string) $empresa->wa_instance_name;

            $statePayload = $this->fetchConnectionState($instanceName);
            $state = data_get($statePayload, 'instance.state');

            // Se /connect retornar code, significa que ainda precisa parear
            $code = $this->fetchConnectCode($instanceName, $empresa->wa_phone ?? null);

            $needsQr = is_string($code) && $code !== '';
            $connected = ($state === 'open') && !$needsQr;

            // Guardamos o code para a tela renderizar o QR
            if ($needsQr) {
                DB::table('empresas')->where('id', $empresaId)->update([
                    'wa_qrcode_base64' => $code,
                ]);
                $empresa->wa_qrcode_base64 = $code;
            }

            DB::table('empresas')->where('id', $empresaId)->update([
                'wa_connection_state' => $connected ? 'open' : ($needsQr ? 'created' : ($state ?: null)),
                'wa_connected_at'     => $connected ? now() : null,
            ]);

            $empresa->wa_connection_state = $connected ? 'open' : ($needsQr ? 'created' : ($state ?: null));
        }

        return view('config.configuracoes.index', [
            'sub'     => $sub,
            'empresa' => $empresa,
        ]);
    }

    /**
     * Cria instância no Evolution para esta empresa (1 por empresa).
     * - Base URL é fixa (config/services.php)
     * - Token gerado automaticamente (hash.apikey) e salvo no banco (não exibir na tela)
     * - instanceName = nome da empresa (slug) + _{empresa_id} (para evitar colisão)
     */
    public function whatsappCriarInstancia(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();
        if (!$empresa) {
            return back()->with('error', 'Empresa não encontrada.');
        }

        // Já existe instância? não cria novamente
        if (!empty($empresa->wa_instance_name) && !empty($empresa->wa_instance_id)) {
            return back()->with('info', 'Esta empresa já possui uma instância criada.');
        }

        $telefone = preg_replace('/\D+/', '', (string) $request->input('wa_phone', ''));

        $empresaNome = (string) ($empresa->nome_fantasia ?? $empresa->nome ?? $empresa->razao_social ?? 'Empresa');
        $instanceName = $this->makeInstanceName($empresaNome, $empresaId);

        $payload = [
            'instanceName' => $instanceName,
            'integration'  => 'WHATSAPP-BAILEYS',
            // token vazio => Evolution cria dinamicamente e retorna hash.apikey
            'token'        => '',
            'qrcode'       => true,
            // number é opcional; enviamos se tiver
            'number'       => $telefone ?: '',
        ];

        try {
            $resp = Http::timeout(20)
                ->withHeaders($this->evolutionHeaders())
                ->contentType('application/json')
                ->post($this->evolutionBaseUrl() . '/instance/create', $payload);

            if (!$resp->successful()) {
                return back()->with('error', 'Falha ao criar instância no Evolution: ' . $resp->body());
            }

            $data = $resp->json();

            $instanceId = (string) (data_get($data, 'instance.instanceId') ?? '');
            $apiKey     = (string) (data_get($data, 'hash.apikey') ?? '');

            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_instance_name'   => $instanceName,
                    'wa_instance_id'     => $instanceId ?: null,
                    'wa_instance_apikey' => $apiKey ?: null,
                    'wa_phone'           => $telefone ?: null,
                    'wa_qrcode_base64'   => null,
                    'wa_connection_state'=> 'created',
                    'wa_connected_at'    => null,
                ]);

            return back()->with('success', 'Instância criada! Agora gere o QRCode para conectar o WhatsApp.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao criar instância: ' . $e->getMessage());
        }
    }

    /**
     * Solicita/atualiza o QRCode (via /instance/connect/{instance}).
     * Salva o "code" retornado no wa_qrcode_base64 (na verdade é o code string).
     * NÃO marca como conectado aqui.
     */
    public function whatsappGerarQrCode(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();
        if (!$empresa || empty($empresa->wa_instance_name)) {
            return back()->with('error', 'Crie a instância antes de gerar o QRCode.');
        }

        $instanceName = (string) $empresa->wa_instance_name;

        try {
            $code = $this->fetchConnectCode($instanceName, $empresa->wa_phone ?? null);

            if ($code === null) {
                return back()->with('error', 'Falha ao solicitar QRCode ao Evolution (sem resposta).');
            }

            DB::table('empresas')->where('id', $empresaId)->update([
                'wa_qrcode_base64'    => $code ?: null,
                'wa_connection_state' => $code ? 'created' : ($empresa->wa_connection_state ?? null),
                'wa_connected_at'     => null,
            ]);

            return back()->with('success', $code
                ? 'QRCode atualizado! Escaneie com o WhatsApp.'
                : 'Não foi retornado QRCode (pode já estar conectado).'
            );

        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao gerar QRCode: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint para o front (poll) saber se está conectado.
     * Critério:
     * - state=open E NÃO houver "code" no /connect => connected=true
     * - se houver code => precisa QR => connected=false
     */
    public function whatsappStatus(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();
        if (!$empresa || empty($empresa->wa_instance_name)) {
            return response()->json([
                'ok' => true,
                'hasInstance' => false,
            ]);
        }

        $instanceName = (string) $empresa->wa_instance_name;

        $statePayload = $this->fetchConnectionState($instanceName);
        $state = (string) (data_get($statePayload, 'instance.state') ?? '');

        $code = $this->fetchConnectCode($instanceName, $empresa->wa_phone ?? null);
        $needsQr = is_string($code) && $code !== '';

        $connected = ($state === 'open') && !$needsQr;

        // Atualiza banco e, se tiver code, salva para render do QR
        $update = [
            'wa_connection_state' => $connected ? 'open' : ($needsQr ? 'created' : ($state ?: null)),
            'wa_connected_at'     => $connected ? now() : null,
        ];
        if ($needsQr) {
            $update['wa_qrcode_base64'] = $code;
        }

        DB::table('empresas')->where('id', $empresaId)->update($update);

        return response()->json([
            'ok'          => true,
            'hasInstance' => true,
            'state'       => $state ?: null,
            'needsQr'     => $needsQr,
            'connected'   => $connected,
            'qrCode'      => $needsQr ? $code : null,
        ]);
    }
}
