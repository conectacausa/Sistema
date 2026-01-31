<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permissao extends Model
{
    use SoftDeletes;

    protected $table = 'permissoes';

    protected $fillable = [
        'empresa_id',
        'nome_grupo',
        'observacoes',
        'status',
        'salarios',
    ];

    protected $casts = [
        'status' => 'boolean',
        'salarios' => 'boolean',
    ];

    public function usuarios()
    {
        // usuarios.permissao_id -> permissoes.id
        return $this->hasMany(Usuario::class, 'permissao_id');
    }
}
