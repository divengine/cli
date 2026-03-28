<?php

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\utils\Console;
use divengine\core\ArgParser;
use divengine\core\TemplateRunner;
use divengine\core\Filesystem;

class RenderCommand extends Command
{
    protected string $name = 'render';
    protected string $description = 'Render a template with input data';
    protected string $usage = 'div render <template> [--input=file.json] [--output=file]';
    protected string $help = 'Render a template file with the provided input data.

The template is processed using the divengine/div engine (or a fallback
for basic {{variable}} substitution) and the result is output.

Arguments:
  <template>          Path to the template file (.tpl)

Options:
  --input=FILE        JSON file containing template context data
  --output=FILE       Output file (default: stdout)
  -h, --help          Show this help message';

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $template = $parser->getPositional(0);
        $inputFile = $parser->getOption('input');
        $outputFile = $parser->getOption('output');

        if (empty($template)) {
            Console::error('Template name is required.');
            Console::note('Run "div render --help" for usage information.');
            return 1;
        }

        $templatePath = Filesystem::absolutePath($template);

        if (!Filesystem::exists($templatePath)) {
            Console::error("Template file not found: {$template}");
            return 1;
        }

        Console::segments([
            ['text' => '[render] ', 'color' => Console::CYAN, 'bold' => true],
            ['text' => $template, 'color' => Console::YELLOW],
        ]);

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
                ['text' => '[input] ', 'color' => Console::CYAN, 'bold' => true],
                ['text' => $inputFile, 'color' => Console::YELLOW],
            ]);
        }

        Console::progress('Rendering...');

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
