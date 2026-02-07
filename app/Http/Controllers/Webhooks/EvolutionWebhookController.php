<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EvolutionWebhookController extends Controller
{
    /**
     * Webhook chamado pelo Evolution.
     * Configure no Evolution para apontar para:
     *   https://{sub}.conecttarh.com.br/webhooks/evolution
     *
     * Importante: NÃO usa auth. Só o middleware tenant (pelo subdomínio).
     */
    public function handle(Request $request, string $sub)
    {
        $payload = $request->all();

        // (Opcional) Validação simples por secret (se você decidir usar)
        // Envie esse header pelo Evolution (se suportar) e defina no .env:
        // EVOLUTION_WEBHOOK_SECRET=xxxxx
        $secret = (string) env('EVOLUTION_WEBHOOK_SECRET', '');
        if ($secret !== '') {
            $incoming = (string) ($request->header('X-Webhook-Secret') ?? '');
            if (!hash_equals($secret, $incoming)) {
                return response()->json(['ok' => false, 'error' => 'Unauthorized'], 401);
            }
        }

        /**
         * O Evolution pode enviar campos diferentes dependendo da versão/config.
         * Vamos capturar o máximo possível.
         */
        $event = (string) (
            data_get($payload, 'event') ??
            data_get($payload, 'type') ??
            data_get($payload, 'action') ??
            ''
        );

        $instanceName = (string) (
            data_get($payload, 'instance') ??
            data_get($payload, 'instanceName') ??
            data_get($payload, 'data.instance') ??
            data_get($payload, 'data.instanceName') ??
            data_get($payload, 'instance.instanceName') ??
            data_get($payload, 'instance.instance') ??
            ''
        );

        // Alguns payloads vem com "instance": { instanceName: ... }
        if ($instanceName === '') {
            $instanceName = (string) (data_get($payload, 'instance.instanceName') ?? '');
        }

        $instanceName = trim($instanceName);

        if ($instanceName === '') {
            // Sem instance, não temos como mapear a empresa
            return response()->json(['ok' => true, 'ignored' => 'missing_instance']);
        }

        $empresa = DB::table('empresas')
            ->select(['id', 'wa_instance_name'])
            ->where('wa_instance_name', $instanceName)
            ->first();

        if (!$empresa) {
            // Instância não encontrada no Conectta
            return response()->json(['ok' => true, 'ignored' => 'instance_not_mapped']);
        }

        // Captura QR base64 real (quando vier)
        $qr = (string) (
            data_get($payload, 'qrcode') ??
            data_get($payload, 'qr') ??
            data_get($payload, 'base64') ??
            data_get($payload, 'data.qrcode') ??
            data_get($payload, 'data.qr') ??
            data_get($payload, 'data.base64') ??
            data_get($payload, 'data.qrcode.base64') ??
            ''
        );

        $qr = trim($qr);

        // Captura estado de conexão (quando vier)
        $state = (string) (
            data_get($payload, 'state') ??
            data_get($payload, 'status') ??
            data_get($payload, 'data.state') ??
            data_get($payload, 'data.status') ??
            ''
        );

        $state = trim($state);

        $update = ['updated_at' => now()];

        // Se vier evento de QR, salvamos QR
        if ($qr !== '') {
            // Se vier data-uri, ok. Se vier base64 puro, ok também.
            $update['wa_qrcode_base64'] = $qr;
            // enquanto tem QR, consideramos aguardando
            $update['wa_connection_state'] = $state !== '' ? $state : 'waiting_qr';
        }

        // Se vier estado, atualiza
        if ($state !== '') {
            $update['wa_connection_state'] = $state;

            // Se conectou de verdade, limpa QR pendente
            if ($state === 'open' || Str::lower($state) === 'connected') {
                $update['wa_qrcode_base64'] = null;
            }
        }

        // Se não veio nada útil, apenas registra o evento como estado (opcional)
        if (count($update) === 1) {
            $update['wa_connection_state'] = $event !== '' ? $event : 'webhook';
        }

        DB::table('empresas')
            ->where('id', $empresa->id)
            ->update($update);

        return response()->json(['ok' => true]);
    }
}
