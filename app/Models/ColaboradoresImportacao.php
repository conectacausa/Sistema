<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColaboradoresImportacao extends Model
{
    protected $table = 'colaboradores_importacoes';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'arquivo_path',
        'arquivo_nome',
        'status',
        'total_linhas',
        'importados',
        'ignorados',
        'mensagem_erro',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
