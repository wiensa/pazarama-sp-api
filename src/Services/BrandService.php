<?php

namespace PazaramaApi\PazaramaSpApi\Services;

use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

class BrandService
{
    private PazaramaSpApi $api;

    public function __construct(PazaramaSpApi $api)
    {
        $this->api = $api;
    }

    /**
     * Marka listesini getirir
     *
     * @param int $page Sayfa numarası (varsayılan: 1)
     * @param int $size Sayfa başına marka sayısı (varsayılan: 100)
     * @param string|null $name Marka adı ile filtreleme (opsiyonel)
     * @return array
     */
    public function getBrands(int $page = 1, int $size = 100, ?string $name = null): array
    {
        $params = [
            'Page' => $page,
            'Size' => $size
        ];
        
        if ($name !== null) {
            $params['name'] = $name;
        }
        
        return $this->api->request('GET', '/brand/getBrands', $params);
    }

    /**
     * Marka detayını ID ile getirir (Pazarama API'de direkt olarak böyle bir servis bulunmamaktadır.
     * Tüm markaları getirip filtreleme yapılabilir)
     *
     * @param string $id Marka ID'si
     * @return array|null
     */
    public function getBrand(string $id): ?array
    {
        $brands = $this->getBrands(1, 1000);
        
        if (!isset($brands['data'])) {
            return null;
        }
        
        foreach ($brands['data'] as $brand) {
            if ($brand['id'] === $id) {
                return $brand;
            }
        }
        
        return null;
    }

    /**
     * Marka detayını adı ile getirir (Pazarama API'de direkt olarak böyle bir servis bulunmamaktadır.
     * İsim parametresi ile filtreleme yapılabilir)
     *
     * @param string $name Marka adı
     * @return array|null
     */
    public function getBrandByName(string $name): ?array
    {
        $brands = $this->getBrands(1, 100, $name);
        
        if (!isset($brands['data']) || empty($brands['data'])) {
            return null;
        }
        
        // Değiştirilen kısım: API yanıt yapısına göre uyarlandı
        // Eğer data içinde 'items' anahtarı varsa, bu koleksiyonun ilk elemanını döndür
        if (isset($brands['data']['items']) && !empty($brands['data']['items'])) {
            return $brands['data']['items'][0];
        }
        
        // Eğer doğrudan brands['data'] bir dizi ise ve elemanı varsa ilk elemanı döndür
        if (is_array($brands['data']) && count($brands['data']) > 0) {
            return $brands['data'][0];
        }
        
        // Hiçbir eleman bulunamazsa null döndür
        return null;
    }
} 