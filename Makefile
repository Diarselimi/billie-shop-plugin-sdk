API_DOCS_VERSION := $(shell date +"%Y.%m")

default:docker-reload

# START Jenkins provisioner required targets:
docker-ftest:test
docker-ftest-clean:
	docker-compose down
# END Jenkins provisioner required targets

docker-reload:
	docker-compose down
	docker-compose up -d

test:
	make docker-reload
	make docs
	make test-migrate
	make test-phpspec
	make test-phpunit
	make test-behat

test-migrate:
	echo " > Running DB migrations... "
	sleep 15 # give time to the mysql server to be ready
	./bin/docker-app-exec vendor/bin/phinx migrate

test-phpspec:
	echo " > Running PHPSpec... "
	./bin/docker-app-exec vendor/bin/phpspec run --stop-on-failure

test-phpunit:
	echo " > Running DB seeds... "
	./bin/docker-app-exec vendor/bin/phinx seed:run
	echo " > Setting up PHPUnit... "
	./bin/docker-app-exec ./.ci/scripts/install-composer.sh
	./bin/docker-app-exec bin/phpunit install -q -n
	echo " > Running PHPUnit... "
	./bin/docker-app-exec bin/phpunit --stop-on-failure

test-behat:
	echo " > Running Behat... "
	./bin/docker-app-exec vendor/bin/behat --stop-on-failure --strict --format progress -vvv

docs:
	echo " > Running OpenAPI Spec generator... "
	./bin/docker-app-exec bin/generate-api-docs "v${API_DOCS_VERSION}"
	echo " > Running OpenAPI Spec validator... "
	./.ci/scripts/docker-run --rm swagger-cli validate /openapi/paella-openapi-full.yaml
	./.ci/scripts/docker-run --rm swagger-cli validate /openapi/paella-openapi-public.yaml

$(V).SILENT:
.PHONY: docs
