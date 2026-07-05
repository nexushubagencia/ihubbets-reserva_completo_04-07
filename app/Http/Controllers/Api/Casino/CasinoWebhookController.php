<?php

namespace App\Http\Controllers\Api\Casino;

use App\Http\Controllers\Controller;
use App\Traits\CasinoProviders\FiversTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CasinoWebhookController extends Controller
{
    use FiversTrait;

    public function playfiver(Request $request)
    {
        Log::info('Casino PlayFiver webhook', $request->all());
        return self::WebhooksFivers($request);
    }
}
