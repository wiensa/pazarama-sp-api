<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\CategoryService;
use PHPUnit\Framework\TestCase;

class CategoryServiceTest extends TestCase
{
    private MockHandler $mock_handler;
    private PazaramaSpApi $api;
    private CategoryService $category_service;

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
        $this->category_service = new CategoryService($this->api);
    }

    public function testGetCategories()
    {
        // Mock response for the categories request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    [
                        'id' => '3053ce72-9208-40bb-8775-001fb4ca654b',
                        'parentId' => '00b8be55-5ec1-46fd-97d9-1f70cfed5a12',
                        'code' => null,
                        'parentCategories' => [
                            'Otomobil, Motosiklet ve Aksesuarları',
                            'Oto Aksesuarları',
                            'Oto Yedek Parça',
                            'Elektrik Aksam',
                            'Isı Sensörleri'
                        ],
                        'name' => 'Isı Sensörleri',
                        'displayName' => 'Isı Sensörleri',
                        'displayOrder' => 1,
                        'description' => null,
                        'leaf' => true
                    ]
                ]
            ]))
        );

        // Call the method
        $result = $this->category_service->getCategories();

        // Assertions
        $this->assertIsArray($result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('Isı Sensörleri', $result['data'][0]['name']);
        $this->assertTrue($result['data'][0]['leaf']);
    }

    public function testGetCategoryAttributes()
    {
        // Mock response for the category attributes request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'id' => 'b861ef2f-d968-4a80-a9f2-0021e346bfb0',
                    'name' => 'Oyuncu Monitör',
                    'displayName' => 'Oyuncu Monitör',
                    'attributes' => [
                        [
                            'id' => '27a48591-18e7-46cb-aa2a-14b946018fb7',
                            'name' => 'Vesa Uyumluluğu',
                            'displayName' => 'Vesa Uyumluluğu',
                            'isVariantable' => false,
                            'isRequired' => true,
                            'attributeValues' => [
                                [
                                    'id' => 'b9c20835-f1ae-41a5-8509-31b209feabd9',
                                    'value' => 'Yok'
                                ],
                                [
                                    'id' => 'aaa01498-3bc7-4bd0-90aa-d8f489cc7a73',
                                    'value' => 'Var'
                                ]
                            ]
                        ],
                        [
                            'id' => 'b67160bc-162f-441a-92f2-694ff41ca760',
                            'name' => 'Renk',
                            'displayName' => 'Renk',
                            'isVariantable' => false,
                            'isRequired' => true,
                            'attributeValues' => []
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
        $result = $this->category_service->getCategoryAttributes('b861ef2f-d968-4a80-a9f2-0021e346bfb0');

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Oyuncu Monitör', $result['data']['name']);
        $this->assertCount(2, $result['data']['attributes']);
        $this->assertEquals('Vesa Uyumluluğu', $result['data']['attributes'][0]['name']);
        $this->assertEquals('Renk', $result['data']['attributes'][1]['name']);
    }

    public function testGetRequiredAttributesForCategory()
    {
        // Mock response for the category attributes request
        $this->mock_handler->append(
            new Response(200, [], json_encode([
                'data' => [
                    'id' => 'b861ef2f-d968-4a80-a9f2-0021e346bfb0',
                    'name' => 'Oyuncu Monitör',
                    'displayName' => 'Oyuncu Monitör',
                    'attributes' => [
                        [
                            'id' => '27a48591-18e7-46cb-aa2a-14b946018fb7',
                            'name' => 'Vesa Uyumluluğu',
                            'displayName' => 'Vesa Uyumluluğu',
                            'isVariantable' => false,
                            'isRequired' => true,
                            'attributeValues' => [
                                [
                                    'id' => 'b9c20835-f1ae-41a5-8509-31b209feabd9',
                                    'value' => 'Yok'
                                ],
                                [
                                    'id' => 'aaa01498-3bc7-4bd0-90aa-d8f489cc7a73',
                                    'value' => 'Var'
                                ]
                            ]
                        ],
                        [
                            'id' => 'b67160bc-162f-441a-92f2-694ff41ca760',
                            'name' => 'Renk',
                            'displayName' => 'Renk',
                            'isVariantable' => false,
                            'isRequired' => false,
                            'attributeValues' => []
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
        $result = $this->category_service->getRequiredAttributesForCategory('b861ef2f-d968-4a80-a9f2-0021e346bfb0');

        // Assertions
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('Oyuncu Monitör', $result['data']['name']);
        $this->assertCount(2, $result['data']['attributes']);
        $this->assertTrue($result['data']['attributes'][0]['isRequired']);
    }
} 