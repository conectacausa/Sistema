<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Config\FiliaisController;

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

            Route::get('/dashboard', fn () => view('dashboard.index'))->name('dashboard');

            /**
             * CONFIG / FILIAIS
             * slug: config/filiais
             * screen_id: 5 (autorização)
             */
            Route::middleware(['screen:5'])->group(function () {
                Route::get('/config/filiais', [FiliaisController::class, 'index'])->name('config.filiais.index');
                Route::get('/config/filiais/nova', [FiliaisController::class, 'create'])->name('config.filiais.create');
                Route::get('/config/filiais/{filial}/editar', [FiliaisController::class, 'edit'])->name('config.filiais.edit');
            });
        });
    });
