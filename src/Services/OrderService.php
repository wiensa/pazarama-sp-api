<?php

namespace PazaramaApi\PazaramaSpApi\Services;

use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

class OrderService
{
    private PazaramaSpApi $api;

    public function __construct(PazaramaSpApi $api)
    {
        $this->api = $api;
    }

    /**
     * Sipariş listesini getirir
     *
     * @param array $params Filtre parametreleri (örn. orderNumber, startDate, endDate)
     * @return array
     */
    public function getOrders(array $params = []): array
    {
        return $this->api->request('POST', '/order/getOrdersForApi', $params);
    }

    /**
     * Tekil sipariş bilgisi getirir
     *
     * @param string $order_number Sipariş numarası
     * @return array
     */
    public function getOrder(string $order_number): array
    {
        return $this->api->request('POST', '/order/getOrdersForApi', [
            'orderNumber' => $order_number
        ]);
    }

    /**
     * Sipariş durumunu günceller (Ürün bazında)
     *
     * @param string $order_number Sipariş numarası
     * @param string $order_item_id Sipariş ürün ID'si
     * @param int $status Yeni durum kodu
     * @return array
     */
    public function updateOrderItemStatus(string $order_number, string $order_item_id, int $status): array
    {
        return $this->api->request('PUT', '/order/updateOrderStatus', [
            'orderNumber' => $order_number,
            'item' => [
                'orderItemId' => $order_item_id,
                'status' => $status
            ]
        ]);
    }

    /**
     * Siparişin tümünün durumunu günceller
     *
     * @param string $order_number Sipariş numarası
     * @param int $status Yeni durum kodu
     * @return array
     */
    public function updateOrderStatus(string $order_number, int $status): array
    {
        return $this->api->request('PUT', '/order/updateOrderStatusList', [
            'orderNumber' => $order_number,
            'status' => $status
        ]);
    }

    /**
     * Sipariş kargo takip bilgilerini günceller
     *
     * @param string $order_number Sipariş numarası
     * @param string $order_item_id Sipariş ürün ID'si
     * @param int $status Kargo durumu (5: Siparişiniz Kargoya Verildi)
     * @param int $delivery_type Teslimat tipi (1: Kargo)
     * @param string $tracking_number Kargo takip numarası
     * @param string $tracking_url Kargo takip URL'i
     * @param string $cargo_company_id Kargo şirketi ID'si
     * @return array
     */
    public function updateOrderShipment(string $order_number, string $order_item_id, int $status, int $delivery_type, string $tracking_number, string $tracking_url, string $cargo_company_id): array
    {
        return $this->api->request('PUT', '/order/updateOrderStatus', [
            'orderNumber' => $order_number,
            'item' => [
                'orderItemId' => $order_item_id,
                'status' => $status,
                'deliveryType' => $delivery_type,
                'shippingTrackingNumber' => $tracking_number,
                'trackingUrl' => $tracking_url,
                'cargoCompanyId' => $cargo_company_id
            ]
        ]);
    }

    /**
     * İade listesini getirir
     *
     * @param array $params Filtre parametreleri (refundStatus, requestStartDate, requestEndDate, pageSize, pageNumber)
     * @return array
     */
    public function getReturns(array $params = []): array
    {
        return $this->api->request('POST', '/order/getRefund', $params);
    }

    /**
     * Tekil iade bilgisini getirir
     *
     * @param string $refund_id İade ID'si
     * @return array
     */
    public function getReturn(string $refund_id): array
    {
        return $this->api->request('POST', '/order/getRefund', [
            'refundId' => $refund_id
        ]);
    }

    /**
     * İade alt öğelerini getirir
     *
     * @param string $refund_id İade ID'si
     * @return array
     */
    public function getReturnItems(string $refund_id): array
    {
        return $this->api->request('POST', '/order/getRefundItems', [
            'refundId' => $refund_id
        ]);
    }

    /**
     * İade talebini günceller (Onay veya Red)
     *
     * @param string $refund_id İade ID'si
     * @param int $status Durum kodu (2: Onay, 3: Red)
     * @return array
     */
    public function updateRefundStatus(string $refund_id, int $status): array
    {
        return $this->api->request('POST', '/order/updateRefund', [
            'refundId' => $refund_id,
            'status' => $status
        ]);
    }

    /**
     * Sipariş fatura linkini günceller
     *
     * @param string $order_id Sipariş ID'si
     * @param string $invoice_link Fatura linki
     * @param string|null $delivery_company_id Teslimat şirketi ID'si (isteğe bağlı)
     * @param string|null $tracking_number Kargo takip numarası (isteğe bağlı)
     * @return array
     */
    public function updateInvoiceLink(string $order_id, string $invoice_link, ?string $delivery_company_id = null, ?string $tracking_number = null): array
    {
        $data = [
            'invoiceLink' => $invoice_link,
            'orderid' => $order_id,
            'deliveryCompanyId' => $delivery_company_id,
            'trackingNumber' => $tracking_number
        ];

        return $this->api->request('POST', '/order/invoice-link', $data);
    }
} 