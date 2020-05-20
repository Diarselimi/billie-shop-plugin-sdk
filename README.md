# Paella Core

The heart of Billie Pay-After-Delivery (PaD).

## Order State Workflow

![orders_workflow](src/Resources/docs/orders-workflow.png)

## API Documentation

More info here:

https://ozean12.atlassian.net/wiki/spaces/PAELLA/pages/954173858/Paella+API#Documentation

## Maintenance Scripts
### Update Workflow Diagrams
Requirements: `graphviz`.

```bash
bin/generate-workflow-diagram
```

The generated images will be stored under the `docs` folder as PNG files.

### Update API Documentation
Requirements: `git`, `openapi-generator`.

You should run this command on your host, not inside the Docker container.
To get `openapi-generator`, run `brew install openapi-generator` first.

Usage:

```bash
bin/generate-api-docs [API_VERSION]
```

Generates all API specification variants at the same time (standard, dashboard, support, etc.).

The API_VERSION argument is optional, by default it uses the latest repository tag (found locally) if possible,
or you can pass it manually.

The generated YAML files live under the `docs/openapi` folder.


### Running tests

In order to run tests locally, first you need to configure your laptop 
[as described in this guide](https://ozean12.atlassian.net/wiki/spaces/INFRA/pages/868385662/Local+Development).

For running tests you need to be able pull images from our AWS Docker registry, to be able
to use the same PHP image as in production.

#### All tests

This command will start the docker containers, run migrations and run all test suites:
```bash
make test
```

#### Behat

To run only behat tests, you need to first start the containers and run the migrations (if you didn't):
```bash
make test-cleanup
make test-up
make test-migrate
```

Then run:
```bash
make test-behat
```

#### PHPSpec

To run only phpspec tests, you need to first start the containers (if you didn't):
```bash
make test-cleanup
make test-up
```

Then run:
```bash
make test-phpspec
```

#### Running other commands

You can also run anything inside the running php-fpm container using the helper script: `./bin/docker-app-exec`.
Examples: 

- `./bin/docker-app-exec bin/console list`
- `./bin/docker-app-exec vendor/bin/behat bdd/specs/foobar.feature`
