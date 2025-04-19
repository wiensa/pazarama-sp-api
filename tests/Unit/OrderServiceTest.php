<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\OrderService;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private MockHandler $mock_handler;
    private PazaramaSpApi $api;
    private OrderService $order_service;

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
        $this->order_service = new OrderService($this->api);
    }

    public function testGetOrders()
    {
        // Mock response for orders request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'orderId' => 'd4fb5f23-6330-4eb0-a81c-35847560df86',
                        'orderNumber' => 235795225,
                        'orderDate' => '2023-01-25 15:22',
                        'orderAmount' => 1.00,
                        'shipmentAmount' => 0.00,
                        'discountAmount' => 0.20,
                        'discountDescription' => 'Moda100',
                        'currency' => 'TL',
                        'paymentType' => 1,
                        'orderStatus' => 3,
                        'customerId' => 'f442961c-d818-4de3-1e11-08d941fd1d2f',
                        'customerName' => 'Pazarama Sipariş',
                        'customerEmail' => 'pzrmsprs@pazarama.com',
                        'shipmentAddress' => [],
                        'billingAddress' => [],
                        'items' => []
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
        $result = $this->order_service->getOrders([
            'startDate' => '2023-01-01',
            'endDate' => '2023-01-31'
        ]);

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('ORD0', $result['messageCode']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(235795225, $result['data'][0]['orderNumber']);
        $this->assertEquals(3, $result['data'][0]['orderStatus']);
    }

    public function testGetOrder()
    {
        // Mock response for order request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'orderId' => 'd4fb5f23-6330-4eb0-a81c-35847560df86',
                        'orderNumber' => 235795225,
                        'orderDate' => '2023-01-25 15:22',
                        'orderAmount' => 1.00,
                        'shipmentAmount' => 0.00,
                        'discountAmount' => 0.20,
                        'discountDescription' => 'Moda100',
                        'currency' => 'TL',
                        'paymentType' => 1,
                        'orderStatus' => 3,
                        'customerId' => 'f442961c-d818-4de3-1e11-08d941fd1d2f',
                        'customerName' => 'Pazarama Sipariş',
                        'customerEmail' => 'pzrmsprs@pazarama.com',
                        'shipmentAddress' => [],
                        'billingAddress' => [],
                        'items' => []
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
        $result = $this->order_service->getOrder('235795225');

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('ORD0', $result['messageCode']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals(235795225, $result['data'][0]['orderNumber']);
    }

    public function testUpdateOrderItemStatus()
    {
        // Mock response for order status update
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => null,
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->order_service->updateOrderItemStatus(
            '235795225',
            '5516bd97-5344-4d22-a346-0f263c0f51ca',
            12 // Siparişiniz Hazırlanıyor
        );

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    public function testUpdateOrderStatus()
    {
        // Mock response for order status update
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => null,
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->order_service->updateOrderStatus(
            '235795225',
            12 // Siparişiniz Hazırlanıyor
        );

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    public function testUpdateOrderShipment()
    {
        // Mock response for order shipment update
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => null,
                'success' => true,
                'messageCode' => null,
                'message' => null,
                'userMessage' => null
            ]))
        );

        // Call the method
        $result = $this->order_service->updateOrderShipment(
            '235795225',
            '5516bd97-5344-4d22-a346-0f263c0f51ca',
            5, // Siparişiniz Kargoya Verildi
            1, // Kargo
            '123456789', // Kargo takip numarası
            'https://www.aras.com.tr/kargo-takip', // Kargo takip URL'i
            '7b5567ff-abe7-487e-5c79-08d8e480366a' // Kargo şirketi ID'si
        );

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
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
        $result = $this->order_service->getReturns([
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
        $result = $this->order_service->updateRefundStatus(
            'ef2affaf-fdfc-4128-b5bc-3a4c1129e662',
            2 // Tedarikçi Tarafından Onaylandı
        );

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('ef2affaf-fdfc-4128-b5bc-3a4c1129e662', $result['data']['refundId']);
        $this->assertEquals('Tedarikçi Tarafından Onaylandı', $result['data']['refundStatus']);
    }

    public function testUpdateInvoiceLink()
    {
        // Mock response for invoice link update
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => null,
                'success' => true,
                'messageCode' => 'ORD0',
                'message' => null,
                'userMessage' => null,
                'fromCache' => false
            ]))
        );

        // Call the method
        $result = $this->order_service->updateInvoiceLink(
            '0d145804-5d05-4e4a-a4a0-921e45c8a5e3',
            'https://faturaUrl.pdf',
            '6d6e004a-23b1-43d1-4d30-08d8e87e3449',
            '321321321'
        );

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('ORD0', $result['messageCode']);
    }
} 