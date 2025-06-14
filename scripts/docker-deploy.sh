#!/bin/bash

# Production Deployment Script for Re:do Laravel Application
# This script handles production deployment with Docker

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

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

# Function to check if production environment file exists
check_prod_env() {
    if [ ! -f .env.prod ]; then
        print_error "Production environment file (.env.prod) not found!"
        echo ""
        echo "Please create .env.prod with production values:"
        echo "  APP_ENV=production"
        echo "  APP_DEBUG=false"
        echo "  DB_PASSWORD=secure_password"
        echo "  DB_ROOT_PASSWORD=secure_root_password"
        echo "  REDIS_PASSWORD=secure_redis_password"
        echo "  APP_KEY=base64:..."
        echo ""
        exit 1
    fi
    print_success "Production environment file found"
}

# Function to backup current deployment
backup_deployment() {
    local backup_dir="backups/$(date +%Y%m%d_%H%M%S)"
    
    print_status "Creating backup..."
    mkdir -p "$backup_dir"
    
    # Backup database
    if docker-compose -f docker-compose.yml -f docker-compose.prod.yml ps | grep -q mysql; then
        print_status "Backing up database..."
        docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec -T mysql \
            mysqldump -u root -p"${DB_ROOT_PASSWORD:-root_password}" redo > "$backup_dir/database.sql"
        print_success "Database backup created: $backup_dir/database.sql"
    fi
    
    # Backup storage
    if [ -d "storage" ]; then
        print_status "Backing up storage..."
        cp -r storage "$backup_dir/"
        print_success "Storage backup created: $backup_dir/storage"
    fi
    
    print_success "Backup completed: $backup_dir"
}

# Function to deploy to production
deploy_production() {
    print_status "Starting production deployment..."
    
    # Load production environment
    export $(cat .env.prod | grep -v '^#' | xargs)
    
    # Build and start production containers
    print_status "Building production containers..."
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml build --no-cache
    
    print_status "Starting production containers..."
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
    
    # Wait for database
    print_status "Waiting for database to be ready..."
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec -T mysql \
           mysqladmin ping -h localhost --silent; then
            print_success "Database is ready"
            break
        fi
        
        print_status "Waiting for database... (attempt $attempt/$max_attempts)"
        sleep 2
        ((attempt++))
        
        if [ $attempt -gt $max_attempts ]; then
            print_error "Database failed to start within expected time"
            return 1
        fi
    done
    
    # Run migrations
    print_status "Running database migrations..."
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan migrate --force
    
    # Clear and cache configurations
    print_status "Optimizing application..."
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan config:cache
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan route:cache
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec app php artisan view:cache
    
    print_success "Production deployment completed!"
}

# Function to rollback deployment
rollback_deployment() {
    local backup_dir="$1"
    
    if [ -z "$backup_dir" ]; then
        print_error "Please specify backup directory for rollback"
        echo "Available backups:"
        ls -la backups/ 2>/dev/null || echo "No backups found"
        exit 1
    fi
    
    if [ ! -d "$backup_dir" ]; then
        print_error "Backup directory not found: $backup_dir"
        exit 1
    fi
    
    print_warning "Rolling back to backup: $backup_dir"
    read -p "Are you sure? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_status "Rollback cancelled"
        exit 0
    fi
    
    # Stop current containers
    print_status "Stopping current containers..."
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml down
    
    # Restore database
    if [ -f "$backup_dir/database.sql" ]; then
        print_status "Restoring database..."
        docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d mysql
        sleep 10
        docker-compose -f docker-compose.yml -f docker-compose.prod.yml exec -T mysql \
            mysql -u root -p"${DB_ROOT_PASSWORD:-root_password}" redo < "$backup_dir/database.sql"
    fi
    
    # Restore storage
    if [ -d "$backup_dir/storage" ]; then
        print_status "Restoring storage..."
        rm -rf storage
        cp -r "$backup_dir/storage" .
    fi
    
    # Restart containers
    print_status "Restarting containers..."
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
    
    print_success "Rollback completed"
}

# Function to show production status
show_status() {
    echo "Production Status"
    echo "================="
    echo ""
    
    # Show container status
    print_status "Container Status:"
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml ps
    echo ""
    
    # Show resource usage
    print_status "Resource Usage:"
    docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}\t{{.BlockIO}}"
    echo ""
    
    # Show application health
    print_status "Application Health:"
    if curl -s http://localhost/health > /dev/null; then
        print_success "Application is responding"
    else
        print_error "Application is not responding"
    fi
}

# Function to show logs
show_logs() {
    local service="${1:-app}"
    print_status "Showing logs for service: $service"
    docker-compose -f docker-compose.yml -f docker-compose.prod.yml logs -f "$service"
}

# Main function
main() {
    case "${1:-help}" in
        "deploy")
            check_prod_env
            backup_deployment
            deploy_production
            ;;
        "rollback")
            rollback_deployment "$2"
            ;;
        "status")
            show_status
            ;;
        "logs")
            show_logs "$2"
            ;;
        "backup")
            backup_deployment
            ;;
        "help")
            echo "Production Deployment Script for Re:do"
            echo "======================================"
            echo ""
            echo "Usage: $0 [command] [options]"
            echo ""
            echo "Commands:"
            echo "  deploy              - Deploy to production"
            echo "  rollback [backup]   - Rollback to specific backup"
            echo "  status              - Show production status"
            echo "  logs [service]      - Show logs (default: app)"
            echo "  backup              - Create backup only"
            echo "  help                - Show this help"
            echo ""
            echo "Examples:"
            echo "  $0 deploy"
            echo "  $0 rollback backups/20240101_120000"
            echo "  $0 logs app"
            echo "  $0 status"
            ;;
        *)
            print_error "Unknown command: $1"
            echo "Use '$0 help' for available commands"
            exit 1
            ;;
    esac
}

# Create backups directory if it doesn't exist
mkdir -p backups

# Run main function
main "$@"
