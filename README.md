# Paella Core

The heart of Billie Pay-After-Delivery (PaD).

## API Documentation

### Generation
Requirements: `git` and `npm` (with npx).
Optionally, you can install `redoc-cli` globally with npm, to speed up the process.

Usage:

```bash
bin/generate-api-docs [API_VERSION]
```

This generates all API documentation variants at the same time (public, private, etc.).

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

The generated YAML and HTML documents live under the `docs/openapi` folder.
