# Docker Setup for Re:do Laravel Application

This document provides comprehensive instructions for running the Re:do Laravel todo list application using Docker.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- Git

## Quick Start

### Automated Setup (Recommended)
```bash
git clone <repository-url>
cd Re-Do
./scripts/docker-setup.sh
```

> **Note**: The initial Docker build may take 5-10 minutes as it installs PHP extensions, Composer dependencies, and builds frontend assets. Subsequent builds will be much faster due to Docker layer caching.

### Manual Setup
1. **Clone and setup environment**:
   ```bash
   git clone <repository-url>
   cd Re-Do
   cp .env.docker .env
   ```

2. **Generate application key**:
   ```bash
   docker-compose run --rm app php artisan key:generate
   ```

3. **Start the application**:
   ```bash
   docker-compose up -d
   ```

4. **Run database migrations**:
   ```bash
   docker-compose exec app php artisan migrate
   ```

5. **Access the application**:
   - Web Application: http://localhost:8080
   - Vite Dev Server: http://localhost:5173
   - MySQL: localhost:3306
   - Redis: localhost:6379

## Helper Scripts

The Docker setup includes several helper scripts to simplify common operations:

### `scripts/docker-setup.sh`
Automated setup and management script:
```bash
./scripts/docker-setup.sh          # Initial setup
./scripts/docker-setup.sh start    # Start containers
./scripts/docker-setup.sh stop     # Stop containers
./scripts/docker-setup.sh restart  # Restart containers
./scripts/docker-setup.sh logs     # View logs
./scripts/docker-setup.sh build    # Build containers
./scripts/docker-setup.sh clean    # Clean up everything
```

### `scripts/docker-artisan.sh`
Easy access to Laravel artisan commands:
```bash
./scripts/docker-artisan.sh migrate
./scripts/docker-artisan.sh make:model Todo
./scripts/docker-artisan.sh cache:clear
./scripts/docker-artisan.sh tinker
```

### `scripts/docker-deploy.sh`
Production deployment management:
```bash
./scripts/docker-deploy.sh deploy    # Deploy to production
./scripts/docker-deploy.sh status    # Show production status
./scripts/docker-deploy.sh logs      # View production logs
./scripts/docker-deploy.sh backup    # Create backup
./scripts/docker-deploy.sh rollback  # Rollback deployment
```

## Architecture

The Docker setup includes the following services:

### Core Services
- **app**: Laravel application (PHP 8.2-FPM)
- **nginx**: Web server (development only)
- **mysql**: MySQL 8.0 database
- **redis**: Redis cache and session store

### Development Services
- **node**: Vite development server for hot reloading
- **queue**: Laravel queue worker
- **scheduler**: Laravel task scheduler

## File Structure

```
docker/
├── nginx/
│   └── nginx.conf          # Nginx configuration
├── php/
│   └── php.ini            # PHP configuration
├── mysql/
│   └── init.sql           # Database initialization
└── supervisor/
    └── supervisord.conf   # Process management (production)

Dockerfile                 # Multi-stage Docker build
docker-compose.yml         # Development environment
docker-compose.prod.yml    # Production overrides
.dockerignore             # Build context optimization
.env.docker              # Docker environment template
```

## Development Workflow

### Starting the Environment
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
```

### Laravel Commands
```bash
# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan make:model Todo

# Install Composer dependencies
docker-compose exec app composer install

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Database Operations
```bash
# Access MySQL CLI
docker-compose exec mysql mysql -u redo_user -p redo

# Run migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Fresh migration with seeding
docker-compose exec app php artisan migrate:fresh --seed
```

### Frontend Development
```bash
# Install Node.js dependencies
docker-compose exec node npm install

# Build assets for production
docker-compose exec node npm run build

# Watch for changes (already running in node service)
docker-compose logs -f node
```

### Testing
```bash
# Run PHPUnit tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter=TodoTest
```

## Production Deployment

### Using Production Compose File
```bash
# Build and start production environment
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build

# Scale queue workers
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --scale queue=3
```

### Environment Variables for Production
Create a `.env.prod` file with production values:
```bash
APP_ENV=production
APP_DEBUG=false
DB_PASSWORD=secure_password
DB_ROOT_PASSWORD=secure_root_password
REDIS_PASSWORD=secure_redis_password
```

### SSL/HTTPS Setup
For production, place SSL certificates in `docker/nginx/ssl/` and update nginx configuration.

## Troubleshooting

### Common Issues

1. **Nginx User Error** (`getpwnam("www-data") failed`):
   ```bash
   # The setup uses nginx-simple.conf which works with Alpine Linux
   # If you see this error, restart the nginx container:
   docker-compose restart nginx

   # Or check nginx logs:
   docker-compose logs nginx
   ```

2. **Permission Issues**:
   ```bash
   # Fix storage permissions
   docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
   docker-compose exec app chmod -R 775 storage bootstrap/cache
   ```

2. **Database Connection Issues**:
   ```bash
   # Check MySQL status
   docker-compose exec mysql mysqladmin ping -h localhost
   
   # Restart MySQL
   docker-compose restart mysql
   ```

3. **Redis Connection Issues**:
   ```bash
   # Test Redis connection
   docker-compose exec redis redis-cli ping
   
   # Clear Redis cache
   docker-compose exec app php artisan cache:clear
   ```

4. **Asset Build Issues**:
   ```bash
   # Rebuild assets
   docker-compose exec node npm run build
   
   # Clear Vite cache
   docker-compose exec node rm -rf node_modules/.vite
   ```

### Debugging

1. **Application Logs**:
   ```bash
   # Laravel logs
   docker-compose exec app tail -f storage/logs/laravel.log
   
   # PHP-FPM logs
   docker-compose logs -f app
   ```

2. **Database Debugging**:
   ```bash
   # Enable query logging
   docker-compose exec app php artisan db:monitor
   ```

3. **Performance Monitoring**:
   Access Laravel Telescope at: http://localhost:8080/telescope

## Maintenance

### Backup Database
```bash
# Create backup
docker-compose exec mysql mysqldump -u redo_user -p redo > backup.sql

# Restore backup
docker-compose exec -T mysql mysql -u redo_user -p redo < backup.sql
```

### Update Dependencies
```bash
# Update Composer dependencies
docker-compose exec app composer update

# Update Node.js dependencies
docker-compose exec node npm update
```

### Clean Up
```bash
# Remove containers and volumes
docker-compose down -v

# Remove images
docker-compose down --rmi all

# Clean up Docker system
docker system prune -a
```

## Security Considerations

1. **Environment Variables**: Never commit `.env` files with sensitive data
2. **Database Passwords**: Use strong passwords in production
3. **Redis Security**: Enable Redis authentication in production
4. **SSL/TLS**: Always use HTTPS in production
5. **Firewall**: Restrict database and Redis ports in production

## Performance Optimization

1. **OPcache**: Enabled by default in PHP configuration
2. **Redis**: Used for caching, sessions, and queues
3. **Asset Optimization**: Vite handles asset bundling and optimization
4. **Database**: MySQL configured with optimized settings

## Support

For issues specific to the Docker setup, check:
1. Docker logs: `docker-compose logs`
2. Container status: `docker-compose ps`
3. Resource usage: `docker stats`

For Laravel-specific issues, refer to the main application documentation.
