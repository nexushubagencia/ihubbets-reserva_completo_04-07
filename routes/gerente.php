<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Gerente\GerenteHomeController;
use App\Http\Controllers\Gerente\GerenteCambistasController;
use App\Http\Controllers\Gerente\GerenteCaixaController;
use App\Http\Controllers\Gerente\GerenteBilhetesController;
use App\Http\Controllers\Gerente\GerenteRelatorioController;

Route::middleware(['auth', 'manager', 'tenant', 'activity'])->prefix('gerente')->group(function () {
    Route::get('/', [GerenteHomeController::class, 'index'])->name('gerente.home');
    Route::get('/dashboard', [GerenteHomeController::class, 'index']);

    Route::get('/cambistas', [GerenteCambistasController::class, 'index'])->name('gerente.cambistas');
    Route::get('/cambistas/{id}', [GerenteCambistasController::class, 'show'])->name('gerente.cambista-detail');

    Route::get('/caixa', [GerenteCaixaController::class, 'index'])->name('gerente.caixa');
    Route::get('/caixa/{id}', [GerenteCaixaController::class, 'show'])->name('gerente.caixa.detail');

    Route::get('/bilhetes', [GerenteBilhetesController::class, 'index'])->name('gerente.bilhetes');
    Route::get('/bilhetes/{id}', [GerenteBilhetesController::class, 'show'])->name('gerente.bilhete-detail');
    Route::post('/bilhetes/{id}/cancelar', [GerenteBilhetesController::class, 'cancel'])->name('gerente.bilhete.cancel');
    Route::post('/bilhetes-search', [GerenteBilhetesController::class, 'search'])->name('gerente.bilhetes.search');

    Route::get('/relatorio', [GerenteRelatorioController::class, 'index'])->name('gerente.relatorio');
    Route::post('/relatorio', [GerenteRelatorioController::class, 'filtrar'])->name('gerente.relatorio.filtrar');

    Route::get('/perfil', [GerenteHomeController::class, 'perfil'])->name('gerente.perfil');
    Route::post('/perfil', [GerenteHomeController::class, 'updatePerfil'])->name('gerente.perfil.update');
});
