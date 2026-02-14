<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteMotorista extends Model
{
    use SoftDeletes, BelongsToEmpresa;

    protected $table = 'transporte_motoristas';

    protected $fillable = [
        'empresa_id',
        'nome','cpf','cnh_numero','cnh_categoria','cnh_validade',
        'telefone','email',
        'status','observacoes',
    ];

    protected $casts = [
        'cnh_validade' => 'date',
    ];

    public function linhas()
    {
        return $this->hasMany(TransporteLinha::class, 'motorista_id');
    }
}
