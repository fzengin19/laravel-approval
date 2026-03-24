# Changelog

All notable changes to `fzengin19/laravel-approval` will be documented in this file.

## [Unreleased]

### Fixed
- Local `approved`, `pending`, and `rejected` scopes now return correct results even when approved-only visibility is enabled.
- Statistics and the `approval:status` command now ignore the approved-only visibility scope so totals stay authoritative.
- Upsert mode now updates the latest approval record deterministically in normal sequential writes.
- Approval helper methods now see freshly written state in the same request by syncing cached relations after writes.
- Explicit invalid `userId` inputs now fail closed instead of silently degrading to anonymous actions.
- Rejection reason validation now runs during reject flows, and unknown reasons continue to normalize to `other`.

### Changed
- Documentation now matches the current contract: PHP 8.3+, Laravel 10+, enum-backed statuses, post-save `ModelSettingPending`, `causedBy` event payloads, and class-based custom actions.
- `getModelStatistics($model)` is documented as class-level aggregation for the model instance you pass in.
- Coverage HTML artefacts are treated as generated output instead of source-controlled package content.

## [1.0.0] - 2025-01-25

### Added
- Initial release of Laravel Approval Package
- `Approvable` trait for easy model integration
- `Approval` model with morphable relationships and enum-backed statuses
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
- **BREAKING:** Approval statuses are exposed through the `ApprovalStatus` enum
- **BREAKING:** All status values are now accessed via the `ApprovalStatus` enum cases
- **BREAKING:** Renamed `HasApprovals` trait to `Approvable` for better naming consistency

### Fixed
- **CRITICAL:** Events are now properly dispatched in upsert mode for all approval methods (setPending, approve, reject)
- Fixed issue where events were only triggered in insert mode, not in upsert mode
- Fixed issue where the global scope was always applied regardless of configuration
