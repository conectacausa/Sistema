<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Filial extends Model
{
    use SoftDeletes;

    protected $table = 'filiais';

    protected $fillable = [
        'empresa_id',
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
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }
}
