<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\MatchModel;
use App\Models\Mercado;
use App\Models\Odd;
use App\Models\BlockLeague;
use App\Models\BlockMatch;
use App\Models\ConfigMercados;
use App\Models\ConfigOdd;
use App\Models\Configuracao;
use App\Models\AmanhaMatch;
use App\Models\AfterTomorowMatchFlash;
use App\Models\BlockOddMatch;

class AtualizaAfterTomorow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AtualizaAfterTomorow {sport_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $hoje;
    private $amanha;
    private $agora;
    private $token;
    private $matchs;
    private $sport_id;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->hoje     = $hoje     = Carbon::today();
        $this->amanha   = $amanha   = Carbon::tomorrow();
        $this->agora    = $agora    = Carbon::now();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->sport_id = $this->argument('sport_id');

        $data_hoje =  date('Y-m-d', strtotime($this->hoje));
        $data_amanha = date('Y-m-d', strtotime($this->amanha));
        $return = array();
        $aferTomorow = $this->amanha->copy()->addDay()->format('Y-m-d');


        $data_hoje =  date('Y-m-d', strtotime($this->hoje));

        $leagues = MatchModel::select('league','league_cc')
                              ->where('sport_id', $this->sport_id )
                              ->where('date' ,'>', $aferTomorow.' 00:00:01')
                              ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                              ->where( 'visible', 'Sim')
                              ->groupBy('league')
                              ->with('odds')
                              ->orderBy('league', 'asc')
                              ->with('odds');
                            if($this->sport_id != 1){
                                $leagues = $leagues->whereHas('odds');
                            }
                            $leagues = $leagues->get();



                              $i=0;

                              foreach($leagues  as $league) {
                                        if($this->sport_id == 1){
                                            $return[$i]['league'] = $league->league;
                                            $return[$i]['league_cc'] = $league->league_cc;
                                        }

                                         $matchs = MatchModel::where('league', $league->league)
                                                            ->where('sport_id', $this->sport_id )
                                                            ->where('date' ,'>', $aferTomorow.' 00:00:01')
                                                            ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                                                            ->where( 'visible', 'Sim');
                                                            if($this->sport_id != 1){
                                                                $matchs = $matchs->whereHas('odds');
                                                              }
                                        $matchs = $matchs->get();
                                        if($matchs->count() && $this->sport_id != 1){
                                            $return[$i]['league'] = $league->league;
                                            $return[$i]['league_cc'] = $league->league_cc;
                                        }

                                          $j=0;
                                          foreach($matchs as $match) {


                                            $return[$i]['match'][$j]['id'] = $match->id;
                                            $return[$i]['match'][$j]['event_id'] = $match->event_id;
                                            $return[$i]['match'][$j]['sport'] = $match->sport_name;
                                            $return[$i]['match'][$j]['league'] = $league->league;
                                            $return[$i]['match'][$j]['confronto'] = $match->confronto;
                                            $return[$i]['match'][$j]['home'] = $match->home;
                                            $return[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                                            $return[$i]['match'][$j]['away'] = $match->away;
                                            $return[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                                            $return[$i]['match'][$j]['date'] = $match->date;
                                            $count = Odd::where('event_id', $match->id)->count();
                                            $return[$i]['match'][$j]['count_odd'] = $count;

                                                  $q=0;
                                                  if($count == 0) {

                                                    for($q = 0; $q < 3; $q++) {
                                                        $return[$i]['match'][$j]['odds'][$q]['type'] = 'NULL';
                                                        $return[$i]['match'][$j]['odds'][$q]['id'] = $match->event_id.$match->id.$q;
                                                        $return[$i]['match'][$j]['odds'][$q]['group_opp'] = 'Vencedor do Encontro';
                                                        $return[$i]['match'][$j]['odds'][$q]['odd'] = $q;
                                                        $return[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                                                    }
                        
                                                } else {
    
                                                      $q=0;
                                                      foreach($match->odds as $odd) {
    
                                                            $return[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                                                            $return[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->market_name;
                                                            $return[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                                                            $return[$i]['match'][$j]['odds'][$q]['cotacao'] = $odd->value;
                                                            $return[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
    
    
                                                      $q++;
                                                      }
    
                                                    }

                                      $j++;
                                      }






                                 $i++;
                              }

                              AfterTomorowMatchFlash::updateOrCreate([
                                    'site_id'   => config('tenant.site_id', 1),
                                    'sport_id'  => $this->sport_id
                                ],[
                                    'dados'     => json_encode($return),
                                ]);

    }
}
