<?php

namespace PazaramaApi\PazaramaSpApi\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException;
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\ProductService;

/**
 * @group unit
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
});

afterEach(function () {
    Mockery::close();
});

test('kimlik doğrulama başarıyla yapılabilmeli', function () {
    $authResponse = [
        'access_token' => 'test_token',
        'expires_in' => 3600
    ];
    
    $this->mockAuthClient->shouldReceive('post')
        ->once()
        ->andReturn(new Response(200, [], json_encode($authResponse)));
    
    $token = $this->api->authenticate();
    
    expect($token)->toBe('test_token');
});

test('geçersiz kimlik bilgileri hata fırlatmalı', function () {
    $this->mockAuthClient->shouldReceive('post')
        ->once()
        ->andReturn(new Response(400, [], json_encode([
            'error' => 'invalid_client',
            'message' => 'Geçersiz kimlik bilgileri'
        ])));
    
    $action = fn() => $this->api->authenticate();
    
    expect($action)->toThrow(PazaramaApiException::class);
});

test('servis sınıfları düzgün şekilde başlatılabilmeli', function () {
    // Token atama
    setPrivateProperty($this->api, 'access_token', 'fake_token');
    setPrivateProperty($this->api, 'token_expires_at', time() + 3600);
    
    $productService = $this->api->products();
    
    expect($productService)->toBeInstanceOf(ProductService::class);
});

test('istek başarılı şekilde yapılabilmeli', function () {
    $mockResponse = [
        'success' => true,
        'data' => ['test' => 'data']
    ];
    
    // Token set et
    setPrivateProperty($this->api, 'access_token', 'fake_token');
    setPrivateProperty($this->api, 'token_expires_at', time() + 3600);
    
    // Mockery'nin her türlü parametre ile çağrılmasına izin ver
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withArgs(function ($method, $endpoint, $options) {
            // Argümanları kontrol et
            return $method === 'GET' && 
                   $endpoint === '/test-endpoint' && 
                   isset($options['headers']['Authorization']) &&
                   $options['headers']['Authorization'] === 'Bearer fake_token' &&
                   isset($options['query']) &&
                   $options['query'] === ['param' => 'value'];
        })
        ->andReturn(new Response(200, [], json_encode($mockResponse)));
    
    $result = $this->api->request('GET', '/test-endpoint', ['param' => 'value']);
    
    expect($result)->toBe($mockResponse);
});

test('istek hata durumunda istisnayı fırlatmalı', function () {
    $errorResponse = [
        'success' => false,
        'error' => 'Bir hata oluştu',
        'errorCode' => 1001
    ];
    
    // Token set et
    setPrivateProperty($this->api, 'access_token', 'fake_token');
    setPrivateProperty($this->api, 'token_expires_at', time() + 3600);
    
    // Mockery'nin her türlü parametre ile çağrılmasına izin ver
    $this->mockClient->shouldReceive('request')
        ->once()
        ->withAnyArgs()
        ->andReturn(new Response(400, [], json_encode($errorResponse)));
    
    $action = fn() => $this->api->request('GET', '/test-endpoint', []);
    
    expect($action)->toThrow(PazaramaApiException::class);
}); 