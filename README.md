# Laravel Approval Package

Laravel için kapsamlı onay sistemi paketi. Modellerinizin onay durumlarını yönetmek için güçlü ve esnek bir çözüm sunar.

## Özellikler

- ✅ **Kolay Entegrasyon**: Sadece trait ekleyerek modellerinizi onay sistemi ile entegre edin
- ✅ **Esnek Yapılandırma**: İki farklı mod (insert/upsert) ve özelleştirilebilir ayarlar
- ✅ **Global Scope**: Otomatik olarak sadece onaylı kayıtları gösterin
- ✅ **Olay Sistemi**: Durum değişikliklerinde olayları dinleyin
- ✅ **Facade Desteği**: Kolay kullanım için Facade API'si
- ✅ **Artisan Komutları**: CLI üzerinden istatistikleri görüntüleyin
- ✅ **TDD Yaklaşımı**: %100 test kapsamı ile güvenilir kod

## Kurulum

### Composer ile Kurulum

```bash
composer require fzengin19/laravel-approval
```

### Service Provider'ı Yayınlama

```bash
php artisan vendor:publish --provider="LaravelApproval\LaravelApprovalServiceProvider"
```

### Migration'ları Çalıştırma

```bash
php artisan migrate
```

## Hızlı Başlangıç

### 1. Model'e Trait Ekleme

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\HasApprovals;

class Post extends Model
{
    use HasApprovals;

    protected $fillable = [
        'title',
        'content',
    ];
}
```

### 2. Temel Kullanım

```php
// Post oluştur
$post = Post::create([
    'title' => 'Yeni Başlık',
    'content' => 'İçerik...',
]);

// Onay durumunu kontrol et
$post->isPending();    // false (henüz onay kaydı yok)
$post->isApproved();   // false
$post->isRejected();   // false

// Beklemede durumuna geçir
$post->setPending(1);  // 1 = onaylayan kullanıcı ID'si

// Onayla
$post->approve(1);

// Reddet
$post->reject(1, 'Geçersiz içerik', 'İçerik kurallara uymuyor');
```

## Yapılandırma

### Config Dosyası

`config/approvals.php` dosyasını yayınlayarak ayarları özelleştirebilirsiniz:

```php
return [
    'default' => [
        'mode' => 'insert',                    // 'insert' veya 'upsert'
        'auto_pending_on_create' => false,     // Model oluşturulduğunda otomatik pending
        'show_only_approved_by_default' => false, // Global scope aktif mi?
        'auto_scope' => true,                  // Global scope'u otomatik ekle
        'events' => true,                      // Olayları tetikle
    ],
    
    'models' => [
        // Model özel ayarları
        'App\Models\Post' => [
            'mode' => 'upsert',
            'auto_pending_on_create' => true,
        ],
    ],
];
```

### Modlar

#### Insert Modu
Her durum değişikliğinde yeni bir onay kaydı oluşturur. Geçmiş takibi için idealdir.

```php
config(['approvals.default.mode' => 'insert']);

$post->setPending(1);  // Yeni kayıt
$post->approve(1);     // Yeni kayıt
$post->reject(1);      // Yeni kayıt
// Toplam: 3 kayıt
```

#### Upsert Modu
Mevcut onay kaydını günceller. Tek kayıt tutmak için idealdir.

```php
config(['approvals.default.mode' => 'upsert']);

$post->setPending(1);  // Yeni kayıt
$post->approve(1);     // Mevcut kaydı güncelle
$post->reject(1);      // Mevcut kaydı güncelle
// Toplam: 1 kayıt
```

## Gelişmiş Kullanım

### Sorgu Scope'ları

```php
// Sadece onaylı post'ları getir
$approvedPosts = Post::approved()->get();

// Sadece beklemede post'ları getir
$pendingPosts = Post::pending()->get();

// Sadece reddedilmiş post'ları getir
$rejectedPosts = Post::rejected()->get();

// Onay durumu ile birlikte getir
$posts = Post::withApprovalStatus()->get();
```

### Global Scope

Global scope aktif olduğunda, sadece onaylı kayıtlar görünür:

```php
// Sadece onaylı post'lar
$posts = Post::all();

// Tüm post'ları görmek için
$allPosts = Post::withUnapproved()->get();
```

### Otomatik Pending

Model oluşturulduğunda otomatik olarak pending durumuna geçirmek için:

```php
config(['approvals.default.auto_pending_on_create' => true]);

$post = Post::create(['title' => 'Test']);
// Otomatik olarak pending durumunda olacak
```

### Olaylar

Durum değişikliklerinde olayları dinleyebilirsiniz:

```php
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;

Event::listen(ModelApproved::class, function ($event) {
    $model = $event->model;
    $approval = $event->approval;
    
    // Onaylandığında yapılacak işlemler
    Mail::to($model->user)->send(new PostApprovedMail($model));
});

Event::listen(ModelRejected::class, function ($event) {
    // Reddedildiğinde yapılacak işlemler
});
```

## Facade Kullanımı

```php
use LaravelApproval\Facades\Approval;

// Model onaylama
Approval::approve($post, 1);

// Model reddetme
Approval::reject($post, 1, 'Geçersiz içerik', 'Açıklama');

// Beklemede durumuna geçirme
Approval::setPending($post, 1);

// İstatistikleri alma
$stats = Approval::getStatistics(Post::class);
// [
//     'total' => 10,
//     'approved' => 7,
//     'pending' => 2,
//     'rejected' => 1,
//     'approved_percentage' => 70.0,
//     'pending_percentage' => 20.0,
//     'rejected_percentage' => 10.0,
// ]
```

## Artisan Komutları

### İstatistikleri Görüntüleme

```bash
# Tüm modeller için istatistikler
php artisan approval:status

# Belirli bir model için istatistikler
php artisan approval:status --model="App\Models\Post"
```

## Test

```bash
# Tüm testleri çalıştır
vendor/bin/pest

# Belirli test dosyasını çalıştır
vendor/bin/pest tests/Models/ApprovalTest.php
```

## Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'Add some amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## Lisans

Bu paket MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## Destek

Sorularınız için [GitHub Issues](https://github.com/fzengin19/laravel-approval/issues) sayfasını kullanabilirsiniz.
