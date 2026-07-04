<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Robô Financeiro Multi-Tenant (Roda 23:55 de todo dia)
Schedule::command('billing:check')->dailyAt('23:55');

// Backup Automático Semanal - Roda todo Domingo às 03:00 (Substitui o anterior)
Schedule::command('tenant:backup-weekly')->weeklyOn(0, '03:00');

// Limpeza de Pré-Apostas (PINs) expiradas há mais de 5 horas (Roda a cada hora)
Schedule::command('prebets:cleanup')->hourly();
// Liquidação Automática de Apostas via API
// Processa bet_items (espelhos das apostas legado criadas pelo UnifiedBetService)
Schedule::command('ihub:settle-api-bets')->everyFiveMinutes();

// Liquidação Automática de Loto (Quininha/Seninha) - usa comando existente
Schedule::command('command:sendResultsQuina')->everyThirtyMinutes();
