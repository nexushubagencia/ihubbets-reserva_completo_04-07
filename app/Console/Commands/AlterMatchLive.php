<?php

namespace App\Console\Commands;

use App\Models\BlockOddMatch;
use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Odd;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AlterMatchLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:alterMatchLive';

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

    public function matchsLive()
    {

        $url = 'https://api.betsapi.com/v1/betfair/sb/inplay?sport_id=1&token=' . env('TOKEN');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $matchs = curl_exec($ch);
          Log::info($matchs);
        if ($matchs === false) {
            $info = curl_getinfo($ch);
            curl_close($ch);

            die('error occured during curl exec. Additioanl info: ' . var_export($info));
        }
        curl_close($ch);
        
        $matchs = json_decode($matchs);
      
        $pagina = intval(ceil($matchs->pager->total / $matchs->pager->per_page));

        $cont = 1;

        $num = 0;
        $arrays = 0;
        $arrMatchs = array();
        $arrMatchs[0] = [];
        for ($i = 1; $i <= ($pagina); $i++) {
            $url2 = 'https://api.betsapi.com/v1/betfair/sb/inplay?sport_id=1&token=' . env('TOKEN') . '&page=' . $i;
            $ch2 = curl_init($url2);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            $matchs2 = curl_exec($ch2);
            $m = json_decode($matchs2);

          
            foreach ($m->results as $match) {

                if($num > 9) {
                    $arrays++;
                    $arrMatchs[$arrays] = array();
                    $num =0;
                } 

                array_push($arrMatchs[$arrays] , $match->id);

                $num++;

                $event = MatchModel::where('event_id', $match->id)->first();

                if($event) {
                    //Atualiza
                    echo $event->event_id."\n";
                    $event->time_status = $match->time_status;
                    $event->visible = 'Sim';
                    $event->save();
                //Cria
                } else {

                }
                
            }
        }
      
    }
    public function handle()
    {
        $this->matchsLive();
    }
}