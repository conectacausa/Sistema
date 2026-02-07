<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class WhatsappIntegracao extends Model
{
    use SoftDeletes;

    protected $table = 'whatsapp_integracoes';

    protected $fillable = [
        'empresa_id',
        'provider',
        'base_url',
        'api_key',
        'instance_name',
        'ativo',
        'status',
        'telefone',
        'connected_at',
        'last_sync_at',
        'webhook_url',
        'webhook_secret',
        'meta',
    ];

    protected $casts = [
        'ativo'        => 'boolean',
        'meta'         => 'array',
        'connected_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    /**
     * Armazena a api_key criptografada no banco.
     */
    public function setApiKeyAttribute($value): void
    {
        $value = trim((string)$value);

        if ($value === '') {
            $this->attributes['api_key'] = null;
            return;
        }

        // Evita dupla criptografia se alguém colar um valor já criptografado
        try {
            Crypt::decryptString($value);
            $this->attributes['api_key'] = $value; // já é criptografado
        } catch (\Throwable $e) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    /**
     * Retorna a api_key descriptografada (para uso interno).
     */
    public function getApiKeyPlain(): ?string
    {
        $val = $this->attributes['api_key'] ?? null;
        if (!$val) return null;

        try {
            return Crypt::decryptString($val);
        } catch (\Throwable $e) {
            // caso tenha algo antigo salvo em texto
            return $val;
        }
    }

    public function apiKeyMasked(): string
    {
        $plain = $this->getApiKeyPlain();
        if (!$plain) return '';
        $len = strlen($plain);
        if ($len <= 6) return str_repeat('*', $len);
        return substr($plain, 0, 3) . str_repeat('*', max(0, $len - 6)) . substr($plain, -3);
    }
}
