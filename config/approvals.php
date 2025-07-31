<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | When an approval action is performed without specifying a user, the
    | currently authenticated user is used. This model will be used to
    | retrieve the user from the database.
    |
    */

    'user_model' => config('auth.providers.users.model'),

    /*
    |--------------------------------------------------------------------------
    | Default Approval Settings
    |--------------------------------------------------------------------------
    |
    | This option controls the default approval settings for all models.
    | You can override these settings for specific models below.
    |
    */
    'default' => [

        // Determines the behavior for models that have no approval record.
        // - null (default): The model has no specific status. It won't appear
        //   in queries for approved, pending, or rejected models. This is the
        //   safest and recommended default.
        // - 'approved': Treat as approved. The model will appear in `approved()`
        //   queries. This mimics the old behavior for backward compatibility.
        // - 'pending': Treat as pending.
        // - 'rejected': Treat as rejected.
        'default_status_for_unaudited' => null,

        // The mode for storing approval records.
        // - 'insert': Creates a new record for each approval action (recommended
        //   for audit trails).
        // - 'upsert': Updates the existing record for the model. Keeps only the
        //   latest status.
        'mode' => 'insert',
        'auto_pending_on_create' => false,     // Auto pending when model is created
        'show_only_approved_by_default' => false, // Is global scope active?
        'auto_scope' => true,                  // Automatically add global scope
        
        // Event Settings
        'events_enabled' => true,              // Enable event system
        'events_logging' => true,              // Log events
        'events_webhooks_enabled' => false,    // Enable webhooks
        'events_webhooks_endpoints' => [],     // Webhook endpoints
        'events_custom_actions' => [           // Custom event actions
            'model_approved' => [
                // Example: function($event) { /* custom logic */ }
            ],
            'model_rejected' => [
                // Example: function($event) { /* custom logic */ }
            ],
            'model_pending' => [
                // Example: function($event) { /* custom logic */ }
            ],
            'model_approving' => [
                // Example: function($event) { /* custom logic */ }
            ],
            'model_rejecting' => [
                // Example: function($event) { /* custom logic */ }
            ],
            'model_setting_pending' => [
                // Example: function($event) { /* custom logic */ }
            ],
        ],
        
        // Rejection Settings
        'allow_custom_reasons' => false,       // Allow custom rejection reasons
        'rejection_reasons' => [
            'inappropriate_content' => 'Inappropriate Content',
            'spam' => 'Spam',
            'duplicate' => 'Duplicate',
            'incomplete' => 'Incomplete',
            'other' => 'Other',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Models Configuration
    |--------------------------------------------------------------------------
    |
    | Custom settings for each model. Override default settings for specific models.
    | Only specify the settings you want to override.
    |
    */
    'models' => [
        // Example: 'App\Models\Post' => [
        //     'mode' => 'upsert',
        //     'auto_pending_on_create' => true,
        //     'show_only_approved_by_default' => true,
        //     'default_status_for_unaudited' => 'approved', // Example of overriding
        //     'events_enabled' => false,
        //     'allow_custom_reasons' => true,
        //     'rejection_reasons' => [
        //         'inappropriate_content' => 'Inappropriate Content',
        //         'spam' => 'Spam',
        //         'duplicate' => 'Duplicate',
        //         'incomplete' => 'Incomplete',
        //         'copyright_violation' => 'Copyright Violation',
        //         'other' => 'Other',
        //     ],
        // ],
    ],
];
