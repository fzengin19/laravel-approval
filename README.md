# Laravel Approval Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fzengin19/laravel-approval.svg?style=flat-square)](https://packagist.org/packages/fzengin19/laravel-approval)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fzengin19/laravel-approval/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/fzengin19/laravel-approval/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/fzengin19/laravel-approval/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/fzengin19/laravel-approval/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/fzengin19/laravel-approval.svg?style=flat-square)](https://packagist.org/packages/fzengin19/laravel-approval)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg?style=flat-square)](https://github.com/fzengin19/laravel-approval)

A comprehensive approval system package for Laravel. Provides a powerful and flexible solution for managing approval statuses of your models. Developed with TDD approach, reliable code with 100% test coverage.

## ✨ Features

- 🚀 **Easy Integration**: Integrate your models with approval system by just adding a trait
- ⚙️ **Flexible Configuration**: Two different modes (insert/upsert) and customizable settings
- 🧠 **Smart Rejection Handling**: Intelligent categorization of rejection reasons
- 🔍 **Global Scope**: Automatically show only approved records
- 🔔 **Event System**: Listen to events on status changes
- 📧 **Email Notifications**: Automatic email notifications for approval status changes
- 🗄️ **Database Notifications**: Store notifications in database
- 🎭 **Facade Support**: Facade API for easy usage
- 🖥️ **Artisan Commands**: View statistics via CLI
- 🧪 **Comprehensive Testing**: 57 tests with 100% code coverage using TDD approach

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

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\Approvable;

class Post extends Model
{
    use Approvable;

    protected $fillable = [
        'title',
        'content',
    ];
}
```

### 4. Start Using

```php
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
```

That's it! Your model now has full approval functionality. 🎉

## 📦 Installation

### Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher

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

The published config file (`config/approvals.php`) contains all available options:

```php
return [
    'default' => [
        'mode' => 'insert',                    // 'insert' or 'upsert'
        'auto_pending_on_create' => false,     // Auto pending when model is created
        'show_only_approved_by_default' => false, // Is global scope active?
        'auto_scope' => true,                  // Automatically add global scope
        'events' => true,                      // Trigger events
    ],
    
    'models' => [
        // Model specific settings
        'App\Models\Post' => [
            'mode' => 'upsert',
            'auto_pending_on_create' => true,
        ],
    ],
    
    'rejection_reasons' => [
        'inappropriate_content' => 'Inappropriate Content',
        'spam' => 'Spam',
        'duplicate' => 'Duplicate',
        'incomplete' => 'Incomplete',
        'other' => 'Other',
    ],
];
```

**Note:** Approval statuses are fixed constants in the `Approval` model:
- `Approval::STATUS_PENDING` = 'pending'
- `Approval::STATUS_APPROVED` = 'approved'  
- `Approval::STATUS_REJECTED` = 'rejected'

## 📖 Usage Guide

### Basic Operations

```php
// Create a model
$post = Post::create(['title' => 'New Post']);

// Check current status
$post->isPending();    // false (no approval record yet)
$post->isApproved();   // false
$post->isRejected();   // false
$post->getApprovalStatus(); // null

// Set to pending
$post->setPending(1);  // 1 = approving user ID

// Approve
$post->approve(1);

// Reject with predefined reason
$post->reject(1, 'spam', 'Additional details');

// Reject with custom reason (automatically categorized as 'other')
$post->reject(1, 'Custom reason', 'Additional details');
```

### Query Scopes

```php
// Get only approved posts
$approvedPosts = Post::approved()->get();

// Get only pending posts
$pendingPosts = Post::pending()->get();

// Get only rejected posts
$rejectedPosts = Post::rejected()->get();

// Get with approval status
$posts = Post::withApprovalStatus()->get();

// Include unapproved posts (when global scope is active)
$allPosts = Post::withUnapproved()->get();
```

### Modes

#### Insert Mode (Default)
Creates a new approval record for each status change. Ideal for history tracking.

```php
config(['approvals.default.mode' => 'insert']);

$post->setPending(1);  // New record
$post->approve(1);     // New record
$post->reject(1);      // New record
// Total: 3 records
```

#### Upsert Mode
Updates existing approval record. Ideal for keeping single record.

```php
config(['approvals.default.mode' => 'upsert']);

$post->setPending(1);  // New record
$post->approve(1);     // Update existing record
$post->reject(1);      // Update existing record
// Total: 1 record
```

### Smart Rejection Handling

The `reject()` method intelligently handles rejection reasons:

```php
// Using predefined reason
$post->reject(1, 'spam', 'Additional details');
// Result: rejection_reason = 'spam', rejection_comment = 'Additional details'

// Using custom reason
$post->reject(1, 'Custom reason', 'Additional details');
// Result: rejection_reason = 'other', rejection_comment = 'Custom reason - Additional details'
```

### Global Scope

When global scope is active, only approved records are visible:

```php
// Only approved posts
$posts = Post::all();

// To see all posts
$allPosts = Post::withUnapproved()->get();
```

#### Auto Scope Configuration

Control whether the global scope is automatically applied:

```php
// Disable auto scope globally
config(['approvals.default.auto_scope' => false]);

// Or disable for specific models
config([
    'approvals.models' => [
        'App\Models\Post' => [
            'auto_scope' => false,
        ],
    ],
]);

// Manually apply the scope when needed
$approvedPosts = Post::withGlobalScope('approvable', new \LaravelApproval\Scopes\ApprovableScope)->get();
```

### Auto Pending

Automatically set to pending status when model is created:

```php
config(['approvals.default.auto_pending_on_create' => true]);

$post = Post::create(['title' => 'Test']);
// Will automatically be in pending status
```

### Model-Specific Configuration

Override default settings for specific models:

```php
config([
    'approvals.default.mode' => 'insert',
    'approvals.models' => [
        'App\Models\Post' => [
            'mode' => 'upsert',                    // This model uses upsert mode
            'auto_pending_on_create' => true,      // Auto pending for this model
            'show_only_approved_by_default' => true, // Global scope active for this model
            'events' => false,                     // No events for this model
        ],
        'App\Models\Comment' => [
            'mode' => 'insert',                    // This model uses insert mode
            'auto_pending_on_create' => false,     // No auto pending for this model
        ],
    ],
]);
```

## 🔔 Events

Listen to events on status changes. Events are triggered in both insert and upsert modes:

```php
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;

Event::listen(ModelApproved::class, function ($event) {
    $model = $event->model;
    $approval = $event->approval;
    
    // Actions to take when approved
    Mail::to($model->user)->send(new PostApprovedMail($model));
});

Event::listen(ModelRejected::class, function ($event) {
    // Actions to take when rejected
});

Event::listen(ModelPending::class, function ($event) {
    // Actions to take when set to pending
});
```

## 📧 Email Notifications

The package includes automatic email notifications for approval status changes. Configure notifications in your `.env` file:

```env
# Enable notifications
APPROVAL_NOTIFICATIONS_ENABLED=true

# Enable email notifications
APPROVAL_MAIL_ENABLED=true

# Enable database notifications
APPROVAL_DB_NOTIFICATIONS_ENABLED=true

# Admin email for notifications
APPROVAL_ADMIN_EMAIL=admin@example.com

# Notify model owner (if model has created_by field)
APPROVAL_NOTIFY_MODEL_OWNER=true

# Which events to notify about
APPROVAL_NOTIFY_APPROVED=true
APPROVAL_NOTIFY_REJECTED=true
APPROVAL_NOTIFY_PENDING=false

# Custom mail template (optional)
APPROVAL_MAIL_TEMPLATE=emails.approval-status

# Mail from settings (optional, uses app defaults if not set)
APPROVAL_MAIL_FROM_ADDRESS=noreply@example.com
APPROVAL_MAIL_FROM_NAME="Your App Name"
```

### Configuration Options

You can also configure notifications programmatically:

```php
// Enable notifications
config(['approvals.features.notifications.enabled' => true]);

// Configure mail settings
config([
    'approvals.features.notifications.mail.enabled' => true,
    'approvals.features.notifications.mail.template' => 'custom.template',
]);

// Configure database notifications
config(['approvals.features.notifications.database.enabled' => true]);

// Configure which events trigger notifications
config([
    'approvals.features.notifications.events.approved' => true,
    'approvals.features.notifications.events.rejected' => true,
    'approvals.features.notifications.events.pending' => false,
]);

// Configure recipients
config([
    'approvals.features.notifications.recipients.admin_email' => 'admin@example.com',
    'approvals.features.notifications.recipients.notify_model_owner' => true,
]);
```

### Custom Mail Templates

You can create custom mail templates for notifications:

```php
// resources/views/emails/approval-status.blade.php
@component('mail::message')
# {{ $approvable->title ?? class_basename($approvable) }} {{ ucfirst($approval->status) }}

Hello {{ $notifiable->name }}!

Your {{ class_basename($approvable) }} has been {{ $approval->status }}.

@if($approval->status === 'rejected' && $approval->reason)
**Reason:** {{ $approval->reason }}

@if($approval->comment)
**Comment:** {{ $approval->comment }}
@endif
@endif

@component('mail::button', ['url' => url('/admin/approvals/' . $approval->id)])
View Details
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
```

### Notification Recipients

The system automatically sends notifications to:

1. **Model Owner**: If the model has a `created_by` field, notifications are sent to that user
2. **Admin**: If `APPROVAL_ADMIN_EMAIL` is set, notifications are sent to that email
3. **Custom Recipients**: You can extend the `SendApprovalNotifications` listener for custom logic

## 🎭 Facade Usage

```php
use LaravelApproval\Facades\Approval;

// Approve model
Approval::approve($post, 1);

// Reject model
Approval::reject($post, 1, 'Invalid content', 'Description');

// Set to pending status
Approval::setPending($post, 1);

// Get statistics
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

// Get all statistics
$allStats = Approval::getAllStatistics();
```

## 🖥️ Artisan Commands

```bash
# Statistics for all models
php artisan approval:status

# Statistics for specific model
php artisan approval:status --model="App\Models\Post"
```

## 🧪 Testing

```bash
composer test
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒 Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## 👨‍💻 Credits

- [Fatih Zengin](https://github.com/fzengin19)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
