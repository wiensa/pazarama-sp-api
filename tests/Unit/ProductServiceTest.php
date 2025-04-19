<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\ProductService;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    private MockHandler $mock_handler;
    private PazaramaSpApi $api;
    private ProductService $product_service;

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
        $this->product_service = new ProductService($this->api);
    }

    public function testGetProducts()
    {
        // Mock response for the products request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'name' => 'Test Product 1',
                        'displayName' => 'Test Product 1',
                        'description' => 'Test Product Description',
                        'brandName' => 'Test Brand',
                        'code' => 'TEST-PRODUCT-1',
                        'groupCode' => '',
                        'stockCount' => 10,
                        'stockCode' => null,
                        'priorityRank' => 0,
                        'listPrice' => 100.00,
                        'salePrice' => 90.00,
                        'vatRate' => 18,
                        'categoryName' => 'Test Category'
                    ]
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->product_service->getProducts(['Approved' => true]);

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('Test Product 1', $result['data'][0]['name']);
        $this->assertEquals('TEST-PRODUCT-1', $result['data'][0]['code']);
    }

    public function testGetProduct()
    {
        // Mock response for the product detail request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'name' => 'Test Product 1',
                    'displayName' => 'Test Product 1',
                    'description' => 'Test Product Description',
                    'brandId' => 'brand-id-1',
                    'brandName' => 'Test Brand',
                    'code' => 'TEST-PRODUCT-1',
                    'stockCount' => 10,
                    'stockCode' => null,
                    'priorityRank' => 0,
                    'vatRate' => 18,
                    'listPrice' => 100.00,
                    'salePrice' => 90.00,
                    'installmentCount' => 0,
                    'categoryId' => 'category-id-1',
                    'state' => 3,
                    'stateDescription' => 'Onaylandı',
                    'attributes' => [],
                    'images' => [],
                    'groupCode' => '',
                    'badges' => [],
                    'isCatalogProduct' => true
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->product_service->getProduct('TEST-PRODUCT-1');

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Test Product 1', $result['data']['name']);
        $this->assertEquals('TEST-PRODUCT-1', $result['data']['code']);
    }

    public function testCreateProduct()
    {
        // Sample product data
        $product_data = [
            'name' => 'New Test Product',
            'displayName' => 'New Test Product',
            'description' => 'New Test Product Description',
            'brandId' => 'brand-id-1',
            'desi' => 1,
            'code' => 'NEW-TEST-PRODUCT',
            'groupCode' => '',
            'stockCount' => 5,
            'stockCode' => 'stock-code-1',
            'vatRate' => 18,
            'listPrice' => 150.00,
            'salePrice' => 140.00,
            'categoryId' => 'category-id-1',
            'attributes' => [],
            'images' => [],
            'deliveries' => []
        ];

        // Mock response for the create product request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'batchRequestId' => 'batch-request-id-1',
                    'creationDate' => '2023-01-01T12:00:00Z'
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->product_service->createProduct($product_data);

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('batch-request-id-1', $result['data']['batchRequestId']);
    }

    public function testUpdateProductPrice()
    {
        // Mock response for the update price request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'error' => null,
                        'item' => null,
                        'success' => ['NEW-TEST-PRODUCT'],
                        'fail' => [],
                        'approve' => []
                    ]
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->product_service->updateProductPrice('NEW-TEST-PRODUCT', 160.00, 150.00);

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('NEW-TEST-PRODUCT', $result['data'][0]['success'][0]);
    }

    public function testUpdateProductStock()
    {
        // Mock response for the update stock request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'error' => null,
                        'item' => null,
                        'success' => ['NEW-TEST-PRODUCT'],
                        'fail' => [],
                        'approve' => []
                    ]
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->product_service->updateProductStock('NEW-TEST-PRODUCT', 15);

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('NEW-TEST-PRODUCT', $result['data'][0]['success'][0]);
    }

    public function testCheckBatchResult()
    {
        // Mock response for the batch result request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'status' => 2,
                    'batchRequestId' => 'batch-request-id-1',
                    'batchResult' => [],
                    'totalCount' => 1,
                    'failedCount' => 0,
                    'creationDate' => '2023-01-01T12:00:00Z'
                ],
                'success' => true,
                'messageCode' => 'BSK0',
                'message' => null,
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Call the method
        $result = $this->product_service->checkBatchResult('batch-request-id-1');

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['data']['status']); // Done = 2
        $this->assertEquals('batch-request-id-1', $result['data']['batchRequestId']);
    }
} 