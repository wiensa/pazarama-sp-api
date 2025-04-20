<?php

namespace PazaramaApi\PazaramaSpApi;

use GuzzleHttp\Client;
use PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException;
use PazaramaApi\PazaramaSpApi\Services\BrandService;
use PazaramaApi\PazaramaSpApi\Services\BulkOperationService;
use PazaramaApi\PazaramaSpApi\Services\CategoryService;
use PazaramaApi\PazaramaSpApi\Services\OrderService;
use PazaramaApi\PazaramaSpApi\Services\ProductService;
use PazaramaApi\PazaramaSpApi\Services\ReturnService;
use PazaramaApi\PazaramaSpApi\Services\ShippingService;
use PazaramaApi\PazaramaSpApi\Traits\ApiRequest;

/**
 * PazaramaApi entegrasyon sınıfı
 */
final class PazaramaSpApi
{
    use ApiRequest;

    /**
     * API temel URL
     *
     * @var string
     */
    private string $base_url;

    /**
     * API kimlik doğrulama URL'i
     *
     * @var string
     */
    private string $auth_url;

    /**
     * API Client ID
     *
     * @var string
     */
    private string $client_id;

    /**
     * API Client Secret
     *
     * @var string
     */
    private string $client_secret;

    /**
     * HTTP Client instance
     *
     * @var \GuzzleHttp\Client
     */
    private Client $http_client;

    /**
     * Authentication HTTP Client instance
     *
     * @var \GuzzleHttp\Client
     */
    private Client $auth_client;

    /**
     * Access token
     *
     * @var string
     */
    private string $access_token;

    /**
     * Token expiration timestamp
     *
     * @var int
     */
    private int $token_expires_at;

    /**
     * Ürün servisi
     *
     * @var ProductService|null
     */
    private ?ProductService $product_service = null;
    
    /**
     * Sipariş servisi
     *
     * @var OrderService|null
     */
    private ?OrderService $order_service = null;
    
    /**
     * Kategori servisi
     *
     * @var CategoryService|null
     */
    private ?CategoryService $category_service = null;
    
    /**
     * Toplu işlem servisi
     *
     * @var BulkOperationService|null
     */
    private ?BulkOperationService $bulk_operation_service = null;
    
    /**
     * Kargo servisi
     *
     * @var ShippingService|null
     */
    private ?ShippingService $shipping_service = null;
    
    /**
     * İade servisi
     *
     * @var ReturnService|null
     */
    private ?ReturnService $return_service = null;
    
    /**
     * Marka servisi
     *
     * @var BrandService|null
     */
    private ?BrandService $brand_service = null;

    /**
     * PazaramaSpApi sınıfı yapıcı fonksiyonu
     *
     * @param array $config Konfigürasyon parametreleri
     */
    public function __construct(array $config = [])
    {
        $this->base_url = $config['base_url'] ?? config('pazarama-api.base_url', 'https://isortagimapi.pazarama.com');
        $this->auth_url = $config['auth_url'] ?? config('pazarama-api.auth_url', 'https://isortagimgiris.pazarama.com/connect/token');
        $this->client_id = $config['client_id'] ?? config('pazarama-api.client_id');
        $this->client_secret = $config['client_secret'] ?? config('pazarama-api.client_secret');

        $this->initHttpClient();
        $this->token_expires_at = 0;
    }

    /**
     * HTTP istemcilerini başlatır
     * 
     * @return void
     */
    private function initHttpClient(): void
    {
        $timeout = config('pazarama-api.timeout', 30);
        
        $this->http_client = new Client([
            'base_uri' => $this->base_url,
            'timeout' => $timeout,
            'http_errors' => false,
        ]);
        
        $this->auth_client = new Client([
            'base_uri' => $this->auth_url,
            'timeout' => $timeout,
            'http_errors' => false,
        ]);
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

        try {
            $response = $this->auth_client->post('', [
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
            $endpoint,
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

    /**
     * HTTP istemcisini döndürür
     *
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient(): Client
    {
        return $this->http_client;
    }

    /**
     * ProductService örneğini döndürür
     * 
     * @return ProductService
     */
    public function products(): ProductService
    {
        if ($this->product_service === null) {
            $this->product_service = new ProductService($this);
        }
        
        return $this->product_service;
    }
    
    /**
     * OrderService örneğini döndürür
     * 
     * @return OrderService
     */
    public function orders(): OrderService
    {
        if ($this->order_service === null) {
            $this->order_service = new OrderService($this);
        }
        
        return $this->order_service;
    }
    
    /**
     * CategoryService örneğini döndürür
     * 
     * @return CategoryService
     */
    public function categories(): CategoryService
    {
        if ($this->category_service === null) {
            $this->category_service = new CategoryService($this);
        }
        
        return $this->category_service;
    }
    
    /**
     * BulkOperationService örneğini döndürür
     * 
     * @return BulkOperationService
     */
    public function bulkOperations(): BulkOperationService
    {
        if ($this->bulk_operation_service === null) {
            $this->bulk_operation_service = new BulkOperationService($this);
        }
        
        return $this->bulk_operation_service;
    }
    
    /**
     * ShippingService örneğini döndürür
     * 
     * @return ShippingService
     */
    public function shipping(): ShippingService
    {
        if ($this->shipping_service === null) {
            $this->shipping_service = new ShippingService($this);
        }
        
        return $this->shipping_service;
    }
    
    /**
     * ReturnService örneğini döndürür
     * 
     * @return ReturnService
     */
    public function returns(): ReturnService
    {
        if ($this->return_service === null) {
            $this->return_service = new ReturnService($this);
        }
        
        return $this->return_service;
    }
    
    /**
     * BrandService örneğini döndürür
     * 
     * @return BrandService
     */
    public function brands(): BrandService
    {
        if ($this->brand_service === null) {
            $this->brand_service = new BrandService($this);
        }
        
        return $this->brand_service;
    }
} 