COMPOSER_COMMAND=composer install --no-interaction --no-scripts
ifeq ($(APP_ENV), prod)
	COMPOSER_COMMAND=composer install --no-interaction --no-scripts --no-dev --optimize-autoloader --classmap-authoritative
endif
CHECK_FILES='.'

default: test-local-init

# START Jenkins CI required targets:
code-check:
	echo " > Checking code... "
	php-cs-check-changed "$(CHECK_FILES)"
	make openapi # checks if openapi files are still valid when regenerated

deps:
	echo " > Installing dependencies... "
	$(COMPOSER_COMMAND)

test-ci: test-migrations test-unit test-integration test-functional
# END Jenkins CI required targets

clean:
	echo " > Cleaning... "
	rm -rf ./var/cache
	rm -rf ./var/logs
	rm -rf ./var/tests/_output

test-migrations:
	echo " > Running DB migrations... "
	sleep 15 # give time to the mysql server to be ready
	./vendor/bin/phinx migrate

test-local-migrations:
	bin/docker/app make test-migrations

test-seed:
	echo " > Running DB seeds... "
	vendor/bin/phinx seed:run

test-functional:
	echo " > Running functional tests... "
	./vendor/bin/behat --stop-on-failure --strict --format progress -vvv

test-integration:
	make test-seed
	echo " > Setting up PHPUnit... "
	bin/phpunit install -q -n
	echo " > Running PHPUnit... "
	bin/phpunit --stop-on-failure -vvv

test-unit:
	echo " > Running unit tests... "
	vendor/bin/phpspec run --stop-on-failure -vvv

openapi:
	echo " > Running OpenAPI Specification generator + validator... "
	composer dumpautoload
	bin/console cache:clear
	# full (no x-group filtering)
	bin/openapi-generate full
	bin/docker/app swagger-cli validate ./docs/openapi/paella-core-openapi-full.yaml
	# public (the one in developers.billie.io)
	bin/openapi-generate public
	bin/docker/app swagger-cli validate ./docs/openapi/paella-core-openapi-public.yaml
	# checkout-client (internal)
	bin/openapi-generate checkout-client --with-extra-config
	bin/docker/app swagger-cli validate ./docs/openapi/paella-core-openapi-checkout-client.yaml

test-local-init:
	echo " > Recreating containers and starting... "
	if [[ ! -f ./docker-compose.override.yml ]]; then \
  		cp docker-compose.override.dist.yml docker-compose.override.yml; \
  	fi; \
	docker-compose up -d --build --force-recreate --remove-orphans
	if [[ ! -d ./vendor/composer ]]; then \
  		bin/docker/app make deps; \
  		rm -rf ./bin/.phpunit; \
  		bin/docker/app ./bin/phpunit --version; \
  	else \
  		bin/docker/app composer dumpautoload; \
  	fi;

test-local: test-local-init test-local-migrations
	bin/docker/app make openapi
	bin/docker/app make test-unit
	bin/docker/app make test-integration
	bin/docker/app make test-functional

pre-commit-hook:
	vendor/bin/cs-fix-staged-files
	make openapi

$(V).SILENT:
.PHONY: docs tests test
