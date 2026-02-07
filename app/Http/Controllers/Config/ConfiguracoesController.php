<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConfiguracoesController extends Controller
{
    private function empresaId(): int
    {
        return (int) (auth()->user()->empresa_id ?? 0);
    }

    private function evolutionBaseUrl(): string
    {
        return rtrim((string) config('services.evolution.base_url'), '/');
    }

    private function evolutionGlobalApikey(): string
    {
        return (string) config('services.evolution.global_apikey');
    }

    private function evolutionHeaders(): array
    {
        return [
            'apikey' => $this->evolutionGlobalApikey(),
            'Accept' => 'application/json',
        ];
    }

    private function makeInstanceName(string $empresaNome): string
    {
        // nome "estável", sem espaços, para virar instanceName
        $base = trim($empresaNome) !== '' ? $empresaNome : 'empresa';
        $slug = Str::slug($base, '_'); // ex: cristal_copo
        return Str::limit($slug ?: ('empresa_' . $this->empresaId()), 50, '');
    }

    public function index(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')
            ->where('id', $empresaId)
            ->first();

        // status ao carregar
        $connection = null;
        if (!empty($empresa?->wa_instance_name)) {
            $connection = $this->fetchConnectionState((string) $empresa->wa_instance_name);
            if ($connection && isset($connection['instance']['state'])) {
                DB::table('empresas')
                    ->where('id', $empresaId)
                    ->update([
                        'wa_connection_state' => $connection['instance']['state'] ?? null,
                        'wa_connected_at'     => (($connection['instance']['state'] ?? null) === 'open') ? now() : null,
                    ]);

                $empresa->wa_connection_state = $connection['instance']['state'] ?? null;
            }
        }

        return view('config.configuracoes.index', [
            'empresa' => $empresa,
        ]);
    }

    public function whatsappCriarInstancia(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();
        if (!$empresa) {
            return back()->with('error', 'Empresa não encontrada.');
        }

        // se já existe instância, não cria de novo
        if (!empty($empresa->wa_instance_name) && !empty($empresa->wa_instance_id)) {
            return back()->with('info', 'Esta empresa já possui uma instância criada.');
        }

        $telefone = preg_replace('/\D+/', '', (string) $request->input('wa_phone', ''));

        $empresaNome = (string) ($empresa->nome_fantasia ?? $empresa->nome ?? $empresa->razao_social ?? 'Empresa');
        $instanceName = $this->makeInstanceName($empresaNome);

        $payload = [
            'instanceName' => $instanceName,
            'integration'  => 'WHATSAPP-BAILEYS',
            // token vazio => Evolution cria dinamicamente (hash.apikey)
            'token'        => '',
            'qrcode'       => true,
            // number é opcional no create. vamos mandar se tiver.
            'number'       => $telefone ?: '',
        ];

        try {
            $resp = Http::withHeaders($this->evolutionHeaders())
                ->contentType('application/json')
                ->post($this->evolutionBaseUrl() . '/instance/create', $payload);

            if (!$resp->successful()) {
                return back()->with('error', 'Falha ao criar instância no Evolution: ' . $resp->body());
            }

            $data = $resp->json();

            $instanceId = data_get($data, 'instance.instanceId');
            $apiKey     = data_get($data, 'hash.apikey'); // gerado pelo Evolution

            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_instance_name'   => $instanceName,
                    'wa_instance_id'     => $instanceId,
                    'wa_instance_apikey' => $apiKey,
                    'wa_phone'           => $telefone ?: null,
                    'wa_connection_state'=> 'created',
                    'wa_connected_at'    => null,
                ]);

            return back()->with('success', 'Instância criada! Agora gere o QRCode e faça a leitura no WhatsApp.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao criar instância: ' . $e->getMessage());
        }
    }

    public function whatsappGerarQrCode(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')->where('id', $empresaId)->first();
        if (!$empresa || empty($empresa->wa_instance_name)) {
            return back()->with('error', 'Crie a instância antes de gerar o QRCode.');
        }

        $instanceName = (string) $empresa->wa_instance_name;
        $telefone     = preg_replace('/\D+/', '', (string) ($empresa->wa_phone ?? ''));

        try {
            // doc: GET /instance/connect/{instance}?number=...
            $url = $this->evolutionBaseUrl() . '/instance/connect/' . urlencode($instanceName);
            if ($telefone) {
                $url .= '?number=' . urlencode($telefone);
            }

            $resp = Http::withHeaders($this->evolutionHeaders())
                ->get($url);

            if (!$resp->successful()) {
                return back()->with('error', 'Falha ao gerar QRCode: ' . $resp->body());
            }

            // A API retorna "code" e/ou "pairingCode". O manager usa isso para montar o QR.
            // Aqui vamos exibir o "code" como texto/placeholder e evoluir para base64 via webhook no próximo passo.
            $data = $resp->json();

            DB::table('empresas')->where('id', $empresaId)->update([
                // guardamos temporariamente em base64 campo (mesmo não sendo base64 ainda)
                // no próximo passo vamos trocar para receber base64 via webhook
                'wa_qrcode_base64' => (string) (data_get($data, 'code') ?? ''),
            ]);

            return back()->with('success', 'QRCode solicitado ao Evolution. Se não aparecer, clique novamente.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao gerar QRCode: ' . $e->getMessage());
        }
    }

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

        $state = null;
        $payload = $this->fetchConnectionState((string) $empresa->wa_instance_name);
        if ($payload && isset($payload['instance']['state'])) {
            $state = $payload['instance']['state'];

            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_connection_state' => $state,
                    'wa_connected_at'     => ($state === 'open') ? now() : null,
                ]);
        }

        return response()->json([
            'ok'          => true,
            'hasInstance' => true,
            'state'       => $state,
        ]);
    }

    private function fetchConnectionState(string $instanceName): ?array
    {
        try {
            $resp = Http::withHeaders($this->evolutionHeaders())
                ->get($this->evolutionBaseUrl() . '/instance/connectionState/' . urlencode($instanceName));

            if (!$resp->successful()) {
                return null;
            }

            return $resp->json();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
