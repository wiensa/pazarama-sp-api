# Pazarama SP API - Laravel Paketi

Bu paket, Laravel uygulamalarında Pazarama Satıcı Paneli (SP) API ile entegrasyonu kolaylaştırmak için geliştirilmiştir. Pazarama marketplace üzerinde ürün yönetimi, sipariş takibi, iade işlemleri ve diğer temel işlemleri yönetmenize olanak sağlar.

## Özellikler

- Pazarama API ile otomatik kimlik doğrulama ve token yönetimi
- Ürün yönetimi (listeleme, görüntüleme, ekleme, güncelleme)
- Sipariş yönetimi (listeleme, görüntüleme, durum güncellemeleri)
- Kategori bilgilerine erişim
- Marka yönetimi
- Kargo ve teslimat yönetimi
- İade işlemleri
- Toplu işlemler (fiyat, stok güncellemeleri)
- Muhasebe ve finans bilgilerine erişim

## Gereksinimler

- PHP 8.1 veya üzeri
- Laravel 9.0 veya üzeri
- Guzzle HTTP 7.0 veya üzeri

## Kurulum

Paketi Composer ile kurun:

```bash
composer require wiensa/pazarama-sp-api
```

Laravel 5.5+ versiyonları için servis sağlayıcısı otomatik olarak kaydedilecektir. Daha eski versiyonlar için, `config/app.php` dosyasındaki `providers` dizisine aşağıdaki satırı ekleyin:

```php
PazaramaApi\PazaramaSpApi\Providers\PazaramaSpApiServiceProvider::class,
```

(Opsiyonel) Alias eklemek için `config/app.php` dosyasındaki `aliases` dizisine aşağıdaki satırı ekleyin:

```php
'PazaramaSpApi' => PazaramaApi\PazaramaSpApi\Facades\PazaramaSpApi::class,
```

Yapılandırma dosyasını yayınlamak için aşağıdaki komutu çalıştırın:

```bash
php artisan vendor:publish --provider="PazaramaApi\PazaramaSpApi\Providers\PazaramaSpApiServiceProvider" --tag="config"
```

## Yapılandırma

`config/pazarama-sp-api.php` dosyasını düzenleyebilir veya `.env` dosyanıza aşağıdaki değişkenleri ekleyebilirsiniz:

```dotenv
PAZARAMA_CLIENT_ID=your-client-id
PAZARAMA_CLIENT_SECRET=your-client-secret
PAZARAMA_API_TIMEOUT=30
PAZARAMA_API_DEBUG=false
```

API kimlik bilgilerinizi (client_id ve client_secret) Pazarama Satıcı Paneli üzerindeki Hesap Bilgileri alanından edinebilirsiniz.

## Kullanım

### Facade ile Kullanım

```php
use PazaramaApi\PazaramaSpApi\Facades\PazaramaSpApi;

// Ürün listesini getir
$products = PazaramaSpApi::getProducts(['Approved' => true, 'Size' => 100, 'Page' => 1]);

// Belirli bir ürünü getir
$product = PazaramaSpApi::getProduct('urun-kodu');

// Sipariş listesini getir
$orders = PazaramaSpApi::getOrders([
    'startDate' => '2023-01-01',
    'endDate' => '2023-01-31'
]);

// Kategori listesini getir
$categories = PazaramaSpApi::getCategories();

// Marka listesini getir
$brands = PazaramaSpApi::getBrands(1, 100);
```

### Helper Fonksiyonu ile Kullanım

```php
// Ürün listesini getir
$products = pazarama_api()->getProducts(['Approved' => true]);

// Ürün servisine erişim
$productService = pazarama_api()->products();
$product = $productService->get('urun-kodu');

// Sipariş servisine erişim
$orderService = pazarama_api()->orders();
$orders = $orderService->list([
    'startDate' => '2023-01-01',
    'endDate' => '2023-01-31'
]);

// Kategori servisine erişim
$categoryService = pazarama_api()->categories();
$categories = $categoryService->list();
```

### Servis Container ile Kullanım

```php
// Pazarama API örneğini al
$api = app('pazarama-sp-api');

// Ürün listesini getir
$products = $api->getProducts(['Approved' => true]);
```

## Servisler

Paket aşağıdaki servisleri içermektedir:

### Ürün Yönetimi (ProductService)

```php
// Ürün servisine erişim
$productService = pazarama_api()->products();

// Ürün listesini getir
$products = $productService->list(['Approved' => true, 'Size' => 100, 'Page' => 1]);

// Belirli bir ürünü getir
$product = $productService->get('urun-kodu');

// Yeni ürün oluştur
$productData = [
    'code' => 'urun-kodu-123',
    'name' => 'Samsung 1TB SSD Harddisk',
    'displayName' => 'Samsung 1TB HDD',
    'description' => 'Yüksek kapasiteli, hızlı harddisk',
    'groupCode' => '',
    'brandId' => '1dcfce4a-8fa2-41ae-b0ce-08d8dcdcce53',
    'desi' => 1,
    'stockCount' => 5,
    'stockCode' => 'smsnghdd1',
    'listPrice' => 750.99,
    'salePrice' => 720.99,
    'vatRate' => 18,
    'images' => [
        [
            'imageurl' => 'https://example.com/image1.jpg'
        ]
    ],
    'categoryId' => '429844d8-a148-40cd-ad25-aa4f200c7041',
    'attributes' => [
        [
            'attributeId' => 'fd04fbbf-2182-485c-a559-d251ba53c70f',
            'attributeValueId' => 'b6b8542f-ee76-49de-9c22-17f67b401c92'
        ]
    ]
];

$result = $productService->createProduct($productData);

// Ürün fiyatını güncelle
$result = $productService->updateProductPrice('urun-kodu-123', 799.99, 749.99);

// Ürün stok miktarını güncelle
$result = $productService->updateProductStock('urun-kodu-123', 10);

// Batch işlem sonucunu kontrol et
$result = $productService->checkBatchResult('batch-request-id');
```

### Sipariş Yönetimi (OrderService)

```php
// Sipariş servisine erişim
$orderService = pazarama_api()->orders();

// Sipariş listesini getir
$orders = $orderService->list([
    'startDate' => '2023-01-01',
    'endDate' => '2023-01-31'
]);

// Sipariş detayını getir
$order = $orderService->get('siparis-numarasi');

// Sipariş durumunu güncelle
$result = $orderService->updateOrderStatus('siparis-numarasi', 12); // 12: Siparişiniz Hazırlanıyor

// Kargo bilgisini güncelle
$result = $orderService->updateOrderShipment(
    'siparis-numarasi',
    'order-item-id',
    5, // 5: Siparişiniz Kargoya Verildi
    1, // 1: Kargo
    'kargo-takip-numarasi',
    'https://kargo-takip-url.com',
    'kargo-sirketi-id'
);

// Fatura linki güncelle
$result = $orderService->updateInvoiceLink(
    'https://fatura-link.com', 
    'siparis-id'
);

// Finansal bilgileri getir
$financialData = $orderService->getPaymentAgreement([
    'startDate' => '2023-01-01T00:00:01.768Z',
    'endDate' => '2023-01-31T23:59:59.768Z'
]);
```

### Kategori Yönetimi (CategoryService)

```php
// Kategori servisine erişim
$categoryService = pazarama_api()->categories();

// Kategori ağacını getir
$categories = $categoryService->list();

// Kategori özelliklerini getir
$categoryAttributes = $categoryService->getCategoryAttributes('kategori-id');

// Kategori için gerekli özellikleri getir
$requiredAttributes = $categoryService->getRequiredAttributesForCategory('kategori-id');
```

### Marka Yönetimi (BrandService)

```php
// Marka servisine erişim
$brandService = pazarama_api()->brands();

// Marka listesini getir
$brands = $brandService->list(1, 100);

// Markayı adına göre getir
$brand = $brandService->getBrandByName('Marka Adı');
```

### Kargo ve Teslimat Yönetimi (ShippingService)

```php
// Kargo servisine erişim
$shippingService = pazarama_api()->shipping();

// Teslimat tiplerini getir
$deliveryTypes = $shippingService->getSellerDelivery();

// Şehir listesini getir
$cities = $shippingService->getCities();

// Kargo firmalarını getir
$carriers = $shippingService->getCarriers();
```

### İade Yönetimi (ReturnService)

```php
// İade servisine erişim
$returnService = pazarama_api()->returns();

// İade listesini getir
$returns = $returnService->getReturns([
    'pageSize' => 10,
    'pageNumber' => 1,
    'refundStatus' => 1, // 1: İade Onayı Bekliyor
    'requestStartDate' => '2023-01-01',
    'requestEndDate' => '2023-01-31'
]);

// İade detayını getir
$return = $returnService->getReturn('iade-id');

// İade durumunu güncelle
$result = $returnService->updateRefundStatus(
    'iade-id',
    2 // 2: Tedarikçi Tarafından Onaylandı
);
```

### Toplu İşlemler (BulkOperationService)

```php
// Toplu işlem servisine erişim
$bulkService = pazarama_api()->bulk();

// Toplu fiyat güncelleme
$priceUpdates = [
    [
        'code' => 'urun-kodu-1',
        'listPrice' => 199.99,
        'salePrice' => 179.99
    ],
    [
        'code' => 'urun-kodu-2',
        'listPrice' => 299.99,
        'salePrice' => 279.99
    ]
];

$result = $bulkService->updatePrices($priceUpdates);

// Toplu stok güncelleme
$stockUpdates = [
    [
        'code' => 'urun-kodu-1',
        'stockCount' => 10
    ],
    [
        'code' => 'urun-kodu-2',
        'stockCount' => 20
    ]
];

$result = $bulkService->updateStocks($stockUpdates);

// Toplu ürün oluşturma
$products = [
    // Ürün verileri dizisi
];

$result = $bulkService->createProducts(['products' => $products]);

// Batch işlem sonucunu kontrol et
$result = $bulkService->checkBatchResult('batch-request-id');
```

## Hata Yönetimi

API istekleri sırasında oluşabilecek hataları yakalamak için try-catch bloğu kullanabilirsiniz:

```php
use PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException;

try {
    $products = pazarama_api()->products()->list();
} catch (PazaramaApiException $e) {
    // API hatası
    $statusCode = $e->getCode();
    $errorMessage = $e->getMessage();
    
    // Hatayı işleyin
} catch (\Exception $e) {
    // Diğer hatalar
    // Hatayı işleyin
}
```

## Durum Kodları

Pazarama API'den dönebilecek önemli durum kodları:

### Sipariş Durum Kodları

- 3: Siparişiniz Alındı
- 12: Siparişiniz Hazırlanıyor
- 13: Tedarik Edilemedi
- 5: Siparişiniz Kargoya Verildi
- 11: Teslim Edildi
- 14: Teslim Edilemedi
- 7: İade Süreci Başlatıldı
- 8: İade Onaylandı
- 9: İade Reddedildi
- 10: İade Edildi

### İade Durum Kodları

- 1: İade Onayı Bekliyor
- 2: Tedarikçi Tarafından Onaylandı
- 3: Tedarikçi Tarafından Reddedildi
- 4: Backoffice Tarafından Onaylandı
- 5: Backoffice Tarafından Reddedildi
- 6: Auto Approved

### Ödeme Tipleri

- 1: Kredi Kartı
- 2: İstanbul Kart
- 3: Taksitli Ek Hesap

### Teslimat Tipleri

- 1: Kargo (CargoDelivery)
- 2: Kurye (FastDelivery)
- 3: Mağazadan Teslimat (StoreDelivery)
- 4: Dijital

## Lisans

Bu paket MIT lisansı altında lisanslanmıştır. Detaylı bilgi için [LICENSE](LICENSE) dosyasına bakabilirsiniz. 