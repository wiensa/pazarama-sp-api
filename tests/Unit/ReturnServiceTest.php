<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\ReturnService;
use PHPUnit\Framework\TestCase;

class ReturnServiceTest extends TestCase
{
    private MockHandler $mock_handler;
    private PazaramaSpApi $api;
    private ReturnService $return_service;

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
        $this->return_service = new ReturnService($this->api);
    }

    public function testGetReturns()
    {
        // Mock response for returns request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'responsePage' => [
                        'pageSize' => 10,
                        'pageIndex' => 1,
                        'totalCount' => 1,
                        'totalPages' => 1
                    ],
                    'pageReport' => [
                        'totalRefundCount' => 1,
                        'totalWaitingRefundCount' => 1,
                        'totalApprovedRefundCount' => 0,
                        'totalRejectedRefundCount' => 0
                    ],
                    'refundList' => [
                        [
                            'id' => 1,
                            'refundId' => 'ef2affaf-fdfc-4128-b5bc-3a4c1129e662',
                            'orderNumber' => 818589784,
                            'orderDate' => '6 Ekim 2021 14:53',
                            'refundNumber' => 564213,
                            'refundType' => 'Ürünün parçası eksik',
                            'refundStatus' => 1,
                            'refundStatusName' => 'İade Onayı Bekliyor',
                            'paymentType' => 'Banka/Kredi Kartı',
                            'refundDate' => '6 Ekim 2021 19:46',
                            'totalAmount' => [],
                            'refundAmount' => [],
                            'customerId' => '',
                            'customerName' => '',
                            'customerEmail' => '',
                            'customerPhoneNumber' => '',
                            'customerAddress' => '',
                            'productName' => '',
                            'productCode' => '',
                            'productStockCode' => null,
                            'shipmentCompanyName' => '',
                            'shipmentCode' => 0,
                            'description' => 'test',
                            'boDescription' => null
                        ]
                    ]
                ],
                'success' => true,
                'messageCode' => 'ORD0',
                'message' => null,
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Call the method
        $result = $this->return_service->getReturns([
            'pageSize' => 10,
            'pageNumber' => 1,
            'refundStatus' => 1,
            'requestStartDate' => '2021-10-01',
            'requestEndDate' => '2021-10-31'
        ]);

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['data']['responsePage']['totalCount']);
        $this->assertEquals('ef2affaf-fdfc-4128-b5bc-3a4c1129e662', $result['data']['refundList'][0]['refundId']);
    }

    public function testGetReturn()
    {
        // Mock response for specific return request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'id' => 1,
                    'refundId' => 'ef2affaf-fdfc-4128-b5bc-3a4c1129e662',
                    'orderNumber' => 818589784,
                    'orderDate' => '6 Ekim 2021 14:53',
                    'refundNumber' => 564213,
                    'refundType' => 'Ürünün parçası eksik',
                    'refundStatus' => 1,
                    'refundStatusName' => 'İade Onayı Bekliyor',
                    'paymentType' => 'Banka/Kredi Kartı',
                    'refundDate' => '6 Ekim 2021 19:46',
                    'totalAmount' => [],
                    'refundAmount' => [],
                    'customerId' => '',
                    'customerName' => '',
                    'customerEmail' => '',
                    'customerPhoneNumber' => '',
                    'customerAddress' => '',
                    'productName' => '',
                    'productCode' => '',
                    'productStockCode' => null,
                    'shipmentCompanyName' => '',
                    'shipmentCode' => 0,
                    'description' => 'test',
                    'boDescription' => null
                ],
                'success' => true,
                'messageCode' => 'ORD0',
                'message' => null,
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Call the method
        $result = $this->return_service->getReturn('ef2affaf-fdfc-4128-b5bc-3a4c1129e662');

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('ef2affaf-fdfc-4128-b5bc-3a4c1129e662', $result['data']['refundId']);
        $this->assertEquals(1, $result['data']['refundStatus']);
        $this->assertEquals('İade Onayı Bekliyor', $result['data']['refundStatusName']);
    }

    public function testUpdateRefundStatus()
    {
        // Mock response for refund status update
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'orderNumber' => 818589784,
                    'requestNo' => 564213,
                    'refundId' => 'ef2affaf-fdfc-4128-b5bc-3a4c1129e662',
                    'orderItemId' => 'e6cc3d81-2805-46e4-9b93-0dfe824b77ad',
                    'refundType' => 'Ürünün parçası eksik',
                    'refundStatus' => 'Tedarikçi Tarafından Onaylandı'
                ],
                'success' => true,
                'messageCode' => 'ORD0',
                'message' => 'ORD0',
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->return_service->updateRefundStatus(
            'ef2affaf-fdfc-4128-b5bc-3a4c1129e662',
            2 // Tedarikçi Tarafından Onaylandı
        );

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('ef2affaf-fdfc-4128-b5bc-3a4c1129e662', $result['data']['refundId']);
        $this->assertEquals('Tedarikçi Tarafından Onaylandı', $result['data']['refundStatus']);
    }

    public function testGetReturnReasons()
    {
        // Mock response for return reasons request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Ürünün parçası eksik',
                        'description' => 'Ürün eksik parçalarla gönderilmiş'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Hatalı ürün gönderimi',
                        'description' => 'Farklı ürün gönderilmiş'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Hasarlı ürün',
                        'description' => 'Ürün hasarlı şekilde gelmiş'
                    ]
                ],
                'success' => true,
                'messageCode' => 'ORD0',
                'message' => null,
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Call the method
        $result = $this->return_service->getReturnReasons();

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['data']);
        $this->assertEquals('Ürünün parçası eksik', $result['data'][0]['name']);
        $this->assertEquals('Hatalı ürün gönderimi', $result['data'][1]['name']);
        $this->assertEquals('Hasarlı ürün', $result['data'][2]['name']);
    }

    public function testGetReturnStatuses()
    {
        // Mock response for return statuses request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'İade Onayı Bekliyor',
                        'description' => 'İade satıcı onayı bekliyor'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Tedarikçi Tarafından Onaylandı',
                        'description' => 'İade satıcı tarafından onaylandı'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Tedarikçi Tarafından Reddedildi',
                        'description' => 'İade satıcı tarafından reddedildi'
                    ]
                ],
                'success' => true,
                'messageCode' => 'ORD0',
                'message' => null,
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Call the method
        $result = $this->return_service->getReturnStatuses();

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['data']);
        $this->assertEquals('İade Onayı Bekliyor', $result['data'][0]['name']);
        $this->assertEquals('Tedarikçi Tarafından Onaylandı', $result['data'][1]['name']);
        $this->assertEquals('Tedarikçi Tarafından Reddedildi', $result['data'][2]['name']);
    }
} 