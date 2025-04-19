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

class PazaramaSpApi
{
    private const API_BASE_URL = 'https://isortagimapi.pazarama.com';
    private const AUTH_URL = 'https://isortagimgiris.pazarama.com/connect/token';

    private Client $http_client;
    private Client $auth_client;
    private array $config;
    private string $access_token;
    private int $token_expires_at;
    
    private ?ProductService $product_service = null;
    private ?OrderService $order_service = null;
    private ?CategoryService $category_service = null;
    private ?BulkOperationService $bulk_operation_service = null;
    private ?ShippingService $shipping_service = null;
    private ?ReturnService $return_service = null;
    private ?BrandService $brand_service = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->http_client = new Client([
            'base_uri' => self::API_BASE_URL,
            'timeout' => $config['timeout'] ?? 30,
            'http_errors' => false,
        ]);
        $this->auth_client = new Client([
            'base_uri' => self::AUTH_URL,
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

        $response = $this->auth_client->post('', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'scope' => 'merchantgatewayapi.fullaccess'
            ],
            'auth' => [
                $this->config['client_id'],
                $this->config['client_secret']
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
    public function bulk(): BulkOperationService
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
    
    /**
     * Ürün listesini getirir
     * 
     * @param array $params Sorgu parametreleri
     * @return array Ürün listesi
     */
    public function getProducts(array $params = []): array
    {
        return $this->products()->list($params);
    }
    
    /**
     * Belirli bir ürünü getirir
     * 
     * @param string $id Ürün ID'si
     * @return array Ürün bilgileri
     */
    public function getProduct(string $id): array
    {
        return $this->products()->get($id);
    }
    
    /**
     * Sipariş listesini getirir
     * 
     * @param array $params Sorgu parametreleri
     * @return array Sipariş listesi
     */
    public function getOrders(array $params = []): array
    {
        return $this->orders()->list($params);
    }
    
    /**
     * Belirli bir siparişi getirir
     * 
     * @param string $id Sipariş ID'si
     * @return array Sipariş bilgileri
     */
    public function getOrder(string $id): array
    {
        return $this->orders()->get($id);
    }
    
    /**
     * Kategori listesini getirir
     * 
     * @param array $params Sorgu parametreleri
     * @return array Kategori listesi
     */
    public function getCategories(array $params = []): array
    {
        return $this->categories()->list($params);
    }
    
    /**
     * Belirli bir kategoriyi getirir
     * 
     * @param string $id Kategori ID'si
     * @return array Kategori bilgileri
     */
    public function getCategory(string $id): array
    {
        return $this->categories()->get($id);
    }
    
    /**
     * Marka listesini getirir
     * 
     * @param int $page Sayfa numarası
     * @param int $size Sayfa başına öğe sayısı
     * @return array Marka listesi
     */
    public function getBrands(int $page = 1, int $size = 100): array
    {
        return $this->brands()->list($page, $size);
    }
} 