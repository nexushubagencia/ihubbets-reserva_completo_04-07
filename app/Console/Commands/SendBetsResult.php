<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendBetsResult extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendBetResult';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Este comando está desabilitado. Use sendResultsQuina ou sendResultSena.');
        $this->warn('O job SendResults não existe mais.');
        return 0;

    }
}
