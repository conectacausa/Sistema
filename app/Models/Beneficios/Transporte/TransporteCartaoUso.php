<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteCartaoUso extends Model
{
    use BelongsToEmpresa;

    protected $table = 'transporte_cartao_usos';

    protected $fillable = [
        'empresa_id',
        'numero_cartao','data_hora_uso','valor',
        'linha_id','parada_id','usuario_id',
        'origem',
    ];

    protected $casts = [
        'data_hora_uso' => 'datetime',
        'valor' => 'decimal:2',
    ];

    public function linha()
    {
        return $this->belongsTo(TransporteLinha::class, 'linha_id');
    }

    public function parada()
    {
        return $this->belongsTo(TransporteParada::class, 'parada_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'usuario_id');
    }
}
