<?php

namespace PazaramaApi\PazaramaSpApi\Contracts;

interface ApiServiceInterface
{
    /**
     * API'ye POST isteği gönderir.
     * 
     * @param string $endpoint API bitiş noktası
     * @param array $data İstek verileri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     * 
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array;

    /**
     * API'ye GET isteği gönderir.
     * 
     * @param string $endpoint API bitiş noktası
     * @param array $params Sorgu parametreleri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     * 
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    public function get(string $endpoint, array $params = [], array $headers = []): array;

    /**
     * API'ye PUT isteği gönderir.
     * 
     * @param string $endpoint API bitiş noktası
     * @param array $data İstek verileri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     * 
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array;

    /**
     * API'ye DELETE isteği gönderir.
     * 
     * @param string $endpoint API bitiş noktası
     * @param array $params Sorgu parametreleri
     * @param array $headers İsteğe bağlı HTTP başlıkları
     * @return array
     * 
     * @throws \PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException
     */
    public function delete(string $endpoint, array $params = [], array $headers = []): array;

    /**
     * HTTP istemcisini döndürür.
     * 
     * @return \GuzzleHttp\Client
     */
    public function getHttpClient(): \GuzzleHttp\Client;
} 