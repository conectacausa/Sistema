<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Colaborador extends Model
{
    use SoftDeletes;

    protected $table = 'colaboradores';

    protected $fillable = [
        'cpf',
        'nome',
        'sexo',
        'matricula',
        'filial_id',
        'empresa_id',
        'data_admissao',
    ];

    protected $casts = [
        'data_admissao' => 'date',
    ];
}
