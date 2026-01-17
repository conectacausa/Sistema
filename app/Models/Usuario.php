<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usuario extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome_completo','cpf','empresa_id','permissao_id','email','telefone','senha','status',
        'data_expiracao','salarios','colaborador_id','operador_whatsapp','foto'
    ];

    protected $hidden = ['senha'];

    // Para o Auth funcionar como "password"
    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }
}
