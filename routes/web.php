<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::domain('{sub}.conecttarh.com.br')
    ->middleware(['web', 'tenant'])
    ->group(function () {

        // index do subdomínio
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
        });
    });
