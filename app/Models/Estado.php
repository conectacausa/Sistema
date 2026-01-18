<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    protected $table = 'estados';

    protected $fillable = ['pais_id', 'nome', 'sigla'];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }
}
