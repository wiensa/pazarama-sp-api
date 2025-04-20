<?php

namespace PazaramaApi\PazaramaSpApi\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PazaramaApi\PazaramaSpApi\Contracts\ApiServiceInterface;
use PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException;
use Illuminate\Support\Facades\Log;

class ApiService implements ApiServiceInterface
{
    /**
     * HTTP Client
     *
     * @var \GuzzleHttp\Client
     */
    protected Client $client;

    /**
     * API client ID
     *
     * @var string
     */
    protected string $client_id;

    /**
     * API client secret
     *
     * @var string
     */
    protected string $client_secret;

    /**
     * API erişim tokeni
     *
     * @var string|null
     */
    protected ?string $access_token = null;

    /**
     * Token geçerlilik süresi
     * 
     * @var int
     */
    protected int $token_expires_at = 0;

    /**
     * Hata ayıklama modu
     *
     * @var bool
     */
    protected bool $debug;

    /**
     * ApiService constructor.
     *
     * @param string $client_id API client ID
     * @param string $client_secret API client secret
     * @param string $api_url API URL
     * @param string|null $auth_url Auth URL
     * @param int $timeout API zaman aşımı (saniye)
     * @param bool $debug Hata ayıklama modu
     */
    public function __construct(
        string $client_id,
        string $client_secret,
        string $api_url,
        ?string $auth_url = null,
        int $timeout = 30,
        bool $debug = false
    ) {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->debug = $debug;

        $this->client = new Client([
            'base_uri' => $api_url,
            'timeout' => $timeout,
            'verify' => !$debug, // SSL doğrulamasını debug modunda devre dışı bırak
        ]);
    }

    /**
     * HTTP istemcisini döndürür.
     *
     * @return Client
     */
    public function getHttpClient(): Client
    {
        return $this->client;
    }

    /**
     * API'ye POST isteği gönderir.
     *
     * @param string $endpoint API bitiş noktası
     * @param array $data İstek verileri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     *
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data], $headers);
    }

    /**
     * API'ye GET isteği gönderir.
     *
     * @param string $endpoint API bitiş noktası
     * @param array $params Sorgu parametreleri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     *
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    public function get(string $endpoint, array $params = [], array $headers = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $params], $headers);
    }

    /**
     * API'ye PUT isteği gönderir.
     *
     * @param string $endpoint API bitiş noktası
     * @param array $data İstek verileri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     *
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data], $headers);
    }

    /**
     * API'ye DELETE isteği gönderir.
     *
     * @param string $endpoint API bitiş noktası
     * @param array $params Sorgu parametreleri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     *
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    public function delete(string $endpoint, array $params = [], array $headers = []): array
    {
        return $this->request('DELETE', $endpoint, ['query' => $params], $headers);
    }

    /**
     * Access token alır
     *
     * @return string
     * @throws PazaramaApiException
     */
    public function getAccessToken(): string
    {
        // Eğer token hala geçerliyse yeni token almaya gerek yok
        if ($this->token_expires_at > time() && $this->access_token !== null) {
            return $this->access_token;
        }

        try {
            $response = $this->client->post('connect/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'scope' => 'merchantgatewayapi.fullaccess'
                ],
                'auth' => [
                    $this->client_id,
                    $this->client_secret
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
        } catch (\Exception $e) {
            throw new PazaramaApiException(
                'Yetkilendirme hatası: ' . $e->getMessage(),
                500,
                null,
                ['error' => $e->getMessage()],
                $e
            );
        }
    }

    /**
     * API isteği gönderir.
     *
     * @param string $method HTTP metodu
     * @param string $endpoint API bitiş noktası
     * @param array $options İstek seçenekleri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     *
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    protected function request(string $method, string $endpoint, array $options = [], array $headers = []): array
    {
        try {
            $access_token = $this->getAccessToken();

            // Başlıkları birleştir
            $options['headers'] = array_merge([
                'Authorization' => "Bearer {$access_token}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ], $headers);

            // İsteği logla
            if ($this->debug) {
                Log::debug('Pazarama API Request', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'options' => $this->sanitizeForLogging($options),
                ]);
            }

            // İsteği gönder
            $response = $this->client->request($method, $endpoint, $options);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            // Yanıtı logla
            if ($this->debug) {
                Log::debug('Pazarama API Response', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $response->getStatusCode(),
                    'data' => $this->sanitizeForLogging($data),
                ]);
            }

            // JSON dönüşüm hatası kontrolü
            if ($data === null && $body !== '') {
                throw new PazaramaApiException(
                    'API yanıtı JSON formatında değil: ' . substr($body, 0, 100),
                    $response->getStatusCode()
                );
            }

            // API hata kontrolü
            if ($response->getStatusCode() >= 400) {
                throw new PazaramaApiException(
                    $data['message'] ?? 'API hatası oluştu',
                    $response->getStatusCode(),
                    $data['code'] ?? null,
                    $data
                );
            }

            return $data;
        } catch (GuzzleException $e) {
            // Guzzle istisnaları için loglama
            if ($this->debug) {
                Log::error('Pazarama API Error', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);
            }

            throw new PazaramaApiException(
                'API isteği gönderilirken hata oluştu: ' . $e->getMessage(),
                $e->getCode() ?: 500,
                null,
                [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage()
                ],
                $e
            );
        }
    }

    /**
     * Hassas verileri loglama için temizler.
     *
     * @param array $data Temizlenecek veri
     * @return array Temizlenmiş veri
     */
    protected function sanitizeForLogging(array $data): array
    {
        $sensitiveKeys = ['password', 'token', 'api_key', 'secret', 'auth', 'client_secret'];
        
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $data[$key] = '***MASKED***';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizeForLogging($value);
            }
        }
        
        return $data;
    }
} 