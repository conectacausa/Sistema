<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cargo extends Model
{
    use SoftDeletes;

    protected $table = 'cargos';

   protected $fillable = [
    'empresa_id',
    'titulo',
    'cbo_id',
    'status',
    'jovem_aprendiz',
    'descricao_cargo',
    'revisao',
];

    protected $casts = [
        'status' => 'boolean',
        'jovem_aprendiz' => 'boolean',
        'revisao' => 'date',
    ];

    public function cbo(): BelongsTo
    {
        return $this->belongsTo(Cbo::class, 'cbo_id');
    }
}
