<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteTicketEntrega extends Model
{
    use SoftDeletes, BelongsToEmpresa;

    protected $table = 'transporte_ticket_entregas';

    protected $fillable = [
        'empresa_id',
        'usuario_id','bloco_id',
        'data_entrega','quantidade_entregue',
        'observacao',
    ];

    protected $casts = [
        'data_entrega' => 'date',
        'quantidade_entregue' => 'integer',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'usuario_id');
    }

    public function bloco()
    {
        return $this->belongsTo(TransporteTicketBloco::class, 'bloco_id');
    }
}
