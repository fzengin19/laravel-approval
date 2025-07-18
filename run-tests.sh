#!/bin/bash

# Laravel Approval Package - Fast Test Runner
echo "🚀 Laravel Approval Package - Fast Test Runner"
echo "=============================================="

# Set memory limit
export PHP_MEMORY_LIMIT=512M

# Clear caches
echo "🧹 Clearing caches..."
rm -rf .phpunit.cache
rm -rf build/
rm -rf coverage/

# Run tests with optimizations
echo "🧪 Running tests..."
php -d memory_limit=512M ./vendor/bin/pest \
    --parallel \
    --processes=4 \
    --stop-on-failure \
    --no-coverage \
    --verbose

# Check exit code
if [ $? -eq 0 ]; then
    echo "✅ All tests passed!"
else
    echo "❌ Some tests failed!"
    exit 1
fi 