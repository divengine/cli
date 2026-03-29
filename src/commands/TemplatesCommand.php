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
use divengine\core\Filesystem;

class TemplatesCommand extends Command
{
    protected string $name = 'templates';
    protected string $description = 'List available templates';
    protected string $usage = 'div templates [path]';
    protected string $help = 'List templates available in the filesystem.

Without arguments, searches standard locations for templates.
With a path argument, lists templates in that specific location.

Arguments:
  [path]              Directory to search for templates (default: current directory)

Options:
  -h, --help          Show this help message';

    private array $searchRoots = [];

    public function __construct()
    {
        $this->searchRoots = [
            getcwd(),
            getcwd() . '/templates',
            getcwd() . '/templates/src',
        ];
    }

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $path = $parser->getPositional(0);

        if ($path) {
            return $this->listFromPath($path);
        }

        return $this->listFromDefaultLocations();
    }

    private function listFromPath(string $path): int
    {
        $fullPath = Filesystem::absolutePath($path);

        if (!Filesystem::isDir($fullPath)) {
            Console::error("Directory not found: {$path}");
            return 1;
        }

        $templates = Filesystem::listTemplates($fullPath);

        if (empty($templates)) {
            Console::warning("No .tpl files found in: {$fullPath}");
            return 0;
        }

        Console::header("Templates in {$path}");
        Console::line();

        foreach ($templates as $template) {
            Console::segments([
                ['text' => '  ' . str_pad($template['name'], 20), 'color' => Console::CYAN, 'bold' => true],
                ['text' => $template['path'], 'color' => Console::YELLOW],
            ]);
        }

        Console::line();
        Console::note('Found ' . count($templates) . ' template(s)');

        return 0;
    }

    private function listFromDefaultLocations(): int
    {
        $allTemplates = [];
        $foundLocations = [];

        foreach ($this->searchRoots as $root) {
            if (!Filesystem::isDir($root)) {
                continue;
            }

            $templates = Filesystem::listTemplates($root);
            
            if (!empty($templates)) {
                $allTemplates = array_merge($allTemplates, $templates);
                $foundLocations[] = $root;
            }
        }

        if (empty($allTemplates)) {
            Console::warning('No templates found in default locations.');
            foreach ($this->searchRoots as $root) {
                Console::dim('  - ' . $root);
            }
            Console::line();
            Console::note('Run "div templates <path>" to search a specific directory.');
            return 0;
        }

        Console::header('Available Templates');
        Console::line();

        foreach ($allTemplates as $template) {
            Console::segments([
                ['text' => '  ' . str_pad($template['name'], 20), 'color' => Console::CYAN, 'bold' => true],
                ['text' => $template['path'], 'color' => Console::YELLOW],
            ]);
        }

        Console::line();
        Console::note('Found ' . count($allTemplates) . ' template(s) in ' . count($foundLocations) . ' location(s)');

        return 0;
    }
}
