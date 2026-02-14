<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteVeiculo extends Model
{
    use SoftDeletes, BelongsToEmpresa;

    protected $table = 'transporte_veiculos';

    protected $fillable = [
        'empresa_id',
        'tipo','placa','renavam','chassi',
        'marca','modelo','ano',
        'capacidade_passageiros',
        'inspecao_cada_meses',
        'status','observacoes',
    ];

    protected $casts = [
        'ano' => 'integer',
        'capacidade_passageiros' => 'integer',
        'inspecao_cada_meses' => 'integer',
    ];

    public function linhas()
    {
        return $this->hasMany(TransporteLinha::class, 'veiculo_id');
    }

    public function inspecoes()
    {
        return $this->hasMany(TransporteInspecao::class, 'veiculo_id');
    }
}
