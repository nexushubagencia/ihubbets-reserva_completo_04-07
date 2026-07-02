<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MatchModel;
use App\Models\Mercado;
use App\Models\Odd;
use App\Models\GerenciadorCron;
use App\Models\ListleaguesMain;
use App\Models\Configuracao;
use Carbon\Carbon;
use DB;

class InsertOddVolleyBall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'command:InsertOddVolleyBall {day=today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $agora;
    private $today;
    private $tomorow;
    private $day;
    private $sport_id = 91;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->agora    = $agora    = Carbon::now()->format('Y-m-d H:i:s');
        $this->today    = $today    = Carbon::today()->format('Y-m-d');
        $this->now      = $now      = Carbon::now();
        $this->tomorow  = Carbon::tomorrow()->format('Y-m-d');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $siteId = config('tenant.site_id', 1);
        $settings = Configuracao::where('site_id', $siteId)->first();
        $sports_available = config('app.sports_available');
        if( $settings->op_volei == 'Sim' && in_array('Volei', $sports_available) ){
            $this->insertOdds();
        }else{
            $this->info("Volei Desativado ou não suportado");
        }
    }

    protected function insertOdds()
    {
        $this->day = $this->argument('day');
        $siteId = config('tenant.site_id', 1);

        if($this->day == 'after-tomorow'){
            $matchDelete = MatchModel::where('date' ,'<=', Carbon::parse($this->today)->subDay(20)->format('Y-m-d').' 23:59:59')->where('sport_id', $this->sport_id)->where('site_id', $siteId)->delete();
        }

        $matchs = MatchModel::select('id', 'event_id', 'date', 'home', 'away')
        ->where('time_status', 0)
        ->where('sport_id',$this->sport_id)
        ->where('site_id', $siteId);
        if($this->day == 'today'){
            $matchs = $matchs->where('date' ,'<=', $this->today.' 23:59:59');
        }elseif( $this->day == 'tomorow' ){
            $matchs = $matchs->where('date' , '>', $this->today)
            ->where('date' ,'<=',  $this->tomorow." 23:59:59");
        }elseif( $this->day == 'after-tomorow' ){
            $matchs = $matchs->where('date' , '>',  $this->tomorow." 23:59:59");
        }

        $matchs = $matchs->orderBy('date', 'ASC')->get();

        $num = 0;
        $arrays = 0;
        $arrMatchs = array();
        $arrMatchs[0] = [];
        
        foreach($matchs as $match) {
            $url =  'https://api.b365api.com/v3/bet365/prematch?token='.env('TOKEN').'&FI='.$match->event_id;
            $tmp = 0;

            echo $url."\n";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            if ($data === false) {
                $info = curl_getinfo($ch);
                curl_close($ch);
                die('error occured during curl exec. Additioanl info: ' . var_export($info));
            }
            curl_close($ch);
            $data = json_decode($data);
            if(isset($data->results)) {
                foreach($data->results as $result) {
                    $match_id  = $match->id;
                    $event_id  = $match->event_id;

                    //Vencedor do Encontro
                    if( isset($result->main->sp->game_lines) ){
                        foreach( $result->main->sp->game_lines->odds  as $line){
                            $market_name = $result->main->sp->game_lines->name;
                            if( $line->name == 'To Win' && $line->header == 1 ){
                                // Casa
                                Odd::updateOrCreate([
                                    'uuid' => 'Vencedor do Encontro'.'Casa'. $event_id
                                ],[
                                    'match_id'                  => $match_id,//$match_id,
                                    'event_id'                  => $event_id, 
                                    'market_name'              => 'Vencedor do Encontro',
                                    'odd'                       => 'Casa',
                                    'mercado_full_name'         => 'Vencedor do Encontro',
                                    'value'                   => $line->odds,
                                    'status'                    => 1,
                                    'selectionId'               => $event_id.'Casa'.'Vencedor do Encontro', 
                                    'state'                     => 'ACTIVE',
                                    'stateMarc'                 => 'OPEN',
                                    'order'                     => 0,
                                    'header'                    => $line->header,
                                    'type'                      => "pre"
                                ]);  
                            }
                            elseif( $line->name == 'To Win' && $line->header == 2 ){
                                // Casa
                                Odd::updateOrCreate([
                                    'uuid' => 'Vencedor do Encontro'.'Fora'. $event_id
                                ],[
                                    'match_id'                  => $match_id,//$match_id,
                                    'event_id'                  => $event_id, 
                                    'market_name'              => 'Vencedor do Encontro',
                                    'odd'                       => 'Fora',
                                    'mercado_full_name'         => 'Vencedor do Encontro',
                                    'value'                   => $line->odds,
                                    'status'                    => 1,
                                    'selectionId'               => $event_id.'Fora'.'Vencedor do Encontro', 
                                    'state'                     => 'ACTIVE',
                                    'stateMarc'                 => 'OPEN',
                                    'order'                     => 0,
                                    'header'                    => $line->header,
                                    'type'                      => "pre"
                                ]);  
                            }
                        }
                    }
                    //Placar Exato
                    if( isset($result->main->sp->correct_set_score) ){
                        foreach( $result->main->sp->correct_set_score->odds  as $line){
                            $market_name = $result->main->sp->correct_set_score->name;
                            $header = str_replace(['1','2'],['Casa', 'Fora'],$line->header);
                            $odd_name = "{$header} - {$line->name}";
                            Odd::updateOrCreate([
                                'uuid' => 'Placar Exato'.$odd_name. $event_id
                            ],[
                                'match_id'                  => $match_id,//$match_id,
                                'event_id'                  => $event_id, 
                                'market_name'              => 'Placar Exato',
                                'odd'                       => $odd_name,
                                'mercado_full_name'         => 'Placar Exato',
                                'value'                   => $line->odds,
                                'status'                    => 1,
                                'selectionId'               => $event_id.$odd_name.'Placar Exato', 
                                'state'                     => 'ACTIVE',
                                'stateMarc'                 => 'OPEN',
                                'order'                     => 1,
                                'header'                    => $line->header,
                                'type'                      => "pre"
                            ]);  
                        }
                    }
                    if(isset($result->others)){
                        foreach( $result->others as $other ){
                            //1º Set - Par ou Ímpar
                            if( isset($other->sp->{'1st_set_lines'}) ){
                                foreach( $other->sp->{'1st_set_lines'}->odds  as $line){
                                    if( $line->name == 'To Win' && $line->header == 1 ){
                                        // Casa
                                        Odd::updateOrCreate([
                                            'uuid' => '1º Set'.'Casa'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '1º Set',
                                            'odd'                       => 'Casa - 1º Set',
                                            'mercado_full_name'         => '1º Set',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Casa'.'1º Set', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'To Win' && $line->header == 2 ){
                                        // Casa
                                        Odd::updateOrCreate([
                                            'uuid' => '1º Set'.'Fora'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '1º Set',
                                            'odd'                       => 'Fora - 1º Set',
                                            'mercado_full_name'         => '1º Set',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Fora'.'1º Set', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //1º Set - Par ou Ímpar
                            if( isset($other->sp->{'1st_set_total_odd_even'}) ){
                                foreach( $other->sp->{'1st_set_total_odd_even'}->odds  as $line){
                                    $market_name = $other->sp->{'1st_set_total_odd_even'}->name;
                                    if( $line->name == 'Odd' ){
                                        //Ímpar
                                        Odd::updateOrCreate([
                                            'uuid' => '1º Set - Par ou Ímpar'.'Ímpar'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '1º Set - Par ou Ímpar',
                                            'odd'                       => '1º Set - Ímpar',
                                            'mercado_full_name'         => '1º Set - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Ímpar'.'1º Set - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 2,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Even' ){
                                        // Par
                                        Odd::updateOrCreate([
                                            'uuid' => '1º Set - Par ou Ímpar'.'Par'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '1º Set - Par ou Ímpar',
                                            'odd'                       => '1º Set - Par',
                                            'mercado_full_name'         => '1º Set - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Par'.'1º Set - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 2,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
