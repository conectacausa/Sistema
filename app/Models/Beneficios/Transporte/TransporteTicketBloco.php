<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteTicketBloco extends Model
{
    use SoftDeletes, BelongsToEmpresa;

    protected $table = 'transporte_ticket_blocos';

    protected $fillable = [
        'empresa_id',
        'codigo_bloco','quantidade_tickets','viagens_por_ticket',
        'status','observacoes',
    ];

    protected $casts = [
        'quantidade_tickets' => 'integer',
        'viagens_por_ticket' => 'integer',
    ];

    public function entregas()
    {
        return $this->hasMany(TransporteTicketEntrega::class, 'bloco_id');
    }
}
