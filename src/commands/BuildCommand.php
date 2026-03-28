<?php

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\utils\Console;
use divengine\core\ArgParser;
use divengine\core\TemplateRunner;
use divengine\core\Filesystem;
use divengine\core\InputParser;

class BuildCommand extends Command
{
    protected string $name = 'build';
    protected string $description = 'Build artifacts from templates (prepares for multi-file generation)';
    protected string $usage = 'div build <template> [--input=file] [--out-dir=DIR] [--dry-run] [--format=FMT]';
    protected string $help = 'Build one or more artifacts from templates.

This command renders a template with input data and writes the output to a file.
Designed for generating final artifacts (config files, source code, etc.).

USAGE:
  div build template.tpl --input=data.json --out-dir=dist
  cat data.json | div build template.tpl --out-dir=generated

ARGUMENTS:
  <template>          Path to the template file

OPTIONS:
  --input=FILE       Input file (JSON, YAML, XML, or PHP). If omitted, reads from stdin.
  --format=FMT       Force input format (json, yaml, xml, php). Auto-detected if omitted.
  --out-dir=DIR      Output directory (created if it does not exist)
  --dry-run          Show what would be built without writing files
  -h, --help         Show this help message

OUTPUT:
  Output filename is based on template name: template.tpl -> template.txt
  Use --output=FILE for a custom output name.

EXAMPLES:
  # Dry run - preview what will be built
  div build template.tpl --input=data.json --dry-run

  # Build to output directory
  div build template.tpl --input=data.json --out-dir=dist

  # Build from stdin
  cat config.yaml | div build template.tpl --out-dir=generated

  # With PHP dynamic data
  div build template.tpl --input=generate.php --out-dir=output

See "div render --help" for detailed format documentation.';

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $template = $parser->getPositional(0);
        $inputFile = $parser->getOption('input');
        $outDir = $parser->getOption('out-dir');
        $dryRun = $parser->hasFlag('dry-run');
        $format = $parser->getOption('format');

        if (empty($template)) {
            Console::error('Template name is required.');
            Console::note('Run "div build --help" for usage information.');
            return 1;
        }

        $templatePath = Filesystem::absolutePath($template);
        
        if (!Filesystem::exists($templatePath)) {
            Console::error("Template file not found: {$template}");
            return 1;
        }

        $data = [];
        
        if ($inputFile) {
            if (!Filesystem::exists($inputFile)) {
                Console::error("Input file not found: {$inputFile}");
                return 1;
            }

            try {
                $data = InputParser::parseFile($inputFile, $format);
            } catch (\Throwable $e) {
                Console::error("Failed to parse input: " . $e->getMessage());
                return 1;
            }
        } elseif (InputParser::hasStdinData()) {
            try {
                $data = InputParser::parseStdin($format);
            } catch (\Throwable $e) {
                Console::error("Failed to parse stdin: " . $e->getMessage());
                return 1;
            }
        }

        $runner = new TemplateRunner();
        $templateName = pathinfo($template, PATHINFO_FILENAME);
        $outputFile = ($outDir ? rtrim($outDir, '/\\') . DIRECTORY_SEPARATOR : '') . $templateName . '.txt';

        Console::segments([
            ['text' => '[build] ', 'color' => Console::CYAN, 'bold' => true],
            ['text' => $template, 'color' => Console::YELLOW],
        ]);

        if ($dryRun) {
            Console::warning('Dry run mode - no files will be written');
            Console::line();
            Console::label('Build Plan:');
            Console::kv('Template', Filesystem::absolutePath($template));
            Console::kv('Input', $inputFile ?: '(none)');
            Console::kv('Output', $outputFile);
            Console::kv('Out Dir', $outDir ?: '(current directory)');
            Console::line();
            Console::note('Run without --dry-run to execute.');
            return 0;
        }

        try {
            Console::progress('Building...');

            $result = $runner->render($templatePath, $data);

            if ($outDir) {
                Filesystem::ensureDir($outDir);
            }

            Filesystem::writeFile($outputFile, $result);
            Console::clearProgress();

            Console::segments([
                ['text' => '[build] ', 'color' => Console::GREEN, 'bold' => true],
                ['text' => $outputFile, 'color' => Console::YELLOW],
                ['text' => ' -> ', 'color' => Console::GRAY],
                ['text' => 'OK', 'color' => Console::GREEN, 'bold' => true],
            ]);
        } catch (\Throwable $e) {
            Console::clearProgress();
            Console::error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
