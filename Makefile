default:install

# START Jenkins provisioner required targets:
docker-ftest:test
docker-ftest-clean:
	docker-compose down
# END Jenkins provisioner required targets

install:
	# A valid COMPOSER_AUTH env var is required in the app container for installing private repos.
	# Set it in your docker-compose.override.yml
	docker-compose down
	docker-compose build # rebuild in case of Dockerfile changes
	docker-compose up -d
	bin/docker/composer install --ignore-platform-reqs --no-interaction
	bin/docker/composer dumpautoload
	make clear-cache
	make test-migrate

clear-cache:
	rm -rf var/cache
	bin/docker/app bin/console cache:warmup

start:
	docker-compose down
	docker-compose up -d

test:
	make start
	make docs
	make test-migrate
	make test-phpspec
	make test-phpunit
	make test-behat

docs:
	echo " > Running OpenAPI Specification generator + validator... "
	make clear-cache
	# full (internal)
	bin/docker/app bin/generate-api-docs full
	bin/docker/run-rm swagger-cli validate /openapi/paella-openapi-full.yaml
	# public (the one in developers.billie.io)
	bin/docker/app bin/generate-api-docs public
	bin/docker/run-rm swagger-cli validate /openapi/paella-openapi-public.yaml
	# checkout-client (internal)
	bin/docker/app bin/generate-api-docs checkout-client --with-extra-config
	bin/docker/run-rm swagger-cli validate /openapi/paella-openapi-checkout-client.yaml

test-migrate:
	echo " > Running DB migrations... "
	sleep 15 # give time to the mysql server to be ready
	bin/docker/app vendor/bin/phinx migrate

test-phpspec:
	echo " > Running PHPSpec... "
	bin/docker/app vendor/bin/phpspec run --stop-on-failure -vvv

test-seed:
	echo " > Running DB seeds... "
	bin/docker/app vendor/bin/phinx seed:run

test-phpunit:
	make test-seed
	echo " > Setting up PHPUnit... "
	bin/docker/app ./.ci/scripts/install-composer.sh
	bin/docker/app bin/phpunit install -q -n
	echo " > Running PHPUnit... "
	bin/docker/app bin/phpunit --stop-on-failure -vvv

test-behat:
	echo " > Running Behat... "
	bin/docker/app vendor/bin/behat --stop-on-failure --strict --format progress -vvv

$(V).SILENT:
.PHONY: docs
