<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cargo\CboController;
use App\Http\Controllers\Cargo\CargoController;

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

            // ✅ Tela Filiais (tela_id = 5)
            Route::get('/config/filiais', fn () => view('config.filiais.index'))
                ->middleware('screen:5')
                ->name('config.filiais.index');

            /*
            |--------------------------------------------------------------------------
            | CADASTROS → CARGOS
            |--------------------------------------------------------------------------
            */

            // ✅ Tela CBOs (tela_id = 6 | slug = cargos/cbo)
            Route::get('/cargos/cbo', [CboController::class, 'index'])
                ->middleware('screen:6')
                ->name('cargos.cbo.index');

            // ✅ Novo CBO (placeholder por enquanto)
            Route::get('/cargos/cbo/novo', [CboController::class, 'create'])
                ->middleware('screen:6')
                ->name('cargos.cbo.create');

Route::post('/cargos/cbo', [CboController::class, 'store'])
    ->middleware('screen:6')
    ->name('cargos.cbo.store');

Route::get('/cargos/cbo/check', [CboController::class, 'checkCodigo'])
    ->middleware('screen:6')
    ->name('cargos.cbo.check');

            // ✅ Tela Cargos (tela_id = 7 | slug = cargos/cargos)
Route::get('/cargos/cargos', [CargoController::class, 'index'])
    ->middleware('screen:7')
    ->name('cargos.cargos.index');

// placeholders (vamos implementar depois)
Route::get('/cargos/cargos/novo', [CargoController::class, 'create'])
    ->middleware('screen:7')
    ->name('cargos.cargos.create');

Route::get('/cargos/cargos/{id}/editar', [CargoController::class, 'edit'])
    ->middleware('screen:7')
    ->name('cargos.cargos.edit');

        });
    });
