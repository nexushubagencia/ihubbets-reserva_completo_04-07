<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MatchModel;
use App\Models\Odd;
use App\Models\BlockLeague;
use App\Models\BlockMatch;
use App\Models\BlockOddMatch;
use App\Models\MainLeague;
use Carbon\Carbon;

class ConfrontosController extends Controller
{
    private $hoje;
    private $amanha;
    private $agora;

    public function __construct()
    {
        $this->hoje   = Carbon::today();
        $this->amanha = Carbon::tomorrow();
        $this->agora  = Carbon::now();
    }

    public function indexView()
    {
        return view('admin.confrontos');
    }

    public function viewAovivo()
    {
        return view('admin.confrontos-aovivo');
    }

    public function indexAovivo()
    {
        $siteId = app('tenant.site_id');

        $bloqueadas = BlockLeague::where('site_id', $siteId)->get();
        $leageBlock = array();
        foreach ($bloqueadas as $bloqueada) {
            $leageBlock[] = $bloqueada->league;
        }

        $block_match = array();
        $matchs_bloqueadas = BlockMatch::where('site_id', $siteId)->get();
        foreach ($matchs_bloqueadas as $match_bloqueada) {
            $block_match[] = $match_bloqueada->event_id;
        }

        $return = array();
        $leagues = MatchModel::select('league')
            ->where('time_status', 1)
            ->whereNotIn('league', $leageBlock)
            ->whereNotIn('event_id', $block_match)
            ->groupBy('league')
            ->orderBy('league', 'asc')
            ->get();

        $i = 0;
        foreach ($leagues as $league) {
            $return[$i]['league'] = $league->league;

            $matchs = MatchModel::where('league', $league->league)
                ->where('time_status', 1)
                ->with('odds')
                ->get();

            $j = 0;
            foreach ($matchs as $match) {
                $return[$i]['match'][$j]['id'] = $match->id;
                $return[$i]['match'][$j]['event_id'] = $match->event_id;
                $return[$i]['match'][$j]['sport'] = $match->sport_name;
                $return[$i]['match'][$j]['home'] = $match->home;
                $return[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                $return[$i]['match'][$j]['away'] = $match->away;
                $return[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                $return[$i]['match'][$j]['score'] = $match->score;
                $return[$i]['match'][$j]['date'] = $match->date;
                $return[$i]['match'][$j]['time'] = $match->time;
                $return[$i]['match'][$j]['visible'] = $match->visible;
                $j++;
            }
            $i++;
        }

        return $return;
    }

    public function index()
    {
        $siteId = app('tenant.site_id');
        $aferTomorow = $this->amanha->addDay()->format('Y-m-d');

        $arr = array();
        $bloqueadas = BlockLeague::where('site_id', $siteId)->get();
        foreach ($bloqueadas as $bloqueada) {
            $arr[] = $bloqueada->league;
        }

        $block_match = array();
        $matchs_bloqueadas = BlockMatch::where('site_id', $siteId)->get();
        foreach ($matchs_bloqueadas as $match_bloqueada) {
            $block_match[] = $match_bloqueada->event_id;
        }

        $return = array();
        $leagues = MatchModel::select('league')
            ->where('date', '>=', $this->agora)
            ->where('date', '<=', $aferTomorow . ' 23:59:00')
            ->where('visible', 'Sim')
            ->whereNotIn('league', $arr)
            ->groupBy('league')
            ->orderBy('league', 'asc')
            ->get();

        $i = 0;
        foreach ($leagues as $league) {
            $return[$i]['league'] = $league->league;

            $matchs = MatchModel::where('league', $league->league)
                ->where('date', '>=', $this->agora)
                ->where('date', '<=', $aferTomorow . ' 23:59:00')
                ->where('visible', 'Sim')
                ->whereNotIn('event_id', $block_match)
                ->get();

            $j = 0;
            foreach ($matchs as $match) {
                $return[$i]['match'][$j]['id'] = $match->id;
                $return[$i]['match'][$j]['sport'] = $match->sport_name;
                $return[$i]['match'][$j]['event_id'] = $match->event_id;
                $return[$i]['match'][$j]['home'] = $match->home;
                $return[$i]['match'][$j]['away'] = $match->away;
                $return[$i]['match'][$j]['date'] = $match->date;
                $return[$i]['match'][$j]['visible'] = $match->visible;
                $j++;
            }
            $i++;
        }

        return $return;
    }

    public function searchMatch(Request $request)
    {
        $siteId = app('tenant.site_id');
        $data = date('Y-m-d', strtotime($request->date));

        $block_league = array();
        $leagues_bloqueadas = BlockLeague::where('site_id', $siteId)->get();
        foreach ($leagues_bloqueadas as $league_bloqueada) {
            $block_league[] = $league_bloqueada->league;
        }

        $block_match = array();
        $matchs_bloqueadas = BlockMatch::where('site_id', $siteId)->get();
        foreach ($matchs_bloqueadas as $match_bloqueada) {
            $block_match[] = $match_bloqueada->event_id;
        }

        $return = array();
        $leagues = MatchModel::select('league')
            ->where('date', '>=', $this->agora)
            ->where('date', '>', $data . ' 00:00:00')
            ->where('date', '<=', $data . ' 23:59:59')
            ->where('visible', 'Sim')
            ->whereNotIn('league', $block_league)
            ->groupBy('league')
            ->orderBy('league', 'asc')
            ->get();

        $i = 0;
        foreach ($leagues as $league) {
            $return[$i]['league'] = $league->league;

            $matchs = MatchModel::where('league', $league->league)
                ->where('date', '>=', $this->agora)
                ->whereNotIn('event_id', $block_match)
                ->where('visible', 'Sim')
                ->get();

            $j = 0;
            foreach ($matchs as $match) {
                $return[$i]['match'][$j]['id'] = $match->id;
                $return[$i]['match'][$j]['sport'] = $match->sport_name;
                $return[$i]['match'][$j]['event_id'] = $match->event_id;
                $return[$i]['match'][$j]['home'] = $match->home;
                $return[$i]['match'][$j]['away'] = $match->away;
                $return[$i]['match'][$j]['date'] = $match->date;
                $return[$i]['match'][$j]['visible'] = $match->visible;
                $j++;
            }
            $i++;
        }

        return $return;
    }

    public function update(Request $request, $id)
    {
        $match = MatchModel::find($id);
        $match->update($request->all());
    }

    public function updateOdd(Request $request, $id)
    {
        $siteId = app('tenant.site_id');

        if ($request->tipo == 0) {
            $odd = BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();
            if (count($odd) > 0) {
                $odd_alt = BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();
                foreach ($odd_alt as $od) {
                    $od->cotacao = $request->cotacao;
                    $od->odd = $request->odd;
                    $od->save();
                }
            } else {
                BlockOddMatch::create([
                    'odd_id'  => $id,
                    'cotacao' => $request->cotacao,
                    'odd'     => $request->odd,
                    'odd_uid' => $request->odd_uid,
                    'status'  => 1,
                    'site_id' => $siteId,
                ]);
            }
        }

        if ($request->tipo == 1) {
            $odd = BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();
            if (count($odd) > 0) {
                $odd_alt = BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();
                foreach ($odd_alt as $od) {
                    $od->status = 0;
                    $od->save();
                }
            } else {
                BlockOddMatch::create([
                    'odd_id'  => $id,
                    'cotacao' => $request->cotacao,
                    'odd'     => $request->odd,
                    'odd_uid' => $request->odd_uid,
                    'status'  => 0,
                    'site_id' => $siteId,
                ]);
            }
        }

        if ($request->tipo == 3) {
            $odd = BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();
            if (count($odd) > 0) {
                $odd_alt = BlockOddMatch::where('odd_id', $id)->where('odd', $request->odd)->get();
                foreach ($odd_alt as $od) {
                    $od->status = 1;
                    $od->save();
                }
            }
        }
    }

    public function deleteLeague($id)
    {
        $liga = BlockLeague::find($id);
        $liga->delete();
    }

    public function deleteMatch($id)
    {
        $match = BlockMatch::find($id);
        $match->delete();
    }

    public function indexLigasBlock()
    {
        $siteId = app('tenant.site_id');
        return BlockLeague::where('site_id', $siteId)->get();
    }

    public function indexMatchsBlock()
    {
        $siteId = app('tenant.site_id');
        return BlockMatch::where('site_id', $siteId)
            ->where('date', '>=', $this->agora)
            ->get();
    }

    public function blockLeague(Request $request)
    {
        $siteId = app('tenant.site_id');
        BlockLeague::create([
            'league'  => $request->league,
            'site_id' => $siteId,
        ]);
    }

    public function blockMatch(Request $request)
    {
        $siteId = app('tenant.site_id');
        BlockMatch::create([
            'event_id'  => $request->event_id,
            'date'      => $request->date,
            'sport'     => $request->sport,
            'confronto' => $request->confronto,
            'site_id'   => $siteId,
        ]);
    }

    public function insertLeagueMain(Request $request)
    {
        $siteId = app('tenant.site_id');
        MainLeague::create([
            'sport'     => $request->sport,
            'league'    => $request->league,
            'league_id' => $request->league_id,
            'site_id'   => $siteId,
        ]);
    }

    public function listLeagueMain()
    {
        $siteId = app('tenant.site_id');
        return MainLeague::orderBy('league', 'ASC')->where('site_id', $siteId)->get();
    }

    public function deleteLeagueByName(Request $request)
    {
        $siteId = app('tenant.site_id');
        BlockLeague::where('league', $request->league)
            ->where('site_id', $siteId)
            ->delete();
    }
}
