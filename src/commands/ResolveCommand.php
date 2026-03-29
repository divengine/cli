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

class ResolveCommand extends Command
{
    protected string $name = 'resolve';
    protected string $description = 'Resolve a template identifier to a physical path';
    protected string $usage = 'div resolve <template>';
    protected string $help = 'Resolve a template identifier or path to its physical location.

This command normalizes and resolves template paths. In future versions,
it will support logical package references like "org/package/template".

Arguments:
  <template>          Template name or path to resolve

Options:
  -h, --help          Show this help message';

    public function run(array $args): int
    {
        $parser = new ArgParser($args);
        $template = $parser->getPositional(0);

        if (empty($template)) {
            Console::error('Template identifier is required.');
            Console::note('Run "div resolve --help" for usage information.');
            return 1;
        }

        $absolutePath = Filesystem::absolutePath($template);

        if (Filesystem::exists($absolutePath)) {
            Console::segments([
                ['text' => '[resolve] ', 'color' => Console::CYAN, 'bold' => true],
                ['text' => $template, 'color' => Console::YELLOW],
                ['text' => ' -> ', 'color' => Console::GRAY],
                ['text' => $absolutePath, 'color' => Console::GREEN],
            ]);
            return 0;
        }

        if (Filesystem::exists($template)) {
            Console::line(Filesystem::absolutePath($template));
            return 0;
        }

        Console::error("Template not found: {$template}");
        return 1;
    }
}
