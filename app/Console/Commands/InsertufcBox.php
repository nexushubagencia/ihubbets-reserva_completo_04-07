<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Mercado;
use App\Models\Odd;
use Carbon\Carbon;

class InsertufcBox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:insertUfcBox';

    private $arr = array();
    private $hoje;
    private $amanha;
    private $token;
    private $configuracao;
    private $matchUpdate;


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
        $this->hoje     = $hoje = Carbon::today()->format('Ymd');
        $this->amanha   = $amanha = Carbon::tomorrow()->format('Ymd');
        $this->token    = $token = env('TOKEN', '84995-cSCzCIUeN54NHf');
    

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $siteId = config('tenant.site_id', 1);

        $c = 0;
        $result = array();
        $url = 'https://api.b365api.com/v1/bet365/upcoming?sport_id=9&LNG_ID=22&token='.$this->token;
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

        //$this->arr[] = $matchs->results;

        $pagina = intval($matchs->pager->total/$matchs->pager->per_page);
        //dd($pagina);
        $cont = 1;
        $j=0;
        for($i=1; $i<= ($pagina); $i++) {
            $url2 = 'https://api.b365api.com/v1/bet365/upcoming?sport_id=9&LNG_ID=22&token='.$this->token.'&page='.$i;
            //dd($url2);
            $ch2 = curl_init($url2);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            $matchs2 = curl_exec($ch2);
            $m = json_decode($matchs2);

            //print_r($m);
            //$this->arr[$j] = ;
            //echo $cont++."\n";

            //print_r($m);


            foreach($m->results as $mr) {
                $this->arr[] = $mr;
              }


            if ($matchs2 === false) {
                $info2 = curl_getinfo($ch2);
                curl_close($ch2);


                die('error occured during curl exec. Additioanl info: ' . var_export($info2));


            }
            curl_close($ch2);
            $j++;
        }


        foreach($this->arr as $match) {
            $dt = Carbon::now('America/Sao_Paulo');
            $dt->timestamp($match->time);
            $date = $dt->format('Y-m-d H:i:s');

            if($match->time_status == 0) {

                if(count(MatchModel::where('event_id', $match->id)->where('site_id', $siteId)->get())>0){
                    echo "Paritda Existe no sistema.<br/>";
                }else{

                   // DB::beginTransaction();
                    $matchsIn = MatchModel::create([
                        'site_id'              => $siteId,
                        'event_id'              => $match->id,
                        'sport_id'              => 9,
                        'sport_name'            => 'Ufc/Boxe',
                        'order'                 => 0,
                        'visible'               => 'Não',
                        'schedule'              => 0,
                        'date'                  => $date,
                        'league'                => $match->league->name,
                        'home'                  => $match->home->name,
                        'away'                  => $match->away->name,
                    ]);

                    $match_id = $matchsIn->id;

                    echo "Partida cadastrada com sucesso\n";

                }

            }
        }

    }
}
