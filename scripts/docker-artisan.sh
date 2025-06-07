#!/bin/bash

# Laravel Artisan Commands via Docker
# This script provides easy access to Laravel artisan commands in Docker containers

set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Check if containers are running
check_containers() {
    if ! docker-compose ps | grep -q "redo-app.*Up"; then
        echo "Error: Application container is not running."
        echo "Please start the containers first: docker-compose up -d"
        exit 1
    fi
}

# Function to run artisan commands
run_artisan() {
    check_containers
    print_status "Running: php artisan $*"
    docker-compose exec app php artisan "$@"
}

# Function to show common commands
show_help() {
    echo "Laravel Artisan Commands via Docker"
    echo "===================================="
    echo ""
    echo "Usage: $0 [artisan-command] [arguments]"
    echo ""
    echo "Common commands:"
    echo "  migrate              - Run database migrations"
    echo "  migrate:fresh        - Drop all tables and re-run migrations"
    echo "  migrate:rollback     - Rollback migrations"
    echo "  db:seed              - Seed the database"
    echo "  make:model [name]    - Create a new model"
    echo "  make:controller [name] - Create a new controller"
    echo "  make:migration [name] - Create a new migration"
    echo "  route:list           - List all routes"
    echo "  cache:clear          - Clear application cache"
    echo "  config:clear         - Clear configuration cache"
    echo "  view:clear           - Clear compiled views"
    echo "  queue:work           - Start queue worker"
    echo "  schedule:run         - Run scheduled tasks"
    echo "  tinker               - Start Laravel tinker"
    echo ""
    echo "Examples:"
    echo "  $0 migrate"
    echo "  $0 make:model Todo"
    echo "  $0 route:list"
    echo "  $0 cache:clear"
    echo ""
}

# Handle arguments
if [ $# -eq 0 ] || [ "$1" = "help" ] || [ "$1" = "--help" ] || [ "$1" = "-h" ]; then
    show_help
    exit 0
fi

# Run the artisan command
run_artisan "$@"
print_success "Command completed"
