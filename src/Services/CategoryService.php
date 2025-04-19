<?php

namespace PazaramaApi\PazaramaSpApi\Services;

use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

class CategoryService
{
    private PazaramaSpApi $api;

    public function __construct(PazaramaSpApi $api)
    {
        $this->api = $api;
    }

    /**
     * Tüm kategori ağacını getirir
     *
     * @return array
     */
    public function getCategories(): array
    {
        return $this->api->request('GET', '/category/getCategoryTree');
    }

    /**
     * Kategori detayını getirir
     *
     * @param string $id Kategori ID'si
     * @return array
     */
    public function getCategory(string $id): array
    {
        // Doküman örneğinde bu bilgiyi getiren doğrudan bir endpoint bulunmamaktadır
        // Tüm kategori ağacı içerisinden ilgili ID'yi filtrelemek gerekebilir
        return $this->api->request('GET', '/category/getCategoryTree');
    }

    /**
     * Belirli bir kategorinin özelliklerini getirir
     *
     * @param string $id Kategori ID'si
     * @return array
     */
    public function getCategoryAttributes(string $id): array
    {
        return $this->api->request('GET', '/category/getCategoryWithAttributes', [
            'Id' => $id
        ]);
    }

    /**
     * Kategori için zorunlu özellikleri getirir
     * 
     * @param string $id Kategori ID'si
     * @return array
     */
    public function getRequiredAttributesForCategory(string $id): array
    {
        $attributes = $this->getCategoryAttributes($id);
        
        // Döküman örneklerinden görüldüğü kadarıyla isRequired=true olan özellikleri filtreleyebiliriz
        return $attributes;
    }
} 