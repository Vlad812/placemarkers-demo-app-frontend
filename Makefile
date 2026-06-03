APP_FRONTEND_DIR := $(patsubst %/,%,$(dir $(abspath $(lastword $(MAKEFILE_LIST)))))
PROJECT_ROOT := $(abspath $(APP_FRONTEND_DIR)/../..)
include $(PROJECT_ROOT)/config.mk

.PHONY: app-frontend-init app-frontend-build app-frontend-up app-frontend-down

app-frontend-init:
	@echo "🏗️  Установка bootstrap и JQ..."
	docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) up app-frontend-node-pm --build -d

	@echo "⏳ Ждём готовности контейнера app-frontend-node-pm…"
	@until docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) exec app-frontend-node-pm sh -c "echo 'Контейнер готов'" > /dev/null 2>&1; do \
		echo "   Контейнер ещё не готов... ждём 2 секунды."; \
		sleep 2; \
	done

	@echo "📦 npm Устанавливаем зависимости..."
	docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) exec app-frontend-node-pm sh -c "npm install && npm run copy-bootstrap"

	docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) down -v app-frontend-node-pm

	@echo "✅ Готово!"
	@echo "composer зависимости"
	docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) run --rm app-frontend-pm-php-cli composer install --optimize-autoloader --no-interaction
	@echo 'Обновляю автозагрузчик Composer...';
	docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) run --rm app-frontend-pm-php-cli composer dump-autoload --optimize;

app-frontend-build:
	@echo build app-frontend
	docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) build

app-frontend-up:
	@echo up app-frontend
	docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) up -d app-frontend-pm

app-frontend-down:
	@echo down app-frontend
	docker compose -f $(APP_FRONTEND_DIR)/docker-compose.yaml -p $(PROJECT_GROUP_MAIN_SERVICE) down -v
