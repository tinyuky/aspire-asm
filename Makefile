DOCKER_COMPOSE = docker-compose
.DEFAULT_GOAL := help
APP_CONTAINER = laravel-app
DB_CONTAINER = laravel-mysql

init: build up

build:
	$(DOCKER_COMPOSE) build

up:
	$(DOCKER_COMPOSE) up -d

composer-install:
	$(DOCKER_COMPOSE) run --rm app composer install

migrate:
	$(DOCKER_COMPOSE) run --rm app php artisan migrate

seed:
	$(DOCKER_COMPOSE) run --rm app php artisan db:seed

down:
	$(DOCKER_COMPOSE) down

clean:
	$(DOCKER_COMPOSE) down -v

help:
	@echo "Available commands:"
	@echo "  make init              Initialize the Laravel project with Docker Compose"
	@echo "  make build             Build the Docker containers"
	@echo "  make up                Bring up the Docker containers"
	@echo "  make composer-install  Install project dependencies using Composer"
	@echo "  make migrate           Run database migrations"
	@echo "  make seed              Seed the database with sample data (if needed)"
	@echo "  make down              Stop and remove the Docker containers"
	@echo "  make clean             Clean up Docker volumes (removes the database data)"
	@echo "  make help              Show this help message"
