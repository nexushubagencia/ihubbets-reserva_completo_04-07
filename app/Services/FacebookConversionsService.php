<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookConversionsService
{
    private $pixelId;
    private $accessToken;

    public function __construct()
    {
        // Pega as chaves dinamarquês para cada banca (Multi-tenant)
        $this->pixelId = env('FB_PIXEL_ID');
        $this->accessToken = env('FB_ACCESS_TOKEN');
    }

    /**
     * Envia evento de 'Purchase' (Compra/Depósito Pago)
     */
    public function sendPurchase($amount, $userData)
    {
        return $this->sendEvent('Purchase', $amount, $userData);
    }

    /**
     * Envia evento de 'Lead' (Cadastro Realizado)
     */
    public function sendLead($userData)
    {
        return $this->sendEvent('Lead', 0, $userData);
    }

    private function sendEvent($eventName, $value, $userData)
    {
        if (!$this->pixelId || !$this->accessToken) return;

        $url = "https://graph.facebook.com/v18.0/{$this->pixelId}/events";

        $data = [
            'data' => [
                [
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'action_source' => 'website',
                    'user_data' => [
                        'em' => hash('sha256', strtolower($userData['email'] ?? '')),
                        'client_ip_address' => request()->ip(),
                        'client_user_agent' => request()->userAgent(),
                    ],
                    'custom_data' => [
                        'currency' => 'BRL',
                        'value' => $value,
                    ],
                ]
            ],
            'access_token' => $this->accessToken,
        ];

        try {
            Http::post($url, $data);
        } catch (\Exception $e) {
            Log::error("Erro Facebook Conversions API: " . $e->getMessage());
        }
    }
}
