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

test('iade listesi başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'items' => [
                [
                    'returnId' => 'RET001',
                    'orderId' => 'ORD001',
                    'orderItemId' => 'ITEM001',
                    'productName' => 'Test Ürün 1',
                    'quantity' => 1,
                    'reason' => 'Ürün hasarlı',
                    'status' => 'Onay Bekliyor',
                    'createDate' => '2023-05-15T10:00:00'
                ],
                [
                    'returnId' => 'RET002',
                    'orderId' => 'ORD002',
                    'orderItemId' => 'ITEM002',
                    'productName' => 'Test Ürün 2',
                    'quantity' => 2,
                    'reason' => 'Yanlış ürün gönderildi',
                    'status' => 'Onay Bekliyor',
                    'createDate' => '2023-05-16T11:00:00'
                ]
            ],
            'totalCount' => 2
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/order/getRefund' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']) && 
                   isset($options['json']['startDate']) && 
                   $options['json']['startDate'] === '2023-05-01' &&
                   isset($options['json']['endDate']) && 
                   $options['json']['endDate'] === '2023-05-31';
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->returns()->getReturns([
        'startDate' => '2023-05-01',
        'endDate' => '2023-05-31'
    ]);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['items'])->toHaveCount(2);
});

test('iade detayı başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'items' => [
                [
                    'returnId' => 'RET001',
                    'orderId' => 'ORD001',
                    'orderItemId' => 'ITEM001',
                    'productName' => 'Test Ürün 1',
                    'productCode' => 'P001',
                    'quantity' => 1,
                    'price' => 150,
                    'reason' => 'Ürün hasarlı',
                    'description' => 'Ürün kutusundan çıktığında hasarlıydı',
                    'status' => 'Onay Bekliyor',
                    'createDate' => '2023-05-15T10:00:00',
                ]
            ],
            'totalCount' => 1
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/order/getRefund' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']['refundNumber']) && 
                   $options['json']['refundNumber'] === 'RET001';
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->returns()->getReturn('RET001');
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
});

test('iade başarıyla onaylanabilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => true
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/order/updateRefund' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']['refundId']) && 
                   $options['json']['refundId'] === 'RET001' &&
                   isset($options['json']['status']) && 
                   $options['json']['status'] === 2;
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->returns()->approveReturn('RET001');
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
});

test('iade başarıyla reddedilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => true
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/order/updateRefund' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']['refundId']) && 
                   $options['json']['refundId'] === 'RET001' &&
                   isset($options['json']['status']) && 
                   $options['json']['status'] === 3;
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->returns()->rejectReturn('RET001', 'Ürün kullanılmış durumda');
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
}); 