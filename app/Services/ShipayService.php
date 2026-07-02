<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use App\Models\Configuracao;
use Illuminate\Support\Facades\Log;

class ShipayService
{
    private $access_key;
    private $secret_key;
    private $client_id;
    private $client;
    private $token;
    private $siteId;

    public function __construct($siteId = null)
    {
        $this->siteId = $siteId ?? config('tenant.site_id', 1);
        $config = Configuracao::where('site_id', $this->siteId)->first();

        $this->access_key = $config->shipay_access_key ?? env('SHIPAY_ACCESS_KEY');
        $this->secret_key = $config->shipay_secret_key ?? env('SHIPAY_SECRET_KEY');
        $this->client_id  = $config->shipay_client_id  ?? env('SHIPAY_CLIENT_ID');
        $this->token      = $config->shipay_token       ?? '';
        $this->client     = new Client(['base_uri' => env('SHIPAY_API_URL', 'https://api.shipay.com.br')]);
    }

    public function auth()
    {
        try {
            $response = $this->client->post('/pdvauth', [
                'json' => [
                    'access_key' => $this->access_key,
                    'secret_key' => $this->secret_key,
                    'client_id'  => $this->client_id,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['token'])) {
                $this->token = $data['token'];
                Configuracao::where('site_id', $this->siteId)
                    ->update(['shipay_token' => $data['token']]);
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('ShipayService auth failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function createOrder($value, $orderRef = null)
    {
        try {
            if (empty($this->token)) {
                $this->auth();
            }

            $response = $this->client->post('/order', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'json' => [
                    'items' => [
                        [
                            'item_title' => 'Aposta',
                            'quantity'   => 1,
                            'unit_price' => floatval($value),
                        ],
                    ],
                    'order_ref'     => $orderRef ?? uniqid(),
                    'total'         => floatval($value),
                    'wallet'        => 'pix',
                    'callback_url'  => env('APP_URL') . '/api/webhook/shipay',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (BadResponseException $e) {
            $body = $e->getResponse()->getBody()->getContents();

            if (str_contains($body, 'token') || str_contains($body, 'unauthorized')) {
                $this->auth();
                return $this->createOrder($value, $orderRef);
            }

            Log::error('ShipayService createOrder failed', ['error' => $body]);
            return ['error' => $body];
        }
    }

    public function getOrder(string $orderId)
    {
        try {
            $response = $this->client->get('/order/' . $orderId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (BadResponseException $e) {
            Log::error('ShipayService getOrder failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getResponse()->getBody()->getContents()];
        }
    }

    public function cancelOrder(string $orderId)
    {
        try {
            $response = $this->client->delete('/order/' . $orderId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (BadResponseException $e) {
            Log::error('ShipayService cancelOrder failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getResponse()->getBody()->getContents()];
        }
    }
}
