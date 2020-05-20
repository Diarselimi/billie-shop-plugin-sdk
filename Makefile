default:
	docker-compose build

# START Jenkins provisioner required targets:
# docker-ftest:test # behat tests temporarly DISABLED on Jenkins:
docker-ftest:
	make test-reload
	make test-migrate
	make test-phpspec
	make test-down

docker-ftest-clean:test-down
# END Jenkins provisioner required targets

test-reload:
	docker-compose down
	docker-compose up -d app

test-down:
	docker-compose down

test: # all test suites
	make test-reload
	make test-migrate
	make test-phpspec
	make test-behat

test-migrate:
	echo " > Running DB migrations... "
	sleep 20 # give time to the mysql server to be ready
	./bin/docker-app-exec vendor/bin/phinx migrate

test-phpspec:
	echo " > Running PHPSpec... "
	./bin/docker-app-exec vendor/bin/phpspec run --stop-on-failure

test-behat:
	echo " > Running Behat... "
	./bin/docker-app-exec vendor/bin/behat --stop-on-failure --strict --format progress -v


$(V).SILENT:
