DOCKER_COMPOSE = docker-compose

EXEC_PHP       = $(DOCKER_COMPOSE) exec -T php /entrypoint
EXEC_NODE      = $(DOCKER_COMPOSE) exec -T node /entrypoint

SYMFONY        = $(EXEC_PHP) bin/console
SYMFONY_TEST   = $(EXEC_PHP) bin/console --env=test
COMPOSER       = $(EXEC_PHP) composer
YARN           = $(EXEC_NODE) yarn
QA             = docker run --rm -v `pwd`:/project -w /project jakzal/phpqa:1.79.0-php8.1-alpine

##
## File dependencies
## -----------------
##
composer.lock: composer.json ## Update Composer dependencies and lockfile
	$(COMPOSER) update

vendor: composer.lock ## Install Composer dependencies
	$(COMPOSER) install --no-scripts

yarn.lock: package.json ## Update YARN dependencies and lockfile
	$(YARN) upgrade

node_modules: yarn.lock ## Install YARN dependencies
	$(YARN) install

##
## Project
## -------
##

install: start database assets ## Install everything except Docker things

clean: stop ## Remove dependencies and built resources
	rm -Rf web/assets
	rm -Rf node_modules
	rm -Rf vendor
	rm -Rf public/assets
	rm -Rf public/bundles
	rm -Rf public/build
	rm -Rf public/media
	rm -Rf var/cache/*

assets: vendor node_modules ## Build assets
	$(YARN) dev

assets-prod: vendor node_modules ## Build prod assets
	$(YARN) build

watch-assets: assets ## Build assets and watch for changes
	$(YARN) watch

##
## Docker
## ------
##

build:
	$(DOCKER_COMPOSE) pull --quiet --ignore-pull-failures
	$(DOCKER_COMPOSE) build --pull

kill:
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

start: ## Start the project
	$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate

stop: ## Stop the project
	$(DOCKER_COMPOSE) stop

restart: ## Restart the project
	$(DOCKER_COMPOSE) stop
	$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate

##
## Utils : Database
## -----
##

build-db: vendor ## Build the database
	$(SYMFONY) doctrine:database:drop --if-exists --force
	$(SYMFONY) doctrine:database:create --if-not-exists
	$(SYMFONY) doctrine:migrations:migrate --no-interaction

build-db-test: vendor ## Build the database
	$(SYMFONY_TEST) doctrine:database:drop --if-exists --force
	$(SYMFONY_TEST) doctrine:database:create --if-not-exists
	$(SYMFONY_TEST) doctrine:migrations:migrate --no-interaction

migrate:
	$(SYMFONY) doctrine:migrations:migrate --no-interaction

database: build-db load-data

database-test: build-db-test load-fixtures-test

load-data: vendor load-fixtures ## Load the fixtures and city data

load-fixtures: build-db ## Load the fixtures
	$(SYMFONY) doctrine:fixture:load --no-interaction

load-fixtures-test: build-db-test ## Load the fixtures
	$(SYMFONY_TEST) doctrine:fixture:load --no-interaction

generate-db-diff: build-db ## Generate a migration by comparing your current database to your mapping information
	$(SYMFONY) doctrine:migrations:diff

##
## Utils : Misc
## -----
##
shell: ## Enter in web container
	$(DOCKER_COMPOSE) exec php gosu foo sh

cache-clear:
	$(SYMFONY) cache:clear

extract-translation: vendor ## Extract translations
	$(SYMFONY) translation:extract fr --force

##
## Tests
## -----
##

test: behat php-cs-fixer phpstan ## Run all tests

doctrine-schema-validate: database ## Run doctrine:schema:validate
	$(SYMFONY) doctrine:schema:validate

behat: assets database-test behat-keep-db ## Run Behat tests

behat-keep-db: ## Run Behat tests without resetting database
	mkdir -p var/fails
	$(EXEC_PHP) vendor/bin/behat --strict --verbose --colors

behat-single: ## Run a single Behat test file (option: `FILE=path/to/test.feature`)
	$(EXEC_PHP) vendor/bin/behat $(FILE) --strict --verbose --colors

php-cs-fixer: ## Run PHP-CS fixer
	$(QA) ./ci/src/php-cs-fixer

phpstan: vendor ## Run PHPStan
	$(QA) phpstan analyse --level 4 src templates public

phpunit: vendor ## Run PHPUnit
	$(EXEC_PHP) bin/phpunit

# For GitLab's CI

bin/selenium-server-standalone-2.53.0.jar: ## Download Selenium
	wget "https://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.0.jar" --output-document="$@" --quiet

.PHONY: install clean
.PHONY: build kill start stop restart
.PHONY: assets build-db database load-data load-fixtures generate-db-diff
.PHONY: extract-translation import-products
.PHONY: test doctrine-schema-validate behat behat-keep-db behat-single php-cs-fixer phpstan

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
