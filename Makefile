.PHONY: dev.up dev.up.build dev.down dev.console dev.composer-install dev.phpstan test dev.migrate

# Check mandatory tools
executables = docker docker-compose
K := $(foreach exec, $(executables), $(if $(shell which $(exec)),imposible_string_086676774066,$(error "'$(exec)' is not installed. Install it or check $$PATH")))

# Commands
dev.up:
	@docker compose up -d

dev.up.build:
	@docker compose up --build -d
	$(MAKE) dev.composer-install
	$(MAKE) dev.migrate

dev.down:
	@docker compose down

dev.restart:
	$(MAKE) dev.down
	$(MAKE) dev.up

dev.console:
	@docker compose exec -it backend-cli zsh

dev.run:
	@docker compose exec -it backend-cli bin/app.php

dev.composer-install:
	@docker compose exec backend-cli composer install

dev.migrate:
	@docker compose exec backend-cli php bin/migration.php

dev.phpstan:
	@docker compose exec -t backend-cli vendor/bin/phpstan analyse src/ tests/

test:
	@docker compose exec -t backend-cli vendor/bin/phpunit --display-deprecations tests/
