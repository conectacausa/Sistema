<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FiliaisApiController;
use App\Http\Controllers\Api\LocalizacaoApiController;

Route::domain('{sub}.conecttarh.com.br')
    ->middleware(['web', 'auth', 'screen:5'])
    ->group(function () {

        // ==========================
        // LOCALIZAÇÃO (GLOBAL - public)
        // ==========================
        Route::get('/paises', [LocalizacaoApiController::class, 'paises'])->name('api.paises.index');

        // ⚠️ usar snake_case para não conflitar com {sub}
        Route::get('/paises/{pais_id}/estados', [LocalizacaoApiController::class, 'estadosByPais'])
            ->whereNumber('pais_id')
            ->name('api.estados.byPais');

        Route::get('/estados/{estado_id}/cidades', [LocalizacaoApiController::class, 'cidadesByEstado'])
            ->whereNumber('estado_id')
            ->name('api.cidades.byEstado');

        // ==========================
        // FILIAIS (TENANT)
        // ==========================
        Route::middleware(['tenant', 'tenant.user'])->group(function () {
            Route::get('/filiais', [FiliaisApiController::class, 'index'])->name('api.filiais.index');
            Route::delete('/filiais/{filial}', [FiliaisApiController::class, 'destroy'])->name('api.filiais.destroy');
        });
    });
