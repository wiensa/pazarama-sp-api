<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\BrandService;
use PHPUnit\Framework\TestCase;

class BrandServiceTest extends TestCase
{
    private MockHandler $mock_handler;
    private PazaramaSpApi $api;
    private BrandService $brand_service;

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
        $this->brand_service = new BrandService($this->api);
    }

    public function testGetBrands()
    {
        // Mock response for the brands request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => '70676496-a99e-4337-9842-a4be4dc6d6e1',
                        'name' => 'Toys',
                        'logoUrl' => null,
                        'website' => null
                    ],
                    [
                        'id' => '2d358674-8e46-4334-beae-08d8f03a1bc7',
                        'name' => 'Dyson',
                        'logoUrl' => 'dyson.jpg',
                        'website' => 'www.dyson.com'
                    ]
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->brand_service->getBrands();

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
        $this->assertEquals('Toys', $result['data'][0]['name']);
        $this->assertEquals('Dyson', $result['data'][1]['name']);
    }

    public function testGetBrandByName()
    {
        // Mock response for the brands request with name filter
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => '2d358674-8e46-4334-beae-08d8f03a1bc7',
                        'name' => 'Dyson',
                        'logoUrl' => 'dyson.jpg',
                        'website' => 'www.dyson.com'
                    ]
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->brand_service->getBrandByName('Dyson');

        // Assertions
        $this->assertNotNull($result);
        $this->assertEquals('2d358674-8e46-4334-beae-08d8f03a1bc7', $result['id']);
        $this->assertEquals('Dyson', $result['name']);
    }

    public function testGetBrandReturnsNull()
    {
        // Mock response for the brands request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method with non-existent ID
        $result = $this->brand_service->getBrand('non-existent-id');

        // Assertions
        $this->assertNull($result);
    }
} 