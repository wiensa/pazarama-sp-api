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

test('marka listesi başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'items' => [
                [
                    'id' => 'BRAND001',
                    'name' => 'Samsung'
                ],
                [
                    'id' => 'BRAND002',
                    'name' => 'Apple'
                ],
                [
                    'id' => 'BRAND003',
                    'name' => 'Xiaomi'
                ]
            ],
            'totalCount' => 3
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'GET' && 
                   $endpoint === '/brand/getBrands' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   (isset($options['query']) || isset($options['json'])) &&
                   (isset($options['query']['Page']) || isset($options['json']['Page'])) &&
                   (isset($options['query']['Size']) || isset($options['json']['Size']));
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->brands()->getBrands(1, 100);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['items'])->toHaveCount(3);
});

test('marka ismine göre başarıyla getirilebilmeli', function () {
    // İlk olarak markalar listesi isteği
    $mockListResponse = [
        'success' => true,
        'data' => [
            'items' => [
                [
                    'id' => 'BRAND001',
                    'name' => 'Samsung'
                ]
            ],
            'totalCount' => 1
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'GET' && 
                   $endpoint === '/brand/getBrands' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   (isset($options['query']) || isset($options['json'])) &&
                   (isset($options['query']['name']) && $options['query']['name'] === 'Samsung' || 
                    isset($options['json']['name']) && $options['json']['name'] === 'Samsung');
        })
        ->andReturn(new Response(200, [], json_encode($mockListResponse)));
    
    $result = $this->api->brands()->getBrandByName('Samsung');
    
    // Doğrudan API'nin döndürdüğü bir sonuç olmadığından, servis metodu veriyi işleyip ilk öğeyi döndürecek
    expect($result)->toBeArray();
    expect($result['id'])->toBe('BRAND001');
    expect($result['name'])->toBe('Samsung');
}); 