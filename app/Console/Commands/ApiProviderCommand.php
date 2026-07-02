<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ApiProviderService;

class ApiProviderCommand extends Command
{
    protected $signature = 'api:provider {provider? : api-football ou bets-api}
                                     {--status : Mostra o provider atual}';

    protected $description = 'Gerencia o provedor de dados esportivos (API-Football ou BetsAPI)';

    public function handle(ApiProviderService $provider): int
    {
        if ($this->option('status')) {
            $this->info("Provedor atual: " . $provider->getProviderLabel());
            $this->info("API-Football: " . ($provider->isApiFootball() ? 'ATIVO' : 'inativo'));
            $this->info("BetsAPI: " . ($provider->isBetsApi() ? 'ATIVO' : 'inativo'));
            return self::SUCCESS;
        }

        $providerStr = $this->argument('provider');

        if (!$providerStr) {
            $choice = $this->choice('Selecione o provedor de dados', [
                ApiProviderService::PROVIDER_API_FOOTBALL => 'API-Football (api-sports.io)',
                ApiProviderService::PROVIDER_BETS_API => 'BetsAPI (betsapi.com)',
            ]);

            $providerStr = $choice;
        }

        if (!in_array($providerStr, [ApiProviderService::PROVIDER_API_FOOTBALL, ApiProviderService::PROVIDER_BETS_API])) {
            $this->error("Provedor invalido. Use: api-football ou bets-api");
            return self::FAILURE;
        }

        if ($provider->setActiveProvider($providerStr)) {
            $this->info("Provedor alterado para: " . $provider->getProviderLabel());
            return self::SUCCESS;
        }

        $this->error("Erro ao alterar provedor.");
        return self::FAILURE;
    }
}
