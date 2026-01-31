<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cargo\CboController;
use App\Http\Controllers\Cargo\CargoController;
use App\Http\Controllers\Recrutamento\FluxoAprovacaoController;
use App\Http\Controllers\Cargo\HeadcountController;

Route::domain('{sub}.conecttarh.com.br')
    ->middleware(['web', 'tenant'])
    ->group(function () {

        Route::get('/', function () {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            return redirect()->route('dashboard');
        })->name('home');

        Route::get('/login', [LoginController::class, 'show'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.post');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

        Route::middleware(['auth', 'tenant.user'])->group(function () {

            Route::get('/dashboard', fn () => view('dashboard.index'))
                ->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | CONFIGURAÇÕES
            |--------------------------------------------------------------------------
            */

            Route::get('/config/filiais', fn () => view('config.filiais.index'))
                ->middleware('screen:5')
                ->name('config.filiais.index');

            /*
            |--------------------------------------------------------------------------
            | CADASTROS → CARGOS
            |--------------------------------------------------------------------------
            */

            Route::get('/cargos/cbo', [CboController::class, 'index'])
                ->middleware('screen:6')
                ->name('cargos.cbo.index');

            Route::get('/cargos/cbo/novo', [CboController::class, 'create'])
                ->middleware('screen:6')
                ->name('cargos.cbo.create');

            Route::post('/cargos/cbo', [CboController::class, 'store'])
                ->middleware('screen:6')
                ->name('cargos.cbo.store');

            Route::get('/cargos/cbo/check', [CboController::class, 'checkCodigo'])
                ->middleware('screen:6')
                ->name('cargos.cbo.check');

            Route::get('/cargos/cargos', [CargoController::class, 'index'])
                ->middleware('screen:7')
                ->name('cargos.cargos.index');

            Route::get('/cargos/cargos/novo', [CargoController::class, 'create'])
                ->middleware('screen:7')
                ->name('cargos.cargos.create');

            Route::get('/cargos/cargos/{id}/editar', [CargoController::class, 'edit'])
                ->middleware('screen:7')
                ->name('cargos.cargos.edit');

            Route::get('/cargos/setores-por-filial', [CargoController::class, 'setoresPorFilial'])
                ->middleware('screen:7')
                ->name('cargos.setores_por_filial');

            Route::get('/cargos/qlp', [HeadcountController::class, 'index'])
                ->name('cargos.headcount.index');

            Route::get('/cargos/qlp/setores-por-filiais', [HeadcountController::class, 'setoresPorFiliais'])
                ->name('cargos.headcount.setores_por_filiais');

            /*
            |--------------------------------------------------------------------------
            | RECRUTAMENTO E SELEÇÃO
            |--------------------------------------------------------------------------
            */

            Route::prefix('recrutamento')->group(function () {
                Route::resource('fluxo', FluxoAprovacaoController::class);
            });

        }); // 🔒 fecha middleware auth + tenant.user

    }); // 🔒 fecha domain
