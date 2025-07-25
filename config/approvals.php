<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains the default settings for the package.
    |
    */
    'default' => [
        /*
        |--------------------------------------------------------------------------
        | Mode
        |--------------------------------------------------------------------------
        |
        | Determines how approval records are created:
        | - 'insert': Creates a new record for each status change
        | - 'upsert': Updates existing record or creates new record
        |
        */
        'mode' => env('APPROVAL_MODE', 'insert'),

        /*
        |--------------------------------------------------------------------------
        | Auto Pending on Create
        |--------------------------------------------------------------------------
        |
        | Whether to automatically set to pending status when model is created.
        |
        */
        'auto_pending_on_create' => env('APPROVAL_AUTO_PENDING_ON_CREATE', false),

        /*
        |--------------------------------------------------------------------------
        | Show Only Approved by Default
        |--------------------------------------------------------------------------
        |
        | Whether global scope shows only approved records by default.
        |
        */
        'show_only_approved_by_default' => env('APPROVAL_SHOW_ONLY_APPROVED_BY_DEFAULT', false),

        /*
        |--------------------------------------------------------------------------
        | Auto Scope
        |--------------------------------------------------------------------------
        |
        | Whether to automatically add global scope to models using Approvable trait.
        |
        */
        'auto_scope' => env('APPROVAL_AUTO_SCOPE', true),

        /*
        |--------------------------------------------------------------------------
        | Events
        |--------------------------------------------------------------------------
        |
        | Whether to trigger events on status changes.
        |
        */
        'events' => env('APPROVAL_EVENTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Models Configuration
    |--------------------------------------------------------------------------
    |
    | Custom settings for each model.
    |
    */
    'models' => [
        // Example: 'App\Models\Post' => [
        //     'mode' => 'upsert',
        //     'auto_pending_on_create' => true,
        //     'show_only_approved_by_default' => true,
        //     'auto_scope' => true,
        //     'events' => true,
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rejection Reasons
    |--------------------------------------------------------------------------
    |
    | Predefined rejection reasons
    |
    */
    'rejection_reasons' => [
        'inappropriate_content' => 'Inappropriate Content',
        'spam' => 'Spam',
        'duplicate' => 'Duplicate',
        'incomplete' => 'Incomplete',
        'other' => 'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Features Configuration
    |--------------------------------------------------------------------------
    |
    | Advanced features like notifications and auto-setup
    |
    */
    'features' => [
        'notifications' => [
            /*
            |--------------------------------------------------------------------------
            | Notifications Enabled
            |--------------------------------------------------------------------------
            |
            | Whether to enable notification system for approval events.
            |
            */
            'enabled' => env('APPROVAL_NOTIFICATIONS_ENABLED', false),

            /*
            |--------------------------------------------------------------------------
            | Mail Notifications
            |--------------------------------------------------------------------------
            |
            | Email notification settings
            |
            */
            'mail' => [
                'enabled' => env('APPROVAL_MAIL_ENABLED', false),
                'from_address' => env('APPROVAL_MAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
                'from_name' => env('APPROVAL_MAIL_FROM_NAME', env('MAIL_FROM_NAME')),
                'template' => env('APPROVAL_MAIL_TEMPLATE', null), // Custom mail template path
            ],

            /*
            |--------------------------------------------------------------------------
            | Database Notifications
            |--------------------------------------------------------------------------
            |
            | Database notification settings
            |
            */
            'database' => [
                'enabled' => env('APPROVAL_DB_NOTIFICATIONS_ENABLED', false),
            ],

            /*
            |--------------------------------------------------------------------------
            | Event Notifications
            |--------------------------------------------------------------------------
            |
            | Which events should trigger notifications
            |
            */
            'events' => [
                'approved' => env('APPROVAL_NOTIFY_APPROVED', true),
                'rejected' => env('APPROVAL_NOTIFY_REJECTED', true),
                'pending' => env('APPROVAL_NOTIFY_PENDING', false),
            ],

            /*
            |--------------------------------------------------------------------------
            | Notification Recipients
            |--------------------------------------------------------------------------
            |
            | Who should receive notifications
            |
            */
            'recipients' => [
                'admin_email' => env('APPROVAL_ADMIN_EMAIL'),
                'notify_model_owner' => env('APPROVAL_NOTIFY_MODEL_OWNER', true),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Auto Setup
        |--------------------------------------------------------------------------
        |
        | Automatic setup features
        |
        */
        'auto_setup' => [
            'migrations' => env('APPROVAL_AUTO_MIGRATIONS', false),
            'notifications_table' => env('APPROVAL_AUTO_NOTIFICATIONS_TABLE', false),
        ],
    ],
];
