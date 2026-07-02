<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Marcket;
use App\Models\Odd;

class InsertMercado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:insertMercado {--event_id= : ID do evento para importar mercados}';

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
        $eventId = $this->option('event_id') ?? 29923346;
        $mercados = Odd::where('event_id', $eventId)
                   ->orderBy('order', 'ASC')
                   ->get();

        foreach($mercados as $mercado) {

            $market = Marcket::where('name', $mercado->mercado_name)->first();

            if($market) {

                echo "Existe o Mercado\n";

            } else {

                Marcket::create([
                    'name' => $mercado->mercado_name,
                    'order' => $mercado->order
                ]);

            }
        }
        
    }
}
