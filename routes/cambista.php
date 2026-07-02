<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cambista\CambistaHomeController;
use App\Http\Controllers\Cambista\CambistaBilhetesController;
use App\Http\Controllers\Cambista\CambistaRelatorioController;
use App\Http\Controllers\Cambista\CambistaPerfilController;

Route::middleware(['auth', 'seller', 'tenant', 'activity'])->prefix('cambista')->group(function () {
    Route::get('/', [CambistaHomeController::class, 'index'])->name('cambista.home');
    Route::get('/dashboard', [CambistaHomeController::class, 'index']);

    Route::get('/bilhetes', [CambistaBilhetesController::class, 'index'])->name('cambista.bilhetes');
    Route::get('/bilhetes/{id}', [CambistaBilhetesController::class, 'show'])->name('cambista.bilhete-detail');
    Route::post('/bilhetes/{id}/cancelar', [CambistaBilhetesController::class, 'cancel'])->name('cambista.bilhete.cancel');

    Route::get('/relatorio', [CambistaRelatorioController::class, 'index'])->name('cambista.relatorio');
    Route::post('/relatorio', [CambistaRelatorioController::class, 'filtrar'])->name('cambista.relatorio.filtrar');

    Route::get('/perfil', [CambistaPerfilController::class, 'index'])->name('cambista.perfil');
    Route::post('/perfil', [CambistaPerfilController::class, 'update'])->name('cambista.perfil.update');
});
