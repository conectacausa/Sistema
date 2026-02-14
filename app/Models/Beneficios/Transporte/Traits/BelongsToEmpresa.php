<?php

namespace App\Models\Beneficios\Transporte\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToEmpresa
{
    public function scopeDaEmpresa(Builder $query, ?int $empresaId = null): Builder
    {
        $empresaId = $empresaId ?? (int) (auth()->user()->empresa_id ?? 0);
        return $query->where('empresa_id', $empresaId);
    }
}
