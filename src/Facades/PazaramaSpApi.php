<?php

namespace PazaramaApi\PazaramaSpApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string authenticate()
 * @method static array request(string $method, string $endpoint, array $data = [])
 * @method static \PazaramaApi\PazaramaSpApi\Services\ProductService products()
 * @method static \PazaramaApi\PazaramaSpApi\Services\OrderService orders()
 * @method static \PazaramaApi\PazaramaSpApi\Services\CategoryService categories()
 * @method static \PazaramaApi\PazaramaSpApi\Services\BulkOperationService bulk()
 * @method static \PazaramaApi\PazaramaSpApi\Services\ShippingService shipping()
 * @method static \PazaramaApi\PazaramaSpApi\Services\ReturnService returns()
 * @method static \PazaramaApi\PazaramaSpApi\Services\BrandService brands()
 * @method static array getProducts(array $params = [])
 * @method static array getProduct(string $id)
 * @method static array getOrders(array $params = [])
 * @method static array getOrder(string $id)
 * @method static array getCategories(array $params = [])
 * @method static array getCategory(string $id)
 * @method static array getBrands(int $page = 1, int $size = 100)
 * 
 * @see \PazaramaApi\PazaramaSpApi\PazaramaSpApi
 */
class PazaramaSpApi extends Facade
{
    /**
     * Facade accessor'ını döndürür
     */
    protected static function getFacadeAccessor(): string
    {
        return 'pazarama-sp-api';
    }
} 