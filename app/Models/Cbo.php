<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cbo extends Model
{
    use SoftDeletes;

    protected $table = 'cbos';

    protected $fillable = [
        'cbo',
        'titulo',
        'descricao',
        'validacao',
    ];

    public function cargos(): HasMany
    {
        return $this->hasMany(Cargo::class, 'cbo_id');
    }
}
