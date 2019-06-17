# Paella Core

The heart of Billie Pay-After-Delivery (PaD).

## API Documentation

### Generation
Requirements: `git` and `tar` binaries and `redoc-cli` npm package installed globally.

Usage:

```bash
bin/redoc-bundle public && bin/redoc-bundle private
```

This generates both API documentation variants at the same time. The generated documents live under the `docs/openapi` folder:
a `yaml` specification, the `html` documentation and a `tar` file containing both, ready for distribution.
