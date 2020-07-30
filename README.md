# Paella Core

The heart of Billie Pay-After-Delivery (PaD).

## Install

How to install paella-core locally:

1. Clone this repository and `cd` to the folder. Make sure you have Docker installed.

2. Configure your laptop [as described in this guide](https://ozean12.atlassian.net/wiki/spaces/INFRA/pages/868385662/Local+Development)
if you haven't done it yet.
   
3. Have a [Github token](https://github.com/settings/tokens) set in your local copy of 
[`docker-compose.override.dist.yml`](docker-compose.override.dist.yml), named `docker-compose.override.yml`,
to be able to install private repositories.

4. Run `make` or `make install`. It will build and freshly start all containers, running composer install 
and all migrations. If you only wish a fresh container (re)start and nothing else, use `make start` instead.

You have many helpers to use dockerized versions of some tools, under this directory: `bin/docker/*`.

## Order State Workflow

![orders_workflow](src/Resources/docs/orders-workflow.png)

## API Documentation

More info here:

https://ozean12.atlassian.net/wiki/spaces/PAELLA/pages/954173858/Paella+API#Documentation

## Maintenance Scripts

To run any command involving `make`, first make sure that your containers are running,
like mentioned in the "install" section.

### Update Workflow Diagrams
Requirements: `graphviz`.

```bash
bin/generate-workflow-diagram
```

The generated images will be stored under the `docs` folder as PNG files.

Every time there is a change in the state machines (e.g. Symfony Workflow for orders),
this command needs to be run to update the diagrams.

### Update API Documentation

Generates and validates all OpenAPI specification groups at once (for public and internal docs).
The generated YAML files live under the `docs/openapi` folder.

Usage:

```bash
make docs
```

### Migrations

We use Phinx for migrations, you can list the available commands by running:
```bash
./bin/docker/app vendor/bin/phinx
```

## Tests

### Test types

We have 3 test types:
* Functional
* Integration
* Unit

We implement the testing pyramid principle which means we should have many unit tests, some integration tests and little functional tests.
When you want to test something you should test it as low level as possible. This drastically increases speed, setup time and debug time.
Some examples:
* You want to test that the sorting of a DB table works. This can be tested with a functional test but we shouldn't do that, because we can also test it with an integration test.
* You want to test that postal code validation works. This can be tested with a functional or an integration test but we shouldn't do that, because we can also test it with a unit test.

#### Functional tests

* How to write them: Behat
* Where to write them: bdd/features/
* What do they test: the whole system from start to finish, e.g. an API call, RabbitMQ consumer or executing a console command

#### Integration tests

* How to write them: PHPUnit
* Where to write them: tests/Integration/
* What do they test: how different components work together, but not the whole system from start to finish: e.g. use case using other components or a repository method which interacts with the database

#### Unit tests

* How to write them: PHPSpec
* Where to write them: bdd/spec/
* What do they test: a single class method in isolation

### Running tests

#### All tests

This command will start the docker containers, run migrations and run all test suites,
including OpenAPI spec validation.
This is useful when you want to freshly test everything in one run.
Used by Jenkins.

Usage:
```bash
make test
```

#### Behat

Usage:
```bash
make test-behat
```

Running behat directly with custom arguments:
```bash
./bin/docker/app vendor/bin/behat -vvv --stop-on-failure bdd/specs/foobar.feature
```

#### PHPUnit
Usage:
```bash
make test-phpunit
```

Running phpunit directly with custom arguments:
```bash
./bin/docker/app bin/phpunit tests/MyTestCase.php
```

#### PHPSpec

Usage:
```bash
make test-phpspec
```

Running phpspec directly with custom arguments:
```bash
./bin/docker/app vendor/bin/phpspec run -vvv --stop-on-failure
```

#### Running other commands

You can also run anything else inside the containers using the helper scripts, for example:

-  `./bin/docker/app php -v`
-  `./bin/docker/console cache:clear` same as: `./bin/docker/app bin/console cache:clear`
-  `./bin/docker/composer dumpautoload`
