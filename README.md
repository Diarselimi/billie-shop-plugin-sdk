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

Then you only need to run `make test`.

You can also run anything inside the php-fpm container using the helper script: `./bin/docker-app-exec`, and
you will be running under the same PHP version as in production. Examples: 

- `./bin/docker-app-exec bin/console list`
- `./bin/docker-app-exec vendor/bin/behat bdd/specs/foobar.feature`

... but first you need the container to be running: `docker-compose up -d app`.
