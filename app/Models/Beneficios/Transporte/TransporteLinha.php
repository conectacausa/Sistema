<?php

namespace App\Models\Beneficios\Transporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Beneficios\Transporte\Traits\BelongsToEmpresa;

class TransporteLinha extends Model
{
    use SoftDeletes, BelongsToEmpresa;

    protected $table = 'transporte_linhas';

    protected $fillable = [
        'empresa_id',
        'nome','tipo_linha','controle_acesso',
        'motorista_id','veiculo_id',
        'status','observacoes',
    ];

    public function motorista()
    {
        return $this->belongsTo(TransporteMotorista::class, 'motorista_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(TransporteVeiculo::class, 'veiculo_id');
    }

    public function filiais()
    {
        return $this->belongsToMany(
            \App\Models\Filial::class,
            'transporte_linha_filiais',
            'linha_id',
            'filial_id'
        )->withTimestamps();
    }

    public function paradas()
    {
        return $this->hasMany(TransporteParada::class, 'linha_id')->orderBy('ordem');
    }

    public function vinculos()
    {
        return $this->hasMany(TransporteVinculo::class, 'linha_id');
    }

    public function custos()
    {
        return $this->hasMany(TransporteLinhaCusto::class, 'linha_id');
    }
}
