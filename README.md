# Laravel Approval Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fzengin19/laravel-approval.svg?style=flat-square)](https://packagist.org/packages/fzengin19/laravel-approval)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fzengin19/laravel-approval/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/fzengin19/laravel-approval/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Code Coverage](https://img.shields.io/badge/coverage-86%25-brightgreen.svg?style=flat-square)](https://github.com/fzengin19/laravel-approval)

[![Total Downloads](https://img.shields.io/packagist/dt/fzengin19/laravel-approval.svg?style=flat-square)](https://packagist.org/packages/fzengin19/laravel-approval)
[![PHP Version](https://img.shields.io/packagist/php-v/fzengin19/laravel-approval.svg?style=flat-square)](https://packagist.org/packages/fzengin19/laravel-approval)
[![Laravel Version](https://img.shields.io/packagist/laravel/framework/fzengin19/laravel-approval.svg?style=flat-square)](https://packagist.org/packages/fzengin19/laravel-approval)

A powerful, flexible, and performant approval system for Laravel applications. This package provides both column-based and pivot table approaches for managing approval workflows in your Eloquent models with advanced features like rate limiting, caching, comprehensive event system, and **automatic model discovery**.

## 🚀 Features

- **🔄 Flexible Approval Methods**: Use existing columns or separate pivot table
- **⚡ Performance Optimized**: Smart query selection based on available columns
- **🔧 Easy Integration**: Minimal code changes required
- **📡 Event System**: Built-in events for approval actions
- **🌐 Global Scopes**: Automatic filtering based on configuration
- **📊 Statistics & Reporting**: Get approval counts and detailed statistics
- **🎭 Facade Support**: Easy access through Laravel Facade
- **🛡️ Rate Limiting**: Built-in protection against spam
- **💾 Caching System**: Performance optimization with configurable cache
- **✅ Validation**: Comprehensive rejection reason validation
- **📱 Notification Ready**: Built-in notification system support
- **🔍 Audit Trail**: Complete approval history tracking
- **📄 Pagination**: Built-in pagination for approval records
- **✨ Automatic Model Discovery**: Models using the HasApproval trait are auto-discovered (can be disabled)
- **🧪 Comprehensive Testing**: 120+ tests with SQLite support for reliable testing

## 📋 Requirements

- PHP 8.2+
- Laravel 10.0+ / 11.0+ / 12.0+
- MySQL 5.7+ / PostgreSQL 9.6+ / SQLite 3.8+

## 📦 Installation

You can install the package via composer:

```bash
composer require fzengin19/laravel-approval
```

## ⚙️ Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="LaravelApproval\LaravelApprovalServiceProvider" --tag="laravel-approval-config"
```

This will create a `config/approval.php` file where you can configure your models.

### 🔍 Automatic Model Discovery

By default, the package will **automatically discover all models using the `HasApproval` trait** under the `App\Models` namespace. This means you do not have to manually list every model in the config file. You can control this feature via the config:

```php
'auto_discovery' => [
    'enabled' => true,           // Enable automatic model discovery
    'paths' => [                 // Namespaces to scan for models
        'App\\Models',
    ],
    'cache_ttl' => 3600,        // Cache TTL for discovered models (seconds)
],
```

- **Disable auto discovery:** Set `'enabled' => false` to only use models listed in the config.
- **Change scan paths:** Add or change namespaces in the `paths` array.
- **Cache:** Discovered models are cached for performance. You can clear the cache with:

```bash
php artisan approval:clear-cache --discovery
```

> **Note:** Models listed in the config always take precedence. Auto-discovered models are added in addition to those.

## 🎯 Quick Start

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelApproval\Traits\HasApproval;

class Job extends Model
{
    use HasApproval;

    // Your model code...
}
```

### 2. Configure Your Model

In `config/approval.php`:

```php
'models' => [
    'App\Models\Job' => [
        'column' => 'approved_at',           // Use existing column
        'fallback_to_pivot' => true,         // Use pivot table if column doesn't exist
        'auto_scope' => true,                // Add global scope
        'events' => true,                    // Fire events
        'show_only_approved_by_default' => false, // Show all by default
    ],
    'App\Models\Company' => [
        'column' => null,                    // No column, use pivot table
        'fallback_to_pivot' => true,         // Use pivot table
        'auto_scope' => true,                // Add global scope
        'events' => true,                    // Fire events
        'show_only_approved_by_default' => true, // Show only approved by default
    ],
],
```

### 3. Run Migrations

If you're using the pivot table approach, run the migrations:

```bash
php artisan vendor:publish --provider="LaravelApproval\LaravelApprovalServiceProvider" --tag="laravel-approval-migrations"
php artisan migrate
```

## 📖 Usage Guide

### Basic Operations

#### Approve a Model

```php
$job = Job::find(1);

// Approve the job
$job->approve();

// Approve with specific user ID
$job->approve(auth()->id());
```

#### Reject a Model

```php
$job = Job::find(1);

// Reject with reason
$job->reject('Inappropriate content');

// Reject with specific user ID
$job->reject('Spam', auth()->id());
```

#### Set to Pending

```php
$job = Job::find(1);

// Set job to pending status
$job->setPending();
```

#### Check Status

```php
$job = Job::find(1);

if ($job->isApproved()) {
    echo "Job is approved!";
}

if ($job->isPending()) {
    echo "Job is pending approval.";
}

if ($job->isRejected()) {
    echo "Job was rejected: " . $job->getRejectionReason();
}

// Get approval status
$status = $job->getApprovalStatus(); // 'pending', 'approved', or 'rejected'
```

### Query Scopes

```php
// Get only approved jobs
$approvedJobs = Job::approved()->get();

// Get only pending jobs
$pendingJobs = Job::pending()->get();

// Get only rejected jobs
$rejectedJobs = Job::rejected()->get();

// Include approval status in query
$jobsWithApproval = Job::withApprovalStatus()->get();
```

### Using the Facade

```php
use LaravelApproval\Facades\Approval;

// Approve a model
Approval::approve($job);

// Reject a model
Approval::reject($job, 'Invalid content');

// Check status
if (Approval::isApproved($job)) {
    echo "Approved!";
}

// Get statistics
$stats = Approval::getStatistics(Job::class);
// Returns: ['pending' => 5, 'approved' => 10, 'rejected' => 2, 'total' => 17]

// Get pending models
$pendingJobs = Approval::getPendingModels(Job::class, 10);

// Get approval records with pagination
$records = Approval::getApprovalRecordsPaginated(Job::class, 'pending', 15);
```

## 🛠️ Commands

### Cache Management

```bash
# Clear all cache (recommended for production)
php artisan approval:clear-cache

# Clear only model discovery cache
php artisan approval:clear-cache --discovery
```

### Status Check

```bash
# Check package status and configuration
php artisan approval:status

# Check status for specific model
php artisan approval:status --model="App\Models\Job"
```

## ⚙️ Configuration Options

### Model Configuration

Each model can be configured with the following options:

- `column`: The column name to use for approval status (e.g., 'approved_at', 'is_approved', 'approval_status')
- `fallback_to_pivot`: Whether to use pivot table if column doesn't exist
- `auto_scope`: Whether to automatically add global scope
- `events`: Whether to fire approval events
- `show_only_approved_by_default`: Whether to show only approved models by default

### Supported Column Types

1. **approved_at**: Timestamp column (NULL = pending, timestamp = approved)
2. **is_approved**: Boolean column (NULL = pending, true = approved, false = rejected)
3. **approval_status**: Enum column ('pending', 'approved', 'rejected')

### Advanced Configuration

```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600, // 1 hour
    'prefix' => 'approval_status_',
],

'rate_limiting' => [
    'enabled' => true,
    'max_attempts' => 60,
    'decay_minutes' => 1,
],

'notifications' => [
    'enabled' => true,
    'channels' => [
        'mail',
        'database',
    ],
],
```

## 📡 Events

The package fires the following events:

- `LaravelApproval\Events\ModelApproved`
- `LaravelApproval\Events\ModelRejected`
- `LaravelApproval\Events\ModelPending`

### Event Listeners

```php
use LaravelApproval\Events\ModelApproved;
use LaravelApproval\Events\ModelRejected;
use LaravelApproval\Events\ModelPending;

Event::listen(ModelApproved::class, function ($event) {
    $model = $event->getModel();
    $approvedBy = $event->getApprovedBy();
    $approvedAt = $event->getApprovedAt();
    $method = $event->getApprovalMethod(); // 'column' or 'pivot'
    
    // Send notification, log, etc.
});

Event::listen(ModelRejected::class, function ($event) {
    // Handle rejection
});

Event::listen(ModelPending::class, function ($event) {
    // Handle pending status
});
```

## 🔧 Advanced Features

### Rate Limiting

Protect your approval system from spam and abuse:

```php
// Configure in config/approval.php
'rate_limiting' => [
    'enabled' => true,
    'max_attempts' => 60,        // Max attempts per minute
    'decay_minutes' => 1,        // Time window
],
```

### Caching System

Improve performance with configurable caching:

```php
// Configure in config/approval.php
'cache' => [
    'enabled' => true,
    'ttl' => 3600,               // Cache TTL in seconds
    'prefix' => 'approval_status_',
],
```

### Custom Rejection Reasons

Configure rejection reasons in `config/approval.php`:

```php
'rejection_reasons' => [
    'inappropriate_content',
    'spam',
    'duplicate',
    'incomplete',
    'violates_policy',
    'other',
],
```

### Statistics and Reporting

```php
use LaravelApproval\Facades\Approval;

// Get statistics for a specific model
$jobStats = Approval::getStatistics(Job::class);

// Get statistics for all configured models
$allStats = Approval::getAllStatistics();

// Get approval records with pagination
$records = Approval::getApprovalRecordsPaginated(Job::class, 'pending', 15);

// Get approval records by status
$pendingRecords = Approval::getApprovalRecords(Job::class, 'pending', 10);
```

### Global Scopes

The package automatically adds global scopes based on your configuration. You can disable this by setting `auto_scope` to `false` in your model configuration.

## 🧪 Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

The package includes comprehensive tests with SQLite support for reliable testing across different environments.

## 📊 Performance

This package is optimized for performance with:

- **Smart Query Selection**: Automatically chooses the most efficient query method
- **Caching Support**: Configurable caching for approval statuses
- **Index Optimization**: Proper database indexes for fast queries
- **Eager Loading**: Optimized relationship loading
- **Rate Limiting**: Protection against performance abuse

## 🔒 Security

- **Rate Limiting**: Built-in protection against spam
- **Validation**: Comprehensive input validation
- **Audit Trail**: Complete approval history tracking
- **User Tracking**: Track who approved/rejected what and when

## 🤝 Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 🐛 Bug Reports

If you discover any bugs, please report them on the [GitHub issues page](https://github.com/fzengin19/laravel-approval/issues).

## 🔐 Security

If you discover any security related issues, please email fatihzengin654@outlook.com instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 👨‍💻 Credits

- [Fatih Zengin](https://github.com/fzengin19)
- [All Contributors](../../contributors)

## 📈 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

**Made with ❤️ by [Fatih Zengin](https://github.com/fzengin19)**
