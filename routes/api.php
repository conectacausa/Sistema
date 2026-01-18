<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FiliaisApiController;
use App\Http\Controllers\Api\LocalizacaoApiController;

Route::domain('{sub}.conecttarh.com.br')
    ->middleware(['web', 'auth', 'screen:5'])
    ->group(function () {

        /**
         * LOCALIZAÇÃO (GLOBAL / CENTRAL)
         * NÃO passa por tenant.
         */
        Route::get('/paises', [LocalizacaoApiController::class, 'paises'])->name('api.paises.index');
        Route::get('/paises/{paisId}/estados', [LocalizacaoApiController::class, 'estadosByPais'])->name('api.estados.byPais');
        Route::get('/estados/{estadoId}/cidades', [LocalizacaoApiController::class, 'cidadesByEstado'])->name('api.cidades.byEstado');

        /**
         * FILIAIS (DO TENANT)
         * Passa por tenant.
         */
        Route::middleware(['tenant', 'tenant.user'])->group(function () {
            Route::get('/filiais', [FiliaisApiController::class, 'index'])->name('api.filiais.index');
            Route::delete('/filiais/{filial}', [FiliaisApiController::class, 'destroy'])->name('api.filiais.destroy');
        });
    });
