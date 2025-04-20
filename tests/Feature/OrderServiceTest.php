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

test('siparişler başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'items' => [
                [
                    'orderId' => 'ORD001',
                    'orderNumber' => '123456',
                    'orderDate' => '2023-05-01T10:00:00',
                    'status' => 'Onaylandı',
                    'totalPrice' => 150
                ],
                [
                    'orderId' => 'ORD002',
                    'orderNumber' => '123457',
                    'orderDate' => '2023-05-02T11:00:00',
                    'status' => 'Hazırlanıyor',
                    'totalPrice' => 200
                ]
            ],
            'totalCount' => 2
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/order/getOrdersForApi' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token';
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->orders()->getOrders();
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['items'])->toHaveCount(2);
});

test('sipariş detayı başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'orderId' => 'ORD001',
            'orderNumber' => '123456',
            'orderDate' => '2023-05-01T10:00:00',
            'status' => 'Onaylandı',
            'totalPrice' => 150,
            'items' => [
                [
                    'lineItemId' => 'ITEM001',
                    'productCode' => 'P001',
                    'productName' => 'Test Ürün 1',
                    'quantity' => 2,
                    'price' => 75
                ]
            ],
            'customer' => [
                'name' => 'Test Müşteri',
                'email' => 'test@example.com',
                'phone' => '5551234567'
            ],
            'shippingAddress' => [
                'addressText' => 'Test Adres',
                'city' => 'İstanbul',
                'district' => 'Kadıköy'
            ]
        ]
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/order/getOrdersForApi' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   ((isset($options['json']) && isset($options['json']['orderNumber']) && $options['json']['orderNumber'] === 'ORD001') ||
                    (isset($options['query']) && isset($options['query']['orderNumber']) && $options['query']['orderNumber'] === 'ORD001'));
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->orders()->getOrder('ORD001');
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['orderId'])->toBe('ORD001');
});

test('sipariş durumu başarıyla güncellenebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => true
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'PUT' && 
                   $endpoint === '/order/updateOrderStatusList' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   ((isset($options['json']) && isset($options['json']['orderNumber']) && $options['json']['orderNumber'] === 'ORD001' &&
                     isset($options['json']['status']) && $options['json']['status'] === 12) ||
                    (isset($options['query']) && isset($options['query']['orderNumber']) && $options['query']['orderNumber'] === 'ORD001' &&
                     isset($options['query']['status']) && $options['query']['status'] === 12));
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->orders()->updateOrderStatus('ORD001', 12);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
});

test('sipariş ürünü durumu başarıyla güncellenebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => true
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'PUT' && 
                   $endpoint === '/order/updateOrderStatus' &&
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']) && 
                   isset($options['json']['orderNumber']) && 
                   $options['json']['orderNumber'] === 'ORD001' &&
                   isset($options['json']['item']) &&
                   isset($options['json']['item']['orderItemId']) &&
                   $options['json']['item']['orderItemId'] === 'ITEM001' &&
                   isset($options['json']['item']['status']) &&
                   $options['json']['item']['status'] === 5;
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->orders()->updateOrderItemStatus('ORD001', 'ITEM001', 5);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
});

test('sipariş kargo bilgisi başarıyla güncellenebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => true
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'PUT' && 
                   $endpoint === '/order/updateOrderStatus' &&
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']) && 
                   isset($options['json']['orderNumber']) && 
                   $options['json']['orderNumber'] === 'ORD001' &&
                   isset($options['json']['item']['orderItemId']) &&
                   $options['json']['item']['orderItemId'] === 'ITEM001' &&
                   isset($options['json']['item']['status']) &&
                   $options['json']['item']['status'] === 5 &&
                   isset($options['json']['item']['deliveryType']) &&
                   $options['json']['item']['deliveryType'] === 1 &&
                   isset($options['json']['item']['shippingTrackingNumber']) &&
                   $options['json']['item']['shippingTrackingNumber'] === 'TRK123456' &&
                   isset($options['json']['item']['trackingUrl']) &&
                   $options['json']['item']['trackingUrl'] === 'https://kargo-takip.com/TRK123456' &&
                   isset($options['json']['item']['cargoCompanyId']) &&
                   $options['json']['item']['cargoCompanyId'] === 'COMP001';
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->orders()->updateOrderShipment(
        'ORD001',
        'ITEM001',
        5,
        1,
        'TRK123456',
        'https://kargo-takip.com/TRK123456',
        'COMP001'
    );
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
});

test('fatura linki başarıyla güncellenebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => true
    ];
    
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            return $method === 'POST' && 
                   $endpoint === '/order/invoice-link' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   ((isset($options['json']) && isset($options['json']['invoiceLink']) && $options['json']['invoiceLink'] === 'https://fatura-link.com' &&
                     isset($options['json']['orderid']) && $options['json']['orderid'] === 'ORD001') ||
                    (isset($options['query']) && isset($options['query']['invoiceLink']) && $options['query']['invoiceLink'] === 'https://fatura-link.com' &&
                     isset($options['query']['orderid']) && $options['query']['orderid'] === 'ORD001'));
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->orders()->updateInvoiceLink('ORD001', 'https://fatura-link.com');
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
});

test('iade listesi başarıyla getirilebilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => [
            'items' => [
                [
                    'refundId' => 'RF001',
                    'orderId' => 'ORD001',
                    'orderNumber' => '123456',
                    'requestDate' => '2023-05-10T14:30:00',
                    'status' => 'Beklemede',
                    'amount' => 75
                ],
                [
                    'refundId' => 'RF002',
                    'orderId' => 'ORD002',
                    'orderNumber' => '123457',
                    'requestDate' => '2023-05-11T15:45:00',
                    'status' => 'Beklemede',
                    'amount' => 150
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
                   $options['headers']['Authorization'] === 'Bearer fake_token';
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->orders()->getReturns();
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['items'])->toHaveCount(2);
});

test('iade durumu başarıyla güncellenebilmeli', function () {
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
                   isset($options['json']) &&
                   isset($options['json']['refundId']) && 
                   $options['json']['refundId'] === 'RF001' &&
                   isset($options['json']['status']) && 
                   $options['json']['status'] === 2;
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->orders()->updateRefundStatus('RF001', 2); // 2: Onay
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
});

test('tekil iade başarıyla getirilebilmeli', function () {
    $refund_id = '12345';
    $mockResponse = [
        'success' => true,
        'data' => [
            'refundId' => $refund_id,
            'refundStatus' => 1,
            'amount' => 100,
            'requestDate' => '2023-01-01',
            'customer' => [
                'name' => 'Test Müşteri'
            ]
        ]
    ];

    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) use ($refund_id) {
            return $method === 'POST' && 
                   $endpoint === '/order/getRefund' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']) &&
                   isset($options['json']['refundId']) && 
                   $options['json']['refundId'] === $refund_id;
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));

    $result = $this->api->orders()->getReturn($refund_id);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['refundId'])->toBe($refund_id);
});

test('iade alt öğeleri başarıyla getirilebilmeli', function () {
    $refund_id = '12345';
    $mockResponse = [
        'success' => true,
        'data' => [
            'items' => [
                [
                    'itemId' => 'item1',
                    'productId' => 'product1',
                    'quantity' => 1,
                    'amount' => 50
                ],
                [
                    'itemId' => 'item2',
                    'productId' => 'product2',
                    'quantity' => 2,
                    'amount' => 150
                ]
            ]
        ]
    ];

    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) use ($refund_id) {
            return $method === 'POST' && 
                   $endpoint === '/order/getRefundItems' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['json']) &&
                   isset($options['json']['refundId']) && 
                   $options['json']['refundId'] === $refund_id;
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));

    $result = $this->api->orders()->getReturnItems($refund_id);
    
    expect($result)->toBe($mockResponse);
    expect($result)->toBeSuccessfulResponse();
    expect($result['data']['items'])->toHaveCount(2);
}); 