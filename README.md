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

### Global Install (Recommended)

**Linux / macOS:**
```bash
./bin/install.sh
# Or system-wide (requires sudo):
# sudo ./bin/install.sh /usr/local/bin
```

**Windows:**
```cmd
bin\install.bat
```

After installation, restart your terminal and run `div --help`.

### Local Install

```bash
composer install
./bin/div --help
```

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

### `div render <template> [--input=file.json] [--output=file]`
Render a template with input data.

```bash
# Render with stdin context
div render template.tpl

# Render with JSON input file
div render template.tpl --input=data.json

# Render and save to file
div render template.tpl --input=data.json --output=result.txt
```

### `div transform <template> [--input=file.json] [--output=file]`
Transform data through a template (semantic alias for render).

```bash
# Same syntax as render
div transform template.tpl --input=data.json
```

### `div build <template> [--input=file.json] [--out-dir=dir] [--dry-run]`
Build artifacts from templates.

```bash
# Dry run (show what would be built)
div build template.tpl --input=data.json --dry-run

# Build to specific directory
div build template.tpl --input=data.json --out-dir=generated
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

Templates use `{{variable}}` syntax for variable substitution:

```tpl
Hello, {{name}}!
Your order #{{order_id}} is ready.
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
echo "Hello, {{name}}!" > greet.tpl

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
- [ ] Full divengine/div integration
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
