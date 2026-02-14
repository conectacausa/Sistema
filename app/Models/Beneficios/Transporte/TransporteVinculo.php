<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteVinculo extends Model
{
    use SoftDeletes, BelongsToEmpresa;

    protected $table = 'transporte_vinculos';

    protected $fillable = [
        'empresa_id',
        'usuario_id','linha_id','parada_id',
        'tipo_acesso','numero_cartao','numero_vale_ticket',
        'valor_passagem',
        'data_inicio','data_fim',
        'status','observacoes',
    ];

    protected $casts = [
        'valor_passagem' => 'decimal:2',
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'usuario_id');
    }

    public function linha()
    {
        return $this->belongsTo(TransporteLinha::class, 'linha_id');
    }

    public function parada()
    {
        return $this->belongsTo(TransporteParada::class, 'parada_id');
    }
}
