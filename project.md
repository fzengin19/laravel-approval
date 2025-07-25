# Laravel Onay Paketi - Detaylı Geliştirme ve Test Rehberi

Bu belge, `laravel-approvals` paketinin adım adım, test odaklı (TDD) bir yaklaşımla geliştirilmesi için bir yol haritası sunar. Her geliştirme adımı, ilgili testlerin yazılıp geçirilmesiyle doğrulanır ve ancak ondan sonra bir sonraki adıma geçilir.

---

### **Kilometre Taşı 1: Temel Altyapı ve Çekirdek Modeller**

**Hedef:** Veritabanı şemasını, yapılandırmayı ve temel Eloquent modellerini oluşturmak. Bu aşama sonunda, paketin temel veri yapıları hazır olacak.

* `[x]` **Geliştirme Adımı 1.1: Migration Dosyasını Oluştur**
    * `/database/migrations` klasörü içinde, proje tanımında belirtilen `approvals` tablosunun şemasını içeren migration dosyasını oluştur.
    * **Odak:** `id`, `approvable_type`, `approvable_id`, `status`, `rejection_reason`, `rejection_comment`, `caused_by`, `responded_at`, `timestamps` sütunları ve indeksler doğru tanımlanmalı.

* `[x]` **Geliştirme Adımı 1.2: Yapılandırma Dosyasını Oluştur**
    * `/config` klasörü içinde, proje tanımında belirtilen tüm seçenekleri içeren `approvals.php` dosyasını oluştur.
    * **Odak:** `default` ve `models` anahtarları altında tüm ayarlar (`mode`, `auto_pending_on_create` vb.) eksiksiz ve varsayılan değerleriyle bulunmalı.

* `[x]` **Geliştirme Adımı 1.3: `Approval` Eloquent Modelini Oluştur**
    * `/src/Models` altında `Approval.php` modelini oluştur.
    * `$fillable` veya `$guarded` özelliklerini tanımla.
    * `approvable()` adında `morphTo` ilişkisini tanımla.

* `[x]` **Test Adımı 1.4: Çekirdek Model Testleri**
    * **Test Senaryosu:** `Approval` modelinin temel özelliklerini doğrula.
    * **Adımlar:**
        * Bir `Approval` nesnesi oluşturulabildiğini test et (`Approval::create([...])`).
        * `approvable()` ilişkisinin doğru çalıştığını test et. Bir `Post` ve ona bağlı bir `Approval` kaydı oluşturup `$approval->approvable` ilişkisinin doğru `Post` nesnesini döndürdüğünü doğrula.

* `[x]` **Geliştirme Adımı 1.5: `HasApprovals` Trait'i ve Temel İlişkileri Oluştur**
    * `/src/Traits` altında `HasApprovals.php` dosyasını oluştur.
    * Trait içinde `approvals()` (`morphMany`) ve `latestApproval()` (`morphOne` ve `latestOfMany`) ilişkilerini tanımla.

* `[x]` **Test Adımı 1.6: Trait İlişki Testleri**
    * **Test Senaryosu:** `HasApprovals` trait'ini kullanan bir modelin ilişkilerinin doğru çalıştığını doğrula.
    * **Adımlar:**
        * Testler için `HasApprovals` trait'ini kullanan geçici bir `Post` modeli oluştur.
        * Bir `Post` ve ona bağlı 3 farklı `Approval` kaydı oluştur.
        * `$post->approvals()->count()` metodunun `3` döndürdüğünü doğrula.
        * `$post->latestApproval` ilişkisinin, bu 3 kayıttan en son oluşturulanı (ID'si en büyük olanı) döndürdüğünü doğrula.

---

### **Kilometre Taşı 2: Temel Durum Yönetimi Mantığı**

**Hedef:** Modelin onay durumunu sorgulayan ve değiştiren temel metotları yazmak ve test etmek.

* `[x]` **Geliştirme Adımı 2.1: Durum Kontrol Metotlarını Ekle**
    * `HasApprovals` trait'ine `isApproved()`, `isPending()`, `isRejected()` ve `getApprovalStatus()` metotlarını ekle. Bu metotlar `latestApproval` ilişkisinden gelen veriyi kullanmalıdır.
    * **Kenar Durum:** Eğer bir modelin hiç onay kaydı yoksa (`latestApproval` null ise), `is...` metotları `false` dönmeli, `getApprovalStatus` ise `null` dönmelidir.

* `[x]` **Test Adımı 2.2: Durum Kontrol Metotlarını Test Et**
    * **Test Senaryosu:** Bir modelin durumunu farklı senaryolarda doğru raporladığını doğrula.
    * **Adımlar:**
        * Bir modele `approved` durumunda bir kayıt ekle. `isApproved()`'un `true`, diğerlerinin `false` döndüğünü test et.
        * Aynı testi `pending` ve `rejected` durumları için tekrarla.
        * Hiç onay kaydı olmayan bir model için tüm `is...` metotlarının `false` döndüğünü doğrula.

* `[x]` **Geliştirme Adımı 2.3: `setPending()` Metodunu Yaz**
    * `HasApprovals` trait'ine `setPending` metodunu ekle.
    * Bu metot, `config('approvals.default.mode')` ayarını kontrol etmeli.
    * `insert` modunda yeni bir kayıt eklemeli, `upsert` modunda ise `updateOrCreate` kullanmalıdır.
    * `caused_by` ve `responded_at` alanlarını doğru şekilde doldurmalıdır.

* `[x]` **Test Adımı 2.4: `setPending()` Metodunu Test Et**
    * **Test Senaryosu:** Modelin durumunu 'pending' olarak ayarlamanın her iki modda da doğru çalıştığını doğrula.
    * **Adımlar:**
        * **Insert Modu:** Config'i 'insert' olarak ayarla. `$post->setPending()` çağrıldığında `approvals` tablosunda yeni bir satır oluştuğunu doğrula. Tekrar çağrıldığında bir satır daha oluştuğunu doğrula.
        * **Upsert Modu:** Config'i 'upsert' olarak ayarla. `$post->setPending()` çağrıldığında yeni bir satır oluştuğunu, tekrar çağrıldığında ise mevcut satırın güncellendiğini ve satır sayısının artmadığını doğrula.
        * Her iki modda da `status`, `caused_by` ve `responded_at` verilerinin doğru kaydedildiğini doğrula.

* `[x]` **Geliştirme Adımı 2.5: `approve()` ve `reject()` Metotlarını Yaz**
    * `setPending` metodundaki mantığı temel alarak `approve` ve `reject` metotlarını oluştur.
    * `reject` metodu, `rejection_reason` ve `rejection_comment` alanlarını da doldurmalıdır.
    * `causedBy` argümanının `null` olması durumunda `auth()->id()`'yi kullanma mantığını ekle.

* `[x]` **Test Adımı 2.6: `approve()` ve `reject()` Metotlarını Test Et**
    * **Test Senaryosu:** Onaylama ve reddetme işlemlerinin tüm detaylarıyla doğru çalıştığını doğrula.
    * **Adımlar:**
        * `approve` metodunu her iki modda (`insert`/`upsert`) test et.
        * `reject` metodunu her iki modda test et.
        * `reject` metoduna verilen `reason` ve `comment`'in veritabanına doğru yazıldığını doğrula.
        * `causedBy` parametresine kullanıcı ID'si, kullanıcı nesnesi ve `null` (oturum açmış kullanıcı) verilmesi durumlarını ayrı ayrı test et.

---

### **Kilometre Taşı 3: Gelişmiş Sorgulama ve Otomasyon**

**Hedef:** Paketi, Global Scope'lar ve otomatik eylemlerle daha entegre ve "akıllı" hale getirmek.

* `[x]` **Geliştirme Adımı 3.1: Yerel Sorgu Scope'larını Ekle**
    * `HasApprovals` trait'ine `scopeApproved()`, `scopePending()`, `scopeRejected()` ve `scopeWithApprovalStatus()` metotlarını ekle.

* `[x]` **Test Adımı 3.2: Yerel Sorgu Scope'larını Test Et**
    * **Test Senaryosu:** Scope'ların veritabanı sorgularını doğru şekilde filtrelediğini doğrula.
    * **Adımlar:**
        * Farklı durumlarda birden çok `Post` oluştur (3 onaylı, 2 beklemede, 1 reddedilmiş).
        * `Post::approved()->count()`'un `3` döndürdüğünü doğrula.
        * Aynı testi `pending` ve `rejected` scope'ları için yap.

* `[x]` **Geliştirme Adımı 3.3: `ApprovableScope` Global Scope'unu Oluştur**
    * `ApprovableScope.php` sınıfını oluştur ve `apply` metodunu yaz.
    * Bu metot, `show_only_approved_by_default` ayarını kontrol edip sorguyu buna göre değiştirmelidir.
    * `HasApprovals` trait'inin `booted` metodunda, `auto_scope` ayarı `true` ise bu scope'u otomatik olarak modele ekle.
    * `scopeWithUnapproved()` metodunu ekleyerek bu global scope'u devre dışı bırakma imkanı sağla.

* `[x]` **Test Adımı 3.4: Global Scope'u Test Et**
    * **Test Senaryosu:** Global scope'un yapılandırmaya göre otomatik olarak çalıştığını ve devre dışı bırakılabildiğini doğrula.
    * **Adımlar:**
        * `show_only_approved_by_default` ayarını `true` yap. `Post::count()`'un sadece onaylı post'ların sayısını verdiğini doğrula.
        * Aynı durumda `Post::withUnapproved()->count()`'un tüm post'ların sayısını verdiğini doğrula.
        * `show_only_approved_by_default` ayarını `false` yap. `Post::count()`'un tüm post'ların sayısını verdiğini doğrula.

* `[x]` **Geliştirme Adımı 3.5: `auto_pending_on_create` Mantığını Uygula**
    * Bir `ApprovalObserver` oluştur veya Trait'in `booted` metodunu kullanarak modelin `created` olayını dinle.
    * `auto_pending_on_create` ayarı `true` ise, model oluşturulduğunda otomatik olarak `setPending()` metodunu çağır.

* `[x]` **Test Adımı 3.6: Otomatik `Pending` Oluşturmayı Test Et**
    * **Test Senaryosu:** `auto_pending_on_create` ayarının beklendiği gibi çalıştığını doğrula.
    * **Adımlar:**
        * Config'de ayarı `true` yap. `Post::create([...])` ile yeni bir post oluştur. `approvals` tablosunda bu post için `pending` durumunda bir kayıt oluştuğunu doğrula.
        * Config'de ayarı `false` yap. Yeni bir post oluştur. `approvals` tablosunda bu post için hiçbir kayıt oluşmadığını doğrula.

---

### **Kilometre Taşı 4: Genişletilebilirlik: Facade, Olaylar ve CLI**

**Hedef:** Paketi, diğer sistemlerle entegrasyonu kolaylaştıran Facade, olaylar ve CLI komutu ile tamamlamak.

* `[x]` **Geliştirme Adımı 4.1: Olay Sınıflarını Oluştur ve Tetikle**
    * `ModelApproved`, `ModelRejected`, `ModelPending` olay sınıflarını oluştur.
    * `approve`, `reject`, `setPending` metotlarının içine, `events` ayarı `true` ise ilgili olayı tetikleyen kodu ekle.

* `[x]` **Test Adımı 4.2: Olayların Tetiklenmesini Test Et**
    * **Test Senaryosu:** Durum değişikliklerinde olayların doğru verilerle tetiklendiğini doğrula.
    * **Adımlar:**
        * `Event::fake()` kullanarak olayları dinle.
        * Bir postu onayla. `Event::assertDispatched(ModelApproved::class, ...)` ile olayın doğru post ve onay kaydı ile tetiklendiğini doğrula.
        * Aynı testi `reject` ve `setPending` için de yap.
        * `events` ayarını `false` yapıp olayların tetiklenmediğini de test et.

* `[x]` **Geliştirme Adımı 4.3: `Approval` Facade'ını ve Servisini Oluştur**
    * `ApprovalService` sınıfını oluştur ve istatistiksel metotları (`getStatistics` vb.) yaz.
    * `Approval` Facade'ını ve `ServiceProvider` bağlantılarını yap.

* `[x]` **Test Adımı 4.4: Facade Metotlarını Test Et**
    * **Test Senaryosu:** Facade'ın tüm metotlarının doğru sonuçlar ürettiğini doğrula.
    * **Adımlar:**
        * `Approval::approve($post)` gibi temel metotları test et.
        * `Approval::getStatistics(Post::class)` metodunun doğru sayıları (`approved`, `pending` vb.) içeren bir dizi döndürdüğünü test et.

* `[x]` **Geliştirme Adımı 4.5: Artisan Komutunu Oluştur**
    * `approval:status` Artisan komut sınıfını oluştur.
    * `handle` metodu içinde, Facade'ı kullanarak istatistikleri çek ve console table ile ekrana yazdır.

* `[x]` **Test Adımı 4.6: Artisan Komutunu Test Et**
    * **Test Senaryosu:** Komutun doğru çıktıyı ürettiğini doğrula.
    * **Adımlar:**
        * `$this->artisan('approval:status')` komutunu çalıştır. `expectsOutputToContain` ile genel tablonun başlıklarının çıktıda olduğunu doğrula.
        * `$this->artisan('approval:status --model="App\Models\Post"')` komutunu çalıştır ve çıktının sadece `Post` modeli için doğru verileri içerdiğini doğrula.

---

### **Kilometre Taşı 5: Yayınlama ve Dokümantasyon**

**Hedef:** Paketi kamuya açık hale getirmek için gerekli son dokunuşları yapmak.

* `[x]` **Geliştirme Adımı 5.1: `README.md` Dosyasını Yaz**
    * Kurulum, yapılandırma ve tüm özelliklerin kullanımı için detaylı ve anlaşılır bir doküman oluştur. Tüm kod örneklerinin çalıştığından emin ol.

* `[x]` **Geliştirme Adımı 5.2: Kod İçi Yorumları ve Temizliği Yap**
    * Tüm public metotların ve karmaşık mantıkların üzerine ne işe yaradıklarını açıklayan DocBlock'lar ekle.
    * Kod formatını PSR-12 standardına göre düzenle.

* `[x]` **Test Adımı 5.3: Son Gözden Geçirme ve Sürüm Etiketleme**
    * Tüm testlerin geçtiğinden emin ol (`vendor/bin/pest` veya `phpunit`).
    * `composer.json` dosyasında sürüm numarasını `v1.0.0` olarak ayarla.
    * Git repozitoryumunda ilk sürümü etiketle ve yayınla.