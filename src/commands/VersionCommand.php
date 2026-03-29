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
use divengine\cli\Application;
use divengine\utils\Console;

class VersionCommand extends Command
{
    protected string $name = 'version';
    protected string $description = 'Show the CLI version';
    protected string $usage = 'div version';
    protected string $help = 'Displays the current version of the Divengine CLI.';

    public function __construct(private Application $app) {}

    public function run(array $args): int
    {
        Console::segments([
            ['text' => 'div ', 'color' => Console::CYAN, 'bold' => true],
            ['text' => 'v' . $this->app->getVersion(), 'color' => Console::YELLOW, 'bold' => true],
        ]);
        return 0;
    }
}
