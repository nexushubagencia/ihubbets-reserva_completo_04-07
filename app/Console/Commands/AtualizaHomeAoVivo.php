<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Odd;
use App\Models\LiveMatch;
use Carbon\Carbon;

class AtualizaHomeAoVivo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:atualizaliveHome';
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
        $this->now      = $now = $agora = Carbon::now();
      
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   

        $matchsDel = MatchModel::where('date', '<', $this->now->subHour(2)->format('Y-m-d H:i:s'))->where('time_status', 1)->get();
       
     
        foreach($matchsDel as $match) {
            $m = MatchModel::find($match->id);
            $m->delete();
        } 

        $url = 'https://api.betsapi.com/v1/betfair/sb/inplay?sport_id=1&token='.env('TOKEN');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $matchs = curl_exec($ch);
        if ($matchs === false) {
            $info = curl_getinfo($ch);
            curl_close($ch);

            die('error occured during curl exec. Additioanl info: ' . var_export($info));

        }
        curl_close($ch);


        $matchs = json_decode($matchs);


        $pagina = intval(ceil($matchs->pager->total/$matchs->pager->per_page));

        $cont = 1;
        $j=0;
        for($i=1; $i<= ($pagina); $i++) {
            $url2 = 'https://api.betsapi.com/v1/betfair/sb/inplay?sport_id=1&token='.env('TOKEN').'&page='.$i;
            $ch2 = curl_init($url2);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            $matchs2 = curl_exec($ch2);
            $m = json_decode($matchs2);

            if ($matchs2 === false) {
                $info2 = curl_getinfo($ch2);
                curl_close($ch2);

                die('error occured during curl exec. Additioanl info: ' . var_export($info2));


            }
            curl_close($ch2);

         
            if(isset($m->results)) {
                foreach($m->results as $match) {
                    $event = MatchModel::where('event_id', $match->id)->first();
                    if($event) {
                        $event->time_status = $match->time_status;
                        $event->save();
                    }
                   
                }
          
            }

           


            

        




        }

      
       



    }
}
