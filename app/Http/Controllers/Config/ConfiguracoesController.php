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
        return rtrim(config('services.evolution.base_url', env('EVOLUTION_BASE_URL', 'https://evolution.conecttarh.com.br')), '/');
    }

    /**
     * Chave ADMIN da Evolution (global) para gerenciar instâncias.
     */
    private function evolutionAdminKey(): string
    {
        // Se você estiver usando EVOLUTION_GLOBAL_APIKEY no .env, use este:
        return (string) env('EVOLUTION_GLOBAL_APIKEY', env('EVOLUTION_ADMIN_API_KEY', ''));
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
                'wa_instance_apikey',     // ✅ nome correto
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
     * Poll do status (JS) — Regra rígida:
     * Se existir QRCode salvo no banco, NÃO está conectado.
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

        $qrFromDb = (string) ($empresa->wa_qrcode_base64 ?? '');
        $hasQrPending = ($qrFromDb !== '');

        $state = null;

        try {
            $resp = Http::withHeaders([
                    'apikey' => $adminKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(15)
                ->get($baseUrl . '/instance/connectionState/' . $empresa->wa_instance_name);

            if ($resp->ok()) {
                $json = $resp->json();
                $state = (string) (data_get($json, 'instance.state') ?? '');
            }
        } catch (\Throwable $e) {
            // fallback abaixo
        }

        if (!$state) {
            $state = (string) ($empresa->wa_connection_state ?? '');
        }

        // ✅ conectado SOMENTE se state=open e NÃO existe QR pendente no banco
        $connected = ($state === 'open') && !$hasQrPending;

        if ($connected) {
            // ao conectar, limpa QR pendente
            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_connection_state' => 'open',
                    'wa_qrcode_base64'    => null,
                    'updated_at'          => now(),
                ]);
        } else {
            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_connection_state' => $state ?: ($hasQrPending ? 'waiting_qr' : null),
                    'updated_at'          => now(),
                ]);
        }

        return response()->json([
            'ok' => true,
            'hasInstance' => true,
            'connected' => $connected,
            'state' => $state ?: null,
            'needsQr' => !$connected,
            'qrCode' => $connected ? null : ($qrFromDb ?: null),
        ]);
    }

    /**
     * Criar instância
     */
    public function whatsappCriarInstancia(Request $request, string $sub)
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
        $instanceName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $instanceName) . '_' . $empresaId;

        $telefone = preg_replace('/\D+/', '', (string) $request->input('wa_phone', ''));

        $baseUrl = $this->evolutionBaseUrl();
        $adminKey = $this->evolutionAdminKey();

        try {
            $resp = Http::withHeaders([
                    'apikey' => $adminKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post($baseUrl . '/instance/create', [
                    'instanceName' => $instanceName,
                    'integration'  => 'WHATSAPP-BAILEYS',
                    'token'        => '',
                    'qrcode'       => true,
                    'number'       => $telefone ?: '',
                ]);

            if (!$resp->ok()) {
                return back()->with('error', 'Falha ao criar instância no Evolution: ' . $resp->body());
            }

            $json = $resp->json();

            $instanceId = data_get($json, 'instance.instanceId') ?: data_get($json, 'instanceId');
            $apiKey     = data_get($json, 'hash.apikey') ?: data_get($json, 'apikey');

            DB::table('empresas')
                ->where('id', $empresaId)
                ->update([
                    'wa_instance_name'     => $instanceName,
                    'wa_instance_id'       => $instanceId,
                    'wa_instance_apikey'   => $apiKey,  // ✅ nome correto
                    'wa_phone'             => $telefone ?: null,
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
     * Gerar/Atualizar QR
     */
    public function whatsappGerarQrCode(Request $request, string $sub)
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

            $qr = data_get($json, 'base64')
                ?: data_get($json, 'qrcode')
                ?: data_get($json, 'qr')
                ?: data_get($json, 'code');

            if (!$qr) {
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
