<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditoriaLogger
{
    /**
     * Registra um log na tabela logs_auditoria.
     *
     * Campos esperados:
     * - empresa_id (nullable)
     * - usuario_id (nullable)
     * - tela_id (nullable)
     * - tela_slug (nullable)
     * - acao (string)
     * - descricao (string|null)
     * - status_code (int|null)
     * - payload (array|null)
     * - request (Request|null)  -> para capturar ip, user_agent, url, method
     */
    public static function log(array $data): void
    {
        /** @var Request|null $request */
        $request = $data['request'] ?? null;

        $payload = $data['payload'] ?? null;

        DB::table('logs_auditoria')->insert([
            'empresa_id'  => $data['empresa_id'] ?? null,
            'usuario_id'  => $data['usuario_id'] ?? null,
            'tela_id'     => $data['tela_id'] ?? null,
            'tela_slug'   => $data['tela_slug'] ?? null,
            'acao'        => $data['acao'] ?? null,
            'descricao'   => $data['descricao'] ?? null,

            'metodo'      => $request?->method(),
            'url'         => $request?->fullUrl(),
            'ip'          => $request?->ip(),
            'user_agent'  => $request?->userAgent(),

            'status_code' => $data['status_code'] ?? null,
            'payload'     => $payload ? json_encode($payload) : null,

            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
