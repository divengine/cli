<?php

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\utils\Console;
use divengine\core\ArgParser;
use divengine\core\TemplateRunner;
use divengine\core\Filesystem;

class TransformCommand extends Command
{
    protected string $name = 'transform';
    protected string $description = 'Transform data using a template (alias for render with semantic focus on transformation)';
    protected string $usage = 'div transform <template> [--input=file.json] [--output=file]';
    protected string $help = 'Transform input data through a template to produce transformed output.

This command is semantically focused on data transformation workflows,
producing intermediate or transformed results.

Arguments:
  <template>          Path to the template file (.tpl)

Options:
  --input=FILE        JSON file containing input data
  --output=FILE      Output file (default: stdout)
  -h, --help         Show this help message';

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $template = $parser->getPositional(0);
        $inputFile = $parser->getOption('input');
        $outputFile = $parser->getOption('output');

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
                Console::error("Input JSON file not found: {$inputFile}");
                return 1;
            }

            $content = Filesystem::readFile($inputFile);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Console::error('Invalid JSON in input file: ' . json_last_error_msg());
                return 1;
            }

            Console::segments([
                ['text' => '[transform] ', 'color' => Console::CYAN, 'bold' => true],
                ['text' => $template, 'color' => Console::YELLOW],
                ['text' => ' <- ', 'color' => Console::GRAY],
                ['text' => $inputFile, 'color' => Console::YELLOW],
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
