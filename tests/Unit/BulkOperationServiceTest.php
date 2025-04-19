<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\BulkOperationService;
use PHPUnit\Framework\TestCase;

class BulkOperationServiceTest extends TestCase
{
    private MockHandler $mock_handler;
    private PazaramaSpApi $api;
    private BulkOperationService $bulk_operation_service;

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
        $this->bulk_operation_service = new BulkOperationService($this->api);
    }

    public function testUpdatePrices()
    {
        // Mock response for bulk price update
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'error' => null,
                        'item' => null,
                        'success' => [
                            '84545410012478',
                            '772515145143'
                        ],
                        'fail' => [],
                        'approve' => []
                    ]
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Price update data
        $price_updates = [
            [
                'code' => '84545410012478',
                'listPrice' => 10000.99,
                'salePrice' => 10000.99
            ],
            [
                'code' => '772515145143',
                'listPrice' => 10001.99,
                'salePrice' => 10001.99
            ]
        ];

        // Call the method
        $result = $this->bulk_operation_service->updatePrices($price_updates);

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data'][0]['success']);
        $this->assertEquals('84545410012478', $result['data'][0]['success'][0]);
        $this->assertEquals('772515145143', $result['data'][0]['success'][1]);
    }

    public function testUpdateStocks()
    {
        // Mock response for bulk stock update
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'error' => null,
                        'item' => null,
                        'success' => [
                            '84545410012478'
                        ],
                        'fail' => [
                            '7725151451431'
                        ],
                        'approve' => null
                    ]
                ],
                'success' => false,
                'messageCode' => null,
                'message' => '1 adet ürün güncellenemedi.',
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Stock update data
        $stock_updates = [
            [
                'code' => '84545410012478',
                'stockCount' => 5
            ],
            [
                'code' => '7725151451431',
                'stockCount' => 3
            ]
        ];

        // Call the method
        $result = $this->bulk_operation_service->updateStocks($stock_updates);

        // Assertions
        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
        $this->assertEquals('1 adet ürün güncellenemedi.', $result['message']);
        $this->assertCount(1, $result['data'][0]['success']);
        $this->assertCount(1, $result['data'][0]['fail']);
        $this->assertEquals('84545410012478', $result['data'][0]['success'][0]);
        $this->assertEquals('7725151451431', $result['data'][0]['fail'][0]);
    }

    public function testCreateProducts()
    {
        // Mock response for bulk product creation
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'batchRequestId' => '3dd1d175-01e5-4fd6-9b6c-76b41b090534',
                    'creationDate' => '2021-05-24T12:29:20.6184712+00:00'
                ],
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Product data
        $products = [
            [
                'code' => '1294058385560',
                'name' => 'Samsung 1TB SSD Harddisk',
                'displayName' => 'Samsung 1TB HDD',
                'description' => 'Yüksek kapasiteli, hızlı harddisk',
                'groupCode' => '',
                'brandId' => '1dcfce4a-8fa2-41ae-b0ce-08d8dcdcce53',
                'desi' => 1,
                'stockCount' => 5,
                'stockCode' => 'smsnghdd1',
                'currencyType' => 'TRY',
                'listPrice' => 750.99,
                'salePrice' => 720.99,
                'vatRate' => 18,
                'images' => [
                    [
                        'imageurl' => 'https://example.com/image1.jpg'
                    ]
                ],
                'categoryId' => '429844d8-a148-40cd-ad25-aa4f200c7041',
                'attributes' => [
                    [
                        'attributeId' => 'fd04fbbf-2182-485c-a559-d251ba53c70f',
                        'attributeValueId' => 'b6b8542f-ee76-49de-9c22-17f67b401c92'
                    ]
                ]
            ]
        ];

        // Call the method
        $result = $this->bulk_operation_service->createProducts(['products' => $products]);

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('3dd1d175-01e5-4fd6-9b6c-76b41b090534', $result['data']['batchRequestId']);
    }

    public function testCheckBatchResult()
    {
        // Mock response for batch result check
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'status' => 2,
                    'batchRequestId' => '6601b819-7635-44d6-8579-e5d3ab938e5b',
                    'batchResult' => [],
                    'totalCount' => 0,
                    'failedCount' => 0,
                    'creationDate' => '2022-10-17T11:28:15.022+03:00'
                ],
                'success' => true,
                'messageCode' => 'BSK0',
                'message' => null,
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Call the method
        $result = $this->bulk_operation_service->checkBatchResult('6601b819-7635-44d6-8579-e5d3ab938e5b');

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('BSK0', $result['messageCode']);
        $this->assertEquals(2, $result['data']['status']); // Done status
        $this->assertEquals('6601b819-7635-44d6-8579-e5d3ab938e5b', $result['data']['batchRequestId']);
        $this->assertEquals(0, $result['data']['failedCount']);
    }
} 