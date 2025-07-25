<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    |
    | Bu bölüm, paketin varsayılan ayarlarını içerir.
    |
    */
    'default' => [
        /*
        |--------------------------------------------------------------------------
        | Mode
        |--------------------------------------------------------------------------
        |
        | Onay kayıtlarının nasıl oluşturulacağını belirler:
        | - 'insert': Her durum değişikliğinde yeni bir kayıt oluşturur
        | - 'upsert': Mevcut kaydı günceller veya yeni kayıt oluşturur
        |
        */
        'mode' => env('APPROVAL_MODE', 'insert'),

        /*
        |--------------------------------------------------------------------------
        | Auto Pending on Create
        |--------------------------------------------------------------------------
        |
        | Model oluşturulduğunda otomatik olarak pending durumuna geçirilip geçirilmeyeceği.
        |
        */
        'auto_pending_on_create' => env('APPROVAL_AUTO_PENDING_ON_CREATE', false),

        /*
        |--------------------------------------------------------------------------
        | Show Only Approved by Default
        |--------------------------------------------------------------------------
        |
        | Global scope'un varsayılan olarak sadece onaylı kayıtları göstermesi.
        |
        */
        'show_only_approved_by_default' => env('APPROVAL_SHOW_ONLY_APPROVED_BY_DEFAULT', false),

        /*
        |--------------------------------------------------------------------------
        | Auto Scope
        |--------------------------------------------------------------------------
        |
        | HasApprovals trait'ini kullanan modellere otomatik olarak global scope eklenip eklenmeyeceği.
        |
        */
        'auto_scope' => env('APPROVAL_AUTO_SCOPE', true),

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        |
        | Durum değişikliklerinde olayların tetiklenip tetiklenmeyeceği.
        |
        */
        'events' => env('APPROVAL_EVENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Models Configuration
    |--------------------------------------------------------------------------
    |
    | Her model için özel ayarlar.
    |
    */
    'models' => [
        // Örnek: 'App\Models\Post' => [
        //     'mode' => 'upsert',
        //     'auto_pending_on_create' => true,
        //     'show_only_approved_by_default' => true,
        //     'auto_scope' => true,
        //     'events' => true,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Options
    |--------------------------------------------------------------------------
    |
    | Kullanılabilir durum seçenekleri
    |
    */
    'statuses' => [
        'pending' => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rejection Reasons
    |--------------------------------------------------------------------------
    |
    | Önceden tanımlanmış reddetme nedenleri
    |
    */
    'rejection_reasons' => [
        'inappropriate_content' => 'Uygunsuz İçerik',
        'spam' => 'Spam',
        'duplicate' => 'Tekrar',
        'incomplete' => 'Eksik',
        'other' => 'Diğer',
    ],
];
