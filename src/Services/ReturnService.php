<?php

namespace PazaramaApi\PazaramaSpApi\Services;

use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

class ReturnService
{
    private PazaramaSpApi $api;
    private OrderService $order_service;

    public function __construct(PazaramaSpApi $api)
    {
        $this->api = $api;
        $this->order_service = new OrderService($api);
    }

    /**
     * İade listesini getirir
     *
     * @param array $params Filtre parametreleri (refundStatus, requestStartDate, requestEndDate)
     * @return array
     */
    public function getReturns(array $params = []): array
    {
        return $this->order_service->getReturns($params);
    }

    /**
     * Tekil iade bilgisi getirir
     *
     * @param string $id İade talep numarası
     * @return array
     */
    public function getReturn(string $id): array
    {
        // Spesifik bir iade detayı getiren endpoint bulunmuyor
        // Tüm iadeleri getirip filtreleme yapılabilir
        $returns = $this->getReturns([
            'refundNumber' => $id
        ]);
        
        return $returns;
    }

    /**
     * İade talebini onaylar
     *
     * @param string $id İade ID'si
     * @return array
     */
    public function approveReturn(string $id): array
    {
        return $this->order_service->updateRefundStatus($id, 2); // 2: Tedarikçi Tarafından Onaylandı
    }

    /**
     * İade talebini reddeder
     *
     * @param string $id İade ID'si
     * @param string $reason_code Red nedeni kodu
     * @param string $reason_detail Red nedeni açıklaması
     * @return array
     */
    public function rejectReturn(string $id, string $reason_code = '', string $reason_detail = ''): array
    {
        return $this->order_service->updateRefundStatus($id, 3); // 3: Tedarikçi Tarafından Reddedildi
    }
} 