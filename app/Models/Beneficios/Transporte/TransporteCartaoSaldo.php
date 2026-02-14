<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteCartaoSaldo extends Model
{
    use BelongsToEmpresa;

    protected $table = 'transporte_cartoes_saldos';

    protected $fillable = [
        'empresa_id',
        'numero_cartao','saldo',
        'data_referencia','origem',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
        'data_referencia' => 'date',
    ];
}
