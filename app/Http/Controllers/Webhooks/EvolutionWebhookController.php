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
     * Importante: esta rota NÃO usa auth (apenas tenant via subdomínio).
     */
    public function handle(Request $request, string $sub)
    {
        $payload = $request->all();

        // (Opcional) Validação por secret (se você quiser endurecer):
        // - Defina no .env: EVOLUTION_WEBHOOK_SECRET=xxxxx
        // - Configure no Evolution para enviar header: X-Webhook-Secret: xxxxx (se suportar)
        $secret = (string) env('EVOLUTION_WEBHOOK_SECRET', '');
        if ($secret !== '') {
            $incoming = (string) ($request->header('X-Webhook-Secret') ?? '');
            if (!hash_equals($secret, $incoming)) {
                return response()->json(['ok' => false, 'error' => 'Unauthorized'], 401);
            }
        }

        // Identifica evento
        $event = (string) (
            data_get($payload, 'event') ??
            data_get($payload, 'type') ??
            data_get($payload, 'action') ??
            data_get($payload, 'name') ??
            ''
        );
        $event = trim($event);

        // Identifica instanceName (tenta várias chaves comuns)
        $instanceName = (string) (
            data_get($payload, 'instance') ??
            data_get($payload, 'instanceName') ??
            data_get($payload, 'data.instance') ??
            data_get($payload, 'data.instanceName') ??
            data_get($payload, 'instance.instanceName') ??
            data_get($payload, 'instance.instance') ??
            ''
        );

        // Alguns payloads vêm com instance como objeto
        if ($instanceName === '' && is_array(data_get($payload, 'instance'))) {
            $instanceName = (string) (data_get($payload, 'instance.instanceName') ?? '');
        }

        $instanceName = trim($instanceName);

        if ($instanceName === '') {
            return response()->json(['ok' => true, 'ignored' => 'missing_instance']);
        }

        // Localiza a empresa pela instanceName
        $empresa = DB::table('empresas')
            ->select(['id', 'wa_instance_name'])
            ->where('wa_instance_name', $instanceName)
            ->first();

        if (!$empresa) {
            return response()->json(['ok' => true, 'ignored' => 'instance_not_mapped']);
        }

        /**
         * Captura QR real (base64)
         * - Pode vir como data:image/png;base64,... OU apenas base64 puro
         */
        $qr = (string) (
            data_get($payload, 'qrcode') ??
            data_get($payload, 'qr') ??
            data_get($payload, 'base64') ??
            data_get($payload, 'data.qrcode') ??
            data_get($payload, 'data.qr') ??
            data_get($payload, 'data.base64') ??
            data_get($payload, 'data.qrcode.base64') ??
            data_get($payload, 'data.qrcodeBase64') ??
            ''
        );
        $qr = trim($qr);

        /**
         * Captura state (open/close/connecting/...)
         */
        $state = (string) (
            data_get($payload, 'state') ??
            data_get($payload, 'status') ??
            data_get($payload, 'data.state') ??
            data_get($payload, 'data.status') ??
            data_get($payload, 'data.connectionState') ??
            ''
        );
        $state = trim($state);

        $update = [
            'updated_at' => now(),
        ];

        // Heurística: se evento sugere QR, salva QR mesmo sem "state"
        $eventLower = Str::lower($event);
        $isQrEvent = Str::contains($eventLower, 'qr');

        // Se veio QR, salva QR e marca aguardando
        if ($qr !== '') {
            $update['wa_qrcode_base64'] = $qr;
            $update['wa_connection_state'] = $state !== '' ? $state : 'waiting_qr';
        } elseif ($isQrEvent) {
            // Evento de QR sem base64 (raro) — ainda marca como aguardando
            $update['wa_connection_state'] = $state !== '' ? $state : 'waiting_qr';
        }

        // Se veio state, atualiza
        if ($state !== '') {
            $update['wa_connection_state'] = $state;

            // Se conectou, limpa QR pendente
            if ($state === 'open' || Str::lower($state) === 'connected') {
                $update['wa_qrcode_base64'] = null;
            }
        }

        // Se não veio nada útil, registra algo mínimo
        if (count($update) === 1) {
            $update['wa_connection_state'] = $event !== '' ? $event : 'webhook';
        }

        DB::table('empresas')
            ->where('id', $empresa->id)
            ->update($update);

        return response()->json(['ok' => true]);
    }
}
