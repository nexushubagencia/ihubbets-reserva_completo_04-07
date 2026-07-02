<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cliente\ClienteHomeController;
use App\Http\Controllers\Cliente\ClienteApostasController;
use App\Http\Controllers\Cliente\ClienteFinanceiroController;
use App\Http\Controllers\Cliente\ClientePerfilController;

Route::middleware(['auth', 'client', 'tenant', 'activity'])->prefix('cliente')->group(function () {
    Route::get('/', [ClienteHomeController::class, 'index'])->name('cliente.home');
    Route::get('/dashboard', [ClienteHomeController::class, 'index']);

    Route::get('/apostas', [ClienteApostasController::class, 'index'])->name('cliente.apostas');
    Route::get('/apostas/{id}', [ClienteApostasController::class, 'show'])->name('cliente.aposta-detail');
    Route::post('/apostas/{id}/cancelar', [ClienteApostasController::class, 'cancel'])->name('cliente.aposta.cancel');

    Route::get('/financeiro', [ClienteFinanceiroController::class, 'index'])->name('cliente.financeiro');
    Route::get('/financeiro/depositos', [ClienteFinanceiroController::class, 'depositos'])->name('cliente.depositos');
    Route::get('/financeiro/saques', [ClienteFinanceiroController::class, 'saques'])->name('cliente.saques');
    Route::post('/financeiro/saque', [ClienteFinanceiroController::class, 'requestSaque'])->name('cliente.saque.request');

    Route::get('/perfil', [ClientePerfilController::class, 'index'])->name('cliente.perfil');
    Route::post('/perfil', [ClientePerfilController::class, 'update'])->name('cliente.perfil.update');
});
