<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use App\Models\Configuracao;
use Illuminate\Support\Facades\Log;

class PaggueService
{
    private $client_key;
    private $client_secret;
    private $signature;
    private $company_id;
    private $client;
    private $siteId;

    public function __construct($siteId = null)
    {
        $this->siteId = $siteId ?? config('tenant.site_id', 1);
        $config = Configuracao::where('site_id', $this->siteId)->first();

        $this->client_key    = $config->paggue_client_key    ?? env('PAGGUE_CLIENT_KEY');
        $this->client_secret = $config->paggue_client_secret ?? env('PAGGUE_CLIENT_SECRET');
        $this->signature     = $config->paggue_signature     ?? env('PAGGUE_SIGNATURE');
        $this->company_id    = $config->paggue_company_id    ?? env('PAGGUE_COMPANY_ID');
        $this->client        = new Client(['base_uri' => env('PAGGUE_API_URL', 'https://api.paggue.com.br')]);
    }

    public function auth()
    {
        try {
            $response = $this->client->post('/payments/api/auth/login', [
                'json' => [
                    'client_key'    => $this->client_key,
                    'client_secret' => $this->client_secret,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('PaggueService auth failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function createOrder(string $payerName, string $value)
    {
        try {
            $valorDecimal = floatval($value);
            $cents        = intval($valorDecimal * 100);
            $auth         = $this->auth();

            if (!$auth || !isset($auth['access_token'])) {
                return ['error' => 'Falha na autenticação Paggue'];
            }

            $response = $this->client->post('/payments/api/billing_order', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $auth['access_token'],
                    'Signature'     => $this->signature,
                    'X-Company-ID'  => $this->company_id,
                ],
                'json' => [
                    'payer_name'  => $payerName,
                    'amount'      => $cents,
                    'external_id' => 'pix-deposito-' . uniqid(),
                    'description' => 'Depósito PIX',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (BadResponseException $e) {
            Log::error('PaggueService createOrder failed', [
                'error'  => $e->getMessage(),
                'response' => $e->getResponse()->getBody()->getContents(),
            ]);
            return ['error' => $e->getResponse()->getBody()->getContents()];
        }
    }

    public function getOrder(string $orderId)
    {
        try {
            $auth = $this->auth();

            $response = $this->client->get('/order/' . $orderId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . ($auth['access_token'] ?? ''),
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (BadResponseException $e) {
            Log::error('PaggueService getOrder failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getResponse()->getBody()->getContents()];
        }
    }

    public function refund(string $orderId, string $value)
    {
        try {
            $auth = $this->auth();
            $cents = intval(floatval($value) * 100);

            $response = $this->client->post('/payments/api/billing_order/' . $orderId . '/refund', [
                'headers' => [
                    'Authorization' => 'Bearer ' . ($auth['access_token'] ?? ''),
                    'Signature'     => $this->signature,
                    'X-Company-ID'  => $this->company_id,
                ],
                'json' => [
                    'amount' => $cents,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (BadResponseException $e) {
            Log::error('PaggueService refund failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getResponse()->getBody()->getContents()];
        }
    }
}
