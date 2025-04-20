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
PazaramaApi\PazaramaSpApi\Providers\PazaramaServiceProvider::class,
```

(Opsiyonel) Alias eklemek için `config/app.php` dosyasındaki `aliases` dizisine aşağıdaki satırı ekleyin:

```php
'Pazarama' => PazaramaApi\PazaramaSpApi\Facades\Pazarama::class,
```

Yapılandırma dosyasını yayınlamak için aşağıdaki komutu çalıştırın:

```bash
php artisan vendor:publish --provider="PazaramaApi\PazaramaSpApi\Providers\PazaramaServiceProvider" --tag="config"
```

## Yapılandırma

`config/pazarama-api.php` dosyasını düzenleyebilir veya `.env` dosyanıza aşağıdaki değişkenleri ekleyebilirsiniz:

```dotenv
PAZARAMA_CLIENT_ID=your-client-id
PAZARAMA_CLIENT_SECRET=your-client-secret
PAZARAMA_API_URL=https://isortagimapi.pazarama.com
PAZARAMA_AUTH_URL=https://isortagimgiris.pazarama.com/connect/token
PAZARAMA_TIMEOUT=30
PAZARAMA_DEBUG=false
PAZARAMA_RETRY_ATTEMPTS=3
PAZARAMA_RETRY_DELAY=1000
```

API kimlik bilgilerinizi (client_id ve client_secret) Pazarama Satıcı Paneli üzerindeki Hesap Bilgileri alanından edinebilirsiniz.

## Kullanım

### Facade ile Kullanım

```php
use PazaramaApi\PazaramaSpApi\Facades\Pazarama;

// Ürün servisine erişim
$products = Pazarama::products()->list(['Approved' => true, 'Size' => 100, 'Page' => 1]);

// Belirli bir ürünü getir
$product = Pazarama::products()->get('urun-kodu');

// Sipariş servisine erişim
$orders = Pazarama::orders()->list([
    'startDate' => '2023-01-01',
    'endDate' => '2023-01-31'
]);

// Kategori servisine erişim
$categories = Pazarama::categories()->list();

// Marka servisine erişim
$brands = Pazarama::brands()->list(1, 100);
```

### Helper Fonksiyonu ile Kullanım

```php
// Ürün servisine erişim
$productService = pazarama()->products();
$products = $productService->list(['Approved' => true]);

// Sipariş servisine erişim
$orderService = pazarama()->orders();
$orders = $orderService->list([
    'startDate' => '2023-01-01',
    'endDate' => '2023-01-31'
]);

// Kategori servisine erişim
$categoryService = pazarama()->categories();
$categories = $categoryService->list();
```

### Servis Container ile Kullanım

```php
// Pazarama API örneğini al
$api = app('pazarama');

// Ürün servisine erişim
$productService = $api->products();
$products = $productService->list(['Approved' => true]);

// Diğer servislere doğrudan erişebilirsiniz
$brandService = app('pazarama.brand');
$brands = $brandService->list();
```

## Servisler

Paket aşağıdaki servisleri içermektedir:

### Ürün Yönetimi (ProductService)

```php
// Ürün servisine erişim
$productService = pazarama()->products();

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

$result = $productService->create($productData);

// Ürün fiyatını güncelle
$result = $productService->updatePrice('urun-kodu-123', 799.99, 749.99);

// Ürün stok miktarını güncelle
$result = $productService->updateStock('urun-kodu-123', 10);

// Batch işlem sonucunu kontrol et
$result = $productService->checkBatchResult('batch-request-id');
```

### Sipariş Yönetimi (OrderService)

```php
// Sipariş servisine erişim
$orderService = pazarama()->orders();

// Sipariş listesini getir
$orders = $orderService->list([
    'startDate' => '2023-01-01',
    'endDate' => '2023-01-31'
]);

// Sipariş detayını getir
$order = $orderService->get('siparis-numarasi');

// Sipariş durumunu güncelle
$result = $orderService->updateStatus('siparis-numarasi', 12); // 12: Siparişiniz Hazırlanıyor

// Kargo bilgisini güncelle
$result = $orderService->updateShipment(
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

// İade listesini getir
$returns = $orderService->getReturns([
    'startDate' => '2023-01-01',
    'endDate' => '2023-01-31'
]);

// İade durumunu güncelle
$result = $orderService->updateRefundStatus('iade-id', 2); // 2: Onay

// Tekil iade bilgisini getir
$return = $orderService->getReturn('iade-id');

// İade alt öğelerini (ürünlerini) getir
$returnItems = $orderService->getReturnItems('iade-id');
```

### Kategori Yönetimi (CategoryService)

```php
// Kategori servisine erişim
$categoryService = pazarama()->categories();

// Kategori ağacını getir
$categories = $categoryService->list();

// Kategori özelliklerini getir
$categoryAttributes = $categoryService->getAttributes('kategori-id');

// Kategori için gerekli özellikleri getir
$requiredAttributes = $categoryService->getRequiredAttributes('kategori-id');
```

### Marka Yönetimi (BrandService)

```php
// Marka servisine erişim
$brandService = pazarama()->brands();

// Marka listesini getir
$brands = $brandService->list(1, 100);

// Markayı adına göre getir
$brand = $brandService->getByName('Marka Adı');
```

### Kargo ve Teslimat Yönetimi (ShippingService)

```php
// Kargo servisine erişim
$shippingService = pazarama()->shipping();

// Kargo firmaları listesini getir
$companies = $shippingService->getCompanies();

// Teslimat sürelerini getir
$deliveryTimes = $shippingService->getDeliveryTimes();
```

### İade Yönetimi (ReturnService)

```php
// İade servisine erişim
$returnService = pazarama()->returns();

// İade listesini getir
$returns = $returnService->list([
    'startDate' => '2023-01-01',
    'endDate' => '2023-01-31'
]);

// İade detayını getir
$return = $returnService->get('iade-id');

// İade onayı ver
$result = $returnService->approve('iade-id', 'onay-notu');

// İade reddet
$result = $returnService->reject('iade-id', 'red-nedeni');
```

### Toplu İşlemler (BulkOperationService)

```php
// Toplu işlem servisine erişim
$bulkService = pazarama()->bulkOperations();

// Toplu fiyat güncelleme
$updates = [
    ['code' => 'urun-kodu-1', 'listPrice' => 150.99, 'salePrice' => 125.99],
    ['code' => 'urun-kodu-2', 'listPrice' => 200.99, 'salePrice' => 185.99],
];
$result = $bulkService->updatePrices($updates);

// Toplu stok güncelleme
$stockUpdates = [
    ['code' => 'urun-kodu-1', 'stockCount' => 15],
    ['code' => 'urun-kodu-2', 'stockCount' => 25],
];
$result = $bulkService->updateStocks($stockUpdates);

// Toplu işlem durumu sorgulama
$status = $bulkService->checkStatus('batch-id');
```

## Hata Yönetimi

Pazarama API istekleri sırasında oluşabilecek hataları yakalamak için `try-catch` blokları kullanabilirsiniz:

```php
use PazaramaApi\PazaramaSpApi\Exceptions\PazaramaApiException;

try {
    $products = pazarama()->products()->list();
} catch (PazaramaApiException $e) {
    // Hata mesajını al
    $message = $e->getMessage();
    
    // HTTP durum kodunu al
    $statusCode = $e->getCode();
    
    // API hata kodunu al
    $apiCode = $e->getApiCode();
    
    // API yanıtını al (detaylı hata bilgisi)
    $response = $e->getResponse();
    
    // Hata kaydı oluştur veya kullanıcıya bilgi ver
    Log::error('Pazarama API Hatası', [
        'message' => $message,
        'code' => $statusCode,
        'api_code' => $apiCode,
        'response' => $response
    ]);
}
```

## Test Edilebilirlik

Paket test edilebilirliği destekler. Gerçek API çağrıları yapmadan birim testleri yazabilmeniz için mock yapıları içerir.

```php
use PazaramaApi\PazaramaSpApi\PazaramaSpApi;
use PazaramaApi\PazaramaSpApi\Services\ProductService;

// Mock PazaramaSpApi nesnesi oluşturma
$mockedApi = Mockery::mock(PazaramaSpApi::class);

// Mock ProductService nesnesi oluşturma
$mockedProductService = Mockery::mock(ProductService::class);

// Beklenen davranışı tanımlama
$mockedProductService->shouldReceive('list')
    ->once()
    ->with(['Approved' => true])
    ->andReturn(['data' => [/* ürün verileri */]]);

$mockedApi->shouldReceive('products')
    ->once()
    ->andReturn($mockedProductService);

// Mock'lanmış servisi uygulama konteynerine kaydetme
$this->app->instance('pazarama', $mockedApi);

// Test kodu
$result = app('pazarama')->products()->list(['Approved' => true]);

// Assertion
$this->assertIsArray($result);
$this->assertArrayHasKey('data', $result);
```

## Lisans

Bu paket MIT lisansı altında lisanslanmıştır. Daha fazla bilgi için [LICENSE](LICENSE) dosyasına göz atabilirsiniz. 