# Development Plan

This document outlines the development plan and future enhancements for the Laravel Approval package.

## Missing Tests Analysis (as of 2024-07-26)

Based on a thorough analysis of the existing test suite against the source code, the following areas have been identified as lacking sufficient test coverage. Adding tests for these scenarios will improve the package's robustness and reliability.

### 1. `Core/ApprovalValidator.php`

The `validateModelConfiguration` method can be made more robust against all possible invalid entries in the configuration file.

-   **Test Scenario:** A specific test for when the `mode` setting contains an invalid value other than `'insert'` or `'upsert'`, such as `'invalid_mode'`.
-   **Test Scenario:** A case where the `rejection_reasons` array contains non-string values (e.g., `integer`, `null`).
-   **Test Scenario:** A case where the `allow_custom_reasons` setting contains a non-boolean value.

### 2. `Services/StatisticsService.php`

The statistics service should be more resilient to incorrect or unexpected inputs.

-   **Test Scenario:** Test what happens when `getStatisticsForDateRange` is given `null`, an empty string, or invalid date formats that are not `Y-m-d` (e.g., `'invalid-date'`, `'2023-13-01'`). The expectation is that it should throw an `Exception` or return a `null`/empty result.
-   **Test Scenario:** A test to verify that when `getDetailedStatistics` is called for a model class with no approval records yet, the method returns an empty but validly structured array.

### 3. `Core/WebhookDispatcher.php`

Error handling in webhook dispatching should cover a wider variety of failure scenarios.

-   **Test Scenario:** The case where the webhook URL returns a `404 Not Found` status.
-   **Test Scenario:** The case where the webhook URL returns a server error, such as `500 Internal Server Error`.
-   **Test Scenario:** The case where the webhook URL is an invalid or unresolvable domain (e.g., `'http://invalid.domain.test'`).

### 4. `Scopes/ApprovableScope.php`

The global scope itself is not tested directly. While less critical as it relies on Laravel's internal mechanisms, it is important for complete unit test coverage.

-   **Test Scenario:** Directly test (with a `Builder` mock) that the `apply` method adds the correct `whereHas` condition to the builder when the `show_only_approved_by_default` setting is `true`.
-   **Test Scenario:** Test that the `extend` method correctly adds the `withUnapproved` macro to the builder. 