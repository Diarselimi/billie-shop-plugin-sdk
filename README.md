# Paella Core

The heart of Billie Pay-After-Delivery (PaD).

## API Documentation

### Public Documentation

Public documentation refers to the parts of the PaD API that are exposed via the public API gateway and are meant to be consumed by our costumers.

#### Standard

General order creation and order management endpoints.

URL path: **`/api/v1/docs` or `/api/v1/docs/standard`**


- Production: [paella.billie.io/api/v1/docs](https://paella.billie.io/api/v1/docs)
- Development: [paella.test10.ozean12.com/api/v1/docs](https://paella.test10.ozean12.com/api/v1/docs)
- Local VM: [paella.dev.ozean12.com/api/v1/docs](http://paella.dev.ozean12.com/api/v1/docs)

#### Checkout

Server-side checkout session management (creation and confirmation), plus order management endpoints.

URL path: **`/api/v1/docs/checkout-server`**


- Production: [paella.billie.io/api/v1/docs/checkout-server](https://paella.billie.io/api/v1/docs/checkout-server)
- Development: [paella.test10.ozean12.com/api/v1/docs/checkout-server](https://paella.test10.ozean12.com/api/v1/docs/checkout-server)
- Local VM: [paella.dev.ozean12.com/api/v1/docs/checkout-server](http://paella.dev.ozean12.com/api/v1/docs/checkout-server)

### Private Documentation

Private API documentation refers to the parts of the PaD API that are only internal (therefore not available via the public API gateway) or those endpoints that are public but are not meant to be consumed by our costumers, but by our own front-end clients only (like the Merchants Dashboard).

> All public API documentation endpoints are also available in the private APIs, but without the `/api/v1/` part.

#### Full

Documentation for the entire Pad API (public and private).

URL path: **`/internal-docs` or `/internal-docs/full`**

- Production: [paella-private-api.billie.io/internal-docs](https://paella-private-api.billie.io/internal-docs)
- Development: [paella-core.test10.ozean12.com/internal-docs](https://paella-core.test10.ozean12.com/internal-docs)
- Local VM: [paella-core.dev.ozean12.com/internal-docs](http://paella-core.dev.ozean12.com/internal-docs)

#### Checkout Client

Documents the endpoints needed for the PaD Checkout Widget.

URL path: **`/internal-docs/checkout-client`**

- Production: [paella-private-api.billie.io/internal-docs/checkout-client](https://paella-private-api.billie.io/internal-docs/checkout-client)
- Development: [paella-core.test10.ozean12.com/internal-docs/checkout-client](https://paella-core.test10.ozean12.com/internal-docs/checkout-client)
- Local VM: [paella-core.dev.ozean12.com/internal-docs/checkout-client](http://paella-core.dev.ozean12.com/internal-docs/checkout-client)

#### Dashboard

Documents the endpoints that are needed for the PaD Merchants Dashboard.

URL path: **`/internal-docs/dashboard`**

- Production: [paella-private-api.billie.io/internal-docs/dashboard](https://paella-private-api.billie.io/internal-docs/dashboard)
- Development: [paella-core.test10.ozean12.com/internal-docs/dashboard](https://paella-core.test10.ozean12.com/internal-docs/dashboard)
- Local VM: [paella-core.dev.ozean12.com/internal-docs/dashboard](http://paella-core.dev.ozean12.com/internal-docs/dashboard)


#### Support

Documents the endpoints that are of most interest
for manual support.

URL path: **`/internal-docs/support`**

- Production: [paella-private-api.billie.io/internal-docs/support](https://paella-private-api.billie.io/internal-docs/support)
- Development: [paella-core.test10.ozean12.com/internal-docs/support](https://paella-core.test10.ozean12.com/internal-docs/support)
- Local VM: [paella-core.dev.ozean12.com/internal-docs/support](http://paella-core.dev.ozean12.com/internal-docs/support)


#### Salesforce

Documents the endpoints that are of most interest
for Salesforce integration with PaD.

URL path: **`/internal-docs/salesforce`**

- Production: [paella-private-api.billie.io/internal-docs/salesforce](https://paella-private-api.billie.io/internal-docs/salesforce)
- Development: [paella-core.test10.ozean12.com/internal-docs/salesforce](https://paella-core.test10.ozean12.com/internal-docs/salesforce)
- Local VM: [paella-core.dev.ozean12.com/internal-docs/salesforce](http://paella-core.dev.ozean12.com/internal-docs/salesforce)


## Maintenance Scripts
### Update Order Workflow Diagram
Requirements: `graphviz`.

```bash
bin/generate-workflow-diagram
```

The generated image will be stored under the `docs` folder as SVG and PNG files.

![orders_workflow](src/Resources/docs/orders-workflow.png).

### Update API Documentation
Requirements: `git`, `openapi-generator`.
You should run this command on your host, so not inside the Docker container. Run `brew install openapi-generator` first.

Usage:

```bash
bin/generate-api-docs [API_VERSION]
```

Generates all API specification variants at the same time (standard, dashboard, support, etc.).

The API_VERSION argument is optional, by default it uses the latest repository tag if possible,
or you can pass it manually. Examples:

```bash
# Automatic version (latest local tag + current commit hash)
bin/generate-api-docs
```

```bash
# Latest local tag + current commit hash (same as default)
bin/generate-api-docs $(git describe --tags 2> /dev/null)
```

```bash
# Latest local tag, without any commit hash
bin/generate-api-docs $(git describe --tags --abbrev=0 2> /dev/null)
```

```bash
# Manual
bin/generate-api-docs "2019.1.72"
```

The generated YAML files live under the `docs/openapi` folder.
