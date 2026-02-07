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
        $base = (string) config('services.evolution.base_url', env('EVOLUTION_BASE_URL', 'http://127.0.0.1:8080'));
        return rtrim($base, '/');
    }

    private function evolutionAdminKey(): string
    {
        return (string) config('services.evolution.global_apikey', env('EVOLUTION_GLOBAL_APIKEY', ''));
    }

    private function evolutionHeaders(): array
    {
        return [
            'apikey' => $this->evolutionAdminKey(),
            'Accept' => 'application/json',
        ];
    }

    private function makeInstanceName(string $nomeEmpresa, int $empresaId): string
    {
        $base = trim($nomeEmpresa) !== '' ? $nomeEmpresa : ('empresa_' . $empresaId);
        $slug = Str::slug($base, '_');
        if ($slug === '') $slug = 'empresa_' . $empresaId;
        $slug .= '_' . $empresaId;

        return Str::limit($slug, 50, '');
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
                'wa_instance_apikey',
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

        $telefone = preg_replace('/\D+/', '', (string) $request->input('wa_phone', ''));

        $nomeEmpresa  = (string) ($empresa->nome_fantasia ?: $empresa->razao_social ?: ('Empresa ' . $empresaId));
        $instanceName = $this->makeInstanceName($nomeEmpresa, $empresaId);

        $payload = [
            'instanceName' => $instanceName,
            'integration'  => 'WHATSAPP-BAILEYS',
            'token'        => '',
            'qrcode'       => true,
            'number'       => $telefone ?: '',
        ];

        try {
            $resp = Http::timeout(30)
                ->withHeaders($this->evolutionHeaders())
                ->contentType('application/json')
                ->post($this->evolutionBaseUrl() . '/instance/create', $payload);

            if (!$resp->successful()) {
                return back()->with('error', 'Falha ao criar instância no Evolution: ' . $resp->body());
            }

            $data = $resp->json();

            $instanceId = (string) (data_get($data, 'instance.instanceId') ?? data_get($data, 'instanceId') ?? '');
            $apiKey     = (string) (data_get($data, 'hash.apikey') ?? data_get($data, 'apikey') ?? '');

            DB::table('empresas')->where('id', $empresaId)->update([
                'wa_instance_name'    => $instanceName,
                'wa_instance_id'      => $instanceId ?: null,
                'wa_instance_apikey'  => $apiKey ?: null,
                'wa_phone'            => $telefone ?: null,
                'wa_connection_state' => 'created',
                'wa_qrcode_base64'    => null,
                'updated_at'          => now(),
            ]);

            return back()->with('success', 'Instância criada! Agora solicite o QRCode.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erro ao criar instância: ' . $e->getMessage());
        }
    }

    /**
     * ✅ Ajustado: agora CAPTURA e DEVOLVE o QR (base64/datauri ou "code")
     * e salva no banco (mesmo se for "code", o front gera a imagem).
     */
    public function whatsappRequestQr(Request $request, string $sub)
    {
        $empresaId = $this->empresaId();

        $empresa = DB::table('empresas')
            ->select(['id', 'wa_instance_name', 'wa_phone'])
            ->where('id', $empresaId)
            ->first();

        if (!$empresa || empty($empresa->wa_instance_name)) {
            return response()->json(['ok' => false, 'error' => 'Instância não encontrada'], 404);
        }

        try {
            $url = $this->evolutionBaseUrl() . '/instance/connect/' . urlencode((string) $empresa->wa_instance_name);

            $telefone = preg_replace('/\D+/', '', (string) ($empresa->wa_phone ?? ''));
            if ($telefone) {
                $url .= '?number=' . urlencode($telefone);
            }

            $resp = Http::timeout(25)
                ->withHeaders($this->evolutionHeaders())
                ->get($url);

            if (!$resp->successful()) {
                return response()->json(['ok' => false, 'error' => $resp->body()], 500);
            }

            $json = $resp->json();

            // tenta achar QR em várias chaves
            $qr = (string) (
                data_get($json, 'base64') ??
                data_get($json, 'qrcode') ??
                data_get($json, 'qr') ??
                data_get($json, 'code') ??
                data_get($json, 'data.base64') ??
                data_get($json, 'data.qrcode') ??
                data_get($json, 'data.qr') ??
                data_get($json, 'data.code') ??
                ''
            );
            $qr = trim($qr);

            // Salva no banco mesmo se for "code" (o front transforma em imagem)
            DB::table('empresas')->where('id', $empresaId)->update([
                'wa_connection_state' => 'waiting_qr',
                'wa_qrcode_base64'    => $qr !== '' ? $qr : (string) null,
                'updated_at'          => now(),
            ]);

            return response()->json([
                'ok' => true,
                'qrCode' => $qr !== '' ? $qr : null,
            ]);

        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Status (poll)
     * ✅ Se state=open: limpa QR e marca conectado
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

        $qrFromDb = (string) ($empresa->wa_qrcode_base64 ?? '');
        $state = '';

        try {
            $resp = Http::timeout(15)
                ->withHeaders($this->evolutionHeaders())
                ->get($this->evolutionBaseUrl() . '/instance/connectionState/' . urlencode((string) $empresa->wa_instance_name));

            if ($resp->ok()) {
                $json = $resp->json();
                $state = (string) (data_get($json, 'instance.state') ?? '');
            }
        } catch (\Throwable $e) {
            // fallback abaixo
        }

        if ($state === '') {
            $state = (string) ($empresa->wa_connection_state ?? '');
        }

        // conectado real
        if ($state === 'open') {
            DB::table('empresas')->where('id', $empresaId)->update([
                'wa_connection_state' => 'open',
                'wa_qrcode_base64'    => null,
                'updated_at'          => now(),
            ]);

            return response()->json([
                'ok' => true,
                'hasInstance' => true,
                'connected' => true,
                'state' => 'open',
                'needsQr' => false,
                'qrCode' => null,
            ]);
        }

        DB::table('empresas')->where('id', $empresaId)->update([
            'wa_connection_state' => $state ?: ($qrFromDb !== '' ? 'waiting_qr' : null),
            'updated_at'          => now(),
        ]);

        return response()->json([
            'ok' => true,
            'hasInstance' => true,
            'connected' => false,
            'state' => $state ?: null,
            'needsQr' => true,
            'qrCode' => $qrFromDb ?: null,
        ]);
    }
}
