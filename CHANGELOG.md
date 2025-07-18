# Changelog

All notable changes to `laravel-approval` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2025-07-17

### Changed
- **Cache Management Simplified**
  - Cache clearing command now uses `Cache::flush()` for better compatibility across all cache drivers
  - Removed complex pattern-based cache clearing in favor of simple, reliable approach
  - Cache clearing now works consistently across file, database, Redis, and Memcached drivers

### Added
- **Enhanced Testing Infrastructure**
  - SQLite database support for reliable testing across environments
  - Database cache driver integration for comprehensive cache testing
  - Improved test isolation and reliability
  - 120+ comprehensive tests covering all package features

### Fixed
- **Test Environment Stability**
  - Fixed SQLite database corruption issues in test environment
  - Improved test setup with proper database migration handling
  - Enhanced cache testing with real database storage

## [1.1.0] - 2025-07-17

### Added
- **Automatic Model Discovery**
  - Models using the `HasApproval` trait are now automatically discovered under configurable namespaces (default: `App\Models`).
  - This can be enabled/disabled via the new `auto_discovery` config section.
  - Discovered models are cached for performance. You can clear the discovery cache with:
    ```bash
    php artisan approval:clear-cache --discovery
    ```
  - Models listed in the config always take precedence. Auto-discovered models are added in addition to those.

### Changed
- README and documentation updated to explain the new auto discovery feature and its configuration.

## [1.0.0] - 2025-07-17

### Added
- **Core Approval System**
  - `HasApproval` trait for Eloquent models
  - Support for both column-based and pivot table approaches
  - Smart query selection based on available columns
  - Automatic fallback to pivot table when column doesn't exist

- **Approval Methods**
  - `approve()` - Approve a model
  - `reject($reason)` - Reject a model with reason
  - `setPending()` - Set model to pending status
  - `isApproved()` - Check if model is approved
  - `isPending()` - Check if model is pending
  - `isRejected()` - Check if model is rejected
  - `getApprovalStatus()` - Get current approval status
  - `getRejectionReason()` - Get rejection reason

- **Query Scopes**
  - `scopeApproved()` - Get only approved models
  - `scopePending()` - Get only pending models
  - `scopeRejected()` - Get only rejected models
  - `scopeWithApprovalStatus()` - Include approval status in queries

- **Configuration System**
  - Model-specific configuration support
  - Flexible column type support (`approved_at`, `is_approved`, `approval_status`)
  - Global scope configuration
  - Event system configuration
  - Fallback to pivot table configuration

- **Database Support**
  - `approvals` table migration
  - Polymorphic relationships
  - Proper indexing for performance
  - Foreign key constraints

- **Event System**
  - `ModelApproved` event
  - `ModelRejected` event
  - `ModelPending` event
  - Event payload with model, user, and method information

- **Facade System**
  - `Approval` facade for easy access
  - Static methods for approval operations
  - Statistics and reporting methods
  - Bulk operations support

- **Performance Features**
  - **Caching System**
    - Configurable cache TTL
    - Cache prefix support
    - Automatic cache invalidation
    - Performance optimization

  - **Rate Limiting**
    - Built-in rate limiting for approval actions
    - Configurable attempt limits
    - Time window configuration
    - Protection against spam

- **Validation System**
  - Rejection reason validation
  - Configurable rejection reasons
  - Input sanitization
  - Error handling

- **Statistics & Reporting**
  - Model-specific statistics
  - Global statistics for all models
  - Pending/approved/rejected counts
  - Pagination support for approval records

- **Global Scopes**
  - `OnlyApprovedScope` for default filtering
  - `ConfigurableScope` for flexible filtering
  - Automatic scope application
  - Configurable scope behavior

- **Service Provider**
  - `LaravelApprovalServiceProvider`
  - Automatic service registration
  - Configuration publishing
  - Migration publishing

- **Testing Suite**
  - Comprehensive unit tests
  - Feature tests for integration
  - Architecture tests
  - Performance tests
  - Edge case coverage

- **Documentation**
  - Complete README with examples
  - Configuration guide
  - Usage examples
  - API documentation
  - Installation guide

### Supported Column Types
- **approved_at**: Timestamp column (NULL = pending, timestamp = approved)
- **is_approved**: Boolean column (NULL = pending, true = approved, false = rejected)
- **approval_status**: Enum column ('pending', 'approved', 'rejected')

### Database Requirements
- MySQL 5.7+
- PostgreSQL 9.6+
- SQLite 3.8+

### Laravel Version Support
- Laravel 10.0+
- Laravel 11.0+
- Laravel 12.0+

### PHP Version Support
- PHP 8.2+

> **Note:** Automatic model discovery did not exist in this version.

## [0.1.0] - 2025-07-16

### Added
- Initial package structure
- Basic trait implementation
- Core approval functionality
- Database migrations
- Basic configuration system

### Changed
- Package name from `laravel-moderation` to `laravel-approval`
- Improved architecture for better flexibility

### Fixed
- Initial bug fixes and improvements

---

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

---

## Support

For support, please email fatihzengin654@outlook.com or create an issue on GitHub.

---

**Note**: This changelog follows the [Keep a Changelog](https://keepachangelog.com/) format and uses [Semantic Versioning](https://semver.org/).
