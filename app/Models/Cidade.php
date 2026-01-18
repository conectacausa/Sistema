<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    protected $table = 'cidades';

    protected $fillable = ['estado_id', 'nome'];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }
}
