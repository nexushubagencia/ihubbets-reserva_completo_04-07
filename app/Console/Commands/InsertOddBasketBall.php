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

class InsertOddBasketBall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'command:InsertOddBasketBall {day=today}';

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
    private $sport_id = 18;

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
        if( $settings->op_basquete == 'Sim' && in_array('Basquete', $sports_available) ){
            $this->insertOdds();
        }else{
            $this->info("Basquete Desativado ou não suportado");
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
                            if( $line->name == 'Money Line' && $line->header == 1 ){
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
                            elseif( $line->name == 'Money Line' && $line->header == 2 ){
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
                    //Primeiro Quarto
                    if( isset($result->main->sp->{'1st_quarter'}) ){
                        foreach( $result->main->sp->{'1st_quarter'}->odds  as $line){
                            $market_name = $result->main->sp->{'1st_quarter'}->name;
                            if( $line->name == 'Money Line' && $line->header == 1 ){
                                // Casa
                                Odd::updateOrCreate([
                                    'uuid' => '1º Quarto'.'Casa'. $event_id
                                ],[
                                    'match_id'                  => $match_id,//$match_id,
                                    'event_id'                  => $event_id, 
                                    'market_name'              => '1º Quarto',
                                    'odd'                       => 'Casa - 1º Quarto',
                                    'mercado_full_name'         => '1º Quarto',
                                    'value'                   => $line->odds,
                                    'status'                    => 1,
                                    'selectionId'               => $event_id.'Casa'.'1º Quarto', 
                                    'state'                     => 'ACTIVE',
                                    'stateMarc'                 => 'OPEN',
                                    'order'                     => 1,
                                    'header'                    => $line->header,
                                    'type'                      => "pre"
                                ]);  
                            }
                            elseif( $line->name == 'Money Line' && $line->header == 2 ){
                                // Casa
                                Odd::updateOrCreate([
                                    'uuid' => '1º Quarto'.'Fora'. $event_id
                                ],[
                                    'match_id'                  => $match_id,//$match_id,
                                    'event_id'                  => $event_id, 
                                    'market_name'              => '1º Quarto',
                                    'odd'                       => 'Fora - 1º Quarto',
                                    'mercado_full_name'         => '1º Quarto',
                                    'value'                   => $line->odds,
                                    'status'                    => 1,
                                    'selectionId'               => $event_id.'Fora'.'1º Quarto', 
                                    'state'                     => 'ACTIVE',
                                    'stateMarc'                 => 'OPEN',
                                    'order'                     => 1,
                                    'header'                    => $line->header,
                                    'type'                      => "pre"
                                ]);  
                            }
                        }
                    }
                    if(isset($result->others)){
                        foreach( $result->others as $other ){
                            //Segundo Quarto
                            if( isset($other->sp->{'2nd_quarter'}) ){
                                foreach( $other->sp->{'2nd_quarter'}->odds  as $line){
                                    if( $line->name == 'Money Line' && $line->header == 1 ){
                                        // Casa
                                        Odd::updateOrCreate([
                                            'uuid' => '2º Quarto'.'Casa'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '2º Quarto',
                                            'odd'                       => 'Casa - 2º Quarto',
                                            'mercado_full_name'         => '2º Quarto',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Casa'.'2º Quarto', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Money Line' && $line->header == 2 ){
                                        // Casa
                                        Odd::updateOrCreate([
                                            'uuid' => '2º Quarto'.'Fora'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '2º Quarto',
                                            'odd'                       => 'Fora - 2º Quarto',
                                            'mercado_full_name'         => '2º Quarto',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Fora'.'2º Quarto', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //Terceiro Quarto
                            if( isset($other->sp->{'3rd_quarter'}) ){
                                foreach( $other->sp->{'3rd_quarter'}->odds  as $line){
                                    if( $line->name == 'Money Line' && $line->header == 1 ){
                                        // Casa
                                        Odd::updateOrCreate([
                                            'uuid' => '3º Quarto'.'Casa'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '3º Quarto',
                                            'odd'                       => 'Casa - 3º Quarto',
                                            'mercado_full_name'         => '3º Quarto',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Casa'.'3º Quarto', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Money Line' && $line->header == 2 ){
                                        // Casa
                                        Odd::updateOrCreate([
                                            'uuid' => '3º Quarto'.'Fora'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '3º Quarto',
                                            'odd'                       => 'Fora - 3º Quarto',
                                            'mercado_full_name'         => '3º Quarto',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Fora'.'3º Quarto', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //Quarto Quarto
                            if( isset($other->sp->{'4th_quarter'}) ){
                                foreach( $other->sp->{'4th_quarter'}->odds  as $line){
                                    if( $line->name == 'Money Line' && $line->header == 1 ){
                                        // Casa
                                        Odd::updateOrCreate([
                                            'uuid' => '4º Quarto'.'Casa'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '4º Quarto',
                                            'odd'                       => 'Casa - 4º Quarto',
                                            'mercado_full_name'         => '4º Quarto',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Casa'.'4º Quarto', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Money Line' && $line->header == 2 ){
                                        // Casa
                                        Odd::updateOrCreate([
                                            'uuid' => '4º Quarto'.'Fora'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '4º Quarto',
                                            'odd'                       => 'Fora - 4º Quarto',
                                            'mercado_full_name'         => '4º Quarto',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Fora'.'4º Quarto', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //Haverá Prolongamento?
                            if( isset($other->sp->{'tied_at_end_of_regulation?'}) ){
                                break;
                                foreach( $other->sp->{'tied_at_end_of_regulation?'}->odds  as $line){
                                    $market_name = $other->sp->{'tied_at_end_of_regulation?'}->name;
                                    if( $line->name == 'Yes' ){
                                        // Impar
                                        Odd::updateOrCreate([
                                            'uuid' => 'Haverá Prolongamento? - Sim'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => 'Haverá Prolongamento?',
                                            'odd'                       => 'Prolongamento - Sim',
                                            'mercado_full_name'         => 'Haverá Prolongamento?',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Prolongamento - Sim'.'Haverá Prolongamento?', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'No' ){
                                        // Par
                                        Odd::updateOrCreate([
                                            'uuid' => 'Haverá Prolongamento? - Não'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => 'Haverá Prolongamento?',
                                            'odd'                       => 'Prolongamento? - Não',
                                            'mercado_full_name'         => 'Haverá Prolongamento?',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Haverá Prolongamento? - Não'.'Haverá Prolongamento?', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //Total de Pontos - Ímpar/Par
                            if( isset($other->sp->game_total_odd_even) ){
                                foreach( $other->sp->game_total_odd_even->odds  as $line){
                                    $market_name = $other->sp->game_total_odd_even->name;
                                    if( $line->name == 'Odd' ){
                                        // Impar
                                        Odd::updateOrCreate([
                                            'uuid' => 'Total de Pontos - Par ou Ímpar'.'Ímpar'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => 'Total de Pontos - Par ou Ímpar',
                                            'odd'                       => 'Total de Pontos - Ímpar',
                                            'mercado_full_name'         => 'Total de Pontos - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Total de Pontos - Ímpar'.'Total de Pontos - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Even' ){
                                        // Par
                                        Odd::updateOrCreate([
                                            'uuid' => 'Total de Pontos - Par ou Ímpar'.'Par'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => 'Total de Pontos - Par ou Ímpar',
                                            'odd'                       => 'Total de Pontos - Par',
                                            'mercado_full_name'         => 'Total de Pontos - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Total de Pontos - Par'.'Total de Pontos - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //1º Quarto - Par ou Ímpar
                            if( isset($other->sp->{'1st_quarter_total_odd_even'}) ){
                                foreach( $other->sp->{'1st_quarter_total_odd_even'}->odds  as $line){
                                    $market_name = $other->sp->{'1st_quarter_total_odd_even'}->name;
                                    if( $line->name == 'Odd' ){
                                        // Impar
                                        Odd::updateOrCreate([
                                            'uuid' => '1º Quarto - Par ou Ímpar'.'Ímpar'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '1º Quarto - Par ou Ímpar',
                                            'odd'                       => '1º Quarto - Ímpar',
                                            'mercado_full_name'         => '1º Quarto - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Ímpar'.'1º Quarto - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Even' ){
                                        // Par
                                        Odd::updateOrCreate([
                                            'uuid' => '1º Quarto - Par ou Ímpar'.'Par'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '1º Quarto - Par ou Ímpar',
                                            'odd'                       => '1º Quarto - Par',
                                            'mercado_full_name'         => '1º Quarto - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Par'.'1º Quarto - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //2º Quarto - Par ou Ímpar
                            if( isset($other->sp->{'2nd_quarter_total_odd_even'}) ){
                                foreach( $other->sp->{'2nd_quarter_total_odd_even'}->odds  as $line){
                                    $market_name = $other->sp->{'2nd_quarter_total_odd_even'}->name;
                                    if( $line->name == 'Odd' ){
                                        // Impar
                                        Odd::updateOrCreate([
                                            'uuid' => '2º Quarto - Par ou Ímpar'.'Ímpar'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '2º Quarto - Par ou Ímpar',
                                            'odd'                       => '2º Quarto - Ímpar',
                                            'mercado_full_name'         => '2º Quarto - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Ímpar'.'2º Quarto - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Even' ){
                                        // Par
                                        Odd::updateOrCreate([
                                            'uuid' => '2º Quarto - Par ou Ímpar'.'Par'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '2º Quarto - Par ou Ímpar',
                                            'odd'                       => '2º Quarto - Par',
                                            'mercado_full_name'         => '2º Quarto - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Par'.'2º Quarto - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //3º Quarto - Par ou Ímpar
                            if( isset($other->sp->{'3rd_quarter_total_odd_even'}) ){
                                foreach( $other->sp->{'3rd_quarter_total_odd_even'}->odds  as $line){
                                    $market_name = $other->sp->{'3rd_quarter_total_odd_even'}->name;
                                    if( $line->name == 'Odd' ){
                                        // Impar
                                        Odd::updateOrCreate([
                                            'uuid' => '3º Quarto - Par ou Ímpar'.'Ímpar'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '3º Quarto - Par ou Ímpar',
                                            'odd'                       => '3º Quarto - Ímpar',
                                            'mercado_full_name'         => '3º Quarto - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Ímpar'.'3º Quarto - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Even' ){
                                        // Par
                                        Odd::updateOrCreate([
                                            'uuid' => '3º Quarto - Par ou Ímpar'.'Par'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '3º Quarto - Par ou Ímpar',
                                            'odd'                       => '3º Quarto - Par',
                                            'mercado_full_name'         => '3º Quarto - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Par'.'3º Quarto - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //3º Quarto - Par ou Ímpar
                            if( isset($other->sp->{'4th_quarter_total_odd_even'}) ){
                                foreach( $other->sp->{'4th_quarter_total_odd_even'}->odds  as $line){
                                    $market_name = $other->sp->{'4th_quarter_total_odd_even'}->name;
                                    if( $line->name == 'Odd' ){
                                        // Impar
                                        Odd::updateOrCreate([
                                            'uuid' => '4º Quarto - Par ou Ímpar'.'Ímpar'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '4º Quarto - Par ou Ímpar',
                                            'odd'                       => '4º Quarto - Ímpar',
                                            'mercado_full_name'         => '4º Quarto - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Ímpar'.'4º Quarto - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                    elseif( $line->name == 'Even' ){
                                        // Par
                                        Odd::updateOrCreate([
                                            'uuid' => '4º Quarto - Par ou Ímpar'.'Par'. $event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => '4º Quarto - Par ou Ímpar',
                                            'odd'                       => '4º Quarto - Par',
                                            'mercado_full_name'         => '4º Quarto - Par ou Ímpar',
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => $event_id.'Par'.'4º Quarto - Par ou Ímpar', 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }
                                }
                            }
                            //Total de Pontos no jogo
                            if( isset($other->sp->alternative_team_totals_2) ){
                                foreach( $other->sp->alternative_team_totals_2->odds  as $line){
                                    $market_name = $other->sp->alternative_team_totals_2->name;
                                    $odd_header = str_replace(['Over','Under'],['Mais de', 'Menos de'],$line->header);
                                    $odd_team = str_replace(['1','2'],['Casa', 'Fora'],$line->team);
                                    if($odd_team == 'Casa'){
                                        //Casa
                                        Odd::updateOrCreate([
                                            'uuid' => 'Total de Pontos'.$odd_team.$event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => "{$odd_team} - Total de Pontos",
                                            'odd'                       => "{$odd_team} - {$odd_header} {$line->name}",
                                            'mercado_full_name'         => "{$odd_team} Total de Pontos",
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => "{$event_id}{$odd_team} {$odd_header}{$line->name} Total de Pontos", 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
                                            'header'                    => $line->header ?? null,
                                            'type'                      => "pre"
                                        ]);  
                                    }else{
                                        //Fora
                                        Odd::updateOrCreate([
                                            'uuid' => 'Total de Pontos'.$odd_team.$event_id
                                        ],[
                                            'match_id'                  => $match_id,//$match_id,
                                            'event_id'                  => $event_id, 
                                            'market_name'              => "{$odd_team} - Total de Pontos",
                                            'odd'                       => "{$odd_team} - {$odd_header} {$line->name}",
                                            'mercado_full_name'         => "{$odd_team} Total de Pontos",
                                            'value'                   => $line->odds,
                                            'status'                    => 1,
                                            'selectionId'               => "{$event_id}{$odd_team} {$odd_header}{$line->name} Total de Pontos", 
                                            'state'                     => 'ACTIVE',
                                            'stateMarc'                 => 'OPEN',
                                            'order'                     => 1,
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
