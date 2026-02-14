<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteLinhaCusto extends Model
{
    use BelongsToEmpresa;

    protected $table = 'transporte_linha_custos';

    protected $fillable = [
        'empresa_id','linha_id',
        'competencia','valor_total',
        'origem','observacao',
    ];

    protected $casts = [
        'competencia' => 'date',
        'valor_total' => 'decimal:2',
    ];

    public function linha()
    {
        return $this->belongsTo(TransporteLinha::class, 'linha_id');
    }
}
