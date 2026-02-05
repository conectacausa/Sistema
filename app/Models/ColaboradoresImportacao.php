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
        'rejeitados_path',
        'status',
        'total_linhas',
        'importados',
        'ignorados',
        'rejeitados_count',
        'mensagem_erro',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'empresa_id'       => 'integer',
        'user_id'          => 'integer',
        'total_linhas'     => 'integer',
        'importados'       => 'integer',
        'ignorados'        => 'integer',
        'rejeitados_count' => 'integer',
        'started_at'       => 'datetime',
        'finished_at'      => 'datetime',
    ];
}
