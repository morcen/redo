# Makefile for re:do Laravel Docker Setup
# Provides convenient shortcuts for Docker operations

.PHONY: help setup start stop restart logs build clean artisan deploy status backup

# Default target
help: ## Show this help message
	@echo "re:do Laravel Docker Commands"
	@echo "============================="
	@echo ""
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

setup: ## Initial setup of the application
	@./scripts/docker-setup.sh setup

start: ## Start all containers
	@./scripts/docker-setup.sh start

stop: ## Stop all containers
	@./scripts/docker-setup.sh stop

restart: ## Restart all containers
	@./scripts/docker-setup.sh restart

logs: ## View logs from all containers
	@./scripts/docker-setup.sh logs

build: ## Build all containers
	@./scripts/docker-setup.sh build

clean: ## Clean up containers, volumes, and images
	@./scripts/docker-setup.sh clean

# Laravel specific commands
artisan: ## Run Laravel artisan command (usage: make artisan cmd="migrate")
	@./scripts/docker-artisan.sh $(cmd)

migrate: ## Run database migrations
	@./scripts/docker-artisan.sh migrate

migrate-fresh: ## Fresh migration with seeding
	@./scripts/docker-artisan.sh migrate:fresh --seed

cache-clear: ## Clear all caches
	@./scripts/docker-artisan.sh cache:clear
	@./scripts/docker-artisan.sh config:clear
	@./scripts/docker-artisan.sh route:clear
	@./scripts/docker-artisan.sh view:clear

tinker: ## Start Laravel tinker
	@./scripts/docker-artisan.sh tinker

# Production commands
deploy: ## Deploy to production
	@./scripts/docker-deploy.sh deploy

prod-status: ## Show production status
	@./scripts/docker-deploy.sh status

prod-logs: ## Show production logs
	@./scripts/docker-deploy.sh logs

backup: ## Create backup
	@./scripts/docker-deploy.sh backup

# Development helpers
shell: ## Access application container shell
	@docker-compose exec app bash

mysql: ## Access MySQL CLI
	@docker-compose exec mysql mysql -u redo_user -p redo

redis: ## Access Redis CLI
	@docker-compose exec redis redis-cli

npm: ## Run npm command (usage: make npm cmd="install")
	@docker-compose exec vite npm $(cmd)

composer: ## Run composer command (usage: make composer cmd="install")
	@docker-compose exec app composer $(cmd)

# Testing
test: ## Run tests
	@./scripts/docker-artisan.sh test

test-coverage: ## Run tests with coverage
	@./scripts/docker-artisan.sh test --coverage-clover=coverage.xml --coverage-html=coverage-html --coverage-text=coverage.txt

test-coverage-report: ## Run tests with coverage and show report
	@./scripts/docker-artisan.sh test --coverage-clover=coverage.xml --coverage-html=coverage-html --coverage-text=coverage.txt
	@echo "Coverage report generated:"
	@echo "- XML: coverage.xml"
	@echo "- HTML: coverage-html/index.html"
	@echo "- Text: coverage.txt"

# Monitoring
ps: ## Show container status
	@docker-compose ps

stats: ## Show container resource usage
	@docker stats --no-stream

# Quick development workflow
dev: start ## Start development environment
	@echo "Development environment started!"
	@echo "Web App: http://localhost:8080"
	@echo "Vite: http://localhost:5173"

fresh: ## Fresh start with clean database
	@make stop
	@make start
	@sleep 10
	@make migrate-fresh
	@echo "Fresh environment ready!"
