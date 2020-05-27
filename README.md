# Paella Core

The heart of Billie Pay-After-Delivery (PaD).

## Order State Workflow

![orders_workflow](src/Resources/docs/orders-workflow.png)

## API Documentation

More info here:

https://ozean12.atlassian.net/wiki/spaces/PAELLA/pages/954173858/Paella+API#Documentation

## Maintenance Scripts

Prerequisites: In order to run the commands involving `make` locally, first you need to configure your laptop 
[as described in this guide](https://ozean12.atlassian.net/wiki/spaces/INFRA/pages/868385662/Local+Development).

You need to be able pull images from our AWS Docker registry, 
so you always use the same PHP image as in production.

To run `make` scripts, first make sure that your containers are running with `make docker-reload`.
Some of the targets like `make test-behat` also requires the DB migrations to be ready (`make test-migrate`).

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

### Running tests

#### All tests

This command will start the docker containers, run migrations and run all test suites,
including OpenAPI spec validation:

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
./bin/docker-app-exec vendor/bin/behat bdd/specs/foobar.feature
```

#### PHPSpec

Usage:
```bash
make test-phpspec
```

Running phpspec directly with custom arguments:
```bash
./bin/docker-app-exec vendor/bin/phpspec run -vvv
```

#### Running other commands

You can also run anything inside the running php-fpm container using the helper script: `./bin/docker-app-exec`.
Example: `./bin/docker-app-exec bin/console list`.
