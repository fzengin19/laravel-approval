[![Latest Version on Packagist](https://img.shields.io/packagist/v/fzengin19/laravel-approval.svg?style=flat-square)](https://packagist.org/packages/fzengin19/laravel-approval)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fzengin19/laravel-approval/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/fzengin19/laravel-approval/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/fzengin19/laravel-approval/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/fzengin19/laravel-approval/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/fzengin19/laravel-approval.svg?style=flat-square)](https://packagist.org/packages/fzengin19/laravel-approval)
[![Code Coverage Status](https://img.shields.io/codecov/c/github/fzengin19/laravel-approval?style=flat-square)](https://codecov.io/gh/fzengin19/laravel-approval)

A comprehensive approval system package for Laravel. Provides a powerful and flexible solution for managing approval statuses of your models. Developed with TDD approach, reliable code with high test coverage.

## ✨ Features

- 🚀 **Easy Integration**: Integrate your models with approval system by just adding a trait.
- ⚙️ **Flexible Configuration**: Two different modes (insert/upsert) and customizable settings.
- 💡 **Configurable Initial State**: Define a default status for models that haven't been through an approval process yet.
- 🧠 **Smart Rejection Handling**: Intelligent categorization of rejection reasons with predefined and custom options.
- 🔍 **Global Scope**: Automatically show only approved records with configurable behavior.
- 🔔 **Comprehensive Event System**: 6 different events (pre/post status changes) using modern PHP 8.1+ features.
- 📊 **Statistics Service**: Built-in statistics calculation with percentages and model-specific data.
- 🎭 **Facade Support**: Static API for easy usage with full IDE support.
- 🖥️ **Artisan Commands**: View statistics via CLI with table formatting.
- 🔧 **Repository Pattern**: Clean data access layer with insert/upsert modes.
- 🛡️ **Validation System**: Input validation with business rule enforcement.
- 🌐 **Webhook Support**: Configurable webhook endpoints for external integrations.
- 🎯 **Model-specific Configuration**: Override settings per model with inheritance.
- 🔄 **Auto Pending**: Automatically set models to pending status on creation.
- 🔗 **Polymorphic Causer**: Approval actions can be caused by any model, not just Users.
- ✨ **Modern PHP**: Utilizes modern PHP features like Enums for type-safe statuses.
- 📈 **Performance Optimized**: Indexed database fields and efficient queries.

## 🚀 Quick Start

### 1. Installation

```bash
composer require fzengin19/laravel-approval
```

### 2. Publish and Migrate

```bash
php artisan vendor:publish --provider="LaravelApproval\LaravelApprovalServiceProvider"
php artisan migrate
```

### 3. Add Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Contracts\ApprovableInterface;
use LaravelApproval\Traits\Approvable;

class Post extends Model implements ApprovableInterface
{
    use HasFactory, Approvable;

    protected $fillable = [
        'title',
        'content',
    ];
}
```

### 4. Start Using

```php
use LaravelApproval\Enums\ApprovalStatus;

// Create a post
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'This is my content...',
]);

// Set to pending
$post->setPending(1); // 1 = user ID

// Approve
$post->approve(1);

// Reject with reason
$post->reject(1, 'spam', 'This is spam content');

// Check status
$post->isApproved(); // true
$post->isPending();  // false
$post->isRejected(); // false
$post->getApprovalStatus(); // Returns ApprovalStatus::APPROVED enum case

// Use query scopes
$approvedPosts = Post::approved()->get();
$pendingPosts = Post::pending()->get();
$rejectedPosts = Post->rejected()->get();

// Get statistics
$stats = Approval::getStatistics(Post::class);

That's it! Your model now has full approval functionality. 🎉
```
## 📦 Installation

### Requirements

- PHP 8.1 or higher
- Laravel 9 or higher

### Install via Composer

```bash
composer require fzengin19/laravel-approval
```

### Publish Configuration

```bash
# Publish migrations and config
php artisan vendor:publish --provider="LaravelApproval\LaravelApprovalServiceProvider"

# Or publish only config
php artisan vendor:publish --tag="laravel-approval-config"
```

### Run Migrations

```bash
php artisan migrate
```

## ⚙️ Configuration

The published config file (`config/approvals.php`) contains comprehensive configuration options. Here are the key sections:

```php
return [
    // The user model that is responsible for approval actions.
    'user_model' => config('auth.providers.users.model'),

    'default' => [
        // Core Settings
        'mode' => 'insert',                    // 'insert' or 'upsert'
        'auto_pending_on_create' => false,     // Auto pending when model is created
        'show_only_approved_by_default' => false, // Is global scope active?
        'auto_scope' => true,                  // Automatically add global scope
        'default_status_for_unaudited' => null, // Default status for models with no approval record. Can be: null, 'pending', 'approved', 'rejected'.

        // Event Settings
        'events_enabled' => true,              // Enable event system
        'events_logging' => true,              // Log events
        'events_webhooks_enabled' => false,    // Enable webhooks
        'events_webhooks_endpoints' => [],     // Webhook endpoints
        'events_custom_actions' => [           // Custom event actions
            'model_approved' => [
                // Example: function($event) { /* custom logic */ }
            ],
            // ... other events
        ],
        
        // Rejection Settings
        'allow_custom_reasons' => false,       // Allow custom rejection reasons
        'rejection_reasons' => [
            'inappropriate_content' => 'Inappropriate Content',
            'spam' => 'Spam',
            'other' => 'Other',
        ],
    ],
    
    'models' => [
        // Model specific settings (override defaults)
        'App\Models\Post' => [
            'mode' => 'upsert',
            'auto_pending_on_create' => true,
        ],
    ],
];
```

**Note:** Approval statuses are managed by the `LaravelApproval\Enums\ApprovalStatus` enum:
- `ApprovalStatus::PENDING`
- `ApprovalStatus::APPROVED`
- `ApprovalStatus::REJECTED`

## 📖 Usage Guide

### Basic Operations

```php
use LaravelApproval\Enums\ApprovalStatus;

// Create a model
$post = Post::create(['title' => 'New Post']);

// Check current status
// By default, a model with no approval record has a `null` status.
$post->isPending();    // false
$post->isApproved();   // false
$post->isRejected();   // false
$post->getApprovalStatus(); // null

// You can change this behavior with `default_status_for_unaudited` config.
// If config is set to 'pending':
// config(['approvals.default.default_status_for_unaudited' => 'pending']);
// $post->isPending(); // true, returns true
// $post->getApprovalStatus(); // ApprovalStatus::PENDING, returns enum case

// Set to pending
$post->setPending(1);  // 1 = approving user ID

// Approve
$post->approve(1);

// Reject with predefined reason
$post->reject(1, 'spam', 'Additional details');

// Reject with custom reason (automatically categorized as 'other' if not in predefined list)
$post->reject(1, 'Custom reason', 'Additional details');

// Get approval history
$allApprovals = $post->approvals; // All approval records (MorphMany)
$latestApproval = $post->latestApproval; // Current approval record (MorphOne)

// Get who caused the approval
$causer = $post->latestApproval->causer; // Returns the User model instance (or other causer model)
```

### Query Scopes

```php
// Get only approved posts
$approvedPosts = Post::approved()->get();

// Get only pending posts
$pendingPosts = Post::pending()->get();

// Get only rejected posts
$rejectedPosts = Post::rejected()->get();

// Get with approval status (eager load latest approval)
$posts = Post::withApprovalStatus()->get();

// Include unapproved posts (when global scope is active)
$allPosts = Post::withUnapproved()->get();

// Check if global scope is active
$posts = Post::all(); // Only approved if global scope is enabled
```

### Modes

#### Insert Mode (Default)
Creates a new approval record for each status change. Ideal for history tracking and audit trails.

Configure in your config/approvals.php:
```php
'default' => [
    'mode' => 'insert',  // Default mode
    // ... other settings
],
```

Usage:
```php
$post->setPending(1);  // New record
$post->approve(1);     // New record
$post->reject(1);      // New record
// Total: 3 records (full history)
```

#### Upsert Mode
Updates existing approval record. Ideal for keeping single record and current status only.

Configure in your config/approvals.php:
```php
'default' => [
    'mode' => 'upsert',  // Or set per model
    // ... other settings
],
```

Usage:
```php
$post->setPending(1);  // New record
$post->approve(1);     // Update existing record
$post->reject(1);      // Update existing record
// Total: 1 record (current status only)
```

**Note:** Mode can be configured per model in the config file.

### Smart Rejection Handling

The reject() method intelligently handles rejection reasons with automatic categorization:

```php
// Using predefined reason
$post->reject(1, 'spam', 'Additional details');
// Result: rejection_reason = 'spam', rejection_comment = 'Additional details'

// Using custom reason (when allowed)
$post->reject(1, 'copyright_violation', 'Image belongs to Getty Images');
// Result: rejection_reason = 'copyright_violation', rejection_comment = 'Image belongs to Getty Images'

// Using custom reason (when not allowed)
$post->reject(1, 'custom_reason', 'Custom rejection reason');
// Result: rejection_reason = 'other', rejection_comment = 'custom_reason - Custom rejection reason'
```

**Validation Rules:**
- reason field: maximum 255 characters (string field limit)
- comment field: no length limit (TEXT field)
- Custom reasons can be enabled/disabled per model

#### Custom Rejection Reasons

Control whether custom rejection reasons are allowed in your config/approvals.php:

```php
'default' => [
    'allow_custom_reasons' => false,  // Set to true to allow globally
    // ... other settings
],

'models' => [
    'App\Models\Post' => [
        'allow_custom_reasons' => true,  // Allow custom reasons for Post model
        // ... other settings
    ],
    'App\Models\Comment' => [
        'allow_custom_reasons' => false, // Only predefined reasons for Comment model
        // ... other settings
    ],
],
```

### Global Scope

When global scope is active, only approved records are visible by default:

```php
// Only approved posts (when global scope is enabled)
$posts = Post::all();

// To see all posts (bypass global scope)
$allPosts = Post::withUnapproved()->get();

// Check if global scope is active for this model
$showOnlyApproved = config('approvals.models.' . Post::class . '.show_only_approved_by_default', 
                          config('approvals.default.show_only_approved_by_default', false));
```

#### Auto Scope Configuration

Control whether the global scope is automatically applied in your config/approvals.php:

```php
'default' => [
    'auto_scope' => true,  // Set to false to disable globally
    // ... other settings
],

'models' => [
    'App\Models\Post' => [
        'auto_scope' => false,  // Disable for specific model
        // ... other settings
    ],
],
```

Or manually apply the scope when needed:

```php
use LaravelApproval\Scopes\ApprovableScope;

$approvedPosts = Post::withGlobalScope('approvable', new ApprovableScope)->get();
```

### Auto Pending

Automatically set to pending status when model is created. Configure in your `config/approvals.php`:

```php
'default' => [
    'auto_pending_on_create' => false,  // Set to true to enable globally
    // ... other settings
],

'models' => [
    'App\Models\Post' => [
        'auto_pending_on_create' => true,  // Enable for specific model
        // ... other settings
    ],
],
```

Usage:
```php
$post = Post::create(['title' => 'Test']);
// Will automatically be in pending status if auto_pending_on_create is enabled
```

### Model-Specific Configuration

Override default settings for specific models in your config/approvals.php. Only specify the settings you want to override:

```php
'models' => [
    'App\Models\Post' => [
        'mode' => 'upsert',                    // This model uses upsert mode
        'auto_pending_on_create' => true,      // Auto pending for this model
        'show_only_approved_by_default' => true, // Global scope active for this model
        'events_enabled' => false,             // No events for this model
        'allow_custom_reasons' => true,        // Allow custom rejection reasons
        'rejection_reasons' => [               // Custom rejection reasons
            'inappropriate_content' => 'Inappropriate Content',
            'spam' => 'Spam',
            'duplicate' => 'Duplicate',
            'incomplete' => 'Incomplete',
            'copyright_violation' => 'Copyright Violation',
            'other' => 'Other',
        ],
    ],
    'App\Models\Comment' => [
        'mode' => 'insert',                    // This model uses insert mode
        'auto_pending_on_create' => false,     // No auto pending for this model
        'events_enabled' => true,              // Events enabled for this model
        'events_logging' => false,             // No logging for this model
        'allow_custom_reasons' => false,       // Only predefined reasons
        'rejection_reasons' => [               // Different rejection reasons
            'spam' => 'Spam',
            'harassment' => 'Harassment',
            'inappropriate' => 'Inappropriate',
            'offensive' => 'Offensive',
            'other' => 'Other',
        ],
    ],
    'App\Models\Product' => [
        'mode' => 'upsert',
        'auto_pending_on_create' => true,
        'events_webhooks_enabled' => true,     // Webhooks enabled for this model
        'events_webhooks_endpoints' => [
            [
                'url' => 'https://api.example.com/webhooks/product-approval',
                'headers' => ['Authorization' => 'Bearer token'],
                'events' => ['model_approved', 'model_rejected'],
            ],
        ],
        'rejection_reasons' => [
            'inappropriate_content' => 'Inappropriate Content',
            'spam' => 'Spam',
            'duplicate' => 'Duplicate',
            'incomplete' => 'Incomplete',
            'pricing_violation' => 'Pricing Violation',
            'category_mismatch' => 'Category Mismatch',
            'other' => 'Other',
        ],
    ],
],
```

## 🔔 Events

Listen to events on status changes. The package provides 6 different events with rich context:

### Available Events

**Pre-events** (Triggered before status change):
- `LaravelApproval\Events\ModelApproving`
- `LaravelApproval\Events\ModelRejecting`
- `LaravelApproval\Events\ModelSettingPending`

**Post-events** (Triggered after status change):
- `LaravelApproval\Events\ModelApproved`
- `LaravelApproval\Events\ModelRejected`
- `LaravelApproval\Events\ModelPending`

### Event Usage

```php
use Illuminate\Support\Facades\Event;
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;

Event::listen(ModelApproved::class, function (ModelApproved $event) {
    // Access event properties directly (they are public readonly)
    $model = $event->model;
    $approval = $event->approval;
    $context = $event->context;
    
    // Actions to take when approved
    \Log::info("Model approved: " . $model->id);
});

Event::listen(ModelRejected::class, function (ModelRejected $event) {
    $model = $event->model;
    $approval = $event->approval;
    
    // Actions to take when rejected
    \Log::info('Model rejected', [
        'model' => get_class($model),
        'reason' => $event->reason,
        'comment' => $event->comment,
    ]);
});
```

### Event Configuration

Configure events globally or per model in your `config/approvals.php`:

```php
'default' => [
    // Event Settings
    'events_enabled' => true,              // Enable event system
    'events_logging' => true,              // Log events
    'events_webhooks_enabled' => false,    // Enable webhooks
    'events_webhooks_endpoints' => [],     // Webhook endpoints
    'events_custom_actions' => [           // Custom event actions
        'model_approved' => [
            // Example: function(ModelApproved $event) { /* custom logic */ }
        ],
        'model_rejected' => [
            // Example: function(ModelRejected $event) { /* custom logic */ }
        ],
        'model_pending' => [
            // Example: function(ModelPending $event) { /* custom logic */ }
        ],
        'model_approving' => [
            // Example: function(ModelApproving $event) { /* custom logic */ }
        ],
        'model_rejecting' => [
            // Example: function(ModelRejecting $event) { /* custom logic */ }
        ],
        'model_setting_pending' => [
            // Example: function(ModelSettingPending $event) { /* custom logic */ }
        ],
    ],
    // ... other settings
],

'models' => [
    'App\Models\Post' => [
        'events_enabled' => false,              // No events for this model
        // ... other settings
    ],
    'App\Models\Comment' => [
        'events_enabled' => true,
        'events_logging' => false,              // No logging for this model
        // ... other settings
    ],
            'App\Models\Product' => [
            'events_webhooks_enabled' => true,      // Webhooks enabled
            'events_webhooks_endpoints' => [
                [
                    'url' => 'https://api.example.com/webhooks/product-approval',
                    'headers' => ['Authorization' => 'Bearer token'],
                    'events' => ['model_approved', 'model_rejected'],
                ],
            ],
            'rejection_reasons' => [
                'inappropriate_content' => 'Inappropriate Content',
                'spam' => 'Spam',
                'duplicate' => 'Duplicate',
                'incomplete' => 'Incomplete',
                'pricing_violation' => 'Pricing Violation',
                'category_mismatch' => 'Category Mismatch',
                'other' => 'Other',
            ],
            // ... other settings
        ],
],
```

### Event Properties

Each event object contains public `readonly` properties for easy access:

- `model`: The model that triggered the event (`ApprovableInterface&Model`)
- `approval`: The approval record (`Approval`)
- `reason`: Rejection reason (`?string`, if applicable)
- `comment`: Rejection comment (`?string`, if applicable)
- `userId`: User ID who performed the action (`?int`)
- `context`: Additional context data (`array`)

## 🎭 Facade Usage

```php
use LaravelApproval\Facades\Approval;

// Assuming $post is an Approvable model instance
// and $user is the user performing the action.

// Approve model
Approval::approve($post, $user->id);

// Reject model
Approval::reject($post, $user->id, 'Invalid content', 'Description');

// Set to pending status
Approval::setPending($post, $user->id);

// Get statistics for specific model
$stats = Approval::getStatistics(\App\Models\Post::class);
// [
//     'total' => 10,
//     'approved' => 7,
//     'pending' => 2,
//     'rejected' => 1,
//     'approved_percentage' => 70.0,
//     'pending_percentage' => 20.0,
//     'rejected_percentage' => 10.0,
// ]

// Get all statistics for configured models
$allStats = Approval::getAllStatistics();
// [
//     'App\Models\Post' => [...],
//     'App\Models\Comment' => [...],
//     'App\Models\Product' => [...],
// ]
```

## 🖥️ Artisan Commands

```bash
# Statistics for all configured models
php artisan approval:status

# Statistics for specific model
php artisan approval:status --model="App\Models\Post"
```

### Command Output Examples

**All Models Statistics:**
+-------------------+-------+----------+--------+----------+-------------+
| Model             | Total | Approved | Pending| Rejected | Approved %  |
+-------------------+-------+----------+--------+----------+-------------+
| App\Models\Post   | 100   | 75       | 15     | 10       | 75.00%      |
| App\Models\Comment| 50    | 40       | 5      | 5        | 80.00%      |
+-------------------+-------+----------+--------+----------+-------------+

**Single Model Statistics:**
+----------+-------+------------+
| Metric   | Count | Percentage |
+----------+-------+------------+
| Total    | 100   | 100%       |
| Approved | 75    | 75.00%     |
| Pending  | 15    | 15.00%     |
| Rejected | 10    | 10.00%     |
+----------+-------+------------+
```

## 🏗️ Architecture Overview

### Package Structure

src/
├── Core/                    # Core business logic
│   ├── ApprovalManager.php  # Main approval logic
│   ├── ApprovalRepository.php # Data access layer
│   ├── ApprovalValidator.php # Validation logic
│   └── ApprovalEventDispatcher.php # Event dispatching
├── Models/                  # Database models
│   └── Approval.php         # Approval model
├── Traits/                  # Model traits
│   ├── Approvable.php       # Main trait (composite)
│   ├── HasApprovals.php     # Relationship management
│   ├── ApprovalScopes.php   # Query scopes
│   └── ApprovalActions.php  # Action methods
├── Events/                  # Event classes
│   ├── ModelApproved.php    # Post-approval event
│   ├── ModelRejected.php    # Post-rejection event
│   └── ...                  # Other events
├── Listeners/               # Event listeners
│   ├── BaseApprovalListener.php # Base listener
│   ├── HandleModelApproved.php  # Approval handler
│   └── ...                  # Other handlers
├── Services/                # Service classes
│   ├── ApprovalService.php  # Main service
│   └── StatisticsService.php # Statistics service
├── Facades/                 # Facade classes
│   └── Approval.php         # Main facade
├── Commands/                # Artisan commands
│   └── ApprovalStatusCommand.php # Status command
├── Scopes/                  # Query scopes
│   └── ApprovableScope.php  # Global scope
├── Contracts/               # Interfaces
│   └── ApprovableInterface.php # Main interface
└── Exceptions/              # Custom exceptions
    ├── ApprovalException.php
    └── UnauthorizedApprovalException.php

### Design Patterns

- **Repository Pattern**: Clean data access layer
- **Service Pattern**: Business logic encapsulation
- **Event-Driven Architecture**: Loose coupling via events
- **Trait Composition**: Modular functionality
- **Facade Pattern**: Simplified API access
- **Observer Pattern**: Event listening system

### Data Flow

1. **Model Action** → ApprovalActions trait
2. **Validation** → ApprovalValidator
3. **Business Logic** → ApprovalManager
4. **Data Persistence** → ApprovalRepository
5. **Event Dispatching** → Event system
6. **Response** → Model with updated status

## 🧪 Testing

```bash
composer test
```

### Test Coverage

The package includes comprehensive tests with high code coverage, ensuring reliability and stability.

- **Unit Tests**: Individual component testing
- **Integration Tests**: End-to-end workflow testing
- **Feature Tests**: Complete feature testing
- **Database Tests**: Migration and factory testing
- **Architecture Tests**: Enforcing clean architecture rules using Pest Arch

### Test Structure

```
tests/
├── ArchTest.php             # Architecture tests
├── Commands/                # Artisan command tests
├── Core/                    # Core service tests
├── Events/                  # Event class tests
├── Exceptions/              # Exception tests
├── Facades/                 # Facade tests
├── Integration/             # Integration tests
├── Listeners/               # Listener tests
├── Models/                  # Model tests
├── Services/                # Service tests
├── Traits/                  # Trait tests
├── ExampleTest.php
└── TestCase.php
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒 Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## ��‍💻 Credits

- [Fatih Zengin](https://github.com/fzengin19)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.  bunu incele ve türkçe olarak bana anlat değerlendir