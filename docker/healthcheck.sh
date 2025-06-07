#!/bin/bash

# Health check script for Docker containers
# This script checks if the Laravel application is responding correctly

set -e

# Check if PHP-FPM is running
if ! pgrep -f "php-fpm: master process" > /dev/null; then
    echo "PHP-FPM is not running"
    exit 1
fi

# Check if the application responds to HTTP requests
if command -v curl > /dev/null; then
    # Use curl if available
    if ! curl -f -s http://localhost/health > /dev/null; then
        echo "Application is not responding to HTTP requests"
        exit 1
    fi
else
    # Fallback to wget
    if ! wget -q --spider http://localhost/health; then
        echo "Application is not responding to HTTP requests"
        exit 1
    fi
fi

# Check database connection (if app container)
if [ -f "/var/www/html/artisan" ]; then
    if ! php /var/www/html/artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; then
        echo "Database connection failed"
        exit 1
    fi
fi

echo "Health check passed"
exit 0
