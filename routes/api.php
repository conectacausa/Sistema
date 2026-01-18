<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FiliaisApiController;
use App\Http\Controllers\Api\LocalizacaoApiController;

Route::middleware(['auth:sanctum', 'screen:5'])->group(function () {

    // Listagem com filtros + paginação
    Route::get('/filiais', [FiliaisApiController::class, 'index'])->name('api.filiais.index');

    // Soft delete
    Route::delete('/filiais/{filial}', [FiliaisApiController::class, 'destroy'])->name('api.filiais.destroy');

    // Combos dependentes
    Route::get('/paises', [LocalizacaoApiController::class, 'paises'])->name('api.paises.index');
    Route::get('/paises/{pais}/estados', [LocalizacaoApiController::class, 'estadosByPais'])->name('api.estados.byPais');
    Route::get('/estados/{estado}/cidades', [LocalizacaoApiController::class, 'cidadesByEstado'])->name('api.cidades.byEstado');
});
