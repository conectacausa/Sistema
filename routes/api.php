<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FiliaisApiController;
use App\Http\Controllers\Api\LocalizacaoApiController;

Route::domain('{sub}.conecttarh.com.br')
    ->middleware(['web', 'tenant', 'auth', 'tenant.user', 'screen:5'])
    ->group(function () {

        // FILIAIS
        Route::get('/filiais', [FiliaisApiController::class, 'index'])->name('api.filiais.index');
        Route::delete('/filiais/{filial}', [FiliaisApiController::class, 'destroy'])->name('api.filiais.destroy');

        // LOCALIZAÇÃO (usando IDs, sem model binding)
        Route::get('/paises', [LocalizacaoApiController::class, 'paises'])->name('api.paises.index');
        Route::get('/paises/{paisId}/estados', [LocalizacaoApiController::class, 'estadosByPais'])->name('api.estados.byPais');
        Route::get('/estados/{estadoId}/cidades', [LocalizacaoApiController::class, 'cidadesByEstado'])->name('api.cidades.byEstado');
    });
