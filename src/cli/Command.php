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

namespace divengine\cli;

use divengine\utils\Console;

abstract class Command
{
    protected string $name;
    protected string $description = '';
    protected string $usage = '';
    protected string $help = '';

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUsage(): string
    {
        return $this->usage ?: "div {$this->name}";
    }

    public function getHelp(): string
    {
        return $this->help;
    }

    public function showHelp(): int
    {
        Console::line();
        
        Console::segments([
            ['text' => 'Usage: ', 'color' => Console::CYAN, 'bold' => true],
            ['text' => $this->getUsage()],
        ]);
        
        Console::line();

        if ($this->description) {
            Console::line('  ' . $this->description);
            Console::line();
        }

        if ($this->help) {
            Console::dim(str_repeat('-', 50));
            Console::line($this->help);
            Console::dim(str_repeat('-', 50));
            Console::line();
        }

        Console::note("Run 'div help' for available commands.");

        return 0;
    }

    abstract public function run(array $args): int;

    protected function getOption(string $key, array $args, mixed $default = null): mixed
    {
        foreach ($args as $arg) {
            if (str_starts_with($arg, "--{$key}=")) {
                return substr($arg, strlen($key) + 3);
            }
        }
        return $default;
    }

    protected function hasFlag(string $flag, array $args): bool
    {
        return in_array("--{$flag}", $args, true);
    }
}
