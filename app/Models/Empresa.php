<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    protected $table = 'empresas';

    protected $fillable = [
        'razao_social',
        'cnpj',
        'nome_fantasia',
        'telefone1',
        'telefone2',
        'email',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade_id',
        'estado_id',
        'pais_id',
        'natureza_juridica_id',
        'porte_empresa_id',
        'situacao_fiscal',
        'data_abertura',
        'cnae_principal_id',
        'subdominio',
    ];

    protected $casts = [
        'data_abertura' => 'date',
    ];
}
