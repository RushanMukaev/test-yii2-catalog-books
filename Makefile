PROJECT_NAME = yii2-books
COMPOSE = docker-compose -p $(PROJECT_NAME)
COMPOSE_EXEC = $(COMPOSE) exec php
PHP_EXEC = $(COMPOSE_EXEC) php

.PHONY: help
help:
	@echo "Available commands:"
	@echo "  make up             - Start containers"
	@echo "  make down           - Stop containers"
	@echo "  make restart        - Restart containers"
	@echo "  make build          - Build containers"
	@echo "  make install        - Install dependencies"
	@echo "  make migrate        - Run migrations"
	@echo "  make shell          - Enter PHP container"
	@echo "  make db             - Enter MySQL console"
	@echo "  make logs           - Show logs"

.PHONY: up
up:
	$(COMPOSE) up -d
	@echo "Application: http://localhost:8081"

.PHONY: down
down:
	$(COMPOSE) down

.PHONY: restart
restart:
	$(COMPOSE) restart

.PHONY: build
build:
	$(COMPOSE) build --no-cache

.PHONY: install
install:
	$(COMPOSE_EXEC) composer install --prefer-dist --no-interaction

.PHONY: migrate
migrate:
	$(PHP_EXEC) yii migrate --interactive=0

.PHONY: shell
shell:
	$(COMPOSE_EXEC) sh

.PHONY: db
db:
	$(COMPOSE) exec db mysql -u yii2 -pyii2 yii2_books

.PHONY: logs
logs:
	$(COMPOSE) logs -f

.DEFAULT_GOAL := help