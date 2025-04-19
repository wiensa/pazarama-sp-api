<?php

namespace PazaramaApi\PazaramaSpApi\Services;

use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

class ProductService
{
    private PazaramaSpApi $api;

    public function __construct(PazaramaSpApi $api)
    {
        $this->api = $api;
    }

    /**
     * Ürün listesini getirir
     *
     * @param array $params Filtre parametreleri
     * @return array
     */
    public function getProducts(array $params = []): array
    {
        return $this->api->request('GET', '/product/products', $params);
    }

    /**
     * Tekil ürün bilgisi getirir
     *
     * @param string $id Ürün ID'si ya da kodu
     * @return array
     */
    public function getProduct(string $id): array
    {
        return $this->api->request('POST', '/product/getProductDetail', [
            'Code' => $id
        ]);
    }

    /**
     * Ürünleri onay durumuna göre filtreler
     * 
     * @param bool $approved Onaylanmış ürünler için true, onaylanmamışlar için false
     * @param string|null $code Ürün kodu (opsiyonel)
     * @param int $page Sayfa numarası
     * @param int $size Sayfa başına ürün sayısı
     * @return array
     */
    public function filterProducts(bool $approved, ?string $code = null, int $page = 1, int $size = 100): array
    {
        $params = [
            'Approved' => $approved,
            'Page' => $page,
            'Size' => $size
        ];
        
        if ($code !== null) {
            $params['Code'] = $code;
        }
        
        return $this->api->request('GET', '/product/products', $params);
    }

    /**
     * Yeni ürün oluşturur
     *
     * @param array $product_data Ürün verileri
     * @return array
     */
    public function createProduct(array $product_data): array
    {
        return $this->api->request('POST', '/product/create', [
            'products' => [$product_data]
        ]);
    }

    /**
     * Toplu ürün oluşturur
     *
     * @param array $products Ürün verileri dizisi
     * @return array
     */
    public function createProducts(array $products): array
    {
        return $this->api->request('POST', '/product/create', [
            'products' => $products
        ]);
    }

    /**
     * Batch işlem sonucunu kontrol eder
     *
     * @param string $batch_request_id Batch işlem ID'si
     * @return array
     */
    public function checkBatchResult(string $batch_request_id): array
    {
        return $this->api->request('GET', '/product/getProductBatchResult', [
            'BatchRequestId' => $batch_request_id
        ]);
    }

    /**
     * Ürün fiyatını günceller
     *
     * @param string $code Ürün kodu (barkod)
     * @param float $list_price Liste fiyatı
     * @param float $sale_price Satış fiyatı
     * @return array
     */
    public function updateProductPrice(string $code, float $list_price, float $sale_price): array
    {
        return $this->api->request('POST', '/product/updatePrice', [
            'items' => [
                [
                    'code' => $code,
                    'listPrice' => $list_price,
                    'salePrice' => $sale_price
                ]
            ]
        ]);
    }

    /**
     * Toplu ürün fiyatlarını günceller
     *
     * @param array $price_updates Fiyat güncellemeleri dizisi [['code' => '...', 'listPrice' => 100, 'salePrice' => 90], ...]
     * @return array
     */
    public function updateProductPrices(array $price_updates): array
    {
        return $this->api->request('POST', '/product/updatePrice', [
            'items' => $price_updates
        ]);
    }

    /**
     * Ürün stok bilgisini günceller
     *
     * @param string $code Ürün kodu (barkod)
     * @param int $stock Yeni stok miktarı
     * @return array
     */
    public function updateProductStock(string $code, int $stock): array
    {
        return $this->api->request('POST', '/product/updateStock', [
            'items' => [
                [
                    'code' => $code,
                    'stockCount' => $stock
                ]
            ]
        ]);
    }

    /**
     * Toplu ürün stoklarını günceller
     *
     * @param array $stock_updates Stok güncellemeleri dizisi [['code' => '...', 'stockCount' => 10], ...]
     * @return array
     */
    public function updateProductStocks(array $stock_updates): array
    {
        return $this->api->request('POST', '/product/updateStock', [
            'items' => $stock_updates
        ]);
    }
} 