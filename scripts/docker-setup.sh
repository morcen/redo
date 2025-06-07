#!/bin/bash

# Docker Setup Script for re:do Laravel Application
# This script helps with initial setup and common Docker operations

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
    print_success "Docker is running"
}

# Function to check if Docker Compose is available
check_docker_compose() {
    if ! command -v docker-compose > /dev/null 2>&1; then
        print_error "Docker Compose is not installed. Please install Docker Compose and try again."
        exit 1
    fi
    print_success "Docker Compose is available"
}

# Function to setup environment file
setup_env() {
    if [ ! -f .env ]; then
        print_status "Setting up environment file..."
        cp .env.docker .env
        print_success "Environment file created from .env.docker"
    else
        print_warning "Environment file already exists. Skipping..."
    fi
}

# Function to generate application key
generate_app_key() {
    print_status "Generating application key..."
    docker-compose run --rm app php artisan key:generate
    print_success "Application key generated"
}

# Function to build and start containers
start_containers() {
    print_status "Building and starting containers..."
    docker-compose up -d --build

    # Check if containers started successfully
    sleep 5
    local failed_containers=$(docker-compose ps --services --filter "status=exited")
    if [ -n "$failed_containers" ]; then
        print_error "Some containers failed to start:"
        echo "$failed_containers"
        print_status "Checking logs for failed containers..."
        for container in $failed_containers; do
            echo "=== Logs for $container ==="
            docker-compose logs --tail=20 "$container"
            echo ""
        done
        return 1
    fi

    print_success "Containers started successfully"
}

# Function to wait for database to be ready
wait_for_database() {
    print_status "Waiting for database to be ready..."
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose exec -T mysql mysqladmin ping -h localhost --silent; then
            print_success "Database is ready"
            return 0
        fi
        
        print_status "Waiting for database... (attempt $attempt/$max_attempts)"
        sleep 2
        ((attempt++))
    done
    
    print_error "Database failed to start within expected time"
    return 1
}

# Function to run migrations
run_migrations() {
    print_status "Running database migrations..."
    docker-compose exec app php artisan migrate --force
    print_success "Migrations completed"
}

# Function to install dependencies
install_dependencies() {
    print_status "Installing Composer dependencies..."
    docker-compose exec app composer install
    
    print_status "Installing Node.js dependencies..."
    docker-compose exec vite npm install
    
    print_success "Dependencies installed"
}

# Function to build assets
build_assets() {
    print_status "Building frontend assets..."
    docker-compose exec vite npm run build
    print_success "Assets built"
}

# Function to show application URLs
show_urls() {
    echo ""
    print_success "Setup completed! Your application is ready."
    echo ""
    echo "Application URLs:"
    echo "  🌐 Web Application: http://localhost:8080"
    echo "  ⚡ Vite Dev Server: http://localhost:5173"
    echo "  🗄️  MySQL Database: localhost:3306"
    echo "  🔴 Redis Cache: localhost:6379"
    echo ""
    echo "Useful commands:"
    echo "  📊 View logs: docker-compose logs -f"
    echo "  🔧 Laravel commands: docker-compose exec app php artisan [command]"
    echo "  🛑 Stop containers: docker-compose down"
    echo ""
}

# Main setup function
main() {
    echo "🚀 Setting up re:do Laravel Application with Docker"
    echo "=================================================="
    
    # Check prerequisites
    check_docker
    check_docker_compose
    
    # Setup environment
    setup_env
    
    # Start containers
    start_containers
    
    # Generate app key if needed
    if ! grep -q "APP_KEY=base64:" .env; then
        generate_app_key
    else
        print_warning "Application key already exists. Skipping generation..."
    fi
    
    # Wait for database and run migrations
    if wait_for_database; then
        run_migrations
    else
        print_error "Skipping migrations due to database connection issues"
    fi
    
    # Install dependencies and build assets
    install_dependencies
    build_assets
    
    # Show final information
    show_urls
}

# Handle command line arguments
case "${1:-setup}" in
    "setup")
        main
        ;;
    "start")
        print_status "Starting containers..."
        docker-compose up -d
        print_success "Containers started"
        ;;
    "stop")
        print_status "Stopping containers..."
        docker-compose down
        print_success "Containers stopped"
        ;;
    "restart")
        print_status "Restarting containers..."
        docker-compose restart
        print_success "Containers restarted"
        ;;
    "logs")
        docker-compose logs -f
        ;;
    "build")
        print_status "Building containers..."
        docker-compose build
        print_success "Containers built"
        ;;
    "clean")
        print_warning "This will remove all containers, volumes, and images"
        read -p "Are you sure? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            docker-compose down -v --rmi all
            print_success "Cleanup completed"
        else
            print_status "Cleanup cancelled"
        fi
        ;;
    "help")
        echo "Usage: $0 [command]"
        echo ""
        echo "Commands:"
        echo "  setup     - Initial setup (default)"
        echo "  start     - Start containers"
        echo "  stop      - Stop containers"
        echo "  restart   - Restart containers"
        echo "  logs      - View logs"
        echo "  build     - Build containers"
        echo "  clean     - Remove all containers and volumes"
        echo "  help      - Show this help"
        ;;
    *)
        print_error "Unknown command: $1"
        echo "Use '$0 help' for available commands"
        exit 1
        ;;
esac
