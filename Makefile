COMPOSER_COMMAND=composer install --no-interaction --no-scripts
ifeq ($(APP_ENV), prod)
	COMPOSER_COMMAND=composer install --no-interaction --no-scripts --no-dev --optimize-autoloader --classmap-authoritative
endif
CHECK_FILES=.

default: local-start local-deps local-test-migrations
test: default local-openapi local-openapi-validation local-test

################	Jenkins CI required targets	###################
code-check:
	echo " > Checking code... "
	php-cs-check-changed "$(CHECK_FILES)"

deps:
	echo " > Installing dependencies... "
	$(COMPOSER_COMMAND)

test-ci: openapi-validation test-migrations test-unit test-integration test-functional
#################################################################

pre-commit-hook:
	docker-compose up -d app
	docker-compose exec -T app make openapi
	docker-compose exec -T app make openapi-validation
	docker-compose exec -T app vendor/bin/cs-fix-staged-files || echo "cs-fix: No changes in code."
cache:
	echo " > Cleaning and warming up caches... "
	composer dumpautoload
	rm -rf var/cache
	bin/console cache:warmup

clean:
	echo " > Cleaning up... "
	rm -rf ./var/cache
	rm -rf ./var/logs
	rm -rf ./var/tests/_output

openapi:
	echo " > Running OpenAPI Specification generator... "
	# full (no x-group filtering)
	bin/openapi-generate full
	# public (the one in developers.billie.io)
	bin/openapi-generate public
	# checkout-client (internal)
	bin/openapi-generate checkout-client --with-extra-config

openapi-validation:
	echo " > Running OpenAPI Specification validator... "
	for filename in docs/openapi/*.yaml; do \
		swagger-cli validate $${filename}; \
	done

test-migrations:
	echo " > Running DB migrations... "
	sleep 15 # give time to the mysql server to be ready
	./vendor/bin/phinx migrate

test-seed:
	echo " > Running DB seeds... "
	./vendor/bin/phinx seed:run

test-functional:
	echo " > Running functional tests... "
	./vendor/bin/behat --stop-on-failure --strict --format progress -vvv

test-integration: test-seed
	echo " > Running integration tests... "
	./vendor/bin/phpunit --stop-on-failure -vvv

test-unit:
	echo " > Running unit tests... "
	./vendor/bin/phpspec run --stop-on-failure -vvv

######################## Docker-wrapped targets #################################
local-start:
	if [[ ! -f ./docker-compose.override.yml ]]; then \
  		cp docker-compose.override.dist.yml docker-compose.override.yml; \
	fi; \
	echo " > Recreating containers and starting... "
	docker-compose up -d --build --force-recreate --remove-orphans

local-deps:
	bin/docker/app make deps

local-openapi:
	bin/docker/app make openapi

local-openapi-validation:
	bin/docker/app make openapi-validation

local-test-migrations:
	bin/docker/app make test-migrations

local-test-seed:
	bin/docker/app make test-seed

local-test-unit:
	bin/docker/app make test-unit

local-test-integration:
	bin/docker/app make test-integration

local-test-functional:
	bin/docker/app make test-functional

local-test: local-test-unit local-test-integration local-test-functional
###################################################################################

$(V).SILENT:
.PHONY: docs tests
