<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteInspecao extends Model
{
    use BelongsToEmpresa;

    protected $table = 'transporte_inspecoes';

    protected $fillable = [
        'empresa_id',
        'veiculo_id','linha_id',
        'data_inspecao','status','validade_ate',
        'checklist_json','observacoes',
        'usuario_id',
    ];

    protected $casts = [
        'data_inspecao' => 'datetime',
        'validade_ate' => 'date',
        'checklist_json' => 'array',
    ];

    public function veiculo()
    {
        return $this->belongsTo(TransporteVeiculo::class, 'veiculo_id');
    }

    public function linha()
    {
        return $this->belongsTo(TransporteLinha::class, 'linha_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\Usuario::class, 'usuario_id');
    }
}
