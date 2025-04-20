<?php

namespace PazaramaApi\PazaramaSpApi\Traits;

use PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException;

trait ApiRequest
{
    /**
     * HTTP istek yapar
     *
     * @param string $method HTTP metodu (GET, POST, PUT, DELETE)
     * @param string $endpoint API endpoint
     * @param array $data İstek verileri
     * @return array API yanıtı
     * @throws PazaramaApiException
     */
    public function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        // Erişim tokenini kontrol et ve gerekirse yenile
        $token = $this->authenticate();
        
        try {
            $options = [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
                'json' => $data,
            ];
            
            $response = $this->http_client->request($method, $endpoint, $options);
            
            $response_body = json_decode($response->getBody()->getContents(), true);
            
            // Başarısız yanıtları kontrol et
            if ($response->getStatusCode() >= 400 || (isset($response_body['success']) && $response_body['success'] === false)) {
                throw new PazaramaApiException(
                    $response_body['error'] ?? $response_body['message'] ?? 'API isteği başarısız oldu',
                    $response->getStatusCode(),
                    $response_body['errorCode'] ?? null,
                    $response_body
                );
            }
            
            return $response_body;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new PazaramaApiException(
                'API isteği sırasında hata oluştu: ' . $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }
} 