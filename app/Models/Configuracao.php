<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Configuracao extends Model
{
    use SoftDeletes;

    protected $table = 'configuracoes';

    protected $fillable = [
        'empresa_id',
        'logo_horizontal_light',
        'logo_horizontal_dark',
        'logo_quadrado_light',
        'logo_quadrado_dark',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
