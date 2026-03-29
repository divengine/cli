<?php

/**
 * [[]] Div CLI - Official runtime for the Divengine template ecosystem
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program as the file LICENSE.txt; if not, please see
 * https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package divengine/cli
 * @author  Rafa Rodriguez @rafageist [https://rafageist.com]
 * @version 1.0.0
 *
 * @link    https://github.com/divengine/cli
 */

declare(strict_types=1);

namespace divengine\commands;

use divengine\cli\Command;
use divengine\utils\Console;
use divengine\core\ArgParser;
use divengine\core\TemplateRunner;
use divengine\core\Filesystem;
use divengine\core\InputParser;

class RenderCommand extends Command
{
    protected string $name = 'render';
    protected string $description = 'Render a template with input data';
    protected string $usage = 'div render <template> [--input=file] [--output=file] [--format=FMT]';
    protected string $help = 'Render a template file with the provided input data.

The template is processed using the divengine/div engine with {$variable} syntax.

USAGE:
  div render template.tpl --input=data.json
  echo \'{"name": "World"}\' | div render template.tpl

ARGUMENTS:
  <template>          Path to the template file

OPTIONS:
  --input=FILE       Input file (JSON, YAML, XML, or PHP). If omitted, reads from stdin.
  --format=FMT       Force input format (json, yaml, xml, php). Auto-detected if omitted.
  --output=FILE      Output file (default: stdout)
  -h, --help         Show this help message

INPUT FORMATS:
  JSON:  Standard JSON objects and arrays
  YAML:  Key-value pairs, nested objects, lists
  XML:   Elements become nested keys, @attr for attributes
  PHP:   File must return array, enables dynamic data

EXAMPLES:
  # With input file
  div render template.tpl --input=data.json

  # Pipe JSON from stdin (Linux/macOS)
  echo \'{"name": "World"}\' | div render template.tpl

  # Pipe YAML from stdin
  cat config.yaml | div render template.tpl

  # Pipe XML from stdin
  echo \'<user name="John"><email>john@example.com</email></user>\' | div render template.tpl

  # Save output to file
  div render template.tpl --input=data.json --output=result.txt

  # Force format for unrecognized extensions
  div render template.tpl --input=data.txt --format=yaml

XML CONVENTION:
  Attributes are prefixed with @:
    <user name="John" email="john@example.com">
      <address>123 Main St</address>
    </user>
  
  Template access:
    {$user.@name}   => "John"
    {$user.@email}  => "john@example.com"
    {$user.address} => "123 Main St"

  Multiple children become arrays:
    <employees>
      <employee name="John"/>
      <employee name="Jane"/>
    </employees>
  
  Template access:
    {$employees.employee[0].@name} => "John"
    {$employees.employee[1].@name} => "Jane"

PHP CONVENTION:
  File must return an array:
    <?php
    return [
        "name" => "John",
        "timestamp" => time(),
        "config" => [
            "env" => getenv("APP_ENV") ?: "production"
        ]
    ];
  
  Use PHP input for dynamic data generation:
    div render template.tpl --input=generate.php';

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $template = $parser->getPositional(0);
        $inputFile = $parser->getOption('input');
        $outputFile = $parser->getOption('output');
        $format = $parser->getOption('format');

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
                ['text' => '[input] ', 'color' => Console::CYAN, 'bold' => true],
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
                ['text' => '[input] ', 'color' => Console::CYAN, 'bold' => true],
                ['text' => '<stdin>', 'color' => Console::GRAY],
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
