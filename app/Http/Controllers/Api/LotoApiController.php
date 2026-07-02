<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuinaTaxa;
use App\Models\SenaTaxa;
use App\Models\BlockDayLoto;
use App\Models\LotoResult;
use Carbon\Carbon;

class LotoApiController extends Controller
{
    public function geraQuina()
    {
        $numeros = [];
        for ($i = 1; $i <= 80; $i++) {
            $numeros[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        return response()->json(['numeros' => $numeros]);
    }

    public function geraSena()
    {
        $numeros = [];
        for ($i = 1; $i <= 60; $i++) {
            $numeros[] = str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        return response()->json(['numeros' => $numeros]);
    }

    public function viewCotacaoQuina()
    {
        $siteId = config('tenant.site_id', 1);
        $taxas = QuinaTaxa::where('status', 1)
            ->where('site_id', $siteId)
            ->orderBy('dezena', 'asc')
            ->get();

        return response()->json(['taxas' => $taxas]);
    }

    public function viewCotacaoSena()
    {
        $siteId = config('tenant.site_id', 1);
        $taxas = SenaTaxa::where('status', 1)
            ->where('site_id', $siteId)
            ->orderBy('dezena', 'asc')
            ->get();

        return response()->json(['taxas' => $taxas]);
    }

    public function viewDiasSorteioQuina()
    {
        $blockedDates = BlockDayLoto::pluck('date')->toArray();
        $dias = [];
        $data = Carbon::today();

        for ($i = 0; $i < 20; $i++) {
            $data->addDay();
            if ($data->dayOfWeek !== Carbon::SUNDAY) {
                if (!in_array($data->format('Y-m-d'), $blockedDates)) {
                    $dias[] = $data->format('d/m/Y');
                }
            }
        }

        return response()->json(['concursos' => $dias]);
    }

    public function viewDiasSorteioSena()
    {
        $blockedDates = BlockDayLoto::pluck('date')->toArray();
        $dias = [];
        $data = Carbon::today();
        $allowedDays = [Carbon::WEDNESDAY, Carbon::SATURDAY];

        for ($i = 0; $i < 20; $i++) {
            $data->addDay();
            if (in_array($data->dayOfWeek, $allowedDays)) {
                if (!in_array($data->format('Y-m-d'), $blockedDates)) {
                    $dias[] = $data->format('d/m/Y');
                }
            }
        }

        return response()->json(['concursos' => $dias]);
    }
}
