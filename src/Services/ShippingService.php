<?php

namespace PazaramaApi\PazaramaSpApi\Services;

use PazaramaApi\PazaramaSpApi\PazaramaSpApi;

class ShippingService
{
    private PazaramaSpApi $api;

    public function __construct(PazaramaSpApi $api)
    {
        $this->api = $api;
    }

    /**
     * Kargo şirketlerini listeler
     *
     * @return array
     */
    public function getCarriers(): array
    {
        return $this->api->request('GET', '/carriers');
    }

    /**
     * Kargo şablonlarını listeler
     *
     * @return array
     */
    public function getShippingTemplates(): array
    {
        return $this->api->request('GET', '/shipping/templates');
    }

    /**
     * Kargo şablonu oluşturur
     *
     * @param array $template_data Şablon verisi
     * @return array
     */
    public function createShippingTemplate(array $template_data): array
    {
        return $this->api->request('POST', '/shipping/templates', $template_data);
    }

    /**
     * Kargo şablonu günceller
     *
     * @param string $id Şablon ID'si
     * @param array $template_data Şablon verisi
     * @return array
     */
    public function updateShippingTemplate(string $id, array $template_data): array
    {
        return $this->api->request('PUT', "/shipping/templates/{$id}", $template_data);
    }

    /**
     * Kargo şablonu siler
     *
     * @param string $id Şablon ID'si
     * @return array
     */
    public function deleteShippingTemplate(string $id): array
    {
        return $this->api->request('DELETE', "/shipping/templates/{$id}");
    }

    /**
     * Kargo etiketi oluşturur
     *
     * @param string $order_id Sipariş ID'si
     * @return array
     */
    public function createShippingLabel(string $order_id): array
    {
        return $this->api->request('POST', "/orders/{$order_id}/shipping-label");
    }

    /**
     * Kargo etiketi indirir
     *
     * @param string $order_id Sipariş ID'si
     * @return array
     */
    public function downloadShippingLabel(string $order_id): array
    {
        return $this->api->request('GET', "/orders/{$order_id}/shipping-label");
    }
} 