<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    // Campo de senha no seu banco é "senha"
    public function getAuthPassword(): string
    {
        return (string) $this->senha;
    }

    /**
     * Relacionamentos
     */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }

    protected $fillable = [
        'nome_completo',
        'cpf',
        'empresa_id',
        'permissao_id',
        'colaborador_id',
        'email',
        'telefone',
        'senha',
        'status',
        'data_expiracao',
        'salarios',
        'operador_whatsapp',
        'foto',
    ];

    protected $hidden = [
        'senha',
        'remember_token',
    ];

    protected $casts = [
        'salarios' => 'boolean',
        'operador_whatsapp' => 'boolean',
        'data_expiracao' => 'date',
    ];
}
