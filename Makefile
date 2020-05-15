default:
	docker-compose build

# START provisioner required targets:
docker-ftest:test
docker-ftest-clean:test-clean
# END provisioner required targets

test:
	docker-compose down
	docker-compose up -d app
	echo " > Running DB migrations... "
	sleep 20 # give time to the mysql server to be ready
	./bin/docker-app-exec vendor/bin/phinx migrate
	echo " > Running PHPSpec... "
	./bin/docker-app-exec vendor/bin/phpspec run --stop-on-failure
	echo " > Running Behat... "
	./bin/docker-app-exec vendor/bin/behat --stop-on-failure --format=progress
	docker-compose down

test-clean:
	docker-compose down

$(V).SILENT:
