<?php

namespace PazaramaApi\PazaramaSpApi\Services;

use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

class BulkOperationService
{
    private PazaramaSpApi $api;
    private ProductService $product_service;

    public function __construct(PazaramaSpApi $api)
    {
        $this->api = $api;
        $this->product_service = new ProductService($api);
    }

    /**
     * Toplu ürün fiyatlarını günceller
     *
     * @param array $price_updates Fiyat güncellemeleri [['code' => 'sku1', 'listPrice' => 100.0, 'salePrice' => 90.0], ...]
     * @return array
     */
    public function updatePrices(array $price_updates): array
    {
        return $this->product_service->updateProductPrices($price_updates);
    }

    /**
     * Toplu ürün stok bilgilerini günceller
     *
     * @param array $stock_updates Stok güncellemeleri [['code' => 'sku1', 'stockCount' => 10], ...]
     * @return array
     */
    public function updateStocks(array $stock_updates): array
    {
        return $this->product_service->updateProductStocks($stock_updates);
    }

    /**
     * Toplu ürün oluşturur
     *
     * @param array $products Ürün verileri dizisi
     * @return array
     */
    public function createProducts(array $products): array
    {
        return $this->product_service->createProducts($products);
    }

    /**
     * Batch işlem sonucunu kontrol eder
     *
     * @param string $batch_request_id Batch işlem ID'si
     * @return array
     */
    public function checkBatchResult(string $batch_request_id): array
    {
        return $this->product_service->checkBatchResult($batch_request_id);
    }

    /**
     * Ürünlerin toplu durumunu günceller (aktif/pasif)
     *
     * @param array $status_updates Ürün durum güncellemeleri
     * @return array
     */
    public function updateStatuses(array $status_updates): array
    {
        return $this->api->request('POST', '/products/statuses/bulk', [
            'items' => $status_updates
        ]);
    }

    /**
     * Toplu kargo takip numarası günceller
     *
     * @param array $shipment_updates Sipariş kargo güncellemeleri
     * @return array
     */
    public function updateShipments(array $shipment_updates): array
    {
        return $this->api->request('POST', '/orders/shipments/bulk', [
            'items' => $shipment_updates
        ]);
    }
} 