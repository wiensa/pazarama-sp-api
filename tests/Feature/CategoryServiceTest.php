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

test('kategori listesi başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'items' => [
                [
                    'id' => 'CAT001',
                    'name' => 'Elektronik',
                    'parentId' => null,
                    'leaf' => false,
                    'children' => [
                        [
                            'id' => 'CAT002',
                            'name' => 'Bilgisayar',
                            'parentId' => 'CAT001',
                            'leaf' => false,
                            'children' => []
                        ]
                    ]
                ],
                [
                    'id' => 'CAT003',
                    'name' => 'Giyim',
                    'parentId' => null,
                    'leaf' => false,
                    'children' => []
                ]
            ]
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'GET' && 
                   $endpoint === '/category/getCategoryTree' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token';
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->categories()->getCategories();
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['items'])->toHaveCount(2);
});

test('kategori özellikleri başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'categoryId' => 'CAT002',
            'attributes' => [
                [
                    'id' => 'ATTR001',
                    'name' => 'İşlemci',
                    'type' => 'list',
                    'required' => true,
                    'values' => [
                        [
                            'id' => 'VAL001',
                            'value' => 'Intel Core i5'
                        ],
                        [
                            'id' => 'VAL002',
                            'value' => 'Intel Core i7'
                        ]
                    ]
                ],
                [
                    'id' => 'ATTR002',
                    'name' => 'RAM',
                    'type' => 'list',
                    'required' => true,
                    'values' => [
                        [
                            'id' => 'VAL003',
                            'value' => '8 GB'
                        ],
                        [
                            'id' => 'VAL004',
                            'value' => '16 GB'
                        ]
                    ]
                ]
            ]
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'GET' && 
                   $endpoint === '/category/getCategoryWithAttributes' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   ((isset($options['query']) && isset($options['query']['Id']) && $options['query']['Id'] === 'CAT002') || 
                    (isset($options['json']) && isset($options['json']['Id']) && $options['json']['Id'] === 'CAT002'));
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->categories()->getCategoryAttributes('CAT002');
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['attributes'])->toHaveCount(2);
});

test('gerekli kategori özellikleri başarıyla getirilebilmeli', function () {
    $attributesResponse = [
        'success' => true,
        'data' => [
            'categoryId' => 'CAT002',
            'attributes' => [
                [
                    'id' => 'ATTR001',
                    'name' => 'İşlemci',
                    'type' => 'list',
                    'required' => true,
                    'values' => [
                        ['id' => 'VAL001', 'value' => 'Intel Core i5'],
                        ['id' => 'VAL002', 'value' => 'Intel Core i7']
                    ]
                ],
                [
                    'id' => 'ATTR002',
                    'name' => 'RAM',
                    'type' => 'list',
                    'required' => true
                ],
                [
                    'id' => 'ATTR003',
                    'name' => 'Renk',
                    'type' => 'list',
                    'required' => false
                ]
            ]
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'GET' && 
                   $endpoint === '/category/getCategoryWithAttributes';
        })
        ->andReturn(new Response(200, [], json_encode($attributesResponse)));
    
    $result = $this->api->categories()->getRequiredAttributesForCategory('CAT002');
    
    expect($result)->toBe($attributesResponse);
}); 