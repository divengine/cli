# Divengine CLI

> Runtime CLI for the Divengine template ecosystem

## Philosophy

`div` is a focused CLI tool centered on **template generation and transformation**. It is not a general-purpose utility belt.

**Focus areas:**
- Rendering and transforming templates
- Building artifacts from templates
- Resolving templates from packages
- Managing the Divengine template ecosystem

**What div is NOT:**
- Not a replacement for `grep`, `sed`, `awk`, or other Unix text tools
- Not a general-purpose script runner
- Not a build system for arbitrary tasks

## Requirements

- PHP >= 8.1

## Installation

### Quick Install (Recommended)

**Linux / macOS:**
```bash
curl -sSL https://raw.githubusercontent.com/divengine/cli/main/install | php
```

**Windows (PowerShell):**
```powershell
irm https://raw.githubusercontent.com/divengine/cli/main/install.ps1 | iex
```

**Windows (CMD):**
```cmd
curl -sL https://raw.githubusercontent.com/divengine/cli/main/install.bat -o install.bat && install.bat
```

After installation, restart your terminal and run `div --help`.

### Custom Install Directory

```bash
# Install to custom directory
curl -sSL https://raw.githubusercontent.com/divengine/cli/main/install | php -s --install-dir=/opt/bin
```

### Local Install (from source)

```bash
composer install
./bin/div --help
```

## Requirements

- PHP >= 8.1

## Commands

### `div help [command]`
Show help for all commands or a specific command.

```bash
div help           # List all commands
div help render   # Show render command help
```

### `div version`
Show CLI version.

```bash
div version
# Output: Divengine CLI v1.0.0
```

### `div render <template> [--input=file] [--output=file]`
Render a template with input data. Input format auto-detected (JSON, YAML, XML, PHP).

```bash
# Pipe JSON from stdin
echo '{"name": "World"}' | div render template.tpl

# Pipe YAML from stdin
cat config.yaml | div render template.tpl

# Pipe XML from stdin
echo '<data name="Test"/>' | div render template.tpl

# Input file (overrides stdin if both present)
div render template.tpl --input=data.json

# Force format
div render template.tpl --input=data.txt --format=yaml

# Save output to file
div render template.tpl --input=data.json --output=result.txt
```

### `div transform <template> [--input=file] [--output=file]`
Transform data through a template (semantic alias for render).

```bash
# Same stdin/file support as render
echo '{"key": "value"}' | div transform template.tpl
```

### `div build <template> [--input=file] [--out-dir=dir] [--dry-run]`
Build artifacts from templates.

```bash
# Pipe input from stdin
cat data.json | div build template.tpl --out-dir=dist

# Dry run (show what would be built)
div build template.tpl --input=data.json --dry-run
```

### `div templates [path]`
List available templates.

```bash
# Search default locations
div templates

# Search specific path
div templates ./my-templates
```

### `div packages [vendor-path]`
List packages in vendor directory.

```bash
div packages
```

### `div resolve <template>`
Resolve a template identifier to a physical path.

```bash
div resolve template.tpl
# Output: /absolute/path/to/template.tpl
```

### `div doctor`
Run environment diagnostics.

```bash
div doctor
```

## Template Format

Templates use `{$variable}` syntax for variable substitution (Divengine syntax):

```tpl
Hello, {$name}!
Your order #{$order_id} is ready.
```

With input JSON:
```json
{
  "name": "Alice",
  "order_id": "12345"
}
```

## Examples

### Basic Template Rendering

```bash
# Create a template
echo "Hello, {$name}!" > greet.tpl

# Create data file
echo '{"name": "World"}' > data.json

# Render
div render greet.tpl --input=data.json
# Output: Hello, World!
```

### Build Workflow

```bash
# Dry run first
div build template.tpl --input=data.json --dry-run

# Build to output directory
div build template.tpl --input=data.json --out-dir=dist
```

## Architecture

```
src/
├── cli/
│   ├── Application.php     # Command registry and routing
│   └── Command.php         # Abstract base command
├── commands/
│   ├── HelpCommand.php
│   ├── VersionCommand.php
│   ├── DoctorCommand.php
│   ├── RenderCommand.php
│   ├── TransformCommand.php
│   ├── BuildCommand.php
│   ├── TemplatesCommand.php
│   ├── PackagesCommand.php
│   └── ResolveCommand.php
├── core/
│   ├── ArgParser.php      # CLI argument parsing
│   ├── TemplateRunner.php  # Template execution engine
│   ├── Filesystem.php     # File utilities
│   ├── PackageInspector.php # Package discovery
│   └── DoctorService.php  # Environment diagnostics
└── utils/
    └── Console.php        # Console output helpers
```

## Extending

Create a new command:

```php
<?php

namespace divengine\commands;

use divengine\cli\Command;
use divengine\utils\Console;
use divengine\core\ArgParser;

class MyCommand extends Command
{
    protected string $name = 'my-command';
    protected string $description = 'Description here';
    protected string $usage = 'div my-command <arg>';
    protected string $help = 'Help text here.';

    public function run(array $args): int
    {
        Console::info('Running my command');
        return 0;
    }
}
```

Register in `Application::registerDefaultCommands()`:

```php
$this->register(new \divengine\commands\MyCommand());
```

## Future Capabilities (Planned)

- [x] Basic template rendering (fallback engine)
- [x] Full divengine/div integration
- [ ] Package-based template resolution (`org/package/template`)
- [ ] Multi-file template generation
- [ ] Template caching system
- [ ] Plugin system
- [ ] Interactive mode for AI agents

## Current Limitations

- Template resolution is file-system only (no package resolution yet)
- Build command is single-file for now (multi-file planned)
- No template caching
- No plugin system

## Console Output

The CLI uses standard ANSI escape codes for colored terminal output. This provides:
- Consistent, readable output across platforms
- Semantic coloring: errors (red), warnings (yellow), success (green), info (cyan)
- Multi-segment lines with different colors in the same output
- Basic progress indicators

Color support is automatically detected and gracefully degraded when not available.

## License

MIT
