# Changelog

All notable changes to `fzengin19/laravel-approval` will be documented in this file.

## [1.0.0] - 2025-01-25

### Added
- Initial release of Laravel Approval Package
- `Approvable` trait for easy model integration
- `Approval` model with morphable relationships and status constants
- Support for insert and upsert modes
- Global scope functionality for approved-only queries
- Local query scopes (approved, pending, rejected)
- Event system for status changes (ModelApproved, ModelRejected, ModelPending)
- `Approval` facade for easy access to approval methods
- Artisan command `approval:status` for viewing statistics
- Auto-pending functionality on model creation
- Comprehensive test coverage with TDD approach
- PSR-12 compliant code formatting
- Complete documentation and examples

### Changed
- **BREAKING:** Removed customizable status configuration from config file
- **BREAKING:** Approval statuses are now fixed constants in the Approval model
- **BREAKING:** All status values are now accessed via `Approval::STATUS_*` constants
- **BREAKING:** Renamed `HasApprovals` trait to `Approvable` for better naming consistency

### Fixed
- **CRITICAL:** Events are now properly dispatched in upsert mode for all approval methods (setPending, approve, reject)
- Fixed issue where events were only triggered in insert mode, not in upsert mode
- **CRITICAL:** Global scope is now conditionally applied based on `auto_scope` configuration
- Fixed issue where global scope was always applied regardless of configuration

### Added
- **SMART REJECTION HANDLING:** The `reject()` method now intelligently handles rejection reasons
  - Predefined reasons from config are used as `rejection_reason`
  - Custom reasons are automatically categorized as `'other'` and moved to `rejection_comment`
  - Ensures data consistency while maintaining flexibility

### Features
- Easy integration with existing Laravel models
- Flexible configuration options (excluding status customization)
- Event-driven architecture
- CLI tools for monitoring
- Full test coverage
- MIT license 