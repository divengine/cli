<?php

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\utils\Console;
use divengine\core\ArgParser;
use divengine\core\TemplateRunner;
use divengine\core\Filesystem;
use divengine\core\InputParser;

class TransformCommand extends Command
{
    protected string $name = 'transform';
    protected string $description = 'Transform data using a template (alias for render with semantic focus on transformation)';
    protected string $usage = 'div transform <template> [--input=file] [--output=file] [--format=FMT]';
    protected string $help = 'Transform input data through a template to produce transformed output.

This command is semantically focused on data transformation workflows,
producing intermediate or transformed results. Same options as render.

USAGE:
  div transform template.tpl --input=data.json
  cat data.json | div transform template.tpl

ARGUMENTS:
  <template>          Path to the template file

OPTIONS:
  --input=FILE       Input file (JSON, YAML, XML, or PHP). If omitted, reads from stdin.
  --format=FMT       Force input format (json, yaml, xml, php). Auto-detected if omitted.
  --output=FILE      Output file (default: stdout)
  -h, --help         Show this help message

EXAMPLES:
  # Transform JSON file
  div transform template.tpl --input=data.json

  # Transform from stdin
  echo \'{"key": "value"}\' | div transform template.tpl

  # Save transformed output
  div transform template.tpl --input=data.json --output=result.txt

  # XML transformation
  echo \'<config><setting>value</setting></config>\' | div transform template.tpl

See "div render --help" for detailed format documentation.';

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $template = $parser->getPositional(0);
        $inputFile = $parser->getOption('input');
        $outputFile = $parser->getOption('output');
        $format = $parser->getOption('format');

        if (empty($template)) {
            Console::error('Template name is required.');
            Console::note('Run "div transform --help" for usage information.');
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

            Console::segments([
                ['text' => '[transform] ', 'color' => Console::CYAN, 'bold' => true],
                ['text' => $template, 'color' => Console::YELLOW],
                ['text' => ' <- ', 'color' => Console::GRAY],
                ['text' => $inputFile, 'color' => Console::YELLOW],
            ]);
        } elseif (InputParser::hasStdinData()) {
            try {
                $data = InputParser::parseStdin($format);
            } catch (\Throwable $e) {
                Console::error("Failed to parse stdin: " . $e->getMessage());
                return 1;
            }

            Console::segments([
                ['text' => '[transform] ', 'color' => Console::CYAN, 'bold' => true],
                ['text' => $template, 'color' => Console::YELLOW],
                ['text' => ' <- ', 'color' => Console::GRAY],
                ['text' => '<stdin>', 'color' => Console::GRAY],
            ]);
        } else {
            Console::segments([
                ['text' => '[transform] ', 'color' => Console::CYAN, 'bold' => true],
                ['text' => $template, 'color' => Console::YELLOW],
            ]);
        }

        Console::progress('Transforming...');

        $runner = new TemplateRunner();
        
        try {
            $result = $runner->render($templatePath, $data);
            Console::clearProgress();

            if ($outputFile) {
                Filesystem::writeFile($outputFile, $result);
                Console::segments([
                    ['text' => '[output] ', 'color' => Console::GREEN, 'bold' => true],
                    ['text' => $outputFile, 'color' => Console::YELLOW],
                    ['text' => ' -> ', 'color' => Console::GRAY],
                    ['text' => 'OK', 'color' => Console::GREEN, 'bold' => true],
                ]);
            } else {
                Console::line();
                Console::line($result);
            }
        } catch (\Throwable $e) {
            Console::clearProgress();
            Console::error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
