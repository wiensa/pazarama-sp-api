<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\ShippingService;
use PHPUnit\Framework\TestCase;

class ShippingServiceTest extends TestCase
{
    private MockHandler $mock_handler;
    private PazaramaSpApi $api;
    private ShippingService $shipping_service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Auth token mock
        $auth_mock = new MockHandler([
            new Response(200, [], json_encode([
                'access_token' => 'dummy_token',
                'expires_in' => 3600
            ]))
        ]);
        
        $auth_handler = HandlerStack::create($auth_mock);
        $auth_client = new Client(['handler' => $auth_handler]);
        
        // Regular requests mock
        $this->mock_handler = new MockHandler();
        $handler = HandlerStack::create($this->mock_handler);
        $client = new Client(['handler' => $handler]);
        
        // Create API instance with mocked clients
        $this->api = new PazaramaSpApi([
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret'
        ]);
        
        // Replace clients with mocked versions
        $reflection = new \ReflectionClass($this->api);
        
        $http_client_prop = $reflection->getProperty('http_client');
        $http_client_prop->setAccessible(true);
        $http_client_prop->setValue($this->api, $client);
        
        $auth_client_prop = $reflection->getProperty('auth_client');
        $auth_client_prop->setAccessible(true);
        $auth_client_prop->setValue($this->api, $auth_client);
        
        // Create the service instance
        $this->shipping_service = new ShippingService($this->api);
    }

    public function testGetDeliveryTypes()
    {
        // Mock response for delivery types request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'cargoCompany' => [
                        'deliveryId' => '1cea5dca-d16c-472e-9c2d-2f0d7edb1b2c',
                        'cargoCompanyId' => '7b5567ff-abe7-487e-5c79-08d8e480366a',
                        'price' => 9.99,
                        'campaignPrice' => 0,
                        'campaignAmount' => 500,
                        'campaignText' => '500 tl üzeri kargo bedava'
                    ],
                    'fastDelivery' => [
                        'deliveryId' => 'b38bff8b-f036-43e5-748e-08d91e7b753a',
                        'price' => 30.99,
                        'campaignAmount' => 0,
                        'campaignPrice' => 0,
                        'campaignText' => ''
                    ],
                    'storeDelivery' => [
                        'deliveryId' => '5bafe16c-db23-4a90-748f-08d91e7b753a',
                        'price' => 0,
                        'storeAddressList' => [
                            [
                                'storeName' => 'Pendik',
                                'city' => '86f65eba-a61f-428d-a689-0d5f3adee575',
                                'addressDetail' => 'Pendik magaza 11 sokak'
                            ],
                            [
                                'storeName' => 'Kadıkoy',
                                'city' => '86f65eba-a61f-428d-a689-0d5f3adee575',
                                'addressDetail' => 'Kadıkoy magaza 1050 sokak'
                            ]
                        ]
                    ]
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->shipping_service->getSellerDelivery();

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('cargoCompany', $result['data']);
        $this->assertArrayHasKey('fastDelivery', $result['data']);
        $this->assertArrayHasKey('storeDelivery', $result['data']);
        $this->assertEquals('1cea5dca-d16c-472e-9c2d-2f0d7edb1b2c', $result['data']['cargoCompany']['deliveryId']);
        $this->assertEquals(9.99, $result['data']['cargoCompany']['price']);
        $this->assertEquals('Pendik', $result['data']['storeDelivery']['storeAddressList'][0]['storeName']);
    }

    public function testGetCities()
    {
        // Mock response for cities request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => '86f65eba-a61f-428d-a689-0d5f3adee575',
                        'code' => '34',
                        'name' => 'İstanbul'
                    ],
                    [
                        'id' => '3e3690b0-3161-42e4-9ad8-2c8e0e2a8e47',
                        'code' => '6',
                        'name' => 'Ankara'
                    ],
                    [
                        'id' => '3e9f336c-7f65-46f7-b55a-690a61ba4468',
                        'code' => '35',
                        'name' => 'İzmir'
                    ]
                ],
                'success' => true,
                'messageCode' => '0',
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->shipping_service->getCities();

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['data']);
        $this->assertEquals('86f65eba-a61f-428d-a689-0d5f3adee575', $result['data'][0]['id']);
        $this->assertEquals('İstanbul', $result['data'][0]['name']);
        $this->assertEquals('3e3690b0-3161-42e4-9ad8-2c8e0e2a8e47', $result['data'][1]['id']);
        $this->assertEquals('Ankara', $result['data'][1]['name']);
    }

    public function testGetCarriers()
    {
        // Mock response for carriers request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => '7b5567ff-abe7-487e-5c79-08d8e480366a',
                        'name' => 'PTT Kargo',
                        'code' => 'PTT',
                        'trackingUrl' => 'https://gonderitakip.ptt.gov.tr'
                    ],
                    [
                        'id' => '6d6e004a-23b1-43d1-4d30-08d8e87e3449',
                        'name' => 'Aras Kargo',
                        'code' => 'ARAS',
                        'trackingUrl' => 'https://www.araskargo.com.tr/kargo-takip'
                    ]
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->shipping_service->getCarriers();

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
        $this->assertEquals('7b5567ff-abe7-487e-5c79-08d8e480366a', $result['data'][0]['id']);
        $this->assertEquals('PTT Kargo', $result['data'][0]['name']);
        $this->assertEquals('6d6e004a-23b1-43d1-4d30-08d8e87e3449', $result['data'][1]['id']);
        $this->assertEquals('Aras Kargo', $result['data'][1]['name']);
    }
} 