<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteParada extends Model
{
    use SoftDeletes, BelongsToEmpresa;

    protected $table = 'transporte_paradas';

    protected $fillable = [
        'empresa_id','linha_id',
        'nome','endereco',
        'horario','valor',
        'ordem',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'ordem' => 'integer',
    ];

    public function linha()
    {
        return $this->belongsTo(TransporteLinha::class, 'linha_id');
    }

    public function vinculos()
    {
        return $this->hasMany(TransporteVinculo::class, 'parada_id');
    }
}
