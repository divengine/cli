## Summary

**Initial release of div CLI** - Official runtime and package manager for the Divengine template ecosystem.

### New Commands
- `render` - Render templates with input data
- `transform` - Transform data through templates
- `build` - Build artifacts from templates
- `doctor` - Run environment diagnostics
- `templates` - List available templates
- `packages` - List packages in vendor directory
- `resolve` - Resolve template identifiers to physical paths

### Input Formats
- **JSON** - Standard JSON objects and arrays
- **YAML** - Key-value pairs, nested objects, lists
- **XML** - Elements as nested keys, @ prefix for attributes
- **PHP** - Files that return arrays (dynamic data generation)

### Features
- Stdin support (pipe data without --input flag)
- Auto-detection of input format
- Cross-platform ANSI colored output
- PHAR distribution for easy installation
- Self-contained builds (no dependencies needed)

### Infrastructure
- **PHAR Build** - Optimized PHAR with ~1MB size, only production dependencies
- **CI/CD** - PHPUnit tests + PHPStan static analysis on PRs
- **Release Pipeline** - Automatic releases on merge to main with PDF documentation
- **Install Scripts** - Unix (curl | php), Windows (PowerShell/CMD)
- **Local Development** - `php install --from-here` builds and installs from local source

## Checks

- [x] Tests pass
- [x] PHPStan passes
- [x] Ready for review

## Installation

```bash
# Unix/macOS
curl -sSL https://raw.githubusercontent.com/divengine/cli/main/install | php

# Windows (PowerShell)
irm https://raw.githubusercontent.com/divengine/cli/main/install.ps1 | iex
```

### Development Install

```bash
git clone https://github.com/divengine/cli.git
cd cli
php install --from-here
```

## Requirements

- PHP >= 8.1

## Documentation

See the `docs/` directory for detailed documentation. Template syntax is documented in [divengine/div](https://github.com/divengine/div).
