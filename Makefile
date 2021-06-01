DOCKER_COMPOSE?=docker-compose
RUN=$(DOCKER_COMPOSE) run --rm app
EXEC?=$(DOCKER_COMPOSE) exec app entrypoint.sh
COMPOSER=$(EXEC) composer
CONSOLE=$(EXEC) bin/console
PHPCSFIXER?=$(EXEC) php -d memory_limit=1024m vendor/bin/php-cs-fixer
BEHAT=$(EXEC) vendor/bin/behat
BEHAT_ARGS?=-vvv
PHPUNIT=$(EXEC) vendor/bin/phpunit
PHPUNIT_ARGS?=-v
DOCKER_FILES=$(shell find ./docker/dev/ -type f -name '*')

.DEFAULT_GOAL := help
.PHONY: help start stop reset db db-diff db-diff-dump db-migrate db-rollback db-load watch clear clean test tu tf tj lint ls ly lt
.PHONY: lj build up perm deps cc phpcs phpcsfix phplint tty tfp tfp-rabbitmq tfp-db test-behat test-phpunit-functional
.PHONY: wait-for-rabbitmq wait-for-db security-check rm-docker-dev.lock

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Project setup
##---------------------------------------------------------------------------

start: build up config/packages/assets_version.yaml db rabbitmq-fabric public/built assets-amp var/public.key perm  ## Install and start the project

start-mac: build up config/packages/assets_version.yaml db rabbitmq-fabric web-built-mac var/public.key perm  ## Install and start the project

stop:                                                                                                  ## Remove docker containers
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) rm -v --force

reset: stop rm-docker-dev.lock start

clear: perm rm-docker-dev.lock                                                                                             ## Remove all the cache, the logs, the sessions and the built assets
	-$(EXEC) rm -rf var/cache/*
	-$(EXEC) rm -rf var/sessions/*
	-$(EXEC) rm -rf supervisord.log supervisord.pid npm-debug.log .tmp
	-$(CONSOLE) redis:flushall -n
	rm -rf var/logs/*
	rm -rf public/built
	rm var/.php_cs.cache

clean: clear                                                                                           ## Clear and remove dependencies
	rm -rf vendor node_modules

cc:                                                                                                    ## Clear the cache in dev env
	$(CONSOLE) cache:clear --no-warmup
	$(CONSOLE) cache:warmup

tty:                                                                                                   ## Run app container in interactive mode
	$(RUN) /bin/bash

var/public.key: var/private.key                                                                        ## Generate the public key
	$(EXEC) openssl rsa -in var/private.key -pubout -out var/public.key

var/private.key:                                                                                       ## Generate the private key
	$(EXEC) openssl genrsa -out var/private.key 1024

wait-for-rabbitmq:
	$(EXEC) php -r "set_time_limit(60);for(;;){if(@fsockopen('rabbitmq',5672)){break;}echo \"Waiting for RabbitMQ\n\";sleep(1);}"

rabbitmq-fabric: wait-for-rabbitmq
	$(CONSOLE) rabbitmq:setup-fabric

##
## Database
##---------------------------------------------------------------------------

wait-for-db:
	$(EXEC) php -r "set_time_limit(60);for(;;){if(@fsockopen('db',5432)){break;}echo \"Waiting for PGSQL\n\";sleep(1);}"

db: vendor wait-for-db                                                                                 ## Reset the database and load fixtures
	$(CONSOLE) doctrine:database:drop --force --if-exists --no-debug
	$(CONSOLE) doctrine:database:create --if-not-exists --no-debug
	$(CONSOLE) doctrine:migrations:migrate -n --no-debug
	$(CONSOLE) doctrine:fixtures:load -n

db-diff: vendor wait-for-db                                                                            ## Generate a migration by comparing your current database to your mapping information
	$(CONSOLE) doctrine:migration:diff --formatted --no-debug

db-diff-dump: vendor wait-for-db                                                                       ## Generate a migration by comparing your current database to your mapping information and display it in console
	$(CONSOLE) doctrine:schema:update --dump-sql

db-migrate: vendor wait-for-db                                                                         ## Migrate database schema to the latest available version
	$(CONSOLE) doctrine:migration:migrate -n --no-debug

db-rollback: vendor wait-for-db                                                                        ## Rollback the latest executed migration
	$(CONSOLE) doctrine:migration:migrate prev -n --no-debug

db-load: vendor wait-for-db                                                                            ## Reset the database fixtures
	$(CONSOLE) doctrine:fixtures:load -n

db-validate: vendor wait-for-db                                                                        ## Check the ORM mapping
	$(CONSOLE) doctrine:schema:validate --no-debug


##
## Assets
##---------------------------------------------------------------------------

watch: node_modules                                                                                    ## Watch the assets and build their development version on change
	$(EXEC) yarn watch

watch-mac:
	yarn watch

assets: node_modules                                                                                   ## Build the development version of the assets
	$(EXEC) yarn build-dev

assets-prod: node_modules                                                                              ## Build the production version of the assets
	$(EXEC) yarn build-prod

assets-amp: node_modules                                                                               ## Build the production version of the AMP CSS
	$(EXEC) yarn build-amp

assets-apps: node_modules                                                                              ## Build the production version of the React apps
	$(EXEC) yarn build-apps


##
## Tests
##---------------------------------------------------------------------------

test: tu tf tj                                                                                         ## Run the PHP and the Javascript tests

test-behat:                                                                                            ## Run behat tests
	$(BEHAT) $(BEHAT_ARGS)

test-phpunit:                                                                                          ## Run phpunit tests
	$(PHPUNIT) $(PHPUNIT_ARGS)

test-debug:                                                                                            ## Run tests with debug group/tags
	$(PHPUNIT) -vvv --group debug
	$(BEHAT) -vvv --tags debug

test-phpunit-functional:                                                                               ## Run phpunit fonctional tests
	$(PHPUNIT) --group functional

tu: vendor config/packages/assets_version.yaml                                                               ## Run the PHP unit tests
	$(PHPUNIT) --exclude-group functional

tf: tfp test-behat test-phpunit-functional                                                             ## Run the PHP functional tests

tfp: assets-amp assets-prod assets-apps vendor perm tfp-rabbitmq tfp-db                                ## Prepare the PHP functional tests

tfp-rabbitmq: wait-for-rabbitmq                                                                        ## Init RabbitMQ setup for tests
	$(DOCKER_COMPOSE) exec rabbitmq rabbitmqctl add_vhost /test || true
	$(DOCKER_COMPOSE) exec rabbitmq rabbitmqctl set_permissions -p /test guest ".*" ".*" ".*"
	$(CONSOLE) --env=test rabbitmq:setup-fabric

tfp-db: wait-for-db                                                                                    ## Init databases for tests
	$(EXEC) rm -rf /tmp/data.db || true
	$(CONSOLE) doctrine:database:drop --force --if-exists --env=test --no-debug
	$(CONSOLE) doctrine:database:create --env=test --no-debug
	$(CONSOLE) doctrine:migration:migrate -n --no-debug --env=test
	$(CONSOLE) doctrine:schema:validate --no-debug --env=test
	$(CONSOLE) doctrine:fixtures:load --env=test -n

tj: node_modules                                                                                       ## Run the Javascript tests
	$(EXEC) yarn test

lint: ls lj phpcs                                                                                ## Run lint on Twig, YAML, PHP and Javascript files

ls: ly lt lc phpstan                                                                                             ## Lint Symfony (Twig and YAML) files

ly:
	$(CONSOLE) lint:yaml config --parse-tags

lt:
	$(CONSOLE) lint:twig templates

lc:
	$(CONSOLE) lint:container

lj: node_modules                                                                                       ## Lint the Javascript to follow the convention
	$(EXEC) yarn lint

ljfix: node_modules                                                                                    ## Lint and try to fix the Javascript to follow the convention
	$(EXEC) yarn lint -- --fix

phpcs: vendor                                                                                          ## Lint PHP code
	$(PHPCSFIXER) fix --diff --dry-run --no-interaction -v

phpcsfix: vendor                                                                                       ## Lint and fix PHP code to follow the convention
	$(PHPCSFIXER) fix

phpstan: vendor
	$(EXEC) vendor/bin/phpstan analyse

phplint: phpcsfix phpstan

security-check: vendor                                                                                 ## Check for vulnerable dependencies
	$(EXEC) local-php-security-checker --path=/app


##
## Dependencies
##---------------------------------------------------------------------------

deps: vendor public/built                                                                                 ## Install the project PHP and JS dependencies

##


# Internal rules

build: docker-dev.lock

docker-dev.lock: $(DOCKER_FILES)
	$(DOCKER_COMPOSE) pull --ignore-pull-failures
	$(DOCKER_COMPOSE) build --force-rm --pull
	touch docker-dev.lock

rm-docker-dev.lock:
	rm -f docker-dev.lock

up:
	$(DOCKER_COMPOSE) up -d --remove-orphans

perm:
	$(EXEC) chmod -R 777 app/data/images app/data/files
	$(EXEC) chmod 660 var/public.key var/private.key

# Rules from files

vendor: vendor/autoload.php

vendor/autoload.php: composer.lock
	$(COMPOSER) install -n

composer.lock: composer.json
	@echo composer.lock is not up to date.

node_modules: yarn.lock
	$(EXEC) yarn install

yarn.lock: package.json
	@echo yarn.lock is not up to date.

public/built: front node_modules
	$(EXEC) yarn build-dev

web-built-mac:
	yarn install
	yarn build-dev

config/packages/assets_version.yaml: node_modules
	 $(EXEC) yarn build-prod
