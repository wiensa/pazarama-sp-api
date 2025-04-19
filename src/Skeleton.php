<?php

namespace PazaramaApi\PazaramaSpApi;

use GuzzleHttp\Client;
use PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException;

class PazaramaSpApi
{
    private const API_BASE_URL = 'https://api.pazarama.com';
    private const API_VERSION = 'v1';

    private Client $http_client;
    private array $config;
    private string $access_token;
    private int $token_expires_at;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->http_client = new Client([
            'base_uri' => self::API_BASE_URL,
            'timeout' => $config['timeout'] ?? 30,
            'http_errors' => false,
        ]);
        $this->token_expires_at = 0;
    }

    /**
     * Pazarama API'sine yetkilendirme yapar ve access token alır
     * 
     * @return string Access token
     * @throws PazaramaApiException
     */
    public function authenticate(): string
    {
        // Eğer token hala geçerliyse yeni token almaya gerek yok
        if ($this->token_expires_at > time() && isset($this->access_token)) {
            return $this->access_token;
        }

        $response = $this->http_client->post('/oauth/token', [
            'json' => [
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'grant_type' => 'client_credentials',
            ]
        ]);

        $response_body = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() !== 200 || !isset($response_body['access_token'])) {
            throw new PazaramaApiException(
                $response_body['message'] ?? 'Yetkilendirme başarısız', 
                $response->getStatusCode()
            );
        }

        $this->access_token = $response_body['access_token'];
        $this->token_expires_at = time() + ($response_body['expires_in'] ?? 3600);

        return $this->access_token;
    }

    /**
     * Pazarama API'sine istek gönderir
     * 
     * @param string $method HTTP metodu (GET, POST, PUT, DELETE vb.)
     * @param string $endpoint API endpoint'i
     * @param array $data İstek verisi
     * @return array Yanıt verisi
     * @throws PazaramaApiException
     */
    public function request(string $method, string $endpoint, array $data = []): array
    {
        $token = $this->authenticate();

        $options = [
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ];

        if (!empty($data)) {
            if (strtoupper($method) === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }
        }

        $response = $this->http_client->request(
            $method, 
            '/'.self::API_VERSION.$endpoint,
            $options
        );

        $response_body = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() >= 400) {
            throw new PazaramaApiException(
                $response_body['message'] ?? 'API isteği başarısız oldu', 
                $response->getStatusCode()
            );
        }

        return $response_body;
    }
}
