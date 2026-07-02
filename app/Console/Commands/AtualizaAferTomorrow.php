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
use App\Models\AferTomorrow;
use App\Models\AfterTomorowMatchFlash;
use App\Models\BlockOddMatch;

class AtualizaAferTomorrow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:atualizaAfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private $amanha;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->amanha   = $amanha   = Carbon::tomorrow();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $return = array();

        $aferTomorow = $this->amanha->copy()->addDay()->format('Y-m-d');


        $leagues = MatchModel::select('league')
                        ->where('date' ,'>', $aferTomorow.' 00:00:01')
                        ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                        ->where( 'visible', 'Sim')
                        ->groupBy('league')
                        ->with('odds')
                        ->orderBy('league', 'asc')
                        ->get();
   

        $i=0;

        foreach($leagues  as $league) {


                    $return[$i]['league'] = $league->league;

                   $matchs = MatchModel::where('league', $league->league)
                                ->where('date' ,'>', $aferTomorow.' 00:00:01')
                                ->where('date' ,'<=', $aferTomorow.' 23:59:00')
                                ->where( 'visible', 'Sim')
                                ->get();



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
      
            $match_home = AfterTomorowMatchFlash::all();
    	    
    		foreach ($match_home as $match) {
                 $update = $match->update([
                    'dados' => json_encode($return),
                ]);
            }
          

    }
}
