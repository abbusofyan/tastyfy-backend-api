<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class HitPayHelper
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {

        $apiKey = env('APP_ENV') === 'production'
            ? config('hitpay.apikey.live')
            : config('hitpay.apikey.sandbox');


        $apiUrl = env('APP_ENV') === 'production'
            ? config('hitpay.url.live')
            : config('hitpay.url.sandbox');

        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->client = new Client([
            'base_uri' => $this->apiUrl,
            'headers' => [
                'X-BUSINESS-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function createPayment(array $data)
    {
        try {
            $response = $this->client->post($this->apiUrl.'/payment-requests', [
                'json' => $data
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function getPayment($payment_id)
    {
        try {
            $response = $this->client->get($this->apiUrl."/payment-requests/{$payment_id}");
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function refundPayment($id, array $data)
    {
        try {
            $response = $this->client->post("/refunds/{$id}", [
                'json' => $data
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function verifySignature($payload, $signature, $secret)
    {
        return hash_hmac('sha256', $payload, $secret) === $signature;
    }
}
