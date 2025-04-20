<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

/**
 * @group feature
 */
beforeEach(function () {
    $this->mockClient = Mockery::mock(Client::class);
    $this->mockAuthClient = Mockery::mock(Client::class);
    
    // API mock yapısını oluştur
    $this->api = new PazaramaSpApi([
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
    ]);
    
    // HTTP istemcilerini yeniden tanımla
    setPrivateProperty($this->api, 'http_client', $this->mockClient);
    setPrivateProperty($this->api, 'auth_client', $this->mockAuthClient);
    setPrivateProperty($this->api, 'access_token', 'fake_token');
    setPrivateProperty($this->api, 'token_expires_at', time() + 3600);
});

afterEach(function () {
    Mockery::close();
});

test('ürün listesi başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'items' => [
                ['code' => 'P001', 'name' => 'Test Ürün 1'],
                ['code' => 'P002', 'name' => 'Test Ürün 2'],
            ],
            'totalCount' => 2
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'GET' &&
                $endpoint === '/product/products' &&
                isset($options['headers']['Authorization']) && 
                $options['headers']['Authorization'] === 'Bearer fake_token';
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->products()->getProducts();
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['items'])->toHaveCount(2);
});

test('belirli bir ürün başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'code' => 'P001',
            'name' => 'Test Ürün 1',
            'description' => 'Test açıklaması',
            'listPrice' => 100,
            'salePrice' => 90
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/product/getProductDetail' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   ((isset($options['json']) && isset($options['json']['Code']) && $options['json']['Code'] === 'P001') ||
                    (isset($options['query']) && isset($options['query']['Code']) && $options['query']['Code'] === 'P001'));
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->products()->getProduct('P001');
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['code'])->toBe('P001');
});

test('yeni ürün başarıyla oluşturulabilmeli', function () {
    $productData = [
        'code' => 'NEW001',
        'name' => 'Yeni Test Ürün',
        'description' => 'Test açıklama',
        'listPrice' => 100,
        'salePrice' => 90,
        'stock' => 50
    ];
    
    $mockResponse = [
        'success' => true,
        'data' => [
            'batchRequestId' => 'batch123'
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) use ($productData) {
            return $method === 'POST' && 
                   $endpoint === '/product/create' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   ((isset($options['json']['products']) && 
                     is_array($options['json']['products']) && 
                     count($options['json']['products']) === 1 && 
                     $options['json']['products'][0]['code'] === 'NEW001'));
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->products()->createProduct($productData);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data'])->toHaveKey('batchRequestId');
});

test('ürün fiyatı başarıyla güncellenebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'batchRequestId' => 'batch456'
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/product/updatePrice' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']) && 
                   isset($options['json']['items']) && 
                   is_array($options['json']['items']) && 
                   $options['json']['items'][0]['code'] === 'P001';
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->products()->updateProductPrice('P001', 120, 100);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
});

test('ürün stok miktarı başarıyla güncellenebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'batchRequestId' => 'batch789'
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/product/updateStock' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']['items']) && 
                   is_array($options['json']['items']) && 
                   count($options['json']['items']) === 1 &&
                   $options['json']['items'][0]['code'] === 'P001' &&
                   $options['json']['items'][0]['stockCount'] === 75;
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->products()->updateProductStock('P001', 75);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
}); 