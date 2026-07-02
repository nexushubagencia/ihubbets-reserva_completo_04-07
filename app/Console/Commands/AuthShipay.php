<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ShipayService;
use App\Models\Configuracao;

class AuthShipay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipay:auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate shipay authentication token';

	private $shipayService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Configuracao $configuration, ShipayService $shipayService)
    {
        parent::__construct();
    	$this->configuration = $configuration;
    	$this->shipayService = $shipayService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	try {
    	$siteId = config('tenant.site_id', 1);
    	$this->configuration::where('site_id', $siteId)->update(['shipay_token' => $this->shipayService->auth()->access_token]);
    	$this->info('Success Generated!');
    	} catch (Exception $e) {
    	$this->error('Error');
    	}
    }
}
