<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which models should use the approval system and how they
    | should handle approvals (column-based or pivot table).
    |
    */
    'models' => [
        /*
        |--------------------------------------------------------------------------
        | Default Configuration
        |--------------------------------------------------------------------------
        |
        | Default configuration for models that don't have specific settings.
        |
        */
        'default' => [
            'column' => 'approved_at',           // Column name for approval status
            'fallback_to_pivot' => true,         // Use pivot table if column doesn't exist
            'auto_scope' => true,                // Automatically add global scope
            'events' => true,                    // Fire approval events
            'show_only_approved_by_default' => false, // Show only approved by default
        ],

        /*
        |--------------------------------------------------------------------------
        | Example Model Configurations
        |--------------------------------------------------------------------------
        |
        | You can configure specific models here. The key should be the full
        | class name of the model.
        |
        */
        // 'App\Models\Job' => [
        //     'column' => 'approved_at',           // Use existing column
        //     'fallback_to_pivot' => true,         // Fallback to pivot if column doesn't exist
        //     'auto_scope' => true,                // Add global scope
        //     'events' => true,                    // Fire events
        //     'show_only_approved_by_default' => false, // Show all by default
        // ],
        // 'App\Models\Company' => [
        //     'column' => null,                    // No column, use pivot table
        //     'fallback_to_pivot' => true,         // Use pivot table
        //     'auto_scope' => true,                // Add global scope
        //     'events' => true,                    // Fire events
        //     'show_only_approved_by_default' => true, // Show only approved by default
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pivot Table Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the pivot table used when models don't have
    | approval columns.
    |
    */
    'pivot_table' => 'approvals',

    /*
    |--------------------------------------------------------------------------
    | Approval Statuses
    |--------------------------------------------------------------------------
    |
    | Available approval statuses.
    |
    */
    'statuses' => [
        'pending',
        'approved',
        'rejected',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rejection Reasons
    |--------------------------------------------------------------------------
    |
    | Predefined rejection reasons that can be used when rejecting models.
    |
    */
    'rejection_reasons' => [
        'inappropriate_content',
        'spam',
        'duplicate',
        'incomplete',
        'violates_policy',
        'other',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model to use for tracking who approved/rejected models.
    |
    */
    'user_model' => config('auth.providers.users.model', 'App\Models\User'),

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Configure whether to send notifications for approval events.
    |
    */
    'notifications' => [
        'enabled' => true,
        'channels' => [
            'mail',
            'database',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for approval statuses to improve performance.
    |
    */
    'cache' => [
        'enabled' => false,
        'ttl' => 3600, // 1 hour
        'prefix' => 'approval_status_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for approval actions.
    |
    */
    'rate_limiting' => [
        'enabled' => false,
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Discovery Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automatic discovery of models using the HasApproval trait.
    |
    */
    'auto_discovery' => [
        'enabled' => true,           // Enable automatic model discovery
        'paths' => [                 // Paths to scan for models
            'App\\Models',
        ],
        'cache_ttl' => 3600,         // Cache TTL for discovered models
    ],
];
